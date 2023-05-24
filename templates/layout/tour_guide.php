<?php
    if (!defined('ABSPATH')) {
        die;
    }
    $tour_id = $tour_id ?? get_the_id();
    $guides = TTBM_Function::get_post_info($tour_id, 'ttbm_tour_guide', array());
    if (sizeof($guides) > 0 && TTBM_Function::get_post_info($tour_id, 'ttbm_display_tour_guide', 'off') != 'off') {
        ?>
        <div class='mpStyle ttbm_wraper' id="ttbm_tour_guide">
            <div class='ttbm_default_widget'>
                <?php do_action('ttbm_section_titles', $tour_id, esc_html__('Meet our guide ', 'tour-booking-manager')); ?>
                <?php
                    if (sizeof($guides) > 1) {
                        include(TTBM_Function::template_path('layout/carousel_indicator.php'));
                    }
                ?>
                <div class="ttbm_widget_content marZero <?php if (sizeof($guides) > 1) { ?> owl-theme owl-carousel <?php } ?>">
                    <?php foreach ($guides as $guide_id) { ?>
                        <div class="">
                            <div class="bg_image_area mb circle" data-placeholder>
                                <div data-bg-image="<?php echo TTBM_Function::get_image_url($guide_id); ?>"></div>
                                <div class="ttbm_list_title absolute_item bottom" data-placeholder="">
                                    <h5><?php echo get_the_title($guide_id); ?></h5>
                                </div>
                            </div>
                            <?php
                                $des = get_post_field('post_content', $guide_id);
                                $word_count = str_word_count($des);
                                if ($des) {
                                    $message = implode(" ", array_slice(explode(" ", $des), 0, 16));
                                    $more_message = implode(" ", array_slice(explode(" ", $des), 16, $word_count));
                                    ?>
                                    <div class="ttbm_description mp_wp_editor" data-placeholder>
                                        <?php echo TTBM_Function::esc_html($message); ?>
                                        <?php if ($word_count > 16) { ?>
                                            <span data-collapse='#<?php echo esc_attr($guide_id); ?>'><?php echo TTBM_Function::esc_html($more_message); ?></span>
                                            <span class="load_more_text" data-collapse-target="#<?php echo esc_attr($guide_id); ?>">	<?php esc_html_e('view more ', 'tour-booking-manager'); ?></span>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>