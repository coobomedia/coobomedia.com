<?php
/**
 * Forms field: Card
 *
 * @package SimplePay\Pro\Forms\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro\Forms\Fields;

use SimplePay\Pro\Payment_Methods;
use SimplePay\Core\Abstracts\Custom_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Card class.
 *
 * @since 3.0.0
 */
class Card extends Custom_Field {

	/**
	 * Prints HTML for field on frontend.
	 *
	 * @since 3.0.0
	 * @since 3.8.0 Passes field type as second arugment.
	 * @since 3.8.0 Passes form instance as third argument.
	 *
	 * @param array                         $settings Field settings.
	 * @param string                        $type Field type.
	 * @param SimplePay\Core\Abstracts\Form $form Form.
	 * @return string
	 */
	public static function print_html( $settings, $type, $form ) {
		$label = isset( $settings['label'] ) ? $settings['label'] : '';
		$icons = isset( $settings['icons'] ) ? $settings['icons'] : 'no';

		$payment_form    = $form;
		$payment_methods = Payment_Methods\get_form_payment_methods( $form );

		/**
		 * Allows output or action to be taken before ouputting the Payment Form's
		 * Payment Methods.
		 *
		 * @since 3.9.0
		 *
		 * @param \SimplePay\Pro\Payment_Methods\Payment_Method[] $payment_methods Payment Methods.
		 * @param \SimplePay\Core\Abstracts\Form                  $payment_form    Payment Form.
		 */
		do_action(
			'simpay_payment_form_payment_methods_before',
			$payment_methods,
			$payment_form
		);

		$payment_methods = array_values( $payment_methods );

		ob_start();
		?>

		<div class="simpay-form-control simpay-form-control--card simpay-card-container">
			<?php echo self::get_label(); // WPCS: XSS okay. ?>

			<div class="simpay-form-tabs">
				<div
					class="simpay-form-tabs-toggles"
					role="tablist"
					aria-label="<?php echo esc_html( $label ); ?>"
					<?php if ( 1 === count( $payment_methods ) ) : ?>
						style="display: none;"
					<?php endif; ?>
				>
					<?php foreach ( $payment_methods as $i => $payment_method ) : ?>
						<button
							role="tab"
							aria-controls="simpay-payment-method-panel-<?php echo esc_attr( $payment_method->id ); ?>"
							id="simpay-payment-method-toggle-<?php echo esc_attr( $payment_method->id ); ?>"
							class="simpay-payment-method-toggle simpay-form-tabs-toggles__toggle <?php echo esc_attr( 0 === $i ? 'is-active' : '' ); ?>"
							data-payment-method="<?php echo esc_attr( $payment_method->id ); ?>"
							<?php if ( 0 === $i ) : ?>
								aria-selected="true"
								tabindex="0"
							<?php else : ?>
								tabindex="-1"
							<?php endif; ?>
						>
							<?php if ( 'yes' === $icons ) : ?>
								<?php echo $payment_method->icon_sm; ?>
							<?php endif; ?>

							<?php echo esc_html( $payment_method->nicename ); ?>
						</button>
					<?php endforeach; ?>
				</div>

				<?php foreach ( $payment_methods as $i => $payment_method ) : ?>
					<div
						id="simpay-payment-method-panel-<?php echo esc_attr( $payment_method->id ); ?>"
						role="tabpanel"
						aria-labelledby="simpay-payment-method-toggle-<?php echo esc_attr( $payment_method->id ); ?>"
						<?php if ( $i > 0 ) : ?>
							hidden
						<?php endif; ?>
					>

						<?php
						switch ( $payment_method->id ) :
							case 'ach-debit':
								self::get_ach_debit_field();
								break;
							case 'card':
								self::get_card_field( $payment_method );
								break;
							case 'fpx':
								self::get_fpx_field();
								break;
							case 'ideal':
								self::get_ideal_field();
								break;
							case 'p24':
								self::get_p24_field();
								break;
							case 'sepa-debit':
								self::get_sepa_debit_field();
								break;
							case 'klarna':
								self::get_klarna_field();
								break;
							case 'afterpay-clearpay':
								self::get_afterpay_clearpay_field();
								break;
						endswitch;
						?>

					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Outputs markup for Card Payment Method.
	 *
	 * @since 3.9.0
	 *
	 * @param \SimplePay\Pro\Payment_Methods\Payment_Method $payment_method Payment Method.
	 */
	public static function get_card_field( $payment_method ) {
		$id          = self::get_id_attr();
		$postal_code = (
			isset( self::$settings['postal_code'] ) ||
			isset( $payment_method->config['hide_postal_code'] )
		)
			? 'no'
			: 'yes';
		?>

		<div
			id="simpay-card-element-<?php echo esc_attr( $id ); ?>"
			class="simpay-card-wrap simpay-field-wrap"
			data-show-postal-code="<?php echo esc_attr( $postal_code ); ?>"
		>
		</div>

		<?php
	}

	/**
	 * Outputs markup for iDEAL Payment Method.
	 *
	 * @since 3.9.0
	 */
	public static function get_ideal_field() {
		$id = self::get_id_attr();
		?>

		<div
			id="simpay-ideal-element-<?php echo esc_attr( $id ); ?>"
			class="simpay-ideal-wrap simpay-field-wrap"
		>
		</div>

		<?php
	}

	/**
	 * Outputs markup for FPX Payment Method.
	 *
	 * @since 3.9.0
	 */
	public static function get_fpx_field() {
		$id = self::get_id_attr();
		?>

		<div
			id="simpay-fpx-element-<?php echo esc_attr( $id ); ?>"
			class="simpay-fpx-wrap simpay-field-wrap"
		>
		</div>

		<div class="simpay-payment-method-terms">
			<p>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
					__(
						'By proceeding you agree to FPX’s %1$sTerms and Conditions%2$s.',
						'simple-pay'
					),
					'<a href="https://www.mepsfpx.com.my/FPXMain/termsAndConditions.jsp" target="_blank" rel="noopener noreferrer">',
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
			</p>
		</div>

		<?php
	}

	/**
	 * Outputs markup for P24 Payment Method.
	 *
	 * @since 4.2.0
	 */
	public static function get_p24_field() {
		$id = self::get_id_attr();
		?>

		<div
			id="simpay-p24-element-<?php echo esc_attr( $id ); ?>"
			class="simpay-p24-wrap simpay-field-wrap"
		>
		</div>

		<div class="simpay-payment-method-terms">
			<p>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. %3$s Opening anchor tag, do not translate. %4$s Closing anchor tag, do not translate. */
					__(
						'By proceeding I declare that I have familiarized myself with the %1$sregulations%2$s and %3$sinformation obligation%4$s of the Przelewy24 service.',
						'simple-pay'
					),
					'<a href="https://www.przelewy24.pl/regulamin" target="_blank" rel="noopener noreferrer">',
					'</a>',
					'<a href="https://www.przelewy24.pl/obowiazekinformacyjny" target="_blank" rel="noopener noreferrer">',
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
			</p>
		</div>

		<?php
	}

	/**
	 * Outputs markup for ACH Debit Payment Method.
	 *
	 * @since 3.9.0
	 */
	public static function get_ach_debit_field() {
		$id = self::get_id_attr();
		?>

		<div
			id="simpay-ach-debit-<?php echo esc_attr( $id ); ?>"
			class="simpay-ach-debit-wrap simpay-field-wrap"
		>
			<button type="button" class="simpay-btn simpay-bank-btn">
				<?php
				echo wp_kses(
					__( 'Select Bank', 'simple-pay' ),
					array(
						'span' => true,
					)
				);
				?>
			</button>
		</div>

		<div class="simpay-payment-method-terms">
			<p id="simpay-ach-debit-terms" style="display: none;">
				<?php
				printf(
					/* translators: %s Company name */
					__(
						'By proceeding you authorize %1$s to debit the bank account specified above for any amount owed for charges arising from your use of %1$s services and/or purchase of products from %1$s, pursuant to %1$s website and terms, until this authorization is revoked. You may amend or cancel this authorization at any time by providing notice to %1$s with 30 (thirty) days notice.',
						'simple-pay'
					),
					get_bloginfo( 'name' )
				);
				?>
			</p>

			<p id="simpay-ach-debit-terms-recurring" style="display: none;">
				<?php
				printf(
					/* translators: %s Company name */
					__(
						'If you use %1$s services or purchase additional products periodically pursuant to %1$s terms, you authorize %1$s to debit your bank account periodically. Payments that fall outside of the regular debits authorized above will only be debited after your authorization is obtained.',
						'simple-pay'
					),
					get_bloginfo( 'name' )
				);
				?>
			</p>
		</div>

		<?php
	}

	/**
	 * Outputs markup for SEPA Direct Debit Payment Method.
	 *
	 * @since 4.2.0
	 */
	public static function get_sepa_debit_field() {
		$id = self::get_id_attr();
		?>

		<div
			id="simpay-sepa-debit-element-<?php echo esc_attr( $id ); ?>"
			class="simpay-sepa-debit-wrap simpay-field-wrap"
		>
		</div>

		<div class="simpay-payment-method-terms">
			<p>
				<?php
				printf(
					/* translators: %s Company name */
					__(
						'By providing your payment information and confirming this payment, you authorise (A) %s and Stripe, our payment service provider, to send instructions to your bank to debit your account and (B) your bank to debit your account in accordance with those instructions. As part of your rights, you are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within 8 weeks starting from the date on which your account was debited.',
						'simple-pay'
					),
					get_bloginfo( 'name' )
				);
				?>
			</p>
		</div>

		<?php
	}

	/**
	 * Outputs markup for Klarna Payment Method.
	 *
	 * @since 4.4.4
	 *
	 * @return void
	 */
	private static function get_klarna_field() {
		$id = self::get_id_attr();
		?>

		<div
			id="simpay-klarna-element-<?php echo esc_attr( $id ); ?>"
			class="simpay-klarna-wrap simpay-field-wrap"
		>
			<?php
			esc_html_e(
				'Buy now and pay later with Klarna.',
				'simple-pay'
			);
			?>
		</div>

		<?php
	}

	/**
	 * Outputs markup for "Afterpay / Clearpay" Payment Method.
	 *
	 * @since 4.4.4
	 *
	 * @return void
	 */
	private static function get_afterpay_clearpay_field() {
		$id = self::get_id_attr();
		?>

		<div
			id="simpay-afterpay-clearpay-element-<?php echo esc_attr( $id ); ?>"
			class="simpay-afterpay-clearpay-wrap simpay-field-wrap"
		>
		</div>

		<?php
	}

}
