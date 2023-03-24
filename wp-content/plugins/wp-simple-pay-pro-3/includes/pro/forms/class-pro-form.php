<?php
/**
 * Forms: Embed/Overlay
 *
 * @package SimplePay\Pro\Forms
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro\Forms;

use SimplePay\Core\PaymentForm\PriceOption;
use SimplePay\Core\Forms\Default_Form;
use SimplePay\Pro\Payment_Methods;

use function SimplePay\Pro\Post_Types\Simple_Pay\Util\get_custom_fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pro_Form class.
 *
 * @since 3.0.0
 */
class Pro_Form extends Default_Form {

	/**
	 * Determines if Subscriptions have been printed.
	 *
	 * @var bool
	 * @since 3.0.0
	 */
	public $printed_subscriptions = false;

	/**
	 * Determines if Custom Amount has been printed.
	 *
	 * @var bool
	 * @since 3.0.0
	 */
	public $printed_custom_amount = false;

	/**
	 * Form constructor.
	 *
	 * @param int|string $id Payment Form ID.
	 */
	public function __construct( $id ) {

		parent::__construct( $id );

		if ( null === $this->post ) {
			return;
		}

		// TODO Need to set this property?
		// Set our form specific filter to apply to each setting.
		$this->filter = 'simpay_form_' . $this->id;

		// Setup the global settings tied to this form.
		$this->pro_set_global_settings();

		// Setup the post meta settings tied to this form.
		$this->pro_set_post_meta_settings();

	}

	/**
	 * Retrieves saved "Subscription Plans" used for "User Select Plan"
	 * Subscription settings.
	 *
	 * @since 3.9.0
	 * @since 4.1.0 Deprecated.
	 *
	 * @return array List of saved Plans. Not necessarily still valid in Stripe.
	 */
	public function get_subscription_plans() {
		// Check property first. This allows unit testing of legacy properties.
		$prices = isset( $this->prices )
			? $this->prices
			: simpay_get_payment_form_prices( $this );

		$plans = array();

		/* @var \SimplePay\Core\PaymentForm\PriceOption[] */
		foreach ( $prices as $price ) {
			if ( false === $price->is_defined_amount() ) {
				continue;
			}

			if ( ! isset( $price->recurring ) ) {
				continue;
			}

			$max_charges = isset( $price->recurring['invoice_limit'] )
				? $price->recurring['invoice_limit']
				: 0;

			$label = null !== $price->label
				? $price->label
				: '';

			$plans[] = array(
				'select_plan'  => $price->id,
				'max_charges'  => $max_charges,
				'custom_label' => $label,
				'plan_object'  => $price->__unstable_stripe_object,
			);
		}

		return $plans;
	}

	/**
	 * Hooks in to WordPress.
	 *
	 * @since 3.0.0
	 */
	public function register_hooks() {
		parent::register_hooks();

		add_action( 'simpay_form_' . $this->id . '_before_payment_form', array( $this, 'before_payment_form' ) );
		add_action( 'simpay_form_' . $this->id . '_after_form_display', array( $this, 'after_form_display' ) );
		add_filter( 'simpay_form_' . $this->id . '_custom_fields', array( $this, 'get_custom_fields_html' ), 10, 3 );
		add_action( 'simpay_form_' . $this->id . '_before_form_bottom', array( $this, 'pro_html' ) );

		add_filter( 'simpay_form_' . $this->id . '_classes', array( $this, 'pro_form_classes' ) );
		add_filter( 'simpay_form_' . $this->id . '_script_variables', array( $this, 'pro_get_form_script_variables' ), 10, 2 );
		add_filter( 'simpay_payment_button_class', array( $this, 'payment_button_class' ) );
	}

	/**
	 * Returns the Payment Button classes.
	 *
	 * @since 3.0.0
	 *
	 * @param array $classes List of class names.
	 * @return array
	 */
	public function payment_button_class( $classes ) {

		$button_action = ( 'overlay' == $this->get_form_display_type() ) ? 'simpay-modal-btn' : 'simpay-payment-btn';

		if ( isset( $classes['simpay-payment-btn'] ) ) {
			unset( $classes['simpay-payment-btn'] );
		}

		$classes[] = $button_action;

		return $classes;
	}

