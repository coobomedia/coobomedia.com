<?php
/**
 * Payment confirmation smart tags
 *
 * @package SimplePay\Pro\Payments\Payment_Confirmation\Template_Tags
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.0
 */

// phpcs:disable WordPress.DateTime.RestrictedFunctions.date_date

namespace SimplePay\Pro\Payments\Payment_Confirmation\Template_Tags;

use Exception;
use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Core\Payments\Stripe_Checkout\Session;
use SimplePay\Core\Payments\Payment_Confirmation\Template_Tags as Core_Template_Tags;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds additional confirmation smart tags.
 *
 * @param array $tags Smart tags.
 * @return array
 */
function add_template_tags( $tags ) {
	return array_merge(
		$tags,
		array(
			'tax-amount',
			'recurring-amount',
			'max-charges',
			'trial-end-date',
			'next-invoice-date',
			'update-payment-method-url',
			'payment',
			'subscription',
			'customer',
		)
	);
}
add_filter( 'simpay_payment_details_template_tags', __NAMESPACE__ . '\\add_template_tags' );

/**
 * Replaces {charge-id} with the Customer's Subscription ID.
 *
 * @since 3.6.0
 *
 * @param string $value Default value (empty string).
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
function charge_id( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $value;
	}

	$subscription = current( $payment_confirmation_data['subscriptions'] );

	return esc_html( $subscription->id );
}
add_filter( 'simpay_payment_confirmation_template_tag_charge-id', __NAMESPACE__ . '\\charge_id', 10, 3 );

/**
 * Replaces {charge-date} with the PaymentIntent's first Charge date.
 *
 * @since 3.6.0
 *
 * @param string $value Default value (empty string).
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
function charge_date( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $value;
	}

	$subscription = current( $payment_confirmation_data['subscriptions'] );

	// Localize to current timezone and formatting.
	$value = get_date_from_gmt(
		date( 'Y-m-d H:i:s', $subscription->created ),
		get_option( 'date_format' )
	);

	/**
	 * Filters the {charge-date} smart tag value.
	 *
	 * @since 3.0.0
	 * @deprecated 3.6.0
	 *
	 * @param string $value Charge date.
	 */
	$value = apply_filters_deprecated(
		'simpay_details_order_date',
		array( $value ),
		'3.6.0',
		'simpay_payment_confirmation_template_tag_charge-date'
	);

	return esc_html( $value );
}
add_filter( 'simpay_payment_confirmation_template_tag_charge-date', __NAMESPACE__ . '\\charge_date', 10, 3 );

