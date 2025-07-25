<?php
/**
 * Web service definitions for videoxapi module
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_videoxapi_send_statement' => [
        'classname' => 'mod_videoxapi\external\send_statement',
        'methodname' => 'execute',
        'description' => 'Send xAPI statement for video interaction',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'mod/videoxapi:view'
    ],
    'mod_videoxapi_save_bookmark' => [
        'classname' => 'mod_videoxapi\external\save_bookmark',
        'methodname' => 'execute',
        'description' => 'Save video bookmark',
        'type' => 'write',
        'ajax' => true,
        'capabilities' => 'mod/videoxapi:createbookmarks'
    ],
    'mod_videoxapi_get_bookmarks' => [
        'classname' => 'mod_videoxapi\external\get_bookmarks',
        'methodname' => 'execute',
        'description' => 'Get video bookmarks',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'mod/videoxapi:viewownbookmarks'
    ]
];

$services = [
    'videoxapi_services' => [
        'functions' => [
            'mod_videoxapi_send_statement',
            'mod_videoxapi_save_bookmark',
            'mod_videoxapi_get_bookmarks'
        ],
        'restrictedusers' => 0,
        'enabled' => 1
    ]
];