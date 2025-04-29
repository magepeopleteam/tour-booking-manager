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


    $('.ttbm_hotel_tab_item').on('click', function() {
        var tab_id = $(this).data('tab');

        $('.ttbm_hotel_tab_item').removeClass('active');
        $(this).addClass('active');

        $('.ttbm_hotel_tab_content').removeClass('active');
        $('#' + tab_id).addClass('active');
    });

})(jQuery);