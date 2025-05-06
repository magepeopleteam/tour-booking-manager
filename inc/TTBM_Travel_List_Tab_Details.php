<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('TTBM_Travel_List_Tab_Details')) {
    class TTBM_Travel_List_Tab_Details{
        public function __construct() {

            add_action('ttbm_travel_list_tour_package_header', array($this, 'travel_list_tour_package_header'), 10, 1);
            add_action('ttbm_travel_lists_tab_display', array($this, 'travel_lists_tab_display'), 10, 2);
            add_action('ttbm_add_new_location_popup', array($this, 'add_new_location_popup'), 10, 1);
            add_action('ttbm_travel_list_category', array($this, 'travel_list_category_add_display'), 10, 1);

        }

        public function add_new_location_popup( $location ){ ?>
            <div id="ttbm-location-popup" class="ttbm-popup-overlay" style="display:none;">
                <div class="ttbm-popup-box">
                    <h3>Add New Tour Location</h3>

                    <label>Name:</label>
                    <input type="text" id="ttbm-location-name" placeholder="Location Name">

                    <label>Slug:</label>
                    <input type="text" id="ttbm-location-slug" placeholder="Optional Slug">

                    <label>Parent Location:</label>
                    <select id="ttbm-location-parent">
                        <option value="">— None —</option>
                        <?php
                        $terms = get_terms(['taxonomy' => 'ttbm_tour_location', 'hide_empty' => false]);
                        foreach ($terms as $term) {
                            echo "<option value='{$term->term_id}'>" . esc_html($term->name) . "</option>";
                        }
                        ?>
                    </select>

                    <label>Description:</label>
                    <textarea id="ttbm-location-desc" placeholder="Short description"></textarea>

                    <label>Full Address:</label>
                    <textarea id="ttbm-location-address" placeholder="Full address"></textarea>

                    <label>Country:</label>
                    <select id="ttbm-location-country">
                        <option value="">Select Country</option>
                        <option value="Bangladesh">Bangladesh</option>
                        <option value="India">India</option>
                        <option value="USA">USA</option>
                        <option value="UK">UK</option>
                        <!-- Add more countries as needed -->
                    </select>

                    <label>Image:</label>
                    <div>
                        <button id="ttbm-upload-image" type="button">Upload Image</button>
                        <input type="hidden" id="ttbm-location-image-id">
                        <div id="ttbm-image-preview"></div>
                    </div>

                    <div class="ttbm-popup-buttons">
                        <button id="ttbm-save-location">Save</button>
                        <button id="ttbm-close-popup">Cancel</button>
                    </div>
                </div>
            </div>
            <p>Content for Trip Locationg</p>
            <div id="ttbm-add-new-location-btn">Add New Locations</div>

            <div class="ttbm_travel_list_location_shows" id="ttbm_travel_list_location_shows"></div>
        <?php }


        public function travel_lists_tab_display( $label, $b ){
            $category = '';
            ?>
            <div class="ttbm_trvel_lists_tab_holder">
                <div class="ttbm_trvel_lists_tabs">
                    <button class="active" data-target="ttbm_trvel_lists_tour"><?php echo __(' Tour Package','tour-booking-manager'); ?></button>
                    <button data-target="ttbm_trvel_lists_category"><?php echo __(' Trip Category','tour-booking-manager'); ?></button>
                    <button data-target="ttbm_trvel_lists_organiser"><?php echo __(' Trip Organiser','tour-booking-manager'); ?></button>
                    <button data-target="ttbm_trvel_lists_location"><?php echo __(' Trip Location','tour-booking-manager'); ?></button>
                    <button data-target="ttbm_trvel_lists_features"><?php echo __(' Features','tour-booking-manager'); ?></button>
                    <button data-target="ttbm_trvel_lists_tag"><?php echo __(' Tags','tour-booking-manager'); ?></button>
                    <button data-target="ttbm_trvel_lists_activities"><?php echo __(' Activities','tour-booking-manager'); ?></button>
                </div>

                <div id="ttbm_trvel_lists_tour" class="ttbm_trvel_lists_content active">
                    <?php do_action( 'ttbm_travel_list_tour_package_header', $label);?>
                </div>
                <div id="ttbm_trvel_lists_category" class="ttbm_trvel_lists_content">
                    <?php do_action( 'ttbm_travel_list_category', $category);?>
                </div>

                <div id="ttbm_trvel_lists_organiser" class="ttbm_trvel_lists_content">
                    <p>Content for Trip Organiser</p>
                </div>
                <div id="ttbm_trvel_lists_location" class="ttbm_trvel_lists_content">
                    <?php do_action( 'ttbm_add_new_location_popup', 'ttbm_tour_location' );?>
                </div>

                <div id="ttbm_trvel_lists_features" class="ttbm_trvel_lists_content">
                    <p>Content for Features</p>
                </div>

                <div id="ttbm_trvel_lists_tag" class="ttbm_trvel_lists_content">
                    <p>Content for Tag nre</p>
                </div>

                <div id="ttbm_trvel_lists_activities" class="ttbm_trvel_lists_content">
                    <p>Content for Activities</p>
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

        public function travel_list_category_add_display(){

        }


    }

    new TTBM_Travel_List_Tab_Details();
}