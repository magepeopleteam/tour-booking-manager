function get_ttbm_ticket(current, date = '') {
    let parent = current.closest('.ttbm_registration_area');
    let tour_id = parent.find('[name="ttbm_id"]').val();
    let tour_date = date ? date : parent.find('[name="ttbm_date"]').val();
    let repeat_target = parent.find('[name="ttbm_date"]');
    let tour_time = '';
    if (repeat_target.length > 0) {
        tour_time = parent.find('[name="ttbm_select_time"]').val();
        tour_date = tour_time ? tour_time : repeat_target.val();
    }
    let target = parent.find('.ttbm_booking_panel').first();
    let currentRequest = parent.data('ttbmTicketRequest');
    if (currentRequest && currentRequest.readyState !== 4) {
        currentRequest.abort();
    }
    let requestToken = Date.now().toString() + Math.random().toString(36).slice(2);
    parent.data('ttbmTicketRequestToken', requestToken);
    let ajaxRequest = jQuery.ajax({
        type: 'POST',
        url: ttbm_ajax_url,
        data: {
            "action": "get_ttbm_ticket",
            "tour_id": tour_id,
            "tour_date": tour_date,
            nonce: ttbm_ajax.nonce
        },
        beforeSend: function () {
            placeholderLoader(parent);
        },
        success: function (data) {
            if (parent.data('ttbmTicketRequestToken') !== requestToken) {
                return;
            }
            target.html(data).slideDown('fast').promise().done(function () {
                ttbm_price_calculation(parent);
                placeholderLoaderRemove(parent);
                simpleSpinnerRemove(parent);
                ttbm_sync_available_seat(parent);
                parent.trigger('ttbm:ticket-refreshed');
            });
        },
        error: function (xhr, textStatus) {
            if (textStatus === 'abort') {
                return;
            }
            placeholderLoaderRemove(parent);
            simpleSpinnerRemove(parent);
        },
        complete: function () {
            if (parent.data('ttbmTicketRequestToken') === requestToken) {
                parent.removeData('ttbmTicketRequestToken');
            }
            if (parent.data('ttbmTicketRequest') === ajaxRequest) {
                parent.removeData('ttbmTicketRequest');
            }
        }
    });
    parent.data('ttbmTicketRequest', ajaxRequest);
}
function ttbm_toggle_book_now_by_date(parent) {
    if (!parent || parent.length < 1) {
        return;
    }
    let dateTarget = parent.find('[name="ttbm_date"]').first();
    if (dateTarget.length < 1) {
        return;
    }
    let hasDate = !!dateTarget.val();
    parent.find('.ttbm_book_now').each(function () {
        jQuery(this)
            .prop('disabled', !hasDate)
            .toggleClass('mpDisabled', !hasDate)
            .attr('aria-disabled', !hasDate ? 'true' : 'false');
    });
}
function ttbm_sync_available_seat(parent) {
    let totalAvailable = parseInt(parent.find('#ttbm_total_available').first().text(), 10);
    if (isNaN(totalAvailable)) {
        return;
    }
    let detailsPage = parent.closest('.ttbm_details_page');
    if (detailsPage.length > 0) {
        detailsPage.find('.ttbm_available_seat_area .ttbm_available_seat').first().text(totalAvailable);
    } else {
        jQuery('.ttbm_available_seat_area .ttbm_available_seat').first().text(totalAvailable);
    }
}
function get_ttbm_sold_ticket(parent, tour_id, tour_date) {
    let target = jQuery('.ttbm_available_seat_area');
    jQuery.ajax({
        type: 'POST',
        url: ttbm_ajax_url,
        data: {
            "action": "get_ttbm_sold_ticket",
            "tour_id": tour_id,
            "tour_date": tour_date,
            nonce: ttbm_ajax.nonce
        },
        beforeSend: function () {
            dLoader_xs(target);
        },
        success: function (data) {
            target.find('.ttbm_available_seat').html(data);
            dLoaderRemove(target);
        }
    });
}
(function ($) {
    "use strict";
    $(document).ready(function () {
        $('.ttbm_registration_area').each(function () {
            ttbm_toggle_book_now_by_date($(this));
        });
    });
    $(document).on('change', '.ttbm_registration_area [name="ttbm_date"]', function () {
        let parent = $(this).closest('.ttbm_registration_area');

        // Clear Validation Error
        let date_input = parent.find('#ttbm_select_date');
        date_input.css('border', '');
        parent.find('.ttbm-date-error').remove();

        let time_slot = parent.find('.ttbm_select_time_area');
        parent.find('.ttbm_booking_panel').html('');
        // With time slots, wait for a slot (or Check Availability) before loading tickets.
        if (time_slot.length > 0) {
            placeholderLoaderRemove(parent);
            time_slot.slideDown();
            parent.find('.ttbm_booking_panel').hide();
            ttbm_toggle_book_now_by_date(parent);
            return true;
        }
        get_ttbm_ticket($(this));
        ttbm_toggle_book_now_by_date(parent);
    });
    $(document).on('ttbm:ticket-refreshed', '.ttbm_registration_area', function () {
        ttbm_toggle_book_now_by_date($(this));
    });

    // Clear time validation error on selection
    $(document).on('click', '.ttbm_select_time_area .customRadio', function () {
        let parent = $(this).closest('.ttbm_select_time_area');
        parent.css('border', '');
        parent.find('.ttbm-time-error').remove();
    });

    $(document).on('click', '.get_particular_ticket', function () {
        let current = $(this).closest('.particular_date_area');
        let parent = $(this).closest('.ttbm_registration_area');
        let tour_date = current.find('[name="ttbm_particular_date"]').val();
        let target = current.find('.ttbm_booking_panel');
        let tour_id = parent.find('[name="ttbm_id"]').val();
        let target_form = target.find('.mp_tour_ticket_form');
        if (target_form.length < 1) {
            $('#particular_item_area').find('.ttbm_booking_panel').slideUp('fast');
            jQuery.ajax({
                type: 'POST',
                url: ttbm_ajax_url,
                data: {
                    "action": "get_ttbm_ticket",
                    "tour_id": tour_id,
                    "tour_date": tour_date,
                    nonce: ttbm_ajax.nonce
                },
                beforeSend: function () {
                    target.slideDown('fast');
                    simpleSpinner(target);
                },
                success: function (data) {
                    target.html(data).promise().done(function () {
                        ttbm_price_calculation(target);
                        ttbm_sync_available_seat(parent);
                    });
                }
            });
        } else {
            ttbm_sync_available_seat(parent);
            if (target.is(':visible')) {
                target.slideUp('fast');
            } else {
                $('#particular_item_area').find('.ttbm_booking_panel').slideUp('fast');
                target.slideDown('fast');
            }
        }
    });
    $(document).on("click", ".ttbm_registration_area .ttbm_check_ability", function () {
        let parent = $(this).closest('.ttbm_registration_area');
        let date_target = parent.find('[name="ttbm_date"]').first();
        let has_date_field = date_target.length > 0;
        let date_val = has_date_field ? date_target.val() : '';
        let time_slot = parent.find('.ttbm_select_time_area');
        let date_input = parent.find('#ttbm_select_date');
        let date_field = date_input.length > 0 ? date_input : date_target;

        // Validation: Date is required
        if (has_date_field && !date_val) {
            // alert('Please Select Date');
            date_field.css('border', '1px solid red');
            if (parent.find('.ttbm-date-error').length === 0) {
                date_field.after('<span class="ttbm-date-error" style="color:red; font-size:12px; display:block; margin-top:5px;">Please Select Date</span>');
            }
            date_field.trigger('focus');
            return;
        } else if (has_date_field) {
            date_field.css('border', '');
            parent.find('.ttbm-date-error').remove();
        }

        if (time_slot.length > 0) {
            if (parent.find('[name="ttbm_select_time"]').val()) {
                parent.find('.ttbm_booking_panel').show();
                get_ttbm_ticket($(this));
            } else if (parent.find('[name="ttbm_select_time"]').length > 0) {
                // alert('Please Select Time');
                time_slot.css('border', '1px solid red');
                time_slot.css('padding', '10px');
                time_slot.css('border-radius', '5px');
                if (time_slot.find('.ttbm-time-error').length === 0) {
                    time_slot.append('<span class="ttbm-time-error" style="color:red; font-size:12px; display:block; margin-top:5px; width:100%;">Please Select Time</span>');
                }
            } else {
                // Should not happen if time_slot exists but inputs are missing
                get_ttbm_ticket($(this));
            }
        } else {
            get_ttbm_ticket($(this));
        }
    });
    /*$(document).on('click', 'div.ttbm_popup  .popupClose', function () {
        $(this).closest('[data-popup]').removeClass('in');
        $('body').removeClass('noScroll').find('[data-active-popup]').removeAttr('data-active-popup');
        return true;
    });*/
    $(document).on('click', 'div.ttbm_popup  .popupCloseBtn', function () {
        $(this).closest('[data-popup]').removeClass('in');
        $('body').removeClass('noScroll').find('[data-active-popup]').removeAttr('data-active-popup');
        return true;
    });
    $(document).on("click", ".ttbm_registration_area .ttbm_load_popup_reg", function () {
        let parent = $(this).closest('.ttbm_registration_area');
        let date_target = parent.find('[name="ttbm_date"]').first();
        let has_date_field = date_target.length > 0;
        let date_val = has_date_field ? date_target.val() : '';
        let time_slot = parent.find('.ttbm_select_time_area');
        let date_input = parent.find('#ttbm_select_date');
        let date_field = date_input.length > 0 ? date_input : date_target;

        // Validation: Date is required
        if (has_date_field && !date_val) {
            date_field.css('border', '1px solid red');
            if (parent.find('.ttbm-date-error').length === 0) {
                date_field.after('<span class="ttbm-date-error" style="color:red; font-size:12px; display:block; margin-top:5px;">Please Select Date</span>');
            }
            date_field.trigger('focus');
            return;
        } else if (has_date_field) {
            date_field.css('border', '');
            parent.find('.ttbm-date-error').remove();
        }

        if (time_slot.length > 0) {
            if (parent.find('[name="ttbm_select_time"]').val()) {
                parent.find('.registration_popup').trigger('click');
                get_ttbm_ticket($(this));
            } else if (parent.find('[name="ttbm_select_time"]').length > 0) {
                // alert('Please Select Time');
                time_slot.css('border', '1px solid red');
                time_slot.css('padding', '10px');
                time_slot.css('border-radius', '5px');
                if (time_slot.find('.ttbm-time-error').length === 0) {
                    time_slot.append('<span class="ttbm-time-error" style="color:red; font-size:12px; display:block; margin-top:5px; width:100%;">Please Select Time</span>');
                }
            } else {
                parent.find('#ttbm_select_date').trigger('focus');
            }
        } else {
            parent.find('.registration_popup').trigger('click');
            let selected_date = has_date_field ? date_target.val() : '';
            if (!has_date_field || selected_date) {
                get_ttbm_ticket($(this));
            }
        }
    });
    $(document).on('change', '.ttbm_registration_area [name="ttbm_tour_hotel_list"]', function () {
        let parent = $(this).closest('.ttbm_registration_area');
        let availability = parent.find('.ttbm_check_ability');
        if (availability.length < 1) {
            get_ttbm_ticket($(this));
        }
    });
    $(document).on('click', '.ttbm_short_list_more', function () {
        let parent = $(this).closest('.item_section');
        let target = parent.find('.small_box.dNone');
        if (target.is(':visible')) {
            target.slideUp(250);
        } else {
            target.slideDown(250);
        }
    });
    $('input[name="ttbm_hotel_date_range"]').daterangepicker({
        autoUpdateInput: false,
        minDate: moment(),
        "autoApply": true,
        "locale": {
            "format": "YYYY/MM/DD"
        }
    }).on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('YYYY/MM/DD') + '    -    ' + picker.endDate.format('YYYY/MM/DD'));
        $('.ttbm_hotel_area').slideUp('fast').find('.ttbm_booking_panel').html('');
    }).on('cancel.daterangepicker', function () {
        $(this).val('');
    });
    $(document).on('click', '.ttbm_hotel_check_availability', function () {
        let target = $('[name="ttbm_hotel_date_range"]');
        let date_range = target.val();
        if (date_range) {
            $('.ttbm_hotel_area').slideDown('fast');
            ttbm_loadBgImage();
        } else {
            $('.ttbm_hotel_area').slideUp('fast');
            target.trigger('focus');
        }
    });
    $(document).on('click', '.ttbm_hotel_open_room_list', function () {
        let current = $(this).closest('.ttbm_hotel_item');
        let tour_id = current.find('[name="ttbm_id"]').val();
        let hotel_id = current.find('[name="ttbm_hotel_id"]').val();
        let date_range = $('[name="ttbm_hotel_date_range"]').val();
        if ($('[name="ttbm_hotel_date_range"]').length > 1) {
            date_range = $(this).closest('.particular_date_area').find('[name="ttbm_hotel_date_range"]').val();
        }
        let target = current.find('.ttbm_booking_panel');
        let target_form = target.find('.mp_tour_ticket_form');
        if (date_range) {
            if (target_form.length < 1) {
                $('.ttbm_hotel_area').find('.ttbm_booking_panel').slideUp('fast');
                jQuery.ajax({
                    type: 'POST',
                    url: ttbm_ajax_url,
                    data: {
                        "action": "get_ttbm_hotel_room_list",
                        "tour_id": tour_id,
                        "hotel_id": hotel_id,
                        "date_range": date_range,
                        nonce: ttbm_ajax.nonce
                    },
                    beforeSend: function () {
                        target.slideDown('fast');
                        simpleSpinner(target);
                    },
                    success: function (data) {
                        target.html(data).promise().done(function () {
                            ttbm_price_calculation(target);
                        });
                    }
                });
            } else {
                if (target.is(':visible')) {
                    target.slideUp('fast');
                } else {
                    $('.ttbm_hotel_area').find('.ttbm_booking_panel').slideUp('fast');
                    target.slideDown('fast');
                }
            }
        }
    });
    $(document).on('click', '.get_particular_hotel', function () {
        let parent = $(this).closest('.particular_date_area');
        parent.find('.ttbm_hotel_area').slideToggle(250);
        ttbm_loadBgImage();
    });
    $(document).on('click', '.ttbm_go_particular_booking', function () {
        pageScrollTo($('#particular_item_area'));
    });
    //=== sidebar modal========
    $(document).on('click', '[data-ttbm-modal]', function (e) {
        const modalTarget = $(this).data('ttbm-modal');
        $(`[data-ttbm-modal-target="${modalTarget}"]`).addClass('open');
    });
    $(document).on('click', '[data-ttbm-modal-target] .ttbm-modal-close', function (e) {
        $(this).closest('[data-ttbm-modal-target]').removeClass('open');
    });

    // === frontend filter modal========

    jQuery(document).ready(function ($) {
        // =========================================================
        // View Switcher (Grid / List)
        // =========================================================
        $('.ttbm_grid_view').on('click', function () {

            $('.ttbm-view-btn').removeClass('ttbm-view-active').attr('aria-pressed', 'false');
            $(this).addClass('ttbm-view-active').attr('aria-pressed', 'true');

            $('.all_filter_item .flexWrap')
                .removeClass('ttbm-list-mode')
                .addClass('ttbm-grid-mode');

            $('.all_filter_item .filter_item')
                .removeClass('list_1')
                .addClass('grid_'.concat(window.ttbm_column || '3'));
        });

        $('.ttbm_list_view').on('click', function () {

            $('.ttbm-view-btn').removeClass('ttbm-view-active').attr('aria-pressed', 'false');
            $(this).addClass('ttbm-view-active').attr('aria-pressed', 'true');

            $('.all_filter_item .flexWrap')
                .removeClass('ttbm-grid-mode')
                .addClass('ttbm-list-mode');

            $('.all_filter_item .filter_item')
                .removeClass(function (index, className) {
                    return (className.match(/(^|\s)grid_\S+/g) || []).join(' ');
                })
                .addClass('list_1');
        });

        // =========================================================
        // Sort Tours
        // =========================================================
        // =========================================================
// Sort Tours
// =========================================================
$(document).on('change', '.ttbm-sort-select', function () {

    let sortValue = $(this).val();

    let container = $('.all_filter_item .flexWrap');

    let items = container.children('.filter_item').get();

    items.sort(function (a, b) {

        let priceA = parseFloat($(a).data('price')) || 0;
        let priceB = parseFloat($(b).data('price')) || 0;

        let titleA = ($(a).data('title') || '').toString().toLowerCase();
        let titleB = ($(b).data('title') || '').toString().toLowerCase();

        let dateA = new Date($(a).data('date')).getTime() || 0;
        let dateB = new Date($(b).data('date')).getTime() || 0;

        switch (sortValue) {

            case 'price_asc':
                return priceA - priceB;

            case 'price_desc':
                return priceB - priceA;

            case 'title_asc':
                return titleA.localeCompare(titleB);

            case 'date_desc':
                return dateB - dateA;

            default:
                return 0;
        }
    });

    $.each(items, function (index, item) {
        container.append(item);
    });

});

        // =========================================================
        // Top Filter Tabs
        // =========================================================
        // When a tab filter is active, completely take over Load More and
        // page-number clicks so the legacy filter_pagination.js handler
        // cannot iterate and slideDown the hidden cards.
        document.addEventListener('click', function (evt) {
            let target = evt.target.closest('.pagination_load_more, [data-pagination]');
            if (!target) return;
            let area = target.closest('.ttbm_filter_area');
            if (!area) return;
            if (!area.querySelector('.filter_item.ttbm-tab-hidden')) return;

            evt.stopImmediatePropagation();
            evt.preventDefault();

            let $area = $(area);
            let matched = $area.find('.filter_item').not('.ttbm-tab-hidden');
            let totalMatched = matched.length;
            let pp = parseInt($area.find('input[name="pagination_per_page"]').val(), 10);
            if (isNaN(pp) || pp < 1) pp = totalMatched;

            let page;
            if (target.classList.contains('pagination_load_more')) {
                page = parseInt(target.getAttribute('data-load-more'), 10) || 0;
                let hiddenInMatched = matched.filter(':hidden').length;
                page = hiddenInMatched > 0 ? page + 1 : 0;
                target.setAttribute('data-load-more', String(page));
            } else {
                page = parseInt(target.getAttribute('data-pagination'), 10) || 0;
                $area.find('[data-pagination]').removeClass('active_pagination');
                $(target).addClass('active_pagination');
            }

            let style = $area.find('input[name="pagination_style"]').val();
            let startIdx = (style === 'load_more') ? 0 : page * pp;
            let endIdx = (style === 'load_more') ? (page + 1) * pp : (page + 1) * pp;

            matched.each(function (idx) {
                if (idx >= startIdx && idx < endIdx) {
                    $(this).removeClass('dNone').show();
                } else {
                    $(this).hide();
                }
            });
            $area.find('.filter_item.ttbm-tab-hidden').hide();

            let visibleNow = matched.filter(':visible').length;
            $area.find('.qty_count').html(visibleNow);
            $area.find('.total_filter_qty').html(totalMatched);

            if (style === 'load_more') {
                if (matched.filter(':hidden').length === 0) {
                    $area.find('.pagination_load_more').attr('disabled', 'disabled');
                } else {
                    $area.find('.pagination_load_more').removeAttr('disabled');
                }
            }
        }, true);

        $(document).on('click', '.ttbm-tab-btn', function () {

            $('.ttbm-tab-btn').removeClass('ttbm-tab-active');
            $(this).addClass('ttbm-tab-active');

            let filter = $(this).data('filter-tab');

            let parent = $(this).closest('.ttbm_filter_area');
            if (!parent.length) parent = $(this).closest('.all_filter_item').parent();
            if (!parent.find('.filter_item').length) parent = $(document);

            let perPage = parseInt(parent.find('input[name="pagination_per_page"]').val(), 10);
            if (isNaN(perPage) || perPage < 1) perPage = parent.find('.filter_item').length;

            let now = new Date();
            now.setHours(0, 0, 0, 0);

            // Calendar week: Monday (start) to Sunday (end)
            let dayOfWeek = now.getDay(); // 0 = Sun, 1 = Mon, ... 6 = Sat
            let daysSinceMonday = (dayOfWeek + 6) % 7;
            let weekStart = new Date(now);
            weekStart.setDate(weekStart.getDate() - daysSinceMonday);
            let weekEnd = new Date(weekStart);
            weekEnd.setDate(weekEnd.getDate() + 6);

            // Step 1: classify every card — match vs. not match
            parent.find('.filter_item').each(function () {

                let item = $(this);
                let itemDate = item.attr('data-date');
                let matches = false;

                if (filter === 'all' || !itemDate) {
                    matches = true;
                } else {
                    let tourDate = new Date(itemDate + 'T00:00:00');
                    if (filter === 'week') {
                        matches = (tourDate >= weekStart && tourDate <= weekEnd);
                    } else if (filter === 'month') {
                        matches = (tourDate.getMonth() === now.getMonth() &&
                                   tourDate.getFullYear() === now.getFullYear());
                    } else if (filter === 'year') {
                        matches = (tourDate.getFullYear() === now.getFullYear());
                    }
                }

                item.toggleClass('ttbm-tab-hidden', !matches);
            });

            // Step 2: paginate within the matched set (first perPage visible, rest hidden)
            let matched = parent.find('.filter_item').not('.ttbm-tab-hidden');
            let totalMatched = matched.length;

            parent.find('.filter_item.ttbm-tab-hidden').hide();
            matched.each(function (idx) {
                let item = $(this);
                if (idx < perPage) {
                    item.removeClass('dNone').show();
                } else {
                    item.hide();
                }
            });

            // Step 3: reset & toggle Load More / pagination area
            let loadMoreBtn = parent.find('.pagination_load_more');
            loadMoreBtn.attr('data-load-more', '0').removeAttr('disabled');
            parent.find('[data-pagination]').removeClass('active_pagination');
            parent.find('[data-pagination="0"]').addClass('active_pagination');

            if (totalMatched <= perPage) {
                parent.find('.pagination_area').hide();
            } else {
                parent.find('.pagination_area').show();
            }

            // Step 4: update "Showing X of Y" + empty-state notice
            let visibleCount = Math.min(perPage, totalMatched);
            parent.find('.qty_count').html(visibleCount);
            parent.find('.total_filter_qty').html(totalMatched);

            if (totalMatched === 0) {
                parent.find('.search_result_empty').slideDown('fast');
                parent.find('.filter_short_result').slideUp('fast');
            } else {
                parent.find('.search_result_empty').slideUp('fast');
                parent.find('.filter_short_result').slideDown('fast');
            }
        });

        // Activity filter is handled in filter_pagination.js (.ttbm_item_filter_by_activity).
        // =========================================================
        // Mobile Left Filter Toggle
        // =========================================================
        function ttbmSetupMobileFilterToggle() {
            $('.ttbm_filter_area .leftSidebar').each(function () {
                let sidebar = $(this);
                let filterBody = sidebar.children('.ttbm_filter').first();
                let toggle = sidebar.find('.ttbm-mobile-filter-toggle').first();
                let showLabel = toggle.data('show-label') || 'Show filters';

                if (filterBody.length < 1 || toggle.length < 1) {
                    return;
                }

                if (window.matchMedia('(max-width: 767px)').matches) {
                    if (!sidebar.hasClass('ttbm-mobile-filter-ready')) {
                        filterBody.hide();
                        toggle
                            .removeClass('is-open')
                            .attr('aria-expanded', 'false')
                            .find('.ttbm-mobile-filter-toggle-text')
                            .text(showLabel);
                        sidebar.addClass('ttbm-mobile-filter-ready');
                    }
                } else {
                    filterBody.show();
                    toggle
                        .removeClass('is-open')
                        .attr('aria-expanded', 'true')
                        .find('.ttbm-mobile-filter-toggle-text')
                        .text(showLabel);
                    sidebar.removeClass('ttbm-mobile-filter-ready');
                }
            });
        }

        ttbmSetupMobileFilterToggle();

        $(window).on('resize', function () {
            ttbmSetupMobileFilterToggle();
        });

        $(document).on('click', '.ttbm_filter_area .ttbm-mobile-filter-toggle', function () {
            if (!window.matchMedia('(max-width: 767px)').matches) {
                return;
            }

            let toggle = $(this);
            let sidebar = toggle.closest('.leftSidebar');
            let filterBody = sidebar.children('.ttbm_filter').first();
            let isOpen = toggle.hasClass('is-open');
            let showLabel = toggle.data('show-label') || 'Show filters';
            let hideLabel = toggle.data('hide-label') || 'Hide filters';

            toggle
                .toggleClass('is-open', !isOpen)
                .attr('aria-expanded', !isOpen ? 'true' : 'false')
                .find('.ttbm-mobile-filter-toggle-text')
                .text(!isOpen ? hideLabel : showLabel);

            filterBody.stop(true, true).slideToggle(220);
        });

    });
}(jQuery));
