<?php
/**
 * Admin Wishlist — integrated as a tab in Tour List page.
 * @package Tour Booking Manager
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
if ( ! class_exists( 'TTBM_Admin_Wishlist' ) ) {
	class TTBM_Admin_Wishlist {

		public function __construct() {
			// Add wishlist tab button to the Tour List tab bar
			add_action( 'ttbm_travel_lists_tab_button', array( $this, 'add_tab_button' ), 10, 1 );
			// Add wishlist content panel inside the tab holder
			add_action( 'ttbm_travel_lists_tab_display', array( $this, 'add_tab_content' ), 20, 3 );
			// AJAX handler for filtering
			add_action( 'wp_ajax_ttbm_wishlist_admin_data', array( $this, 'ajax_get_data' ) );
			// Enqueue admin styles
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		}

		// ─── Tab Button ───────────────────────────────────────────
		public function add_tab_button( $label ) {
			?>
			<button data-target="ttbm_trvel_lists_wishlist" data-tab-type="Wishlist">
				<span class="icon-wrap"><i class="mi mi-heart"></i><?php esc_html_e( ' Wishlist', 'tour-booking-manager' ); ?></span>
			</button>
			<?php
		}

		// ─── Tab Content Panel ────────────────────────────────────
		public function add_tab_content( $label, $analytics_Data, $posts_query ) {
			$data = $this->get_wishlist_data();
			?>
			<div id="ttbm_trvel_lists_wishlist" class="ttbm_trvel_lists_content">
				<?php $this->render_wishlist_table( $data ); ?>
			</div>
			<?php
		}

		// ─── Admin Styles ─────────────────────────────────────────
		public function admin_styles( $hook ) {
			if ( $hook !== 'ttbm_tour_page_ttbm_list' ) {
				return;
			}
			$ver = filemtime( TTBM_PLUGIN_DIR . '/admin/ttbm-wishlist-admin.css' );
			wp_enqueue_style( 'ttbm-wishlist-admin', TTBM_PLUGIN_URL . '/admin/ttbm-wishlist-admin.css', array(), $ver );
		}

		// ─── Data Gathering ───────────────────────────────────────
		private function get_wishlist_data( $search = '', $tour_id = 0, $paged = 1, $per_page = 20 ) {
			global $wpdb;

			$user_ids = $wpdb->get_col( $wpdb->prepare(
				"SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s",
				'ttbm_wishlist'
			) );

			$rows = array();

			if ( ! empty( $user_ids ) ) {
				foreach ( $user_ids as $uid ) {
					$list = get_user_meta( $uid, 'ttbm_wishlist', true );
					if ( ! is_array( $list ) || empty( $list ) ) {
						continue;
					}
					$user = get_userdata( $uid );
					if ( ! $user ) {
						continue;
					}
					// Filter by search term (name or email)
					if ( $search ) {
						$haystack = strtolower( $user->display_name . ' ' . $user->user_email . ' ' . $user->first_name . ' ' . $user->last_name );
						if ( strpos( $haystack, strtolower( $search ) ) === false ) {
							continue;
						}
					}

					foreach ( $list as $tid ) {
						if ( $tour_id && absint( $tid ) !== $tour_id ) {
							continue;
						}
						$tour = get_post( $tid );
						if ( ! $tour ) {
							continue;
						}

						$rows[] = array(
							'user_id'          => $uid,
							'display_name'     => $user->display_name,
							'user_email'       => $user->user_email,
							'first_name'       => $user->first_name,
							'last_name'        => $user->last_name,
							'billing_first'    => get_user_meta( $uid, 'billing_first_name', true ) ?: $user->first_name,
							'billing_last'     => get_user_meta( $uid, 'billing_last_name', true ) ?: $user->last_name,
							'billing_phone'    => get_user_meta( $uid, 'billing_phone', true ),
							'billing_city'     => get_user_meta( $uid, 'billing_city', true ),
							'billing_state'    => get_user_meta( $uid, 'billing_state', true ),
							'billing_country'  => get_user_meta( $uid, 'billing_country', true ),
							'billing_postcode' => get_user_meta( $uid, 'billing_postcode', true ),
							'billing_address'  => trim( get_user_meta( $uid, 'billing_address_1', true ) . ' ' . get_user_meta( $uid, 'billing_address_2', true ) ),
							'tour_id'          => $tid,
							'tour_title'       => $tour->post_title,
							'tour_status'      => $tour->post_status,
							'tour_edit_link'   => admin_url( 'post.php?post=' . $tid . '&action=edit' ),
						);
					}
				}
			}

			$total       = count( $rows );
			$total_pages = max( 1, (int) ceil( $total / $per_page ) );
			$rows        = array_slice( $rows, ( $paged - 1 ) * $per_page, $per_page );

			// Tours for dropdown filter
			$tours = get_posts( array(
				'post_type'      => 'ttbm_tour',
				'post_status'    => array( 'publish', 'draft', 'pending' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'orderby'        => 'title',
				'order'          => 'ASC',
			) );

			$tour_options = array();
			foreach ( $tours as $tid ) {
				$tour_options[] = array(
					'id'    => $tid,
					'title' => get_the_title( $tid ),
				);
			}

			return array(
				'rows'         => $rows,
				'total'        => $total,
				'paged'        => $paged,
				'per_page'     => $per_page,
				'total_pages'  => $total_pages,
				'tour_options' => $tour_options,
			);
		}

		// ─── AJAX Data ─────────────────────────────────────────────
		public function ajax_get_data() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_admin_nonce' ) ) {
				wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
			}

			$search  = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
			$tour_id = isset( $_POST['tour_id'] ) ? absint( $_POST['tour_id'] ) : 0;
			$paged   = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;

			$data = $this->get_wishlist_data( $search, $tour_id, $paged );

			ob_start();
			$this->render_wishlist_table( $data );
			$html = ob_get_clean();

			wp_send_json_success( array( 'html' => $html, 'total' => $data['total'] ) );
		}

		// ─── Render Table ──────────────────────────────────────────
		private function render_wishlist_table( $data ) {
			$search  = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
			$tour_id = isset( $_GET['tour_id'] ) ? absint( $_GET['tour_id'] ) : 0;
			?>
			<div class="ttbm-wishlist-admin-wrap">
				<div class="ttbm-wishlist-header">
					<h2><i class="mi mi-heart"></i> <?php esc_html_e( 'Customer Wishlists', 'tour-booking-manager' ); ?> <span class="ttbm-wishlist-count">(<?php echo esc_html( $data['total'] ); ?>)</span></h2>
				</div>

				<div class="ttbm-wishlist-filters">
					<input type="text" id="ttbm-wishlist-search" placeholder="<?php esc_attr_e( 'Search by name or email…', 'tour-booking-manager' ); ?>" value="" class="ttbm-wishlist-filter-input">
					<select id="ttbm-wishlist-tour-filter" class="ttbm-wishlist-filter-select">
						<option value="0"><?php esc_html_e( 'All Tours', 'tour-booking-manager' ); ?></option>
						<?php foreach ( $data['tour_options'] as $topt ) : ?>
							<option value="<?php echo esc_attr( $topt['id'] ); ?>"><?php echo esc_html( $topt['title'] ); ?></option>
						<?php endforeach; ?>
					</select>
					<button type="button" id="ttbm-wishlist-filter-btn" class="button button-primary"><i class="fas fa-search"></i> <?php esc_html_e( 'Filter', 'tour-booking-manager' ); ?></button>
					<button type="button" id="ttbm-wishlist-reset-btn" class="button"><?php esc_html_e( 'Reset', 'tour-booking-manager' ); ?></button>
				</div>

				<?php if ( ! empty( $data['rows'] ) ) : ?>
				<table class="wp-list-table widefat fixed striped ttbm-wishlist-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Customer', 'tour-booking-manager' ); ?></th>
							<th><?php esc_html_e( 'Email', 'tour-booking-manager' ); ?></th>
							<th><?php esc_html_e( 'Phone', 'tour-booking-manager' ); ?></th>
							<th><?php esc_html_e( 'Address', 'tour-booking-manager' ); ?></th>
							<th><?php esc_html_e( 'City', 'tour-booking-manager' ); ?></th>
							<th><?php esc_html_e( 'Country', 'tour-booking-manager' ); ?></th>
							<th><?php esc_html_e( 'Postcode', 'tour-booking-manager' ); ?></th>
							<th><?php esc_html_e( 'Tour', 'tour-booking-manager' ); ?></th>
							<th><?php esc_html_e( 'Status', 'tour-booking-manager' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $data['rows'] as $row ) :
							$country_name = '';
							if ( $row['billing_country'] && function_exists( 'WC' ) ) {
								$countries = WC()->countries->get_countries();
								$country_name = isset( $countries[ $row['billing_country'] ] ) ? $countries[ $row['billing_country'] ] : $row['billing_country'];
							} else {
								$country_name = $row['billing_country'];
							}
						?>
						<tr>
							<td>
								<a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . $row['user_id'] ) ); ?>">
									<strong><?php echo esc_html( $row['billing_first'] . ' ' . $row['billing_last'] ); ?></strong>
								</a>
								<?php if ( $row['display_name'] !== trim( $row['billing_first'] . ' ' . $row['billing_last'] ) ) : ?>
									<br><small class="ttbm-subtle"><?php echo esc_html( '@' . $row['display_name'] ); ?></small>
								<?php endif; ?>
							</td>
							<td><a href="mailto:<?php echo esc_attr( $row['user_email'] ); ?>"><?php echo esc_html( $row['user_email'] ); ?></a></td>
							<td><?php echo esc_html( $row['billing_phone'] ); ?></td>
							<td><?php echo esc_html( $row['billing_address'] ); ?></td>
							<td><?php echo esc_html( $row['billing_city'] ); ?></td>
							<td><?php echo esc_html( $country_name ); ?></td>
							<td><?php echo esc_html( $row['billing_postcode'] ); ?></td>
							<td><a href="<?php echo esc_url( $row['tour_edit_link'] ); ?>"><?php echo esc_html( $row['tour_title'] ); ?></a></td>
							<td>
								<?php
								$status_map = array(
									'publish' => array( 'ttbm-status-publish', __( 'Published', 'tour-booking-manager' ) ),
									'draft'   => array( 'ttbm-status-draft',   __( 'Draft', 'tour-booking-manager' ) ),
									'pending' => array( 'ttbm-status-pending', __( 'Pending', 'tour-booking-manager' ) ),
								);
								if ( isset( $status_map[ $row['tour_status'] ] ) ) {
									echo '<span class="ttbm-status ' . esc_attr( $status_map[ $row['tour_status'] ][0] ) . '">' . esc_html( $status_map[ $row['tour_status'] ][1] ) . '</span>';
								} else {
									echo esc_html( ucfirst( $row['tour_status'] ) );
								}
								?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<?php if ( $data['total_pages'] > 1 ) : ?>
				<div class="ttbm-wishlist-pagination tablenav">
					<div class="tablenav-pages">
						<span class="displaying-num"><?php printf( esc_html__( '%d items', 'tour-booking-manager' ), $data['total'] ); ?></span>
						<span class="pagination-links" id="ttbm-wishlist-pagination">
							<?php if ( $data['paged'] > 1 ) : ?>
								<button type="button" class="button first-page" data-page="1">&laquo;</button>
								<button type="button" class="button prev-page" data-page="<?php echo esc_attr( $data['paged'] - 1 ); ?>">&lsaquo;</button>
							<?php else : ?>
								<span class="button disabled">&laquo;</span>
								<span class="button disabled">&lsaquo;</span>
							<?php endif; ?>
							<span class="paging-input"><span class="current-page"><?php echo esc_html( $data['paged'] ); ?></span> of <span class="total-pages"><?php echo esc_html( $data['total_pages'] ); ?></span></span>
							<?php if ( $data['paged'] < $data['total_pages'] ) : ?>
								<button type="button" class="button next-page" data-page="<?php echo esc_attr( $data['paged'] + 1 ); ?>">&rsaquo;</button>
								<button type="button" class="button last-page" data-page="<?php echo esc_attr( $data['total_pages'] ); ?>">&raquo;</button>
							<?php else : ?>
								<span class="button disabled">&rsaquo;</span>
								<span class="button disabled">&raquo;</span>
							<?php endif; ?>
						</span>
					</div>
				</div>
				<?php endif; ?>

				<?php else : ?>
				<div class="ttbm-wishlist-empty-state">
					<i class="mi mi-heart" style="font-size:48px;color:#ddd;"></i>
					<h3><?php esc_html_e( 'No wishlists found', 'tour-booking-manager' ); ?></h3>
					<p><?php esc_html_e( 'No customers have added tours to their wishlist yet.', 'tour-booking-manager' ); ?></p>
				</div>
				<?php endif; ?>
			</div>
			<?php
		}
	}
	new TTBM_Admin_Wishlist();
}