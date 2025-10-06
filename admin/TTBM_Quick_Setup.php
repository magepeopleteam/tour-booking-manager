<?php
/*
* Quick Setup Wizard for Tour Booking Manager
* Copyright: mage-people.com
*/
if (!defined('ABSPATH')) {
	die;
} // Cannot access pages directly.

if (!class_exists('TTBM_Quick_Setup')) {
	class TTBM_Quick_Setup {
		public function __construct() {
			add_action('admin_menu', array($this, 'register_quick_setup_menu'));
			add_action('admin_enqueue_scripts', array($this, 'enqueue_quick_setup_assets'));
			add_action('admin_notices', array($this, 'show_setup_notice'));
			add_action('wp_ajax_ttbm_install_woocommerce', array($this, 'ajax_install_woocommerce'));
			add_action('wp_ajax_ttbm_activate_woocommerce', array($this, 'ajax_activate_woocommerce'));
			add_action('wp_ajax_ttbm_save_labels_slugs', array($this, 'ajax_save_labels_slugs'));
			add_action('wp_ajax_ttbm_finish_setup', array($this, 'ajax_finish_setup'));
			add_action('wp_ajax_ttbm_dismiss_setup_notice', array($this, 'ajax_dismiss_setup_notice'));
		}

		/**
		 * Register admin menu for quick setup
		 */
		public function register_quick_setup_menu() {
			$woo_status = TTBM_Global_Function::check_woocommerce();
			$setup_done = get_option('ttbm_quick_setup_done', 'no');
			
			// Always add main menu if setup is not done OR WooCommerce is not active
			if ($setup_done == 'no' || $woo_status != 1) {
				add_menu_page(
					esc_html__('Tour Booking', 'tour-booking-manager'),
					esc_html__('Tour Booking', 'tour-booking-manager'),
					'manage_options',
					'ttbm_quick_setup',
					array($this, 'render_quick_setup_page'),
					'dashicons-admin-site-alt2',
					30
				);
			}
			
			// Also add as submenu if WooCommerce is active (regardless of setup status)
			if ($woo_status == 1) {
				add_submenu_page(
					'edit.php?post_type=ttbm_tour',
					esc_html__('Quick Setup', 'tour-booking-manager'),
					esc_html__('Quick Setup', 'tour-booking-manager'),
					'manage_options',
					'ttbm_quick_setup',
					array($this, 'render_quick_setup_page')
				);
			}
		}


		/**
		 * Show admin notice for quick setup
		 */
		public function show_setup_notice() {
			// Only show in admin
			if (!is_admin()) {
				return;
			}

			// Don't show if setup is done
			if (get_option('ttbm_quick_setup_done', 'no') == 'yes') {
				return;
			}

			// Don't show if user dismissed the notice
			if (get_option('ttbm_setup_notice_dismissed', 'no') == 'yes') {
				return;
			}

			// Don't show on the quick setup page itself
			if (isset($_GET['page']) && $_GET['page'] == 'ttbm_quick_setup') {
				return;
			}

			$setup_url = admin_url('admin.php?page=ttbm_quick_setup');
			?>
			<div class="notice notice-info is-dismissible ttbm-setup-notice" data-dismiss-action="ttbm_dismiss_setup_notice">
				<p>
					<strong><?php esc_html_e('Welcome to WpTravelly!', 'tour-booking-manager'); ?></strong>
					<?php esc_html_e('Complete the quick setup wizard to get started with your tour booking system.', 'tour-booking-manager'); ?>
				</p>
				<p>
					<a href="<?php echo esc_url($setup_url); ?>" class="button button-primary">
						<?php esc_html_e('Start Setup Wizard', 'tour-booking-manager'); ?>
					</a>
					<a href="#" class="button button-secondary ttbm-dismiss-setup-notice">
						<?php esc_html_e('Maybe Later', 'tour-booking-manager'); ?>
					</a>
				</p>
			</div>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					$('.ttbm-dismiss-setup-notice, .ttbm-setup-notice .notice-dismiss').on('click', function(e) {
						e.preventDefault();
						$.post(ajaxurl, {
							action: 'ttbm_dismiss_setup_notice',
							nonce: '<?php echo wp_create_nonce('ttbm_dismiss_notice'); ?>'
						});
						$('.ttbm-setup-notice').fadeOut();
					});
				});
			</script>
			<style>
				.ttbm-setup-notice p:first-child {
					margin-bottom: 10px;
				}
				.ttbm-setup-notice .button {
					margin-right: 10px;
				}
			</style>
			<?php
		}

		/**
		 * AJAX: Dismiss setup notice
		 */
		public function ajax_dismiss_setup_notice() {
			check_ajax_referer('ttbm_dismiss_notice', 'nonce');
			update_option('ttbm_setup_notice_dismissed', 'yes');
			wp_send_json_success();
		}

		/**
		 * Enqueue assets for quick setup page
		 */
		public function enqueue_quick_setup_assets($hook) {
			// Only load on our quick setup page
			if (strpos($hook, 'ttbm_quick_setup') === false) {
				return;
			}

			// Enqueue with timestamp to avoid cache issues and higher priority
			$version = '1.0.' . filemtime(TTBM_PLUGIN_DIR . '/assets/admin/ttbm_quick_setup.css');
			wp_enqueue_style('ttbm-quick-setup-css', TTBM_PLUGIN_URL . '/assets/admin/ttbm_quick_setup.css', array(), $version, 'all');
			
			// Add inline style to ensure button fixes
			$inline_css = '
			.ttbm-quick-setup-wrapper .ttbm-btn {
				min-height: 40px !important;
				padding: 10px 25px !important;
			}
			';
			wp_add_inline_style('ttbm-quick-setup-css', $inline_css);
			
			wp_enqueue_script('ttbm-quick-setup-js', TTBM_PLUGIN_URL . '/assets/admin/ttbm_quick_setup.js', array('jquery'), $version, true);
			
			wp_localize_script('ttbm-quick-setup-js', 'ttbmQuickSetup', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('ttbm_quick_setup_nonce'),
				'woo_status' => TTBM_Global_Function::check_woocommerce(),
				'texts' => array(
					'installing' => esc_html__('Installing WooCommerce...', 'tour-booking-manager'),
					'activating' => esc_html__('Activating WooCommerce...', 'tour-booking-manager'),
					'error' => esc_html__('An error occurred. Please try again.', 'tour-booking-manager'),
				)
			));
		}

		/**
		 * Render quick setup page
		 */
		public function render_quick_setup_page() {
			$woo_status = TTBM_Global_Function::check_woocommerce();
			
			// Get existing settings from the plugin's settings array
			$settings = get_option('ttbm_basic_gen_settings', array());
			
			// Handle case where settings might not be an array
			if (!is_array($settings)) {
				$settings = array();
			}
			
			$tour_label = isset($settings['ttbm_travel_label']) ? $settings['ttbm_travel_label'] : 'Tour';
			$tour_slug = isset($settings['ttbm_travel_slug']) ? $settings['ttbm_travel_slug'] : 'tour';
			?>
			<div class="ttbm-quick-setup-wrapper">
				<div class="ttbm-quick-setup-container">
					<div class="ttbm-setup-header">
						<h1><?php esc_html_e('Welcome to WpTravelly', 'tour-booking-manager'); ?></h1>
						<p><?php esc_html_e('Let\'s set up your tour booking system in just a few steps!', 'tour-booking-manager'); ?></p>
					</div>

					<!-- Progress Steps -->
					<div class="ttbm-setup-progress">
						<div class="ttbm-step <?php echo ($woo_status == 1) ? 'completed' : 'active'; ?>" data-step="1">
							<div class="ttbm-step-number">1</div>
							<div class="ttbm-step-title"><?php esc_html_e('WooCommerce', 'tour-booking-manager'); ?></div>
						</div>
						<div class="ttbm-step" data-step="2">
							<div class="ttbm-step-number">2</div>
							<div class="ttbm-step-title"><?php esc_html_e('Labels & Slugs', 'tour-booking-manager'); ?></div>
						</div>
						<div class="ttbm-step" data-step="3">
							<div class="ttbm-step-number">3</div>
							<div class="ttbm-step-title"><?php esc_html_e('Finish', 'tour-booking-manager'); ?></div>
						</div>
					</div>

					<!-- Step Content -->
					<div class="ttbm-setup-content">
						<!-- Step 1: WooCommerce -->
						<div class="ttbm-step-content <?php echo ($woo_status == 1) ? 'completed' : 'active'; ?>" data-step="1">
							<h2><?php esc_html_e('WooCommerce Setup', 'tour-booking-manager'); ?></h2>
							<?php if ($woo_status == 1): ?>
								<div class="ttbm-status-box success">
									<span class="dashicons dashicons-yes-alt"></span>
									<p><?php esc_html_e('WooCommerce is already installed and activated!', 'tour-booking-manager'); ?></p>
								</div>
							<?php elseif ($woo_status == 2): ?>
								<div class="ttbm-status-box warning">
									<span class="dashicons dashicons-warning"></span>
									<p><?php esc_html_e('WooCommerce is installed but not activated.', 'tour-booking-manager'); ?></p>
								</div>
								<button class="ttbm-btn ttbm-btn-primary" id="ttbm-activate-woo">
									<?php esc_html_e('Activate WooCommerce', 'tour-booking-manager'); ?>
								</button>
							<?php else: ?>
								<div class="ttbm-status-box warning">
									<span class="dashicons dashicons-warning"></span>
									<p><?php esc_html_e('WooCommerce is required for Tour Booking Manager to function properly.', 'tour-booking-manager'); ?></p>
								</div>
								<button class="ttbm-btn ttbm-btn-primary" id="ttbm-install-woo">
									<?php esc_html_e('Install WooCommerce', 'tour-booking-manager'); ?>
								</button>
							<?php endif; ?>
							<div class="ttbm-loading" style="display:none;">
								<span class="spinner is-active"></span>
								<p class="ttbm-loading-text"></p>
							</div>
						</div>

						<!-- Step 2: Labels & Slugs -->
						<div class="ttbm-step-content" data-step="2">
							<h2><?php esc_html_e('Configure Labels & Slugs', 'tour-booking-manager'); ?></h2>
							<p><?php esc_html_e('Customize the labels and URL slugs for your tour booking system.', 'tour-booking-manager'); ?></p>
							
							<form id="ttbm-labels-slugs-form">
								<div class="ttbm-form-group">
									<label for="tour_label"><?php esc_html_e('Tour Label (Singular)', 'tour-booking-manager'); ?></label>
									<input type="text" id="tour_label" name="tour_label" value="<?php echo esc_attr($tour_label); ?>" placeholder="Tour" />
									<small><?php esc_html_e('This will be used in menus, buttons, and admin pages.', 'tour-booking-manager'); ?></small>
								</div>

								<div class="ttbm-form-group">
									<label for="tour_slug"><?php esc_html_e('Tour Slug (URL)', 'tour-booking-manager'); ?></label>
									<input type="text" id="tour_slug" name="tour_slug" value="<?php echo esc_attr($tour_slug); ?>" placeholder="tour" />
									<small><?php esc_html_e('This will be used in URLs (e.g., yoursite.com/tour/tour-name).', 'tour-booking-manager'); ?></small>
								</div>

								<button type="submit" class="ttbm-btn ttbm-btn-primary">
									<?php esc_html_e('Save & Continue', 'tour-booking-manager'); ?>
								</button>
							</form>
						</div>

						<!-- Step 3: Finish -->
						<div class="ttbm-step-content" data-step="3">
							<div class="ttbm-finish-content">
								<div class="ttbm-success-icon">
									<span class="dashicons dashicons-yes-alt"></span>
								</div>
								<h2><?php esc_html_e('Setup Complete!', 'tour-booking-manager'); ?></h2>
								<p><?php esc_html_e('Your Tour Booking Manager is now ready to use.', 'tour-booking-manager'); ?></p>
								
								<div class="ttbm-next-steps">
									<h3><?php esc_html_e('What\'s Next?', 'tour-booking-manager'); ?></h3>
									<ul>
										<li>
											<span class="dashicons dashicons-admin-post"></span>
											<a href="<?php echo esc_url(admin_url('post-new.php?post_type=ttbm_tour')); ?>">
												<?php esc_html_e('Create Your First Tour', 'tour-booking-manager'); ?>
											</a>
										</li>
										<li>
											<span class="dashicons dashicons-admin-settings"></span>
											<a href="<?php echo esc_url(admin_url('edit.php?post_type=ttbm_tour&page=ttbm_general_setting')); ?>">
												<?php esc_html_e('Configure General Settings', 'tour-booking-manager'); ?>
											</a>
										</li>
										<li>
											<span class="dashicons dashicons-admin-appearance"></span>
											<a href="<?php echo esc_url(admin_url('edit.php?post_type=ttbm_tour')); ?>">
												<?php esc_html_e('View All Tours', 'tour-booking-manager'); ?>
											</a>
										</li>
									</ul>
								</div>

								<button class="ttbm-btn ttbm-btn-primary" id="ttbm-finish-setup">
									<?php esc_html_e('Go to Dashboard', 'tour-booking-manager'); ?>
								</button>
							</div>
						</div>
					</div>

					<!-- Navigation Buttons -->
					<div class="ttbm-setup-footer">
						<button class="ttbm-btn ttbm-btn-secondary" id="ttbm-prev-step" style="display:none;">
							<?php esc_html_e('Previous', 'tour-booking-manager'); ?>
						</button>
						<button class="ttbm-btn ttbm-btn-primary" id="ttbm-next-step">
							<?php esc_html_e('Next', 'tour-booking-manager'); ?>
						</button>
						<?php 
						// Show Skip Setup button - different URLs based on WooCommerce status
						if ($woo_status != 1): 
							// WooCommerce not active - go to Dashboard
							$skip_url = admin_url('index.php');
						else: 
							// WooCommerce active - go to tour list page
							$skip_url = admin_url('edit.php?post_type=ttbm_tour&page=ttbm_list');
						endif;
						?>
						<a href="<?php echo esc_url($skip_url); ?>" class="ttbm-skip-setup">
							<?php esc_html_e('Skip Setup', 'tour-booking-manager'); ?>
						</a>
					</div>
				</div>
			</div>
			<style type="text/css">
			/* Force hide navigation buttons on completion page */
			.ttbm-step-content[data-step="3"].active ~ * #ttbm-next-step,
			.ttbm-step-content[data-step="3"].active ~ * #ttbm-prev-step {
				display: none !important;
				visibility: hidden !important;
			}
			/* Alternative selector */
			.ttbm-quick-setup-container:has(.ttbm-step-content[data-step="3"].active) #ttbm-next-step,
			.ttbm-quick-setup-container:has(.ttbm-step-content[data-step="3"].active) #ttbm-prev-step {
				display: none !important;
			}
			/* Quick Setup menu item green color */
			#adminmenu .wp-submenu a[href*="ttbm_quick_setup"],
			#adminmenu .wp-menu-name a[href*="ttbm_quick_setup"] {
				color: #00a32a !important;
			}
			#adminmenu .wp-submenu a[href*="ttbm_quick_setup"]:hover,
			#adminmenu .wp-menu-name a[href*="ttbm_quick_setup"]:hover {
				color: #008a20 !important;
			}
			</style>
			<script type="text/javascript">
			// Force hide navigation buttons on final step
			jQuery(document).ready(function($) {
				function hideNavButtons() {
					// Check if we're on step 3 (finish page)
					if ($('.ttbm-step-content[data-step="3"]').hasClass('active')) {
						$('#ttbm-next-step, #ttbm-prev-step, .ttbm-skip-setup').hide();
						$('.ttbm-setup-footer').css('justify-content', 'center');
					}
				}
				
				// Run immediately
				hideNavButtons();
				
				// Run again after a short delay to catch any late renders
				setTimeout(hideNavButtons, 100);
				setTimeout(hideNavButtons, 500);
				
				// Hide Skip Setup button after WooCommerce is activated
				$(document).on('click', '#ttbm-activate-woo', function() {
					setTimeout(function() {
						$('.ttbm-skip-setup').fadeOut();
					}, 2000);
				});
			});
			</script>
			<?php
		}

		/**
		 * AJAX: Install WooCommerce
		 */
		public function ajax_install_woocommerce() {
			check_ajax_referer('ttbm_quick_setup_nonce', 'nonce');

			if (!current_user_can('install_plugins')) {
				wp_send_json_error(array('message' => esc_html__('You do not have permission to install plugins.', 'tour-booking-manager')));
			}

			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			include_once ABSPATH . 'wp-admin/includes/file.php';
			include_once ABSPATH . 'wp-admin/includes/misc.php';
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			$api = plugins_api('plugin_information', array('slug' => 'woocommerce', 'fields' => array('sections' => false)));

			if (is_wp_error($api)) {
				wp_send_json_error(array('message' => $api->get_error_message()));
			}

			$upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
			$result = $upgrader->install($api->download_link);

			if (is_wp_error($result)) {
				wp_send_json_error(array('message' => $result->get_error_message()));
			}

			if ($result) {
				// Activate the plugin
				$activate = activate_plugin('woocommerce/woocommerce.php');
				if (is_wp_error($activate)) {
					wp_send_json_error(array('message' => $activate->get_error_message()));
				}
				wp_send_json_success(array('message' => esc_html__('WooCommerce installed and activated successfully!', 'tour-booking-manager')));
			} else {
				wp_send_json_error(array('message' => esc_html__('Failed to install WooCommerce.', 'tour-booking-manager')));
			}
		}

		/**
		 * AJAX: Activate WooCommerce
		 */
		public function ajax_activate_woocommerce() {
			check_ajax_referer('ttbm_quick_setup_nonce', 'nonce');

			if (!current_user_can('activate_plugins')) {
				wp_send_json_error(array('message' => esc_html__('You do not have permission to activate plugins.', 'tour-booking-manager')));
			}

			$activate = activate_plugin('woocommerce/woocommerce.php');
			
			if (is_wp_error($activate)) {
				wp_send_json_error(array('message' => $activate->get_error_message()));
			}

			wp_send_json_success(array('message' => esc_html__('WooCommerce activated successfully!', 'tour-booking-manager')));
		}

		/**
		 * AJAX: Save Labels & Slugs
		 */
		public function ajax_save_labels_slugs() {
			check_ajax_referer('ttbm_quick_setup_nonce', 'nonce');

			if (!current_user_can('manage_options')) {
				wp_send_json_error(array('message' => esc_html__('You do not have permission to save settings.', 'tour-booking-manager')));
			}

			$tour_label = isset($_POST['tour_label']) ? sanitize_text_field($_POST['tour_label']) : 'Tour';
			$tour_slug = isset($_POST['tour_slug']) ? sanitize_title($_POST['tour_slug']) : 'tour';

			// Get existing settings array - handle both array and string cases
			$settings = get_option('ttbm_basic_gen_settings', array());
			
			// If settings is not an array (could be empty string), initialize as array
			if (!is_array($settings)) {
				$settings = array();
			}
			
			// Update the correct keys used by the plugin
			$settings['ttbm_travel_label'] = $tour_label;
			$settings['ttbm_travel_slug'] = $tour_slug;
			
			// Save the updated settings
			update_option('ttbm_basic_gen_settings', $settings);
			
			// Flush rewrite rules to apply the new slug
			flush_rewrite_rules();

			wp_send_json_success(array('message' => esc_html__('Labels and slugs saved successfully!', 'tour-booking-manager')));
		}

		/**
		 * AJAX: Finish Setup
		 */
		public function ajax_finish_setup() {
			check_ajax_referer('ttbm_quick_setup_nonce', 'nonce');

			if (!current_user_can('manage_options')) {
				wp_send_json_error(array('message' => esc_html__('You do not have permission to complete setup.', 'tour-booking-manager')));
			}

			update_option('ttbm_quick_setup_done', 'yes');
			// Remove the dismissed notice option
			delete_option('ttbm_setup_notice_dismissed');
			
			// Clean up any old wrong option keys from previous Quick Setup versions
			delete_option('ttbm_tour_label');
			delete_option('ttbm_tour_slug');

			wp_send_json_success(array(
				'message' => esc_html__('Setup completed successfully!', 'tour-booking-manager'),
				'redirect_url' => admin_url('edit.php?post_type=ttbm_tour&page=ttbm_list')
			));
		}
	}
	new TTBM_Quick_Setup();
}

