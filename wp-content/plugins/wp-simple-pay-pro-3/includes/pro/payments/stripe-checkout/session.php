<?php
/**
 * Stripe Checkout: Session
 *
 * Pro-only functionality adjustments for Stripe Checkout Sessions.
 *
 * @package SimplePay\Pro\Payments\Stripe_Checkout\Plan
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Pro\Payments\Stripe_Checkout\Session;

use SimplePay\Core\API;
use SimplePay\Pro\Payment_Methods;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds Tax Rates to line items.
 *
 * @since 4.1.0
 *
 * @param array                         $session_args Arguments used to create a PaymentIntent.
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @return array
 */
function add_tax_rates( $session_args, $form ) {
	$tax_status = get_post_meta( $form->id, '_tax_status', true );

	// Do not proceed if a tax status hasn't been saved, or it's set to something
	// other than fixed-global.
	if ( ! empty( $tax_status ) && 'fixed-global' !== $tax_status ) {
		return $session_args;
	}

	// Tax rates.
	$tax_rates = simpay_get_payment_form_tax_rates( $form );

	if ( ! empty( $tax_rates ) ) {
		$session_args['line_items'][0]['tax_rates'] =
			wp_list_pluck( $tax_rates, 'id' );
	}

	return $session_args;
}
add_filter(
	'simpay_get_session_args_from_payment_form_request',
	__NAMESPACE__ . '\\add_tax_rates',
	10,
	2
);

/**
 * Adds automatic tax support to a Stripe Checkout Session.
 *
 * @since 4.6.0
 *
 * @param array                         $session_args Arguments used to create a PaymentIntent.
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 * @return array
 */
function add_automatic_tax( $session_args, $form ) {
	$tax_status = get_post_meta( $form->id, '_tax_status', true );

	if ( 'automatic' !== $tax_status ) {
		return $session_args;
	}

	$session_args['automatic_tax'] = array(
		'enabled' => true,
	);

	if ( isset( $session_args['customer'] ) ) {
		$session_args['customer_update'] = array(
			'address' => 'auto',
		);
	}

	return $session_args;
}
add_filter(
	'simpay_get_session_args_from_payment_form_request',
	__NAMESPACE__ . '\\add_automatic_tax',
	10,
	2
);

/**
 * Adds `payment_method_types` to Stripe Checkout Session.
 *
 * @since 3.8.0
 *
 * @param array                          $session_args Arguments used to create a PaymentIntent.
 * @param \SimplePay\Core\Abstracts\Form $form Form instance.
 * @param array                          $form_data Form data generated by the client.
 * @return array
 */
function add_payment_method_types( $session_args, $form, $form_data ) {
	// Retrieve price option.
	$price = simpay_payment_form_prices_get_price_by_id(
		$form,
		$form_data['price']['id']
	);

	if ( false === $price ) {
		throw new \Exception(
			__( 'Unable to locate payment form price.', 'simple-pay' )
		);
	}

	$currency  = $price->currency;
	$recurring = null !== $price->recurring && false === $price->can_recur;

	$is_optionally_recurring = isset( $form_data['isRecurring'] ) &&
		true === $form_data['isRecurring'];

	$payment_methods = Payment_Methods\get_form_payment_methods( $form );

	// Remove Payment Methods that do not support the current currency.
	$payment_methods = array_filter(
		$payment_methods,
		function( $payment_method ) use ( $currency ) {
			return in_array( $currency, $payment_method->currencies, true );
		}
	);

	// Remove Payment Methods that do not support the current recurring options
	// if recurring is being used.
	if ( true === $recurring || true === $is_optionally_recurring ) {
		$payment_methods = array_filter(
			$payment_methods,
			function( $payment_method ) {
				// Check for Stripe Checkout-specific overrides first.
				if (
					is_array( $payment_method->stripe_checkout ) &&
					isset( $payment_method->stripe_checkout['recurring'] )
				) {
					return true === $payment_method->stripe_checkout['recurring'];
				}

				// Check general recurring capabilities.
				return true === $payment_method->recurring;
			}
		);
	}

	// Transform IDs such as sepa-debit to sepa_debit.
	$session_args['payment_method_types'] = array_map(
		function( $payment_method_id ) {
			switch ( $payment_method_id ) {
				case 'ach-debit':
					return 'us_bank_account';
				default:
					return str_replace( '-', '_', $payment_method_id );
			}
		},
		array_keys( $payment_methods )
	);

	return $session_args;
}
add_filter(
	'simpay_get_session_args_from_payment_form_request',
	__NAMESPACE__ . '\\add_payment_method_types',
	10,
	5
);

