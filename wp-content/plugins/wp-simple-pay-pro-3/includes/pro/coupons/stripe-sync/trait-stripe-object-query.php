<?php
/**
 * Coupons: Stripe object query
 *
 * Ensures Stripe objects stay synced when querying internal records by overloading
 * standard query methods and performing additional actions.
 *
 * Warning: This is in an unstable state and namespace. Do not extend or rely on this.
 *
 * @package SimplePay
 * @subpackage Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.3.0
 */

namespace SimplePay\Pro\Coupons\Stripe_Sync;

/**
 * Stripe_Object_Query trait.
 *
 * @since 4.3.0
 */
trait Stripe_Object_Query_Trait {

	/**
	 * Retrieves a single synced item.
	 *
	 * @since 4.3.0
	 *
	 * @param int $id Item ID.
	 * @return null|\SimplePay\Pro\Coupons\Coupon
	 */
	public function get_synced_item( $id ) {
		$item = parent::get( $id );

		if ( null === $item ) {
			return null;
		}

		return $this->sync_on_get( $item );
	}

	/**
	 * Retrieves a single synced item by a column.
	 *
	 * @since 4.3.0
	 *
	 * @param string $column_name Colum name.
	 * @param mixed  $column_valvue Column value.
	 * @return null|\SimplePay\Pro\Coupons\Coupon
	 */
	public function get_synced_item_by( $column_name, $column_value ) {
		$item = parent::get_by( $column_name, $column_value );

		if ( null === $item ) {
			return null;
		}

		return $this->sync_on_get( $item );
	}

	/**
	 * Adds a single synced item.
	 *
	 * @since 4.3.0
	 *
	 * @param array $data Item data.
	 * @return null|\SimplePay\Pro\Coupons\Coupon
	 */
	public function add_synced_item( $data ) {
		$item = parent::add( $data );

		if ( null === $item ) {
			return null;
		}

		return $this->sync_on_add( $item );
	}

	/**
	 * Updates a single synced item.
	 *
	 * @since 4.3.0
	 *
	 * @param int   $id Item ID.
	 * @param array $data Item data.
	 * @return null|\SimplePay\Pro\Coupons\Coupon
	 */
	public function update_synced_item( $id, $data ) {
		$item = parent::update( $id, $data );

		if ( null === $item ) {
			return null;
		}

		return $this->sync_on_update( $item );
	}

	/**
	 * Deletes a single synced item.
	 *
	 * @since 4.3.0
	 *
	 * @param int $id Item ID.
	 * @return null|\SimplePay\Pro\Coupons\Coupon
	 */
	public function delete_synced_item( $id ) {
		$item = parent::get( $id );

		if ( null === $item ) {
			return null;
		}

		// Determine keys for current mode.
		$object_id_key = $this->is_livemode
			? 'object_id_live'
			: 'object_id_test';

		$object_modified_key = $this->is_livemode
			? 'object_modified_live'
			: 'object_modified_test';

		// If keys for current mode are already empty, do nothing.
		if (
			null === $item->$object_id_key &&
			null === $item->$object_modified_key
		) {
			return null;
		}

		// Delete the item from Stripe.
		$item = $this->delete_on_delete( $item );

		// Clear Stripe object cache so dynamic values are available.
		delete_transient( 'simpay_stripe_' . $item->$object_id_key );

		// Update the internal record that it has been deleted in this mode.
		$item = parent::update(
			$id,
			array(
				$object_id_key       => null,
				$object_modified_key => null,
				'active'             => 0,
			)
		);

		if ( null === $item ) {
			return null;
		}

		// If all modes have been deleted, delete the internal record.
		if (
			null === $item->object_id_live &&
			null === $item->object_modified_live &&
			null === $item->object_id_test &&
			null === $item->object_modified_test
		) {
			return parent::delete( $item->id );
		}

		return $item;
	}

	/**
	 * Removes inactive results from the query.
	 *
	 * @since 4.3.0
	 *
	 * @param array $args Query arguments.
	 * @return SimplePay\Pro\Coupons\Coupon[] List of items or number of results if counting.
	 */
	public function query( $args = array() ) {
		$items = parent::query( $args );

		// Ensure all items are synced on query.
		$items = array_map(
			function( $item ) {
				return $this->get_synced_item( $item->id );
			},
			$items
		);

		// Remove invalid synced items.
		return array_filter(
			$items,
			function( $item ) {
				return (
					$item instanceof Abstract_Synced_Stripe_Object &&
					true === $item->active
				);
			}
		);
	}

	/**
	 * Ensures counts only include active results. Inactive results are removed
	 * when queried.
	 *
	 * @since 4.3.0
	 *
	 * @param array $args Query arguments.
	 * @return int The number of results.
	 */
	public function count( $args = array() ) {
		$args['active'] = true;

		return parent::count( $args );
	}

}