	/**
	 * Returns the Payment Form classes.
	 *
	 * @since 3.0.0
	 *
	 * @param array $classes List of class names.
	 * @return array
	 */
	public function pro_form_classes( $classes ) {

		$classes[] = 'simpay-checkout-form--' . $this->get_form_display_type();

		// If Stripe Checkout is enabled, maybe add custom form styling.
		if ( 'stripe_checkout' === $this->get_form_display_type() ) {
			$styled = simpay_get_filtered(
				'stripe_enable_form_styles',
				simpay_get_saved_meta( $this->id, '_enable_stripe_checkout_form_styles', 'no' ),
				$this->id
			);

			if ( 'yes' === $styled ) {
				$classes[] = 'simpay-checkout-form--stripe_checkout-styled';

				// If the on-page fields should not be styled remove the `.simpay-styled` class.
			} else {
				$simpay_styled = array_search( 'simpay-styled', $classes, true );

				if ( false !== $simpay_styled ) {
					unset( $classes[ $simpay_styled ] );
				}
			}
		}

		return $classes;
	}

	/**
	 * Outputs additional markup before the payment form.
	 *
	 * @since 3.0.0
	 */
	public function before_payment_form() {

		$html              = '';
		$heading_html      = '';
		$form_display_type = $this->get_form_display_type();
		$form_title        = $this->company_name;
		$form_description  = $this->item_description;

		// Add title & description text for Embedded & Overlay form types if they exist.

		if ( 'embedded' === $form_display_type || 'overlay' === $form_display_type ) {

			if ( ! empty( $form_title ) ) {
				$heading_html .= '<h3 class="simpay-form-title">' . esc_html( $form_title ) . '</h3>';
			}

			if ( ! empty( $form_description ) ) {
				$heading_html .= '<p class="simpay-form-description">' . esc_html( $form_description ) . '</p>';
			}
		}

		if ( 'embedded' === $form_display_type ) {

			if ( ! empty( $heading_html ) ) {
				$html .= '<div class="simpay-embedded-heading simpay-styled">';
				$html .= $heading_html;
				$html .= '</div>';
			}
		} elseif ( 'overlay' === $form_display_type ) {

			$html .= '<label for="simpay-modal-control-' . esc_attr( $this->id ) . '" class="simpay-modal-control-open">' . $this->get_payment_button( $this->custom_fields ) . '</label>';
			$html .= '<input type="checkbox" id="simpay-modal-control-' . esc_attr( $this->id ) . '" class="simpay-modal-control" data-form-id="' . esc_attr( $this->id ) . '">';

			$classes = array(
				'simpay-modal',
			);

			if ( 'disabled' !== simpay_get_setting( 'default_plugin_styles', 'enabled' ) ) {
				$classes[] = 'simpay-styled';
			}

			$html .= '<div class="' . esc_attr( implode( ' ', $classes ) ) . '" data-form-id="' . esc_attr( $this->id ) . '">';
			$html .= '<div class="simpay-modal__body">';
			$html .= '<div class="simpay-modal__content">';
			$html .= $heading_html;
			$html .= '<label for="simpay-modal-control-' . esc_attr( $this->id ) . '" class="simpay-modal-control-close">&#x2715;</label>';
		}

		echo $html;
	}

	/**
	 * Outputs additional markup after the payment form.
	 *
	 * @since 3.0.0
	 */
	public function after_form_display() {

		$html = '';

		if ( 'overlay' == $this->get_form_display_type() ) {
			$html .= '</div>';
			$html .= '</div>';
			$html .= '<label for="simpay-modal-control-' . esc_attr( $this->id ) . '" class="simpay-modal-overlay-close" z-index="-1"></label>';
			$html .= '</div>';

			// Show a test mode badge here since the main one is only shown on the custom overlay.
			if ( true === $this->test_mode ) {
				$html .= simpay_get_test_mode_badge();
			}
		}

		echo $html;
	}

	/**
	 * Returns the Payment Button.
	 *
	 * @since 3.0.0
	 *
	 * @param array $fields Custom fields.
	 */
	private function get_payment_button( $fields ) {

		$html = '';

		foreach ( $fields as $k => $v ) {
			switch ( $v['type'] ) {
				case 'payment_button':
					$html .= \SimplePay\Core\Forms\Fields\Payment_Button::html(
						$v,
						'payment-button',
						$this
					);
			}
		}

		return $html;
	}

