<?php
/**
 * Customers: Subscription Management
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Customers\Subscription_Management;

use SimplePay\Core\API;
use SimplePay\Core\Utils;
use SimplePay\Core\Settings;
use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Pro\Forms\Fields;
use SimplePay\Pro\Payment_Methods;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers settings subsections.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Subsections_Collection $subsections Subsections collection.
 */
function register_subsections( $subsections ) {
	if ( false === simpay_subscriptions_enabled() ) {
		return;
	}

	// Customers/Subscription Management.
	$subsections->add(
		new Settings\Subsection(
			array(
				'id'       => 'subscription-management',
				'section'  => 'customers',
				'label'    => esc_html_x(
					'Subscription Management',
					'settings subsection label',
					'simple-pay'
				),
				'priority' => 10,
			)
		)
	);
}
add_action( 'simpay_register_settings_subsections', __NAMESPACE__ . '\\register_subsections' );

/**
 * Registers the settings.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_settings( $settings ) {
	if ( false === simpay_subscriptions_enabled() ) {
		return;
	}

	$configure_portal_url = sprintf(
		'https://dashboard.stripe.com/%ssettings/billing/portal',
		simpay_is_test_mode() ? 'test/' : ''
	);

	// Update Payment Method functionality.
	$settings->add(
		new Settings\Setting_Select(
			array(
				'id'          => 'subscription_management',
				'section'     => 'customers',
				'subsection'  => 'subscription-management',
				'label'       => esc_html_x(
					'Subscription Management',
					'setting label',
					'simple-pay'
				),
				'options'     => array(
					'none'            => esc_html__( 'None', 'simple-pay' ),
					'on-site'         => esc_html__( 'On-site', 'simple-pay' ),
					'customer-portal' => esc_html__( 'Stripe Customer Portal', 'simple-pay' ),
				),
				'value'       => simpay_get_setting( 'subscription_management', 'on-site' ),
				'description' => wpautop(
					esc_html__(
						'Determines how customers can manage their subscription.',
						'simple-pay'
					) . '<br /><br />' .
					'<strong>' . esc_html__( 'None', 'simple-pay' ) . '</strong>: ' .
					esc_html__(
						'Subscription management is controlled by the site owner.',
						'simple-pay'
					) . '<br />' .
					'<strong>' . esc_html__( 'On-site', 'simple-pay' ) . '</strong>: ' .
					esc_html__(
						'Update the subscription\'s payment method via the original purchase form\'s available payment methods.',
						'simple-pay'
					) . '<br />' .
					'<strong>' . esc_html__( 'Stripe Customer Portal', 'simple-pay' ) . '</strong>: ' .
					esc_html__(
						'Manage the subscription through Stripe\'s hosted Customer Portal.',
						'simple-pay'
					) .
					' <a href="' . $configure_portal_url . '" target="_blank" rel="noopener noreferrer" class="simpay-external-link">' .
						esc_html__( 'Configure Customer Portal', 'simple-pay' ) .
						Utils\get_external_link_markup() .
					'</a>'
				),
				'priority'    => 10,
				'schema'      => array(
					'type' => 'string',
					'enum' => array( 'none', 'on-site', 'customer-portal' ),
				),
				'toggles'     => array(
					'value'    => 'on-site',
					'settings' => array(
						'cancel_subscription',
						'cancel_subscription_schedule',
					),
				),
			)
		)
	);

	// Cancel Subscription.
	$settings->add(
		new Settings\Setting_Checkbox(
			array(
				'id'          => 'cancel_subscription',
				'section'     => 'customers',
				'subsection'  => 'subscription-management',
				'label'       => esc_html_x(
					'Cancel Subscriptions',
					'setting label',
					'simple-pay'
				),
				'input_label' => esc_html__(
					'Allow customers to cancel subscriptions',
					'simple-pay'
				),
				'value'       => simpay_get_setting( 'cancel_subscription', 'yes' ),
				'priority'    => 20,
				'schema'      => array(
					'type' => 'string',
					'enum' => array( 'yes', 'no' ),
				),
				'toggles'     => array(
					'value'    => 'yes',
					'settings' => array(
						'cancel_subscription_schedule',
					),
				),
			)
		)
	);

	// Cancel schedule.
	$settings->add(
		new Settings\Setting_Radio(
			array(
				'id'          => 'cancel_subscription_schedule',
				'section'     => 'customers',
				'subsection'  => 'subscription-management',
				'label'       => '&nbsp;',
				'options'     => array(
					'immediately'   => esc_html_x(
						'Cancel immediately',
						'setting label',
						'simple-pay'
					),
					'at_period_end' => esc_html_x(
						'Cancel at end of billing period',
						'setting label',
						'simple-pay'
					),
				),
				'value'       => simpay_get_setting(
					'cancel_subscription_schedule',
					'at_period_end'
				),
				'description' => wpautop(
					esc_html__(
						'Subscriptions can be reactivated until cancelled.',
						'simple-pay'
					)
				),
				'priority'    => 30,
				'schema'      => array(
					'type' => 'string',
				),
			)
		)
	);
}
add_action( 'simpay_register_settings', __NAMESPACE__ . '\\register_settings' );

/**
 * Returns a message/summary about the current Subscription/Payment Method.
 *
 * @since 4.0.0
 *
 * @param array $payment_confirmation_data {
 *  Contextual information about this payment confirmation.
 *
 *   @type \SimplePay\Vendor\Stripe\Customer               $customer Stripe Customer
 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
 *   @type object                         $subscriptions Subscriptions associated with the Customer.
 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
 * }
 * @return string
 */
