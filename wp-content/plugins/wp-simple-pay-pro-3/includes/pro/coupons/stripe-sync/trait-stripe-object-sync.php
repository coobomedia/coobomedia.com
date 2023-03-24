<?php
/**
 * Coupons: Stripe object sync
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

use DateTime;
use Exception;
use SimplePay\Vendor\Stripe\StripeObject;
use SimplePay\Vendor\Stripe\Exception\ApiErrorException;

/**
 * Stripe_Object_Sync_Trait
 *
 * Helpers for keeping Stripe objects in sync with internal records.
 *
 * @since 4.3.0
 */
trait Stripe_Object_Sync_Trait {

	/**
	 * Determines which mode requests are currently being made in.
	 *
	 * @since 4.3.0
	 * @var bool
	 */
	private $is_livemode;

	/**
	 * The API key required to make changes in the current mode.
	 *
	 * @since 4.3.0
	 * @var bool
	 */
	private $api_key;

	/**
	 * Syncs a Stripe object when retrieving an internal item.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Stripe_Sync\Synced_Stripe_Object_Interface $item Internal item.
	 * @return \SimplePay\Pro\Coupons\Stripe_Sync\Synced_Stripe_Object_Interface
	 */
	private function sync_on_get( Synced_Stripe_Object_Interface $item ) {
		if ( false === $item->active ) {
			return $this->delete_synced_item( $item->id );
		}

		$object   = null;
		$modified = false;

		$has_object_in_current_mode = $this->has_object_in_current_mode( $item );
		$current_mode_modified      = $this->get_current_mode_modified_date( $item );
		$alt_mode_modified          = $this->get_alt_mode_modified_date( $item );

		// Nothing exists in the current mode, create the Stripe object.
		if ( false === $has_object_in_current_mode ) {
			try {
				$object   = $this->create_object( $item );
				$modified = true;
			} catch ( ApiErrorException $e ) {
				$code = $e->getStripeCode()
					? $e->getStripeCode()
					: $e->getError()->type;

				switch ( $code ) {
					// If the resource already exists, update the internal record.
					case 'resource_already_exists':
						$current_key = $this->get_current_mode_object_id_key();
						$alt_key     = $this->get_alt_mode_object_id_key();

						$this->update(
							$item->id,
							array(
								$current_key => $item->$alt_key,
							)
						);

						// Update internal object property.
						$item->$current_key = $item->$alt_key;

						$object   = $this->retrieve_object( $item );
						$modified = true;
						break;
					default:
						$item->error = $e;
				}
			}

			// Current mode is older than the alt mode, update the Stripe object.
		} elseif (
			true === $has_object_in_current_mode &&
			$current_mode_modified < $alt_mode_modified
		) {
			try {
				$object   = $this->update_object( $item );
				$modified = true;
			} catch ( ApiErrorException $e ) {
				switch ( $e->getStripeCode() ) {
					// If the resource can't be found, recreate it.
					case 'resource_missing':
						$object   = $this->create_object( $item );
						$modified = true;
						break;
					default:
						$item->error = $e;
				}
			}

			// Retrieve existing object.
		} else {
			try {
				// Always retrieve an uncached version. This hinders performance
				// (currently limited to 15 requests per page) but ensures switching
				// accounts or manually deleting an object in Stripe does not
				// disrupt syncing.
				$object = $this->retrieve_object( $item, false );
			} catch ( ApiErrorException $e ) {
				switch ( $e->getStripeCode() ) {
					// If the resource can't be found, recreate it.
					case 'resource_missing':
						try {
							$object   = $this->create_object( $item );
							$modified = true;

							// Item cannot be recreated for some reason, keep the internal record
							// to allow for manual deletion from UI.
						} catch ( Exception $e ) {
							// Do nothing.
						}

						break;
					default:
						$item->error = $e;
				}
			}
		}

		// Make full object available via the internal record.
		if ( $object instanceof StripeObject ) {
			$item->object = $object;

			// Update the internal record if the Stripe object was modified.
			if ( true === $modified ) {
				$item = $this->set_current_mode_as_latest( $item, $object );
			}
		}

		return $item;
	}

	/**
	 * Syncs a Stripe object when adding an internal item.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Stripe_Sync\Synced_Stripe_Object_Interface $item Internal item.
	 * @return \SimplePay\Pro\Coupons\Stripe_Sync\Synced_Stripe_Object_Interface
	 */
	private function sync_on_add( Synced_Stripe_Object_Interface $item ) {
		try {
			$object = $this->create_object( $item );

			// Make full object available via the internal record.
			if ( $object instanceof StripeObject ) {
				$item->object = $object;

				// Update the internal record if the Stripe object was modified.
				$item = $this->set_current_mode_as_latest( $item, $object );
			}

			// If something went wrong adding to Stripe remove the internal record.
		} catch ( ApiErrorException $e ) {
			$item = $this->delete( $item->id );

			// Rethrow so implementors can handle the Stripe error as well.
			throw $e;
		}

		return $item;
	}

	/**
	 * Syncs a Stripe object when updating an internal item.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Stripe_Sync\Synced_Stripe_Object_Interface $item Internal item.
	 * @return \SimplePay\Pro\Coupons\Stripe_Sync\Synced_Stripe_Object_Interface
	 */
	private function sync_on_update( Synced_Stripe_Object_Interface $item ) {
		$object = null;

		$has_object_in_current_mode = $this->has_object_in_current_mode( $item );
		$current_mode_modified      = $this->get_current_mode_modified_date( $item );
		$alt_mode_modified          = $this->get_alt_mode_modified_date( $item );

		if (
			true === $has_object_in_current_mode &&
			$current_mode_modified < $alt_mode_modified
		) {
			$object = $this->update_object( $item );
		} else {
			$object = $this->create_object( $item );
		}

		// Make full object available via the internal record.
		if ( $object instanceof StripeObject ) {
			$item->object = $object;

			// Update the internal record if the Stripe object was modified.
			$item = $this->set_current_mode_as_latest( $item, $object );
		}

		return $item;
	}

