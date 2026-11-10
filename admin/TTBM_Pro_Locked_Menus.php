<?php
/*
 * "Pro Features" / "Addon Feature" placeholder menu.
 * - When Pro is inactive: shows as Pro Features and lists Pro + Addon teasers.
 * - When Pro is active: shows active Pro capabilities followed by Addon teasers.
 */
if (!defined('ABSPATH')) {
	die;
}

if (!class_exists('TTBM_Pro_Locked_Menus')) {
	class TTBM_Pro_Locked_Menus {
		public function __construct() {
			add_action('admin_menu', array($this, 'register_locked_menu'));
		}

		private function is_pro_active() {
			return class_exists('TTBM_Woocommerce_Plugin_Pro');
		}

		/**
		 * Label with a small pill after it. Inline styles because the admin menu shows on
		 * every screen, where the plugin's stylesheets are not loaded. Kept on one line.
		 */
		public static function menu_label($label, $badge, $background) {
			return '<span style="white-space:nowrap;">' . esc_html($label)
				. ' <span style="display:inline-block;margin-left:4px;padding:0 6px;border-radius:999px;background:' . esc_attr($background) . ';color:#fff;font-size:9px;font-weight:700;line-height:16px;letter-spacing:.4px;vertical-align:1px;">'
				. esc_html($badge) . '</span></span>';
		}

		/** No explicit position -- appends naturally after other submenus. */
		public function register_locked_menu() {
			$is_pro = $this->is_pro_active();
			if ($is_pro) {
				$page_title = esc_html__('Add-ons', 'tour-booking-manager');
				$menu_title = self::menu_label(__('Add-ons', 'tour-booking-manager'), __('ADDON', 'tour-booking-manager'), '#B91C3C');
			} else {
				$page_title = esc_html__('Pro Features', 'tour-booking-manager');
				$menu_title = self::menu_label(__('Pro Features', 'tour-booking-manager'), __('PRO', 'tour-booking-manager'), '#D9480F');
			}

			add_submenu_page(
				'edit.php?post_type=ttbm_tour',
				$page_title,
				$menu_title,
				'manage_options',
				'ttbm_pro_features',
				array($this, 'render_locked_page')
			);
		}

		/**
		 * Stroke icons (one 24×24 path each) for the cards below.
		 */
		private static function icon_path($name) {
			$paths = array(
				'form' => 'M7 3h10a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2zM9 8h6M9 12h6M9 16h3',
				'cal' => 'M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zM4 10h16M8 3v4M16 3v4',
				'people' => 'M9 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7zM3 20c0-3.3 2.7-6 6-6s6 2.7 6 6M16 4.5a3.5 3.5 0 0 1 0 6.5M18 14.5c1.8.8 3 2.7 3 5',
				'spark' => 'M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8zM19 16l.8 2.2L22 19l-2.2.8L19 22l-.8-2.2L16 19l2.2-.8z',
				'mail' => 'M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1zM3.5 7l8.5 6 8.5-6',
				'card' => 'M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1zM3 10h18M7 15h3',
				'clip' => 'M9 4h6v3H9zM8 5H6a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-2M9 12h6M9 16h4',
				'chart' => 'M4 20h16M7 16v-5M12 16V7M17 16v-8',
				'ticket' => 'M4 8a2 2 0 0 0 0 4 2 2 0 0 1 0 4v2h16v-2a2 2 0 0 1 0-4 2 2 0 0 0 0-4V6H4zM14 6v12',
				'user' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM4 21c0-4.4 3.6-8 8-8s8 3.6 8 8',
				'send' => 'M21 3L10 14M21 3l-7 18-4-7-7-4z',
				'sheet' => 'M5 3h14a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zM4 9h16M4 15h16M10 3v18',
				'star' => 'M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.9l-5.2 2.7 1-5.8L3.5 9.7l5.9-.9z',
				'qr' => 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h2v2h-2zM18 14h2v2M14 18h2v2M18 18h2v2',
				'sort' => 'M8 4v16M4 8l4-4 4 4M16 20V4M12 16l4 4 4-4',
				'clock' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM12 7v5l3 2',
				'plus' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM12 8v8M8 12h8',
				'seat' => 'M6 4h4v4H6zM14 4h4v4h-4zM6 12h4v4H6zM14 12h4v4h-4zM5 20h14',
				'gift' => 'M4 10h16v10H4zM3 7h18v3H3zM12 7v13M12 7c-1.5-3-5-3-5-1s3 1 5 1zM12 7c1.5-3 5-3 5-1s-3 1-5 1z',
			);
			return isset($paths[$name]) ? $paths[$name] : $paths['plus'];
		}

		public function render_locked_page() {
			$is_pro = $this->is_pro_active();
			$pro_url = 'https://mage-people.com/product/woocommerce-tour-and-travel-booking-manager-pro/';
			$features = array(
				array('icon' => 'form', 'label' => __('Booking Forms', 'tour-booking-manager'), 'desc' => __('Build custom guest forms with a drag-and-drop builder and assign them to any tour.', 'tour-booking-manager')),
				array('icon' => 'cal', 'label' => __('Booking Calendar', 'tour-booking-manager'), 'desc' => __('See every booking on a calendar across all dates, tours and order statuses.', 'tour-booking-manager')),
				array('icon' => 'people', 'label' => __('Bookings & Guests', 'tour-booking-manager'), 'desc' => __('Filter orders, edit guest details and export guest lists from one screen.', 'tour-booking-manager')),
				array('icon' => 'spark', 'label' => __('AI Assistant', 'tour-booking-manager'), 'desc' => __('Help writing tour content, answering admin questions and speeding up daily tasks.', 'tour-booking-manager')),
				array('icon' => 'mail', 'label' => __('Marketing Emails', 'tour-booking-manager'), 'desc' => __('Send promotional campaigns to past guests, with sending queue controls.', 'tour-booking-manager')),
				array('icon' => 'card', 'label' => __('Custom Payment Gateways', 'tour-booking-manager'), 'desc' => __('Take PayPal, Stripe and Offline payments without the WooCommerce checkout.', 'tour-booking-manager'), 'new' => true),
				array('icon' => 'clip', 'label' => __('Unified Booking Management', 'tour-booking-manager'), 'desc' => __('Manage WooCommerce and Custom Payment bookings together, with notes and history.', 'tour-booking-manager'), 'new' => true),
				array('icon' => 'chart', 'label' => __('Reports & Exports', 'tour-booking-manager'), 'desc' => __('Booking reports by tour, date, status, source and payment method, as PDF or CSV.', 'tour-booking-manager'), 'new' => true),
				array('icon' => 'ticket', 'label' => __('PDF & Thermal Tickets', 'tour-booking-manager'), 'desc' => __('QR-enabled A4 PDF tickets, and single or batch printing on 58 mm and 80 mm printers.', 'tour-booking-manager'), 'new' => true),
				array('icon' => 'user', 'label' => __('Customer Booking Portal', 'tour-booking-manager'), 'desc' => __('A My Bookings page where customers look up bookings, track status and download tickets.', 'tour-booking-manager'), 'new' => true),
				array('icon' => 'send', 'label' => __('Booking Emails', 'tour-booking-manager'), 'desc' => __('Branded confirmations sent on each status change, with optional PDF tickets.', 'tour-booking-manager'), 'new' => true),
				array('icon' => 'sheet', 'label' => __('Google Sheets Sync', 'tour-booking-manager'), 'desc' => __('Keep booking and guest details in sync with Google Sheets.', 'tour-booking-manager')),
				array('icon' => 'star', 'label' => __('Reviews & Ratings', 'tour-booking-manager'), 'desc' => __('Collect and moderate customer reviews and show ratings on tour pages.', 'tour-booking-manager')),
			);
			$addons = array(
				array('icon' => 'cal', 'label' => __('Seasonal Pricing', 'tour-booking-manager'), 'desc' => __('Different prices by date range, time slot and season, for peak and off-peak.', 'tour-booking-manager'), 'url' => 'https://mage-people.com/product/seasonal-pricing-addon-for-woocommerce-tour-plugin/'),
				array('icon' => 'qr', 'label' => __('QR Code Check-in', 'tour-booking-manager'), 'desc' => __('QR codes on tickets and bookings so guests check in quickly on arrival.', 'tour-booking-manager'), 'url' => 'https://mage-people.com/product/qr-code-addon-for-tour-booking-manager/'),
				array('icon' => 'sort', 'label' => __('Min & Max Quantity', 'tour-booking-manager'), 'desc' => __('Set the fewest and most tickets a customer can book for each ticket type.', 'tour-booking-manager'), 'url' => 'https://mage-people.com/product/tour-booking-max-min-addon/'),
				array('icon' => 'people', 'label' => __('Group Pricing', 'tour-booking-manager'), 'desc' => __('Quantity-based prices for group bookings, such as bulk discounts and party rates.', 'tour-booking-manager'), 'url' => 'https://mage-people.com/product/group-ticket-quantity-for-tour-plugin-wptravelly/'),
				array('icon' => 'clock', 'label' => __('Early Bird Pricing', 'tour-booking-manager'), 'desc' => __('Lower prices for early bookings, until the standard price starts.', 'tour-booking-manager'), 'url' => 'https://mage-people.com/product/early-bird-pricing-addon-for-tour-booking-manager/'),
				array('icon' => 'plus', 'label' => __('Backend Orders', 'tour-booking-manager'), 'desc' => __('Create bookings from the admin for phone bookings, walk-ins and orders on a customer\'s behalf.', 'tour-booking-manager'), 'url' => 'https://mage-people.com/product/backend-order-addon-for-tour-booking-manager/'),
				array('icon' => 'seat', 'label' => __('Seat Plan', 'tour-booking-manager'), 'desc' => __('Sell tickets with a seat plan, for cruises, restaurant tables and other seating.', 'tour-booking-manager'), 'url' => 'https://mage-people.com/product/seat-plan-addon-for-tour-booking-manager/'),
				array('icon' => 'ticket', 'label' => __('Group Ticket Quantity', 'tour-booking-manager'), 'desc' => __('Set group ticket sizes and price ranges by how many people book together.', 'tour-booking-manager'), 'url' => 'https://mage-people.com/product/group-ticket-quantity-for-tour-plugin-wptravelly/'),
				array('icon' => 'gift', 'label' => __('Buy X Get Y Free', 'tour-booking-manager'), 'desc' => __('Run Buy X Get Y Free offers to encourage customers to book more tickets.', 'tour-booking-manager'), 'url' => 'https://mage-people.com/product/tour-booking-buy-x-and-get-y-free-addon/'),
			);
			$icon = function ($name) {
				return '<svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="' . esc_attr(self::icon_path($name)) . '"/></svg>';
			};
			$external = '<svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>';
			?>
			<div class="wrap ttbm-addons-page<?php echo $is_pro ? ' is-pro' : ''; ?>">
				<div class="ttbm-ao">
					<header class="ttbm-ao-head">
						<div class="ttbm-ao-head__copy">
							<h1><?php echo $is_pro ? esc_html__('Add-ons', 'tour-booking-manager') : esc_html__('Pro Features', 'tour-booking-manager'); ?></h1>
							<p>
								<?php echo $is_pro
									? esc_html__('Everything in your PRO plan, and add-ons you can buy separately for pricing, check-in and admin bookings.', 'tour-booking-manager')
									: esc_html__('What Tour Booking Manager PRO adds, and add-ons you can buy separately for pricing, check-in and admin bookings.', 'tour-booking-manager'); ?>
							</p>
						</div>
						<nav class="ttbm-ao-jump" aria-label="<?php esc_attr_e('Page sections', 'tour-booking-manager'); ?>">
							<a href="#ttbm-ao-pro"><?php echo $is_pro ? esc_html__('In your PRO plan', 'tour-booking-manager') : esc_html__('PRO features', 'tour-booking-manager'); ?> <span><?php echo esc_html(number_format_i18n(count($features))); ?></span></a>
							<a href="#ttbm-ao-addons"><?php esc_html_e('Add-ons', 'tour-booking-manager'); ?> <span><?php echo esc_html(number_format_i18n(count($addons))); ?></span></a>
						</nav>
					</header>

					<section class="ttbm-ao-section" id="ttbm-ao-pro" aria-labelledby="ttbm-ao-pro-title">
						<div class="ttbm-ao-section__head">
							<div>
								<h2 id="ttbm-ao-pro-title"><?php echo $is_pro ? esc_html__('In your PRO plan', 'tour-booking-manager') : esc_html__('Unlock with PRO', 'tour-booking-manager'); ?></h2>
								<p><?php echo $is_pro ? esc_html__('All of these are switched on and ready to use.', 'tour-booking-manager') : esc_html__('Install and activate Tour Booking Manager PRO to use these.', 'tour-booking-manager'); ?></p>
							</div>
							<?php if ($is_pro) : ?>
								<span class="ttbm-ao-active">
									<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
									<?php esc_html_e('PRO is active', 'tour-booking-manager'); ?>
								</span>
							<?php else : ?>
								<a class="ttbm-ao-btn ttbm-ao-btn--primary" href="<?php echo esc_url($pro_url); ?>" target="_blank" rel="noopener noreferrer">
									<?php esc_html_e('Get PRO', 'tour-booking-manager'); ?><?php echo $external; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
									<span class="screen-reader-text"><?php esc_html_e('(opens in a new tab)', 'tour-booking-manager'); ?></span>
								</a>
							<?php endif; ?>
						</div>
						<ul class="ttbm-ao-grid">
							<?php foreach ($features as $feature) : ?>
								<li class="ttbm-ao-card">
									<span class="ttbm-ao-icon is-accent"><?php echo $icon($feature['icon']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure ?></span>
									<span class="ttbm-ao-card__copy">
										<span class="ttbm-ao-card__title">
											<b><?php echo esc_html($feature['label']); ?></b>
											<?php if (!empty($feature['new'])) : ?><span class="ttbm-ao-new"><?php esc_html_e('NEW', 'tour-booking-manager'); ?></span><?php endif; ?>
										</span>
										<span class="ttbm-ao-card__desc"><?php echo esc_html($feature['desc']); ?></span>
									</span>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>

					<section class="ttbm-ao-section" id="ttbm-ao-addons" aria-labelledby="ttbm-ao-addons-title">
						<div class="ttbm-ao-section__head">
							<div>
								<h2 id="ttbm-ao-addons-title"><?php esc_html_e('Add-ons you can get', 'tour-booking-manager'); ?></h2>
								<p><?php esc_html_e('Bought separately. Each one installs as its own plugin.', 'tour-booking-manager'); ?></p>
							</div>
						</div>
						<ul class="ttbm-ao-grid">
							<?php foreach ($addons as $addon) : ?>
								<li class="ttbm-ao-card ttbm-ao-card--addon">
									<span class="ttbm-ao-card__row">
										<span class="ttbm-ao-icon"><?php echo $icon($addon['icon']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the closure ?></span>
										<span class="ttbm-ao-card__copy">
											<span class="ttbm-ao-card__title"><b><?php echo esc_html($addon['label']); ?></b></span>
											<span class="ttbm-ao-card__desc"><?php echo esc_html($addon['desc']); ?></span>
										</span>
									</span>
									<a class="ttbm-ao-btn" href="<?php echo esc_url($addon['url']); ?>" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e('Get add-on', 'tour-booking-manager'); ?><?php echo $external; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
										<span class="screen-reader-text"><?php
											/* translators: %s: add-on name */
											echo esc_html(sprintf(__('%s (opens in a new tab)', 'tour-booking-manager'), $addon['label'])); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				</div>
			</div>
			<style>
				.ttbm-addons-page { --ao-accent: #0F766E; --ao-accent-soft: #E7F1F0; --ao-accent-ink: #0A524C; --ao-ink: #1B2430; --ao-muted: #4A5565; --ao-line: #DDE2E8; color: var(--ao-ink); }
				.ttbm-addons-page .ttbm-ao { max-width: 1160px; margin: 24px auto 40px; display: flex; flex-direction: column; gap: 32px; }
				.ttbm-addons-page .ttbm-ao-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px; }
				.ttbm-addons-page .ttbm-ao-head__copy { display: flex; flex-direction: column; gap: 6px; }
				.ttbm-addons-page .ttbm-ao-head h1 { margin: 0; padding: 0; font-size: 30px; line-height: 1.2; font-weight: 700; color: var(--ao-ink); }
				.ttbm-addons-page .ttbm-ao-head p { margin: 0; max-width: 640px; font-size: 15px; line-height: 1.55; color: var(--ao-muted); }
				.ttbm-addons-page .ttbm-ao-jump { display: flex; gap: 4px; padding: 4px; border-radius: 10px; background: #E7EBEF; }
				.ttbm-addons-page .ttbm-ao-jump a { display: inline-flex; align-items: center; gap: 6px; min-height: 40px; padding: 0 14px; border-radius: 7px; color: var(--ao-muted); font-size: 14px; font-weight: 600; text-decoration: none; }
				.ttbm-addons-page .ttbm-ao-jump a:hover { background: #fff; color: var(--ao-ink); box-shadow: 0 1px 2px rgba(15, 23, 42, .12); }
				.ttbm-addons-page .ttbm-ao-jump a span { font-weight: 500; }
				.ttbm-addons-page .ttbm-ao-jump a:focus-visible,
				.ttbm-addons-page .ttbm-ao-btn:focus-visible { outline: 2px solid var(--ao-accent); outline-offset: 2px; box-shadow: none; }
				.ttbm-addons-page .ttbm-ao-section { display: flex; flex-direction: column; gap: 16px; scroll-margin-top: 48px; }
				.ttbm-addons-page .ttbm-ao-section__head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
				.ttbm-addons-page .ttbm-ao-section__head h2 { margin: 0 0 4px; padding: 0; font-size: 20px; font-weight: 700; color: var(--ao-ink); }
				.ttbm-addons-page .ttbm-ao-section__head p { margin: 0; font-size: 14px; color: var(--ao-muted); }
				.ttbm-addons-page .ttbm-ao-active { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px; background: var(--ao-accent-soft); color: #134E4A; font-size: 13px; font-weight: 600; }
				.ttbm-addons-page .ttbm-ao-active svg { color: var(--ao-accent); }
				.ttbm-addons-page .ttbm-ao-grid { margin: 0; padding: 0; list-style: none; display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 12px; }
				.ttbm-addons-page .ttbm-ao-card { display: flex; gap: 14px; align-items: flex-start; margin: 0; padding: 18px; border: 1px solid var(--ao-line); border-radius: 12px; background: #fff; }
				.ttbm-addons-page .ttbm-ao-card--addon { flex-direction: column; gap: 14px; padding: 20px; }
				.ttbm-addons-page .ttbm-ao-card__row { display: flex; gap: 14px; align-items: flex-start; }
				.ttbm-addons-page .ttbm-ao-icon { flex: none; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: #EEF1F4; color: var(--ao-ink); }
				.ttbm-addons-page .ttbm-ao-icon.is-accent { background: var(--ao-accent-soft); color: var(--ao-accent); }
				.ttbm-addons-page:not(.is-pro) .ttbm-ao-icon.is-accent { background: #EEF1F4; color: var(--ao-muted); }
				.ttbm-addons-page .ttbm-ao-card__copy { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
				.ttbm-addons-page .ttbm-ao-card__title { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
				.ttbm-addons-page .ttbm-ao-card__title b { font-size: 15px; font-weight: 600; }
				.ttbm-addons-page .ttbm-ao-card__desc { font-size: 13.5px; line-height: 1.5; color: var(--ao-muted); }
				.ttbm-addons-page .ttbm-ao-new { padding: 1px 8px; border-radius: 999px; background: #FFF1D6; color: #8A4B00; font-size: 11px; font-weight: 700; letter-spacing: .03em; }
				.ttbm-addons-page .ttbm-ao-btn { align-self: flex-start; display: inline-flex; align-items: center; gap: 6px; min-height: 40px; padding: 0 14px; border: 1px solid #C5CCD5; border-radius: 8px; background: #fff; color: var(--ao-ink); font-size: 14px; font-weight: 600; text-decoration: none; }
				.ttbm-addons-page .ttbm-ao-card--addon .ttbm-ao-btn { margin-left: 54px; }
				.ttbm-addons-page .ttbm-ao-btn:hover { border-color: var(--ao-accent); color: var(--ao-accent); }
				.ttbm-addons-page .ttbm-ao-btn--primary { border-color: var(--ao-accent); background: var(--ao-accent); color: #fff; }
				.ttbm-addons-page .ttbm-ao-btn--primary:hover { border-color: var(--ao-accent-ink); background: var(--ao-accent-ink); color: #fff; }
				@media (max-width: 600px) {
					.ttbm-addons-page .ttbm-ao-grid { grid-template-columns: minmax(0, 1fr); }
					.ttbm-addons-page .ttbm-ao-card--addon .ttbm-ao-btn { margin-left: 0; }
				}
			</style>
			<?php
		}
	}
	new TTBM_Pro_Locked_Menus();
}