function summary( $payment_confirmation_data ) {
	try {
		$form              = $payment_confirmation_data['form'];
		$subscription      = current( $payment_confirmation_data['subscriptions'] );
		$customer          = $payment_confirmation_data['customer'];
		$payment_method_id = $customer->invoice_settings->default_payment_method;

		// If the Customer has no Payment Method, try the Subscription.
		if ( empty( $payment_method_id ) ) {
			$payment_method_id = $subscription->default_payment_method;
		}

		// If the Subscription has no Payment Method, try the default Source.
		if ( empty( $payment_method_id ) ) {
			$payment_method_id = $customer->default_source;
		}

		// Find Payment Method.

		// Payment Method.
		if ( false === strpos( $payment_method_id, 'ba_' ) ) {
			$payment_method = API\PaymentMethods\retrieve(
				$payment_method_id,
				$form->get_api_request_args()
			);

			// Bank Account.
		} else {
			$stripe         = new \SimplePay\Vendor\Stripe\StripeClient( $form->get_api_request_args() );
			$payment_method = $stripe->customers->retrieveSource(
				$customer->id,
				$payment_method_id
			);
		}

		// Retrieve the Upcoming Invoice.
		try {
			$upcoming_invoice = Stripe_API::request(
				'Invoice',
				'upcoming',
				array(
					'customer' => $customer->id,
				),
				$form->get_api_request_args()
			);
		} catch ( \Exception $e ) {
			$upcoming_invoice = false;
		}

		/**
		 * Filters the content to be shown before the "Update Payment Method" form.
		 *
		 * @since 3.9.0
		 *
		 * @param string                               $message          Update Payment Method message.
		 * @param \SimplePay\Vendor\Stripe\PaymentMethod|\SimplePay\Vendor\Stripe\Source $payment_method   Payment Method or Source.
		 * @param \SimplePay\Vendor\Stripe\Subscription                 $subscription     Subscription.
		 * @param \SimplePay\Vendor\Stripe\Invoice                      $upcoming_invoice Upcoming invoice.
		 */
		$update_payment_method_message = apply_filters(
			'simpay_update_payment_method_message',
			'',
			$payment_method,
			$subscription,
			$upcoming_invoice
		);

		$subscription_management = simpay_get_setting( 'subscription_management', 'on-site' );

		// Add Cancel link if managing on site.
		if ( 'on-site' === $subscription_management ) {
			$can_cancel = simpay_get_setting( 'cancel_subscription', 'yes' );

			if (
				'yes' === $can_cancel &&
				( 'canceled' !== $subscription->status && ! $subscription->cancel_at )
			) {
				$subscription_key = $subscription->metadata->simpay_subscription_key;

				$cancel_link = add_query_arg(
					array(
						'customer_id'      => $subscription->customer,
						'subscription_id'  => $subscription->id,
						'subscription_key' => $subscription_key,
						'form_id'          => $form->id,
						'cancel'           => true,
						'nonce'            => wp_create_nonce(
							'simpay-cancel-subscription-' . $subscription_key
						),
					),
					$form->payment_success_page
				);

				$update_payment_method_message .= (
					' <a href="' . esc_url( $cancel_link ) . '">' .
					esc_html__( 'Cancel Subscription', 'simple-pay' ) .
					'</a>'
				);
			}
		}

		return wpautop( $update_payment_method_message ); // WPCS: XSS okay.
	} catch ( \Exception $e ) {
		return $e->getMessage();
	}
}

