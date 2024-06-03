<?php
	if (!defined('ABSPATH')) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$include_services = $include_services ?? TTBM_Function::get_feature_list($ttbm_post_id, 'ttbm_service_included_in_price');
	if (sizeof($include_services) > 0) {
		$term_name = $term_name ?? '';
		$term_count = $term_count ?? sizeof($include_services);
		$list_view_task=$list_view_task??false;
		?>
        <ul>
			<?php
				$count = 0;
				foreach ($include_services as $key => $services) {
					//if ( $count < $term_count && $services ) {
					$term = get_term_by('name', $services, 'ttbm_tour_features_list');
					if ($term) {
						$icon = get_term_meta($term->term_id, 'ttbm_feature_icon', true);
						$icon = $icon ?: 'fas fa-forward';
						$term_name = $term_name ? $term->name : '';
						if ($key < 3 || $list_view_task):
							?>
                            <li title="<?php echo esc_attr($term->name); ?>">
                                <span class="circleIcon_xs <?php esc_attr_e($icon); ?>"></span>
								<?php echo esc_html($term_name); ?>
                            </li>
						<?php
						endif;
					}
					//}
					$count++;
				}
			?>
        </ul>
	<?php } ?>