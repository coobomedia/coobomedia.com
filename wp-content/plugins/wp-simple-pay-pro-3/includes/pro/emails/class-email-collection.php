<?php
/**
 * Emails: Collection
 *
 * @package SimplePay\Pro\Emails
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Emails;

use SimplePay\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email_Collection class.
 *
 * @since 4.0.0
 */
class Email_Collection extends Utils\Collection {

	/**
	 * Adds an Email to the collection.
	 *
	 * @since 4.0.0
	 *
	 * @param \SimplePay\Pro\Emails\Email $email Email arguments.
	 * @return \WP_Error|true True on successful addition, otherwise a \WP_Error object.
	 */
	public function add( $email ) {
		// Ensure a valid Section.
		if ( ! $email instanceof \SimplePay\Pro\Emails\Email ) {
			return new \WP_Error(
				'invalid_email',
				__( 'Invalid email.', 'simple-pay' )
			);
		}

		// Validate ID.
		if ( empty( $email->id ) ) {
			return new \WP_Error(
				'invalid_email_id',
				__( 'Parameter <code>id</code> is required when registering an Email.', 'simple-pay' )
			);
		}

		return $this->add_item( $email->id, $email );
	}

	/**
	 * Determines if at least one registered email is active and enabled.
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public function has_enabled_emails() {
		return ! empty(
			array_filter(
				$this->get_items(),
				function( $email ) {
					return $email->is_enabled();
				}
			)
		);
	}

}
