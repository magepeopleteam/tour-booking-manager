<?php
if (!defined('ABSPATH')) {
    die;
}
if (!is_user_logged_in()) {
    return;
}
$user_id = get_current_user_id();
$customer_orders = wc_get_orders(array(
    'customer_id' => $user_id,
    'limit' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
));
$cancel_period = (int) TTBM_Global_Function::get_settings('ttbm_basic_gen_settings', 'ttbm_cancel_period_hours', 24);
echo '<div class="ttbm_notice ttbm_style" style="margin-bottom:20px;padding:10px 15px;background:#eaf4ff;border-left:4px solid #3174d0;color:#222;font-size:16px;">' . sprintf(esc_html__('You can cancel your order within %d hours of placing it.', 'tour-booking-manager'), $cancel_period) . '</div>';
?>
<div class="ttbm_total_booking_wrapper ttbm_style" style="max-width:1000px;margin:auto;">
    <h2 class="ttbm_total_booking_title" style="color:#3174d0;font-size:2rem;margin-bottom:20px;"><?php esc_html_e('My Tour Orders', 'tour-booking-manager'); ?></h2>
    <table class="ttbm_total_booking_table" style="background:#fff;border-radius:8px;overflow:hidden;">
        <thead class="ttbm_total_booking_thead" style="background:#f5faff;">
            <tr>
                <th><?php esc_html_e('Order ID', 'tour-booking-manager'); ?></th>
                <th><?php esc_html_e('Tour', 'tour-booking-manager'); ?></th>
                <th><?php esc_html_e('Date', 'tour-booking-manager'); ?></th>
                <th><?php esc_html_e('Status', 'tour-booking-manager'); ?></th>
                <th><?php esc_html_e('Amount', 'tour-booking-manager'); ?></th>
                <th><?php esc_html_e('Cancellation Window', 'tour-booking-manager'); ?></th>
                <th><?php esc_html_e('Action', 'tour-booking-manager'); ?></th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($customer_orders) {
            foreach ($customer_orders as $order) {
                $order_id = $order->get_id();
                $items = $order->get_items();
                foreach ($items as $item_id => $item) {
                    $ttbm_id = $item->get_meta('_ttbm_id');
                    if ($ttbm_id && get_post_type($ttbm_id) === 'ttbm_tour') {
                        $tour_title = get_the_title($ttbm_id);
                        $tour_link = get_permalink($ttbm_id);
                        $order_date = $order->get_date_created() ? $order->get_date_created()->date_i18n(get_option('date_format')) : '';
                        $status = wc_get_order_status_name($order->get_status());
                        $amount = $order->get_formatted_order_total();
                        $can_cancel = false;
                        $cancel_status = '';
                        $now = current_time('timestamp');
                        $order_placed = $order->get_date_created() ? $order->get_date_created()->getTimestamp() : 0;
                        $cancel_deadline = $order_placed + ($cancel_period * 3600);
                        $cancel_deadline_str = $cancel_deadline ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $cancel_deadline) : '-';
                        // Check for existing cancel request
                        $existing_req = get_posts(array(
                            'post_type' => 'ttbm_cancel_request',
                            'post_status' => array('publish', 'pending', 'draft'),
                            'meta_query' => array(
                                array('key' => 'order_id', 'value' => $order_id),
                                array('key' => 'tour_id', 'value' => $ttbm_id),
                                array('key' => 'user_id', 'value' => $user_id),
                            ),
                            'numberposts' => 1
                        ));
                        if ($existing_req) {
                            $req = $existing_req[0];
                            $req_status = get_post_meta($req->ID, 'cancel_status', true);
                            if ($req_status === 'approved') {
                                $cancel_status = '<span style="color:green;font-weight:bold;">' . esc_html__('Cancelled', 'tour-booking-manager') . '</span>';
                            } elseif ($req_status === 'rejected') {
                                $cancel_status = '<span style="color:red;font-weight:bold;">' . esc_html__('Rejected', 'tour-booking-manager') . '</span>';
                            } else {
                                $cancel_status = '<span style="color:orange;font-weight:bold;">' . esc_html__('Pending for approval', 'tour-booking-manager') . '</span>';
                            }
                        } elseif ($order->get_status() !== 'cancelled' && $order->get_status() !== 'refunded' && $now < $cancel_deadline) {
                            $can_cancel = true;
                        }
                        echo '<tr class="ttbm_total_booking_tr">';
                        echo '<td class="ttbm_total_booking_td">#' . esc_html($order_id) . '</td>';
                        echo '<td class="ttbm_total_booking_td"><a href="' . esc_url($tour_link) . '" target="_blank" style="color:#3174d0;font-weight:500;">' . esc_html($tour_title) . '</a></td>';
                        echo '<td class="ttbm_total_booking_td">' . esc_html($order_date) . '</td>';
                        echo '<td class="ttbm_total_booking_td">' . esc_html($status) . '</td>';
                        echo '<td class="ttbm_total_booking_td">' . wp_kses_post($amount) . '</td>';
                        echo '<td class="ttbm_total_booking_td" style="color:#3174d0;font-weight:500;">' . esc_html($cancel_deadline_str) . '</td>';
                        echo '<td class="ttbm_total_booking_td">';
                        if ($cancel_status) {
                            echo $cancel_status;
                        } elseif ($can_cancel) {
                            echo '<button class="ttbm_total_booking_filter_btn ttbm-cancel-btn" data-order="' . esc_attr($order_id) . '" data-tour="' . esc_attr($ttbm_id) . '" style="background:#3174d0;color:#fff;">' . esc_html__('Cancel', 'tour-booking-manager') . '</button>';
                        } else {
                            echo '<span style="color:#aaa;">' . esc_html__('Not eligible', 'tour-booking-manager') . '</span>';
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                }
            }
        } else {
            echo '<tr><td colspan="7">' . esc_html__('No tour orders found.', 'tour-booking-manager') . '</td></tr>';
        }
        ?>
        </tbody>
    </table>
    <div id="ttbm-cancel-success" style="display:none;margin-bottom:20px;padding:12px 18px;background:#e6f9e6;border-left:4px solid #2ecc40;color:#222;font-size:16px;border-radius:4px;"></div>
</div>
<!-- Modal for cancellation reason -->
<div id="ttbm-cancel-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:30px 20px;max-width:400px;margin:auto;border-radius:8px;position:relative;box-shadow:0 4px 24px rgba(0,0,0,0.12);">
        <h3 style="color:#3174d0;margin-bottom:15px;"><?php esc_html_e('Request Cancellation', 'tour-booking-manager'); ?></h3>
        <form id="ttbm-cancel-form">
            <input type="hidden" name="order_id" id="ttbm-cancel-order-id" value="" />
            <input type="hidden" name="tour_id" id="ttbm-cancel-tour-id" value="" />
            <label for="ttbm-cancel-reason" style="font-weight:500;margin-bottom:5px;display:block;"><?php esc_html_e('Reason for cancellation', 'tour-booking-manager'); ?></label>
            <textarea name="reason" id="ttbm-cancel-reason" rows="4" style="width:100%;margin-bottom:15px;border-radius:4px;border:1px solid #ccc;"></textarea>
            <button type="submit" class="ttbm_total_booking_filter_btn" style="background:#3174d0;color:#fff;min-width:120px;"><?php esc_html_e('Submit Request', 'tour-booking-manager'); ?></button>
            <button type="button" id="ttbm-cancel-close" class="ttbm_total_booking_reset_btn" style="margin-left:10px;background:#eee;color:#222;">&times; <?php esc_html_e('Close', 'tour-booking-manager'); ?></button>
        </form>
    </div>
</div>
<script type="text/javascript">var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('ttbm-cancel-modal');
    var closeBtn = document.getElementById('ttbm-cancel-close');
    var successBox = document.getElementById('ttbm-cancel-success');
    document.querySelectorAll('.ttbm-cancel-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('ttbm-cancel-order-id').value = btn.getAttribute('data-order');
            document.getElementById('ttbm-cancel-tour-id').value = btn.getAttribute('data-tour');
            modal.style.display = 'flex';
        });
    });
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    document.getElementById('ttbm-cancel-form').addEventListener('submit', function(e) {
        e.preventDefault();
        var order_id = document.getElementById('ttbm-cancel-order-id').value;
        var tour_id = document.getElementById('ttbm-cancel-tour-id').value;
        var reason = document.getElementById('ttbm-cancel-reason').value;
        var btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Submitting...';
        var errorBox = successBox;
        errorBox.style.display = 'none';
        fetch(ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=ttbm_submit_cancel_request&order_id=' + encodeURIComponent(order_id) + '&tour_id=' + encodeURIComponent(tour_id) + '&reason=' + encodeURIComponent(reason)
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.textContent = 'Submit';
            if (data.success) {
                var successBox = document.getElementById('ttbm-cancel-success');
                successBox.textContent = data.message;
                successBox.style.display = 'block';
                setTimeout(function(){ successBox.style.display = 'none'; }, 4000);
                // Update table row
                var row = document.querySelector('.ttbm_total_booking_tr[data-order="' + order_id + '"]');
                if (row) {
                    var actionCell = row.querySelector('.ttbm_total_booking_td:nth-child(7)');
                    if (actionCell) {
                        actionCell.innerHTML = '<span style="color:orange;font-weight:bold;">' + data.message + '</span>';
                        actionCell.style.color = '#3174d0';
                        actionCell.style.fontWeight = 'bold';
                    }
                }
                // Disable cancel button
                var cancelBtn = document.querySelector('.ttbm-cancel-btn[data-order="' + order_id + '"]');
                if (cancelBtn) {
                    cancelBtn.disabled = true;
                    cancelBtn.style.backgroundColor = '#ccc';
                    cancelBtn.style.color = '#999';
                    cancelBtn.textContent = 'Cancelled';
                }
                // Close the modal after success
                modal.style.display = 'none';
            } else {
                errorBox.textContent = data.message;
                errorBox.style.color = '#ff3333';
                errorBox.style.borderColor = '#ff3333';
                errorBox.style.display = 'block';
            }
        });
    });
});
</script> 