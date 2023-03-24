<?php
/**
 * Afterpay / Clearpay: Payment confirmation
 *
 * @package SimplePay\Pro\Payments\Payment_Methods\AfterpayClearpay
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.4
 */

namespace SimplePay\Pro\Payments\Payment_Methods\AfterpayClearpay;

use SimplePay\Core\Payments\Payment_Confirmation;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates the Payment Confirmation data.
 *
 * If the data includes an invalid or incomplete PaymentIntent
 * redirect to the form's failure page.
 *
 * @since 4.4.4
 */
function validate_payment_confirmation_data() {
	// Ensure we can retrieve a PaymentIntent.
	if ( ! isset(
		$_GET['payment_intent'],
		$_GET['payment_intent_client_secret'],
		$_GET['customer_id'],
		$_GET['form_id']
	) ) {
		return;
	}

	$payment_confirmation_data = Payment_Confirmation\get_confirmation_data();

	// Ensure we have a Payment Form to reference.
	if ( ! isset( $payment_confirmation_data['form'] ) ) {
		return;
	}

	$payment_intent = isset( $payment_confirmation_data['paymentintents'] )
		? current( $payment_confirmation_data['paymentintents'] )
		: false;

	$failure_page = $payment_confirmation_data['form']->payment_cancelled_page;

	// Redirect to failure if PaymentIntent cannot be found.
	if ( false === $payment_intent ) {
		wp_safe_redirect( $failure_page );
	}

	// Do nothing if the Intent has succeeded.
	if ( 'succeeded' === $payment_intent->status ) {
		return;
	}

	// Do nothing if the Intent is not from Afterpay / Clearpay.
	if ( 'afterpay_clearpay' !== $payment_intent->last_payment_error->payment_method->type ) {
		return;
	}

	// Redirect to failure page.
	wp_safe_redirect( $failure_page );
}
add_action(
	'template_redirect',
	__NAMESPACE__ . '\\validate_payment_confirmation_data'
);
