<?php	if ( ! defined( 'ABSPATH' ) ) {		die;	}	$ttbm_post_id     = $ttbm_post_id ?? get_the_id();	$why_chooses = TTBM_Function::get_why_choose_us( $ttbm_post_id );	if ( sizeof( $why_chooses ) > 0 && MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_why_choose_us', 'on' ) != 'off' ) {		?>		<div class='ttbm_default_widget'>			<?php do_action( 'ttbm_section_title', 'ttbm_string_why_with_us', esc_html__( 'Why Book With Us? ', 'tour-booking-manager' ) ); ?>			<div class="ttbm_widget_content">				<ul>					<?php						foreach ( $why_chooses as $why_choose ) {							?>							<li><?php echo esc_html( $why_choose ); ?></li>						<?php } ?>				</ul>			</div>		</div>	<?php } ?>