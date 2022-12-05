(function ($) {
	"use strict";
	$(document).on('click', 'button.mp_input_add_icon_button', function () {
		$(this).attr('data-icon-target', 'icon');
		$('body').addClass('noScroll').find('.add_icon_list_popup').addClass('in');
	});
	$(document).on('click', '.add_icon_list_popup .popupClose', function () {
		let parent = $(this).closest('.add_icon_list_popup');
		parent.removeClass('in');
		$('body').removeClass('noScroll');
		$('[data-icon-target]').removeAttr('data-icon-target');
		parent.find('[data-icon-menu="all_item"]').trigger('click');
		parent.find('.iconItem').removeClass('active');
	});
	$(document).on('click', '.add_icon_list_popup .iconItem', function () {
		let target = $('[data-icon-target]');
		let icon_class = $(this).data('icon-class');
		target.find('span.remove_input_icon').slideDown('fast');
		target.find('span[data-empty-text]').removeAttr('class').addClass(icon_class).html('');
		target.find('input').val(icon_class);
		let targetParent = $(this).closest('.add_icon_list_popup');
		targetParent.find('.iconItem').removeClass('active');
		$(this).addClass('active');
		targetParent.find('.popupClose').trigger('click');
	});
	$(document).on('click', 'button.mp_input_add_icon_button span.remove_input_icon', function (e) {
		e.stopImmediatePropagation();
		let parent = $(this).closest('button.mp_input_add_icon_button');
		let text = parent.find('span[data-empty-text]').data('empty-text');
		parent.find('span[data-empty-text]').removeAttr('class').html(text);
		parent.find('input').val('');
		$(this).slideUp('fast');
	});
	$(document).on('click', '.add_icon_list_popup [data-icon-menu]', function () {
		if (!$(this).hasClass('active')) {
			let target = $(this);
			let tabsTarget = target.data('icon-menu');
			let targetParent = target.closest('.add_icon_list_popup');
			targetParent.find('[data-icon-menu]').removeClass('active');
			target.addClass('active');
			targetParent.find('[data-icon-list]').each(function () {
				let targetItem = $(this).data('icon-list');
				if (tabsTarget === 'all_item' || targetItem === tabsTarget) {
					$(this).slideDown(250);
				} else {
					$(this).slideUp(250);
				}
			});
		}
		return false;
	});
}(jQuery));