<?php
/**
 * Settings Registration: Stripe
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 *
 * @todo This should be inside of a "stripe" module.
 * Currently other related things exist inside of includes/core/stripe-connect
 */

namespace SimplePay\Pro\Settings\Stripe;

use SimplePay\Core\Settings;
use SimplePay\Core\i18n;
use SimplePay\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determines if the website can manage API keys manually.
 *
 * If there are existing API keys but no Stripe Account ID, continue letting the
 * user manually manage the keys.
 *
 * @since 4.4.0
 *
 * @return bool
 */
function can_manage_api_keys_manually( $can ) {
	if ( ! empty( simpay_get_secret_key() ) && empty( simpay_get_account_id() ) ) {
		return true;
	}

	return $can;
}
add_filter( 'simpay_can_site_manage_stripe_keys', __NAMESPACE__ . '\\can_manage_api_keys_manually' );

/**
 * Registers the settings.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_settings( $settings ) {
	register_locale_settings( $settings );
}
add_action( 'simpay_register_settings', __NAMESPACE__ . '\\register_settings' );

/**
 * Registers settings for Stripe/Locale subsection.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_locale_settings( $settings ) {
	// Stripe Elements locale.
	$settings->add(
		new Settings\Setting_Select(
			array(
				'id'          => 'stripe_elements_locale',
				'section'     => 'stripe',
				'subsection'  => 'locale',
				'label'       => esc_html_x( 'Stripe Elements', 'setting label', 'simple-pay' ),
				'options'     => i18n\get_stripe_elements_locales(),
				'value'       => simpay_get_setting( 'stripe_elements_locale', '' ),
				'description' => wpautop(
					esc_html__(
						'Specify "Auto-detect" to display the on-site Stripe field placeholders, validation messages, and more in the user\'s preferred language, if available.',
						'simple-pay'
					)
				),
				'priority'    => 20,
				'schema'      => array(
					'type' => 'string',
				),
			)
		)
	);

	// Afterpay / Clearpay locale.
	$settings->add(
		new Settings\Setting_Select(
			array(
				'id'          => 'stripe_elements_afterpay_clearpay_locale',
				'section'     => 'stripe',
				'subsection'  => 'locale',
				'label'       => esc_html_x(
					'Afterpay / Clearpay',
					'setting label',
					'simple-pay'
				),
				'options'     => array(
					'en-AU' => esc_html__( 'English (AU)', 'simple-pay' ),
					'en-CA' => esc_html__( 'English (CA)', 'simple-pay' ),
					'en-NZ' => esc_html__( 'English (NZ)', 'simple-pay' ),
					'en-GB' => esc_html__( 'English (UK)', 'simple-pay' ),
					'en-US' => esc_html__( 'English (US)', 'simple-pay' ),
					'fr-FR' => esc_html__( 'French', 'simple-pay' ),
					'it-IT' => esc_html__( 'Italian', 'simple-pay' ),
					'es-ES' => esc_html__( 'Spanish', 'simple-pay' ),
				),
				'value'       => simpay_get_setting(
					'stripe_elements_afterpay_clearpay_locale',
					'en-US'
				),
				'description' => wpautop(
					esc_html__(
						'Localize the Afterpay / Clearpay on-site messaging.',
						'simple-pay'
					)
				),
				'priority'    => 30,
				'schema'      => array(
					'type' => 'string',
				),
			)
		)
	);
}
