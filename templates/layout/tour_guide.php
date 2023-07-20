<?php
	if (!defined('ABSPATH')) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$guides = MP_Global_Function::get_post_info($ttbm_post_id, 'ttbm_tour_guide', array());
	if (sizeof($guides) > 0 && MP_Global_Function::get_post_info($ttbm_post_id, 'ttbm_display_tour_guide', 'off') != 'off') {
		$ttbm_guide_style = MP_Global_Function::get_post_info($ttbm_post_id, 'ttbm_guide_style', 'carousel');
		$ttbm_guide_image_style = MP_Global_Function::get_post_info($ttbm_post_id, 'ttbm_guide_image_style', 'squire');
		$ttbm_guide_description_style = MP_Global_Function::get_post_info($ttbm_post_id, 'ttbm_guide_description_style', 'full');
		?>
		<div class='mpStyle ttbm_wraper ttbm_tour_guide' <?php if (sizeof($guides) > 1 && $ttbm_guide_style=='carousel') { ?>  id="ttbm_tour_guide"<?php } ?>>
			<div class='ttbm_default_widget'>
				<?php do_action('ttbm_section_titles', $ttbm_post_id, esc_html__('Meet our guide ', 'tour-booking-manager')); ?>
				<?php
					if (sizeof($guides) > 1 && $ttbm_guide_style=='carousel') {
						include(TTBM_Function::template_path('layout/carousel_indicator.php'));
					}
				?>
				<div class="ttbm_widget_content _mZero <?php if (sizeof($guides) > 1 && $ttbm_guide_style=='carousel') { ?> owl-theme owl-carousel <?php } ?>">
					<?php foreach ($guides as $guide_id) { ?>
						<div class="">
							<div class="bg_image_area mb" data-placeholder>
								<div class="<?php echo esc_attr($ttbm_guide_image_style); ?>" data-bg-image="<?php echo MP_Global_Function::get_image_url($guide_id); ?>">
									<div class="ttbm_list_title absolute_item bottom" data-placeholder="">
										<h5><?php echo get_the_title($guide_id); ?></h5>
									</div>
								</div>
							</div>
							<?php
								$des = get_post_field('post_content', $guide_id);
								if ($des) {
									if ($ttbm_guide_description_style == 'short') {
										$word_count = str_word_count($des);
										$message = implode(" ", array_slice(explode(" ", $des), 0, 16));
										$more_message = implode(" ", array_slice(explode(" ", $des), 16, $word_count));
										?>
										<div class="ttbm_description mp_wp_editor" data-placeholder>
											<?php echo MP_Global_Function::esc_html($message); ?>
											<?php if ($word_count > 16) { ?>
												<span data-collapse='#<?php echo esc_attr($guide_id); ?>'><?php echo MP_Global_Function::esc_html($more_message); ?></span>
												<span class="load_more_text" data-collapse-target="#<?php echo esc_attr($guide_id); ?>">	<?php esc_html_e('view more ', 'tour-booking-manager'); ?></span>
											<?php } ?>
										</div>
									<?php } else { ?>
										<div class="ttbm_description mp_wp_editor" data-placeholder>
											<?php echo do_shortcode($des); ?>
										</div>
									<?php } ?>
								<?php } ?>
						</div>
					<?php } ?>
				</div>
			</div>
		</div>
	<?php } ?>