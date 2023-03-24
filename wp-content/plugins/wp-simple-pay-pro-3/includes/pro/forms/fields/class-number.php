<?php
/**
 * Forms field: Number
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
 * Number class.
 *
 * @since 3.0.0
 */
class Number extends Custom_Field {

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
		$default     = self::get_default_value();
		$placeholder = isset( $settings['placeholder'] ) ? $settings['placeholder'] : '';
		$required    = isset( $settings['required'] );

		$meta_name = isset( $settings['metadata'] ) && ! empty( $settings['metadata'] )
			? $settings['metadata']
			: $id;
		$name      = 'simpay_field[' . esc_attr( $meta_name ) . ']';

		$min = isset( $settings['minimum'] )
			? intval( $settings['minimum'] )
			: '';

		$max = isset( $settings['maximum'] )
			? intval( $settings['maximum'] )
			: '';

		$quantity = isset( $settings['quantity'] )
			? $settings['quantity']
			: '';

		$classes = '';

		if ( ! empty( $quantity ) ) {
			$classes .= 'simpay-quantity-input';
		}

		ob_start();
		?>

		<div class="simpay-form-control simpay-number-container">
			<div class="simpay-number-label simpay-label-wrap">
				<?php echo self::get_label(); // WPCS: XSS okay. ?>
				<div class="simpay-number-wrap simpay-field-wrap">
					<input
						type="number"
						name="<?php echo esc_attr( $name ); ?>"
						class="<?php echo esc_attr( ! empty( $quantity ) ? 'simpay-quantity-input' : '' ); ?>"
						id="<?php echo esc_attr( $id ); ?>"
						placeholder="<?php echo esc_attr( $placeholder ); ?>"
						value="<?php echo esc_attr( $default ); ?>"
						step="1"
						<?php if ( ! empty( $min ) ) : ?>
							min="<?php echo esc_attr( $min ); ?>"
						<?php endif; ?>
						<?php if ( ! empty( $max ) ) : ?>
							max="<?php echo esc_attr( $max ); ?>"
						<?php endif; ?>
						<?php if ( true === $required ) : ?>
							required
						<?php endif; ?>
					/>
				</div>
			</div>
		</div>

		<?php if ( ! empty( $quantity ) ) : ?>
			<input type="hidden" name="simpay_quantity" class="simpay-quantity" value="" />
		<?php endif; ?>

		<?php
		return ob_get_clean();
	}

}
