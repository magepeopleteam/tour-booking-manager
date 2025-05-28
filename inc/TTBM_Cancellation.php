<?php
// All cancellation request management code moved here from tour-booking-manager.php
if (!defined('ABSPATH')) { die; }

// AJAX handler for cancellation request
add_action('wp_ajax_ttbm_submit_cancel_request', function() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => __('You must be logged in.', 'tour-booking-manager')]);
    }
    $user_id = get_current_user_id();
    $order_id = intval($_POST['order_id'] ?? 0);
    $tour_id = intval($_POST['tour_id'] ?? 0);
    $reason = sanitize_textarea_field($_POST['reason'] ?? '');
    if (!$order_id || !$tour_id || empty($reason)) {
        wp_send_json_error(['message' => __('Missing data.', 'tour-booking-manager')]);
    }
    // Prevent duplicate requests
    $existing = get_posts([
        'post_type' => 'ttbm_cancel_request',
        'post_status' => ['publish', 'pending', 'draft'],
        'meta_query' => [
            ['key' => 'order_id', 'value' => $order_id],
            ['key' => 'tour_id', 'value' => $tour_id],
            ['key' => 'user_id', 'value' => $user_id],
        ],
        'numberposts' => 1
    ]);
    if ($existing) {
        wp_send_json_error(['message' => __('A cancellation request already exists for this order.', 'tour-booking-manager')]);
    }
    $post_id = wp_insert_post([
        'post_type' => 'ttbm_cancel_request',
        'post_status' => 'publish',
        'post_title' => 'Cancel Request #' . $order_id,
        'post_content' => $reason,
        'post_author' => $user_id,
    ]);
    if ($post_id) {
        update_post_meta($post_id, 'order_id', $order_id);
        update_post_meta($post_id, 'tour_id', $tour_id);
        update_post_meta($post_id, 'user_id', $user_id);
        update_post_meta($post_id, 'reason', $reason);
        update_post_meta($post_id, 'cancel_status', 'pending');
        ttbm_send_cancel_email('admin_new', [
            'order_id' => $order_id,
            'tour_id' => $tour_id,
            'user_id' => $user_id,
            'reason' => $reason,
        ]);
        wp_send_json_success(['message' => __('Cancellation request submitted.', 'tour-booking-manager')]);
    } else {
        wp_send_json_error(['message' => __('Failed to submit request.', 'tour-booking-manager')]);
    }
});
add_action('wp_ajax_nopriv_ttbm_submit_cancel_request', function() {
    wp_send_json_error(['message' => __('You must be logged in.', 'tour-booking-manager')]);
});

// Admin menu for Cancellation Requests
add_action('admin_menu', function() {
    add_submenu_page(
        'edit.php?post_type=ttbm_tour',
        __('Cancellation Requests', 'tour-booking-manager'),
        __('Cancellation Requests', 'tour-booking-manager'),
        'manage_options',
        'ttbm_cancel_requests',
        'ttbm_render_cancel_requests_admin_page'
    );
});

