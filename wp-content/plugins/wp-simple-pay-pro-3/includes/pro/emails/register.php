<?php
/**
 * Emails: Registration
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Emails;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers emails.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Utils\Collections $registry Collections registry.
 */
function register_emails( $registry ) {
	// Add Email registry to Collections registry.
	$emails = new Email_Collection();
	$registry->add( 'emails', $emails );

	// Payment Confirmation.
	$emails->add(
		new Email_Payment_Confirmation(
			array(
				'id'          => 'payment-confirmation',
				'label'       => __( 'Payment Receipt', 'simple-pay' ),
				'description' => __(
					'Send a payment receipt email to the customer upon successful payment',
					'simple-pay'
				),
				'settings'    => array(
					'subject' => esc_html(
						sprintf(
							/* translators: %s Site name */
							__( 'Payment Receipt for %s', 'simple-pay' ),
							get_bloginfo( 'name' )
						)
					),
				),
			)
		)
	);

	$emails->add(
		new Email_Payment_Notification(
			array(
				'id'          => 'payment-notification',
				'label'       => __( 'Payment Notification', 'simple-pay' ),
				'description' => __(
					'Send a payment notification email upon successful payment',
					'simple-pay'
				),
				'settings'    => array(
					'to'      => esc_html( get_bloginfo( 'admin_email' ) ),
					'subject' => esc_html(
						sprintf(
							/* translators: %s Site name */
							__( 'Payment Notification for %s', 'simple-pay' ),
							get_bloginfo( 'name' )
						)
					),
					'body'    => 'A new payment from {customer:email} for {total-amount} has been received for &ldquo;{form-title}&rdquo;.',
				),
			)
		)
	);

	// Upcoming Invoice.
	$subscription_management = simpay_get_setting(
		'subscription_management',
		'on-site'
	);

	$default = 'Dear {customer:email},<br /><br />This is a friendly reminder that the next payment for your subscription to &ldquo;{form-title}&rdquo; will automatically process on {next-invoice-date}. Your payment method on file will be charged at that time.<br /><ul><li><strong>Subscription Activation Date:</strong> {charge-date}</li><li><strong>Initial Payment Amount:</strong> {total-amount}</li><li><strong>Recurring Payment Amount:</strong> {recurring-amount}</li></ul>';

	if ( 'none' !== $subscription_management ) {
		$default .= '<br /><br />You can manage your subscription at any time by visiting: {update-payment-method-url}';
	}

	$emails->add(
		new Email_Upcoming_Invoice(
			array(
				'id'          => 'upcoming-invoice',
				'label'       => __( 'Upcoming Invoice', 'simple-pay' ),
				'description' => __(
					'Send an email to the customer for upcoming invoice payments',
					'simple-pay'
				),
				'settings'    => array(
					'subject' => esc_html(
						sprintf(
							/* translators: %s Site name */
							__( 'Upcoming Invoice for %s', 'simple-pay' ),
							get_bloginfo( 'name' )
						)
					),
					'body'    => $default,
				),
				'licenses'    => array(
					'plus',
					'professional',
					'ultimate',
					'elite',
				),
			)
		)
	);

	// Invoice confirmation.
	$default = 'Dear {customer:email},<br /><br />A recurring payment for your subscription to &ldquo;{form-title}&rdquo; has processed.<br /><ul><li><strong>Payment ID:</strong> {charge-id}</li><li><strong>Subscription Activation Date:</strong> {charge-date}</li><li><strong>Initial Payment Amount:</strong> {total-amount}</li><li><strong>Recurring Payment Amount:</strong> {recurring-amount}</li></ul>';

	if ( 'none' !== $subscription_management ) {
		$default .= '<br /><br />You can manage your subscription at any time by visiting: {update-payment-method-url}';
	}

	$emails->add(
		new Email_Invoice_Confirmation(
			array(
				'id'          => 'invoice-confirmation',
				'label'       => __( 'Invoice Receipt', 'simple-pay' ),
				'description' => __(
					'Send an email to the customer upon successful invoice payment.',
					'simple-pay'
				),
				'settings'    => array(
					'subject' => esc_html(
						sprintf(
							/* translators: %s Site name */
							__( 'Payment Received for %s', 'simple-pay' ),
							get_bloginfo( 'name' )
						)
					),
					'body'    => $default,
				),
				'licenses'    => array(
					'professional',
					'ultimate',
					'elite',
				),
			)
		)
	);

	/**
	 * Allows further emails to be registered.
	 *
	 * @since 4.0.0
	 *
	 * @param \SimplePay\Pro\Emails\Email_Collection $emails Emails collection.
	 */
	do_action( 'simpay_register_emails', $emails );
}
add_action( 'simpay_register_collections', __NAMESPACE__ . '\\register_emails', 5 );
