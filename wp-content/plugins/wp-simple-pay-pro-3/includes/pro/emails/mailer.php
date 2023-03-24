<?php
/**
 * Emails: Mailer
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Emails\Mailer;

use SimplePay\Core\Utils;
use SimplePay\Core\Payments\Payment_Confirmation;

use SimplePay\Pro\Emails;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sends an email.
 *
 * @since 4.0.0
 *
 * @link https://developer.wordpress.org/reference/functions/wp_mail/
 *
 * @param \SimplePay\Pro\Emails\Email $email Registered email.
 * @param string                      $to Valid email address(es) that comply with RFC 2822.
 * @param string                      $subject Email subject.
 * @param string                      $body Email body.
 * @return bool
 */
function send( $email, $to, $subject, $body ) {
	if ( ! $email instanceof \SimplePay\Pro\Emails\Email ) {
		return false;
	}

	$email->to      = $to;
	$email->subject = $subject;
	$email->body    = $body;

	return $email->send();
}

/**
 * Replaces registered smart tag filter callbacks with a callback that
 * supplies sample data.
 *
 * @since 4.0.0
 */
function set_sample_template_tags() {
	// Reset smart tags to use sample data.
	$tags = Payment_Confirmation\Template_Tags\get_tags( array() );

	foreach ( $tags as $tag_id ) {
		// Remove existing filters.
		remove_all_filters(
			sprintf(
				'simpay_payment_confirmation_template_tag_%s',
				$tag_id
			)
		);

		add_filter(
			sprintf(
				'simpay_payment_confirmation_template_tag_%s',
				$tag_id
			),
			__NAMESPACE__ . '\\template_tag_sample',
			10,
			3
		);
	}
}

/**
 * Replaces registered smart tags with sample data.
 *
 * @since 4.0.0
 *
 * @param string $value Default value (empty string).
 * @param array  $payment_confirmation_data (empty array).
 * @param string $tag Payment confirmation smart tag name, excluding curly braces.
 */
function template_tag_sample( $value, $payment_confirmation_data, $tag ) {
	$now = time();

	switch ( $tag ) {
		case 'charge-id':
			$value = 'pi_123';
			break;

		case 'charge-date':
			$value = get_date_from_gmt(
				date( 'Y-m-d H:i:s', $now ),
				get_option( 'date_format' )
			);
			break;

		case 'recurring-amount':
		case 'total-amount':
			$value = simpay_format_currency( 9999, 'usd' );
			break;

		case 'form-title':
		case 'company-name':
			$value = get_bloginfo( 'name' );
			break;

		case 'form-description':
		case 'item-description':
			$value = get_bloginfo( 'description' );
			break;

		case 'tax-amount':
			$value = simpay_format_currency( 999, 'usd' );
			break;

		case 'max-charges':
			$value = 10;
			break;

		case 'next-invoice-date':
		case 'trial-end-date':
			$value = get_date_from_gmt(
				date( 'Y-m-d H:i:s', ( $now + ( DAY_IN_SECONDS * 10 ) ) ),
				get_option( 'date_format' )
			);
			break;

		case 'update-payment-method-url':
			$value = add_query_arg(
				array(
					'customer_id'      => 'cus_123',
					'subscription_key' => 'sub_123',
					'form_id'          => '213',
				),
				get_bloginfo( 'url' )
			);
			break;

		default:
			$value = esc_html(
				sprintf(
					/* translators: %s Payment confirmation smart tag. Do not translate. */
					__(
						'{%s} (dynamic tags not supported in test emails)',
						'simple-pay'
					),
					$tag
				)
			);
	}

	/**
	 * Filters the value of the smart tag for use in sample data.
	 *
	 * @since 4.0.0
	 *
	 * @param string $value Default value (empty string).
	 * @param array  $payment_confirmation_data (empty array).
	 * @param string $tag Payment confirmation smart tag name, excluding curly braces.
	 */
	$value = apply_filters(
		sprintf( 'simpay_payment_confirmation_sample_template_tag_%s', $tag ),
		$value,
		$payment_confirmation_data,
		$tag
	);

	return $value;
}
