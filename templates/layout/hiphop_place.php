<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$ttbm_post_id   = $ttbm_post_id ?? get_the_id();
	$places    = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_hiphop_places', array());
	$all_place = new WP_Query( array(
		'post_type'   => 'ttbm_places',
		'post_status' => 'publish'
	) );
	if ( $all_place->post_count > 0 && sizeof( $places ) > 0 && TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_display_hiphop', 'on' ) != 'off' ) {
		?>
		<div class="place_you_see" id="place_you_see">

			<h2 class="content-title"><?php esc_html_e( "Places You’ll See :", 'tour-booking-manager' ); ?></h2>

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
							$thumbnail = TTBM_Global_Function::get_image_url( $place_id );
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
												<p><?php echo esc_html( $description ) ?></p>
											</div>
										<?php } ?>
								</div>
								<h6 class="_dFlex_mT"><?php echo esc_html($count.'.  '. $place_name ); ?></h6>
							</div>
							<?php
							$count ++;
						}
					}
				?>
			</div>
		</div>
		<?php
	}
	do_action( 'ttbm_hiphop_place_map', $ttbm_post_id );
?>

