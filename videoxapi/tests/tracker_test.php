<?php
/**
 * Unit tests for Tracker class
 *
 * @package    mod_videoxapi
 * @category   test
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoxapi;

use advanced_testcase;
use mod_videoxapi\xapi\Tracker;

/**
 * Test Tracker functionality
 *
 * @package    mod_videoxapi
 * @category   test
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tracker_test extends advanced_testcase {

    /** @var Tracker Tracker instance */
    private $tracker;

    /**
     * Set up test fixtures
     */
    protected function setUp(): void {
        $this->resetAfterTest();

        $this->tracker = new Tracker(
            'https://example.com/xapi',
            'testuser',
            'testpass',
            'basic',
            2, // Max retries.
            5  // Timeout.
        );
    }    /**
     * Test statement queuing
     */
    public function test_queue_statement() {
        global $DB;

        $statement = [
            'actor' => ['mbox' => 'mailto:test@example.com'],
            'verb' => ['id' => 'http://adlnet.gov/expapi/verbs/played'],
            'object' => ['id' => 'http://example.com/video/1']
        ];

        $result = $this->tracker->queueStatement($statement);
        $this->assertTrue($result);

        // Check database record.
        $records = $DB->get_records('videoxapi_statements');
        $this->assertCount(1, $records);

        $record = reset($records);
        $this->assertEquals(0, $record->status); // Pending.
        $this->assertEquals(0, $record->attempts);
        $this->assertNotEmpty($record->statement);

        $decodedStatement = json_decode($record->statement, true);
        $this->assertEquals($statement, $decodedStatement);
    }

    /**
     * Test queue statistics
     */
    public function test_queue_stats() {
        global $DB;

        // Insert test records.
        $DB->insert_record('videoxapi_statements', [
            'statement' => '{}',
            'status' => 0,
            'attempts' => 0,
            'timecreated' => time(),
            'timemodified' => time()
        ]);

        $DB->insert_record('videoxapi_statements', [
            'statement' => '{}',
            'status' => 1,
            'attempts' => 1,
            'timecreated' => time(),
            'timemodified' => time()
        ]);

        $DB->insert_record('videoxapi_statements', [
            'statement' => '{}',
            'status' => 2,
            'attempts' => 3,
            'timecreated' => time(),
            'timemodified' => time()
        ]);

        $stats = $this->tracker->getQueueStats();

        $this->assertEquals(1, $stats['pending']);
        $this->assertEquals(1, $stats['sent']);
        $this->assertEquals(1, $stats['failed']);
        $this->assertEquals(3, $stats['total']);
    }    /**
     * Test queue cleanup
     */
    public function test_cleanup_queue() {
        global $DB;

        $oldTime = time() - (40 * 24 * 60 * 60); // 40 days ago.
        $recentTime = time() - (10 * 24 * 60 * 60); // 10 days ago.

        // Insert old processed records.
        $DB->insert_record('videoxapi_statements', [
            'statement' => '{}',
            'status' => 1,
            'attempts' => 1,
            'timecreated' => $oldTime,
            'timemodified' => $oldTime
        ]);

        $DB->insert_record('videoxapi_statements', [
            'statement' => '{}',
            'status' => 2,
            'attempts' => 3,
            'timecreated' => $oldTime,
            'timemodified' => $oldTime
        ]);

        // Insert recent processed record.
        $DB->insert_record('videoxapi_statements', [
            'statement' => '{}',
            'status' => 1,
            'attempts' => 1,
            'timecreated' => $recentTime,
            'timemodified' => $recentTime
        ]);

        // Insert pending record.
        $DB->insert_record('videoxapi_statements', [
            'statement' => '{}',
            'status' => 0,
            'attempts' => 0,
            'timecreated' => $oldTime,
            'timemodified' => $oldTime
        ]);

        $deleted = $this->tracker->cleanupQueue(30);
        $this->assertEquals(2, $deleted); // Only old processed records.

        $remaining = $DB->count_records('videoxapi_statements');
        $this->assertEquals(2, $remaining); // Recent processed + pending.
    }

    /**
     * Test invalid JSON handling in queue processing
     */
    public function test_process_queue_invalid_json() {
        global $DB;

        // Insert record with invalid JSON.
        $DB->insert_record('videoxapi_statements', [
            'statement' => 'invalid json',
            'status' => 0,
            'attempts' => 0,
            'timecreated' => time(),
            'timemodified' => time()
        ]);

        $results = $this->tracker->processQueue();

        $this->assertEquals(1, $results['processed']);
        $this->assertEquals(0, $results['successful']);
        $this->assertEquals(1, $results['failed']);

        // Check that record was marked as failed.
        $record = $DB->get_record('videoxapi_statements', ['statement' => 'invalid json']);
        $this->assertEquals(2, $record->status); // Failed.
        $this->assertStringContains('Invalid JSON', $record->error_message);
    }
}