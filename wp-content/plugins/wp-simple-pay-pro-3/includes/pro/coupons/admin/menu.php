<?php
/**
 * Coupons: Admin menu
 *
 * @package SimplePay
 * @subpackage Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.3.0
 */

namespace SimplePay\Pro\Coupons\Admin;

/**
 * Adds the "Coupons" submenu page.
 *
 * Added at priority 0 to ensure it is inserted directly after the automatically added
 * custom post type menu items.
 *
 * @since 4.3.0
 */
function add_menu_item() {
	add_submenu_page(
		'edit.php?post_type=simple-pay',
		__( 'Coupons', 'simple-pay' ),
		__( 'Coupons', 'simple-pay' ),
		'manage_options',
		'simpay_coupons',
		__NAMESPACE__ . '\render_page'
	);
}
add_action( 'admin_menu', __NAMESPACE__ . '\\add_menu_item', 0 );
