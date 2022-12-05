<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'TTBM_Quick_Setup' ) ) {
		class TTBM_Quick_Setup {
			public function __construct() {
				if ( ! class_exists( 'TTBM_Dependencies' ) ) {
					add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts' ), 10, 1 );
				}
				add_action( 'admin_menu', array( $this, 'quick_setup_menu' ) );
				add_action( 'ttbm_quick_setup_content_start', array( $this, 'setup_welcome_content' ) );
				add_action( 'ttbm_quick_setup_content_general', array( $this, 'setup_general_content' ) );
				add_action( 'ttbm_quick_setup_content_done', array( $this, 'setup_content_done' ) );
			}
			public function add_admin_scripts() {
				wp_enqueue_script( 'mp_admin_settings', TTBM_PLUGIN_URL . '/assets/admin/mp_admin_settings.js', array( 'jquery' ), time(), true );
				wp_enqueue_style( 'mp_admin_settings', TTBM_PLUGIN_URL . '/assets/admin/mp_admin_settings.css', array(), time() );
				wp_enqueue_script( 'ttbm_admin_script', TTBM_PLUGIN_URL . '/assets/admin/ttbm_admin_script.js', array( 'jquery' ), time(), true );
				wp_enqueue_style( 'ttbm_admin_style', TTBM_PLUGIN_URL . '/assets/admin/ttbm_admin_style.css', array(), time() );
				wp_enqueue_style( 'mp_font_awesome', '//cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.3/css/all.min.css', array(), '5.15.3' );
			}
			public function quick_setup_menu() {
				$status = TTBM_Woocommerce_Plugin::check_woocommerce();
				if ( $status == 1 ) {
					add_submenu_page( 'edit.php?post_type=ttbm_tour', esc_html__( 'Quick Setup', 'tour-booking-manager' ), '<span style="color:#10dd10">' . esc_html__( 'Quick Setup', 'tour-booking-manager' ) . '</span>', 'manage_options', 'ttbm_quick_setup', array( $this, 'quick_setup' ) );
					add_submenu_page( 'ttbm_tour', esc_html__( 'Quick Setup', 'tour-booking-manager' ), '<span style="color:#10dd10">' . esc_html__( 'Quick Setup', 'tour-booking-manager' ) . '</span>', 'manage_options', 'ttbm_quick_setup', array( $this, 'quick_setup' ) );
				} else {
					add_menu_page( esc_html__( 'Tour', 'tour-booking-manager' ), esc_html__( 'Tour', 'tour-booking-manager' ), 'manage_options', 'ttbm_tour', array( $this, 'quick_setup' ), 'dashicons-admin-site-alt2', 6 );
					add_submenu_page( 'ttbm_tour', esc_html__( 'Quick Setup', 'tour-booking-manager' ), '<span style="color:#10dd17">' . esc_html__( 'Quick Setup', 'tour-booking-manager' ) . '</span>', 'manage_options', 'ttbm_quick_setup', array( $this, 'quick_setup' ) );
				}
			}
			public function quick_setup() {
				$mep_settings_tab   = array();
				$mep_settings_tab[] = array(
					'id'       => 'start',
					'title'    => '<i class="far fa-thumbs-up"></i>' . esc_html__( 'Welcome', 'tour-booking-manager' ),
					'priority' => 1,
					'active'   => true,
				);
				$mep_settings_tab[] = array(
					'id'       => 'general',
					'title'    => '<i class="fas fa-list-ul"></i>' . esc_html__( 'General', 'tour-booking-manager' ),
					'priority' => 2,
					'active'   => false,
				);
				$mep_settings_tab[] = array(
					'id'       => 'done',
					'title'    => '<i class="fas fa-pencil-alt"></i>' . esc_html__( 'Done', 'tour-booking-manager' ),
					'priority' => 4,
					'active'   => false,
				);
				$mep_settings_tab   = apply_filters( 'qa_welcome_tabs', $mep_settings_tab );
				$tabs_sorted        = array();
				foreach ( $mep_settings_tab as $page_key => $tab ) {
					$tabs_sorted[ $page_key ] = $tab['priority'] ?? 0;
				}
				array_multisort( $tabs_sorted, SORT_ASC, $mep_settings_tab );
				if ( isset( $_POST['active_woo_btn'] ) ) {
					?>
					<script>
								  defaultLoaderBody();
					</script>
					<?php
					activate_plugin( 'woocommerce/woocommerce.php' );
					?>
					<script>
								  let ttbm_admin_location = window.location.href;
								  ttbm_admin_location = ttbm_admin_location.replace('admin.php?page=ttbm_tour', 'edit.php?post_type=ttbm_tour&page=ttbm_quick_setup');
								  window.location.href = ttbm_admin_location;
					</script>
					<?php
				}
				if ( isset( $_POST['install_and_active_woo_btn'] ) ) {
					echo '<div style="display:none">';
					include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' ); //for plugins_api..
					$plugin = 'woocommerce';
					$api    = plugins_api( 'plugin_information', array(
						'slug'   => $plugin,
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
					//includes necessary for Plugin_Upgrader and Plugin_Installer_Skin
					include_once( ABSPATH . 'wp-admin/includes/file.php' );
					include_once( ABSPATH . 'wp-admin/includes/misc.php' );
					include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
					$woocommerce_plugin = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) );
					$woocommerce_plugin->install( $api->download_link );
					activate_plugin( 'woocommerce/woocommerce.php' );
					echo '</div>';
					?>
					<script>
								  let ttbm_admin_location = window.location.href;
								  ttbm_admin_location = ttbm_admin_location.replace('admin.php?page=ttbm_tour', 'edit.php?post_type=ttbm_tour&page=ttbm_quick_setup');
								  window.location.href = ttbm_admin_location;
					</script>
					<?php
				}
				if ( isset( $_POST['finish_quick_setup'] ) ) {
					$event_label                 = isset( $_POST['ttbm_travel_label'] ) ? sanitize_text_field( $_POST['ttbm_travel_label'] ) : 'Events';
					$event_slug                  = isset( $_POST['ttbm_travel_slug'] ) ? sanitize_text_field( $_POST['ttbm_travel_slug'] ) : 'event';
					$general_settings_data       = get_option( 'ttbm_basic_gen_settings' );
					$update_general_settings_arr = [
						'ttbm_travel_label' => $event_label,
						'ttbm_travel_slug'  => $event_slug
					];
					$new_general_settings_data   = is_array( $general_settings_data ) ? array_replace( $general_settings_data, $update_general_settings_arr ) : $update_general_settings_arr;
					update_option( 'ttbm_basic_gen_settings', $new_general_settings_data );
					flush_rewrite_rules();
					wp_redirect( admin_url( 'edit.php?post_type=ttbm_tour&page=ttbm_welcome_page' ) );
				}
				?>
				<div id="ttbm_quick_setup" class="wrap">
					<div id="icon-tools" class="icon32"><br></div>
					<h2></h2>
					<form method="post" action="">
						<input type="hidden" name="qa_hidden" value="Y">
						<div class="welcome-tabs">
							<ul class="tab-navs">
								<?php
									foreach ( $mep_settings_tab as $tab ) {
										$id     = $tab['id'];
										$title  = $tab['title'];
										$active = $tab['active'];
										$hidden = $tab['hidden'] ?? false;
										?>
										<li class="tab-nav <?php echo $active ? 'active' : ''; ?> <?php echo $hidden ? 'hidden' : ''; ?> " data-id="<?php echo esc_html( $id ); ?>"><?php echo $title; ?></li>
									<?php } ?>
							</ul>
							<?php
								foreach ( $mep_settings_tab as $tab ) {
									$id     = $tab['id'];
									$active = $tab['active'];
									?>
									<div class="tab-content <?php echo $active ? 'active' : ''; ?>" id="<?php echo esc_html( $id ); ?>">
										<?php do_action( 'ttbm_quick_setup_content_' . $id ); ?>
										<?php do_action( 'ttbm_quick_setup_content_after', $tab ); ?>
									</div>
								<?php } ?>
							<div class="next-prev">
								<div class="prev"><span>&longleftarrow;<?php esc_html_e( 'Previous', 'tour-booking-manager' ); ?></span></div>
								<div class="next"><span><?php esc_html_e( 'Next', 'tour-booking-manager' ); ?>&longrightarrow;</span></div>
							</div>
						</div>
					</form>
				</div>
				<?php
			}
			public function setup_welcome_content() {
				$status = TTBM_Woocommerce_Plugin::check_woocommerce();
				?>
				<h2><?php esc_html_e( 'Tour Booking Manager For Woocommerce Plugin', 'tour-booking-manager' ); ?></h2>
				<p><?php esc_html_e( 'Thanks for choosing Tour Booking Manager Plugin for WooCommerce for your site, Please go step by step and choose some options to get started.', 'tour-booking-manager' ); ?></p>
				<table class="wc_status_table widefat" id="status">
					<tr>
						<td data-export-label="WC Version">
							<?php if ( $status == 1 ) {
								esc_html_e( 'Woocommerce already installed and activated', 'tour-booking-manager' );
							} elseif ( $status == 0 ) {
								esc_html_e( 'Woocommerce need to install and active', 'tour-booking-manager' );
							} else {
								esc_html_e( 'Woocommerce already install , please activate it', 'tour-booking-manager' );
							} ?>
						</td>
						<td class="help"><span class="woocommerce-help-tip"></span></td>
						<td class="woo_btn_td">
							<?php if ( $status == 1 ) { ?>
								<span class="fas fa-check-circle"></span>
							<?php } elseif ( $status == 0 ) { ?>
								<button class="button" type="submit" name="install_and_active_woo_btn"><?php esc_html_e( 'Install & Active Now', 'tour-booking-manager' ); ?></button>
							<?php } else { ?>
								<button class="button" type="submit" name="active_woo_btn"><?php esc_html_e( 'Active Now', 'tour-booking-manager' ); ?></button>
							<?php } ?>
						</td>
					</tr>
				</table>
				<?php
			}
			public function setup_general_content() {
				$general_data = get_option( 'ttbm_basic_gen_settings' );
				$label        = $general_data['ttbm_travel_label'] ?? 'Travel';
				$slug         = $general_data['ttbm_travel_slug'] ?? 'travel';
				?>
				<div class="section">
					<h2><?php esc_html_e( 'General settings', 'tour-booking-manager' ); ?></h2>
					<p class="description section-description"><?php echo __( 'Choose some general option.', 'tour-booking-manager' ); ?></p>
					<table class="wc_status_table widefat" id="status">
						<tr>
							<td><?php esc_html_e( 'Travel Label:', 'tour-booking-manager' ); ?></td>
							<td>
								<label><input type="text" name="ttbm_travel_label" value='<?php echo esc_html( $label ); ?>'/></label>
								<p class="info"><?php esc_html_e( 'It will change the Tour post type label on the entire plugin.', 'tour-booking-manager' ); ?></p>
							</td>
						</tr>
						<tr>
							<td><?php esc_html_e( 'Travel Slug:', 'tour-booking-manager' ); ?></td>
							<td>
								<label><input type="text" name="ttbm_travel_slug" value='<?php echo esc_html( $slug ); ?>'/></label>
								<p class="info"><?php esc_html_e( 'It will change the Tour slug on the entire plugin. Remember after changing this slug you need to flush permalinks. Just go to Settings->Permalinks hit the Save Settings button', 'tour-booking-manager' ); ?></p>
							</td>
						</tr>
					</table>
				</div>
				<?php
			}
			public function setup_content_done() {
				?>
				<div class="section">
					<h2><?php esc_html_e( 'Finalize Setup', 'tour-booking-manager' ); ?></h2>
					<p class="description section-description"><?php esc_html_e( 'You are about to Finish & Save Tour Booking Manager For Woocommerce Plugin setup process', 'tour-booking-manager' ); ?></p>
					<div class="setup_save_finish_area">
						<button type="submit" name="finish_quick_setup" class="button setup_save_finish"><?php esc_html_e( 'Finish & Save', 'tour-booking-manager' ); ?></button>
					</div>
				</div>
				<?php
			}
		}
		new TTBM_Quick_Setup();
	}