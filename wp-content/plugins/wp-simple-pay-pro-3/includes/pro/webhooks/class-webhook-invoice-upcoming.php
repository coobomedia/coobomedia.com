<?php
/**
 * Webhooks: Invoice Upcoming
 *
 * @package SimplePay\Pro\Webhooks
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.7.0
 */

namespace SimplePay\Pro\Webhooks;

use SimplePay\Core\API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhook_Invoice_Upcoming class.
 *
 * @since 3.7.0
 */
class Webhook_Invoice_Upcoming extends Webhook_Base implements Webhook_Interface {

	/**
	 * Stripe Invoice.
	 *
	 * @since 3.7.0
	 * @var \SimplePay\Vendor\Stripe\Invoice
	 */
	public $invoice;

	/**
	 * Stripe Subscription.
	 *
	 * @since 3.7.0
	 * @var \SimplePay\Vendor\Stripe\Subscription
	 */
	public $subscription;

	/**
	 * Handles the Webhook's data.
	 *
	 * @since 3.7.0
	 *
	 * @throws \Exception When required data is missing or cannot be verified.
	 */
	public function handle() {
		$this->invoice = $this->event->data->object;

		// We can't safely proceed if we are unable to identify the Payment Form
		// this webhook originated from.
		if ( ! isset( end( $this->invoice->lines->data )->metadata->simpay_form_id ) ) {
			return;
		}

		$form_id = end( $this->invoice->lines->data )->metadata->simpay_form_id;
		$form    = simpay_get_form( $form_id );

		$api_request_args = false !== $form
			? $form->get_api_request_args()
			: array(
				'api_key' => simpay_get_secret_key(),
			);

		if ( ! $this->invoice->subscription ) {
			throw new \Exception( esc_html__( 'Subscription not found.', 'simple-pay' ) );
		}

		$this->subscription = API\Subscriptions\retrieve(
			array(
				'id'     => $this->invoice->subscription,
				'expand' => array(
					'customer',
				),
			),
			$api_request_args
		);

		/**
		 * Allows additional actions to be performed inside the `invoice.upcoming` event processing.
		 *
		 * @since 3.7.0
		 *
		 * @param \SimplePay\Vendor\Stripe\Event        $event        Stripe Event object.
		 * @param \SimplePay\Vendor\Stripe\Invoice      $invoice      Stripe Invoice object.
		 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription object.
		 */
		do_action(
			'simpay_webhook_invoice_upcoming',
			$this->event,
			$this->invoice,
			$this->subscription
		);
	}

}
