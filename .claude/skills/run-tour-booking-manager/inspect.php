<?php
/**
 * Direct-invocation inspector for Tour Booking Manager internals.
 *
 * Boots WordPress and reports the state that drives the tour list: the
 * ttbm_upcoming_date meta (the list query filters on it), the daily rebuild
 * transients, and what TTBM_Query::ttbm_query() actually returns. This is the
 * fast path for changes to get_date(), get_meta_values(),
 * update_all_upcoming_date_month(), etc. -- no browser needed.
 *
 *   php .claude/skills/run-tour-booking-manager/inspect.php
 *   php .claude/skills/run-tour-booking-manager/inspect.php --rebuild   # force date rebuild
 *
 * Override the WP path with WP_PATH=/path/to/site.
 */
$wp_path = getenv('WP_PATH') ?: '/var/www/html/tour';
require $wp_path . '/wp-load.php';

if (in_array('--rebuild', $argv, true)) {
    delete_transient('ttbm_last_upcoming_update');
    delete_transient('ttbm_upcoming_update_lock');
    TTBM_Function::update_all_upcoming_date_month();
    echo "[rebuilt upcoming dates]\n";
}

$ids = get_posts(['post_type'=>'ttbm_tour','post_status'=>'publish','numberposts'=>-1,'fields'=>'ids']);
printf("published tours: %d\n", count($ids));
foreach ($ids as $id) {
    printf("  #%-4d %-40s upcoming=%s\n", $id, get_the_title($id),
        var_export(get_post_meta($id,'ttbm_upcoming_date',true), true));
}
printf("done_transient(ttbm_last_upcoming_update): %s\n", var_export(get_transient('ttbm_last_upcoming_update'), true));
printf("lock_transient(ttbm_upcoming_update_lock): %s\n", var_export(get_transient('ttbm_upcoming_update_lock'), true));
printf("render cap: %d\n", TTBM_Function::get_list_render_cap());

$loop = TTBM_Query::ttbm_query(TTBM_Function::get_list_render_cap());
printf("ttbm_query() found_posts: %d  (0 = list will look empty)\n", $loop->found_posts);
