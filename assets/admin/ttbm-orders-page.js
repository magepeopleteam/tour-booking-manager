jQuery(function ($) {
	'use strict';

	/*
	 * Kebab action menu.
	 *
	 * The orders table can be an overflow container at narrower widths. Moving
	 * the open menu to <body> keeps it above that container instead of creating
	 * an inner scrollbar or clipping the actions. Its fixed position is
	 * recalculated from the trigger and flips above it when space is limited.
	 */
	function closeActionMenus(returnFocus) {
		$('.ttbm-co-action-menu.is-portal-open').each(function () {
			var $menu = $(this);
			var $owner = $menu.data('ttbmActionOwner');
			var $trigger = $menu.data('ttbmActionTrigger');

			$menu.removeClass('is-portal-open').removeAttr('style');
			if ($owner && $owner.length) {
				$owner.append($menu);
			}
			$menu.removeData('ttbmActionOwner ttbmActionTrigger');
			if (returnFocus && $trigger && $trigger.length) {
				$trigger.trigger('focus');
			}
		});

		$('.ttbm-co-action-dropdown')
			.removeClass('is-open')
			.find('.ttbm-co-kebab-btn')
			.attr('aria-expanded', 'false');
	}

	function positionActionMenu($button, $menu) {
		var rect = $button[0].getBoundingClientRect();
		var viewport = window.visualViewport;
		var viewportLeft = viewport ? viewport.offsetLeft : 0;
		var viewportTop = viewport ? viewport.offsetTop : 0;
		var viewportWidth = viewport ? viewport.width : window.innerWidth;
		var viewportHeight = viewport ? viewport.height : window.innerHeight;
		var viewportRight = viewportLeft + viewportWidth;
		var viewportBottom = viewportTop + viewportHeight;
		var gutter = 8;
		var gap = 6;
		var menuWidth = $menu.outerWidth();
		var menuHeight = $menu.outerHeight();
		var left = rect.right - menuWidth;
		var top = rect.bottom + gap;

		left = Math.max(viewportLeft + gutter, Math.min(left, viewportRight - menuWidth - gutter));
		if (top + menuHeight > viewportBottom - gutter && rect.top - menuHeight - gap >= viewportTop + gutter) {
			top = rect.top - menuHeight - gap;
		}
		top = Math.max(viewportTop + gutter, Math.min(top, viewportBottom - menuHeight - gutter));

		$menu.css({
			left: Math.round(left) + 'px',
			top: Math.round(top) + 'px'
		});
	}

	// One menu is open at a time and every outside click closes it.
	$(document).on('click', '.ttbm-co-kebab-btn', function (e) {
		e.preventDefault();
		e.stopPropagation();
		var $button = $(this);
		var $dropdown = $(this).closest('.ttbm-co-action-dropdown');
		var wasOpen = $dropdown.hasClass('is-open');
		closeActionMenus();
		if (!wasOpen) {
			$dropdown.addClass('is-open');
			var $menu = $dropdown.find('.ttbm-co-action-menu');
			$menu
				.data('ttbmActionOwner', $dropdown)
				.data('ttbmActionTrigger', $button)
				.appendTo(document.body)
				.addClass('is-portal-open');
			$button.attr('aria-expanded', 'true');
			positionActionMenu($button, $menu);
			$menu.find('.ttbm-co-action-item').first().trigger('focus');
		}
	});
	$(document).on('click', function () {
		closeActionMenus();
	});
	$(document).on('click', '.ttbm-co-action-menu', function (e) {
		e.stopPropagation();
	});
	$(document).on('keydown', function (e) {
		if (e.key === 'Escape') {
			closeActionMenus(true);
		}
	});
	$(window).on('resize.ttbmOrders scroll.ttbmOrders', closeActionMenus);

	// Filter panel collapse toggle — the whole header bar is clickable.
	$(document).on('click', '[data-ttbm-filter-toggle]', function () {
		$(this).closest('.ttbm-co-filter-panel').toggleClass('is-collapsed');
	});

	// Add Note — appends to the order's activity timeline via AJAX, no reload.
	$(document).on('click', '.ttbm-co-note-add', function () {
		var $btn = $(this);
		var $wrap = $btn.closest('.ttbm-co-note-form');
		var $textarea = $wrap.find('.ttbm-co-note-input');
		var note = $.trim($textarea.val());
		if (!note) {
			return;
		}
		$btn.addClass('is-saving');
		$.post(ajaxurl, {
			action: 'ttbm_order_add_note',
			nonce: $wrap.data('nonce'),
			order_id: $wrap.data('order-id'),
			source: $wrap.data('source'),
			note: note
		}).done(function (res) {
			if (res && res.success && res.data && res.data.html) {
				var $list = $wrap.siblings('.ttbm-co-notes-list');
				$list.find('.ttbm-co-log-empty').remove();
				$list.prepend(res.data.html);
				$textarea.val('');
				if (window.ttbmToast) { window.ttbmToast('Note added.', 'success'); }
			} else if (window.ttbmToast) {
				window.ttbmToast((res && res.data) || 'Could not save the note.', 'error');
			}
		}).fail(function () {
			if (window.ttbmToast) { window.ttbmToast('Could not save the note.', 'error'); }
		}).always(function () {
			$btn.removeClass('is-saving');
		});
	});

	/* ===== Status pill patching (list row + detail-page pill share this) ===== */

	// Exact-match whitelist: a naive "strip anything starting with is-" would also
	// eat unrelated modifier classes on the same element.
	var STATUS_SLUG_CLASSES = ['pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed', 'partially-paid'].map(function (s) {
		return 'is-' + s;
	});

	function paintStatusPill($el, status, label) {
		var classes = ($el.attr('class') || '').split(/\s+/).filter(function (c) {
			return c && STATUS_SLUG_CLASSES.indexOf(c) === -1;
		});
		classes.push('is-' + status);
		$el.attr('class', classes.join(' ')).text(label);
	}

	function updateStatusPill(id, status, label) {
		paintStatusPill($('tr[data-row-id="' + id + '"] .ttbm-co-pill'), status, label);
		paintStatusPill($('.ttbm-co-current-status-pill[data-order-id="' + id + '"]'), status, label);
		$('.ttbm-co-change-status-btn[data-id="' + id + '"]').attr('data-status', status);
	}

	/* ===== Shared "Change Status" modal (list row kebab + detail-page header) ===== */

	function syncStatusOptionSelection() {
		$('#ttbm-co-status-modal-options .ttbm-co-status-option').each(function () {
			$(this).toggleClass('is-selected', $(this).find('input').is(':checked'));
		});
	}

	$(document).on('change', '#ttbm-co-status-modal-options input[type=radio]', syncStatusOptionSelection);

	$(document).on('click', '.ttbm-co-change-status-btn', function (e) {
		e.preventDefault();
		closeActionMenus();
		var id = $(this).data('id');
		var ref = $(this).data('ref');
		var source = $(this).data('source') || 'custom';
		$('#ttbm-co-status-modal-id').val(id);
		$('#ttbm-co-status-modal-source').val(source);
		$('#ttbm-co-status-modal-ref').text(ref ? ref : ('#' + id));
		// Only offer statuses valid for this order's source (e.g. hide an
		// extra WooCommerce-only status for a custom-payment order).
		$('#ttbm-co-status-modal-options .ttbm-co-status-option').each(function () {
			var s = $(this).data('source');
			$(this).toggle(s === 'both' || s === source);
		});
		$('#ttbm-co-status-modal-options input[type=radio]').prop('checked', false);
		$('#ttbm-co-status-modal-options input[value="' + $(this).data('status') + '"]').prop('checked', true);
		syncStatusOptionSelection();
		$('#ttbm-co-status-modal').hide().css('display', 'flex').hide().fadeIn(150);
	});

	$(document).on('click', '.ttbm-co-modal-close', function () {
		$(this).closest('.ttbm-co-modal').fadeOut(150);
	});
	$(document).on('click', '.ttbm-co-modal', function (e) {
		if (e.target === this) { $(this).fadeOut(150); }
	});

	$(document).on('click', '#ttbm-co-status-modal-save', function () {
		var $btn = $(this).toggleClass('is-saving', true).prop('disabled', true);
		var $modal = $('#ttbm-co-status-modal');
		var id = $('#ttbm-co-status-modal-id').val();
		var source = $('#ttbm-co-status-modal-source').val();
		var status = $('#ttbm-co-status-modal-options input[type=radio]:checked').val();
		if (!status) {
			if (window.ttbmToast) { window.ttbmToast('Please choose a status.', 'warning'); }
			$btn.toggleClass('is-saving', false).prop('disabled', false);
			return;
		}
		$.post(ajaxurl, {
			action: 'ttbm_order_update_status',
			nonce: $modal.data('nonce'),
			order_id: id,
			source: source,
			new_status: status
		}).done(function (res) {
			if (res && res.success) {
				updateStatusPill(id, res.data.status, res.data.label);
				if (res.data.log_entry) {
					$('.ttbm-co-log-list:not(.ttbm-co-notes-list)').prepend(res.data.log_entry);
				}
				if (window.ttbmToast) { window.ttbmToast('Order status updated.', 'success'); }
				$modal.fadeOut(150);
			} else if (window.ttbmToast) {
				window.ttbmToast((res && res.data && res.data.message) || 'Could not update the status.', 'error');
			}
		}).fail(function () {
			if (window.ttbmToast) { window.ttbmToast('Could not update the status.', 'error'); }
		}).always(function () {
			$btn.toggleClass('is-saving', false).prop('disabled', false);
		});
	});
});
