<?php
	if (!defined('ABSPATH')) {
		die;
	}
	$ttbm_post_id = $ttbm_post_id ?? get_the_id();
	$guides = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_tour_guide', array());
	if (sizeof($guides) > 0 && TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_display_tour_guide', 'off') != 'off') {
		$ttbm_guide_style = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_guide_style', 'carousel');
		$ttbm_guide_image_style = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_guide_image_style', 'squire');
		$ttbm_guide_description_style = TTBM_Global_Function::get_post_info($ttbm_post_id, 'ttbm_guide_description_style', 'full');
		?>
		<div class="ttbm_style ttbm_wraper ttbm-tour-guide">
			<div class='ttbm_default_widget'>
				<?php do_action('ttbm_section_titles', $ttbm_post_id, esc_html__('Meet our guide ', 'tour-booking-manager')); ?>
				<?php
					if (sizeof($guides) > 1 && $ttbm_guide_style=='carousel') {
						include(TTBM_Function::template_path('layout/carousel_indicator.php'));
					}
				?>
				<div class="ttbm_widget_content">
					<div class="tour-guide-lists <?php echo esc_attr($ttbm_guide_image_style); ?> <?php if (sizeof($guides) > 1 && $ttbm_guide_style=='carousel') { ?> owl-theme owl-carousel <?php } ?>" <?php if (sizeof($guides) > 1 && $ttbm_guide_style=='carousel') { ?>  id="ttbm-tour-guide"<?php } ?>>
						<?php foreach ($guides as $guide_id) { ?>
							<div class="ttbm_tour_guide_item <?php if (sizeof($guides) > 1 && $ttbm_guide_style=='carousel') { ?>item<?php } ?>" id="<?php echo esc_attr($guide_id); ?>">
								<div class="bg_image_area" data-placeholder>
								<div class="" data-bg-image="<?php echo esc_url( TTBM_Global_Function::get_image_url( $guide_id ) ); ?>">
									<div class="ttbm_list_title absolute_item bottom" data-placeholder="">
										<h5><?php echo esc_html( get_the_title( $guide_id ) ); ?></h5>
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
											<div class="ttbm_description ttbm_wp_editor" data-placeholder>
												<?php echo esc_html( $message ); ?>
												<?php if ($word_count > 16) { ?>
													<span data-collapse='#<?php echo esc_attr($guide_id); ?>'><?php echo esc_html($more_message); ?></span>
													<span class="load_more_text" data-collapse-target="#<?php echo esc_attr($guide_id); ?>">	<?php esc_html_e('view more ', 'tour-booking-manager'); ?></span>
												<?php } ?>
											</div>
										<?php } else { ?>
											<div class="ttbm_description ttbm_wp_editor" data-placeholder>
												<?php echo apply_filters( 'the_content', $des ); ?>
											</div>
										<?php } ?>
								<?php } ?>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}