function ttbm_price_calculation(parent) {
    let total = mpTourTotalPrice(parent);
    let qty = mp_tour_ticket_qty(parent);
    parent.find(' #ttbm_total_price').val(total);
    parent.find(' .tour_price').html(ttbm_price_format(total));
    parent.find('.tour_qty').html(qty);
    // Partial Payment Job
    ttbm_partial_payment_job(parent, total);
}
function mpTourTotalPrice(parent) {
    let currentTarget = parent.find('.formControl[data-price]');
    let total = 0;
    let totalQty = 0;
    currentTarget.each(function () {
        let unitPrice = parseFloat(jQuery(this).attr('data-price'));
        let qty = parseInt(jQuery(this).val());
        if (qty > 0 && jQuery(this).data('unit-qty') > 0 && jQuery(this).data('group-ticket-option') == 'on') {
            //qty = parseInt(qty*jQuery(this).data('unit-qty'));
        }
        totalQty += qty;
        let hotel_parent = jQuery(this).closest('.ttbm_hotel_item');
        let hotel_id = hotel_parent.find('[name="ttbm_hotel_id"').val();
        if (hotel_id > 0) {
            if (jQuery(this).closest('.mp_tour_ticket_type').length > 0) {
                let date_count = parseInt(hotel_parent.find('[name="ttbm_hotel_num_of_day"').val());
                qty *= date_count;
            }
        }
        total = total + (unitPrice * qty > 0 ? unitPrice * qty : 0);
        if (parent.find('.ttbm_tier_price_chart').length > 0) {
            parent.find('[data-discount]').each(function () {
                let start_qty = parseInt(jQuery(this).data('start-qty'));
                let end_qty = parseInt(jQuery(this).data('end-qty'));
                if (start_qty <= qty && end_qty >= qty) {
                    let discount = parseInt(jQuery(this).data('discount'));
                    total = total - total * discount / 100;
                }
            });
        }
    });
    if (totalQty > 0) {
        currentTarget.removeClass('error');
    }
    return total;
}
function mpTourTicketQtyValidation(target, value) {
    let extraParents = target.closest('.mp_tour_ticket_extra');
    if (extraParents.length > 0) {
        if (mp_tour_ticket_qty(target.closest('.ttbm_registration_area')) > 0) {
            extraParents.find('.formControl[data-price]').each(function () {
                jQuery(this).removeAttr('disabled');
            }).promise().done(function () {
                mpTourTicketQty(target, value);
            });
        } else {
            extraParents.find('.formControl[data-price]').each(function () {
                jQuery(this).attr("disabled", "disabled");
            }).promise().done(function () {
                jQuery('.ttbm_registration_area .mp_tour_ticket_type tbody tr:first-child').find('.formControl[data-price]').trigger('focus');
            });
        }
    } else {
        jQuery('.mp_tour_ticket_extra').find('.formControl[data-price]').each(function () {
            jQuery(this).removeAttr("disabled", "disabled");
        }).promise().done(function () {
            mpTourTicketQty(target, value);
        });
    }
}
function mpTourTicketQty(target, value) {
    let min = parseInt(target.attr('min'));
    let max = parseInt(target.attr('max'));
    target.parents('.qtyIncDec').find('.incQty , .decQty').removeClass('mage_disabled');
    if (value < min || isNaN(value) || value === 0) {
        value = min;
        target.parents('.qtyIncDec').find('.decQty').addClass('mage_disabled');
    }
    if (value > max) {
        value = max;
        target.parents('.qtyIncDec').find('.incQty').addClass('mage_disabled');
    }
    target.val(value);
    let parent = target.closest('.ttbm_registration_area');
    ttbm_price_calculation(parent);
}
function mp_tour_ticket_qty(parent) {
    let totalQty = 0;
    let single_attendee = parent.find('[name="ttbm_single_attendee_display"]').val();
    parent.find('.mp_tour_ticket_type').find('.formControl[data-price]').each(function () {
        let qty = parseInt(jQuery(this).val());
        qty = qty > 0 ? qty : 0;
        totalQty += qty;
        if (single_attendee === 'off') {
            ttbm_multi_attendee_form(jQuery(this).closest('tr'), qty);
        }
    });
    totalQty = totalQty > 0 ? totalQty : 0;
    if (single_attendee === 'on') {
        ttbm_single_attendee_form(parent, totalQty);
    }
    if (totalQty > 0) {
        parent.find('.ttbm_extra_service_area').slideDown(250);
    } else {
        parent.find('.ttbm_extra_service_area').slideUp(250);
    }
    return totalQty;
}
function ttbm_multi_attendee_form(parentTr, qty) {
    let target_tr = parentTr.next('tr');
    let target_form = target_tr.find('.ttbm_attendee_form_item');
    let formLength = target_form.length;
    if (qty > 0) {
        if (formLength !== qty) {
            if (formLength > qty) {
                for (let i = formLength; i > qty; i--) {
                    target_tr.find('.ttbm_attendee_form_item:last-child').slideUp(250).remove();
                }
            } else {
                let name = target_tr.find('[name="ticket_name[]"]').val();
                let form_copy = jQuery('[data-form-type]').html();
                for (let i = formLength; i < qty; i++) {
                    target_tr.find('td').append(form_copy).find('.ttbm_attendee_form_item:last-child').slideDown(250).promise().done(function () {
                        let current_item = target_tr.find('td').find('.ttbm_attendee_form_item:last-child');
                        current_item.find('.form_title_text').html(name);
                        current_item.find('.ttbm_attendee_title').html(qty);
                        if (target_tr.find('[name="ticket_qroup_qty[]"]').length > 0) {
                            let group_qty = parseInt(target_tr.find('[name="ticket_qroup_qty[]"]').val());
                            if (current_item.find('.ttbm_guest_item').length !== group_qty) {
                                for (let j = current_item.find('.ttbm_guest_item').length; j < group_qty; j++) {
                                    let clone_form = jQuery('[data-form-type]').find('.ttbm_guest_item').clone();
                                    current_item.append(clone_form);
                                }
                            }
                            let item_count = 1;
                            current_item.find('.ttbm_group_title').each(function () {
                                jQuery(this).html(item_count);
                                item_count++;
                            });
                        }
                        target_tr.find(".date_type").removeClass('hasDatepicker').attr('id', '').removeData('datepicker').unbind().promise().done(function () {
                            ttbm_load_date_picker(target_tr);
                            // Initialize autocomplete for attendee fields
                            if (typeof ttbm_init_attendee_autocomplete === 'function') {
                                ttbm_init_attendee_autocomplete(target_tr);
                            }
                        });
                    });
                }
            }
        }
    } else {
        target_form.slideUp(250).remove();
    }
}
function ttbm_single_attendee_form(parent, totalQty) {
    let target_form = parent.find('.ttbm_attendee_form_area').find('.ttbm_attendee_form_item');
    if (totalQty > 0) {
        if (target_form.length === 0) {
            let form_copy = parent.find('[data-form-type]').html();
            parent.find('.ttbm_attendee_form_area').append(form_copy).promise().done(function () {
                parent.find('.ttbm_attendee_form_area').find(".date_type").removeClass('hasDatepicker').attr('id', '').removeData('datepicker').unbind().promise().done(function () {
                    ttbm_load_date_picker(parent.find('.ttbm_attendee_form_area'));
                    // Initialize autocomplete for attendee fields
                    if (typeof ttbm_init_attendee_autocomplete === 'function') {
                        ttbm_init_attendee_autocomplete(parent.find('.ttbm_attendee_form_area'));
                    }
                });
            });
        }
    } else {
        target_form.slideUp(250).remove();
    }
}
function ttbm_partial_payment_job(parent, total) {
    let payment = 0;
    let deposit_type = parent.find('[name="payment_plan"]').val();
    parent.find(' .tour_price').attr('data-total-price', total);
    if (!deposit_type) {
        return;
    }
    if (deposit_type === 'percent') {
        let percent = parseFloat(parent.find('[name="payment_plan"]').data('percent'));
        payment = total * percent / 100;
        parent.find('.payment_amount').html(ttbm_price_format(payment));
    }
    if (deposit_type === 'minimum_amount') {
        parent.find('.mep-pp-payment-terms .mep-pp-user-amountinput').attr('max', total);
    }
}
(function ($) {
    "use strict";
    $(document).ready(function () {
        $('body').find('.ttbm_registration_area').each(function () {
            ttbm_price_calculation($(this));
        });
    });
    $(document).on("change", ".ttbm_registration_area .formControl[data-price]", function (e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            return false;
        }
        let target = $(this);
        let value = parseInt(target.val());
        mpTourTicketQtyValidation(target, value);
    });
    $(document).on("click", ".ttbm_book_now", function (e) {
        e.preventDefault();
        if (mp_tour_ticket_qty($(this).closest('.ttbm_registration_area')) > 0) {
            let error_exit = 0;
            let parent = $(this).closest('.mp_tour_ticket_form');
            parent.find('.formControl').each(function () {
                if ($(this).is(':required') && $(this).val() === '') {
                    $(this).closest('.ttbm_form_item').addClass('mage_error');
                    error_exit++;
                } else {
                    $(this).closest('.ttbm_form_item').removeClass('mage_error');
                }
            });
            if (error_exit > 0) {
                return false;
            } else {
                let book_now_area = $(this).closest('.ttbm_book_now_area');
                if (book_now_area.find('[name="ttbm_direct_order_product_id"]').length > 0) {
                    book_now_area.find('.ttbm_add_to_cart').attr('name', '').trigger('click');
                } else {
                    book_now_area.find('.ttbm_add_to_cart').trigger('click');
                }
            }
        } else {
            alert('Please Select Ticket Type');
            let currentTarget = $(this).closest('.ttbm_registration_area').find('.mp_tour_ticket_type .formControl[data-price]');
            currentTarget.addClass('error');
            return false;
        }
    });

    // Enhanced Availability Updates
    setInterval(function () {
        updateTicketAvailability();
    }, 30000);

    function updateTicketAvailability(contextArea) {
        var parent = contextArea && contextArea.length ? contextArea : $('.ttbm_registration_area:has(.ttbm_enhanced_ticket_area):first');
        if (!parent || parent.length === 0) return;

        var ticketArea = parent.find('.ttbm_enhanced_ticket_area').first();
        if (ticketArea.length === 0) return;

        var tourId = parent.find('input[name="ttbm_id"]').first().val() || ticketArea.find('.ttbm_last_updated').data('tour-id');
        var selectedTime = parent.find('[name="ttbm_select_time"]').first().val();
        var selectedDate = parent.find('input[name="ttbm_date"]').first().val();
        var fallbackDate = ticketArea.find('.ttbm_last_updated').data('tour-date');
        var tourDate = selectedTime || selectedDate || fallbackDate;

        if (!tourId || !tourDate) return;

        $.ajax({
            url: (typeof ttbm_price_calc_vars !== 'undefined' && ttbm_price_calc_vars.ajax_url) || ttbm_ajax_url || ajaxurl,
            type: 'POST',
            data: {
                action: 'get_ticket_availability',
                nonce: (typeof ttbm_price_calc_vars !== 'undefined' && ttbm_price_calc_vars.nonce) || (typeof ttbm_ajax !== 'undefined' && ttbm_ajax.nonce) || $('#ttbm_nonce_field').val(),
                tour_id: tourId,
                tour_date: tourDate
            },
            success: function (response) {
                if (response.success && response.data.availability) {
                    updateTicketDisplay(response.data.availability, ticketArea);
                    updateLastRefreshTime(ticketArea);
                }
            },
            error: function () {
                console.log('Failed to update availability');
            }
        });
    }

    function updateTicketDisplay(availability, ticketArea) {
        var scope = ticketArea && ticketArea.length ? ticketArea : $(document);
        $.each(availability, function (ticketName, info) {
            var row = scope.find('[data-ticket-name="' + ticketName + '"]');
            if (row.length === 0) return;

            // Update available count
            var availableSpan = row.find('.ttbm_available_number');
            if (availableSpan.length > 0) {
                availableSpan.text(info.available_qty);
            }

            // Update available label
            var availableLabel = row.find('.ttbm_available_label');
            if (availableLabel.length > 0) {
                var labelText = info.available_qty === 1 ? 'ticket left' : 'tickets left';
                availableLabel.text(labelText);
            }

            // Update progress bar
            var progressBar = row.find('.ttbm_progress_fill');
            if (progressBar.length > 0) {
                progressBar.css('width', info.percentage_sold + '%');
                progressBar.removeClass('ttbm_progress_in_stock ttbm_progress_sold_out');
                progressBar.addClass('ttbm_progress_' + info.stock_status);
            }

            // Update capacity text
            var capacityText = row.find('.ttbm_capacity_text');
            if (capacityText.length > 0) {
                capacityText.text(info.sold_qty + ' of ' + info.total_capacity + ' sold');
            }

            // Update row classes
            row.removeClass('ttbm_stock_in_stock ttbm_stock_sold_out ttbm_sold_out');
            row.addClass('ttbm_stock_' + info.stock_status);

            if (info.available_qty <= 0) {
                row.addClass('ttbm_sold_out');
                // Show sold out state
                var stockInfo = row.find('.ttbm_stock_info');
                if (stockInfo.find('.ttbm_stock_status.sold_out').length === 0) {
                    stockInfo.html('<span class="ttbm_stock_status sold_out"><i class="fas fa-times-circle"></i> Sold Out</span>');
                }
                // Disable quantity input
                row.find('.formControl[data-price]').prop('disabled', true).val(0);
            } else {
                // Remove urgency message if exists
                row.find('.ttbm_urgency_message').remove();
                row.find('.formControl[data-price]').prop('disabled', false);
            }

            // Update quantity input max value
            var quantityInput = row.find('.formControl[data-price]');
            if (quantityInput.length > 0) {
                var currentValue = parseInt(quantityInput.val()) || 0;
                quantityInput.attr('max', info.available_qty);

                // If current value exceeds available, reset to available
                if (currentValue > info.available_qty) {
                    quantityInput.val(info.available_qty);
                    quantityInput.trigger('change');
                }
            }
        });

        // Update total availability
        var totalAvailable = 0;
        $.each(availability, function (ticketName, info) {
            totalAvailable += parseInt(info.available_qty) || 0;
        });
        scope.find('#ttbm_total_available').text(totalAvailable);

        var detailsPage = scope.closest('.ttbm_details_page');
        if (detailsPage.length > 0) {
            detailsPage.find('.ttbm_available_seat_area .ttbm_available_seat').first().text(totalAvailable);
        } else {
            $('.ttbm_available_seat_area .ttbm_available_seat').first().text(totalAvailable);
        }
    }

    function updateLastRefreshTime(ticketArea) {
        var now = new Date();
        var timeString = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        if (ticketArea && ticketArea.length) {
            ticketArea.find('.ttbm_last_updated').html('<i class="far fa-clock"></i> Updated at ' + timeString);
        } else {
            $('.ttbm_last_updated').html('<i class="far fa-clock"></i> Updated at ' + timeString);
        }
    }

}(jQuery));
