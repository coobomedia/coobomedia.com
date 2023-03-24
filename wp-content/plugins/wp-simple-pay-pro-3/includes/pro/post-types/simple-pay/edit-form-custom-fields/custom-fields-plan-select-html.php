<?php
/**
 * Custom Field: Plan Select
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

$counter = absint( $counter );

// Label.
$legacy_label = simpay_get_saved_meta(
	get_the_ID(),
	'_plan_select_form_field_label',
	''
);

$label = ! empty( $field['label'] )
	? $field['label']
	: $legacy_label;

// Display type.
$legacy_display_type = simpay_get_saved_meta(
	get_the_ID(),
	'_multi_plan_display',
	'radio'
);

$display_type = ! empty( $field['display_type'] )
	? $field['display_type']
	: $legacy_display_type;
?>

<tr class="simpay-panel-field simpay-panel-field-price-select-notice" style="display: table-row;">
	<td colspan="2" style="padding-top: 12px;">
		<div class="notice inline notice-info">
			<p>
				<?php
				esc_html_e(
					'This field is not output on the frontend when a single default price option is used.',
					'simple-pay'
				);
				?>
			</p>
		</div>
	</td>
</tr>

<tr class="simpay-panel-field simpay-panel-field-price-select" style="display: none;">
	<th>
		<label for="simpay-plan-select-form-field-label">
			<?php esc_html_e( 'Label', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[plan_select][' . $counter . '][label]',
				'id'          => 'simpay-plan-select-form-field-label',
				'value'       => $label,
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

<tr class="simpay-panel-field simpay-panel-field-price-select" style="display: none;">
	<th>
		<label for="simpay-plan-select-form-field-label">
			<?php esc_html_e( 'Display Style', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'    => 'radio',
				'name'    => '_simpay_custom_field[plan_select][' . $counter . '][display_type]',
				'id'      => '_simpay_custom_field-plan_select-display-type' . $counter,
				'options' => array(
					'radio'    => __( 'Radio select', 'simple-pay' ),
					'dropdown' => __( 'Dropdown', 'simple-pay' ),
					'list'     => __( 'List', 'simple-pay' ),
					'buttons'  => __( 'Buttons', 'simple-pay' ),
				),
				'default' => 'radio',
				'value'   => $display_type,
				'class'   => array( 'simpay-multi-toggle' ),
			)
		);
		?>
	</td>
</tr>
