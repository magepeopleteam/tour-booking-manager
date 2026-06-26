<?php
if (!defined('ABSPATH')) {
	die;
}
if (!class_exists('TTBM_Settings_Sidebar')) {
	class TTBM_Settings_Sidebar {
		public function __construct() {
			add_action('ttbm_right_sidebar_content', [$this, 'render_featured_sidebar'], 10);
			add_action('ttbm_right_sidebar_content', [$this, 'render_taxonomy_sidebar'], 20);
			add_action('ttbm_hotel_right_sidebar_content', [$this, 'render_featured_sidebar'], 10);
			add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
			add_action('admin_head', [$this, 'output_styles']);
			add_action('admin_notices', [$this, 'output_skeleton_loader']);
			add_action('add_meta_boxes', [$this, 'remove_default_side_boxes'], 20);
			add_filter('get_user_option_screen_layout_' . TTBM_Function::get_cpt_name(), [$this, 'force_one_column_on_edit']);
			add_filter('get_user_option_screen_layout_ttbm_hotel', [$this, 'force_one_column_on_edit']);
			add_filter('admin_body_class', [$this, 'add_edit_body_class']);
		}

		public function force_one_column_on_edit() {
			return $this->is_modern_edit_screen() ? 1 : null;
		}

		/** Adds edit-page body classes for tour and hotel post screens. */
		public function add_edit_body_class($classes) {
			$screen = get_current_screen();
			if (!$screen || $screen->base !== 'post') {
				return $classes;
			}
			if ($screen->post_type === TTBM_Function::get_cpt_name()) {
				$classes .= ' ttbm-tour-edit-page ttbm-modern-edit-page';
			} elseif ($screen->post_type === 'ttbm_hotel') {
				$classes .= ' ttbm-hotel-edit-page ttbm-modern-edit-page';
			}
			return $classes;
		}

		private function is_tour_edit_screen(): bool {
			$screen = get_current_screen();
			return $screen
				&& $screen->post_type === TTBM_Function::get_cpt_name()
				&& $screen->base === 'post';
		}

		private function is_hotel_edit_screen(): bool {
			$screen = get_current_screen();
			return $screen
				&& $screen->post_type === 'ttbm_hotel'
				&& $screen->base === 'post';
		}

		private function is_modern_edit_screen(): bool {
			return $this->is_tour_edit_screen() || $this->is_hotel_edit_screen();
		}

		private function is_modern_edit_hook(string $hook): bool {
			if (!in_array($hook, array('post.php', 'post-new.php'), true)) {
				return false;
			}
			if ($this->is_modern_edit_screen()) {
				return true;
			}
			if ($hook !== 'post-new.php') {
				return false;
			}
			$post_type = isset($_GET['post_type']) ? sanitize_key(wp_unslash($_GET['post_type'])) : 'post'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return in_array($post_type, array(TTBM_Function::get_cpt_name(), 'ttbm_hotel'), true);
		}

		public function output_styles() {
			if (!$this->is_modern_edit_screen()) {
				return;
			}

			$post_id     = isset($_GET['post']) ? intval($_GET['post']) : 0; // phpcs:ignore
			$page_title  = $post_id ? get_the_title($post_id) : '';
			$is_tour     = $this->is_tour_edit_screen();
			$is_hotel    = $this->is_hotel_edit_screen();
			if ($is_tour) {
				$back_url   = admin_url('edit.php?post_type=' . TTBM_Function::get_cpt_name() . '&page=ttbm_list');
				$back_label = sprintf(__('← Back to %s', 'tour-booking-manager'), TTBM_Function::get_name() . 's');
			} else {
				$back_url   = admin_url('edit.php?post_type=' . TTBM_Function::get_cpt_name() . '&page=ttbm_hotel_booking_lists');
				$back_label = __('← Back to Hotels', 'tour-booking-manager');
			}
			?>
<style id="ttbm-sidebar-css">
/*
 * All rules below are prefixed with body.ttbm-modern-edit-page (or use
 * unique plugin class names) so they ONLY apply on the tour/hotel edit/add page
 * and cannot leak to any other WordPress admin page.
 */

/* ── Hide Screen Options + Help tabs ── */
body.ttbm-modern-edit-page #screen-meta-links,
body.ttbm-modern-edit-page #contextual-help-link-wrap,
body.ttbm-modern-edit-page #screen-options-link-wrap { display: none !important; }

/* ── 1-column layout: collapse empty WP side container ── */
body.ttbm-modern-edit-page #postbox-container-1 { display: none !important; }
body.ttbm-modern-edit-page #postbox-container-2 {
	margin-right: 0 !important;
	float: none !important;
	width: 100% !important;
	padding: 0 !important;
}
body.ttbm-modern-edit-page #post-body.columns-2 #postbox-container-2 { float: none !important; width: 100% !important; }
body.ttbm-modern-edit-page #postbox-container-1 .postbox { display: none !important; }

/* ── Post-body spacing ── */
body.ttbm-modern-edit-page #poststuff { padding-top: 10px !important; }
body.ttbm-modern-edit-page #post-body-content { margin-bottom: 0 !important; }

/* ── Page background ── */
body.ttbm-modern-edit-page #wpcontent,
body.ttbm-modern-edit-page #wpbody-content { background: #f4f6f9 !important; }
body.ttbm-modern-edit-page #wpcontent { padding-left: 0 !important; }
/* No left/right padding on the outer container — header fills 100% width.
   The .wrap inside handles content padding. */
body.ttbm-modern-edit-page #wpbody-content { padding: 0 !important; }
body.ttbm-modern-edit-page .wrap { padding: 0 20px 20px !important; margin: 0 auto !important; max-width: 90% !important; background: #fff !important; }

/* ══════════════════════════════════════════════
   SKELETON / PAGE LOADER
   ══════════════════════════════════════════════ */
#ttbm-page-loader {
	position: fixed !important;
	inset: 0 !important;
	z-index: 999999 !important;
	background: #f4f6f9 !important;
	display: flex !important;
	flex-direction: column !important;
	overflow: hidden !important;
}
@keyframes ttbmShimmer {
	0%   { background-position: -800px 0; }
	100% { background-position:  800px 0; }
}
.ttbm-sk {
	background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%) !important;
	background-size: 800px 100% !important;
	animation: ttbmShimmer 1.6s ease-in-out infinite !important;
	border-radius: 6px !important;
}

/* Header skeleton */
.ttbm-sk-header {
	height: 56px !important;
	background: #fff !important;
	border-bottom: 1px solid #e5e7eb !important;
	display: flex !important;
	align-items: center !important;
	justify-content: space-between !important;
	padding: 0 24px !important;
	flex-shrink: 0 !important;
}
.ttbm-sk-header-left { display: flex; align-items: center; gap: 12px; }
.ttbm-sk-header-left .ttbm-sk { width: 120px; height: 14px; }
.ttbm-sk-header-center .ttbm-sk { width: 260px; height: 18px; }
.ttbm-sk-header-right { display: flex; align-items: center; gap: 10px; }
.ttbm-sk-header-right .ttbm-sk:first-child { width: 90px; height: 34px; border-radius: 7px !important; }
.ttbm-sk-header-right .ttbm-sk:last-child  { width: 110px; height: 34px; border-radius: 7px !important; }

/* Body skeleton */
.ttbm-sk-body {
	display: flex !important;
	flex: 1 !important;
	overflow: hidden !important;
	max-width: 1460px !important;
	width: 100% !important;
	margin: 0 auto !important;
	background: #fff !important;
}

/* Left tabs skeleton */
.ttbm-sk-tabs {
	width: 220px !important;
	min-width: 220px !important;
	flex-shrink: 0 !important;
	border-right: 1px solid #e5e7eb !important;
	padding: 16px 12px !important;
	display: flex !important;
	flex-direction: column !important;
	gap: 6px !important;
	background: #fff !important;
}
.ttbm-sk-tab-item {
	height: 36px !important;
	border-radius: 6px !important;
	display: flex !important;
	align-items: center !important;
	padding: 0 10px !important;
	gap: 10px !important;
}
.ttbm-sk-tab-item:first-child { background: #eff6ff !important; }
.ttbm-sk-tab-icon  { width: 16px; height: 16px; flex-shrink: 0; }
.ttbm-sk-tab-label { height: 10px; flex: 1; }

/* Main content skeleton */
.ttbm-sk-content {
	flex: 1 !important;
	padding: 20px !important;
	display: flex !important;
	flex-direction: column !important;
	gap: 16px !important;
	background: #f4f6f9 !important;
	overflow: hidden !important;
}
.ttbm-sk-card {
	background: #fff !important;
	border-radius: 10px !important;
	padding: 20px 24px !important;
	display: flex !important;
	flex-direction: column !important;
	gap: 14px !important;
}
.ttbm-sk-card-title { height: 16px; width: 40%; }
.ttbm-sk-row { display: flex; gap: 16px; }
.ttbm-sk-row .ttbm-sk { flex: 1; height: 38px; border-radius: 6px !important; }
.ttbm-sk-line { height: 12px; }
.ttbm-sk-line.w60 { width: 60%; }
.ttbm-sk-line.w80 { width: 80%; }
.ttbm-sk-line.w40 { width: 40%; }
.ttbm-sk-editor { height: 160px; border-radius: 6px !important; }

/* Right sidebar skeleton */
.ttbm-sk-sidebar {
	width: 280px !important;
	min-width: 280px !important;
	flex-shrink: 0 !important;
	padding: 20px 16px 20px 0 !important;
	display: flex !important;
	flex-direction: column !important;
	gap: 14px !important;
	background: #f4f6f9 !important;
}
.ttbm-sk-sb-card {
	background: #fff !important;
	border-radius: 10px !important;
	padding: 16px !important;
	display: flex !important;
	flex-direction: column !important;
	gap: 12px !important;
}
.ttbm-sk-sb-img { height: 140px; border-radius: 8px !important; }
.ttbm-sk-sb-title { height: 13px; width: 55%; }
.ttbm-sk-cb-row { display: flex; align-items: center; gap: 8px; }
.ttbm-sk-cb { width: 14px; height: 14px; flex-shrink: 0; border-radius: 3px !important; }
.ttbm-sk-cb-label { height: 10px; flex: 1; max-width: 120px; }

/* Loader is a fixed overlay; do not hide page siblings or tab content stays invisible. */

/* ── Hide original WP page title row (replaced by our header) ── */
body.ttbm-modern-edit-page .wrap > h1.wp-heading-inline,
body.ttbm-modern-edit-page .wrap > .page-title-action,
body.ttbm-modern-edit-page .ttbm-page-tour-title { display: none !important; }

/* ── Main panel postbox: strip WP chrome ── */
body.ttbm-modern-edit-page #ttbm_meta_box_panel {
	border: none !important;
	box-shadow: none !important;
	background: transparent !important;
	margin-top: 0 !important;
}
body.ttbm-modern-edit-page #ttbm_meta_box_panel > .postbox-header { display: none !important; }
body.ttbm-modern-edit-page #ttbm_meta_box_panel > .inside { margin: 0 !important; padding: 0 !important; }

/* ── Tab layout ── */
body.ttbm-modern-edit-page #ttbm_meta_box_panel .ttbm_configuration { padding: 0 !important; }
body.ttbm-modern-edit-page #ttbm_meta_box_panel .ttbm_settings { background: transparent !important; }
body.ttbm-modern-edit-page #ttbm_meta_box_panel .ttbmTabs.leftTabs {
	gap: 0 !important;
	align-items: flex-start !important;
	min-height: calc(100vh - 120px) !important;
}

