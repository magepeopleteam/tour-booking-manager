/**
 * Frontend Ticket Preview (right sidebar card).
 *
 * Builds a read-only mock of the booking tickets from the live ticket rows (and the extra service rows, when
 * there are any) on the Pricing step, and refreshes whenever a row is edited, added, removed or reordered.
 * The quantity buttons only change the preview; nothing here is saved. Whatever has a quantity above zero is
 * listed in a short breakdown above the total.
 */
(function ($) {
	'use strict';

	var $card = $('#ttbm-ticket-preview');
	if (!$card.length) {
		return;
	}
	var $body = $card.find('.ttbm-tp__body');
	var $footer = $card.find('.ttbm-tp__footer');
	var symbol = $card.attr('data-currency') || '$';
	var qtys = {t: {}, s: {}};
	var timer = null;

	function num(value) {
		var n = parseFloat(String(value || '').replace(/[^0-9.\-]/g, ''));
		return isNaN(n) ? 0 : n;
	}

	function money(n) {
		return symbol + n.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
	}

	function collectTickets() {
		var rows = [];
		$('.ttbm-ticket-types-table .ttbm_insert_ticket_type > tr.ttbm-ticket-type-row').each(function () {
			var $r = $(this);
			var price = num($r.find('input[name="ticket_type_price[]"]').val());
			var sale = num($r.find('input[name="ticket_type_sale_price[]"]').val());
			var capRaw = $.trim($r.find('input[name="ticket_type_qty[]"]').val());
			var defRaw = $.trim($r.find('input[name="ticket_type_default_qty[]"]').val());
			rows.push({
				name: $.trim($r.find('input[name="ticket_type_name[]"]').val()),
				desc: $.trim($r.find('input[name="ticket_type_description[]"]').val()),
				price: price,
				sale: sale > 0 && sale < price ? sale : 0,
				cap: capRaw === '' ? null : Math.max(0, parseInt(capRaw, 10) || 0),
				def: Math.max(0, parseInt(defRaw, 10) || 0)
			});
		});
		return rows;
	}

	function collectServices() {
		var rows = [];
		$('.ttbm-extra-service-table .ttbm_insert_extra_service > tr').each(function () {
			var $r = $(this);
			var name = $.trim($r.find('input[name="service_name[]"]').val());
			if (!name) {
				return;
			}
			var price = num($r.find('input[name="service_price[]"]').val());
			var sale = num($r.find('input[name="service_sale_price[]"]').val());
			var capRaw = $.trim($r.find('input[name="service_qty[]"]').val());
			rows.push({
				name: name,
				desc: $.trim($r.find('input[name="extra_service_description[]"]').val()),
				price: price,
				sale: sale > 0 && sale < price ? sale : 0,
				cap: capRaw === '' ? null : Math.max(0, parseInt(capRaw, 10) || 0),
				def: 0
			});
		});
		return rows;
	}

	function buildItem(kind, i, t, qty, max) {
		var avail = t.cap === null
			? $card.attr('data-available-text')
			: t.cap + ' ' + $card.attr('data-left-text');
		var $price = $('<span class="ttbm-tp__price"></span>')
			.append($('<span class="ttbm-tp__price-now"></span>').text(money(t.sale || t.price)));
		if (t.sale) {
			$price.append($('<s class="ttbm-tp__price-was"></s>').text(money(t.price)));
		}
		var $info = $('<div class="ttbm-tp__info"></div>')
			.append($('<span class="ttbm-tp__name"></span>').text(t.name || '\u2026'))
			.append($price)
			.append($('<span class="ttbm-tp__avail"></span>').append('<span class="ttbm-tp__avail-dot" aria-hidden="true"></span>').append(document.createTextNode(avail)));
		var $qty = $('<div class="ttbm-tp__qty"></div>')
			.append($('<button type="button" class="ttbm-tp__qty-btn" data-k="' + kind + '" data-i="' + i + '" data-d="-1" aria-label="-"></button>').text('\u2212').prop('disabled', qty <= 0))
			.append($('<span class="ttbm-tp__qty-val"></span>').text(qty))
			.append($('<button type="button" class="ttbm-tp__qty-btn" data-k="' + kind + '" data-i="' + i + '" data-d="1" aria-label="+"></button>').text('+').prop('disabled', qty >= max));
		return $('<div class="ttbm-tp__item"></div>').append($info).append($qty);
	}

	function render() {
		var tickets = collectTickets();
		var services = collectServices();
		$body.empty();
		$footer.empty();
		if (!tickets.length && !services.length) {
			$body.append($('<p class="ttbm-tp__empty"></p>').text($card.attr('data-empty-text')));
			return;
		}
		var total = 0;
		var lines = [];

		$.each(tickets, function (i, t) {
			if (typeof qtys.t[i] === 'undefined') {
				qtys.t[i] = t.def;
			}
			var max = t.cap === null ? 99 : t.cap;
			qtys.t[i] = Math.min(qtys.t[i], max);
			var unit = t.sale || t.price;
			total += unit * qtys.t[i];
			if (qtys.t[i] > 0) {
				lines.push({name: t.name, qty: qtys.t[i], amount: unit * qtys.t[i]});
			}
			$body.append(buildItem('t', i, t, qtys.t[i], max));
		});

		// Extra services only make sense once at least one ticket is selected; until then they stay hidden
		// and any quantity picked earlier is cleared, so it cannot linger in the total.
		var ticketCount = 0;
		$.each(tickets, function (i) {
			ticketCount += parseInt(qtys.t[i], 10) || 0;
		});
		if (!ticketCount) {
			qtys.s = {};
		}

		if (services.length && ticketCount > 0) {
			$body.append($('<div class="ttbm-tp__group"></div>').text($card.attr('data-extra-text')));
			$.each(services, function (i, t) {
				if (typeof qtys.s[i] === 'undefined') {
					qtys.s[i] = 0;
				}
				var max = t.cap === null ? 99 : t.cap;
				qtys.s[i] = Math.min(qtys.s[i], max);
				var unit = t.sale || t.price;
				total += unit * qtys.s[i];
				if (qtys.s[i] > 0) {
					lines.push({name: t.name, qty: qtys.s[i], amount: unit * qtys.s[i]});
				}
				$body.append(buildItem('s', i, t, qtys.s[i], max));
			});
		}

		if (lines.length) {
			var $lines = $('<div class="ttbm-tp__lines"></div>');
			$.each(lines, function (k, l) {
				$lines.append(
					$('<div class="ttbm-tp__line"></div>')
						.append($('<span></span>').text(l.name + ' \u00d7 ' + l.qty))
						.append($('<span></span>').text(money(l.amount)))
				);
			});
			$footer.append($lines);
		}
		$footer
			.append(
				$('<div class="ttbm-tp__total"></div>')
					.append($('<span></span>').text($card.attr('data-total-text')))
					.append($('<strong></strong>').text(money(total)))
			)
			.append($('<button type="button" class="ttbm-tp__book" tabindex="-1"></button>').text($card.attr('data-book-text')));
	}

	function schedule() {
		clearTimeout(timer);
		timer = setTimeout(render, 120);
	}

	$card.on('click', '.ttbm-tp__qty-btn', function (e) {
		e.preventDefault();
		var k = $(this).attr('data-k');
		var i = parseInt($(this).attr('data-i'), 10);
		qtys[k][i] = Math.max(0, (qtys[k][i] || 0) + parseInt($(this).attr('data-d'), 10));
		render();
	});

	$(document).on('input change keyup', '.ttbm-ticket-types-table input, .ttbm-ticket-types-table select, .ttbm-extra-service-table input, .ttbm-extra-service-table select', schedule);
	$(document).on('click', '.ttbm_item_remove, .ttbm_add_item, .ttbm-wz-step', schedule);
	$(document).on('sortupdate sortstop', schedule);

	['.ttbm-ticket-types-table .ttbm_insert_ticket_type', '.ttbm-extra-service-table .ttbm_insert_extra_service'].forEach(function (sel) {
		var node = document.querySelector(sel);
		if (node && window.MutationObserver) {
			new MutationObserver(schedule).observe(node, {childList: true});
		}
	});

	$(render);
	$(window).on('load', function () {
		setTimeout(render, 300);
	});
})(jQuery);

