<?php
	/**
	 * @author Sahahdat Hossain <raselsha@gmail.com>
	 * @license mage-people.com
	 * @var 1.0.0
	 */
	if (!defined('ABSPATH'))
		die;
	if (!class_exists('TTBM_Hotel_Faq')) {
		class TTBM_Hotel_Faq {
			public function __construct() {
				add_action('add_ttbm_settings_hotel_tab_content', [$this, 'faq_settings']);
				add_action('admin_enqueue_scripts', [$this, 'my_custom_editor_enqueue']);
				// save faq data
				add_action('wp_ajax_ttbm_faq_data_save', [$this, 'save_faq_data_settings']);
				add_action('wp_ajax_nopriv_ttbm_faq_data_save', [$this, 'save_faq_data_settings']);
				// update faq data
				add_action('wp_ajax_ttbm_faq_data_update', [$this, 'faq_data_update']);
				add_action('wp_ajax_nopriv_ttbm_faq_data_update', [$this, 'faq_data_update']);
				// ttbm_delete_faq_data
				add_action('wp_ajax_ttbm_faq_delete_item', [$this, 'faq_delete_item']);
				add_action('wp_ajax_nopriv_ttbm_faq_delete_item', [$this, 'faq_delete_item']);
				// FAQ sort_faq
				add_action('wp_ajax_ttbm_sort_faq', [$this, 'sort_faq']);
			}

			public function sort_faq() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
					wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
				}
				$post_id = isset($_POST['postID']) ? sanitize_text_field(wp_unslash($_POST['postID'])) : '';
				$sorted_ids = isset($_POST['sortedIDs']) ? array_map('intval', $_POST['sortedIDs']) : [];
				$ttbm_faq = get_post_meta($post_id, 'ttbm_faq', true);;
				$new_ordered = [];
				foreach ($sorted_ids as $id) {
					if (isset($ttbm_faq[$id])) {
						$new_ordered[$id] = $ttbm_faq[$id];
					}
				}
				update_post_meta($post_id, 'ttbm_faq', $new_ordered);
				ob_start();
				$resultMessage = esc_html__('Data Updated Successfully', 'service-booking-manager');
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
			public function faq_settings($post_id) {
				$faq_status = get_post_meta($post_id, 'ttbm_hotel_faq_status', 'off');
				$active_class = $faq_status == 'on' ? 'mActive' : '';
				$ttbm_faq_active_checked = $faq_status == 'on' ? 'checked' : '';
				?>
                <div class="tabsItem ttbm_settings_hotel_faq" data-tabs="#ttbm_settings_hotel_faq">
                    <header>
                        <h2><?php esc_html_e('FAQ Settings', 'service-booking-manager'); ?></h2>
                        <span><?php esc_html_e('FAQ Settings will be here.', 'service-booking-manager'); ?></span>
                    </header>
                    <section>
                        <label class="label">
                            <p><?php esc_html_e('Enable FAQ Section', 'service-booking-manager'); ?></p>
                            <?php TTBM_Custom_Layout::switch_button('ttbm_hotel_faq_status', $ttbm_faq_active_checked); ?>

                        </label>
                    </section>
                    <section class="ttbm-faq-section <?php echo esc_attr($active_class); ?>" data-collapse="#ttbm_hotel_faq_status">
                        <div class="ttbm-faq-items mB">
							<?php $this->show_faq_data($post_id); ?>
                        </div>
                        <button class="button ttbm-faq-item-new" data-modal="ttbm-faq-item-new" type="button"><?php esc_html_e('Add FAQ', 'service-booking-manager'); ?></button>
                    </section>
                    <!-- sidebar collapse open -->
                    <div class="ttbm-modal-container" data-modal-target="ttbm-faq-item-new">
                        <div class="ttbm-modal-content">
                            <span class="ttbm-modal-close"><i class="fas fa-times"></i></span>
                            <div class="title">
                                <h3><?php esc_html_e('Add F.A.Q.', 'service-booking-manager'); ?></h3>
                                <div id="ttbm-service-msg"></div>
                            </div>
                            <div class="content">
                                <label>
									<?php esc_html_e('Add Title', 'service-booking-manager'); ?>
                                    <input type="hidden" name="ttbm_post_id" value="<?php echo esc_attr($post_id); ?>">
                                    <input type="text" name="ttbm_faq_title">
                                    <input type="hidden" name="ttbm_faq_item_id">
                                </label>
                                <label>
									<?php esc_html_e('Add Content', 'service-booking-manager'); ?>
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
                                <div class="mT"></div>
                                <div class="ttbm_faq_save_buttons">
                                    <p>
                                        <button id="ttbm_faq_save" class="button button-primary button-large"><?php esc_html_e('Save', 'service-booking-manager'); ?></button>
                                        <button id="ttbm_faq_save_close" class="button button-primary button-large">save close</button>
                                    <p>
                                </div>
                                <div class="ttbm_faq_update_buttons" style="display: none;">
                                    <p>
                                        <button id="ttbm_faq_update" class="button button-primary button-large"><?php esc_html_e('Update and Close', 'service-booking-manager'); ?></button>
                                    <p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}

			public function show_faq_data($post_id) {
				$ttbm_faq = get_post_meta($post_id, 'ttbm_faq', true);
				if (!empty($ttbm_faq)):
					foreach ($ttbm_faq as $key => $value) :
						?>
                        <div class="ttbm-faq-item" data-id="<?php echo esc_attr($key); ?>">
                            <section class="faq-header" data-collapse-target="#faq-content-<?php echo esc_attr($key); ?>">
                                <label class="label">
                                    <p><?php echo esc_html($value['title']); ?></p>
                                    <div class="faq-action">
                                        <span class=""><i class="fas fa-eye"></i></span>
                                        <span class="ttbm-faq-item-edit" data-modal="ttbm-faq-item-new"><i class="fas fa-edit"></i></span>
                                        <span class="ttbm-faq-item-delete"><i class="fas fa-trash"></i></span>
                                    </div>
                                </label>
                            </section>
                            <section class="faq-content mB" data-collapse="#faq-content-<?php echo esc_attr($key); ?>">
								<?php echo wp_kses_post($value['content']); ?>
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
				$ttbm_faq_content = isset($_POST['ttbm_faq_content']) ? wp_kses_post(wp_unslash($_POST['ttbm_faq_content'])) : '';
				$ttbm_faq = get_post_meta($post_id, 'ttbm_faq', true);
				$ttbm_faq = !empty($ttbm_faq) ? $ttbm_faq : [];
				$new_data = ['title' => $ttbm_faq_title, 'content' => $ttbm_faq_content];
				if (!empty($ttbm_faq)) {
					if (isset($_POST['ttbm_faq_itemID'])) {
						$ttbm_faq[sanitize_text_field(wp_unslash($_POST['ttbm_faq_itemID']))] = $new_data;
					}
				}
				update_post_meta($post_id, 'ttbm_faq', $ttbm_faq);
				ob_start();
				$resultMessage = esc_html__('Data Updated Successfully', 'service-booking-manager');
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
				update_post_meta($post_id, 'ttbm_hotel_faq_status', 'on');
				$ttbm_faq_title = isset($_POST['ttbm_faq_title']) ? sanitize_text_field(wp_unslash($_POST['ttbm_faq_title'])) : '';
				$ttbm_faq_content = isset($_POST['ttbm_faq_content']) ? wp_kses_post(wp_unslash($_POST['ttbm_faq_content'])) : '';
				$ttbm_faq = get_post_meta($post_id, 'ttbm_faq', true);
				$ttbm_faq = !empty($ttbm_faq) ? $ttbm_faq : [];
				$new_data = ['title' => $ttbm_faq_title, 'content' => $ttbm_faq_content];
				if (isset($post_id)) {
					array_push($ttbm_faq, $new_data);
				}
				$result = update_post_meta($post_id, 'ttbm_faq', $ttbm_faq);
				if ($result) {
					ob_start();
					$resultMessage = esc_html__('Data Added Successfully', 'service-booking-manager');
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
				$ttbm_faq = get_post_meta($post_id, 'ttbm_faq', true);
				$ttbm_faq = !empty($ttbm_faq) ? $ttbm_faq : [];
				if (!empty($ttbm_faq)) {
					if (isset($_POST['itemId'])) {
						unset($ttbm_faq[sanitize_text_field(wp_unslash($_POST['itemId']))]);
						$ttbm_faq = array_values($ttbm_faq);
					}
				}
				$result = update_post_meta($post_id, 'ttbm_faq', $ttbm_faq);
				if ($result) {
					ob_start();
					$resultMessage = esc_html__('Data Deleted Successfully', 'service-booking-manager');
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
		new TTBM_Hotel_Faq();
	}