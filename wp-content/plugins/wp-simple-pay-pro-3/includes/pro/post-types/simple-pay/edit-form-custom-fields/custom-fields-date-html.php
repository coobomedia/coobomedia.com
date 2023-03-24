<?php
/**
 * Custom Field: Date
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
		<label for="<?php echo 'simpay-date-label-' . $counter; ?>"><?php esc_html_e( 'Label', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[date][' . $counter . '][label]',
				'id'          => 'simpay-date-label-' . $counter,
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
		<label for="<?php echo 'simpay-date-default-' . $counter; ?>"><?php esc_html_e( 'Default Value', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'       => 'standard',
				'subtype'    => 'text',
				'name'       => '_simpay_custom_field[date][' . $counter . '][default]',
				'id'         => 'simpay-date-default-' . $counter,
				'value'      => isset( $field['default'] ) ? $field['default'] : '',
				'class'      => array(
					'simpay-field-text',
					'simpay-date-input',
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
		<label for="<?php echo 'simpay-date-required-' . $counter; ?>"><?php esc_html_e( 'Required', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php

		simpay_print_field(
			array(
				'type'        => 'checkbox',
				'name'        => '_simpay_custom_field[date][' . $counter . '][required]',
				'id'          => 'simpay-date-required-' . $counter,
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
		<label for="<?php echo 'simpay-date-metadata-' . $counter; ?>"><?php esc_html_e( 'Stripe Metadata Label', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		$metadata = isset( $field['metadata'] ) ? $field['metadata'] : '';

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[date][' . $counter . '][metadata]',
				'id'          => 'simpay-date-metadata-' . $counter,
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
			id="simpay-date-metadata-<?php echo esc_attr( $counter ); ?>-smart-tag"
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
