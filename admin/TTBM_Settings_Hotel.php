<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'TTBM_Settings_Hotel' ) ) {
		class TTBM_Settings_Hotel {
			public function __construct() {
				add_action( 'add_meta_boxes', [ $this, 'hotel_settings_meta' ] );
			}
			public function hotel_settings_meta() {
				$ttbm_label = TTBM_Function::get_name();
				add_meta_box( 'ttbm_add_meta_box', '<span class="dashicons dashicons-info"></span>' . $ttbm_label . esc_html__( ' Hotel Settings : ', 'tour-booking-manager' ) . get_the_title( get_the_id() ), array( $this, 'hotel_settings' ), 'ttbm_hotel', 'normal', 'high' );
			}
			public function hotel_settings() {
				$hotel_id    = get_the_id();
				//$ttbm_label = TTBM_Function::get_name();
				?>
				<div class="mpStyle ttbm_settings">
					<div class="mpTabs leftTabs">
						<ul class="tabLists">
							<li data-tabs-target="#ttbm_general_info">
								<span class="dashicons dashicons-admin-settings"></span><?php esc_html_e( 'General Info', 'tour-booking-manager' ); ?>
							</li>
							<li data-tabs-target="#ttbm_settings_pricing">
								<span class="dashicons dashicons-money-alt"></span><?php esc_html_e( ' Pricing', 'tour-booking-manager' ); ?>
							</li>
							<li data-tabs-target="#ttbm_settings_feature">
								<span class="dashicons dashicons-plugins-checked"></span><?php esc_html_e( ' Features', 'tour-booking-manager' ); ?>
							</li>
						</ul>
						<div class="tabsContent tab-content">
							<?php
								//echo get_current_screen();
								$this->general_info( $hotel_id );
								do_action('add_ttbm_settings_feature_content',$hotel_id);
								do_action('add_ttbm_settings_price_content',$hotel_id);
							?>
						</div>
					</div>
				</div>
				<?php
			}
			public function general_info( $tour_id ) {
				?>
				<div class="tabsItem ttbm_settings_general" data-tabs="#ttbm_general_info">
					<h5><?php esc_html_e( 'General Information Settings', 'tour-booking-manager' ); ?></h5>
					<div class="divider"></div>
					<table class="layoutFixed">
						<tbody>
						<?php //$this->duration( $tour_id ); ?>
						<?php //$this->start_price( $tour_id ); ?>
						<?php //$this->max_people( $tour_id ); ?>
						<?php  //$this->age_range( $tour_id ); ?>
						<?php //$this->start_place( $tour_id ); ?>
						<?php $this->location( $tour_id ); ?>
						<?php $this->distance_description( $tour_id ); ?>
						<?php $this->rating( $tour_id ); ?>
						</tbody>
					</table>
				</div>
				<?php
			}
			public function location( $tour_id ) {
				$display_name = 'ttbm_display_hotel_location';
				$display      = TTBM_Function::get_post_info( $tour_id, $display_name, 'on' );
				$checked      = $display == 'off' ? '' : 'checked';
				?>
				<tr>
					<th colspan="3">
						<?php esc_html_e( 'Hotel Location', 'tour-booking-manager' ); ?>
						<?php TTBM_Layout::popup_button_xs( 'add_new_location_popup', esc_html__( 'Create New Location', 'tour-booking-manager' ) ); ?>
					</th>
					<td><?php TTBM_Layout::switch_button( $display_name, $checked ); ?></td>
					<td colspan="3" class="ttbm_location_select_area"><?php TTBM_Settings::location_select( $tour_id ); ?></td>
				</tr>
				<?php
				TTBM_Settings::des_row( 'location' );
				TTBM_Settings::add_new_location_popup();
			}
			public function distance_description($tour_id){
				$display_name = 'ttbm_display_hotel_distance';
				$display      = TTBM_Function::get_post_info( $tour_id, $display_name, 'on' );
				$value_name   = 'ttbm_hotel_distance_des';
				$value        = TTBM_Function::get_post_info( $tour_id, $value_name );
				$placeholder  = esc_html__( 'EX. 1.9 km from centre', 'tour-booking-manager' );
				$checked      = $display == 'off' ? '' : 'checked';
				$active       = $display == 'off' ? '' : 'mActive';
				?>
				<tr>
					<th colspan="3"><?php esc_html_e( 'Distance Description', 'tour-booking-manager' ); ?></th>
					<td><?php TTBM_Layout::switch_button( $display_name, $checked ); ?></td>
					<td colspan="3">
						<label data-collapse="#<?php echo esc_attr( $display_name ); ?>" class="<?php echo esc_attr( $active ); ?>">
							<input class="formControl" name="<?php echo esc_attr( $value_name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"/>
						</label>
					</td>
				</tr>
				<?php
				TTBM_Settings::des_row( 'ttbm_display_hotel_distance' );
			}
			public function rating($tour_id){
				$display_name = 'ttbm_display_hotel_rating';
				$display      = TTBM_Function::get_post_info( $tour_id, $display_name, 'on' );
				$checked      = $display == 'off' ? '' : 'checked';
				$active       = $display == 'off' ? '' : 'mActive';
				$rating=TTBM_Function::get_post_info( $tour_id, 'ttbm_hotel_rating');
				?>
				<tr>
					<th colspan="3">
						<?php esc_html_e( 'Hotel Rating ', 'tour-booking-manager' ); ?>
					</th>
					<td><?php TTBM_Layout::switch_button( $display_name, $checked ); ?></td>
					<td colspan="3" >
						<label data-collapse="#<?php echo esc_attr( $display_name ); ?>" class="<?php echo esc_attr( $active ); ?>">
							<select class="formControl" name="ttbm_hotel_rating">
								<option value="" selected><?php esc_html_e( 'please select hotel rating', 'tour-booking-manager' ); ?></option>
								<option value="1" <?php echo esc_attr( $rating == '1' ? 'selected' : '' ); ?>><?php esc_html_e( '1 Star', 'tour-booking-manager' ); ?></option>
								<option value="2" <?php echo esc_attr( $rating == '2' ? 'selected' : '' ); ?>><?php esc_html_e( '2 Star', 'tour-booking-manager' ); ?></option>
								<option value="3" <?php echo esc_attr( $rating == '3' ? 'selected' : '' ); ?>><?php esc_html_e( '3 Star', 'tour-booking-manager' ); ?> </option>
								<option value="4" <?php echo esc_attr( $rating == '4' ? 'selected' : '' ); ?>><?php esc_html_e( '4 Star', 'tour-booking-manager' ); ?> </option>
								<option value="5" <?php echo esc_attr( $rating == '5' ? 'selected' : '' ); ?>><?php esc_html_e( '5 Star', 'tour-booking-manager' ); ?> </option>
							</select>
						</label>
						</td>
				</tr>
				<?php
				TTBM_Settings::des_row( 'ttbm_display_hotel_rating' );
			}
		}
		new TTBM_Settings_Hotel();
	}