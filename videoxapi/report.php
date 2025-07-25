<?php
/**
 * Video xAPI activity reports
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course module id.
$id = required_param('id', PARAM_INT);
$action = optional_param('action', 'overview', PARAM_ALPHA);

$cm = get_coursemodule_from_id('videoxapi', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$videoxapi = $DB->get_record('videoxapi', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/videoxapi:viewreports', $context);

$PAGE->set_url('/mod/videoxapi/report.php', array('id' => $cm->id, 'action' => $action));
$PAGE->set_title(format_string($videoxapi->name) . ' - ' . get_string('reports', 'mod_videoxapi'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Add navigation.
$PAGE->navbar->add(get_string('reports', 'mod_videoxapi'));

echo $OUTPUT->header();

// Report navigation tabs.
$tabs = array();
$tabs[] = new tabobject('overview', new moodle_url('/mod/videoxapi/report.php', array('id' => $id, 'action' => 'overview')), 
    get_string('overview', 'mod_videoxapi'));
$tabs[] = new tabobject('engagement', new moodle_url('/mod/videoxapi/report.php', array('id' => $id, 'action' => 'engagement')), 
    get_string('engagement', 'mod_videoxapi'));
$tabs[] = new tabobject('bookmarks', new moodle_url('/mod/videoxapi/report.php', array('id' => $id, 'action' => 'bookmarks')), 
    get_string('bookmarks', 'mod_videoxapi'));

echo $OUTPUT->tabtree($tabs, $action);

switch ($action) {
    case 'engagement':
        show_engagement_report($videoxapi, $cm, $context);
        break;
    case 'bookmarks':
        show_bookmarks_report($videoxapi, $cm, $context);
        break;
    case 'overview':
    default:
        show_overview_report($videoxapi, $cm, $context);
        break;
}

echo $OUTPUT->footer();/**
 * Show overview report
 */
