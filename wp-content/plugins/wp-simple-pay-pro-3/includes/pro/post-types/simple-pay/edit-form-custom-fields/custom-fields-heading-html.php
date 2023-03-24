<?php
/**
 * Custom Field: Heading
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
?>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-label-label-' . $counter; ?>">
			<?php esc_html_e( 'Heading', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_simpay_custom_field[heading][' . $counter . '][label]',
				'id'          => 'simpay-heading-label-' . $counter,
				'value'       => isset( $field['label'] ) ? $field['label'] : '',
				'class'       => array(
					'simpay-field-text',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'description' => '',
			)
		);
		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="<?php echo 'simpay-heading-level-' . $counter; ?>">
			<?php esc_html_e( 'Heading Level', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'select',
				'name'        => '_simpay_custom_field[heading][' . $counter . '][level]',
				'id'          => 'simpay-heading-level-' . $counter,
				'value'       => isset( $field['level'] ) ? $field['level'] : '2',
				'options'     => array(
					1 => 'H1',
					2 => 'H2',
					3 => 'H3',
					4 => 'H4',
					5 => 'H5',
					6 => 'H6',
				),
				'attributes'  => array(
					'data-field-key' => $counter,
				),
				'description' => '',
			)
		);
		?>
	</td>
</tr>
