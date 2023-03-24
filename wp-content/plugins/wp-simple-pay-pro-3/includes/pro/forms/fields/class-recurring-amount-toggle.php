<?php
/**
 * Forms field: Recurring Amount Toggle
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
 * Recurring_Amount_Toggle class.
 *
 * @since 3.0.0
 */
class Recurring_Amount_Toggle extends Custom_Field {

	/**
	 * Prints HTML for field on frontend.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {
		$id = self::get_id_attr();

		ob_start();
		?>

		<div
			class="simpay-form-control simpay-recurring-amount-toggle-container"
			style="display: none;"
		>
			<div class="simpay-checkbox-wrap simpay-field-wrap">
				<input
					type="checkbox"
					name="recurring_amount_toggle"
					id="<?php echo esc_attr( $id ); ?>"
					class="simpay-recurring-amount-toggle"
				/>
				<?php echo self::get_label(); // WPCS: XSS okay. ?>
			</div>
		</div>

		<?php
		return ob_get_clean();
	}

}
