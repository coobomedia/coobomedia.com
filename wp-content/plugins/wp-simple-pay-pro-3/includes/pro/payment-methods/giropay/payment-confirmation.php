<?php
/**
 * giropay: Payment confirmation
 *
 * @package SimplePay\Pro\Payments\Payment_Methods\giropay
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.2.0
 */

namespace SimplePay\Pro\Payments\Payment_Methods\Giropay;

use SimplePay\Core\API;
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
 * @since 4.2.0
 */
function validate_payment_confirmation_data() {
	// Ensure we can retrieve a PaymentIntent.
	if ( ! isset( $_GET['payment_intent'] ) ) {
		return;
	}

	// Ensure we have a customer so `Payment_Confirmation\get_confirmation_data()` doesn't fail.
	if ( ! isset( $_GET['customer_id'] ) ) {
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

	// Do nothing if the Intent is not from Giropay.
	if ( 'giropay' !== $payment_intent->last_payment_error->payment_method->type ) {
		return;
	}

	// Redirect to failure page.
	wp_safe_redirect( $failure_page );
}
add_action( 'template_redirect', __NAMESPACE__ . '\\validate_payment_confirmation_data' );
