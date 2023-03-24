<?php
/**
 * Simple Pay: Actions
 *
 * @package SimplePay\Pro\Post_Types\Simple_Pay\Actions
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Pro\Post_Types\Simple_Pay\Actions;

use SimplePay\Pro\Payments\Plan;
use SimplePay\Pro\Payment_Methods;
use SimplePay\Pro\Payment_Methods\Payment_Method;
use SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Saves the Payment Form's settings.
 *
 * @since 3.8.0
 *
 * @param int                            $post_id Payment Form ID.
 * @param \WP_Post                       $post Payment Form \WP_Post object.
 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
 */
function save( $post_id, $post, $form ) {
	$license       = simpay_get_license();
	$license_level = $license->get_level();

	// Allow settings to save depending on the current mode.
	$livemode = isset( $_POST['_livemode'] ) && '' !== $_POST['_livemode']
		? (bool) absint( $_POST['_livemode'] )
		: '';

	$prev_livemode = get_post_meta( $post_id, '_livemode_prev', true );
	$prev_livemode = '' !== $prev_livemode
		? (bool) $prev_livemode
		: '';

	// Display type.
	$form_type = isset( $_POST['_form_type'] )
		? sanitize_text_field( $_POST['_form_type'] )
		: '';

	$form_display_type = 'off-site' === $form_type
		? 'stripe_checkout'
		: 'embedded';

	if ( isset( $_POST['_is_overlay'] ) ) {
		$form_display_type = 'overlay';
	}

	update_post_meta( $post_id, '_form_display_type', $form_display_type );

	// Payment Methods.

	// Ensure `card` is always enabled.
	$default_payment_methods = array(
		'card' => array(
			'id' => 'card',
		),
	);

	$payment_methods = isset( $_POST['_simpay_payment_methods'] )
		? $_POST['_simpay_payment_methods']
		: $default_payment_methods;

	if ( empty( $payment_methods ) ) {
		$payment_methods = $default_payment_methods;
	}

	// Filter out any payment methods that are not available.
	$payment_methods = array_filter(
		$payment_methods,
		function( $payment_method ) use ( $license_level ) {
			$payment_method = Payment_Methods\get_payment_method( $payment_method['id'] );

			if ( ! $payment_method instanceof Payment_Method ) {
				return false;
			}

			if ( false === $payment_method->is_available() ) {
				return false;
			}

			if ( ! in_array( $license_level, $payment_method->licenses, true ) ) {
				return false;
			}

			return true;
		}
	);

	// Context is no longer used, but it is maintained to avoid an upgrade routine.
	// Eventually it will be removed when form settings storage s restructured.
	$context = 'stripe_checkout' === $form_display_type
		? 'stripe-checkout'
		: 'stripe-elements';

	$payment_methods_context             = array();
	$payment_methods_context[ $context ] = $payment_methods;

	update_post_meta( $post_id, '_payment_methods', $payment_methods_context );

	// Tax.
	$tax_status = sanitize_text_field( $_POST['_tax_status'] );
	update_post_meta( $post_id, '_tax_status', $tax_status );

	if ( 'automatic' === $tax_status ) {
		$tax_code = sanitize_text_field( $_POST['_tax_code'] );
		update_post_meta( $post_id, '_tax_code', $tax_code );

		$set_tax_behavior = get_post_meta( $post_id, '_tax_behavior', true );

		if ( empty( $set_tax_behavior ) && $_POST['_tax_behavior'] ) {
			$tax_behavior = sanitize_text_field( $_POST['_tax_behavior'] );
			update_post_meta( $post_id, '_tax_behavior', $tax_behavior );
		}
	}

	// Stripe Checkout form styles.
	$enable_form_styles = isset( $_POST['_enable_stripe_checkout_form_styles'] )
		? 'yes'
		: 'no';

	update_post_meta( $post_id, '_enable_stripe_checkout_form_styles', $enable_form_styles );
}
add_action( 'simpay_save_form_settings', __NAMESPACE__ . '\\save', 10, 3 );

/**
 * Saves the Payment Form's Custom Fields.
 *
 * @since 4.1.0
 *
 * @param int                            $post_id Payment Form ID.
 * @param \WP_Post                       $post Payment Form \WP_Post object.
 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
 */
function save_custom_fields( $post_id, $post, $form ) {
	// Custom fields.
	$fields = isset( $_POST['_simpay_custom_field'] )
		? $_POST['_simpay_custom_field']
		: array();

	// Form display type.
	$form_display_type = get_post_meta( $post_id, '_form_display_type', true );

	// Check & create required missing fields for this form display type.
	$fields = Edit_Form\add_missing_custom_fields( $fields, $form->id, $form_display_type );

	// Re-index the array so if fields were removed we don't overwrite the index with a new field.
	foreach ( $fields as $k => $v ) {
		$fields[ $k ] = array_values( $v );
	}

	update_post_meta( $post_id, '_custom_fields', $fields );
}
add_action( 'simpay_save_form_settings', __NAMESPACE__ . '\\save_custom_fields', 40, 3 );

