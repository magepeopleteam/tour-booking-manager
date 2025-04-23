<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Admin_Tour_List')) {
		class TTBM_Admin_Tour_List {
			public function __construct() {
				add_action('admin_menu', array($this, 'tour_list_menu'), 1);
				//===//
				add_action('wp_ajax_ttbm_trash_post', array($this, 'ttbm_trash_post'));

                add_action('wp_ajax_ttbm_load_more', array($this, 'load_more_callback') );
			}

            function load_more_callback() {
                check_ajax_referer('ttbm_load_more', 'nonce');
            
                $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
                $post_per_page = isset($_POST['post_per_page']) ? intval($_POST['post_per_page']) : 2;
            
                $args = array(
                    'post_type'      => 'ttbm_tour',
                    'post_status'    => 'publish',
                    'paged'          => $paged,
                    'posts_per_page' => $post_per_page,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                );

                $posts_query = new WP_Query($args);
                ob_start();
                $this->tour_list($posts_query);
                $html = ob_get_clean();
            
                wp_send_json_success(array(
                    'html' => $html,
                    'max_pages' => $posts_query->max_num_pages
                ));
            }

			public function tour_list_menu() {
				$label = TTBM_Function::get_name();
				add_submenu_page('edit.php?post_type=ttbm_tour', $label . ' ' . esc_html__('List', 'tour-booking-manager'), $label . ' ' . esc_html__('List', 'tour-booking-manager'), 'manage_options', 'ttbm_list', array($this, 'ttbm_list'));
			}

			public function ttbm_list() {
                ?>
                <div class="wrap ttbm-tour-list-page">
                    <div class="ttbm-event-list">
                    <?php
                        $paged = isset($_GET['paged']) ? (int) $_GET['paged'] : 1;
                        $post_per_page = isset($_REQUEST['post_per_page']) ? (int) $_REQUEST['post_per_page'] : 2;
                        if (isset($_GET['_wpnonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ttbm_pagination')) {
                            $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
                        } else {
                            $paged = 1;
                        }
                        $args = array(
                            'post_type'      => 'ttbm_tour',
                            'post_status'    => 'publish',
                            'paged'          => $paged,
                            'posts_per_page' => $post_per_page,
                            'orderby'        => 'date',
                            'order'          => 'DESC',
                        );
                        $posts_query = new WP_Query($args);
                        
                        $this->tour_list($posts_query); 
                        ?>
                    </div>
                    <?php if ($posts_query->max_num_pages > $paged) : ?>
                        <div class="ttbm-load-more-wrap">
                            <button id="ttbm-load-more" class="button" 
                                    data-paged="<?php echo esc_attr($paged + 1); ?>" 
                                    data-posts-per-page="<?php echo esc_attr($post_per_page); ?>" 
                                    data-nonce="<?php echo wp_create_nonce('ttbm_load_more'); ?>">
                                <?php esc_html_e('Load More', 'tour-booking-manager'); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
				<?php
			}

            public function tour_list($posts_query){
                
                if ($posts_query->have_posts()) {
                    while ($posts_query->have_posts()) {
                        $posts_query->the_post();
                        $post_id = get_the_ID();
                        $location = get_post_meta($post_id, 'ttbm_location_name', true);
                        $upcoming_date = MP_Global_Function::get_post_info($post_id, 'ttbm_upcoming_date');
                        $total = TTBM_Function::get_total_seat($post_id);
                        $sold = TTBM_Function::get_total_sold($post_id, $upcoming_date);
                        $reserve = TTBM_Function::get_total_reserve($post_id);
                        ?>
                        
                        <div class="event-card">
                            <div class="event-details">
                                <div class="event-thumb">
                                    <?php echo get_the_post_thumbnail($post_id, 'thumbnail'); ?>
                                </div>
                                <div class="event-info">
                                    <h3><a href="<?php echo get_the_permalink($post_id); ?>"><?php echo get_the_title($post_id); ?></a></h3>
                                    <p class="location"><?php echo esc_html($location); ?></p>
                                    <p class="description"><?php echo esc_html(wp_trim_words(get_the_excerpt($post_id), 15)); ?></p>
                                </div>
                            </div>
                            <div class="event-meta">
                                <div class="meta-item">
                                    <div class="meta-icon"><i class="fa fa-dollar-sign"></i></div>
                                    <div class="meta-label"><?php echo esc_html($total); ?> total</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-icon"><i class="fas fa-ticket-alt"></i></div>
                                    <div class="meta-label"><?php echo esc_html($sold); ?> sold</div>
                                </div>
                                <div class="meta-item">
                                    <div class="meta-icon"><i class="fas fa-calendar-alt"></i></div>
                                    <div class="meta-label">
                                        <?php
                                        if ($upcoming_date) {
                                            echo esc_html(MP_Global_Function::date_format($upcoming_date));
                                        } else {
                                            echo '<span class="textWarning">' . esc_html__('Expired!', 'tour-booking-manager') . '</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="meta-action">
                                    <?php wp_nonce_field('edd_sample_nonce', 'edd_sample_nonce');  ?>
                                    <a href="<?php echo get_edit_post_link($post_id); ?>"><i class="fa fa-eye"></i></a>
                                    <button class="_mpBtn_xs_textGray" type="button" title="<?php esc_attr_e('Edit Details.', 'tour-booking-manager'); ?>" data-href="<?php echo esc_url(admin_url('post.php?post=' . $post_id . '&action=edit')); ?>">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <a href="<?php echo get_edit_post_link($post_id); ?>"><i class="fa fa-edit"></i></a>
                                    <?php do_action('add_ttbm_list_action_button', $post_id); ?>
                                    <button class="ttbm_trash_post" type="button" data-alert="<?php echo esc_attr__('Are you sure ? To trash : ', 'tour-booking-manager') . ' ' . get_the_title($post_id); ?>" data-post-id="<?php echo esc_attr($post_id); ?>" title="<?php echo esc_attr__('Trash ', 'tour-booking-manager') . ' : ' . get_the_title($post_id); ?>">
                                        <i class="fa fa-trash"></i> 
                                    </button>
                                    
                                </div>
                            </div>
                        </div>

                        <?php
                    }
                    wp_reset_postdata();
                } else {
                    echo '<p>' . esc_html__('No tours found.', 'your-textdomain') . '</p>';
                }
            }

			public function ttbm_trash_post() {
				if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'edd_sample_nonce' ) ) {
					die();
				}
				if (current_user_can('administrator')) {
					if (get_post_type($_REQUEST['post_id']) == TTBM_Function::get_cpt_name()) {
						$post_id = isset($_REQUEST['post_id']) ? MP_Global_Function::data_sanitize($_REQUEST['post_id']) : '';
						if ($post_id > 0) {
							$args = array('post_type' => array('ttbm_tour'), 'posts_per_page' => -1, 'p' => $post_id, 'post_status' => 'publish');
							$loop = new WP_Query($args);
							if ($loop->found_posts) {
								$current_post = get_post($post_id, 'ARRAY_A');
								$current_post['post_status'] = 'trash';
								wp_update_post($current_post);
							}
						}
					}
				} else {
					echo "You don't have the permissions to delete the post";
				}
				die();
			}
		}
		new TTBM_Admin_Tour_List();
	}
