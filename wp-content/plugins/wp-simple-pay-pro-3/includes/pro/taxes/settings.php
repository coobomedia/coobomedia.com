<?php
/**
 * Taxes: Settings
 *
 * @package SimplePay\Pro\Settings\General\Taxes
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

namespace SimplePay\Pro\Taxes\Settings;

use Sandhills\Utils\Persistent_Dismissible;
use SimplePay\Core\Settings;
use SimplePay\Core\API;
use SimplePay\Core\Utils;

use stdClass;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers settings subsections.
 *
 * @since 4.1.0
 *
 * @param \SimplePay\Core\Settings\Subsections_Collection $subsections Subsections collection.
 */
function register_subsections( $subsections ) {
	// General/Tax.
	$subsections->add(
		new Settings\Subsection(
			array(
				'id'       => 'taxes',
				'section'  => 'general',
				'label'    => esc_html_x( 'Taxes', 'settings subsection label', 'simple-pay' ),
				'priority' => 20,
			)
		)
	);
}
add_action( 'simpay_register_settings_subsections', __NAMESPACE__ . '\\register_subsections' );

/**
 * Registers tax settings.
 *
 * @since 4.1.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_settings( $settings ) {
	// Return early with a placeholder if there is no Stripe connection.
	if ( empty( simpay_get_secret_key() ) ) {
		$connection = new Settings\Setting(
			array(
				'id'          => 'taxes',
				'section'     => 'general',
				'subsection'  => 'taxes',
				'label'       => esc_html__( 'Fixed Tax Rates', 'simple-pay' ),
				'output'      => function() {
					add_filter(
						'simpay_admin_page_settings_general_submit',
						'__return_false'
					);

					$redirect_url = Settings\get_url(
						array(
							'section'    => 'general',
							'subsection' => 'taxes',
						)
					);

					echo '<style>.simpay-settings-taxes th { display: none; }';

					include_once SIMPLE_PAY_DIR . '/views/admin-page-stripe-connect.php'; // @phpstan-ignore-line
				},
				'schema' => array(
					'type' => 'string',
					'enum' => array( 'yes', 'no' ),
				),
			)
		);

		$settings->add( $connection );

		return;
	}

	// Enable/disable.
	$settings->add(
		new Settings\Setting_Checkbox(
			array(
				'id'          => 'taxes',
				'section'     => 'general',
				'subsection'  => 'taxes',
				'label'       => esc_html__( 'Global Tax Rates', 'simple-pay' ),
				'input_label' => esc_html__( 'Enable', 'simple-pay' ),
				'value'       => simpay_get_setting( 'taxes', 'no' ),
				'priority'    => 10,
				'toggles'     => array(
					'value'    => 'yes',
					'settings' => array(
						'tax_rates',
					),
				),
				'description' => wpautop(
					esc_html__(
						'Apply global tax rates to payments.',
						'simple-pay'
					)
				),
				'schema'      => array(
					'type' => 'string',
					'enum' => array( 'yes', 'no' ),
				)
			)
		)
	);

	// Tax rates.
	$settings->add(
		new Settings\Setting(
			array(
				'id'          => 'tax_rates',
				'section'     => 'general',
				'subsection'  => 'taxes',
				'label'       => esc_html__( 'Tax Rates', 'simple-pay' ),
				'priority'    => 20,
				'output'      => __NAMESPACE__ . '\\tax_rates_table',
			)
		)
	);
}
add_action( 'simpay_register_settings', __NAMESPACE__ . '\\register_settings' );

/**
 * Outputs "Tax Rates" setting.
 *
 * @since 4.1.0
 */
