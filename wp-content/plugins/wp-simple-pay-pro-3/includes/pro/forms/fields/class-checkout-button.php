<?php
/**
 * Forms field: Checkout Button
 *
 * @package SimplePay\Pro\Forms\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro\Forms\Fields;

use SimplePay\Core\Abstracts\Custom_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checkout_Button class.
 *
 * @since 3.0.0
 */
class Checkout_Button extends Custom_Field {

	/**
	 * Prints HTML for field on frontend.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {
		$id          = self::get_id_attr();
		$button_text = self::print_button_text( $settings );
		$style       = isset( $settings['style'] ) ? $settings['style'] : 'none';

		$button_classes = array(
			'simpay-btn',
			'simpay-checkout-btn',
		);

		if ( 'stripe' === $style ) {
			$button_classes[] = 'stripe-button-el';
		}

		ob_start();
		?>

		<div
			class="simpay-form-control simpay-checkout-btn-container"
		>
			<button
				id="<?php echo esc_attr( $id ); ?>"
				class="<?php echo esc_attr( implode( ' ', $button_classes ) ); ?>"
				type="submit"
			>
				<span>
					<?php echo $button_text; ?>
				</span>
			</button>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * HTML for the button text including total amount.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_button_text( $settings ) {

		// TODO Handle trials -- "Start your free trial" text.

		$prices = simpay_get_payment_form_prices( self::$form );
		$price  = simpay_get_payment_form_default_price( $prices );

		$formatted_amount = simpay_format_currency(
			$price->unit_amount,
			$price->currency
		);

		$text = isset( $settings['text'] ) && ! empty( $settings['text'] )
			? $settings['text']
			: esc_html__( 'Pay {{amount}}', 'simple-pay' );

		$text = str_replace(
			'{{amount}}',
			'<em class="simpay-total-amount-value">' . $formatted_amount . '</em>',
			$text
		);

		return $text;
	}

}
