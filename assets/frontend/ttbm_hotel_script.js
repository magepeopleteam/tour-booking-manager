function formatDate(date) {
    let year = date.getFullYear();
    let month = ('0' + (date.getMonth() + 1)).slice(-2);
    let day = ('0' + date.getDate()).slice(-2);
    return `${year}/${month}/${day}`;
}
// hotel booking form will display 
jQuery(document).ready(function ($) {
    // $('.ttbm_hotel_room_check_availability').click();
    let current = $('.ttbm_hotel_room_check_availability').closest('.ttbm_hotel_item');
        
    // let hotel_id = current.find('[name="ttbm_tour_hotel_list"]').val();
    let today = new Date();
    let tomorrow = new Date();
    tomorrow.setDate(today.getDate() + 1);

    let hotel_id = $("#ttbm_booking_hotel_id").val().trim();
    let date_range = `${formatDate(today)}    -    ${formatDate(tomorrow)}`;
    $('[name="ttbm_hotel_date_range"]').val(date_range);
    if ($('[name="ttbm_hotel_date_range"]').length > 1) {
        date_range = $(this).closest('.particular_date_area').find('[name="ttbm_hotel_date_range"]').val();
    }
    let target = current.find('.ttbm_booking_panel');
    let target_form = target.find('.mp_tour_ticket_form');
    if (date_range) {
        $('.ttbm_hotel_area').find('.ttbm_booking_panel').slideUp('fast');
        jQuery.ajax({
            type: 'POST',
            url: ttbm_ajax_url,
            data: {
                "action": "ttbm_get_hotel_room_list",
                "hotel_id": hotel_id,
                "date_range": date_range, nonce: ttbm_ajax.nonce
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
    }
});

(function ($) {

    $(document).on('click', '.ttbm_hotel_room_check_availability', function () {
        let current = $(this).closest('.ttbm_hotel_item');
        
        // let hotel_id = current.find('[name="ttbm_tour_hotel_list"]').val();
        let hotel_id = $("#ttbm_booking_hotel_id").val().trim();
        let date_range = $('[name="ttbm_hotel_date_range"]').val();
        if ($('[name="ttbm_hotel_date_range"]').length > 1) {
            date_range = $(this).closest('.particular_date_area').find('[name="ttbm_hotel_date_range"]').val();
        }
        let target = current.find('.ttbm_booking_panel');
        let target_form = target.find('.mp_tour_ticket_form');
        console.log(date_range);
        if (date_range) {
            $('.ttbm_hotel_area').find('.ttbm_booking_panel').slideUp('fast');
            jQuery.ajax({
                type: 'POST',
                url: ttbm_ajax_url,
                data: {
                    "action": "ttbm_get_hotel_room_list",
                    "hotel_id": hotel_id,
                    "date_range": date_range, nonce: ttbm_ajax.nonce
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
        }
    });
    $(document).on('click', '.ttbm_hotel_book_now', function (e) {
        e.preventDefault();
        let hotel_id = $("#ttbm_booking_hotel_id").val().trim();
        let date_range = $('[name="ttbm_hotel_date_range"]').val();
        if ($('[name="ttbm_hotel_date_range"]').length > 1) {
            date_range = $(this).closest('.particular_date_area').find('[name="ttbm_hotel_date_range"]').val();
        }
        let roomDataInfo = {};
        let total_price = 0;
        $('.ttbm_hotel_room_incDec').each(function () {
            const roomDiv = $(this).find('.qtyIncDec');
            let roomName = roomDiv.data('ticket-type-name');
            if (!roomName) return;
            // Remove all spaces from room name
            roomName = roomName.replace(/\s+/g, '');
            const quantity = parseInt(roomDiv.find('.inputIncDec').val()) || 0;
            const price = parseFloat(roomDiv.find('.inputIncDec').data('price')) || 0;
            // Only include if quantity is greater than 0
            if (quantity > 0) {
                roomDataInfo[roomName] = {
                    quantity: quantity,
                    price: price
                };
                total_price += quantity * price;
            }
        });
        roomDataInfo = JSON.stringify(roomDataInfo);
        jQuery.ajax({
            type: 'POST',
            url: ttbm_ajax_url,
            data: {
                "action": "ttbm_hotel_room_booking",
                "hotel_id": hotel_id,
                "date_range": date_range,
                "room_data_info": roomDataInfo,
                "price": total_price, nonce: ttbm_ajax.nonce
            },
            success: function (data) {
                window.location.href = ttbm_site_url + '/index.php/checkout/';
            }
        });
    });
   
    $(document).on('click','.ttbm-hotel-share',function(e){
        e.preventDefault();
        e.stopPropagation();
        $('#ttbm-share-tooltip').toggle();
        
    });
}(jQuery));
