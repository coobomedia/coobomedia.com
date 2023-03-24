<?php
/**
 * Coupons: Admin page
 *
 * @package SimplePay
 * @subpackage Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.3.0
 */

namespace SimplePay\Pro\Coupons\Admin;

use SimplePay\Core\i18n;
use SimplePay\Core\Utils;

/**
 * Renders the coupon management page.
 *
 * @since 4.3.0
 */
function render_page() {
	$action = isset( $_GET['simpay-action'] )
		? sanitize_text_field( $_GET['simpay-action'] )
		: null;

	switch ( $action ) {
		case 'add-coupon':
			return render_add_coupon();
		default:
			return render_coupon_list();
	}
}

/**
 * Renders the "Add Coupon" page.
 *
 * @since 4.3.0
 *
 * @return void
 */
function render_add_coupon() {
	$list_url = add_query_arg(
		array(
			'post_type' => 'simple-pay',
			'page'      => 'simpay_coupons',
		),
		admin_url( 'edit.php' )
	);
	?>

	<div class="wrap">
		<h1 class="wp-heading-inline">
			<?php esc_html_e( 'Add Coupon', 'simple-pay' ); ?>
		</h1>
		<hr class="wp-header-end">

		<div id="simpay-admin-add-coupon-wrapper">
			<form action="<?php echo esc_url( $list_url ); ?>" method="post">
				<?php
					render_add_coupon_form();
					submit_button( esc_html__( 'Add Coupon', 'simple-pay' ) );
					wp_nonce_field( 'simpay-add-coupon' );
				?>
				<input type="hidden" name="simpay-action" value="add-coupon" />
			</form>
		</div>

	<?php
}

/**
 * Renders the "Add Coupon" form inputs.
 *
 * @since 4.3.0
 *
 * @return void
 */
