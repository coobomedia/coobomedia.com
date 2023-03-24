<?php
/**
 * Afterpay / Clearpay: Functions
 *
 * @package SimplePay\Pro\Payments\Payment_Methods\AfterpayClearpay
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.4
 */

namespace SimplePay\Pro\Payments\Payment_Methods\AfterpayClearpay;

use SimplePay\Core\Payments\Stripe_Checkout;

/**
 * Adds `shipping_address_collection` to Stripe Checkout Session when using Afterpay / Clearpay.
 *
 * @since 4.4.4
 *
 * @param array $args Arguments used to create a Checkout Session.
 * @return array
 */
function add_shipping_address_collection( $args ) {
	if ( ! in_array( 'afterpay_clearpay', $args['payment_method_types'], true ) ) {
		return $args;
	}

	$args['shipping_address_collection'] = array(
		'allowed_countries' => Stripe_Checkout\get_available_shipping_address_countries(),
	);

	return $args;
}
add_filter(
	'simpay_get_session_args_from_payment_form_request',
	__NAMESPACE__ . '\\add_shipping_address_collection',
	20
);
