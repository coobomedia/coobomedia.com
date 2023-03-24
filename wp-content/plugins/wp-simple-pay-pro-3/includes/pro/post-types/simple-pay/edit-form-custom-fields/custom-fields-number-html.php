<?php
/**
 * Custom Field: Number
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
		<label for="<?php echo 'simpay-number-label-' . $counter; ?>"><?php esc_html_e( 'Label', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[number][' . $counter . '][label]',
				'id'          => 'simpay-number-label-' . $counter,
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
		<label for="<?php echo 'simpay-number-placeholder-' . $counter; ?>"><?php esc_html_e( 'Placeholder', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'number',
				'name'        => '_simpay_custom_field[number][' . $counter . '][placeholder]',
				'id'          => 'simpay-number-placeholder-' . $counter,
				'value'       => isset( $field['placeholder'] ) ? $field['placeholder'] : '',
				'class'       => array(
					'small-text',
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
		<label for="<?php echo 'simpay-number-default-' . $counter; ?>"><?php esc_html_e( 'Default Value', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'       => 'standard',
				'subtype'    => 'number',
				'name'       => '_simpay_custom_field[number][' . $counter . '][default]',
				'id'         => 'simpay-number-default-' . $counter,
				'value'      => isset( $field['default'] ) ? $field['default'] : '',
				'class'      => array(
					'small-text',
				),
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
		<label for="<?php echo 'simpay-number-minimum-' . $counter; ?>"><?php esc_html_e( 'Minimum', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'       => 'standard',
				'subtype'    => 'number',
				'name'       => '_simpay_custom_field[number][' . $counter . '][minimum]',
				'id'         => 'simpay-number-minimum-' . $counter,
				'value'      => isset( $field['minimum'] ) ? $field['minimum'] : '',
				'class'      => array(
					'small-text',
				),
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
		<label for="<?php echo 'simpay-number-maximum-' . $counter; ?>"><?php esc_html_e( 'Maximum', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'       => 'standard',
				'subtype'    => 'number',
				'name'       => '_simpay_custom_field[number][' . $counter . '][maximum]',
				'id'         => 'simpay-number-maximum-' . $counter,
				'value'      => isset( $field['maximum'] ) ? $field['maximum'] : '',
				'class'      => array(
					'small-text',
				),
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
		<label for="<?php echo 'simpay-number-required-' . $counter; ?>"><?php esc_html_e( 'Required', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'        => 'checkbox',
				'name'        => '_simpay_custom_field[number][' . $counter . '][required]',
				'id'          => 'simpay-number-required-' . $counter,
				'value'       => isset( $field['required'] ) ? $field['required'] : '',
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'description' => simpay_required_field_description(),
			)
		);

		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-number-quantity-' . $counter; ?>"><?php esc_html_e( 'Quantity Field', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'       => 'checkbox',
				'name'       => '_simpay_custom_field[number][' . $counter . '][quantity]',
				'id'         => 'simpay-number-quantity-' . $counter,
				'value'      => isset( $field['quantity'] ) ? $field['quantity'] : '',
				'attributes' => array(
					'data-field-key' => $counter,
				),
			)
		);

		?>

		<p class="description">
			<?php esc_html_e( 'Multiply the payment form amount by this value.', 'simple-pay' ); ?>
		</p>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-number-metadata-' . $counter; ?>"><?php esc_html_e( 'Stripe Metadata Label', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		$metadata = isset( $field['metadata'] ) ? $field['metadata'] : '';

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[number][' . $counter . '][metadata]',
				'id'          => 'simpay-number-metadata-' . $counter,
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
			id="simpay-number-metadata-<?php echo esc_attr( $counter ); ?>-smart-tag"
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
