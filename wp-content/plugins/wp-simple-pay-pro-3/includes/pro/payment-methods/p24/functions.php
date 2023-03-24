<?php
/**
 * P24: Functions
 *
 * @package SimplePay\Pro\Payment_Methods\P24
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.2.0
 */

namespace SimplePay\Pro\Payment_Methods\P24;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filters Payment Form script variables.
 *
 * @since 4.2.0
 *
 * @param \SimplePay\Core\Abstracts\Form[] $forms List of Payment Forms.
 * @return array
 */
function localize( $forms ) {
	$no_bank_message = __( 'Please select a bank.', 'simple-pay' );

	foreach ( $forms as $form_id => $form_vars ) {
		foreach ( $forms[ $form_id ]['form']['config']['paymentMethods'] as $k => $payment_method ) {
			if ( 'p24' !== $payment_method->id ) {
				continue;
			}

			$forms[ $form_id ]['form']['config']['paymentMethods'][ $k ]->i18n = array(
				'empty' => esc_html( $no_bank_message ),
			);
		}
	}

	return $forms;
}
add_filter( 'simpay_form_script_variables', __NAMESPACE__ . '\\localize', 10, 2 );
