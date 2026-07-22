/**
 * Global admin toast notification utility — available on every TTBM admin
 * screen (see TTBM_Dependencies::admin_script()). Any admin JS in this
 * plugin (or Pro) can call window.ttbmToast(message, type, duration) once
 * this script has loaded; no per-feature toast markup/CSS needed.
 */
(function ($) {
	'use strict';

	function ttbmToast(message, type, duration) {
		type = type || 'info';
		duration = duration || 3500;

		var $wrap = $('#ttbm-admin-toast-wrap');
		if (!$wrap.length) {
			$wrap = $('<div id="ttbm-admin-toast-wrap" class="ttbm-admin-toast-wrap"></div>');
			$('body').append($wrap);
		}

		var $item = $('<div class="ttbm-admin-toast ttbm-admin-toast-' + type + '"></div>');
		var $icon = $('<span class="ttbm-admin-toast-icon dashicons ' + iconFor(type) + '"></span>');
		var $content = $('<span class="ttbm-admin-toast-content"></span>').text(message);
		var $close = $('<button type="button" class="ttbm-admin-toast-close" aria-label="Dismiss">&times;</button>');

		$item.append($icon, $content, $close);
		$wrap.append($item);

		// Force reflow so the slide-in transition actually plays.
		void $item[0].offsetWidth;
		$item.addClass('is-visible');

		var timer = setTimeout(function () { dismiss(); }, duration);

		function dismiss() {
			clearTimeout(timer);
			$item.removeClass('is-visible');
			setTimeout(function () { $item.remove(); }, 220);
		}

		$close.on('click', dismiss);
	}

	function iconFor(type) {
		switch (type) {
			case 'success': return 'dashicons-yes-alt';
			case 'error': return 'dashicons-warning';
			case 'warning': return 'dashicons-flag';
			default: return 'dashicons-info';
		}
	}

	window.ttbmToast = ttbmToast;
})(jQuery);
