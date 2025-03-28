<?php
namespace TTBMPlugin\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class TTBMTourTopFilterWidget extends Widget_Base {

    public function get_name() {
        return 'ttbm-tour-top-filter-widget';
    }

    public function get_title() {
        return esc_html__('Tour Top Filter', 'tour-booking-manager');
    }

    public function get_icon() {
        return 'fa fa-filter';
    }

    public function get_categories() {
        return ['ttbm-elementor-support'];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Tour Top Filter', 'tour-booking-manager'),
            ]
        );

        $this->add_control(
            'show',
            [
                'label' => esc_html__('Number of Items to Show', 'tour-booking-manager'),
                'type' => Controls_Manager::NUMBER,
                'default' => 10,
                'min' => 1,
                'max' => 100,
            ]
        );

        $this->add_control(
            'pagination',
            [
                'label' => esc_html__('Pagination', 'tour-booking-manager'),
                'type' => Controls_Manager::SELECT,
                'default' => 'yes',
                'options' => [
                    'yes' => esc_html__('Yes', 'tour-booking-manager'),
                    'no' => esc_html__('No', 'tour-booking-manager'),
                ],
            ]
        );

        $this->add_control(
            'search-filter',
            [
                'label' => esc_html__('Search Filter', 'tour-booking-manager'),
                'type' => Controls_Manager::SELECT,
                'default' => 'yes',
                'options' => [
                    'yes' => esc_html__('Yes', 'tour-booking-manager'),
                    'no' => esc_html__('No', 'tour-booking-manager'),
                ],
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
                'default' => '3',
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

        $show = array_key_exists('show', $settings) ? $settings['show'] : '10';
        $pagination = array_key_exists('pagination', $settings) ? $settings['pagination'] : 'yes';
        $search_filter = array_key_exists('search-filter', $settings) ? $settings['search-filter'] : 'yes';
        $style = array_key_exists('style', $settings) ? $settings['style'] : 'grid';
        $column = array_key_exists('column', $settings) ? $settings['column'] : '3';
        $sort = array_key_exists('sort', $settings) ? $settings['sort'] : 'ASC';

        ?>
        <div class="ttbm-elementor-top-filter-widget">
            <?php echo do_shortcode("[ttbm-top-filter show='$show' pagination='$pagination' search-filter='$search_filter' style='$style' column='$column' sort='$sort']"); ?>
        </div>
        <?php
    }

    protected function _content_template() {
        ?>
        <div class="elementor-ttbm-top-filter">
            <?php esc_html_e('Tour Top Filter will be displayed here', 'tour-booking-manager'); ?>
        </div>
        <?php
    }
} 