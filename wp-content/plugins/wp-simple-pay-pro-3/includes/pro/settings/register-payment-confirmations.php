<?php
/**
 * Settings Registration: Payment Confirmation
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 *
 * @todo This should be inside of a "payment confirmations" module.
 * Currently other related things exist inside of includes/core/payments
 */

namespace SimplePay\Pro\Settings\Payment_Confirmations;

use SimplePay\Core\Settings;
use SimplePay\Core\i18n;
use SimplePay\Core\Payments\Payment_Confirmation;

use SimplePay\Pro\Payments\Payment_Confirmation as Pro_Payment_Confirmation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers settings subsections.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Subsections_Collection $subsections Subsections collection.
 */
function register_subsections( $subsections ) {
	if ( false === simpay_subscriptions_enabled() ) {
		return;
	}

	// Payment Confirmations/Subscription.
	$subsections->add(
		new Settings\Subsection(
			array(
				'id'       => 'subscription',
				'section'  => 'payment-confirmations',
				'label'    => esc_html_x( 'Subscription', 'settings subsection label', 'simple-pay' ),
				'priority' => 30,
			)
		)
	);

	// Payment Confirmations/Subscription.
	if ( false === simpay_get_license()->is_enhanced_subscriptions_enabled() ) {
		return;
	}

	$subsections->add(
		new Settings\Subsection(
			array(
				'id'       => 'subscription-with-trial',
				'section'  => 'payment-confirmations',
				'label'    => esc_html_x(
					'Subscription with Trial',
					'settings subsection label',
					'simple-pay'
				),
				'priority' => 40,
			)
		)
	);
}
add_action( 'simpay_register_settings_subsections', __NAMESPACE__ . '\\register_subsections' );

/**
 * Registers the settings.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_settings( $settings ) {
	if ( false === simpay_subscriptions_enabled() ) {
		return;
	}

	// Subscription.
	$settings->add(
		new Settings\Setting(
			array(
				'id'         => 'subscription_details',
				'section'    => 'payment-confirmations',
				'subsection' => 'subscription',
				'label'      => esc_html_x(
					'Confirmation Message',
					'setting label',
					'simple-pay'
				),
				'output'     => function() {
					wp_editor(
						simpay_get_setting(
							'subscription_details',
							Pro_Payment_Confirmation\get_subscription_message_default()
						),
						'subscription_details',
						array(
							'textarea_name' => 'simpay_settings[subscription_details]',
							'textarea_rows' => 10,
						)
					);

					Payment_Confirmation\Template_Tags\__unstable_print_tag_list(
						esc_html__(
							'Enter what your customers will see after a successful subscription.',
							'simple-pay'
						),
						array_merge(
							Payment_Confirmation\Template_Tags\__unstable_get_tags_and_descriptions(),
							Pro_Payment_Confirmation\Template_Tags\__unstable_get_tags_and_descriptions()
						)
					);
				},
				'schema'    => array(
					'type' => 'string',
				)
			)
		)
	);

	if ( false === simpay_get_license()->is_enhanced_subscriptions_enabled() ) {
		return;
	}

	// Subscription with Trial.
	$settings->add(
		new Settings\Setting(
			array(
				'id'         => 'trial_details',
				'section'    => 'payment-confirmations',
				'subsection' => 'subscription-with-trial',
				'label'      => esc_html_x(
					'Confirmation Message',
					'setting label',
					'simple-pay'
				),
				'output'     => function() {
					wp_editor(
						simpay_get_setting(
							'trial_details',
							Pro_Payment_Confirmation\get_trial_message_default()
						),
						'trial_details',
						array(
							'textarea_name' => 'simpay_settings[trial_details]',
							'textarea_rows' => 10,
						)
					);

					Payment_Confirmation\Template_Tags\__unstable_print_tag_list(
						esc_html__(
							'Enter what your customers will see after a successful subscription.',
							'simple-pay'
						),
						array_merge(
							Payment_Confirmation\Template_Tags\__unstable_get_tags_and_descriptions(),
							Pro_Payment_Confirmation\Template_Tags\__unstable_get_tags_and_descriptions()
						)
					);
				},
				'schema'     => array(
					'type' => 'string',
				),
			)
		)
	);
}
add_action( 'simpay_register_settings', __NAMESPACE__ . '\\register_settings' );
