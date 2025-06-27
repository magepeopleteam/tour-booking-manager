<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$faqs = get_post_meta($ttbm_post_id, 'mep_event_faq',true);
	$display_faq = get_post_meta($ttbm_post_id, 'ttbm_display_faq',true);

	if( !empty($faqs) && $display_faq == 'on' ) {
		?>
		<div class='ttbm_description'>
			<h2 class="ttbm_description_title"><?php esc_html_e( 'F.A.Q ', 'tour-booking-manager' ) ?></h2>
			<div class='ttbm_faq_content'>
				<?php
					foreach ( $faqs as $key => $faq ) {
					?>
					<div class="ttbm_faq_item">
						<h2 class="ttbm_faq_title justifyBetween" data-open-icon="fa-plus" data-close-icon="fa-minus" data-collapse-target="#ttbm_faq_datails_<?php esc_attr_e( $key ); ?>" data-add-class="active">
							<?php echo esc_html( $faq['ttbm_faq_title'] ); ?>
							<span data-icon class="fas fa-plus"></span>
						</h2>
						<div data-collapse="#ttbm_faq_datails_<?php esc_attr_e( $key ); ?>">
							<div class="ttbm_faq_content ttbm_wp_editor">
								<?php echo wp_kses_post($faq['ttbm_faq_content']); ?>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	<?php } ?>