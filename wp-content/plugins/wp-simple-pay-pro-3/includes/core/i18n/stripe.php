<?php
/**
 * Internationalization: Stripe
 *
 * @package SimplePay\Core\i18n
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.9.0
 */

namespace SimplePay\Core\i18n;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns Stripe's supported countries.
 *
 * @since 3.9.0
 *
 * @return array
 */
function get_stripe_countries() {
	$countries = array(
		'AU' => __( 'Australia', 'simple-pay' ),
		'AT' => __( 'Austria', 'simple-pay' ),
		'BE' => __( 'Belgium', 'simple-pay' ),
		'BR' => __( 'Brazil', 'simple-pay' ),
		'BG' => __( 'Bulgaria', 'simple-pay' ),
		'CA' => __( 'Canada', 'simple-pay' ),
		'HR' => __( 'Croatia', 'simple-pay' ),
		'CY' => __( 'Cyprus', 'simple-pay' ),
		'CZ' => __( 'Czech Republic', 'simple-pay' ),
		'DK' => __( 'Denmark', 'simple-pay' ),
		'EE' => __( 'Estonia', 'simple-pay' ),
		'FI' => __( 'Finland', 'simple-pay' ),
		'FR' => __( 'France', 'simple-pay' ),
		'DE' => __( 'Germany', 'simple-pay' ),
		'GI' => __( 'Gibraltar', 'simple-pay' ),
		'GR' => __( 'Greece', 'simple-pay' ),
		'HK' => __( 'Hong Kong', 'simple-pay' ),
		'HU' => __( 'Hungary', 'simple-pay' ),
		'IN' => __( 'India', 'simple-pay' ),
		'IE' => __( 'Ireland', 'simple-pay' ),
		'IT' => __( 'Italy', 'simple-pay' ),
		'JP' => __( 'Japan', 'simple-pay' ),
		'LV' => __( 'Latvia', 'simple-pay' ),
		'LI' => __( 'Liechtenstein', 'simple-pay' ),
		'LT' => __( 'Lithuania', 'simple-pay' ),
		'LU' => __( 'Luxembourg', 'simple-pay' ),
		'MY' => __( 'Malaysia', 'simple-pay' ),
		'MT' => __( 'Malta', 'simple-pay' ),
		'MX' => __( 'Mexico', 'simple-pay' ),
		'NL' => __( 'Netherlands', 'simple-pay' ),
		'NZ' => __( 'New Zealand', 'simple-pay' ),
		'NO' => __( 'Norway', 'simple-pay' ),
		'PL' => __( 'Poland', 'simple-pay' ),
		'PT' => __( 'Portugal', 'simple-pay' ),
		'RO' => __( 'Romania', 'simple-pay' ),
		'SG' => __( 'Singapore', 'simple-pay' ),
		'SK' => __( 'Slovakia', 'simple-pay' ),
		'SI' => __( 'Slovenia', 'simple-pay' ),
		'ES' => __( 'Spain', 'simple-pay' ),
		'SE' => __( 'Sweden', 'simple-pay' ),
		'CH' => __( 'Switzerland', 'simple-pay' ),
		'TH' => __( 'Thailand', 'simple-pay' ),
		'AE' => __( 'United Arab Emirates', 'simple-pay' ),
		'GB' => __( 'United Kingdom', 'simple-pay' ),
		'US' => __( 'United States', 'simple-pay' ),
	);

	/**
	 * Filters the countries supported by Stripe.
	 *
	 * @since 3.9.0
	 *
	 * @param array $countries Country list, keyed by country code.
	 */
	$countries = apply_filters( 'simpay_get_stripe_countries', $countries );

	return $countries;
}

/**
 * Returns Stripe Checkout's supported locales.
 *
 * @since 3.9.0
 *
 * @return array
 */
