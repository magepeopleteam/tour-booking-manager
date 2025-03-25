<?php

/**
 * Get Enquiry
 * @package Tour Booking Manager
 * @version 1.0
 * @author Shahadat Hossain <raselsha@gmail.com>
 * 
 */
if (! defined('ABSPATH')) die;
if (! class_exists('TTBM_Get_Enquiry')) {
    class TTBM_Get_Enquiry
    {
        public function __construct()
        {
            add_action('init', [$this, 'register_enquiry_post_type']);
            add_action('admin_menu', [$this, 'get_enquiry_admin_menu']);
            add_action('ttbm_enquery_popup', [$this, 'get_enquiry_pop_up']);
            add_action('ttbm_enquery_popup_button', [$this, 'get_enquiry_button']);
            add_action('wp_ajax_ttbm_enquiry_form_submit', [$this, 'enquiry_form_submit']);
            add_action('wp_ajax_noprev_ttbm_enquiry_form_submit', [$this, 'enquiry_form_submit']);
            add_action('wp_ajax_ttbm_delete_enquiry', [$this, 'delete_enquiry']);
        }

        public function delete_enquiry(){
            if (!isset($_POST['enquiry_id']) || !is_numeric($_POST['enquiry_id'])) {
                wp_send_json_error(['message' => __('Invalid enquiry ID.', 'tour-booking-manager')]);
            }

            $enquiry_id = intval($_POST['enquiry_id']);

            $post = get_post($enquiry_id);
            if ($post && $post->post_type === 'ttbm_enquiry' && wp_delete_post($enquiry_id, true)) {
                wp_send_json_success(['message' => __('Enquiry deleted successfully.', 'tour-booking-manager')]);
            } else {
                wp_send_json_error(['message' => __('Failed to delete enquiry. Please try again.', 'tour-booking-manager')]);
            }
            die;
        }
        public function enquiry_form_submit(){
            if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_frontend_nonce')) {
                wp_send_json_error(['message' => __('Failed to submit enquiry. Please try again.', 'tour-booking-manager')]);
			}
            $form_data = [];
            parse_str($_POST['data'], $form_data);
            $name = sanitize_text_field($form_data['name'] ?? '');
            $email = sanitize_email($form_data['email'] ?? '');
            $subject = sanitize_text_field($form_data['subject'] ?? '');
            $message = sanitize_textarea_field($form_data['message'] ?? '');
            $enquiry_id = wp_insert_post([
                'post_title'   => $subject,
                'post_content' => $message,
                'post_type'    => 'ttbm_enquiry',
                'post_status'  => 'publish',
                'meta_input'   => [
                    'name'   => $name,
                    'email'   => $email,
                ],
            ]);

            if ($enquiry_id) {
                wp_send_json_success(['message' => __('Enquiry submitted successfully.', 'tour-booking-manager')]);
            } else {
                wp_send_json_error(['message' => __('Failed to submit enquiry. Please try again.', 'tour-booking-manager')]);
            }
            die;
        }
        public function get_enquiry_button(){
            ?>
            <button type="button" class="_dButton_fullWidth" data-target-popup="get-enquiry-popup">
                <span class="far fa-envelope"></span>
                <?php esc_html_e('Get Enquiry','tour-booking-manager'); ?>				
            </button>
            <?php
        }

        public function get_enquiry_admin_menu()
        {
            add_submenu_page('edit.php?post_type=ttbm_tour', 'Enquiry', 'Enquiry', 'manage_options', 'ttbm_get_enquiry', [$this, 'get_enquiry_page']);
        }
        public function register_enquiry_post_type()
        {
            $labels = [
                'name'               => _x('Enquiries', 'post type general name', 'tour-booking-manager'),
                'singular_name'      => _x('Enquiry', 'post type singular name', 'tour-booking-manager'),
                'menu_name'          => _x('Enquiries', 'admin menu', 'tour-booking-manager'),
                'name_admin_bar'     => _x('Enquiry', 'add new on admin bar', 'tour-booking-manager'),
                'add_new'            => _x('Add New', 'enquiry', 'tour-booking-manager'),
                'add_new_item'       => __('Add New Enquiry', 'tour-booking-manager'),
                'new_item'           => __('New Enquiry', 'tour-booking-manager'),
                'edit_item'          => __('Edit Enquiry', 'tour-booking-manager'),
                'view_item'          => __('View Enquiry', 'tour-booking-manager'),
                'all_items'          => __('All Enquiries', 'tour-booking-manager'),
                'search_items'       => __('Search Enquiries', 'tour-booking-manager'),
                'parent_item_colon'  => __('Parent Enquiries:', 'tour-booking-manager'),
                'not_found'          => __('No enquiries found.', 'tour-booking-manager'),
                'not_found_in_trash' => __('No enquiries found in Trash.', 'tour-booking-manager')
            ];

            $args = [
                'labels'             => $labels,
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'query_var'          => true,
                'rewrite'            => ['slug' => 'ttbm_enquiry'],
                'capability_type'    => 'post',
                'has_archive'        => true,
                'hierarchical'       => false,
                'menu_position'      => null,
                'supports'           => ['title', 'editor', 'custom-fields'],
                'capabilities'       => [
                    'create_posts' => 'do_not_allow',
                ],
                'map_meta_cap'       => true,
            ];

            register_post_type('ttbm_enquiry', $args);
        }

        public function get_enquiry_pop_up($tour_id) {
            ?>
                <div class="mpPopup mpStyle" data-popup="get-enquiry-popup">
                    <div class="popupMainArea">
                        <div class="popupHeader allCenter">
                            <h2 class="_mR"><?php esc_html_e('Get Enquiry', 'bus-ticket-booking-with-seat-reservation'); ?></h2>
                            <span class="fas fa-times popupClose"></span>
                        </div>
                        <div class="popupBody">
                            <div class="ajax-response"></div>
                            <form method="post" id="ttbm-enquiry-form">
                                <fieldset>
                                <legend><?php esc_html_e('Enquiry', 'tour-booking-manager'); ?></legend>
                                <div class="get-enquiry-form">
                                    <label for="name"><?php esc_html_e('Name:', 'tour-booking-manager'); ?></label>
                                    <input type="text" name="name" id="name" placeholder="<?php esc_attr_e('Your Name', 'tour-booking-manager'); ?>" required>

                                    <label for="email"><?php esc_html_e('Email:', 'tour-booking-manager'); ?></label>
                                    <input type="email" name="email" id="email" placeholder="<?php esc_attr_e('Your Email', 'tour-booking-manager'); ?>" required>

                                    <label for="subject"><?php esc_html_e('Subject:', 'tour-booking-manager'); ?></label>
                                    <input type="text" name="subject" id="subject" placeholder="<?php esc_attr_e('Subject', 'tour-booking-manager'); ?>" required>

                                    <label for="message"><?php esc_html_e('Message:', 'tour-booking-manager'); ?></label>
                                    <textarea name="message" id="message" placeholder="<?php esc_attr_e('Your Message', 'tour-booking-manager'); ?>" rows="5" required></textarea>

                                    <button class="_dButton_fullWidth" id="ttbm-enquiry-form-submit"><?php esc_html_e('Send Message', 'tour-booking-manager'); ?></button>
                                </div>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
            <?php
        }

        public function get_enquiry_page()
        {
        ?>
            <div class="wrap">
                <h1 class="wp-heading-inline">Enquiry</h1>
                <hr class="wp-header-end">
                <h2 class="nav-tab-wrapper">
                    <a href="#tab1" class="nav-tab nav-tab-active">Enquiry List</a>
                    <a href="#tab2" class="nav-tab">Enquiry Settings</a>
                </h2>
                <div id="tab1" class="tab-content" style="display: block;">
                    <div class="wrap">
                        <table class="wp-list-table widefat fixed striped posts">
                            <thead>
                                <tr>
                                    <th class="manage-column column-title">Subject</th>
                                    <th class="manage-column column-title">Name</th>
                                    <th class="manage-column column-title">Email</th>
                                    <th class="manage-column column-title">Message</th>
                                    <th class="manage-column column-title">Date</th>
                                    <th class="manage-column column-title">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $args = [
                                    'post_type' => 'ttbm_enquiry',
                                    'post_status' => 'publish',
                                    'posts_per_page' => -1
                                ];
                                $enquiry = new WP_Query($args);
                                if ($enquiry->have_posts()) {
                                    while ($enquiry->have_posts()) {
                                        $enquiry->the_post();
                                ?>
                                <tr>
                                    <td><?php the_title(); ?></td>
                                    <td><?php echo esc_html(get_post_meta(get_the_ID(), 'name', true)); ?></td>
                                    <td><?php echo esc_html(get_post_meta(get_the_ID(), 'email', true)); ?></td>
                                    <td><?php echo esc_html(get_the_content()); ?></td>
                                    <td><?php echo get_the_date() . ' ' . get_the_time(); ?></td>
                                    <td>
                                        <a href="#" class="ttbm-delete-enquiry" data-id="<?php echo get_the_ID(); ?>">Delete</a>
                                    </td>
                                </tr>
                                <?php
                                    }
                                    wp_reset_postdata();
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="5"><?php esc_html_e('No enquiries found.', 'tour-booking-manager'); ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="tab2" class="tab-content" style="display: none;">
                    <div id="col-left">
                        <div class="col-wrap">
                            <div class="form-wrap">
                                <h2>Enquiry Settings</h2>
                                <form id="addtag" method="post" action="">
                                    <div class="form-field term-name-wrap">
                                        <label for="name">Name</label>
                                        <input name="name" id="name" type="text" value="" size="40" aria-required="true">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        }
    }
    new TTBM_Get_Enquiry();
}
