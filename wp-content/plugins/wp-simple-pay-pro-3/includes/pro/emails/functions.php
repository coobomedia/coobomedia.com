<?php
/**
 * Emails: Functions
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Emails;

use SimplePay\Core\Utils;
use SimplePay\Pro\Webhooks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves a registered Email.
 *
 * @since 4.0.0
 *
 * @param string $email_id Registered email ID.
 * @return \SimplePay\Pro\Emails\Email|false
 */
function get( $email_id ) {
	$emails = Utils\get_collection( 'emails' );

	// Registered emails cannot be found, do nothing.
	if ( false === $emails ) {
		return false;
	}

	return $emails->get_item( $email_id );
}

/**
 * Returns the content type used to send HTML emails.
 *
 * @since 4.0.0
 *
 * @return string
 */
function html_content_type() {
	return 'text/html';
}

/**
 * Formats an email's message body for use in HTML.
 *
 * @since 4.0.0
 *
 * @param string $body HTML email body.
 * @return string
 */
function format_body( $body ) {
	$autop = true;

	/**
	 * Determines if an email's message body should have wpautop applied.
	 *
	 * @since 4.0.0
	 *
	 * @param bool $autop Determines if an email's message body should have
	 *                    wpautop applied.
	 */
	$autop = apply_filters( 'simpay_emails_autop', $autop );

	if ( true === $autop ) {
		$body = wpautop( $body );
	}

	return $body;
}

/**
 * Formats an email's subject based on the current Payment Mode.
 *
 * @since 4.0.0
 *
 * @param string $subject Email subject.
 * @param bool   $is_livemode If the Payment Mode is live mode or test mode.
 * @return string
 */
function format_subject_for_mode( $subject, $is_livemode ) {
	if ( true === $is_livemode ) {
		return $subject;
	}

	return sprintf(
		/* translators: %s Email subject */
		esc_html__( '[Test Mode] %s', 'simple-pay' ),
		$subject
	);
}
