<?php
/**
 * Webhooks: Smart tags
 *
 * @package SimplePay\Pro\Webhooks
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the description for webhooks
 *
 * @since unknown
 *
 * @return string
 */
function simpay_webhook_help_text() {
	$html  = esc_html__( 'In order for "Charge Limit" to function correctly you must set up a Stripe webhook endpoint.', 'simple-pay' );
	$html .= '<br /><br />';
	$html .= '<a href="' . simpay_docs_link( 'See our Charge Limit documentation for a step-by-step guide.', 'installment-plans', 'payment-form-payment-settings', true ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'See our documentation for a step-by-step guide.', 'simple-pay' ) . '</a>';

	return $html;
}

/**
 * Return the webhook URL specific for this user's site
 *
 * @since unknown
 *
 * @return string
 */
function simpay_get_webhook_url() {
	return trailingslashit( rest_url( 'wpsp/v1/webhook-receiver' ) );
}
