<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Layout')) {
		class TTBM_Layout {
			public function __construct() {
				add_action('ttbm_hidden_item_table', array($this, 'hidden_item_table'), 10, 2);
			}
			/****************************/
			public function hidden_item_table($hook_name, $data = array()) {
				?>
				<div class="mp_hidden_content">
					<table>
						<tbody class="mp_hidden_item">
						<?php do_action($hook_name, $data); ?>
						</tbody>
					</table>
				</div>
				<?php
			}
			/*****************************/
			public static function switch_button($name, $checked = '') {
				?>
				<label class="roundSwitchLabel">
					<input type="checkbox" name="<?php echo esc_attr($name); ?>" <?php echo esc_attr($checked); ?>>
					<span class="roundSwitch" data-collapse-target="#<?php echo esc_attr($name); ?>"></span>
				</label>
				<?php
			}
			public static function single_image_button($name) {
				?>
				<div class="mp_add_single_image">
					<input type="hidden" name="<?php echo esc_attr($name); ?>"/>
					<button type="button" class="_dButton_xs">
						<span class="fas fa-plus-square"></span>
						<?php esc_html_e('Add Image', 'tour-booking-manager'); ?>
					</button>
				</div>
				<?php
			}
			public static function add_multi_image($name, $images) {
				$images = is_array($images) ? MP_Global_Function::array_to_string($images) : $images;
				?>
				<div class="mp_multi_image_area">
					<input type="hidden" class="mp_multi_image_value" name="<?php echo esc_attr($name); ?>" value="<?php esc_attr_e($images); ?>"/>
					<div class="mp_multi_image">
						<?php
							$all_images = explode(',', $images);
							if ($images && sizeof($all_images) > 0) {
								foreach ($all_images as $image) {
									?>
									<div class="mp_multi_image_item" data-image-id="<?php esc_attr_e($image); ?>">
										<span class="fas fa-times circleIcon_xs mp_remove_multi_image"></span>
										<img class="w-100" src="<?php echo MP_Global_Function::get_image_url('', $image, 'medium'); ?>" alt="<?php esc_attr_e($image); ?>"/>
									</div>
									<?php
								}
							}
						?>
					</div>
					<div class="">
						<?php MP_Custom_Layout::add_new_button(esc_html__('Add Image', 'tour-booking-manager'), 'add_multi_image', 'btn'); ?>
					</div>
				</div>
				<?php
			}
			/*****************************/
			public static function availability_button($tour_id) {
				$travel_type = TTBM_Function::get_travel_type($tour_id);
				$check_ability = MP_Global_Function::get_post_info($tour_id, 'ttbm_ticketing_system', 'availability_section');
				// if ($check_ability == 'availability_section' && $travel_type != 'fixed') { 
					?>
					<button class="navy_blueButton ttbm_check_ability" type="button">
						<?php esc_html_e('Check  Availability', 'tour-booking-manager'); ?>
					</button>
				<?php // }
			}
			public static function ttbm_add_button($button_text, $class = 'ttbm_add_item', $button_class = 'btn my-2', $icon_class = 'fas fa-plus-square') {
				?>
				<button class="<?php echo esc_attr($button_class . ' ' . $class); ?>" type="button">
					<span class="<?php echo esc_attr($icon_class); ?> pe-1"></span>
					<span class="ml_xs"><?php echo esc_html($button_text); ?></span>
				</button>
				<?php
			}
			public static function add_new_button($button_text, $class = 'mp_add_item', $button_class = 'btn', $icon_class = 'fas fa-plus-square') {
				?>
				<button class="<?php echo esc_attr($button_class . ' ' . $class); ?>" type="button">
					<span class="<?php echo esc_attr($icon_class); ?>"></span>
					<span class="ml_xs"><?php echo esc_html($button_text); ?></span>
				</button>
				<?php
			}
			public static function move_remove_button() {
				?>
				<div class="allCenter">
					<div class="buttonGroup max_100">
						<button class="_warningButton_xs mp_item_remove" type="button">
							<span class="fas fa-trash-alt mp_zero"></span>
						</button>
						<div class="_mpBtn_themeButton_xs mp_sortable_button" type="">
							<span class="fas fa-expand-arrows-alt mp_zero"></span>
						</div>
					</div>
				</div>
				<?php
			}
			public static function qty_input($name, $available_seat, $ticket_qty_type, $default_qty, $min_qty, $max_qty, $ticket_price_raw, $input_name,$tour_id='') {
				$min_qty = max($default_qty, $min_qty);
				$data_ticket_name = preg_replace('/[^A-Za-z0-9\-]/', '', $name);
				if ($available_seat > $min_qty) {
					?>
					<div data-ticket-type-name="<?php echo esc_attr($data_ticket_name); ?>">
						<?php
							if ($ticket_qty_type == 'inputbox') {
								?>
								<div class="groupContent qtyIncDec" data-ticket-type-name="<?php echo esc_html($data_ticket_name); ?>">
									<div class="decQty addonGroupContent">
										<span class="fas fa-minus"></span>
									</div>
									<label>
										<input type="text"
											class="formControl inputIncDec"
											data-price="<?php echo esc_attr($ticket_price_raw); ?>"
											name="<?php echo esc_attr($input_name); ?>"
											value="<?php echo esc_attr(max(0, $default_qty)); ?>"
											min="<?php echo esc_attr($min_qty); ?>"
											max="<?php echo esc_attr($max_qty > 0 ? $max_qty : $available_seat); ?>"
										/>
									</label>
									<div class="incQty addonGroupContent">
										<span class="fas fa-plus"></span>
									</div>
								</div>
							<?php } elseif ($ticket_qty_type == 'dropdown') { ?>
								<label data-ticket-type-name="<?php echo esc_html($data_ticket_name); ?>">
									<select name="<?php echo esc_attr($input_name); ?>" data-price="<?php echo esc_html($ticket_price_raw); ?>" class="formControl">
										<?php
											$max_total = $max_qty > 0 ? $max_qty : $available_seat;
											for ($i = $min_qty; $i <= $max_total; $i++) {
												?>
												<option value="<?php echo esc_html($i); ?>"> <?php echo esc_html($i); ?> </option>
											<?php } ?>
									</select>
								</label>
							<?php } ?>
					</div>
				<?php } else { ?>
					<input type="hidden" name="<?php echo esc_attr($input_name); ?>"/>
					<span class='textWarning'>
						<?php  esc_html_e('Sorry, Not Available', 'tour-booking-manager'); ?>
					</span>
					<?php
				}
			}
			public static function tour_list_in_select() {
				$label = TTBM_Function::get_name();
				?>
				<label class="min_400 ttbm_id_select">
					<select name="ttbm_id" class="formControl ttbm_select2" id="all_tour_list" required>
						<option value="" selected><?php echo esc_html__('Select', 'tour-booking-manager') . ' ' . esc_html($label); ?></option>
						<?php
							$post_query = MP_Global_Function::query_post_type(TTBM_Function::get_cpt_name());
							$all_posts = $post_query->posts;
							foreach ($all_posts as $post) {
								$ttbm_post_id = $post->ID;
								$tour_id = TTBM_Function::post_id_multi_language($ttbm_post_id);
								if ($ttbm_post_id == $tour_id) {
									$recurring = TTBM_Function::recurring_check($ttbm_post_id);
									$tour_type = TTBM_Function::get_tour_type($ttbm_post_id);
									?>
									<option value="<?php echo $ttbm_post_id; ?>" data-recurring="<?php echo esc_attr($recurring ? 'yes' : ''); ?>">
										<?php echo get_the_title($ttbm_post_id); ?>
										<?php esc_html_e($recurring ? '- Multi date' : ''); ?>
										<?php esc_html_e($tour_type == 'hotel' ? '- Hotel' : ''); ?>
									</option>
									<?php
								}
							}
							wp_reset_postdata();
						?>
					</select>
				</label>
				<?php
			}
			/****************************/
			public static function pro_text() {
				ob_start();
				if (!class_exists('TTBM_Woocommerce_Plugin_Pro')) {
					?>
					&nbsp;
					<h6 class="ttbm_pro_badge">Pro</h6>
					<?php
				}
				return ob_get_clean();
			}
			public static function no_pro_disabled($name): string {
				$text = '';
				if (!class_exists('TTBM_Woocommerce_Plugin_Pro')) {
					if ($name == 'ttbm_particular_dates' || $name == 'mep_ticket_times_global' || $name == 'mep_disable_ticket_time') {
						$text = 'disabled';
					}
					if ($name == 'mep_ticket_times_sat' || $name == 'mep_ticket_times_sun' || $name == 'mep_ticket_times_mon') {
						$text = 'disabled';
					}
					if ($name == 'mep_ticket_times_tue' || $name == 'mep_ticket_times_wed' || $name == 'mep_ticket_times_thu') {
						$text = 'disabled';
					}
					if ($name == 'mep_ticket_times_fri' || $name == 'mep_ticket_off_dates' || $name == 'mep_ticket_offdays') {
						$text = 'disabled';
					}
				}
				return $text;
			}
		}
		new TTBM_Layout();
	}