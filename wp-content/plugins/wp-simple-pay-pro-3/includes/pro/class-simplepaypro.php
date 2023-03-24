<?php
/**
 * SimplePay: Pro
 *
 * @package SimplePay\Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro;

use SimplePay\Pro\Admin;
use SimplePay\Pro\Forms\Ajax;
use SimplePay\Pro\Webhooks\Database\Table as Webhooks_Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pro SimplePay Class
 */
final class SimplePayPro {

	/**
	 * The single instance of this class.
	 *
	 * @var \SimplePay\Pro\SimplePayPro
	 * @since 3.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Simple Pay instance
	 *
	 * Ensures only one instance of Simple Pay is loaded or can be loaded.
	 *
	 * @since 3.0.0
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 3.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'simple-pay' ), '3.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 3.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'simple-pay' ), '3.0' );
	}

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		$this->load();

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ), 9 );
	}

	/**
	 * Load the plugin.
	 *
	 * @since 3.0.0
	 */
	public function load() {
		// Migrations.
		require_once( SIMPLE_PAY_INC . 'pro/utils/migrations/register.php' );

		// Settings.
		require_once( SIMPLE_PAY_INC . 'pro/settings/register-stripe.php' );
		require_once( SIMPLE_PAY_INC . 'pro/settings/register-general.php' );
		require_once( SIMPLE_PAY_INC . 'pro/settings/register-payment-confirmations.php' );

		// Post types.
		require_once( SIMPLE_PAY_INC . 'pro/post-types/simple-pay/functions.php' );

		// Load pro shared back-end & front-end functions.
		require_once( SIMPLE_PAY_INC . 'pro/functions/license-management.php' );
		require_once( SIMPLE_PAY_INC . 'pro/functions/shared.php' );
		require_once( SIMPLE_PAY_INC . 'pro/functions/coupons.php' );

		// Payment Methods.
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/functions.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/ach-debit/functions.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/ach-debit/payment-confirmation.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/afterpay-clearpay/functions.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/afterpay-clearpay/payment-confirmation.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/alipay/payment-confirmation.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/bancontact/payment-confirmation.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/card/functions.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/card/payment-confirmation.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/fpx/functions.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/fpx/payment-confirmation.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/giropay/payment-confirmation.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/ideal/functions.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/ideal/payment-confirmation.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/klarna/payment-confirmation.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/p24/functions.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/p24/payment-confirmation.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/sepa-debit/functions.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payment-methods/sepa-debit/payment-confirmation.php' );

		// Taxes.
		require_once( SIMPLE_PAY_INC . 'pro/taxes/class-tax-rate.php' );
		require_once( SIMPLE_PAY_INC . 'pro/taxes/class-tax-rates.php' );
		require_once( SIMPLE_PAY_INC . 'pro/taxes/functions.php' );
		require_once( SIMPLE_PAY_INC . 'pro/taxes/settings.php' );

		// Webhooks.
		$table = new Webhooks_Table();
		$table->maybe_upgrade();
		require_once( SIMPLE_PAY_INC . 'pro/webhooks/template-tags.php' );
		require_once( SIMPLE_PAY_INC . 'pro/webhooks/functions.php' );
		require_once( SIMPLE_PAY_INC . 'pro/webhooks/emails.php' );
		require_once( SIMPLE_PAY_INC . 'pro/webhooks/settings.php' );

		// Coupons. Manually include some classes to work towards better naming conventions.
		require_once( SIMPLE_PAY_INC . 'pro/coupons/stripe-sync/interface-synced-stripe-object.php' );
		require_once( SIMPLE_PAY_INC . 'pro/coupons/stripe-sync/trait-stripe-object-sync.php' );
		require_once( SIMPLE_PAY_INC . 'pro/coupons/stripe-sync/trait-stripe-object-query.php' );
		require_once( SIMPLE_PAY_INC . 'pro/coupons/database.php' );
		require_once( SIMPLE_PAY_INC . 'pro/coupons/admin/menu.php' );
		require_once( SIMPLE_PAY_INC . 'pro/coupons/admin/page.php' );
		require_once( SIMPLE_PAY_INC . 'pro/coupons/admin/actions.php' );

		// REST API.
		require_once( SIMPLE_PAY_INC . 'pro/rest-api/functions.php' );

		// Customers.
		require_once( SIMPLE_PAY_INC . 'pro/customers/settings.php' );
		require_once( SIMPLE_PAY_INC . 'pro/customers/subscription-management.php' );

		// Emails.
		require_once( SIMPLE_PAY_INC . 'pro/emails/functions.php' );
		require_once( SIMPLE_PAY_INC . 'pro/emails/register.php' );
		require_once( SIMPLE_PAY_INC . 'pro/emails/settings.php' );
		require_once( SIMPLE_PAY_INC . 'pro/emails/mailer.php' );
		require_once( SIMPLE_PAY_INC . 'pro/emails/tools.php' );
		require_once( SIMPLE_PAY_INC . 'pro/emails/tools/send-test-email.php' );
		require_once( SIMPLE_PAY_INC . 'pro/emails/tools/resend-payment-confirmation.php' );

		// Payments/Purchase Flow.
		require_once( SIMPLE_PAY_INC . 'pro/payments/shared.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payments/plan.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payments/product.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payments/subscription.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payments/charge.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payments/payment-confirmation.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payments/payment-confirmation-template-tags.php' );

		// Stripe Checkout.
		require_once( SIMPLE_PAY_INC . 'pro/payments/stripe-checkout/session.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payments/stripe-checkout/subscription.php' );
		require_once( SIMPLE_PAY_INC . 'pro/payments/stripe-checkout/customer.php' );

		// Legacy.
		require_once( SIMPLE_PAY_INC . 'pro/legacy/hooks.php' );

		// Load Lite helper class to update various differences between Lite and Pro.
		new Lite_Helper();
		new Objects();
		new Assets();

		// Load frontend AJAX.
		new Ajax();

		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			$this->load_admin();
		}
	}

	/**
	 * Load the plugin admin.
	 *
	 * @since 3.0.0
	 */
	public function load_admin() {
		require_once( SIMPLE_PAY_INC . 'pro/functions/admin.php' );
		require_once( SIMPLE_PAY_INC . 'pro/admin/apple-pay.php' );
		require_once( SIMPLE_PAY_INC . 'pro/admin/license-management.php' );

		// Post types.
		require_once( SIMPLE_PAY_INC . 'pro/post-types/simple-pay/menu.php' );
		require_once( SIMPLE_PAY_INC . 'pro/post-types/simple-pay/actions.php' );
		require_once( SIMPLE_PAY_INC . 'pro/post-types/simple-pay/edit-form-payment-options.php' );
		require_once( SIMPLE_PAY_INC . 'pro/post-types/simple-pay/edit-form-custom-fields.php' );
		require_once( SIMPLE_PAY_INC . 'pro/post-types/simple-pay/edit-form-stripe-checkout.php' );
		require_once( SIMPLE_PAY_INC . 'pro/post-types/simple-pay/edit-form-subscription-options.php' );
		require_once( SIMPLE_PAY_INC . 'pro/post-types/simple-pay/edit-form-payment-page.php' );

		new Admin\Assets();

		// Admin ajax callbacks.
		new Admin\Ajax();
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 3.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'simple-pay', false, plugin_basename( dirname( SIMPLE_PAY_MAIN_FILE ) ) . '/languages' );
	}
}

/**
 * Start WP Simple Pay Pro.
 */
function SimplePayPro() {
	return SimplePayPro::instance();
}

SimplePayPro();
