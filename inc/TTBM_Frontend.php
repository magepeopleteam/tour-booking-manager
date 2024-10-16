<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'TTBM_Frontend' ) ) {
		class TTBM_Frontend {
			public function __construct() {
				add_filter( 'single_template', array( $this, 'load_single_template' ) );
				add_filter( 'template_include', array( $this, 'load_template' ) );
			}
			public function load_single_template( $template ): string {
				global $post;
				if ( $post->post_type && $post->post_type == TTBM_Function::get_cpt_name()) {
					$template = TTBM_Function::template_path( 'single_page/single-ttbm.php' );
				}
				if ( $post->post_type && $post->post_type == 'ttbm_booking') {
					$template = TTBM_Function::template_path( 'single_page/ttbm_booking.php' );
				}
				return $template;
			}
			public function load_template( $template ): string {
				if ( is_tax( 'ttbm_tour_cat' ) ) {
					$template = TTBM_Function::template_path( 'single_page/category.php' );
				}
				if ( is_tax( 'ttbm_tour_org' ) ) {
					$template = TTBM_Function::template_path( 'single_page/organizer.php' );
				}
				if ( is_tax( 'ttbm_tour_location' ) ) {
					$template = TTBM_Function::template_path( 'single_page/location.php' );
				}
				return $template;
			}
		}
		new TTBM_Frontend();
	}