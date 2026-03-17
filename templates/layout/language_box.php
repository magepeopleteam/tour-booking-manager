<?php
	if ( ! defined( 'ABSPATH' ) )die;

	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$status = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_travel_language_status');
	$tour_type = TTBM_Function::get_tour_type( $ttbm_post_id );

    $language = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_travel_language');
	$languages = is_array($language) ? $language : (!empty($language) ? array($language) : array());

	$resolve_language_label = static function ($locale) {
		$language_label = $locale;
		$translations = array();
		if (!function_exists('wp_get_available_translations')) {
			require_once ABSPATH . 'wp-admin/includes/translation-install.php';
		}
		if (function_exists('wp_get_available_translations')) {
			$translations = wp_get_available_translations();
		}

		if (!empty($translations[$locale])) {
			$native_name = $translations[$locale]['native_name'] ?? '';
			$english_name = $translations[$locale]['english_name'] ?? '';
			$language_label = $english_name ? $english_name : ($native_name ? $native_name : $locale);
		} elseif (class_exists('Locale') && preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale)) {
			$english_name = \Locale::getDisplayLanguage($locale, 'en');
			$native_name = \Locale::getDisplayLanguage($locale, $locale);
			if ($english_name) {
				$language_label = $english_name;
			} elseif ($native_name) {
				$language_label = $native_name;
			}
		}
		return $language_label;
	};

	$language_labels = array();
	foreach ($languages as $locale) {
		if ($locale) {
			$language_labels[] = $resolve_language_label($locale);
		}
	}
	$language_labels = array_unique($language_labels);
	$language_label_text = implode(', ', $language_labels);

	if ( $tour_type == 'general' && $status != 'off' && !empty($language_labels)) {
		?>
       <div class="item_icon" title="<?php echo esc_attr( __('Language', 'tour-booking-manager') ); ?>">
            <i class="mi mi-language"></i>
            <?php echo esc_html( $language_label_text ); ?>
        </div>
	<?php
	}
