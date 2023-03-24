<?php
/**
 * Tools: Resend Payment Confirmation
 *
 * @package SimplePay\Pro\Customers\Tools\Resend_Payment_Confirmation
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Emails\Tools\Resend_Payment_Confirmation;

use SimplePay\Core\API;
use SimplePay\Core\Settings;
use SimplePay\Core\Payments;
use SimplePay\Pro\Emails;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Outputs the "Resend Payment Receipt" tool markup.
 *
 * @since 4.0.0
 */
function output() {
	$nonce = wp_create_nonce( 'simpay-resend-payment-confirmation' );
	$email = Emails\get( 'payment-confirmation' );

	$payment_confirmation_email_url = Settings\get_url(
		array(
			'section'    => 'emails',
			'subsection' => 'payment-confirmation',
			'setting'    => 'email_payment-confirmation',

		)
	);
	?>

	<div
		class="simpay-send-payment-confirmation card"
		data-nonce="<?php echo esc_attr( $nonce ); ?>"
	>
		<h2><?php esc_html_e( 'Resend Payment Receipt', 'simple-pay' ); ?></h2>

		<?php if ( true === $email->is_enabled() ) : ?>
			<p class="simpay-send-payment-confirmation__email">
				<label for="simpay-send-payment-confirmation-email">
					<?php esc_html_e( 'Customer Email Address', 'simple-pay' ); ?>:
				</label><br />

				<input
					type="text"
					id="simpay-send-payment-confirmation-email"
					class="regular-text"
					value=""
					autocomplete="off"
				/>

				<button
					type="button"
					name="simpay-send-payment-confirmation-search"
					id="simpay-send-payment-confirmation-search"
					class="button button-secondary"
				>
					<?php esc_html_e( 'Search', 'simple-pay' ); ?>
				</button>
			</p>

			<fieldset
				class="simpay-send-payment-confirmation__results hidden"
			>
				<legend class="screen-reader-text">
					<?php esc_html_e( 'Search Results', 'simple-pay' ); ?>
				</legend>

				<div id="simpay-send-payment-confirmation-results"></div>
			</fieldset>

			<p>
				<button
					id="simpay-send-payment-confirmation-submit"
					class="simpay-send-payment-confirmation__button button button-primary"
					disabled
					data-loading="<?php esc_attr_e( 'Sending&hellip;', 'simple-pay' ); ?>"
					data-active="<?php esc_attr_e( 'Resend Payment Receipt', 'simple-pay' ); ?>"
				>
					<?php esc_html_e( 'Resend Payment Receipt', 'simple-pay' ); ?>
				</button>
			</p>
		<?php else : ?>
			<p>
				<?php
				esc_html_e(
					'The "Payment Receipt" email must be enabled to resend receipts.',
					'simple-pay'
				);
				?>
			</p>

			<p>
				<a
					href="<?php echo esc_url( $payment_confirmation_email_url ); ?>"
					class="button button-secondary"
				>
					<?php esc_html_e( 'Enable Payment Receipt Email', 'simple-pay' ); ?>
				</a>
			</p>
		<?php endif; ?>
	</div>

	<?php
}

/**
 * Retrieves Customer results based on an email address.
 *
 * Returns a list of Customers with additional fields:
 * - created_i18n: Creation date in site timezone and formatting.
 * - link: URL to Customer record in the Stripe dashboard.
 *
 * @since 4.0.0
 *
 * @param string $email Customer email address.
 * @return \SimplePay\Vendor\Stripe\Customer[]
 */
function get_customers( $email ) {
	try {
		$customers = API\Customers\all(
			array(
				'email' => $email,
				'limit' => 100,
			),
			array(
				'api_key' => simpay_get_secret_key(),
			)
		);

		// Hide Customers that have an invalid email according to WordPress.
		$customers->data = array_filter(
			$customers->data,
			function( $customer ) {
				return is_email( $customer->email );
			}
		);

		// Adds additional fields to returned Customer objects.
		array_walk(
			$customers->data,
			function( $customer ) {
				$customer->created_i18n = get_date_from_gmt(
					date( 'Y-m-d H:i:s', $customer->created ),
					get_option( 'date_format' )
				);

				$customer->link = sprintf(
					'https://dashboard.stripe.com/%scustomers/%s',
					simpay_is_test_mode() ? 'test/' : '',
					$customer->id
				);

				return $customer;
			}
		);

		return $customers->data;
	} catch ( \Exception $e ) {
		return array();
	}
}

/**
 * Sends a Payment Confirmation email based on a Customer's available object.
 *
 * @since 4.0.0
 *
 * @see \SimplePay\Pro\Webhooks\Emails
 *
 * @param \SimplePay\Vendor\Stripe\Subscription|\SimplePay\Vendor\Stripe\PaymentIntent $object Stripe object.
 * @return bool
 */