	/**
	 * Outputs additional HTML for the Pro form.
	 *
	 * @since 3.0.0
	 * @since 4.1.0 These hidden fields are not referenced by the plugin directly
	 *              but remain in the DOM for backwards compatibility.
	 */
	public function pro_html() {
		// Check property first. This allows unit testing of legacy properties.
		$prices = isset( $this->prices )
			? $this->prices
			: simpay_get_payment_form_prices( $this );

		$html = '';

		if ( $this->is_subscription() ) {
			$html .= '<input type="hidden" name="simpay_multi_plan_id" value="" class="simpay-multi-plan-id" />';
			$html .= '<input type="hidden" name="simpay_multi_plan_setup_fee" value="" class="simpay-multi-plan-setup-fee" />';
			$html .= '<input type="hidden" name="simpay_max_charges" value="" class="simpay-max-charges" />';
			$html .= '<input type="hidden" name="simpay_has_custom_plan" class="simpay-has-custom-plan" value="' . ( 'single' === $this->subscription_type ? 'true' : '' ) . '" />';
		}

		$html .= '<input type="hidden" name="simpay_tax_amount" value="" class="simpay-tax-amount" />';

		echo $html;
	}

	/**
	 * Print the subscription options
	 *
	 * @param bool $custom_amount If a custom amount is found and should be printed.
	 * @return string
	 */
	public function print_subscription_options( $custom_amount = false ) {}

	/**
	 * Print a custom amount field.
	 *
	 * @since 3.0.0
	 * @since 3.7.0 Remove $print_wrapper parameter. Always print wrapper.
	 *
	 * @return string
	 */
	public function print_custom_amount() {}

	/**
	 * Print out the custom fields.
	 *
	 * @param string                         $html Custom fields HTML.
	 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
	 * @return string
	 */
	public function get_custom_fields_html( $html, $form ) {
		$fields = get_custom_fields( $form->id );

		foreach ( $fields as $item ) {
			switch ( $item['type'] ) {

				case 'customer_name':
					$html .= Fields\Customer_Name::html( $item, 'customer-name', $form );
					break;

				case 'email':
					$html .= Fields\Email::html( $item, 'email', $form );
					break;

				case 'telephone':
					$html .= Fields\Telephone::html( $item, 'telephone', $form );
					break;

				case 'tax_id':
					$html .= Fields\Tax_ID::html( $item, 'tax_id', $form );
					break;

				case 'card':
					$html .= Fields\Card::html( $item, 'card', $form );
					break;

				case 'address':
					$html .= Fields\Address::html( $item, 'address', $form );
					break;

				case 'checkbox':
					$html .= Fields\Checkbox::html( $item, 'checkbox', $form );
					break;

				case 'coupon':
					$html .= Fields\Coupon::html( $item, 'coupon', $form );
					break;

				case 'date':
					$html .= Fields\Date::html( $item, 'date', $form );
					break;

				case 'dropdown':
					$html .= Fields\Dropdown::html( $item, 'dropdown', $form );
					break;

				case 'number':
					$html .= Fields\Number::html( $item, 'number', $form );
					break;

				case 'radio':
					$html .= Fields\Radio::html( $item, 'radio', $form );
					break;

				case 'custom_amount':
					$html .= Fields\Custom_Amount::html(
						$item,
						'custom_amount',
						$form
					);

					$this->printed_custom_amount = true;
					break;

				case 'plan_select':
					$html .= Fields\Price_Select::html(
						$item,
						'plan_select',
						$form
					);

					$this->printed_subscriptions = true;
					break;

				case 'total_amount':
					$html .= Fields\Total_Amount::html( $item, 'total-amount-labels', $form );
					break;

				case 'text':
					$html .= Fields\Text::html( $item, 'text', $form );
					break;

				case 'hidden':
					$html .= Fields\Hidden::html( $item, 'hidden', $form );
					break;

				case 'recurring_amount_toggle':
					$html .= Fields\Recurring_Amount_Toggle::html( $item, 'recurring-amount-toggle', $form );
					break;

				case 'heading':
					$html .= Fields\Heading::html( $item, 'heading', $form );
					break;

				case 'checkout_button':
					// TODO Need to use set_total like 'total_amount' case?
					$html .= Fields\Checkout_Button::html( $item, 'checkout-button', $form );
					break;

				case 'payment_button':
					if ( 'overlay' !== $this->get_form_display_type() ) {
						$html .= \SimplePay\Core\Forms\Fields\Payment_Button::html(
							$item,
							'payment-button',
							$form
						);
					}
					break;

				case 'payment_request_button':
					$html .= Fields\Payment_Request_Button::html( $item, 'payment-request-button', $form );
					break;

				default:
					$html .= apply_filters( 'simpay_custom_field_html_for_non_native_fields', '', $item, $form );
					break;
			}
		}

		return $html;
	}

