<?php
/**
 * External API for sending xAPI statements
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoxapi\external;

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_module;
use mod_videoxapi\xapi\StatementBuilder;
use mod_videoxapi\xapi\Tracker;
use mod_videoxapi\xapi\ConfigManager;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * External API for sending xAPI statements
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_statement extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'videoxapiid' => new external_value(PARAM_INT, 'Video xAPI instance ID'),
            'verb' => new external_value(PARAM_ALPHA, 'xAPI verb'),
            'data' => new external_value(PARAM_RAW, 'Statement data as JSON')
        ]);
    }

    /**
     * Send xAPI statement
     *
     * @param int $videoxapiid Video xAPI instance ID
     * @param string $verb xAPI verb
     * @param string $data Statement data as JSON
     * @return array Result
     */
    public static function execute($videoxapiid, $verb, $data) {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'videoxapiid' => $videoxapiid,
            'verb' => $verb,
            'data' => $data
        ]);

        // Get videoxapi instance and validate access.
        $videoxapi = $DB->get_record('videoxapi', ['id' => $params['videoxapiid']], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $videoxapi->course], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('videoxapi', $videoxapi->id, $course->id, false, MUST_EXIST);

        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videoxapi:view', $context);

        // Check if xAPI is enabled.
        if (!ConfigManager::isXapiEnabled()) {
            return ['success' => false, 'error' => 'xAPI tracking is disabled'];
        }

        try {
            $statementData = json_decode($params['data'], true);
            if ($statementData === null) {
                return ['success' => false, 'error' => 'Invalid JSON data'];
            }

            // Build xAPI statement.
            $builder = new StatementBuilder($USER, $videoxapi, $course);
            
            switch ($params['verb']) {
                case 'played':
                    $statement = $builder->buildPlayStatement(
                        $statementData['time'],
                        $statementData['length']
                    );
                    break;
                case 'paused':
                    $statement = $builder->buildPauseStatement(
                        $statementData['time'],
                        $statementData['length']
                    );
                    break;
                case 'seeked':
                    $statement = $builder->buildSeekStatement(
                        $statementData['timeFrom'],
                        $statementData['timeTo'],
                        $statementData['length']
                    );
                    break;
                case 'completed':
                    $statement = $builder->buildCompletedStatement(
                        $statementData['time'],
                        $statementData['length'],
                        $statementData['watchedPercentage']
                    );
                    break;
                case 'bookmarked':
                    $statement = $builder->buildBookmarkStatement(
                        $statementData['time'],
                        $statementData['title'],
                        $statementData['description'],
                        $statementData['length']
                    );
                    break;
                default:
                    return ['success' => false, 'error' => 'Unknown verb: ' . $params['verb']];
            }

            // Send or queue statement.
            $config = ConfigManager::getLrsConfig();
            if ($config['endpoint'] && $config['username'] && $config['password']) {
                $tracker = new Tracker(
                    $config['endpoint'],
                    $config['username'],
                    $config['password'],
                    $config['auth_method']
                );
                
                $result = $tracker->sendStatement($statement);
                if (!$result['success']) {
                    // Queue for later if sending fails.
                    $tracker->queueStatement($statement);
                }
                
                return $result;
            } else {
                // Queue statement if LRS not configured.
                $tracker = new Tracker('', '', '');
                $tracker->queueStatement($statement);
                return ['success' => true, 'queued' => true];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'error' => new external_value(PARAM_TEXT, 'Error message', VALUE_OPTIONAL),
            'queued' => new external_value(PARAM_BOOL, 'Statement was queued', VALUE_OPTIONAL)
        ]);
    }
}