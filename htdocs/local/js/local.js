define(['jquery-loader'], function (jq) {
    _$ = jq;

    // Feel free to do local customizations here.
    _$(document).ready(function () {
    });

    // Add support for mobile devices drag and drop.
    var touchHandler = function (event) {
        var touch = event.changedTouches[0];

        var simulatedEvent = document.createEvent("MouseEvent");
        simulatedEvent.initMouseEvent({
            touchstart: "mousedown",
            touchmove: "mousemove",
            touchend: "mouseup"
        }[event.type], true, true, window, 1,
                touch.screenX, touch.screenY,
                touch.clientX, touch.clientY, false,
                false, false, false, 0, null);

        touch.target.dispatchEvent(simulatedEvent);

        var $target = _$(event.target);
        if ($target.hasClass('handle')) {
            event.preventDefault();
            _$(':focus').trigger('blur');
        }
    };

    return {
        add_tinymce: function (selector) {
            var opts = _$.extend({
                setupfunc: _$.noop,
                autoresize: false,
                images: true,
                advanced: true
            }, arguments[1] || {});

            if (typeof (tinyMCE) != 'undefined') {
                var plugins = ['emotions','fullscreen','inlinepopups','paste'];

                // arguments[2] is noautoresize (= true to disable autoresizing)
                if (opts.autoresize) {
                    plugins.push('autoresize');
                }

                var advanced_buttons = 'bold,italic,underline,separator,bullist,' +
                    'numlist,separator,image,emotions,separator,link,unlink,' +
                    'separator,fullscreen';
                var advanced_fullscreen_buttons1 = 'bold,italic,underline,' +
                        'strikethrough,separator,forecolor,backcolor,separator,' +
                        'fontselect,separator,fontsizeselect,formatselect,' +
                        'separator,justifyleft,justifycenter,justifyright,' +
                        'justifyfull,separator,hr,emotions,image,cleanup,' +
                        'separator,link,unlink,separator,code,fullscreen';

                if (!opts.images) {
                    advanced_buttons = advanced_buttons.replace(',image', '');
                    advanced_fullscreen_buttons1 = advanced_fullscreen_buttons1.replace(',image', '');
                }
                if (!opts.advanced) {
                    advanced_buttons = '';
                    advanced_fullscreen_buttons1 = '';
                }

                tinymce.init({
                    selector: selector,
                    theme: 'advanced',
                    content_css: get_themeurl('style/tinymce.css'),
                    width: '100%',
                    plugins: plugins.join(','),
                    theme_advanced_buttons1: advanced_buttons,
                    fullscreen_settings: {
                        plugins: 'table,emotions,inlinepopups,paste,fullscreen',
                        theme_advanced_buttons1: advanced_fullscreen_buttons1,
                        theme_advanced_buttons2: 'undo,redo,separator,bullist,numlist,separator,tablecontrols,separator,cut,copy,paste,pasteword'
                    },
                    setup: opts.setupfunc,
                    file_browser_callback: 'ourFileBrowser',
                    document_base_url: window.config.wwwroot,
                    convert_urls: true,
                    urlconverter_callback: 'custom_urlconvert',
                    relative_urls: false,
                    language: window.config.lang,
                    invalid_elements: 'form,input,button,fieldset,legend,optgroup,option,select,textarea'
                });
            }
        },
        init_touchhandler: function () {
            document.addEventListener("touchstart", touchHandler, true);
            document.addEventListener("touchmove", touchHandler, true);
            document.addEventListener("touchend", touchHandler, true);
            document.addEventListener("touchcancel", touchHandler, true);
        },
        form_to_object: function (form) {
            var obj = {};

            _$(form).serializeArray().map(function (item) {
                obj[item.name] = item.value;
            });

            return obj;
        },

        identify: function (elements, prefix) {
            var i = 0;

            prefix = prefix || 'el';

            return _$(elements).each(function() {
                if (this.id) {
                    return;
                }

                do {
                    i++;
                    var id = prefix + '_' + i;
                } while (_$('#' + id).length > 0);

                _$(this).attr('id', id);
            });
        }
    };
});
