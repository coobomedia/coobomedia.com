<?php
/**
 * Form Field: Custom Amount
 *
 * @package SimplePay\Pro\Forms\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

namespace SimplePay\Pro\Forms\Fields;

use SimplePay\Core\Abstracts\Custom_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom_Amount class.
 *
 * @since 4.1.0
 */
class Custom_Amount extends Custom_Field {

	/**
	 * Prints HTML for custom amount field on frontend.
	 *
	 * @since 4.1.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {
		$id      = self::get_id_attr();
		$name    = 'simpay_custom_price_amount';

		// Default price information.
		$prices        = simpay_get_payment_form_prices( self::$form );
		$default_price = simpay_get_payment_form_default_price( $prices );

		$value = simpay_format_currency(
			$default_price->unit_amount,
			$default_price->currency,
			false
		);

		$prefill_default = isset( self::$settings['prefill_default'] )
			? 'yes' === self::$settings['prefill_default']
			: false;

		/**
		 * Filters the Payment Form's custom amount input type.
		 *
		 * @since 3.0.0
		 *
		 * @param string $input_type Input type.
		 */
		$input_type = apply_filters(
			'simpay_custom_amount_field_type',
			'tel'
		);

		if ( ! in_array( $input_type, array( 'number', 'tel' ), true ) ) {
			$input_type = 'tel';
		}

		$input_atts = '';

		if ( 'number' === $input_type ) {
			$input_atts .= 'step="any"';
		}

		/**
		 * Filters the Payment Form's custom amount input attributes.
		 *
		 * @since 3.0.0
		 *
		 * @param string $input_atts Input attributes string.
		 */
		$input_atts = apply_filters(
			'simpay_custom_amount_input_attributes',
			$input_atts
		);

		$input_classes = array(
			'simpay-amount-input',
			'simpay-custom-amount-input',
			'simpay-custom-amount-input-symbol-' .
				self::get_currency_symbol_position()
		);

		// Backwards compatibility fallback.
		$label_fallback = isset( self::$form->subscription_custom_amount_label )
			? self::$form->subscription_custom_amount_label
			: '';

		$label_fallback = empty( $label_fallback )
			? simpay_get_saved_meta(
				self::$form->id,
				'_custom_amount_label',
				''
			)
			: $label_fallback;

		// Backwards compatibility.
		//
		// Hide custom amount field if simpay_form_123_amount or simpay_form_123_currency
		// filters were being used to previously adjust the amount or currency.
		$has_legacy_filtered_custom_amount = get_post_meta(
			self::$form->id,
			'_simpay_has_legacy_filtered_custom_amount',
			true
		);

		ob_start();
		?>

		<?php if ( ! empty( $has_legacy_filtered_custom_amount ) ) : ?>
		<style>
			#simpay-form-<?php echo esc_attr( self::$form->id ); ?> .simpay-custom-amount-container {
				display: none !important;
			}
		</style>
		<?php endif; ?>

		<div class="simpay-form-control simpay-custom-amount-container">
			<?php echo self::get_label( 'label', $label_fallback ); // WPCS: XSS okay. ?>
			<div class="simpay-custom-amount-wrap simpay-field-wrap">
				<?php
				if ( 'left' === self::get_currency_symbol_position() ) :
					echo self::get_currency_symbol_html( $default_price );
				endif;
				?>
				<input
					type="<?php echo esc_attr( $input_type ); ?>"
					name="<?php echo esc_attr( $name ); ?>"
					id="<?php echo esc_attr( $id ); ?>"
					<?php
					if (
						true === $prefill_default ||
						! empty( $has_legacy_filtered_custom_amount )
					) :
					?>
					value="<?php echo esc_attr( $value ); ?>"
					<?php endif; ?>
					placeholder="<?php echo esc_attr( $value ); ?>"
					class="<?php echo esc_attr( implode( ' ', $input_classes ) ) ; ?>"
					data-prefill-default="<?php echo esc_attr( $prefill_default ); ?>"
					<?php echo $input_atts; ?>
				/>
				<?php
				if ( 'right' === self::get_currency_symbol_position() ) :
					echo self::get_currency_symbol_html( $default_price );
				endif;
				?>
			</div>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Returns a noramlized currency position. `left` or `right`.
	 *
	 * @since 4.1.0
	 *
	 * @return string `left` or `right`
	 */
	private static function get_currency_symbol_position() {
		return (
			'left' === self::$form->currency_position ||
			'left_space' === self::$form->currency_position
		)
			? 'left'
			: 'right';
	}

	/**
	 * Returns the fields currency symbol (derived from the Payment Form).
	 *
	 * @since 4.1.0
	 *
	 * @param string $currency Currency code.
	 * @return string
	 */
	private static function get_currency_symbol( $currency ) {
		return simpay_get_currency_symbol( $currency );
	}

	/**
	 * Returns the markup for amount input's currency symbol.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption
	 * @return string
	 */
	private static function get_currency_symbol_html( $price ) {
		$symbol          = self::get_currency_symbol( $price->currency );
		$symbol_position = self::get_currency_symbol_position();

		return sprintf(
			'<span class="simpay-currency-symbol simpay-currency-symbol-%s">%s</span>',
			$symbol_position,
			$symbol
		);
	}

}
