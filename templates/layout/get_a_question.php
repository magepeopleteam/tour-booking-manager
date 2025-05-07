<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	if ( TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_get_question', 'on' ) != 'off' ) {
		$contact_text  = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_contact_text');
		$contact_phone = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_contact_phone');
		$contact_email = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_contact_email');
		if ( $contact_text || $contact_phone || $contact_email ) {
			?>
			<div class='ttbm_default_widget'>
				<?php do_action( 'ttbm_section_title', 'ttbm_string_get_question', esc_html__( 'Got a Question? ', 'tour-booking-manager' ) ); ?>
				<div class="ttbm_widget_content">
					<ul>
						<?php if ( $contact_text ) { ?>
							<li><?php echo esc_html( $contact_text ); ?></li>
						<?php } ?>

						<?php if ( $contact_phone ) { ?>
							<li>
								<a href='tel:<?php echo esc_html( $contact_phone ); ?>'>
									<span class="circleIcon_xs fas fa-phone-alt"></span>
									<?php echo esc_html( $contact_phone ); ?>
								</a>
							</li>
						<?php } ?>

						<?php if ( $contact_email ) { ?>
							<li>
								<a href='mailto:<?php echo esc_html( $contact_email ); ?>'>
									<span class="circleIcon_xs far fa-envelope"></span>
									<?php echo esc_html( $contact_email ); ?>
								</a>
							</li>
						<?php } ?>
					</ul>
					<?php do_action( 'ttbm_enquery_popup_button');?>
				</div>
			</div>
			<?php
		}
	}
?>