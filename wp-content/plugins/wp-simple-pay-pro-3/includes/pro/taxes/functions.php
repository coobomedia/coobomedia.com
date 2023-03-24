<?php
/**
 * Taxes: Functions
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Core\Utils;
use SimplePay\Pro\Taxes\TaxRates;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the saved tax rates for a payment mode.
 *
 * @since 4.1.0
 *
 * @param bool $livemode If the current payment mode is live.
 * @return \SimplePay\Pro\Taxes\TaxRate[]
 */
function simpay_get_tax_rates( $livemode ) {
	return ( new TaxRates( $livemode ) )->get_tax_rates();
}

/**
 * Returns the saved tax rates for a Payment Form.
 *
 * Handles syncing for the current payment mode.
 *
 * @since 4.1.0
 *
 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
 * @return \SimplePay\Pro\Taxes\TaxRate[]
 */
function simpay_get_payment_form_tax_rates( $form ) {
	if ( false === $form ) {
		return array();
	}

	$taxes = simpay_get_setting( 'taxes', 'no' );

	if ( 'yes' !== $taxes ) {
		return array();
	}

	// Migrate legacy tax percent information if required.
	$migrations = Utils\get_collection( 'migrations' );
	$migration  = $migrations->get_item( 'tax-rates-api' );

	if ( false === $migration->is_complete() ) {
		$migration->run( $form );
	}

	$tax_rates = array_values(
		( new TaxRates( $form->is_livemode() ) )->get_tax_rates()
	);

	/**
	 * Filters the tax rates for a specific Payment Form.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Pro\Taxes\TaxRate[] $tax_rates Tax rates.
	 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
	 */
	$tax_rates = apply_filters(
		'simpay_get_payment_form_tax_rates',
		$tax_rates,
		$form
	);

	return $tax_rates;
}

/**
 * Returns the total inclusive tax percentage for a Payment Form.
 *
 * @since 4.1.0
 *
 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
 * @param string                         $calculation Tax rate calculation.
 *                                                    `inclusive` or `exclusive`.
 * @return float
 */
function simpay_get_payment_form_tax_percentage( $form, $calculation ) {
	if ( false === $form ) {
		return array();
	}

	$valid_calculation = in_array(
		$calculation,
		array( 'inclusive', 'exclusive' ),
		true
	);

	if ( false === $valid_calculation ) {
		$calculation = 'exclusive';
	}

	$tax_rates = array_values(
		( new TaxRates( $form->is_livemode() ) )->get_tax_rates()
	);

	return array_reduce(
		$tax_rates,
		function( $tax_percentage, $tax_rate ) use ( $calculation ) {
			if ( $calculation !== $tax_rate->calculation ) {
				return $tax_percentage;
			}

			return $tax_percentage + $tax_rate->percentage;
		},
		0
	);
}

/**
 * Returns a list of Stripe tax codes.
 *
 * @since 4.6.0
 *
 * @return array<\SimplePay\Vendor\Stripe\TaxCode> Available Stripe tax codes.
 */
function simpay_get_stripe_tax_codes() {
	$tax_codes      = array();
	$tax_codes_file = SIMPLE_PAY_DIR . '/data/tax-codes.json';

	if ( file_exists( $tax_codes_file ) ) {
		$tax_codes = json_decode( file_get_contents( $tax_codes_file ) );

		if ( false === $tax_codes ) {
			$tax_codes = array();
		}
	}

	/**
	 * Filters the tax codes available for selection.
	 *
	 * @since 4.6.0
	 *
	 * @param array<\SimplePay\Vendor\Stripe\TaxCode> Available Stripe tax codes.
	 */
	$tax_codes = apply_filters( 'simpay_get_stripe_tax_codes', $tax_codes );

	return $tax_codes;
}