/**
 * Cancels a Stripe Subscription using a link.
 *
 * @since 4.0.0
 */
function on_cancel_link() {
	$cancel = isset( $_GET['cancel'] );

	$subscription_key = isset( $_GET['subscription_key'] )
		? sanitize_text_field( $_GET['subscription_key'] )
		: false;

	$subscription_id = isset( $_GET['subscription_id'] )
		? sanitize_text_field( $_GET['subscription_id'] )
		: false;

	$form_id = isset( $_GET['form_id'] )
		? sanitize_text_field( $_GET['form_id'] )
		: false;

	$nonce = isset( $_GET['nonce'] )
		? sanitize_text_field( $_GET['nonce'] )
		: false;

	// Do nothing if require information is not present.
	if (
		false === $cancel ||
		false === $subscription_key ||
		false === $subscription_id ||
		false == $form_id ||
		false == $nonce
	) {
		return;
	}

	// Do nothing if nonce cannot be verified.
	if ( ! wp_verify_nonce( $nonce, 'simpay-cancel-subscription-' . $subscription_key ) ) {
		return;
	}

	try {
		$form = simpay_get_form( $form_id );

		if ( false === $form ) {
			throw new \Exception(
				__( 'Unable to locate payment form.', 'simple-pay' )
			);
		}

		// Retrieve the Subscription from Stripe.
		$subscription = API\Subscriptions\retrieve( $subscription_id );

		// Do nothing if the stored key does not match the URL.
		if ( $subscription_key !== $subscription->metadata->simpay_subscription_key ) {
			return;
		}

		$schedule = simpay_get_setting( 'cancel_subscription_schedule', 'at_period_end' );

		$subscription = API\Subscriptions\cancel(
			$subscription_id,
			$schedule,
			$form->get_api_request_args()
		);

		wp_redirect(
			add_query_arg(
				array(
					'customer_id'      => $subscription->customer,
					'subscription_key' => $subscription_key,
					'form_id'          => $form->id,
				),
				$form->payment_success_page
			)
		);

		exit;
	} catch ( \Exception $e ) {
		wp_die( Utils\handle_exception_message( $e ) );
	}
}
add_action( 'template_redirect', __NAMESPACE__ . '\\on_cancel_link' );

/**
 * Appends the on-site "Update Payment Method" form to the confirmation content.
 *
 * @since 4.0.0
 *
 * @param string $content Payment confirmation shortcode content.
 * @param array  $payment_confirmation_data Array of data to send to the Payment Confirmation smart tags.
 * @throws \Exception If data cannot be found, or is invalid.
 */
