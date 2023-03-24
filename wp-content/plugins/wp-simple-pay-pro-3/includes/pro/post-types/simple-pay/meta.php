<?php
/**
 * Post Types: Simple Pay
 *
 * @package SimplePay\Pro\Post_Types\Simple_Pay
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Pro\Post_Types\Simple_Pay;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers `simple-pay` meta fields to store Payment Form settings.
 *
 * @since 3.8.0
 */
function register() {
	// Amount type.
	register_post_meta(
		'simple-pay',
		'_amount_type',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form amount type.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Display type.
	register_post_meta(
		'simple-pay',
		'_form_display_type',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form display type.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Stripe Checkout form styles.
	register_post_meta(
		'simple-pay',
		'_enable_stripe_chekcout_form_styles',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form Stripe Checkout form styles.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Minimum amount.
	register_post_meta(
		'simple-pay',
		'_minimum_amount',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form minimum amount.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Custom amount default.
	register_post_meta(
		'simple-pay',
		'_custom_amount_default',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form custom amount default.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Custom amount label.
	register_post_meta(
		'simple-pay',
		'_custom_amount_label',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form custom amount label.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Subscription Plan label.
	register_post_meta(
		'simple-pay',
		'_plan_select_form_field_label',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form Subscription Plan label.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Form display type.
	register_post_meta(
		'simple-pay',
		'_form_display_type',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form display type.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Subscription type.
	register_post_meta(
		'simple-pay',
		'_subscription_type',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form Subscription type.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Subscription display type.
	register_post_meta(
		'simple-pay',
		'_multi_plan_display',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form multi-Subscription display type (radio or list).', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Subscription single Plan ID.
	register_post_meta(
		'simple-pay',
		'_single_plan',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form single Subscription Plan ID.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Subscription initial setup fee.
	register_post_meta(
		'simple-pay',
		'_setup_fee',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form Subscription Plan initial setup fee.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Subscription max charges.
	register_post_meta(
		'simple-pay',
		'_max_charges',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form single Subscription Plan max charges.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Subscription custom Plan label.
	register_post_meta(
		'simple-pay',
		'_custom_plan_label',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form custom Subscription Plan label.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Subscription Plans.
	register_post_meta(
		'simple-pay',
		'_multi_plan',
		array(
			'type'              => 'array',
			'description'       => __( 'Payment Form Subscription Plans.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => null,
		)
	);

	// Subscription Plans default.
	register_post_meta(
		'simple-pay',
		'_multi_plan_default_value',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form Subscription Plans default.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Subscription custom amount.
	register_post_meta(
		'simple-pay',
		'_subscription_custom_amount',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form Subscription Plans custom amount.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Subscription custom amount minimum.
	register_post_meta(
		'simple-pay',
		'_multi_plan_minimum_amount',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form Subscription Plans custom amount minimum.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Subscription custom amount default.
	register_post_meta(
		'simple-pay',
		'_multi_plan_default_amount',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form Subscription Plans custom amount.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	// Custom Subscription interval.
	register_post_meta(
		'simple-pay',
		'_plan_interval',
		array(
			'type'              => 'string',
			'description'       => __( 'Payment Form custom Subscription Plan interval.', 'simple-pay' ),
			'single'            => true,
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
}
add_action( 'init', __NAMESPACE__ . '\\register', 20 );
