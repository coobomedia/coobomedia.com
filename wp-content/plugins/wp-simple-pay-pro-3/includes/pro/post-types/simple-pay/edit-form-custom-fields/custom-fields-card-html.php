<?php
/**
 * Custom Field: Payment Method
 *
 * Named `card` so existing forms keep the default
 * card field enabled.
 *
 * @package SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form\Custom_Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

$counter = absint( $counter );
?>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-card-label-' . $counter; ?>"><?php esc_html_e( 'Label', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'       => 'standard',
				'subtype'    => 'text',
				'name'       => '_simpay_custom_field[card][' . $counter . '][label]',
				'id'         => 'simpay-card-label-' . $counter,
				'value'      => isset( $field['label'] ) ? $field['label'] : 'Payment Method',
				'class'      => array(
					'simpay-field-text',
					'simpay-label-input',
				),
				'attributes' => array(
					'data-field-key' => $counter,
				),
			)
		);
		?>
		<p class="description">
			<?php
			esc_html_e(
				'A text label displayed above the payment method selector.',
				'simple-pay'
			);
			?>
		</p>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-card-label-' . $counter; ?>">
			<?php esc_html_e( 'Display Icons', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'checkbox',
				'name'        => '_simpay_custom_field[card][' . $counter . '][icons]',
				'id'          => 'simpay-card-icons-' . $counter,
				'value'       => isset( $field['icons'] )
					? $field['icons']
					: 'no',
				'description' => esc_html__(
					'Display icons in the payment method selector.',
					'simple-pay'
				),
			)
		);
		?>
	</td>
</tr>
