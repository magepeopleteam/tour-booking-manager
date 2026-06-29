<?php
	/**
	 * Tour List → extra CPT tabs (Guides & Ticket Types).
	 *
	 * Adds two tabs to the Tour List dashboard (edit.php?post_type=ttbm_tour&page=ttbm_list)
	 * using the same extension hooks the Wishlist tab uses, so no core file is modified.
	 * Each tab lists its CPT entries as cards and offers full add/edit/delete through the
	 * shared modal holder (#ttbm_travel_list_popup) reusing the existing popup design.
	 *
	 * The matching JS lives in assets/admin/ttbm_hotel_booking.js (same IIFE that already
	 * drives the taxonomy tabs) and the styling in assets/admin/ttbm_list_cpt_tabs.css.
	 *
	 * @package Tour Booking Manager
	 */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}
	if ( ! class_exists( 'TTBM_Travel_List_CPT_Tabs' ) ) {
		class TTBM_Travel_List_CPT_Tabs {

			public function __construct() {
				// Tab buttons + content panels via the Tour List extension hooks.
				add_action( 'ttbm_travel_lists_tab_button', array( $this, 'add_tab_buttons' ), 15, 1 );
				add_action( 'ttbm_travel_lists_tab_display', array( $this, 'add_tab_content' ), 30, 3 );

				// List loaders.
				add_action( 'wp_ajax_ttbm_get_guides_html', array( $this, 'ajax_get_guides_html' ) );
				add_action( 'wp_ajax_ttbm_get_ticket_types_html', array( $this, 'ajax_get_ticket_types_html' ) );

				// Add/Edit modal HTML.
				add_action( 'wp_ajax_ttbm_guide_form_html', array( $this, 'ajax_guide_form_html' ) );
				add_action( 'wp_ajax_ttbm_ticket_type_form_html', array( $this, 'ajax_ticket_type_form_html' ) );

				// Save handlers.
				add_action( 'wp_ajax_ttbm_save_guide', array( $this, 'ajax_save_guide' ) );
				add_action( 'wp_ajax_ttbm_save_ticket_type', array( $this, 'ajax_save_ticket_type' ) );

				// Delete (shared for the CPTs managed from the Tour List).
				add_action( 'wp_ajax_ttbm_delete_cpt_item', array( $this, 'ajax_delete_cpt_item' ) );

				// Hide the now-redundant CPT submenus and enqueue assets.
				add_action( 'admin_menu', array( $this, 'hide_cpt_submenus' ), 999 );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ), 100 );
			}

			/* ─────────────────────────── Helpers ─────────────────────────── */

			private function verify_request() {
				if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_admin_nonce' ) ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Invalid nonce', 'tour-booking-manager' ) ), 403 );
				}
				if ( ! current_user_can( 'manage_options' ) ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized access', 'tour-booking-manager' ) ), 403 );
				}
			}

			/* ─────────────────────────── Tab Buttons ─────────────────────── */

			public function add_tab_buttons( $label ) {
				?>
				<button data-target="ttbm_trvel_lists_guides" data-tab-type="Guides">
					<span class="icon-wrap"><i class="mi mi-guide"></i><?php esc_html_e( ' Guides', 'tour-booking-manager' ); ?></span>
				</button>
				<button data-target="ttbm_trvel_lists_ticket_types" data-tab-type="Ticket Types">
					<span class="icon-wrap"><i class="mi mi-ticket"></i><?php esc_html_e( ' Ticket Types', 'tour-booking-manager' ); ?></span>
				</button>
				<?php
			}

			/* ─────────────────────────── Tab Panels ──────────────────────── */

			public function add_tab_content( $label, $analytics_Data, $posts_query ) {
				?>
				<div id="ttbm_trvel_lists_guides" class="ttbm_trvel_lists_content">
					<?php
					$this->tab_header(
						esc_html__( 'Guides', 'tour-booking-manager' ),
						esc_html__( 'Add New Guide', 'tour-booking-manager' ),
						'ttbm-add-guide-btn',
						'ttbm_guides_search',
						esc_html__( 'Search Guides', 'tour-booking-manager' )
					);
					?>
					<div class="ttbm_travel_list_guides_content" id="ttbm_travel_list_guides_content">
						<div class="ttbm_travel_content_loader"><?php echo TTBM_Travel_List_Tab_Details::travel_list_content_skeleton(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					</div>
				</div>

				<div id="ttbm_trvel_lists_ticket_types" class="ttbm_trvel_lists_content">
					<?php
					$this->tab_header(
						esc_html__( 'Ticket Types', 'tour-booking-manager' ),
						esc_html__( 'Add New Ticket Type', 'tour-booking-manager' ),
						'ttbm-add-ticket-type-btn',
						'ttbm_ticket_types_search',
						esc_html__( 'Search Ticket Types', 'tour-booking-manager' )
					);
					?>
					<div class="ttbm_travel_list_ticket_types_content" id="ttbm_travel_list_ticket_types_content">
						<div class="ttbm_travel_content_loader"><?php echo TTBM_Travel_List_Tab_Details::travel_list_content_skeleton(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					</div>
				</div>
				<?php
			}

			/**
			 * Compact tab header (title + add button + live search), styled to match
			 * the taxonomy tab toolbar but with dedicated, non-colliding classes.
			 */
			private function tab_header( $title, $add_btn_text, $add_btn_class, $search_id, $search_placeholder ) {
				?>
				<div class="ttbm-tour-list-header">
					<div class="ttbm_tab_header_shortcode_title">
						<h1 class="page-title"><?php echo esc_html( $title ); ?></h1>
					</div>
					<div class="ttbm_tour_search_add_holder ttbm_travel_taxonomy_toolbar">
						<button type="button" class="page-title-action ttbm_travel_taxonomy_add_btn <?php echo esc_attr( $add_btn_class ); ?>">
							<span class="ttbm_travel_taxonomy_add_btn__icon" aria-hidden="true"><i class="fas fa-plus"></i></span>
							<span class="ttbm_travel_taxonomy_add_btn__text"><?php echo esc_html( $add_btn_text ); ?></span>
						</button>
						<div class="ttbm_travel_taxonomy_search_wrap">
							<i class="fas fa-search ttbm_travel_taxonomy_search_icon" aria-hidden="true"></i>
							<input type="search" class="ttbm_travel_taxonomy_search_input" id="<?php echo esc_attr( $search_id ); ?>" placeholder="<?php echo esc_attr( $search_placeholder ); ?>" autocomplete="off">
						</div>
					</div>
				</div>
				<?php
			}

			/* ─────────────────────────── Guides: list ────────────────────── */

			public function ajax_get_guides_html() {
				$this->verify_request();
				$query = new WP_Query(
					array(
						'post_type'      => 'ttbm_guide',
						'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
						'posts_per_page' => -1,
						'orderby'        => 'date',
						'order'          => 'DESC',
						'no_found_rows'  => true,
					)
				);
				ob_start();
				if ( $query->have_posts() ) {
					echo '<div class="ttbm-locations-list">';
					while ( $query->have_posts() ) {
						$query->the_post();
						$post_id = get_the_ID();
						$name    = get_the_title();
						$desc    = wp_trim_words( get_the_excerpt(), 20 );
						$img_url = get_the_post_thumbnail_url( $post_id, 'thumbnail' );
						if ( ! $img_url ) {
							$img_url = 'https://i.imgur.com/GD3zKtz.png';
						}
						?>
						<div class="ttbm-location-card ttbm-cpt-card ttbm_search_guide_by_title" data-taxonomy="<?php echo esc_attr( $name ); ?>">
							<div class="ttbm-card-left">
								<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="ttbm-location-thumb" width="70" height="70" />
							</div>
							<div class="ttbm-card-right">
								<h3 class="ttbm-title"><?php echo esc_html( $name ); ?></h3>
								<?php if ( $desc ) : ?>
									<p class="ttbm-description"><?php echo esc_html( $desc ); ?></p>
								<?php endif; ?>
							</div>
							<div class="ttbm-card-actions">
								<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" target="_blank"><button class="ttbm-btn ttbm-view-btn"><i class="fas fa-eye"></i></button></a>
								<button class="ttbm-btn ttbm-edit-btn ttbm-cpt-edit-btn" data-cpt="guide" data-id="<?php echo esc_attr( $post_id ); ?>"><i class="fas fa-edit"></i></button>
								<button class="ttbm-btn ttbm-delete-btn ttbm-cpt-delete-btn" data-cpt="guide" data-id="<?php echo esc_attr( $post_id ); ?>"><i class="fas fa-trash-alt"></i></button>
							</div>
						</div>
						<?php
					}
					echo '</div>';
					wp_reset_postdata();
				} else {
					echo '<p class="ttbm-cpt-empty">' . esc_html__( 'No guides found.', 'tour-booking-manager' ) . '</p>';
				}
				wp_send_json_success( array( 'html' => ob_get_clean() ) );
			}

			/* ─────────────────────────── Guides: form ────────────────────── */

			public function ajax_guide_form_html() {
				$this->verify_request();
				$post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
				$name    = '';
				$desc    = '';
				$img_id  = '';
				$img_url = '';
				if ( $post_id ) {
					$post = get_post( $post_id );
					if ( $post && 'ttbm_guide' === $post->post_type ) {
						$name   = $post->post_title;
						$desc   = $post->post_content;
						$img_id = get_post_thumbnail_id( $post_id );
						if ( $img_id ) {
							$img_url = wp_get_attachment_image_url( $img_id, 'thumbnail' );
						}
					}
				}
				$is_edit     = $post_id > 0;
				$title       = $is_edit ? esc_html__( 'Edit Guide', 'tour-booking-manager' ) : esc_html__( 'Add New Guide', 'tour-booking-manager' );
				$button_name = $is_edit ? esc_html__( 'Update', 'tour-booking-manager' ) : esc_html__( 'Save', 'tour-booking-manager' );
				ob_start();
				?>
				<div id="ttbm-location-popup" class="ttbm-popup-overlay" style="display:flex;">
					<div class="ttbm-popup-box ttbm-taxonomy-popup" role="dialog" aria-modal="true" aria-labelledby="ttbm-cpt-popup-title">
						<div class="ttbm-taxonomy-popup__header">
							<h3 id="ttbm-cpt-popup-title" class="ttbm-taxonomy-popup__title"><?php echo esc_html( $title ); ?></h3>
							<button type="button" id="ttbm-close-popup" class="ttbm-taxonomy-popup__close ttbm-taxonomy-popup__cancel" aria-label="<?php esc_attr_e( 'Close', 'tour-booking-manager' ); ?>">
								<span class="fas fa-times" aria-hidden="true"></span>
							</button>
						</div>
						<div class="ttbm-taxonomy-popup__body">
							<div class="ttbm-taxonomy-popup-fields" id="ttbm-guide-form">
								<input type="hidden" id="ttbm-cpt-post-id" value="<?php echo esc_attr( $post_id ); ?>">
								<?php
								TTBM_Travel_List_Tab_Details::taxonomy_popup_field(
									esc_html__( 'Name', 'tour-booking-manager' ),
									'<input type="text" class="ttbm-taxonomy-popup-input" id="ttbm-guide-name" value="' . esc_attr( $name ) . '" placeholder="' . esc_attr__( 'e.g. John Doe', 'tour-booking-manager' ) . '">',
									'ttbm-guide-name'
								);
								// Reuse the taxonomy image upload field ids so the existing media handler works.
								ob_start();
								?>
								<div class="ttbm-taxonomy-popup-image">
									<button type="button" id="ttbm-upload-image" class="ttbm-taxonomy-popup__btn ttbm-taxonomy-popup__btn--secondary"><?php esc_html_e( 'Upload Image', 'tour-booking-manager' ); ?></button>
									<input type="hidden" id="ttbm-location-image-id" value="<?php echo esc_attr( $img_id ); ?>">
									<div id="ttbm-image-preview" class="ttbm-taxonomy-popup-image-preview">
										<?php if ( $img_url ) : ?>
											<img src="<?php echo esc_url( $img_url ); ?>" alt="">
										<?php endif; ?>
									</div>
								</div>
								<?php
								TTBM_Travel_List_Tab_Details::taxonomy_popup_field( esc_html__( 'Photo', 'tour-booking-manager' ), ob_get_clean() );
								ob_start();
								?>
								<textarea class="ttbm-taxonomy-popup-input ttbm-taxonomy-popup-textarea" id="ttbm-guide-desc" rows="5" placeholder="<?php esc_attr_e( 'Guide bio / description…', 'tour-booking-manager' ); ?>"><?php echo esc_textarea( $desc ); ?></textarea>
								<?php
								TTBM_Travel_List_Tab_Details::taxonomy_popup_field( esc_html__( 'Description', 'tour-booking-manager' ), ob_get_clean(), 'ttbm-guide-desc' );
								?>
							</div>
						</div>
						<div class="ttbm-taxonomy-popup__footer">
							<p class="ttbm-taxonomy-popup-form-message" role="alert" aria-live="polite" hidden></p>
							<div class="ttbm-popup-buttons">
								<button type="button" id="ttbm-close-popup-footer" class="ttbm-taxonomy-popup__btn ttbm-taxonomy-popup__btn--secondary ttbm-taxonomy-popup__cancel"><?php esc_html_e( 'Cancel', 'tour-booking-manager' ); ?></button>
								<button type="button" class="ttbm-taxonomy-popup__btn ttbm-taxonomy-popup__btn--primary ttbm-save-guide"><?php echo esc_html( $button_name ); ?></button>
							</div>
						</div>
					</div>
				</div>
				<?php
				wp_send_json_success( array( 'html' => ob_get_clean() ) );
			}

			public function ajax_save_guide() {
				$this->verify_request();
				$post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
				$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
				$desc    = isset( $_POST['description'] ) ? wp_kses_post( wp_unslash( $_POST['description'] ) ) : '';
				$img_id  = isset( $_POST['image_id'] ) ? absint( wp_unslash( $_POST['image_id'] ) ) : 0;

				if ( '' === $name ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Name is required.', 'tour-booking-manager' ) ) );
				}

				$postarr = array(
					'post_title'   => $name,
					'post_content' => $desc,
					'post_type'    => 'ttbm_guide',
				);
				if ( $post_id && get_post( $post_id ) && 'ttbm_guide' === get_post_type( $post_id ) ) {
					$postarr['ID'] = $post_id;
					$result        = wp_update_post( $postarr, true );
				} else {
					$postarr['post_status'] = 'publish';
					$result                 = wp_insert_post( $postarr, true );
					$post_id                = is_wp_error( $result ) ? 0 : $result;
				}
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( array( 'message' => $result->get_error_message() ) );
				}
				if ( $img_id ) {
					set_post_thumbnail( $post_id, $img_id );
				} else {
					delete_post_thumbnail( $post_id );
				}
				wp_send_json_success( array( 'post_id' => $post_id ) );
			}

			/* ─────────────────────── Ticket Types: list ──────────────────── */

			public function ajax_get_ticket_types_html() {
				$this->verify_request();
				$query = new WP_Query(
					array(
						'post_type'      => 'ttbm_ticket_types',
						'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
						'posts_per_page' => -1,
						'orderby'        => 'date',
						'order'          => 'DESC',
						'no_found_rows'  => true,
					)
				);
				ob_start();
				if ( $query->have_posts() ) {
					echo '<div class="ttbm-taxonomy-list-holder">';
					while ( $query->have_posts() ) {
						$query->the_post();
						$post_id = get_the_ID();
						$name    = get_the_title();
						$rows    = TTBM_Global_Function::get_post_info( $post_id, 'ttbm_ticket_type', array() );
						$rows    = is_array( $rows ) ? $rows : array();
						$names   = array();
						foreach ( $rows as $row ) {
							if ( ! empty( $row['ticket_type_name'] ) ) {
								$names[] = $row['ticket_type_name'];
							}
						}
						$summary = empty( $names )
							? esc_html__( 'No ticket rows yet.', 'tour-booking-manager' )
							: sprintf(
								/* translators: 1: number of ticket rows, 2: comma separated names */
								esc_html__( '%1$d ticket type(s): %2$s', 'tour-booking-manager' ),
								count( $names ),
								implode( ', ', array_slice( $names, 0, 5 ) )
							);
						?>
						<div class="ttbm-taxonomy-card ttbm-cpt-card ttbm_search_ticket_type_by_title" data-taxonomy="<?php echo esc_attr( $name ); ?>">
							<div class="ttbm-card-right">
								<div class="ttbm-title-row">
									<h3 class="ttbm-title"><i class="fas fa-ticket-alt"></i> <?php echo esc_html( $name ); ?> <span class="ttbm-term-id-badge">(ID: <?php echo esc_attr( $post_id ); ?>)</span></h3>
									<div class="ttbm-taxonomy-card-actions">
										<button class="ttbm-btn ttbm-edit-btn ttbm-cpt-edit-btn" data-cpt="ticket_types" data-id="<?php echo esc_attr( $post_id ); ?>"><i class="fas fa-edit"></i></button>
										<button class="ttbm-btn ttbm-delete-btn ttbm-cpt-delete-btn" data-cpt="ticket_types" data-id="<?php echo esc_attr( $post_id ); ?>"><i class="fas fa-trash-alt"></i></button>
									</div>
								</div>
								<p class="ttbm-description"><?php echo esc_html( $summary ); ?></p>
							</div>
						</div>
						<?php
					}
					echo '</div>';
					wp_reset_postdata();
				} else {
					echo '<p class="ttbm-cpt-empty">' . esc_html__( 'No ticket types found.', 'tour-booking-manager' ) . '</p>';
				}
				wp_send_json_success( array( 'html' => ob_get_clean() ) );
			}

			/* ─────────────────────── Ticket Types: form ──────────────────── */

			public function ajax_ticket_type_form_html() {
				$this->verify_request();
				$post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
				$name    = '';
				$rows    = array();
				if ( $post_id ) {
					$post = get_post( $post_id );
					if ( $post && 'ttbm_ticket_types' === $post->post_type ) {
						$name = $post->post_title;
						$rows = TTBM_Global_Function::get_post_info( $post_id, 'ttbm_ticket_type', array() );
						$rows = is_array( $rows ) ? $rows : array();
					}
				}
				$is_edit     = $post_id > 0;
				$title       = $is_edit ? esc_html__( 'Edit Ticket Type', 'tour-booking-manager' ) : esc_html__( 'Add New Ticket Type', 'tour-booking-manager' );
				$button_name = $is_edit ? esc_html__( 'Update', 'tour-booking-manager' ) : esc_html__( 'Save', 'tour-booking-manager' );
				ob_start();
				?>
				<div id="ttbm-location-popup" class="ttbm-popup-overlay" style="display:flex;">
					<div class="ttbm-popup-box ttbm-taxonomy-popup ttbm-ticket-type-popup" role="dialog" aria-modal="true" aria-labelledby="ttbm-cpt-popup-title">
						<div class="ttbm-taxonomy-popup__header">
							<h3 id="ttbm-cpt-popup-title" class="ttbm-taxonomy-popup__title"><?php echo esc_html( $title ); ?></h3>
							<button type="button" id="ttbm-close-popup" class="ttbm-taxonomy-popup__close ttbm-taxonomy-popup__cancel" aria-label="<?php esc_attr_e( 'Close', 'tour-booking-manager' ); ?>">
								<span class="fas fa-times" aria-hidden="true"></span>
							</button>
						</div>
						<div class="ttbm-taxonomy-popup__body">
							<div class="ttbm-taxonomy-popup-fields" id="ttbm-ticket-type-form">
								<input type="hidden" id="ttbm-cpt-post-id" value="<?php echo esc_attr( $post_id ); ?>">
								<?php
								TTBM_Travel_List_Tab_Details::taxonomy_popup_field(
									esc_html__( 'Ticket Type Set Name', 'tour-booking-manager' ),
									'<input type="text" class="ttbm-taxonomy-popup-input" id="ttbm-ticket-type-name" value="' . esc_attr( $name ) . '" placeholder="' . esc_attr__( 'e.g. Standard Pricing', 'tour-booking-manager' ) . '">',
									'ttbm-ticket-type-name'
								);
								?>
								<div class="ttbm-taxonomy-popup-field">
									<label class="ttbm-taxonomy-popup-label"><?php esc_html_e( 'Pricing', 'tour-booking-manager' ); ?></label>
									<div class="ttbm_style ttbm-ticket-type-repeater">
										<div class="ttbm_settings_area">
											<div class="ttbm-ticket-type-table-scroll">
												<table class="price_config_table">
													<thead>
														<tr>
															<th><?php esc_html_e( 'Icon', 'tour-booking-manager' ); ?></th>
															<th><?php esc_html_e( 'Ticket Type', 'tour-booking-manager' ); ?><span class="textRequired">&nbsp;*</span></th>
															<th><?php esc_html_e( 'Short Description', 'tour-booking-manager' ); ?></th>
															<th><?php esc_html_e( 'Regular Price', 'tour-booking-manager' ); ?><span class="textRequired">&nbsp;*</span></th>
															<th><?php esc_html_e( 'Sale Price', 'tour-booking-manager' ); ?></th>
															<th><?php esc_html_e( 'Capacity', 'tour-booking-manager' ); ?><span class="textRequired">&nbsp;*</span></th>
															<th><?php esc_html_e( 'Default Qty', 'tour-booking-manager' ); ?></th>
															<th><?php esc_html_e( 'Reserve Qty', 'tour-booking-manager' ); ?></th>
															<th><?php esc_html_e( 'Qty Box Type', 'tour-booking-manager' ); ?></th>
															<th><?php esc_html_e( 'Action', 'tour-booking-manager' ); ?></th>
														</tr>
													</thead>
													<tbody class="ttbm_sortable_area ttbm_item_insert ttbm_insert_ticket_type">
														<?php
														wp_nonce_field( 'ttbm_ticket_item_nonce', 'ttbm_ticket_item_nonce' );
														if ( ! empty( $rows ) ) {
															foreach ( $rows as $field ) {
																// Reuse the existing row renderer (registered by TTBM_Ticket_Types).
																do_action( 'ttbm_ticket_item', $field );
															}
														} else {
															do_action( 'ttbm_ticket_item', array() );
														}
														?>
													</tbody>
												</table>
											</div>
											<?php TTBM_Custom_Layout::add_new_button( esc_html__( 'Add New Ticket Type', 'tour-booking-manager' ) ); ?>
											<?php do_action( 'add_ttbm_hidden_table', 'ttbm_ticket_item' ); ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="ttbm-taxonomy-popup__footer">
							<p class="ttbm-taxonomy-popup-form-message" role="alert" aria-live="polite" hidden></p>
							<div class="ttbm-popup-buttons">
								<button type="button" id="ttbm-close-popup-footer" class="ttbm-taxonomy-popup__btn ttbm-taxonomy-popup__btn--secondary ttbm-taxonomy-popup__cancel"><?php esc_html_e( 'Cancel', 'tour-booking-manager' ); ?></button>
								<button type="button" class="ttbm-taxonomy-popup__btn ttbm-taxonomy-popup__btn--primary ttbm-save-ticket-type"><?php echo esc_html( $button_name ); ?></button>
							</div>
						</div>
					</div>
				</div>
				<?php
				wp_send_json_success( array( 'html' => ob_get_clean() ) );
			}

			public function ajax_save_ticket_type() {
				$this->verify_request();
				$post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
				$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

				if ( '' === $name ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Ticket type set name is required.', 'tour-booking-manager' ) ) );
				}

				if ( $post_id && get_post( $post_id ) && 'ttbm_ticket_types' === get_post_type( $post_id ) ) {
					$result = wp_update_post(
						array(
							'ID'         => $post_id,
							'post_title' => $name,
						),
						true
					);
				} else {
					$result  = wp_insert_post(
						array(
							'post_title'  => $name,
							'post_type'   => 'ttbm_ticket_types',
							'post_status' => 'publish',
						),
						true
					);
					$post_id = is_wp_error( $result ) ? 0 : $result;
				}
				if ( is_wp_error( $result ) ) {
					wp_send_json_error( array( 'message' => $result->get_error_message() ) );
				}

				// Build the repeater meta using the same shape/sanitisation as TTBM_Ticket_Types::save_ticket_types().
				$new_ticket_type = array();
				$icon            = isset( $_POST['ticket_type_icon'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['ticket_type_icon'] ) ) : array();
				$names           = isset( $_POST['ticket_type_name'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['ticket_type_name'] ) ) : array();
				$ticket_price    = isset( $_POST['ticket_type_price'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['ticket_type_price'] ) ) : array();
				$sale_price      = isset( $_POST['ticket_type_sale_price'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['ticket_type_sale_price'] ) ) : array();
				$qty             = isset( $_POST['ticket_type_qty'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['ticket_type_qty'] ) ) : array();
				$default_qty     = isset( $_POST['ticket_type_default_qty'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['ticket_type_default_qty'] ) ) : array();
				$rsv             = isset( $_POST['ticket_type_resv_qty'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['ticket_type_resv_qty'] ) ) : array();
				$qty_type        = isset( $_POST['ticket_type_qty_type'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['ticket_type_qty_type'] ) ) : array();
				$description     = isset( $_POST['ticket_type_description'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['ticket_type_description'] ) ) : array();

				$count = count( $names );
				for ( $i = 0; $i < $count; $i++ ) {
					if ( ! empty( $names[ $i ] ) && isset( $ticket_price[ $i ] ) && '' !== $ticket_price[ $i ] && $ticket_price[ $i ] >= 0 ) {
						$new_ticket_type[] = array(
							'ticket_type_icon'        => $icon[ $i ] ?? '',
							'ticket_type_name'        => $names[ $i ],
							'ticket_type_price'       => $ticket_price[ $i ],
							'sale_price'              => $sale_price[ $i ] ?? '',
							'ticket_type_qty'         => ! empty( $qty[ $i ] ) ? $qty[ $i ] : 0,
							'ticket_type_default_qty' => $default_qty[ $i ] ?? 0,
							'ticket_type_resv_qty'    => $rsv[ $i ] ?? 0,
							'ticket_type_qty_type'    => $qty_type[ $i ] ?? 'inputbox',
							'ticket_type_description' => $description[ $i ] ?? '',
						);
					}
				}
				$new_ticket_type = apply_filters( 'ttbm_ticket_type_arr_save', $new_ticket_type );
				update_post_meta( $post_id, 'ttbm_ticket_type', $new_ticket_type );

				wp_send_json_success( array( 'post_id' => $post_id ) );
			}

			/* ─────────────────────────── Delete ──────────────────────────── */

			public function ajax_delete_cpt_item() {
				$this->verify_request();
				$post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : 0;
				$cpt     = isset( $_POST['cpt'] ) ? sanitize_key( wp_unslash( $_POST['cpt'] ) ) : '';
				$map     = array(
					'guide'        => 'ttbm_guide',
					'ticket_types' => 'ttbm_ticket_types',
				);
				if ( ! $post_id || ! isset( $map[ $cpt ] ) ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Invalid request.', 'tour-booking-manager' ) ) );
				}
				if ( get_post_type( $post_id ) !== $map[ $cpt ] || ! current_user_can( 'delete_post', $post_id ) ) {
					wp_send_json_error( array( 'message' => esc_html__( 'Invalid or unauthorized request.', 'tour-booking-manager' ) ) );
				}
				$result = wp_trash_post( $post_id );
				if ( $result ) {
					wp_send_json_success( array( 'message' => esc_html__( 'Item moved to trash.', 'tour-booking-manager' ), 'deleted_id' => $post_id ) );
				}
				wp_send_json_error( array( 'message' => esc_html__( 'Failed to delete item.', 'tour-booking-manager' ) ) );
			}

			/* ─────────────────────── Menu + Assets ───────────────────────── */

			public function hide_cpt_submenus() {
				remove_submenu_page( 'edit.php?post_type=ttbm_tour', 'edit.php?post_type=ttbm_guide' );
				remove_submenu_page( 'edit.php?post_type=ttbm_tour', 'edit.php?post_type=ttbm_ticket_types' );
			}

			public function enqueue_assets( $hook ) {
				if ( 'ttbm_tour_page_ttbm_list' !== $hook ) {
					return;
				}
				$css = TTBM_PLUGIN_DIR . '/assets/admin/ttbm_list_cpt_tabs.css';
				if ( file_exists( $css ) ) {
					wp_enqueue_style( 'ttbm_list_cpt_tabs', TTBM_PLUGIN_URL . '/assets/admin/ttbm_list_cpt_tabs.css', array( 'ttbm_admin_modern' ), filemtime( $css ) );
				}
			}
		}
		new TTBM_Travel_List_CPT_Tabs();
	}
