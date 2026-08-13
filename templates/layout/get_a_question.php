<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$get_question = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_get_question', 'on' );
	$display_enquiry = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_display_enquiry', 'on');
	if ( $get_question != 'off' || $display_enquiry != 'off' ) {
		$contact_text  = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_contact_text');
		$contact_phone = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_contact_phone');
		$contact_email = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_contact_email');
		?>
		<div class="ttbm_default_widget ttbm_get_question">
			<?php do_action( 'ttbm_section_title', 'ttbm_string_get_question', esc_html__( 'Got a Question? ', 'tour-booking-manager' ) ); ?>
			<div class="ttbm_widget_content">
				<?php if ( $get_question != 'off' ) : ?>
					<?php if ( $contact_text ) { ?>
						<p class="ttbm_get_question_text"><?php echo esc_html( $contact_text ); ?></p>
					<?php } ?>

					<?php if ( $contact_phone || $contact_email ) { ?>
						<ul class="ttbm_get_question_contacts">
							<?php if ( $contact_phone ) { ?>
								<li>
									<a href="tel:<?php echo esc_attr( $contact_phone ); ?>">
										<span class="ttbm_get_question_icon" aria-hidden="true"><i class="mi mi-phone-call"></i></span>
										<span class="ttbm_get_question_label"><?php echo esc_html( $contact_phone ); ?></span>
									</a>
								</li>
							<?php } ?>

							<?php if ( $contact_email ) { ?>
								<li>
									<a href="mailto:<?php echo esc_attr( $contact_email ); ?>">
										<span class="ttbm_get_question_icon" aria-hidden="true"><i class="mi mi-envelope"></i></span>
										<span class="ttbm_get_question_label"><?php echo esc_html( $contact_email ); ?></span>
									</a>
								</li>
							<?php } ?>
						</ul>
					<?php } ?>
				<?php endif; ?>
				<?php do_action( 'ttbm_enquery_popup_button' ); ?>
			</div>
		</div>
		<?php
	}
?>
