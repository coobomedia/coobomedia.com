<?php
/**
 * Forms field: Email
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
 * Email class.
 *
 * @since 3.0.0
 */
class Email extends Custom_Field {

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

		ob_start();
		?>

		<div class="simpay-form-control simpay-email-container">
			<?php echo self::get_label(); // WPCS: XSS okay. ?>
			<div class="simpay-email-wrap simpay-field-wrap">
				<input
					type="email"
					name="simpay_email"
					id="<?php echo esc_attr( $id ); ?>"
					class="simpay-email"
					value="<?php echo esc_attr( $default ); ?>"
					placeholder="<?php echo esc_attr( $placeholder ); ?>"
					required
				/>
			</div>
		</div>

		<?php
		return ob_get_clean();
	}

}
