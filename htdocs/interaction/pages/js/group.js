/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage TODO
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2014 Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define(['jquery-loader', 'gridder', 'local', 'jquery.searchbox'], function(jq, gridder, local) {
    // Reference to jQuery.
    var _$ = jq;
    var _gridder = gridder;
    var _local = local;
    var _opts = {};

    // Hides/shows the group tags based on the group type selection.
    var toggle_group_tags = function() {
        var show_tags = _$.inArray(_$('#group-type').val(), ['allmygroups', 'admin']) >= 0;
        _$('.group-tags')[show_tags ? 'show' : 'hide']();
    };

    // Group filters have changed, resets the page number and performs the
    // search.
    var group_filters_changed = function() {
        _gridder.set_page(0);
        do_group_search();
    };

    // Perform the group search manually.
    var do_group_search = function() {
        _$.searchbox.process(_$('#group-search').val());
    };

    // Should be called every time the filter settings change. Changes the
    // states of the filter buttons and shuffles the list.
    var tag_selection_changed = function(btn, callback) {
        // User pressed the "All"-button.
        if (btn.hasClass('all')) {
            btn.addClass('active');
            btn.siblings().removeClass('active');
        }
        else {
            btn.toggleClass('active');
            var all_button = btn.siblings('.all').first();

            if (btn.hasClass('active')) {
                all_button.removeClass('active');
            }

            var active_buttons = _$(btn.get()[0].parentElement).children('.active');

            // None of the tags are selected, activate the "All"-button.
            if (active_buttons.size() === 0) {
                all_button.addClass('active');
            }
        }

        callback();
    };

    var apply_inst_filter = function($el) {
        var groupinst = _$('#group-institution').val();
        //show everything
        if (groupinst == 0) {
            return true;
        }
        //only show myinst
        else {
            if ($el.hasClass('myinst') || $el.hasClass('gridder-new')) {
                return true;
            }
            else {
                return false;
            }
        }
    };

    // Initializes the group search box.
    var init_group_searchbox = function() {
        _$('#group-search').searchbox({
            url: window.config.wwwroot + 'interaction/pages/groupsearch.json.php?limit=' + _gridder.get_items_per_page(),
            param: 'query',
            liveparams: {
                filter: function() {
                    return _$('#group-type').val();
                },
                category: function() {
                    return _$('#group-category').val() || -1;
                },
                tags : function() {
                    var activetags = _$('.group-tags button[class="active"]').map(function() {
                        return _$(this).text();
                    }).toArray();
                    return activetags;
                },
                sort : function() {
                    return _$('input[name="sortpagesby"]:checked').val();
                },
                // implemented institution filter
                institution: function() {
                    return _$('#group-institution').val();
                },
                offset: function() {
                    return _gridder.get_page() * _gridder.get_items_per_page();
                }
            },
            callback: handle_group_search_results,
            onkeyup: function() {
                _gridder.set_page(0);
            },
            delay: 350,
            loading_css: '#search-spinner'
        });
    };

    // Group search is done. Displays the results and updates the pagination.
    var handle_group_search_results = function(data) {
        _gridder.replace(data.message.html, function () {
            _gridder.get_grid().shuffle('shuffle', function ($el) {
                return true;
            }, _gridder.get_sort_opts());
            
            _gridder.update_pagination(data.message.total);
        });
    };

    var init_observers = function() {
        _$('.gridder-new').click(function() {
            document.location.href = window.config.wwwroot + 'group/edit.php';
        });

        // Group type select box.
        _$('#group-type').on('change', function() {
            toggle_group_tags();
            group_filters_changed();
        });

        // Tag buttons.
        _$('.group-tags').on('click', 'button', function() {
           tag_selection_changed(_$(this), group_filters_changed);
        });
            
        _$('.sort-by input[name="sortpagesby"]').on('change', function() {
            group_filters_changed();
        });

        // Group category select box.
        _$('#group-category').on('change', group_filters_changed);

        //Group institution select box
        _$('#group-institution').on('change', group_filters_changed);

        init_group_searchbox();
    };

    return {
        init: function() {
            _gridder.init(this);
            _opts = _$.extend({
                fulltextsearch: 1,
                groupid: -1,
                total: 0
            }, arguments[0] || {});

            init_observers();
//            toggle_group_tags();
            do_group_search();
        },
        
        do_search: function () {
            do_group_search();
        }
    };
});