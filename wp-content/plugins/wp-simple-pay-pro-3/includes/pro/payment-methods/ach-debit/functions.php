<?php
/**
 * ACH Debit: Functions
 *
 * @package SimplePay\Pro\Payment_Methods\ACH_Debit
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.9.0
 */

namespace SimplePay\Pro\Payment_Methods\ACH_Debit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sets ACH Debit PaymentIntent arguments.
 *
 * @since 4.4.5
 *
 * @param array<mixed> $args PaymentIntent arguments.
 * @return array<mixed>
 */
function set_paymentintent_args( $args, $form, $form_data ) {
	// Do not apply on Stripe Checkout payment forms. This prevents these
	// parameters from being nested as session.payment_intent_data.payment_method_options.
	if ( 'stripe_checkout' === $form->get_display_type() ) {
		return $args;
	}

	// Pull the payment method from the fom data.
	// This filter does not have payment_method_types set already, and it is
	// instead added after the filter in /wpsp/v2/paymentintent/create.
	// @todo refactor so the filter has all data available.
	$payment_method = $form_data['paymentMethod']['id'];

	// Only modify arguments for ACH Debit.
	if ( 'ach-debit' !== $payment_method ) {
		return $args;
	}

	if ( ! isset( $args['payment_method_options'] ) ) {
		$args['payment_method_options'] = array();
	}

	// Only instant verifications.
	$args['payment_method_options']['us_bank_account']['verification_method'] = 'instant';

	// Always setup for future usage (to match previous Plaid behavior).
	$args['setup_future_usage'] = 'off_session';

	return $args;
}
add_filter(
	'simpay_get_paymentintent_args_from_payment_form_request',
	__NAMESPACE__ . '\\set_paymentintent_args',
	10,
	3
);

/**
 * Sets ACH Debit SetupIntent arguments.
 *
 * @since 4.4.5
 *
 * @param array<mixed> $args PaymentIntent arguments.
 * @return array<mixed>
 */
function set_setupintent_args( $args, $form, $form_data ) {
	// Do not apply on Stripe Checkout payment forms. This prevents these
	// parameters from being nested as session.payment_intent_data.payment_method_options.
	if ( 'stripe_checkout' === $form->get_display_type() ) {
		return $args;
	}

	// Pull the payment method from the fom data.
	// This filter does not have payment_method_types set already, and it is
	// instead added after the filter in /wpsp/v2/paymentintent/create.
	// @todo refactor so the filter has all data available.
	$payment_method = $form_data['paymentMethod']['id'];

	// Only modify arguments for ACH Debit.
	if ( 'ach-debit' !== $payment_method ) {
		return $args;
	}

	if ( ! isset( $args['payment_method_options'] ) ) {
		$args['payment_method_options'] = array();
	}

	// Only instant verifications.
	$args['payment_method_options']['us_bank_account']['verification_method'] = 'instant';

	return $args;
}
add_filter(
	'simpay_get_setupintent_args_from_payment_form_request',
	__NAMESPACE__ . '\\set_setupintent_args',
	10,
	3
);

/**
 * Sets ACH Debit Checkout Session arguments.
 *
 * @since 4.4.5
 *
 * @param array<mixed> $args PaymentIntent arguments.
 * @return array<mixed>
 */
function set_checkout_session_args( $args ) {
	if ( false === array_search( 'us_bank_account', $args['payment_method_types'], true ) ) {
		return $args;
	}

	if ( ! isset( $args['payment_method_options'] ) ) {
		$args['payment_method_options'] = array();
	}

	// Only instant verifications.
	$args['payment_method_options']['us_bank_account']['verification_method'] = 'instant';

	return $args;
}
add_filter(
	'simpay_get_session_args_from_payment_form_request',
	__NAMESPACE__ . '\\set_checkout_session_args',
	99
);

/**
 * Sets ACH Debit Subscription arguments.
 *
 * @since 4.4.5
 *
 * @param array<mixed> $args Subscription arguments.
 * @return array<mixed>
 */
function set_subscription_args( $args, $form, $form_data ) {
	// Do not apply on Stripe Checkout payment forms. This prevents these
	// parameters from being nested as session.subscription_data.payment_method_options.
	if ( 'stripe_checkout' === $form->get_display_type() ) {
		return $args;
	}

	// Pull the payment method from the fom data.
	// This filter does not have payment_method_types set already, and it is
	// instead added after the filter in /wpsp/v2/paymentintent/create.
	// @todo refactor so the filter has all data available.
	$payment_method = $form_data['paymentMethod']['id'];

	// Only modify arguments for ACH Debit.
	if ( 'ach-debit' !== $payment_method ) {
		return $args;
	}

	if ( ! isset( $args['payment_settings'] ) ) {
		$args['payment_settings'] = array();
	}

	// Only instant verifications.
	$args['payment_settings']['payment_method_types'] = array( 'us_bank_account' );
	$args['payment_settings']['payment_method_options']['us_bank_account']['verification_method'] = 'instant';

	// Set linked PaymentIntent behavior.
	$args['payment_behavior'] = 'default_incomplete';

	return $args;
}
add_filter(
	'simpay_get_subscription_args_from_payment_form_request',
	__NAMESPACE__ . '\\set_subscription_args',
	10,
	3
);
