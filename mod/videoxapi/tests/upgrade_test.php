<?php
/**
 * Unit tests for videoxapi database upgrade processes
 *
 * @package    mod_videoxapi
 * @category   test
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoxapi;

use advanced_testcase;

/**
 * Test database upgrade functionality
 *
 * @package    mod_videoxapi
 * @category   test
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class upgrade_test extends advanced_testcase {

    /**
     * Test upgrade function exists and is callable
     */
    public function test_upgrade_function_exists() {
        global $CFG;

        require_once($CFG->dirroot . '/mod/videoxapi/db/upgrade.php');

        $this->assertTrue(function_exists('xmldb_videoxapi_upgrade'));
    }    /**
     * Test upgrade function returns true for current version
     */
    public function test_upgrade_returns_true() {
        global $CFG;

        $this->resetAfterTest();

        require_once($CFG->dirroot . '/mod/videoxapi/db/upgrade.php');

        // Test with current version - should return true without changes.
        $result = xmldb_videoxapi_upgrade(2024011502);
        $this->assertTrue($result);
    }

    /**
     * Test upgrade from old version creates all tables
     */
    public function test_upgrade_from_old_version() {
        global $DB, $CFG;

        $this->resetAfterTest();

        require_once($CFG->dirroot . '/mod/videoxapi/db/upgrade.php');

        $dbman = $DB->get_manager();

        // Drop tables if they exist to simulate old version.
        $tables = ['videoxapi_statements', 'videoxapi_bookmarks', 'videoxapi'];
        foreach ($tables as $tablename) {
            $table = new \xmldb_table($tablename);
            if ($dbman->table_exists($table)) {
                $dbman->drop_table($table);
            }
        }

        // Run upgrade from very old version.
        $result = xmldb_videoxapi_upgrade(2024011400);
        $this->assertTrue($result);

        // Verify all tables were created.
        $this->assertTrue($dbman->table_exists('videoxapi'));
        $this->assertTrue($dbman->table_exists('videoxapi_bookmarks'));
        $this->assertTrue($dbman->table_exists('videoxapi_statements'));
    }

    /**
     * Test incremental upgrades work correctly
     */
    public function test_incremental_upgrades() {
        global $DB, $CFG;

        $this->resetAfterTest();

        require_once($CFG->dirroot . '/mod/videoxapi/db/upgrade.php');

        $dbman = $DB->get_manager();

        // Drop all tables to start fresh.
        $tables = ['videoxapi_statements', 'videoxapi_bookmarks', 'videoxapi'];
        foreach ($tables as $tablename) {
            $table = new \xmldb_table($tablename);
            if ($dbman->table_exists($table)) {
                $dbman->drop_table($table);
            }
        }

        // Test upgrade to first version.
        $result = xmldb_videoxapi_upgrade(2024011499);
        $this->assertTrue($result);
        $this->assertTrue($dbman->table_exists('videoxapi'));
        $this->assertFalse($dbman->table_exists('videoxapi_bookmarks'));

        // Test upgrade to second version.
        $result = xmldb_videoxapi_upgrade(2024011500);
        $this->assertTrue($result);
        $this->assertTrue($dbman->table_exists('videoxapi_bookmarks'));
        $this->assertFalse($dbman->table_exists('videoxapi_statements'));

        // Test upgrade to final version.
        $result = xmldb_videoxapi_upgrade(2024011501);
        $this->assertTrue($result);
        $this->assertTrue($dbman->table_exists('videoxapi_statements'));
    }
}