<?php
/**
 * License management
 *
 * This is a temporary file to ensure some license-specific functionality is also
 * available on the frontend when the context shifts unexpectedly.
 *
 * https://github.com/awesomemotive/wp-simple-pay-pro/issues/1773
 *
 * @package SimplePay
 * @subpackage Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.3.0
 */

namespace SimplePay\Pro\License_Management;

/**
 * Load the EDD SL Updater class if a key is saved
 *
 * @since unknown
 */
function load_updater() {
	// To support auto-updates, this needs to run during the wp_version_check cron job for privileged users.
	$doing_cron = defined( 'DOING_CRON' ) && DOING_CRON;

	if ( ! current_user_can( 'manage_options' ) && ! $doing_cron ) {
		return;
	}

	// Load custom updater file.
	require_once trailingslashit( SIMPLE_PAY_INC ) . 'pro/class-edd-sl-plugin-updater.php';

	$license = simpay_get_license();

	new \Simple_Pay_EDD_SL_Plugin_Updater(
		SIMPLE_PAY_STORE_URL,
		SIMPLE_PAY_MAIN_FILE,
		array(
			'version' => SIMPLE_PAY_VERSION,
			'license' => $license->get_key(),
			'item_id' => SIMPLE_PAY_ITEM_ID,
			'author'  => 'Sandhills Development, LLC',
			'beta'    => false,
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\\load_updater', 0 );
