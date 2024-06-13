<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_faq_day_wise_details')) {
		class TTBM_Settings_faq_day_wise_details {
			public function __construct() {
				add_action('add_ttbm_settings_tab_name', [$this, 'add_tab'], 90);
				add_action('add_ttbm_settings_tab_content', [$this, 'tab_content'], 10, 1);
				add_action('wp_ajax_get_ttbm_add_faq_content', [$this, 'get_ttbm_add_faq_content']);
				add_action('wp_ajax_nopriv_get_ttbm_add_faq_content', [$this, 'get_ttbm_add_faq_content']);
				add_action('wp_ajax_get_ttbm_add_day_wise_details', [$this, 'get_ttbm_add_day_wise_details']);
				add_action('wp_ajax_nopriv_get_ttbm_add_day_wise_details', [$this, 'get_ttbm_add_day_wise_details']);
				add_action('ttbm_settings_save', [$this, 'save_day_details_faq']);
			}
			public function add_tab() {
				?>
				<li data-tabs-target="#ttbm_settings_day_wise_details">
					<i class="fas fa-th-list"></i><?php esc_html_e('Day wise Details', 'tour-booking-manager'); ?>
				</li>
				<li data-tabs-target="#ttbm_settings_faq">
					<i class="fas fa-question-circle"></i><?php esc_html_e('F.A.Q', 'tour-booking-manager'); ?>
				</li>
				<?php
			}
			public function tab_content($tour_id) {
				$this->ttbm_settings_day_wise_details($tour_id);
				$this->ttbm_settings_faq($tour_id);
			}
			//********Day wise Details**************//
			public function ttbm_settings_day_wise_details($tour_id) {
				$display = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_schedule', 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem ttbm_settings_day_wise_details" data-tabs="#ttbm_settings_day_wise_details">
					<h2><?php esc_html_e('Day wise Details', 'tour-booking-manager'); ?></h2>
					<p><?php TTBM_Settings::des_p('daywise_details_description') ?></p>
					
					<section class="bg-light">
                        <label for="" class="label">
							<div>
								<p><?php esc_html_e('Day Wise Details', 'tour-booking-manager'); ?></p> 
								<span class="text"><?php esc_html_e('Here you can set tour daywise details information.', 'tour-booking-manager'); ?></span>   
							</div>
						</label>
                    </section>

					<section>
                       <label class="label">
							<div>
								<p><?php esc_html_e('Day Wise Details Settings', 'tour-booking-manager'); ?></p> 
								<span class="text"><?php TTBM_Settings::des_p('ttbm_display_schedule'); ?></span>   
							</div>
							<?php MP_Custom_Layout::switch_button('ttbm_display_schedule', $checked); ?>
					   </label>
                    </section>

					<div data-collapse="#ttbm_display_schedule" class="<?php echo esc_attr($active); ?>">
						<?php
							$day_details = MP_Global_Function::get_post_info($tour_id, 'ttbm_daywise_details', array());
							if (sizeof($day_details) > 0) {
								foreach ($day_details as $day_detail) {
									$id = 'ttbm_day_content_' . uniqid();
									$this->ttbm_repeated_item($id, 'ttbm_daywise_details', $day_detail);
								}
							}
						?>
						<div class="mt-2">
							<?php MP_Custom_Layout::add_new_button(esc_html__('Add New Day Wise Details', 'tour-booking-manager'), 'ttbm_add_day_wise_details', 'btn'); ?>
						</div>
					</div>
				</div>
				<?php
			}
			public function get_ttbm_add_day_wise_details() {
				$id = MP_Global_Function::data_sanitize($_POST['id']);
				$this->ttbm_repeated_item($id, 'ttbm_daywise_details');
				die();
			}
			//*************F.A.Q******************//
			public function ttbm_settings_faq($tour_id) {
				$display = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_faq', 'on');
				$active = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem ttbm_settings_faq" data-tabs="#ttbm_settings_faq">
					<h2><?php esc_html_e('F.A.Q Settings', 'tour-booking-manager'); ?></h2>
					<p><?php TTBM_Settings::des_p('faq_settings_description'); ?></p>
					
					<section class="bg-light">
                        <label for="" class="label">
							<div>
								<p><?php esc_html_e('Frequently Asked Question', 'tour-booking-manager'); ?></p>
								<span class="text"><?php esc_html_e('You can add frequently asked question for your tour.', 'tour-booking-manager'); ?></span>
							</div>
						</label>
                    </section>
					
					<section >
                        <label class="label">
							<div>
								<p><?php esc_html_e('F.A.Q Settings', 'tour-booking-manager'); ?></p>
								<span><?php TTBM_Settings::des_p('ttbm_display_faq'); ?></span>
							</div>
							<?php MP_Custom_Layout::switch_button('ttbm_display_faq', $checked); ?>
						</label>
                    </section>
					
					<div data-collapse="#ttbm_display_faq" class="<?php echo esc_attr($active); ?>">
						<?php
							$faqs = MP_Global_Function::get_post_info($tour_id, 'mep_event_faq', []);
							if (sizeof($faqs) > 0) {
								foreach ($faqs as $faq) {
									$id = 'ttbm_faq_content_' . uniqid();
									$this->ttbm_repeated_item($id, 'mep_event_faq', $faq);
								}
							}
						?>
						<?php MP_Custom_Layout::add_new_button(esc_html__('Add New F.A.Q', 'tour-booking-manager'), 'btn ttbm_add_faq_content', ''); ?>
					</div>
				</div>
				<?php
			}
			public function get_ttbm_add_faq_content() {
				$id = MP_Global_Function::data_sanitize($_POST['id']);
				$this->ttbm_repeated_item($id, 'mep_event_faq');
				die();
			}
			//*********************//
			public static function get_ttbm_repeated_setting_array($meta_key): array {
				$array = ['mep_event_faq' => ['title' => esc_html__(' F.A.Q Title', 'tour-booking-manager'), 'title_name' => 'ttbm_faq_title', 'img_title' => esc_html__(' F.A.Q Details image', 'tour-booking-manager'), 'img_name' => 'ttbm_faq_img', 'content_title' => esc_html__(' F.A.Q Details Content', 'tour-booking-manager'), 'content_name' => 'ttbm_faq_content',], 'ttbm_daywise_details' => ['title' => esc_html__(' Day wise Details Title', 'tour-booking-manager'), 'title_name' => 'ttbm_day_title', 'img_title' => esc_html__(' Day wise Details image', 'tour-booking-manager'), 'img_name' => 'ttbm_day_image', 'content_title' => esc_html__(' Day wise Details Content', 'tour-booking-manager'), 'content_name' => 'ttbm_day_content',]];
				return $array[$meta_key];
			}
			public function ttbm_repeated_item($id, $meta_key, $data = []) {
				//ob_start();
				$array = self::get_ttbm_repeated_setting_array($meta_key);
				$title = $array['title'];
				$title_name = $array['title_name'];
				$title_value = array_key_exists($title_name, $data) ? html_entity_decode($data[$title_name]) : '';
				$image_title = $array['img_title'];
				$image_name = $array['img_name'];
				$images = array_key_exists($image_name, $data) ? $data[$image_name] : '';
				$content_title = $array['content_title'];
				$content_name = $array['content_name'];
				$content = array_key_exists($content_name, $data) ? html_entity_decode($data[$content_name]) : '';
				?>
				<div class='mp_remove_area my-5'>
					<section class="bg-light">
						<label class="label">
							<p><?php echo esc_html($title); ?></p>
							<input type="text"class="long-input mb-20" name="<?php echo esc_attr($title_name); ?>[]" value="<?php echo esc_attr($title_value); ?>"/>
						</label>
					</section>
					<section>
						<div class="w-100">
							<!-- <label>
								<?php echo esc_html($content_title); ?>
							</label> -->
							<?php TTBM_Layout::add_multi_image($image_name . '[]', $images); ?>
							<?php
								$settings = ['wpautop' => false, 'media_buttons' => false, 'textarea_name' => $content_name . '[]', 'tabindex' => '323', 'editor_height' => 200, 'editor_css' => '', 'editor_class' => '', 'teeny' => false, 'dfw' => false, 'tinymce' => true, 'quicktags' => true];
								wp_editor($content, $id, $settings);
							?>
						</div>
					</section>
					<span class="fas fa-times circleIcon_xs mp_remove_icon"></span>
				</div>
				
				<?php
				//return ob_get_clean();
			}
			//*********************//
			public function save_day_details_faq($post_id) {
				if (get_post_type($post_id) == TTBM_Function::get_cpt_name()) {
					$this->save_ttbm_repeated_setting($post_id, 'mep_event_faq');
					$this->save_ttbm_repeated_setting($post_id, 'ttbm_daywise_details');
				}
			}
			public function save_ttbm_repeated_setting($tour_id, $meta_key) {
				$array = self::get_ttbm_repeated_setting_array($meta_key);
				$title_name = $array['title_name'];
				$image_name = $array['img_name'];
				$content_name = $array['content_name'];
				if (get_post_type($tour_id) == TTBM_Function::get_cpt_name()) {
					$new_data = array();
					$title = MP_Global_Function::get_submit_info($title_name, array());
					$images = MP_Global_Function::get_submit_info($image_name, array());
					$content = $_POST[$content_name] ?? array();
					$count = count($title);
					if ($count > 0) {
						for ($i = 0; $i < $count; $i++) {
							if ($title[$i] != '') {
								$new_data[$i][$title_name] = $title[$i];
								if ($images[$i] != '') {
									$new_data[$i][$image_name] = $images[$i];
								}
								if ($content[$i] != '') {
									$new_data[$i][$content_name] = htmlentities($content[$i]);
								}
							}
						}
					}
					update_post_meta($tour_id, $meta_key, $new_data);
					if ($meta_key == 'ttbm_daywise_details') {
						$schedule = MP_Global_Function::get_submit_info('ttbm_display_schedule') ? 'on' : 'off';
						update_post_meta($tour_id, 'ttbm_display_schedule', $schedule);
					}
					if ($meta_key == 'mep_event_faq') {
						$faq = MP_Global_Function::get_submit_info('ttbm_display_faq') ? 'on' : 'off';
						update_post_meta($tour_id, 'ttbm_display_faq', $faq);
					}
				}
			}
		}
		new TTBM_Settings_faq_day_wise_details();
	}