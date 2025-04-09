<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

/**
 * Print Button Template
 *
 * This template displays a print button that links to the tour print template.
 */

$ttbm_post_id = $ttbm_post_id ?? get_the_id();
$tour_id = $tour_id ?? TTBM_Function::post_id_multi_language($ttbm_post_id);
$print_url = add_query_arg(array('action' => 'ttbm_print_tour', 'tour_id' => $tour_id), admin_url('admin-ajax.php'));
?>

<div class="ttbm_print_button">
    <a href="<?php echo esc_url($print_url); ?>" target="_blank" class="ttbm_print_link">
        <i class="fas fa-print"></i> <?php esc_html_e('Print Tour Details', 'tour-booking-manager'); ?>
    </a>
</div>

<style>
    .ttbm_print_button {
        margin: 10px 0;
    }
    .ttbm_print_link {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, #f12971 0%, #e91e63 100%);
        color: white !important;
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 3px 10px rgba(241, 41, 113, 0.2);
    }
    .ttbm_print_link:hover {
        background: linear-gradient(135deg, #e91e63 0%, #f12971 100%);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(241, 41, 113, 0.3);
    }
    .ttbm_print_link i {
        margin-right: 8px;
    }
</style>
