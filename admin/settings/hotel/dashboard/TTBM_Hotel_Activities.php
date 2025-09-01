<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('TTBM_Hotel_Activities')) {
    class TTBM_Hotel_Activities{
        public function __construct() {

            // save feature data
            add_action('ttbm_hotel_dashboard_tabs', [$this, 'dashbaord_features']);
            add_action('ttbm_hotel_dashboard_content', [$this, 'dashbaord_features_content']);
			add_action('wp_ajax_ttbm_hotel_feature_save', [$this, 'hotel_feature_save']);
            // ttbm_update_faq_data
			add_action('wp_ajax_ttbm_hotel_feature_update', [$this, 'feature_update_item']);
            // ttbm_delete_faq_data
			add_action('wp_ajax_ttbm_hotel_feature_delete', [$this, 'feature_delete_item']);
        }
        public function dashbaord_features(){
            ?>
            <div class="ttbm_hotel_tab_item" data-tab="ttbm_hotel_activities_tab"><?php echo esc_attr__( 'Hotel Activities', 'tour-booking-manager' )?></div>
            <?php
        }

        public function dashbaord_features_content(){
            ?>
            <!--Features List Display-->
            <div id="ttbm_hotel_activities_tab" class="ttbm_hotel_tab_content">
                <div>
                    <div class="ttbm_style features_header">
                        <h2 class="ttbm_total_booking_title">
                            <?php echo esc_attr__( 'Hotel Activities List', 'tour-booking-manager' )?>
                        </h2>
                        <button class="_themeButton_xs ttbm-hotel-new-feature" data-ttbm-modal="ttbm-hotel-feature-modal"> <?php echo esc_attr__( 'Add new feature', 'tour-booking-manager' )?></button>
                    </div>
                    <div class="ttbm-hotel-activities-items">
                        <?php $this->show_activities_lists(); ?>
                    </div>
                </div>
            </div>
            <?php $this->display_sidebar_modal(); ?>
            <?php
        }

        public function show_activities_lists() {	
            $features = get_terms(array(
                'taxonomy'   => 'ttbm_hotel_features_list',
                'hide_empty' => false,
            ));
            if (!empty($features)) :?>
            <div class="ttbm-features">
                <?php foreach ($features as $feature): ?>
                    <?php $icon = get_term_meta($feature->term_id, 'ttbm_hotel_feature_icon', true); ?>
                    <div class="ttbm-features-item" data-id="<?php echo esc_attr($feature->term_id); ?>" data-name="<?php echo esc_attr($feature->name); ?>" data-slug="<?php echo esc_attr($feature->slug); ?>" data-icon="<?php echo esc_attr($icon); ?>" data-description="<?php echo esc_attr($feature->description); ?>">
                        <div class="features-item-info">
                            <i class="<?php echo esc_attr($icon); ?>"></i>
                            <span><?php echo esc_html($feature->name); ?></span>
                        </div>
                        <div class="ttbm-hotel-feature-action">
                            <button class="ttbm-hotel-edit-feature" data-ttbm-modal="ttbm-hotel-feature-modal"><i class="mi mi-pencil"></i></button>
                            <button class="ttbm-hotel-delete-feature"><i class="mi mi-trash"></i></button>
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
            <div class="ttbm-modal-container" data-ttbm-modal-target="ttbm-hotel-feature-modal">
                <div class="ttbm-modal-content">
                    <span class="ttbm-modal-close"><i class="fas fa-times"></i></span>
                    <div class="title">
                        <h3><?php esc_html_e('Add New Features', 'tour-booking-manager'); ?></h3>
                        <div id="ttbm-hotel-feature-msg"></div>
                    </div>
                    <div class="content">
                        <form action="#" method="post" id="ttbm-hotel-feature-form" autocomplete="off">
                            <input type="hidden" name="ttbm_hotel_feature_id">
                            <label>
                                <?php esc_html_e('Add Features Title', 'tour-booking-manager'); ?>
                                <input type="text" name="ttbm_hotel_feature_title">
                            </label>
                            <label>
                                <?php esc_html_e('Add Features Slug', 'tour-booking-manager'); ?>
                                <input type="text" name="ttbm_hotel_feature_slug">
                            </label>
                            <div class="feature-icon">
                                <label>
                                    <?php esc_html_e('Add Features Icon', 'tour-booking-manager'); ?>
                                </label>
                                <?php do_action('ttbm_input_add_icon', 'ttbm_hotel_feature_icon', '');  ?>
                            </div>
                            <label>
                                <?php esc_html_e('Add Features Description', 'tour-booking-manager'); ?>
                            </label>
                            <textarea name="ttbm_hotel_feature_description" rows="10" cols="10"></textarea>
                            <div class="mT"></div>
                            <div class="ttbm_hotel_feature_save">
                                <p>
                                    <button id="ttbm_hotel_feature_save" class="button button-primary button-large"><?php esc_html_e('Save', 'tour-booking-manager'); ?></button>
                                    <button id="ttbm_hotel_feature_save_close" class="button button-primary button-large">save close</button>
                                <p>
                            </div>
                            <div class="ttbm_hotel_feature_update" style="display: none;">
                                <p>
                                    <button id="ttbm_hotel_feature_update_btn" class="button button-primary button-large"><?php esc_html_e('Update and Close', 'tour-booking-manager'); ?></button>
                                <p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    new TTBM_Hotel_Activities();
}