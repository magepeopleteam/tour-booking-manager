<?php
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.

class TTBM_Blocks {
    public function __construct() {
        add_action('init', array($this, 'register_blocks'));
    }

    public function register_blocks() {
        if (!function_exists('register_block_type')) {
            return;
        }

        // Register block script first
        wp_register_script(
            'ttbm-blocks',
            TTBM_PLUGIN_URL . '/support/blocks/build/index.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
            filemtime(TTBM_PLUGIN_DIR . '/support/blocks/build/index.js')
        );

        // Register blocks
        $blocks = array(
            'top-search' => array(),
            'travel-list' => array(
                'style' => array('type' => 'string', 'default' => 'modern'),
                'show' => array('type' => 'number', 'default' => 12),
                'pagination' => array('type' => 'string', 'default' => 'yes'),
                'sidebar-filter' => array('type' => 'string', 'default' => 'yes'),
                'column' => array('type' => 'number', 'default' => 3)
            ),
            'top-filter' => array(
                'show' => array('type' => 'number', 'default' => 12),
                'pagination' => array('type' => 'string', 'default' => 'yes'),
                'search-filter' => array('type' => 'string', 'default' => 'yes')
            ),
            'location-list' => array(),
            'search-result' => array(),
            'hotel-list' => array(
                'show' => array('type' => 'number', 'default' => 12),
                'pagination' => array('type' => 'string', 'default' => 'yes')
            ),
            'registration' => array(
                'ttbm_id' => array('type' => 'string', 'default' => '')
            ),
            'related' => array(
                'ttbm_id' => array('type' => 'string', 'default' => ''),
                'show' => array('type' => 'number', 'default' => 4)
            )
        );

        foreach ($blocks as $block => $attributes) {
            $block_config = array(
                'api_version' => 2,
                'editor_script' => 'ttbm-blocks',
                'render_callback' => array($this, 'render_' . str_replace('-', '_', $block))
            );

            if (!empty($attributes)) {
                $block_config['attributes'] = $attributes;
            }

            register_block_type(
                'tour-booking-manager/' . $block,
                $block_config
            );
        }
    }

    // Render callbacks
    public function render_top_search($attributes) {
        return do_shortcode('[ttbm-top-search]');
    }

    public function render_travel_list($attributes) {
        $shortcode = sprintf(
            '[travel-list style="%s" show="%d" pagination="%s" sidebar-filter="%s" column="%d"]',
            $attributes['style'],
            $attributes['show'],
            $attributes['pagination'],
            $attributes['sidebar-filter'],
            $attributes['column']
        );
        return do_shortcode($shortcode);
    }

    public function render_top_filter($attributes) {
        $shortcode = sprintf(
            '[ttbm-top-filter show="%d" pagination="%s" search-filter="%s"]',
            $attributes['show'],
            $attributes['pagination'],
            $attributes['search-filter']
        );
        return do_shortcode($shortcode);
    }

    public function render_location_list($attributes) {
        return do_shortcode('[travel-location-list]');
    }

    public function render_search_result($attributes) {
        return do_shortcode('[ttbm-search-result]');
    }

    public function render_hotel_list($attributes) {
        $shortcode = sprintf(
            '[ttbm-hotel-list show="%d" pagination="%s"]',
            $attributes['show'],
            $attributes['pagination']
        );
        return do_shortcode($shortcode);
    }

    public function render_registration($attributes) {
        $shortcode = sprintf(
            '[ttbm-registration ttbm_id="%s"]',
            $attributes['ttbm_id']
        );
        return do_shortcode($shortcode);
    }

    public function render_related($attributes) {
        $shortcode = sprintf(
            '[ttbm-related ttbm_id="%s" show="%d"]',
            $attributes['ttbm_id'],
            $attributes['show']
        );
        return do_shortcode($shortcode);
    }
}

// Initialize blocks
add_action('init', function() {
    new TTBM_Blocks();
}, 0); 