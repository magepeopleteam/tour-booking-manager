jQuery(function ($) {
	'use strict';

	var $root = $('.ttbm-booking-mode');
	if (!$root.length || typeof ttbm_admin_ajax === 'undefined' || typeof ttbmPaymentSettings === 'undefined') {
		return;
	}

	/**
	 * Keep the editor warning, booking-mode warning and right-sidebar summary
	 * aligned with the server after an AJAX gateway change.
	 */
	function syncPaymentUi(state, closeWhenReady) {
		if (!state || typeof state.has_gateway === 'undefined') {
			return;
		}

		var isReady = state.has_gateway === true;
		var warning = state.warning || '';
		var gatewayNames = $.isArray(state.gateway_names) ? state.gateway_names : [];
		var $warningSlot = $('.ttbm-booking-mode-warning-slot');

		$warningSlot.empty();
		if (!isReady && warning) {
			$warningSlot.append(
				$('<div class="ttbm-booking-mode-warning"><span class="dashicons dashicons-warning"></span><p></p></div>')
					.find('p').text(warning).end()
			);
		}

		if (isReady) {
			$('#ttbm-edit-payment-notice').stop(true, true).fadeOut(150, function () { $(this).remove(); });
			$('.ttbm-pay-gateway-notice').stop(true, true).fadeOut(150, function () { $(this).remove(); });
		} else if ($('#ttbm-edit-payment-modal').length && !$('#ttbm-edit-payment-notice').length && $('#poststuff').length) {
			var $editNotice = $('<div class="ttbm-edit-payment-notice" id="ttbm-edit-payment-notice"></div>');
			$editNotice.text(ttbmPaymentSettings.edit_notice_text + ' ');
			$editNotice.append(
				$('<a href="#" class="ttbm-edit-payment-notice-link" data-ttbm-payment-modal-open></a>')
					.text(ttbmPaymentSettings.edit_notice_action)
			);
			$('#poststuff').prepend($editNotice);
		}

		var $sidebar = $('.ttbm-sb-payment-card');
		if ($sidebar.length) {
			var $values = $sidebar.find('.ttbm-sb-payment-info-row strong');
			$values.eq(0).text(state.mode_label || '');
			$values.eq(1).text(gatewayNames.length ? gatewayNames.join(', ') : ttbmPaymentSettings.none_label);

			var $action = $sidebar.find('.ttbm-sb-payment-link, .ttbm-sb-payment-warning').first();
			if (!$action.length) {
				$action = $('<p><a href="#" data-ttbm-payment-modal-open></a></p>').appendTo($sidebar.find('.ttbm-sb-payment-info-list'));
			}
			$action
				.toggleClass('ttbm-sb-payment-link', isReady)
				.toggleClass('ttbm-sb-payment-warning', !isReady)
				.find('a')
				.text(isReady ? ttbmPaymentSettings.payment_settings_label : ttbmPaymentSettings.configure_payment_label);
		}

		if (isReady && closeWhenReady && $('#ttbm-edit-payment-modal').is(':visible')) {
			setTimeout(function () {
				$('#ttbm-edit-payment-modal').fadeOut(150);
			}, 350);
		}
	}

	// ------------------------------------------------------------------
	// Booking Mode selector (WooCommerce Checkout vs Custom Payment) —
	// saves instantly via its own endpoint (not the main settings form),
	// then displays only the matching flow settings.
	// ------------------------------------------------------------------
	$(document).on('click', '.ttbm-booking-mode-card', function () {
		var $card = $(this);
		var $group = $card.closest('.ttbm-booking-mode');
		if ($card.hasClass('is-active') || $card.hasClass('is-disabled') || $group.data('can-choose') != 1) {
			return;
		}
		var $warnSlot = $group.find('.ttbm-booking-mode-warning-slot');
		var mode = $card.data('mode');
		var subtab = $card.data('subtab');
		var modeLabel = mode === 'custom' ? ttbmPaymentSettings.custom_mode_label : ttbmPaymentSettings.wc_mode_label;

		$group.find('.ttbm-booking-mode-card').addClass('is-saving');

		$.post(ttbm_admin_ajax.ajax_url, {
			action: 'ttbm_save_booking_mode',
			nonce: $group.data('nonce'),
			mode: mode
		}).done(function (res) {
			if (!res || !res.success) {
				if (window.ttbmToast) { window.ttbmToast((res && res.data) || ttbmPaymentSettings.error_label, 'error'); }
				return;
			}
			$group.find('.ttbm-booking-mode-card').removeClass('is-active');
			$card.addClass('is-active');
			// The "Active" badge only ever exists in the DOM for the active
			// card (see render_booking_mode_selector()) — move it here on
			// switch instead of toggling a CSS-driven visibility class.
			$group.find('.ttbm-booking-mode-badge').remove();
			$card.find('.ttbm-booking-mode-card-head').append(
				$('<span class="ttbm-booking-mode-badge"></span>').text(ttbmPaymentSettings.active_label || 'Active')
			);

			if (window.ttbmToast) {
				window.ttbmToast(ttbmPaymentSettings.mode_saved_label.replace('%s', modeLabel), 'success');
				if (res.data && res.data.has_gateway === false && res.data.warning) {
					window.ttbmToast(res.data.warning, 'warning', 5000);
				}
			}

			$('.ttbm-pay-mode-panel').hide();
			$('.ttbm-pay-mode-panel[data-subtab-panel="' + subtab + '"]').show();

			// Refresh the inline "no gateway configured" warning for the
			// newly active mode (never both possible modes at once).
			$warnSlot.empty();
			if (res.data && res.data.has_gateway === false && res.data.warning) {
				$warnSlot.append(
					$('<div class="ttbm-booking-mode-warning"><span class="dashicons dashicons-warning"></span><p></p></div>')
						.find('p').text(res.data.warning).end()
				);
			}

			// Refresh the mode-aware admin notice at the top of the page too.
			var $notice = $('.ttbm-pay-gateway-notice');
			if (res.data.warning) {
				if (!$notice.length) {
					$notice = $(
						'<div class="notice ttbm-pay-gateway-notice ttbm-pay-gateway-notice-modern">' +
							'<div class="ttbm-pay-gateway-notice-inner">' +
								'<span class="ttbm-pay-gateway-notice-icon" aria-hidden="true"><span class="dashicons dashicons-money-alt"></span></span>' +
								'<div class="ttbm-pay-gateway-notice-copy">' +
									'<span class="ttbm-pay-gateway-notice-kicker"></span>' +
									'<h3></h3><p></p>' +
								'</div>' +
								'<a class="ttbm-pay-gateway-notice-action"><span class="dashicons dashicons-admin-settings" aria-hidden="true"></span><span class="ttbm-pay-gateway-notice-action-label"></span></a>' +
							'</div>' +
						'</div>'
					);
					$notice.find('.ttbm-pay-gateway-notice-kicker').text(ttbmPaymentSettings.payment_notice_kicker);
					$notice.find('h3').text(ttbmPaymentSettings.payment_notice_title);
					$notice.find('.ttbm-pay-gateway-notice-action').attr('href', ttbmPaymentSettings.payment_settings_url);
					$('.ttbm-booking-mode').first().before($notice);
				}
				$notice.find('p').text(res.data.warning);
				$notice.find('.ttbm-pay-gateway-notice-action-label').text(
					mode === 'custom' ? ttbmPaymentSettings.enable_offline_label : ttbmPaymentSettings.configure_wc_label
				);
				$notice.show();
			} else {
				$notice.hide();
			}

			syncPaymentUi(res.data, false);
		}).fail(function () {
			if (window.ttbmToast) { window.ttbmToast(ttbmPaymentSettings.error_label, 'error'); }
		}).always(function () {
			$group.find('.ttbm-booking-mode-card').removeClass('is-saving');
		});
	});

	// ------------------------------------------------------------------
	// WooCommerce Payment Methods / Additional Settings accordions —
	// only one open at a time.
	// ------------------------------------------------------------------
	$(document).on('click', '.ttbm-pay-acc-header', function () {
		var $header = $(this);
		var acc = $header.data('acc');
		var willOpen = !$header.hasClass('is-open');

		$('.ttbm-pay-acc-header').removeClass('is-open');
		$('.ttbm-pay-acc-body').slideUp(150);

		if (willOpen) {
			$header.addClass('is-open');
			$('.ttbm-pay-acc-body[data-acc-body="' + acc + '"]').slideDown(150);
		}
	});

	// ------------------------------------------------------------------
	// "Activate WooCommerce Now" — opens a modal (matching the reference
	// plugin's Set Up WooCommerce popup) whose action button drives the
	// existing, already-working install/activate endpoint in
	// TTBM_Woo_Installer::ajax_woo_step() (download → extract → activate →
	// setup); nothing about the install itself is reimplemented here.
	// ------------------------------------------------------------------
	var $wooModal = $('#ttbm-woo-install-modal');
	var wooWorking = false;
	// The WooCommerce panel is hidden while Custom Payment is the only
	// available flow. Move the shared installer modal out of that panel so
	// the CTA inside the disabled WooCommerce card can still open it.
	if ($wooModal.length) {
		$wooModal.appendTo('body');
	}

	$(document).on('click', '.ttbm-woo-install-trigger', function (e) {
		e.preventDefault();
		$wooModal.css('display', 'flex').hide().fadeIn(200);
	});
	$(document).on('click', '#ttbm-woo-install-modal-close', function () {
		if (!wooWorking) {
			$wooModal.fadeOut(200);
		}
	});
	$(document).on('click', '#ttbm-woo-install-modal', function (e) {
		if (!wooWorking && $(e.target).is('#ttbm-woo-install-modal')) {
			$wooModal.fadeOut(200);
		}
	});

	$(document).on('click', '#ttbm-woo-modal-action-btn', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var nonce = $btn.data('nonce');
		var $info = $('#ttbm-woo-modal-info');
		var $progress = $('#ttbm-woo-modal-progress');
		var $fill = $('#ttbm-woo-modal-progress-fill');
		var $status = $('#ttbm-woo-modal-status-text');

		wooWorking = true;
		$btn.prop('disabled', true);
		$info.hide();
		$fill.css('width', '0%');
		$status.removeClass('is-success is-error').text('');
		$progress.fadeIn(200);

		function runStep(step) {
			$.post(ttbm_admin_ajax.ajax_url, {
				action: 'ttbm_woo_step',
				nonce: nonce,
				step: step
			}).done(function (res) {
				if (!res || !res.success || !res.data) {
					fail();
					return;
				}
				var data = res.data;
				if (typeof data.percent === 'number') {
					$fill.css('width', data.percent + '%');
				}
				if (data.message) {
					$status.text(data.message);
				}
				if (data.next && data.next !== 'done') {
					runStep(data.next);
				} else {
					succeed(data.message);
				}
			}).fail(fail);
		}

		function succeed(message) {
			$fill.css('width', '100%');
			$status.addClass('is-success').text(message || ttbmPaymentSettings.enabled_label);
			setTimeout(function () {
				refreshPaymentSettings(message);
			}, 400);
		}

		function refreshPaymentSettings(successMessage) {
			$.post(ttbm_admin_ajax.ajax_url, {
				action: 'ttbm_refresh_payment_settings',
				nonce: ttbm_admin_ajax.nonce
			}).done(function (res) {
				var $content = $('.ttbm-payment-settings-content').first();
				if (!res || !res.success || !res.data || !res.data.html || !$content.length) {
					fail();
					return;
				}
				$content.replaceWith(res.data.html);
				$wooModal.remove();
				$wooModal = $();
				wooWorking = false;
				syncPaymentUi(res.data, false);
				if (window.ttbmToast) {
					window.ttbmToast(successMessage || ttbmPaymentSettings.enabled_label, 'success');
				}
			}).fail(fail);
		}

		function fail() {
			wooWorking = false;
			$btn.prop('disabled', false);
			$fill.css('width', '100%');
			$status.addClass('is-error').text(ttbmPaymentSettings.error_label);
			setTimeout(function () {
				$progress.hide();
				$info.show();
			}, 3000);
		}

		runStep('download');
	});

	// ------------------------------------------------------------------
	// "Confirm Ticket Based on Payment Status" — shared with the General
	// tab's Seat Booked Status field; auto-saves on change into the same
	// ttbm_basic_gen_settings option (see TTBM_Payment_Settings::
	// ajax_save_book_status()).
	// ------------------------------------------------------------------
	$(document).on('change', '.ttbm-pay-book-status-input', function () {
		var $group = $(this).closest('.ttbm-pay-book-status');
		var $msg = $group.find('.ttbm-pay-book-status-msg');
		var statuses = [];
		$group.find('.ttbm-pay-book-status-input:checked').each(function () {
			statuses.push($(this).data('key'));
		});

		$.post(ttbm_admin_ajax.ajax_url, {
			action: 'ttbm_save_book_status',
			nonce: $group.data('nonce'),
			statuses: statuses
		}).done(function (res) {
			$msg.text(res.success ? ttbmPaymentSettings.enabled_label : ttbmPaymentSettings.error_label);
			setTimeout(function () { $msg.text(''); }, 1500);
		}).fail(function () {
			$msg.text(ttbmPaymentSettings.error_label);
			setTimeout(function () { $msg.text(''); }, 1500);
		});
	});

	// ------------------------------------------------------------------
	// Scoped "Save Changes" buttons — each only submits the field keys
	// listed in its own data-fields (Booking Confirmation Page + Allow
	// Guest Booking on the Custom Payment tab; Cart Redirect + Show
	// Billing Info on the WooCommerce tab), never the whole page.
	// ------------------------------------------------------------------
	$(document).on('click', '.ttbm-pay-misc-save-btn', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var $scope = $btn.closest('.ttbm-pay-subtab-panel');
		var keys = ($btn.data('fields') || '').toString().split(',');
		var fields = {};

		keys.forEach(function (key) {
			key = key.trim();
			if (!key) {
				return;
			}
			var $field = $scope.find('[name="ttbm_payment_settings[' + key + ']"]');
			if (!$field.length) {
				return;
			}
			fields[key] = $field.attr('type') === 'checkbox' ? ($field.is(':checked') ? 'on' : 'off') : $field.val();
		});

		$btn.prop('disabled', true);

		$.post(ttbm_admin_ajax.ajax_url, {
			action: 'ttbm_save_misc_fields',
			nonce: $btn.data('nonce'),
			fields: fields
		}).done(function (res) {
			if (res && res.success) {
				if (window.ttbmToast) { window.ttbmToast((res.data && res.data.message) || ttbmPaymentSettings.enabled_label, 'success'); }
			} else {
				if (window.ttbmToast) { window.ttbmToast((res && res.data) || ttbmPaymentSettings.error_label, 'error'); }
			}
		}).fail(function () {
			if (window.ttbmToast) { window.ttbmToast(ttbmPaymentSettings.error_label, 'error'); }
		}).always(function () {
			$btn.prop('disabled', false);
		});
	});

	// ------------------------------------------------------------------
	// Custom Payment cards (PayPal / Stripe / Offline) — each Configure
	// button opens its own modal; Save posts the modal's fields via AJAX
	// into the shared ttbm_payment_settings option.
	// ------------------------------------------------------------------
	$(document).on('click', '.ttbm-pm-gateways .ttbm-pm-configure-btn', function (e) {
		e.preventDefault();
		var modalId = $(this).data('modal');
		$('#' + modalId).css('display', 'flex').hide().fadeIn(150);
	});
	$(document).on('click', '.ttbm-gw-modal-close', function () {
		$(this).closest('.ttbm-gw-modal').fadeOut(150);
	});
	$(document).on('click', '.ttbm-gw-modal', function (e) {
		if ($(e.target).hasClass('ttbm-gw-modal')) {
			$(this).fadeOut(150);
		}
	});
	$(document).on('click', '.ttbm-gw-save-btn', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var $modal = $btn.closest('.ttbm-gw-modal-box');
		var gateway = $btn.data('gateway');
		var $msg = $modal.find('.ttbm-gw-save-msg');
		var fields = {};

		$modal.find('[data-field]').each(function () {
			var $field = $(this);
			var key = $field.data('field');
			if ($field.attr('type') === 'checkbox') {
				fields[key] = $field.is(':checked') ? 'on' : 'off';
			} else {
				fields[key] = $field.val();
			}
		});

		$btn.prop('disabled', true);
		$msg.hide();

		$.post(ttbm_admin_ajax.ajax_url, {
			action: 'ttbm_save_gateway_settings',
			nonce: ttbm_admin_ajax.nonce,
			gateway: gateway,
			fields: fields
		}).done(function (res) {
			if (res.success) {
				$msg.css('color', '#0a7c2f').text(res.data.message).fadeIn(150);
				var isEnabled = res.data.enabled === 'on';
				var $card = $('.ttbm-pm-card[data-gateway-id="' + gateway + '"]');
				$card.toggleClass('is-enabled', isEnabled).toggleClass('is-disabled', !isEnabled);
				$card.find('.ttbm-pm-badge').text(isEnabled ? ttbmPaymentSettings.enabled_label : ttbmPaymentSettings.disabled_label);
			} else {
				$msg.css('color', '#d63638').text(res.data || ttbmPaymentSettings.error_label).fadeIn(150);
			}
		}).fail(function () {
			$msg.css('color', '#d63638').text(ttbmPaymentSettings.error_label).fadeIn(150);
		}).always(function () {
			$btn.prop('disabled', false);
			setTimeout(function () { $msg.fadeOut(400); }, 2000);
		});
	});

	// Toggle a WooCommerce gateway's inline configuration form open/closed.
	$(document).on('click', '.ttbm-pay-subtab-panel[data-subtab-panel="woocommerce"] .ttbm-pm-configure-btn', function () {
		$(this).closest('.ttbm-pm-card').find('.ttbm-pm-body').slideToggle(150);
	});

	// Quick enable/disable from the card header switch.
	$(document).on('change', '.ttbm-pm-toggle-input', function () {
		var $input = $(this);
		var $card = $input.closest('.ttbm-pm-card');
		var gatewayId = $input.data('gateway-id');
		var enabled = $input.is(':checked') ? 'yes' : 'no';

		$input.prop('disabled', true);
		$.post(ttbm_admin_ajax.ajax_url, {
			action: 'ttbm_wc_toggle_gateway',
			nonce: ttbm_admin_ajax.nonce,
			gateway_id: gatewayId,
			enabled: enabled
		}).done(function (res) {
			var isEnabled = res.success && res.data.enabled === 'yes';
			$input.prop('checked', isEnabled);
			$card.toggleClass('is-enabled', isEnabled).toggleClass('is-disabled', !isEnabled);
			$card.find('.ttbm-pm-badge').text(isEnabled ? ttbmPaymentSettings.enabled_label : ttbmPaymentSettings.disabled_label);
			if (res.success && res.data.notice) {
				window.alert(res.data.notice);
			}
			if (res.success) {
				syncPaymentUi(res.data, true);
			}
		}).fail(function () {
			$input.prop('checked', !$input.is(':checked'));
		}).always(function () {
			$input.prop('disabled', false);
		});
	});

	// Save one gateway's native settings form via AJAX (WooCommerce's own
	// process_admin_options(), nothing reimplemented on our side).
	$(document).on('submit', '.ttbm-pm-form', function (e) {
		e.preventDefault();
		var $form = $(this);
		var $card = $form.closest('.ttbm-pm-card');
		var $btn = $form.find('.ttbm-pm-save-btn');
		var $status = $form.find('.ttbm-pm-status');
		var data = $form.serializeArray();
		data.push({ name: 'action', value: 'ttbm_wc_save_gateway' });
		data.push({ name: 'nonce', value: ttbm_admin_ajax.nonce });
		data.push({ name: 'gateway_id', value: $form.data('gateway-id') });

		$btn.prop('disabled', true);
		$status.removeClass('is-success is-error').text('');

		$.post(ttbm_admin_ajax.ajax_url, $.param(data)).done(function (res) {
			if (res.success) {
				$status.addClass('is-success').text(res.data.message);
				var isEnabled = res.data.enabled === 'yes';
				$card.toggleClass('is-enabled', isEnabled).toggleClass('is-disabled', !isEnabled);
				$card.find('.ttbm-pm-toggle-input').prop('checked', isEnabled);
				$card.find('.ttbm-pm-badge').text(isEnabled ? ttbmPaymentSettings.enabled_label : ttbmPaymentSettings.disabled_label);
				syncPaymentUi(res.data, true);
			} else {
				$status.addClass('is-error').text(res.data || ttbmPaymentSettings.error_label);
			}
		}).fail(function () {
			$status.addClass('is-error').text(ttbmPaymentSettings.error_label);
		}).always(function () {
			$btn.prop('disabled', false);
		});
	});
});