/**
 * Replaces {tax-amount} smart tag.
 *
 * @since 3.6.0
 *
 * @param string $value Smart tag value.
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
function tax_amount( $value, $payment_confirmation_data ) {
	$form        = $payment_confirmation_data['form'];
	$customer_id = $payment_confirmation_data['customer']->id;

	if ( ! empty( $payment_confirmation_data['subscriptions'] ) ) {
		$object      = current( $payment_confirmation_data['subscriptions'] );
		$object_type = 'subscription';
		$currency    = $object->plan->currency;
	} else {
		$object      = current( $payment_confirmation_data['paymentintents'] );
		$object_type = 'payment_intent';
		$currency    = $object->currency;
	}

	// Tax amounts are only added as metadata when using fixed tax rates.
	// If they do not exist, check the tax status and retrieve the automatic tax amount.
	if ( ! isset( $object->metadata->simpay_tax_amount ) ) {
		$tax_status = get_post_meta( $form->id, '_tax_status', true );

		// Retrieve automatic tax amount.
		if ( 'automatic' === $tax_status ) {
			switch ( $object_type ) {
				// Subscription payment.
				case 'subscription':
					$value = simpay_format_currency(
						$object->latest_invoice->tax,
						$object->currency
					);

					break;

				// One-time payment.
				case 'payment_intent':
					// Stripe Checkout.
					if ( 'stripe_checkout' === $form->get_display_type() ) {
						try {
							$session_id = isset( $_GET['session_id'] )
								? esc_attr( $_GET['session_id'] )
								: '';

							if ( ! empty( $session_id ) ) {
								$session = Session\retrieve(
									$session_id,
									$form->get_api_request_args()
								);

								$value = simpay_format_currency(
									$session->total_details->amount_tax,
									$object->currency
								);
							}
						} catch ( Exception $e ) {
							// Pass through, use default value.
						}

						// Order API.
					} else {
						try {
							$orders = Stripe_API::request(
								'Order',
								'all',
								array(
									'customer' => $customer_id,
								),
								$form->get_api_request_args()
							);

							if ( ! empty( $orders->data ) ) {
								$order = current( $orders->data );

								$value = simpay_format_currency(
									$order->total_details->amount_tax,
									$object->currency
								);
							}
						} catch ( Exception $e ) {
							// Pass through, use default value.
						}
					}

					break;
			}
		}

		// Use metadata from fixed tax rates value.
	} else {
		$value = $object->metadata->simpay_tax_amount;
	}

	if ( empty( $value ) ) {
		$value = simpay_format_currency( 0, $currency );
	}

	return esc_html( $value );
}
add_filter(
	'simpay_payment_confirmation_template_tag_tax-amount',
	__NAMESPACE__ . '\\tax_amount',
	10,
	2
);

/**
 * Replaces {recurring-amount} smart tag.
 *
 * @since 3.6.0
 *
 * @param string $value Smart tag value.
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
function recurring_amount( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $value;
	}

	$subscription = current( $payment_confirmation_data['subscriptions'] );

	// Subscription is cancelled, show status.
	if ( $subscription->canceled_at ) {
		return esc_html_x( 'Cancelled', 'subscription status', 'simple-pay' );
	}

	$recurring_nouns = simpay_get_recurring_intervals();

	// Retrieve line item data.
	$line_item      = current( $subscription->items->data )->price;
	$interval_count = $line_item->recurring->interval_count;
	$interval       = $line_item->recurring->interval;

	// If the interval count is singular, do not show it.
	$interval_string = 1 === $interval_count ? '' : $interval_count;

	// Determine singular or plural day, month, year.
	$interval_count_string = 1 === $interval_count
		? $recurring_nouns[ $interval ][0]
		: $recurring_nouns[ $interval ][1];

	// Determine if a coupon is applied to the Customer.
	$customer             = $payment_confirmation_data['customer'];
	$has_discount         = null !== $customer->discount;
	$has_limited_discount = $has_discount
		&& 'forever' !== $customer->discount->coupon->duration;

	// Current recurring amount.
	$upcoming_invoice = Stripe_API::request(
		'Invoice',
		'upcoming',
		array(
			'customer' => $customer->id,
		),
		$payment_confirmation_data['form']->get_api_request_args()
	);

	$current_recurring_amount = simpay_format_currency(
		$upcoming_invoice->amount_due,
		$upcoming_invoice->currency
	);

	$current_recurring_amount_string = esc_html(
		sprintf(
			/* translators: %1$s Recurring amount. %2$s Recurring interval count. %3$s Recurring interval. */
			_x(
				'%1$s every %2$s %3$s',
				'recurring interval',
				'simple-pay'
			),
			$current_recurring_amount,
			$interval_string,
			$interval_count_string
		)
	);

	// No discount, or forever discount.
	if ( false === $has_discount || false === $has_limited_discount ) {
		return $current_recurring_amount_string;
	}

	// Undiscounted recurring amount.
	$undiscounted_recurring_amount = simpay_format_currency(
		$line_item->unit_amount,
		$line_item->currency
	);

	$undiscounted_recurring_amount_string = esc_html(
		sprintf(
			/* translators: %1$s Recurring amount. %2$s Recurring interval count. %3$s Recurring interval. */
			_x(
				'%1$s every %2$s %3$s',
				'recurring interval',
				'simple-pay'
			),
			$undiscounted_recurring_amount,
			$interval_string,
			$interval_count_string
		)
	);

	$tax_status = get_post_meta(
		$payment_confirmation_data['form']->id,
		'_tax_status',
		true
	);

	if ( 'automatic' === $tax_status ) {
		return esc_html(
			sprintf(
				/* translators: %1$s Recurring amount. %2$s Recurring interval count. %3$s Recurring interval. */
				__(
					'%1$s every %2$s %3$s until coupon expires',
					'simple-pay'
				),
				$current_recurring_amount,
				$interval_string,
				$interval_count_string
			)
		);
	}

	return esc_html(
		sprintf(
			/* translators: %1$s Recurring amount. %2$s Recurring interval count -- not output when 1. %3$s Recurring interval. %4$s Limited discount interval count. %5$s Recurring amount without discount. */
			__(
				'%1$s every %2$s %3$s for %4$s months then %5$s',
				'simple-pay'
			),
			$current_recurring_amount,
			$interval_string,
			$interval_count_string,
			$customer->discount->coupon->duration_in_months,
			$undiscounted_recurring_amount_string
		)
	);
}
add_filter( 'simpay_payment_confirmation_template_tag_recurring-amount', __NAMESPACE__ . '\\recurring_amount', 10, 2 );

