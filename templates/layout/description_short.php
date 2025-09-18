<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	// Ensure we have the correct post ID from the current context
	$ttbm_post_id = $ttbm_post_id ?? ($tour_id ?? get_the_id());
	
	$description = TTBM_Global_Function::get_post_info( $ttbm_post_id, 'ttbm_short_description' );
	
	// Check if the description contains Las Vegas demo content - if so, use post content instead
	if ( strlen($description) == 0 || strpos($description, 'Gerry McCambridge') !== false || strpos($description, 'Las Vegas') !== false ) 
	{
		$post_content = get_post_field( 'post_content', $ttbm_post_id );
		$post_content = wp_strip_all_tags($post_content); // Better than sanitize_text_field for content
		$description = substr($post_content, 0, 150); // Increased to 150 chars for better excerpts
		$description = (strlen($post_content) > 150) ? $description.'...' : $description;
	}
	
	if ( $description ) 
	{
	?>
	<div class="ttbm_description ttbm_wp_editor" data-placeholder>
		<div>
<?php echo esc_html($description,'tour-booking-manager'); ?>
		</div>
	</div>
	<?php 
	}