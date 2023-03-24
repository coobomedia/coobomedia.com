<?php
/**
 * Plugin Name: WP Simple Pay Pro
 * Plugin URI: https://wpsimplepay.com
 * Description: Add high conversion Stripe payment and subscription forms to your WordPress site in minutes.
 * Author: WP Simple Pay
 * Author URI: https://wpsimplepay.com
 * Version: 4.6.4.1
 * Text Domain: simple-pay
 * Domain Path: /languages
 *
 * @package SimplePay
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright 2014-2022 Sandhills Development, LLC. All rights reserved.
 */

namespace SimplePay;

use SimplePay\Core\Plugin;
use SimplePay\Core\Bootstrap\Compatibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//
// Shared.
//
if ( ! defined( 'SIMPLE_PAY_STORE_URL' ) ) {
	define( 'SIMPLE_PAY_STORE_URL', 'https://wpsimplepay.com/' );
}

if ( ! defined( 'SIMPLE_PAY_ITEM_ID' ) ) {
	define( 'SIMPLE_PAY_ITEM_ID', 177993 );
}

//
// Lite/Pro-specific.
//
if ( ! defined( 'SIMPLE_PAY_VERSION' ) ) {
	define( 'SIMPLE_PAY_VERSION', '4.6.4.1' );
}

if ( ! defined( 'SIMPLE_PAY_PLUGIN_NAME' ) ) {
	define( 'SIMPLE_PAY_PLUGIN_NAME', 'WP Simple Pay Pro' );
}

if ( ! defined( 'SIMPLE_PAY_ITEM_NAME' ) ) {
	define( 'SIMPLE_PAY_ITEM_NAME', SIMPLE_PAY_PLUGIN_NAME );
}

//
// Stripe.
//
if ( ! defined( 'SIMPLE_PAY_STRIPE_API_VERSION' ) ) {
	define( 'SIMPLE_PAY_STRIPE_API_VERSION', '2020-08-27' );
}

if ( ! defined( 'SIMPLE_PAY_STRIPE_PARTNER_ID' ) ) {
	define( 'SIMPLE_PAY_STRIPE_PARTNER_ID', 'pp_partner_DKkf27LbiCjOYt' );
}

//
// Helpers.
//
if ( ! defined( 'SIMPLE_PAY_MAIN_FILE' ) ) {
	define( 'SIMPLE_PAY_MAIN_FILE', __FILE__ );
}

if ( ! defined( 'SIMPLE_PAY_URL' ) ) {
	define( 'SIMPLE_PAY_URL', plugin_dir_url( SIMPLE_PAY_MAIN_FILE ) );
}

if ( ! defined( 'SIMPLE_PAY_DIR' ) ) {
	define( 'SIMPLE_PAY_DIR', plugin_dir_path( SIMPLE_PAY_MAIN_FILE ) );
}

if ( ! defined( 'SIMPLE_PAY_INC' ) ) {
	define( 'SIMPLE_PAY_INC', plugin_dir_path( SIMPLE_PAY_MAIN_FILE ) . 'includes/' );
}

if ( ! defined( 'SIMPLE_PAY_INC_URL' ) ) {
	define( 'SIMPLE_PAY_INC_URL', plugin_dir_url( SIMPLE_PAY_MAIN_FILE ) . 'includes/' );
}

/**
 * Show warning if Lite version is active.
 *
 * @since unknown
 */
function simpay_deactivate_lite_notice() {
	?>

	<div class="notice notice-error">
		<p>
			<?php
			echo wp_kses(
				(
					'<strong> ' .
					__(
						'WP Simple Pay Lite must be deactivated to use WP Simple Pay Pro.',
						'simple-pay'
					) .
					'</strong><br />' .
					'<a href="https://docs.wpsimplepay.com/articles/upgrading-wp-simple-pay-lite-to-pro/" target="_blank" rel="noopener noreferrer">' .
					__( 'View the upgrade guide', 'simple-pay' ) .
					sprintf(
						'<span class="screen-reader-text">%s</span><span aria-hidden="true" class="dashicons dashicons-external" style="text-decoration: none;"></span>',
						esc_html__( '(opens in a new tab)', 'simple-pay' )
					) .
					'</a>'
				),
				array(
					'span'   => array(
						'class' => true,
						'style' => true,
					),
					'strong' => true,
					'br'     => true,
					'a'      => array(
						'href'   => true,
						'target' => true,
						'rel'    => true,
					),
				)
			);
			?>
		</p>
	</div>

	<?php
}

// Stop any further checks if Lite is already loaded.
if ( class_exists( 'SimplePay\Core\SimplePay' ) ) {
	add_action( 'admin_notices', __NAMESPACE__ . '\\simpay_deactivate_lite_notice' );
	return;
}

// Compatibility files.
require_once( SIMPLE_PAY_DIR . 'includes/core/bootstrap/compatibility.php' );

if ( Compatibility\server_requirements_met() ) {
	// Autoloader.
	require_once( SIMPLE_PAY_DIR . 'vendor/autoload.php' );
	require_once( SIMPLE_PAY_DIR . 'includes/core/bootstrap/autoload.php' );

	// Core & Pro main plugin files.
	require_once( SIMPLE_PAY_DIR . 'includes/core/class-simplepay.php' );
	require_once( SIMPLE_PAY_DIR . 'includes/pro/class-simplepaypro.php' );

	// New plugin container.
	$plugin = new Plugin( __FILE__ );
	$plugin->load();
} else {
	Compatibility\show_admin_notices();
}