/* ── Left tab navigation ── */
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar {
	background: #eef3fa !important;
	border-right: 1px solid #dce4f0 !important;
	border-radius: 0 0 20px 0 !important;
	min-height: calc(100vh - 120px) !important;
	padding: 16px 12px !important;
	box-shadow: none !important;
	overflow-y: auto !important;
	scrollbar-width: thin !important;
	scrollbar-color: #c5d0e3 transparent !important;
}

/* Nav items */
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar li {
	display: flex !important;
	align-items: center !important;
	gap: 12px !important;
	margin: 4px 0 !important;
	padding: 11px 14px !important;
	border-radius: 8px !important;
	font-size: 14px !important;
	font-weight: 500 !important;
	color: #3d4f6f !important;
	background: transparent !important;
	border: none !important;
	cursor: pointer !important;
	transition: background .15s ease, color .15s ease !important;
	position: relative !important;
	list-style: none !important;
}

/* Hover state */
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar li:hover {
	background: #e3ebf7 !important;
	color: #2c3e5c !important;
	transform: none !important;
	box-shadow: none !important;
}

/* Active state */
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar li.active {
	background: #2271b1 !important;
	color: #fff !important;
	font-weight: 600 !important;
	box-shadow: none !important;
	transform: none !important;
}

