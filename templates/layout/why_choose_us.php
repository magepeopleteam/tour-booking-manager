<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id     = $ttbm_post_id ?? get_the_id();
	$why_chooses = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_why_choose_us_texts', array());
	if ( sizeof( $why_chooses ) > 0 && TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_why_choose_us', 'on' ) != 'off' ) {
		?>
		<div class="ttbm_default_widget ttbm_why_choose_us">
			<?php do_action( 'ttbm_section_title', 'ttbm_string_why_with_us', esc_html__( 'Why Book With Us? ', 'tour-booking-manager' ) ); ?>
			<div class="ttbm_widget_content">
				<ul class="ttbm_why_choose_us_list">
					<?php
						foreach ( $why_chooses as $why_choose ) {
							?>
							<li>
								<span class="ttbm_why_choose_us_icon" aria-hidden="true"><i class="mi mi-check"></i></span>
								<span class="ttbm_why_choose_us_text"><?php echo esc_html( $why_choose ); ?></span>
							</li>
							<?php
						}
						?>
				</ul>
			</div>
		</div>
		<?php
	}
