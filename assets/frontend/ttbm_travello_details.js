/**
 * "Travello" single-tour template — tab-bar scroll-nav + share button.
 *
 * Everything else on this template that looks interactive is handled by
 * code that already exists elsewhere in the plugin and needed nothing new:
 *   - FAQ accordion: the generic `[data-collapse-target]` handler in
 *     assets/mp_style/ttbm_plugin_global.js already runs for any element
 *     inside `.ttbm_style` (this template's own root class) — faq.php's
 *     own markup already carries `data-collapse-target`/`data-add-class`.
 *   - Wishlist toggle: `.ttbm-gc-wishlist` click handler in ttbm_script.js.
 *   - Booking widget (dates/ticket qty/live price/Reserve→cart):
 *     ttbm_price_calculation.js, unchanged, via the reused ticket templates.
 */
(function ($) {
	"use strict";

	// ── Tab bar: scroll to section, toggle active state ──────────────────
	$(document).on('click', '#ttbm-travello-tabs .ttbm-travello-tab', function (e) {
		e.preventDefault();
		var $tab = $(this);
		var targetId = $tab.data('target');
		var $target = targetId ? $('#' + targetId) : null;

		$('#ttbm-travello-tabs .ttbm-travello-tab').removeClass('is-active');
		$tab.addClass('is-active');

		if ($target && $target.length) {
			$('html, body').animate({ scrollTop: $target.offset().top - 80 }, 400);
		}
	});

	// Keep the tab bar in sync with manual scrolling.
	var $travelloSections = $('.ttbm-travello-section[id]');
	if ($travelloSections.length) {
		$(window).on('scroll', function () {
			var scrollPos = $(window).scrollTop() + 120;
			var currentId = null;
			$travelloSections.each(function () {
				if ($(this).offset().top <= scrollPos) {
					currentId = $(this).attr('id');
				}
			});
			if (currentId) {
				$('#ttbm-travello-tabs .ttbm-travello-tab').removeClass('is-active');
				$('#ttbm-travello-tabs .ttbm-travello-tab[data-target="' + currentId + '"]').addClass('is-active');
			}
		});
	}

	// ── Share button ───────────────────────────────────────────────────
	$(document).on('click', '#ttbm-travello-share-btn', function () {
		var title = $(this).data('share-title') || document.title;
		var url = window.location.href;

		if (navigator.share) {
			navigator.share({ title: title, url: url });
		} else if (navigator.clipboard) {
			navigator.clipboard.writeText(url);
			if (typeof ttbmShowToast === 'function') {
				ttbmShowToast('Link copied to clipboard.', 'info', 3000, false);
			}
		}
	});

}(jQuery));
