<?php
/**
 * Card: Payment confirmation
 *
 * @package SimplePay\Pro\Payment_Methods\Card
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.9.0
 */

namespace SimplePay\Pro\Payment_Methods\Card;

/**
 * Filters the content to be shown before the "Update Payment Method" form.
 *
 * @since 3.9.0
 *
 * @param string                                                                 $message          Update Payment Method message.
 * @param \SimplePay\Vendor\Stripe\PaymentMethod|\SimplePay\Vendor\Stripe\Source $payment_method   Payment Method or Source.
 * @param \SimplePay\Vendor\Stripe\Subscription                                  $subscription     Subscription.
 * @param \SimplePay\Vendor\Stripe\Invoice|false                                 $upcoming_invoice Upcoming invoice.
 * @return string
 */
function update_payment_method_message( $message, $payment_method, $subscription, $upcoming_invoice ) {
	// Not a Card.
	if ( ! isset( $payment_method->card ) ) {
		return $message;
	}

	// No upcoming Invoice.
	if ( false === $upcoming_invoice ) {
		return $message;
	}

	$amount_due = $upcoming_invoice->amount_due;
	$currency   = $upcoming_invoice->currency;

	$amount_due = html_entity_decode(
		simpay_format_currency( $amount_due, $currency )
	);

	return wp_kses(
		sprintf(
			/* translators: %1$s Upcoming invoice amount. %2$s Card name. %3$s Card last 4. %4$s Upcoming invoice date. */
			__(
				'The next invoice for %1$s will automatically charge %2$s &bull;&bull;&bull;&bull; %3$s on %4$s.',
				'simple-pay'
			),
			$amount_due,
			'<strong>' . ucwords( $payment_method->card->brand ) . '</strong>',
			'<strong>' . $payment_method->card->last4 . '</strong>',
			get_date_from_gmt(
				date( 'Y-m-d H:i:s', $subscription->current_period_end ),
				get_option( 'date_format' )
			)
		),
		array(
			'strong' => true,
		)
	);
}
add_filter( 'simpay_update_payment_method_message', __NAMESPACE__ . '\\update_payment_method_message', 10, 4 );
