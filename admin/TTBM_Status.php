<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'TTBM_Status' ) ) {
		class TTBM_Status {
			public function __construct() {
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
				add_filter( 'ttbm_settings_sec_reg', array( $this, 'add_status_section' ), 999 );
				add_action( 'wsa_form_bottom_ttbm_status_settings', array( $this, 'render_status_content' ) );
			}
			public function enqueue_assets( $hook ) {
				if ( strpos( $hook, 'ttbm_settings_page' ) === false ) {
					return;
				}
				wp_enqueue_style( 'ttbm-status-page', TTBM_PLUGIN_URL . '/assets/admin/ttbm-status-page.css', array(), filemtime( TTBM_PLUGIN_DIR . '/assets/admin/ttbm-status-page.css' ) );
			}
			public function add_status_section( $sections ) {
				$sections[] = array(
					'id'   => 'ttbm_status_settings',
					'title' => __( 'Status', 'tour-booking-manager' ),
					'icon' => 'dashicons dashicons-performance',
					'desc' => __( 'System requirements and configuration check for Tour Booking Manager.', 'tour-booking-manager' ),
				);
				return $sections;
			}
			public function render_status_content() {
				global $wpdb;
				$wp_version     = get_bloginfo( 'version' );
				$wc_version     = defined( 'WC_VERSION' ) ? WC_VERSION : '';
				$wc_installed   = TTBM_Global_Function::check_woocommerce() == 1;
				$active_plugins = (array) get_option( 'active_plugins', array() );
				$theme          = wp_get_theme();
				$server         = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : __( 'Unknown', 'tour-booking-manager' );
				$memory_limit   = ini_get( 'memory_limit' ) ?: __( 'Unknown', 'tour-booking-manager' );
				$upload_limit   = size_format( wp_max_upload_size() );
				$php_ok         = version_compare( PHP_VERSION, '7.4', '>=' );
				$wp_ok          = version_compare( $wp_version, '6.0', '>=' );
				$wc_ok          = $wc_installed && version_compare( $wc_version, '7.0', '>=' );
				$writable       = wp_is_writable( WP_CONTENT_DIR );
				$required_ext   = array( 'curl', 'dom', 'json', 'mbstring', 'openssl' );
				$missing_ext    = array_values( array_filter( $required_ext, function ( $extension ) {
					return ! extension_loaded( $extension );
				} ) );
				$checks         = array( $php_ok, $wp_ok, $wc_ok, $writable, empty( $missing_ext ) );
				$passed         = count( array_filter( $checks ) );
				$warnings       = count( $checks ) - $passed;

				$cards = array(
					array(
						'title' => __( 'WordPress', 'tour-booking-manager' ),
						'icon'  => 'dashicons dashicons-wordpress',
						'rows'  => array(
							array( __( 'WordPress Version', 'tour-booking-manager' ), $wp_version, $wp_ok ? 'ok' : 'warn' ),
							array( __( 'Site URL', 'tour-booking-manager' ), home_url(), 'neutral' ),
							array( __( 'Site Language', 'tour-booking-manager' ), get_locale(), 'neutral' ),
							array( __( 'Timezone', 'tour-booking-manager' ), wp_timezone_string() ?: 'UTC', 'neutral' ),
							array( __( 'Multisite', 'tour-booking-manager' ), is_multisite() ? __( 'Enabled', 'tour-booking-manager' ) : __( 'Disabled', 'tour-booking-manager' ), 'neutral' ),
							array( __( 'Debug Mode', 'tour-booking-manager' ), defined( 'WP_DEBUG' ) && WP_DEBUG ? __( 'Enabled', 'tour-booking-manager' ) : __( 'Disabled', 'tour-booking-manager' ), defined( 'WP_DEBUG' ) && WP_DEBUG ? 'warn' : 'ok' ),
							array( __( 'Content Directory', 'tour-booking-manager' ), $writable ? __( 'Writable', 'tour-booking-manager' ) : __( 'Not writable', 'tour-booking-manager' ), $writable ? 'ok' : 'error' ),
						),
					),
					array(
						'title' => __( 'PHP', 'tour-booking-manager' ),
						'icon'  => 'dashicons dashicons-editor-code',
						'rows'  => array(
							array( __( 'PHP Version', 'tour-booking-manager' ), PHP_VERSION, $php_ok ? 'ok' : 'error' ),
							array( __( 'PHP SAPI', 'tour-booking-manager' ), PHP_SAPI, 'neutral' ),
							array( __( 'Memory Limit', 'tour-booking-manager' ), $memory_limit, wp_convert_hr_to_bytes( $memory_limit ) >= 128 * MB_IN_BYTES ? 'ok' : 'warn' ),
							array( __( 'Maximum Execution Time', 'tour-booking-manager' ), (string) ini_get( 'max_execution_time' ) . 's', (int) ini_get( 'max_execution_time' ) === 0 || (int) ini_get( 'max_execution_time' ) >= 60 ? 'ok' : 'warn' ),
							array( __( 'Maximum Input Variables', 'tour-booking-manager' ), (string) ini_get( 'max_input_vars' ), (int) ini_get( 'max_input_vars' ) >= 1000 ? 'ok' : 'warn' ),
							array( __( 'Post Maximum Size', 'tour-booking-manager' ), (string) ini_get( 'post_max_size' ), 'neutral' ),
							array( __( 'Upload Maximum', 'tour-booking-manager' ), $upload_limit, 'neutral' ),
						),
					),
					array(
						'title' => __( 'Server & Database', 'tour-booking-manager' ),
						'icon'  => 'dashicons dashicons-database',
						'rows'  => array(
							array( __( 'Operating System', 'tour-booking-manager' ), PHP_OS_FAMILY, 'neutral' ),
							array( __( 'Web Server', 'tour-booking-manager' ), $server, 'neutral' ),
							array( __( 'Database Version', 'tour-booking-manager' ), $wpdb->db_version(), 'neutral' ),
							array( __( 'Database Charset', 'tour-booking-manager' ), $wpdb->charset ?: __( 'Default', 'tour-booking-manager' ), 'neutral' ),
							array( __( 'HTTPS', 'tour-booking-manager' ), is_ssl() ? __( 'Active', 'tour-booking-manager' ) : __( 'Not active', 'tour-booking-manager' ), is_ssl() ? 'ok' : 'warn' ),
							array( __( 'WordPress Cron', 'tour-booking-manager' ), defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ? __( 'Disabled', 'tour-booking-manager' ) : __( 'Enabled', 'tour-booking-manager' ), defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ? 'warn' : 'ok' ),
						),
					),
					array(
						'title' => __( 'Plugins & Theme', 'tour-booking-manager' ),
						'icon'  => 'dashicons dashicons-admin-plugins',
						'rows'  => array(
							array( __( 'TTBM Core', 'tour-booking-manager' ), defined( 'TTBM_PLUGIN_VERSION' ) ? TTBM_PLUGIN_VERSION : __( 'Unknown', 'tour-booking-manager' ), 'ok' ),
							array( __( 'WooCommerce', 'tour-booking-manager' ), $wc_installed ? $wc_version : __( 'Not installed', 'tour-booking-manager' ), $wc_ok ? 'ok' : 'error' ),
							array( __( 'Active Plugins', 'tour-booking-manager' ), (string) count( $active_plugins ), 'neutral' ),
							array( __( 'Active Theme', 'tour-booking-manager' ), $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ), 'neutral' ),
							array( __( 'WooCommerce Email Sender', 'tour-booking-manager' ), get_option( 'woocommerce_email_from_address' ) ?: __( 'Not configured', 'tour-booking-manager' ), get_option( 'woocommerce_email_from_address' ) ? 'ok' : 'warn' ),
						),
					),
				);
				?>
				<div id="ttbm_status_settings" class="ttbm-status-settings-panel">
					<?php do_action( 'ttbm_status_notice_sec' ); ?>
					<div class="ttbm-status-dashboard">
					<div class="ttbm-status-hero">
						<div class="ttbm-status-hero__icon"><i class="dashicons dashicons-performance"></i></div>
						<div class="ttbm-status-hero__copy">
							<span class="ttbm-status-eyebrow"><?php esc_html_e( 'Environment health', 'tour-booking-manager' ); ?></span>
							<h2><?php esc_html_e( 'System Status', 'tour-booking-manager' ); ?></h2>
							<p><?php esc_html_e( 'A diagnostic overview of the services and limits used by Tour Booking Manager.', 'tour-booking-manager' ); ?></p>
						</div>
						<div class="ttbm-status-score <?php echo $warnings ? 'has-warnings' : 'is-healthy'; ?>">
							<strong><?php echo esc_html( $passed . '/' . count( $checks ) ); ?></strong>
							<span><?php echo $warnings ? esc_html( sprintf( _n( '%d warning', '%d warnings', $warnings, 'tour-booking-manager' ), $warnings ) ) : esc_html__( 'All checks passed', 'tour-booking-manager' ); ?></span>
						</div>
					</div>
					<div class="ttbm-status-quick-stats">
						<div><span><?php esc_html_e( 'PHP', 'tour-booking-manager' ); ?></span><strong><?php echo esc_html( PHP_VERSION ); ?></strong></div>
						<div><span><?php esc_html_e( 'WordPress', 'tour-booking-manager' ); ?></span><strong><?php echo esc_html( $wp_version ); ?></strong></div>
						<div><span><?php esc_html_e( 'WooCommerce', 'tour-booking-manager' ); ?></span><strong><?php echo esc_html( $wc_installed ? $wc_version : __( 'Missing', 'tour-booking-manager' ) ); ?></strong></div>
						<div><span><?php esc_html_e( 'Upload Limit', 'tour-booking-manager' ); ?></span><strong><?php echo esc_html( $upload_limit ); ?></strong></div>
					</div>
					<?php if ( ! empty( $missing_ext ) ) { ?>
						<div class="ttbm-status-alert"><i class="dashicons dashicons-warning"></i><div><strong><?php esc_html_e( 'Missing recommended PHP extensions', 'tour-booking-manager' ); ?></strong><span><?php echo esc_html( implode( ', ', $missing_ext ) ); ?></span></div></div>
					<?php } ?>
					<div class="ttbm-status-grid">
						<?php foreach ( $cards as $card ) { ?>
							<section class="ttbm-status-card">
								<header><span class="ttbm-status-card__icon"><i class="<?php echo esc_attr( $card['icon'] ); ?>"></i></span><h3><?php echo esc_html( $card['title'] ); ?></h3></header>
								<div class="ttbm-status-card__rows">
									<?php foreach ( $card['rows'] as $row ) { ?>
										<div class="ttbm-status-row"><span><?php echo esc_html( $row[0] ); ?></span><strong class="is-<?php echo esc_attr( $row[2] ); ?>"><?php echo esc_html( $row[1] ); ?></strong></div>
									<?php } ?>
								</div>
							</section>
						<?php } ?>
					</div>
					<div class="ttbm-status-extensions">
						<h3><i class="dashicons dashicons-admin-plugins"></i> <?php esc_html_e( 'Addon Checks', 'tour-booking-manager' ); ?></h3>
						<table class="ttbm-status-table"><tbody><?php do_action( 'ttbm_status_table_item_sec' ); ?></tbody></table>
					</div>
					</div>
				</div>
				<?php
			}
		}
		new TTBM_Status();
	}