function get_stripe_checkout_locales() {
	return array(
		'auto'  => __( 'Auto-detect', 'simple-pay' ),
		'bg'    => __( 'Bulgarian (bg)', 'simple-pay' ),
		'zh'    => __( 'Chinese Simplified (zh)', 'simple-pay' ),
		'zh-HK' => __( 'Chinese Traditional (zh-HK)', 'simple-pay' ),
		'zh-TW' => __( 'Chinese Traditional (zh-TW)', 'simple-pay' ),
		'hr'    => __( 'Croatian (hr)', 'simple-pay' ),
		'cs'    => __( 'Czech (cs)', 'simple-pay' ),
		'da'    => __( 'Danish (da)', 'simple-pay' ),
		'de'    => __( 'German (de)', 'simple-pay' ),
		'el'    => __( 'Greek (el)', 'simple-pay' ),
		'en'    => __( 'English (en)', 'simple-pay' ),
		'en-GB' => __( 'English (en-gb)', 'simple-pay' ),
		'et'    => __( 'Estonian (et)', 'simple-pay' ),
		'fi'    => __( 'Finnish (fi)', 'simple-pay' ),
		'fil'   => __( 'Filipino (fil)', 'simple-pay' ),
		'fr'    => __( 'French (fr)', 'simple-pay' ),
		'fr-CA' => __( 'French (fr-ca)', 'simple-pay' ),
		'hu'    => __( 'Hungarian (hu)', 'simple-pay' ),
		'it'    => __( 'Italian (it)', 'simple-pay' ),
		'ja'    => __( 'Japanese (ja)', 'simple-pay' ),
		'ko'    => __( 'Korean (kr)', 'simple-pay' ),
		'lt'    => __( 'Lithuanian (lt)', 'simple-pay' ),
		'lv'    => __( 'Latvian (lv)', 'simple-pay' ),
		'ms'    => __( 'Malay (ms)', 'simple-pay' ),
		'mt'    => __( 'Maltese (mt)', 'simple-pay' ),
		'nb'    => __( 'Norwegian Bokmål (nb)', 'simple-pay' ),
		'nl'    => __( 'Dutch (nl)', 'simple-pay' ),
		'pl'    => __( 'Polish (pl)', 'simple-pay' ),
		'pt'    => __( 'Portuguese (pt)', 'simple-pay' ),
		'pt-BR' => __( 'Portuguese (pt-BR)', 'simple-pay' ),
		'ro'    => __( 'Romanian (ro)', 'simple-pay' ),
		'ru'    => __( 'Russian (ru)', 'simple-pay' ),
		'sk'    => __( 'Slovak (sk)', 'simple-pay' ),
		'sl'    => __( 'Slovenian (sl)', 'simple-pay' ),
		'es'    => __( 'Spanish (es)', 'simple-pay' ),
		'sv'    => __( 'Swedish (sv)', 'simple-pay' ),
		'th'    => __( 'Thai (th)', 'simple-pay' ),
		'tk'    => __( 'Turkish (tk)', 'simple-pay' ),
	);
}

/**
 * Returns Stripe Element's supported locales.
 *
 * @since 3.9.0
 *
 * @return array
 */
