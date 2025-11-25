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
		margin: 10,
		nav: true,
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
		$('#ttbm-enquiry-form')[0].reset();
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


	//========= google map load=========
	if(ttbm_map.api_key){
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
		var osmMap = L.map(map_canvas, { minZoom: 4, maxZoom: 18 }).setView([lati, longdi], 12);
	
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
			zoom: 12, // Zoom level
			center: location // Set center of the map
		});

		// Add a marker at the center
		var marker = new google.maps.Marker({
			position: location,
			map: map,
		});
    }


}(jQuery));