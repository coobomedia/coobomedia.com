<?php
/**
 * Forms field: Radio
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
 * Radio class.
 *
 * @since 3.0.0
 */
class Radio extends Quantity_Amount {

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

		$label = isset( self::$settings[ 'label' ] )
			? self::$settings[ 'label' ]
			: '';

		$label_classes = array();

		if ( empty( $label ) ) {
			$label     = self::$settings['type'];
			$label_classes[] = 'screen-reader-text';
		}

		$list_classes = array( 'simpay-radio-wrap', 'simpay-field-wrap' );

		if ( true === $is_quantity ) {
			$list_classes[] = 'simpay-quantity-radio';
		}

		ob_start();
		?>

		<fieldset class="simpay-form-control simpay-radio-container">
			<div class="simpay-<?php echo esc_attr( self::$settings['type'] ); ?>-label simpay-label-wrap">
				<legend class="<?php echo esc_attr( implode( ' ', $label_classes ) ); ?>">
					<?php echo esc_html( $label ); ?>
				<legend>
			</div>
			<div class="<?php echo esc_attr( implode( ' ', $list_classes ) ); ?>">
				<ul>
					<?php
					if ( $is_quantity ) :
						echo self::get_quantity_items( $settings );
					else :
						echo self::get_standard_items( $settings );
					endif;
					?>
				</ul>
			</div>
		</fieldset>

		<?php
		return ob_get_clean();
	}

	/**
	 * Returns HTML for standard radios.
	 *
	 * @since 3.9.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	private static function get_standard_items( $settings ) {
		$options      = self::get_options( $settings );
		$items_markup = '';

		foreach ( $options as $v ) {
			$items_markup .= (
				'<li>' .
					'<label>' .
						'<input type="radio" name="' . self::$name . '" value="' . esc_attr( $v ) . '" ' . checked( $v, self::$default, false ) . ' />' .
						esc_html( $v ) .
					'</label>' .
				'</li>'
			);
		}

		return $items_markup;
	}

	/**
	 * Returns HTML for quantity radios.
	 *
	 * @since 3.9.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	private static function get_quantity_items( $settings ) {
		$options      = self::get_options( $settings );
		$quantities   = self::get_quantities( $settings );
		$items_markup = '';

		// Make sure the number of options and amounts is equal before continuing.
		if ( count( $options ) !== count( $quantities ) ) {
			return (
				'<div style="color: red;">' .
					esc_html__( 'You have a mismatched number of options and amounts. Please correct this for the items to appear.', 'simple-pay' ) .
				'</div>'
			);
		}

		foreach ( $options as $i => $v ) {
			$items_markup .= (
				'<li>' .
					'<label>' .
						'<input type="radio" name="' . self::$name . '" value="' . esc_attr( $v ) . '" ' . checked( $v, self::$default, false ) . ' data-quantity="' . esc_attr( $quantities[ $i ] ) . '" />' .
						esc_html( $v ) .
					'</label>' .
				'</li>'
			);
		}

		$items_markup .= '<input type="hidden" name="simpay_quantity" class="simpay-quantity" value="" />';

		return $items_markup;
	}

	/**
	 * Returns HTML for amount radios.
	 *
	 * @since 3.9.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	private static function get_amount_items( $settings ) {
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
