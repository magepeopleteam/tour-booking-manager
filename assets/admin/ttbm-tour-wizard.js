/**
 * Tour editor: step-based layout.
 *
 * The legacy tab panels (#ttbm_general_info, #ttbm_settings_dates, ...) stay in the DOM and keep
 * their ids, so validation, auto-save, the map and add-ons keep working. This script only groups
 * them into steps: a step shows its panes stacked ("plain") or as collapsible cards ("acc"), and a
 * legacy tab activation (e.g. a validation jump) switches to the step that owns the pane.
 */
(function ($) {
	'use strict';

	var $root = $('#ttbm_content.ttbm-tour-wizard');
	if (!$root.length) {
		return;
	}
	var $content = $root.find('.tabsContent:first');
	var $footer = $content.children('.ttbm-wz-footer');
	var $nav = $root.find('.ttbm-wz-steps');
	var config = [];
	try {
		config = JSON.parse($root.attr('data-wizard') || '[]');
	} catch (e) {
		config = [];
	}
	if (!config.length || !$content.length) {
		return;
	}
	$root.addClass('ttbm-wz-enabled');

	// The page header's Publish/Update button (injected by the sidebar script), else core's #publish.
	function mainSaveButton() {
		var $b = $('.ttbm-split-publish__main').first();
		return $b.length ? $b : $('#publish').first();
	}
	function esc(sel) {
		return String(sel).replace(/"/g, '\\"');
	}
	function $pane(id) {
		return $content.children('[data-tabs="' + esc(id) + '"]');
	}
	function $tabBtn(id) {
		return $root.find('.tabLists [data-tabs-target="' + esc(id) + '"]').first();
	}
	function isBlank($p) {
		return !$p.children().length && !$.trim($p.text());
	}

	// ---- Build the model: step -> ordered list of {id, acc} -------------------------------
	var model = {};
	var placed = {};
	config.forEach(function (s) {
		model[s.key] = [];
		(s.panes || []).forEach(function (id) {
			if ($pane(id).length && !placed[id]) {
				placed[id] = true;
				model[s.key].push({id: id, acc: false});
			}
		});
		(s.acc || []).forEach(function (id) {
			var $p = $pane(id);
			if ($p.length && !placed[id] && !isBlank($p)) {
				placed[id] = true;
				model[s.key].push({id: id, acc: true});
			}
		});
	});
	// Add-on tabs we don't know about: keep them reachable, placed by their spot in the tab list.
	var tabIds = $root.find('.tabLists [data-tabs-target]').map(function () {
		return $(this).attr('data-tabs-target');
	}).get();
	var idxDates = tabIds.indexOf('#ttbm_settings_dates');
	var idxMore = -1;
	tabIds.forEach(function (id, i) {
		if (idxMore < 0 && placed[id] && model.more && model.more.some(function (m) { return m.id === id; })) {
			idxMore = i;
		}
	});
	tabIds.forEach(function (id, i) {
		if (placed[id] || !$pane(id).length) {
			return;
		}
		var inPricing = idxDates >= 0 && i > idxDates && (idxMore < 0 || i < idxMore);
		var key = inPricing && model.pricing ? 'pricing' : (model.more ? 'more' : config[config.length - 1].key);
		placed[id] = true;
		model[key].push({id: id, acc: true});
	});

	// ---- Collapsible cards for "acc" panes -----------------------------------------------
	var $heads = {};
	function buildHead(item) {
		var $btn = $tabBtn(item.id);
		var icon = $btn.find('i').first().attr('class') || 'fas fa-cog';
		var title = $.trim($btn.find('span').first().text()) || $.trim($pane(item.id).find('h2').first().text());
		var $head = $(
			'<div class="ttbm-wz-acc" data-pane="' + item.id + '">' +
			'<button type="button" class="ttbm-wz-acc__head" aria-expanded="false">' +
			'<span class="ttbm-wz-acc__icon"><i></i></span>' +
			'<span class="ttbm-wz-acc__title"></span>' +
			'<span class="ttbm-wz-acc__chev"><i class="fas fa-chevron-down"></i></span>' +
			'</button></div>'
		);
		$head.find('.ttbm-wz-acc__icon i').attr('class', icon);
		$head.find('.ttbm-wz-acc__title').text(title);
		return $head;
	}
	config.forEach(function (s) {
		model[s.key].forEach(function (item) {
			var $p = $pane(item.id);
			if (item.acc) {
				$p.addClass('ttbm-wz-acc-pane');
				$heads[item.id] = buildHead(item);
				$footer.before($heads[item.id]);
			}
			$footer.before($p); // also puts every pane in step order
		});
	});

	// ---- Showing a step ------------------------------------------------------------------
	var current = '';
	var openAcc = {};

	function initPane(id) {
		var $p = $pane(id);
		if (!$p.length) {
			return;
		}
		var first = !$p.data('ttbm-ui-initialized');
		if (typeof window.ttbm_init_dynamic_ui === 'function') {
			window.ttbm_init_dynamic_ui($p, first ? {} : {light: true});
		}
		$p.data('ttbm-ui-initialized', 1);
		$(document).trigger('ttbm_tab_activated', [id, $p, $tabBtn(id)]);
	}

	function applyVisibility() {
		$content.children('.tabsItem[data-tabs]').removeClass('ttbm-wz-show');
		$content.children('.ttbm-wz-acc').removeClass('is-shown');
		var shown = [];
		(model[current] || []).forEach(function (item) {
			if (item.acc) {
				var open = !!openAcc[item.id];
				$heads[item.id].addClass('is-shown').toggleClass('is-open', open)
					.find('.ttbm-wz-acc__head').attr('aria-expanded', open ? 'true' : 'false');
				if (open) {
					$pane(item.id).addClass('ttbm-wz-show');
					shown.push(item.id);
				}
			} else {
				$pane(item.id).addClass('ttbm-wz-show');
				shown.push(item.id);
			}
		});
		return shown;
	}

	function stepIndex(key) {
		for (var i = 0; i < config.length; i++) {
			if (config[i].key === key) {
				return i;
			}
		}
		return 0;
	}

	function updateChrome() {
		var idx = stepIndex(current);
		$nav.find('.ttbm-wz-step').each(function (i) {
			$(this).toggleClass('is-active', i === idx).toggleClass('is-complete', i < idx)
				.attr('aria-current', i === idx ? 'step' : null);
		});
		var last = idx === config.length - 1;
		$footer.find('.ttbm-wz-prev').prop('disabled', idx === 0).toggleClass('is-hidden', idx === 0);
		var finalLabel = $.trim(mainSaveButton().text()) || $.trim(mainSaveButton().val()) || 'Save';
		$footer.find('.ttbm-wz-next').text(last ? finalLabel : $footer.data('next-label'));
		$footer.find('.ttbm-wz-progress').text((idx + 1) + ' / ' + config.length);
		$root.attr('data-wz-step', current);
	}

	// Activate the step's primary legacy tab so the right sidebar, hash and saved-tab stay in sync.
	function syncLegacyTab(item) {
		var $b = $tabBtn(item.id);
		if ($b.length && !$b.hasClass('active')) {
			$b.trigger('click');
		}
	}

	function showStep(key, opts) {
		opts = opts || {};
		if (!model[key] || !model[key].length) {
			return;
		}
		var changed = key !== current;
		current = key;
		if (!openAcc.__seeded) {
			openAcc.__seeded = {};
		}
		// First card of an all-card step starts open so the step never looks empty.
		if (!openAcc.__seeded[key]) {
			openAcc.__seeded[key] = true;
			var firstItem = model[key][0];
			if (firstItem && firstItem.acc && !model[key].some(function (m) { return !m.acc; })) {
				openAcc[firstItem.id] = true;
			}
		}
		var shown = applyVisibility();
		updateChrome();
		shown.forEach(initPane);
		if (!opts.fromLegacy) {
			syncLegacyTab(model[key][0]);
		}
		if (changed && opts.scroll !== false) {
			var top = $nav.offset() ? $nav.offset().top - 90 : 0;
			if (top < $(window).scrollTop()) {
				$('html,body').animate({scrollTop: Math.max(0, top)}, 200);
			}
		}
	}

	function stepOfPane(id) {
		for (var k in model) {
			if (model[k].some(function (m) { return m.id === id; })) {
				return k;
			}
		}
		return '';
	}

	function toggleAcc(id, open) {
		openAcc[id] = open;
		applyVisibility();
		if (open) {
			initPane(id);
		}
	}

	// ---- Events --------------------------------------------------------------------------
	$footer.data('next-label', $footer.find('.ttbm-wz-next').text());

	$nav.on('click', '.ttbm-wz-step', function () {
		showStep($(this).attr('data-step'));
	});

	$content.on('click', '.ttbm-wz-acc__head', function () {
		var id = $(this).closest('.ttbm-wz-acc').attr('data-pane');
		toggleAcc(id, !openAcc[id]);
	});

	$footer.on('click', '.ttbm-wz-prev', function () {
		var idx = stepIndex(current);
		if (idx > 0) {
			showStep(config[idx - 1].key);
		}
	});

	// Block "Next" on the steps that have required fields, reusing the editor's own validators.
	function validateStep(key) {
		window.ttbmLastValidationFocus = null;
		var ok = true;
		if (key === 'basic') {
			if (typeof window.ttbmValidateTitle === 'function' && !window.ttbmValidateTitle()) {
				ok = false;
			}
			if (ok && typeof window.ttbmValidateLocation === 'function' && !window.ttbmValidateLocation()) {
				ok = false;
			}
		} else if (key === 'schedule') {
			if (typeof window.ttbmValidateDates === 'function' && !window.ttbmValidateDates()) {
				ok = false;
			}
		}
		if (!ok) {
			if (typeof window.ttbmFocusValidationTarget === 'function') {
				window.ttbmFocusValidationTarget();
			}
			if (typeof window.ttbmToast === 'function') {
				window.ttbmToast('Please complete the highlighted required fields to continue.', 'error');
			}
		}
		return ok;
	}

	$footer.on('click', '.ttbm-wz-next', function () {
		var idx = stepIndex(current);
		if (idx >= config.length - 1) {
			var $save = mainSaveButton();
			if ($save.length) {
				$save.trigger('click');
			} else {
				$('form#post').trigger('submit');
			}
			return;
		}
		if (!validateStep(current)) {
			return;
		}
		showStep(config[idx + 1].key);
	});

	// A legacy tab activation (validation jump, saved tab, #hash) -> show the owning step/card.
	$(document).on('ttbm_tab_activated', function (e, tabsTarget) {
		var key = stepOfPane(tabsTarget);
		if (!key) {
			return;
		}
		var item = model[key].filter(function (m) { return m.id === tabsTarget; })[0];
		var needsOpen = item && item.acc && !openAcc[tabsTarget];
		if (key === current && !needsOpen) {
			return;
		}
		if (needsOpen) {
			openAcc[tabsTarget] = true;
		}
		showStep(key, {fromLegacy: true, scroll: false});
	});

	// Start from the tab the legacy code marked active (or step 1).
	var $activeBtn = $root.find('.tabLists [data-tabs-target].active').first();
	var startKey = $activeBtn.length ? stepOfPane($activeBtn.attr('data-tabs-target')) : '';
	if ($activeBtn.length && startKey) {
		var activeItem = model[startKey].filter(function (m) { return m.id === $activeBtn.attr('data-tabs-target'); })[0];
		if (activeItem && activeItem.acc) {
			openAcc[activeItem.id] = true;
		}
	}
	showStep(startKey || config[0].key, {scroll: false, fromLegacy: !!startKey});
	// The header save button is injected after this script runs; refresh the last-step label then.
	$(window).on('load', function () {
		updateChrome();
	});
}(jQuery));
