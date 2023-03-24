<?php
/**
 * Forms field: Customer Name
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
 * Customer_Name class.
 *
 * @since 3.0.0
 */
class Customer_Name extends Custom_Field {

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

		ob_start();
		?>

		<div class="simpay-form-control simpay-name-container simpay-customer-name-container">
			<?php echo self::get_label(); // WPCS: XSS okay. ?>
			<div class="simpay-customer-name-wrap simpay-field-wrap">
				<input
					type="text"
					name="simpay_customer_name"
					id="<?php echo esc_attr( $id ); ?>"
					class="simpay-customer-name"
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
