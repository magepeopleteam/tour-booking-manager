<?php
	if (!defined('ABSPATH')) {
		die;
	}
	if (!class_exists('TTBM_Theme_Align')) {
		class TTBM_Theme_Align {
			/**
			 * Shortcode tags registered by TTBM_Shortcodes.
			 *
			 * @return string[]
			 */
			public static function shortcode_tags() {
				return array(
					'ttbm-top-search',
					'travel-list',
					'ttbm-tour-list',
					'ttbm-top-filter',
					'travel-location-list',
					'ttbm-search-result',
					'ttbm-hotel-list',
					'ttbm-registration',
					'ttbm-related',
					'wptravelly-tour-list',
					'ttbm-top-attractions',
					'ttbm-activity_browse',
					'ttbm-texonomy-display',
					'wptravelly-hotel-list',
					'ttbm-hotel-location-list',
					'ttbm-feature-hotel',
					'ttbm-popular-hotel',
					'wptravelly-hotel-search-list',
					'wptravelly-hotel-search',
					'ttbm-hotel-rooms',
					'ttbm-hotel-map',
					'ttbm-hotel-slider',
				);
			}

			/**
			 * @param WP_Post|null $post
			 * @return bool
			 */
			public static function post_has_ttbm_shortcode($post = null) {
				$post = $post ?: get_post();
				if (!$post instanceof WP_Post || !$post->post_content) {
					return false;
				}
				foreach (self::shortcode_tags() as $tag) {
					if (has_shortcode($post->post_content, $tag)) {
						return true;
					}
				}
				if (
					false !== strpos($post->post_content, '[ttbm-') ||
					false !== strpos($post->post_content, '[travel-') ||
					false !== strpos($post->post_content, '[wptravelly-')
				) {
					return true;
				}
				$elementor_data = get_post_meta($post->ID, '_elementor_data', true);
				if (is_string($elementor_data) && false !== strpos($elementor_data, 'ttbm-')) {
					return true;
				}
				return (bool) apply_filters('ttbm_post_has_shortcode', false, $post);
			}

			/**
			 * @return bool
			 */
			public static function should_align_theme() {
				if (!is_singular()) {
					return false;
				}
				$plugin_post_types = array(
					'ttbm_tour',
					'ttbm_hotel',
					'ttbm_hotel_booking',
					'ttbm_places',
					'ttbm_guide',
				);
				if (is_singular($plugin_post_types)) {
					return false;
				}
				return self::post_has_ttbm_shortcode();
			}

			public function __construct() {
				add_filter('body_class', array($this, 'body_class'));
				add_action('wp_enqueue_scripts', array($this, 'enqueue'), 100);
			}

			/**
			 * @param string[] $classes
			 * @return string[]
			 */
			public function body_class($classes) {
				if (self::should_align_theme()) {
					$classes[] = 'ttbm-page-with-shortcode';
				}
				return $classes;
			}

			public function enqueue() {
				if (!self::should_align_theme()) {
					return;
				}
				$content_width = apply_filters('ttbm_theme_align_content_width', '1460px');
				wp_enqueue_style(
					'ttbm_theme_align',
					TTBM_PLUGIN_URL . '/assets/frontend/ttbm_theme_align.css',
					array('ttbm_plugin_global'),
					TTBM_PLUGIN_VERSION
				);
				wp_add_inline_style(
					'ttbm_theme_align',
					sprintf(
						'body.ttbm-page-with-shortcode{--ttbm-theme-shell-width:%1$s;}',
						esc_attr($content_width)
					)
				);
			}
		}
		new TTBM_Theme_Align();
	}
