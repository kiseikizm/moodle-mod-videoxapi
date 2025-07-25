<?php
/**
 * Direct video file access test
 */

require(__DIR__.'/../../config.php');

// Get parameters
$contextid = required_param('contextid', PARAM_INT);
$filename = required_param('filename', PARAM_TEXT);

// Get context
$context = context::instance_by_id($contextid);

// Basic security check
require_login();

echo "<!DOCTYPE html><html><head><title>Video Test</title></head><body>";
echo "<h1>Direct Video File Access Test</h1>";

echo "<p><strong>Context ID:</strong> {$contextid}</p>";
echo "<p><strong>Filename:</strong> {$filename}</p>";
echo "<p><strong>Context Level:</strong> {$context->contextlevel}</p>";

// Get file storage
$fs = get_file_storage();

// Get all files in video area
$files = $fs->get_area_files($contextid, 'mod_videoxapi', 'video', 0, 'filename', false);

echo "<h2>Available Files:</h2>";
echo "<ul>";
foreach ($files as $file) {
    echo "<li>";
    echo "Filename: " . $file->get_filename() . "<br>";
    echo "Size: " . display_size($file->get_filesize()) . "<br>";
    echo "MIME: " . $file->get_mimetype() . "<br>";
    echo "Hash: " . $file->get_contenthash() . "<br>";
    
    // Create direct URL
    $url = moodle_url::make_pluginfile_url(
        $file->get_contextid(),
        $file->get_component(),
        $file->get_filearea(),
        $file->get_itemid(),
        $file->get_filepath(),
        $file->get_filename()
    );
    
    echo "URL: <a href='{$url}' target='_blank'>{$url}</a><br>";
    echo "</li><br>";
}
echo "</ul>";

// Try to find specific file
$targetfile = null;
foreach ($files as $file) {
    if ($file->get_filename() === $filename) {
        $targetfile = $file;
        break;
    }
}

if ($targetfile) {
    echo "<h2>Target File Found:</h2>";
    echo "<p>Attempting to serve file directly...</p>";
    
    // Create video element
    $videourl = moodle_url::make_pluginfile_url(
        $targetfile->get_contextid(),
        $targetfile->get_component(),
        $targetfile->get_filearea(),
        $targetfile->get_itemid(),
        $targetfile->get_filepath(),
        $targetfile->get_filename()
    );
    
    echo "<video controls width='640' height='360'>";
    echo "<source src='{$videourl}' type='video/mp4'>";
    echo "Your browser does not support the video tag.";
    echo "</video>";
    
} else {
    echo "<h2>Target File NOT Found!</h2>";
}

echo "</body></html>";
?>