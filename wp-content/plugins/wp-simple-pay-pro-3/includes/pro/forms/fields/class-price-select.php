<?php
/**
 * Form Field: Price Select
 *
 * @package SimplePay\Pro\Forms\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

namespace SimplePay\Pro\Forms\Fields;

use SimplePay\Core\Abstracts\Custom_Field;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Price_Select class.
 *
 * @since 4.1.0
 */
class Price_Select extends Custom_Field {

	/**
	 * Prints HTML for price on frontend.
	 *
	 * @since 4.1.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {
		$display_type = self::get_display_type();

		switch ( $display_type ) {
			case 'dropdown':
				return self::get_price_selector_select();
			case 'radio':
				return self::get_price_selector_radio();
			case 'list':
				return self::get_price_selector_list();
			case 'buttons':
				return self::get_price_selector_buttons();
		}
	}

	/**
	 * Returns the markup for the dropdown/select price selector.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	private static function get_price_selector_select() {
		$prices = simpay_get_payment_form_prices( self::$form );
		$id     = self::get_id_attr();

		// Backwards compatibility fallback.
		$label_fallback = simpay_get_saved_meta(
			self::$form->id,
			'_plan_select_form_field_label',
			''
		);

		$display_type  = self::get_display_type();
		$display_style = 1 === count( $prices ) ? 'none' : 'block';

		$wrapper_classes = 'simpay-plan-wrapper simpay-field-wrap simpay-dropdown-wrap';

		ob_start();
		?>

		<div
			class="simpay-form-control simpay-plan-select-container"
			data-display-type="<?php echo esc_attr( $display_type ); ?>"
			style="display: <?php echo esc_attr( $display_style ); ?>"
		>
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.
			echo self::get_label( 'label', $label_fallback );
			?>
			<div class="<?php echo esc_attr( $wrapper_classes ); ?>">
				<select id="<?php echo esc_attr( $id ); ?>" name="simpay_price">
				<?php
				/* @var $prices \SimplePay\Core\PaymentForm\PriceOption[] */
				foreach ( $prices as $price ) :
				?>
					<option
						<?php
						// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
						echo self::get_price_option_atts( $price, 'select', '' );
						?>
					>
						<?php echo esc_html( $price->get_display_label() ); ?>
					</option>
				<?php endforeach; ?>
				</select>
			</div>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Returns the markup for the radio price selector.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	private static function get_price_selector_radio() {
		$prices = simpay_get_payment_form_prices( self::$form );
		$id     = self::get_id_attr();

		// Backwards compatibility fallback.
		$label_fallback = simpay_get_saved_meta(
			self::$form->id,
			'_plan_select_form_field_label',
			''
		);

		$label = isset( self::$settings[ 'label' ] )
			? self::$settings[ 'label' ]
			: $label_fallback;

		$label_classes = array();

		if ( empty( $label ) ) {
			$label     = self::$settings['type'];
			$label_classes[] = 'screen-reader-text';
		}

		$wrapper_classes = 'simpay-plan-wrapper simpay-field-wrap simpay-dropdown-wrap';

		$display_type = self::get_display_type();

		$display_style = 1 === count( $prices ) ? 'none' : 'block';

		ob_start();
		?>

		<fieldset
			class="simpay-form-control simpay-plan-select-container"
			data-display-type="<?php echo esc_attr( $display_type ); ?>"
			style="display: <?php echo esc_attr( $display_style ); ?>"
		>
			<div class="simpay-<?php echo esc_attr( self::$settings['type'] ); ?>-label simpay-label-wrap">
				<legend class="<?php echo esc_attr( implode( ' ', $label_classes ) ); ?>">
					<?php echo esc_html( $label ); ?>
				</legend>
			</div>

			<div class="<?php echo esc_attr( $wrapper_classes ); ?>">
				<ul class="simpay-multi-plan-radio-group">
				<?php
				/* @var $prices \SimplePay\Core\PaymentForm\PriceOption[] */
				foreach ( $prices as $price ) :
					$id = self::get_unique_price_id( $price );
				?>
					<li>
						<label for="<?php echo esc_attr( $id ); ?>">
							<input
								type="radio"
								<?php
								// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
								echo self::get_price_option_atts( $price, 'radio', $id );
								?>
							/>
							<?php echo esc_html( $price->get_display_label() ); ?>
						</label>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
		</fieldset>

		<?php
		return ob_get_clean();
	}

