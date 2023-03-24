<?php
/**
 * Forms field: Date
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
 * Date class.
 *
 * @since 3.0.0
 */
class Date extends Custom_Field {

	/**
	 * Prints HTML for field on frontend.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'wp-jquery-ui-dialog' );

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

		<div class="simpay-form-control simpay-date-container">
			<div class="simpay-date-label simpay-label-wrap">
				<?php echo self::get_label(); // WPCS: XSS okay. ?>
				<div class="simpay-date-wrap simpay-field-wrap">
					<input
						type="text"
						name="<?php echo esc_attr( $name ); ?>"
						class="simpay-date-input"
						id="<?php echo esc_attr( $id ); ?>"
						placeholder="<?php echo esc_attr( $placeholder ); ?>"
						value="<?php echo esc_attr( $default ); ?>"
						<?php if ( true === $required ) : ?>
							required
						<?php endif; ?>
					/>
				</div>
			</div>
		</div>

		<?php
		return ob_get_clean();
	}

}
