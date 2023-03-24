<?php
/**
 * Forms field: Dropdown
 *
 * @package SimplePay\Pro\Forms\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro\Forms\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dropdown class.
 *
 * @since 3.0.0
 */
class Dropdown extends Quantity_Amount {

	/**
	 * Prints HTML for field on frontend.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {
		// Set shared properties.
		// Called here due to the legacy architecture of these fields.
		self::set_properties( $settings );

		$is_quantity = isset( $settings['amount_quantity'] ) &&
			in_array(
				$settings['amount_quantity'],
				array( 'yes', 'quantity' ),
				true
			);

		ob_start();
		?>

		<div class="simpay-form-control simpay-dropdown-container">
			<div class="simpay-dropdown-label simpay-label-wrap">
				<?php echo self::get_label(); // WPCS: XSS okay. ?>
				<div class="simpay-dropdown-wrap simpay-field-wrap">
					<?php
					if ( $is_quantity ) :
						echo self::get_quantity_dropdown( $settings );
					else :
						echo self::get_standard_dropdown( $settings );
					endif;
					?>
				</div>
			</div>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Returns HTML options for the dropdown.
	 *
	 * @since 3.9.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	private static function get_standard_dropdown( $settings ) {
		$options        = self::get_options( $settings );
		$options_markup = '';
		$required       = isset( $settings['required'] );
		$default        = self::get_default_value();

		if ( empty( $default ) ) {
			$default = $options[0];
		}

		foreach ( $options as $v ) {
			$disabled = substr( $v, 0, 1 ) === '~' && substr( $v, -1, 1 ) === '~';

			$options_markup .= sprintf(
				'<option value="%s" %s %s>%s</option>',
				esc_attr( $disabled ? '' : $v ),
				selected( $v, $default, false ),
				disabled( true, $disabled, false),
				esc_html( str_replace('~', '', $v) )
			);
		}

		return (
			sprintf(
				'<select name="%s" id="%s" %s>%s</select>',
				esc_attr( self::$name ),
				esc_attr( self::$id ),
				esc_attr($required ? 'required' : ''),
				$options_markup
			)
		);
	}

	/**
	 * Returns HTML options for the Quantity dropdown.
	 *
	 * @since 3.9.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	private static function get_quantity_dropdown( $settings ) {
		$options    = self::get_options( $settings );
		$quantities = self::get_quantities( $settings );

		// Make sure the number of options and amounts is equal before continuing.
		if ( count( $options ) !== count( $quantities ) ) {
			return (
				'<div style="color: red;">' .
					esc_html__( 'You have a mismatched number of options and amounts. Please correct this for the dropdown to appear.', 'simple-pay' ) .
				'</div>'
			);
		}

		$options_markup = '';
		$default        = self::get_default_value();

		foreach ( $options as $i => $v ) {
			$options_markup .= '<option ' . selected( $v, $default, false ) . ' data-quantity="' . esc_attr( intval( $quantities[ $i ] ) ) . '">' . esc_html( $v ) . '</option>';
		}

		return (
			'<select name="' . esc_attr( self::$name ) . '" id="' . esc_attr( self::$id ) . '" class="simpay-quantity-dropdown">' .
				$options_markup .
			'</select>' .
			'<input type="hidden" name="simpay_quantity" class="simpay-quantity" value="" />'
		);
	}

	/**
	 * Returns HTML options for the Amount dropdown.
	 *
	 * @since 3.9.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	private static function get_amount_dropdown( $settings ) {
		_doing_it_wrong(
			__METHOD__,
			esc_html__(
				'No longer used. Use multiple price options.',
				'simple-pay'
			),
			'4.1.0'
		);
	}

}
