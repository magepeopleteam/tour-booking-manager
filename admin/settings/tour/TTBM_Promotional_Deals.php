<?php
if (!defined('ABSPATH')) {
    die;
}

if (!class_exists('TTBM_Promotional_Deals')) {
    class TTBM_Promotional_Deals{
        public function __construct() {
            add_action('ttbm_meta_box_tab_content', [$this, 'ttbm_settings_activities'], 10, 1);
        }

        public function ttbm_settings_activities($tour_id) {
            $display = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_display_top_picks_deals', 'on');
            $active = $display == 'off' ? '' : 'mActive';
            $checked = $display == 'off' ? '' : 'checked';
            $activities = array( 'feature', 'popular', 'trending', 'deal-discount' );
            $tour_activities_array = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_top_picks_deals', []);
            ?>
            <div class="tabsItem ttbm_settings_area ttbm_settings_activities" data-tabs="#ttbm_add_promotional_setting">
                <h2><?php esc_html_e('Top Picks & Deals', 'tour-booking-manager'); ?></h2>
                <p><?php TTBM_Settings::des_p('top_picks_and_deals'); ?></p>
                <section>
                    <div class="ttbm-header">
                        <h4><i class="fas fa-clipboard-list"></i><?php esc_html_e('Top Picks Deals Setting', 'tour-booking-manager'); ?></h4>
                        <?php TTBM_Custom_Layout::switch_button('ttbm_display_top_picks_deals', $checked); ?>
                    </div>
                    <div data-collapse="#ttbm_display_top_picks_deals" class="ttbm_activities_area <?php echo esc_attr($active); ?>">
                        <div class="ttbm_activities_table">
                            <div class="includedd-features-section">
                                <div class="groupCheckBox">
                                    <?php foreach ( $activities as $activity ) { ?>
                                        <label class="customCheckboxLabel">
                                            <input type="checkbox" name="ttbm_top_picks_deals[]" value="<?php echo esc_attr( $activity ); ?>" <?php echo in_array( $activity, $tour_activities_array ) ? 'checked' : ''; ?> />
                                            <span class="customCheckbox"><?php echo esc_html( $activity ); ?></span>
                                        </label>
                                    <?php } ?>
                                </div>
                                <!--<div class="ttbm_promotional_shortcode_holder">
                                    <?php /*foreach ( $activities as $activity ) { */?>
                                        <div class="ttbm_promotional_shortcode">
                                            [wptravelly-tour-list type='<?php /*echo esc_attr( $activity );*/?>' column=2 show=2 carousel='no']
                                        </div>
                                    <?php /*} */?>
                                </div>-->
                                
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="ttbm_checked_top_picks_deals_holder" name="ttbm_checked_top_picks_deals_holder" value="<?php echo esc_attr(implode(',', TTBM_Global_Function::get_post_info($tour_id, 'ttbm_checked_top_picks_deals_holder', []))); ?>"/>
                </section>
                <section>
                    <div class="ttbm-header">
                        <h4><i class="fas fa-clipboard-list"></i><?php esc_html_e('Promotional Shortcodes', 'tour-booking-manager'); ?></h4>
                    </div>
                    
                    <div class="ttbm_promotional_shortcode">
                        <h3 class="ttbm_shortcode_item_title"><?php esc_attr_e( 'Feature Tours', 'tour-booking-manager' );?></h3>
                        <div class="ttbm_shortcode_box">
                            <div class="ttbm_shortcode_text">[wptravelly-tour-list type='feature' column=2 show=4 carousel='no']</div>
                            <button class="ttbm_copy_btn"><?php esc_attr_e( 'Copy', 'tour-booking-manager' );?></button>
                        </div>
                    </div>
                    <div class="ttbm_promotional_shortcode">
                        <h3 class="ttbm_shortcode_item_title"><?php esc_attr_e( 'Popular Tours', 'tour-booking-manager' );?></h3>
                        <div class="ttbm_shortcode_box">
                            <div class="ttbm_shortcode_text">[wptravelly-tour-list type='popular' column=2 show=4 carousel='no']</div>
                            <button class="ttbm_copy_btn"><?php esc_attr_e( 'Copy', 'tour-booking-manager' );?></button>
                        </div>
                    </div>
                    <div class="ttbm_promotional_shortcode">
                        <h3 class="ttbm_shortcode_item_title"><?php esc_attr_e( 'Trending Tours', 'tour-booking-manager' );?></h3>
                        <div class="ttbm_shortcode_box">
                            <div class="ttbm_shortcode_text">[wptravelly-tour-list type='trending' column=2 show=4 carousel='no']</div>
                            <button class="ttbm_copy_btn"><?php esc_attr_e( 'Copy', 'tour-booking-manager' );?></button>
                        </div>
                    </div>
                    <div class="ttbm_promotional_shortcode">
                        <h3 class="ttbm_shortcode_item_title"><?php esc_attr_e( 'Deal & Discounts', 'tour-booking-manager' );?></h3>
                        <div class="ttbm_shortcode_box">
                            <div class="ttbm_shortcode_text">[wptravelly-tour-list type='deal-discount' column=2 show=2 carousel='no']</div>
                            <button class="ttbm_copy_btn"><?php esc_attr_e( 'Copy', 'tour-booking-manager' );?></button>
                        </div>
                    </div>
                </section>
            </div>
            <?php
        }

    }

    new TTBM_Promotional_Deals();
}