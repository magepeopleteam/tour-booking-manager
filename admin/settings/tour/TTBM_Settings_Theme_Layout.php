<?php
	/**
	 * "Theme layout" sidebar card: choose the tour page template and its theme colour.
	 * Sits under the Ticket preview card. Both values are saved per tour (ttbm_theme_file / ttbm_theme_color);
	 * an empty colour means "follow the site-wide Theme Color" from the global style settings.
	 */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Settings_Theme_Layout')) {
		class TTBM_Settings_Theme_Layout {
			public function __construct() {
				add_action('ttbm_right_sidebar_content', [$this, 'sidebar_card'], 6);
				add_action('admin_enqueue_scripts', [$this, 'enqueue']);
			}
			public static function preset_colors(string $default): array {
				$presets = ['#2563eb', '#d97706', '#be123c', '#7c3aed', '#0f172a', '#0f766e'];
				return array_values(array_filter($presets, function ($c) use ($default) {
					return strtolower($c) !== strtolower($default);
				}));
			}
			public static function default_color(): string {
				$color = sanitize_hex_color((string) TTBM_Global_Function::get_style_settings('theme_color', '#6b35e8'));
				return $color ? $color : '#6b35e8';
			}
			public function enqueue() {
				$screen = function_exists('get_current_screen') ? get_current_screen() : null;
				if (!$screen || $screen->base !== 'post') {
					return;
				}
				wp_enqueue_script('ttbm-theme-layout', TTBM_PLUGIN_URL . '/assets/admin/ttbm-theme-layout.js', array('jquery'), filemtime(TTBM_PLUGIN_DIR . '/assets/admin/ttbm-theme-layout.js'), true);
			}
			public function sidebar_card($tour_id) {
				$default      = self::default_color();
				$saved        = sanitize_hex_color((string) TTBM_Global_Function::get_post_info($tour_id, 'ttbm_theme_color', ''));
				$saved        = $saved ? $saved : '';
				$current      = TTBM_Global_Function::get_post_info($tour_id, 'ttbm_theme_file', 'default.php');
				$templates    = TTBM_Function::all_details_template();
				$presets      = self::preset_colors($default);
				$is_preset    = $saved && in_array(strtolower($saved), array_map('strtolower', $presets), true);
				$is_custom    = $saved && strtolower($saved) !== strtolower($default) && !$is_preset;
				$active_color = $saved ? $saved : $default;
				$screens      = TTBM_Function::template_screenshot_url();
				?>
                <div class="ttbm-sb-card ttbm-tl" id="ttbm-theme-layout" data-default="<?php echo esc_attr($default); ?>" style="--ttbm-tl-accent: <?php echo esc_attr($active_color); ?>;">
                    <div class="ttbm-tl__head">
                        <h4><?php esc_html_e('Theme layout', 'tour-booking-manager'); ?></h4>
                        <p><?php esc_html_e('Template and colour of this tour page. Changes apply live.', 'tour-booking-manager'); ?></p>
                    </div>
                    <div class="ttbm-tl__label"><?php esc_html_e('Template', 'tour-booking-manager'); ?></div>
                    <div class="ttbm-tl__templates" role="radiogroup" aria-label="<?php esc_attr_e('Template', 'tour-booking-manager'); ?>">
						<?php foreach ($templates as $key => $name) {
							if ($key === 'hotel_default.php' || $key === 'viator.php') {
								continue;
							}
							$image = preg_replace('/\.php$/', '.webp', $key);
							?>
                            <button type="button" class="ttbm-tl__tpl<?php echo $current === $key ? ' is-active' : ''; ?>" role="radio" aria-checked="<?php echo $current === $key ? 'true' : 'false'; ?>" data-template="<?php echo esc_attr($key); ?>">
                                <span class="ttbm-tl__thumb"><img src="<?php echo esc_url($screens . $image); ?>" alt="" loading="lazy"></span>
                                <span class="ttbm-tl__tpl-foot">
                                    <span class="ttbm-tl__tpl-name"><?php echo esc_html($name); ?></span>
                                    <span class="ttbm-tl__radio" aria-hidden="true"></span>
                                </span>
                            </button>
						<?php } ?>
                    </div>
                    <div class="ttbm-tl__label"><?php esc_html_e('Theme color', 'tour-booking-manager'); ?></div>
                    <p class="ttbm-tl__desc"><?php esc_html_e('The first color is your site default (Theme Settings). Saved right away.', 'tour-booking-manager'); ?></p>
                    <input type="hidden" name="ttbm_theme_color" value="<?php echo esc_attr($saved); ?>">
                    <div class="ttbm-tl__swatches" role="radiogroup" aria-label="<?php esc_attr_e('Theme color', 'tour-booking-manager'); ?>">
                        <button type="button" class="ttbm-tl__swatch<?php echo !$saved || strtolower($saved) === strtolower($default) ? ' is-active' : ''; ?>" role="radio" data-color="" data-hex="<?php echo esc_attr($default); ?>" style="background: <?php echo esc_attr($default); ?>;" title="<?php esc_attr_e('Site default', 'tour-booking-manager'); ?>"></button>
						<?php foreach ($presets as $color) { ?>
                            <button type="button" class="ttbm-tl__swatch<?php echo $saved && strtolower($saved) === strtolower($color) ? ' is-active' : ''; ?>" role="radio" data-color="<?php echo esc_attr($color); ?>" data-hex="<?php echo esc_attr($color); ?>" style="background: <?php echo esc_attr($color); ?>;" title="<?php echo esc_attr($color); ?>"></button>
						<?php } ?>
                        <label class="ttbm-tl__swatch ttbm-tl__swatch--custom<?php echo $is_custom ? ' is-active' : ''; ?>" title="<?php esc_attr_e('Custom color', 'tour-booking-manager'); ?>"<?php echo $is_custom ? ' style="background:' . esc_attr($saved) . ';"' : ''; ?>>
                            <input type="color" value="<?php echo esc_attr($active_color); ?>" aria-label="<?php esc_attr_e('Custom color', 'tour-booking-manager'); ?>">
                        </label>
                    </div>
                </div>
				<?php
			}
		}
		new TTBM_Settings_Theme_Layout();
	}
