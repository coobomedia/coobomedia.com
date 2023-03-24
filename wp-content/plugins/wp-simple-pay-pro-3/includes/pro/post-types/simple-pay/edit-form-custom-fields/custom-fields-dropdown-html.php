<?php
/**
 * Custom Field: Dropdown
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
		<label for="<?php echo 'simpay-dropdown-label-' . $counter; ?>"><?php esc_html_e( 'Label', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[dropdown][' . $counter . '][label]',
				'id'          => 'simpay-dropdown-label-' . $counter,
				'value'       => isset( $field['label'] ) ? $field['label'] : '',
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
		<label for="<?php echo 'simpay-dropdown-options-' . $counter; ?>"><?php esc_html_e( 'Options', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[dropdown][' . $counter . '][options]',
				'id'          => 'simpay-dropdown-options-' . $counter,
				'value'       => isset( $field['options'] ) ? $field['options'] : '',
				'class'       => array(
					'simpay-field-text',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'description' => esc_html( 'Options to choose from separated by a comma.', 'simple-pay' ),
			)
		);
		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-dropdown-default-' . $counter; ?>"><?php esc_html_e( 'Default Value', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'       => 'standard',
				'subtype'    => 'text',
				'name'       => '_simpay_custom_field[dropdown][' . $counter . '][default]',
				'id'         => 'simpay-dropdown-default-' . $counter,
				'value'      => isset( $field['default'] ) ? $field['default'] : '',
				'class'      => array(
					'simpay-field-text',
				),
				'attributes' => array(
					'data-field-key' => $counter,
				),
			)
		);

		?>

		<p class="description">
			<?php esc_html_e( 'Option to be selected by default. Will be first in list if left blank or no match.', 'simple-pay' ); ?>
		</p>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-dropdown-required-' . $counter; ?>"><?php esc_html_e( 'Required', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'       => 'checkbox',
				'name'       => '_simpay_custom_field[dropdown][' . $counter . '][required]',
				'id'         => 'simpay-dropdown-required-' . $counter,
				'value'      => isset( $field['required'] ) ? $field['required'] : '',
				'attributes' => array(
					'data-field-key' => $counter,
				),
			)
		);

		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-dropdown-type-' . $counter; ?>">
			<?php esc_html_e( 'Quantity Multiplier', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php

		$type = isset( $field['amount_quantity'] ) && ! empty( $field['amount_quantity'] )
			? $field['amount_quantity']
			: 'not_used';

		$type = in_array( $type, array( 'quantity', 'yes' ), true ) ? 'yes' : '';

		simpay_print_field(
			array(
				'type'  => 'checkbox',
				'name'  => '_simpay_custom_field[dropdown][' . $counter . '][amount_quantity]',
				'id'    => 'simpay-dropdown-amount-quantity-' . $counter,
				'label' => 'Quantity Multiplier',
				'value' => $type,
				'class' => array(
					'simpay-dropdown-type',
				),
			)
		);
		?>
	</td>
</tr>

<tr class="simpay-panel-field simpay-show-if" data-if=".simpay-dropdown-type" data-is="yes">
	<th>
		<label for="<?php echo 'simpay-dropdown-quantities-' . $counter; ?>"><?php esc_html_e( 'Quantities', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[dropdown][' . $counter . '][quantities]',
				'id'          => 'simpay-dropdown-quantities-' . $counter,
				'value'       => isset( $field['quantities'] ) ? $field['quantities'] : '',
				'class'       => array(
					'simpay-field-text',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'description' => esc_html__( 'Quantity values to multiply the payment form amount by separated by a comma. Must match the number of options and their order. Must be whole numbers.', 'simple-pay' ),
			)
		);
		?>
	</td>
</tr>

</table>

<table>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-dropdown-metadata-' . $counter; ?>"><?php esc_html_e( 'Stripe Metadata Label', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		$metadata = isset( $field['metadata'] ) ? $field['metadata'] : '';

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[dropdown][' . $counter . '][metadata]',
				'id'          => 'simpay-dropdown-metadata-' . $counter,
				'value'       => $metadata,
				'class'       => array(
					'simpay-field-text',
					'simpay-label-input',
					'simpay-field-smart-tag',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
					'maxlength'      => simpay_metadata_title_length(),
				),
				'description' => simpay_metadata_label_description(),
			)
		);
		?>

		<div
			id="simpay-dropdown-metadata-<?php echo esc_attr( $counter ); ?>-smart-tag"
			style="margin: 12px 0 0; align-items: center; display: <?php echo ! empty( $field['metadata'] ) ? 'flex' : 'none'; ?>"
		>
			<button type="button" class="button button-secondary simpay-copy-button" data-copied="<?php echo esc_attr( 'Copied!', 'simple-pay' ); ?>" data-clipboard-text="<?php echo esc_attr( $metadata ); ?>">
				<?php echo esc_html( 'Copy Smart Tag', 'simple-pay' ); ?>
			</button>

			<code style="margin-left: 8px;">
				{payment:metadata:<?php echo esc_html( $metadata ); ?>}
			</code>
		</div>
	</td>
</tr>