body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar li.active::before,
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar li.active::after {
	display: none !important;
	content: none !important;
}

/* Icons */
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar li i {
	font-size: 16px !important;
	width: 18px !important;
	text-align: center !important;
	flex-shrink: 0 !important;
	color: inherit !important;
	transition: color .15s ease !important;
	margin: 0 !important;
}

body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar li.active i,
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar li.active span {
	color: #fff !important;
}

/* Label */
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar li span {
	color: inherit !important;
	font-size: inherit !important;
	font-weight: inherit !important;
	transition: color .15s ease !important;
	white-space: nowrap !important;
	overflow: hidden !important;
	text-overflow: ellipsis !important;
}

body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar li::after {
	display: none !important;
	content: none !important;
}

/* Override global [data-tabs-target] tab skin on tour + hotel sidebar */
body.ttbm-modern-edit-page #ttbm_meta_box_panel .ttbm_style .tabLists.meta-sidebar [data-tabs-target],
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar li[data-tabs-target] {
	background: transparent !important;
	color: #3d4f6f !important;
	border: none !important;
	border-bottom: none !important;
	box-shadow: none !important;
	transform: none !important;
}

body.ttbm-modern-edit-page #ttbm_meta_box_panel .ttbm_style .tabLists.meta-sidebar [data-tabs-target]:hover,
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar li[data-tabs-target]:hover {
	background: #e3ebf7 !important;
	color: #2c3e5c !important;
}

body.ttbm-modern-edit-page #ttbm_meta_box_panel .ttbm_style .tabLists.meta-sidebar [data-tabs-target].active,
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar li[data-tabs-target].active {
	background: #2271b1 !important;
	color: #fff !important;
	font-weight: 600 !important;
}

body.ttbm-modern-edit-page #ttbm_meta_box_panel .ttbm_style .tabLists.meta-sidebar [data-tabs-target] i,
body.ttbm-modern-edit-page #ttbm_meta_box_panel .ttbm_style .tabLists.meta-sidebar [data-tabs-target] span,
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar li[data-tabs-target] i,
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar li[data-tabs-target] span {
	color: inherit !important;
	margin: 0 !important;
}

body.ttbm-modern-edit-page #ttbm_meta_box_panel .ttbm_style .tabLists.meta-sidebar [data-tabs-target].active i,
body.ttbm-modern-edit-page #ttbm_meta_box_panel .ttbm_style .tabLists.meta-sidebar [data-tabs-target].active span,
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar li[data-tabs-target].active i,
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabLists.meta-sidebar li[data-tabs-target].active span {
	color: #fff !important;
}

body.ttbm-modern-edit-page #ttbm_meta_box_panel .ttbm_style.leftTabs > .tabLists.meta-sidebar,
body.ttbm-modern-edit-page #ttbm_meta_box_panel .leftTabs > .tabLists.meta-sidebar {
	background: #eef3fa !important;
	border-right: 1px solid #dce4f0 !important;
}

/* Sidebar toggle button */
body.ttbm-modern-edit-page #ttbm_meta_box_panel .meta-sidebar-toggle {
	display: none !important;
}

/* ── Tab content panel ── */
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabsContent {
	flex: 1 !important;
	min-width: 0 !important;
	background: #fff !important;
	padding: 20px !important;
}

/* ── Section cards ── */
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabsItem > h2 {
	font-size: 18px !important;
	font-weight: 700 !important;
	color: #111827 !important;
	margin: 0 0 6px !important;
}
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabsItem > p {
	color: #6b7280 !important;
	font-size: 13px !important;
	margin-bottom: 18px !important;
}
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabsItem section {
	border-radius: 10px !important;
	background: #fff !important;
	box-shadow: 0 1px 6px rgba(0,0,0,.07) !important;
	padding: 20px 24px !important;
	margin-bottom: 16px !important;
}
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabsItem section .ttbm-header h4 {
	font-size: 14px !important;
	font-weight: 600 !important;
	color: #111827 !important;
}
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabsItem section .ttbm-header h4 i { color: #2271b1 !important; }
body.ttbm-modern-edit-page #ttbm_meta_box_panel .tabsItem section .ttbm-header { border-bottom-color: #f3f4f6 !important; }

/* ── Right sidebar column ── */
body.ttbm-modern-edit-page #ttbm_meta_box_panel .ttbm-right-sidebar {
	background: #fff !important;
	padding: 20px 16px 20px 0 !important;
}

/* ── Hide sidebar publish card ── */
body.ttbm-modern-edit-page .ttbm-sb-publish-card { display: none !important; }

/* =====================================================
   TTBM Right Sidebar
   ===================================================== */

/* Sidebar column */
.ttbm-right-sidebar {
	width: 272px !important;
	min-width: 272px !important;
	flex-shrink: 0 !important;
	padding: 20px 0 20px 16px !important;
	box-sizing: border-box !important;
	display: flex !important;
	flex-direction: column !important;
	gap: 12px !important;
}

/* Card base */
.ttbm-sb-card {
	background: #fff;
	border: 1px solid #ddd;
	border-radius: 10px;
	box-shadow: 0 1px 8px rgba(0,0,0,.09);
	padding: 16px;
	box-sizing: border-box;
}

/* Publish card — no background */
.ttbm-sb-publish-card {
	background: transparent !important;
	box-shadow: none !important;
	padding: 4px 0 !important;
}

/* Card title */
.ttbm-sb-card p.ttbm-sb-card-title {
	font-size: 14px !important;
	font-weight: 700 !important;
	color: #111827 !important;
	margin: 0 0 12px 0 !important;
	padding: 0 0 10px 0 !important;
	border-bottom: 1px solid #f3f4f6 !important;
	line-height: 1.3 !important;
}

