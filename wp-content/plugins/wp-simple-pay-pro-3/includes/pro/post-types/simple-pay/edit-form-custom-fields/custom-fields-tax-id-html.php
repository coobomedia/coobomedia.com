<?php
/**
 * Custom Field: Tax ID
 *
 * @package SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form\Custom_Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$counter = absint( $counter );
?>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-tax-id-label-' . $counter; ?>">
			<?php esc_html_e( 'Label', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[tax_id][' . $counter . '][label]',
				'id'          => 'simpay-tax-id-label-' . $counter,
				'value'       => isset( $field['label'] )
					? esc_html( $field['label'] )
					: esc_html( 'Tax ID', 'simple-pay' ),
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
		<label for="<?php echo 'simpay-tax-id-placeholder-' . $counter; ?>">
			<?php esc_html_e( 'Placeholder', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[tax_id][' . $counter . '][placeholder]',
				'id'          => 'simpay-telephone-placeholder-' . $counter,
				'value'       => isset( $field['placeholder'] )
					? esc_html( $field['placeholder'] )
					: '',
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
		<label for="<?php echo 'simpay-tax-id-required-' . $counter; ?>">
			<?php esc_html_e( 'Required', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'checkbox',
				'name'        => '_simpay_custom_field[tax_id][' . $counter . '][required]',
				'id'          => 'simpay-tax-id-required-' . $counter,
				'value'       => isset( $field['required'] )
					? $field['required']
					: '',
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'description' => simpay_required_field_description(),
			)
		);
		?>
	</td>
</tr>
