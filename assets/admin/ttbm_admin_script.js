(function ($) {
    "use strict";
    $(document).on('click', '.ttbm_copy_btn', function (e) {
        e.preventDefault();
        var shortcodeText = $(this).siblings('.ttbm_shortcode_text').text();
        var tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(shortcodeText).select();
        document.execCommand('copy');
        tempInput.remove();
        $(this).text('Copied!').delay(1500).queue(function (next) {
            $(this).text('Copy');
            next();
        });
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
    $(document).on('click', '.ttbm-pricing-type', function (e) {
        e.preventDefault();
        $('.ttbm-pricing-type').removeClass('active');
        $(this).addClass('active');
        
        let ttbm_type = $(this).data('price-type');
        $(this).closest('.ttbm-pricing-types').find('input[name="ttbm_type"]').val(ttbm_type);

        let parent = $(this).closest('.ttbm_settings');
        if (ttbm_type === 'hotel') {
            parent.find('.ttbm_ticket_config').slideUp(250);
            parent.find('.ttbma_group_price').slideUp(250);
            parent.find('.ttbma_group_price_config').slideUp(250);
            parent.find('.ttbm_tour_hotel_setting').slideDown(250);
        } else {
            parent.find('.ttbm_ticket_config').slideDown(250);
            parent.find('.ttbma_group_price').slideDown(250);
            parent.find('.ttbm_tour_hotel_setting').slideUp(250);
        }
        parent.find('[name="ttbm_pricing_type"]').trigger('change');
    });
    //*********Pricing************//
    $(document).on('click', '.ttbm_price_config  .ttbm_add_item', function (e) {
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
                type: 'POST', url: ttbm_ajax_url, data: {
                    "action": "get_ttbm_insert_ticket_type", "form_id": form_id, "post_id": post_id, nonce: ttbm_admin_ajax.nonce,
                }, beforeSend: function () {
                    dLoader(parent);
                }, success: function (data) {
                    if ($.trim(data)) {
                        target.html(data);
                    } else {
                        let fallback = parent.find('>.ttbm_hidden_content .ttbm_hidden_item').html();
                        if (fallback) {
                            target.html(fallback);
                        }
                    }
                    dLoaderRemove(parent);
                }
            });
        }
    });
    //*********Day wise details************//
    $(document).on('click', '.ttbm_add_day_wise_details', function () {
        let $this = $(this);
        let parent = $this.closest('.tabsItem');
        let dt = new Date();
        let time = dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
        $.ajax({
            type: 'POST', url: ttbm_ajax_url, data: {"action": "get_ttbm_add_day_wise_details", "id": time, nonce: ttbm_admin_ajax.nonce,}, beforeSend: function () {
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
    function ttbm_get_location_popup() {
        return $('[data-popup="add_new_location_popup"]').first();
    }

    function ttbm_get_location_popup_form_area() {
        return ttbm_get_location_popup().find('.ttbm_location_form_area');
    }

    function ttbm_show_location_save_error($popup, message) {
        let $error = $popup.find('.ttbm-location-save-error');
        if (!$error.length) {
            return;
        }
        if (message) {
            $error.text(message).show();
        } else {
            $error.hide().text('');
        }
    }

    function ttbm_load_location_popup_form() {
        let $popup = ttbm_get_location_popup();
        let target = $popup.find('.ttbm_location_form_area');
        if (!target.length) {
            return;
        }
        ttbm_show_location_save_error($popup, '');
        $.ajax({
            type: 'POST',
            url: ttbm_ajax_url,
            data: {
                action: 'load_ttbm_location_form'
            },
            beforeSend: function () {
                simpleSpinner(target);
            },
            success: function (data) {
                target.html(data).show();
                simpleSpinnerRemove(target);
            },
            error: function (response) {
                simpleSpinnerRemove(target);
                ttbm_show_location_save_error($popup, 'Could not load the form. Please try again.');
                console.log(response);
            }
        });
    }

    $(document).on('click', '[data-target-popup="add_new_location_popup"]', function (e) {
        e.preventDefault();
        ttbm_load_location_popup_form();
    });

    $(document).on('click', '.ttbm-location-popup .popupClose', function () {
        let $popup = $(this).closest('[data-popup="add_new_location_popup"]');
        ttbm_get_location_popup_form_area().empty();
        ttbm_show_location_save_error($popup, '');
        $popup.find('.ttbm_success_info').removeClass('is-visible').hide();
    });

    $(document).on('click', '.ttbm-location-popup .ttbm_new_location_save_close', function (e) {
        e.preventDefault();
        e.stopPropagation();
        ttbm_get_location_popup_form_area().empty();
        ttbm_show_location_save_error($(this).closest('[data-popup="add_new_location_popup"]'), '');
        $(this).closest('[data-popup]').find('.popupClose').trigger('click');
    });

    $(document).on('click', '.ttbm-location-popup .ttbm_new_location_save', function (e) {
        e.preventDefault();
        e.stopPropagation();
        ttbm_new_location_save($(this));
    });

    function ttbm_new_location_save($this) {
        let $popup = $this.closest('[data-popup="add_new_location_popup"]');
        let parent = $this.closest('.popupMainArea');
        ttbm_show_location_save_error($popup, '');
        parent.find('.ttbm_success_info').removeClass('is-visible').slideUp('fast');
        parent.find('[data-required]').hide();

        let name = $.trim(parent.find('[name="ttbm_new_location_name"]').val() || '');
        let description = parent.find('[name="ttbm_location_description"]').val() || '';
        let address = parent.find('[name="ttbm_location_address"]').val() || '';
        let country = parent.find('[name="ttbm_location_country"]').val() || '';
        let image = parent.find('[name="ttbm_location_image"]').val() || '';
        let isValid = true;

        if (!name) {
            parent.find('[data-required="ttbm_new_location_name"]').show();
            isValid = false;
        }
        if (!image) {
            parent.find('[data-required="ttbm_location_image"]').show();
            isValid = false;
        }
        if (!isValid) {
            ttbm_show_location_save_error($popup, 'Please fill in all required fields.');
            parent.find('.popupBody').scrollTop(0);
            return false;
        }
        if (!parent.find('[name="ttbm_add_new_location_popup"]').val()) {
            ttbm_show_location_save_error($popup, 'Form is not ready. Please close and reopen the popup.');
            return false;
        }

        $.ajax({
            type: 'POST',
            url: ttbm_ajax_url,
            data: {
                action: 'ttbm_new_location_save',
                name: name,
                description: description,
                address: address,
                country: country,
                image: image,
                _wp_nonce: parent.find('[name="ttbm_add_new_location_popup"]').val(),
                nonce: ttbm_admin_ajax.nonce
            },
            beforeSend: function () {
                dLoader(parent);
            },
            success: function () {
                parent.find('[name="ttbm_new_location_name"]').val('');
                parent.find('[name="ttbm_location_description"]').val('');
                parent.find('[name="ttbm_location_address"]').val('');
                parent.find('[name="ttbm_location_country"]').val('');
                parent.find('[name="ttbm_location_image"]').val('');
                parent.find('.ttbm_remove_single_image').trigger('click');
                parent.find('.ttbm_success_info').addClass('is-visible').slideDown('fast');
                ttbm_reload_location();
                dLoaderRemove(parent);

                let $locationSelect = $('#ttbm_location_select, select[name="ttbm_location_name"], select[name="ttbm_hotel_location"]');
                if ($locationSelect.find('option[value="' + name.replace(/"/g, '\\"') + '"]').length === 0) {
                    $locationSelect.append($('<option></option>').attr('value', name).text(name));
                }
                $locationSelect.val(name).trigger('change');
                return true;
            },
            error: function (response) {
                dLoaderRemove(parent);
                ttbm_show_location_save_error($popup, 'Could not save location. Please try again.');
                console.log(response);
            }
        });
        return false;
    }
    function ttbm_reload_location() {
        let ttbm_id = $('[name="post_id"]').val();
        let parent = $('.ttbm_location_select_area');
        $.ajax({
            type: 'POST', url: ttbm_ajax_url, data: {
                "action": "ttbm_reload_location_list", "ttbm_id": ttbm_id, nonce: ttbm_admin_ajax.nonce
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
            type: 'POST', url: ttbm_ajax_url, data: {
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
                url: ttbm_ajax_url,
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
            url: ttbm_ajax_url,
            data: {
                "action": "ttbm_reload_feature_list",
                "ttbm_id": ttbm_id, nonce: ttbm_admin_ajax.nonce
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
    //*******Activity**************//
    $(document).on('click', '.ttbm_settings_activities [data-target-popup]', function () {
        let target = $(this).closest('.ttbm_settings_activities').find('.ttbm_activity_form_area');
        $.ajax({
            type: 'POST', url: ttbm_ajax_url, data: {
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
                type: 'POST', url: ttbm_ajax_url, data: {
                    "action": "ttbm_new_activity_save", "activity_name": activity_name, "activity_description": activity_description, "activity_icon": activity_icon, "_wp_nonce": parent.find('[name="ttbm_add_new_activity_popup"]').val(),
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
                    if ($(this).hasClass('ttbm_new_activity_save_close')) {
                        $this.closest('.popupMainArea').find('.popupClose').trigger('click');
                        setTimeout(function () {
                            ttbm_reload_activity_list();
                        }, 300);
                    }
                    return true;
                }, error: function (response) {
                    console.log(response);
                }
            });
        }
        return false;
    }
    var ttbm_checked_activities = [];
    function updateCheckedActivitiesHolder() {
        var checked = [];
        $('.ttbm_activities_table input[name="ttbm_tour_activities[]"]:checked').each(function () {
            checked.push($(this).val());
        });
        $('#ttbm_checked_activities_holder').val(checked.join(','));
    }
    function updateCheckedTopPicksDealsHolder() {
        var checked = [];
        $('.tabsItem[data-tabs="#ttbm_add_promotional_setting"] input[name="ttbm_top_picks_deals[]"]:checked').each(function () {
            checked.push($(this).val());
        });
        $('#ttbm_checked_top_picks_deals_holder').val(checked.join(','));
    }
    window.updateCheckedActivitiesHolder = updateCheckedActivitiesHolder;
    window.updateCheckedTopPicksDealsHolder = updateCheckedTopPicksDealsHolder;
    /**
     * Copy visible map UI values into the always-submitted hidden fields
     * (#ttbm_*_submit) so lat/lng/address survive collapsed tabs and disabled inputs.
     */
    function ttbmSyncMapLocationFieldsForSubmit() {
        var loc = document.getElementById('ttbm_iframe_location')
            || document.getElementById('ttbm_map_location')
            || document.querySelector('[data-ttbm-map-sync="ttbm_full_location_name"]')
            || document.querySelector('.ttbm-map-location-input:not(#ttbm_hotel_map_location)')
            || document.querySelector('.auto-search-wrapper input');
        var hotelLoc = document.getElementById('ttbm_hotel_map_location')
            || document.querySelector('[data-ttbm-map-sync="ttbm_hotel_map_location"]');
        var lat = document.getElementById('map_latitude')
            || document.querySelector('[data-ttbm-map-sync="ttbm_map_latitude"]');
        var lng = document.getElementById('map_longitude')
            || document.querySelector('[data-ttbm-map-sync="ttbm_map_longitude"]');
        var locSubmit = document.getElementById('ttbm_full_location_name_submit');
        var hotelLocSubmit = document.getElementById('ttbm_hotel_map_location_submit');
        var latSubmit = document.getElementById('ttbm_map_latitude_submit');
        var lngSubmit = document.getElementById('ttbm_map_longitude_submit');
        var titleInput = document.getElementById('ttbm_post_title');
        var titleSubmit = document.getElementById('ttbm_post_title_submit');
        if (titleInput && titleSubmit) {
            titleSubmit.value = titleInput.value || '';
            titleSubmit.disabled = false;
            titleSubmit.removeAttribute('disabled');
            // Keep POST post_title in sync with the visible UI value.
            titleSubmit.setAttribute('name', 'post_title');
            if (titleInput.getAttribute('name') === 'post_title') {
                titleInput.setAttribute('name', 'ttbm_post_title_ui');
            }
        }
        if (loc && locSubmit) {
            locSubmit.value = loc.value || '';
            locSubmit.disabled = false;
            locSubmit.removeAttribute('disabled');
        }
        if (hotelLoc && hotelLocSubmit) {
            hotelLocSubmit.value = hotelLoc.value || '';
            hotelLocSubmit.disabled = false;
            hotelLocSubmit.removeAttribute('disabled');
        }
        if (lat && latSubmit) {
            latSubmit.value = lat.value || '';
            latSubmit.disabled = false;
            latSubmit.removeAttribute('disabled');
        }
        if (lng && lngSubmit) {
            lngSubmit.value = lng.value || '';
            lngSubmit.disabled = false;
            lngSubmit.removeAttribute('disabled');
        }
    }
    window.ttbmSyncMapLocationFieldsForSubmit = ttbmSyncMapLocationFieldsForSubmit;

    /**
     * Persist map fields via AJAX so values survive even when classic form POST misses them.
     * @param {boolean} sync Use synchronous XHR before page unload on Update.
     */
    function ttbmPersistMapLocationToServer(sync) {
        if (typeof ttbm_admin_ajax === 'undefined' || !ttbm_admin_ajax.ajax_url) {
            return false;
        }
        var postIdInput = document.getElementById('post_ID');
        var postId = postIdInput ? String(postIdInput.value || '').trim() : '';
        if (!postId) {
            return false;
        }
        ttbmSyncMapLocationFieldsForSubmit();
        var hotelLoc = document.getElementById('ttbm_hotel_map_location');
        var loc = hotelLoc
            || document.getElementById('ttbm_iframe_location')
            || document.getElementById('ttbm_map_location')
            || document.getElementById('ttbm_full_location_name_submit');
        var lat = document.getElementById('map_latitude') || document.getElementById('ttbm_map_latitude_submit');
        var lng = document.getElementById('map_longitude') || document.getElementById('ttbm_map_longitude_submit');
        var address = loc ? String(loc.value || '').trim() : '';
        var latitude = lat ? String(lat.value || '').trim() : '';
        var longitude = lng ? String(lng.value || '').trim() : '';
        if (!address && !latitude && !longitude) {
            return false;
        }
        var body = new URLSearchParams();
        body.set('action', 'ttbm_save_map_location');
        body.set('nonce', ttbm_admin_ajax.nonce || '');
        body.set('post_id', postId);
        body.set('address', address);
        body.set('latitude', latitude);
        body.set('longitude', longitude);
        if (sync) {
            try {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', ttbm_admin_ajax.ajax_url, false);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                xhr.send(body.toString());
                return xhr.status >= 200 && xhr.status < 300;
            } catch (err) {
                return false;
            }
        }
        if (navigator.sendBeacon) {
            try {
                var blob = new Blob([body.toString()], {type: 'application/x-www-form-urlencoded; charset=UTF-8'});
                navigator.sendBeacon(ttbm_admin_ajax.ajax_url, blob);
            } catch (err2) { /* fall through */ }
        }
        fetch(ttbm_admin_ajax.ajax_url, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            body: body.toString(),
            credentials: 'same-origin',
            keepalive: true,
        }).catch(function () { /* ignore */ });
        return true;
    }
    window.ttbmPersistMapLocationToServer = ttbmPersistMapLocationToServer;

    /**
     * Persist hotel/tour title via AJAX before classic Update.
     * @param {boolean} sync Synchronous XHR when unloading.
     */
    function ttbmPersistPostTitleToServer(sync) {
        if (typeof ttbm_admin_ajax === 'undefined' || !ttbm_admin_ajax.ajax_url) {
            return false;
        }
        var postIdInput = document.getElementById('post_ID');
        var postId = postIdInput ? String(postIdInput.value || '').trim() : '';
        var titleInput = document.getElementById('ttbm_post_title');
        var title = titleInput ? String(titleInput.value || '').trim() : '';
        if (!postId || !title) {
            return false;
        }
        ttbmSyncMapLocationFieldsForSubmit();
        var body = new URLSearchParams();
        body.set('action', 'ttbm_save_post_title');
        body.set('nonce', ttbm_admin_ajax.nonce || '');
        body.set('post_id', postId);
        body.set('title', title);
        if (sync) {
            try {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', ttbm_admin_ajax.ajax_url, false);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                xhr.send(body.toString());
                return xhr.status >= 200 && xhr.status < 300;
            } catch (err) {
                return false;
            }
        }
        fetch(ttbm_admin_ajax.ajax_url, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            body: body.toString(),
            credentials: 'same-origin',
            keepalive: true,
        }).catch(function () { /* ignore */ });
        return true;
    }
    window.ttbmPersistPostTitleToServer = ttbmPersistPostTitleToServer;

    window.ttbmPrepareTourSettingsFormForSubmit = function () {
        var $panel = $('#ttbm_meta_box_panel');
        if ($panel.length) {
            $panel.find('input, select, textarea').each(function () {
                var $field = $(this);
                if ($field.closest('.ttbm_hidden_content').length) {
                    return;
                }
                var type = ($field.attr('type') || '').toLowerCase();
                if (type === 'button' || type === 'submit' || $field.is('.ttbm-deleting')) {
                    return;
                }
                $field.prop('disabled', false).removeAttr('disabled');
            });
            $panel.find('[data-collapse="#ttbm_display_map"], [data-collapse="#ttbm_display_location"], [data-collapse="#ttbm_display_hotel_map"]').find('input, select, textarea').prop('disabled', false).removeAttr('disabled');
        }
        $('#ttbm_full_location_name_submit, #ttbm_hotel_map_location_submit, #ttbm_map_latitude_submit, #ttbm_map_longitude_submit, #ttbm_post_title_submit')
            .prop('disabled', false)
            .removeAttr('disabled');
        ttbmSyncMapLocationFieldsForSubmit();
        ttbmPersistPostTitleToServer(true);
        ttbmPersistMapLocationToServer(true);
        if (typeof ttbm_sync_visible_dates_to_hidden === 'function') {
            ttbm_sync_visible_dates_to_hidden();
        }
        updateCheckedTopPicksDealsHolder();
        updateCheckedActivitiesHolder();
        if (typeof window.ttbmSyncTicketHiddenText === 'function') {
            window.ttbmSyncTicketHiddenText();
        }
    };
    $(document).on(
        'input change',
        '#ttbm_iframe_location, #ttbm_map_location, #ttbm_hotel_map_location, #map_latitude, #map_longitude, #ttbm_post_title, .ttbm-map-location-input, .ttbm-map-coord-input',
        function () {
            ttbmSyncMapLocationFieldsForSubmit();
        }
    );
    // Sync + AJAX persist before WordPress serializes the form.
    $(document).on('mousedown click', '#publish, #save-post, .editor-post-publish-button, .editor-post-publish-button__button, .editor-post-save-draft', function () {
        window.ttbmPrepareTourSettingsFormForSubmit();
    });
    $(document).on('submit', 'form#post', function () {
        window.ttbmPrepareTourSettingsFormForSubmit();
    });
    $(document).on('change', '.ttbm_activities_table input[name="ttbm_tour_activities[]"]', function () {
        updateCheckedActivitiesHolder();
    });
    $(document).on('change', '.tabsItem[data-tabs="#ttbm_add_promotional_setting"] input[name="ttbm_top_picks_deals[]"]', function () {
        updateCheckedTopPicksDealsHolder();
    });
    function ttbm_reload_activity_list() {
        var ttbm_id = $('[name="post_id"]').val();
        var parent = $('.ttbm_activities_table');
        $.ajax({
            type: 'POST',
            url: ttbm_ajax_url,
            data: {
                "action": "ttbm_reload_activity_list",
                "ttbm_id": ttbm_id, nonce: ttbm_admin_ajax.nonce
            },
            beforeSend: function () {
                dLoader(parent);
            },
            success: function (data) {
                parent.empty().append(data).promise().done(function () {
                    parent.find('.ttbm_select2').select2({});
                    // Restore checked state from hidden field
                    var checked = $('#ttbm_checked_activities_holder').val().split(',');
                    parent.find('input[name="ttbm_tour_activities[]"]').each(function () {
                        if (checked.includes($(this).val())) {
                            $(this).prop('checked', true);
                        }
                    });
                    updateCheckedActivitiesHolder();
                });
                return true;
            },
            error: function (response) {
                console.log(response);
            }
        });
    }
    //*****Place you see****************//
    function ttbm_get_place_popup() {
        return $('[data-popup="add_new_place_popup"]');
    }
    function ttbm_get_place_popup_form_area() {
        return ttbm_get_place_popup().find('.ttbm_place_you_see_form_area');
    }
    function ttbm_parse_place_save_response(response) {
        if (response && typeof response === 'object') {
            return response;
        }
        if (typeof response === 'string' && response.trim()) {
            try {
                return JSON.parse(response);
            } catch (error) {
                return null;
            }
        }
        return null;
    }
    function ttbm_reinit_place_select($select) {
        if (!$select || !$select.length || !$.fn.select2) {
            return;
        }
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }
        $select.removeClass('add_ttbm_select2').addClass('ttbm_select2');
        $select.select2({});
    }
    function ttbm_refresh_place_dropdowns(selectedPlaceId, placeName) {
        let $area = $('.ttbm_settings_place_you_see .ttbm_place_you_see_table');
        let $targetSelect = $('.ttbm_settings_place_you_see').data('ttbm-place-target-select');
        if (typeof ttbm_admin_ajax === 'undefined') {
            return;
        }
        $.ajax({
            type: 'POST',
            url: ttbm_ajax_url,
            data: {
                action: 'ttbm_reload_place_dropdown_options',
                nonce: ttbm_admin_ajax.nonce
            },
            beforeSend: function () {
                if ($area.length) {
                    dLoader($area);
                }
            },
            success: function (response) {
                let parsed = ttbm_parse_place_save_response(response);
                let optionsHtml = parsed && parsed.success && parsed.data ? parsed.data.options : '';
                if (!optionsHtml) {
                    dLoaderRemove($area);
                    return;
                }
                let $selects = $('.ttbm_settings_place_you_see').find('select[name="ttbm_city_place_id[]"]');
                $selects.each(function () {
                    let $select = $(this);
                    let preserveVal = '';
                    if ($targetSelect && $targetSelect.length && $select.is($targetSelect) && selectedPlaceId) {
                        preserveVal = String(selectedPlaceId);
                    } else if ($select.val()) {
                        preserveVal = String($select.val());
                    }
                    $select.html(optionsHtml);
                    if (preserveVal && $select.find('option[value="' + preserveVal + '"]').length) {
                        $select.val(preserveVal);
                    }
                    ttbm_reinit_place_select($select);
                    if (preserveVal) {
                        $select.trigger('change');
                    }
                });
                if ($targetSelect && $targetSelect.length && selectedPlaceId) {
                    let $row = $targetSelect.closest('tr');
                    if (placeName) {
                        $row.find('input[name="ttbm_place_label[]"]').val(placeName);
                    }
                }
                dLoaderRemove($area);
            },
            error: function (response) {
                dLoaderRemove($area);
                console.log(response);
            }
        });
    }
    function ttbm_show_place_save_error($popup, message) {
        let $error = $popup.find('.ttbm-place-save-error');
        if (!$error.length) {
            return;
        }
        if (message) {
            $error.text(message).show();
        } else {
            $error.hide().text('');
        }
    }
    function ttbm_load_place_popup_form() {
        let $popup = ttbm_get_place_popup();
        let target = $popup.find('.ttbm_place_you_see_form_area');
        if (!target.length) {
            return;
        }
        ttbm_show_place_save_error($popup, '');
        $.ajax({
            type: 'POST',
            url: ttbm_ajax_url,
            data: {
                action: 'load_ttbm_place_you_see_form',
                nonce: (typeof ttbm_admin_ajax !== 'undefined') ? ttbm_admin_ajax.nonce : ''
            },
            beforeSend: function () {
                dLoader(target);
            },
            success: function (data) {
                target.html(data).show();
                dLoaderRemove(target);
            },
            error: function (response) {
                dLoaderRemove(target);
                ttbm_show_place_save_error($popup, 'Could not load the form. Please try again.');
                console.log(response);
            }
        });
    }
    $(document).on('click', '.ttbm_settings_place_you_see [data-target-popup="add_new_place_popup"]', function (e) {
        e.preventDefault();
        $('.ttbm_settings_place_you_see').data('ttbm-place-target-select', $(this).closest('.ttbm-place-select-wrap').find('select'));
        ttbm_load_place_popup_form();
    });
    $(document).on('click', '.ttbm-place-popup .popupClose', function () {
        let $popup = $(this).closest('[data-popup="add_new_place_popup"]');
        ttbm_get_place_popup_form_area().empty();
        ttbm_show_place_save_error($popup, '');
        $popup.find('.ttbm_success_info').removeClass('is-visible').hide();
    });
    $(document).on('click', '.ttbm-place-popup .ttbm_new_place_save_close', function (e) {
        e.preventDefault();
        e.stopPropagation();
        ttbm_get_place_popup_form_area().empty();
        ttbm_show_place_save_error($(this).closest('[data-popup="add_new_place_popup"]'), '');
        $(this).closest('[data-popup]').find('.popupClose').trigger('click');
    });
    $(document).on('click', '.ttbm-place-popup .ttbm_new_place_save', function (e) {
        e.preventDefault();
        e.stopPropagation();
        ttbm_new_place_save($(this));
    });
    function ttbm_new_place_save($this) {
        let $popup = $this.closest('[data-popup="add_new_place_popup"]');
        let parent = $this.closest('.popupMainArea');
        ttbm_show_place_save_error($popup, '');
        parent.find('.ttbm_success_info').removeClass('is-visible').hide();
        parent.find('[data-required]').hide();
        let name = $.trim(parent.find('[name="ttbm_place_name"]').val() || '');
        let description = parent.find('[name="ttbm_place_description"]').val() || '';
        let image = parent.find('[name="ttbm_place_image"]').val() || '';
        let isValid = true;
        if (!name) {
            parent.find('[data-required="ttbm_place_name"]').show();
            isValid = false;
        }
        if (!image) {
            parent.find('[data-required="ttbm_place_image"]').show();
            isValid = false;
        }
        if (!isValid) {
            ttbm_show_place_save_error($popup, 'Please fill in all required fields.');
            parent.find('.popupBody').scrollTop(0);
            return false;
        }
        if (!parent.find('[name="ttbm_add_new_place_popup"]').val()) {
            ttbm_show_place_save_error($popup, 'Form is not ready. Please close and reopen the popup.');
            return false;
        }
        $.ajax({
            type: 'POST',
            url: ttbm_ajax_url,
            data: {
                action: 'ttbm_new_place_save',
                name: name,
                description: description,
                image: image,
                _wp_nonce: parent.find('[name="ttbm_add_new_place_popup"]').val()
            },
            beforeSend: function () {
                dLoader(parent);
            },
            success: function (response) {
                dLoaderRemove(parent);
                let parsed = ttbm_parse_place_save_response(response);
                if (!parsed || !parsed.success) {
                    let message = (parsed && parsed.data && parsed.data.message) ? parsed.data.message : 'Could not save place. Please try again.';
                    ttbm_show_place_save_error($popup, message);
                    return;
                }
                let postId = parsed.data ? parsed.data.post_id : 0;
                let placeName = parsed.data ? parsed.data.name : name;
                parent.find('[name="ttbm_place_name"]').val('');
                parent.find('[name="ttbm_place_description"]').val('');
                parent.find('[name="ttbm_place_image"]').val('');
                parent.find('.ttbm_image_remove, .ttbm_remove_single_image').trigger('click');
                parent.find('.ttbm_success_info').addClass('is-visible').show();
                ttbm_refresh_place_dropdowns(postId, placeName);
                return true;
            },
            error: function (response) {
                dLoaderRemove(parent);
                ttbm_show_place_save_error($popup, 'Could not save place. Please try again.');
                console.log(response);
            }
        });
        return false;
    }
    $(document).on('change', '.ttbm_settings_place_you_see select[name="ttbm_city_place_id[]"]', function () {
        let $select = $(this);
        let placeId = $select.val();
        if (!placeId) {
            return;
        }
        let placeName = $select.find('option:selected').text().trim();
        $select.closest('tr').find('input[name="ttbm_place_label[]"]').val(placeName);
    });
}(jQuery));
//====================//
(function ($) {
    "use strict";
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
        var faqContentId = parent.find('.faq-content').html().trim();
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
    $(document).on("ready", function (e) {
        $(".ttbm-faq-items").sortable({
            update: function (event, ui) {
                event.preventDefault();
                var sortedIDs = $(this).sortable("toArray", {attribute: "data-id"});
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
    // ================daywise sidebar modal=================//
    $(document).on('click', '.ttbm-daywise-item-new', function (e) {
        $('#ttbm-daywise-msg').html('');
        $('.ttbm_daywise_save_buttons').show();
        $('.ttbm_daywise_update_buttons').hide();
        empty_daywise_form();
    });
    function close_sidebar_modal(e) {
        e.preventDefault();
        e.stopPropagation();
        $('.ttbm-modal-container').removeClass('open');
    }
    $(document).on('click', '.ttbm-daywise-item-edit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#ttbm-daywise-msg').html('');
        $('.ttbm_daywise_save_buttons').hide();
        $('.ttbm_daywise_update_buttons').show();
        var itemId = $(this).closest('.ttbm-daywise-item').data('id');
        var parent = $(this).closest('.ttbm-daywise-item');
        var headerText = parent.find('.daywise-header p').text().trim();
        var daywiseContentId = parent.find('.daywise-content').html().trim();
        var editorId = 'ttbm_day_content';
        $('input[name="ttbm_day_title"]').val(headerText);
        $('input[name="ttbm_daywise_item_id"]').val(itemId);
        if (tinymce.get(editorId)) {
            tinymce.get(editorId).setContent(daywiseContentId);
        } else {
            $('#' + editorId).val(daywiseContentId);
        }
    });
    $(document).on('click', '.ttbm-daywise-item-delete', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var itemId = $(this).closest('.ttbm-daywise-item').data('id');
        var isConfirmed = confirm('Are you sure you want to delete this row?');
        if (isConfirmed) {
            delete_daywise_item(itemId);
        } else {
            console.log('Deletion canceled.' + itemId);
        }
    });
    function empty_daywise_form() {
        $('input[name="ttbm_day_title"]').val('');
        tinyMCE.get('ttbm_day_content').setContent('');
        $('input[name="ttbm_daywise_item_id"]').val('');
    }
    $(document).on('click', '#ttbm_daywise_update', function (e) {
        e.preventDefault();
        update_daywise();
    });
    $(document).on('click', '#ttbm_daywise_save', function (e) {
        e.preventDefault();
        save_daywise();
    });
    $(document).on('click', '#ttbm_daywise_save_close', function (e) {
        e.preventDefault();
        save_daywise();
        close_sidebar_modal(e);
    });
    function update_daywise() {
        var title = $('input[name="ttbm_day_title"]');
        var content = tinyMCE.get('ttbm_day_content').getContent();
        var postID = $('input[name="ttbm_post_id"]');
        var itemId = $('input[name="ttbm_daywise_item_id"]');
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_daywise_data_update',
                ttbm_day_title: title.val(),
                ttbm_day_content: content,
                ttbm_daywise_postID: postID.val(),
                ttbm_daywise_itemID: itemId.val(),
                nonce: ttbm_admin_ajax.nonce
            },
            success: function (response) {
                $('#ttbm-daywise-msg').html(response.data.message);
                $('.ttbm-daywise-items').html('');
                $('.ttbm-daywise-items').append(response.data.html);
                setTimeout(function () {
                    $('.ttbm-modal-container').removeClass('open');
                    empty_daywise_form();
                }, 1000);
            },
            error: function (error) {
                console.log('Error:', error);
            }
        });
    }
    function save_daywise() {
        var title = $('input[name="ttbm_day_title"]');
        var content = tinyMCE.get('ttbm_day_content').getContent();
        var postID = $('input[name="ttbm_post_id"]');
        console.log(ttbm_admin_ajax.ajax_url);
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_daywise_data_save',
                ttbm_day_title: title.val(),
                ttbm_day_content: content,
                ttbm_daywise_postID: postID.val(),
                nonce: ttbm_admin_ajax.nonce
            },
            success: function (response) {
                $('#ttbm-daywise-msg').html(response.data.message);
                $('.ttbm-daywise-items').html('');
                $('.ttbm-daywise-items').append(response.data.html);
                empty_daywise_form();
            },
            error: function (error) {
                console.log('Error:', error);
            }
        });
    }
    function delete_daywise_item(itemId) {
        var postID = $('input[name="ttbm_post_id"]');
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_daywise_delete_item',
                ttbm_daywise_postID: postID.val(),
                itemId: itemId,
                nonce: ttbm_admin_ajax.nonce
            },
            success: function (response) {
                $('.ttbm-daywise-items').html('');
                $('.ttbm-daywise-items').append(response.data.html);
            },
            error: function (error) {
                console.log('Error:', error);
            }
        });
    }
    // daywise sorting
    $(document).on("ready", function (e) {
        $(".ttbm-daywise-items").sortable({
            update: function (event, ui) {
                event.preventDefault();
                var sortedIDs = $(this).sortable("toArray", {attribute: "data-id"});
                $.ajax({
                    url: ttbm_admin_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'ttbm_sort_daywise',
                        postID: $('input[name="ttbm_post_id"]').val(),
                        sortedIDs: sortedIDs,
                        nonce: ttbm_admin_ajax.nonce
                    },
                    success: function (response) {
                        $('.ttbm-daywise-items').html('');
                        $('.ttbm-daywise-items').append(response.data.html);
                    },
                    error: function (error) {
                        console.log('Error:', error);
                    }
                })
            }
        });
    });
    //=================== tour lists load more===========================
    $(document).on('click', '#ttbm-load-more', function (e) {
        e.preventDefault();
        const button = $(this);
        const paged = parseInt(button.attr('data-paged'));
        const postPerPage = button.data('posts-per-page');
        const nonce = button.data('nonce');
        const activeFilter = $('.ttbm_travel_filter_item.ttbm_filter_btn_active_bg_color').attr('data-filter-item') || 'all';
        const searchTerm = $('#ttbm-tour-search').val() || '';
        let load_more_count = $(this).children('.ttbm_load_more_remaining_travel').text().trim();
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_load_more',
                paged: paged,
                post_per_page: postPerPage,
                nonce: nonce,
                selected_filter: activeFilter,
                search_term: searchTerm
            },
            beforeSend: function () {
                button.text('Loading...');
            },
            success: function (response) {
                if (response.success && response.data.html) {
                    $('.ttbm-tour-list').append(response.data.html);
                    if (paged >= response.data.max_pages) {
                        button.hide();
                    } else {
                        let foundPosts = parseInt(response.data.found_posts || 0, 10);
                        let loadedPosts = $('.ttbm-tour-list .ttbm-tour-card').length;
                        let remainig_travel = Math.max(foundPosts - loadedPosts, 0);
                        button.attr('data-paged', paged + 1).show().text('Load More');
                        let remaining_span = '(<span class="ttbm_load_more_remaining_travel">' + remainig_travel + '</span>)';
                        button.append(remaining_span);
                    }
                } else {
                    button.hide();
                }
            }
        });
    });
    //=================== tour lists search===========================
    $(document).on('input', '#ttbm-tour-search', function () {
        var search = $(this).val();
        var nonce = $(this).data('nonce');
        const activeFilter = $('.ttbm_travel_filter_item.ttbm_filter_btn_active_bg_color').attr('data-filter-item') || 'all';
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_search_tours',
                search_term: search,
                selected_filter: activeFilter,
                nonce: ttbm_admin_ajax.nonce
            },
            beforeSend: function () {
                if (typeof window.ttbmShowTourPackageSkeleton === 'function') {
                    window.ttbmShowTourPackageSkeleton('.ttbm-tour-list', 4);
                } else if (typeof window.ttbmTravelTourPackageSkeletonHtml === 'function') {
                    $('.ttbm-tour-list').html(window.ttbmTravelTourPackageSkeletonHtml(4));
                }
            },
            success: function (response) {
                if (response.success && response.data.html) {
                    $('.ttbm-tour-list').html('');
                    $('.ttbm-tour-list').append(response.data.html);

                    const $loadMoreButton = $('#ttbm-load-more');
                    if ($loadMoreButton.length) {
                        if (response.data.max_pages > 1) {
                            $loadMoreButton.attr('data-paged', 2).show();
                        } else {
                            $loadMoreButton.hide();
                        }
                    } else {
                        // No button found in DOM, nothing to update.
                    }
                } else {
                    $('.ttbm-tour-list').html('<p>No tours found.</p>');
                    $('#ttbm-load-more').hide();
                }
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
                    $(this).addClass('ttbm_search_on').removeClass('ttbm_search_off');
                } else {
                    $(this).addClass('ttbm_search_off').removeClass('ttbm_search_on');
                }
            } else {
                $(this).addClass('ttbm_search_on').removeClass('ttbm_search_off');
            }
        });
    }
    // FIXED: Professional delete functionality with proper error handling - 2025-01-27 by Shahnur Alam
    $(document).on('click', '.ttbm-tour-card .ttbm_trash_post', function (e) {
        e.preventDefault();
        
        let $this = $(this);
        let alert_text = $this.data('alert');
        let post_id = $this.data('post-id');
        let nonce = $this.data('nonce');
        let target = $this.closest('.ttbm-tour-card');
        let post_title = target.find('h3 a').text().trim();
        
        // Step 1: Validate required data
        if (!post_id || !nonce) {
            alert('Error: Missing required data. Please refresh the page and try again.');
            return false;
        }
        
        // Step 2: Show professional confirmation dialog
        let confirmMessage = alert_text + '\n\n' +
            'This action will move the tour to trash. You can restore it later from the trash if needed.\n\n' +
            'Click OK to continue or Cancel to abort.';
            
        if (!confirm(confirmMessage)) {
            return false;
        }
        
        // Step 3: Disable button to prevent double-clicks
        $this.prop('disabled', true).addClass('ttbm-deleting');
        
        // Step 4: Send AJAX request
        $.ajax({
            type: 'POST',
            url: ttbm_ajax_url,
            data: {
                'action': 'ttbm_trash_post',
                'post_id': post_id,
                'nonce': nonce
            },
            beforeSend: function () {
                // Show loading state
                dLoader(target);
                $this.find('i').removeClass('fa-trash').addClass('fa-spinner fa-spin');
            },
            success: function (response) {
                dLoaderRemove(target);
                
                if (response.success) {
                    // Step 5: Show success message and remove card with animation
                    target.addClass('ttbm-deleting-success');
                    
                    // Show success notification
                    if (typeof response.data.message !== 'undefined') {
                        // Create a temporary success message
                        let successMsg = $('<div class="ttbm-success-message">' + response.data.message + '</div>');
                        target.prepend(successMsg);
                        
                        setTimeout(function() {
                            successMsg.fadeOut(300);
                        }, 2000);
                    }
                    
                    // Animate card removal
                    setTimeout(function() {
                        target.fadeOut(500, function() {
                            $(this).remove();
                            
                            // Check if no more tours exist
                            if ($('.ttbm-tour-card').length === 0) {
                                $('.ttbm-tour-list').html('<p>' + 'No tours found.' + '</p>');
                            }
                        });
                    }, 1000);
                    
                } else {
                    // Step 6: Handle error response
                    let errorMessage = 'An error occurred while deleting the tour.';
                    if (typeof response.data.message !== 'undefined') {
                        errorMessage = response.data.message;
                    }
                    
                    alert('Error: ' + errorMessage);
                    
                    // Reset button state
                    $this.prop('disabled', false).removeClass('ttbm-deleting');
                    $this.find('i').removeClass('fa-spinner fa-spin').addClass('fa-trash');
                }
            },
            error: function (xhr, status, error) {
                dLoaderRemove(target);
                
                // Step 7: Handle AJAX error
                console.error('AJAX Error:', error);
                alert('Network error occurred. Please check your connection and try again.');
                
                // Reset button state
                $this.prop('disabled', false).removeClass('ttbm-deleting');
                $this.find('i').removeClass('fa-spinner fa-spin').addClass('fa-trash');
            }
        });
        
        return false;
    });
    // ================Get Enquiry=================//
    $(document).ready(function ($) {
        // Only run tab logic on our plugin's settings page
        if ($('#ttbm-settings-page').length) {
            const tabs = $('#ttbm-settings-page .nav-tab');
            const contents = $('#ttbm-settings-page .tab-content');
            tabs.on('click', function (e) {
                e.preventDefault();
                tabs.removeClass('nav-tab-active');
                contents.hide();
                $(this).addClass('nav-tab-active');
                const target = $(this).attr('href');
                if (target && target.startsWith('#')) {
                    $(target).show();
                }
            });
        }
    });
    $(document).on('click', '.ttbm-delete-enquiry', function (e) {
        e.preventDefault();
        let isConfirmed = confirm('Are you sure you want to delete this row?');
        if (isConfirmed) {
            let row = $(this).closest('tr');
            let enquiryId = $(this).data('id');
            $.ajax({
                url: ttbm_admin_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'ttbm_delete_enquiry',
                    enquiry_id: enquiryId,
                    nonce: ttbm_admin_ajax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        row.remove();
                    } else {
                        alert('Failed to delete the enquiry. Please try again.');
                    }
                },
                error: function (error) {
                    console.log('Error:', error);
                    alert('An error occurred while deleting the enquiry.');
                }
            });
        }
    });
    $(document).on('click', '.ttbm-reply-enquiry', function (e) {
        e.preventDefault();
        let enquiryId = $(this).data('id');
        var row = $(this).closest('.ttbm-enquiry-list');
        var subject = row.find('td:eq(0)').text().trim();
        var name = row.find('td:eq(1)').text().trim();
        var email = row.find('td:eq(2)').text().trim();
        $('#ttbm-post-id').val(enquiryId);
        $('#ttbm-reply-to').val(email);
        $('#ttbm-reply-subject').val(subject);
    });
    $('#ttbm-reply-enquiry-form').on('submit', function (e) {
        e.preventDefault();
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('ttbm-reply-message')) {
            tinyMCE.get('ttbm-reply-message').save();
        }
        var formData = $(this).serialize();
        $.ajax({
            type: "POST",
            url: ttbm_admin_ajax.ajax_url,
            data: {
                action: 'ttbm_reply_enquiry',
                nonce: ttbm_admin_ajax.nonce,
                data: formData
            },
            success: function (response) {
                console.log(response);
                if (response.success) {
                    $('.reply-ajax-response').html(response.data.message).css('color', 'green');
                } else {
                    $('.reply-ajax-response').html(response.data.message).css('color', 'red');
                }
            },
            error: function (error) {
                console.log('Error:', error);
                alert('An error occurred while loading the enquiry details.');
            }
        });
    });
    // ==========view enquiry=============
    $(document).on('click', '.ttbm-view-enquiry', function (e) {
        e.preventDefault();
        let enquiryId = $(this).data('id');
        $.ajax({
            url: ttbm_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ttbm_view_enquiry',
                enquiry_id: enquiryId,
                nonce: ttbm_admin_ajax.nonce
            },
            success: function (response) {
                if (response.success) {
                    $('.ttbm-view-enquiry-response').html(response.data.html);
                } else {
                    alert('Failed to load the enquiry details. Please try again.');
                }
            },
            error: function (error) {
                console.log('Error:', error);
                alert('An error occurred while loading the enquiry details.');
            }
        });
    });
    $(document).on('click', '#ttbm-enquiry-modal .modal-close', function () {
        $('#ttbm-enquiry-modal').removeClass('open');
    });
}(jQuery));
// =================Open Street map location search==================
(function ($) {
    let osmMap, osmMarker, osmGeocodeTimer, lastGeocodedAddress = '';
    const nominatimHeaders = {'Accept-Language': document.documentElement.lang || 'en'};

    // Autocomplete's own keydown handler on the input fires (and calls
    // preventDefault + closes its dropdown) before our document-delegated
    // Enter fallback runs, so aria-expanded is already "false" by then.
    // This flag lets a real selection short-circuit our raw-text fallback
    // so a slower manual geocode never overwrites the instant, precise
    // coordinates a selection already applied.
    let ttbmMapSelectionHandled = false;
    function ttbmMarkMapSelectionHandled() {
        ttbmMapSelectionHandled = true;
        setTimeout(function () {
            ttbmMapSelectionHandled = false;
        }, 400);
    }

    function updateOSMPosition(lat, lng, zoom) {
        if (!osmMap || !osmMarker || isNaN(lat) || isNaN(lng)) {
            return;
        }
        osmMarker.setLatLng([lat, lng]);
        osmMap.setView([lat, lng], zoom || 12);
        const latEl = document.getElementById('map_latitude');
        const lngEl = document.getElementById('map_longitude');
        if (latEl) {
            latEl.value = lat;
        }
        if (lngEl) {
            lngEl.value = lng;
        }
        if (typeof ttbmSyncMapLocationFieldsForSubmit === 'function') {
            ttbmSyncMapLocationFieldsForSubmit();
        }
    }

    function applyOSMGeoFeature(feature) {
        if (!feature || !feature.geometry || !feature.geometry.coordinates) {
            return;
        }
        ttbmMarkMapSelectionHandled();
        const [lng, lat] = feature.geometry.coordinates;
        updateOSMPosition(lat, lng);
        const displayName = feature.properties && feature.properties.display_name;
        const input = document.getElementById('ttbm_osmap_location');
        if (displayName && input) {
            input.value = displayName;
            lastGeocodedAddress = displayName;
        }
    }

    function normalizeMapSearchQuery(address) {
        const q = String(address || '').trim();
        if (!q) {
            return '';
        }
        // Google Maps embed accepts "coxesbazar seabeach"; Nominatim/Photon often do not.
        const compact = q.toLowerCase().replace(/[^a-z0-9]/g, '');
        // Only rewrite short misspellings — never overwrite a full street address.
        if (/^(coxes?|coxs)bazar/.test(compact) && compact.length <= 28 && !/cultural|center|hotel|road|zone|district|museum/.test(compact)) {
            if (compact.indexOf('sea') !== -1 || compact.indexOf('beach') !== -1) {
                return "Cox's Bazar Sea Beach, Bangladesh";
            }
            return "Cox's Bazar, Bangladesh";
        }
        return q.replace(/\bseabeach\b/gi, 'sea beach');
    }

    function geocodeQueryCandidates(address) {
        const raw = String(address || '').trim();
        const list = [];
        const normalized = normalizeMapSearchQuery(raw);
        if (normalized) {
            list.push(normalized);
        }
        if (raw && list.indexOf(raw) === -1) {
            list.push(raw);
        }
        const spaced = raw.replace(/\bseabeach\b/gi, 'sea beach');
        if (spaced && list.indexOf(spaced) === -1) {
            list.push(spaced);
        }
        return list;
    }

    function photonFeatureToMatch(feature) {
        if (!feature) {
            return null;
        }
        const props = feature.properties || {};
        const parts = [props.name, props.city || props.county, props.state, props.country].filter(Boolean);
        const display_name = parts.length ? parts.join(', ') : (props.name || '');
        return {
            type: 'Feature',
            geometry: feature.geometry,
            properties: Object.assign({}, props, {display_name: display_name}),
        };
    }

    function geocodeViaPhotonBrowser(query) {
        const api = 'https://photon.komoot.io/api/?q=' + encodeURIComponent(query) + '&limit=1';
        return fetch(api, {headers: nominatimHeaders})
            .then((response) => response.json())
            .then((data) => {
                const feature = data && data.features && data.features[0];
                if (!feature || !feature.geometry || !Array.isArray(feature.geometry.coordinates)) {
                    return null;
                }
                const lon = feature.geometry.coordinates[0];
                const lat = feature.geometry.coordinates[1];
                if (lat == null || lon == null) {
                    return null;
                }
                const match = photonFeatureToMatch(feature);
                return {
                    lat: String(lat),
                    lon: String(lon),
                    display_name: (match && match.properties && match.properties.display_name) || query,
                };
            })
            .catch(() => null);
    }

    function geocodeViaNominatimBrowser(query) {
        const api = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query);
        return fetch(api, {headers: nominatimHeaders})
            .then((response) => response.json())
            .then((data) => (data && data.length > 0 ? data[0] : null))
            .catch(() => null);
    }

    function searchMapPlaceFeatures(currentValue) {
        const query = normalizeMapSearchQuery(currentValue) || String(currentValue || '').trim();
        if (!query) {
            return Promise.resolve([]);
        }
        const nominatimApi = 'https://nominatim.openstreetmap.org/search?format=geojson&limit=5&q=' + encodeURIComponent(query);
        return fetch(nominatimApi, {headers: nominatimHeaders})
            .then((response) => response.json())
            .then((data) => {
                const features = (data && data.features) || [];
                if (features.length) {
                    return features;
                }
                // Nominatim often returns nothing for "coxesbazar"; Photon is more forgiving.
                const photonApi = 'https://photon.komoot.io/api/?q=' + encodeURIComponent(query) + '&limit=5';
                return fetch(photonApi, {headers: nominatimHeaders})
                    .then((response) => response.json())
                    .then((photonData) => ((photonData && photonData.features) || []).map(photonFeatureToMatch).filter(Boolean))
                    .catch(() => []);
            })
            .catch(() => {
                const photonApi = 'https://photon.komoot.io/api/?q=' + encodeURIComponent(query) + '&limit=5';
                return fetch(photonApi, {headers: nominatimHeaders})
                    .then((response) => response.json())
                    .then((photonData) => ((photonData && photonData.features) || []).map(photonFeatureToMatch).filter(Boolean))
                    .catch(() => []);
            });
    }

    function geocodeOSMAddress(address) {
        if (!address || address.trim().length < 2) {
            return Promise.resolve(null);
        }
        const candidates = geocodeQueryCandidates(address);
        const tryBrowserForQuery = function (query) {
            return geocodeViaNominatimBrowser(query).then((nominatimResult) => {
                if (nominatimResult) {
                    return nominatimResult;
                }
                return geocodeViaPhotonBrowser(query);
            });
        };
        const tryBrowserFallbacks = function () {
            let chain = Promise.resolve(null);
            candidates.forEach(function (query) {
                chain = chain.then(function (prev) {
                    if (prev) {
                        return prev;
                    }
                    return tryBrowserForQuery(query);
                });
            });
            return chain;
        };
        // Prefer server-side geocode (Photon + Nominatim + spelling normalize).
        if (typeof ttbm_admin_ajax !== 'undefined' && ttbm_admin_ajax.ajax_url) {
            const preferred = candidates[0] || address.trim();
            const body = new URLSearchParams();
            body.set('action', 'ttbm_geocode_address');
            body.set('nonce', ttbm_admin_ajax.nonce || '');
            body.set('address', preferred);
            return fetch(ttbm_admin_ajax.ajax_url, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                body: body.toString(),
                credentials: 'same-origin',
            })
                .then((response) => response.json())
                .then((payload) => {
                    if (payload && payload.success && payload.data) {
                        return {
                            lat: payload.data.lat,
                            lon: payload.data.lon,
                            display_name: payload.data.display_name || preferred,
                        };
                    }
                    return null;
                })
                .catch(() => null)
                .then((serverResult) => (serverResult ? serverResult : tryBrowserFallbacks()));
        }
        return tryBrowserFallbacks();
    }

    function applyOSMGeoJsonResult(result) {
        if (!result) {
            return;
        }
        updateOSMPosition(parseFloat(result.lat), parseFloat(result.lon));
        if (result.display_name) {
            const input = document.getElementById('ttbm_osmap_location');
            if (input) {
                input.value = result.display_name;
                lastGeocodedAddress = result.display_name;
            }
        }
    }

    function geocodeOSMInputValue(address) {
        const query = (address || '').trim();
        if (!query || query === lastGeocodedAddress) {
            return;
        }
        geocodeOSMAddress(query).then((result) => {
            if (result) {
                applyOSMGeoJsonResult(result);
            }
        });
    }

    function bindOSMLocationInput() {
        const locationInput = document.getElementById('ttbm_osmap_location');
        if (!locationInput || locationInput.dataset.osmBound === '1') {
            return;
        }
        locationInput.dataset.osmBound = '1';
        lastGeocodedAddress = locationInput.value.trim();

        new Autocomplete('ttbm_osmap_location', {
            selectFirst: true,
            insertToInput: false,
            cache: false,
            howManyCharacters: 2,
            onSearch: ({currentValue}) => {
                return searchMapPlaceFeatures(currentValue).then((features) => {
                    lastAutocompleteMatches = features || [];
                    return lastAutocompleteMatches;
                });
            },
            onResults: ({currentValue, matches, template}) => {
                const regex = new RegExp(currentValue, 'gi');
                return matches.length === 0
                    ? template(`<li>No results found: "${currentValue}"</li>`)
                    : matches.map((element, index) => {
                        const coords = extractLatLngFromGeoObject(element);
                        const latAttr = coords ? String(coords.lat) : '';
                        const lngAttr = coords ? String(coords.lng) : '';
                        const name = element.properties && element.properties.display_name
                            ? element.properties.display_name
                            : '';
                        const safeName = String(name).replace(/</g, '&lt;').replace(/>/g, '&gt;');
                        return `<li data-ttbm-lat="${latAttr}" data-ttbm-lng="${lngAttr}" data-ttbm-index="${index}"><p>${safeName.replace(regex, (str) => `<b>${str}</b>`)}</p></li>`;
                    }).join('');
            },
            onSelectedItem: ({object}) => {
                lastHighlightedFeature = object || null;
            },
            onSubmit: (data) => {
                const feature = resolveFeatureFromAutocompleteData(data);
                if (feature) {
                    applyAutocompleteGeoFeature(feature);
                    return;
                }
                const typed = data && data.element ? String(data.element.value || '').trim() : '';
                if (typed) {
                    applyConfirmedMapAddress(typed, null, null);
                }
            },
            noResults: ({currentValue, template}) => template(`<li>No results found: "${currentValue}"</li>`),
        });

        // Autocomplete only while typing; map updates on Enter or selection.
        $(locationInput).on('keydown', function (e) {
            if (e.key !== 'Enter' && e.keyCode !== 13) {
                return;
            }
            if (this.getAttribute('aria-expanded') === 'true' || ttbmMapSelectionHandled) {
                return;
            }
            e.preventDefault();
            clearTimeout(osmGeocodeTimer);
            geocodeOSMInputValue(this.value);
        });
    }

    function initOSMMap() {
        const osmapCanvas = document.getElementById('osmap_canvas');
        if (!osmapCanvas) {
            return;
        }
        if (osmMap) {
            setTimeout(() => osmMap.invalidateSize(), 350);
            return;
        }

        const lati = parseFloat(document.getElementById('map_latitude')?.value) || 40.712776;
        const longdi = parseFloat(document.getElementById('map_longitude')?.value) || -74.005974;

        osmMap = L.map('osmap_canvas', {minZoom: 1, maxZoom: 20}).setView([lati, longdi], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        }).addTo(osmMap);

        osmMarker = L.marker([lati, longdi], {title: 'Tour Location', draggable: true}).addTo(osmMap);
        osmMarker.on('dragend', function () {
            const newLatLng = osmMarker.getLatLng();
            document.getElementById('map_latitude').value = newLatLng.lat;
            document.getElementById('map_longitude').value = newLatLng.lng;
            if (typeof ttbmSyncMapLocationFieldsForSubmit === 'function') {
                ttbmSyncMapLocationFieldsForSubmit();
            }
        });

        osmMap.on('click', function (event) {
            updateOSMPosition(event.latlng.lat, event.latlng.lng);
        });

        bindOSMLocationInput();

        const fsControl = L.control.fullscreen();
        osmMap.addControl(fsControl);

        setTimeout(() => osmMap.invalidateSize(), 350);
    }

    let iframeMapTimer = null;
    let iframeMapListenersBound = false;

    const LOCATION_INPUT_SELECTOR = '#ttbm_hotel_map_location, #ttbm_iframe_location, #ttbm_map_location, .ttbm-map-location-input';

    function resolveIframeLocationInput() {
        return document.getElementById('ttbm_hotel_map_location')
            || document.getElementById('ttbm_iframe_location')
            || document.getElementById('ttbm_map_location')
            || document.querySelector('.ttbm-map-location-input');
    }

    function updateIframeMapPointer(address) {
        const iframe = document.getElementById('ttbm_gmap_iframe');
        const query = (address || '').trim();
        if (!query || !iframe) {
            return;
        }
        // Cache-bust so Google Maps embed reloads when the query changes.
        const url = 'https://maps.google.com/maps?q=' + encodeURIComponent(query) + '&z=14&output=embed&_t=' + Date.now();
        if (iframe.getAttribute('src') === url) {
            return;
        }
        iframe.setAttribute('src', url);
    }

    function getMapLatLngInputs() {
        return {
            latEl: document.getElementById('map_latitude'),
            lngEl: document.getElementById('map_longitude'),
        };
    }

    function hasNumericMapCoords() {
        const {latEl, lngEl} = getMapLatLngInputs();
        if (!latEl || !lngEl) {
            return false;
        }
        const lat = (latEl.value || '').trim();
        const lng = (lngEl.value || '').trim();
        return lat !== '' && lng !== '' && !Number.isNaN(Number(lat)) && !Number.isNaN(Number(lng));
    }

    function hasStaleDefaultMapCoords() {
        const {latEl, lngEl} = getMapLatLngInputs();
        if (!latEl || !lngEl) {
            return false;
        }
        const lat = parseFloat(latEl.value);
        const lng = parseFloat(lngEl.value);
        if (Number.isNaN(lat) || Number.isNaN(lng)) {
            return false;
        }
        // Legacy UI default: New York City.
        return Math.abs(lat - 40.712776) < 0.0002 && Math.abs(lng - (-74.005974)) < 0.0002;
    }

    function applyMapLatLng(lat, lng) {
        const latVal = lat != null && lat !== '' ? String(lat) : '';
        const lngVal = lng != null && lng !== '' ? String(lng) : '';
        if (latVal === '' || lngVal === '' || Number.isNaN(Number(latVal)) || Number.isNaN(Number(lngVal))) {
            return false;
        }
        const targets = [
            document.getElementById('map_latitude'),
            document.getElementById('ttbm_map_latitude_submit'),
            document.querySelector('input[name="ttbm_map_latitude"]'),
        ];
        const lngTargets = [
            document.getElementById('map_longitude'),
            document.getElementById('ttbm_map_longitude_submit'),
            document.querySelector('input[name="ttbm_map_longitude"]'),
        ];
        targets.forEach(function (el) {
            if (!el) {
                return;
            }
            el.disabled = false;
            el.readOnly = false;
            el.value = latVal;
            el.setAttribute('value', latVal);
            try {
                el.dispatchEvent(new Event('input', {bubbles: true}));
                el.dispatchEvent(new Event('change', {bubbles: true}));
            } catch (err) { /* ignore */ }
        });
        lngTargets.forEach(function (el) {
            if (!el) {
                return;
            }
            el.disabled = false;
            el.readOnly = false;
            el.value = lngVal;
            el.setAttribute('value', lngVal);
            try {
                el.dispatchEvent(new Event('input', {bubbles: true}));
                el.dispatchEvent(new Event('change', {bubbles: true}));
            } catch (err) { /* ignore */ }
        });
        if (typeof window.jQuery !== 'undefined') {
            window.jQuery('#map_latitude, #ttbm_map_latitude_submit, input[name="ttbm_map_latitude"]').val(latVal).trigger('input').trigger('change');
            window.jQuery('#map_longitude, #ttbm_map_longitude_submit, input[name="ttbm_map_longitude"]').val(lngVal).trigger('input').trigger('change');
        }
        if (typeof ttbmSyncMapLocationFieldsForSubmit === 'function') {
            ttbmSyncMapLocationFieldsForSubmit();
        }
        return true;
    }

    function extractLatLngFromGeoObject(object) {
        if (!object || typeof object !== 'object') {
            return null;
        }
        let lat = null;
        let lng = null;
        if (object.geometry && Array.isArray(object.geometry.coordinates) && object.geometry.coordinates.length >= 2) {
            // GeoJSON Point: [lng, lat]
            if (!object.geometry.type || object.geometry.type === 'Point') {
                lng = object.geometry.coordinates[0];
                lat = object.geometry.coordinates[1];
            } else if (Array.isArray(object.geometry.coordinates[0])) {
                // Polygon / LineString — use first position.
                const first = object.geometry.coordinates[0];
                const point = Array.isArray(first[0]) ? first[0] : first;
                if (Array.isArray(point) && point.length >= 2) {
                    lng = point[0];
                    lat = point[1];
                }
            }
        }
        if ((lat == null || lng == null) && object.properties) {
            if (object.properties.lat != null && (object.properties.lon != null || object.properties.lng != null)) {
                lat = object.properties.lat;
                lng = object.properties.lon != null ? object.properties.lon : object.properties.lng;
            }
        }
        if ((lat == null || lng == null) && object.lat != null && (object.lon != null || object.lng != null)) {
            lat = object.lat;
            lng = object.lon != null ? object.lon : object.lng;
        }
        if (lat == null || lng == null || Number.isNaN(Number(lat)) || Number.isNaN(Number(lng))) {
            return null;
        }
        return {lat: Number(lat), lng: Number(lng)};
    }

    let lastAutocompleteMatches = [];
    let lastHighlightedFeature = null;

    function applyConfirmedMapAddress(address, lat, lng) {
        const name = (address || '').trim();
        if (name) {
            updateIframeMapPointer(name);
            const locationInput = resolveIframeLocationInput() || document.getElementById('ttbm_hotel_map_location');
            if (locationInput) {
                locationInput.value = name;
            }
            lastGeocodedAddress = name;
            const locSubmit = document.getElementById('ttbm_full_location_name_submit');
            const hotelSubmit = document.getElementById('ttbm_hotel_map_location_submit');
            if (locSubmit && locationInput && locationInput.id !== 'ttbm_hotel_map_location') {
                locSubmit.value = name;
            }
            if (hotelSubmit && locationInput && locationInput.id === 'ttbm_hotel_map_location') {
                hotelSubmit.value = name;
            }
        }
        if (lat != null && lng != null && lat !== '' && lng !== '' && !Number.isNaN(Number(lat)) && !Number.isNaN(Number(lng))) {
            applyMapLatLng(lat, lng);
            if (osmMap && osmMarker) {
                updateOSMPosition(Number(lat), Number(lng));
            }
            ttbmMarkMapSelectionHandled();
            if (typeof window.ttbmPersistMapLocationToServer === 'function') {
                window.ttbmPersistMapLocationToServer(false);
            }
            return;
        }
        if (name) {
            // Always geocode confirmed addresses (handles typed "coxesbazar" via Photon fallback).
            updateIframeMapCoords(name, true, true);
            ttbmMarkMapSelectionHandled();
        }
    }

    function applyMapSuggestionFromElement(el) {
        if (!el) {
            return;
        }
        const $li = window.jQuery ? window.jQuery(el).closest('li') : null;
        const node = $li && $li.length ? $li.get(0) : el;
        const idx = parseInt(node.getAttribute('data-ttbm-index'), 10);
        let lat = node.getAttribute('data-ttbm-lat');
        let lng = node.getAttribute('data-ttbm-lng');
        let name = (node.textContent || '').replace(/\s+/g, ' ').trim();
        let feature = null;
        if (!Number.isNaN(idx) && lastAutocompleteMatches[idx]) {
            feature = lastAutocompleteMatches[idx];
        } else if (name && lastAutocompleteMatches.length) {
            feature = lastAutocompleteMatches.find(function (item) {
                return item && item.properties && item.properties.display_name
                    && name.indexOf(String(item.properties.display_name).substring(0, 20)) !== -1;
            }) || null;
        }
        if (feature) {
            const coords = extractLatLngFromGeoObject(feature);
            if (coords) {
                lat = String(coords.lat);
                lng = String(coords.lng);
            }
            if (feature.properties && feature.properties.display_name) {
                name = feature.properties.display_name;
            }
        }
        applyConfirmedMapAddress(name, lat, lng);
    }

    function applyAutocompleteGeoFeature(feature) {
        if (!feature) {
            return;
        }
        const coords = extractLatLngFromGeoObject(feature);
        const name = feature.properties && feature.properties.display_name
            ? feature.properties.display_name
            : (feature.display_name || '');
        applyConfirmedMapAddress(name, coords ? coords.lat : null, coords ? coords.lng : null);
    }

    function resolveFeatureFromAutocompleteData(data) {
        if (!data) {
            return null;
        }
        if (data.object) {
            return data.object;
        }
        if (typeof data.index === 'number' && lastAutocompleteMatches[data.index]) {
            return lastAutocompleteMatches[data.index];
        }
        const typed = data.element && data.element.value ? String(data.element.value).trim() : '';
        if (typed && lastAutocompleteMatches.length) {
            const exact = lastAutocompleteMatches.find(function (item) {
                return item && item.properties && item.properties.display_name === typed;
            });
            if (exact) {
                return exact;
            }
        }
        return lastHighlightedFeature;
    }

    function updateIframeMapCoords(address, force = false, persist = false) {
        const query = (address || '').trim();
        if (!query) {
            return;
        }
        // Do not overwrite user/saved lat-lng unless forced (e.g. address autocomplete pick)
        // or the fields still hold legacy NYC placeholder coords.
        if (!force && hasNumericMapCoords() && !hasStaleDefaultMapCoords()) {
            return;
        }
        geocodeOSMAddress(query).then((result) => {
            if (result) {
                applyMapLatLng(result.lat, result.lon);
                if (persist && typeof window.ttbmPersistMapLocationToServer === 'function') {
                    window.ttbmPersistMapLocationToServer(false);
                }
            }
        });
    }

    function updateIframeMapLocation(address, displayName) {
        const locationInput = resolveIframeLocationInput();
        const query = (address || '').trim();
        if (!query) {
            return;
        }
        updateIframeMapPointer(query);
        if (displayName && locationInput) {
            locationInput.value = displayName;
            lastGeocodedAddress = displayName;
            if (typeof ttbmSyncMapLocationFieldsForSubmit === 'function') {
                ttbmSyncMapLocationFieldsForSubmit();
            }
        } else {
            lastGeocodedAddress = query;
            if (typeof ttbmSyncMapLocationFieldsForSubmit === 'function') {
                ttbmSyncMapLocationFieldsForSubmit();
            }
        }
        // Address confirmed: refresh coordinates from geocoder.
        updateIframeMapCoords(displayName || query, true);
    }

    function bindIframeLocationInput() {
        const locationInput = resolveIframeLocationInput();
        if (locationInput && !lastGeocodedAddress) {
            lastGeocodedAddress = locationInput.value.trim();
        }

        // Bind document listeners once even if the iframe is not in the DOM yet
        // (location tab / map collapse may mount later).
        if (!iframeMapListenersBound) {
            iframeMapListenersBound = true;

            // Direct click on a suggestion LI — do not rely on Autocomplete onSubmit.
            $(document).on('mousedown.ttbmMapSuggestion click.ttbmMapSuggestion', '[id*="-results"] li, .auto-results-wrapper li, .auto-is-active li', function (e) {
                if (e.type === 'click') {
                    // mousedown already handled most cases; still catch click as backup.
                }
                if (e.which && e.which !== 1) {
                    return;
                }
                const li = this.closest ? this.closest('li') : this;
                if (!li || !(li.getAttribute('data-ttbm-lat') || li.getAttribute('data-ttbm-index') || (li.textContent || '').trim())) {
                    return;
                }
                // Ignore "No results" rows.
                if ((li.textContent || '').indexOf('No results found') !== -1) {
                    return;
                }
                applyMapSuggestionFromElement(li);
            });

            // Geocode when leaving the address field (paste + click away / tab out).
            $(document).on('change.ttbmIframeMapGeocode blur.ttbmIframeMapGeocode', LOCATION_INPUT_SELECTOR, function () {
                const value = (this.value || '').trim();
                if (value.length < 2) {
                    return;
                }
                if (ttbmMapSelectionHandled) {
                    return;
                }
                if (value === lastGeocodedAddress && hasNumericMapCoords() && !hasStaleDefaultMapCoords()) {
                    return;
                }
                clearTimeout(iframeMapTimer);
                iframeMapTimer = setTimeout(function () {
                    if (ttbmMapSelectionHandled) {
                        return;
                    }
                    applyConfirmedMapAddress(value, null, null);
                }, 200);
            });

            // Do not update the map while typing — only autocomplete suggestions.
            // Map + lat/lng update on Enter (dropdown closed) or autocomplete selection.
            $(document).on('keydown.ttbmHotelIframeMap', LOCATION_INPUT_SELECTOR, function (e) {
                if (e.key !== 'Enter' && e.keyCode !== 13) {
                    return;
                }
                // Autocomplete handles Enter when its dropdown is open (selection confirm).
                if (this.getAttribute('aria-expanded') === 'true') {
                    const selected = document.querySelector('.auto-results-wrapper li.auto-selected, [id*="-results"] li.auto-selected, [id*="-results"] li[aria-selected="true"]');
                    if (selected) {
                        applyMapSuggestionFromElement(selected);
                    } else if (lastHighlightedFeature) {
                        const coords = extractLatLngFromGeoObject(lastHighlightedFeature);
                        const name = lastHighlightedFeature.properties && lastHighlightedFeature.properties.display_name
                            ? lastHighlightedFeature.properties.display_name
                            : this.value.trim();
                        applyConfirmedMapAddress(name, coords ? coords.lat : null, coords ? coords.lng : null);
                    } else if (lastAutocompleteMatches[0]) {
                        applyMapSuggestionFromElement(document.querySelector('[id*="-results"] li[data-ttbm-index="0"]') || document.querySelector('[id*="-results"] li'));
                    }
                    return;
                }
                if (ttbmMapSelectionHandled) {
                    return;
                }
                e.preventDefault();
                clearTimeout(iframeMapTimer);
                clearTimeout(osmGeocodeTimer);
                const value = this.value.trim();
                if (value.length < 2) {
                    return;
                }
                // Typed address (e.g. coxesbazar) — map + server/Photon geocode for lat/lng.
                applyConfirmedMapAddress(value, null, null);
            });
        }

        if (!locationInput || !locationInput.id) {
            return;
        }
        if (typeof Autocomplete === 'undefined') {
            // Autocomplete script may load just after us — retry briefly.
            if (!locationInput.dataset.acRetry) {
                locationInput.dataset.acRetry = '1';
                let attempts = 0;
                const retry = setInterval(function () {
                    attempts += 1;
                    if (typeof Autocomplete !== 'undefined') {
                        clearInterval(retry);
                        bindIframeLocationInput();
                    } else if (attempts > 25) {
                        clearInterval(retry);
                    }
                }, 200);
            }
            return;
        }
        if (locationInput.dataset.acBound === '1') {
            return;
        }

        locationInput.dataset.acBound = '1';
        new Autocomplete(locationInput.id, {
            selectFirst: true,
            insertToInput: false,
            cache: false,
            howManyCharacters: 2,
            onSearch: ({currentValue}) => {
                return searchMapPlaceFeatures(currentValue).then((features) => {
                    lastAutocompleteMatches = features || [];
                    return lastAutocompleteMatches;
                });
            },
            onResults: ({currentValue, matches, template}) => {
                const regex = new RegExp(currentValue, 'gi');
                return matches.length === 0
                    ? template(`<li>No results found: "${currentValue}"</li>`)
                    : matches.map((element, index) => {
                        const coords = extractLatLngFromGeoObject(element);
                        const latAttr = coords ? String(coords.lat) : '';
                        const lngAttr = coords ? String(coords.lng) : '';
                        const name = element.properties && element.properties.display_name
                            ? element.properties.display_name
                            : '';
                        const safeName = String(name).replace(/</g, '&lt;').replace(/>/g, '&gt;');
                        return `<li data-ttbm-lat="${latAttr}" data-ttbm-lng="${lngAttr}" data-ttbm-index="${index}"><p>${safeName.replace(regex, (str) => `<b>${str}</b>`)}</p></li>`;
                    }).join('');
            },
            onSelectedItem: ({object}) => {
                lastHighlightedFeature = object || null;
            },
            onSubmit: (data) => {
                const feature = resolveFeatureFromAutocompleteData(data);
                if (feature) {
                    applyAutocompleteGeoFeature(feature);
                    return;
                }
                const typed = data && data.element ? String(data.element.value || '').trim() : '';
                if (typed) {
                    applyConfirmedMapAddress(typed, null, null);
                }
            },
            noResults: ({currentValue, template}) => template(`<li>No results found: "${currentValue}"</li>`),
        });
    }

    function ensureLocationMap() {
        const hasApiKey = typeof ttbm_map !== 'undefined' && !!ttbm_map.api_key;
        const gmapCanvas = document.getElementById('gmap_canvas');
        const iframe = document.getElementById('ttbm_gmap_iframe');
        const osmCanvas = document.getElementById('osmap_canvas');

        // Prefer whichever map UI is actually rendered. Avoid requiring ttbm_map
        // (localization can race admin script enqueue and leave it undefined).
        if (hasApiKey && gmapCanvas) {
            bindGmapLocationInputLive();
            if (typeof google !== 'undefined' && google.maps) {
                initGMap();
            }
            return;
        }
        if (iframe || document.getElementById('ttbm_iframe_location') || document.getElementById('ttbm_map_location') || document.querySelector('.ttbm-map-location-input')) {
            bindIframeLocationInput();
            return;
        }
        if (osmCanvas) {
            initOSMMap();
        }
    }

    window.ensureLocationMap = ensureLocationMap;

    window.initMap = function () {
        ensureLocationMap();
    };

    // ===========Google Map setup=============
    let gmap, gmapMarker, gmapAutocomplete, gmapGeocoder, gmapInputTimer = null;
    let gmapLiveBound = false;
    let lastGmapGeocodedQuery = '';

    function resolveGmapLocationInput() {
        return document.getElementById('ttbm_hotel_map_location')
            || document.getElementById('ttbm_map_location')
            || document.getElementById('ttbm_iframe_location')
            || document.querySelector('.ttbm-map-location-input');
    }

    function setGmapLocationInputValue(value) {
        const locationInput = resolveGmapLocationInput();
        if (locationInput) {
            locationInput.value = value;
            if (typeof ttbmSyncMapLocationFieldsForSubmit === 'function') {
                ttbmSyncMapLocationFieldsForSubmit();
            }
        }
    }

    function geocodeGmapAddress(address) {
        const query = (address || '').trim();
        if (!query || !gmapGeocoder || !gmap || !gmapMarker) {
            return;
        }
        if (query === lastGmapGeocodedQuery) {
            return;
        }
        gmapGeocoder.geocode({address: query}, function (results, status) {
            if (status !== google.maps.GeocoderStatus.OK || !results[0]) {
                return;
            }
            lastGmapGeocodedQuery = query;
            const location = results[0].geometry.location;
            gmap.setCenter(location);
            gmapMarker.setPosition(location);
            const latEl = document.getElementById('map_latitude');
            const lngEl = document.getElementById('map_longitude');
            if (latEl) {
                latEl.value = location.lat();
            }
            if (lngEl) {
                lngEl.value = location.lng();
            }
            if (typeof ttbmSyncMapLocationFieldsForSubmit === 'function') {
                ttbmSyncMapLocationFieldsForSubmit();
            }
        });
    }

    function bindGmapLocationInputLive() {
        if (gmapLiveBound) {
            return;
        }
        gmapLiveBound = true;

        // Google Places autocomplete shows suggestions while typing.
        // Map updates only on Enter or place selection (place_changed).
        $(document).on('keydown.ttbmGmapLive', LOCATION_INPUT_SELECTOR, function (e) {
            if (e.key !== 'Enter' && e.keyCode !== 13) {
                return;
            }
            // Let Places dropdown handle Enter when open.
            if (this.getAttribute('aria-expanded') === 'true' || ttbmMapSelectionHandled || document.querySelector('.pac-container:visible')) {
                return;
            }
            e.preventDefault();
            clearTimeout(gmapInputTimer);
            const value = this.value.trim();
            if (value.length >= 2) {
                lastGmapGeocodedQuery = '';
                geocodeGmapAddress(value);
            }
        });
    }

    function initGmapPlacesAutocomplete() {
        const locationInput = resolveGmapLocationInput();
        if (!locationInput || locationInput.dataset.gmapAcBound === '1') {
            return;
        }
        if (typeof google === 'undefined' || !google.maps || !google.maps.places) {
            return;
        }
        locationInput.dataset.gmapAcBound = '1';
        gmapAutocomplete = new google.maps.places.Autocomplete(locationInput);
        gmapAutocomplete.addListener('place_changed', onPlaceChanged);
    }

    function syncMapFromCurrentLocationInput() {
        const locationInput = resolveIframeLocationInput() || resolveGmapLocationInput();
        if (!locationInput) {
            return;
        }
        const value = (locationInput.value || '').trim();
        if (value.length < 2) {
            return;
        }
        // Don't move the map for an address the user is still typing.
        if (lastGeocodedAddress && value !== lastGeocodedAddress.trim()) {
            return;
        }
        if (document.getElementById('ttbm_gmap_iframe')) {
            updateIframeMapPointer(value);
            updateIframeMapCoords(value, hasStaleDefaultMapCoords() || !hasNumericMapCoords());
        }
        if (document.getElementById('gmap_canvas') && gmapGeocoder && !hasNumericMapCoords()) {
            lastGmapGeocodedQuery = '';
            geocodeGmapAddress(value);
        }
    }

    $(document).ready(function () {
        ensureLocationMap();
        setTimeout(function () {
            ensureLocationMap();
            syncMapFromCurrentLocationInput();
        }, 500);
    });

    $(document).on('ttbm_tab_activated', function (e, tabsTarget) {
        if (tabsTarget === '#ttbm_settings_location' || tabsTarget === '#ttbm_settings_hotel_location') {
            requestAnimationFrame(function () {
                ensureLocationMap();
                syncMapFromCurrentLocationInput();
            });
        }
    });

    $(document).on('click', '.ttbm_settings_location,[data-collapse-target="#ttbm_display_map"]', function () {
        requestAnimationFrame(function () {
            ensureLocationMap();
            syncMapFromCurrentLocationInput();
        });
    });

    // for hotel trigger
    $(document).on('click', '.ttbm_hotel_map_location,[data-collapse-target="#ttbm_display_hotel_map"]', function () {
        requestAnimationFrame(function () {
            ensureLocationMap();
            syncMapFromCurrentLocationInput();
        });
    });

    function initGMap() {
        const latitudeEl = document.getElementById('map_latitude');
        const longitudeEl = document.getElementById('map_longitude');
        const gmapCanvas = document.getElementById('gmap_canvas');
        if (!latitudeEl || !longitudeEl || !gmapCanvas) {
            return;
        }
        if (gmap) {
            google.maps.event.trigger(gmap, 'resize');
            gmap.setCenter(gmapMarker.getPosition());
            initGmapPlacesAutocomplete();
            setTimeout(() => google.maps.event.trigger(gmap, 'resize'), 350);
            syncGmapToCurrentInput();
            return;
        }
        let lati = parseFloat(latitudeEl.value);
        let longdi = parseFloat(longitudeEl.value);
        if (Number.isNaN(lati) || Number.isNaN(longdi)) {
            lati = 21.4272;
            longdi = 92.0058;
        }
        // Initialize Google Map
        gmap = new google.maps.Map(gmapCanvas, {
            center: {lat: lati, lng: longdi},
            zoom: 12,
            zoomControl: true,
            streetViewControl: false,
            mapTypeControl: false,
            scaleControl: true
        });
        // Initialize Google Marker
        gmapMarker = new google.maps.Marker({
            position: {lat: lati, lng: longdi},
            map: gmap,
            title: "Selected Location",
            draggable: true
        });
        // Initialize Google geocoder for reverse geocoding
        gmapGeocoder = new google.maps.Geocoder();
        const locationInput = resolveGmapLocationInput();
        if (locationInput) {
            lastGmapGeocodedQuery = locationInput.value.trim();
        }
        // Update latitude and longitude when the marker is dragged
        gmapMarker.addListener("dragend", function (event) {
            document.getElementById('map_latitude').value = event.latLng.lat();
            document.getElementById('map_longitude').value = event.latLng.lng();
            if (typeof ttbmSyncMapLocationFieldsForSubmit === 'function') {
                ttbmSyncMapLocationFieldsForSubmit();
            }
            reverseGeocode(event.latLng); // Update location name when dragging the marker
        });
        initGmapPlacesAutocomplete();
        // Add a click event listener to Google Map
        gmap.addListener("click", function (event) {
            let clickedLatLng = event.latLng;
            gmapMarker.setPosition(clickedLatLng);
            document.getElementById('map_latitude').value = clickedLatLng.lat();
            document.getElementById('map_longitude').value = clickedLatLng.lng();
            if (typeof ttbmSyncMapLocationFieldsForSubmit === 'function') {
                ttbmSyncMapLocationFieldsForSubmit();
            }
            reverseGeocode(clickedLatLng);
        });
    }
    function syncGmapToCurrentInput() {
        const locationInput = resolveGmapLocationInput();
        if (!locationInput || !gmapGeocoder) {
            return;
        }
        const value = locationInput.value.trim();
        if (value && value !== lastGmapGeocodedQuery) {
            geocodeGmapAddress(value);
        }
    }

    // Reverse geocoding for Google Map
    function reverseGeocode(latLng) {
        gmapGeocoder.geocode({'location': latLng}, function (results, status) {
            if (status === google.maps.GeocoderStatus.OK && results[0]) {
                const addr = results[0].formatted_address;
                setGmapLocationInputValue(addr);
                lastGmapGeocodedQuery = addr;
            }
        });
    }
    // Handle place change for Google Map
    function onPlaceChanged() {
        let place = gmapAutocomplete.getPlace();
        if (!place.geometry) {
            return;
        }
        ttbmMarkMapSelectionHandled();
        let location = place.geometry.location;
        gmap.setCenter(location);
        gmapMarker.setPosition(location);
        document.getElementById("map_latitude").value = location.lat();
        document.getElementById("map_longitude").value = location.lng();
        if (typeof ttbmSyncMapLocationFieldsForSubmit === 'function') {
            ttbmSyncMapLocationFieldsForSubmit();
        }
        const formatted = place.formatted_address || '';
        if (formatted) {
            setGmapLocationInputValue(formatted);
            lastGmapGeocodedQuery = formatted;
        }
    }
})(jQuery);
//=================title style switcher==================
(function($){
    $(document).on('click', '.ttbm-title-styles .title-style', function(){
       var parent = $(this).closest('.ttbm-title-styles');
        $(parent).find('.title-style').removeClass('active');
        $(this).addClass('active');
        var titleStyle = $(this).data('title-style');
        $('#ttbm-title-style').val(titleStyle);
    });
})(jQuery);

