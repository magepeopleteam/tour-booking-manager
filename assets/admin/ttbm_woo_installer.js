/**
 * TTBM WooCommerce Installer
 * Drives a server-directed, multi-step install/activate flow.
 * Each AJAX request performs ONE small unit of work (download → extract →
 * activate → setup) and returns the next step, keeping peak memory/time low.
 * Popup shows on every admin page when WooCommerce is not active.
 */
(function ($) {
	'use strict';

	var config    = window.ttbm_woo_installer || {};
	var $overlay  = null;
	var $popup    = null;
	var $btn      = null;
	var $progress = null;
	var $fill     = null;
	var $status   = null;
	var $actions  = null;
	var isWorking = false;

	$(document).ready(function () {
		$overlay  = $('#ttbm-woo-overlay');
		$popup    = $overlay.find('.ttbm-woo-popup');
		$btn      = $('#ttbm-woo-install-btn');
		$progress = $('#ttbm-woo-progress');
		$fill     = $('#ttbm-woo-progress-fill');
		$status   = $('#ttbm-woo-status-text');
		$actions  = $overlay.find('.ttbm-woo-actions');

		if (!$overlay.length) {
			return;
		}

		$btn.on('click', function (e) {
			e.preventDefault();
			if (isWorking) {
				return;
			}
			startProcess();
		});
	});

	function startProcess() {
		isWorking = true;
		$btn.prop('disabled', true);

		$actions.slideUp(250);
		$progress.slideDown(300);

		var firstStep = config.first_step || 'download';
		var firstText = firstStep === 'activate'
			? config.i18n.activating
			: config.i18n.downloading;

		setProgress(15, firstText);
		runStep(firstStep);
	}

	/**
	 * Run a single server step, then chain to whatever step the server returns
	 * until it reports 'done'.
	 */
	function runStep(step) {
		$.ajax({
			url:      config.ajax_url,
			type:     'POST',
			dataType: 'json',
			data: {
				action: 'ttbm_woo_step',
				nonce:  config.step_nonce,
				step:   step
			},
			success: function (response) {
				if (!response || !response.success || !response.data) {
					showError(stepError(step));
					return;
				}

				var data    = response.data;
				var percent = typeof data.percent === 'number' ? data.percent : null;
				if (percent !== null) {
					setProgress(percent, data.message || '');
				} else if (data.message) {
					$status.text(data.message);
				}

				if (data.next && data.next !== 'done') {
					runStep(data.next);
				} else {
					showSuccess();
				}
			},
			error: function () {
				showError(stepError(step));
			}
		});
	}

	function stepError(step) {
		if (step === 'activate' || step === 'setup') {
			return config.i18n.activate_error;
		}
		return config.i18n.install_error;
	}

	function setProgress(percent, text) {
		$fill.css('width', percent + '%');
		$status.text(text).removeClass('ttbm-success ttbm-error');
	}

	function showSuccess() {
		setProgress(100, config.i18n.success);
		$popup.addClass('ttbm-state-success');
		$status.addClass('ttbm-success');

		$popup.find('.ttbm-woo-icon').html(
			'<svg width="40" height="40" viewBox="0 0 24 24" fill="none">' +
			'<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>' +
			'<path d="M8 12l3 3 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
			'</svg>'
		);

		$popup.find('.ttbm-woo-title').text(config.i18n.success);
		$popup.find('.ttbm-woo-desc').text(config.i18n.redirecting);

		setTimeout(function () {
			window.location.href = config.redirect_url;
		}, 1500);
	}

	function showError(message) {
		isWorking = false;
		$popup.addClass('ttbm-state-error');
		$status.text(message).addClass('ttbm-error');
		$fill.css('width', '100%');

		$btn.prop('disabled', false);
		$actions.slideDown(250);

		setTimeout(function () {
			$popup.removeClass('ttbm-state-error');
			$progress.slideUp(250);
			$fill.css('width', '0%');
		}, 3000);
	}

})(jQuery);
