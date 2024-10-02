<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$ttbm_post_id   = $ttbm_post_id ?? get_the_id();
	$places    = MP_Global_Function::get_post_info($ttbm_post_id, 'ttbm_hiphop_places', array());
	$all_place = new WP_Query( array(
		'post_type'   => 'ttbm_places',
		'post_status' => 'publish'
	) );
	if ( $all_place->post_count > 0 && sizeof( $places ) > 0 && MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_hiphop', 'on' ) != 'off' ) {
		?>
		<div class="ttbm_default_widget" id="place_you_see">

			<?php do_action( 'ttbm_section_title', 'ttbm_string_hiphop_heading', esc_html__( 'Places You’ll See : ', 'tour-booking-manager' ) ); ?>

			<?php
				if ( sizeof( $places ) > 3 ) {
					include( TTBM_Function::template_path( 'layout/carousel_indicator.php' ) );
				}
			?>
			<div class="ttbm_widget_content _mZero <?php if ( sizeof( $places ) > 3 ) { ?> owl-theme owl-carousel <?php } else {
				echo "flexWrap grid";
			} ?>">
				<?php
					$count = 1;
					foreach ( $places as $_places ) {
						$place_name = $_places['ttbm_place_label'];
						$place_id   = $_places['ttbm_city_place_id'];
						if ( $place_id ) {
							$thumbnail = MP_Global_Function::get_image_url( $place_id );
							?>
							<div class="filter_item <?php if ( sizeof( $places ) < 4 ) {
								echo "grid_3";
							} ?>">
								<div class="bg_image_area">
									<div data-bg-image="<?php echo esc_attr( $thumbnail ); ?>"></div>
									<?php
										$description = get_post_field( 'post_content', $place_id );
										if ( $description ) {
											?>
											<span class="circleIcon_xs abTopRight fas fa-question-circle"></span>
											<div class="popover-content">
												<p><?php echo MP_Global_Function::esc_html( $description ) ?></p>
											</div>
										<?php } ?>
								</div>
								<h6 class="_dFlex_mT"><span class="circleIcon_xs"><?php echo esc_html( $count ); ?></span><?php echo esc_html( $place_name ); ?></h6>
							</div>
							<?php
							$count ++;
						}
					}
				?>
			</div>
		</div>
		<?php
		do_action( 'ttbm_hiphop_place_map', $ttbm_post_id );
	}
?>