function tax_rates_table() {
	$tax_rates = simpay_get_tax_rates( ! simpay_is_test_mode() );

	$templates = array(
		'form-add-tax-rate',
		'form-edit-tax-rate',
		'tax-rate',
		'empty-tax-rates',
	);
	?>

	<?php foreach ( $templates as $template ) : ?>
		<script
			id="tmpl-simpay-<?php echo esc_attr( $template ); ?>"
			type="text/html"
		>
		<?php include SIMPLE_PAY_DIR . 'includes/pro/taxes/tmpl/tmpl-' . $template . '.php'; ?>
		</script>
	<?php endforeach; ?>

	<script>
		var simpayTaxRates = <?php echo wp_json_encode( $tax_rates ); ?>;
	</script>

	<div
		id="simpay-form-add-tax-rate-dialog"
		title="<?php esc_attr_e( 'Add Tax Rate', 'simple-pay' ); ?>"
		style="display: none;"
	>
		<div id="simpay-form-add-tax-rate-dialog-content"></div>
	</div>

	<div
		id="simpay-form-edit-tax-rate-dialog"
		title="<?php esc_attr_e( 'Edit Tax Rate', 'simple-pay' ); ?>"
		style="display: none;"
	>
		<div id="simpay-form-edit-tax-rate-dialog-content"></div>
	</div>

	<div id="simpay-tax-rate-manager">
		<p style="margin-bottom: 10px;">
			<button class="simpay-add-tax-rate button button-secondary">
				<?php esc_html_e( 'Add Rate', 'simple-pay' ); ?>
			</button>
		</p>

		<table class="wp-list-table widefat simpay-tax-rates">
			<thead>
				<tr>
					<th>
						<?php esc_html_e( 'Display Name', 'simple-pay' ); ?>
					</th>
					<th>
						<?php esc_html_e( 'Rate', 'simple-pay' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="2">
						<?php
							if ( 0 === count( $tax_rates ) ) :
								esc_html_e( 'No tax rates.', 'simple-pay' );
							else :
								esc_html_e( 'Loading&hellip;', 'simple-pay' );
							endif;
						?>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<p class="description" style="margin-top: 10px;">
		<?php
		esc_html_e(
			'All tax rates are applied to the entire payment. Active Subscriptions are not affected by changes to rates.',
			'simple-pay'
		);
		?>
	</p>

	<?php
}

/**
 * Creates or updates a Tax Rate when settings are saved.
 *
 * @since 4.1.0
 *
 * @param array $new_settings Updated settings.
 * @return array Updated settings.
 */
function save_tax_rates( $new_settings ) {
	$tax_rates = isset( $new_settings['tax_rates'] )
		? $new_settings['tax_rates']
		: 'no';

	unset( $new_settings['tax_rates'] );

	if ( false === is_array( $tax_rates ) ) {
		return $new_settings;
	}

	$saved_tax_rates = array();

	$api_request_args = array(
		'api_key' => simpay_get_setting(
			simpay_is_test_mode() ? 'test_secret_key' : 'live_secret_key',
			''
		),
	);

	foreach ( $tax_rates as $instance_id => $tax_rate ) {
		$saved = strpos( $tax_rate['id'], 'txr_' ) !== false;

		try {
			// Create a new Tax Rate if one is not saved, or settings have changed.
			if ( false === $saved ) {
				$tax_rate = API\TaxRates\create(
					array(
						'display_name' => $tax_rate['display_name'],
						'inclusive'    => 'inclusive' === $tax_rate['calculation'],
						'percentage'   => str_pad(
							number_format(
								$tax_rate['percentage'],
								4
							),
							1,
							0
						),
					),
					$api_request_args
				);

				// Display name has changed, update it.
			} else {
				$tax_rate = API\TaxRates\update(
					$tax_rate['id'],
					array(
						'display_name' => $tax_rate['display_name']
					),
					$api_request_args
				);
			}
		} catch ( Exception $e ) {
			wp_die( esc_html( $e->getMessage() ) );
		}

		$saved_tax_rates[ $instance_id ] = array(
			'id' => $tax_rate->id,
		);
	}

	$key = simpay_is_test_mode()
		? 'tax_rates_test'
		: 'tax_rates_live';

	$modified_key = simpay_is_test_mode()
		? 'tax_rates_test_modified'
		: 'tax_rates_live_modified';

	$new_settings[ $key ]          = $saved_tax_rates;
	$new_settings[ $modified_key ] = time();

	return $new_settings;
}
add_filter( 'simpay_update_settings', __NAMESPACE__ . '\\save_tax_rates' );

/**
 * Displays a notice about using automatic tax calculation in payment form settings.
 *
 * @since 4.6.0
 *
 * @return void
 */
function __unstable_automatic_tax_settings_notice() {
	$section = ! empty( $_GET['tab'] )
		? sanitize_key( $_GET['tab'] )
		: Settings\get_main_section_id();

	$subsection = ! empty( $_GET['subsection'] )
		? sanitize_key( $_GET['subsection'] )
		: Settings\get_main_subsection_id( $section );

	if ( 'taxes' !== $subsection ) {
		return;
	}

	$has_automatic_tax_docs_notice = ! (bool) Persistent_Dismissible::get(
		array(
			'id' => 'simpay-form-settings-automatic-tax-education',
		)
	);

	if ( false === $has_automatic_tax_docs_notice ) {
		return;
	}

	?>

	<div
		class="simpay-notice simpay-form-settings-notice"
		data-nonce="<?php echo esc_attr( wp_create_nonce( 'simpay-dismiss-notice-simpay-form-settings-automatic-tax-education' ) ); ?>"
		data-id="simpay-form-settings-automatic-tax-education"
		data-lifespan="<?php echo esc_attr( DAY_IN_SECONDS * 180 ); // @phpstan-ignore-line ?>"
		style="margin: 10px 0;"
	>
		<strong style="display: flex; align-items: center;">
			<svg xmlns="http://www.w3.org/2000/svg" style="width: 18px; height: 18px; margin-right: 8px;" viewBox="0 0 20 20" fill="#635aff">
				<path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 6.477V16h2a1 1 0 110 2H7a1 1 0 110-2h2V6.477L6.237 7.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L9 4.323V3a1 1 0 011-1zm-5 8.274l-.818 2.552c.25.112.526.174.818.174.292 0 .569-.062.818-.174L5 10.274zm10 0l-.818 2.552c.25.112.526.174.818.174.292 0 .569-.062.818-.174L15 10.274z" clip-rule="evenodd" />
			</svg>
			<span><?php esc_html_e( 'Learn About Automatic Location-Based Tax Calculation', 'simple-pay' ); ?></span>
		</strong>

		<p style="margin-left: 26px;">
		<?php
		echo wp_kses(
			sprintf(
				/* translators: %1$s Opening <a> tag, do not translate. %2$s Closing </a> tag, do not translate. */
				__(
					'%1$sLearn more about Stripe Tax%2$s and how to automate tax calculation and collection. Know where to register, automatically collect the right amount of tax, and access the reports you need to file returns.',
					'simple-pay'
				),
				'<a href="' . esc_url( simpay_docs_link( 'Automatic tax', 'taxes', 'form-tax-settings', true ) ) . '" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
				Utils\get_external_link_markup() . '</a>'
			),
			array(
				'a'    => array(
					'href'   => true,
					'rel'    => true,
					'target' => true,
					'class'  => true,
				),
				'span' => array(
					'class' => true,
				),
			)
		);
		?>
		</p>

		<button type="button" class="button button-link simpay-notice-dismiss">
			&times;
			<span class="screen-reader-text">
				<?php esc_html_e( 'Dismiss', 'simple-pay' ); ?>
			</span>
		</button>
	</div>

	<?php
}

add_action(
	'simpay_admin_page_settings_general_start',
	__NAMESPACE__ . '\\__unstable_automatic_tax_settings_notice'
);
