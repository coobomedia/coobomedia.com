<?php
/**
 * Admin: AJAX
 *
 * @package SimplePay\Pro\Admin
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro\Admin;

use Plugin_Upgrader;
use SimplePay\Core\API;
use SimplePay\Core\License;
use SimplePay\Core\PaymentForm\PriceOption;
use SimplePay\Core\Settings;
use SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form;
use SimplePay\Pro\License_Management;
use WP_Ajax_Upgrader_Skin;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin ajax.
 *
 * @since 3.0.0
 */
class Ajax {

	/**
	 * Set up ajax hooks.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		add_action( 'wp_ajax_simpay_add_field', array( __CLASS__, 'add_field' ) );

		add_action( 'wp_ajax_simpay_add_price', array( __CLASS__, 'add_price' ) );
		add_action( 'wp_ajax_simpay_add_plan', array( __CLASS__, 'add_plan' ) );

		// Plugin installation and activation.
		add_action(
			'wp_ajax_simpay_activate_plugin',
			array( __CLASS__, 'activate_plugin' )
		);

		add_action(
			'wp_ajax_simpay_install_plugin',
			array( __CLASS__, 'install_plugin' )
		);

		// Coupon "Applies to forms" payment form search.
		add_action(
			'wp_ajax_simpay_coupons_payment_forms',
			array( __CLASS__, 'coupons_payment_forms' )
		);
	}

	/**
	 * Add a new metabox for custom fields settings
	 */
	public static function add_field() {

		// Check the nonce first.
		check_ajax_referer( 'simpay_custom_fields_nonce', 'addFieldNonce' );

		ob_start();

		$type = isset( $_POST['fieldType'] ) ? sanitize_key( strtolower( $_POST['fieldType'] ) ) : '';

		$counter = isset( $_POST['counter'] ) ? intval( $_POST['counter'] ) : 0;
		$uid     = isset( $_POST['nextUid'] ) ? intval( $_POST['nextUid'] ) : $counter;

		// Load new metabox depending on what type was selected.
		if ( ! empty( $type ) ) {
			try {
				global $post;

				$post = isset( $_POST['post_id'] )
					? get_post( absint( $_POST['post_id'] ) )
					: new \stdClass();

				echo Edit_Form\get_custom_field(
					$type,
					$counter,
					array(
						'uid' => $uid,
					),
					$post->ID
				);
			} catch ( \Exception $e ) {
				wp_send_json_error(
					array(
						'success' => false,
						'message' => $e,
					)
				);
			}
		} else {
			wp_send_json_error( array( 'success' => false ) );
		}

		ob_end_flush();

		die();
	}

