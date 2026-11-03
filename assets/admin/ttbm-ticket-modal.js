/**
 * Ticket types: card list + popup editor.
 *
 * The ticket table (.ttbm-ticket-types-table) stays in the page, hidden, and remains the single source of
 * truth: its inputs are what WordPress saves, what the autosave and the live preview read, and what the
 * existing add / remove / import / sort scripts act on. This script only puts a nicer face on it:
 *  - every filled row is shown as a card (name, summary, price, Edit, Delete),
 *  - "Add ticket type" and "Edit" open one modal whose fields are copied from / written back to the row,
 *  - cards can be dragged to reorder (the rows are reordered to match).
 * The same card list + popup is used for the Extra Service rows (KINDS below).
 * Strings come from ttbmTicketModal (wp_localize_script); the extra-service ones are prefixed "x_".
 */
(function ($) {
	'use strict';

	var L = window.ttbmTicketModal || {};

	var KINDS = [
		{
			id: 'ticket', glyph: 'ticket', prefix: '',
			area: '.ttbm-ticket-types-area', tbody: '.ttbm_insert_ticket_type', row: 'tr.ttbm-ticket-type-row',
			panel: '.ttbm-ticket-types-panel', add: '.ttbm-ticket-types-add-btn', icon: 'ticket_type_icon[]',
			required: ['name', 'price', 'cap'],
			fields: {
				name: 'ticket_type_name[]', desc: 'ticket_type_description[]', price: 'ticket_type_price[]',
				sale: 'ticket_type_sale_price[]', cap: 'ticket_type_qty[]', def: 'ticket_type_default_qty[]',
				resv: 'ticket_type_resv_qty[]', type: 'ticket_type_qty_type[]'
			}
		},
		{
			id: 'extra', glyph: 'gift', prefix: 'x_',
			area: '.ttbm-extra-service-area', tbody: '.ttbm_insert_extra_service', row: 'tr.ttbm-extra-service-row',
			panel: '.ttbm-extra-service-panel', add: '.ttbm-ticket-types-add-btn', icon: 'service_icon[]',
			required: ['name', 'price'],
			fields: {
				name: 'service_name[]', desc: 'extra_service_description[]', price: 'service_price[]',
				sale: 'service_sale_price[]', cap: 'service_qty[]', type: 'service_qty_type[]'
			}
		}
	];

	KINDS.forEach(mount);

	function mount(cfg) {
	var $areas = $(cfg.area);
	if (!$areas.length) {
		return;
	}
	var FIELD = cfg.fields;
	var PFX = 'ttbm-tkm-' + cfg.id + '-';

	function t(key, fallback) {
		return L[cfg.prefix + key] || L[key] || fallback;
	}

	function $f($tr, key) {
		return $tr.find('[name="' + FIELD[key] + '"]').first();
	}

	function read($tr) {
		var out = {};
		$.each(FIELD, function (key) {
			out[key] = $.trim($f($tr, key).val() || '');
		});
		if (!out.type) {
			out.type = 'inputbox';
		}
		return out;
	}

	function isBlank($tr) {
		var d = read($tr);
		return d.name === '' && d.price === '';
	}

	function num(v) {
		var n = parseFloat(String(v || '').replace(/[^0-9.\-]/g, ''));
		return isNaN(n) ? 0 : n;
	}

	function svg(path, size, width) {
		return '<svg viewBox="0 0 24 24" width="' + size + '" height="' + size + '" fill="none" stroke="currentColor" stroke-width="' + (width || 2) + '" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + path + '</svg>';
	}

	var ICON = {
		gift: '<path d="M20 12v8H4v-8M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 110-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 100-5C13 2 12 7 12 7z"/>',
		ticket: '<path d="M3 9a2 2 0 002-2h14a2 2 0 002 2v0a2 2 0 000 6v0a2 2 0 00-2 2H5a2 2 0 00-2-2v0a2 2 0 000-6z"/>',
		plus: '<path d="M12 5v14M5 12h14"/>',
		edit: '<path d="M4 20h4L19 9l-4-4L4 16v4zM13.5 6.5l4 4"/>',
		trash: '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
		close: '<path d="M6 6l12 12M18 6L6 18"/>',
		check: '<path d="M5 12l5 5 9-10"/>',
		grip: '<circle cx="9" cy="6" r="1.6" fill="currentColor"/><circle cx="15" cy="6" r="1.6" fill="currentColor"/><circle cx="9" cy="12" r="1.6" fill="currentColor"/><circle cx="15" cy="12" r="1.6" fill="currentColor"/><circle cx="9" cy="18" r="1.6" fill="currentColor"/><circle cx="15" cy="18" r="1.6" fill="currentColor"/>'
	};

	/* ---------------------------------------------------------------- the one shared modal */
	var $modal = null;
	var ctx = null; // {inst, $tr, isNew}
	var iconTimer = null;

	function bodyHtml() {
		var h = '';
		h += '<div class="ttbm-tkm__field"><label for="' + PFX + 'name">' + t('name', 'Ticket name') + ' <b>*</b></label>' +
			'<div class="ttbm-tkm__name-row"><span class="ttbm-tkm__icon-wrap"><button type="button" class="ttbm-tkm__icon-btn" data-tkm="icon" aria-label="' + t('icon', 'Choose icon') + '"></button>' +
			'<button type="button" class="ttbm-tkm__icon-clear" data-tkm="icon-clear" aria-label="' + t('iconClear', 'Remove icon') + '" hidden>' + svg(ICON.close, 10, 3) + '</button></span>' +
			'<input id="' + PFX + 'name" type="text" data-f="name" placeholder="' + t('namePh', 'Ex: Adult') + '"></div></div>';
		h += '<div class="ttbm-tkm__field"><label for="' + PFX + 'desc">' + t('desc', 'Short description') + '</label>' +
			'<input id="' + PFX + 'desc" type="text" data-f="desc" placeholder="' + t('descPh', 'Ex: Regular') + '"></div>';
		h += '<div class="ttbm-tkm__grid ttbm-tkm__grid--2">' +
			'<div class="ttbm-tkm__field"><label for="' + PFX + 'price">' + t('price', 'Regular price') + ' <b>*</b></label>' +
			'<span class="ttbm-tkm__money"><span class="ttbm-tkm__sym"></span><input id="' + PFX + 'price" type="text" inputmode="decimal" data-f="price" placeholder="0.00"></span></div>' +
			'<div class="ttbm-tkm__field"><label for="' + PFX + 'sale">' + t('sale', 'Sale price') + '</label>' +
			'<span class="ttbm-tkm__money"><span class="ttbm-tkm__sym"></span><input id="' + PFX + 'sale" type="text" inputmode="decimal" data-f="sale" placeholder="0.00"></span></div>' +
			'</div>' +
			'<p class="ttbm-tkm__hint">' + t('saleHint', 'A sale price shows the regular price struck through.') + '</p>';
		if (cfg.id === 'ticket') {
			h += '<div class="ttbm-tkm__grid ttbm-tkm__grid--3">' +
				'<div class="ttbm-tkm__field"><label for="' + PFX + 'cap">' + t('cap', 'Capacity') + ' <b>*</b></label><input id="' + PFX + 'cap" type="number" min="0" step="1" data-f="cap" placeholder="500"></div>' +
				'<div class="ttbm-tkm__field"><label for="' + PFX + 'def">' + t('def', 'Default quantity') + '</label><input id="' + PFX + 'def" type="number" min="0" step="1" data-f="def" placeholder="1"></div>' +
				'<div class="ttbm-tkm__field"><label for="' + PFX + 'resv">' + t('resv', 'Reserved') + '</label><input id="' + PFX + 'resv" type="number" min="0" step="1" data-f="resv" placeholder="0"></div>' +
				'</div>';
		} else {
			h += '<div class="ttbm-tkm__field"><label for="' + PFX + 'cap">' + t('cap', 'Available quantity') + '</label><input id="' + PFX + 'cap" type="number" min="0" step="1" data-f="cap" placeholder="' + t('capPh', 'Ex: 100') + '"></div>';
		}
		h += '<div class="ttbm-tkm__field"><span class="ttbm-tkm__label">' + t('qtyType', 'Quantity box type') + '</span>' +
			'<div class="ttbm-tkm__seg" role="group" aria-label="' + t('qtyType', 'Quantity box type') + '">' +
			'<button type="button" data-tkm="type" data-val="inputbox">' + t('inputbox', 'Input box') + '</button>' +
			'<button type="button" data-tkm="type" data-val="dropdown">' + t('dropdown', 'Dropdown list') + '</button>' +
			'</div></div>';
		return h;
	}

	function buildModal() {
		var html =
			'<div class="ttbm-tkm" hidden>' +
			'<button type="button" class="ttbm-tkm__backdrop" data-tkm="close" aria-label="' + t('close', 'Close') + '"></button>' +
			'<div class="ttbm-tkm__dialog" role="dialog" aria-modal="true" aria-labelledby="' + PFX + 'title">' +
			'<div class="ttbm-tkm__head">' +
			'<span class="ttbm-tkm__head-icon">' + svg(ICON[cfg.glyph], 20) + '</span>' +
			'<div class="ttbm-tkm__head-text"><h3 id="' + PFX + 'title"></h3><span>' + t('subtitle', 'Guests pick this ticket when they book.') + '</span></div>' +
			'<button type="button" class="ttbm-tkm__x" data-tkm="close" aria-label="' + t('close', 'Close') + '">' + svg(ICON.close, 16, 2.4) + '</button>' +
			'</div>' +
			'<div class="ttbm-tkm__body">' + bodyHtml() +
			'<div class="ttbm-tkm__error" role="alert" hidden></div>' +
			'</div>' +
			'<div class="ttbm-tkm__foot">' +
			'<button type="button" class="ttbm-tkm__btn" data-tkm="close">' + t('cancel', 'Cancel') + '</button>' +
			'<button type="button" class="ttbm-tkm__btn ttbm-tkm__btn--primary" data-tkm="save">' + svg(ICON.check, 16, 2.6) + '<span class="ttbm-tkm__save-label"></span></button>' +
			'</div>' +
			'</div></div>';
		$modal = $(html).addClass('ttbm-tkm--' + cfg.id).appendTo(document.body);

		$modal.on('click', '[data-tkm="close"]', function () {
			closeModal(true);
		});
		$modal.on('click', '[data-tkm="save"]', save);
		$modal.on('click', '[data-tkm="type"]', function () {
			setType($(this).attr('data-val'));
		});
		$modal.on('click', '[data-tkm="icon"]', pickIcon);
		$modal.on('click', '[data-tkm="icon-clear"]', function () {
			if (ctx) {
				ctx.$tr.find('.ttbm_icon_remove').first().trigger('click');
				setTimeout(syncIcon, 50);
			}
		});
		$modal.on('input', '[data-f]', function () {
			showError('');
		});
		$modal.on('keydown', function (e) {
			if (e.key === 'Escape') {
				e.stopPropagation();
				closeModal(true);
			} else if (e.key === 'Enter' && $(e.target).is('input')) {
				e.preventDefault();
				save();
			}
		});
	}

	function setType(val) {
		$modal.find('[data-tkm="type"]').each(function () {
			var on = $(this).attr('data-val') === val;
			$(this).toggleClass('is-on', on).attr('aria-pressed', on ? 'true' : 'false');
		});
		$modal.data('type', val);
	}

	function showError(msg) {
		var $e = $modal.find('.ttbm-tkm__error');
		if (msg) {
			$e.text(msg).prop('hidden', false);
		} else {
			$e.prop('hidden', true).text('');
		}
	}

	/** Mirror the row's icon (kept in the row's own hidden icon widget) onto the modal's icon button. */
	function syncIcon() {
		if (!ctx || !$modal) {
			return;
		}
		var $row = ctx.$tr;
		var val = $.trim($row.find('input[name="' + cfg.icon + '"]').val() || '');
		var cls = $.trim($row.find('[data-add-icon]').attr('class') || '');
		var $btn = $modal.find('.ttbm-tkm__icon-btn');
		if (val && cls) {
			$btn.empty().append($('<span/>').addClass(cls)).addClass('has-icon');
			$modal.find('.ttbm-tkm__icon-clear').prop('hidden', false);
		} else {
			$btn.html(svg(ICON.plus, 16, 2.4)).removeClass('has-icon');
			$modal.find('.ttbm-tkm__icon-clear').prop('hidden', true);
		}
	}

	/** The icon chooser is the plugin's own popup; hide our modal while it is open, then bring it back. */
	function pickIcon() {
		if (!ctx) {
			return;
		}
		var $row = ctx.$tr;
		$modal.addClass('is-yielding');
		$row.find('.ttbm_icon_add').first().trigger('click');
		clearInterval(iconTimer);
		var waited = 0;
		iconTimer = setInterval(function () {
			waited += 200;
			var open = $('.ttbm_add_icon_popup.in').length > 0;
			if (!open && waited > 400) {
				clearInterval(iconTimer);
				$modal.removeClass('is-yielding');
				syncIcon();
				$modal.find('[data-f="name"]').trigger('focus');
			}
		}, 200);
	}

	function openModal(inst, $tr, isNew) {
		if (!$modal) {
			buildModal();
		}
		// A new ticket is edited on a detached copy of the row template; it joins the table (and so the
		// live preview) only when "Save & close" is clicked.
		if (!$tr) {
			$tr = inst.newRow();
			isNew = true;
		}
		ctx = {inst: inst, $tr: $tr, isNew: !!isNew};
		var d = read($tr);
		$modal.find('[data-f]').each(function () {
			var key = $(this).attr('data-f');
			var v = d[key];
			if (key === 'def' && v === '' && isNew) {
				v = '1';
			}
			$(this).val(v);
		});
		$modal.find('.ttbm-tkm__sym').text(inst.symbol());
		$modal.find('h3').text(isNew ? t('titleNew', 'Add ticket type') : t('titleEdit', 'Edit ticket type'));
		$modal.find('.ttbm-tkm__save-label').text(t('saveClose', 'Save & close'));
		setType(d.type === 'dropdown' ? 'dropdown' : 'inputbox');
		showError('');
		syncIcon();
		$modal.removeClass('is-yielding').prop('hidden', false);
		$('body').addClass('ttbm-tkm-open');
		setTimeout(function () {
			$modal.find('[data-f="name"]').trigger('focus');
		}, 30);
	}

	function closeModal(cancelled) {
		if (!$modal || $modal.prop('hidden')) {
			return;
		}
		clearInterval(iconTimer);
		if (cancelled && ctx && ctx.isNew && $.contains(document.documentElement, ctx.$tr[0]) && ctx.inst.rows().length > 1 && isBlank(ctx.$tr)) {
			ctx.$tr.remove(); // fallback path only (no row template): drop the blank row that was added
		}
		$modal.prop('hidden', true).removeClass('is-yielding');
		$('body').removeClass('ttbm-tkm-open');
		var inst2 = ctx && ctx.inst;
		var $focus = ctx && ctx.isNew ? inst2 && inst2.$addBtn : null;
		ctx = null;
		if (inst2) {
			inst2.render();
			if ($focus) {
				$focus.trigger('focus');
			}
		}
	}

	function save() {
		if (!ctx) {
			return;
		}
		var vals = {};
		$modal.find('[data-f]').each(function () {
			vals[$(this).attr('data-f')] = $.trim($(this).val());
		});
		vals.type = $modal.data('type') || 'inputbox';
		var $first;
		function fail(msg, key) {
			showError(msg);
			$first = $modal.find('[data-f="' + key + '"]');
			$first.trigger('focus');
		}
		var req = cfg.required;
		if (req.indexOf('name') > -1 && !vals.name) {
			return fail(t('errName', 'Enter a name.'), 'name');
		}
		if (req.indexOf('price') > -1 && (vals.price === '' || isNaN(num(vals.price)))) {
			return fail(t('errPrice', 'Enter a regular price.'), 'price');
		}
		if (req.indexOf('cap') > -1 && vals.cap === '') {
			return fail(t('errCap', 'Enter how many seats this ticket has.'), 'cap');
		}
		if (vals.sale !== '' && num(vals.sale) >= num(vals.price)) {
			return fail(t('errSale', 'The sale price must be lower than the regular price.'), 'sale');
		}
		var $tr = ctx.$tr;
		if (!$.contains(document.documentElement, $tr[0])) {
			ctx.inst.attach($tr);
		}
		$.each(FIELD, function (key) {
			var $input = $f($tr, key);
			if (!$input.length) {
				return;
			}
			$input.prop('disabled', false).val(vals[key]).trigger('input').trigger('change');
		});
		ctx.isNew = false;
		closeModal(false);
	}

	/* ---------------------------------------------------------------- one instance per ticket area */
	function Instance($area) {
		var self = this;
		this.$area = $area;
		this.$tbody = $area.find(cfg.tbody).first();
		this.$addBtn = $area.find(cfg.add).first();
		if (!this.$tbody.length || !this.$addBtn.length) {
			return;
		}
		this.$ui = $(
			'<div class="ttbm-tk ttbm-tk--' + cfg.id + '">' +
			'<div class="ttbm-tk__head"><h4 class="ttbm-tk__title">' + t('listTitle', 'Ticket types') + ' <span class="ttbm-tk__count">0</span></h4>' +
			'</div>' +
			'<div class="ttbm-tk__list"></div>' +
			'<div class="ttbm-tk__empty" hidden><strong>' + t('emptyTitle', 'No ticket types yet') + '</strong><span>' + t('emptyText', 'Add your first ticket, like Adult or Child.') + '</span></div>' +
			'<div class="ttbm-tk__foot"><button type="button" class="ttbm-tk__add">' + svg(ICON.plus, 15, 2.6) + '<span>' + t('titleNew', 'Add ticket type') + '</span></button></div>' +
			'</div>'
		);
		this.$list = this.$ui.find('.ttbm-tk__list');
		this.$empty = this.$ui.find('.ttbm-tk__empty');
		this.$count = this.$ui.find('.ttbm-tk__count');
		var $panel = $area.find(cfg.panel).first();
		($panel.length ? $panel : $area).before(this.$ui);
		$area.addClass('ttbm-tk-on');

		this.$ui.on('click', '.ttbm-tk__add', function () {
			self.add();
		});
		this.$list.on('click', '[data-tk="edit"]', function () {
			var $tr = self.rows().eq(parseInt($(this).closest('.ttbm-tk-card').attr('data-i'), 10));
			if ($tr.length) {
				openModal(self, $tr, false);
			}
		});
		this.$list.on('click', '[data-tk="del"]', function () {
			var $tr = self.rows().eq(parseInt($(this).closest('.ttbm-tk-card').attr('data-i'), 10));
			if ($tr.length) {
				$tr.find('.ttbm_item_remove').first().trigger('click');
				setTimeout(function () {
					self.render();
				}, 350);
			}
		});

		if ($.fn.sortable) {
			this.$list.sortable({
				handle: '.ttbm-tk-card__sort',
				items: '> .ttbm-tk-card',
				axis: 'y',
				update: function () {
					var rows = self.rows().get();
					var order = self.$list.children('.ttbm-tk-card').map(function () {
						return parseInt($(this).attr('data-i'), 10);
					}).get();
					$.each(order, function (i, idx) {
						self.$tbody.append(rows[idx]);
					});
					$(rows[order[0]]).find('input').first().trigger('change');
					self.render();
				}
			});
		}

		// Rows added / removed / replaced by the existing scripts (import, reset, ...).
		if (window.MutationObserver) {
			new MutationObserver(function () {
				clearTimeout(self.timer);
				self.timer = setTimeout(function () {
					if (!ctx) {
						self.render();
					}
				}, 60);
			}).observe(this.$tbody[0], {childList: true});
		}
		this.render();
	}

	Instance.prototype.rows = function () {
		return this.$tbody.children(cfg.row);
	};

	Instance.prototype.symbol = function () {
		var s = $.trim(this.$tbody.find('.ttbm-ticket-input-prefix__symbol').first().text());
		if (!s) {
			s = $('#ttbm-ticket-preview').attr('data-currency') || '$';
		}
		return s;
	};

	/** A fresh, detached ticket row cloned from the area's hidden row template (null when there is none). */
	Instance.prototype.newRow = function () {
		var $tpl = this.$area.find('.ttbm_hidden_content').first().find('.ttbm_hidden_item').children('tr').first();
		if (!$tpl.length) {
			return null;
		}
		var $tr = $tpl.clone();
		$tr.find('input, select, textarea').prop('disabled', false);
		return $tr;
	};

	/** Put a saved new row into the table, the way the existing "add item" script would. */
	Instance.prototype.attach = function ($tr) {
		var uid = new Date().getTime();
		$tr.find('[data-input-text]').attr('data-input-text', uid);
		$tr.find('[name="ttbm_hidden_ticket_text[]"]').val(uid);
		this.rows().filter(function () {
			return isBlank($(this));
		}).remove(); // the untouched starter row is replaced by the new ticket
		this.$tbody.append($tr);
		if ($.fn.sortable && this.$tbody.hasClass('ui-sortable')) {
			this.$tbody.sortable('refresh');
		}
	};

	Instance.prototype.add = function () {
		if (this.newRow()) {
			openModal(this, null, true);
			return;
		}
		var $blank = this.rows().filter(function () {
			return isBlank($(this));
		}).first();
		if ($blank.length) {
			openModal(this, $blank, true);
			return;
		}
		this.$addBtn.trigger('click'); // the existing handler appends a fresh row from its template
		var $new = this.rows().last();
		if ($new.length) {
			openModal(this, $new, true);
		}
	};

	Instance.prototype.render = function () {
		var self = this;
		var sym = this.symbol();
		var shown = 0;
		this.$list.empty();
		this.rows().each(function (i) {
			var $tr = $(this);
			if (isBlank($tr)) {
				return;
			}
			shown++;
			var d = read($tr);
			var price = num(d.price);
			var sale = num(d.sale);
			var hasSale = d.sale !== '' && sale > 0 && sale < price;
			var bits = [];
			if (d.desc) {
				bits.push(d.desc);
			}
			if (d.cap !== '') {
				bits.push(d.cap + ' ' + (cfg.id === 'ticket' ? t('seats', 'seats') : t('available', 'available')));
			}
			if (cfg.id === 'ticket') {
				bits.push(t('defaultShort', 'default') + ' ' + (d.def === '' ? '0' : d.def));
			}
			bits.push(d.type === 'dropdown' ? t('dropdownLc', 'dropdown list') : t('inputboxLc', 'input box'));

			var $card = $(
				'<div class="ttbm-tk-card" data-i="' + i + '">' +
				'<span class="ttbm-tk-card__sort" title="' + t('drag', 'Drag to reorder') + '">' + svg(ICON.grip, 16) + '</span>' +
				'<span class="ttbm-tk-card__icon">' + svg(ICON[cfg.glyph], 20) + '</span>' +
				'<div class="ttbm-tk-card__main"><strong class="ttbm-tk-card__name"></strong><span class="ttbm-tk-card__meta"></span></div>' +
				'<div class="ttbm-tk-card__price"><b class="ttbm-tk-card__now"></b><s class="ttbm-tk-card__was" hidden></s></div>' +
				'<span class="ttbm-tk-card__off" hidden></span>' +
				'<div class="ttbm-tk-card__actions">' +
				'<button type="button" class="ttbm-tk-card__btn" data-tk="edit" aria-label="' + t('edit', 'Edit') + '">' + svg(ICON.edit, 15) + '<span>' + t('edit', 'Edit') + '</span></button>' +
				'<button type="button" class="ttbm-tk-card__btn ttbm-tk-card__btn--del" data-tk="del" aria-label="' + t('remove', 'Remove ticket type') + '">' + svg(ICON.trash, 16) + '</button>' +
				'</div></div>'
			);
			$card.find('.ttbm-tk-card__name').text(d.name);
			$card.find('.ttbm-tk-card__meta').text(bits.join(' · '));
			$card.find('.ttbm-tk-card__now').text(sym + (hasSale ? d.sale : d.price));
			if (hasSale) {
				$card.find('.ttbm-tk-card__was').text(sym + d.price).prop('hidden', false);
				$card.find('.ttbm-tk-card__off').text(Math.round((1 - sale / price) * 100) + '% ' + t('off', 'off')).prop('hidden', false);
			}
			var iconCls = $.trim($tr.find('input[name="' + cfg.icon + '"]').val() || '') ? $.trim($tr.find('[data-add-icon]').attr('class') || '') : '';
			if (iconCls) {
				$card.find('.ttbm-tk-card__icon').empty().append($('<span/>').addClass(iconCls));
			}
			self.$list.append($card);
		});
		this.$count.text(shown);
		this.$empty.prop('hidden', shown > 0);
	};

	$areas.each(function () {
		new Instance($(this));
	});
	}
})(jQuery);
