jQuery(function ($) {
	'use strict';

	if (!$('.ttbm-payment-settings-content').length || typeof ttbm_admin_ajax === 'undefined' || typeof ttbmPaymentSettings === 'undefined') {
		return;
	}
	var L = ttbmPaymentSettings;

	function toast(message, type, ms) {
		if (window.ttbmToast && message) {
			window.ttbmToast(message, type, ms);
		}
	}

	/**
	 * Bring the status message, the setup checklist, the editor warning and the
	 * right-sidebar summary in line with the server after any save.
	 */
	function syncPaymentUi(state, closeWhenReady) {
		if (!state || typeof state.has_gateway === 'undefined') {
			return;
		}
		var isReady = state.has_gateway === true;
		var gatewayNames = $.isArray(state.gateway_names) ? state.gateway_names : [];

		// Status message at the top of the payments screen.
		var $status = $('[data-pset-status]');
		if ($status.length) {
			$status.toggleClass('is-ready', isReady).toggleClass('is-warn', !isReady);
			if (typeof state.status_title === 'string') {
				$status.find('[data-pset-status-title]').text(state.status_title);
			}
			if (typeof state.status_text === 'string') {
				$status.find('[data-pset-status-text]').text(state.status_text);
			}
		}

		// Setup checklist.
		if (state.mode_label) {
			$('[data-pset-rail="mode"]').text(state.mode_label);
		}
		$('[data-pset-rail="methods"]').text(isReady && gatewayNames.length ? gatewayNames.join(', ') : L.rail_nothing);
		var $method = $('[data-pset-rail="method-step"]');
		var $rules = $('[data-pset-rail="rules-step"]');
		$method.toggleClass('is-done', isReady).toggleClass('is-todo', !isReady);
		$rules.toggleClass('is-next', isReady);
		if (isReady) {
			$method.removeAttr('aria-current');
			$rules.attr('aria-current', 'step');
		} else {
			$method.attr('aria-current', 'step');
			$rules.removeAttr('aria-current');
		}

		// Yellow banner on the tour / hotel editor.
		if (isReady) {
			$('#ttbm-edit-payment-notice').stop(true, true).fadeOut(150, function () { $(this).remove(); });
			$('.ttbm-pay-gateway-notice').stop(true, true).fadeOut(150, function () { $(this).remove(); });
		} else if ($('#ttbm-edit-payment-modal').length && !$('#ttbm-edit-payment-notice').length && ($('#post-body').length || $('#poststuff').length)) {
			var $editNotice = $('<div class="ttbm-edit-payment-notice" id="ttbm-edit-payment-notice"><div class="ttbm-edit-payment-notice-main"><span class="ttbm-edit-payment-notice-icon" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"></rect><path d="M2 10h20"></path></svg></span><div class="ttbm-edit-payment-notice-text"><strong></strong><span></span></div></div></div>');
			$editNotice.find('strong').text(L.edit_notice_text);
			$editNotice.find('.ttbm-edit-payment-notice-text span').text(L.edit_notice_action);
			$editNotice.append($('<a href="#" class="ttbm-edit-payment-notice-link" data-ttbm-payment-modal-open></a>').text(L.edit_notice_button || L.edit_notice_action));
			($('#post-body').length ? $('#post-body') : $('#poststuff')).prepend($editNotice);
		}

		// Payment card in the editor's right sidebar.
		var $sidebar = $('.ttbm-sb-payment-card');
		if ($sidebar.length) {
			var $sbStatus = $sidebar.find('.ttbm-sb-payment-status');
			$sidebar.toggleClass('is-ready', isReady).toggleClass('is-missing', !isReady);
			$sbStatus.find('.ttbm-sb-payment-status__text').text(isReady ? $sbStatus.attr('data-ready-text') : $sbStatus.attr('data-missing-text'));
			var $values = $sidebar.find('.ttbm-sb-payment-info-row strong');
			$values.eq(0).text(state.mode_label || '');
			$values.eq(1).text(gatewayNames.length ? gatewayNames.join(', ') : L.none_label);
			var $action = $sidebar.find('.ttbm-sb-payment-link, .ttbm-sb-payment-warning').first();
			if (!$action.length) {
				$action = $('<p><a href="#" data-ttbm-payment-modal-open></a></p>').appendTo($sidebar.find('.ttbm-sb-payment-info-list'));
			}
			$action.toggleClass('ttbm-sb-payment-link', isReady).toggleClass('ttbm-sb-payment-warning', !isReady)
				.find('a').text(isReady ? L.payment_settings_label : L.configure_payment_label);
		}

		if (isReady && closeWhenReady && $('#ttbm-edit-payment-modal').is(':visible')) {
			setTimeout(function () { $('#ttbm-edit-payment-modal').fadeOut(150); }, 350);
		}
	}

	// ------------------------------------------------------------------
	// Checkout tabs. selectTab() only shows a tab and its note; with both
	// checkouts available, a click also saves the new checkout (below).
	// ------------------------------------------------------------------
	function selectTab($tab) {
		var $tabs = $tab.closest('.ttbm-pset-tabs').find('[role="tab"]');
		$tabs.removeClass('is-active').attr({ 'aria-selected': 'false', tabindex: '-1' });
		$tab.addClass('is-active').attr({ 'aria-selected': 'true', tabindex: '0' });
		$tabs.each(function () {
			$('#' + $(this).attr('aria-controls')).prop('hidden', this !== $tab[0]);
		});
	}
	// Without a real choice the other tab only explains itself (e.g. WooCommerce not active).
	$(document).on('click', '.ttbm-pset-tabs:not([data-pset-mode-group]) [role="tab"]', function () {
		selectTab($(this));
	});
	// Arrow keys move between the tabs; Enter or Space picks one.
	$(document).on('keydown', '.ttbm-pset-tabs [role="tab"]', function (e) {
		var keys = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 };
		var $tabs = $(this).closest('.ttbm-pset-tabs').find('[role="tab"]');
		var i = $tabs.index(this);
		var next = null;
		if (keys[e.key]) {
			next = (i + keys[e.key] + $tabs.length) % $tabs.length;
		} else if (e.key === 'Home') {
			next = 0;
		} else if (e.key === 'End') {
			next = $tabs.length - 1;
		}
		if (next !== null) {
			e.preventDefault();
			$tabs.attr('tabindex', '-1').eq(next).attr('tabindex', '0').trigger('focus');
		}
	});

	$(document).on('click', '[data-pset-mode-group] .ttbm-booking-mode-card', function () {
		var $card = $(this);
		var $group = $card.closest('[data-pset-mode-group]');
		if ($card.hasClass('is-active')) {
			return;
		}
		var mode = $card.data('mode');
		var subtab = $card.data('subtab');
		var modeLabel = mode === 'custom' ? L.custom_mode_label : L.wc_mode_label;
		var $cards = $group.find('.ttbm-booking-mode-card').addClass('is-saving');

		$.post(ttbm_admin_ajax.ajax_url, {
			action: 'ttbm_save_booking_mode',
			nonce: $group.data('nonce'),
			mode: mode
		}).done(function (res) {
			if (!res || !res.success) {
				toast((res && res.data) || L.error_label, 'error');
				return;
			}
			selectTab($card);
			$cards.find('[data-pset-badge]').removeClass('is-use').text('');
			$card.find('[data-pset-badge]').addClass('is-use').text(L.in_use_label);
			toast(L.mode_saved_label.replace('%s', modeLabel), 'success');
			$('.ttbm-pay-mode-panel').hide();
			$('.ttbm-pay-mode-panel[data-subtab-panel="' + subtab + '"]').show();

			// The "payment setup required" notice at the top of the Settings page.
			var $notice = $('.ttbm-pay-gateway-notice');
			if (res.data && res.data.warning) {
				if (!$notice.length) {
					$notice = $(
						'<div class="notice ttbm-pay-gateway-notice ttbm-pay-gateway-notice-modern">' +
							'<div class="ttbm-pay-gateway-notice-inner">' +
								'<span class="ttbm-pay-gateway-notice-icon" aria-hidden="true"><span class="dashicons dashicons-money-alt"></span></span>' +
								'<div class="ttbm-pay-gateway-notice-copy"><span class="ttbm-pay-gateway-notice-kicker"></span><h3></h3><p></p></div>' +
								'<a class="ttbm-pay-gateway-notice-action"><span class="dashicons dashicons-admin-settings" aria-hidden="true"></span><span class="ttbm-pay-gateway-notice-action-label"></span></a>' +
							'</div>' +
						'</div>'
					);
					$notice.find('.ttbm-pay-gateway-notice-kicker').text(L.payment_notice_kicker);
					$notice.find('h3').text(L.payment_notice_title);
					$notice.find('.ttbm-pay-gateway-notice-action').attr('href', L.payment_settings_url);
					$('.ttbm-payment-settings-content').first().before($notice);
				}
				$notice.find('p').text(res.data.warning);
				$notice.find('.ttbm-pay-gateway-notice-action-label').text(mode === 'custom' ? L.enable_offline_label : L.configure_wc_label);
				$notice.show();
			} else {
				$notice.hide();
			}
			syncPaymentUi(res.data, false);
		}).fail(function () {
			toast(L.error_label, 'error');
		}).always(function () {
			$cards.removeClass('is-saving');
		});
	});

	// ------------------------------------------------------------------
	// WooCommerce panel accordions: only one open at a time.
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
	// "Use WooCommerce instead" / "Activate WooCommerce Now": opens the Set Up
	// WooCommerce popup, whose button drives TTBM_Woo_Installer::ajax_woo_step()
	// (download → extract → activate → setup).
	// ------------------------------------------------------------------
	var $wooModal = $('#ttbm-woo-install-modal');
	var wooWorking = false;
	// The WooCommerce panel is hidden while Custom Payment is in use; move the
	// popup out of it so the button in the Checkout row can still open it.
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
		var $msg = $('#ttbm-woo-modal-status-text');

		wooWorking = true;
		$btn.prop('disabled', true);
		$info.hide();
		$fill.css('width', '0%');
		$msg.removeClass('is-success is-error').text('');
		$progress.fadeIn(200);

		function runStep(step) {
			$.post(ttbm_admin_ajax.ajax_url, { action: 'ttbm_woo_step', nonce: nonce, step: step }).done(function (res) {
				if (!res || !res.success || !res.data) {
					fail();
					return;
				}
				if (typeof res.data.percent === 'number') {
					$fill.css('width', res.data.percent + '%');
				}
				if (res.data.message) {
					$msg.text(res.data.message);
				}
				if (res.data.next && res.data.next !== 'done') {
					runStep(res.data.next);
				} else {
					succeed(res.data.message);
				}
			}).fail(fail);
		}
		function succeed(message) {
			$fill.css('width', '100%');
			$msg.addClass('is-success').text(message || L.enabled_label);
			setTimeout(function () { refreshPaymentSettings(message); }, 400);
		}
		function refreshPaymentSettings(successMessage) {
			$.post(ttbm_admin_ajax.ajax_url, { action: 'ttbm_refresh_payment_settings', nonce: ttbm_admin_ajax.nonce }).done(function (res) {
				var $content = $('.ttbm-payment-settings-content').first();
				if (!res || !res.success || !res.data || !res.data.html || !$content.length) {
					fail();
					return;
				}
				$content.replaceWith(res.data.html);
				$wooModal.remove();
				$wooModal = $();
				wooWorking = false;
				rememberSaved();
				syncPaymentUi(res.data, false);
				toast(successMessage || L.enabled_label, 'success');
			}).fail(fail);
		}
		function fail() {
			wooWorking = false;
			$btn.prop('disabled', false);
			$fill.css('width', '100%');
			$msg.addClass('is-error').text(L.error_label);
			setTimeout(function () {
				$progress.hide();
				$info.show();
			}, 3000);
		}
		runStep('download');
	});

	// ------------------------------------------------------------------
	// WooCommerce panel: "Confirm ticket based on payment status" (saves on
	// change into ttbm_basic_gen_settings, shared with the General tab).
	// ------------------------------------------------------------------
	$(document).on('change', '.ttbm-pay-book-status-input', function () {
		var $group = $(this).closest('.ttbm-pay-book-status');
		var $msg = $group.find('.ttbm-pay-book-status-msg');
		var statuses = [];
		$group.find('.ttbm-pay-book-status-input:checked').each(function () {
			statuses.push($(this).data('key'));
		});
		$.post(ttbm_admin_ajax.ajax_url, { action: 'ttbm_save_book_status', nonce: $group.data('nonce'), statuses: statuses }).done(function (res) {
			$msg.text(res.success ? L.enabled_label : L.error_label);
			setTimeout(function () { $msg.text(''); }, 1500);
		}).fail(function () {
			$msg.text(L.error_label);
			setTimeout(function () { $msg.text(''); }, 1500);
		});
	});

	// WooCommerce panel: its scoped "Save Changes" (cart redirect, billing info).
	$(document).on('click', '.ttbm-pay-misc-save-btn', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var $scope = $btn.closest('.ttbm-pay-subtab-panel');
		var fields = {};
		($btn.data('fields') || '').toString().split(',').forEach(function (key) {
			key = key.trim();
			var $field = key ? $scope.find('[name="ttbm_payment_settings[' + key + ']"]') : $();
			if ($field.length) {
				fields[key] = $field.attr('type') === 'checkbox' ? ($field.is(':checked') ? 'on' : 'off') : $field.val();
			}
		});
		$btn.prop('disabled', true);
		$.post(ttbm_admin_ajax.ajax_url, { action: 'ttbm_save_misc_fields', nonce: $btn.data('nonce'), fields: fields }).done(function (res) {
			if (res && res.success) {
				toast((res.data && res.data.message) || L.saved_label, 'success');
			} else {
				toast((res && res.data) || L.error_label, 'error');
			}
		}).fail(function () {
			toast(L.error_label, 'error');
		}).always(function () {
			$btn.prop('disabled', false);
		});
	});

	// ------------------------------------------------------------------
	// Custom Payment panel: Offline Payment switches on in place; PayPal and
	// Stripe (PRO) keep their own setup popup; one Save for the whole panel.
	// ------------------------------------------------------------------
	var saved = null;

	function panel() {
		return $('.ttbm-pset-panel').first();
	}
	function readPanel() {
		var $p = panel();
		var state = {};
		$p.find('.ttbm-pset-method [data-field]').each(function () {
			var $f = $(this);
			state[$f.data('field')] = $f.is('[role="switch"]') ? $f.attr('aria-checked') : $f.val();
		});
		$p.find('select[name^="ttbm_payment_settings["]').each(function () {
			state[this.name] = $(this).val();
		});
		return state;
	}
	function rememberSaved() {
		saved = readPanel();
		setMsg('', '');
	}
	function setMsg(text, kind) {
		panel().find('[data-pset-msg]').text(text).removeClass('is-error is-saved').addClass(kind ? 'is-' + kind : '');
	}
	function isDirty() {
		return saved !== null && JSON.stringify(readPanel()) !== JSON.stringify(saved);
	}
	function markDirty() {
		setMsg(isDirty() ? L.unsaved_label : '', '');
	}
	function setSwitch($switch, on) {
		var $method = $switch.closest('.ttbm-pset-method');
		$switch.attr('aria-checked', on ? 'true' : 'false');
		$method.find('[data-pset-state]').text(on ? L.on_label : L.off_label);
		$method.find('.ttbm-pset-method__detail').prop('hidden', !on);
	}

	$(document).on('click', '.ttbm-pset-panel .ttbm-pset-switch', function () {
		setSwitch($(this), $(this).attr('aria-checked') !== 'true');
		markDirty();
	});
	$(document).on('input change', '.ttbm-pset-panel .ttbm-pset-input, .ttbm-pset-panel .ttbm-pset-select', markDirty);

	// Cancel (popup only): put back the saved values and close.
	$(document).on('click', '.ttbm-pset-panel [data-pset-cancel]', function () {
		var $p = panel();
		if (saved) {
			$p.find('.ttbm-pset-method [data-field]').each(function () {
				var $f = $(this);
				var value = saved[$f.data('field')];
				if ($f.is('[role="switch"]')) {
					setSwitch($f, value === 'true');
				} else {
					$f.val(value);
				}
			});
			$p.find('select[name^="ttbm_payment_settings["]').each(function () {
				$(this).val(saved[this.name]);
			});
		}
		setMsg('', '');
		$('#ttbm-edit-payment-modal').hide();
	});

	$(document).on('click', '.ttbm-pset-panel [data-pset-save]', function () {
		var $btn = $(this);
		var $p = panel();
		var $offline = $p.find('.ttbm-pset-method[data-gateway-id="offline"]');
		var gatewayFields = {};
		$offline.find('[data-field]').each(function () {
			var $f = $(this);
			gatewayFields[$f.data('field')] = $f.is('[role="switch"]') ? ($f.attr('aria-checked') === 'true' ? 'on' : 'off') : $f.val();
		});
		var miscFields = {
			ttbm_payment_confirmation_page: $p.find('[name="ttbm_payment_settings[ttbm_payment_confirmation_page]"]').val(),
			ttbm_payment_allow_guest_booking: $p.find('[name="ttbm_payment_settings[ttbm_payment_allow_guest_booking]"]').val()
		};

		$btn.prop('disabled', true);
		setMsg(L.saving_label, '');

		var gatewaySave = $offline.length
			? $.post(ttbm_admin_ajax.ajax_url, { action: 'ttbm_save_gateway_settings', nonce: ttbm_admin_ajax.nonce, gateway: 'offline', fields: gatewayFields })
			: $.Deferred().resolve([{ success: true }]).promise();

		gatewaySave.then(function (res) {
			if (!res || !res.success) {
				return $.Deferred().reject((res && res.data) || L.error_label).promise();
			}
			if ($offline.length) {
				$offline.toggleClass('is-on', res.data && res.data.enabled === 'on');
			}
			var state = res.data;
			return $.post(ttbm_admin_ajax.ajax_url, { action: 'ttbm_save_misc_fields', nonce: $btn.data('nonce'), fields: miscFields }).then(function (res2) {
				if (!res2 || !res2.success) {
					return $.Deferred().reject((res2 && res2.data) || L.error_label).promise();
				}
				return state;
			});
		}).done(function (state) {
			rememberSaved();
			setMsg(L.saved_label, 'saved');
			toast(L.saved_label, 'success');
			syncPaymentUi(state, false);
		}).fail(function (message) {
			var text = typeof message === 'string' ? message : L.error_label;
			setMsg(text, 'error');
			toast(text, 'error');
		}).always(function () {
			$btn.prop('disabled', false);
		});
	});

	// PayPal / Stripe (PRO): their own setup popup with keys and test mode.
	$(document).on('click', '[data-ttbm-gw-modal]', function (e) {
		e.preventDefault();
		$('#' + $(this).data('ttbm-gw-modal')).css('display', 'flex').hide().fadeIn(150);
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
		var $box = $btn.closest('.ttbm-gw-modal-box');
		var gateway = $btn.data('gateway');
		var $msg = $box.find('.ttbm-gw-save-msg');
		var fields = {};
		$box.find('[data-field]').each(function () {
			var $field = $(this);
			fields[$field.data('field')] = $field.attr('type') === 'checkbox' ? ($field.is(':checked') ? 'on' : 'off') : $field.val();
		});
		$btn.prop('disabled', true);
		$msg.hide();
		$.post(ttbm_admin_ajax.ajax_url, { action: 'ttbm_save_gateway_settings', nonce: ttbm_admin_ajax.nonce, gateway: gateway, fields: fields }).done(function (res) {
			if (res.success) {
				$msg.css('color', '#0a7c2f').text(res.data.message).fadeIn(150);
				var isEnabled = res.data.enabled === 'on';
				var $method = $('.ttbm-pset-method[data-gateway-id="' + gateway + '"]');
				$method.toggleClass('is-on', isEnabled).find('[data-pset-state]').text(isEnabled ? L.on_label : L.off_label);
				syncPaymentUi(res.data, false);
			} else {
				$msg.css('color', '#d63638').text(res.data || L.error_label).fadeIn(150);
			}
		}).fail(function () {
			$msg.css('color', '#d63638').text(L.error_label).fadeIn(150);
		}).always(function () {
			$btn.prop('disabled', false);
			setTimeout(function () { $msg.fadeOut(400); }, 2000);
		});
	});

	// ------------------------------------------------------------------
	// WooCommerce panel: gateway cards (inline form, quick on/off switch, save).
	// ------------------------------------------------------------------
	$(document).on('click', '.ttbm-pay-subtab-panel[data-subtab-panel="woocommerce"] .ttbm-pm-configure-btn', function () {
		var $card = $(this).closest('.ttbm-pm-card');
		$card.find('.ttbm-pm-body').slideToggle(150, function () {
			$card.toggleClass('is-open', $card.find('.ttbm-pm-body').first().is(':visible'));
		});
	});
	$(document).on('change', '.ttbm-pm-toggle-input', function () {
		var $input = $(this);
		var $card = $input.closest('.ttbm-pm-card');
		$input.prop('disabled', true);
		$.post(ttbm_admin_ajax.ajax_url, {
			action: 'ttbm_wc_toggle_gateway',
			nonce: ttbm_admin_ajax.nonce,
			gateway_id: $input.data('gateway-id'),
			enabled: $input.is(':checked') ? 'yes' : 'no'
		}).done(function (res) {
			var isEnabled = res.success && res.data.enabled === 'yes';
			$input.prop('checked', isEnabled);
			$card.toggleClass('is-enabled', isEnabled).toggleClass('is-disabled', !isEnabled);
			$card.find('.ttbm-pm-badge').text(isEnabled ? L.enabled_label : L.disabled_label);
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
	$(document).on('submit', '.ttbm-pm-form', function (e) {
		e.preventDefault();
		var $form = $(this);
		var $card = $form.closest('.ttbm-pm-card');
		var $btn = $form.find('.ttbm-pm-save-btn');
		var $msg = $form.find('.ttbm-pm-status');
		var data = $form.serializeArray();
		data.push({ name: 'action', value: 'ttbm_wc_save_gateway' });
		data.push({ name: 'nonce', value: ttbm_admin_ajax.nonce });
		data.push({ name: 'gateway_id', value: $form.data('gateway-id') });
		$btn.prop('disabled', true);
		$msg.removeClass('is-success is-error').text('');
		$.post(ttbm_admin_ajax.ajax_url, $.param(data)).done(function (res) {
			if (res.success) {
				$msg.addClass('is-success').text(res.data.message);
				var isEnabled = res.data.enabled === 'yes';
				$card.toggleClass('is-enabled', isEnabled).toggleClass('is-disabled', !isEnabled);
				$card.find('.ttbm-pm-toggle-input').prop('checked', isEnabled);
				$card.find('.ttbm-pm-badge').text(isEnabled ? L.enabled_label : L.disabled_label);
				syncPaymentUi(res.data, true);
			} else {
				$msg.addClass('is-error').text(res.data || L.error_label);
			}
		}).fail(function () {
			$msg.addClass('is-error').text(L.error_label);
		}).always(function () {
			$btn.prop('disabled', false);
		});
	});

	rememberSaved();
});
