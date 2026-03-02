(function () {
	'use strict';

	const DAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
	const DAY_KEYS = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

	function cloneSlots(slots) {
		return Array.isArray(slots) ? slots.map((slot) => ({
			mep_ticket_time_name: slot && slot.mep_ticket_time_name ? String(slot.mep_ticket_time_name) : '',
			mep_ticket_time: slot && slot.mep_ticket_time ? String(slot.mep_ticket_time) : ''
		})) : [];
	}

	function normalizeTime(value) {
		const time = String(value || '').trim();
		return /^\d{2}:\d{2}$/.test(time) ? time : '';
	}

	function normalizeSlots(slots) {
		const seen = new Set();
		const normalized = [];
		(Array.isArray(slots) ? slots : []).forEach((slot) => {
			const time = normalizeTime(slot && slot.mep_ticket_time ? slot.mep_ticket_time : slot);
			if (!time || seen.has(time)) {
				return;
			}
			seen.add(time);
			normalized.push({
				mep_ticket_time_name: slot && slot.mep_ticket_time_name ? String(slot.mep_ticket_time_name) : time,
				mep_ticket_time: time
			});
		});
		normalized.sort((left, right) => left.mep_ticket_time.localeCompare(right.mep_ticket_time));
		return normalized;
	}

	function dateKey(dateValue) {
		const date = String(dateValue || '').trim();
		return /^\d{4}-\d{2}-\d{2}$/.test(date) ? date : '';
	}

	function toDate(dateValue) {
		const value = dateKey(dateValue);
		return value ? new Date(value + 'T00:00:00') : null;
	}

	function formatDate(date) {
		const year = date.getFullYear();
		const month = String(date.getMonth() + 1).padStart(2, '0');
		const day = String(date.getDate()).padStart(2, '0');
		return year + '-' + month + '-' + day;
	}

	function escapeHtml(value) {
		return String(value == null ? '' : value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');
	}

	function uniqDates(values) {
		return Array.from(new Set((Array.isArray(values) ? values : []).map(dateKey).filter(Boolean))).sort();
	}

	function byRole(root, role) {
		return root.querySelector('[data-role="' + role + '"]');
	}

	function allByRole(root, role) {
		return Array.from(root.querySelectorAll('[data-role="' + role + '"]'));
	}

	function buildState(rawState) {
		const baseSlots = rawState && rawState.baseSlots ? rawState.baseSlots : {};
		const dateRules = rawState && rawState.dateRules ? rawState.dateRules : {};
		const normalizedRules = {};

		Object.keys(dateRules).forEach((date) => {
			const cleanDate = dateKey(date);
			const cleanSlots = normalizeSlots(dateRules[date]);
			if (cleanDate && cleanSlots.length) {
				normalizedRules[cleanDate] = cleanSlots;
			}
		});

		return {
			tourId: rawState && rawState.tourId ? parseInt(rawState.tourId, 10) : 0,
			travelType: rawState && rawState.travelType ? String(rawState.travelType) : 'fixed',
			availableDates: uniqDates(rawState && rawState.availableDates ? rawState.availableDates : []),
			baseSlots: {
				default: normalizeSlots(baseSlots.default || []),
				sun: normalizeSlots(baseSlots.sun || []),
				mon: normalizeSlots(baseSlots.mon || []),
				tue: normalizeSlots(baseSlots.tue || []),
				wed: normalizeSlots(baseSlots.wed || []),
				thu: normalizeSlots(baseSlots.thu || []),
				fri: normalizeSlots(baseSlots.fri || []),
				sat: normalizeSlots(baseSlots.sat || [])
			},
			offDates: uniqDates(rawState && rawState.offDates ? rawState.offDates : []),
			dateRules: normalizedRules
		};
	}

	function copyPlannerState(state) {
		return {
			tourId: state.tourId,
			travelType: state.travelType,
			availableDates: state.availableDates.slice(),
			baseSlots: {
				default: cloneSlots(state.baseSlots.default),
				sun: cloneSlots(state.baseSlots.sun),
				mon: cloneSlots(state.baseSlots.mon),
				tue: cloneSlots(state.baseSlots.tue),
				wed: cloneSlots(state.baseSlots.wed),
				thu: cloneSlots(state.baseSlots.thu),
				fri: cloneSlots(state.baseSlots.fri),
				sat: cloneSlots(state.baseSlots.sat)
			},
			offDates: state.offDates.slice(),
			dateRules: Object.keys(state.dateRules).reduce((acc, date) => {
				acc[date] = cloneSlots(state.dateRules[date]);
				return acc;
			}, {})
		};
	}

	function initPlanner(root) {
		const rawStateEl = root.querySelector('.ttbm-easy-planner-state');
		if (!rawStateEl) {
			return;
		}

		let rawState = {};
		try {
			rawState = JSON.parse(rawStateEl.textContent || '{}');
		} catch (error) {
			rawState = {};
		}

		const initialState = buildState(rawState);
		const planner = {
			root,
			initialState,
			state: copyPlannerState(initialState),
			enabledInput: root.querySelector('.ttbm-easy-planner-enabled'),
			payloadInput: root.querySelector('.ttbm-easy-planner-payload'),
			single: {
				date: '',
				mode: 'add',
				cancel: false,
				removed: new Set(),
				added: []
			},
			bulk: {
				mode: 'add',
				selectedDays: new Set(),
				selectedTimes: new Set(),
				addedTimes: []
			}
		};

		initDatePickers(planner);
		bindTabs(planner);
		bindSingle(planner);
		bindBulk(planner);
		bindRules(planner);
		bindSaveBar(planner);
		renderAll(planner);
	}

	function initDatePickers(planner) {
		if (typeof window.jQuery === 'undefined' || !window.jQuery.fn || !window.jQuery.fn.datepicker) {
			return;
		}

		const $ = window.jQuery;
		const availableDates = planner.state.availableDates.slice();
		const availableSet = new Set(availableDates);
		const firstDate = availableDates.length ? availableDates[0] : null;
		const lastDate = availableDates.length ? availableDates[availableDates.length - 1] : null;
		const displayFormat = typeof window.ttbm_date_format === 'string' && window.ttbm_date_format ? window.ttbm_date_format : 'yy-mm-dd';

		function bindPicker(displayRole, hiddenRole, onSelect) {
			const displayInput = byRole(planner.root, displayRole);
			const hiddenInput = byRole(planner.root, hiddenRole);
			if (!displayInput || !hiddenInput) {
				return;
			}

			$(displayInput).datepicker({
				dateFormat: displayFormat,
				altField: hiddenInput,
				altFormat: 'yy-mm-dd',
				minDate: firstDate ? new Date(firstDate + 'T00:00:00') : null,
				maxDate: lastDate ? new Date(lastDate + 'T00:00:00') : null,
				changeMonth: true,
				changeYear: true,
				beforeShowDay: function (date) {
					const iso = formatDate(date);
					return [availableSet.has(iso), availableSet.has(iso) ? 'ttbm-easy-planner__datepicker-available' : '', availableSet.has(iso) ? 'Available' : 'Unavailable'];
				},
				onSelect: function () {
					onSelect(hiddenInput.value);
				}
			});
		}

		bindPicker('single-date-display', 'single-date', function (value) {
			openSingleEditor(planner, value);
		});
		bindPicker('bulk-start-display', 'bulk-start', function () {
			renderBulk(planner);
		});
		bindPicker('bulk-end-display', 'bulk-end', function () {
			renderBulk(planner);
		});
	}

	function bindTabs(planner) {
		const tabs = Array.from(planner.root.querySelectorAll('[data-planner-tab]'));
		const panes = Array.from(planner.root.querySelectorAll('[data-planner-pane]'));
		tabs.forEach((tab) => {
			tab.addEventListener('click', function () {
				const target = tab.getAttribute('data-planner-tab');
				tabs.forEach((item) => item.classList.toggle('is-active', item === tab));
				panes.forEach((pane) => pane.classList.toggle('is-active', pane.getAttribute('data-planner-pane') === target));
			});
		});
	}

	function bindSingle(planner) {
		const singleDateInput = byRole(planner.root, 'single-date');
		const cancelToggle = byRole(planner.root, 'single-cancel-toggle');
		const addInput = byRole(planner.root, 'single-new-time');
		const addButton = byRole(planner.root, 'single-add-time');
		const saveButton = byRole(planner.root, 'single-save');
		const resetButton = byRole(planner.root, 'single-reset');
		const newPills = byRole(planner.root, 'single-new-pills');
		const modeButtons = allByRole(planner.root, 'single-mode');
		const pillRow = byRole(planner.root, 'single-pills');

		if (singleDateInput) {
			singleDateInput.addEventListener('change', function () {
				openSingleEditor(planner, singleDateInput.value);
			});
		}

		if (cancelToggle) {
			cancelToggle.addEventListener('click', function () {
				planner.single.cancel = !planner.single.cancel;
				renderSingle(planner);
			});
		}

		modeButtons.forEach((button) => {
			button.addEventListener('click', function () {
				planner.single.mode = button.getAttribute('data-mode') || 'add';
				renderSingleModes(planner);
			});
		});

		if (addButton && addInput) {
			addButton.addEventListener('click', function () {
				const time = normalizeTime(addInput.value);
				if (!time) {
					window.alert('Format: HH:MM');
					return;
				}
				const existingTimes = new Set(getFinalSlotsForSingle(planner).map((slot) => slot.mep_ticket_time));
				if (existingTimes.has(time)) {
					addInput.value = '';
					return;
				}
				planner.single.added = normalizeSlots(planner.single.added.concat([{ mep_ticket_time: time, mep_ticket_time_name: time }]));
				addInput.value = '';
				renderSingle(planner);
			});
		}

		if (newPills) {
			newPills.addEventListener('click', function (event) {
				const target = event.target.closest('[data-remove-time]');
				if (!target) {
					return;
				}
				const time = target.getAttribute('data-remove-time');
				planner.single.added = planner.single.added.filter((slot) => slot.mep_ticket_time !== time);
				renderSingle(planner);
			});
		}

		if (pillRow) {
			pillRow.addEventListener('click', function (event) {
				const target = event.target.closest('[data-base-time]');
				if (!target || planner.single.cancel) {
					return;
				}
				const time = target.getAttribute('data-base-time');
				if (!time) {
					return;
				}
				if (planner.single.removed.has(time)) {
					planner.single.removed.delete(time);
				} else {
					planner.single.removed.add(time);
				}
				renderSingle(planner);
			});
		}

		if (saveButton) {
			saveButton.addEventListener('click', function () {
				if (!planner.single.date) {
					window.alert('Please pick a date.');
					return;
				}
				const finalSlots = getFinalSlotsForSingle(planner);
				applyDateChange(planner, planner.single.date, finalSlots, planner.single.cancel || finalSlots.length === 0);
				openSingleEditor(planner, planner.single.date);
				renderAll(planner);
			});
		}

		if (resetButton) {
			resetButton.addEventListener('click', function () {
				openSingleEditor(planner, planner.single.date);
			});
		}
	}

	function bindBulk(planner) {
		const weekdayRow = byRole(planner.root, 'bulk-weekdays');
		const modeButtons = allByRole(planner.root, 'bulk-mode');
		const addButton = byRole(planner.root, 'bulk-add-time');
		const addInput = byRole(planner.root, 'bulk-new-time');
		const newPills = byRole(planner.root, 'bulk-new-pills');
		const bulkPills = byRole(planner.root, 'bulk-pills');
		const saveButton = byRole(planner.root, 'bulk-save');
		const resetButton = byRole(planner.root, 'bulk-reset');
		const startInput = byRole(planner.root, 'bulk-start');
		const endInput = byRole(planner.root, 'bulk-end');
		const startDisplayInput = byRole(planner.root, 'bulk-start-display');
		const endDisplayInput = byRole(planner.root, 'bulk-end-display');

		if (weekdayRow) {
			weekdayRow.addEventListener('click', function (event) {
				const target = event.target.closest('[data-day]');
				if (!target) {
					return;
				}
				const day = parseInt(target.getAttribute('data-day') || '', 10);
				if (Number.isNaN(day)) {
					return;
				}
				if (planner.bulk.selectedDays.has(day)) {
					planner.bulk.selectedDays.delete(day);
				} else {
					planner.bulk.selectedDays.add(day);
				}
				renderBulk(planner);
			});
		}

		modeButtons.forEach((button) => {
			button.addEventListener('click', function () {
				planner.bulk.mode = button.getAttribute('data-mode') || 'add';
				renderBulk(planner);
			});
		});

		if (bulkPills) {
			bulkPills.addEventListener('click', function (event) {
				const target = event.target.closest('[data-bulk-time]');
				if (!target || planner.bulk.mode === 'cancel') {
					return;
				}
				const time = target.getAttribute('data-bulk-time');
				if (!time) {
					return;
				}
				if (planner.bulk.selectedTimes.has(time)) {
					planner.bulk.selectedTimes.delete(time);
				} else {
					planner.bulk.selectedTimes.add(time);
				}
				renderBulk(planner);
			});
		}

		if (addButton && addInput) {
			addButton.addEventListener('click', function () {
				const time = normalizeTime(addInput.value);
				if (!time) {
					window.alert('Format: HH:MM');
					return;
				}
				if (!planner.bulk.addedTimes.includes(time)) {
					planner.bulk.addedTimes.push(time);
					planner.bulk.addedTimes.sort();
				}
				addInput.value = '';
				renderBulk(planner);
			});
		}

		if (newPills) {
			newPills.addEventListener('click', function (event) {
				const target = event.target.closest('[data-remove-bulk-time]');
				if (!target) {
					return;
				}
				const time = target.getAttribute('data-remove-bulk-time');
				planner.bulk.addedTimes = planner.bulk.addedTimes.filter((item) => item !== time);
				renderBulk(planner);
			});
		}

		[startInput, endInput].forEach((input) => {
			if (!input) {
				return;
			}
			input.addEventListener('change', function () {
				renderBulk(planner);
			});
		});

		if (saveButton) {
			saveButton.addEventListener('click', function () {
				const dates = getBulkDates(planner);
				if (!dates.length) {
					window.alert('Please set a valid date range.');
					return;
				}

				dates.forEach((date) => {
					const currentSlots = getResolvedSlots(planner, date);
					const finalSlots = applyBulkOperation(planner, currentSlots);
					applyDateChange(planner, date, finalSlots, planner.bulk.mode === 'cancel' || finalSlots.length === 0);
				});
				renderAll(planner);
			});
		}

		if (resetButton) {
			resetButton.addEventListener('click', function () {
				planner.bulk.mode = 'add';
				planner.bulk.selectedDays = new Set();
				planner.bulk.selectedTimes = new Set();
				planner.bulk.addedTimes = [];
				if (startInput) {
					startInput.value = '';
				}
				if (endInput) {
					endInput.value = '';
				}
				if (startDisplayInput) {
					startDisplayInput.value = '';
				}
				if (endDisplayInput) {
					endDisplayInput.value = '';
				}
				renderBulk(planner);
			});
		}
	}

	function bindRules(planner) {
		const rulesList = byRole(planner.root, 'rules-list');
		if (!rulesList) {
			return;
		}
		rulesList.addEventListener('click', function (event) {
			const removeButton = event.target.closest('[data-rule-date]');
			if (!removeButton) {
				return;
			}
			const date = removeButton.getAttribute('data-rule-date');
			if (!date) {
				return;
			}
			planner.state.offDates = planner.state.offDates.filter((item) => item !== date);
			delete planner.state.dateRules[date];
			renderAll(planner);
		});
	}

	function bindSaveBar(planner) {
		const clearButton = byRole(planner.root, 'clear-all');
		const applyButton = byRole(planner.root, 'apply-all');

		if (clearButton) {
			clearButton.addEventListener('click', function () {
				planner.state = copyPlannerState(planner.initialState);
				openSingleEditor(planner, planner.single.date || '');
				renderAll(planner);
			});
		}

		if (applyButton) {
			applyButton.addEventListener('click', function () {
				const pending = getPendingChangeCount(planner);
				syncPayload(planner);
				window.alert(pending > 0 ? 'Planner changes are ready. Click Update or Publish to save this tour.' : 'No pending planner changes.');
			});
		}
	}

	function renderAll(planner) {
		renderSingle(planner);
		renderBulk(planner);
		renderRules(planner);
		syncPayload(planner);
	}

	function openSingleEditor(planner, selectedDate) {
		const date = dateKey(selectedDate);
		planner.single.date = date;
		planner.single.mode = 'add';
		planner.single.cancel = false;
		planner.single.removed = new Set();
		planner.single.added = [];

		if (!date) {
			renderSingle(planner);
			return;
		}

		const baseSlots = getBaseSlotsForDate(planner, date);
		const currentSlots = getResolvedSlots(planner, date);
		const currentMap = new Set(currentSlots.map((slot) => slot.mep_ticket_time));
		const baseMap = new Set(baseSlots.map((slot) => slot.mep_ticket_time));

		planner.single.cancel = planner.state.offDates.includes(date);
		baseSlots.forEach((slot) => {
			if (!currentMap.has(slot.mep_ticket_time)) {
				planner.single.removed.add(slot.mep_ticket_time);
			}
		});
		planner.single.added = currentSlots.filter((slot) => !baseMap.has(slot.mep_ticket_time));
		renderSingle(planner);
	}

	function renderSingle(planner) {
		const panel = byRole(planner.root, 'single-panel');
		const notice = byRole(planner.root, 'single-notice');
		const slotPanel = byRole(planner.root, 'single-slot-panel');
		const preview = byRole(planner.root, 'single-preview');
		const pills = byRole(planner.root, 'single-pills');
		const newPills = byRole(planner.root, 'single-new-pills');
		const toggle = byRole(planner.root, 'single-cancel-toggle');

		renderSingleModes(planner);

		if (!panel || !notice || !slotPanel || !preview || !pills || !newPills || !toggle) {
			return;
		}

		if (!planner.single.date) {
			panel.hidden = true;
			return;
		}

		panel.hidden = false;
		const date = toDate(planner.single.date);
		const weekdayIndex = date ? date.getDay() : 0;
		const baseSlots = getBaseSlotsForDate(planner, planner.single.date);
		notice.textContent = DAY_NAMES[weekdayIndex] + ' - Regular schedule loaded';
		toggle.classList.toggle('is-on', planner.single.cancel);
		toggle.setAttribute('aria-pressed', planner.single.cancel ? 'true' : 'false');
		slotPanel.style.opacity = planner.single.cancel ? '0.4' : '1';
		slotPanel.style.pointerEvents = planner.single.cancel ? 'none' : 'auto';

		pills.innerHTML = baseSlots.map((slot) => {
			const removed = planner.single.removed.has(slot.mep_ticket_time);
			return '<button type="button" class="ttbm-easy-planner__pill ' + (removed ? 'is-remove' : '') + '" data-base-time="' + escapeHtml(slot.mep_ticket_time) + '">' +
				escapeHtml(slot.mep_ticket_time) +
				'</button>';
		}).join('');

		newPills.innerHTML = planner.single.added.map((slot) => {
			return '<span class="ttbm-easy-planner__pill is-add">' +
				escapeHtml(slot.mep_ticket_time) +
				' <button type="button" class="ttbm-easy-planner__pill-remove" data-remove-time="' + escapeHtml(slot.mep_ticket_time) + '">x</button>' +
				'</span>';
		}).join('');

		const finalSlots = getFinalSlotsForSingle(planner);
		if (planner.single.cancel || !finalSlots.length) {
			preview.innerHTML = '<div class="ttbm-easy-planner__preview-item"><div class="ttbm-easy-planner__preview-date">' + escapeHtml(planner.single.date) + '</div><div class="ttbm-easy-planner__preview-times"><span class="ttbm-easy-planner__badge is-cancel">Cancelled</span></div></div>';
			return;
		}

		preview.innerHTML = '<div class="ttbm-easy-planner__preview-item"><div class="ttbm-easy-planner__preview-date">' + escapeHtml(planner.single.date) + '</div><div class="ttbm-easy-planner__preview-times">' +
			finalSlots.map((slot) => '<span class="ttbm-easy-planner__badge is-keep">' + escapeHtml(slot.mep_ticket_time) + '</span>').join('') +
			'</div></div>';
	}

	function renderSingleModes(planner) {
		allByRole(planner.root, 'single-mode').forEach((button) => {
			const mode = button.getAttribute('data-mode');
			button.className = 'ttbm-easy-planner__mode' + (mode === planner.single.mode ? ' is-active-' + mode : '');
		});
	}

	function getFinalSlotsForSingle(planner) {
		if (!planner.single.date || planner.single.cancel) {
			return [];
		}
		const baseSlots = getBaseSlotsForDate(planner, planner.single.date).filter((slot) => !planner.single.removed.has(slot.mep_ticket_time));
		return normalizeSlots(baseSlots.concat(planner.single.added));
	}

	function renderBulk(planner) {
		const modeButtons = allByRole(planner.root, 'bulk-mode');
		modeButtons.forEach((button) => {
			const mode = button.getAttribute('data-mode');
			button.className = 'ttbm-easy-planner__mode' + (mode === planner.bulk.mode ? ' is-active-' + mode : '');
		});

		const weekdayButtons = Array.from(planner.root.querySelectorAll('[data-role="bulk-weekdays"] [data-day]'));
		weekdayButtons.forEach((button) => {
			const day = parseInt(button.getAttribute('data-day') || '', 10);
			button.classList.toggle('is-selected', planner.bulk.selectedDays.has(day));
		});

		const bulkSlotPanel = byRole(planner.root, 'bulk-slot-panel');
		if (bulkSlotPanel) {
			bulkSlotPanel.style.display = planner.bulk.mode === 'cancel' ? 'none' : '';
		}

		const unionTimes = getKnownTimes(planner);
		const bulkPills = byRole(planner.root, 'bulk-pills');
		if (bulkPills) {
			bulkPills.innerHTML = unionTimes.map((time) => {
				const selected = planner.bulk.selectedTimes.has(time);
				const className = planner.bulk.mode === 'remove' ? ' is-remove' : ' is-add';
				return '<button type="button" class="ttbm-easy-planner__pill' + (selected ? className : '') + '" data-bulk-time="' + escapeHtml(time) + '">' + escapeHtml(time) + '</button>';
			}).join('');
		}

		const bulkNewPills = byRole(planner.root, 'bulk-new-pills');
		if (bulkNewPills) {
			bulkNewPills.innerHTML = planner.bulk.addedTimes.map((time) => {
				return '<span class="ttbm-easy-planner__pill is-add">' +
					escapeHtml(time) +
					' <button type="button" class="ttbm-easy-planner__pill-remove" data-remove-bulk-time="' + escapeHtml(time) + '">x</button>' +
					'</span>';
			}).join('');
		}

		renderBulkPreview(planner);
	}

	function getBulkDates(planner) {
		const startInput = byRole(planner.root, 'bulk-start');
		const endInput = byRole(planner.root, 'bulk-end');
		const start = dateKey(startInput ? startInput.value : '');
		const end = dateKey(endInput ? endInput.value : '');
		if (!start || !end || start > end) {
			return [];
		}

		const dates = [];
		const cursor = new Date(start + 'T00:00:00');
		const endDate = new Date(end + 'T00:00:00');
		while (cursor <= endDate) {
			const weekday = cursor.getDay();
			if (!planner.bulk.selectedDays.size || planner.bulk.selectedDays.has(weekday)) {
				dates.push(formatDate(cursor));
			}
			cursor.setDate(cursor.getDate() + 1);
		}
		return dates;
	}

	function applyBulkOperation(planner, currentSlots) {
		if (planner.bulk.mode === 'cancel') {
			return [];
		}

		const currentMap = new Map(currentSlots.map((slot) => [slot.mep_ticket_time, slot]));
		const customSlots = planner.bulk.addedTimes.map((time) => ({ mep_ticket_time_name: time, mep_ticket_time: time }));
		const selectedTimes = Array.from(planner.bulk.selectedTimes);

		if (planner.bulk.mode === 'add') {
			selectedTimes.forEach((time) => {
				if (!currentMap.has(time)) {
					currentMap.set(time, { mep_ticket_time_name: time, mep_ticket_time: time });
				}
			});
			customSlots.forEach((slot) => currentMap.set(slot.mep_ticket_time, slot));
			return normalizeSlots(Array.from(currentMap.values()));
		}

		selectedTimes.concat(planner.bulk.addedTimes).forEach((time) => currentMap.delete(time));
		return normalizeSlots(Array.from(currentMap.values()));
	}

	function renderBulkPreview(planner) {
		const preview = byRole(planner.root, 'bulk-preview');
		if (!preview) {
			return;
		}

		const dates = getBulkDates(planner);
		if (!dates.length) {
			preview.innerHTML = '<span class="ttbm-easy-planner__rule-meta">Set date range and weekdays to see preview.</span>';
			return;
		}

		const sampleDates = dates.slice(0, 8);
		preview.innerHTML = sampleDates.map((date) => {
			const currentSlots = getResolvedSlots(planner, date);
			const finalSlots = applyBulkOperation(planner, currentSlots);
			if (planner.bulk.mode === 'cancel' || !finalSlots.length) {
				return '<div class="ttbm-easy-planner__preview-item"><div class="ttbm-easy-planner__preview-date">' + escapeHtml(date) + '</div><div class="ttbm-easy-planner__preview-times"><span class="ttbm-easy-planner__badge is-cancel">Cancelled</span></div></div>';
			}

			const currentMap = new Set(currentSlots.map((slot) => slot.mep_ticket_time));
			const finalMap = new Set(finalSlots.map((slot) => slot.mep_ticket_time));
			const badges = [];

			finalSlots.forEach((slot) => {
				const badgeClass = currentMap.has(slot.mep_ticket_time) ? 'is-keep' : 'is-add';
				badges.push('<span class="ttbm-easy-planner__badge ' + badgeClass + '">' + escapeHtml(slot.mep_ticket_time) + '</span>');
			});
			currentSlots.forEach((slot) => {
				if (!finalMap.has(slot.mep_ticket_time)) {
					badges.push('<span class="ttbm-easy-planner__badge is-remove">' + escapeHtml(slot.mep_ticket_time) + '</span>');
				}
			});

			return '<div class="ttbm-easy-planner__preview-item"><div class="ttbm-easy-planner__preview-date">' + escapeHtml(date) + '</div><div class="ttbm-easy-planner__preview-times">' + badges.join('') + '</div></div>';
		}).join('') + (dates.length > 8 ? '<div class="ttbm-easy-planner__rule-meta">...and more dates</div>' : '');
	}

	function renderRules(planner) {
		const rulesList = byRole(planner.root, 'rules-list');
		const emptyState = byRole(planner.root, 'rules-empty');
		if (!rulesList || !emptyState) {
			return;
		}

		const items = [];
		const allDates = uniqDates(planner.state.offDates.concat(Object.keys(planner.state.dateRules)));
		allDates.forEach((date) => {
			const isOffDate = planner.state.offDates.includes(date);
			const baseSlots = getBaseSlotsForDate(planner, date);
			const currentSlots = isOffDate ? [] : getResolvedSlots(planner, date);
			const baseMap = new Set(baseSlots.map((slot) => slot.mep_ticket_time));
			const currentMap = new Set(currentSlots.map((slot) => slot.mep_ticket_time));
			const badges = [];

			if (isOffDate) {
				badges.push('<span class="ttbm-easy-planner__badge is-cancel">Cancelled</span>');
			} else {
				currentSlots.forEach((slot) => {
					badges.push('<span class="ttbm-easy-planner__badge ' + (baseMap.has(slot.mep_ticket_time) ? 'is-keep' : 'is-add') + '">' + escapeHtml(slot.mep_ticket_time) + '</span>');
				});
				baseSlots.forEach((slot) => {
					if (!currentMap.has(slot.mep_ticket_time)) {
						badges.push('<span class="ttbm-easy-planner__badge is-remove">' + escapeHtml(slot.mep_ticket_time) + '</span>');
					}
				});
			}

			items.push(
				'<div class="ttbm-easy-planner__rule">' +
					'<div class="ttbm-easy-planner__rule-icon ' + (isOffDate ? 'is-cancel' : 'is-add') + '">' + (isOffDate ? '!' : '+') + '</div>' +
					'<div class="ttbm-easy-planner__rule-body">' +
						'<div class="ttbm-easy-planner__rule-title">' + (isOffDate ? 'Full Day Cancel' : 'Date Schedule Override') + '</div>' +
						'<div class="ttbm-easy-planner__rule-meta">' + escapeHtml(date) + '</div>' +
						'<div class="ttbm-easy-planner__rule-times">' + badges.join('') + '</div>' +
					'</div>' +
					'<button type="button" class="ttbm-easy-planner__button is-danger is-small" data-rule-date="' + escapeHtml(date) + '">Remove</button>' +
				'</div>'
			);
		});

		rulesList.innerHTML = items.join('');
		emptyState.style.display = items.length ? 'none' : '';
	}

	function getBaseSlotsForDate(planner, dateValue) {
		const date = toDate(dateValue);
		if (!date) {
			return [];
		}
		const dayKey = DAY_KEYS[date.getDay()];
		const daySlots = planner.state.baseSlots[dayKey] || [];
		return cloneSlots(daySlots.length ? daySlots : planner.state.baseSlots.default || []);
	}

	function getResolvedSlots(planner, date) {
		const cleanDate = dateKey(date);
		if (!cleanDate || planner.state.offDates.includes(cleanDate)) {
			return [];
		}
		if (planner.state.dateRules[cleanDate]) {
			return cloneSlots(planner.state.dateRules[cleanDate]);
		}
		return getBaseSlotsForDate(planner, cleanDate);
	}

	function applyDateChange(planner, date, finalSlots, cancel) {
		const cleanDate = dateKey(date);
		if (!cleanDate) {
			return;
		}

		if (cancel) {
			if (!planner.state.offDates.includes(cleanDate)) {
				planner.state.offDates.push(cleanDate);
				planner.state.offDates.sort();
			}
			delete planner.state.dateRules[cleanDate];
			return;
		}

		planner.state.offDates = planner.state.offDates.filter((item) => item !== cleanDate);
		planner.state.dateRules[cleanDate] = normalizeSlots(finalSlots);
	}

	function getKnownTimes(planner) {
		const known = new Set();
		Object.keys(planner.state.baseSlots).forEach((key) => {
			(planner.state.baseSlots[key] || []).forEach((slot) => {
				if (slot && slot.mep_ticket_time) {
					known.add(slot.mep_ticket_time);
				}
			});
		});
		Object.keys(planner.state.dateRules).forEach((date) => {
			(planner.state.dateRules[date] || []).forEach((slot) => {
				if (slot && slot.mep_ticket_time) {
					known.add(slot.mep_ticket_time);
				}
			});
		});
		return Array.from(known).sort();
	}

	function serializeState(state) {
		const dateRules = {};
		Object.keys(state.dateRules).sort().forEach((date) => {
			dateRules[date] = normalizeSlots(state.dateRules[date]);
		});
		return JSON.stringify({
			offDates: uniqDates(state.offDates),
			dateRules: dateRules
		});
	}

	function getPendingChangeCount(planner) {
		const dates = new Set();
		uniqDates(planner.initialState.offDates.concat(Object.keys(planner.initialState.dateRules))).forEach((date) => dates.add(date));
		uniqDates(planner.state.offDates.concat(Object.keys(planner.state.dateRules))).forEach((date) => dates.add(date));

		let changes = 0;
		dates.forEach((date) => {
			const initialOff = planner.initialState.offDates.includes(date);
			const currentOff = planner.state.offDates.includes(date);
			const initialRule = JSON.stringify(normalizeSlots(planner.initialState.dateRules[date] || []));
			const currentRule = JSON.stringify(normalizeSlots(planner.state.dateRules[date] || []));
			if (initialOff !== currentOff || initialRule !== currentRule) {
				changes += 1;
			}
		});
		return changes;
	}

	function syncPayload(planner) {
		const pending = getPendingChangeCount(planner);
		if (planner.payloadInput) {
			planner.payloadInput.value = serializeState(planner.state);
		}
		if (planner.enabledInput) {
			planner.enabledInput.value = pending > 0 ? '1' : '0';
		}
		const count = byRole(planner.root, 'pending-count');
		if (count) {
			count.textContent = String(pending);
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.ttbm-easy-planner').forEach(initPlanner);
	});
})();
