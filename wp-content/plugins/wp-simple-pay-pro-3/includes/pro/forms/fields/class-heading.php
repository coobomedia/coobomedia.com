<?php
/**
 * Forms field: Label
 *
 * @package SimplePay\Pro\Forms\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Pro\Forms\Fields;
use SimplePay\Core\Abstracts\Custom_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Heading class.
 *
 * @since 3.8.0
 */
class Heading extends Custom_Field {

	/**
	 * Prints HTML for field on frontend.
	 *
	 * @since 3.8.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {
		$id    = isset( $settings['id'] ) ? simpay_dashify( $settings['id'] ) : '';
		$label = isset( $settings['label'] )
			? $settings['label']
			: '';
		$level = isset( $settings['level'] )
			? $settings['level']
			: '2';

		ob_start();
		?>

<div id="<?php echo esc_attr( $id ); ?>" class="simpay-form-control">
	<h<?php echo esc_attr( $level ); ?>>
		<?php echo esc_html( $label ); ?>
	</h<?php echo esc_attr( $level ); ?>>
</div>

		<?php
		return ob_get_clean();
	}

}
