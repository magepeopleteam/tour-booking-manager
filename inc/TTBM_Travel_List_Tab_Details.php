<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('TTBM_Travel_List_Tab_Details')) {
    class TTBM_Travel_List_Tab_Details{
        public function __construct() {

            add_action('ttbm_travel_list_tour_package_header', array($this, 'travel_list_tour_package_header'), 10, 1);
            add_action('ttbm_travel_lists_tab_display', array($this, 'travel_lists_tab_display'), 10, 2);

        }

    public static function edit_location_popup( $term_id, $button_name ){

            if( $term_id ){
                $term = get_term( $term_id, 'ttbm_tour_location' );

                $term_name = esc_html( $term->name );
                $term_slug = esc_html( $term->slug );
                $description = esc_html( $term->description );
                $parent = esc_html( $term->parent );

                $meta = get_term_meta( $term_id );


                $location_image = isset( $meta['ttbm_location_image'][0] ) ? $meta['ttbm_location_image'][0] : '';
                $full_address = isset( $meta['ttbm_location_address'][0] ) ? $meta['ttbm_location_address'][0] : '';
                $country_location = isset( $meta['ttbm_country_location'][0] ) ? $meta['ttbm_country_location'][0] : '';
                $img_url = isset( $meta['ttbm_location_image'][0] ) && !empty( $meta['ttbm_location_image'][0] )
                    ? wp_get_attachment_image_url( $meta['ttbm_location_image'][0], 'thumbnail')
                    : '';
            }else{
                $term_name = '';
                $term_slug = '';
                $description = '';
                $location_image = '';
                $full_address = '';
                $country_location = '';
                $parent = '';
                $img_url =  '';
            }

            ob_start();
            ?>
            <div id="ttbm-location-popup" class="ttbm-popup-overlay" style="display:flex;">
                <div class="ttbm-popup-box">
                    <h3><?php echo __('Add New Tour Location','tour-booking-manager'); ?></h3>

                    <!--Term Common Fields-->
                    <?php wp_kses_post( self::add_term_common_fields( $term_name, $term_slug, $description ) );?>

                    <!--parent Location-->
                    <?php self::parent_location_add( $parent );?>

                    <!--Full Address-->
                    <?php wp_kses_post( self::location_full_address_add( $full_address ) );?>

                    <!--Country-->
                    <?php wp_kses_post( self::country_add( $country_location ) );?>

                    <!--Image-->
                    <?php wp_kses_post( self::image_add( $location_image, $img_url ) )?>

                    <div class="ttbm-popup-buttons" id="<?php echo esc_attr( $term_id )?>">
                        <button class="ttbm-save-location"><?php echo esc_attr( $button_name )?></button>
                        <button id="ttbm-close-popup"><?php echo __('Cancel','tour-booking-manager'); ?></button>
                    </div>
                </div>
            </div>

        <?php
        return ob_get_clean();
        }

        public static function add_term_common_fields( $term_name, $term_slug, $description ){ ?>
            <label><?php echo __('Name:','tour-booking-manager'); ?></label>
            <input type="text" value="<?php echo esc_attr( $term_name );?>" id="ttbm-location-name" placeholder="Location Name">

            <label><?php echo __('Slug:','tour-booking-manager'); ?></label>
            <input type="text" id="ttbm-location-slug" value="<?php echo esc_attr( $term_slug );?>" placeholder="Optional Slug">

            <label><?php echo __('Description','tour-booking-manager'); ?>:</label>
            <textarea id="ttbm-location-desc" placeholder="Short description"><?php echo esc_attr( $description )?></textarea>

        <?php }

        public static function location_full_address_add( $full_address ){ ?>
            <label><?php echo __('Full Address','tour-booking-manager'); ?>:</label>
            <textarea id="ttbm-location-address" placeholder="Full address"><?php echo esc_attr( $full_address )?></textarea>

        <?php }

        public static function parent_location_add( $parent ){ ?>
            <label><?php echo __('Parent Location:','tour-booking-manager'); ?></label>
            <select id="ttbm-location-parent">
                <option value=""><?php echo __('— None —','tour-booking-manager'); ?></option>
                <?php
                $terms = get_terms(['taxonomy' => 'ttbm_tour_location', 'hide_empty' => false]);
                if( is_array( $terms ) && !empty( $terms ) ){
                    foreach ($terms as $term) {
                        if( $parent == $term->term_id){
                            $selected = 'selected';
                        }else{
                            $selected = '';
                        }
                        echo "<option value='{$term->term_id}' {$selected}>" . esc_html($term->name) . "</option>";
                    }
                }
                ?>
            </select>
        <?php }

        public static function country_add( $country_location ){
            $country_list = ttbm_get_coutnry_arr();
            ?>
            <label><?php echo __('Country','tour-booking-manager'); ?>:</label>
            <select id="ttbm-location-country">
                <?php foreach ($country_list as $key => $country) {
                    if( $key === $country_location ){
                        $selected = 'selected';
                    }else{
                        $selected = '';
                    }
                    ?>
                    <option value="<?php echo esc_attr( $key )?>" <?php echo esc_attr( $selected )?>> <?php echo esc_attr( $country ); ?></option>
                <?php }?>
            </select>
        <?php }

        public static function image_add( $location_image, $img_url ){ ?>
            <label><?php echo __('Image','tour-booking-manager'); ?>:</label>
            <div>
                <button id="ttbm-upload-image" type="button"><?php echo __('Upload Image','tour-booking-manager'); ?></button>
                <input type="hidden" id="ttbm-location-image-id" value="<?php echo esc_attr( $location_image )?>">
                <div id="ttbm-image-preview">
                    <img src="<?php echo esc_attr( $img_url )?>" style="max-width:100px;">
                </div>
            </div>
        <?php }

        public function travel_lists_tab_display( $label, $b ){
            $category = '';
            ?>
            <div class="ttbm_trvel_lists_tab_holder">
                <div class="ttbm_travel_list_popup" id="ttbm_travel_list_popup"></div>
                <div class="ttbm_trvel_lists_tabs">
                    <button class="active" data-target="ttbm_trvel_lists_tour"><?php echo __(' Tour Package','tour-booking-manager'); ?></button>
                    <button data-target="ttbm_trvel_lists_places"><?php echo __('Tourist Attraction','tour-booking-manager'); ?></button>
                    <button data-target="ttbm_trvel_lists_organiser"><?php echo __('Trip Organiser','tour-booking-manager'); ?></button>
                    <button data-target="ttbm_trvel_lists_location"><?php echo __('Trip Location','tour-booking-manager'); ?></button>
                    <button data-target="ttbm_trvel_lists_features"><?php echo __('Features','tour-booking-manager'); ?></button>
                    <button data-target="ttbm_trvel_lists_tag"><?php echo __('Tags','tour-booking-manager'); ?></button>
                    <button data-target="ttbm_trvel_lists_activities"><?php echo __('Activities','tour-booking-manager'); ?></button>
                </div>

                <div id="ttbm_trvel_lists_tour" class="ttbm_trvel_lists_content active">
                    <?php do_action( 'ttbm_travel_list_tour_package_header', $label);?>
                </div>
                <div id="ttbm_trvel_lists_places" class="ttbm_trvel_lists_content">
                    <?php do_action( 'ttbm_travel_list_category', $category);?>
                    <a href="<?php echo admin_url('post-new.php?post_type=ttbm_places'); ?>">
                        <button class="ttbm-button">Add New Places</button>
                    </a>
                    <div class="ttbm_travel_list_places_content" id="ttbm_travel_list_places_content">
                        <div class="ttbm_travel_content_loader">Loading...</div>
                    </div>
                </div>

                <div id="ttbm_trvel_lists_organiser" class="ttbm_trvel_lists_content">
                    <p><?php echo __('Content for Trip Organiser','tour-booking-manager'); ?></p>

                    <button>Add new organiser</button>
                    <div class="ttbm_travel_list_organiser_content" id="ttbm_travel_list_organiser_content">
                        <div class="ttbm_travel_content_loader">Loading...</div>
                    </div>
                </div>
                <div id="ttbm_trvel_lists_location" class="ttbm_trvel_lists_content">
                    <?php do_action( 'ttbm_add_new_location_popup', 'ttbm_tour_location' );?>
                    <p><?php echo __('Content for Trip Locationg','tour-booking-manager'); ?></p>
                    <div id="ttbm-add-new-location-btn"><?php echo __('Add New Locations','tour-booking-manager'); ?></div>
                    <div class="ttbm_travel_list_location_shows" id="ttbm_travel_list_location_shows">
                        <div class="ttbm_travel_content_loader">Loading...</div>
                    </div>
                </div>

                <div id="ttbm_trvel_lists_features" class="ttbm_trvel_lists_content">
                    <p>Content for Features</p>
                    <button>Add new Feature</button>
                    <div class="ttbm_travel_list_feature_content" id="ttbm_travel_list_feature_content">
                        <div class="ttbm_travel_content_loader">Loading...</div>
                    </div>
                </div>

                <div id="ttbm_trvel_lists_tag" class="ttbm_trvel_lists_content">
                    <p>Content for Tag nre</p>
                    <button >Add New tag</button>
                    <div class="ttbm_travel_list_tag_content" id="ttbm_travel_list_tag_content">
                        <div class="ttbm_travel_content_loader">Loading...</div>
                    </div>
                </div>

                <div id="ttbm_trvel_lists_activities" class="ttbm_trvel_lists_content">
                    <p>Content for Activities</p>
                    <button >Add New Activities</button>
                    <div class="ttbm_travel_list_activies_content" id="ttbm_travel_list_activies_content">
                        <div class="ttbm_travel_content_loader">Loading...</div>
                    </div>

                </div>

            </div>
        <?php  }

        public static function travel_list_tour_package_header( $label ){ ?>
            <div class="ttbm-tour-list-header">
                <h1 class="page-title"><?php echo esc_html($label).__(' Lists','tour-booking-manager'); ?></h1>
                <div class="ttbm_tour_search_add_holder">
                    <input type="text" name="ttbm_tour_search" id="ttbm-tour-search" data-nonce="<?php echo wp_create_nonce("ttbm_search_nonce"); ?>" placeholder="Search <?php echo esc_html($label); ?>">
                    <a href="<?php echo admin_url('post-new.php?post_type=ttbm_tour'); ?>" class="page-title-action">
                        <i class="fas fa-plus"></i> <?php esc_html_e('Add New', 'tour-booking-manager'); ?>
                    </a>
                </div>
            </div>
        <?php }


    }

    new TTBM_Travel_List_Tab_Details();
}