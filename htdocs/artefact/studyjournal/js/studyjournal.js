define(['jquery-loader', 'gridder', 'local', 'jquery.searchbox'], function(jq, gridder, local) {
    var _$ = jq;
    var _gridder = gridder;
    var _local = local;
    var _opts = {};
    var _timer = null;
    var _imagelist = {};

    var add_journal_template_preview_button = function() {
        var previewbutton = _$('<button class="btn">' + get_string('previewtemplate') + '</button>');

        if (_$('#studyjournal_template').size() > 0) {
            _$('#studyjournal_template').after(previewbutton);
        }
        else {
            _$('#studyjournal_template_container td').append(previewbutton);
        }

        _$('#template-preview-modal').on('hidden.bs.modal', function() {
            _$(this).removeData('bs.modal').find('.modal-content').empty();
        });

        _$('#template-preview-modal').on('loaded.bs.modal', function() {
            _local.add_tinymce('.template-preview textarea');
        });

        previewbutton.click(function(evt) {
            evt.preventDefault();
            var templateid = _$('#studyjournal_template').size() > 0
                    ? _$('#studyjournal_template').val()
                    : _$('#studyjournal_template_container td input').val();

            _$('#template-preview-modal').modal({
                remote: window.config.wwwroot + 'artefact/studyjournal/previewtemplate.php?id=' +
                        templateid
            });
        });
    };

    var show_imagewindow = function () {
        var t = tinyMCE.activeEditor;

        _imagelist = attached_image_list();

        var template = [];

        template['file'] = window.config['wwwroot'] + 'artefact/blog/image_popup.php';
        template['width'] = 355;
        template['height'] = 275 + (tinyMCE.isMSIE ? 25 : 0);

        // Language specific width and height addons
        template['width'] += t.getLang('lang_insert_image_delta_width', 0);
        template['height'] += t.getLang('lang_insert_image_delta_height', 0);
        template['inline'] = true;

        t.windowManager.open(template);
    };

    // Override the image button on the tinyMCE editor.  Rather than the
    // normal image popup, open up a modified popup which allows the user
    // to select an image from the list of image files attached to the
    // post.

    // Get all the files in the attached files list that have been
    // recognised as images.  This function is called by the the popup
    // window, but needs access to the attachment list on this page
    //
    // Copied from blog/post.php
    var attached_image_list = function () {
        var images = [];
        var attachments = studyjournalentry_filebrowser.selecteddata;
        for (var a in attachments) {
            if (attachments[a].artefacttype == 'image' || attachments[a].artefacttype == 'profileicon') {
                images.push({
                    'id': attachments[a].id,
                    'name': attachments[a].title,
                    'description': attachments[a].description ? attachments[a].description : ''
                });
            }
        }
        return images;
    };

    var attach_portfolio_pages = function() {
        _$('#attach-items').click(function() {
            var obj = {c: [], v: []};

            obj.c = _$('#attach-collections input:checked').map(function() {
                return parseInt(_$(this).val());
            }).toArray();

            obj.v = _$('.attach-views input:checked').map(function() {
                return parseInt(_$(this).val());
            }).toArray();

            // PENDING: I can haz < IE8 support?
            _$('#studyjournalentry_attached').val(JSON.stringify(obj));
            _$('#attach-from-portfolio-modal').modal('hide');

            _$('.attachedpages').html(get_attached_items_list());
        });

        _$('#attach-from-portfolio-modal').on('show.bs.modal', function() {
            var collections = [];
            var views = [];

            try {
                var attached = JSON.parse(_$('#studyjournalentry_attached').val());
                collections = attached.c;
                views = attached.v;
            }
            catch (e) {
            }

            for (var i = 0; i < collections.length; i++) {
                _$('#link-collection-' + collections[i]).attr('checked', true);
            }

            for (var i = 0; i < views.length; i++) {
                _$('#link-view-' + views[i]).attr('checked', true);
            }
        });

        sendjsonrequest('views.json.php', {}, 'get', function(resp) {
            _$('#attach-from-portfolio-modal .modal-body').html(resp.html);
            _$('#attach-from-portfolio-modal').modal();
        });
    };

    var get_attached_items_list = function() {
        var itemhtml = '';
        var listitem = function() {
            return '<li><a href="' + _$(this).data('url') + '" target="_blank">' + _$(this).data('title') + '</a></li>';
        };

        itemhtml += _$('#attach-collections input:checked').map(listitem).toArray().join('');
        itemhtml += _$('.attach-views input:checked').map(listitem).toArray().join('');

        if (itemhtml !== '') {
            itemhtml = '<ul>' + itemhtml + '</ul>';
        }
        else {
            itemhtml = get_string('noattachedpages');
        }

        return itemhtml;
    };

    var delete_template = function(templateid) {
        if (!window.confirm(get_string('confirmremovetemplate'))) {
            return;
        }

        sendjsonrequest('delete.json.php', {id: templateid}, 'post',
                function() {
                    delete_success(templateid);
                });
    };

    var delete_journal = function(journalid) {
        if (!window.confirm(get_string('confirmremovejournal'))) {
            return;
        }

        sendjsonrequest('delete.json.php', {id: journalid}, 'post', function() {
            delete_success(journalid);
        });

    };

    var delete_success = function(templateid) {
        var items = _$('.gridder-item[data-id="' + templateid + '"]');
        _gridder.get_grid().shuffle('remove', items);
        _$('.popover-toggle').popover('destroy');
    };

    var filter_templates_by_context = function () {
         var context = _$("#template-type button[class=active]").data("value");
        _gridder.get_grid_elements().each(function () {
            var todisable = context !== 'all' && _$(this).data('templatetype') !== context;
            _gridder.disable_item(_$(this), todisable);
        });
    };

    var search_own_journals = function () {
        _gridder.update_pagination(_gridder.filter());
    };

    var init_shared_search_field = function () {
        var url = 'search.json.php?limit=' + _gridder.get_items_per_page();

        _$('#page-search').searchbox({
            url: url,
            param: 'query',
            liveparams: {
                sortby: function () {
                    return _$('input[name="sortpagesby"]:checked').val();
                },
                offset: function() {
                    return _gridder.get_page() * _gridder.get_items_per_page();
                },
                name: function () {
                    return _$('#student-search').val();
                },
                shared: _gridder.get_selected_publicity,
                tags: _gridder.get_selected_tags
            },
            callback: handle_search_results,
            onkeyup: function() {
                _gridder.set_page(0);
            },
            delay: 250,
            loading_css: '#search-spinner'
        });
    };

    var init_student_search_field = function () {
        var field = _$('#student-search');

        field.keyup(function() {
            var val = _$.trim(field.val());
            var previous = _$(this).data('previousvalue');

            if (!previous || val !== previous) {
                _gridder.set_page(0);

                if (_timer) {
                    clearTimeout(_timer);
                }

                _timer = setTimeout(_do_search, 250);
                _$(this).data('previousvalue', val);
            }
        });
    };

    var handle_search_results = function (data) {
        _gridder.replace(data.message.html, function () {
            _gridder.get_grid().shuffle('shuffle', function ($el) {
                return true;
            }, _gridder.get_sort_opts());

            _gridder.update_pagination(data.message.total);
        });
    };

    var _do_search = function () {
        _$.searchbox.process(_$('#page-search').val());
    };

    var copy_template = function(templateid, context) {
        sendjsonrequest('../copy.json.php', {id: templateid, context: context}, 'post', function(resp) {
            window.location.href = window.config.wwwroot + 'artefact/studyjournal/tutor/edit.php?id=' + resp.id;
        });
    };

    return {

        /**
         * Initializes the study journal template grid.
         *
         * @param {type} context
         */
        init_template_grid: function(context) {
            _gridder.init(this, {
                on_publicity_changed: function () {
                    _gridder.set_page(0);
                    search_own_journals();
                },
                faux_pagination: true
            });

            if (_$('#template-type').size() > 0) {
                _$('#template-type button').click(function (evt) {
                    _gridder.set_page(0);
                    _$(this).addClass('active').siblings().removeClass('active');
                    filter_templates_by_context();
                    _gridder.update_pagination(_gridder.filter());
                });
            }

            _$('html').on('click', '.delete-template', function(evt) {
                evt.preventDefault();
                delete_template(_$(evt.target).parents('ul').data('itemid'));
            });

            _$('.view-tags button').on('click', function() {
                _gridder.set_page(0);
                _gridder.tag_selection_changed(_$(this), search_own_journals);
            });

            // Order by -buttons.
            _$('.sort-by input[name="sortpagesby"]').on('change', function () {
                _gridder.set_page(0);
                search_own_journals();
            });

            _$('html').on('click', '.copy-template', function(evt) {
                evt.preventDefault();
                var templateid = _$(evt.target).parents('ul').data('itemid');
                copy_template(templateid, context);
            });

            _gridder.update_pagination(_gridder.filter());
        },

        /**
         * Initializes the teacher's template list.
         */
        init_teacher_template_list: function() {
            this.init_template_grid('teacher');

            _$('.gridder-new').click(function() {
                document.location.href = window.config.wwwroot +
                        'artefact/studyjournal/tutor/new.php';
            });
        },

        /**
         * Initializes the list of own study journals.
         */
        init_student: function() {
            _opts = _$.extend({
                total: 0
            }, arguments[0] || {});

            _gridder.init(this, {
                on_publicity_changed: function () {
                    _gridder.set_page(0);
                    search_own_journals();
                },
                faux_pagination: true
            });

            _$('.gridder-new').click(function() {
                document.location.href = window.config.wwwroot +
                        'artefact/studyjournal/student/edit.php';
            });

            _$('html').on('click', '.delete-journal', function(evt) {
                evt.preventDefault();
                delete_journal(_$(evt.target).parents('ul').data('itemid'));
            });

            _$('html').on('click', '.create-view', function (evt) {
                evt.preventDefault();
                var id = _$(evt.target).parents('ul').data('itemid');
                var type = "studyjournal";
                sendjsonrequest('create_view.json.php', { id: id, type: type }, 'post', function (resp) {
                    var backto = _$(location).attr('href');
                    backto = backto.replace(window.config.wwwroot, '');
                    var viewid = resp.view;
                    window.location.href = window.config.wwwroot + 'view/access.php?new=1&id=' + viewid + '&backto='+backto;
                });
            });

            _$('.view-tags button').on('click', function() {
                _gridder.set_page(0);
                _gridder.tag_selection_changed(_$(this), search_own_journals);
            });

            // Order by -buttons.
            _$('.sort-by input[name="sortpagesby"]').on('change', function () {
                _gridder.set_page(0);
                search_own_journals();
            });

            search_own_journals();
        },

        /**
         * Initializes the new journal page.
         */
        init_new_journal: function() {
            add_journal_template_preview_button();
        },

        /**
         * Initializes the study journal page.
         */
        init_journal: function() {
        },

        /**
         * Initializes the study journal post page.
         */
        init_journal_post: function() {
            _local.add_tinymce('#studyjournalentry textarea', {
                setupfunc: function (ed) {
                    ed.addCommand('mceImage', show_imagewindow);
                }
            });

            _$('#studyjournalentry_portfoliolink').click(attach_portfolio_pages);
        },

        /**
         * Initializes the shared journals page.
         */
        init_shared: function() {
            _gridder.init(this);
            _opts = _$.extend({
                total: 0
            });

            // Order by -buttons.
            _$('.sort-by input[name="sortpagesby"]').on('change', function () {
                _gridder.set_page(0);
                _do_search();
            });

            init_shared_search_field();
            init_student_search_field();

            _gridder.change_page();
        },

        /**
         * Callback to perform the journal search in shared journals.
         */
        do_search: function () {
            _do_search();
        },

        /**
         * Callback called after the publicity selection has changed.
         */
        publicity_changed: function () {
            _gridder.set_page(0);
            _do_search();
        },

        get_imagelist: function () {
            return _imagelist;
        }
    };
});