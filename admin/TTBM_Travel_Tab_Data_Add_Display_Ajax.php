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
            add_action('wp_ajax_ttbm_get_category_html_data', [ $this, 'ttbm_get_category_html_data' ] );
            add_action('wp_ajax_ttbm_get_tag_html_data', [ $this, 'ttbm_get_tag_html_data' ] );
            add_action('wp_ajax_ttbm_get_activities_html_data', [ $this, 'ttbm_get_activities_html_data' ] );
            add_action('wp_ajax_ttbm_get_places_html_data', [ $this, 'ttbm_get_places_html_data' ] );

            add_action('wp_ajax_ttbm_add_new_location_term', [ $this, 'ttbm_add_new_location_term' ]);
            add_action('wp_ajax_ttbm_add_new_locations_ajax_html', [ $this, 'ttbm_add_new_locations_ajax_html' ]);
            add_action('wp_ajax_ttbm_edit_locations_ajax_html', [ $this, 'ttbm_edit_locations_ajax_html' ]);

            add_action('wp_ajax_ttbm_delete_taxonomy_data_by_id', [ $this, 'ttbm_delete_taxonomy_data_by_id' ]);
            add_action('wp_ajax_ttbm_add_edit_new_places_term', [ $this, 'ttbm_add_edit_new_places_term' ]);

            add_action('admin_action_ttbm_duplicate_post', [$this,'ttbm_duplicate_post_function']);

        }

        function ttbm_duplicate_post_function() {
            if ( !isset( $_GET['post_id']) || !isset($_GET['_wpnonce']) ||
                !wp_verify_nonce($_GET['_wpnonce'], 'ttbm_duplicate_post_' . sanitize_text_field( $_GET['post_id'] ) )
            ) {
                wp_die('Invalid request (missing or invalid nonce).');
            }

            $post_id = (int)sanitize_text_field( wp_unslash( $_GET['post_id'] ) );
            $post = get_post($post_id);

            /*if (!$post || $post->post_type !== 'ttbm_tour') {
                wp_die('Invalid post or post type.');
            }*/

            // Create new post array
            $new_post = array(
                'post_title'   => $post->post_title . ' (Copy)',
                'post_content' => $post->post_content,
                'post_status'  => 'draft',
                'post_type'    => $post->post_type,
                'post_author'  => get_current_user_id(),
            );

            // Insert new post
            $new_post_id = wp_insert_post($new_post);

            if (is_wp_error($new_post_id) || !$new_post_id) {
                wp_die('Failed to duplicate post.');
            }

            // Copy post meta
            $meta = get_post_meta($post_id);
            foreach ($meta as $key => $values) {
                foreach ($values as $value) {
                    add_post_meta($new_post_id, $key, maybe_unserialize($value));
                }
            }

            // Redirect to the edit page of the new post
            wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
            exit;
        }

        function ttbm_add_edit_new_places_term() {

            if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_admin_nonce') ) {
                wp_send_json_error(['message' => 'Invalid nonce']);
            }

            $post_name   = sanitize_text_field( wp_unslash( $_POST['post_name'] ) );
            $post_id   = sanitize_text_field(  wp_unslash( $_POST['post_id'] ) );
            $description = sanitize_textarea_field(  wp_unslash( $_POST['description'] ) );
            $thumbnail_id = absint(  wp_unslash( $_POST['thumbnail_id'] ) );

            if ( $post_id && get_post($post_id) ) {
                $update_post = array(
                    'ID'           => $post_id,
                    'post_title'   => $post_name,
                    'post_name'    => sanitize_title($post_name), // optional: update slug
                    'post_content' => $description,
                    'post_type'    => 'ttbm_places',
                );

                $result = wp_update_post( $update_post, true);
                if ($thumbnail_id) {
                    set_post_thumbnail($post_id, $thumbnail_id);
                }
                wp_send_json_success(['message' => 'Place updated successfully.', 'post_id' => $post_id]);

            }else{
                $post_id = wp_insert_post(array(
                    'post_title'   => $post_name,
                    'post_content' => $description,
                    'post_status'  => 'publish',
                    'post_type'    => 'ttbm_places',
                ));
                if (is_wp_error($post_id)) {
                    wp_send_json_error(['message' => 'Post creation failed.']);
                }
                if ($thumbnail_id) {
                    set_post_thumbnail($post_id, $thumbnail_id);
                }
                wp_send_json_success(['message' => 'Place created successfully.', 'post_id' => $post_id]);
            }
        }

        public static function ttbm_get_term_data( $term_type ){
            return get_terms([
                'taxonomy'   => $term_type,
                'hide_empty' => false,
            ]);
        }

        public function ttbm_get_locations_html() {

            if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_admin_nonce') ) {
                wp_send_json_error(['message' => 'Invalid nonce']);
            }

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
                        $term_link = get_term_link( (int) $term_id, 'ttbm_tour_location' );
                        $meta    = get_term_meta( $term_id );
                        $location_short_code = "[travel-list city='$term_slug']";
                        $img_url = isset( $meta['ttbm_location_image'][0] ) && !empty( $meta['ttbm_location_image'][0] )
                            ? wp_get_attachment_image_url( $meta['ttbm_location_image'][0], 'thumbnail' )
                            : 'https://i.imgur.com/GD3zKtz.png';

                        ?>
                        <div class="ttbm-location-card ttbm_search_location_by_title" data-taxonomy="<?php echo esc_attr( $term_name )?>" >
                            <div class="ttbm-card-left">
                                <img src="<?php echo esc_attr( $img_url ) ?>" alt="<?php echo esc_attr( $term_name ) ?>" width="70" height="70">
                            </div>
                            <div class="ttbm-card-right">
                                <h3 class="ttbm-title"><?php echo esc_attr( $term_name ) ?></h3>
                                <p class="ttbm-description"><?php echo esc_attr( $description ) ?></p>
                                <span class="ttbm_show_location_shortcode"><?php echo esc_attr( $location_short_code )?></span>
                            </div>
                            <div class=" ttbm-card-actions"  ttbm-data-location-id="<?php echo esc_attr( $term_id )?>">
                                <a href="<?php echo esc_attr( $term_link ) ;?>" target="_blank"><button class="ttbm-btn ttbm-view-btn"> <i class="fas fa-eye"></i></button></a>
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

            if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_admin_nonce') ) {
                wp_send_json_error(['message' => 'Invalid nonce']);
            }

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
        public function ttbm_get_category_html_data() {

            if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_admin_nonce') ) {
                wp_send_json_error(['message' => 'Invalid nonce']);
            }

            $terms = self::ttbm_get_term_data( 'ttbm_tour_cat' );

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

                        $search_class = 'ttbm_search_from_category';
                        self::ttbm_display_taxonomy_data( $term_id, $term_name, $description, $search_class, $get_feature_icon );
                        ?>

                    <?php endforeach; ?>
                </div>
                <?php
            } else {
                ?>
                <p>No category found.</p>
                <?php
            }
            $html = ob_get_clean();

            wp_send_json_success([
                'html' => $html,
            ]);
        }

        public function ttbm_get_feature_html_data() {

            if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_admin_nonce') ) {
                wp_send_json_error(['message' => 'Invalid nonce']);
            }

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

            if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_admin_nonce') ) {
                wp_send_json_error(['message' => 'Invalid nonce']);
            }

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

            if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_admin_nonce') ) {
                wp_send_json_error(['message' => 'Invalid nonce']);
            }
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
                    <?php if($description): ?>
                        <p class="ttbm-description"><?php echo esc_attr( $description ) ?></p>
                    <?php endif; ?>
                </div>
            </div>


        <?php }

        public function ttbm_get_places_html_data() {

            if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_admin_nonce') ) {
                wp_send_json_error(['message' => 'Invalid nonce']);
            }

            $loaded_post_ids_str = isset( $_POST['loaded_post_ids_str'] ) ? sanitize_text_field( wp_unslash( $_POST['loaded_post_ids_str'] ) ) : '';
            if( empty( $loaded_post_ids_str ) ) {
                $not_in_places = array();
            }else{
                $not_in_places = explode( ',', $loaded_post_ids_str );
            }

            $args = array(
                'post_type'      => 'ttbm_places',
                'post_status'    => 'publish',
                'orderby'        => 'date',
                'order'          => 'DESC',
                'posts_per_page' => 5,
                'post__not_in'   => $not_in_places,
            );

            $places_query = new WP_Query($args);
            $all_places_count = $places_query->found_posts;
            $total_found= $places_query->post_count;

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
                        <div class="ttbm-location-card ttbm_search_place_by_title" data-taxonomy="<?php echo esc_attr( $places_name )?>" ttbm-data-places-id="<?php echo esc_attr($post_id); ?>">
                            <div class="ttbm-card-left">
                                <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($places_name); ?>" class="ttbm-location-thumb" />
                            </div>
                            <div class="ttbm-card-right">
                                <h3 class="ttbm-title">
                                    <?php echo esc_html($places_name); ?>
                                </h3>
                                <p class="ttbm-description"><?php echo esc_html($description); ?></p>
                            </div>

                            <div class=" ttbm-card-actions"  ttbm-data-location-id="<?php echo esc_attr( $post_id )?>">
                                <a href="<?php echo esc_url($view_link); ?>" target="_blank" class="ttbm-view-link"><button class="ttbm-btn ttbm-view-btn"> <i class="fas fa-eye"></i></button></a>
                                <button class="ttbm-btn ttbm-edit-btn ttbm_edit_trip_location"><i class="fas fa-edit"></i></button>
                                <button class="ttbm-btn ttbm-delete-btn ttbm_delete_taxonomy_data"> <i class="fas fa-trash-alt"></i></button>
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
                'total_found' => $total_found,
                'all_places_count' => $all_places_count,
            ]);
        }

        function ttbm_add_new_location_term() {

            if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_admin_nonce') ) {
                wp_send_json_error(['message' => 'Invalid nonce']);
            }

            $img_url = '';
            $name = isset( $_POST['name'] ) ? sanitize_text_field($_POST['name']) : '';
            $slug = isset( $_POST['slug'] ) ? sanitize_title($_POST['slug']) : '';
            $parent = isset( $_POST['parent'] ) ? absint($_POST['parent']) : '';
            $desc = isset( $_POST['desc'] ) ? sanitize_textarea_field($_POST['desc']) : '';
            $address = isset( $_POST['address'] ) ? sanitize_textarea_field($_POST['address']) : '';
            $country = isset( $_POST['address'] ) ? sanitize_text_field($_POST['country']) : '';
            $action_type = isset( $_POST['action_type'] ) ? sanitize_text_field($_POST['action_type']) : '';
            $taxonomy_type =isset( $_POST['taxonomy_type'] ) ? sanitize_text_field($_POST['taxonomy_type']) : '';
            $icon_name = isset( $_POST['icon'] ) ? sanitize_text_field($_POST['icon']) : '';
            $imageId = isset( $_POST['imageId'] ) ? absint($_POST['imageId']) : '';

            if (empty($name)) {
                wp_send_json_error(['message' => 'Name is required']);
            }

            $term = false;

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

                $term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id']) : '';
                if( $term_id ){
                    $term = wp_update_term( $term_id, $taxonomy_type, $args);
                }
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

            if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_admin_nonce') ) {
                wp_send_json_error(['message' => 'Invalid nonce']);
            }

            $term_id = '';
            $add_popup = '';
            $success = false;

            if ( !is_wp_error( $term_id ) ) {
                $button_name = 'Save';
                $tab_type = isset( $_POST['tab_type'] ) ? sanitize_text_field( wp_unslash( $_POST['tab_type'] ) ) : '' ;

                if( $tab_type ){
                    $add_popup = TTBM_Travel_List_Tab_Details::edit_location_popup( $term_id, $button_name, $tab_type );
                }

                $success = true;
            }

            wp_send_json_success([
                'success' => $success,
                'add_popup' => $add_popup,
            ]);
        }

        public function ttbm_edit_locations_ajax_html(){

            if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_admin_nonce') ) {
                wp_send_json_error(['message' => 'Invalid nonce']);
            }

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

            if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_admin_nonce') ) {
                wp_send_json_error(['message' => 'Invalid nonce']);
            }

            $term_id = absint( sanitize_text_field( $_POST['term_id'] ) );
            $success = $result = false;
            $message = 'Something went wrong.';

            if (!is_wp_error( $term_id )) {
                $button_name = 'Update';
                $tab_type = sanitize_text_field( $_POST['tab_type'] );
                $taxonomy_type= TTBM_Travel_List_Tab_Details::get_taxonomy_type( $tab_type );

                if( $taxonomy_type === 'ttbm_places' ){
                    if ( ! $term_id || ! current_user_can('delete_post', $term_id) ) {
                        wp_send_json_error(['message' => 'Invalid or unauthorized request.']);
                    }
                    $result = wp_delete_post($term_id, true); // true = force delete (bypasses trash)
                    if ( $result ) {
                        $message = 'Post deleted successfully.';
                    } else {
                        $message = 'Failed to delete post.';
                    }
                }else {
                    $result = wp_delete_term($term_id, $taxonomy_type);
                    if ( $result ) {
                        $message = 'Taxonomy data has been deleted successfully.';
                    } else {
                        $message = 'Failed to delete Taxonomy.';
                    }
                }
                $success = true;
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