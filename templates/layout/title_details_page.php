<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
?>
<div class="ttbm_default__title">
	<h1><?php echo esc_html( get_the_title( $ttbm_post_id ) ); ?></h1>
</div>