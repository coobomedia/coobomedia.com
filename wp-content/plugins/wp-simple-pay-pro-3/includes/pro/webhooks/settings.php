<?php
/**
 * Webhooks: Settings
 *
 * @package SimplePay\Pro\Webhooks
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Webhooks;

use SimplePay\Core\Utils;
use SimplePay\Core\Settings\Subsection;
use SimplePay\Core\Settings\Setting;
use SimplePay\Core\Settings\Setting_Input;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Outputs webhook setup content.
 *
 * @since 4.0.0
 */
function setup_description() {
	ob_start();

	$webhooks_url = simpay_is_test_mode()
		? 'https://dashboard.stripe.com/test/webhooks/create'
		: 'https://dashboard.stripe.com/webhooks/create';

	/**
	 * Allows additional output before the Webhook "Setup" setting.
	 *
	 * @since 4.4.1
	 */
	do_action( '__unstable_simpay_before_webhook_setting' );
	?>

	<p>
		<?php
		echo wp_kses_post(
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__( 'Stripe uses webhooks to notify WP Simple Pay when an event has occurred in your Stripe account. Ensure an endpoint with the following URL is present in the %1$sStripe webhook settings%2$s', 'simple-pay' ),
				'<a href="' . $webhooks_url . '" target="_blank" rel="noopener noreferrer" class="simpay-external-link"><strong>',
				'</strong>' . Utils\get_external_link_markup() . '</a>'
			)
		);
		?>
	</p>

	<div style="margin: 15px 0 10px;">
		<input type="text" value="<?php echo esc_attr( simpay_get_webhook_url() ); ?>" style="width: 100%; font-size: 1.05em; padding: 4px 14px; font-family: Consolas, Monaco, monospace;" id="simpay-webhook-endpoint-url" />
	</div>

	<div style="display: flex; align-items: center;">
		<button type="button" class="button button-secondary button-copy simpay-copy-button" data-copied="<?php echo esc_attr__( 'Copied!', 'simple-pay' ); ?>" data-clipboard-target="#simpay-webhook-endpoint-url">
			<?php esc_html_e( 'Copy URL to Clipboard', 'simple-pay' ); ?>
		</button>
	</div>

	<?php
	return ob_get_clean();
}


/**
 * Registers subsection.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Subsections_Collection $subsections Subsections collection.
 */
function register_subsections( $subsections ) {
	if ( empty( simpay_get_secret_key() ) ) {
		return;
	}

	// Stripe/Webhooks.
	$subsections->add(
		new Subsection(
			array(
				'id'       => 'webhooks',
				'section'  => 'stripe',
				'label'    => esc_html_x( 'Webhooks', 'settings subsection label', 'simple-pay' ),
				'priority' => 30,
			)
		)
	);
}
add_action( 'simpay_register_settings_subsections', __NAMESPACE__ . '\\register_subsections' );

/**
 * Registers settings.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_settings( $settings ) {
	// Setup.
	$settings->add(
		new Setting(
			array(
				'id'         => 'webhook_setup',
				'section'    => 'stripe',
				'subsection' => 'webhooks',
				'label'      => esc_html_x( 'Setup', 'setting label', 'simple-pay' ),
				'output'     => __NAMESPACE__ . '\\setup_description',
			)
		)
	);

	/**
	 * Retrieves shared arguments for webhook key settings.
	 *
	 * @since 4.3.0
	 *
	 * @param bool $is_livemode If the arguments are for live mode, or not.
	 * @return array
	 */
	function get_args( $is_livemode ) {
		$url = true === $is_livemode
			? 'https://dashboard.stripe.com/webhooks'
			: 'https://dashboard.stripe.com/test/webhooks';

		$mode = true === $is_livemode
			? __( 'live mode', 'simple-pay' )
			: __( 'test mode', 'simple-pay' );

		return array(
			'section'     => 'stripe',
			'subsection'  => 'webhooks',
			'type'        => 'password',
			'description' => wpautop(
				wp_kses(
					sprintf(
						/* translators: %1$s opening anchor tag and URL, do not translate. %2$s closing anchor tag, do not translate */
						__(
							'Retrieve your %3$s "Signing secret" from your %1$sStripe webhook settings%2$s. Select the endpoint then click "Reveal".',
							'simple-pay'
						),
						'<a href="' . $url . '" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
						Utils\get_external_link_markup() . '</a>',
						'<strong>' . $mode . '</strong>'
					),
					array(
						'a'      => array(
							'href'   => true,
							'target' => true,
							'rel'    => true,
							'class'  => true,
						),
						'em'     => array(),
						'br'     => array(),
						'span'   => array(
							'class' => 'screen-reader-text',
						),
						'strong' => array(),
					)
				)
			),
			'schema'      => array(
				'type' => 'string',
			),
			'classes'     => array(
				'regular-text',
			),
		);
	}

	$settings->add(
		new Setting_Input(
			array_merge(
				get_args( false ),
				array(
					'id'       => 'test_webhook_endpoint_secret',
					'label'    => esc_html_x(
						'Test Mode Signing Secret',
						'setting label',
						'simple-pay'
					),
					'value'    => simpay_get_setting( 'test_webhook_endpoint_secret', '' ),
					'priority' => 20,
				)
			)
		)
	);

	$settings->add(
		new Setting_Input(
			array_merge(
				get_args( true ),
				array(
					'id'       => 'live_webhook_endpoint_secret',
					'label'    => esc_html_x(
						'Live Mode Signing Secret',
						'setting label',
						'simple-pay'
					),
					'value'    => simpay_get_setting( 'live_webhook_endpoint_secret', '' ),
					'priority' => 30,
				)
			)
		)
	);
}
add_action( 'simpay_register_settings', __NAMESPACE__ . '\\register_settings' );
