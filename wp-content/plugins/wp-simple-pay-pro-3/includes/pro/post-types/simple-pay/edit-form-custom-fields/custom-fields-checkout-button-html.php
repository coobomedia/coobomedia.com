<?php
/**
 * Custom Field: Checkout Button
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
		<label for="<?php echo 'simpay-checkout-button-text-' . $counter; ?>">
			<?php esc_html_e( 'Complete Payment Text', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[checkout_button][' . $counter . '][text]',
				'id'          => 'simpay-checkout-button-text-' . $counter,
				'value'       => isset( $field['text'] )
					? $field['text']
					: 'Pay {{amount}}',
				'class'       => array(
					'simpay-field-text',
					'simpay-label-input',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'placeholder' => esc_attr__( 'Pay {{amount}}', 'simple-pay' ),
				'description' => esc_html__(
					'Text to display to complete the payment.',
					'simple-pay'
				),
			)
		);
		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-checkout-button-trial-text-' . $counter; ?>">
			<?php
			esc_html_e(
				'Start Trial Text',
				'simple-pay'
			);
			?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[checkout_button][' . $counter . '][trial_text]',
				'id'          => 'simpay-checkout-button-trial-text-' . $counter,
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
				'placeholder' => esc_attr__( 'Continue', 'simple-pay' ),
				'description' => esc_html__(
					'Text to display to a activate a subscription with a trial.',
					'simple-pay'
				),
			)
		);
		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-checkout-button-bnpl-text-' . $counter; ?>">
			<?php
			esc_html_e(
				'Buy Now, Pay Later Text',
				'simple-pay'
			);
			?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[checkout_button][' . $counter . '][bnpl_text]',
				'id'          => 'simpay-checkout-button-bnpl-text-' . $counter,
				'value'       => isset( $field['bnpl_text'] )
					? $field['bnpl_text']
					: 'Continue',
				'class'       => array(
					'simpay-field-text',
					'simpay-label-input',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'placeholder' => esc_attr__( 'Continue', 'simple-pay' ),
				'description' => esc_html__(
					'Text to display when using a "buy now, pay later" payment method that redirects to complete the payment.',
					'simple-pay'
				),
			)
		);
		?>
	</td>
</tr>

<tr class="simpay-panel-field">
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
				'name'        => '_simpay_custom_field[checkout_button][' . $counter . '][processing_text]',
				'id'          => 'simpay-processing-button-text-' . $counter,
				'value'       => isset( $field['processing_text'] )
					? $field['processing_text']
					: 'Please wait...',
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
		<label for="<?php echo esc_attr( 'simpay-checkout-button-style-' . $counter ); ?>"><?php esc_html_e( 'Payment Button Style', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'    => 'radio',
				'name'    => '_simpay_custom_field[checkout_button][' . $counter . '][style]',
				'id'      => esc_attr( 'simpay-checkout-button-style-' . $counter ),
				'value'   => isset( $field['style'] ) ? $field['style'] : 'none',
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
