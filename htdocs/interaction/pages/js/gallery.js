define(['jquery-loader', 'gridder', 'local', 'jquery.searchbox'], function(jq, gridder, local) {
    // Reference to jQuery.
    var _$ = jq;

    var _gridder = gridder;
    var _local = local;

    // Module options.
    var _opts = {};
    var _timer = null;

    var get_active_type_buttons = function() {
        return _$('.view-types button[class*="active"]');
    };

    var get_all_type_buttons = function() {
        return _$('.view-types button');
    };

    var get_active_shared_buttons = function() {
        return _$('.view-shared button[class*="active"]');
    };

    var get_selected_shared = function() {
        var active = get_active_shared_buttons().map(function() {
            return _$(this).attr('id');
        }).toArray();

        return active;
    };

    var get_selected_types = function() {
        var active = get_active_type_buttons().map(function() {
            if (!_$(this).hasClass('all')) {
                return _$(this).attr('id');
            }
        }).toArray();

        //show all if none is selected
        if (!active.length) {
            active = get_all_type_buttons().map(function() {
                if (!_$(this).hasClass('all')) {
                    return _$(this).attr('id');
                }
            }).toArray();
        }

        return active;
    };

    var do_gallery_search = function() {
        _$.searchbox.process(_$('#gallery-search').val());
    };

    var init_student_search_field = function () {
        var field = _$('#student-search');

        field.keyup(function() {
            var val = _$.trim(field.val());
            var previous = _$(this).data('previousvalue');

            if (!previous || val !== previous) {
                if (_timer) {
                    clearTimeout(_timer);
                }

                _timer = setTimeout(apply_gallery_filter, 250);
                _$(this).data('previousvalue', val);
            }
        });
    };

    // Creates the page event observers.
    var init_observers = function() {
        // Gallery institution, group, user filters
        _$('#search_institution').on('change', apply_gallery_filter);
        _$('#search_group').on('change', apply_gallery_filter);
        _$('#search_student').on('change', apply_gallery_filter);

        init_student_search_field();

        _$('.view-types button').on('click', function() {
            _gridder.set_page(0);
            _gridder.tag_selection_changed(_$(this), do_gallery_search);
        });

        _$('.view-shared button').on('click', function() {
            _gridder.set_page(0);
            _gridder.sharing_selection_changed(_$(this), do_gallery_search);
        });

        // Gallery Search field
        init_search_field();

        // Order by -buttons.
        _$('.sort-by input[name="sortpagesby"]').on('change', function () {
            _gridder.set_page(0);
            do_gallery_search();
        });

        init_copying();
    };

    // Enables the functionality to copy copyable collections to learning
    // objects.
    var init_copying = function () {
        _$('html').on('click', 'a.copytolearningobject', function (evt) {
            evt.preventDefault();

            var id = _$(evt.target).parents('ul').data('itemid');
            var params = { id: id };
            var url = window.config.wwwroot + 'interaction/learningobject/createfromcollection.json.php';

            sendjsonrequest(url, params, 'post', function (resp) {
                window.location.href = window.config.wwwroot +
                        'interaction/learningobject/index.php';
            });
        });
    };

    var apply_gallery_filter = function() {
        _gridder.set_page(0);
        do_gallery_search();
    };

    var init_search_field = function() {
        if (_opts.returns) {
            var url = window.config.wwwroot + 'interaction/pages/returnssearch.json.php?returns=1&limit=' + _gridder.get_items_per_page();
        }
        else {
            var url = window.config.wwwroot + 'interaction/pages/sharedsearch.json.php?limit=' + _gridder.get_items_per_page();
        }


        _$('#gallery-search').searchbox({
            url: url,
            param: 'query',
            liveparams: {
                sortby: function () {
                    return _$('input[name="sortpagesby"]:checked').val();
                },
                institution: function() {
                    return _$('#search_institution').val();
                },
                groups: function() {
                    if (_$('#search_group').length > 0) {
                       return _$('#search_group').val();
                    }
                    else {
                        return 0;
                    }
                },
                student: function() {
                    if (_$('#search_student').length > 0) {
                        return _$('#search_student').val();
                    }
                    else {
                        return 0;
                    }
                },
                ownerquery: function() {
                    return _$('#student-search').val();
                },
                offset: function() {
                    return _gridder.get_page() * _gridder.get_items_per_page();
                },
                types: get_selected_types,
                shared: get_selected_shared
            },
            callback: handle_gallery_search_results,
            onkeyup: function() {
                _gridder.set_page(0);
            },
            delay: 250,
            loading_css: '#search-spinner'
        });
    };

    var handle_gallery_search_results = function(data) {
        _gridder.replace(data.message.html, function () {
            _gridder.get_grid().shuffle('shuffle', function ($el) {
                return true;
            }, _gridder.get_sort_opts());

            var studentselect = data.message.students;
            data.message.students = null;
            var groupselect = data.message.groups;
            data.message.groups = null;

            if (!_$.isEmptyObject(groupselect) && _$('#search_group').length > 0) {
                group_selection(groupselect);
            }

            if (!_$.isEmptyObject(studentselect) && _$('#search_group').length > 0) {
                student_selection(studentselect);
            }

            _gridder.update_pagination(data.message.total);
        });
    };

    //changes select for students
    var student_selection = function(studentselect) {
        var selectedOption = _$("#search_student option:selected");
        var html = [];

        _$('#search_student').get(0).options.length = 0; // Clear the list

        if (studentselect) {
            _$.each(studentselect, function(index, student) {
                html.push('<option value="' + index + '"' +
                        (index == selectedOption.attr('value') ? ' selected="selected"' : '') +
                        '>' + student + '</option>');

            });

            _$('#search_student').html(html.join(''));
        }

        studentselect = [];
    };

    //changes select for groups
    var group_selection = function(groupselect) {
        var selectedOption = _$("#search_group option:selected");
        var firstOption = _$("#search_group option:first-child").removeAttr('selected');

        _$('#search_group').get(0).options.length = 0; // Clear the list

        if (groupselect) {
            _$.each(groupselect, function(index, group) {
                if (index == selectedOption.attr("value")) {
                    _$('#search_group').append(_$("<option></option>").attr("value", index).text(group).attr("selected", "selected"));
                }
                else {
                    _$('#search_group').append(_$("<option></option>").attr("value", index).text(group));
                }
            });

        }
        var keys = _$.map(groupselect, function(value, key) {
            return key;
        });
        var selected = selectedOption.val();
        var selected_in_options = _$.grep(keys, function(n, i) {
            return n == selected;
        });
        selected_in_options = _$.makeArray(selected_in_options);

        if (!selected_in_options.length > 0) {
            firstOption.attr("selected", "selected");
            _$('#search_group').val(0);
        }

        selectedOption = null;
        groupselect = null;
    };

    return {
        init: function() {
            _gridder.init(this);
            _opts = _$.extend({
                fulltextsearch: 1,
                groupid: -1,
                returns: 0,
            }, arguments[0] || {});

            init_observers();

            _gridder.change_page();
        },

        do_search: function () {
            do_gallery_search();
        },

        get_viewid: function (item) {
            var ret = item.data('id');

            // Collection view id is stored in item's id-attribute, ie.
            // "collection-xx". The data-id contains the collection id.
            if (item.hasClass('collection-item')) {
                ret = item.attr('id').split('-')[1];
            }

            return ret;
        }
    };
});