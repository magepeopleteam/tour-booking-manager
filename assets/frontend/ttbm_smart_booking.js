(function ($) {
    'use strict';

    function ttbm_smart_sync_ticket_cards(parent) {
        parent = parent && parent.length ? parent : $('.ttbm_smart_inline_booking');
        parent.find('.ttbm_smart_ticket_card').each(function () {
            let card = $(this);
            let qty = parseInt(card.find('.inputIncDec').first().val(), 10);
            card.toggleClass('has-qty', !isNaN(qty) && qty > 0);
        });
    }

    function ttbm_smart_get_addon_max(card, qtyInput) {
        let candidates = [
            parseInt(card.attr('data-available'), 10),
            parseInt(qtyInput.attr('data-available'), 10),
            parseInt(qtyInput.attr('max'), 10)
        ];
        for (let i = 0; i < candidates.length; i++) {
            if (!isNaN(candidates[i]) && candidates[i] > 0) {
                return candidates[i];
            }
        }
        return 0;
    }

    function ttbm_smart_clamp_addon_qty(card, qtyInput, value, isSelected) {
        isSelected = typeof isSelected === 'boolean' ? isSelected : card.find('.ttbm_smart_addon_check').is(':checked');
        value = parseInt(value, 10);
        value = isNaN(value) ? 0 : value;

        if (!isSelected) {
            return 0;
        }

        let max = ttbm_smart_get_addon_max(card, qtyInput);
        if (max < 1) {
            return 0;
        }
        if (value < 1) {
            value = 1;
        }
        if (value > max) {
            value = max;
        }
        return value;
    }

    function ttbm_smart_apply_addon_qty(card, qtyInput, value, isSelected) {
        let parent = card.closest('.ttbm_registration_area');
        let nextValue = ttbm_smart_clamp_addon_qty(card, qtyInput, value, isSelected);
        if (parseInt(qtyInput.val(), 10) !== nextValue) {
            qtyInput.val(nextValue);
        }
        if (typeof ttbm_price_calculation === 'function') {
            ttbm_price_calculation(parent);
        }
        ttbm_smart_sync_addon_checks(parent);
        return nextValue;
    }

    function ttbm_smart_sync_addon_checks(parent) {
        parent = parent && parent.length ? parent : $('.ttbm_smart_inline_booking');
        parent.find('.ttbm_smart_addon_card').each(function () {
            let card = $(this);
            let checkbox = card.find('.ttbm_smart_addon_check');
            let isSelected = checkbox.is(':checked');
            let qtyInput = card.find('.inputIncDec, select.formControl[data-price]').first();
            let max = ttbm_smart_get_addon_max(card, qtyInput);

            if (max < 1 && !card.hasClass('ttbm_stock_sold_out')) {
                checkbox.prop('checked', false).prop('disabled', true);
                card.addClass('ttbm_stock_sold_out').removeClass('is-selected');
                if (qtyInput.length) {
                    qtyInput.val(0);
                }
                return;
            }

            card.toggleClass('is-selected', isSelected);

            if (!qtyInput.length) {
                return;
            }

            if (!isSelected) {
                qtyInput.val(0);
                return;
            }

            let qty = ttbm_smart_clamp_addon_qty(card, qtyInput, qtyInput.val(), true);
            qtyInput.val(qty);

            let min = 1;
            let controls = qtyInput.closest('.qtyIncDec');
            if (controls.length) {
                controls.find('.incQty, .decQty').removeClass('mage_disabled mpDisabled');
                controls.find('.decQty').toggleClass('mage_disabled mpDisabled', qty <= min);
                controls.find('.incQty').toggleClass('mage_disabled mpDisabled', qty >= max);
            }
        });
    }

    function ttbm_smart_adjust_addon_qty(button) {
        let card = button.closest('.ttbm_smart_addon_card');
        if (!card.length || !card.hasClass('is-selected')) {
            return false;
        }

        let qtyInput = card.find('.inputIncDec, select.formControl[data-price]').first();
        if (!qtyInput.length) {
            return false;
        }

        let currentValue = parseInt(qtyInput.val(), 10);
        currentValue = isNaN(currentValue) ? 1 : currentValue;
        let max = ttbm_smart_get_addon_max(card, qtyInput);
        if (button.hasClass('incQty')) {
            if (max > 0 && currentValue >= max) {
                ttbm_smart_apply_addon_qty(card, qtyInput, max, true);
                return true;
            }
        }
        let nextValue = button.hasClass('incQty') ? currentValue + 1 : currentValue - 1;

        ttbm_smart_apply_addon_qty(card, qtyInput, nextValue, true);
        return true;
    }

    function ttbm_smart_toggle_placeholder(parent) {
        parent = parent && parent.length ? parent : $('.ttbm_smart_inline_booking');
        let panel = parent.find('.ttbm_booking_panel');
        let placeholder = parent.find('.ttbm_smart_tickets_placeholder');
        if (!placeholder.length) {
            return;
        }
        let hasTickets = panel.find('.ttbm_smart_ticket_list').length > 0;
        placeholder.toggle(!hasTickets);
    }

    function ttbm_smart_auto_init_date(parent) {
        parent = parent && parent.length ? parent : $('.ttbm_smart_inline_booking[data-ttbm-auto-date="1"]');
        if (!parent.length || parent.data('ttbmAutoDateInit')) {
            return;
        }
        parent.data('ttbmAutoDateInit', true);

        let dateTarget = parent.find('[name="ttbm_date"]').first();
        if (!dateTarget.length) {
            return;
        }

        let panel = parent.find('.ttbm_booking_panel');
        let hasTickets = panel.find('.ttbm_smart_ticket_list, .mp_tour_ticket_form').length > 0;
        let dateVal = dateTarget.val();
        let picker = parent.find('#ttbm_select_date');

        if (!dateVal && dateTarget.is('select')) {
            let firstOpt = dateTarget.find('option[value!=""]').first();
            if (firstOpt.length) {
                dateTarget.val(firstOpt.val());
                dateVal = firstOpt.val();
            }
        }

        if (!dateVal && picker.length) {
            let firstDate = picker.data('ttbm-first-date');
            if (firstDate) {
                let formattedDate = $.datepicker.formatDate(ttbm_date_format, new Date(firstDate + 'T00:00:00'));
                picker.val(formattedDate);
                parent.find('input[name="ttbm_date"]').val(firstDate);
                dateVal = firstDate;
            }
        }

        if (typeof ttbm_toggle_book_now_by_date === 'function') {
            ttbm_toggle_book_now_by_date(parent);
        }

        if (hasTickets) {
            ttbm_smart_toggle_placeholder(parent);
            if (typeof ttbm_price_calculation === 'function') {
                ttbm_price_calculation(parent);
            }
            return;
        }

        if (!dateVal || typeof get_ttbm_ticket !== 'function') {
            return;
        }

        let timeSlot = parent.find('.ttbm_select_time_area');
        if (timeSlot.length) {
            timeSlot.show();
            let timeInput = timeSlot.find('[name="ttbm_select_time"], [data-radio-value]').first();
            if (timeInput.length && !timeInput.val()) {
                if (timeInput.is('select')) {
                    let firstTime = timeInput.find('option').first().val();
                    if (firstTime) {
                        timeInput.val(firstTime).trigger('change');
                    }
                } else {
                    let firstRadio = timeSlot.find('.customRadio[data-radio]').first();
                    if (firstRadio.length) {
                        firstRadio.trigger('click');
                    }
                }
            } else if (timeInput.length && timeInput.val()) {
                get_ttbm_ticket(timeInput);
            }
            return;
        }

        get_ttbm_ticket(dateTarget);
    }

    function ttbm_smart_init_booking(parent) {
        parent = parent && parent.length ? parent : $('.ttbm_smart_inline_booking');
        if (!parent.length) {
            return;
        }
        ttbm_smart_sync_ticket_cards(parent);
        ttbm_smart_sync_addon_checks(parent);
        ttbm_smart_toggle_placeholder(parent);
        parent.find('.ttbm_extra_service_area').hide();
    }

    $(document).ready(function () {
        $('.ttbm_smart_inline_booking').each(function () {
            ttbm_smart_init_booking($(this));
        });
        window.setTimeout(function () {
            $('.ttbm_smart_inline_booking[data-ttbm-auto-date="1"]').each(function () {
                ttbm_smart_auto_init_date($(this));
            });
        }, 0);
    });

    $(document).on('ttbm:ticket-refreshed', '.ttbm_smart_inline_booking', function () {
        ttbm_smart_init_booking($(this));
    });

    $(document).on('change input blur', '.ttbm_smart_inline_booking .ttbm_smart_addon_qty .formControl[data-price]', function () {
        let qtyInput = $(this);
        let card = qtyInput.closest('.ttbm_smart_addon_card');
        ttbm_smart_apply_addon_qty(card, qtyInput, qtyInput.val());
    });

    $(document).on('change', '.ttbm_smart_inline_booking .inputIncDec', function () {
        let parent = $(this).closest('.ttbm_registration_area');
        if ($(this).closest('.ttbm_smart_addon_card').length) {
            return;
        }
        ttbm_smart_sync_ticket_cards(parent);
        ttbm_smart_sync_addon_checks(parent);
    });

    $(document).on('change', '.ttbm_smart_addon_check', function () {
        let card = $(this).closest('.ttbm_smart_addon_card');
        let qtyInput = card.find('.inputIncDec, select.formControl[data-price]').first();
        if (!qtyInput.length) {
            return;
        }
        let isChecked = $(this).is(':checked');
        let max = ttbm_smart_get_addon_max(card, qtyInput);
        if (isChecked && max < 1) {
            $(this).prop('checked', false);
            card.removeClass('is-selected');
            qtyInput.val(0);
            return;
        }
        let nextVal = isChecked ? 1 : 0;
        card.toggleClass('is-selected', isChecked);
        ttbm_smart_apply_addon_qty(card, qtyInput, nextVal, isChecked);
    });

    $(document).on('click', '.ttbm_smart_inline_booking .ttbm_smart_addon_qty .incQty, .ttbm_smart_inline_booking .ttbm_smart_addon_qty .decQty', function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        ttbm_smart_adjust_addon_qty($(this));
        return false;
    });

    document.addEventListener('click', function (e) {
        let button = e.target.closest('.ttbm_smart_inline_booking .ttbm_smart_addon_qty .incQty, .ttbm_smart_inline_booking .ttbm_smart_addon_qty .decQty');
        if (!button) {
            return;
        }
        e.preventDefault();
        e.stopImmediatePropagation();
        ttbm_smart_adjust_addon_qty($(button));
    }, true);

    $(document).on('click', '.ttbm_smart_inline_booking .qtyIncDec .incQty, .ttbm_smart_inline_booking .qtyIncDec .decQty', function () {
        if ($(this).closest('.ttbm_smart_addon_qty').length) {
            return;
        }
        let parent = $(this).closest('.ttbm_registration_area');
        window.setTimeout(function () {
            ttbm_smart_sync_ticket_cards(parent);
            ttbm_smart_sync_addon_checks(parent);
        }, 0);
    });
})(jQuery);