function get_stripe_elements_locales() {
	return array(
		'auto'  => __( 'Auto-detect', 'simple-pay' ),
		'ar'    => __( 'Arabic', 'simple-pay' ),
		'bg'    => __( 'Bulgarian (bg)', 'simple-pay' ),
		'zh'    => __( 'Chinese Simplified (zh)', 'simple-pay' ),
		'zh-HK' => __( 'Chinese Traditional (zh-HK)', 'simple-pay' ),
		'zh-TW' => __( 'Chinese Traditional (zh-TW)', 'simple-pay' ),
		'hr'    => __( 'Croatian (hr)', 'simple-pay' ),
		'cs'    => __( 'Czech (cs)', 'simple-pay' ),
		'da'    => __( 'Danish (da)', 'simple-pay' ),
		'de'    => __( 'German (de)', 'simple-pay' ),
		'el'    => __( 'Greek (el)', 'simple-pay' ),
		'en'    => __( 'English (en)', 'simple-pay' ),
		'en-GB' => __( 'English (en-gb)', 'simple-pay' ),
		'et'    => __( 'Estonian (et)', 'simple-pay' ),
		'fi'    => __( 'Finnish (fi)', 'simple-pay' ),
		'fil'   => __( 'Filipino (fil)', 'simple-pay' ),
		'fr'    => __( 'French (fr)', 'simple-pay' ),
		'fr-CA' => __( 'French (fr-ca)', 'simple-pay' ),
		'he'    => __( 'Hebrew (he)', 'simple-pay' ),
		'hu'    => __( 'Hungarian (hu)', 'simple-pay' ),
		'it'    => __( 'Italian (it)', 'simple-pay' ),
		'ja'    => __( 'Japanese (ja)', 'simple-pay' ),
		'ko'    => __( 'Korean (kr)', 'simple-pay' ),
		'lt'    => __( 'Lithuanian (lt)', 'simple-pay' ),
		'lv'    => __( 'Latvian (lv)', 'simple-pay' ),
		'ms'    => __( 'Malay (ms)', 'simple-pay' ),
		'mt'    => __( 'Maltese (mt)', 'simple-pay' ),
		'nb'    => __( 'Norwegian Bokmål (nb)', 'simple-pay' ),
		'nl'    => __( 'Dutch (nl)', 'simple-pay' ),
		'pl'    => __( 'Polish (pl)', 'simple-pay' ),
		'pt'    => __( 'Portuguese (pt)', 'simple-pay' ),
		'pt-BR' => __( 'Portuguese (pt-BR)', 'simple-pay' ),
		'ro'    => __( 'Romanian (ro)', 'simple-pay' ),
		'ru'    => __( 'Russian (ru)', 'simple-pay' ),
		'sk'    => __( 'Slovak (sk)', 'simple-pay' ),
		'sl'    => __( 'Slovenian (sl)', 'simple-pay' ),
		'es'    => __( 'Spanish (es)', 'simple-pay' ),
		'sv'    => __( 'Swedish (sv)', 'simple-pay' ),
		'th'    => __( 'Thai (th)', 'simple-pay' ),
		'tk'    => __( 'Turkish (tk)', 'simple-pay' ),
	);
}

/**
 * Returns a list of Stripe-supported currencies.
 *
 * @since 4.0.0
 *
 * @return array
 */