/**
 * Ticket types table: grouped column headings.
 *
 * Adds a first heading row (Ticket / Pricing / Inventory / Display) above the column headings. It reads
 * the visible <th> cells, so columns added by add-ons through the table hooks simply get an unlabelled
 * stretch of the rule instead of breaking the layout.
 */
(function ($) {
	'use strict';

	var $table = $('.ttbm-ticket-types-table').first();
	if (!$table.length) {
		return;
	}
	var labels = {};
	try {
		labels = JSON.parse($table.attr('data-groups') || '{}');
	} catch (e) {
		labels = {};
	}
	var $head = $table.children('thead').first();
	var $cells = $head.find('tr').first().children('th:visible');
	if (!$head.length || !$cells.length || $head.find('.ttbm-ticket-group-row').length) {
		return;
	}

	function groupOf($th) {
		if ($th.is('.ttbm-ticket-col--name, .ttbm-ticket-col--desc')) {
			return 'ticket';
		}
		if ($th.is('.ttbm-ticket-col--price, .ttbm-ticket-col--sale')) {
			return 'pricing';
		}
		if ($th.is('.ttbm-ticket-col--cap, .ttbm-ticket-col--default, .ttbm-ticket-col--reserve')) {
			return 'inventory';
		}
		if ($th.is('.ttbm-ticket-col--qty-type')) {
			return 'display';
		}
		return '';
	}

	var $row = $('<tr class="ttbm-ticket-group-row"></tr>');
	var last = null;
	var $current = null;
	$cells.each(function () {
		var g = groupOf($(this));
		if ($current && g === last && g !== '') {
			$current.attr('colspan', (parseInt($current.attr('colspan'), 10) || 1) + 1);
			return;
		}
		$current = $('<th colspan="1"></th>').text(g ? (labels[g] || '') : '');
		if (!g) {
			$current.addClass('ttbm-ticket-group--blank');
		}
		$row.append($current);
		last = g;
	});
	$head.prepend($row);
})(jQuery);
