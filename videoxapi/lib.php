<?php
/**
 * Library of interface functions and constants.
 *
 * @package     mod_videoxapi
 * @copyright   2024 Your Name <your.email@example.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use context_course;

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function videoxapi_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_INTERACTIVECONTENT;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_videoxapi into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the new instance.
 *
 * @param stdClass $moduleinstance An object from the form.
 * @param mod_videoxapi_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function videoxapi_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();
    $moduleinstance->timemodified = time();    // Handle file uploads if video source is file.
    if ($moduleinstance->video_source === 'file' && $mform) {
        $context = context_module::instance($moduleinstance->coursemodule);
        $maxvideosize = get_config('mod_videoxapi', 'max_video_size') ?: 100;
        $maxvideobytes = $maxvideosize * 1024 * 1024;
        
        file_save_draft_area_files(
            $moduleinstance->video_file,
            $context->id,
            'mod_videoxapi',
            'video',
            0,
            array(
                'subdirs' => 0, 
                'maxfiles' => 1,
                'maxbytes' => $maxvideobytes
            )
        );
    }

    $id = $DB->insert_record('videoxapi', $moduleinstance);
    $moduleinstance->id = $id;

    // Trigger module instance created event.
    $event = \mod_videoxapi\event\course_module_instance_list_viewed::create(array(
        'context' => context_course::instance($moduleinstance->course),
        'objectid' => $id,
    ));
    $event->trigger();

    return $id;
}

/**
 * Updates an instance of the mod_videoxapi in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param stdClass $moduleinstance An object from the form in mod_form.php.
 * @param mod_videoxapi_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */function videoxapi_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    // Handle file uploads if video source is file.
    if ($moduleinstance->video_source === 'file' && $mform) {
        $context = context_module::instance($moduleinstance->coursemodule);
        $maxvideosize = get_config('mod_videoxapi', 'max_video_size') ?: 100;
        $maxvideobytes = $maxvideosize * 1024 * 1024;
        
        file_save_draft_area_files(
            $moduleinstance->video_file,
            $context->id,
            'mod_videoxapi',
            'video',
            0,
            array(
                'subdirs' => 0, 
                'maxfiles' => 1,
                'maxbytes' => $maxvideobytes
            )
        );
    }

    $result = $DB->update_record('videoxapi', $moduleinstance);

    return $result;
}

/**
 * Removes an instance of the mod_videoxapi from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function videoxapi_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('videoxapi', array('id' => $id));
    if (!$exists) {
        return false;
    }    // Delete all associated bookmarks.
    $DB->delete_records('videoxapi_bookmarks', array('videoxapi' => $id));

    // Delete all queued xAPI statements.
    $DB->delete_records('videoxapi_statements', array('videoxapi' => $id));

    // Delete the instance.
    $DB->delete_records('videoxapi', array('id' => $id));

    return true;
}

/**
 * Returns the information on whether the module supports a feature.
 *
 * See {@see plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function videoxapi_get_coursemodule_info($coursemodule) {
    global $DB;

    $dbparams = array('id' => $coursemodule->instance);
    $fields = 'id, name, intro, introformat, timemodified';
    if (!$videoxapi = $DB->get_record('videoxapi', $dbparams, $fields)) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $videoxapi->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $result->content = format_module_intro('videoxapi', $videoxapi, $coursemodule->id, false);
    }

    return $result;
}/**
 * File serving function.
 *
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param stdClass $context The context.
 * @param string $filearea The name of the file area.
 * @param array $args Extra arguments (itemid, path).
 * @param bool $forcedownload Whether or not force download.
 * @param array $options Additional options affecting the file serving.
 * @return bool False if the file not found, just send the file otherwise and do not return anything.
 */
function videoxapi_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $DB, $CFG;

    // Debug logging
    if (debugging()) {
        error_log("videoxapi_pluginfile called with: context={$context->id}, filearea={$filearea}, args=" . implode('/', $args));
    }

    if ($context->contextlevel != CONTEXT_MODULE) {
        if (debugging()) {
            error_log("videoxapi_pluginfile: Invalid context level");
        }
        send_file_not_found();
    }

    require_login($course, true, $cm);
    
    // Check capability
    if (!has_capability('mod/videoxapi:view', $context)) {
        if (debugging()) {
            error_log("videoxapi_pluginfile: No view capability");
        }
        send_file_not_found();
    }

    if ($filearea !== 'video') {
        if (debugging()) {
            error_log("videoxapi_pluginfile: Invalid filearea: {$filearea}");
        }
        send_file_not_found();
    }

    $relativepath = implode('/', $args);
    
    // Try different approaches to find the file
    $fs = get_file_storage();
    
    // Method 1: Direct file lookup
    $file = $fs->get_file($context->id, 'mod_videoxapi', $filearea, 0, '/', $relativepath);
    
    if (!$file || $file->is_directory()) {
        // Method 2: Get all files and find matching filename
        $files = $fs->get_area_files($context->id, 'mod_videoxapi', $filearea, 0, 'filename', false);
        foreach ($files as $f) {
            if ($f->get_filename() === $relativepath) {
                $file = $f;
                break;
            }
        }
    }

    if (!$file || $file->is_directory()) {
        if (debugging()) {
            error_log("videoxapi_pluginfile: File not found: {$relativepath}");
            error_log("Available files: " . print_r(array_map(function($f) { return $f->get_filename(); }, $files), true));
        }
        send_file_not_found();
    }

    // Set proper MIME type for video files
    $filename = $file->get_filename();
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    $mimetypes = [
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogg' => 'video/ogg',
        'avi' => 'video/x-msvideo',
        'mov' => 'video/quicktime',
        'wmv' => 'video/x-ms-wmv',
        'flv' => 'video/x-flv',
        'm4v' => 'video/x-m4v'
    ];
    
    if (isset($mimetypes[$extension])) {
        $options['mimetype'] = $mimetypes[$extension];
    }

    // Add headers for video streaming
    $options['cacheability'] = 'public';
    $options['immutable'] = false;
    
    if (debugging()) {
        error_log("videoxapi_pluginfile: Serving file: {$filename}, MIME: " . ($options['mimetype'] ?? 'default'));
    }
    
    send_stored_file($file, null, 0, $forcedownload, $options);
}

/**
 * Whether the activity is branded.
 * This information is used, for instance, to decide if a filter should be applied to the icon or not.
 *
 * @return bool True if the activity is branded, false otherwise.
 */
function videoxapi_is_branded(): bool {
    return false;
}