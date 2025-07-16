<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Booking')) {
		class TTBM_Booking {
			public function __construct() {
				add_action('wp_ajax_get_ttbm_ticket', array($this, 'get_ttbm_ticket'));
				add_action('wp_ajax_nopriv_get_ttbm_ticket', array($this, 'get_ttbm_ticket'));
				add_action('wp_ajax_get_ttbm_sold_ticket', array($this, 'get_ttbm_sold_ticket'));
				add_action('wp_ajax_nopriv_get_ttbm_sold_ticket', array($this, 'get_ttbm_sold_ticket'));
				add_action('wp_ajax_get_ttbm_hotel_room_list', array($this, 'get_ttbm_hotel_room_list'));
				add_action('wp_ajax_nopriv_get_ttbm_hotel_room_list', array($this, 'get_ttbm_hotel_room_list'));
				add_action('ttbm_booking_panel', array($this, 'booking_panel'), 10, 4);
			}
			public function get_ttbm_ticket() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_frontend_nonce')) {
					wp_send_json_error(['message' => 'Invalid nonce']);
					die;
				}
				$tour_id = isset($_REQUEST['tour_id']) ? sanitize_text_field(wp_unslash($_REQUEST['tour_id'])) : '';
				$hotel_id = isset($_REQUEST['hotel_id']) ? sanitize_text_field(wp_unslash($_REQUEST['hotel_id'])) : '';
				$date = isset($_REQUEST['tour_date']) ? sanitize_text_field(wp_unslash($_REQUEST['tour_date'])) : '';
				$date = str_replace('/', '-', $date);
				$time = TTBM_Function::get_time($tour_id, gmdate('Y-m-d', strtotime($date)));
				$date_format = $time ? 'Y-m-d H:i' : 'Y-m-d';
				$date = $date ? gmdate($date_format, strtotime($date)) : $date;
				do_action('ttbm_booking_panel', $tour_id, $date, $hotel_id);
				die();
			}
			public function get_ttbm_sold_ticket() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_frontend_nonce')) {
					wp_send_json_error(['message' => 'Invalid nonce']);
					die;
				}
				$tour_id = isset($_REQUEST['tour_id']) ? sanitize_text_field(wp_unslash($_REQUEST['tour_id'])) : '';
				$date = isset($_REQUEST['tour_date']) ? sanitize_text_field(wp_unslash($_REQUEST['tour_date'])) : '';;
				$date = str_replace('/', '-', $date);
				$time = TTBM_Function::get_time($tour_id, gmdate('Y-m-d', strtotime($date)));
				$date_format = $time ? 'Y-m-d H:i' : 'Y-m-d';
				$date = $date ? gmdate($date_format, strtotime($date)) : $date;
				echo esc_html(TTBM_Function::get_total_available($tour_id, $date));
				die();
			}
			public function get_ttbm_hotel_room_list() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_frontend_nonce')) {
					wp_send_json_error(['message' => 'Invalid nonce']);
					die;
				}
				$tour_id = isset($_REQUEST['tour_id']) ? sanitize_text_field(wp_unslash($_REQUEST['tour_id'])) : '';
				$hotel_id = isset($_REQUEST['hotel_id']) ? sanitize_text_field(wp_unslash($_REQUEST['hotel_id'])) : '';
				$date_range = isset($_REQUEST['date_range']) ? sanitize_text_field(wp_unslash($_REQUEST['date_range'])) : '';
				$date = explode("    -    ", $date_range);
				$start_date = gmdate('Y-m-d', strtotime($date[0]));
				do_action('ttbm_booking_panel', $tour_id, $start_date, $hotel_id);
				die();
			}
			public function booking_panel($tour_id, $tour_date = '', $hotel_id = '') {
				$tour_date = $tour_date ?: current(TTBM_Function::get_date($tour_id));
				$action = apply_filters('ttbm_form_submit_path', '', $tour_id);
				?>
                <form action="<?php echo esc_attr($action); ?>" method='post' class="mp_tour_ticket_form">
	                <?php wp_nonce_field('ttbm_form_nonce', 'ttbm_form_nonce'); ?>
                    <input type="hidden" name='ttbm_start_date' id='ttbm_tour_datetime' value='<?php echo esc_attr($tour_date); ?>'/>
                    <input type="hidden" name='ttbm_total_price' id="ttbm_total_price" value='0'/>
					<?php do_action('ttbm_booking_panel_inside_form', $tour_id, $hotel_id); ?>
					<?php
						$ttbm_type = TTBM_Function::get_tour_type($tour_id);
						if ($ttbm_type == 'general') {
							$file = TTBM_Function::template_path('ticket/regular_ticket.php');
							$file = apply_filters('ttbm_regular_ticket_file', $file, $tour_id);
							require_once $file;
						}
						if ($ttbm_type == 'hotel') {
							$file = TTBM_Function::template_path('ticket/hotel_ticket.php');
							$file = apply_filters('ttbm_hotel_ticket_file', $file, $tour_id);
							require_once $file;
						}
					?>
                </form>
				<?php do_action('ttbm_tour_reg_form_hidden', $tour_id, $hotel_id); ?>
				<?php
			}
		}
		new TTBM_Booking();
	}