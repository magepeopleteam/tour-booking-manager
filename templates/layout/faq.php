<?php
	if (!defined('ABSPATH')) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$faqs = get_post_meta($ttbm_post_id, 'mep_event_faq', true);
	$display_faq = get_post_meta($ttbm_post_id, 'ttbm_display_faq', true);
	if (!empty($faqs) && $display_faq == 'on') {
		?>
        <div class='ttbm_wp_editor'>
			<h2 class="content-title"><?php esc_html_e( "F.A.Q", 'tour-booking-manager' ); ?></h2>

            <div class='ttbm_faq_content'>
                <?php foreach ($faqs as $key => $faq) { ?>
                    <div class="ttbm_faq_item">
                        <div class="ttbm_faq_title justifyBetween" data-open-icon="fa-plus" data-close-icon="fa-minus" data-collapse-target="#ttbm_faq_datails_<?php echo esc_attr($key); ?>" data-add-class="active">
                            <h5><?php echo esc_html($faq['ttbm_faq_title']); ?></h5>
                            <span data-icon class="fas fa-plus"></span>
                        </div>
                        <div data-collapse="#ttbm_faq_datails_<?php echo esc_attr($key); ?>">
                            <div class="ttbm_faq_content ttbm_wp_editor">
                                <?php echo wp_kses_post(html_entity_decode($faq['ttbm_faq_content'])); ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
	<?php } ?>