	/**
	 * Handles the AJAX action `simpay_add_plan`.
	 *
	 * @since 4.1.0
	 * @access private
	 */
	public static function add_plan() {
		// Verify nonce.
		$nonce = check_ajax_referer( 'simpay_add_plan_nonce', '_wpnonce', false );

		if ( false === $nonce ) {
			wp_send_json_error(
				array(
					'message' => esc_html__(
						'Unable to add plan. Invalid security token.',
						'simple-pay'
					),
				)
			);
		}

		// Verify form.
		$form_id = isset( $_POST['form_id'] )
			? sanitize_text_field( $_POST['form_id' ] )
			: '';

		$form = simpay_get_form( $form_id );

		if ( false === $form ) {
			wp_send_json_error(
				array(
					'message' => esc_html__(
						'Unable to add plan. Invalid payment form.',
						'simple-pay'
					),
				)
			);
		}

		// Find Plan.
		$plan_id = isset( $_POST['plan_id'] )
			? sanitize_text_field( $_POST['plan_id' ] )
			: '';

		if ( empty( $plan_id ) ) {
			wp_send_json_error(
				array(
					'message' => array(
						'Unable to add plan. Plan ID not found.',
						'simple-pay'
					),
				)
			);
		}

		try {
			$plan = API\Plans\retrieve( $plan_id, $form->get_api_request_args() );

			$price = new PriceOption(
				array(
					'id'          => $plan->id,
					'default'     => false,
					'currency'    => $plan->currency,
					'unit_amount' => $plan->amount,
					'recurring'   => array(
						'interval'          => $plan->interval,
						'interval_count'    => $plan->interval_count,
						'trial_period_days' => $plan->trial_period_days,
					),
				),
				$form,
				wp_generate_uuid4()
			);

			ob_start();
			Edit_Form\__unstable_price_option( $price, wp_generate_uuid4(), array() );
			$html = ob_get_clean();

			wp_send_json_success( $html );
		} catch ( \Exception $e ) {
			wp_send_json_error(
				array(
					'message' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * Handles the AJAX action `simpay_add_price`.
	 *
	 * @since 4.1.0
	 * @access private
	 */
	public static function add_price() {
		$nonce = check_ajax_referer( 'simpay_add_price_nonce', '_wpnonce', false );

		if ( false === $nonce ) {
			wp_send_json_error(
				array(
					'message' => esc_html__(
						'Unable to add price. Invalid security token.',
						'simple-pay'
					),
				)
			);
		}

		// Verify form.
		$form_id = isset( $_POST['form_id'] )
			? sanitize_text_field( $_POST['form_id' ] )
			: '';

		$form = simpay_get_form( $form_id );

		if ( false === $form ) {
			wp_send_json_error(
				array(
					'message' => esc_html__(
						'Unable to add price. Invalid payment form.',
						'simple-pay'
					),
				)
			);
		}

		$currency = strtolower( simpay_get_setting( 'currency', 'USD' ) );

		$price = new PriceOption(
			array(
				'unit_amount' => simpay_get_currency_minimum( $currency ),
				'currency'    => $currency,
				'default'     => false,
				'can_recur'   => false,
			),
			$form,
			wp_generate_uuid4()
		);

		// Provides a way to circumvent a lack of `id` or `unit_amount_min`
		// before the PriceOption has a chance to be saved.
		$price->__unstable_unsaved = true;

		ob_start();
		Edit_Form\__unstable_price_option( $price, wp_generate_uuid4(), array() );
		$html = ob_get_clean();

		wp_send_json_success( $html );
	}

	/**
	 * Activate plugin ajax action.
	 *
	 * @since 4.3.0
	 */
	public static function activate_plugin() {

		// Run a security check.
		check_ajax_referer( 'simpay-admin', 'nonce' );

		// Check for permissions.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( esc_html__( 'Plugin activation is disabled for you on this site.', 'simple-pay' ) );
		}

		if ( ! isset( $_POST['plugin'] ) ) {
			wp_send_json_error( esc_html__( 'Could not activate the plugin. Plugin slug is empty.', 'simple-pay' ) );
		}

		$plugin   = sanitize_text_field( wp_unslash( $_POST['plugin'] ) );
		$activate = activate_plugins( $plugin );

		if ( ! is_wp_error( $activate ) ) {
			wp_send_json_success( esc_html__( 'Plugin activated.', 'simple-pay' ) );
		}

		wp_send_json_error( esc_html__( 'Could not activate the plugin. Please activate it on the Plugins page.', 'simple-pay' ) );
	}

	/**
	 * Install plugin ajax action.
	 *
	 * @since 4.3.0
	 */
	public static function install_plugin() {

		// Run a security check.
		check_ajax_referer( 'simpay-admin', 'nonce' );

		$generic_error = esc_html__( 'There was an error while performing your request.', 'simple-pay' );

		// Check if new installations are allowed.
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( $generic_error );
		}

		$error = esc_html__( 'Could not install the plugin. Please download and install it manually.', 'simple-pay' );

		if ( empty( $_POST['plugin'] ) ) {
			wp_send_json_error( $error );
		}

		// Prepare variables.
		$url = esc_url_raw( admin_url( 'plugins.php' ) );

		ob_start();
		$creds = request_filesystem_credentials( $url, '', false, false, null );

		// Hide the filesystem credentials form.
		ob_end_clean();

		// Check for file system permissions.
		if ( $creds === false ) {
			wp_send_json_error( $error );
		}

		if ( ! WP_Filesystem( $creds ) ) {
			wp_send_json_error( $error );
		}

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', [ 'Language_Pack_Upgrader', 'async_upgrade' ], 20 );

		/** \Plugin_Upgrader class */
		$upgrader = ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		if ( ! file_exists( $upgrader ) ) {
			wp_send_json_error( $error );
		}

		require_once $upgrader;

		$installer = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );

		// Error check.
		if ( ! method_exists( $installer, 'install' ) || empty( $_POST['plugin'] ) ) {
			wp_send_json_error( $error );
		}

		$installer->install( $_POST['plugin'] ); // phpcs:ignore

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		$plugin_basename = $installer->plugin_info();

		if ( empty( $plugin_basename ) ) {
			wp_send_json_error( $error );
		}

		$result = array(
			'msg'          => $generic_error,
			'is_activated' => false,
			'basename'     => $plugin_basename,
		);

		// Check for permissions.
		if ( ! current_user_can( 'activate_plugins' ) ) {
			$result['msg'] = esc_html__( 'Plugin installed.', 'simple-pay' );

			wp_send_json_success( $result );
		}

		// Activate the plugin silently.
		$activated = activate_plugin( $plugin_basename );

		if ( ! is_wp_error( $activated ) ) {

			$result['is_activated'] = true;
			$result['msg']          = esc_html__( 'Plugin installed & activated.', 'simple-pay' );

			wp_send_json_success( $result );
		}

		// Fallback error just in case.
		wp_send_json_error( $result );
	}

	/**
	 * Returns a list of payment form IDs and titles for a given search.
	 *
	 * @since 4.3.0
	 */
	public static function coupons_payment_forms() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_success(
				array(
					'forms' => array(),
				)
			);
		}

