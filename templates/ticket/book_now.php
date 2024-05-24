<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$tour_id=$tour_id??TTBM_Function::post_id_multi_language($ttbm_post_id);
	$ttbm_product_id = MP_Global_Function::get_post_info( $tour_id, 'link_wc_product' );
	if ( ! empty( $ttbm_product_id ) ) {
		$seat_infos = MP_Global_Function::get_post_info( $tour_id, 'ttbma_seat_plan', array());
		$display = MP_Global_Function::get_post_info( $tour_id, 'ttbma_display_seat_plan', 'off' );
		$display_front_end = MP_Global_Function::get_post_info($tour_id, 'frontend_display_seat_plan', 'on');
		$seat_plan  = class_exists('TTBMA_Seat_Plan') && $display == 'on' && sizeof($seat_infos)>0 && $display_front_end=='on'? 'dNone' : '';
		$button_type=apply_filters('ttbm_book_now_button_type','button',$tour_id);
		?>
		<div class="dLayout_xs justifyBetween ttbm_book_now_area" data-placeholder>
			<div class="fdColumn">
				<p><strong> <?php esc_html_e( 'Quantity : ', 'tour-booking-manager' ); ?></strong>&nbsp;<span class="tour_qty"></span></p>
				<p><strong> <?php esc_html_e( 'Total : ', 'tour-booking-manager' ); ?></strong>&nbsp;<span class="tour_price"></span></p>
			</div>
			<?php do_action('ttbm_before_add_cart_btn', $ttbm_product_id,$tour_id); ?>
			<?php if(class_exists('TTBMA_Seat_Plan') && $display == 'on' && sizeof($seat_infos)>0 && $display_front_end=='on'){ ?>
				<button class="dButton ttbm_load_seat_plan" type="submit">
					<?php esc_html_e( 'Seat Plan', 'tour-booking-manager' ) ; ?>
				</button>
			<?php } ?>
			<button class="dButton ttbm_book_now <?php echo esc_attr($seat_plan); ?>" type="<?php echo esc_attr($button_type); ?>">
				<span class="fas fa-cart-plus"></span>
				<?php esc_html_e( 'Book Now', 'tour-booking-manager' ); ?>
			</button>
			<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $ttbm_product_id ); ?>" class="dNone ttbm_add_to_cart">
				<?php esc_html_e( 'Book Now', 'tour-booking-manager' ); ?>
			</button>
		</div>
	<?php } ?>