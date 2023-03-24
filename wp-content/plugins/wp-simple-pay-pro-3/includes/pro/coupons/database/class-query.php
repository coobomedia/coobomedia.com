<?php
/**
 * Coupons: Database query
 *
 * @package SimplePay
 * @subpackage Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.3.0
 */

namespace SimplePay\Pro\Coupons\Database;

use SimplePay\Vendor\BerlinDB\Database\Query as BerlinDBQuery;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query class.
 *
 * @since 4.3.0
 */
class Query extends BerlinDBQuery {

	/**
	 * {@inheritdoc}
	 */
	protected $prefix = 'wpsp';

	/**
	 * {@inheritdoc}
	 */
	protected $table_name = 'coupons';

	/**
	 * {@inheritdoc}
	 */
	protected $table_alias = 'cpn';

	/**
	 * {@inheritdoc}
	 */
	protected $table_schema = '\\SimplePay\\Pro\\Coupons\\Database\\Schema';

	/**
	 * {@inheritdoc}
	 */
	protected $item_name = 'coupon';

	/**
	 * {@inheritdoc}
	 */
	protected $item_name_plural = 'coupons';

	/**
	 * {@inheritdoc}
	 */
	protected $item_shape = '\stdClass';

	/**
	 * {@inheritdoc}
	 */
	protected $cache_group = 'coupons';

}
