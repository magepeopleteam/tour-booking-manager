<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('TTBM_Hotel_Activities')) {
    class TTBM_Hotel_Activities{
        public function __construct() {

            // save feature data
            add_action('ttbm_hotel_dashboard_tabs', [$this, 'dashbaord_activity']);
            add_action('ttbm_hotel_dashboard_content', [$this, 'dashbaord_activity_content']);
			add_action('wp_ajax_ttbm_hotel_activity_save', [$this, 'hotel_activity_save']);
            // ttbm_update_faq_data
			add_action('wp_ajax_ttbm_hotel_activity_update', [$this, 'activity_update_item']);
            // ttbm_delete_faq_data
			add_action('wp_ajax_ttbm_hotel_activity_delete', [$this, 'activity_delete_item']);
        }
        public function dashbaord_activity(){
            ?>
            <div class="ttbm_hotel_tab_item" data-tab="ttbm_hotel_activities_tab"><i class="mi mi-practice"></i> <?php echo esc_attr__( 'Hotel Activities', 'tour-booking-manager' )?></div>
            <?php
        }

        public function dashbaord_activity_content(){
            ?>
            <!--Features List Display-->
            <div id="ttbm_hotel_activities_tab" class="ttbm_hotel_tab_content">
                <div>
                    <div class="ttbm_style features_header">
                        <h2 class="ttbm_total_booking_title">
                            <?php echo esc_attr__( 'Hotel Activities List', 'tour-booking-manager' )?>
                        </h2>
                        <button class="_themeButton_xs ttbm-hotel-new-activity" data-ttbm-modal="ttbm-hotel-activity-modal"> <?php echo esc_attr__( 'Add new feature', 'tour-booking-manager' )?></button>
                    </div>
                    <div class="ttbm-hotel-activity-items">
                        <?php $this->show_activities_lists(); ?>
                    </div>
                </div>
            </div>
            <?php $this->display_sidebar_modal(); ?>
            <?php
        }

        public function show_activities_lists() {	
            $activities = get_terms(array(
                'taxonomy'   => 'ttbm_hotel_activities_list',
                'hide_empty' => false,
            ));
            if (!empty($activities)) :?>
            <div class="ttbm-features">
                <?php foreach ($activities as $feature): ?>
                    <?php $icon = get_term_meta($feature->term_id, 'ttbm_hotel_activity_icon', true); ?>
                    <div class="ttbm-features-item" data-id="<?php echo esc_attr($feature->term_id); ?>" data-name="<?php echo esc_attr($feature->name); ?>" data-slug="<?php echo esc_attr($feature->slug); ?>" data-icon="<?php echo esc_attr($icon); ?>" data-description="<?php echo esc_attr($feature->description); ?>">
                        <div class="features-item-info">
                            <i class="<?php echo esc_attr($icon); ?>"></i>
                            <span><?php echo esc_html($feature->name); ?></span>
                        </div>
                        <div class="ttbm-hotel-feature-action">
                            <button class="ttbm-hotel-edit-activity" data-ttbm-modal="ttbm-hotel-activity-modal"><i class="mi mi-pencil"></i></button>
                            <button class="ttbm-hotel-delete-activity"><i class="mi mi-trash"></i></button>
                        </div>
                    </div>
                <?php endforeach;?>
            </div>
            <?php else: ?>
                <p><?php esc_html__('No features found.', 'tour-booking-manager'); ?></p>
            <?php endif;
        }

        public static function display_sidebar_modal( ) {
            ?>
            <div class="ttbm-modal-container" data-ttbm-modal-target="ttbm-hotel-activity-modal">
                <div class="ttbm-modal-content">
                    <span class="ttbm-modal-close"><i class="fas fa-times"></i></span>
                    <div class="title">
                        <h3><?php esc_html_e('Add New Activity', 'tour-booking-manager'); ?></h3>
                        <div id="ttbm-hotel-activity-msg"></div>
                    </div>
                    <div class="content">
                        <form action="#" method="post" id="ttbm-hotel-activity-form" autocomplete="off">
                            <input type="hidden" name="ttbm_hotel_activity_id">
                            <label>
                                <?php esc_html_e('Add Activity Title', 'tour-booking-manager'); ?>
                                <input type="text" name="ttbm_hotel_activity_title">
                            </label>
                            <label>
                                <?php esc_html_e('Add Activity Slug', 'tour-booking-manager'); ?>
                                <input type="text" name="ttbm_hotel_activity_slug">
                            </label>
                            <div class="feature-icon">
                                <label>
                                    <?php esc_html_e('Add Activity Icon', 'tour-booking-manager'); ?>
                                </label>
                                <?php do_action('ttbm_input_add_icon', 'ttbm_hotel_activity_icon', '');  ?>
                            </div>
                            <label>
                                <?php esc_html_e('Add Activity Description', 'tour-booking-manager'); ?>
                            </label>
                            <textarea name="ttbm_hotel_activity_description" rows="10" cols="10"></textarea>
                            <div class="mT"></div>
                            <div class="ttbm_hotel_activity_save">
                                <p>
                                    <button id="ttbm_hotel_activity_save" class="button button-primary button-large"><?php esc_html_e('Save', 'tour-booking-manager'); ?></button>
                                    <button id="ttbm_hotel_activity_save_close" class="button button-primary button-large">save close</button>
                                <p>
                            </div>
                            <div class="ttbm_hotel_activity_update" style="display: none;">
                                <p>
                                    <button id="ttbm_hotel_activity_update_btn" class="button button-primary button-large"><?php esc_html_e('Update and Close', 'tour-booking-manager'); ?></button>
                                <p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php
        }

        public function hotel_activity_save() {	
            if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
                wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
            }
            $feature_title = isset($_POST['ttbm_hotel_activity_title']) ? sanitize_text_field(wp_unslash($_POST['ttbm_hotel_activity_title'])) : '';
            $feature_slug = isset($_POST['ttbm_hotel_activity_slug']) ? sanitize_title(wp_unslash($_POST['ttbm_hotel_activity_slug'])) : '';
            $feature_icon = isset($_POST['ttbm_hotel_activity_icon']) ? sanitize_text_field(wp_unslash($_POST['ttbm_hotel_activity_icon'])) : '';
            $feature_description = isset($_POST['ttbm_hotel_activity_description']) ? sanitize_textarea_field(wp_unslash($_POST['ttbm_hotel_activity_description'])) : '';

            if (empty($feature_title)) {
                ob_start();
                $resultMessage = esc_html__('Activity title is required.', 'tour-booking-manager');
                $this->show_activities_lists();
                $html_output = ob_get_clean();
                wp_send_json_success([
                    'message' => $resultMessage,
                    'html' => $html_output,
                ]);
            }

            $args = array(
                'description' => $feature_description,
                'slug'        => $feature_slug,
            );

            $result = wp_insert_term($feature_title, 'ttbm_hotel_activities_list', $args);

            if (!is_wp_error($result) && isset($result['term_id'])) {
                if (!empty($feature_icon)) {
                    update_term_meta($result['term_id'], 'ttbm_hotel_activity_icon', $feature_icon);
                }
                $result = true;
            } else {
                $result = false;
            }
            if ($result) {
                ob_start();
                $resultMessage = esc_html__('Data Added Successfully', 'tour-booking-manager');
                $this->show_activities_lists();
                $html_output = ob_get_clean();
                wp_send_json_success([
                    'message' => $resultMessage,
                    'html' => $html_output,
                ]);
            } else {
                wp_send_json_success([
                    'message' => 'Data not inserted',
                    'html' => 'error',
                ]);
            }
            die;
        }

        public function activity_update_item() {
            if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
                wp_send_json_error('Invalid nonce!');
            }

            $feature_id = isset($_POST['ttbm_hotel_activity_id']) ? intval($_POST['ttbm_hotel_activity_id']) : 0;
            $feature_title = isset($_POST['ttbm_hotel_activity_title']) ? sanitize_text_field(wp_unslash($_POST['ttbm_hotel_activity_title'])) : '';
            $feature_slug = isset($_POST['ttbm_hotel_activity_slug']) ? sanitize_title(wp_unslash($_POST['ttbm_hotel_activity_slug'])) : '';
            $feature_icon = isset($_POST['ttbm_hotel_activity_icon']) ? sanitize_text_field(wp_unslash($_POST['ttbm_hotel_activity_icon'])) : '';
            $feature_description = isset($_POST['ttbm_hotel_activity_description']) ? sanitize_textarea_field(wp_unslash($_POST['ttbm_hotel_activity_description'])) : '';
           
            if ($feature_id > 0 && !empty($feature_title) && taxonomy_exists('ttbm_hotel_activities_list')) {
                $args = array(
                    'name' => $feature_title,
                    'slug'        => $feature_slug,
                    'description' => $feature_description,
                );
                // Update the term name separately
                wp_update_term($feature_id, 'ttbm_hotel_activities_list', $args);

                if (!empty($feature_icon)) {
                    update_term_meta($feature_id, 'ttbm_hotel_activity_icon', $feature_icon);
                }
            }
            ob_start();
            $resultMessage = esc_html__('Data Updated Successfully', 'tour-booking-manager');
            $this->show_activities_lists();
            $html_output = ob_get_clean();
            wp_send_json_success([
                'message' => $resultMessage,
                'html' => $html_output,
            ]);
            die;
        }


        public function activity_delete_item() {
            // Check nonce for security
            if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ttbm_admin_nonce')) {
                wp_send_json_error('Invalid nonce!');
            }

            $item_id = isset($_POST['itemId']) ? intval($_POST['itemId']) : 0;

            if ($item_id > 0 && taxonomy_exists('ttbm_hotel_activities_list')) {
                $deleted = wp_delete_term($item_id, 'ttbm_hotel_activities_list');
            } else {
                $deleted = false;
            }
            if ($deleted) {
                ob_start();
                $resultMessage = esc_html__('Data Deleted Successfully', 'tour-booking-manager');
                $this->show_activities_lists();
                $html_output = ob_get_clean();
                wp_send_json_success([
                    'message' => $resultMessage,
                    'html' => $html_output,
                ]);
            } else {
                wp_send_json_success([
                    'message' => 'Data not inserted',
                    'html' => '',
                ]);
            }
            die;
        }
    }

    new TTBM_Hotel_Activities();
}