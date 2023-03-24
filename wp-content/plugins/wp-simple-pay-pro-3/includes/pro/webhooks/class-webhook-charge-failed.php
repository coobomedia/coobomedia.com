<?php
/**
 * Webhooks: Charge Failed
 *
 * @package SimplePay\Pro\Webhooks
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.9.0
 */

namespace SimplePay\Pro\Webhooks;

use SimplePay\Pro\Payments\Charge;
use SimplePay\Core\Payments\PaymentIntent;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhook_Charge_Failed class.
 *
 * @since 3.9.0
 */
class Webhook_Charge_Failed extends Webhook_Base implements Webhook_Interface {

	/**
	 * Alerts when a Charge has failed.
	 *
	 * @since 3.9.0
	 */
	public function handle() {
		$charge = $this->event->data->object;

		// Look to see if the metadata exists in the Charge (one time payment).
		if ( isset( $charge->metadata->simpay_form_id ) ) {
			$form_id = $charge->metadata->simpay_form_id;

			// Look to see if the metadata exists in the Subscription's Invoice PaymentIntent (recurring payment).
		} else if ( isset( $charge->payment_intent ) ) {
			$payment_intent = PaymentIntent\retrieve(
				array(
					'id'     => $charge->payment_intent,
					'expand' => array(
						'invoice.subscription'
					)
				),
				array(
					'api_key' => simpay_get_secret_key(),
				)
			);

			if (
				! isset(
					$payment_intent->invoice,
					$payment_intent->invoice->subscription,
					$payment_intent->invoice->subscription->metadata->simpay_form_id
				)
			) {
				return;
			}

			$form_id = $payment_intent->invoice->subscription->metadata->simpay_form_id;
		} else {
			return;
		}

		$form = simpay_get_form( $form_id );

		$api_request_args = false !== $form
			? $form->get_api_request_args()
			: array(
				'api_key' => simpay_get_secret_key(),
			);

		// Retreive again with Customer expanded.
		$charge = Charge\retrieve(
			array(
				'id'     => $charge->id,
				'expand' => array(
					'customer',
					'payment_intent',
					'invoice.subscription',
				),
			),
			$api_request_args
		);

		/**
		 * Allows processing after a Charge fails.
		 *
		 * @since 3.9.0
		 *
		 * @param \SimplePay\Vendor\Stripe\Event  $event  Stripe webhook event.
		 * @param \SimplePay\Vendor\Stripe\Charge $charge Stripe Charge.
		 */
		do_action( 'simpay_webhook_charge_failed', $this->event, $charge );
	}
}
