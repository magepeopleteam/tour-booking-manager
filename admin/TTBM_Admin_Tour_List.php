<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Admin_Tour_List')) {
		class TTBM_Admin_Tour_List {
			public function __construct() {

				// add_action('admin_menu', array($this, 'tour_list_menu'), 1);
				add_action('wp_ajax_ttbm_trash_post', array($this, 'ttbm_trash_post'));

                add_action('wp_ajax_ttbm_load_more', array($this, 'load_more_callback') );
                add_action('wp_ajax_ttbm_search_tours', array($this, 'search_tours_callback'));

                add_action('admin_head', [$this,'remove_admin_notice']);
                add_action('admin_menu', [$this,'remove_default_menu'],0);

            }

            public function remove_default_menu(){
                remove_submenu_page('edit.php?post_type=ttbm_tour', 'edit.php?post_type=ttbm_tour');
                remove_submenu_page('edit.php?post_type=ttbm_tour', 'post-new.php?post_type=ttbm_tour');
                $label = TTBM_Function::get_name();
				add_submenu_page('edit.php?post_type=ttbm_tour', $label . ' ' . esc_html__('List', 'tour-booking-manager'), $label . ' ' . esc_html__('List', 'tour-booking-manager'), 'manage_options', 'ttbm_list', array($this, 'ttbm_list'),0);
            }

            public function search_tours_callback(){
	            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['nonce'] )), 'ttbm_admin_nonce' ) ) {
		            wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
		            die;
	            }
                $paged = isset($_POST['paged']) ? intval(wp_unslash($_POST['paged'])) : 1;
                $post_per_page = isset($_POST['post_per_page']) ? intval(wp_unslash($_POST['post_per_page'])) : 10;
                $search = isset($_POST['search_term']) ? sanitize_text_field(wp_unslash($_POST['search_term'])) : '';
                $args = array(
                    'post_type'      => 'ttbm_tour',
//                    'post_status'    => 'publish',
                    'paged'          => $paged,
                    'posts_per_page' => $post_per_page,
                    's'              => $search,
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
                wp_die();
            }

            public function remove_admin_notice(){
                $screen = get_current_screen();
                if ($screen && $screen->id === 'ttbm_tour_page_ttbm_list') {
                    remove_all_actions('admin_notices');
                    remove_all_actions('all_admin_notices');
                }
            }

            public  function load_more_callback() {
                check_ajax_referer('ttbm_load_more', 'nonce');
            
                $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
                $post_per_page = isset($_POST['post_per_page']) ? intval($_POST['post_per_page']) : 10;
            
                $args = array(
                    'post_type'      => 'ttbm_tour',
//                    'post_status'    => 'publish',
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
                    'count_travels' => $posts_query->post_count,
                    'max_pages' => $posts_query->max_num_pages
                ));
            }

			public function tour_list_menu() {
				$label = TTBM_Function::get_name();
				add_submenu_page('edit.php?post_type=ttbm_tour', $label . ' ' . esc_html__('List', 'tour-booking-manager'), $label . ' ' . esc_html__('List', 'tour-booking-manager'), 'manage_options', 'ttbm_list', array($this, 'ttbm_list'));
			}

			public function ttbm_list() {
                $label = TTBM_Function::get_name();
                $paged = isset($_GET['paged']) ? (int) $_GET['paged'] : 1;
                $post_per_page = isset($_REQUEST['post_per_page']) ? (int) $_REQUEST['post_per_page'] : 10;
                if (isset($_GET['_wpnonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ttbm_pagination')) {
                    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
                } else {
                    $paged = 1;
                }
                $args = array(
                    'post_type'      => 'ttbm_tour',
//                    'post_status'    => 'publish',
                    'paged'          => $paged,
                    'posts_per_page' => $post_per_page,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                );

                $posts_query = new WP_Query($args);
                $remaining_travel = $posts_query->found_posts - $post_per_page;

                $analytics_Data = TTBM_Function::get_travel_analytical_data();

                ?>
                <div class="wrap ttbm-tour-list-page ">



                    <!--Here Analytics-->
                    <?php do_action('ttbm_travel_analytics_display', $posts_query->found_posts, $analytics_Data )?>

                    <?php do_action('ttbm_travel_lists_tab_display', $label, $analytics_Data, $posts_query )?>

                    <!--<div class="ttbm_travel_filter_holder">
                        <div class="ttbm_travel_filter_item ttbm_filter_btn_active_bg_color" data-filter-item="all">All</div>
                        <div class="ttbm_travel_filter_item ttbm_filter_btn_bg_color" data-filter-item="publish">Publish</div>
                        <div class="ttbm_travel_filter_item ttbm_filter_btn_bg_color" data-filter-item="draft">Draft</div>
                    </div>-->
                    <div class="ttbm-tour-list_holder">
                        <div class="ttbm-tour-list">
                            <?php
                            $this->tour_list($posts_query);
                            ?>
                        </div>
                        <?php if ($posts_query->max_num_pages > $paged) : ?>
                            <div class="ttbm-load-more-wrap">
                                <button id="ttbm-load-more" class="button"
                                        data-paged="<?php echo esc_attr($paged + 1); ?>"
                                        data-posts-per-page="<?php echo esc_attr($post_per_page); ?>"
                                        data-nonce="<?php echo esc_attr(wp_create_nonce('ttbm_load_more')); ?>">
                                    <i class="fas fa-sync-alt"></i> <?php esc_html_e('Load More', 'tour-booking-manager'); ?> (<span class="ttbm_load_more_remaining_travel"><?php echo esc_attr( $remaining_travel );?></span>)
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
				<?php
			}

            public function tour_list($posts_query){
                
                if ($posts_query->have_posts()) {
                    while ($posts_query->have_posts()) {
                        $posts_query->the_post();
                        $post_id = get_the_ID();
                        $location = get_post_meta($post_id, 'ttbm_location_name', true);
                        $upcoming_date = TTBM_Global_Function::get_post_info($post_id, 'ttbm_upcoming_date');
                        if( !$upcoming_date ){
                            $all_dates     = TTBM_Function::get_date( $post_id );
                            $upcoming_date = TTBM_Function::get_upcoming_date_month( $post_id,true, $all_dates );
                        }
                        $total = TTBM_Function::get_total_seat($post_id);
                        $sold = TTBM_Function::get_total_sold($post_id, $upcoming_date);
                        $reserve = TTBM_Function::get_total_reserve($post_id);

                        $post_status = get_post_status();
                        $publish_status = '';
                        
                        switch ($post_status) {
                            case 'publish':
                                $publish_status = 'publish';
                                break;
                            case 'draft':
                                $publish_status = 'draft';
                                break;
                            case 'pending':
                                $publish_status = 'pending';
                                break;
                            case 'future':
                                $publish_status = 'scheduled';
                                break;
                            case 'trash':
                                $publish_status = 'trash';
                                break;
                            default:
                                $publish_status = '';
                        }

                        $max_features = [];
                        $features = get_post_meta( $post_id, 'ttbm_service_included_in_price', true );
                        if( is_array( $features ) && !empty( $features ) ){
                            $max_features = array_slice( $features, 0, 3 );
                        }

                        $is_expire = 'upcoming_tour';
                        if( $upcoming_date === '' ){
                            $is_expire = 'expired_tour';
                        }

                        ?>
                        
                        <div class="ttbm-tour-card" data-travel-type="<?php echo esc_attr( $post_status )?>" data-expire-tour="<?php echo esc_attr( $is_expire );?>">
                            <div class="ttbm-tour-thumb">
                                <?php echo get_the_post_thumbnail($post_id, 'full'); ?>
                            </div>
                            <div class="ttbm-tour-card-content">
                                <div>
                                    <h3><a href="<?php echo esc_url(get_edit_post_link($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a></h3>
                                    <?php if($location): ?>
                                    <div class="location"><i class="fas fa-map-marker-alt"></i> <?php echo esc_html($location); ?></div>
                                    <?php endif; ?>
                                    <div class="meta-action">
                                        <div class="action-links">
                                            <?php wp_nonce_field('edd_sample_nonce', 'edd_sample_nonce');  ?>
                                            <a title="<?php echo esc_attr__('View ', 'tour-booking-manager') . ' : ' . esc_attr(get_the_title($post_id)); ?>" class="ttbm_view_post" href="<?php  the_permalink($post_id); ?>" target="_blank"><i class="fa fa-eye"></i></a>
                                            <a title="<?php echo esc_attr__('Edit ', 'tour-booking-manager') . ' : ' . esc_attr(get_the_title($post_id)); ?>" class="ttbm_edit_post" href="<?php echo esc_url(get_edit_post_link($post_id)); ?>"><i class="fa fa-edit"></i></a>
                                            <a title="<?php echo esc_attr__('Duplicate Post ', 'tour-booking-manager') . ' : ' . esc_url(get_the_title($post_id)); ?>" class="ttbm_duplicate_post" href="<?php echo esc_url(wp_nonce_url(
                                                admin_url('admin.php?action=ttbm_duplicate_post&post_id=' . $post_id),
                                                'ttbm_duplicate_post_' . $post_id
                                            )); ?>">
                                                <i class="fa fa-clone"></i>
                                            </a>
                                            <a class="ttbm_trash_post" data-alert="<?php echo esc_attr__('Are you sure ? To trash : ', 'tour-booking-manager') . ' ' . esc_attr(get_the_title($post_id)); ?>" data-post-id="<?php echo esc_attr($post_id); ?>" title="<?php echo esc_attr__('Trash ', 'tour-booking-manager') . ' : ' . esc_attr(get_the_title($post_id)); ?>">
                                                <i class="fa fa-trash"></i> 
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="ttbm-tour-details">
                                    <div class="ttbm-tour-info">
                                        <div class="description"><?php echo esc_html(wp_trim_words(get_the_excerpt($post_id), 80)); ?></div>
                                        <div class="ttbm_travel_lists_tour-features">
                                            <?php if( !empty( $max_features ) ){
                                                foreach ( $max_features as $key => $feature ){
                                                    if( $key === 0 ){
                                                        $feature_bg = 'ttbm_travel_lists_first_feature';
                                                    }elseif ( $key === 1 ){
                                                        $feature_bg = 'ttbm_travel_lists_second_feature';
                                                    }else{
                                                        $feature_bg = 'ttbm_travel_lists_third_feature';
                                                    }
                                                ?>
                                            <div class="ttbm_travel_lists_tour-feature <?php echo esc_attr( $feature_bg )?>"><?php echo esc_attr( $feature )?></div>
                                            <?php } }?>
                                        </div>
                                    </div>
                                    <div class="ttbm-tour-meta">
                                        <div class="ttbm_travel_status <?php echo esc_attr($publish_status?$publish_status:''); ?>">
                                            <?php echo esc_attr( $publish_status )?>
                                        </div>
                                        <div class="tour-stats">
                                            <div class="stat">
                                                <span class="value"><?php echo esc_html($total); ?></span> 
                                                <span class="label"><?php echo esc_html__('Total Seats','tour-booking-manager'); ?></span>
                                            </div>
                                            <div class="stat">
                                                <span class="value"><?php echo esc_html($sold); ?></span> 
                                                <span class="label"><?php echo esc_html__('Sold Seats','tour-booking-manager'); ?></span> 
                                            </div>
                                        </div>
                                        <div class="meta-date">
                                            <?php
                                                if ($upcoming_date) {
                                                    echo esc_html(TTBM_Global_Function::date_format($upcoming_date));
                                                } else {
                                                    echo '<span class="textWarning">' . esc_html__('Expired!', 'tour-booking-manager') . '</span>';
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            
                        </div>

                        <?php
                    }
                    wp_reset_postdata();
                } else {
                    echo '<p>' . esc_html__('No tours found.', 'tour-booking-manager') . '</p>';
                }
            }

			public function ttbm_trash_post() {
				if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce(sanitize_text_field(wp_unslash( $_REQUEST['nonce'])), 'edd_sample_nonce' ) ) {
					die();
				}
				if (current_user_can('administrator')) {
					$post_id = isset($_POST['ttbm_id']) ? sanitize_text_field(wp_unslash($_POST['ttbm_id'])) : 0;
					if (get_post_type($post_id) == TTBM_Function::get_cpt_name()) {
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
