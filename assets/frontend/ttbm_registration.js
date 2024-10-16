function get_ttbm_ticket(current, date = '') {
	let parent = current.closest('.ttbm_registration_area');
	let tour_id = parent.find('[name="ttbm_id"]').val();
	let tour_date = date ? date : parent.find('[name="ttbm_date"]').val();
	let repeat_target = parent.find('[name="ttbm_date"]');
	let tour_time = '';
	if (repeat_target.length > 0) {
		tour_time = parent.find('[name="ttbm_select_time"]').val();
		tour_date = tour_time ? tour_time : repeat_target.val();
	}
	let target = jQuery('.ttbm_booking_panel');
	jQuery.ajax({
		type: 'POST',
		url: mp_ajax_url,
		data: {
			"action": "get_ttbm_ticket",
			"tour_id": tour_id,
			"tour_date": tour_date,
		},
		beforeSend: function () {
			if (jQuery('.mp_tour_ticket_form').length > 0) {
				placeholderLoader(parent);
			} else {
				simpleSpinner(parent);
			}
		},
		success: function (data) {
			target.html(data).slideDown('fast').promise().done(function () {
				// parent.find('.ttbm_check_ability').slideUp('fast').promise().done(function () {
					// ttbm_price_calculation(parent);
				// });
				ttbm_price_calculation(parent);
				placeholderLoaderRemove(parent);
				simpleSpinnerRemove(parent);
				get_ttbm_sold_ticket(parent, tour_id, tour_date);
			});
		}
	});
}

function get_ttbm_sold_ticket(parent, tour_id, tour_date) {
	let target = jQuery('.ttbm_available_seat_area');
	jQuery.ajax({
		type: 'POST',
		url: mp_ajax_url,
		data: {
			"action": "get_ttbm_sold_ticket",
			"tour_id": tour_id,
			"tour_date": tour_date,
		},
		beforeSend: function () {
			dLoader_xs(target);
		},
		success: function (data) {
			target.find('.ttbm_available_seat').html(data);
			dLoaderRemove(target);
		}
	});
}

