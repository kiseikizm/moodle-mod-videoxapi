<?php
/**
 * All tests for mod_videoxapi
 *
 * @package    mod_videoxapi
 * @category   test
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Test suite for all videoxapi tests
 */
class mod_videoxapi_all_tests extends PHPUnit\Framework\TestSuite {

    /**
     * Build the test suite
     * @return PHPUnit\Framework\TestSuite
     */
    public static function suite() {
        $suite = new self('mod_videoxapi');
        
        // Add all test classes
        $suite->addTestSuite('mod_videoxapi\database_test');
        $suite->addTestSuite('mod_videoxapi\upgrade_test');
        $suite->addTestSuite('mod_videoxapi\capabilities_test');
        $suite->addTestSuite('mod_videoxapi\mod_form_test');
        $suite->addTestSuite('mod_videoxapi\statement_builder_test');
        $suite->addTestSuite('mod_videoxapi\tracker_test');
        $suite->addTestSuite('mod_videoxapi\config_manager_test');
        
        return $suite;
    }
}