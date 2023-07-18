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
}(jQuery));