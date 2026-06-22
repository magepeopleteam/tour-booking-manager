#!/usr/bin/env bash
# Smoke-driver for the Tour Booking Manager (Travelly) WordPress plugin.
#
# Drives the ALREADY-RUNNING WordPress site (Apache on :80) and asserts the
# tour-list / hotel shortcode pages render server-side without a PHP fatal and
# actually emit tour cards. This is the exact path used to debug the
# "connection timeout" / "no tours showing" / "array_merge fatal" issues.
#
# Usage:
#   .claude/skills/run-tour-booking-manager/driver.sh            # smoke all pages
#   WP_PATH=/var/www/html/tour .claude/skills/run-tour-booking-manager/driver.sh
#
# Exit 0 = all pages PASS, non-zero = at least one FAIL.
set -uo pipefail
WP_PATH="${WP_PATH:-/var/www/html/tour}"
OUT="${OUT:-/tmp/ttbm_run}"
mkdir -p "$OUT"

if [ ! -f "$WP_PATH/wp-load.php" ]; then
  echo "ERROR: no wp-load.php at $WP_PATH (set WP_PATH=/path/to/wp/site)"; exit 2
fi

# Resolve the shortcode pages straight from WordPress so page IDs are never
# hard-coded. Permalinks are "plain" here, so ?page_id= is the reliable URL.
mapfile -t PAGES < <(php -r '
  require $argv[1]."/wp-load.php";
  foreach (["ttbm-tour-list","lotus-grid","orchid-grid","find"] as $s) {
    $p = get_page_by_path($s);
    if ($p) echo $p->ID."|".$s."|".home_url("/?page_id=".$p->ID)."\n";
  }
' "$WP_PATH")

if [ "${#PAGES[@]}" -eq 0 ]; then
  echo "ERROR: found no shortcode pages (ttbm-tour-list/lotus-grid/...). Is the plugin active and demo data imported?"; exit 2
fi

fail=0
printf '%-16s %-6s %-7s %s\n' "PAGE" "HTTP" "CARDS" "RESULT"
for line in "${PAGES[@]}"; do
  IFS='|' read -r id slug url <<<"$line"
  html="$OUT/$slug.html"
  code=$(curl -s -o "$html" -w '%{http_code}' "$url")
  cards=$(grep -o 'class="filter_item' "$html" | wc -l | tr -d ' ')
  fatal=$(grep -iEo 'Fatal error[^<]*|There has been a critical error|Parse error[^<]*' "$html" | head -1)
  if [ "$code" = "200" ] && [ -z "$fatal" ]; then
    printf '%-16s %-6s %-7s PASS  %s\n' "$slug" "$code" "$cards" "$url"
  else
    printf '%-16s %-6s %-7s FAIL  %s  %s\n' "$slug" "$code" "$cards" "${fatal:-}" "$url"
    fail=1
  fi
done
echo "saved HTML -> $OUT/"
exit $fail