function get_stripe_currencies() {
	return array(
		'AED' => esc_html__( 'United Arab Emirates Dirham', 'simple-pay' ),
		'AFN' => esc_html__( 'Afghan Afghani', 'simple-pay' ),
		'ALL' => esc_html__( 'Albanian Lek', 'simple-pay' ),
		'AMD' => esc_html__( 'Armenian Dram', 'simple-pay' ),
		'ANG' => esc_html__( 'Netherlands Antillean Gulden', 'simple-pay' ),
		'AOA' => esc_html__( 'Angolan Kwanza', 'simple-pay' ),
		'ARS' => esc_html__( 'Argentine Peso', 'simple-pay' ),
		'AUD' => esc_html__( 'Australian Dollar', 'simple-pay' ),
		'AWG' => esc_html__( 'Aruban Florin', 'simple-pay' ),
		'AZN' => esc_html__( 'Azerbaijani Manat', 'simple-pay' ),
		'BAM' => esc_html__( 'Bosnia & Herzegovina Convertible Mark', 'simple-pay' ),
		'BBD' => esc_html__( 'Barbadian Dollar', 'simple-pay' ),
		'BDT' => esc_html__( 'Bangladeshi Taka', 'simple-pay' ),
		'BIF' => esc_html__( 'Burundian Franc', 'simple-pay' ),
		'BGN' => esc_html__( 'Bulgarian Lev', 'simple-pay' ),
		'BMD' => esc_html__( 'Bermudian Dollar', 'simple-pay' ),
		'BND' => esc_html__( 'Brunei Dollar', 'simple-pay' ),
		'BOB' => esc_html__( 'Bolivian Boliviano', 'simple-pay' ),
		'BRL' => esc_html__( 'Brazilian Real', 'simple-pay' ),
		'BSD' => esc_html__( 'Bahamian Dollar', 'simple-pay' ),
		'BWP' => esc_html__( 'Botswana Pula', 'simple-pay' ),
		'BYR' => esc_html__( 'Belarusian Ruble', 'simple-pay' ),
		'BZD' => esc_html__( 'Belize Dollar', 'simple-pay' ),
		'CAD' => esc_html__( 'Canadian Dollar', 'simple-pay' ),
		'CDF' => esc_html__( 'Congolese Franc', 'simple-pay' ),
		'CHF' => esc_html__( 'Swiss Franc', 'simple-pay' ),
		'CLP' => esc_html__( 'Chilean Peso', 'simple-pay' ),
		'CNY' => esc_html__( 'Chinese Renminbi Yuan', 'simple-pay' ),
		'COP' => esc_html__( 'Colombian Peso', 'simple-pay' ),
		'CRC' => esc_html__( 'Costa Rican Colón', 'simple-pay' ),
		'CVE' => esc_html__( 'Cape Verdean Escudo', 'simple-pay' ),
		'CZK' => esc_html__( 'Czech Koruna', 'simple-pay' ),
		'DJF' => esc_html__( 'Djiboutian Franc', 'simple-pay' ),
		'DKK' => esc_html__( 'Danish Krone', 'simple-pay' ),
		'DOP' => esc_html__( 'Dominican Peso', 'simple-pay' ),
		'DZD' => esc_html__( 'Algerian Dinar', 'simple-pay' ),
		'EGP' => esc_html__( 'Egyptian Pound', 'simple-pay' ),
		'ETB' => esc_html__( 'Ethiopian Birr', 'simple-pay' ),
		'EUR' => esc_html__( 'Euro', 'simple-pay' ),
		'FJD' => esc_html__( 'Fijian Dollar', 'simple-pay' ),
		'FKP' => esc_html__( 'Falkland Islands Pound', 'simple-pay' ),
		'GBP' => esc_html__( 'British Pound', 'simple-pay' ),
		'GEL' => esc_html__( 'Georgian Lari', 'simple-pay' ),
		'GIP' => esc_html__( 'Gibraltar Pound', 'simple-pay' ),
		'GMD' => esc_html__( 'Gambian Dalasi', 'simple-pay' ),
		'GNF' => esc_html__( 'Guinean Franc', 'simple-pay' ),
		'GTQ' => esc_html__( 'Guatemalan Quetzal', 'simple-pay' ),
		'GYD' => esc_html__( 'Guyanese Dollar', 'simple-pay' ),
		'HKD' => esc_html__( 'Hong Kong Dollar', 'simple-pay' ),
		'HNL' => esc_html__( 'Honduran Lempira', 'simple-pay' ),
		'HRK' => esc_html__( 'Croatian Kuna', 'simple-pay' ),
		'HTG' => esc_html__( 'Haitian Gourde', 'simple-pay' ),
		'HUF' => esc_html__( 'Hungarian Forint', 'simple-pay' ),
		'IDR' => esc_html__( 'Indonesian Rupiah', 'simple-pay' ),
		'ILS' => esc_html__( 'Israeli New Sheqel', 'simple-pay' ),
		'INR' => esc_html__( 'Indian Rupee', 'simple-pay' ),
		'ISK' => esc_html__( 'Icelandic Króna', 'simple-pay' ),
		'JMD' => esc_html__( 'Jamaican Dollar', 'simple-pay' ),
		'JPY' => esc_html__( 'Japanese Yen', 'simple-pay' ),
		'KES' => esc_html__( 'Kenyan Shilling', 'simple-pay' ),
		'KGS' => esc_html__( 'Kyrgyzstani Som', 'simple-pay' ),
		'KHR' => esc_html__( 'Cambodian Riel', 'simple-pay' ),
		'KMF' => esc_html__( 'Comorian Franc', 'simple-pay' ),
		'KRW' => esc_html__( 'South Korean Won', 'simple-pay' ),
		'KYD' => esc_html__( 'Cayman Islands Dollar', 'simple-pay' ),
		'KZT' => esc_html__( 'Kazakhstani Tenge', 'simple-pay' ),
		'LAK' => esc_html__( 'Lao Kip', 'simple-pay' ),
		'LBP' => esc_html__( 'Lebanese Pound', 'simple-pay' ),
		'LKR' => esc_html__( 'Sri Lankan Rupee', 'simple-pay' ),
		'LRD' => esc_html__( 'Liberian Dollar', 'simple-pay' ),
		'LSL' => esc_html__( 'Lesotho Loti', 'simple-pay' ),
		'MAD' => esc_html__( 'Moroccan Dirham', 'simple-pay' ),
		'MDL' => esc_html__( 'Moldovan Leu', 'simple-pay' ),
		'MGA' => esc_html__( 'Malagasy Ariary', 'simple-pay' ),
		'MKD' => esc_html__( 'Macedonian Denar', 'simple-pay' ),
		'MMK' => esc_html__( 'Myanmar Kyat', 'simple-pay' ),
		'MNT' => esc_html__( 'Mongolian Tögrög', 'simple-pay' ),
		'MOP' => esc_html__( 'Macanese Pataca', 'simple-pay' ),
		'MRO' => esc_html__( 'Mauritanian Ouguiya', 'simple-pay' ),
		'MUR' => esc_html__( 'Mauritian Rupee', 'simple-pay' ),
		'MVR' => esc_html__( 'Maldivian Rufiyaa', 'simple-pay' ),
		'MWK' => esc_html__( 'Malawian Kwacha', 'simple-pay' ),
		'MXN' => esc_html__( 'Mexican Peso', 'simple-pay' ),
		'MYR' => esc_html__( 'Malaysian Ringgit', 'simple-pay' ),
		'MZN' => esc_html__( 'Mozambican Metical', 'simple-pay' ),
		'NAD' => esc_html__( 'Namibian Dollar', 'simple-pay' ),
		'NGN' => esc_html__( 'Nigerian Naira', 'simple-pay' ),
		'NIO' => esc_html__( 'Nicaraguan Córdoba', 'simple-pay' ),
		'NOK' => esc_html__( 'Norwegian Krone', 'simple-pay' ),
		'NPR' => esc_html__( 'Nepalese Rupee', 'simple-pay' ),
		'NZD' => esc_html__( 'New Zealand Dollar', 'simple-pay' ),
		'PAB' => esc_html__( 'Panamanian Balboa', 'simple-pay' ),
		'PEN' => esc_html__( 'Peruvian Nuevo Sol', 'simple-pay' ),
		'PGK' => esc_html__( 'Papua New Guinean Kina', 'simple-pay' ),
		'PHP' => esc_html__( 'Philippine Peso', 'simple-pay' ),
		'PKR' => esc_html__( 'Pakistani Rupee', 'simple-pay' ),
		'PLN' => esc_html__( 'Polish Złoty', 'simple-pay' ),
		'PYG' => esc_html__( 'Paraguayan Guaraní', 'simple-pay' ),
		'QAR' => esc_html__( 'Qatari Riyal', 'simple-pay' ),
		'RON' => esc_html__( 'Romanian Leu', 'simple-pay' ),
		'RSD' => esc_html__( 'Serbian Dinar', 'simple-pay' ),
		'RUB' => esc_html__( 'Russian Ruble', 'simple-pay' ),
		'RWF' => esc_html__( 'Rwandan Franc', 'simple-pay' ),
		'SAR' => esc_html__( 'Saudi Riyal', 'simple-pay' ),
		'SBD' => esc_html__( 'Solomon Islands Dollar', 'simple-pay' ),
		'SCR' => esc_html__( 'Seychellois Rupee', 'simple-pay' ),
		'SEK' => esc_html__( 'Swedish Krona', 'simple-pay' ),
		'SGD' => esc_html__( 'Singapore Dollar', 'simple-pay' ),
		'SHP' => esc_html__( 'Saint Helenian Pound', 'simple-pay' ),
		'SLL' => esc_html__( 'Sierra Leonean Leone', 'simple-pay' ),
		'SOS' => esc_html__( 'Somali Shilling', 'simple-pay' ),
		'SRD' => esc_html__( 'Surinamese Dollar', 'simple-pay' ),
		'STD' => esc_html__( 'São Tomé and Príncipe Dobra', 'simple-pay' ),
		'SZL' => esc_html__( 'Swazi Lilangeni', 'simple-pay' ),
		'THB' => esc_html__( 'Thai Baht', 'simple-pay' ),
		'TJS' => esc_html__( 'Tajikistani Somoni', 'simple-pay' ),
		'TOP' => esc_html__( 'Tongan Paʻanga', 'simple-pay' ),
		'TRY' => esc_html__( 'Turkish Lira', 'simple-pay' ),
		'TTD' => esc_html__( 'Trinidad and Tobago Dollar', 'simple-pay' ),
		'TWD' => esc_html__( 'New Taiwan Dollar', 'simple-pay' ),
		'TZS' => esc_html__( 'Tanzanian Shilling', 'simple-pay' ),
		'UAH' => esc_html__( 'Ukrainian Hryvnia', 'simple-pay' ),
		'UGX' => esc_html__( 'Ugandan Shilling', 'simple-pay' ),
		'USD' => esc_html__( 'United States Dollar', 'simple-pay' ),
		'UYU' => esc_html__( 'Uruguayan Peso', 'simple-pay' ),
		'UZS' => esc_html__( 'Uzbekistani Som', 'simple-pay' ),
		'VND' => esc_html__( 'Vietnamese Đồng', 'simple-pay' ),
		'VUV' => esc_html__( 'Vanuatu Vatu', 'simple-pay' ),
		'WST' => esc_html__( 'Samoan Tala', 'simple-pay' ),
		'XAF' => esc_html__( 'Central African Cfa Franc', 'simple-pay' ),
		'XCD' => esc_html__( 'East Caribbean Dollar', 'simple-pay' ),
		'XOF' => esc_html__( 'West African Cfa Franc', 'simple-pay' ),
		'XPF' => esc_html__( 'Cfp Franc', 'simple-pay' ),
		'YER' => esc_html__( 'Yemeni Rial', 'simple-pay' ),
		'ZAR' => esc_html__( 'South African Rand', 'simple-pay' ),
		'ZMW' => esc_html__( 'Zambian Kwacha', 'simple-pay' ),
	);
}

