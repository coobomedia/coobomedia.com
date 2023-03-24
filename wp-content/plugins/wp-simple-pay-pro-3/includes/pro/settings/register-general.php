<?php
/**
 * Settings Registration: General
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Settings\General;

use SimplePay\Core\Settings;
use SimplePay\Core\i18n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers settings subsections.
 *
 * @since 4.0.0
 * @since 4.1.0 Deprecated. Moved to \SimplePay\Pro\Settings\General\Taxes
 *
 * @param \SimplePay\Core\Settings\Subsections_Collection $subsections Subsections collection.
 */
function register_subsections( $subsections ) {
	_doing_it_wrong(
		__FUNCTION__,
		esc_html__( 'No longer used.', 'simple-pay' ),
		'4.1.0'
	);
}

/**
 * Registers the settings.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_settings( $settings ) {
	register_advanced_settings( $settings );
}
add_action( 'simpay_register_settings', __NAMESPACE__ . '\\register_settings' );

/**
 * Registers tax settings.
 *
 * @since 4.0.0
 * @since 4.1.0 Deprecated. Moved to \SimplePay\Pro\Settings\General\Taxes
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_tax_settings( $settings ) {
	_doing_it_wrong(
		__FUNCTION__,
		esc_html__( 'No longer used.', 'simple-pay' ),
		'4.1.0'
	);
}

/**
 * Registers advanced settings.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_advanced_settings( $settings ) {
	// Plugin styles.
	// @todo Switch to checkbox.
	$settings->add(
		new Settings\Setting_Radio(
			array(
				'id'          => 'default_plugin_styles',
				'section'     => 'general',
				'subsection'  => 'advanced',
				'label'       => esc_html_x(
					'Opinionated Styles',
					'setting label',
					'simple-pay'
				),
				'options'     => array(
					'enabled'  => esc_html_x( 'Enabled', 'setting label', 'simple-pay' ),
					'disabled' => esc_html_x( 'Disabled', 'setting label', 'simple-pay' ),
				),
				'value'       => simpay_get_setting( 'default_plugin_styles', 'enabled' ),
				'description' => wpautop(
					sprintf(
						/* translators: Plugin name */
						esc_html__(
							'Automatically apply %1$s styles to payment form fields and buttons.',
							'simple-pay'
						),
						SIMPLE_PAY_PLUGIN_NAME
					) . '<br />' .
					esc_html__(
						'Styles on the Stripe.com Checkout page cannot be changed.',
						'simple-pay'
					)
				),
				'priority'    => 10,
				'schema'      => array(
					'type' => 'string',
					'enum' => array( 'enabled', 'disabled' ),
				),
			)
		)
	);

	// Date format.
	$settings->add(
		new Settings\Setting_Input(
			array(
				'id'          => 'date_format',
				'section'     => 'general',
				'subsection'  => 'advanced',
				'label'       => esc_html_x(
					'Date Format',
					'setting label',
					'simple-pay'
				),
				'value'       => simpay_get_setting( 'date_format', 'mm/dd/yy' ),
				'placeholder' => 'mm/dd/yy',
				'description' => wpautop(
					sprintf(
						/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
						__( '%1$sDate format options%2$s (uses jQuery UI Datepicker). Format only applies to collected values.', 'simple-pay' ),
						'<a href="http://api.jqueryui.com/datepicker/#utility-formatDate" target="_blank" rel="noopener noreferrer">',
						'</a>'
					),
					array(
						'a' => array(
							'href'   => true,
							'target' => true,
							'rel'    => true,
						),
					)
				),
				'priority'    => 20,
				'schema'      => array(
					'type' => 'string',
				),
			)
		)
	);

}
