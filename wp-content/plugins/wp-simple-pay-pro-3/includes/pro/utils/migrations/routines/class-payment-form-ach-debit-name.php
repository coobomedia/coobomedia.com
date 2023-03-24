<?php
/**
 * Routines: Payment Form ACH Direct Debit "Customer Name" custom field
 *
 * @package SimplePay\Core\Utils\Migrations
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.5.0
 */

namespace SimplePay\Pro\Utils\Migrations\Routines;

use SimplePay\Core\Utils\Migrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Payment_Form_ACH_Debit_Name class
 *
 * @since 4.5.0
 */
class Payment_Form_ACH_Debit_Name extends Migrations\Bulk_Migration {

	/**
	 * Runs the migration.
	 *
	 * @since 4.0.0
	 */
	public function run() {
		$forms = get_posts(
			array(
				'post_type'      => 'simple-pay',
				'posts_per_page' => -1,
			)
		);

		if ( empty( $forms ) ) {
			return $this->complete();
		}

		foreach ( $forms as $form ) {
			$form_display_type = get_post_meta(
				$form->ID,
				'_form_display_type',
				true
			);

			// Only look at Embedded or Overlay forms.
			if ( 'stripe_checkout' === $form_display_type ) {
				continue;
			}

			$payment_methods = get_post_meta(
				$form->ID,
				'_payment_methods',
				true
			);

			$payment_methods = isset( $payment_methods['stripe-elements'] )
				? $payment_methods['stripe-elements']
				: array();

			// Only look at forms with ACH Direct Debit enabled.
			if (
				! isset( $payment_methods['ach-debit'] ) ||
				! isset( $payment_methods['ach-debit']['id'] )
			) {
				continue;
			}

			$custom_fields = get_post_meta(
				$form->ID,
				'_custom_fields',
				true
			);

			if ( isset( $custom_fields['customer_name'] ) ) {
				continue;
			}

			$count = 1;

			foreach ( $custom_fields as $custom_field_group ) {
				foreach ( $custom_field_group as $custom_field ) {
					$count++;
				}
			}

			$added_fields = array(
				'customer_name' => array(
					array(
						'label'    => __( 'Full Name', 'simple-pay' ),
						'id'       => 'simpay_' . $form->ID . '_customer_name',
						'uid'      => $count,
						'required' => 'yes',
					)
				)
			);

			$updated_custom_fields = array_merge(
				$added_fields,
				$custom_fields
			);

			update_post_meta(
				$form->ID,
				'_custom_fields',
				$updated_custom_fields
			);
		}

		$this->complete();
	}

}
