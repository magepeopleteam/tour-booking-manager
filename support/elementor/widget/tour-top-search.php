<?php
namespace TTBMPlugin\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class TTBMTourTopSearchWidget extends Widget_Base {

    public function get_name() {
        return 'ttbm-tour-top-search-widget';
    }

    public function get_title() {
        return esc_html__('Tour Top Search', 'tour-booking-manager');
    }

    public function get_icon() {
        return 'fa fa-search';
    }

    public function get_categories() {
        return ['ttbm-elementor-support'];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__('Tour Top Search', 'tour-booking-manager'),
                'description' => esc_html__('Default fields: Tour Location, Activities, Month, and Find Tour Button', 'tour-booking-manager'),
            ]
        );

        $this->add_control(
            'title-filter',
            [
                'label' => esc_html__('Title Filter', 'tour-booking-manager'),
                'type' => Controls_Manager::SELECT,
                'default' => 'no',
                'options' => [
                    'yes' => esc_html__('Yes', 'tour-booking-manager'),
                    'no' => esc_html__('No', 'tour-booking-manager'),
                ],
            ]
        );

        $this->add_control(
            'category-filter',
            [
                'label' => esc_html__('Category Filter', 'tour-booking-manager'),
                'type' => Controls_Manager::SELECT,
                'default' => 'no',
                'options' => [
                    'yes' => esc_html__('Yes', 'tour-booking-manager'),
                    'no' => esc_html__('No', 'tour-booking-manager'),
                ],
            ]
        );

        $this->add_control(
            'organizer-filter',
            [
                'label' => esc_html__('Organizer Filter', 'tour-booking-manager'),
                'type' => Controls_Manager::SELECT,
                'default' => 'no',
                'options' => [
                    'yes' => esc_html__('Yes', 'tour-booking-manager'),
                    'no' => esc_html__('No', 'tour-booking-manager'),
                ],
            ]
        );

        $this->add_control(
            'location-filter',
            [
                'label' => esc_html__('Location Filter', 'tour-booking-manager'),
                'type' => Controls_Manager::SELECT,
                'default' => 'yes',
                'options' => [
                    'yes' => esc_html__('Yes', 'tour-booking-manager'),
                    'no' => esc_html__('No', 'tour-booking-manager'),
                ],
            ]
        );

        $this->add_control(
            'country-filter',
            [
                'label' => esc_html__('Country Filter', 'tour-booking-manager'),
                'type' => Controls_Manager::SELECT,
                'default' => 'no',
                'options' => [
                    'yes' => esc_html__('Yes', 'tour-booking-manager'),
                    'no' => esc_html__('No', 'tour-booking-manager'),
                ],
            ]
        );

        $this->add_control(
            'activity-filter',
            [
                'label' => esc_html__('Activity Filter', 'tour-booking-manager'),
                'type' => Controls_Manager::SELECT,
                'default' => 'yes',
                'options' => [
                    'yes' => esc_html__('Yes', 'tour-booking-manager'),
                    'no' => esc_html__('No', 'tour-booking-manager'),
                ],
            ]
        );

        $this->add_control(
            'month-filter',
            [
                'label' => esc_html__('Month Filter', 'tour-booking-manager'),
                'type' => Controls_Manager::SELECT,
                'default' => 'yes',
                'options' => [
                    'yes' => esc_html__('Yes', 'tour-booking-manager'),
                    'no' => esc_html__('No', 'tour-booking-manager'),
                ],
            ]
        );

        $this->add_control(
            'tag-filter',
            [
                'label' => esc_html__('Tag Filter', 'tour-booking-manager'),
                'type' => Controls_Manager::SELECT,
                'default' => 'no',
                'options' => [
                    'yes' => esc_html__('Yes', 'tour-booking-manager'),
                    'no' => esc_html__('No', 'tour-booking-manager'),
                ],
            ]
        );

        $this->add_control(
            'feature-filter',
            [
                'label' => esc_html__('Feature Filter', 'tour-booking-manager'),
                'type' => Controls_Manager::SELECT,
                'default' => 'no',
                'options' => [
                    'yes' => esc_html__('Yes', 'tour-booking-manager'),
                    'no' => esc_html__('No', 'tour-booking-manager'),
                ],
            ]
        );

        $this->add_control(
            'duration-filter',
            [
                'label' => esc_html__('Duration Filter', 'tour-booking-manager'),
                'type' => Controls_Manager::SELECT,
                'default' => 'no',
                'options' => [
                    'yes' => esc_html__('Yes', 'tour-booking-manager'),
                    'no' => esc_html__('No', 'tour-booking-manager'),
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Build shortcode attributes
        $shortcode_attrs = [];
        
        $filter_options = [
            'title-filter',
            'category-filter',
            'organizer-filter',
            'location-filter',
            'country-filter',
            'activity-filter',
            'month-filter',
            'tag-filter',
            'feature-filter',
            'duration-filter'
        ];

        foreach ($filter_options as $filter) {
            if ($settings[$filter] === 'yes') {
                $shortcode_attrs[] = $filter . "='yes'";
            }
        }

        ?>
        <div class="ttbm-elementor-top-search-widget">
            <?php echo do_shortcode('[ttbm-top-search' . (!empty($shortcode_attrs) ? ' ' . implode(' ', $shortcode_attrs) : '') . ']'); ?>
        </div>
        <?php
    }

    protected function _content_template() {
        ?>
        <div class="elementor-ttbm-top-search">
            <?php esc_html_e('Tour Top Search will be displayed here', 'tour-booking-manager'); ?>
        </div>
        <?php
    }
} 