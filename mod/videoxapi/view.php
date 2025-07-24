<?php
/**
 * Prints an instance of mod_videoxapi.
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

// Activity instance id.
$v = optional_param('v', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('videoxapi', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $videoxapi = $DB->get_record('videoxapi', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    $videoxapi = $DB->get_record('videoxapi', array('id' => $v), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $videoxapi->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('videoxapi', $videoxapi->id, $course->id, false, MUST_EXIST);
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/videoxapi:view', $context);

$event = \mod_videoxapi\event\course_module_viewed::create(array(
    'objectid' => $videoxapi->id,
    'context' => $context,
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('videoxapi', $videoxapi);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);$PAGE->set_url('/mod/videoxapi/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($videoxapi->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Add CSS and JavaScript.
$PAGE->requires->css('/mod/videoxapi/styles/styles.css');
$PAGE->requires->js_call_amd('mod_videoxapi/player', 'init', [
    [
        'playerId' => 'videoxapi-player-' . $videoxapi->id,
        'videoxapiId' => $videoxapi->id,
        'videoUrl' => $videoxapi->video_url,
        'width' => $videoxapi->video_width,
        'height' => $videoxapi->video_height,
        'responsive' => !empty($videoxapi->responsive_sizing),
        'trackingLevel' => $videoxapi->xapi_tracking_level,
        'bookmarksEnabled' => !empty($videoxapi->enable_bookmarks)
    ]
]);

echo $OUTPUT->header();

// Activity introduction.
if (trim(strip_tags($videoxapi->intro))) {
    echo $OUTPUT->box_start('mod_introbox', 'videoxapiintro');
    echo format_module_intro('videoxapi', $videoxapi, $cm->id);
    echo $OUTPUT->box_end();
}

// Prepare template data.
$templatedata = [
    'videoxapi' => $videoxapi,
    'cm' => $cm,
    'course' => $course,
    'playerid' => 'videoxapi-player-' . $videoxapi->id,
    'cancreatebookmarks' => has_capability('mod/videoxapi:createbookmarks', $context),
    'canviewallbookmarks' => has_capability('mod/videoxapi:viewallbookmarks', $context),
    'canviewreports' => has_capability('mod/videoxapi:viewreports', $context)
];

// Render the main view template.
echo $OUTPUT->render_from_template('mod_videoxapi/view', $templatedata);

echo $OUTPUT->footer();