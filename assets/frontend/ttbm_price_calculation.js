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
    let parent = target.closest('.ttbm_registration_area');
    let isSharedCapacity = target.closest('.ttbm_ticket_row').attr('data-shared-capacity-enabled') === '1';
    value = ttbmConstrainSharedCapacity(target, value, parent);
    max = parseInt(target.attr('max'));
    if (isSharedCapacity && !isNaN(max) && max < min) {
        min = 0;
    }
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
    ttbm_price_calculation(parent);
    ttbmSyncSharedCapacityInputs(parent);
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

function ttbmGetSharedCapacityRows(parent) {
    return parent.find('.ttbm_ticket_row[data-shared-capacity-enabled="1"]');
}

function ttbmGetSharedCapacityAvailable(parent) {
    let totalAvailable = null;
    ttbmGetSharedCapacityRows(parent).each(function () {
        let rowAvailable = parseInt(jQuery(this).attr('data-shared-available-qty'));
        if (!isNaN(rowAvailable)) {
            totalAvailable = totalAvailable === null ? rowAvailable : Math.min(totalAvailable, rowAvailable);
        }
    });
    return totalAvailable;
}

function ttbmGetSharedSelectedQty(parent, currentRow) {
    let totalSelected = 0;
    ttbmGetSharedCapacityRows(parent).each(function () {
        if (currentRow && this === currentRow.get(0)) {
            return;
        }
        let input = jQuery(this).find('.formControl[data-price]').first();
        if (!input.length) {
            return;
        }
        totalSelected += parseInt(input.val()) || 0;
    });
    return totalSelected;
}

function ttbmConstrainSharedCapacity(target, value, parent) {
    let row = target.closest('.ttbm_ticket_row');
    if (!row.length || row.attr('data-shared-capacity-enabled') !== '1') {
        return value;
    }

    let totalAvailable = ttbmGetSharedCapacityAvailable(parent);
    if (totalAvailable === null) {
        return value;
    }

    let selectedOthers = ttbmGetSharedSelectedQty(parent, row);
    let sharedMax = Math.max(0, totalAvailable - selectedOthers);
    target.attr('max', sharedMax);

    return value > sharedMax ? sharedMax : value;
}

function ttbmSyncSharedCapacityInputs(parent) {
    let sharedRows = ttbmGetSharedCapacityRows(parent);
    if (!sharedRows.length) {
        return;
    }

    let totalAvailable = ttbmGetSharedCapacityAvailable(parent);
    if (totalAvailable === null) {
        return;
    }

    let hasCorrection = false;
    sharedRows.each(function () {
        let row = jQuery(this);
        let input = row.find('.formControl[data-price]').first();
        if (!input.length) {
            return;
        }

        let min = parseInt(input.attr('min')) || 0;
        let currentValue = parseInt(input.val()) || 0;
        let selectedOthers = ttbmGetSharedSelectedQty(parent, row);
        let sharedMax = Math.max(0, totalAvailable - selectedOthers);
        let effectiveMin = sharedMax < min ? 0 : min;
        let nextValue = currentValue > sharedMax ? sharedMax : currentValue;

        input.attr('max', sharedMax);
        if (nextValue !== currentValue) {
            input.val(nextValue);
            hasCorrection = true;
        }

        let controls = input.closest('.qtyIncDec');
        controls.find('.incQty, .decQty').removeClass('mpDisabled mage_disabled');
        if (nextValue <= effectiveMin) {
            controls.find('.decQty').addClass('mpDisabled');
        }
        if (nextValue >= sharedMax) {
            controls.find('.incQty').addClass('mpDisabled');
        }
    });

    if (hasCorrection) {
        ttbm_price_calculation(parent);
    }
}

(function ($) {
    "use strict";
    var availabilityRequestState = {};

    $(document).ready(function () {
        $('body').find('.ttbm_registration_area').each(function () {
            let currentArea = $(this);
            ttbm_price_calculation(currentArea);
            ttbmSyncSharedCapacityInputs(currentArea);
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


}(jQuery));
