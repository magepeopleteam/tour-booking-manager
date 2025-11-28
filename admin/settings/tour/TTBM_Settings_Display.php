<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Display')) {
		class TTBM_Settings_Display {
			public function __construct() {
				add_action('ttbm_meta_box_tab_content', [$this, 'display_settings']);
			}
			public function display_settings($tour_id) {
				$seat_details_checked = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_seat_details', 'on') == 'off' ? '' : 'checked';
				$tour_type_checked = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_tour_type', 'on') == 'off' ? '' : 'checked';
				$hotel_checked = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_hotels', 'on') == 'off' ? '' : 'checked';
				$sidebar_checked = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_sidebar', 'off') == 'off' ? '' : 'checked';


				$display_enquiry = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_enquiry', 'on');
				$enquiry_checked = $display_enquiry == 'off' ? '' : 'checked';
				?>
                <div class="tabsItem ttbm_display_settings" data-tabs="#ttbm_display_settings">
                    <h2><?php esc_html_e('Display Settings', 'tour-booking-manager'); ?></h2>
                    <p><?php TTBM_Settings::des_p('display_settings_description'); ?> </p>
					<?php $content_title_style = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_section_title_style') ?: 'ttbm_title_style_2'; ?>
					<?php $ticketing_system = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_ticketing_system', 'regular_ticket'); ?>
                    <section>
                        <div class="ttbm-header">
                            <h4><i class="fas fa-chalkboard"></i><?php esc_html_e('Display Settings', 'tour-booking-manager'); ?></h4>
                        </div>
                        <div class="dFlex">
                            <div class="col-left">
                                <div class="label">
                                    <div>
                                        <p><?php esc_html_e('Display Seat Count', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_display_seat_details'); ?></span></i></p>
                                    </div>
									<?php TTBM_Custom_Layout::switch_button('ttbm_display_seat_details', $seat_details_checked); ?>
                                </div>
                                <div class="label">
                                    <div>
                                        <p><?php esc_html_e('Display Hotels Info', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_display_hotels'); ?></span></i></p>
                                    </div>
									<?php TTBM_Custom_Layout::switch_button('ttbm_display_hotels', $hotel_checked); ?>
                                </div>
                                <div class="label">
                                    <div>
                                        <p><?php esc_html_e('Display Duration', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_display_duration'); ?></span></i></p>
                                    </div>
									<?php TTBM_Custom_Layout::switch_button('ttbm_display_duration', $display_enquiry); ?>
                                </div>
                                <label class="label">
                                    <div>
                                        <p><?php esc_html_e('Get Enquiry', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('Enable/Disable Get Enquiry Button in frontend', 'tour-booking-manager'); ?></span></i></p>
                                    </div>
									<?php TTBM_Custom_Layout::switch_button('ttbm_display_enquiry', $enquiry_checked); ?>
                                </label>
                                 <?php do_action('add_ttbm_display_settings_left', $tour_id); ?>
                            </div>
                            <div class="col-right">
                                <div class="label">
                                    <div>
                                        <p><?php esc_html_e('Display Tour Type', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_display_tour_type'); ?></span></i></p>
                                    </div>
									<?php TTBM_Custom_Layout::switch_button('ttbm_display_tour_type', $tour_type_checked); ?>
                                </div>
                                <div class="label">
                                    <div>
                                        <p><?php esc_html_e('Dispaly Sidebar widget', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttbm_display_sidebar'); ?></span></i></p>
                                    </div>
									<?php TTBM_Custom_Layout::switch_button('ttbm_display_sidebar', $sidebar_checked); ?>
                                </div>
								<?php $this->rank_tour($tour_id); ?>
                                <?php do_action('add_ttbm_display_settings', $tour_id); ?>
                            </div>
                        </div>
                    </section>

                    <section>
                        <div class="ttbm-header">
                            <h4><i class="mi mi-blog-text"></i><?php esc_html_e('Section Title Settings', 'tour-booking-manager'); ?></h4>
                        </div>
                        <?php
                            $styles = [
                                [
                                    'id'=>'style_1',
                                    'img'=>TTBM_PLUGIN_URL.'/assets/images/style-1.png',
                                    'title'=> 'Style 1'
                                ],
                                [
                                    'id'=>'ttbm_title_style_2',
                                    'img'=>TTBM_PLUGIN_URL.'/assets/images/style-2.png',
                                    'title'=> 'Style 2'
                                ],
                                [
                                    'id'=>'ttbm_title_style_3',
                                    'img'=>TTBM_PLUGIN_URL.'/assets/images/style-3.png',
                                    'title'=> 'Style 3'
                                ],
                            ];
                        ?>
                        <div class="ttbm-title-styles">
                            <input type="hidden" id="ttbm-title-style" name="ttbm_section_title_style" value="<?php echo esc_attr( $content_title_style ); ?>" />
                            <?php foreach( $styles as  $value): ?>
                                <div class="title-style <?php echo ($content_title_style==$value['id'])?'active':''; ?>" data-title-style="<?php echo $value['id']; ?>">
                                    <img src="<?php echo  $value['img']; ?>" alt="">
                                    <p><?php echo $value['title']; ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section>
                        <div class="ttbm-header">
                            <h4><i class="mi mi-blog-text"></i><?php esc_html_e('Booking Form Style', 'tour-booking-manager'); ?></h4>
                        </div>
                        <?php 
                        $booking_form = [
                                [
                                    'id'=>'regular_ticket',
                                    'img'=>TTBM_PLUGIN_URL.'/assets/images/booking-style-1.png',
                                    'title'=> 'Booking Form Open'
                                ],
                                [
                                    'id'=>'availability_section',
                                    'img'=>TTBM_PLUGIN_URL.'/assets/images/booking-style-2.png',
                                    'title'=> 'Booking Form Collapse'
                                ],
                            ];
                        ?>
                        <div class="ttbm-booking-styles">
                            <input type="hidden" id="ttbm-booking-style" name="ttbm_ticketing_system" value="<?php echo esc_attr( $ticketing_system ); ?>" />
                            <?php foreach( $booking_form as  $value): ?>
                                <div class="booking-style <?php echo ($ticketing_system==$value['id'])?'active':''; ?>" data-booking-style="<?php echo $value['id']; ?>">
                                    <img src="<?php echo  $value['img']; ?>" alt="">
                                    <p><?php echo $value['title']; ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>
				<?php
			}
			public function rank_tour($tour_id) {
				$display_name = 'ttbm_display_order_tour';
				$display = TTBM_Global_Function::get_post_info($tour_id, $display_name, 'off');
				$checked = ($display == 'off') ? '' : 'checked';
				$active = ($display == 'off') ? '' : 'mActive';
				$placeholder = '';
				?>
                <div class="label">
                    <div class="label-inner">
                        <p><?php esc_html_e('Rank Tour', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php esc_html_e('Turn on/off rank tour settings.', 'tour-booking-manager'); ?></span></i></p>
                    </div>
                    <div class="_dFlex_alignCenter_justfyBetween">
						<?php TTBM_Custom_Layout::switch_button($display_name, $checked); ?>
                        <input type="number" data-collapse="#<?php echo esc_attr($display_name); ?>" min="0" class="ms-2 <?php echo esc_attr($active); ?>" name="ttbm_travel_rank_tour" value="<?php echo esc_html(TTBM_Global_Function::get_post_info($tour_id, 'ttbm_travel_rank_tour')); ?>" placeholder="<?php echo esc_attr($placeholder); ?>"/>
                    </div>
                </div>
				<?php
			}
		}
		new TTBM_Settings_Display();
	}