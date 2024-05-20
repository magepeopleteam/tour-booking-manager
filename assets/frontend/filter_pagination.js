(function ($) {
	"use strict";
	$(document).ready(function () {
		load_pagination_initial_item();
	});
	$(document).on('click', '.ttbm_filter_area .ttbm_grid_view', function () {
		let parent = $(this).closest('.ttbm_filter_area');
		let all_item = parent.find('.all_filter_item');
		placeholderLoader(all_item);
		$(this).attr('disabled', '');
		all_item.find('.modern').toggleClass('grid modern').promise().done(function () {
			parent.find('.ttbm_list_view').removeAttr('disabled');
			parent.find('.ttbm_explore_button').slideToggle(250);
			placeholderLoaderRemove(all_item);
		});
	});
	$(document).on('click', '.ttbm_filter_area .ttbm_list_view', function () {
		let parent = $(this).closest('.ttbm_filter_area');
		let all_item = parent.find('.all_filter_item');
		placeholderLoader(all_item);
		$(this).attr('disabled', '');
		all_item.find('.grid').toggleClass('grid modern').promise().done(function () {
			parent.find('.ttbm_grid_view').removeAttr('disabled');
			parent.find('.ttbm_explore_button').slideToggle(250);
			placeholderLoaderRemove(all_item);
		});
	});
	//************************************//
	function search_filter_initial(parent) {
		parent.find('.all_filter_item').slideDown('fast');
		parent.find('.all_filter_item .filter_item').each(function () {
			$(this).removeClass('search_of').removeClass('search_on').removeClass('dNone');
		}).promise().done(function () {
			load_pagination(parent, 0);
		});
		parent.find('.search_result_empty').slideUp('fast');
	}
	function search_filter_exit(parent, result) {
		if (result > 0) {
			parent.find('.all_filter_item').slideDown('fast');
			parent.find('.search_result_empty').slideUp('fast');
		} else {
			parent.find('.all_filter_item').slideUp('fast');
			parent.find('.search_result_empty').slideDown('fast');
		}
	}
	function filter_item_config(target, active) {
		let result = 0;
		if (active === 2) {
			result++;
			target.addClass('search_on').removeClass('search_of').removeClass('dNone');
		} else {
			target.addClass('search_of').removeClass('search_on').removeClass('dNone');
		}
		return result;
	}
	let ttbm_filter_item = {
		title_filter: 'data-title',
		type_filter: 'data-type',
		category_filter: 'data-category',
		organizer_filter: 'data-organizer',
		location_filter: 'data-location',
		location_filter_multiple: 'data-location',
		country_filter: 'data-country',
		duration_filter: 'data-duration',
		duration_filter_multiple: 'data-duration',
		feature_filter_multiple: 'data-feature',
		tag_filter_multiple: 'data-tag',
		activity_filter: 'data-activity',
		activity_filter_multiple: 'data-activity',
		month_filter: 'data-month',
		date_range_filter: 'data-date',
	};
	//************Filter*************//
	$(document).on('change', '.ttbm_filter .formControl', function (e) {
		e.preventDefault();
		let parent = $(this).closest('.ttbm_filter_area');
		list_filter(parent);
	});
	function list_filter(parent) {
		let result = 0;
		if (filter_value_exit(parent)) {
			parent.find('.all_filter_item .filter_item').each(function () {
				result = result + get_item_result(parent, $(this));
			}).promise().done(function () {
				search_filter_exit(parent, result);
			}).promise().done(function () {
				load_pagination(parent, 0);
			});
		} else {
			search_filter_initial(parent);
		}
	}
	function get_item_result(parent, item) {
		let active = 3;
		active = active > 0 ? Math.min(active, filter_text(parent, item, 'title_filter', active)) : active;
		active = active > 0 ? Math.min(active, filter_text(parent, item, 'type_filter', active)) : active;
		active = active > 0 ? Math.min(active, filter_single_in_multi(parent, item, 'category_filter', active)) : active;
		active = active > 0 ? Math.min(active, filter_single_in_multi(parent, item, 'organizer_filter', active)) : active;
		active = active > 0 ? Math.min(active, filter_text(parent, item, 'location_filter', active)) : active;
		active = active > 0 ? Math.min(active, filter_multi_in_single(parent, item, 'location_filter_multiple', active)) : active;
		active = active > 0 ? Math.min(active, filter_text(parent, item, 'country_filter', active)) : active;
		active = active > 0 ? Math.min(active, filter_text(parent, item, 'duration_filter', active)) : active;
		active = active > 0 ? Math.min(active, filter_multi_in_single(parent, item, 'duration_filter_multiple', active)) : active;
		active = active > 0 ? Math.min(active, filter_multi_in_multi(parent, item, 'feature_filter_multiple', active)) : active;
		active = active > 0 ? Math.min(active, filter_multi_in_multi(parent, item, 'tag_filter_multiple', active)) : active;
		active = active > 0 ? Math.min(active, filter_single_in_multi(parent, item, 'activity_filter', active)) : active;
		active = active > 0 ? Math.min(active, filter_multi_in_multi(parent, item, 'activity_filter_multiple', active)) : active;
		active = active > 0 ? Math.min(active, filter_single_in_multi(parent, item, 'month_filter', active)) : active;
		return filter_item_config(item, active);
	}
	//*********************//
	function filter_value_exit(parent) {
		for (let name in ttbm_filter_item) {
			let value = parent.find('[name="' + name + '"]').val();
			if (value) {
				return true;
			}
		}
		return false;
	}
	function filter_text(parent, item, name, active) {
		let filter_values = parent.find('[name="' + name + '"]').val();
		if (filter_values) {
			let value = item.attr(ttbm_filter_item[name]).toString();
			active = (value && value.match(new RegExp(filter_values, "i"))) ? 2 : 0;
		}
		//console.log(parent + " "+ item + " " + name + " " + active );
		return active;
	}
	function filter_single_in_multi(parent, item, name, active) {
		let filter_values = parent.find('[name="' + name + '"]').val();
		if (filter_values) {
			let value = item.attr(ttbm_filter_item[name]).toString();
			value = value.split(",");
			active = (value.indexOf(filter_values) !== -1) ? 2 : 0;
		}
		//console.log(parent + " "+ item + " " + name + " " + active );
		return active;
	}
	function filter_multi_in_single(parent, item, name, active) {
		let filter_values = parent.find('[name="' + name + '"]').val();
		if (filter_values) {
			filter_values = filter_values.split(",");
			let value = item.attr(ttbm_filter_item[name]).toString();
			active = (filter_values.indexOf(value) !== -1) ? 2 : 0;
		}
		//console.log(parent + " "+ item + " " + name + " " + active );
		return active;
	}
	function filter_multi_in_multi(parent, item, name, active) {
		let filter_values = parent.find('[name="' + name + '"]').val();
		if (filter_values) {
			let result = 0;
			filter_values = filter_values.split(",");
			let value = item.attr(ttbm_filter_item[name]).toString();
			value = value.split(",");
			value.forEach(function (item) {
				if (filter_values.indexOf(item) !== -1) {
					result = 2;
				}
			});
			active = result;
		}
		//console.log(parent + " "+ item + " " + name + " " + active );
		return active;
	}
	//************Pagination*************//
	$(document).on('click', '.ttbm_filter_area .pagination_area [data-pagination]', function (e) {
		e.preventDefault();
		let pagination_page = $(this).data('pagination');
		let parent = $(this).closest('.ttbm_filter_area');
		parent.find('[data-pagination]').removeClass('active_pagination');
		$(this).addClass('active_pagination').promise().done(function () {
			load_pagination(parent, pagination_page);
		}).promise().done(function () {
			loadBgImage();
		});
	});
	$(document).on('click', '.ttbm_filter_area .pagination_area .pagination_load_more', function () {
		let pagination_page = parseInt($(this).attr('data-load-more'));
		let parent = $(this).closest('.ttbm_filter_area');
		let item_class = get_item_class(parent);
		if (parent.find(item_class + ':hidden').size() > 0) {
			pagination_page = pagination_page + 1;
		} else {
			pagination_page = 0;
		}
		$(this).attr('data-load-more', pagination_page).promise().done(function () {
			load_pagination(parent, pagination_page);
		}).promise().done(function () {
			lode_more_init(parent);
		}).promise().done(function () {
			loadBgImage();
		});
	});
	function lode_more_init(parent) {
		let item_class = get_item_class(parent);
		if (parent.find(item_class + ':hidden').length === 0) {
			parent.find('[data-load-more]').attr('disabled', 'disabled');
		} else {
			parent.find('[data-load-more]').removeAttr('disabled');
		}
	}
	function load_more_scroll(parent, pagination_page) {
		let per_page_item = parseInt(parent.find('input[name="pagination_per_page"]').val());
		let start_item = pagination_page > 0 ? pagination_page * per_page_item : 0;
		let item_class = get_item_class(parent);
		let target = parent.find(item_class + ':nth-child(' + (start_item + 1) + ')');
		pageScrollTo(target);
	}
	function load_pagination_initial_item() {
		$('.ttbm_filter_area').each(function () {
			list_filter($(this))
		});
	}
	function load_pagination(parent, pagination_page) {
		let all_item = parent.find('.all_filter_item');
		let per_page_item = parseInt(parent.find('input[name="pagination_per_page"]').val());
		let pagination_type = parent.find('input[name="pagination_style"]').val();
		let start_item = pagination_page > 0 ? pagination_page * per_page_item : 0;
		let end_item = pagination_page > 0 ? start_item + per_page_item : per_page_item;
		let item = 0;
		let items_class = get_item_class(parent);
		placeholderLoader(all_item);
		if (pagination_type === 'load_more') {
			start_item = 0;
		} else {
			let all_item_height = all_item.outerHeight();
			all_item.css({"height": all_item_height, "overflow": "hidden"});
		}
		parent.find(items_class).each(function () {
			if (item >= start_item && item < end_item) {
				if ($(this).is(':hidden')) {
					$(this).slideDown(200);
				}
			} else {
				$(this).slideUp('fast');
			}
			item++;
		}).promise().done(function () {
			all_item.css({"height": "auto", "overflow": "inherit"}).promise().done(function () {
				loadBgImage();
				filter_qty_palace(parent, items_class);
				pagination_management(parent, pagination_page);
				placeholderLoaderRemove(all_item);
			});
		});
	}
	function pagination_management(parent, pagination_page) {
		let pagination_type = parent.find('input[name="pagination_style"]').val();
		let per_page_item = parseInt(parent.find('input[name="pagination_per_page"]').val());
		let total_item = parent.find(get_item_class(parent)).length;
		if (total_item <= per_page_item) {
			parent.find('.pagination_area').slideUp(200);
		} else {
			parent.find('.pagination_area').slideDown(200);
			if (pagination_type === 'load_more') {
				parent.find('[data-load-more]').attr('data-load-more', pagination_page);
				lode_more_init(parent);
			} else {
				let total_item = parent.find(get_item_class(parent)).length;
				ttbm_pagination_page_management(parent, pagination_page, total_item);
			}
		}
	}
	function get_item_class(parent, items = '.filter_item') {
		if (parent.find('.filter_item.search_on').length > 0 || parent.find('.filter_item.search_of').length > 0) {
			items = '.filter_item.search_on';
			parent.find('.filter_item.search_of').slideUp('fast');
		}
		return items;
	}
	function filter_qty_palace(parent, item_class) {
		parent.find('.qty_count').html($(parent).find(item_class + ':visible').length);
		parent.find('.total_filter_qty').html($(parent).find(item_class).length);
	}
}(jQuery));
function ttbm_pagination_page_management(parent, pagination_page, total_item) {
	let per_page_item = parseInt(parent.find('input[name="pagination_per_page"]').val());
	let total_active_page = Math.floor(total_item / per_page_item) + ((total_item % per_page_item) > 0 ? 1 : 0);
	let page_limit_start = (pagination_page > 2) ? (pagination_page - 2) : 0;
	let page_limit_end = (pagination_page > 2) ? (pagination_page + 2) : 4;
	let limit_dif = total_active_page - pagination_page;
	if (total_active_page > 5 && limit_dif < 3) {
		page_limit_start = page_limit_start - ((limit_dif > 1) ? 1 : 2);
	}
	let total_page = parent.find('[data-pagination]').length;
	for (let i = 0; i < total_page; i++) {
		if (i < total_active_page && i >= page_limit_start && i <= page_limit_end) {
			parent.find('[data-pagination="' + i + '"]').slideDown(200);
		} else {
			parent.find('[data-pagination="' + i + '"]').slideUp(200);
		}
	}
	if (pagination_page > 0) {
		parent.find('.page_prev').removeAttr('disabled');
	} else {
		parent.find('.page_prev').prop('disabled', true);
	}
	if (pagination_page > 2 && total_active_page > 5) {
		parent.find('.ellipse_left').slideDown(200);
	} else {
		parent.find('.ellipse_left').slideUp(200);
	}
	if (pagination_page < total_active_page - 3 && total_active_page > 5) {
		parent.find('.ellipse_right').slideDown(200);
	} else {
		parent.find('.ellipse_right').slideUp(200);
	}
	if (pagination_page < total_active_page - 1) {
		parent.find('.page_next').removeAttr('disabled');
	} else {
		parent.find('.page_next').prop('disabled', true);
	}
	return true;
}