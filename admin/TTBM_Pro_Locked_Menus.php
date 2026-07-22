<?php
/*
 * "Pro Features" / "Addon Feature" placeholder menu.
 * - When Pro is inactive: shows as Pro Features and lists Pro + Addon teasers.
 * - When Pro is active: shows as Addon Feature and lists only Addon teasers
 *   (Pro's own menus cover the real Pro pages).
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

		/** No explicit position -- appends naturally after other submenus. */
		public function register_locked_menu() {
			$is_pro = $this->is_pro_active();
			if ($is_pro) {
				$page_title = esc_html__('Addon Feature', 'tour-booking-manager');
				$menu_title = esc_html__('Addon Feature', 'tour-booking-manager') . ' <span style="display:inline-block;background:linear-gradient(135deg,#d31a2f,#961331);color:#fff;font-size:6px;font-weight:700;letter-spacing:.5px;border-radius:999px;padding:1px 7px;margin-left:4px;vertical-align:middle;">' . esc_html__('ADDON', 'tour-booking-manager') . '</span>';
			} else {
				$page_title = esc_html__('Pro Features', 'tour-booking-manager');
				$menu_title = esc_html__('Pro Features', 'tour-booking-manager') . ' <span style="display:inline-block;background:linear-gradient(135deg,#f7b733,#fc4a1a);color:#fff;font-size:9px;font-weight:700;letter-spacing:.5px;border-radius:999px;padding:1px 7px;margin-left:4px;vertical-align:middle;">' . esc_html__('PRO', 'tour-booking-manager') . '</span>';
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

		public function render_locked_page() {
			$is_pro = $this->is_pro_active();
			$features = array(
				array(
					'icon' => 'dashicons-feedback',
					'label' => esc_html__('Feature Booking Form', 'tour-booking-manager'),
					'desc' => esc_html__('Build custom attendee booking forms with a drag-and-drop form builder and assign them to any tour.', 'tour-booking-manager'),
				),
				array(
					'icon' => 'dashicons-calendar-alt',
					'label' => esc_html__('Booking Calendar', 'tour-booking-manager'),
					'desc' => esc_html__('See every tour booking on a calendar view across all dates, tours, and order statuses at a glance.', 'tour-booking-manager'),
				),
				array(
					'icon' => 'dashicons-groups',
					'label' => esc_html__('Booking and Guestlist', 'tour-booking-manager'),
					'desc' => esc_html__('Manage bookings and guest lists together -- filter orders, edit attendee details, and export guest data from one screen.', 'tour-booking-manager'),
				),
				array(
					'icon' => 'dashicons-superhero',
					'label' => esc_html__('AI Assistant', 'tour-booking-manager'),
					'desc' => esc_html__('Get AI-powered help writing tour content, answering admin questions, and speeding up day-to-day tour management tasks.', 'tour-booking-manager'),
				),
				array(
					'icon' => 'dashicons-email-alt',
					'label' => esc_html__('Marketing & Promo Email', 'tour-booking-manager'),
					'desc' => esc_html__('Send promotional and marketing emails to past attendees and tour customers with bulk campaigns and queue controls.', 'tour-booking-manager'),
				),
			);
			$addons = array(
				array(
					'icon' => 'dashicons-calendar-alt',
					'label' => esc_html__('Seasonal Price Addon', 'tour-booking-manager'),
					'desc' => esc_html__('Set different prices based on date ranges, time slots, and seasons — perfect for peak and off-peak management.', 'tour-booking-manager'),
					'url' => 'https://mage-people.com/product/seasonal-pricing-addon-for-woocommerce-tour-plugin/',
				),
				array(
					'icon' => 'dashicons-camera',
					'label' => esc_html__('QR Code Addon', 'tour-booking-manager'),
					'desc' => esc_html__('Generate QR codes for tickets and bookings so guests can be checked in quickly on arrival.', 'tour-booking-manager'),
					'url' => 'https://mage-people.com/product/qr-code-addon-for-tour-booking-manager/',
				),
				array(
					'icon' => 'dashicons-sort',
					'label' => esc_html__('Max -Min Qty Addon', 'tour-booking-manager'),
					'desc' => esc_html__('Control minimum and maximum ticket quantities customers can book for each ticket type.', 'tour-booking-manager'),
					'url' => 'https://mage-people.com/product/tour-booking-max-min-addon/',
				),
				array(
					'icon' => 'dashicons-groups',
					'label' => esc_html__('Group Pricing Addon', 'tour-booking-manager'),
					'desc' => esc_html__('Offer quantity-based pricing for group bookings — ideal for bulk discounts and party rates.', 'tour-booking-manager'),
					'url' => 'https://mage-people.com/product/group-ticket-quantity-for-tour-plugin-wptravelly/',
				),
				array(
					'icon' => 'dashicons-backup',
					'label' => esc_html__('Early Bird Addon', 'tour-booking-manager'),
					'desc' => esc_html__('Reward early bookings with time-limited discounted rates before the standard price kicks in.', 'tour-booking-manager'),
					'url' => 'https://mage-people.com/product/early-bird-pricing-addon-for-tour-booking-manager/',
				),
				array(
					'icon' => 'dashicons-plus-alt',
					'label' => esc_html__('Backend Order Addon', 'tour-booking-manager'),
					'desc' => esc_html__('Create bookings manually from the admin for phone bookings, walk-ins, or any order placed on a customer\'s behalf.', 'tour-booking-manager'),
					'url' => 'https://mage-people.com/product/backend-order-addon-for-tour-booking-manager/',
				),
				array(
					'icon' => 'dashicons-grid-view',
					'label' => esc_html__('Seat Plan Addon', 'tour-booking-manager'),
					'desc' => esc_html__('Sell tour tickets with seat plans — ideal for cruises, restaurant tables, and other seating arrangements.', 'tour-booking-manager'),
					'url' => 'https://mage-people.com/product/seat-plan-addon-for-tour-booking-manager/',
				),
				array(
					'icon' => 'dashicons-tickets-alt',
					'label' => esc_html__('Group Ticket Qty Addon', 'tour-booking-manager'),
					'desc' => esc_html__('Manage group ticket sizes and set special pricing ranges based on how many people book together.', 'tour-booking-manager'),
					'url' => 'https://mage-people.com/product/group-ticket-quantity-for-tour-plugin-wptravelly/',
				),
				array(
					'icon' => 'dashicons-buddicons-groups',
					'label' => esc_html__('Tour Booking Buy X and Get Y Free Addon', 'tour-booking-manager'),
					'desc' => esc_html__('Create Buy X Get Y Free promotions to encourage customers to purchase more tickets.', 'tour-booking-manager'),
					'url' => 'https://mage-people.com/product/tour-booking-buy-x-and-get-y-free-addon/',
				),
			);
			?>
			<div class="wrap">
				<div class="ttbm-pro-lock-wrap">
					<?php if (!$is_pro) : ?>
						<header class="ttbm-pro-lock-header">
							<h1><?php esc_html_e('Pro Features', 'tour-booking-manager'); ?></h1>
							<p><?php esc_html_e('Requires the tour-booking-manager-pro plugin to be installed and activated.', 'tour-booking-manager'); ?></p>
							<a class="ttbm-pro-get-btn" href="https://mage-people.com/product/woocommerce-tour-and-travel-booking-manager-pro/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Buy Pro Plugin', 'tour-booking-manager'); ?></a>
						</header>
						<div class="ttbm-pro-lock-grid">
							<?php foreach ($features as $feature) : ?>
								<div class="ttbm-pro-lock-box">
									<div class="ttbm-pro-lock-icon"><span class="dashicons <?php echo esc_attr($feature['icon']); ?>"></span></div>
									<h2><?php echo esc_html($feature['label']); ?> <span class="ttbm-pro-badge"><?php esc_html_e('PRO', 'tour-booking-manager'); ?></span></h2>
									<p><?php echo esc_html($feature['desc']); ?></p>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<header class="ttbm-pro-lock-header<?php echo $is_pro ? '' : ' ttbm-pro-lock-header--addon'; ?>">
						<h1><?php esc_html_e('Available Addons', 'tour-booking-manager'); ?></h1>
						<p><?php esc_html_e('Extend Tour Booking Manager with dedicated addons for pricing, check-in, and admin booking tools.', 'tour-booking-manager'); ?></p>
					</header>
					<div class="ttbm-pro-lock-grid">
						<?php foreach ($addons as $addon) : ?>
							<div class="ttbm-pro-lock-box">
								<div class="ttbm-pro-lock-icon"><span class="dashicons <?php echo esc_attr($addon['icon']); ?>"></span></div>
								<h2><?php echo esc_html($addon['label']); ?></h2>
								<p><?php echo esc_html($addon['desc']); ?></p>
								<?php if (!empty($addon['url'])) : ?>
									<a class="ttbm-addon-get-btn" href="<?php echo esc_url($addon['url']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Get Addon', 'tour-booking-manager'); ?></a>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
			<style>
				.ttbm-pro-lock-wrap { max-width: 1280px; margin: 30px auto 0; }
				.ttbm-pro-lock-header { text-align: center; margin-bottom: 28px; }
				.ttbm-pro-lock-header--addon { margin-top: 48px; padding-top: 32px; border-top: 1px solid #dcdcde; }
				.ttbm-pro-lock-header h1 { font-size: 22px; margin-bottom: 8px; }
				.ttbm-pro-lock-header p { color: #787c82; font-size: 13px; }
				.ttbm-pro-lock-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
				@media (max-width: 1200px) { .ttbm-pro-lock-grid { grid-template-columns: repeat(3, 1fr); } }
				@media (max-width: 900px) { .ttbm-pro-lock-grid { grid-template-columns: repeat(2, 1fr); } }
				@media (max-width: 600px) { .ttbm-pro-lock-grid { grid-template-columns: 1fr; } }
				.ttbm-pro-lock-box { background: #fff; border: 1px solid #dcdcde; border-radius: 10px; padding: 22px; text-align: center; }
				.ttbm-pro-lock-icon { width: 52px; height: 52px; margin: 0 auto 14px; border-radius: 50%; background: #f0f0f1; display: flex; align-items: center; justify-content: center; }
				.ttbm-pro-lock-icon .dashicons { font-size: 24px; width: 24px; height: 24px; color: #787c82; }
				.ttbm-pro-lock-box h2 { font-size: 16px; margin: 0 0 10px; }
				.ttbm-pro-lock-box p { color: #50575e; font-size: 13.5px; line-height: 1.6; margin: 0; }
				.ttbm-pro-badge { display: inline-block; background: linear-gradient(135deg,#f7b733,#fc4a1a); color: #fff; font-size: 10px; font-weight: 700; letter-spacing: .5px; border-radius: 999px; padding: 2px 8px; margin-left: 4px; vertical-align: middle; }
				.ttbm-pro-get-btn { display: inline-block; margin-top: 14px; background: linear-gradient(135deg,#f7b733,#fc4a1a); color: #fff !important; text-decoration: none; font-size: 13px; font-weight: 600; border-radius: 6px; padding: 10px 18px; }
				.ttbm-pro-get-btn:hover { opacity: .9; color: #fff !important; }
				.ttbm-addon-get-btn { display: inline-block; margin-top: 14px; background: linear-gradient(135deg,#d31a2f,#961331); color: #fff !important; text-decoration: none; font-size: 12px; font-weight: 600; border-radius: 6px; padding: 8px 14px; }
				.ttbm-addon-get-btn:hover { opacity: .9; color: #fff !important; }
			</style>
			<?php
		}
	}
	new TTBM_Pro_Locked_Menus();
}
