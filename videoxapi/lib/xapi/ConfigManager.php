<?php
/**
 * Configuration manager for videoxapi xAPI settings
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoxapi\xapi;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for managing xAPI LRS configuration
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ConfigManager {

    /** @var string Configuration prefix */
    const CONFIG_PREFIX = 'videoxapi_';

    /** @var string Encryption method */
    const ENCRYPTION_METHOD = 'AES-256-CBC';

    /**
     * Get LRS endpoint URL
     *
     * @return string|null LRS endpoint URL
     */
    public static function getLrsEndpoint() {
        return get_config('mod_videoxapi', 'lrs_endpoint');
    }

    /**
     * Set LRS endpoint URL
     *
     * @param string $endpoint LRS endpoint URL
     * @return bool Success status
     */
    public static function setLrsEndpoint($endpoint) {
        if (!self::validateEndpointUrl($endpoint)) {
            return false;
        }
        
        return set_config('lrs_endpoint', $endpoint, 'mod_videoxapi');
    }    /**
     * Get LRS authentication username
     *
     * @return string|null LRS username
     */
    public static function getLrsUsername() {
        // Temporarily disable encryption for debugging
        return get_config('mod_videoxapi', 'lrs_username');
    }

    /**
     * Set LRS authentication username
     *
     * @param string $username LRS username
     * @return bool Success status
     */
    public static function setLrsUsername($username) {
        $encrypted = self::encrypt($username);
        return set_config('lrs_username', $encrypted, 'mod_videoxapi');
    }

    /**
     * Get LRS authentication password
     *
     * @return string|null LRS password
     */
    public static function getLrsPassword() {
        // Temporarily disable encryption for debugging
        return get_config('mod_videoxapi', 'lrs_password');
    }

    /**
     * Set LRS authentication password
     *
     * @param string $password LRS password
     * @return bool Success status
     */
    public static function setLrsPassword($password) {
        $encrypted = self::encrypt($password);
        return set_config('lrs_password', $encrypted, 'mod_videoxapi');
    }

    /**
     * Get LRS authentication method
     *
     * @return string Authentication method ('basic' or 'oauth')
     */
    public static function getLrsAuthMethod() {
        return get_config('mod_videoxapi', 'lrs_auth_method') ?: 'basic';
    }

    /**
     * Set LRS authentication method
     *
     * @param string $method Authentication method
     * @return bool Success status
     */
    public static function setLrsAuthMethod($method) {
        if (!in_array($method, ['basic', 'oauth'])) {
            return false;
        }
        
        return set_config('lrs_auth_method', $method, 'mod_videoxapi');
    }    /**
     * Get xAPI tracking enabled status
     *
     * @return bool True if xAPI tracking is enabled
     */
    public static function isXapiEnabled() {
        return (bool) get_config('mod_videoxapi', 'xapi_enabled');
    }

    /**
     * Set xAPI tracking enabled status
     *
     * @param bool $enabled Enable/disable xAPI tracking
     * @return bool Success status
     */
    public static function setXapiEnabled($enabled) {
        return set_config('xapi_enabled', $enabled ? 1 : 0, 'mod_videoxapi');
    }

    /**
     * Get queue processing enabled status
     *
     * @return bool True if queue processing is enabled
     */
    public static function isQueueEnabled() {
        return (bool) get_config('mod_videoxapi', 'queue_enabled');
    }

    /**
     * Set queue processing enabled status
     *
     * @param bool $enabled Enable/disable queue processing
     * @return bool Success status
     */
    public static function setQueueEnabled($enabled) {
        return set_config('queue_enabled', $enabled ? 1 : 0, 'mod_videoxapi');
    }

    /**
     * Get all LRS configuration
     *
     * @return array Configuration array
     */
    public static function getLrsConfig() {
        return [
            'endpoint' => self::getLrsEndpoint(),
            'username' => self::getLrsUsername(),
            'password' => self::getLrsPassword(),
            'auth_method' => self::getLrsAuthMethod(),
            'xapi_enabled' => self::isXapiEnabled(),
            'queue_enabled' => self::isQueueEnabled()
        ];
    }

    /**
     * Validate LRS configuration
     *
     * @param array $config Configuration array
     * @return array Validation result with errors
     */
    public static function validateLrsConfig($config) {
        $errors = [];

        // Validate endpoint.
        if (empty($config['endpoint'])) {
            $errors['endpoint'] = 'LRS endpoint is required';
        } else if (!self::validateEndpointUrl($config['endpoint'])) {
            $errors['endpoint'] = 'Invalid LRS endpoint URL';
        }

        // Validate credentials.
        if (empty($config['username'])) {
            $errors['username'] = 'LRS username is required';
        }

        if (empty($config['password'])) {
            $errors['password'] = 'LRS password is required';
        }

        // Validate auth method.
        if (!empty($config['auth_method']) && !in_array($config['auth_method'], ['basic', 'oauth'])) {
            $errors['auth_method'] = 'Invalid authentication method';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }    /**
     * Test LRS connection with current configuration
     *
     * @return array Test result
     */
    public static function testLrsConnection() {
        $config = self::getLrsConfig();
        
        $validation = self::validateLrsConfig($config);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => 'Invalid configuration: ' . implode(', ', $validation['errors'])
            ];
        }

        try {
            $tracker = new Tracker(
                $config['endpoint'],
                $config['username'],
                $config['password'],
                $config['auth_method']
            );

            return $tracker->testConnection();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Connection test failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Encrypt sensitive data
     *
     * @param string $data Data to encrypt
     * @return string Encrypted data
     */
    private static function encrypt($data) {
        if (empty($data)) {
            return '';
        }

        $key = self::getEncryptionKey();
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::ENCRYPTION_METHOD));
        $encrypted = openssl_encrypt($data, self::ENCRYPTION_METHOD, $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt sensitive data
     *
     * @param string $encryptedData Encrypted data
     * @return string Decrypted data
     */
    private static function decrypt($encryptedData) {
        if (empty($encryptedData)) {
            return '';
        }

        $key = self::getEncryptionKey();
        $data = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length(self::ENCRYPTION_METHOD);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        
        return openssl_decrypt($encrypted, self::ENCRYPTION_METHOD, $key, 0, $iv);
    }    /**
     * Get encryption key for sensitive data
     *
     * @return string Encryption key
     */
    private static function getEncryptionKey() {
        global $CFG;
        
        // Use Moodle's secret key as base for encryption.
        $baseKey = isset($CFG->passwordsaltmain) ? $CFG->passwordsaltmain : 'default_salt';
        
        // Create a consistent key for this plugin.
        return hash('sha256', $baseKey . 'videoxapi_encryption_key');
    }

    /**
     * Validate LRS endpoint URL
     *
     * @param string $url URL to validate
     * @return bool True if valid
     */
    private static function validateEndpointUrl($url) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parsedUrl = parse_url($url);
        
        // Must be HTTPS for security.
        if ($parsedUrl['scheme'] !== 'https') {
            return false;
        }

        // Must have host.
        if (empty($parsedUrl['host'])) {
            return false;
        }

        return true;
    }

    /**
     * Get default configuration values
     *
     * @return array Default configuration
     */
    public static function getDefaultConfig() {
        return [
            'endpoint' => '',
            'username' => '',
            'password' => '',
            'auth_method' => 'basic',
            'xapi_enabled' => false,
            'queue_enabled' => true
        ];
    }

    /**
     * Reset configuration to defaults
     *
     * @return bool Success status
     */
    public static function resetConfig() {
        $defaults = self::getDefaultConfig();
        
        foreach ($defaults as $key => $value) {
            $configKey = str_replace('_', '_', $key);
            if (!set_config($configKey, $value, 'mod_videoxapi')) {
                return false;
            }
        }
        
        return true;
    }
}