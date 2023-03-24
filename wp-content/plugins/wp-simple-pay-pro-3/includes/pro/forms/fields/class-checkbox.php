<?php
/**
 * Forms field: Checkbox
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
 * Checkbox class.
 *
 * @since 3.0.0
 */
class Checkbox extends Custom_Field {

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

		ob_start();
		?>

		<div class="simpay-form-control simpay-checkbox-container">
			<div class="simpay-checkbox-label simpay-label-wrap">
				<div class="simpay-checkbox-wrap simpay-field-wrap">
					<input
						type="checkbox"
						name="<?php echo esc_attr( $name ); ?>"
						class="simpay-checkbox"
						id="<?php echo esc_attr( $id ); ?>"
						<?php if ( true === $required ) : ?>
							required
						<?php endif; ?>
						<?php checked( ! empty( $default ), true ); ?>
					/>
					<?php echo self::get_label(); // WPCS: XSS okay. ?>
				</div>
			</div>
		</div>

		<?php
		return ob_get_clean();
	}

}
