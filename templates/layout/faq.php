<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$faqs = MP_Global_Function::get_post_info($ttbm_post_id, 'mep_event_faq', array());
	if ( sizeof( $faqs ) > 0 && MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_faq', 'on' ) != 'off' ) {
		?>
		<div class='ttbm_description'>
			<h2><?php esc_html_e( 'F.A.Q ', 'tour-booking-manager' ) ?></h2>
			<div class='ttbm_faq_content'>
				<?php
					foreach ( $faqs as $key => $faq ) {
						$faq_title = array_key_exists( 'ttbm_faq_title', $faq ) ? html_entity_decode( $faq['ttbm_faq_title'] ) : '';
						$faq_text = array_key_exists( 'ttbm_faq_content', $faq ) ? html_entity_decode( $faq['ttbm_faq_content'] ) : '';
						$faq_images = array_key_exists( 'ttbm_faq_img', $faq ) ? html_entity_decode( $faq['ttbm_faq_img'] ) : '';
						$images = explode( ',', $faq_images );
						?>
						<div class="ttbm_faq_item">
							<h2 class="ttbm_faq_title justifyBetween" data-open-icon="fa-plus" data-close-icon="fa-minus" data-collapse-target="#ttbm_faq_datails_<?php esc_attr_e( $key ); ?>" data-add-class="active">
								<?php echo esc_html( $faq_title ); ?>
								<span data-icon class="fas fa-plus"></span>
							</h2>
							<div data-collapse="#ttbm_faq_datails_<?php esc_attr_e( $key ); ?>">
								<div class="ttbm_faq_content mp_wp_editor">
									<?php
										if ( $faq_images && sizeof( $images ) > 0 ) {
											do_action( 'add_mp_custom_slider_only', $images );
										}
									?>
									<?php echo do_shortcode($faq_text); ?>
								</div>
							</div>
						</div>
					<?php } ?>
			</div>
		</div>
		<?php } ?>