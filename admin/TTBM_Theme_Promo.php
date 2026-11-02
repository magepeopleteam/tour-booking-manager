<?php
/**
 * "Travail" free-theme card for the Tour Manager screen.
 *
 * The free plugin never downloads or installs a theme (WordPress.org guideline 8). It only:
 *  - tells the admin about the free Travail theme and links to its GitHub release page, and
 *  - mentions that Tour Booking Manager PRO can install and activate it in one click.
 *
 * PRO plugs the one-click installer in through two hooks:
 *   apply_filters( 'ttbm_theme_card_has_installer', false )   -> true when an installer is available
 *   do_action( 'ttbm_theme_card_installer', $state )          -> renders the installer's own button(s)
 *
 * Shown only on the plugin's own Tour Manager screen, to users who can install themes. It is dismissible
 * per user, hidden once Travail is active, and respects the DISABLE_NAG_NOTICES constant.
 *
 * @package Tour Booking Manager
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'TTBM_Theme_Promo' ) ) {
	class TTBM_Theme_Promo {
		const THEME_SLUG   = 'travail';
		const PRO_URL      = 'https://mage-people.com/product/woocommerce-tour-and-travel-booking-manager-pro/';
		const DISMISS_META = 'ttbm_theme_card_dismissed';

		public function __construct() {
			add_action( 'ttbm_tour_manager_notices', array( $this, 'render_card' ) );
			add_action( 'wp_ajax_ttbm_dismiss_theme_card', array( $this, 'ajax_dismiss' ) );
		}

		/** 'active' | 'installed' | 'missing' */
		public static function theme_state(): string {
			if ( get_stylesheet() === self::THEME_SLUG || get_template() === self::THEME_SLUG ) {
				return 'active';
			}
			$theme = wp_get_theme( self::THEME_SLUG );
			return $theme->exists() ? 'installed' : 'missing';
		}

		/** 'active' | 'installed' (files present, not active) | 'missing' */
		public static function pro_state(): string {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$file = 'tour-booking-manager-pro/tour-booking-manager-pro.php';
			if ( is_plugin_active( $file ) ) {
				return 'active';
			}
			return file_exists( WP_PLUGIN_DIR . '/' . $file ) ? 'installed' : 'missing';
		}

		/**
		 * The buttons shown when no installer is available (the one-click install is a PRO feature):
		 *  - theme already installed       -> link to Appearance → Themes (activation is a core WordPress action)
		 *  - PRO active but installs locked -> short note (file changes are disabled on the site)
		 *  - PRO installed, not active      -> "Activate PRO"
		 *  - PRO not installed              -> "Get PRO"
		 * The free plugin never links to GitHub or downloads anything.
		 */
		public static function render_no_installer_actions( string $state ) {
			if ( 'installed' === $state ) {
				?>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'themes.php?theme=' . self::THEME_SLUG ) ); ?>"><?php esc_html_e( 'Open Travail in Appearance → Themes', 'tour-booking-manager' ); ?></a>
				<p class="ttbm-theme-card__hint ttbm-theme-page__note"><?php esc_html_e( 'Travail is already installed. Click Activate on the theme screen.', 'tour-booking-manager' ); ?></p>
				<?php
				return;
			}
			$pro = self::pro_state();
			if ( 'active' === $pro ) {
				?>
				<p class="ttbm-theme-card__hint ttbm-theme-page__note"><?php esc_html_e( 'Installing themes is turned off on this site (file changes are locked), so Travail cannot be installed from here.', 'tour-booking-manager' ); ?></p>
				<?php
			} elseif ( 'installed' === $pro ) {
				?>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>"><?php esc_html_e( 'Activate PRO to install Travail', 'tour-booking-manager' ); ?></a>
				<p class="ttbm-theme-card__hint ttbm-theme-page__note"><?php esc_html_e( 'Tour Booking Manager PRO is installed but not active. Activate it to install and activate Travail with one click.', 'tour-booking-manager' ); ?></p>
				<?php
			} else {
				?>
				<a class="button button-primary" href="<?php echo esc_url( self::PRO_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get PRO to install Travail', 'tour-booking-manager' ); ?></a>
				<p class="ttbm-theme-card__hint ttbm-theme-page__note"><?php esc_html_e( 'Travail is a free theme. Tour Booking Manager PRO installs and activates it in one click.', 'tour-booking-manager' ); ?></p>
				<?php
			}
		}

		private function should_show(): bool {
			if ( defined( 'DISABLE_NAG_NOTICES' ) && DISABLE_NAG_NOTICES ) {
				return false;
			}
			if ( ! current_user_can( 'install_themes' ) ) {
				return false;
			}
			if ( get_user_meta( get_current_user_id(), self::DISMISS_META, true ) ) {
				return false;
			}
			if ( 'active' === self::theme_state() ) {
				return false;
			}
			return (bool) apply_filters( 'ttbm_theme_promo_enabled', true );
		}

		public function ajax_dismiss() {
			check_ajax_referer( 'ttbm_theme_card', 'nonce' );
			if ( ! current_user_can( 'install_themes' ) ) {
				wp_send_json_error( '', 403 );
			}
			update_user_meta( get_current_user_id(), self::DISMISS_META, 1 );
			wp_send_json_success();
		}

		public function render_card() {
			if ( ! $this->should_show() ) {
				return;
			}
			$state         = self::theme_state();
			$has_installer = (bool) apply_filters( 'ttbm_theme_card_has_installer', false );
			?>
			<div class="ttbm-theme-card" id="ttbm-theme-card">
				<button type="button" class="ttbm-theme-card__close" aria-label="<?php esc_attr_e( 'Hide this suggestion', 'tour-booking-manager' ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'ttbm_theme_card' ) ); ?>">&times;</button>
				<span class="ttbm-theme-card__badge"><?php esc_html_e( 'Free theme', 'tour-booking-manager' ); ?></span>
				<h3><?php esc_html_e( 'Make your tour site look great with Travail', 'tour-booking-manager' ); ?></h3>
				<p>
					<?php esc_html_e( 'Travail is a free theme built for this plugin: tour grids, a booking-ready tour page, search and an Elementor homepage.', 'tour-booking-manager' ); ?>
					<?php if ( class_exists( 'TTBM_Theme_Page' ) ) : ?>
						<a href="<?php echo esc_url( TTBM_Theme_Page::page_url() ); ?>"><?php esc_html_e( 'See details', 'tour-booking-manager' ); ?></a>
					<?php endif; ?>
				</p>

				<?php if ( $has_installer ) : ?>
					<div class="ttbm-theme-card__actions">
						<?php do_action( 'ttbm_theme_card_installer', $state ); ?>
					</div>
				<?php else : ?>
					<div class="ttbm-theme-card__actions">
						<?php self::render_no_installer_actions( $state ); ?>
					</div>
				<?php endif; ?>
			</div>
			<style>
				.ttbm-theme-card{position:relative;margin:0 0 16px;padding:20px 24px;text-align:left;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px}
				.ttbm-theme-card h3{margin:6px 0 6px;font-size:17px}
				.ttbm-theme-card p{margin:0 0 12px;color:#475569;line-height:1.55}
				.ttbm-theme-card__badge{display:inline-block;padding:2px 10px;border-radius:999px;background:#dcfce7;color:#166534;font-size:11px;font-weight:700;letter-spacing:.04em;text-transform:uppercase}
				.ttbm-theme-card__actions{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin:4px 0 10px}
				.ttbm-theme-card__hint{margin:0;font-size:13px}
				.ttbm-theme-card__close{position:absolute;top:8px;right:12px;padding:2px 8px;border:0;background:transparent;color:#64748b;font-size:22px;line-height:1;cursor:pointer}
				.ttbm-theme-card__close:hover{color:#0f172a}
			</style>
			<script>
				jQuery(function ($) {
					$('#ttbm-theme-card .ttbm-theme-card__close').on('click', function () {
						$.post(ajaxurl, { action: 'ttbm_dismiss_theme_card', nonce: $(this).data('nonce') });
						$('#ttbm-theme-card').slideUp(150);
					});
				});
			</script>
			<?php
		}
	}
	new TTBM_Theme_Promo();
}
