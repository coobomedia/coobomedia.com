<?php
/**
 * Routines: Tax Rates
 *
 * @package SimplePay\Core\Utils\Migrations
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

namespace SimplePay\Pro\Utils\Migrations\Routines;

use SimplePay\Core\API;
use SimplePay\Core\Utils\Migrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tax_Rates_API class
 *
 * @since 4.1.0
 */
class Tax_Rates_API extends Migrations\Bulk_Migration {

	/**
	 * Runs the migration.
	 *
	 * @since 4.1.0
	 */
	public function run() {
		$test_mode = simpay_is_test_mode();
		$api_key   = simpay_get_setting(
			( $test_mode ? 'test' : 'live' ) . '_secret_key',
			''
		);

		// Do not proceed until we have an API key.
		if ( empty( $api_key ) ) {
			return;
		}

		$tax_percent = simpay_get_setting( 'tax_percent', '' );

		if ( empty( $tax_percent ) ) {
			return $this->complete();
		}

		try {
			$tax_rate = API\TaxRates\create(
				array(
					'display_name' => 'Sales tax',
					'percentage'   => $tax_percent,
					'inclusive'    => false,
				),
				array(
					'api_key' => $api_key,
				)
			);

			$updated = simpay_update_setting(
				'tax_rates_' . ( $test_mode ? 'test' : 'live' ),
				array(
					array(
						'id' => $tax_rate->id,
					),
				)
			);

			if ( false !== $updated ) {
				simpay_update_setting(
					'tax_rates_' . ( $test_mode ? 'test' : 'live' ) . '_modified',
					time()
				);

				simpay_update_setting( 'taxes', 'yes' );

				$this->complete();
			}
		} catch ( Exception $e ) {
			return false;
		}
	}

}
