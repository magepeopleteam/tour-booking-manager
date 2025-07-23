<?php
	/*
	* @Author 	:	MagePeople Inc
	* Copyright	: 	mage-people.com
	* Developer :   Mahin
	* Version	:	1.0.0
	*/

	/*******************/
	function ttbm_array_strip( $array_or_string ) {
		if ( is_string( $array_or_string ) ) {
			$array_or_string = sanitize_text_field( $array_or_string );
		} elseif ( is_array( $array_or_string ) ) {
			foreach ( $array_or_string as &$value ) {
				if ( is_array( $value ) ) {
					$value = ttbm_array_strip( $value );
				} else {
					$value = sanitize_text_field( $value );
				}
			}
		}
		return $array_or_string;
	}