	/**
	 * Deletes a Stripe object when deleting an internal item.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Stripe_Sync\Synced_Stripe_Object_Interface $item Internal item.
	 * @return \SimplePay\Pro\Coupons\Stripe_Sync\Synced_Stripe_Object_Interface
	 */
	private function delete_on_delete( Synced_Stripe_Object_Interface $item ) {
		try {
			call_user_func(
				$item->get_deletable_callback(),
				$this->get_current_mode_object_id( $item ),
				$this->get_api_request_args()
			);
		} catch ( Exception $e ) {
			// Do nothing if deletion fails. Item was probably manually deleted
			// in Stripe.
			// @todo log?
		}

		return $item;
	}

	/**
	 * Returns the current mode's object ID.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Stripe_Sync\Synced_Stripe_Object_Interface $item Internal item.
	 * @return string|null
	 */
	private function get_current_mode_object_id( Synced_Stripe_Object_Interface $item ) {
		return $this->is_livemode
			? $item->object_id_live
			: $item->object_id_test;
	}

	/**
	 * Returns the current mode's object modification date.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Synced_Stripe_Object_Interface $item Internal item.
	 * @return \DateTime|null
	 */
	private function get_current_mode_modified_date( Synced_Stripe_Object_Interface $item ) {
		return $this->is_livemode
			? $item->object_modified_live
			: $item->object_modified_test;
	}

	/**
	 * Returns the alternative mode's object modification date.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Stripe_Sync\Synced_Stripe_Object_Interface $item Internal item.
	 * @return \DateTime|null
	 */
	private function get_alt_mode_modified_date( Synced_Stripe_Object_Interface $item ) {
		return $this->is_livemode
			? $item->object_modified_test
			: $item->object_modified_live;
	}

	/**
	 * Returns the current mode's object ID key.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	private function get_current_mode_object_id_key() {
		return $this->is_livemode
			? 'object_id_live'
			: 'object_id_test';
	}

	/**
	 * Returns the alternative mode's object ID key.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	private function get_alt_mode_object_id_key() {
		return $this->is_livemode
			? 'object_id_test'
			: 'object_id_live';
	}

	/**
	 * Returns the alternative mode's object modification date key.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	private function get_current_mode_object_modified_key() {
		return $this->is_livemode
			? 'object_modified_live'
			: 'object_modified_test';
	}

	/**
	 * Determines if an object ID is present in the current mode.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Stripe_Sync\Synced_Stripe_Object_Interface $item Internal item.
	 * @return bool
	 */
	private function has_object_in_current_mode( Synced_Stripe_Object_Interface $item ) {
		return null !== $this->get_current_mode_object_id( $item );
	}

	/**
	 * Returns Stripe API request arguments.
	 *
	 * @since 4.3.0
	 *
	 * @return array
	 */
	private function get_api_request_args() {
		return array(
			'api_key' => $this->api_key,
		);
	}

	/**
	 * Calls the internal item's Stripe object "creatable" callback.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Stripe_Sync\Synced_Stripe_Object_Interface $item Internal item.
	 * @return \SimplePay\Vendor\Stripe\StripeObject
	 * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException
	 */
	private function create_object( $item ) {
		return call_user_func(
			$item->get_creatable_callback(),
			$item->get_creatable_props(),
			$this->get_api_request_args()
		);
	}


	/**
	 * Calls the internal item's Stripe object "retrieve" callback.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Stripe_Sync\Synced_Stripe_Object_Interface $item Internal item.
	 * @param bool                                                              $cached If a cached object should be returned.
	 * @return \SimplePay\Vendor\Stripe\StripeObject
	 * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException
	 */
	private function retrieve_object( $item, $cached = false ) {
		return call_user_func(
			$item->get_retrievable_callback(),
			$this->get_current_mode_object_id( $item ),
			$this->get_api_request_args(),
			array(
				'cached' => $cached,
			)
		);
	}

	/**
	 * Calls the internal item's Stripe object "editable" callback.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Stripe_Sync\Synced_Stripe_Object_Interface $item Internal item.
	 * @return \SimplePay\Vendor\Stripe\StripeObject
	 * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException
	 */
	private function update_object( $item ) {
		return call_user_func(
			$item->get_editable_callback(),
			$this->get_current_mode_object_id( $item ),
			$item->get_editable_props(),
			$this->get_api_request_args()
		);
	}

	/**
	 * Updates the internal record's modification date for the current mode.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Stripe_Sync\Synced_Stripe_Object_Interface $item Internal item.
	 * @param \SimplePay\Vendor\Stripe\StripeObject                             $object Stripe object.
	 */
	private function set_current_mode_as_latest(
		Synced_Stripe_Object_Interface $item,
		StripeObject $object
	) {
		$id_key       = $this->get_current_mode_object_id_key();
		$modified_key = $this->get_current_mode_object_modified_key();
		$now          = date( 'Y-m-d H:i:s' );

		// Update in database.
		$this->update(
			$item->id,
			array(
				$id_key       => $object->id,
				$modified_key => $now,
			)
		);

		// Update reference.
		$item->$id_key       = $object->id;
		$item->$modified_key = new DateTime( $now );

		return $item;
	}

}
