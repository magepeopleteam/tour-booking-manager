<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('TTBM_Hotel_Data_Display')) {

    class TTBM_Hotel_Data_Display{

        public function __construct(){
            add_action('ttbm_hotel_left_filter', array($this, 'hotel_left_filter'), 10, 1);
            add_action('ttbm_hotel_filter_top_bar', array($this, 'filter_top_bar'), 10, 2);
            add_action('ttbm_all_hotel_list_item', array($this, 'all_hotel_list_item'), 10, 2);
        }
        public function hotel_left_filter( $params ) {
            ?>
            <div class="filter-top-label">
                <h4 data-placeholder><span class="mR_xs fas fa-filter"></span><?php esc_html_e('Filters', 'tour-booking-manager'); ?></h4>
            </div>
            <div class="ttbm_filter">
                <?php /*$this->location_filter_multiple($params); */?><!--
                <?php /*$this->country_filter_left($params); */?>
                <?php /*$this->title_filter_left($params); */?>
                <?php /*$this->type_filter_left($params); */?>
                <?php /*$this->category_filter_left($params); */?>
                <?php /*$this->month_filter_left($params); */?>
                <?php /*$this->duration_filter_multiple($params); */?>
                <?php /*$this->feature_filter_multiple($params); */?>
                <?php /*$this->activity_filter_multiple($params); */?>
                <?php /*$this->tag_filter_multiple($params); */?>
                --><?php /*$this->organizer_filter_left($params); */?>
            </div>
            <?php
        }

        public function all_hotel_list_item( $loop, $params ){
            $hotel_data = self::all_hotel_list_data( $loop );

        }

        public static function all_hotel_list_data( $loop ) {

            $hotel_data = array();

            if ( $loop->have_posts() ) {
                while ($loop->have_posts()) {
                    $loop->the_post();
                    $id = get_the_ID();
                    $featured_image = get_the_post_thumbnail_url( $id, 'full' );
                    $hotel_data[] = array(
                        'id'                            => $id,
                        'title'                         => get_the_title(),
                        'content'                       => get_the_title(),
                        'excerpt'                       => get_the_excerpt(),
                        'hotel_activity_status'         => get_post_meta( $id, 'ttbm_hotel_activity_status', true ),
                        'hotel_area_info'               => get_post_meta( $id, 'ttbm_hotel_area_info', true ),
                        'hotel_map_location'            => get_post_meta( $id, 'ttbm_hotel_map_location', true ),
                        'hotel_location'                => get_post_meta( $id, 'ttbm_hotel_location', true ),
                        'hotel_room_details'            => get_post_meta( $id, 'ttbm_room_details', true ),
                        'hotel_gallery_images_ids'      => get_post_meta( $id, 'ttbm_gallery_images_hotel', true ),
                        'hotel_distance_description'    => get_post_meta( $id, 'ttbm_hotel_distance_des', true ),
                        'hotel_featured_image'          => $featured_image,
                        'permalink'                     => get_permalink($id),
                    );
                }
            }

            return $hotel_data;
        }

        public function filter_top_bar( $loop, $params ) {
            $style = $params['style'] ?: 'modern';
            $style = $style == 'list' ? 'modern' : $style;
            if (is_page('find') || 1 ) {
                ?>
                <div class="placeholder_area filter_top_bar justifyBetween">
						<span>
							<strong class="total_filter_qty"><?php echo esc_html($loop->post_count); ?></strong>
							<?php esc_html_e(' Trips match your search criteria', 'tour-booking-manager'); ?>
						</span>
                    <div class="dFlex">
                        <button class="ttbm_grid_view " type="button" <?php echo esc_attr($style == 'grid' ? 'disabled' : ''); ?> title="<?php esc_attr_e('Grid view', 'tour-booking-manager'); ?>">
                            <i class="fas fa-th-large"></i>
                        </button>
                        <button class="ttbm_list_view" type="button" <?php echo esc_attr($style == 'modern' ? 'disabled' : ''); ?> title="<?php esc_attr_e('LIst view', 'tour-booking-manager'); ?>">
                            <i class="fas fa-th-list"></i>
                        </button>
                    </div>
                </div>
                <?php
            }
        }


    }

    new TTBM_Hotel_Data_Display();
}