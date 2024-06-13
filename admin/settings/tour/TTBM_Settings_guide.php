<?php
	if (!defined('ABSPATH')) {
        die;
    } // Cannot access pages directly.
    if (!class_exists('TTBM_Settings_guide')) {
        class TTBM_Settings_guide {
            public function __construct() {
	            add_action('add_ttbm_settings_tab_name',[$this,'add_tab'],90);
                add_action('add_ttbm_settings_tab_content', [$this, 'guide_setting']);
                add_action('ttbm_settings_save', [$this, 'save_guide']);
            }
	        public function add_tab(){
		        $ttbm_label = TTBM_Function::get_name();
		        ?>
				<li data-tabs-target="#ttbm_settings_guide">
					<i class="fas fa-hiking"></i><?php echo $ttbm_label.'  '.esc_html__('Guide ', 'tour-booking-manager'); ?>
				</li>
		        <?php
	        }
            public function guide_setting($tour_id){
                $ttbm_label = TTBM_Function::get_name();
                $all_guides = MP_Global_Function::query_post_type('ttbm_guide');
                $display_guide = MP_Global_Function::get_post_info($tour_id, 'ttbm_display_tour_guide', 'off');
                $active_guide = $display_guide == 'off' ? '' : 'mActive';
                $checked_guide = $display_guide == 'off' ? '' : 'checked';
                $guides = MP_Global_Function::get_post_info($tour_id, 'ttbm_tour_guide', array());
                $ttbm_guide_style = MP_Global_Function::get_post_info($tour_id, 'ttbm_guide_style', 'carousel');
                $ttbm_guide_image_style = MP_Global_Function::get_post_info($tour_id, 'ttbm_guide_image_style', 'squire');
                $ttbm_guide_description_style = MP_Global_Function::get_post_info($tour_id, 'ttbm_guide_description_style', 'full');
                ?>
                <div class="tabsItem ttbm_settings_guide" data-tabs="#ttbm_settings_guide">
                    <h2><?php esc_html_e('Guide Settings', 'tour-booking-manager'); ?></h2>
                    <p><?php TTBM_Settings::des_p('guide_settings_description'); ?> </p>
                    
                    <section class="bg-light">
                        <label class="label">
                            <div>
                                <p><?php echo esc_html__('Guide Settings', 'tour-booking-manager').'  '.$ttbm_label.'  '.esc_html__('Guide', 'tour-booking-manager'); ?></p>
                                <span class="text"><?php echo esc_html__('Here you can set tour guide, on/off tour guide etc.', 'tour-booking-manager'); ?></span>
                            </div>
                        </label>
                    </section>

                    <section>
                        <label class="label">
                            <div>
                                <p><?php echo esc_html__('On/Off', 'tour-booking-manager').'  '.$ttbm_label.'  '.esc_html__('Guide', 'tour-booking-manager'); ?> </p>
                                <span class="text"><?php TTBM_Settings::des_p('ttbm_display_tour_guide'); ?> </span>
                            </div>
                            <?php MP_Custom_Layout::switch_button('ttbm_display_tour_guide', $checked_guide); ?>
                        </label>
                    </section>

                    <div data-collapse="#ttbm_display_tour_guide" class="<?php echo esc_attr($active_guide); ?>">
                        <section>
                            <label class="label">
                                <div>
                                    <p><?php esc_html_e('Select tour guide', 'tour-booking-manager'); ?><i class="fas fa-question-circle tool-tips"><span><?php TTBM_Settings::des_p('ttip_tour_guide'); ?></span></i></p>
                                    <span class="text"><?php TTBM_Settings::des_p('ttbm_tour_guide'); ?></span>
                                </div>
                                <div class="w-50">
                                    <select name="ttbm_tour_guide[]" multiple='multiple' class='formControl mp_select2 w-50' data-placeholder="<?php echo esc_html__('Please Select Guide', 'tour-booking-manager'); ?>">
                                        <?php
                                            if ($all_guides->post_count > 0) {
                                                foreach ($all_guides->posts as $guide) {
                                                    $ttbm_id = $guide->ID;
                                                    ?>
                                                    <option value="<?php echo esc_attr($ttbm_id) ?>" <?php echo in_array($ttbm_id, $guides) ? 'selected' : ''; ?>><?php echo get_the_title($ttbm_id); ?></option>
                                                    <?php
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                            </label>
                        </section>

                        <section class="bg-light" style="margin-top: 20px;">
                            <label class="label">
                                <div>
                                    <p><?php echo esc_html__('Guide Dispaly Style', 'tour-booking-manager'); ?></p>
                                    <span class="text"><?php echo esc_html__('To view Guide Photos on frontend, set guide view style here', 'tour-booking-manager'); ?></span>
                                </div>
                            </label>
                        </section>
                        <section>
                            <label class="label">
                                <div>
                                    <p><?php esc_html_e('Guide Style', 'tour-booking-manager'); ?></p>
                                    <span class="text"><?php TTBM_Settings::des_p('ttbm_guide_style'); ?></span>
                                </div>
                                <select name="ttbm_guide_style" class='formControl'>
                                    <option value="all" <?php echo esc_attr($ttbm_guide_style=='all'? 'selected' : '') ; ?>><?php esc_html_e('All Visible', 'tour-booking-manager'); ?></option>
                                    <option value="carousel" <?php echo esc_attr($ttbm_guide_style=='carousel'? 'selected' : '') ; ?>><?php esc_html_e('Carousel', 'tour-booking-manager'); ?></option>
                                </select>
                            </label>
                        </section>
                        
                        <section>
                            <label class="label">
                                <div>
                                    <p><?php esc_html_e('Guide Image Style', 'tour-booking-manager'); ?></p>
                                    <span class="text"><?php TTBM_Settings::des_p('ttbm_guide_image_style'); ?></span>
                                </div>
                                <select name="ttbm_guide_image_style" class='formControl'>
                                    <option value="circle" <?php echo esc_attr($ttbm_guide_image_style=='circle'? 'selected' : '') ; ?>><?php esc_html_e('Circle', 'tour-booking-manager'); ?></option>
                                    <option value="squire" <?php echo esc_attr($ttbm_guide_image_style=='squire'? 'selected' : '') ; ?>><?php esc_html_e('Squire', 'tour-booking-manager'); ?></option>
                                </select>
                            </label>
                        </section>
                        <section>
                            <label class="label">
                                <div>
                                    <p><?php esc_html_e('Guide Description Style', 'tour-booking-manager'); ?></p>  
                                    <span class="text"><?php TTBM_Settings::des_p('ttbm_guide_description_style'); ?></span>
                                </div>
                                <select name="ttbm_guide_description_style" class='formControl'>
                                    <option value="short" <?php echo esc_attr($ttbm_guide_description_style=='short'? 'selected' : '') ; ?>><?php esc_html_e('Short', 'tour-booking-manager'); ?></option>
                                    <option value="full" <?php echo esc_attr($ttbm_guide_description_style=='full'? 'selected' : '') ; ?>><?php esc_html_e('Full', 'tour-booking-manager'); ?></option>
                                </select>
                            </label>
                        </section>
                    </div>
                </div>
                <?php
            }
            public function save_guide( $tour_id ) {
                if ( get_post_type( $tour_id ) == TTBM_Function::get_cpt_name() ) {
                    $ttbm_display_tour_guide = MP_Global_Function::get_submit_info( 'ttbm_display_tour_guide' ) ? 'on' : 'off';
                    update_post_meta( $tour_id, 'ttbm_display_tour_guide', $ttbm_display_tour_guide );
                    $ttbm_tour_guide = MP_Global_Function::get_submit_info( 'ttbm_tour_guide', array() );
                    update_post_meta( $tour_id, 'ttbm_tour_guide', $ttbm_tour_guide );
                    $ttbm_guide_style = MP_Global_Function::get_submit_info( 'ttbm_guide_style', 'carousel' );
                    update_post_meta( $tour_id, 'ttbm_guide_style', $ttbm_guide_style );
                    $ttbm_guide_image_style = MP_Global_Function::get_submit_info( 'ttbm_guide_image_style', 'squire' );
                    update_post_meta( $tour_id, 'ttbm_guide_image_style', $ttbm_guide_image_style );
                    $ttbm_guide_description_style = MP_Global_Function::get_submit_info( 'ttbm_guide_description_style', 'full' );
                    update_post_meta( $tour_id, 'ttbm_guide_description_style', $ttbm_guide_description_style );
                }
            }
        }
        new TTBM_Settings_guide();
    }