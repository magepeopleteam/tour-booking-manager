(function ($) {
$(document).on('click', '.ttbm_hotel_check_availability', function () {
    let current = $(this).closest('.ttbm_hotel_item');
    let tour_id = 103;
    let hotel_id = 106;
    let date_range = $('[name="ttbm_hotel_date_range"]').val();
    if ($('[name="ttbm_hotel_date_range"]').length > 1) {
        date_range = $(this).closest('.particular_date_area').find('[name="ttbm_hotel_date_range"]').val();
    }

    let target = current.find('.ttbm_booking_panel');
    let target_form = target.find('.mp_tour_ticket_form');
    if (date_range) {
        // if (target_form.length < 1) {
            $('.ttbm_hotel_area').find('.ttbm_booking_panel').slideUp('fast');
            jQuery.ajax({
                type: 'POST',
                url: mp_ajax_url,
                data: {
                    "action": "ttbm_get_hotel_room_list",
                    "tour_id": tour_id,
                    "hotel_id": hotel_id,
                    "date_range": date_range
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
        /*} else {
            if (target.is(':visible')) {
                target.slideUp('fast');
            } else {
                $('.ttbm_hotel_area').find('.ttbm_booking_panel').slideUp('fast');
                target.slideDown('fast');
            }
        }*/
    }
});
}(jQuery));