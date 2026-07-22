# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

**Travelly / Tour & Travel Booking Manager for WooCommerce** (`tour-booking-manager`) — a WordPress plugin (not a standalone app) that adds tour and hotel booking to a WooCommerce site. Plain PHP, procedural-OOP style, no autoloader/framework, no build step for the plugin itself except the optional Gutenberg blocks bundle. Requires PHP 7.0+, WordPress 4.4+, and an active WooCommerce install for most features to load at all.

Main plugin file: [tour-booking-manager.php](tour-booking-manager.php). Current version: 2.1.9 (also check [readme.txt](readme.txt) changelog — it's the canonical version/changelog source, not this file).

## Running and testing

There is no PHPUnit/PHPCS/composer setup in this repo — verification is done against a live WordPress install. Use the **`run-tour-booking-manager`** skill (`.claude/skills/run-tour-booking-manager/`) to drive it:

- `.claude/skills/run-tour-booking-manager/driver.sh` — curl-based smoke test of the tour-list shortcode pages against the running site (default `http://localhost/tour`, Apache on :80, DB `tour`). Checks HTTP 200, no PHP fatal, and counts rendered `.filter_item` cards. No browser is available in this container — this is the primary verification path.
- `php .claude/skills/run-tour-booking-manager/inspect.php [--rebuild]` — boots WordPress directly and calls plugin internals (`TTBM_Query::ttbm_query()`, `ttbm_upcoming_date` meta, rebuild transients) without HTTP. Use for changes to date/query logic. `--rebuild` clears the daily-rebuild transients and forces `TTBM_Function::update_all_upcoming_date_month()` to re-run.
- Override the site path with `WP_PATH=/path/to/site`.

Read that skill's `SKILL.md` before touching list/query/date code — it documents known gotchas (see below).

### Gutenberg blocks sub-build

`support/blocks/` has its own `package.json` (webpack + Babel, no WP `@wordpress/scripts`):

```bash
cd support/blocks && npm install
npm run build     # webpack --mode production -> build/index.js
npm run start      # webpack --watch --mode development
```

`support/blocks/index.php` wires this into the block editor; `tour-booking-manager.php` enqueues `support/blocks/build/index.js` on `enqueue_block_editor_assets`.

### Known correctness traps (from the run skill)

- **Permalinks are plain** on the dev site (`?page_id=N`, not pretty URLs) — don't assume pretty permalinks work when testing.
- The list shortcodes filter on the `ttbm_upcoming_date` post meta, rebuilt daily by `TTBM_Function::update_all_upcoming_date_month()` behind a transient lock. If that meta is stale/empty, list pages render with zero cards even though tours exist — not a query bug.
- `get_meta_values()`-style helpers return **raw serialized DB values**; array-typed meta (activities, features, included services) must be `maybe_unserialize()`d before `array_merge()`, or you get a fatal.
- Never name a local variable `$wp` in any script that does `require wp-load.php` — it shadows WordPress's global `$wp`.
- List rendering is capped by `TTBM_Function::get_list_render_cap()` (default 300, filterable via `ttbm_list_render_cap`) — all matching tours render server-side, pagination is client-side JS.

## Architecture

### Bootstrap chain

1. `tour-booking-manager.php` — plugin entry point. Defines `TTBM_PLUGIN_DIR`/`_URL`/`_VERSION`, requires `inc/TTBM_Dependencies.php`, registers the activation hook (auto-creates shortcode landing pages like `find`, `hotel-search`, `lotus-grid` on activation, gated by WooCommerce being active), and wires up Gutenberg block support.
2. `inc/TTBM_Dependencies.php` — the central loader and asset manager. Always loads `TTBM_Global_Function`, `TTBM_Global_Style`, `TTBM_Custom_Layout`, `TTBM_Woo_Installer` (the "please install WooCommerce" nag). **Everything else is gated behind `TTBM_Global_Function::check_woocommerce() == 1`** — most of the plugin (booking, checkout, shortcodes, admin CPT screens) simply doesn't load without WooCommerce active. This class also owns frontend/admin asset enqueueing, guarded by `should_load_frontend_assets()` / `should_load_admin_assets()` so the (large) asset bundle only loads on relevant pages/screens — respect and extend these guards rather than enqueueing unconditionally. Both guards are filterable (`ttbm_load_assets`, `ttbm_load_admin_assets`).
3. `admin/TTBM_Admin.php` — loaded only in the WooCommerce-active branch; requires the meta-box framework (`lib/classes/`) and then every `admin/*.php` and `admin/settings/**/*.php` file (CPTs, taxonomies, per-tab settings panels, hotel dashboard, etc).

All classes follow the same guard pattern: `if (!class_exists('TTBM_X')) { class TTBM_X { ... } }` then instantiate immediately at file scope (`new TTBM_X();`). There is no dependency injection or service container — classes talk to each other through static calls (`TTBM_Function::...`, `TTBM_Global_Function::...`) and WordPress hooks.

### Class naming and responsibility split (`inc/`)

- **`TTBM_Function`** (~2450 lines) — the tour domain's main static helper class: CPT name/slug/icon (white-label hooks — never hardcode `'ttbm_tour'` literals in new code, use `TTBM_Function::get_cpt_name()`), date/schedule logic (`update_upcoming_date_month`, `update_all_upcoming_date_month`, `get_reg_end_date`), pricing, settings getters.
- **`TTBM_Global_Function`** (~1340 lines) — cross-cutting utilities used by both tour and hotel code: `get_post_info()` (the standard post-meta getter used everywhere instead of raw `get_post_meta`), `get_settings()` (options getter), date formatting, WooCommerce-active check, a hand-rolled `esc_html()` wrapper with its own allowed-tags/attributes whitelist (used instead of core `wp_kses` in several places — extend its whitelist array rather than switching escaping strategy mid-file).
- **`TTBM_Query`** — builds the `WP_Query` args for tour listings (`ttbm_query()`) and hotel listings; encodes all the meta_query/tax_query filter logic (category, organizer, city, country, activity, feature, expired status). This is the file to touch for new list-filter shortcode attributes.
- **`TTBM_Shortcodes`** (class is actually `TTBM_Shortcode`, singular) — every `[travel-*]`, `[ttbm-*]`, `[wptravelly-*]` shortcode registration and its render method lives here. See [readme.txt](readme.txt) for the full public shortcode list/attributes — treat it as the API contract when changing shortcode attribute names.
- **`TTBM_Woocommerce`** — all WooCommerce cart/checkout/order integration: injecting tour data into cart items (`add_cart_item_data`), recalculating cart totals, writing tour/date/ticket/extra-service meta onto order line items at checkout, and reacting to order status changes (`ttbm_wc_order_status_change` is the internal hook other code — including Pro add-ons — listens on for booking confirmation/cancellation).
- **`TTBM_Woo_Installer`** — standalone "WooCommerce required" admin nag/popup; loads even when WooCommerce is inactive.
- **`TTBM_Filter_Pagination`** — AJAX-driven list filter + pagination backend (large file, ~1000 lines) for the frontend filter UI.
- **`TTBM_Details_Layout`, `TTBM_Layout`, `TTBM_Custom_Layout`, `TTBM_Tour_List`, `TTBM_Travel_List_Tab_Details`** — render helpers for the single tour page and list-card layouts; these `include`/`require` the matching files under `templates/`.
- **`TTBM_Hotel_*`** files mirror the tour-side classes for the hotel booking type (separate CPT `ttbm_hotel`, its own booking flow via `ttbm_hotel_booking`, a non-public CPT for hotel reservation records).
- **`TTBM_Wishlist`** — WooCommerce My-Account wishlist endpoint integration.

### Admin settings architecture (`admin/settings/`)

Settings are split per-entity-type (`admin/settings/tour/`, `admin/settings/hotel/`) and per-tab (one class per settings tab/section, e.g. `TTBM_Settings_General`, `TTBM_Settings_Dates`, `TTBM_Settings_pricing`, `TTBM_Settings_Feature`...). Every tab class hooks into the shared action `ttbm_meta_box_tab_content` and renders its own `<div class="tabsItem ...">` fragment; `lib/classes/class-meta-box.php` (`TTBM_Meta_Box`/similar) and `lib/classes/class-form-fields-generator.php` (`FormFieldsGenerator`, ~467KB — the field-rendering toolkit: text/number/toggle/color/repeater fields) drive the actual meta box shell and save routine. When adding a new tour/hotel meta field:
1. Add a new method to the relevant `TTBM_Settings_*` tab class, hooked to `ttbm_meta_box_tab_content`, rendering with the `FormFieldsGenerator` helpers/markup conventions already used in that file (see [admin/settings/tour/TTBM_Settings_General.php](admin/settings/tour/TTBM_Settings_General.php) for the pattern).
2. Read it back via `TTBM_Global_Function::get_post_info($post_id, 'your_meta_key', $default)`, not raw `get_post_meta`.
3. Register the file's `require_once` in `admin/TTBM_Admin.php`'s `load_ttbm_admin()`.

Global (non-per-post) plugin settings live in `admin/TTBM_Settings_Global.php` + `admin/settings/TTBM_Setting_API.php`, read via `TTBM_Global_Function::get_settings($option_group, $key, $default)` (option groups like `ttbm_global_settings`, `mp_style_settings`) — note `TTBM_Dependencies::ttbm_upgrade()` runs one-time migrations between several legacy option names (`mp_global_settings` → `ttbm_global_settings` etc.), so don't assume a single canonical option name without checking that method.

Admin styling has two eras: legacy `assets/admin/ttbm_admin.css` and a newer CSS-only design-token layer `assets/admin/ttbm_admin_modern.css` (loaded after, `depends: ttbm_admin`) introduced by recent commits — new admin UI should use the modern tokens/classes, not extend the legacy stylesheet.

### Custom post types & taxonomies

Registered in `admin/TTBM_CPT.php` / `admin/TTBM_Taxonomy.php`: `ttbm_tour` (public, main tour CPT, slug/label filtered through `TTBM_Function::get_name()/get_slug()/get_icon()` for white-labeling), `ttbm_ticket_types`, `ttbm_hotel` (public but hidden from the admin menu — edited through the plugin's own hotel dashboard, not `edit.php`), `ttbm_places`, `ttbm_guide`, `ttbm_hotel_booking` (private, `show_ui => false` — internal booking records only). Taxonomies include `ttbm_tour_cat`, `ttbm_tour_org`, `ttbm_tour_location`, `ttbm_tour_tag`, `ttbm_tour_activities`, `ttbm_tour_features_list`, `ttbm_hotel_features_list`, `ttbm_hotel_activities_list`.

### Templates (`templates/`)

Plain PHP includes, no templating engine — organized by concern:
- `templates/themes/` — top-level single-tour page skins (`default.php`, `smart.php`, `viator.php`, `hotel_default.php`), selected per-tour via a settings field.
- `templates/layout/` — reusable fragments used across themes/lists (pricing box, gallery, FAQ, location map, related tours, etc).
- `templates/list/` — the different list/grid render styles referenced by shortcode `style` attribute (`grid_list`, `lotus_list`, `orchid_list`, `flora_list`, `blossom_list`, `default`).
- `templates/ticket/` — booking/registration form partials (date selection, extra services, hotel booking form) rendered inside the WooCommerce add-to-cart flow.
- `templates/single_page/`, `templates/hotel_layout/` — single tour/hotel page assembly.

### Frontend↔backend data flow (booking)

1. Registration/date-picker form (`templates/ticket/*`) posts to WooCommerce add-to-cart, nonce-checked against `ttbm_form_nonce`.
2. `TTBM_Woocommerce::add_cart_item_data()` reads `$_POST`, resolves the linked `ttbm_tour` post (handles the "hidden WooCommerce product linked to a tour" pattern via `link_ttbm_id` meta and multilingual post-id resolution), computes price, and stashes tour/date/ticket/extra-service/hotel info onto the cart item.
3. `checkout_create_order_line_item` copies that cart-item data onto the order line item meta at checkout.
4. `order_status_changed` / the internal `ttbm_wc_order_status_change` action is the hook point for anything that needs to react to a booking being confirmed/cancelled (inventory, cancellation-window logic, notifications) — Pro add-ons (PDF tickets, CSV export, seasonal/group pricing) hook in here rather than being in this repo.

### Multilingual / white-label seams

Several functions exist purely as indirection points — don't bypass them even though they currently look like they return a constant:
- `TTBM_Function::get_cpt_name()/get_name()/get_slug()/get_icon()` — CPT branding, filterable for white-label builds.
- `TTBM_Function::post_id_multi_language()` and the `ttbm_get_translation_post_id` filter — WPML/Polylang post-id resolution; any code that takes a tour ID from `$_POST`/cart data should be passed through this instead of used raw.

### Pro/Free boundary

This is the free version. Readme and code comments reference paid add-ons (Backend Order, Seat Plan, Seasonal/Group/Early-Bird Pricing, QR Code, PDF tickets, CSV export) that are **not in this repo** — they hook into actions/filters exposed here (`ttbm_add_cart_item`, `ttbm_wc_order_status_change`, `ttbm_admin_script`, `add_ttbm_registration_enqueue`, etc). When adding new extension points, follow the existing `do_action('ttbm_*', ...)` / `apply_filters('ttbm_*', ...)` naming convention so add-ons can hook in the same way.

### Third-party bundled libs

`lib/appsero/` — Appsero telemetry SDK (opt-in only, initialized in `TTBM_Dependencies::appsero_init_tracker_ttbm()`). `assets/` bundles vendored frontend libs as static files (Select2, Owl Carousel, a date range picker, a timepicker, Font Awesome/"mage-icon" icon fonts) — these are committed binaries/minified files, not managed by a package manager; update by replacing the file in place.

## Conventions to follow

- Escape output with `esc_html()`/`esc_attr()`/`esc_url()` (or `TTBM_Global_Function::esc_html()` for the whitelisted-HTML case) and verify nonces (`ttbm_form_nonce`, `ttbm_frontend_nonce`, `ttbm_admin_nonce`) on every new AJAX handler / form processor, matching the existing handlers in `inc/TTBM_Filter_Pagination.php` and `admin/TTBM_Travel_Tab_Data_Add_Display_Ajax.php`.
- AJAX actions are named `wp_ajax_ttbm_*` (always paired with `wp_ajax_nopriv_ttbm_*` when the action must work for logged-out visitors, e.g. ticket availability).
- Post meta is read via `TTBM_Global_Function::get_post_info($post_id, $key, $default)`, not `get_post_meta()` directly, so default-value handling stays centralized.
- Version bumps touch three places in lockstep: `tour-booking-manager.php` header comment `Version:`, the `TTBM_PLUGIN_VERSION` define, and `readme.txt`'s `Stable tag` + changelog.
