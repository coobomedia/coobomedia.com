<?php
/**
 * Coupons: Database table
 *
 * @package SimplePay
 * @subpackage Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.3.0
 */

namespace SimplePay\Pro\Coupons\Database;

use SimplePay\Vendor\BerlinDB\Database\Table as BerlinDBTable;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Table class.
 *
 * @since 4.3.0
 */
final class Table extends BerlinDBTable {

	/**
	 * {@inheritdoc}
	 */
	protected $prefix = 'wpsp';

	/**
	 * {@inheritdoc}
	 */
	protected $name = 'coupons';

	/**
	 * {@inheritdoc}
	 *
	 * YYYYMMDDXXXX
	 */
	protected $version = 202206020001;

	/**
	 * {@inheritdoc}
	 */
	protected $schema = __NAMESPACE__ . '\\Schema';

	/**
	 * {@inheritdoc}
	 */
	protected $upgrades = array(
		'202206020001' => 202206020001,
	);

	/**
	 * {@inheritdoc}
	 */
	protected function set_schema() {
		$this->schema = "
			id bigint(20) unsigned not null auto_increment,
			object_id_live varchar(255) default null,
			object_id_test varchar(255) default null,
			object_modified_live datetime default null,
			object_modified_test datetime default null,
			name varchar(255) not null,
			percent_off decimal(5,2) default null,
			amount_off bigint(20) default null,
			currency varchar(3) default null,
			duration varchar(255) default 'forever',
			duration_in_months smallint(4) default null,
			max_redemptions bigint(20) default null,
			redeem_by datetime default null,
			applies_to_forms longtext default null,
			active tinyint(1) default 1,
			primary key (id),
			KEY object_id_live (object_id_live(255)),
			KEY object_id_test (object_id_test(255))";
	}

	/**
	 * Upgrade to version 202206020001.
	 *  - Change type and length of column `percent_off` to `decimal(5,2)`.
	 *
	 * @since 4.4.7
	 *
	 * @return bool
	 */
	protected function __202206020001() {
		$this->get_db()->query( "ALTER TABLE {$this->table_name} MODIFY COLUMN `percent_off` decimal(5,2) default null;" );

		return $this->is_success( true );
	}

}
