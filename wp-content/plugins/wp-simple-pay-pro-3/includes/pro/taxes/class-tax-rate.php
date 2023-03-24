<?php
/**
 * Taxes: Tax Rate
 *
 * @package SimplePay\Core\PaymentForm
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

namespace SimplePay\Pro\Taxes;

use SimplePay\Core\API;
use Exception;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TaxRate class.
 *
 * @since 4.1.0
 */
class TaxRate {

	/**
	 * Current payment mode.
	 *
	 * @since 4.1.0
	 * @var bool
	 */
	public $livemode;

	/**
	 * Tax Rate ID.
	 *
	 * @since 4.1.0
	 * @var string
	 */
	public $id;

	/**
	 * Tax Rate display name.
	 *
	 * @since 4.1.0
	 * @var string
	 */
	public $display_name;

	/**
	 * Tax Rate perentage.
	 *
	 * @since 4.1.0
	 * @var float
	 */
	public $percentage;

	/**
	 * Tax Rate caclulation.
	 *
	 * @since 4.1.0
	 * @var string
	 */
	public $calculation;

	/**
	 * Tax Rate object.
	 *
	 * @since 4.1.0
	 * @var \SimplePay\Vendor\Stripe\TaxRate
	 */
	private $tax_rate;

	/**
	 * Constructs a TaxRate.
	 *
	 * @since 4.1.0
	 *
	 * @param array $tax_rate_data {
	 *   Tax rate data.
	 *
	 *   @type int $id Tax Rate ID.
	 * }
	 * @param bool  $livemode If TaxRate should be retrieved in live mode.
	 */
	public function __construct( $tax_rate_data, $livemode ) {
		$this->livemode = $livemode;

		if ( ! isset( $tax_rate_data['id'] ) ) {
			throw new Exception(
				__(
					'Unable to create TaxRate. Invalid ID.',
					'simple-pay'
				)
			);
		}

		$this->id = sanitize_text_field( $tax_rate_data['id'] );

		$this->tax_rate = API\TaxRates\retrieve(
			$this->id,
			array(
				'api_key' => simpay_get_setting(
					true === $livemode ? 'live_secret_key' : 'test_secret_key',
					''
				),
			),
			array(
				'cached' => true,
			)
		);

		$this->display_name = $this->tax_rate->display_name;
		$this->percentage   = $this->tax_rate->percentage;
		$this->calculation  = true === $this->tax_rate->inclusive
			? 'inclusive'
			: 'exclusive';
	}

	/**
	 * Returns a label for display.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	public function get_display_label() {
		return sprintf(
			'%1$s (%2$s%%%3$s)',
			$this->display_name,
			$this->percentage,
			'inclusive' === $this->calculation
				? ' ' . _x( 'inclusive', 'tax calculation', 'simple-pay' )
				: ''
		);
	}

}
