<?php

namespace TTBMPlugin;

class TTBMPluginElementor {
	
	private static $_instance = null;
	
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
	
	public function widget_scripts() {
		//wp_register_script( 'tour-booking-helper-script', plugins_url( '/assets/js/hello-world.js', __FILE__ ), [ 'jquery' ], false, true );
	}
	
	public function add_widget_categories( $elements_manager ) {
		
		$elements_manager->add_category(
			'ttbm-elementor-support',
			[
				'title' => __( 'Tour Booking Manager', 'tour-booking-manager'),
				'icon'  => 'fa fa-plug',
			]
		);
		
	}
	
	private function include_widgets_files() {		
		require_once( __DIR__ . '/widget/tour-list.php' );		
	}
	
	public function register_widgets() {
		
		// Its is now safe to include Widgets files
		$this->include_widgets_files();
		
		// Register Widgets					
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Widgets\TTBMTourListWidget() );		
	}
	
	public function __construct() {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if ( \is_plugin_active( 'elementor/elementor.php' ) ) {
		add_action( 'elementor/frontend/after_register_scripts', [ $this, 'widget_scripts' ] );
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
		add_action( 'elementor/elements/categories_registered', [ $this, 'add_widget_categories' ] );
        }
	}
}


// Instantiate Plugin Class
TTBMPluginElementor::instance();