(function ($) {
	"use strict";
	$(document).on('change', '.ttbm_registration_area [name="ttbm_date"]', function () {
		let parent = $(this).closest('.ttbm_registration_area');
		let time_slot = parent.find('.ttbm_select_time_area');
		parent.find('.ttbm_booking_panel').html('');
		if (time_slot.length > 0) {
			return true;
		} else {
			get_ttbm_ticket($(this));
		}
	});
	$(document).on('click', '.get_particular_ticket', function () {
		let current = $(this).closest('.particular_date_area');
		let parent = $(this).closest('.ttbm_registration_area');
		let tour_date = current.find('[name="ttbm_particular_date"]').val();
		let target = current.find('.ttbm_booking_panel');
		let tour_id = parent.find('[name="ttbm_id"]').val();
		let target_form = target.find('.mp_tour_ticket_form');
		if (target_form.length < 1) {
			$('#particular_item_area').find('.ttbm_booking_panel').slideUp('fast');
			jQuery.ajax({
				type: 'POST',
				url: mp_ajax_url,
				data: {
					"action": "get_ttbm_ticket",
					"tour_id": tour_id,
					"tour_date": tour_date
				},
				beforeSend: function () {
					target.slideDown('fast');
					simpleSpinner(target);
				},
				success: function (data) {
					target.html(data).promise().done(function () {
						ttbm_price_calculation(target);
						get_ttbm_sold_ticket(parent, tour_id, tour_date);
					});
				}
			});
		} else {
			get_ttbm_sold_ticket(parent, tour_id, tour_date);
			if (target.is(':visible')) {
				target.slideUp('fast');
			} else {
				$('#particular_item_area').find('.ttbm_booking_panel').slideUp('fast');
				target.slideDown('fast');
			}
		}
	});
	$(document).on("click", ".ttbm_registration_area .ttbm_check_ability", function () {
		let parent = $(this).closest('.ttbm_registration_area');
		let time_slot = parent.find('.ttbm_select_time_area');
		if (time_slot.length > 0) {
			if (parent.find('[name="ttbm_select_time"]').val()) {
				get_ttbm_ticket($(this));
			} else if (parent.find('[name="ttbm_select_time"]').length > 0) {
				alert('Please Select Time');
			} else {
				parent.find('#ttbm_select_date').trigger('focus');
			}
		} else {
			parent.find('#ttbm_select_date').trigger('focus');
		}
	});
	$(document).on('change', '.ttbm_registration_area [name="ttbm_tour_hotel_list"]', function () {
		let parent = $(this).closest('.ttbm_registration_area');
		let availability = parent.find('.ttbm_check_ability');
		if (availability.length < 1) {
			get_ttbm_ticket($(this));
		}
	});
	$(document).on('click', '.ttbm_short_list_more', function () {
		let parent = $(this).closest('.item_section');
		let target = parent.find('.small_box.dNone');
		if (target.is(':visible')) {
			target.slideUp(250);
		} else {
			target.slideDown(250);
		}
	});
	$('input[name="ttbm_hotel_date_range"]').daterangepicker({
		autoUpdateInput: false,
		minDate: moment(),
		"autoApply": true,
		"locale": {
			"format": "YYYY/MM/DD"
		}
	}).on('apply.daterangepicker', function (ev, picker) {
		$(this).val(picker.startDate.format('YYYY/MM/DD') + '    -    ' + picker.endDate.format('YYYY/MM/DD'));
		$('.ttbm_hotel_area').slideUp('fast').find('.ttbm_booking_panel').html('');
	}).on('cancel.daterangepicker', function () {
		$(this).val('');
	});
	$(document).on('click', '.ttbm_hotel_check_availability', function () {
		let target = $('[name="ttbm_hotel_date_range"]');
		let date_range = target.val();
		if (date_range) {
			$('.ttbm_hotel_area').slideDown('fast');
			loadBgImage();
		} else {
			$('.ttbm_hotel_area').slideUp('fast');
			target.trigger('focus');
		}
	});
	$(document).on('click', '.ttbm_hotel_open_room_list', function () {
		let current = $(this).closest('.ttbm_hotel_item');
		let tour_id = current.find('[name="ttbm_id"]').val();
		let hotel_id = current.find('[name="ttbm_hotel_id"]').val();
		let date_range = $('[name="ttbm_hotel_date_range"]').val();
		if ($('[name="ttbm_hotel_date_range"]').length > 1) {
			date_range = $(this).closest('.particular_date_area').find('[name="ttbm_hotel_date_range"]').val();
		}
		let target = current.find('.ttbm_booking_panel');
		let target_form = target.find('.mp_tour_ticket_form');
		if (date_range) {
			if (target_form.length < 1) {
				$('.ttbm_hotel_area').find('.ttbm_booking_panel').slideUp('fast');
				jQuery.ajax({
					type: 'POST',
					url: mp_ajax_url,
					data: {
						"action": "get_ttbm_hotel_room_list",
						"tour_id": tour_id,
						"hotel_id": hotel_id,
						"date_range": date_range
					},
					beforeSend: function () {
						target.slideDown('fast');
						simpleSpinner(target);
					},
					success: function (data) {
						target.html(data).promise().done(function () {
							ttbm_price_calculation(target);
						});
					}
				});
			} else {
				if (target.is(':visible')) {
					target.slideUp('fast');
				} else {
					$('.ttbm_hotel_area').find('.ttbm_booking_panel').slideUp('fast');
					target.slideDown('fast');
				}
			}
		}
	});
	$(document).on('click', '.get_particular_hotel', function () {
		let parent = $(this).closest('.particular_date_area');
		parent.find('.ttbm_hotel_area').slideToggle(250);
		loadBgImage();
	});
	$(document).on('click', '.ttbm_go_particular_booking', function () {
		pageScrollTo($('#particular_item_area'));
	});
}(jQuery));