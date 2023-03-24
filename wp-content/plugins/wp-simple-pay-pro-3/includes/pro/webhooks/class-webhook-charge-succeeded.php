<?php
/**
 * Webhooks: Charge Succeeded
 *
 * @package SimplePay\Pro\Webhooks
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.7
 */

namespace SimplePay\Pro\Webhooks;

use SimplePay\Pro\Payments\Charge;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhook_Charge_Succeeded class.
 *
 * @since 4.1.7
 */
class Webhook_Charge_Succeeded extends Webhook_Base implements Webhook_Interface {

	/**
	 * Alerts when a Charge has succeeds.
	 *
	 * @since 4.1.7
	 */
	public function handle() {
		$charge = $this->event->data->object;

		// We can't safely proceed if we are unable to identify the Payment Form
		// this webhook originated from.
		if ( ! isset( $charge->metadata->simpay_form_id ) ) {
			return;
		}

		// This Charge is attached to a PaymentIntent. Utilize payment_intent.succeeded instead.
		if ( isset( $charge->payment_intent ) ) {
			return;
		}

		$form_id = isset( $charge->metadata->simpay_form_id )
			? $charge->metadata->simpay_form_id
			: 0;
		$form    = simpay_get_form( $form_id );

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
				),
			),
			$api_request_args
		);

		/**
		 * Allows processing after a Charge succeeds.
		 *
		 * @since 4.1.7
		 *
		 * @param \SimplePay\Vendor\Stripe\Event  $event  Stripe webhook event.
		 * @param \SimplePay\Vendor\Stripe\Charge $charge Stripe Charge.
		 */
		do_action(
			'simpay_webhook_charge_succeeded',
			$this->event,
			$charge
		);
	}
}
