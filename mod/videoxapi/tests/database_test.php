<?php
/**
 * Unit tests for videoxapi database schema and operations
 *
 * @package    mod_videoxapi
 * @category   test
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoxapi;

use advanced_testcase;

/**
 * Test database schema creation and operations
 *
 * @package    mod_videoxapi
 * @category   test
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class database_test extends advanced_testcase {

    /**
     * Test that all required tables exist after installation
     */
    public function test_tables_exist() {
        global $DB;

        $this->resetAfterTest();

        $dbman = $DB->get_manager();

        // Check that main videoxapi table exists.
        $this->assertTrue($dbman->table_exists('videoxapi'));

        // Check that bookmarks table exists.
        $this->assertTrue($dbman->table_exists('videoxapi_bookmarks'));

        // Check that statements queue table exists.
        $this->assertTrue($dbman->table_exists('videoxapi_statements'));
    }    /**
     * Test videoxapi table structure and constraints
     */
    public function test_videoxapi_table_structure() {
        global $DB;

        $this->resetAfterTest();

        $dbman = $DB->get_manager();
        $table = new \xmldb_table('videoxapi');

        // Test required fields exist.
        $this->assertTrue($dbman->field_exists($table, 'id'));
        $this->assertTrue($dbman->field_exists($table, 'course'));
        $this->assertTrue($dbman->field_exists($table, 'name'));
        $this->assertTrue($dbman->field_exists($table, 'intro'));
        $this->assertTrue($dbman->field_exists($table, 'introformat'));
        $this->assertTrue($dbman->field_exists($table, 'video_source'));
        $this->assertTrue($dbman->field_exists($table, 'video_url'));
        $this->assertTrue($dbman->field_exists($table, 'video_width'));
        $this->assertTrue($dbman->field_exists($table, 'video_height'));
        $this->assertTrue($dbman->field_exists($table, 'enable_bookmarks'));
        $this->assertTrue($dbman->field_exists($table, 'xapi_tracking_level'));
        $this->assertTrue($dbman->field_exists($table, 'timemodified'));
        $this->assertTrue($dbman->field_exists($table, 'timecreated'));
    }

    /**
     * Test videoxapi_bookmarks table structure and constraints
     */
    public function test_bookmarks_table_structure() {
        global $DB;

        $this->resetAfterTest();

        $dbman = $DB->get_manager();
        $table = new \xmldb_table('videoxapi_bookmarks');

        // Test required fields exist.
        $this->assertTrue($dbman->field_exists($table, 'id'));
        $this->assertTrue($dbman->field_exists($table, 'videoxapi'));
        $this->assertTrue($dbman->field_exists($table, 'userid'));
        $this->assertTrue($dbman->field_exists($table, 'timestamp'));
        $this->assertTrue($dbman->field_exists($table, 'title'));
        $this->assertTrue($dbman->field_exists($table, 'description'));
        $this->assertTrue($dbman->field_exists($table, 'timecreated'));
    }    /**
     * Test videoxapi_statements table structure and constraints
     */
    public function test_statements_table_structure() {
        global $DB;

        $this->resetAfterTest();

        $dbman = $DB->get_manager();
        $table = new \xmldb_table('videoxapi_statements');

        // Test required fields exist.
        $this->assertTrue($dbman->field_exists($table, 'id'));
        $this->assertTrue($dbman->field_exists($table, 'statement'));
        $this->assertTrue($dbman->field_exists($table, 'status'));
        $this->assertTrue($dbman->field_exists($table, 'attempts'));
        $this->assertTrue($dbman->field_exists($table, 'timecreated'));
        $this->assertTrue($dbman->field_exists($table, 'timemodified'));
        $this->assertTrue($dbman->field_exists($table, 'error_message'));
    }

    /**
     * Test basic CRUD operations on videoxapi table
     */
    public function test_videoxapi_crud_operations() {
        global $DB;

        $this->resetAfterTest();

        // Create test course.
        $course = $this->getDataGenerator()->create_course();

        // Test insert operation.
        $record = new \stdClass();
        $record->course = $course->id;
        $record->name = 'Test Video Activity';
        $record->intro = 'Test introduction';
        $record->introformat = 1;
        $record->video_source = 'url';
        $record->video_url = 'https://example.com/video.mp4';
        $record->video_width = 800;
        $record->video_height = 600;
        $record->enable_bookmarks = 1;
        $record->xapi_tracking_level = 3;
        $record->timecreated = time();
        $record->timemodified = time();

        $id = $DB->insert_record('videoxapi', $record);
        $this->assertNotEmpty($id);

        // Test select operation.
        $retrieved = $DB->get_record('videoxapi', ['id' => $id]);
        $this->assertEquals($record->name, $retrieved->name);
        $this->assertEquals($record->course, $retrieved->course);
        $this->assertEquals($record->video_url, $retrieved->video_url);

        // Test update operation.
        $retrieved->name = 'Updated Video Activity';
        $retrieved->timemodified = time();
        $DB->update_record('videoxapi', $retrieved);

        $updated = $DB->get_record('videoxapi', ['id' => $id]);
        $this->assertEquals('Updated Video Activity', $updated->name);

        // Test delete operation.
        $DB->delete_records('videoxapi', ['id' => $id]);
        $this->assertFalse($DB->record_exists('videoxapi', ['id' => $id]));
    }    /**
     * Test bookmark CRUD operations and constraints
     */
    public function test_bookmark_crud_operations() {
        global $DB;

        $this->resetAfterTest();

        // Create test data.
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();

        // Create videoxapi activity.
        $videoxapi = new \stdClass();
        $videoxapi->course = $course->id;
        $videoxapi->name = 'Test Video';
        $videoxapi->intro = 'Test';
        $videoxapi->timecreated = time();
        $videoxapi->timemodified = time();
        $videoxapiid = $DB->insert_record('videoxapi', $videoxapi);

        // Test bookmark insert.
        $bookmark = new \stdClass();
        $bookmark->videoxapi = $videoxapiid;
        $bookmark->userid = $user->id;
        $bookmark->timestamp = 45.500;
        $bookmark->title = 'Important moment';
        $bookmark->description = 'This is a key learning point';
        $bookmark->timecreated = time();

        $bookmarkid = $DB->insert_record('videoxapi_bookmarks', $bookmark);
        $this->assertNotEmpty($bookmarkid);

        // Test unique constraint - should fail with duplicate timestamp.
        $duplicate = clone $bookmark;
        $duplicate->title = 'Duplicate bookmark';

        $this->expectException(\dml_write_exception::class);
        $DB->insert_record('videoxapi_bookmarks', $duplicate);
    }

    /**
     * Test statement queue operations
     */
    public function test_statement_queue_operations() {
        global $DB;

        $this->resetAfterTest();

        // Test statement insert.
        $statement = new \stdClass();
        $statement->statement = json_encode([
            'actor' => ['mbox' => 'mailto:test@example.com'],
            'verb' => ['id' => 'http://adlnet.gov/expapi/verbs/played'],
            'object' => ['id' => 'http://example.com/video/1']
        ]);
        $statement->status = 0; // Pending.
        $statement->attempts = 0;
        $statement->timecreated = time();
        $statement->timemodified = time();

        $id = $DB->insert_record('videoxapi_statements', $statement);
        $this->assertNotEmpty($id);

        // Test status update.
        $DB->set_field('videoxapi_statements', 'status', 1, ['id' => $id]); // Sent.
        $DB->set_field('videoxapi_statements', 'attempts', 1, ['id' => $id]);

        $updated = $DB->get_record('videoxapi_statements', ['id' => $id]);
        $this->assertEquals(1, $updated->status);
        $this->assertEquals(1, $updated->attempts);

        // Test error handling.
        $DB->set_field('videoxapi_statements', 'status', 2, ['id' => $id]); // Failed.
        $DB->set_field('videoxapi_statements', 'error_message', 'Connection timeout', ['id' => $id]);

        $failed = $DB->get_record('videoxapi_statements', ['id' => $id]);
        $this->assertEquals(2, $failed->status);
        $this->assertEquals('Connection timeout', $failed->error_message);
    }
}