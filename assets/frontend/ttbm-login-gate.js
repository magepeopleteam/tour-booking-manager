jQuery(function ($) {
	'use strict';

	if (typeof ttbmLoginGate === 'undefined') {
		return;
	}

	// --- Click-time login gate ------------------------------------------------
	// The booking form (tickets, dates, extras) is always visible — browsing
	// and selecting never requires an account. The real submit is the hidden
	// .ttbm_add_to_cart button that ttbm_price_calculation.js's .ttbm_book_now
	// handler triggers once its own validation passes. Intercepting the click
	// here — rather than editing that (large, unrelated) validation file —
	// only needs .preventDefault() to stop the native form submission; no
	// event-binding-order dependency on the other script.
	$(document).on('click', '.ttbm_add_to_cart', function (e) {
		var $btn = $(this);
		var $area = $btn.closest('.ttbm_book_now_area');
		if (!$area.length || !$area.data('ttbm-require-login') || $area.data('ttbm-login-verified')) {
			return; // not gated, or already logged in this page view — submit normally.
		}
		var $modal = $area.find('.ttbm-login-required-modal');
		if (!$modal.length) {
			return; // Nothing to show — fail open rather than silently blocking booking.
		}
		e.preventDefault();
		e.stopImmediatePropagation();
		$modal.data('resume-btn', $btn);
		$modal.css('display', 'flex');
		return false;
	});

	$(document).on('click', '[data-ttbm-login-modal-close]', function (e) {
		e.preventDefault();
		$(this).closest('.ttbm-login-required-modal').hide();
	});
	$(document).on('keydown', function (e) {
		if (e.key === 'Escape' || e.keyCode === 27) {
			$('.ttbm-login-required-modal:visible').hide();
		}
	});

	// Toggle between the login and register field sets within one panel.
	$(document).on('click', '.ttbm-login-gate-toggle', function (e) {
		e.preventDefault();
		var $gate = $(this).closest('.ttbm-login-gate');
		var to = $(this).data('to');
		$gate.find('.ttbm-login-gate-panel').attr('data-mode', to);
		$gate.find('.ttbm-login-gate-toggle').show();
		$gate.find('.ttbm-login-gate-toggle[data-to="' + to + '"]').hide();
		$gate.find('.ttbm-login-gate-fields-login').toggle(to === 'login');
		$gate.find('.ttbm-login-gate-fields-register').toggle(to === 'register');
		$gate.find('.ttbm-login-gate-submit').text(
			to === 'register' ? ttbmLoginGate.register_label : ttbmLoginGate.login_label
		);
		$gate.find('.ttbm-login-gate-msg').text('');
	});

	// Submit login or register via AJAX, then either resume the booking click
	// (modal flow) or swap in the real booking button (legacy whole-panel
	// flow) — never window.location.reload(), which would wipe every ticket/
	// date/attendee field the guest already filled in.
	$(document).on('click', '.ttbm-login-gate-submit', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var $gate = $btn.closest('.ttbm-login-gate');
		var $panel = $gate.find('.ttbm-login-gate-panel');
		var $msg = $gate.find('.ttbm-login-gate-msg');
		var mode = $panel.attr('data-mode') || 'login';
		var action = mode === 'register' ? 'ttbm_portal_register' : 'ttbm_portal_login';
		var data = {
			action: action,
			nonce: $gate.data('nonce')
		};
		if (mode === 'register') {
			data.user_name = $gate.find('.ttbm-login-gate-name').val();
			data.user_email = $gate.find('.ttbm-login-gate-email').val();
			data.user_phone = $gate.find('.ttbm-login-gate-phone').val();
			data.user_password = $gate.find('.ttbm-login-gate-reg-pass').val();
		} else {
			data.user_login = $gate.find('.ttbm-login-gate-user').val();
			data.user_password = $gate.find('.ttbm-login-gate-pass').val();
		}

		$btn.prop('disabled', true);
		$msg.removeClass('is-error').text('');

		$.post(ttbmLoginGate.ajax_url, data)
			.done(function (res) {
				if (!res || !res.success) {
					$msg.addClass('is-error').text((res && res.data) || ttbmLoginGate.error_label);
					$btn.prop('disabled', false);
					return;
				}
				if ($gate.hasClass('ttbm-login-required-modal')) {
					resumeAfterLogin($gate);
				} else {
					swapInBookingArea($gate);
				}
			})
			.fail(function () {
				$msg.addClass('is-error').text(ttbmLoginGate.error_label);
				$btn.prop('disabled', false);
			});
	});

	// Modal flow: close it, mark this booking area verified so the interceptor
	// above lets the next click through, then replay the exact click the
	// visitor originally made — no second click required, no page reload.
	function resumeAfterLogin($modal) {
		var $area = $modal.closest('.ttbm_book_now_area');
		$modal.hide();
		$area.data('ttbm-login-verified', true);
		var $resumeBtn = $modal.data('resume-btn');
		if ($resumeBtn && $resumeBtn.length) {
			$resumeBtn.trigger('click');
		}
	}

	// Legacy whole-panel flow (render_login_prompt, no longer used by the
	// three book_now partials but kept for any direct caller).
	function swapInBookingArea($gate) {
		$.post(ttbmLoginGate.ajax_url, {
			action: 'ttbm_render_book_now',
			tour_id: $gate.data('tour-id'),
			template: $gate.data('template')
		}).done(function (res) {
			if (res && res.success && res.data && res.data.html) {
				$gate.replaceWith(res.data.html);
			} else {
				// Fall back to a reload only if the re-render itself failed —
				// the login/registration already succeeded either way.
				window.location.reload();
			}
		}).fail(function () {
			window.location.reload();
		});
	}
});