function on_site( $content, $payment_confirmation_data ) {
	// No Subscription key, do nothing.
	if ( ! isset( $_GET['subscription_key'] ) ) {
		return $content;
	}

	$subscription_management = simpay_get_setting( 'subscription_management', 'on-site' );

	// Different update method, do nothing.
	if ( 'on-site' !== $subscription_management ) {
		return $content;
	}

	// No Subscriptions found, do nothing.
	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $content;
	}

	$subscription     = current( $payment_confirmation_data['subscriptions'] );
	$subscription_key = esc_attr( $_GET['subscription_key'] );

	// Subscription is completely cancelled, do nothing.
	if ( 'canceled' === $subscription->status ) {
		return $content;
	}

	$form = $payment_confirmation_data['form'];

	try {
		$customer = $payment_confirmation_data['customer'];

		// Match stored meta key to passed subscription key.
		if ( $subscription_key !== $subscription->metadata->simpay_subscription_key ) {
			throw new \Exception( esc_html__( 'Unable to match Customer records to allow payment method updates.', 'simple-pay' ) );
		}

		if ( false === $subscription->cancel_at_period_end ) {
			$button = __( 'Update Payment Method', 'simple-pay' );
		} else {
			$button = __( 'Reactivate Subscription', 'simple-pay' );
		}

		wp_enqueue_script(
			'simpay-update-payment-method',
			SIMPLE_PAY_INC_URL . 'pro/assets/js/simpay-public-pro-update-payment-method.min.js',
			array(
				'jquery',
				'simpay-shared',
				'simpay-public',
				'sandhills-stripe-js-v3',
			),
			SIMPLE_PAY_VERSION
		);

		add_filter( 'nonce_life', 'simpay_nonce_life_2_hour' );

		$customer_nonce = wp_create_nonce(
			'simpay_payment_form_customer_' . $customer->id
		);

		remove_filter( 'nonce_life', 'simpay_nonce_life_2_hour' );

		add_filter( 'nonce_life', 'simpay_nonce_life_2_min' );

		$customer_setupintent_nonce = wp_create_nonce(
			'simpay_payment_form_customer_' . $customer->id
		);

		remove_filter( 'nonce_life', 'simpay_nonce_life_2_min' );

		wp_localize_script(
			'simpay-update-payment-method',
			'simpayUpdatePaymentMethod',
			array(
				'id'                         => $form->id,
				'customer_id'                => $customer->id,
				'customer_nonce'             => $customer_nonce,
				'customer_setupintent_nonce' => $customer_setupintent_nonce,
				'subscription_id'            => $subscription->id,
				'subscription_key'           => $subscription_key,
				'nonce'                      => wp_create_nonce( 'simpay_payment_form' ),
				'i18n'                       => array(
					'submit'  => esc_html( $button ),
					'loading' => esc_html__( 'Please Wait&hellip;', 'simple-pay' ),
				),
			)
		);

		$classes = array(
			'simpay-update-payment-method',
			'simpay-checkout-form simpay-checkout-form--embedded',
		);

		if ( 'disabled' !== simpay_get_setting( 'default_plugin_styles', 'enabled' ) ) {
			$classes[] = 'simpay-styled';
		}

		// Filter out Payment Methods on that do not support the Subscription's currency.
		// @todo This is hacky here.
		add_filter(
			'simpay_form_' . $form->id . '_payment_methods',
			function( $payment_methods ) use ( $form, $subscription ) {
				$form_type = 'stripe_checkout' === $form->get_display_type()
					? 'stripe-checkout'
					: 'stripe-elements';

				$payment_methods = $payment_methods[ $form_type ];
				$payment_methods = array_filter(
					$payment_methods,
					function( $payment_method ) use ( $subscription ) {
						if ( ! isset( $payment_method['id'] ) ) {
							return false;
						}

						$payment_method = Payment_Methods\get_payment_method(
							$payment_method['id']
						);

						if ( false === $payment_method->recurring ) {
							return false;
						}

						return in_array(
							$subscription->latest_invoice->currency,
							$payment_method->currencies,
							true
						);
					}
				);

				return array(
					$form_type => $payment_methods
				);
			}
		);

		ob_start();
		?>

<form
	action=""
	method="POST"
	id="simpay-form-update-payment-method"
>

	<h3><?php esc_html_e( 'Manage Subscription', 'simple-pay' ); ?></h3>

	<?php
	echo wpautop(
		summary( $payment_confirmation_data )
	); // WPCS: XSS okay.
	?>

	<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
		<?php
		$field = array(
			'type'  => 'card',
			'uid'   => 1,
			'id'    => 'update-payment-method',
			'label' => '',
		);

		echo Fields\Card::html( $field, 'card', $form ); // WPCS: XSS okay.
		?>

		<div class="simpay-form-control simpay-checkout-btn-container">
			<button class="simpay-btn simpay-checkout-btn" type="submit"><span>
				<?php echo esc_html( $button ); ?>
			</span></button>
		</div>

		<div id="simpay-card-element-update-payment-method-errors" class="simpay-errors"></div>

		<?php
		if ( false === $payment_confirmation_data['form']->is_livemode() ) :
			echo simpay_get_test_mode_badge();
		endif;
		?>

		<input
			type="hidden"
			class="simpay-customer-name"
			value="<?php echo esc_attr( $customer->name ); ?>"
		/>

		<input
			type="hidden"
			class="simpay-email"
			value="<?php echo esc_attr( $customer->email ); ?>"
		/>

		<?php wp_nonce_field( 'simpay_payment_form' ); ?>
	</div>

</form>

		<?php
		$content .= trim( ob_get_clean() );
	} catch ( \Exception $e ) {
		if ( current_user_can( 'manage_options' ) ) {
			$content .= Utils\handle_exception_message( $e );
		}
	}

	return $content;
}
add_filter( 'simpay_after_payment_details', __NAMESPACE__ . '\\on_site', 20, 2 );

