<?php
/**
 * Assets
 *
 * @package SimplePay\Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Assets class.
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

		add_filter( 'simpay_before_register_public_scripts', array( $this, 'add_public_scripts' ) );

		add_filter( 'simpay_before_register_public_styles', array( $this, 'add_public_styles' ), 10, 2 );
	}

	/**
	 * Register public scripts.
	 *
	 * @since 3.0.0
	 *
	 * @param array $scripts Scripts to register.
	 * @return array
	 */
	public function add_public_scripts( $scripts ) {
		$scripts['simpay-public-pro'] = array(
			'src'    => SIMPLE_PAY_INC_URL . 'pro/assets/js/simpay-public-pro.min.js',
			'deps'   => array(
				'jquery',
				'simpay-accounting',
				'simpay-shared',
				'simpay-public',
			),
			'ver'    => SIMPLE_PAY_VERSION,
			'footer' => true,
		);

		return $scripts;
	}

	/**
	 * Register public styles.
	 *
	 * @since 3.0.0
	 *
	 * @param array $styles Styles to register.
	 * @return array
	 */
	public function add_public_styles( $styles ) {
		$styles['simpay-public-pro'] = array(
			'src'   => SIMPLE_PAY_INC_URL . 'pro/assets/css/simpay-public-pro.min.css',
			'deps'  => array(),
			'ver'   => SIMPLE_PAY_VERSION,
			'media' => 'all',
		);

		return $styles;
	}
}
