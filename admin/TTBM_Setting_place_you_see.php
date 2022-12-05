<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'TTBM_Setting_place_you_see' ) ) {
		class TTBM_Setting_place_you_see {
			public function __construct() {
				add_action( 'add_ttbm_settings_tab_name', [ $this, 'add_tab' ], 10 );
				add_action( 'add_ttbm_settings_tab_content', [ $this, 'place_you_see_settings' ], 10, 1 );
				//*********add new Place***************//
				add_action( 'wp_ajax_load_ttbm_place_you_see_form', [ $this, 'load_ttbm_place_you_see_form' ] );
				add_action( 'wp_ajax_nopriv_load_ttbm_place_you_see_form', [ $this, 'load_ttbm_place_you_see_form' ] );
				add_action( 'wp_ajax_ttbm_reload_place_you_see_list', [ $this, 'ttbm_reload_place_you_see_list' ] );
				add_action( 'wp_ajax_nopriv_ttbm_reload_place_you_see_list', [ $this, 'ttbm_reload_place_you_see_list' ] );
			}
			public function add_tab() {
				?>
				<li data-tabs-target="#ttbm_settings_place_you_see">
					<span class="dashicons dashicons-location"></span><?php esc_html_e( ' Places You\'ll See', 'tour-booking-manager' ); ?>
				</li>
				<?php
			}
			public function place_you_see_settings( $tour_id ) {
				$ttbm_label = TTBM_Function::get_name();
				$display    = TTBM_Function::get_post_info( $tour_id, 'ttbm_display_hiphop', 'on' );
				$active     = $display == 'off' ? '' : 'mActive';
				$checked    = $display == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem mp_settings_area ttbm_settings_place_you_see" data-tabs="#ttbm_settings_place_you_see">
					<h5 class="dFlex">
						<span class="mR"><?php esc_html_e( 'Places You\'ll See ' . $ttbm_label . ' Settings', 'tour-booking-manager' ); ?></span>
						<?php TTBM_Layout::switch_button( 'ttbm_display_hiphop', $checked ); ?>
					</h5>
					<?php TTBM_Settings::des_p( 'ttbm_display_hiphop' ); ?>
					<div class="divider"></div>
					<div data-collapse="#ttbm_display_hiphop" class="ttbm_place_you_see_area <?php echo esc_attr( $active ); ?>">
						<?php $this->place_you_see( $tour_id ); ?>
					</div>
				</div>
				<?php
			}
			public function place_you_see( $tour_id ) {
				$hiphop_places = TTBM_Function::get_post_info( $tour_id, 'ttbm_hiphop_places', array() );
				$all_places    = TTBM_Query::query_post_type( 'ttbm_places' );
				$places        = $all_places->posts;
				?>
				<div class="ttbm_place_you_see_table">
					<table class="layoutFixed">
						<tbody>
						<tr>
							<th>
								<?php esc_html_e( 'Places You\'ll See :', 'tour-booking-manager' ); ?>
								<?php TTBM_Settings::des_p( 'ttbm_place_you_see' ); ?>
									<div class="divider"></div>
								<?php TTBM_Layout::popup_button_xs( 'add_new_place_you_see_popup', esc_html__( 'Create New Place', 'tour-booking-manager' ) ); ?>
							</th>
							<td colspan="3">
								<?php if ( $all_places->post_count > 0 ) { ?>
									<table>
										<thead>
										<tr>
											<th><?php esc_html_e( 'Place Label', 'tour-booking-manager' ); ?></th>
											<th><?php esc_html_e( 'Place', 'tour-booking-manager' ); ?></th>
											<th><?php esc_html_e( 'Action', 'tour-booking-manager' ); ?></th>
										</tr>
										</thead>
										<tbody class="mp_sortable_area mp_item_insert">
										<?php
											if ( sizeof( $hiphop_places ) ) {
												foreach ( $hiphop_places as $hiphop_place ) {
													$this->place_you_see_item( $places, $hiphop_place );
												}
											} else {
												$this->place_you_see_item( $places );
											}
										?>
										</tbody>
									</table>
									<?php
									TTBM_Layout::add_new_button( esc_html__( 'Add New Place', 'tour-booking-manager' ) );
								} else {
									TTBM_Layout::popup_button( 'add_new_place_you_see_popup', esc_html__( 'Create New Place', 'tour-booking-manager' ) );
								}
								?>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div class="mp_hidden_content">
					<table>
						<tbody class="mp_hidden_item">
						<?php $this->place_you_see_item( $places ); ?>
						</tbody>
					</table>
				</div>
				<?php
				wp_reset_postdata();
				$this->add_new_place_you_see_popup();
			}
			public function place_you_see_item( $places, $hiphop_place = array() ) {
				$place_id   = is_array( $hiphop_place ) && array_key_exists( 'ttbm_city_place_id', $hiphop_place ) ? $hiphop_place['ttbm_city_place_id'] : '';
				$place_name = is_array( $hiphop_place ) && array_key_exists( 'ttbm_place_label', $hiphop_place ) ? $hiphop_place['ttbm_place_label'] : '';
				$place_name = $place_id && ! $place_name ? get_the_title( $place_id ) : $place_name;
				?>
				<tr class="mp_remove_area">
					<td>
						<label>
							<input class="formControl mp_name_validation" name="ttbm_place_label[]" value="<?php echo esc_attr( $place_name ); ?>"/>
						</label>
					</td>
					<td>
						<label>
							<select class="formControl <?php echo esc_attr( is_array( $hiphop_place ) && sizeof( $hiphop_place ) > 0 ? 'ttbm_select2' : 'add_ttbm_select2' ); ?>" name="ttbm_city_place_id[]">
								<option value="" selected disabled>
									<?php esc_html_e( 'Please Select a Place', 'tour-booking-manager' ); ?>
								</option>
								<?php
									foreach ( $places as $place ) {
										$id = $place->ID;
										?>
										<option value="<?php echo esc_attr( $id ); ?>" <?php echo esc_attr( $id == $place_id ? 'selected' : '' ); ?>>
											<?php echo esc_html( $place->post_title ); ?>
										</option>
									<?php } ?>
							</select>
						</label>
					</td>
					<td><?php TTBM_Layout::move_remove_button(); ?></td>
				</tr>
				<?php
			}
			public function add_new_place_you_see_popup() {
				?>
				<div class="mpPopup" data-popup="add_new_place_you_see_popup">
					<div class="popupMainArea">
						<div class="popupHeader">
							<h4>
								<?php esc_html_e( 'Add New Place', 'tour-booking-manager' ); ?>
								<p class="_textSuccess_ml_dNone ttbm_success_info">
									<span class="fas fa-check-circle mR_xs"></span>
									<?php esc_html_e( 'Place is added successfully.', 'tour-booking-manager' ) ?>
								</p>
							</h4>
							<span class="fas fa-times popupClose"></span>
						</div>
						<div class="popupBody ttbm_place_you_see_form_area">
						</div>
						<div class="popupFooter">
							<div class="buttonGroup">
								<button class="_themeButton ttbm_new_place_you_see_save" type="button"><?php esc_html_e( 'Save', 'tour-booking-manager' ); ?></button>
								<button class="_warningButton ttbm_new_place_you_see_save_close" type="button"><?php esc_html_e( 'Save & Close', 'tour-booking-manager' ); ?></button>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			public function load_ttbm_place_you_see_form() {
				?>
				<label class="flexEqual">
					<span><?php esc_html_e( 'Place Name : ', 'tour-booking-manager' ); ?><sup class="textRequired">*</sup></span>
					<input type="text" name="ttbm_place_name" class="formControl" required>
				</label>
				<p class="textRequired" data-required="ttbm_place_name">
					<span class="fas fa-info-circle"></span>
					<?php esc_html_e( 'Place name is required!', 'tour-booking-manager' ); ?>
				</p>
				<?php TTBM_Settings::des_p('ttbm_place_name'); ?>
				<div class="divider"></div>
				<label class="flexEqual">
					<span><?php esc_html_e( 'Place Description : ', 'tour-booking-manager' ); ?></span>
					<textarea name="ttbm_place_description" class="formControl" rows="3"></textarea>
				</label>
				<?php TTBM_Settings::des_p('ttbm_place_description'); ?>
				<div class="divider"></div>
				<div class="flexEqual">
					<span><?php esc_html_e( 'Place Image : ', 'tour-booking-manager' ); ?><sup class="textRequired">*</sup></span>
					<?php TTBM_Layout::single_image_button( 'ttbm_place_image' ); ?>
				</div>
				<p class="textRequired" data-required="ttbm_place_image">
					<span class="fas fa-info-circle"></span>
					<?php esc_html_e( 'Place image is required!', 'tour-booking-manager' ); ?>
				</p>
				<?php TTBM_Settings::des_p('ttbm_place_image'); ?>
				<?php
				die();
			}
			public function ttbm_reload_place_you_see_list() {
				$ttbm_id = TTBM_Function::data_sanitize( $_POST['ttbm_id'] );
				$this->place_you_see( $ttbm_id );
				die();
			}
		}
		new TTBM_Setting_place_you_see();
	}