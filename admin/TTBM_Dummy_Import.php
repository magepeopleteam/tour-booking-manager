<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Dummy_Import')) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		class TTBM_Dummy_Import {
			public function __construct() {
				//update_option('ttbm_dummy_already_inserted','no');exit;
				//add_action('admin_init', array($this, 'dummy_import'), 99);
			}
		}
	}