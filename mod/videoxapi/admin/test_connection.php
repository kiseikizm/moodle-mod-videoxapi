<?php
/**
 * Test LRS connection page for videoxapi module.
 *
 * @package    mod_videoxapi
 * @copyright  2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/mod/videoxapi/lib/xapi/ConfigManager.php');

admin_externalpage_setup('mod_videoxapi_testconnection');

$PAGE->set_url('/mod/videoxapi/admin/test_connection.php');
$PAGE->set_title(get_string('testconnection', 'videoxapi'));
$PAGE->set_heading(get_string('testconnection', 'videoxapi'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('testconnection', 'videoxapi'));

// Get xAPI configuration using static methods
$lrs_endpoint = \mod_videoxapi\xapi\ConfigManager::getLrsEndpoint();
$lrs_username = \mod_videoxapi\xapi\ConfigManager::getLrsUsername();
$lrs_password = \mod_videoxapi\xapi\ConfigManager::getLrsPassword();

if (empty($lrs_endpoint)) {
    echo $OUTPUT->notification(get_string('lrsnotconfigured', 'videoxapi'), 'error');
    echo $OUTPUT->single_button(new moodle_url('/admin/settings.php', ['section' => 'modsetting_videoxapi']), 
        get_string('configurelrs', 'videoxapi'));
} else {    // Test connection
    $success = false;
    $message = '';
    
    try {
        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $lrs_endpoint . '/about');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Experience-API-Version: 1.0.3',
            'Content-Type: application/json'
        ]);
        
        // Add authentication if provided
        if (!empty($lrs_username) && !empty($lrs_password)) {
            curl_setopt($ch, CURLOPT_USERPWD, $lrs_username . ':' . $lrs_password);
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            $message = get_string('connectionerror', 'videoxapi') . ': ' . $error;
        } else if ($http_code >= 200 && $http_code < 300) {
            $success = true;
            $message = get_string('connectionsuccess', 'videoxapi');
        } else {
            $message = get_string('connectionfailed', 'videoxapi') . ' (HTTP ' . $http_code . ')';
        }
        
    } catch (Exception $e) {
        $message = get_string('connectionexception', 'videoxapi') . ': ' . $e->getMessage();
    }
    
    // Display results
    if ($success) {
        echo $OUTPUT->notification($message, 'success');
    } else {
        echo $OUTPUT->notification($message, 'error');
    }
    
    // Show connection details
    echo html_writer::start_tag('div', ['class' => 'mt-3']);
    echo html_writer::tag('h4', get_string('connectiondetails', 'videoxapi'));
    echo html_writer::tag('p', '<strong>' . get_string('lrsendpoint', 'videoxapi') . ':</strong> ' . $lrs_endpoint);
    echo html_writer::tag('p', '<strong>' . get_string('lrsusername', 'videoxapi') . ':</strong> ' . 
        (!empty($lrs_username) ? $lrs_username : get_string('notset', 'videoxapi')));
    echo html_writer::end_tag('div');
}

// Back to settings button
echo html_writer::start_tag('div', ['class' => 'mt-4']);
echo $OUTPUT->single_button(new moodle_url('/admin/settings.php', ['section' => 'modsetting_videoxapi']), 
    get_string('backtosettings', 'videoxapi'));
echo html_writer::end_tag('div');

echo $OUTPUT->footer();