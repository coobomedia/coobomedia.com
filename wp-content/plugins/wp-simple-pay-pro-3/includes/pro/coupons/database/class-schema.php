<?php
/**
 * Coupons: Database schema
 *
 * @package SimplePay
 * @subpackage Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.3.0
 */

namespace SimplePay\Pro\Coupons\Database;

use SimplePay\Vendor\BerlinDB\Database\Schema as BerlinDBSchema;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schema class.
 *
 * @since 4.3.0
 */
final class Schema extends BerlinDBSchema {

	/**
	 * {@inheritdoc}
	 */
	public $columns = array(

		// id.
		array(
			'name'     => 'id',
			'type'     => 'bigint',
			'length'   => '20',
			'unsigned' => true,
			'extra'    => 'auto_increment',
			'primary'  => true,
			'sortable' => true,
		),

		// object_id_live.
		array(
			'name'       => 'object_id_live',
			'type'       => 'varchar',
			'length'     => '255',
			'default'    => null,
			'allow_null' => true,
		),

		// object_id_test.
		array(
			'name'       => 'object_id_test',
			'type'       => 'varchar',
			'length'     => '255',
			'default'    => null,
			'allow_null' => true,
		),

		// object_modified_live.
		array(
			'name'       => 'object_modified_live',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true,
			'allow_null' => true,
		),

		// object_modified_test.
		array(
			'name'       => 'object_modified_test',
			'type'       => 'datetime',
			'default'    => null,
			'date_query' => true,
			'sortable'   => true,
			'allow_null' => true,
		),

		// name.
		array(
			'name'     => 'name',
			'type'     => 'varchar',
			'length'   => '255',
			'sortable' => true,
			'validate' => '\SimplePay\Pro\Coupons\sanitize_coupon_name',
		),

		// percent_off.
		array(
			'name'       => 'percent_off',
			'type'       => 'decimal',
			'length'     => '5,2',
			'default'    => null,
			'sortable'   => true,
			'allow_null' => true,
		),

		// amount_off.
		array(
			'name'       => 'amount_off',
			'type'       => 'bigint',
			'length'     => '20',
			'default'    => null,
			'sortable'   => true,
			'allow_null' => true,
		),

		// currency.
		array(
			'name'       => 'currency',
			'type'       => 'varchar',
			'length'     => '3',
			'default'    => null,
			'sortable'   => true,
			'allow_null' => true,
		),

		// duration.
		array(
			'name'     => 'duration',
			'type'     => 'varchar',
			'length'   => '255',
			'default'  => 'forever',
			'sortable' => false,
		),

		// duration_in_months.
		array(
			'name'       => 'duration_in_months',
			'type'       => 'smallint',
			'length'     => '4',
			'default'    => null,
			'sortable'   => true,
			'allow_null' => true,
		),

		// max_redemptions.
		array(
			'name'       => 'max_redemptions',
			'type'       => 'smallint',
			'length'     => '4',
			'default'    => null,
			'sortable'   => true,
			'allow_null' => true,
		),

		// redeem_by.
		array(
			'name'       => 'redeem_by',
			'type'       => 'datetime',
			'default'    => null,
			'sortable'   => true,
			'allow_null' => true,
			'validate'   => '\SimplePay\Pro\Coupons\sanitize_timestamp_to_date',
		),

		// applies_to_forms.
		array(
			'name'       => 'applies_to_forms',
			'type'       => 'string',
			'default'    => null,
			'sortable'   => false,
			'allow_null' => true,
		),

		// active.
		array(
			'name'       => 'active',
			'type'       => 'tinyint',
			'length'     => 1,
			'default'    => 1,
			'sortable'   => false,
			'allow_null' => false,
		),

	);
}
