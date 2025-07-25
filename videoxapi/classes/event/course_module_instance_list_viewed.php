<?php
/**
 * The mod_videoxapi course module instance list viewed event.
 *
 * @package     mod_videoxapi
 * @copyright   2024 Your Name <your.email@example.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoxapi\event;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_videoxapi course module instance list viewed event class.
 *
 * @package    mod_videoxapi
 * @copyright  2024 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_module_instance_list_viewed extends \core\event\course_module_instance_list_viewed {
    
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'videoxapi';
    }
}