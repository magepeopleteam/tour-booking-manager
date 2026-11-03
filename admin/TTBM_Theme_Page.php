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

		/**
		 * Feature list: title, one-line description and an inline SVG path (24×24, stroke icon).
		 */
		private static function features(): array {
			return array(
				array(
					'title' => __( 'Tour pages built for booking', 'tour-booking-manager' ),
					'text'  => __( 'Header with info tiles, date tiles, ticket cards and a live total.', 'tour-booking-manager' ),
					'icon'  => 'M3 9a3 3 0 0 0 0 6v3a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1v-3a3 3 0 0 1 0-6V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1zM13 5v2M13 11v2M13 17v2',
				),
				array(
					'title' => __( 'Easy-to-read tour details', 'tour-booking-manager' ),
					'text'  => __( 'Photo gallery, itinerary, included / excluded items, FAQ and reviews.', 'tour-booking-manager' ),
					'icon'  => 'M4 4h16v16H4zM4 15l4-4 5 5M14 14l2-2 4 4M15 8.5h.01',
				),
				array(
					'title' => __( 'Homepage search', 'tour-booking-manager' ),
					'text'  => __( 'Tabs for Tours, Destinations, Activities and Hotels.', 'tour-booking-manager' ),
					'icon'  => 'M11 4a7 7 0 1 0 0 14 7 7 0 0 0 0-14zM20 20l-4-4',
				),
				array(
					'title' => __( 'Grids that match your brand', 'tour-booking-manager' ),
					'text'  => __( 'Tour grids, destination cards and activity filters follow your Theme Color.', 'tour-booking-manager' ),
					'icon'  => 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z',
				),
				array(
					'title' => __( 'Elementor-ready', 'tour-booking-manager' ),
					'text'  => __( 'Homepages and widgets for Elementor, plus a Setup Wizard.', 'tour-booking-manager' ),
					'icon'  => 'M4 4h16v5H4zM4 13h7v7H4zM15 13h5v7h-5z',
				),
				array(
					'title' => __( 'One-click demo import', 'tour-booking-manager' ),
					'text'  => __( 'Sample tours, hotels, guides and places, ready to edit.', 'tour-booking-manager' ),
					'icon'  => 'M12 4v11M7 10l5 5 5-5M5 20h14',
				),
				array(
					'title' => __( 'WooCommerce checkout', 'tour-booking-manager' ),
					'text'  => __( 'Works with WooCommerce checkout and Tour Booking Manager.', 'tour-booking-manager' ),
					'icon'  => 'M3 4h2l2.5 11h11L21 7H6.2M9 20h.01M18 20h.01',
				),
				array(
					'title' => __( 'Fast and translation-ready', 'tour-booking-manager' ),
					'text'  => __( 'Responsive on every screen and ready to translate.', 'tour-booking-manager' ),
					'icon'  => 'M13 3 4 14h7l-1 7 9-11h-7z',
				),
			);
		}

		public function render_page() {
			$state         = TTBM_Theme_Promo::theme_state();
			$has_installer = (bool) apply_filters( 'ttbm_theme_card_has_installer', false );
			$theme         = wp_get_theme( TTBM_Theme_Promo::THEME_SLUG );
			$version       = $theme->exists() ? $theme->get( 'Version' ) : '';
			$demo_url      = (string) apply_filters( 'ttbm_travail_demo_url', self::DEMO_URL );
			$demo_host     = $demo_url ? (string) wp_parse_url( $demo_url, PHP_URL_HOST ) : '';
			?>
			<div class="wrap ttbm-theme-page">
				<div class="ttbm-theme-page__inner">
					<header class="ttbm-theme-page__header">
						<h1><?php esc_html_e( 'Travail Theme', 'tour-booking-manager' ); ?></h1>
						<?php if ( $demo_url ) : ?>
							<a class="ttbm-theme-page__header-link" href="<?php echo esc_url( $demo_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'View live demo', 'tour-booking-manager' ); ?>
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 17 17 7"/><path d="M8 7h9v9"/></svg>
								<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'tour-booking-manager' ); ?></span>
							</a>
						<?php endif; ?>
					</header>
					<hr class="wp-header-end">

					<section class="ttbm-theme-page__card ttbm-theme-page__hero">
						<div class="ttbm-theme-page__shot">
							<div class="ttbm-theme-page__browser">
								<div class="ttbm-theme-page__browser-bar" aria-hidden="true">
									<span></span><span></span><span></span>
									<?php if ( $demo_host ) : ?>
										<em><?php echo esc_html( $demo_host ); ?></em>
									<?php endif; ?>
								</div>
								<img src="<?php echo esc_url( TTBM_PLUGIN_URL . '/assets/images/travail-theme.jpg' ); ?>" alt="<?php esc_attr_e( 'Travail theme preview', 'tour-booking-manager' ); ?>" loading="lazy" width="1000" height="750" />
							</div>
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

							<div class="ttbm-theme-card__actions ttbm-theme-page__actions">
								<?php $this->render_actions( $state, $has_installer ); ?>
								<?php if ( $demo_url ) : ?>
									<a class="button ttbm-theme-page__demo-btn" href="<?php echo esc_url( $demo_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Live demo', 'tour-booking-manager' ); ?><span class="screen-reader-text"> <?php esc_html_e( '(opens in a new tab)', 'tour-booking-manager' ); ?></span></a>
								<?php endif; ?>
							</div>

							<dl class="ttbm-theme-page__facts">
								<?php if ( $version ) : ?>
									<div><dt><?php esc_html_e( 'Installed version', 'tour-booking-manager' ); ?></dt><dd><?php echo esc_html( $version ); ?></dd></div>
								<?php endif; ?>
								<div><dt><?php esc_html_e( 'Requires', 'tour-booking-manager' ); ?></dt><dd><?php esc_html_e( 'WordPress 6.0+ and PHP 7.4+', 'tour-booking-manager' ); ?></dd></div>
								<div><dt><?php esc_html_e( 'Works with', 'tour-booking-manager' ); ?></dt><dd><?php esc_html_e( 'Tour Booking Manager, WooCommerce, Elementor', 'tour-booking-manager' ); ?></dd></div>
								<div><dt><?php esc_html_e( 'License', 'tour-booking-manager' ); ?></dt><dd><?php esc_html_e( 'Free, GPL v2 or later', 'tour-booking-manager' ); ?></dd></div>
							</dl>
						</div>
					</section>

					<section class="ttbm-theme-page__card ttbm-theme-page__features-card">
						<h3><?php esc_html_e( 'What you get', 'tour-booking-manager' ); ?></h3>
						<ul class="ttbm-theme-page__features">
							<?php foreach ( self::features() as $feature ) : ?>
								<li>
									<span class="ttbm-theme-page__icon" aria-hidden="true">
										<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="<?php echo esc_attr( $feature['icon'] ); ?>"/></svg>
									</span>
									<strong><?php echo esc_html( $feature['title'] ); ?></strong>
									<span class="ttbm-theme-page__feature-text"><?php echo esc_html( $feature['text'] ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				</div>
			</div>
			<style>
				.ttbm-theme-page{--ttbm-accent:var(--wp-admin-theme-color,#2271b1);--ttbm-accent-tint:#f0f6fc;--ttbm-line:#dcdcde;--ttbm-text:#1d2327;--ttbm-muted:#50575e}
				@supports (background:color-mix(in srgb,red 10%,white)){.ttbm-theme-page{--ttbm-accent-tint:color-mix(in srgb,var(--ttbm-accent) 8%,#fff)}}
				.ttbm-theme-page__inner{max-width:1180px;display:flex;flex-direction:column;gap:24px;margin-top:8px}
				.ttbm-theme-page__header{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px}
				.ttbm-theme-page__header h1{margin:0;padding:0;font-size:26px;line-height:1.25;font-weight:600;color:var(--ttbm-text)}
				.ttbm-theme-page__header-link{display:inline-flex;align-items:center;gap:6px;min-height:44px;font-size:14px;font-weight:500;text-decoration:none;color:var(--ttbm-accent)}
				.ttbm-theme-page__header-link:hover,.ttbm-theme-page__header-link:focus{text-decoration:underline}
				.ttbm-theme-page .wp-header-end{display:none}
				.ttbm-theme-page__card{background:#fff;border:1px solid var(--ttbm-line);border-radius:12px;padding:32px;box-sizing:border-box}
				.ttbm-theme-page__hero{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,440px),1fr));gap:40px;align-items:center}
				.ttbm-theme-page__browser{border:1px solid var(--ttbm-line);border-radius:10px;overflow:hidden;background:#f6f7f7;box-shadow:0 1px 2px rgba(0,0,0,.04),0 12px 32px rgba(29,35,39,.08)}
				.ttbm-theme-page__browser-bar{display:flex;align-items:center;gap:6px;padding:10px 12px;border-bottom:1px solid var(--ttbm-line)}
				.ttbm-theme-page__browser-bar span{width:10px;height:10px;border-radius:50%;background:#c3c4c7}
				.ttbm-theme-page__browser-bar em{margin-left:10px;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-style:normal;font-size:12px;color:var(--ttbm-muted);background:#fff;border:1px solid var(--ttbm-line);border-radius:6px;padding:3px 10px}
				.ttbm-theme-page__browser img{display:block;width:100%;height:auto;aspect-ratio:4/3;object-fit:cover;object-position:top}
				.ttbm-theme-page__info{display:flex;flex-direction:column;gap:20px;min-width:0}
				.ttbm-theme-page__head{display:flex;flex-wrap:wrap;align-items:center;gap:12px}
				.ttbm-theme-page__head h2{margin:0;font-size:32px;line-height:1.15;font-weight:700;letter-spacing:-.01em;color:var(--ttbm-text)}
				.ttbm-theme-page__badge{font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#00701a;background:#edfaef;border:1px solid #b8e6bf;border-radius:999px;padding:4px 10px}
				.ttbm-theme-page__badge.is-active{color:#135e96;background:#f0f6fc;border-color:#c5d9ed}
				.ttbm-theme-page__badge.is-installed{color:#8a4b00;background:#fcf9e8;border-color:#f0d58c}
				.ttbm-theme-page__lead{margin:0;font-size:16px;line-height:1.6;color:#3c434a;max-width:52ch}
				.ttbm-theme-page .ttbm-theme-page__actions{display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin:0;padding:20px;border-radius:10px;background:var(--ttbm-accent-tint)}
				.ttbm-theme-page__actions .button{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 20px;border-radius:6px;font-size:15px;line-height:1.2}
				.ttbm-theme-page__actions .button-primary{font-weight:600}
				.ttbm-theme-page__actions .ttbm-theme-page__demo-btn{background:#fff;font-weight:500}
				.ttbm-theme-page__actions .ttbm-theme-page__note{order:10;flex-basis:100%;margin:0;font-size:13px;line-height:1.5;color:var(--ttbm-muted)}
				.ttbm-theme-page__facts{margin:0;padding:0;border-top:1px solid #f0f0f1;display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px}
				.ttbm-theme-page__facts div{display:flex;flex-direction:column;gap:4px;padding-top:14px}
				.ttbm-theme-page__facts dt{margin:0;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--ttbm-muted)}
				.ttbm-theme-page__facts dd{margin:0;font-size:14px;line-height:1.45;color:var(--ttbm-text)}
				.ttbm-theme-page__features-card{display:flex;flex-direction:column;gap:24px}
				.ttbm-theme-page__features-card h3{margin:0;font-size:18px;font-weight:600;color:var(--ttbm-text)}
				.ttbm-theme-page__features{margin:0;padding:0;list-style:none;display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,250px),1fr));gap:28px 32px}
				.ttbm-theme-page__features li{display:flex;flex-direction:column;gap:10px;margin:0}
				.ttbm-theme-page__icon{width:40px;height:40px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;background:var(--ttbm-accent-tint);color:var(--ttbm-accent)}
				.ttbm-theme-page__features strong{font-size:15px;font-weight:600;color:var(--ttbm-text)}
				.ttbm-theme-page__feature-text{font-size:14px;line-height:1.55;color:var(--ttbm-muted)}
				@media (max-width:782px){.ttbm-theme-page__card{padding:20px}.ttbm-theme-page__hero{gap:24px}.ttbm-theme-page__head h2{font-size:26px}}
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
