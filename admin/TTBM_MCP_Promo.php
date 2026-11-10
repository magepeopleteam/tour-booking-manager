<?php
/*
 * "MCP" page in the Tour menu for sites without Tour Booking Manager PRO's MCP
 * server: explains what MCP does and links to PRO. PRO registers its own MCP
 * page under the same slug first; this one only appears when that page doesn't.
 */
if (!defined('ABSPATH')) {
	die;
}

if (!class_exists('TTBM_MCP_Promo')) {
	class TTBM_MCP_Promo {
		const PAGE_SLUG = 'ttbm_mcp';
		const PRO_URL = 'https://mage-people.com/product/woocommerce-tour-and-travel-booking-manager-pro/';

		public function __construct() {
			// PRO's MCP page registers at 100, right after Travail Theme (99); this runs just after it.
			add_action('admin_menu', array($this, 'add_menu'), 101);
		}

		private static function has_mcp_page(): bool {
			global $submenu;
			foreach ((array) ($submenu['edit.php?post_type=ttbm_tour'] ?? array()) as $item) {
				if (isset($item[2]) && self::PAGE_SLUG === $item[2]) {
					return true;
				}
			}
			return false;
		}

		public function add_menu() {
			if (self::has_mcp_page()) {
				return;
			}
			$label = __('MCP', 'tour-booking-manager');
			add_submenu_page(
				'edit.php?post_type=ttbm_tour',
				$label,
				$label,
				'manage_options',
				self::PAGE_SLUG,
				array($this, 'render_page')
			);
		}

		private static function icon($path, $size = 20) {
			return '<svg aria-hidden="true" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="' . esc_attr($path) . '"/></svg>';
		}

		public function render_page() {
			if (!current_user_can('manage_options')) {
				return;
			}
			// An older PRO without MCP: the fix is an update, not a purchase.
			$has_pro = class_exists('TTBM_Woocommerce_Plugin_Pro');
			$cta = $has_pro ? __('Update PRO to get MCP', 'tour-booking-manager') : __('Get PRO', 'tour-booking-manager');
			$cta_url = $has_pro ? admin_url('plugins.php') : self::PRO_URL;
			$external = $has_pro ? '' : self::icon('M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5', 14);
			$prompts = array(
				__('Create a 3-day river tour starting 12 June, with adult and child tickets.', 'tour-booking-manager'),
				__('Which tours still have seats next weekend?', 'tour-booking-manager'),
				__('Show this month\'s revenue by tour.', 'tour-booking-manager'),
				__('Email everyone booked on Saturday\'s boat trip about the new meeting point.', 'tour-booking-manager'),
			);
			$abilities = array(
				array('M12 5v14M5 12h14', __('Create and update tours', 'tour-booking-manager'), __('Dates, tickets, itinerary, FAQs, places, guides and tour images.', 'tour-booking-manager')),
				array('M5 5h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zM4 10h16M8 3v4M16 3v4', __('Check bookings and seats', 'tour-booking-manager'), __('Look up bookings and orders, and see which dates still have seats.', 'tour-booking-manager')),
				array('M4 20h16M7 16v-5M12 16V7M17 16v-8', __('Get revenue reports', 'tour-booking-manager'), __('Ask for revenue by tour or period, plus a quick look at your dashboard numbers.', 'tour-booking-manager')),
				array('M4 6h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1zM3.5 7l8.5 6 8.5-6', __('Email your guests', 'tour-booking-manager'), __('Send a message to everyone booked on a tour, straight from the chat.', 'tour-booking-manager')),
			);
			$steps = array(
				array(__('Turn MCP on', 'tour-booking-manager'), __('Activate PRO and switch MCP on in this menu.', 'tour-booking-manager')),
				array(__('Add the connector', 'tour-booking-manager'), __('Paste your site\'s connector URL into your AI app.', 'tour-booking-manager')),
				array(__('Sign in and approve', 'tour-booking-manager'), __('Log in to your site and approve the app. That\'s it.', 'tour-booking-manager')),
			);
			$safety = array(
				__('Off until you switch it on.', 'tour-booking-manager'),
				__('Only administrators can approve an app.', 'tour-booking-manager'),
				__('See whether an app asks for read-only or read-and-write access before you approve it.', 'tour-booking-manager'),
				__('Revoke any app at any time.', 'tour-booking-manager'),
			);
			$check = self::icon('M5 12.5l4.5 4.5L19 7.5', 16);
			?>
			<div class="wrap ttbm-mcp-promo">
				<div class="ttbm-mp">
					<header class="ttbm-mp-head">
						<h1><?php esc_html_e('MCP', 'tour-booking-manager'); ?> <span class="ttbm-mp-pro"><?php esc_html_e('PRO', 'tour-booking-manager'); ?></span></h1>
						<p><?php esc_html_e('Connect AI assistants to your tours and bookings.', 'tour-booking-manager'); ?></p>
					</header>

					<section class="ttbm-mp-hero" aria-labelledby="ttbm-mp-hero-title">
						<div class="ttbm-mp-hero__copy">
							<h2 id="ttbm-mp-hero-title"><?php esc_html_e('Run your tour business from your AI assistant', 'tour-booking-manager'); ?></h2>
							<p><?php esc_html_e('With PRO, a built-in MCP server lets Claude, ChatGPT, Cursor and other AI apps work with your tour data. Ask in plain language and the assistant creates tours, checks bookings and pulls reports for you.', 'tour-booking-manager'); ?></p>
							<ul class="ttbm-mp-apps" aria-label="<?php esc_attr_e('Works with', 'tour-booking-manager'); ?>">
								<li>Claude</li>
								<li>ChatGPT</li>
								<li>Cursor</li>
								<li><?php esc_html_e('Other MCP apps', 'tour-booking-manager'); ?></li>
							</ul>
							<a class="ttbm-mp-btn ttbm-mp-btn--primary" href="<?php echo esc_url($cta_url); ?>"<?php echo $has_pro ? '' : ' target="_blank" rel="noopener noreferrer"'; ?>>
								<?php echo esc_html($cta); ?><?php echo $external; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
								<?php if (!$has_pro) : ?><span class="screen-reader-text"><?php esc_html_e('(opens in a new tab)', 'tour-booking-manager'); ?></span><?php endif; ?>
							</a>
						</div>
						<div class="ttbm-mp-chat" aria-label="<?php esc_attr_e('Things you can ask', 'tour-booking-manager'); ?>">
							<span class="ttbm-mp-chat__label"><?php esc_html_e('Things you can ask', 'tour-booking-manager'); ?></span>
							<ul>
								<?php foreach ($prompts as $prompt) : ?>
									<li><?php echo esc_html($prompt); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</section>

					<section class="ttbm-mp-section" aria-labelledby="ttbm-mp-can-title">
						<h2 id="ttbm-mp-can-title"><?php esc_html_e('What your assistant can do', 'tour-booking-manager'); ?></h2>
						<ul class="ttbm-mp-grid">
							<?php foreach ($abilities as $ability) : ?>
								<li class="ttbm-mp-card">
									<span class="ttbm-mp-icon"><?php echo self::icon($ability[0]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in icon() ?></span>
									<b><?php echo esc_html($ability[1]); ?></b>
									<span><?php echo esc_html($ability[2]); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>

					<div class="ttbm-mp-split">
						<section class="ttbm-mp-section ttbm-mp-box" aria-labelledby="ttbm-mp-how-title">
							<h2 id="ttbm-mp-how-title"><?php esc_html_e('How it works', 'tour-booking-manager'); ?></h2>
							<ol class="ttbm-mp-steps">
								<?php foreach ($steps as $i => $step) : ?>
									<li>
										<span class="ttbm-mp-steps__num" aria-hidden="true"><?php echo esc_html(number_format_i18n($i + 1)); ?></span>
										<span><b><?php echo esc_html($step[0]); ?></b><span><?php echo esc_html($step[1]); ?></span></span>
									</li>
								<?php endforeach; ?>
							</ol>
						</section>
						<section class="ttbm-mp-section ttbm-mp-box" aria-labelledby="ttbm-mp-safe-title">
							<h2 id="ttbm-mp-safe-title"><?php esc_html_e('You stay in control', 'tour-booking-manager'); ?></h2>
							<ul class="ttbm-mp-checks">
								<?php foreach ($safety as $line) : ?>
									<li><?php echo $check; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?><span><?php echo esc_html($line); ?></span></li>
								<?php endforeach; ?>
							</ul>
						</section>
					</div>

					<section class="ttbm-mp-cta" aria-labelledby="ttbm-mp-cta-title">
						<div>
							<h2 id="ttbm-mp-cta-title"><?php echo $has_pro ? esc_html__('Your PRO version doesn\'t include MCP yet', 'tour-booking-manager') : esc_html__('MCP comes with Tour Booking Manager PRO', 'tour-booking-manager'); ?></h2>
							<p><?php echo $has_pro ? esc_html__('Update Tour Booking Manager PRO to the latest version, then switch MCP on here.', 'tour-booking-manager') : esc_html__('Along with booking forms, the booking calendar, PDF tickets, reports and more.', 'tour-booking-manager'); ?></p>
						</div>
						<a class="ttbm-mp-btn ttbm-mp-btn--primary" href="<?php echo esc_url($cta_url); ?>"<?php echo $has_pro ? '' : ' target="_blank" rel="noopener noreferrer"'; ?>>
							<?php echo esc_html($cta); ?><?php echo $external; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
							<?php if (!$has_pro) : ?><span class="screen-reader-text"><?php esc_html_e('(opens in a new tab)', 'tour-booking-manager'); ?></span><?php endif; ?>
						</a>
					</section>
				</div>
			</div>
			<style>
				.ttbm-mcp-promo { --mp-accent: #0F766E; --mp-accent-soft: #E7F1F0; --mp-accent-ink: #0A524C; --mp-ink: #1B2430; --mp-muted: #4A5565; --mp-line: #DDE2E8; color: var(--mp-ink); }
				.ttbm-mcp-promo .ttbm-mp { max-width: 1160px; margin: 24px auto 40px; display: flex; flex-direction: column; gap: 28px; }
				.ttbm-mcp-promo .ttbm-mp-head h1 { display: flex; align-items: center; gap: 10px; margin: 0 0 6px; padding: 0; font-size: 30px; line-height: 1.2; font-weight: 700; color: var(--mp-ink); }
				.ttbm-mcp-promo .ttbm-mp-head p { margin: 0; font-size: 15px; color: var(--mp-muted); }
				.ttbm-mcp-promo .ttbm-mp-pro { padding: 2px 9px; border-radius: 999px; background: #D9480F; color: #fff; font-size: 12px; font-weight: 700; letter-spacing: .04em; }
				.ttbm-mcp-promo .ttbm-mp-hero { display: flex; flex-wrap: wrap; gap: 28px; padding: 32px; border-radius: 16px; background: #10302D; color: #fff; }
				.ttbm-mcp-promo .ttbm-mp-hero__copy { flex: 1 1 420px; display: flex; flex-direction: column; align-items: flex-start; gap: 14px; }
				.ttbm-mcp-promo .ttbm-mp-hero h2 { margin: 0; padding: 0; font-size: 26px; line-height: 1.25; font-weight: 700; color: #fff; }
				.ttbm-mcp-promo .ttbm-mp-hero p { margin: 0; max-width: 560px; font-size: 15px; line-height: 1.6; color: #D3E6E3; }
				.ttbm-mcp-promo .ttbm-mp-apps { display: flex; flex-wrap: wrap; gap: 8px; margin: 0; padding: 0; list-style: none; }
				.ttbm-mcp-promo .ttbm-mp-apps li { margin: 0; padding: 4px 12px; border: 1px solid rgba(255, 255, 255, .28); border-radius: 999px; color: #fff; font-size: 13px; font-weight: 600; }
				.ttbm-mcp-promo .ttbm-mp-chat { flex: 1 1 340px; display: flex; flex-direction: column; gap: 12px; padding: 20px; border-radius: 12px; background: rgba(255, 255, 255, .08); }
				.ttbm-mcp-promo .ttbm-mp-chat__label { font-size: 12px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #A9CFCA; }
				.ttbm-mcp-promo .ttbm-mp-chat ul { display: flex; flex-direction: column; gap: 10px; margin: 0; padding: 0; list-style: none; }
				.ttbm-mcp-promo .ttbm-mp-chat li { align-self: flex-start; max-width: 100%; margin: 0; padding: 10px 14px; border-radius: 12px 12px 12px 4px; background: #fff; color: var(--mp-ink); font-size: 14px; line-height: 1.45; }
				.ttbm-mcp-promo .ttbm-mp-section { display: flex; flex-direction: column; gap: 14px; }
				.ttbm-mcp-promo .ttbm-mp-section h2 { margin: 0; padding: 0; font-size: 20px; font-weight: 700; color: var(--mp-ink); }
				.ttbm-mcp-promo .ttbm-mp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px; margin: 0; padding: 0; list-style: none; }
				.ttbm-mcp-promo .ttbm-mp-card { display: flex; flex-direction: column; gap: 6px; margin: 0; padding: 20px; border: 1px solid var(--mp-line); border-radius: 12px; background: #fff; }
				.ttbm-mcp-promo .ttbm-mp-card b { margin-top: 6px; font-size: 15px; font-weight: 600; }
				.ttbm-mcp-promo .ttbm-mp-card span:last-child { font-size: 13.5px; line-height: 1.5; color: var(--mp-muted); }
				.ttbm-mcp-promo .ttbm-mp-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: var(--mp-accent-soft); color: var(--mp-accent); }
				.ttbm-mcp-promo .ttbm-mp-split { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 12px; }
				.ttbm-mcp-promo .ttbm-mp-box { padding: 24px; border: 1px solid var(--mp-line); border-radius: 12px; background: #fff; }
				.ttbm-mcp-promo .ttbm-mp-steps { display: flex; flex-direction: column; gap: 16px; margin: 0; padding: 0; list-style: none; }
				.ttbm-mcp-promo .ttbm-mp-steps li { display: flex; gap: 12px; align-items: flex-start; margin: 0; }
				.ttbm-mcp-promo .ttbm-mp-steps li > span:last-child { display: flex; flex-direction: column; gap: 2px; }
				.ttbm-mcp-promo .ttbm-mp-steps b { font-size: 14.5px; font-weight: 600; }
				.ttbm-mcp-promo .ttbm-mp-steps li > span:last-child span { font-size: 13.5px; line-height: 1.5; color: var(--mp-muted); }
				.ttbm-mcp-promo .ttbm-mp-steps__num { flex: none; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: var(--mp-accent); color: #fff; font-size: 13px; font-weight: 700; }
				.ttbm-mcp-promo .ttbm-mp-checks { display: flex; flex-direction: column; gap: 12px; margin: 0; padding: 0; list-style: none; }
				.ttbm-mcp-promo .ttbm-mp-checks li { display: flex; gap: 10px; align-items: flex-start; margin: 0; font-size: 14px; line-height: 1.5; }
				.ttbm-mcp-promo .ttbm-mp-checks svg { flex: none; margin-top: 3px; color: var(--mp-accent); }
				.ttbm-mcp-promo .ttbm-mp-cta { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; padding: 24px; border-radius: 12px; background: var(--mp-accent-soft); }
				.ttbm-mcp-promo .ttbm-mp-cta h2 { margin: 0 0 4px; padding: 0; font-size: 18px; font-weight: 700; color: var(--mp-ink); }
				.ttbm-mcp-promo .ttbm-mp-cta p { margin: 0; font-size: 14px; color: var(--mp-muted); }
				.ttbm-mcp-promo .ttbm-mp-btn { display: inline-flex; align-items: center; gap: 6px; min-height: 44px; padding: 0 20px; border-radius: 8px; font-size: 15px; font-weight: 600; text-decoration: none; }
				.ttbm-mcp-promo .ttbm-mp-btn--primary { background: var(--mp-accent); color: #fff; }
				.ttbm-mcp-promo .ttbm-mp-hero .ttbm-mp-btn--primary { background: #fff; color: #10302D; }
				.ttbm-mcp-promo .ttbm-mp-btn--primary:hover { background: var(--mp-accent-ink); color: #fff; }
				.ttbm-mcp-promo .ttbm-mp-hero .ttbm-mp-btn--primary:hover { background: #E7F1F0; color: #10302D; }
				.ttbm-mcp-promo .ttbm-mp-btn:focus-visible { outline: 2px solid var(--mp-accent); outline-offset: 2px; box-shadow: none; }
				.ttbm-mcp-promo .ttbm-mp-hero .ttbm-mp-btn:focus-visible { outline-color: #fff; }
				@media (max-width: 600px) { .ttbm-mcp-promo .ttbm-mp-hero { padding: 20px; } }
			</style>
			<?php
		}
	}
	new TTBM_MCP_Promo();
}
