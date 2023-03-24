<?php
/**
 * Stripe Checkout: Customer
 *
 * Pro-only functionality adjustments for Stripe Checkout Customers.
 *
 * @package SimplePay\Pro\Payments\Stripe_Checkout\Customer
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.7.0
 */

namespace SimplePay\Pro\Payments\Stripe_Checkout\Customer;

use SimplePay\Core\API;
use SimplePay\Core\Payments\PaymentIntent;

/**
 * Adds `simpay_is_generated_customer` metadata to Stripe Customer records that are
 * generated for Stipe Checkout.
 *
 * This allows cleanup of these records if the Stripe Checkout Session is not completed.
 *
 * @link https://github.com/wpsimplepay/WP-Simple-Pay-Pro-3/issues/964
 * @see \SimplePay\Pro\Payments\Stripe_Checkout\Subscription\create_custom_plan_from_template()
 *
 * @since 3.7.0
 *
 * @param array                          $customer_args Arguments used to create a Stripe Customer.
 * @param \SimplePay\Core\Abstracts\Form $form Form instance.
 */
function add_generated_record_metadata( $customer_args, $form ) {
	if ( 'stripe_checkout' !== $form->get_display_type() ) {
		return $customer_args;
	}

	$customer_args['metadata']['simpay_is_generated_customer'] = 1;

	return $customer_args;
}
add_filter( 'simpay_get_customer_args_from_payment_form_request', __NAMESPACE__ . '\\add_generated_record_metadata', 10, 2 );

/**
 * Removes `simpay_is_generated_customer` metadata from a Stripe Customer record
 * when a Stripe Checkout Session has been completed.
 *
 * @link https://github.com/wpsimplepay/WP-Simple-Pay-Pro-3/issues/964
 *
 * @since 3.7.0
 *
 * @param \SimplePay\Vendor\Stripe\Event         $event Stripe webhook event.
 * @param null|\SimplePay\Vendor\Stripe\Customer $customer Stripe Customer.
 */
function remove_generated_record_metadata( $event, $customer ) {
	if ( null === $customer ) {
		return;
	}

	$form_id = isset( $customer->metadata->simpay_form_id )
		? $customer->metadata->simpay_form_id
		: '';

	if ( empty( $form_id ) ) {
		return;
	}

	$form = simpay_get_form( $form_id );

	if ( false === $form ) {
		return;
	}

	$metadata = $customer->metadata->toArray();

	if ( isset( $metadata['simpay_is_generated_customer'] ) ) {
		$metadata['simpay_is_generated_customer'] = '';

		API\Customers\update(
			$customer->id,
			array(
				'metadata' => $metadata,
			),
			$form->get_api_request_args()
		);
	}
}
add_action( 'simpay_webhook_checkout_session_completed', __NAMESPACE__ . '\\remove_generated_record_metadata', 10, 2 );

/**
 * Finds all Customers created in the last 24 hours and removes any that have been
 * generated but not assigned to a completed purchase.
 *
 * @since 3.7.0
 *
 * @param \SimplePay\Vendor\Stripe\Event         $event Stripe webhook event.
 * @param null|\SimplePay\Vendor\Stripe\Customer $customer Stripe Customer.
 */
function cleanup_generated_records( $event, $customer ) {
	if ( null === $customer ) {
		return;
	}

	$form_id = isset( $customer->metadata->simpay_form_id )
		? $customer->metadata->simpay_form_id
		: 0;

	$form = simpay_get_form( $form_id );

	if ( false === $form ) {
		return;
	}

	$remove_generated_customers = true;

	/**
	 * Filters if generated Customers should be removed.
	 *
	 * @since 3.7.0
	 *
	 * @param bool $remove_generated_customers Determines if generated Customers should be removed.
	 */
	$remove_generated_customers = apply_filters( 'simpay_remove_generated_customers', $remove_generated_customers );

	if ( true !== $remove_generated_customers ) {
		return;
	}

	try {
		$start = time() - ( DAY_IN_SECONDS * 2 );
		$end   = time() - ( DAY_IN_SECONDS );

		/**
		 * Filters the timestamp used as the starting point of the time range
		 * for finding generated Customers.
		 *
		 * @since 3.7.0
		 *
		 * @param bool $start Starting timestamp for query time range.
		 *                    Default 2 days ago.
		 */
		$start = apply_filters( 'simpay_remove_generated_items_start', $start );

		/**
		 * Filters the timestamp used as the ending point of the time range
		 * for finding generated Customers.
		 *
		 * @since 3.9.0
		 *
		 * @param bool $end Ending timestamp for query time range.
		 *                  Default 1 day ago.
		 */
		$end = apply_filters( 'simpay_remove_generated_items_end', $end );

		$customers = API\Customers\all(
			array(
				'created' => array(
					'gte' => $start,
					'lte' => $end,
				),
			),
			$form->get_api_request_args()
		);

		foreach ( $customers->autoPagingIterator() as $customer ) {
			// Customer is not generated.
			if ( ! isset( $customer->metadata->simpay_is_generated_customer ) ) {
				continue;
			}

			// Customer has Subscriptions/Payments.
			$subscriptions = ! empty( $customer->subscriptions->data );

			$_payments = PaymentIntent\all(
				array(
					'customer' => $customer->id,
					'limit'    => 1,
				),
				$form->get_api_request_args()
			);
			$_payments = $_payments->data;

			// Has at one succeeded payment.
			$payments = ! empty( $_payments ) && 'succeeded' === current( $_payments )['status'];

			if ( true === $payments || true === $subscriptions ) {
				continue;
			}

			$customer->delete();
		}
	} catch ( \Exception $e ) {
		// Webhook should still succeed even if the Customer cannot be deleted.
	}
}
add_action( 'simpay_webhook_checkout_session_completed', __NAMESPACE__ . '\\cleanup_generated_records', 20, 2 );
