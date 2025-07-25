

/**
 * Bookmark management for videoxapi module
 *
 * @module     mod_videoxapi/bookmarks
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification', 'core/str'], function($, Ajax, Notification, Str) {
    'use strict';

    /**
     * BookmarkManager class
     */
    class BookmarkManager {
        /**
         * Constructor
         * @param {Object} config Configuration
         */
        constructor(config) {
            this.config = config;
            this.bookmarks = [];
            this.player = null;
            
            this.init();
        }

        /**
         * Initialize bookmark manager
         */
        init() {
            this.loadBookmarks();
            this.setupEventListeners();
        }

        /**
         * Set video player reference
         * @param {Object} player Video.js player instance
         */
        setPlayer(player) {
            this.player = player;
        }

        /**
         * Load bookmarks from server
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
         * Display bookmarks in the UI
         */
        displayBookmarks() {
            const container = $('#videoxapi-bookmarks-' + this.config.videoxapiId);
            container.empty();

            if (this.bookmarks.length === 0) {
                container.append('<p class="text-muted">' + M.util.get_string('nobookmarks', 'videoxapi') + '</p>');
                return;
            }

            const bookmarksList = $('<div class="bookmarks-list"></div>');

            this.bookmarks.forEach((bookmark) => {
                const bookmarkItem = this.createBookmarkItem(bookmark);
                bookmarksList.append(bookmarkItem);
            });

            container.append(bookmarksList);
        }        /**
         * Create bookmark item element
         * @param {Object} bookmark Bookmark data
         * @returns {jQuery} Bookmark element
         */
        createBookmarkItem(bookmark) {
            const timeFormatted = this.formatTime(bookmark.timestamp);
            
            const item = $(`
                <div class="bookmark-item" data-bookmark-id="${bookmark.id}" data-timestamp="${bookmark.timestamp}">
                    <div class="bookmark-time">${timeFormatted}</div>
                    <div class="bookmark-content">
                        <div class="bookmark-title">${this.escapeHtml(bookmark.title)}</div>
                        ${bookmark.description ? `<div class="bookmark-description">${this.escapeHtml(bookmark.description)}</div>` : ''}
                        ${bookmark.username ? `<div class="bookmark-author">by ${this.escapeHtml(bookmark.username)}</div>` : ''}
                    </div>
                    <div class="bookmark-actions">
                        <button class="btn btn-sm btn-outline-primary bookmark-goto" title="Go to timestamp">
                            <i class="fa fa-play"></i>
                        </button>
                        ${bookmark.userid == this.config.currentUserId ? `
                            <button class="btn btn-sm btn-outline-danger bookmark-delete" title="Delete bookmark">
                                <i class="fa fa-trash"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `);

            return item;
        }

        /**
         * Setup event listeners
         */
        setupEventListeners() {
            $(document).on('click', '.bookmark-item .bookmark-goto', (e) => {
                e.stopPropagation();
                const timestamp = $(e.target).closest('.bookmark-item').data('timestamp');
                this.goToTimestamp(timestamp);
            });

            $(document).on('click', '.bookmark-item .bookmark-delete', (e) => {
                e.stopPropagation();
                const bookmarkId = $(e.target).closest('.bookmark-item').data('bookmark-id');
                this.deleteBookmark(bookmarkId);
            });

            $(document).on('click', '.bookmark-item', (e) => {
                if (!$(e.target).hasClass('btn') && !$(e.target).parent().hasClass('btn')) {
                    const timestamp = $(e.currentTarget).data('timestamp');
                    this.goToTimestamp(timestamp);
                }
            });
        }

        /**
         * Go to specific timestamp in video
         * @param {number} timestamp Timestamp in seconds
         */
        goToTimestamp(timestamp) {
            if (this.player) {
                this.player.currentTime(timestamp);
                if (this.player.paused()) {
                    this.player.play();
                }
            }
        }        /**
         * Delete bookmark
         * @param {number} bookmarkId Bookmark ID
         */
        deleteBookmark(bookmarkId) {
            Str.get_string('confirmdeletebookmark', 'videoxapi').then((confirmText) => {
                if (confirm(confirmText)) {
                    const promises = Ajax.call([{
                        methodname: 'mod_videoxapi_delete_bookmark',
                        args: {
                            bookmarkid: bookmarkId
                        }
                    }]);

                    promises[0].then(() => {
                        this.loadBookmarks(); // Reload bookmarks
                        Notification.addNotification({
                            message: M.util.get_string('bookmarkdeleted', 'videoxapi'),
                            type: 'success'
                        });
                    }).catch((error) => {
                        Notification.exception(error);
                    });
                }
            });
        }

        /**
         * Add new bookmark
         * @param {number} timestamp Timestamp in seconds
         * @param {string} title Bookmark title
         * @param {string} description Bookmark description
         */
        addBookmark(timestamp, title, description = '') {
            const promises = Ajax.call([{
                methodname: 'mod_videoxapi_save_bookmark',
                args: {
                    videoxapiid: this.config.videoxapiId,
                    timestamp: timestamp,
                    title: title,
                    description: description
                }
            }]);

            promises[0].then(() => {
                this.loadBookmarks(); // Reload bookmarks
                Notification.addNotification({
                    message: M.util.get_string('bookmarksaved', 'videoxapi'),
                    type: 'success'
                });
            }).catch((error) => {
                Notification.exception(error);
            });
        }

        /**
         * Format time in MM:SS or HH:MM:SS format
         * @param {number} seconds Time in seconds
         * @returns {string} Formatted time
         */
        formatTime(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = Math.floor(seconds % 60);

            if (hours > 0) {
                return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            } else {
                return `${minutes}:${secs.toString().padStart(2, '0')}`;
            }
        }

        /**
         * Escape HTML to prevent XSS
         * @param {string} text Text to escape
         * @returns {string} Escaped text
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    }

    return {
        /**
         * Initialize bookmark manager
         * @param {Object} config Configuration
         * @returns {BookmarkManager} BookmarkManager instance
         */
        init: function(config) {
            return new BookmarkManager(config);
        }
    };
});