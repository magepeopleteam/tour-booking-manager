<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$description = MP_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_short_description' );
	if ( strlen($description) == 0) 
	{
		$post_content = get_post_field( 'post_content', $ttbm_post_id );
		$description = substr($post_content,0,100);
		$description = (strlen($description) >= 100) ? $description.'...':$description;
	}
	
	if ( $description ) 
	{
	?>
	<div class="ttbm_description mp_wp_editor" data-placeholder>
		<div>
			<?php esc_html_e($description); ?>				
			<?php //echo do_shortcode($description); ?>
			<?php //echo MP_Global_Function::esc_html( $description ); ?>
		</div>
	</div>
	<?php 
	}