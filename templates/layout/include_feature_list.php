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
				$hidden_count = 0;
				foreach ($include_services as $key => $services) {
					//if ( $count < $term_count && $services ) {
					$term = get_term_by('name', $services, 'ttbm_tour_features_list');
					if ($term) {
						$icon = get_term_meta($term->term_id, 'ttbm_feature_icon', true);
						$icon = $icon ?: 'fas fa-forward';
						$display_name = $term_name ? $term->name : '';
						
						$li_class = '';
						if (!$list_view_task && $count >= 3) {
							$li_class = 'ttbm-feature-hidden';
							$hidden_count++;
						}
						?>
                        <li class="<?php echo esc_attr($li_class); ?>" title="<?php echo esc_attr($term->name); ?>">
                            <span class="circleIcon_xs <?php echo esc_attr($icon); ?>"></span>
							<?php echo esc_html($display_name); ?>
                        </li>
					<?php
					}
					//}
					$count++;
				}
				if (!$list_view_task && $hidden_count > 0) {
					?>
                    <li class="ttbm-feature-view-more">
                        <a href="javascript:void(0);" class="ttbm-view-more-features-btn" style="text-decoration: none; color: inherit;">+<?php echo esc_html($hidden_count); ?></a>
                    </li>
					<?php
				}
			?>
        </ul>
	<?php } ?>