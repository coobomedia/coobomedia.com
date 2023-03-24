<?php
/**
 * Webhooks: Checkout Session Completed
 *
 * @package SimplePay\Pro\Webhooks
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.7.0
 */

namespace SimplePay\Pro\Webhooks;

use SimplePay\Core\API;
use SimplePay\Core\Payments\PaymentIntent;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhook_Checkout_Session_Completed class.
 *
 * @since 3.7.0
 */
class Webhook_Checkout_Session_Completed extends Webhook_Base implements Webhook_Interface {

	/**
	 * Customer.
	 *
	 * @since 3.7.0
	 * @var \SimplePay\Vendor\Stripe\Customer
	 */
	public $customer = null;

	/**
	 * Payment Intent.
	 *
	 * @since 3.7.0
	 * @var \SimplePay\Vendor\Stripe\PaymentIntent
	 */
	public $payment_intent = null;

	/**
	 * Subscription.
	 *
	 * @since 3.7.0
	 * @var \SimplePay\Vendor\Stripe\Subscription
	 */
	public $subscription = null;

	/**
	 * Handle the Webhook's data.
	 *
	 * @since 3.7.0
	 */
	public function handle() {
		$object = $this->event->data->object;

		// We can't safely proceed if we are unable to identify the Payment Form
		// this webhook originated from.
		$form_id = isset( $object->metadata->simpay_form_id )
			? $object->metadata->simpay_form_id
			: 0;
		$form    = simpay_get_form( $form_id );

		if ( false === $form ) {
			return;
		}

		if ( null !== $object->customer ) {
			$this->customer = API\Customers\retrieve(
				$object->customer,
				$form->get_api_request_args()
			);
		}

		if ( null !== $object->payment_intent ) {
			$this->payment_intent = PaymentIntent\retrieve(
				$object->payment_intent,
				$form->get_api_request_args()
			);
		}

		if ( null !== $object->subscription ) {
			$this->subscription = API\Subscriptions\retrieve(
				array(
					'id' => $object->subscription,
					'expand' => array(
						'latest_invoice.payment_intent',
						'pending_setup_intent',
					)
				),
				$form->get_api_request_args()
			);
		}

		/**
		 * Allows processing after a Checkout Session is completed.
		 *
		 * @since 3.7.0
		 *
		 * @param \SimplePay\Vendor\Stripe\Event              $event Stripe webhook event.
		 * @param null|\SimplePay\Vendor\Stripe\Customer      $customer Stripe Customer.
		 * @param null|\SimplePay\Vendor\Stripe\PaymentIntent $payment_intent Stripe PaymentIntent.
		 * @param null|\SimplePay\Vendor\Stripe\Subscription  $subscription Stripe Subscription.
		 */
		do_action(
			'simpay_webhook_checkout_session_completed',
			$this->event,
			$this->customer,
			$this->payment_intent,
			$this->subscription
		);
	}
}
