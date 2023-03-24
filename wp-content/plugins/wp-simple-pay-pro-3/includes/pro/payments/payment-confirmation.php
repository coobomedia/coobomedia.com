<?php
/**
 * Payment confirmation
 *
 * @package SimplePay\Pro\Payments\Payment_Confirmation
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.0
 */

namespace SimplePay\Pro\Payments\Payment_Confirmation;

use SimplePay\Core\API;
use SimplePay\Pro\Payments\Charge;
use SimplePay\Pro\Customers\Subscription_Management;
use SimplePay\Pro\Emails;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds Customer Charges to available payment confirmation data.
 *
 * @since 3.9.0
 *
 * @param array $payment_confirmation_data Array of data to send to the Payment Confirmation smart tags.
 * @return array $payment_confirmation_data
 */
function add_charge_data( $payment_confirmation_data ) {
	if ( ! empty( $payment_confirmation_data['paymentintents'] ) ) {
		return $payment_confirmation_data;
	}

	$form     = $payment_confirmation_data['form'];
	$customer = $payment_confirmation_data['customer'];

	$charges = Charge\all(
		array(
			'customer' => $customer->id,
			'limit'    => 1,
		),
		$form->get_api_request_args()
	);

	// Trial maybe? No charges.
	if ( empty( $charges->data ) ) {
		return $payment_confirmation_data;
	}

	// Dirty clone first Charge to fill in top level properties.
	$charge = unserialize( serialize( current( $charges->data ) ) );

	$paymentintent          = $charge;
	$paymentintent->charges = $charges;

	$paymentintents = array( $paymentintent );

	$payment_confirmation_data['paymentintents'] = $paymentintents;

	return $payment_confirmation_data;
}
add_filter( 'simpay_payment_confirmation_data', __NAMESPACE__ . '\\add_charge_data' );

/**
 * Adds Customer Subscriptions to available payment confirmation data.
 *
 * @since 3.6.0
 *
 * @param array $payment_confirmation_data Array of data to send to the Payment Confirmation smart tags.
 * @return array $payment_confirmation_data
 */
function add_subscriptions_data( $payment_confirmation_data ) {
	$subscriptions = API\Subscriptions\all(
		array(
			'customer' => $payment_confirmation_data['customer']->id,
			'limit'    => 1,
			'status'   => 'all',
			'expand'   => array(
				'data.latest_invoice',
				'data.latest_invoice.payment_intent.charges',
			),
		),
		$payment_confirmation_data['form']->get_api_request_args()
	);

	$payment_confirmation_data['subscriptions'] = $subscriptions->data;

	return $payment_confirmation_data;
}
add_filter( 'simpay_payment_confirmation_data', __NAMESPACE__ . '\\add_subscriptions_data' );

/**
 * Returns the default Payment Confirmation message for "Subscription" payments.
 *
 * @since 4.0.0
 *
 * @return string
 */
function get_subscription_message_default() {
	$email     = Emails\get( 'payment-confirmation' );
	$has_email = false !== $email && $email->is_enabled();
	$message   = '';

	if ( true === $has_email ) {
		$message = 'Thank you. Your payment of {total-amount} has been received and your subscription has been activated. You will be charged {recurring-amount} from {next-invoice-date}. Please check your email for additional information.';
	} else {
		$message = 'Thank you. Your payment of {total-amount} has been received and your subscription has been activated. You will be charged {recurring-amount} from {next-invoice-date}.';
	}

	return $message;
}

/**
 * Returns the default Payment Confirmation message for "Subscription with Trial" payments.
 *
 * @since 4.0.0
 *
 * @return string
 */
function get_trial_message_default() {
	$email     = Emails\get( 'payment-confirmation' );
	$has_email = false !== $email && $email->is_enabled();
	$message   = '';

	if ( true === $has_email ) {
		$message = 'Thank you. Your free trial has been activated and you will be charged {recurring-amount} starting {next-invoice-date}. Please check your email for additional information.';
	} else {
		$message = 'Thank you. Your free trial has been activated and you will be charged {recurring-amount} starting {next-invoice-date}.';
	}

	return $message;
}

/**
 * Change the base confirmation message depending on the form type.
 *
 * @since 3.6.0
 *
 * @param string $content Payment confirmation content.
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function get_content( $content, $payment_confirmation_data ) {
	// Not a subscription.
	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $content;
	}

	$subscription = current( $payment_confirmation_data['subscriptions'] );
	$type         = $subscription->trial_end ? 'trial' : 'subscription';

	$content = simpay_get_setting(
		$type . '_details',
		call_user_func( __NAMESPACE__ . sprintf( '\get_%s_message_default', $type ) )
	);

	return $content;
}
add_filter( 'simpay_payment_confirmation_content', __NAMESPACE__ . '\\get_content', 10, 2 );

/**
 * Appends the "Update Payment Method" form to the confirmation content.
 *
 * @since 3.7.0
 * @since 4.0.0 Moved to SimplePay\Pro\Customers\Subscription_Management\on_site()
 *
 * @param string $content Payment confirmation shortcode content.
 * @param array  $payment_confirmation_data Array of data to send to the Payment Confirmation smart tags.
 */
function update_payment_method_form( $content, $payment_confirmation_data ) {
	_doing_it_wrong(
		__FUNCTION__,
		__(
			'SimplePay\Pro\Customers\Subscription_Management\on_site() is attached directly. Calling this function directly is not needed.',
			'simple-pay'
		),
		'4.0.0'
	);

	Subscription_Management\on_site( $content, $payment_confirmation_data );
}
