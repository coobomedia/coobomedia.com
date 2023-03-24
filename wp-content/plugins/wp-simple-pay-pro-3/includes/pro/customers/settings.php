<?php
/**
 * Customers: Settings
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Customers;

use SimplePay\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers settings section.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Section_Collection $sections Section collection.
 */
function register_sections( $sections ) {
	if ( false === simpay_subscriptions_enabled() ) {
		return;
	}

	$sections->add(
		new Settings\Section(
			array(
				'id'       => 'customers',
				'label'    => esc_html_x(
					'Subscription Management',
					'settings subsection label',
					'simple-pay'
				),
				'priority' => 70,
			)
		)
	);
}
add_action( 'simpay_register_settings_sections', __NAMESPACE__ . '\\register_sections' );