// Email sending function
function ttbm_send_cancel_email($type, $args) {
    $settings = get_option('ttbm_basic_gen_settings', []);
    $email_settings = get_option('ttbm_cancel_email_settings', []);
    $admin_email = get_option('admin_email');
    $order_id = $args['order_id'] ?? '';
    $tour_id = $args['tour_id'] ?? '';
    $user_id = $args['user_id'] ?? '';
    $reason = $args['reason'] ?? '';
    $order = $order_id ? wc_get_order($order_id) : null;
    $tour_title = $tour_id ? get_the_title($tour_id) : '';
    $customer_email = $order ? $order->get_billing_email() : '';
    $customer_name = $order ? $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() : '';
    $tags = [
        '{customer_name}' => sanitize_text_field($customer_name),
        '{order_id}' => sanitize_text_field($order_id),
        '{tour_title}' => sanitize_text_field($tour_title),
        '{reason}' => sanitize_textarea_field($reason),
    ];
    // Admin new request
    if ($type === 'admin_new' && ($email_settings['ttbm_notify_admin_cancel_request'] ?? 'on') === 'on') {
        $subject = $email_settings['ttbm_cancel_admin_subject'] ?? __('New Cancellation Request for Order #{order_id}', 'tour-booking-manager');
        $body = $email_settings['ttbm_cancel_admin_body'] ?? '';
        $subject = strtr($subject, $tags);
        $body = strtr($body, $tags);
        $body = wpautop($body);
        add_filter('wp_mail_content_type', 'ttbm_set_html_mail_content_type');
        wp_mail(sanitize_email($admin_email), wp_kses_post($subject), $body);
        remove_filter('wp_mail_content_type', 'ttbm_set_html_mail_content_type');
    }
    // Customer new request (confirmation) - only if enabled
    if ($type === 'admin_new' && ($email_settings['ttbm_notify_customer_cancel_request'] ?? 'on') === 'on' && $customer_email) {
        $subject = __('Your cancellation request has been received (Order #{order_id})', 'tour-booking-manager');
        $body = __('We have received your cancellation request for tour "{tour_title}" (Order #{order_id}). Our team will review and notify you soon.\nThank you.', 'tour-booking-manager');
        $subject = strtr($subject, $tags);
        $body = strtr($body, $tags);
        $body = wpautop($body);
        add_filter('wp_mail_content_type', 'ttbm_set_html_mail_content_type');
        wp_mail(sanitize_email($customer_email), wp_kses_post($subject), $body);
        remove_filter('wp_mail_content_type', 'ttbm_set_html_mail_content_type');
    }
    // Customer approved
    if ($type === 'customer_approved' && ($email_settings['ttbm_notify_customer_cancel_approved'] ?? 'on') === 'on' && $customer_email) {
        $subject = $email_settings['ttbm_cancel_customer_approved_subject'] ?? __('Your Cancellation Request Approved (Order #{order_id})', 'tour-booking-manager');
        $body = $email_settings['ttbm_cancel_customer_approved_body'] ?? '';
        $subject = strtr($subject, $tags);
        $body = strtr($body, $tags);
        $body = wpautop($body);
        add_filter('wp_mail_content_type', 'ttbm_set_html_mail_content_type');
        wp_mail(sanitize_email($customer_email), wp_kses_post($subject), $body);
        remove_filter('wp_mail_content_type', 'ttbm_set_html_mail_content_type');
    }
    // Customer rejected
    if ($type === 'customer_rejected' && ($email_settings['ttbm_notify_customer_cancel_rejected'] ?? 'on') === 'on' && $customer_email) {
        $subject = $email_settings['ttbm_cancel_customer_rejected_subject'] ?? __('Your Cancellation Request Rejected (Order #{order_id})', 'tour-booking-manager');
        $body = $email_settings['ttbm_cancel_customer_rejected_body'] ?? '';
        $subject = strtr($subject, $tags);
        $body = strtr($body, $tags);
        $body = wpautop($body);
        add_filter('wp_mail_content_type', 'ttbm_set_html_mail_content_type');
        wp_mail(sanitize_email($customer_email), wp_kses_post($subject), $body);
        remove_filter('wp_mail_content_type', 'ttbm_set_html_mail_content_type');
    }
}

