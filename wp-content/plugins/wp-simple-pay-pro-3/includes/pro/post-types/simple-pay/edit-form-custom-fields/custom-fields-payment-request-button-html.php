<?php
/**
 * Output Payment Request Button settings metabox in the admin.
 *
 * @link https://stripe.com/docs/stripe-js/elements/payment-request-button
 * @link https://www.w3.org/TR/payment-request/
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

/**
 * Allow output of extra settings before the defaults.
 *
 * @since 3.4.0
 */
do_action( 'simpay_admin_before_custom_field_payment_request_button' );
?>

<tr class="simpay-panel-field simpay-show-if" data-if="_tax_status" data-is="automatic">
	<td colspan="2" style="padding-top: 12px;">
		<div class="notice inline notice-warning">
			<p>
				<?php
				esc_html_e(
					'Sorry, Apple Pay/Google Pay Button is not compatible with automatically calculated taxes.',
					'simple-pay'
				);
				?>
			</p>
	</td>
</tr>

<tr class="simpay-panel-field simpay-show-if" data-if="_tax_status" data-is="none fixed-global">
	<td colspan="2" style="padding-top: 12px;">
		<div class="notice inline notice-info">
			<p><?php esc_html_e( 'Using this field, site visitors are shown either an Apple Pay, Google Pay, or Microsoft Pay button if their browser and device combination supports it. If none are available, the button is not displayed.', 'simple-pay' ); ?></p>

		<p><strong><?php esc_html_e( 'To use Apple Pay, you must be connected to your Stripe account in Live mode or have your Live API keys enabled.', 'simple-pay' ); ?></strong></p>

		<p><a href="<?php echo simpay_docs_link( 'Help docs for the Apple Pay/Google Pay Button', 'apple-pay-google-pay', 'payment-form-field-settings', true ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Help docs for the Apple Pay/Google Pay Button', 'simple-pay' ); ?></a></p>
	</td>
</tr>

<tr class="simpay-panel-field simpay-show-if" data-if="_tax_status" data-is="none fixed-global">
	<th>
		<?php esc_html_e( 'Button Type', 'simple-pay' ); ?>
	</th>

	<td>
		<?php
		simpay_print_field(
			array(
				'type'    => 'radio',
				'name'    => '_simpay_custom_field[payment_request_button][' . $counter . '][button_type]',
				'id'      => '_payment_request_button_type',
				'class'   => array( 'simpay-multi-toggle' ),
				'options' => array(
					'default' => __( 'Pay', 'simple-pay' ),
					'donate'  => __( 'Donate', 'simple-pay' ),
					'buy'     => __( 'Buy', 'simple-pay' ),
					'book'    => __( 'Booking', 'simple-pay' ),
				),
				'default' => 'default',
				'value'   => isset( $field['button_type'] ) ? $field['button_type'] : 'default',
				'inline'  => 'inline',
			)
		);
		?>
	</td>
</tr>

<tr class="simpay-panel-field simpay-show-if" data-if="_tax_status" data-is="none fixed-global">
	<th>
		<?php esc_html_e( 'Button Theme', 'simple-pay' ); ?>
	</th>

	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'radio',
				'name'        => '_simpay_custom_field[payment_request_button][' . $counter . '][button_theme]',
				'id'          => '_payment_request_button_theme',
				'class'       => array( 'simpay-multi-toggle' ),
				'options'     => array(
					'dark'          => __( 'Dark', 'simple-pay' ),
					'light'         => __( 'Light', 'simple-pay' ),
					'light-outline' => __( 'Light Outline', 'simple-pay' ),
				),
				'default'     => 'dark',
				'value'       => isset( $field['button_theme'] )
					? $field['button_theme']
					: 'dark',
				'inline'      => 'inline',
				'description' => esc_html__(
					'Button theme for non-branded buttons.',
					'simple-pay'
				),
			)
		);
		?>
	</td>
</tr>

<?php
/**
 * Allow output of extra settings after the defaults.
 *
 * @since 3.4.0
 */
do_action( 'simpay_admin_after_custom_field_payment_request_button' );
