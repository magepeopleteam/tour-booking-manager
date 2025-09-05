<?php
if (!class_exists('TTBM_Hotel_Lists')) {
    class TTBM_Hotel_Lists{
        public function __construct() {

            // save feature data
            add_action('ttbm_hotel_dashboard_tabs', [$this, 'dashbaord_hotel_lists']);
            add_action('ttbm_hotel_dashboard_content', [$this, 'dashbaord_hotel_lists_content']);
        }

        public function dashbaord_hotel_lists(){
            ?>
            <div class="ttbm_hotel_tab_item active" data-tab="ttbm_hotel_list_tab"><i class="mi mi-hotel"></i> <?php echo esc_attr__( 'Hotel Lists', 'tour-booking-manager' )?></div>
            <?php
        }
        
        public function dashbaord_hotel_lists_content(){
            $published_count = isset($counts->publish) ? $counts->publish : 0;
            $trash_count     = isset($counts->trash) ? $counts->trash : 0;
            $draft_count     = isset($counts->draft) ? $counts->draft : 0;
            $total_count = $published_count + $trash_count + $draft_count;
            $posts_per_page = 10;
            $hotel_list_query = self::ttbm_hotel_list_query( $posts_per_page );
            $trash_link = add_query_arg([
                'post_status' => 'trash',
                'post_type'   => 'ttbm_hotel',
            ], admin_url('edit.php'));
            ?>
            <!--Hotel List Tab-->
            <div id="ttbm_hotel_list_tab" class="ttbm_hotel_tab_content active">
                <div class="ttbm_total_booking_wrapper" style="display: block">

                    <div class="ttbm_hotel_list_header">


                        <div class="ttbm_tour_list_text_header">

                            <div class="ttbm_travel_list_header_text">
                                <h2 class="ttbm_total_booking_title"><?php echo esc_attr__( 'Hotel List', 'tour-booking-manager' )?></h2>
                            </div>

                            <div class="ttbm_hotel_count_holder">
                                <div class="ttbm_travel_filter_item ttbm_filter_btn_active_bg_color" data-filter-item="all">All (<?php echo esc_attr( $total_count )?>)</div>
                                <div class="ttbm_travel_filter_item ttbm_filter_btn_bg_color" data-filter-item="publish">Publish (<?php echo esc_attr( $published_count )?>)</div>
                                <div class="ttbm_travel_filter_item ttbm_filter_btn_bg_color" data-filter-item="draft">Draft (<?php echo esc_attr( $draft_count )?>)</div>

                                <a class="ttbm_trash_link" href="<?php echo esc_url( $trash_link )?>" target="_blank">
                                    <div class="ttbm_total_trash_display">Trash Tour (<?php echo esc_attr( $trash_count )?>) </div>
                                </a>
                            </div>
                        </div>

                        <div class="ttbm_hotel_search_addHolder">
                            <div class="ttbm_add_new_hotel_header">
                                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=ttbm_hotel')); ?>" class="ttbm_add_new_hotel_text">
                                    <i class="fas fa-plus"></i> <?php echo esc_attr__( 'Add New', 'tour-booking-manager' )?></a>
                            </div>
                            <div class="ttbm_hotel_titleSearchContainer">
                                <input type="text" class="ttbm_hotel_title_SearchBox" id="ttbm_hotel_title_SearchBox" placeholder="Hotel Name Search...">
                            </div>
                        </div>
                    </div>

                    <?php echo wp_kses_post( self::ttbm_display_Hotel_lists( $hotel_list_query, $posts_per_page ) )?>
                </div>
            </div>
            <?php
        }

        public static function ttbm_display_Hotel_lists( $query, $posts_per_page ) {
            ob_start(); ?>
                <div class="hotel-list-container">
                    <table class="ttbm-hotel-list-table">
                        <thead>
                        <tr>
                            <th scope="col" class="ttbm-hotel-list-column-image"><?php echo esc_attr__('Image', 'tour-booking-manager'); ?></th>
                            <th scope="col" class="ttbm-hotel-list-column-hotel"><?php echo esc_attr__('Hotel', 'tour-booking-manager'); ?></th>
                            <th scope="col" class="ttbm-hotel-list-column-hotel"><?php echo esc_attr__('Status', 'tour-booking-manager'); ?></th>
                            <th scope="col" class="ttbm-hotel-list-column-actions"><?php echo esc_attr__('Actions', 'tour-booking-manager'); ?></th>
                        </tr>
                        </thead>
                        <tbody class="ttbm_hotel_list_view" id="ttbm_hotel_list_view">
                            <?php echo wp_kses_post( self::display_hotel_lists_as_table( $query ) )?>
                        </tbody>
                    </table>
                    <?php if( $query->found_posts > $posts_per_page ){?>
                        <div class="ttbm_hotel_list_load_more_btn_holder">
                            <button class="ttbm_hotel_list_load_more_btn" id="ttbm_hotel_list_load_more_btn"><?php esc_attr_e( 'Load More', 'tour-booking-manager');?></button>
                        </div>
                    <?php }?>
                </div>
            <?php
            return ob_get_clean();
        }

        public static function ttbm_hotel_list_query( $posts_per_page = 20, $excluded_post_ids=[] ) {

            $args = array(
                'post_type'      => 'ttbm_hotel',
                'posts_per_page' => $posts_per_page,
                'post_status'    => array( 'publish', 'draft' ),
                'post__not_in'   => $excluded_post_ids,
            );

            return new WP_Query($args);
        }

        public static function display_hotel_lists_as_table( $query, $is_load_more = '' ) {
            $tag_color = array(
              'first-tag', 'second-tag', 'third-tag',
            );

            ob_start();
            if ( $query->have_posts() ) :
                while ( $query->have_posts() ) : $query->the_post();
                    $post_id   = get_the_ID();
                    $title     = get_the_title();
                    $desc      = get_the_excerpt();
                    $image     = get_the_post_thumbnail_url( $post_id, 'thumbnail' );
                    $image     = $image ? $image : esc_url( includes_url( 'images/media/default.png' ) ); 
                    $location  = get_post_meta( $post_id, 'ttbm_hotel_location', true );
                    
                    $selected_features = TTBM_Function::get_feature_list($post_id, 'ttbm_hotel_feat_selection');
				    $selected_features = is_array($selected_features) ? $selected_features : [];
                    $all_features = TTBM_Global_Function::get_taxonomy('ttbm_hotel_features_list');
                    
                    $post_status = get_post_status();
                   

                    ?>
                    <tr id="hotel_<?php echo esc_attr( $post_id ); ?>" class="ttbm-tour-card" data-travel-type="<?php echo esc_attr( $post_status )?>">
                        <td class="ttbm-hotel-list-column-image">
                            <div class="ttbm-hotel-list-image-placeholder">
                                <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>"/>
                            </div>
                        </td>
                        <td class="ttbm-hotel-list-column-hotel">
                            <div class="ttbm-hotel-list-hotel-title">
                                <?php echo esc_html( $title ); ?> <span class="hotel-id">(ID: <?php echo esc_html( $post_id ); ?>)</span>
                            </div>
                            <?php if ( $location ) : ?>
                                <div class="ttbm-hotel-list-hotel-location">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo esc_html( $location ); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ( $desc ) : ?>
                                <div class="ttbm-hotel-list-hotel-description">
                                    <?php echo esc_html( $desc ); ?>
                                </div>
                            <?php endif; ?>
                            <?php
                            $icon = '';
                            if ( ! empty( $all_features ) && is_array( $all_features ) ) : ?>
                                <div class="ttbm-hotel-list-hotel-features">
                                    <?php
                                    $count = 0;
                                    foreach ($all_features as $feature) :?>
                                        <?php if (in_array($feature->term_id, $selected_features)) : 
                                            $icon = get_term_meta($feature->term_id, 'ttbm_hotel_feature_icon', true);
                                            $icon = $icon ? $icon : 'mi mi-home';
                                        ?>
                                        <?php if( $count < 3): ?>
                                        <span class="ttbm-hotel-list-feature-tag <?php echo esc_attr( $tag_color[$count] );?>">
                                            <i class="<?php echo esc_attr($icon); ?>"></i>
									        <span><?php echo esc_html($feature->name); ?></span>
                                        </span>
                                    <?php endif; $count++; endif;?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="ttbm-hotel-list-column-actions">
                            <div class="ttbm-hotel-lists-status">
                                <div class="ttbm_hotel_status_items" >
                                    <div class="ttbm_travel_status"><?php echo esc_attr( $post_status )?></div>
                                </div>
                            </div>
                        </td>
                        <td class="ttbm-hotel-list-column-actions">
                            <div class="ttbm-hotel-list-action-buttons">
                                <a href="<?php echo esc_url(get_permalink( $post_id )); ?>"
                                   class="ttbm-hotel-list-action-btn ttbm-hotel-list-view-btn"
                                   title="View" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="<?php echo esc_url( get_edit_post_link( $post_id )); ?>"
                                   class="ttbm-hotel-list-action-btn ttbm-hotel-list-edit-btn"
                                   title="Edit" target="_blank">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <a title="<?php echo esc_attr__('Duplicate Hotel ', 'tour-booking-manager') . ' : ' . esc_attr(get_the_title($post_id)); ?>"  class="ttbm-hotel-list-action-btn ttbm_hotel_duplicate_post" href="<?php echo esc_attr(wp_nonce_url(
                                    admin_url('admin.php?action=ttbm_duplicate_post&post_id=' . $post_id),
                                    'ttbm_duplicate_post_' . $post_id
                                )); ?>">
                                    <i class="fa fa-clone"></i>
                                </a>

                                <a href="<?php echo get_delete_post_link( $post_id ); ?>"
                                   class="ttbm-hotel-list-action-btn ttbm-hotel-list-delete-btn"
                                   data-confirm="Are you sure you want to delete this hotel?"
                                   title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>

                        </td>
                    </tr>
                <?php
                endwhile;
            endif;
            wp_reset_postdata();

            return ob_get_clean();
        }
    }
    new TTBM_Hotel_Lists();
}