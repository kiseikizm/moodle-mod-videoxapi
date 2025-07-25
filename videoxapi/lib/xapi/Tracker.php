<?php
/**
 * xAPI LRS communication tracker for videoxapi module
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoxapi\xapi;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for communicating with Learning Record Store (LRS)
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class Tracker {

    /** @var string LRS endpoint URL */
    private $endpoint;

    /** @var string Authentication username */
    private $username;

    /** @var string Authentication password */
    private $password;

    /** @var string Authentication method */
    private $authMethod;

    /** @var int Maximum retry attempts */
    private $maxRetries;

    /** @var int Base delay for exponential backoff (seconds) */
    private $baseDelay;

    /** @var int Connection timeout (seconds) */
    private $timeout;

    /**
     * Constructor
     *
     * @param string $endpoint LRS endpoint URL
     * @param string $username Authentication username
     * @param string $password Authentication password
     * @param string $authMethod Authentication method ('basic' or 'oauth')
     * @param int $maxRetries Maximum retry attempts
     * @param int $timeout Connection timeout in seconds
     */
    public function __construct($endpoint, $username, $password, $authMethod = 'basic', $maxRetries = 3, $timeout = 30) {
        $this->endpoint = rtrim($endpoint, '/');
        $this->username = $username;
        $this->password = $password;
        $this->authMethod = $authMethod;
        $this->maxRetries = $maxRetries;
        $this->baseDelay = 1;
        $this->timeout = $timeout;
    }    /**
     * Send xAPI statement to LRS
     *
     * @param array $statement xAPI statement
     * @return array Result with success status and response data
     */
    public function sendStatement($statement) {
        $url = $this->endpoint . '/statements';
        
        $data = json_encode($statement);
        if ($data === false) {
            return [
                'success' => false,
                'error' => 'Failed to encode statement as JSON',
                'http_code' => 0
            ];
        }

        return $this->sendWithRetry('POST', $url, $data);
    }

    /**
     * Send multiple xAPI statements to LRS
     *
     * @param array $statements Array of xAPI statements
     * @return array Result with success status and response data
     */
    public function sendStatements($statements) {
        $url = $this->endpoint . '/statements';
        
        $data = json_encode($statements);
        if ($data === false) {
            return [
                'success' => false,
                'error' => 'Failed to encode statements as JSON',
                'http_code' => 0
            ];
        }

        return $this->sendWithRetry('POST', $url, $data);
    }

    /**
     * Test LRS connection
     *
     * @return array Result with success status and response data
     */
    public function testConnection() {
        $url = $this->endpoint . '/about';
        return $this->sendWithRetry('GET', $url);
    }

    /**
     * Queue statement for later sending
     *
     * @param array $statement xAPI statement
     * @return bool Success status
     */
    public function queueStatement($statement) {
        global $DB;

        $record = new \stdClass();
        $record->statement = json_encode($statement);
        $record->status = 0; // Pending.
        $record->attempts = 0;
        $record->timecreated = time();
        $record->timemodified = time();

        try {
            $DB->insert_record('videoxapi_statements', $record);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }    /**
     * Process queued statements
     *
     * @param int $limit Maximum number of statements to process
     * @return array Processing results
     */
    public function processQueue($limit = 50) {
        global $DB;

        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'errors' => []
        ];

        // Get pending statements.
        $statements = $DB->get_records('videoxapi_statements', 
            ['status' => 0], 
            'timecreated ASC', 
            '*', 
            0, 
            $limit
        );

        foreach ($statements as $record) {
            $results['processed']++;

            // Decode statement.
            $statement = json_decode($record->statement, true);
            if ($statement === null) {
                $this->markStatementFailed($record->id, 'Invalid JSON in queued statement');
                $results['failed']++;
                continue;
            }

            // Send statement.
            $response = $this->sendStatement($statement);

            if ($response['success']) {
                $this->markStatementSent($record->id);
                $results['successful']++;
            } else {
                $record->attempts++;
                if ($record->attempts >= $this->maxRetries) {
                    $this->markStatementFailed($record->id, $response['error']);
                    $results['failed']++;
                } else {
                    $this->updateStatementAttempt($record->id, $record->attempts, $response['error']);
                }
                $results['errors'][] = $response['error'];
            }
        }

        return $results;
    }

    /**
     * Send HTTP request with retry mechanism
     *
     * @param string $method HTTP method
     * @param string $url Request URL
     * @param string $data Request body data
     * @return array Result with success status and response data
     */
    private function sendWithRetry($method, $url, $data = null) {
        $attempt = 0;
        
        while ($attempt < $this->maxRetries) {
            $result = $this->sendHttpRequest($method, $url, $data);
            
            if ($result['success'] || !$this->isRetryableError($result['http_code'])) {
                return $result;
            }
            
            $attempt++;
            if ($attempt < $this->maxRetries) {
                $delay = $this->baseDelay * pow(2, $attempt - 1);
                sleep($delay);
            }
        }
        
        return $result;
    }    /**
     * Send HTTP request to LRS
     *
     * @param string $method HTTP method
     * @param string $url Request URL
     * @param string $data Request body data
     * @return array Result with success status and response data
     */
    private function sendHttpRequest($method, $url, $data = null) {
        $headers = [
            'Content-Type: application/json',
            'X-Experience-API-Version: 1.0.3'
        ];

        // Add authentication header.
        if ($this->authMethod === 'basic') {
            $headers[] = 'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'Moodle VideoXAPI Plugin/1.0'
        ]);

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return [
                'success' => false,
                'error' => 'cURL error: ' . $error,
                'http_code' => 0,
                'response' => null
            ];
        }

        $success = $httpCode >= 200 && $httpCode < 300;
        
        return [
            'success' => $success,
            'error' => $success ? null : 'HTTP ' . $httpCode . ': ' . $response,
            'http_code' => $httpCode,
            'response' => $response
        ];
    }

    /**
     * Check if HTTP error code is retryable
     *
     * @param int $httpCode HTTP status code
     * @return bool True if retryable
     */
    private function isRetryableError($httpCode) {
        // Retry on server errors and rate limiting.
        return $httpCode >= 500 || $httpCode === 429 || $httpCode === 0;
    }    /**
     * Mark statement as successfully sent
     *
     * @param int $id Statement record ID
     */
    private function markStatementSent($id) {
        global $DB;
        
        $DB->set_field('videoxapi_statements', 'status', 1, ['id' => $id]);
        $DB->set_field('videoxapi_statements', 'timemodified', time(), ['id' => $id]);
    }

    /**
     * Mark statement as failed
     *
     * @param int $id Statement record ID
     * @param string $error Error message
     */
    private function markStatementFailed($id, $error) {
        global $DB;
        
        $DB->set_field('videoxapi_statements', 'status', 2, ['id' => $id]);
        $DB->set_field('videoxapi_statements', 'error_message', $error, ['id' => $id]);
        $DB->set_field('videoxapi_statements', 'timemodified', time(), ['id' => $id]);
    }

    /**
     * Update statement attempt count
     *
     * @param int $id Statement record ID
     * @param int $attempts Number of attempts
     * @param string $error Last error message
     */
    private function updateStatementAttempt($id, $attempts, $error) {
        global $DB;
        
        $DB->set_field('videoxapi_statements', 'attempts', $attempts, ['id' => $id]);
        $DB->set_field('videoxapi_statements', 'error_message', $error, ['id' => $id]);
        $DB->set_field('videoxapi_statements', 'timemodified', time(), ['id' => $id]);
    }

    /**
     * Get queue statistics
     *
     * @return array Queue statistics
     */
    public function getQueueStats() {
        global $DB;

        return [
            'pending' => $DB->count_records('videoxapi_statements', ['status' => 0]),
            'sent' => $DB->count_records('videoxapi_statements', ['status' => 1]),
            'failed' => $DB->count_records('videoxapi_statements', ['status' => 2]),
            'total' => $DB->count_records('videoxapi_statements')
        ];
    }

    /**
     * Clean up old processed statements
     *
     * @param int $days Number of days to keep processed statements
     * @return int Number of records deleted
     */
    public function cleanupQueue($days = 30) {
        global $DB;

        $cutoff = time() - ($days * 24 * 60 * 60);
        
        return $DB->delete_records_select('videoxapi_statements', 
            'status IN (1, 2) AND timemodified < ?', 
            [$cutoff]
        );
    }
}