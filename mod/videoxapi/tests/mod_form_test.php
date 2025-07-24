<?php
/**
 * Unit tests for videoxapi mod_form validation
 *
 * @package    mod_videoxapi
 * @category   test
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoxapi;

use advanced_testcase;

/**
 * Test mod_form validation logic
 *
 * @package    mod_videoxapi
 * @category   test
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_form_test extends advanced_testcase {

    /**
     * Test form validation with valid URL
     */
    public function test_valid_url_validation() {
        global $CFG;

        $this->resetAfterTest();

        require_once($CFG->dirroot . '/mod/videoxapi/mod_form.php');

        $course = $this->getDataGenerator()->create_course();

        // Create form instance.
        $form = new \mod_videoxapi_mod_form(null, ['course' => $course]);

        // Test data with valid URL.
        $data = [
            'name' => 'Test Video Activity',
            'intro' => 'Test introduction',
            'video_source' => 'url',
            'video_url' => 'https://example.com/video.mp4',
            'video_width' => 640,
            'video_height' => 360,
            'xapi_tracking_level' => 3
        ];

        $errors = $form->validation($data, []);

        // Should not have video_url error for valid format.
        $this->assertArrayNotHasKey('video_url', $errors);
    }    /**
     * Test form validation with invalid URL
     */
    public function test_invalid_url_validation() {
        global $CFG;

        $this->resetAfterTest();

        require_once($CFG->dirroot . '/mod/videoxapi/mod_form.php');

        $course = $this->getDataGenerator()->create_course();
        $form = new \mod_videoxapi_mod_form(null, ['course' => $course]);

        // Test data with invalid URL.
        $data = [
            'name' => 'Test Video Activity',
            'video_source' => 'url',
            'video_url' => 'not-a-valid-url',
            'video_width' => 640,
            'video_height' => 360
        ];

        $errors = $form->validation($data, []);

        // Should have video_url error for invalid format.
        $this->assertArrayHasKey('video_url', $errors);
    }

    /**
     * Test form validation with empty URL when URL source is selected
     */
    public function test_empty_url_validation() {
        global $CFG;

        $this->resetAfterTest();

        require_once($CFG->dirroot . '/mod/videoxapi/mod_form.php');

        $course = $this->getDataGenerator()->create_course();
        $form = new \mod_videoxapi_mod_form(null, ['course' => $course]);

        // Test data with empty URL.
        $data = [
            'name' => 'Test Video Activity',
            'video_source' => 'url',
            'video_url' => '',
            'video_width' => 640,
            'video_height' => 360
        ];

        $errors = $form->validation($data, []);

        // Should have video_url error for empty URL.
        $this->assertArrayHasKey('video_url', $errors);
    }    /**
     * Test video dimension validation
     */
    public function test_video_dimension_validation() {
        global $CFG;

        $this->resetAfterTest();

        require_once($CFG->dirroot . '/mod/videoxapi/mod_form.php');

        $course = $this->getDataGenerator()->create_course();
        $form = new \mod_videoxapi_mod_form(null, ['course' => $course]);

        // Test data with invalid dimensions.
        $data = [
            'name' => 'Test Video Activity',
            'video_source' => 'url',
            'video_url' => 'https://example.com/video.mp4',
            'video_width' => 50, // Too small.
            'video_height' => 2000 // Too large.
        ];

        $errors = $form->validation($data, []);

        // Should have dimension errors.
        $this->assertArrayHasKey('video_width', $errors);
        $this->assertArrayHasKey('video_height', $errors);
    }

    /**
     * Test valid video dimensions
     */
    public function test_valid_video_dimensions() {
        global $CFG;

        $this->resetAfterTest();

        require_once($CFG->dirroot . '/mod/videoxapi/mod_form.php');

        $course = $this->getDataGenerator()->create_course();
        $form = new \mod_videoxapi_mod_form(null, ['course' => $course]);

        // Test data with valid dimensions.
        $data = [
            'name' => 'Test Video Activity',
            'video_source' => 'url',
            'video_url' => 'https://example.com/video.mp4',
            'video_width' => 800,
            'video_height' => 600
        ];

        $errors = $form->validation($data, []);

        // Should not have dimension errors.
        $this->assertArrayNotHasKey('video_width', $errors);
        $this->assertArrayNotHasKey('video_height', $errors);
    }    /**
     * Test video file extension validation
     */
    public function test_video_file_extension_validation() {
        global $CFG;

        $this->resetAfterTest();

        require_once($CFG->dirroot . '/mod/videoxapi/mod_form.php');

        $course = $this->getDataGenerator()->create_course();
        $form = new \mod_videoxapi_mod_form(null, ['course' => $course]);

        // Test data with non-video file extension.
        $data = [
            'name' => 'Test Video Activity',
            'video_source' => 'url',
            'video_url' => 'https://example.com/document.pdf',
            'video_width' => 640,
            'video_height' => 360
        ];

        $errors = $form->validation($data, []);

        // Should have video_url error for invalid file format.
        $this->assertArrayHasKey('video_url', $errors);
    }

    /**
     * Test completion rule validation
     */
    public function test_completion_rule_enabled() {
        global $CFG;

        $this->resetAfterTest();

        require_once($CFG->dirroot . '/mod/videoxapi/mod_form.php');

        $course = $this->getDataGenerator()->create_course();
        $form = new \mod_videoxapi_mod_form(null, ['course' => $course]);

        // Test with completion enabled.
        $data = ['completionwatched' => 1];
        $this->assertTrue($form->completion_rule_enabled($data));

        // Test with completion disabled.
        $data = ['completionwatched' => 0];
        $this->assertFalse($form->completion_rule_enabled($data));

        // Test with completion not set.
        $data = [];
        $this->assertFalse($form->completion_rule_enabled($data));
    }
}