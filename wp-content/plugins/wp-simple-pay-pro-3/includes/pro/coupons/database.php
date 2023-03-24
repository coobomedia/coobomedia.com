<?php
/**
 * Coupons: Database
 *
 * @package SimplePay
 * @subpackage Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.3.0
 */

namespace SimplePay\Pro\Coupons;

/**
 * Creates or upgrades the coupon database table.
 *
 * @since 4.3.0
 */
function setup_database() {
	new Database\Table;
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\setup_database' );

/**
 * Converts a timestamp to a Y-m-d H:i:s formatted datetime.
 *
 * @since 4.3.0
 *
 * @param string|int $value Possible timestamp.
 * @return string Y-m-d H:i:s formatted UTC datetime.
 */
function sanitize_timestamp_to_date( $value ) {
	return ( 1 === preg_match( '~^[1-9][0-9]*$~', $value ) )
		? gmdate( 'Y-m-d H:i:s', $value )
		: $value;
}

/**
 * Sanitizes a coupon name/ID for the Stripe API.
 *
 * @since 4.3.0
 *
 * @param string $coupon_name Unsanitized coupon name.
 * @return string
 */
function sanitize_coupon_name( $value ) {
	$coupon = preg_replace(
		'/[^a-zA-Z0-9_\-]/',
		'',
		sanitize_text_field( $value )
	);

	// Stripe limits length to 200.
	return substr( $coupon, 0, 200 );
}
