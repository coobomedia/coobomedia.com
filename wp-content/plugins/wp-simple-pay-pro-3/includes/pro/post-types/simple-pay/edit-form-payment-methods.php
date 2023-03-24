<?php
/**
 * Simple Pay: Edit form Payment Methods
 *
 * @package SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 *
 * @var int    $post_id The ID of the payment form.
 * @var array  $payment_methods List of enabled Payment Methods and configuration.
 * @var array  $available_payment_methods List of available Payment Methods.
 * @var array  $payment_methods_with_configs List of Payment Methods with configuration.
 * @var string $form_type Payment form type.
 */

namespace SimplePay\Pro\Admin\Metaboxes\Views\Partials;

use SimplePay\Core\Settings;
use SimplePay\Core\i18n;
use function SimplePay\Pro\Post_Types\Simple_Pay\Util\get_custom_fields;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$account_country = simpay_get_setting( 'account_country', 'us' );
$license         = simpay_get_license();
?>

<tr class="simpay-panel-field">
	<th>
		<div style="display: flex; align-items: center;">
			<strong>
				<?php esc_html_e( 'Payment Methods', 'simple-pay' ); ?>
			</strong>
			<select
				class="simpay-panel-field-payment-method-filter"
				style="font-size: 12px; min-height: 26px; margin-left: 10px; font-weight: normal;"
			>
				<option value="popular">
					<?php esc_html_e( 'Popular', 'simple-pay' ); ?>
				</option>
				<option value="all">
					<?php esc_html_e( 'All', 'simple-pay' ); ?>
				</option>
			</select>
		</div>
	</th>
	<td>
		<div class="simpay-payment-methods">
		<?php
		foreach ( $available_payment_methods as $payment_method ) :
			if ( ! is_a( $payment_method, 'SimplePay\Pro\Payment_Methods\Payment_Method' ) ) {
				continue;
			}

			$disabled = false === $payment_method->is_available();

			// Disable Payment Methods that do not support automatic tax.
			$automatic_tax_restricted_payment_methods = array(
				'ach-debit',
				'fpx',
			);

			if (
				in_array( $payment_method->id, $automatic_tax_restricted_payment_methods, true ) &&
				'automatic' === $tax_status &&
				'stripe_checkout' !== $form_type
			) {
				$disabled = true;
			}

			$checked = (
				! $disabled &&
				isset(
					$payment_methods[ $payment_method->id ],
					$payment_methods[ $payment_method->id ]['id']
				)
			);

			$id = sprintf(
				'simpay-payment-method-%s',
				$payment_method->id
			);

			$currency_limitations = count( i18n\get_stripe_currencies() ) !==
				count( $payment_method->currencies );

			$is_popular = 'popular' === $payment_method->scope;

			$has_config = in_array(
				$payment_method->id,
				$payment_methods_with_config,
				true
			);

			$upgrade_title = sprintf(
				/* translators: %s Payment Method name. */
				esc_html__(
					'Unlock the "%s" Payment Method',
					'simple-pay'
				),
				$payment_method->name
			);

			$upgrade_description = sprintf(
				/* translators: %1$s Payment method name. %2$s Payment method license requirement. */
				__(
					'We\'re sorry, the %1$s payment method is not available on your plan. Please upgrade to the %2$s plan to unlock this and other awesome features.',
					'simple-pay'
				),
				$payment_method->name,
				(
					'<strong>' .
					ucfirst( current( $payment_method->licenses ) ) .
					'</strong>'
				)
			);

			$upgrade_url = simpay_pro_upgrade_url(
				'form-payment-method-settings',
				sprintf( '%s Payment Method', $payment_method->name )
			);

			$upgrade_purchased_url = simpay_docs_link(
				sprintf( '%s Payment Method (already purchased)', $payment_method->name ),
				$license->is_lite()
					? 'upgrading-wp-simple-pay-lite-to-pro'
					: 'activate-wp-simple-pay-pro-license',
				'form-payment-method-settings',
				true
			);
			?>
			<div
				class="simpay-panel-field-payment-method"
				data-payment-method='<?php echo wp_json_encode( $payment_method->to_array_json(), JSON_HEX_QUOT | JSON_HEX_APOS ); ?>'
				style="display: <?php echo esc_attr( $is_popular || $checked ? 'block' : 'none' ); ?>"
			>
				<label for="<?php echo esc_attr( $id ); ?>">
					<div style="display: flex; align-items: center;">
						<span
							class="dashicons dashicons-menu-alt2 simpay-panel-field-payment-method__move simpay-show-if"
							style="margin: 1px 4px 0 5px; cursor: move;"
							data-if="_form_type"
							data-is="on-site"
						></span>

						<div class="simpay-panel-field-payment-method__icon">
							<?php echo $payment_method->icon; // WPCS: XSS ok. ?>
						</div>

						<input
							name="_simpay_payment_methods[<?php echo esc_attr( $payment_method->id ); ?>][id]"
							type="checkbox"
							value="<?php echo esc_attr( $payment_method->id ); ?>"
							id="<?php echo esc_attr( $id ); ?>"
							class="simpay-field simpay-field-checkbox simpay-field simpay-field-checkboxes simpay-payment-method"
							<?php checked( true, $checked && $payment_method->is_country_supported() ); ?>
							<?php disabled( true, $disabled ); ?>
							data-available="no"
							data-payment-method="<?php echo esc_attr( $id ); ?>"
							<?php if ( false === $payment_method->is_available() ) : ?>
							data-disabled
							<?php endif; ?>
							<?php if ( true === $payment_method->recurring ) : ?>
							data-recurring
							<?php endif; ?>
							data-upgrade-title="<?php echo esc_attr( $upgrade_title ); ?>"
							data-upgrade-description="<?php echo esc_attr( $upgrade_description ); ?>"
							data-upgrade-url="<?php echo esc_url( $upgrade_url ); ?>"
							data-upgrade-purchased-url="<?php echo esc_url( $upgrade_purchased_url ); ?>"
						>

						<?php if ( false === $payment_method->is_country_supported() ) : ?>
							<span>
							<?php
							echo wp_kses(
								sprintf(
									/* translators: %1$s Payment Method name. %2$s Opening anchor tag, do not translate. %3$s Closing anchor tag, do not translate. */
									__(
										'%1$s is not available in your %2$sStripe account\'s country%3$s',
										'simple-pay'
									),
									$payment_method->nicename,
									sprintf(
										'<a href="%s" target="_blank" rel="noopener noreferrer">',
										Settings\get_url(
											array(
												'section' => 'stripe',
												'subsection' => 'account',
												'setting' => 'account_country',
											)
										)
									),
									'</a>'
								),
								array(
									'a' => array(
										'href'   => true,
										'target' => true,
										'rel'    => true,
									),
								)
							);
							?>
							</span>
						<?php else : ?>
							<?php echo esc_html( $payment_method->name ); ?>
						<?php endif; ?>

						<div style="display: flex; align-items: center; margin-left: auto;">
							<?php if ( ! empty( $payment_method->internal_docs ) ) : ?>
							<a
								href="<?php echo esc_url( $payment_method->internal_docs ); ?>"
								target="_blank"
								rel="noopener noreferrer"
								class="simpay-panel-field-payment-method__help"
							>
								<span class="dashicons dashicons-editor-help"></span>
								<span class="screen-reader-text"><?php esc_html_e( 'Learn about Payment Method', 'simple-pay' ); ?></span>
							</a>
							<?php endif; ?>

							<?php
							if (
								true === $has_config &&
								true === $payment_method->is_available()
							) :
								?>
							<button
								data-payment-method="<?php echo esc_attr( $payment_method->id ); ?>"
								class="simpay-panel-field-payment-method__configure button button-secondary button-small simpay-show-if"
								data-if="_form_type"
								data-is="on-site"
							>
								<?php esc_html_e( 'Configure', 'simple-pay' ); ?>
							</button>
							<?php endif; ?>
						</div>
					</div>
					<div
						class="simpay-panel-field-payment-method__restrictions"
						style="display: <?php echo esc_attr( $checked ? 'block' : 'none' ); ?>"
					>
						<?php if ( true === $currency_limitations ) : ?>
						<p class="description">
							<?php
							echo esc_html(
								sprintf(
									/* translators: Currency code list. */
									__( 'Currencies: %s', 'simple-pay' ),
									implode(
										', ',
										array_map(
											'strtoupper',
											$payment_method->currencies
										)
									)
								)
							);
							?>
						</p>
						<?php endif; ?>

						<?php if ( false === $payment_method->recurring ) : ?>
						<p class="description">
							<?php
							if ( false === $payment_method->bnpl ) :
								esc_html_e(
									'Payment type: One time',
									'simple-pay'
								);
							else :
								esc_html_e(
									'Payment type: Buy now, pay later',
									'simple-pay'
								);
							endif;
							?>
						</p>
						<?php endif; ?>
					</div>
					<?php if ( in_array( $payment_method->id, $automatic_tax_restricted_payment_methods, true ) ) : ?>
					<div
						class="simpay-panel-field-payment-method__restrictions-ach simpay-show-if"
						data-if="_form_type"
						data-is="on-site"
					>
						<div
							class="simpay-show-if"
							data-if="_tax_status"
							data-is="automatic"
						>
							<p class="description">
								<?php
								echo wp_kses(
									sprintf(
										__(
											'Sorry, %s is not compatible with automatic taxes.',
											'simple-pay'
										),
										$payment_method->name
									),
									array()
								);
								?>
							</p>
						</div>
					</div>
					<?php endif; ?>
				</label>
			</div>
		<?php endforeach; ?>
		</div>
	</td>
</tr>

<div
	title="<?php esc_attr_e( 'Configure Card', 'simple-pay' ); ?>"
	id="simpay-payment-method-configure-card"
	style="display: none;"
>
	<p class="simpay-payment-method-option">
		<label for="simpay-card-hide-postal-code">
			<?php esc_html_e( 'Hide Postal Code', 'simple-pay' ); ?>
		</label>
		<?php
		// Super hacky way to get the legacy value of the card configuration.
		$custom_fields = get_custom_fields( $post_id );
		$cards         = array_filter(
			$custom_fields,
			function( $field ) {
				return 'card' === $field['type'];
			}
		);
		$card          = current( $cards );

		simpay_print_field(
			array(
				'type'  => 'checkbox',
				'name'  => '_simpay_payment_methods[card][hide_postal_code]',
				'id'    => 'simpay-card-hide-postal-code',
				'value' => (
					isset( $payment_methods['card']['hide_postal_code'] ) ||
					isset( $card['postal_code'] )
				)
					? 'yes'
					: 'no',
			)
		);
		?>
	</p>

	<p class="simpay-payment-method-close">
		<button class="button button-primary update"><?php esc_html_e( 'Update', 'simple-pay' ); ?></button>
	</p>
</div>
