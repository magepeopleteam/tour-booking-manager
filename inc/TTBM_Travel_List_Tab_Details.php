<?php
	if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('TTBM_Travel_List_Tab_Details')) {
    class TTBM_Travel_List_Tab_Details{
        public function __construct() {

            add_action('ttbm_travel_list_tour_package_header', array($this, 'travel_list_tour_package_header'), 10, 2 );
            add_action('ttbm_travel_lists_tab_display', array($this, 'travel_lists_tab_display'), 10, 3);
            add_action('admin_head', [$this,'remove_admin_notice']);
        }

        public static function get_taxonomy_type( $tab_type ){

            $taxonomy_type = '';
            if( $tab_type === 'Add New Locations' ){
                $taxonomy_type = 'ttbm_tour_location';
            }else if( $tab_type === 'Add New Organiser' ){
                $taxonomy_type = 'ttbm_tour_org';
            }else if( $tab_type === 'Add New Places' ){
                $taxonomy_type = 'ttbm_places';
            }else if( $tab_type === 'Add New Feature' ){
                $taxonomy_type = 'ttbm_tour_features_list';
            }else if( $tab_type === 'Add New Tag' ){
                $taxonomy_type = 'ttbm_tour_tag';
            }else if( $tab_type === 'Add New Activities' ){
                $taxonomy_type = 'ttbm_tour_activities';
            }else if( $tab_type === 'Add New Category' ){
                $taxonomy_type = 'ttbm_tour_cat';
            }

            return $taxonomy_type;
        }

        public function remove_admin_notice(){
            $screen = get_current_screen();
            if ($screen && $screen->id === 'ttbm_tour_page_ttbm_list') {
                remove_all_actions('admin_notices');
                remove_all_actions('all_admin_notices');
            }
        }

        public static function edit_location_popup( $term_id, $button_name, $tab_type ){

            $meta = [];
            $taxonomy_type= self::get_taxonomy_type( $tab_type );


            if( $tab_type === 'Add New Places' ){
                if( $term_id ){

                    $post_id = $term_id; // Replace with dynamic post ID

                    $term_name = '';
                    $term_slug = '';
                    $description = '';
                    $location_image = '';
                    $img_url = '';

                    $post = get_post($post_id);

                    if ($post && $post->post_type === 'ttbm_places') {
                        $term_name = $post->post_title;
                        $term_slug = $post->post_name;
                        $description = $post->post_content;

                        $location_image = get_post_thumbnail_id($post_id);

                        if ($location_image) {
                            $img_url = wp_get_attachment_url($location_image);
                        }
                    }

                }
                else{
                    $term_name = '';
                    $term_slug = '';
                    $description = '';
                    $location_image = '';
                    $full_address = '';
                    $country_location = '';
                    $parent = '';
                    $img_url =  '';
                }
            }else{
                if( $term_id ){
                    $term = get_term( $term_id, $taxonomy_type );
                    $term_name = esc_html( $term->name );
                    $term_slug = esc_html( $term->slug );
                    $description = esc_html( $term->description );
                    $parent = esc_html( $term->parent );
                    $meta = get_term_meta( $term_id );

                    if( $tab_type === 'Add New Locations' ){
                        $location_image = isset( $meta['ttbm_location_image'][0] ) ? $meta['ttbm_location_image'][0] : '';
                        $full_address = isset( $meta['ttbm_location_address'][0] ) ? $meta['ttbm_location_address'][0] : '';
                        $country_location = isset( $meta['ttbm_country_location'][0] ) ? $meta['ttbm_country_location'][0] : '';
                        $img_url = isset( $meta['ttbm_location_image'][0] ) && !empty( $meta['ttbm_location_image'][0] )
                            ? wp_get_attachment_image_url( $meta['ttbm_location_image'][0], 'thumbnail')
                            : '';
                    }else{
                        $location_image = '';
                        $full_address = '';
                        $country_location = '';
                        $img_url =  '';
                    }

                }
                else{
                    $term_name = '';
                    $term_slug = '';
                    $description = '';
                    $location_image = '';
                    $full_address = '';
                    $country_location = '';
                    $parent = '';
                    $img_url =  '';
                }
            }



            ob_start();
            if ( $tab_type === 'Add New Places' ) {
                $save_btn_class = 'ttbm-save-places_data';
            } else {
                $save_btn_class = 'ttbm-save-location';
            }
            ?>
            <div id="ttbm-location-popup" class="ttbm-popup-overlay" style="display:flex;">
                <div class="ttbm-popup-box ttbm-taxonomy-popup" role="dialog" aria-modal="true" aria-labelledby="ttbm-taxonomy-popup-title">
                    <div class="ttbm-taxonomy-popup__header">
                        <h3 id="ttbm-taxonomy-popup-title" class="ttbm-taxonomy-popup__title"><?php echo esc_html( $tab_type ); ?></h3>
                        <button type="button" id="ttbm-close-popup" class="ttbm-taxonomy-popup__close ttbm-taxonomy-popup__cancel" aria-label="<?php esc_attr_e( 'Close', 'tour-booking-manager' ); ?>">
                            <span class="fas fa-times" aria-hidden="true"></span>
                        </button>
                    </div>

                    <div class="ttbm-taxonomy-popup__body">
                        <div class="ttbm-taxonomy-popup-fields">
                            <?php self::add_term_common_fields( $term_name, $term_slug, $description, $tab_type ); ?>

                            <?php
                            if ( $tab_type !== 'Add New Tag' && $tab_type !== 'Add New Places' ) {
                                self::parent_taxonomy_add( $parent, $taxonomy_type, $tab_type );
                            }

                            if ( $tab_type === 'Add New Locations' ) {
                                self::location_full_address_add( $full_address );
                                self::country_add( $country_location );
                                self::image_add( $location_image, $img_url );
                            }

                            if ( $tab_type === 'Add New Places' ) {
                                self::image_add( $location_image, $img_url );
                            }

                            $icon_class = '';
                            if ( $tab_type === 'Add New Activities' ) {
                                if ( ! empty( $meta ) && isset( $meta['ttbm_tour_activities'] ) ) {
                                    $icon_class = $meta['ttbm_activities_icon'][0];
                                }
                                self::add_icon( __( 'Activity Icon', 'tour-booking-manager' ), 'ttbm_activity_icon', $icon_class );
                            }

                            if ( $tab_type === 'Add New Feature' ) {
                                if ( ! empty( $meta ) && isset( $meta['ttbm_feature_icon'] ) ) {
                                    $icon_class = $meta['ttbm_feature_icon'][0];
                                }
                                self::add_icon( __( 'Feature Icon', 'tour-booking-manager' ), 'ttbm_feature_icon', $icon_class );
                            }
                            ?>
                        </div>
                    </div>

                    <div class="ttbm-taxonomy-popup__footer">
                        <p class="ttbm-taxonomy-popup-form-message" role="alert" aria-live="polite" hidden></p>
                        <div class="ttbm-popup-buttons" id="<?php echo esc_attr( $term_id ); ?>">
                            <input type="hidden" class="ttbm_get_clicked_tab_name" value="<?php echo esc_attr( $tab_type ); ?>">
                            <button type="button" id="ttbm-close-popup-footer" class="ttbm-taxonomy-popup__btn ttbm-taxonomy-popup__btn--secondary ttbm-taxonomy-popup__cancel"><?php esc_html_e( 'Cancel', 'tour-booking-manager' ); ?></button>
                            <button type="button" class="ttbm-taxonomy-popup__btn ttbm-taxonomy-popup__btn--primary <?php echo esc_attr( $save_btn_class ); ?>"><?php echo esc_html( $button_name ); ?></button>
                        </div>
                    </div>
                </div>
            </div>

        <?php
        return ob_get_clean();
        }

        public static function taxonomy_popup_field( $label, $content, $for = '' ) {
            ?>
            <div class="ttbm-taxonomy-popup-field"<?php echo $for ? ' data-field="' . esc_attr( $for ) . '"' : ''; ?>>
                <label class="ttbm-taxonomy-popup-label"<?php echo $for ? ' for="' . esc_attr( $for ) . '"' : ''; ?>><?php echo esc_html( $label ); ?></label>
                <?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted field markup ?>
                <?php if ( $for ) : ?>
                    <p class="ttbm-taxonomy-popup-error" role="alert" aria-live="polite" hidden></p>
                <?php endif; ?>
            </div>
            <?php
        }

        public static function travel_list_content_skeleton( $count = 6 ) {
            ob_start();
            ?>
            <div class="ttbm_travel_skeleton_loader ttbm_travel_skeleton-loader--grid" aria-busy="true" aria-label="<?php esc_attr_e( 'Loading', 'tour-booking-manager' ); ?>">
                <?php for ( $i = 0; $i < $count; $i++ ) : ?>
                    <div class="ttbm_travel_skeleton_card">
                        <div class="ttbm_travel_skeleton_thumb" aria-hidden="true"></div>
                        <div class="ttbm_travel_skeleton_body">
                            <span class="ttbm_travel_skeleton_line ttbm_travel_skeleton-line--title" aria-hidden="true"></span>
                            <span class="ttbm_travel_skeleton_line" aria-hidden="true"></span>
                            <span class="ttbm_travel_skeleton_line ttbm_travel_skeleton-line--short" aria-hidden="true"></span>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
            <?php
            return ob_get_clean();
        }

        public static function get_taxonomy_name_placeholder( $tab_type ) {
            $placeholders = array(
                'Add New Category'   => __( 'e.g. Fixed Tour', 'tour-booking-manager' ),
                'Add New Locations'  => __( 'e.g. Paris, France', 'tour-booking-manager' ),
                'Add New Organiser'  => __( 'e.g. Adventure Tours Co.', 'tour-booking-manager' ),
                'Add New Feature'    => __( 'e.g. Free WiFi', 'tour-booking-manager' ),
                'Add New Tag'        => __( 'e.g. Family Friendly', 'tour-booking-manager' ),
                'Add New Activities' => __( 'e.g. Hiking', 'tour-booking-manager' ),
            );

            return $placeholders[ $tab_type ] ?? __( 'Enter name', 'tour-booking-manager' );
        }

        public static function add_term_common_fields( $term_name, $term_slug, $description, $tab_type ) {
            $name_placeholder = self::get_taxonomy_name_placeholder( $tab_type );

            ob_start();
            ?>
            <input type="text" class="ttbm-taxonomy-popup-input" value="<?php echo esc_attr( $term_name ); ?>" id="ttbm-location-name" placeholder="<?php echo esc_attr( $name_placeholder ); ?>">
            <?php
            self::taxonomy_popup_field( __( 'Name', 'tour-booking-manager' ), ob_get_clean(), 'ttbm-location-name' );

            if ( $tab_type !== 'Add New Places' ) {
                ob_start();
                ?>
                <input type="text" class="ttbm-taxonomy-popup-input" id="ttbm-location-slug" value="<?php echo esc_attr( $term_slug ); ?>" placeholder="<?php esc_attr_e( 'e.g. fixed-tour', 'tour-booking-manager' ); ?>">
                <?php
                self::taxonomy_popup_field( __( 'Slug', 'tour-booking-manager' ), ob_get_clean(), 'ttbm-location-slug' );
            }

            ob_start();
            ?>
            <textarea class="ttbm-taxonomy-popup-input ttbm-taxonomy-popup-textarea" id="ttbm-location-desc" placeholder="<?php esc_attr_e( 'Enter a short description...', 'tour-booking-manager' ); ?>" rows="4"><?php echo esc_textarea( $description ); ?></textarea>
            <?php
            self::taxonomy_popup_field( __( 'Description', 'tour-booking-manager' ), ob_get_clean(), 'ttbm-location-desc' );
        }

        public static function location_full_address_add( $full_address ) {
            ob_start();
            ?>
            <textarea class="ttbm-taxonomy-popup-input ttbm-taxonomy-popup-textarea" id="ttbm-location-address" placeholder="<?php esc_attr_e( 'Enter full address...', 'tour-booking-manager' ); ?>" rows="3"><?php echo esc_textarea( $full_address ); ?></textarea>
            <?php
            self::taxonomy_popup_field( __( 'Full Address', 'tour-booking-manager' ), ob_get_clean(), 'ttbm-location-address' );
        }

        public static function add_icon( $taxonomy_type, $taxonomy_name, $icon_class = '' ) {
            ob_start();
            ?>
            <div class="ttbm-taxonomy-popup-icon-wrap">
                <?php do_action( 'ttbm_input_add_icon', $taxonomy_name, $icon_class ); ?>
            </div>
            <?php
            self::taxonomy_popup_field( $taxonomy_type, ob_get_clean(), $taxonomy_name );
        }

        public static function get_parent_taxonomy_label( $tab_type ) {
            $labels = array(
                'Add New Category'   => __( 'Parent Category', 'tour-booking-manager' ),
                'Add New Locations'  => __( 'Parent Location', 'tour-booking-manager' ),
                'Add New Organiser'  => __( 'Parent Organiser', 'tour-booking-manager' ),
                'Add New Feature'    => __( 'Parent Feature', 'tour-booking-manager' ),
                'Add New Activities' => __( 'Parent Activity', 'tour-booking-manager' ),
            );

            return $labels[ $tab_type ] ?? __( 'Parent', 'tour-booking-manager' );
        }

        public static function parent_taxonomy_add( $parent, $taxonomy_type, $tab_type = '' ) {
            $parent_label = self::get_parent_taxonomy_label( $tab_type );

            ob_start();
            ?>
            <div class="ttbm-taxonomy-popup-select-wrap">
                <select class="ttbm-taxonomy-popup-input ttbm-taxonomy-popup-select" id="ttbm-location-parent">
                    <option value=""><?php esc_html_e( '— None —', 'tour-booking-manager' ); ?></option>
                    <?php
                    $terms = get_terms( array( 'taxonomy' => $taxonomy_type, 'hide_empty' => false ) );
                    if ( is_array( $terms ) && ! empty( $terms ) ) {
                        foreach ( $terms as $term ) {
                            $selected = ( (string) $parent === (string) $term->term_id ) ? 'selected' : '';
                            ?>
                            <option value="<?php echo esc_attr( $term->term_id ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $term->name ); ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
            </div>
            <?php
            self::taxonomy_popup_field( $parent_label, ob_get_clean(), 'ttbm-location-parent' );
        }

        public static function country_add( $country_location ) {
            $country_list = ttbm_get_coutnry_arr();

            ob_start();
            ?>
            <div class="ttbm-taxonomy-popup-select-wrap">
                <select class="ttbm-taxonomy-popup-input ttbm-taxonomy-popup-select" id="ttbm-location-country">
                    <?php foreach ( $country_list as $key => $country ) {
                        $selected = ( $key === $country_location ) ? 'selected' : '';
                        ?>
                        <option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_html( $country ); ?></option>
                    <?php } ?>
                </select>
            </div>
            <?php
            self::taxonomy_popup_field( __( 'Country', 'tour-booking-manager' ), ob_get_clean(), 'ttbm-location-country' );
        }

        public static function image_add( $location_image, $img_url ) {
            ob_start();
            ?>
            <div class="ttbm-taxonomy-popup-image">
                <button type="button" id="ttbm-upload-image" class="ttbm-taxonomy-popup__btn ttbm-taxonomy-popup__btn--secondary"><?php esc_html_e( 'Upload Image', 'tour-booking-manager' ); ?></button>
                <input type="hidden" id="ttbm-location-image-id" value="<?php echo esc_attr( $location_image ); ?>">
                <div id="ttbm-image-preview" class="ttbm-taxonomy-popup-image-preview">
                    <?php if ( $img_url ) : ?>
                        <img src="<?php echo esc_url( $img_url ); ?>" alt="">
                    <?php endif; ?>
                </div>
            </div>
            <?php
            self::taxonomy_popup_field( __( 'Image', 'tour-booking-manager' ), ob_get_clean() );
        }

        public static function shortcode_display( $type ){ ?>

            <div class="ttbm_promotional_shortcode">
                <div class="ttbm_shortcode_box">
                    <?php if( $type === 'attraction'){?>
                        <div class="ttbm_shortcode_text">[ttbm-top-attractions show=5 column=4 carousel='yes' load-more-button= 'yes']</div>
                    <?php } else if( $type === 'location'){?>
                        <div class="ttbm_shortcode_text">[travel-location-list show=5 column=2]</div>
                    <?php } else if($type === 'activity'){?>
                        <div class="ttbm_shortcode_text">[ttbm-activity_browse show=3 column=5 carousel='yes' load-more-button= 'yes']</div>
                    <?php } else{?>
                        <div class="ttbm_shortcode_text">[ttbm-texonomy-display type='<?php echo esc_attr( $type );?>' show=3 column=3 carousel='no' load-more-button= 'yes']</div>
                    <?php }?>
                    <button class="ttbm_copy_btn"><?php esc_attr_e( 'Copy', 'tour-booking-manager' );?></button>
                </div>
            </div>
        <?php }

        public static function ttbm_travel_list_tab_header( $type, $tab_subtitle, $add_new_btn_title, $search_name, $search_id, $place_holder, $add_btn_class_name, $is_btn_link = '', $ttbm_sub_title_class= '' ){
            ?>
            <?php echo self::shortcode_display($type) ; ?>
            <div class="ttbm-tour-list-header">
                <div class="ttbm_tab_header_shortcode_title">
                    <h1 class="page-title <?php echo esc_attr( $ttbm_sub_title_class );?>"><?php echo esc_attr( $tab_subtitle )?></h1>
                    
                </div>
                <div class="ttbm_tour_search_add_holder ttbm_travel_taxonomy_toolbar">
                    <?php if ( $is_btn_link === '' ) { ?>
                        <button type="button" class="page-title-action ttbm_travel_taxonomy_add_btn <?php echo esc_attr( $add_btn_class_name ); ?>">
                            <span class="ttbm_travel_taxonomy_add_btn__icon" aria-hidden="true"><i class="fas fa-plus"></i></span>
                            <span class="ttbm_travel_taxonomy_add_btn__text"><?php echo esc_html( $add_new_btn_title ); ?></span>
                        </button>
                    <?php } else { ?>
                        <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ttbm_places' ) ); ?>" class="page-title-action ttbm_travel_taxonomy_add_btn ttbm_travel_taxonomy_add_link">
                            <span class="ttbm_travel_taxonomy_add_btn__icon" aria-hidden="true"><i class="fas fa-plus"></i></span>
                            <span class="ttbm_travel_taxonomy_add_btn__text"><?php echo esc_html( $add_new_btn_title ); ?></span>
                        </a>
                    <?php } ?>
                    <div class="ttbm_travel_taxonomy_search_wrap">
                        <i class="fas fa-search ttbm_travel_taxonomy_search_icon" aria-hidden="true"></i>
                        <input
                            type="search"
                            class="ttbm_travel_taxonomy_search_input"
                            name="<?php echo esc_attr( $search_name ); ?>"
                            id="<?php echo esc_attr( $search_id ); ?>"
                            placeholder="<?php echo esc_attr( $place_holder ); ?>"
                            autocomplete="off"
                        >
                    </div>
                </div>
            </div>
            
        <?php }

        public function travel_lists_tab_display( $label, $b, $posts_query ){
            $category = '';
            ?>
            <div class="ttbm_trvel_lists_tab_holder">

                <?php //wp_kses_post( self::icon_popup());?>
                <?php self::icon_popup(); ?>


                <div class="ttbm_travel_list_popup" id="ttbm_travel_list_popup"></div>
                

                <div id="ttbm_trvel_lists_tour" class="ttbm_trvel_lists_content active">
                    <?php do_action( 'ttbm_travel_list_tour_package_header', $label, $posts_query );?>
                </div>
                <div id="ttbm_trvel_lists_places" class="ttbm_trvel_lists_content">
                    <?php do_action( 'ttbm_travel_list_category', $category);?>

                    <?php self::ttbm_travel_list_tab_header( 'attraction', 'Places', 'Add New Places', 'ttbm_tourist_place_Search', 'ttbm_tourist_place_Search', 'Search Tourist Placess',  'ttbm-add-new-taxonomy-btn', '', 'ttbm_places_sub_title_class' );?>

                    <div class="ttbm_travel_list_places_content" id="ttbm_travel_list_places_content">
                        <div class="ttbm_travel_content_loader"><?php echo self::travel_list_content_skeleton(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                    </div>

                    <div class="ttbm_places_load_more_holder" id="ttbm_places_load_more_holder" style="display: none">
                        <span class="ttbm_places_load_more_btn" id="ttbm_places_load_more_btn"><?php esc_attr_e( 'Load more', 'tour-booking-manager' )?></span>
                    </div>
                </div>

                <div id="ttbm_trvel_lists_organiser" class="ttbm_trvel_lists_content">

                    <?php  self::ttbm_travel_list_tab_header( 'organizer','Trip Organiser', 'Add New Organiser', 'ttbm_tourist_organiser_Search', 'ttbm_tourist_organiser_Search', 'Search Organiser',  'ttbm-add-new-taxonomy-btn'  );?>

                    <div class="ttbm_travel_list_organiser_content" id="ttbm_travel_list_organiser_content">
                        <div class="ttbm_travel_content_loader"><?php echo self::travel_list_content_skeleton(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                    </div>
                </div>
                <div id="ttbm_trvel_lists_location" class="ttbm_trvel_lists_content">
                    <?php do_action( 'ttbm_add_new_location_popup', 'ttbm_tour_location' );?>

                    <?php self::ttbm_travel_list_tab_header( 'location' ,'Trip Location', 'Add New Locations', 'ttbm_tourist_location_Search', 'ttbm_tourist_location_Search', 'Search Location',  'ttbm-add-new-taxonomy-btn', '','ttbm_location_sub_title_class' );?>
                    <div class="ttbm_travel_list_location_shows" id="ttbm_travel_list_location_shows">
                        <div class="ttbm_travel_content_loader"><?php echo self::travel_list_content_skeleton(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                    </div>

                    <div class="ttbm_plocation_load_more_holder" id="ttbm_plocation_load_more_holder" style="display: none">
                        <span class="ttbm-location-load-more" id="ttbm-location-load-more"><?php esc_attr_e( 'Load more', 'tour-booking-manager' )?></span>
                    </div>

                </div>

                <div id="ttbm_trvel_lists_features" class="ttbm_trvel_lists_content">

                    <?php self::ttbm_travel_list_tab_header( 'feature','Content for Features', 'Add New Feature', 'ttbm_tab_features_Search', 'ttbm_tab_features_Search', 'Search Features',  'ttbm-add-new-taxonomy-btn' );?>

                    <div class="ttbm_travel_list_feature_content" id="ttbm_travel_list_feature_content">
                        <div class="ttbm_travel_content_loader"><?php echo self::travel_list_content_skeleton(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                    </div>
                </div>

                <div id="ttbm_trvel_lists_tour_category" class="ttbm_trvel_lists_content">

                    <?php self::ttbm_travel_list_tab_header( 'category','Content for Category', 'Add New Category', 'ttbm_tab_category_search', 'ttbm_tab_category_search', 'Search Category',  'ttbm-add-new-taxonomy-btn' );?>

                    <div class="ttbm_travel_list_category_content" id="ttbm_travel_list_category_content">
                        <div class="ttbm_travel_content_loader"><?php echo self::travel_list_content_skeleton(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                    </div>
                </div>

                <div id="ttbm_trvel_lists_tag" class="ttbm_trvel_lists_content">

                    <?php self::ttbm_travel_list_tab_header( 'tag','Content for Tag', 'Add New Tag', 'ttbm_tab_tag_Search', 'ttbm_tab_tag_Search', 'Search Tag',  'ttbm-add-new-taxonomy-btn'  );?>
                    <div class="ttbm_travel_list_tag_content" id="ttbm_travel_list_tag_content">
                        <div class="ttbm_travel_content_loader"><?php echo self::travel_list_content_skeleton(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                    </div>
                </div>

                <div id="ttbm_trvel_lists_activities" class="ttbm_trvel_lists_content">

                    <?php self::ttbm_travel_list_tab_header( 'activity','All Activities', 'Add New Activities', 'ttbm_tab_activities_Search', 'ttbm_tab_activities_Search', 'Search Activities',  'ttbm-add-new-taxonomy-btn'  );?>

                    <div class="ttbm_travel_list_activies_content" id="ttbm_travel_list_activies_content">
                        <div class="ttbm_travel_content_loader"><?php echo self::travel_list_content_skeleton(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
                    </div>

                </div>

            </div>
        <?php  }

        public static function travel_list_tour_package_header( $label, $posts_query ){
            $counts = wp_count_posts('ttbm_tour');
            /*$total_count     = array_sum((array) $counts);
            $published_count = isset($counts->publish) ? $counts->publish : 0;
            $trash_count     = isset($counts->trash) ? $counts->trash : 0;
            $draft_count     = isset($counts->draft) ? $counts->draft : 0;*/

            $expire_count = 0;
            $all_tour_ids = get_posts(array(
                'post_type' => 'ttbm_tour',
                'post_status' => array('publish', 'draft', 'pending', 'future', 'private'),
                'posts_per_page' => -1,
                'fields' => 'ids',
                'no_found_rows' => true,
                'cache_results' => false,
            ));
            if (!empty($all_tour_ids)) {
                foreach ($all_tour_ids as $post_id) {
                    if (get_post_status((int)$post_id) !== 'publish') {
                        continue;
                    }
                    $upcoming_date = TTBM_Global_Function::get_post_info((int)$post_id, 'ttbm_upcoming_date');
                    if (!$upcoming_date) {
                        $all_dates = TTBM_Function::get_date((int)$post_id);
                        $upcoming_date = TTBM_Function::get_upcoming_date_month((int)$post_id, true, $all_dates);
                    }
                    if ($upcoming_date === '') {
                        $expire_count++;
                    }
                }
            }

            $published_count = isset($counts->publish) ? $counts->publish : 0;
            $trash_count     = isset($counts->trash) ? $counts->trash : 0;
            $draft_count     = isset($counts->draft) ? $counts->draft : 0;
            $pending_count   = isset($counts->pending) ? $counts->pending : 0;
            $future_count    = isset($counts->future) ? $counts->future : 0;
            $private_count   = isset($counts->private) ? $counts->private : 0;
            $total_count = $published_count + $trash_count + $draft_count + $pending_count + $future_count + $private_count;

            $trash_link = add_query_arg([
                'post_status' => 'trash',
                'post_type'   => 'ttbm_tour',
            ], admin_url('edit.php'));

            ?>
            <div class="ttbm-tour-list-header ttbm_travel_list_page_title_bg">

                <div class="ttbm_tour_list_text_header">
                    <div class="ttbm_travel_list_header_text">
                        <h1 class="ttbm_tour_page-title"><?php echo esc_html($label).esc_html__(' Lists','tour-booking-manager'); ?></h1>
                    </div>

                    <div class="ttbm_tour_count_holder">
                        <div class="ttbm_travel_filter_item ttbm_filter_btn_active_bg_color" data-filter-item="all">All (<?php echo esc_attr( $total_count )?>)</div>
                        <div class="ttbm_travel_filter_item ttbm_filter_btn_bg_color" data-filter-item="publish">Publish (<?php echo esc_attr( $published_count )?>)</div>
                        <div class="ttbm_travel_filter_item ttbm_filter_btn_bg_color" data-filter-item="draft">Draft (<?php echo esc_attr( $draft_count )?>)</div>
                        <?php if( $expire_count > 0 ){?>
                            <div class="ttbm_travel_filter_item ttbm_filter_btn_bg_color" data-filter-item="expired_tour">Expire Tour( <?php echo esc_attr( $expire_count )?>)</div>
                        <?php }?>
                        <a class="ttbm_trash_link" href="<?php echo esc_url( $trash_link )?>" target="_blank">
                            <div class="ttbm_total_trash_display">Trash Tour (<?php echo esc_attr( $trash_count )?>) </div>
                        </a>

                    </div>
                </div>

                <div class="ttbm_tour_search_add_holder ttbm_travel_taxonomy_toolbar">
                    <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=ttbm_tour' ) ); ?>" class="page-title-action ttbm_travel_taxonomy_add_btn ttbm_travel_taxonomy_add_link">
                        <span class="ttbm_travel_taxonomy_add_btn__icon" aria-hidden="true"><i class="fas fa-plus"></i></span>
                        <span class="ttbm_travel_taxonomy_add_btn__text"><?php esc_html_e( 'Add New Tour', 'tour-booking-manager' ); ?></span>
                    </a>
                    <div class="ttbm_travel_taxonomy_search_wrap">
                        <i class="fas fa-search ttbm_travel_taxonomy_search_icon" aria-hidden="true"></i>
                        <input
                            type="search"
                            class="ttbm_travel_taxonomy_search_input"
                            name="ttbm_tour_search"
                            id="ttbm-tour-search"
                            data-nonce="<?php echo esc_attr( wp_create_nonce( 'ttbm_search_nonce' ) ); ?>"
                            placeholder="<?php echo esc_attr( sprintf( /* translators: %s: tour label */ __( 'Search %s', 'tour-booking-manager' ), $label ) ); ?>"
                            autocomplete="off"
                        >
                    </div>
                </div>
            </div>
        <?php }

        public static function icon_popup() {
            if (!$GLOBALS['ttbm_icon_popup_exit']) {
                $GLOBALS['ttbm_icon_popup_exit'] = true;
                ?>
                <div class="ttbm_add_icon_popup ttbm_popup ttbm_style" data-popup="#ttbm_add_icon_popup">
                    <div class="popupMainArea fullWidth">
                        <div class="popupHeader allCenter">
                            <?php TTBM_Select_Icon_image::disaply_icon_header_in_popup(); ?>
                        </div>
                        <div class="popupBody">
                            <?php TTBM_Select_Icon_image::disaply_icon_in_popup(); ?>
                        </div>
                    </div>
                </div>
                <?php
            }
        }


    }

    new TTBM_Travel_List_Tab_Details();
}
