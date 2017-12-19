/**
 * To use ControlPanel it needs jQuery
 *    https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js
 * (Optional) Add Noty for messaging
 *    https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.css
 *    https://cdnjs.cloudflare.com/ajax/libs/noty/3.1.4/noty.min.js
 *
 * (Recommended) Change page to default home if not set with:
 * <code>
 * if(location.hash.length === 0) {
 *     location.replace('#/home');
 * }
 * </code>
 *
 * And then call this class with
 * new ControlPanel({
 *     key: value
 * });
 *
 * @param options
 * @constructor
 */
function ControlPanel(options) {
    var defaultOptions = {
        elementLoader: null,
        elementPage: null,
        debug: false,
        ajaxPageTarget: null,
        elementTask: null,
        controlPanelPageSystem: 'href' // It can either be href, hash or url
    };

    this.config = Object.assign(defaultOptions, options);

    this.startLoading = function() {
        $(this.config.elementLoader).show();
    };

    this.stopLoading = function() {
        $(this.config.elementLoader).hide();
    };

    this.changePage = function(sPageName) {
        if(typeof this.config.ajaxPageTarget === 'object') {
            if(this.config.debug)
                console.error('[ControlPanel] Can\'t change page because no url has been defined');
            return;
        }

        this.startLoading();
        $.ajax({
            controlPanel: this, // Needed to access inside the function

            url: this.config.ajaxPageTarget,
            data: {
                p: sPageName
            },
            async: true,
            complete: function() {
                this.controlPanel.stopLoading();
            },
            success: function(data) {
                $(this.controlPanel.config.elementPage).empty().append($(data));

                if(this.controlPanel.config.debug)
                    console.debug('[ControlPanel] Page changed to "' + sPageName + '"');
            },
            error: function() {
                if (Noty) {
                    new Noty({
                        text       : "Error while loading page",
                        type       : "error",
                        layout     : "bottomRight",
                        timeout	   : 3000,
                        maxVisible : 3
                    }).show();
                }
                console.error('[ControlPanel] Could not load page "' + sPageName + '"');
            }
        });
    };

    this.processPageChange = function(page) {
        this.startLoading();
        this.changePage(page);
    };

    // We now have to add all the events to respond to page changing
    var controlPanel = this;
    if (this.config.controlPanelPageSystem === 'hash') {
        if('hashchange' in window) {
            if(this.config.debug)
                console.debug('[ControlPanel] Your browser supports onHashChange');
        }

        $(window).bind('hashchange', function(e) {
            var page = location.hash;
            page = page.substr(2);
            controlPanel.processPageChange(page);
        });

        // Current button has to be set active (after document is loaded)
        $(document).ready(function() {
            var url = location.hash;
            url = url.substr(2);
            url && $('a[href="#/' + url + '"]').closest('li').addClass('active');
        });

        // Since the event is only triggered when the hash changes, we need to trigger
        // the event now, to handle the hash the page may have loaded with.
        $(window).trigger('hashchange');

    } else if(this.config.controlPanelPageSystem === 'url') {
        $('*[data-toggle="page"]').click(function() {
            var page = $(this).attr('href');
            page = page.substr(2);
            controlPanel.processPageChange(page);
        });
    }

    // Add handler to button click
    $(document).on('click', 'li > a', function(){
        var parent = $(this).parent();

        // Ensure link isn't just a dropdown menu link
        if (!parent.hasClass('dropdown')) {
            $('li.active').removeClass('active');

            $(this).closest('li').addClass('active');
        }
    });
}
/*
(function($){
    'use strict';

    $.ControlPanelTask = {

        config: {
            lastTask: 0,
            debug: false
        },

        init: function(control_panel, title, type) {
            var id = self.config.lastTask + 1;
            control_panel.config.elementTask.append('<li class="task-' + id + '">' +
                '<a href>' +
                '<div>' +
                '<p>' +
                '<strong>' + title + '</strong>' +
                '<span class="pull-right text-muted">0% Complete</span>' +
                '</p>' +
                '<div class="progress progress-striped active">' +
                '<div class="progress-bar progress-bar-' + type + '" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0">' +
                '<span class="sr-only">0% Complete (' + type + ')</span>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</a>' +
                '</li>');

            if(self.config['debug'])
                console.debug('[ControlPanelTag] Added');
        },

        set: function(element, terminal, commands) {
        },

        get: function(element, terminal, commands) {
        },

        remove: function(element, terminal, commands) {
        }

    }

})(jQuery);
*/
