<?php
/**
 * Coupons: List
 *
 * @package SimplePay
 * @subpackage Pro
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.3.0
 */

namespace SimplePay\Pro\Coupons\Admin;

use SimplePay\Core\Utils;
use SimplePay\Pro\Coupons\Coupon;
use SimplePay\Pro\Coupons\Coupon_Query;
use WP_List_Table;

// Load WP_List_Table if not loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class List_table extends WP_List_Table {

	/**
	 * The number of items to display per page.
	 *
	 * @since 4.3.0
	 * @var int
	 */
	private $per_page = 15;

	/**
	 * {@inheritdoc}
	 */
	public function __construct() {
		// Process bulk actions.
		$this->process_bulk_actions();

		parent::__construct(
			array(
				'singular' => 'coupon',
				'plural'   => 'coupons',
				'ajax'     => false,
			)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function prepare_items() {
		// Columns.
		$this->_column_headers = array( $this->get_columns() );

		// Results.
		$this->items = $this->get_data();

		// Pagination.
		$total = $this->get_total();

		$this->set_pagination_args(
			array(
				'total_pages' => ceil( $total / $this->per_page ),
				'total_items' => $total,
				'per_page'    => $this->per_page,
			)
		);
	}

	/**
	 * Returns the total number of results.
	 *
	 * @since 4.3.0
	 *
	 * @return int
	 */
	private function get_total() {
		// Return 0 results if no secret key.
		if ( empty( simpay_get_secret_key() ) ) {
			return 0;
		}

		$args     = $this->get_args();
		$database = new Coupon_Query(
			simpay_is_livemode(),
			simpay_get_secret_key()
		);

		return $database->count( $args );
	}

	/**
	 * Returns the query result items.
	 *
	 * @since 4.3.0
	 *
	 * @return \SimplePay\Pro\Coupons\Coupon[]
	 */
	public function get_data() {
		// Output nothing if there is no secret key.
		if ( empty( simpay_get_secret_key() ) ) {
			return array();
		}

		$args     = $this->get_args();
		$database = new Coupon_Query(
			simpay_is_livemode(),
			simpay_get_secret_key()
		);

		return $database->query( $args );
	}

	/**
	 * Retrieves query arguments from the current URL.
	 *
	 * @since 4.3.0
	 *
	 * @return array
	 */
	private function get_args() {
		// Pagination.
		$paged = isset( $_GET['paged'] )
			? absint( $_GET['paged'] )
			: 1;

		if ( ! empty( $paged ) && is_numeric( $paged ) && ( $paged > 1 ) ) {
			$offset = ceil( $this->per_page * ( $paged - 1 ) );
		} else {
			$offset = 0;
		}

		return array(
			'number' => $this->per_page,
			'offset' => $offset,
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function no_items() {
		if ( ! empty( simpay_get_secret_key() ) ) {
			return parent::no_items();
		}

		echo wp_kses(
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__(
					'%1$sConnect your Stripe account%2$s to manage coupons.',
					'simple-pay'
				),
				'<a href="' . esc_url( simpay_get_stripe_connect_url() ) . '">',
				'</a>'
			),
			array(
				'a' => array(
					'href' => true,
				)
			)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_bulk_actions() {
		return array(
			'delete' => esc_html__( 'Delete', 'simple-pay' ),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_columns() {
		return array(
			'cb'           => '<input type="checkbox" />',
			'coupon_code'  => esc_html__( 'Code', 'simple-pay' ),
			'terms'        => esc_html__( 'Terms', 'simple-pay' ),
			'redemptions'  => esc_html__( 'Redemptions', 'simple-pay' ),
			'restrictions' => esc_html__( 'Payment Forms', 'simple-pay' ),
			'expires'      => esc_html__( 'Expires', 'simple-pay' ),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function column_default( $coupon, $column_name ) {
		$prop = isset( $coupon->$column_name )
			? $coupon->$column_name
			: '';

		// Show error if one exists.
		if ( null !== $coupon->error ) {
			switch ( $column_name ) {
				case 'coupon_code':
					return $this->get_error_message( $coupon );
				default:
					return '';
			}
		}

		return method_exists( $this, 'get_column_' . $column_name )
			// Render custom column if available.
			? call_user_func(
				array( $this, 'get_column_' . $column_name ),
				$coupon
			)
			// Fall back to object propert.
			: $prop;
	}

	/**
	 * Returns a checkbox to select the coupon row.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Coupon $coupon Coupon.
	 * @return string
	 */
	public function column_cb( $coupon ) {
		// Do not output if there is an error.
		if ( null !== $coupon->error ) {
			return '';
		}

		return sprintf(
			'<input type="checkbox" name="coupons[]" id="coupon-%1$s" value="%1$s" /><label for="coupon-%1$s" class="screen-reader-text">%2$s</label>',
			esc_attr( $coupon->id ),
			esc_html(
				sprintf(
					/* translators: %s coupon code */
					__( 'Select coupon %s', 'simple-pay' ),
					$coupon->name
				)
			)
		);
	}

	/**
	 * Returns the coupon's code for display.
	 *
	 * @since 4.3.0
	 *
	 * @return string
	 */
	public function get_column_coupon_code( Coupon $coupon ) {
		$delete_url = wp_nonce_url(
			add_query_arg(
				array(
					'post_type'     => 'simple-pay',
					'page'          => 'simpay_coupons',
					'simpay-action' => 'delete-coupon',
					'id'            => $coupon->id,
				),
				admin_url( 'edit.php' )
			),
			'simpay-delete-coupon'
		);

		$actions = $this->row_actions(
			array(
				'delete' => sprintf(
					'<a href="%s">' . esc_html__( 'Delete', 'simple-pay' ) . '</a>',
					esc_url( $delete_url )
				),
			)
		);

		$code = '<strong><code>' . esc_html( $coupon->name ) . '</code></strong>';

		if ( $coupon->redeem_by ) {
			$expired_flag = $coupon->redeem_by->getTimestamp() < time()
				? '<strong> &mdash; ' . esc_html__( 'Expired', 'simple-pay' ) . '</strong>'
				: '';
		} else {
			$expired_flag = '';
		}

		return $code . $expired_flag . $actions;
	}

	/**
	 * Returns the coupon's terms for display, i.e 20% off forever.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Coupon $coupon Coupon.
	 * @return string
	 */
	public function get_column_terms( Coupon $coupon ) {
		$amount = $coupon->get_display_amount();

		switch ( $coupon->duration ) {
			case 'once':
				$duration = _x( 'once', 'coupon duration', 'simple-pay' );
				break;
			case 'repeating':
				$duration = sprintf(
					/* translators: %d Coupon duration in months */
					_nx(
						'for %d month',
						'for %d months',
						$coupon->duration_in_months,
						'coupon duration',
						'simple-pay'
					),
					$coupon->duration_in_months
				);
				break;
			default:
				$duration = 'forever';
		}

		return sprintf(
			/* translators: %1$s coupon amount, do not translate. %2$s coupon duration, do not translate. */
			esc_html__( '%1$s off %2$s', 'simple-pay' ),
			$amount,
			$duration
		);
	}

	/**
	 * Returns the coupon's redemption count.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Coupon $coupon Coupon.
	 */
	public function get_column_redemptions( Coupon $coupon ) {
		$times_redeemed = $coupon->times_redeemed ? $coupon->times_redeemed : 0;

		return $coupon->max_redemptions
			? sprintf( '%d / %d', $times_redeemed, $coupon->max_redemptions )
			: $times_redeemed;
	}

	/**
	 * Returns the coupon's payment form restrictions.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Coupon $coupon Coupon.
	 */
	public function get_column_restrictions( Coupon $coupon ) {
		if ( null === $coupon->applies_to_forms ) {
			return '&mdash;';
		}

		$links = array();

		foreach ( $coupon->applies_to_forms as $payment_form_id ) {
			$link = add_query_arg(
				array(
					'post'   => $payment_form_id,
					'action' => 'edit',
				),
				admin_url( 'post.php' )
			);

			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $link ),
				get_the_title( $payment_form_id )
			);
		}

		return implode( ', ', $links );
	}

	/**
	 * Returns the coupon's expiration date.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Coupon $coupon Coupon.
	 * @return string
	 */
	public function get_column_expires( Coupon $coupon ) {
		$format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		return isset( $coupon->redeem_by )
			? get_date_from_gmt(
				$coupon->redeem_by->format( 'Y-m-d H:i:s' ),
				$format
			)
			: '&mdash;';
	}

	/**
	 * Returns an error message with special handling for certain error codes.
	 *
	 * @since 4.3.0
	 *
	 * @param \SimplePay\Pro\Coupons\Coupon $coupon Coupon.
	 * @return string
	 */
	private function get_error_message( Coupon $coupon ) {
		$mode = simpay_is_test_mode()
			? _x( 'test', 'payment mode', 'simple-pay' )
			: _x( 'live', 'payment mode', 'simple-pay' );

		$coupon_url = sprintf(
			'https://dashboard.stripe.com%s/coupons/%s',
			simpay_is_test_mode() ? '/test' : '',
			$coupon->name
		);

		switch ( $coupon->get_error_code() ) {
			case 'resource_already_exists':
				return sprintf(
					/* translators: %1$s Coupon name. %2$s Opening anchor tag, do not translate. %3$s Closing anchor tag, do not translate. */
					__(
						'A coupon with the ID %1$ss already exists in %2$s mode. To keep the item in sync automatically please %3$sdelete the coupon%4$s to allow WP Simple Pay to recreate it.',
						'simple-pay'
					),
					'<code>' . $coupon->name . '</code>',
					$mode,
					'<a href="' . esc_url( $coupon_url ) . '" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
					Utils\get_external_link_markup() . '</a>'
				);
			default:
				return $coupon->get_error_message();
		}

	}

}
