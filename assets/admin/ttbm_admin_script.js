(function ($) {
    "use strict";
    $(document).on('click', '.ttbm_add_item', function () {
        let parent = $(this).closest('.mp_settings_area');
        let item = parent.find('>.mp_hidden_content').first().find('.mp_hidden_item').html();
        load_sortable_datepicker(parent, item);
        parent.find('.mp_item_insert').find('.add_ttbm_select2').select2({});
        return true;
    });
    $(document).on("click", ".mp_remove_icon", function (e) {
        e.preventDefault();
        if (
            confirm(
                "Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel ."
            )
        ) {
            $(this).closest(".mp_remove_area").slideUp(250).remove();
            return true;
        } else {
            return false;
        }
    });
}(jQuery));
(function ($) {
    "use strict";
    $(document).on('change', '.ttbm_settings [name="ttbm_type"]', function (e) {
        e.preventDefault();
        let ttbm_type = $(this).val();
        let parent = $(this).closest('.ttbm_settings');
        if (ttbm_type === 'hotel') {
            parent.find('.ttbm_ticket_config').slideUp(250);
            parent.find('.ttbm_tour_hotel_setting').slideDown(250);
        } else {
            parent.find('.ttbm_ticket_config').slideDown(250);
            parent.find('.ttbm_tour_hotel_setting').slideUp(250);
        }
    });
    //*********Pricing************//
    $(document).on('click', '.ttbm_price_config  .mp_add_item', function (e) {
        if (e.result) {
            let parent = $(this).closest('.ttbm_price_config');
            let unique_id = new Date().getTime();
            parent.find('table tbody tr:last-child').find('[data-input-text]').attr('data-input-text', unique_id);
            parent.find('table tbody tr:last-child').find('[name="ttbm_hidden_ticket_text[]"]').val(unique_id);
        }
    });
    //*********Import ticket type************//
    $(document).on('change', '.ttbm_price_config [name="ticket_type_import"]', function () {
        let form_id = $(this).val();
        let parent = $(this).closest('.ttbm_price_config');
        let target = parent.find('.ttbm_insert_ticket_type');
        let post_id = $('[name="post_ID"]').val();
        if (form_id) {
            $.ajax({
                type: 'POST', url: mp_ajax_url, data: {
                    "action": "get_ttbm_insert_ticket_type", "form_id": form_id, "post_id": post_id
                }, beforeSend: function () {
                    dLoader(parent);
                }, success: function (data) {
                    target.html(data);
                    dLoaderRemove(parent);
                }
            });
        }
    });
    //*********Add F.A.Q Item************//
    $(document).on('click', '.ttbm_add_faq_content', function () {
        let $this = $(this);
        let parent = $this.closest('.tabsItem');
        let dt = new Date();
        let time = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
        $.ajax({
            type: 'POST', url: mp_ajax_url, data: {"action": "get_ttbm_add_faq_content", "id": time}, beforeSend: function () {
                dLoader(parent);
            }, success: function (data) {
                $this.before(data);
                tinymce.execCommand('mceAddEditor', true, time);
                dLoaderRemove(parent);
            }, error: function (response) {
                console.log(response);
            }
        });
        return false;
    });
    //*********Day wise details************//
    $(document).on('click', '.ttbm_add_day_wise_details', function () {
        let $this = $(this);
        let parent = $this.closest('.tabsItem');
        let dt = new Date();
        let time = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
        $.ajax({
            type: 'POST', url: mp_ajax_url, data: {"action": "get_ttbm_add_day_wise_details", "id": time}, beforeSend: function () {
                dLoader(parent);
            }, success: function (data) {
                $this.before(data);
                tinymce.execCommand('mceAddEditor', true, time);
                dLoaderRemove(parent);
            }, error: function (response) {
                console.log(response);
            }
        });
        return false;
    });
    //*****Location****************//
    $(document).on('click', '.ttbm_settings_general [data-target-popup]', function () {
        let target = $(this).closest('.ttbm_settings_general').find('.ttbm_location_form_area');
        $.ajax({
            type: 'POST', url: mp_ajax_url, data: {
                "action": "load_ttbm_location_form"
            }, beforeSend: function () {
                simpleSpinner(target);
            }, success: function (data) {
                target.html(data).slideDown('fast').promise().done(function () {
                    simpleSpinnerRemove(target);
                });
            }
        });
    });
    $(document).on('click', '.ttbm_settings_general  .popupClose', function (e) {
        if (e.result) {
            $(this).closest('.ttbm_settings_general').find('.ttbm_location_form_area').html('');
        }
    });
    $(document).on('click', '.ttbm_new_location_save,.ttbm_new_location_save_close', function () {
        ttbm_new_location_save($(this));
    });
    function ttbm_new_location_save($this) {
        let parent = $this.closest('.popupMainArea');
        parent.find('.ttbm_success_info').slideUp('fast');
        let name = parent.find('[name="ttbm_new_location_name"]').val();
        let description = parent.find('[name="ttbm_location_description"]').val();
        let address = parent.find('[name="ttbm_location_address"]').val();
        let country = parent.find('[name="ttbm_location_country"]').val();
        let image = parent.find('[name="ttbm_location_image"]').val();
        if (!name) {
            parent.find('[data-required="ttbm_new_location_name"]').slideDown('fast');
        } else {
            parent.find('[data-required="ttbm_new_location_name"]').slideUp('fast');
        }
        if (!image) {
            parent.find('[data-required="ttbm_location_image"]').slideDown('fast');
        } else {
            parent.find('[data-required="ttbm_location_image"]').slideUp('fast');
        }
        if (name && image) {
            $.ajax({
                type: 'POST', url: mp_ajax_url, data: {
                    "action": "ttbm_new_location_save", "name": name, "description": description, "address": address, "country": country, "image": image, "_wp_nonce": parent.find('[name="ttbm_add_new_location_popup"]').val(),
                }, beforeSend: function () {
                    dLoader(parent);
                }, success: function () {
                    parent.find('[name="ttbm_new_location_name"]').val('');
                    parent.find('[name="ttbm_location_description"]').val('');
                    parent.find('[name="ttbm_location_address"]').val('');
                    parent.find('[name="ttbm_location_country"]').val('');
                    parent.find('[name="ttbm_location_image"]').val('');
                    $this.closest('.popupMainArea').find('.mp_remove_single_image').trigger('click');
                    parent.find('.ttbm_success_info').slideDown('fast');
                    ttbm_reload_location();
                    dLoaderRemove(parent);
                    if (($this).hasClass('ttbm_new_location_save_close')) {
                        $this.closest('.popupMainArea').find('.popupClose').trigger('click');
                    }
                    return true;
                }, error: function (response) {
                    console.log(response);
                }
            });
        }
        return false;
    }
    function ttbm_reload_location() {
        let ttbm_id = $('[name="post_id"]').val();
        let parent = $('.ttbm_location_select_area');
        $.ajax({
            type: 'POST', url: mp_ajax_url, data: {
                "action": "ttbm_reload_location_list", "ttbm_id": ttbm_id
            }, beforeSend: function () {
                dLoader(parent);
            }, success: function (data) {
                parent.empty().append(data).promise().done(function () {
                    parent.find('.ttbm_select2').select2({});
                });
                return true;
            }, error: function (response) {
                console.log(response);
            }
        });
    }
    //*******Feature**************//
    $(document).on('click', '.ttbm_settings_feature [data-target-popup="add_new_feature_popup"]', function () {
        let target = $(this).closest('.ttbm_settings_feature').find('.ttbm_feature_form_area');
        $.ajax({
            type: 'POST', url: mp_ajax_url, data: {
                "action": "load_ttbm_feature_form"
            }, beforeSend: function () {
                simpleSpinner(target);
            }, success: function (data) {
                target.html(data).slideDown('fast').promise().done(function () {
                    simpleSpinnerRemove(target);
                });
            }
        });
    });
    $(document).on('click', '.ttbm_settings_feature  .popupClose', function (e) {
        if (e.result) {
            $(this).closest('.ttbm_settings_feature').find('.ttbm_feature_form_area').html('');
        }
    });
    $(document).on('click', '.ttbm_new_feature_save,.ttbm_new_feature_save_close', function () {
        ttbm_new_feature_save($(this));
    });
    function ttbm_new_feature_save($this) {
        let parent = $this.closest('.popupMainArea');
        parent.find('.ttbm_success_info').slideUp('fast');
        let feature_name = parent.find('[name="ttbm_feature_name"]').val();
        let feature_description = parent.find('[name="ttbm_feature_description"]').val();
        let feature_icon = parent.find('[name="ttbm_feature_icon"]').val();
        if (!feature_name) {
            parent.find('[data-required="ttbm_feature_name"]').slideDown('fast');
        } else {
            parent.find('[data-required="ttbm_feature_name"]').slideUp('fast');
        }
        if (!feature_icon) {
            parent.find('[data-required="ttbm_feature_icon"]').slideDown('fast');
        } else {
            parent.find('[data-required="ttbm_feature_icon"]').slideUp('fast');
        }
        let selected_included_in_price_features = $('[name="ttbm_service_included_in_price"]').val();
        let selected_excluded_in_price_features = $('[name="ttbm_service_excluded_in_price"]').val();
        let includedFeaturesArray = selected_included_in_price_features.split(',');
        let excludedFeaturesArray = selected_excluded_in_price_features.split(',');
        if (feature_name && feature_icon) {
            $.ajax({
                type: 'POST',
                url: mp_ajax_url,
                data: {
                    "action": "ttbm_new_feature_save",
                    "feature_name": feature_name,
                    "feature_description": feature_description,
                    "feature_icon": feature_icon,
                    "_wp_nonce": parent.find('[name="ttbm_add_new_feature_popup"]').val(),
                },
                beforeSend: function () {
                    dLoader(parent);
                },
                success: function () {
                    parent.find('[name="ttbm_feature_name"]').val('');
                    parent.find('[name="ttbm_feature_description"]').val('');
                    parent.find('[name="ttbm_feature_icon"]').val('');
                    $this.closest('.popupMainArea').find('.remove_input_icon').trigger('click');
                    parent.find('.ttbm_success_info').slideDown('fast');
                    console.log("Included Features Array: ", includedFeaturesArray);
                    console.log("Excluded Features Array: ", excludedFeaturesArray);
                    //ttbm_reload_feature_list(includedFeaturesArray, excludedFeaturesArray);
                    reload_features();
                    dLoaderRemove(parent);
                    if (($this).hasClass('ttbm_new_feature_save_close')) {
                        $this.closest('.popupMainArea').find('.popupClose').trigger('click');
                    }
                    return true;
                },
                error: function (response) {
                    console.log(response);
                }
            });
        }
        return false;
    }
    function reload_features() {
        let ttbm_id = $('[name="post_ID"]').val();
        let parent = $('.ttbm_features_table');
        $.ajax({
            type: 'POST',
            url: mp_ajax_url,
            data: {
                "action": "ttbm_reload_feature_list",
                "ttbm_id": ttbm_id
            },
            beforeSend: function () {
                dLoader(parent);
            },
            success: function (response) {
                // parent.empty().append(data);
                // // Update the included features section
                // $('.included-features-section').html(response.data.included_features_html);
                // $('#ttbm_display_include_service').html(response.data.included_features_html);
                // Update the excluded features section
                // $('.excluded-features-section').html(response.data.excluded_features_html);
                // $('#ttbm_display_exclude_service').html(response.data.excluded_features_html);
                if (window.location.href.indexOf('#ttbm_settings_feature') === -1) {
                    window.location.href = window.location.href + '#ttbm_settings_feature';
                }
                window.location.reload();
            },
            error: function (response) {
                console.log(response);
            }
        });
    }
    function ttbm_reload_feature_list(includedFeaturesArray, excludedFeaturesArray) {
        let ttbm_id = $('[name="post_ID"]').val();
        let parent = $('.ttbm_features_table');
        $.ajax({
            type: 'POST',
            url: mp_ajax_url,
            data: {
                "action": "ttbm_reload_feature_list",
                "ttbm_id": ttbm_id
            },
            beforeSend: function () {
                dLoader(parent);
            },
            success: function (data) {
                parent.empty().append(data);
                console.log("Reloaded feature list");
                // After reloading, reapply the checkbox states
                $('[name="ttbm_service_included_in_price"]').each(function () {
                    let feature = $(this).val();
                    console.log("Checking included feature:", feature);
                    if (includedFeaturesArray.includes(feature)) {
                        $(this).prop('checked', true);
                        console.log("Included feature checked:", feature);
                    }
                });
                $('[name="ttbm_service_excluded_in_price"]').each(function () {
                    let feature = $(this).val();
                    console.log("Checking excluded feature:", feature);
                    if (excludedFeaturesArray.includes(feature)) {
                        $(this).prop('checked', true);
                        console.log("Excluded feature checked:", feature);
                    }
                });
                return true;
            },
            error: function (response) {
                console.log(response);
            }
        });
    }
    //*******Activity**************//
    $(document).on('click', '.ttbm_settings_activities [data-target-popup]', function () {
        let target = $(this).closest('.ttbm_settings_activities').find('.ttbm_activity_form_area');
        $.ajax({
            type: 'POST', url: mp_ajax_url, data: {
                "action": "load_ttbm_activity_form"
            }, beforeSend: function () {
                simpleSpinner(target);
            }, success: function (data) {
                target.html(data).slideDown('fast').promise().done(function () {
                    simpleSpinnerRemove(target);
                });
            }
        });
    });
    $(document).on('click', '.ttbm_settings_activities  .popupClose', function (e) {
        if (e.result) {
            $(this).closest('.ttbm_settings_activities').find('.ttbm_activity_form_area').html('');
        }
    });
    $(document).on('click', '.ttbm_new_activity_save,.ttbm_new_activity_save_close', function () {
        ttbm_new_activity_save($(this));
    });
    function ttbm_new_activity_save($this) {
        let parent = $this.closest('.popupMainArea');
        parent.find('.ttbm_success_info').slideUp('fast');
        let activity_name = parent.find('[name="ttbm_activity_name"]').val();
        let activity_description = parent.find('[name="ttbm_activity_description"]').val();
        let activity_icon = parent.find('[name="ttbm_activity_icon"]').val();
        if (!activity_name) {
            parent.find('[data-required="ttbm_activity_name"]').slideDown('fast');
        } else {
            parent.find('[data-required="ttbm_activity_name"]').slideUp('fast');
        }
        if (!activity_icon) {
            parent.find('[data-required="ttbm_activity_icon"]').slideDown('fast');
        } else {
            parent.find('[data-required="ttbm_activity_icon"]').slideUp('fast');
        }
        if (activity_name && activity_icon) {
            $.ajax({
                type: 'POST', url: mp_ajax_url, data: {
                    "action": "ttbm_new_activity_save", "activity_name": activity_name, "activity_description": activity_description, "activity_icon": activity_icon,"_wp_nonce": parent.find('[name="ttbm_add_new_activity_popup"]').val(),
                }, beforeSend: function () {
                    dLoader(parent);
                }, success: function () {
                    parent.find('[name="ttbm_activity_name"]').val('');
                    parent.find('[name="ttbm_activity_description"]').val('');
                    parent.find('[name="ttbm_activity_icon"]').val('');
                    $this.closest('.popupMainArea').find('.remove_input_icon').trigger('click');
                    parent.find('.ttbm_success_info').slideDown('fast');
                    ttbm_reload_activity_list();
                    dLoaderRemove(parent);
                    if (($this).hasClass('ttbm_new_activity_save_close')) {
                        $this.closest('.popupMainArea').find('.popupClose').trigger('click');
                    }
                    return true;
                }, error: function (response) {
                    console.log(response);
                }
            });
        }
        return false;
    }
    function ttbm_reload_activity_list() {
        let ttbm_id = $('[name="post_id"]').val();
        let parent = $('.ttbm_activities_table');
        $.ajax({
            type: 'POST', url: mp_ajax_url, data: {
                "action": "ttbm_reload_activity_list", "ttbm_id": ttbm_id
            }, beforeSend: function () {
                dLoader(parent);
            }, success: function (data) {
                parent.empty().append(data).promise().done(function () {
                    parent.find('.ttbm_select2').select2({});
                });
                return true;
            }, error: function (response) {
                console.log(response);
            }
        });
    }
    //*****Place you see****************//
    $(document).on('click', '.ttbm_settings_place_you_see [data-target-popup]', function () {
        let target = $(this).closest('.ttbm_settings_place_you_see').find('.ttbm_place_you_see_form_area');
        $.ajax({
            type: 'POST', url: mp_ajax_url, data: {
                "action": "load_ttbm_place_you_see_form"
            }, beforeSend: function () {
                dLoader(target);
            }, success: function (data) {
                target.html(data).slideDown('fast').promise().done(function () {
                    dLoaderRemove(target);
                });
            }
        });
    });
    $(document).on('click', '.ttbm_settings_place_you_see  .popupClose', function (e) {
        if (e.result) {
            $(this).closest('.ttbm_settings_place_you_see').find('.ttbm_place_you_see_form_area').html('');
        }
    });
    function ttbm_reload_place_you_see() {
        let ttbm_id = $('[name="post_id"]').val();
        let parent = $('.ttbm_place_you_see_table');
        $.ajax({
            type: 'POST', url: mp_ajax_url, data: {
                "action": "ttbm_reload_place_you_see_list", "ttbm_id": ttbm_id
            }, beforeSend: function () {
                dLoader(parent);
            }, success: function (data) {
                parent.empty().append(data);
                return true;
            }, error: function (response) {
                console.log(response);
            }
        });
    }
}(jQuery));
//====================//
(function ($) {
    "use strict";
    $(document).ready(function () {
        ttbm_travel_type_change();
    });
    $(document).on('change', '#ttbm_travel_type', function () {
        ttbm_travel_type_change();
    });
    function ttbm_travel_type_change() {
        let ticket_type = $('#ttbm_travel_type').val();
        let fixed = {
            0: '#mage_row_ttbm_travel_reg_end_date', 1: '#mage_row_ttbm_travel_start_date', 2: '#mage_row_ttbm_travel_start_date_time', 3: '#mage_row_ttbm_travel_end_date'
        };
        let particular = {
            0: '#mage_row_ttbm_particular_dates'
        };
        let repeated = {
            0: '#mage_row_ttbm_travel_repeated_after', 1: '#mage_row_mep_disable_ticket_time', 2: '#mage_row_mep_ticket_times_global', 3: '#mage_row_mep_ticket_times_sat', 4: '#mage_row_mep_ticket_times_sun', 5: '#mage_row_mep_ticket_times_mon', 6: '#mage_row_mep_ticket_times_tue', 7: '#mage_row_mep_ticket_times_wed', 8: '#mage_row_mep_ticket_times_thu', 9: '#mage_row_mep_ticket_times_fri', 10: '#mage_row_mep_ticket_offdays', 11: '#mage_row_mep_ticket_off_dates', 12: '#mage_row_ttbm_travel_repeated_start_date', 13: '#mage_row_ttbm_travel_repeated_end_date', 14: '.ttbm_special_on_dates_setting',
        };
        if (ticket_type === 'fixed') {
            ttbm_travel_type(fixed, particular, repeated)
        }
        if (ticket_type === 'particular') {
            ttbm_travel_type(particular, fixed, repeated)
        }
        if (ticket_type === 'repeated') {
            ttbm_travel_type(repeated, particular, fixed)
        }
    }
    function ttbm_travel_type(visible, hidden_1, hidden_2) {
        for (let id in hidden_1) {
            $(hidden_1[id]).slideUp('fast');
        }
        for (let id in hidden_2) {
            $(hidden_2[id]).slideUp('fast');
        }
        for (let id in visible) {
            $(visible[id]).slideDown('fast');
        }
    }
    // =====================sidebar modal open close=============
    $(document).on('click', '[data-modal]', function (e) {
        const modalTarget = $(this).data('modal');
        $(`[data-modal-target="${modalTarget}"]`).addClass('open');
    });
    $(document).on('click', '[data-modal-target] .ttbm-modal-close', function (e) {
        $(this).closest('[data-modal-target]').removeClass('open');
    });
    // ================FAQ sidebar modal=================//
    $(document).on('click', '.ttbm-faq-item-new', function (e) {
        $('#ttbm-faq-msg').html('');
        $('.ttbm_faq_save_buttons').show();
        $('.ttbm_faq_update_buttons').hide();
        empty_faq_form();
    });
    function close_sidebar_modal(e) {
        e.preventDefault();
        e.stopPropagation();
        $('.ttbm-modal-container').removeClass('open');
    }
    $(document).on('click', '.ttbm-faq-item-edit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#ttbm-faq-msg').html('');
        $('.ttbm_faq_save_buttons').hide();
        $('.ttbm_faq_update_buttons').show();
        var itemId = $(this).closest('.ttbm-faq-item').data('id');
        var parent = $(this).closest('.ttbm-faq-item');
        var headerText = parent.find('.faq-header p').text().trim();
        var faqContentId = parent.find('.faq-content').text().trim();
        var editorId = 'ttbm_faq_content';
        $('input[name="ttbm_faq_title"]').val(headerText);
        $('input[name="ttbm_faq_item_id"]').val(itemId);
        if (tinymce.get(editorId)) {
            tinymce.get(editorId).setContent(faqContentId);
        } else {
            $('#' + editorId).val(faqContentId);
        }
    });
    $(document).on('click', '.ttbm-faq-item-delete', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var itemId = $(this).closest('.ttbm-faq-item').data('id');
        var isConfirmed = confirm('Are you sure you want to delete this row?');
        if (isConfirmed) {
            delete_faq_item(itemId);
        } else {
            console.log('Deletion canceled.' + itemId);
        }
    });
    function empty_faq_form() {
        $('input[name="ttbm_faq_title"]').val('');
        tinyMCE.get('ttbm_faq_content').setContent('');
        $('input[name="ttbm_faq_item_id"]').val('');
    }
    $(document).on('click', '#ttbm_faq_update', function (e) {
        e.preventDefault();
        update_faq();
    });
    $(document).on('click', '#ttbm_faq_save', function (e) {
        e.preventDefault();
        save_faq();
    });
    $(document).on('click', '#ttbm_faq_save_close', function (e) {
        e.preventDefault();
        save_faq();
        close_sidebar_modal(e);
    });
    function update_faq() {
        var title = $('input[name="ttbm_faq_title"]');
        var content = tinyMCE.get('ttbm_faq_content').getContent();
        var postID = $('input[name="ttbm_post_id"]');
        var itemId = $('input[name="ttbm_faq_item_id"]');
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_faq_data_update',
                ttbm_faq_title: title.val(),
                ttbm_faq_content: content,
                ttbm_faq_postID: postID.val(),
                ttbm_faq_itemID: itemId.val(),
                nonce: ttbm_admin_ajax.nonce
            },
            success: function (response) {
                $('#ttbm-faq-msg').html(response.data.message);
                $('.ttbm-faq-items').html('');
                $('.ttbm-faq-items').append(response.data.html);
                setTimeout(function () {
                    $('.ttbm-modal-container').removeClass('open');
                    empty_faq_form();
                }, 1000);
            },
            error: function (error) {
                console.log('Error:', error);
            }
        });
    }
    function save_faq() {
        var title = $('input[name="ttbm_faq_title"]');
        var content = tinyMCE.get('ttbm_faq_content').getContent();
        var postID = $('input[name="ttbm_post_id"]');
        console.log(ttbm_admin_ajax.ajax_url);
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_faq_data_save',
                ttbm_faq_title: title.val(),
                ttbm_faq_content: content,
                ttbm_faq_postID: postID.val(),
                nonce: ttbm_admin_ajax.nonce
            },
            success: function (response) {
                $('#ttbm-faq-msg').html(response.data.message);
                $('.ttbm-faq-items').html('');
                $('.ttbm-faq-items').append(response.data.html);
                empty_faq_form();
            },
            error: function (error) {
                console.log('Error:', error);
            }
        });
    }
    function delete_faq_item(itemId) {
        var postID = $('input[name="ttbm_post_id"]');
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_faq_delete_item',
                ttbm_faq_postID: postID.val(),
                itemId: itemId,
                nonce: ttbm_admin_ajax.nonce
            },
            success: function (response) {
                $('.ttbm-faq-items').html('');
                $('.ttbm-faq-items').append(response.data.html);
            },
            error: function (error) {
                console.log('Error:', error);
            }
        });
    }

    // faq sorting
    $(document).on("ready", function(e) {
        $(".ttbm-faq-items").sortable({
            update: function(event, ui) {
                event.preventDefault();
                var sortedIDs = $(this).sortable("toArray", { attribute: "data-id" });
                $.ajax({
                    url: ttbm_admin_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'ttbm_sort_faq',
                        postID: $('input[name="ttbm_post_id"]').val(),
                        sortedIDs: sortedIDs,
                        nonce: ttbm_admin_ajax.nonce
                    },
                    success: function (response) {
                        $('.ttbm-faq-items').html('');
                        $('.ttbm-faq-items').append(response.data.html);
                    },
                    error: function (error) {
                        console.log('Error:', error);
                    }
                })
            }
        });
    });
}(jQuery));
//==========search tour list page=================//
(function ($) {
    "use strict";
    $(document).on('change', '#ttbm_list_page [name="ttbm_filter_type"]', function () {
        let parent = $(this).closest('#ttbm_list_page');
        let value = $(this).val();
        parent.find('[name="' + value + '"]').trigger('change');
    });
    $(document).on('change', '#ttbm_list_page [name="ttbm_id"]', function () {
        let parent = $(this).closest('#ttbm_list_page');
        filter_ttbm_list(parent, $(this), 'post_id');
    });
    $(document).on('change', '#ttbm_list_page [name="ttbm_list_category_filter"]', function () {
        let parent = $(this).closest('#ttbm_list_page');
        filter_ttbm_list(parent, $(this), 'category');
    });
    $(document).on('change', '#ttbm_list_page [name="ttbm_list_organizer_filter"]', function () {
        let parent = $(this).closest('#ttbm_list_page');
        filter_ttbm_list(parent, $(this), 'organizer');
    });
    $(document).on('change', '#ttbm_list_page [name="ttbm_list_location_filter"]', function () {
        let parent = $(this).closest('#ttbm_list_page');
        filter_ttbm_list(parent, $(this), 'location');
    });
    function filter_ttbm_list(parent, current, data) {
        let current_value = current.val();
        parent.find('tr[data-' + data + ']').each(function () {
            if (current_value) {
                let value = $(this).data(data).toString();
                value = value.split(",");
                let active = (value.indexOf(current_value) !== -1) ? 1 : 0;
                if (active > 0) {
                    $(this).addClass('mp_search_on').removeClass('mp_search_off');
                } else {
                    $(this).addClass('mp_search_off').removeClass('mp_search_on');
                }
            } else {
                $(this).addClass('mp_search_on').removeClass('mp_search_off');
            }
        });
    }
    $(document).on('click', '#ttbm_list_page .ttbm_trash_post', function () {
        let alert_text = $(this).data('alert');
        if (confirm(alert_text + '\n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .')) {
            let target = $(this).closest('#ttbm_list_page');
            let post_id = $(this).data('post-id');
            $.ajax({
                type: 'POST', url: mp_ajax_url, data: {
                    "action": "ttbm_trash_post",
                    "post_id": post_id,
                    "nonce": $(this).closest('.buttonGroup').find('#edd_sample_nonce').val(),
                }, beforeSend: function () {
                    dLoader(target);
                }, success: function (data) {
                    dLoaderRemove(target);
                    window.location.reload();
                }
            });
            return true;
        }
        return false;
    });
}(jQuery));


