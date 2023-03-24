<?php
/**
 * Coupons
 *
 * @package SimplePay\Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SimplePay\Pro\Coupons\Coupon;
use SimplePay\Pro\Coupons\Coupon_Query;

/**
 * Validate a coupon based on Stripe settings.
 *
 * @since 3.5.0
 *
 * @param \SimplePay\Vendor\Stripe\Coupon $coupon Stripe coupon.
 * @return bool
 */
function simpay_is_coupon_valid( $coupon ) {
	$valid = true;

	// If coupon is not found then exit now.
	if ( false === $coupon ) {
		$valid = false;
	}

	// Generally invalid.
	if ( ! $coupon->valid ) {
		$valid = false;
	}

	// Used too many times.
	if ( $coupon->max_redemptions && ( $coupon->times_redeemed === $coupon->max_redemptions ) ) {
		$valid = false;
	}

	// Expired.
	if ( $coupon->redeem_by && ( time() > $coupon->redeem_by ) ) {
		$valid = false;
	}

	/**
	 * Filter coupon validity.
	 *
	 * @since 3.5.0
	 *
	 * @param bool $valid If the coupon is valid or not.
	 * @param object $coupon Stripe coupon.
	 */
	return apply_filters( 'simpay_is_coupon_valid', $valid, $coupon );
}

/**
 * Creates a translated string for invalid (missing) coupons.
 *
 * Detects $_POST data for coupon validation and filters the localization
 * list to provide a coupon-specific string for the `resource_missing` code.
 *
 * @since 3.9.3
 *
 * @param array $error_list List of error codes and corresponding error messages.
 * @return array $error_list List of error codes and corresponding error messages.
 */
function _simpay_invalid_coupon_localized_error( $error_list ) {
	if ( ! isset( $_POST['coupon'] ) ) {
		return $error_list;
	}

	$error_list['resource_missing'] = __( 'Coupon is invalid.', 'simple-pay' );

	return $error_list;
}
add_filter( 'simpay_get_localized_error_list', '_simpay_invalid_coupon_localized_error' );

/**
 * Ensures the Customer's `coupon` attribute can be used on the current form.
 *
 * This is done in the filter because coupons are not available in the Core namespace.
 *
 * @since 4.3.0
 *
 * @param array                          $customer_args Stripe customer arguments.
 * @param \SimplePay\Core\Abstracts\Form $form Payment form.
 * @return array
 */
function validate_coupon_for_payment_form( $customer_args, $form ) {
	if ( ! isset( $customer_args['coupon'] ) ) {
		return $customer_args;
	}

	try {
		// Look at internal records first to force a sync between modes.
		$api_args = $form->get_api_request_args();
		$coupons  = new Coupon_Query(
			$form->is_livemode(),
			$api_args['api_key']
		);

		$coupon = $coupons->get_by_name( $customer_args['coupon'] );

		// No internal record so no further validation can/should be done.
		if ( ! $coupon instanceof Coupon ) {
			return $customer_args;
		}

		// Remove coupon if it does not apply to the form.
		if ( false === $coupon->applies_to_form( $form->id ) ) {
			unset( $customer_args['coupon'] );
		}

		// Something went wrong retrieving, assume it is invalid.
	} catch ( Exception $e ) {
		unset( $customer_args['coupon'] );
	}

	// Clear Stripe object cache so dynamic values are available.
	// @todo implement cache clearing within Stripe_Object_Query_Trait
	// when it is available in this namespace.
	delete_transient( 'simpay_stripe_' . $customer_args['coupon'] );

	return $customer_args;
}
add_filter(
	'simpay_get_customer_args_from_payment_form_request',
	__NAMESPACE__ . '\\validate_coupon_for_payment_form',
	10,
	2
);
