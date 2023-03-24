<?php
/**
 * Forms field: Tax ID
 *
 * @package SimplePay\Pro\Forms\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.2.0
 */

namespace SimplePay\Pro\Forms\Fields;

use SimplePay\Core\i18n;
use SimplePay\Core\Abstracts\Custom_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tax_ID class.
 *
 * @since 4.2.0
 */
class Tax_ID extends Custom_Field {

	/**
	 * Prints HTML for field on frontend.
	 *
	 * @since 4.2.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {
		$id          = self::get_id_attr();
		$placeholder = isset( $settings['placeholder'] ) ? $settings['placeholder'] : '';
		$required    = isset( $settings['required'] );

		ob_start();
		?>

		<div class="simpay-form-control simpay-tax-id-container">
			<?php echo self::get_label(); // WPCS: XSS okay. ?>
			<div class="simpay-tax-id-wrap simpay-tax-id-field simpay-field-wrap">
				<select
					name="simpay_tax_id_type"
					class="simpay-tax-id-type"
				>
					<?php
					echo self::get_tax_id_type_options(); // WPCS: XSS okay.
					?>
				</select>

				<input
					type="text"
					name="simpay_tax_id"
					id="<?php echo esc_attr( $id ); ?>"
					class="simpay-tax-id"
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

	/**
	 * Returns Tax ID type options HTML markup.
	 *
	 * @since 4.2.0
	 *
	 * @return string
	 */
	private static function get_tax_id_type_options() {
		$tax_id_types = i18n\get_stripe_tax_id_types();
		$options      = array_merge(
			array(
				'' => __( 'Select ID type&hellip;', 'simple-pay' ),
			),
			$tax_id_types
		);

		$options_markup = array();

		foreach ( $options as $tax_id_type => $tax_id_label ) {
			$options_markup[] = sprintf(
				'<option value="%s">%s</option>',
				esc_html( $tax_id_type ),
				esc_html( $tax_id_label )
			);
		}

		return implode( '', $options_markup );
	}

}
