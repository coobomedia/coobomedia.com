<?php
/**
 * Post Types: Simple Pay
 *
 * @package SimplePay\Pro\Post_Types\Simple_Pay
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Pro\Post_Types\Simple_Pay;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Removes the "Upgrade" link.
 *
 * @since 3.8.0
 */
function remove_upgrade_menu_item() {
	global $submenu;

	unset( $submenu['edit.php?post_type=simple-pay'][99] );
}
add_action( 'admin_menu', __NAMESPACE__ . '\\remove_upgrade_menu_item', 100 );
