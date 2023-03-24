<?php
/**
 * Emails: Tools
 *
 * @package SimplePay\Pro\Emails
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Emails\Tools;

use SimplePay\Core\Utils;
use SimplePay\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers settings subsection.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Subsections_Collection $subsections Subsections collection.
 */
function register_subsections( $subsections ) {
	// Active emails.
	$emails = Utils\get_collection( 'emails' );

	if ( false === $emails ) {
		return;
	}

	if ( true === $emails->has_enabled_emails() ) {
		$subsections->add(
			new Settings\Subsection(
				array(
					'id'       => 'emails-tools',
					'section'  => 'emails',
					'label'    => '<span class="dashicons dashicons-admin-generic"></span>' . esc_html_x(
						'Tools',
						'settings subsection label',
						'simple-pay'
					),
					'priority' => 99,
				)
			)
		);
	}
}
add_action( 'simpay_register_settings_subsections', __NAMESPACE__ . '\\register_subsections' );

/**
 * Outputs "Tools" subsection.
 *
 * @since 4.0.0
 */
function output() {
	$subsection = ! empty( $_GET['subsection'] )
		? sanitize_key( $_GET['subsection'] )
		: '';

	if ( 'emails-tools' !== $subsection ) {
		return;
	}

	add_filter( 'simpay_admin_page_settings_emails_submit', '__return_false' );

	// Resend Payment Confirmation.
	Resend_Payment_Confirmation\output();

	// Send Test Email.
	Send_Test_Email\output();
}
add_action( 'simpay_admin_page_settings_emails_end', __NAMESPACE__ . '\\output' );
