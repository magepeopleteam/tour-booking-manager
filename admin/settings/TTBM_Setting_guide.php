<?php
    if (!defined('ABSPATH')) {
        die;
    } // Cannot access pages directly.
    if (!class_exists('TTBM_Setting_guide')) {
        class TTBM_Setting_guide {
            public function __construct() {
                add_action('add_ttbm_settings_tab_content', [$this, 'guide_setting']);
                add_action('ttbm_settings_save', [$this, 'save_guide']);
            }
            public function guide_setting($tour_id){
                $ttbm_label = TTBM_Function::get_name();
                $all_guides = TTBM_Query::query_post_type('ttbm_guide');
                $display_guide = TTBM_Function::get_post_info($tour_id, 'ttbm_display_tour_guide', 'off');
                $active_guide = $display_guide == 'off' ? '' : 'mActive';
                $checked_guide = $display_guide == 'off' ? '' : 'checked';
                $guides = TTBM_Function::get_post_info($tour_id, 'ttbm_tour_guide', array());
                ?>
                <div class="tabsItem" data-tabs="#ttbm_settings_guide">
                    <h5 class="dFlex">
                        <span class="mR"><?php echo esc_html__('On/Off', 'tour-booking-manager').'  '.$ttbm_label.'  '.esc_html__('On/Off Tour Guide', 'tour-booking-manager'); ?></span>
                        <?php TTBM_Layout::switch_button('ttbm_display_tour_guide', $checked_guide); ?>
                    </h5>
                    <?php TTBM_Settings::des_p('ttbm_display_tour_guide'); ?>
                    <div class="divider"></div>
                    <div data-collapse="#ttbm_display_tour_guide" class="<?php echo esc_attr($active_guide); ?>">
                        <table class="layoutFixed">
                            <tbody>
                            <tr>
                                <th><?php esc_html_e('Select tour guide', 'tour-booking-manager'); ?></th>
                                <td colspan="3">
                                    <?php //echo '<pre>';print_r($all_guides);echo '</pre>';  ?>
                                    <label>
                                        <select name="ttbm_tour_guide[]" multiple='multiple' class='formControl ttbm_select2' data-placeholder="<?php echo esc_html__('Please Select Guide', 'tour-booking-manager'); ?>">
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
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4"><?php TTBM_Settings::des_p('ttbm_tour_guide'); ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php
            }
            public function save_guide( $tour_id ) {
                if ( get_post_type( $tour_id ) == TTBM_Function::get_cpt_name() ) {
                    $ttbm_display_tour_guide = TTBM_Function::get_submit_info( 'ttbm_display_tour_guide' ) ? 'on' : 'off';
                    update_post_meta( $tour_id, 'ttbm_display_tour_guide', $ttbm_display_tour_guide );
                    $ttbm_tour_guide = TTBM_Function::get_submit_info( 'ttbm_tour_guide', array() );
                    update_post_meta( $tour_id, 'ttbm_tour_guide', $ttbm_tour_guide );
                }
            }
        }
        new TTBM_Setting_guide();
    }