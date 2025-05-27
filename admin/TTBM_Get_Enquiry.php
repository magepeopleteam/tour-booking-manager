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
            add_action('wp_ajax_ttbm_view_enquiry', [$this, 'view_enquiry']);
            add_action('wp_ajax_ttbm_delete_enquiry', [$this, 'delete_enquiry']);

            add_action('wp_ajax_ttbm_reply_enquiry', [$this, 'reply_enquiry']);
        }

        public function view_enquiry(){
            $enquiry_id = intval($_POST['enquiry_id']);
            $post = get_post($enquiry_id);

            if ($post && $post->post_type === 'ttbm_enquiry') {
                $response = [
                    'title'   => esc_html($post->post_title),
                    'content' => esc_html($post->post_content),
                    'date'    => get_the_date('F j, Y', $post),
                    'time'    => get_the_time('g:i a', $post),
                    'name'    => esc_html(get_post_meta($enquiry_id, 'name', true)),
                    'email'   => esc_html(get_post_meta($enquiry_id, 'email', true)),
                    'status'   => esc_html(get_post_meta($enquiry_id, 'status', true)),
                ];
                if($response['status']=='new'){
                    update_post_meta($enquiry_id, 'status', 'viewed');
                }
                ob_start();
                ?>
                <table>
                    <tr>
                        <th><?php esc_html_e('Subject:', 'tour-booking-manager'); ?></th>
                        <td><?php echo $response['title']; ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Date:', 'tour-booking-manager'); ?></th>
                        <td><?php echo $response['date']; ?> <?php echo $response['time']; ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Name:', 'tour-booking-manager'); ?></th>
                        <td><?php echo $response['name']; ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Email:', 'tour-booking-manager'); ?></th>
                        <td><?php echo $response['email']; ?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Message:', 'tour-booking-manager'); ?></th>
                        <td><?php echo $response['content']; ?></td>
                    </tr>
                </table>
                <?php
                $html = ob_get_clean();
                wp_send_json_success(['html' => $html]);
            } else {
                wp_send_json_error(['message' => __('Enquiry not found.', 'tour-booking-manager')]);
            }
            die;
        }

        public function delete_enquiry(){
            $enquiry_id = intval($_POST['enquiry_id']);
            $post = get_post($enquiry_id);
            if ($post && $post->post_type === 'ttbm_enquiry' && wp_delete_post($enquiry_id, true)) {
                wp_send_json_success(['message' => __('Enquiry deleted successfully.', 'tour-booking-manager')]);
            } else {
                wp_send_json_error(['message' => __('Failed to delete enquiry. Please try again.', 'tour-booking-manager')]);
            }
            die;
        }
        public function reply_enquiry(){
            if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
                wp_send_json_error(['message' => __('Failed to submit enquiry. Please try again.', 'tour-booking-manager')]);
			}
            $form_data = [];
            parse_str($_POST['data'], $form_data);
            $postId=sanitize_text_field($form_data['ttbm_post_id'] ?? '');
            $from = sanitize_text_field($form_data['ttbm-reply-from'] ?? '');
            $to = sanitize_email($form_data['ttbm-reply-to'] ?? '');
            $subject = sanitize_text_field($form_data['ttbm-reply-subject'] ?? '');
            $message = sanitize_textarea_field($form_data['ttbm-reply-message'] ?? '');
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            if (wp_mail($to, $subject, $message, $headers)) {
                update_post_meta($postId, 'status', 'replied');
                wp_send_json_success(['message' => __('Message sent successfully!','tour-booking-manager')]);
            } else {
                wp_send_json_error(['message' => __('Failed to send email.','tour-booking-manager')]);
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
            $headers = ['Content-Type: text/html; charset=UTF-8'];
            $enquiry_id = wp_insert_post([
                'post_title'   => $subject,
                'post_content' => $message,
                'post_type'    => 'ttbm_enquiry',
                'post_status'  => 'publish',
                'meta_input'   => [
                    'name'   => $name,
                    'email'   => $email,
                    'status' => 'new',
                ],
            ]);
            $to = get_option('admin_email', 'admin@' . parse_url(get_site_url(), PHP_URL_HOST));
            wp_mail($to, $subject, $message, $headers);
            if ($enquiry_id) {
                wp_send_json_success(['message' => __('Enquiry submitted successfully.', 'tour-booking-manager')]);
            } else {
                wp_send_json_error(['message' => __('Failed to submit enquiry. Please try again.', 'tour-booking-manager')]);
            }
            die;
        }
        public function get_enquiry_button(){
            $display_enquiry = TTBM_Global_Function::get_post_info(get_the_ID(), 'ttbm_display_enquiry', 'on');
            if($display_enquiry == 'on'):
            ?>
            <div class="get-enquiry-popup">
                <button type="button" class="_dButton_fullWidth" data-target-popup="get-enquiry-popup">
                    <span class="far fa-envelope"></span>
                    <?php esc_html_e('Get Enquiry','tour-booking-manager'); ?>				
                </button>
            </div>
            <?php
            endif;
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
                'show_ui'            => false,
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

        public function reply_enquery_popup() {
            $from_email = get_option('admin_email', 'admin@' . parse_url(get_site_url(), PHP_URL_HOST));
            ?>
            <div class="ttbm_popup ttbm_style" data-popup="reply-enquiry-popup">
                <div class="popupMainArea">
                    <div class="popupHeader allCenter">
                        <h2 class="_mR"><?php esc_html_e('Reply', 'bus-ticket-booking-with-seat-reservation'); ?></h2>
                        <span class="fas fa-times popupClose"></span>
                    </div>
                    <div class="popupBody">
                        <div class="reply-ajax-response"></div>
                        <form method="post" id="ttbm-reply-enquiry-form">
                            <fieldset>
                                <div class="reply-enquiry-form">
                                    <input type="hidden" name="ttbm_post_id" id="ttbm-post-id" value="">
                                    <label for="name"><?php esc_html_e('From:', 'tour-booking-manager'); ?></label>
                                    <input type="text" name="ttbm-reply-from" id="ttbm-reply-from" value="<?php echo esc_attr($from_email); ?>" placeholder="<?php esc_attr_e('admin@gamil.com', 'tour-booking-manager'); ?>" required>

                                    <label for="email"><?php esc_html_e('To:', 'tour-booking-manager'); ?></label>
                                    <input type="email" name="ttbm-reply-to" id="ttbm-reply-to" placeholder="<?php esc_attr_e('To', 'tour-booking-manager'); ?>" required>

                                    <label for="subject"><?php esc_html_e('Subject:', 'tour-booking-manager'); ?></label>
                                    <input type="text" name="ttbm-reply-subject" id="ttbm-reply-subject" placeholder="<?php esc_attr_e('Subject', 'tour-booking-manager'); ?>" required>

                                    <label for="message"><?php esc_html_e('Message:', 'tour-booking-manager'); ?></label>
                                    <div class="ttbm-reply-message">
                                        <?php 
                                            $editor_id = 'ttbm-reply-message'; 
                                            $editor_content = ''; 
                                            $editor_settings = array(
                                                'textarea_name' => 'ttbm-reply-message',
                                                'media_buttons' => false,
                                                'teeny'         => false,
                                                'quicktags'     => true,
                                                'editor_height' => 200,
                                                'tinymce'       => array(
                                                    'toolbar1' => 'bold italic | bullist numlist | link unlink | undo redo',
                                                    'toolbar2' => '',
                                                )
                                            );
                                            wp_editor($editor_content, $editor_id, $editor_settings);
                                            ?>
                                    </div>
                                    <button class="_dButton_fullWidth" type="submit" id="ttbm-enquiry-form-reply"><?php esc_html_e('Send Message', 'tour-booking-manager'); ?></button>
                                </div>
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
            <?php
        }
        public function get_enquiry_pop_up($tour_id) {
            ?>
                <div class="ttbm_popup ttbm_style" data-popup="get-enquiry-popup">
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
            
            if (isset($_POST['ttbm_enquiry_submit']) and isset($_POST['ttbm_enquiry_nonce'])) {
                
                if (wp_verify_nonce($_POST['ttbm_enquiry_nonce'], 'ttbm_enquiry_nonce')) {
                    update_option('ttbm_enquiry_from_email', sanitize_email($_POST['ttbm_enquiry_from_email']));
                    
                } else {
                    wp_die(__('Nonce verification failed', 'tour-booking-manager'));
                }
            } 
            $from_email = get_option('ttbm_enquiry_from_email');
            if (empty($from_email)) {
                $from_email = get_option('admin_email', 'admin@' . parse_url(get_site_url(), PHP_URL_HOST));
            }
            $from_email = sanitize_email($from_email);        
        ?>
            <div id="ttbm-settings-page">
            <div class="wrap ">
                <h1 class="wp-heading-inline"><?php _e('Enquiry', 'tour-booking-manager'); ?></h1>
                <hr class="wp-header-end">
                <h2 class="nav-tab-wrapper">
                    <a href="#mptbm-enquiry-list" class="nav-tab nav-tab-active"><?php _e('Enquiry List', 'tour-booking-manager'); ?></a>
                    <a href="#mptbm-enquiry-settings" class="nav-tab"><?php _e('Enquiry Settings', 'tour-booking-manager'); ?></a>
                </h2>
                <div id="mptbm-enquiry-list" class="tab-content" style="display: block;">
                    <div class="wrap ">
                        <div class="ttbm_style">
                            <?php $this->reply_enquery_popup(); ?>
                            <div class="ttbm_popup ttbm_style" data-popup="view-enquiry-popup">
                                <div class="popupMainArea">
                                    <div class="popupHeader allCenter">
                                        <h2 class="_mR"><?php esc_html_e('View Enquiry', 'bus-ticket-booking-with-seat-reservation'); ?></h2>
                                        <span class="fas fa-times popupClose"></span>
                                    </div>
                                    <div class="popupBody">
                                        <div class="ttbm-view-enquiry-response"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <ul class="subsubsub">
                            <li class="all">
                                <a href="<?php echo esc_url(add_query_arg('post_type', 'ttbm_tour')); ?>" class="current" aria-current="page">
                                    <?php _e('All', 'tour-booking-manager'); ?>
                                    <span class="count">
                                        (<?php echo esc_html(wp_count_posts('ttbm_enquiry')->publish); ?>)
                                    </span>
                                </a>
                            </li>
                        </ul>

                        <form method="get" style="float:right;">
                            <input type="hidden" name="post_type" value="ttbm_tour">
                            <input type="hidden" name="page" value="ttbm_get_enquiry">
                            <input type="text" name="s" value="<?php echo esc_attr($_GET['s'] ?? ''); ?>" placeholder="Search enquiries..." />
                            <input type="submit" class="button" value="Search" />
                        </form>

                        <table class="wp-list-table widefat fixed striped posts ttbm-enquiry-table">
                            <thead>
                                <tr>
                                    <th class="manage-column column-title"><?php _e('Subject', 'tour-booking-manager'); ?></th>
                                    <th class="manage-column column-title"><?php _e('Name', 'tour-booking-manager'); ?></th>
                                    <th class="manage-column column-title"><?php _e('Email', 'tour-booking-manager'); ?></th>
                                    <th class="manage-column column-title"><?php _e('Message', 'tour-booking-manager'); ?></th>
                                    <th class="manage-column column-title"><?php _e('Status', 'tour-booking-manager'); ?></th>
                                    <th class="manage-column column-title"><?php _e('Date', 'tour-booking-manager'); ?></th>
                                    <th class="manage-column column-title"><?php _e('Action', 'tour-booking-manager'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
                                $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
                                $args = [
                                    'post_type'      => 'ttbm_enquiry',
                                    'post_status'    => 'publish',
                                    'posts_per_page' => 10,
                                    'paged'          => $paged,
                                ];
                                if (!empty($search)) {
                                    $args['s'] = $search;
                                }
                                $enquiry = new WP_Query($args);

                                if ($enquiry->have_posts()) :
                                    while ($enquiry->have_posts()) : $enquiry->the_post();
                                        $status = get_post_meta(get_the_ID(), 'status', true);
                                ?>
                                        <tr class="ttbm-enquiry-list" data-id="<?php echo get_the_ID(); ?>">
                                            <td class="<?php echo esc_attr($status === 'new' ? 'new' : ''); ?>">
                                                <?php
                                                $title = get_the_title();
                                                echo esc_html(mb_strimwidth($title, 0, 40, '...'));
                                                ?>
                                            </td>
                                            <td><?php echo esc_html(get_post_meta(get_the_ID(), 'name', true)); ?></td>
                                            <td><?php echo esc_html(get_post_meta(get_the_ID(), 'email', true)); ?></td>
                                            <td>
                                                <?php
                                                $message = get_the_content();
                                                echo esc_html(mb_strimwidth($message, 0, 40, '...'));
                                                ?>
                                            </td>
                                            <td class="<?php echo esc_attr($status); ?>"><?php echo esc_html(ucfirst($status)); ?></td>
                                            <td><?php echo esc_html(get_the_date() . ' ' . get_the_time()); ?></td>
                                            <td class="ttbm_style">
                                                <a href="#" class="ttbm-view-enquiry" data-id="<?php echo get_the_ID(); ?>" data-target-popup="view-enquiry-popup"><?php _e('View |', 'tour-booking-manager'); ?></a>
                                                <a href="#" class="ttbm-reply-enquiry" data-id="<?php echo get_the_ID(); ?>" data-target-popup="reply-enquiry-popup"><?php _e('Reply |', 'tour-booking-manager'); ?></a>
                                                <a href="#" class="ttbm-delete-enquiry" data-id="<?php echo get_the_ID(); ?>"><?php _e('Delete', 'tour-booking-manager'); ?></a>
                                            </td>
                                        </tr>
                                <?php
                                    endwhile;
                                    wp_reset_postdata();
                                else :
                                ?>
                                    <tr>
                                        <td colspan="7"><?php esc_html_e('No enquiries found.', 'tour-booking-manager'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <?php
                        // Pagination
                        $total_pages = $enquiry->max_num_pages;

                        if ($total_pages > 1) :
                            $current_page = max(1, $paged);
                            echo '<div class="tablenav"><div class="tablenav-pages">';
                            echo '<span class="displaying-num">' . $enquiry->found_posts . ' items</span>';
                            echo '<span class="pagination-links">';

                            // First and Previous
                            if ($current_page > 1) {
                                echo '<a class="first-page button" href="' . esc_url(add_query_arg('paged', 1)) . '"><span aria-hidden="true">«</span></a>';
                                echo '<a class="prev-page button" href="' . esc_url(add_query_arg('paged', $current_page - 1)) . '"><span aria-hidden="true">‹</span></a>';
                            } else {
                                echo '<span class="tablenav-pages-navspan button disabled">«</span>';
                                echo '<span class="tablenav-pages-navspan button disabled">‹</span>';
                            }

                            // Page info
                            echo '<span class="screen-reader-text">Current Page</span>';
                            echo '<span class="paging-input"><span class="tablenav-paging-text">' . $current_page . ' of <span class="total-pages">' . $total_pages . '</span></span></span>';

                            // Next and Last
                            if ($current_page < $total_pages) {
                                echo '<a class="next-page button" href="' . esc_url(add_query_arg('paged', $current_page + 1)) . '"><span aria-hidden="true">›</span></a>';
                                echo '<a class="last-page button" href="' . esc_url(add_query_arg('paged', $total_pages)) . '"><span aria-hidden="true">»</span></a>';
                            } else {
                                echo '<span class="tablenav-pages-navspan button disabled">›</span>';
                                echo '<span class="tablenav-pages-navspan button disabled">»</span>';
                            }

                            echo '</span></div></div>';
                        endif;
                        ?>

                    </div>
                </div>
                <div id="mptbm-enquiry-settings" class="tab-content" style="display: none;">
                    <div id="col-left">
                        <div class="col-wrap">
                            <div class="form-wrap">
                                <h2><?php esc_html_e('Enquiry Settings', 'tour-booking-manager'); ?></h2>
                                <form method="post" action="">
                                    <input type="hidden" name="ttbm_enquiry_nonce" value="<?php echo wp_create_nonce('ttbm_enquiry_nonce'); ?>">
                                    <div class="form-field term-name-wrap">
                                        <label><?php esc_html_e('From Email', 'tour-booking-manager'); ?></label>
                                        <input name="ttbm_enquiry_from_email" type="email" value="<?php echo esc_attr($from_email); ?>" size="40" aria-required="true">
                                    </div>
                                    <input type="submit" name="ttbm_enquiry_submit" class="button button-primary" value="Save">
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
