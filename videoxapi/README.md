# Moodle Video xAPI Plugin

A comprehensive Moodle 5 activity module that tracks student video engagement through xAPI (Tin Can API) statements. The plugin enables detailed learning analytics by capturing video interactions and sending them to external Learning Record Stores (LRS).

## Features

- **Video.js Integration**: Modern HTML5 video player with comprehensive event tracking
- **xAPI 1.0.3 Compliance**: Generates standard xAPI statements for learning analytics
- **Learning Record Store (LRS) Support**: Sends statements to external LRS with retry mechanisms
- **Student Bookmarks**: Allow students to create bookmarks at specific video timestamps
- **Instructor Analytics**: Detailed engagement reports and statistics
- **Background Processing**: Queue-based statement processing for better performance
- **Privacy Compliance**: GDPR-compliant data handling and export functionality

## Requirements

- Moodle 5.0 or higher
- PHP 8.1 or higher
- MySQL 5.7+ or PostgreSQL 10+
- Modern web browser with HTML5 video support

## Installation

1. Download the plugin files
2. Extract to `mod/videoxapi` in your Moodle installation
3. Visit Site Administration → Notifications to complete the installation
4. Configure xAPI settings in Site Administration → Plugins → Activity modules → Video xAPI

## Configuration

### LRS Setup

1. Go to Site Administration → Plugins → Activity modules → Video xAPI
2. Enable xAPI tracking
3. Enter your LRS endpoint URL (must be HTTPS)
4. Provide LRS authentication credentials
5. Test the connection using the "Test Connection" button

### Queue Processing

The plugin uses background tasks to process xAPI statements:

1. Enable queue processing in the plugin settings
2. Set the processing frequency (recommended: every 5 minutes)
3. Configure batch size (recommended: 50 statements per batch)

## Usage

### For Instructors

1. Add a "Video xAPI" activity to your course
2. Configure video source (URL or file upload)
3. Set video dimensions and tracking level
4. Enable/disable bookmarks as needed
5. View reports to analyze student engagement

### For Students

1. Watch videos with automatic progress tracking
2. Create bookmarks at important moments
3. Navigate using bookmark timestamps
4. Resume watching from where you left off

## xAPI Statements

The plugin generates the following xAPI statements:

- **played**: When video playback starts
- **paused**: When video is paused
- **seeked**: When user seeks to different timestamp
- **completed**: When video watching is completed
- **bookmarked**: When user creates a bookmark
- **experienced**: For progress tracking and other interactions

## Reporting

### Overview Report
- Total enrolled users
- Bookmark statistics
- xAPI statement statistics
- Export options

### Engagement Report
- Per-student engagement metrics
- Bookmark counts and activity dates
- Sortable by engagement level

### Bookmarks Report
- All bookmarks with timestamps
- Student information
- Chronological ordering

## Privacy and Security

- GDPR-compliant data export and deletion
- Encrypted credential storage
- Input validation and CSRF protection
- Role-based access control

## Capabilities

- `mod/videoxapi:addinstance` - Add new Video xAPI activities
- `mod/videoxapi:view` - View Video xAPI activities
- `mod/videoxapi:createbookmarks` - Create bookmarks
- `mod/videoxapi:viewownbookmarks` - View own bookmarks
- `mod/videoxapi:viewallbookmarks` - View all bookmarks
- `mod/videoxapi:viewreports` - View engagement reports
- `mod/videoxapi:exportreports` - Export report data
- `mod/videoxapi:configurexapi` - Configure xAPI settings

## Troubleshooting

### Video Not Playing
- Check video URL accessibility
- Verify video format compatibility (MP4, WebM, OGG)
- Ensure HTTPS for external video URLs

### xAPI Statements Not Sending
- Verify LRS configuration and credentials
- Check network connectivity to LRS
- Review failed statements in queue statistics
- Check Moodle logs for error messages

### Performance Issues
- Enable queue processing for better performance
- Adjust batch size and frequency settings
- Monitor server resources during peak usage

## Support

For support and bug reports, please visit the plugin's GitHub repository or contact the development team.

## License

This plugin is licensed under the GNU General Public License v3.0. See LICENSE file for details.

## Credits

Developed for Moodle 5 with Video.js integration and xAPI 1.0.3 compliance.