function resend_for_object( $object ) {
	$email = Emails\get( 'payment-confirmation' );

	if ( false === $email ) {
		return false;
	}

	$payment_confirmation_data = Payments\Payment_Confirmation\get_confirmation_data(
		$object->customer->id,
		false,
		$object->metadata->simpay_form_id
	);

	// Set "To" address to the Customer's email address.
	$to = $object->customer->email;

	// Set "Subject" to the stored subject.
	$subject = Emails\format_subject_for_mode(
		$email->get_setting( 'subject' ),
		$payment_confirmation_data['form']->is_livemode()
	);

	if ( empty( $payment_confirmation_data ) ) {
		$body = Payments\Payment_Confirmation\get_error();
	} else {
		// Retrieved the stored body content.
		$type = 'one_time';

		if ( is_a( $object, '\SimplePay\Vendor\Stripe\Subscription' ) ) {
			$type = 'subscription';

			if ( 'trialing' === $object->status ) {
				$type = 'trial';
			}
		}

		$body = $email->get_body_setting_or_default( $type );

		// Parse smart tags.
		$body = Payments\Payment_Confirmation\Template_Tags\parse_content(
			$body,
			$payment_confirmation_data
		);
	}

	// Set "Message" to the the parsed body or error.
	$body = Emails\format_body( $body );

	return Emails\Mailer\send( $email, $to, $subject, $body );
}

/**
 * Retrieves a list of Customers via the
 * `simpay_resend_payment_confirmation_results` AJAX request.
 *
 * @since 4.0.0
 */
function get_customers_ajax() {
	$error = array(
		'message' => esc_html__( 'Unable to retrieve results.', 'simple-pay' ),
	);

	// Verify permissions.
	if ( ! current_user_can( 'manage_options' ) ) {
		return wp_send_json_error( $error );
	}

	// Verify nonce.
	if (
		! isset( $_POST['nonce'] ) ||
		! wp_verify_nonce( $_POST['nonce'], 'simpay-resend-payment-confirmation' )
	) {
		return wp_send_json_error( $error );
	}

	// "Email" to search.
	$email = isset( $_POST['email'] )
		? sanitize_text_field( $_POST['email'] )
		: '';

	// Find any Customers.
	$customers = get_customers( $email );

	if ( ! empty( $customers ) ) {
		wp_send_json_success(
			array(
				'customers' => $customers,
			)
		);
	} else {
		wp_send_json_error(
			array(
				'message' => esc_html__( 'No results found.', 'simple-pay' ),
			)
		);
	}
}
add_action(
	'wp_ajax_simpay_resend_payment_confirmation_results',
	__NAMESPACE__ . '\\get_customers_ajax'
);

/**
 * Sends a Payment Confirmation email via the `simpay_resend_payment_confirmation`
 * AJAX request.
 *
 * @since 4.0.0
 *
 * @throws \Exception If data cannot be found, or is invalid.
 */
function resend_ajax() {
	$error = array(
		'message' => esc_html__( 'Unable to resend payment receipt.', 'simple-pay' ),
	);

	// Verify permissions.
	if ( ! current_user_can( 'manage_options' ) ) {
		return wp_send_json_error( $error );
	}

	// Verify nonce.
	if (
		! isset( $_POST['nonce'] ) ||
		! wp_verify_nonce( $_POST['nonce'], 'simpay-resend-payment-confirmation' )
	) {
		return wp_send_json_error( $error );
	}

	// Customer ID.
	$customer = isset( $_POST['customer'] )
		? sanitize_text_field( $_POST['customer'] )
		: '';

	$api_args = array(
		'api_key' => simpay_get_secret_key(),
	);

	// Find the Customer's Subscription or Payment.
	try {
		// Shim an event.
		$event = new \stdClass();

		// Determine if the Customer has a Subscription.
		$subscriptions = API\Subscriptions\all(
			array(
				'customer' => $customer,
				'expand'   => array(
					'data.customer',
				),
			),
			$api_args
		);

		// Subscription.
		if ( ! empty( $subscriptions->data ) ) {
			$object = $subscriptions->first();

			if ( in_array( $object->status, array( 'incomplete', 'incomplete_expired' ), true ) ) {
				throw new \Exception(
					esc_html__( 'No subscription attached to customer.', 'simple-pay' )
				);
			}

			// Find a Payment.
		} else {
			$paymentintents = Payments\PaymentIntent\all(
				array(
					'customer' => $customer,
					'expand'   => array(
						'data.customer',
					),
				),
				$api_args
			);

			$object = $paymentintents->first();

			if ( 'succeeded' !== $object->status ) {
				throw new \Exception(
					esc_html__( 'No succesful payment attached to customer.', 'simple-pay' )
				);
			}
		}

		if ( null === $object ) {
			throw new \Exception(
				esc_html__( 'No payment or subscription attached to customer.', 'simple-pay' )
			);
		}

		if ( ! isset( $object->metadata->simpay_form_id ) ) {
			throw new \Exception(
				esc_html__( 'Payment or subscription not created by WP Simple Pay.', 'simple-pay' )
			);
		}

		$send = resend_for_object( $object );
	} catch ( \Exception $e ) {
		$error = array(
			'message' => $e->getMessage(),
		);

		$send = false;
	}

	if ( true === $send ) {
		return wp_send_json_success(
			array(
				'message' => esc_html(
					sprintf(
						/* translators: %s Payment receipt email address. */
						__(
							'Payment receipt sent to %s',
							'simple-pay'
						),
						$object->customer->email
					)
				),
			)
		);
	} else {
		return wp_send_json_error( $error );
	}
}
add_action(
	'wp_ajax_simpay_resend_payment_confirmation',
	__NAMESPACE__ . '\\resend_ajax'
);