function ttbm_render_cancel_requests_admin_page() {
    if (!current_user_can('manage_options')) return;
    // Handle Bulk Delete
    if (isset($_POST['ttbm_bulk_action']) && check_admin_referer('ttbm_cancel_bulk_action')) {
        $action = sanitize_text_field($_POST['ttbm_bulk_action']);
        $ids = isset($_POST['ttbm_request_ids']) ? array_map('intval', (array)$_POST['ttbm_request_ids']) : [];
        if ($action === 'delete' && $ids) {
            foreach ($ids as $id) {
                wp_delete_post($id, true);
            }
            echo '<div class="updated"><p>' . esc_html__('Selected requests deleted.', 'tour-booking-manager') . '</p></div>';
        }
    }
    // Handle Single Delete
    if (isset($_GET['ttbm_single_delete'], $_GET['request_id']) && check_admin_referer('ttbm_single_delete_' . $_GET['request_id'])) {
        $id = intval($_GET['request_id']);
        wp_delete_post($id, true);
        echo '<div class="updated"><p>' . esc_html__('Request deleted.', 'tour-booking-manager') . '</p></div>';
    }
    // Handle Export CSV
    if (isset($_GET['ttbm_export']) && $_GET['ttbm_export'] === 'csv' && check_admin_referer('ttbm_export_csv')) {
        if (ob_get_length()) ob_end_clean(); // Discard all previous output
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="cancellation-requests.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        $requests = get_posts([
            'post_type' => 'ttbm_cancel_request',
            'post_status' => ['publish', 'pending', 'draft'],
            'numberposts' => 1000,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Order ID', 'Tour', 'Customer', 'Reason', 'Date', 'Status']);
        foreach ($requests as $req) {
            $order_id = get_post_meta($req->ID, 'order_id', true);
            $tour_id = get_post_meta($req->ID, 'tour_id', true);
            $user_id = get_post_meta($req->ID, 'user_id', true);
            $reason = get_post_meta($req->ID, 'reason', true);
            $status = get_post_meta($req->ID, 'cancel_status', true);
            $date = get_the_date('', $req->ID);
            $tour_title = $tour_id ? get_the_title($tour_id) : '-';
            $user = $user_id ? get_userdata($user_id) : null;
            $user_display = $user ? $user->display_name . ' (' . $user->user_email . ')' : '-';
            fputcsv($output, [
                '#' . $order_id,
                $tour_title,
                $user_display,
                $reason,
                $date,
                ucfirst($status)
            ]);
        }
        fclose($output);
        exit;
    }
    // Handle Export PDF (fallback to styled HTML if mPDF not available)
    if (isset($_GET['ttbm_export']) && $_GET['ttbm_export'] === 'pdf' && check_admin_referer('ttbm_export_pdf')) {
        if (ob_get_length()) ob_end_clean(); // Discard all previous output
        $requests = get_posts([
            'post_type' => 'ttbm_cancel_request',
            'post_status' => ['publish', 'pending', 'draft'],
            'numberposts' => 1000,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
        $html = '<h2 style="font-family:sans-serif;">Cancellation Requests</h2>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse;font-family:sans-serif;width:100%;">';
        $html .= '<thead style="background:#f5f5f5;"><tr>';
        $html .= '<th>Order ID</th><th>Tour</th><th>Customer</th><th>Reason</th><th>Date</th><th>Status</th></tr></thead><tbody>';
        foreach ($requests as $req) {
            $order_id = get_post_meta($req->ID, 'order_id', true);
            $tour_id = get_post_meta($req->ID, 'tour_id', true);
            $user_id = get_post_meta($req->ID, 'user_id', true);
            $reason = get_post_meta($req->ID, 'reason', true);
            $status = get_post_meta($req->ID, 'cancel_status', true);
            $date = get_the_date('', $req->ID);
            $tour_title = $tour_id ? get_the_title($tour_id) : '-';
            $user = $user_id ? get_userdata($user_id) : null;
            $user_display = $user ? $user->display_name . ' (' . $user->user_email . ')' : '-';
            $html .= '<tr>';
            $html .= '<td>#' . esc_html($order_id) . '</td>';
            $html .= '<td>' . esc_html($tour_title) . '</td>';
            $html .= '<td>' . esc_html($user_display) . '</td>';
            $html .= '<td>' . esc_html($reason) . '</td>';
            $html .= '<td>' . esc_html($date) . '</td>';
            $html .= '<td>' . esc_html(ucfirst($status)) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        if (class_exists('Mpdf\\Mpdf')) {
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML($html);
            $mpdf->Output('cancellation-requests.pdf', 'D');
            exit;
        } else {
            // Fallback: Output styled HTML in a new tab (no PDF headers)
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Cancellation Requests</title>';
            echo '<style>body{font-family:sans-serif;margin:40px;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #ccc;padding:8px;}th{background:#f5f5f5;}</style>';
            echo '</head><body>';
            echo $html;
            echo '<p style="margin-top:30px;color:#888;font-size:14px;">PDF export is not available on this server. You can print or save this page as PDF using your browser (Ctrl+P).</p>';
            echo '</body></html>';
            exit;
        }
    }
    // Handle Approve/Reject
    if (isset($_GET['ttbm_cancel_action'], $_GET['request_id']) && check_admin_referer('ttbm_cancel_action')) {
        $id = intval($_GET['request_id']);
        $action = sanitize_text_field($_GET['ttbm_cancel_action']);
        $order_id = get_post_meta($id, 'order_id', true);
        $tour_id = get_post_meta($id, 'tour_id', true);
        $user_id = get_post_meta($id, 'user_id', true);
        $reason = get_post_meta($id, 'reason', true);
        if ($action === 'approve') {
            update_post_meta($id, 'cancel_status', 'approved');
            // Set order status to cancelled
            if ($order_id) {
                $order = wc_get_order($order_id);
                if ($order && $order->get_status() !== 'cancelled') {
                    $order->update_status('cancelled', __('Order cancelled by admin approval', 'tour-booking-manager'));
                }
            }
            ttbm_send_cancel_email('customer_approved', [
                'order_id' => $order_id,
                'tour_id' => $tour_id,
                'user_id' => $user_id,
                'reason' => $reason,
            ]);
            echo '<div class="updated"><p>' . esc_html__('Cancellation request approved.', 'tour-booking-manager') . '</p></div>';
        } elseif ($action === 'reject') {
            update_post_meta($id, 'cancel_status', 'rejected');
            ttbm_send_cancel_email('customer_rejected', [
                'order_id' => $order_id,
                'tour_id' => $tour_id,
                'user_id' => $user_id,
                'reason' => $reason,
            ]);
            echo '<div class="updated"><p>' . esc_html__('Cancellation request rejected.', 'tour-booking-manager') . '</p></div>';
        }
    }
    // List all requests
    $requests = get_posts([
        'post_type' => 'ttbm_cancel_request',
        'post_status' => ['publish', 'pending', 'draft'],
        'numberposts' => 100,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);
    echo '<div class="wrap"><h1>' . esc_html__('Cancellation Requests', 'tour-booking-manager') . '</h1>';
    // Export/Actions Forms (separate for CSV and PDF)
    // CSV Export Form
    echo '<form method="get" style="margin-bottom:15px;display:inline-block;">';
    echo '<input type="hidden" name="post_type" value="ttbm_tour">';
    echo '<input type="hidden" name="page" value="ttbm_cancel_requests">';
    wp_nonce_field('ttbm_export_csv');
    echo '<button type="submit" name="ttbm_export" value="csv" class="button action" style="margin-right:8px;">' . esc_html__('Export CSV', 'tour-booking-manager') . '</button>';
    echo '</form>';
    // PDF Export Form
    echo '<form method="get" style="margin-bottom:15px;display:inline-block;">';
    echo '<input type="hidden" name="post_type" value="ttbm_tour">';
    echo '<input type="hidden" name="page" value="ttbm_cancel_requests">';
    wp_nonce_field('ttbm_export_pdf');
    echo '<button type="submit" name="ttbm_export" value="pdf" class="button action">' . esc_html__('Export PDF', 'tour-booking-manager') . '</button>';
    echo '</form>';
    // After the export forms, add a message for users
    echo '<div style="margin-bottom:20px;color:#666;font-size:14px;">'
        . esc_html__('Tip: If the CSV opens in your browser, right-click and choose "Save As...". Open the file in Excel or Google Sheets for best results.', 'tour-booking-manager')
        . '</div>';
    // Bulk Actions Form
    echo '<form method="post" style="display:inline-block;margin-left:20px;">';
    wp_nonce_field('ttbm_cancel_bulk_action');
    echo '<select name="ttbm_bulk_action" class="action" style="margin-right:8px;"><option value="">' . esc_html__('Bulk Actions', 'tour-booking-manager') . '</option><option value="delete">' . esc_html__('Delete', 'tour-booking-manager') . '</option></select>';
    echo '<button type="submit" class="button action">' . esc_html__('Apply', 'tour-booking-manager') . '</button>';
    // Table
    echo '<table class="widefat fixed striped"><thead><tr>';
    echo '<th><input type="checkbox" id="ttbm-select-all"></th>';
    echo '<th>' . esc_html__('Order ID', 'tour-booking-manager') . '</th>';
    echo '<th>' . esc_html__('Tour', 'tour-booking-manager') . '</th>';
    echo '<th>' . esc_html__('Customer', 'tour-booking-manager') . '</th>';
    echo '<th>' . esc_html__('Reason', 'tour-booking-manager') . '</th>';
    echo '<th>' . esc_html__('Date', 'tour-booking-manager') . '</th>';
    echo '<th>' . esc_html__('Status', 'tour-booking-manager') . '</th>';
    echo '<th>' . esc_html__('Actions', 'tour-booking-manager') . '</th>';
    echo '</tr></thead><tbody>';
    if ($requests) {
        foreach ($requests as $req) {
            $order_id = get_post_meta($req->ID, 'order_id', true);
            $tour_id = get_post_meta($req->ID, 'tour_id', true);
            $user_id = get_post_meta($req->ID, 'user_id', true);
            $reason = get_post_meta($req->ID, 'reason', true);
            $status = get_post_meta($req->ID, 'cancel_status', true);
            $date = get_the_date('', $req->ID);
            $order_link = $order_id ? '<a href="' . esc_url(admin_url('post.php?post=' . $order_id . '&action=edit')) . '" target="_blank">#' . esc_html($order_id) . '</a>' : '-';
            $tour_link = $tour_id ? '<a href="' . esc_url(get_edit_post_link($tour_id)) . '" target="_blank">' . esc_html(get_the_title($tour_id)) . '</a>' : '-';
            $user = $user_id ? get_userdata($user_id) : null;
            $user_display = $user ? esc_html($user->display_name . ' (' . $user->user_email . ')') : '-';
            $actions = '';
            if ($status === 'pending' || !$status) {
                $approve_url = wp_nonce_url(admin_url('edit.php?post_type=ttbm_tour&page=ttbm_cancel_requests&ttbm_cancel_action=approve&request_id=' . $req->ID), 'ttbm_cancel_action');
                $reject_url = wp_nonce_url(admin_url('edit.php?post_type=ttbm_tour&page=ttbm_cancel_requests&ttbm_cancel_action=reject&request_id=' . $req->ID), 'ttbm_cancel_action');
                $actions = '<a href="' . esc_url($approve_url) . '" class="button button-primary" style="margin-right:5px;">' . esc_html__('Approve', 'tour-booking-manager') . '</a>';
                $actions .= '<a href="' . esc_url($reject_url) . '" class="button">' . esc_html__('Reject', 'tour-booking-manager') . '</a>';
            } else {
                $actions = esc_html(ucfirst($status));
            }
            // Single delete button
            $delete_url = wp_nonce_url(admin_url('edit.php?post_type=ttbm_tour&page=ttbm_cancel_requests&ttbm_single_delete=1&request_id=' . $req->ID), 'ttbm_single_delete_' . $req->ID);
            echo '<tr>';
            echo '<td><input type="checkbox" name="ttbm_request_ids[]" value="' . esc_attr($req->ID) . '"></td>';
            echo '<td>' . $order_link . '</td>';
            echo '<td>' . $tour_link . '</td>';
            echo '<td>' . $user_display . '</td>';
            echo '<td>' . esc_html($reason) . '</td>';
            echo '<td>' . esc_html($date) . '</td>';
            echo '<td>' . esc_html(ucfirst($status)) . '</td>';
            echo '<td>' . $actions . ' <a href="' . esc_url($delete_url) . '" class="button button-link-delete" onclick="return confirm(\'Are you sure you want to delete this request?\');">' . esc_html__('Delete', 'tour-booking-manager') . '</a></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="8">' . esc_html__('No cancellation requests found.', 'tour-booking-manager') . '</td></tr>';
    }
    echo '</tbody></table>';
    echo '</form>';
    // JS for select all
    echo '<script>document.getElementById("ttbm-select-all").addEventListener("change",function(){var cbs=document.querySelectorAll("input[name=\\"ttbm_request_ids[]\\"]");for(var i=0;i<cbs.length;i++){cbs[i].checked=this.checked;}});</script>';
    echo '</div>';
}

// Add a named function for HTML email content type
function ttbm_set_html_mail_content_type() { return 'text/html'; }