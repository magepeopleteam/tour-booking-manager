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
	$("#ttbm_tour_guide .owl-carousel").owlCarousel({
		loop: true,
		margin: 10,
		nav: true,
		responsive: {
			0: {
				items: 1
			}
		}
	});
	$("#ttbm_tour_guide .next").click(function () {
		$('#ttbm_tour_guide .owl-next').trigger('click');
	});
	$("#ttbm_tour_guide .prev").click(function () {
		$('#ttbm_tour_guide .owl-prev').trigger('click');
	});
	
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

	//========= google map load=========
	if(ttbm_map.api_key){
        initGMap();
    }else{
        initOSMMap();
    }
	function initOSMMap() {
		var map_canvas = document.getElementById("osmap_canvas");
		
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