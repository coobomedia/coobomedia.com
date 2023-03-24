<?php
/**
 * Payments: Product
 *
 * @package SimplePay\Pro\Payments\Plan
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Pro\Payments\Product;

use SimplePay\Core\API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves a Product.
 *
 * @since 3.8.0
 * @since 4.1.0 Deprecated.
 *
 * @param string|array $product Product ID or {
 *   Arguments used to retrieve a Product.
 *
 *   @type string $id Product ID.
 * }
 * @param array        $api_request_args {
 *   Additional request arguments to send to the Stripe API when making a request.
 *
 *   @type string $api_key API Secret Key to use.
 * }
 * @return \SimplePay\Vendor\Stripe\Product
 */
function retrieve( $product, $api_request_args = array() ) {
	_deprecated_function(
		__FUNCTION__,
		'4.1.0',
		'\SimplePay\Core\API\Products\retrieve'
	);

	return API\Products\retrieve( $product, $api_request_args );
}
