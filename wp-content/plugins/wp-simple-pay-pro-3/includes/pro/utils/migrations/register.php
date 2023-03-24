<?php
/**
 * Migrations: Register
 *
 * @package SimplePay\Core\Utils
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Utils\Migrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers available migrations.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Utils\Migrations\Migration_Collection $migrations Migration collection.
 */
function register( $migrations ) {
	// Payment Form "Display Type" metadata.
	$migrations->add(
		new Routines\Payment_Form_Display_Type(
			array(
				'id'        => 'payment-form-display-type',
				'automatic' => true,
			)
		)
	);

	// Tax percentage to Tax Rates.
	$migrations->add(
		new Routines\Tax_Rates_API(
			array(
				'id'        => 'tax-rates-api',
				'automatic' => true,
			)
		)
	);

	// Add "Customer Name" to payment forms using ACH Debit.
	$migrations->add(
		new Routines\Payment_Form_ACH_Debit_Name(
			array(
				'id'        => 'payment-form-ach-debit-name',
				'automatic' => true,
			)
		)
	);
}
add_action( 'simpay_register_migrations', __NAMESPACE__ . '\\register' );
