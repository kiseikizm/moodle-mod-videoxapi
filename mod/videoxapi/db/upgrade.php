<?php
/**
 * Database upgrade script for mod_videoxapi
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute videoxapi upgrade from the given old version
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_videoxapi_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Moodle v5.0.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2024011500) {
        // Define table videoxapi to be created.
        $table = new xmldb_table('videoxapi');

        // Adding fields to table videoxapi.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('intro', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('introformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('video_source', XMLDB_TYPE_CHAR, '10', null, null, null, null);
        $table->add_field('video_url', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('video_width', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '640');
        $table->add_field('video_height', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '360');
        $table->add_field('enable_bookmarks', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('xapi_tracking_level', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '3');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table videoxapi.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('course', XMLDB_KEY_FOREIGN, ['course'], 'course', ['id']);

        // Conditionally launch create table for videoxapi.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Videoxapi savepoint reached.
        upgrade_mod_savepoint(true, 2024011500, 'videoxapi');
    }    if ($oldversion < 2024011501) {
        // Define table videoxapi_bookmarks to be created.
        $table = new xmldb_table('videoxapi_bookmarks');

        // Adding fields to table videoxapi_bookmarks.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('videoxapi', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timestamp', XMLDB_TYPE_NUMBER, '10,3', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table videoxapi_bookmarks.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('videoxapi', XMLDB_KEY_FOREIGN, ['videoxapi'], 'videoxapi', ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_key('unique_user_timestamp', XMLDB_KEY_UNIQUE, ['videoxapi', 'userid', 'timestamp']);

        // Adding indexes to table videoxapi_bookmarks.
        $table->add_index('videoxapi_userid', XMLDB_INDEX_NOTUNIQUE, ['videoxapi', 'userid']);

        // Conditionally launch create table for videoxapi_bookmarks.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Videoxapi savepoint reached.
        upgrade_mod_savepoint(true, 2024011501, 'videoxapi');
    }

    if ($oldversion < 2024011502) {
        // Define table videoxapi_statements to be created.
        $table = new xmldb_table('videoxapi_statements');

        // Adding fields to table videoxapi_statements.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('statement', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('attempts', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('error_message', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table videoxapi_statements.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table videoxapi_statements.
        $table->add_index('status_attempts', XMLDB_INDEX_NOTUNIQUE, ['status', 'attempts']);
        $table->add_index('timecreated', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);

        // Conditionally launch create table for videoxapi_statements.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Videoxapi savepoint reached.
        upgrade_mod_savepoint(true, 2024011502, 'videoxapi');
    }

    return true;
}