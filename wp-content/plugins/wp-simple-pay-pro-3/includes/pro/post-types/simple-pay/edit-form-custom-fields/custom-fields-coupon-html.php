<?php
/**
 * Custom Field: Coupon
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
		<label for="<?php echo 'simpay-coupon-label-' . $counter; ?>"><?php esc_html_e( 'Label', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[coupon][' . $counter . '][label]',
				'id'          => 'simpay-coupon-label-' . $counter,
				'value'       => isset( $field['label'] ) ? $field['label'] : 'Coupon',
				'class'       => array(
					'simpay-field-text',
					'simpay-label-input',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'description' => simpay_form_field_label_description(),
			)
		);
		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-coupon-placeholder-' . $counter; ?>"><?php esc_html_e( 'Placeholder', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[coupon][' . $counter . '][placeholder]',
				'id'          => 'simpay-coupon-placeholder-' . $counter,
				'value'       => isset( $field['placeholder'] ) ? $field['placeholder'] : '',
				'class'       => array(
					'simpay-field-text',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'description' => simpay_placeholder_description(),
			)
		);
		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-coupon-style-' . $counter; ?>"><?php esc_html_e( '"Apply" Button Display Style', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'    => 'radio',
				'name'    => '_simpay_custom_field[coupon][' . $counter . '][style]',
				'id'      => 'simpay-coupon-style-' . $counter,
				'value'   => isset( $field['style'] )
					? $field['style']
					: 'none',
				'class'   => array( 'simpay-multi-toggle' ),
				'options' => array(
					'stripe' => __( 'Stripe blue', 'simple-pay' ),
					'none'   => __( 'Default', 'simple-pay' ),
				),
				'inline'  => 'inline',
			)
		);
		?>
	</td>
</tr>
