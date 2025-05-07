<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('TTBM_Travel_Tab_Data_Add_Display_Ajax')) {
    class TTBM_Travel_Tab_Data_Add_Display_Ajax{
        public function __construct() {
            add_action('wp_ajax_ttbm_get_locations_html', [ $this, 'ttbm_get_locations_html' ] );

            add_action('wp_ajax_ttbm_add_new_location_term', [ $this, 'ttbm_add_new_location_term' ]);
            add_action('wp_ajax_ttbm_add_new_locations_ajax_html', [ $this, 'ttbm_add_new_locations_ajax_html' ]);
            add_action('wp_ajax_ttbm_edit_locations_ajax_html', [ $this, 'ttbm_edit_locations_ajax_html' ]);

        }

        public function ttbm_get_locations_html() {
            $terms = get_terms([
                'taxonomy'   => 'ttbm_tour_location',
                'hide_empty' => false,
            ]);

            ob_start();

            if (!empty($terms) && !is_wp_error($terms)) {
                ?>
                <div class="ttbm-locations-list">
                    <?php foreach ($terms as $term):
                        $term_id    = $term->term_id;
                        $term_name  = esc_html( $term->name );
                        $term_slug  = esc_html( $term->slug );
                        $description = esc_html( $term->description );
                        $edit_link  = get_edit_term_link( $term_id, 'ttbm_tour_location' );
                        $meta    = get_term_meta( $term_id );
                        $img_url = isset( $meta['ttbm_location_image'][0] ) && !empty( $meta['ttbm_location_image'][0] )
                            ? wp_get_attachment_image_url( $meta['ttbm_location_image'][0], 'thumbnail' )
                            : 'https://i.imgur.com/GD3zKtz.png';

                        ?>
                        <div class="ttbm-location-card"
                             ttbm-data-location-name="<?php echo esc_attr( $term_name )?>"
                             ttbm-data-location-slug="<?php echo esc_attr( $term_slug )?>"
                             ttbm-data-location-id="<?php echo esc_attr( $term_id )?>"
                             ttbm-data-location-description="<?php echo esc_attr( $description )?>"
                             ttbm-data-location-img-url="<?php echo esc_attr( $img_url )?>"
                             ttbm-data-location-img-id="<?php echo esc_attr( $meta['ttbm_location_image'][0] )?>"
                             ttbm-data-location-address="<?php echo esc_attr( $meta['ttbm_location_address'][0] )?>"
                             ttbm-data-location-country="<?php echo esc_attr( $meta['ttbm_country_location'][0]  )?>"
                        >
                            <div class="ttbm-card-left">
                                <img src="<?= $img_url ?>" alt="<?= $term_name ?>" width="70" height="70">
                            </div>
                            <div class="ttbm-card-right">
                                <h3 class="ttbm-title"><?= $term_name ?></h3>
                                <p class="ttbm-description"><?= $description ?></p>
                                <span class="ttbm-edit-btn ttbm_edit_trip_location">Edit</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php
            } else {
                ?>
                <p>No locations found.</p>
                <?php
            }

            $html = ob_get_clean();

            wp_send_json_success([
                'html' => $html,
            ]);
        }



        function ttbm_add_new_location_term() {
//            check_ajax_referer('ttbm_add_location_nonce');

            $name = sanitize_text_field($_POST['name']);
            $slug = sanitize_title($_POST['slug']);
            $parent = absint($_POST['parent']);
            $desc = sanitize_textarea_field($_POST['desc']);
            $address = sanitize_textarea_field($_POST['address']);
            $country = sanitize_text_field($_POST['country']);
            $action_type = sanitize_text_field($_POST['action_type']);
            $imageId = absint($_POST['imageId']);

            if (empty($name)) {
                wp_send_json_error(['message' => 'Name is required']);
            }

            if( $action_type === 'Save' ){
                $args = [
                    'description' => $desc,
                    'slug'        => $slug ?: null,
                    'parent'      => $parent ?: 0
                ];
                $term = wp_insert_term( $name, 'ttbm_tour_location', $args );
            }else{
                $args = [
                    'name' => $name,
                    'description' => $desc,
                    'slug'        => $slug ?: null,
                    'parent'      => $parent ?: 0
                ];
                $term_id = absint($_POST['term_id']);
                $term = wp_update_term( $term_id, 'ttbm_tour_location', $args);
            }

            if (is_wp_error($term)) {
                wp_send_json_error(['message' => $term->get_error_message()]);
            }

            $term_id = $term['term_id'];

            update_term_meta($term_id, 'ttbm_location_address', $address);
            update_term_meta($term_id, 'ttbm_country_location', $country);
            update_term_meta($term_id, 'ttbm_location_image', $imageId);

            $img_url = wp_get_attachment_image_url( $imageId, 'thumbnail' );

            wp_send_json_success([
                'term_id' => $term_id,
                'img_url'=>$img_url,
            ]);
        }


        public function ttbm_add_new_locations_ajax_html(){

            $term_id = '';
            $add_popup = '';
            $success = false;

            if (!is_wp_error( $term_id )) {
                $button_name = 'Save';
                $add_popup = TTBM_Travel_List_Tab_Details::edit_location_popup( $term_id,$button_name );
                $success = true;
            }

            wp_send_json_success([
                'success' => $success,
                'add_popup' => $add_popup,
            ]);
        }

        public function ttbm_edit_locations_ajax_html(){

            $term_id = absint($_POST['term_id']);
            $edit_popup = '';
            $success = false;

            if (!is_wp_error( $term_id )) {
                $button_name = 'Update';
                $edit_popup = TTBM_Travel_List_Tab_Details::edit_location_popup( $term_id, $button_name );
                $success = true;
            }

            wp_send_json_success([
                'success' => $success,
                'edit_popup' => $edit_popup,
            ]);
        }


    }

    new TTBM_Travel_Tab_Data_Add_Display_Ajax();
}