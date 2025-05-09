<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('TTBM_Travel_Tab_Data_Add_Display_Ajax')) {
    class TTBM_Travel_Tab_Data_Add_Display_Ajax{
        public function __construct() {
            add_action('wp_ajax_ttbm_get_locations_html', [ $this, 'ttbm_get_locations_html' ] );
            add_action('wp_ajax_ttbm_get_organiser_html_data', [ $this, 'ttbm_get_organiser_html_data' ] );
            add_action('wp_ajax_ttbm_get_feature_html_data', [ $this, 'ttbm_get_feature_html_data' ] );
            add_action('wp_ajax_ttbm_get_tag_html_data', [ $this, 'ttbm_get_tag_html_data' ] );
            add_action('wp_ajax_ttbm_get_activities_html_data', [ $this, 'ttbm_get_activities_html_data' ] );
            add_action('wp_ajax_ttbm_get_places_html_data', [ $this, 'ttbm_get_places_html_data' ] );

            add_action('wp_ajax_ttbm_add_new_location_term', [ $this, 'ttbm_add_new_location_term' ]);
            add_action('wp_ajax_ttbm_add_new_locations_ajax_html', [ $this, 'ttbm_add_new_locations_ajax_html' ]);
            add_action('wp_ajax_ttbm_edit_locations_ajax_html', [ $this, 'ttbm_edit_locations_ajax_html' ]);

            add_action('wp_ajax_ttbm_delete_taxonomy_data_by_id', [ $this, 'ttbm_delete_taxonomy_data_by_id' ]);

        }

        public static function ttbm_get_term_data( $term_type ){
            return $terms = get_terms([
                'taxonomy'   => $term_type,
                'hide_empty' => false,
            ]);
        }

        public function ttbm_get_locations_html() {
            $terms = self::ttbm_get_term_data( 'ttbm_tour_location' );
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
                        <div class="ttbm-location-card ttbm_search_location_by_title" data-taxonomy="<?php echo esc_attr( $term_name )?>" >
                            <div class="ttbm-card-left">
                                <img src="<?= $img_url ?>" alt="<?= $term_name ?>" width="70" height="70">
                            </div>
                            <div class="ttbm-card-right">
                                <h3 class="ttbm-title"><?= $term_name ?></h3>
                                <p class="ttbm-description"><?= $description ?></p>
                            </div>
                            <div class=" ttbm-card-actions"  ttbm-data-location-id="<?php echo esc_attr( $term_id )?>">
                                <button class="ttbm-btn ttbm-view-btn"> <i class="fas fa-eye"></i></button>
                                <button class="ttbm-btn ttbm-edit-btn ttbm_edit_trip_location"><i class="fas fa-edit"></i></button>
                                <button class="ttbm-btn ttbm-delete-btn ttbm_delete_taxonomy_data"> <i class="fas fa-trash-alt"></i></button>
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

        public function ttbm_get_organiser_html_data() {
            $terms = self::ttbm_get_term_data( 'ttbm_tour_org' );

            ob_start();

            if (!empty($terms) && !is_wp_error($terms)) {
                ?>
                <div class="ttbm-taxonomy-list-holder">
                    <?php foreach ($terms as $term):
                        $term_id    = $term->term_id;
                        $term_name  = esc_html( $term->name );
                        $term_slug  = esc_html( $term->slug );
                        $description = esc_html( $term->description );

                        $search_class = 'ttbm_search_from_organiser';
                        self::ttbm_display_taxonomy_data( $term_id, $term_name, $description, $search_class );
                        ?>

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
        public function ttbm_get_feature_html_data() {
            $terms = self::ttbm_get_term_data( 'ttbm_tour_features_list' );

            ob_start();

            if (!empty($terms) && !is_wp_error($terms)) {
                ?>
                <div class="ttbm-taxonomy-list-holder">
                    <?php foreach ($terms as $term):
                        $term_id    = $term->term_id;
                        $term_name  = esc_html( $term->name );
                        $term_slug  = esc_html( $term->slug );
                        $description = esc_html( $term->description );

                        $get_feature_icon = get_term_meta( $term_id, 'ttbm_feature_icon', true );

                        $search_class = 'ttbm_search_from_feature';
                        self::ttbm_display_taxonomy_data( $term_id, $term_name, $description, $search_class, $get_feature_icon );
                        ?>

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
        public function ttbm_get_tag_html_data() {
            $terms = self::ttbm_get_term_data( 'ttbm_tour_tag' );

            ob_start();

            if (!empty($terms) && !is_wp_error($terms)) {
                ?>
                <div class="ttbm-taxonomy-list-holder">
                    <?php foreach ($terms as $term):
                        $term_id    = $term->term_id;
                        $term_name  = esc_html( $term->name );
                        $term_slug  = esc_html( $term->slug );
                        $description = esc_html( $term->description );

                        $search_class = 'ttbm_search_from_tag';
                        self::ttbm_display_taxonomy_data( $term_id, $term_name, $description, $search_class );
                        ?>
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
        public function ttbm_get_activities_html_data() {
            $terms = self::ttbm_get_term_data( 'ttbm_tour_activities' );

            ob_start();

            if (!empty($terms) && !is_wp_error($terms)) {
                ?>
                <div class="ttbm-taxonomy-list-holder">
                    <?php foreach ($terms as $term):
                        $term_id    = $term->term_id;
                        $term_name  = esc_html( $term->name );
                        $term_slug  = esc_html( $term->slug );
                        $description = esc_html( $term->description );
                        $get_activities_icon = get_term_meta( $term_id, 'ttbm_activities_icon', true );
                        $search_class = 'ttbm_search_from_activity';

                        self::ttbm_display_taxonomy_data( $term_id, $term_name, $description, $search_class, $get_activities_icon );
                        ?>

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

        public static function ttbm_display_taxonomy_data( $term_id, $term_name, $description, $search_class, $icon='' ){
            ?>

            <div class="ttbm-taxonomy-card <?php echo esc_attr( $search_class )?>" data-taxonomy="<?php echo esc_attr( $term_name )?>">
                <div class="ttbm-card-right">
                    <div class="ttbm-title-row">
                        <h3 class="ttbm-title"><i class="<?php echo esc_attr( $icon )?> "></i> <?php echo esc_attr( $term_name ) ?></h3>
                        <div class="ttbm-taxonomy-card-actions" ttbm-data-location-id="<?php echo esc_attr( $term_id )?>">
<!--                            <button class="ttbm-btn ttbm-view-btn"><i class="fas fa-eye"></i></button>-->
                            <button class="ttbm-btn ttbm-edit-btn ttbm_edit_trip_location"><i class="fas fa-edit"></i></button>
                            <button class="ttbm-btn ttbm-delete-btn ttbm_delete_taxonomy_data"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    </div>
                    <p class="ttbm-description"><?php echo esc_attr( $description ) ?></p>
                </div>
            </div>


        <?php }

        public function ttbm_get_places_html_data() {
            $args = array(
                'post_type'      => 'ttbm_places',
                'post_status'    => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC',
                'posts_per_page' => -1,
            );

            $places_query = new WP_Query($args);
            ob_start();

            if ($places_query->have_posts()) {
                ?>
                <div class="ttbm-locations-list">
                    <?php while ($places_query->have_posts()) : $places_query->the_post(); ?>
                        <?php
                        $post_id = get_the_ID();
                        $places_name = get_the_title();
                        $description = get_the_excerpt();
                        $view_link = get_permalink($post_id);
                        $edit_link = get_edit_post_link($post_id);
                        $delete_link  = get_delete_post_link($post_id, '', true);

                        $img_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
                        if (!$img_url) {
                            $img_url = 'https://i.imgur.com/GD3zKtz.png';
                        }
                        ?>
                        <div class="ttbm-location-card ttbm_search_place_by_title" data-taxonomy="<?php echo esc_attr( $places_name )?>" ttbm-data-location-id="<?php echo esc_attr($post_id); ?>">
                            <div class="ttbm-card-left">
                                <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($places_name); ?>" class="ttbm-location-thumb" />
                            </div>
                            <div class="ttbm-card-right">
                                <h3 class="ttbm-title">
                                    <?php echo esc_html($places_name); ?>
                                </h3>
                                <p class="ttbm-description"><?php echo esc_html($description); ?></p>
                            </div>
                            <div class=" ttbm-card-actions"  ttbm-data-place-id="<?php echo esc_attr( $post_id )?>">
                                <a href="<?php echo esc_url($view_link); ?>" target="_blank" class="ttbm-view-link"><button class="ttbm-btn ttbm-view-btn"> <i class="fas fa-eye"></i></button></a>
                                <a href="<?php echo esc_url($edit_link); ?>" target="_blank" class="ttbm-edit-link"><button class="ttbm-btn ttbm-edit-btn"><i class="fas fa-edit"></i></button></a>
<!--                                <a href="--><?php //echo esc_url($delete_link); ?><!--" target="_blank" class="ttbm-delete-link"><button class="ttbm-btn ttbm-delete-btn"> <i class="fas fa-trash-alt"></i></button></a>-->
                                <a href="<?php echo esc_url($delete_link); ?>"
                                   class="ttbm-btn ttbm-delete-btn"
                                   onclick="return confirm('Are you sure you want to delete this place?');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <?php
            } else {
                echo '<p>No locations found.</p>';
            }

            wp_reset_postdata();

            $html = ob_get_clean();

            wp_send_json_success([
                'html' => $html,
            ]);
        }


        function ttbm_add_new_location_term() {

            $img_url = '';
            $name = sanitize_text_field($_POST['name']);
            $slug = sanitize_title($_POST['slug']);
            $parent = absint($_POST['parent']);
            $desc = sanitize_textarea_field($_POST['desc']);
            $address = sanitize_textarea_field($_POST['address']);
            $country = sanitize_text_field($_POST['country']);
            $action_type = sanitize_text_field($_POST['action_type']);
            $taxonomy_type = sanitize_text_field($_POST['taxonomy_type']);
            $icon_name = sanitize_text_field($_POST['icon']);
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
                $term = wp_insert_term( $name, $taxonomy_type, $args );
            }else{
                $args = [
                    'name' => $name,
                    'description' => $desc,
                    'slug'        => $slug ?: null,
//                    'parent'      => $parent ?: 0
                ];

                if( $taxonomy_type !== 'ttbm_tour_tag' ){
                    $args['parent'] = $parent ?: 0;
                }

                $term_id = absint($_POST['term_id']);
                $term = wp_update_term( $term_id, $taxonomy_type, $args);
            }

            if (is_wp_error($term)) {
                wp_send_json_error(['message' => $term->get_error_message()]);
            }

            $term_id = $term['term_id'];

            if( $taxonomy_type === 'ttbm_tour_location' ) {
                update_term_meta($term_id, 'ttbm_location_address', $address );
                update_term_meta($term_id, 'ttbm_country_location', $country );
                update_term_meta($term_id, 'ttbm_location_image', $imageId );
                $img_url = wp_get_attachment_image_url( $imageId, 'thumbnail' );
            }


            if( $taxonomy_type === 'ttbm_tour_activities' ){
                update_term_meta( $term_id, 'ttbm_activities_icon', $icon_name );
            }
            if( $taxonomy_type === 'ttbm_tour_features_list' ){
                update_term_meta( $term_id, 'ttbm_feature_icon', $icon_name );
            }




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
                $tab_type = $_POST['tab_type'];
                $add_popup = TTBM_Travel_List_Tab_Details::edit_location_popup( $term_id, $button_name, $tab_type );
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
                $tab_type = $_POST['tab_type'];
                $edit_popup = TTBM_Travel_List_Tab_Details::edit_location_popup( $term_id, $button_name, $tab_type );
                $success = true;
            }

            wp_send_json_success([
                'success' => $success,
                'edit_popup' => $edit_popup,
            ]);
        }

        public function ttbm_delete_taxonomy_data_by_id(){

            $term_id = absint($_POST['term_id']);
            $success = $result = false;
            $message = 'Something went wrong.';

            if (!is_wp_error( $term_id )) {
                $button_name = 'Update';
                $tab_type = $_POST['tab_type'];
                $taxonomy_type= TTBM_Travel_List_Tab_Details::get_taxonomy_type( $tab_type );

                $result = wp_delete_term( $term_id, $taxonomy_type );
                $success = true;
                $message = 'Taxonomy data has been deleted.';
            }

            wp_send_json_success([
                'success' => $success,
                'deleted_id' => $term_id,
                'message' => $message,
            ]);
        }


    }

    new TTBM_Travel_Tab_Data_Add_Display_Ajax();
}