/**
 * Saves the Payment Form's Payment Page settings.
 *
 * @since 4.5.0
 *
 * @param int                            $post_id Payment Form ID.
 * @param \WP_Post                       $post Payment Form \WP_Post object.
 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
 */
function save_payment_page( $post_id, $post, $form ) {
	// Enable/Disable.
	$enable_payment_page = isset( $_POST['_enable_payment_page'] )
		? 'yes'
		: 'no';

	update_post_meta( $post_id, '_enable_payment_page', $enable_payment_page );

	// Show Title/Description.
	$payment_page_title_description = isset( $_POST['_payment_page_title_description'] )
		? 'yes'
		: 'no';

	update_post_meta(
		$post_id,
		'_payment_page_title_description',
		$payment_page_title_description
	);

	// Color scheme.
	$background_color = sanitize_hex_color(
		$_POST['_payment_page_background_color']
	);

	update_post_meta(
		$post_id,
		'_payment_page_background_color',
		$background_color
	);

	// Footer text.
	$footer_text = sanitize_text_field(
		$_POST['_payment_page_footer_text']
	);

	update_post_meta(
		$post_id,
		'_payment_page_footer_text',
		$footer_text
	);

	// Powered by.
	$payment_page_powered_by = isset( $_POST['_payment_page_powered_by'] )
		? 'yes'
		: 'no';

	update_post_meta(
		$post_id,
		'_payment_page_powered_by',
		$payment_page_powered_by
	);

	// Image URL.
	$payment_page_image = esc_url( $_POST['_payment_page_image_url'] );

	update_post_meta(
		$post_id,
		'_payment_page_image_url',
		$payment_page_image
	);

	// Self confirmation.
	$payment_page_self_confirmation = isset( $_POST['_payment_page_self_confirmation'] )
		? 'yes'
		: 'no';

	update_post_meta(
		$post_id,
		'_payment_page_self_confirmation',
		$payment_page_self_confirmation
	);
}
add_action(
	'simpay_save_form_settings',
	__NAMESPACE__ . '\\save_payment_page',
	50,
	3
);

/**
 * Updates the Payment Page's URL with a unique slug.
 *
 * @since 4.5.0
 *
 * @param array<string, mixed> $data Post data.
 * @return array<string, mixed> Filtered post data.
 */
function save_payment_page_name( $data ) {
	if ( ! isset( $_POST['_payment_page_slug'] ) ) {
		return $data;
	}

	if ( empty( $data['post_type'] ) || 'simple-pay' !== $data['post_type'] ) {
		return $data;
	}

	$post_id  = absint( $_POST['post_ID'] );
	$slug     = sanitize_title( $_POST['_payment_page_slug'] );
	$existing = get_page_by_path( $slug, OBJECT, 'simple-pay' );

	if ( null !== $existing && $existing->ID !== $post_id ) {
		$slug = wp_unique_post_slug(
			$slug,
			absint( $_POST['post_id'] ),
			$data['status'],
			$data['post_type'],
			0
		);
	}

	$data['post_name'] = $slug;

	return $data;
}
add_filter(
	'wp_insert_post_data',
	__NAMESPACE__ . '\\save_payment_page_name'
);

/**
 * Saves the Payment Form's Purchase Restriction settings.
 *
 * @since 4.6.4
 *
 * @param int $post_id Payment Form ID.
 * @return void
 */
