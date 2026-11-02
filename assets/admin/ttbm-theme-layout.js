/**
 * Theme layout sidebar card: template + theme colour for the tour page.
 * Writes to the real form fields (ttbm_theme_file / ttbm_theme_color) and fires "change" so the
 * auto-save stores them; the colour is also applied to this admin screen straight away.
 */
(function ($) {
	'use strict';

	var $card = $('#ttbm-theme-layout');
	if (!$card.length) {
		return;
	}
	var $color = $card.find('input[name="ttbm_theme_color"]');
	var $custom = $card.find('.ttbm-tl__swatch--custom');
	var defaultHex = $card.attr('data-default');
	var root = document.documentElement;
	var vars = ['--color_theme', '--color_theme_rgb', '--color_theme_rbg', '--color_theme_ee', '--color_theme_cc', '--color_theme_aa', '--color_theme_88', '--color_theme_77'];

	function hexToRgb(hex) {
		hex = String(hex).replace('#', '');
		if (hex.length === 3) {
			hex = hex.replace(/(.)/g, '$1$1');
		}
		return parseInt(hex.substr(0, 2), 16) + ',' + parseInt(hex.substr(2, 2), 16) + ',' + parseInt(hex.substr(4, 2), 16);
	}

	function applyColor(hex, isDefault) {
		$card[0].style.setProperty('--ttbm-tl-accent', hex);
		var preview = document.getElementById('ttbm-ticket-preview');
		if (preview) {
			preview.style.setProperty('--ttbm-tp-accent', hex);
		}
		root.style.setProperty('--ttbm-ed-accent', hex);
		if (isDefault) {
			vars.forEach(function (v) { root.style.removeProperty(v); });
			return;
		}
		var rgb = hexToRgb(hex);
		root.style.setProperty('--color_theme', hex);
		root.style.setProperty('--color_theme_rgb', rgb);
		root.style.setProperty('--color_theme_rbg', rgb);
		root.style.setProperty('--color_theme_ee', hex + 'ee');
		root.style.setProperty('--color_theme_cc', hex + 'cc');
		root.style.setProperty('--color_theme_aa', hex + 'aa');
		root.style.setProperty('--color_theme_88', hex + '88');
		root.style.setProperty('--color_theme_77', hex + '77');
	}

	function setColor(value, hex) {
		var isDefault = value === '' || value.toLowerCase() === String(defaultHex).toLowerCase();
		if (isDefault) {
			value = '';
			hex = defaultHex;
		}
		$color.val(value).trigger('change');
		applyColor(hex, isDefault);
	}

	// Template: drives the existing hidden field (also used by the Template tab).
	function setTemplate(key) {
		$card.find('.ttbm-tl__tpl').each(function () {
			var on = $(this).attr('data-template') === key;
			$(this).toggleClass('is-active', on).attr('aria-checked', on ? 'true' : 'false');
		});
		$('.ttbm-template').each(function () {
			$(this).toggleClass('active', $(this).find('img').attr('data-ttbm-template') === key);
		});
	}

	$card.on('click', '.ttbm-tl__tpl', function () {
		var key = $(this).attr('data-template');
		setTemplate(key);
		$('input[name="ttbm_theme_file"]').val(key).trigger('change');
	});
	// Keep the card in step when the template is picked from the Template tab instead.
	$(document).on('change', 'input[name="ttbm_theme_file"]', function () {
		setTemplate($(this).val());
	});

	$card.on('click', '.ttbm-tl__swatch[data-hex]', function () {
		$card.find('.ttbm-tl__swatch').removeClass('is-active');
		$(this).addClass('is-active');
		$custom.css('background', '');
		setColor($(this).attr('data-color') || '', $(this).attr('data-hex'));
	});

	$custom.find('input[type="color"]').on('input change', function () {
		var hex = $(this).val();
		$card.find('.ttbm-tl__swatch').removeClass('is-active');
		$custom.addClass('is-active').css('background', hex);
		setColor(hex, hex);
	});

	// Apply a saved colour to the editor on load.
	var saved = $color.val();
	if (saved) {
		applyColor(saved, false);
	} else {
		applyColor(defaultHex, true);
	}
})(jQuery);
