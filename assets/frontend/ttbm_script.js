//*******owlCarousel***********//
(function ($) {
	"use strict";
	let target_related_tour = $("#ttbm_related_tour .owl-carousel");
	let trt_num = target_related_tour.data('show');
	let trt_num_600 = Math.min(trt_num - 2, 2)
	trt_num_600 = Math.max(trt_num_600, 1)
	let trt_num_800 = Math.min(trt_num - 1, 3)
	trt_num_800 = Math.max(trt_num_800, 1)
	target_related_tour.owlCarousel({
		loop: true,
		margin: 10,
		nav: true,
		rtl: false,
		responsive: {
			0: {
				items: 1
			},
			600: {
				items: trt_num_600
			},
			800: {
				items: trt_num_800
			},
			1000: {
				items: trt_num
			}
		}
	});
	$("#ttbm_related_tour .next").click(function () {
		$('#ttbm_related_tour .owl-next').trigger('click');
	});
	$("#ttbm_related_tour .prev").click(function () {
		$('#ttbm_related_tour .owl-prev').trigger('click');
	});
	$("#place_you_see .owl-carousel").owlCarousel({
		loop: true,
		margin: 20,
		nav: true,
		rtl: true,
		responsive: {
			0: {
				items: 1
			},
			600: {
				items: 2
			},
			1000: {
				items: 3
			}
		}
	});
	$("#place_you_see .next").click(function () {
		$('#place_you_see .owl-next').trigger('click');
	});
	$("#place_you_see .prev").click(function () {
		$('#place_you_see .owl-prev').trigger('click');
	});
	// ==========Tour Guide Carousel============
	$("#ttbm-tour-guide").owlCarousel({
		loop: true,
		center: true,
		margin: 5,
		nav: true,
		startPosition:0,
		responsive: {
			0: {
				items:1
			}
		}
	});
	$(".ttbm-tour-guide .next").click(function () {
		$('#ttbm-tour-guide .owl-next').trigger('click');
	});
	$(".ttbm-tour-guide .prev").click(function () {
		$('#ttbm-tour-guide .owl-prev').trigger('click');
	});
	// ==========Tour Guide Carousel End============
	$(document).on('click', '[data-target-popup="get-enquiry-popup"]', function () {
		const form = $('#ttbm-enquiry-form');
		form[0].reset();
		form.find('#ttbm_website').val('');
		form.find('#ttbm-enquiry-time').val(Math.floor(Date.now() / 1000));
		$('.ajax-response').html('');
	});

	// ==========get enquiry form submit============
	$(document).on('click', '#ttbm-enquiry-form-submit', function(e){	
		e.preventDefault();
		let form = $(this).closest('form');
		let data = form.serialize();
		jQuery.ajax({
			type: 'POST',
			url: ttbm_ajax.ajax_url,
			data:{
				action: 'ttbm_enquiry_form_submit',
				nonce: ttbm_ajax.nonce,
				data: data
			},
			success: function(response){
				if (response.success) {
					$('.ajax-response').html(response.data.message).css('color', 'green');
					form[0].reset();
				} else {
					$('.ajax-response').html(response.data.message).css('color', 'red');
				}
			}
		});
	});

	function ttbm_smart_theme_mobile_booking_position() {
		const isMobile = window.matchMedia('(max-width: 767px)').matches;
		$('.ttbm_smart_theme').each(function () {
			const theme = $(this);
			const bookingArea = theme.find('.ttbm-sidebar-booking.ttbm_registration_area').first();
			const overviewAnchor = theme.find('.ttbm-smart-overview-anchor').first();
			const bookingOrigin = theme.find('.ttbm-smart-booking-origin').first();

			if (!bookingArea.length || !overviewAnchor.length || !bookingOrigin.length) {
				return;
			}

			if (isMobile) {
				bookingArea.insertAfter(overviewAnchor);
			} else {
				bookingArea.insertAfter(bookingOrigin);
			}
		});
	}

	ttbm_smart_theme_mobile_booking_position();
	$(window).on('resize load', ttbm_smart_theme_mobile_booking_position);

	function ttbm_display_promotional_tour_load_more( load_more_btn, load_more_btn_show, tour_type ) {

		const loadMoreBtn = $('#'+load_more_btn);
		const itemsPerLoad = parseInt($('#'+load_more_btn_show).val()) || 3;
		const allItems = $('.'+tour_type);
		let currentIndex = 0;

		// Hide all items initially
		// allItems.hide();

		function showNextItems() {
			const nextItems = allItems.slice(currentIndex, currentIndex + itemsPerLoad);
			nextItems.each(function () {
				const itemId = $(this).attr('id');
				// $('#' + itemId).fadeIn();
				$(this).fadeIn();
			});

			currentIndex += itemsPerLoad;

			if (currentIndex >= allItems.length) {
				loadMoreBtn.hide();
			}
		}

		// Initial load
		if ($('#'+load_more_btn_show).val() !== '') {
			showNextItems();
			loadMoreBtn.show();
		}

		// On "Load More" click
		loadMoreBtn.on('click', function () {
			showNextItems();
		});
	}

	ttbm_display_promotional_tour_load_more( 'ttbm_trending_load_more_text', 'ttbm_trending_load_more_tour_shortcode', 'ttbm_trending_shortcode_load_tour' );
	ttbm_display_promotional_tour_load_more( 'ttbm_popular_load_more_text', 'ttbm_popular_load_more_tour_shortcode', 'ttbm_popular_shortcode_load_tour' );
	ttbm_display_promotional_tour_load_more( 'ttbm_feature_load_more_text', 'ttbm_feature_load_more_tour_shortcode', 'ttbm_feature_shortcode_load_tour' );
	ttbm_display_promotional_tour_load_more( 'ttbm_deal-discount_load_more_text', 'ttbm_deal-discount_load_more_tour_shortcode', 'ttbm_deal-discount_shortcode_load_tour' );



	function ttbm_left_filter_see_more_button( checkBoxHolderId, checkBox, seeMoreButtonId ){
		const ttbm_itemsToShow = 8;
		const ttbm_activity_checkboxes = $('#'+checkBoxHolderId+' .'+checkBox+'');
		let activityVisibleCount = ttbm_itemsToShow;
		ttbm_activity_checkboxes.hide().slice(0, ttbm_itemsToShow).show();
		if (ttbm_activity_checkboxes.length <= ttbm_itemsToShow) {
			$('#'+seeMoreButtonId ).hide();
		}
		$(document).on('click', '#'+seeMoreButtonId, function () {
			activityVisibleCount += ttbm_itemsToShow;
			ttbm_activity_checkboxes.slice(0, activityVisibleCount).slideDown();
			if (activityVisibleCount >= ttbm_activity_checkboxes.length) {
				$(this).hide();
			}
		});
	}

	function ttbm_hotel_left_filter_see_more_button(checkBoxHolderId, checkBox, seeMoreButtonId){
		const ttbm_itemsToShow = 6;
		const ttbm_activity_checkboxes = $('#' + checkBoxHolderId + ' .' + checkBox);
		let activityVisibleCount = ttbm_itemsToShow;

		ttbm_activity_checkboxes.hide().slice(0, ttbm_itemsToShow).show();

		if (ttbm_activity_checkboxes.length > ttbm_itemsToShow) {
			$('#' + seeMoreButtonId).show(); // ✅ এখানে show করলাম
		} else {
			$('#' + seeMoreButtonId).hide();
		}

		$(document).on('click', '#' + seeMoreButtonId, function () {
			activityVisibleCount += ttbm_itemsToShow;
			ttbm_activity_checkboxes.slice(0, activityVisibleCount).slideDown();

			if (activityVisibleCount >= ttbm_activity_checkboxes.length) {
				$(this).hide();
			}
		});
	}


	ttbm_left_filter_see_more_button( 'ttbm_featureList', 'ttbm_feature_checkBoxLevel', 'ttbm_show_feature_seeMoreBtn' );
	ttbm_left_filter_see_more_button('ttbm_activityList', 'ttbm_activity_checkBoxLevel', 'ttbm_show_activity_seeMoreBtn' );
	ttbm_left_filter_see_more_button( 'ttbm_locationList', 'ttbm_location_checkBoxLevel', 'ttbm_show_location_seeMoreBtn' );

	ttbm_hotel_left_filter_see_more_button('ttbm_hotelActivityList', 'ttbm_activity_checkBoxLevel', 'ttbm_hotel_show_activity_seeMoreBtn');
	ttbm_hotel_left_filter_see_more_button('ttbm_hotelFeatureList', 'ttbm_feature_checkBoxLevel', 'ttbm_show_hotel_feature_seeMoreBtn');
	ttbm_hotel_left_filter_see_more_button('ttbm_hotelLocationList', 'ttbm_location_checkBoxLevel', 'ttbm_show_hotel_location_seeMoreBtn');


	// Hero stats "Load more" — bind early so a later script error cannot skip it.
	function initHeroStatsLoadMore() {
		$(document).off('click.ttbmHeroStats', '.ttbm_hero_stats_load_more').on('click.ttbmHeroStats', '.ttbm_hero_stats_load_more', function (e) {
			e.preventDefault();
			var $btn = $(this);
			var $grid = $btn.closest('.ttbm_hero_stats_grid');
			if (!$grid.length) {
				return;
			}
			var labelMore = $btn.attr('data-label-more') || 'Load more';
			var labelLess = $btn.attr('data-label-less') || 'Show less';
			var collapsed = $grid.toggleClass('ttbm_hero_stats_grid--collapsed').hasClass('ttbm_hero_stats_grid--collapsed');
			$btn.attr('aria-expanded', collapsed ? 'false' : 'true').text(collapsed ? labelMore : labelLess);
		});
	}

	$(initHeroStatsLoadMore);

	//========= google map load=========
	if (typeof ttbm_map !== 'undefined' && ttbm_map.api_key) {
        initGMap();
    }else{
        initOSMMap();
    }
	function initOSMMap() {
		var map_canvas = document.getElementById("osmap_canvas");
		
		// Check if map canvas exists before proceeding
		if (!map_canvas) {
			return;
		}
		
		// Ensure the data-lati and data-longdi attributes exist
		var lati = parseFloat(map_canvas.getAttribute("data-lati")) || 0;
		var longdi = parseFloat(map_canvas.getAttribute("data-longdi")) || 0;
		var location = map_canvas.getAttribute("data-location") || 'Tour Location';
	
		// Initialize the map with Leaflet (OpenStreetMap)
		var osmMap = L.map(map_canvas, { minZoom: 4, maxZoom: 18, scrollWheelZoom: false }).setView([lati, longdi], 12);
	
		// Add OpenStreetMap tile layer
		L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
		}).addTo(osmMap);
	
		// Optional: Add a marker at the specified location
		L.marker([lati, longdi]).addTo(osmMap).bindPopup(location);
	}
	

	function initGMap() {
		var gmap_canvas = document.getElementById("gmap_canvas");
        
		// Check if map canvas exists before proceeding
		if (!gmap_canvas) {
			return;
		}
        
		var lati = parseFloat(gmap_canvas.getAttribute("data-lati")) || 0;
		var longdi = parseFloat(gmap_canvas.getAttribute("data-longdi")) || 0;

		var location = { lat: lati, lng: longdi };

		// Create a new map instance
		var map = new google.maps.Map(gmap_canvas, {
			zoom: 12,
			center: location,
			scrollwheel: false
		});

		// Add a marker at the center
		var marker = new google.maps.Marker({
			position: location,
			map: map,
		});
    }


	// ═══ Toast Notification ════════════════════════════════════════
	function ttbmShowToast(message, type, duration, html) {
		type = type || 'info';
		duration = duration || 3500;
		var wrap = $('#ttbm-toast-wrap');
		if (!wrap.length) {
			wrap = $('<div id="ttbm-toast-wrap" class="ttbm-toast-wrap"></div>');
			$('body').append(wrap);
		}
		var item = $('<div class="ttbm-toast-item ttbm-toast-' + type + '"></div>');
		var content = $('<span class="ttbm-toast-content"></span>');
		if (html) {
			content.html(message);
		} else {
			content.text(message);
		}
		item.append(content);
		var closeBtn = $('<button type="button" class="ttbm-toast-close">&times;</button>');
		item.append(closeBtn);
		wrap.append(item);
		// Force reflow for transition
		void wrap[0].offsetWidth;
		wrap.addClass('ttbm-toast-visible');

		var timer = setTimeout(function() {
			item.fadeOut(250, function() {
				$(this).remove();
				if (!wrap.children().length) {
					wrap.removeClass('ttbm-toast-visible');
				}
			});
		}, duration);

		closeBtn.on('click', function() {
			clearTimeout(timer);
			item.fadeOut(200, function() {
				$(this).remove();
				if (!wrap.children().length) {
					wrap.removeClass('ttbm-toast-visible');
				}
			});
		});
	}

	function ttbmSetWishlistButtonState(btn, inWishlist) {
		var icon = btn.find('.mi');
		var label = inWishlist ? 'Remove from wishlist' : 'Add to wishlist';

		btn.toggleClass('active', !!inWishlist);
		btn.attr('aria-label', label);
		btn.attr('title', label);
		icon.toggleClass('mi-wishlist-heart', !!inWishlist);
		icon.toggleClass('mi-heart', !inWishlist);
	}

	function ttbmSyncWishlistButtons(tourId, inWishlist) {
		$('.ttbm-gc-wishlist[data-tour-id="' + tourId + '"]').each(function() {
			ttbmSetWishlistButtonState($(this), inWishlist);
		});
	}

	// ═══ Wishlist Toggle ═══════════════════════════════════════════
	$(document).on('click', '.ttbm-gc-wishlist', function(e) {
		e.preventDefault();
		e.stopPropagation();
		e.stopImmediatePropagation();

		var btn = $(this);
		var tourId = btn.data('tour-id');

		if (!tourId) return;

		$.ajax({
			type: 'POST',
			url: ttbm_ajax.ajax_url,
			data: {
				action: 'ttbm_wishlist_toggle',
				nonce: ttbm_ajax.nonce,
				tour_id: tourId
			},
			success: function(response) {
				if (response.success) {
					ttbmSetWishlistButtonState(btn, response.data.in_wishlist);
					ttbmSyncWishlistButtons(tourId, response.data.in_wishlist);
					if (response.data.in_wishlist) {
						var toastMsg = 'Added to wishlist.';
						if (ttbm_ajax.wishlist_url) {
							toastMsg = 'Added to wishlist. <a href="' + ttbm_ajax.wishlist_url + '">Open wishlist</a>';
						}
						ttbmShowToast(toastMsg, 'success', 4000, true);
					} else {
						ttbmShowToast('Removed from wishlist.', 'info', 3000, false);
					}
				} else if (response.data && response.data.need_login) {
					// Show login modal
					$('#ttbm-wishlist-login-modal').addClass('ttbm-modal-active');
				}
			},
			error: function() {
				// Fallback: show modal for any AJAX failure when not logged in
				if (!ttbm_ajax_is_logged_in) {
					$('#ttbm-wishlist-login-modal').addClass('ttbm-modal-active');
				}
			}
		});
	});

	// Close modal
	$(document).on('click', '.ttbm-modal-close, .ttbm-modal-overlay', function() {
		$(this).closest('.ttbm-modal-wrap').removeClass('ttbm-modal-active');
	});

	// ═══ Wishlist Remove (My Account page) ══════════════════════════
	$(document).on('click', '.ttbm-wishlist-remove', function(e) {
		e.preventDefault();
		var btn = $(this);
		var tourId = btn.data('tour-id');
		if (!tourId) return;

		$.ajax({
			type: 'POST',
			url: ttbm_ajax.ajax_url,
			data: {
				action: 'ttbm_wishlist_toggle',
				nonce: ttbm_ajax.nonce,
				tour_id: tourId
			},
			success: function(response) {
				if (response.success && !response.data.in_wishlist) {
					ttbmSyncWishlistButtons(tourId, false);
					btn.closest('.ttbm-wishlist-item').fadeOut(300, function() { $(this).remove(); });
					ttbmShowToast('Removed from wishlist.', 'info', 3000, false);
				}
			}
		});
	});

	// ═══ Wishlist View Toggle (Grid / List) ══════════════════════════
	$(document).on('click', '.ttbm-wishlist-view-btn', function(e) {
		e.preventDefault();
		var btn = $(this);
		var view = btn.data('view');
		var container = btn.closest('.ttbm-myaccount-wishlist');
		var grid = container.find('.ttbm-wishlist-grid');

		// Toggle active state on buttons
		container.find('.ttbm-wishlist-view-btn').removeClass('ttbm-wishlist-view-active').attr('aria-pressed', 'false');
		btn.addClass('ttbm-wishlist-view-active').attr('aria-pressed', 'true');

		// Switch view class on grid container
		if (view === 'list') {
			grid.removeClass('ttbm-wishlist-view-grid').addClass('ttbm-wishlist-view-list');
		} else {
			grid.removeClass('ttbm-wishlist-view-list').addClass('ttbm-wishlist-view-grid');
		}
	});

	$(document).on('click', '.ttbm-view-more-features-btn', function(e) {
		e.preventDefault();
		const list = $(this).closest('ul');
		list.find('.ttbm-feature-hidden').removeClass('ttbm-feature-hidden').prop('hidden', false).hide().slideDown();
		$(this).closest('li').remove();
	});
	$(document).on('keydown', '.ttbm-view-more-features-btn', function(e) {
		if (e.key === 'Enter' || e.key === ' ') {
			e.preventDefault();
			$(this).trigger('click');
		}
	});

	// Hero "Book Now" reveals the (hidden) booking section, scrolls to it,
	// auto-selects the next available date and opens the ticket section.
	$(document).on('click', '[data-ttbm-book-now]', function (e) {
		e.preventDefault();
		var $section = $('#ttbm_booking_section');
		if (!$section.length) { return; }
		$section.addClass('ttbm-show');
		$('html, body').animate({ scrollTop: $section.offset().top - 40 }, 450, function () {
			var $picker = $section.find('#ttbm_select_date').first();
			if (!$picker.length) { return; }
			if ($picker.val()) { return; } // date already chosen by user — do nothing
			var firstDate = $picker.data('ttbm-first-date');
			if (!firstDate) { return; }
			// Format and set the visible date input directly, bypassing datepicker('setDate')
			// which can fire onSelect internally and cause a duplicate AJAX call.
			var formattedDate = $.datepicker.formatDate(ttbm_date_format, new Date(firstDate + 'T00:00:00'));
			$picker.val(formattedDate);
			var $regArea   = $section.find('.ttbm_registration_area').first();
			var $hiddenDate = $picker.closest('label').find('input[name="ttbm_date"]');
			// For regular_ticket the PHP has already pre-rendered the ticket form for
			// the first available date. Clearing + reloading the exact same content
			// causes a visible double-load. If the form is already there for this date,
			// just sync the hidden input and enable the Book Now button — no AJAX needed.
			var loadedDate = $regArea.find('.ttbm_last_updated').data('tour-date');
			if ($regArea.find('.mp_tour_ticket_form').length > 0
					&& (!loadedDate || String(loadedDate) === String(firstDate))) {
				$hiddenDate.val(firstDate);
				ttbm_toggle_book_now_by_date($regArea);
				return;
			}
			// Form not yet loaded (e.g. availability_section) — trigger AJAX load.
			$hiddenDate.val(firstDate).trigger('change');
		});
	});

	// Daily Schedule timeline — sync active day marker with accordion state.
	$(document).on('click', '.ttbm_day_wise_timeline .day_wise_details_item_title[data-collapse-target]', function () {
		var $title = $(this);
		setTimeout(function () {
			var $item = $title.closest('.day_wise_details_item');
			var isOpen = $title.hasClass('mActive');
			$item.toggleClass('is-active', isOpen);
		}, 260);
	});

}(jQuery));
