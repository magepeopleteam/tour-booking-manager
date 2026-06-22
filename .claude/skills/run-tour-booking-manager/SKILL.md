---
name: run-tour-booking-manager
description: Run, launch, smoke-test, and screenshot the Tour Booking Manager (Travelly) WordPress plugin frontend, or directly invoke its internals. Use when asked to run/start/verify/test the tour list, tour booking plugin, ttbm shortcodes, or check that tours render.
---

# Run: Tour Booking Manager (Travelly)

WordPress + WooCommerce plugin (`ttbm_*` / `TTBM_*`) for tour, hotel, and travel
bookings. It is **not** a standalone app — it runs inside an already-installed
WordPress site served by **Apache on port 80** at `http://localhost/tour`. You
drive it two ways, both already built and committed here:

- **`driver.sh`** — `curl`-based smoke test of the tour-list shortcode pages
  (HTTP 200 + no PHP fatal + counts rendered tour cards). This is the primary
  agent path. No browser is installed; the cards are in the server-rendered
  HTML, so `curl` is sufficient.
- **`inspect.php`** — direct-invocation harness: boots WordPress and calls the
  plugin's internals (`TTBM_Query::ttbm_query()`, the `ttbm_upcoming_date` meta,
  rebuild transients). Use this for changes to `get_date()`,
  `get_meta_values()`, `update_all_upcoming_date_month()`, etc.

All paths below are relative to the **plugin root**
(`/var/www/html/tour/wp-content/plugins/tour-booking-manager/`). The skill's own
files are under `.claude/skills/run-tour-booking-manager/`.

## Prerequisites

Already present in this container: Apache (running, `:80`), PHP 8.3 CLI, `curl`,
MySQL with DB `tour`. Nothing to install. There is **no `wp-cli`** and **no
browser** (`chromium`/`chromium-cli` are absent).

The plugin must be active and demo data imported (it is: 5 published `ttbm_tour`
posts). Verify the site responds:

```bash
curl -s -o /dev/null -w '%{http_code}\n' "http://localhost/tour/"
```

## Run (agent path) — smoke the frontend

```bash
.claude/skills/run-tour-booking-manager/driver.sh
```

Expected output (all PASS, `cards` = number of published tours, currently 5):

```
PAGE             HTTP   CARDS   RESULT
ttbm-tour-list   200    5       PASS  http://localhost/tour/?page_id=11
lotus-grid       200    5       PASS  http://localhost/tour/?page_id=9
orchid-grid      200    5       PASS  http://localhost/tour/?page_id=10
find             200    5       PASS  http://localhost/tour/?page_id=6
```

It resolves page IDs from WordPress by slug (never hard-coded), saves each
page's HTML to `/tmp/ttbm_run/<slug>.html`, and exits non-zero if any page
returns non-200 or contains a PHP fatal. `cards=0` with HTTP 200 means the list
renders but is **empty** — see Gotchas.

Override the site path with `WP_PATH=/path/to/site .claude/.../driver.sh`.

## Run (direct invocation) — plugin internals

```bash
php .claude/skills/run-tour-booking-manager/inspect.php
php .claude/skills/run-tour-booking-manager/inspect.php --rebuild   # force date rebuild
```

Prints each tour's `ttbm_upcoming_date`, the rebuild transients, the render cap,
and `ttbm_query()->found_posts`. `found_posts: 0` ⇒ the list will look empty.
`--rebuild` clears the transients and re-runs `update_all_upcoming_date_month()`.

## Run (human path)

Open `http://localhost/tour/?page_id=11` in a browser. The tour list renders
server-side; Load More / filters are client-side JS. Useless headless — use
`driver.sh`.

## Gotchas

- **Permalinks are "plain"** (`permalink_structure` is empty). Pretty URLs like
  `/tour/ttbm-tour-list/` return **404**. Always use `?page_id=<N>`. `driver.sh`
  builds these URLs via `home_url('/?page_id='.$id)`.
- **Never name a PHP variable `$wp`** in a `wp-load.php` script — it shadows
  WordPress's global `$wp` object and crashes boot with
  `Call to a member function add_query_var() on string` deep in
  `class-wp-taxonomy.php`. `inspect.php` uses `$wp_path`.
- **The tour list filters on the `ttbm_upcoming_date` post meta.** If that meta
  is empty for all tours, the page renders but shows **zero cards** even though
  tours exist. The meta is (re)built daily by
  `update_all_upcoming_date_month()`, guarded by a transient. If a rebuild is
  interrupted, run `inspect.php --rebuild` to repopulate.
- **`get_meta_values()` returns raw serialized DB rows.** Array-typed meta
  (activities, features) must be `maybe_unserialize`d before `array_merge`, or
  you get `array_merge(): Argument #2 must be of type array, string given` in
  the left-filter functions.
- **Render cap:** list shortcodes render every tour server-side (client-side
  pagination), capped at `TTBM_Function::get_list_render_cap()` (default 300,
  filter `ttbm_list_render_cap`) to avoid request timeouts on large catalogs.
- No browser ⇒ no screenshots. The server-rendered HTML in `/tmp/ttbm_run/`
  contains the `filter_item` cards; grep it to inspect output.

## Troubleshooting

- **`driver.sh` says `found no shortcode pages`** — the plugin isn't active or
  demo data isn't imported. Check `active_plugins` and that `ttbm_tour` posts
  exist.
- **All pages FAIL with HTTP 500 / "critical error"** — a PHP fatal in the
  plugin. Open the saved HTML in `/tmp/ttbm_run/<slug>.html` for the message, or
  run `php -l` on changed files. The recurring one is the `array_merge` fatal
  above.
- **HTTP 200 but `cards=0`** — empty `ttbm_upcoming_date` meta. Run
  `php .claude/skills/run-tour-booking-manager/inspect.php --rebuild`.
- **`inspect.php` fatals on boot** — usually the `$wp` variable collision (see
  Gotchas) or a wrong `WP_PATH`.
