<?php
	/**
	 * Custom-payment checkout modal.
	 * Opened when Booking Mode is "Custom Payment" and the visitor clicks Book Now.
	 * Shows booking summary, billing details, payment methods, Cancel / Complete Registration.
	 */
	if (!defined('ABSPATH')) {
		die;
	}
	$gateways = TTBM_Payment_Gateway_Manager::get_instance()->get_available_gateways();
	$billing_name = '';
	$billing_email = '';
	$billing_phone = '';
	if (is_user_logged_in()) {
		$user = wp_get_current_user();
		$billing_name = $user->display_name;
		$billing_email = $user->user_email;
		$billing_phone = (string) get_user_meta($user->ID, 'billing_phone', true);
	}
?>
<div id="ttbm-custom-checkout-modal" class="ttbm-cc-modal-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="ttbm-cc-modal-title">
	<div class="ttbm-cc-modal-box">
		<div class="ttbm-cc-modal-header">
			<div class="ttbm-cc-modal-heading">
				<h3 id="ttbm-cc-modal-title" class="ttbm-cc-modal-title"><?php esc_html_e('Complete Registration', 'tour-booking-manager'); ?></h3>
				<p class="ttbm-cc-modal-subtitle"><?php esc_html_e('Review your booking and confirm your details.', 'tour-booking-manager'); ?></p>
			</div>
			<button type="button" class="ttbm-cc-modal-close" aria-label="<?php esc_attr_e('Close', 'tour-booking-manager'); ?>">&times;</button>
		</div>

		<div class="ttbm-cc-modal-body">
			<div class="ttbm-cc-modal-summary ttbm-cc-card">
				<h4 class="ttbm-cc-section-title"><?php esc_html_e('Booking Summary', 'tour-booking-manager'); ?></h4>
				<div class="ttbm-cc-tour-head">
					<span class="ttbm-cc-tour-name" id="ttbm-cc-tour-name"></span>
					<span class="ttbm-cc-tour-date" id="ttbm-cc-tour-date" style="display:none;"></span>
				</div>
				<div id="ttbm-cc-line-items"></div>
				<div id="ttbm-cc-guest-details" class="ttbm-cc-guest-details" style="display:none;"></div>
				<div class="ttbm-cc-total-row">
					<span><?php esc_html_e('Total', 'tour-booking-manager'); ?></span>
					<span id="ttbm-cc-total-display"></span>
				</div>
			</div>

			<div class="ttbm-cc-modal-side">
			<div class="ttbm-cc-modal-billing">
				<h4 class="ttbm-cc-section-title"><?php esc_html_e('Billing Details', 'tour-booking-manager'); ?></h4>
				<div class="ttbm-cc-field">
					<label for="ttbm-cc-billing-name"><?php esc_html_e('Full Name', 'tour-booking-manager'); ?> <span class="ttbm-cc-req">*</span></label>
					<input type="text" id="ttbm-cc-billing-name" class="ttbm-cc-input" name="ttbm_cc_billing_name" value="<?php echo esc_attr($billing_name); ?>" placeholder="<?php esc_attr_e('Jane Doe', 'tour-booking-manager'); ?>" autocomplete="name" required>
				</div>
				<div class="ttbm-cc-field">
					<label for="ttbm-cc-billing-email"><?php esc_html_e('Email Address', 'tour-booking-manager'); ?> <span class="ttbm-cc-req">*</span></label>
					<input type="email" id="ttbm-cc-billing-email" class="ttbm-cc-input" name="ttbm_cc_billing_email" value="<?php echo esc_attr($billing_email); ?>" placeholder="<?php esc_attr_e('you@example.com', 'tour-booking-manager'); ?>" autocomplete="email" required>
				</div>
				<div class="ttbm-cc-field">
					<label for="ttbm-cc-billing-phone"><?php esc_html_e('Phone', 'tour-booking-manager'); ?> <span class="ttbm-cc-req">*</span></label>
					<input type="tel" id="ttbm-cc-billing-phone" class="ttbm-cc-input" name="ttbm_cc_billing_phone" value="<?php echo esc_attr($billing_phone); ?>" placeholder="<?php esc_attr_e('+1 555 000 0000', 'tour-booking-manager'); ?>" autocomplete="tel" required>
				</div>
			</div>

			<?php if (!empty($gateways)) : ?>
				<div class="ttbm-cc-modal-payment">
					<h4 class="ttbm-cc-section-title"><?php esc_html_e('Payment Method', 'tour-booking-manager'); ?></h4>
					<div class="ttbm-cc-payment-options">
						<?php
							$first = true;
							foreach ($gateways as $gateway_id => $gateway) :
						?>
							<label class="ttbm-cc-payment-option">
								<input type="radio" name="ttbm_cc_payment_method" value="<?php echo esc_attr($gateway_id); ?>" <?php checked($first); ?>>
								<span class="ttbm-cc-pay-check" aria-hidden="true"></span>
								<span class="ttbm-cc-pay-name"><?php echo esc_html($gateway->get_title()); ?></span>
							</label>
						<?php
							$first = false;
							endforeach;
						?>
					</div>
				</div>
			<?php else : ?>
				<div class="ttbm-cc-offline-notice">
					<p><?php esc_html_e('Your booking will be submitted for review. The organizer will contact you regarding payment.', 'tour-booking-manager'); ?></p>
				</div>
				<input type="hidden" name="ttbm_cc_payment_method" value="offline">
			<?php endif; ?>

			<div id="ttbm-cc-checkout-msg" class="ttbm-cc-msg" style="display:none;" role="alert"></div>
			</div><!-- .ttbm-cc-modal-side -->

			<div id="ttbm-cc-result" class="ttbm-cc-result" style="display:none;" aria-live="polite"></div>
		</div>

		<div class="ttbm-cc-modal-footer">
			<button type="button" class="ttbm-cc-modal-close ttbm-cc-cancel-btn">
				<?php esc_html_e('Cancel', 'tour-booking-manager'); ?>
			</button>
			<button type="button" id="ttbm-cc-confirm-btn" class="ttbm-cc-primary-btn">
				<span class="ttbm-cc-btn-text">
					<span class="ttbm-cc-btn-icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 11V8a5 5 0 0 1 10 0v3"/><rect x="4" y="11" width="16" height="10" rx="2.5"/></svg>
					</span>
					<span class="ttbm-cc-btn-label"><?php esc_html_e('Complete Registration', 'tour-booking-manager'); ?></span>
				</span>
				<span class="ttbm-cc-btn-loading" style="display:none;">
					<span class="ttbm-cc-spinner" aria-hidden="true"></span>
					<span class="ttbm-cc-btn-label"><?php esc_html_e('Processing…', 'tour-booking-manager'); ?></span>
				</span>
			</button>
			<button type="button" id="ttbm-cc-done-btn" class="ttbm-cc-primary-btn ttbm-cc-done-btn" style="display:none;" data-ttbm-cc-close>
				<span class="ttbm-cc-btn-text">
					<span class="ttbm-cc-btn-label"><?php esc_html_e('Done', 'tour-booking-manager'); ?></span>
				</span>
			</button>
		</div>
		<div class="ttbm-cc-processing-lock" aria-hidden="true">
			<span class="ttbm-cc-processing-lock-panel">
				<span class="ttbm-cc-spinner" aria-hidden="true"></span>
				<span class="ttbm-cc-processing-lock-text"><?php esc_html_e('Processing your booking…', 'tour-booking-manager'); ?></span>
			</span>
		</div>
	</div>
</div>
