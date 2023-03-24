<?php
/**
 * Forms: AJAX
 *
 * @package SimplePay\Pro\Forms
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SimplePay\Core\API;
use SimplePay\Core\Utils;
use SimplePay\Pro\Coupons\Coupon;
use SimplePay\Pro\Coupons\Coupon_Query;

/**
 * Ajax class.
 *
 * @since 3.0.0
 */
class Ajax {

	/**
	 * Ajax constructor.
	 */
	public function __construct() {

		add_action( 'wp_ajax_simpay_get_coupon', array( $this, 'simpay_get_coupon' ) );
		add_action( 'wp_ajax_nopriv_simpay_get_coupon', array( $this, 'simpay_get_coupon' ) );
	}

	/**
	 * Check for a coupon and return the discounted amount.
	 */
	public function simpay_get_coupon() {

		// Check nonce first.
		if ( false === check_ajax_referer( 'simpay_coupon_nonce', 'couponNonce', false ) ) {
			echo esc_html__( 'Coupon security check failed.', 'simple-pay' );
			wp_die();
		}

		$form_id = isset( $_POST['form_id'] )
			? sanitize_text_field( $_POST['form_id'] )
			: false;

		$form = simpay_get_form( $form_id );

		if ( false === $form ) {
			wp_send_json_error(
				array(
					'error' => esc_html__(
						'Unable to locate payment form.',
						'simple-pay'
					),
				)
			);
		}

		$price    = isset( $_POST['price'] )
			? array_map( 'sanitize_text_field', $_POST['price'] )
			: false;

		if ( false === $price ) {
			wp_send_json_error(
				array(
					'error' => esc_html__(
						'Unable to locate price.',
						'simple-pay'
					),
				)
			);
		}

		$code     = sanitize_text_field( $_POST['coupon'] );
		$amount   = floatval( $_POST['amount'] );
		$discount = 0;

		$json = array(
			'amount' => $amount,
			'coupon' => array(
				'code' => $code,
			),
		);

		try {
			// Look at internal records first to force a sync between modes.
			$api_args = $form->get_api_request_args();
			$coupons = new Coupon_Query(
				$form->is_livemode(),
				$api_args['api_key']
			);

			$coupon = $coupons->get_by_name( $code );

			// Fall back to a direct Stripe check.
			if ( ! $coupon instanceof Coupon ) {
				$coupon = API\Coupons\retrieve( $code, $api_args );

			} else {
				// We can only check for restrictions on an internally tracked coupon.
				if ( false === $coupon->applies_to_form( $form->id ) ) {
					return wp_send_json_error(
						array(
							'error' => esc_html__( 'Coupon is invalid.', 'simple-pay' ),
						)
					);
				}

				// Use just the Stripe object of the internal record for the remaining
				// checks to match preexisting direct API usage.
				$coupon = $coupon->object;
			}

			// Invalid coupon.
			if ( ! simpay_is_coupon_valid( $coupon ) ) {
				return wp_send_json_error(
					array(
						'error' => esc_html__( 'Coupon is invalid.', 'simple-pay' ),
					)
				);
			}

			// Check coupon type.
			if ( ! empty( $coupon->percent_off ) ) {

				// Coupon is percent off so handle that.

				$json['coupon']['amountOff'] = $coupon->percent_off;
				$json['coupon']['type']      = 'percent';

				if ( $coupon->percent_off == 100 ) {
					$discount = $amount;
				} else {
					$discount_pct = ( 100 - $coupon->percent_off ) / 100;
					$discount     = $amount - round( $amount * $discount_pct, simpay_get_decimal_places() );
				}
			} elseif ( ! empty( $coupon->amount_off ) ) {

				if ( $coupon->currency !== $price['currency'] ) {
					return wp_send_json_error(
						array(
							'error' => esc_html__(
								'Coupon currency does not match selected price.',
								'simple-pay'
							),
						)
					);
				}

				// Coupon is a set amount off (e,g, $3.00 off).
				if ( simpay_is_zero_decimal() ) {
					$amountOff = $coupon->amount_off;
				} else {
					$amountOff = $coupon->amount_off / 100;
				}

				$json['coupon']['amountOff'] = $amountOff;
				$json['coupon']['type']      = 'amount';

				$discount = simpay_convert_amount_to_cents( $amount - ( $amount - $amountOff ) );

				if ( $discount < 0 ) {
					$discount = 0;
				}
			}

			$min = simpay_convert_amount_to_cents(
				simpay_global_minimum_amount()
			);

			// Check if the coupon puts the total below the minimum amount.
			if ( ( $amount - $discount ) < $min ) {
				echo esc_html__( 'Coupon entered puts the total below the required minimum amount.', 'simple-pay' );
				wp_die();
			} else {

				$json['success'] = true;

				// We want to send the correct amount back to the JS.
				$json['discount'] = $discount;

				// Send back full Stripe Coupon object.
				$json['stripeCoupon'] = $coupon;
			}

			// Return coupon duration for recurring amount label.
			if ( ! empty( $coupon->duration ) ) {
				$json['coupon']['duration'] = $coupon->duration;
			}

			// Return as JSON.
			wp_send_json( $json );
		} catch ( \Exception $e ) {
			wp_send_json_error(
				array(
					'error' => Utils\handle_exception_message( $e ),
				)
			);
		}
	}
}
