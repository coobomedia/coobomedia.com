<?php
/**
 * Forms field: Payment Request Button
 *
 * @link https://stripe.com/docs/stripe-js/elements/payment-request-button
 * @link https://www.w3.org/TR/payment-request/
 *
 * @package SimplePay\Pro\Forms\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.4.0
 */

namespace SimplePay\Pro\Forms\Fields;
use SimplePay\Core\Abstracts\Custom_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Payment_Request_Button class.
 *
 * @since 3.4.0
 */
class Payment_Request_Button extends Custom_Field {

	/**
	 * Output markup for "Or enter..." payment divider.
	 *
	 * @since 3.4.0
	 */
	public static function divider() {
		/**
		 * Filter the default dividing string for "Or enter..." payment divider.
		 *
		 * @since 3.4.0
		 *
		 * @param string $string Payment divider string.
		 * @return string
		 */
		$string = apply_filters(
			'simpay_payment_request_button_or_label',
			__( 'Or enter your payment details below', 'simple-pay' )
		);
		?>

<p class="simpay-payment-request-button-container__or">
		<?php echo esc_html( $string ); ?>
</p>

		<?php
	}

	/**
	 * Prints HTML for field on frontend.
	 *
	 * @since 3.4.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {
		// Not compatible with automatic taxes.
		$tax_status = get_post_meta( self::$form->id, '_tax_status', true );

		if ( 'automatic' === $tax_status ) {
			return;
		}

		$id = isset( $settings['id'] ) ? $settings['id'] : '';

		if ( false === strpos( $id, 'payment_request_button' ) ) {
			$id = 'simpay_' . self::$form->id . '_payment_request_button_' . $id;
		}

		/**
		 * Filter the position the "Or enter..." payment divider.
		 *
		 * @since 3.4.0
		 *
		 * @param string $position Dividier position.
		 * @return string
		 */
		$or = apply_filters( 'simpay_payment_request_button_or_position', 'after' );

		ob_start();
		?>

<div id="<?php echo esc_attr( simpay_dashify( $id ) ); ?>" class="simpay-form-control simpay-payment-request-button-container">
		<?php 'before' === $or ? self::divider() : null; // WPCS: XSS okay. ?>
	<div class="simpay-payment-request-button-container__button"></div>
		<?php 'after' === $or ? self::divider() : null; // WPCS: XSS okay. ?>
</div>

		<?php
		return ob_get_clean();
	}

}
