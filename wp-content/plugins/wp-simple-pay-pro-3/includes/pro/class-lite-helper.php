<?php
/**
 * "Lite Helper"
 *
 * @todo Move all of these things in to more organized areas.
 *
 * @package SimplePay\Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro;

use SimplePay\Pro\Forms\Pro_Form;
use SimplePay\Pro\Payments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lite_Helper class.
 *
 * @since 3.0.0
 */
class Lite_Helper {

	/**
	 * Hooks in to WordPress.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		add_filter( 'simpay_upgrade_link', array( $this, 'pro_upgrade_link' ), 10, 3 );
		add_filter( 'simpay_utm_campaign', array( $this, 'pro_ga_campaign' ) );

		// Load the pro shared script variables.
		add_filter( 'simpay_shared_script_variables', array( $this, 'pro_shared_script_variables' ), 11 );

		// We need to make our object factory use the Pro_Form and not the Default_Form for form objects.
		add_filter( 'simpay_form_object_type', array( $this, 'pro_form_object' ) );
		add_filter( 'simpay_form_namespace', array( $this, 'pro_object_namespace' ) );

		// Use Pro form instead of Default_Form.
		add_filter( 'simpay_form_view', array( $this, 'load_pro_form' ), 10, 2 );
	}

	/**
	 * Filters form loading to load a \SimplePay\Pro\Forms\Pro_Form object.
	 *
	 * @since 3.0.0
	 *
	 * @param string     $view Unused.
	 * @param string|int $id Payment Form ID.
	 * @return \SimplePay\Pro\Forms\Pro_Form
	 */
	public function load_pro_form( $view, $id ) {
		return new Pro_Form( $id );
	}

	/**
	 * Sets the namespace for Pro objects.
	 *
	 * @todo Remove this/find out what this does.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function pro_object_namespace() {
		return 'SimplePay\\Pro';
	}

	/**
	 * Sets the prefix for Pro objects.
	 *
	 * @todo Remove this/find out what this does.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function pro_form_object() {
		return 'pro-form';
	}

	/**
	 * Replaces the Google Analytics campaign name.
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public function pro_ga_campaign() {
		return 'pro-plugin';
	}

	/**
	 * Replaces the Pro upgrade link with additional tracking.
	 *
	 * @since 3.0.0
	 *
	 * @param string $link Upgrade link.
	 * @param string $utm_medium utm_medium paramter.
	 * @param string $utm_content utm_content parameter.
	 * @return string
	 */
	public function pro_upgrade_link( $link, $utm_medium, $utm_content ) {
		$key = simpay_get_license()->get_key();
		$url = "https://wpsimplepay.com/pricing/?license_key={$key}";

		return simpay_ga_url( $url, $utm_medium, $utm_content );
	}

	/**
	 * Adds additional script data for Pro forms.
	 *
	 * @since 3.0.0
	 *
	 * @param array $arr Script data.
	 * @return array
	 */
	public function pro_shared_script_variables( $arr ) {

		$i18n['i18n'] = array_merge(
			isset( $arr['i18n'] ) ? $arr['i18n'] : array(),
			array(
				/* translators: Coupon percentage off. */
				'couponPercentOffText' => esc_html_x(
					'%s off',
					'This is for the coupon percent off text on the frontend. i.e. 10% off',
					'simple-pay'
				),
				/* translators: Coupon amount off. */
				'couponAmountOffText'  => esc_html_x(
					'%s off',
					'This is for coupon amount off on the frontend. i.e. $3.00 off',
					'simple-pay'
				),
			)
		);

		$integers['integers'] = array_merge(
			isset( $arr['integers'] ) ? $arr['integers'] : array(),
			array(
				'minAmount' => simpay_global_minimum_amount(),
			)
		);

		return array_merge( $arr, $i18n, $integers );
	}

}
