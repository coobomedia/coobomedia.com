<?php
/**
 * Custom Field: Payment Button
 *
 * @package SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form\Custom_Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.9.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Do intval on counter here so we don't have to run it each time we use it below. Saves some function calls.
$counter = absint( $counter );

?>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-payment-button-text-' . $counter; ?>">
			<?php esc_html_e( 'Continue to Payment Text', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[payment_button][' . $counter . '][text]',
				'id'          => 'simpay-payment-button-text-' . $counter,
				'value'       => isset( $field['text'] ) ? $field['text'] : '',
				'class'       => array(
					'simpay-field-text',
					'simpay-label-input',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'placeholder' => esc_attr__( 'Pay with Card', 'simple-pay' ),
				'description' => esc_html__(
					'Text to display to continue to payment.',
					'simple-pay'
				),
			)
		);

		?>
	</td>
</tr>

<tr class="simpay-panel-field simpay-show-if" data-if="_form_type" data-is="off-site">
	<th>
		<label for="<?php echo 'simpay-payment-button-trial-text-' . $counter; ?>">
			<?php esc_html_e( 'Continue to Trial Text', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[payment_button][' . $counter . '][trial_text]',
				'id'          => 'simpay-payment-button-text-' . $counter,
				'value'       => isset( $field['trial_text'] )
					? $field['trial_text']
					: 'Start Trial',
				'class'       => array(
					'simpay-field-text',
					'simpay-label-input',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'placeholder' => esc_attr__( 'Start Trial', 'simple-pay' ),
				'description' => esc_html__(
					'Text to display to a activate a subscription with a trial.',
					'simple-pay'
				),
			)
		);
		?>
	</td>
</tr>

<tr class="simpay-panel-field simpay-show-if" data-if="_form_type" data-is="off-site">
	<th>
		<label for="<?php echo 'simpay-processing-button-text-' . $counter; ?>">
			<?php esc_html_e( 'Processing Text', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[payment_button][' . $counter . '][processing_text]',
				'id'          => 'simpay-processing-button-text-' . $counter,
				'value'       => isset( $field['processing_text'] ) ? $field['processing_text'] : '',
				'class'       => array(
					'simpay-field-text',
					'simpay-label-input',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'placeholder' => esc_attr__( 'Please Wait...', 'simple-pay' ),
				'description' => esc_html__(
					'Text to display when the payment form is processing.',
					'simple-pay'
				),
			)
		);
		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo esc_attr( 'simpay-payment-button-style-' . $counter ); ?>"><?php esc_html_e( 'Button Style', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'    => 'radio',
				'name'    => '_simpay_custom_field[payment_button][' . $counter . '][style]',
				'id'      => esc_attr( 'simpay-payment-button-style-' . $counter ),
				'value'   => isset( $field['style'] )
					? $field['style']
					: 'stripe',
				'class'   => array( 'simpay-multi-toggle' ),
				'options' => array(
					'stripe' => esc_html__( 'Stripe blue', 'simple-pay' ),
					'none'   => esc_html__( 'Default', 'simple-pay' ),
				),
				'inline'  => 'inline',
			)
		);
		?>
	</td>
</tr>
