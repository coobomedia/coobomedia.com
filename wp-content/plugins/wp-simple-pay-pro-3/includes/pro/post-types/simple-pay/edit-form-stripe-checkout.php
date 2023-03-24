<?php
/**
 * Simple Pay: Edit form Stripe Checkout
 *
 * @package SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form;

use SimplePay\Pro\Payment_Methods;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hides the "Stripe Checkout Display" tab by default.
 *
 * Tab will become visible if the Form Display Type is "Stripe Checkout".
 *
 * @since 3.8.0
 *
 * @param array $tabs Payment Form settings tabs.
 * @return array $tabs Payment Form settings tabs.
 */
function hide_tab( $tabs ) {
	$tabs['stripe_checkout']['class'] = array(
		'simpay-show-if',
	);

	return $tabs;
}
add_filter( 'simpay_form_settings_meta_tabs_li', __NAMESPACE__ . '\\hide_tab' );

/**
 * Allow custom form styles to be used on the frontend.
 *
 * @since 3.8.0
 *
 * @param int $post_id Current Payment Form ID.
 */
function add_form_styles_setting( $post_id ) {
	?>

<tr class="simpay-panel-field">
	<th>
		<label for="_enable_stripe_checkout_form_styles">
			<?php esc_html_e( 'Enable Form Styles', 'simple-pay' ); ?>
		</label>
	</th>
	<td>
		<?php
		$enable_form_styles = simpay_get_payment_form_setting(
			$post_id,
			'_enable_stripe_checkout_form_styles',
			'no',
			__unstable_simpay_get_payment_form_template_from_url()
		);

		simpay_print_field(
			array(
				'type'        => 'checkbox',
				'name'        => '_enable_stripe_checkout_form_styles',
				'id'          => '_enable_stripe_checkout_form_styles',
				'value'       => $enable_form_styles,
				'description' => esc_html__(
					'Apply plugin styling to form fields that appear on-page. Otherwise the styles will inherit from the current theme.',
					'simple-pay'
				),
			)
		);
		?>
	</td>
</tr>

	<?php
}
add_action( 'simpay_after_checkout_button_text', __NAMESPACE__ . '\\add_form_styles_setting' );

/**
 * Output a link back to "Custom Form Fields" under the "Checkout Button Text" field.
 *
 * @since 3.8.0
 */
function add_custom_form_fields_link() {
	_doing_it_wrong(
		__FUNCTION__,
		esc_html__( 'No longer used.', 'simple-pay' ),
		'4.1.0'
	);
}
