<?php
/**
 * Taxes: Tax Rates
 *
 * @package SimplePay\Pro\Taxes
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
 * TaxRates class.
 *
 * @since 4.1.0
 */
class TaxRates {

	/**
	 * Current payment mode.
	 *
	 * @since 4.1.0
	 * @var bool
	 */
	public $livemode;

	/**
	 * Tax rates.
	 *
	 * @since 4.1.0
	 * @var \SimplePay\Pro\Taxes\TaxRate[]
	 */
	private $tax_rates;

	/**
	 * Constructs TaxRates.
	 *
	 * @since 4.1.0
	 *
	 * @param bool $livemode If current payment mode is livemode.
	 */
	public function __construct( $livemode ) {
		$this->livemode = $livemode;
	}

	/**
	 * Returns a list of tax rates.
	 *
	 * @since 4.1.0
	 *
	 * @return \SimplePay\Pro\Taxes\TaxRate[]
	 */
	public function get_tax_rates() {
		if ( null !== $this->tax_rates ) {
			return $this->tax_rates;
		}

		// Current mode has not been modified the most recently.
		try {
			if ( false === $this->is_current_mode_latest() ) {
				$tax_rates = $this->sync();

				// Current mode is considered the latest.
			} else {
				$tax_rates = $this->get_current_mode_tax_rates();
			}
		} catch ( Exception $e ) {
			$tax_rates = array();
		}

		$tax_rate_objects = array();

		foreach ( $tax_rates as $instance_id => $tax_rate_data ) {
			try {
				$tax_rate_objects[ $instance_id ] = new TaxRate(
					$tax_rate_data,
					$this->livemode
				);
			} catch ( Exception $e ) {
				continue;
			}
		}

		$this->tax_rates = $tax_rate_objects;

		return $this->tax_rates;
	}

	/**
	 * Syncs tax rates to the current payment mode.
	 *
	 * This should only occur once the current mode is determined to be out
	 * of date.
	 *
	 * @since 4.1.0
	 *
	 * @return array Updated tax rates for the current mode.
	 */
	public function sync() {
		// API keys are required on both sides. If they are missing, nothing
		// can be done.
		if ( false === $this->can_sync() ) {
			return array();
		}

		$current_tax_rates = $this->get_current_mode_tax_rates();
		$alt_tax_rates     = $this->get_alt_mode_tax_rates();

		// Tax rates in current mode are empty, but not alternative. Copy.
		if ( empty( $current_tax_rates ) && ! empty( $alt_tax_rates ) ) {
			$tax_rates = $this->copy_alt_mode_tax_rates();

			// Tax rates exist in current mode. Sync with alternative mode.
		} else {
			$tax_rates = $this->sync_alt_mode_tax_rates();
		}

		// Persist copied tax rates.
		simpay_update_setting(
			$this->get_current_mode_tax_rates_storage_key(),
			$tax_rates
		);

		simpay_update_setting(
			$this->get_current_mode_tax_rates_modified_storage_key(),
			time()
		);

		return $tax_rates;
	}

	/**
	 * Determines if tax rates can be synced. Requires both mode's API
	 * keys to be available.
	 *
	 * @since 4.1.0
	 *
	 * @return bool
	 */
	private function can_sync() {
		$live_secret = ! empty( simpay_get_setting( 'live_secret_key', '' ) );
		$test_secret = ! empty( simpay_get_setting( 'test_secret_key', '' ) );

		return ! empty( $live_secret ) && ! empty( $test_secret );
	}

	/**
	 * Copies tax rates from an alternative mode when the current mode is empty.
	 *
	 * @since 4.1.0
	 *
	 * @return array[] Tax rates.
	 */
	private function copy_alt_mode_tax_rates() {
		$alt_mode_tax_rates        = $this->get_alt_mode_tax_rates();
		$alt_mode_api_request_args = $this->get_alt_mode_api_request_args();

		$current_mode_api_request_args =
			$this->get_current_mode_api_request_args();

		$copied_tax_rates = array();

		// Loop through existing alternative mode tax rates and copy to current mode.
		foreach ( $alt_mode_tax_rates as $instance_id => $tax_rate ) {
			try {
				$alt_tax_rate = API\TaxRates\retrieve(
					$tax_rate['id'],
					$alt_mode_api_request_args
				);

				$new_tax_rate = API\TaxRates\create(
					$this->get_tax_rate_args_to_copy( $alt_tax_rate ),
					$current_mode_api_request_args
				);

				// Update ID with newly created object.
				$tax_rate['id'] = $new_tax_rate->id;

				$copied_tax_rates[ $instance_id ] = $tax_rate;
			} catch ( Exception $e ) {
				continue;
			}
		}

		return $copied_tax_rates;
	}

