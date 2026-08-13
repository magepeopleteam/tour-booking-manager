/**
 * Active-filter chip row for [ttbm-tour-list] (the tour archive's "modern"
 * grid). Purely additive UI on top of the existing filter engine in
 * filter_pagination.js — every chip action just toggles/resets the same
 * checkboxes and hidden inputs a real click already would, then re-triggers
 * 'change' on the relevant hidden input so the existing pipeline
 * (list_filter() -> get_item_result() -> load_pagination()) does the actual
 * filtering exactly as it already does for a manual click. This file only
 * ever reads/renders that state — it never filters anything itself.
 *
 * Scoped entirely to .ttbm-tour-list-shortcode so it cannot affect hotel
 * listings or any other shortcode's sidebar-filter usage elsewhere.
 */
(function ($) {
	"use strict";

	function getCheckboxLabelText($customCheckbox) {
		return $customCheckbox.contents().filter(function () {
			return this.nodeType === 3;
		}).text().trim();
	}

	/* Mirrors the group-recompute logic in ttbm_plugin_global.js's
	   .groupCheckBox click handler, so a chip's ✕ has exactly the same effect
	   on the hidden input as unchecking the box by hand would. */
	function recomputeGroup($group) {
		let value = '';
		$group.find('input[type="checkbox"]').each(function () {
			if ($(this).is(':checked')) {
				let currentValue = $(this).attr('data-checked') || $(this).val();
				value = value + (value ? ',' : '') + currentValue;
			}
		});
		$group.find('input[type="hidden"]').val(value).trigger('change');
	}

	function resetPriceRange($wrap) {
		let $minInput = $wrap.find('.ttbm-price-thumb-min');
		let $maxInput = $wrap.find('.ttbm-price-thumb-max');
		$minInput.val($minInput.attr('min'));
		$maxInput.val($maxInput.attr('max'));
		$minInput.trigger('input');
	}

	function collectActiveFilters($filterArea) {
		let chips = [];

		$filterArea.find('.groupCheckBox').each(function () {
			let $group = $(this);
			$group.find('input[type="checkbox"]:checked').each(function () {
				let $checkbox = $(this);
				let text = getCheckboxLabelText($checkbox.closest('.customCheckboxLabel').find('.customCheckbox'));
				if (text) {
					chips.push({
						label: text,
						remove: function () {
							$checkbox.prop('checked', false);
							recomputeGroup($group);
						}
					});
				}
			});
		});

		$filterArea.find('.ttbm-price-range').each(function () {
			let $wrap = $(this);
			let $minInput = $wrap.find('.ttbm-price-thumb-min');
			let $maxInput = $wrap.find('.ttbm-price-thumb-max');
			let boundMin = parseFloat($minInput.attr('min'));
			let boundMax = parseFloat($maxInput.attr('max'));
			let curMin = parseFloat($minInput.val());
			let curMax = parseFloat($maxInput.val());
			if (curMin > boundMin || curMax < boundMax) {
				let currency = $wrap.attr('data-currency') || '$';
				let label;
				if (curMin > boundMin && curMax < boundMax) {
					label = currency + Math.round(curMin) + ' – ' + currency + Math.round(curMax);
				} else if (curMax < boundMax) {
					label = 'Under ' + currency + Math.round(curMax);
				} else {
					label = currency + Math.round(curMin) + ' & up';
				}
				chips.push({
					label: label,
					remove: function () {
						resetPriceRange($wrap);
					}
				});
			}
		});

		$filterArea.find('.ttbm-rating-option.active').each(function () {
			let $option = $(this);
			let label = $option.find('.ttbm-rating-option-label').text().trim();
			if (label) {
				chips.push({
					label: label,
					remove: function () {
						$option.removeClass('active');
						$option.closest('.ttbm-rating-filter-group').find('input[name="rating_filter_threshold"]').val('').trigger('change');
					}
				});
			}
		});

		return chips;
	}

	function resetAll($filterArea) {
		$filterArea.find('.groupCheckBox').each(function () {
			let $group = $(this);
			$group.find('input[type="checkbox"]:checked').prop('checked', false);
			recomputeGroup($group);
		});
		$filterArea.find('.ttbm-price-range').each(function () {
			resetPriceRange($(this));
		});
		$filterArea.find('.ttbm-rating-option').removeClass('active');
		$filterArea.find('input[name="rating_filter_threshold"]').val('').trigger('change');
	}

	function renderChips($filterArea) {
		let $shortcodeRoot = $filterArea.closest('.ttbm-tour-list-shortcode');
		let $row = $shortcodeRoot.find('.ttbm-active-filter-chips');
		if (!$row.length) {
			return;
		}
		let chips = collectActiveFilters($filterArea);
		$row.empty();
		if (!chips.length) {
			$row.removeClass('has-chips');
			return;
		}
		$row.addClass('has-chips');
		chips.forEach(function (chip) {
			let $pill = $('<span class="ttbm-chip"></span>').text(chip.label);
			let $remove = $('<button type="button" class="ttbm-chip-remove" aria-label="Remove filter">✕</button>');
			$remove.on('click', function (e) {
				e.preventDefault();
				chip.remove();
			});
			$pill.append($remove);
			$row.append($pill);
		});
		let $clearAll = $('<button type="button" class="ttbm-chip-clear-all"></button>').text('Clear all');
		$clearAll.on('click', function (e) {
			e.preventDefault();
			resetAll($filterArea);
		});
		$row.append($clearAll);
	}

	/* Every checkbox group and the new price/rating controls all funnel into a
	   change event on a hidden input (see ttbm_plugin_global.js's groupCheckBox
	   handler and this plugin's own price/rating handlers in
	   filter_pagination.js) — one listener here covers every filter dimension. */
	$(document).on('change', '.ttbm-tour-list-shortcode .ttbm_filter input[type="hidden"]', function () {
		renderChips($(this).closest('.ttbm_filter'));
	});

	$(document).on('click', '.ttbm-tour-list-shortcode .ttbm-filter-reset-all', function (e) {
		e.preventDefault();
		resetAll($(this).closest('.ttbm_filter_area').find('.ttbm_filter'));
	});

	$(function () {
		$('.ttbm-tour-list-shortcode .ttbm_filter').each(function () {
			renderChips($(this));
		});
	});

}(jQuery));
