<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'TTBM_Save' ) ) {
		class TTBM_Save {
			public function __construct() {
				add_action( 'save_post', array( $this, 'save_ttbm_settings' ), 99, 1 );
				add_action( 'save_post', array( $this, 'save_hotel' ), 99, 1 );
				/************add New location save********************/
				add_action( 'wp_ajax_ttbm_new_location_save', [ $this, 'ttbm_new_location_save' ] );
				add_action( 'wp_ajax_nopriv_ttbm_new_location_save', [ $this, 'ttbm_new_location_save' ] );
				/************add New Feature********************/
				add_action( 'wp_ajax_ttbm_new_feature_save', [ $this, 'ttbm_new_feature_save' ] );
				add_action( 'wp_ajax_nopriv_ttbm_new_feature_save', [ $this, 'ttbm_new_feature_save' ] );
				/************add New activity********************/
				add_action( 'wp_ajax_ttbm_new_activity_save', [ $this, 'ttbm_new_activity_save' ] );
				add_action( 'wp_ajax_nopriv_ttbm_new_activity_save', [ $this, 'ttbm_new_activity_save' ] );
				/***********Add new place*********************/
				add_action( 'wp_ajax_ttbm_new_place_save', [ $this, 'ttbm_new_place_save' ] );
				add_action( 'wp_ajax_nopriv_ttbm_new_place_save', [ $this, 'ttbm_new_place_save' ] );
			}
			public function save_ttbm_settings( $tour_id ) {
				if ( ! isset( $_POST['ttbm_ticket_type_nonce'] ) || ! wp_verify_nonce( $_POST['ttbm_ticket_type_nonce'], 'ttbm_ticket_type_nonce' ) && defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE && ! current_user_can( 'edit_post', $tour_id ) ) {
					return;
				}
				$this->save_general_info( $tour_id );
				$this->save_pricing( $tour_id );
				$this->save_features( $tour_id );
				$this->save_place_you_see( $tour_id );
				$this->save_why_chose_us( $tour_id );
				$this->save_ttbm_repeated_setting( $tour_id, 'mep_event_faq' );
				$this->save_ttbm_repeated_setting( $tour_id, 'ttbm_daywise_details' );
				$this->save_slider_gallery( $tour_id );
				$this->save_map( $tour_id );
				$this->save_activities( $tour_id );
				$this->save_extras( $tour_id );
				$this->save_related_tour( $tour_id );
				$this->save_details_display( $tour_id );
				do_action( 'wcpp_partial_settings_saved', $tour_id );
				do_action( 'ttbm_settings_save', $tour_id );
				TTBM_Function::update_upcoming_date_month( $tour_id, true );
			}
			public function save_general_info( $tour_id ) {
				if ( get_post_type( $tour_id ) == TTBM_Function::get_cpt_name() ) {
					/***************/
					$ttbm_travel_duration      = TTBM_Function::get_submit_info( 'ttbm_travel_duration' );
					$ttbm_travel_duration_type = TTBM_Function::get_submit_info( 'ttbm_travel_duration_type', 'day' );
					update_post_meta( $tour_id, 'ttbm_travel_duration', $ttbm_travel_duration );
					update_post_meta( $tour_id, 'ttbm_travel_duration_type', $ttbm_travel_duration_type );
					$ttbm_display_duration      = TTBM_Function::get_submit_info( 'ttbm_display_duration_night' ) ? 'on' : 'off';
					$ttbm_travel_duration_night = TTBM_Function::get_submit_info( 'ttbm_travel_duration_night' );
					update_post_meta( $tour_id, 'ttbm_travel_duration_night', $ttbm_travel_duration_night );
					update_post_meta( $tour_id, 'ttbm_display_duration_night', $ttbm_display_duration );
					/***************/
					$ttbm_display_price_start = TTBM_Function::get_submit_info( 'ttbm_display_price_start' ) ? 'on' : 'off';
					$ttbm_travel_start_price  = TTBM_Function::get_submit_info( 'ttbm_travel_start_price' );
					update_post_meta( $tour_id, 'ttbm_display_price_start', $ttbm_display_price_start );
					update_post_meta( $tour_id, 'ttbm_travel_start_price', $ttbm_travel_start_price );
					/***************/
					$ttbm_display_max_people      = TTBM_Function::get_submit_info( 'ttbm_display_max_people' ) ? 'on' : 'off';
					$ttbm_travel_max_people_allow = TTBM_Function::get_submit_info( 'ttbm_travel_max_people_allow' );
					update_post_meta( $tour_id, 'ttbm_display_max_people', $ttbm_display_max_people );
					update_post_meta( $tour_id, 'ttbm_travel_max_people_allow', $ttbm_travel_max_people_allow );
					/***************/
					$ttbm_display_min_age = TTBM_Function::get_submit_info( 'ttbm_display_min_age' ) ? 'on' : 'off';
					$ttbm_travel_min_age  = TTBM_Function::get_submit_info( 'ttbm_travel_min_age' );
					update_post_meta( $tour_id, 'ttbm_display_min_age', $ttbm_display_min_age );
					update_post_meta( $tour_id, 'ttbm_travel_min_age', $ttbm_travel_min_age );
					/***************/
					$visible_start_location = TTBM_Function::get_submit_info( 'ttbm_display_start_location' ) ? 'on' : 'off';
					$start_location         = TTBM_Function::get_submit_info( 'ttbm_travel_start_place' );
					update_post_meta( $tour_id, 'ttbm_display_start_location', $visible_start_location );
					update_post_meta( $tour_id, 'ttbm_travel_start_place', $start_location );
					/***************/
					$ttbm_display_location = TTBM_Function::get_submit_info( 'ttbm_display_location' ) ? 'on' : 'off';
					$ttbm_location_name    = TTBM_Function::get_submit_info( 'ttbm_location_name' );
					update_post_meta( $tour_id, 'ttbm_display_location', $ttbm_display_location );
					update_post_meta( $tour_id, 'ttbm_location_name', $ttbm_location_name );
					/***************/
					$ttbm_display_map        = TTBM_Function::get_submit_info( 'ttbm_display_map' ) ? 'on' : 'off';
					$ttbm_full_location_name = TTBM_Function::get_submit_info( 'ttbm_full_location_name' );
					update_post_meta( $tour_id, 'ttbm_display_map', $ttbm_display_map );
					update_post_meta( $tour_id, 'ttbm_full_location_name', $ttbm_full_location_name );
					/***************/
					$visible_description = TTBM_Function::get_submit_info( 'ttbm_display_description' ) ? 'on' : 'off';
					$description         = TTBM_Function::get_submit_info( 'ttbm_short_description' );
					update_post_meta( $tour_id, 'ttbm_display_description', $visible_description );
					update_post_meta( $tour_id, 'ttbm_short_description', $description );
					/***************/
				}
			}
			public function save_pricing( $tour_id ) {
				if ( get_post_type( $tour_id ) == TTBM_Function::get_cpt_name() ) {
					$registration = TTBM_Function::get_submit_info( 'ttbm_display_registration' ) ? 'on' : 'off';
					update_post_meta( $tour_id, 'ttbm_display_registration', $registration );
					$advance_option = TTBM_Function::get_submit_info( 'ttbm_display_advance' ) ? 'on' : 'off';
					update_post_meta( $tour_id, 'ttbm_display_advance', $advance_option );
					//*********Tour Type**************//
					$tour_type = TTBM_Function::get_submit_info( 'ttbm_type', 'general' );
					update_post_meta( $tour_id, 'ttbm_type', $tour_type );
					//*********Hotel Configuration**************//
					$ttbm_hotels = TTBM_Function::get_submit_info( 'ttbm_hotels', array() );
					update_post_meta( $tour_id, 'ttbm_hotels', $ttbm_hotels );
					//*********Regular Ticket Price**************//
					$ttbm_travel_type = TTBM_Function::get_travel_type( $tour_id );
					if ( $ttbm_travel_type == 'particular' ) {
						$after_day = date( 'Y-m-d', strtotime( ' +500 day' ) );
						update_post_meta( $tour_id, 'ttbm_travel_reg_end_date', $after_day );
					} elseif ( $ttbm_travel_type == 'repeated' ) {
						update_post_meta( $tour_id, 'ttbm_travel_reg_end_date', TTBM_Function::get_post_info( $tour_id, 'ttbm_travel_end_date' ) );
					} else {
						update_post_meta( $tour_id, 'ttbm_travel_reg_end_date', TTBM_Function::get_post_info( $tour_id, 'ttbm_travel_reg_end_date' ) );
					}
					//*************Regular ticket***********************//
					$new_ticket_type = array();
					$icon            = TTBM_Function::get_submit_info( 'ticket_type_icon', array() );
					$names           = TTBM_Function::get_submit_info( 'ticket_type_name', array() );
					$ticket_price    = TTBM_Function::get_submit_info( 'ticket_type_price', array() );
					$sale_price      = TTBM_Function::get_submit_info( 'ticket_type_sale_price', array() );
					$qty             = TTBM_Function::get_submit_info( 'ticket_type_qty', array() );
					$qty             = apply_filters( 'ttbm_ticket_type_qty', $qty, $tour_id );
					$default_qty     = TTBM_Function::get_submit_info( 'ticket_type_default_qty', array() );
					$rsv             = TTBM_Function::get_submit_info( 'ticket_type_resv_qty', array() );
					$rsv             = apply_filters( 'ttbm_ticket_type_resv_qty', $rsv, $tour_id );
					$qty_type        = TTBM_Function::get_submit_info( 'ticket_type_qty_type', array() );
					$description     = TTBM_Function::get_submit_info( 'ticket_type_description', array() );
					$count           = count( $names );
					for ( $i = 0; $i < $count; $i ++ ) {
						if ( $names[ $i ] && $ticket_price[ $i ] >= 0 && $qty[ $i ] > 0 ) {
							$new_ticket_type[ $i ]['ticket_type_icon']        = $icon[ $i ] ?? '';
							$new_ticket_type[ $i ]['ticket_type_name']        = $names[ $i ];
							$new_ticket_type[ $i ]['ticket_type_price']       = $ticket_price[ $i ];
							$new_ticket_type[ $i ]['sale_price']              = $sale_price[ $i ];
							$new_ticket_type[ $i ]['ticket_type_qty']         = $qty[ $i ];
							$new_ticket_type[ $i ]['ticket_type_default_qty'] = $default_qty[ $i ] ?? 0;
							$new_ticket_type[ $i ]['ticket_type_resv_qty']    = $rsv[ $i ] ?? 0;
							$new_ticket_type[ $i ]['ticket_type_qty_type']    = $qty_type[ $i ] ?? 'inputbox';
							$new_ticket_type[ $i ]['ticket_type_description'] = $description[ $i ] ?? '';
						}
					}
					$ticket_type_list = apply_filters( 'ttbm_ticket_type_arr_save', $new_ticket_type );
					update_post_meta( $tour_id, 'ttbm_ticket_type', $ticket_type_list );
					//*********Extra service price**************//
					$new_extra_service         = array();
					$extra_icon                = TTBM_Function::get_submit_info( 'service_icon', array() );
					$extra_names               = TTBM_Function::get_submit_info( 'service_name', array() );
					$extra_price               = TTBM_Function::get_submit_info( 'service_price', array() );
					$extra_qty                 = TTBM_Function::get_submit_info( 'service_qty', array() );
					$extra_qty_type            = TTBM_Function::get_submit_info( 'service_qty_type', array() );
					$extra_service_description = TTBM_Function::get_submit_info( 'extra_service_description', array() );
					$extra_count               = count( $extra_names );
					for ( $i = 0; $i < $extra_count; $i ++ ) {
						if ( $extra_names[ $i ] && $extra_price[ $i ] >= 0 && $extra_qty[ $i ] > 0 ) {
							$new_extra_service[ $i ]['service_icon']              = $extra_icon[ $i ] ?? '';
							$new_extra_service[ $i ]['service_name']              = $extra_names[ $i ];
							$new_extra_service[ $i ]['service_price']             = $extra_price[ $i ];
							$new_extra_service[ $i ]['service_qty']               = $extra_qty[ $i ];
							$new_extra_service[ $i ]['service_qty_type']          = $extra_qty_type[ $i ] ?? 'inputbox';
							$new_extra_service[ $i ]['extra_service_description'] = $extra_service_description[ $i ] ?? '';
						}
					}
					$extra_service_data_arr = apply_filters( 'ttbm_extra_service_arr_save', $new_extra_service );
					update_post_meta( $tour_id, 'ttbm_extra_service_data', $extra_service_data_arr );
				}
			}
			public function save_features( $tour_id ) {
				if ( get_post_type( $tour_id ) == TTBM_Function::get_cpt_name() ) {
					$this->save_feature_data( $tour_id );
				}
			}
			public function save_feature_data( $tour_id ) {
				$include_service = TTBM_Function::get_submit_info( 'ttbm_display_include_service' ) ? 'on' : 'off';
				$exclude_service = TTBM_Function::get_submit_info( 'ttbm_display_exclude_service' ) ? 'on' : 'off';
				update_post_meta( $tour_id, 'ttbm_display_include_service', $include_service );
				update_post_meta( $tour_id, 'ttbm_display_exclude_service', $exclude_service );
				$include     = TTBM_Function::get_submit_info( 'ttbm_service_included_in_price', array() );
				$new_include = TTBM_Function::feature_id_to_array( $include );
				update_post_meta( $tour_id, 'ttbm_service_included_in_price', $new_include );
				$exclude     = TTBM_Function::get_submit_info( 'ttbm_service_excluded_in_price', array() );
				$new_exclude = TTBM_Function::feature_id_to_array( $exclude );
				update_post_meta( $tour_id, 'ttbm_service_excluded_in_price', $new_exclude );
			}
			public function save_place_you_see( $tour_id ) {
				if ( get_post_type( $tour_id ) == TTBM_Function::get_cpt_name() ) {
					$place_info = array();
					$hiphop     = TTBM_Function::get_submit_info( 'ttbm_display_hiphop' ) ? 'on' : 'off';
					update_post_meta( $tour_id, 'ttbm_display_hiphop', $hiphop );
					$place_labels = TTBM_Function::get_submit_info( 'ttbm_place_label', array() );
					$place_ids    = TTBM_Function::get_submit_info( 'ttbm_city_place_id', array() );
					if ( sizeof( $place_ids ) > 0 ) {
						foreach ( $place_ids as $key => $place_id ) {
							if ( $place_id && $place_id > 0 ) {
								$place_name                               = $place_labels[ $key ];
								$place_info[ $key ]['ttbm_city_place_id'] = $place_id;
								$place_info[ $key ]['ttbm_place_label']   = $place_name ?: get_the_title( $place_id );
							}
						}
					}
					update_post_meta( $tour_id, 'ttbm_hiphop_places', $place_info );
				}
			}
			public function save_ttbm_repeated_setting( $tour_id, $meta_key ) {
				$array        = TTBM_Setting_faq_day_wise_details::get_ttbm_repeated_setting_array( $meta_key );
				$title_name   = $array['title_name'];
				$image_name   = $array['img_name'];
				$content_name = $array['content_name'];
				if ( get_post_type( $tour_id ) == TTBM_Function::get_cpt_name() ) {
					$new_data = array();
					$title    = TTBM_Function::get_submit_info( $title_name, array() );
					$images   = TTBM_Function::get_submit_info( $image_name, array() );
					$content  = $_POST[ $content_name ] ?? array();
					$count    = count( $title );
					if ( $count > 0 ) {
						for ( $i = 0; $i < $count; $i ++ ) {
							if ( $title[ $i ] != '' ) {
								$new_data[ $i ][ $title_name ] = $title[ $i ];
								if ( $images[ $i ] != '' ) {
									$new_data[ $i ][ $image_name ] = $images[ $i ];
								}
								if ( $content[ $i ] != '' ) {
									$new_data[ $i ][ $content_name ] = htmlentities( $content[ $i ] );
								}
							}
						}
					}
					update_post_meta( $tour_id, $meta_key, $new_data );
					if ( $meta_key == 'ttbm_daywise_details' ) {
						$schedule = TTBM_Function::get_submit_info( 'ttbm_display_schedule' ) ? 'on' : 'off';
						update_post_meta( $tour_id, 'ttbm_display_schedule', $schedule );
					}
					if ( $meta_key == 'mep_event_faq' ) {
						$faq = TTBM_Function::get_submit_info( 'ttbm_display_faq' ) ? 'on' : 'off';
						update_post_meta( $tour_id, 'ttbm_display_faq', $faq );
					}
				}
			}
			public function save_why_chose_us( $tour_id ) {
				if ( get_post_type( $tour_id ) == TTBM_Function::get_cpt_name() ) {
					$why_chose_us_info  = array();
					$why_choose_display = TTBM_Function::get_submit_info( 'ttbm_display_why_choose_us' ) ? 'on' : 'off';
					update_post_meta( $tour_id, 'ttbm_display_why_choose_us', $why_choose_display );
					$why_chose_infos = TTBM_Function::get_submit_info( 'ttbm_why_choose_us_texts', array() );
					if ( sizeof( $why_chose_infos ) > 0 ) {
						foreach ( $why_chose_infos as $why_chose ) {
							if ( $why_chose ) {
								$why_chose_us_info[] = $why_chose;
							}
						}
					}
					update_post_meta( $tour_id, 'ttbm_why_choose_us_texts', $why_chose_us_info );
				}
			}
			public function save_activities( $tour_id ) {
				if ( get_post_type( $tour_id ) == TTBM_Function::get_cpt_name() ) {
					$display_activities = TTBM_Function::get_submit_info( 'ttbm_display_activities' ) ? 'on' : 'off';
					update_post_meta( $tour_id, 'ttbm_display_activities', $display_activities );
					$activities = TTBM_Function::get_submit_info( 'ttbm_tour_activities' ,array());
					update_post_meta( $tour_id, 'ttbm_tour_activities', $activities );
				}
			}
			public function save_slider_gallery( $tour_id ) {
				if ( get_post_type( $tour_id ) == TTBM_Function::get_cpt_name() ) {
					$slider = TTBM_Function::get_submit_info( 'ttbm_display_slider' ) ? 'on' : 'off';
					update_post_meta( $tour_id, 'ttbm_display_slider', $slider );
					$images     = TTBM_Function::get_submit_info( 'ttbm_gallery_images', array() );
					$all_images = explode( ',', $images );
					update_post_meta( $tour_id, 'ttbm_gallery_images', $all_images );
				}
			}
			public function save_map( $tour_id ) {
				if ( get_post_type( $tour_id ) == 'ttbm_places' ) {
					$address = TTBM_Function::get_submit_info( 'ttbm_place_address' );
					$lat     = TTBM_Function::get_submit_info( 'ttbm_place_lat' );
					$lon     = TTBM_Function::get_submit_info( 'ttbm_place_lon' );
					update_post_meta( $tour_id, 'ttbm_place_address', $address );
					update_post_meta( $tour_id, 'ttbm_place_lat', $lat );
					update_post_meta( $tour_id, 'ttbm_place_lon', $lon );
				}
			}
			public function save_extras( $tour_id ) {
				if ( get_post_type( $tour_id ) == TTBM_Function::get_cpt_name() ) {
					$get_question = TTBM_Function::get_submit_info( 'ttbm_display_get_question' ) ? 'on' : 'off';
					update_post_meta( $tour_id, 'ttbm_display_get_question', $get_question );
					$email = TTBM_Function::get_submit_info( 'ttbm_contact_email' );
					$phone = TTBM_Function::get_submit_info( 'ttbm_contact_phone' );
					$des   = TTBM_Function::get_submit_info( 'ttbm_contact_text' );
					update_post_meta( $tour_id, 'ttbm_contact_email', $email );
					update_post_meta( $tour_id, 'ttbm_contact_phone', $phone );
					update_post_meta( $tour_id, 'ttbm_contact_text', $des );
					$ttbm_display_tour_guide = TTBM_Function::get_submit_info( 'ttbm_display_tour_guide' ) ? 'on' : 'off';
					update_post_meta( $tour_id, 'ttbm_display_tour_guide', $ttbm_display_tour_guide );
					$ttbm_tour_guide = TTBM_Function::get_submit_info( 'ttbm_tour_guide', array() );
					update_post_meta( $tour_id, 'ttbm_tour_guide', $ttbm_tour_guide );
				}
			}
			public function save_related_tour( $tour_id ) {
				if ( get_post_type( $tour_id ) == TTBM_Function::get_cpt_name() ) {
					$related = TTBM_Function::get_submit_info( 'ttbm_display_related' ) ? 'on' : 'off';
					update_post_meta( $tour_id, 'ttbm_display_related', $related );
					$related_tours = TTBM_Function::get_submit_info( 'ttbm_related_tour', array() );
					update_post_meta( $tour_id, 'ttbm_related_tour', $related_tours );
				}
			}
			public function save_details_display( $tour_id ) {
				if ( get_post_type( $tour_id ) == TTBM_Function::get_cpt_name() ) {
					$content_title_style = TTBM_Function::get_submit_info( 'ttbm_section_title_style' ) ?: 'style_1';
					$ticketing_system    = TTBM_Function::get_submit_info( 'ttbm_ticketing_system', 'availability_section' );
					$seat_info           = TTBM_Function::get_submit_info( 'ttbm_display_seat_details' ) ? 'on' : 'off';
					$sidebar             = TTBM_Function::get_submit_info( 'ttbm_display_sidebar' ) ? 'on' : 'off';
					$tour_type           = TTBM_Function::get_submit_info( 'ttbm_display_tour_type' ) ? 'on' : 'off';
					$hotels              = TTBM_Function::get_submit_info( 'ttbm_display_hotels' ) ? 'on' : 'off';
					$duration            = TTBM_Function::get_submit_info( 'ttbm_display_duration' ) ? 'on' : 'off';
					update_post_meta( $tour_id, 'ttbm_section_title_style', $content_title_style );
					update_post_meta( $tour_id, 'ttbm_ticketing_system', $ticketing_system );
					update_post_meta( $tour_id, 'ttbm_display_seat_details', $seat_info );
					update_post_meta( $tour_id, 'ttbm_display_sidebar', $sidebar );
					update_post_meta( $tour_id, 'ttbm_display_tour_type', $tour_type );
					update_post_meta( $tour_id, 'ttbm_display_hotels', $hotels );
					update_post_meta( $tour_id, 'ttbm_display_duration', $duration );
				}
			}
			/************************/
			public function save_hotel( $post_id ) {
				if ( ! isset( $_POST['ttbm_hotel_type_nonce'] ) || ! wp_verify_nonce( $_POST['ttbm_hotel_type_nonce'], 'ttbm_hotel_type_nonce' ) && defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE && ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
				if ( get_post_type( $post_id ) == 'ttbm_hotel' ) {
					$ttbm_display_location = TTBM_Function::get_submit_info( 'ttbm_display_hotel_location' ) ? 'on' : 'off';
					$ttbm_location_name    = TTBM_Function::get_submit_info( 'ttbm_hotel_location' );
					update_post_meta( $post_id, 'ttbm_display_hotel_location', $ttbm_display_location );
					update_post_meta( $post_id, 'ttbm_hotel_location', $ttbm_location_name );
					/***************/
					$ttbm_display_distance   = TTBM_Function::get_submit_info( 'ttbm_display_hotel_distance' ) ? 'on' : 'off';
					$ttbm_hotel_distance_des = TTBM_Function::get_submit_info( 'ttbm_hotel_distance_des' );
					update_post_meta( $post_id, 'ttbm_display_hotel_distance', $ttbm_display_distance );
					update_post_meta( $post_id, 'ttbm_hotel_distance_des', $ttbm_hotel_distance_des );
					/***************/
					$this->save_hotel_price( $post_id );
					/***************/
					$this->save_feature_data( $post_id );
					/*********************************/
					do_action('ttbm_hotel_settings_save',$post_id);
				}
			}
			public function save_hotel_price( $post_id ) {
				$advance_option = TTBM_Function::get_submit_info( 'ttbm_display_advance' ) ? 'on' : 'off';
				update_post_meta( $post_id, 'ttbm_display_advance', $advance_option );
				/************************/
				$old_ticket_type = TTBM_Function::get_post_info( $post_id, 'ttbm_room_details', array() );
				$new_ticket_type = array();
				$icon            = TTBM_Function::get_submit_info( 'room_type_icon', array() );
				$names           = TTBM_Function::get_submit_info( 'ttbm_hotel_room_name', array() );
				$ticket_price    = TTBM_Function::get_submit_info( 'ttbm_hotel_room_price', array() );
				$sale_price      = TTBM_Function::get_submit_info( 'sale_price', array() );
				$qty             = TTBM_Function::get_submit_info( 'ttbm_hotel_room_qty', array() );
				$adult_qty       = TTBM_Function::get_submit_info( 'ttbm_hotel_room_capacity_adult', array() );
				$child_qty       = TTBM_Function::get_submit_info( 'ttbm_hotel_room_capacity_child', array() );
				$rsv             = TTBM_Function::get_submit_info( 'room_reserve_qty', array() );
				$qty_type        = TTBM_Function::get_submit_info( 'room_qty_type', array() );
				$description     = TTBM_Function::get_submit_info( 'room_description', array() );
				$count           = count( $names );
				for ( $i = 0; $i < $count; $i ++ ) {
					if ( $names[ $i ] && $ticket_price[ $i ] >= 0 && $qty[ $i ] > 0 ) {
						$new_ticket_type[ $i ]['room_type_icon']                 = $icon[ $i ] ?? '';
						$new_ticket_type[ $i ]['ttbm_hotel_room_name']           = $names[ $i ];
						$new_ticket_type[ $i ]['ttbm_hotel_room_price']          = $ticket_price[ $i ];
						$new_ticket_type[ $i ]['sale_price']                     = $sale_price[ $i ];
						$new_ticket_type[ $i ]['ttbm_hotel_room_qty']            = $qty[ $i ];
						$new_ticket_type[ $i ]['ttbm_hotel_room_capacity_adult'] = $adult_qty[ $i ] ?? 0;
						$new_ticket_type[ $i ]['ttbm_hotel_room_capacity_child'] = $child_qty[ $i ] ?? 0;
						$new_ticket_type[ $i ]['room_reserve_qty']               = $rsv[ $i ] ?? 0;
						$new_ticket_type[ $i ]['room_qty_type']                  = $qty_type[ $i ] ?? 'inputbox';
						$new_ticket_type[ $i ]['room_description']               = $description[ $i ] ?? '';
					}
				}
				$ticket_type_list = apply_filters( 'ttbm_hotel_type_arr_save', $new_ticket_type );
				if ( ! empty( $ticket_type_list ) && $ticket_type_list != $old_ticket_type ) {
					update_post_meta( $post_id, 'ttbm_room_details', $ticket_type_list );
				} elseif ( empty( $ticket_type_list ) && $old_ticket_type ) {
					delete_post_meta( $post_id, 'ttbm_room_details', $old_ticket_type );
				}
			}
			/************************/
			public function ttbm_new_feature_save() {
				$feature_name        = TTBM_Function::data_sanitize( $_POST['feature_name'] );
				$feature_description = TTBM_Function::data_sanitize( $_POST['feature_description'] );
				$feature_icon        = TTBM_Function::data_sanitize( $_POST['feature_icon'] );
				$query               = wp_insert_term( $feature_name,   // the term
					'ttbm_tour_features_list', // the taxonomy
					array(
						'description' => $feature_description
					) );
				if ( is_array( $query ) && $query['term_id'] != '' ) {
					$term_id = $query['term_id'];
					update_term_meta( $term_id, 'ttbm_feature_icon', $feature_icon );
				}
				die();
			}
			public function ttbm_new_activity_save() {
				$name        = TTBM_Function::data_sanitize( $_POST['activity_name'] );
				$description = TTBM_Function::data_sanitize( $_POST['activity_description'] );
				$icon        = TTBM_Function::data_sanitize( $_POST['activity_icon'] );
				$query       = wp_insert_term( $name,   // the term
					'ttbm_tour_activities', // the taxonomy
					array(
						'description' => $description
					) );
				if ( is_array( $query ) && $query['term_id'] != '' ) {
					$term_id = $query['term_id'];
					update_term_meta( $term_id, 'ttbm_activities_icon', $icon );
				}
				die();
			}
			public function ttbm_new_place_save() {
				$place_name        = TTBM_Function::data_sanitize( $_POST['place_name'] );
				$place_description = TTBM_Function::data_sanitize( $_POST['place_description'] );
				$place_image       = TTBM_Function::data_sanitize( $_POST['place_image'] );
				$args              = array(
					'post_title'   => $place_name,
					'post_content' => $place_description,
					'post_status'  => 'publish',
					'post_type'    => 'ttbm_places'
				);
				$query             = wp_insert_post( $args );
				if ( $query ) {
					set_post_thumbnail( $query, $place_image );
				}
				die();
			}
			public function ttbm_new_location_save() {
				$name        = TTBM_Function::data_sanitize( $_POST['name'] );
				$description = TTBM_Function::data_sanitize( $_POST['description'] );
				$address     = TTBM_Function::data_sanitize( $_POST['address'] );
				$country     = TTBM_Function::data_sanitize( $_POST['country'] );
				$image       = TTBM_Function::data_sanitize( $_POST['image'] );
				$query       = wp_insert_term( $name,   // the term
					'ttbm_tour_location', // the taxonomy
					array(
						'description' => $description
					) );
				if ( is_array( $query ) && $query['term_id'] != '' ) {
					$term_id = $query['term_id'];
					update_term_meta( $term_id, 'ttbm_location_address', $address );
					update_term_meta( $term_id, 'ttbm_country_location', $country );
					update_term_meta( $term_id, 'ttbm_location_image', $image );
				}
				die();
			}
		}
		new TTBM_Save();
	}