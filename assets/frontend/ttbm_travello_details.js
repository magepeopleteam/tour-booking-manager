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

	// ── Gallery "more photos" badge: fix its wording + count ─────────────
	// TTBM_Custom_Slider::slider_showcase_style_1() (shared by every theme,
	// not something this template can safely rewrite) always renders the
	// real showcase as 4 cells and stamps the 4th one's badge with a bare
	// number — "how many photos beyond these 4 thumbnails exist". CSS
	// (ttbm_travello_details.css) hides 2 of those 4 cells to match the
	// reference design's exactly-2-photo showcase, which makes that baked-in
	// number wrong (it'd still say "+1" while 3 more are actually hidden).
	// Recompute it here from the real slide count already in the DOM and
	// swap in the reference's own wording ("View all N photos") — N is the
	// *total* photo count (what clicking this actually opens to), not a
	// remaining-count, matching what "View all 18 photos" means in the
	// reference itself.
	$('#ttbm_travello_gallery .sliderMoreItem').each(function () {
		var $badge = $(this);
		// The inline `.superSlider` this badge lives in also CONTAINS the
		// full-screen lightbox popup as a descendant (`.sliderPopup
		// .superSlider .sliderAllItem`), which duplicates every real slide
		// again for its own carousel. `.closest('.superSlider').find(...)`
		// would walk into that nested copy too and double the count (10
		// instead of 5 on a tour with 5 real photos) — going through the
		// inline hero's own `.ttbm_slider-wrapper` child first keeps this
		// scoped to just its real slides, since the popup is a *sibling* of
		// that wrapper, not a descendant of it.
		var $gallery = $badge.closest('.superSlider');
		var totalPhotos = $gallery.children('.ttbm_slider-wrapper').find('.sliderAllItem .sliderItem').length;

		if (!totalPhotos) {
			return;
		}

		$badge.empty()
			.append($('<span class="mi mi-gallery" aria-hidden="true"></span>'))
			.append(document.createTextNode(' View all ' + totalPhotos + ' photos'));
	});

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
