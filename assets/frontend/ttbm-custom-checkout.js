/**
 * Custom-payment checkout modal.
 * When Booking Mode is custom, intercepts add-to-cart after ticket validation
 * and opens a modal (summary → billing → payment → Complete Registration).
 */
(function ($) {
	'use strict';

	function escHtml(str) {
		return $('<span>').text(str == null ? '' : String(str)).html();
	}

	function formatPrice(amount) {
		if (typeof ttbm_price_format === 'function') {
			return ttbm_price_format(amount);
		}
		var n = parseFloat(amount);
		return isNaN(n) ? String(amount) : n.toFixed(2);
	}

	function resolveLineName($qty, type) {
		var $card = $qty.closest('.ttbm_smart_ticket_card, .ttbm_smart_addon_card, .ttbm_hotel_item, tr');
		var $scope = $card.length ? $card : $qty.closest('.ttbm_registration_area');
		var name = '';
		if (type === 'ticket') {
			name = $scope.find('[name^="ticket_name"]').first().val()
				|| $qty.closest('.ticket-type-name').data('ticket-type-name')
				|| $qty.closest('[data-ticket-type-name]').data('ticket-type-name')
				|| $scope.data('ticket-name')
				|| '';
			if (!name) {
				var $nextHidden = $qty.closest('tr').nextAll('.ttbm_hidden_inputs').first();
				name = $nextHidden.find('[name^="ticket_name"]').val() || '';
			}
			if (!name) {
				name = $.trim($scope.find('.ttbm_smart_ticket_card__name').first().text() || '');
			}
		} else {
			name = $scope.find('[name^="service_name"]').first().val()
				|| $scope.data('addon-name')
				|| '';
			if (!name) {
				var $svcHidden = $qty.closest('tr').nextAll('.ttbm_hidden_inputs').first();
				name = $svcHidden.find('[name^="service_name"]').val() || '';
			}
			if (!name) {
				name = $.trim($scope.find('.ttbm_smart_addon_name').first().text() || '');
			}
		}
		return $.trim(name) || (type === 'ticket' ? 'Ticket' : 'Extra service');
	}

	function collectLines($parent) {
		var lines = [];
		var total = 0;
		var ticketQtySelector = [
			'.mp_tour_ticket_type .formControl[data-price]',
			'.ttbm_smart_ticket_list .formControl[data-price]',
			'.ttbm_smart_ticket_card .formControl[data-price]'
		].join(', ');
		var serviceQtySelector = [
			'.mp_tour_ticket_extra .formControl[data-price]',
			'.ttbm_smart_addon_list .formControl[data-price]',
			'.ttbm_smart_addon_card .formControl[data-price]',
			'.ttbm_smart_addon_area .formControl[data-price]'
		].join(', ');

		$parent.find(ticketQtySelector).each(function () {
			var $qty = $(this);
			var qty = parseInt($qty.val(), 10) || 0;
			if (qty <= 0) {
				return;
			}
			var price = parseFloat($qty.attr('data-price')) || 0;
			var mult = qty;
			var $hotel = $qty.closest('.ttbm_hotel_item');
			if ($hotel.length) {
				var days = parseInt($hotel.find('[name="ttbm_hotel_num_of_day"]').val(), 10) || 1;
				mult = qty * days;
			}
			var lineTotal = price * mult;
			total += lineTotal;
			lines.push({
				name: resolveLineName($qty, 'ticket'),
				qty: qty,
				price: price,
				lineTotal: lineTotal,
				kind: 'ticket'
			});
		});

		$parent.find(serviceQtySelector).each(function () {
			var $qty = $(this);
			var qty = parseInt($qty.val(), 10) || 0;
			if (qty <= 0) {
				return;
			}
			var price = parseFloat($qty.attr('data-price')) || 0;
			var lineTotal = price * qty;
			total += lineTotal;
			lines.push({
				name: resolveLineName($qty, 'service'),
				qty: qty,
				price: price,
				lineTotal: lineTotal,
				kind: 'service'
			});
		});

		return { lines: lines, total: total };
	}

	function fieldLabel($item) {
		var $label = $item.find('.ttbm-field-label, .ttbm_form_item_label').first();
		if (!$label.length) {
			return '';
		}
		return $.trim($label.clone().children('sup, .textRequired').remove().end().text()).replace(/\*+\s*$/, '');
	}

	function fieldValue($item) {
		var type = ($item.attr('data-ttbm-type') || '').toLowerCase();
		if (type === 'checkbox-group' || $item.hasClass('groupCheckBox')) {
			var checked = [];
			$item.find('input[type="checkbox"]:checked').each(function () {
				var v = $.trim($(this).val() || '');
				var lab = $.trim($(this).closest('label').text() || '');
				checked.push(lab || v);
			});
			return checked.join(', ');
		}
		if (type === 'radio-group' || $item.hasClass('groupRadioBox')) {
			var $r = $item.find('input[type="radio"]:checked');
			if (!$r.length) {
				return '';
			}
			var rv = $.trim($r.val() || '');
			var rl = $.trim($r.closest('label').text() || '');
			return rl || rv;
		}
		var $input = $item.find('input, select, textarea').filter(function () {
			var t = (($(this).attr('type') || '') + '').toLowerCase();
			return t !== 'hidden' && t !== 'checkbox' && t !== 'radio' && t !== 'file' && !$(this).prop('disabled');
		}).first();
		if (!$input.length) {
			return '';
		}
		if ($input.is('select')) {
			return $.trim($input.find('option:selected').text() || $input.val() || '');
		}
		return $.trim($input.val() || '');
	}

	function resolveTicketTypeName($form) {
		var ticketType = $.trim($form.find('.form_title_text').first().text());
		if (ticketType) {
			return ticketType;
		}
		// Per-ticket form rows store ticket_name on the sibling hidden inputs / smart card.
		var $card = $form.closest('.ttbm_smart_ticket_card');
		if ($card.length) {
			ticketType = $.trim($card.find('input[name^="ticket_name"]').first().val() || '');
			if (ticketType) {
				return ticketType;
			}
		}
		var $attendeeRow = $form.closest('tr.ttbm_attendee_form_row');
		if ($attendeeRow.length) {
			ticketType = $.trim(
				$attendeeRow.prevAll('tr.ttbm_hidden_inputs').first().find('input[name^="ticket_name"]').val()
				|| ''
			);
			if (ticketType) {
				return ticketType;
			}
		}
		return '';
	}

	function buildGuestTitle($form, idx, totalVisible, counters) {
		var prefix = (typeof ttbmCustomCheckout !== 'undefined' && ttbmCustomCheckout.guest_info)
			? ttbmCustomCheckout.guest_info
			: 'Guest Information';
		var ticketType = resolveTicketTypeName($form);
		var guestNo = $.trim($form.find('.ttbm_attendee_title').first().text());
		if (!/^\d+$/.test(guestNo)) {
			guestNo = '';
		}

		// Prefer per-ticket-type sequence so Adult #1/#2 and Child #1 stay correct.
		if (ticketType) {
			counters[ticketType] = (counters[ticketType] || 0) + 1;
			guestNo = String(counters[ticketType]);
			return prefix + ': ' + ticketType + ' #' + guestNo;
		}

		if (!guestNo) {
			guestNo = String(idx + 1);
		}
		if (totalVisible > 1) {
			return (typeof ttbmCustomCheckout !== 'undefined' && ttbmCustomCheckout.guest_n)
				? ttbmCustomCheckout.guest_n.replace('%d', guestNo)
				: (prefix + ' #' + guestNo);
		}
		return prefix;
	}

	/**
	 * Collect filled guest/attendee form values from the registration area.
	 * Returns [{ title, rows: [{ label, value }] }, ...]
	 */
	function collectGuestInfo($parent) {
		var guests = [];
		var counters = {};
		var $forms = $parent.find('.ttbm_attendee_form_item').filter(function () {
			var $el = $(this);
			// Skip hidden template/placeholder clones and empty shells.
			if (!$el.is(':visible') || $el.find('.ttbm_form_item').length === 0) {
				return false;
			}
			return true;
		});
		// Also pick forms injected into the single-attendee area (may sit just
		// outside ticket rows but still inside registration).
		if (!$forms.length) {
			$forms = $parent.find('.ttbm_attendee_form_area .ttbm_attendee_form_item').filter(':visible');
		}

		var totalVisible = $forms.length;
		$forms.each(function (idx) {
			var $form = $(this);
			var rows = [];
			$form.find('.ttbm_form_item').each(function () {
				var $item = $(this);
				var label = fieldLabel($item);
				var value = fieldValue($item);
				if (!label || !value) {
					return;
				}
				rows.push({ label: label, value: value });
			});
			if (!rows.length) {
				return;
			}
			guests.push({
				title: buildGuestTitle($form, idx, totalVisible, counters),
				rows: rows
			});
		});

		return guests;
	}

	function renderGuestDetails($modal, guests) {
		var $wrap = $modal.find('#ttbm-cc-guest-details');
		if (!guests.length) {
			$wrap.empty().hide();
			return;
		}
		var html = '';
		guests.forEach(function (guest) {
			html += '<div class="ttbm-cc-guest-block">'
				+ '<div class="ttbm-cc-guest-title">' + escHtml(guest.title) + '</div>';
			guest.rows.forEach(function (row) {
				html += '<div class="ttbm-cc-guest-row">'
					+ '<span class="ttbm-cc-guest-label">' + escHtml(row.label) + '</span>'
					+ '<span class="ttbm-cc-guest-value">' + escHtml(row.value) + '</span>'
					+ '</div>';
			});
			html += '</div>';
		});
		$wrap.html(html).show();
	}

	function prefillBillingFromGuests($modal, guests) {
		if (!guests.length) {
			return;
		}
		var $name = $modal.find('#ttbm-cc-billing-name');
		var $email = $modal.find('#ttbm-cc-billing-email');
		var $phone = $modal.find('#ttbm-cc-billing-phone');
		if ($.trim($name.val()) && $.trim($email.val()) && $.trim($phone.val())) {
			return; // already populated (e.g. logged-in user)
		}
		guests[0].rows.forEach(function (row) {
			var label = row.label.toLowerCase();
			if (!$.trim($name.val()) && (label.indexOf('name') !== -1 || label.indexOf('full') !== -1)) {
				$name.val(row.value);
			} else if (!$.trim($email.val()) && (label.indexOf('email') !== -1 || row.value.indexOf('@') !== -1)) {
				$email.val(row.value);
			} else if (!$.trim($phone.val()) && (label.indexOf('phone') !== -1 || label.indexOf('mobile') !== -1 || label.indexOf('tel') !== -1)) {
				$phone.val(row.value);
			}
		});
	}

	function tourTitle($parent) {
		var fromTrigger = $parent.find('.ttbm_custom_checkout_trigger').data('tour-title');
		if (fromTrigger) {
			return String(fromTrigger);
		}
		var heading = $.trim($('.ttbm_title, .ttbm_details_title, h1.entry-title').first().text());
		return heading || '';
	}

	function tourDate($parent) {
		var raw = $.trim(
			$parent.find('[name="ttbm_start_date"]').val()
			|| $parent.find('[name="ttbm_date"]').val()
			|| $parent.find('[name="ttbm_hotel_date"]').val()
			|| $parent.find('[name="ttbm_checkin_date"]').val()
			|| ''
		);
		return raw;
	}

	function resetModalCheckoutView($modal) {
		setModalProcessing($modal, false);
		$modal.removeClass('is-success');
		$modal.find('.ttbm-cc-modal-summary, .ttbm-cc-modal-side').show();
		$modal.find('#ttbm-cc-result').hide().empty();
		$modal.find('.ttbm-cc-cancel-btn, #ttbm-cc-confirm-btn').show();
		$modal.find('#ttbm-cc-done-btn').hide();
		$modal.find('#ttbm-cc-confirm-btn .ttbm-cc-btn-text, #ttbm-cc-done-btn .ttbm-cc-btn-text').show();
		$modal.find('#ttbm-cc-confirm-btn .ttbm-cc-btn-loading').hide();
		$modal.find('#ttbm-cc-confirm-btn').prop('disabled', false).removeClass('is-loading');
		$modal.find('.ttbm-cc-modal-subtitle').text(
			(typeof ttbmCustomCheckout !== 'undefined' && ttbmCustomCheckout.review_subtitle)
				? ttbmCustomCheckout.review_subtitle
				: 'Review your booking and confirm your details.'
		);
		$modal.find('#ttbm-cc-modal-title').text(
			(typeof ttbmCustomCheckout !== 'undefined' && ttbmCustomCheckout.modal_title)
				? ttbmCustomCheckout.modal_title
				: 'Complete Registration'
		);
	}

	function setModalProcessing($modal, on) {
		var $box = $modal.find('.ttbm-cc-modal-box');
		$box.toggleClass('is-processing', !!on);
		$modal.toggleClass('is-processing', !!on);
		$box.find('.ttbm-cc-processing-lock').attr('aria-hidden', on ? 'false' : 'true');
		$box.find('.ttbm-cc-modal-close, .ttbm-cc-cancel-btn, .ttbm-cc-input, [name="ttbm_cc_payment_method"]')
			.prop('disabled', !!on);
	}

	function showModalSuccess($modal, html) {
		setModalProcessing($modal, false);
		$modal.addClass('is-success');
		$modal.find('.ttbm-cc-modal-summary, .ttbm-cc-modal-side').hide();
		$modal.find('#ttbm-cc-result').html(html).show();
		$modal.find('.ttbm-cc-cancel-btn, #ttbm-cc-confirm-btn').hide();
		$modal.find('#ttbm-cc-done-btn').show();
		$modal.find('#ttbm-cc-done-btn .ttbm-cc-btn-text').show();
		$modal.find('#ttbm-cc-modal-title').text(
			(typeof ttbmCustomCheckout !== 'undefined' && ttbmCustomCheckout.received_title)
				? ttbmCustomCheckout.received_title
				: 'Booking received'
		);
		$modal.find('.ttbm-cc-modal-subtitle').text('');
		var resultEl = $modal.find('#ttbm-cc-result').get(0);
		if (resultEl) {
			resultEl.scrollTop = 0;
		}
		$modal.find('.ttbm-cc-modal-body').scrollTop(0);
	}

	function openModal($area) {
		var $parent = $area.closest('.ttbm_registration_area');
		if (!$parent.length) {
			$parent = $area.closest('form');
		}
		if (!$parent.length) {
			$parent = $area.closest('.ttbm_smart_inline_booking, .ttbm-sidebar-booking');
		}
		var collected = collectLines($parent);
		if (!collected.lines.length) {
			// Fallback: booking bar may sit slightly outside ticket markup.
			collected = collectLines($area.closest('.ttbm_smart_inline_booking, .ttbm-sidebar-booking, .ttbm_booking_panel, form'));
		}
		if (!collected.lines.length) {
			alert((typeof ttbmCustomCheckout !== 'undefined' && ttbmCustomCheckout.select_ticket) || 'Please Select Ticket Type');
			return;
		}

		var $modal = $('#ttbm-custom-checkout-modal');
		if (!$modal.length) {
			return;
		}

		resetModalCheckoutView($modal);

		var html = '';
		collected.lines.forEach(function (line) {
			html += '<div class="ttbm-cc-line-row">'
				+ '<div class="ttbm-cc-line-info">'
				+ '<span class="ttbm-cc-line-name">' + escHtml(line.name) + '</span>'
				+ '<span class="ttbm-cc-line-sub">' + escHtml(String(line.qty)) + ' &times; ' + formatPrice(line.price) + '</span>'
				+ '</div>'
				+ '<span class="ttbm-cc-line-total">' + formatPrice(line.lineTotal) + '</span>'
				+ '</div>';
		});

		$modal.find('#ttbm-cc-line-items').html(html);
		$modal.find('#ttbm-cc-total-display').html(formatPrice(collected.total));

		var guests = collectGuestInfo($parent);
		renderGuestDetails($modal, guests);
		prefillBillingFromGuests($modal, guests);

		var title = tourTitle($parent);
		$modal.find('#ttbm-cc-tour-name').text(title).toggle(!!title);

		var date = tourDate($parent);
		var $date = $modal.find('#ttbm-cc-tour-date');
		if (date) {
			$date.text(date).show();
		} else {
			$date.text('').hide();
		}

		$modal.find('#ttbm-cc-checkout-msg').hide().removeClass('is-error is-success').text('');
		$modal.find('.ttbm-cc-input').removeClass('ttbm-cc-input-error');
		$modal.data('resume-area', $area);
		$modal.data('resume-parent', $parent);

		$modal.css('display', 'flex').hide().fadeIn(200);
		$('body').addClass('ttbm-cc-modal-open');
		$modal.find('#ttbm-cc-billing-name').trigger('focus');
	}

	function closeModal() {
		var $modal = $('#ttbm-custom-checkout-modal');
		if ($modal.hasClass('is-processing')) {
			return;
		}
		var wasSuccess = $modal.hasClass('is-success');
		$modal.fadeOut(150);
		$('body').removeClass('ttbm-cc-modal-open');
		resetModalCheckoutView($modal);
		// After a successful booking, refresh ticket availability without a full
		// navigation only if seats may have changed — soft reload of panel.
		if (wasSuccess) {
			var $area = $modal.data('resume-area');
			var $parent = $area && $area.length ? $area.closest('.ttbm_registration_area') : $();
			if ($parent.length && typeof get_ttbm_ticket === 'function') {
				var $date = $parent.find('[name="ttbm_date"]').first();
				if ($date.length) {
					get_ttbm_ticket($date);
				}
			}
		}
	}

	// Intercept the hidden add-to-cart click after Book Now validation
	// (and after the login gate has cleared the visitor, when login is required).
	$(document).on('click', '.ttbm_add_to_cart', function (e) {
		var $btn = $(this);
		var $area = $btn.closest('.ttbm_book_now_area');
		if (!$area.length || !$area.find('.ttbm_custom_checkout_trigger').length) {
			return;
		}
		if ($area.data('ttbm-require-login') && !$area.data('ttbm-login-verified')) {
			return; // login-gate.js owns this click
		}
		e.preventDefault();
		e.stopImmediatePropagation();
		openModal($area);
		return false;
	});

	$(document).on('click', '.ttbm-cc-modal-close, [data-ttbm-cc-close]', function (e) {
		e.preventDefault();
		closeModal();
	});
	$(document).on('click', '#ttbm-custom-checkout-modal', function (e) {
		if ($(e.target).is('#ttbm-custom-checkout-modal')) {
			closeModal();
		}
	});
	$(document).on('keydown', function (e) {
		if ((e.key === 'Escape' || e.keyCode === 27) && $('#ttbm-custom-checkout-modal:visible').length) {
			closeModal();
		}
	});

	$(document).on('click', '#ttbm-cc-confirm-btn', function (e) {
		e.preventDefault();
		var $btn = $(this);
		var $modal = $('#ttbm-custom-checkout-modal');
		var $msg = $modal.find('#ttbm-cc-checkout-msg');
		var $area = $modal.data('resume-area');
		var $parent = $modal.data('resume-parent');

		if (!$area || !$area.length || !$parent || !$parent.length) {
			return;
		}

		var name = $.trim($modal.find('#ttbm-cc-billing-name').val() || '');
		var email = $.trim($modal.find('#ttbm-cc-billing-email').val() || '');
		var phone = $.trim($modal.find('#ttbm-cc-billing-phone').val() || '');
		var payMethod = $modal.find('[name="ttbm_cc_payment_method"]:checked').val()
			|| $modal.find('[name="ttbm_cc_payment_method"]').filter('[type="hidden"]').val()
			|| '';

		$modal.find('.ttbm-cc-input').removeClass('ttbm-cc-input-error');
		$msg.hide().removeClass('is-error').text('');

		var missing = [];
		if (!name) {
			missing.push($modal.find('#ttbm-cc-billing-name'));
		}
		if (!email) {
			missing.push($modal.find('#ttbm-cc-billing-email'));
		}
		if (!phone) {
			missing.push($modal.find('#ttbm-cc-billing-phone'));
		}
		if (missing.length) {
			missing.forEach(function ($f) {
				$f.addClass('ttbm-cc-input-error');
			});
			$msg.text(
				(typeof ttbmCustomCheckout !== 'undefined' && ttbmCustomCheckout.billing_required)
					|| 'Please fill in all billing details (name, email and phone).'
			).addClass('is-error').show();
			return;
		}
		if (email.indexOf('@') === -1) {
			$modal.find('#ttbm-cc-billing-email').addClass('ttbm-cc-input-error');
			$msg.text(
				(typeof ttbmCustomCheckout !== 'undefined' && ttbmCustomCheckout.email_invalid)
					|| 'Please enter a valid email address.'
			).addClass('is-error').show();
			return;
		}
		if (!payMethod && $modal.find('[name="ttbm_cc_payment_method"]').filter('[type="radio"]').length) {
			$msg.text(
				(typeof ttbmCustomCheckout !== 'undefined' && ttbmCustomCheckout.payment_required)
					|| 'Please select a payment method.'
			).addClass('is-error').show();
			return;
		}

		$parent.find('.ttbm_billing_name_field').val(name);
		$parent.find('.ttbm_billing_email_field').val(email);
		$parent.find('.ttbm_billing_phone_field').val(phone);
		$parent.find('.ttbm_payment_method_field').val(payMethod);

		var submitBtn = $area.find('.ttbm_add_to_cart').get(0);
		var form = (submitBtn && submitBtn.form)
			|| $parent.find('form.mp_tour_ticket_form').get(0)
			|| $parent.find('form').get(0)
			|| $parent.closest('form').get(0)
			|| null;
		if (!form) {
			$msg.text(
				(typeof ttbmCustomCheckout !== 'undefined' && ttbmCustomCheckout.generic_error)
					|| 'Something went wrong while processing your booking. Please try again.'
			).addClass('is-error').show();
			return;
		}

		var formData = new FormData(form);
		formData.set('action', 'ttbm_custom_checkout');
		formData.set('ttbm_custom_checkout_ajax', '1');
		// Never post add-to-cart on admin-ajax — WooCommerce hijacks it and
		// redirects to the checkout page (HTML), so Complete Registration fails.
		formData.delete('add-to-cart');
		var productId = (submitBtn && submitBtn.value)
			|| $area.find('.ttbm_custom_checkout_trigger').data('product-id')
			|| $parent.find('.ttbm_custom_checkout_trigger').data('product-id')
			|| '';
		if (productId) {
			formData.set('ttbm_product_id', String(productId));
		}
		var tourId = $area.find('.ttbm_custom_checkout_trigger').data('tour-id')
			|| $parent.find('.ttbm_custom_checkout_trigger').data('tour-id');
		if (tourId) {
			formData.set('ttbm_id', String(tourId));
		}
		formData.set('ttbm_billing_name', name);
		formData.set('ttbm_billing_email', email);
		formData.set('ttbm_billing_phone', phone);
		formData.set('ttbm_payment_method', payMethod);

		$btn.prop('disabled', true).addClass('is-loading');
		$btn.find('.ttbm-cc-btn-text').hide();
		$btn.find('.ttbm-cc-btn-loading').show();
		setModalProcessing($modal, true);

		var ajaxUrl = (typeof ttbmCustomCheckout !== 'undefined' && ttbmCustomCheckout.ajax_url)
			? ttbmCustomCheckout.ajax_url
			: (typeof ttbm_ajax_url_pro !== 'undefined' ? ttbm_ajax_url_pro : '/wp-admin/admin-ajax.php');

		function resetConfirmBtn() {
			setModalProcessing($modal, false);
			$btn.prop('disabled', false).removeClass('is-loading');
			$btn.find('.ttbm-cc-btn-text').show();
			$btn.find('.ttbm-cc-btn-loading').hide();
		}

		function showCheckoutError(message) {
			$msg.text(
				message
				|| (typeof ttbmCustomCheckout !== 'undefined' && ttbmCustomCheckout.generic_error)
				|| 'Something went wrong while processing your booking. Please try again.'
			).addClass('is-error').show();
			resetConfirmBtn();
		}

		function handleCheckoutResponse(res) {
			if (!res || !res.success) {
				showCheckoutError(res && res.data && res.data.message);
				return;
			}
			var data = res.data || {};
			if (data.redirect) {
				window.location.href = data.redirect;
				return;
			}
			if (data.html) {
				showModalSuccess($modal, data.html);
				return;
			}
			// Booking succeeded but confirmation HTML was empty — still treat as OK.
			if (data.order_id) {
				showModalSuccess(
					$modal,
					'<div class="ttbm_booking_confirmation ttbm_style ttbm-cc-inline-confirmation">'
					+ '<div class="ttbm_booking_banner is-pending"><h3>'
					+ escHtml((typeof ttbmCustomCheckout !== 'undefined' && ttbmCustomCheckout.received_title) || 'Booking received')
					+ '</h3></div></div>'
				);
				return;
			}
			showCheckoutError();
		}

		function parseAjaxPayload(raw) {
			if (raw && typeof raw === 'object') {
				return raw;
			}
			if (typeof raw !== 'string') {
				return null;
			}
			// Recover JSON if PHP notices were prepended while debugging.
			var start = raw.indexOf('{');
			if (start === -1) {
				return null;
			}
			try {
				return JSON.parse(raw.slice(start));
			} catch (err) {
				return null;
			}
		}

		$.ajax({
			url: ajaxUrl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			dataType: 'text'
		}).done(function (raw) {
			handleCheckoutResponse(parseAjaxPayload(raw));
		}).fail(function (xhr) {
			var fromJson = xhr && xhr.responseJSON;
			if (fromJson) {
				handleCheckoutResponse(fromJson);
				return;
			}
			var recovered = parseAjaxPayload(xhr && xhr.responseText);
			if (recovered) {
				handleCheckoutResponse(recovered);
				return;
			}
			showCheckoutError();
		});
	});
}(jQuery));
