<?php
/**
 * SEPA Direct Debit: Functions
 *
 * @package SimplePay\Pro\Payment_Methods\SEPA_Direct_Debit
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.0
 */

namespace SimplePay\Pro\Payment_Methods\SEPA_Direct_Debit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets SEPA Direct Debit Subscription arguments.
 *
 * @since 4.6.0
 *
 * @param array<mixed> $args Subscription arguments.
 * @return array<mixed>
 */
function set_subscription_args( $args, $form, $form_data, $form_values ) {
	// Do not apply on Stripe Checkout payment forms. This prevents these
	// parameters from being nested as session.subscription_data.payment_method_options.
	if ( 'stripe_checkout' === $form->get_display_type() ) {
		return $args;
	}

	$payment_method_type = isset( $form_values['payment_method_type'] )
		? sanitize_text_field( $form_values['payment_method_type'] )
		: '';

	if ( 'sepa_debit' !== $payment_method_type ) {
		return $args;
	}

	// SEPA Direct Debit with a SetupIntent cannot use default_incomplete.
	if ( isset( $args['payment_behavior'] ) ) {
		unset( $args['payment_behavior'] );
	}

	// Set the Subscription's default payment method.
	$args['default_payment_method'] = sanitize_text_field(
		$form_values['payment_method_id']
	);

	return $args;
}
add_filter(
	'simpay_get_subscription_args_from_payment_form_request',
	__NAMESPACE__ . '\\set_subscription_args',
	20,
	4
);