	/**
	 * Set the global settings options to the form attributes.
	 *
	 * @since unknown
	 */
	public function pro_set_global_settings() {
		// Tax percentage.
		$tax_percent       = simpay_get_payment_form_tax_percentage(
			$this,
			'exclusive'
		);
		$this->tax_percent = simpay_get_filtered( 'tax_percent', $tax_percent, $this->id );

		// Date format.
		$date_format       = simpay_get_date_format();
		$this->date_format = simpay_get_filtered( 'date_format', $date_format, $this->id );

		// Stripe Elements locale.
		$elements_locale       = simpay_get_filtered(
			'stripe_elements_locale',
			simpay_get_setting( 'stripe_elements_locale', '' ),
			$this->id
		);
		$this->elements_locale = $elements_locale ? $elements_locale : 'auto';
	}

	/**
	 * Set the form settings options to the form attributes.
	 *
	 * @since unknown
	 */
	public function pro_set_post_meta_settings() {
		// Check property first. This allows unit testing of legacy properties.
		$prices = isset( $this->prices )
			? $this->prices
			: simpay_get_payment_form_prices( $this );

		$custom_fields = $this->custom_fields;

		$default_price = simpay_get_payment_form_default_price( $prices );

		$has_subscription = simpay_payment_form_prices_has_subscription_price(
			$prices
		);

		$subscription_price =
			__unstable_simpay_get_payment_form_prices_subscription_price( $prices );

		$has_custom_price = simpay_payment_form_prices_has_custom_price(
			$prices
		);

		$default_price = true === $has_subscription
			? $subscription_price
			: $default_price;

		$custom_price = __unstable_simpay_get_payment_form_custom_price( $prices );

		// Shim a default price because the legacy form expects itself to be able
		// to set all of its properties, always.
		if ( false === $default_price ) {
			$currency     = strtolower( simpay_get_setting( 'currency', 'USD' ) );
			$currency_min = simpay_get_currency_minimum( $currency );

			$default_price = new PriceOption(
				array(
					'unit_amount'     => $currency_min,
					'unit_amount_min' => $currency_min,
					'currency'        => $currency,
					'default'         => true,
				),
				$this,
				wp_generate_uuid4()
			);
		}

		// Shim a few properties that are referenced later without checking existence.
		$this->is_trial                       = false;
		$this->has_subscription_custom_amount = false;
		$this->tax_amount                     = 0;
		$this->subscription_setup_fee         = 0;
		$this->subscription_minimum_amount    = 0;
		$this->subscription_interval          = 0;
		$this->subscription_frequency         = 0;

		//
		// Subscription-related properties.
		//

		// Reset base amount so it's not included in calculations.
		$this->amount = 0;

		// Subscriptions.
		if ( true === $has_subscription ) {
			$this->has_subscription_custom_amount = $has_custom_price;

			$this->is_one_time_custom_amount = simpay_get_filtered(
				'one_time_custom_amount',
				false,
				$this->id
			);

			if ( $this->has_subscription_custom_amount ) {
				$subscription_price          = $custom_price;
				$subscription_default_amount = $subscription_price->unit_amount;
			} else {
				$subscription_price          = $default_price;
				$subscription_default_amount = 0;
			}

			// Legacy properties that get set when they don't need to require
			// these values to be set.
			if ( null === $subscription_price->unit_amount_min ) {
				$subscription_minimum_amount = simpay_get_currency_minimum(
					$subscription_price->currency
				);

				$subscription_price->unit_amount_min =
					$subscription_minimum_amount;
			}

			// Subscription type.
			$subscription_type = count( $prices ) > 1
				? 'user'
				: 'single';

			$this->subscription_type = simpay_get_filtered(
				'subscription_type',
				$subscription_type,
				$this->id
			);

			$this->subscription_amount = simpay_convert_amount_to_dollars(
				$subscription_price->unit_amount
			);

			// Multi-plan list and selected default.
			if ( 'user' === $this->subscription_type ) {
				$this->plans = $this->get_subscription_plans();

				$this->amount = 0;

				$this->default_plan = simpay_get_filtered(
					'default_plan',
					$default_price->id,
					$this->id
				);

				$this->subscription_display_type = simpay_get_filtered(
					'subscription_display_type',
					'radio',
					$this->id
				);
			} else {
				if ( ! $this->has_subscription_custom_amount ) {
					$this->amount = $this->subscription_amount;
				}

				$single_plan_id = $default_price->is_defined_amount()
					? $default_price->id
					: 'empty';

				$this->single_plan = simpay_get_filtered(
					'single_plan',
					$single_plan_id,
					$this->id
				);
			}

			// Subscription "Custom Amount" label.
			$subscription_custom_amount_label = null !== $subscription_price->label
				? $subscription_price->label
				: 'Other amount';

			$this->subscription_custom_amount_label = simpay_get_filtered(
				'subscription_custom_amount_label',
				$subscription_custom_amount_label,
				$this->id
			);

			// Custom Subscription default amount.
			$this->subscription_default_amount = simpay_get_filtered(
				'subscription_default_amount',
				simpay_convert_amount_to_dollars(
					$subscription_default_amount
				),
				$this->id
			);

			// Custom Subscription minimum amount.
			$this->subscription_minimum_amount = simpay_get_filtered(
				'subscription_minimum_amount',
				simpay_convert_amount_to_dollars(
					$subscription_price->unit_amount_min
				),
				$this->id
			);

			$this->minimum_amount = $this->subscription_minimum_amount;

			// Subscription interval count.
			$interval_count = isset( $subscription_price->recurring['interval_count'] )
				? $subscription_price->recurring['interval_count']
				: 1;

			$this->subscription_interval = simpay_get_filtered(
				'subscription_interval',
				$interval_count,
				$this->id
			);

			// Subscription interval frequency (day, month, year).
			$interval = isset( $subscription_price->recurring['interval'] )
				? $subscription_price->recurring['interval']
				: 'month';

			$this->subscription_frequency = simpay_get_filtered(
				'subscription_frequency',
				$interval,
				$this->id
			);

			// Subscription "Initial Setup Fee".
			if ( null !== $subscription_price->line_items ) {
				$subscription_setup_fee = current(
					array_filter(
						$subscription_price->line_items,
						function( $line_item ) {
							return 'initial-setup-fee' === $line_item['id'];
						}
					)
				);
			} else {
				$subscription_setup_fee = '';
			}

			$subscription_setup_fee = ! empty( $subscription_setup_fee )
				? $subscription_setup_fee['unit_amount']
				: 0;

			$this->subscription_setup_fee = simpay_get_filtered(
				'subscription_setup_fee',
				simpay_convert_amount_to_dollars( $subscription_setup_fee ),
				$this->id
			);

			// Subscription "Max Charges" (When "Custom Amount" is enabled).
			$subscription_max_charges = null !== $subscription_price->recurring
				&& isset( $subscription_price->recurring['invoice_limit'] )
				? $subscription_price->recurring['invoice_limit']
				: 0;

			$this->subscription_max_charges = simpay_get_filtered(
				'subscription_max_charges',
				$subscription_max_charges,
				$this->id
			);

			if ( $this->subscription_max_charges > 0 ) {
				$this->has_max_charges = true;
			}

			$this->is_trial = null !== $subscription_price->recurring
				&& isset( $subscription_price->recurring['trial_period_days'] )
				? isset( $subscription_price->recurring['trial_period_days'] )
				: false;

			// Subscription tax amount.
			$this->recurring_tax_amount = simpay_calculate_tax_amount(
				$this->subscription_amount,
				$this->tax_percent
			);

			// Subscription total (amount + tax).
			$this->recurring_total_amount = $this->subscription_amount + $this->recurring_tax_amount;

		} else {

			// Subscription type.
			$this->subscription_type = 'disabled';

			$is_one_time_custom_amount       = false !== $custom_price;
			$this->is_one_time_custom_amount = simpay_get_filtered(
				'one_time_custom_amount',
				$is_one_time_custom_amount,
				$this->id
			);

			if ( $this->is_one_time_custom_amount ) {
				$price = $custom_price;
			} else {
				$price = $default_price;

				// Legacy properties that get set when they don't need to require
				// these values to be set.
				$price->unit_amount_min = simpay_get_currency_minimum(
					$price->currency
				);
			}

			// Amount type (One-time or One-time custom).
			$amount_type = $this->is_one_time_custom_amount
				? 'one_time_custom'
				: 'one_time_set';

			$this->amount_type = simpay_get_filtered(
				'amount_type',
				$amount_type,
				$this->id
			);

			// Default amount.
			$this->default_amount = simpay_get_filtered(
				'_default_amount',
				simpay_convert_amount_to_dollars( $price->unit_amount ),
				$this->id
			);

			// Minimum amount.
			$minimum_amount = null !== $price->unit_amount_min
				? $price->unit_amount_min
				: 0;

			$this->minimum_amount = simpay_get_filtered(
				'minimum_amount',
				simpay_convert_amount_to_dollars( $minimum_amount ),
				$this->id
			);

			$this->custom_amount_label = simpay_get_filtered(
				'custom_amount_label',
				'',
				$this->id
			);

			if ( $this->is_one_time_custom_amount ) {
				if ( $this->default_amount > $this->minimum_amount ) {
					$this->amount = $this->default_amount;
				} else {
					$this->amount = $this->minimum_amount;
				}
			} else {
				$this->amount = simpay_get_filtered(
					'amount',
					$this->default_amount,
					$this->id
				);
			}
		}

		// Recurring Amount Toggle" "Frequency".
		$this->recurring_amount_toggle_frequency = $this->extract_custom_field_setting(
			'recurring_amount_toggle',
			'plan_frequency',
			'month'
		);

		// Recurring Amount Toggle" "Interval".
		$this->recurring_amount_toggle_interval = absint(
			$this->extract_custom_field_setting(
				'recurring_amount_toggle',
				'plan_interval',
				1
			)
		);

		// Recurring Amount Toggle" "Max Charges".
		$this->recurring_amount_toggle_max_charges = $this->extract_custom_field_setting(
			'recurring_amount_toggle',
			'max_charges',
			0
		);

		// Optional fee.
		//
		// Not UI is provided for these, but they can be set via filters.
		$this->fee_percent = floatval( simpay_get_filtered( 'fee_percent', 0, $this->id ) );
		$this->fee_amount  = simpay_unformat_currency(
			simpay_get_filtered( 'fee_amount', 0, $this->id )
		);
	}

