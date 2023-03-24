<?php
/**
 * REST API: v1 Base Controller
 *
 * @package SimplePay\Pro\REST_API\v1
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Pro\REST_API\v1;

use SimplePay\Core\REST_API\Controller;
use SimplePay\Core\Payments\Stripe_API;
use SimplePay\Pro\Webhooks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Webhooks_Controller class.
 *
 * @since 3.5.0
 */
class Webhooks_Controller extends Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wpsp/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'webhooks';

	/**
	 * List of Webhooks.
	 *
	 * @var string
	 */
	protected $webhooks = false;

	/**
	 * Register the routes for Webhooks.
	 *
	 * @since 3.5.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::READABLE ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( \WP_REST_Server::CREATABLE ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Handle an incoming webhook.
	 *
	 * @since 3.5.0
	 *
	 * @param \WP_REST_Request $request Incoming REST API request data.
	 * @return bool
	 */
	public function get_items_permissions_check( $request ) {
		return false;
	}

	/**
	 * List webhooks and connection data.
	 *
	 * @since 3.5.0
	 *
	 * @param \WP_REST_Request $request Incoming REST API request data.
	 * @return \WP_Error|\WP_REST_Reponse
	 */
	public function get_items( $request ) {
		return rest_ensure_response( array() );
	}

	/**
	 * Verify permission for creating a webhook.
	 *
	 * @since 3.5.0
	 *
	 * @param \WP_REST_Request $request Incoming REST API request data.
	 * @return bool
	 */
	public function create_item_permissions_check( $request ) {
		return false;
	}

	/**
	 * Create a webhook for all registered events.
	 *
	 * @since 3.5.0
	 *
	 * @param \WP_REST_Request $request Incoming REST API request data.
	 * @return \WP_Error|\WP_REST_Reponse
	 */
	public function create_item( $request ) {
		return rest_ensure_response( array() );
	}

}
