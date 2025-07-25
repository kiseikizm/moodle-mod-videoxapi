<?php
/**
 * Activity configuration form for videoxapi module
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_videoxapi_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('videoxapiname', 'videoxapi'), ['size' => '64']);
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements();

        // Video source configuration section.
        $mform->addElement('header', 'videosource', get_string('videosource', 'videoxapi'));

        // Video source selection.
        $sourceoptions = [
            'url' => get_string('videourl', 'videoxapi'),
            'file' => get_string('videofile', 'videoxapi')
        ];
        $mform->addElement('select', 'video_source', get_string('videosourcetype', 'videoxapi'), $sourceoptions);
        $mform->addHelpButton('video_source', 'videosourcetype', 'videoxapi');
        $mform->setDefault('video_source', 'url');

        // Video URL input.
        $mform->addElement('url', 'video_url', get_string('videourl', 'videoxapi'), ['size' => '60']);
        $mform->setType('video_url', PARAM_URL);
        $mform->addHelpButton('video_url', 'videourl', 'videoxapi');
        $mform->hideIf('video_url', 'video_source', 'eq', 'file');

        // Video file upload.
        $maxvideosize = get_config('mod_videoxapi', 'max_video_size') ?: 100; // Default 100MB
        $maxvideobytes = $maxvideosize * 1024 * 1024; // Convert MB to bytes
        
        $mform->addElement('filemanager', 'video_file', get_string('videofile', 'videoxapi'), null,
            [
                'subdirs' => 0,
                'maxbytes' => min($CFG->maxbytes, $maxvideobytes),
                'areamaxbytes' => $maxvideobytes,
                'maxfiles' => 1,
                'accepted_types' => ['video'],
                'return_types' => FILE_INTERNAL | FILE_EXTERNAL
            ]
        );
        $mform->addHelpButton('video_file', 'videofile', 'videoxapi');
        
        // Add note about maximum file size
        $mform->addElement('static', 'video_file_note', '', 
            get_string('maxvideosize', 'videoxapi') . ': ' . $maxvideosize . ' MB');
        $mform->hideIf('video_file_note', 'video_source', 'eq', 'url');
        
        $mform->hideIf('video_file', 'video_source', 'eq', 'url');

        // Video display settings section.
        $mform->addElement('header', 'videodisplay', get_string('videodisplay', 'videoxapi'));

        // Video width.
        $mform->addElement('text', 'video_width', get_string('videowidth', 'videoxapi'), ['size' => '6']);
        $mform->setType('video_width', PARAM_INT);
        $mform->setDefault('video_width', 640);
        $mform->addRule('video_width', get_string('numeric', 'videoxapi'), 'numeric', null, 'client');
        $mform->addHelpButton('video_width', 'videowidth', 'videoxapi');        // Video height.
        $mform->addElement('text', 'video_height', get_string('videoheight', 'videoxapi'), ['size' => '6']);
        $mform->setType('video_height', PARAM_INT);
        $mform->setDefault('video_height', 360);
        $mform->addRule('video_height', get_string('numeric', 'videoxapi'), 'numeric', null, 'client');
        $mform->addHelpButton('video_height', 'videoheight', 'videoxapi');

        // Responsive sizing option.
        $mform->addElement('advcheckbox', 'responsive_sizing', get_string('responsivesizing', 'videoxapi'));
        $mform->setDefault('responsive_sizing', 1);
        $mform->addHelpButton('responsive_sizing', 'responsivesizing', 'videoxapi');

        // xAPI tracking settings section.
        $mform->addElement('header', 'xapitracking', get_string('xapitracking', 'videoxapi'));

        // xAPI tracking level.
        $trackingoptions = [
            0 => get_string('trackingdisabled', 'videoxapi'),
            1 => get_string('trackingbasic', 'videoxapi'),
            2 => get_string('trackingstandard', 'videoxapi'),
            3 => get_string('trackingdetailed', 'videoxapi')
        ];
        $mform->addElement('select', 'xapi_tracking_level', get_string('xapitrackinglevel', 'videoxapi'), $trackingoptions);
        $mform->setDefault('xapi_tracking_level', 3);
        $mform->addHelpButton('xapi_tracking_level', 'xapitrackinglevel', 'videoxapi');

        // Enable bookmarks.
        $mform->addElement('advcheckbox', 'enable_bookmarks', get_string('enablebookmarks', 'videoxapi'));
        $mform->setDefault('enable_bookmarks', 1);
        $mform->addHelpButton('enable_bookmarks', 'enablebookmarks', 'videoxapi');

        // Bookmark permissions.
        $bookmarkpermissions = [
            'all' => get_string('bookmarksall', 'videoxapi'),
            'own' => get_string('bookmarksown', 'videoxapi'),
            'none' => get_string('bookmarksnone', 'videoxapi')
        ];
        $mform->addElement('select', 'bookmark_permissions', get_string('bookmarkpermissions', 'videoxapi'), $bookmarkpermissions);
        $mform->setDefault('bookmark_permissions', 'own');
        $mform->hideIf('bookmark_permissions', 'enable_bookmarks', 'notchecked');
        $mform->addHelpButton('bookmark_permissions', 'bookmarkpermissions', 'videoxapi');        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();
    }

    /**
     * Perform minimal validation on the settings form
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validate video source configuration.
        if ($data['video_source'] == 'url') {
            if (empty($data['video_url'])) {
                $errors['video_url'] = get_string('required');
            } else if (!filter_var($data['video_url'], FILTER_VALIDATE_URL)) {
                $errors['video_url'] = get_string('invalidurl', 'videoxapi');
            } else {
                // Check if URL is accessible and is a video file.
                $urlinfo = $this->validate_video_url($data['video_url']);
                if (!$urlinfo['valid']) {
                    $errors['video_url'] = $urlinfo['error'];
                }
            }
        }

        // Validate video dimensions.
        if (!empty($data['video_width'])) {
            if ($data['video_width'] < 100 || $data['video_width'] > 1920) {
                $errors['video_width'] = get_string('invalidvideowidth', 'videoxapi');
            }
        }

        if (!empty($data['video_height'])) {
            if ($data['video_height'] < 100 || $data['video_height'] > 1080) {
                $errors['video_height'] = get_string('invalidvideoheight', 'videoxapi');
            }
        }

        return $errors;
    }    /**
     * Validate video URL accessibility and format
     *
     * @param string $url
     * @return array
     */
    private function validate_video_url($url) {
        $result = ['valid' => false, 'error' => ''];

        // Check URL format.
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $result['error'] = get_string('invalidurl', 'videoxapi');
            return $result;
        }

        // Check if URL has video file extension.
        $videoextensions = ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'flv', 'm4v'];
        $urlpath = parse_url($url, PHP_URL_PATH);
        $extension = strtolower(pathinfo($urlpath, PATHINFO_EXTENSION));

        if (!in_array($extension, $videoextensions)) {
            $result['error'] = get_string('invalidvideoformat', 'videoxapi');
            return $result;
        }

        // Try to get headers to check if URL is accessible.
        $context = stream_context_create([
            'http' => [
                'method' => 'HEAD',
                'timeout' => 10,
                'user_agent' => 'Moodle VideoXAPI Plugin'
            ]
        ]);

        $headers = @get_headers($url, 1, $context);
        if ($headers === false) {
            $result['error'] = get_string('videourlnotaccessible', 'videoxapi');
            return $result;
        }

        // Check HTTP status code.
        $statuscode = 0;
        if (isset($headers[0])) {
            preg_match('/HTTP\/\d\.\d\s+(\d+)/', $headers[0], $matches);
            $statuscode = isset($matches[1]) ? intval($matches[1]) : 0;
        }

        if ($statuscode < 200 || $statuscode >= 400) {
            $result['error'] = get_string('videourlnotfound', 'videoxapi');
            return $result;
        }

        $result['valid'] = true;
        return $result;
    }

    /**
     * Add any custom completion rules to the form.
     *
     * @return array Array of string IDs of added items, empty array if none
     */
    public function add_completion_rules() {
        $mform =& $this->_form;

        $mform->addElement('checkbox', 'completionwatched', '', get_string('completionwatched', 'videoxapi'));
        $mform->addElement('text', 'completionwatchedpercent', get_string('completionwatchedpercent', 'videoxapi'), ['size' => 3]);
        $mform->setType('completionwatchedpercent', PARAM_INT);
        $mform->setDefault('completionwatchedpercent', 80);
        $mform->hideIf('completionwatchedpercent', 'completionwatched', 'notchecked');

        return ['completionwatched'];
    }

    /**
     * Called during validation to see whether some module-specific completion rules are selected.
     *
     * @param array $data Input data not yet validated.
     * @return bool True if one or more rules is enabled, false if none are.
     */
    public function completion_rule_enabled($data) {
        return !empty($data['completionwatched']);
    }
}