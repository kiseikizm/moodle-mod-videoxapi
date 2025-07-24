<?php
/**
 * Unit tests for StatementBuilder class
 *
 * @package    mod_videoxapi
 * @category   test
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoxapi;

use advanced_testcase;
use mod_videoxapi\xapi\StatementBuilder;

/**
 * Test StatementBuilder functionality
 *
 * @package    mod_videoxapi
 * @category   test
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class statement_builder_test extends advanced_testcase {

    /** @var object Test user */
    private $user;

    /** @var object Test course */
    private $course;

    /** @var object Test videoxapi instance */
    private $videoxapi;

    /** @var StatementBuilder Statement builder instance */
    private $builder;

    /**
     * Set up test fixtures
     */
    protected function setUp(): void {
        $this->resetAfterTest();

        // Create test data.
        $this->course = $this->getDataGenerator()->create_course();
        $this->user = $this->getDataGenerator()->create_user([
            'email' => 'test@example.com',
            'firstname' => 'Test',
            'lastname' => 'User'
        ]);

        // Create videoxapi instance.
        $this->videoxapi = (object) [
            'id' => 1,
            'name' => 'Test Video Activity',
            'intro' => 'Test video description',
            'video_url' => 'https://example.com/video.mp4'
        ];

        $this->builder = new StatementBuilder($this->user, $this->videoxapi, $this->course);
    }    /**
     * Test play statement generation
     */
    public function test_build_play_statement() {
        $statement = $this->builder->buildPlayStatement(45.5, 300.0);

        // Test basic structure.
        $this->assertEquals('1.0.3', $statement['version']);
        $this->assertArrayHasKey('timestamp', $statement);
        $this->assertArrayHasKey('actor', $statement);
        $this->assertArrayHasKey('verb', $statement);
        $this->assertArrayHasKey('object', $statement);
        $this->assertArrayHasKey('result', $statement);
        $this->assertArrayHasKey('context', $statement);

        // Test verb.
        $this->assertEquals('https://w3id.org/xapi/video/verbs/played', $statement['verb']['id']);
        $this->assertEquals('played', $statement['verb']['display']['en-US']);

        // Test result extensions.
        $extensions = $statement['result']['extensions'];
        $this->assertEquals(45.5, $extensions['https://w3id.org/xapi/video/extensions/time']);
        $this->assertEquals(300.0, $extensions['https://w3id.org/xapi/video/extensions/length']);
        $this->assertEquals(0.15166666666667, $extensions['https://w3id.org/xapi/video/extensions/progress'], '', 0.001);

        // Test actor.
        $this->assertEquals('Agent', $statement['actor']['objectType']);
        $this->assertEquals('mailto:test@example.com', $statement['actor']['mbox']);
        $this->assertEquals('Test User', $statement['actor']['name']);

        // Test object.
        $this->assertEquals('Activity', $statement['object']['objectType']);
        $this->assertStringContains('/mod/videoxapi/view.php?id=1', $statement['object']['id']);
        $this->assertEquals('Test Video Activity', $statement['object']['definition']['name']['en-US']);
    }

    /**
     * Test pause statement generation
     */
    public function test_build_pause_statement() {
        $statement = $this->builder->buildPauseStatement(120.0, 300.0);

        // Test verb.
        $this->assertEquals('https://w3id.org/xapi/video/verbs/paused', $statement['verb']['id']);
        $this->assertEquals('paused', $statement['verb']['display']['en-US']);

        // Test result extensions.
        $extensions = $statement['result']['extensions'];
        $this->assertEquals(120.0, $extensions['https://w3id.org/xapi/video/extensions/time']);
        $this->assertEquals(300.0, $extensions['https://w3id.org/xapi/video/extensions/length']);
        $this->assertEquals(0.4, $extensions['https://w3id.org/xapi/video/extensions/progress']);
    }    /**
     * Test seek statement generation
     */
    public function test_build_seek_statement() {
        $statement = $this->builder->buildSeekStatement(60.0, 180.0, 300.0);

        // Test verb.
        $this->assertEquals('https://w3id.org/xapi/video/verbs/seeked', $statement['verb']['id']);
        $this->assertEquals('seeked', $statement['verb']['display']['en-US']);

        // Test result extensions.
        $extensions = $statement['result']['extensions'];
        $this->assertEquals(60.0, $extensions['https://w3id.org/xapi/video/extensions/time-from']);
        $this->assertEquals(180.0, $extensions['https://w3id.org/xapi/video/extensions/time-to']);
        $this->assertEquals(300.0, $extensions['https://w3id.org/xapi/video/extensions/length']);
        $this->assertEquals(0.6, $extensions['https://w3id.org/xapi/video/extensions/progress']);
    }

    /**
     * Test completed statement generation
     */
    public function test_build_completed_statement() {
        $statement = $this->builder->buildCompletedStatement(300.0, 300.0, 0.95);

        // Test verb.
        $this->assertEquals('http://adlnet.gov/expapi/verbs/completed', $statement['verb']['id']);
        $this->assertEquals('completed', $statement['verb']['display']['en-US']);

        // Test result.
        $result = $statement['result'];
        $this->assertTrue($result['completion']);
        $this->assertTrue($result['success']); // 95% > 80% threshold.
        $this->assertEquals(0.95, $result['score']['scaled']);
        $this->assertEquals(95, $result['score']['raw']);
        $this->assertEquals(0, $result['score']['min']);
        $this->assertEquals(100, $result['score']['max']);

        // Test extensions.
        $extensions = $result['extensions'];
        $this->assertEquals(300.0, $extensions['https://w3id.org/xapi/video/extensions/time']);
        $this->assertEquals(300.0, $extensions['https://w3id.org/xapi/video/extensions/length']);
        $this->assertEquals(0.95, $extensions['https://w3id.org/xapi/video/extensions/progress']);
    }    /**
     * Test bookmark statement generation
     */
    public function test_build_bookmark_statement() {
        $statement = $this->builder->buildBookmarkStatement(150.0, 'Important Point', 'Key learning moment', 300.0);

        // Test verb.
        $this->assertEquals('https://w3id.org/xapi/adb/verbs/bookmarked', $statement['verb']['id']);
        $this->assertEquals('bookmarked', $statement['verb']['display']['en-US']);

        // Test result.
        $result = $statement['result'];
        $this->assertEquals('Important Point: Key learning moment', $result['response']);

        // Test extensions.
        $extensions = $result['extensions'];
        $this->assertEquals(150.0, $extensions['https://w3id.org/xapi/video/extensions/time']);
        $this->assertEquals(300.0, $extensions['https://w3id.org/xapi/video/extensions/length']);
        $this->assertEquals(0.5, $extensions['https://w3id.org/xapi/video/extensions/progress']);
    }

    /**
     * Test statement validation
     */
    public function test_statement_validation() {
        $validStatement = $this->builder->buildPlayStatement(45.5, 300.0);
        $this->assertTrue($this->builder->validateStatement($validStatement));

        // Test invalid statement - missing actor.
        $invalidStatement = $validStatement;
        unset($invalidStatement['actor']);
        $this->assertFalse($this->builder->validateStatement($invalidStatement));

        // Test invalid statement - missing verb.
        $invalidStatement = $validStatement;
        unset($invalidStatement['verb']);
        $this->assertFalse($this->builder->validateStatement($invalidStatement));

        // Test invalid statement - missing object.
        $invalidStatement = $validStatement;
        unset($invalidStatement['object']);
        $this->assertFalse($this->builder->validateStatement($invalidStatement));
    }

    /**
     * Test actor building with different user configurations
     */
    public function test_actor_building_variations() {
        // Test user without email.
        $userNoEmail = $this->getDataGenerator()->create_user([
            'email' => '',
            'username' => 'testuser',
            'firstname' => 'John',
            'lastname' => 'Doe'
        ]);

        $builderNoEmail = new StatementBuilder($userNoEmail, $this->videoxapi, $this->course);
        $statement = $builderNoEmail->buildPlayStatement(0, 100);

        // Should use account instead of mbox.
        $this->assertArrayNotHasKey('mbox', $statement['actor']);
        $this->assertArrayHasKey('account', $statement['actor']);
        $this->assertEquals('testuser', $statement['actor']['account']['name']);
        $this->assertEquals('John Doe', $statement['actor']['name']);
    }
}