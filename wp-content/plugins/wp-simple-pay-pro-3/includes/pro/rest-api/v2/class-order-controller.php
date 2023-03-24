<?php
/**
 * REST API: "Order" Controller
 *
 * "Order" refers to an internal (temporary) WP Simple Pay order, not a Stripe Order object.
 *
 * @package SimplePay\Pro\REST_API\v2
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.0
 */

namespace SimplePay\Pro\REST_API\v2;

use Exception;
use SimplePay\Core\API;
use SimplePay\Core\i18n;
use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Core\Payments\PaymentIntent;
use SimplePay\Core\REST_API\Controller;
use SimplePay\Core\Utils;
use WP_REST_Response;
use WP_REST_Server;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Order_Controller.
 *
 * @since 4.6.0
 */
class Order_Controller extends Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 * @since 4.6.0
	 */
	protected $namespace = 'wpsp/v2';

	/**
	 * Route base.
	 *
	 * @var string
	 * @since 4.6.0
	 */
	protected $rest_base = 'order';

	/**
	 * Registers the REST API routes.
	 *
	 * @since 4.6.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/preview',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'preview_item' ),
					'permission_callback' => array( $this, 'preview_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema(
						WP_REST_Server::CREATABLE
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/submit',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'submit_item' ),
					'permission_callback' => array( $this, 'submit_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema(
						WP_REST_Server::CREATABLE
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Determines access to POST /wpsp/v2/order/preview.
	 *
	 * This endpoint is pretty much always accessible. We need to fetch previews
	 * when the address changes (fully, or the country) to update the tax amount.
	 *
	 * Previewing a recurring price generates fake future Invoices, which cannot
	 * be directly processeed. Previewing a one-time price creates a Stripe Order,
	 * which then must be submitted before the payment can be confirmed. The
	 * submit endpoint is rate limited and payment confirmation must be done by
	 * the client.
	 *
	 * It will still run reCAPTCHA checks if enabled.
	 *
	 * @since 4.6.0
	 *
	 * @param \WP_REST_Request $request Incoming REST API request data.
	 * @return \WP_Error|true Error if a permission check fails.
	 */
	public function preview_item_permissions_check( $request ) {
		$checks = array(
			'form_nonce',
		);

		return $this->permission_checks( $checks, $request );
	}

	/**
	 * Handles POST /wpsp/v2/order/preview
	 *
	 * @since 4.6.0
	 *
	 * @param \WP_REST_Request $request Incoming REST API request data.
	 * @throws \Exception When automatic tax is not enabled.
	 * @throws \Exception When incorrect payment form type is sent.
	 * @throws \Exception When required data is missing or cannot be verified.
	 * @return \WP_REST_Response
	 */
	public function preview_item( $request ) {
		try {
			$form_id = sanitize_text_field( $request['form_id'] );
			$form    = simpay_get_form( $form_id );

			if ( false === $form ) {
				throw new Exception(
					esc_html__(
						'Invalid request. Please try again.',
						'simple-pay'
					)
				);
			}

			// Block access if not in schedule.
			if ( false === $form->has_available_schedule() ) {
				throw new Exception(
					esc_html__(
						'Invalid request. Please try again.',
						'simple-pay'
					)
				);
			}

			// Block acccess if form type is incorrect.
			if ( 'stripe_checkout' === $form->get_display_type() ) {
				throw new Exception(
					esc_html__(
						'Invalid request. Please try again.',
						'simple-pay'
					)
				);
			}

			$tax_status = get_post_meta( $form_id, '_tax_status', true );

			if ( 'automatic' !== $tax_status ) {
				throw new Exception(
					esc_html__(
						'Invalid request. Please try again.',
						'simple-pay'
					)
				);
			}

			/**
			 * Allows processing before an order is previewed.
			 *
			 * @since 4.6.0
			 *
			 * @param \WP_REST_Request              $request Incoming REST API request data.
			 * @param SimplePay\Core\Abstracts\Form $form Form instance.
			 */
			do_action(
				'simpay_before_order_preview_from_payment_form_request',
				$request,
				$form
			);

			$is_recurring   = 'true' === sanitize_text_field(
				$request['__unstable_is_recurring']
			);
			$address_fields = $this->get_address_field_data(
				$form,
				$this->get_address_details( $request )
			);

			if ( true === $is_recurring ) {
				$today_preview = $this->preview_recurring( $request, $form, false );
				$next_preview  = $this->preview_recurring( $request, $form, true );

				$automatic_tax_status = $today_preview->automatic_tax->status;

				$today_tax_amount = array_reduce(
					$today_preview->total_tax_amounts,
					array( $this, 'sum_amount' ),
					0
				);

				$today_discount_amount = array_reduce(
					$today_preview->total_discount_amounts,
					array( $this, 'sum_amount' ),
					0
				);

				$next_tax_amount = array_reduce(
					$next_preview->total_tax_amounts,
					array( $this, 'sum_amount' ),
					0
				);

				$next_discount_amount = array_reduce(
					$next_preview->total_discount_amounts,
					array( $this, 'sum_amount' ),
					0
				);

				return new WP_REST_Response(
					array(
						'order'          => array(
							// Fake subscription ID. Not used when completing
							// the order, but is referenced by the client.
							'id'               => $today_preview->subscription,
							'client_secret'    => $today_preview->client_secret,
							'billing_details'  => array(
								'address' => $today_preview->customer_address,
							),
							'shipping_details' => $today_preview->customer_shipping,
							'automatic_tax'    => array(
								'enabled' => true,
								'status'  => $automatic_tax_status,
							),
							'tax'              => array(
								'behavior' => $this->get_tax_behavior( $form ),
							),
							'total_details'    => array(
								'amount_discount' => $today_discount_amount,
								'amount_shipping' => 0,
								'amount_tax'      => $today_tax_amount,
							),
							'upcoming_invoice' => array(
								'total_details' => array(
									'amount_discount' => $next_discount_amount,
									'amount_shipping' => 0,
									'amount_tax'      => $next_tax_amount,
								),
							),
						),
						'address_fields' => $address_fields,
					)
				);
			} else {
				$preview = $this->preview_one_time( $request, $form );

				return new WP_REST_Response(
					array(
						'order'          => array(
							'id'               => $preview->id,
							'client_secret'    => $preview->client_secret,
							'billing_details'  => $preview->billing_details,
							'shipping_details' => $preview->shipping_details,
							'automatic_tax'    => array(
								'enabled' => true,
								'status'  => $preview->automatic_tax->status,
							),
							'tax'              => array(
								'behavior' => $this->get_tax_behavior( $form ),
							),
							'total_details'    => array(
								'amount_discount' => $preview->total_details->amount_discount,
								'amount_shipping' => 0,
								'amount_tax'      => $preview->total_details->amount_tax,
							),
						),
						'address_fields' => $address_fields,
					)
				);
			}
		} catch ( Exception $e ) {
			return new WP_REST_Response(
				array(
					'message' => Utils\handle_exception_message( $e ),
				),
				400
			);
		}
	}

	/**
	 * Determines access to POST /wpsp/v2/order/submit.
	 *
	 * @since 4.6.0
	 *
	 * @param \WP_REST_Request $request Incoming REST API request data.
	 * @return \WP_Error|true Error if a permission check fails.
	 */
	public function submit_item_permissions_check( $request ) {
		$checks = array(
			'rate_limit',
			'form_nonce',
			'customer_nonce',
			'required_fields',
		);

		return $this->permission_checks( $checks, $request );
	}

	/**
	 * Handles POST /wpsp/v2/order/submit
	 *
	 * Currently only used to submit actual Stripe Orders for one-time payments
	 * using automatic tax. This endpoint is not used for activating subscriptions.
	 *
	 * @since 4.6.0
	 *
	 * @param \WP_REST_Request $request Incoming REST API request data.
	 * @throws \Exception When required data is missing or cannot be verified.
	 * @return \WP_REST_Response
	 */
	public function submit_item( $request ) {
		try {
			if ( ! isset( $request['order_id'] ) ) {
				throw new Exception(
					esc_html__(
						'Invalid request. Please try again.',
						'simple-pay'
					)
				);
			}

			$form_id = sanitize_text_field( $request['form_id'] );
			$form    = simpay_get_form( $form_id );

			if ( false === $form ) {
				throw new Exception(
					esc_html__(
						'Invalid request. Please try again.',
						'simple-pay'
					)
				);
			}

			// Block access if not in schedule.
			if ( false === $form->has_available_schedule() ) {
				throw new Exception(
					esc_html__(
						'Invalid request. Please try again.',
						'simple-pay'
					)
				);
			}

			// Block acccess if form type is incorrect.
			if ( 'stripe_checkout' === $form->get_display_type() ) {
				throw new Exception(
					esc_html__(
						'Invalid request. Please try again.',
						'simple-pay'
					)
				);
			}

			$tax_status = get_post_meta( $form_id, '_tax_status', true );

			if ( 'automatic' !== $tax_status ) {
				throw new Exception(
					esc_html__(
						'Invalid request. Please try again.',
						'simple-pay'
					)
				);
			}

			/**
			 * Allows processing before a Stripe Order is submitted.
			 *
			 * @since 4.6.0
			 *
			 * @param \WP_REST_Request              $request Incoming REST API request data.
			 * @param SimplePay\Core\Abstracts\Form $form Form instance.
			 */
			do_action(
				'simpay_before_order_submit_from_payment_form_request',
				$request,
				$form
			);

			// Build order arguments to update, starting with customer,
			// payment method, and return URL.
			$order_id    = sanitize_text_field( $request['order_id'] );
			$customer_id = sanitize_text_field( $request['customer_id'] );

			$payment_method_type = isset( $request['payment_method_type'] )
				? sanitize_text_field( $request['payment_method_type'] )
				: false;

			$paymentintent_args = PaymentIntent\get_args_from_payment_form_request(
				$form,
				json_decode( $request['form_data'], true ),
				array_merge(
					array(
						'payment_method_type' => $payment_method_type,
					),
					$request['form_values']
				),
				null
			);

			// Retrieve the Order.
			$order = Stripe_API::request(
				'Order',
				'retrieve',
				$order_id,
				$form->get_api_request_args()
			);

			// Reopen the Order so it can be updated.
			if ( 'submitted' === $order->status ) {
				$order = Stripe_API::request(
					'Order',
					'reopen',
					$order_id,
					$form->get_api_request_args()
				);
			}

			// Build new Order arguments.
			$order_args = array(
				'customer' => $customer_id,
				'payment'  => array(
					'settings' => array(
						'payment_method_types' =>
							$paymentintent_args['payment_method_types'],
						'return_url'           => add_query_arg(
							'customer',
							$customer_id,
							$form->payment_success_page
						),
					),
				),
			);

			// Update shipping_details if present.
			$address = $this->get_address_details( $request );

			if (
				! empty( $address['shipping'] ) &&
				! empty( $address['shipping']['address'] ) &&
				! empty( $address['shipping']['address']['country'] ) &&
				! empty( $address['shipping']['name'] )
			) {
				$order_args['shipping_details'] = $address['shipping'];
			}

			// Update the Order with latest data.
			$order = Stripe_API::request(
				'Order',
				'update',
				$order->id,
				$order_args,
				$form->get_api_request_args()
			);

			/**
			 * Filters Order arguments before the order is submitted.
			 *
			 * Occurs after the Order is initially updated with payment form data.
			 * A second update is required to ensure we are using the latest data
			 * associated with the Order, after it is initially updated with
			 * the payment form data.
			 *
			 * @since 4.6.0
			 *
			 * @param array<string, mixed>           $order_args Order arguments.
			 * @param \SimplePay\Vendor\Stripe\Order $order Order object.
			 */
			$filtered_order_args = apply_filters(
				'simpay_get_order_args_from_payment_form_request',
				$order_args,
				$order
			);

			// Update the Order once more, if the filtered arguments are different.
			if ( $order_args !== $filtered_order_args ) {
				$order = Stripe_API::request(
					'Order',
					'update',
					$order_id,
					$filtered_order_args,
					$form->get_api_request_args()
				);
			}

			// ...then submit.
			$order = Stripe_API::request(
				'Order',
				'submit',
				$order_id,
				array(
					'expected_total' => $order->amount_total,
					'expand'         => array(
						'customer',
						'payment.payment_intent',
					),
				),
				$form->get_api_request_args()
			);

			/**
			 * Allow further processing after an Order is is submited from a posted form.
			 *
			 * @since 4.6.0
			 *
			 * @param array<mixed>                   $request REST API request.
			 * @param \SimplePay\Vendor\Stripe\Order $order Stripe Order.
			 * @param \SimplePay\Core\Abstracts\Form $form Form instance.
			 */
			do_action(
				'simpay_after_order_submit_from_payment_form_request',
				$request,
				$order,
				$form
			);

			// Update the created PaymentIntent with the same information that
			// would be added when using the PaymentIntent API directly to
			// improve compatibility between APIs.
			if ( $order->payment && $order->payment->payment_intent ) {
				unset( $paymentintent_args['amount'] );
				unset( $paymentintent_args['currency'] );
				unset( $paymentintent_args['payment_method_types'] );

				API\PaymentIntents\update(
					$order->payment->payment_intent->id,
					$paymentintent_args
				);
			}

			return $order;
		} catch ( Exception $e ) {
			return new WP_REST_Response(
				array(
					'message' => Utils\handle_exception_message( $e ),
				),
				400
			);
		}
	}

	/**
	 * Previews a one-time payment amount by generating a Stripe Order.
	 *
	 * @link https://stripe.com/docs/orders/tax
	 *
	 * @since 4.6.0
	 *
	 * @param \WP_REST_Request               $request Incoming REST API request data.
	 * @param \SimplePay\Core\Abstracts\Form $form Payment form.
	 * @throws \SimplePay\Vendor\Stripe\Exception\ExceptionInterface Invalid Stripe API request.
	 * @return \SimplePay\Vendor\Stripe\Order Preview Stripe Order.
	 */
	private function preview_one_time( $request, $form ) {
		// Setup base Order arguments.
		$order_args = array(
			'automatic_tax' => array(
				'enabled' => true,
			),
			'currency'      => sanitize_text_field( $request['currency'] ),
			'ip_address'    => Utils\get_current_ip_address(),
			'expand'        => array(
				'line_items',
			),
			'payment'       => array(
				'settings' => array(),
			),
		);

		// Payment Method type.
		$payment_method_type = isset( $request['payment_method_type'] )
			? sanitize_text_field( $request['payment_method_type'] )
			: false;

		switch ( $payment_method_type ) {
			case 'afterpay-clearpay':
				$payment_method_type = 'afterpay_clearpay';
				break;
			case 'sepa-debit':
				$payment_method_type = 'sepa_debit';
				break;
			default:
				$payment_method_type = $payment_method_type;
		}

		$paymentintent_args = PaymentIntent\get_args_from_payment_form_request(
			$form,
			json_decode( $request['form_data'], true ),
			array_merge(
				array(
					'payment_method_type' => $payment_method_type,
				),
				$request['form_values']
			),
			null
		);

		$order_args['payment']['settings']['payment_method_types'] =
			$paymentintent_args['payment_method_types'];

		$off_session_pms     = array( 'card', 'sepa_debit' );
		$payment_method_type = current(
			$paymentintent_args['payment_method_types']
		);

		if ( in_array( $payment_method_type, $off_session_pms, true ) ) {
			$order_args['payment']
				['settings']
				['payment_method_options']
				[ $payment_method_type ]
				['setup_future_usage'] = 'off_session';
		}

		$line_items = array( $this->get_base_line_item( $request, $form ) );
		$address    = $this->get_address_details( $request );

		// Add line items.
		$order_args['line_items'] = $line_items;

		// Remove State from location if it's not supported by Stripe Tax.
		$billing_address = $address['billing']['address'];
		$billing_country = $billing_address['country'];

		if ( ! in_array( $billing_country, array( 'US', 'CA' ), true ) ) {
			unset( $billing_address['state'] );
		}

		$order_args['billing_details'] = array(
			'address' => $billing_address,
		);

		if (
			! empty( $address['shipping'] ) &&
			! empty( $address['shipping']['address'] ) &&
			! empty( $address['shipping']['address']['country'] )
		) {
			// Remove State from location if it's not supported by Stripe Tax.
			$shipping_address = $address['shipping']['address'];
			$shipping_country = $shipping_address['country'];

			if ( ! in_array( $shipping_country, array( 'US', 'CA' ), true ) ) {
				unset( $shipping_address['state'] );
			}

			$order_args['shipping_details'] = array(
				'name'    => ! empty( $address['shipping']['name'] )
					? sanitize_text_field( $address['shipping']['name'] )
					: ' ',
				'address' => $shipping_address,
			);
		}

		// Add a coupon if needed.
		if ( ! empty( $request['coupon'] ) ) {
			$order_args['discounts'][] = array(
				'coupon' => sanitize_text_field(
					$request['coupon']['id']
				),
			);
		}

		$object_id = ! empty( $request['object_id'] )
			? sanitize_text_field( $request['object_id'] )
			: false;

		if ( false === $object_id ) {
			return Stripe_API::request(
				'Order',
				'create',
				$order_args,
				$form->get_api_request_args()
			);
		} else {
			// Repopen the order before updating, if already submitted.
			// @todo We have to pull this again on the server because we do not
			// send any order data from the client. In the future, to improve
			// performance, we could send the order status from the client.
			$order = Stripe_API::request(
				'Order',
				'retrieve',
				$object_id,
				$form->get_api_request_args()
			);

			if ( 'submitted' === $order->status ) {
				$order = Stripe_API::request(
					'Order',
					'reopen',
					$object_id,
					$form->get_api_request_args()
				);
			}

			return Stripe_API::request(
				'Order',
				'update',
				$order->id,
				$order_args,
				$form->get_api_request_args()
			);
		}
	}

	/**
	 * Previews a recurring payment amount by generating an upcoming invoice.
	 *
	 * @link https://stripe.com/docs/tax/subscriptions#preview-price
	 *
	 * @since 4.6.0
	 *
	 * @param \WP_REST_Request               $request Incoming REST API request data.
	 * @param \SimplePay\Core\Abstracts\Form $form Payment form.
	 * @param bool                           $is_next If the line items should preview as the "second" invoice
	 *                                                which ignores trials and setup fees.
	 * @throws \SimplePay\Vendor\Stripe\Exception\ExceptionInterface Invalid Stripe API request.
	 * @return \SimplePay\Vendor\Stripe\Invoice Preview invoice.
	 */
	private function preview_recurring( $request, $form, $is_next ) {
		// Setup base Invoice arguments.
		$invoice_args = array(
			'automatic_tax' => array(
				'enabled' => true,
			),
		);

		$recurring_line_item = $this->get_base_line_item( $request, $form );
		$one_time_line_items = $this->get_setup_fee_line_items(
			$request,
			$form
		);

		$address = $this->get_address_details( $request, $form );

		// Add line items.
		$invoice_args['subscription_items'] = array( $recurring_line_item );

		if ( false === $is_next ) {
			$invoice_args['invoice_items'] = $one_time_line_items;
		}

		// Add address information.
		$invoice_args['customer_details'] = array(
			'address' => $address['billing']['address'],
		);

		if ( ! empty( $address['shipping']['address'] ) ) {
			$invoice_args['customer_details']['shipping'] = $address['shipping'];

			$invoice_args['customer_details']['shipping']['name'] =
				! empty( $address['shipping']['name'] )
					? sanitize_text_field( $address['shipping']['name'] )
					: ' ';
		}

		// Add a coupon if needed.
		if ( ! empty( $request['coupon'] ) ) {
			$coupon_duration = sanitize_text_field(
				$request['coupon']['duration']
			);

			if ( ! ( 'once' === $coupon_duration && true === $is_next ) ) {
				$invoice_args['coupon'] = sanitize_text_field(
					$request['coupon']['id']
				);
			}
		}

		return Stripe_API::request(
			'Invoice',
			'upcoming',
			$invoice_args,
			$form->get_api_request_args()
		);
	}

	/**
	 * Returns address details from the REST API request.
	 *
	 * @since 4.6.0
	 *
	 * @param \WP_REST_Request $request Incoming REST API request data.
	 * @return array<string, array<string, string|array<string, string>>>
	 */
	private function get_address_details( $request ) {
		$_billing  = array();
		$_shipping = array();

		$address_type = sanitize_text_field(
			$request['automatic_tax']['address_type']
		);

		$current_address_country = isset( $request['automatic_tax']['current_address_country'] )
			? sanitize_text_field(
				$request['automatic_tax']['current_address_country']
			)
			: '';

		$next_address_country = sanitize_text_field(
			$request['automatic_tax']['next_address_country']
		);

		$is_new_country = (
			! empty( $current_address_country ) &&
			$current_address_country !== $next_address_country
		);

		// Billing.
		$billing_details = $request['billing_details'];

		// ...name.
		if ( ! empty( $billing_details['name'] ) ) {
			$_billing['name'] = sanitize_text_field(
				$billing_details['name']
			);
		};

		// ...email.
		if ( ! empty( $billing_details['email'] ) ) {
			$_billing['email'] = sanitize_text_field(
				$billing_details['email']
			);
		};

		// ...address.
		$billing_address = empty( $request['billing_details']['address'] )
			? array()
			: $request['billing_details']['address'];

		$shipping_address = empty( $request['shipping_details']['address'] )
			? array()
			: $request['shipping_details']['address'];

		$billing_address = array_map(
			function( $address_part ) {
				return sanitize_text_field( $address_part );
			},
			$billing_address
		);

		// Remove items if the country has changed.
		if ( true === $is_new_country && 'billing' === $address_type ) {
			unset( $billing_address['line1'] );
			unset( $billing_address['city'] );
			unset( $billing_address['state'] );
			unset( $billing_address['postal_code'] );

			$is_shipping_address_same_as_billing =
				(bool) $request['automatic_tax']['is_shipping_address_same_as_billing'];

			if ( $is_shipping_address_same_as_billing ) {
				unset( $shipping_address['line1'] );
				unset( $shipping_address['city'] );
				unset( $shipping_address['state'] );
				unset( $shipping_address['postal_code'] );
			}
		}

		$_billing['address'] = array_filter( $billing_address );

		// Shipping.
		$shipping_details = $request['shipping_details'];

		// ...name.
		if ( ! empty( $shipping_details['name'] ) ) {
			$_shipping['name'] = sanitize_text_field(
				$shipping_details['name']
			);
		}

		// ...address.
		$shipping_address = array_map(
			function( $address_part ) {
				return sanitize_text_field( $address_part );
			},
			$shipping_address
		);

		// Remove items if the country has changed.
		if ( true === $is_new_country && 'shipping' === $address_type ) {
			unset( $shipping_address['line1'] );
			unset( $shipping_address['city'] );
			unset( $shipping_address['state'] );
			unset( $shipping_address['postal_code'] );
		}

		$shipping_address = array_filter( $shipping_address );

		if ( ! empty( $shipping_address ) ) {
			$_shipping['address'] = $shipping_address;
		}

		// Build return value with found data.
		$_billing  = array_filter( $_billing );
		$_shipping = array_filter( $_shipping );

		$retval = array();

		if ( ! empty( $_billing ) ) {
			$retval['billing'] = $_billing;
		}

		if ( ! empty( $_shipping ) ) {
			$retval['shipping'] = $_shipping;
		}

		return $retval;
	}

	/**
	 * Returns a list of fields that are used to create inputs on the client.
	 *
	 * @since 4.6.0
	 *
	 * @param \SimplePay\Core\Abstracts\Form              $form Payment Form.
	 * @param array<string, string|array<string, string>> $address_data Address data from the REST API request.
	 * @return array<string, array<string, array<string, string|array<string, string>>>> $fields List of fields.
	 */
	private function get_address_field_data( $form, $address_data ) {
		$data = array();

		/** @var array<string, array<string, string>> $states */
		$states = include SIMPLE_PAY_DIR . 'includes/core/i18n/states.php'; // @phpstan-ignore-line

		/** @var array<string, array<string, string>> $custom_fields */
		$custom_fields = get_post_meta(
			$form->id,
			'_custom_fields',
			true
		);
		$address_field = current( $custom_fields['address'] );
		$address_types = array( 'billing', 'shipping' );

		foreach ( $address_types as $address_type ) {
			$address = empty( $address_data[ $address_type ]['address'] )
				? $address_data['billing']['address']
				: $address_data[ $address_type ]['address'];

			$data[ $address_type ] = array(
				'country'     => array(
					'label'   => isset( $address_field['label-country'] )
						? esc_attr( $address_field['label-country'] )
						: '',
					'value'   => isset( $address['country'] )
						? esc_attr( $address['country'] )
						: '',
					'options' => i18n\get_countries(),
				),
				'line1'       => array(
					'label'       => isset( $address_field['label-street'] )
						? esc_attr( $address_field['label-street'] )
						: '',
					'value'       => isset( $address['line1'] )
						? esc_attr( $address['line1'] )
						: '',
					'placeholder' => isset( $address_field['placeholder-street'] )
						? esc_attr( $address_field['placeholder-street'] )
						: '',
					'options'     => '',
				),
				'city'        => array(
					'label'       => isset( $address_field['label-city'] )
						? esc_attr( $address_field['label-city'] )
						: '',
					'value'       => isset( $address['city'] )
						? esc_attr( $address['city'] )
						: '',
					'placeholder' => isset( $address_field['placeholder-city'] )
						? esc_attr( $address_field['placeholder-city'] )
						: '',
					'options'     => '',
				),
				'state'       => array(
					'label'       => isset( $address_field['label-state'] )
						? esc_attr( $address_field['label-state'] )
						: '',
					'value'       => isset( $address['state'] )
						? esc_attr( $address['state'] )
						: '',
					'placeholder' => isset( $address_field['placeholder-state'] )
						? esc_attr( $address_field['placeholder-state'] )
						: '',
					'options'     => ! empty( $states[ $address['country'] ] )
						? $states[ $address['country'] ]
						: '',
				),
				'postal_code' => array(
					'label'       => isset( $address_field['label-zip'] )
						? esc_attr( $address_field['label-zip'] )
						: '',
					'value'       => isset( $address['postal_code'] )
						? esc_attr( $address['postal_code'] )
						: '',
					'placeholder' => isset( $address_field['placeholder-zip'] )
						? esc_attr( $address_field['placeholder-zip'] )
						: '',
					'options'     => '',
				),
			);
		}

		return $data;
	}

	/**
	 * Returns the "base" line item for a given request.
	 *
	 * @since 4.6.0
	 *
	 * @param \WP_REST_Request               $request Incoming REST API request data.
	 * @param \SimplePay\Core\Abstracts\Form $form Payment form.
	 * @return array<string, string|array<string, string>>
	 */
	private function get_base_line_item( $request, $form ) {
		// Find the "base" line item from the request data.
		// Setup and Plan fees will be retrieved from the saved price option settings.
		$request_line_item = array_filter(
			$request['line_items'],
			function( $line_item ) {
				return ! empty( $line_item['price'] );
			}
		);

		if ( empty( $request_line_item ) ) {
			return array();
		}

		$base_line_item = current( $request_line_item );
		$base_price     = $this->get_base_line_item_price( $request, $form );

		if ( false === $base_price ) {
			return array();
		}

		// Start building line item arguments.
		$line_item = array(
			'quantity' => intval( $base_line_item['quantity'] ),
		);

		$is_defined_price = simpay_payment_form_prices_is_defined_price(
			$base_price->id
		);

		// Defined price, send it straight through.
		if ( true === $is_defined_price ) {
			$line_item['price'] = $base_price->id;

			// Build an ad-hoc price.
		} else {
			// Retrieve tax behavior.
			$tax_behavior = $this->get_tax_behavior( $form );

			// Ensure custom amount meets minimum requirement.
			$unit_amount = intval( $base_line_item['unit_amount'] );

			if ( $unit_amount < $base_price->unit_amount_min ) {
				$unit_amount = $base_price->unit_amount_min;
			}

			$line_item['price_data'] = array(
				'currency'     => sanitize_text_field(
					$request['currency']
				),
				'product'      => $base_price->product_id,
				'tax_behavior' => $tax_behavior,
				'unit_amount'  => $unit_amount,
			);

			// Add recurring information if needed.
			$is_recurring = 'true' === sanitize_text_field(
				$request['__unstable_is_recurring']
			);

			if ( $base_price->recurring && $is_recurring ) {
				$line_item['price_data']['recurring'] = array(
					'interval'       => $base_price->recurring['interval'],
					'interval_count' => $base_price->recurring['interval_count'],
				);
			}
		}

		return $line_item;
	}

	/**
	 * Returns the "Setup Fee" (and "Plan Fee") line item(s), built from the "base"
	 * line item's price options settings.
	 *
	 * @since 4.6.0
	 *
	 * @param \WP_REST_Request               $request Incoming REST API request data.
	 * @param \SimplePay\Core\Abstracts\Form $form Payment form.
	 * @return array<string, string|array<string, string>>
	 */
	private function get_setup_fee_line_items( $request, $form ) {
		$base_price = $this->get_base_line_item_price( $request, $form );

		if ( false === $base_price ) {
			return array();
		}

		if ( empty( $base_price->line_items ) ) {
			return array();
		}

		return array_map(
			function( $line_item ) use ( $request, $form, $base_price ) {
				return array(
					'quantity'   => 1,
					'price_data' => array(
						'currency'     => sanitize_text_field(
							$request['currency']
						),
						'unit_amount'  => intval(
							$line_item['unit_amount']
						),
						'tax_behavior' => $this->get_tax_behavior( $form ),
						'product'      => $base_price->product_id,
					),
				);
			},
			$base_price->line_items
		);
	}

	/**
	 * Returns the payment form's tax behavior.
	 *
	 * @since 4.6.0
	 *
	 * @param \SimplePay\Core\Abstracts\Form $form Payment form.
	 * @return string
	 */
	private function get_tax_behavior( $form ) {
		return get_post_meta( $form->id, '_tax_behavior', true );
	}

	/**
	 * Returns the "base" line item's associated price option. The configuration
	 * of this price option is used to generate setup fee and plan fee line items.
	 *
	 * @since 4.6.0
	 *
	 * @param \WP_REST_Request               $request Incoming REST API request data.
	 * @param \SimplePay\Core\Abstracts\Form $form Payment form.
	 * @return \SimplePay\Core\PaymentForm\PriceOption|false
	 */
	private function get_base_line_item_price( $request, $form ) {
		// Retrieve the "base" line item from the request data.
		// Setup and Plan fees will be retrieved from the saved price option settings.
		$base_line_item = array_filter(
			$request['line_items'],
			function( $line_item ) {
				return ! empty( $line_item['price'] );
			}
		);

		if ( empty( $base_line_item ) ) {
			return false;
		}

		$base_line_item = current( $base_line_item );

		return simpay_payment_form_prices_get_price_by_id(
			$form,
			sanitize_text_field( $base_line_item['price'] )
		);
	}

	/**
	 * SUMs a list of objects containing an `amount` property.
	 *
	 * @since 4.6.0
	 *
	 * @param int       $total Total amount carry from previous iteration.
	 * @param \stdClass $object Object containing an `amount` property.
	 */
	private function sum_amount( $total, $object ) {
		if ( ! isset( $object->amount ) ) {
			return $total;
		}

		return $total += $object->amount;
	}

}
