define(['jquery-loader', 'local', 'jquery.autosize', 'jquery-ui', 'bootstrap'], function(jq, local) {
    _$ = jq;
    _local = local;
    _container = null;

    var init_template_field_selector = function() {
        _$('#studyjournaltemplate_addfield').popover({
            html: true,
            placement: 'right',
            content: function() {
                return _$('#template-field-selector').show();
            }
        });

        _$('#template-field-selector a').click(function(evt) {
            evt.preventDefault();

            var type = _$(this).data('type');
            var field = _$('.template-field-template.template-field-' + type).clone();

            _container.append(field);

            field.removeClass('template-field-template').show('fast');

            // Not quite pluggable way to initialize the field, but what the heck.
            switch (type) {
                case 'text':
                    field.find('textarea').autosize().focus();
                    break;
                case 'vibe':
                    field.find('input').focus();
                    break;
            }

            _$('#studyjournaltemplate_addfield').popover('hide');
            update_row_indices();
        });
    };

    var remove_template_field = function(evt) {
        evt.preventDefault();

        if (window.confirm(get_string('confirmremovefield'))) {
            _$(this).parents('.template-field').remove();
        }

        update_row_indices();
    };

    var update_row_indices = function() {
        _container.find('.template-field').each(function(index) {
            _$(this).removeClass('r0 r1').addClass('r' + (index % 2));
        });
    };

    var preview_template = function (evt) {
        try {
            var title = _$.trim(_$('#studyjournaltemplate_title').val());
            var templatefields = get_template_data();

            var data = {
                title: title,
                fields: JSON.stringify(templatefields)
            };

            sendjsonrequest(window.config.wwwroot + 'artefact/studyjournal/preview.json.php', data, 'post', function(resp) {
                _$('#template-preview-modal .modal-content').html(resp.html);
                _$('#template-preview-modal').modal('show');

                _local.add_tinymce('#template-preview-modal .modal-content textarea');
            });
        }
        catch (e) {
            show_error(e);
        }
    };

    var get_template_data = function () {
        var fields = [];

        _container.find('.template-field').each(function(index) {

            // Handle textareas.
            if (_$(this).hasClass('template-field-text')) {
                val = _$.trim(_$(this).children('textarea').val());

                if (val === '') {
                    throw get_string('erroremptytemplatefield');
                }

                fields.push({type: 'text', weight: index, value: val});
            }

            // Handle vibe meters.
            else if (_$(this).hasClass('template-field-vibe')) {
                var val = _$.trim(_$(this).children('input').val());

                if (val === '') {
                    throw get_string('erroremptytemplatefield');
                }

                fields.push({type: 'vibe', weight: index, value: val});
            }
        });

        return fields;
    };

    var save_template = function(evt) {
        evt.preventDefault();

        var title = _$.trim(_$('#studyjournaltemplate_title').val());
        var error = '';
        var fields = [];

        if (title === '') {
            error = get_string('errormissingtemplatetitle');
        }

        if (!error) {
            try {
                fields = get_template_data();
            }
            catch (e) {
                error = e;
            }
        }

        if (!error && fields.length === 0) {
            error = get_string('errornotemplatefieldsadded');
        }

        if (error) {
            show_error(error);
            return;
        }

        var data = {
            title: title,
            fields: JSON.stringify(fields),
            tags: $j('#studyjournaltemplate_tags').val()
        };

        if (_$('#studyjournaltemplate_templateid').size() > 0) {
            data.id = _$('#studyjournaltemplate_templateid').val();
        }

        // Remove old messages.
        _$('#messages').children().remove();

        sendjsonrequest('save.json.php', data, 'post', function(resp) {
            var url = window.config.wwwroot + 'artefact/studyjournal/tutor/templates.php';
            window.location.href = url + '?saved=1';
        });
    };

    var show_error = function(msg) {
        var error = _$('<div></div>').addClass('error').text(msg);
        _$('#messages').hide().html('').append(error).show('fast');
    };

    return {
        init: function() {
            if (window.config.handheld_device){
                _local.init_touchhandler();
            }
            _container = _$('#template-container');
            _container.sortable({
                handle: '.handle',
                items: '.template-field',
                axis: 'y',
                update: update_row_indices
            });

            init_template_field_selector();

            _container.on('click', '.remove-field', remove_template_field);
            _$('#save-template').click(save_template);

            // Make textareas autoresizable.
            _$('#template-container .template-field textarea').autosize();

            // Empty the modal when closing.
            _$('#template-preview-modal').on('hidden.bs.modal', function() {
                _$(this).removeData('bs.modal');
            });

            _$('#preview-template').click(function(evt) {
                evt.preventDefault();
                preview_template();
            });

            update_row_indices();
        }
    };
});