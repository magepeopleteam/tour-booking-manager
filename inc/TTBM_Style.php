<?php
	/*
	* @Author 		magePeople
	* Copyright: 	mage-people.com
	*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'TTBM_Style' ) ) {
		class TTBM_Style {
			public function __construct() {
				add_action( 'wp_head', array( $this, 'ttbm_style' ), 100 );
				add_action( 'admin_head', array( $this, 'ttbm_style' ), 100 );
			}
			public function ttbm_style() {
				if ( ! class_exists( 'TTBM_Woocommerce_Plugin_Pro' ) ) {
					?>
					<style>
						#mage_row_ttbm_particular_dates,
						#mage_row_mep_ticket_times_global,
						#mage_row_mep_disable_ticket_time,
						#mage_row_mep_ticket_times_sat,
						#mage_row_mep_ticket_times_sun,
						#mage_row_mep_ticket_times_mon,
						#mage_row_mep_ticket_times_tue,
						#mage_row_mep_ticket_times_wed,
						#mage_row_mep_ticket_times_thu,
						#mage_row_mep_ticket_times_fri,
						#mage_row_mep_ticket_offdays,
						#mage_row_mep_ticket_off_dates{
							opacity: 0.3;
							cursor: not-allowed;
							color: rgba(44, 51, 56, .5);
						}
					</style>
					<?php
				}
			}
		}
		new TTBM_Style();
	}