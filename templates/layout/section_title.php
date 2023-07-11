<?php
    if (!defined('ABSPATH')) {
        exit;
    }
    $ttbm_post_id = $ttbm_post_id ?? get_the_id();
    $title_style = MP_Global_Function::get_post_info($ttbm_post_id, 'ttbm_section_title_style', 'ttbm_title_style_2');
    $title_class = $title_style == 'style_1' ? 'ttbm_widget_title' : $title_style;
    $ttbm_title = $ttbm_title ?? '';
    if ($ttbm_title && $ttbm_post_id) {
        ?>
        <h4 class="<?php echo esc_attr($title_class); ?>" data-placeholder>
            <?php echo esc_html($ttbm_title); ?>
        </h4>
    <?php } ?>