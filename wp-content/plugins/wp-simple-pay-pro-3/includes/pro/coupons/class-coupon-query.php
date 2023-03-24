<?php
/**
 * Coupons: Query
 *
 * @package SimplePay
 * @subpackage Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.3.0
 */

namespace SimplePay\Pro\Coupons;

use SimplePay\Core\Repository\BerlinDbRepository;
use SimplePay\Pro\Coupons\Database\Query as Coupon_Query_Base;
use SimplePay\Pro\Coupons\Stripe_Sync\Stripe_Object_Query_Trait;
use SimplePay\Pro\Coupons\Stripe_Sync\Stripe_Object_Sync_Trait;

/**
 * Coupon_Query class.
 *
 * @since 4.3.0
 */
class Coupon_Query extends BerlinDbRepository {

	use Stripe_Object_Query_Trait, Stripe_Object_Sync_Trait;

	/**
	 * Coupon_Query
	 *
	 * @since 4.3.0
	 *
	 * @param bool   $is_livemode Determines which mode requests are currently being made in.
	 * @param string $api_key The API key required to make changes in the current mode.
	 */
	public function __construct( $is_livemode, $api_key ) {
		$this->is_livemode = (bool) $is_livemode;
		$this->api_key     = (string) $api_key;

		parent::__construct(
			Coupon::class,
			Coupon_Query_Base::class
		);
	}

	/**
	 * Retrieves a coupon given a name.
	 *
	 * @since 4.3.0
	 *
	 * @param string $name Coupon name.
	 * @return \SimplePay\Pro\Coupons\Coupon|null
	 */
	public function get_by_name( $name ) {
		$item = $this->get_by( 'name', $name );

		if ( null === $item ) {
			return null;
		}

		// Double check that the name is case sensitive.
		if ( $name !== $item->name ) {
			return null;
		}

		return $this->get_synced_item( $item->id );
	}

}
