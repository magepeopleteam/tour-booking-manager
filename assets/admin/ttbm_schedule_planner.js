(function () {
    function getStrings() {
        return window.TTBMSchedulePlannerStrings || {};
    }

    function text(key, fallback) {
        var strings = getStrings();
        return Object.prototype.hasOwnProperty.call(strings, key) ? strings[key] : fallback;
    }

    function textWithTokens(key, fallback, replacements) {
        var message = text(key, fallback);
        Object.keys(replacements || {}).forEach(function (token) {
            message = message.replace(new RegExp('\\{' + token + '\\}', 'g'), String(replacements[token]));
        });
        return message;
    }

    var BULK_PREVIEW_PAGE_SIZE = 10;

    function parseConfig(root) {
        var node = root.querySelector('[data-planner-config]');
        if (!node) {
            return null;
        }
        try {
            return JSON.parse(node.textContent || '{}');
        } catch (error) {
            return null;
        }
    }

    function normalizeTime(value) {
        if (!value) {
            return '';
        }
        var cleaned = String(value).trim();
        if (/^\d{1,2}:\d{2}$/.test(cleaned)) {
            var parts = cleaned.split(':');
            return parts[0].padStart(2, '0') + ':' + parts[1];
        }
        return '';
    }

    function uniqueTimes(times) {
        return Array.from(new Set((times || []).map(normalizeTime).filter(Boolean))).sort();
    }

    function normalizeRule(rule) {
        if (!rule || typeof rule !== 'object') {
            return null;
        }
        if (rule.type === 'single') {
            return {
                id: String(rule.id || Date.now()),
                type: 'single',
                date: rule.date || '',
                full_cancel: !!(rule.full_cancel || rule.fullCancel),
                removed: uniqueTimes(rule.removed || []),
                added: uniqueTimes(rule.added || [])
            };
        }
        if (rule.type === 'bulk') {
            var mode = rule.bulk_mode || rule.bulkMode || 'add';
            if (['add', 'remove', 'cancel'].indexOf(mode) === -1) {
                mode = 'add';
            }
            return {
                id: String(rule.id || Date.now()),
                type: 'bulk',
                start: rule.start || '',
                end: rule.end || '',
                days: sortNumbers(Array.isArray(rule.days) ? rule.days : []),
                dates: uniqueDates(rule.dates || rule.selectedDates || []),
                bulk_mode: mode,
                selected_times: uniqueTimes(rule.selected_times || rule.selectedTimes || []),
                added_times: uniqueTimes(rule.added_times || rule.addedTimes || [])
            };
        }
        return null;
    }

    function normalizeRules(rules) {
        return (Array.isArray(rules) ? rules : []).map(normalizeRule).filter(Boolean);
    }

    function sortNumbers(items) {
        return Array.from(new Set(items || [])).sort(function (a, b) {
            return a - b;
        });
    }

    function uniqueDates(dates) {
        return Array.from(new Set((dates || []).map(function (date) {
            return String(date || '').trim();
        }).filter(function (date) {
            return /^\d{4}-\d{2}-\d{2}$/.test(date);
        }))).sort();
    }

    function getDayIndex(date) {
        if (!date) {
            return -1;
        }
        var current = new Date(date + 'T00:00:00');
        if (Number.isNaN(current.getTime())) {
            return -1;
        }
        return current.getDay();
    }

    function formatDay(date, labels) {
        if (!date) {
            return '';
        }
        var current = new Date(date + 'T00:00:00');
        if (Number.isNaN(current.getTime())) {
            return '';
        }
        return labels[current.getDay()] || '';
    }

    function formatDisplayDate(date) {
        if (!date) {
            return '';
        }
        var current = new Date(date + 'T00:00:00');
        if (Number.isNaN(current.getTime()) || !window.jQuery || !jQuery.datepicker) {
            return date;
        }
        return jQuery.datepicker.formatDate(window.ttbm_date_format || 'yy-mm-dd', current);
    }

    function renderPreviewDate(date, labels) {
        if (!date) {
            return '';
        }
        var displayDate = formatDisplayDate(date) || date;
        var dayName = formatDay(date, labels || []);
        if (!dayName) {
            return displayDate;
        }
        return '<span>' + displayDate + '</span><span class="ttbm-schedule-preview-day">' + dayName + '</span>';
    }

    function renderSelectableDate(date, labels) {
        var displayDate = formatDisplayDate(date) || date;
        var dayName = formatDay(date, labels || []);
        return '<span>' + displayDate + '</span>' + (dayName ? '<small>' + dayName + '</small>' : '');
    }

    function formatSpecificDateMeta(dates) {
        var normalizedDates = uniqueDates(dates);
        if (!normalizedDates.length) {
            return '';
        }
        var preview = normalizedDates.slice(0, 3).map(function (date) {
            return formatDisplayDate(date) || date;
        }).join(', ');
        if (normalizedDates.length > 3) {
            preview += ' +' + (normalizedDates.length - 3);
        }
        return textWithTokens('specificDatesSummary', '{count} specific dates', {
            count: normalizedDates.length
        }) + ' - ' + preview;
    }

    function syncPlannerDateState(state, stateKey, isoDate) {
        if (!isoDate) {
            return false;
        }
        if (stateKey === 'single-date') {
            state.single.date = isoDate;
            state.single.removed = [];
            state.single.added = [];
            return true;
        }
        if (stateKey === 'bulk-start') {
            state.bulk.start = isoDate;
            state.bulk.dates = [];
            state.bulk.manualDates = false;
            state.bulk.previewLimit = BULK_PREVIEW_PAGE_SIZE;
            return true;
        }
        if (stateKey === 'bulk-end') {
            state.bulk.end = isoDate;
            state.bulk.dates = [];
            state.bulk.manualDates = false;
            state.bulk.previewLimit = BULK_PREVIEW_PAGE_SIZE;
            return true;
        }
        return false;
    }

    function initPlannerDatePickers(root, state, rerenderFn) {
        if (!window.jQuery || !jQuery.datepicker) {
            return;
        }
        var availableDates = Array.isArray(state.availableDates) ? state.availableDates : [];
        var availableLookup = {};
        availableDates.forEach(function (date) {
            availableLookup[date] = true;
        });
        root.querySelectorAll('[data-planner-hidden-date]').forEach(function (hiddenInput) {
            var stateKey = hiddenInput.getAttribute('data-state-key');
            var visibleInput = hiddenInput.parentElement ? hiddenInput.parentElement.querySelector('.date_type') : null;
            if (!visibleInput) {
                return;
            }
            var $visible = jQuery(visibleInput);
            if ($visible.hasClass('hasDatepicker')) {
                $visible.datepicker('destroy');
            }
            $visible.datepicker({
                dateFormat: window.ttbm_date_format || 'yy-mm-dd',
                autoSize: true,
                changeMonth: true,
                changeYear: true,
                beforeShowDay: function (dateObj) {
                    if (!availableDates.length) {
                        return [true, '', ''];
                    }
                    var iso = dateObj.getFullYear() + '-' + String(dateObj.getMonth() + 1).padStart(2, '0') + '-' + String(dateObj.getDate()).padStart(2, '0');
                    return [!!availableLookup[iso], availableLookup[iso] ? 'ui-state-highlight' : '', availableLookup[iso] ? 'Tour date available' : 'Unavailable'];
                },
                onSelect: function (dateText, inst) {
                    var iso = inst.selectedYear + '-' + String(parseInt(inst.selectedMonth, 10) + 1).padStart(2, '0') + '-' + String(parseInt(inst.selectedDay, 10)).padStart(2, '0');
                    hiddenInput.value = iso;
                    if (syncPlannerDateState(state, stateKey, iso)) {
                        rerenderFn(root, state);
                    }
                    jQuery(this).datepicker('hide');
                },
                onClose: function () {
                    jQuery(this).blur();
                }
            });
        });
    }

    function matchesBulkRule(rule, date) {
        if (!rule || rule.type !== 'bulk' || !date || !rule.start || !rule.end) {
            return false;
        }
        var selectedDates = uniqueDates(rule.dates || []);
        if (selectedDates.length) {
            return selectedDates.indexOf(date) !== -1;
        }
        if (date < rule.start || date > rule.end) {
            return false;
        }
        var current = new Date(date + 'T00:00:00');
        if (Number.isNaN(current.getTime())) {
            return false;
        }
        if (!Array.isArray(rule.days) || !rule.days.length) {
            return true;
        }
        return rule.days.indexOf(current.getDay()) !== -1;
    }

    function applyRules(baseTimes, rules, date) {
        var times = uniqueTimes(baseTimes);
        var lookup = {};
        times.forEach(function (time) {
            lookup[time] = true;
        });
        var matchedRules = rules.filter(function (rule) {
            if (!rule || !date) {
                return false;
            }
            if (rule.type === 'single') {
                return rule.date === date;
            }
            return matchesBulkRule(rule, date);
        });
        if (matchedRules.some(function (rule) {
            return (rule.type === 'single' && rule.full_cancel) || (rule.type === 'bulk' && rule.bulk_mode === 'cancel');
        })) {
            return { cancelled: true, times: [] };
        }
        matchedRules.forEach(function (rule) {
            if (rule.type === 'single') {
                (rule.removed || []).forEach(function (time) {
                    delete lookup[time];
                });
                (rule.added || []).forEach(function (time) {
                    lookup[time] = true;
                });
                return;
            }
            if (rule.bulk_mode === 'remove') {
                (rule.selected_times || []).forEach(function (time) {
                    delete lookup[time];
                });
                return;
            }
            if (rule.bulk_mode === 'add') {
                (rule.selected_times || []).concat(rule.added_times || []).forEach(function (time) {
                    lookup[time] = true;
                });
            }
        });
        return {
            cancelled: false,
            times: Object.keys(lookup).sort()
        };
    }

    function renderPreview(date, outcome, labels) {
        if (!date) {
            return '<div class="ttbm-schedule-empty">' + text('noDatePreview', 'Select a date to preview the final schedule.') + '</div>';
        }
        var previewDate = renderPreviewDate(date, labels);
        if (outcome.cancelled) {
            return '<div class="ttbm-schedule-preview-item"><div class="ttbm-schedule-preview-date">' + previewDate + '</div><div><span class="ttbm-schedule-badge cancel">' + text('fullDayCancelled', 'Full day cancelled') + '</span></div></div>';
        }
        if (!outcome.times.length) {
            return '<div class="ttbm-schedule-preview-item"><div class="ttbm-schedule-preview-date">' + previewDate + '</div><div><span class="ttbm-schedule-badge remove">' + text('noActiveSlots', 'No active slots') + '</span></div></div>';
        }
        return '<div class="ttbm-schedule-preview-item"><div class="ttbm-schedule-preview-date">' + previewDate + '</div><div class="ttbm-schedule-preview-times">' + outcome.times.map(function (time) {
            return '<span class="ttbm-schedule-badge keep">' + time + '</span>';
        }).join('') + '</div></div>';
    }

    function buildSingleRule(singleState) {
        if (!singleState.date) {
            return null;
        }
        return {
            id: String(Date.now()),
            type: 'single',
            date: singleState.date,
            full_cancel: !!singleState.fullCancel,
            removed: uniqueTimes(singleState.removed),
            added: uniqueTimes(singleState.added)
        };
    }

    function buildBulkRule(bulkState, effectiveDays) {
        if (!bulkState.start || !bulkState.end) {
            return null;
        }
        var selectedDays = sortNumbers(Array.isArray(effectiveDays) ? effectiveDays : bulkState.days);
        var selectedDates = uniqueDates(bulkState.dates || []);
        if (!selectedDates.length) {
            return null;
        }
        return {
            id: String(Date.now()),
            type: 'bulk',
            start: bulkState.start <= bulkState.end ? bulkState.start : bulkState.end,
            end: bulkState.start <= bulkState.end ? bulkState.end : bulkState.start,
            days: selectedDays,
            dates: bulkState.manualDates ? selectedDates : [],
            bulk_mode: bulkState.mode,
            selected_times: bulkState.mode === 'cancel' ? [] : uniqueTimes(bulkState.selected),
            added_times: bulkState.mode === 'add' ? uniqueTimes(bulkState.added) : []
        };
    }

    window.TTBMSchedulePlannerBoot = function () {
        document.querySelectorAll('[data-planner-root]').forEach(function (root) {
            if (root.dataset.plannerReady === 'yes') {
                return;
            }
            root.dataset.plannerReady = 'yes';
            var config = parseConfig(root);
            if (!config) {
                return;
            }
            var store = root.querySelector('[data-planner-store]');
            var countTarget = root.querySelector('[data-planner-count]');
            var state = {
                rules: normalizeRules(config.rules),
                tab: 'single',
                labels: config.labels || { days: [], fullDays: [] },
                globalTimes: uniqueTimes(config.globalTimes || []),
                configuredGlobalTimes: uniqueTimes(config.configuredGlobalTimes || []),
                weekdayTimes: config.weekdayTimes || {},
                specialDateTimes: Array.isArray(config.specialDateTimes) ? config.specialDateTimes : [],
                availableDates: Array.isArray(config.availableDates) ? config.availableDates : [],
                single: {
                    date: (Array.isArray(config.availableDates) && config.availableDates.length ? config.availableDates[0] : (config.today || '')),
                    fullCancel: false,
                    removed: [],
                    added: []
                },
                bulk: {
                    start: (Array.isArray(config.availableDates) && config.availableDates.length ? config.availableDates[0] : (config.today || '')),
                    end: (Array.isArray(config.availableDates) && config.availableDates.length ? config.availableDates[config.availableDates.length - 1] : (config.today || '')),
                    days: [],
                    autoDays: true,
                    dates: [],
                    manualDates: false,
                    mode: 'add',
                    selected: [],
                    added: [],
                    previewLimit: BULK_PREVIEW_PAGE_SIZE
                }
            };

            function syncStore() {
                store.value = JSON.stringify(state.rules);
                if (countTarget) {
                    countTarget.textContent = String(state.rules.length);
                }
            }

            function getSpecialTimesForDate(date) {
                if (!date) {
                    return [];
                }
                var matched = (state.specialDateTimes || []).find(function (item) {
                    if (!item || !item.start || !item.end) {
                        return false;
                    }
                    return date >= item.start && date <= item.end;
                });
                return matched ? uniqueTimes(matched.times || []) : [];
            }

            function getBulkRangeDates() {
                if (!state.bulk.start || !state.bulk.end) {
                    return [];
                }
                var start = state.bulk.start <= state.bulk.end ? state.bulk.start : state.bulk.end;
                var end = state.bulk.start <= state.bulk.end ? state.bulk.end : state.bulk.start;
                return state.availableDates.filter(function (date) {
                    return date >= start && date <= end;
                });
            }

            function getBulkRangeWeekdays() {
                var weekdays = [];
                getBulkRangeDates().forEach(function (date) {
                    var current = new Date(date + 'T00:00:00');
                    if (!Number.isNaN(current.getTime())) {
                        weekdays.push(current.getDay());
                    }
                });
                return sortNumbers(weekdays);
            }

            function getAutoBulkDays() {
                var rangeWeekdays = getBulkRangeWeekdays();
                if (state.configuredGlobalTimes.length) {
                    return rangeWeekdays;
                }
                var configuredWeekdays = rangeWeekdays.filter(function (day) {
                    var weekdayTimes = state.weekdayTimes[String(day)] || [];
                    return Array.isArray(weekdayTimes) && weekdayTimes.length;
                });
                // Prefer explicit time-slot config over fallback preview times when auto-selecting weekdays.
                if (configuredWeekdays.length) {
                    return configuredWeekdays;
                }
                var autoDays = [];
                getBulkRangeDates().forEach(function (date) {
                    var resolved = getResolvedSchedule(date);
                    if (resolved.cancelled || !Array.isArray(resolved.times) || !resolved.times.length) {
                        return;
                    }
                    var current = new Date(date + 'T00:00:00');
                    if (!Number.isNaN(current.getTime())) {
                        autoDays.push(current.getDay());
                    }
                });
                return sortNumbers(autoDays);
            }

            function getEffectiveBulkDays() {
                return state.bulk.autoDays ? getAutoBulkDays() : sortNumbers(state.bulk.days);
            }

            function getBaseTimesForDate(date) {
                if (!date) {
                    return state.globalTimes;
                }
                var specialTimes = getSpecialTimesForDate(date);
                if (specialTimes.length) {
                    return specialTimes;
                }
                var current = new Date(date + 'T00:00:00');
                if (Number.isNaN(current.getTime())) {
                    return state.globalTimes;
                }
                var weekdayTimes = state.weekdayTimes[String(current.getDay())] || [];
                return uniqueTimes(weekdayTimes.length ? weekdayTimes : state.globalTimes);
            }

            function getSelectableTimes() {
                var allTimes = state.globalTimes.slice();
                Object.keys(state.weekdayTimes || {}).forEach(function (key) {
                    allTimes = allTimes.concat(state.weekdayTimes[key] || []);
                });
                return uniqueTimes(allTimes);
            }
            function getBulkMatchedDates() {
                var effectiveDays = getEffectiveBulkDays();
                return getBulkRangeDates().filter(function (date) {
                    if (!effectiveDays.length) {
                        return true;
                    }
                    var current = new Date(date + 'T00:00:00');
                    return !Number.isNaN(current.getTime()) && effectiveDays.indexOf(current.getDay()) !== -1;
                });
            }
            function getBulkEnabledWeekdays() {
                var enabled = {};
                getBulkRangeWeekdays().forEach(function (day) {
                    enabled[day] = true;
                });
                return enabled;
            }

            function syncBulkDatesWithFilters() {
                var matchingDates = getBulkMatchedDates();
                var matchingLookup = {};
                matchingDates.forEach(function (date) {
                    matchingLookup[date] = true;
                });
                if (!state.bulk.manualDates) {
                    state.bulk.dates = matchingDates;
                    return;
                }
                state.bulk.dates = uniqueDates((state.bulk.dates || []).filter(function (date) {
                    return !!matchingLookup[date];
                }));
            }

            function getBulkSelectedDates() {
                return uniqueDates(state.bulk.dates || []);
            }

            function syncBulkDaysWithAvailability() {
                if (state.bulk.autoDays) {
                    return;
                }
                var enabled = getBulkEnabledWeekdays();
                state.bulk.days = state.bulk.days.filter(function (day) {
                    return !!enabled[day];
                });
            }
            function getBulkSelectableTimes() {
                var allTimes = [];
                getBulkSelectedDates().forEach(function (date) {
                    var resolved = getResolvedSchedule(date);
                    if (!resolved.cancelled) {
                        allTimes = allTimes.concat(resolved.times || []);
                    }
                });
                return uniqueTimes(allTimes);
            }

            function getResolvedSchedule(date) {
                var baseTimes = getBaseTimesForDate(date);
                return applyRules(baseTimes, state.rules, date);
            }

            function renderTabs() {
                root.querySelectorAll('[data-planner-tab]').forEach(function (button) {
                    button.classList.toggle('active', button.getAttribute('data-planner-tab') === state.tab);
                });
                root.querySelectorAll('[data-planner-panel]').forEach(function (panel) {
                    panel.classList.toggle('active', panel.getAttribute('data-planner-panel') === state.tab);
                });
            }

            function renderSinglePanel() {
                var panel = root.querySelector('[data-planner-panel="single"]');
                var resolved = getResolvedSchedule(state.single.date);
                var baseTimes = resolved.cancelled ? [] : resolved.times;
                var dayName = formatDay(state.single.date, state.labels.fullDays);
                var outcome = applyRules(baseTimes, [buildSingleRule(state.single)].filter(Boolean), state.single.date);
                panel.innerHTML = ''
                    + '<div class="ttbm-schedule-card">'
                    + '<div class="ttbm-schedule-card-title">' + text('pickDate', 'Pick a Date') + '</div>'
                    + '<div class="ttbm-schedule-grid">'
                    + '<div class="ttbm-schedule-field">'
                    + '<label>' + text('selectDate', 'Select Date') + '</label>'
                    + '<label><input type="hidden" data-planner-hidden-date data-state-key="single-date" value="' + (state.single.date || '') + '"><input type="text" class="formControl date_type" readonly value="' + formatDisplayDate(state.single.date || '') + '"></label>'
                    + '</div>'
                    + '</div>'
                    + (state.single.date ? '<div class="ttbm-schedule-notice">' + (dayName ? dayName + ' - ' : '') + text('baseScheduleLoaded', 'Base schedule loaded for this date.') + '</div>' : '')
                    + '<div class="ttbm-schedule-toggle">'
                    + '<div><strong>' + text('cancelEntireDay', 'Cancel entire day') + '</strong><p>' + text('cancelHelp', 'Use this when the whole date should disappear from booking.') + '</p></div>'
                    + '<button type="button" data-single-cancel class="' + (state.single.fullCancel ? 'active' : '') + '"></button>'
                    + '</div>'
                    + '<div class="ttbm-schedule-card" style="margin-top:16px;' + (state.single.fullCancel ? 'opacity:.45;pointer-events:none;' : '') + '">'
                    + '<div class="ttbm-schedule-note-label">' + text('existingSlots', 'Existing slots - click to mark for removal') + '</div>'
                    + '<div class="ttbm-schedule-pills">' + (baseTimes.length ? baseTimes.map(function (time) {
                        var active = state.single.removed.indexOf(time) !== -1 ? 'active-remove' : '';
                        return '<button type="button" data-single-remove="' + time + '" class="' + active + '">' + time + '</button>';
                    }).join('') : '<span class="ttbm-schedule-empty">' + (resolved.cancelled ? text('fullDayCancelled', 'Full day cancelled') : text('noBaseSlots', 'No base slots found. Add a custom slot below.')) + '</span>') + '</div>'
                    + '<div class="ttbm-schedule-card-title" style="margin-top:18px;">' + text('addNewSlot', 'Add New Slot') + '</div>'
                    + '<div class="ttbm-schedule-row">'
                    + '<div class="ttbm-schedule-field" style="max-width:180px;">'
                    + '<input type="time" data-single-new-time>'
                    + '</div>'
                    + '<button type="button" class="ttbm-schedule-action" data-single-add>' + text('addSlot', 'Add Slot') + '</button>'
                    + '</div>'
                    + '<div class="ttbm-schedule-draft-pills" style="margin-top:12px;">' + state.single.added.map(function (time) {
                        return '<span>' + time + '<button type="button" data-single-added-remove="' + time + '">x</button></span>';
                    }).join('') + '</div>'
                    + '</div>'
                    + '<div class="ttbm-schedule-preview">'
                    + '<h4>' + text('previewTitle', 'Preview - Final Schedule') + '</h4>'
                    + renderPreview(state.single.date, outcome, state.labels.fullDays)
                    + '</div>'
                    + '<div class="ttbm-schedule-actions">'
                    + '<button type="button" class="ttbm-schedule-action primary" data-single-save>' + text('saveRule', 'Save Rule') + '</button>'
                    + '<button type="button" class="ttbm-schedule-action" data-single-reset>' + text('reset', 'Reset') + '</button>'
                    + '</div>'
                    + '</div>';
            }

            function renderBulkPanel() {
                var panel = root.querySelector('[data-planner-panel="bulk"]');
                syncBulkDaysWithAvailability();
                var matchingDates = getBulkMatchedDates();
                syncBulkDatesWithFilters();
                var selectedDates = getBulkSelectedDates();
                var availableTimes = getBulkSelectableTimes();
                var effectiveDays = getEffectiveBulkDays();
                var previewLimit = Math.max(BULK_PREVIEW_PAGE_SIZE, parseInt(state.bulk.previewLimit || BULK_PREVIEW_PAGE_SIZE, 10));
                var previewDates = selectedDates.slice(0, previewLimit);
                var hasMorePreviewDates = selectedDates.length > previewDates.length;
                var canCollapsePreviewDates = previewDates.length > BULK_PREVIEW_PAGE_SIZE;
                var enabledWeekdays = getBulkEnabledWeekdays();
                panel.innerHTML = ''
                    + '<div class="ttbm-schedule-card">'
                    + '<div class="ttbm-schedule-card-title">' + text('dateRangeWeekdays', 'Date Range and Weekdays') + '</div>'
                    + '<div class="ttbm-schedule-grid">'
                    + '<div class="ttbm-schedule-field"><label>' + text('startDate', 'Start Date') + '</label><label><input type="hidden" data-planner-hidden-date data-state-key="bulk-start" value="' + (state.bulk.start || '') + '"><input type="text" class="formControl date_type" readonly value="' + formatDisplayDate(state.bulk.start || '') + '"></label></div>'
                    + '<div class="ttbm-schedule-field"><label>' + text('endDate', 'End Date') + '</label><label><input type="hidden" data-planner-hidden-date data-state-key="bulk-end" value="' + (state.bulk.end || '') + '"><input type="text" class="formControl date_type" readonly value="' + formatDisplayDate(state.bulk.end || '') + '"></label></div>'
                    + '</div>'
                    + '<div class="ttbm-schedule-note-label" style="margin-top:18px;">' + text('appliesToWeekdays', 'Applies to weekdays') + '</div>'
                    + '<div class="ttbm-schedule-days">' + state.labels.days.map(function (label, index) {
                        var active = effectiveDays.indexOf(index) !== -1 ? 'active' : '';
                        var disabled = !enabledWeekdays[index];
                        return '<button type="button" data-bulk-day="' + index + '" class="' + active + (disabled ? ' disabled' : '') + '"' + (disabled ? ' disabled' : '') + '>' + label + '</button>';
                    }).join('') + '</div>'
                    + '<div class="ttbm-schedule-note-label" style="margin-top:18px;">' + text('actionType', 'Action type') + '</div>'
                    + '<div class="ttbm-schedule-modes">'
                    + '<button type="button" data-bulk-mode="add" class="' + (state.bulk.mode === 'add' ? 'active-add' : '') + '">' + text('addSlots', 'Add Slots') + '</button>'
                    + '<button type="button" data-bulk-mode="remove" class="' + (state.bulk.mode === 'remove' ? 'active-remove' : '') + '">' + text('removeSlots', 'Remove Slots') + '</button>'
                    + '<button type="button" data-bulk-mode="cancel" class="' + (state.bulk.mode === 'cancel' ? 'active-cancel' : '') + '">' + text('cancelDays', 'Cancel Days') + '</button>'
                    + '</div>'
                    + '<div class="ttbm-schedule-note-label" style="margin-top:18px;">' + text('chooseDates', 'Choose Dates') + '</div>'
                    + (matchingDates.length ? '<div class="ttbm-schedule-preview-meta" style="margin:-4px 0 12px;">' + textWithTokens('selectedRangeDates', 'Selected {selected} of {total} matching dates', {
                        selected: selectedDates.length,
                        total: matchingDates.length
                    }) + '</div>' : '')
                    + '<div class="ttbm-schedule-date-pills">' + (matchingDates.length ? matchingDates.map(function (date) {
                        var active = selectedDates.indexOf(date) !== -1 ? ' active' : '';
                        return '<button type="button" data-bulk-date="' + date + '" class="' + active.trim() + '">' + renderSelectableDate(date, state.labels.fullDays) + '</button>';
                    }).join('') : '<span class="ttbm-schedule-empty">' + text('noMatchingDates', 'No dates match the selected weekdays.') + '</span>') + '</div>'
                    + '<div style="margin-top:18px;' + (state.bulk.mode === 'cancel' ? 'display:none;' : '') + '">'
                    + '<div class="ttbm-schedule-note-label">' + text('existingSlotsShort', 'Existing slots') + '</div>'
                    + '<div class="ttbm-schedule-pills">' + (availableTimes.length ? availableTimes.map(function (time) {
                        var active = state.bulk.selected.indexOf(time) !== -1 ? (state.bulk.mode === 'remove' ? 'active-remove' : 'active-add') : '';
                        return '<button type="button" data-bulk-select="' + time + '" class="' + active + '">' + time + '</button>';
                    }).join('') : '<span class="ttbm-schedule-empty">' + (selectedDates.length ? text('noBaseSlotsConfigured', 'No base slots are configured yet.') : text('noSelectedDates', 'Select at least one date from the range.')) + '</span>') + '</div>'
                    + '<div class="ttbm-schedule-card-title" style="margin-top:18px;">' + text('addCustomSlots', 'Add custom slots') + '</div>'
                    + '<div class="ttbm-schedule-row"><div class="ttbm-schedule-field" style="max-width:180px;"><input type="time" data-bulk-new-time></div><button type="button" class="ttbm-schedule-action" data-bulk-add>' + text('addSlot', 'Add Slot') + '</button></div>'
                    + '<div class="ttbm-schedule-draft-pills" style="margin-top:12px;">' + state.bulk.added.map(function (time) {
                        return '<span>' + time + '<button type="button" data-bulk-added-remove="' + time + '">x</button></span>';
                    }).join('') + '</div>'
                    + '</div>'
                    + '<div class="ttbm-schedule-preview">'
                    + '<h4>' + text('affectedDatesPreview', 'Affected dates preview') + '</h4>'
                    + (selectedDates.length ? '<div class="ttbm-schedule-preview-meta">' + textWithTokens('showingPreviewDates', 'Showing {visible} of {total} affected dates', {
                        visible: previewDates.length,
                        total: selectedDates.length
                    }) + '</div>' : '')
                    + (previewDates.length ? previewDates.map(function (date) {
                        var resolved = getResolvedSchedule(date);
                        var baseTimes = resolved.cancelled ? [] : resolved.times;
                        var previewRule = buildBulkRule(state.bulk, effectiveDays);
                        var outcome = resolved.cancelled && (!previewRule || previewRule.bulk_mode !== 'add') ? resolved : applyRules(baseTimes, previewRule ? [previewRule] : [], date);
                        return renderPreview(date, outcome, state.labels.fullDays);
                    }).join('') : '<div class="ttbm-schedule-empty">' + text('noSelectedDates', 'Select at least one date from the range.') + '</div>')
                    + ((hasMorePreviewDates || canCollapsePreviewDates) ? '<div class="ttbm-schedule-preview-footer"><div class="ttbm-schedule-preview-actions">' + (hasMorePreviewDates ? '<button type="button" class="ttbm-schedule-action" data-bulk-preview-more>' + text('loadMoreDates', 'Load More') + '</button>' : '') + (canCollapsePreviewDates ? '<button type="button" class="ttbm-schedule-action" data-bulk-preview-less>' + text('showLessDates', 'Show Less') + '</button>' : '') + '</div></div>' : '')
                    + '</div>'
                    + '<div class="ttbm-schedule-actions"><button type="button" class="ttbm-schedule-action primary" data-bulk-save>' + text('saveBulkRule', 'Save Bulk Rule') + '</button><button type="button" class="ttbm-schedule-action" data-bulk-reset>' + text('reset', 'Reset') + '</button></div>'
                    + '</div>';
            }

            function renderRulesPanel() {
                var panel = root.querySelector('[data-planner-panel="rules"]');
                if (!state.rules.length) {
                    panel.innerHTML = '<div class="ttbm-schedule-empty">' + text('noRulesYet', 'No planner rules yet. Save a single-date or bulk rule and it will appear here.') + '</div>';
                    return;
                }
                panel.innerHTML = state.rules.map(function (rule, index) {
                    var title = '';
                    var meta = '';
                    var badges = '';
                    if (rule.type === 'single') {
                        title = rule.full_cancel ? text('singleDateCancel', 'Single Date Cancel') : text('singleDateOverride', 'Single Date Override');
                        meta = rule.date || '';
                        if (rule.full_cancel) {
                            badges = '<span class="ttbm-schedule-badge cancel">' + text('cancelled', 'Cancelled') + '</span>';
                        } else {
                            badges = (rule.added || []).map(function (time) {
                                return '<span class="ttbm-schedule-badge add">+' + time + '</span>';
                            }).join('') + (rule.removed || []).map(function (time) {
                                return '<span class="ttbm-schedule-badge remove">-' + time + '</span>';
                            }).join('');
                        }
                    } else {
                        title = rule.bulk_mode === 'cancel' ? text('bulkDayCancel', 'Bulk Day Cancel') : (rule.bulk_mode === 'remove' ? text('bulkSlotRemoval', 'Bulk Slot Removal') : text('bulkSlotAddition', 'Bulk Slot Addition'));
                        if (Array.isArray(rule.dates) && rule.dates.length) {
                            meta = formatSpecificDateMeta(rule.dates);
                        } else {
                            meta = (rule.start || '') + ' - ' + (rule.end || '');
                        }
                        if (!rule.dates || !rule.dates.length) {
                            if (Array.isArray(rule.days) && rule.days.length) {
                                meta += ' - ' + rule.days.map(function (dayIndex) {
                                    return state.labels.days[dayIndex] || '';
                                }).join(', ');
                            } else {
                                meta += ' - ' + text('allDays', 'All days');
                            }
                        }
                        badges = (rule.selected_times || []).map(function (time) {
                            return '<span class="ttbm-schedule-badge ' + (rule.bulk_mode === 'remove' ? 'remove' : 'add') + '">' + (rule.bulk_mode === 'remove' ? '-' : '+') + time + '</span>';
                        }).join('') + (rule.added_times || []).map(function (time) {
                            return '<span class="ttbm-schedule-badge add">+' + time + '</span>';
                        }).join('');
                        if (rule.bulk_mode === 'cancel') {
                            badges = '<span class="ttbm-schedule-badge cancel">' + text('cancelled', 'Cancelled') + '</span>';
                        }
                    }
                    return '<div class="ttbm-schedule-rule"><div><h4>' + title + '</h4><p>' + meta + '</p><div class="ttbm-schedule-preview-times" style="margin-top:10px;">' + badges + '</div></div><button type="button" class="ttbm-schedule-rule-remove" data-rule-remove="' + index + '">' + text('remove', 'Remove') + '</button></div>';
                }).join('');
            }

            function rerender(currentRoot, currentState) {
                syncStore();
                renderTabs();
                renderSinglePanel();
                renderBulkPanel();
                renderRulesPanel();
                initPlannerDatePickers(currentRoot || root, currentState || state, rerender);
            }

            root.addEventListener('click', function (event) {
                var target = event.target.closest('button');
                if (!target) {
                    return;
                }
                if (target.hasAttribute('data-planner-tab')) {
                    state.tab = target.getAttribute('data-planner-tab');
                    rerender(root, state);
                    return;
                }
                if (target.hasAttribute('data-single-cancel')) {
                    state.single.fullCancel = !state.single.fullCancel;
                    rerender(root, state);
                    return;
                }
                if (target.hasAttribute('data-single-remove')) {
                    var singleTime = target.getAttribute('data-single-remove');
                    var singleIndex = state.single.removed.indexOf(singleTime);
                    if (singleIndex === -1) {
                        state.single.removed.push(singleTime);
                    } else {
                        state.single.removed.splice(singleIndex, 1);
                    }
                    rerender(root, state);
                    return;
                }
                if (target.hasAttribute('data-single-add')) {
                    var singleInput = root.querySelector('[data-single-new-time]');
                    var newSingleTime = normalizeTime(singleInput ? singleInput.value : '');
                    if (newSingleTime && state.single.added.indexOf(newSingleTime) === -1) {
                        state.single.added.push(newSingleTime);
                    }
                    rerender(root, state);
                    return;
                }
                if (target.hasAttribute('data-single-added-remove')) {
                    state.single.added = state.single.added.filter(function (time) {
                        return time !== target.getAttribute('data-single-added-remove');
                    });
                    rerender(root, state);
                    return;
                }
                if (target.hasAttribute('data-single-save')) {
                    var singleRule = buildSingleRule(state.single);
                    if (singleRule) {
                        state.rules.push(singleRule);
                        state.tab = 'rules';
                        state.single.fullCancel = false;
                        state.single.removed = [];
                        state.single.added = [];
                        rerender(root, state);
                    }
                    return;
                }
                if (target.hasAttribute('data-single-reset')) {
                    state.single.fullCancel = false;
                    state.single.removed = [];
                    state.single.added = [];
                    rerender(root, state);
                    return;
                }
                if (target.hasAttribute('data-bulk-date')) {
                    var bulkDate = target.getAttribute('data-bulk-date');
                    if (!state.bulk.manualDates) {
                        state.bulk.manualDates = true;
                        state.bulk.dates = [bulkDate];
                    } else {
                        var bulkDateIndex = state.bulk.dates.indexOf(bulkDate);
                        if (bulkDateIndex === -1) {
                            state.bulk.dates.push(bulkDate);
                        } else {
                            state.bulk.dates.splice(bulkDateIndex, 1);
                        }
                    }
                    state.bulk.dates = uniqueDates(state.bulk.dates);
                    state.bulk.previewLimit = BULK_PREVIEW_PAGE_SIZE;
                    rerender(root, state);
                    return;
                }
                if (target.hasAttribute('data-bulk-day')) {
                    if (target.disabled) {
                        return;
                    }
                    var dayValue = parseInt(target.getAttribute('data-bulk-day'), 10);
                    var manualDays = state.bulk.autoDays ? getEffectiveBulkDays().slice() : state.bulk.days.slice();
                    var dayIndex = manualDays.indexOf(dayValue);
                    state.bulk.autoDays = false;
                    state.bulk.manualDates = false;
                    if (dayIndex === -1) {
                        manualDays.push(dayValue);
                    } else {
                        manualDays.splice(dayIndex, 1);
                    }
                    state.bulk.days = sortNumbers(manualDays);
                    state.bulk.dates = [];
                    state.bulk.previewLimit = BULK_PREVIEW_PAGE_SIZE;
                    rerender(root, state);
                    return;
                }
                if (target.hasAttribute('data-bulk-mode')) {
                    state.bulk.mode = target.getAttribute('data-bulk-mode');
                    rerender(root, state);
                    return;
                }
                if (target.hasAttribute('data-bulk-select')) {
                    var bulkTime = target.getAttribute('data-bulk-select');
                    var bulkIndex = state.bulk.selected.indexOf(bulkTime);
                    if (bulkIndex === -1) {
                        state.bulk.selected.push(bulkTime);
                    } else {
                        state.bulk.selected.splice(bulkIndex, 1);
                    }
                    rerender(root, state);
                    return;
                }
                if (target.hasAttribute('data-bulk-add')) {
                    var bulkInput = root.querySelector('[data-bulk-new-time]');
                    var newBulkTime = normalizeTime(bulkInput ? bulkInput.value : '');
                    if (newBulkTime && state.bulk.added.indexOf(newBulkTime) === -1) {
                        state.bulk.added.push(newBulkTime);
                    }
                    rerender(root, state);
                    return;
                }
                if (target.hasAttribute('data-bulk-added-remove')) {
                    state.bulk.added = state.bulk.added.filter(function (time) {
                        return time !== target.getAttribute('data-bulk-added-remove');
                    });
                    rerender(root, state);
                    return;
                }
                if (target.hasAttribute('data-bulk-save')) {
                    var bulkRule = buildBulkRule(state.bulk, getEffectiveBulkDays());
                    if (bulkRule) {
                        state.rules.push(bulkRule);
                        state.tab = 'rules';
                        state.bulk.days = [];
                        state.bulk.autoDays = true;
                        state.bulk.dates = [];
                        state.bulk.manualDates = false;
                        state.bulk.mode = 'add';
                        state.bulk.selected = [];
                        state.bulk.added = [];
                        state.bulk.previewLimit = BULK_PREVIEW_PAGE_SIZE;
                        rerender();
                    }
                    return;
                }
                if (target.hasAttribute('data-bulk-reset')) {
                    state.bulk.days = [];
                    state.bulk.autoDays = true;
                    state.bulk.dates = [];
                    state.bulk.manualDates = false;
                    state.bulk.mode = 'add';
                    state.bulk.selected = [];
                    state.bulk.added = [];
                    state.bulk.previewLimit = BULK_PREVIEW_PAGE_SIZE;
                    rerender(root, state);
                    return;
                }
                if (target.hasAttribute('data-bulk-preview-more')) {
                    state.bulk.previewLimit = previewLimitOrDefault(state.bulk.previewLimit) + BULK_PREVIEW_PAGE_SIZE;
                    rerender(root, state);
                    return;
                }
                if (target.hasAttribute('data-bulk-preview-less')) {
                    state.bulk.previewLimit = BULK_PREVIEW_PAGE_SIZE;
                    rerender(root, state);
                    return;
                }
                if (target.hasAttribute('data-rule-remove')) {
                    state.rules.splice(parseInt(target.getAttribute('data-rule-remove'), 10), 1);
                    rerender(root, state);
                    return;
                }
                if (target.hasAttribute('data-planner-clear')) {
                    state.rules = [];
                    rerender(root, state);
                }
            });

            rerender(root, state);
            setTimeout(function () {
                if (document.body.contains(root)) {
                    rerender(root, state);
                }
            }, 250);
            document.addEventListener('click', function (event) {
                var tabButton = event.target.closest('[data-tabs-target="#ttbm_settings_schedule_planner"]');
                if (!tabButton) {
                    return;
                }
                setTimeout(function () {
                    if (document.body.contains(root)) {
                        rerender(root, state);
                    }
                }, 50);
            });
        });
    };

    function previewLimitOrDefault(limit) {
        var parsed = parseInt(limit || BULK_PREVIEW_PAGE_SIZE, 10);
        return Number.isNaN(parsed) ? BULK_PREVIEW_PAGE_SIZE : Math.max(BULK_PREVIEW_PAGE_SIZE, parsed);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            if (window.TTBMSchedulePlannerBoot) {
                window.TTBMSchedulePlannerBoot();
            }
        });
    } else if (window.TTBMSchedulePlannerBoot) {
        window.TTBMSchedulePlannerBoot();
    }
}());
