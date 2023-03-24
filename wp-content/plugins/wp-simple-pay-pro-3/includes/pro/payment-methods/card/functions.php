<?php
/**
 * Card: Functions
 *
 * @package SimplePay\Pro\Payment_Methods\Card
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

namespace SimplePay\Pro\Payment_Methods\Card;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds `payment_intent_data.setup_future_usage` if Card is the only Payment Method
 * for Stripe Checkout.
 *
 * @since 4.1.0
 *
 * @param array $session_args Stripe Checkout Session arguments.
 * @return array
 */
function checkout_session_setup_future_usage( $session_args ) {
	if ( 'payment' !== $session_args['mode'] ) {
		return $session_args;
	}

	$payment_method_types = isset( $session_args['payment_method_types'] )
		? $session_args['payment_method_types']
		: array();

	if ( false === array_search( 'card', $payment_method_types, true ) ) {
		return $session_args;
	}

	// Set future usage if card is the only Payment Method.
	if ( 1 === count( $payment_method_types ) ) {
		$session_args['payment_intent_data']['setup_future_usage'] = 'off_session';
	}

	return $session_args;
}
add_filter(
	'simpay_get_session_args_from_payment_form_request',
	__NAMESPACE__ . '\\checkout_session_setup_future_usage',
	20
);
