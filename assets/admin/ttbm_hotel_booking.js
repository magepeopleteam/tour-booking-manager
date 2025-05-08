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
                url: mp_ajax_url,
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
            let setUrl = mp_ajax_url;
            let display_limit = 2;

            let hotelIds = [];

            $('.ttbm_hotel_list_view tr[id^="hotel_"]').each(function() {
                let rawId = $(this).attr('id');
                let cleanId = rawId.replace('hotel_', '');
                hotelIds.push(cleanId);
            });
            hotelIds = hotelIds.join(',');
            let type = 'POST';
            jQuery.ajax({
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
                url: mp_ajax_url,
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
        jQuery.ajax({
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
        let search_term = jQuery(this).val();
        let nonce = ttbm_admin_ajax.nonce;
        let setUrl = mp_ajax_url;
        let type = 'POST';
        if( search_term.length > 0 ) {
            jQuery("#productTitleWrapper").show();
            jQuery("#productDropDownMenu").show();
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

        let action = '';
        if( targetId === 'ttbm_trvel_lists_location' ){
            action = 'ttbm_get_locations_html';
        }else if( targetId === 'ttbm_trvel_lists_organiser' ){
            action = 'ttbm_get_organiser_html_data';
        }else if( targetId === 'ttbm_trvel_lists_features' ){
            action = 'ttbm_get_feature_html_data';
        }else if( targetId === 'ttbm_trvel_lists_tag' ){
            action = 'ttbm_get_tag_html_data';
        }else if( targetId === 'ttbm_trvel_lists_activities' ){
            action = 'ttbm_get_activities_html_data';
        }else if( targetId === 'ttbm_trvel_lists_places' ){
            action = 'ttbm_get_places_html_data';
        }

        if( targetId === 'ttbm_trvel_lists_tour' ){
            $(".ttbm-tour-list_holder").show();
        }else{
            $(".ttbm-tour-list_holder").hide();
        }

        $('.ttbm_trvel_lists_tabs button').removeClass('active');
        $('.ttbm_trvel_lists_content').removeClass('active');

        $(this).addClass('active');
        $('#' + targetId).addClass('active');

        let nonce = ttbm_admin_ajax.nonce;

        $.ajax({
            url: mp_ajax_url,
            nonce: nonce,
            type: 'POST',
            data: {
                action: action,
            },
            success: function (response) {
                if( targetId === 'ttbm_trvel_lists_location' ){
                    if (response.success) {
                        $('#ttbm_travel_list_location_shows').html(response.data.html);
                    } else {
                        $('#ttbm_travel_list_location_shows').html('<p>No locations found.</p>');
                    }
                }else if(  targetId === 'ttbm_trvel_lists_organiser'){
                    if (response.success) {
                        $('#ttbm_travel_list_organiser_content').html(response.data.html);
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
                        $('#ttbm_travel_list_places_content').html(response.data.html);
                    }
                }
            }
        });


    });


    let ttbm_image_frame;

    // Hide popup
    $(document).on('click',  '#ttbm-close-popup',function() {
        $('#ttbm-location-popup').fadeOut();
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
        }

        return taxonomy_type;
    }
    function get_tab_action_name_by_tab( tab_action_type ){
        let tab_type = '';
        if( tab_action_type === 'Trip Location' ){
            tab_type = 'Add New Locations';
        }else if( tab_action_type === 'Trip Organiser' ){
            tab_type = 'Add New Organiser';
        }else if( tab_action_type === 'Features' ){
            tab_type = 'Add New Feature';
        }else if( tab_action_type === 'Tags' ){
            tab_type = 'Add New Tag';
        }else if( tab_action_type === 'Activities' ){
            tab_type = 'Add New Activities';
        }

        return tab_type;
    }

    jQuery(document).on('click', '.ttbm-save-location', function (e) {
        e.preventDefault();

        let tab_type = $(this).siblings('.ttbm_get_clicked_tab_name').val().trim();
        let taxonomy_type = get_add_taxonomy_type( tab_type );


        let action_type = $(this).text().trim();
        let action = '';
        let term_id = '';
        if( action_type === 'Save' ){
            action = 'ttbm_add_new_location_term';
        }else{
            action = 'ttbm_add_new_location_term';
            term_id = $(this).parent().attr('id');
        }

        const name = jQuery('#ttbm-location-name').val().trim();
        const slug = jQuery('#ttbm-location-slug').val().trim();
        const desc = jQuery('#ttbm-location-desc').val().trim();

        const parent = jQuery('#ttbm-location-parent').val();

        let address = '';
        let country = '';
        let imageId = '';

        if( tab_type === 'Add New Locations' ){
            address = jQuery('#ttbm-location-address').val().trim();
            country = jQuery('#ttbm-location-country').val();
            imageId = jQuery('#ttbm-location-image-id').val();
        }


        if (!name) {
            alert('Name is required.');
            return;
        }

        // Optional: Show loading indicator
        jQuery('.ttbm-save-location').text('Saving...').prop('disabled', true);

        jQuery.post(mp_ajax_url, {
            action: action,
            _wpnonce: ttbm_admin_ajax.nonce,
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

        },
            function (response) {
            jQuery('.ttbm-save-location').text('Save').prop('disabled', false);
            if (response.success) {
                let img_url = response.data.img_url;

                let new_location_added = `<div class="ttbm-location-card">
                                        <div class="ttbm-card-left">
                                            <img src="${img_url}" alt="${name}" width="70" height="70">
                                        </div>
                                        <div class="ttbm-card-right">
                                            <h3 class="ttbm-title">${name}</h3>
                                            <p class="ttbm-description">${address}</p>
                                            <span class="ttbm-edit-btn ttbm_edit_trip_location">Edit</span>
                                        </div>
                                    </div>`;
                if( term_id === '' ){
                    $(".ttbm-locations-list").prepend( new_location_added );
                }
                $('#ttbm-location-popup').fadeOut();
            } else {
                alert(response.data?.message || 'Something went wrong. Please try again.');
            }
        }).fail(function () {
            jQuery('.ttbm-save-location').text('Save').prop('disabled', false);
            alert('AJAX request failed. Please check your connection.');
        });
    });
    
    $(document).on( 'click', '#ttbm-add-new-location-btn', function () {

        let term_id = '';
        let tab_type = $(this).text().trim();

        $.ajax({
            url: mp_ajax_url,
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

    $(document).on( 'click', '.ttbm_edit_trip_location', function () {

        let term_id = $(this).parent().parent().attr('ttbm-data-location-id');
        let tab_action_type = $('.ttbm_trvel_lists_tabs button.active').text().trim();
        let tab_type = get_tab_action_name_by_tab( tab_action_type );

        console.log( term_id );

        $.ajax({
            url: mp_ajax_url,
            type: 'POST',
            data: {
                term_id: term_id,
                tab_type: tab_type,
                nonce:  ttbm_admin_ajax.nonce,
                action: 'ttbm_edit_locations_ajax_html',
            },
            success: function (response) {
                if (response.success) {
                    console.log('HTML returned as object:', response.data); // ✅ object with HTML
                    // $('#ttbm_travel_list_location_shows').html(response.data.html); // render HTML
                    $('#ttbm_travel_list_popup').html(response.data.edit_popup );
                }
            }
        });
    })


})(jQuery);