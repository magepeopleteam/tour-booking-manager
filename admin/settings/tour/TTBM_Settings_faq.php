<?php
/**
 * FAQ Settings 
 * 
 * @package Tour Booking Manager
 * @since 1.8.7
 * @version 1.0.0
 * @author Shahadat Hossain <raselsha@gmail.com>
 * 
 */
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('TTBM_Settings_faq')) {
	class TTBM_Settings_faq {
		public function __construct() {
			add_action('add_ttbm_settings_tab_name', [$this, 'add_tab'], 90);
			add_action('add_ttbm_settings_tab_content', [$this, 'tab_content'], 10, 1);
			add_action('admin_enqueue_scripts', [$this, 'my_custom_editor_enqueue']);
			// save faq data
			add_action('wp_ajax_ttbm_faq_data_save', [$this, 'save_faq_data_settings']);
			// update faq data
			add_action('wp_ajax_ttbm_faq_data_update', [$this, 'faq_data_update']);
			// ttbm_delete_faq_data
			add_action('wp_ajax_ttbm_faq_delete_item', [$this, 'faq_delete_item']);
			// FAQ sort_faq
			add_action('wp_ajax_ttbm_sort_faq', [$this, 'sort_faq']);
			// FAQ sort_faq
			add_action('ttbm_settings_save', [$this, 'save_faq_settings']);
		}


		public function add_tab() {
			?>
			<li data-tabs-target="#ttbm_faq_settings">
				<i class="fas fa-question-circle"></i><?php esc_html_e('F.A.Q', 'tour-booking-manager'); ?>
			</li>
			<?php
		}

		public function sort_faq() {
			if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
				wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
			}
			$post_id = isset($_POST['postID']) ? sanitize_text_field(wp_unslash($_POST['postID'])) : '';
			$sorted_ids = isset($_POST['sortedIDs']) ? array_map('intval', $_POST['sortedIDs']) : [];
			$ttbm_faq = get_post_meta($post_id, 'mep_event_faq', true);;
			$new_ordered = [];
			foreach ($sorted_ids as $id) {
				if (isset($ttbm_faq[$id])) {
					$new_ordered[$id] = $ttbm_faq[$id];
				}
			}
			update_post_meta($post_id, 'mep_event_faq', $new_ordered);
			ob_start();
			$resultMessage = esc_html__('Data Updated Successfully', 'tour-booking-manager');
			$this->show_faq_data($post_id);
			$html_output = ob_get_clean();
			wp_send_json_success([
				'message' => $resultMessage,
				'html' => $html_output,
			]);
			die;
		}

		public function my_custom_editor_enqueue() {
			// Enqueue necessary scripts
			wp_enqueue_script('jquery');
			wp_enqueue_script('editor');
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			wp_enqueue_style('thickbox');
		}
		public function tab_content($post_id) {
			$faq_active = MP_Global_Function::get_post_info($post_id, 'ttbm_display_faq', 'off');
			$active_class = $faq_active == 'on' ? 'mActive' : '';
			$checked = $faq_active == 'on' ? 'checked' : '';
			?>
			<div class="tabsItem ttbm_settings_faq" data-tabs="#ttbm_faq_settings">
				<h2><?php esc_html_e('F.A.Q Settings', 'tour-booking-manager'); ?></h2>
				<p><?php TTBM_Settings::des_p('faq_settings_description'); ?></p>
				
				<section class="bg-light">
					<label class="label">
						<div>
							<p><?php esc_html_e('Frequently Asked Question', 'tour-booking-manager'); ?></p>
							<span class="text"><?php esc_html_e('You can add frequently asked question for your tour.', 'tour-booking-manager'); ?></span>
						</div>
					</label>
				</section>
				
				<section >
					<div class="label">
						<div>
							<p><?php esc_html_e('F.A.Q Enable/Disable', 'tour-booking-manager'); ?></p>
							<span><?php TTBM_Settings::des_p('ttbm_display_faq'); ?></span>
						</div>
						<?php MP_Custom_Layout::switch_button('ttbm_display_faq', $checked); ?>
					</div>
				</section>
				<section class="ttbm-faq-section ">
					<div class="ttbm-faq-items mB">
						<?php $this->show_faq_data($post_id); ?>
					</div>
					<button class="button ttbm-faq-item-new" data-modal="ttbm-faq-item-new" type="button"><?php esc_html_e('Add FAQ', 'tour-booking-manager'); ?></button>
				</section>
				<!-- sidebar collapse open -->
				<div class="ttbm-modal-container" data-modal-target="ttbm-faq-item-new">
					<div class="ttbm-modal-content">
						<span class="ttbm-modal-close"><i class="fas fa-times"></i></span>
						<div class="title">
							<h3><?php esc_html_e('Add F.A.Q.', 'tour-booking-manager'); ?></h3>
							<div id="ttbm-service-msg"></div>
						</div>
						<div class="content">
							<label>
								<?php esc_html_e('Add Title', 'tour-booking-manager'); ?>
								<input type="hidden" name="ttbm_post_id" value="<?php echo esc_attr($post_id); ?>">
								<input type="text" name="ttbm_faq_title">
								<input type="hidden" name="ttbm_faq_item_id">
							</label>
							<label>
								<?php esc_html_e('Add Content', 'tour-booking-manager'); ?>
							</label>
							<?php
								$content = '';
								$editor_id = 'ttbm_faq_content';
								$settings = array(
									'textarea_name' => 'ttbm_faq_content',
									'media_buttons' => true,
									'textarea_rows' => 100,
								);
								wp_editor($content, $editor_id, $settings);
							?>
							<div class="mT"></div>
							<div class="ttbm_faq_save_buttons">
								<p>
									<button id="ttbm_faq_save" class="button button-primary button-large"><?php esc_html_e('Save', 'tour-booking-manager'); ?></button>
									<button id="ttbm_faq_save_close" class="button button-primary button-large">save close</button>
								<p>
							</div>
							<div class="ttbm_faq_update_buttons" style="display: none;">
								<p>
									<button id="ttbm_faq_update" class="button button-primary button-large"><?php esc_html_e('Update and Close', 'tour-booking-manager'); ?></button>
								<p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		public function show_faq_data($post_id) {
			$ttbm_faq = get_post_meta($post_id, 'mep_event_faq', true);
			if (!empty($ttbm_faq)):
				foreach ($ttbm_faq as $key => $value) :
					?>
					<div class="ttbm-faq-item" data-id="<?php echo esc_attr($key); ?>">
						<section class="faq-header" data-collapse-target="#faq-content-<?php echo esc_attr($key); ?>">
							<label class="label">
								<p><?php echo esc_html($value['ttbm_faq_title']); ?></p>
								<div class="faq-action">
									<span class=""><i class="fas fa-eye"></i></span>
									<span class="ttbm-faq-item-edit" data-modal="ttbm-faq-item-new"><i class="fas fa-edit"></i></span>
									<span class="ttbm-faq-item-delete"><i class="fas fa-trash"></i></span>
								</div>
							</label>
						</section>
						<section class="faq-content mB" data-collapse="#faq-content-<?php echo esc_attr($key); ?>">
							<?php echo wp_kses_post($value['ttbm_faq_content']); ?>
						</section>
					</div>
				<?php
				endforeach;
			endif;
		}
		public function faq_data_update() {
			if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
				wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
			}
			$post_id = isset($_POST['ttbm_faq_postID']) ? sanitize_text_field(wp_unslash($_POST['ttbm_faq_postID'])) : '';
			$ttbm_faq_title = isset($_POST['ttbm_faq_title']) ? sanitize_text_field(wp_unslash($_POST['ttbm_faq_title'])) : '';
			
			$allowed_tags = wp_kses_allowed_html('post'); 
			$ttbm_faq_content = isset($_POST['ttbm_faq_content']) ? wp_kses(wp_unslash($_POST['ttbm_faq_content']), $allowed_tags) : '';
			$ttbm_faq = get_post_meta($post_id, 'mep_event_faq', true);
			$ttbm_faq = !empty($ttbm_faq) ? $ttbm_faq : [];
			$new_data = ['ttbm_faq_title' => $ttbm_faq_title, 'ttbm_faq_content' => $ttbm_faq_content];
			if (!empty($ttbm_faq)) {
				if (isset($_POST['ttbm_faq_itemID'])) {
					$ttbm_faq[sanitize_text_field(wp_unslash($_POST['ttbm_faq_itemID']))] = $new_data;
				}
			}
			update_post_meta($post_id, 'mep_event_faq', $ttbm_faq);
			ob_start();
			$resultMessage = esc_html__('Data Updated Successfully', 'tour-booking-manager');
			$this->show_faq_data($post_id);
			$html_output = ob_get_clean();
			wp_send_json_success([
				'message' => $resultMessage,
				'html' => $html_output,
			]);
			die;
		}
		public function save_faq_data_settings() {
			if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
				wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
			}
			$post_id = isset($_POST['ttbm_faq_postID']) ? sanitize_text_field(wp_unslash($_POST['ttbm_faq_postID'])) : '';
			update_post_meta($post_id, 'ttbm_faq_active', 'on');
			$ttbm_faq_title = isset($_POST['ttbm_faq_title']) ? sanitize_text_field(wp_unslash($_POST['ttbm_faq_title'])) : '';
			$allowed_tags = wp_kses_allowed_html('post'); 
			$ttbm_faq_content = isset($_POST['ttbm_faq_content']) ? wp_kses(wp_unslash($_POST['ttbm_faq_content']), $allowed_tags) : '';
			$ttbm_faq = get_post_meta($post_id, 'mep_event_faq', true);
			$ttbm_faq = !empty($ttbm_faq) ? $ttbm_faq : [];
			$new_data = ['ttbm_faq_title' => $ttbm_faq_title, 'ttbm_faq_content' => $ttbm_faq_content];
			if (isset($post_id)) {
				array_push($ttbm_faq, $new_data);
			}
			$result = update_post_meta($post_id, 'mep_event_faq', $ttbm_faq);
			if ($result) {
				ob_start();
				$resultMessage = esc_html__('Data Added Successfully', 'tour-booking-manager');
				$this->show_faq_data($post_id);
				$html_output = ob_get_clean();
				wp_send_json_success([
					'message' => $resultMessage,
					'html' => $html_output,
				]);
			} else {
				wp_send_json_success([
					'message' => 'Data not inserted',
					'html' => 'error',
				]);
			}
			die;
		}
		public function faq_delete_item() {
			if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
				wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
			}
			$post_id = isset($_POST['ttbm_faq_postID']) ? sanitize_text_field(wp_unslash($_POST['ttbm_faq_postID'])) : '';
			$ttbm_faq = get_post_meta($post_id, 'mep_event_faq', true);
			$ttbm_faq = !empty($ttbm_faq) ? $ttbm_faq : [];
			if (!empty($ttbm_faq)) {
				if (isset($_POST['itemId'])) {
					unset($ttbm_faq[sanitize_text_field(wp_unslash($_POST['itemId']))]);
					$ttbm_faq = array_values($ttbm_faq);
				}
			}
			$result = update_post_meta($post_id, 'mep_event_faq', $ttbm_faq);
			if ($result) {
				ob_start();
				$resultMessage = esc_html__('Data Deleted Successfully', 'tour-booking-manager');
				$this->show_faq_data($post_id);
				$html_output = ob_get_clean();
				wp_send_json_success([
					'message' => $resultMessage,
					'html' => $html_output,
				]);
			} else {
				wp_send_json_success([
					'message' => 'Data not inserted',
					'html' => '',
				]);
			}
			die;
		}
		public function save_faq_settings($tour_id){
			$faq = MP_Global_Function::get_submit_info('ttbm_display_faq') ? 'on' : 'off';
			update_post_meta($tour_id, 'ttbm_display_faq', $faq);
		}
	}
	new TTBM_Settings_faq();
}