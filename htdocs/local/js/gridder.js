define(['jquery-loader', 'local', 'jquery.shuffle', 'bootstrap'], function(jq, local) {
    // Reference to jQuery.
    var _$ = jq;
    var _local = local;
    var _grid = null;
    var _context = null;
    // Cache the previous minimum height of the page to prevent layout jumping.
    var _page_min_height = 0;
    var _itemsperpage = 20;
    var _page = 0;
    var _opts = {};
    var _total = 0;

    var init_popovers = function() {
        _grid.on('click', '.popover-toggle', function(evt) {
            var content = _$(this).siblings('.action-items').html();
            // Hide all other popovers. If we call .popover('hide'), the whole
            // popover-thingy in the grid breaks. It's an issue with event
            // delegation and Bootstrap.
            _$('.popover-toggle').not(this).popover('destroy');
            _$(this).popover({
                content: content,
                html: true,
                trigger: 'manual',
                container: 'body',
                placement: 'auto right'
            }).popover('toggle');
            evt.stopPropagation();
        });
        // Hide all popovers when clicking anywhere.
        //Hides the popover element if and only if the element clicked was neither the container element nor one of its descendants.
        _$('html').click(function(e) {
            if (_$(e.target).closest(".popover-content").length === 0) {
                _$('.popover-toggle').each(function() {
                    // Why destroy instead of just hiding? Read the note above.
                    _$(this).popover('destroy');
                });
            }
        });
    };

    var filter_templates = function() {
        var publicity = _public.get_selected_publicity();
        var filter = _$.trim(_$('#template-filter').val().toLowerCase());
        var tags = _public.get_selected_tags();
        var total = 0;

        _grid.shuffle('shuffle', function($el, shuffle) {
            if ($el.hasClass('gridder-new')) {
                return true;
            }

            var show_page = !publicity || publicity === 'all' || $el.data('publicity') === publicity;

            // Do not show grid items that are "disabled" before filtering
            // (usually by scripts calling this filter).
            if ($el.data('disabled')) {
                show_page = false;
            }

            _$(tags).each(function(index, tag) {
                tag = tag + '';

                if (_$.inArray(tag, $el.data('tags')) === -1) {
                    show_page = false;
                    return false;
                }
            });

            if (filter !== '') {
                var tag_found = false;

                _$($el.data('tags')).each(function (index, tag) {
                    tag = tag + '';

                    if (tag.toLowerCase().indexOf(filter) >= 0) {
                        tag_found = true;
                        return false;
                    }
                });

                if ($el.data('title').toLowerCase().indexOf(filter) < 0 &&
                        !tag_found && (!$el.data('description') ||
                        $el.data('description').toLowerCase().indexOf(filter) < 0)) {
                    show_page = false;
                }
            }

            if (show_page) {
                total++;
            }

            return show_page;
        }, _public.get_sort_opts(), _opts.faux_pagination);

        // Apply the local pagination using Shuffle.
        if (_opts.faux_pagination) {
            _public.paginate();
        }

        return total;
    };

    var resize_new_item = function() {
        if (_grid.children('.gridder-item').size() >= 2) {
            _grid.children('.gridder-new').height(_grid.children('.gridder-existing').height());
        }
    };

    // Returns all the active tag buttons.
    var get_active_tag_buttons = function() {
        return _$('.tag-buttons button[class="active"]');
    };

    var init_publicity_buttons = function() {
        _$('#filter-publicity button').click(function(evt) {
            _$(this).addClass('active').siblings().removeClass('active');
            _opts.on_publicity_changed();
        });
    };

    // Boxes publishing from publicity div
    var init_publicity_div = function() {
       _grid.on('click', '.gridder-item .access-editable', function (evt) {
            var gridderitem = _$(this).parent('.gridder-item');
            var viewid = gridderitem.data('view');
            var type = gridderitem.data('type');
            var id = gridderitem.data('id');
            var page = false;
            var backto = _$(window.location).attr('href').toLowerCase();

            backto = backto.replace(window.config.wwwroot.toLowerCase(), '');

            if (type === 'grouppage' || type === 'page' || type === 'gallery') {
                // Get the viewid using the submodule, if possible.
                viewid = _context.get_viewid ? _context.get_viewid(gridderitem) : id;
                page = true;
            }

            // PENDING: Move to studyjournal-module.
            if (gridderitem.hasClass('journal-template')) {
                window.location.href = window.config.wwwroot +
                        'artefact/studyjournal/tutor/access.php?id=' + id;
            }
            // PENDING: Move to ePSP-module.
            else if (gridderitem.hasClass('epsp-template')) {
                window.location.href = window.config.wwwroot +
                        'artefact/epsp/access.php?id=' + id;
            }
            else {
                if (!_$.isNumeric(viewid) && page === false) {
                    //goes to local artefact's create_view.json.php
                    sendjsonrequest('create_view.json.php', { id: id, type: type }, 'post', function (resp) {
                        viewid = resp.view;
                        window.location.href = window.config.wwwroot +
                                'view/access.php?new=1&id=' + viewid + '&backto=' + backto;
                    });
                }
                else {
                    // HACK: Move to collection/learningobject -modules (hidetabs).
                    window.location.href = window.config.wwwroot +
                            'view/access.php?new=1&id=' + viewid + '&backto=' +
                            backto + '&hidetabs=' + (type === 'portfolio' || type === 'learningobject' ? 1 : 0);
                }
            }
        });
    };

    var init_copypage = function () {
        _$('html').on('click', 'a.copypage', function(evt) {
            evt.preventDefault();

            var pageid = null;
            var collectionid = null;
            var groupid = null;

            var id = _$(evt.target).parents('ul').data('itemid');
            var gridderitem = _$('.gridder-item[data-id='+ id +']');
            var forme = _$(evt.target).hasClass('forme');

            if (gridderitem.hasClass('collection-item')) {
                collectionid = gridderitem.data('id');
            }
            else {
                pageid = gridderitem.data('id');
            }

            if (gridderitem.hasClass('grouppage-item')) {
                var groupid = gridderitem.data('groupid');
            }

            var copyparams = _$.extend({
                viewid: pageid,
                collectionid: collectionid,
                groupid: groupid,
                forme: forme
            }, _context['get_copy_params'] ? _context.get_copy_params(gridderitem) : {});

            var url = window.config.wwwroot + 'local/copy_view.json.php';

            sendjsonrequest(url, copyparams, 'post', function (resp) {
                var copyid = resp.copyid; // View or collection id
                var gotourl = _context['get_edit_path'] ? _context.get_edit_path(copyid) : null;

                if (gotourl === null) {
                    if (collectionid) {
                        gotourl =  'collection/edit.php?copy=1&id=' + copyid;
                    }
                    else {
                        gotourl =  'view/edit.php?new=1&id=' + copyid;
                    }
                }

                window.location.href = window.config.wwwroot + gotourl;
            });
        });
    };

    var init_item_top_div = function() {
        _grid.on('click', '.gridder-item .gridder-item-top', function(evt) {
            var href = _$(this).children('h3').children('a').attr('href');
            window.location.href = href;
        });
    };

    // Adds the pagination link elements (pagecount + previous-/next-items) to
    // pagination list.
    var add_pagination_links = function(list, pagecount) {
        list.append(listitem('&laquo;').addClass('previous'));

        for (var i = 1; i <= pagecount; i++) {
            list.append(listitem(i).attr('id', 'page_' + i).addClass('pagelink'));
        }

        list.append(listitem('&raquo;').addClass('next'));
    };

    // Creates a single list item with a specified value.
    var listitem = function(val) {
        return _$('<li></li>').html('<a href="#">' + val + '</a>');
    };

    // Change the group grid page.
    var _change_page = function() {
        var scroll = arguments[0] || false;

        // Scroll up!
        if (scroll) {
            _$('html, body').animate({
                scrollTop: _$('.gridder').offset().top - 30
            }, 500);
        }

        if (_opts.faux_pagination) {
            var total = filter_templates();
            _public.update_pagination(total);
        }
        else if (_context && _context.do_search) {
            _context.do_search();
        }
        else {
            console.warn('Gridder: No search callback defined in module.');
        }
    };

    var publicity_changed = function () {
        if (_context && _context.publicity_changed) {
            _context.publicity_changed();
        }
        else {
            _public.filter();
        }
    };

    // Public stuff
    var _public = {

        /**
         * When removing boxes the page height changes accordingly and it looks
         * disturbing. Sets the min height of the page to be the max height
         * of the list, so the page height can only grow.
         */
        preserve_page_height: function() {
            var height = _$('#main-column-container').height();
            if (height > _page_min_height) {
                _page_min_height = height;
                _$('#main-column-container').css('min-height', _page_min_height);
            }
        },

        /**
         * Initializes the gridder module.
         *
         * @param {type} context
         */
        init: function(context) {
            var self = this;

            _grid = _$('.gridder');
            _context = context;
            _opts = _$.extend({
                item_selector: '.gridder-item',
                speed: 150,
                sizer: _$('.shuffle-sizer'),
                on_publicity_changed: publicity_changed,
                faux_pagination: false
            }, arguments[1] || {});

            resize_new_item();

            _grid.on('shrink.shuffle', this.preserve_page_height);
            _grid.on('removed.shuffle', function () {
                if (_opts.faux_pagination) {
                    self.update_pagination(self.filter());
                    self.refresh();
                }
                else {
                    if (_context && _context.do_search) {
                        _context.do_search();
                    }
                }
            });

            _grid.shuffle({
                itemSelector: _opts.item_selector,
                speed: _opts.speed,
                sequentialFadeDelay: 0,
                sizer: _opts.sizer
            });

            init_popovers();
            init_publicity_buttons();
            init_publicity_div();
            init_item_top_div();
            init_copypage();

            if (_$('#template-filter').size() > 0 && _opts.faux_pagination) {
                _$('#template-filter').keyup(function () {
                    self.set_page(0);
                    self.update_pagination(filter_templates());
                });
            }
        },

        paginate: function () {
            _grid.shuffle('paginate', this.get_sort_opts(), {
                page: _page,
                limit: this.get_items_per_page()
            }, '.gridder-new');
        },

        /**
         * Returns the grid element.
         */
        get_grid: function() {
            return _grid;
        },

        /**
         * Returns the grid elements, without the "create new" element.
         */
        get_grid_elements: function() {
            return _grid.children('.gridder-existing');
        },

        /**
         * Disables (hides) the selected grid element so that it isn't shown
         * next time the grid is filtered.
         *
         * @param {HTMLElement} item The grid item to disable/enable.
         * @param {boolean} disable True to disable, false to enable.
         */
        disable_item: function(item, disable) {
            _$(item).data('disabled', disable);
        },

        /**
         * Filters the grid using the selected filters.
         *
         * @return {int} The number of items to be shown after the filter is
         *      applied.
         */
        filter: function() {
            return filter_templates();
        },

        /**
         * Replaces the contents of the grid with {content}.
         *
         * @param {HTMLElement[]} content An array of new grid elements.
         * @param {Function} callback Called after the content is replaced.
         *      The callback gets the grid as an argument.
         */
        replace: function(content, callback) {
            var items = _$(content).filter('.gridder-item'); // Remove possible text nodes.
            var toadd = [];
            var frag = document.createDocumentFragment();

            items.each(function() {
                toadd.push(_$(this).get(0));
                frag.appendChild(_$(this).get(0));
            });

            this.get_grid_elements().remove();

            _grid.append(frag);
            _grid.shuffle('appended', _$(toadd), true, false);

            callback(_grid);

            // Just in case, if the height differs in new items.
            resize_new_item();

            // Just in case, fixes the wrong y-coordinate of the item below
            // the "Create new" item.
            this.refresh();
        },

        refresh: function () {
            _grid.shuffle('layout');
        },

        /**
         * Returns all the currently selected tags.
         *
         * @return {String[]} The selected tags.
         */
        get_selected_tags: function() {
            return get_active_tag_buttons().map(function() {
                return _$(this).data('value');
            }).toArray();
        },

        /**
         * Called after the tag selection has changed, changes the button
         * states accordingly.
         *
         * @param {HTMLElement} btn The button that was clicked.
         * @param {Function} callback Called after the button states have
         *      changed.
         */
        tag_selection_changed: function(btn, callback) {
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
        },

        sharing_selection_changed: function(btn, callback) {
            // User pressed the "All"-button.
            if (btn.hasClass('all')) {
                btn.addClass('active');
                btn.siblings().removeClass('active');
            }

            // User pressed the "Own"-button
            else if (btn.hasClass('own')) {
                btn.addClass('active');
                btn.siblings().removeClass('active');
            }

            else {
                btn.toggleClass('active');

                var all_button = btn.siblings('.all').first();
                var own_button = btn.siblings('.own').first();

                if (btn.hasClass('active')) {
                    all_button.removeClass('active');
                    own_button.addClass('active');
                }

                var active_buttons = _$(btn.get()[0].parentElement).children('.active');

                // None of the tags are selected, activate the "All"-button.
                if (active_buttons.size() === 0) {
                    all_button.addClass('active');
                }
            }

            callback();
        },

        /**
         * Returns the selected publicity setting, ie. 'public', 'private' etc.
         *
         * @returns {String} The selected publicity.
         */
        get_selected_publicity: function() {
            return _$('#filter-publicity button[class="active"]').data('value');
        },

        /**
         * Returns current sorting options used by the Shuffle-script.
         *
         * @returns {Object} The sorting options.
         */
        get_sort_opts: function() {
            var sortel = _$('input[name="sortpagesby"]');
            var sortby = sortel.size() === 0 ? 'title' : sortel.filter(':checked').val();
            var opts = {};

            if (sortby === 'modified') {
                opts.reverse = true;
                opts.by = function($el) {
                    return $el.data('mtime');
                };
            }
            else if (sortby === 'title') {
                opts.by = function($el) {
                    //< EKAMPUS
                    return _$.trim($el.data('title')).toLowerCase();
                    // EKAMPUS >
                };
            }

            return opts;
        },

        /**
         * Updates the pagination.
         *
         * @param {int} total The total number of items.
         */
        update_pagination: function(total) {
            var parent = _$('#gridder-pagination');
            var list = parent.children('ul.pagination');
            var pages = Math.ceil(total / this.get_items_per_page());

            total = parseInt(total);

            // Create pagination if it doesn't exist yet.
            if (list.size() === 0) {
                list = _$('<ul></ul>').addClass('pagination');

                if (pages > 1) {
                    add_pagination_links(list, pages);
                }

                parent.append(list);

                list.on('click', 'a', function(evt) {
                    evt.preventDefault();

                    // Skip clicks on disabled elements.
                    if (_$(this).parent().hasClass('disabled')) {
                        return;
                    }

                    // Go to previous page.
                    if (_$(this).parent().hasClass('previous')) {
                        _page--;
                    }
                    // Go to next page
                    else if (_$(this).parent().hasClass('next')) {
                        _page++;
                    }
                    else {
                        _page = parseInt(_$(this).html()) - 1;
                    }

                    _change_page(true);
                });
            }

            // Changing filters may have changed the number of pages. Update page
            // links if necessary.
            else if (pages !== list.children('li.pagelink').size()) {
                list.children('li').remove();

                if (pages > 1) {
                    add_pagination_links(list, pages);
                }
            }

            // Select current page.
            _$('li#page_' + (_page + 1)).addClass('active').siblings().removeClass('active');

            // Mark a few previous/next pages relevant (others will be hidden
            // in navigation, no need to show EVERY page link).
            _$('li.pagelink').removeClass('relevant');
            _$('li.spacer').remove();

            _$.each(_$.unique([ 1, _page+1, _page, _page-1, _page+2, _page+3, pages ]), function () {
                    if (this > 0) {
                        _$('li#page_' + this).addClass('relevant');
                    }
                });

            // Add "..." between relevant pagelinks.
            _$('li.pagelink.relevant + li.pagelink:not(.relevant)').
                    after('<li class="spacer">...</li>');

            // Disable/enable next- and previous-links.
            _$(list.children('li')).first()[(_page === 0 ? 'add' : 'remove') + 'Class']('disabled');
            _$(list.children('li')).last()[(_page === pages - 1 ? 'add' : 'remove') + 'Class']('disabled');
        },

        /**
         * Sets the current page.
         *
         * @param {int} page The page number.
         */
        set_page: function (page) {
            _page = page;
        },

        /**
         * Returns the current page number.
         *
         * @returns {int} The page number.
         */
        get_page: function () {
            return _page;
        },

        set_total: function (total) {
            _total = total;
        },

        get_total: function () {
            return _total;
        },

        /**
         * Returns the items per page -setting.
         *
         * @returns {int} Items per page.
         */
        get_items_per_page: function () {
            // Get one item less if there's the "create new" -item to get the
            // same amount of boxes every time.
            return (_grid.children('.gridder-new').size() === 0 ? _itemsperpage : _itemsperpage - 1);
        },

        /**
         * Called when the page should be changed.
         */
        change_page: function () {
            _change_page();
        }
    };

    return _public;
});