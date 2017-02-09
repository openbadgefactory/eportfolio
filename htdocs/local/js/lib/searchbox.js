// Author: Ryan Heath
// http://rpheath.com

(function($) {
    $.searchbox = {};

    $.extend(true, $.searchbox, {
        settings: {
            url: '/search',
            param: 'query',
            dom_id: '#results',
            delay: 100,
            loading_css: '#loading',
            callback: $.noop,
            onkeyup: $.noop,
            liveparams: {}
        },
        loading: function() {
            $($.searchbox.settings.loading_css).animate({easing: 'linear', opacity: 1});
        },
        resetTimer: function(timer) {
            if (timer) {
                clearTimeout(timer);
            }
        },
        idle: function() {
            $($.searchbox.settings.loading_css).animate({easing: 'linear', opacity: 0});
        },
        process: function(terms) {
            var path = $.searchbox.settings.url.split('?');
            var base = path[0];
            var params = path[1];
            var query_string = '';
            var liveparams = $.searchbox.settings.liveparams;
            var query_params = {};

            query_params[$.searchbox.settings.param] = terms;

            if (params) {
                query_string = [params.replace('&amp;', '&')].join('&');
            }

            // Params that can change between keystrokes.
            for (var key in liveparams) {
                query_params[key] = liveparams[key]();
            }

            var url = [base, '?', query_string].join('');

            $.get(url, query_params, function (data) {
                $.searchbox.settings.callback(data);
            });
        },
        start: function() {
            $(document).trigger('before.searchbox');
            $.searchbox.loading();
        },
        stop: function() {
            $.searchbox.idle();
            $(document).trigger('after.searchbox');
        }
    });

    $.fn.searchbox = function(config) {
        var settings = $.extend(true, $.searchbox.settings, config || {});

        $(document).trigger('init.searchbox');
        $.searchbox.idle();

        return this.each(function() {
            var $input = $(this);

            $(document).ajaxStart(function() {
                $.searchbox.start();
            }).ajaxStop(function() {
                $.searchbox.stop();
            });

            $input
                    .focus()
                    .keyup(function() {
                        var val = $.trim($input.val());
                        var previous = this.previousValue || '';

                        if (val !== previous) {
                            $.searchbox.settings.onkeyup();
                            $.searchbox.resetTimer(this.timer);

                            this.timer = setTimeout(function() {
                                $.searchbox.process(val);
                            }, $.searchbox.settings.delay);

                            this.previousValue = val;
                        }
                    });
        });
    };
})(jQuery);