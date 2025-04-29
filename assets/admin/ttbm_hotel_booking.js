(function ($) {

    $(document).on("click", ".ttbm_total_booking_filter_btn", function (e) {
        e.preventDefault();
        let hotel_id = $("#all_hotel_list").val().trim();
        let date = $("#ttbm_booking_date_filter").val().trim();
        let display_limit = 20;

        $.ajax({
            type: 'POST',
            url: mp_ajax_url,
            data: {
                "action": "get_ttbm_hotel_booking_all_lists",
                "date": date,
                "hotel_id": hotel_id,
                "display_limit": display_limit,
                "nonce": ttbm_admin_ajax.nonce,
            }
            , success: function ( response) {
                console.log( response.data.data );
                if( response ){
                    $("#ttbm_total_booking_tbody").html( response.data.data );
                }
            }, error: function (response) {
                console.log( response );
            }
        });


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
        dateFormat: 'yy-mm-dd', // তারিখ ফরম্যাট
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        minDate: 0 // আজকের আগে কোনো তারিখ সিলেক্ট করা যাবে না
    });

})(jQuery);