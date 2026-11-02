<?php
/**
 * "Travail Theme" page in the plugin's wp-admin menu (Tours → Travail Theme).
 *
 * Showcases the free Travail theme: screenshot, details, features and links. It installs nothing itself
 * (WordPress.org guideline 8). The button area works like the Tour Manager card:
 *  - PRO active  : PRO supplies "Install & activate" / "Activate" through the ttbm_theme_card_installer action.
 *  - PRO missing : a "Download free theme" link to the GitHub release plus a note about PRO's one-click install.
 *
 * @package Tour Booking Manager
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'TTBM_Theme_Page' ) ) {
	class TTBM_Theme_Page {
		const PAGE_SLUG = 'ttbm_travail_theme';
		/** The plugin's public live demo. A Travail-only demo can replace it with the ttbm_travail_demo_url filter. */
		const DEMO_URL  = 'https://wptravelly.com/';

		public function __construct() {
			add_action( 'admin_menu', array( $this, 'register_menu' ), 99 );
		}

		public function register_menu() {
			add_submenu_page(
				'edit.php?post_type=ttbm_tour',
				esc_html__( 'Travail Theme', 'tour-booking-manager' ),
				esc_html__( 'Travail Theme', 'tour-booking-manager' ),
				'manage_options',
				self::PAGE_SLUG,
				array( $this, 'render_page' )
			);
		}

		public static function page_url(): string {
			return admin_url( 'edit.php?post_type=ttbm_tour&page=' . self::PAGE_SLUG );
		}

		public function render_page() {
			$state         = TTBM_Theme_Promo::theme_state();
			$has_installer = (bool) apply_filters( 'ttbm_theme_card_has_installer', false );
			$theme         = wp_get_theme( TTBM_Theme_Promo::THEME_SLUG );
			$version       = $theme->exists() ? $theme->get( 'Version' ) : '';
			$demo_url      = (string) apply_filters( 'ttbm_travail_demo_url', self::DEMO_URL );
			$features      = array(
				esc_html__( 'Tour pages built for booking: header with info tiles, date tiles, ticket cards and a live total.', 'tour-booking-manager' ),
				esc_html__( 'Photo gallery, itinerary, included / excluded items, FAQ and reviews, laid out for easy reading.', 'tour-booking-manager' ),
				esc_html__( 'Homepage search with Tours, Destinations, Activities and Hotels tabs.', 'tour-booking-manager' ),
				esc_html__( 'Tour grids, destination cards and activity filters that match your Theme Color.', 'tour-booking-manager' ),
				esc_html__( 'Elementor-ready homepages and widgets, with a Setup Wizard.', 'tour-booking-manager' ),
				esc_html__( 'One-click demo import with sample tours, hotels, guides and places.', 'tour-booking-manager' ),
				esc_html__( 'Works with WooCommerce checkout and Tour Booking Manager.', 'tour-booking-manager' ),
				esc_html__( 'Fast, responsive and translation-ready.', 'tour-booking-manager' ),
			);
			?>
			<div class="wrap ttbm-theme-page">
				<h1><?php esc_html_e( 'Travail Theme', 'tour-booking-manager' ); ?></h1>
				<div class="ttbm-theme-page__grid">
					<div class="ttbm-theme-page__shot">
						<img src="<?php echo esc_url( TTBM_PLUGIN_URL . '/assets/images/travail-theme.jpg' ); ?>" alt="<?php esc_attr_e( 'Travail theme preview', 'tour-booking-manager' ); ?>" loading="lazy" />
						<?php if ( $demo_url ) : ?>
							<a class="ttbm-theme-page__demo" href="<?php echo esc_url( $demo_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View live demo', 'tour-booking-manager' ); ?> <span aria-hidden="true">↗</span></a>
						<?php endif; ?>
					</div>
					<div class="ttbm-theme-page__info">
						<div class="ttbm-theme-page__head">
							<h2>Travail</h2>
							<?php if ( 'active' === $state ) : ?>
								<span class="ttbm-theme-page__badge is-active"><?php esc_html_e( 'Active', 'tour-booking-manager' ); ?></span>
							<?php elseif ( 'installed' === $state ) : ?>
								<span class="ttbm-theme-page__badge is-installed"><?php esc_html_e( 'Installed', 'tour-booking-manager' ); ?></span>
							<?php else : ?>
								<span class="ttbm-theme-page__badge"><?php esc_html_e( 'Free theme', 'tour-booking-manager' ); ?></span>
							<?php endif; ?>
						</div>
						<p class="ttbm-theme-page__lead"><?php esc_html_e( 'A free travel and tour theme made for Tour Booking Manager. Show your tours, take bookings and look professional from day one.', 'tour-booking-manager' ); ?></p>

						<ul class="ttbm-theme-page__features">
							<?php foreach ( $features as $feature ) : ?>
								<li><?php echo esc_html( $feature ); ?></li>
							<?php endforeach; ?>
						</ul>

						<dl class="ttbm-theme-page__facts">
							<?php if ( $version ) : ?>
								<div><dt><?php esc_html_e( 'Installed version', 'tour-booking-manager' ); ?></dt><dd><?php echo esc_html( $version ); ?></dd></div>
							<?php endif; ?>
							<div><dt><?php esc_html_e( 'Requires', 'tour-booking-manager' ); ?></dt><dd><?php esc_html_e( 'WordPress 6.0+ and PHP 7.4+', 'tour-booking-manager' ); ?></dd></div>
							<div><dt><?php esc_html_e( 'Works with', 'tour-booking-manager' ); ?></dt><dd><?php esc_html_e( 'Tour Booking Manager, WooCommerce, Elementor', 'tour-booking-manager' ); ?></dd></div>
							<div><dt><?php esc_html_e( 'License', 'tour-booking-manager' ); ?></dt><dd><?php esc_html_e( 'Free, GPL v2 or later', 'tour-booking-manager' ); ?></dd></div>
						</dl>

						<div class="ttbm-theme-card__actions ttbm-theme-page__actions">
							<?php $this->render_actions( $state, $has_installer ); ?>
						</div>
						<?php if ( $demo_url ) : ?>
							<p class="ttbm-theme-page__links">
								<a href="<?php echo esc_url( $demo_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Live demo', 'tour-booking-manager' ); ?></a>
							</p>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<style>
				.ttbm-theme-page__grid{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(0,1fr);gap:32px;align-items:start;margin-top:16px;max-width:1180px}
				.ttbm-theme-page__shot{position:relative}
				.ttbm-theme-page__shot img{display:block;width:100%;height:auto;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 8px 30px rgba(15,23,42,.1)}
				.ttbm-theme-page__demo{position:absolute;left:16px;bottom:16px;display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:999px;background:#fff;color:#0f172a;font-weight:700;font-size:13px;text-decoration:none;box-shadow:0 6px 18px rgba(15,23,42,.25)}
				.ttbm-theme-page__demo:hover,.ttbm-theme-page__demo:focus{background:#0f172a;color:#fff}
				.ttbm-theme-page__info{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:26px 28px}
				.ttbm-theme-page__head{display:flex;align-items:center;gap:12px}
				.ttbm-theme-page__head h2{margin:0;font-size:26px}
				.ttbm-theme-page__badge{padding:3px 12px;border-radius:999px;background:#dcfce7;color:#166534;font-size:12px;font-weight:700}
				.ttbm-theme-page__badge.is-active{background:#dbeafe;color:#1d4ed8}
				.ttbm-theme-page__badge.is-installed{background:#fef3c7;color:#92400e}
				.ttbm-theme-page__lead{margin:12px 0 16px;font-size:14px;line-height:1.6;color:#475569}
				.ttbm-theme-page__features{margin:0 0 18px;padding:0;list-style:none}
				.ttbm-theme-page__features li{position:relative;padding:5px 0 5px 26px;font-size:13.5px;line-height:1.5;color:#1e293b}
				.ttbm-theme-page__features li:before{content:"";position:absolute;left:3px;top:10px;width:6px;height:11px;border:solid #16a34a;border-width:0 2px 2px 0;transform:rotate(45deg) scale(.9)}
				.ttbm-theme-page__facts{margin:0 0 20px;padding:14px 0 0;border-top:1px solid #eef2f7}
				.ttbm-theme-page__facts div{display:flex;gap:14px;padding:4px 0;font-size:13px}
				.ttbm-theme-page__facts dt{flex:0 0 130px;margin:0;color:#64748b}
				.ttbm-theme-page__facts dd{margin:0;color:#0f172a;font-weight:600}
				.ttbm-theme-page__actions{margin:0 0 12px}
				.ttbm-theme-page__links{margin:0;font-size:13px}
				.ttbm-theme-page__note{flex-basis:100%;margin:6px 0 0;font-size:13px;color:#475569}
				@media (max-width:1100px){.ttbm-theme-page__grid{grid-template-columns:1fr}}
			</style>
			<?php
		}

		private function render_actions( string $state, bool $has_installer ) {
			if ( 'active' === $state ) {
				?>
				<strong><?php esc_html_e( 'Travail is active on this site ✓', 'tour-booking-manager' ); ?></strong>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=travail-setup-wizard' ) ); ?>"><?php esc_html_e( 'Open Setup Wizard', 'tour-booking-manager' ); ?></a>
				<a class="button" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Customize', 'tour-booking-manager' ); ?></a>
				<?php
				return;
			}
			if ( $has_installer ) {
				// PRO: installs and/or activates with one click.
				do_action( 'ttbm_theme_card_installer', $state );
				return;
			}
			TTBM_Theme_Promo::render_no_installer_actions( $state );
		}
	}
	new TTBM_Theme_Page();
}
