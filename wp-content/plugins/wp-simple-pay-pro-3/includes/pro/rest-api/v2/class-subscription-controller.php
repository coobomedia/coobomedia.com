<?php
/**
 * REST API: Subscription Controller
 *
 * @package SimplePay\Core\REST_API\v2
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.6.0
 */

namespace SimplePay\Pro\REST_API\v2;

use SimplePay\Core\API;
use SimplePay\Core\REST_API\Controller;
use SimplePay\Pro\Payments as Pro_Payments;
use SimplePay\Core\Payments as Core_Payments;
use SimplePay\Core\Legacy;
use SimplePay\Core\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Subscription_Controller class.
 *
 * @since 3.6.0
 */
class Subscription_Controller extends Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wpsp/v2';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'subscription';

	/**
	 * Register the routes for Checkout Session.
	 *
	 * @since 3.6.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/payment_method/(?P<subscription_id>[sub_[0-9a-z]+)/(?P<customer_id>[cus_[0-9a-z]+)',
			array(
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item_payment_method' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( false ),
				),
			)
		);
	}

	/**
	 * Allows requests originating from a payment form.
	 *
	 * @since 3.6.0
	 *
	 * @param \WP_REST_Request $request Incoming REST API request data.
	 * @return \WP_Error|true Error if a permission check fails.
	 */
	public function create_item_permissions_check( $request ) {
		$checks = array(
			'rate_limit',
			'form_nonce',
			'required_fields',
			'customer_nonce',
		);

		return $this->permission_checks( $checks, $request );
	}

	/**
	 * Handle an incoming request to create a Checkout Session.
	 *
	 * @since 3.6.0
	 *
	 * @param \WP_REST_Request $request {
	 *   Incoming REST API request data.
	 *
	 *   @type int   $customer_id Customer ID previously generated with Payment Source.
	 *   @type int   $form_id Form ID used to generate PaymentIntent data.
	 *   @type array $form_data Client-generated formData information.
	 *   @type array $form_values Values of named fields in the payment form.
	 * }
	 * @throws \Exception When required data is missing or cannot be verified.
	 * @return \WP_REST_Response
	 */
	public function create_item( $request ) {
		try {
			// Locate form.
			if ( ! isset( $request['form_id'] ) ) {
				throw new \Exception(
					__( 'Unable to locate payment form.', 'simple-pay' )
				);
			}

			// Gather customer information.
			$customer_id = isset( $request['customer_id'] ) ? $request['customer_id'] : false;

			if ( ! $customer_id ) {
				throw new \Exception( __( 'A customer must be provided.', 'simple-pay' ) );
			}

			// Payment Method ID.
			$payment_method_id = isset( $request['payment_method_id'] )
				? sanitize_text_field( $request['payment_method_id'] )
				: null;

			// Payment Method type.
			$payment_method_type = isset( $request['payment_method_type'] )
				? sanitize_text_field( $request['payment_method_type'] )
				: false;

			// Gather <form> information.
			$form_id     = $request['form_id'];
			$form_data   = json_decode( $request['form_data'], true );
			$form_values = $request['form_values'];

			$form = simpay_get_form( $form_id );

			if ( false === $form ) {
				throw new \Exception(
					__( 'Unable to locate payment form.', 'simple-pay' )
				);
			}

			// Block access if not in schedule.
			if ( false === $form->has_available_schedule() ) {
				throw new \Exception(
					esc_html__( 'Invalid request. Please try again.', 'simple-pay' )
				);
			}

			// Block acccess if form type is incorrect.
			if ( 'stripe_checkout' === $form->get_display_type() ) {
				throw new \Exception(
					esc_html__( 'Invalid request. Please try again.', 'simple-pay' )
				);
			}

			$subscription_args = array_merge(
				Pro_Payments\Subscription\get_args_from_payment_form_request(
					$form,
					$form_data,
					array_merge(
						array(
							'payment_method_type' => $payment_method_type,
							'payment_method_id'   => $payment_method_id,
						),
						$form_values
					),
					$customer_id
				),
				array(
					'customer'          => $customer_id,
					'expand'            => array(
						'latest_invoice.payment_intent',
						'customer',
						'pending_setup_intent',
					),
					'add_invoice_items' => Pro_Payments\Subscription\get_setup_fee_from_payment_form_request(
						$form,
						$form_data,
						$form_values,
						$customer_id
					),
				)
			);

			// Handle legacy form processing.
			Legacy\Hooks\simpay_process_form( $form, $form_data, $form_values, $customer_id );

			/**
			 * Allow further processing before a Subscription is created from a posted form.
			 *
			 * @since 3.6.0
			 *
			 * @param array                         $subscription_args Arguments used to create a PaymentIntent.
			 * @param SimplePay\Core\Abstracts\Form $form Form instance.
			 * @param array                         $form_data Form data generated by the client.
			 * @param array                         $form_values Values of named fields in the payment form.
			 * @param int                           $customer_id Stripe Customer ID.
			 */
			do_action(
				'simpay_before_subscription_from_payment_form_request',
				$subscription_args,
				$form,
				$form_data,
				$form_values,
				$customer_id
			);

			$subscription = API\Subscriptions\create(
				$subscription_args,
				$form->get_api_request_args()
			);

			/**
			 * Allow further processing after a Subscription is created from a posted form.
			 *
			 * @since 3.6.0
			 *
			 * @param \SimplePay\Vendor\Stripe\Subscription          $subscription Stripe Subscription.
			 * @param SimplePay\Core\Abstracts\Form $form Form instance.
			 * @param array                         $form_data Form data generated by the client.
			 * @param array                         $form_values Values of named fields in the payment form.
			 * @param int                           $customer_id Stripe Customer ID.
			 */
			do_action(
				'simpay_after_subscription_from_payment_form_request',
				$subscription,
				$form,
				$form_data,
				$form_values,
				$customer_id
			);

			return $subscription;
		} catch ( \Exception $e ) {
			return new \WP_REST_Response(
				array(
					'message' => Utils\handle_exception_message( $e ),
				),
				400
			);
		}
	}

	/**
	 * Allows requests originating from a payment method update form.
	 *
	 * @since 3.7.0
	 *
	 * @param \WP_REST_Request $request {
	 *   Incoming REST API request data.
	 *
	 *   @type array $form_values Values of named fields in the payment form.
	 * }
	 * @return bool
	 */
	public function update_item_permissions_check( $request ) {
		$checks = array(
			'rate_limit',
			'form_nonce',
		);

		$checks = $this->permission_checks( $checks, $request );

		if ( is_wp_error( $checks ) ) {
			return $checks;
		}

		// Ensure a Customer ID is available.
		$customer_id = isset( $request['customer_id'] )
			? $request['customer_id']
			: false;

		if ( false === $customer_id ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'Invalid customer record. Please try again.', 'simple-pay' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		$customer_nonce = isset( $request['customer_nonce'] )
			? $request['customer_nonce']
			: false;

		if ( false === $customer_nonce ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'Missing customer token. Please try again.', 'simple-pay' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		// Validate the nonce based on the Customer ID.
		add_filter( 'nonce_life', 'simpay_nonce_life_2_hour' );

		$customer_nonce_action = sprintf(
			'simpay_payment_form_customer_%s',
			$customer_id
		);

		$valid_nonce = wp_verify_nonce(
			$customer_nonce,
			$customer_nonce_action
		);

		remove_filter( 'nonce_life', 'simpay_nonce_life_2_hour' );

		if ( false === $valid_nonce ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'Invalid customer token. Please refresh the page and try again.', 'simple-pay' ),
				array(
					'status' => rest_authorization_required_code(),
				)
			);
		}

		return true;
	}

	/**
	 * Handles an incoming request to update a Subscription's payment method.
	 *
	 * @since 3.7.0
	 *
	 * @param \WP_REST_Request $request {
	 *   Incoming REST API request data.
	 *
	 *   @type string $source_id Source ID.
	 *   @type string $customer_id Customer ID.
	 * }
	 * @throws \Exception When required data is missing or cannot be verified.
	 * @return \WP_REST_Response
	 */
	public function update_item_payment_method( $request ) {
		$subscription_id = $request['subscription_id'];
		$customer_id     = $request['customer_id'];

		try {
			$form_id = isset( $request['form_id'] ) ? $request['form_id'] : 0;
			$form    = simpay_get_form( $form_id );

			if ( false === $form ) {
				throw new \Exception(
					__( 'Unable to find Payment Form.', 'simple-pay' )
				);
			}

			$form_values = $request['form_values'];

			$subscription_key = isset( $form_values['subscription_key'] )
				? $form_values['subscription_key']
				: null;

			if ( null === $subscription_key ) {
				throw new \Exception(
					__( 'Unable to find Subscription key.', 'simple-pay' )
				);
			}

			// Gather Payment Method information.
			$payment_method_id = isset( $form_values['payment_method_id'] )
				? $form_values['payment_method_id']
				: null;

			$subscription = API\Subscriptions\retrieve(
				array(
					'id'     => $subscription_id,
					'expand' => array(
						'customer',
					),
				)
			);

			// Confirm the actual Subscription's Customer matches the request.
			if ( $subscription->customer->id !== $customer_id ) {
				throw new \Exception( __( 'Subscription Customer does not match.', 'simple-pay' ) );
			}

			// Confirm the passed Subscription Key matches the actual Subscription record metadata.
			if (
				! isset( $subscription->metadata->simpay_subscription_key ) ||
				$subscription_key !== $subscription->metadata->simpay_subscription_key
			) {
				throw new \Exception( __( 'Invalid Subscription key.', 'simple-pay' ) );
			}

			$customer_args = array();

			$subscription_args = array(
				// Always reactivate if deactivated.
				'cancel_at_period_end' => false,
			);

			// Attach a PaymentMethod to the Customer.
			if ( false === strpos( $payment_method_id, 'btok_' ) ) {
				$payment_method = API\PaymentMethods\__experimental_attach(
					$payment_method_id,
					$customer_id,
					$form->get_api_request_args()
				);

				$customer_args['invoice_settings']['default_payment_method'] =
					$payment_method->id;

				$subscription_args['default_payment_method'] = $payment_method->id;
				$subscription_args['default_source']         = '';

				// Attach a Source to the Customer.
			} else {
				// Remove previous Bank Accounts to ensure duplicates can be
				// added back.
				$customer = $subscription->customer;
				$sources  = $customer::allSources(
					$customer_id,
					array(
						'object' => 'bank_account',
					),
					$form->get_api_request_args()
				);

				if ( ! empty( $sources->data ) ) {
					foreach ( $sources as $source ) {
						$customer::deleteSource(
							$subscription->customer->id,
							$source->id,
							array(),
							$form->get_api_request_args()
						);
					}
				}

				$source = Core_Payments\Stripe_API::request(
					'Customer',
					'createSource',
					$customer_id,
					array(
						'source' => $payment_method_id,
					),
					$form->get_api_request_args()
				);

				$customer_args['default_source']                             = $source->id;
				$customer_args['invoice_settings']['default_payment_method'] = '';

				$subscription_args['default_source']         = $source->id;
				$subscription_args['default_payment_method'] = '';
			}

			// Update the Customer.
			$customer = API\Customers\update(
				$customer_id,
				$customer_args,
				$form->get_api_request_args()
			);

			// Update Subscription.
			$subscription = API\Subscriptions\update(
				$subscription_id,
				$subscription_args,
				$form->get_api_request_args()
			);

			return array(
				'customer'     => $customer,
				'subscription' => $subscription,
			);
		} catch ( \Exception $e ) {
			return new \WP_REST_Response(
				array(
					'message' => Utils\handle_exception_message( $e ),
				),
				400
			);
		}
	}
}