	/**
	 * Extract the value from a custom field setting if it exists
	 *
	 * @since unknown
	 * @deprecated 3.6.0
	 *
	 * @param string $field_type Custom Field type.
	 * @param string $setting Custom field setting.
	 * @param string $default Default setting value.
	 * @return mixed
	 */
	public function extract_custom_field_setting( $field_type, $setting, $default = '' ) {
		$custom_fields = $this->custom_fields;

		if ( empty( $custom_fields ) ) {
			return $default;
		}

		foreach ( $custom_fields as $k => $field ) {
			if ( $field_type === $field['type'] ) {
				return isset( $field[ $setting ] ) ? $field[ $setting ] : $default;
			}
		}

		return $default;
	}

	/**
	 * Check if this form has subscriptions enabled or not.
	 *
	 * @since unknown
	 * @deprecated 4.1.0
	 *
	 * @return bool
	 */
	public function is_subscription() {
		// Check property first. This allows unit testing of legacy properties.
		$prices = isset( $this->prices )
			? $this->prices
			: simpay_get_payment_form_prices( $this );

		return simpay_payment_form_prices_has_subscription_price( $prices );
	}

	/**
	 * Place to set our script variables for this form.
	 *
	 * @param array      $arr Script variables.
	 * @param int|string $id Payment Form ID.
	 * @return array
	 */
	public function pro_get_form_script_variables( $arr, $id ) {
		$custom_fields = simpay_get_saved_meta(
			$this->id,
			'_custom_fields',
			array()
		);

		$checkout_text         = __( 'Pay {{amount}}', 'simple-pay' );
		$checkout_trial_text   = __( 'Start Trial', 'simple-pay' );
		$checkout_bnpl_text    = __( 'Continue', 'simple-pay' );
		$checkout_loading_text = __( 'Please Wait...', 'simple-pay' );

		// Checkout Button (Embed + Overlay).
		if ( isset( $custom_fields['checkout_button'] ) && is_array( $custom_fields['checkout_button'] ) ) {
			// There can only be one Checkout Button, but it's saved in an array.
			$checkout_button = current( $custom_fields['checkout_button'] );

			// Base.
			if ( ! empty( $checkout_button['text'] ) ) {
				$checkout_text = $checkout_button['text'];
			}

			// Trial.
			if ( ! empty( $checkout_button['trial_text'] ) ) {
				$checkout_trial_text = $checkout_button['trial_text'];
			}

			// "Continue" text for Buy Now Pay Later
			if ( ! empty( $checkout_button['bnpl_text'] ) ) {
				$checkout_bnpl_text = $checkout_button['bnpl_text'];
			}

			// Processing.
			if ( ! empty( $checkout_button['processing_text'] ) ) {
				$checkout_loading_text = $checkout_button['processing_text'];
			}
		}

		// Determine if Customer fields are being used.
		$has_customer_fields = (
			array_key_exists( 'customer_name', $custom_fields ) ||
			array_key_exists( 'email', $custom_fields ) ||
			array_key_exists( 'telephone', $custom_fields ) ||
			array_key_exists( 'address', $custom_fields ) ||
			array_key_exists( 'coupon', $custom_fields )
		);

		$form_arr = $arr[ $id ]['form'];

		// Payment Request Button configuration.
		$has_prb = isset( $custom_fields['payment_request_button'] );

		if ( $has_prb ) {
			$button    = $custom_fields['payment_request_button'][0];
			$button_id = $button['id'];

			if ( false === strpos( $button_id, 'payment_request_button' ) ) {
				$button_id = 'simpay_' . $id . '_payment_request_button_' . $button_id;
			}

			$prb = array(
				'id'                => simpay_dashify( $button_id ),
				'type'              => isset( $button['button_type'] ) ? $button['button_type'] : 'default',
				'theme'             => isset( $button['button_theme'] )
					? $button['button_theme']
					: 'dark',
				'requestPayerName'  => isset( $custom_fields['customer_name'] ) && isset( $custom_fields['customer_name'][0]['required'] ),
				'requestPayerEmail' => isset( $custom_fields['email'] ),
				'requestPayerPhone' => isset( $custom_fields['telephone'] ),
				// There can technically be two address fields.
				// @link https://github.com/wpsimplepay/WP-Simple-Pay-Pro-3/issues/531
				// @todo Or switch to $this->enable_shipping_address when it returns the correct value (currently incorrect).
				'requestShipping'   => isset( $custom_fields['address'] ) && isset( $custom_fields['address'][0]['collect-shipping'] ),

				/**
				 * Filter shipping options presented in the Payment Request API.
				 *
				 * Note: The `amount` key is not used to calculate the payment total and these options
				 * are only present to satisfy the Stripe API when collecting a shipping address.
				 *
				 * @since 3.4.0
				 *
				 * @param array $shipping_options Shipping options.
				 */
				'shippingOptions'   => apply_filters(
					'simpay_payment_request_button_shipping_options',
					array(
						array(
							'id'     => '0',
							'label'  => _x( 'Default', 'payment request button shipping option label', 'simple-pay' ),
							'amount' => 0,
						),
					)
				),
				'i18n'              => array(
					'planLabel'     => _x( 'Subscription', 'payment request single subscription label', 'simple-pay' ),
					'totalLabel'    => _x( 'Total', 'payment request button total label', 'simple-pay' ),
					/* translators: %s Tax amount. */
					'taxLabel'      => _x( 'Tax: %s%', 'payment request button total label', 'simple-pay' ),
					/* translators: %s Coupon ID. */
					'couponLabel'   => _x( 'Coupon: %s', 'payment request button total label', 'simple-pay' ),
					'setupFeeLabel' => _x( 'Setup Fee', 'payment request button total label', 'simple-pay' ),
				),
			);
		}

		$bools['bools'] = array_merge(
			isset( $form_arr['bools'] ) ? $form_arr['bools'] : array(),
			array(
				'isTestMode'              => simpay_is_test_mode(),
				'isSubscription'          => $this->is_subscription(),
				'isTrial'                 => $this->is_trial,
				'hasCustomerFields'       => $has_customer_fields,
				'hasPaymentRequestButton' => true === $has_prb ? $prb : false,
			)
		);

		$integers['integers'] = array_merge(
			isset( $form_arr['integers'] ) ? $form_arr['integers'] : array(),
			array(
				'setupFee'          => $this->subscription_setup_fee,
				'minAmount'         => $this->minimum_amount,
				'totalAmount'       => $this->total_amount,
				'subMinAmount'      => $this->subscription_minimum_amount,
				'planIntervalCount' => $this->subscription_interval,
				'taxPercent'        => $this->tax_percent,
				'feePercent'        => $this->fee_percent,
				'feeAmount'         => $this->fee_amount,
			)
		);

		$strings['strings'] = array_merge(
			isset( $form_arr['strings'] ) ? $form_arr['strings'] : array(),
			array(
				'companyName'               => $this->company_name,
				'subscriptionType'          => $this->subscription_type,
				'planInterval'              => $this->subscription_frequency,
				'checkoutButtonText'        => esc_html( $checkout_text ),
				'checkoutButtonTrialText'   => esc_html( $checkout_trial_text ),
				'checkoutButtonLoadingText' => esc_html( $checkout_loading_text ),
				'checkoutButtonBnplText'    => esc_html( $checkout_bnpl_text ),
				'dateFormat'                => $this->date_format,
				'formDisplayType'           => $this->get_form_display_type(),
			)
		);

		$i18n['i18n'] = array_merge(
			isset( $form_arr['i18n'] ) ? $form_arr['i18n'] : array(),
			array(
				/* translators: Message displayed on front-end for amount below minimum amount for one-time payment custom amount field. */
				'minCustomAmountError'    => esc_html__(
					'The minimum amount allowed is %s',
					'simple-pay'
				),
				/* translators: Message displayed on front-end for amount below minimum amount for subscription custom amount field. */
				'subMinCustomAmountError' => esc_html__(
					'The minimum amount allowed is %s',
					'simple-pay'
				),
				/* translators: %s Price option minimum amount. */
				'emptyCustomAmountError'  => esc_html__(
					'Please enter a custom amount. The minimum amount allowed is %s',
					'simple-pay'
				),
			)
		);

		// Add Elements locale.
		if ( isset( $arr[ $id ]['stripe'] ) ) {
			$arr[ $id ]['stripe']['strings']['elementsLocale'] = $this->elements_locale;

			$arr[ $id ]['stripe']['strings']['afterpayClearpayLocale'] =
				simpay_get_setting( 'stripe_elements_afterpay_clearpay_locale', 'en-US' );
		}

		$form_arr = array_merge( $form_arr, $integers, $strings, $bools, $i18n );

		// @since 3.8.0 start with a less complex configuration object.
		// @link https://github.com/wpsimplepay/wp-simple-pay-pro/issues/1440
		$payment_methods = array_map(
			function( $payment_method ) {
				return $payment_method->get_data_for_payment_form();
			},
			Payment_Methods\get_form_payment_methods( $this )
		);

		$tax_status = get_post_meta( $id, '_tax_status', true );

		if ( empty( $tax_status ) ) {
			$tax_status = 'fixed-global';
		}

		$config = array(
			'paymentMethods' => array_values( $payment_methods ),
			'taxStatus'      => $tax_status,
			'taxRates'       => simpay_get_payment_form_tax_rates( $this ),
		);

		// Shim Payment Request Payment Method.
		// @todo Register as a true Payment Method.
		if ( $has_prb ) {
			$card = Payment_Methods\get_payment_method( 'card' );

			$config['paymentMethods'][] = new Payment_Methods\Payment_Method(
				array(
					'id'         => 'payment-request',
					'name'       => 'Payment Request',
					'nicename'   => 'Payment Request',
					'flow'       => 'payment-request',
					'countries'  => $card->countries,
					'currencies' => $card->currencies,
				)
			);
		}

		$arr[ $id ]['form'] = array_merge(
			$form_arr,
			array(
				'config' => $config,
			)
		);

		return $arr;
	}

	/**
	 * Retrieve the form display type.
	 *
	 * @since unknown
	 *
	 * @return string
	 */
	public function get_form_display_type() {
		return simpay_get_saved_meta( $this->id, '_form_display_type', 'stripe_checkout' );
	}
}
