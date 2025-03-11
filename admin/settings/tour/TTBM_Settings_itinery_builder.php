<?php
/**
 * Itinerary Builder Settings 
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

if (!class_exists('TTBM_Daywise_Details')) {
	class TTBM_Daywise_Details {
		public function __construct() {
			add_action('add_ttbm_settings_tab_name', [$this, 'add_tab'], 90);
			add_action('add_ttbm_settings_tab_content', [$this, 'tab_content'], 10, 1);
			add_action('admin_enqueue_scripts', [$this, 'my_custom_editor_enqueue']);
			// save daywise data
			add_action('wp_ajax_ttbm_daywise_data_save', [$this, 'save_daywise_data_settings']);
			// update daywise data
			add_action('wp_ajax_ttbm_daywise_data_update', [$this, 'daywise_data_update']);
			// ttbm_delete_daywise_data
			add_action('wp_ajax_ttbm_daywise_delete_item', [$this, 'daywise_delete_item']);
			// daywise sort_daywise
			add_action('wp_ajax_ttbm_sort_daywise', [$this, 'sort_daywise']);
			// daywise sort_daywise
			add_action('ttbm_settings_save', [$this, 'save_daywise_settings']);
		}


		public function add_tab() {
			?>
			<li data-tabs-target="#ttbm_daywise_settings">
				<i class="fas fa-list-ul"></i><?php esc_html_e('Itinerary Builder', 'tour-booking-manager'); ?>
			</li>
			<?php
		}

		public function sort_daywise() {
			if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
				wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
			}
			$post_id = isset($_POST['postID']) ? sanitize_text_field(wp_unslash($_POST['postID'])) : '';
			$sorted_ids = isset($_POST['sortedIDs']) ? array_map('intval', $_POST['sortedIDs']) : [];
			$ttbm_daywise = get_post_meta($post_id, 'ttbm_daywise_details', true);;
			$new_ordered = [];
			foreach ($sorted_ids as $id) {
				if (isset($ttbm_daywise[$id])) {
					$new_ordered[$id] = $ttbm_daywise[$id];
				}
			}
			update_post_meta($post_id, 'ttbm_daywise_details', $new_ordered);
			ob_start();
			$resultMessage = esc_html__('Data Updated Successfully', 'tour-booking-manager');
			$this->show_daywise_data($post_id);
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
			$daywise_active = MP_Global_Function::get_post_info($post_id, 'ttbm_display_schedule', 'off');
			$checked = $daywise_active == 'on' ? 'checked' : '';
			?>
			<div class="tabsItem ttbm_settings_daywise" data-tabs="#ttbm_daywise_settings">
				<h2><?php esc_html_e('Itinerary Builder Settings', 'tour-booking-manager'); ?></h2>
				<p><?php esc_html_e('Itinerary Builder Settings', 'tour-booking-manager'); ?></p>
				
				<section class="bg-light">
					<label class="label">
						<div>
							<p><?php esc_html_e('Itinerary Builder', 'tour-booking-manager'); ?></p>
							<span class="text"><?php esc_html_e('You can add frequently asked question for your tour.', 'tour-booking-manager'); ?></span>
						</div>
					</label>
				</section>
				
				<section >
					<div class="label">
						<div>
							<p><?php esc_html_e('Itinerary Builder Enable/Disable', 'tour-booking-manager'); ?></p>
							<span><?php esc_html_e('Itinerary Builder Enable/Disable by this toggle switch.', 'tour-booking-manager'); ?></span>
						</div>
						<?php MP_Custom_Layout::switch_button('ttbm_display_schedule', $checked); ?>
					</div>
				</section>
				<section class="ttbm-daywise-section ">
					<div class="ttbm-daywise-items mB">
						<?php $this->show_daywise_data($post_id); ?>
					</div>
					<button class="button ttbm-daywise-item-new" data-modal="ttbm-daywise-item-new" type="button"><?php esc_html_e('Add daywise', 'tour-booking-manager'); ?></button>
				</section>
				<!-- sidebar collapse open -->
				<div class="ttbm-modal-container" data-modal-target="ttbm-daywise-item-new">
					<div class="ttbm-modal-content">
						<span class="ttbm-modal-close"><i class="fas fa-times"></i></span>
						<div class="title">
							<h3><?php esc_html_e('Add New Itinerary', 'tour-booking-manager'); ?></h3>
							<div id="ttbm-service-msg"></div>
						</div>
						<div class="content">
							<label>
								<?php esc_html_e('Add Title', 'tour-booking-manager'); ?>
								<input type="hidden" name="ttbm_post_id" value="<?php echo esc_attr($post_id); ?>">
								<input type="text" name="ttbm_day_title">
								<input type="hidden" name="ttbm_daywise_item_id">
							</label>
							<label>
								<?php esc_html_e('Add Content', 'tour-booking-manager'); ?>
							</label>
							<?php
								$content = '';
								$editor_id = 'ttbm_day_content';
								$settings = array(
									'textarea_name' => 'ttbm_day_content',
									'media_buttons' => true,
									'textarea_rows' => 10,
								);
								wp_editor($content, $editor_id, $settings);
							?>
							<div class="mT"></div>
							<div class="ttbm_daywise_save_buttons">
								<p>
									<button id="ttbm_daywise_save" class="button button-primary button-large"><?php esc_html_e('Save', 'tour-booking-manager'); ?></button>
									<button id="ttbm_daywise_save_close" class="button button-primary button-large">save close</button>
								<p>
							</div>
							<div class="ttbm_daywise_update_buttons" style="display: none;">
								<p>
									<button id="ttbm_daywise_update" class="button button-primary button-large"><?php esc_html_e('Update and Close', 'tour-booking-manager'); ?></button>
								<p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		public function show_daywise_data($post_id) {
			$ttbm_daywise = get_post_meta($post_id, 'ttbm_daywise_details', true);
			if (!empty($ttbm_daywise)):
				foreach ($ttbm_daywise as $key => $value) :
					?>
					<div class="ttbm-daywise-item" data-id="<?php echo esc_attr($key); ?>">
						<section class="daywise-header" data-collapse-target="#daywise-content-<?php echo esc_attr($key); ?>">
							<label class="label">
								<p><?php echo esc_html($value['ttbm_day_title']); ?></p>
								<div class="daywise-action">
									<span class=""><i class="fas fa-eye"></i></span>
									<span class="ttbm-daywise-item-edit" data-modal="ttbm-daywise-item-new"><i class="fas fa-edit"></i></span>
									<span class="ttbm-daywise-item-delete"><i class="fas fa-trash"></i></span>
								</div>
							</label>
						</section>
						<section class="daywise-content mB" data-collapse="#daywise-content-<?php echo esc_attr($key); ?>">
							<?php echo wp_kses_post($value['ttbm_day_content']); ?>
						</section>
					</div>
				<?php
				endforeach;
			endif;
		}
		public function daywise_data_update() {
			if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
				wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
			}
			$post_id = isset($_POST['ttbm_daywise_postID']) ? sanitize_text_field(wp_unslash($_POST['ttbm_daywise_postID'])) : '';
			$ttbm_day_title = isset($_POST['ttbm_day_title']) ? sanitize_text_field(wp_unslash($_POST['ttbm_day_title'])) : '';
			$allowed_tags = wp_kses_allowed_html('post'); 
			$ttbm_day_content = isset($_POST['ttbm_day_content']) ? wp_kses(wp_unslash($_POST['ttbm_day_content']), $allowed_tags) : '';

			$ttbm_daywise = get_post_meta($post_id, 'ttbm_daywise_details', true);
			$ttbm_daywise = !empty($ttbm_daywise) ? $ttbm_daywise : [];
			$new_data = ['ttbm_day_title' => $ttbm_day_title, 'ttbm_day_content' => $ttbm_day_content];
			if (!empty($ttbm_daywise)) {
				if (isset($_POST['ttbm_daywise_itemID'])) {
					$ttbm_daywise[sanitize_text_field(wp_unslash($_POST['ttbm_daywise_itemID']))] = $new_data;
				}
			}
			update_post_meta($post_id, 'ttbm_daywise_details', $ttbm_daywise);
			ob_start();
			$resultMessage = esc_html__('Data Updated Successfully', 'tour-booking-manager');
			$this->show_daywise_data($post_id);
			$html_output = ob_get_clean();
			wp_send_json_success([
				'message' => $resultMessage,
				'html' => $html_output,
			]);
			die;
		}
		public function save_daywise_data_settings() {
			if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
				wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
			}
			$post_id = isset($_POST['ttbm_daywise_postID']) ? sanitize_text_field(wp_unslash($_POST['ttbm_daywise_postID'])) : '';
			$ttbm_day_title = isset($_POST['ttbm_day_title']) ? sanitize_text_field(wp_unslash($_POST['ttbm_day_title'])) : '';
			$allowed_tags = wp_kses_allowed_html('post'); 
			$ttbm_day_content = isset($_POST['ttbm_day_content']) ? wp_kses(wp_unslash($_POST['ttbm_day_content']), $allowed_tags) : '';
			$ttbm_daywise = get_post_meta($post_id, 'ttbm_daywise_details', true);
			$ttbm_daywise = !empty($ttbm_daywise) ? $ttbm_daywise : [];
			$new_data = ['ttbm_day_title' => $ttbm_day_title, 'ttbm_day_content' => $ttbm_day_content];
			if (isset($post_id)) {
				array_push($ttbm_daywise, $new_data);
			}
			$result = update_post_meta($post_id, 'ttbm_daywise_details', $ttbm_daywise);
			if ($result) {
				ob_start();
				$resultMessage = esc_html__('Data Added Successfully', 'tour-booking-manager');
				$this->show_daywise_data($post_id);
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
		public function daywise_delete_item() {
			if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
				wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
			}
			$post_id = isset($_POST['ttbm_daywise_postID']) ? sanitize_text_field(wp_unslash($_POST['ttbm_daywise_postID'])) : '';
			$ttbm_daywise = get_post_meta($post_id, 'ttbm_daywise_details', true);
			$ttbm_daywise = !empty($ttbm_daywise) ? $ttbm_daywise : [];
			if (!empty($ttbm_daywise)) {
				if (isset($_POST['itemId'])) {
					unset($ttbm_daywise[sanitize_text_field(wp_unslash($_POST['itemId']))]);
					$ttbm_daywise = array_values($ttbm_daywise);
				}
			}
			$result = update_post_meta($post_id, 'ttbm_daywise_details', $ttbm_daywise);
			if ($result) {
				ob_start();
				$resultMessage = esc_html__('Data Deleted Successfully', 'tour-booking-manager');
				$this->show_daywise_data($post_id);
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
		public function save_daywise_settings($tour_id){
			$daywise = MP_Global_Function::get_submit_info('ttbm_display_schedule') ? 'on' : 'off';
			update_post_meta($tour_id, 'ttbm_display_schedule', $daywise);
		}
	}
	new TTBM_Daywise_Details();
}