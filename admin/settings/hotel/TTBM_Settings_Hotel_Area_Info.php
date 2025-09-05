<?php
	/**
	 * @author Sahahdat Hossain <raselsha@gmail.com>
	 * @license mage-people.com
	 * @var 1.0.0
	 */
	if (!defined('ABSPATH'))
		die;
	if (!class_exists('TTBM_hotel_area_info')) {
		class TTBM_hotel_area_info {
			public function __construct() {
				add_action('add_ttbm_settings_hotel_tab_content', [$this, 'naearest_area_settings']);
				add_action('ttbm_single_hotel_area', [$this, 'frontend_hotel_area_info']);

				add_action('save_post', [$this, 'save_hotel_area_info']);

				add_action('admin_enqueue_scripts', [$this, 'my_custom_editor_enqueue']);
				// save faq data
				add_action('wp_ajax_ttbm_hotel_faq_save', [$this, 'save_faq_data_settings']);
				// update faq data
				add_action('wp_ajax_ttbm_hotel_faq_update', [$this, 'faq_data_update']);
				// ttbm_delete_faq_data
				add_action('wp_ajax_ttbm_hotel_faq_delete', [$this, 'faq_delete_item']);
				// FAQ sort_faq
				add_action('wp_ajax_ttbm_hotel_ttbm_faq_sort', [$this, 'sort_faq']);
			}

			public function my_custom_editor_enqueue() {
				// Enqueue necessary scripts
				wp_enqueue_script('jquery');
				wp_enqueue_script('editor');
				wp_enqueue_script('media-upload');
				wp_enqueue_script('thickbox');
				wp_enqueue_style('thickbox');
			}

			public function naearest_area_settings($post_id) {
				$area_status = get_post_meta($post_id, 'ttbm_hotel_area_status', 'off');
				$active_class = $area_status == 'on' ? 'mActive' : '';
				$ttbm_faq_active_checked = $area_status == 'on' ? 'checked' : '';
				?>
                <div class="tabsItem ttbm_settings_hotel_area_info" data-tabs="#ttbm_settings_hotel_area_info">
                    
                    <h2><?php esc_html_e('Hotel area info', 'tour-booking-manager'); ?></h2>
                    <p><?php esc_html_e('Hotel area info Settings will be here.', 'tour-booking-manager'); ?></p>
                    
                    <section>
                        <div class="ttbm-header">
                            <h4><i class="fas fa-question-circle"></i><?php esc_html_e('Enable Area Info Section', 'tour-booking-manager'); ?></h4>
                            <?php TTBM_Custom_Layout::switch_button('ttbm_hotel_area_status', $ttbm_faq_active_checked); ?>
                        </div>
						<div class="ttbm-htl-area <?php echo esc_attr($active_class); ?>" data-collapse="#ttbm_hotel_area_status">
							<?php 
								$this->show_hotel_area_info($post_id);
							?>
						</div>
                    </section>
                </div>
				<?php
			}

			public function show_hotel_area_info($post_id) {
				$ttbm_hotel_area_info = get_post_meta($post_id, 'ttbm_hotel_area_info', true);
				$ttbm_hotel_area_info = !empty($ttbm_hotel_area_info) ? $ttbm_hotel_area_info : [];

				// Example default if empty
				if (empty($ttbm_hotel_area_info)) {
					$ttbm_hotel_area_info = [
						[
							'area_icon' => 'mi mi-restaurants',
							'area_title' => __("What's nearby", 'tour-booking-manager'),
							'area_items' => [
								[
									'item_title' => '',
									'item_distance' => 0,
									'item_type' => 'km',
								],
							],
						],
					];
				}
				?>
				<div id="ttbm-hotel-area-info-wrapper">
					<?php foreach ($ttbm_hotel_area_info as $area_index => $area_info): ?>
						<div class="ttbm-htl-area-section" data-area-index="<?php echo esc_attr($area_index); ?>">
							<div class="ttbm-htl-area-header">
								<input type="hidden" name="ttbm_hotel_area_info[<?php echo esc_attr($area_index); ?>][area_icon]" value="<?php echo esc_attr($area_info['area_icon']); ?>">
								
								<div class="icon">
									<?php 
									$area_icon_name = 'ttbm_hotel_area_info['.$area_index.'][area_icon]';
									do_action('ttbm_input_add_icon', $area_icon_name, $area_info['area_icon']); 
									?>
								</div>
								<input type="text" name="ttbm_hotel_area_info[<?php echo esc_attr($area_index); ?>][area_title]" class="ttbm-htl-area-title"
									value="<?php echo esc_attr($area_info['area_title']); ?>"
									placeholder="<?php esc_attr_e("What's nearby", 'tour-booking-manager'); ?>">
								<div class="action-buttons">
									<button type="button" class="btn icon ttbm-add-area" data-area-index="<?php echo esc_attr($area_index); ?>">
										<i class="mi mi-plus"></i>
									</button>
									<button type="button" class="btn icon ttbm-delete-area" data-area-index="<?php echo esc_attr($area_index); ?>">
										<i class="mi mi-trash"></i>
									</button>
								</div>
							</div>
							<div class="ttbm-htl-area-items">
								<?php
								if (!empty($area_info['area_items'])):
									foreach ($area_info['area_items'] as $item_index => $info_items): ?>
									<div class="ttbm-htl-area-item" data-item-index="<?php echo esc_attr($item_index); ?>">
										<input type="text" class="ttbm-htl-area-item-title" name="ttbm_hotel_area_info[<?php echo esc_attr($area_index); ?>][area_items][<?php echo esc_attr($item_index); ?>][item_title]"
											
											value="<?php echo esc_attr($info_items['item_title']); ?>"
											placeholder="<?php esc_attr_e('Area name', 'tour-booking-manager'); ?>">
										<input type="number" step="any" min="0"
											name="ttbm_hotel_area_info[<?php echo esc_attr($area_index); ?>][area_items][<?php echo esc_attr($item_index); ?>][item_distance]"
											
											value="<?php echo esc_attr($info_items['item_distance']); ?>"
											placeholder="<?php esc_attr_e('Distance', 'tour-booking-manager'); ?>">
										<input type="text"
											name="ttbm_hotel_area_info[<?php echo esc_attr($area_index); ?>][area_items][<?php echo esc_attr($item_index); ?>][item_type]"
											
											value="<?php echo esc_attr($info_items['item_type']); ?>"
											placeholder="<?php esc_attr_e('Type (e.g. km, m)', 'tour-booking-manager'); ?>">
										<div class="action-buttons">
											<button type="button" class="icon ttbm-add-area-item" data-area-index="<?php echo esc_attr($area_index); ?>" data-item-index="<?php echo esc_attr($item_index); ?>">
												<i class="mi mi-plus"></i>
											</button>
											<button type="button" class="icon ttbm-delete-feature" data-area-index="<?php echo esc_attr($area_index); ?>" data-item-index="<?php echo esc_attr($item_index); ?>">
												<i class="mi mi-trash"></i>
											</button>
										</div>
									</div>
								<?php endforeach;
								endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<button type="button" class="_themeButton_xs ttbm-add-area-info">
					<i class="mi mi-plus"></i>
					<?php _e('Add New Area Info', 'tour-booking-manager'); ?>
				</button>
				<?php
			}

			public function frontend_hotel_area_info() {
				$post_id = get_the_id();
				$area_status = get_post_meta($post_id, 'ttbm_hotel_area_status', true);
				$ttbm_hotel_area_info = get_post_meta($post_id, 'ttbm_hotel_area_info', true);
				$ttbm_hotel_area_info = !empty($ttbm_hotel_area_info) ? $ttbm_hotel_area_info : [];
				if($area_status == 'on' && !empty($ttbm_hotel_area_info)):?>
					<div class="ttbm-hotel-area-info">
						<h2><?php _e("Hotel Area Info",'tour-booking-manager'); ?></h2>	
						<div class="ttbm-area-section">
							<?php foreach($ttbm_hotel_area_info as $hotel_area): ?>
								<div class="ttbm-area-items">
									<h2><i class="<?php echo esc_attr($hotel_area['area_icon']) ?>"></i> <?php echo esc_html($hotel_area['area_title']) ?></h2>
									<?php if(!empty($hotel_area['area_items'])): foreach($hotel_area['area_items'] as $area_item): ?>
										<div class="ttbm-area-item">
											<p><?php echo esc_html($area_item['item_title']); ?></p>
											<p><?php echo esc_html($area_item['item_distance']); ?> <span><?php echo esc_html($area_item['item_type']); ?></span></p>
										</div>
									<?php endforeach; endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; 
			}

			/**
			 * Save hotel area info data on post save
			 */
			public function save_hotel_area_info($post_id) {
				if (!isset($_POST['ttbm_hotel_type_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ttbm_hotel_type_nonce'])), 'ttbm_hotel_type_nonce') && defined('DOING_AUTOSAVE') && DOING_AUTOSAVE && !current_user_can('edit_post', $post_id)) {
					return;
				}

				if (get_post_type($post_id) == 'ttbm_hotel') {
					if (!empty($_POST['ttbm_hotel_area_info']) && is_array($_POST['ttbm_hotel_area_info'])) {
						$area_info = $_POST['ttbm_hotel_area_info'];
						
						$sanitized = [];
						foreach ($area_info as $area) {
							$area_icon = isset($area['area_icon']) ? sanitize_text_field($area['area_icon']) : '';
							$area_title = isset($area['area_title']) ? sanitize_text_field($area['area_title']) : '';
							$area_items = [];
							if (isset($area['area_items']) && is_array($area['area_items'])) {
								foreach ($area['area_items'] as $item) {
									$area_items[] = [
										'item_title'    => isset($item['item_title']) ? sanitize_text_field($item['item_title']) : '',
										'item_distance' => isset($item['item_distance']) ? floatval($item['item_distance']) : '',
										'item_type'     => isset($item['item_type']) ? sanitize_text_field($item['item_type']) : '',
									];
								}
							}
							$sanitized[] = [
								'area_icon'  => $area_icon,
								'area_title' => $area_title,
								'area_items' => $area_items,
							];
							$sanitized = !empty($sanitized)?$sanitized:[];
						}
						$hotel_area_status = isset($_POST['ttbm_hotel_area_status']) && sanitize_text_field(wp_unslash($_POST['ttbm_hotel_area_status'])) ? 'on' : 'off';
						update_post_meta($post_id, 'ttbm_hotel_area_status', $hotel_area_status);
						update_post_meta($post_id, 'ttbm_hotel_area_info',$sanitized);
					}
					else{
						update_post_meta($post_id, 'ttbm_hotel_area_info', []);
					}
				}
			}
		}
		new TTBM_hotel_area_info();
	}