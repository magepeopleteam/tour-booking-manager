<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	}

	if ( ! function_exists( 'ttbm_parse_day_title' ) ) {
		function ttbm_parse_day_title( $title, $index ) {
			$num           = $index + 1;
			$display_title = $title;

			if ( preg_match( '/^Day\s*(\d+)\s*[:\-]\s*(.+)$/i', $title, $matches ) ) {
				$num           = (int) $matches[1];
				$display_title = trim( $matches[2] );
			}

			return array(
				'num'   => $num,
				'badge' => str_pad( (string) $num, 2, '0', STR_PAD_LEFT ),
				'title' => $display_title,
			);
		}
	}

	if ( ! function_exists( 'ttbm_day_category_label' ) ) {
		function ttbm_day_category_label( $title ) {
			$text = strtolower( $title );

			if ( preg_match( '/arrival|check.?in/', $text ) ) {
				return esc_html__( 'Arrival Day', 'tour-booking-manager' );
			}
			if ( preg_match( '/pompeii|vesuvius|historical|museum|archaeological/', $text ) ) {
				return esc_html__( 'Historical Excursion', 'tour-booking-manager' );
			}
			if ( preg_match( '/positano|amalfi|coast|ravello|capri|scenic|beach/', $text ) ) {
				return esc_html__( 'Coastal Adventure', 'tour-booking-manager' );
			}
			if ( preg_match( '/departure|day trip/', $text ) ) {
				return esc_html__( 'Final Day', 'tour-booking-manager' );
			}
			if ( preg_match( '/leisure|free time|shopping/', $text ) ) {
				return esc_html__( 'Leisure Day', 'tour-booking-manager' );
			}

			return esc_html__( 'Daily Itinerary', 'tour-booking-manager' );
		}
	}

	if ( ! function_exists( 'ttbm_day_activity_icon' ) ) {
		function ttbm_day_activity_icon( $text ) {
			$text = strtolower( $text );

			if ( preg_match( '/\bmorning\b|\bbreakfast\b|\bsunrise\b|\barrive\b/', $text ) ) {
				return 'fa-sun';
			}
			if ( preg_match( '/\blunch\b|\bdinner\b|\bmeal\b|\beat\b|\bcuisine\b|\bpizza\b|\bcaf/', $text ) ) {
				return 'fa-utensils';
			}
			if ( preg_match( '/\bevening\b|\bnight\b|\bovernight\b|\bhotel\b|\bstroll\b|\bcheck in\b/', $text ) ) {
				return 'fa-moon';
			}
			if ( preg_match( '/\breturn\b|\bdeparture\b|\bferry\b|\bdrive\b|\bvisit\b|\bexplore\b/', $text ) ) {
				return 'fa-route';
			}

			return 'fa-map-marker-alt';
		}
	}

	if ( ! function_exists( 'ttbm_render_day_activities' ) ) {
		function ttbm_render_day_activities( $html ) {
			$html = html_entity_decode( $html );

			if ( empty( trim( wp_strip_all_tags( $html ) ) ) ) {
				return '';
			}

			libxml_use_internal_errors( true );
			$doc = new DOMDocument();
			$doc->loadHTML( mb_convert_encoding( '<div id="ttbm-day-root">' . $html . '</div>', 'HTML-ENTITIES', 'UTF-8' ) );
			libxml_clear_errors();

			$root = $doc->getElementById( 'ttbm-day-root' );
			$lis  = $root ? $root->getElementsByTagName( 'li' ) : null;

			if ( ! $lis || ! $lis->length ) {
				return '<div class="day_wise_details_item_details ttbm_wp_editor day_wise_details_fallback">' . wp_kses_post( $html ) . '</div>';
			}

			$output = '<div class="day_wise_details_item_details"><ul class="day_wise_activity_list">';

			foreach ( $lis as $li ) {
				$plain = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $li->textContent ) ) );
				$inner = '';

				foreach ( $li->childNodes as $child ) {
					$inner .= $doc->saveHTML( $child );
				}

				$inner       = trim( $inner );
				$icon        = ttbm_day_activity_icon( $plain );
				$act_title   = $plain;
				$act_desc    = '';
				$has_heading = false;

				if ( preg_match( '/^([^:]+):\s*(.+)$/', $plain, $matches ) ) {
					$has_heading = true;
					$remainder   = trim( $matches[2] );
					$parts       = preg_split( '/\.\s+/', $remainder, 2 );
					$lead        = trim( preg_replace( '/\.\s*$/', '', $parts[0] ) );
					$act_title   = trim( $matches[1] ) . ': ' . $lead;

					if ( isset( $parts[1] ) && trim( $parts[1] ) !== '' ) {
						$act_desc = trim( $parts[1] );
					}
				}

				$output .= '<li class="day_wise_activity_item">';
				$output .= '<span class="day_wise_activity_icon" aria-hidden="true"><i class="fas ' . esc_attr( $icon ) . '"></i></span>';
				$output .= '<div class="day_wise_activity_body">';

				if ( $has_heading && $act_desc ) {
					$output .= '<p class="day_wise_activity_title">' . esc_html( $act_title ) . '</p>';
					$output .= '<p class="day_wise_activity_desc">' . esc_html( $act_desc ) . '</p>';
				} else {
					$output .= '<p class="day_wise_activity_title">' . wp_kses_post( $inner ? $inner : esc_html( $plain ) ) . '</p>';
				}

				$output .= '</div></li>';
			}

			$output .= '</ul></div>';

			return $output;
		}
	}

	$ttbm_post_id    = $ttbm_post_id ?? get_the_id();
	$daywise         = get_post_meta( $ttbm_post_id, 'ttbm_daywise_details', true );
	$display_daywise = get_post_meta( $ttbm_post_id, 'ttbm_display_schedule', true );

	if ( ! empty( $daywise ) && $display_daywise == 'on' ) {
		?>
		<div class="day-wise-details-area">
			<h2 class="content-title ttbm_section_title"><?php esc_html_e( 'Daily Schedule', 'tour-booking-manager' ); ?></h2>

			<div class="ttbm_widget_content ttbm_day_wise_details ttbm_day_wise_timeline">
				<?php
				foreach ( $daywise as $key => $day ) {
					$parsed       = ttbm_parse_day_title( $day['ttbm_day_title'], (int) $key );
					$is_first     = ( 0 === (int) $key );
					$collapse_id  = '#ttbm_day_datails_' . esc_attr( $key );
					$title_class  = 'day_wise_details_item_title justifyBetween' . ( $is_first ? ' mActive active' : '' );
					$item_class   = 'day_wise_details_item' . ( $is_first ? ' is-active' : '' );
					$category     = ttbm_day_category_label( $parsed['title'] );
					?>
					<div class="<?php echo esc_attr( $item_class ); ?>">
						<div class="day_wise_details_marker" aria-hidden="true"><?php echo esc_html( $parsed['badge'] ); ?></div>
						<div class="day_wise_details_card">
							<h5 class="<?php echo esc_attr( $title_class ); ?>" data-open-icon="fa-chevron-down" data-close-icon="fa-chevron-up" data-collapse-target="<?php echo esc_attr( $collapse_id ); ?>">
								<span class="day_wise_details_header">
									<span class="day_wise_details_label"><?php echo esc_html( $category ); ?></span>
									<span class="day_wise_details_name"><?php echo esc_html( $parsed['title'] ); ?></span>
								</span>
								<span data-icon class="fas fa-chevron-<?php echo $is_first ? 'up' : 'down'; ?>"></span>
							</h5>
							<div data-collapse="<?php echo esc_attr( $collapse_id ); ?>"<?php echo $is_first ? ' class="mActive"' : ' style="display: none;"'; ?>>
								<?php echo ttbm_render_day_activities( $day['ttbm_day_content'] ); ?>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	<?php } ?>
