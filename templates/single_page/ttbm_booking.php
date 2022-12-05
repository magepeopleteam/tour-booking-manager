<?php
	if ( is_user_logged_in() ) {
		the_post();
		$user               = wp_get_current_user();
		$ticket_user_id     = get_post_meta(get_the_id(),'ttbm_user_id',true);
		$current_user_id    = get_current_user_id();
		?>
		<!DOCTYPE html>
		<html lang="en">
		<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2.0">
			<?php wp_head(); ?>
			<style>
				<?php  do_action('ttbm_booking_details_page_style',get_the_id()); ?>
				.ttbm_tkt_row {
					display: block;
				}

				.ttbm_ticket_body_col_6 {
					display: inline-block;
					width: 49%;
				}
			</style>
			<?php  do_action('ttbm_booking_details_page_head',get_the_id()); ?>
		</head>
		<body>
		<?php
			if($ticket_user_id == $current_user_id ||  in_array( 'administrator', (array) $user->roles ) ){
				do_action('ttbm_booking_details_page',get_the_id());
			}else{
				esc_html_e('Sorry, You Can not see this page, Because Its not your Attendee Information.','tour-booking-manager');
			}
		?>

		<?php  do_action('ttbm_booking_details_page_footer',get_the_id()); ?>
		</body>
		</html>
	<?php } ?>