/**
 * Returns a list of Customer Tax ID types.
 *
 * @since 4.2.0
 *
 * @return array $tax_id_types List of tax ID types.
 */
function get_stripe_tax_id_types() {
	$tax_id_types = array(
		'ae_trn'  => esc_html__( 'United Arab Emirates TRN', 'simple-pay' ),
		'au_abn'  => esc_html__( 'Australian Business Number', 'simple-pay' ),
		'br_cnpj' => esc_html__( 'Brazilian CNPJ number', 'simple-pay' ),
		'br_cpf'  => esc_html__( 'Brazilian CPF number', 'simple-pay' ),
		'ca_bn'   => esc_html__( 'Canadian BN', 'simple-pay' ),
		'ca_qst'  => esc_html__( 'Canadian QST number', 'simple-pay' ),
		'ch_vat'  => esc_html__( 'Switzerland VAT number', 'simple-pay' ),
		'cl_tin'  => esc_html__( 'Chilean TIN', 'simple-pay' ),
		'es_cif'  => esc_html__( 'Spanish CIF number', 'simple-pay' ),
		'eu_vat'  => esc_html__( 'European VAT number', 'simple-pay' ),
		'gb_vat'  => esc_html__( 'United Kingdom VAT number', 'simple-pay' ),
		'hk_br'   => esc_html__( 'Hong Kong BR number', 'simple-pay' ),
		'id_npwp' => esc_html__( 'Indonesian NPWP number', 'simple-pay' ),
		'id_gst'  => esc_html__( 'Indian GST number', 'simple-pay' ),
		'jp_cn'   => esc_html__( 'Japanese Corporate Number', 'simple-pay' ),
		'jp_rn'   => esc_html__(
			'Japanese Registered Foreign Businesses\' Registration Number',
			'simple-pay'
		),
		'kr_brn'   => esc_html__( 'Korean BRN', 'simple-pay' ),
		'li_uid'   => esc_html__( 'Liechtensteinian UID number', 'simple-pay' ),
		'mx_rfc'   => esc_html__( 'Mexican RFC number', 'simple-pay' ),
		'my_frp'   => esc_html__( 'Malaysian FRP number', 'simple-pay' ),
		'my_itn'   => esc_html__( 'Malaysian ITN', 'simple-pay' ),
		'my_sst'   => esc_html__( 'Malaysian SST number', 'simple-pay' ),
		'no_vat'   => esc_html__( 'Norwegian VAT number', 'simple-pay' ),
		'nz_gst'   => esc_html__( 'New Zealand GST number', 'simple-pay' ),
		'ru_inn'   => esc_html__( 'Russian INN', 'simple-pay' ),
		'ru_kpp'   => esc_html__( 'Russian KPP', 'simple-pay' ),
		'sa_vat'   => esc_html__( 'Saudi Arabia VAT', 'simple-pay' ),
		'sg_gst'   => esc_html__( 'Singaporean GST', 'simple-pay' ),
		'sg_uen'   => esc_html__( 'Singaporean UEN', 'simple-pay' ),
		'th_vat'   => esc_html__( 'Thai VAT', 'simple-pay' ),
		'tw_vat'   => esc_html__( 'Taiwanese VAT', 'simple-pay' ),
		'us_ein'   => esc_html__( 'United States EIN', 'simple-pay' ),
		'za_vat'   => esc_html__( 'South African VAT number', 'simple-pay' ),
	);

	/**
	 * Filters the supported Customer Tax ID types.
	 *
	 * @since 4.2.0
	 *
	 * @param array $tax_id_types Supported Customer Tax ID types.
	 */
	$tax_id_types = apply_filters( 'simpay_stripe_tax_id_types', $tax_id_types );

	return $tax_id_types;
}

