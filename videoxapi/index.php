<?php
/**
 * Display information about all the mod_videoxapi modules in the requested course.
 *
 * @package     mod_videoxapi
 * @copyright   2024 Your Name <your.email@example.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');

require_once(__DIR__.'/lib.php');

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course);

$coursecontext = context_course::instance($course->id);

$event = \mod_videoxapi\event\course_module_instance_list_viewed::create(array(
    'context' => $coursecontext
));
$event->add_record_snapshot('course', $course);
$event->trigger();$PAGE->set_url('/mod/videoxapi/index.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($coursecontext);

echo $OUTPUT->header();

$modulenameplural = get_string('modulenameplural', 'videoxapi');
echo $OUTPUT->heading($modulenameplural);

$videoxapis = get_all_instances_in_course('videoxapi', $course);

if (empty($videoxapis)) {
    notice(get_string('no$MODULENAMEs', 'videoxapi'), new moodle_url('/course/view.php', array('id' => $course->id)));
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($course->format == 'weeks') {
    $table->head  = array(get_string('week'), get_string('name'));
    $table->align = array('center', 'left');
} else if ($course->format == 'topics') {
    $table->head  = array(get_string('topic'), get_string('name'));
    $table->align = array('center', 'left');
} else {
    $table->head  = array(get_string('name'));
    $table->align = array('left');
}

foreach ($videoxapis as $videoxapi) {
    if (!$videoxapi->visible) {
        $link = html_writer::link(
            new moodle_url('/mod/videoxapi/view.php', array('id' => $videoxapi->coursemodule)),
            format_string($videoxapi->name, true),
            array('class' => 'dimmed')
        );
    } else {
        $link = html_writer::link(
            new moodle_url('/mod/videoxapi/view.php', array('id' => $videoxapi->coursemodule)),
            format_string($videoxapi->name, true)
        );
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array($videoxapi->section, $link);
    } else {
        $table->data[] = array($link);
    }
}

echo html_writer::table($table);
echo $OUTPUT->footer();