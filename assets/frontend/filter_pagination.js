(function ($) {
	"use strict";

	function ttbmBuildTopSearchOptionMarkup(option, compact) {
		const $option = option.element ? $(option.element) : null;
		const title = option.text ? $.trim(option.text) : '';
		let subtitle = '';
		if ($option && $option.length) {
			subtitle = $option.attr('data-subtitle') || $option.data('ttbmSubtitle') || '';
		}
		if (!subtitle && option.id !== undefined && option.id !== null && option.id !== '') {
			const $fallback = $('.ttbm-top-search-modern select[name="location_filter"] option[value="' + option.id + '"], .ttbm-top-search-modern select[name="activity_filter"] option[value="' + option.id + '"]');
			if ($fallback.length) {
				subtitle = $fallback.attr('data-subtitle') || $fallback.data('ttbmSubtitle') || '';
			}
		}
		subtitle = subtitle ? String(subtitle).trim() : '';
		const compactClass = compact ? ' ttbm-search-opt--compact' : '';

		if (!subtitle) {
			return '<span class="ttbm-search-opt' + compactClass + '"><span class="ttbm-search-opt__title">' + title + '</span></span>';
		}

		return '<span class="ttbm-search-opt' + compactClass + '">' +
			'<span class="ttbm-search-opt__title">' + title + '</span>' +
			'<span class="ttbm-search-opt__subtitle">' + subtitle + '</span>' +
			'</span>';
	}

	function ttbmCacheTopSearchOptionSubtitles($select) {
		$select.find('option').each(function () {
			const $opt = $(this);
			const subtitle = ($opt.attr('data-subtitle') || '').trim();
			if (subtitle) {
				$opt.data('ttbmSubtitle', subtitle);
			}
		});
	}

	function ttbmInitTopSearchSelects() {
		if (typeof $.fn.select2 !== 'function') {
			return;
		}

		const $selects = $('.ttbm-top-search-modern select[name="location_filter"], .ttbm-top-search-modern select[name="activity_filter"], .ttbm-top-search-modern .ttbm-top-search-select');

		$selects.each(function () {
			const $select = $(this);
			if ($select.hasClass('select2-hidden-accessible')) {
				return;
			}

			const dropdownModifier = $select.is('[name="location_filter"]') || $select.hasClass('ttbm-top-search-select--location')
				? ' ttbm-top-search-select2-dropdown--location'
				: ' ttbm-top-search-select2-dropdown--activity';
			const dropdownClass = 'ttbm-top-search-select2-dropdown' + dropdownModifier;

			$select.addClass('ttbm-top-search-select');
			ttbmCacheTopSearchOptionSubtitles($select);

			$select.select2({
				minimumResultsForSearch: 8,
				width: '100%',
				dropdownParent: $('body'),
				templateResult: function (option) {
					return $(ttbmBuildTopSearchOptionMarkup(option, false));
				},
				templateSelection: function (option) {
					return $(ttbmBuildTopSearchOptionMarkup(option, true));
				},
				escapeMarkup: function (markup) {
					return markup;
				}
			});

			$select.next('.select2-container').addClass('ttbm-top-search-select2');

			$select
				.on('select2:open', function () {
					const $field = $(this).closest('.ttbm-top-search-field');
					$field.addClass('is-open');
					window.requestAnimationFrame(function () {
						$('.select2-container--open .select2-dropdown')
							.removeClass('ttbm-top-search-select2-dropdown ttbm-top-search-select2-dropdown--location ttbm-top-search-select2-dropdown--activity')
							.addClass(dropdownClass)
							.css({ width: '', minWidth: '', maxWidth: '' });
					});
				})
				.on('select2:close', function () {
					$(this).closest('.ttbm-top-search-field').removeClass('is-open');
				});
		});
	}

	window.ttbmInitTopSearchSelects = ttbmInitTopSearchSelects;

	// ── Top search bar ([ttbm-top-search]) — AJAX submit, same page ──
	// The form (ttbm_top_filter_static() in TTBM_Filter_Pagination.php) still
	// plain-GETs to /find/ ([ttbm-search-result]) by default — that stays as
	// the fallback for no-JS visitors and for any page (e.g. the homepage
	// hero search widget) that has no results grid of its own to swap. Only
	// when #ttbm-archive-results is actually present on the current page
	// (the tour archive — see archive-tour.php) does this intercept the
	// submit and swap that container's contents in place instead, via the
	// exact same search_result()/list_with_left_filter_for_search() render
	// path /find/ itself already uses (TTBM_Shortcode::ajax_top_search()),
	// just returned over admin-ajax instead of a full page load.
	$(document).on('submit', '.ttbm-top-search-form', function (e) {
		var $form = $(this);
		var $results = $('#ttbm-archive-results');

		if (!$results.length || typeof ttbm_ajax_url === 'undefined') {
			return; // no same-page grid to swap — let the normal GET submit happen
		}

		e.preventDefault();

		var $submitBtn = $form.find('.ttbm-top-search-submit').prop('disabled', true);
		placeholderLoader($results);
		pageScrollTo($results);

		$.ajax({
			url: ttbm_ajax_url,
			type: 'GET',
			dataType: 'html',
			data: $form.serialize() + '&action=ttbm_top_search_ajax'
		}).done(function (html) {
			$results.html(html);
			ttbm_loadBgImage();
			load_pagination_initial_item();
			if (window.history && window.history.pushState) {
				window.history.pushState({}, '', window.location.pathname + '?' + $form.serialize());
			}
		}).fail(function () {
			// Same safety net as a real network/nonce failure elsewhere in this
			// file — fall back to the form's original full-page behavior rather
			// than leaving the click looking like it did nothing.
			$form.off('submit').trigger('submit');
		}).always(function () {
			$submitBtn.prop('disabled', false);
			placeholderLoaderRemove($results);
		});
	});

	$(document).ready(function () {
		load_pagination_initial_item();

		ttbmInitTopSearchSelects();
		$(window).on('load', ttbmInitTopSearchSelects);
		setTimeout(ttbmInitTopSearchSelects, 250);
		setTimeout(ttbmInitTopSearchSelects, 1000);

		/*$("#ttbm_date-input_from").datepicker({
			dateFormat: "MM d, yy", // Custom date format: March 20, 2024
			minDate: 0, // Disable past dates
			showAnim: "fadeIn"
		});

		// Open the datepicker when clicking the icon
		$("#ttbm_calendar-icon").on("click", function () {
			$("#ttbm_date-input_from").datepicker("show");
		});
		$("#ttbm_date-input_to").datepicker({
			dateFormat: "MM d, yy", // Custom date format: March 20, 2024
			minDate: 0, // Disable past dates
			showAnim: "fadeIn"
		});

		// Open the datepicker when clicking the icon
		$("#ttbm_calendar-icon").on("click", function () {
			$("#ttbm_date-input_to").datepicker("show");
		});*/




		/*let rangePicker = $("#ttbm_date-input_from").flatpickr({
			mode: "range",
			dateFormat: "F j, Y",
			minDate: "today",
			onChange: function(selectedDates, dateStr, instance) {
				if (selectedDates.length === 2) {
					$("#ttbm_date-input_from").val(
						instance.formatDate(selectedDates[0], "F j, Y")
					);
					$("#ttbm_date-input_to").val(
						instance.formatDate(selectedDates[1], "F j, Y")
					);
				}
			}
		});
		$("#ttbm_date-input_from").on("focus click", function () {
			rangePicker.open();
		});
		$("#ttbm_date-input_to").on("focus click", function () {
			rangePicker.open();
		});
		$("#ttbm_calendar-icon").on("click", function () {
			rangePicker.open();
		});*/


		let ttbmInitTourSearchDateRangePicker = function () {
			const $input = $('#ttbm_date_start_end_input');
			if (!$input.length || $input.data('ttbm-tour-drp-init') || typeof $.fn.daterangepicker !== 'function' || typeof moment !== 'function') {
				return;
			}

			const separator = ' \u2013 ';
			const pickerOptions = {
				autoApply: true,
				autoUpdateInput: false,
				minDate: moment().startOf('day'),
				opens: 'left',
				drops: 'down',
				parentEl: 'body',
				locale: {
					format: 'MMM D, YYYY',
					separator: separator
				}
			};

			const existingValue = ($input.val() || '').trim();
			if (existingValue) {
				const parts = existingValue.split(/\s+[-\u2013\u2014]\s+/);
				if (parts.length === 2) {
					const start = moment(parts[0].trim(), ['MMM D, YYYY', 'MMMM D, YYYY', 'M/D/YYYY', 'YYYY-MM-DD'], true);
					const end = moment(parts[1].trim(), ['MMM D, YYYY', 'MMMM D, YYYY', 'M/D/YYYY', 'YYYY-MM-DD'], true);
					if (start.isValid()) {
						pickerOptions.startDate = start;
					}
					if (end.isValid()) {
						pickerOptions.endDate = end;
					}
				}
			}

			$input.daterangepicker(pickerOptions);
			const drpInstance = $input.data('daterangepicker');
			if (drpInstance && drpInstance.container) {
				drpInstance.container.addClass('ttbm-tour-daterange');
			}

			$input
				.on('show.daterangepicker', function (ev, picker) {
					picker.container.addClass('ttbm-tour-daterange');
				})
				.on('apply.daterangepicker', function (ev, picker) {
					const formatted = picker.startDate.format('MMM D, YYYY') + separator + picker.endDate.format('MMM D, YYYY');
					$(this).val(formatted);
				});

			$input.data('ttbm-tour-drp-init', true);
		};

		ttbmInitTourSearchDateRangePicker();

		$("#ttbm_date_start_end_input").on("focus click", function () {
			const drp = $(this).data('daterangepicker');
			if (drp) {
				drp.show();
			}
		});
		$("#ttbm_start_end_calendar_icon").on("click", function () {
			$("#ttbm_date_start_end_input").trigger('click');
		});


		$(document).on('click', '.ttbm_item_filter_by_activity', function () {
			let $clicked = $(this);
			let clickedId = $clicked.attr('id');

			if (clickedId === 'all') {
				$('.ttbm_item_filter_by_activity').removeClass('ttbm_item_activity_active');
				$clicked.addClass('ttbm_item_activity_active');
				$('.filter_item').each(function () {
					$(this).fadeIn('fast');
					$(this).removeClass('search_off').addClass('search_on');
				});
			} else {
				$('.ttbm_item_filter_by_activity#all').removeClass('ttbm_item_activity_active');
				$clicked.toggleClass('ttbm_item_activity_active');
				let activeIds = [];
				$('.ttbm_item_activity_active').each(function () {
					let id = $(this).attr('id');
					if (id && id !== 'all') {
						activeIds.push(id);
					}
				});
				if (activeIds.length === 0) {
					$('.ttbm_item_filter_by_activity#all').addClass('ttbm_item_activity_active');
					$('.filter_item').each(function () {
						$(this).fadeIn('fast');
						$(this).removeClass('search_off').addClass('search_on');
					});
				} else {
					$('.filter_item').each(function () {
						let activities = $(this).find('input[name="ttbm_item_activities"]').val();
						if (activities) {
							let activityArray = activities.split(',');
							if (activeIds.some(id => activityArray.includes(id))) {
								$(this).fadeIn('fast');
								$(this).removeClass('search_off').addClass('search_on');
							} else {
								$(this).fadeOut('fast');
								$(this).removeClass('search_on').addClass('search_off');
							}
						} else {
							$(this).fadeOut('fast');
							$(this).removeClass('search_on').addClass('search_off');
						}
					});
				}
			}

			function filter_qty_palace() {
				let countSearchOn = $('.search_on').length;
				$('.filter_short_result .qty_count').text(countSearchOn);
				$('.filter_short_result .total_filter_qty').text(countSearchOn);
			}
			filter_qty_palace();
		});

		const holder = $('.ttbm_all_item_activities_holder');
		const parent = holder.parent();
		const scrollLeftBtn = $('<button class="scroll-left">&lt;</button>').appendTo(parent);
		const scrollRightBtn = $('<button class="scroll-right">&gt;</button>').appendTo(parent);
		const scrollAmount = 150;
		function updateArrows() {
			const totalItemsWidth = holder.find('.ttbm_item_activity').toArray().reduce((total, item) => {
				return total + $(item).outerWidth(true); // Include margins
			}, 0);
			const holderWidth = parent.width(); // Parent width
			const maxScroll = totalItemsWidth - holderWidth;
			const currentScroll = holder.scrollLeft();
			scrollLeftBtn.toggle(currentScroll > 0); // Show left arrow if not at the start
			scrollRightBtn.toggle(totalItemsWidth > holderWidth && currentScroll < maxScroll); // Show right arrow if overflow exists and not at the end
		}
		scrollLeftBtn.on('click', function () {
			holder.scrollLeft(holder.scrollLeft() - scrollAmount);
			setTimeout(updateArrows, 50); // Delay to allow scrolling to complete
		});
		scrollRightBtn.on('click', function () {
			holder.scrollLeft(holder.scrollLeft() + scrollAmount);
			setTimeout(updateArrows, 50); // Delay to allow scrolling to complete
		});
		holder.on('scroll', updateArrows);
		updateArrows();
		$(window).on('resize', updateArrows);

	});

	$(document).on('click', '.ttbm_filter_area .ttbm_grid_view', function () {
		let parent = $(this).closest('.ttbm_filter_area');
		let all_item = parent.find('.all_filter_item');
		placeholderLoader(all_item);
		$(this).attr('disabled', '');
		all_item.find('.modern').toggleClass('grid modern').promise().done(function () {
			parent.find('.ttbm_list_view').removeAttr('disabled');
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
		category_filter_multiple: 'data-category',
		organizer_filter: 'data-organizer',
		organizer_filter_multiple: 'data-organizer',
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
		/* Only used by filter_value_exit()'s "is any filter active" scan below —
		   filter_price_range()/filter_rating_min() read their own hidden inputs
		   directly rather than going through this name->attribute lookup, but
		   without an entry here filter_value_exit() never notices either one is
		   set, so list_filter() takes its "show everything" shortcut and
		   get_item_result() (where the actual price/rating comparison happens)
		   never runs at all. */
		price_filter_range: 'data-price',
		rating_filter_threshold: 'data-rating',
	};
	//************Filter*************//
	$(document).on('change', '.ttbm_filter .formControl:not([type="checkbox"]):not([type="radio"]), .ttbm_filter input[type="hidden"]', function (e) {
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
		active = active > 0 ? Math.min(active, filter_multi_in_multi(parent, item, 'category_filter_multiple', active)) : active;
		active = active > 0 ? Math.min(active, filter_single_in_multi(parent, item, 'organizer_filter', active)) : active;
		active = active > 0 ? Math.min(active, filter_multi_in_multi(parent, item, 'organizer_filter_multiple', active)) : active;
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
		active = active > 0 ? Math.min(active, filter_price_range(parent, item, active)) : active;
		active = active > 0 ? Math.min(active, filter_rating_min(parent, item, active)) : active;
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
	/* "Price per person" sidebar filter — hidden input holds "min,max", set by the
	   drag handler below; compared against the data-price every card already
	   carries. Same passthrough-when-empty / 2-or-0 contract as the filter_*
	   helpers above, so it composes into get_item_result()'s Math.min() chain
	   exactly like every other dimension. */
	function filter_price_range(parent, item, active) {
		let raw = parent.find('input[name="price_filter_range"]').val();
		if (raw) {
			let parts = raw.split(",");
			let min = parseFloat(parts[0]);
			let max = parseFloat(parts[1]);
			let price = parseFloat(item.attr('data-price'));
			if (!isNaN(min) && !isNaN(max) && !isNaN(price)) {
				active = (price >= min && price <= max) ? 2 : 0;
			}
		}
		return active;
	}
	/* "Minimum rating" sidebar filter — hidden input holds a single numeric
	   threshold (5/4/3), set by the tier-click handler below. A tour with no
	   data-rating (never reviewed) correctly never matches any threshold. */
	function filter_rating_min(parent, item, active) {
		let threshold = parent.find('input[name="rating_filter_threshold"]').val();
		if (threshold) {
			let rating = parseFloat(item.attr('data-rating'));
			active = (!isNaN(rating) && rating >= parseFloat(threshold)) ? 2 : 0;
		}
		return active;
	}
	//************Price range slider (drag)*************//
	$(document).on('input', '.ttbm-price-thumb-input', function () {
		let $this = $(this);
		let wrap = $this.closest('.ttbm-price-range');
		let minInput = wrap.find('.ttbm-price-thumb-min');
		let maxInput = wrap.find('.ttbm-price-thumb-max');
		let min = parseFloat(minInput.attr('min'));
		let max = parseFloat(maxInput.attr('max'));
		let minVal = parseFloat(minInput.val());
		let maxVal = parseFloat(maxInput.val());
		/* Keep the two thumbs from crossing — whichever one is being dragged wins. */
		if (minVal > maxVal) {
			if ($this.hasClass('ttbm-price-thumb-min')) {
				minVal = maxVal;
				minInput.val(minVal);
			} else {
				maxVal = minVal;
				maxInput.val(maxVal);
			}
		}
		let span = (max - min) || 1;
		let leftPct = ((minVal - min) / span) * 100;
		let rightPct = ((maxVal - min) / span) * 100;
		wrap.find('.ttbm-price-fill').css({ left: leftPct + '%', right: (100 - rightPct) + '%' });
		let currency = wrap.attr('data-currency') || '$';
		wrap.find('.ttbm-price-label-min').text(currency + Math.round(minVal));
		wrap.find('.ttbm-price-label-max').text(currency + Math.round(maxVal));
		wrap.find('input[name="price_filter_range"]').val(minVal + ',' + maxVal).trigger('change');
	});
	//************Rating tier (single-select)*************//
	$(document).on('click', '.ttbm-rating-option', function () {
		let $this = $(this);
		let group = $this.closest('.ttbm-rating-filter-group');
		let hidden = group.find('input[name="rating_filter_threshold"]');
		if ($this.hasClass('active')) {
			/* Clicking the already-active tier clears the filter back to "any". */
			group.find('.ttbm-rating-option').removeClass('active');
			hidden.val('').trigger('change');
		} else {
			group.find('.ttbm-rating-option').removeClass('active');
			$this.addClass('active');
			hidden.val($this.attr('data-rating-tier')).trigger('change');
		}
	});
	//************Pagination*************//
	$(document).on('click', '.ttbm_filter_area .pagination_area [data-pagination]', function (e) {
		e.preventDefault();
		let pagination_page = $(this).data('pagination');
		let parent = $(this).closest('.ttbm_filter_area');
		parent.find('[data-pagination]').removeClass('active_pagination');
		$(this).addClass('active_pagination').promise().done(function () {
			load_pagination(parent, pagination_page);
		}).promise().done(function () {
			ttbm_loadBgImage();
		});
	});
	$(document).on('click', '.ttbm_filter_area .pagination_area .pagination_load_more', function () {
		let pagination_page = parseInt($(this).attr('data-load-more'));
		let parent = $(this).closest('.ttbm_filter_area');
		let item_class = get_item_class(parent);
		if (parent.find(item_class + ':hidden').length > 0) {
			pagination_page = pagination_page + 1;
		} else {
			pagination_page = 0;
		}
		$(this).attr('data-load-more', pagination_page).promise().done(function () {
			load_pagination(parent, pagination_page);
		}).promise().done(function () {
			lode_more_init(parent);
		}).promise().done(function () {
			ttbm_loadBgImage();
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
			all_item.css({ "height": all_item_height, "overflow": "hidden" });
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
			all_item.css({ "height": "auto", "overflow": "inherit" }).promise().done(function () {
				ttbm_loadBgImage();
				filter_qty_palace(parent, items_class);
				pagination_management(parent, pagination_page);
				placeholderLoaderRemove(all_item);
				parent.find('.filter_item.ttbm-tab-hidden').stop(true, true).hide();
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
		if (parent.find('.filter_item.ttbm-tab-hidden').length > 0) {
			items = '.filter_item:not(.ttbm-tab-hidden)';
			parent.find('.filter_item.ttbm-tab-hidden').hide();
		} else if (parent.find('.filter_item.search_on').length > 0 || parent.find('.filter_item.search_of').length > 0) {
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