/**
 * Returns a list of error codes and corresponding localized error messages.
 *
 * @since 3.9.0
 *
 * @return array $error_list List of error codes and corresponding error messages.
 */
function get_localized_error_messages() {
	$error_list = array(
		'invalid_number'           => __( 'The card number is not a valid credit card number.', 'simple-pay' ),
		'invalid_expiry_month'     => __( 'The card\'s expiration month is invalid.', 'simple-pay' ),
		'invalid_expiry_year'      => __( 'The card\'s expiration year is invalid.', 'simple-pay' ),
		'invalid_cvc'              => __( 'The card\'s security code is invalid.', 'simple-pay' ),
		'incorrect_number'         => __( 'The card number is incorrect.', 'simple-pay' ),
		'incomplete_number'        => __( 'The card number is incomplete.', 'simple-pay' ),
		'incomplete_cvc'           => __( 'The card\'s security code is incomplete.', 'simple-pay' ),
		'incomplete_expiry'        => __( 'The card\'s expiration date is incomplete.', 'simple-pay' ),
		'expired_card'             => __( 'The card has expired.', 'simple-pay' ),
		'incorrect_cvc'            => __( 'The card\'s security code is incorrect.', 'simple-pay' ),
		'incorrect_zip'            => __( 'The card\'s zip code failed validation.', 'simple-pay' ),
		'invalid_expiry_year_past' => __( 'The card\'s expiration year is in the past', 'simple-pay' ),
		'card_declined'            => __( 'The card was declined.', 'simple-pay' ),
		'processing_error'         => __( 'An error occurred while processing the card.', 'simple-pay' ),
		'invalid_request_error'    => __( 'Unable to process this payment, please try again or use alternative method.', 'simple-pay' ),
		'email_invalid'            => __( 'Invalid email address, please correct and try again.', 'simple-pay' ),
	);

	/**
	 * Filters the list of available error codes and corresponding error messages.
	 *
	 * @since 3.9.0
	 *
	 * @param array $error_list List of error codes and corresponding error messages.
	 */
	$error_list = apply_filters( 'simpay_get_localized_error_list', $error_list );

	return $error_list;
}

/**
 * Returns a localized error message for a corresponding Stripe
 * error code.
 *
 * @link https://stripe.com/docs/error-codes
 *
 * @since 3.9.0
 *
 * @param string $error_code Error code.
 * @param string $error_message Original error message to return if a localized version does not exist.
 * @return string $error_message Potentially localized error message.
 */
function get_localized_error_message( $error_code, $error_message ) {
	$error_list = get_localized_error_messages();

	if ( isset( $error_list[ $error_code ] ) ) {
		return $error_list[ $error_code ];
	}

	return $error_message;
}
