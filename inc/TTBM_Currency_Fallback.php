<?php
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	/**
	 * Store currency while WooCommerce is not loaded (Custom Payment mode without
	 * WooCommerce). The WooCommerce fallback shims in tour-booking-manager.php
	 * read it, so tour prices keep the currency the admin chose in WooCommerce
	 * -- its options stay in wp_options after it is deactivated -- and match the
	 * currency the custom checkout charges in (TTBM_Custom_Checkout::currency_code()).
	 *
	 * The legacy `ttbm_currency_settings` option, when present, still overrides
	 * the matching WooCommerce value.
	 */
	if (!class_exists('TTBM_Currency_Fallback')) {
		class TTBM_Currency_Fallback {
			public static function currency(): string {
				$currency = (string) apply_filters('woocommerce_currency', get_option('woocommerce_currency', 'USD'));
				return '' !== $currency ? $currency : 'USD';
			}
			public static function settings(): array {
				$decimal_sep = (string) get_option('woocommerce_price_decimal_sep', '.');
				$settings = wp_parse_args(
					(array) get_option('ttbm_currency_settings', array()),
					array(
						'position'     => (string) get_option('woocommerce_currency_pos', 'left'),
						'decimal_sep'  => '' !== $decimal_sep ? stripslashes($decimal_sep) : '.',
						'thousand_sep' => stripslashes((string) get_option('woocommerce_price_thousand_sep', ',')),
						'num_decimals' => absint(get_option('woocommerce_price_num_decimals', 2)),
					)
				);
				if (!isset($settings['symbol'])) {
					$settings['symbol'] = self::symbol();
				}
				return $settings;
			}
			public static function symbol($currency = ''): string {
				if (!$currency) {
					$currency = self::currency();
				}
				$symbols = self::symbols();
				$symbol = isset($symbols[$currency]) ? $symbols[$currency] : '';
				return (string) apply_filters('woocommerce_currency_symbol', $symbol, $currency);
			}
			// Copied from WooCommerce get_woocommerce_currency_symbols() (11.2.1).
			public static function symbols(): array {
				return (array) apply_filters('woocommerce_currency_symbols', array(
					'AED' => '&#x62f;.&#x625;',
					'AFN' => '&#x60b;',
					'ALL' => 'L',
					'AMD' => 'AMD',
					'ANG' => '&fnof;',
					'AOA' => 'Kz',
					'ARS' => '&#36;',
					'AUD' => '&#36;',
					'AWG' => 'Afl.',
					'AZN' => '&#8380;',
					'BAM' => 'KM',
					'BBD' => '&#36;',
					'BDT' => '&#2547;&nbsp;',
					'BGN' => '&#1083;&#1074;.',
					'BHD' => '.&#x62f;.&#x628;',
					'BIF' => 'Fr',
					'BMD' => '&#36;',
					'BND' => '&#36;',
					'BOB' => 'Bs.',
					'BRL' => '&#82;&#36;',
					'BSD' => '&#36;',
					'BTC' => '&#3647;',
					'BTN' => 'Nu.',
					'BWP' => 'P',
					'BYR' => 'Br',
					'BYN' => 'Br',
					'BZD' => '&#36;',
					'CAD' => '&#36;',
					'CDF' => 'Fr',
					'CHF' => '&#67;&#72;&#70;',
					'CHW' => 'CHW',
					'CLP' => '&#36;',
					'CNY' => '&yen;',
					'COP' => '&#36;',
					'CRC' => '&#x20a1;',
					'CUC' => '&#36;',
					'CUP' => '&#36;',
					'CVE' => '&#36;',
					'CZK' => '&#75;&#269;',
					'DJF' => 'Fr',
					'DKK' => 'kr.',
					'DOP' => 'RD&#36;',
					'DZD' => '&#x62f;.&#x62c;',
					'EGP' => 'EGP',
					'ERN' => 'Nfk',
					'ETB' => 'Br',
					'EUR' => '&euro;',
					'FJD' => '&#36;',
					'FKP' => '&pound;',
					'GBP' => '&pound;',
					'GEL' => '&#x20be;',
					'GGP' => '&pound;',
					'GHS' => '&#x20b5;',
					'GIP' => '&pound;',
					'GMD' => 'D',
					'GNF' => 'Fr',
					'GTQ' => 'Q',
					'GYD' => '&#36;',
					'HKD' => '&#36;',
					'HNL' => 'L',
					'HRK' => 'kn',
					'HTG' => 'G',
					'HUF' => '&#70;&#116;',
					'IDR' => 'Rp',
					'ILS' => '&#8362;',
					'IMP' => '&pound;',
					'INR' => '&#8377;',
					'IQD' => '&#x62f;.&#x639;',
					'IRR' => '&#xfdfc;',
					'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
					'ISK' => 'kr.',
					'JEP' => '&pound;',
					'JMD' => '&#36;',
					'JOD' => '&#x62f;.&#x627;',
					'JPY' => '&yen;',
					'KES' => 'KSh',
					'KGS' => '&#x441;&#x43e;&#x43c;',
					'KHR' => '&#x17db;',
					'KMF' => 'Fr',
					'KPW' => '&#x20a9;',
					'KRW' => '&#8361;',
					'KWD' => '&#x62f;.&#x643;',
					'KYD' => '&#36;',
					'KZT' => '&#8376;',
					'LAK' => '&#8365;',
					'LBP' => '&#x644;.&#x644;',
					'LKR' => '&#xdbb;&#xdd4;',
					'LRD' => '&#36;',
					'LSL' => 'L',
					'LYD' => '&#x62f;.&#x644;',
					'MAD' => '&#x62f;.&#x645;.',
					'MDL' => 'MDL',
					'MGA' => 'Ar',
					'MKD' => '&#x434;&#x435;&#x43d;',
					'MMK' => 'Ks',
					'MNT' => '&#x20ae;',
					'MOP' => 'MOP&#36;',
					'MRU' => 'UM',
					'MUR' => '&#x20a8;',
					'MVR' => '.&#x783;',
					'MWK' => 'MK',
					'MXN' => '&#36;',
					'MYR' => '&#82;&#77;',
					'MZN' => 'MT',
					'NAD' => 'N&#36;',
					'NGN' => '&#8358;',
					'NIO' => 'C&#36;',
					'NOK' => '&#107;&#114;',
					'NPR' => '&#8360;',
					'NZD' => '&#36;',
					'OMR' => '&#x631;.&#x639;.',
					'PAB' => 'B/.',
					'PEN' => 'S/',
					'PGK' => 'K',
					'PHP' => '&#8369;',
					'PKR' => '&#8360;',
					'PLN' => '&#122;&#322;',
					'PRB' => '&#x440;.',
					'PYG' => '&#8370;',
					'QAR' => '&#x631;.&#x642;',
					'RMB' => '&yen;',
					'RON' => 'lei',
					'RSD' => '&#1088;&#1089;&#1076;',
					'RUB' => '&#8381;',
					'RWF' => 'Fr',
					'SAR' => '&#x631;.&#x633;',
					'SBD' => '&#36;',
					'SCR' => '&#x20a8;',
					'SDG' => '&#x62c;.&#x633;.',
					'SEK' => '&#107;&#114;',
					'SGD' => '&#36;',
					'SHP' => '&pound;',
					'SLL' => 'Le',
					'SOS' => 'Sh',
					'SRD' => '&#36;',
					'SSP' => '&pound;',
					'STN' => 'Db',
					'SYP' => '&#x644;.&#x633;',
					'SZL' => 'E',
					'THB' => '&#3647;',
					'TJS' => '&#x405;&#x41c;',
					'TMT' => 'm',
					'TND' => '&#x62f;.&#x62a;',
					'TOP' => 'T&#36;',
					'TRY' => '&#8378;',
					'TTD' => '&#36;',
					'TWD' => '&#78;&#84;&#36;',
					'TZS' => 'Sh',
					'UAH' => '&#8372;',
					'UGX' => 'UGX',
					'USD' => '&#36;',
					'UYU' => '&#36;',
					'UZS' => 'UZS',
					'VEF' => 'Bs F',
					'VES' => 'Bs.',
					'VND' => '&#8363;',
					'VUV' => 'Vt',
					'WST' => 'T',
					'XAF' => 'CFA',
					'XCD' => '&#36;',
					'XOF' => 'CFA',
					'XPF' => 'XPF',
					'YER' => '&#xfdfc;',
					'ZAR' => '&#82;',
					'ZMW' => 'K',
				));
			}
		}
	}
