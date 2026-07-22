/**
 * TTBM tour editor — background auto-save.
 *
 * Runs the real save pipeline (the same one the Update button triggers, including
 * live date/booking migration) over AJAX, without a page reload. It is:
 *   - debounced        (waits until the admin stops editing),
 *   - dirty-tracked    (only fires when something actually changed),
 *   - single-flight    (never overlaps requests — a server lock backs this up),
 *   - validation-gated  (never runs on incomplete data, so it can't force the
 *                        post to draft or migrate half-typed dates).
 *
 * All heavy form preparation is delegated to the existing
 * window.ttbmPrepareTourSettingsFormForSubmit() / ttbmValidateSettingsFormBeforeSubmit()
 * helpers so a manual Update and an auto-save always send identical data.
 */
(function ($) {
	'use strict';

	if (window.ttbmAutosaveBooted) {
		return;
	}
	window.ttbmAutosaveBooted = true;

	var cfg = window.ttbm_autosave_vars || {};
	var i18n = cfg.i18n || {};

	function t(key, fallback) {
		return (i18n && i18n[key]) ? i18n[key] : fallback;
	}

	// Only run on the tour edit screen, and only when enabled.
	if (cfg.enabled === false) {
		return;
	}
	if (!$('#ttbm_meta_box_panel').length) {
		return;
	}
	if ($('body').hasClass('post-type-ttbm_hotel')) {
		return; // hotel editor keeps its own flow for now
	}

	var DEBOUNCE_MS = parseInt(cfg.debounce, 10) || 2500;
	var MIN_INTERVAL_MS = parseInt(cfg.min_interval, 10) || 8000;
	var SUCCESS_TOAST_COOLDOWN = 30000;
	var RETRY_BUSY_MS = 3000;
	var RETRY_ERROR_MS = 15000;

	var state = {
		dirty: false,
		inFlight: false,
		queued: false,
		lastSuccessAt: 0,
		lastSuccessToastAt: 0,
		lastPausedToastAt: 0,
		timer: null,
		agoTimer: null,
		manual: false,
		requestedStatus: ''
	};

	var $pill = null;

	function toast(message, type, duration) {
		if (typeof window.ttbmToast === 'function' && message) {
			window.ttbmToast(message, type, duration);
		}
	}

	/* ---------------------------------------------------------------- status pill */

	function buildPill() {
		var $el = $(
			'<span class="ttbm-autosave-status" role="status" aria-live="polite">' +
				'<span class="ttbm-autosave-status__dot"></span>' +
				'<span class="ttbm-autosave-status__text"></span>' +
			'</span>'
		);
		var $right = $('.ttbm-header-right').first();
		if ($right.length) {
			var $preview = $right.find('.ttbm-header-preview').first();
			if ($preview.length) {
				$el.insertBefore($preview);
			} else {
				$right.prepend($el);
			}
		} else {
			$el.addClass('ttbm-autosave-status--floating');
			$('body').append($el);
		}
		return $el;
	}

	function ensurePill() {
		if ($pill && $pill.closest('body').length) {
			return $pill;
		}
		$pill = buildPill();
		return $pill;
	}

	// state: idle | dirty | saving | saved | paused | error
	function setStatus(status, text) {
		var $el = ensurePill();
		$el.attr('data-state', status);
		$el.find('.ttbm-autosave-status__text').text(text || '');
	}

	function humanAgo(ms) {
		var mins = Math.floor(ms / 60000);
		if (mins <= 0) {
			return t('just_now', 'just now');
		}
		if (mins === 1) {
			return t('one_min', '1 min ago');
		}
		return (t('mins_ago', '%d mins ago')).replace('%d', String(mins));
	}

	function refreshSavedLabel() {
		if (state.dirty || state.inFlight || !state.lastSuccessAt) {
			return;
		}
		setStatus('saved', (t('saved', 'Saved')) + ' • ' + humanAgo(Date.now() - state.lastSuccessAt));
	}

	/* ----------------------------------------------------------------- scheduling */

	function scheduleAutosave(delayOverride) {
		if (state.timer) {
			window.clearTimeout(state.timer);
		}
		var base = typeof delayOverride === 'number' ? delayOverride : DEBOUNCE_MS;
		var sinceLast = state.lastSuccessAt ? (Date.now() - state.lastSuccessAt) : MIN_INTERVAL_MS;
		var throttled = Math.max(base, MIN_INTERVAL_MS - sinceLast);
		state.timer = window.setTimeout(runAutosave, Math.max(0, throttled));
	}

	function markDirty() {
		state.dirty = true;
		if (!state.inFlight) {
			setStatus('dirty', t('unsaved', 'Unsaved changes'));
		}
		scheduleAutosave();
	}

	/* --------------------------------------------------------------------- saving */

	function runAutosave(force) {
		if (!state.dirty && force !== true) {
			return;
		}
		if (state.inFlight) {
			state.queued = true;
			return;
		}

		// Bring every hidden/date/ticket field in sync exactly like a manual save.
		if (typeof window.ttbmPrepareTourSettingsFormForSubmit === 'function') {
			window.ttbmPrepareTourSettingsFormForSubmit({ skipRemotePersistence: true });
		}

		// Run validation to highlight incomplete required fields, but do not block
		// background persistence. The server's auto-save mode never forces a post
		// to draft and returns validation warnings in JSON, so valid changes from
		// other tabs must not be lost merely because one required field is missing.
		if (typeof window.ttbmValidateSettingsFormBeforeSubmit === 'function') {
			window.ttbmLastValidationFocus = null;
			var formIsValid = window.ttbmValidateSettingsFormBeforeSubmit();
			if (state.manual && !formIsValid) {
				setStatus('paused', t('paused', 'Auto-save paused — complete required fields'));
				toast(t('paused_toast', 'Please complete the highlighted required fields before saving.'), 'warning', 7000);
				state.manual = false;
				state.requestedStatus = '';
				return;
			}
		}

		// The modern TTBM editor can visually relocate the metabox outside
		// WordPress's form#post. Serialize the unique union of both containers so
		// detached tour fields (dates, toggles, radios, repeaters, add-ons) are not
		// omitted from the request.
		var $payloadFields = $('#post :input, #ttbm_meta_box_panel :input');
		var payload = $payloadFields.serialize() +
			'&action=ttbm_autosave_tour' +
			'&nonce=' + encodeURIComponent(cfg.nonce || '') +
			'&post_ID=' + encodeURIComponent(cfg.post_id || $('#post [name="post_ID"]').val() || '') +
			'&requested_post_status=' + encodeURIComponent(state.requestedStatus || '');
		var isManualSave = state.manual;
		state.manual = false;
		state.requestedStatus = '';

		// Preparing the form re-enabled toggled-off fields so their values serialize.
		// With no page reload we must restore the greyed/disabled visual state.
		if (typeof window.ttbmInitGeneralInfoToggles === 'function') {
			window.ttbmInitGeneralInfoToggles();
		}

		state.inFlight = true;
		state.dirty = false; // optimistic; edits during flight re-mark dirty
		setStatus('saving', t('saving', 'Saving…'));

		$.ajax({
			url: cfg.ajax_url,
			method: 'POST',
			data: payload,
			dataType: 'json'
		}).done(function (res) {
			if (res && res.success) {
				var persisted = (res.data && res.data.persisted) || {};
				var submittedTravelType = ($('#post [name="ttbm_travel_type"]').val() || '').trim();
				if (submittedTravelType && persisted.travel_type !== submittedTravelType) {
					handleFailure({
						responseJSON: { data: { message: t('error_toast', 'Auto-save failed — your changes are not saved yet.') } }
					});
					return;
				}
				state.lastSuccessAt = Date.now();
				if (res.data && res.data.post_status) {
					var postStatus = res.data.post_status;
					var $postStatus = $('.ttbm-header-status');
					$postStatus.removeClass(function (index, className) {
						return (className.match(/(^|\s)is-status-\S+/g) || []).join(' ');
					}).addClass('is-status-' + postStatus)
						.text(postStatus === 'publish' ? 'Published' : (postStatus === 'pending' ? 'Pending' : 'Draft'));
					var $mainButton = $('.ttbm-split-publish__main');
					if (postStatus === 'publish') {
						$mainButton.attr('data-ttbm-save-action', 'update').text('Update');
					} else {
						$mainButton.text($mainButton.attr('data-ttbm-save-action') === 'publish' ? 'Publish' : 'Update');
					}
				}
				refreshSavedLabel();
				var warnings = (res.data && res.data.warnings) || [];
				if (warnings.length) {
					toast(warnings[0], 'warning', isManualSave ? 7000 : undefined);
				} else if (isManualSave || Date.now() - state.lastSuccessToastAt > SUCCESS_TOAST_COOLDOWN) {
					state.lastSuccessToastAt = Date.now();
					toast(isManualSave ? 'Tour saved successfully.' : t('saved_toast', 'All changes auto-saved.'), 'success', isManualSave ? 7000 : 2000);
				}
			} else {
				handleFailure(null);
			}
		}).fail(function (xhr) {
			handleFailure(xhr);
		}).always(function () {
			state.inFlight = false;
			if (state.queued || state.dirty) {
				state.queued = false;
				scheduleAutosave(RETRY_BUSY_MS);
			} else if (state.lastSuccessAt) {
				// refreshSavedLabel() intentionally ignores in-flight requests, so it
				// must run after inFlight is cleared or the pill remains on "Saving…"
				// until the 30-second relative-time timer fires.
				refreshSavedLabel();
			}
		});
	}

	function handleFailure(xhr) {
		state.dirty = true; // so it retries
		var status = xhr && xhr.status;
		if (status === 409) {
			// Another save is in progress server-side; just retry shortly.
			setStatus('saving', t('saving', 'Saving…'));
			scheduleAutosave(RETRY_BUSY_MS);
			return;
		}
		var msg = t('error_toast', 'Auto-save failed — your changes are not saved yet.');
		if (xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
			msg = xhr.responseJSON.data.message;
		}
		setStatus('error', t('error', 'Auto-save failed — will retry'));
		toast(msg, 'error');
		scheduleAutosave(RETRY_ERROR_MS);
	}

	/* ------------------------------------------------------------- change tracking */

	var $panel = $('#ttbm_meta_box_panel');

	// Typed/selected fields inside the settings panel + the tour title.
	$(document).on('input change', '#ttbm_meta_box_panel :input, #ttbm_post_title, #ttbm_thumb_id', function () {
		var type = ($(this).attr('type') || '').toLowerCase();
		if (type === 'button' || type === 'submit') {
			return;
		}
		markDirty();
	});

	// TinyMCE edits do not emit input/change on their backing textareas.
	$(document).on('tinymce-editor-init', function (event, editor) {
		if (!editor || !$(editor.getElement()).closest('#ttbm_meta_box_panel').length) {
			return;
		}
		editor.on('input change undo redo', markDirty);
	});

	// Radio-card / toggle clicks write hidden inputs asynchronously (no change event).
	$(document).on('click', '#ttbm_meta_box_panel [data-group-radio], #ttbm_meta_box_panel [data-collapse-target], #ttbm_meta_box_panel .roundSwitchLabel', function () {
		window.setTimeout(markDirty, 0);
	});

	// Adding/removing repeater rows (dates, tickets, services, places…).
	$(document).on('click', '#ttbm_meta_box_panel .ttbm_add_item, #ttbm_meta_box_panel .ttbm_item_remove, #ttbm_meta_box_panel .ttbm_remove_icon, #ttbm_meta_box_panel .ttbm-field-clear', function () {
		window.setTimeout(markDirty, 0);
	});

	// Update/Publish uses the same verified AJAX pipeline without reloading.
	$(document).on('click', '#publish, #save-post, .editor-post-publish-button, .editor-post-publish-button__button, .editor-post-save-draft, .ttbm-split-publish__main, .ttbm-split-publish__draft', function (event) {
		event.preventDefault();
		event.stopImmediatePropagation();
		if (state.timer) {
			window.clearTimeout(state.timer);
			state.timer = null;
		}
		state.dirty = true;
		state.manual = true;
		if ($(this).is('.ttbm-split-publish__draft, #save-post, .editor-post-save-draft')) {
			state.requestedStatus = 'draft';
		} else if ($(this).is('.ttbm-split-publish__main')) {
			state.requestedStatus = $(this).attr('data-ttbm-save-action') === 'publish' ? 'publish' : '';
		} else {
			state.requestedStatus = 'publish';
		}
		runAutosave(true);
		return false;
	});

	$(document).on('submit', 'form#post', function (event) {
		event.preventDefault();
		if (state.timer) {
			window.clearTimeout(state.timer);
			state.timer = null;
		}
		state.dirty = true;
		state.manual = true;
		var mainAction = $('.ttbm-split-publish__main').attr('data-ttbm-save-action');
		state.requestedStatus = mainAction === 'publish' ? 'publish' : '';
		runAutosave(true);
		return false;
	});

	// Flush pending work when the tab is hidden (best effort, non-blocking).
	document.addEventListener('visibilitychange', function () {
		if (document.visibilityState === 'hidden' && state.dirty && !state.inFlight) {
			if (state.timer) {
				window.clearTimeout(state.timer);
				state.timer = null;
			}
			runAutosave();
		}
	});

	/* ----------------------------------------------------------------------- boot */

	$(function () {
		// The modern header is injected asynchronously; place the pill once it exists.
		var tries = 0;
		var placer = window.setInterval(function () {
			tries++;
			if ($('.ttbm-header-right').length || tries > 20) {
				window.clearInterval(placer);
				ensurePill();
				setStatus('idle', t('ready', 'Auto-save on'));
			}
		}, 150);

		state.agoTimer = window.setInterval(refreshSavedLabel, 30000);
	});
})(jQuery);
