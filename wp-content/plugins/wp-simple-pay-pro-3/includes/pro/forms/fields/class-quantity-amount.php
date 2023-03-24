<?php
/**
 * Forms field: Quantity/Amount
 *
 * @package SimplePay\Pro\Forms\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.9.0
 */

namespace SimplePay\Pro\Forms\Fields;

use SimplePay\Core\Abstracts\Custom_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quantity/Amount shared.
 *
 * @since 3.9.0
 */
class Quantity_Amount extends Custom_Field {

	/**
	 * ID attribute value.
	 *
	 * @var string
	 */
	protected static $id;

	/**
	 * Name attribute value.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Required.
	 *
	 * @var bool
	 */
	protected static $required;

	/**
	 * Default value.
	 *
	 * @var string
	 */
	protected static $default;

	/**
	 * Sets shared properties.
	 *
	 * @since 3.9.0
	 *
	 * @param array $settings Field settings.
	 */
	protected static function set_properties( $settings ) {
		self::$id = self::get_id_attr();

		self::$required = isset( $settings['required'] );

		$meta_name = isset( $settings['metadata'] ) && ! empty( $settings['metadata'] )
			? $settings['metadata']
			: self::$id;

		self::$name = 'simpay_field[' . $meta_name . ']';

		$default = self::get_default_value();
		$options = self::get_options( $settings );

		self::$default = array_search( $default, $options, true )
			? $default
			: current( $options );
	}

	/**
	 * Returns the list of options set in the settings.
	 *
	 * @since 3.9.0
	 *
	 * @param array $settings Field settings.
	 * @return array
	 */
	protected static function get_options( $settings ) {
		$options = isset( $settings['options'] )
			? $settings['options']
			: '';

		$list = explode( simpay_list_separator(), $options );

		return array_map( 'trim', $list );
	}

	/**
	 * Returns the list of quantities set in the settings.
	 *
	 * @since 3.9.0
	 *
	 * @param array $settings Field settings.
	 * @return array
	 */
	protected static function get_quantities( $settings ) {
		$quantities = isset( $settings['quantities'] )
			? $settings['quantities']
			: '';

		$list = explode( simpay_list_separator(), $quantities );

		return array_map( 'trim', $list );
	}

	/**
	 * Returns the list of amounts set in the settings.
	 *
	 * @since 3.9.0
	 *
	 * @param array $settings Field settings.
	 * @return array
	 */
	protected static function get_amounts( $settings ) {
		$amounts = isset( $settings['amounts'] )
			? $settings['amounts']
			: '';

		$list = explode( simpay_list_separator(), $amounts );

		return array_map( 'trim', $list );
	}

}
