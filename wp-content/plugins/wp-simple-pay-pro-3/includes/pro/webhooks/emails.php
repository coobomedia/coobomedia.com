<?php
/**
 * Webhooks: Emails
 *
 * @package SimplePay\Pro\Webhooks\Emails
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Webhooks\Emails;

use SimplePay\Core\Utils;
use SimplePay\Core\Payments\Payment_Confirmation;

use SimplePay\Pro\Emails;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mails the "Payment Confirmation" email.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Vendor\Stripe\Event                                               $event Stripe Event.
 * @param \SimplePay\Vendor\Stripe\Subscription|\SimplePay\Vendor\Stripe\PaymentIntent $object Stripe object.
 * @return bool True if the email was sent.
 */
function payment_confirmation( $event, $object ) {
	// Object is not linked to a Payment Form, do nothing.
	if ( ! isset( $object->metadata->simpay_form_id ) ) {
		return;
	}

	/** @var \SimplePay\Pro\Emails\Email_Payment_Confirmation $email */
	$email = Emails\get( 'payment-confirmation' );

	if ( false === $email ) {
		return;
	}

	$payment_confirmation_data = Payment_Confirmation\get_confirmation_data(
		$object->customer->id,
		false,
		$object->metadata->simpay_form_id
	);

	// Set "To" address to the Customer's email address.
	$to = $object->customer->email;

	// Set "Subject" to the stored subject.
	$subject = Emails\format_subject_for_mode(
		$email->get_setting( 'subject' ),
		$payment_confirmation_data['form']->is_livemode()
	);

	if ( empty( $payment_confirmation_data ) ) {
		$body = Payment_Confirmation\get_error();
	} else {
		// Retrieved the stored body content.
		$type = 'one_time';

		if ( is_a( $object, '\SimplePay\Vendor\Stripe\Subscription' ) ) {
			$type = 'subscription';

			if ( 'trialing' === $object->status ) {
				$type = 'trial';
			}
		}

		$body = $email->get_body_setting_or_default( $type );

		// Parse smart tags.
		$body = Payment_Confirmation\Template_Tags\parse_content(
			$body,
			$payment_confirmation_data
		);
	}

	// set "message" to the the parsed body or error.
	$body = Emails\format_body( $body );

	Emails\Mailer\send( $email, $to, $subject, $body );
}
add_action(
	'simpay_webhook_subscription_created',
	__NAMESPACE__ . '\\payment_confirmation',
	10,
	2
);
add_action(
	'simpay_webhook_charge_succeeded',
	__NAMESPACE__ . '\\payment_confirmation',
	10,
	2
);
add_action(
	'simpay_webhook_payment_intent_succeeded',
	__NAMESPACE__ . '\\payment_confirmation',
	10,
	2
);

/**
 * Mails the "Payment Notification" email.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Vendor\Stripe\Event                                               $event Stripe Event.
 * @param \SimplePay\Vendor\Stripe\Subscription|\SimplePay\Vendor\Stripe\PaymentIntent $object Stripe object.
 * @return bool True if the email was sent.
 */
function payment_notification( $event, $object ) {
	// Object is not linked to a Payment Form, do nothing.
	if ( ! isset( $object->metadata->simpay_form_id ) ) {
		return;
	}

	$email = Emails\get( 'payment-notification' );

	if ( false === $email ) {
		return;
	}

	$payment_confirmation_data = Payment_Confirmation\get_confirmation_data(
		$object->customer->id,
		false,
		$object->metadata->simpay_form_id
	);

	// Set "To" address to the stored setting.
	$to = $email->get_setting( 'to' );

	// Set "Subject" to the stored subject.
	$subject = Emails\format_subject_for_mode(
		$email->get_setting( 'subject' ),
		$payment_confirmation_data['form']->is_livemode()
	);

	if ( empty( $payment_confirmation_data ) ) {
		$body = Payment_Confirmation\get_error();
	} else {
		// Parse smart tags.
		$body = Payment_Confirmation\Template_Tags\parse_content(
			$email->get_setting( 'body' ),
			$payment_confirmation_data
		);
	}

	// Set "Message" to the the parsed body or error.
	$body = Emails\format_body( $body );

	Emails\Mailer\send( $email, $to, $subject, $body );
}
add_action(
	'simpay_webhook_subscription_created',
	__NAMESPACE__ . '\\payment_notification',
	10,
	2
);
add_action(
	'simpay_webhook_charge_succeeded',
	__NAMESPACE__ . '\\payment_notification',
	10,
	2
);
add_action(
	'simpay_webhook_payment_intent_succeeded',
	__NAMESPACE__ . '\\payment_notification',
	10,
	2
);

