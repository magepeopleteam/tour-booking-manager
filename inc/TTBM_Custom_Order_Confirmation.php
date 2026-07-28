<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	/**
	 * Renders the post-checkout confirmation for custom (non-WooCommerce)
	 * orders: a status banner plus an order summary. TTBM_Custom_Checkout
	 * redirects to the Booking Confirmation Page (Settings → Payments → Custom
	 * Payment) — or the tour page — with ?ttbm_state=<state>&ttbm_order=<id>
	 * &key=<order key>. (Deliberately NOT "ttbm_booking" — that collides with
	 * the public ttbm_booking CPT's auto-registered query_var and 404s the
	 * whole request; see TTBM_Custom_Checkout::redirect_with_notice().)
	 *
	 * Output goes through the [ttbm-booking-confirmation] shortcode when the
	 * page contains it; otherwise it is prepended to the page content. The
	 * banner state always derives from the order's real post_status, never the
	 * (spoofable) URL hint, and the summary only renders with a matching key.
	 */
	if (!class_exists('TTBM_Custom_Order_Confirmation')) {
		class TTBM_Custom_Order_Confirmation {
			private static $instance = null;
			public function __construct() {
				if (self::$instance) {
					return;
				}
				self::$instance = $this;
				add_shortcode('ttbm-booking-confirmation', array($this, 'shortcode'));
				add_filter('the_content', array($this, 'maybe_prepend'), 20);
				// Single tour pages render through the plugin's own template, not
				// the_content — hook their wrapper action for cancel/failure banners.
				add_action('ttbm_single_page_before_wrapper', array($this, 'maybe_echo'));
				add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
			}
			public static function instance() {
				return self::$instance ?: new self();
			}
			public function maybe_echo() {
				echo $this->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- render() escapes internally
			}
			public function enqueue_assets() {
				if (isset($_GET['ttbm_state'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only
					wp_enqueue_style('ttbm-custom-checkout', TTBM_PLUGIN_URL . '/assets/frontend/ttbm-custom-checkout.css', array(), TTBM_PLUGIN_VERSION);
				}
			}
			public function shortcode() {
				return $this->render();
			}
			public function maybe_prepend($content) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only
				if (!isset($_GET['ttbm_state']) || !is_singular() || !in_the_loop() || !is_main_query()) {
					return $content;
				}
				if (has_shortcode($content, 'ttbm-booking-confirmation')) {
					return $content;
				}
				return $this->render() . $content;
			}
			/**
			 * Inline confirmation HTML for the checkout modal (AJAX response).
			 *
			 * @param int    $order_id ttbm_custom_order post ID.
			 * @param string $state    success|pending|cancelled|failed.
			 * @return string
			 */
			public static function render_order_confirmation($order_id, $state = 'pending') {
				$order_id = absint($order_id);
				if (!$order_id || get_post_type($order_id) !== 'ttbm_custom_order') {
					return '';
				}
				$self = self::instance();
				$banners = $self->banner_copy();
				if (!isset($banners[$state])) {
					$state = 'pending';
				}
				$banner = $banners[$state];
				ob_start();
				?>
				<div class="ttbm_booking_confirmation ttbm_style ttbm-cc-inline-confirmation">
					<div class="ttbm_booking_banner is-<?php echo esc_attr($state); ?>">
						<h3><?php echo esc_html($banner['title']); ?></h3>
						<p><?php echo esc_html($banner['text']); ?></p>
					</div>
					<?php if ($state !== 'failed') {
						$self->summary($order_id);
					} ?>
				</div>
				<?php
				return ob_get_clean();
			}
			private function banner_copy() {
				return array(
					'success' => array(
						'title' => esc_html__('Booking confirmed!', 'tour-booking-manager'),
						'text' => esc_html__('Thank you — your payment was received and your booking is confirmed. A confirmation email has been sent.', 'tour-booking-manager'),
					),
					'pending' => array(
						'title' => esc_html__('Booking received', 'tour-booking-manager'),
						'text' => esc_html__('Your booking has been recorded and will be confirmed once payment is completed.', 'tour-booking-manager'),
					),
					'cancelled' => array(
						'title' => esc_html__('Booking cancelled', 'tour-booking-manager'),
						'text' => esc_html__('The payment was cancelled and your booking was not completed. You can try again at any time.', 'tour-booking-manager'),
					),
					'failed' => array(
						'title' => esc_html__('Booking failed', 'tour-booking-manager'),
						'text' => esc_html__('Something went wrong while processing your booking. Please try again or contact us.', 'tour-booking-manager'),
					),
				);
			}
			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only display of the landing state
			private function render() {
				static $rendered = false;
				if ($rendered || !isset($_GET['ttbm_state'])) {
					return '';
				}
				$rendered = true;
				$order_id = isset($_GET['ttbm_order']) ? absint($_GET['ttbm_order']) : 0;
				$key = isset($_GET['key']) ? sanitize_text_field(wp_unslash($_GET['key'])) : '';
				$order = ($order_id && get_post_type($order_id) === 'ttbm_custom_order') ? $order_id : 0;
				$verified = false;
				if ($order) {
					$order_key = (string) get_post_meta($order, '_ttbm_order_key', true);
					$verified = $order_key && $key && hash_equals($order_key, $key);
				}
				// The banner reflects the order's true status; the URL state is only
				// a fallback for order-less notices (e.g. validation failures).
				$url_state = sanitize_key(wp_unslash($_GET['ttbm_state']));
				$state = in_array($url_state, array('cancelled', 'failed'), true) ? $url_state : 'pending';
				if ($order && $verified) {
					switch (get_post_status($order)) {
						case 'processing':
						case 'publish':
							$state = 'success';
							break;
						case 'cancelled':
							$state = 'cancelled';
							break;
						case 'failed':
							$state = 'failed';
							break;
						default:
							$state = 'pending';
					}
				}
				$banner = $this->banner_copy()[$state];
				$notice = $order ? get_transient('ttbm_booking_notice_' . $order) : get_transient('ttbm_booking_notice_' . get_current_user_id());
				ob_start();
				?>
				<div class="ttbm_booking_confirmation ttbm_style">
					<div class="ttbm_booking_banner is-<?php echo esc_attr($state); ?>">
						<h3><?php echo esc_html($banner['title']); ?></h3>
						<p><?php echo esc_html($banner['text']); ?></p>
						<?php if ($notice && in_array($state, array('failed', 'cancelled'), true)) : ?>
							<p><em><?php echo esc_html($notice); ?></em></p>
						<?php endif; ?>
					</div>
					<?php if ($order && $verified && $state !== 'failed') {
						$this->summary($order);
					} ?>
				</div>
				<?php
				return ob_get_clean();
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
			public function summary($order_id) {
				$tour_id = (int) get_post_meta($order_id, '_ttbm_tour_id', true);
				$total = (float) get_post_meta($order_id, '_ttbm_order_total', true);
				$date = (string) get_post_meta($order_id, '_ttbm_date', true);
				$data_format = TTBM_Global_Function::check_time_exit_date($date) ? 'full' : 'date';
				$gateway_id = (string) get_post_meta($order_id, '_ttbm_payment_gateway', true);
				$gateway = TTBM_Payment_Gateway_Manager::get_instance()->get_gateway($gateway_id);
				$gateway_title = $gateway ? $gateway->get_title() : ucfirst($gateway_id);
				$ticket_info = (array) get_post_meta($order_id, '_ttbm_ticket_info', true);
				$service_info = (array) get_post_meta($order_id, '_ttbm_service_info', true);
				$customer_name = (string) get_post_meta($order_id, '_ttbm_customer_name', true);
				$status = get_post_status($order_id);
				$status_label = TTBM_Custom_Order_CPT::status_label($status);
				$status_slug = sanitize_html_class($status);
				$tickets = array();
				foreach ($ticket_info as $ticket) {
					if (!empty($ticket['ticket_qty'])) {
						$tickets[] = sprintf('%s × %d', $ticket['ticket_name'], $ticket['ticket_qty']);
					}
				}
				$services = array();
				foreach ($service_info as $service) {
					if (!empty($service['service_qty'])) {
						$services[] = sprintf('%s × %d', $service['service_name'], $service['service_qty']);
					}
				}
				?>
				<div class="ttbm_booking_summary_card">
					<div class="ttbm_booking_summary_top">
						<div class="ttbm_booking_summary_total">
							<span class="ttbm_booking_summary_total_label"><?php esc_html_e('Total', 'tour-booking-manager'); ?></span>
							<strong class="ttbm_booking_summary_total_value"><?php echo esc_html(number_format_i18n($total, 2) . ' ' . TTBM_Custom_Checkout::currency_code()); ?></strong>
						</div>
						<span class="ttbm_booking_summary_status is-<?php echo esc_attr($status_slug); ?>"><?php echo esc_html($status_label); ?></span>
					</div>
					<ul class="ttbm_booking_summary_list">
						<li class="ttbm_booking_summary_row is-order">
							<span class="ttbm_booking_summary_label"><?php esc_html_e('Order number', 'tour-booking-manager'); ?></span>
							<span class="ttbm_booking_summary_value">#<?php echo esc_html($order_id); ?></span>
						</li>
						<li class="ttbm_booking_summary_row is-tour">
							<span class="ttbm_booking_summary_label"><?php echo esc_html(TTBM_Function::get_name()); ?></span>
							<span class="ttbm_booking_summary_value">
								<a href="<?php echo esc_url(get_permalink($tour_id)); ?>"><?php echo esc_html(get_the_title($tour_id)); ?></a>
							</span>
						</li>
						<?php if ($date) : ?>
							<li class="ttbm_booking_summary_row">
								<span class="ttbm_booking_summary_label"><?php esc_html_e('Date', 'tour-booking-manager'); ?></span>
								<span class="ttbm_booking_summary_value"><?php echo esc_html(TTBM_Global_Function::date_format($date, $data_format)); ?></span>
							</li>
						<?php endif; ?>
						<?php if ($customer_name) : ?>
							<li class="ttbm_booking_summary_row">
								<span class="ttbm_booking_summary_label"><?php esc_html_e('Booked by', 'tour-booking-manager'); ?></span>
								<span class="ttbm_booking_summary_value"><?php echo esc_html($customer_name); ?></span>
							</li>
						<?php endif; ?>
						<?php if ($tickets) : ?>
							<li class="ttbm_booking_summary_row">
								<span class="ttbm_booking_summary_label"><?php echo esc_html(TTBM_Function::ticket_name_text()); ?></span>
								<span class="ttbm_booking_summary_value"><?php echo esc_html(implode(', ', $tickets)); ?></span>
							</li>
						<?php endif; ?>
						<?php if ($services) : ?>
							<li class="ttbm_booking_summary_row">
								<span class="ttbm_booking_summary_label"><?php esc_html_e('Extra services', 'tour-booking-manager'); ?></span>
								<span class="ttbm_booking_summary_value"><?php echo esc_html(implode(', ', $services)); ?></span>
							</li>
						<?php endif; ?>
						<li class="ttbm_booking_summary_row">
							<span class="ttbm_booking_summary_label"><?php esc_html_e('Payment method', 'tour-booking-manager'); ?></span>
							<span class="ttbm_booking_summary_value"><?php echo esc_html($gateway_title ?: esc_html__('Free', 'tour-booking-manager')); ?></span>
						</li>
					</ul>
				</div>
				<?php $this->ticket_price_breakdown($order_id); ?>
				<?php $this->maybe_download_button($order_id); ?>
				<?php
			}
			private function ticket_price_breakdown($order_id) {
				$allocation = TTBM_Booking_Normalizer::ticket_price_allocations($order_id);
				if (empty($allocation['tickets']) || count($allocation['tickets']) < 2) {
					return;
				}
				$currency = TTBM_Custom_Checkout::currency_code();
				$format_price = static function ($amount) use ($currency) {
					return number_format_i18n((float) $amount, 2) . ' ' . $currency;
				};
				$format_qty = static function ($qty) {
					$qty = (float) $qty;
					return abs($qty - round($qty)) < 0.00001 ? (string) absint($qty) : number_format_i18n($qty, 2);
				};
				?>
				<div class="ttbm_booking_ticket_breakdown">
					<div class="ttbm_booking_ticket_breakdown_head">
						<div>
							<span><?php esc_html_e('Individual tickets', 'tour-booking-manager'); ?></span>
							<strong><?php esc_html_e('Ticket and extra-service allocation', 'tour-booking-manager'); ?></strong>
						</div>
						<div class="ttbm_booking_ticket_order_total">
							<span><?php esc_html_e('Booking total', 'tour-booking-manager'); ?></span>
							<strong><?php echo esc_html($format_price($allocation['order_total'])); ?></strong>
						</div>
					</div>
					<div class="ttbm_booking_ticket_breakdown_list">
						<?php foreach ($allocation['tickets'] as $ticket) : ?>
							<div class="ttbm_booking_ticket_breakdown_row">
								<div class="ttbm_booking_ticket_breakdown_main">
									<div class="ttbm_booking_ticket_identity">
										<span class="ttbm_booking_ticket_id">#<?php echo esc_html($ticket['booking_id']); ?></span>
										<strong><?php echo esc_html($ticket['name']); ?></strong>
									</div>
									<div class="ttbm_booking_ticket_components">
										<?php esc_html_e('Ticket', 'tour-booking-manager'); ?>: <?php echo esc_html($format_price($ticket['ticket_amount'])); ?>
										<span aria-hidden="true">·</span>
										<?php esc_html_e('Extra services', 'tour-booking-manager'); ?>: <?php echo esc_html($format_price($ticket['service_amount'])); ?>
										<?php if (abs((float) $ticket['adjustment']) >= 0.005) : ?>
											<span aria-hidden="true">·</span>
											<?php esc_html_e('Order adjustment', 'tour-booking-manager'); ?>: <?php echo esc_html($format_price($ticket['adjustment'])); ?>
										<?php endif; ?>
									</div>
									<?php if (!empty($ticket['services'])) : ?>
										<div class="ttbm_booking_ticket_services">
											<?php foreach ($ticket['services'] as $index => $service) : ?>
												<?php if ($index) : ?><span aria-hidden="true">·</span><?php endif; ?>
												<span><?php echo esc_html(sprintf('%s × %s (%s)', $service['name'], $format_qty($service['qty_share']), $format_price($service['amount_share']))); ?></span>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>
								</div>
								<div class="ttbm_booking_ticket_total">
									<span><?php esc_html_e('Ticket total', 'tour-booking-manager'); ?></span>
									<strong><?php echo esc_html($format_price($ticket['total'])); ?></strong>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<p class="ttbm_booking_ticket_breakdown_note"><?php esc_html_e('Order-level extra services are shared across the individual tickets so they are charged once, not repeated on every ticket.', 'tour-booking-manager'); ?></p>
				</div>
				<?php
			}
			// Same gate as the "Send Email on" statuses that trigger the ticket-
			// attached confirmation email (Settings → Booking Email Settings) and
			// the Customer Portal's own Download Ticket button — a booking only
			// gets a downloadable ticket once it's actually confirmed, not while
			// still pending/offline-awaiting-payment.
			private function maybe_download_button($order_id) {
				if (!TTBM_Booking_Normalizer::is_ticket_ready(get_post_status($order_id))) {
					return;
				}
				if (!class_exists('TTBM_Pro_Pdf') || !function_exists('is_plugin_active') || !is_plugin_active('magepeople-pdf-support-master/mage-pdf.php')) {
					return;
				}
				?>
				<p class="ttbm_booking_download">
					<a href="<?php echo esc_url(TTBM_Pro_Pdf::get_pdf_url(array('order_id' => $order_id))); ?>" class="dButton ttbm-confirm-btn">
						<?php esc_html_e('Download PDF Ticket', 'tour-booking-manager'); ?>
					</a>
				</p>
				<?php
			}
		}
		new TTBM_Custom_Order_Confirmation();
	}
