<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('TTBM_Admin_Tour_List')) {
		class TTBM_Admin_Tour_List {
			public function __construct() {
				add_action('admin_menu', array($this, 'tour_list_menu'), 1);
				//===//
				add_action('wp_ajax_ttbm_trash_post', array($this, 'ttbm_trash_post'));
				add_action('wp_ajax_nopriv_ttbm_trash_post', array($this, 'ttbm_trash_post'));
			}
			public function tour_list_menu() {
				$label = TTBM_Function::get_name();
				add_submenu_page('edit.php?post_type=ttbm_tour', $label . ' ' . esc_html__('List', 'tour-booking-manager'), $label . ' ' . esc_html__('List', 'tour-booking-manager'), 'manage_options', 'ttbm_list', array($this, 'ttbm_list'));
			}
			public function ttbm_list() {
				$page = isset($_REQUEST['page']) ? MP_Global_Function::data_sanitize($_REQUEST['page']) : 1;
				$post_query = MP_Global_Function::query_post_type(TTBM_Function::get_cpt_name());
				$label = TTBM_Function::get_name();
				$categories = MP_Global_Function::get_taxonomy('ttbm_tour_cat');
				$organizers = MP_Global_Function::get_taxonomy('ttbm_tour_org');
				$locations = MP_Global_Function::get_taxonomy('ttbm_tour_location');
				?>
                <div class="wrap travel-list">
                    <div class="mpStyle">
                        <div class="_dLayout_pRelative placeholder_area " id="ttbm_list_page">
                            <button class="btn btn-primary rounded" type="button" data-href="<?php echo esc_url(admin_url('post-new.php?post_type=' . TTBM_Function::get_cpt_name())); ?>" title="<?php esc_attr_e('Add New', 'tour-booking-manager'); ?>">
                                <span class="fas fa-plus _mR_xs"></span><?php echo esc_html__('Add New ', 'tour-booking-manager') . ' ' . $label; ?>
                            </button>
                            <div class="_mb_dFlex_justifyBetween_alignCenter my-2" data-placeholder>
                                <div class="dFlex">
                                    <input type="text" placeholder="Bulk Action">
                                    <button class="btn btn-primary ms-2">Action</button>
                                </div>
                                <div class="col_4 _allCenter">
                                    <div class="groupContent bgWhite">
                                        <label class="min_150">
                                            <select class="formControl" name="ttbm_filter_type" data-collapse-target>
                                                <option value="ttbm_id" data-option-target="#ttbm_list_id" selected><?php echo esc_html($label); ?></option>
												<?php if (is_array($categories) && sizeof($categories) > 0) { ?>
                                                    <option value="ttbm_list_category_filter" data-option-target="#ttbm_list_category_filter"><?php esc_html_e('Filter By Category ', 'tour-booking-manager'); ?></option>
												<?php } ?>
												<?php if (is_array($organizers) && sizeof($organizers) > 0) { ?>
                                                    <option value="ttbm_list_organizer_filter" data-option-target="#ttbm_list_organizer_filter"><?php esc_html_e('Filter By  Organizer ', 'tour-booking-manager'); ?></option>
												<?php } ?>
												<?php if (is_array($locations) && sizeof($locations) > 0) { ?>
                                                    <option value="ttbm_list_location_filter" data-option-target="#ttbm_list_location_filter"><?php esc_html_e('Filter By  Location ', 'tour-booking-manager'); ?></option>
												<?php } ?>
                                            </select>
                                        </label>
                                        <div data-collapse="#ttbm_list_id" class="mActive">
											<?php TTBM_Layout::tour_list_in_select(); ?>
                                        </div>
										<?php if (is_array($categories) && sizeof($categories) > 0) { ?>
                                            <div class="min_300" data-collapse="#ttbm_list_category_filter">
                                                <label data-placeholder="">
                                                    <select class="formControl" name="ttbm_list_category_filter">
                                                        <option selected value=""><?php esc_html_e('All Category', 'tour-booking-manager'); ?></option>
														<?php foreach ($categories as $category) { ?>
                                                            <option value="<?php echo esc_attr($category->term_id); ?>"><?php echo esc_html($category->name); ?></option>
														<?php } ?>
                                                    </select>
                                                </label>
                                            </div>
										<?php } ?>
										<?php if (is_array($organizers) && sizeof($organizers) > 0) { ?>
                                            <div class="min_300" data-collapse="#ttbm_list_organizer_filter">
                                                <label data-placeholder>
                                                    <select class="formControl" name="ttbm_list_organizer_filter">
                                                        <option selected value=""><?php esc_html_e('All Organizer', 'tour-booking-manager'); ?></option>
														<?php foreach ($organizers as $organizer) { ?>
                                                            <option value="<?php echo esc_attr($organizer->term_id); ?>"><?php echo esc_html($organizer->name); ?></option>
														<?php } ?>
                                                    </select>
                                                </label>
                                            </div>
										<?php } ?>
										<?php if (is_array($organizers) && sizeof($organizers) > 0) { ?>
                                            <div class="min_300" data-collapse="#ttbm_list_location_filter">
                                                <label data-placeholder>
                                                    <select class="formControl" name="ttbm_list_location_filter">
                                                        <option selected value=""><?php esc_html_e('All Location', 'tour-booking-manager'); ?></option>
														<?php foreach ($locations as $location) { ?>
                                                            <option value="<?php echo esc_attr($location->term_id); ?>"><?php echo esc_html($location->name); ?></option>
														<?php } ?>
                                                    </select>
                                                </label>
                                            </div>
										<?php } ?>
                                    </div>
                                </div>
                                <p class="total-found">
									<?php esc_html_e('Total Found :', 'tour-booking-manager'); ?>&nbsp;
                                    <span class="badge purple"><b><?php echo esc_html($post_query->found_posts); ?></b> <?php echo esc_html__('Travels', 'tour-booking-manager'); ?></span>
                                </p>
                                <!-- <label class="groupContent bgWhite textDefault"><span class="padding_xs">--><?php //echo $label . ' ' . esc_html__('Per Page', 'tour-booking-manager'); ?><!--</span> <input type="number" min="1" class="formControl _max_100_textCenter" name="post_per_page" value="20"/></label>-->
                            </div>
							<?php $this->tour_table($post_query, $page); ?>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function tour_table($post_query, $page) {
				$label = TTBM_Function::get_name();
				$total_post = $post_query->post_count;
				if ($total_post > 0) {
					$all_posts = $post_query->posts;
					$active_page = (int)$page - 1;
					$active_page = max($active_page, 0);
					$post_per_page = $_REQUEST['post_per_page'] ?? 20;
					$post_per_page = $post_per_page > 0 ? $post_per_page : 20;
					?>
                    <table class="layoutFixed" data-placeholder>
                        <thead>
                        <tr>
                            <th class="w-3"><input type="checkbox"></th>
                            <th class="textLeft w-8"><?php esc_html_e('Featured Image', 'tour-booking-manager'); ?></th>
                            <th class="textLeft w-30"><?php esc_html_e('Title', 'tour-booking-manager'); ?></th>
                            <th class="textLeft"><?php esc_html_e('Category', 'tour-booking-manager'); ?></th>
                            <th class="textLeft"><?php esc_html_e('Organizer', 'tour-booking-manager'); ?></th>
                            <th class="textLeft"><?php esc_html_e('Location', 'tour-booking-manager'); ?></th>
                            <th class="textLeft"><?php esc_html_e('Upcoming Date', 'tour-booking-manager'); ?></th>
                            <th class="textLeft"><?php esc_attr_e('End Date', 'tour-booking-manager'); ?></th>
                            <th class="textLeft"><?php esc_html_e('Action', 'tour-booking-manager'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="9">
                            </td>
                        </tr>
						<?php
							$count = $active_page * $post_per_page + 1;
							foreach ($all_posts as $post) {
								$post_id = $post->ID;
								TTBM_Function::update_upcoming_date_month($post_id);
								$upcoming_date = MP_Global_Function::get_post_info($post_id, 'ttbm_upcoming_date');
								$total = TTBM_Function::get_total_seat($post_id);
								$reserve = TTBM_Function::get_total_reserve($post_id);
								$sold = TTBM_Function::get_total_sold($post_id, $upcoming_date);
								$category = TTBM_Function::get_taxonomy_id_string($post_id, 'ttbm_tour_cat');
								$organizer = TTBM_Function::get_taxonomy_id_string($post_id, 'ttbm_tour_org');
								$location = TTBM_Function::get_taxonomy_id_string($post_id, 'ttbm_tour_location');
								$admin_note = MP_Global_Function::get_post_info($post_id, 'ttbm_admin_note');
								?>
                                <tr data-upcoming_date="<?php echo esc_attr($upcoming_date); ?>" data-post_id="<?php echo esc_attr($post_id); ?>" data-category="<?php echo esc_attr($category); ?>" data-organizer="<?php echo esc_attr($organizer); ?>" data-location="<?php echo esc_attr($location); ?>">
                                    <td class="textCenter"><input type="checkbox"></td>
                                    <td valign="middle"><?php echo get_the_post_thumbnail($post_id, 'thumbnail'); ?></td>
                                    <td class="textLeft w-10" valign="middle">
                                        <span class="badge green"><?php esc_html_e('Total', 'tour-booking-manager') ?> <b><?php echo esc_html($total) ?></b></span>
                                        <span class="badge beguni"><?php esc_html_e('Sold', 'tour-booking-manager') ?> <b><?php echo esc_html($sold) ?></b></span>
                                        <span class="badge blue"><?php esc_html_e('Reserved', 'tour-booking-manager') ?> <b><?php echo esc_html($reserve) ?></b></span>
                                        <span class="badge purple"><?php esc_html_e('Available', 'tour-booking-manager') ?> <b><?php echo esc_html($total - ($reserve + $sold)) ?></b></span>
                                        <p><a href="<?php echo get_the_permalink($post_id); ?>"><?php echo get_the_title($post_id); ?></a></p>
                                    </td>
                                    <td><?php echo esc_attr(TTBM_Function::get_taxonomy_string($post_id, 'ttbm_tour_cat')); ?></td>
                                    <td><?php echo esc_attr(TTBM_Function::get_taxonomy_string($post_id, 'ttbm_tour_org')); ?></td>
                                    <td><?php echo TTBM_Function::get_full_location($post_id); ?></td>
                                    <td>
										<?php if ($upcoming_date) { ?>
                                            <span class="textSuccess"><?php echo esc_html(MP_Global_Function::date_format( $upcoming_date)); ?></span>
										<?php } else { ?>
                                            <span class="textWarning"><?php esc_html_e('Expired !', 'tour-booking-manager'); ?></span>
										<?php } ?>
                                    </td>
                                    <td><?php echo MP_Global_Function::date_format( TTBM_Function::get_reg_end_date($post_id)); ?></td>
                                    <td>
                                        <div class="buttonGroup">
                                            <?php wp_nonce_field('edd_sample_nonce', 'edd_sample_nonce');  ?>
                                            <button class="_mpBtn_xs_textGray" type="button" title="<?php esc_attr_e('Edit Details.', 'tour-booking-manager'); ?>" data-href="<?php echo esc_url(admin_url('post.php?post=' . $post_id . '&action=edit')); ?>">
                                                <span class="fas fa-edit mp_zero"></span>
                                            </button>
											<?php do_action('add_ttbm_list_action_button', $post_id); ?>
                                            <button class="_mpBtn_xs_textDanger ttbm_trash_post" type="button" data-alert="<?php echo esc_attr__('Are you sure ? To trash : ', 'tour-booking-manager') . ' ' . get_the_title($post_id); ?>" data-post-id="<?php echo esc_attr($post_id); ?>" title="<?php echo esc_attr__('Trash ', 'tour-booking-manager') . ' : ' . get_the_title($post_id); ?>">
                                                <span class="fas fa-trash-alt mp_zero"></span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr data-post_id="<?php echo esc_attr($post_id); ?>" data-category="<?php echo esc_attr($category); ?>" data-organizer="<?php echo esc_attr($organizer); ?>" data-location="<?php echo esc_attr($location); ?>">
                                    <td colspan="9">
										<?php do_action('add_ttbm_list_action_details', $post_id); ?>
                                    </td>
                                </tr>
								<?php
								$count++;
							}
						?>
                        </tbody>
                    </table>
				<?php } else { ?>
                    <p style="text-align: center;"><?php esc_html_e('No Record Found.', 'tour-booking-manager'); ?></p>
					<?php
				}
				//echo '<pre>'; print_r( $all_orders ); echo '</pre>';
			}
			public function ttbm_trash_post() {
				if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( $_REQUEST['nonce'], 'edd_sample_nonce' ) ) {
					die();
				}
				if (current_user_can('administrator')) {
					if (get_post_type($_REQUEST['post_id']) == TTBM_Function::get_cpt_name()) {
						$post_id = isset($_REQUEST['post_id']) ? MP_Global_Function::data_sanitize($_REQUEST['post_id']) : '';
						if ($post_id > 0) {
							$args = array('post_type' => array('ttbm_tour'), 'posts_per_page' => -1, 'p' => $post_id, 'post_status' => 'publish');
							$loop = new WP_Query($args);
							if ($loop->found_posts) {
								$current_post = get_post($post_id, 'ARRAY_A');
								$current_post['post_status'] = 'trash';
								wp_update_post($current_post);
							}
						}
					}
				} else {
					echo "You don't have the permissions to delete the post";
				}
				die();
			}
		}
		new TTBM_Admin_Tour_List();
	}
