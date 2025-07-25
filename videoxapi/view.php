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
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/videoxapi/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($videoxapi->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Add CSS.
$PAGE->requires->css('/mod/videoxapi/styles/styles.css');

// Determine video URL and check if video is configured.
$videourl = '';
$hasvideo = false;

if ($videoxapi->video_source === 'file') {
    // Handle uploaded file.
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_videoxapi', 'video', 0, 'filename', false);
    if (!empty($files)) {
        $file = reset($files);
        $videourl = moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        )->out();
        $hasvideo = true;
    }
} else if ($videoxapi->video_source === 'url' && !empty($videoxapi->video_url)) {
    $videourl = $videoxapi->video_url;
    $hasvideo = true;
}

// Add JavaScript for video player if video is available.
if ($hasvideo && !empty($videourl)) {
    $PAGE->requires->js_call_amd('mod_videoxapi/player', 'init', [
        [
            'playerId' => 'videoxapi-player-' . $videoxapi->id,
            'videoxapiId' => $videoxapi->id,
            'videoUrl' => $videourl,
            'width' => $videoxapi->video_width ?? 640,
            'height' => $videoxapi->video_height ?? 360,
            'responsive' => !empty($videoxapi->responsive_sizing),
            'trackingLevel' => $videoxapi->xapi_tracking_level ?? 'standard',
            'bookmarksEnabled' => !empty($videoxapi->enable_bookmarks)
        ]
    ]);
}

echo $OUTPUT->header();

// Activity introduction.
if (trim(strip_tags($videoxapi->intro))) {
    echo $OUTPUT->box_start('mod_introbox', 'videoxapiintro');
    echo format_module_intro('videoxapi', $videoxapi, $cm->id);
    echo $OUTPUT->box_end();
}

// Debug: Show video configuration info (remove in production)
if (debugging()) {
    echo '<div class="alert alert-info">';
    echo '<strong>Debug Info:</strong><br>';
    echo 'Video Source: ' . ($videoxapi->video_source ?? 'not set') . '<br>';
    echo 'Video URL: ' . ($videoxapi->video_url ?? 'not set') . '<br>';
    echo 'Has Video: ' . ($hasvideo ? 'true' : 'false') . '<br>';
    echo 'Video URL Generated: ' . ($videourl ?? 'not set') . '<br>';
    
    if ($videoxapi->video_source === 'file') {
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_videoxapi', 'video', 0, 'filename', false);
        echo 'Files found: ' . count($files) . '<br>';
        if (!empty($files)) {
            foreach ($files as $file) {
                $filename = $file->get_filename();
                $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $filesize = display_size($file->get_filesize());
                echo 'File: ' . $filename . ' (' . $filesize . ', ext: ' . $extension . ')<br>';
                echo 'MIME Type: ' . $file->get_mimetype() . '<br>';
                echo 'File Hash: ' . $file->get_contenthash() . '<br>';
            }
        }
    }
    
    // Test video URL accessibility
    if (!empty($videourl)) {
        echo '<br><strong>Video URL Test:</strong><br>';
        echo '<a href="' . $videourl . '" target="_blank">Test Video URL</a><br>';
        
        // Check if URL is accessible
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $videourl);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contenttype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);
        
        echo 'HTTP Status: ' . $httpcode . '<br>';
        echo 'Content Type: ' . ($contenttype ?: 'unknown') . '<br>';
    }
    echo '</div>';
}

// Show warning message if no video is configured.
if (!$hasvideo) {
    echo $OUTPUT->notification(get_string('novideo', 'videoxapi'), 'warning');
}

// Prepare template data.
$templatedata = [
    'videoxapi' => $videoxapi,
    'cm' => $cm,
    'course' => $course,
    'playerid' => 'videoxapi-player-' . $videoxapi->id,
    'hasvideo' => $hasvideo,
    'videourl' => $videourl,
    'videosource' => $videoxapi->video_source,
    'cancreatebookmarks' => has_capability('mod/videoxapi:createbookmarks', $context),
    'canviewallbookmarks' => has_capability('mod/videoxapi:viewallbookmarks', $context),
    'canviewreports' => has_capability('mod/videoxapi:viewreports', $context),
    'caneditinstance' => has_capability('moodle/course:manageactivities', $context),
    'config' => ['wwwroot' => $CFG->wwwroot]
];

// Only initialize player if video is available.
if ($hasvideo) {
    // Render the main view template.
    echo $OUTPUT->render_from_template('mod_videoxapi/view', $templatedata);
} else {
    // Show basic template without video player.
    echo $OUTPUT->render_from_template('mod_videoxapi/view_novideo', $templatedata);
}

echo $OUTPUT->footer();