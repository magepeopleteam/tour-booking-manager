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
				wp_enqueue_style( 'ttbm-status-page', TTBM_PLUGIN_URL . '/assets/admin/ttbm-status-page.css', [], TTBM_PLUGIN_VERSION );
			}
			public function add_status_section( $sections ) {
				$sections[] = array(
					'id'   => 'ttbm_status_settings',
					'title' => __( 'Status', 'tour-booking-manager' ),
					'icon' => 'mi mi-monitor',
					'desc' => __( 'System requirements and configuration check for Tour Booking Manager.', 'tour-booking-manager' ),
				);
				return $sections;
			}
			public function render_status_content() {
				$wp_v       = get_bloginfo( 'version' );
				$wc_v       = function_exists( 'WC' ) ? WC()->version : '';
				$wc_i       = TTBM_Global_Function::check_woocommerce();
				$from_name  = get_option( 'woocommerce_email_from_name' );
				$from_email = get_option( 'woocommerce_email_from_address' );
				?>
				<?php do_action( 'ttbm_status_notice_sec' ); ?>
				<div class="ttbm-status-body" style="padding:16px 0;">
					<table class="ttbm-status-table">
						<tbody>
							<tr class="ttbm-status-section-row">
								<td colspan="2"><i class="fab fa-wordpress"></i> <?php esc_html_e( 'WordPress', 'tour-booking-manager' ); ?></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'WordPress Version', 'tour-booking-manager' ); ?></td>
								<td>
									<?php if ( $wp_v > 5.5 ) {
										echo '<span class="ttbm-sbadge ttbm-sbadge-ok"><i class="far fa-check-circle"></i>' . esc_html( $wp_v ) . '</span>';
									} else {
										echo '<span class="ttbm-sbadge ttbm-sbadge-warn"><i class="fas fa-exclamation-triangle"></i>' . esc_html( $wp_v ) . '</span>';
									} ?>
								</td>
							</tr>
							<tr class="ttbm-status-section-row">
								<td colspan="2"><i class="fas fa-shopping-cart"></i> <?php esc_html_e( 'WooCommerce', 'tour-booking-manager' ); ?></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'WooCommerce Installed', 'tour-booking-manager' ); ?></td>
								<td>
									<?php if ( $wc_i == 1 ) {
										echo '<span class="ttbm-sbadge ttbm-sbadge-ok"><i class="far fa-check-circle"></i>' . esc_html__( 'Yes', 'tour-booking-manager' ) . '</span>';
									} else {
										echo '<span class="ttbm-sbadge ttbm-sbadge-warn"><i class="fas fa-exclamation-triangle"></i>' . esc_html__( 'No', 'tour-booking-manager' ) . '</span>';
									} ?>
								</td>
							</tr>
							<?php if ( $wc_i == 1 && $wc_v ) { ?>
								<tr>
									<td><?php esc_html_e( 'WooCommerce Version', 'tour-booking-manager' ); ?></td>
									<td>
										<?php if ( $wc_v > 4.8 ) {
											echo '<span class="ttbm-sbadge ttbm-sbadge-ok"><i class="far fa-check-circle"></i>' . esc_html( $wc_v ) . '</span>';
										} else {
											echo '<span class="ttbm-sbadge ttbm-sbadge-warn"><i class="fas fa-exclamation-triangle"></i>' . esc_html( $wc_v ) . '</span>';
										} ?>
									</td>
								</tr>
								<tr>
									<td><?php esc_html_e( 'Email From Name', 'tour-booking-manager' ); ?></td>
									<td>
										<?php if ( $from_name ) {
											echo '<span class="ttbm-sbadge ttbm-sbadge-ok"><i class="far fa-check-circle"></i>' . esc_html( $from_name ) . '</span>';
										} else {
											echo '<span class="ttbm-sbadge ttbm-sbadge-warn"><i class="fas fa-exclamation-triangle"></i>' . esc_html__( 'Not set', 'tour-booking-manager' ) . '</span>';
										} ?>
									</td>
								</tr>
								<tr>
									<td><?php esc_html_e( 'From Email Address', 'tour-booking-manager' ); ?></td>
									<td>
										<?php if ( $from_email ) {
											echo '<span class="ttbm-sbadge ttbm-sbadge-ok"><i class="far fa-check-circle"></i>' . esc_html( $from_email ) . '</span>';
										} else {
											echo '<span class="ttbm-sbadge ttbm-sbadge-warn"><i class="fas fa-exclamation-triangle"></i>' . esc_html__( 'Not set', 'tour-booking-manager' ) . '</span>';
										} ?>
									</td>
								</tr>
							<?php } ?>
							<tr class="ttbm-status-section-row">
								<td colspan="2"><i class="fas fa-plug"></i> <?php esc_html_e( 'Extensions & Server', 'tour-booking-manager' ); ?></td>
							</tr>
							<?php do_action( 'ttbm_status_table_item_sec' ); ?>
						</tbody>
					</table>
				</div>
				<?php
			}
		}
		new TTBM_Status();
	}
