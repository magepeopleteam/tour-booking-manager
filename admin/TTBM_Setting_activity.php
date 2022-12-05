<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'TTBM_Setting_activity' ) ) {
		class TTBM_Setting_activity {
			public function __construct() {
				add_action( 'add_ttbm_settings_tab_name', [ $this, 'add_tab' ], 10 );
				add_action( 'add_ttbm_settings_tab_content', [ $this, 'ttbm_settings_activities' ], 10, 1 );
				//*********Activity***************//
				add_action( 'wp_ajax_load_ttbm_activity_form', [ $this, 'load_ttbm_activity_form' ] );
				add_action( 'wp_ajax_nopriv_load_ttbm_activity_form', [ $this, 'load_ttbm_activity_form' ] );
				add_action( 'wp_ajax_ttbm_reload_activity_list', [ $this, 'ttbm_reload_activity_list' ] );
				add_action( 'wp_ajax_nopriv_ttbm_reload_activity_list', [ $this, 'ttbm_reload_activity_list' ] );
			}
			public function add_tab() {
				?>
				<li data-tabs-target="#ttbm_settings_activies">
					<span class="dashicons dashicons-nametag"></span><?php esc_html_e( ' Activities', 'tour-booking-manager' ); ?>
				</li>
				<?php
			}
			public function ttbm_settings_activities( $tour_id ) {
				$ttbm_label = TTBM_Function::get_name();
				$display    = TTBM_Function::get_post_info( $tour_id, 'ttbm_display_activities', 'on' );
				$active     = $display == 'off' ? '' : 'mActive';
				$checked    = $display == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem mp_settings_area ttbm_settings_activities" data-tabs="#ttbm_settings_activies">
					<h5 class="dFlex">
						<?php TTBM_Layout::switch_button( 'ttbm_display_activities', $checked ); ?>
						<?php esc_html_e( $ttbm_label . ' Activities Settings', 'tour-booking-manager' ); ?>
					</h5>
					<?php TTBM_Settings::des_p( 'ttbm_display_activities' ); ?>
					<div class="divider"></div>
					<div data-collapse="#ttbm_display_activities" class="ttbm_activities_area <?php echo esc_attr( $active ); ?>">
						<?php $this->activities( $tour_id ); ?>
					</div>
				</div>
				<?php
			}
			public function activities( $tour_id ) {
				$activities      = TTBM_Function::get_taxonomy( 'ttbm_tour_activities' );
				$tour_activities = TTBM_Function::get_post_info( $tour_id, 'ttbm_tour_activities', [] );
				?>
				<div class="ttbm_activities_table">
					<table class="layoutFixed">
						<tbody>
						<tr>
							<th>
								<?php esc_html_e( 'Activities', 'tour-booking-manager' ); ?>
								<?php TTBM_Settings::des_p( 'activities' ); ?>
								<?php TTBM_Layout::popup_button_xs( 'add_new_activity_popup', esc_html__( 'Create New Activity', 'tour-booking-manager' ) ); ?>
							</th>
							<td colspan="3">
								<?php if ( sizeof( $activities ) > 0 ) { ?>
									<label>
										<select name="ttbm_tour_activities[]" multiple='multiple' class='formControl ttbm_select2' data-placeholder="<?php esc_html_e( 'Please Select a Activities ', 'tour-booking-manager' ); ?>">
											<?php foreach ( $activities as $activity ) { ?>
												<option value="<?php echo esc_attr( $activity->name ) ?>" <?php echo in_array( $activity->name, $tour_activities ) ? 'selected' : ''; ?>>
													<?php echo esc_html( $activity->name ); ?>
												</option>
											<?php } ?>
										</select>
									</label>
								<?php } else { ?>
									<?php TTBM_Layout::popup_button( 'add_new_activity_popup', esc_html__( 'Create New Activity', 'tour-booking-manager' ) ); ?>
								<?php } ?>
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				<?php
				$this->add_new_activity_popup();
			}
			public function add_new_activity_popup() {
				?>
				<div class="mpPopup" data-popup="add_new_activity_popup">
					<div class="popupMainArea">
						<div class="popupHeader">
							<h4>
								<?php esc_html_e( 'Add New Activity', 'tour-booking-manager' ); ?>
								<p class="_textSuccess_ml_dNone ttbm_success_info">
									<span class="fas fa-check-circle mR_xs"></span>
									<?php esc_html_e( 'Activity is added successfully.', 'tour-booking-manager' ) ?>
								</p>
							</h4>
							<span class="fas fa-times popupClose"></span>
						</div>
						<div class="popupBody ttbm_activity_form_area">
						</div>
						<div class="popupFooter">
							<div class="buttonGroup">
								<button class="_themeButton ttbm_new_activity_save" type="button"><?php esc_html_e( 'Save', 'tour-booking-manager' ); ?></button>
								<button class="_warningButton ttbm_new_activity_save_close" type="button"><?php esc_html_e( 'Save & Close', 'tour-booking-manager' ); ?></button>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			public function load_ttbm_activity_form() {
				?>
				<label class="flexEqual">
					<span><?php esc_html_e( 'Activity Name : ', 'tour-booking-manager' ); ?><sup class="textRequired">*</sup></span>
					<input type="text" name="ttbm_activity_name" class="formControl" required>
				</label>
				<p class="textRequired" data-required="ttbm_activity_name">
					<span class="fas fa-info-circle"></span>
					<?php esc_html_e( 'Activity name is required!', 'tour-booking-manager' ); ?>
				</p>
				<?php TTBM_Settings::des_p( 'ttbm_activity_name' ); ?>
				<div class="divider"></div>
				<label class="flexEqual">
					<span><?php esc_html_e( 'Activity Description : ', 'tour-booking-manager' ); ?></span>
					<textarea name="ttbm_activity_description" class="formControl" rows="3"></textarea>
				</label>
				<?php TTBM_Settings::des_p( 'ttbm_activity_description' ); ?>
				<div class="divider"></div>
				<div class="flexEqual">
					<span><?php esc_html_e( 'Activity Icon : ', 'tour-booking-manager' ); ?><sup class="textRequired">*</sup></span>
					<?php do_action( 'mp_input_add_icon', 'ttbm_activity_icon' ); ?>
				</div>
				<p class="textRequired" data-required="ttbm_activity_icon">
					<span class="fas fa-info-circle"></span>
					<?php esc_html_e( 'Activity icon is required!', 'tour-booking-manager' ); ?>
				</p>
				<?php
				die();
			}
			public function ttbm_reload_activity_list() {
				$ttbm_id = TTBM_Function::data_sanitize( $_POST['ttbm_id'] );
				$this->activities( $ttbm_id );
				die();
			}
		}
		new TTBM_Setting_activity();
	}