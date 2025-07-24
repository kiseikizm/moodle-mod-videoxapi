<?php
/**
 * xAPI Statement Builder for videoxapi module
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videoxapi\xapi;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for building xAPI 1.0.3 compliant statements
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class StatementBuilder {

    /** @var string xAPI specification version */
    const XAPI_VERSION = '1.0.3';

    /** @var array Standard xAPI verbs */
    const VERBS = [
        'played' => 'https://w3id.org/xapi/video/verbs/played',
        'paused' => 'https://w3id.org/xapi/video/verbs/paused',
        'seeked' => 'https://w3id.org/xapi/video/verbs/seeked',
        'completed' => 'http://adlnet.gov/expapi/verbs/completed',
        'bookmarked' => 'https://w3id.org/xapi/adb/verbs/bookmarked',
        'experienced' => 'http://adlnet.gov/expapi/verbs/experienced'
    ];

    /** @var array Activity extensions */
    const EXTENSIONS = [
        'video_length' => 'https://w3id.org/xapi/video/extensions/length',
        'video_progress' => 'https://w3id.org/xapi/video/extensions/progress',
        'video_time' => 'https://w3id.org/xapi/video/extensions/time',
        'video_time_from' => 'https://w3id.org/xapi/video/extensions/time-from',
        'video_time_to' => 'https://w3id.org/xapi/video/extensions/time-to',
        'bookmark_time' => 'https://w3id.org/xapi/video/extensions/time'
    ];

    /** @var object Moodle user object */
    private $user;

    /** @var object Video activity instance */
    private $videoxapi;

    /** @var object Course object */
    private $course;    /**
     * Constructor
     *
     * @param object $user Moodle user object
     * @param object $videoxapi Video activity instance
     * @param object $course Course object
     */
    public function __construct($user, $videoxapi, $course) {
        $this->user = $user;
        $this->videoxapi = $videoxapi;
        $this->course = $course;
    }

    /**
     * Build a play statement
     *
     * @param float $time Current video time in seconds
     * @param float $length Total video length in seconds
     * @return array xAPI statement
     */
    public function buildPlayStatement($time, $length) {
        return [
            'version' => self::XAPI_VERSION,
            'timestamp' => $this->getTimestamp(),
            'actor' => $this->buildActor(),
            'verb' => $this->buildVerb('played'),
            'object' => $this->buildVideoObject(),
            'result' => [
                'extensions' => [
                    self::EXTENSIONS['video_time'] => $time,
                    self::EXTENSIONS['video_length'] => $length,
                    self::EXTENSIONS['video_progress'] => $length > 0 ? $time / $length : 0
                ]
            ],
            'context' => $this->buildContext()
        ];
    }

    /**
     * Build a pause statement
     *
     * @param float $time Current video time in seconds
     * @param float $length Total video length in seconds
     * @return array xAPI statement
     */
    public function buildPauseStatement($time, $length) {
        return [
            'version' => self::XAPI_VERSION,
            'timestamp' => $this->getTimestamp(),
            'actor' => $this->buildActor(),
            'verb' => $this->buildVerb('paused'),
            'object' => $this->buildVideoObject(),
            'result' => [
                'extensions' => [
                    self::EXTENSIONS['video_time'] => $time,
                    self::EXTENSIONS['video_length'] => $length,
                    self::EXTENSIONS['video_progress'] => $length > 0 ? $time / $length : 0
                ]
            ],
            'context' => $this->buildContext()
        ];
    }    /**
     * Build a seek statement
     *
     * @param float $timeFrom Time seeked from in seconds
     * @param float $timeTo Time seeked to in seconds
     * @param float $length Total video length in seconds
     * @return array xAPI statement
     */
    public function buildSeekStatement($timeFrom, $timeTo, $length) {
        return [
            'version' => self::XAPI_VERSION,
            'timestamp' => $this->getTimestamp(),
            'actor' => $this->buildActor(),
            'verb' => $this->buildVerb('seeked'),
            'object' => $this->buildVideoObject(),
            'result' => [
                'extensions' => [
                    self::EXTENSIONS['video_time_from'] => $timeFrom,
                    self::EXTENSIONS['video_time_to'] => $timeTo,
                    self::EXTENSIONS['video_length'] => $length,
                    self::EXTENSIONS['video_progress'] => $length > 0 ? $timeTo / $length : 0
                ]
            ],
            'context' => $this->buildContext()
        ];
    }

    /**
     * Build a completed statement
     *
     * @param float $time Final video time in seconds
     * @param float $length Total video length in seconds
     * @param float $watchedPercentage Percentage of video watched
     * @return array xAPI statement
     */
    public function buildCompletedStatement($time, $length, $watchedPercentage) {
        return [
            'version' => self::XAPI_VERSION,
            'timestamp' => $this->getTimestamp(),
            'actor' => $this->buildActor(),
            'verb' => $this->buildVerb('completed'),
            'object' => $this->buildVideoObject(),
            'result' => [
                'completion' => true,
                'success' => $watchedPercentage >= 0.8, // 80% threshold for success.
                'score' => [
                    'scaled' => $watchedPercentage,
                    'raw' => $watchedPercentage * 100,
                    'min' => 0,
                    'max' => 100
                ],
                'extensions' => [
                    self::EXTENSIONS['video_time'] => $time,
                    self::EXTENSIONS['video_length'] => $length,
                    self::EXTENSIONS['video_progress'] => $watchedPercentage
                ]
            ],
            'context' => $this->buildContext()
        ];
    }    /**
     * Build a bookmark statement
     *
     * @param float $time Bookmark time in seconds
     * @param string $title Bookmark title
     * @param string $description Bookmark description
     * @param float $length Total video length in seconds
     * @return array xAPI statement
     */
    public function buildBookmarkStatement($time, $title, $description, $length) {
        return [
            'version' => self::XAPI_VERSION,
            'timestamp' => $this->getTimestamp(),
            'actor' => $this->buildActor(),
            'verb' => $this->buildVerb('bookmarked'),
            'object' => $this->buildVideoObject(),
            'result' => [
                'response' => $title . ($description ? ': ' . $description : ''),
                'extensions' => [
                    self::EXTENSIONS['bookmark_time'] => $time,
                    self::EXTENSIONS['video_length'] => $length,
                    self::EXTENSIONS['video_progress'] => $length > 0 ? $time / $length : 0
                ]
            ],
            'context' => $this->buildContext()
        ];
    }

    /**
     * Build actor component
     *
     * @return array Actor object
     */
    private function buildActor() {
        global $CFG;

        $actor = [
            'objectType' => 'Agent'
        ];

        // Use email as identifier if available.
        if (!empty($this->user->email)) {
            $actor['mbox'] = 'mailto:' . $this->user->email;
        } else {
            // Fallback to account identifier.
            $actor['account'] = [
                'homePage' => $CFG->wwwroot,
                'name' => $this->user->username
            ];
        }

        // Add name if available.
        if (!empty($this->user->firstname) || !empty($this->user->lastname)) {
            $actor['name'] = trim($this->user->firstname . ' ' . $this->user->lastname);
        }

        return $actor;
    }    /**
     * Build verb component
     *
     * @param string $verbKey Verb key from VERBS constant
     * @return array Verb object
     */
    private function buildVerb($verbKey) {
        $verbLabels = [
            'played' => ['en-US' => 'played'],
            'paused' => ['en-US' => 'paused'],
            'seeked' => ['en-US' => 'seeked'],
            'completed' => ['en-US' => 'completed'],
            'bookmarked' => ['en-US' => 'bookmarked'],
            'experienced' => ['en-US' => 'experienced']
        ];

        return [
            'id' => self::VERBS[$verbKey],
            'display' => $verbLabels[$verbKey]
        ];
    }

    /**
     * Build video object component
     *
     * @return array Object component
     */
    private function buildVideoObject() {
        global $CFG;

        $object = [
            'objectType' => 'Activity',
            'id' => $CFG->wwwroot . '/mod/videoxapi/view.php?id=' . $this->videoxapi->id,
            'definition' => [
                'name' => ['en-US' => $this->videoxapi->name],
                'type' => 'https://w3id.org/xapi/video/activity-type/video'
            ]
        ];

        // Add description if available.
        if (!empty($this->videoxapi->intro)) {
            $object['definition']['description'] = ['en-US' => strip_tags($this->videoxapi->intro)];
        }

        // Add video URL if available.
        if (!empty($this->videoxapi->video_url)) {
            $object['definition']['moreInfo'] = $this->videoxapi->video_url;
        }

        return $object;
    }

    /**
     * Build context component
     *
     * @return array Context object
     */
    private function buildContext() {
        global $CFG;

        $context = [
            'platform' => 'Moodle',
            'language' => 'en-US',
            'contextActivities' => [
                'parent' => [
                    [
                        'objectType' => 'Activity',
                        'id' => $CFG->wwwroot . '/course/view.php?id=' . $this->course->id,
                        'definition' => [
                            'name' => ['en-US' => $this->course->fullname],
                            'type' => 'http://adlnet.gov/expapi/activities/course'
                        ]
                    ]
                ]
            ]
        ];

        // Add instructor information if available.
        $context['instructor'] = $this->getInstructor();

        return $context;
    }    /**
     * Get instructor information for context
     *
     * @return array Instructor actor object
     */
    private function getInstructor() {
        global $DB;

        // Get course context.
        $coursecontext = \context_course::instance($this->course->id);

        // Find users with editing teacher capability.
        $instructors = get_enrolled_users($coursecontext, 'mod/videoxapi:configurexapi', 0, 'u.*', null, 0, 1);

        if (!empty($instructors)) {
            $instructor = reset($instructors);
            
            $actor = [
                'objectType' => 'Agent'
            ];

            if (!empty($instructor->email)) {
                $actor['mbox'] = 'mailto:' . $instructor->email;
            } else {
                global $CFG;
                $actor['account'] = [
                    'homePage' => $CFG->wwwroot,
                    'name' => $instructor->username
                ];
            }

            if (!empty($instructor->firstname) || !empty($instructor->lastname)) {
                $actor['name'] = trim($instructor->firstname . ' ' . $instructor->lastname);
            }

            return $actor;
        }

        return null;
    }

    /**
     * Get current timestamp in ISO 8601 format
     *
     * @return string Timestamp
     */
    private function getTimestamp() {
        return date('c');
    }

    /**
     * Validate statement structure
     *
     * @param array $statement xAPI statement
     * @return bool True if valid
     */
    public function validateStatement($statement) {
        // Check required fields.
        $requiredFields = ['actor', 'verb', 'object'];
        
        foreach ($requiredFields as $field) {
            if (!isset($statement[$field])) {
                return false;
            }
        }

        // Validate actor.
        if (!$this->validateActor($statement['actor'])) {
            return false;
        }

        // Validate verb.
        if (!$this->validateVerb($statement['verb'])) {
            return false;
        }

        // Validate object.
        if (!$this->validateObject($statement['object'])) {
            return false;
        }

        return true;
    }    /**
     * Validate actor component
     *
     * @param array $actor Actor object
     * @return bool True if valid
     */
    private function validateActor($actor) {
        if (!isset($actor['objectType']) || $actor['objectType'] !== 'Agent') {
            return false;
        }

        // Must have either mbox or account.
        if (!isset($actor['mbox']) && !isset($actor['account'])) {
            return false;
        }

        // Validate mbox format.
        if (isset($actor['mbox']) && !filter_var(str_replace('mailto:', '', $actor['mbox']), FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return true;
    }

    /**
     * Validate verb component
     *
     * @param array $verb Verb object
     * @return bool True if valid
     */
    private function validateVerb($verb) {
        if (!isset($verb['id']) || !filter_var($verb['id'], FILTER_VALIDATE_URL)) {
            return false;
        }

        return true;
    }

    /**
     * Validate object component
     *
     * @param array $object Object component
     * @return bool True if valid
     */
    private function validateObject($object) {
        if (!isset($object['objectType']) || $object['objectType'] !== 'Activity') {
            return false;
        }

        if (!isset($object['id']) || !filter_var($object['id'], FILTER_VALIDATE_URL)) {
            return false;
        }

        return true;
    }
}