function render_add_coupon_form() {
	$currencies       = i18n\get_stripe_currencies();
	$currency_options = array();
	$global_currency  = simpay_get_setting( 'currency', 'USD' );

	foreach ( $currencies as $currency_code => $currency_label ) {
		$currency_options[ $currency_code ] = sprintf(
			'%s (%s)',
			strtoupper( $currency_code ),
			simpay_get_currency_symbol( $currency_code )
		);
	}

	$license = simpay_get_license();

	$upgrade_title = __(
		'Unlock Subscription Functionality',
		'simple-pay'
	);

	$upgrade_description = __(
		'We\'re sorry, using coupons with recurring payments is not available on your plan. Please upgrade to the <strong>Professional</strong> plan or higher to unlock this and other awesome features.',
		'simple-pay'
	);

	$upgrade_url = simpay_pro_upgrade_url(
		'coupon-settings',
		'Coupon duration'
	);

	$upgrade_purchased_url = simpay_docs_link(
		'Coupon duration (already purchased)',
		$license->is_lite()
			? 'upgrading-wp-simple-pay-lite-to-pro'
			: 'activate-wp-simple-pay-pro-license',
		'form-price-option-settings',
		true
	);

	?>

	<table class="form-table" role="presentation">
		<tbody>
			<tr>
				<th scope="row">
					<label for="coupon-name">
						<?php esc_html_e( 'Code', 'simple-pay' ); ?>
					</label>
				</th>
				<td>
					<input name="coupon[name]" type="text" id="coupon-name" class="regular-text" required />
					<p class="description">
						<?php
						echo wp_kses(
							__(
								'A unique value users will enter in the coupon field. Letters and numbers only.',
								'simple-pay'
							),
							array(
								'strong' => array(),
							)
						);
						?>
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="coupon-name">
						<?php esc_html_e( 'Type', 'simple-pay' ); ?>
					</label>
				</th>
				<td>
					<select name="coupon[type]" id="coupon-type">
						<option value="percent_off">
							<?php esc_html_e( 'Percentage discount', 'simple-pay' ); ?>
						</option>
						<option value="amount_off">
							<?php esc_html_e( 'Fixed amount discount', 'simple-pay' ); ?>
						</option>
					</select>
				</td>
			</tr>

			<tr id="coupon-type-percent_off">
				<th scope="row">
					<label for="coupon-percent_off">
						<?php esc_html_e( 'Percentage Off', 'simple-pay' ); ?>
					</label>
				</th>
				<td>
					<input name="coupon[percent_off]" type="number" min=".01" max="99.99" step=".01" id="coupon-percent_off" class="small-text" required />
					<span>%</span>
				</td>
			</tr>

			<tr id="coupon-type-amount_off" style="display: none">
				<th scope="row">
					<label for="coupon-amount_off">
						<?php esc_html_e( 'Amount Off', 'simple-pay' ); ?>
					</label>
				</th>
				<td>
					<div style="display: flex">
						<div style="margin-right: 16px;">
							<label for="coupon-currency" style="display: block; margin-bottom: 4px;">
								<?php
								esc_html_e(
									'Currency',
									'simple-pay'
								)
								?>
							</label>
							<select name="coupon[currency]" id="coupon-currency">
								<?php foreach ( $currency_options as $code => $display ) : ?>
									<option
										value="<?php echo esc_attr( $code ); ?>"
										<?php selected( $code, $global_currency ); ?>
									>
										<?php echo esc_html( $display ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<div>
							<label for="coupon-amount_off" style="display: block; margin-bottom: 4px;">
								<?php
								esc_html_e(
									'Discount amount',
									'simple-pay'
								)
								?>
							</label>
							<input name="coupon[amount_off]" type="number" min=".01" step=".01" id="coupon-amount_off" />
						</div>
					</div>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="coupon-enable-duration">
						<?php esc_html_e( 'Duration', 'simple-pay' ); ?>
					</label>
				</th>
				<td>
					<div style="display: flex">
						<div style="margin-right: 16px;">
							<label for="coupon-enable-duration" style="display: block; margin-bottom: 4px;">
								<?php esc_html_e( 'Repeat', 'simple-pay' ); ?>
							</label>
							<select
								name="coupon[duration]"
								id="coupon-enable-duration"
								data-available="<?php echo esc_attr( $license->is_enhanced_subscriptions_enabled() ? 'yes' : 'no' ); ?>"
								data-upgrade-title="<?php echo esc_attr( $upgrade_title ); ?>"
								data-upgrade-description="<?php echo esc_attr( $upgrade_description ); ?>"
								data-upgrade-url="<?php echo esc_url( $upgrade_url ); ?>"
								data-upgrade-purchased-url="<?php echo esc_url( $upgrade_purchased_url ); ?>"
							>
								<option value="once">
									<?php esc_html_e( 'Once', 'simple-pay' ); ?>
								</option>
								<option value="forever">
									<?php esc_html_e( 'Forever', 'simple-pay' ); ?>
								</option>
								<option value="repeating">
									<?php esc_html_e( 'Multiple months', 'simple-pay' ); ?>
								</option>
							</select>
						</div>
						<div id="coupon-has-duration_in_months" style="display: none">
							<label for="coupon-duration_in_months" style="display: block; margin-bottom: 4px;">
								<?php esc_html_e( 'Number of months', 'simple-pay' ); ?>
							</label>
							<input name="coupon[duration_in_months]" type="number" min="1" step="1" id="coupon-duration_in_months" class="small-text" />
						</div>
					</div>

					<p class="description">
						<?php
						esc_html_e(
							'Invoices for recurring payments will continue to receive the discounted amount for this duration.',
							'simple-pay'
						);
						?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php esc_html_e( 'Redemption Restrictions', 'simple-pay' ); ?>
				</th>
				<td>
					<div style="margin: 0 0 20px">
						<label for="coupon-enable-redeem_by" style="display: block; margin: 0 0 8px">
							<input type="checkbox" id="coupon-enable-redeem_by" name="coupon[redeem_by_toggle]" />
							<?php
							esc_html_e(
								'Specify an expiration date and time by which the coupon can be redeemed',
								'simple-pay'
							);
							?>
						</label>

						<div id="coupon-has-redeem_by" style="margin-left: 24px; display: none;">
							<span><?php esc_html_e( 'Redeem by', 'simple-pay' ); ?></span>
							<label for="coupon-redeem_by-date" class="screen-reader-text">
								<?php esc_html_e( 'Redeem by date', 'simple-pay' ); ?>
							</label>
							<input name="coupon[redeem_by_date]" type="date" id="coupon-redeem_by-date" value="<?php echo esc_attr( date( 'Y-m-d', time() ) ); ?>" min="<?php echo esc_attr( date( 'Y-m-d', time() ) ); ?>" />

							<label for="coupon-redeem_by-time" class="screen-reader-text">
								<?php
								echo esc_html(
									sprintf(
										/* translators: %s Timezone string. */
										__( 'Redeem by time (%s)', 'simple-pay' ),
										simpay_wp_timezone_string()
									)
								);
								?>
							</label>
							<input name="coupon[redeem_by_time]" type="time" id="coupon-redeem_by-time" value="<?php echo esc_attr( date( 'H:i', strtotime( '23:59' ) ) ); ?>" min="<?php echo esc_attr( date( 'H:i', time() ) ); ?>" />
							<span><?php echo esc_html( simpay_wp_timezone_string() ); ?></span>
						</div>
					</div>

					<div style="margin: 0 0 20px">
						<label for="coupon-enable-max_redemptions" style="display: block; margin: 0 0 8px;">
							<input type="checkbox" id="coupon-enable-max_redemptions" name="coupon[max_redemptions_toggle]" />
							<?php
							esc_html_e(
								'Limit the total number of times this coupon can be redeemed',
								'simple-pay'
							);
							?>
						</label>

						<div id="coupon-has-max_redemptions" style="margin-left: 24px; display: none;">
							<input name="coupon[max_redemptions]" type="number" step="1" min="1" id="coupon-max_redemptions" class="small-text" />

							<span
								id="coupon-max_redemptions-decorator"
								data-singular="<?php esc_html_e( 'time', 'simple-pay' ); ?>"
								data-plural="<?php esc_html_e( 'times', 'simple-pay' ); ?>"
							>
								<?php esc_html_e( 'time', 'simple-pay' ); ?>
							</span>

							<p class="description" style="margin-bottom: 8px;">
								<?php
								esc_html_e(
									'This limit applies across customers so it won\'t prevent a single customer from redeeming multiple times.',
									'simple-pay'
								);
								?>
							</p>
						</div>
					</div>

					<div>
						<label for="coupon-enable-applies_to_forms" style="display: block; margin: 0 0 8px;">
							<input type="checkbox" id="coupon-enable-applies_to_forms" name="coupon[applies_to_forms_toggle]" />
							<?php
							esc_html_e(
								'Limit the payment forms where this coupon can be redeemed',
								'simple-pay'
							);
							?>
						</label>

						<p class="description" style="margin: -5px 0 10px 24px;">
							<?php esc_html_e( 'Not available with Stripe Checkout', 'simple-pay' ); ?>
						</p>

						<div id="coupon-has-applies_to_forms" style="margin-left: 24px; display: none;">
							<input
								type="text" id="coupon-search-applies_to_forms"
								placeholder="<?php esc_html_e( 'Search for a payment form…', 'simple-pay' ); ?>"
								class="regular-text"
								data-nonce="<?php echo esc_attr( wp_create_nonce( 'simpay-coupons-payment-forms' ) ); ?>"
							/>

							<div id="coupon-restrictions-applies_to_forms" style="margin: 10px 0;"></div>
							<div id="coupon-results-applies_to_forms"></div>
						</div>
					</div>
				</td>
			</tr>
		</tbody>
	</table>

	<?php
}

/**
 * Renders the coupon list.
 *
 * @since 4.3.0
 *
 * @return void
 */
function render_coupon_list() {
	if ( empty( simpay_get_secret_key() ) ) {
		$redirect_url = add_query_arg(
			array(
				'post_type' => 'simple-pay',
				'page'      => 'simpay_coupons',
			),
			admin_url( 'edit.php' )
		);

		include_once SIMPLE_PAY_DIR . '/views/admin-page-stripe-connect.php'; // @phpstan-ignore-line

		return;
	}

	$list_table = new List_Table();
	$list_table->prepare_items();

	$add_new_url = add_query_arg(
		array(
			'post_type'     => 'simple-pay',
			'page'          => 'simpay_coupons',
			'simpay-action' => 'add-coupon',
		),
		admin_url( 'edit.php' )
	);
	?>

	<div class="wrap">
		<h1 class="wp-heading-inline">
			<?php esc_html_e( 'Coupons', 'simple-pay' ); ?>
		</h1>
		<?php if ( ! empty( simpay_get_secret_key() ) ) : ?>
		<a href="<?php echo esc_url( $add_new_url ); ?>" class="page-title-action">
			<?php esc_html_e( 'Add New', 'simple-pay' ); ?>
		</a>
		<?php endif; ?>
		<hr class="wp-header-end">

		<?php
		// @todo Remove when a better notice registry is setup.
		if ( isset( $_GET['message'] ) && 'coupon-added' === $_GET['message'] ) :
			$coupon_name = sanitize_text_field( $_GET['coupon'] );
			?>
		<div class="notice notice-success is-dismissible">
			<p>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %s Coupon name. */
					__( 'Coupon %s added.', 'simple-pay' ),
					'<strong>' . $coupon_name . '</strong>'
				),
				array(
					'strong' => array(),
				)
			);
			?>
			</p>
		</div>
		<?php endif; ?>

		<?php
		// @todo Remove when a better notice registry is setup.
		if ( isset( $_GET['message'] ) && 'coupon-deleted' === $_GET['message'] ) :
			$coupon_name = sanitize_text_field( $_GET['coupon'] );
			?>
		<div class="notice notice-success is-dismissible">
			<p>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %1$s Coupon name. */
					__( 'Coupon %1$s deleted. Customers with the discount already applied will continue to receive discounts until redemption limits are met, but new redemptions will not be allowed.', 'simple-pay' ),
					'<strong>' . $coupon_name . '</strong>'
				),
				array(
					'strong' => array(),
				)
			);
			?>
			</p>
		</div>
		<?php endif; ?>

		<?php
		// @todo Remove when a better notice registry is setup.
		if ( isset( $_GET['message'] ) && 'coupons-deleted' === $_GET['message'] ) :
			$count = intval( $_GET['count'] );
			?>
		<div class="notice notice-success is-dismissible">
			<p>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %1$s Coupon count. */
					_n(
						'%1$s coupon deleted. Customers with the discount already applied will continue to receive discounts until redemption limits are met, but new redemptions will not be allowed.',
						'%1$s coupons deleted. Customers with the discounts already applied will continue to receive discounts until redemption limits are met, but new redemptions will not be allowed.',
						$count,
						'simple-pay'
					),
					'<strong>' . $count . '</strong>'
				),
				array(
					'strong' => array(),
					'a'      => array(
						'href'   => true,
						'target' => true,
						'rel'    => true,
						'class'  => 'simpay-external-link',
					),
					'span'   => array(
						'class' => 'screen-reader-text',
					),
				)
			);
			?>
			</p>
		</div>
		<?php endif; ?>

		<div id="simpay-admin-coupons-wrapper">
			<?php $list_table->views(); ?>

			<form action="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>" method="get">
				<?php $list_table->display(); ?>
				<input type="hidden" name="post_type" value="simple-pay" />
				<input type="hidden" name="page" value="simpay_coupons" />
			</form>

			<div id="ajax-response"></div>
			<br class="clear">
		</div>
	</div>
	<?php
}
