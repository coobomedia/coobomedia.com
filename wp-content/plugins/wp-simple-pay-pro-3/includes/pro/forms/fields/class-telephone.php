<?php
/**
 * Forms field: Telephone
 *
 * @package SimplePay\Pro\Forms\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Pro\Forms\Fields;

use SimplePay\Core\Abstracts\Custom_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Telephone class.
 *
 * @since 3.5.0
 */
class Telephone extends Custom_Field {

	/**
	 * Prints HTML for field on frontend.
	 *
	 * @since 3.5.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {
		$id          = self::get_id_attr();
		$default     = self::get_default_value();
		$placeholder = isset( $settings['placeholder'] ) ? $settings['placeholder'] : '';
		$required    = isset( $settings['required'] );

		ob_start();
		?>

		<div class="simpay-form-control simpay-telephone-container">
			<?php echo self::get_label(); // WPCS: XSS okay. ?>
			<div class="simpay-telephone-wrap simpay-telephone-field simpay-field-wrap">
				<input
					type="tel"
					name="simpay_telephone"
					id="<?php echo esc_attr( $id ); ?>"
					class="simpay-telephone"
					value="<?php echo esc_attr( $default ); ?>"
					placeholder="<?php echo esc_attr( $placeholder ); ?>"
					<?php if ( true === $required ) : ?>
						required
					<?php endif; ?>
				/>
			</div>
		</div>

		<?php
		return ob_get_clean();
	}

}
