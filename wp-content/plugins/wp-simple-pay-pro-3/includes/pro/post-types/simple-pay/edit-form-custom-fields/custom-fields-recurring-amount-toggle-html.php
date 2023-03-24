<?php
/**
 * Custom Field: Recurring Amount Toggle
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
		<label for="<?php echo 'simpay-recurring-amount-toggle-label-' . $counter; ?>"><?php esc_html_e( 'Label', 'simple-pay' ); ?></label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'       => 'standard',
				'subtype'    => 'text',
				'name'       => '_simpay_custom_field[recurring_amount_toggle][' . $counter . '][label]',
				'id'         => 'simpay-recurring-amount-toggle-label-' . $counter,
				'value'      => isset( $field['label'] ) ? $field['label'] : 'Make this a recurring amount',
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
