// Frontend Attendee Autocomplete for Tour Booking Manager
function ttbm_init_attendee_autocomplete(container) {
    if (!container || container.length === 0) {
        container = jQuery('body');
    }

    // Target all text and email inputs in attendee forms
    container.find('.ttbm_attendee_form_item input[type="text"], .ttbm_attendee_form_item input[type="email"], .ttbm_guest_item input[type="text"], .ttbm_guest_item input[type="email"]').each(function () {
        let $input = jQuery(this);

        // Skip if already has autocomplete or is a date field
        if ($input.hasClass('ui-autocomplete-input') || $input.hasClass('date_type')) {
            return;
        }

        let name = $input.attr('name') || '';
        if (!name) return;

        // Only target fields that look like name, email, or phone + common translations
        let isMatch = name.match(/(name|email|phone|full-name|address|nome|cognome|indirizzo|telefono|mail|tel|cel|birth|nascita)/i);
        // Also allow if it's explicitly an attendee form field
        let isAttendeeField = name.indexOf('[') !== -1;

        if (!isMatch && !isAttendeeField) return;

        let ajax_url = typeof ttbm_ajax_url !== 'undefined' ? ttbm_ajax_url : '/wp-admin/admin-ajax.php';

        $input.autocomplete({
            source: function (request, response) {
                jQuery.ajax({
                    url: ajax_url,
                    dataType: "json",
                    data: {
                        action: "ttbm_customer_search",
                        term: request.term
                    },
                    success: function (data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                event.preventDefault();

                // Find the container for this specific attendee
                let $container = $input.closest('.ttbm_attendee_form_item, .ttbm_guest_item');

                // Helper to fill by partial name match
                let fillField = function (patterns, value) {
                    if (!value) return;
                    if (!Array.isArray(patterns)) patterns = [patterns];

                    let $target = $container.find('input').filter(function () {
                        let targetName = jQuery(this).attr('name') || '';
                        targetName = targetName.toLowerCase();
                        return patterns.some(pattern => targetName.indexOf(pattern) !== -1);
                    });
                    if ($target.length > 0) $target.val(value);
                };

                fillField(['first-name', 'nome', 'first_name'], ui.item.first_name);
                fillField(['last-name', 'cognome', 'last_name', 'surname'], ui.item.last_name);
                fillField(['email', 'mail'], ui.item.email);
                fillField(['phone', 'telefono', 'tel', 'cel'], ui.item.phone);
                fillField(['address', 'indirizzo'], ui.item.address || '');

                // If there's a full name field
                let fullName = (ui.item.first_name + ' ' + ui.item.last_name).trim();
                fillField(['full-name', 'name', 'nome-completo'], fullName);

                // Try to fill other custom fields if we have all_meta
                if (ui.item.all_meta) {
                    $container.find('input').each(function () {
                        let inputName = jQuery(this).attr('name') || '';
                        let cleanName = inputName.replace(/\[\]/g, '');
                        if (ui.item.all_meta[cleanName]) {
                            jQuery(this).val(ui.item.all_meta[cleanName]);
                        }
                    });
                }

                // Also update the current field explicitly
                $input.val(ui.item.value);
            },
            open: function () {
                jQuery(this).removeClass("ui-corner-all").addClass("ui-corner-top");
                jQuery('.ui-autocomplete').css('z-index', 100000);
            },
            close: function () {
                jQuery(this).removeClass("ui-corner-top").addClass("ui-corner-all");
            }
        });
    });
}

// Initialize on page load for any existing forms
jQuery(document).ready(function () {
    ttbm_init_attendee_autocomplete();
});