	/**
	 * Returns the markup for the list price selector.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	private static function get_price_selector_list() {
		$prices = simpay_get_payment_form_prices( self::$form );
		$id     = self::get_id_attr();

		// Backwards compatibility fallback.
		$label_fallback = simpay_get_saved_meta(
			self::$form->id,
			'_plan_select_form_field_label',
			''
		);

		$label = isset( self::$settings[ 'label' ] )
			? self::$settings[ 'label' ]
			: $label_fallback;

		$label_classes = array();

		if ( empty( $label ) ) {
			$label     = self::$settings['type'];
			$label_classes[] = 'screen-reader-text';
		}

		$wrapper_classes = 'simpay-plan-wrapper simpay-field-wrap';

		$display_type = self::get_display_type();

		$display_style = 1 === count( $prices ) ? 'none' : 'block';

		ob_start();
		?>

		<fieldset
			class="simpay-form-control simpay-plan-select-container"
			data-display-type="<?php echo esc_attr( $display_type ); ?>"
			style="display: <?php echo esc_attr( $display_style ); ?>"
		>
			<div class="simpay-<?php echo esc_attr( self::$settings['type'] ); ?>-label simpay-label-wrap">
				<legend class="<?php echo esc_attr( implode( ' ', $label_classes ) ); ?>">
					<?php echo esc_html( $label ); ?>
				</legend>
			</div>

			<div class="<?php echo esc_attr( $wrapper_classes ); ?>">
				<ul class="simpay-multi-plan-list-group">
				<?php
				/* @var $prices \SimplePay\Core\PaymentForm\PriceOption[] */
				foreach ( $prices as $price ) :
					$custom_label = $price->label;
					$amount       = $price->get_generated_label();

					$label = ! empty( $custom_label ) ? $custom_label : $amount;

					$id = self::get_unique_price_id( $price );
				?>
					<li>
						<input
							type="radio"
							<?php
							// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
							echo self::get_price_option_atts( $price, 'radio', $id );
							?>
						/>
						<label for="<?php echo esc_attr( $id ); ?>">
							<?php echo esc_html( $label ); ?>
							<?php if ( ! empty( $custom_label ) ) : ?>
							<small><?php echo esc_html( $amount ); ?></small>
							<?php endif; ?>

							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
						</label>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
		</fieldset>

