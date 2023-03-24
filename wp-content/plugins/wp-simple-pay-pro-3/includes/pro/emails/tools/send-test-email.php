<?php
/**
 * Tools: Send Test Email
 *
 * @package SimplePay\Pro\Emails\Tools\Send_Test_Email
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Emails\Tools\Send_Test_Email;

use SimplePay\Core\Utils;
use SimplePay\Core\Payments\Payment_Confirmation;
use SimplePay\Pro\Emails;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Outputs the "Send Test Email" form.
 *
 * @since 4.0.0
 */
function output() {
	// Active emails.
	$emails = Utils\get_collection( 'emails' );

	if ( false === $emails ) {
		return;
	}

	$payment_confirmation_enabled = $emails->get_item( 'payment-confirmation' );

	$nonce = wp_create_nonce( 'simpay-test-email' );
	?>

	<div
		class="simpay-send-test-email card"
		data-nonce="<?php echo esc_attr( $nonce ); ?>"
	>
		<h2><?php esc_html_e( 'Preview Email', 'simple-pay' ); ?></h2>

		<p style="margin-top: 0;">
			<?php
			esc_html_e(
				'Preview email tags and formatting. Complete a purchase in Test Mode to verify deliverability.',
				'simple-pay'
			);
			?>
		</p>

		<p class="simpay-send-test-email__email">
			<label for="send-test-email-email">
				<?php esc_html_e( 'Email', 'simple-pay' ); ?>:
			</label><br />

			<select id="send-test-email-email">
			<?php
			/* @var $emails \SimplePay\Pro\Emails\Email[] */
			foreach ( $emails->get_items() as $email ) :
				if ( false === $email->is_enabled() ) :
					continue;
				endif;

				printf(
					'<option value="%s">%s</option>',
					esc_attr( $email->id ),
					esc_html( $email->label )
				);
			endforeach;
			?>
			</select>
		</p>

		<?php
		if (
			true === simpay_subscriptions_enabled() &&
			(
				false !== $payment_confirmation_enabled &&
				true === $payment_confirmation_enabled->is_enabled()
			)
		) :
			?>
		<p class="simpay-send-test-email__type">
			<label for="send-test-email-type">
				<?php esc_html_e( 'Type', 'simple-pay' ); ?>:
			</label><br />

			<select id="send-test-email-type" >
				<option value="one_time">
					<?php esc_html_e( 'One Time', 'simple-pay' ); ?>
				</option>
				<option value="subscription">
					<?php esc_html_e( 'Subscription', 'simple-pay' ); ?>
				</option>
				<option value="trial">
					<?php esc_html_e( 'Subscription with Trial', 'simple-pay' ); ?>
				</option>
			</select>
		</p>
		<?php endif; ?>

		<p class="simpay-send-test-email__to">
			<label for="send-test-email-to">
				<?php esc_html_e( 'Send To', 'simple-pay' ); ?>:
			</label><br />

			<input
				type="text"
				id="send-test-email-to"
				class="regular-text"
				value="<?php echo esc_attr( get_bloginfo( 'admin_email' ) ); ?>"
			/>
		</p>

		<p>
			<button class="simpay-send-test-email__button button button-primary">
				<?php esc_html_e( 'Send Preview Email', 'simple-pay' ); ?>
			</button>
		</p>
	</div>

	<?php
}

/**
 * Handles sending a sample email via AJAX.
 *
 * @since 4.0.0
 */
function send_test_email_ajax() {
	$error = array(
		'message' => esc_html__( 'Unable to send test email', 'simple-pay' ),
	);

	// Verify permissions.
	if ( ! current_user_can( 'manage_options' ) ) {
		return wp_send_json_error( $error );
	}

	// Verify nonce.
	if (
		! isset( $_POST['nonce'] ) ||
		! wp_verify_nonce( $_POST['nonce'], 'simpay-test-email' )
	) {
		return wp_send_json_error( $error );
	}

	// Find the registered email.
	$email = isset( $_POST['email'] )
		? sanitize_text_field( $_POST['email'] )
		: '';

	$email = Emails\get( $email );

	if ( false === $email ) {
		return wp_send_json_error( $error );
	}

	// To.
	$to = isset( $_POST['to'] )
		? sanitize_text_field( $_POST['to'] )
		: '';

	// Subject.
	$subject = sprintf(
		/* translators: %s Email subject */
		esc_html__( '[Test Email] %s', 'simple-pay' ),
		$email->get_setting( 'subject' )
	);

	// Body.
	$type = isset( $_POST['type'] ) && ! empty( $_POST['type'] )
		? sanitize_text_field( $_POST['type'] )
		: 'one_time';

	switch ( $email->id ) {
		case 'payment-confirmation':
			$body = $email->get_body_setting_or_default( $type );
			break;
		default:
			$body = $email->get_setting( 'body' );
	}

	// Overrides smart tag values with sample data.
	Emails\Mailer\set_sample_template_tags();

	// Parse smart tags.
	$body = Payment_Confirmation\Template_Tags\parse_content(
		$body,
		array()
	);

	// Set "Message" to the the parsed body.
	$body = Emails\format_body( $body );

	$send = Emails\Mailer\send( $email, $to, $subject, $body );

	if ( true === $send ) {
		return wp_send_json_success(
			array(
				'message' => esc_html(
					sprintf(
						/* translators: %s Test email address. */
						__(
							'Test email sent to %s',
							'simple-pay'
						),
						$to
					)
				),
			)
		);
	} else {
		return wp_send_json_error( $error );
	}
}
add_action(
	'wp_ajax_simpay_send_test_email',
	__NAMESPACE__ . '\\send_test_email_ajax'
);
