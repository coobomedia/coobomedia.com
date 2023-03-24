<?php
/**
 * Functions
 *
 * @package SimplePay\Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

use SimplePay\Core\API;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stripe Elements supported locales.
 *
 * @since 3.6.0
 *
 * @return array
 */
function simpay_get_stripe_elements_locales() {
	return SimplePay\Core\i18n\get_stripe_elements_locales();
}

/**
 * Retrieves a list of Plans available in the Stripe.
 *
 * @since 3.8.0
 * @since 3.9.0 Adds optional form context.
 *
 * @param array                         $args Plan retrieval arguments.
 * @param SimplePay\Core\Abstracts\Form $form Optional Payment Form context.
 *                                            Default null.
 * @return array List of all Plan objects.
 */
function _simpay_get_plans( $args, $form = null ) {
	$plans = array();

	try {
		$_plans = API\Plans\all(
			$args,
			$form ? $form->get_api_request_args() : array()
		);

		foreach ( $_plans->autopagingiterator() as $plan ) {
			$plans[] = $plan;
		}
	} catch ( \Exception $e ) {
		// An empty plan list is valid.
	}

	return $plans;
}

/**
 * Retrieves a possibly cached list of Plans available in Stripe.
 *
 * Plan lists are cached if enabled via `simpay_cache_plans` (enabled by default)
 * and if the total count is greater than 25 results.
 *
 * @since 3.6.0
 * @since 3.8.0 Results are cached.
 * @since 3.9.0 Adds optional form context.
 *
 * @param array                         $args Plan retrieval arguments.
 * @param SimplePay\Core\Abstracts\Form $form Optional Payment Form context.
 *                                            Default null.
 * @return array List of all Plan objects.
 */
function simpay_get_plans( $args = array(), $form = null ) {
	$defaults = array(
		'active' => true,
		'limit'  => 9999,
	);

	$args = wp_parse_args( $args, $defaults );

	$cache_key = 'simpay_plans_' . md5( serialize( $args ) );
	$cache     = get_transient( $cache_key );

	$plans = array();

	$cache_plans = true;

	/**
	 * Determines if Plan retrieval should be cached.
	 *
	 * @since 3.8.0
	 *
	 * @param bool $cache_plans If Plan retrieval should be cached. Default `true`.
	 */
	$cache_plans = apply_filters( 'simpay_cache_plans', $cache_plans );

	try {

		// Cache value exists, determine if it is up to date.
		if ( true === $cache_plans && false !== $cache ) {
			$latest = API\Plans\all(
				array(
					'limit' => 1,
				),
				$form ? $form->get_api_request_args() : array()
			);

			// Retrieve Plan list again if latest Plan in Stripe does not match
			// cached Plan list.
			if ( empty( $latest->data ) || current( $latest->data )->id !== current( $cache )->id ) {
				$plans = _simpay_get_plans( $args );
			} else {
				$plans = $cache;
			}
			// No cache, fresh query.
		} else {
			$plans = _simpay_get_plans( $args, $form );
		}
	} catch ( \Exception $e ) {
		// An empty plan list is valid.
	}

	if ( true === $cache_plans && count( $plans ) > 25 ) {
		set_transient( $cache_key, $plans, DAY_IN_SECONDS );
	}

	return $plans;
}

/**
 * Get a list of all the Stripe plans
 *
 * @since 3.0
 * @since 3.9.0 Requires Payment Form context.
 *
 * @param SimplePay\Core\Abstracts\Form $form Form instance.
 */
function simpay_get_plan_list( $form ) {
	$options = array();

	// Make sure the API keys exist before we try to load the plan list.
	if ( ! simpay_check_keys_exist() ) {
		return $options;
	}

	$skip_metered_plans = true;

	/**
	 * Filters whether or not the list of Plans should include
	 * "Metered usage" pricing options.
	 *
	 * @since 3.6.0
	 *
	 * @param bool $skip_metered_plans If the metered plans should be skipped.
	 * @return bool
	 */
	$skip_metered_plans = apply_filters( 'simpay_get_plan_list_skip_metered_plans', $skip_metered_plans );

	$plans = simpay_get_plans( array(), $form );

	/* @var $plans \SimplePay\Vendor\Stripe\Plan[] */
	foreach ( $plans as $plan ) {

		// Skip generated plans.
		if ( isset( $plan->metadata->simpay_is_generated_plan ) ) {
			continue;
		}

		// Skip "Metered usage" pricing.
		if ( $skip_metered_plans && ( 'licensed' !== $plan->usage_type ) ) {
			continue;
		}

		$nickname       = $plan->nickname; // New pricing plan name (as of Stripe API 2018-02-05).
		$legacy_name    = isset( $plan->name ) ? $plan->name : $nickname; // Legacy plan name attribute (before Stripe API 2018-02-05).
		$id             = $plan->id;
		$currency       = $plan->currency;
		$amount         = $plan->amount;
		$interval       = $plan->interval;
		$interval_count = $plan->interval_count;
		$decimals       = 0;

		// Display "PlanName - $##/month". Omit product name & plan ID.
		// If no plan name (nickname attr), try (legacy) name attr, then finally plan id attr.

		// TODO Display "ProductName/PlanName - $##/month". Omit plan ID. ...at some point?
		// Would need to access Products in Stripe API.

		$plan_name = $nickname;

		if ( empty( $plan_name ) ) {
			if ( ! empty( $legacy_name ) ) {
				$plan_name = $legacy_name;
			} else {
				$plan_name = $id;
			}
		}

		if ( ! simpay_is_zero_decimal( $currency ) ) {
			$amount   = $amount / 100;
			$decimals = 2;
		}

		// Put currency symbol + amount in one string to make it easier.
		$amount = simpay_get_currency_symbol( $currency ) . number_format( $amount, $decimals );

		$billing_cycle = sprintf(
			/* translators: %1$s Payment amount. %2$d Billing interval count. %3$s Billing interval. */
			_n(
				'%1$s/%3$s',
				'%1$s every %2$d %3$ss',
				$interval_count,
				'simple-pay'
			),
			$amount,
			$interval_count,
			$interval
		);

		$options[ $id ] = $plan_name . ' - ' . $billing_cycle;
	}

	asort( $options );

	return $options;
}

/**
 * Returns the description for Form Field Label.
 *
 * @since 3.8.0
 *
 * @return string
 */
function simpay_form_field_label_description() {
	return esc_html__( 'A text label displayed above the field.', 'simple-pay' );
}

/**
 * Returns the description for Placeholder.
 *
 * @since 3.8.0
 *
 * @return string
 */
function simpay_placeholder_description() {
	return esc_html__(
		'A short hint shown when the field is empty.',
		'simple-pay'
	);
}

/**
 * Returns a string explaining the `required` HTML attribute.
 *
 * @since 3.8.0
 *
 * @return string
 */
function simpay_required_field_description() {
	return esc_html__(
		'Determines if the field must be filled out before submitting the payment form.',
		'simple-pay'
	);
}

/**
 * Returns the description for Stripe Metadata Label
 *
 * @since 3.8.0
 *
 * @return string
 */
function simpay_metadata_label_description() {
	return esc_html__(
		'Used to identify this field within Stripe payment records. Not displayed on the payment form.',
		'simple-pay'
	);
}
