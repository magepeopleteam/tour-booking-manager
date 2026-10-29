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
				add_action('ttbm_tour_itinerary_inner', [$this, 'render_faq_section'], 10, 1);
				add_action('admin_enqueue_scripts', [$this, 'my_custom_editor_enqueue']);
				// save faq data
				add_action('wp_ajax_ttbm_faq_data_save', [$this, 'save_faq_data_settings']);
				// update faq data
				add_action('wp_ajax_ttbm_faq_data_update', [$this, 'faq_data_update']);
				// ttbm_delete_faq_data
				add_action('wp_ajax_ttbm_faq_delete_item', [$this, 'faq_delete_item']);
				// FAQ sort_faq
				add_action('wp_ajax_ttbm_sort_faq', [$this, 'sort_faq']);
			}
			private function current_user_can_edit_faq_post($post_id) {
				return $post_id > 0 && current_user_can('edit_post', $post_id);
			}
			public function sort_faq() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
					wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
				}
				$post_id = isset($_POST['postID']) ? absint(wp_unslash($_POST['postID'])) : 0;
				if (!$this->current_user_can_edit_faq_post($post_id)) {
					wp_send_json_error('Permission denied!', 403);
				}
				$sorted_ids = isset($_POST['sortedIDs']) ? array_map('absint', (array) wp_unslash($_POST['sortedIDs'])) : [];
				$ttbm_faq = get_post_meta($post_id, 'mep_event_faq', true);
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
			public function render_faq_section($post_id) {
				$faq_active = TTBM_Global_Function::get_post_info($post_id, 'ttbm_display_faq', 'off');
				$active_class = $faq_active == 'on' ? 'mActive' : '';
				$checked = $faq_active == 'on' ? 'checked' : '';
				?>
                    <section class="ttbm_settings_faq">
                        <div class="ttbm-header">
                            <h4><i class="fas fa-question-circle"></i><?php esc_html_e('Frequently Asked Question', 'tour-booking-manager'); ?></h4>
							<?php TTBM_Custom_Layout::switch_button('ttbm_display_faq', $checked); ?>
                            <p class="ttbm-header__sub"><?php esc_html_e('Answer common questions guests ask before booking.', 'tour-booking-manager'); ?></p>
                        </div>
                        <div data-collapse="#ttbm_display_faq" class="ttbm-faq-section <?php echo esc_attr($active_class); ?>">
                            <div class="ttbm-faq-items mB">
								<?php $this->show_faq_data($post_id); ?>
                            </div>
                            <button class="button ttbm-faq-item-new" data-modal="ttbm-faq-item-new" type="button"><?php esc_html_e('Add FAQ', 'tour-booking-manager'); ?></button>
                        </div>
                    </section>
                    <!-- FAQ slide-in modal (same layout as the itinerary one) -->
                    <div class="ttbm-modal-container" data-modal-target="ttbm-faq-item-new">
                        <div class="ttbm-modal-content">
                            <!-- Header -->
                            <div class="ttbm-modal-header">
                                <div class="ttbm-modal-header__inner">
                                    <div class="ttbm-modal-header__icon">
                                        <i class="fas fa-question-circle"></i>
                                    </div>
                                    <div>
                                        <h3><?php esc_html_e('Add FAQ', 'tour-booking-manager'); ?></h3>
                                        <p><?php esc_html_e('Answer a question travellers often ask about this tour.', 'tour-booking-manager'); ?></p>
                                    </div>
                                </div>
                                <button type="button" class="ttbm-modal-close" title="<?php esc_attr_e('Close', 'tour-booking-manager'); ?>" aria-label="<?php esc_attr_e('Close', 'tour-booking-manager'); ?>">
                                    <i class="fas fa-times" aria-hidden="true"></i>
                                </button>
                            </div>
                            <!-- Body -->
                            <div class="ttbm-modal-body">
                                <div id="ttbm-faq-msg"></div>
                                <div class="ttbm-modal-field">
                                    <label class="ttbm-modal-label" for="ttbm_faq_title_input">
										<?php esc_html_e('Question', 'tour-booking-manager'); ?>
                                        <span class="ttbm-modal-required">*</span>
                                    </label>
                                    <input type="hidden" name="ttbm_post_id" value="<?php echo esc_attr($post_id); ?>">
                                    <input type="hidden" name="ttbm_faq_item_id">
                                    <input type="text" id="ttbm_faq_title_input" name="ttbm_faq_title"
                                           placeholder="<?php esc_attr_e('e.g. Is hotel pickup included?', 'tour-booking-manager'); ?>">
                                </div>
                                <div class="ttbm-modal-field">
                                    <label class="ttbm-modal-label">
										<?php esc_html_e('Answer', 'tour-booking-manager'); ?>
                                    </label>
									<?php
										$content = '';
										$editor_id = 'ttbm_faq_content';
										$settings = array(
											'textarea_name' => 'ttbm_faq_content',
											'media_buttons' => true,
											'textarea_rows' => 10,
										);
										wp_editor($content, $editor_id, $settings);
									?>
                                </div>
                            </div>
                            <!-- Footer -->
                            <div class="ttbm-modal-footer">
                                <div class="ttbm_faq_save_buttons ttbm-modal-footer__group">
                                    <button id="ttbm_faq_save" class="ttbm-modal-btn ttbm-modal-btn--secondary" type="button">
                                        <i class="fas fa-save"></i>
										<?php esc_html_e('Save & Continue', 'tour-booking-manager'); ?>
                                    </button>
                                    <button id="ttbm_faq_save_close" class="ttbm-modal-btn ttbm-modal-btn--primary" type="button">
                                        <i class="fas fa-check"></i>
										<?php esc_html_e('Save & Close', 'tour-booking-manager'); ?>
                                    </button>
                                </div>
                                <div class="ttbm_faq_update_buttons ttbm-modal-footer__group" style="display:none;">
                                    <button id="ttbm_faq_update" class="ttbm-modal-btn ttbm-modal-btn--primary" type="button">
                                        <i class="fas fa-sync-alt"></i>
										<?php esc_html_e('Update & Close', 'tour-booking-manager'); ?>
                                    </button>
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
                            <div class="faq-header" data-collapse-target="#faq-content-<?php echo esc_attr($key); ?>">
                                <div class="dFlex">
                                    <p><?php echo esc_html($value['ttbm_faq_title']); ?></p>
                                    <div class="faq-action">
                                        <span class=""><i class="fas fa-eye"></i></span>
                                        <span class="ttbm-faq-item-edit" data-modal="ttbm-faq-item-new"><i class="fas fa-edit"></i></span>
                                        <span class="ttbm-faq-item-delete"><i class="fas fa-trash"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="faq-content" data-collapse="#faq-content-<?php echo esc_attr($key); ?>">
								<?php echo wp_kses_post($value['ttbm_faq_content']); ?>
                            </div>
                        </div>
					<?php
					endforeach;
				endif;
			}
			public function faq_data_update() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
					wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
				}
				$post_id = isset($_POST['ttbm_faq_postID']) ? absint(wp_unslash($_POST['ttbm_faq_postID'])) : 0;
				if (!$this->current_user_can_edit_faq_post($post_id)) {
					wp_send_json_error('Permission denied!', 403);
				}
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
				$post_id = isset($_POST['ttbm_faq_postID']) ? absint(wp_unslash($_POST['ttbm_faq_postID'])) : 0;
				if (!$this->current_user_can_edit_faq_post($post_id)) {
					wp_send_json_error('Permission denied!', 403);
				}
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
				$post_id = isset($_POST['ttbm_faq_postID']) ? absint(wp_unslash($_POST['ttbm_faq_postID'])) : 0;
				if (!$this->current_user_can_edit_faq_post($post_id)) {
					wp_send_json_error('Permission denied!', 403);
				}
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
		}
		new TTBM_Settings_faq();
	}
