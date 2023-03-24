<?php
/**
 * Functions
 *
 * @package SimplePay\Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the max length for metadata fields
 */
function simpay_metadata_title_length() {
	return 40;
}

/**
 * Get the max length for the metadata description
 *
 * @return int
 */
function simpay_metadata_description_length() {
	return 500;
}

/**
 * Handle metadata truncation using calls to other DRY functions
 *
 * @param string $type Metadata type.
 * @param string $value Metadata value.
 * @return bool|string
 */
function simpay_truncate_metadata( $type, $value ) {

	switch ( $type ) {
		case 'title':
			return substr( $value, 0, simpay_metadata_title_length() );
		case 'description':
			return substr( $value, 0, simpay_metadata_description_length() );
		default:
			return $value;
	}
}

/**
 * Calculate the tax of an amount when passed in the percentage value. Defaults to the form amount and tax_percent.
 *
 * @todo This should have required arguments and not rely on the $simpay_form global.
 *
 * @param string $amount Total amount.
 * @param string $tax_percent Tax percentage.
 * @return float
 */
function simpay_calculate_tax_amount( $amount = '', $tax_percent = '' ) {

	global $simpay_form;

	// If the global does not exist and one of the parameters wasn't passed in then we leave now.
	if ( ! isset( $simpay_form ) && ( empty( $amount ) || empty( $tax_percent ) ) ) {
		return 0;
	}

	if ( empty( $amount ) ) {
		$amount = simpay_unformat_currency( $simpay_form->amount );
	}

	if ( empty( $tax_percent ) ) {
		$tax_percent = floatval( $simpay_form->tax_percent );
	}

	$retval = round( $amount * ( $tax_percent / 100 ), simpay_get_decimal_places() );

	return $retval;
}

/**
 * Get the separator to use for fields that list multiple values
 * Affected Custom Fields: Dropdown values/amounts/quantities, radio values/amounts/quantities
 */
function simpay_list_separator() {
	return apply_filters( 'simpay_list_separator', ',' );
}

