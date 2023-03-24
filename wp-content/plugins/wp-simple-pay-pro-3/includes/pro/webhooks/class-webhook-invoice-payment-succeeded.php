<?php
/**
 * Webhooks: Invoice Payment Succeeded
 *
 * @package SimplePay\Pro\Webhooks
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Pro\Webhooks;

use SimplePay\Core\API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhook_Invoice_Payment_Succeeded class.
 *
 * @since 3.5.0
 */
class Webhook_Invoice_Payment_Succeeded extends Webhook_Base implements Webhook_Interface {

	/**
	 * Payment Form.
	 *
	 * @var \SimplePay\Core\Abstracts\Form
	 * @since 3.8.0
	 */
	public $form;

	/**
	 * Stripe Subscription.
	 *
	 * @var \SimplePay\Vendor\Stripe\Subscription
	 * @since 3.6.3
	 */
	public $subscription;

	/**
	 * Handle the Webhook's data.
	 *
	 * @since 3.5.0
	 */
	public function handle() {
		$invoice = $this->event->data->object;

		if ( false === isset( $invoice->subscription ) ) {
			return;
		}

		// Subscription was created without form context. Use global Payment Mode.
		if ( ! isset( end( $invoice->lines->data )->metadata->simpay_form_id ) ) {
			$this->form = false;

			$this->subscription = API\Subscriptions\retrieve(
				array(
					'id'     => $invoice->subscription,
					'expand' => array(
						'customer',
						'latest_invoice.payment_intent',
						'pending_setup_intent',
					),
				),
				array(
					'api_key' => simpay_get_secret_key(),
				)
			);
		} else {
			$form_id = end( $invoice->lines->data )->metadata->simpay_form_id;
			$form    = simpay_get_form( $form_id );

			$api_request_args = false !== $form
				? $form->get_api_request_args()
				: array(
					'api_key' => simpay_get_secret_key(),
				);

			$this->form = $form;

			$this->subscription = API\Subscriptions\retrieve(
				array(
					'id'     => $invoice->subscription,
					'expand' => array(
						'customer',
						'latest_invoice.payment_intent',
						'pending_setup_intent',
					),
				),
				$api_request_args
			);
		}

		// Initial invoice, Subscription is new.
		if ( 'subscription_create' === $invoice->billing_reason ) {
			/**
			 * Allows processing after a subscription's first payment has been completed.
			 *
			 * This is done here instead of the actual `customer.subscription.created` webhook
			 * to ensure it is only run after an invoice has been successfully paid.
			 *
			 * @since 3.6.3
			 *
			 * @param \SimplePay\Vendor\Stripe\Event        $event Stripe webhook event.
			 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription.
			 */
			do_action( 'simpay_webhook_subscription_created', $this->event, $this->subscription );
		}

		$this->handle_installment_plan();
	}

	/**
	 * Tracks the number of invoices that have been charged, and cancels the subscription
	 * when the maximum charge count has been reached.
	 *
	 * @todo May be better to retrieve the actual Invoices from Stripe and count those.
	 *
	 * @since 3.6.0
	 *
	 * @see https://stripe.com/docs/recipes/installment-plan
	 */
	private function handle_installment_plan() {
		$invoice = $this->event->data->object;

		/**
		 * Allow additional actions to be performed inside the `invoice.payment_succeeded` event processing.
		 *
		 * @since 3.5.0
		 *
		 * @param \SimplePay\Vendor\Stripe\Event        $this->event Stripe Event object.
		 * @param \SimplePay\Vendor\Stripe\Invoice      $invoice Stripe Invoice object.
		 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription object.
		 */
		do_action( 'simpay_webhook_invoice_payment_succeeded', $this->event, $invoice, $this->subscription );

		// No max charge is set, so do nothing.
		if ( ! isset( $this->subscription->metadata['simpay_charge_max'] ) ) {
			return;
		}

		$max_charges  = $this->subscription->metadata['simpay_charge_max'];
		$charge_count = $this->subscription->metadata['simpay_charge_count'];

		$charge_count++;

		// Update the total count metadata.
		$this->subscription->metadata['simpay_charge_count'] = absint( $charge_count );
		$this->subscription->save();

		/**
		 * Allow additional actions to be performed before subscription metadata is updated.
		 *
		 * Since 3.5.0 this now actually happens *after* the subscription is updated in Stripe.
		 *
		 * @since 3.5.0
		 *
		 * @param \SimplePay\Vendor\Stripe\Event        $this->event Stripe Event object.
		 * @param \SimplePay\Vendor\Stripe\Invoice      $invoice Stripe Invoice object.
		 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription object.
		 */
		do_action( 'simpay_webhook_after_installment_increase', $this->event, $invoice, $this->subscription );

		// Cancel subscription if the new charge count equals (or is somehow greater) than the max charges.
		if ( $charge_count >= $max_charges ) {
			$this->subscription->cancel();

			/**
			 * Allow additional actions to be performed after a subscription is cancelled.
			 *
			 * @since 3.5.0
			 *
			 * @param object $this->event Stripe Event object.
			 * @param object $invoice Stripe Invoice object.
			 * @param object $subscription Stripe Subscription object.
			 */
			do_action( 'simpay_webhook_after_subscription_cancel', $this->event, $invoice, $this->subscription );
		}
	}
}
