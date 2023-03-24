<?php
/**
 * Forms field: Text
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
 * Text class.
 *
 * @since 3.0.0
 */
class Text extends Custom_Field {

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

		$multiline = isset( $settings['multiline'] );
		$rows      = isset( $settings['rows'] ) && ! empty( $settings['rows'] )
			? intval( $settings['rows'] )
			: 5;

		ob_start();
		?>

		<div class="simpay-form-control simpay-text-container">
			<?php echo self::get_label(); // WPCS: XSS okay. ?>
			<div class="simpay-text-wrap simpay-field-wrap">
				<?php if ( false === $multiline ) : ?>
					<input
						type="text"
						name="<?php echo esc_attr( $name ); ?>"
						id="<?php echo esc_attr( $id ); ?>"
						placeholder="<?php echo esc_attr( $placeholder ); ?>"
						value="<?php echo esc_attr( $default ); ?>"
						maxlength="500"
						<?php if ( true === $required ) : ?>
							required
						<?php endif; ?>
					/>
				<?php else : ?>
					<textarea
						name="<?php echo esc_attr( $name ); ?>"
						id="<?php echo esc_attr( $id ); ?>"
						placeholder="<?php echo esc_attr( $placeholder ); ?>"
						rows="<?php echo esc_attr( $rows ); ?>"
						maxlength="500"
						<?php if ( true === $required ) : ?>
							required
						<?php endif; ?>
						><?php echo esc_html( $default ); ?></textarea>
				<?php endif; ?>
			</div>
		</div>

		<?php
		return ob_get_clean();
	}

}