/**
 * Maps Stripe Checkout Session data to the Customer.
 *
 * Collected Billing Address is only assigned to the Payment Method, and collected
 * Shipping Address is only available directly on the Checkout Session object.
 *
 * This data may not be immediately available in confirmations or emails due
 * to the order (or lack of order) webhooks fire. Populating the data still benefits
 * Stripe's Customer Billing Portal.
 *
 * @link https://github.com/wpsimplepay/wp-simple-pay-pro/issues/973
 *
 * @since 4.1.0
 *
 * @param \SimplePay\Vendor\Stripe\Event         $event Stripe webhook event.
 * @param null|\SimplePay\Vendor\Stripe\Customer $customer Stripe Customer.
 */
function map_object_data_to_customer( $event, $customer ) {
	if ( null === $customer ) {
		return;
	}

	$object = $event->data->object;

	$form_id = isset( $object->metadata->simpay_form_id )
		? $object->metadata->simpay_form_id
		: '';

	if ( empty( $form_id ) ) {
		return;
	}

	$form = simpay_get_form( $form_id );

	if ( false === $form ) {
		return;
	}

	$payment_methods = API\PaymentMethods\all(
		array(
			'type'     => 'card',
			'customer' => $customer->id,
		),
		$form->get_api_request_args()
	);

	$payment_method = current( $payment_methods->data );

	if ( false === $payment_method ) {
		return;
	}

	// Metadata.
	$metadata = $customer->metadata->toArray();

	// Ensure generated metadata key is not copied over.
	if ( isset( $metadata['simpay_is_generated_customer'] ) ) {
		$metadata['simpay_is_generated_customer'] = '';
	}

	// Address.
	$billing_address = isset(
		$payment_method->billing_details,
		$payment_method->billing_details->address
	)
		? $payment_method->billing_details->address->toArray()
		: array();

	// Name.
	$billing_name = isset(
		$payment_method->billing_details,
		$payment_method->billing_details->name
	)
		? $payment_method->billing_details->name
		: '';

	// Attempt to find the Shipping Address.
	if ( isset( $event->data->object->shipping ) ) {
		$shipping_name = isset( $event->data->object->shipping->name )
			? $event->data->object->shipping->name
			: '';

		$shipping_address = isset( $event->data->object->shipping->address )
			? $event->data->object->shipping->address->toArray()
			: '';

		$shipping_args = array();

		if ( ! empty( $shipping_name ) ) {
			$shipping_args['name'] = $shipping_name;
		}

		if ( ! empty( $shipping_address ) ) {
			$shipping_args['address'] = $shipping_address;
		}
	} else {
		$shipping_args = array();
	}

	$customer_update_args = array(
		'metadata' => $metadata,
	);

	if ( ! empty( $billing_address ) ) {
		$customer_update_args['address'] = $billing_address;
	}

	if ( ! empty( $billing_name ) ) {
		$customer_update_args['name'] = $billing_name;
	}

	if ( ! empty( $shipping_args ) ) {
		$customer_update_args['shipping'] = $shipping_args;
	}

	try {
		API\Customers\update(
			$customer->id,
			$customer_update_args,
			$form->get_api_request_args()
		);
	} catch ( \Exception $e ) {
		// Updating this is not required, and a failure should not cause other
		// actions to rerun.
	}
}
add_filter(
	'simpay_webhook_checkout_session_completed',
	__NAMESPACE__ . '\\map_object_data_to_customer',
	20,
	2
);
