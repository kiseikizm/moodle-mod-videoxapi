<?php
/**
 * Unit tests for videoxapi capabilities
 *
 * @package    mod_videoxapi
 * @category   test
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoxapi;

use advanced_testcase;

/**
 * Test capability definitions and permissions
 *
 * @package    mod_videoxapi
 * @category   test
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class capabilities_test extends advanced_testcase {

    /**
     * Test that all required capabilities are defined
     */
    public function test_capabilities_defined() {
        global $CFG;

        require_once($CFG->dirroot . '/mod/videoxapi/db/access.php');

        $this->assertArrayHasKey('mod/videoxapi:addinstance', $capabilities);
        $this->assertArrayHasKey('mod/videoxapi:view', $capabilities);
        $this->assertArrayHasKey('mod/videoxapi:createbookmarks', $capabilities);
        $this->assertArrayHasKey('mod/videoxapi:viewownbookmarks', $capabilities);
        $this->assertArrayHasKey('mod/videoxapi:viewallbookmarks', $capabilities);
        $this->assertArrayHasKey('mod/videoxapi:deleteownbookmarks', $capabilities);
        $this->assertArrayHasKey('mod/videoxapi:deleteanybookmarks', $capabilities);
        $this->assertArrayHasKey('mod/videoxapi:viewreports', $capabilities);
        $this->assertArrayHasKey('mod/videoxapi:exportreports', $capabilities);
        $this->assertArrayHasKey('mod/videoxapi:configurexapi', $capabilities);
    }    /**
     * Test capability structure and required fields
     */
    public function test_capability_structure() {
        global $CFG;

        require_once($CFG->dirroot . '/mod/videoxapi/db/access.php');

        foreach ($capabilities as $capname => $capdef) {
            // Test required fields exist.
            $this->assertArrayHasKey('captype', $capdef, "Capability $capname missing captype");
            $this->assertArrayHasKey('contextlevel', $capdef, "Capability $capname missing contextlevel");
            $this->assertArrayHasKey('archetypes', $capdef, "Capability $capname missing archetypes");

            // Test captype is valid.
            $this->assertContains($capdef['captype'], ['read', 'write'], 
                "Capability $capname has invalid captype");

            // Test contextlevel is valid.
            $validcontexts = [CONTEXT_SYSTEM, CONTEXT_USER, CONTEXT_COURSECAT, 
                             CONTEXT_COURSE, CONTEXT_MODULE, CONTEXT_BLOCK];
            $this->assertContains($capdef['contextlevel'], $validcontexts,
                "Capability $capname has invalid contextlevel");

            // Test archetypes is array.
            $this->assertIsArray($capdef['archetypes'], 
                "Capability $capname archetypes must be array");
        }
    }

    /**
     * Test specific capability permissions for different roles
     */
    public function test_role_permissions() {
        global $CFG;

        require_once($CFG->dirroot . '/mod/videoxapi/db/access.php');

        // Test addinstance capability - only editing teachers and managers.
        $addinstance = $capabilities['mod/videoxapi:addinstance'];
        $this->assertEquals(CAP_ALLOW, $addinstance['archetypes']['editingteacher']);
        $this->assertEquals(CAP_ALLOW, $addinstance['archetypes']['manager']);
        $this->assertArrayNotHasKey('student', $addinstance['archetypes']);

        // Test view capability - all roles should have access.
        $view = $capabilities['mod/videoxapi:view'];
        $this->assertEquals(CAP_ALLOW, $view['archetypes']['student']);
        $this->assertEquals(CAP_ALLOW, $view['archetypes']['teacher']);
        $this->assertEquals(CAP_ALLOW, $view['archetypes']['editingteacher']);

        // Test bookmark creation - students and teachers.
        $createbookmarks = $capabilities['mod/videoxapi:createbookmarks'];
        $this->assertEquals(CAP_ALLOW, $createbookmarks['archetypes']['student']);
        $this->assertEquals(CAP_ALLOW, $createbookmarks['archetypes']['teacher']);

        // Test view all bookmarks - only teachers and managers.
        $viewall = $capabilities['mod/videoxapi:viewallbookmarks'];
        $this->assertArrayNotHasKey('student', $viewall['archetypes']);
        $this->assertEquals(CAP_ALLOW, $viewall['archetypes']['teacher']);

        // Test delete any bookmarks - only editing teachers and managers.
        $deleteany = $capabilities['mod/videoxapi:deleteanybookmarks'];
        $this->assertArrayNotHasKey('student', $deleteany['archetypes']);
        $this->assertArrayNotHasKey('teacher', $deleteany['archetypes']);
        $this->assertEquals(CAP_ALLOW, $deleteany['archetypes']['editingteacher']);
    }

    /**
     * Test risk levels are appropriate
     */
    public function test_risk_levels() {
        global $CFG;

        require_once($CFG->dirroot . '/mod/videoxapi/db/access.php');

        // Test high-risk capabilities have appropriate risk flags.
        $addinstance = $capabilities['mod/videoxapi:addinstance'];
        $this->assertEquals(RISK_XSS, $addinstance['riskbitmask']);

        $deleteany = $capabilities['mod/videoxapi:deleteanybookmarks'];
        $this->assertEquals(RISK_DATALOSS, $deleteany['riskbitmask']);

        $configurexapi = $capabilities['mod/videoxapi:configurexapi'];
        $this->assertEquals(RISK_CONFIG, $configurexapi['riskbitmask']);
    }
}