/**
 * Appends the "Update Subcription" Customer Portal link to the confirmation content.
 *
 * @since 4.0.0
 *
 * @param string $content Payment confirmation shortcode content.
 * @param array  $payment_confirmation_data Array of data to send to the Payment Confirmation smart tags.
 * @throws \Exception If data cannot be found, or is invalid.
 */
function customer_portal( $content, $payment_confirmation_data ) {
	// No Subscription key, do nothing.
	if ( ! isset( $_GET['subscription_key'] ) ) {
		return $content;
	}

	$subscription_management = simpay_get_setting( 'subscription_management', 'on-site' );

	// Different update method, do nothing.
	if ( 'customer-portal' !== $subscription_management ) {
		return $content;
	}

	// No Subscriptions found, do nothing.
	if ( empty( $payment_confirmation_data['subscriptions'] ) ) {
		return $content;
	}

	$subscription     = current( $payment_confirmation_data['subscriptions'] );
	$subscription_key = esc_attr( $_GET['subscription_key'] );

	try {
		// Match stored meta key to passed subscription key.
		if ( $subscription_key !== $subscription->metadata->simpay_subscription_key ) {
			throw new \Exception( esc_html__( 'Unable to match Customer records to allow payment method updates.', 'simple-pay' ) );
		}

		$form        = $payment_confirmation_data['form'];
		$customer_id = $payment_confirmation_data['customer']->id;

		$scheme     = is_ssl() ? 'https://' : 'http://';
		$return_url = "{$scheme}{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

		$session = Stripe_API::request(
			'BillingPortal\Session',
			'create',
			array(
				'customer'   => $customer_id,
				'return_url' => $return_url,
			),
			$form->get_api_request_args()
		);

		$classes = array(
			'simpay-update-payment-method',
		);

		if ( 'disabled' !== simpay_get_setting( 'default_plugin_styles', 'enabled' ) ) {
			$classes[] = 'simpay-styled';
		}

		$content .= sprintf(
			'
			<div id="simpay-form-update-payment-method">
				<h3>' . esc_html__( 'Manage Subscription', 'simple-pay' ) . '</h3>
				%2$s
				<div class="%1$s">
					<div class="simpay-form-control simpay-checkout-btn-container simpay-styled">
						<a href="%3$s" class="simpay-btn stripe-button-el" type="submit" target="_blank" rel="noopener noreferrer">
							<span>' . esc_html__( 'Update Subscription', 'simple-pay' ) . '</span>
						</a>
					</div>
				</div>
			</div>',
			esc_attr( implode( ' ', $classes ) ),
			wpautop(
				summary( $payment_confirmation_data )
			),
			esc_url( $session->url )
		);
	} catch ( \Exception $e ) {
		if ( current_user_can( 'manage_options' ) ) {
			$content .= Utils\handle_exception_message( $e );
		}
	}

	return $content;
}
add_filter( 'simpay_after_payment_details', __NAMESPACE__ . '\\customer_portal', 20, 2 );
