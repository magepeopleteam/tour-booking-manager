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

	// ── Review cards: real-name avatar + reorganized header ──────────────
	// Pro's real widget (ttbm_review_list(), tour-booking-manager-pro) has no
	// avatar/initials markup at all — the reviewer's name only ever exists
	// as plain text inside .ttbm-tour-review-info ("Emily R., <span>August
	// 2026</span>"), and the bold line up top is the review's own headline
	// (post_title), not the name. Built the avatar from that same real name
	// text (not a separate/fabricated data source) and regrouped it next to
	// the star rating, closer to the reference layout — rather than forking
	// Pro's shared method for one theme. No avatar-photo, "traveler type", or
	// review-photo fields exist in the real data at all, so none of those
	// are invented here.
	$('#ttbm-travello-reviews .ttbm-tour-review-item').each(function () {
		var $item = $(this);
		var $info = $item.find('> .ttbm-tour-review-info');
		var $title = $item.find('> .ttbm-tour-review-rating-with-title');
		if (!$info.length || !$title.length) {
			return;
		}

		var $dateSpan = $info.find('.ttbm-tour-review-date').detach();
		var name = $.trim($info.text()).replace(/,\s*$/, '');
		var $stars = $title.find('.ttbm-tour-rating').detach();
		var headline = $.trim($title.find('.ttbm-tour-review-name').text());

		if (!name) {
			return;
		}
		var initials = $.map(name.split(/\s+/).slice(0, 2), function (word) {
			return word.charAt(0).toUpperCase();
		}).join('');

		var $head = $('<div class="ttbm-travello-review-head"></div>')
			.append($('<span class="ttbm-travello-review-avatar" aria-hidden="true"></span>').text(initials))
			.append(
				$('<div class="ttbm-travello-review-identity"></div>')
					.append($('<p class="ttbm-travello-review-name"></p>').text(name))
					.append($('<p class="ttbm-travello-review-meta"></p>').append($dateSpan))
			)
			.append($stars);

		$title.replaceWith($head);
		$info.remove();
		if (headline) {
			$head.after($('<p class="ttbm-travello-review-headline"></p>').text(headline));
		}
	});

	// ── "See all N reviews" — expand in place, no separate view to link to ──
	$(document).on('click', '.ttbm-travello-reviews-more', function () {
		$('#ttbm-travello-reviews').addClass('ttbm-travello-reviews-expanded');
		$(this).remove();
	});

	// ── Ticket picker: tint the row while qty > 0 ─────────────────────
	// Qty is a text input whose value attribute stays "0" after JS
	// increments it, so :has([value="0"]) can't drive this from CSS.
	function ttbmTravelloSyncTicketSelection() {
		$('#ttbm_booking_section .ttbm_ticket_row').each(function () {
			var qty = parseInt($(this).find('.inputIncDec').val(), 10) || 0;
			$(this).toggleClass('is-selected', qty > 0);
		});
	}
	ttbmTravelloSyncTicketSelection();
	$(document).on('change input', '#ttbm_booking_section .inputIncDec', ttbmTravelloSyncTicketSelection);

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

	// ── Image placeholders: gallery + similar-tour thumbs ─────────────
	// Boxes already have a CSS shimmer (`:not(.ttbm-img-loaded)::after`)
	// filling the same size/position as the real photo. Watch the actual
	// image URL and drop that overlay once it has decoded. Related-tour
	// thumbs sit below the fold, so IntersectionObserver starts the
	// decode just before they enter view.
	function ttbmTravelloMarkImageLoaded($el) {
		$el.addClass('ttbm-img-loaded');
	}

	function ttbmTravelloWatchBgImage(el) {
		var $el = $(el);
		if ($el.data('ttbmImgWatching') || $el.hasClass('ttbm-img-loaded')) {
			return;
		}
		$el.data('ttbmImgWatching', true);

		var url = $el.attr('data-bg-image') || '';
		if (!url) {
			var bg = $el.css('background-image') || '';
			var match = bg.match(/url\(["']?(.+?)["']?\)/);
			url = match ? match[1] : '';
		}
		if (!url || url === 'none') {
			ttbmTravelloMarkImageLoaded($el);
			return;
		}

		var img = new Image();
		var done = function () {
			ttbmTravelloMarkImageLoaded($el);
		};
		img.onload = function () {
			if (typeof img.decode === 'function') {
				img.decode().then(done).catch(done);
			} else {
				done();
			}
		};
		img.onerror = done;
		img.src = url;
		if (img.complete && img.naturalWidth > 0) {
			done();
		}
		setTimeout(done, 8000);
	}

	$('#ttbm_travello_gallery [data-bg-image]').each(function () {
		ttbmTravelloWatchBgImage(this);
	});

	var $relatedThumbs = $('.ttbm-travello-similar .ttbm-gc-thumb[data-bg-image], .ttbm-travello-similar .ttbm-gc-thumb[style*="background-image"]');
	if ($relatedThumbs.length && 'IntersectionObserver' in window) {
		var relatedIo = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (!entry.isIntersecting) {
					return;
				}
				ttbmTravelloWatchBgImage(entry.target);
				relatedIo.unobserve(entry.target);
			});
		}, { rootMargin: '220px 0px' });
		$relatedThumbs.each(function () {
			relatedIo.observe(this);
		});
	} else {
		$relatedThumbs.each(function () {
			ttbmTravelloWatchBgImage(this);
		});
	}

}(jQuery));
