<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('TTBM_Hotel_Data_Display')) {

    class TTBM_Hotel_Data_Display{

        public function __construct(){
            add_action('ttbm_hotel_left_filter', array($this, 'hotel_left_filter'), 10, 1);
            add_action('ttbm_hotel_filter_top_bar', array($this, 'filter_top_bar'), 10, 2);
            add_action('ttbm_all_hotel_list_item', array($this, 'all_hotel_list_item'), 10, 4 );
            add_action('ttbm_search_hotel_list_item', array($this, 'ttbm_search_hotel_list_item'), 10, 2);
            add_action('ttbm_filter_hotel_search_top_bar', array($this, 'filter_hotel_search_top_bar'), 10, 2);
        }

        public function filter_hotel_search_top_bar( $post_count, $params ) {
            $style = $params['style'] ?: 'modern';
            $style = $style == 'list' ? 'modern' : $style;
            ?>
            <div class="placeholder_area filter_top_bar justifyBetween">
                    <span>
                        <?php esc_html_e(' Total ', 'tour-booking-manager'); ?>
                        <strong class="total_hotel_qty"><?php echo esc_html( $post_count ); ?></strong>
<!--                        --><?php //esc_html_e(' Hotel ', 'tour-booking-manager'); ?>
                        <?php esc_html_e(' Hotel match your search criteria', 'tour-booking-manager'); ?>
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
        public function hotel_left_filter( $params ) {
            ?>
            <div class="filter-top-label">
                <h4 data-placeholder><span class="mR_xs fas fa-filter"></span><?php esc_html_e('Filters', 'tour-booking-manager'); ?></h4>
            </div>
            <div class="ttbm_filter">
                <?php
                    if ($params['price-filter'] == 'yes') {
                        $this->price_filter_left($params);
                    }
                    $this->location_filter_multiple($params);
                    $this->activity_filter_multiple($params);
                    $this->feature_filter_multiple($params);
                ?>
            </div>
            <?php
        }

        public function price_filter_left( $params ){
             ?>
                <div class="ttbm_hotel_price_slider">
                    <span>Filter by Price</span>
                    <div class="ttbm_hotel_slider_track"></div>

                    <div class="ttbm_slider_inputs">
                        <input type="range" id="ttbm_min_range" min="0" max="5000" value="0" step="10">
                        <input type="range" id="ttbm_max_range" min="0" max="5000" value="3000" step="10">
                    </div>

                    <div class="ttbm_price_values">
                        <span id="ttbm_min_price">500</span> – <span id="ttbm_max_price">3000</span>
                    </div>
                </div>
            <?php
        }

        public function activity_filter_multiple( $params) {
            if ($params['activity-filter'] == 'yes') {
                $activities = TTBM_Function::get_meta_values('ttbm_hotel_activity_selection', 'ttbm_hotel' );

                $all_activities = [];

                foreach ($activities as $activity_group) {
                    $all_activities = array_merge($all_activities, $activity_group);
                }
                $unique_activities = array_values(array_unique($all_activities));
                $activity_count = sizeof($unique_activities);
                if ( $activity_count > 0) {
                    $url_activity = '';
                    if (isset($_POST['ttbm_search_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ttbm_search_nonce'])), 'ttbm_search_nonce')) {
                        $url_activity = isset($_GET['activity_filter']) ? sanitize_text_field(wp_unslash($_GET['activity_filter'])) : '';
                    }
                    $current_activity = $url_activity ? (($term = get_term_by('id', $url_activity, 'ttbm_tour_activities')) ? $term->term_id : '') : '';
                    ?>
                    <h5 class="mT justifyBetween _alignCenter" data-open-icon="fa-chevron-down" data-close-icon="fa-chevron-right" data-collapse-target="#activity_filter_multiple" data-placeholder>
                        <?php esc_html_e('Filter By Activity', 'tour-booking-manager'); ?>
                        <span data-icon class="fas fa-chevron-down"></span>
                    </h5>
                    <div class="divider"></div>
                    <div class="mActive" data-collapse="#activity_filter_multiple" data-placeholder>
                        <div class="groupCheckBox _dFlex flexColumn" id="ttbm_hotelActivityList">
                            <input type="hidden" name="hotel_activity_filter_multiple" value="<?php echo esc_attr($current_activity); ?>"/>
                            <?php foreach ( $unique_activities as $activity ) {
                                if( $activity ){
//									$term = get_term_by('name', $activity, 'ttbm_tour_activities');
                                    $term = get_term( $activity, 'ttbm_hotel_activities_list' );
                                    $term_name =$term? $term->name:'';

                                    $term_id = $term ? $term->term_id : 0;
                                    $checked = $current_activity == $term_id ? 'checked' : '';
                                    if( $term_id > 0 ){
                                        ?>
                                        <label class="customCheckboxLabel ttbm_activity_checkBoxLevel">
                                            <input type="checkbox" class="formControl" data-checked="<?php echo esc_attr($term_id); ?>" <?php echo esc_attr($checked); ?>/>
                                            <span class="customCheckbox"><?php echo esc_html($term_name); ?></span>
                                        </label>
                                    <?php } }
                            }
                            if( $activity_count > 6 ){
                            ?>
                                <button id="ttbm_hotel_show_activity_seeMoreBtn" class="ttbm_see-more-button"><?php esc_html_e('See More+', 'tour-booking-manager'); ?></button>
                            <?php }?>
                        </div>
                    </div>
                    <?php
                }
            }
        }
        public function location_filter_multiple($params) {
            if ( $params['location-filter'] == 'yes' ) {
                $locations = TTBM_Function::get_meta_values('ttbm_hotel_location', 'ttbm_hotel');
                $exist_locations = $locations;

                $exist_locations = array_unique($exist_locations);
                $location_count = sizeof( $exist_locations );
                if ( $location_count > 0 ) {
                    $url_location = '';
                    if (isset($_POST['ttbm_search_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ttbm_search_nonce'])), 'ttbm_search_nonce')) {
                        $url_location = isset($_GET['location_filter']) ? sanitize_text_field(wp_unslash($_GET['location_filter'])) : '';
                    }
                    $current_location = $url_location ? (($term = get_term_by('id', $url_location, 'ttbm_tour_location')) ? $term->term_id : '') : '';
                    ?>
                    <h5 class="justifyBetween _alignCenter" data-open-icon="fa-chevron-down" data-close-icon="fa-chevron-right" data-collapse-target="#ttbm_location_filter_multiple" data-placeholder>
                        <?php esc_html_e('Filters By Location', 'tour-booking-manager'); ?>
                        <span data-icon class="fas fa-chevron-down"></span>
                    </h5>
                    <div class="divider"></div>
                    <div class="mActive" data-collapse="#ttbm_location_filter_multiple" data-placeholder>
                        <div class="groupCheckBox _dFlex flexColumn" id="ttbm_hotelLocationList">
                            <input type="hidden" name="location_filter_multiple" value="<?php echo esc_attr($current_location); ?>"/>
                            <?php foreach ($exist_locations as $location) { ?>
                                <?php
                                $term = get_term_by('name', $location, 'ttbm_tour_location');
                                $term_id = $term ? $term->term_id : 0;
                                $checked = $current_location == $term_id ? 'checked' : ''; ?>
                                <label class="customCheckboxLabel ttbm_location_checkBoxLevel">
                                    <input type="checkbox" class="formControl" data-checked="<?php echo esc_attr($term_id); ?>" <?php echo esc_attr($checked); ?> />
                                    <span class="customCheckbox"><?php echo esc_html($location); ?></span>
                                </label>
                            <?php }
                            if( $location_count > 6 ){
                            ?>
                                <button id="ttbm_show_hotel_location_seeMoreBtn" class="ttbm_see-more-button"><?php esc_html_e('See More+', 'tour-booking-manager'); ?></button>
                            <?php }?>
                        </div>
                    </div>
                    <?php
                }
            }
        }
        public function feature_filter_multiple($params) {
            if ($params['feature-filter'] == 'yes') {
                $features = TTBM_Function::get_meta_values('ttbm_hotel_feat_selection', 'ttbm_hotel');

                $exist_feature = [];
                for ($i = 0; $i < count($features); $i++) {
                    $exist_feature = array_unique(array_merge($exist_feature, $features[$i]));
                }

                $feature_count = sizeof( $exist_feature);
                if ( $feature_count > 0 ) {
                    $url = '';
                    if (isset($_POST['ttbm_search_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ttbm_search_nonce'])), 'ttbm_search_nonce')) {
                        $url = isset($_GET['feature_filter']) ? sanitize_text_field(wp_unslash($_GET['feature_filter'])) : '';
                    }
                    ?>
                    <h5 class="mT justifyBetween _alignCenter" data-open-icon="fa-chevron-down" data-close-icon="fa-chevron-right" data-collapse-target="#feature_filter_multiple" data-placeholder>
                        <?php esc_html_e('Filters By Feature', 'tour-booking-manager'); ?>
                        <span data-icon class="fas fa-chevron-down"></span>
                    </h5>
                    <div class="divider"></div>
                    <div class="mActive" data-collapse="#feature_filter_multiple" data-placeholder>
                        <div class="groupCheckBox _dFlex flexColumn" id="ttbm_hotelFeatureList">
                            <input type="hidden" name="feature_filter_multiple" value="<?php echo esc_attr($url); ?>"/>
                            <?php foreach ($exist_feature as $feature_item) { ?>
                                <?php
                                $term = get_term( $feature_item, 'ttbm_hotel_features_list' );
                                $term_id = $term ? $term->term_id : 0;
                                $term_name = $term ? $term->name : '';
                                $icon = $term_id ? ( get_term_meta($term_id, 'ttbm_hotel_feature_icon', true) ? get_term_meta($term_id, 'ttbm_hotel_feature_icon', true) : 'fas fa-forward') : 'fas fa-forward';
                                ?>

                                <label class="customCheckboxLabel ttbm_feature_checkBoxLevel">
                                    <input type="checkbox" class="formControl" data-checked="<?php echo esc_attr( $term_id ); ?>"/>
                                    <span class="customCheckbox"><span class="mR_xs <?php echo esc_attr($icon); ?>"></span><?php echo esc_html( $term_name ); ?></span>
                                </label>
                            <?php }
                            if( $feature_count > 6 ){ ?>
                                <button id="ttbm_show_hotel_feature_seeMoreBtn" class="ttbm_see-more-button"><?php esc_html_e('See More+', 'tour-booking-manager'); ?></button>
                            <?php }?>
                        </div>
                    </div>
                    <?php
                }
            }
        }


        public static function display_hotel_list( $hotel_data, $currency, $date_range, $list, $params, $popular_feature = '' ){
            $number_of_column_class = 'ttbm-grid-columns-'.$params['column'];
            ob_start();
            if( $popular_feature === 'yes' ){
                $hotel_card_class = 'ttbm_hotel_lists_popular_card';
            }else if( $popular_feature === 'location' ){
                $hotel_card_class = 'ttbm_hotel_location_lists_card';
            }else{
                $hotel_card_class = 'ttbm_hotel_lists_card';
            }

            ?>
            <div class="ttbm_hotel_lists_wrapper <?php echo esc_attr( $params['list_grid'] );?>-view <?php echo esc_attr( $number_of_column_class );?>">
                <?php
                foreach ( $hotel_data as $key => $hotel ){
                    $hotel_room_details = $hotel[ 'hotel_room_details' ];

                    $hotel_hotel_features = $hotel[ 'hotel_features' ];
                    $hotel_activities = $hotel[ 'hotel_activities' ];

                    $hotel_hotel_feature_str = $hotel_activities_str = '';
                    $lowest_price = 0 ;
                    if( is_array( $hotel_room_details ) && !empty( $hotel_room_details ) ){
                        $prices = array_column($hotel_room_details, 'ttbm_hotel_room_price');
                        $lowest_price = min($prices);
                    }

                    if( is_array( $hotel_hotel_features ) && !empty( $hotel_hotel_features ) ){
                        $hotel_hotel_feature_str = implode( ',',$hotel_hotel_features  );
                    }
                    if( is_array( $hotel_activities ) && !empty( $hotel_activities ) ){
                        $hotel_activities_str = implode( ',',$hotel_activities  );
                    }

                    $inline_booking = '';
                    if( $list === 'search_list' ){
                        $inline_booking = self::inline_room_book( $hotel['id'], $date_range );
                    }

                    $dot_separator = '';
                    $hori_separator = '';
                    if( $hotel['hotel_map_location'] && $hotel['hotel_location']){
                        $dot_separator = '.';
                    }
                    if( $hotel['hotel_map_location'] && $hotel['hotel_distance_description'] ){
                        $hori_separator = ' | ';
                    }

                    ?>
                    <div class="<?php echo esc_attr( $hotel_card_class );?>"
                         id="<?php echo esc_attr(  $hotel['id']);?>"
                         data-hotel-location = "<?php echo esc_attr( $hotel['hotel_location'] );?>"
                         data-hotel-feature = "<?php echo esc_attr( $hotel_hotel_feature_str )?>"
                         data-hotel-activity = "<?php echo esc_attr( $hotel_activities_str )?>"
                         data-hotel-price = "<?php echo esc_attr( $lowest_price )?>"
                         data-date-range = "<?php echo esc_attr( $date_range )?>"
                    >
                        <div class="ttbm_hotel_lists_card_content">
                            <div class="ttbm_hotel_lists_image">
                                <img src="<?php echo esc_attr( $hotel['hotel_featured_image'] );?>" alt="<?php echo esc_attr( $hotel['title'] );?>">
                                <button class="ttbm_hotel_lists_wishlist">♥</button>
                            </div>
                            <div class="ttbm_hotel_lists_content">
                                <div class="ttbm_hotel_lists_header">
                                    <a href="<?php echo esc_attr( $hotel['permalink'])?>"><h3 class="ttbm_hotel_lists_title"><?php echo esc_attr( $hotel['title'] );?></h3></a>
                                    <div class="ttbm_hotel_lists_rating_box">
                                        <span class="ttbm_hotel_lists_rating_text"><?php esc_attr_e( 'Rating', 'tour-booking-manager' );?></span>
                                        <span class="ttbm_hotel_lists_rating"><?php echo $hotel['hotel_rating'];?></span>
                                    </div>
                                </div>
                                <div class="ttbm_hotel_lists_location">
                                    <a class="ttbm_location_separator" href="#" style="color: #555"><?php echo esc_attr( $hotel['hotel_location'] );?></a> <?php echo esc_attr( $dot_separator );?>
                                    <a class="ttbm_location_separator" href="#" style="color: #555"><?php echo esc_attr( $hotel['hotel_map_location'] );?></a> <?php echo esc_attr( $hori_separator );?>
                                    <span><?php echo esc_attr( $hotel['hotel_distance_description'] );?></span>
                                </div>
                                <div class="ttbm_hotel_lists_offer">
                                    <p class="ttbm_hotel_lists_room_description"><?php echo esc_attr( $hotel['excerpt'] );?></p>
                                </div>
                                <div class="ttbm_hotel_lists_footer">
                                    <div class="ttbm_hotel_lists_price_box">
                                        <span class="ttbm_hotel_lists_nights"><?php esc_attr_e( '1 nights, 2 adults', 'tour-booking-manager' );?></span>
                                        <span class="ttbm_hotel_lists_note"><?php esc_attr_e( 'Additional charges may apply', 'tour-booking-manager' );?></span>
                                    </div>
                                    <?php if( $list === 'search_list' ){?>
                                        <div class="ttbm_hotel_lists_button ttbm_see_available_hotel"><?php esc_attr_e( 'See availability', 'tour-booking-manager' );?></div>
                                    <?php }else{ ?>
                                        <a href="<?php echo esc_attr( $hotel['permalink'])?>" class="ttbm_hotel_lists_button"><?php esc_attr_e( 'See availability', 'tour-booking-manager' );?></a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="ttbm_hotel_inline_booking"  style="display: none">
                            <?php echo $inline_booking; ?>
                        </div>
                    </div>
                    <?php
                } ?>
            </div>
            <?php
            return ob_get_clean();
        }

        public function all_hotel_list_item( $loop, $params, $list, $popular_feature ){
            $currency = get_woocommerce_currency();
            $hotel_data = self::all_hotel_list_data( $loop );
            $date_range = '' ;
            $list = 'hotel_list';

            echo self::display_hotel_list( $hotel_data, $currency, $date_range, $list, $params, $popular_feature );
            ?>
            <input type="hidden" name="ttbm_number_of_show" id="ttbm_number_of_show" value="<?php echo esc_attr( $params['show'] );?>">
            <?php
            if( $popular_feature !== 'yes' && count( $hotel_data ) >= $params['show'] ){
            ?>
                <button id="ttbm_loadMoreHotels" class="ttbm_hotel_load_more_btn" style="display: block"><?php esc_attr_e( 'Load More', 'tour-booking-manager' );?></button>
            <?php
            }

        }
        public function ttbm_search_hotel_list_item( $hotel_data, $params ){

            $date_range = isset( $_GET['search_date_range'] ) ? $_GET['search_date_range'] : '';

            $list = 'search_list';
            $currency = get_woocommerce_currency();
            echo self::display_hotel_list( $hotel_data, $currency, $date_range, $list, $params );
            ?>
            <input type="hidden" name="ttbm_number_of_show" id="ttbm_number_of_show" value="<?php echo esc_attr( $params['show'] );?>">
            <button id="ttbm_loadMoreHotels" class="ttbm_hotel_load_more_btn" ><?php esc_attr_e( 'Load More', 'tour-booking-manager' );?></button>
        <?php

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
                        'hotel_features'                => get_post_meta( $id, 'ttbm_hotel_feat_selection', true ),
                        'hotel_activities'                => get_post_meta( $id, 'ttbm_hotel_activity_selection', true ),
                        'hotel_area_info'               => get_post_meta( $id, 'ttbm_hotel_area_info', true ),
                        'hotel_map_location'            => get_post_meta( $id, 'ttbm_hotel_map_location', true ),
                        'hotel_location'                => get_post_meta( $id, 'ttbm_hotel_location', true ),
                        'hotel_room_details'            => get_post_meta( $id, 'ttbm_room_details', true ),
                        'hotel_gallery_images_ids'      => get_post_meta( $id, 'ttbm_gallery_images_hotel', true ),
                        'hotel_distance_description'    => get_post_meta( $id, 'ttbm_hotel_distance_des', true ),
                        'hotel_rating'                  => get_post_meta( $id, 'ttbm_hotel_rating', true ),
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
            ?>
            <div class="placeholder_area filter_top_bar justifyBetween">
                    <span>
                        <?php esc_html_e(' Total ', 'tour-booking-manager'); ?>
                        <strong class="total_hotel_qty"><?php echo esc_html( $loop->post_count ); ?></strong>
                        <?php esc_html_e(' Hotel ', 'tour-booking-manager'); ?>
<!--                        --><?php //esc_html_e(' Hotel match your search criteria', 'tour-booking-manager'); ?>
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

        public static function inline_room_book( $hotel_id, $date_range ){
            //$room_lists = TTBM_Global_Function::get_post_info( $hotel_id, 'ttbm_room_details', array() );
            ob_start();
            if ( $hotel_id && $date_range ) {
                $hotel_date = explode( " - ", $date_range );
                $date1      = gmdate( 'Y-m-d', strtotime( $hotel_date[0] ) );
                $date2      = gmdate( 'Y-m-d', strtotime( $hotel_date[1] ) );
                $days       = date_diff( date_create( $date1 ), date_create( $date2 ) );

                $room_lists_new = TTBM_Global_Function::ttbm_get_full_room_ticket_info( $hotel_id, $date1, $date2 );


                ?>
                <input type="hidden" name='ttbm_tour_hotel_list' value='<?php echo esc_attr( $hotel_id ); ?>'>
                <input type="hidden" name='ttbm_hotel_num_of_day' value='<?php echo esc_attr( $days->days ); ?>'>
                <input type="hidden" name='ttbm_checkin_date' value='<?php echo esc_attr( $date1 ); ?>'>
                <input type="hidden" name='ttbm_checkout_date' value='<?php echo esc_attr( $date2 ); ?>'>
                <div class="booking_form_wrapper">
                    <div class="ttbm_widget_content" data-placeholder>
                        <?php
                            $option_name   = 'ttbm_string_availabe_ticket_list';
                            $default_title = esc_html__( 'Available Room List ', 'tour-booking-manager' );
                            include( TTBM_Function::template_path( 'layout/title_section.php' ) );
                        ?>
                        <div class="mp_tour_ticket_type">
                            <?php
                            foreach ( $room_lists_new as $ticket ) {
                                $room_name        = array_key_exists( 'ttbm_hotel_room_name', $ticket ) ? $ticket['ttbm_hotel_room_name'] : '';
                                $price            = array_key_exists( 'ttbm_hotel_room_price', $ticket ) ? $ticket['ttbm_hotel_room_price'] : 0;
                                $sale_price            = array_key_exists( 'sale_price', $ticket ) ? $ticket['sale_price'] : '';
                                $price            = TTBM_Global_Function::wc_price( $hotel_id, $price );
                                $ticket_price_raw = TTBM_Global_Function::price_convert_raw( $price );
                                $ticket_qty       = array_key_exists( 'ttbm_hotel_room_qty', $ticket ) ? $ticket['ttbm_hotel_room_qty'] : 0;
                                $reserve          = 0;
                                $min_qty          = apply_filters( 'ttbm_ticket_type_min_qty', 0 );
                                $max_qty          = apply_filters( 'ttbm_ticket_type_max_qty', 0 );
                                $sold_type = 0;
                                $available        = $ticket['available'];
                                ?>
                                <div class="mp_tour_ticket_item">
                                    <div class="ttbm-hotel-room-info">
                                        <h6><?php echo esc_html( $room_name ); ?></h6>
                                        <?php
                                        $adult_qty = array_key_exists( 'ttbm_hotel_room_capacity_adult', $ticket ) ? $ticket['ttbm_hotel_room_capacity_adult'] : 0;
                                        $child_qty = array_key_exists( 'ttbm_hotel_room_capacity_child', $ticket ) ? $ticket['ttbm_hotel_room_capacity_child'] : 0;
                                        ?>
                                        <span class="seat-count">
                                            <i class="fas fa-user-alt"></i>
                                            <?php echo esc_html($adult_qty); ?>
                                        </span>
                                        <span class="seat-count">
                                            <i class="fas fa-child-dress"></i>
                                            <?php echo esc_html($child_qty); ?>
                                        </span>
                                    </div>
                                    <p style="text-align: right;">
                                        
                                        <?php if ($sale_price) { ?>
                                            <span class="strikeLine"><?php
                                                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                                echo wp_kses_post( wc_price( $sale_price ) ); ?></span>
                                        <?php } ?>
                                        <?php echo wp_kses_post( $price ); ?>/
                                        <?php esc_html_e( 'Night ', 'tour-booking-manager' ); ?>&nbsp;X
                                        <?php echo esc_html( $days->days ); ?>
                                        
                                    </p>
                                    <div style="text-align: right;" class="ttbm_hotel_room_incDec"><?php TTBM_Layout::qty_input( $room_name, $available, 'inputbox', 0, $min_qty, $max_qty, $ticket_price_raw, 'ticket_qty[]' ); ?></div>
                                </div>
                                <div>
                                    <input type="hidden" name='hotel_id[]' value='<?php echo esc_html( $hotel_id ); ?>'>
                                    <input type="hidden" name='ticket_name[]' value='<?php echo esc_html( $room_name ); ?>'>
                                    <input type="hidden" name='ticket_max_qty[]' value='<?php echo esc_html( $max_qty ); ?>'>
                                    <?php do_action( 'ttbm_after_ticket_type_item', $hotel_id, $ticket ); ?>
                                </div>
                            <?php } ?>
                            
                        </div>
                    </div>
                </div>
                <?php
                include( TTBM_Function::template_path( 'ticket/extra_service.php' ) );
                do_action( 'ttbm_book_now_before', $hotel_id ); ?>
                    <div class="dLayout_xs justifyBetween ttbm_book_now_area" data-placeholder>
                        <div class="fdColumn">
                            <p><strong> <?php esc_html_e( 'Quantity : ', 'tour-booking-manager' ); ?></strong>&nbsp;<span class="tour_qty"></span></p>
                            <p><strong> <?php esc_html_e( 'Total : ', 'tour-booking-manager' ); ?></strong>&nbsp;<span class="tour_price"></span></p>
                        </div>
                        <div class=" justifyBetween ttbm_hotel_book_cancel_btn">
                            <button class="dButton ttbm_multiple_hotel_book_now"  type="button">
                                <span class="fas fa-cart-plus"></span>
                                <?php esc_html_e( 'Book Now', 'tour-booking-manager' ); ?>
                            </button>
                            <button class="dButton ttbm_cancel_hotel_book"  type="button">
                                <span class="fas fa-times"></span>
                                <?php esc_html_e( 'Cancel', 'tour-booking-manager' ); ?>
                            </button>
                        </div>
                    </div>
                <?php
//                include( TTBM_Function::template_path( 'ticket/hotel_book_now.php' ) );
            }
            else {
                ?>
                <div class="dLayout allCenter _mT_bgWarning" data-placeholder>
                    <h3 class="textWhite"><?php esc_html_e( 'No Room available !', 'tour-booking-manager' ); ?></h3>
                </div>
                <?php
            }

            return ob_get_clean();
        }


    }

    new TTBM_Hotel_Data_Display();
}