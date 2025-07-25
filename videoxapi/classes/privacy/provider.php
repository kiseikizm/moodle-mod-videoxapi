<?php
/**
 * Privacy Subsystem implementation for mod_videoxapi
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoxapi\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\deletion_criteria;
use core_privacy\local\request\helper;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for mod_videoxapi implementing null_provider.
 *
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Return the fields which contain personal data.
     *
     * @param collection $items a reference to the collection to use to store the metadata.
     * @return collection the updated collection of metadata items.
     */
    public static function get_metadata(collection $items): collection {
        $items->add_database_table(
            'videoxapi_bookmarks',
            [
                'userid' => 'privacy:metadata:videoxapi_bookmarks:userid',
                'timestamp' => 'privacy:metadata:videoxapi_bookmarks:timestamp',
                'title' => 'privacy:metadata:videoxapi_bookmarks:title',
                'description' => 'privacy:metadata:videoxapi_bookmarks:description',
                'timecreated' => 'privacy:metadata:videoxapi_bookmarks:timecreated',
            ],
            'privacy:metadata:videoxapi_bookmarks'
        );

        $items->add_external_location_link(
            'lrs',
            [
                'userid' => 'privacy:metadata:lrs:userid',
                'timestamp' => 'privacy:metadata:lrs:timestamp',
                'videointeraction' => 'privacy:metadata:lrs:videointeraction',
            ],
            'privacy:metadata:lrs'
        );

        return $items;
    }    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid the userid.
     * @return contextlist the list of contexts containing user info for the user.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        // Find contexts where user has bookmarks.
        $sql = "SELECT c.id
                FROM {context} c
                INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                INNER JOIN {videoxapi} v ON v.id = cm.instance
                INNER JOIN {videoxapi_bookmarks} vb ON vb.videoxapi = v.id
                WHERE vb.userid = :userid";

        $params = [
            'contextlevel' => CONTEXT_MODULE,
            'modname' => 'videoxapi',
            'userid' => $userid,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        // Find users who have bookmarks in this context.
        $sql = "SELECT vb.userid
                FROM {course_modules} cm
                JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                JOIN {videoxapi} v ON v.id = cm.instance
                JOIN {videoxapi_bookmarks} vb ON vb.videoxapi = v.id
                WHERE cm.id = :cmid";

        $params = [
            'cmid' => $context->instanceid,
            'modname' => 'videoxapi',
        ];

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export personal data for the given approved_contextlist.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for export.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT cm.id AS cmid, vb.*
                FROM {context} c
                INNER JOIN {course_modules} cm ON cm.id = c.instanceid
                INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                INNER JOIN {videoxapi} v ON v.id = cm.instance
                INNER JOIN {videoxapi_bookmarks} vb ON vb.videoxapi = v.id
                WHERE c.id {$contextsql} AND vb.userid = :userid
                ORDER BY cm.id, vb.timestamp";

        $params = $contextparams + [
            'modname' => 'videoxapi',
            'userid' => $user->id,
        ];

        $bookmarks = $DB->get_recordset_sql($sql, $params);

        foreach ($bookmarks as $bookmark) {
            $context = \context_module::instance($bookmark->cmid);
            
            $bookmarkdata = [
                'timestamp' => $bookmark->timestamp,
                'title' => $bookmark->title,
                'description' => $bookmark->description,
                'timecreated' => \core_privacy\local\request\transform::datetime($bookmark->timecreated),
            ];

            writer::with_context($context)->export_data(
                [get_string('bookmarks', 'videoxapi')],
                (object) $bookmarkdata
            );
        }

        $bookmarks->close();
    }    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context the context to delete in.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }

        if ($cm = get_coursemodule_from_id('videoxapi', $context->instanceid)) {
            $DB->delete_records('videoxapi_bookmarks', ['videoxapi' => $cm->instance]);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist a list of contexts approved for deletion.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }

            if ($cm = get_coursemodule_from_id('videoxapi', $context->instanceid)) {
                $DB->delete_records('videoxapi_bookmarks', [
                    'videoxapi' => $cm->instance,
                    'userid' => $userid,
                ]);
            }
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof \context_module) {
            return;
        }

        $cm = get_coursemodule_from_id('videoxapi', $context->instanceid);
        if (!$cm) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        list($usersql, $userparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        $select = "videoxapi = :videoxapi AND userid {$usersql}";
        $params = ['videoxapi' => $cm->instance] + $userparams;

        $DB->delete_records_select('videoxapi_bookmarks', $select, $params);
    }
}