function ttbm_load_sortable_datepicker(parent, item) {
    var enableNewRowFields = function () {
        parent.find(".ttbm_item_insert > .ttbm_remove_area").last().find("input, select, textarea").prop("disabled", false);
    };
    if (parent.find(".ttbm_item_insert_before").length > 0) {
        jQuery(item)
            .insertBefore(parent.find(".ttbm_item_insert_before").first())
            .promise()
            .done(function () {
                parent.find(".ttbm_sortable_area").sortable({
                    handle: jQuery(this).find(".ttbm_sortable_button"),
                });
                ttbm_load_date_picker(parent);
                enableNewRowFields();
            });
    } else {
        parent
            .find(".ttbm_item_insert")
            .first()
            .append(item)
            .promise()
            .done(function () {
                parent.find(".ttbm_sortable_area").sortable({
                    handle: jQuery(this).find(".ttbm_sortable_button"),
                });
                ttbm_load_date_picker(parent);
                enableNewRowFields();
            });
    }
    return true;
}
(function ($) {
    "use strict";
    $(document).ready(function () {
        //=========Short able==============//
        $(document).find(".ttbm_sortable_area").sortable({handle: $(this).find(".ttbm_sortable_button"),});
        ttbmSyncPillCustomInput($(".ttbm_settings_dates .ttbm-pill-group"));
    });
    function ttbmSyncPillCustomInput($pillGroup) {
        if (!$pillGroup || !$pillGroup.length) {
            return;
        }
        let $customBtn = $pillGroup.find("[data-pill-custom]");
        let $customInput = $pillGroup.find(".ttbm-pill-custom-input");
        let $customWrap = $pillGroup.find(".ttbm-pill-custom-wrap");
        if (!$customBtn.length || !$customInput.length) {
            return;
        }
        if ($customBtn.hasClass("active")) {
            $customInput.removeClass("dNone").show().focus();
            $customWrap.removeClass("dNone");
        } else {
            $customInput.addClass("dNone").hide();
            $customWrap.addClass("dNone");
        }
    }
    $(document).on("click", ".ttbm_settings_dates .ttbm-pill-group [data-group-radio]", function () {
        let $pillGroup = $(this).closest(".ttbm-pill-group");
        window.setTimeout(function () {
            ttbmSyncPillCustomInput($pillGroup);
        }, 0);
    });
    $(document).on("click focus", ".ttbm_settings_dates .ttbm-pill-custom-input", function (e) {
        e.stopPropagation();
    });
    //=========upload image==============//
    $(document).on("click", ".ttbm_add_single_image", function () {
        let parent = $(this);
        parent.find(".ttbm_single_image_item").remove();
        wp.media.editor.send.attachment = function (props, attachment) {
            let attachment_id = attachment.id;
            let attachment_url = attachment.url;
            let html =
                '<div class="ttbm_single_image_item" data-image-id="' +
                attachment_id +
                '"><span class="fas fa-times circleIcon_xs ttbm_remove_single_image"></span>';
            html += '<img src="' + attachment_url + '" alt="' + attachment_id + '"/>';
            html += "</div>";
            parent.append(html);
            parent.find("input").val(attachment_id);
            parent.find("button").slideUp("fast");
        };
        wp.media.editor.open($(this));
        return false;
    });
    $(document).on("click", ".ttbm_remove_single_image", function (e) {
        e.stopPropagation();
        let parent = $(this).closest(".ttbm_add_single_image");
        $(this).closest(".ttbm_single_image_item").remove();
        parent.find("input").val("");
        parent.find("button").slideDown("fast");
    });
    $(document).on("click", ".ttbm_remove_multi_image", function () {
        let parent = $(this).closest(".ttbm_multi_image_area");
        let current_parent = $(this).closest(".ttbm_multi_image_item");
        let img_id = current_parent.data("image-id");
        current_parent.remove();
        let all_img_ids = parent.find(".ttbm_multi_image_value").val();
        all_img_ids = all_img_ids.replace("," + img_id, "");
        all_img_ids = all_img_ids.replace(img_id + ",", "");
        all_img_ids = all_img_ids.replace(img_id, "");
        parent.find(".ttbm_multi_image_value").val(all_img_ids);
    });
    $(document).on("click", ".ttbm_add_multi_image", function () {
        let parent = $(this).closest(".ttbm_multi_image_area");
        wp.media.editor.send.attachment = function (props, attachment) {
            let attachment_id = attachment.id;
            let attachment_url = attachment.url;
            let html =
                '<div class="ttbm_multi_image_item" data-image-id="' +
                attachment_id +
                '"><span class="fas fa-times circleIcon_xs ttbm_remove_multi_image"></span>';
            html += '<img src="' + attachment_url + '" alt="' + attachment_id + '"/>';
            html += "</div>";
            parent.find(".ttbm_multi_image").append(html);
            let value = parent.find(".ttbm_multi_image_value").val();
            value = value ? value + "," + attachment_id : attachment_id;
            parent.find(".ttbm_multi_image_value").val(value);
        };
        wp.media.editor.open($(this));
        return false;
    });
    //=========Clear date/time field ==============//
    function ttbmClearDateTimeField($trigger) {
        let $clear = $trigger.closest(".ttbm-field-clear");
        let $wrap = $clear.closest(".ttbm-datetime-clear-wrap");
        if (!$wrap.length) {
            return;
        }
        $wrap.find('input[type="hidden"]').each(function () {
            this.value = "";
        });
        $wrap.find('input[type="time"], input.date_type, input.date_type_without_year, .formControl').each(function () {
            if (this.type === "hidden") {
                return;
            }
            let $input = $(this);
            this.value = "";
            $input.val("");
            if ($input.hasClass("hasDatepicker")) {
                try {
                    $input.datepicker("setDate", null);
                } catch (err) {}
            }
            $input.trigger("change").trigger("input").trigger("blur");
        });
    }
    $(document).on("click", ".ttbm_settings_dates .ttbm-field-clear, .ttbm_settings_dates .ttbm-field-clear *", function (e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        ttbmClearDateTimeField($(e.target));
        return false;
    });
    $(document).on("keydown", ".ttbm_settings_dates .ttbm-field-clear", function (e) {
        if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            e.stopPropagation();
            ttbmClearDateTimeField($(this));
        }
    });
    //=========Remove Setting Item ==============//
    $(document).on("click", ".ttbm_item_remove,.ttbm_remove_icon", function (e) {
        e.preventDefault();
        let $row = $(this).closest(".ttbm_remove_area");
        let $ticketRows = $row.closest(".ttbm_insert_ticket_type");
        let $extraRows = $row.closest(".ttbm_insert_extra_service");
        if ($ticketRows.length && $ticketRows.find("> tr.ttbm_remove_area").length <= 1) {
            return false;
        }
        if ($extraRows.length && $extraRows.find("> tr.ttbm_remove_area").length <= 1) {
            return false;
        }
        if (confirm("Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .")) {
            $row.slideUp(250).remove();
            return true;
        } else {
            return false;
        }
    });
    $(document).on("click", ".ttbm_add_item", function (e) {
        e.preventDefault();
        let parent = $(this).closest('.ttbm_settings_area, .ttbm_settings_place_you_see');
        if (!parent.length) {
            return false;
        }
        let target = parent.find('>.ttbm_hidden_content').first().find('.ttbm_hidden_item');
        if (!target.length) {
            return false;
        }
        target.find('[data-collapse-target]').each(function () {
            let current_id = $(this).attr('data-collapse-target');
            let unique_id = '#unique_id_' + Math.floor((Math.random() * 9999) + 999);
            target.find('[data-collapse-target="' + current_id + '"]').attr('data-collapse-target', unique_id);
            target.find('[data-collapse="' + current_id + '"]').attr('data-collapse', unique_id);
        }).promise().done(function () {
            let item = target.html();
            ttbm_load_sortable_datepicker(parent, item);
            let $newSelect = parent.find('.ttbm_item_insert tr').last().find('select[name="ttbm_city_place_id[]"]');
            if ($newSelect.length && $.fn.select2) {
                if ($newSelect.hasClass('select2-hidden-accessible')) {
                    $newSelect.select2('destroy');
                }
                $newSelect.removeClass('add_ttbm_select2').addClass('ttbm_select2');
                $newSelect.select2({});
            } else {
                parent.find('.ttbm_item_insert').find('.add_ttbm_select2').select2({});
            }
        });
        return false;
    });
    function ttbmUpdateShortDescCharCount($textarea) {
        let $wrap = $textarea.closest('.ttbm-gen-short-desc');
        if (!$wrap.length) {
            return;
        }
        let max = parseInt($wrap.find('.ttbm-gen-char-count').data('max'), 10) || 500;
        let len = ($textarea.val() || '').length;
        $wrap.find('.ttbm-gen-char-count').text(len + ' / ' + max);
    }
    $(document).on('input', '.ttbm-gen-short-desc__textarea', function () {
        ttbmUpdateShortDescCharCount($(this));
    });
    $(document).ready(function () {
        $('.ttbm-gen-short-desc__textarea').each(function () {
            ttbmUpdateShortDescCharCount($(this));
        });
        ttbmFixTourLanguageSelect2SearchWidth();
    });

    function ttbmFixTourLanguageSelect2SearchWidth() {
        $('#ttbm_meta_box_panel select[name="ttbm_travel_language[]"]').each(function () {
            var $field = $(this).next('.select2-container').find('.select2-search__field');
            if ($field.length) {
                $field.css('width', '');
            }
        });
    }

    $(document).on('select2:open select2:close select2:select select2:unselect', '#ttbm_meta_box_panel select[name="ttbm_travel_language[]"]', function () {
        window.setTimeout(ttbmFixTourLanguageSelect2SearchWidth, 0);
    });
})(jQuery);
(function ($) {
    "use strict";
    //=================select icon=========================//
    // Select/replace icon from popup (event delegation so it works for both add button and existing icon click)
    $(document).on("click", ".ttbm_add_icon_popup .iconItem", function () {
        let parent = $("[data-active-popup]").closest(".ttbm_add_icon_image_area");
        let icon_class = $(this).data("icon-class");
        let target_popup = $(this).closest(".ttbm_add_icon_popup");
        if (icon_class) {
            parent.find('input[type="hidden"]').val(icon_class);
            parent.find(".ttbm_add_icon_image_button_area").slideUp("fast");
            parent.find(".ttbm_image_item").slideUp("fast");
            parent.find(".ttbm_icon_item").slideDown("fast");
            parent.find("[data-add-icon]").removeAttr("class").addClass(icon_class);
            target_popup.find(".iconItem").removeClass("active");
            target_popup.find(".popupClose").trigger("click");
        }
    });
    // Icon category tabs in popup (event delegation)
    $(document).on("click", ".ttbm_add_icon_popup [data-icon-menu]", function () {
        if (!$(this).hasClass("active")) {
            let target = $(this);
            let tabsTarget = target.data("icon-menu");
            let target_popup = target.closest(".ttbm_add_icon_popup");
            target_popup.find("[data-icon-menu]").removeClass("active");
            target.addClass("active");
            target_popup.find("[data-icon-list]").each(function () {
                let targetItem = $(this).data("icon-list");
                if (tabsTarget === "all_item" || targetItem === tabsTarget) {
                    $(this).slideDown(250);
                } else {
                    $(this).slideUp(250);
                }
            });
        }
        return false;
    });
    // Reset popup state when closed (event delegation)
    $(document).on("click", ".ttbm_add_icon_popup .popupClose", function () {
        let target_popup = $(this).closest(".ttbm_add_icon_popup");
        target_popup.find('[data-icon-menu="all_item"]').trigger("click");
        target_popup.find(".iconItem").removeClass("active");
    });
    // Click existing icon to replace it (open popup via the add button)
    $(document).on("click", ".ttbm_add_icon_image_area .ttbm_icon_item", function (e) {
        if ($(e.target).closest(".ttbm_icon_remove").length) {
            return;
        }
        $(this).closest(".ttbm_add_icon_image_area").find(".ttbm_icon_add").trigger("click");
    });
    // Remove icon
    $(document).on("click", ".ttbm_add_icon_image_area .ttbm_icon_remove", function () {
        let parent = $(this).closest(".ttbm_add_icon_image_area");
        parent.find('input[type="hidden"]').val("");
        parent.find("[data-add-icon]").removeAttr("class");
        parent.find(".ttbm_icon_item").slideUp("fast");
        parent.find(".ttbm_add_icon_image_button_area").slideDown("fast");
    });
    //=================select Single image=========================//
    $(document).on("click", "button.ttbm_image_add", function () {
        let $this = $(this);
        let parent = $this.closest(".ttbm_add_icon_image_area");
        wp.media.editor.send.attachment = function (props, attachment) {
            let attachment_id = attachment.id;
            let attachment_url = attachment.url;
            parent.find('input[type="hidden"]').val(attachment_id);
            parent.find(".ttbm_icon_item").slideUp("fast");
            parent.find("img").attr("src", attachment_url);
            parent.find(".ttbm_image_item").slideDown("fast");
            parent.find(".ttbm_add_icon_image_button_area").slideUp("fast");
        };
        wp.media.editor.open($this);
        return false;
    });
    $(document).on("click", ".ttbm_add_icon_image_area .ttbm_image_remove", function () {
            let parent = $(this).closest(".ttbm_add_icon_image_area");
            parent.find('input[type="hidden"]').val("");
            parent.find("img").attr("src", "");
            parent.find(".ttbm_image_item").slideUp("fast");
            parent.find(".ttbm_add_icon_image_button_area").slideDown("fast");
        }
    );

    // ================ Template slection ===============
    $(document).on('click', '.ttbm-template img', function (e) {
        $('[name="ttbm_theme_file"]').val($(this).data('ttbm-template'));
        $('.ttbm-template ').removeClass('active')
        $(this).parent('.ttbm-template ').addClass('active');
    });

    // Location off cascades to map; map can be off while location stays on.
    var ttbmLocationMapSyncing = false;

    function ttbmSetCollapseState($panel, targetId, isOn) {
        var $sections = $panel.find('[data-collapse="' + targetId + '"]');
        var $switches = $panel.find('[data-collapse-target="' + targetId + '"]');
        if (isOn) {
            $sections.addClass('mActive').stop(true, true).slideDown(250);
            $switches.addClass('mActive');
        } else {
            $sections.removeClass('mActive').stop(true, true).slideUp(250);
            $switches.removeClass('mActive');
        }
    }

    function ttbmOnLocationToggleChanged(isOn) {
        var $panel = $('#ttbm_meta_box_panel');
        var $mapInput = $panel.find('input[name="ttbm_display_map"]');
        if (!$panel.find('input[name="ttbm_display_location"]').length) {
            return;
        }

        ttbmLocationMapSyncing = true;
        ttbmSetCollapseState($panel, '#ttbm_display_location', isOn);

        var $mapEnableWrap = $panel.find('.ttbm-map-enable-wrap');
        if (isOn) {
            $mapEnableWrap.stop(true, true).slideDown(250);
            if ($mapInput.length) {
                $mapInput.prop('checked', true);
                ttbmSetCollapseState($panel, '#ttbm_display_map', true);
            }
        } else if ($mapInput.length) {
            $mapInput.prop('checked', false);
            ttbmSetCollapseState($panel, '#ttbm_display_map', false);
            $mapEnableWrap.stop(true, true).slideUp(250);
        }

        ttbmLocationMapSyncing = false;

        if (typeof window.ttbmSyncLocationRequiredState === 'function') {
            window.ttbmSyncLocationRequiredState();
        }

        if (isOn && typeof window.ensureLocationMap === 'function') {
            setTimeout(window.ensureLocationMap, 400);
        }
    }

    function ttbmOnMapToggleChanged(isOn) {
        var $panel = $('#ttbm_meta_box_panel');
        if (!$panel.find('input[name="ttbm_display_map"]').length) {
            return;
        }

        ttbmLocationMapSyncing = true;
        ttbmSetCollapseState($panel, '#ttbm_display_map', isOn);
        ttbmLocationMapSyncing = false;

        if (isOn && typeof window.ensureLocationMap === 'function') {
            setTimeout(window.ensureLocationMap, 400);
        }
    }

    $(document).on('change', '#ttbm_meta_box_panel input[name="ttbm_display_location"]', function () {
        if (ttbmLocationMapSyncing) {
            return;
        }
        ttbmOnLocationToggleChanged($(this).is(':checked'));
    });

    $(document).on('click', '#ttbm_meta_box_panel [data-collapse-target="#ttbm_display_location"]', function () {
        window.setTimeout(function () {
            if (typeof window.ttbmSyncLocationRequiredState === 'function') {
                window.ttbmSyncLocationRequiredState();
            }
        }, 0);
    });

    $(document).on('change', '#ttbm_meta_box_panel input[name="ttbm_display_map"]', function () {
        if (ttbmLocationMapSyncing) {
            return;
        }
        ttbmOnMapToggleChanged($(this).is(':checked'));
    });

    // General Information inline toggles: keep fields visible, enable/disable by toggle state.
    function ttbmSetInlineToggleFieldState($field, isOn) {
        var $cb = $field.find('.roundSwitchLabel input[type="checkbox"]').first();
        if (!$cb.length) {
            return;
        }

        var targetId = '#' + $cb.attr('name');
        var $switch = $cb.siblings('[data-collapse-target="' + targetId + '"]');
        var $controls = $field.find('[data-ttbm-toggle-field="' + targetId + '"]');

        $switch.toggleClass('mActive', isOn);
        $field.toggleClass('is-toggle-off', !isOn);

        $controls.each(function () {
            var $control = $(this);
            var $inputs = $control.is('input, select, textarea') ? $control : $control.find('input, select, textarea');
            $inputs.not('[type="checkbox"], [type="hidden"]').prop('disabled', !isOn);
            $control.find('select.ttbm_select2').each(function () {
                var $select = $(this);
                if ($select.data('select2')) {
                    $select.trigger('change.select2');
                }
            });
        });
    }

    function ttbmSyncGeneralInfoInlineToggle($checkbox) {
        var $field = $($checkbox).closest('.ttbm-gen-field--inline, .ttbm-hotel-review-card, .ttbm-hotel-field--availability');
        if (!$field.length) {
            return false;
        }
        ttbmSetInlineToggleFieldState($field, $($checkbox).is(':checked'));
        return true;
    }

    function ttbmSyncGeneralInfoToggle($checkbox, animate) {
        if (ttbmSyncGeneralInfoInlineToggle($checkbox)) {
            return;
        }

        var $cb = $($checkbox);
        if ($cb.closest('.ttbm-hotel-details-card').length) {
            var targetId = '#' + $cb.attr('name');
            $cb.siblings('[data-collapse-target="' + targetId + '"]').toggleClass('mActive', $cb.is(':checked'));
            return;
        }

        var $card = $cb.closest('.ttbm-general-info-card');
        if (!$card.length) {
            return;
        }

        var targetId = '#' + $cb.attr('name');
        var $sections = $card.find('[data-collapse="' + targetId + '"]');
        var $switch = $cb.siblings('[data-collapse-target="' + targetId + '"]');
        var $field = $cb.closest('.ttbm-gen-short-desc');
        var isOn = $cb.is(':checked');
        var useAnimation = animate !== false;

        if (isOn) {
            $switch.addClass('mActive');
            $sections.addClass('mActive');
            if (useAnimation) {
                $sections.stop(true, true).slideDown(250);
            } else {
                $sections.show();
            }
            $sections.find('input, select, textarea').filter(function () {
                return $(this).attr('type') !== 'checkbox';
            }).prop('disabled', false);
            $field.removeClass('is-toggle-off');
        } else {
            $switch.removeClass('mActive');
            $sections.removeClass('mActive');
            if (useAnimation) {
                $sections.stop(true, true).slideUp(250);
            } else {
                $sections.hide();
            }
            $sections.find('input, select, textarea').filter(function () {
                return $(this).attr('type') !== 'checkbox';
            }).prop('disabled', true);
            $field.addClass('is-toggle-off');
        }
    }

    function ttbmInitGeneralInfoToggles() {
        $('#ttbm_meta_box_panel .ttbm-general-info-card .ttbm-gen-field--inline').each(function () {
            if ($(this).closest('.ttbm-hotel-details-card').length) {
                return;
            }
            var $cb = $(this).find('.roundSwitchLabel input[type="checkbox"]').first();
            ttbmSetInlineToggleFieldState($(this), $cb.is(':checked'));
        });

        $('#ttbm_meta_box_panel .ttbm-hotel-details-card').find('.ttbm-gen-field--inline, .ttbm-hotel-review-card, .ttbm-hotel-field--availability').each(function () {
            var $cb = $(this).find('.roundSwitchLabel input[type="checkbox"]').first();
            ttbmSetInlineToggleFieldState($(this), $cb.is(':checked'));
        });

        $('#ttbm_meta_box_panel .ttbm-general-info-card .ttbm-gen-short-desc .roundSwitchLabel input[type="checkbox"]').each(function () {
            if ($(this).closest('.ttbm-hotel-details-card').length) {
                return;
            }
            ttbmSyncGeneralInfoToggle(this, false);
        });
    }

    $(document).on('change', '#ttbm_meta_box_panel .ttbm-general-info-card .roundSwitchLabel input[type="checkbox"]', function () {
        ttbmSyncGeneralInfoToggle(this, true);
    });

    // Prevent global collapse handler from re-toggling the same targets.
    $(document).on('click', '#ttbm_meta_box_panel .ttbm-general-info-card [data-collapse-target]', function (e) {
        e.stopImmediatePropagation();
    });

    var ttbmTourContentEditorId = 'ttbm_post_content_editor';
    var ttbmTourContentAutosaveAt = null;
    var ttbmTourContentAutosaveTimer = null;

    function ttbmGetTourContentEditorWrap() {
        return $('#wp-' + ttbmTourContentEditorId + '-wrap');
    }

    function ttbmGetTourDescriptionField() {
        return $('#ttbm_meta_box_panel .ttbm-tour-description-field');
    }

    function ttbmValidateTitle() {
        var $input = $('#ttbm_post_title');
        var $err = $('.ttbm-title-error');
        if (!$input.length) {
            return true;
        }

        if (!$input.val().trim()) {
            $input.addClass('is-invalid');
            $err.show();
            ttbmSetValidationFocus($input, '[data-tabs-target="#ttbm_general_info"]');
            return false;
        }

        $input.removeClass('is-invalid');
        $err.hide();
        return true;
    }

    window.ttbmValidateTitle = ttbmValidateTitle;
    window.ttbmLastValidationFocus = null;

    function ttbmIsHotelEditPage() {
        return $('body').hasClass('post-type-ttbm_hotel') && $('#ttbm_content').length > 0;
    }

    function ttbmResolveFieldTabSelector($field) {
        if (!$field || !$field.length) {
            return null;
        }
        var $tabPanel = $field.closest('.tabsItem[data-tabs]');
        if ($tabPanel.length) {
            var tabId = $tabPanel.attr('data-tabs');
            if (tabId) {
                return '[data-tabs-target="' + tabId + '"]';
            }
        }
        return null;
    }

    function ttbmExpandFieldCollapse($field) {
        var $collapsed = $field.closest('[data-collapse]');
        if (!$collapsed.length) {
            return;
        }
        if ($collapsed.is(':visible') && $collapsed.hasClass('mActive')) {
            return;
        }
        var collapseId = $collapsed.attr('data-collapse');
        if (!collapseId) {
            return;
        }
        var $toggle = $('[data-collapse-target="' + collapseId + '"]').first();
        if ($toggle.length && !$toggle.hasClass('mActive')) {
            if ($toggle.is('input[type="checkbox"]')) {
                if (!$toggle.is(':checked')) {
                    $toggle.prop('checked', true).trigger('change');
                }
            } else {
                $toggle.trigger('click');
            }
            return;
        }
        $collapsed.slideDown(250).addClass('mActive');
    }

    function ttbmSetValidationFocus($field, tabSelector) {
        if (!$field || !$field.length) {
            return;
        }
        if (!window.ttbmLastValidationFocus) {
            window.ttbmLastValidationFocus = {
                $field: $field,
                tab: tabSelector || ttbmResolveFieldTabSelector($field) || null
            };
        }
    }

    function ttbmFocusValidationTarget(target) {
        target = target || window.ttbmLastValidationFocus;
        if (!target || !target.$field || !target.$field.length) {
            return;
        }
        var tabSelector = target.tab || ttbmResolveFieldTabSelector(target.$field);
        var delay = tabSelector ? 240 : 0;
        if (tabSelector) {
            var $tabBtn = $(tabSelector).first();
            if ($tabBtn.length && !$tabBtn.hasClass('active')) {
                $tabBtn.trigger('click');
            }
        }
        window.setTimeout(function () {
            ttbmExpandFieldCollapse(target.$field);
            window.setTimeout(function () {
                var $field = target.$field;
                var $scrollTarget = $field;
                if (!$field.is(':visible')) {
                    $scrollTarget = $field.closest('tr, .ttbm-particular-date-card, section, .tabsItem, .ttbm-sb-card, label, td').first();
                }
                if (!$scrollTarget.length) {
                    $scrollTarget = $field;
                }
                if ($scrollTarget.length && $scrollTarget.offset()) {
                    $('html,body').animate({ scrollTop: Math.max(0, $scrollTarget.offset().top - 120) }, 250);
                }
                var $focusable = $field.is('input, select, textarea, button') ? $field : $field.find('input, select, textarea').filter(':visible').first();
                if ($focusable.length) {
                    $focusable.addClass('is-invalid').attr('aria-invalid', 'true').trigger('focus');
                    if (typeof $focusable[0].reportValidity === 'function') {
                        $focusable[0].reportValidity();
                    }
                }
            }, tabSelector ? 140 : 0);
        }, delay);
    }

    window.ttbmFocusValidationTarget = ttbmFocusValidationTarget;
    window.ttbmSetValidationFocus = ttbmSetValidationFocus;

    function ttbmValidateFeaturedImage() {
        var thumbId = parseInt($('#ttbm_thumb_id').val(), 10) || 0;
        var $card = $('#ttbm_featured_image_card');
        var $err = $('#ttbm_featured_image_error');
        if (!$card.length) {
            return true;
        }
        if (thumbId > 0) {
            $card.css({ 'border-color': '', 'box-shadow': '' });
            $err.hide();
            return true;
        }
        $card.css({ 'border-color': '#dc2626', 'box-shadow': '0 0 0 2px rgba(220,38,38,.15)' });
        $err.show();
        ttbmSetValidationFocus($card, null);
        return false;
    }

    function ttbmValidateHotelLocation() {
        var $toggle = $('#ttbm_meta_box_panel input[name="ttbm_display_hotel_location"]');
        if ($toggle.length && !$toggle.is(':checked')) {
            return true;
        }
        var $select = $('#ttbm_location_select');
        var $err = $('#ttbm_hotel_location_error');
        if (!$select.length) {
            return true;
        }
        if (!$select.val()) {
            $select.addClass('is-invalid').css({ 'border-color': '#dc2626', 'box-shadow': '0 0 0 2px rgba(220,38,38,.15)' });
            if ($err.length) {
                $err.show();
            }
            ttbmSetValidationFocus($select, '[data-tabs-target="#ttbm_general_info"]');
            return false;
        }
        $select.removeClass('is-invalid').css({ 'border-color': '', 'box-shadow': '' });
        if ($err.length) {
            $err.hide();
        }
        return true;
    }

    function ttbmValidateHotelMapLocation() {
        var $toggle = $('#ttbm_meta_box_panel input[name="ttbm_display_hotel_map"]');
        if ($toggle.length && !$toggle.is(':checked')) {
            return true;
        }
        var $input = $('#ttbm_hotel_map_location');
        var $err = $('#ttbm_hotel_map_location_error');
        if (!$input.length) {
            return true;
        }
        if (!$input.val().trim()) {
            $input.addClass('is-invalid').css({ 'border-color': '#dc2626', 'box-shadow': '0 0 0 2px rgba(220,38,38,.15)' });
            if ($err.length) {
                $err.show();
            }
            ttbmSetValidationFocus($input, '[data-tabs-target="#ttbm_settings_hotel_location"]');
            return false;
        }
        $input.removeClass('is-invalid').css({ 'border-color': '', 'box-shadow': '' });
        if ($err.length) {
            $err.hide();
        }
        return true;
    }

    function ttbmMarkHotelFieldError($field) {
        if ($field && $field.length) {
            $field.closest('td, label').addClass('ttbm-hotel-field-error');
            $field.addClass('is-invalid');
        }
    }

    function ttbmClearHotelFieldErrors() {
        $('#ttbm_hotel_rooms_error').hide().text('');
        $('.ttbm_item_insert .ttbm-hotel-field-error').removeClass('ttbm-hotel-field-error');
        $('.ttbm_item_insert input.is-invalid').removeClass('is-invalid');
    }

    function ttbmValidateHotelRooms() {
        window.ttbmLastValidationFocus = null;
        ttbmClearHotelFieldErrors();

        var $rows = $('.ttbm_item_insert > tr.ttbm_remove_area');
        var pricingTab = '[data-tabs-target="#ttbm_settings_pricing"]';
        var hasComplete = false;

        for (var i = 0; i < $rows.length; i++) {
            var $row = $($rows[i]);
            var name = ($row.find('input[name="ttbm_hotel_room_name[]"]').val() || '').trim();
            var price = ($row.find('input[name="ttbm_hotel_room_price[]"]').val() || '').trim();
            var qty = ($row.find('input[name="ttbm_hotel_room_qty[]"]').val() || '').trim();

            if (!name && !price && !qty) {
                continue;
            }

            var missing = [];
            var $focusField = null;
            if (!name) {
                missing.push('Room Name');
                $focusField = $row.find('input[name="ttbm_hotel_room_name[]"]');
                ttbmMarkHotelFieldError($focusField);
            }
            if (!price) {
                missing.push('Regular Price');
                if (!$focusField) {
                    $focusField = $row.find('input[name="ttbm_hotel_room_price[]"]');
                }
                ttbmMarkHotelFieldError($row.find('input[name="ttbm_hotel_room_price[]"]'));
            }
            if (!qty) {
                missing.push('Available Qty');
                if (!$focusField) {
                    $focusField = $row.find('input[name="ttbm_hotel_room_qty[]"]');
                }
                ttbmMarkHotelFieldError($row.find('input[name="ttbm_hotel_room_qty[]"]'));
            }

            if (missing.length) {
                $('#ttbm_hotel_rooms_error')
                    .text('Room row ' + (i + 1) + ' is incomplete. Required: ' + missing.join(', ') + '.')
                    .show();
                ttbmSetValidationFocus($focusField, pricingTab);
                return false;
            }

            hasComplete = true;
        }

        if (!hasComplete) {
            $('#ttbm_hotel_rooms_error')
                .text('At least one room with Room Name, Regular Price, and Available Qty is required.')
                .show();
            var $firstField = $rows.first().find('input[name="ttbm_hotel_room_name[]"]');
            if (!$firstField.length) {
                $firstField = $('.ttbm_item_insert').closest('section').find('.ttbm_add_item, .add_new_button, button').first();
            }
            ttbmSetValidationFocus($firstField.length ? $firstField : $('.ttbm_item_insert'), pricingTab);
            return false;
        }

        return true;
    }

    function ttbmValidateHotelFormBeforeSubmit() {
        window.ttbmLastValidationFocus = null;
        if (!ttbmValidateTitle()) {
            return false;
        }
        if (!ttbmValidateFeaturedImage()) {
            return false;
        }
        if (!ttbmValidateHotelLocation()) {
            return false;
        }
        if (!ttbmValidateHotelMapLocation()) {
            return false;
        }
        if (!ttbmValidateHotelRooms()) {
            return false;
        }
        return true;
    }

    window.ttbmValidateHotelFormBeforeSubmit = ttbmValidateHotelFormBeforeSubmit;

    function ttbmHandleServerValidationNotices() {
        var $ticketNotice = $('#ttbm-tickets-required-notice');
        if (!$ticketNotice.length) {
            return;
        }
        var rowNum = parseInt($ticketNotice.data('row'), 10) || 0;
        var $rows = $('.ttbm_insert_ticket_type > tr.ttbm_remove_area');
        var $row = rowNum > 0 ? $rows.eq(rowNum - 1) : $rows.first();
        if (!$row.length) {
            return;
        }
        $row.find('.ttbm-ticket-field-error').removeClass('ttbm-ticket-field-error');
        var $field = null;
        $row.find('input[name="ticket_type_name[]"], input[name="ticket_type_price[]"], input[name="ticket_type_qty[]"]').each(function () {
            var $input = $(this);
            if (!($input.val() || '').trim()) {
                $input.closest('.ttbm-ticket-col, .ttbm-ticket-name-field, td').addClass('ttbm-ticket-field-error');
                if (!$field) {
                    $field = $input;
                }
            }
        });
        if (!$field) {
            $field = $row.find('input[name="ticket_type_name[]"]').first();
        }
        $('#ttbm_ticket_types_error').text($ticketNotice.find('[data-message]').text() || '').show();
        window.ttbmLastValidationFocus = {
            $field: $field,
            tab: '[data-tabs-target="#ttbm_settings_pricing"]'
        };
        ttbmFocusValidationTarget();
    }

    function ttbmGetTravelType() {
        return $('.ttbm_settings_dates input[name="ttbm_travel_type"]').val() || 'fixed';
    }

    function ttbmSyncDateRequiredMarks() {
        var type = ttbmGetTravelType();
        var repeatType = ($('.ttbm_settings_dates input[name="ttbm_repeat_type"]').val() || '').trim();
        $('.ttbm_settings_dates .ttbm-date-required-mark').each(function () {
            var requiredType = $(this).data('ttbm-date-required');
            if ($(this).hasClass('ttbm-repeated-end-date-required')) {
                $(this).toggle(type === 'repeated' && repeatType === 'fixed');
                return;
            }
            $(this).toggle(requiredType === type);
        });
    }

    function ttbmClearDateFieldErrors() {
        $('.ttbm_settings_dates .ttbm-date-field-error').removeClass('ttbm-date-field-error');
        $('#ttbm_fixed_dates_error, #ttbm_particular_dates_error, #ttbm_repeated_dates_error').hide().text('');
    }

    function ttbmMarkDateFieldError($field) {
        if ($field && $field.length) {
            $field.addClass('ttbm-date-field-error');
        }
    }

    function ttbmValidateDates() {
        window.ttbmLastValidationFocus = null;
        ttbmClearDateFieldErrors();
        ttbmSyncDateRequiredMarks();

        var type = ttbmGetTravelType();
        var datesTab = '[data-tabs-target="#ttbm_settings_dates"]';

        if (type === 'fixed') {
            var fixedFields = [
                {
                    name: 'ttbm_travel_start_date',
                    label: 'Start Date',
                    $field: $('.ttbm-fixed-date-field:has(input[name="ttbm_travel_start_date"])')
                },
                {
                    name: 'ttbm_travel_end_date',
                    label: 'End Date',
                    $field: $('.ttbm-fixed-date-field:has(input[name="ttbm_travel_end_date"])')
                },
                {
                    name: 'ttbm_travel_start_time',
                    label: 'Start Time',
                    $field: $('.ttbm-fixed-date-field:has(input[name="ttbm_travel_start_time"])')
                },
                {
                    name: 'ttbm_travel_end_time',
                    label: 'End Time',
                    $field: $('.ttbm-fixed-date-field:has(input[name="ttbm_travel_end_time"])')
                }
            ];
            var missing = [];

            fixedFields.forEach(function (field) {
                var value = ($('input[name="' + field.name + '"]').val() || '').trim();
                if (!value) {
                    missing.push(field.label);
                    ttbmMarkDateFieldError(field.$field);
                    ttbmSetValidationFocus($('input[name="' + field.name + '"]'), datesTab);
                }
            });

            if (missing.length) {
                $('#ttbm_fixed_dates_error')
                    .text('Fixed tour dates require: ' + missing.join(', ') + '.')
                    .show();
                return false;
            }
        } else if (type === 'particular') {
            var $cards = $('.ttbm-particular-dates-list .ttbm-particular-date-card');
            var hasComplete = false;
            var hasPartial = false;

            $cards.each(function () {
                var $card = $(this);
                var startDate = ($card.find('input[name="ttbm_particular_start_date[]"]').val() || '').trim();
                var startTime = ($card.find('input[name="ttbm_particular_start_time[]"]').val() || '').trim();
                var endDate = ($card.find('input[name="ttbm_particular_end_date[]"]').val() || '').trim();

                if (!startDate && !startTime && !endDate) {
                    return;
                }

                if (!startDate || !startTime || !endDate) {
                    hasPartial = true;
                    $card.find('.ttbm-particular-date-card__field').addClass('ttbm-date-field-error');
                    if (!startDate) {
                        ttbmSetValidationFocus($card.find('input[name="ttbm_particular_start_date[]"]').first(), datesTab);
                    } else if (!startTime) {
                        ttbmSetValidationFocus($card.find('input[name="ttbm_particular_start_time[]"]').first(), datesTab);
                    } else {
                        ttbmSetValidationFocus($card.find('input[name="ttbm_particular_end_date[]"]').first(), datesTab);
                    }
                    return false;
                }

                hasComplete = true;
            });

            if (!hasComplete || hasPartial) {
                $('#ttbm_particular_dates_error')
                    .text(hasPartial
                        ? 'Each particular date entry must include check-in date, check-in time, and check-out date.'
                        : 'At least one particular date entry with check-in date, check-in time, and check-out date is required.')
                    .show();
                if (!window.ttbmLastValidationFocus) {
                    ttbmSetValidationFocus($('.ttbm-particular-dates-list .ttbm-particular-date-card').first().find('input').first(), datesTab);
                }
                return false;
            }
        } else if (type === 'repeated') {
            var repeatedFields = [
                {
                    name: 'ttbm_travel_repeated_start_date',
                    label: 'Start Date',
                    $field: $('.ttbm-repeated-date-field:has(input[name="ttbm_travel_repeated_start_date"])')
                },
                {
                    name: 'ttbm_travel_repeated_start_time',
                    label: 'Start Time',
                    selector: 'input.ttbm_travel_repeated_start_time',
                    $field: $('.ttbm-repeated-date-field:has(input.ttbm_travel_repeated_start_time)')
                },
                {
                    name: 'ttbm_repeat_type',
                    label: 'End Repeat Logic',
                    $field: $('.ttbm-repeat-end')
                }
            ];
            var repeatedMissing = [];

            repeatedFields.forEach(function (field) {
                var $input = field.selector ? $(field.selector) : $('input[name="' + field.name + '"]');
                var value = ($input.val() || '').trim();
                if (!value) {
                    repeatedMissing.push(field.label);
                    ttbmMarkDateFieldError(field.$field);
                    ttbmSetValidationFocus($input.first(), datesTab);
                }
            });

            var repeatType = ($('input[name="ttbm_repeat_type"]').val() || '').trim();
            if (repeatType === 'fixed') {
                var repeatedEndDate = ($('input[name="ttbm_travel_repeated_end_date"]').val() || '').trim();
                if (!repeatedEndDate) {
                    repeatedMissing.push('End Date');
                    ttbmMarkDateFieldError($('.ttbm-repeat-end'));
                    ttbmSetValidationFocus($('input[name="ttbm_travel_repeated_end_date"]').first(), datesTab);
                }
            }

            if (repeatedMissing.length) {
                $('#ttbm_repeated_dates_error')
                    .text('Repeated tour dates require: ' + repeatedMissing.join(', ') + '.')
                    .show();
                return false;
            }
        }

        return true;
    }

    window.ttbmValidateDates = ttbmValidateDates;
    window.ttbmSyncDateRequiredMarks = ttbmSyncDateRequiredMarks;

    function ttbmIsRegistrationEnabled() {
        return $('#ttbm_meta_box_panel input[name="ttbm_display_registration"]').is(':checked');
    }

    function ttbmIsGeneralTourType() {
        var type = $('#ttbm_meta_box_panel input[name="ttbm_type"]').val() || 'general';
        return type === 'general';
    }

    function ttbmBuildTicketHiddenText(name, tourId) {
        var slug = (name || '').replace(/[{}()<>+ ]/g, '_');
        return slug + (tourId ? '_' + tourId : '');
    }

    function ttbmSyncTicketHiddenText() {
        var tourId = $('#ttbm_meta_box_panel input[name="post_ID"]').val() || $('input[name="post_ID"]').val() || '';
        $('.ttbm_insert_ticket_type > tr.ttbm_remove_area').each(function () {
            var $row = $(this);
            var $name = $row.find('input[name="ticket_type_name[]"]');
            var $hidden = $row.find('input[name="ttbm_hidden_ticket_text[]"]');
            var nameVal = ($name.val() || '').trim();
            if (nameVal) {
                var hiddenVal = ttbmBuildTicketHiddenText(nameVal, tourId);
                $hidden.val(hiddenVal);
                $name.attr('data-input-text', hiddenVal);
            }
        });
    }
    window.ttbmSyncTicketHiddenText = ttbmSyncTicketHiddenText;

    function ttbmMarkTicketFieldError($field) {
        if ($field && $field.length) {
            $field.addClass('ttbm-ticket-field-error');
        }
    }

    function ttbmFirstMissingTicketField($row, name, price, qty) {
        if (!name) {
            return $row.find('input[name="ticket_type_name[]"]');
        }
        if (!price) {
            return $row.find('input[name="ticket_type_price[]"]');
        }
        if (!qty) {
            return $row.find('input[name="ticket_type_qty[]"]');
        }
        return $row.find('input[name="ticket_type_name[]"]');
    }

    function ttbmValidateTickets() {
        window.ttbmLastValidationFocus = null;
        $('#ttbm_ticket_types_error').hide().text('');
        $('.ttbm_settings_pricing .ttbm-ticket-field-error').removeClass('ttbm-ticket-field-error');

        if (!ttbmIsRegistrationEnabled() || !ttbmIsGeneralTourType()) {
            return true;
        }

        ttbmSyncTicketHiddenText();

        var $rows = $('.ttbm_insert_ticket_type > tr.ttbm_remove_area');
        var hasComplete = false;
        var pricingTab = '[data-tabs-target="#ttbm_settings_pricing"]';

        for (var i = 0; i < $rows.length; i++) {
            var $row = $($rows[i]);
            var name = ($row.find('input[name="ticket_type_name[]"]').val() || '').trim();
            var hidden = ($row.find('input[name="ttbm_hidden_ticket_text[]"]').val() || '').trim();
            var price = ($row.find('input[name="ticket_type_price[]"]').val() || '').trim();
            var qty = ($row.find('input[name="ticket_type_qty[]"]').val() || '').trim();

            if (!name && !price && !qty) {
                continue;
            }

            var missing = [];
            if (!name) {
                missing.push('Ticket Name');
                ttbmMarkTicketFieldError($row.find('.ttbm-ticket-name-field__input'));
            }
            if (!price) {
                missing.push('Reg. Price');
                ttbmMarkTicketFieldError($row.find('.ttbm-ticket-col--price'));
            }
            if (!qty) {
                missing.push('Capacity');
                ttbmMarkTicketFieldError($row.find('.ttbm-ticket-col--cap'));
            }
            if (!hidden && name) {
                hidden = ttbmBuildTicketHiddenText(name, $('#ttbm_meta_box_panel input[name="post_ID"]').val() || $('input[name="post_ID"]').val() || '');
                $row.find('input[name="ttbm_hidden_ticket_text[]"]').val(hidden);
            }

            if (missing.length) {
                $('#ttbm_ticket_types_error')
                    .text('Ticket type row ' + (i + 1) + ' is incomplete. Required: ' + missing.join(', ') + '.')
                    .show();
                ttbmSetValidationFocus(ttbmFirstMissingTicketField($row, name, price, qty), pricingTab);
                return false;
            }

            hasComplete = true;
        }

        if (!hasComplete) {
            $('#ttbm_ticket_types_error')
                .text('At least one ticket type with Ticket Name, Reg. Price, and Capacity is required when registration is enabled.')
                .show();
            ttbmSetValidationFocus($rows.first().find('input[name="ticket_type_name[]"]'), pricingTab);
            return false;
        }

        return true;
    }

    window.ttbmValidateTickets = ttbmValidateTickets;
    window.ttbmSyncTicketHiddenText = ttbmSyncTicketHiddenText;

    function ttbmValidateTourFormBeforeSubmit() {
        if (!$('#ttbm_meta_box_panel').length) {
            return true;
        }
        window.ttbmLastValidationFocus = null;
        if (typeof ttbmValidateTitle === 'function' && !ttbmValidateTitle()) {
            return false;
        }
        if (!ttbmValidateFeaturedImage()) {
            return false;
        }
        if (typeof ttbmValidateLocation === 'function' && !ttbmValidateLocation()) {
            return false;
        }
        if (typeof ttbmValidateDates === 'function' && !ttbmValidateDates()) {
            return false;
        }
        if (typeof ttbmSyncTicketHiddenText === 'function') {
            ttbmSyncTicketHiddenText();
        }
        if (typeof ttbmValidateTickets === 'function' && !ttbmValidateTickets()) {
            return false;
        }
        return true;
    }

    function ttbmValidateSettingsFormBeforeSubmit() {
        if (!$('#ttbm_meta_box_panel').length) {
            return true;
        }
        if (ttbmIsHotelEditPage()) {
            return ttbmValidateHotelFormBeforeSubmit();
        }
        return ttbmValidateTourFormBeforeSubmit();
    }

    window.ttbmValidateSettingsFormBeforeSubmit = ttbmValidateSettingsFormBeforeSubmit;

    function ttbmEnableToggleFieldsForSubmit() {
        if (!$('#ttbm_meta_box_panel').length) {
            return;
        }
        $('#ttbm_meta_box_panel .ttbm-hotel-details-card, #ttbm_meta_box_panel .ttbm-general-info-card')
            .find('input:disabled, select:disabled, textarea:disabled')
            .not('[type="checkbox"]')
            .prop('disabled', false);
        $('#ttbm_meta_box_panel')
            .find('[data-collapse="#ttbm_display_map"], [data-collapse="#ttbm_display_location"], [data-collapse="#ttbm_display_hotel_map"]')
            .find('input, select, textarea')
            .prop('disabled', false)
            .removeAttr('disabled');
        $('#ttbm_full_location_name_submit, #ttbm_hotel_map_location_submit, #ttbm_map_latitude_submit, #ttbm_map_longitude_submit, #ttbm_iframe_location, #ttbm_map_location, #map_latitude, #map_longitude, [name="ttbm_full_location_name_ui"], [name="ttbm_map_latitude_ui"], [name="ttbm_map_longitude_ui"]')
            .prop('disabled', false)
            .removeAttr('disabled');
        if (typeof window.ttbmSyncMapLocationFieldsForSubmit === 'function') {
            window.ttbmSyncMapLocationFieldsForSubmit();
        }
    }

    $(document).on('submit', 'form#post', function (e) {
        ttbmEnableToggleFieldsForSubmit();
        if (!ttbmValidateSettingsFormBeforeSubmit()) {
            e.preventDefault();
            ttbmInitGeneralInfoToggles();
            if (typeof window.ttbmFocusValidationTarget === 'function') {
                window.ttbmFocusValidationTarget();
            }
            return false;
        }
    });

    function ttbmInitRepeatedEndDateRules() {
        $(document).on('click mousedown', '.ttbm_settings_dates .ttbm-radio-btn .date_type, .ttbm_settings_dates .ttbm-repeat-end-date-field .date_type', function (e) {
            e.stopPropagation();
        });
    }

    $(document).on('click', '.ttbm_settings_dates .ttbm-tour-type-selector [data-group-radio]', function () {
        window.setTimeout(ttbmSyncDateRequiredMarks, 0);
    });

    $(document).on('click', '.ttbm_settings_dates .ttbm-radio-group [data-group-radio]', function () {
        window.setTimeout(ttbmSyncDateRequiredMarks, 0);
    });

    $(document).on('input change', '.ttbm_settings_dates input', function () {
        $(this).closest('.ttbm-date-field-error').removeClass('ttbm-date-field-error');
    });

    $(document).on('input change', '.ttbm_item_insert input', function () {
        $(this).removeClass('is-invalid').closest('.ttbm-hotel-field-error').removeClass('ttbm-hotel-field-error');
        $('#ttbm_hotel_rooms_error').hide();
    });

    $(document).on('change', '#ttbm_location_select', function () {
        if ($(this).val()) {
            $(this).removeClass('is-invalid').css({ 'border-color': '', 'box-shadow': '' });
            $('#ttbm_hotel_location_error, #ttbm_location_error').hide();
        }
    });

    $(document).on('input change', '#ttbm_hotel_map_location', function () {
        if ($(this).val().trim()) {
            $(this).removeClass('is-invalid').css({ 'border-color': '', 'box-shadow': '' });
            $('#ttbm_hotel_map_location_error').hide();
        }
    });

    $(document).on('input change', '.ttbm_settings_pricing .ttbm_insert_ticket_type input', function () {
        $(this).closest('.ttbm-ticket-field-error').removeClass('ttbm-ticket-field-error');
    });

    function ttbmDisableHiddenTemplateInputs() {
        $('.ttbm_hidden_content').find('input, select, textarea').prop('disabled', true);
    }

    $(document).ready(function () {
        ttbmInitGeneralInfoToggles();
        ttbmInitTourContentMediaButton();
        ttbmSyncDateRequiredMarks();
        ttbmDisableHiddenTemplateInputs();
        window.setTimeout(function () {
            ttbmInitRepeatedEndDateRules();
            ttbmHandleServerValidationNotices();
        }, 300);
    });

    function ttbmTourContentFormatAutosaveLabel(mins) {
        var $field = ttbmGetTourDescriptionField();
        if (!$field.length) {
            return '';
        }

        if (mins <= 0) {
            return $field.data('autosave-just-now') || 'just now';
        }

        var template = mins === 1
            ? ($field.data('autosave-min') || '%d min')
            : ($field.data('autosave-mins') || '%d mins');

        return template.replace('%d', String(mins));
    }

    function ttbmTourContentUpdateAutosaveLabel() {
        var $field = ttbmGetTourDescriptionField();
        var $label = $field.find('.ttbm-tour-description-field__autosave');
        if (!$field.length || !$label.length) {
            return;
        }

        if (!ttbmTourContentAutosaveAt) {
            $label.text($label.data('default-text') || 'Ready to save');
            return;
        }

        var mins = Math.max(0, Math.floor((Date.now() - ttbmTourContentAutosaveAt) / 60000));
        var ago = ttbmTourContentFormatAutosaveLabel(mins);
        var template = $field.data('autosave-label') || 'Auto-saved %s ago';
        $label.text(template.replace('%s', ago));
    }

    function ttbmTourContentMarkAutosaved() {
        ttbmTourContentAutosaveAt = Date.now();
        ttbmTourContentUpdateAutosaveLabel();
    }

    function ttbmTourContentBindAutosave() {
        var $field = ttbmGetTourDescriptionField();
        if (!$field.length || $field.data('autosave-bound')) {
            return;
        }

        $field.data('autosave-bound', true);

        $(document).on('input keyup change', '#' + ttbmTourContentEditorId, ttbmTourContentMarkAutosaved);

        if (ttbmTourContentAutosaveTimer) {
            window.clearInterval(ttbmTourContentAutosaveTimer);
        }

        ttbmTourContentAutosaveTimer = window.setInterval(ttbmTourContentUpdateAutosaveLabel, 30000);
        ttbmTourContentUpdateAutosaveLabel();
    }

    function ttbmTourContentBindTitleValidation() {
        $(document).on('blur', '#ttbm_post_title', ttbmValidateTitle);
        $(document).on('input', '#ttbm_post_title', function () {
            if ($(this).val().trim()) {
                $(this).removeClass('is-invalid');
                $('.ttbm-title-error').hide();
            }
        });
    }

    function ttbmMarkTourContentMediaPlaced($wrap, $mediaBtn) {
        $wrap.find('.wp-media-buttons').addClass('ttbm-media-buttons-moved');
        $wrap.addClass('ttbm-media-placement-ready');
        if ($mediaBtn && $mediaBtn.length) {
            $mediaBtn.addClass('ttbm-tour-media-btn');
        }
    }

    function ttbmMoveTourContentMediaAfterCloseTags(retryCount) {
        var retry = typeof retryCount === 'number' ? retryCount : 0;
        var $wrap = ttbmGetTourContentEditorWrap();
        if (!$wrap.length || !$wrap.hasClass('html-active')) {
            return;
        }

        var $mediaBtn = $wrap.find('#insert-media-button');
        var $closeBtn = $wrap.find('#qt_' + ttbmTourContentEditorId + '_close');
        if (!$mediaBtn.length) {
            if (retry < 30) {
                window.setTimeout(function () {
                    ttbmMoveTourContentMediaAfterCloseTags(retry + 1);
                }, 50);
            }
            return;
        }

        if ($closeBtn.length) {
            if (!$mediaBtn.prev().is($closeBtn)) {
                $closeBtn.after($mediaBtn);
            }
        } else {
            var $quicktags = $wrap.find('.quicktags-toolbar');
            if ($quicktags.length && !$mediaBtn.parent().is($quicktags)) {
                $quicktags.append($mediaBtn);
            } else if (retry < 30) {
                window.setTimeout(function () {
                    ttbmMoveTourContentMediaAfterCloseTags(retry + 1);
                }, 50);
                return;
            }
        }

        ttbmMarkTourContentMediaPlaced($wrap, $mediaBtn);
    }

    function ttbmMoveTourContentMediaAfterAdv(retryCount) {
        var retry = typeof retryCount === 'number' ? retryCount : 0;
        var $wrap = ttbmGetTourContentEditorWrap();
        if (!$wrap.length || !$wrap.hasClass('tmce-active')) {
            return;
        }

        if (typeof tinymce === 'undefined' || !tinymce.get(ttbmTourContentEditorId)) {
            if (retry < 30) {
                window.setTimeout(function () {
                    ttbmMoveTourContentMediaAfterAdv(retry + 1);
                }, 50);
            }
            return;
        }

        var $mediaBtn = $wrap.find('#insert-media-button');
        var $advBtn = $wrap.find('.mce-toolbar-grp .mce-i-wp_adv').first().closest('.mce-btn');
        if (!$mediaBtn.length || !$advBtn.length) {
            if (retry < 30) {
                window.setTimeout(function () {
                    ttbmMoveTourContentMediaAfterAdv(retry + 1);
                }, 50);
            }
            return;
        }

        var $toolbar = $advBtn.closest('.mce-toolbar');
        if ($toolbar.length && !$mediaBtn.parent().is($toolbar)) {
            $toolbar.append($mediaBtn);
        } else if (!$mediaBtn.prev().is($advBtn)) {
            $advBtn.after($mediaBtn);
        }

        ttbmMarkTourContentMediaPlaced($wrap, $mediaBtn);
    }

    function ttbmInitTourContentMediaPlacement() {
        var $wrap = ttbmGetTourContentEditorWrap();
        if (!$wrap.length) {
            return;
        }

        $wrap.find('.mce-tinymce').css('visibility', 'visible');

        if ($wrap.hasClass('tmce-active')) {
            ttbmMoveTourContentMediaAfterAdv(0);
        } else if ($wrap.hasClass('html-active')) {
            ttbmMoveTourContentMediaAfterCloseTags(0);
        }
    }

    function ttbmInitTourContentMediaButton() {
        ttbmInitTourContentMediaPlacement();
        ttbmTourContentBindTitleValidation();
        ttbmTourContentBindAutosave();
    }

    $(document).on('tinymce-editor-init', function (event, editor) {
        if (editor.id === ttbmTourContentEditorId) {
            editor.on('change keyup', ttbmTourContentMarkAutosaved);
            if (editor.theme && typeof editor.theme.resizeTo === 'function') {
                editor.theme.resizeTo(null, 200);
            }
            ttbmMoveTourContentMediaAfterAdv(0);
        }
    });

    $(document).on('click', '#wp-' + ttbmTourContentEditorId + '-wrap .wp-switch-editor', function () {
        var $wrap = ttbmGetTourContentEditorWrap();
        $wrap.removeClass('ttbm-media-placement-ready');
        window.setTimeout(ttbmInitTourContentMediaPlacement, 0);
    });

    $(document).on('click', '#wp-' + ttbmTourContentEditorId + '-wrap .mce-i-wp_adv', function () {
        window.setTimeout(function () {
            ttbmMoveTourContentMediaAfterAdv(0);
        }, 0);
    });

})(jQuery);

