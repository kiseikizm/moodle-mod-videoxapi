

/**
 * Video.js player integration for videoxapi module
 *
 * @module     mod_videoxapi/player
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {
    'use strict';

    /**
     * VideoXAPI Player class
     */
    class VideoXAPIPlayer {
        /**
         * Constructor
         * @param {Object} config Player configuration
         */
        constructor(config) {
            this.config = config;
            this.player = null;
            this.lastTime = 0;
            this.progressInterval = null;
            this.trackingLevel = config.trackingLevel || 3;
            this.bookmarksEnabled = config.bookmarksEnabled || false;
            this.bookmarks = [];
            
            this.init();
        }

        /**
         * Initialize the video player
         */
        init() {
            this.loadVideoJS().then(() => {
                this.setupPlayer();
                this.setupEventListeners();
                this.loadBookmarks();
            }).catch((error) => {
                Notification.exception(error);
            });
        }        /**
         * Load Video.js library
         * @returns {Promise}
         */
        loadVideoJS() {
            return new Promise((resolve, reject) => {
                if (window.videojs) {
                    resolve();
                    return;
                }

                // Load Video.js CSS
                const cssLink = document.createElement('link');
                cssLink.rel = 'stylesheet';
                cssLink.href = 'https://vjs.zencdn.net/8.6.1/video-js.css';
                document.head.appendChild(cssLink);

                // Load Video.js JavaScript
                const script = document.createElement('script');
                script.src = 'https://vjs.zencdn.net/8.6.1/video.min.js';
                script.onload = resolve;
                script.onerror = () => reject(new Error('Failed to load Video.js'));
                document.head.appendChild(script);
            });
        }

        /**
         * Setup Video.js player
         */
        setupPlayer() {
            const playerOptions = {
                controls: true,
                responsive: this.config.responsive || true,
                fluid: this.config.responsive || true,
                playbackRates: [0.5, 1, 1.25, 1.5, 2],
                plugins: {
                    hotkeys: {
                        volumeStep: 0.1,
                        seekStep: 5,
                        enableModifiersForNumbers: false
                    }
                }
            };

            // Set dimensions if not responsive
            if (!this.config.responsive) {
                playerOptions.width = this.config.width || 640;
                playerOptions.height = this.config.height || 360;
            }

            this.player = window.videojs(this.config.playerId, playerOptions);

            // Add custom styling
            this.player.addClass('videoxapi-player');

            // Handle player ready
            this.player.ready(() => {
                this.onPlayerReady();
            });

            // Error handling
            this.player.on('error', () => {
                this.handleError();
            });
        }        /**
         * Setup event listeners for xAPI tracking
         */
        setupEventListeners() {
            if (this.trackingLevel === 0) {
                return; // No tracking
            }

            // Basic events (level 1+)
            this.player.on('play', () => this.onPlay());
            this.player.on('pause', () => this.onPause());
            this.player.on('ended', () => this.onEnded());

            // Standard events (level 2+)
            if (this.trackingLevel >= 2) {
                this.player.on('seeked', () => this.onSeeked());
                this.startProgressTracking();
            }

            // Detailed events (level 3+)
            if (this.trackingLevel >= 3) {
                this.player.on('volumechange', () => this.onVolumeChange());
                this.player.on('ratechange', () => this.onRateChange());
                this.player.on('fullscreenchange', () => this.onFullscreenChange());
            }
        }

        /**
         * Handle player ready event
         */
        onPlayerReady() {
            this.sendXAPIStatement('experienced', {
                time: 0,
                length: this.player.duration() || 0
            });

            if (this.bookmarksEnabled) {
                this.addBookmarkButton();
                this.displayBookmarks();
            }
        }

        /**
         * Handle play event
         */
        onPlay() {
            const currentTime = this.player.currentTime();
            const duration = this.player.duration() || 0;

            this.sendXAPIStatement('played', {
                time: currentTime,
                length: duration
            });

            this.lastTime = currentTime;
        }

        /**
         * Handle pause event
         */
        onPause() {
            const currentTime = this.player.currentTime();
            const duration = this.player.duration() || 0;

            this.sendXAPIStatement('paused', {
                time: currentTime,
                length: duration
            });
        }        /**
         * Handle seek event
         */
        onSeeked() {
            const currentTime = this.player.currentTime();
            const duration = this.player.duration() || 0;

            this.sendXAPIStatement('seeked', {
                timeFrom: this.lastTime,
                timeTo: currentTime,
                length: duration
            });

            this.lastTime = currentTime;
        }

        /**
         * Handle video end event
         */
        onEnded() {
            const duration = this.player.duration() || 0;
            const watchedPercentage = duration > 0 ? 1.0 : 0;

            this.sendXAPIStatement('completed', {
                time: duration,
                length: duration,
                watchedPercentage: watchedPercentage
            });

            this.stopProgressTracking();
        }

        /**
         * Start progress tracking
         */
        startProgressTracking() {
            this.stopProgressTracking(); // Clear any existing interval

            this.progressInterval = setInterval(() => {
                if (!this.player.paused()) {
                    const currentTime = this.player.currentTime();
                    const duration = this.player.duration() || 0;

                    // Send progress update every 30 seconds
                    if (Math.floor(currentTime) % 30 === 0 && currentTime !== this.lastTime) {
                        this.sendXAPIStatement('experienced', {
                            time: currentTime,
                            length: duration
                        });
                    }

                    this.lastTime = currentTime;
                }
            }, 1000);
        }

        /**
         * Stop progress tracking
         */
        stopProgressTracking() {
            if (this.progressInterval) {
                clearInterval(this.progressInterval);
                this.progressInterval = null;
            }
        }        /**
         * Send xAPI statement
         * @param {string} verb xAPI verb
         * @param {Object} data Statement data
         */
        sendXAPIStatement(verb, data) {
            const promises = Ajax.call([{
                methodname: 'mod_videoxapi_send_statement',
                args: {
                    videoxapiid: this.config.videoxapiId,
                    verb: verb,
                    data: JSON.stringify(data)
                }
            }]);

            promises[0].catch((error) => {
                // Queue statement for later if sending fails
                this.queueStatement(verb, data);
                console.warn('Failed to send xAPI statement:', error);
            });
        }

        /**
         * Queue statement for later sending
         * @param {string} verb xAPI verb
         * @param {Object} data Statement data
         */
        queueStatement(verb, data) {
            const queuedStatements = JSON.parse(localStorage.getItem('videoxapi_queue') || '[]');
            queuedStatements.push({
                videoxapiId: this.config.videoxapiId,
                verb: verb,
                data: data,
                timestamp: Date.now()
            });
            localStorage.setItem('videoxapi_queue', JSON.stringify(queuedStatements));
        }

        /**
         * Add bookmark button to player
         */
        addBookmarkButton() {
            const bookmarkButton = this.player.controlBar.addChild('button', {
                text: 'Bookmark',
                className: 'vjs-bookmark-button'
            });

            bookmarkButton.on('click', () => {
                this.createBookmark();
            });
        }

        /**
         * Create a new bookmark
         */
        createBookmark() {
            const currentTime = this.player.currentTime();
            const title = prompt('Bookmark title:');
            
            if (title) {
                const description = prompt('Bookmark description (optional):') || '';
                
                this.saveBookmark(currentTime, title, description);
            }
        }        /**
         * Save bookmark to database
         * @param {number} time Bookmark time
         * @param {string} title Bookmark title
         * @param {string} description Bookmark description
         */
        saveBookmark(time, title, description) {
            const promises = Ajax.call([{
                methodname: 'mod_videoxapi_save_bookmark',
                args: {
                    videoxapiid: this.config.videoxapiId,
                    timestamp: time,
                    title: title,
                    description: description
                }
            }]);

            promises[0].then(() => {
                this.loadBookmarks();
                this.sendXAPIStatement('bookmarked', {
                    time: time,
                    title: title,
                    description: description,
                    length: this.player.duration() || 0
                });
            }).catch((error) => {
                Notification.exception(error);
            });
        }

        /**
         * Load bookmarks from database
         */
        loadBookmarks() {
            const promises = Ajax.call([{
                methodname: 'mod_videoxapi_get_bookmarks',
                args: {
                    videoxapiid: this.config.videoxapiId
                }
            }]);

            promises[0].then((bookmarks) => {
                this.bookmarks = bookmarks;
                this.displayBookmarks();
            }).catch((error) => {
                console.warn('Failed to load bookmarks:', error);
            });
        }

        /**
         * Display bookmarks on timeline
         */
        displayBookmarks() {
            // Remove existing bookmark markers
            $('.vjs-bookmark-marker').remove();

            this.bookmarks.forEach((bookmark) => {
                this.addBookmarkMarker(bookmark);
            });
        }        /**
         * Add bookmark marker to timeline
         * @param {Object} bookmark Bookmark data
         */
        addBookmarkMarker(bookmark) {
            const duration = this.player.duration();
            if (!duration) return;

            const percentage = (bookmark.timestamp / duration) * 100;
            const progressBar = this.player.el().querySelector('.vjs-progress-control');
            
            if (progressBar) {
                const marker = document.createElement('div');
                marker.className = 'vjs-bookmark-marker';
                marker.style.left = percentage + '%';
                marker.title = bookmark.title;
                marker.addEventListener('click', () => {
                    this.player.currentTime(bookmark.timestamp);
                });
                
                progressBar.appendChild(marker);
            }
        }

        /**
         * Handle player error
         */
        handleError() {
            const error = this.player.error();
            console.error('Video player error:', error);
            
            // Send error statement if tracking is enabled
            if (this.trackingLevel > 0) {
                this.sendXAPIStatement('experienced', {
                    time: this.player.currentTime() || 0,
                    length: this.player.duration() || 0,
                    error: error.message || 'Unknown error'
                });
            }
        }

        /**
         * Handle volume change (detailed tracking)
         */
        onVolumeChange() {
            // Only track significant volume changes
            const volume = this.player.volume();
            this.sendXAPIStatement('experienced', {
                time: this.player.currentTime(),
                length: this.player.duration() || 0,
                volume: volume
            });
        }

        /**
         * Handle playback rate change (detailed tracking)
         */
        onRateChange() {
            const rate = this.player.playbackRate();
            this.sendXAPIStatement('experienced', {
                time: this.player.currentTime(),
                length: this.player.duration() || 0,
                playbackRate: rate
            });
        }

        /**
         * Handle fullscreen change (detailed tracking)
         */
        onFullscreenChange() {
            const isFullscreen = this.player.isFullscreen();
            this.sendXAPIStatement('experienced', {
                time: this.player.currentTime(),
                length: this.player.duration() || 0,
                fullscreen: isFullscreen
            });
        }

        /**
         * Destroy player and cleanup
         */
        destroy() {
            this.stopProgressTracking();
            if (this.player) {
                this.player.dispose();
            }
        }
    }

    return {
        /**
         * Initialize VideoXAPI player
         * @param {Object} config Player configuration
         * @returns {VideoXAPIPlayer} Player instance
         */
        init: function(config) {
            return new VideoXAPIPlayer(config);
        }
    };
});