/**
 * Replaces {max-charges} smart tag.
 *
 * @since 3.6.0
 *
 * @param string $value Smart tag value.
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
function max_charges( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $value;
	}

	$subscription = current( $payment_confirmation_data['subscriptions'] );

	$value = isset( $subscription->metadata->simpay_charge_max ) ? $subscription->metadata->simpay_charge_max : $value;

	return esc_html( $value );
}
add_filter( 'simpay_payment_confirmation_template_tag_max-charges', __NAMESPACE__ . '\\max_charges', 10, 2 );

/**
 * Replaces {trial-end-date} smart tag.
 *
 * @since 3.6.0
 *
 * @param string $value Smart tag value.
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
function trial_end_date( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $value;
	}

	$subscription = current( $payment_confirmation_data['subscriptions'] );

	$value = date_i18n( get_option( 'date_format' ), $subscription->trial_end );

	return $value;
}
add_filter( 'simpay_payment_confirmation_template_tag_trial-end-date', __NAMESPACE__ . '\\trial_end_date', 10, 2 );

/**
 * Replaces {next-invoice-date} with the Subscription's next Invoice date.
 *
 * @since 4.0.0
 *
 * @param string $value Default value (empty string).
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
function next_invoice_date( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $value;
	}

	$subscription = current( $payment_confirmation_data['subscriptions'] );

	// Localize to current timezone and formatting.
	$value = get_date_from_gmt(
		date( 'Y-m-d H:i:s', $subscription->current_period_end ),
		get_option( 'date_format' )
	);

	return esc_html( $value );
}
add_filter(
	'simpay_payment_confirmation_template_tag_next-invoice-date',
	__NAMESPACE__ . '\\next_invoice_date',
	10,
	3
);

/**
 * Replaces {update-payment-method-url} with a URL to update the Subscription's
 * Payment Method.
 *
 * @since 4.0.0
 *
 * @param string $value Default value (empty string).
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
function update_payment_method_url( $value, $payment_confirmation_data ) {
	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $value;
	}

	$subscription = current( $payment_confirmation_data['subscriptions'] );
	$form         = $payment_confirmation_data['form'];

	$value = esc_url_raw(
		add_query_arg(
			array(
				'customer_id'      => $subscription->customer,
				'subscription_key' => $subscription->metadata->simpay_subscription_key,
				'form_id'          => $subscription->metadata->simpay_form_id,
			),
			$form->payment_success_page
		)
	);

	return esc_url( $value );
}
add_filter(
	'simpay_payment_confirmation_template_tag_update-payment-method-url',
	__NAMESPACE__ . '\\update_payment_method_url',
	10,
	3
);

/**
 * Replaces {payment}, {subscription}, or {customer} smart tag.
 *
 * Tags can be used in the following way:
 *
 *  {payment:metadata:simpay_form_id}
 *  {payment:currency}
 *  {subscription:metadata:simpay_form_id}
 *  {subscription:id}
 *
 * To access object properties.
 *
 * @link https://stripe.com/docs/api/payment_intents
 * @link https://stripe.com/docs/api/subscriptions
 * @link https://stripe.com/docs/api/customers
 *
 * @since 3.7.0
 *
 * @param string $value Smart tag value.
 * @param array  $payment_confirmation_data {
 *   Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @param string $tag Payment confirmation smart tag name, excluding curly braces.
 * @param array  $tag_with_keys Payment confirmation smart tags including keys, excluding curly braces.
 * @return string
 */
function stripe_object_with_keys( $value, $payment_confirmation_data, $tag, $tag_with_keys ) {
	switch ( $tag ) {
		case 'payment':
			// Use first PaymentIntent.
			$object = current( $payment_confirmation_data['paymentintents'] );
			break;
		case 'subscription':
			// Use first Subscription.
			$object = current( $payment_confirmation_data['subscriptions'] );
			break;
		case 'customer':
			$object = $payment_confirmation_data['customer'];
			break;
	}

	$tag_keys = Core_Template_Tags\get_tag_keys( $tag_with_keys );
	$value    = Core_Template_tags\get_object_property_deep( $tag_keys, $object );

	if (
		'' === $value &&
		'payment' === $tag &&
		'' !== $payment_confirmation_data['subscriptions']
	) {
		$object = current( $payment_confirmation_data['subscriptions'] );
		$value  = Core_Template_tags\get_object_property_deep( $tag_keys, $object );
	}

	return esc_html( $value );
}
add_filter( 'simpay_payment_confirmation_template_tag_payment', __NAMESPACE__ . '\\stripe_object_with_keys', 10, 4 );
add_filter( 'simpay_payment_confirmation_template_tag_subscription', __NAMESPACE__ . '\\stripe_object_with_keys', 10, 4 );
add_filter( 'simpay_payment_confirmation_template_tag_customer', __NAMESPACE__ . '\\stripe_object_with_keys', 10, 4 );

/**
 * Returns a list of available smart tags and their descriptions.
 *
 * @todo Temporary until this can be more easily generated through a tag registry.
 *
 * @since 4.0.0
 *
 * @return array
 */
function __unstable_get_tags_and_descriptions() { // phpcs:ignore PHPCompatibility.FunctionNameRestrictions.ReservedFunctionNames.FunctionDoubleUnderscore
	return array(
		'recurring-amount'          => esc_html__(
			'The recurring amount to be charged each period of the subscription plan.',
			'simple-pay'
		),
		'max-charges'               => esc_html__(
			'The total number of max charges set for an installment plan.',
			'simple-pay'
		),
		'trial-end-date'            => esc_html__(
			'The day the plan\'s free trial ends.',
			'simple-pay'
		),
		'next-invoice-date'         => esc_html__(
			'The date the next invoice is due.',
			'simple-pay'
		),
		'update-payment-method-url' => esc_html__(
			'URL to update the subscription\'s payment method.',
			'simple-pay'
		),
	);
}
