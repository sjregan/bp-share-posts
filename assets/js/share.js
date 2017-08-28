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
        this.sharedIcon = this.$container.data('shared-icon');
        this.sharedLabel = this.$container.data('shared-label');
        this.postId = parseInt(this.$container.data('post-id'));

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
            
            if (this.$container.hasClass('bp-share-posts-shared') || this.$container.hasClass('bp-share-posts-loading')) {
                return;
            }

            this.$container.addClass('bp-share-posts-loading');

            var self = this;
            var data = {
                action: 'bp_share_post',
                post_id: this.postId
            };

            jQuery
                .post(ajaxurl, data, function(response) {
                    if (response.success) {
                        if (self.sharedIcon.length) {
                            var $icon = jQuery(self.sharedIcon);

                            self.$container.find('.bp-share-posts-icon').remove();
                            self.$container.prepend($icon);
                            self.$container.addClass('bp-share-posts-shared');
                        }

                        if (self.sharedLabel.length) {
                            self.$container.find('.bp-share-posts-label').html(self.sharedLabel);
                        }
                    }
                }, 'json')
                .always(function () {
                    self.$container.removeClass('bp-share-posts-loading');
                });
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