(function($){
    $(document).on('click', '.ttbm-booking-styles .booking-style', function(){
       var parent = $(this).closest('.ttbm-booking-styles');
        $(parent).find('.booking-style').removeClass('active');
        $(this).addClass('active');
        var titleStyle = $(this).data('booking-style');
        $('#ttbm-booking-style').val(titleStyle);
    });
})(jQuery);

// ==============metabox sidebar collapse==============
(function($){
    $(document).on('click', '.meta-sidebar-toggle', function() {
        $('.meta-sidebar-toggle i').toggleClass('mi-angle-right mi-angle-left');
        $('.tabLists.meta-sidebar').closest('.leftTabs').toggleClass('leftTabs-collapsed');
        $('.tabLists.meta-sidebar').toggleClass('meta-sidebar-collapsed');
    });
})(jQuery);

// New Activity Popup Logic
(function ($) {
    "use strict";
    // Open popup and load form
    $(document).on('click', '.open-activity-popup', function (e) {
        e.preventDefault();
        var $popup = $('[data-popup="add_new_activity_popup"]');
        var $formArea = $popup.find('.ttbm_activity_form_area');
        $formArea.html('<div class="loading">Loading...</div>');
        $popup.show();
        $.post(ttbm_ajax_url, {action: 'load_ttbm_activity_form'}, function (data) {
            $formArea.html(data);
        });
    });
    // Close popup
    $(document).on('click', '[data-popup="add_new_activity_popup"] .popupClose', function () {
        $(this).closest('[data-popup="add_new_activity_popup"]').hide();
    });
    // Save activity
    $(document).on('click', '.ttbm_new_activity_save, .ttbm_new_activity_save_close', function (event) {
        var $popup = $('[data-popup="add_new_activity_popup"]');
        var $formArea = $popup.find('.ttbm_activity_form_area');
        var $parent = $popup.find('.popupMainArea');
        var name = $parent.find('[name="ttbm_activity_name"]').val();
        var description = $parent.find('[name="ttbm_activity_description"]').val();
        var icon = $parent.find('[name="ttbm_activity_icon"]').val();
        var nonce = $parent.find('[name="ttbm_add_new_activity_popup"]').val();
        // Simple validation
        if (!name) {
            $parent.find('[data-required="ttbm_activity_name"]').slideDown('fast');
            return false;
        } else {
            $parent.find('[data-required="ttbm_activity_name"]').slideUp('fast');
        }
        if (!icon) {
            $parent.find('[data-required="ttbm_activity_icon"]').slideDown('fast');
            return false;
        } else {
            $parent.find('[data-required="ttbm_activity_icon"]').slideUp('fast');
        }
        // Save via AJAX
        $.post(ttbm_ajax_url, {
            action: 'ttbm_new_activity_save',
            activity_name: name,
            activity_description: description,
            activity_icon: icon,
            _wp_nonce: nonce
        }, function (response) {
            // Optionally handle response
            $parent.find('[name="ttbm_activity_name"]').val('');
            $parent.find('[name="ttbm_activity_description"]').val('');
            $parent.find('[name="ttbm_activity_icon"]').val('');
            $parent.find('.ttbm_success_info').slideDown('fast');
            ttbm_reload_activity_list();
            if ($(event.target).hasClass('ttbm_new_activity_save_close')) {
                $popup.hide();
                setTimeout(function () {
                    ttbm_reload_activity_list();
                }, 300);
            }
        });
        return false;
    });
    // Reload activity list
    window.ttbm_reload_activity_list = function () {
        var ttbm_id = $('[name="post_id"]').val();
        var $parent = $('.ttbm_activities_table');
        $.post(ttbm_ajax_url, {action: 'ttbm_reload_activity_list', ttbm_id: ttbm_id}, function (data) {
            $parent.empty().append(data);
        });
    };

    // Tour list layout switcher (classic / compact / modern). The design class
    // lives on .ttbm-tour-list-page, which AJAX never replaces, so load-more /
    // search cards inherit the active layout automatically. Persisted per user.
    $(document).on('click', '.ttbm-design-opt', function () {
        var design = String($(this).data('design'));
        if (['classic', 'compact', 'modern'].indexOf(design) === -1) {
            return;
        }
        var $page = $('.ttbm-tour-list-page');
        $page.removeClass('ttbm-design-classic ttbm-design-compact ttbm-design-modern').addClass('ttbm-design-' + design);
        $(this).addClass('active').siblings('.ttbm-design-opt').removeClass('active');
        if (typeof ttbm_admin_ajax !== 'undefined') {
            $.post(ttbm_admin_ajax.ajax_url, {
                action: 'ttbm_save_list_design',
                design: design,
                nonce: ttbm_admin_ajax.nonce
            });
        }
    });

    // Hotel list layout switcher (classic / compact / modern). Mirrors the tour
    // switcher above. The class lives on .ttbm-hotel-listing, which AJAX never
    // replaces (it only appends rows into the table body), so load-more / search
    // / filter results inherit the active layout. Persisted per user.
    $(document).on('click', '.ttbm-hdesign-opt', function () {
        var design = String($(this).data('design'));
        if (['classic', 'compact', 'modern'].indexOf(design) === -1) {
            return;
        }
        var $wrap = $('.ttbm-hotel-listing');
        $wrap.removeClass('ttbm-hdesign-classic ttbm-hdesign-compact ttbm-hdesign-modern').addClass('ttbm-hdesign-' + design);
        $(this).addClass('active').siblings('.ttbm-hdesign-opt').removeClass('active');
        if (typeof ttbm_admin_ajax !== 'undefined') {
            $.post(ttbm_admin_ajax.ajax_url, {
                action: 'ttbm_save_hotel_list_design',
                design: design,
                nonce: ttbm_admin_ajax.nonce
            });
        }
    });
})(jQuery);
