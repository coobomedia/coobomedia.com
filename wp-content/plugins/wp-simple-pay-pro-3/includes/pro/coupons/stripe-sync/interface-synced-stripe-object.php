<?php
/**
 * Coupons: Synced Stripe object interface
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
 * Synced_Stripe_Object interface.
 *
 * @since 4.3.0
 */
interface Synced_Stripe_Object_Interface {

	/**
	 * Returns a callback used to retrieve an object in Stripe.
	 *
	 * This should be a API wrapper function located in `includes/core/api`.
	 *
	 * @see \SimplePay\Core\API
	 *
	 * @since 4.3.0
	 *
	 * @return array
	 */
	public function get_retrievable_callback();

	/**
	 * Returns a list of object properties and respective values that can be
	 * used to create an object in Stripe.
	 *
	 * @since 4.3.0
	 *
	 * @return array
	 */
	public function get_creatable_props();

	/**
	 * Returns a callback used to create an object in Stripe.
	 *
	 * This should be a API wrapper function located in `includes/core/api`.
	 *
	 * @see \SimplePay\Core\API
	 *
	 * @since 4.3.0
	 *
	 * @return callable
	 */
	public function get_creatable_callback();

	/**
	 * Returns a list of object properties and respective values that can be
	 * used to update an object in Stripe.
	 *
	 * @since 4.3.0
	 *
	 * @return array
	 */
	public function get_editable_props();

	/**
	 * Returns a callback used to edit an object in Stripe.
	 *
	 * This should be a API wrapper function located in `includes/core/api`.
	 *
	 * @see \SimplePay\Core\API
	 *
	 * @since 4.3.0
	 *
	 * @return callable
	 */
	public function get_editable_callback();

	/**
	 * Returns a callback used to delete an object in Stripe.
	 *
	 * This should be a API wrapper function located in `includes/core/api`.
	 *
	 * @see \SimplePay\Core\API
	 *
	 * @since 4.3.0
	 *
	 * @return callable
	 */
	public function get_deletable_callback();

}
