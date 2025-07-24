<?php
/**
 * External API for saving bookmarks
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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * External API for saving bookmarks
 */
class save_bookmark extends external_api {

    /**
     * Returns description of method parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'videoxapiid' => new external_value(PARAM_INT, 'Video xAPI instance ID'),
            'timestamp' => new external_value(PARAM_FLOAT, 'Bookmark timestamp in seconds'),
            'title' => new external_value(PARAM_TEXT, 'Bookmark title'),
            'description' => new external_value(PARAM_TEXT, 'Bookmark description', VALUE_DEFAULT, '')
        ]);
    }

    /**
     * Save bookmark
     */
    public static function execute($videoxapiid, $timestamp, $title, $description = '') {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'videoxapiid' => $videoxapiid,
            'timestamp' => $timestamp,
            'title' => $title,
            'description' => $description
        ]);

        // Get videoxapi instance and validate access.
        $videoxapi = $DB->get_record('videoxapi', ['id' => $params['videoxapiid']], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $videoxapi->course], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('videoxapi', $videoxapi->id, $course->id, false, MUST_EXIST);

        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videoxapi:createbookmarks', $context);

        // Check if bookmarks are enabled.
        if (!$videoxapi->enable_bookmarks) {
            throw new \moodle_exception('bookmarksdisabled', 'mod_videoxapi');
        }

        // Validate timestamp.
        if ($params['timestamp'] < 0) {
            throw new \invalid_parameter_exception('Timestamp cannot be negative');
        }

        // Check for duplicate bookmark at same timestamp.
        $existing = $DB->get_record('videoxapi_bookmarks', [
            'videoxapi' => $params['videoxapiid'],
            'userid' => $USER->id,
            'timestamp' => $params['timestamp']
        ]);

        if ($existing) {
            throw new \moodle_exception('duplicatebookmark', 'mod_videoxapi');
        }

        // Create bookmark record.
        $bookmark = new \stdClass();
        $bookmark->videoxapi = $params['videoxapiid'];
        $bookmark->userid = $USER->id;
        $bookmark->timestamp = $params['timestamp'];
        $bookmark->title = $params['title'];
        $bookmark->description = $params['description'];
        $bookmark->timecreated = time();

        try {
            $id = $DB->insert_record('videoxapi_bookmarks', $bookmark);
            
            return [
                'success' => true,
                'bookmarkid' => $id
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to save bookmark: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Returns description of method result value
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Success status'),
            'bookmarkid' => new external_value(PARAM_INT, 'Bookmark ID', VALUE_OPTIONAL),
            'error' => new external_value(PARAM_TEXT, 'Error message', VALUE_OPTIONAL)
        ]);
    }
}