<?php
namespace TTBMPlugin\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class TTBMTourRelatedWidget extends Widget_Base {

    public function get_name() {
        return 'ttbm-tour-related-widget';
    }

    public function get_title() {
        return esc_html__('Related Tours', 'tour-booking-manager');
    }

    public function get_icon() {
        return 'fa fa-link';
    }

    public function get_categories() {
        return ['ttbm-elementor-support'];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Related Tours', 'tour-booking-manager'),
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
            'show',
            [
                'label' => esc_html__('Number of Tours to Show', 'tour-booking-manager'),
                'type' => Controls_Manager::NUMBER,
                'default' => 4,
                'min' => 1,
                'max' => 100,
            ]
        );

        $this->add_control(
            'style',
            [
                'label' => esc_html__('List Style', 'tour-booking-manager'),
                'type' => Controls_Manager::SELECT,
                'default' => 'grid',
                'options' => [
                    'grid' => esc_html__('Grid', 'tour-booking-manager'),
                    'list' => esc_html__('List', 'tour-booking-manager'),
                ],
            ]
        );

        $this->add_control(
            'column',
            [
                'label' => esc_html__('Columns', 'tour-booking-manager'),
                'type' => Controls_Manager::SELECT,
                'default' => '4',
                'options' => [
                    '1' => esc_html__('1', 'tour-booking-manager'),
                    '2' => esc_html__('2', 'tour-booking-manager'),
                    '3' => esc_html__('3', 'tour-booking-manager'),
                    '4' => esc_html__('4', 'tour-booking-manager'),
                ],
            ]
        );

        $this->add_control(
            'sort',
            [
                'label' => esc_html__('Sort', 'tour-booking-manager'),
                'type' => Controls_Manager::SELECT,
                'default' => 'ASC',
                'options' => [
                    'ASC' => esc_html__('Ascending', 'tour-booking-manager'),
                    'DESC' => esc_html__('Descending', 'tour-booking-manager'),
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $ttbm_id = array_key_exists('ttbm_id', $settings) ? $settings['ttbm_id'] : '';
        $show = array_key_exists('show', $settings) ? $settings['show'] : '4';
        $style = array_key_exists('style', $settings) ? $settings['style'] : 'grid';
        $column = array_key_exists('column', $settings) ? $settings['column'] : '4';
        $sort = array_key_exists('sort', $settings) ? $settings['sort'] : 'ASC';

        ?>
        <div class="ttbm-elementor-related-widget">
            <?php echo do_shortcode("[ttbm-related ttbm_id='$ttbm_id' show='$show' style='$style' column='$column' sort='$sort']"); ?>
        </div>
        <?php
    }

    protected function _content_template() {
        ?>
        <div class="elementor-ttbm-related">
            <?php esc_html_e('Related Tours will be displayed here', 'tour-booking-manager'); ?>
        </div>
        <?php
    }
} 