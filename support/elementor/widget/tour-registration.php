<?php
namespace TTBMPlugin\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class TTBMTourRegistrationWidget extends Widget_Base {

    public function get_name() {
        return 'ttbm-tour-registration-widget';
    }

    public function get_title() {
        return esc_html__('Tour Registration', 'tour-booking-manager');
    }

    public function get_icon() {
        return 'fa fa-user-plus';
    }

    public function get_categories() {
        return ['ttbm-elementor-support'];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Tour Registration', 'tour-booking-manager'),
            ]
        );

        $this->add_control(
            'ttbm_id',
            [
                'label' => esc_html__('Tour ID', 'tour-booking-manager'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
                'description' => esc_html__('Enter the Tour ID. Leave empty to use current tour.', 'tour-booking-manager'),
            ]
        );

        $this->add_control(
            'style',
            [
                'label' => esc_html__('Form Style', 'tour-booking-manager'),
                'type' => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default' => esc_html__('Default', 'tour-booking-manager'),
                    'compact' => esc_html__('Compact', 'tour-booking-manager'),
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $ttbm_id = array_key_exists('ttbm_id', $settings) ? $settings['ttbm_id'] : '';
        $style = array_key_exists('style', $settings) ? $settings['style'] : 'default';

        ?>
        <div class="ttbm-elementor-registration-widget">
            <?php echo do_shortcode("[ttbm-registration ttbm_id='$ttbm_id' style='$style']"); ?>
        </div>
        <?php
    }

    protected function _content_template() {
        ?>
        <div class="elementor-ttbm-registration">
            <?php esc_html_e('Tour Registration Form will be displayed here', 'tour-booking-manager'); ?>
        </div>
        <?php
    }
} 