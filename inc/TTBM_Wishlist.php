<?php
/*
* Wishlist functionality for Tour Booking Manager
* Handles: AJAX add/remove, user meta storage, My Account endpoint
*/
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
if ( ! class_exists( 'TTBM_Wishlist' ) ) {
	class TTBM_Wishlist {

		private static $meta_key = 'ttbm_wishlist';

		public function __construct() {
			// AJAX handlers
			add_action( 'wp_ajax_ttbm_wishlist_toggle', array( $this, 'ajax_toggle' ) );
			add_action( 'wp_ajax_nopriv_ttbm_wishlist_toggle', array( $this, 'ajax_not_logged_in' ) );

			// My Account endpoint
			add_action( 'init', array( $this, 'add_endpoint' ) );
			add_filter( 'woocommerce_get_query_vars', array( $this, 'add_query_var' ) );
			add_filter( 'woocommerce_account_menu_items', array( $this, 'add_menu_item' ) );
			add_action( 'woocommerce_account_ttbm-wishlist_endpoint', array( $this, 'endpoint_content' ) );

			// Login modal + CSS in footer
			add_action( 'wp_footer', array( $this, 'login_modal' ) );
			add_action( 'wp_footer', array( $this, 'modal_css' ) );
		}

		// ─── User Meta Storage ──────────────────────────────────────

		/**
		 * Get wishlist tour IDs for a user.
		 */
		public static function get_wishlist( $user_id = 0 ) {
			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}
			if ( ! $user_id ) {
				return array();
			}
			$list = get_user_meta( $user_id, self::$meta_key, true );
			return is_array( $list ) ? $list : array();
		}

		/**
		 * Add a tour to the user's wishlist. Returns true on success.
		 */
		public static function add( $tour_id, $user_id = 0 ) {
			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}
			if ( ! $user_id ) {
				return false;
			}
			$list   = self::get_wishlist( $user_id );
			$tour_id = (int) $tour_id;
			if ( ! in_array( $tour_id, $list, true ) ) {
				$list[] = $tour_id;
				update_user_meta( $user_id, self::$meta_key, $list );
			}
			return true;
		}

		/**
		 * Remove a tour from the user's wishlist. Returns true on success.
		 */
		public static function remove( $tour_id, $user_id = 0 ) {
			if ( ! $user_id ) {
				$user_id = get_current_user_id();
			}
			if ( ! $user_id ) {
				return false;
			}
			$list   = self::get_wishlist( $user_id );
			$tour_id = (int) $tour_id;
			$list    = array_values( array_diff( $list, array( $tour_id ) ) );
			update_user_meta( $user_id, self::$meta_key, $list );
			return true;
		}

		/**
		 * Check if a tour is in the user's wishlist.
		 */
		public static function is_in_wishlist( $tour_id, $user_id = 0 ) {
			$list = self::get_wishlist( $user_id );
			return in_array( (int) $tour_id, $list, true );
		}

		// ─── AJAX Handlers ──────────────────────────────────────────

		/**
		 * AJAX: Toggle wishlist for logged-in users.
		 */
		public function ajax_toggle() {
			check_ajax_referer( 'ttbm_frontend_nonce', 'nonce' );

			if ( ! is_user_logged_in() ) {
				wp_send_json_error( array(
					'message'    => esc_html__( 'Please log in to add to wishlist.', 'tour-booking-manager' ),
					'need_login' => true,
				) );
			}

			$tour_id = isset( $_POST['tour_id'] ) ? absint( $_POST['tour_id'] ) : 0;
			if ( ! $tour_id || get_post_type( $tour_id ) !== 'ttbm_tour' ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Invalid tour.', 'tour-booking-manager' ) ) );
			}

			$user_id = get_current_user_id();

			if ( self::is_in_wishlist( $tour_id, $user_id ) ) {
				self::remove( $tour_id, $user_id );
				$in_wishlist = false;
				$action_text = esc_html__( 'Removed from wishlist', 'tour-booking-manager' );
			} else {
				self::add( $tour_id, $user_id );
				$in_wishlist = true;
				$action_text = esc_html__( 'Added to wishlist', 'tour-booking-manager' );
			}

			wp_send_json_success( array(
				'in_wishlist' => $in_wishlist,
				'message'     => $action_text,
			) );
		}

		/**
		 * AJAX: Not-logged-in response — tells JS to show login modal.
		 */
		public function ajax_not_logged_in() {
			wp_send_json_error( array(
				'message'    => esc_html__( 'Please log in to add to wishlist.', 'tour-booking-manager' ),
				'need_login' => true,
			) );
		}

		// ─── My Account Endpoint ─────────────────────────────────────

		/**
		 * Register the /my-account/ttbm-wishlist/ rewrite endpoint.
		 */
		public function add_endpoint() {
			add_rewrite_endpoint( 'ttbm-wishlist', EP_PAGES );
		}

		/**
		 * Tell WooCommerce about our custom query var.
		 */
		public function add_query_var( $vars ) {
			$vars['ttbm-wishlist'] = 'ttbm-wishlist';
			return $vars;
		}

		/**
		 * Add "Wishlist" menu item to My Account navigation.
		 */
		public function add_menu_item( $items ) {
			$logout = isset( $items['customer-logout'] ) ? $items['customer-logout'] : null;
			unset( $items['customer-logout'] );

			$items['ttbm-wishlist'] = esc_html__( 'Wishlist', 'tour-booking-manager' );

			if ( $logout ) {
				$items['customer-logout'] = $logout;
			}
			return $items;
		}

		/**
		 * Render the Wishlist tab content on My Account page.
		 */
		public function endpoint_content() {
			if ( ! is_user_logged_in() ) {
				return;
			}

			$user_id    = get_current_user_id();
			$tour_ids   = self::get_wishlist( $user_id );

			?>
			<div class="ttbm-myaccount-wishlist">
				<div class="ttbm-wishlist-header">
					<h2><?php esc_html_e( 'My Wishlist', 'tour-booking-manager' ); ?></h2>
					<?php if ( ! empty( $tour_ids ) ) : ?>
					<div class="ttbm-wishlist-view-toggle">
						<button type="button" class="ttbm-wishlist-view-btn ttbm-wishlist-view-active" data-view="grid" aria-pressed="true" title="<?php esc_attr_e( 'Grid view', 'tour-booking-manager' ); ?>">
							<span class="mi mi-grid"></span>
						</button>
						<button type="button" class="ttbm-wishlist-view-btn" data-view="list" aria-pressed="false" title="<?php esc_attr_e( 'List view', 'tour-booking-manager' ); ?>">
							<span class="mi mi-list"></span>
						</button>
					</div>
					<?php endif; ?>
				</div>

				<?php if ( empty( $tour_ids ) ) : ?>
					<p class="ttbm-wishlist-empty"><?php esc_html_e( 'Your wishlist is empty.', 'tour-booking-manager' ); ?></p>
				<?php else : ?>
					<div class="ttbm-wishlist-grid ttbm-wishlist-view-grid">
						<?php foreach ( $tour_ids as $tour_id ) :
							$tour = get_post( $tour_id );
							if ( ! $tour || $tour->post_status !== 'publish' ) {
								continue;
							}
							$ttbm_post_id = $tour_id;
							$thumbnail    = get_the_post_thumbnail_url( $tour_id, 'medium' );
							if ( ! $thumbnail ) {
								$thumbnail = TTBM_PLUGIN_URL . '/assets/images/no_image.png';
							}
							$location      = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_location_name' );
							$duration       = TTBM_Function::get_duration( $tour_id );
							$night          = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_travel_duration_night' );
							$duration_type  = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_travel_duration_type', 'day' );
							$start_price    = TTBM_Function::get_tour_start_price( $tour_id );
							$regular_price  = TTBM_Function::check_discount_price_exit( $tour_id );
							$show_price     = TTBM_Global_Function::get_post_info( $tour_id, 'ttbm_display_price_start', 'on' ) !== 'off';

							$duration_label = '';
							if ( $duration ) {
								$duration_label .= esc_html( $duration ) . ' ';
								if ( $duration_type === 'day' ) {
									$duration_label .= $duration > 1 ? esc_html__( 'DAYS', 'tour-booking-manager' ) : esc_html__( 'DAY', 'tour-booking-manager' );
								} elseif ( $duration_type === 'min' ) {
									$duration_label .= $duration > 1 ? esc_html__( 'MINUTES', 'tour-booking-manager' ) : esc_html__( 'MINUTE', 'tour-booking-manager' );
								} else {
									$duration_label .= $duration > 1 ? esc_html__( 'HOURS', 'tour-booking-manager' ) : esc_html__( 'HOUR', 'tour-booking-manager' );
								}
							}
							if ( $night ) {
								$duration_label .= ' / ' . esc_html( $night ) . ' ';
								$duration_label .= $night > 1 ? esc_html__( 'NIGHTS', 'tour-booking-manager' ) : esc_html__( 'NIGHT', 'tour-booking-manager' );
							}
							?>
							<div class="ttbm-wishlist-item" data-tour-id="<?php echo esc_attr( $tour_id ); ?>">
								<div class="ttbm-wishlist-item-thumb">
									<a href="<?php echo esc_url( get_permalink( $tour_id ) ); ?>">
										<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( get_the_title( $tour_id ) ); ?>" />
									</a>
									<button type="button" class="ttbm-wishlist-remove ttbm-wishlist-in-list" data-tour-id="<?php echo esc_attr( $tour_id ); ?>" title="<?php esc_attr_e( 'Remove from wishlist', 'tour-booking-manager' ); ?>">
										<span class="mi mi-wishlist-heart"></span>
									</button>
								</div>
								<div class="ttbm-wishlist-item-info">
									<h3><a href="<?php echo esc_url( get_permalink( $tour_id ) ); ?>"><?php echo esc_html( get_the_title( $tour_id ) ); ?></a></h3>
									<?php if ( $location ) : ?>
										<div class="ttbm-wishlist-meta"><span class="mi mi-map-pin"></span> <?php echo esc_html( $location ); ?></div>
									<?php endif; ?>
									<?php if ( $duration_label ) : ?>
										<div class="ttbm-wishlist-meta"><span class="mi mi-clock"></span> <?php echo esc_html( $duration_label ); ?></div>
									<?php endif; ?>
									<?php if ( $start_price && $show_price ) : ?>
										<div class="ttbm-wishlist-price"><?php echo wc_price( $start_price ); ?></div>
									<?php endif; ?>
									<a href="<?php echo esc_url( get_permalink( $tour_id ) ); ?>" class="ttbm-wishlist-explore-btn"><?php esc_html_e( 'Explore', 'tour-booking-manager' ); ?></a>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}

		// ─── Login Modal & Footer Assets ──────────────────────────────

		/**
		 * Output login modal HTML in footer.
		 */
		public function login_modal() {
			if ( is_user_logged_in() ) {
				return;
			}
			$login_url = wp_login_url( get_permalink() );
			?>
			<div class="ttbm-modal-wrap" id="ttbm-wishlist-login-modal">
				<div class="ttbm-modal-overlay"></div>
				<div class="ttbm-modal-box">
					<button type="button" class="ttbm-modal-close">×</button>
					<div class="ttbm-modal-icon">
						<span class="mi mi-heart"></span>
					</div>
					<h3 class="ttbm-modal-title"><?php esc_html_e( 'Login Required', 'tour-booking-manager' ); ?></h3>
					<p class="ttbm-modal-text"><?php esc_html_e( 'Please log in to add tours to your wishlist.', 'tour-booking-manager' ); ?></p>
					<a href="<?php echo esc_url( $login_url ); ?>" class="ttbm-modal-btn"><?php esc_html_e( 'Log In', 'tour-booking-manager' ); ?></a>
				</div>
			</div>
			<?php
		}

		/**
		 * Output wishlist CSS in footer.
		 */
		public function modal_css() {
			?>
			<style>
			/* ── Wishlist Login Modal ─────────────────────────────── */
			.ttbm-modal-wrap{display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:999999;align-items:center;justify-content:center;}
			.ttbm-modal-wrap.ttbm-modal-active{display:flex;}
			.ttbm-modal-overlay{position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.55);}
			.ttbm-modal-box{position:relative;background:#fff;border-radius:14px;padding:36px 30px 30px;max-width:380px;width:90%;text-align:center;box-shadow:0 16px 48px rgba(0,0,0,.18);z-index:1;}
			.ttbm-modal-close{position:absolute;top:10px;right:14px;background:none;border:none;font-size:22px;color:#999;cursor:pointer;line-height:1;padding:4px;}
			.ttbm-modal-close:hover{color:#333;}
			.ttbm-modal-icon{margin-bottom:14px;}
			.ttbm-modal-icon .mi{font-size:40px;color:#e84c6a;}
			.ttbm-modal-title{font-size:20px;font-weight:600;margin:0 0 8px;color:#222;}
			.ttbm-modal-text{font-size:14px;color:#666;margin:0 0 22px;}
			.ttbm-modal-btn{display:inline-block;background:#6c47ff;color:#fff;padding:10px 28px;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600;transition:background .2s;}
			.ttbm-modal-btn:hover{background:#5a3be0;color:#fff;}

			/* ── Wishlist Header & View Toggle ──────────────────── */
			.ttbm-wishlist-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;flex-wrap:wrap;gap:10px;}
			.ttbm-wishlist-header h2{margin:0;}
			.ttbm-wishlist-view-toggle{display:flex;gap:4px;background:#f1f1f5;border-radius:8px;padding:3px;}
			.ttbm-wishlist-view-btn{display:flex;align-items:center;justify-content:center;width:34px;height:34px;border:none;border-radius:6px;background:transparent;color:#9ca3af;cursor:pointer;transition:background .18s ease,color .18s ease;font-size:16px;padding:0;line-height:1;}
			.ttbm-wishlist-view-btn .mi{font-size:16px;color:inherit;}
			.ttbm-wishlist-view-btn:hover{background:#e8e8ef;color:#374151;}
			.ttbm-wishlist-view-btn.ttbm-wishlist-view-active,
			.ttbm-wishlist-view-btn[aria-pressed="true"]{background:#6c47ff;color:#fff;}
			.ttbm-wishlist-view-btn.ttbm-wishlist-view-active .mi,
			.ttbm-wishlist-view-btn[aria-pressed="true"] .mi{color:#fff;}

			/* ── My Account Wishlist Grid ────────────────────────── */
			.ttbm-myaccount-wishlist{margin-top:10px;}
			.ttbm-wishlist-empty{color:#888;font-size:15px;}

			/* ── GRID VIEW (default) ─────────────────────────────── */
			.ttbm-wishlist-grid.ttbm-wishlist-view-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;margin-top:16px;}
			.ttbm-wishlist-view-grid .ttbm-wishlist-item{background:#fff;border:1px solid #eee;border-radius:12px;overflow:hidden;transition:box-shadow .2s;}
			.ttbm-wishlist-view-grid .ttbm-wishlist-item:hover{box-shadow:0 4px 16px rgba(0,0,0,.1);}
			.ttbm-wishlist-view-grid .ttbm-wishlist-item-thumb{position:relative;}
			.ttbm-wishlist-view-grid .ttbm-wishlist-item-thumb img{width:100%;height:180px;object-fit:cover;display:block;}
			.ttbm-wishlist-view-grid .ttbm-wishlist-remove{position:absolute;top:10px;right:10px;background:#e84c6a;border:none;border-radius:50%;width:34px;height:34px;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(232,76,106,.35);transition:background .2s,transform .2s;padding:0;}
			.ttbm-wishlist-view-grid .ttbm-wishlist-remove .mi{font-size:16px;color:#fff;}
			.ttbm-wishlist-view-grid .ttbm-wishlist-remove:hover{background:#d63d5e;transform:scale(1.15);}.ttbm-wishlist-view-grid .ttbm-wishlist-remove:hover .mi{color:#fff;}
			.ttbm-wishlist-view-grid .ttbm-wishlist-item-info{padding:14px 16px;}
			.ttbm-wishlist-view-grid .ttbm-wishlist-item-info h3{margin:0 0 10px;font-size:16px;font-weight:600;line-height:1.4;}
			.ttbm-wishlist-view-grid .ttbm-wishlist-item-info h3 a{color:#222;text-decoration:none;}
			.ttbm-wishlist-view-grid .ttbm-wishlist-item-info h3 a:hover{color:#6c47ff;}
			.ttbm-wishlist-view-grid .ttbm-wishlist-meta{display:flex;align-items:center;gap:4px;font-size:13px;color:#666;margin-bottom:6px;}
			.ttbm-wishlist-view-grid .ttbm-wishlist-meta .mi{font-size:13px;color:#999;}
			.ttbm-wishlist-view-grid .ttbm-wishlist-price{font-size:16px;font-weight:700;color:#6c47ff;margin-top:4px;margin-bottom:10px;}
			.ttbm-wishlist-view-grid .ttbm-wishlist-price .woocommerce-Price-amount{font-size:16px;}
			.ttbm-wishlist-view-grid .ttbm-wishlist-explore-btn{display:inline-block;background:#6c47ff;color:#fff !important;padding:8px 20px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;transition:background .2s;}
			.ttbm-wishlist-view-grid .ttbm-wishlist-explore-btn:hover{background:#5a3be0;color:#fff !important;}

			/* ── LIST VIEW ────────────────────────────────────────── */
			.ttbm-wishlist-grid.ttbm-wishlist-view-list{display:flex;flex-direction:column;gap:16px;margin-top:16px;}
			.ttbm-wishlist-view-list .ttbm-wishlist-item{display:flex;flex-direction:row;align-items:stretch;background:#fff;border:1px solid #eee;border-radius:14px;overflow:hidden;transition:box-shadow .2s;}
			.ttbm-wishlist-view-list .ttbm-wishlist-item:hover{box-shadow:0 4px 16px rgba(0,0,0,.1);}
			.ttbm-wishlist-view-list .ttbm-wishlist-item-thumb{position:relative;width:300px;min-width:300px;max-width:300px;flex-shrink:0;}
			.ttbm-wishlist-view-list .ttbm-wishlist-item-thumb img{width:100%;height:100%;min-height:100%;object-fit:cover;display:block;}
			.ttbm-wishlist-view-list .ttbm-wishlist-remove{position:absolute;top:10px;right:10px;background:#e84c6a;border:none;border-radius:50%;width:34px;height:34px;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(232,76,106,.35);transition:background .2s,transform .2s;padding:0;}
			.ttbm-wishlist-view-list .ttbm-wishlist-remove .mi{font-size:16px;color:#fff;}
			.ttbm-wishlist-view-list .ttbm-wishlist-remove:hover{background:#d63d5e;transform:scale(1.15);}.ttbm-wishlist-view-list .ttbm-wishlist-remove:hover .mi{color:#fff;}
			.ttbm-wishlist-view-list .ttbm-wishlist-item-info{flex:1;display:flex;flex-direction:column;padding:20px 24px;min-width:0;}
			.ttbm-wishlist-view-list .ttbm-wishlist-item-info h3{margin:0 0 10px;font-size:20px;font-weight:600;line-height:1.3;}
			.ttbm-wishlist-view-list .ttbm-wishlist-item-info h3 a{color:#222;text-decoration:none;}
			.ttbm-wishlist-view-list .ttbm-wishlist-item-info h3 a:hover{color:#6c47ff;}
			.ttbm-wishlist-view-list .ttbm-wishlist-meta{display:flex;align-items:center;gap:4px;font-size:13px;color:#666;margin-bottom:6px;}
			.ttbm-wishlist-view-list .ttbm-wishlist-meta .mi{font-size:13px;color:#999;}
			.ttbm-wishlist-view-list .ttbm-wishlist-price{font-size:22px;font-weight:700;color:#6c47ff;margin-top:auto;margin-bottom:12px;}
			.ttbm-wishlist-view-list .ttbm-wishlist-price .woocommerce-Price-amount{font-size:22px;}
			.ttbm-wishlist-view-list .ttbm-wishlist-explore-btn{display:inline-block;background:#6c47ff;color:#fff !important;padding:10px 24px;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600;transition:background .2s;align-self:flex-start;}
			.ttbm-wishlist-view-list .ttbm-wishlist-explore-btn:hover{background:#5a3be0;color:#fff !important;}

			/* ── Toast Notification ─────────────────────────────── */
			.ttbm-toast-wrap{position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(20px);z-index:999999;display:flex;flex-direction:column;gap:10px;align-items:center;pointer-events:none;transition:transform .35s cubic-bezier(.22,1,.36,1),opacity .35s ease;opacity:0;}
			.ttbm-toast-wrap.ttbm-toast-visible{transform:translateX(-50%) translateY(0);opacity:1;}
			.ttbm-toast-item{background:#222;color:#fff;padding:14px 22px;border-radius:10px;font-size:14px;font-weight:500;line-height:1.5;box-shadow:0 8px 28px rgba(0,0,0,.25);display:flex;align-items:center;gap:10px;pointer-events:auto;max-width:420px;text-align:center;}
			.ttbm-toast-item.ttbm-toast-success{background:#1a7a3e;}
			.ttbm-toast-item.ttbm-toast-info{background:#6c47ff;}
			.ttbm-toast-item a{color:#ffd700;text-decoration:underline;font-weight:600;}
			.ttbm-toast-item a:hover{color:#fff;}
			.ttbm-toast-item .ttbm-toast-close{background:none;border:none;color:#fff;font-size:16px;line-height:1;cursor:pointer;padding:0 0 0 6px;opacity:.7;}
			.ttbm-toast-item .ttbm-toast-close:hover{opacity:1;}

			/* ── List View Responsive ─────────────────────────────── */
			@media (max-width:767px) {
			.ttbm-wishlist-view-list .ttbm-wishlist-item{flex-direction:column !important;}
			.ttbm-wishlist-view-list .ttbm-wishlist-item-thumb{width:100%;max-width:100%;min-width:100%;height:220px;}
			.ttbm-wishlist-view-list .ttbm-wishlist-item-thumb img{height:220px;}
			.ttbm-wishlist-view-list .ttbm-wishlist-item-info{padding:16px;}
			.ttbm-wishlist-view-list .ttbm-wishlist-item-info h3{font-size:18px;}
			.ttbm-wishlist-view-list .ttbm-wishlist-explore-btn{width:100%;text-align:center;}
			}
			@media (max-width:480px) {
			.ttbm-wishlist-view-list .ttbm-wishlist-item-thumb{height:200px;}
			.ttbm-wishlist-view-list .ttbm-wishlist-item-thumb img{height:200px;}
			.ttbm-wishlist-view-list .ttbm-wishlist-item-info{padding:14px;}
			.ttbm-wishlist-view-list .ttbm-wishlist-item-info h3{font-size:16px;}
			}
			</style>
			<?php
		}
	}
	new TTBM_Wishlist();
}