		<?php
		return ob_get_clean();
	}

	/**
	 * Returns the markup for the buttons price selector.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	private static function get_price_selector_buttons() {
		$prices = simpay_get_payment_form_prices( self::$form );
		$id     = self::get_id_attr();

		// Backwards compatibility fallback.
		$label_fallback = simpay_get_saved_meta(
			self::$form->id,
			'_plan_select_form_field_label',
			''
		);

		$label = isset( self::$settings[ 'label' ] )
			? self::$settings[ 'label' ]
			: $label_fallback;

		$label_classes = array();

		if ( empty( $label ) ) {
			$label     = self::$settings['type'];
			$label_classes[] = 'screen-reader-text';
		}

		$wrapper_classes = 'simpay-plan-wrapper simpay-field-wrap';

		$display_type = self::get_display_type();

		$display_style = 1 === count( $prices ) ? 'none' : 'block';

		ob_start();
		?>

		<fieldset
			class="simpay-form-control simpay-plan-select-container"
			data-display-type="<?php echo esc_attr( $display_type ); ?>"
			style="display: <?php echo esc_attr( $display_style ); ?>"
		>
			<div class="simpay-<?php echo esc_attr( self::$settings['type'] ); ?>-label simpay-label-wrap">
				<legend class="<?php echo esc_attr( implode( ' ', $label_classes ) ); ?>">
					<?php echo esc_html( $label ); ?>
				</legend>
			</div>

			<div class="<?php echo esc_attr( $wrapper_classes ); ?>">
				<ul class="simpay-multi-plan-buttons-group">
				<?php
				/* @var $prices \SimplePay\Core\PaymentForm\PriceOption[] */
				foreach ( $prices as $price ) :
					$label = $price->get_simplified_label();

					$is_custom = ! simpay_payment_form_prices_is_defined_price(
						$price->id
					);

					$classname = 'simpay-multi-plan-buttons-group__' . (
						$is_custom ? 'custom' : 'defined'
					);

					$id = self::get_unique_price_id( $price );
				?>
					<li class="<?php echo esc_attr( $classname ); ?>">
						<input
							type="radio"
							<?php
							// phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
							echo self::get_price_option_atts( $price, 'radio', $id );
							?>
						/>
						<label for="<?php echo esc_attr( $id ); ?>">
							<?php echo esc_html( $label ); ?>
						</label>
					</li>
				<?php endforeach; ?>
				</ul>
			</div>
		</fieldset>

		<?php
		return ob_get_clean();
	}

	/**
	 * Returns markup for price option attributes.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @param string $display_type Price option display type.
	 * @param string $id HTML element ID (unique).
	 * @return string
	 */
	private static function get_price_option_atts( $price, $display_type, $id ) {
		$selected_func = 'radio' === $display_type ? 'checked' : 'selected';

		return sprintf( '
			id="%1$s"
			class="simpay-multi-sub simpay-price-option-%8$s"
			data-price=\'%2$s\'
			%3$s
			%4$s
			%5$s
			%6$s
			%7$s
			',
			esc_attr( $id ),
			wp_json_encode(
				array_merge(
					$price->to_array(),
					array(
						'generated_label'     => $price->get_generated_label(),
						'simplified_label'    => $price->get_simplified_label(),
						'currency_min_amount' => simpay_get_currency_minimum(
							$price->currency
						),
					)
				),
				JSON_HEX_QUOT | JSON_HEX_APOS
			),
			in_array( $display_type, array( 'radio', 'list' ), true )
				? 'name="simpay_price"'
				: '',
			$selected_func( true, $price->default && $price->is_in_stock(), false ),
			disabled( false, $price->is_in_stock(), false),

			// Backwards compatibility attributes.
			self::get_price_option_atts_price_id( $price ),
			self::get_price_option_atts_recurring( $price ),

			esc_attr( $price->id )
		);
	}

	/**
	 * Returns markup for price option attributes when a Price/Plan
	 * has been defined.
	 *
	 * These attributes are no longer used by the plugin but remain in the DOM
	 * for backwards compatibility.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @return string
	 */
	private static function get_price_option_atts_price_id( $price ) {
		if ( null == $price->id ) {
			return 'value=""';
		}

		$currency_amount = simpay_convert_amount_to_dollars(
			$price->unit_amount
		);

		return sprintf( '
			value="%1$s"
			data-plan-id="%1$s"
			data-plan-amount="%2$s"',
			esc_attr( $price->id ),
			esc_attr( $currency_amount )
		);
	}

	/**
	 * Returns markup for price option attributes when a Price/Plan
	 * is recurring.
	 *
	 * These attributes are no longer used by the plugin but remain in the DOM
	 * for backwards compatibility.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @return string
	 */
	private static function get_price_option_atts_recurring( $price ) {
		if ( true === $price->can_recur || null === $price->recurring ) {
			return '';
		}

		$currency_setup_fee_amount = '';

		if ( null !== $price->line_items && isset( $price->line_items[0] ) ) {
			$currency_setup_fee_amount = simpay_convert_amount_to_dollars(
				$price->line_items[0]['unit_amount']
			);
		}

		$invoice_limit = 0;

		if ( null !== $price->recurring && isset( $price->recurring['invoice_limit'] ) ) {
			$invoice_limit = absint( $price->recurring['invoice_limit'] );
		}

		$trial_period_days = null !== $price->recurring
			&& isset( $price->recurring['trial_period_days'] );

		return sprintf( '
			data-plan-setup-fee="%1$s"
			data-plan-interval="%2$s"
			data-plan-interval-count="%3$s"
			data-plan-max-charges="%4$s"
			data-plan-trial="%5$s"
			',
			esc_attr( $currency_setup_fee_amount ),
			esc_attr( $price->recurring['interval'] ),
			esc_attr( $price->recurring['interval_count'] ),
			esc_attr( $invoice_limit ),
			esc_attr( $trial_period_days )
		);
	}

	/**
	 * Retrieves the field's display type.
	 *
	 * Falls back to the legacy "User select" display style.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	private static function get_display_type() {
		$legacy_display_type = simpay_get_saved_meta(
			self::$form->id,
			'_multi_plan_display',
			'radio'
		);

		$display_type = ! empty( self::$settings['display_type'] )
			? self::$settings['display_type']
			: $legacy_display_type;

		return $display_type;
	}

	/**
	 * Retrieves an unique ID for HTML to avoid duplicate elements.
	 *
	 * Based off the price option ID and a random number.
	 *
	 * @since 4.2.0
	 *
	 * @param \SimplePay\Core\PaymentForm\PriceOption $price Price option.
	 * @return string
	 */
	private static function get_unique_price_id( $price ) {
		return $price->id . '-instance-' . rand(0, 1000);
	}

}
