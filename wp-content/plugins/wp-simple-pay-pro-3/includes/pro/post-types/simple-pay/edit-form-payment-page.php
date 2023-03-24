<?php
/**
 * Edit Form: Payment Page
 *
 * @package SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.5.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Edit_Form;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the "Permalink" setting.
 *
 * @since 4.5.0
 *
 * @param int $post_id Payment Form ID.
 * @return void
 */
function payment_page_permalink( $post_id ) {
	$post_name  = get_post_field( 'post_name', $post_id );
	$form_title = simpay_get_payment_form_setting(
		$post_id,
		'title',
		get_bloginfo( 'name' ),
		__unstable_simpay_get_payment_form_template_from_url()
	);

	// If the slug hasn't been set previously (using ID), create one from the title.
	$slug = is_numeric( $post_name ) || empty( $post_name )
		? sanitize_title( $form_title )
		: $post_name;
	$slug = wp_unique_post_slug(
		$slug,
		$post_id,
		'publish',
		'simple-pay',
		0
	);

	$payment_page_self_confirmation = simpay_get_payment_form_setting(
		$post_id,
		'_payment_page_self_confirmation',
		'yes',
		__unstable_simpay_get_payment_form_template_from_url()
	);
	?>

	<div class="simpay-show-if" data-if="_enable_payment_page" data-is="yes">
	<table >
		<tbody class="simpay-panel-section">
			<tr class="simpay-panel-field">
				<th style="border-top: 1px solid #ddd;">
					<label for="_payment_page_title_description">
						<?php esc_html_e( 'Permalink', 'simple-pay' ); ?>
					</label>
				</th>
				<td>
					<div style="display: flex; align-items: center; margin: 5px 0 0;">
						<div style="display: flex; align-items: center;">
							<code><?php echo esc_url( trailingslashit( home_url() ) ); ?></code>
							<input
								name="_payment_page_slug"
								type="text"
								id="_payment_page_slug"
								class="simpay-field"
								value="<?php echo esc_attr( $slug ); ?>"
							/>
						</div>
						<?php
						printf(
							'<button type="button" class="button button-secondary simpay-copy-button simpay-payment-page-url" data-copied="%1$s" data-clipboard-text="%2$s" style="margin-left: 5px;">%3$s</button>',
							esc_attr__( 'Copied!', 'simple-pay' ),
							esc_url( get_permalink( $post_id ) ),
							esc_html__( 'Copy URL', 'simple-pay' )
						);
						?>
					</div>

					<p class="description">
						<?php esc_html_e( 'This is the URL to your payment page. It must be unique.', 'simple-pay' ); ?>
					</p>

					<label for="_payment_page_self_confirmation" class="simpay-field-bool" style="display: block; margin-top: 8px;">
						<input
							name="_payment_page_self_confirmation"
							type="checkbox"
							id="_payment_page_self_confirmation"
							class="simpay-field simpay-field-checkbox simpay-field simpay-field-checkboxes"
							value="yes"
							<?php checked( true, 'yes' === $payment_page_self_confirmation ); ?>
						/><?php esc_html_e( 'Display payment confirmation on the same page', 'simple-pay' ); ?>
					</label>
				</td>
			</tr>
		</tbody>
	</table>
	</div>

	<?php
}
add_action(
	'simpay_form_settings_payment_page_panel',
	__NAMESPACE__ . '\\payment_page_permalink'
);

/**
 * Adds the display settings to the "Payment Page" panel.
 *
 * @since 4.5.0
 *
 * @param int $post_id Payment Form ID.
 * @return void
 */
