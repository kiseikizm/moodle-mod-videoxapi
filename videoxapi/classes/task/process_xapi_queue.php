<?php
/**
 * Scheduled task for processing xAPI statement queue
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoxapi\task;

use mod_videoxapi\xapi\ConfigManager;
use mod_videoxapi\xapi\Tracker;

defined('MOODLE_INTERNAL') || die();

/**
 * Scheduled task to process queued xAPI statements
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class process_xapi_queue extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task
     *
     * @return string
     */
    public function get_name() {
        return get_string('processxapiqueue', 'videoxapi');
    }

    /**
     * Execute the task
     */
    public function execute() {
        // Check if xAPI is enabled and queue processing is enabled.
        if (!ConfigManager::isXapiEnabled() || !ConfigManager::isQueueEnabled()) {
            mtrace('xAPI or queue processing is disabled, skipping...');
            return;
        }

        $config = ConfigManager::getLrsConfig();
        $validation = ConfigManager::validateLrsConfig($config);
        
        if (!$validation['valid']) {
            mtrace('Invalid LRS configuration: ' . implode(', ', $validation['errors']));
            return;
        }

        try {
            $tracker = new Tracker(
                $config['endpoint'],
                $config['username'],
                $config['password'],
                $config['auth_method']
            );

            $batchSize = get_config('mod_videoxapi', 'queue_batch_size') ?: 50;
            $results = $tracker->processQueue($batchSize);

            mtrace("Processed {$results['processed']} statements:");
            mtrace("- Successful: {$results['successful']}");
            mtrace("- Failed: {$results['failed']}");

            if (!empty($results['errors'])) {
                mtrace('Errors encountered:');
                foreach (array_unique($results['errors']) as $error) {
                    mtrace('- ' . $error);
                }
            }

        } catch (\Exception $e) {
            mtrace('Error processing xAPI queue: ' . $e->getMessage());
        }
    }
}