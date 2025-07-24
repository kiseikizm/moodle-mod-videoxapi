<?php
/**
 * English strings for videoxapi
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Video xAPI';
$string['modulenameplural'] = 'Video xAPI activities';
$string['pluginname'] = 'Video xAPI';
$string['pluginadministration'] = 'Video xAPI administration';
$string['modulename_help'] = 'The Video xAPI activity enables students to watch videos with comprehensive learning analytics tracking through xAPI statements.';

// Form strings.
$string['videoxapiname'] = 'Activity name';
$string['videoxapiname_help'] = 'This is the name of the video activity that will be displayed to students.';

// Video source configuration.
$string['videosource'] = 'Video source';
$string['videosourcetype'] = 'Video source type';
$string['videosourcetype_help'] = 'Choose whether to use a video file uploaded to Moodle or an external video URL.';
$string['videourl'] = 'Video URL';
$string['videourl_help'] = 'Enter the direct URL to the video file. Supported formats: MP4, WebM, OGG.';
$string['videofile'] = 'Video file';
$string['videofile_help'] = 'Upload a video file to Moodle. Maximum file size is 10MB.';

// Video display settings.
$string['videodisplay'] = 'Video display settings';
$string['videowidth'] = 'Video width (pixels)';
$string['videowidth_help'] = 'Set the width of the video player in pixels (100-1920).';
$string['videoheight'] = 'Video height (pixels)';
$string['videoheight_help'] = 'Set the height of the video player in pixels (100-1080).';
$string['responsivesizing'] = 'Enable responsive sizing';
$string['responsivesizing_help'] = 'When enabled, the video player will automatically adjust to fit different screen sizes.';// xAPI tracking settings.
$string['xapitracking'] = 'xAPI tracking settings';
$string['xapitrackinglevel'] = 'Tracking level';
$string['xapitrackinglevel_help'] = 'Choose the level of detail for xAPI statement generation.';
$string['trackingdisabled'] = 'Disabled - No xAPI statements';
$string['trackingbasic'] = 'Basic - Play/pause/complete only';
$string['trackingstandard'] = 'Standard - Include seeking and progress';
$string['trackingdetailed'] = 'Detailed - All interactions and bookmarks';

// Bookmark settings.
$string['enablebookmarks'] = 'Enable bookmarks';
$string['enablebookmarks_help'] = 'Allow students to create bookmarks at specific video timestamps.';
$string['bookmarkpermissions'] = 'Bookmark visibility';
$string['bookmarkpermissions_help'] = 'Control who can see bookmarks created by students.';
$string['bookmarksall'] = 'All users can see all bookmarks';
$string['bookmarksown'] = 'Users can only see their own bookmarks';
$string['bookmarksnone'] = 'Bookmarks are private to creators';

// Completion settings.
$string['completionwatched'] = 'Student must watch video';
$string['completionwatched_help'] = 'Require students to watch a minimum percentage of the video for completion.';
$string['completionwatchedpercent'] = 'Minimum watch percentage';

// Validation error messages.
$string['numeric'] = 'This field must be numeric';
$string['invalidurl'] = 'Please enter a valid URL';
$string['invalidvideoformat'] = 'URL must point to a video file (mp4, webm, ogg, avi, mov, wmv, flv, m4v)';
$string['videourlnotaccessible'] = 'Video URL is not accessible';
$string['videourlnotfound'] = 'Video URL returned an error (404 or similar)';
$string['invalidvideowidth'] = 'Video width must be between 100 and 1920 pixels';
$string['invalidvideoheight'] = 'Video height must be between 100 and 1080 pixels';

// General strings.
$string['privacy:metadata'] = 'The Video xAPI plugin stores video interaction data to generate xAPI statements for learning analytics.';
$string['privacy:metadata:videoxapi_bookmarks'] = 'Information about bookmarks created by users';
$string['privacy:metadata:videoxapi_bookmarks:userid'] = 'The ID of the user who created the bookmark';
$string['privacy:metadata:videoxapi_bookmarks:timestamp'] = 'The video timestamp where the bookmark was created';
$string['privacy:metadata:videoxapi_bookmarks:title'] = 'The title given to the bookmark';
$string['privacy:metadata:videoxapi_bookmarks:description'] = 'The description of the bookmark';
$string['privacy:metadata:videoxapi_bookmarks:timecreated'] = 'The time when the bookmark was created';// xAPI Configuration strings.
$string['xapiconfig'] = 'xAPI Configuration';
$string['xapiconfig_desc'] = 'Configure Learning Record Store (LRS) settings for xAPI statement tracking.';
$string['xapienabled'] = 'Enable xAPI tracking';
$string['xapienabled_desc'] = 'Enable or disable xAPI statement generation and sending to LRS.';
$string['lrsendpoint'] = 'LRS endpoint URL';
$string['lrsendpoint_desc'] = 'The HTTPS URL of your Learning Record Store endpoint (e.g., https://lrs.example.com/xapi).';
$string['lrsusername'] = 'LRS username';
$string['lrsusername_desc'] = 'Username for LRS authentication.';
$string['lrspassword'] = 'LRS password';
$string['lrspassword_desc'] = 'Password for LRS authentication.';
$string['lrsauthmethod'] = 'Authentication method';
$string['lrsauthmethod_desc'] = 'Choose the authentication method for LRS communication.';
$string['authbasic'] = 'Basic Authentication';
$string['authoauth'] = 'OAuth Authentication';

// Queue Configuration strings.
$string['queueconfig'] = 'Queue Configuration';
$string['queueconfig_desc'] = 'Configure background processing of xAPI statements.';
$string['queueenabled'] = 'Enable queue processing';
$string['queueenabled_desc'] = 'Enable background processing of xAPI statements. Recommended for better performance.';
$string['queuefrequency'] = 'Queue processing frequency';
$string['queuefrequency_desc'] = 'How often to process queued xAPI statements.';
$string['queuebatchsize'] = 'Queue batch size';
$string['queuebatchsize_desc'] = 'Number of statements to process in each batch.';
$string['connectiontimeout'] = 'Connection timeout';
$string['connectiontimeout_desc'] = 'Timeout in seconds for LRS connections.';

// Time frequency options.
$string['every1minute'] = 'Every minute';
$string['every5minutes'] = 'Every 5 minutes';
$string['every15minutes'] = 'Every 15 minutes';
$string['every30minutes'] = 'Every 30 minutes';
$string['every1hour'] = 'Every hour';

// Test connection.
$string['testconnection'] = 'Test LRS connection';
$string['testconnectionbutton'] = 'Test Connection';
$string['connectionsuccessful'] = 'Connection to LRS successful';
$string['connectionfailed'] = 'Connection to LRS failed: {$a}';// Additional UI strings.
$string['novideo'] = 'No video configured for this activity.';
$string['bookmarks'] = 'Bookmarks';
$string['reports'] = 'Reports';
$string['viewreports'] = 'View Reports';

// Task strings.
$string['processxapiqueue'] = 'Process xAPI statement queue';

// Event strings.
$string['eventcoursemoduleviewed'] = 'Course module viewed';

// Error strings.
$string['errorloadingvideo'] = 'Error loading video';
$string['errorsendingtolrs'] = 'Error sending statement to LRS';
$string['errorsavingbookmark'] = 'Error saving bookmark';

// Capability strings.
$string['videoxapi:addinstance'] = 'Add a new Video xAPI activity';
$string['videoxapi:view'] = 'View Video xAPI activity';
$string['videoxapi:createbookmarks'] = 'Create bookmarks';
$string['videoxapi:viewownbookmarks'] = 'View own bookmarks';
$string['videoxapi:viewallbookmarks'] = 'View all bookmarks';
$string['videoxapi:deleteownbookmarks'] = 'Delete own bookmarks';
$string['videoxapi:deleteanybookmarks'] = 'Delete any bookmarks';
$string['videoxapi:viewreports'] = 'View reports';
$string['videoxapi:exportreports'] = 'Export reports';
$string['videoxapi:configurexapi'] = 'Configure xAPI settings';// Report strings.
$string['activityoverview'] = 'Activity Overview';
$string['metric'] = 'Metric';
$string['value'] = 'Value';
$string['enrolledusers'] = 'Enrolled Users';
$string['totalbookmarks'] = 'Total Bookmarks';
$string['userswithbookmarks'] = 'Users with Bookmarks';
$string['totalstatements'] = 'Total xAPI Statements';
$string['sentstatements'] = 'Sent Statements';
$string['pendingstatements'] = 'Pending Statements';
$string['failedstatements'] = 'Failed Statements';
$string['exportoptions'] = 'Export Options';
$string['exportcsv'] = 'Export as CSV';
$string['exportpdf'] = 'Export as PDF';
$string['engagementreport'] = 'Engagement Report';
$string['bookmarksreport'] = 'Bookmarks Report';
$string['overview'] = 'Overview';
$string['engagement'] = 'Engagement';
$string['student'] = 'Student';
$string['bookmarkcount'] = 'Bookmark Count';
$string['firstactivity'] = 'First Activity';
$string['lastactivity'] = 'Last Activity';
$string['timestamp'] = 'Timestamp';
$string['title'] = 'Title';
$string['description'] = 'Description';
$string['created'] = 'Created';

// Bookmark strings.
$string['nobookmarks'] = 'No bookmarks found.';
$string['bookmarksaved'] = 'Bookmark saved successfully.';
$string['bookmarkdeleted'] = 'Bookmark deleted successfully.';
$string['confirmdeletebookmark'] = 'Are you sure you want to delete this bookmark?';
$string['duplicatebookmark'] = 'A bookmark already exists at this timestamp.';
$string['bookmarksdisabled'] = 'Bookmarks are disabled for this activity.';

// Privacy strings.
$string['privacy:metadata:lrs'] = 'Video interaction data sent to Learning Record Store';
$string['privacy:metadata:lrs:userid'] = 'User identifier for xAPI statements';
$string['privacy:metadata:lrs:timestamp'] = 'Timestamp of video interaction';
$string['privacy:metadata:lrs:videointeraction'] = 'Details of video interaction (play, pause, seek, etc.)';