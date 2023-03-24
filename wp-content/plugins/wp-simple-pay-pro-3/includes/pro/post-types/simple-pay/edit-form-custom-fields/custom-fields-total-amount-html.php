<?php
/**
 * Custom Field: Total Amount
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
		<label for="<?php echo 'simpay-subtotal-amount-label-' . $counter; ?>">
			<?php esc_html_e( '"Subtotal Amount" Label', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'       => 'standard',
				'subtype'    => 'text',
				'name'       => sprintf(
					'_simpay_custom_field[total_amount][%s][subtotal_label]',
					$counter
				),
				'id'         => sprintf(
					'simpay-subtotal-amount-label-%s',
					$counter
				),
				'value'      => isset( $field['subtotal_label'] )
					? $field['subtotal_label']
					: 'Subtotal',
				'class'      => array(
					'simpay-field-text',
					'simpay-label-input',
				),
			)
		);
		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-total-amount-label-' . $counter; ?>">
			<?php esc_html_e( '"Total Amount" Label', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'       => 'standard',
				'subtype'    => 'text',
				'name'       => sprintf(
					'_simpay_custom_field[total_amount][%s][label]',
					$counter
				),
				'id'         => sprintf(
					'simpay-total-amount-label-%s',
					$counter
				),
				'value'      => isset( $field['label'] )
					? $field['label']
					: 'Total due',
				'class'      => array(
					'simpay-field-text',
					'simpay-label-input',
				),
			)
		);
		?>
	</td>
</tr>

<?php if ( simpay_subscriptions_enabled() ) : ?>
<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-total-recurring-total-label-' . $counter; ?>">
			<?php esc_html_e( '"Recurring Payment" Label', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'       => 'standard',
				'subtype'    => 'text',
				'name'       => '_simpay_custom_field[total_amount][' . $counter . '][recurring_total_label]',
				'id'         => 'simpay-total-amount-recurring-total-label-' . $counter,
				'value'      => isset( $field['recurring_total_label'] )
					? $field['recurring_total_label']
					: 'Recurring amount',
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
	</td>
</tr>
<?php endif; ?>