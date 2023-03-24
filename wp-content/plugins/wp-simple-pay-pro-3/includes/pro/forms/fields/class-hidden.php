<?php
/**
 * Form Field: Hidden
 *
 * @package SimplePay\Pro\Forms\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.7.0
 */

namespace SimplePay\Pro\Forms\Fields;

use SimplePay\Core\Abstracts\Custom_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hidden class.
 *
 * @since 3.7.0
 */
class Hidden extends Custom_Field {

	/**
	 * Prints HTML for text field on frontend.
	 *
	 * @since 3.7.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {
		$id      = self::get_id_attr();
		$default = self::get_default_value();

		$meta_name = isset( $settings['metadata'] ) && ! empty( $settings['metadata'] )
			? $settings['metadata']
			: $id;
		$name      = 'simpay_field[' . esc_attr( $meta_name ) . ']';

		$field = sprintf(
			'<input type="hidden" name="%1$s" id="%2$s" value="%3$s" />',
			esc_attr( $name ),
			$id,
			$default
		);

		return $field;
	}

}