function show_overview_report($videoxapi, $cm, $context) {
    global $DB, $OUTPUT;

    // Get enrolled users.
    $enrolledusers = get_enrolled_users($context, 'mod/videoxapi:view');
    $totalusers = count($enrolledusers);

    // Get bookmark statistics.
    $bookmarkstats = $DB->get_record_sql("
        SELECT COUNT(*) as total_bookmarks, COUNT(DISTINCT userid) as users_with_bookmarks
        FROM {videoxapi_bookmarks} 
        WHERE videoxapi = ?", [$videoxapi->id]);

    // Get xAPI statement statistics.
    $statementstats = $DB->get_record_sql("
        SELECT COUNT(*) as total_statements, 
               SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as sent_statements,
               SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as pending_statements,
               SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as failed_statements
        FROM {videoxapi_statements}");

    echo $OUTPUT->heading(get_string('activityoverview', 'mod_videoxapi'), 3);

    // Overview statistics table.
    $table = new html_table();
    $table->head = [get_string('metric', 'mod_videoxapi'), get_string('value', 'mod_videoxapi')];
    $table->data = [
        [get_string('enrolledusers', 'mod_videoxapi'), $totalusers],
        [get_string('totalbookmarks', 'mod_videoxapi'), $bookmarkstats->total_bookmarks ?: 0],
        [get_string('userswithbookmarks', 'mod_videoxapi'), $bookmarkstats->users_with_bookmarks ?: 0],
        [get_string('totalstatements', 'mod_videoxapi'), $statementstats->total_statements ?: 0],
        [get_string('sentstatements', 'mod_videoxapi'), $statementstats->sent_statements ?: 0],
        [get_string('pendingstatements', 'mod_videoxapi'), $statementstats->pending_statements ?: 0],
        [get_string('failedstatements', 'mod_videoxapi'), $statementstats->failed_statements ?: 0]
    ];

    echo html_writer::table($table);

    // Export options.
    echo $OUTPUT->heading(get_string('exportoptions', 'mod_videoxapi'), 3);
    echo html_writer::start_div('export-options');
    
    $exporturl = new moodle_url('/mod/videoxapi/export.php', ['id' => $cm->id, 'format' => 'csv']);
    echo html_writer::link($exporturl, get_string('exportcsv', 'mod_videoxapi'), ['class' => 'btn btn-secondary']);
    
    $exporturl->param('format', 'pdf');
    echo ' ';
    echo html_writer::link($exporturl, get_string('exportpdf', 'mod_videoxapi'), ['class' => 'btn btn-secondary']);
    
    echo html_writer::end_div();
}

/**
 * Show engagement report
 */
function show_engagement_report($videoxapi, $cm, $context) {
    global $DB, $OUTPUT;

    echo $OUTPUT->heading(get_string('engagementreport', 'mod_videoxapi'), 3);

    // Get user engagement data.
    $sql = "SELECT u.id, u.firstname, u.lastname, u.email,
                   COUNT(DISTINCT b.id) as bookmark_count,
                   MIN(b.timecreated) as first_bookmark,
                   MAX(b.timecreated) as last_bookmark
            FROM {user} u
            JOIN {user_enrolments} ue ON u.id = ue.userid
            JOIN {enrol} e ON ue.enrolid = e.id
            LEFT JOIN {videoxapi_bookmarks} b ON u.id = b.userid AND b.videoxapi = ?
            WHERE e.courseid = ? AND u.deleted = 0
            GROUP BY u.id, u.firstname, u.lastname, u.email
            ORDER BY bookmark_count DESC, u.lastname, u.firstname";

    $users = $DB->get_records_sql($sql, [$videoxapi->id, $cm->course]);

    $table = new html_table();
    $table->head = [
        get_string('student', 'mod_videoxapi'),
        get_string('email'),
        get_string('bookmarkcount', 'mod_videoxapi'),
        get_string('firstactivity', 'mod_videoxapi'),
        get_string('lastactivity', 'mod_videoxapi')
    ];

    foreach ($users as $user) {
        $fullname = fullname($user);
        $bookmarkcount = $user->bookmark_count ?: 0;
        $firstactivity = $user->first_bookmark ? userdate($user->first_bookmark) : '-';
        $lastactivity = $user->last_bookmark ? userdate($user->last_bookmark) : '-';

        $table->data[] = [$fullname, $user->email, $bookmarkcount, $firstactivity, $lastactivity];
    }

    echo html_writer::table($table);
}/**
 * Show bookmarks report
 */
function show_bookmarks_report($videoxapi, $cm, $context) {
    global $DB, $OUTPUT;

    echo $OUTPUT->heading(get_string('bookmarksreport', 'mod_videoxapi'), 3);

    // Get all bookmarks with user information.
    $sql = "SELECT b.*, u.firstname, u.lastname, u.email
            FROM {videoxapi_bookmarks} b
            JOIN {user} u ON b.userid = u.id
            WHERE b.videoxapi = ?
            ORDER BY b.timestamp ASC";

    $bookmarks = $DB->get_records_sql($sql, [$videoxapi->id]);

    if (empty($bookmarks)) {
        echo $OUTPUT->notification(get_string('nobookmarks', 'mod_videoxapi'), 'info');
        return;
    }

    $table = new html_table();
    $table->head = [
        get_string('timestamp', 'mod_videoxapi'),
        get_string('title', 'mod_videoxapi'),
        get_string('description', 'mod_videoxapi'),
        get_string('student', 'mod_videoxapi'),
        get_string('created', 'mod_videoxapi')
    ];

    foreach ($bookmarks as $bookmark) {
        $timestamp = format_time($bookmark->timestamp);
        $title = format_string($bookmark->title);
        $description = $bookmark->description ? format_text($bookmark->description) : '-';
        $student = fullname($bookmark);
        $created = userdate($bookmark->timecreated);

        $table->data[] = [$timestamp, $title, $description, $student, $created];
    }

    echo html_writer::table($table);
}

/**
 * Format time in MM:SS or HH:MM:SS format
 */
function format_time($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = floor($seconds % 60);

    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
    } else {
        return sprintf('%d:%02d', $minutes, $secs);
    }
}