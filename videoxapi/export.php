<?php
/**
 * Export video xAPI activity data
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course module id.
$id = required_param('id', PARAM_INT);
$format = required_param('format', PARAM_ALPHA);

$cm = get_coursemodule_from_id('videoxapi', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$videoxapi = $DB->get_record('videoxapi', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/videoxapi:exportreports', $context);

switch ($format) {
    case 'csv':
        export_csv($videoxapi, $cm, $context);
        break;
    case 'pdf':
        export_pdf($videoxapi, $cm, $context);
        break;
    default:
        throw new moodle_exception('invalidformat', 'mod_videoxapi');
}

/**
 * Export data as CSV
 */
function export_csv($videoxapi, $cm, $context) {
    global $DB;

    $filename = clean_filename($videoxapi->name . '_export_' . date('Y-m-d')) . '.csv';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // CSV headers.
    fputcsv($output, [
        'Student Name',
        'Email',
        'Bookmark Count',
        'First Activity',
        'Last Activity',
        'Bookmark Timestamp',
        'Bookmark Title',
        'Bookmark Description'
    ]);

    // Get user engagement data with bookmarks.
    $sql = "SELECT u.firstname, u.lastname, u.email,
                   b.timestamp, b.title, b.description, b.timecreated
            FROM {user} u
            JOIN {user_enrolments} ue ON u.id = ue.userid
            JOIN {enrol} e ON ue.enrolid = e.id
            LEFT JOIN {videoxapi_bookmarks} b ON u.id = b.userid AND b.videoxapi = ?
            WHERE e.courseid = ? AND u.deleted = 0
            ORDER BY u.lastname, u.firstname, b.timestamp";

    $records = $DB->get_records_sql($sql, [$videoxapi->id, $cm->course]);

    foreach ($records as $record) {
        $row = [
            fullname($record),
            $record->email,
            '', // Will be calculated separately
            '', // Will be calculated separately  
            '', // Will be calculated separately
            $record->timestamp ? format_time($record->timestamp) : '',
            $record->title ?: '',
            $record->description ?: ''
        ];

        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

/**
 * Export data as PDF (basic implementation)
 */
function export_pdf($videoxapi, $cm, $context) {
    // For a full PDF implementation, you would use a library like TCPDF
    // This is a simplified version that outputs HTML
    
    $filename = clean_filename($videoxapi->name . '_export_' . date('Y-m-d')) . '.html';

    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo '<h1>' . format_string($videoxapi->name) . ' - Export Report</h1>';
    echo '<p>Generated on: ' . date('Y-m-d H:i:s') . '</p>';
    
    // Add report content here
    echo '<p>PDF export functionality would be implemented here using a PDF library.</p>';
    
    exit;
}