<?php
namespace TTBMPlugin\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;
// use Elementor\Scheme_Typography;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * @since 1.1.0
 */

class TTBMTourListWidget extends Widget_Base {

	public function get_name() {
		return 'ttbm-tour-list-widget';
	}

	public function get_title() {
		return esc_html__( 'Tour List', 'tour-booking-manager' );
	}

	public function get_icon() {
		return 'fa fa-list-alt';
	}

	public function get_categories() {
		return [ 'ttbm-elementor-support' ];
	}

	protected function _register_controls() {

		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Tour List', 'tour-booking-manager' ),
			]
		);




		$this->add_control(
			'ttbm_tour_list_cat',
			[
				'label' => esc_html__( 'Category', 'tour-booking-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => '0',
				'options' => ttbm_elementor_get_tax_term('ttbm_tour_cat'),			
				'separator' => 'none',
			]
		);

		$this->add_control(
			'ttbm_tour_list_org',
			[
				'label' => esc_html__( 'Organizer', 'tour-booking-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => '0',
				'options' => ttbm_elementor_get_tax_term('ttbm_tour_org'),			
				'separator' => 'none',
			]
		);

		$this->add_control(
			'ttbm_tour_list_location',
			[
				'label' => esc_html__( 'Location/City', 'tour-booking-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => '0',
				'options' => ttbm_elementor_get_tax_term('ttbm_tour_location','slug'),			
				'separator' => 'none',
			]
		);

		$this->add_control(
			'ttbm_tour_list_country',
			[
				'label' => esc_html__( 'Country', 'tour-booking-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => '0',
				'options' => ttbm_get_coutnry_arr(),			
				'separator' => 'none',
			]
		);

		$this->add_control(
			'ttbm_tour_list_style',
			[
				'label' => esc_html__( 'List Style', 'tour-booking-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'grid',				
				'options' => [
					'grid' => esc_html__( 'Grid', 'tour-booking-manager' ),
					'list' => esc_html__( 'List', 'tour-booking-manager' )
				],			
				'separator' => 'none',
			]
		);

		// $this->add_control(
		// 	'ttbm_tour_list_column',
		// 	[
		// 		'label' => __( 'Column', 'tour-booking-manager' ),
		// 		'type' => Controls_Manager::SELECT,
		// 		'default' => '3',				
		// 		'options' => [
		// 			'1' => __( '1', 'tour-booking-manager' ),
		// 			'2' => __( '2', 'tour-booking-manager' ),
		// 			'3' => __( '3', 'tour-booking-manager' ),
		// 			'4' => __( '4', 'tour-booking-manager' )
		// 		],			
		// 		'separator' => 'none',
		// 	]
		// );

		// $this->add_control(
		// 	'ttbm_tour_list_cat_filter',
		// 	[
		// 		'label' => __( 'Category Filter', 'tour-booking-manager' ),
		// 		'type' => Controls_Manager::SELECT,
		// 		'default' => 'no',
		// 		'options' => [
		// 			'yes' => __( 'Yes', 'tour-booking-manager' ),
		// 			'no' => __( 'No', 'tour-booking-manager' )
		// 		],		
		// 		'separator' => 'none',
		// 	]
		// );

		// $this->add_control(
		// 	'ttbm_tour_list_org_filter',
		// 	[
		// 		'label' => __( 'Organizer Filter', 'tour-booking-manager' ),
		// 		'type' => Controls_Manager::SELECT,
		// 		'default' => 'no',
		// 		'options' => [
		// 			'yes' => __( 'Yes', 'tour-booking-manager' ),
		// 			'no' => __( 'No', 'tour-booking-manager' )
		// 		],		
		// 		'separator' => 'none',
		// 	]
		// );

		$this->add_control(
			'ttbm_tour_list_show',
			[
				'label' => esc_html__( 'No of Item Show', 'tour-booking-manager' ),
				'type' => Controls_Manager::TEXT,
				'default' => esc_html__( '10', 'tour-booking-manager' ),
			]
		);
		
		$this->add_control(
			'ttbm_tour_list_pagination',
			[
				'label' => esc_html__( 'Pagination', 'tour-booking-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'no',
				'options' => [
					'yes' => esc_html__( 'Yes', 'tour-booking-manager' ),
					'no' => esc_html__( 'No', 'tour-booking-manager' )
				],			
				'separator' => 'none',
			]
		);
		// $this->add_control(
		// 	'ttbm_tour_carousal_id',
		// 	[
		// 		'label' => __( 'Carousal Unique ID', 'tour-booking-manager' ),
		// 		'type' => Controls_Manager::TEXT,
		// 		'default' => __( '102448', 'tour-booking-manager' ),
		// 	]
		// );		
		// $this->add_control(
		// 	'ttbm_tour_list_carousal_nav',
		// 	[
		// 		'label' => __( 'Carousal Navigation', 'tour-booking-manager' ),
		// 		'type' => Controls_Manager::SELECT,
		// 		'default' => 'no',
		// 		'options' => [
		// 			'yes' => __( 'Yes', 'tour-booking-manager' ),
		// 			'no' => __( 'No', 'tour-booking-manager' )
		// 		],			
		// 		'separator' => 'none',
		// 	]
		// );
		
		// $this->add_control(
		// 	'ttbm_tour_list_carousal_dot',
		// 	[
		// 		'label' => __( 'Carousal Dot', 'tour-booking-manager' ),
		// 		'type' => Controls_Manager::SELECT,
		// 		'default' => 'yes',
		// 		'options' => [
		// 			'yes' => __( 'Yes', 'tour-booking-manager' ),
		// 			'no' => __( 'No', 'tour-booking-manager' )
				
		// 		],			
		// 		'separator' => 'none',
		// 	]
		// );
		
		// $this->add_control(
		// 	'ttbm_tour_list_timeline_mode',
		// 	[
		// 		'label' => __( 'Timeline Style', 'tour-booking-manager' ),
		// 		'type' => Controls_Manager::SELECT,
		// 		'default' => 'vertical',
		// 		'options' => [
		// 			'vertical' => __( 'Vertical', 'tour-booking-manager' ),
		// 			'horizontal' => __( 'Horizontal', 'tour-booking-manager' )
				
		// 		],			
		// 		'separator' => 'none',
		// 	]
		// );
		
		$this->add_control(
			'ttbm_tour_list_sort',
			[
				'label' => esc_html__( 'Sort', 'tour-booking-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'ASC',
				'options' => [
					'ASC' => esc_html__( 'Ascending', 'tour-booking-manager' ),
					'DESC' => esc_html__( 'Descending', 'tour-booking-manager' )
				
				],			
				'separator' => 'none',
			]
		);
		    		
		$this->add_control(
			'ttbm_tour_list_status',
			[
				'label' => esc_html__( 'Status', 'tour-booking-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'upcoming',
				'options' => [
					'upcoming' => esc_html__( 'Upcoming', 'tour-booking-manager' ),
					'expired' => esc_html__( 'Expired', 'tour-booking-manager' )
				
				],			
				'separator' => 'none',

			]
		);
		

		$this->add_control(
			'ttbm_tour_show_thumbnail',
			[
				'label' => esc_html__( 'Show Thumbnail', 'tour-booking-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => esc_html__( 'Yes', 'tour-booking-manager' ),
					'none' => esc_html__( 'No', 'tour-booking-manager' )
				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .ttbm-elementor-tour-list-widget .bg_image_area' => 'display: {{VALUE}};',
                   
                ],				
			]
		);		
		      
		$this->end_controls_section();
        
    


		
        // Date Style
		$this->start_controls_section(
			'ttbm_box_style',
			[
				'label' => __( 'Box', 'tour-booking-manager' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
			   		
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'ttbm_box_border',
                'selector' => '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_travel_list_item',
            ]
		);		
        $this->add_responsive_control(
            'ttbm_boxe_border_radius',
            [
                'label' => __( 'Border Radius', 'tour-booking-manager' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_travel_list_item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                   
                ],
            ]
		);    
		        
		$this->add_responsive_control(
			'ttbm_box_padding',
			[
				'label' => __( 'Padding', 'tour-booking-manager' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_travel_list_item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);         
		$this->add_responsive_control(
			'ttbm_box_margin',
			[
				'label' => __( 'Margin', 'tour-booking-manager' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_travel_list_item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);         
		$this->add_control(
			'ttbm_box_bg_color',
			[
				'label' => __( 'Background Color', 'tour-booking-manager' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_travel_list_item, {{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_travel_list_item .ttbm_list_details' => 'background: {{VALUE}};',
				],
			]
        );
			
		$this->add_group_control(
			\Elementor\Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'ttbm_boxbox_shadow',
				'label' => __( 'Box Shadow', 'plugin-domain' ),
				'selector' => '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_travel_list_item',
			]
		);		 
 
		
        $this->end_controls_section();  
		
		

		
        // Date Style
		$this->start_controls_section(
			'ttbm_tour_list_date_style',
			[
				'label' => __( 'Date', 'tour-booking-manager' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
			'ttbm_tour_show_date',
			[
				'label' => __( 'Show Date', 'tour-booking-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => __( 'Yes', 'tour-booking-manager' ),
					'none' => __( 'No', 'tour-booking-manager' )
				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_start_date' => 'display: {{VALUE}};',
                   
                ],				
			]
		);	
		$this->add_control(
			'ttbm_tour_date_width',
			[
				'label' => __( 'Width', 'simple-email-mailchimp-subscriber' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 392,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 140,
				],
				'selectors' => [
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_start_date' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);    		

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'mep_date_border',
                'selector' => '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_start_date',
            ]
		);		
        $this->add_responsive_control(
            'mep_date_border_radius',
            [
                'label' => __( 'Border Radius', 'tour-booking-manager' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_start_date' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                   
                ],
            ]
		);    
		        
		$this->add_responsive_control(
			'mep_date_padding',
			[
				'label' => __( 'Padding', 'tour-booking-manager' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_start_date' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);         
		$this->add_responsive_control(
			'mep_date_margin',
			[
				'label' => __( 'Margin', 'tour-booking-manager' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_start_date' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);         
		$this->add_control(
			'mep_date_bg_color',
			[
				'label' => __( 'Background Color', 'tour-booking-manager' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_start_date' => 'background: {{VALUE}};',
				],
			]
        );
			
		$this->add_control(
			'mep_date_text_color',
			[
				'label' => __( 'Text Color', 'tour-booking-manager' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_start_date' => 'color: {{VALUE}};',
				],
			]
        ); 

// 		$this->add_group_control(
// 			Group_Control_Typography::get_type(),
// 			[
// 				'name' => 'mep_date_typo',
// 				'scheme' => Scheme_Typography::TYPOGRAPHY_3,
// 				'selector' => '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_start_date',
// 			]
//         );  
        $this->end_controls_section();  
		
		



		
        // Date Style
		$this->start_controls_section(
			'ttbm_tour_list_location_style',
			[
				'label' => __( 'Location', 'tour-booking-manager' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
			'ttbm_tour_show_location',
			[
				'label' => __( 'Show Location', 'tour-booking-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => __( 'Yes', 'tour-booking-manager' ),
					'none' 	=> __( 'No', 'tour-booking-manager' )
				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_location' => 'display: {{VALUE}};',
                   
                ],				
			]
		);	
		$this->add_control(
			'ttbm_tour_location_width',
			[
				'label' => __( 'Width', 'simple-email-mailchimp-subscriber' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 392,
						'step' => 1,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 175,
				],
				'selectors' => [
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_location' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);    		

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'ttbm_tour_location_border',
                'selector' => '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_location',
            ]
		);		
        $this->add_responsive_control(
            'ttbm_tour_location_border_radius',
            [
                'label' => __( 'Border Radius', 'tour-booking-manager' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_location' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                   
                ],
            ]
		);    
		        
		$this->add_responsive_control(
			'ttbm_tour_location_padding',
			[
				'label' => __( 'Padding', 'tour-booking-manager' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_location' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);         
		$this->add_responsive_control(
			'ttbm_tour_location_margin',
			[
				'label' => __( 'Margin', 'tour-booking-manager' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_location' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);         
		$this->add_control(
			'ttbm_tour_location_bg_color',
			[
				'label' => __( 'Background Color', 'tour-booking-manager' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_location' => 'background: {{VALUE}};',
				],
			]
        );
			
		$this->add_control(
			'ttbm_tour_location_text_color',
			[
				'label' => __( 'Text Color', 'tour-booking-manager' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_location' => 'color: {{VALUE}};',
				],
			]
        ); 

// 		$this->add_group_control(
// 			Group_Control_Typography::get_type(),
// 			[
// 				'name' => 'ttbm_tour_location_typo',
// 				'scheme' => Scheme_Typography::TYPOGRAPHY_3,
// 				'selector' => '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_location',
// 			]
//         );  
        $this->end_controls_section();  
		
		






        // Title Style
		$this->start_controls_section(
			'ttbm_tour_title_style',
			[
				'label' => __( 'Title', 'tour-booking-manager' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
			'ttbm_tour_show_title',
			[
				'label' => __( 'Show Title', 'tour-booking-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => __( 'Yes', 'tour-booking-manager' ),
					'none' => __( 'No', 'tour-booking-manager' )
				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_title' => 'display: {{VALUE}};',
                   
                ],				
			]
		);	
		$this->add_responsive_control(
			'ttbm_tour_title_style_padding',
			[
				'label' => __( 'Padding', 'tour-booking-manager' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);   
// 		$this->add_group_control(
// 			Group_Control_Typography::get_type(),
// 			[
// 				'name' => 'ttbm_tour_title_style_type',
// 				'scheme' => Scheme_Typography::TYPOGRAPHY_3,
// 				'selector' => '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_title h3',
// 			]
//         );    
		$this->add_control(
			'ttbm_tour_title_style_color',
			[
				'label' => __( 'Text Color', 'tour-booking-manager' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [				
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_title h3' => 'color: {{VALUE}};',
				],
			]
        );            	            
        $this->end_controls_section();  


        // Description Style
		$this->start_controls_section(
			'ttbm_tour_desc_style',
			[
				'label' => __( 'Description', 'tour-booking-manager' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
			'ttbm_tour_show_desc',
			[
				'label' => __( 'Show Title', 'tour-booking-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => __( 'Yes', 'tour-booking-manager' ),
					'none' => __( 'No', 'tour-booking-manager' )
				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_description' => 'display: {{VALUE}};',
                   
                ],				
			]
		);	
		$this->add_responsive_control(
			'ttbm_tour_desc_style_padding',
			[
				'label' => __( 'Padding', 'tour-booking-manager' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_description' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);   
// 		$this->add_group_control(
// 			Group_Control_Typography::get_type(),
// 			[
// 				'name' => 'ttbm_tour_desc_style_type',
// 				'scheme' => Scheme_Typography::TYPOGRAPHY_3,
// 				'selector' => '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_description',
// 			]
//         );    
		$this->add_control(
			'ttbm_tour_title_desc_color',
			[
				'label' => __( 'Text Color', 'tour-booking-manager' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [				
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_description' => 'color: {{VALUE}};',
				],
			]
		);  

		$this->add_control(
			'ttbm_tour_show_desc_readmore',
			[
				'label' => __( 'Show Read More', 'tour-booking-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => __( 'Yes', 'tour-booking-manager' ),
					'none' 	=> __( 'No', 'tour-booking-manager' )
				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_description a' => 'display: {{VALUE}};',
                   
                ],				
			]
		);		          	            
        $this->end_controls_section();  
        






        // Price Style
		$this->start_controls_section(
			'ttbm_tour_price_style',
			[
				'label' => __( 'Price', 'tour-booking-manager' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'ttbm_tour_show_price',
			[
				'label' => __( 'Show Price', 'tour-booking-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => __( 'Yes', 'tour-booking-manager' ),
					'none' => __( 'No', 'tour-booking-manager' )
				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_meta_price' => 'display: {{VALUE}};',
                   
                ],				
			]
		);			             
		$this->add_responsive_control(
			'ttbm_tour_price_style_padding',
			[
				'label' => __( 'Padding', 'tour-booking-manager' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_meta_price' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);   
// 		$this->add_group_control(
// 			Group_Control_Typography::get_type(),
// 			[
// 				'name' => 'ttbm_tour_price_style_type',
// 				'scheme' => Scheme_Typography::TYPOGRAPHY_3,
// 				'selector' => '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_meta_price p',
// 			]
//         );    
		$this->add_control(
			'ttbm_tour_price_style_color',
			[
				'label' => __( 'Text Color', 'tour-booking-manager' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [				
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_meta_price strong' => 'color: {{VALUE}};',
				],
			]
        );            
        $this->end_controls_section();  
        




        // Time Style
		$this->start_controls_section(
			'ttbm_tour_time_style',
			[
				'label' => __( 'Time', 'tour-booking-manager' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'ttbm_tour_show_time',
			[
				'label' => __( 'Show Time', 'tour-booking-manager' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'block',
				'options' => [
					'block' => __( 'Yes', 'tour-booking-manager' ),
					'none' => __( 'No', 'tour-booking-manager' )
				
				],			
				'separator' => 'none',
				'selectors' => [
                    '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_meta ul' => 'display: {{VALUE}};',
                   
                ],				
			]
		);			             
		$this->add_responsive_control(
			'ttbm_tour_time_style_padding',
			[
				'label' => __( 'Padding', 'tour-booking-manager' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors' => [
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_meta ul li' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);   
// 		$this->add_group_control(
// 			Group_Control_Typography::get_type(),
// 			[
// 				'name' => 'ttbm_tour_time_style_type',
// 				'scheme' => Scheme_Typography::TYPOGRAPHY_3,
// 				'selector' => '{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_meta ul li',
// 			]
//         );    
		$this->add_control(
			'ttbm_tour_time_style_color',
			[
				'label' => __( 'Text Color', 'tour-booking-manager' ),
				'type' => Controls_Manager::COLOR,
				'selectors' => [				
					'{{WRAPPER}} .ttbm-elementor-tour-list-widget .ttbm_list_meta ul li' => 'color: {{VALUE}};',
				],
			]
        );            
        $this->end_controls_section();  
        





        
        

	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$cat 				= array_key_exists('ttbm_tour_list_cat',$settings) && $settings['ttbm_tour_list_cat'] > 0 ? $settings['ttbm_tour_list_cat'] : '';
		$org 				= array_key_exists('ttbm_tour_list_org',$settings) && $settings['ttbm_tour_list_org'] > 0 ? $settings['ttbm_tour_list_org'] : '';
	    $city 				= array_key_exists('ttbm_tour_list_location',$settings) ? $settings['ttbm_tour_list_location'] : '';
		$country 			= array_key_exists('ttbm_tour_list_country',$settings) ? $settings['ttbm_tour_list_country'] : '';
		$style 				= array_key_exists('ttbm_tour_list_style',$settings) ? $settings['ttbm_tour_list_style'] : 'grid';
		$column 			= array_key_exists('ttbm_tour_list_column',$settings) ? $settings['ttbm_tour_list_column'] : '3';		
		$show 				= array_key_exists('ttbm_tour_list_show',$settings) ? $settings['ttbm_tour_list_show'] : '10';
		$pagination 		= array_key_exists('ttbm_tour_list_pagination',$settings) ? $settings['ttbm_tour_list_pagination'] : 'no';
		$sort 				= array_key_exists('ttbm_tour_list_sort',$settings) ? $settings['ttbm_tour_list_sort'] : 'ASC';
		$status 			= array_key_exists('ttbm_tour_list_status',$settings) ? $settings['ttbm_tour_list_status'] : 'upcoming';


?>
<div class="ttbm-elementor-tour-list-widget">
		<?php echo do_shortcode("[travel-list cat='$cat' city='$city' country='$country' org='$org' style='$style' column='$column'  show='$show' pagination='$pagination' sort='$sort' status='$status']"); ?>
</div>
<?php
}

	protected function _content_template() {
	
	}
}
