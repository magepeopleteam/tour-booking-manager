<?php
/**
 * "Travail Theme" page in the plugin's wp-admin menu (Tours → Travail Theme).
 *
 * Showcases the free Travail theme: screenshot, details, install steps and features. It installs nothing
 * itself (WordPress.org guideline 8):
 *  - not installed: a link to the GitHub release page and steps to upload the .zip, or PRO's one-click
 *    installer through the ttbm_theme_card_installer action when PRO is active.
 *  - installed: WordPress's own theme activation link.
 *  - active: links to Travail's Setup Wizard and the Customizer.
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
		/** Latest Travail release on GitHub: the admin downloads the .zip there and uploads it (nothing is installed from here). */
		const RELEASE_URL = 'https://github.com/magepeopleteam/travail/releases/latest';

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
			// Manual install steps: shown until Travail is active, unless PRO's one-click installer does it for them.
			$show_steps    = 'active' !== $state && ! ( 'missing' === $state && $has_installer );
			$external      = '<svg aria-hidden="true" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/></svg>';
			if ( 'active' === $state ) {
				$badge = array( 'is-active', __( 'Active', 'tour-booking-manager' ) );
			} elseif ( 'installed' === $state ) {
				$badge = array( 'is-installed', __( 'Installed', 'tour-booking-manager' ) );
			} else {
				$badge = array( '', __( 'Free', 'tour-booking-manager' ) );
			}
			?>
			<div class="wrap ttbm-theme-page">
				<div class="ttbm-tp">
					<header class="ttbm-tp-head">
						<h1><?php esc_html_e( 'Travail Theme', 'tour-booking-manager' ); ?> <span class="ttbm-tp-badge <?php echo esc_attr( $badge[0] ); ?>"><?php echo esc_html( $badge[1] ); ?></span></h1>
						<p><?php esc_html_e( 'The free theme made for Tour Booking Manager.', 'tour-booking-manager' ); ?></p>
					</header>
					<hr class="wp-header-end">

					<section class="ttbm-tp-hero" aria-labelledby="ttbm-tp-hero-title">
						<figure class="ttbm-tp-shot">
							<div class="ttbm-tp-shot__bar" aria-hidden="true">
								<span></span><span></span><span></span>
								<?php if ( $demo_host ) : ?><em><?php echo esc_html( $demo_host ); ?></em><?php endif; ?>
							</div>
							<img src="<?php echo esc_url( TTBM_PLUGIN_URL . '/assets/images/travail-theme.jpg' ); ?>" alt="<?php esc_attr_e( 'Travail theme home page with a tour search and tour grid', 'tour-booking-manager' ); ?>" loading="lazy" width="1000" height="750">
						</figure>

						<div class="ttbm-tp-info">
							<div class="ttbm-tp-info__copy">
								<h2 id="ttbm-tp-hero-title">
									<?php echo 'active' === $state
										? esc_html__( 'Travail is active on this site', 'tour-booking-manager' )
										: esc_html__( 'Make your tour site look like the demo', 'tour-booking-manager' ); ?>
								</h2>
								<p><?php esc_html_e( 'Travail gives you booking-ready tour pages, a homepage search and tour grids that use your brand colour. It\'s free, and built to work with this plugin.', 'tour-booking-manager' ); ?></p>
							</div>

							<div class="ttbm-theme-card__actions ttbm-tp-actions">
								<?php $this->render_actions( $state, $has_installer ); ?>
								<?php if ( $demo_url ) : ?>
									<a class="ttbm-tp-btn" href="<?php echo esc_url( $demo_url ); ?>" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e( 'View live demo', 'tour-booking-manager' ); ?><?php echo $external; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
										<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'tour-booking-manager' ); ?></span>
									</a>
								<?php endif; ?>
							</div>
							<?php if ( 'active' !== $state ) : ?>
								<p class="ttbm-tp-note">
									<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>
									<span><?php echo 'installed' === $state
										? esc_html__( 'Travail is installed but not active. Your current theme stays installed, so you can switch back at any time.', 'tour-booking-manager' )
										: esc_html__( 'Your current theme stays installed, so you can switch back at any time.', 'tour-booking-manager' ); ?></span>
								</p>
							<?php endif; ?>

							<dl class="ttbm-tp-facts">
								<div><dt><?php echo $version ? esc_html__( 'Installed version', 'tour-booking-manager' ) : esc_html__( 'Price', 'tour-booking-manager' ); ?></dt><dd><?php echo $version ? esc_html( $version ) : esc_html__( 'Free', 'tour-booking-manager' ); ?></dd></div>
								<div><dt><?php esc_html_e( 'Requires', 'tour-booking-manager' ); ?></dt><dd><?php esc_html_e( 'WordPress 6.0+, PHP 7.4+', 'tour-booking-manager' ); ?></dd></div>
								<div><dt><?php esc_html_e( 'Works with', 'tour-booking-manager' ); ?></dt><dd><?php esc_html_e( 'WooCommerce, Elementor', 'tour-booking-manager' ); ?></dd></div>
								<div><dt><?php esc_html_e( 'License', 'tour-booking-manager' ); ?></dt><dd><?php esc_html_e( 'GPL v2 or later', 'tour-booking-manager' ); ?></dd></div>
							</dl>
						</div>
					</section>

					<?php if ( $show_steps ) :
						$installed = 'installed' === $state;
						$steps     = array(
							array(
								$installed ? __( 'Downloaded', 'tour-booking-manager' ) : __( 'Download the theme', 'tour-booking-manager' ),
								$installed ? __( 'Done.', 'tour-booking-manager' ) : __( 'Click Download Travail, then download the travail .zip file from the release page. Keep the .zip as it is.', 'tour-booking-manager' ),
								$installed,
							),
							array(
								$installed ? __( 'Installed', 'tour-booking-manager' ) : __( 'Upload it', 'tour-booking-manager' ),
								$installed
									/* translators: %s: Travail version number */
									? ( $version ? sprintf( __( 'Travail %s is on your site.', 'tour-booking-manager' ), $version ) : __( 'Travail is on your site.', 'tour-booking-manager' ) )
									: '',
								$installed,
							),
							array(
								__( 'Activate and import the demo', 'tour-booking-manager' ),
								__( 'Click Activate, then follow Travail\'s Setup Wizard to add the demo homepage.', 'tour-booking-manager' ),
								false,
							),
						);
						?>
						<section class="ttbm-tp-section" aria-labelledby="ttbm-tp-steps-title">
							<h2 id="ttbm-tp-steps-title"><?php echo $installed ? esc_html__( 'One step left', 'tour-booking-manager' ) : esc_html__( 'Install it in three steps', 'tour-booking-manager' ); ?></h2>
							<ol class="ttbm-tp-steps">
								<?php foreach ( $steps as $i => $step ) :
									$current = ! $step[2] && ( $installed ? 2 === $i : 0 === $i );
									?>
									<li class="<?php echo $step[2] ? 'is-done' : ''; ?><?php echo $current ? ' is-current' : ''; ?>"<?php echo $current ? ' aria-current="step"' : ''; ?>>
										<span class="ttbm-tp-steps__mark" aria-hidden="true">
											<?php if ( $step[2] ) : ?>
												<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
											<?php else : ?>
												<?php echo esc_html( number_format_i18n( $i + 1 ) ); ?>
											<?php endif; ?>
										</span>
										<span class="ttbm-tp-steps__copy">
											<b><?php echo esc_html( $step[0] ); ?></b>
											<span>
												<?php if ( 1 === $i && ! $installed ) : ?>
													<?php
													printf(
														/* translators: %s: link to the theme upload screen */
														esc_html__( 'Go to %s and choose the .zip file.', 'tour-booking-manager' ),
														'<a href="' . esc_url( admin_url( 'theme-install.php?upload' ) ) . '">' . esc_html__( 'Appearance › Themes › Add New › Upload Theme', 'tour-booking-manager' ) . '</a>'
													);
													?>
												<?php else : ?>
													<?php echo esc_html( $step[1] ); ?>
												<?php endif; ?>
											</span>
										</span>
									</li>
								<?php endforeach; ?>
							</ol>
							<?php if ( ! $installed ) : ?>
								<p class="ttbm-tp-hint"><?php esc_html_e( 'Have Tour Booking Manager PRO? It installs and activates Travail in one click.', 'tour-booking-manager' ); ?></p>
							<?php endif; ?>
						</section>
					<?php endif; ?>

					<section class="ttbm-tp-section" aria-labelledby="ttbm-tp-features-title">
						<h2 id="ttbm-tp-features-title"><?php esc_html_e( 'What you get', 'tour-booking-manager' ); ?></h2>
						<ul class="ttbm-tp-features">
							<?php foreach ( self::features() as $feature ) : ?>
								<li>
									<span class="ttbm-tp-icon" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="<?php echo esc_attr( $feature['icon'] ); ?>"/></svg></span>
									<b><?php echo esc_html( $feature['title'] ); ?></b>
									<span><?php echo esc_html( $feature['text'] ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				</div>
			</div>
			<style>
				.ttbm-theme-page { --tp-accent: #0F766E; --tp-accent-soft: #E7F1F0; --tp-accent-ink: #0A524C; --tp-ink: #1B2430; --tp-muted: #4A5565; --tp-line: #DDE2E8; color: var(--tp-ink); }
				.ttbm-theme-page .wp-header-end { display: none; }
				.ttbm-theme-page .ttbm-tp { max-width: 1160px; margin: 24px auto 40px; display: flex; flex-direction: column; gap: 28px; }
				.ttbm-theme-page .ttbm-tp-head h1 { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin: 0 0 6px; padding: 0; font-size: 30px; line-height: 1.2; font-weight: 700; color: var(--tp-ink); }
				.ttbm-theme-page .ttbm-tp-head p { margin: 0; font-size: 15px; color: var(--tp-muted); }
				.ttbm-theme-page .ttbm-tp-badge { padding: 2px 10px; border-radius: 999px; background: var(--tp-accent-soft); color: #134E4A; font-size: 12px; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; }
				.ttbm-theme-page .ttbm-tp-badge.is-installed { background: #FFF1D6; color: #8A4B00; }
				.ttbm-theme-page .ttbm-tp-badge.is-active { background: var(--tp-accent); color: #fff; }
				.ttbm-theme-page .ttbm-tp-hero { display: flex; flex-wrap: wrap; gap: 32px; align-items: center; padding: 28px; border: 1px solid var(--tp-line); border-radius: 16px; background: #fff; }
				.ttbm-theme-page .ttbm-tp-shot { flex: 1 1 480px; min-width: 0; margin: 0; border: 1px solid var(--tp-line); border-radius: 12px; overflow: hidden; background: #F6F8FA; }
				.ttbm-theme-page .ttbm-tp-shot__bar { display: flex; align-items: center; gap: 6px; padding: 10px 14px; border-bottom: 1px solid var(--tp-line); background: #fff; }
				.ttbm-theme-page .ttbm-tp-shot__bar span { width: 10px; height: 10px; border-radius: 50%; background: #C5CCD5; }
				.ttbm-theme-page .ttbm-tp-shot__bar em { margin-left: 10px; padding: 3px 12px; border-radius: 999px; background: #EEF1F4; color: var(--tp-muted); font-size: 12px; font-style: normal; }
				.ttbm-theme-page .ttbm-tp-shot img { display: block; width: 100%; height: auto; aspect-ratio: 4 / 3; object-fit: cover; object-position: top; }
				.ttbm-theme-page .ttbm-tp-info { flex: 1 1 420px; min-width: 0; display: flex; flex-direction: column; gap: 18px; }
				.ttbm-theme-page .ttbm-tp-info__copy { display: flex; flex-direction: column; gap: 8px; }
				.ttbm-theme-page .ttbm-tp-info h2 { margin: 0; padding: 0; font-size: 26px; line-height: 1.25; font-weight: 700; color: var(--tp-ink); }
				.ttbm-theme-page .ttbm-tp-info__copy p { margin: 0; font-size: 15px; line-height: 1.6; color: var(--tp-muted); }
				.ttbm-theme-page .ttbm-tp-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin: 0; padding: 0; background: none; }
				.ttbm-theme-page .ttbm-tp-actions > strong { flex-basis: 100%; font-size: 14px; }
				.ttbm-theme-page .ttbm-tp-actions .ttbm-theme-card__hint,
				.ttbm-theme-page .ttbm-tp-actions .ttbm-theme-page__note { order: 10; flex-basis: 100%; margin: 0; font-size: 13.5px; line-height: 1.5; color: var(--tp-muted); }
				.ttbm-theme-page .ttbm-tp-btn,
				.ttbm-theme-page .ttbm-tp-actions .button { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; margin: 0; padding: 0 18px; border: 1px solid #C5CCD5; border-radius: 8px; background: #fff; color: var(--tp-ink); font-size: 15px; font-weight: 600; line-height: 1.2; text-decoration: none; box-shadow: none; }
				.ttbm-theme-page .ttbm-tp-btn:hover,
				.ttbm-theme-page .ttbm-tp-actions .button:hover { border-color: var(--tp-accent); color: var(--tp-accent); background: #fff; }
				.ttbm-theme-page .ttbm-tp-btn--primary,
				.ttbm-theme-page .ttbm-tp-actions .button-primary { border-color: var(--tp-accent); background: var(--tp-accent); color: #fff; }
				.ttbm-theme-page .ttbm-tp-btn--primary:hover,
				.ttbm-theme-page .ttbm-tp-actions .button-primary:hover { border-color: var(--tp-accent-ink); background: var(--tp-accent-ink); color: #fff; }
				.ttbm-theme-page .ttbm-tp-btn:focus-visible,
				.ttbm-theme-page .ttbm-tp-actions .button:focus-visible { outline: 2px solid var(--tp-accent); outline-offset: 2px; box-shadow: none; }
				.ttbm-theme-page .ttbm-tp-note { display: flex; gap: 8px; align-items: flex-start; margin: 0; font-size: 13.5px; line-height: 1.5; color: var(--tp-muted); }
				.ttbm-theme-page .ttbm-tp-note svg { flex: none; margin-top: 2px; color: var(--tp-accent); }
				.ttbm-theme-page .ttbm-tp-facts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px 20px; margin: 0; padding: 18px 0 0; border-top: 1px solid var(--tp-line); }
				.ttbm-theme-page .ttbm-tp-facts div { display: flex; flex-direction: column; gap: 2px; }
				.ttbm-theme-page .ttbm-tp-facts dt { margin: 0; font-size: 12px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; color: var(--tp-muted); }
				.ttbm-theme-page .ttbm-tp-facts dd { margin: 0; font-size: 14px; font-weight: 500; color: var(--tp-ink); }
				.ttbm-theme-page .ttbm-tp-section { display: flex; flex-direction: column; gap: 14px; }
				.ttbm-theme-page .ttbm-tp-section > h2 { margin: 0; padding: 0; font-size: 20px; font-weight: 700; color: var(--tp-ink); }
				.ttbm-theme-page .ttbm-tp-steps { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px; margin: 0; padding: 0; list-style: none; }
				.ttbm-theme-page .ttbm-tp-steps li { display: flex; gap: 14px; align-items: flex-start; margin: 0; padding: 20px; border: 1px solid var(--tp-line); border-radius: 12px; background: #fff; }
				.ttbm-theme-page .ttbm-tp-steps li.is-current { padding: 19px; border: 2px solid var(--tp-accent); }
				.ttbm-theme-page .ttbm-tp-steps__mark { flex: none; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 50%; background: var(--tp-accent); color: #fff; font-size: 14px; font-weight: 700; }
				.ttbm-theme-page .ttbm-tp-steps li.is-done .ttbm-tp-steps__mark { background: var(--tp-accent-soft); color: var(--tp-accent); }
				.ttbm-theme-page .ttbm-tp-steps__copy { display: flex; flex-direction: column; gap: 4px; }
				.ttbm-theme-page .ttbm-tp-steps__copy b { font-size: 15px; font-weight: 600; }
				.ttbm-theme-page .ttbm-tp-steps li.is-done .ttbm-tp-steps__copy b { color: var(--tp-muted); }
				.ttbm-theme-page .ttbm-tp-steps__copy span { font-size: 13.5px; line-height: 1.5; color: var(--tp-muted); }
				.ttbm-theme-page .ttbm-tp-steps__copy a { color: var(--tp-accent); }
				.ttbm-theme-page .ttbm-tp-hint { margin: 0; font-size: 13.5px; color: var(--tp-muted); }
				.ttbm-theme-page .ttbm-tp-features { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px; margin: 0; padding: 0; list-style: none; }
				.ttbm-theme-page .ttbm-tp-features li { display: flex; flex-direction: column; gap: 6px; margin: 0; padding: 20px; border: 1px solid var(--tp-line); border-radius: 12px; background: #fff; }
				.ttbm-theme-page .ttbm-tp-features b { margin-top: 6px; font-size: 15px; font-weight: 600; }
				.ttbm-theme-page .ttbm-tp-features li > span:last-child { font-size: 13.5px; line-height: 1.5; color: var(--tp-muted); }
				.ttbm-theme-page .ttbm-tp-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: var(--tp-accent-soft); color: var(--tp-accent); }
				@media (max-width: 782px) {
					.ttbm-theme-page .ttbm-tp-hero { padding: 20px; gap: 20px; }
					.ttbm-theme-page .ttbm-tp-facts { grid-template-columns: minmax(0, 1fr); }
				}
			</style>
			<?php
		}

		/**
		 * Main button(s) for each state. Activating an installed theme uses WordPress's own
		 * activation link; installing comes from PRO's one-click installer when it is there,
		 * otherwise the admin downloads the theme from its release page and uploads it.
		 */
		private function render_actions( string $state, bool $has_installer ) {
			if ( 'active' === $state ) {
				?>
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=travail-setup-wizard' ) ); ?>"><?php esc_html_e( 'Open Setup Wizard', 'tour-booking-manager' ); ?></a>
				<a class="button" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Customize', 'tour-booking-manager' ); ?></a>
				<?php
				return;
			}
			if ( 'installed' === $state && current_user_can( 'switch_themes' ) ) {
				$activate = wp_nonce_url(
					admin_url( 'themes.php?action=activate&stylesheet=' . rawurlencode( TTBM_Theme_Promo::THEME_SLUG ) ),
					'switch-theme_' . TTBM_Theme_Promo::THEME_SLUG
				);
				?>
				<a class="ttbm-tp-btn ttbm-tp-btn--primary" href="<?php echo esc_url( $activate ); ?>">
					<svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
					<?php esc_html_e( 'Activate Travail', 'tour-booking-manager' ); ?>
				</a>
				<?php
				return;
			}
			if ( $has_installer ) {
				// PRO: installs and/or activates with one click.
				do_action( 'ttbm_theme_card_installer', $state );
				return;
			}
			?>
			<a class="ttbm-tp-btn ttbm-tp-btn--primary" href="<?php echo esc_url( self::RELEASE_URL ); ?>" target="_blank" rel="noopener noreferrer">
				<svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v11M7 10l5 5 5-5M5 20h14"/></svg>
				<?php esc_html_e( 'Download Travail (free)', 'tour-booking-manager' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( '(opens the release page in a new tab)', 'tour-booking-manager' ); ?></span>
			</a>
			<?php
		}
	}
	new TTBM_Theme_Page();
}
