<?php
/**
 * Coupons: Actions
 *
 * @package SimplePay
 * @subpackage Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.3.0
 */

namespace SimplePay\Pro\Coupons\Admin;

use Exception;
use SimplePay\Pro\Coupons\Coupon;
use SimplePay\Pro\Coupons\Coupon_Query;
use function SimplePay\Pro\Coupons\sanitize_coupon_name;
use SimplePay\Vendor\Stripe\Exception\ApiErrorException;

/**
 * Adds a coupon when the "Add Coupon" form is posted.
 *
 * @since 4.3.0
 *
 * @return void
 */
function add_coupon() {
	if ( ! isset( $_POST['simpay-action'] ) ) {
		return;
	}

	$action = sanitize_text_field( $_POST['simpay-action'] );

	if ( 'add-coupon' !== $action ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if (
		! isset( $_POST['_wpnonce'] ) ||
		false === wp_verify_nonce( $_POST['_wpnonce'], 'simpay-add-coupon' )
	) {
		return;
	}

	$coupon      = $_POST['coupon'];
	$coupon_args = array(
		'id'   => sanitize_coupon_name( $coupon['name'] ),
		'name' => sanitize_coupon_name( $coupon['name'] ),
	);

	// Validate and set type.
	switch ( $coupon['type'] ) {
		case 'percent_off':
			$percent_off = floatval( $coupon['percent_off'] );

			if ( $percent_off > 99.99 ) {
				$percent_off = 99.99;
			}

			if ( $percent_off < 0.01 ) {
				$percent_off = 0.01;
			}

			$coupon_args['percent_off'] = $percent_off;
			$coupon_args['amount_off']  = null;
			$coupon_args['currency']    = null;

			break;
		case 'amount_off':
			$amount_off = floatval( $coupon['amount_off'] );
			$currency   = strtolower( sanitize_text_field( $coupon['currency'] ) );

			if ( $amount_off < 0.01 ) {
				$amount_off = 0.01;
			}

			if ( false === simpay_is_zero_decimal( $currency ) ) {
				$amount_off = $amount_off * 100;
			}

			$coupon_args['amount_off']  = $amount_off;
			$coupon_args['currency']    = $currency;
			$coupon_args['percent_off'] = null;
	}

	// Validate and set duration.
	$coupon_args['duration'] = sanitize_text_field( $coupon['duration'] );

	switch ( $coupon['duration'] ) {
		case 'forever':
		case 'once':
			$coupon_args['duration_in_months'] = null;

			break;
		case 'repeating':
			$coupon_args['duration_in_months'] = intval(
				$coupon['duration_in_months']
			);

			break;
	}

	// Validate and set redemption limits.
	if ( isset( $coupon['redeem_by_toggle'] ) ) {
		$date = sanitize_text_field( $coupon['redeem_by_date'] );
		$time = sanitize_text_field( $coupon['redeem_by_time'] );

		$datetime = $date . ' ' . $time;

		if ( strtotime( $datetime ) ) {
			$coupon_args['redeem_by'] = get_gmt_from_date( $datetime, 'U' );
		}
	}

	if ( isset( $coupon['max_redemptions_toggle'] ) ) {
		$coupon_args['max_redemptions'] = intval( $coupon['max_redemptions'] );
	}

	// Form restrictions.
	if ( isset( $coupon['applies_to_forms'] ) ) {
		$applies_to_forms = array_map( 'intval', $coupon['applies_to_forms'] );

		$coupon_args['applies_to_forms'] = serialize( $applies_to_forms );
	}

	try {
		$coupons = new Coupon_Query(
			simpay_is_livemode(),
			simpay_get_secret_key()
		);

		$coupon = $coupons->add_synced_item( $coupon_args );

		if ( ! $coupon instanceof Coupon ) {
			throw new Exception(
				__( 'Unable to add coupon. Please try again.', 'simple-pay' )
			);
		}

		$redirect_url = add_query_arg(
			array(
				'post_type' => 'simple-pay',
				'page'      => 'simpay_coupons',
				'message'   => 'coupon-added',
				'coupon'    => $coupon->name,
			),
			admin_url( 'edit.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	} catch ( ApiErrorException $e ) {
		$code = $e->getStripeCode()
			? $e->getStripeCode()
			: $e->getError()->type;

		switch ( $code ) {
			// If the coupon already exists, link them to it.
			case 'resource_already_exists':
				$coupon_name = $coupon_args['name'];

				wp_die(
					wp_kses(
						sprintf(
							/* translators: %1$s Coupon name. */
							__(
								'A coupon named %1$s already exists. Please delete the existing coupon before adding it again.',
								'simple-pay'
							),
							'<strong>' . $coupon_name . '</strong>'
						),
						array(
							'strong' => array(),
						)
					),
					null,
					array(
						'back_link' => true,
					)
				);

				break;
		}

		wp_die(
			$e->getMessage(),
			null,
			array(
				'back_link' => true,
			)
		);
	} catch ( Exception $e ) {
		wp_die(
			$e->getMessage(),
			null,
			array(
				'back_link' => true,
			)
		);
	}
}
add_action( 'admin_init', __NAMESPACE__ . '\\add_coupon' );

/**
 * Deletes a coupon when a relevant action link is visited.
 *
 * @since 4.3.0
 *
 * @return void
 */
function delete_coupon() {
	if ( ! isset( $_GET['simpay-action'] ) ) {
		return;
	}

	$action = sanitize_text_field( $_GET['simpay-action'] );

	if ( 'delete-coupon' !== $action ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if (
		! isset( $_GET['_wpnonce'] ) ||
		false === wp_verify_nonce( $_GET['_wpnonce'], 'simpay-delete-coupon' )
	) {
		return;
	}

	$coupon_id = isset( $_GET['id'] )
		? sanitize_text_field( $_GET['id'] )
		: false;

	if ( false === $coupon_id ) {
		return;
	}

	try {
		$coupons = new Coupon_Query(
			simpay_is_livemode(),
			simpay_get_secret_key()
		);

		$coupon = $coupons->delete_synced_item( $coupon_id );

		$redirect_url = add_query_arg(
			array(
				'post_type' => 'simple-pay',
				'page'      => 'simpay_coupons',
				'message'   => 'coupon-deleted',
				'coupon'    => $coupon->name,
			),
			admin_url( 'edit.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	} catch ( \Exception $e ) {
		wp_die(
			$e->getMessage(),
			null,
			array(
				'back_link' => true,
			)
		);
	}
}
add_action( 'admin_init', __NAMESPACE__ . '\\delete_coupon' );

/**
 * Processes bulk actions.
 *
 * @since 4.3.0
 *
 * @return void
 */
function process_bulk_actions() {
	if (
		! isset( $_GET['_wpnonce'] ) ||
		false === wp_verify_nonce( $_GET['_wpnonce'], 'bulk-coupons' )
	) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! isset( $_GET['coupons'] ) ) {
		return;
	}

	$coupons = new Coupon_Query(
		simpay_is_livemode(),
		simpay_get_secret_key()
	);

	$action = isset( $_GET['action'] )
		? sanitize_text_field( $_GET['action'] )
		: null;
	$ids    = array_map( 'intval', $_GET['coupons'] );
	$count  = 0;

	switch ( $action ) {
		case 'delete':
			foreach ( $ids as $id ) {
				$deleted = $coupons->delete_synced_item( $id );

				if ( $deleted instanceof Coupon ) {
					$count++;
				}
			}

			$message = 'coupons-deleted';

			break;
	}

	$redirect_url = add_query_arg(
		array(
			'post_type' => 'simple-pay',
			'page'      => 'simpay_coupons',
			'message'   => $message,
			'count'     => $count,
		),
		admin_url( 'edit.php' )
	);

	wp_safe_redirect( $redirect_url );
	exit;
}
add_action( 'admin_init', __NAMESPACE__ . '\\process_bulk_actions' );