function save_purchase_restrictions_inventory( $post_id ) {
	$inventory_enabled = isset( $_POST['_inventory'] ) ? 'yes' : 'no';

	update_post_meta( $post_id, '_inventory', $inventory_enabled );

	if ( 'yes' === $inventory_enabled ) {
		// Inventory behavior.
		$inventory_behavior = isset( $_POST['_inventory_behavior'] )
			? sanitize_text_field( $_POST['_inventory_behavior'] )
			: 'combined';

		update_post_meta( $post_id, '_inventory_behavior', $inventory_behavior );

		// Combined.
		if ( 'combined' === $inventory_behavior ) {
			$inventory_combined = isset( $_POST['_inventory_behavior_combined'] )
				? absint( $_POST['_inventory_behavior_combined'] )
				: 0;

			$existing_combined = get_post_meta(
				$post_id,
				'_inventory_behavior_combined',
				true
			);

			if ( ! empty( $existing_combined ) ) {
				$updated_combined = array(
					'available' => $inventory_combined,
				);

				if ( $inventory_combined > intval( $existing_combined['initial'] ) ) {
					$updated_combined['initial'] = $inventory_combined;
				}

				update_post_meta(
					$post_id,
					'_inventory_behavior_combined',
					array_merge(
						$existing_combined,
						$updated_combined
					)
				);
			} else {
				update_post_meta(
					$post_id,
					'_inventory_behavior_combined',
					array(
						'initial'   => $inventory_combined,
						'available' => $inventory_combined,
					)
				);
			}

			delete_post_meta( $post_id, '_inventory_behavior_individual' );

			// Individual.
		} else {
			$inventory_individual = isset( $_POST['_inventory_behavior_individual'] )
				? $_POST['_inventory_behavior_individual']
				: array();

			$existing_inventory = get_post_meta(
				$post_id,
				'_inventory_behavior_individual',
				true
			);

			if ( ! is_array( $existing_inventory ) ) {
				$existing_inventory = array();
			}

			$inventories = array();

			foreach ( $inventory_individual as $instance_id => $inventory_available ) {
				$instance_id         = str_replace( 'price-', '', $instance_id );
				$inventory_available = absint( $inventory_available );

				if ( ! isset( $existing_inventory[ $instance_id ] ) ) {
					$inventories[ $instance_id ] = array(
						'available' => $inventory_available,
						'initial'   => $inventory_available,
					);
				} else {
					$updated_individual = array(
						'available' => absint( $inventory_available ),
					);

					if ( $inventory_available > $existing_inventory[ $instance_id ]['initial'] ) {
						$updated_individual['initial'] = $inventory_available;
					}

					$inventories[ $instance_id ] = array_merge(
						$existing_inventory[ $instance_id ],
						$updated_individual
					);
				}
			}

			update_post_meta(
				$post_id,
				'_inventory_behavior_individual',
				$inventories
			);

			delete_post_meta( $post_id, '_inventory_behavior_combined' );
		}

		// Remove items if inventory is disabled.
	} else {
		delete_post_meta( $post_id, '_inventory_behavior' );
		delete_post_meta( $post_id, '_inventory_behavior_combined' );
		delete_post_meta( $post_id, '_inventory_behavior_individual' );
	}

	// Always remove if using Stripe Checkout.
	$form_type = isset( $_POST['_form_type'] )
		? sanitize_text_field( $_POST['_form_type'] )
		: '';

	if ( 'off-site' === $form_type ) {
		delete_post_meta( $post_id, '_inventory' );
		delete_post_meta( $post_id, '_inventory_behavior' );
		delete_post_meta( $post_id, '_inventory_behavior_combined' );
		delete_post_meta( $post_id, '_inventory_behavior_individual' );
	}
}
add_action(
	'simpay_save_form_settings',
	__NAMESPACE__ . '\\save_purchase_restrictions_inventory',
	60
);

/**
 * Saves the Payment Form's Purchase Restriction scheduling settings.
 *
 * @since 4.6.4
 *
 * @param int $post_id Payment Form ID.
 * @return void
 */
function save_purchase_restrictions_schedule( $post_id ) {
	// Start.
	$enable_start = isset( $_POST['_schedule_start'] ) ? 'yes' : 'no';

	update_post_meta( $post_id, '_schedule_start', $enable_start );

	if ( 'yes' === $enable_start ) {
		$date = sanitize_text_field( $_POST['_schedule_start_date'] );
		$time = sanitize_text_field( $_POST['_schedule_start_time']  );

		$datetime = trim( $date . ' ' . $time );

		if ( ! empty( $datetime ) && strtotime( $datetime ) ) {
			$start = get_gmt_from_date( $datetime, 'U' );

			update_post_meta( $post_id, '_schedule_start_gmt', $start );
		} else {
			delete_post_meta( $post_id, '_schedule_start' );
			delete_post_meta( $post_id, '_schedule_start_gmt' );
		}
	} else {
		delete_post_meta( $post_id, '_schedule_start_gmt' );
	}

	// End.
	$enable_end = isset( $_POST['_schedule_end'] ) ? 'yes' : 'no';

	update_post_meta( $post_id, '_schedule_end', $enable_end );

	if ( 'yes' === $enable_end ) {
		$date = sanitize_text_field( $_POST['_schedule_end_date'] );
		$time = sanitize_text_field( $_POST['_schedule_end_time']  );

		$datetime = trim( $date . ' ' . $time );

		if ( ! empty( $datetime ) && strtotime( $datetime ) ) {
			$end = get_gmt_from_date( $datetime, 'U' );

			update_post_meta( $post_id, '_schedule_end_gmt', $end );
		} else {
			delete_post_meta( $post_id, '_schedule_end' );
			delete_post_meta( $post_id, '_schedule_end_gmt' );
		}
	} else {
		delete_post_meta( $post_id, '_schedule_end_gmt' );
	}
}
add_action(
	'simpay_save_form_settings',
	__NAMESPACE__ . '\\save_purchase_restrictions_schedule',
	70
);