		if ( ! wp_verify_nonce( $_POST['nonce'], 'simpay-coupons-payment-forms' ) ) {
			wp_send_json_success(
				array(
					'forms' => array(),
				)
			);
		}

		$search  = sanitize_text_field( $_POST['search'] );
		$exclude = isset( $_POST['exclude'] )
			? array_map( 'absint', $_POST['exclude'] )
			: array( -999999 );

		// Search metadata.
		add_filter(
			'posts_where',
			array( __CLASS__, 'coupon_product_forms_search_where' )
		);

		add_filter(
			'posts_join',
			array( __CLASS__, 'coupon_product_forms_search_join' )
		);

		$payment_forms = new WP_Query(
			array(
				'post_type'      => 'simple-pay',
				'posts_per_page' => 10,
				's'              => $search,
				'post__not_in'   => $exclude,
			)
		);

		remove_filter(
			'posts_where',
			array( __CLASS__, 'coupon_product_forms_search_where' )
		);

		remove_filter(
			'posts_join',
			array( __CLASS__, 'coupon_product_forms_search_join' )
		);

		$payment_forms = array_map(
			function( $payment_form ) {
				return array(
					'id'      => intval( $payment_form->ID ),
					'title'   => esc_html( get_the_title( $payment_form->ID ) ),
				);
			},
			$payment_forms->posts
		);

		if ( empty( $payment_forms ) ) {
			wp_send_json_success(
				array(
					'message' => __( 'No payment forms found.', 'simple-pay' ),
				)
			);
		}

		wp_send_json_success(
			array(
				'forms' => $payment_forms,
				'message' => sprintf(
					/* translators: %d Number of payment forms found. */
					__( '%d results found', 'simple-pay' ),
					count( $payment_forms )
				),
			)
		);
	}

	/**
	 * Adjusts the `where` clause when performing a search on the `simple-pay`
	 * post type.
	 *
	 * @since 4.3.0
	 *
	 * @param string $where The WHERE clause of the query.
	 * @return string
	 */
	public static function coupon_product_forms_search_where( $where ) {
		global $wpdb;

		// Find the existing search WHERE and add an additional OR for meta_value.
		$where = preg_replace(
			"/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
			"(" . $wpdb->posts . ".post_title LIKE $1) OR (" . $wpdb->postmeta . ".meta_value LIKE $1) AND (" . $wpdb->postmeta . ".meta_key = '_company_name' OR " . $wpdb->postmeta . ".meta_key = '_item_description')",
			$where
		);

		$where .= " GROUP BY {$wpdb->posts}.ID";

		return $where;
	}

	/**
	 * Adjusts the `join` clause when performing a search on the `simple-pay`
	 * post type.
	 *
	 * @since 4.3.0
	 *
	 * @param string $where The WHERE clause of the query.
	 * @return string
	 */
	public static function coupon_product_forms_search_join( $join ) {
		global $wpdb;

		$join .= " LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id ";

		return $join;
	}

}
