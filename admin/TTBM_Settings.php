<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'TTBM_Settings' ) ) {
		class TTBM_Settings {
			public function __construct() {
				add_action( 'add_meta_boxes', [ $this, 'ttbm_settings_meta' ] );
				add_action( 'admin_init', [ $this, 'tour_settings_meta_box' ], 10 );
				//********Location************//
				add_action( 'wp_ajax_load_ttbm_location_form', [ $this, 'load_ttbm_location_form' ] );
				add_action( 'wp_ajax_nopriv_load_ttbm_location_form', [ $this, 'load_ttbm_location_form' ] );
				add_action( 'wp_ajax_ttbm_reload_location_list', [ $this, 'ttbm_reload_location_list' ] );
				add_action( 'wp_ajax_nopriv_ttbm_reload_location_list', [ $this, 'ttbm_reload_location_list' ] );
			}
			//************************//
			public function ttbm_settings_meta() {
				$ttbm_label = TTBM_Function::get_name();
				add_meta_box( 'ttbm_add_meta_box', '<span class="dashicons dashicons-info"></span>' . $ttbm_label . esc_html__( ' Information Settings : ', 'tour-booking-manager' ) . get_the_title( get_the_id() ), array( $this, 'ttbm_settings' ), 'ttbm_tour', 'normal', 'high' );
			}
			//******************************//
			public function ttbm_settings() {
				$tour_id    = get_the_id();
				$ttbm_label = TTBM_Function::get_name();
				?>
				<div class="mpStyle ttbm_settings">
					<div class="mpTabs leftTabs">
						<ul class="tabLists">
							<li data-tabs-target="#ttbm_general_info">
								<span class="dashicons dashicons-admin-settings"></span><?php esc_html_e( 'General Info', 'tour-booking-manager' ); ?>
							</li>
							<?php do_action( 'ttbm_meta_box_tab_name', $tour_id ); ?>
							<?php do_action( 'add_ttbm_settings_tab_name' ); ?>
							<li data-tabs-target="#ttbm_settings_gallery">
								<span class="dashicons dashicons-images-alt"></span><?php esc_html_e( 'Gallery ', 'tour-booking-manager' ); ?>
							</li>
							<li data-tabs-target="#ttbm_settings_extras">
								<span class="dashicons dashicons-text-page"></span><?php esc_html_e( 'Extras ', 'tour-booking-manager' ); ?>
							</li>
							<li data-tabs-target="#ttbm_settings_related_tour">
								<span class="dashicons dashicons-location-alt"></span><?php echo esc_html__( 'Related ', 'tour-booking-manager' ) . $ttbm_label; ?>
							</li>
							<li data-tabs-target="#ttbm_display_settings">
								<span class="dashicons dashicons-index-card"></span><?php esc_html_e( ' Display settings', 'tour-booking-manager' ); ?>
							</li>
							<?php if ( is_plugin_active( 'mage-partial-payment-pro/mage_partial_pro.php' ) ) : ?>
								<li data-tabs-target="#_mep_pp_deposits_type">
									<span class="dashicons dashicons-index-card"></span>&nbsp;&nbsp;<?php esc_html_e( 'Partial Payment', 'bus-ticket-booking-with-seat-reservation' ); ?>
								</li>
							<?php endif; ?>
						</ul>
						<div class="tabsContent tab-content">
							<?php
								$this->general_info( $tour_id );
								do_action( 'ttbm_meta_box_tab_content', $tour_id );
								do_action( 'add_ttbm_settings_tab_content', $tour_id );
								$this->gallery_settings( $tour_id );
								$this->extras_settings( $tour_id );
								$this->related_tour_settings( $tour_id );
								$this->details_page_settings( $tour_id );
								$this->partial_payment_settings( $tour_id );
							?>
						</div>
					</div>
				</div>
				<?php
			}
			//***************//
			public function general_info( $tour_id ) {
				?>
				<div class="tabsItem ttbm_settings_general" data-tabs="#ttbm_general_info">
					<h5><?php esc_html_e( 'General Information Settings', 'tour-booking-manager' ); ?></h5>
					<div class="divider"></div>
					<table class="layoutFixed">
						<tbody>
						<?php $this->duration( $tour_id ); ?>
						<?php $this->start_price( $tour_id ); ?>
						<?php $this->max_people( $tour_id ); ?>
						<?php $this->age_range( $tour_id ); ?>
						<?php $this->start_place( $tour_id ); ?>
						<?php $this->location( $tour_id ); ?>
						<?php $this->full_location( $tour_id ); ?>
						<?php $this->short_des( $tour_id ); ?>
						</tbody>
					</table>
				</div>
				<?php
			}
			public function duration( $tour_id ) {
				$value_name    = 'ttbm_travel_duration';
				$value         = TTBM_Function::get_post_info( $tour_id, $value_name );
				$duration_type = TTBM_Function::get_post_info( $tour_id, 'ttbm_travel_duration_type', 'day' );
				$placeholder   = esc_html__( 'Ex: 3', 'tour-booking-manager' );
				$display_name  = 'ttbm_display_duration_night';
				$display       = TTBM_Function::get_post_info( $tour_id, $display_name, 'off' );
				$checked       = $display == 'off' ? '' : 'checked';
				$active        = $display == 'off' ? '' : 'mActive';
				?>
				<tr>
					<th colspan="3" rowspan="2">
						<?php esc_html_e( 'Tour Duration', 'tour-booking-manager' ); ?>
						<?php self::des_p( 'duration' ); ?>
					</th>
					<td colspan="2">
						<label>
							<input class="formControl" name="<?php echo esc_attr( $value_name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"/>
						</label>
					</td>
					<td colspan="2">
						<label>
							<select class="formControl" name="ttbm_travel_duration_type">
								<option value="day" <?php echo esc_attr( $duration_type == 'day' ? 'selected' : '' ); ?>><?php esc_html_e( 'Days', 'tour-booking-manager' ); ?></option>
								<option value="hour" <?php echo esc_attr( $duration_type == 'hour' ? 'selected' : '' ); ?>><?php esc_html_e( 'Hours', 'tour-booking-manager' ); ?></option>
								<option value="min" <?php echo esc_attr( $duration_type == 'min' ? 'selected' : '' ); ?>><?php esc_html_e( 'Minutes', 'tour-booking-manager' ); ?> </option>
							</select>
						</label>
					</td>
				</tr>
				<tr>
					<th colspan="2">
						<div class="dFlex">
							<h5 class="mR_xs"><?php esc_html_e( 'Night:', 'tour-booking-manager' ); ?></h5>
							<?php TTBM_Layout::switch_button( $display_name, $checked ); ?>
						</div>
					</th>
					<td colspan="2">
						<div class="<?php echo esc_attr( $active ); ?>" data-collapse="#<?php echo esc_attr( $display_name ); ?>">
							<label>
								<input class="formControl" name="ttbm_travel_duration_night" value="<?php echo TTBM_Function::get_post_info( $tour_id, 'ttbm_travel_duration_night' ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"/>
							</label>
						</div>
					</td>
				</tr>
				<?php
			}
			public function start_price( $tour_id ) {
				$display_name = 'ttbm_display_price_start';
				$display      = TTBM_Function::get_post_info( $tour_id, $display_name, 'on' );
				$value_name   = 'ttbm_travel_start_price';
				$value        = TTBM_Function::get_post_info( $tour_id, $value_name );
				$placeholder  = esc_html__( 'Type Start Price', 'tour-booking-manager' );
				$checked      = $display == 'off' ? '' : 'checked';
				$active       = $display == 'off' ? '' : 'mActive';
				?>
				<tr>
					<th colspan="3"><?php esc_html_e( 'Tour Start Price', 'tour-booking-manager' ); ?></th>
					<td><?php TTBM_Layout::switch_button( $display_name, $checked ); ?></td>
					<td colspan="3">
						<label data-collapse="#<?php echo esc_attr( $display_name ); ?>" class="<?php echo esc_attr( $active ); ?>">
							<input class="formControl" name="<?php echo esc_attr( $value_name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"/>
						</label>
					</td>
				</tr>
				<?php
				self::des_row( 'start_price' );
			}
			public function max_people( $tour_id ) {
				$display_name = 'ttbm_display_max_people';
				$display      = TTBM_Function::get_post_info( $tour_id, $display_name, 'on' );
				$value_name   = 'ttbm_travel_max_people_allow';
				$value        = TTBM_Function::get_post_info( $tour_id, $value_name );
				$placeholder  = esc_html__( '50', 'tour-booking-manager' );
				$checked      = $display == 'off' ? '' : 'checked';
				$active       = $display == 'off' ? '' : 'mActive';
				?>
				<tr>
					<th colspan="3"><?php esc_html_e( 'Max People Allow', 'tour-booking-manager' ); ?></th>
					<td><?php TTBM_Layout::switch_button( $display_name, $checked ); ?></td>
					<td colspan="3">
						<label data-collapse="#<?php echo esc_attr( $display_name ); ?>" class="<?php echo esc_attr( $active ); ?>">
							<input class="formControl" name="<?php echo esc_attr( $value_name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"/>
						</label>
					</td>
				</tr>
				<?php
				self::des_row( 'max_people' );
			}
			public function age_range( $tour_id ) {
				$display_name = 'ttbm_display_min_age';
				$display      = TTBM_Function::get_post_info( $tour_id, $display_name, 'on' );
				$value_name   = 'ttbm_travel_min_age';
				$value        = TTBM_Function::get_post_info( $tour_id, $value_name );
				$placeholder  = esc_html__( 'Ex: 5 - 50 Years', 'tour-booking-manager' );
				$checked      = $display == 'off' ? '' : 'checked';
				$active       = $display == 'off' ? '' : 'mActive';
				?>
				<tr>
					<th colspan="3"><?php esc_html_e( 'Age Range', 'tour-booking-manager' ); ?></th>
					<td><?php TTBM_Layout::switch_button( $display_name, $checked ); ?></td>
					<td colspan="3">
						<label data-collapse="#<?php echo esc_attr( $display_name ); ?>" class="<?php echo esc_attr( $active ); ?>">
							<input class="formControl" name="<?php echo esc_attr( $value_name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"/>
						</label>
					</td>
				</tr>
				<?php
				self::des_row( 'age_range' );
			}
			public function start_place( $tour_id ) {
				$display_name = 'ttbm_display_start_location';
				$display      = TTBM_Function::get_post_info( $tour_id, $display_name, 'on' );
				$value_name   = 'ttbm_travel_start_place';
				$value        = TTBM_Function::get_post_info( $tour_id, $value_name );
				$placeholder  = esc_html__( 'Type Start Place...', 'tour-booking-manager' );
				$checked      = $display == 'off' ? '' : 'checked';
				$active       = $display == 'off' ? '' : 'mActive';
				?>
				<tr>
					<th colspan="3"><?php esc_html_e( 'Start Place', 'tour-booking-manager' ); ?></th>
					<td><?php TTBM_Layout::switch_button( $display_name, $checked ); ?></td>
					<td colspan="3">
						<label data-collapse="#<?php echo esc_attr( $display_name ); ?>" class="<?php echo esc_attr( $active ); ?>">
							<input class="formControl" name="<?php echo esc_attr( $value_name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"/>
						</label>
					</td>
				</tr>
				<?php
				self::des_row( 'start_place' );
			}
			public function full_location( $tour_id ) {
				$display_name = 'ttbm_display_map';
				$display      = TTBM_Function::get_post_info( $tour_id, $display_name, 'on' );
				$value_name   = 'ttbm_full_location_name';
				$value        = TTBM_Function::get_post_info( $tour_id, $value_name );
				$placeholder  = esc_html__( 'Please type Full address location...', 'tour-booking-manager' );
				$checked      = $display == 'off' ? '' : 'checked';
				$active       = $display == 'off' ? '' : 'mActive';
				?>
				<tr>
					<th colspan="3"><?php esc_html_e( 'Full Location for Map ', 'tour-booking-manager' ); ?></th>
					<td><?php TTBM_Layout::switch_button( $display_name, $checked ); ?></td>
					<td colspan="3">
						<label data-collapse="#<?php echo esc_attr( $display_name ); ?>" class="<?php echo esc_attr( $active ); ?>">
							<textarea class="formControl" name="<?php echo esc_attr( $value_name ); ?>" rows="4" placeholder="<?php echo esc_attr( $placeholder ); ?>"><?php echo esc_attr( $value ); ?></textarea>
						</label>
					</td>
				</tr>
				<?php
				self::des_row( 'full_location' );
			}
			public function short_des( $tour_id ) {
				$display_name = 'ttbm_display_description';
				$display      = TTBM_Function::get_post_info( $tour_id, $display_name, 'on' );
				$value_name   = 'ttbm_short_description';
				$value        = TTBM_Function::get_post_info( $tour_id, $value_name );
				$placeholder  = esc_html__( 'Please Type Short Description...', 'tour-booking-manager' );
				$checked      = $display == 'off' ? '' : 'checked';
				$active       = $display == 'off' ? '' : 'mActive';
				?>
				<tr>
					<th colspan="3"><?php esc_html_e( 'Short Description', 'tour-booking-manager' ); ?></th>
					<td><?php TTBM_Layout::switch_button( $display_name, $checked ); ?></td>
					<td colspan="3">
						<label data-collapse="#<?php echo esc_attr( $display_name ); ?>" class="<?php echo esc_attr( $active ); ?>">
							<textarea class="formControl" name="<?php echo esc_attr( $value_name ); ?>" rows="4" placeholder="<?php echo esc_attr( $placeholder ); ?>"><?php echo esc_attr( $value ); ?></textarea>
						</label>
					</td>
				</tr>
				<?php
				self::des_row( 'short_des' );
			}
			//************************//
			public function location( $tour_id ) {
				$display_name = 'ttbm_display_location';
				$display      = TTBM_Function::get_post_info( $tour_id, $display_name, 'on' );
				$checked      = $display == 'off' ? '' : 'checked';
				?>
				<tr>
					<th colspan="3">
						<?php esc_html_e( 'Tour Location', 'tour-booking-manager' ); ?>
						<?php TTBM_Layout::popup_button_xs( 'add_new_location_popup', esc_html__( 'Create New Location', 'tour-booking-manager' ) ); ?>
					</th>
					<td><?php TTBM_Layout::switch_button( $display_name, $checked ); ?></td>
					<td colspan="3" class="ttbm_location_select_area"><?php self::location_select( $tour_id ); ?></td>
				</tr>
				<?php
				self::des_row( 'location' );
				self::add_new_location_popup();
			}
			public static function location_select( $tour_id ) {
				if ( get_post_type( $tour_id ) == TTBM_Function::get_cpt_name() ) {
					$location_key = 'ttbm_location_name';
				} else {
					$location_key = 'ttbm_hotel_location';
				}
				$value        = TTBM_Function::get_post_info( $tour_id, $location_key, array() );
				$all_location = TTBM_Function::get_all_location();
				?>
				<label>
					<select class="formControl ttbm_select2" name="<?php echo esc_attr( $location_key ); ?>">
						<?php foreach ( $all_location as $key => $location ) { ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $key == $value ? 'selected' : '' ); ?>><?php echo esc_html( $location ); ?></option>
						<?php } ?>
					</select>
				</label>
				<?php
			}
			public static function add_new_location_popup() {
				?>
				<div class="mpPopup" data-popup="add_new_location_popup">
					<div class="popupMainArea">
						<div class="popupHeader">
							<h4>
								<?php esc_html_e( 'Add New Location', 'tour-booking-manager' ); ?>
								<p class="_textSuccess_ml_dNone ttbm_success_info">
									<span class="fas fa-check-circle mR_xs"></span>
									<?php esc_html_e( 'Location is added successfully.', 'tour-booking-manager' ) ?>
								</p>
							</h4>
							<span class="fas fa-times popupClose"></span>
						</div>
						<div class="popupBody ttbm_location_form_area">
						</div>
						<div class="popupFooter">
							<div class="buttonGroup">
								<button class="_themeButton ttbm_new_location_save" type="button"><?php esc_html_e( 'Save', 'tour-booking-manager' ); ?></button>
								<button class="_warningButton ttbm_new_location_save_close" type="button"><?php esc_html_e( 'Save & Close', 'tour-booking-manager' ); ?></button>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			public function load_ttbm_location_form() {
				$all_countries = ttbm_get_coutnry_arr();
				?>
				<label class="flexEqual">
					<span><?php esc_html_e( 'Location Name : ', 'tour-booking-manager' ); ?><sup class="textRequired">*</sup></span>
					<input type="text" name="ttbm_new_location_name" class="formControl" required>
				</label>
				<p class="textRequired" data-required="ttbm_new_location_name">
					<span class="fas fa-info-circle"></span>
					<?php esc_html_e( 'Location name is required!', 'tour-booking-manager' ); ?>
				</p>
				<?php self::des_p( 'ttbm_new_location_name' ); ?>
				<div class="divider"></div>
				<label class="flexEqual">
					<span><?php esc_html_e( 'Location Description : ', 'tour-booking-manager' ); ?></span>
					<textarea name="ttbm_location_description" class="formControl" rows="3"></textarea>
				</label>
				<?php self::des_p( 'ttbm_location_description' ); ?>
				<div class="divider"></div>
				<label class="flexEqual">
					<span><?php esc_html_e( 'Location Address : ', 'tour-booking-manager' ); ?></span>
					<textarea name="ttbm_location_address" class="formControl" rows="3"></textarea>
				</label>
				<?php self::des_p( 'ttbm_location_address' ); ?>
				<div class="divider"></div>
				<label class="flexEqual">
					<span><?php esc_html_e( 'Location Country : ', 'tour-booking-manager' ); ?></span>
					<select class="formControl" name="ttbm_location_country>">
						<?php foreach ( $all_countries as $key => $country ) { ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $country ); ?></option>
						<?php } ?>
					</select>
				</label>
				<?php self::des_p( 'ttbm_location_country' ); ?>
				<div class="divider"></div>
				<div class="flexEqual">
					<span><?php esc_html_e( 'Location Image : ', 'tour-booking-manager' ); ?><sup class="textRequired">*</sup></span>
					<?php TTBM_Layout::single_image_button( 'ttbm_location_image' ); ?>
				</div>
				<p class="textRequired" data-required="ttbm_location_image">
					<span class="fas fa-info-circle"></span>
					<?php esc_html_e( 'Location image is required!', 'tour-booking-manager' ); ?>
				</p>
				<?php self::des_p( 'ttbm_location_image' ); ?>
				<?php
				die();
			}
			public function ttbm_reload_location_list() {
				$ttbm_id = TTBM_Function::data_sanitize( $_POST['ttbm_id'] );
				self::location_select( $ttbm_id );
				die();
			}
			//************************//
			public function tour_settings_meta_box() {
				$tour_label                 = TTBM_Function::get_name();
				$ttbm_date_info_boxs        = [
					'page_nav' => esc_html__( 'Date Configuration', 'tour-booking-manager' ),
					'priority' => 10,
					'sections' => [
						'section_2' => [
							'title'       => esc_html__( '', 'tour-booking-manager' ),
							'description' => esc_html__( '', 'tour-booking-manager' ),
							'options'     => apply_filters( 'ttbm_date_info_boxs_meta_box', [
								[
									'id'      => 'ttbm_travel_type',
									'title'   => $tour_label . esc_html__( ' Type', 'tour-booking-manager' ),
									'details' => esc_html__( 'Please Select the Type', 'tour-booking-manager' ),
									'type'    => 'select',
									'class'   => 'omg',
									'default' => 'fixed',
									'args'    => TTBM_Function::travel_type_array(),
								],
								[
									'id'          => 'ttbm_travel_start_date',
									'title'       => esc_html__( ' Check In Date', 'tour-booking-manager' ),
									'details'     => esc_html__( 'Please Select the Start Date', 'tour-booking-manager' ),
									'date_format' => 'yy-mm-dd',
									'placeholder' => 'yy-mm-dd',
									'default'     => '', // today date
									'type'        => 'datepicker',
								],
								[
									'id'          => 'ttbm_travel_start_date_time',
									'title'       => esc_html__( ' Check in Time', 'tour-booking-manager' ),
									'details'     => esc_html__( 'Please Select the Start Time', 'tour-booking-manager' ),
									'date_format' => 'yy-mm-dd',
									'placeholder' => 'yy-mm-dd',
									'default'     => '', // today date
									'type'        => 'time',
								],
								array(
									'id'          => 'ttbm_travel_end_date',
									'title'       => esc_html__( ' Check out Date', 'tour-booking-manager' ),
									'details'     => esc_html__( 'Please Enter the End Date', 'tour-booking-manager' ),
									'type'        => 'datepicker',
									'date_format' => 'yy-mm-dd',
									'placeholder' => 'yy-mm-dd',
									'default'     => '', // today date
								),
								[
									'id'          => 'ttbm_travel_reg_end_date',
									'title'       => esc_html__( ' Registration End Date', 'tour-booking-manager' ),
									'details'     => esc_html__( 'Please Select the Registration End Date', 'tour-booking-manager' ),
									'date_format' => 'yy-mm-dd',
									'placeholder' => 'yy-mm-dd',
									'default'     => '', // today date
									'type'        => 'datepicker',
								],
								array(
									'id'          => 'ttbm_particular_dates',
									'title'       => esc_html__( 'Dates', 'tour-booking-manager' ) . TTBM_Layout::pro_text(),
									'details'     => esc_html__( 'Please Enter All Dates', 'tour-booking-manager' ),
									'collapsible' => true,
									'type'        => 'repeatable',
									'title_field' => 'ttbm_particular_start_date',
									'btn_text'    => esc_html__( 'Add New Particular Date & Time', 'tour-booking-manager' ),
									'fields'      => array(
										array(
											'type'    => 'date',
											'args'    => '',
											'default' => 'option_1',
											'item_id' => 'ttbm_particular_start_date',
											'name'    => esc_html__( 'Check in Date', 'tour-booking-manager' ),
										),
										array(
											'type'    => 'time',
											'args'    => '',
											'default' => 'option_1',
											'item_id' => 'ttbm_particular_start_time',
											'name'    => __( 'Check in Time', 'tour-booking-manager' ),
										),
										array(
											'type'    => 'date',
											'args'    => '',
											'default' => 'option_1',
											'item_id' => 'ttbm_particular_end_date',
											'name'    => __( 'Check out Date', 'tour-booking-manager' ),
										)
									),
								),
								[
									'id'          => 'ttbm_travel_repeated_start_date',
									'title'       => __( ' First Tour Date of Recurring Tour', 'tour-booking-manager' ),
									'details'     => __( 'Please Select the First Tour Date of Recurring Tour span', 'tour-booking-manager' ),
									'date_format' => 'yy-mm-dd',
									'placeholder' => 'yy-mm-dd',
									'default'     => '', // today date
									'type'        => 'datepicker',
								],
								[
									'id'          => 'ttbm_travel_repeated_end_date',
									'title'       => __( ' Last Tour Date of Recurring Tour', 'tour-booking-manager' ),
									'details'     => __( 'Please Select the Last Tour Date of Recurring Tour span', 'tour-booking-manager' ),
									'date_format' => 'yy-mm-dd',
									'placeholder' => 'yy-mm-dd',
									'default'     => '', // today date
									'type'        => 'datepicker',
								],
								array(
									'id'          => 'ttbm_travel_repeated_after',
									'title'       => __( ' Repeated After', 'tour-booking-manager' ),
									'details'     => __( 'Please Enter the Duration of Repeat', 'tour-booking-manager' ),
									'type'        => 'text',
									'default'     => '',
									'placeholder' => __( '3', 'tour-booking-manager' ),
								),
								array(
									'id'      => 'mep_disable_ticket_time',
									'title'   => __( 'Display Time?', 'tour-booking-manager' ) . TTBM_Layout::pro_text(),
									'details' => __( 'If you want to display time please check this Yes', 'tour-booking-manager' ),
									'type'    => 'checkbox',
									'default' => '',
									'args'    => array(
										'yes' => __( 'Yes', 'tour-booking-manager' )
									),
								),
								array(
									'id'          => 'mep_ticket_times_global',
									'title'       => __( 'Default Times', 'tour-booking-manager' ) . TTBM_Layout::pro_text(),
									'details'     => __( 'Please Enter Add Ticket Times', 'tour-booking-manager' ),
									'collapsible' => true,
									'type'        => 'repeatable',
									'title_field' => 'mep_ticket_time_name',
									'btn_text'    => 'Add New Time Default/Global Time',
									'fields'      => array(
										array(
											'type'    => 'text',
											'args'    => '',
											'default' => '',
											'item_id' => 'mep_ticket_time_name',
											'name'    => 'Time Slot Label',
										),
										array(
											'type'    => 'time',
											'args'    => '',
											'default' => 'option_1',
											'item_id' => 'mep_ticket_time',
											'name'    => 'Time',
										)
									),
								),
								array(
									'id'          => 'mep_ticket_times_sat',
									'title'       => __( 'Saturday Ticket Time', 'tour-booking-manager' ) . TTBM_Layout::pro_text(),
									'details'     => __( 'Please Enter Add Ticket Times', 'tour-booking-manager' ),
									'collapsible' => true,
									'type'        => 'repeatable',
									'title_field' => 'mep_ticket_time_name',
									'btn_text'    => 'Add New Time Fro Saturday',
									'fields'      => array(
										array(
											'type'    => 'text',
											'args'    => '',
											'default' => '',
											'item_id' => 'mep_ticket_time_name',
											'name'    => 'Time Slot Label',
										),
										array(
											'type'    => 'time',
											'args'    => '',
											'default' => 'option_1',
											'item_id' => 'mep_ticket_time',
											'name'    => 'Time',
										),
									),
								),
								array(
									'id'          => 'mep_ticket_times_sun',
									'title'       => __( 'Sunday Ticket Time', 'tour-booking-manager' ) . TTBM_Layout::pro_text(),
									'details'     => __( 'Please Enter Add Ticket Times', 'tour-booking-manager' ),
									'collapsible' => true,
									'type'        => 'repeatable',
									'title_field' => 'mep_ticket_time_name',
									'btn_text'    => 'Add New Time Fro Sunday',
									'fields'      => array(
										array(
											'type'    => 'text',
											'args'    => '',
											'default' => '',
											'item_id' => 'mep_ticket_time_name',
											'name'    => 'Time Slot Label',
										),
										array(
											'type'    => 'time',
											'args'    => '',
											'default' => 'option_1',
											'item_id' => 'mep_ticket_time',
											'name'    => 'Time',
										)
									),
								),
								array(
									'id'          => 'mep_ticket_times_mon',
									'title'       => __( 'Monday Ticket Time', 'tour-booking-manager' ) . TTBM_Layout::pro_text(),
									'details'     => __( 'Please Enter Add Ticket Times', 'tour-booking-manager' ),
									'collapsible' => true,
									'type'        => 'repeatable',
									'title_field' => 'mep_ticket_time_name',
									'btn_text'    => 'Add New Time For Monday',
									'fields'      => array(
										array(
											'type'    => 'text',
											'args'    => '',
											'default' => '',
											'item_id' => 'mep_ticket_time_name',
											'name'    => 'Time Slot Label',
										),
										array(
											'type'    => 'time',
											'args'    => '',
											'default' => 'option_1',
											'item_id' => 'mep_ticket_time',
											'name'    => 'Time',
										)
									),
								),
								array(
									'id'          => 'mep_ticket_times_tue',
									'title'       => __( 'Tuesday Ticket Time', 'tour-booking-manager' ) . TTBM_Layout::pro_text(),
									'details'     => __( 'Please Enter Add Ticket Times', 'tour-booking-manager' ),
									'collapsible' => true,
									'type'        => 'repeatable',
									'title_field' => 'mep_ticket_time_name',
									'btn_text'    => 'Add New Time For Tuesday',
									'fields'      => array(
										array(
											'type'    => 'text',
											'args'    => '',
											'default' => '',
											'item_id' => 'mep_ticket_time_name',
											'name'    => 'Time Slot Label',
										),
										array(
											'type'    => 'time',
											'default' => 'option_1',
											'args'    => '',
											'item_id' => 'mep_ticket_time',
											'name'    => 'Time',
										)
									),
								),
								array(
									'id'          => 'mep_ticket_times_wed',
									'title'       => __( 'Wednesday Ticket Time', 'tour-booking-manager' ) . TTBM_Layout::pro_text(),
									'details'     => __( 'Please Enter Add Ticket Times', 'tour-booking-manager' ),
									'collapsible' => true,
									'type'        => 'repeatable',
									'title_field' => 'mep_ticket_time_name',
									'btn_text'    => 'Add New Time For Wednesday',
									'fields'      => array(
										array(
											'type'    => 'text',
											'args'    => '',
											'default' => '',
											'item_id' => 'mep_ticket_time_name',
											'name'    => 'Time Slot Label',
										),
										array(
											'type'    => 'time',
											'default' => 'option_1',
											'args'    => '',
											'item_id' => 'mep_ticket_time',
											'name'    => 'Time',
										)
									),
								),
								array(
									'id'          => 'mep_ticket_times_thu',
									'title'       => __( 'Thursday Ticket Time', 'tour-booking-manager' ) . TTBM_Layout::pro_text(),
									'details'     => __( 'Please Enter Add Ticket Times', 'tour-booking-manager' ),
									'collapsible' => true,
									'type'        => 'repeatable',
									'title_field' => 'mep_ticket_time_name',
									'btn_text'    => 'Add New Time For Thursday',
									'fields'      => array(
										array(
											'type'    => 'text',
											'args'    => '',
											'default' => '',
											'item_id' => 'mep_ticket_time_name',
											'name'    => 'Time Slot Label',
										),
										array(
											'type'    => 'time',
											'default' => 'option_1',
											'args'    => '',
											'item_id' => 'mep_ticket_time',
											'name'    => 'Time',
										)
									),
								),
								array(
									'id'          => 'mep_ticket_times_fri',
									'title'       => __( 'Friday Ticket Time', 'tour-booking-manager' ) . TTBM_Layout::pro_text(),
									'details'     => __( 'Please Enter Add Ticket Times', 'tour-booking-manager' ),
									'collapsible' => true,
									'type'        => 'repeatable',
									'title_field' => 'mep_ticket_time_name',
									'btn_text'    => 'Add New Time for Friday',
									'fields'      => array(
										array(
											'type'    => 'text',
											'args'    => '',
											'default' => '',
											'item_id' => 'mep_ticket_time_name',
											'name'    => 'Time Slot Label',
										),
										array(
											'type'    => 'time',
											'default' => '',
											'args'    => '',
											'item_id' => 'mep_ticket_time',
											'name'    => 'Time',
										)
									),
								),
								array(
									'id'       => 'mep_ticket_offdays',
									'title'    => __( 'Ticket Offdays', 'tour-booking-manager' ) . TTBM_Layout::pro_text(),
									'details'  => __( 'Please select the offday days. Ticket will be not available on the selected days', 'tour-booking-manager' ),
									'type'     => 'select2',
									'class'    => 'ttbm_select2',
									'default'  => '',
									'multiple' => true,
									'args'     => array(
										'sun' => __( 'Sunday', 'tour-booking-manager' ),
										'mon' => __( 'Monday', 'tour-booking-manager' ),
										'tue' => __( 'Tuesday', 'tour-booking-manager' ),
										'wed' => __( 'Wednesday', 'tour-booking-manager' ),
										'thu' => __( 'Thursday', 'tour-booking-manager' ),
										'fri' => __( 'Friday', 'tour-booking-manager' ),
										'sat' => __( 'Saturday', 'tour-booking-manager' ),
									),
								),
								array(
									'id'          => 'mep_ticket_off_dates',
									'title'       => __( 'Ticket Off Dates List', 'tour-booking-manager' ) . TTBM_Layout::pro_text(),
									'details'     => __( 'If you want to off selling ticket on particular dates please select them', 'tour-booking-manager' ),
									'collapsible' => true,
									'type'        => 'repeatable',
									'title_field' => 'mep_ticket_off_date',
									'btn_text'    => 'Add New Off Date',
									'fields'      => array(
										array(
											'type'    => 'date',
											'default' => 'option_1',
											'args'    => '',
											'item_id' => 'mep_ticket_off_date',
											'name'    => 'OffDate',
										)
									),
								)
							] )
						],
					],
				];
				$ttbm_date_config_boxs_args = [
					'meta_box_id'    => 'ttbm_travel_date_config_meta_boxes',
					'meta_box_title' => '<span class="dashicons dashicons-calendar-alt"></span>' . __( 'Date Configuration', 'tour-booking-manager' ),
					'screen'         => [ TTBM_Function::get_cpt_name() ],
					'context'        => 'normal',
					'priority'       => 'high',
					'callback_args'  => [],
					'nav_position'   => 'none',
					'item_name'      => "MagePeople",
					'item_version'   => "2.0",
					'panels'         => [
						'ttbm_date_config_meta_boxs' => $ttbm_date_info_boxs
					]
				];
				new TtbmAddMetaBox( $ttbm_date_config_boxs_args );
				$ttbm_tax_meta_boxs      = [
					'page_nav' => $tour_label . __( ' Tax', 'tour-booking-manager' ),
					'priority' => 10,
					'sections' => [
						'section_2' => [
							'title'       => __( '', 'tour-booking-manager' ),
							'description' => __( '', 'tour-booking-manager' ),
							'options'     => [
								[
									'id'      => '_tax_status',
									'title'   => $tour_label . __( ' Tax Status', 'tour-booking-manager' ),
									'details' => __( 'Please Select Tax Status', 'tour-booking-manager' ),
									'type'    => 'select',
									'class'   => 'omg',
									'default' => 'taxable',
									'args'    => [
										'taxable'  => __( 'Taxable', 'tour-booking-manager' ),
										'shipping' => __( 'Shipping only', 'tour-booking-manager' ),
										'none'     => __( 'None', 'tour-booking-manager' )
									]
								],
								[
									'id'      => '_tax_class',
									'title'   => $tour_label . __( ' Tax Class', 'tour-booking-manager' ),
									'details' => __( 'Please Select Tax Class', 'tour-booking-manager' ),
									'type'    => 'select',
									'class'   => 'omg',
									'default' => 'none',
									'args'    => TTBM_Function::all_tax_list()
								],
							]
						],
					],
				];
				$ttbm_tax_meta_boxs_args = [
					'meta_box_id'    => 'ttbm_tax_meta_boxes',
					'meta_box_title' => '<span class="dashicons dashicons-text-page"></span> ' . __( ' Tax', 'tour-booking-manager' ),
					'screen'         => [ TTBM_Function::get_cpt_name() ],
					'context'        => 'normal',
					'priority'       => 'low',
					'callback_args'  => [],
					'nav_position'   => 'none', // right, top, left, none
					'item_name'      => "MagePeople",
					'item_version'   => "2.0",
					'panels'         => [
						'ttbm_tax_meta_boxs' => $ttbm_tax_meta_boxs
					],
				];
				if ( get_option( 'woocommerce_calc_taxes' ) == 'yes' ) {
					new TtbmAddMetaBox( $ttbm_tax_meta_boxs_args );
				}
				$ttbm_list_thumbnail       = [
					'page_nav' => __( 'List Thumbnail', 'tour-booking-manager' ),
					'priority' => 10,
					'sections' => [
						'section_2' => [
							'title'       => __( '', 'tour-booking-manager' ),
							'description' => __( '', 'tour-booking-manager' ),
							'options'     => [
								[
									'id'          => 'ttbm_list_thumbnail',
									'title'       => __( 'Thumbmnail ', 'tour-booking-manager' ),
									'details'     => __( 'Please upload image for list', 'tour-booking-manager' ),
									'placeholder' => 'https://via.placeholder.com/1000x500',
									'type'        => 'media',
								]
							]
						],
					],
				];
				$ttbm_list_thumb_meta_args = [
					'meta_box_id'    => 'ttbm_list_thumbnail_meta_boxes',
					'meta_box_title' => __( 'List Thumbnail', 'tour-booking-manager' ),
					'screen'         => [ TTBM_Function::get_cpt_name() ],
					'context'        => 'side', // 'normal', 'side', and 'advanced'
					'priority'       => 'low', // 'high', 'low'
					'callback_args'  => [],
					'nav_position'   => 'none', // right, top, left, none
					'item_name'      => "MagePeople",
					'item_version'   => "2.0",
					'panels'         => [
						'ttbm_list_thumb_meta_box' => $ttbm_list_thumbnail
					],
				];
				new TtbmAddMetaBox( $ttbm_list_thumb_meta_args );
				$ttbm_list_template           = [
					'page_nav' => __( 'Template', 'tour-booking-manager' ),
					'priority' => 10,
					'sections' => [
						'section_2' => [
							'title'       => __( '', 'tour-booking-manager' ),
							'description' => __( '', 'tour-booking-manager' ),
							'options'     => [
								[
									'id'      => 'ttbm_theme_file',
									'title'   => __( ' Template', 'tour-booking-manager' ),
									'details' => __( 'Please Select a Template', 'tour-booking-manager' ),
									'type'    => 'select',
									'class'   => 'omg',
									'default' => 'fixed',
									'args'    => TTBM_Function::all_details_template()
								],
							]
						],
					],
				];
				$ttbm_list_template_meta_args = [
					'meta_box_id'    => 'ttbm_list_thumbnail_meta_boxes',
					'meta_box_title' => __( 'Template', 'tour-booking-manager' ),
					'screen'         => [ TTBM_Function::get_cpt_name() ],
					'context'        => 'side', // 'normal', 'side', and 'advanced'
					'priority'       => 'low', // 'high', 'low'
					'callback_args'  => [],
					'nav_position'   => 'none', // right, top, left, none
					'item_name'      => "MagePeople",
					'item_version'   => "2.0",
					'panels'         => [
						'ttbm_list_template_meta_box' => $ttbm_list_template
					],
				];
				new TtbmAddMetaBox( $ttbm_list_template_meta_args );
			}
			//********* Gallery settings*************//
			public function gallery_settings( $tour_id ) {
				$display   = TTBM_Function::get_post_info( $tour_id, 'ttbm_display_slider', 'on' );
				$active    = $display == 'off' ? '' : 'mActive';
				$checked   = $display == 'off' ? '' : 'checked';
				$image_ids = TTBM_Function::get_post_info( $tour_id, 'ttbm_gallery_images', array() );
				?>
				<div class="tabsItem ttbm_settings_gallery" data-tabs="#ttbm_settings_gallery">
					<h5 class="dFlex">
						<span class="mR"><?php esc_html_e( 'On/Off Slider', 'tour-booking-manager' ); ?></span>
						<?php TTBM_Layout::switch_button( 'ttbm_display_slider', $checked ); ?>
					</h5>
					<?php self::des_p( 'ttbm_display_slider' ); ?>
					<div class="divider"></div>
					<div data-collapse="#ttbm_display_slider" class="<?php echo esc_attr( $active ); ?>">
						<table class="layoutFixed">
							<tbody>
							<tr>
								<th><?php esc_html_e( 'Gallery Images ', 'tour-booking-manager' ); ?></th>
								<td colspan="3">
									<?php TTBM_Layout::add_multi_image( 'ttbm_gallery_images', $image_ids ); ?>
								</td>
							</tr>
							<tr>
								<td colspan="4"><?php self::des_p( 'ttbm_gallery_images' ); ?></td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>
				<?php
			}
			//********* extras settings*************//
			public function extras_settings( $tour_id ) {
				$contact_text  = TTBM_Function::get_contact_text( $tour_id );
				$contact_phone = TTBM_Function::get_contact_phone( $tour_id );
				$contact_email = TTBM_Function::get_contact_email( $tour_id );
				$display_gaq   = TTBM_Function::get_post_info( $tour_id, 'ttbm_display_get_question', 'on' );
				$active_gaq    = $display_gaq == 'off' ? '' : 'mActive';
				$checked_gaq   = $display_gaq == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem ttbm_settings_extras" data-tabs="#ttbm_settings_extras">
					<h5 class="dFlex">
						<span class="mR"><?php esc_html_e( 'On/Off Get a Questions', 'tour-booking-manager' ); ?></span>
						<?php TTBM_Layout::switch_button( 'ttbm_display_get_question', $checked_gaq ); ?>
					</h5>
					<?php self::des_p( 'ttbm_display_get_question' ); ?>
					<div class="divider"></div>
					<div data-collapse="#ttbm_display_get_question" class="<?php echo esc_attr( $active_gaq ); ?>">
						<table class="layoutFixed">
							<tbody>
							<tr>
								<th><?php esc_html_e( 'Contact E-Mail', 'tour-booking-manager' ); ?></th>
								<td colspan="3">
									<label>
										<input class="formControl" name="ttbm_contact_email" value="<?php echo esc_attr( $contact_email ); ?>" placeholder="<?php esc_html_e( 'Please enter Contact Email', 'tour-booking-manager' ); ?>"/>
									</label>
								</td>
							</tr>
							<tr>
								<td colspan="4"><?php self::des_p( 'ttbm_contact_email' ); ?></td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Contact Phone', 'tour-booking-manager' ); ?></th>
								<td colspan="3">
									<label>
										<input class="formControl" name="ttbm_contact_phone" value="<?php echo esc_attr( $contact_phone ); ?>" placeholder="<?php esc_html_e( 'Please enter Contact Phone', 'tour-booking-manager' ); ?>"/>
									</label>
								</td>
							</tr>
							<tr>
								<td colspan="4"><?php self::des_p( 'ttbm_contact_phone' ); ?></td>
							</tr>
							<tr>
								<th><?php esc_html_e( 'Short Description', 'tour-booking-manager' ); ?></th>
								<td colspan="3">
									<label>
										<textarea class="formControl" name="ttbm_contact_text" rows="4" placeholder="<?php esc_html_e( 'Please Enter Contact Section Text', 'tour-booking-manager' ); ?>"><?php echo esc_attr( $contact_text ); ?></textarea>
									</label>
								</td>
							</tr>
							<tr>
								<td colspan="4"><?php self::des_p( 'ttbm_contact_text' ); ?></td>
							</tr>
							</tbody>
						</table>
					</div>
					<?php
						$active_tour_guide = TTBM_Function::get_general_settings( 'ttbm_active_guide', 'no' );
						if ( $active_tour_guide == 'yes' ) {
							$all_guides    = TTBM_Query::query_post_type( 'ttbm_guide' );
							$display_guide = TTBM_Function::get_post_info( $tour_id, 'ttbm_display_get_question', 'off' );
							$active_guide  = $display_guide == 'off' ? '' : 'mActive';
							$checked_guide = $display_guide == 'off' ? '' : 'checked';
							$guides        = TTBM_Function::get_post_info( $tour_id, 'ttbm_tour_guide', array() );
							?>
							<div class="divider"></div>
							<h5 class="dFlex">
								<span class="mR"><?php esc_html_e( 'On/Off Tour Guide', 'tour-booking-manager' ); ?></span>
								<?php TTBM_Layout::switch_button( 'ttbm_display_tour_guide', $checked_guide ); ?>
							</h5>
							<?php self::des_p( 'ttbm_display_tour_guide' ); ?>
							<div class="divider"></div>
							<div data-collapse="#ttbm_display_tour_guide" class="<?php echo esc_attr( $active_guide ); ?>">
								<table class="layoutFixed">
									<tbody>
									<tr>
										<th><?php esc_html_e( 'Select tour guide', 'tour-booking-manager' ); ?></th>
										<td colspan="3">
											<?php //echo '<pre>';print_r($all_guides);echo '</pre>';  ?>
											<label>
												<select name="ttbm_tour_guide[]" multiple='multiple' class='formControl ttbm_select2' data-placeholder="<?php echo esc_html__( 'Please Select Guide', 'tour-booking-manager' ); ?>">
													<?php
														if ( $all_guides->post_count > 0 ) {
															foreach ( $all_guides->posts as $guide ) {
																$ttbm_id = $guide->ID;
																?>
																<option value="<?php echo esc_attr( $ttbm_id ) ?>" <?php echo in_array( $ttbm_id, $guides ) ? 'selected' : ''; ?>><?php echo get_the_title( $ttbm_id ); ?></option>
																<?php
															}
														}
													?>
												</select>
											</label>
										</td>
									</tr>
									<tr>
										<td colspan="4"><?php self::des_p( 'ttbm_tour_guide' ); ?></td>
									</tr>
									</tbody>
								</table>
							</div>
						<?php } ?>
				</div>
				<?php
			}
			//********* related Tour settings*************//
			public function related_tour_settings( $tour_id ) {
				$ttbm_label    = TTBM_Function::get_name();
				$display       = TTBM_Function::get_post_info( $tour_id, 'ttbm_display_related', 'on' );
				$active        = $display == 'off' ? '' : 'mActive';
				$related_tours = TTBM_Function::get_related_tour( $tour_id );
				$all_tours     = TTBM_Query::query_post_type( 'ttbm_tour' );
				$tours         = $all_tours->posts;
				$checked       = $display == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem" data-tabs="#ttbm_settings_related_tour">
					<h5 class="dFlex">
						<span class="mR"><?php echo esc_html__( 'Related ', 'tour-booking-manager' ) . $ttbm_label . esc_html__( ' Settings', 'tour-booking-manager' ) ?></span>
						<?php TTBM_Layout::switch_button( 'ttbm_display_related', $checked ); ?>
					</h5>
					<?php self::des_p( 'ttbm_display_related' ); ?>
					<div class="divider"></div>
					<div data-collapse="#ttbm_display_related" class="<?php echo esc_attr( $active ); ?>">
						<table class="layoutFixed">
							<tbody>
							<tr>
								<th>
									<?php esc_html_e( 'Related ' . $ttbm_label . ' : ', 'tour-booking-manager' ); ?>
									<?php self::des_p( 'ttbm_related_tour' ); ?>
								</th>
								<td colspan="3">
									<label>
										<select name="ttbm_related_tour[]" multiple='multiple' class='formControl ttbm_select2' data-placeholder="<?php echo esc_html__( 'Please Select ', 'tour-booking-manager' ) . $ttbm_label; ?>">
											<?php
												foreach ( $tours as $tour ) {
													$ttbm_id = $tour->ID;
													?>
													<option value="<?php echo esc_attr( $ttbm_id ) ?>" <?php echo in_array( $ttbm_id, $related_tours ) ? 'selected' : ''; ?>><?php echo get_the_title( $ttbm_id ); ?></option>
												<?php } ?>
										</select>
									</label>
								</td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>
				<?php
				wp_reset_postdata();
			}
			//********* Display settings*************//
			public function details_page_settings( $tour_id ) {
				$seat_details_checked = TTBM_Function::get_post_info( $tour_id, 'ttbm_display_seat_details', 'on' ) == 'off' ? '' : 'checked';
				$tour_type_checked    = TTBM_Function::get_post_info( $tour_id, 'ttbm_display_tour_type', 'on' ) == 'off' ? '' : 'checked';
				$hotel_checked        = TTBM_Function::get_post_info( $tour_id, 'ttbm_display_hotels', 'on' ) == 'off' ? '' : 'checked';
				$sidebar_checked      = TTBM_Function::get_post_info( $tour_id, 'ttbm_display_sidebar', 'off' ) == 'off' ? '' : 'checked';
				$duration_checked     = TTBM_Function::get_post_info( $tour_id, 'ttbm_display_duration', 'on' ) == 'off' ? '' : 'checked';
				?>
				<div class="ttbm_settings_panel tabsItem" data-tabs="#ttbm_display_settings">
					<h5><?php esc_html_e( 'Details page settings', 'tour-booking-manager' ); ?></h5>
					<table>
						<tbody>
						<?php $content_title_style = TTBM_Function::get_post_info( $tour_id, 'ttbm_section_title_style' ) ?: 'ttbm_title_style_2'; ?>
						<tr>
							<th><?php esc_html_e( 'Section Title Style?', 'tour-booking-manager' ); ?></th>
							<td>
								<label>
									<select class="formControl" name="ttbm_section_title_style">
										<option value="style_1" <?php echo esc_attr( $content_title_style == 'style_1' ? 'selected' : '' ); ?>><?php esc_html_e( 'Style One', 'tour-booking-manager' ); ?></option>
										<option value="ttbm_title_style_2" <?php echo esc_attr( $content_title_style == 'ttbm_title_style_2' ? 'selected' : '' ); ?>><?php esc_html_e( 'Style Two', 'tour-booking-manager' ); ?></option>
										<option value="ttbm_title_style_3" <?php echo esc_attr( $content_title_style == 'ttbm_title_style_3' ? 'selected' : '' ); ?>><?php esc_html_e( 'Style Three', 'tour-booking-manager' ); ?></option>
									</select>
								</label>
							</td>
						</tr>
						<tr>
							<td colspan="2"><?php self::des_p( 'ttbm_section_title_style' ); ?></td>
						</tr>
						<?php $ticketing_system = TTBM_Function::get_post_info( $tour_id, 'ttbm_ticketing_system', 'availability_section' ); ?>
						<tr>
							<th><?php esc_html_e( 'Ticket Purchase Settings', 'tour-booking-manager' ); ?></th>
							<td>
								<label>
									<select class="formControl" name="ttbm_ticketing_system">
										<option value="regular_ticket" <?php echo esc_attr( ! $ticketing_system ? 'selected' : '' ); ?>><?php esc_html_e( 'Ticket Open', 'tour-booking-manager' ); ?></option>
										<option value="availability_section" <?php echo esc_attr( $ticketing_system == 'availability_section' ? 'selected' : '' ); ?>><?php esc_html_e( 'Ticket Collapse System', 'tour-booking-manager' ); ?></option>
									</select>
								</label>
							</td>
						</tr>
						<tr>
							<td colspan="2"><?php self::des_p( 'ttbm_ticketing_system' ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'On/Off Seat Info', 'tour-booking-manager' ); ?></th>
							<td><?php TTBM_Layout::switch_button( 'ttbm_display_seat_details', $seat_details_checked ); ?></td>
						</tr>
						<tr>
							<td colspan="2"><?php self::des_p( 'ttbm_display_seat_details' ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'On/Off Tour Type', 'tour-booking-manager' ); ?></th>
							<td><?php TTBM_Layout::switch_button( 'ttbm_display_tour_type', $tour_type_checked ); ?></td>
						</tr>
						<tr>
							<td colspan="2"><?php self::des_p( 'ttbm_display_tour_type' ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'On/Off Hotels', 'tour-booking-manager' ); ?></th>
							<td><?php TTBM_Layout::switch_button( 'ttbm_display_hotels', $hotel_checked ); ?></td>
						</tr>
						<tr>
							<td colspan="2"><?php self::des_p( 'ttbm_display_hotels' ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'On/Off Sidebar widget', 'tour-booking-manager' ); ?></th>
							<td><?php TTBM_Layout::switch_button( 'ttbm_display_sidebar', $sidebar_checked ); ?></td>
						</tr>
						<tr>
							<td colspan="2"><?php self::des_p( 'ttbm_display_sidebar' ); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'On/Off Duration', 'tour-booking-manager' ); ?></th>
							<td><?php TTBM_Layout::switch_button( 'ttbm_display_duration', $duration_checked ); ?></td>
						</tr>
						<tr>
							<td colspan="2"><?php self::des_p( 'ttbm_display_duration' ); ?></td>
						</tr>
						<?php do_action( 'add_ttbm_display_settings', $tour_id ); ?>
						</tbody>
					</table>
				</div>
				<?php
			}
			public function partial_payment_settings( $tour_id ) {
				$values = get_post_custom( $tour_id );
				echo '<div class="tabsItem" data-tabs="#_mep_pp_deposits_type">';
				do_action( 'wcpp_partial_product_settings', $values );
				echo '</div>';
			}
			//******************************//
			public static function des_array( $key ) {
				$des = array(
					'start_price'                  => esc_html__( 'Price Starts  are displayed on the tour details and tour list pages. If you would like to hide them, you can do so by switching the option.', 'tour-booking-manager' ),
					'max_people'                   => esc_html__( 'This tour only allows a maximum of X people. This number is displayed for informational purposes only and can be hidden by switching the option.', 'tour-booking-manager' ),
					'age_range'                    => esc_html__( 'The age limit for this tour is X to Y years old. This is for information purposes only.', 'tour-booking-manager' ),
					'start_place'                  => esc_html__( 'This will be the starting point for the tour group. The tour will begin from here.', 'tour-booking-manager' ),
					'location'                     => esc_html__( 'Please select the name of the location you wish to create a tour for. If you would like to create a new location, please go to the Tour page.', 'tour-booking-manager' ),
					'full_location'                => esc_html__( 'Please Type Full Address of the location, it will use for the google map', 'tour-booking-manager' ),
					'short_des'                    => esc_html__( 'For a Tour short description, toggle this switching option.', 'tour-booking-manager' ),
					'duration'                     => esc_html__( 'Please enter the number of days and nights for your tour package.', 'tour-booking-manager' ),
					'ttbm_new_location_name'       => esc_html__( 'Please add the new location to the location list when creating a tour.', 'tour-booking-manager' ),
					'ttbm_location_description'    => esc_html__( 'The description is not always visible by default, but some themes may display it.', 'tour-booking-manager' ),
					'ttbm_location_address'        => esc_html__( 'Please Enter the Full Address of Your Location', 'tour-booking-manager' ),
					'ttbm_location_country'        => esc_html__( 'Please select your tour location country from the list below.', 'tour-booking-manager' ),
					'ttbm_location_image'          => esc_html__( 'Please select an image for your tour location.', 'tour-booking-manager' ),
					'ttbm_display_registration'    => esc_html__( "If you don't want to use the tour registration feature, you can just keep it turned off.", 'tour-booking-manager' ),
					'ttbm_short_code'              => esc_html__( 'You can display this Ticket type list with the add to cart button anywhere on your website by copying the shortcode and using it on any post or page.', 'tour-booking-manager' ),
					'ttbm_display_schedule'        => esc_html__( 'Please find the detailed timeline for you tour as day 1, day 2 etc.', 'tour-booking-manager' ),
					'add_new_feature_popup'        => esc_html__( 'To include or exclude a feature from your tour, please select it from the list below. To create a new feature, go to the Tour page.', 'tour-booking-manager' ),
					'ttbm_display_include_service' => esc_html__( 'The price of this tour includes the service, which you can keep hidden by turning it off.', 'tour-booking-manager' ),
					'ttbm_display_exclude_service' => esc_html__( 'The price of this tour excludes the service, which you can keep hidden by turning it off.', 'tour-booking-manager' ),
					'ttbm_feature_name'            => esc_html__( 'The name is how it appears on your site.', 'tour-booking-manager' ),
					'ttbm_feature_description'     => esc_html__( 'The description is not prominent by default; however, some themes may show it.', 'tour-booking-manager' ),
					'ttbm_display_hiphop'          => esc_html__( 'By default Places You\'ll See  is ON but you can keep it off by switching this option', 'tour-booking-manager' ),
					'ttbm_place_you_see'           => esc_html__( 'Please Select Place Name. To create new place, go Tour->Places; or click on the Create New Place button', 'tour-booking-manager' ),
					'ttbm_place_name'              => esc_html__( 'The name is how it appears on your site.', 'tour-booking-manager' ),
					'ttbm_place_description'       => esc_html__( 'The description is not prominent by default; however, some themes may show it.', 'tour-booking-manager' ),
					'ttbm_place_image'             => esc_html__( 'Please Select Place Image.', 'tour-booking-manager' ),
					'ttbm_display_faq'             => esc_html__( 'Frequently Asked Questions about this tour that customers need to know', 'tour-booking-manager' ),
					'ttbm_display_why_choose_us'   => esc_html__( 'Why choose us section, write a key feature list that tourist get Trust to book. you can switch it off.', 'tour-booking-manager' ),
					'why_chose_us'                 => esc_html__( 'Please add why to book feature list one by one.', 'tour-booking-manager' ),
					'ttbm_display_activities'      => esc_html__( 'By default Activities type is ON but you can keep it off by switching this option', 'tour-booking-manager' ),
					'activities'                   => esc_html__( 'Add a list of tour activities for this tour.', 'tour-booking-manager' ),
					'ttbm_activity_name'           => esc_html__( 'The name is how it appears on your site.', 'tour-booking-manager' ),
					'ttbm_activity_description'    => esc_html__( 'The description is not prominent by default; however, some themes may show it.', 'tour-booking-manager' ),
					'ttbm_display_related'         => esc_html__( 'Please select a related tour from this list.', 'tour-booking-manager' ),
					'ttbm_display_slider'          => esc_html__( 'By default slider is ON but you can keep it off by switching this option', 'tour-booking-manager' ),
					'ttbm_section_title_style'     => esc_html__( 'By default Section title is style one', 'tour-booking-manager' ),
					'ttbm_ticketing_system'        => esc_html__( 'By default, the ticket purchase system is open. Once you check the availability, you can choose the system that best suits your needs.', 'tour-booking-manager' ),
					'ttbm_display_seat_details'    => esc_html__( 'By default Seat Info is ON but you can keep it off by switching this option', 'tour-booking-manager' ),
					'ttbm_display_tour_type'       => esc_html__( 'By default Tour type is ON but you can keep it off by switching this option', 'tour-booking-manager' ),
					'ttbm_display_hotels'          => esc_html__( 'By default Display hotels is ON but you can keep it off by switching this option', 'tour-booking-manager' ),
					'ttbm_display_get_question'    => esc_html__( 'By default Display Get a Questions is ON but you can keep it off by switching this option', 'tour-booking-manager' ),
					'ttbm_display_sidebar'         => esc_html__( 'By default Sidebar Widget is Off but you can keep it ON by switching this option', 'tour-booking-manager' ),
					'ttbm_display_duration'        => esc_html__( 'By default Duration is ON but you can keep it off by switching this option', 'tour-booking-manager' ),
					'ttbm_related_tour'            => esc_html__( 'Please add related  Tour', 'tour-booking-manager' ),
					'ttbm_contact_phone'           => esc_html__( 'Please Enter contact phone no', 'tour-booking-manager' ),
					'ttbm_contact_text'            => esc_html__( 'Please Enter Contact Section Text', 'tour-booking-manager' ),
					'ttbm_contact_email'           => esc_html__( 'Please Enter contact phone email', 'tour-booking-manager' ),
					'ttbm_gallery_images'          => esc_html__( 'Please upload images for gallery', 'tour-booking-manager' ),
					'ttbm_type'                    => esc_html__( 'By default Type is General', 'tour-booking-manager' ),
					'ttbm_display_advance'         => esc_html__( 'By default Advance option is Off but you can keep it On by switching this option', 'tour-booking-manager' ),
					'ttbm_display_extra_advance'   => esc_html__( 'By default Advance option is on but you can keep it off by switching this option', 'tour-booking-manager' ),
					'ttbm_display_hotel_distance'  => esc_html__( 'Please add Distance Description', 'tour-booking-manager' ),
					'ttbm_display_hotel_rating'    => esc_html__( 'Please Select Hotel rating ', 'tour-booking-manager' ),
					'ttbm_display_tour_guide'      => esc_html__( 'You can keep off tour guide information by switching this option', 'tour-booking-manager' ),
					'ttbm_tour_guide'              => esc_html__( 'To add tour guide information, simply select an option from the list below.', 'tour-booking-manager' ),
					//''          => esc_html__( '', 'tour-booking-manager' ),
				);
				$des = apply_filters( 'ttbm_filter_description_array', $des );
				return $des[ $key ];
			}
			public static function des_row( $key ) {
				?>
				<tr>
					<td colspan="7" class="textInfo">
						<p class="ttbm_description">
							<span class="fas fa-info-circle"></span>
							<?php echo self::des_array( $key ); ?>
						</p>
					</td>
				</tr>
				<?php
			}
			public static function des_p( $key ) {
				?>
				<p class="ttbm_description">
					<span class="fas fa-info-circle"></span>
					<?php echo self::des_array( $key ); ?>
				</p>
				<?php
			}
		}
		new TTBM_Settings();
	}