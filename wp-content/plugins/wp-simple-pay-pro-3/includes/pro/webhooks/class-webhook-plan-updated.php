<?php
/**
 * Webhooks: Plan Updated
 *
 * @package SimplePay\Pro\Webhooks
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Pro\Webhooks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhook_Plan_Updated class.
 *
 * @since 3.8.0
 */
class Webhook_Plan_Updated extends Webhook_Base implements Webhook_Interface {

	/**
	 * Removes all Plan caches.
	 *
	 * @since 3.8.0
	 */
	public function handle() {
		global $wpdb;

		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_simpay\_plans\_%'" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_simpay\_plans\_%'" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_timeout\_simpay\_plans\_%'" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_timeout\_simpay\_plans\_%'" );
	}
}
