<?php
	/**
	 * Template Settings
	 *
	 * @package Tour Booking Manager
	 * @since 1.8.7
	 * @version 1.0.0
	 * @author Shahadat Hossain <raselsha@gmail.com>
	 *
	 */
	defined('ABSPATH')|| exit;

	if (!class_exists('TTBM_Settings_Template')) {
		class TTBM_Settings_Template {
			public function __construct() {
				add_action('ttbm_meta_box_tab_content', [$this, 'tab_content'], 10, 1);
			}

			public function tab_content($post_id) {
				$current_template = TTBM_Global_Function::get_post_info( get_the_ID(), 'ttbm_theme_file', 'default.php');
				$template_lists = TTBM_Function::all_details_template();
				?>
                <div class="tabsItem ttbm_settings_template" data-tabs="#ttbm_settings_template">
                    <h2><?php esc_html_e('Template Settings', 'tour-booking-manager'); ?></h2>
					<p><?php esc_html_e('Select suitable template to dispaly your tour.', 'tour-booking-manager'); ?></p>
                    
					<section>
                        <div class="ttbm-header">
                            <h4><i class="mi mi-table-layout"></i><?php esc_html_e('Template Settings', 'tour-booking-manager'); ?></h4>
                        </div>
						<div class="ttbm-template-section">
							<input type="hidden" name="ttbm_theme_file" value="<?php echo esc_attr( $current_template ); ?>"/>
							
							<?php foreach ( $template_lists as $key => $template ): ?>
								<?php  
									$image = preg_replace( '/\.php$/', '.webp', $key);
								?>
								<?php  if ($key != 'hotel_default.php'and $key != 'viator.php' ): ?>
								<div class="ttbm-template <?php echo $current_template == $key ? 'active' : ''; ?>">
									<img src="<?php  echo TTBM_Function::template_screenshot_url() . $image; ?>" data-ttbm-template="<?php echo $key; ?>">
									<h5><?php echo $template; ?></h5>
								</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
                    </section>
                </div>
				<?php
			}
		}
		new TTBM_Settings_Template();
	}