<?php
/**
 * Simple Pay: Edit form Subscription options
 *
 * @package SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds hidden fields for Subscription settings that control
 * the visibility of other settings when Subscriptions are not
 * available on the current install.
 *
 * @since 3.8.1
 */
function add_hidden_subscription_options() {
	_doing_it_wrong(
		__FUNCTION__,
		esc_html__( 'No longer used.', 'simple-pay' ),
		'4.1.0'
	);
}

/**
 * Adds "Subscription Options" Payment Form settings tab content.
 *
 * @since 3.8.0
 *
 * @param int $post_id Current Payment Form ID.
 */
function add_subscription_options( $post_id ) {
	_doing_it_wrong(
		__FUNCTION__,
		esc_html__( 'No longer used.', 'simple-pay' ),
		'4.1.0'
	);
}

/**
 * Retrieves the markup for a Subscription Plan.
 *
 * @since 3.8.0
 *
 * @param array $plan         Subscription Plan settings.
 * @param int   $plan_counter Current Plan count.
 * @param int   $post_id      Current Payment Form ID.
 * @return string
 */
function get_subscription_plan( $plan, $plan_counter, $post_id ) {
	_doing_it_wrong(
		__FUNCTION__,
		esc_html__( 'No longer used.', 'simple-pay' ),
		'4.1.0'
	);
}
