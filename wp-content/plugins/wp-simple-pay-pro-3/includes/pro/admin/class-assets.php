<?php
/**
 * Admin: Assets
 *
 * @package SimplePay
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets class
 *
 * @since 3.0.0
 */
class Assets {

	/**
	 * Hooks in to WordPress.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		add_filter( 'simpay_before_register_admin_scripts', array( $this, 'add_admin_scripts' ) );

		add_filter( 'simpay_before_register_admin_styles', array( $this, 'add_admin_styles' ) );
	}

	/**
	 * Registers admin scripts.
	 *
	 * @since 3.0.0
	 *
	 * @param array $scripts Scripts to register.
	 * @return array
	 */
	public function add_admin_scripts( $scripts ) {

		$scripts['simpay-admin-pro'] = array(
			'src'    => SIMPLE_PAY_INC_URL . 'pro/assets/js/simpay-admin-pro.min.js',
			'deps'   => array(
				'jquery',
				'jquery-ui-datepicker',
				'thickbox',
				'jquery-ui-dialog',
				'wp-api',
				'wp-util',
				'wp-backbone',
				'wp-a11y',
				'underscore',
				'clipboard',
				'simpay-admin',
			),
			'ver'    => SIMPLE_PAY_VERSION,
			'footer' => false,
		);

		return $scripts;
	}

	/**
	 * Registers admin styles.
	 *
	 * @since 3.0.0
	 *
	 * @param array $styles Styles to register.
	 * @return array
	 */
	public function add_admin_styles( $styles ) {

		$styles['simpay-admin-pro'] = array(
			'src'   => SIMPLE_PAY_INC_URL . 'pro/assets/css/simpay-admin-pro.min.css',
			'deps'  => array(
				'wp-jquery-ui-dialog',
			),
			'ver'   => SIMPLE_PAY_VERSION,
			'media' => 'all',
		);

		return $styles;
	}
}
