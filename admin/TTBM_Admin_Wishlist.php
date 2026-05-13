<?php
/**
 * Admin Wishlist Page
 * Shows which customers wishlisted which tours with user details.
 * @package Tour Booking Manager
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
if ( ! class_exists( 'TTBM_Admin_Wishlist' ) ) {
	class TTBM_Admin_Wishlist {

		public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
			add_action( 'wp_ajax_ttbm_admin_wishlist_search', array( $this, 'ajax_search' ) );
		}

		// ─── Admin Menu ──────────────────────────────────────────
		public function add_admin_menu() {
			$label = TTBM_Function::get_name();
			add_submenu_page(
				'edit.php?post_type=ttbm_tour',
				$label . ' ' . esc_html__( 'Wishlist', 'tour-booking-manager' ),
				$label . ' ' . esc_html__( 'Wishlist', 'tour-booking-manager' ),
				'manage_options',
				'ttbm_wishlist_admin',
				array( $this, 'render_page' )
			);
		}

		// ─── AJAX Search ─────────────────────────────────────────
		public function ajax_search() {
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ttbm_admin_nonce' ) ) {
				wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
			}
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
			}

			$search   = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
			$tour_id  = isset( $_POST['tour_id'] ) ? absint( $_POST['tour_id'] ) : 0;
			$paged    = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;
			$per_page = 20;

			$results = $this->get_wishlist_data( $search, $tour_id, $paged, $per_page );

			wp_send_json_success( $results );
		}

		// ─── Data Gathering ───────────────────────────────────────
		private function get_wishlist_data( $search = '', $tour_id = 0, $paged = 1, $per_page = 20 ) {
			global $wpdb;

			$meta_key = 'ttbm_wishlist';

			// Get all users who have wishlist data
			$user_ids = $wpdb->get_col( $wpdb->prepare(
				"SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s",
				$meta_key
			) );

			$rows = array();

			if ( ! empty( $user_ids ) ) {
				foreach ( $user_ids as $uid ) {
					$list = get_user_meta( $uid, $meta_key, true );
					if ( ! is_array( $list ) || empty( $list ) ) {
						continue;
					}
					$user = get_userdata( $uid );
					if ( ! $user ) {
						continue;
					}
					// Filter by search term (name or email)
					if ( $search ) {
						$match = false;
						$haystack = strtolower( $user->display_name . ' ' . $user->user_email . ' ' . $user->first_name . ' ' . $user->last_name );
						if ( strpos( $haystack, strtolower( $search ) ) !== false ) {
							$match = true;
						}
						if ( ! $match ) {
							continue;
						}
					}

					foreach ( $list as $tid ) {
						// Filter by specific tour
						if ( $tour_id && $tid !== $tour_id ) {
							continue;
						}
						$tour = get_post( $tid );
						if ( ! $tour ) {
							continue;
						}

						// User address details
						$billing_first = get_user_meta( $uid, 'billing_first_name', true );
						$billing_last  = get_user_meta( $uid, 'billing_last_name', true );
						$billing_phone = get_user_meta( $uid, 'billing_phone', true );
						$billing_city  = get_user_meta( $uid, 'billing_city', true );
						$billing_state = get_user_meta( $uid, 'billing_state', true );
						$billing_country = get_user_meta( $uid, 'billing_country', true );
						$billing_postcode = get_user_meta( $uid, 'billing_postcode', true );
						$billing_address_1 = get_user_meta( $uid, 'billing_address_1', true );
						$billing_address_2 = get_user_meta( $uid, 'billing_address_2', true );

						$rows[] = array(
							'user_id'         => $uid,
							'display_name'    => $user->display_name,
							'user_email'      => $user->user_email,
							'first_name'      => $user->first_name,
							'last_name'       => $user->last_name,
							'billing_first'   => $billing_first ?: $user->first_name,
							'billing_last'    => $billing_last ?: $user->last_name,
							'billing_phone'   => $billing_phone,
							'billing_city'    => $billing_city,
							'billing_state'   => $billing_state,
							'billing_country' => $billing_country,
							'billing_postcode' => $billing_postcode,
							'billing_address' => trim( $billing_address_1 . ' ' . $billing_address_2 ),
							'tour_id'         => $tid,
							'tour_title'      => $tour->post_title,
							'tour_status'     => $tour->post_status,
							'tour_edit_link'  => admin_url( 'post.php?post=' . $tid . '&action=edit' ),
						);
					}
				}
			}

			$total      = count( $rows );
			$total_pages = max( 1, (int) ceil( $total / $per_page ) );
			$offset      = ( $paged - 1 ) * $per_page;
			$rows        = array_slice( $rows, $offset, $per_page );

			// Get tours list for filter dropdown
			$tours = get_posts( array(
				'post_type'      => 'ttbm_tour',
				'post_status'     => array( 'publish', 'draft', 'pending' ),
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
				'rows'          => $rows,
				'total'         => $total,
				'paged'         => $paged,
				'per_page'      => $per_page,
				'total_pages'   => $total_pages,
				'tour_options'  => $tour_options,
			);
		}

		// ─── Render Page ──────────────────────────────────────────
		public function render_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$search   = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
			$tour_id  = isset( $_GET['tour_id'] ) ? absint( $_GET['tour_id'] ) : 0;
			$paged    = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
			$per_page = 20;

			$data = $this->get_wishlist_data( $search, $tour_id, $paged, $per_page );

			$label = TTBM_Function::get_name();
			?>
			<div class="wrap ttbm-wishlist-admin-wrap">
				<h1 class="wp-heading-inline">
					<span class="dashicons dashicons-heart" style="color:#e84c6a;margin-right:6px;"></span>
					<?php echo esc_html( $label ); ?> <?php esc_html_e( 'Wishlist', 'tour-booking-manager' ); ?>
				</h1>
				<span class="ttbm-wishlist-count"><?php printf( esc_html__( '(%d entries)', 'tour-booking-manager' ), $data['total'] ); ?></span>
				<hr class="wp-header-end">

				<!-- Filters -->
				<div class="ttbm-wishlist-filters">
					<form method="get" class="ttbm-wishlist-filter-form">
						<input type="hidden" name="post_type" value="ttbm_tour">
						<input type="hidden" name="page" value="ttbm_wishlist_admin">
						<div class="ttbm-filter-row">
							<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search by name or email…', 'tour-booking-manager' ); ?>" class="ttbm-filter-input">
							<select name="tour_id" class="ttbm-filter-select">
								<option value="0"><?php esc_html_e( 'All Tours', 'tour-booking-manager' ); ?></option>
								<?php foreach ( $data['tour_options'] as $topt ) : ?>
									<option value="<?php echo esc_attr( $topt['id'] ); ?>" <?php selected( $tour_id, $topt['id'] ); ?>><?php echo esc_html( $topt['title'] ); ?></option>
								<?php endforeach; ?>
							</select>
							<input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Filter', 'tour-booking-manager' ); ?>">
							<?php if ( $search || $tour_id ) : ?>
								<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ttbm_tour&page=ttbm_wishlist_admin' ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'tour-booking-manager' ); ?></a>
							<?php endif; ?>
						</div>
					</form>
				</div>

				<!-- Table -->
				<?php if ( ! empty( $data['rows'] ) ) : ?>
				<table class="wp-list-table widefat fixed striped ttbm-wishlist-table">
					<thead>
						<tr>
							<th class="column-customer"><?php esc_html_e( 'Customer', 'tour-booking-manager' ); ?></th>
							<th class="column-email"><?php esc_html_e( 'Email', 'tour-booking-manager' ); ?></th>
							<th class="column-phone"><?php esc_html_e( 'Phone', 'tour-booking-manager' ); ?></th>
							<th class="column-address"><?php esc_html_e( 'Address', 'tour-booking-manager' ); ?></th>
							<th class="column-city"><?php esc_html_e( 'City', 'tour-booking-manager' ); ?></th>
							<th class="column-state"><?php esc_html_e( 'State', 'tour-booking-manager' ); ?></th>
							<th class="column-country"><?php esc_html_e( 'Country', 'tour-booking-manager' ); ?></th>
							<th class="column-postcode"><?php esc_html_e( 'Postcode', 'tour-booking-manager' ); ?></th>
							<th class="column-tour"><?php esc_html_e( 'Tour', 'tour-booking-manager' ); ?></th>
							<th class="column-status"><?php esc_html_e( 'Tour Status', 'tour-booking-manager' ); ?></th>
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
							<td class="column-customer">
								<a href="<?php echo esc_url( admin_url( 'user-edit.php?user_id=' . $row['user_id'] ) ); ?>">
									<strong><?php echo esc_html( $row['billing_first'] . ' ' . $row['billing_last'] ); ?></strong>
								</a>
								<?php if ( $row['billing_first'] !== $row['first_name'] && $row['first_name'] ) : ?>
									<br><small class="ttbm-subtle"><?php echo esc_html( '@' . $row['display_name'] ); ?></small>
								<?php endif; ?>
							</td>
							<td class="column-email">
								<a href="mailto:<?php echo esc_attr( $row['user_email'] ); ?>"><?php echo esc_html( $row['user_email'] ); ?></a>
							</td>
							<td class="column-phone"><?php echo esc_html( $row['billing_phone'] ); ?></td>
							<td class="column-address"><?php echo esc_html( $row['billing_address'] ); ?></td>
							<td class="column-city"><?php echo esc_html( $row['billing_city'] ); ?></td>
							<td class="column-state"><?php echo esc_html( $row['billing_state'] ); ?></td>
							<td class="column-country"><?php echo esc_html( $country_name ); ?></td>
							<td class="column-postcode"><?php echo esc_html( $row['billing_postcode'] ); ?></td>
							<td class="column-tour">
								<a href="<?php echo esc_url( $row['tour_edit_link'] ); ?>">
									<?php echo esc_html( $row['tour_title'] ); ?>
								</a>
							</td>
							<td class="column-status">
								<?php
								$status_labels = array(
									'publish' => '<span class="ttbm-status ttbm-status-publish">' . esc_html__( 'Published', 'tour-booking-manager' ) . '</span>',
									'draft'   => '<span class="ttbm-status ttbm-status-draft">' . esc_html__( 'Draft', 'tour-booking-manager' ) . '</span>',
									'pending' => '<span class="ttbm-status ttbm-status-pending">' . esc_html__( 'Pending', 'tour-booking-manager' ) . '</span>',
								);
								echo isset( $status_labels[ $row['tour_status'] ] ) ? $status_labels[ $row['tour_status'] ] : esc_html( ucfirst( $row['tour_status'] ) );
								?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<!-- Pagination -->
				<?php if ( $data['total_pages'] > 1 ) : ?>
				<div class="tablenav bottom">
					<div class="tablenav-pages">
						<span class="displaying-num"><?php printf( esc_html__( '%d items', 'tour-booking-manager' ), $data['total'] ); ?></span>
						<span class="pagination-links">
							<?php
							$base_url = admin_url( 'edit.php?post_type=ttbm_tour&page=ttbm_wishlist_admin' );
							if ( $search ) {
								$base_url = add_query_arg( 's', urlencode( $search ), $base_url );
							}
							if ( $tour_id ) {
								$base_url = add_query_arg( 'tour_id', $tour_id, $base_url );
							}

							if ( $paged > 1 ) {
								echo '<a class="button first-page" href="' . esc_url( add_query_arg( 'paged', 1, $base_url ) ) . '"><span aria-hidden="true">&laquo;</span></a> ';
								echo '<a class="button prev-page" href="' . esc_url( add_query_arg( 'paged', $paged - 1, $base_url ) ) . '"><span aria-hidden="true">&lsaquo;</span></a> ';
							} else {
								echo '<span class="button disabled">&laquo;</span> ';
								echo '<span class="button disabled">&lsaquo;</span> ';
							}

							echo '<span class="paging-input">' . esc_html( $paged ) . ' of <span class="total-pages">' . esc_html( $data['total_pages'] ) . '</span></span>';

							if ( $paged < $data['total_pages'] ) {
								echo ' <a class="button next-page" href="' . esc_url( add_query_arg( 'paged', $paged + 1, $base_url ) ) . '"><span aria-hidden="true">&rsaquo;</span></a>';
								echo ' <a class="button last-page" href="' . esc_url( add_query_arg( 'paged', $data['total_pages'], $base_url ) ) . '"><span aria-hidden="true">&raquo;</span></a>';
							} else {
								echo ' <span class="button disabled">&rsaquo;</span>';
								echo ' <span class="button disabled">&raquo;</span>';
							}
							?>
						</span>
					</div>
				</div>
				<?php endif; ?>

				<?php else : ?>
				<div class="ttbm-wishlist-empty-state">
					<span class="dashicons dashicons-heart"></span>
					<h3><?php esc_html_e( 'No wishlists found', 'tour-booking-manager' ); ?></h3>
					<p><?php esc_html_e( 'No customers have added tours to their wishlist yet.', 'tour-booking-manager' ); ?></p>
				</div>
				<?php endif; ?>
			</div>

			<style>
			.ttbm-wishlist-admin-wrap { margin-top: 10px; }
			.ttbm-wishlist-admin-wrap .wp-heading-inline { display: inline-flex; align-items: center; gap: 4px; }
			.ttbm-wishlist-count { font-size: 14px; color: #666; margin-left: 8px; }
			.ttbm-wishlist-filters { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; padding: 12px 16px; margin: 16px 0; }
			.ttbm-filter-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
			.ttbm-filter-input { min-width: 240px; }
			.ttbm-filter-select { min-width: 200px; }
			.ttbm-wishlist-table th.column-customer,
			.ttbm-wishlist-table td.column-customer { width: 14%; }
			.ttbm-wishlist-table th.column-email,
			.ttbm-wishlist-table td.column-email { width: 14%; }
			.ttbm-wishlist-table th.column-phone,
			.ttbm-wishlist-table td.column-phone { width: 9%; }
			.ttbm-wishlist-table th.column-address,
			.ttbm-wishlist-table td.column-address { width: 12%; }
			.ttbm-wishlist-table th.column-city,
			.ttbm-wishlist-table td.column-city { width: 7%; }
			.ttbm-wishlist-table th.column-state,
			.ttbm-wishlist-table td.column-state { width: 7%; }
			.ttbm-wishlist-table th.column-country,
			.ttbm-wishlist-table td.column-country { width: 8%; }
			.ttbm-wishlist-table th.column-postcode,
			.ttbm-wishlist-table td.column-postcode { width: 6%; }
			.ttbm-wishlist-table th.column-tour,
			.ttbm-wishlist-table td.column-tour { width: 15%; }
			.ttbm-wishlist-table th.column-status,
			.ttbm-wishlist-table td.column-status { width: 8%; }
			.ttbm-wishlist-table td { vertical-align: middle; }
			.ttbm-wishlist-table td a { text-decoration: none; }
			.ttbm-wishlist-table td a:hover { color: #6c47ff; }
			.ttbm-subtle { color: #999; }
			.ttbm-status { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 12px; font-weight: 500; }
			.ttbm-status-publish { background: #d4edda; color: #155724; }
			.ttbm-status-draft { background: #fff3cd; color: #856404; }
			.ttbm-status-pending { background: #cce5ff; color: #004085; }
			.ttbm-wishlist-empty-state { text-align: center; padding: 60px 20px; color: #888; }
			.ttbm-wishlist-empty-state .dashicons { font-size: 48px; width: 48px; height: 48px; color: #ddd; }
			.ttbm-wishlist-empty-state h3 { margin: 16px 0 4px; font-size: 18px; color: #555; }
			.ttbm-wishlist-empty-state p { font-size: 14px; color: #888; }
			@media (max-width: 782px) {
				.ttbm-filter-row { flex-direction: column; align-items: stretch; }
				.ttbm-filter-input, .ttbm-filter-select { width: 100%; min-width: 0; }
			}
			</style>
			<?php
		}
	}
	new TTBM_Admin_Wishlist();
}