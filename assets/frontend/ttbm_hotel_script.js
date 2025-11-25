function formatDate(date) {
    let year = date.getFullYear();
    let month = ('0' + (date.getMonth() + 1)).slice(-2);
    let day = ('0' + date.getDate()).slice(-2);
    return `${year}/${month}/${day}`;
}
// hotel booking form will display 
jQuery(document).ready(function ($) {
    // $('.ttbm_hotel_room_check_availability').click();
    /*let current = $('.ttbm_hotel_room_check_availability').closest('.ttbm_hotel_item');

    // let hotel_id = current.find('[name="ttbm_tour_hotel_list"]').val();
    let today = new Date();
    let tomorrow = new Date();
    tomorrow.setDate(today.getDate() + 1);

    let hotel_id = $("#ttbm_booking_hotel_id").val();
    hotel_id = hotel_id ? hotel_id.trim() : '';
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
    }*/
});

(function ($) {

    $(document).on('click', '.ttbm_hotel_room_check_availability', function () {
        let current = $(this).closest('.ttbm_hotel_item');
        
        // let hotel_id = current.find('[name="ttbm_tour_hotel_list"]').val();
        let hotel_id = $("#ttbm_booking_hotel_id").val();
        hotel_id = hotel_id ? hotel_id.trim() : '';
        let date_range = $('[name="ttbm_hotel_date_range"]').val();
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

    $(document).on('click', '.ttbm_multiple_hotel_book_now', function (e) {
        e.preventDefault();

        let hotelCard = $(this).closest('.ttbm_hotel_lists_card');

        let hotel_id = hotelCard.attr('id');
        hotel_id = hotel_id ? hotel_id.trim() : '';

        let date_range = hotelCard.attr('data-date-range');

        let roomDataInfo = {};
        let total_price = 0;

        let parentTable = $(this).closest('.ttbm_hotel_lists_content');


        hotelCard.find('.ttbm_hotel_room_incDec').each(function () {
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

    $(document).on('click', '.ttbm_hotel_book_now', function (e) {
        e.preventDefault();
        let hotel_id = $("#ttbm_booking_hotel_id").val();
        hotel_id = hotel_id ? hotel_id.trim() : '';
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


    $(document).on("click", ".ttbm_list_view", function(){
        $(".ttbm_hotel_lists_wrapper")
            .removeClass("grid-view")
            .addClass("list-view");
    });

    $(document).on("click", ".ttbm_grid_view ", function(){
        $(".ttbm_hotel_lists_wrapper")
            .removeClass("list-view")
            .addClass("grid-view");
    });

    let ttbmMinRange = $("#ttbm_min_range");
    let ttbmMaxRange = $("#ttbm_max_range");
    let ttbmMinPrice = $("#ttbm_min_price");
    let ttbmMaxPrice = $("#ttbm_max_price");
    let ttbmMinGap = 100; // minimum difference allowed

    function updateSlider() {
        let minVal = parseInt(ttbmMinRange.val());
        let maxVal = parseInt(ttbmMaxRange.val());

        if (maxVal - minVal <= ttbmMinGap ) {
            if ($(this).attr("id") === "ttbm_min_range") {
                ttbmMinRange.val(maxVal - ttbmMinGap);
                minVal = maxVal - ttbmMinGap;
            } else {
                ttbmMaxRange.val(minVal + ttbmMinGap);
                maxVal = minVal + ttbmMinGap;
            }
        }

        ttbmMinPrice.text(minVal);
        ttbmMaxPrice.text(maxVal);

        // Update track fill
        let percent1 = (minVal / ttbmMinRange.attr("max")) * 100;
        let percent2 = (maxVal / ttbmMaxRange.attr("max")) * 100;
        $(".ttbm_hotel_slider_track::before").css({
            left: percent1 + "%",
            right: (100 - percent2) + "%"
        });
    }

    ttbmMinRange.on("input", updateSlider);
    ttbmMaxRange.on("input", updateSlider);

    updateSlider();

    function filterHotels() {
        let minPrice = parseInt($('#ttbm_min_range').val());
        let maxPrice = parseInt($('#ttbm_max_range').val());

        let selectedLocations = [];
        $('#ttbm_hotelLocationList input:checked').each(function () {
            selectedLocations.push($(this).next('span').text().trim());
        });

        let selectedActivities = [];
        $('#ttbm_hotelActivityList input:checked').each(function () {
            selectedActivities.push($(this).attr('data-checked'));
        });

        let selectedFeatures = [];
        $('#ttbm_hotelFeatureList input:checked').each(function () {
            selectedFeatures.push($(this).attr('data-checked'));
        });

        let $matchedHotels = $();

        $('.ttbm_hotel_lists_card').each(function () {
            let $card = $(this);
            let price = parseInt($card.data('hotel-price'));
            let location = $card.data('hotel-location');
            let features = ($card.data('hotel-feature') + '').split(',');
            let activities = ($card.data('hotel-activity') + '').split(',');

            let show = true;

            if (price < minPrice || price > maxPrice) show = false;
            if (selectedLocations.length > 0 && !selectedLocations.includes(location)) show = false;
            if (selectedActivities.length > 0 && !selectedActivities.some(val => activities.includes(val))) show = false;
            if (selectedFeatures.length > 0 && !selectedFeatures.some(val => features.includes(val))) show = false;

            if ( show ) {
                $matchedHotels = $matchedHotels.add( $card );
            }

            $card.hide();
        });

        let itemsToShow = $("#ttbm_number_of_show").val();
        $matchedHotels.slice(0, itemsToShow).fadeIn();

        // Toggle Load More button
        if ($matchedHotels.length > itemsToShow) {
            $('#ttbm_loadMoreHotels').show();
        } else {
            $('#ttbm_loadMoreHotels').hide();
        }

        // Save matched hotels for "Load More" to use
        window.ttbm_totalHotelItems = $matchedHotels;
        window.ttbm_itemsToShow = itemsToShow;
    }


    $('#ttbm_min_range, #ttbm_max_range').on('input change', function() {
        $('#ttbm_min_price').text($('#ttbm_min_range').val());
        $('#ttbm_max_price').text($('#ttbm_max_range').val());
        filterHotels();
    });

    $('#ttbm_hotelLocationList input, #ttbm_hotelActivityList input, #ttbm_hotelFeatureList input').on('change', function() {
        filterHotels();
    });
    // Initial call to show correct hotels on page load
    filterHotels();

    let ttbm_itemsToShow = parseInt($("#ttbm_number_of_show").val(), 10);
    let itemsIncrement = ttbm_itemsToShow;
    let ttbmHotelCards = $(".ttbm_hotel_lists_card");
    let ttbm_totalHotelItems = ttbmHotelCards.length;

    ttbmHotelCards.hide();
    ttbmHotelCards.slice(0, ttbm_itemsToShow).show();
    $(document).on( 'click' ,"#ttbm_loadMoreHotels", function () {
        ttbm_itemsToShow += itemsIncrement;
        ttbmHotelCards.slice(0, ttbm_itemsToShow).fadeIn();
        if (ttbm_itemsToShow >= ttbm_totalHotelItems) {
            $(this).hide();
        }
    });

    if (ttbm_itemsToShow >= ttbm_totalHotelItems) {
        $("#ttbm_loadMoreHotels").hide();
    }


    let startDate, endDate;

    $('#ttbm_date_range').daterangepicker({
        autoApply: true,
        minDate: moment(), // 🚫 disables previous dates
        locale: {
            // format: 'YYYY-MM-DD',
            format: 'MMM D, YYYY',
            separator: ' - '
        }
    });

    let $input = $("#ttbm_location_input");
    let $dropdown = $(".ttbm_location_dropdown");
    $input.on("focus", function(){
        $dropdown.show();
    });
    $(document).on("click", function(e){
        if (!$(e.target).closest(".ttbm_location_wrapper").length) {
            $dropdown.hide();
        }
    });
    $dropdown.on("click", "li", function(){
        let value = $(this).text();
        $input.val(value);
        $dropdown.hide();
    });

    $(document).on('click', '.ttbm_see_available_hotel', function (e) {

        $('.ttbm_hotel_inline_booking').fadeOut();
        $('.ttbm_see_available_hotel').fadeIn();
        $(this).hide();
        let parent  = $(this).closest('.ttbm_hotel_lists_card');
        parent.find('.ttbm_hotel_inline_booking').fadeIn();

    });

    $(document).on('click', '.ttbm_cancel_hotel_book', function (e) {

        let parent  = $(this).closest('.ttbm_hotel_lists_card');
        parent.find('.ttbm_see_available_hotel').fadeIn();
        parent.find('.ttbm_hotel_inline_booking').fadeOut();

    });

}(jQuery));

jQuery(window).on('load', function () {
    jQuery(function ($) {
        let current = $('.ttbm_hotel_item').first();
        let hotel_id = $("#ttbm_booking_hotel_id").val();
        hotel_id = hotel_id ? hotel_id.trim() : '';
        let date_range = $('[name="ttbm_hotel_date_range"]').val();
        if ($('[name="ttbm_hotel_date_range"]').length > 1) {
            date_range = $('.particular_date_area')
                .find('[name="ttbm_hotel_date_range"]')
                .val();
        }
        let target = current.find('.ttbm_booking_panel');
        let target_form = target.find('.mp_tour_ticket_form');
        if (date_range) {
            $('.ttbm_hotel_area')
                .find('.ttbm_booking_panel')
                .slideUp('fast');

            $.ajax({
                type: 'POST',
                url: ttbm_ajax_url,
                data: {
                    action: "ttbm_get_hotel_room_list",
                    hotel_id: hotel_id,
                    date_range: date_range,
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
        }

    });
});
