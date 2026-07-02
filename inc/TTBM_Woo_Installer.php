<?php
/**
 * TTBM WooCommerce Installer
 * Handles WooCommerce dependency check, beautiful popup display,
 * and AJAX-based installation & activation.
 * The popup shows on EVERY admin page when WooCommerce is not active.
 *
 * @package TourBookingManager
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'TTBM_Woo_Installer' ) ) {

	class TTBM_Woo_Installer {

		/**
		 * Constructor – hooks into WordPress.
		 */
		public function __construct() {
			add_action( 'admin_init', array( $this, 'handle_activation_redirect' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			add_action( 'admin_footer', array( $this, 'render_popup' ) );
			// Single stepped endpoint: each request does ONE small unit of work
			// (download → extract → activate → setup) to keep peak memory/time low.
			add_action( 'wp_ajax_ttbm_woo_step', array( $this, 'ajax_woo_step' ) );
		}

		/**
		 * Transient key holding the downloaded package path between the
		 * "download" and "extract" steps, scoped to the current user.
		 */
		private function pkg_transient_key(): string {
			return 'ttbm_woo_pkg_' . get_current_user_id();
		}

		/**
		 * Check if WooCommerce plugin file exists (installed but maybe not active).
		 */
		private function is_woo_installed(): bool {
			return file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
		}

		/**
		 * Check if WooCommerce is active.
		 */
		private function is_woo_active(): bool {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
			return is_plugin_active( 'woocommerce/woocommerce.php' );
		}

		/**
		 * Runs on admin_init. If the transient from activation exists
		 * and WooCommerce IS active, redirect to tour list page.
		 */
		public function handle_activation_redirect() {
			if ( ! get_transient( 'ttbm_plugin_activated' ) ) {
				return;
			}

			if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
				delete_transient( 'ttbm_plugin_activated' );
				return;
			}

			// WooCommerce is active → redirect immediately
			if ( $this->is_woo_active() ) {
				delete_transient( 'ttbm_plugin_activated' );
				set_transient( 'ttbm_woo_just_activated', true, 300 );
				wp_safe_redirect( admin_url( 'edit.php?post_type=ttbm_tour&page=ttbm_list' ) );
				exit;
			}

			// WooCommerce is NOT active → clear transient, popup will show
			delete_transient( 'ttbm_plugin_activated' );
		}

		/**
		 * Should we show the popup on this page load?
		 *
		 * WooCommerce is now an optional integration, not a hard requirement, so
		 * this installer prompt is permanently disabled. The class stays loaded
		 * (dormant) rather than removed, in case a future release needs to
		 * reintroduce an opt-in "connect WooCommerce" prompt.
		 */
		private function should_show_popup(): bool {
			return false;
		}

		/**
		 * Enqueue CSS & JS for the popup only when needed.
		 */
		public function enqueue_assets() {
			if ( ! $this->should_show_popup() ) {
				return;
			}

			wp_enqueue_style(
				'ttbm-woo-installer',
				TTBM_PLUGIN_URL . '/assets/admin/ttbm_woo_installer.css',
				array(),
				filemtime( TTBM_PLUGIN_DIR . '/assets/admin/ttbm_woo_installer.css' )
			);

			wp_enqueue_script(
				'ttbm-woo-installer',
				TTBM_PLUGIN_URL . '/assets/admin/ttbm_woo_installer.js',
				array( 'jquery' ),
				filemtime( TTBM_PLUGIN_DIR . '/assets/admin/ttbm_woo_installer.js' ),
				true
			);

			wp_localize_script( 'ttbm-woo-installer', 'ttbm_woo_installer', array(
				'ajax_url'       => admin_url( 'admin-ajax.php' ),
				'step_nonce'     => wp_create_nonce( 'ttbm_woo_step' ),
				// First step: skip download/extract if the files are already present.
				'first_step'     => $this->is_woo_installed() ? 'activate' : 'download',
				'redirect_url'   => admin_url( 'edit.php?post_type=ttbm_tour&page=ttbm_list' ),
				'woo_installed'  => $this->is_woo_installed() ? 'yes' : 'no',
				'i18n'           => array(
					'downloading'    => __( 'Downloading WooCommerce...', 'tour-booking-manager' ),
					'extracting'     => __( 'Extracting files...', 'tour-booking-manager' ),
					'installing'     => __( 'Installing WooCommerce...', 'tour-booking-manager' ),
					'activating'     => __( 'Activating WooCommerce...', 'tour-booking-manager' ),
					'finishing'      => __( 'Finishing setup...', 'tour-booking-manager' ),
					'success'        => __( 'WooCommerce activated successfully!', 'tour-booking-manager' ),
					'redirecting'    => __( 'Redirecting...', 'tour-booking-manager' ),
					'error'          => __( 'Something went wrong. Please try again.', 'tour-booking-manager' ),
					'install_error'  => __( 'Installation failed. Please install WooCommerce manually.', 'tour-booking-manager' ),
					'activate_error' => __( 'Activation failed. Please activate WooCommerce manually.', 'tour-booking-manager' ),
				),
			) );
		}

		/**
		 * Render the popup HTML in admin footer.
		 */
		public function render_popup() {
			if ( ! $this->should_show_popup() ) {
				return;
			}

			$is_installed = $this->is_woo_installed();
			$btn_text     = $is_installed
				? __( 'Activate WooCommerce', 'tour-booking-manager' )
				: __( 'Install & Activate WooCommerce', 'tour-booking-manager' );
			?>
			<div id="ttbm-woo-overlay" class="ttbm-woo-overlay">
				<div class="ttbm-woo-popup">

					<div class="ttbm-woo-header">
						<div class="ttbm-woo-header-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
								<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						<span class="ttbm-woo-header-text"><?php esc_html_e( 'Tour Booking Manager', 'tour-booking-manager' ); ?></span>
					</div>

					<div class="ttbm-woo-icon-wrapper">
						<div class="ttbm-woo-icon">
							<svg width="40" height="40" viewBox="0 0 24 24" fill="none">
								<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
								<path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
						</div>
					</div>

					<div class="ttbm-woo-content">
						<h2 class="ttbm-woo-title"><?php esc_html_e( 'WooCommerce Required', 'tour-booking-manager' ); ?></h2>
						<p class="ttbm-woo-desc">
							<?php esc_html_e( 'Tour Booking Manager requires WooCommerce to manage tour bookings, ticket sales, and payments. Please install and activate WooCommerce to continue.', 'tour-booking-manager' ); ?>
						</p>
					</div>

					<div class="ttbm-woo-features">
						<div class="ttbm-woo-feature">
							<span class="ttbm-woo-feature-icon">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3 4.3L6 11.6 2.7 8.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
							<span><?php esc_html_e( 'Ticket selling & payments', 'tour-booking-manager' ); ?></span>
						</div>
						<div class="ttbm-woo-feature">
							<span class="ttbm-woo-feature-icon">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3 4.3L6 11.6 2.7 8.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
							<span><?php esc_html_e( 'Booking management', 'tour-booking-manager' ); ?></span>
						</div>
						<div class="ttbm-woo-feature">
							<span class="ttbm-woo-feature-icon">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3 4.3L6 11.6 2.7 8.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
							<span><?php esc_html_e( 'Hotel reservations', 'tour-booking-manager' ); ?></span>
						</div>
					</div>

					<div id="ttbm-woo-progress" class="ttbm-woo-progress" style="display:none;">
						<div class="ttbm-woo-progress-bar">
							<div id="ttbm-woo-progress-fill" class="ttbm-woo-progress-fill"></div>
						</div>
						<p id="ttbm-woo-status-text" class="ttbm-woo-status-text"></p>
					</div>

					<div class="ttbm-woo-actions">
						<button type="button" id="ttbm-woo-install-btn" class="ttbm-woo-btn ttbm-woo-btn-primary">
							<span class="ttbm-woo-btn-icon">
								<svg width="18" height="18" viewBox="0 0 20 20" fill="none">
									<path d="M10 3v10m0 0l-4-4m4 4l4-4M3 17h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</span>
							<span class="ttbm-woo-btn-text"><?php echo esc_html( $btn_text ); ?></span>
						</button>
						<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ); ?>" class="ttbm-woo-btn ttbm-woo-btn-secondary">
							<?php esc_html_e( 'Install Manually', 'tour-booking-manager' ); ?>
						</a>
					</div>

					<p class="ttbm-woo-footer-note">
						<svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="vertical-align: -2px; flex-shrink: 0;">
							<path d="M7 1a6 6 0 100 12A6 6 0 007 1zm0 8.5a.75.75 0 110-1.5.75.75 0 010 1.5zM7.75 6.25a.75.75 0 01-1.5 0V4a.75.75 0 011.5 0v2.25z" fill="currentColor"/>
						</svg>
						<?php esc_html_e( 'WooCommerce is free, open-source, and trusted by millions of stores worldwide.', 'tour-booking-manager' ); ?>
					</p>
				</div>
			</div>
			<?php
		}

		/**
		 * AJAX dispatcher: runs ONE step per request and tells the client
		 * which step to call next. Splitting the work this way keeps each
		 * request's peak memory and execution time low on constrained hosts.
		 */
		public function ajax_woo_step() {
			check_ajax_referer( 'ttbm_woo_step', 'nonce' );

			// Give this single, small unit of work headroom without forcing the
			// whole install+activate to fit one request.
			if ( function_exists( 'wp_raise_memory_limit' ) ) {
				wp_raise_memory_limit( 'admin' );
			}
			if ( function_exists( 'set_time_limit' ) ) {
				@set_time_limit( 120 );
			}

			$step = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';

			switch ( $step ) {
				case 'download':
					$this->step_download();
					break;
				case 'extract':
					$this->step_extract();
					break;
				case 'activate':
					$this->step_activate();
					break;
				case 'setup':
					$this->step_setup();
					break;
				default:
					wp_send_json_error( array( 'message' => __( 'Invalid installation step.', 'tour-booking-manager' ) ) );
			}
		}

		/**
		 * Step 1 — Download the WooCommerce package to a temp file.
		 * Uses download_url() which streams to disk (no large in-memory buffer).
		 */
		private function step_download() {
			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to install plugins.', 'tour-booking-manager' ) ) );
			}

			// Already present? Skip straight to activation.
			if ( $this->is_woo_installed() ) {
				wp_send_json_success( array(
					'next'    => 'activate',
					'percent' => 60,
					'message' => __( 'Activating WooCommerce...', 'tour-booking-manager' ),
				) );
			}

			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			include_once ABSPATH . 'wp-admin/includes/file.php';

			$api = plugins_api( 'plugin_information', array(
				'slug'   => 'woocommerce',
				'fields' => array(
					'short_description' => false,
					'sections'          => false,
					'requires'          => false,
					'rating'            => false,
					'ratings'           => false,
					'downloaded'        => false,
					'last_updated'      => false,
					'added'             => false,
					'tags'              => false,
					'compatibility'     => false,
					'homepage'          => false,
					'donate_link'       => false,
				),
			) );

			if ( is_wp_error( $api ) ) {
				wp_send_json_error( array( 'message' => $api->get_error_message() ) );
			}

			$tmp = download_url( $api->download_link );
			if ( is_wp_error( $tmp ) ) {
				wp_send_json_error( array( 'message' => $tmp->get_error_message() ) );
			}

			// Hand the package path to the extract step (short-lived, user-scoped).
			set_transient( $this->pkg_transient_key(), $tmp, 15 * MINUTE_IN_SECONDS );

			wp_send_json_success( array(
				'next'    => 'extract',
				'percent' => 40,
				'message' => __( 'Extracting files...', 'tour-booking-manager' ),
			) );
		}

		/**
		 * Step 2 — Extract the downloaded package into the plugins directory.
		 */
		private function step_extract() {
			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to install plugins.', 'tour-booking-manager' ) ) );
			}

			$tmp = get_transient( $this->pkg_transient_key() );
			if ( empty( $tmp ) || ! file_exists( $tmp ) ) {
				wp_send_json_error( array( 'message' => __( 'Download package not found. Please try again.', 'tour-booking-manager' ) ) );
			}

			include_once ABSPATH . 'wp-admin/includes/file.php';

			global $wp_filesystem;
			if ( ! WP_Filesystem() ) {
				@unlink( $tmp );
				delete_transient( $this->pkg_transient_key() );
				wp_send_json_error( array( 'message' => __( 'Unable to access the filesystem.', 'tour-booking-manager' ) ) );
			}

			$result = unzip_file( $tmp, WP_PLUGIN_DIR );

			// Clean up the temp package regardless of outcome.
			@unlink( $tmp );
			delete_transient( $this->pkg_transient_key() );

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message() ) );
			}

			if ( ! $this->is_woo_installed() ) {
				wp_send_json_error( array( 'message' => __( 'Installation failed. Please install WooCommerce manually.', 'tour-booking-manager' ) ) );
			}

			wp_send_json_success( array(
				'next'    => 'activate',
				'percent' => 60,
				'message' => __( 'Activating WooCommerce...', 'tour-booking-manager' ),
			) );
		}

		/**
		 * Step 3 — Activate WooCommerce silently. Silent activation skips firing
		 * the activation hook in this request, so the heavy WC_Install routine
		 * (tables, pages, cron) is deferred to the next, isolated step.
		 */
		private function step_activate() {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to activate plugins.', 'tour-booking-manager' ) ) );
			}

			if ( ! $this->is_woo_installed() ) {
				wp_send_json_error( array( 'message' => __( 'WooCommerce is not installed.', 'tour-booking-manager' ) ) );
			}

			if ( ! $this->is_woo_active() ) {
				// $silent = true → defer WC_Install to the setup step.
				$result = activate_plugin( 'woocommerce/woocommerce.php', '', false, true );
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( array( 'message' => $result->get_error_message() ) );
				}
			}

			wp_send_json_success( array(
				'next'    => 'setup',
				'percent' => 85,
				'message' => __( 'Finishing setup...', 'tour-booking-manager' ),
			) );
		}

		/**
		 * Step 4 — Run WooCommerce's own installer in its own request, where WC
		 * is already loaded as an active plugin. Isolating the DB/page/cron setup
		 * here keeps it off the activation request's memory budget.
		 */
		private function step_setup() {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to activate plugins.', 'tour-booking-manager' ) ) );
			}

			if ( ! $this->is_woo_active() ) {
				wp_send_json_error( array( 'message' => __( 'WooCommerce is not active.', 'tour-booking-manager' ) ) );
			}

			// WC is loaded normally on this request; run its idempotent installer
			// so tables/pages/cron are created deterministically.
			if ( class_exists( 'WC_Install' ) ) {
				WC_Install::install();
			}

			// Signal the demo import popup to auto-show on the next page load (tour list).
			set_transient( 'ttbm_woo_just_activated', true, 300 );

			wp_send_json_success( array(
				'next'    => 'done',
				'percent' => 100,
				'message' => __( 'WooCommerce activated successfully!', 'tour-booking-manager' ),
			) );
		}
	}

	new TTBM_Woo_Installer();
}
