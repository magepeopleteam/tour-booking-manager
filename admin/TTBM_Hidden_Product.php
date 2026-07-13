<?php
	if (!defined('ABSPATH')) {
		exit;
	}  // if direct access
	if (!class_exists('TTBM_Hidden_Product')) {
		class TTBM_Hidden_Product {
			/**
			 * Prevents nested save_post work while syncing the linked WC product.
			 *
			 * @var bool
			 */
			private static $syncing_hidden_product = false;

			public function __construct() {
				add_action('wp_insert_post', array($this, 'create_hidden_wc_product_on_publish'), 10, 3);
				add_action('save_post', array($this, 'run_link_product_on_save'), 99, 1);
				add_action('parse_query', array($this, 'hide_wc_hidden_product_from_product_list'));
				add_action('wp', array($this, 'hide_hidden_wc_product_from_frontend'));
				//******************//
				add_action('wp_head', [$this, 'url_exclude_search_engine']);
				add_action('init', [$this, 'get_all_hidden_product_id']);
				add_filter('wpseo_exclude_from_sitemap_by_post_ids', [$this, 'get_all_hidden_product_id']);
			}
			private function sync_hidden_product_price($product_id) {
				$current_price = get_post_meta($product_id, '_price', true);
				$current_regular = get_post_meta($product_id, '_regular_price', true);
				$current_sale = get_post_meta($product_id, '_sale_price', true);
				$needs_update = ('0' !== (string) $current_price)
					|| ('0' !== (string) $current_regular)
					|| ('' !== (string) $current_sale);
				if (!$needs_update) {
					return;
				}
				update_post_meta($product_id, '_price', 0);
				update_post_meta($product_id, '_regular_price', 0);
				update_post_meta($product_id, '_sale_price', '');
				if (function_exists('wc_delete_product_transients')) {
					wc_delete_product_transients($product_id);
				}
			}
			private function update_product_meta_if_changed($product_id, $meta_key, $meta_value): void {
				$current_value = get_post_meta($product_id, $meta_key, true);
				if ((string) $current_value === (string) $meta_value) {
					return;
				}
				update_post_meta($product_id, $meta_key, $meta_value);
			}
			private function find_hidden_wc_product_id($post_id): int {
				$product_ids = get_posts(
					array(
						'post_type'              => 'product',
						'post_status'            => 'any',
						'posts_per_page'         => 1,
						'fields'                 => 'ids',
						'no_found_rows'          => true,
						'update_post_meta_cache' => false,
						'update_post_term_cache'=> false,
						'meta_query'             => array(
							array(
								'key'     => 'link_ttbm_id',
								'value'   => $post_id,
								'compare' => '=',
							),
						),
					)
				);
				return !empty($product_ids) ? (int) $product_ids[0] : 0;
			}
			private function resolve_linked_product_id($post_id): int {
				$product_id = (int) TTBM_Global_Function::get_post_info($post_id, 'link_wc_product', 0);
				if ($product_id > 0 && 'product' === get_post_type($product_id)) {
					return $product_id;
				}
				$product_id = $this->find_hidden_wc_product_id($post_id);
				if ($product_id > 0) {
					update_post_meta($post_id, 'link_wc_product', $product_id);
					update_post_meta($product_id, 'link_ttbm_id', $post_id);
				}
				return $product_id;
			}
			public function create_hidden_wc_product($post_id, $title) {
				$new_post = array(
					'post_title' => $title,
					'post_content' => '',
					'post_name' => uniqid(),
					'post_category' => array(),
					'tags_input' => array(),
					'post_status' => 'publish',
					'post_type' => 'product'
				);
				$pid = wp_insert_post($new_post);
				update_post_meta($post_id, 'link_wc_product', $pid);
				update_post_meta($pid, 'link_ttbm_id', $post_id);
				$this->sync_hidden_product_price($pid);
				update_post_meta($pid, '_sold_individually', 'yes');
				update_post_meta($pid, '_virtual', 'yes');
				$terms = array('exclude-from-catalog', 'exclude-from-search');
				wp_set_object_terms($pid, $terms, 'product_visibility');
				update_post_meta($post_id, 'check_if_run_once', true);
			}
			public function create_hidden_wc_product_on_publish($post_id, $post) {
				if (!TTBM_Global_Function::has_woocommerce()) {
					return;
				}
				if ($post->post_type == TTBM_Function::get_cpt_name() && $post->post_status == 'publish' && empty(TTBM_Global_Function::get_post_info($post_id, 'check_if_run_once'))) {
					$new_post = array(
						'post_title' => $post->post_title,
						'post_content' => '',
						'post_name' => uniqid(),
						'post_category' => array(),  // Usable for custom taxonomies too
						'tags_input' => array(),
						'post_status' => 'publish', // Choose: publish, preview, future, draft, etc.
						'post_type' => 'product'  //'post',page' or use a custom post type if you want to
					);
					$pid = wp_insert_post($new_post);
					$product_type = 'yes';
					update_post_meta($post_id, 'link_wc_product', $pid);
					update_post_meta($pid, 'link_ttbm_id', $post_id);
					$this->sync_hidden_product_price($pid);
					update_post_meta($pid, '_sold_individually', 'yes');
					update_post_meta($pid, '_virtual', $product_type);
					$terms = array('exclude-from-catalog', 'exclude-from-search');
					wp_set_object_terms($pid, $terms, 'product_visibility');
					update_post_meta($post_id, 'check_if_run_once', true);
				}
			}
			public function count_hidden_wc_product($post_id): int {
				$args = array(
					'post_type' => 'product',
					'posts_per_page' => -1,
					'meta_query' => array(
						array(
							'key' => 'link_ttbm_id',
							'value' => $post_id,
							'compare' => '='
						)
					)
				);
				$loop = new WP_Query($args);
				return $loop->post_count;
			}
			public function run_link_product_on_save($post_id) {
				if (!TTBM_Global_Function::has_woocommerce()) {
					return;
				}
				if (self::$syncing_hidden_product) {
					return;
				}
				if (get_post_type($post_id) != TTBM_Function::get_cpt_name()) {
					return;
				}
				if (!isset($_POST['ttbm_ticket_type_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ttbm_ticket_type_nonce'])), 'ttbm_ticket_type_nonce')) {
					return;
				}
				if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
					return;
				}
				if (!current_user_can('edit_post', $post_id)) {
					return;
				}

				$event_name = get_the_title($post_id);
				$product_id = $this->resolve_linked_product_id($post_id);
				if ($product_id <= 0) {
					$this->create_hidden_wc_product($post_id, $event_name);
					$product_id = $this->resolve_linked_product_id($post_id);
				}
				if ($product_id <= 0 || 'product' !== get_post_type($product_id)) {
					return;
				}

				self::$syncing_hidden_product = true;

				$tour_thumb_id = (int) get_post_thumbnail_id($post_id);
				$product_thumb_id = (int) get_post_thumbnail_id($product_id);
				if ($tour_thumb_id !== $product_thumb_id) {
					if ($tour_thumb_id > 0) {
						set_post_thumbnail($product_id, $tour_thumb_id);
					} else {
						delete_post_thumbnail($product_id);
					}
				}

				if ('publish' !== get_post_status($product_id)) {
					wp_publish_post($product_id);
				}

				$tax_status = isset($_POST['_tax_status']) ? sanitize_text_field(wp_unslash($_POST['_tax_status'])) : 'none';
				$tax_class  = isset($_POST['_tax_class']) ? sanitize_text_field(wp_unslash($_POST['_tax_class'])) : '';
				$this->update_product_meta_if_changed($product_id, '_tax_status', $tax_status);
				$this->update_product_meta_if_changed($product_id, '_tax_class', $tax_class);
				$this->update_product_meta_if_changed($product_id, '_stock_status', 'instock');
				$this->update_product_meta_if_changed($product_id, '_manage_stock', 'no');
				$this->update_product_meta_if_changed($product_id, '_virtual', 'yes');
				$this->update_product_meta_if_changed($product_id, '_sold_individually', 'yes');
				$this->sync_hidden_product_price($product_id);

				$product_post = get_post($product_id);
				if ($product_post instanceof WP_Post && $product_post->post_title !== $event_name) {
					remove_action('save_post', array($this, 'run_link_product_on_save'), 99);
					wp_update_post(
						array(
							'ID'         => $product_id,
							'post_title' => $event_name,
							'post_name'  => uniqid(),
						)
					);
					add_action('save_post', array($this, 'run_link_product_on_save'), 99, 1);
				}

				self::$syncing_hidden_product = false;
			}
			public function hide_wc_hidden_product_from_product_list($query) {
				if (!TTBM_Global_Function::has_woocommerce()) {
					return;
				}
				global $pagenow;
				$q_vars = &$query->query_vars;
				if ($pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == 'product') {
					$tax_query = array(
						[
							'taxonomy' => 'product_visibility',
							'field' => 'slug',
							'terms' => 'exclude-from-catalog',
							'operator' => 'NOT IN',
						]
					);
					$query->set('tax_query', $tax_query);
				}
			}
			public function hide_hidden_wc_product_from_frontend() {
				if (!TTBM_Global_Function::has_woocommerce()) {
					return;
				}
				global $post, $wp_query;
				if (is_product()) {
					$post_id = $post->ID;
					$visibility = get_the_terms($post_id, 'product_visibility');
					if (is_object($visibility)) {
						if ($visibility[0]->name == 'exclude-from-catalog') {
							$check_event_hidden = TTBM_Global_Function::get_post_info($post_id, 'link_ttbm_id', 0);
							if ($check_event_hidden > 0) {
								$wp_query->set_404();
								status_header(404);
								get_template_part(404);
								exit();
							}
						}
					}
				}
			}
			//**************Google search url hidden*********************//
			public function url_exclude_search_engine() {
				if (!TTBM_Global_Function::has_woocommerce()) {
					return;
				}
				global $post;
				if (is_single() && is_product()) {
					$post_id = $post->ID;
					$visibility = get_the_terms($post_id, 'product_visibility') ? get_the_terms($post_id, 'product_visibility') : [0];
					if (is_object($visibility[0]) && $visibility[0]->name == 'exclude-from-catalog') {
						$check_hidden = TTBM_Global_Function::get_post_info($post_id, 'link_ttbm_id', 0);
						if ($check_hidden > 0) {
							?>
                            <meta name="robots" content="noindex, nofollow">
							<?php
						}
					}
				}
			}
			public function get_all_hidden_product_id() {
				if (!TTBM_Global_Function::has_woocommerce()) {
					return [];
				}
				$product_id = [];
				$query = TTBM_Global_Function::query_post_type(TTBM_Function::get_cpt_name());
				foreach ($query->posts as $result) {
					$post_id = $result->ID;
					$product_id[] = TTBM_Global_Function::get_post_info($post_id, 'link_wc_product');
				}
				return array_filter($product_id);
			}
		}
	}
	new TTBM_Hidden_Product();