function payment_page_display_settings( $post_id ) {
	wp_enqueue_script( 'wp-color-picker' );
	wp_enqueue_style( 'wp-color-picker' );
	?>

	<div class="simpay-show-if" data-if="_enable_payment_page" data-is="yes">
	<table >
		<tbody class="simpay-panel-section">
			<tr class="simpay-panel-field">
				<th>
					<strong>
						<?php esc_html_e( 'Color Scheme', 'simple-pay' ); ?>
					</strong>
				</th>
				<td style="border-bottom: 0; padding-bottom: 0;">
					<?php
					$background_color = simpay_get_payment_form_setting(
						$post_id,
						'_payment_page_background_color',
						'#428bca',
						__unstable_simpay_get_payment_form_template_from_url()
					);

					$colors = array(
						'#428bca' => __( 'Blue', 'simple-pay' ),
						'#1aa59f' => __( 'Teal', 'simple-pay' ),
						'#5ab552' => __( 'Green', 'simple-pay' ),
						'#d34342' => __( 'Red', 'simple-pay' ),
						'#9376b5' => __( 'Purple', 'simple-pay' ),
						'#999999' => __( 'Gray', 'simple-pay' ),
					);
					?>

					<div class="payment-page-background-color-selector">
						<?php foreach ( $colors as $hex => $name ) : ?>
							<div class="simpay-payment-page-background-color <?php echo $background_color === $hex ? 'is-selected' : ''; ?>" >
								<input
									type="radio"
									name="_payment_page_background_color"
									id="payment-page-background-color-<?php echo esc_attr( $hex ); ?>"
									value="<?php echo esc_attr( $hex ); ?>"
									<?php checked( true, $background_color === $hex ); ?>
								/>
								<label for="payment-page-background-color-<?php echo esc_attr( $hex ); ?>" style="background-color: <?php echo $hex; ?>; border-color: <?php echo $hex; ?>">
									<span class="screen-reader-text">
										<?php echo esc_html( $name ); ?>
									</span>
								</label>
							</div>
						<?php endforeach; ?>

						<?php
						$custom_color = array_key_exists( $background_color, $colors )
							? '#cacaca'
							: $background_color;
						?>

						<div class="simpay-payment-page-background-color <?php echo ! array_key_exists( $background_color, $colors ) ? 'is-selected' : ''; ?>" >
							<input
								type="radio"
								name="_payment_page_background_color"
								id="payment-page-background-color-custom"
								<?php checked( false, array_key_exists( $background_color, $colors ) ); ?>
								value="<?php echo esc_attr( $custom_color ); ?>"
							/>
						</div>
					</div>
				</td>
			</tr>
			<tr class="simpay-panel-field">
				<th>
					<strong>
						<?php esc_html_e( 'Form Title & Description', 'simple-pay' ); ?>
					</strong>
				</th>
				<td style="border-bottom: 0; padding-bottom: 0;">
					<?php
					$payment_page_title_description = simpay_get_payment_form_setting(
						$post_id,
						'_payment_page_title_description',
						'yes',
						__unstable_simpay_get_payment_form_template_from_url()
					);
					?>

					<label for="_payment_page_title_description" class="simpay-field-bool">
						<input
							name="_payment_page_title_description"
							type="checkbox"
							id="_payment_page_title_description"
							class="simpay-field simpay-field-checkbox simpay-field simpay-field-checkboxes"
							value="yes"
							<?php checked( true, 'yes' === $payment_page_title_description ); ?>
						/><?php esc_html_e( 'Display the payment form\'s title and description', 'simple-pay' ); ?>
					</label>
				</td>
			</tr>
			<tr class="simpay-panel-field">
				<th>
					<strong>
						<?php esc_html_e( 'Header Image / Logo', 'simple-pay' ); ?>
					</strong>
				</th>
				<td style="border-bottom: 0; padding-bottom: 0;">
					<?php
					$payment_page_image_url = simpay_get_payment_form_setting(
						$post_id,
						'_payment_page_image_url',
						'',
						__unstable_simpay_get_payment_form_template_from_url()
					);

					simpay_print_field(
						array(
							'type'    => 'standard',
							'subtype' => 'hidden',
							'name'    => '_payment_page_image_url',
							'id'      => '_payment_page_image_url',
							'value'   => $payment_page_image_url,
							'class'   => array(
								'simpay-field-text',
								'simpay-field-image-url',
							),
						)
					);
					?>

					<div style="display: flex; align-items: center;">
						<button type="button" class="simpay-media-uploader button button-secondary" style="margin-top: 4px;"><?php esc_html_e( 'Choose Image', 'simple-pay' ); ?></button>

						<button class="simpay-remove-image-preview button button-secondary button-danger button-link" style="margin-left: 8px; display: <?php echo ! empty( $payment_page_image_url ) ? 'block' : 'none'; ?>">
							<?php esc_attr_e( 'Remove', 'simple-pay' ); ?>
						</button>
					</div>

					<div class="simpay-image-preview-wrap <?php echo( empty( $payment_page_image_url ) ? 'simpay-panel-hidden' : '' ); ?>">
						<img src="<?php echo esc_attr( $payment_page_image_url ); ?>" class="simpay-image-preview" />
					</div>
				</td>
			</tr>
			<tr class="simpay-panel-field">
				<th>
					<strong>
						<?php esc_html_e( 'Footer Text', 'simple-pay' ); ?>
					</strong>
				</th>
				<td style="border-bottom: 0;">
					<?php
					$payment_page_footer_text = simpay_get_payment_form_setting(
						$post_id,
						'_payment_page_footer_text',
						'This content is neither created nor endorsed by WP Simple Pay',
						__unstable_simpay_get_payment_form_template_from_url()
					);
					?>

					<label for="_payment_page_footer_text" class="simpay-field-bool">
						<input
							name="_payment_page_footer_text"
							type="text"
							id="_payment_page_footer_text"
							class="simpay-field"
							value="<?php echo esc_attr( $payment_page_footer_text ); ?>"
							style="width: 80%;"
						/>
					</label>
					<div style="height: 8px;"></div>

					<?php

					$payment_page_powered_by = simpay_get_payment_form_setting(
						$post_id,
						'_payment_page_powered_by',
						'no',
						__unstable_simpay_get_payment_form_template_from_url()
					);
					?>

					<label for="_payment_page_powered_by" class="simpay-field-bool">
						<input
							name="_payment_page_powered_by"
							type="checkbox"
							id="_payment_page_powered_by"
							class="simpay-field simpay-field-checkbox simpay-field simpay-field-checkboxes"
							value="yes"
							<?php checked( true, 'yes' === $payment_page_powered_by ); ?>
						/><?php esc_html_e( 'Hide WP Simple Pay branding', 'simple-pay' ); ?>
					</label>
				</td>
			</tr>
		</tbody>
	</table>
	</div>

	<?php
}
add_action(
	'simpay_form_settings_payment_page_panel',
	__NAMESPACE__ . '\\payment_page_display_settings'
);
