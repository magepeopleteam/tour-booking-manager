<?php
if (!defined('ABSPATH')) {
	die;
}

/** Keep operational records behind the same permission as the booking screens. */
if (!class_exists('TTBM_Security')) {
	class TTBM_Security {
		private static function internal_post_types() {
			return array('ttbm_booking', 'ttbm_service_booking', 'ttbm_custom_order', 'ttbm_hotel_booking', 'ttbm_ahistory', 'ttbm_reg_form');
		}

		public static function protected_meta($protected, $meta_key, $meta_type) {
			if ('post' === $meta_type && in_array($meta_key, array('ttbm_theme_file', 'ttbm_hotel_template'), true)) {
				return true;
			}
			return $protected;
		}

		public static function post_type_args($args, $post_type) {
			if (!in_array($post_type, self::internal_post_types(), true)) {
				return $args;
			}
			$args['show_ui'] = isset($args['show_ui']) ? $args['show_ui'] : !empty($args['public']);
			$args['public'] = false;
			$args['publicly_queryable'] = false;
			$args['exclude_from_search'] = true;
			$args['has_archive'] = false;
			$args['rewrite'] = false;
			$args['map_meta_cap'] = false;
			$args['capabilities'] = array_fill_keys(array(
				'edit_post', 'read_post', 'delete_post', 'edit_posts', 'edit_others_posts',
				'publish_posts', 'read_private_posts', 'delete_posts', 'delete_private_posts',
				'delete_published_posts', 'delete_others_posts', 'edit_private_posts',
				'edit_published_posts', 'create_posts',
			), 'manage_options');
			return $args;
		}

		/** Core REST permits reading published posts regardless of their edit capability. */
		public static function rest_access($result, $server, $request) {
			foreach (self::internal_post_types() as $post_type) {
				$type = get_post_type_object($post_type);
				if (!$type || !$type->show_in_rest) {
					continue;
				}
				$namespace = !empty($type->rest_namespace) ? $type->rest_namespace : 'wp/v2';
				$base = !empty($type->rest_base) ? $type->rest_base : $post_type;
				$route = '/' . trim($namespace, '/') . '/' . trim($base, '/');
				if (preg_match('~^' . preg_quote($route, '~') . '(?:/|$)~i', $request->get_route()) && !current_user_can('manage_options')) {
					return new WP_Error('ttbm_private_records', __('You do not have permission to access booking records.', 'tour-booking-manager'), array('status' => rest_authorization_required_code()));
				}
			}
			return $result;
		}
	}
	add_filter('register_post_type_args', array('TTBM_Security', 'post_type_args'), 20, 2);
	add_filter('rest_pre_dispatch', array('TTBM_Security', 'rest_access'), 10, 3);
	add_filter('is_protected_meta', array('TTBM_Security', 'protected_meta'), 10, 3);
}
