function ttbm_load_sortable_datepicker(parent, item) {
    if (parent.find(".ttbm_item_insert_before").length > 0) {
        jQuery(item)
            .insertBefore(parent.find(".ttbm_item_insert_before").first())
            .promise()
            .done(function () {
                parent.find(".ttbm_sortable_area").sortable({
                    handle: jQuery(this).find(".ttbm_sortable_button"),
                });
                ttbm_load_date_picker(parent);
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
        if (!$customBtn.length || !$customInput.length) {
            return;
        }
        if ($customBtn.hasClass("active")) {
            $customInput.removeClass("dNone").show().focus();
        } else {
            $customInput.addClass("dNone").hide();
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
        if (confirm("Are You Sure , Remove this row ? \n\n 1. Ok : To Remove . \n 2. Cancel : To Cancel .")) {
            $(this).closest(".ttbm_remove_area").slideUp(250).remove();
            return true;
        } else {
            return false;
        }
    });
    //=========Add Setting Item==============//
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

        if (isOn && typeof ensureLocationMap === 'function') {
            setTimeout(ensureLocationMap, 400);
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

        if (isOn && typeof ensureLocationMap === 'function') {
            setTimeout(ensureLocationMap, 400);
        }
    }

    $(document).on('change', '#ttbm_meta_box_panel input[name="ttbm_display_location"]', function () {
        if (ttbmLocationMapSyncing) {
            return;
        }
        ttbmOnLocationToggleChanged($(this).is(':checked'));
    });

    $(document).on('change', '#ttbm_meta_box_panel input[name="ttbm_display_map"]', function () {
        if (ttbmLocationMapSyncing) {
            return;
        }
        ttbmOnMapToggleChanged($(this).is(':checked'));
    });

})(jQuery);

