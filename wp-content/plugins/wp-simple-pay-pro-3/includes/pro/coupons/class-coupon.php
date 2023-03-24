<?php
/**
 * Coupons: Coupon
 *
 * @package SimplePay
 * @subpackage Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.3.0
 */

namespace SimplePay\Pro\Coupons;

use DateTime;
use SimplePay\Core\Model\ModelInterface;
use SimplePay\Pro\Coupons\Stripe_Sync\Abstract_Synced_Stripe_Object;

/**
 * Coupon class.
 *
 * @since 4.3.0
 */
class Coupon extends Abstract_Synced_Stripe_Object implements ModelInterface {

	/**
	 * Internal coupon record ID.
	 *
	 * @since 4.3.0
	 * @var int
	 */
	public $id;

	/**
	 * Stripe object.
	 *
	 * @since 4.3.0
	 * @var \SimplePay\Vendor\Stripe\Coupon|null
	 */
	public $object;

	/**
	 * Coupon name.
	 *
	 * Used as both the `id` and `name` in the Stripe object.
	 *
	 * @since 4.3.0
	 * @var string
	 */
	public $name;

	/**
	 * Coupon percent off.
	 *
	 * @since 4.3.0
	 * @var int|null
	 */
	public $percent_off;

	/**
	 * Coupon amount off.
	 *
	 * @since 4.3.0
	 * @var int|null
	 */
	public $amount_off;

	/**
	 * Coupon amount off currency.
	 *
	 * The three-letter ISO code for the currency of the amount to take off.
	 *
	 * @link https://stripe.com/docs/currencies
	 *
	 * @since 4.3.0
	 * @var string|null
	 */
	public $currency;

	/**
	 * Coupon duration.
	 *
	 * One of forever, once, and repeating. Describes how long a customer who
	 * applies this coupon will get the discount.
	 *
	 * @since 4.3.0
	 * @var string|null
	 */
	public $duration;

	/**
	 * Coupon duration in months.
	 *
	 * If duration is repeating, the number of months the coupon applies.
	 * Null if coupon duration is forever or once.
	 *
	 * @since 4.3.0
	 * @var int|null
	 */
	public $duration_in_months;

	/**
	 * Coupon redemption limit
	 * Maximum number of times this coupon can be redeemed, in total, across
	 * all customers, before it is no longer valid.
	 *
	 * @since 4.3.0
	 * @var int|null
	 */
	public $max_redemptions;

	/**
	 * Coupon redemption limit date.
	 *
	 * Date after which the coupon can no longer be redeemed.
	 *
	 * @since 4.3.0
	 * @var \DateTime|null
	 */
	public $redeem_by;

	/**
	 * Coupon product limitations.
	 *
	 * A list of payment forms the coupon can be applied to. Stripe coupon objects
	 * do not set the applies_to.products property because Products are mode-specific.
	 * Instead internal validation is done before applying the coupon to a Customer.
	 *
	 * @since 4.3.0
	 * @var array|null
	 */
	public $applies_to_forms;

	/**
	 * Determines if the coupon is still active.
	 *
	 * An inactive coupon should remove the Stripe object in the current mode.
	 *
	 * @since 4.3.0
	 * @var bool
	 */
	protected $active;

	/**
	 * Coupon.
	 *
	 * @since 4.3.0
	 *
	 * @param array $data Data to create an transaction from.
	 */
	public function __construct( $data ) {
		// Populate object.
		foreach ( (array) $data as $key => $value ) {
			$this->{$key} = $value;
		}

		// Cast values.
		if ( isset( $this->id ) ) {
			$this->id = (int) $this->id;
		}

		if ( isset( $this->percent_off ) ) {
			$this->percent_off = (float) $this->percent_off;
		}

		if ( isset( $this->amount_off ) ) {
			$this->amount_off = (int) $this->amount_off;
		}

		if ( isset( $this->duration_in_months ) ) {
			$this->duration_in_months = (int) $this->duration_in_months;
		}

		if ( isset( $this->max_redemptions ) ) {
			$this->max_redemptions = (int) $this->max_redemptions;
		}

		if ( isset( $this->redeem_by ) ) {
			$this->redeem_by = new DateTime( $this->redeem_by );
		}

		if ( isset( $this->applies_to_forms ) ) {
			$this->applies_to_forms = maybe_unserialize(
				$this->applies_to_forms
			);

			if ( empty( $this->applies_to_forms ) ) {
				$this->applies_to_forms = null;
			}
		}

		if ( isset( $this->active ) ) {
			$this->active = (bool) $this->active;
		}

		parent::__construct();
	}

	/**
	 * Returns the correct value for a given property.
	 *
	 * @since 4.3.0
	 *
	 * @param string $property Coupon property.
	 * @return mixed
	 */
	public function __get( $property ) {
		switch ( $property ) {
			// Dynamic read-only properties of the Coupon.
			case 'times_redeemed':
			case 'valid':
				return $this->object ? $this->object->$property : null;
			// Locally stored properties.
			default:
				return $this->$property;
		}
	}

	/**
	 * Returns a human readable display amount for the coupon discount.
	 *
	 * i.e $12.00 or 12%.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	public function get_display_amount() {
		$amount = $this->amount_off ? $this->amount_off : $this->percent_off;

		// Fixed amount with currency.
		if ( $this->amount_off ) {
			return simpay_format_currency( $this->amount_off, $this->currency );
		}

		// Percentage amount.
		return $amount . '%';
	}

	/**
	 * Determmines if a coupon applies to a specific payment form.
	 *
	 * @since 4.3.0
	 *
	 * @param int $payment_form Payment Form ID.
	 * @return bool True if the coupon can be applied to the payment form.
	 */
	public function applies_to_form( $form_id ) {
		// No restrictions, always apply.
		if ( null === $this->applies_to_forms ) {
			return true;
		}

		// Check for restrictions.
		return (
			null !== $this->applies_to_forms &&
			in_array( $form_id, $this->applies_to_forms, true )
		);
	}

	/**
	 * Returns an array containing the representation of the public properties.
	 *
	 * @since 4.3.0
	 *
	 * @return array
	 */
	public function to_array() {
		return array(
			'id'                 => $this->id,
			'object'             => $this->object,
			'name'               => $this->name,
			'percent_off'        => $this->percent_off,
			'amount_off'         => $this->amount_off,
			'currency'           => $this->currency,
			'duration'           => $this->duration,
			'duration_in_months' => $this->duration_in_months,
			'max_redemptions'    => $this->max_redemptions,
			'redeem_by'          => $this->redeem_by,
			'times_redeebed'     => $this->times_redeemed,
			'valid'              => $this->valid,
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_creatable_props() {
		return array_filter(
			array(
				'id'                 => $this->name,
				'name'               => $this->name,
				'percent_off'        => $this->percent_off,
				'amount_off'         => $this->amount_off,
				'currency'           => $this->currency,
				'duration'           => $this->duration,
				'duration_in_months' => $this->duration_in_months,
				'max_redemptions'    => $this->max_redemptions,
				'redeem_by'          => $this->redeem_by ?
					$this->redeem_by->getTimestamp()
					: null,
			)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_retrievable_callback() {
		return '\SimplePay\Core\API\Coupons\retrieve';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_creatable_callback() {
		return '\SimplePay\Core\API\Coupons\create';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_editable_props() {
		// We do not provide an interface to change the name but our API wrapper
		// does not do well with empty arguments -- so provide one.
		return array(
			'name' => $this->name,
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_editable_callback() {
		return '\SimplePay\Core\API\Coupons\update';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_deletable_callback() {
		return '\SimplePay\Core\API\Coupons\delete';
	}

}