	/**
	 * Synces tax rate data from the alternative mode to the current mode.
	 *
	 * @since 4.1.0
	 *
	 * @return array[]
	 */
	private function sync_alt_mode_tax_rates() {
		$alt_mode_tax_rates     = $this->get_alt_mode_tax_rates();
		$current_mode_tax_rates = $this->get_current_mode_tax_rates();

		$alt_mode_api_request_args =
			$this->get_alt_mode_api_request_args();

		$current_mode_api_request_args =
			$this->get_current_mode_api_request_args();

		$synced_tax_rates = array();

		foreach ( $alt_mode_tax_rates as $instance_id => $tax_rate ) {
			try {
				$alt_tax_rate = API\TaxRates\retrieve(
					$tax_rate['id'],
					$alt_mode_api_request_args
				);

				// Tax rate already exists in the current mode, update mutable properties.
				if ( isset( $current_mode_tax_rates[ $instance_id ] ) ) {
					$tax_rate_obj = API\TaxRates\update(
						$current_mode_tax_rates[ $instance_id ]['id'],
						array(
							'display_name' => $alt_tax_rate->display_name,
						),
						$current_mode_api_request_args
					);

					// Tax rate does not exist in current mode, create it.
				} else {
					$tax_rate_obj = API\TaxRates\create(
						$this->get_tax_rate_args_to_copy( $alt_tax_rate ),
						$current_mode_api_request_args
					);
				}

				$synced_tax_rates[ $instance_id ] = array(
					'id' => $tax_rate_obj->id,
				);
			} catch ( Exception $e ) {
				continue;
			}
		}

		// Sort by order of alternative mode's tax rates.
		$sycned_tax_rates = array_replace(
			array_flip( array_keys( $alt_mode_tax_rates ) ),
			$synced_tax_rates
		);

		return $synced_tax_rates;
	}

	/**
	 * Returns the storage key for the current mode's tax rates.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	private function get_current_mode_tax_rates_storage_key() {
		return $this->livemode
			? 'tax_rates_live'
			: 'tax_rates_test';
	}

	/**
	 * Returns the storage key for the current mode's tax rates modification time.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	private function get_current_mode_tax_rates_modified_storage_key() {
		return $this->livemode
			? 'tax_rates_live_modified'
			: 'tax_rates_test_modified';
	}

	/**
	 * Returns API request arguments for the current payment mode.
	 *
	 * @since 4.1.0
	 *
	 * @return array
	 */
	private function get_current_mode_api_request_args() {
		return array(
			'api_key' => simpay_get_setting(
				$this->livemode ? 'live_secret_key' : 'test_secret_key',
				''
			),
		);
	}

	/**
	 * Retrieves saved tax rate data for the current payment mode.
	 *
	 * @since 4.1.0
	 *
	 * @return array[] List of tax rate data. Empty if no tax rates are
	 *                 available in the current mode.
	 */
	private function get_current_mode_tax_rates() {
		return simpay_get_setting(
			$this->get_current_mode_tax_rates_storage_key(),
			array()
		);
	}

	/**
	 * Returns the storage key for the alternative mode's tax rates.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	private function get_alt_mode_tax_rates_storage_key() {
		return $this->livemode
			? 'tax_rates_test'
			: 'tax_rates_live';
	}

	/**
	 * Returns the storage key for the alternative mode's tax rates modification time.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	private function get_alt_mode_tax_rates_modified_storage_key() {
		return $this->livemode
			? 'tax_rates_test_modified'
			: 'tax_rates_live_modified';
	}

	/**
	 * Returns API request arguments for the alternative payment mode.
	 *
	 * @since 4.1.0
	 *
	 * @return array
	 */
	private function get_alt_mode_api_request_args() {
		return array(
			'api_key' => simpay_get_setting(
				$this->livemode ? 'test_secret_key' : 'live_secret_key',
				''
			),
		);
	}

	/**
	 * Retrieves saved tax rate data for the alternative payment mode.
	 *
	 * @since 4.1.0
	 *
	 * @return array[] List of tax rate data. Empty if no tax rates are
	 *                 available in the current mode.
	 */
	private function get_alt_mode_tax_rates() {
		return simpay_get_setting(
			$this->get_alt_mode_tax_rates_storage_key(),
			array()
		);
	}

	/**
	 * Determines if the tax rates in the current payment mode should be
	 * considered the most up to date.
	 *
	 * @since 4.1.0
	 *
	 * @return bool
	 */
	private function is_current_mode_latest() {
		$current_mode_modified = simpay_get_setting(
			$this->get_current_mode_tax_rates_modified_storage_key(),
			''
		);

		$alt_mode_modified = simpay_get_setting(
			$this->get_alt_mode_tax_rates_modified_storage_key(),
			''
		);

		// Neither modes have been set, use this mode as the latest.
		if ( empty( $current_mode_modified ) && empty( $alt_mode_modified ) ) {
			return true;
		}

		// Alt mode has not been modified, current is latest.
		if ( empty( $alt_mode_modified ) ) {
			return true;
		}

		// Current mode has not been modified, it is not the latest.
		if ( empty( $current_mode_modified ) ) {
			return false;
		}

		// Determine if the current mode has been modified more recently.
		return $current_mode_modified > $alt_mode_modified;
	}

	/**
	 * Returns a list of TaxRate arguments that should be copied.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Vendor\Stripe\TaxRate $tax_rate Existing Tax rate.
	 * @return array
	 */
	private function get_tax_rate_args_to_copy( $tax_rate ) {
		$args = array(
			'display_name' => $tax_rate->display_name,
			'percentage'   => $tax_rate->percentage,
			'inclusive'    => $tax_rate->inclusive,
		);

		return $args;
	}

}
