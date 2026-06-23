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

    function ttbm_smart_sync_addon_checks(parent) {
        parent = parent && parent.length ? parent : $('.ttbm_smart_inline_booking');
        parent.find('.ttbm_smart_addon_card').each(function () {
            let card = $(this);
            let qty = parseInt(card.find('.inputIncDec').first().val(), 10);
            let checked = !isNaN(qty) && qty > 0;
            card.find('.ttbm_smart_addon_check').prop('checked', checked);
            card.toggleClass('is-selected', checked);
        });
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
        ttbm_smart_init_booking($('.ttbm_smart_inline_booking'));
    });

    $(document).on('ttbm:ticket-refreshed', '.ttbm_smart_inline_booking', function () {
        ttbm_smart_init_booking($(this));
    });

    $(document).on('change', '.ttbm_smart_inline_booking .inputIncDec', function () {
        let parent = $(this).closest('.ttbm_registration_area');
        ttbm_smart_sync_ticket_cards(parent);
        ttbm_smart_sync_addon_checks(parent);
    });

    $(document).on('change', '.ttbm_smart_addon_check', function () {
        let card = $(this).closest('.ttbm_smart_addon_card');
        let qtyInput = card.find('.inputIncDec').first();
        if (!qtyInput.length) {
            return;
        }
        let nextVal = $(this).is(':checked') ? 1 : 0;
        qtyInput.val(nextVal).trigger('change');
    });

    $(document).on('click', '.ttbm_smart_inline_booking .qtyIncDec .incQty, .ttbm_smart_inline_booking .qtyIncDec .decQty', function () {
        let parent = $(this).closest('.ttbm_registration_area');
        window.setTimeout(function () {
            ttbm_smart_sync_ticket_cards(parent);
            ttbm_smart_sync_addon_checks(parent);
        }, 0);
    });
})(jQuery);
