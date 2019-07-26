(function($, window, document, undefined) {

    "use strict";

    var pluginName = "bpSharePostsButton",
        defaults = {
        };

    /**
     * Initialise
     * @param {DomElement} element 
     * @param {Object} options 
     */
    function Plugin (element, options) {
        this.element = element;
        this.settings = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;

        this.$container = $(element);
        this.shareIcon = this.$container.data('share-icon');
        this.sharedIcon = this.$container.data('shared-icon');
        this.shareLabel = this.$container.data('share-label');
        this.sharedLabel = this.$container.data('shared-label');
        this.itemId = parseInt(this.$container.data('item-id'));
        this.type = this.$container.data('type');

        this.init();
    }

    $.extend(Plugin.prototype, {
        /**
         * Init the plugin
         */
        init: function() {
            this.$container.on('click', this.shareClicked.bind(this));
        },
        shareClicked: function (e) {
            e.preventDefault();
            
            if (this.$container.hasClass('bp-share-posts-loading')) {
                return;
            }

            this.$container.addClass('bp-share-posts-loading');

            var self = this;
            var status = this.$container.data('status') === 'shared' ? '' : 'share';
            var data = {
                action: 'bp_share_post',
                type: this.type,
                item_id: this.itemId,
                status: status
            };

            jQuery
                .post(ajaxurl, data, function(response) {
                    if (response && response.success) {
                        if (status === 'share') {
                            self.markAsShared();
                        } else {
                            self.markAsUnshared();
                        }
                    }
                }, 'json')
                .always(function () {
                    self.$container.removeClass('bp-share-posts-loading');
                });
        },
        /**
         * Mark the button as shared.
         */
        markAsShared: function() {
            this.$container.data('status', 'shared');
            this.$container.addClass('bp-share-posts-shared');

            if (this.sharedIcon.length) {
                var $icon = jQuery(this.sharedIcon);

                this.$container.find('.bp-share-posts-icon').remove();
                this.$container.prepend($icon);
            }

            if (this.sharedLabel.length) {
                this.$container.find('.bp-share-posts-label').html(this.sharedLabel);
            }
        },
        /**
         * Mark the button as unshared.
         */
        markAsUnshared: function() {
            this.$container.data('status', '');
            this.$container.removeClass('bp-share-posts-shared');

            if (this.shareIcon.length) {
                var $icon = jQuery(this.shareIcon);

                this.$container.find('.bp-share-posts-icon').remove();
                this.$container.prepend($icon);
            }

            if (this.shareLabel.length) {
                this.$container.find('.bp-share-posts-label').html(this.shareLabel);
            }
        }
    });

    $.fn[ pluginName ] = function(options) {
        return this.each(function() {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" +
                    pluginName, new Plugin(this, options));
            }
        });
    };

})(jQuery, window, document);
