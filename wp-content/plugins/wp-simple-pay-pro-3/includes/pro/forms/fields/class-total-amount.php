<?php
/**
 * Forms field: Total Amount
 *
 * @package SimplePay\Pro\Forms\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro\Forms\Fields;

use SimplePay\Core\Abstracts\Custom_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Total_Amount class.
 *
 * @since 3.0.0
 */
class Total_Amount extends Custom_Field {

	/**
	 * Prints HTML for field on frontend.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {
		$prices    = simpay_get_payment_form_prices( self::$form );
		$recurring = simpay_payment_form_prices_has_subscription_price( $prices );

		ob_start();

		echo '<div class="simpay-form-control simpay-amounts-container">';

		// Subtotal.
		self::print_subtotal_amount( $settings );

		// Coupons.
		self::print_coupon( $settings );

		// Taxes.
		$tax_status = get_post_meta( self::$form->id, '_tax_status', true );
		$tax_rates  = simpay_get_payment_form_tax_rates( self::$form );

		// Fixed rates (or unsaved setting).
		if (
			( empty( $tax_status ) && ! empty( $tax_rates ) ) ||
			'fixed-global' === $tax_status && ! empty( $tax_rates )
		) {
			self::print_tax_amount_label( $settings );
		} else if ( 'automatic' === $tax_status ) {
			self::print_automatic_tax_label( $settings );
		}

		// Total.
		self::print_total_amount_label( $settings );

		// Recurring.
		if ( true === $recurring ) {
			self::print_recurring_total_label( $settings );
		}

		echo '</div>';

		return ob_get_clean();
	}

	/**
	 * HTML for the subtotal amount.
	 *
	 * @since 4.1.0
	 *
	 * @param array $settings Field settings.
	 */
	public static function print_subtotal_amount( $settings ) {
		$label = isset( $settings['subtotal_label'] ) && ! empty( $settings['subtotal_label'] )
			? $settings['subtotal_label']
			: esc_html__( 'Subtotal', 'simple-pay' );

		$prices = simpay_get_payment_form_prices( self::$form );
		$price  = simpay_payment_form_get_default_price( $prices );
		?>

		<div class="simpay-subtotal-amount-container">
			<p class="simpay-subtotal-amount-label simpay-label-wrap">
				<span>
					<?php echo esc_html( $label ); ?>
				</span>
				<span class="simpay-subtotal-amount-value">
					<?php
					echo simpay_format_currency(
						$price->unit_amount,
						$price->currency
					);
					?>
				</span>
			</p>
		</div>

		<?php
	}

	/**
	 * HTML for the coupon amount.
	 *
	 * @since 4.1.0
	 *
	 * @param array $settings Field settings.
	 */
	public static function print_coupon( $settings ) {
		?>

		<div
			class="simpay-coupon-amount-container"
			style="display: none;"
		>
			<p class="simpay-coupon-amount-label simpay-label-wrap">
				<span class="simpay-coupon-amount-name"></span>
				<span class="simpay-coupon-amount-value"></span>
			</p>
		</div>

		<?php
	}

	/**
	 * HTML for the total amount label.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Field settings.
	 */
	public static function print_total_amount_label( $settings ) {
		$label = isset( $settings['label'] ) && ! empty( $settings['label'] )
			? $settings['label']
			: esc_html__( 'Total Amount:', 'simple-pay' );

		$prices = simpay_get_payment_form_prices( self::$form );
		$price  = simpay_payment_form_get_default_price( $prices );
		?>

		<div class="simpay-total-amount-container">
			<p class="simpay-total-amount-label simpay-label-wrap">
				<?php echo esc_html( $label ); ?>
				<span class="simpay-total-amount-value">
					<?php
					echo simpay_format_currency(
						$price->unit_amount,
						$price->currency
					);
					?>
				</span>
			</p>
		</div>

		<?php
	}

	/**
	 * HTML for the recurring total label
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Field settings.
	 */
	public static function print_recurring_total_label( $settings ) {
		$label = isset( $settings['recurring_total_label'] ) &&
			! empty( $settings['recurring_total_label'] )
				? $settings['recurring_total_label']
				: esc_html__( 'Recurring payment', 'simple-pay' );

		$prices = simpay_get_payment_form_prices( self::$form );
		$price  = simpay_payment_form_get_default_price( $prices );

		$intervals = simpay_get_recurring_intervals();
		?>

		<div
			class="simpay-total-amount-recurring-container"
			<?php if ( null === $price->recurring ) : ?>
				style="display: none;"
			<?php endif; ?>
		>
			<p class="simpay-total-amount-recurring-label simpay-label-wrap">
				<?php echo esc_html( $label ); ?>

				<span class="simpay-total-amount-recurring-value">
					<?php
					echo esc_html(
						$price->get_generated_label(
							array(
								'include_trial'      => false,
								'include_line_items' => false,
							)
						)
					);
					?>
				</span>
			</p>
		</div>

		<?php
	}

	/**
	 * HTML for the tax amount label
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Field settings.
	 */
	public static function print_tax_amount_label( $settings ) {
		$tax_rates = simpay_get_payment_form_tax_rates( self::$form );

		$prices = simpay_get_payment_form_prices( self::$form );
		$price  = simpay_payment_form_get_default_price( $prices );
		?>

		<div class="simpay-tax-amount-container">
			<?php
				foreach ( $tax_rates as $tax_rate ) :

				$wrapper_classnames = array(
					'simpay-tax-rate-' . $tax_rate->id,
					'simpay-tax-rate-' . $tax_rate->calculation,
					'simpay-total-amount-tax-label',
					'simpay-label-wrap',
				);

				$value_classnames = array(
					'simpay-tax-amount-value',
					'simpay-tax-amount-value-' . $tax_rate->id,
				);
			?>
			<p class="<?php echo esc_attr( implode( ' ', $wrapper_classnames ) ); ?>">
				<span>
					<?php echo esc_html( $tax_rate->get_display_label() ); ?>
				</span>
				<span class="<?php echo esc_attr( implode( ' ', $value_classnames ) ); ?>">
					<?php
					echo simpay_format_currency(
						$price->unit_amount * ( $tax_rate->percentage / 100 ),
						$price->currency
					);
					?>
				</span>
			</p>
			<?php endforeach; ?>
		</div>

		<?php
	}

	/**
	 * HTML for the automatic tax amount label.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Field settings.
	 * @return void
	 */
	public static function print_automatic_tax_label( $settings ) {
		?>

		<div class="simpay-tax-amount-container">
			<p class="simpay-total-amount-tax-label simpay-automatic-tax-label simpay-label-wrap">
				<span class="simpay-tax-amount-label">
					<?php
					echo esc_html(
						_x( 'Tax', 'automatic tax amount label', 'simple-pay' )
					);
					?>
				</span>
				<span class="simpay-tax-amount-value">
					<?php
					if ( 'stripe_checkout' === self::$form->get_display_type() ) :
						esc_html_e(
							'Calculated at checkout',
							'simple-pay'
						);
					else :
						esc_html_e(
							'Enter address to calculate',
							'simple-pay'
						);
					endif;
					?>
				</span>
			</p>
		</div>

		<?php
	}
}
