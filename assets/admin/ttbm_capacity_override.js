(function () {
    function getStrings() {
        return window.TTBMCapacityOverrideStrings || {};
    }

    function text(key, fallback) {
        var strings = getStrings();
        return Object.prototype.hasOwnProperty.call(strings, key) ? strings[key] : fallback;
    }

    function parseConfig(root) {
        var node = root.querySelector('[data-capacity-config]');
        if (!node) {
            return null;
        }
        try {
            return JSON.parse(node.textContent || '{}');
        } catch (error) {
            return null;
        }
    }

    function normalizeDate(value) {
        if (!value) {
            return '';
        }
        var stamp = Date.parse(String(value).replace(/-/g, '/'));
        if (Number.isNaN(stamp)) {
            return '';
        }
        var date = new Date(stamp);
        return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
    }

    function formatDisplayDate(date) {
        if (!date) {
            return '';
        }
        if (window.jQuery && jQuery.datepicker) {
            return jQuery.datepicker.formatDate(window.ttbm_date_format || 'yy-mm-dd', new Date(date + 'T00:00:00'));
        }
        return date;
    }

    function formatLogDate(start, end) {
        if (!start) {
            return '';
        }
        if (start === end || !end) {
            return start;
        }
        return start + ' - ' + end;
    }

    function countDays(start, end) {
        if (!start || !end) {
            return 0;
        }
        var current = new Date(start + 'T00:00:00');
        var finish = new Date(end + 'T00:00:00');
        if (Number.isNaN(current.getTime()) || Number.isNaN(finish.getTime()) || current > finish) {
            return 0;
        }
        var total = 0;
        while (current <= finish) {
            total += 1;
            current.setDate(current.getDate() + 1);
        }
        return total;
    }
    function normalizeTime(value) {
        if (!value) {
            return '';
        }
        var match = String(value).match(/^(\d{1,2}):(\d{2})/);
        if (!match) {
            return '';
        }
        return String(parseInt(match[1], 10)).padStart(2, '0') + ':' + match[2];
    }
    function formatScopeLabel(start, end, time) {
        var label = formatLogDate(start, end);
        if (!time) {
            return label;
        }
        return label + ' @ ' + time;
    }
    function getPricingRoot() {
        return document.querySelector('.ttbm_settings_pricing') || document;
    }
    function getLiveSharedSettings(state) {
        var pricingRoot = getPricingRoot();
        var tourTypeInput = pricingRoot.querySelector('input[name="ttbm_type"]');
        var toggle = pricingRoot.querySelector('input[name="ttbm_display_global_qty"]');
        var qtyInput = pricingRoot.querySelector('input[name="ttbm_global_qty"]');
        var collapsePanel = pricingRoot.querySelector('[data-collapse="#ttbm_display_global_qty"]');
        var isGeneralTour = !tourTypeInput || tourTypeInput.value === 'general';
        var collapseActive = collapsePanel ? collapsePanel.classList.contains('mActive') : null;
        if (toggle) {
            return {
                enabled: isGeneralTour && (collapseActive !== null ? collapseActive : !!toggle.checked),
                capacity: qtyInput ? Math.max(0, parseInt(qtyInput.value, 10) || 0) : Math.max(0, parseInt(state.sharedCapacity, 10) || 0)
            };
        }
        return {
            enabled: false,
            capacity: Math.max(0, parseInt(state.sharedCapacity, 10) || 0)
        };
    }
    function isSharedCapacityEnabled(state) {
        return getLiveSharedSettings(state).enabled;
    }
    function getDefaultCapacityForTicket(state, ticketName) {
        if (isSharedCapacityEnabled(state)) {
            return getLiveSharedSettings(state).capacity;
        }
        return state.ticketMap[ticketName] || 0;
    }
    function getTicketSelectorTitle(state) {
        return isSharedCapacityEnabled(state)
            ? text('sharedSelectTicketType', 'Select Ticket Label')
            : text('selectTicketType', 'Select Ticket Type');
    }
    function getTicketSelectorDescription(state) {
        return isSharedCapacityEnabled(state)
            ? text('sharedTicketSub', 'Shared Quantity is enabled. The selected ticket label identifies the rule, but the override changes the shared capacity pool for all ticket types.')
            : text('ticketSub', 'Choose which ticket type should receive the override.');
    }
    function getAdjustmentDescription(state) {
        return isSharedCapacityEnabled(state)
            ? text('sharedAdjustSub', 'This override updates the shared quantity pool used by every ticket type on the selected date or range.')
            : text('adjustSub', 'Override capacity for one date or a continuous date range.');
    }

    function normalizeRules(rules) {
        return (Array.isArray(rules) ? rules : []).map(function (rule) {
            if (!rule || typeof rule !== 'object') {
                return null;
            }
            var start = normalizeDate(rule.start || '');
            var end = normalizeDate(rule.end || rule.start || '');
            if (!start || !end || !rule.ticket_type) {
                return null;
            }
            return {
                id: String(rule.id || Date.now()),
                type: rule.type === 'range' ? 'range' : 'single',
                ticket_type: String(rule.ticket_type),
                start: start,
                end: end,
                time: normalizeTime(rule.time || ''),
                adjustment_mode: ['add', 'reduce', 'override'].indexOf(rule.adjustment_mode) !== -1 ? rule.adjustment_mode : 'add',
                amount: Math.max(0, parseInt(rule.amount, 10) || 0)
            };
        }).filter(Boolean);
    }
    function normalizeSlotMap(slotMap) {
        var map = {};
        if (!slotMap || typeof slotMap !== 'object') {
            return map;
        }
        Object.keys(slotMap).forEach(function (date) {
            var normalizedDate = normalizeDate(date);
            if (!normalizedDate) {
                return;
            }
            var slotLookup = {};
            (Array.isArray(slotMap[date]) ? slotMap[date] : []).forEach(function (slot) {
                var time = normalizeTime(slot && (slot.time || slot.value || slot.mep_ticket_time || slot));
                if (!time) {
                    return;
                }
                slotLookup[time] = {
                    time: time,
                    label: String((slot && (slot.label || slot.mep_ticket_time_name)) || time)
                };
            });
            map[normalizedDate] = Object.keys(slotLookup).sort().map(function (time) {
                return slotLookup[time];
            });
        });
        return map;
    }
    function getSlotsForDate(state, date) {
        var normalizedDate = normalizeDate(date);
        if (!normalizedDate || !state.slotMap[normalizedDate]) {
            return [];
        }
        return state.slotMap[normalizedDate].slice();
    }
    function getDatesInRange(state, start, end) {
        var dates = [];
        var normalizedStart = normalizeDate(start);
        var normalizedEnd = normalizeDate(end);
        if (!normalizedStart || !normalizedEnd || normalizedStart > normalizedEnd) {
            return dates;
        }
        state.availableDates.forEach(function (date) {
            if (date >= normalizedStart && date <= normalizedEnd) {
                dates.push(date);
            }
        });
        return dates;
    }
    function getSlotsForRange(state, start, end) {
        var slotLookup = {};
        getDatesInRange(state, start, end).forEach(function (date) {
            getSlotsForDate(state, date).forEach(function (slot) {
                slotLookup[slot.time] = slot;
            });
        });
        return Object.keys(slotLookup).sort().map(function (time) {
            return slotLookup[time];
        });
    }
    function ensureValidDraftTime(state, draftKey) {
        var draft = draftKey === 'single' ? state.single : state.range;
        var slots = draftKey === 'single'
            ? getSlotsForDate(state, draft.date)
            : getSlotsForRange(state, draft.start, draft.end);
        if (!draft.time) {
            return;
        }
        var exists = slots.some(function (slot) {
            return slot.time === draft.time;
        });
        if (!exists) {
            draft.time = '';
        }
    }

    function initDatePickers(root, state, rerender) {
        if (!window.jQuery || !jQuery.datepicker) {
            return;
        }
        var availableDates = Array.isArray(state.availableDates) ? state.availableDates : [];
        var allowed = {};
        availableDates.forEach(function (date) {
            allowed[date] = true;
        });
        root.querySelectorAll('[data-capacity-hidden-date]').forEach(function (hiddenInput) {
            var key = hiddenInput.getAttribute('data-capacity-hidden-date');
            var visibleInput = hiddenInput.parentElement ? hiddenInput.parentElement.querySelector('.ttbm-capacity-date-input') : null;
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
                    return [!!allowed[iso], allowed[iso] ? 'ui-state-highlight' : '', allowed[iso] ? 'Tour date available' : 'Unavailable'];
                },
                onSelect: function (dateText, inst) {
                    var iso = inst.selectedYear + '-' + String(parseInt(inst.selectedMonth, 10) + 1).padStart(2, '0') + '-' + String(parseInt(inst.selectedDay, 10)).padStart(2, '0');
                    hiddenInput.value = iso;
                    if (key === 'single-date') {
                        state.single.date = iso;
                        ensureValidDraftTime(state, 'single');
                    } else if (key === 'range-start') {
                        state.range.start = iso;
                        ensureValidDraftTime(state, 'range');
                    } else if (key === 'range-end') {
                        state.range.end = iso;
                        ensureValidDraftTime(state, 'range');
                    }
                    rerender();
                    jQuery(this).datepicker('hide');
                }
            });
        });
    }

    function draftToRule(state) {
        var draft = state.mode === 'single' ? state.single : state.range;
        var ticket = state.selectedTicket;
        if (!ticket || !draft.start && state.mode === 'range') {
            return null;
        }
        var baseCapacity = state.ticketMap[ticket] || 0;
        var overrideValue = draft.override === '' ? '' : Math.max(0, parseInt(draft.override, 10) || 0);
        var add = Math.max(0, parseInt(draft.add, 10) || 0);
        var reduce = Math.max(0, parseInt(draft.reduce, 10) || 0);
        var mode = 'add';
        var amount = add;
        var finalCapacity = baseCapacity + add - reduce;
        var kind = finalCapacity > baseCapacity ? 'up' : (finalCapacity < baseCapacity ? 'down' : 'override');
        if (overrideValue !== '') {
            mode = 'override';
            amount = overrideValue;
            finalCapacity = overrideValue;
            kind = overrideValue > baseCapacity ? 'up' : (overrideValue < baseCapacity ? 'down' : 'override');
        } else if (reduce > 0 && add === 0) {
            mode = 'reduce';
            amount = reduce;
            finalCapacity = Math.max(0, baseCapacity - reduce);
            kind = finalCapacity < baseCapacity ? 'down' : 'override';
        } else if (add >= reduce) {
            mode = 'add';
            amount = Math.max(0, add - reduce);
            finalCapacity = baseCapacity + amount;
            kind = amount > 0 ? 'up' : 'override';
        } else {
            mode = 'reduce';
            amount = reduce - add;
            finalCapacity = Math.max(0, baseCapacity - amount);
            kind = amount > 0 ? 'down' : 'override';
        }
        if (amount === 0 && mode !== 'override') {
            return null;
        }
        return {
            id: state.editingId || String(Date.now()),
            type: state.mode,
            ticket_type: ticket,
            start: state.mode === 'single' ? draft.date : draft.start,
            end: state.mode === 'single' ? draft.date : draft.end,
            time: normalizeTime(draft.time || ''),
            adjustment_mode: mode,
            amount: amount,
            computed_capacity: Math.max(0, finalCapacity),
            change_kind: kind
        };
    }

    function resetDraft(state) {
        var defaultDate = state.availableDates.length ? state.availableDates[0] : state.today;
        var rangeEnd = state.availableDates.length ? state.availableDates[state.availableDates.length - 1] : state.today;
        state.single = { date: defaultDate, time: '', add: 0, reduce: 0, override: '' };
        state.range = { start: defaultDate, end: rangeEnd, time: '', add: 0, reduce: 0, override: '' };
        state.editingId = '';
        ensureValidDraftTime(state, 'single');
        ensureValidDraftTime(state, 'range');
    }

    window.TTBMCapacityOverrideBoot = function () {
        document.querySelectorAll('[data-capacity-root]').forEach(function (root) {
            if (root.dataset.capacityReady === 'yes') {
                return;
            }
            root.dataset.capacityReady = 'yes';
            var config = parseConfig(root);
            if (!config) {
                return;
            }
            var store = root.querySelector('[data-capacity-store]');
            var count = root.querySelector('[data-capacity-count]');
            var tickets = Array.isArray(config.tickets) ? config.tickets : [];
            var state = {
                rules: normalizeRules(config.rules),
                tickets: tickets,
                ticketMap: tickets.reduce(function (acc, item) {
                    acc[item.name] = parseInt(item.capacity, 10) || 0;
                    return acc;
                }, {}),
                sharedCapacityEnabled: !!config.sharedCapacityEnabled,
                sharedCapacity: parseInt(config.sharedCapacity, 10) || 0,
                availableDates: Array.isArray(config.availableDates) ? config.availableDates : [],
                slotMap: normalizeSlotMap(config.slotMap || {}),
                today: normalizeDate(config.today || '') || '',
                selectedTicket: tickets.length ? tickets[0].name : '',
                mode: 'single',
                single: { date: '', time: '', add: 0, reduce: 0, override: '' },
                range: { start: '', end: '', time: '', add: 0, reduce: 0, override: '' },
                editingId: ''
            };
            resetDraft(state);

            function syncStore() {
                store.value = JSON.stringify(state.rules);
                if (count) {
                    count.textContent = String(state.rules.length);
                }
            }

            function renderTicketSelector() {
                if (!state.tickets.length) {
                    return '<div class="ttbm-capacity-empty">' + text('noTickets', 'Add ticket types in Pricing first to use capacity override.') + '</div>';
                }
                return '<div class="ttbm-capacity-card"><div class="ttbm-capacity-card-head"><i class="fas fa-ticket-alt"></i><div><h4>' + getTicketSelectorTitle(state) + '</h4><p>' + getTicketSelectorDescription(state) + '</p></div></div><div class="ttbm-capacity-card-body"><div class="ttbm-capacity-ticket-pills">' + state.tickets.map(function (ticket) {
                    var capLabel = isSharedCapacityEnabled(state)
                        ? text('sharedPoolLabel', 'Shared Pool') + ' ' + getDefaultCapacityForTicket(state, ticket.name)
                        : ticket.capacity + ' ' + text('capShort', 'cap');
                    return '<button type="button" class="ttbm-capacity-ticket-pill ' + (state.selectedTicket === ticket.name ? 'active' : '') + '" data-ticket-name="' + ticket.name + '">' + ticket.name + ' <small>' + capLabel + '</small></button>';
                }).join('') + '</div><div class="ttbm-capacity-note">' + getTicketSelectorDescription(state) + '</div></div></div>';
            }

            function renderAdjustmentCard() {
                if (!state.tickets.length) {
                    return '';
                }
                var draft = state.mode === 'single' ? state.single : state.range;
                var draftSlots = state.mode === 'single'
                    ? getSlotsForDate(state, draft.date)
                    : getSlotsForRange(state, draft.start, draft.end);
                var defaultCap = getDefaultCapacityForTicket(state, state.selectedTicket);
                var previewRule = draftToRule(state);
                var previewCap = previewRule ? previewRule.computed_capacity : defaultCap;
                var previewClass = previewRule ? previewRule.change_kind : '';
                var affectedDays = state.mode === 'range' ? countDays(draft.start, draft.end) : 1;
                var dateFields = state.mode === 'single'
                    ? '<div class="ttbm-capacity-field"><label>' + text('selectDate', 'Select Date') + '</label><label><input type="hidden" data-capacity-hidden-date="single-date" value="' + (draft.date || '') + '"><input type="text" class="formControl ttbm-capacity-date-input" readonly value="' + formatDisplayDate(draft.date || '') + '"></label></div>'
                    : '<div class="ttbm-capacity-field"><label>' + text('startDate', 'Start Date') + '</label><label><input type="hidden" data-capacity-hidden-date="range-start" value="' + (draft.start || '') + '"><input type="text" class="formControl ttbm-capacity-date-input" readonly value="' + formatDisplayDate(draft.start || '') + '"></label></div><div class="ttbm-capacity-field"><label>' + text('endDate', 'End Date') + '</label><label><input type="hidden" data-capacity-hidden-date="range-end" value="' + (draft.end || '') + '"><input type="text" class="formControl ttbm-capacity-date-input" readonly value="' + formatDisplayDate(draft.end || '') + '"></label></div>';
                var timeField = draftSlots.length
                    ? '<div class="ttbm-capacity-field"><label>' + text('timeSlot', 'Time Slot') + '</label><select class="formControl" data-draft-select="' + state.mode + '-time"><option value="">' + text('allTimeSlots', 'All time slots') + '</option>' + draftSlots.map(function (slot) {
                        return '<option value="' + slot.time + '"' + (draft.time === slot.time ? ' selected' : '') + '>' + slot.label + '</option>';
                    }).join('') + '</select></div>'
                    : '<div class="ttbm-capacity-field"><label>' + text('timeSlot', 'Time Slot') + '</label><div class="ttbm-capacity-note">' + text('noTimeSlots', 'No time slots available for this selection.') + '</div></div>';
                return '<div class="ttbm-capacity-card"><div class="ttbm-capacity-card-head"><i class="fas fa-calendar-alt"></i><div><h4>' + text('adjustCapacity', 'Adjust Capacity') + '</h4><p>' + getAdjustmentDescription(state) + '</p></div></div><div class="ttbm-capacity-card-body"><div class="ttbm-capacity-mode-tabs"><button type="button" class="ttbm-capacity-mode-tab ' + (state.mode === 'single' ? 'active' : '') + '" data-capacity-mode="single"><strong>' + text('singleDate', 'Single Date') + '</strong><div>' + text('singleDateSub', 'Override one specific day') + '</div></button><button type="button" class="ttbm-capacity-mode-tab ' + (state.mode === 'range' ? 'active' : '') + '" data-capacity-mode="range"><strong>' + text('dateRange', 'Date Range') + '</strong><div>' + text('dateRangeSub', 'Override a span of days') + '</div></button></div><div class="ttbm-capacity-grid ' + (state.mode === 'single' ? 'single' : '') + '">' + dateFields + timeField + '</div><div class="ttbm-capacity-adjust"><div class="ttbm-capacity-adjust-head"><strong>' + text('capacityAdjustment', 'Capacity Adjustment') + '</strong><span>' + (isSharedCapacityEnabled(state) ? text('sharedDefaultSeats', 'Shared Default') : text('defaultSeats', 'Default')) + ': <strong>' + defaultCap + '</strong> seats</span></div><div class="ttbm-capacity-adjust-grid"><div class="ttbm-capacity-action add"><strong>' + text('addSeats', 'Add Seats') + '</strong><div class="ttbm-capacity-stepper"><button type="button" data-stepper="decrease" data-target="' + state.mode + '-add">-</button><input type="number" min="0" value="' + draft.add + '" data-draft-input="' + state.mode + '-add"><button type="button" data-stepper="increase" data-target="' + state.mode + '-add">+</button></div></div><div class="ttbm-capacity-action reduce"><strong>' + text('reduceSeats', 'Reduce Seats') + '</strong><div class="ttbm-capacity-stepper"><button type="button" data-stepper="decrease" data-target="' + state.mode + '-reduce">-</button><input type="number" min="0" value="' + draft.reduce + '" data-draft-input="' + state.mode + '-reduce"><button type="button" data-stepper="increase" data-target="' + state.mode + '-reduce">+</button></div></div><div class="ttbm-capacity-action override"><strong>' + (state.mode === 'single' ? text('fixedOverride', 'Set Fixed Override') : text('fixedOverrideRange', 'Set Fixed Override (All Days in Range)')) + '</strong><input type="number" min="0" class="ttbm-capacity-override-input" value="' + draft.override + '" data-draft-input="' + state.mode + '-override"></div></div></div><div class="ttbm-capacity-preview"><span class="ttbm-capacity-note">' + text('preview', 'Preview') + '</span><span class="ttbm-capacity-preview-chip">' + (isSharedCapacityEnabled(state) ? text('sharedPoolLabel', 'Shared Pool') + ' via ' + state.selectedTicket : state.selectedTicket) + '</span><span class="ttbm-capacity-preview-chip">' + formatScopeLabel(state.mode === 'single' ? draft.date : draft.start, state.mode === 'single' ? draft.date : draft.end, draft.time) + '</span><span class="ttbm-capacity-preview-chip"><span class="ttbm-capacity-preview-old">' + defaultCap + '</span> &rarr; <span class="ttbm-capacity-preview-new ' + previewClass + '">' + previewCap + '</span> seats</span>' + (state.mode === 'range' ? '<span class="ttbm-capacity-preview-chip">' + affectedDays + ' ' + text('daysAffected', 'days affected') + '</span>' : '') + '</div></div><div class="ttbm-capacity-card-footer"><div>' + text('pendingChanges', 'Pending changes') + ': <strong>' + state.rules.length + '</strong></div><div class="ttbm-capacity-footer-actions"><button type="button" class="ttbm-capacity-btn" data-capacity-discard>' + text('discard', 'Discard') + '</button><button type="button" class="ttbm-capacity-btn primary" data-capacity-save>' + text('saveOverride', 'Save Override') + '</button></div></div></div>';
            }

            function renderLog() {
                if (!state.rules.length) {
                    return '<div class="ttbm-capacity-card"><div class="ttbm-capacity-card-head"><i class="fas fa-clipboard-list"></i><div><h4>' + text('overrideLog', 'Override Log') + '</h4><p>' + text('overrideLogSub', 'Saved capacity overrides for upcoming dates.') + '</p></div></div><div class="ttbm-capacity-card-body"><div class="ttbm-capacity-empty">' + text('noOverrides', 'No capacity overrides saved yet.') + '</div></div></div>';
                }
                return '<div class="ttbm-capacity-card"><div class="ttbm-capacity-card-head"><i class="fas fa-clipboard-list"></i><div><h4>' + text('overrideLog', 'Override Log') + '</h4><p>' + text('overrideLogSub', 'Saved capacity overrides for upcoming dates.') + '</p></div></div><div class="ttbm-capacity-card-body" style="padding:0;"><table class="ttbm-capacity-log"><thead><tr><th>' + text('logScope', 'Scope') + '</th><th>' + text('logTicket', 'Ticket Type') + '</th><th>' + (isSharedCapacityEnabled(state) ? text('logSharedDefault', 'Shared Default') : text('logDefault', 'Default')) + '</th><th>' + text('logOverride', 'Override') + '</th><th>' + text('logChange', 'Change') + '</th><th>' + text('logAction', 'Action') + '</th></tr></thead><tbody>' + state.rules.map(function (rule, index) {
                    var defaultCap = getDefaultCapacityForTicket(state, rule.ticket_type);
                    var finalCap = defaultCap;
                    if (rule.adjustment_mode === 'override') {
                        finalCap = rule.amount;
                    } else if (rule.adjustment_mode === 'add') {
                        finalCap = defaultCap + rule.amount;
                    } else {
                        finalCap = Math.max(0, defaultCap - rule.amount);
                    }
                    var diff = finalCap - defaultCap;
                    var diffClass = diff >= 0 ? 'up' : 'down';
                    var diffText = (diff > 0 ? '+' : '') + diff;
                    return '<tr><td>' + formatScopeLabel(rule.start, rule.end, rule.time || '') + '</td><td><span class="ttbm-capacity-type">' + (isSharedCapacityEnabled(state) ? text('sharedPoolLabel', 'Shared Pool') + ' via ' + rule.ticket_type : rule.ticket_type) + '</span></td><td>' + defaultCap + '</td><td>' + finalCap + '</td><td><span class="ttbm-capacity-change ' + diffClass + '">' + diffText + '</span></td><td><div class="ttbm-capacity-rule-actions"><button type="button" class="ttbm-capacity-btn" data-rule-edit="' + index + '">' + text('edit', 'Edit') + '</button><button type="button" class="ttbm-capacity-btn" data-rule-remove="' + index + '">' + text('remove', 'Remove') + '</button></div></td></tr>';
                }).join('') + '</tbody></table></div></div>';
            }

            function rerender() {
                syncStore();
                var body = root.querySelector('.ttbm-capacity-body');
                body.innerHTML = renderTicketSelector() + renderAdjustmentCard() + renderLog();
                initDatePickers(root, state, rerender);
            }

            root.addEventListener('click', function (event) {
                var button = event.target.closest('button');
                if (!button) {
                    return;
                }
                if (button.hasAttribute('data-ticket-name')) {
                    state.selectedTicket = button.getAttribute('data-ticket-name');
                    rerender();
                    return;
                }
                if (button.hasAttribute('data-capacity-mode')) {
                    state.mode = button.getAttribute('data-capacity-mode');
                    ensureValidDraftTime(state, state.mode);
                    rerender();
                    return;
                }
                if (button.hasAttribute('data-stepper')) {
                    var target = button.getAttribute('data-target');
                    var direction = button.getAttribute('data-stepper') === 'increase' ? 1 : -1;
                    var parts = target.split('-');
                    var draftState = parts[0] === 'single' ? state.single : state.range;
                    var field = parts[1];
                    draftState[field] = Math.max(0, (parseInt(draftState[field], 10) || 0) + direction);
                    rerender();
                    return;
                }
                if (button.hasAttribute('data-capacity-save')) {
                    var rule = draftToRule(state);
                    if (!rule || !rule.start || !rule.end || !rule.ticket_type) {
                        return;
                    }
                    state.rules = state.rules.filter(function (item) {
                        return item.id !== state.editingId;
                    });
                    delete rule.computed_capacity;
                    delete rule.change_kind;
                    state.rules.unshift(rule);
                    resetDraft(state);
                    rerender();
                    return;
                }
                if (button.hasAttribute('data-capacity-discard')) {
                    resetDraft(state);
                    rerender();
                    return;
                }
                if (button.hasAttribute('data-rule-remove')) {
                    state.rules.splice(parseInt(button.getAttribute('data-rule-remove'), 10), 1);
                    rerender();
                    return;
                }
                if (button.hasAttribute('data-rule-edit')) {
                    var ruleToEdit = state.rules[parseInt(button.getAttribute('data-rule-edit'), 10)];
                    if (!ruleToEdit) {
                        return;
                    }
                    state.selectedTicket = ruleToEdit.ticket_type;
                    state.mode = ruleToEdit.type === 'range' ? 'range' : 'single';
                    state.editingId = ruleToEdit.id;
                    var targetDraft = state.mode === 'single' ? state.single : state.range;
                    targetDraft.date = ruleToEdit.start;
                    targetDraft.start = ruleToEdit.start;
                    targetDraft.end = ruleToEdit.end;
                    targetDraft.time = normalizeTime(ruleToEdit.time || '');
                    targetDraft.add = 0;
                    targetDraft.reduce = 0;
                    targetDraft.override = '';
                    if (ruleToEdit.adjustment_mode === 'add') {
                        targetDraft.add = ruleToEdit.amount;
                    } else if (ruleToEdit.adjustment_mode === 'reduce') {
                        targetDraft.reduce = ruleToEdit.amount;
                    } else {
                        targetDraft.override = ruleToEdit.amount;
                    }
                    ensureValidDraftTime(state, state.mode);
                    rerender();
                }
            });

            root.addEventListener('input', function (event) {
                var input = event.target.closest('[data-draft-input]');
                if (!input) {
                    return;
                }
                var target = input.getAttribute('data-draft-input');
                var parts = target.split('-');
                var draftState = parts[0] === 'single' ? state.single : state.range;
                var field = parts[1];
                draftState[field] = input.value === '' ? '' : Math.max(0, parseInt(input.value, 10) || 0);
                rerender();
            });

            root.addEventListener('change', function (event) {
                var select = event.target.closest('[data-draft-select]');
                if (!select) {
                    var toggle = event.target.closest('input[name="ttbm_display_global_qty"]');
                    if (toggle) {
                        rerender();
                    }
                    return;
                }
                var target = select.getAttribute('data-draft-select');
                var parts = target.split('-');
                var draftState = parts[0] === 'single' ? state.single : state.range;
                draftState[parts[1]] = normalizeTime(select.value || '');
                rerender();
            });

            document.addEventListener('change', function (event) {
                if (event.target && event.target.matches('input[name="ttbm_display_global_qty"]')) {
                    rerender();
                }
                if (event.target && event.target.matches('.ttbm_settings_pricing input[name="ttbm_type"]')) {
                    rerender();
                }
            });

            document.addEventListener('input', function (event) {
                if (event.target && event.target.matches('input[name="ttbm_global_qty"]')) {
                    rerender();
                }
            });

            document.addEventListener('click', function (event) {
                var toggleUi = event.target && event.target.closest('.ttbm_settings_pricing [data-collapse-target="#ttbm_display_global_qty"]');
                var pricingType = event.target && event.target.closest('.ttbm_settings_pricing .ttbm-pricing-type');
                if (toggleUi || pricingType) {
                    setTimeout(rerender, 0);
                }
            });

            rerender();
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            if (window.TTBMCapacityOverrideBoot) {
                window.TTBMCapacityOverrideBoot();
            }
        });
    } else if (window.TTBMCapacityOverrideBoot) {
        window.TTBMCapacityOverrideBoot();
    }
}());
