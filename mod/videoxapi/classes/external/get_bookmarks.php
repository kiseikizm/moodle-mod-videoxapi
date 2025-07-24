<?php
/**
 * External API for getting bookmarks
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoxapi\external;

use external_api;
use external_function_parameters;
use external_value;
use external_multiple_structure;
use external_single_structure;
use context_module;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * External API for getting bookmarks
 */
class get_bookmarks extends external_api {

    /**
     * Returns description of method parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'videoxapiid' => new external_value(PARAM_INT, 'Video xAPI instance ID')
        ]);
    }

    /**
     * Get bookmarks
     */
    public static function execute($videoxapiid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'videoxapiid' => $videoxapiid
        ]);

        // Get videoxapi instance and validate access.
        $videoxapi = $DB->get_record('videoxapi', ['id' => $params['videoxapiid']], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $videoxapi->course], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('videoxapi', $videoxapi->id, $course->id, false, MUST_EXIST);

        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videoxapi:viewownbookmarks', $context);

        $bookmarks = [];

        // Check bookmark permissions.
        $canviewall = has_capability('mod/videoxapi:viewallbookmarks', $context);
        
        if ($canviewall) {
            // Get all bookmarks.
            $sql = "SELECT b.*, u.firstname, u.lastname 
                    FROM {videoxapi_bookmarks} b 
                    JOIN {user} u ON b.userid = u.id 
                    WHERE b.videoxapi = ? 
                    ORDER BY b.timestamp ASC";
            $records = $DB->get_records_sql($sql, [$params['videoxapiid']]);
        } else {
            // Get only own bookmarks.
            $records = $DB->get_records('videoxapi_bookmarks', [
                'videoxapi' => $params['videoxapiid'],
                'userid' => $USER->id
            ], 'timestamp ASC');
        }

        foreach ($records as $record) {
            $bookmark = [
                'id' => $record->id,
                'timestamp' => $record->timestamp,
                'title' => $record->title,
                'description' => $record->description,
                'timecreated' => $record->timecreated,
                'userid' => $record->userid
            ];

            if ($canviewall && isset($record->firstname)) {
                $bookmark['username'] = fullname($record);
            }

            $bookmarks[] = $bookmark;
        }

        return $bookmarks;
    }

    /**
     * Returns description of method result value
     */
    public static function execute_returns() {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Bookmark ID'),
                'timestamp' => new external_value(PARAM_FLOAT, 'Bookmark timestamp'),
                'title' => new external_value(PARAM_TEXT, 'Bookmark title'),
                'description' => new external_value(PARAM_TEXT, 'Bookmark description'),
                'timecreated' => new external_value(PARAM_INT, 'Time created'),
                'userid' => new external_value(PARAM_INT, 'User ID'),
                'username' => new external_value(PARAM_TEXT, 'User full name', VALUE_OPTIONAL)
            ])
        );
    }
}