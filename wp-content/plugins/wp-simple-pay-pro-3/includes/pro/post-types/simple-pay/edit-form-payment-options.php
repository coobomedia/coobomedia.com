<?php
/**
 * Simple Pay: Edit form payment options
 *
 * @package SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form;

use Sandhills\Utils\Persistent_Dismissible;
use SimplePay\Core\PaymentForm\PriceOption;
use SimplePay\Core\Settings;
use SimplePay\Core\Utils;
use SimplePay\Pro\Payment_Methods;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Outputs markup for the "Tax Rates" setting.
 *
 * @since 4.5.0
 * @access private
 *
 * @param int $post_id Payment form ID.
 * @return void
 */
function __unstable_add_tax( $post_id ) {
	$license = simpay_get_license();

	// Tax status defaults to "fixed-global" if not previously set.
	// Set to "automatic" for new payment forms.
	$tax_status = get_post_meta( $post_id, '_tax_status', true );

	if ( 'auto-draft' !== get_post_status() && empty( $tax_status ) ) {
		$tax_status = 'fixed-global';
	} else if ( 'auto-draft' === get_post_status() ) {
		$tax_status = simpay_get_payment_form_setting(
			$post_id,
			'tax_status',
			'none',
			__unstable_simpay_get_payment_form_template_from_url()
		);
	}

	// Tax code.
	$tax_code = get_post_meta( $post_id, '_tax_code', true );

	// Tax behavior.
	$tax_behavior = get_post_meta( $post_id, '_tax_behavior', true );

	if ( empty( $tax_behavior ) ) {
		$tax_behavior = 'unspecified';
	}

	$has_automatic_tax_docs_notice = ! (bool) Persistent_Dismissible::get(
		array(
			'id' => 'simpay-form-settings-automatic-tax-education',
		)
	);

	$automatic_tax_supported = in_array(
		strtolower( simpay_get_setting( 'account_country', 'us' ) ),
		array(
			'ae',
			'at',
			'au',
			'be',
			'bg',
			'ca',
			'cy',
			'cz',
			'de',
			'dk',
			'ee',
			'es',
			'fi',
			'fr',
			'gb',
			'gr',
			'hk',
			'hr',
			'ie',
			'is',
			'it',
			'jp',
			'lt',
			'lu',
			'lv',
			'mt',
			'nl',
			'no',
			'nz',
			'po',
			'pt',
			'ro',
			'se',
			'sg',
			'si',
			'sk',
			'us',
			'za',
		),
		true
	);

	$upgrade_automatic_title = esc_html__(
		'Unlock Automatically Calculated Tax Amounts',
		'simple-pay'
	);

	$upgrade_automatic_description = esc_html__(
		'We\'re sorry, automatically calculating and collecting taxes is not available in your plan. Please upgrade to the <strong>Professional</strong> plan or higher to unlock this and other awesome features.',
		'simple-pay'
	);

	$upgrade_automatic_url = simpay_pro_upgrade_url(
		'form-tax-settings',
		'Automatic Rate Taxes'
	);

	$upgrade_automatic_purchased_url = simpay_docs_link(
		'Automatic Rate Taxes (already purchased)',
		'upgrading-wp-simple-pay-pro-license',
		'form-tax-settings',
		true
	);
	?>
	<table>
		<tbody class="simpay-panel-section">
			<tr class="simpay-panel-field">
				<th>
					<?php if ( $has_automatic_tax_docs_notice ) : ?>
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
					<?php endif; ?>

					<label for="_tax_status">
						<?php esc_html_e( 'Tax Collection', 'simple-pay' ); ?>
					</label>
				</th>
				<td>
					<div style="display: flex; align-items: center;">
						<select id="_tax_status" name="_tax_status">
							<option
								value="none"
								<?php selected( 'none', $tax_status ); ?>
							>
								<?php esc_html_e( 'None', 'simple-pay' ); ?>
							</option>
							<option
								value="fixed-global"
								<?php selected( 'fixed-global', $tax_status ); ?>
							>
								<?php esc_html_e( 'Global tax rates', 'simple-pay' ); ?>
							</option>
							<option
								value="automatic"
								<?php selected( 'automatic', $tax_status ); ?>
								<?php disabled( false, $automatic_tax_supported ); ?>
								data-available="<?php echo $license->is_pro( 'professional' ) ? 'yes' : 'no'; ?>"
								data-upgrade-title="<?php echo esc_attr( $upgrade_automatic_title ); ?>"
								data-upgrade-description="<?php echo esc_attr( $upgrade_automatic_description ); ?>"
								data-upgrade-url="<?php echo esc_url( $upgrade_automatic_url ); ?>"
								data-upgrade-purchased-url="<?php echo esc_url( $upgrade_automatic_purchased_url ); ?>"
								data-prev-value="<?php echo esc_attr( $tax_status ); ?>"
							>
								<?php
								esc_html_e(
									'Automatically calculated by location',
									'simple-pay'
								);
								?>
							</option>
						</select>

						<div
							class="simpay-test-mode-badge-container simpay-show-if"
							data-if="_tax_status"
							data-is="automatic"
							style="margin-left: 10px;"
						>
							<span class="simpay-test-mode-badge" style=" background-color: #cbf4c9; color: #0e6245;">
								<?php esc_html_e( 'Beta', 'simple-pay' ); ?>
							</span>
						</div>
					</div>

					<p
						class="description simpay-show-if"
						data-if="_tax_status"
						data-is="none"
					>
						<?php
						esc_html_e(
							'No tax will be collected.',
							'simple-pay'
						);
						?>
					</p>

					<p
						class="description simpay-show-if"
						data-if="_tax_status"
						data-is="fixed-global"
					>
						<?php
						$tax_rates_url = Settings\get_url(
							array(
								'section'    => 'general',
								'subsection' => 'taxes',
							)
						);

						echo wp_kses(
							sprintf(
								/* translators: Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
								__(
									'Apply all enabled %1$sglobal tax rates%2$s to payments.',
									'simple-pay'
								),
								'<a href="' . esc_url( $tax_rates_url ) . '" target="_blank">',
								'</a>'
							),
							array(
								'a' => array(
									'href'   => true,
									'target' => true,
								),
							)
						);
						?>
					</p>

					<?php if ( true === $automatic_tax_supported ) : ?>
					<p
						class="description simpay-show-if"
						data-if="_tax_status"
						data-is="automatic"
					>
						<?php
						echo wp_kses(
							sprintf(
								/* translators: Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
								__(
									'Automatically determine tax based on your %1$sStripe tax registrations%2$s and users\'s location.',
									'simple-pay'
								),
								'<a href="https://dashboard.stripe.com/test/settings/tax" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
								Utils\get_external_link_markup() . '</a>'
							),
							array(
								'a' => array(
									'href'   => true,
									'target' => true,
									'rel'    => true,
									'class'  => true,
								),
								'span' => array(
									'class' => true,
								),
							)
						);
						?>
					</p>
					<?php else: ?>
					<p class="description">
						<?php
						esc_html_e(
							'Automatic tax is not available for your Stripe account\'s country.',
							'simple-pay'
						);
						?>
					</p>
					<?php endif; ?>

					<div
						class="simpay-show-if"
						data-if="_tax_status"
						data-is="automatic"
						style="margin-top: 12px;"
					>
						<label for="_tax_code" style="display: block; margin-bottom: 4px;">
							<strong>
								<?php
								esc_html_e(
									'Tax Category',
									'simple-pay'
								);
								?>
							</strong>
						</label>
						<select id="_tax_code" name="_tax_code" style="max-width: 300px;">
							<?php
							$stripe_tax_codes = simpay_get_stripe_tax_codes();

							foreach ( $stripe_tax_codes as $stripe_tax_code ) :
							?>
							<option
								value="<?php echo esc_attr( $stripe_tax_code->id ); ?>"
								<?php selected( $stripe_tax_code->id, $tax_code ); ?>
							>
								<?php echo esc_html( $stripe_tax_code->name ); ?>
							</option>
							<?php endforeach; ?>
						</select>

						<p class="description">
							<?php
							echo wp_kses(
								sprintf(
									/* translators: Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
									__(
										'Selecting the appropriate %1$stax category%2$s ensures the lowest applicable tax rate is applied.',
										'simple-pay'
									),
									'<a href="https://stripe.com/docs/tax/tax-categories" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
									Utils\get_external_link_markup() . '</a>'
								),
								array(
									'a' => array(
										'href'   => true,
										'target' => true,
										'rel'    => true,
										'class'  => true,
									),
									'span' => array(
										'class' => true,
									),
								)
							);
							?>
						</p>
					</div>

					<div
						class="simpay-show-if"
						data-if="_tax_status"
						data-is="automatic"
						style="margin-top: 12px;"
					>
						<label
							for="_tax_behavior"
							style="display: block; margin-bottom: 4px;"
						>
							<strong>
								<?php
								esc_html_e(
									'Tax Behavior',
									'simple-pay'
								);
								?>
							</strong>
						</label>

						<div style="display: flex; align-items: center;">
							<select
								id="_tax_behavior"
								name="_tax_behavior"
								<?php if ( 'unspecified' !== $tax_behavior ) : ?>
									disabled
								<?php endif; ?>
							>
								<option
									value="exclusive"
									<?php selected( 'exclusive', $tax_behavior ); ?>
								>
									<?php
									esc_html_e(
										'Exclusive',
										'simple-pay'
									);
									?>
								</option>
								<option
									value="inclusive"
									<?php selected( 'inclusive', $tax_behavior ); ?>
								>
									<?php
									esc_html_e(
										'Inclusive',
										'simple-pay'
									);
									?>
								</option>
							</select>
						</div>

						<p class="description">
							<?php
							if ( 'unspecified' !== $tax_behavior ) :
								esc_html_e(
									'Tax behavior for automatic taxes cannot be adjusted after being set to ensure accuracy in accounting. Create a new payment form if you need to change the tax calculation behavior.',
									'simple-pay'
								);
							else:
								esc_html_e(
									'Setting tax behavior to "Exclusive" adds tax onto the subtotal amount specified on the price options. When set to inclusive, the amount your buyer pays never changes, even if the tax rate varies.',
									'sipmle-pay'
								);
							endif;
							?>
						</p>
					</div>

					<p style="margin: 0.5em 0;"></p>
				</td>
			</tr>
		</tbody>
	</table>

	<?php
}
add_action(
	'simpay_form_settings_meta_payment_options_panel',
	__NAMESPACE__ . '\\__unstable_add_tax',
	8
);

remove_action(
	'simpay_form_settings_meta_payment_options_panel',
	'SimplePay\Core\Post_Types\Simple_Pay\Edit_Form\__unstable_add_tax_upsell',
	10.5
);

/**
 * Outputs markup for the price option list.
 *
 * @since 4.1.0
 * @access private
 *
 * @param int $post_id Current Post ID (Payment Form ID).
 */
function __unstable_price_options( $post_id ) {
	$form = simpay_get_form( $post_id );

	if ( false === $form ) {
		return;
	}

	$add_price_nonce = wp_create_nonce( 'simpay_add_price_nonce' );
	$add_plan_nonce  = wp_create_nonce( 'simpay_add_plan_nonce' );

	$prices = simpay_get_payment_form_prices( $form );

	// Prefill the price options from a template or default fallback.
	//
	// Special handling vs using simpay_get_payment_form_setting() because we need
	// full access to the form to create a PriceOption instance.
	if ( empty( $prices ) ) {
		$template = __unstable_simpay_get_payment_form_template_from_url();

		// Generate from a template.
		if ( ! empty( $template ) ) {
			foreach ( $template['data']['prices'] as $price ) {
				$price                     = new PriceOption(
					$price,
					$form,
					wp_generate_uuid4()
				);
				$price->__unstable_unsaved = true;

				$prices[ wp_generate_uuid4() ] = $price;
			}

			// Single price option fallback.
		} else {
			$currency = strtolower(
				simpay_get_setting( 'currency', 'USD' )
			);

			$prices = array(
				wp_generate_uuid4() => new PriceOption(
					array(
						'unit_amount' => simpay_get_currency_minimum( $currency ),
						'currency'    => $currency,
					),
					$form,
					wp_generate_uuid4()
				),
			);
		}
	}
	?>

	<table>
		<tbody class="simpay-panel-section">
			<tr class="simpay-panel-field">
				<th>
					<?php esc_html_e( 'Price Options', 'simple-pay' ); ?>
				</th>
				<td style="border-bottom: 0;">
					<div
						style="
							display: flex;
							align-items: center;
							justify-content: space-between;
							margin: 0 0 12px;
						"
					>
						<button
							id="simpay-add-price"
							class="button button-secondary"
							data-nonce="<?php echo esc_attr( $add_price_nonce ); ?>"
							data-form-id="<?php echo esc_attr( $form->id ); ?>"
						>
							<?php esc_html_e( 'Add Price', 'simple-pay' ); ?>
						</button>

						<button
							id="simpay-prices-advanced-toggle"
							class="button button-link"
							style="
								display: flex;
								text-decoration: none;
								align-items: center;
								color: #666;
							"
						>
							<?php esc_html_e( 'Advanced', 'simple-pay' ); ?>
							<span
								class="dashicons dashicons-arrow-down-alt2"
								style="
									width: 14px;
									height: 14px;
									font-size: 14px;
									margin-left: 4px;
								"
							></span>
						</button>
					</div>

					<div
						id="simpay-prices-advanced"
						style="display: none; margin-bottom: 12px;"
					>
						<input
							type="text"
							value=""
							style="margin-right: 5px; width: 150px;"
							placeholder="plan_123"
							id="simpay-prices-advanced-plan-id"
						/>
						<button
							id="simpay-prices-advanced-add"
							class="button button-secondary"
							data-nonce="<?php echo esc_attr( $add_plan_nonce ); ?>"
							data-form-id="<?php echo esc_attr( $post_id ); ?>"
						>
							<?php esc_html_e( 'Add existing Plan', 'simple-pay' ); ?>
						</button>
					</div>

					<div
						id="simpay-prices-wrap"
						class="panel simpay-metaboxes-wrapper"
					>
						<div
							id="simpay-prices"
							class="simpay-prices simpay-metaboxes ui-sortable"
						>
							<?php
							/** @var \SimplePay\Core\PaymentForm\PriceOption[] $price Price option.  */
							foreach ( $prices as $instance_id => $price ) :
								__unstable_price_option( $price, $instance_id, $prices );
							endforeach;
							?>
						</div>
					</div>

					<?php
					/**
					 * Allows extra output after the price option list.
					 *
					 * @since 4.4.0
					 */
					do_action( '__unstable_simpay_form_settings_pro_after_price_options' )
					?>
				</td>
			</tr>
		</tbody>
	</table>

	<?php
}
add_action(
	'simpay_form_settings_meta_payment_options_panel',
	__NAMESPACE__ . '\\__unstable_price_options',
	5
);
// Remove Lite output.
remove_action(
	'simpay_form_settings_meta_payment_options_panel',
	'SimplePay\Core\Post_Types\Simple_Pay\Edit_Form\__unstable_add_price_options'
);

/**
 * Outputs markup for the payment method list.
 *
 * @since 4.4.7
 *
 * @param int $post_id Current Post ID (Payment Form ID).
 * @return void
 */
function __unstable_add_payment_methods( $post_id ) {
	$payment_methods = simpay_get_payment_form_setting(
		$post_id,
		'payment_methods',
		array(),
		__unstable_simpay_get_payment_form_template_from_url()
	);

	// Retrieve available Payment Methods for the given context.
	uasort(
		$payment_methods,
		function( $a ) {
			return isset( $a['id'] ) ? -1 : 1;
		}
	);

	$available_payment_methods = array_merge(
		array_flip( array_keys( $payment_methods ) ),
		Payment_Methods\get_payment_methods()
	);

	// Payment Methods with additional configuration.
	$payment_methods_with_config = array(
		'card',
	);

	// Initial form type.
	$form_type = simpay_get_payment_form_setting(
		$post_id,
		'type',
		'stripe_checkout',
		__unstable_simpay_get_payment_form_template_from_url()
	);

	// Tax status.
	$tax_status = simpay_get_payment_form_setting(
		$post_id,
		'tax_status',
		'none',
		__unstable_simpay_get_payment_form_template_from_url()
	);
	?>

	<table>
		<tbody class="simpay-panel-section">
			<?php include SIMPLE_PAY_INC . 'pro/post-types/simple-pay/edit-form-payment-methods.php'; ?>
		</tbody>
	</table>

	<?php
}
add_action(
	'simpay_form_settings_meta_payment_options_panel',
	__NAMESPACE__ . '\\__unstable_add_payment_methods',
	5
);
// Remove Lite output.
remove_action(
	'simpay_form_settings_meta_payment_options_panel',
	'SimplePay\Core\Post_Types\Simple_Pay\Edit_Form\__unstable_add_payment_methods'
);

/**
 * Displays a single price option.
 *
 * @since 4.1.0
 * @access private
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption        $price Price option.
 * @param string                                         $instance_id Price option instance ID.
 *                                                                    Shared by both payment
 *                                                                    modes.
 * @param array<\SimplePay\Core\PaymentForm\PriceOption> $prices All price options.
 */
function __unstable_price_option( $price, $instance_id, $prices ) {
	$amount_type = null !== $price->recurring && false === $price->can_recur
		? 'recurring'
		: 'one-time';
	$label       = $price->get_display_label();

	$recurring_settings_display = 'recurring' === $amount_type ? 'table' : 'none';

	$one_time_settings_display = (
		'recurring' === $amount_type &&
		! isset( $price->recurring['id'] ) &&
		false === $price->can_recur
	)
		? 'none'
		: 'table';

	$has_one_price = 1 === count( $prices );
	?>

	<div
		id="price-<?php echo esc_attr( $instance_id ); ?>"
		class="postbox <?php echo esc_attr( $has_one_price ? '' : 'closed' ); ?> simpay-field-metabox simpay-metabox simpay-price"
		<?php if ( false === $has_one_price ) : ?>
		aria-expanded="false"
		<?php endif; ?>
	>
		<input
			type="hidden"
			name="<?php echo esc_attr( __unstable_get_input_name( 'id', $instance_id ) ); ?>"
			value="<?php echo esc_attr( $price->id ); ?>"
		/>

		<input
			type="hidden"
			name="<?php echo esc_attr( __unstable_get_input_name( 'amount_type', $instance_id ) ); ?>"
			value="<?php echo esc_attr( $amount_type ); ?>"
			class="simpay-price-amount-type"
		/>

		<button type="button" class="simpay-handlediv simpay-price-label-expand">
			<span class="screen-reader-text">
				<?php
				echo esc_html(
					sprintf(
						/* translators: Price option label. */
						__( 'Toggle price option: %s', 'simple-pay' ),
						$label
					)
				);
				?>
			</span>
			<span class="toggle-indicator" aria-hidden="true"></span>
		</button>

		<h2 class="simpay-hndle ui-sortable-handle">
			<span class="custom-field-dashicon dashicons dashicons-menu-alt2" style="cursor: move;"></span>

			<strong class="simpay-price-label-display">
				<?php echo esc_html( $label ); ?>
			</strong>

			<strong class="simpay-price-label-default" style="display: none;">
				<?php esc_html_e( 'Default Price', 'simple-pay' ); ?>
			</strong>
		</h2>

		<div class="simpay-field-data simpay-metabox-content inside">
			<table>
				<?php
				__unstable_price_option_label( $price, $instance_id );
				__unstable_price_option_amount( $price, $instance_id );
				?>
			</table>
			<table
				class="simpay-price-recurring-amount-toggle"
				style="display: <?php echo esc_attr( $one_time_settings_display ); ?>;"
			>
				<?php
				__unstable_price_option_recurring_amount_toggle( $price, $instance_id );
				?>
			</table>
			<table>
				<?php
				__unstable_price_option_custom_amount_toggle( $price, $instance_id );
				?>
			</table>
			<table
				style="
					background: #f5f5f5;
					margin-top: -1px;
				"
			>
				<?php
				__unstable_price_option_custom_amount( $price, $instance_id );
				?>
			</table>
			<table
				class="simpay-price-recurring-settings"
				style="display: <?php echo esc_attr( $recurring_settings_display ); ?>"
			>
				<?php
				__unstable_price_option_billing_period( $price, $instance_id );
				__unstable_price_option_invoice_limit( $price, $instance_id );
				__unstable_price_option_trial( $price, $instance_id );
				__unstable_price_option_setup_fee( $price, $instance_id );
				__unstable_price_option_plan_setup_fee( $price, $instance_id );
				?>
			</table>

			<div
				class="simpay-metabox-content-actions"
				style="display: flex; align-items: center;"
			>
				<button class="button-link button-link-delete simpay-price-remove" style="padding: 8px 0;">
					<?php esc_html_e( 'Remove Price', 'simple-pay' ); ?>
				</button>
				<label
					class="simpay-price-default-check"
					for="<?php echo esc_attr( __unstable_get_input_id( 'default', $instance_id ) ); ?>"
					style="display: flex; align-items: center; padding: 8px 0; margin-left: auto"
				>
					<input
						type="checkbox"
						name="<?php echo esc_attr( __unstable_get_input_name( 'default', $instance_id ) ); ?>"
						id="<?php echo esc_attr( __unstable_get_input_id( 'default', $instance_id ) ); ?>"
						class="simpay-price-default"
						style="margin: 0 4px 0 0;"
						value=""
						<?php if ( true === $price->default ) : ?>
						checked
						<?php endif; ?>
					/>
					<?php esc_html_e( 'Default Price', 'simple-pay' ); ?>
				</label>
			</div>

			<?php
			if (
				$price->is_defined_amount() &&
				$price->__unstable_stripe_object->livemode !==
					$price->form->is_livemode()
			) :
				?>
			<p style="margin: 0; padding: 9px 18px; font-size: 12px; color: #d63638;">
				<?php
				esc_html_e(
					'Price not available in the current payment mode. Please remove and add again.',
					'simple-pay'
				);
				?>
			</p>
			<?php endif; ?>

		</div>
	</div>

	<?php
}

/**
 * Outputs markup for the price option's "Label" field.
 *
 * @since 4.1.0
 * @access private
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
 * @param string                                  $instance_id Unique instance ID.
 */
function __unstable_price_option_label( $price, $instance_id ) {
	$id   = __unstable_get_input_id( 'label', $instance_id );
	$name = __unstable_get_input_name( 'label', $instance_id );

	$label = null !== $price->label
		? $price->label
		: '';
	?>

	<tr class="simpay-panel-field simpay-price-option-label">
		<th>
			<label for="<?php echo esc_attr( $id ); ?>">
				<?php esc_html_e( 'Label', 'simple-pay' ); ?>
			</label>
		</th>
		<td>
			<input
				type="text"
				name="<?php echo esc_attr( $name ); ?>"
				id="<?php echo esc_attr( $id ); ?>"
				class="simpay-field simpay-field-text simpay-price-label"
				value="<?php echo esc_attr( $label ); ?>"
			/>

			<p class="description">
				<?php
				esc_html_e(
					'Optional display label.',
					'simple-pay'
				);
				?>
			</p>
		</td>
	</tr>

	<?php
}

/**
 * Outputs markup for the "Amount" settings.
 *
 * @since 4.1.0
 * @access private
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
 * @param string                                  $instance_id Unique instance ID.
 */
function __unstable_price_option_amount( $price, $instance_id ) {
	$id   = __unstable_get_input_id( 'unit_amount', $instance_id );
	$name = __unstable_get_input_name( 'unit_amount', $instance_id );

	$is_locked = $price->is_defined_amount() && ! isset( $price->__unstable_unsaved );
	?>

	<tr class="simpay-panel-field">
		<th>
			<label for="<?php echo esc_attr( $id ); ?>">
				<?php esc_html_e( 'Amount', 'simple-pay' ); ?>
			</label>

			<?php if ( $is_locked ) : ?>
				<span class="dashicons dashicons-lock"></span>
			<?php endif; ?>

			<?php if ( $is_locked ) : ?>
			<p style="font-weight: normal; margin-top: 3px;">
				<?php
				esc_html_e(
					'Defined prices cannot be modified after creation. Remove this price and create a new price to make changes.',
					'simple-pay'
				)
				?>
			</p>
			<?php endif; ?>
		</th>
		<td
			style="border-bottom: 0;"
			class="<?php echo esc_attr( $is_locked ? 'simpay-price-locked' : '' ); ?>"
		>
			<div style="display: flex; align-items: center;">
				<div>
					<?php
						__unstable_price_option_amount_control(
							$price,
							$instance_id,
							'unit_amount',
							'currency'
						);
					?>
				</div>

				<div style="margin-left: 15px;">
					<?php
					__unstable_price_option_amount_type_control(
						$price,
						$instance_id
					);
					?>
				</div>
			</div>
		</td>
	</tr>

	<?php
}

/**
 * Outputs markup for the "Allow conversion to subscription" setting.
 *
 * @since 4.1.0
 * @access private
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
 * @param string                                  $instance_id Unique instance ID.
 */
function __unstable_price_option_recurring_amount_toggle( $price, $instance_id ) {
	$price_locked_class = $price->is_defined_amount()
		? 'simpay-price-locked'
		: '';

	$name = __unstable_get_input_name( 'can_recur', $instance_id );
	$id   = __unstable_get_input_id( 'can_recur', $instance_id );

	$can_recur_name = __unstable_get_input_name(
		array(
			'recurring',
			'id',
		),
		$instance_id
	);

	$license = simpay_get_license();

	$upgrade_title = __(
		'Unlock Opt-in Subscription Functionality',
		'simple-pay'
	);

	$upgrade_description = __(
		'We\'re sorry, opt-in recurring payments through subscriptions are not available on your plan. Please upgrade to the <strong>Plus</strong> plan or higher to unlock this and other awesome features.',
		'simple-pay'
	);

	$upgrade_url = simpay_pro_upgrade_url(
		'form-price-option-settings',
		'Opt-in subscriptions'
	);

	$upgrade_purchased_url = simpay_docs_link(
		'Opt-in subscriptions (already purchased)',
		$license->is_lite()
			? 'upgrading-wp-simple-pay-lite-to-pro'
			: 'activate-wp-simple-pay-pro-license',
		'form-price-option-settings',
		true
	);
	?>

	<tr
		class="simpay-panel-field <?php echo esc_attr( $price_locked_class ); ?>"
	>
		<td style="border-bottom: 0; padding-bottom: 10px;">
			<label for="<?php echo esc_attr( $id ); ?>">
				<input
					type="checkbox"
					name="<?php echo esc_attr( $name ); ?>"
					id="<?php echo esc_attr( $id ); ?>"
					class="simpay-price-enable-optional-subscription"
					<?php checked( true, $price->can_recur ); ?>
					data-available="<?php echo esc_attr( simpay_subscriptions_enabled() ? 'yes' : 'no' ); ?>"
					data-upgrade-title="<?php echo esc_attr( $upgrade_title ); ?>"
					data-upgrade-description="<?php echo esc_attr( $upgrade_description ); ?>"
					data-upgrade-url="<?php echo esc_url( $upgrade_url ); ?>"
					data-upgrade-purchased-url="<?php echo esc_url( $upgrade_purchased_url ); ?>"
				/>
				<?php
				esc_html_e(
					'Allow price to optionally be purchased as a subscription',
					'simple-pay'
				);
				?>
			</label>

			<?php if ( isset( $price->recurring['id'] ) ) : ?>
			<input
				type="hidden"
				name="<?php echo esc_attr( $can_recur_name ); ?>"
				value="<?php echo esc_attr( $price->recurring['id'] ); ?>"
			/>
			<?php endif; ?>
		</td>
	</tr>

	<?php
}

/**
 * Outputs markup for the "Custom Amount Toggle" setting.
 *
 * @since 4.1.0
 * @access private
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
 * @param string                                  $instance_id Unique instance ID.
 */
function __unstable_price_option_custom_amount_toggle( $price, $instance_id ) {
	$price_locked_class = $price->is_defined_amount()
		? 'simpay-price-locked'
		: '';
	?>

	<tr
		class="simpay-panel-field <?php echo esc_attr( $price_locked_class ); ?>"
	>
		<td>
			<label for="<?php echo esc_attr( __unstable_get_input_id( 'custom', $instance_id ) ); ?>">
				<input
					type="checkbox"
					name="<?php echo esc_attr( __unstable_get_input_name( 'custom', $instance_id ) ); ?>"
					id="<?php echo esc_attr( __unstable_get_input_id( 'custom', $instance_id ) ); ?>"
					class="simpay-price-enable-custom-amount"
					<?php checked( true, null !== $price->unit_amount_min ); ?>
				/>
				<?php
				esc_html_e(
					'Allow amount to be determined by user',
					'simple-pay'
				);
				?>
			</label>
		</td>
	</tr>

	<?php
}

/**
 * Outputs markup for the "Custom Amount" toggle.
 *
 * @since 4.1.0
 * @access private
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
 * @param string                                  $instance_id Unique instance ID.
 */
function __unstable_price_option_custom_amount( $price, $instance_id ) {
	$price_locked_class = $price->is_defined_amount()
		? 'simpay-price-locked'
		: '';

	$custom_amount_id   = __unstable_get_input_id( 'unit_amount_min', $instance_id );
	$custom_amount_name = __unstable_get_input_name( 'unit_amount_min', $instance_id );

	$unit_amount_min = null !== $price->unit_amount_min
		? $price->unit_amount_min
		: simpay_get_currency_minimum( $price->currency );

	$display_style = null !== $price->unit_amount_min ? 'block' : 'none';
	?>

	<tr
		class="simpay-panel-field simpay-price-custom-amount <?php echo esc_attr( $price_locked_class ); ?>"
		style="display: <?php echo esc_attr( $display_style ); ?>"
	>
		<th style="padding-top: 0;">
			<label for="<?php echo esc_attr( $custom_amount_id ); ?>">
				<?php esc_html_e( 'Minimum Amount', 'simple-pay' ); ?>
			</label>
		</th>
		<td>
			<?php
			__unstable_price_option_amount_control_fixed_currency(
				$price,
				$instance_id,
				$unit_amount_min,
				'unit_amount_min'
			);
			?>
		</td>
	</tr>

	<?php
}

/**
 * Outputs markup for the "Billing Period" (recurring) settings.
 *
 * @since 4.1.0
 * @access private
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
 * @param string                                  $instance_id Unique instance ID.
 */
function __unstable_price_option_billing_period( $price, $instance_id ) {
	$price_locked_class = $price->is_defined_amount()
		? 'simpay-price-locked'
		: '';

	$interval_count_id = __unstable_get_input_id(
		array(
			'recurring',
			'interval_count',
		),
		$instance_id
	);

	$interval_count_name = __unstable_get_input_name(
		array(
			'recurring',
			'interval_count',
		),
		$instance_id
	);
	?>

	<tr class="simpay-panel-field <?php echo esc_attr( $price_locked_class ); ?>">
		<th>
			<?php esc_html_e( 'Billing Period', 'simple-pay' ); ?>
		</th>
		<td>
			<div style="display: flex; align-items: center;">
				<span style="margin-right: 5px;">
					<?php esc_html_e( 'every', 'simple-pay' ); ?>
				</span>

				<label
					for="<?php echo esc_attr( $interval_count_id ); ?>"
					class="screen-reader-text"
				>
					<?php esc_html_e( 'Billing Interval Count', 'simple-pay' ); ?>
				</label>

				<?php
				simpay_print_field(
					array(
						'type'       => 'standard',
						'subtype'    => 'number',
						'name'       => $interval_count_name,
						'id'         => $interval_count_id,
						'value'      => null !== $price->recurring
							? $price->recurring['interval_count']
							: 1,
						'class'      => array(
							'simpay-price-recurring-interval-count',
							'simpay-field',
							'small-text',
						),
						'attributes' => array(
							'min'  => 1,
							'max'  => 365,
							'step' => 1,
						),
					)
				);
				?>

				<label for="<?php echo esc_attr( 'price-billing-custom-interval-count-' . $instance_id ); ?>" class="screen-reader-text">
					<?php esc_html_e( 'Billing Interval', 'simple-pay' ); ?>
				</label>
				<?php

				// Current value.
				$value = null !== $price->recurring
					? $price->recurring['interval']
					: 'month';

				// Recurring intervals.
				$intervals = simpay_get_recurring_intervals();

				// Use the current value to set initialize plurazation of options.
				$options = array_map(
					function( $interval ) use ( $value ) {
						return 1 === intval( $value )
							? $interval[0]
							: $interval[1];
					},
					$intervals
				);

				simpay_print_field(
					array(
						'type'       => 'select',
						'name'       => '_simpay_prices[' . $instance_id . '][recurring][interval]',
						'id'         => 'price-billing-custom-interval-count-' . $instance_id,
						'value'      => $value,
						'options'    => $options,
						'attributes' => array(
							'data-intervals' => wp_json_encode( $intervals ),
						),
						'class'      => array(
							'simpay-price-recurring-interval',
						),
					)
				);
				?>
			</div>
		</td>
	</tr>

	<?php
}

/**
 * Outputs markup for the "Invoice Limit" (recurring) settings.
 *
 * @since 4.1.0
 * @access private
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
 * @param string                                  $instance_id Unique instance ID.
 */
function __unstable_price_option_invoice_limit( $price, $instance_id ) {
	$invoice_limit_id = __unstable_get_input_id(
		array(
			'recurring',
			'invoice_limit',
		),
		$instance_id
	);

	$invoice_limit_name = __unstable_get_input_name(
		array(
			'recurring',
			'invoice_limit',
		),
		$instance_id
	);

	$license = simpay_get_license();

	$upgrade_url = simpay_pro_upgrade_url(
		'form-price-option-settings',
		'Invoice limit'
	);
	?>

	<tr class="simpay-panel-field simpay-panel-field--requires-upgrade">
		<th>
			<label for="<?php echo esc_attr( $invoice_limit_id ); ?>">
				<?php esc_html_e( 'Invoice Limit', 'simple-pay' ); ?>
			</label>
		</th>
		<?php if ( false === $license->is_enhanced_subscriptions_enabled() ) : ?>
		<td>
			<div>
				<?php
				esc_html_e(
					'Automatically cancel subscriptions after a specified number of payments (installment plan).',
					'simple-pay'
				);
				?>

				<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noreferrer noopener" class="button button-primary button-small">
					<?php esc_html_e( 'Upgrade', 'simple-pay' ); ?>
				</a>
			</div>
		</td>
		<?php else : ?>
		<td>
			<?php
			simpay_print_field(
				array(
					'type'    => 'standard',
					'subtype' => 'number',
					'name'    => $invoice_limit_name,
					'id'      => $invoice_limit_id,
					'value'   => null !== $price->recurring
						&& isset( $price->recurring['invoice_limit'] )
						? $price->recurring['invoice_limit']
						: '',
					'class'   => array(
						'simpay-field',
						'small-text',
						'simpay-price-invoice-limit',
					),
				)
			);
			?>
			<p class="description">
				<?php
				echo esc_html(
					__(
						'The number of times the recurring amount will be charged (installment plan). Leave blank for indefinite.',
						'simple-pay'
					) . ' '
				);

				echo '<a href="' . esc_url( simpay_docs_link( 'Changes do not affect existing subscriptions', 'installment-plans', 'form-price-option-settings', true ) ) . '#note-of-caution" class="simpay-external-link" target="_blank" rel="noopener noreferrer">';
				esc_html_e(
					'Changes do not affect existing Subscriptions.',
					'simple-pay'
				);
				echo Utils\get_external_link_markup() . '</a>';
				?>
			</p>

			<p class="description">
				<span class="dashicons dashicons-editor-help"></span>

				<?php
				esc_html_e(
					'Webooks are required.',
					'simple-pay'
				);
				?>

				<a href="#help/webhooks" class="simpay-external-link">
					<?php
					esc_html_e(
						'View the webhook documentation',
						'simple-pay'
					);
					?>
				</a>
			</p>
		</td>
		<?php endif; ?>
	</tr>

	<?php
}

/**
 * Outputs markup for the "Trial" (recurring) settings.
 *
 * @since 4.1.0
 * @access private
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
 * @param string                                  $instance_id Unique instance ID.
 */
function __unstable_price_option_trial( $price, $instance_id ) {
	$id = __unstable_get_input_id(
		array( 'recurring', 'trial_period_days' ),
		$instance_id
	);

	$name = __unstable_get_input_name(
		array( 'recurring', 'trial_period_days' ),
		$instance_id
	);

	$trial_period_days = null !== $price->recurring
		&& isset( $price->recurring['trial_period_days'] )
		? $price->recurring['trial_period_days']
		: '';

	$license = simpay_get_license();

	$upgrade_url = simpay_pro_upgrade_url(
		'form-price-option-settings',
		'Free trials'
	);
	?>

	<tr class="simpay-panel-field simpay-panel-field--requires-upgrade">
		<th>
			<label for="<?php echo esc_attr( $id ); ?>">
				<?php esc_html_e( 'Free Trial', 'simple-pay' ); ?>
			</label>
		</th>
		<?php if ( false === $license->is_enhanced_subscriptions_enabled() ) : ?>
		<td>
			<div>
				<?php
				esc_html_e(
					'Let customers trial a subscription plan for a specified period of time before being charged.',
					'simple-pay'
				);
				?>

				<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noreferrer noopener" class="button button-primary button-small">
					<?php esc_html_e( 'Upgrade', 'simple-pay' ); ?>
				</a>
			</div>
		</td>
		<?php else : ?>
		<td>
			<div>
				<?php
				simpay_print_field(
					array(
						'type'    => 'standard',
						'subtype' => 'number',
						'name'    => $name,
						'id'      => $id,
						'value'   => $trial_period_days,
						'class'   => array(
							'simpay-field',
							'small-text',
							'simpay-price-free-trial',
						),
					)
				);
				?>

				<span>
					<?php echo esc_html( _x( 'days', 'trial period', 'simple-pay' ) ); ?>
				</span>
			</div>

			<p class="description">
				<?php esc_html_e( 'Leave empty for no trial.', 'simple-pay' ); ?>
			</p>
		</td>
		<?php endif; ?>
	</tr>

	<?php
}

/**
 * Outputs markup for the "Setup Fee" (recurring) settings.
 *
 * @since 4.1.0
 * @access private
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
 * @param string                                  $instance_id Unique instance ID.
 */
function __unstable_price_option_setup_fee( $price, $instance_id ) {
	$setup_fee_id = __unstable_get_input_id(
		array(
			'line_items',
			'subscription-setup-fee',
			'unit_amount',
		),
		$instance_id
	);

	$setup_fee = null !== $price->line_items
		&& isset( $price->line_items[0] )
		? $price->line_items[0]['unit_amount']
		: 0;

	$license = simpay_get_license();

	$upgrade_url = simpay_pro_upgrade_url(
		'form-price-option-settings',
		'Setup fees'
	);
	?>

	<tr class="simpay-panel-field simpay-panel-field--requires-upgrade">
		<th>
			<label for="<?php echo esc_attr( $setup_fee_id ); ?>">
				<?php esc_html_e( 'Setup Fee', 'simple-pay' ); ?>
			</label>
		</th>
		<?php if ( false === $license->is_enhanced_subscriptions_enabled() ) : ?>
		<td>
			<div>
				<?php
				esc_html_e(
					'Charge an additional fee as part of the first subscription payment.',
					'simple-pay'
				);
				?>

				<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noreferrer noopener" class="button button-primary button-small">
					<?php esc_html_e( 'Upgrade', 'simple-pay' ); ?>
				</a>
			</div>
		</td>
		<?php else : ?>
		<td class="simpay-price-setup-fee">
			<?php
			__unstable_price_option_amount_control_fixed_currency(
				$price,
				$instance_id,
				$setup_fee,
				array(
					'line_items',
					'subscription-setup-fee',
					'unit_amount',
				)
			);
			?>

			<p class="description">
				<?php
				esc_html_e(
					'Additional amount to add to the initial payment.',
					'simple-pay'
				);
				?>
			</p>
		</td>
		<?php endif; ?>
	</tr>

	<?php
}

/**
 * Outputs markup for the "Plan Setup Fee" (recurring) settings.
 *
 * @since 4.1.0
 * @access private
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
 * @param string                                  $instance_id Unique instance ID.
 */
function __unstable_price_option_plan_setup_fee( $price, $instance_id ) {
	$setup_fee_id = __unstable_get_input_id(
		array(
			'line_items',
			'plan-setup-fee',
			'unit_amount',
		),
		$instance_id
	);

	$setup_fee = null !== $price->line_items
		&& isset( $price->line_items[1] )
		? $price->line_items[1]['unit_amount']
		: 0;

	if ( 0 === $setup_fee ) {
		return;
	}
	?>

	<tr class="simpay-panel-field">
		<td style="padding-top: 18px;">
			<button class="button button-secondary button-small simpay-price-legacy-setting-toggle">
				Legacy settings
			</button>
		</td>
	</tr>

	<tr class="simpay-panel-field simpay-price-legacy-setting" style="display: none;">
		<th>
			<label for="<?php echo esc_attr( $setup_fee_id ); ?>">
				<?php esc_html_e( 'Additional Setup Fee', 'simple-pay' ); ?>
			</label>
		</th>
		<td>
			<?php
			__unstable_price_option_amount_control_fixed_currency(
				$price,
				$instance_id,
				$setup_fee,
				array(
					'line_items',
					'plan-setup-fee',
					'unit_amount',
				)
			);
			?>

			<p class="description">
				<?php
				esc_html_e(
					'An additional amount to add to the first payment.',
					'simple-pay'
				);
				?>
			</p>
		</td>
	</tr>

	<?php
}

/**
 * Outputs markup for an "Amount" control.
 *
 * @since 4.1.0
 * @access private
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
 * @param string                                  $instance_id Unique instance ID.
 * @param string                                  $amount_input_name Amount input name.
 * @param string                                  $currency_input_name Currency input name.
 */
function __unstable_price_option_amount_control(
	$price, $instance_id, $amount_input_name, $currency_input_name
) {
	$currency_position = simpay_get_currency_position();
	$is_zero_decimal   = simpay_is_zero_decimal( $price->currency );

	$currency_position_left = in_array(
		$currency_position,
		array( 'left', 'left_space' ),
		true
	);

	$currency_position_right = ! $currency_position_left;

	$amount_placeholder = simpay_format_currency(
		simpay_get_currency_minimum( $price->currency ),
		$price->currency,
		false
	);

	$currency_id   = __unstable_get_input_id( $currency_input_name, $instance_id );
	$currency_name = __unstable_get_input_name( $currency_input_name, $instance_id );

	$amount_id   = __unstable_get_input_id( $amount_input_name, $instance_id );
	$amount_name = __unstable_get_input_name( $amount_input_name, $instance_id );

	$amount = simpay_format_currency( $price->unit_amount, $price->currency, false );
	?>

	<div class="simpay-currency-field">
		<?php if ( $currency_position_left ) : ?>
			<label
				for="<?php echo esc_attr( $currency_id ); ?>"
				class="screen-reader-text"
			>
				<?php esc_html_e( 'Currency', 'simple-pay' ); ?>
			</label>
			<select
				name="<?php echo esc_attr( $currency_name ); ?>"
				id="<?php echo esc_attr( $currency_id ); ?>"
				class="simpay-price-currency simpay-currency-symbol simpay-currency-symbol-left"
				style="border-top-right-radius: 0; border-bottom-right-radius: 0;"
			>
				<?php __unstable_currency_select_options( $price->currency ); ?>
			</select>
		<?php endif; ?>

		<input
			type="text"
			name="<?php echo esc_attr( $amount_name ); ?>"
			id="<?php echo esc_attr( $amount_id ); ?>"
			class="simpay-price-amount simpay-field simpay-field-tiny simpay-field-amount"
			value="<?php echo esc_attr( $amount ); ?>"
			placeholder="<?php echo esc_attr( $amount_placeholder ); ?>"
		/>

		<?php if ( $currency_position_right ) : ?>
			<label
				for="<?php echo esc_attr( $currency_id ); ?>"
				class="screen-reader-text"
			>
				<?php esc_html_e( 'Currency', 'simple-pay' ); ?>
			</label>
			<select
				name="<?php echo esc_attr( $currency_name ); ?>"
				id="<?php echo esc_attr( $currency_id ); ?>"
				class="simpay-price-currency simpay-currency-symbol simpay-currency-symbol-right"
				style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
			>
				<?php __unstable_currency_select_options( $price->currency ); ?>
			</select>
		<?php endif ?>
	</div>

	<?php
}

/**
 * Outputs markup for an "Amount" control with a fixed currency symbol.
 *
 * @since 4.1.0
 * @access private
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
 * @param string                                  $instance_id Unique instance ID.
 * @param int                                     $unit_amount Unit amount to display.
 * @param string                                  $amount_input_name Amount input name.
 */
function __unstable_price_option_amount_control_fixed_currency(
	$price, $instance_id, $unit_amount, $amount_input_name
) {
	$currency_position = simpay_get_currency_position();
	$is_zero_decimal   = simpay_is_zero_decimal( $price->currency );

	$currency_position_left = in_array(
		$currency_position,
		array( 'left', 'left_space' ),
		true
	);

	$currency_position_right = ! $currency_position_left;

	$amount_placeholder = simpay_format_currency(
		simpay_get_currency_minimum( $price->currency ),
		$price->currency,
		false
	);

	$amount_id   = __unstable_get_input_id( $amount_input_name, $instance_id );
	$amount_name = __unstable_get_input_name( $amount_input_name, $instance_id );

	if ( 0 === $unit_amount ) {
		$amount = '';
	} else {
		$amount = simpay_format_currency( $unit_amount, $price->currency, false );
	}

	?>

	<div class="simpay-currency-field">
		<?php if ( $currency_position_left ) : ?>
			<div
				class="simpay-price-currency-symbol simpay-currency-symbol simpay-currency-symbol-left"
				style="border-top-right-radius: 0; border-bottom-right-radius: 0;"
			>
				<?php
				echo esc_html(
					simpay_get_currency_symbol( $price->currency )
				);
				?>
			</div>
		<?php endif; ?>

		<input
			type="text"
			name="<?php echo esc_attr( $amount_name ); ?>"
			id="<?php echo esc_attr( $amount_id ); ?>"
			class="simpay-field simpay-field-tiny simpay-field-amount simpay-price-amount"
			value="<?php echo esc_attr( $amount ); ?>"
			placeholder="<?php echo esc_attr( $amount_placeholder ); ?>"
		/>

		<?php if ( $currency_position_right ) : ?>
			<div
				class="simpay-price-currency-symbol simpay-currency-symbol simpay-currency-symbol-right"
				style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
			>
				<?php
				echo esc_html(
					simpay_get_currency_symbol( $price->currency )
				);
				?>
			</div>
		<?php endif ?>
	</div>

	<?php
}

/**
 * Outputs markup for an "Amount Type" control.
 *
 * @since 4.1.0
 * @access private
 *
 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
 * @param string                                  $instance_id Unique instance ID.
 */
function __unstable_price_option_amount_type_control( $price, $instance_id ) {
	$one_time_active_class = (
		null === $price->recurring ||
		isset( $price->recurring['id'] ) ||
		true === $price->can_recur
	)
		? 'button-primary'
		: '';

	$recurring_active_class = (
		null !== $price->recurring &&
		! isset( $price->recurring['id'] ) &&
		false === $price->can_recur
	)
		? 'button-primary'
		: '';

	$license = simpay_get_license();

	$upgrade_title = __(
		'Unlock Subscription Functionality',
		'simple-pay'
	);

	$upgrade_description = __(
		'We\'re sorry, recurring payments through subscriptions are not available on your plan. Please upgrade to the <strong>Plus</strong> plan or higher to unlock this and other awesome features.',
		'simple-pay'
	);

	$upgrade_url = simpay_pro_upgrade_url(
		'form-price-option-settings',
		'Subscription amount type'
	);

	$upgrade_purchased_url = simpay_docs_link(
		'Subscription amount type (already purchased)',
		$license->is_lite()
			? 'upgrading-wp-simple-pay-lite-to-pro'
			: 'activate-wp-simple-pay-pro-license',
		'form-price-option-settings',
		true
	);
	?>

	<fieldset>
		<legend class="screen-reader-text">
			<?php esc_html( 'Price Type', 'simple-pay' ); ?>
		</legend>

		<div class="button-group simpay-price-amount-type">
			<button
				class="button <?php echo esc_attr( $one_time_active_class ); ?>"
				aria-title="<?php esc_attr_e( 'One time', 'simple-pay' ); ?>"
				data-amount-type="one-time"
			>
				<?php esc_html_e( 'One time', 'simple-pay' ); ?>
			</button>
			<button
				class="button <?php echo esc_attr( $recurring_active_class ); ?>"
				aria-title="<?php esc_attr_e( 'Subscription', 'simple-pay' ); ?>"
				data-amount-type="recurring"
				data-available="<?php echo esc_attr( simpay_subscriptions_enabled() ? 'yes' : 'no' ); ?>"
				data-upgrade-title="<?php echo esc_attr( $upgrade_title ); ?>"
				data-upgrade-description="<?php echo esc_attr( $upgrade_description ); ?>"
				data-upgrade-url="<?php echo esc_url( $upgrade_url ); ?>"
				data-upgrade-purchased-url="<?php echo esc_url( $upgrade_purchased_url ); ?>"
			>
				<?php esc_html_e( 'Subscription', 'simple-pay' ); ?>
			</button>
		</div>
	</fieldset>

	<?php
}

/**
 * Outputs <options> markup for a list of available Stripe currencies.
 *
 * @since 4.1.0
 * @access private
 *
 * @param false|string $selected Currently selected option.
 */
function __unstable_currency_select_options( $selected = false ) {
	$currencies = simpay_get_currencies();
	$options    = array();

	foreach ( $currencies as $code => $symbol ) {
		$options[] = sprintf(
			'<option value="%1$s" %4$s data-symbol="%3$s">%2$s (%3$s)</option>',
			esc_attr( strtolower( $code ) ),
			esc_html( $code ),
			esc_html( $symbol ),
			selected( $selected, strtolower( $code ), false )
		);
	}

	$options = implode( '', $options );

	echo $options;
}

/**
 * Returns a price option's input `name` attribute.
 *
 * @since 4.1.0
 * @access private
 *
 * @param string|array $input Input name. List of items will be nested.
 * @param string       $instance_id Unique instance ID.
 */
function __unstable_get_input_name( $input, $instance_id ) {
	$name = $input;

	if ( is_array( $input ) ) {
		$name = implode( '][', $input );
	}

	return sprintf(
		'_simpay_prices[%1$s][%2$s]',
		$instance_id,
		$name
	);
}

/**
 * Returns a price option's input `id` attribute.
 *
 * @since 4.1.0
 * @access private
 *
 * @param string|array $input Input name. List of items will be nested.
 * @param string       $instance_id Unique instance ID.
 */
function __unstable_get_input_id( $input, $instance_id ) {
	$id = $input;

	if ( is_array( $input ) ) {
		$id = implode( '-', $input );
	}

	return sprintf(
		'simpay-price-%1$s-%2$s',
		$id,
		$instance_id
	);
}