/* ── Publish buttons ── */
.ttbm-sb-btn-row {
	display: flex !important;
	align-items: center !important;
	justify-content: space-between !important;
}
.ttbm-sb-btn-preview {
	font-size: 13px !important;
	font-weight: 500 !important;
	color: #6b7280 !important;
	text-decoration: none !important;
	background: none !important;
	border: none !important;
	padding: 0 !important;
	cursor: pointer !important;
	box-shadow: none !important;
}
.ttbm-sb-btn-preview:hover { color: #111827 !important; text-decoration: underline !important; }
.ttbm-sb-btn-publish {
	font-size: 13px !important;
	font-weight: 600 !important;
	color: #fff !important;
	background: #2271b1 !important;
	border: none !important;
	padding: 9px 20px !important;
	border-radius: 8px !important;
	cursor: pointer !important;
	line-height: 1.4 !important;
	box-shadow: none !important;
}
.ttbm-sb-btn-publish:hover { background: #135e96 !important; }

/* ── Featured image ── */
.ttbm-sb-upload-area {
	border: 2px dashed #d1d5db !important;
	border-radius: 10px !important;
	padding: 20px 12px 16px !important;
	text-align: center !important;
	cursor: pointer !important;
	background: #f9fafb !important;
	margin-bottom: 12px !important;
	transition: border-color .2s !important;
}
.ttbm-sb-upload-area:hover { border-color: #2271b1 !important; background: #eff6ff !important; }
.ttbm-sb-upload-icon { font-size: 28px !important; color: #2271b1 !important; display: block !important; margin-bottom: 8px !important; }
.ttbm-sb-upload-area > p { font-size: 12px !important; color: #374151 !important; font-weight: 500 !important; margin: 0 0 3px 0 !important; line-height: 1.5 !important; }
.ttbm-sb-upload-area > span { font-size: 11px !important; color: #9ca3af !important; display: block !important; }
.ttbm-sb-thumb-preview { width: 100% !important; height: 150px !important; object-fit: cover !important; border-radius: 8px !important; display: block !important; margin-bottom: 10px !important; }
.ttbm-sb-img-actions { display: flex !important; gap: 14px !important; }
.ttbm-sb-img-actions a { font-size: 12px !important; font-weight: 500 !important; color: #2271b1 !important; cursor: pointer !important; text-decoration: none !important; }
.ttbm-sb-img-actions a:hover { text-decoration: underline !important; }
.ttbm-sb-img-actions a.ttbm-sb-remove { color: #dc2626 !important; }

/* ── Taxonomy list — override ALL WP admin defaults ── */
.ttbm-sb-tax-list { max-height: 196px !important; overflow-y: auto !important; margin: 0 0 10px 0 !important; padding: 0 !important; }
.ttbm-sb-tax-list ul { margin: 0 !important; padding: 0 !important; list-style: none !important; border: none !important; background: none !important; }
.ttbm-sb-tax-list ul.children { padding-left: 14px !important; }
.ttbm-sb-tax-list li { margin: 0 !important; padding: 5px 0 !important; list-style: none !important; border: none !important; background: none !important; float: none !important; }
.ttbm-sb-tax-list li label.selectit,
.ttbm-sb-tax-list li label {
	display: flex !important;
	align-items: center !important;
	gap: 8px !important;
	font-size: 13px !important;
	font-weight: 400 !important;
	color: #374151 !important;
	cursor: pointer !important;
	margin: 0 !important;
	padding: 0 !important;
	text-indent: 0 !important;
	background: none !important;
	box-shadow: none !important;
	line-height: 1.5 !important;
}
/* Custom styled checkbox */
.ttbm-sb-tax-list li input[type="checkbox"] {
	-webkit-appearance: none !important;
	-moz-appearance: none !important;
	appearance: none !important;
	width: 16px !important;
	height: 16px !important;
	min-width: 16px !important;
	border: 1.5px solid #d1d5db !important;
	border-radius: 4px !important;
	background: #fff !important;
	cursor: pointer !important;
	flex-shrink: 0 !important;
	margin: 0 !important;
	padding: 0 !important;
	float: none !important;
	vertical-align: middle !important;
	box-shadow: none !important;
	outline: none !important;
}
.ttbm-sb-tax-list li input[type="checkbox"]:checked {
	background-color: #2271b1 !important;
	border-color: #2271b1 !important;
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fff'%3E%3Cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3E%3C/svg%3E") !important;
	background-size: 11px !important;
	background-repeat: no-repeat !important;
	background-position: center !important;
}

/* ── Add-new link ── */
.ttbm-sb-add-link { font-size: 12px !important; font-weight: 500 !important; color: #2271b1 !important; cursor: pointer !important; text-decoration: none !important; display: inline-block !important; }
.ttbm-sb-add-link:hover { text-decoration: underline !important; }

/* ── Add-new form — hidden until toggled ── */
.ttbm-sb-add-new-form { display: none !important; margin-top: 10px !important; padding-top: 10px !important; border-top: 1px solid #f3f4f6 !important; }
.ttbm-sb-add-new-form.ttbm-sb-open { display: block !important; }
.ttbm-sb-add-new-form input[type="text"] {
	width: 100% !important;
	padding: 8px 10px !important;
	border: 1px solid #d1d5db !important;
	border-radius: 6px !important;
	font-size: 13px !important;
	margin: 0 0 8px 0 !important;
	box-sizing: border-box !important;
	outline: none !important;
	box-shadow: none !important;
	background: #fff !important;
	color: #111827 !important;
	display: block !important;
}
.ttbm-sb-add-new-form input[type="text"]:focus { border-color: #2271b1 !important; box-shadow: 0 0 0 2px rgba(34,113,177,.15) !important; }
.ttbm-sb-form-actions { display: flex !important; align-items: center !important; gap: 6px !important; }
.ttbm-sb-submit-term { background: #2271b1 !important; color: #fff !important; border: none !important; padding: 7px 16px !important; border-radius: 6px !important; font-size: 12px !important; font-weight: 600 !important; cursor: pointer !important; line-height: 1.4 !important; box-shadow: none !important; }
.ttbm-sb-submit-term:hover { background: #135e96 !important; }
.ttbm-sb-cancel-term { background: none !important; border: none !important; color: #9ca3af !important; font-size: 12px !important; cursor: pointer !important; padding: 7px 4px !important; line-height: 1.4 !important; box-shadow: none !important; }
.ttbm-sb-cancel-term:hover { color: #374151 !important; }

/* ── Custom page header bar ── */
.ttbm-admin-page-header {
	position: sticky !important;
	top: 32px !important;
	z-index: 999 !important;
	background: #fff !important;
	border-bottom: 1px solid #e5e7eb !important;
	box-shadow: 0 2px 8px rgba(0,0,0,.06) !important;
	display: flex !important;
	align-items: center !important;
	justify-content: space-between !important;
	padding: 12px 24px !important;
	margin: 0 !important;
	width: 100% !important;
	box-sizing: border-box !important;
}
/* ── Header left: back link ── */
.ttbm-header-left {
	display: flex !important;
	align-items: center !important;
	flex: 1 !important;
	min-width: 0 !important;
}
.ttbm-header-back {
	display: inline-flex !important;
	align-items: center !important;
	gap: 6px !important;
	font-size: 13px !important;
	font-weight: 500 !important;
	color: #2271b1 !important;
	text-decoration: none !important;
	white-space: nowrap !important;
}
.ttbm-header-back:hover { color: #135e96 !important; text-decoration: underline !important; }
.ttbm-header-back svg { flex-shrink: 0 !important; }

/* ── Center title ── */
.ttbm-header-tour-title {
	position: absolute !important;
	left: 50% !important;
	top: 50% !important;
	transform: translate(-50%, -50%) !important;
	font-size: 17px !important;
	font-weight: 700 !important;
	color: #111827 !important;
	white-space: nowrap !important;
	overflow: hidden !important;
	text-overflow: ellipsis !important;
	max-width: 44% !important;
	text-align: center !important;
	line-height: 1.3 !important;
	margin: 0 !important;
	padding: 0 !important;
	background: none !important;
	border: none !important;
	letter-spacing: -.01em !important;
}

/* ── Header right: outlined preview + solid publish ── */
.ttbm-header-right {
	display: flex !important;
	align-items: center !important;
	gap: 8px !important;
	flex-shrink: 0 !important;
	flex: 1 !important;
	justify-content: flex-end !important;
}
.ttbm-header-preview {
	display: inline-flex !important;
	align-items: center !important;
	gap: 6px !important;
	font-size: 13px !important;
	font-weight: 500 !important;
	color: #374151 !important;
	background: #fff !important;
	border: 1px solid #d1d5db !important;
	border-radius: 7px !important;
	padding: 8px 16px !important;
	text-decoration: none !important;
	cursor: pointer !important;
	line-height: 1.4 !important;
	white-space: nowrap !important;
}
.ttbm-header-preview:hover {
	background: #f9fafb !important;
	border-color: #9ca3af !important;
	color: #111827 !important;
	text-decoration: none !important;
}
.ttbm-header-publish {
	font-size: 13px !important;
	font-weight: 600 !important;
	color: #fff !important;
	background: #2271b1 !important;
	border: none !important;
	padding: 9px 22px !important;
	border-radius: 7px !important;
	cursor: pointer !important;
	line-height: 1.4 !important;
	white-space: nowrap !important;
	box-shadow: none !important;
}
.ttbm-header-publish:hover { background: #135e96 !important; }

/* Hide original WP title / action / old badge — scoped to tour page */
body.ttbm-modern-edit-page .wrap > h1.wp-heading-inline,
body.ttbm-modern-edit-page .wrap > .page-title-action,
body.ttbm-modern-edit-page .ttbm-page-tour-title { display: none !important; }
</style>
<script>
jQuery(function($){
	var pageTitle   = <?php echo wp_json_encode($page_title); ?>;
	var isHotelEdit = <?php echo $is_hotel ? 'true' : 'false'; ?>;
	var isPublished = <?php echo ($post_id && get_post_status($post_id) === 'publish') ? 'true' : 'false'; ?>;
	var previewUrl  = <?php echo wp_json_encode($post_id ? get_preview_post_link(get_post($post_id)) : '#'); ?>;
	var btnLabel    = isPublished ? <?php echo wp_json_encode(__('Update Post', 'tour-booking-manager')); ?> : <?php echo wp_json_encode(__('Publish', 'tour-booking-manager')); ?>;

	var backUrl   = <?php echo wp_json_encode($back_url); ?>;
	var backLabel = <?php echo wp_json_encode($back_label); ?>;

	var arrowSvg = '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	var eyeSvg   = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

	var header = $(
		'<div class="ttbm-admin-page-header" id="ttbm-admin-page-header" style="position:relative;">' +
			'<div class="ttbm-header-left">' +
				'<a class="ttbm-header-back"></a>' +
			'</div>' +
			'<h1 class="ttbm-header-tour-title"></h1>' +
			'<div class="ttbm-header-right">' +
				'<a class="ttbm-header-preview" target="_blank"></a>' +
				'<button type="submit" name="publish" value="publish" class="ttbm-header-publish"></button>' +
			'</div>' +
		'</div>'
	);

	header.find('.ttbm-header-back').attr('href', backUrl).html(arrowSvg + backLabel);
	header.find('.ttbm-header-tour-title').text(pageTitle || '');
	header.find('.ttbm-header-preview').attr('href', previewUrl).html(eyeSvg + <?php echo wp_json_encode(__('Preview', 'tour-booking-manager')); ?>);
	header.find('.ttbm-header-publish').text(btnLabel);

	/* Inject inside form#post so the submit button is part of the form */
	$('form#post').prepend(header);

	/* Keep header title in sync with the title input */
	$(document).on('input', '#ttbm_post_title', function(){
		$('.ttbm-header-tour-title').text($(this).val().trim());
	});

	/* ── Location required validation (tour only) ── */
	if (!isHotelEdit) {
	window.ttbmSyncLocationRequiredState = function () {
		var $toggle = $('#ttbm_meta_box_panel input[name="ttbm_display_location"]');
		var locationEnabled = $toggle.length > 0 && $toggle.is(':checked');
		var $mark = $('.ttbm-location-required-mark');
		var $select = $('#ttbm_location_select');
		var $err = $('#ttbm_location_error');

		if (locationEnabled) {
			$mark.show();
		} else {
			$mark.hide();
			$err.hide();
			if ($select.length) {
				$select.css({'border-color': '', 'box-shadow': ''});
			}
		}
		return locationEnabled;
	};

	function ttbmValidateLocation() {
		if (!window.ttbmSyncLocationRequiredState()) {
			return true;
		}

		var $select = $('#ttbm_location_select');
		var $err = $('#ttbm_location_error');
		if (!$select.length) {
			return true;
		}

		if (!$select.val()) {
			$select.css({'border-color': '#dc2626', 'box-shadow': '0 0 0 2px rgba(220,38,38,.15)'});
			$err.show();
			if (typeof window.ttbmSetValidationFocus === 'function') {
				window.ttbmSetValidationFocus($select, '[data-tabs-target="#ttbm_settings_location"]');
			}
			return false;
		}
		$select.css({'border-color': '', 'box-shadow': ''});
		$err.hide();
		return true;
	}

	window.ttbmSyncLocationRequiredState();
	window.ttbmValidateLocation = ttbmValidateLocation;

	$(document).on('change', '#ttbm_location_select', function(){
		if ($(this).val()) {
			$(this).css({'border-color':'','box-shadow':''});
			$('#ttbm_location_error, #ttbm_hotel_location_error').hide();
		}
	});
	}

	/* ── Single consolidated submit interceptor ── */
	$(document).on('click', '.ttbm-header-publish, [name="publish"], [name="save"]', function(e){
		if (typeof ttbm_sync_visible_dates_to_hidden === 'function') {
			ttbm_sync_visible_dates_to_hidden();
		}
		if (typeof window.ttbmPrepareTourSettingsFormForSubmit === 'function') {
			window.ttbmPrepareTourSettingsFormForSubmit();
		}
		if (typeof window.ttbmValidateSettingsFormBeforeSubmit === 'function') {
			if (!window.ttbmValidateSettingsFormBeforeSubmit()) {
				e.preventDefault();
				if (typeof window.ttbmFocusValidationTarget === 'function') {
					window.ttbmFocusValidationTarget();
				}
				return false;
			}
		}
	});
});</script>
			<?php
		}

		public function output_skeleton_loader() {
			if (!$this->is_modern_edit_screen()) {
				return;
			}
			?>
<div id="ttbm-page-loader" role="status" aria-label="<?php esc_attr_e('Loading…', 'tour-booking-manager'); ?>">

	<!-- Header skeleton -->
	<div class="ttbm-sk-header">
		<div class="ttbm-sk-header-left">
			<div class="ttbm-sk"></div>
		</div>
		<div class="ttbm-sk-header-center">
			<div class="ttbm-sk"></div>
		</div>
		<div class="ttbm-sk-header-right">
			<div class="ttbm-sk"></div>
			<div class="ttbm-sk"></div>
		</div>
	</div>

	<!-- Body skeleton -->
	<div class="ttbm-sk-body">

		<!-- Left tab nav -->
		<div class="ttbm-sk-tabs">
			<?php for ($i = 0; $i < 14; $i++) : ?>
			<div class="ttbm-sk-tab-item">
				<div class="ttbm-sk ttbm-sk-tab-icon"></div>
				<div class="ttbm-sk ttbm-sk-tab-label" style="width:<?php echo esc_attr((55 + ($i % 4) * 12) . '%'); ?>"></div>
			</div>
			<?php endfor; ?>
		</div>

		<!-- Main content -->
		<div class="ttbm-sk-content">

			<!-- Title & Content card -->
			<div class="ttbm-sk-card">
				<div class="ttbm-sk ttbm-sk-card-title" style="width:30%"></div>
				<div class="ttbm-sk ttbm-sk-row" style="display:block;height:38px;border-radius:6px;"></div>
				<div class="ttbm-sk ttbm-sk-editor"></div>
			</div>

			<!-- General Info card -->
			<div class="ttbm-sk-card">
				<div class="ttbm-sk ttbm-sk-card-title" style="width:35%"></div>
				<div class="ttbm-sk-row">
					<div class="ttbm-sk" style="height:38px;flex:1;border-radius:6px;"></div>
					<div class="ttbm-sk" style="height:38px;flex:1;border-radius:6px;"></div>
				</div>
				<div class="ttbm-sk-row">
					<div class="ttbm-sk" style="height:38px;flex:1;border-radius:6px;"></div>
					<div class="ttbm-sk" style="height:38px;flex:1;border-radius:6px;"></div>
				</div>
				<div class="ttbm-sk-row">
					<div class="ttbm-sk" style="height:38px;flex:1;border-radius:6px;"></div>
					<div class="ttbm-sk" style="height:38px;flex:1;border-radius:6px;"></div>
				</div>
			</div>

		</div>

		<!-- Right sidebar -->
		<div class="ttbm-sk-sidebar">

			<!-- Featured image card -->
			<div class="ttbm-sk-sb-card">
				<div class="ttbm-sk ttbm-sk-sb-title"></div>
				<div class="ttbm-sk ttbm-sk-sb-img"></div>
				<div class="ttbm-sk" style="height:10px;width:50%;border-radius:4px;"></div>
			</div>

			<!-- Category card -->
			<div class="ttbm-sk-sb-card">
				<div class="ttbm-sk ttbm-sk-sb-title"></div>
				<?php for ($i = 0; $i < 4; $i++) : ?>
				<div class="ttbm-sk-cb-row">
					<div class="ttbm-sk ttbm-sk-cb"></div>
					<div class="ttbm-sk ttbm-sk-cb-label" style="width:<?php echo esc_attr((50 + $i * 12) . 'px'); ?>"></div>
				</div>
				<?php endfor; ?>
			</div>

			<!-- Organizer card -->
			<div class="ttbm-sk-sb-card">
				<div class="ttbm-sk ttbm-sk-sb-title"></div>
				<?php for ($i = 0; $i < 3; $i++) : ?>
				<div class="ttbm-sk-cb-row">
					<div class="ttbm-sk ttbm-sk-cb"></div>
					<div class="ttbm-sk ttbm-sk-cb-label" style="width:<?php echo esc_attr((60 + $i * 15) . 'px'); ?>"></div>
				</div>
				<?php endfor; ?>
			</div>

		</div>
	</div><!-- /.ttbm-sk-body -->
</div><!-- /#ttbm-page-loader -->
<script>
(function(){
	function ttbmHideLoader(){
		var el = document.getElementById('ttbm-page-loader');
		if (!el) return;
		el.style.transition = 'opacity .35s ease';
		el.style.opacity    = '0';
		setTimeout(function(){ el && el.parentNode && el.parentNode.removeChild(el); }, 380);
	}
	if (document.readyState === 'complete' || document.readyState === 'interactive') {
		setTimeout(ttbmHideLoader, 120);
	} else {
		window.addEventListener('DOMContentLoaded', function(){ setTimeout(ttbmHideLoader, 120); });
	}
	/* Fallback: force-hide after 4 seconds if anything hangs */
	setTimeout(ttbmHideLoader, 4000);
})();
</script>
			<?php
		}

		public function remove_default_side_boxes() {
			$cpt = TTBM_Function::get_cpt_name();
			remove_meta_box('submitdiv',             $cpt, 'side');
			remove_meta_box('postimagediv',          $cpt, 'side');
			remove_meta_box('ttbm_tour_catdiv',      $cpt, 'side');
			remove_meta_box('ttbm_tour_orgdiv',      $cpt, 'side');
			remove_meta_box('tagsdiv-ttbm_tour_tag', $cpt, 'side');
			remove_meta_box('postexcerpt',           $cpt, 'normal');
			remove_meta_box('submitdiv',             'ttbm_hotel', 'side');
			remove_meta_box('postimagediv',          'ttbm_hotel', 'side');
			remove_meta_box('postexcerpt',           'ttbm_hotel', 'normal');
		}

		public function enqueue_assets($hook) {
			if (!$this->is_modern_edit_hook($hook)) {
				return;
			}

			wp_enqueue_media();

			// Load sidebar CSS as a proper standalone stylesheet
			$css_file = TTBM_PLUGIN_DIR . '/assets/admin/ttbm_sidebar.css';
			$css_url  = TTBM_PLUGIN_URL . '/assets/admin/ttbm_sidebar.css';
			$version  = file_exists($css_file) ? filemtime($css_file) : TTBM_PLUGIN_VERSION;
			wp_enqueue_style('ttbm_sidebar', $css_url, ['ttbm_admin'], $version);

			// JS for add-new-term toggle + AJAX
			wp_add_inline_script('jquery', '
				jQuery(function($){
					$(document).on("click", ".ttbm-sb-add-link", function(e){
						e.preventDefault();
						$(this).closest(".ttbm-sb-card").find(".ttbm-sb-add-new-form").addClass("ttbm-sb-open");
						$(this).hide();
					});
					$(document).on("click", ".ttbm-sb-cancel-term", function(){
						var card = $(this).closest(".ttbm-sb-card");
						card.find(".ttbm-sb-add-new-form").removeClass("ttbm-sb-open");
						card.find(".ttbm-sb-add-link").show();
						card.find(".ttbm-sb-add-new-form input[type=text]").val("");
					});
					$(document).on("click", ".ttbm-sb-submit-term", function(){
						var card     = $(this).closest(".ttbm-sb-card");
						var taxonomy = card.data("taxonomy");
						var nonce    = card.data("nonce");
						var input    = card.find(".ttbm-sb-add-new-form input[type=text]");
						var name     = input.val().trim();
						if (!name) { input.focus(); return; }
						$(this).prop("disabled", true);
						$.post(ajaxurl, {
							action:    "ttbm_add_term",
							taxonomy:  taxonomy,
							term_name: name,
							nonce:     nonce
						}, function(res){
							card.find(".ttbm-sb-submit-term").prop("disabled", false);
							if (!res.success) { alert(res.data.message); return; }
							var li = \'<li><label class="selectit"><input type="checkbox" name="tax_input[\' + taxonomy + \'][]" value="\' + res.data.term_id + \'" checked> \' + res.data.name + \'</label></li>\';
							card.find(".ttbm-sb-tax-list ul:first").append(li);
							input.val("");
							card.find(".ttbm-sb-add-new-form").removeClass("ttbm-sb-open");
							card.find(".ttbm-sb-add-link").show();
						});
					});
				});
			');
		}

		public function render_featured_sidebar($tour_id) {
			$post = get_post($tour_id);
			if (!$post) {
				return;
			}
			$this->render_featured_image_section($post);
		}

		public function render_taxonomy_sidebar($tour_id) {
			$post = get_post($tour_id);
			if (!$post) {
				return;
			}
			$this->render_category_section($post);
			$this->render_organizer_section($post);
		}

		/* ── Publish ── */
		private function render_publish_section($post) {
			$is_published = get_post_status($post->ID) === 'publish';
			$preview_url  = get_preview_post_link($post);
			$btn_label    = $is_published
				? esc_html__('Update Post', 'tour-booking-manager')
				: esc_html__('Publish', 'tour-booking-manager');
			?>
			<div class="ttbm-sb-card ttbm-sb-publish-card">
				<div class="ttbm-sb-btn-row">
					<a href="<?php echo esc_url($preview_url); ?>" target="_blank" class="ttbm-sb-btn-preview">
						<?php esc_html_e('Preview', 'tour-booking-manager'); ?>
					</a>
					<button type="submit" name="publish" value="publish" class="ttbm-sb-btn-publish">
						<?php echo esc_html($btn_label); ?>
					</button>
				</div>
			</div>
			<?php
		}

		/* ── Featured Image ── */
		private function render_featured_image_section($post) {
			$thumb_id  = get_post_thumbnail_id($post->ID);
			$thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'large') : '';
			?>
			<div class="ttbm-sb-card" id="ttbm_featured_image_card">
				<p class="ttbm-sb-card-title">
					<?php esc_html_e('Featured Image', 'tour-booking-manager'); ?>
					<span style="color:#dc2626;font-weight:700;margin-left:3px;" title="<?php esc_attr_e('Required', 'tour-booking-manager'); ?>">*</span>
				</p>

				<div id="ttbm_upload_area" class="ttbm-sb-upload-area"<?php echo $thumb_url ? ' style="display:none;"' : ''; ?>>
					<div class="ttbm-sb-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
					<p><?php esc_html_e('Click to upload or drag & drop', 'tour-booking-manager'); ?></p>
					<span><?php esc_html_e('PNG, JPG or WebP (max. 5MB)', 'tour-booking-manager'); ?></span>
				</div>

				<input type="hidden" name="_thumbnail_id" id="ttbm_thumb_id" value="<?php echo esc_attr($thumb_id ?: -1); ?>">

				<?php if ($thumb_url) : ?>
				<img src="<?php echo esc_url($thumb_url); ?>" id="ttbm_thumb_preview" class="ttbm-sb-thumb-preview" alt="">
				<?php endif; ?>

				<div id="ttbm_img_actions_wrap" class="ttbm-sb-img-actions"<?php echo $thumb_url ? '' : ' style="display:none;"'; ?>>
					<a id="ttbm_change_thumb"><?php esc_html_e('Change image', 'tour-booking-manager'); ?></a>
					<a id="ttbm_remove_thumb" class="ttbm-sb-remove"><?php esc_html_e('Remove', 'tour-booking-manager'); ?></a>
				</div>
				<p id="ttbm_featured_image_error" style="display:none;color:#dc2626;font-size:12px;font-weight:500;margin:8px 0 0;">
					<span style="margin-right:4px;">&#9888;</span><?php esc_html_e('Please upload a featured image before saving.', 'tour-booking-manager'); ?>
				</p>
			</div>
			<script>
			(function($){
				var ttbmFrame;

				function openMedia(){
					if (ttbmFrame){ ttbmFrame.open(); return; }
					ttbmFrame = wp.media({
						title:    '<?php echo esc_js(__('Select Featured Image', 'tour-booking-manager')); ?>',
						button:   { text: '<?php echo esc_js(__('Use this image', 'tour-booking-manager')); ?>' },
						multiple: false
					});
					ttbmFrame.on('select', function(){
						var att = ttbmFrame.state().get('selection').first().toJSON();
						var url = att.sizes && att.sizes.large ? att.sizes.large.url : att.url;
						$('#ttbm_thumb_id').val(att.id);
						if ($('#ttbm_thumb_preview').length) {
							$('#ttbm_thumb_preview').attr('src', url);
						} else {
							$('#ttbm_upload_area').after('<img src="' + url + '" id="ttbm_thumb_preview" class="ttbm-sb-thumb-preview" alt="">');
						}
						$('#ttbm_upload_area').hide();
						$('#ttbm_img_actions_wrap').show();
						$('#ttbm_featured_image_error').hide();
						$('#ttbm_upload_area, #ttbm_featured_image_card').css({'border-color':'','box-shadow':''});
					});
					ttbmFrame.open();
				}

				$(document).on('click', '#ttbm_upload_area, #ttbm_change_thumb', function(e){
					e.preventDefault(); openMedia();
				});
				$(document).on('click', '#ttbm_remove_thumb', function(e){
					e.preventDefault();
					$('#ttbm_thumb_id').val(-1);
					$('#ttbm_thumb_preview').remove();
					$('#ttbm_img_actions_wrap').hide();
					$('#ttbm_upload_area').show();
					ttbmFrame = null;
				});
			})(jQuery);
			</script>
			<?php
		}

		/* ── Category ── */
		private function render_category_section($post) {
			$this->render_taxonomy_section($post, 'ttbm_tour_cat', 'fas fa-folder-open',
				esc_html__('Tour Category', 'tour-booking-manager'),
				esc_html__('+ Add new category', 'tour-booking-manager'),
				'ttbm_new_cat');
		}

		/* ── Organizer ── */
		private function render_organizer_section($post) {
			$tax_obj = get_taxonomy('ttbm_tour_org');
			$label   = $tax_obj ? $tax_obj->labels->name : esc_html__('Organizer', 'tour-booking-manager');
			$this->render_taxonomy_section($post, 'ttbm_tour_org', 'fas fa-user-tie',
				$label,
				esc_html__('+ Add new organizer', 'tour-booking-manager'),
				'ttbm_new_org');
		}

		private function render_taxonomy_section($post, $taxonomy, $icon, $title, $add_label, $input_id) {
			$tax_obj = get_taxonomy($taxonomy);
			if (!$tax_obj) return;
			$nonce = wp_create_nonce('ttbm_add_term_' . $taxonomy);
			?>
			<div class="ttbm-sb-card" data-taxonomy="<?php echo esc_attr($taxonomy); ?>" data-nonce="<?php echo esc_attr($nonce); ?>">
				<p class="ttbm-sb-card-title"><?php echo esc_html($title); ?></p>
				<div class="ttbm-sb-tax-list">
					<ul><?php wp_terms_checklist($post->ID, ['taxonomy' => $taxonomy, 'checked_ontop' => false]); ?></ul>
				</div>
				<a class="ttbm-sb-add-link"><?php echo esc_html($add_label); ?></a>
				<div class="ttbm-sb-add-new-form">
					<input type="text" id="<?php echo esc_attr($input_id); ?>"
						placeholder="<?php echo esc_attr(sprintf(__('%s name', 'tour-booking-manager'), $tax_obj->labels->singular_name)); ?>">
					<div class="ttbm-sb-form-actions">
						<button type="button" class="ttbm-sb-submit-term"><?php esc_html_e('Add', 'tour-booking-manager'); ?></button>
						<button type="button" class="ttbm-sb-cancel-term"><?php esc_html_e('Cancel', 'tour-booking-manager'); ?></button>
					</div>
				</div>
			</div>
			<?php
		}
	}

	/* AJAX: add new term */
	add_action('wp_ajax_ttbm_add_term', function () {
		$taxonomy = isset($_POST['taxonomy'])  ? sanitize_key(wp_unslash($_POST['taxonomy']))         : '';
		$name     = isset($_POST['term_name']) ? sanitize_text_field(wp_unslash($_POST['term_name'])) : '';
		$nonce    = isset($_POST['nonce'])     ? sanitize_text_field(wp_unslash($_POST['nonce']))     : '';

		if (!$taxonomy || !$name) {
			wp_send_json_error(['message' => __('Name is required.', 'tour-booking-manager')]);
		}
		if (!wp_verify_nonce($nonce, 'ttbm_add_term_' . $taxonomy)) {
			wp_send_json_error(['message' => __('Security check failed.', 'tour-booking-manager')]);
		}
		if (!current_user_can('manage_categories')) {
			wp_send_json_error(['message' => __('Permission denied.', 'tour-booking-manager')]);
		}
		$result = wp_insert_term($name, $taxonomy);
		if (is_wp_error($result)) {
			wp_send_json_error(['message' => $result->get_error_message()]);
		}
		$term = get_term($result['term_id'], $taxonomy);
		wp_send_json_success(['term_id' => $result['term_id'], 'name' => $term->name, 'slug' => $term->slug]);
	});

	new TTBM_Settings_Sidebar();
}
