<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	/**
	 * Admin page listing tour orders from BOTH sources: the ttbm_custom_order
	 * CPT created by TTBM_Custom_Checkout and real WooCommerce orders, merged
	 * via the free plugin's TTBM_Booking_Normalizer. Tour Bookings → Tour
	 * Orders: stats bar, filters (search / tour / status / gateway / date
	 * range), pagination, CSV export, per-order detail view (WC orders render
	 * fully in-plugin, they are not redirected out) and status management that
	 * keeps the ttbm_booking records in sync for both sources. In the free
	 * plugin, analytics, advanced filters and CSV export render as a locked PRO
	 * preview; the separate PRO renderer supplies their functional versions.
	 */
	if (!class_exists('TTBM_Custom_Orders_Page')) {
		class TTBM_Custom_Orders_Page {
			const SLUG = 'ttbm_custom_orders';
			const PER_PAGE = 20;
			const PRO_URL = 'https://mage-people.com/product/woocommerce-tour-and-travel-booking-manager-pro/';
			private static $index_cache = null;
			private static function is_pro_active() {
				return class_exists('TTBM_Woocommerce_Plugin_Pro');
			}
			public static function init() {
				add_action('admin_menu', array(__CLASS__, 'add_menu_page'), 20);
				add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
				add_action('admin_post_ttbm_order_delete', array(__CLASS__, 'handle_delete'));
				add_action('admin_post_ttbm_orders_export_csv', array(__CLASS__, 'export_csv'));
				add_action('wp_ajax_ttbm_order_add_note', array(__CLASS__, 'ajax_add_note'));
				add_action('wp_ajax_ttbm_order_update_status', array(__CLASS__, 'ajax_update_status'));
			}
			//----------------------------------------------------------------------
			// Activity log / notes — two data sources behind one shared UI:
			//   - custom orders: one combined timeline stored as an array on
			//     _ttbm_order_log (status changes are logged automatically; notes
			//     are admin-authored freeform text), split into two views
			//     (status-change/created vs note).
			//   - WooCommerce orders: the REAL WooCommerce order notes
			//     (wc_get_order_notes()/$order->add_order_note()) — adding a note
			//     here writes straight into WooCommerce's own comment-based notes
			//     table, so it's visible on the native WooCommerce order screen
			//     too, not a separate copy. System-authored notes (added_by
			//     "system" — WC's own status-change/payment messages) are shown
			//     as Activity Log; everything else (admin/customer-authored) as
			//     Notes.
			//----------------------------------------------------------------------
			// Adapts a wc_get_order_note() object onto the same entry shape
			// render_log_entry() already understands for custom orders.
			private static function wc_note_to_entry($note) {
				$is_system = 'system' === $note->added_by;
				return array(
					'type' => $is_system ? 'system' : 'note',
					'note' => $note->content,
					'by' => $is_system ? '' : $note->added_by,
					'time' => $note->date_created ? $note->date_created->date('Y-m-d H:i:s') : '',
				);
			}
			private static function append_log($order_id, $entry) {
				$log = get_post_meta($order_id, '_ttbm_order_log', true);
				$log = is_array($log) ? $log : array();
				$user = wp_get_current_user();
				$log[] = array_merge(array(
					'type' => 'status_change',
					'from' => '',
					'to' => '',
					'note' => '',
					'by' => ($user && $user->exists()) ? $user->display_name : '',
					'time' => current_time('mysql'),
				), $entry);
				update_post_meta($order_id, '_ttbm_order_log', $log);
			}
			private static function get_log($order_id) {
				$log = get_post_meta($order_id, '_ttbm_order_log', true);
				return is_array($log) ? $log : array();
			}
			private static function log_status_change($order_id, $new_status, $old_status) {
				if ($new_status === $old_status) {
					return;
				}
				self::append_log($order_id, array('type' => 'status_change', 'from' => $old_status, 'to' => $new_status));
			}
			public static function ajax_add_note() {
				check_ajax_referer('ttbm_orders_page', 'nonce');
				if (!current_user_can('manage_options')) {
					wp_send_json_error(esc_html__('Permission denied.', 'tour-booking-manager'));
				}
				if (!self::is_pro_active()) {
					wp_send_json_error(esc_html__('Order details and notes require Tour Booking Manager PRO.', 'tour-booking-manager'), 403);
				}
				$order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
				$source = isset($_POST['source']) ? sanitize_key(wp_unslash($_POST['source'])) : TTBM_Booking_Normalizer::SOURCE_CUSTOM;
				$note = isset($_POST['note']) ? sanitize_textarea_field(wp_unslash($_POST['note'])) : '';
				if (!$order_id || '' === trim($note)) {
					wp_send_json_error(esc_html__('Please enter a note.', 'tour-booking-manager'));
				}
				if (TTBM_Booking_Normalizer::SOURCE_WOO === $source) {
					$order = TTBM_Booking_Normalizer::resolve_wc_order($order_id);
					if (!$order) {
						wp_send_json_error(esc_html__('Order not found.', 'tour-booking-manager'));
					}
					// $added_by_user = true attributes the note to the current admin
					// (instead of a generic "WooCommerce" system author) and is what
					// makes this a real WooCommerce order note, not a separate copy.
					$note_id = $order->add_order_note($note, false, true);
					$wc_note = $note_id ? wc_get_order_note($note_id) : null;
					$entry = $wc_note ? self::wc_note_to_entry($wc_note) : array(
						'type' => 'note',
						'note' => $note,
						'by' => wp_get_current_user()->display_name,
						'time' => current_time('mysql'),
					);
				} else {
					if (get_post_type($order_id) !== 'ttbm_custom_order') {
						wp_send_json_error(esc_html__('Order not found.', 'tour-booking-manager'));
					}
					self::append_log($order_id, array('type' => 'note', 'note' => $note));
					$log = self::get_log($order_id);
					$entry = end($log);
				}
				ob_start();
				self::render_log_entry($entry);
				wp_send_json_success(array('html' => ob_get_clean()));
			}
			// Newest-first activity: for custom orders, a synthetic "created" entry
			// plus every logged status change; for WooCommerce orders, WC's own
			// system-authored notes (status changes, payment confirmations, etc).
			private static function activity_entries($order_id, $source = TTBM_Booking_Normalizer::SOURCE_CUSTOM) {
				if (TTBM_Booking_Normalizer::SOURCE_WOO === $source) {
					$order = TTBM_Booking_Normalizer::resolve_wc_order($order_id);
					if (!$order) {
						return array();
					}
					$entries = array();
					foreach (wc_get_order_notes(array('order_id' => $order_id)) as $note) {
						if ('system' === $note->added_by) {
							$entries[] = self::wc_note_to_entry($note);
						}
					}
					return $entries;
				}
				$entries = array_values(array_filter(self::get_log($order_id), function ($e) {
					return isset($e['type']) && $e['type'] !== 'note';
				}));
				array_unshift($entries, array(
					'type' => 'created',
					'from' => '',
					'to' => '',
					'note' => '',
					'gateway' => (string) get_post_meta($order_id, '_ttbm_payment_gateway', true),
					'by' => '',
					'time' => get_post_field('post_date', $order_id),
				));
				return array_reverse($entries);
			}
			// Newest-first admin-authored notes (custom orders: from _ttbm_order_log;
			// WooCommerce orders: real order notes not authored by "system").
			private static function notes_entries($order_id, $source = TTBM_Booking_Normalizer::SOURCE_CUSTOM) {
				if (TTBM_Booking_Normalizer::SOURCE_WOO === $source) {
					$order = TTBM_Booking_Normalizer::resolve_wc_order($order_id);
					if (!$order) {
						return array();
					}
					$entries = array();
					foreach (wc_get_order_notes(array('order_id' => $order_id)) as $note) {
						if ('system' !== $note->added_by) {
							$entries[] = self::wc_note_to_entry($note);
						}
					}
					return $entries;
				}
				return array_reverse(array_values(array_filter(self::get_log($order_id), function ($e) {
					return isset($e['type']) && $e['type'] === 'note';
				})));
			}
			private static function render_log_entry($entry) {
				$type = isset($entry['type']) ? $entry['type'] : 'status_change';
				$when = !empty($entry['time']) ? mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $entry['time']) : '';
				?>
				<div class="ttbm-co-log-entry">
					<?php if ('note' === $type) : ?>
						<p class="ttbm-co-log-text"><?php echo nl2br(esc_html($entry['note'])); ?></p>
					<?php elseif ('system' === $type) : ?>
						<p class="ttbm-co-log-text">
							<span class="dashicons dashicons-update"></span>
							<?php echo nl2br(esc_html($entry['note'])); ?>
						</p>
					<?php elseif ('created' === $type) : ?>
						<p class="ttbm-co-log-text">
							<span class="dashicons dashicons-cart"></span>
							<?php if (!empty($entry['gateway'])) : ?>
								<?php /* translators: %s: payment gateway label */ printf(esc_html__('Order placed via %s', 'tour-booking-manager'), esc_html(self::gateway_label(array('source' => TTBM_Booking_Normalizer::SOURCE_CUSTOM, 'gateway' => $entry['gateway'])))); ?>
							<?php else : ?>
								<?php esc_html_e('Order placed', 'tour-booking-manager'); ?>
							<?php endif; ?>
						</p>
					<?php else : ?>
						<p class="ttbm-co-log-text">
							<span class="dashicons dashicons-update"></span>
							<?php
							$to_label = TTBM_Booking_Normalizer::status_label(isset($entry['to']) ? $entry['to'] : '');
							if (!empty($entry['from'])) {
								$from_label = TTBM_Booking_Normalizer::status_label($entry['from']);
								/* translators: 1: previous status, 2: new status */
								printf(esc_html__('Status changed from %1$s to %2$s', 'tour-booking-manager'), '<strong>' . esc_html($from_label) . '</strong>', '<strong>' . esc_html($to_label) . '</strong>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- both args esc_html()'d above
							} else {
								/* translators: %s: new status */
								printf(esc_html__('Status set to %s', 'tour-booking-manager'), '<strong>' . esc_html($to_label) . '</strong>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- arg esc_html()'d above
							}
							?>
						</p>
					<?php endif; ?>
					<span class="ttbm-co-log-meta"><?php echo esc_html(isset($entry['by']) ? $entry['by'] : ''); ?><?php echo (!empty($entry['by']) && $when) ? ' &middot; ' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static separator string ?><?php echo esc_html($when); ?></span>
				</div>
				<?php
			}
			private static function render_activity_log($order_id, $source = TTBM_Booking_Normalizer::SOURCE_CUSTOM) {
				$entries = self::activity_entries($order_id, $source);
				if (empty($entries)) {
					echo '<p class="ttbm-co-log-empty">' . esc_html__('No activity yet.', 'tour-booking-manager') . '</p>';
					return;
				}
				foreach ($entries as $entry) {
					self::render_log_entry($entry);
				}
			}
			private static function render_notes_list($order_id, $source = TTBM_Booking_Normalizer::SOURCE_CUSTOM) {
				$notes = self::notes_entries($order_id, $source);
				if (empty($notes)) {
					echo '<p class="ttbm-co-log-empty">' . esc_html__('No notes yet.', 'tour-booking-manager') . '</p>';
					return;
				}
				foreach ($notes as $entry) {
					self::render_log_entry($entry);
				}
			}
			public static function add_menu_page() {
				add_submenu_page(
					'edit.php?post_type=' . TTBM_Function::get_cpt_name(),
					esc_html__('Tour Booking', 'tour-booking-manager'),
					esc_html__('Tour Booking', 'tour-booking-manager'),
					'manage_options',
					self::SLUG,
					array(__CLASS__, 'render_page')
				);
			}
			public static function enqueue_assets($hook) {
				if (strpos($hook, self::SLUG) === false) {
					return;
				}
				wp_enqueue_style('ttbm-orders-page', TTBM_PLUGIN_URL . '/assets/admin/ttbm-orders-page.css', array(), filemtime(TTBM_PLUGIN_DIR . '/assets/admin/ttbm-orders-page.css'));
				wp_enqueue_script('ttbm-orders-page', TTBM_PLUGIN_URL . '/assets/admin/ttbm-orders-page.js', array('jquery'), filemtime(TTBM_PLUGIN_DIR . '/assets/admin/ttbm-orders-page.js'), true);
				// CPT-parent submenu pages can leave $GLOBALS['title'] null, which
				// trips strip_tags(null) deprecations in admin-header.php on PHP 8+.
				if (empty($GLOBALS['title'])) {
					$GLOBALS['title'] = esc_html__('Tour Booking', 'tour-booking-manager');
				}
			}
			// Native ttbm_custom_order statuses only — the base of status_choices()'s
			// merged list, and used to validate custom-order status changes.
			public static function get_order_statuses() {
				return array(
					'pending' => esc_html__('Pending', 'tour-booking-manager'),
					'processing' => esc_html__('Processing', 'tour-booking-manager'),
					'on-hold' => esc_html__('On hold', 'tour-booking-manager'),
					'publish' => esc_html__('Completed', 'tour-booking-manager'),
					'cancelled' => esc_html__('Cancelled', 'tour-booking-manager'),
					'refunded' => esc_html__('Refunded', 'tour-booking-manager'),
					'failed' => esc_html__('Failed', 'tour-booking-manager'),
				);
			}
			// Shared, source-tolerant status list for the list filter dropdown.
			private static function get_filter_statuses() {
				$labels = array();
				foreach (TTBM_Booking_Normalizer::status_map() as $slug => $meta) {
					if (in_array($slug, array('draft', 'trash'), true)) {
						continue;
					}
					$labels[$slug] = $meta['label'];
				}
				return $labels;
			}
			// "woocommerce" is a synthetic option meaning "any WooCommerce order" —
			// WC's own gateway ids live in a different namespace than the native
			// paypal/stripe/offline ids, so we don't pretend to cross-match them.
			private static function get_gateway_filter_options() {
				$options = array('woocommerce' => esc_html__('WooCommerce (any method)', 'tour-booking-manager'));
				foreach (TTBM_Payment_Gateway_Manager::get_instance()->get_all_gateways() as $gateway_id => $gateway) {
					$options[$gateway_id] = $gateway->get_title();
				}
				$options['free'] = esc_html__('Free', 'tour-booking-manager');
				return $options;
			}
			//----------------------------------------------------------------------
			// Data
			//----------------------------------------------------------------------
			private static function sanitize_filters() {
				// Analytics, filtering and CSV export are supplied by Tour Pro. The
				// free list deliberately ignores crafted filter query strings too,
				// while retaining pagination for the unlocked order table below.
				return array(
					'search' => '',
					'tour' => 0,
					'status' => '',
					'gateway' => '',
					'date_from' => '',
					'date_to' => '',
					'paged' => isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1,
				);
			}
			// Full merged index, cheap (no WC_Order objects) — cached per-request
			// since render_page(), get_stats() and export_csv() all need it.
			private static function get_index() {
				if (self::$index_cache === null) {
					self::$index_cache = TTBM_Booking_Normalizer::query_index();
				}
				return self::$index_cache;
			}
			private static function filter_rows($rows, $filters) {
				$from = $filters['date_from'] ? strtotime($filters['date_from'] . ' 00:00:00') : null;
				$to = $filters['date_to'] ? strtotime($filters['date_to'] . ' 23:59:59') : null;
				return array_values(array_filter($rows, function ($row) use ($filters, $from, $to) {
					if ($filters['tour'] && (int) $row['tour_id'] !== (int) $filters['tour']) {
						return false;
					}
					if ($filters['status'] && TTBM_Booking_Normalizer::normalize_status($row['status']) !== $filters['status']) {
						return false;
					}
					if ($filters['gateway']) {
						if ($filters['gateway'] === 'woocommerce') {
							if ($row['source'] !== TTBM_Booking_Normalizer::SOURCE_WOO) {
								return false;
							}
						} elseif ($row['source'] !== TTBM_Booking_Normalizer::SOURCE_CUSTOM || $row['gateway'] !== $filters['gateway']) {
							return false;
						}
					}
					if ($filters['search']) {
						if (is_numeric($filters['search'])) {
							if ((int) $row['id'] !== (int) $filters['search']) {
								return false;
							}
						} else {
							$needle = strtolower($filters['search']);
							$haystack = strtolower($row['customer_name'] . ' ' . $row['customer_email']);
							if (strpos($haystack, $needle) === false) {
								return false;
							}
						}
					}
					$placed = strtotime($row['placed_at']);
					if ($from && $placed < $from) {
						return false;
					}
					if ($to && $placed > $to) {
						return false;
					}
					return true;
				}));
			}
			private static function get_stats() {
				$stats = array('total' => 0, 'pending' => 0, 'paid' => 0, 'cancelled' => 0, 'revenue' => 0.0);
				foreach (self::get_index() as $row) {
					$stats['total']++;
					$slug = TTBM_Booking_Normalizer::normalize_status($row['status']);
					if (TTBM_Booking_Normalizer::is_ticket_ready($slug)) {
						$stats['paid']++;
						$stats['revenue'] += (float) $row['total'];
					} elseif (in_array($slug, array('cancelled', 'refunded', 'failed'), true)) {
						$stats['cancelled']++;
					} else {
						$stats['pending']++;
					}
				}
				return $stats;
			}
			private static function get_all_tours() {
				return get_posts(array(
					'post_type' => TTBM_Function::get_cpt_name(),
					'posts_per_page' => -1,
					'orderby' => 'title',
					'order' => 'ASC',
				));
			}
			private static function format_price($amount) {
				return number_format_i18n((float) $amount, 2) . ' ' . TTBM_Custom_Checkout::currency_code();
			}
			// PDF ticket download (TTBM_Pro_Pdf) needs the MagePeople PDF Support
			// plugin (mPDF wrapper). get_order_info() there queries ttbm_booking
			// posts by their ttbm_order_id meta, which is set the same way for
			// both WooCommerce and custom-payment orders, so one URL builder
			// works for both detail views.
			private static function pdf_ready() {
				return function_exists('is_plugin_active') && is_plugin_active('magepeople-pdf-support-master/mage-pdf.php') && class_exists('TTBM_Pro_Pdf');
			}
			private static function render_detail_header_actions($order_id, $base_url, $status, $source, $woo_edit_url = '') {
				?>
				<div class="ttbm-co-header-actions">
					<?php if ($woo_edit_url) : ?>
						<a href="<?php echo esc_url($woo_edit_url); ?>" target="_blank" rel="noopener" class="ttbm-co-btn ttbm-co-btn-outline"><span class="dashicons dashicons-external"></span><?php esc_html_e('Open in WooCommerce', 'tour-booking-manager'); ?></a>
					<?php endif; ?>
					<?php if (self::pdf_ready()) : ?>
						<a href="<?php echo esc_url(TTBM_Pro_Pdf::get_pdf_url(array('order_id' => $order_id))); ?>" class="ttbm-co-btn ttbm-co-btn-outline"><span class="dashicons dashicons-download"></span><?php esc_html_e('Download PDF Ticket', 'tour-booking-manager'); ?></a>
					<?php else : ?>
						<span class="ttbm-co-btn ttbm-co-btn-outline is-disabled" title="<?php esc_attr_e('Install the MagePeople PDF Support plugin to enable PDF export.', 'tour-booking-manager'); ?>"><span class="dashicons dashicons-download"></span><?php esc_html_e('Download PDF Ticket', 'tour-booking-manager'); ?></span>
					<?php endif; ?>
					<button type="button" class="ttbm-co-btn ttbm-co-btn-outline" onclick="window.print()"><span class="dashicons dashicons-printer"></span><?php esc_html_e('Print', 'tour-booking-manager'); ?></button>
					<button type="button" class="ttbm-co-btn ttbm-co-btn-outline ttbm-co-change-status-btn" data-id="<?php echo esc_attr($order_id); ?>" data-ref="<?php echo esc_attr('#' . $order_id); ?>" data-status="<?php echo esc_attr(TTBM_Booking_Normalizer::normalize_status($status)); ?>" data-source="<?php echo esc_attr($source); ?>">
						<span class="dashicons dashicons-update"></span><?php esc_html_e('Change Status', 'tour-booking-manager'); ?>
					</button>
					<a href="<?php echo esc_url($base_url); ?>" class="ttbm-co-btn ttbm-co-btn-outline"><span class="dashicons dashicons-arrow-left-alt2"></span><?php esc_html_e('Back to Orders', 'tour-booking-manager'); ?></a>
				</div>
				<?php
			}
			private static function gateway_label($row) {
				if ($row['source'] === TTBM_Booking_Normalizer::SOURCE_WOO) {
					return $row['gateway'] !== '' ? $row['gateway'] : esc_html__('WooCommerce', 'tour-booking-manager');
				}
				$gateway_id = $row['gateway'];
				if ($gateway_id === 'free' || $gateway_id === '') {
					return esc_html__('Free', 'tour-booking-manager');
				}
				$gateway = TTBM_Payment_Gateway_Manager::get_instance()->get_gateway($gateway_id);
				return $gateway ? $gateway->get_title() : ucfirst($gateway_id);
			}
			//----------------------------------------------------------------------
			// Status update — shared modal (list row + detail page), AJAX
			//----------------------------------------------------------------------
			/**
			 * One merged, normalized status list for the modal (e.g. custom orders
			 * use post_status "publish" for what WooCommerce calls "completed" —
			 * the modal always deals in normalized slugs and ajax_update_status()
			 * translates back to each source's literal value on save).
			 *
			 * @return array<string,string> normalized slug => label
			 */
			private static function status_choices() {
				$choices = array();
				foreach (self::get_order_statuses() as $slug => $label) {
					$norm = ('publish' === $slug) ? 'completed' : $slug;
					$choices[$norm] = $label;
				}
				if (function_exists('wc_get_order_statuses')) {
					foreach (wc_get_order_statuses() as $key => $label) {
						$bare = preg_replace('/^wc-/', '', $key);
						if (!isset($choices[$bare])) {
							$choices[$bare] = $label;
						}
					}
				}
				return $choices;
			}
			/**
			 * Which source(s) a normalized status may be assigned to. Tour's own
			 * 7 custom-order statuses happen to line up 1:1 with WooCommerce's core
			 * statuses, so almost everything is "both" — this only diverges when a
			 * site registers extra WooCommerce-only statuses (subscriptions, etc).
			 *
			 * @return string both | custom | woo
			 */
			private static function status_source($slug) {
				$custom_slugs = array('pending', 'processing', 'on-hold', 'completed', 'cancelled', 'refunded', 'failed');
				$woo_slugs = array();
				if (function_exists('wc_get_order_statuses')) {
					foreach (array_keys(wc_get_order_statuses()) as $key) {
						$woo_slugs[] = preg_replace('/^wc-/', '', $key);
					}
				}
				$in_custom = in_array($slug, $custom_slugs, true);
				$in_woo = in_array($slug, $woo_slugs, true);
				if ($in_custom && $in_woo) {
					return 'both';
				}
				return $in_woo ? TTBM_Booking_Normalizer::SOURCE_WOO : TTBM_Booking_Normalizer::SOURCE_CUSTOM;
			}
			/** Single shared "Change Status" modal reused by the list rows and both detail pages. */
			private static function render_status_modal() {
				?>
				<div id="ttbm-co-status-modal" class="ttbm-co-modal" style="display:none;" data-nonce="<?php echo esc_attr(wp_create_nonce('ttbm_orders_page')); ?>">
					<div class="ttbm-co-modal-content ttbm-co-modal-status">
						<div class="ttbm-co-modal-header">
							<h2><span class="dashicons dashicons-update"></span><?php esc_html_e('Change Order Status', 'tour-booking-manager'); ?></h2>
							<span class="ttbm-co-modal-close" role="button" aria-label="<?php esc_attr_e('Close', 'tour-booking-manager'); ?>">&times;</span>
						</div>
						<div class="ttbm-co-modal-body">
							<input type="hidden" id="ttbm-co-status-modal-id" value="">
							<input type="hidden" id="ttbm-co-status-modal-source" value="">
							<p class="ttbm-co-modal-subtitle"><?php esc_html_e('Order', 'tour-booking-manager'); ?> <strong id="ttbm-co-status-modal-ref">#0</strong></p>
							<div class="ttbm-co-status-options" id="ttbm-co-status-modal-options" role="radiogroup" aria-label="<?php esc_attr_e('Status', 'tour-booking-manager'); ?>">
								<?php foreach (self::status_choices() as $slug => $label) : ?>
									<label class="ttbm-co-status-option is-<?php echo esc_attr(TTBM_Booking_Normalizer::status_class($slug)); ?>" data-source="<?php echo esc_attr(self::status_source($slug)); ?>">
										<input type="radio" name="ttbm_status_modal_option" value="<?php echo esc_attr($slug); ?>">
										<span class="ttbm-co-status-option-dot"></span>
										<span class="ttbm-co-status-option-label"><?php echo esc_html($label); ?></span>
										<span class="dashicons dashicons-yes-alt ttbm-co-status-option-check"></span>
									</label>
								<?php endforeach; ?>
							</div>
							<div class="ttbm-co-modal-actions">
								<button type="button" class="ttbm-co-btn ttbm-co-btn-outline ttbm-co-modal-close"><?php esc_html_e('Cancel', 'tour-booking-manager'); ?></button>
								<button type="button" id="ttbm-co-status-modal-save" class="ttbm-co-btn ttbm-co-btn-primary">
									<span class="dashicons dashicons-saved"></span>
									<span class="ttbm-co-btn-text"><?php esc_html_e('Save Status', 'tour-booking-manager'); ?></span>
								</button>
							</div>
						</div>
					</div>
				</div>
				<?php
			}
			/**
			 * Applies one status change, source-aware:
			 *   - custom orders: same finalize/propagate/log logic the old admin-post
			 *     handler used, translating the modal's normalized slug ("completed")
			 *     back to the literal ttbm_custom_order post_status ("publish").
			 *   - WooCommerce orders: $order->update_status() alone is enough to keep
			 *     everything else in sync — woocommerce_order_status_changed already
			 *     fires WC's own hooks/emails, and TTBM_Woocommerce::order_status_changed()
			 *     (free plugin) already mirrors the new status onto every linked
			 *     ttbm_booking/ttbm_service_booking record via ttbm_wc_order_status_change.
			 */
			public static function ajax_update_status() {
				check_ajax_referer('ttbm_orders_page', 'nonce');
				if (!current_user_can('manage_options')) {
					wp_send_json_error(array('message' => esc_html__('Unauthorized', 'tour-booking-manager')), 403);
				}
				if (!self::is_pro_active()) {
					wp_send_json_error(array('message' => esc_html__('Changing an order status requires Tour Booking Manager PRO.', 'tour-booking-manager')), 403);
				}
				$order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
				$source = isset($_POST['source']) ? sanitize_key(wp_unslash($_POST['source'])) : '';
				$new_status = isset($_POST['new_status']) ? sanitize_key(wp_unslash($_POST['new_status'])) : '';
				$choices = self::status_choices();
				if (!$order_id || !in_array($source, array(TTBM_Booking_Normalizer::SOURCE_CUSTOM, TTBM_Booking_Normalizer::SOURCE_WOO), true) || !isset($choices[$new_status])) {
					wp_send_json_error(array('message' => esc_html__('Invalid order or status.', 'tour-booking-manager')));
				}
				$allowed_source = self::status_source($new_status);
				if ('both' !== $allowed_source && $allowed_source !== $source) {
					wp_send_json_error(array('message' => esc_html__('That status is not valid for this order type.', 'tour-booking-manager')));
				}
				$log_html = '';
				if (TTBM_Booking_Normalizer::SOURCE_CUSTOM === $source) {
					if (get_post_type($order_id) !== 'ttbm_custom_order') {
						wp_send_json_error(array('message' => esc_html__('Order not found.', 'tour-booking-manager')));
					}
					$literal_status = ('completed' === $new_status) ? 'publish' : $new_status;
					$old_status = get_post_status($order_id);
					$booking_ids = get_post_meta($order_id, '_ttbm_booking_ids', true);
					if (empty($booking_ids) && in_array($literal_status, array('pending', 'processing', 'on-hold', 'publish'), true)) {
						// Order was never finalized (e.g. gateway failure) — create the
						// booking records now, exactly as a successful checkout would.
						$gateway_id = (string) get_post_meta($order_id, '_ttbm_payment_gateway', true);
						if (class_exists('TTBM_Custom_Checkout')) {
							TTBM_Custom_Checkout::instance()->finalize_order($order_id, $literal_status, $gateway_id ?: 'offline');
						}
					} else {
						wp_update_post(array('ID' => $order_id, 'post_status' => $literal_status));
						TTBM_Custom_Checkout::propagate_status($order_id, TTBM_Custom_Order_CPT::booking_status($literal_status));
						do_action('ttbm_custom_order_status_changed', $order_id, $literal_status, $old_status);
					}
					self::log_status_change($order_id, $literal_status, $old_status);
					if ($old_status !== $literal_status) {
						$log = self::get_log($order_id);
						$last = end($log);
						if ($last) {
							ob_start();
							self::render_log_entry($last);
							$log_html = ob_get_clean();
						}
					}
				} else {
					$order = TTBM_Booking_Normalizer::resolve_wc_order($order_id);
					if (!$order) {
						wp_send_json_error(array('message' => esc_html__('Order not found.', 'tour-booking-manager')));
					}
					$order->update_status($new_status, esc_html__('Status changed via Tour Booking admin.', 'tour-booking-manager'));
					// update_status() just wrote a new system order note documenting the
					// change — pull it back so the Activity Log can prepend it live too.
					$notes = wc_get_order_notes(array('order_id' => $order_id, 'limit' => 1));
					if (!empty($notes) && 'system' === $notes[0]->added_by) {
						ob_start();
						self::render_log_entry(self::wc_note_to_entry($notes[0]));
						$log_html = ob_get_clean();
					}
				}
				wp_send_json_success(array(
					'status' => TTBM_Booking_Normalizer::normalize_status($new_status),
					'label' => self::status_choices()[$new_status],
					'log_entry' => $log_html,
				));
			}
			//----------------------------------------------------------------------
			// Delete (admin_post)
			//----------------------------------------------------------------------
			public static function handle_delete() {
				if (!current_user_can('manage_options')) {
					wp_die(esc_html__('Permission denied.', 'tour-booking-manager'));
				}
				check_admin_referer('ttbm_order_delete');
				$order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
				$source = isset($_GET['source']) ? sanitize_key(wp_unslash($_GET['source'])) : '';
				if ($source === TTBM_Booking_Normalizer::SOURCE_WOO) {
					$order = TTBM_Booking_Normalizer::resolve_wc_order($order_id);
					if (!$order) {
						wp_die(esc_html__('Order not found.', 'tour-booking-manager'));
					}
					$order->delete(false); // trash, not permanent delete
				} elseif ($source === TTBM_Booking_Normalizer::SOURCE_CUSTOM) {
					if (get_post_type($order_id) !== 'ttbm_custom_order') {
						wp_die(esc_html__('Order not found.', 'tour-booking-manager'));
					}
					wp_trash_post($order_id);
				} else {
					wp_die(esc_html__('Invalid order source.', 'tour-booking-manager'));
				}
				wp_safe_redirect(add_query_arg(array(
					'post_type' => TTBM_Function::get_cpt_name(),
					'page' => self::SLUG,
					'deleted' => 1,
				), admin_url('edit.php')));
				exit;
			}
			//----------------------------------------------------------------------
			// CSV export (admin_post)
			//----------------------------------------------------------------------
			public static function export_csv() {
				if (!current_user_can('manage_options')) {
					wp_die(esc_html__('Permission denied.', 'tour-booking-manager'));
				}
				check_admin_referer('ttbm_orders_export');
				wp_die(
					esc_html__('CSV export is available with Tour Booking Manager PRO.', 'tour-booking-manager'),
					esc_html__('PRO feature', 'tour-booking-manager'),
					array('response' => 403)
				);
			}
			//----------------------------------------------------------------------
			// Page rendering
			//----------------------------------------------------------------------
			public static function render_page() {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- routing only
				if (self::is_pro_active() && isset($_GET['action'], $_GET['order']) && $_GET['action'] === 'view') {
					$source = isset($_GET['source']) ? sanitize_key(wp_unslash($_GET['source'])) : TTBM_Booking_Normalizer::SOURCE_CUSTOM; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					self::render_order_detail(absint($_GET['order']), $source); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					return;
				}
				$filters = self::sanitize_filters();
				$all_rows = self::filter_rows(self::get_index(), $filters);
				$total_rows = count($all_rows);
				$total_pages = max(1, (int) ceil($total_rows / self::PER_PAGE));
				$filters['paged'] = min($filters['paged'], $total_pages);
				$slice = array_slice($all_rows, (max(1, $filters['paged']) - 1) * self::PER_PAGE, self::PER_PAGE);
				$rows = TTBM_Booking_Normalizer::hydrate($slice);
				$base_url = add_query_arg(array('post_type' => TTBM_Function::get_cpt_name(), 'page' => self::SLUG), admin_url('edit.php'));
				?>
				<div class="wrap ttbm-co-wrap">
					<div class="ttbm-co-header">
						<div>
							<h1 class="ttbm-co-title"><?php esc_html_e('Tour Booking', 'tour-booking-manager'); ?></h1>
							<p class="ttbm-co-subtitle"><?php esc_html_e('All tour bookings — placed through WooCommerce or the custom payment gateways (PayPal, Stripe, Offline).', 'tour-booking-manager'); ?></p>
						</div>
					</div>
					<?php if (isset($_GET['deleted'])) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
						<div class="notice notice-success is-dismissible"><p><?php esc_html_e('Order moved to trash.', 'tour-booking-manager'); ?></p></div>
					<?php endif; ?>
					<div class="ttbm-co-pro-locked">
						<div class="ttbm-co-pro-preview" aria-hidden="true" inert>
					<div class="ttbm-co-stats">
						<div class="ttbm-co-stat">
							<div class="ttbm-co-stat-body">
								<span class="ttbm-co-stat-icon dashicons dashicons-list-view"></span>
								<div class="ttbm-co-stat-info">
									<span class="ttbm-co-stat-num">&bull;&bull;&bull;</span>
									<span class="ttbm-co-stat-label"><?php esc_html_e('Total orders', 'tour-booking-manager'); ?></span>
								</div>
							</div>
						</div>
						<div class="ttbm-co-stat is-pending">
							<div class="ttbm-co-stat-body">
								<span class="ttbm-co-stat-icon dashicons dashicons-clock"></span>
								<div class="ttbm-co-stat-info">
									<span class="ttbm-co-stat-num">&bull;&bull;&bull;</span>
									<span class="ttbm-co-stat-label"><?php esc_html_e('Pending / On hold', 'tour-booking-manager'); ?></span>
								</div>
							</div>
						</div>
						<div class="ttbm-co-stat is-paid">
							<div class="ttbm-co-stat-body">
								<span class="ttbm-co-stat-icon dashicons dashicons-yes-alt"></span>
								<div class="ttbm-co-stat-info">
									<span class="ttbm-co-stat-num">&bull;&bull;&bull;</span>
									<span class="ttbm-co-stat-label"><?php esc_html_e('Paid', 'tour-booking-manager'); ?></span>
								</div>
							</div>
						</div>
						<div class="ttbm-co-stat is-cancelled">
							<div class="ttbm-co-stat-body">
								<span class="ttbm-co-stat-icon dashicons dashicons-dismiss"></span>
								<div class="ttbm-co-stat-info">
									<span class="ttbm-co-stat-num">&bull;&bull;&bull;</span>
									<span class="ttbm-co-stat-label"><?php esc_html_e('Cancelled / Failed', 'tour-booking-manager'); ?></span>
								</div>
							</div>
						</div>
						<div class="ttbm-co-stat is-revenue">
							<div class="ttbm-co-stat-body">
								<span class="ttbm-co-stat-icon dashicons dashicons-money-alt"></span>
								<div class="ttbm-co-stat-info">
									<span class="ttbm-co-stat-num">&bull;&bull;&bull;</span>
									<span class="ttbm-co-stat-label"><?php esc_html_e('Revenue (paid)', 'tour-booking-manager'); ?></span>
								</div>
							</div>
						</div>
					</div>
					<div class="ttbm-co-filter-panel">
						<div class="ttbm-co-filter-panel-header">
							<span class="dashicons dashicons-filter"></span>
							<span><?php esc_html_e('Filters', 'tour-booking-manager'); ?></span>
							<span class="ttbm-co-filter-toggle"><span class="dashicons dashicons-arrow-up-alt2"></span></span>
						</div>
						<div class="ttbm-co-filter-body">
							<div class="ttbm-co-filter-row">
							<div class="ttbm-co-filter-grid">
								<div class="ttbm-co-filter-field ttbm-co-filter-search">
									<label><?php esc_html_e('Search', 'tour-booking-manager'); ?></label>
									<div class="ttbm-co-input-icon-wrap">
										<span class="dashicons dashicons-search"></span>
										<input type="search" value="" placeholder="<?php esc_attr_e('Order #, name or email…', 'tour-booking-manager'); ?>" disabled>
									</div>
								</div>
								<div class="ttbm-co-filter-field">
									<label><?php echo esc_html(TTBM_Function::get_name()); ?></label>
									<select disabled>
										<option value=""><?php esc_html_e('All tours', 'tour-booking-manager'); ?></option>
									</select>
								</div>
								<div class="ttbm-co-filter-field">
									<label><?php esc_html_e('Status', 'tour-booking-manager'); ?></label>
									<select disabled>
										<option value=""><?php esc_html_e('All statuses', 'tour-booking-manager'); ?></option>
									</select>
								</div>
								<div class="ttbm-co-filter-field">
									<label><?php esc_html_e('Payment method', 'tour-booking-manager'); ?></label>
									<select disabled>
										<option value=""><?php esc_html_e('All payment methods', 'tour-booking-manager'); ?></option>
									</select>
								</div>
								<div class="ttbm-co-filter-field ttbm-co-filter-date">
									<label><?php esc_html_e('From date', 'tour-booking-manager'); ?></label>
									<input type="date" value="" disabled>
								</div>
								<div class="ttbm-co-filter-field ttbm-co-filter-date">
									<label><?php esc_html_e('To date', 'tour-booking-manager'); ?></label>
									<input type="date" value="" disabled>
								</div>
							</div>
							<div class="ttbm-co-filter-actions">
								<span class="ttbm-co-btn ttbm-co-btn-outline"><span class="dashicons dashicons-download"></span> <?php esc_html_e('Export CSV', 'tour-booking-manager'); ?></span>
								<span class="ttbm-co-btn ttbm-co-btn-ghost"><?php esc_html_e('Reset', 'tour-booking-manager'); ?></span>
								<span class="ttbm-co-btn ttbm-co-btn-primary"><span class="dashicons dashicons-yes"></span> <?php esc_html_e('Apply Filters', 'tour-booking-manager'); ?></span>
							</div>
							</div>
						</div>
					</div>
						</div>
						<div class="ttbm-co-pro-overlay">
							<div class="ttbm-co-pro-card">
								<span class="ttbm-co-pro-badge"><span class="dashicons dashicons-lock"></span><?php esc_html_e('PRO', 'tour-booking-manager'); ?></span>
								<h2><?php esc_html_e('Unlock booking analytics and advanced filters', 'tour-booking-manager'); ?></h2>
								<p><?php esc_html_e('View revenue insights, search and filter every booking, and export your results to CSV with Tour Booking Manager PRO.', 'tour-booking-manager'); ?></p>
								<a href="<?php echo esc_url(self::PRO_URL); ?>" target="_blank" rel="noopener noreferrer" class="ttbm-co-pro-cta">
									<span class="dashicons dashicons-star-filled"></span>
									<?php esc_html_e('Upgrade to PRO', 'tour-booking-manager'); ?>
								</a>
							</div>
						</div>
					</div>
					<div class="ttbm-co-table-wrap">
						<div class="ttbm-co-table-toolbar">
							<span class="ttbm-co-result-count">
								<?php
								/* translators: %d: number of matching orders */
								printf(esc_html(_n('%d order', '%d orders', $total_rows, 'tour-booking-manager')), (int) $total_rows);
								?>
							</span>
						</div>
						<table class="ttbm-co-table">
							<thead>
								<tr>
									<th><?php esc_html_e('Order', 'tour-booking-manager'); ?></th>
									<th><?php echo esc_html(TTBM_Function::get_name()); ?></th>
									<th><?php esc_html_e('Customer', 'tour-booking-manager'); ?></th>
									<th><?php echo esc_html(TTBM_Function::get_name()); ?> <?php esc_html_e('Date', 'tour-booking-manager'); ?></th>
									<th><?php esc_html_e('Tickets', 'tour-booking-manager'); ?></th>
									<th class="ttbm-co-col-total"><?php esc_html_e('Total', 'tour-booking-manager'); ?></th>
									<th><?php esc_html_e('Payment', 'tour-booking-manager'); ?></th>
									<th><?php esc_html_e('Status', 'tour-booking-manager'); ?></th>
									<th><?php esc_html_e('Placed', 'tour-booking-manager'); ?></th>
									<th class="ttbm-co-col-actions"></th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($rows)) : ?>
									<tr><td colspan="10" class="ttbm-co-empty"><span class="dashicons dashicons-tickets-alt"></span><p><?php esc_html_e('No orders found.', 'tour-booking-manager'); ?></p></td></tr>
								<?php endif; ?>
								<?php foreach ($rows as $row) : ?>
									<?php self::render_table_row($row, $base_url); ?>
								<?php endforeach; ?>
							</tbody>
						</table>
						<?php self::render_pagination($filters, $total_pages, $base_url); ?>
					</div>
				</div>
				<?php if (self::is_pro_active()) : ?>
					<?php self::render_status_modal(); ?>
				<?php endif; ?>
				<?php
			}
			private static function render_table_row($row, $base_url) {
				$data_format = TTBM_Global_Function::check_time_exit_date($row['tour_date']) ? 'full' : 'date';
				$view_url = add_query_arg(array('action' => 'view', 'order' => $row['id'], 'source' => $row['source']), $base_url);
				$is_woo = $row['source'] === TTBM_Booking_Normalizer::SOURCE_WOO;
				$is_pro = self::is_pro_active();
				?>
				<tr data-row-id="<?php echo esc_attr($row['id']); ?>">
					<td data-label="<?php esc_attr_e('Order', 'tour-booking-manager'); ?>" class="ttbm-co-col-id">
						<?php if ($is_pro) : ?>
							<a href="<?php echo esc_url($view_url); ?>" class="ttbm-co-order-link">#<?php echo esc_html($row['id']); ?></a>
						<?php else : ?>
							<span class="ttbm-co-order-link">#<?php echo esc_html($row['id']); ?></span>
						<?php endif; ?>
						<span class="ttbm-co-source ttbm-co-source-<?php echo esc_attr($row['source']); ?>"><?php echo esc_html(TTBM_Booking_Normalizer::source_label($row['source'])); ?></span>
					</td>
					<td data-label="<?php echo esc_attr(TTBM_Function::get_name()); ?>"><a href="<?php echo esc_url(get_edit_post_link($row['tour_id'])); ?>"><?php echo esc_html(get_the_title($row['tour_id'])); ?></a></td>
					<td data-label="<?php esc_attr_e('Customer', 'tour-booking-manager'); ?>">
						<?php echo esc_html($row['customer_name']); ?><br>
						<span class="ttbm-co-muted"><?php echo esc_html($row['customer_email']); ?></span>
					</td>
					<td data-label="<?php echo esc_attr(TTBM_Function::get_name()); ?> <?php esc_attr_e('Date', 'tour-booking-manager'); ?>" class="ttbm-co-col-date"><?php echo $row['tour_date'] ? esc_html(TTBM_Global_Function::date_format($row['tour_date'], $data_format)) : '—'; ?></td>
					<td data-label="<?php esc_attr_e('Tickets', 'tour-booking-manager'); ?>"><?php echo esc_html($row['ticket_qty']); ?></td>
					<td data-label="<?php esc_attr_e('Total', 'tour-booking-manager'); ?>" class="ttbm-co-col-total"><?php echo esc_html(self::format_price($row['total'])); ?></td>
					<td data-label="<?php esc_attr_e('Payment', 'tour-booking-manager'); ?>"><span class="ttbm-co-gateway-label"><?php echo esc_html(self::gateway_label($row)); ?></span></td>
					<td data-label="<?php esc_attr_e('Status', 'tour-booking-manager'); ?>"><span class="ttbm-co-pill is-<?php echo esc_attr(TTBM_Booking_Normalizer::status_class($row['status'])); ?>"><?php echo esc_html(TTBM_Booking_Normalizer::status_label($row['status'])); ?></span></td>
					<td data-label="<?php esc_attr_e('Placed', 'tour-booking-manager'); ?>" class="ttbm-co-col-date"><?php echo esc_html(mysql2date('M j, Y g:i a', $row['placed_at'])); ?></td>
					<td class="ttbm-co-col-actions">
						<?php
						$delete_url = wp_nonce_url(add_query_arg(array(
							'action' => 'ttbm_order_delete',
							'order_id' => $row['id'],
							'source' => $row['source'],
						), admin_url('admin-post.php')), 'ttbm_order_delete');
						?>
						<div class="ttbm-co-action-dropdown">
							<button type="button" class="ttbm-co-kebab-btn" aria-label="<?php esc_attr_e('Actions', 'tour-booking-manager'); ?>" aria-haspopup="menu" aria-expanded="false"><span class="dashicons dashicons-ellipsis"></span></button>
							<div class="ttbm-co-action-menu" role="menu">
								<?php if ($is_pro) : ?>
									<a href="<?php echo esc_url($view_url); ?>" class="ttbm-co-action-item" role="menuitem"><span class="dashicons dashicons-visibility"></span> <?php esc_html_e('View Detail', 'tour-booking-manager'); ?></a>
									<button type="button" class="ttbm-co-action-item ttbm-co-change-status-btn" role="menuitem" data-id="<?php echo esc_attr($row['id']); ?>" data-ref="<?php echo esc_attr('#' . $row['id']); ?>" data-status="<?php echo esc_attr(TTBM_Booking_Normalizer::normalize_status($row['status'])); ?>" data-source="<?php echo esc_attr($row['source']); ?>"><span class="dashicons dashicons-update"></span> <?php esc_html_e('Change Status', 'tour-booking-manager'); ?></button>
								<?php else : ?>
									<a href="<?php echo esc_url(self::PRO_URL); ?>" target="_blank" rel="noopener noreferrer" class="ttbm-co-action-item ttbm-co-action-pro" role="menuitem" aria-label="<?php esc_attr_e('View Detail — available in PRO', 'tour-booking-manager'); ?>"><span class="dashicons dashicons-lock"></span> <?php esc_html_e('View Detail', 'tour-booking-manager'); ?><span class="ttbm-co-action-pro-badge"><?php esc_html_e('PRO', 'tour-booking-manager'); ?></span></a>
									<a href="<?php echo esc_url(self::PRO_URL); ?>" target="_blank" rel="noopener noreferrer" class="ttbm-co-action-item ttbm-co-action-pro" role="menuitem" aria-label="<?php esc_attr_e('Change Status — available in PRO', 'tour-booking-manager'); ?>"><span class="dashicons dashicons-lock"></span> <?php esc_html_e('Change Status', 'tour-booking-manager'); ?><span class="ttbm-co-action-pro-badge"><?php esc_html_e('PRO', 'tour-booking-manager'); ?></span></a>
								<?php endif; ?>
								<?php if ($is_woo && !empty($row['edit_url'])) : ?>
									<a href="<?php echo esc_url($row['edit_url']); ?>" target="_blank" class="ttbm-co-action-item" role="menuitem"><span class="dashicons dashicons-external"></span> <?php esc_html_e('Open in WooCommerce', 'tour-booking-manager'); ?></a>
								<?php endif; ?>
								<?php if ($is_woo && class_exists('TTBM_Pro_Pdf')) : ?>
									<a href="<?php echo esc_url(TTBM_Pro_Pdf::get_pdf_url(array('order_id' => $row['id']))); ?>" target="_blank" class="ttbm-co-action-item" role="menuitem"><span class="dashicons dashicons-media-document"></span> <?php esc_html_e('PDF', 'tour-booking-manager'); ?></a>
								<?php endif; ?>
								<a href="<?php echo esc_url($delete_url); ?>" class="ttbm-co-action-item ttbm-co-action-danger" role="menuitem" onclick="return confirm('<?php echo esc_js(__('Move this order to trash?', 'tour-booking-manager')); ?>');"><span class="dashicons dashicons-trash"></span> <?php esc_html_e('Delete', 'tour-booking-manager'); ?></a>
							</div>
						</div>
					</td>
				</tr>
				<?php
			}
			private static function render_pagination($filters, $total_pages, $base_url) {
				if ($total_pages <= 1) {
					return;
				}
				$page_links = paginate_links(array(
					'base' => add_query_arg('paged', '%#%', add_query_arg(array_filter(array(
						's' => $filters['search'],
						'tour' => $filters['tour'],
						'status' => $filters['status'],
						'gateway' => $filters['gateway'],
						'date_from' => $filters['date_from'],
						'date_to' => $filters['date_to'],
					)), $base_url)),
					'format' => '',
					'current' => $filters['paged'],
					'total' => $total_pages,
					'type' => 'plain',
				));
				echo '<div class="tablenav"><div class="tablenav-pages">' . wp_kses_post($page_links) . '</div></div>';
			}
			//----------------------------------------------------------------------
			// Detail view — dispatch by source
			//----------------------------------------------------------------------
			public static function render_order_detail($order_id, $source) {
				if ($source === TTBM_Booking_Normalizer::SOURCE_WOO) {
					self::render_woo_order_detail($order_id);
					return;
				}
				self::render_custom_order_detail($order_id);
			}
			private static function render_custom_order_detail($order_id) {
				if (!$order_id || get_post_type($order_id) !== 'ttbm_custom_order') {
					echo '<div class="wrap"><h1>' . esc_html__('Order not found.', 'tour-booking-manager') . '</h1></div>';
					return;
				}
				$base_url = add_query_arg(array('post_type' => TTBM_Function::get_cpt_name(), 'page' => self::SLUG), admin_url('edit.php'));
				$status = get_post_status($order_id);
				$tour_id = (int) get_post_meta($order_id, '_ttbm_tour_id', true);
				$ticket_info = (array) get_post_meta($order_id, '_ttbm_ticket_info', true);
				$service_info = (array) get_post_meta($order_id, '_ttbm_service_info', true);
				$hotel_info = (array) get_post_meta($order_id, '_ttbm_hotel_info', true);
				$tour_date = (string) get_post_meta($order_id, '_ttbm_date', true);
				$data_format = TTBM_Global_Function::check_time_exit_date($tour_date) ? 'full' : 'date';
				$gateway_error = (string) get_post_meta($order_id, '_ttbm_gateway_error', true);
				$ticket_allocations = TTBM_Booking_Normalizer::ticket_price_allocations($order_id);
				?>
				<div class="wrap ttbm-co-wrap">
					<div class="ttbm-co-header ttbm-co-detail-header">
						<div>
							<h1 class="ttbm-co-title">
								<span class="dashicons dashicons-tickets-alt"></span>
								<?php /* translators: %d: order id */ printf(esc_html__('Order #%d', 'tour-booking-manager'), (int) $order_id); ?>
								<span class="ttbm-co-pill ttbm-co-current-status-pill is-<?php echo esc_attr(TTBM_Booking_Normalizer::status_class($status)); ?>" data-order-id="<?php echo esc_attr($order_id); ?>"><?php echo esc_html(TTBM_Custom_Order_CPT::status_label($status)); ?></span>
							</h1>
							<p class="ttbm-co-subtitle"><?php esc_html_e('Placed on', 'tour-booking-manager'); ?> <?php echo esc_html(get_the_date('M j, Y g:i a', $order_id)); ?></p>
						</div>
						<?php self::render_detail_header_actions($order_id, $base_url, $status, TTBM_Booking_Normalizer::SOURCE_CUSTOM); ?>
					</div>
					<?php if (isset($_GET['updated'])) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
						<div class="notice notice-success is-dismissible"><p><?php esc_html_e('Order updated.', 'tour-booking-manager'); ?></p></div>
					<?php endif; ?>
					<?php if ($gateway_error) : ?>
						<div class="notice notice-error"><p><strong><?php esc_html_e('Gateway error:', 'tour-booking-manager'); ?></strong> <?php echo esc_html($gateway_error); ?></p></div>
					<?php endif; ?>
					<div class="ttbm-co-detail-columns">
						<div class="ttbm-co-detail-main">
							<div class="ttbm-co-detail-grid">
								<div class="ttbm-co-card">
									<h2><?php esc_html_e('Order details', 'tour-booking-manager'); ?></h2>
									<table class="ttbm-co-detail-table">
										<tr><th><?php esc_html_e('Placed on', 'tour-booking-manager'); ?></th><td><?php echo esc_html(get_the_date('M j, Y g:i a', $order_id)); ?></td></tr>
										<tr><th><?php echo esc_html(TTBM_Function::get_name()); ?></th><td><a href="<?php echo esc_url(get_edit_post_link($tour_id)); ?>"><?php echo esc_html(get_the_title($tour_id)); ?></a></td></tr>
										<?php if ($tour_date) : ?>
											<tr><th><?php echo esc_html(TTBM_Function::get_name()); ?> <?php esc_html_e('Date', 'tour-booking-manager'); ?></th><td><?php echo esc_html(TTBM_Global_Function::date_format($tour_date, $data_format)); ?></td></tr>
										<?php endif; ?>
										<?php if (!empty($hotel_info['hotel_id'])) : ?>
											<tr><th><?php esc_html_e('Hotel', 'tour-booking-manager'); ?></th><td><?php echo esc_html(get_the_title($hotel_info['hotel_id'])); ?></td></tr>
											<tr><th><?php esc_html_e('Check in / out', 'tour-booking-manager'); ?></th><td><?php echo esc_html($hotel_info['ttbm_checkin_date'] . ' — ' . $hotel_info['ttbm_checkout_date']); ?></td></tr>
										<?php endif; ?>
										<tr><th><?php esc_html_e('Payment method', 'tour-booking-manager'); ?></th><td><?php echo esc_html(self::gateway_label(array('source' => TTBM_Booking_Normalizer::SOURCE_CUSTOM, 'gateway' => get_post_meta($order_id, '_ttbm_payment_gateway', true)))); ?></td></tr>
										<tr><th><?php esc_html_e('Total', 'tour-booking-manager'); ?></th><td><strong><?php echo esc_html(self::format_price(get_post_meta($order_id, '_ttbm_order_total', true))); ?></strong></td></tr>
									</table>
								</div>
								<div class="ttbm-co-card">
									<h2><?php esc_html_e('Customer', 'tour-booking-manager'); ?></h2>
									<table class="ttbm-co-detail-table">
										<tr><th><?php esc_html_e('Name', 'tour-booking-manager'); ?></th><td><?php echo esc_html(get_post_meta($order_id, '_ttbm_customer_name', true)); ?></td></tr>
										<tr><th><?php esc_html_e('Email', 'tour-booking-manager'); ?></th><td><?php echo esc_html(get_post_meta($order_id, '_ttbm_customer_email', true)); ?></td></tr>
										<tr><th><?php esc_html_e('Phone', 'tour-booking-manager'); ?></th><td><?php echo esc_html(get_post_meta($order_id, '_ttbm_customer_phone', true)); ?></td></tr>
										<?php $customer_id = (int) get_post_meta($order_id, '_ttbm_customer_id', true); ?>
										<?php if ($customer_id) : ?>
											<tr><th><?php esc_html_e('Account', 'tour-booking-manager'); ?></th><td><a href="<?php echo esc_url(get_edit_user_link($customer_id)); ?>"><?php echo esc_html(get_the_author_meta('display_name', $customer_id)); ?></a></td></tr>
										<?php endif; ?>
									</table>
								</div>
							</div>
							<div class="ttbm-co-card">
								<h2><?php esc_html_e('Items', 'tour-booking-manager'); ?></h2>
								<table class="widefat striped">
									<thead>
										<tr>
											<th><?php esc_html_e('Item', 'tour-booking-manager'); ?></th>
											<th><?php esc_html_e('Qty', 'tour-booking-manager'); ?></th>
											<th><?php esc_html_e('Price', 'tour-booking-manager'); ?></th>
											<th><?php esc_html_e('Subtotal', 'tour-booking-manager'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($ticket_info as $ticket) :
											if (empty($ticket['ticket_qty'])) {
												continue;
											} ?>
											<tr>
												<td><?php echo esc_html($ticket['ticket_name']); ?></td>
												<td><?php echo esc_html($ticket['ticket_qty']); ?></td>
												<td><?php echo esc_html(self::format_price($ticket['ticket_price'])); ?></td>
												<td><?php echo esc_html(self::format_price($ticket['ticket_price'] * $ticket['ticket_qty'])); ?></td>
											</tr>
										<?php endforeach; ?>
										<?php foreach ($service_info as $service) :
											if (empty($service['service_qty'])) {
												continue;
											} ?>
											<tr class="ttbm-co-addon-row">
												<td><?php echo esc_html($service['service_name']); ?> <em class="ttbm-co-muted"><?php esc_html_e('(extra service)', 'tour-booking-manager'); ?></em></td>
												<td><?php echo esc_html($service['service_qty']); ?></td>
												<td><?php echo esc_html(self::format_price($service['service_price'])); ?></td>
												<td><?php echo esc_html(self::format_price($service['service_price'] * $service['service_qty'])); ?></td>
											</tr>
										<?php endforeach; ?>
										<tr class="ttbm-co-total-row">
											<td colspan="3"><strong><?php esc_html_e('Grand Total', 'tour-booking-manager'); ?></strong></td>
											<td><strong><?php echo esc_html(self::format_price(get_post_meta($order_id, '_ttbm_order_total', true))); ?></strong></td>
										</tr>
									</tbody>
								</table>
							</div>
							<?php if (!empty($ticket_allocations['tickets'])) : ?>
								<div class="ttbm-co-card">
									<h2><?php esc_html_e('Individual ticket records', 'tour-booking-manager'); ?></h2>
									<table class="widefat striped">
										<thead>
											<tr>
												<th><?php esc_html_e('Booking ID', 'tour-booking-manager'); ?></th>
												<th><?php echo esc_html(TTBM_Function::ticket_name_text()); ?></th>
												<th><?php esc_html_e('Extra-service allocation', 'tour-booking-manager'); ?></th>
												<th><?php esc_html_e('Ticket total', 'tour-booking-manager'); ?></th>
												<th><?php esc_html_e('Attendee', 'tour-booking-manager'); ?></th>
												<th><?php esc_html_e('Status', 'tour-booking-manager'); ?></th>
												<th><?php esc_html_e('PIN', 'tour-booking-manager'); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($ticket_allocations['tickets'] as $ticket) :
												$booking_id = (int) $ticket['booking_id']; ?>
												<tr>
													<td>#<?php echo esc_html($booking_id); ?></td>
													<td>
														<strong><?php echo esc_html($ticket['name']); ?></strong><br>
														<span class="ttbm-co-muted"><?php printf(esc_html__('Base: %s', 'tour-booking-manager'), esc_html(self::format_price($ticket['ticket_amount']))); ?></span>
													</td>
													<td>
														<?php if (!empty($ticket['services'])) : ?>
															<?php foreach ($ticket['services'] as $service) : ?>
																<div><?php echo esc_html(sprintf('%s — %s', $service['name'], self::format_price($service['amount_share']))); ?> <span class="ttbm-co-muted"><?php esc_html_e('(order share)', 'tour-booking-manager'); ?></span></div>
															<?php endforeach; ?>
													<?php else : ?>
														<span class="ttbm-co-muted">—</span>
													<?php endif; ?>
													<?php if (abs((float) $ticket['adjustment']) >= 0.005) : ?>
														<div><?php printf(esc_html__('Order adjustment — %s', 'tour-booking-manager'), esc_html(self::format_price($ticket['adjustment']))); ?></div>
													<?php endif; ?>
												</td>
													<td><strong><?php echo esc_html(self::format_price($ticket['total'])); ?></strong></td>
													<td><?php echo esc_html(get_post_meta($booking_id, 'ttbm_billing_name', true)); ?></td>
													<td><?php echo esc_html(ucfirst(get_post_meta($booking_id, 'ttbm_order_status', true))); ?></td>
													<td><?php echo esc_html(get_post_meta($booking_id, 'ttbm_pin', true)); ?></td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							<?php endif; ?>
						</div>
						<div class="ttbm-co-detail-sidebar">
							<div class="ttbm-co-card">
								<h2><span class="dashicons dashicons-admin-comments"></span> <?php esc_html_e('Notes', 'tour-booking-manager'); ?></h2>
								<div class="ttbm-co-card-body">
									<div class="ttbm-co-note-form" data-order-id="<?php echo esc_attr($order_id); ?>" data-source="<?php echo esc_attr(TTBM_Booking_Normalizer::SOURCE_CUSTOM); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('ttbm_orders_page')); ?>">
										<textarea class="ttbm-co-note-input" rows="3" placeholder="<?php esc_attr_e('Add a private note…', 'tour-booking-manager'); ?>"></textarea>
										<button type="button" class="ttbm-co-note-add button button-primary"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Add Note', 'tour-booking-manager'); ?></button>
									</div>
									<div class="ttbm-co-log-list ttbm-co-notes-list">
										<?php self::render_notes_list($order_id, TTBM_Booking_Normalizer::SOURCE_CUSTOM); ?>
									</div>
								</div>
							</div>
							<div class="ttbm-co-card">
								<h2><span class="dashicons dashicons-clock"></span> <?php esc_html_e('Activity Log', 'tour-booking-manager'); ?></h2>
								<div class="ttbm-co-card-body">
									<div class="ttbm-co-log-list">
										<?php self::render_activity_log($order_id, TTBM_Booking_Normalizer::SOURCE_CUSTOM); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php self::render_status_modal(); ?>
				<?php
			}
			// Renders a real WooCommerce order fully in-plugin (never a redirect).
			// Same card layout as the custom-order detail above, plus a read-only
			// order-notes sidebar and an "Open in WooCommerce" link that stays
			// alongside the in-plugin view rather than replacing it.
			private static function render_woo_order_detail($order_id) {
				$order = TTBM_Booking_Normalizer::resolve_wc_order($order_id);
				if (!$order) {
					echo '<div class="wrap"><h1>' . esc_html__('Order not found.', 'tour-booking-manager') . '</h1></div>';
					return;
				}
				$base_url = add_query_arg(array('post_type' => TTBM_Function::get_cpt_name(), 'page' => self::SLUG), admin_url('edit.php'));
				$status = $order->get_status();
				// Representative tour/date come from the first linked ttbm_booking row.
				$booking_posts = get_posts(array(
					'post_type' => 'ttbm_booking',
					'posts_per_page' => 1,
					'meta_key' => 'ttbm_order_id',
					'meta_value' => $order_id,
				));
				$tour_id = $booking_posts ? (int) get_post_meta($booking_posts[0]->ID, 'ttbm_id', true) : 0;
				$tour_date = $booking_posts ? (string) get_post_meta($booking_posts[0]->ID, 'ttbm_date', true) : '';
				$data_format = TTBM_Global_Function::check_time_exit_date($tour_date) ? 'full' : 'date';
				$customer_id = (int) $order->get_customer_id();
				?>
				<div class="wrap ttbm-co-wrap">
					<div class="ttbm-co-header ttbm-co-detail-header">
						<div>
							<h1 class="ttbm-co-title">
								<span class="dashicons dashicons-tickets-alt"></span>
								<?php /* translators: %d: order id */ printf(esc_html__('Order #%d', 'tour-booking-manager'), (int) $order_id); ?>
								<span class="ttbm-co-pill ttbm-co-current-status-pill is-<?php echo esc_attr(TTBM_Booking_Normalizer::status_class($status)); ?>" data-order-id="<?php echo esc_attr($order_id); ?>"><?php echo esc_html(wc_get_order_status_name($status)); ?></span>
								<span class="ttbm-co-source ttbm-co-source-woo"><?php esc_html_e('WooCommerce', 'tour-booking-manager'); ?></span>
							</h1>
							<p class="ttbm-co-subtitle"><?php esc_html_e('Placed on', 'tour-booking-manager'); ?> <?php echo esc_html($order->get_date_created() ? $order->get_date_created()->date_i18n('M j, Y g:i a') : ''); ?></p>
						</div>
						<?php self::render_detail_header_actions($order_id, $base_url, $status, TTBM_Booking_Normalizer::SOURCE_WOO, $order->get_edit_order_url()); ?>
					</div>
					<?php if (isset($_GET['updated'])) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
						<div class="notice notice-success is-dismissible"><p><?php esc_html_e('Order updated.', 'tour-booking-manager'); ?></p></div>
					<?php endif; ?>
					<div class="ttbm-co-detail-columns">
						<div class="ttbm-co-detail-main">
							<div class="ttbm-co-detail-grid">
								<div class="ttbm-co-card">
									<h2><?php esc_html_e('Order details', 'tour-booking-manager'); ?></h2>
									<table class="ttbm-co-detail-table">
										<tr><th><?php esc_html_e('Placed on', 'tour-booking-manager'); ?></th><td><?php echo esc_html($order->get_date_created() ? $order->get_date_created()->date_i18n('M j, Y g:i a') : ''); ?></td></tr>
										<?php if ($tour_id) : ?>
											<tr><th><?php echo esc_html(TTBM_Function::get_name()); ?></th><td><a href="<?php echo esc_url(get_edit_post_link($tour_id)); ?>"><?php echo esc_html(get_the_title($tour_id)); ?></a></td></tr>
										<?php endif; ?>
										<?php if ($tour_date) : ?>
											<tr><th><?php echo esc_html(TTBM_Function::get_name()); ?> <?php esc_html_e('Date', 'tour-booking-manager'); ?></th><td><?php echo esc_html(TTBM_Global_Function::date_format($tour_date, $data_format)); ?></td></tr>
										<?php endif; ?>
										<tr><th><?php esc_html_e('Payment method', 'tour-booking-manager'); ?></th><td><?php echo esc_html($order->get_payment_method_title()); ?></td></tr>
										<tr><th><?php esc_html_e('Total', 'tour-booking-manager'); ?></th><td><strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong></td></tr>
									</table>
								</div>
								<div class="ttbm-co-card">
									<h2><?php esc_html_e('Customer', 'tour-booking-manager'); ?></h2>
									<table class="ttbm-co-detail-table">
										<tr><th><?php esc_html_e('Name', 'tour-booking-manager'); ?></th><td><?php echo esc_html(trim($order->get_formatted_billing_full_name())); ?></td></tr>
										<tr><th><?php esc_html_e('Email', 'tour-booking-manager'); ?></th><td><?php echo esc_html($order->get_billing_email()); ?></td></tr>
										<tr><th><?php esc_html_e('Phone', 'tour-booking-manager'); ?></th><td><?php echo esc_html($order->get_billing_phone()); ?></td></tr>
										<?php if ($customer_id) : ?>
											<tr><th><?php esc_html_e('Account', 'tour-booking-manager'); ?></th><td><a href="<?php echo esc_url(get_edit_user_link($customer_id)); ?>"><?php echo esc_html(get_the_author_meta('display_name', $customer_id)); ?></a></td></tr>
										<?php endif; ?>
									</table>
								</div>
							</div>
							<div class="ttbm-co-card">
								<h2><?php esc_html_e('Items', 'tour-booking-manager'); ?></h2>
								<table class="widefat striped">
									<thead>
										<tr>
											<th><?php esc_html_e('Item', 'tour-booking-manager'); ?></th>
											<th><?php esc_html_e('Qty', 'tour-booking-manager'); ?></th>
											<th><?php esc_html_e('Price', 'tour-booking-manager'); ?></th>
											<th><?php esc_html_e('Subtotal', 'tour-booking-manager'); ?></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($order->get_items() as $item) :
											// The ticket-type / extra-service breakdown lives in item meta
											// (TTBM_Woocommerce::checkout_create_order_line_item), not as
											// separate WC line items — one product line item can bundle
											// several ticket types and services. Expand it here the same
											// way the custom-order table does, falling back to the plain
											// line item when a non-tour product has no ttbm breakdown.
											$item_ticket_info = TTBM_Global_Function::data_sanitize($item->get_meta('_ttbm_ticket_info'));
											$item_service_info = TTBM_Global_Function::data_sanitize($item->get_meta('_ttbm_service_info'));
											$item_ticket_info = is_array($item_ticket_info) ? $item_ticket_info : array();
											$item_service_info = is_array($item_service_info) ? $item_service_info : array();
											if (empty($item_ticket_info) && empty($item_service_info)) :
												$qty = max(1, (int) $item->get_quantity());
												?>
												<tr>
													<td><?php echo esc_html($item->get_name()); ?></td>
													<td><?php echo esc_html($qty); ?></td>
													<td><?php echo wp_kses_post(wc_price($item->get_total() / $qty, array('currency' => $order->get_currency()))); ?></td>
													<td><strong><?php echo wp_kses_post(wc_price($item->get_total(), array('currency' => $order->get_currency()))); ?></strong></td>
												</tr>
											<?php else :
												foreach ($item_ticket_info as $ticket) :
													if (empty($ticket['ticket_qty'])) {
														continue;
													} ?>
													<tr>
														<td><?php echo esc_html($ticket['ticket_name']); ?></td>
														<td><?php echo esc_html($ticket['ticket_qty']); ?></td>
														<td><?php echo wp_kses_post(wc_price($ticket['ticket_price'], array('currency' => $order->get_currency()))); ?></td>
														<td><strong><?php echo wp_kses_post(wc_price($ticket['ticket_price'] * $ticket['ticket_qty'], array('currency' => $order->get_currency()))); ?></strong></td>
													</tr>
												<?php endforeach;
												foreach ($item_service_info as $service) :
													if (empty($service['service_qty'])) {
														continue;
													} ?>
													<tr class="ttbm-co-addon-row">
														<td><?php echo esc_html($service['service_name']); ?> <em class="ttbm-co-muted"><?php esc_html_e('(extra service)', 'tour-booking-manager'); ?></em></td>
														<td><?php echo esc_html($service['service_qty']); ?></td>
														<td><?php echo wp_kses_post(wc_price($service['service_price'], array('currency' => $order->get_currency()))); ?></td>
														<td><?php echo wp_kses_post(wc_price($service['service_price'] * $service['service_qty'], array('currency' => $order->get_currency()))); ?></td>
													</tr>
												<?php endforeach;
											endif; ?>
										<?php endforeach; ?>
										<?php foreach ($order->get_items('fee') as $fee) : ?>
											<tr class="ttbm-co-addon-row">
												<td><?php echo esc_html($fee->get_name()); ?> <em class="ttbm-co-muted"><?php esc_html_e('(fee)', 'tour-booking-manager'); ?></em></td>
												<td>—</td>
												<td><?php echo wp_kses_post(wc_price($fee->get_total(), array('currency' => $order->get_currency()))); ?></td>
												<td><?php echo wp_kses_post(wc_price($fee->get_total(), array('currency' => $order->get_currency()))); ?></td>
											</tr>
										<?php endforeach; ?>
										<tr class="ttbm-co-total-row">
											<td colspan="3"><strong><?php esc_html_e('Grand Total', 'tour-booking-manager'); ?></strong></td>
											<td><strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong></td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
						<div class="ttbm-co-detail-sidebar">
							<div class="ttbm-co-card">
								<h2><span class="dashicons dashicons-admin-comments"></span> <?php esc_html_e('Notes', 'tour-booking-manager'); ?></h2>
								<div class="ttbm-co-card-body">
									<p class="description"><?php esc_html_e('Adds a real WooCommerce order note (private) — also visible on the native WooCommerce order screen.', 'tour-booking-manager'); ?></p>
									<div class="ttbm-co-note-form" data-order-id="<?php echo esc_attr($order_id); ?>" data-source="<?php echo esc_attr(TTBM_Booking_Normalizer::SOURCE_WOO); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('ttbm_orders_page')); ?>">
										<textarea class="ttbm-co-note-input" rows="3" placeholder="<?php esc_attr_e('Add a private note…', 'tour-booking-manager'); ?>"></textarea>
										<button type="button" class="ttbm-co-note-add button button-primary"><span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Add Note', 'tour-booking-manager'); ?></button>
									</div>
									<div class="ttbm-co-log-list ttbm-co-notes-list">
										<?php self::render_notes_list($order_id, TTBM_Booking_Normalizer::SOURCE_WOO); ?>
									</div>
								</div>
							</div>
							<div class="ttbm-co-card">
								<h2><span class="dashicons dashicons-clock"></span> <?php esc_html_e('Activity Log', 'tour-booking-manager'); ?></h2>
								<div class="ttbm-co-card-body">
									<div class="ttbm-co-log-list">
										<?php self::render_activity_log($order_id, TTBM_Booking_Normalizer::SOURCE_WOO); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php self::render_status_modal(); ?>
				<?php
			}
		}
		TTBM_Custom_Orders_Page::init();
	}
