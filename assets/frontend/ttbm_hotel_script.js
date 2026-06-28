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

    function ttbmGetHotelDateRange($context) {
        const $scope = $context && $context.length ? $context : $(document);
        const $input = $scope.find('input.ttbm_hotel_date_input[name="ttbm_hotel_date_range"]').first();
        if (!$input.length) {
            return $scope.find('[name="ttbm_hotel_date_range"]').first().val() || '';
        }
        const checkin = $input.attr('data-checkin');
        const checkout = $input.attr('data-checkout');
        if (checkin && checkout) {
            return String(checkin).replace(/-/g, '/') + '    -    ' + String(checkout).replace(/-/g, '/');
        }
        return $input.val() || '';
    }

    $(document).on('click', '.ttbm_hotel_room_check_availability', function () {
        let current = $(this).closest('.ttbm_hotel_item');
        
        // let hotel_id = current.find('[name="ttbm_tour_hotel_list"]').val();
        let hotel_id = $("#ttbm_booking_hotel_id").val();
        hotel_id = hotel_id ? hotel_id.trim() : '';
        let date_range = ttbmGetHotelDateRange(current);
        if (!date_range && $('[name="ttbm_hotel_date_range"]').length > 1) {
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
                let response = data;
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (err) {
                        response = null;
                    }
                }
                if (response && typeof response === 'object' && response.success === false) {
                    const message = response.data && response.data.message ? response.data.message : 'Unable to complete booking for selected dates.';
                    alert(message);
                    return;
                }
                window.location.href = ttbm_site_url + '/index.php/checkout/';
            },
            error: function (xhr) {
                let message = 'Unable to complete booking for selected dates.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    message = xhr.responseJSON.data.message;
                }
                alert(message);
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
                let response = data;
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (err) {
                        response = null;
                    }
                }
                if (response && typeof response === 'object' && response.success === false) {
                    const message = response.data && response.data.message ? response.data.message : 'Unable to complete booking for selected dates.';
                    alert(message);
                    return;
                }
                window.location.href = ttbm_site_url + '/index.php/checkout/';
            },
            error: function (xhr) {
                let message = 'Unable to complete booking for selected dates.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    message = xhr.responseJSON.data.message;
                }
                alert(message);
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

    function updateSlider(evt) {
        let minVal = parseInt(ttbmMinRange.val(), 10);
        let maxVal = parseInt(ttbmMaxRange.val(), 10);
        const rangeMin = parseInt(ttbmMinRange.attr("min"), 10) || 0;
        const rangeMax = parseInt(ttbmMinRange.attr("max"), 10) || 5000;
        const sourceId = evt && evt.target ? evt.target.id : '';

        if (maxVal - minVal <= ttbmMinGap) {
            if (sourceId === "ttbm_min_range") {
                minVal = maxVal - ttbmMinGap;
                ttbmMinRange.val(minVal);
            } else if (sourceId === "ttbm_max_range") {
                maxVal = minVal + ttbmMinGap;
                ttbmMaxRange.val(maxVal);
            }
        }

        ttbmMinPrice.text(minVal);
        ttbmMaxPrice.text(maxVal);

        const trackEl = $(".ttbm_hotel_slider_track").get(0);
        const thumbSize = 18;
        const trackWidth = trackEl ? trackEl.offsetWidth : 0;
        const rangeSpan = rangeMax - rangeMin;

        if (trackWidth > 0 && rangeSpan > 0) {
            const thumbOffset = (thumbSize / trackWidth) * 100;
            const usable = 100 - thumbOffset;
            const minPercent = ((minVal - rangeMin) / rangeSpan) * usable + (thumbOffset / 2);
            const maxPercent = ((maxVal - rangeMin) / rangeSpan) * usable + (thumbOffset / 2);
            $(".ttbm_hotel_slider_track").css({
                '--ttbm-range-left': minPercent + '%',
                '--ttbm-range-right': (100 - maxPercent) + '%'
            });
        } else {
            const percent1 = ((minVal - rangeMin) / rangeSpan) * 100;
            const percent2 = ((maxVal - rangeMin) / rangeSpan) * 100;
            $(".ttbm_hotel_slider_track").css({
                '--ttbm-range-left': percent1 + '%',
                '--ttbm-range-right': (100 - percent2) + '%'
            });
        }
    }

    $('#ttbm_min_range, #ttbm_max_range').on('input change', function(e) {
        updateSlider(e);
        filterHotels();
    });

    function filterHotels() {
        let minPrice = parseInt($('#ttbm_min_range').val(), 10);
        let maxPrice = parseInt($('#ttbm_max_range').val(), 10);

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
            let price = parseInt($card.data('hotel-price'), 10);
            let location = $card.data('hotel-location');
            let features = ($card.data('hotel-feature') + '').split(',');
            let activities = ($card.data('hotel-activity') + '').split(',');

            let show = true;

            if (price < minPrice || price > maxPrice) show = false;
            if (selectedLocations.length > 0 && !selectedLocations.includes(location)) show = false;
            if (selectedActivities.length > 0 && !selectedActivities.some(val => activities.includes(val))) show = false;
            if (selectedFeatures.length > 0 && !selectedFeatures.some(val => features.includes(val))) show = false;

            if (show) {
                $matchedHotels = $matchedHotels.add($card);
            }

            $card.hide();
        });

        let itemsToShow = $("#ttbm_number_of_show").val();
        $matchedHotels.slice(0, itemsToShow).fadeIn();

        if ($matchedHotels.length > itemsToShow) {
            $('#ttbm_loadMoreHotels').show();
        } else {
            $('#ttbm_loadMoreHotels').hide();
        }

        window.ttbm_totalHotelItems = $matchedHotels;
        window.ttbm_itemsToShow = itemsToShow;
    }

    updateSlider();

    $(window).on('resize.ttbmPriceSlider', function () {
        updateSlider();
    });

    $('#ttbm_hotelLocationList input, #ttbm_hotelActivityList input, #ttbm_hotelFeatureList input').on('change', function() {
        filterHotels();
    });
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

    function ttbmInitHotelSearchDateRangePicker() {
        const $input = $('#ttbm_date_range');
        if (!$input.length || $input.data('ttbm-hotel-drp-init') || typeof $.fn.daterangepicker !== 'function') {
            return;
        }
        const separator = ' \u2013 ';
        $input.daterangepicker({
            autoApply: true,
            autoUpdateInput: true,
            minDate: moment().startOf('day'),
            opens: 'left',
            drops: 'down',
            parentEl: 'body',
            locale: {
                format: 'MMM D, YYYY',
                separator: separator
            }
        });
        const drpInstance = $input.data('daterangepicker');
        if (drpInstance && drpInstance.container) {
            drpInstance.container.addClass('ttbm-hotel-daterange');
        }
        $input
            .on('show.daterangepicker', function (ev, picker) {
                picker.container.addClass('ttbm-hotel-daterange');
            })
            .on('apply.daterangepicker', function (ev, picker) {
                const formatted = picker.startDate.format('MMM D, YYYY') + separator + picker.endDate.format('MMM D, YYYY');
                $(this).val(formatted);
            });
        $input.data('ttbm-hotel-drp-init', true);
    }

    ttbmInitHotelSearchDateRangePicker();

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

    $(window).on('load', function () {
        if ($('#ttbm_booking_hotel_id').length < 1) {
            return;
        }
        let current = $('.ttbm_hotel_item').first();
        let hotel_id = $("#ttbm_booking_hotel_id").val();
        hotel_id = hotel_id ? hotel_id.trim() : '';
        let date_range = ttbmGetHotelDateRange(current);
        if (!date_range && $('[name="ttbm_hotel_date_range"]').length > 1) {
            date_range = $('.particular_date_area')
                .find('[name="ttbm_hotel_date_range"]')
                .val();
        }
        let target = current.find('.ttbm_booking_panel');
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

}(jQuery));