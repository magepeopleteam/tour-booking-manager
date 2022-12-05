<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'TTBM_Setting_faq_day_wise_details' ) ) {
		class TTBM_Setting_faq_day_wise_details {
			public function __construct() {
				add_action( 'add_ttbm_settings_tab_name', [ $this, 'add_tab' ], 10 );
				add_action( 'add_ttbm_settings_tab_content', [ $this, 'tab_content' ], 10, 1 );
				add_action( 'wp_ajax_get_ttbm_add_faq_content', [ $this, 'get_ttbm_add_faq_content' ] );
				add_action( 'wp_ajax_nopriv_get_ttbm_add_faq_content', [ $this, 'get_ttbm_add_faq_content' ] );
				add_action( 'wp_ajax_get_ttbm_add_day_wise_details', [ $this, 'get_ttbm_add_day_wise_details' ] );
				add_action( 'wp_ajax_nopriv_get_ttbm_add_day_wise_details', [ $this, 'get_ttbm_add_day_wise_details' ] );
			}
			public function add_tab() {
				?>
				<li data-tabs-target="#ttbm_settings_day_wise_details">
					<span class="dashicons dashicons-list-view"></span><?php esc_html_e( ' Day wise Details', 'tour-booking-manager' ); ?>
				</li>
				<li data-tabs-target="#ttbm_settings_faq">
					<span class="dashicons dashicons-editor-help"></span><?php esc_html_e( ' F.A.Q', 'tour-booking-manager' ); ?>
				</li>
				<?php
			}
			public function tab_content( $tour_id ) {
				$this->ttbm_settings_day_wise_details( $tour_id );
				$this->ttbm_settings_faq( $tour_id );
			}
			//********Day wise Details**************//
			public function ttbm_settings_day_wise_details( $tour_id ) {
				$display = TTBM_Function::get_post_info( $tour_id, 'ttbm_display_schedule', 'on' );
				$active  = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem ttbm_settings_day_wise_details" data-tabs="#ttbm_settings_day_wise_details">
					<h5 class="dFlex">
						<span class="mR"><?php esc_html_e( 'Day Wise Details Settings', 'tour-booking-manager' ); ?></span>
						<?php TTBM_Layout::switch_button( 'ttbm_display_schedule', $checked ); ?>
					</h5>
					<?php TTBM_Settings::des_p( 'ttbm_display_schedule' ); ?>
					<div class="divider"></div>
					<div data-collapse="#ttbm_display_schedule" class="<?php echo esc_attr( $active ); ?>">
						<?php
							$day_details = TTBM_Function::get_post_info( $tour_id, 'ttbm_daywise_details', array() );
							if ( sizeof( $day_details ) > 0 ) {
								foreach ( $day_details as $day_detail ) {
									$id = 'ttbm_day_content_' . uniqid();
									$this->ttbm_repeated_item( $id, 'ttbm_daywise_details', $day_detail );
								}
							}
						?>
						<?php TTBM_Layout::add_new_button( esc_html__( 'Add New Day Wise Details', 'tour-booking-manager' ), 'ttbm_add_day_wise_details', '_dButton_bgBlue' ); ?>
					</div>
				</div>
				<?php
			}
			public function get_ttbm_add_day_wise_details() {
				$id = TTBM_Function::data_sanitize( $_POST['id'] );
				$this->ttbm_repeated_item( $id, 'ttbm_daywise_details' );
				die();
			}
			//*************F.A.Q******************//
			public function ttbm_settings_faq( $tour_id ) {
				$display = TTBM_Function::get_post_info( $tour_id, 'ttbm_display_faq', 'on' );
				$active  = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				?>
				<div class="tabsItem" data-tabs="#ttbm_settings_faq">
					<h5 class="dFlex">
						<span class="mR"><?php esc_html_e( 'F.A.Q Settings', 'tour-booking-manager' ); ?></span>
						<?php TTBM_Layout::switch_button( 'ttbm_display_faq', $checked ); ?>
					</h5>
					<?php TTBM_Settings::des_p( 'ttbm_display_faq' ); ?>
					<div class="divider"></div>
					<div data-collapse="#ttbm_display_faq" class="<?php echo esc_attr( $active ); ?>">
						<?php
							$faqs = TTBM_Function::get_post_info( $tour_id, 'mep_event_faq', [] );
							if ( sizeof( $faqs ) > 0 ) {
								foreach ( $faqs as $faq ) {
									$id = 'ttbm_faq_content_' . uniqid();
									$this->ttbm_repeated_item( $id, 'mep_event_faq', $faq );
								}
							}
						?>
						<?php TTBM_Layout::add_new_button( esc_html__( 'Add New F.A.Q', 'tour-booking-manager' ), 'ttbm_add_faq_content', '_dButton_bgBlue' ); ?>
					</div>
				</div>
				<?php
			}
			public function get_ttbm_add_faq_content() {
				$id = TTBM_Function::data_sanitize( $_POST['id'] );
				$this->ttbm_repeated_item( $id, 'mep_event_faq' );
				die();
			}
			//*********************//
			public static function get_ttbm_repeated_setting_array( $meta_key ): array {
				$array = [
					'mep_event_faq'        => [
						'title'         => esc_html__( ' F.A.Q Title', 'tour-booking-manager' ),
						'title_name'    => 'ttbm_faq_title',
						'img_title'     => esc_html__( ' F.A.Q Details image', 'tour-booking-manager' ),
						'img_name'      => 'ttbm_faq_img',
						'content_title' => esc_html__( ' F.A.Q Details Content', 'tour-booking-manager' ),
						'content_name'  => 'ttbm_faq_content',
					],
					'ttbm_daywise_details' => [
						'title'         => esc_html__( ' Day wise Details Title', 'tour-booking-manager' ),
						'title_name'    => 'ttbm_day_title',
						'img_title'     => esc_html__( ' Day wise Details image', 'tour-booking-manager' ),
						'img_name'      => 'ttbm_day_image',
						'content_title' => esc_html__( ' Day wise Details Content', 'tour-booking-manager' ),
						'content_name'  => 'ttbm_day_content',
					]
				];
				return $array[ $meta_key ];
			}
			public function ttbm_repeated_item( $id, $meta_key, $data = [] ) {
				//ob_start();
				$array         = self::get_ttbm_repeated_setting_array( $meta_key );
				$title         = $array['title'];
				$title_name    = $array['title_name'];
				$title_value   = array_key_exists( $title_name, $data ) ? html_entity_decode( $data[ $title_name ] ) : '';
				$image_title   = $array['img_title'];
				$image_name    = $array['img_name'];
				$images        = array_key_exists( $image_name, $data ) ? $data[ $image_name ] : '';
				$content_title = $array['content_title'];
				$content_name  = $array['content_name'];
				$content       = array_key_exists( $content_name, $data ) ? html_entity_decode( $data[ $content_name ] ) : '';
				?>
				<div class='dLayout mp_remove_area'>
					<label>
						<span class="min_200"><?php echo esc_html( $title ); ?></span>
						<input type="text" class="formControl" name="<?php echo esc_attr( $title_name ); ?>[]" value="<?php echo esc_attr( $title_value ); ?>"/>
					</label>
					<div class="dFlex">
						<span class="min_200"><?php echo esc_html( $image_title ); ?></span>
						<?php TTBM_Layout::add_multi_image($image_name.'[]',$images); ?>
					</div>
					<label>
						<span class="min_200"><?php echo esc_html( $content_title ); ?></span>
						<?php
							$settings = [
								'wpautop'       => false,
								'media_buttons' => false,
								'textarea_name' => $content_name . '[]',
								'tabindex'      => '323',
								'editor_height' => 200,
								'editor_css'    => '',
								'editor_class'  => '',
								'teeny'         => false,
								'dfw'           => false,
								'tinymce'       => true,
								'quicktags'     => true
							];
							wp_editor( $content, $id, $settings );
						?>
					</label>
					<span class="fas fa-times circleIcon_xs mp_remove_icon"></span>
				</div>
				<?php
				//return ob_get_clean();
			}
		}
		new TTBM_Setting_faq_day_wise_details();
	}