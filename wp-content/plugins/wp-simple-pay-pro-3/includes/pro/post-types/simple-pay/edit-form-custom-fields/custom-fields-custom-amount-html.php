<?php
/**
 * Custom Field: Custom Amount
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

$legacy_label = simpay_get_saved_meta(
	get_the_ID(),
	'_custom_amount_label',
	''
);

$value = ! empty( $field['label'] )
	? $field['label']
	: $legacy_label;

// "Prefill with default amount" checkbox attributes.
$prefill_default_id = sprintf(
	'simpay-custom-amount-prefill-default-%s',
	$counter
);

$prefill_default_name = sprintf(
	'_simpay_custom_field[custom_amount][%s][prefill_default]',
	$counter
);
?>

<tr class="simpay-panel-field">
	<td colspan="2" style="padding-top: 12px;">
		<div class="notice inline notice-info">
			<p>
				<?php
				esc_html_e(
					'This is where the amount input will appear if the selected price option allows custom amounts.',
					'simple-pay'
				);
				?>
			</p>
		</div>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="simpay-custom-amount-label"><?php esc_html_e( 'Label', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[custom_amount][' . $counter . '][label]',
				'id'          => 'simpay-custom-amount-label',
				'value'       => $value,
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
		<label for="<?php echo esc_attr( $prefill_default_id ); ?>">
			<?php
			esc_html_e( 'Prefill input with default price amount', 'simple-pay' );
			?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'checkbox',
				'name'        => $prefill_default_name,
				'id'          => $prefill_default_id,
				'value'       => isset( $field['prefill_default'] )
					? $field['prefill_default']
					: '',
				'description' => '',
			)
		);

		?>
	</td>
</tr>
