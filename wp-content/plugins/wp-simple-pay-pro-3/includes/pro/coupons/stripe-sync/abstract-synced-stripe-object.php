<?php
/**
 * Coupons: Synced Stripe object abstract
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

/**
 * Abstract_Synced_Stripe_Object
 *
 * @since 4.3.0
 */
abstract class Abstract_Synced_Stripe_Object implements Synced_Stripe_Object_Interface {

	/**
	 * Syncing error.
	 *
	 * @since 4.3.0
	 * @var \SimplePay\Vendor\Stripe\Exception\ApiErrorException|null
	 */
	public $error;

	/**
	 * Stripe object ID in live mode.
	 *
	 * @since 4.3.0
	 * @var int
	 */
	public $object_id_live;

	/**
	 * Stripe object ID in test mode.
	 *
	 * @since 4.3.0
	 * @var int
	 */
	public $object_id_test;

	/**
	 * Stripe object modification datetime in live mode.
	 *
	 * @since 4.3.0
	 * @var string|\DateTime
	 */
	public $object_modified_live;

	/**
	 * Stripe object modification datetime in test mode.
	 *
	 * @since 4.3.0
	 * @var string|\DateTime
	 */
	public $object_modified_test;

	/**
	 * Abstract_Synced_Stripe_Object.
	 *
	 * @since 4.3.0
	 */
	public function __construct() {
		if ( isset( $this->object_modified_live ) ) {
			$this->object_modified_live = new DateTime(
				$this->object_modified_live
			);
		}

		if ( isset( $this->object_modified_test ) ) {
			$this->object_modified_test = new DateTime(
				$this->object_modified_test
			);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_retrievable_callback();

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_creatable_props();

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_creatable_callback();

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_editable_props();

	/**
	 * {@inheritdoc}
	 */
	abstract public function get_editable_callback();

	/**
	 * Returns the synced object's error message.
	 *
	 * @since 4.3.0
	 *
	 * @return string|null
	 */
	public function get_error_message() {
		return $this->error ? $this->error->getMessage() : null;
	}

	/**
	 * Returns the synced object's error code.
	 *
	 * @since 4.3.0
	 *
	 * @return string|null
	 */
	public function get_error_code() {
		// Use a Stripe error code if available.
		$code = $this->error->getStripeCode()
			? $this->error->getStripeCode()
			: null;

		return $this->error ? $code : null;
	}

}
