(function ($) {

    $(document).on("click", ".ttbm_hotel_booking_load_more_btn", function (e) {
        e.preventDefault();

        let btnText =  $(this).text().trim();
        if( btnText === 'Load More' ){
            $(this).text('Loading...');
            let hotel_id = $("#all_hotel_list").val().trim();
            let date = $("#ttbm_booking_date_filter").val().trim();
            let display_limit = $('.ttbm_total_booking_page_input').val().trim();
            let loaded_ids = [];
            let ttbm_shown_lists = parseInt($('.ttbm_showing_items').text(), 10);

            $('.ttbm_total_booking_tr').each(function () {
                let id = $(this).attr('id');
                loaded_ids.push(id);
            });
            let loaded_ids_str = loaded_ids.join(',');

            $.ajax({
                type: 'POST',
                url: ttbm_admin_ajax.ajax_url,
                data: {
                    action: "get_ttbm_hotel_booking_load_more_lists",
                    date: date,
                    hotel_id: hotel_id,
                    display_limit: display_limit,
                    loaded_ids_str: loaded_ids_str,
                    nonce: ttbm_admin_ajax.nonce,
                },
                success: function ( response) {
                    if( response ){
                        let load_items = response.data.post_count;
                        load_items = load_items + ttbm_shown_lists;
                        $("#ttbm_total_booking_tbody").append( response.data.data );
                        $('.ttbm_showing_items').text( load_items );
                        $(".ttbm_hotel_booking_load_more_btn").text('Load More');

                        if( response.data.post_count < display_limit ){
                            $(".ttbm_hotel_booking_load_more_holder").fadeOut();
                        }else{
                            $(".ttbm_hotel_booking_load_more_holder").fadeIn();
                        }

                    }
                }, error: function (response) {
                    $(".ttbm_hotel_booking_load_more_btn").text('Load More');
                    console.log( response );
                }
            });
        }else{
            alert('Please Wait Sometimes');
        }

    });

    $(document).on('click', '#ttbm_hotel_list_load_more_btn',function() {

        let clicked_btn_txt = $(this).text().trim();
        if( clicked_btn_txt === 'Load More' ){
            $(this).text('Loading...');
            let nonce = ttbm_admin_ajax.nonce;
            let setUrl = ttbm_admin_ajax.ajax_url;

            let display_limit = 2;

            let hotelIds = [];

            $('.ttbm_hotel_list_view tr[id^="hotel_"]').each(function() {
                let rawId = $(this).attr('id');
                let cleanId = rawId.replace('hotel_', '');
                hotelIds.push(cleanId);
            });
            hotelIds = hotelIds.join(',');
            let type = 'POST';
            $.ajax({
                type: type,
                url: setUrl,
                data: {
                    action: "ttbm_load_more_hotel_lists_admin",
                    loaded_ids_str: hotelIds,
                    display_limit: display_limit,
                    nonce: nonce,
                },
                success: function( response ) {
                    let searchData =  response.data.result_data;
                    $("#ttbm_hotel_list_view").append( searchData );

                    $("#ttbm_hotel_list_load_more_btn").text(clicked_btn_txt );
                    if( response.data.post_count < display_limit ){
                        $(".ttbm_hotel_list_load_more_btn_holder").fadeOut();
                    }else{
                        $(".ttbm_hotel_list_load_more_btn_holder").fadeIn();
                    }

                },
                error: function(xhr, status, error) {
                    $("#ttbm_hotel_list_load_more_btn").text(clicked_btn_txt );
                    console.error(xhr.responseText);
                }
            });
        }else{
            alert('Please Wait Somethimes');
        }

    });

    $(document).on('click', '.ttbm-hotel-list-delete-btn', function(e) {
        var confirmMsg = $(this).data('confirm') || 'Are you sure?';
        if (!confirm(confirmMsg)) {
            e.preventDefault(); // Stop link navigation if cancel
        }
    });

    $(document).on("click", ".ttbm_total_booking_filter_btn", function (e) {
        e.preventDefault();

        let filterTxt = $(".ttbm_total_booking_filter_btn").text().trim();

        if( filterTxt === 'Filter' ) {
            $(this).text('Filtering...');
            let hotel_id = $("#all_hotel_list").val().trim();
            let date = $("#ttbm_booking_date_filter").val().trim();
            let display_limit = $('.ttbm_total_booking_page_input').val().trim();

            $.ajax({
                type: 'POST',
                url: ttbm_admin_ajax.ajax_url,
                data: {
                    action: "get_ttbm_hotel_booking_all_lists",
                    date: date,
                    hotel_id: hotel_id,
                    display_limit: display_limit,
                    nonce: ttbm_admin_ajax.nonce,
                },
                success: function (response) {
                    if (response) {
                        $("#ttbm_total_booking_tbody").html(response.data.data);
                        $('.ttbm_total_posts').text(response.data.found_posts);
                        $('.ttbm_showing_items').text(response.data.post_count);
                        $(".ttbm_total_booking_filter_btn").text( filterTxt );
                        if( response.data.post_count < display_limit ){
                            $(".ttbm_hotel_booking_load_more_holder").fadeOut();
                        }else{
                            $(".ttbm_hotel_booking_load_more_holder").fadeIn();
                        }
                    }
                }, error: function (response) {
                    $(".ttbm_total_booking_filter_btn").text( filterTxt );
                    console.log(response);
                }
            });
        }else{
            alert( "Please Wait Sometimes" );
        }


    });

    $(document).on("click", ".ttbm_total_booking_view_more", function (e) {
        e.preventDefault();
        $('.ttbm_booking_user_more_info').fadeOut();
        if( $(this).text().trim() === 'View More' ){
            $(this).text('See Less');
        }else{
            $(this).text('View More');
        }

        $(this).siblings('.ttbm_booking_user_more_info').toggle();
    });

    $('#ttbm_booking_date_filter').datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        minDate: 0
    });


    $(document).on('click', '.ttbm_hotel_tab_item', function() {
        var tab_id = $(this).data('tab');

        $('.ttbm_hotel_tab_item').removeClass('active');
        $(this).addClass('active');

        $('.ttbm_hotel_tab_content').removeClass('active');
        $('#' + tab_id).addClass('active');
    });

    function get_search_data_and_display( setUrl, type, search_term, nonce){
        $.ajax({
            type: type,
            url: setUrl,
            data: {
                action: "get_ttbm_hotel_search_by_title",
                search_term: search_term,
                limit: 20
            },
            success: function( response ) {
                let searchData =  response.data.result_data;
                $("#ttbm_hotel_list_view").html( searchData );
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });

    }

    $(document).on('input', '#ttbm_hotel_title_SearchBox',function() {
        let search_term = $(this).val();
        let nonce = ttbm_admin_ajax.nonce;
        let setUrl = ttbm_admin_ajax.ajax_url;
        let type = 'POST';
        if( search_term.length > 0 ) {
            $("#productTitleWrapper").show();
            $("#productDropDownMenu").show();
        }
        if( search_term.length === 3 ) {
            get_search_data_and_display( setUrl, type, search_term, nonce);
        }else if( search_term.length === 12 ){
            get_search_data_and_display( setUrl, type, search_term, nonce);
        }else if( search_term.length === 22 ){
            get_search_data_and_display( setUrl, type, search_term, nonce);
        }else if( search_term.length === 33 ){
            get_search_data_and_display( setUrl, type, search_term, nonce);
        }
        else if( search_term.length < 3 ){
            search_term = '';
            get_search_data_and_display( setUrl, type, search_term, nonce);
        }
    });

    $(document).on('click', '.ttbm_trvel_lists_tabs button', function() {
        var targetId = $(this).data('target');
        let tabStorageKey = '';
        try {
            let bodyId = ($('body').attr('id') || 'ttbm_tour_page_ttbm_list').replace(/[^a-zA-Z0-9_-]/g, '');
            tabStorageKey = 'ttbm_active_tab_' + bodyId + '_travel_list';
            localStorage.setItem(tabStorageKey, targetId);
        } catch (e) {}

        if (history.pushState) {
            history.pushState(null, null, '#' + targetId);
        } else {
            window.location.hash = targetId;
        }

        let tabParam = '';

        let action = '';
        if( targetId === 'ttbm_trvel_lists_location' ){
            action = 'ttbm_get_locations_html';
            tabParam = 'location';

        }else if( targetId === 'ttbm_trvel_lists_tour_category' ){
            action = 'ttbm_get_category_html_data';
            tabParam = 'category';
        }else if( targetId === 'ttbm_trvel_lists_organiser' ){
            action = 'ttbm_get_organiser_html_data';
            tabParam = 'organiser';
        }else if( targetId === 'ttbm_trvel_lists_features' ){
            action = 'ttbm_get_feature_html_data';
            tabParam = 'features';
        }else if( targetId === 'ttbm_trvel_lists_tag' ){
            action = 'ttbm_get_tag_html_data';
            tabParam = 'tag';
        }else if( targetId === 'ttbm_trvel_lists_activities' ){
            action = 'ttbm_get_activities_html_data';
            tabParam = 'activities';
        }else if( targetId === 'ttbm_trvel_lists_places' ){
            action = 'ttbm_get_places_html_data';
            tabParam = 'places';
        }else if( targetId === 'ttbm_trvel_lists_guides' ){
            action = 'ttbm_get_guides_html';
            tabParam = 'guides';
        }else if( targetId === 'ttbm_trvel_lists_ticket_types' ){
            action = 'ttbm_get_ticket_types_html';
            tabParam = 'ticket_types';
        }else{
            tabParam = 'tour_list';
        }

        /*if (tabParam !== '') {
            let currentUrl = window.location.href;
            let cleanUrl = currentUrl.replace(/&tab=[^&]*!/g, '');
            let newUrl = cleanUrl + '&tab=' + tabParam;
            history.pushState({ path: newUrl }, '', newUrl);
        }*/


        if( targetId === 'ttbm_trvel_lists_tour' ){
            const $tourHolder = $('.ttbm-tour-list_holder');
            $tourHolder.show();
            if ($tourHolder.data('ttbm-needs-reload')) {
                ttbmReloadTourPackageList();
                $tourHolder.removeData('ttbm-needs-reload');
            }
        }else{
            $('.ttbm-tour-list_holder').hide().data('ttbm-needs-reload', 1);
        }

        $('.ttbm_trvel_lists_tabs button').removeClass('active');
        $('.ttbm_trvel_lists_content').removeClass('active');

        $(this).addClass('active');
        $('#' + targetId).addClass('active');

        if (!action) {
            return;
        }

        const tabContainer = ttbmGetTravelListTabContainer(targetId);
        ttbmShowTravelListSkeleton(tabContainer);

        let nonce = ttbm_admin_ajax.nonce;

        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                nonce: nonce,
                action: action,
            },
            success: function (response) {
                if( targetId === 'ttbm_trvel_lists_location' ){
                    if (response.success) {
                        $('#ttbm_travel_list_location_shows').html(response.data.html);
                        ttbm_load_more_location_data(3 );
                    } else {
                        $('#ttbm_travel_list_location_shows').html('<p>No locations found.</p>');
                    }
                }else if(  targetId === 'ttbm_trvel_lists_organiser'){
                    if (response.success) {
                        $('#ttbm_travel_list_organiser_content').html(response.data.html);
                    }
                }else if(  targetId === 'ttbm_trvel_lists_tour_category'){
                    if (response.success) {
                        $('#ttbm_travel_list_category_content').html(response.data.html);
                    }
                }else if(  targetId === 'ttbm_trvel_lists_features'){
                    if (response.success) {
                        $('#ttbm_travel_list_feature_content').html(response.data.html);
                    }
                }else if(  targetId === 'ttbm_trvel_lists_tag'){
                    if (response.success) {
                        $('#ttbm_travel_list_tag_content').html(response.data.html);
                    }
                }else if(  targetId === 'ttbm_trvel_lists_activities'){
                    if (response.success) {
                        $('#ttbm_travel_list_activies_content').html(response.data.html);
                    }
                }else if(  targetId === 'ttbm_trvel_lists_places'){
                    if (response.success) {
                        if( response.data.all_places_count >= 5){
                            let remianing = response.data.all_places_count - response.data.total_found;
                            $("#ttbm_places_load_more_holder").fadeIn();
                            $(".ttbm_places_sub_title_class").text( 'Places('+response.data.all_places_count+')' );
                            $("#ttbm_places_load_more_btn").text( 'Load More('+remianing+')' );
                        }
                        $('#ttbm_travel_list_places_content').html(response.data.html);
                    }
                }else if( targetId === 'ttbm_trvel_lists_guides' ){
                    if (response.success) {
                        $('#ttbm_travel_list_guides_content').html(response.data.html);
                    }
                }else if( targetId === 'ttbm_trvel_lists_ticket_types' ){
                    if (response.success) {
                        $('#ttbm_travel_list_ticket_types_content').html(response.data.html);
                    }
                }
            },
            error: function () {
                if (tabContainer) {
                    $(tabContainer).html('<p class="ttbm_travel_list_load_error">' + (ttbm_admin_ajax.strings?.request_failed || 'Request failed. Please check your connection and try again.') + '</p>');
                }
            }
        });


    });

    $(document).ready(function () {
        let tabs = $('.ttbm_trvel_lists_tabs button');
        if (!tabs.length) {
            return;
        }

        let hashTarget = (window.location.hash || '').replace('#', '');
        let savedTarget = '';
        try {
            let bodyId = ($('body').attr('id') || 'ttbm_tour_page_ttbm_list').replace(/[^a-zA-Z0-9_-]/g, '');
            let tabStorageKey = 'ttbm_active_tab_' + bodyId + '_travel_list';
            savedTarget = localStorage.getItem(tabStorageKey) || '';
        } catch (e) {}

        let initialTarget = '';
        if (hashTarget && $('.ttbm_trvel_lists_tabs button[data-target="' + hashTarget + '"]').length) {
            initialTarget = hashTarget;
        } else if (savedTarget && $('.ttbm_trvel_lists_tabs button[data-target="' + savedTarget + '"]').length) {
            initialTarget = savedTarget;
        }

        if (!initialTarget) {
            return;
        }

        let activeTarget = $('.ttbm_trvel_lists_tabs button.active').data('target') || '';
        if (activeTarget !== initialTarget) {
            $('.ttbm_trvel_lists_tabs button[data-target="' + initialTarget + '"]').first().trigger('click');
        }
    });


    function ttbm_load_more_location_data( cardsPerClick){

        let cards = $('.ttbm_search_location_by_title');
        let load_more_btn_id = $('#ttbm-location-load-more');
        cards.hide().slice(0, cardsPerClick).show();
        load_more_btn_id.on('click', function() {
            let hiddenCards = cards.filter(':hidden');
            hiddenCards.slice(0, cardsPerClick).slideDown();
            if (hiddenCards.length <= cardsPerClick) {
                load_more_btn_id.parent().fadeOut();
            }
        });
        if (cards.length <= cardsPerClick) {
            load_more_btn_id.parent().hide();
        }else{
            load_more_btn_id.parent().fadeIn();
        }
    }


    let ttbm_image_frame;

    function closeTaxonomyPopup() {
        $('#ttbm_travel_list_popup').empty();
        $('body').removeClass('noScroll');
    }

    function ttbmTravelTaxonomySkeletonHtml(count) {
        count = count || 6;
        let cards = '';
        for (let i = 0; i < count; i++) {
            cards += '<div class="ttbm_travel_skeleton_card">' +
                '<div class="ttbm_travel_skeleton_thumb" aria-hidden="true"></div>' +
                '<div class="ttbm_travel_skeleton_body">' +
                '<span class="ttbm_travel_skeleton_line ttbm_travel_skeleton-line--title" aria-hidden="true"></span>' +
                '<span class="ttbm_travel_skeleton_line" aria-hidden="true"></span>' +
                '<span class="ttbm_travel_skeleton_line ttbm_travel_skeleton-line--short" aria-hidden="true"></span>' +
                '</div></div>';
        }
        return '<div class="ttbm_travel_skeleton_loader ttbm_travel_skeleton-loader--grid" aria-busy="true" aria-label="Loading">' + cards + '</div>';
    }

    function ttbmTravelTourSkeletonHtml(count) {
        count = count || 6;
        let cards = '';
        for (let i = 0; i < count; i++) {
            cards += '<div class="ttbm_travel_skeleton_tour_package_card">' +
                '<div class="ttbm_travel_skeleton_tour_package_thumb" aria-hidden="true"></div>' +
                '<div class="ttbm_travel_skeleton_tour_package_content">' +
                    '<div class="ttbm_travel_skeleton_tour_package_main">' +
                        '<span class="ttbm_travel_skeleton_line ttbm_travel_skeleton-line--title" aria-hidden="true"></span>' +
                        '<span class="ttbm_travel_skeleton_line ttbm_travel_skeleton-line--location" aria-hidden="true"></span>' +
                    '</div>' +
                    '<div class="ttbm_travel_skeleton_tour_package_meta" aria-hidden="true">' +
                        '<span class="ttbm_travel_skeleton_line ttbm_travel_skeleton-line--badge"></span>' +
                        '<span class="ttbm_travel_skeleton_stat_pair"><span class="ttbm_travel_skeleton_line ttbm_travel_skeleton-line--stat-val"></span><span class="ttbm_travel_skeleton_line ttbm_travel_skeleton-line--stat-lbl"></span></span>' +
                        '<span class="ttbm_travel_skeleton_stat_pair"><span class="ttbm_travel_skeleton_line ttbm_travel_skeleton-line--stat-val"></span><span class="ttbm_travel_skeleton_line ttbm_travel_skeleton-line--stat-lbl"></span></span>' +
                        '<span class="ttbm_travel_skeleton_line ttbm_travel_skeleton-line--date"></span>' +
                    '</div>' +
                '</div>' +
                '<div class="ttbm_travel_skeleton_tour_package_actions" aria-hidden="true">' +
                    '<span></span><span></span><span></span><span></span>' +
                '</div>' +
            '</div>';
        }
        return '<div class="ttbm_travel_skeleton_loader ttbm_travel_skeleton-loader--tour-package" aria-busy="true" aria-label="Loading">' + cards + '</div>';
    }

    function ttbmShowTourPackageSkeleton(selector, count) {
        if (!selector) {
            return;
        }
        $(selector).html(ttbmTravelTourSkeletonHtml(count));
    }

    function ttbmReloadTourPackageList() {
        const $list = $('.ttbm-tour-list');
        if (!$list.length) {
            return;
        }

        const activeFilter = $('.ttbm_travel_filter_item.ttbm_filter_btn_active_bg_color').attr('data-filter-item') || 'all';
        const searchTerm = $('#ttbm-tour-search').val() || '';
        const $loadMore = $('#ttbm-load-more');
        const postPerPage = parseInt($loadMore.data('posts-per-page'), 10) || 10;

        ttbmShowTourPackageSkeleton($list, 4);

        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_search_tours',
                paged: 1,
                post_per_page: postPerPage,
                selected_filter: activeFilter,
                search_term: searchTerm,
                nonce: ttbm_admin_ajax.nonce,
            },
            success: function (response) {
                if (!(response && response.success && response.data && typeof response.data.html !== 'undefined')) {
                    $list.html('<p class="ttbm_travel_list_load_error">' + (ttbm_admin_ajax.strings?.no_tours_found || 'No tours found.') + '</p>');
                    $loadMore.hide();
                    return;
                }

                $list.html(response.data.html);
                const foundPosts = parseInt(response.data.found_posts || 0, 10);
                const loadedPosts = $list.find('.ttbm-tour-card').length;
                const remaining = Math.max(foundPosts - loadedPosts, 0);

                if ($loadMore.length) {
                    if (remaining > 0) {
                        $loadMore.attr('data-paged', 2);
                        $loadMore.attr('data-posts-per-page', postPerPage);
                        $loadMore.html('<i class="fas fa-sync-alt"></i> Load More (<span class="ttbm_load_more_remaining_travel">' + remaining + '</span>)');
                        $loadMore.show();
                    } else {
                        $loadMore.hide();
                    }
                }
            },
            error: function () {
                $list.html('<p class="ttbm_travel_list_load_error">' + (ttbm_admin_ajax.strings?.request_failed || 'Request failed. Please check your connection and try again.') + '</p>');
            },
        });
    }

    window.ttbmTravelTourPackageSkeletonHtml = ttbmTravelTourSkeletonHtml;
    window.ttbmShowTourPackageSkeleton = ttbmShowTourPackageSkeleton;
    window.ttbmReloadTourPackageList = ttbmReloadTourPackageList;

    function ttbmGetTravelListTabContainer(targetId) {
        const map = {
            ttbm_trvel_lists_location: '#ttbm_travel_list_location_shows',
            ttbm_trvel_lists_tour_category: '#ttbm_travel_list_category_content',
            ttbm_trvel_lists_organiser: '#ttbm_travel_list_organiser_content',
            ttbm_trvel_lists_features: '#ttbm_travel_list_feature_content',
            ttbm_trvel_lists_tag: '#ttbm_travel_list_tag_content',
            ttbm_trvel_lists_activities: '#ttbm_travel_list_activies_content',
            ttbm_trvel_lists_places: '#ttbm_travel_list_places_content',
            ttbm_trvel_lists_guides: '#ttbm_travel_list_guides_content',
            ttbm_trvel_lists_ticket_types: '#ttbm_travel_list_ticket_types_content',
        };
        return map[targetId] || null;
    }

    function ttbmShowTravelListSkeleton(selector, count) {
        if (!selector) {
            return;
        }
        $(selector).html(ttbmTravelTaxonomySkeletonHtml(count));
    }

    function clearTaxonomyPopupValidation(clearFormMessage) {
        const $popup = $('#ttbm_travel_list_popup');
        $popup.find('.ttbm-taxonomy-popup-field').removeClass('is-invalid');
        $popup.find('.ttbm-taxonomy-popup-input, .ttbm-taxonomy-popup-select, textarea, input').removeClass('is-invalid').removeAttr('aria-invalid');
        $popup.find('.ttbm-taxonomy-popup-error').attr('hidden', true).text('');
        if (clearFormMessage !== false) {
            $popup.find('.ttbm-taxonomy-popup-form-message').attr('hidden', true).removeClass('is-error is-success').text('');
        }
    }

    function showTaxonomyFieldError(fieldKey, message) {
        const $field = $('#ttbm_travel_list_popup .ttbm-taxonomy-popup-field[data-field="' + fieldKey + '"]');
        if (!$field.length) {
            showTaxonomyFormMessage(message, 'error');
            return false;
        }
        $field.addClass('is-invalid');
        const $input = $field.find('#' + fieldKey);
        if ($input.length) {
            $input.addClass('is-invalid').attr('aria-invalid', 'true').trigger('focus');
        }
        $field.find('.ttbm-taxonomy-popup-error').text(message).removeAttr('hidden');
        return true;
    }

    function showTaxonomyFormMessage(message, type) {
        const $message = $('#ttbm_travel_list_popup .ttbm-taxonomy-popup-form-message');
        $message
            .text(message)
            .removeClass('is-error is-success')
            .addClass(type === 'success' ? 'is-success' : 'is-error')
            .removeAttr('hidden');
    }

    function validateTaxonomyPopup() {
        clearTaxonomyPopupValidation();
        const name = $('#ttbm-location-name').val().trim();

        if (!name) {
            showTaxonomyFieldError('ttbm-location-name', ttbm_admin_ajax.strings?.name_required || 'Name is required.');
            return false;
        }

        return true;
    }

    $(document).on('input change', '#ttbm_travel_list_popup .ttbm-taxonomy-popup-input, #ttbm_travel_list_popup .ttbm-taxonomy-popup-select, #ttbm_travel_list_popup textarea, #ttbm_travel_list_popup input[name="ttbm_feature_icon"], #ttbm_travel_list_popup input[name="ttbm_activity_icon"]', function () {
        const $field = $(this).closest('.ttbm-taxonomy-popup-field');
        if (!$field.length) {
            return;
        }
        $field.removeClass('is-invalid');
        $field.find('.ttbm-taxonomy-popup-error').attr('hidden', true).text('');
        $(this).removeClass('is-invalid').removeAttr('aria-invalid');
        $('#ttbm_travel_list_popup .ttbm-taxonomy-popup-form-message').attr('hidden', true).removeClass('is-error is-success').text('');
    });

    // Hide taxonomy popup (Cancel, close icon, overlay, Escape).
    $(document).on('click', '#ttbm_travel_list_popup .ttbm-taxonomy-popup__cancel, #ttbm_travel_list_popup #ttbm-close-popup, #ttbm_travel_list_popup #ttbm-close-popup-footer', function (e) {
        e.preventDefault();
        e.stopPropagation();
        closeTaxonomyPopup();
    });

    $(document).on('click', '#ttbm_travel_list_popup #ttbm-location-popup.ttbm-popup-overlay', function (e) {
        if ($(e.target).is('#ttbm-location-popup')) {
            e.preventDefault();
            closeTaxonomyPopup();
        }
    });

    $(document).on('keydown.ttbmTaxonomyPopup', function (e) {
        if (e.key === 'Escape' && $('#ttbm_travel_list_popup #ttbm-location-popup').length) {
            closeTaxonomyPopup();
        }
    });
    $(document).on('click', '#ttbm-upload-image',function(e) {
        e.preventDefault();
        if (ttbm_image_frame) {
            ttbm_image_frame.open();
            return;
        }
        ttbm_image_frame = wp.media({
            title: 'Select Location Image',
            button: { text: 'Use this image' },
            multiple: false
        });
        ttbm_image_frame.on('select', function() {
            const attachment = ttbm_image_frame.state().get('selection').first().toJSON();
            console.log( attachment );
            $('#ttbm-location-image-id').val(attachment.id);
            $('#ttbm-image-preview').html('<img src="' + attachment.url + '" style="max-width:100px;">');
        });

        ttbm_image_frame.open();
    });

    function get_add_taxonomy_type( tab_type ){
        let taxonomy_type = '';
        if( tab_type === 'Add New Locations' ){
            taxonomy_type = 'ttbm_tour_location';
        }else if( tab_type === 'Add New Organiser' ){
            taxonomy_type = 'ttbm_tour_org';
        }else if( tab_type === 'Add New Feature' ){
            taxonomy_type = 'ttbm_tour_features_list';
        }else if( tab_type === 'Add New Tag' ){
            taxonomy_type = 'ttbm_tour_tag';
        }else if( tab_type === 'Add New Activities' ){
            taxonomy_type = 'ttbm_tour_activities';
        }else if( tab_type === 'Add New Category' ){
            taxonomy_type = 'ttbm_tour_cat';
        }

        return taxonomy_type;
    }
    function get_tab_action_name_by_tab( tab_action_type ){
        let tab_type = '';
        if( tab_action_type === 'Trip Location' ){
            tab_type = 'Add New Locations';
        }else if( tab_action_type === 'Tourist Attraction' ){
            tab_type = 'Add New Places';
        }else if( tab_action_type === 'Trip Organiser' ){
            tab_type = 'Add New Organiser';
        }else if( tab_action_type === 'Features' ){
            tab_type = 'Add New Feature';
        }else if( tab_action_type === 'Tags' ){
            tab_type = 'Add New Tag';
        }else if( tab_action_type === 'Activities' ){
            tab_type = 'Add New Activities';
        }else if( tab_action_type === 'Category' ){
            tab_type = 'Add New Category';
        }

        return tab_type;
    }

    $(document).on('click', '.ttbm-save-places_data', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const action_type = $btn.text().trim();
        const action = 'ttbm_add_edit_new_places_term';
        let post_id = '';
        if (action_type === 'Update') {
            post_id = $btn.parent().attr('id');
        }

        clearTaxonomyPopupValidation();
        const name = $('#ttbm-location-name').val().trim();

        if (!name) {
            showTaxonomyFieldError('ttbm-location-name', ttbm_admin_ajax.strings?.name_required || 'Name is required.');
            return;
        }

        const desc = $('#ttbm-location-desc').val().trim();
        const imageId = $('#ttbm-location-image-id').val();
        const originalText = $btn.data('original-text') || action_type;
        $btn.data('original-text', originalText).text(ttbm_admin_ajax.strings?.saving || 'Saving...').prop('disabled', true);

        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                post_id: post_id,
                post_name: name,
                description: desc,
                thumbnail_id: imageId,
                nonce: ttbm_admin_ajax.nonce,
                action: action,
            },
            success: function (response) {
                $btn.text(originalText).prop('disabled', false);
                if (response.success) {
                    closeTaxonomyPopup();
                    location.reload();
                    return;
                }
                showTaxonomyFormMessage(response.data?.message || ttbm_admin_ajax.strings?.save_failed || 'Something went wrong. Please try again.', 'error');
            },
            error: function () {
                $btn.text(originalText).prop('disabled', false);
                showTaxonomyFormMessage(ttbm_admin_ajax.strings?.request_failed || 'Request failed. Please check your connection and try again.', 'error');
            }
        });
    });

    $(document).on('click', '.ttbm-save-location', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const tab_type = $btn.siblings('.ttbm_get_clicked_tab_name').val().trim();
        const taxonomy_type = get_add_taxonomy_type(tab_type);

        if (!validateTaxonomyPopup()) {
            return;
        }

        const action_type = $btn.text().trim();
        let term_id = '';
        if (action_type !== 'Save') {
            term_id = $btn.parent().attr('id');
        }

        const name = $('#ttbm-location-name').val().trim();
        const slug = $('#ttbm-location-slug').val().trim();
        const desc = $('#ttbm-location-desc').val().trim();
        const parent = $('#ttbm-location-parent').val();

        let address = '';
        let country = '';
        let imageId = '';

        if (tab_type === 'Add New Locations') {
            address = $('#ttbm-location-address').val().trim();
            country = $('#ttbm-location-country').val();
            imageId = $('#ttbm-location-image-id').val();
        }

        let icon = '';
        if (tab_type === 'Add New Feature') {
            icon = $('[name="ttbm_feature_icon"]').val();
        }

        if (tab_type === 'Add New Activities') {
            icon = $('[name="ttbm_activity_icon"]').val();
        }

        const originalText = $btn.data('original-text') || action_type;
        $btn.data('original-text', originalText).text(ttbm_admin_ajax.strings?.saving || 'Saving...').prop('disabled', true);

        $.post(ttbm_admin_ajax.ajax_url, {
            action: 'ttbm_add_new_location_term',
            nonce: ttbm_admin_ajax.nonce,
            name,
            slug,
            parent,
            desc,
            address,
            country,
            imageId,
            term_id,
            action_type,
            taxonomy_type,
            icon,
        },
        function (response) {
            $btn.text(originalText).prop('disabled', false);
            if (response.success) {
                closeTaxonomyPopup();
                location.reload();
                return;
            }
            const serverMessage = response.data?.message || ttbm_admin_ajax.strings?.save_failed || 'Something went wrong. Please try again.';
            if (serverMessage.toLowerCase().includes('name')) {
                showTaxonomyFieldError('ttbm-location-name', serverMessage);
                return;
            }
            showTaxonomyFormMessage(serverMessage, 'error');
        }).fail(function () {
            $btn.text(originalText).prop('disabled', false);
            showTaxonomyFormMessage(ttbm_admin_ajax.strings?.request_failed || 'Request failed. Please check your connection and try again.', 'error');
        });
    });
    
    $(document).on( 'click', '.ttbm-add-new-taxonomy-btn', function () {

        let term_id = '';
        let tab_type = $(this).text().trim();

        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                term_id: term_id,
                tab_type: tab_type,
                nonce:  ttbm_admin_ajax.nonce,
                action: 'ttbm_add_new_locations_ajax_html',
            },
            success: function (response) {
                if (response.success) {
                    $('#ttbm_travel_list_popup').html(response.data.add_popup );
                }
            }
        });
    })

    $(document).on( 'click', '.ttbm_delete_taxonomy_data', function ( e ) {
        e.preventDefault();
        let term_id = $(this).parent().attr('ttbm-data-location-id');
        let tab_type = $('.ttbm_trvel_lists_tabs button.active').data('tab-type');
        const clicked_btn = $(this);
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                term_id: term_id,
                tab_type: tab_type,
                nonce:  ttbm_admin_ajax.nonce,
                action: 'ttbm_delete_taxonomy_data_by_id',
            },
            success: function (response) {
                if (response.success) {
                    if( tab_type === 'Add New Locations' || tab_type === 'Add New Places' ){
                        clicked_btn.closest('.ttbm-location-card').fadeOut;
                    }else{
                        clicked_btn.closest('.ttbm-taxonomy-card').fadeOut();
                    }
                    alert( response.data.message);
                }
            }
        });
    });

    $(document).on( 'click', '.ttbm_edit_trip_location', function () {

        let term_id = $(this).parent().attr('ttbm-data-location-id');
        let tab_type = $('.ttbm_trvel_lists_tabs button.active').data('tab-type');

        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                term_id: term_id,
                tab_type: tab_type,
                nonce:  ttbm_admin_ajax.nonce,
                action: 'ttbm_edit_locations_ajax_html',
            },
            success: function (response) {
                if (response.success) {
                    // $('#ttbm_travel_list_location_shows').html(response.data.html); // render HTML
                    $('#ttbm_travel_list_popup').html(response.data.edit_popup );
                }
            }
        });
    })

    /*Search By Title Tab Data*/
    $(document).on('keyup', '#ttbm_tourist_location_Search', function() {
        let searchText = $(this).val().toLowerCase();
        ttbm_function_search_title( searchText, 'ttbm_search_location_by_title' );
    });
    $(document).on('keyup', '#ttbm_tab_features_Search', function() {
        let searchText = $(this).val().toLowerCase();
        ttbm_function_search_title( searchText, 'ttbm_search_from_feature' );
    });
    $(document).on('keyup', '#ttbm_tourist_organiser_Search', function() {
        let searchText = $(this).val().toLowerCase();
        ttbm_function_search_title( searchText, 'ttbm_search_from_organiser' );
    });
    $(document).on('keyup', '#ttbm_tab_tag_Search', function() {
        let searchText = $(this).val().toLowerCase();
        ttbm_function_search_title( searchText, 'ttbm_search_from_tag' );
    });
    $(document).on('keyup', '#ttbm_tab_activities_Search', function() {
        let searchText = $(this).val().toLowerCase();
        ttbm_function_search_title( searchText, 'ttbm_search_from_activity' );
    });
    $(document).on('keyup', '#ttbm_tab_category_search', function() {
        let searchText = $(this).val().toLowerCase();
        ttbm_function_search_title( searchText, 'ttbm_search_from_category' );
    });
    $(document).on('keyup', '#ttbm_tourist_place_Search', function() {
        let searchText = $(this).val().toLowerCase();
        ttbm_function_search_title( searchText, 'ttbm_search_place_by_title' );
    });

    $(document).on('click', '.ttbm_travel_filter_item', function() {

        $('.ttbm_travel_filter_item').removeClass('ttbm_filter_btn_active_bg_color').addClass('ttbm_filter_btn_bg_color');
        $(this).removeClass('ttbm_filter_btn_bg_color').addClass('ttbm_filter_btn_active_bg_color');
        let searchText = $(this).attr('data-filter-item');
        let expired_find = '';
        if( searchText === 'expired_tour' ) {
            expired_find = 'expire';
        }
        ttbm_function_filter_by_post_type( searchText, 'ttbm-tour-card', expired_find );
        ttbm_reload_tour_list_by_filter(searchText);
    });
    function ttbm_reload_tour_list_by_filter(selected_filter) {
        let $loadMore = $('#ttbm-load-more');
        if (!$loadMore.length) {
            const loadMoreHtml = '<button id="ttbm-load-more" class="button" data-paged="2" data-posts-per-page="10" data-nonce="' + ttbm_admin_ajax.nonce + '"><i class="fas fa-sync-alt"></i> Load More (<span class="ttbm_load_more_remaining_travel">0</span>)</button>';
            if ($('.ttbm-load-more-wrap').length) {
                $('.ttbm-load-more-wrap').html(loadMoreHtml).show();
            } else {
                $('.ttbm-tour-list_holder').append('<div class="ttbm-load-more-wrap">' + loadMoreHtml + '</div>');
            }
            $loadMore = $('#ttbm-load-more');
        }
        let postPerPage = parseInt($loadMore.data('posts-per-page'), 10);
        if (!postPerPage || postPerPage < 1) {
            postPerPage = 10;
        }
        let searchText = $('#ttbm-tour-search').val() || '';
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_search_tours',
                paged: 1,
                post_per_page: postPerPage,
                selected_filter: selected_filter,
                search_term: searchText,
                nonce: ttbm_admin_ajax.nonce
            },
            beforeSend: function () {
                ttbmShowTourPackageSkeleton('.ttbm-tour-list', 4);
            },
            success: function (response) {
                if (!(response && response.success && response.data && typeof response.data.html !== 'undefined')) {
                    $('.ttbm-tour-list').html('<p class="ttbm_travel_list_load_error">' + (ttbm_admin_ajax.strings?.no_tours_found || 'No tours found.') + '</p>');
                    $('#ttbm-load-more').hide();
                    return;
                }
                $('.ttbm-tour-list').html(response.data.html);
                let foundPosts = parseInt(response.data.found_posts || 0, 10);
                let loadedPosts = $('.ttbm-tour-list .ttbm-tour-card').length;
                let remaining = Math.max(foundPosts - loadedPosts, 0);
                if ($loadMore.length) {
                    if (remaining > 0) {
                        $loadMore.attr('data-paged', 2);
                        $loadMore.attr('data-posts-per-page', postPerPage);
                        $loadMore.html('<i class="fas fa-sync-alt"></i> Load More (<span class="ttbm_load_more_remaining_travel">' + remaining + '</span>)');
                        $loadMore.show();
                    } else {
                        $loadMore.hide();
                    }
                }
            }
        });
    }
    function ttbm_function_filter_by_post_type( searchText, class_name, expired_find='' ){
        $('.'+class_name).each(function() {
            let by_filter = '';
            let post_status = ($(this).data('travel-type') || '').toLowerCase();
            if( expired_find === 'expire' ){
                by_filter = $(this).data('expire-tour').toLowerCase();
            }else{
                by_filter = post_status;
            }

            if( searchText === 'all' ){
                $(this).fadeIn();
            }else{
                if (expired_find === 'expire' && post_status !== 'publish') {
                    $(this).fadeOut();
                    return;
                }
                if ( by_filter.includes( searchText ) ) {
                    $(this).fadeIn();
                } else {
                    $(this).fadeOut();
                }
            }

        });
    }

    function ttbm_function_search_title( searchText, class_name ){
        $('.'+class_name).each(function() {
            let location = $(this).data('taxonomy').toLowerCase();

            if ( location.includes( searchText ) ) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    $(document).on('click', '.ttbm_places_load_more_btn', function( e ) {
        e.preventDefault();
        let loaded_post_ids = [];
        let $this = $(this);
        $this.text( 'Loading...' );

        $('[ttbm-data-places-id]').each(function() {
            let id = $(this).attr('ttbm-data-places-id');
            if (id) {
                loaded_post_ids.push(id);
            }
        });

        let total_loaded = loaded_post_ids.length;

        let loaded_post_ids_str = loaded_post_ids.join(',');
        // console.log(idString);
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                loaded_post_ids_str: loaded_post_ids_str,
                nonce:  ttbm_admin_ajax.nonce,
                action: 'ttbm_get_places_html_data',
            },
            success: function (response) {
                if (response.success) {
                    if (response.success) {
                        if( response.data.total_found < 5){
                            $("#ttbm_places_load_more_holder").fadeOut();
                        }else{
                            $("#ttbm_places_load_more_holder").fadeIn();
                            let remaining = response.data.all_places_count - response.data.total_found;
                            $this.text( 'Load More('+remaining+')' );
                        }

                        $('#ttbm_travel_list_places_content').append(response.data.html);

                    }
                }
            }
        });

    });

    $(document).on('click', '.ttbm_show_location_shortcode', function() {
        const textToCopy = $(this).text().trim();
        const tempInput = $('<textarea>');

        $('body').append(tempInput);
        tempInput.val(textToCopy).select();
        document.execCommand('copy');
        tempInput.remove();

        alert('Copied to clipboard: ' + textToCopy);
    });

   // =====================sidebar modal open close=============
   // this script updated version for conflict ttbm-modal
    $(document).on('click', '[data-ttbm-modal]', function (e) {
        const modalTarget = $(this).data('ttbm-modal');
        $(`[data-ttbm-modal-target="${modalTarget}"]`).addClass('open');
    });
    $(document).on('click', '[data-ttbm-modal-target] .ttbm-modal-close', function (e) {
        $(this).closest('[data-ttbm-modal-target]').removeClass('open');
    });

    function close_sidebar_modal(e) {
        e.preventDefault();
        e.stopPropagation();
        $('.ttbm-modal-container').removeClass('open');
    }
    //==============FAQ==================
    $(document).on('click', '.ttbm-hotel-faq-new', function (e) {
        $('#ttbm-hotel-faq-msg').html('');
        $('.ttbm_hotel_faq_save').show();
        $('.ttbm_hotel_faq_update').hide();
        empty_faq_form();
    });

    $(document).on('click', '.ttbm-hotel-faq-edit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#ttbm-hotel-faq-msg').html('');
        $('.ttbm_hotel_faq_save').hide();
        $('.ttbm_hotel_faq_update').show();
        var itemId = $(this).closest('.ttbm-faq-item').data('id');
        var parent = $(this).closest('.ttbm-faq-item');
        var headerText = parent.find('.faq-header p').text().trim();
        var faqContentId = parent.find('.faq-content').text().trim();
        var editorId = 'ttbm_hotel_faq_content';
        $('input[name="ttbm_hotel_faq_title"]').val(headerText);
        $('input[name="ttbm_faq_item_id"]').val(itemId);
        if (tinymce.get(editorId)) {
            tinymce.get(editorId).setContent(faqContentId);
        } else {
            $('#' + editorId).val(faqContentId);
        }
    });

    $(document).on('click', '.ttbm-hotel-faq-delete', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var itemId = $(this).closest('.ttbm-faq-item').data('id');
        var isConfirmed = confirm('Are you sure you want to delete this row?');
        if (isConfirmed) {
            delete_faq_item(itemId);
        } else {
            console.log('Deletion canceled.' + itemId);
        }
    });

    function empty_faq_form() {
        $('input[name="ttbm_hotel_faq_title"]').val('');
        var editorId = 'ttbm_hotel_faq_content';
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get(editorId)) {
            tinyMCE.get(editorId).setContent('');
        } else {
            $('#' + editorId).val('');
        }
        $('input[name="ttbm_faq_item_id"]').val('');
    }

    $(document).on('click', '#ttbm_hotel_faq_update_btn', function (e) {
        e.preventDefault();
        update_faq();
    });

    $(document).on('click', '#ttbm_hotel_faq_save', function (e) {
        e.preventDefault();
        save_faq();
    });

    $(document).on('click', '#ttbm_hotel_faq_save_close', function (e) {
        e.preventDefault();
        save_faq();
        close_sidebar_modal(e);
    });

    function update_faq() {
        var title = $('input[name="ttbm_hotel_faq_title"]');
        var content = (typeof tinyMCE !== 'undefined' && tinyMCE.get('ttbm_hotel_faq_content')) ? tinyMCE.get('ttbm_hotel_faq_content').getContent() : $('#ttbm_hotel_faq_content').val();
        var postID = $('input[name="ttbm_post_id"]');
        var itemId = $('input[name="ttbm_faq_item_id"]');
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_hotel_faq_update',
                ttbm_hotel_faq_title: title.val(),
                ttbm_hotel_faq_content: content,
                ttbm_faq_postID: postID.val(),
                ttbm_faq_itemID: itemId.val(),
                nonce: ttbm_admin_ajax.nonce
            },
            success: function (response) {
                $('#ttbm-hotel-faq-msg').html(response.data.message);
                $('.ttbm-hotel-faq-items').html('');
                $('.ttbm-hotel-faq-items').append(response.data.html);
                setTimeout(function () {
                    $('.ttbm-modal-container').removeClass('open');
                    empty_faq_form();
                }, 1000);
            },
            error: function (error) {
                console.log('Error:', error);
            }
        });
    }

    function save_faq() {
        var title = $('input[name="ttbm_hotel_faq_title"]');
        var content = (typeof tinyMCE !== 'undefined' && tinyMCE.get('ttbm_hotel_faq_content')) ? tinyMCE.get('ttbm_hotel_faq_content').getContent() : $('#ttbm_hotel_faq_content').val();
        var postID = $('input[name="ttbm_post_id"]');
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_hotel_faq_save',
                ttbm_hotel_faq_title: title.val(),
                ttbm_hotel_faq_content: content,
                ttbm_faq_postID: postID.val(),
                nonce: ttbm_admin_ajax.nonce
            },
            success: function (response) {
                $('#ttbm-hotel-faq-msg').html(response.data.message);
                $('.ttbm-hotel-faq-items').html('');
                $('.ttbm-hotel-faq-items').append(response.data.html);
                empty_faq_form();
            },
            error: function (error) {
                console.log('Error:', error);
            }
        });
    }

    function delete_faq_item(itemId) {
        var postID = $('input[name="ttbm_post_id"]');
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_hotel_faq_delete',
                ttbm_faq_postID: postID.val(),
                itemId: itemId,
                nonce: ttbm_admin_ajax.nonce
            },
            success: function (response) {
                $('.ttbm-hotel-faq-items').html('');
                $('.ttbm-hotel-faq-items').append(response.data.html);
            },
            error: function (error) {
                console.log('Error:', error);
            }
        });
    }
    // sort faq
    $(document).on("ready", function(e) {
        $(".ttbm-hotel-faq-items").sortable({
            update: function(event, ui) {
                event.preventDefault();
                var sortedIDs = $(this).sortable("toArray", { attribute: "data-id" });
                $.ajax({
                    url: ttbm_admin_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'ttbm_hotel_ttbm_faq_sort',
                        postID: $('input[name="ttbm_post_id"]').val(),
                        sortedIDs: sortedIDs,
                        nonce: ttbm_admin_ajax.nonce
                    },
                    success: function (response) {
                        $('.ttbm-hotel-faq-items').html('');
                        $('.ttbm-hotel-faq-items').append(response.data.html);
                    },
                    error: function (error) {
                        console.log('Error:', error);
                    }
                })
            }
        });
    });
    
    // ==========hotel-area-info.js=============
    $(document).ready(function($) {
        // Helper: get next area index
        function getNextAreaIndex() {
            let max = -1;
            $('#ttbm-hotel-area-info-wrapper .ttbm-htl-area-section').each(function() {
                const idx = parseInt($(this).attr('data-area-index'), 10);
                if (!isNaN(idx) && idx > max) max = idx;
            });
            return max + 1;
        }
        // Helper: get next item index for area
        function getNextItemIndex($areaSection) {
            let max = -1;
            $areaSection.find('.ttbm-htl-area-item').each(function() {
                const idx = parseInt($(this).attr('data-item-index'), 10);
                if (!isNaN(idx) && idx > max) max = idx;
            });
            return max + 1;
        }
        // Area template
        function areaTemplate(areaIndex) {
            return `<div class="ttbm-htl-area-section" data-area-index="${areaIndex}">
                <div class="ttbm-htl-area-header">
                    <input type="hidden" name="ttbm_hotel_area_info[${areaIndex}][area_icon]" value="mi mi-home">
                    <div class="icon">
                        <div class="ttbm_style">
                            <div class="ttbm_add_icon_image_area fdColumn">
                                <input type="hidden" name="ttbm_hotel_area_info[0][area_icon]" value="mi mi-apartment">
                                <div class="ttbm_icon_item ">
                                    <div class="allCenter">
                                        <span class="mi mi-restaurants" data-add-icon=""></span>
                                    </div>
                                    <span class="mi mi-x ttbm_icon_remove" title="Remove Icon"></span>
                                </div>
                                <div class="ttbm_add_icon_image_button_area dNone">
                                    <div class="flexEqual">
                                        <button class="_mpBtn_xs ttbm_icon_add" type="button" data-target-popup="#ttbm_add_icon_popup" title="Add Icon" aria-label="Add Icon">
                                            <span class="mi mi-plus" aria-hidden="true"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="text" name="ttbm_hotel_area_info[${areaIndex}][area_title]" class="ttbm-htl-area-title"
                        value="" placeholder="What's nearby">
                    <div class="action-buttons">
                        <button type="button" class="btn icon btn-add ttbm-add-area" data-area-index="${areaIndex}">
                            <i class="mi mi-plus"></i>
                        </button>
                        <button type="button" class="btn icon btn-delete ttbm-delete-area" data-area-index="${areaIndex}">
                            <i class="mi mi-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="ttbm-htl-area-items">
                    ${itemTemplate(areaIndex, 0)}
                </div>
            </div>`;
        }
        // Item template
        function itemTemplate(areaIndex, itemIndex) {
            return `<div class="ttbm-htl-area-item" data-item-index="${itemIndex}">
                <input type="text" name="ttbm_hotel_area_info[${areaIndex}][area_items][${itemIndex}][item_title]"
                    class="ttbm-htl-area-item-title"
                    value="" placeholder="Feature name">
                <input type="number" step="any" min="0"
                    name="ttbm_hotel_area_info[${areaIndex}][area_items][${itemIndex}][item_distance]"
                    class="ttbm-htl-area-item-distance"
                    value="" placeholder="Distance">
                <input type="text"
                    name="ttbm_hotel_area_info[${areaIndex}][area_items][${itemIndex}][item_type]"
                    class="ttbm-htl-area-item-type"
                    value="" placeholder="Type (e.g. km, m)">
                <div class="action-buttons">
                    <button type="button" class="icon ttbm-add-area-item" data-area-index="${areaIndex}" data-item-index="${itemIndex}">
                        <i class="mi mi-plus"></i>
                    </button>
                    <button type="button" class="icon ttbm-delete-feature" data-area-index="${areaIndex}" data-item-index="${itemIndex}">
                        <i class="mi mi-trash"></i>
                    </button>
                </div>
            </div>`;
        }
        // Add new area
        $('.ttbm-add-area-info').on('click', function() {
            const areaIndex = getNextAreaIndex();
            $('#ttbm-hotel-area-info-wrapper').append(areaTemplate(areaIndex));
        });
        // Add area from plus button in header
        $('#ttbm-hotel-area-info-wrapper').on('click', '.ttbm-add-area', function() {
            const areaIndex = getNextAreaIndex();
            $('#ttbm-hotel-area-info-wrapper').append(areaTemplate(areaIndex));
        });
        // Delete area
        $('#ttbm-hotel-area-info-wrapper').on('click', '.ttbm-delete-area', function() {
            if (confirm('Are you sure you want to delete this area?')) {
                $(this).closest('.ttbm-htl-area-section').remove();
            }
        });
        // Add feature/item
        $('#ttbm-hotel-area-info-wrapper').on('click', '.ttbm-add-area-item', function() {
            const $areaSection = $(this).closest('.ttbm-htl-area-section');
            const areaIndex = $areaSection.attr('data-area-index');
            let itemIndex = getNextItemIndex($areaSection);

            // If clicked from inside an item, insert after that item
            const $item = $(this).closest('.ttbm-htl-area-item');
            if ($item.length) {
                $item.after(itemTemplate(areaIndex, itemIndex));
            } else {
                // Otherwise, add before the "Add New Feature" button at the end
                $(this).closest('.ttbm-htl-area-items').find('button.ttbm-add-feature').before(itemTemplate(areaIndex, itemIndex));
            }
        });
        // Delete feature/item
        $('#ttbm-hotel-area-info-wrapper').on('click', '.ttbm-delete-feature', function() {
            if (confirm('Are you sure you want to delete this feature?')) {
                $(this).closest('.ttbm-htl-area-item').remove();
            }
        });
    });

    // =================hotel-feature js=============
    $(document).on('click', '.ttbm-hotel-new-feature', function (e) {
        $('#ttbm-hotel-feature-msg').html('');
        $('.ttbm_hotel_feature_save').show();
        $('.ttbm_hotel_feature_update').hide();
        empty_feature_form();
        
    });

    $(document).on('click', '.ttbm-hotel-edit-feature', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#ttbm-hotel-faq-msg').html('');
        $('.ttbm_hotel_feature_save').hide();
        $('.ttbm_hotel_feature_update').show();

        var itemId = $(this).closest('.ttbm-features-item').data('id');
        var parent = $(this).closest('.ttbm-features-item');
        var title = parent.data('name') || '';
        var slug = parent.data('slug') || '';
        var icon = parent.data('icon') || '';
        var description = parent.data('description') || '';
        // Hide or show icon/image button area based on icon value
        
        var $iconItem = $('#ttbm-hotel-feature-form').find('.ttbm_icon_item');
        var $iconButtonArea = $('#ttbm-hotel-feature-form').find('.ttbm_add_icon_image_button_area');
        if (icon) {
            $iconItem.removeClass('dNone').show();
            $iconButtonArea.addClass('dNone').hide();
        } else {
            $iconItem.addClass('dNone').hide();
            $iconButtonArea.removeClass('dNone').show();
        }
        $('#ttbm-hotel-feature-form').find('.ttbm_icon_item .allCenter span[data-add-icon]')
            .attr('class', icon)
            .attr('data-add-icon', '');

        var $form = $('#ttbm-hotel-feature-form');
        $form.find('input[name="ttbm_hotel_feature_title"]').val(title);
        $form.find('input[name="ttbm_hotel_feature_slug"]').val(slug);
        $form.find('input[name="ttbm_hotel_feature_icon"]').val(icon);
        $form.find('textarea[name="ttbm_hotel_feature_description"]').val(description);
        $form.find('input[name="ttbm_hotel_feature_id"]').val(itemId);
    });

    function empty_feature_form() {
        $('input[name="ttbm_hotel_feature_title"]').val('');
        $('input[name="ttbm_hotel_feature_slug"]').val('');
        $('input[name="ttbm_hotel_feature_icon"]').val('');
        $('textarea[name="ttbm_hotel_feature_description"]').val('');
        $('#ttbm-hotel-feature-form').find('.ttbm_icon_item').addClass('dNone').hide();
        $('#ttbm-hotel-feature-form').find('.ttbm_add_icon_image_button_area').show();
        console.log('new feature clicked');
    }

    $(document).on('click', '#ttbm_hotel_feature_save', function (e) {
        e.preventDefault();
        ttbm_hotel_feature_save();
    });

    $(document).on('click', '#ttbm_hotel_feature_save_close', function (e) {
        e.preventDefault();
        ttbm_hotel_feature_save();
        close_sidebar_modal(e);
    });

    $(document).on('click', '.ttbm-hotel-delete-feature', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var itemId = $(this).closest('.ttbm-features-item').data('id');
        var isConfirmed = confirm('Are you sure you want to delete this row?');
        if (isConfirmed) {
            ttbm_hotel_feature_delete(itemId);
        } else {
            console.log('Deletion canceled.');
        }
    });

    $(document).on('click', '#ttbm_hotel_feature_update_btn', function (e) {
        e.preventDefault();
        ttbm_hotel_feature_update();
    });

    function ttbm_hotel_feature_update() {
        var $form = $('#ttbm-hotel-feature-form');
        var formData = {};
        $form.find('input, textarea').each(function() {
            var name = $(this).attr('name');
            if (name) {
                formData[name] = $(this).val();
            }
        });
        formData.action = 'ttbm_hotel_feature_update';
        formData.nonce = ttbm_admin_ajax.nonce;

        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function (response) {
                $('#ttbm-hotel-feature-msg').html(response.data.message);
                $('.ttbm-hotel-feature-items').html('');
                $('.ttbm-hotel-feature-items').append(response.data.html);
                empty_feature_form();
                setTimeout(function () {
                    $('.ttbm-modal-container').removeClass('open');
                }, 1000);
            },
            error: function (error) {
                console.log('Error:d', error);
            }
        });
    }

    function ttbm_hotel_feature_save() {
        var formData = {};
        $('#ttbm-hotel-feature-form').find('input, textarea').each(function() {
            var name = $(this).attr('name');
            if (name) {
                formData[name] = $(this).val();
            }
        });
        formData.action = 'ttbm_hotel_feature_save';
        formData.nonce = ttbm_admin_ajax.nonce;

        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function (response) {
                $('#ttbm-hotel-feature-msg').html(response.data.message);
                $('.ttbm-hotel-feature-items').html('');
                $('.ttbm-hotel-feature-items').append(response.data.html);
                empty_feature_form();
            },
            error: function (error) {
                console.log('Error:', error);
            }
        });
    }

    function ttbm_hotel_feature_delete(itemId) {
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_hotel_feature_delete',
                itemId: itemId,
                nonce: ttbm_admin_ajax.nonce
            },
            success: function (response) {
                $('#ttbm-hotel-feature-msg').html(response.data.message);
                $('.ttbm-hotel-feature-items').html('');
                $('.ttbm-hotel-feature-items').append(response.data.html);
            },
            error: function (error) {
                console.log('Error:', error);
            }
        });
    }

    // =================hotel-activity js=============
    $(document).on('click', '.ttbm-hotel-new-activity', function (e) {
        $('#ttbm-hotel-activity-msg').html('');
        $('.ttbm_hotel_activity_save').show();
        $('.ttbm_hotel_activity_update').hide();
        empty_activity_form();
    });

    function empty_activity_form() {
        $('input[name="ttbm_hotel_activity_title"]').val('');
        $('input[name="ttbm_hotel_activity_slug"]').val('');
        $('input[name="ttbm_hotel_activity_icon"]').val('');
        $('textarea[name="ttbm_hotel_activity_description"]').val('');
        $('#ttbm-hotel-activity-form').find('.ttbm_icon_item').addClass('dNone').hide();
        $('#ttbm-hotel-activity-form').find('.ttbm_add_icon_image_button_area').show();
    }

    $(document).on('click', '#ttbm_hotel_activity_save', function (e) {
        e.preventDefault();
        ttbm_hotel_activity_save();
    });

    $(document).on('click', '#ttbm_hotel_activity_save_close', function (e) {
        e.preventDefault();
        ttbm_hotel_activity_save();
        close_sidebar_modal(e);
    });

    function ttbm_hotel_activity_save() {
        var formData = {};
        $('#ttbm-hotel-activity-form').find('input, textarea').each(function() {
            var name = $(this).attr('name');
            if (name) {
                formData[name] = $(this).val();
            }
        });
        formData.action = 'ttbm_hotel_activity_save';
        formData.nonce = ttbm_admin_ajax.nonce;

        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function (response) {
                $('#ttbm-hotel-activity-msg').html(response.data.message);
                $('.ttbm-hotel-activity-items').html('');
                $('.ttbm-hotel-activity-items').append(response.data.html);
                empty_feature_form();
            },
            error: function (error) {
                console.log('Error:', error);
            }
        });
    }

    $(document).on('click', '.ttbm-hotel-edit-activity', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#ttbm-hotel-activity-msg').html('');
        $('.ttbm_hotel_activity_save').hide();
        $('.ttbm_hotel_activity_update').show();

        var itemId = $(this).closest('.ttbm-features-item').data('id');
        var parent = $(this).closest('.ttbm-features-item');
        var title = parent.data('name') || '';
        var slug = parent.data('slug') || '';
        var icon = parent.data('icon') || '';
        var description = parent.data('description') || '';
        // Hide or show icon/image button area based on icon value
        
        var $iconItem = $('#ttbm-hotel-activity-form').find('.ttbm_icon_item');
        var $iconButtonArea = $('#ttbm-hotel-activity-form').find('.ttbm_add_icon_image_button_area');
        if (icon) {
            $iconItem.removeClass('dNone').show();
            $iconButtonArea.addClass('dNone').hide();
        } else {
            $iconItem.addClass('dNone').hide();
            $iconButtonArea.removeClass('dNone').show();
        }
        $('#ttbm-hotel-activity-form').find('.ttbm_icon_item .allCenter span[data-add-icon]')
            .attr('class', icon)
            .attr('data-add-icon', '');

        var $form = $('#ttbm-hotel-activity-form');
        $form.find('input[name="ttbm_hotel_activity_title"]').val(title);
        $form.find('input[name="ttbm_hotel_activity_slug"]').val(slug);
        $form.find('input[name="ttbm_hotel_activity_icon"]').val(icon);
        $form.find('textarea[name="ttbm_hotel_activity_description"]').val(description);
        $form.find('input[name="ttbm_hotel_activity_id"]').val(itemId);
    });

    $(document).on('click', '#ttbm_hotel_activity_update_btn', function (e) {
        e.preventDefault();
        ttbm_hotel_activity_update();
    });

    function ttbm_hotel_activity_update() {
        var $form = $('#ttbm-hotel-activity-form');
        var formData = {};
        $form.find('input, textarea').each(function() {
            var name = $(this).attr('name');
            if (name) {
                formData[name] = $(this).val();
            }
        });
        formData.action = 'ttbm_hotel_activity_update';
        formData.nonce = ttbm_admin_ajax.nonce;

        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function (response) {
                $('#ttbm-hotel-activity-msg').html(response.data.message);
                $('.ttbm-hotel-activity-items').html('');
                $('.ttbm-hotel-activity-items').append(response.data.html);
                empty_feature_form();
                setTimeout(function () {
                    $('.ttbm-modal-container').removeClass('open');
                }, 1000);
            },
            error: function (error) {
                console.log('Error:', error);
            }
        });
    }

    $(document).on('click', '.ttbm-hotel-delete-activity', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var itemId = $(this).closest('.ttbm-features-item').data('id');
        var isConfirmed = confirm('Are you sure you want to delete this row?');
        if (isConfirmed) {
            ttbm_hotel_activity_delete(itemId);
        } else {
            console.log('Deletion canceled.');
        }
    });

    function ttbm_hotel_activity_delete(itemId) {
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_hotel_activity_delete',
                itemId: itemId,
                nonce: ttbm_admin_ajax.nonce
            },
            success: function (response) {
                $('#ttbm-hotel-activity-msg').html(response.data.message);
                $('.ttbm-hotel-activity-items').html('');
                $('.ttbm-hotel-activity-items').append(response.data.html);
            },
            error: function (error) {
                console.log('Error:', error);
            }
        });
    }

    // Copy shortcode to clipboard when clicked
    $(document).on('click', '.ttbm_show_location_shortcode', function(e) {
        e.preventDefault();
        var shortcode = $(this).text().trim();
        var $this = $(this);
        
        // Create temporary input element
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(shortcode).select();
        document.execCommand("copy");
        $temp.remove();
        
        // Show feedback
        var originalText = $this.html();
        $this.html('✓ Copied!');
        $this.css('background-color', '#4CAF50');
        $this.css('color', 'white');
        
        setTimeout(function() {
            $this.html(originalText);
            $this.css('background-color', '');
            $this.css('color', '');
        }, 2000);
    });

    // ─── Wishlist Tab Filtering & Pagination ─────────────────
    $(document).on('click', '#ttbm-wishlist-filter-btn', function() {
        ttbm_wishlist_load(1);
    });
    $(document).on('keypress', '#ttbm-wishlist-search', function(e) {
        if (e.which === 13) { e.preventDefault(); ttbm_wishlist_load(1); }
    });
    $(document).on('click', '#ttbm-wishlist-reset-btn', function() {
        $('#ttbm-wishlist-search').val('');
        $('#ttbm-wishlist-tour-filter').val('0');
        ttbm_wishlist_load(1);
    });
    $(document).on('click', '#ttbm-wishlist-pagination .button[data-page]', function() {
        ttbm_wishlist_load($(this).data('page'));
    });

    function ttbm_wishlist_load(page) {
        var search  = $('#ttbm-wishlist-search').val();
        var tourId  = $('#ttbm-wishlist-tour-filter').val();
        var $wrap   = $('#ttbm_trvel_lists_wishlist .ttbm-wishlist-admin-wrap');
        $wrap.css('opacity', 0.5);
        $.post(ttbm_admin_ajax.ajax_url, {
            action: 'ttbm_wishlist_admin_data',
            nonce: ttbm_admin_ajax.nonce,
            search: search,
            tour_id: tourId,
            paged: page
        }, function(res) {
            if (res.success) {
                $wrap.replaceWith(res.data.html);
            }
        });
    }

    /* ─────────────────────────────────────────────────────────────
     * Tour List → Guides & Ticket Types tabs (add/edit/delete modal).
     * Reuses the taxonomy popup shell + helpers defined above.
     * ───────────────────────────────────────────────────────────── */

    function ttbmOpenCptForm(action, postId) {
        $('#ttbm_travel_list_popup').html('<div id="ttbm-location-popup" class="ttbm-popup-overlay" style="display:flex;"><div class="ttbm-cpt-form-loading"><span class="fas fa-spinner fa-spin"></span></div></div>');
        $('body').addClass('noScroll');
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: { action: action, post_id: postId || 0, nonce: ttbm_admin_ajax.nonce },
            success: function (response) {
                if (response.success) {
                    $('#ttbm_travel_list_popup').html(response.data.html);
                } else {
                    closeTaxonomyPopup();
                }
            },
            error: function () {
                closeTaxonomyPopup();
            }
        });
    }

    function ttbmSubmitCptForm($btn, data) {
        var originalText = $btn.data('original-text') || $btn.text().trim();
        $btn.data('original-text', originalText).text(ttbm_admin_ajax.strings && ttbm_admin_ajax.strings.saving ? ttbm_admin_ajax.strings.saving : 'Saving...').prop('disabled', true);
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: data,
            success: function (response) {
                $btn.text(originalText).prop('disabled', false);
                if (response.success) {
                    closeTaxonomyPopup();
                    location.reload();
                    return;
                }
                showTaxonomyFormMessage((response.data && response.data.message) || (ttbm_admin_ajax.strings && ttbm_admin_ajax.strings.save_failed) || 'Something went wrong. Please try again.', 'error');
            },
            error: function () {
                $btn.text(originalText).prop('disabled', false);
                showTaxonomyFormMessage((ttbm_admin_ajax.strings && ttbm_admin_ajax.strings.request_failed) || 'Request failed. Please check your connection and try again.', 'error');
            }
        });
    }

    // Open Add modals.
    $(document).on('click', '.ttbm-add-guide-btn', function (e) {
        e.preventDefault();
        ttbmOpenCptForm('ttbm_guide_form_html', 0);
    });
    $(document).on('click', '.ttbm-add-ticket-type-btn', function (e) {
        e.preventDefault();
        ttbmOpenCptForm('ttbm_ticket_type_form_html', 0);
    });

    // Open Edit modals (guide + ticket types).
    $(document).on('click', '.ttbm-cpt-edit-btn', function (e) {
        e.preventDefault();
        var cpt = $(this).data('cpt');
        if (cpt !== 'guide' && cpt !== 'ticket_types') {
            return;
        }
        var action = cpt === 'ticket_types' ? 'ttbm_ticket_type_form_html' : 'ttbm_guide_form_html';
        ttbmOpenCptForm(action, $(this).data('id'));
    });

    // Save Guide.
    $(document).on('click', '.ttbm-save-guide', function (e) {
        e.preventDefault();
        var $btn = $(this);
        clearTaxonomyPopupValidation();
        var name = ($('#ttbm-guide-name').val() || '').trim();
        if (!name) {
            showTaxonomyFieldError('ttbm-guide-name', (ttbm_admin_ajax.strings && ttbm_admin_ajax.strings.name_required) || 'Name is required.');
            return;
        }
        ttbmSubmitCptForm($btn, {
            action: 'ttbm_save_guide',
            nonce: ttbm_admin_ajax.nonce,
            post_id: $('#ttbm-cpt-post-id').val() || 0,
            name: name,
            description: $('#ttbm-guide-desc').val() || '',
            image_id: $('#ttbm-location-image-id').val() || 0
        });
    });

    // Save Ticket Type (serializes the whole pricing repeater).
    $(document).on('click', '.ttbm-save-ticket-type', function (e) {
        e.preventDefault();
        var $btn = $(this);
        clearTaxonomyPopupValidation();
        var name = ($('#ttbm-ticket-type-name').val() || '').trim();
        if (!name) {
            showTaxonomyFieldError('ttbm-ticket-type-name', (ttbm_admin_ajax.strings && ttbm_admin_ajax.strings.name_required) || 'Name is required.');
            return;
        }
        // Exclude the hidden repeater template row (its inputs aren't disabled on
        // AJAX-injected markup, unlike the normal metabox), then serialize the rest.
        var $fields = $('#ttbm-ticket-type-form').find('input, select, textarea').filter(function () {
            return $(this).closest('.ttbm_hidden_content').length === 0;
        });
        var formData = $fields.serializeArray();
        formData.push({ name: 'action', value: 'ttbm_save_ticket_type' });
        formData.push({ name: 'nonce', value: ttbm_admin_ajax.nonce });
        formData.push({ name: 'post_id', value: $('#ttbm-cpt-post-id').val() || 0 });
        formData.push({ name: 'name', value: name });
        ttbmSubmitCptForm($btn, $.param(formData));
    });

    // Delete (guide + ticket types).
    $(document).on('click', '.ttbm-cpt-delete-btn', function (e) {
        e.preventDefault();
        var cpt = $(this).data('cpt');
        if (cpt !== 'guide' && cpt !== 'ticket_types') {
            return;
        }
        var $btn = $(this);
        if (!window.confirm('Are you sure you want to move this to trash?')) {
            return;
        }
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: { action: 'ttbm_delete_cpt_item', nonce: ttbm_admin_ajax.nonce, cpt: cpt, post_id: $btn.data('id') },
            success: function (response) {
                if (response.success) {
                    $btn.closest('.ttbm-cpt-card').fadeOut(200, function () { $(this).remove(); });
                } else {
                    window.alert((response.data && response.data.message) || 'Failed to delete.');
                }
            }
        });
    });

    // Live (client-side) search for the new tabs.
    $(document).on('keyup', '#ttbm_guides_search', function () {
        ttbm_function_search_title(($(this).val() || '').toLowerCase(), 'ttbm_search_guide_by_title');
    });
    $(document).on('keyup', '#ttbm_ticket_types_search', function () {
        ttbm_function_search_title(($(this).val() || '').toLowerCase(), 'ttbm_search_ticket_type_by_title');
    });

})(jQuery);


