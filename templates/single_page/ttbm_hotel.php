<?php
/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.
if ( wp_is_block_theme() ) { ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <?php
        $block_content = do_blocks( '
		<!-- wp:group {"layout":{"type":"constrained"}} -->
		<div class="wp-block-group">
		<!-- wp:post-content /-->
		</div>
		<!-- /wp:group -->'
        );
        wp_head(); ?>
    </head>
    <body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <div class="wp-site-blocks">
        <header class="wp-block-template-part site-header">
            <?php block_header_area(); ?>
        </header>
    </div>
    <?php
} else {
    get_header();
    the_post();
}
do_action( 'mptrs_single_page_before_wrapper' );
if ( post_password_required() ) {
    echo wp_kses_post( get_the_password_form() ); // WPCS: XSS ok.
} else {
    do_action( 'woocommerce_before_single_product' );
    $post_id = get_the_id();
    include_once( TTBM_Function::details_template_file_path( $post_id ) );
}
do_action( 'mptrs_single_page_after_wrapper' );
if ( wp_is_block_theme() ) {
// Code for block themes goes here.
    ?>
    <footer class="wp-block-template-part">
        <?php block_footer_area(); ?>
    </footer>
    <?php wp_footer(); ?>
    </body>
    <?php
} else {
    get_footer();
}
