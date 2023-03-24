<?php
/**
 * Webhook: Payment Intent Succeeded
 *
 * @package SimplePay\Pro\Webhooks
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.3
 */

namespace SimplePay\Pro\Webhooks;

use SimplePay\Core\Payments\PaymentIntent;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhook_Payment_Intent_Succeeded class.
 *
 * @since 3.6.3
 */
class Webhook_Payment_Intent_Succeeded extends Webhook_Base implements Webhook_Interface {

	/**
	 * Stripe Payment Intent.
	 *
	 * @var \SimplePay\Vendor\Stripe\PaymentIntent
	 * @since 3.6.3
	 */
	public $payment_intent;

	/**
	 * Handle the Webhook's data.
	 *
	 * @since 3.6.3
	 */
	public function handle() {
		$payment_intent = $this->event->data->object;

		// We can't safely proceed if we are unable to identify the Payment Form
		// this webhook originated from.
		if ( ! isset( $payment_intent->metadata->simpay_form_id ) ) {
			return;
		}

		$form_id = isset( $payment_intent->metadata->simpay_form_id )
			? $payment_intent->metadata->simpay_form_id
			: 0;
		$form    = simpay_get_form( $form_id );

		if ( false === $form ) {
			return;
		}

		// Retreive again with Customer expanded.
		$this->payment_intent = PaymentIntent\retrieve(
			array(
				'id'     => $payment_intent->id,
				'expand' => array(
					'customer',
				),
			),
			$form->get_api_request_args()
		);

		// PaymentIntent is not created by an Invoice.
		if ( ! $payment_intent->invoice && 'succeeded' === $payment_intent->status ) {
			/**
			 * Allows processing after a single payment intent succeeds.
			 *
			 * @since 3.6.3
			 *
			 * @param \SimplePay\Vendor\Stripe\Event         $event Stripe webhook event.
			 * @param \SimplePay\Vendor\Stripe\PaymentIntent $payment_intent Stripe PaymentIntent.
			 */
			do_action( 'simpay_webhook_payment_intent_succeeded', $this->event, $this->payment_intent );
		}
	}

}
