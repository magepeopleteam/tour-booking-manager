jQuery(function ($) {
	'use strict';

	// Real "add to cart" submission (the visible Book Now / Book This Trip
	// button just forwards a click to this hidden button — see
	// ttbm_price_calculation.js) is a genuine form submit/page navigation, so
	// the loading state only needs to last until the browser unloads the
	// page; it also guards against a double-submit if that takes a moment.
	$(document).on('submit', '.mp_tour_ticket_form', function () {
		$(this).find('.ttbm-confirm-btn:visible').addClass('is-loading').prop('disabled', true);
	});
});