/**
 * Mails the "Upcoming Invoice" email.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Vendor\Stripe\Event        $event        Stripe Event object.
 * @param \SimplePay\Vendor\Stripe\Invoice      $invoice      Stripe Invoice object.
 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription object.
 * @return bool True if the email was sent.
 */
function upcoming_invoice( $event, $invoice, $subscription ) {
	$email = Emails\get( 'upcoming-invoice' );

	if ( false === $email ) {
		return;
	}

	$send_upcoming_invoice_email = true;

	/**
	 * Determines if the "Upcoming Invoice" email should be sent.
	 *
	 * @since 3.9.0
	 * @since 4.0.0 Deprecated. Use email settings.
	 *
	 * @param bool                 $send_upcoming_invoice_email If the email should be sent.
	 * @param \SimplePay\Vendor\Stripe\Event        $event                       Stripe Event object.
	 * @param \SimplePay\Vendor\Stripe\Invoice      $invoice                     Stripe Invoice object.
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription                Stripe Subscription object.
	 */
	$send_upcoming_invoice_email = apply_filters(
		'simpay_send_upcoming_invoice_email',
		$send_upcoming_invoice_email,
		$event,
		$invoice,
		$subscription
	);

	// Email is disabled via legacy filter, do nothing.
	if ( false === $send_upcoming_invoice_email ) {
		return;
	}

	// Do nothing if Subscription was created before 3.7.0, or is missing a key.
	if ( ! isset( $subscription->metadata->simpay_subscription_key ) ) {
		return;
	}

	$payment_confirmation_data = Payment_Confirmation\get_confirmation_data(
		$subscription->customer->id,
		false,
		$subscription->metadata->simpay_form_id
	);

	// Set "To" address to the Customer's email address.
	$to = $invoice->customer_email;

	// Set "Subject" to the stored subject.
	$subject = Emails\format_subject_for_mode(
		$email->get_setting( 'subject' ),
		$payment_confirmation_data['form']->is_livemode()
	);

	if ( empty( $payment_confirmation_data ) ) {
		$body = Payment_Confirmation\get_error();
	} else {
		// Parse smart tags.
		$body = Payment_Confirmation\Template_Tags\parse_content(
			$email->get_setting( 'body' ),
			$payment_confirmation_data
		);
	}

	// Set "Message" to the the parsed body or error.
	$body = Emails\format_body( $body );

	Emails\Mailer\send( $email, $to, $subject, $body );
}
add_action(
	'simpay_webhook_invoice_upcoming',
	__NAMESPACE__ . '\\upcoming_invoice',
	10,
	3
);

/**
 * Mails the "Invoice Confirmation" email.
 *
 * @since 4.4.6
 *
 * @param \SimplePay\Vendor\Stripe\Event        $event        Stripe Event object.
 * @param \SimplePay\Vendor\Stripe\Invoice      $invoice      Stripe Invoice object.
 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription object.
 * @return bool True if the email was sent.
 */
function invoice_confirmation( $event, $invoice, $subscription ) {
	$email = Emails\get( 'invoice-confirmation' );

	if ( false === $email ) {
		return;
	}

	// First invoice of the Subscription, use the payment confirmation.
	if ( 'subscription_create' === $invoice->billing_reason ) {
		return;
	}

	// Do nothing if Subscription was created before 3.7.0, or is missing a key.
	if ( ! isset( $subscription->metadata->simpay_subscription_key ) ) {
		return;
	}

	$payment_confirmation_data = Payment_Confirmation\get_confirmation_data(
		$subscription->customer->id,
		false,
		$subscription->metadata->simpay_form_id
	);

	// Set "To" address to the Customer's email address.
	$to = $invoice->customer_email;

	// Set "Subject" to the stored subject.
	$subject = Emails\format_subject_for_mode(
		$email->get_setting( 'subject' ),
		$payment_confirmation_data['form']->is_livemode()
	);

	if ( empty( $payment_confirmation_data ) ) {
		$body = Payment_Confirmation\get_error();
	} else {
		// Parse smart tags.
		$body = Payment_Confirmation\Template_Tags\parse_content(
			$email->get_setting( 'body' ),
			$payment_confirmation_data
		);
	}

	// Set "Message" to the the parsed body or error.
	$body = Emails\format_body( $body );

	Emails\Mailer\send( $email, $to, $subject, $body );
}
add_action(
	'simpay_webhook_invoice_payment_succeeded',
	__NAMESPACE__ . '\\invoice_confirmation',
	10,
	3
);
