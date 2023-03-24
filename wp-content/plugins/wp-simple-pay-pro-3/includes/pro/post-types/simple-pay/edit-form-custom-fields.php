<?php
/**
 * Simple Pay: Edit form custom fields
 *
 * @package SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form;

use SimplePay\Core\Utils;
use SimplePay\Core\Post_Types\Simple_Pay\Edit_Form as Core_Edit_Form;
use function SimplePay\Pro\Payment_Methods\get_payment_method;
use function SimplePay\Pro\Payment_Methods\get_payment_methods;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get custom field option group labels.
 *
 * @since 3.8.0
 *
 * @return array Group label names.
 */
function get_custom_field_type_groups() {
	return Core_Edit_Form\get_custom_field_type_groups();
}

/**
 * Get the available custom field types.
 *
 * @since 3.8.0
 *
 * @return array $fields Custom fields.
 */
function get_custom_field_types() {
	return Core_Edit_Form\get_custom_field_types();
}

/**
 * Get a grouped list of custom field options.
 *
 * @since 3.8.0
 *
 * @param array $options Flat list of options.
 * @return array $options Grouped list of options.
 */
function get_custom_fields_grouped( $options = array() ) {
	return Core_Edit_Form\get_custom_fields_grouped();
}

/**
 * Adds "Custom Fields" Payment Form settings tab content.
 *
 * @since 3.8.0
 *
 * @param int $post_id Current Payment Form ID.
 */
function add_custom_fields( $post_id ) {
	$field_groups = get_custom_fields_grouped();
	$field_types  = get_custom_field_types();

	if ( empty( $field_groups ) ) {
		return;
	}

	$fields = simpay_get_payment_form_setting(
		$post_id,
		'fields',
		array(
			array(
				'type'  => 'email',
				'label' => 'Email Address',
			),
			array(
				'type'  => 'plan_select',
				'label' => 'Price Options',
			),
			array(
				'type'  => 'card',
				'label' => 'Payment Method',
			),
			array(
				'type' => 'checkout_button',
			),
		),
		__unstable_simpay_get_payment_form_template_from_url()
	);

	wp_nonce_field( 'simpay_custom_fields_nonce', 'simpay_custom_fields_nonce' );
	?>

<table>
	<tbody class="simpay-panel-section">
		<tr class="simpay-panel-field">
			<th>
				<label for="custom-field-select">
					<?php esc_html_e( 'Form Fields', 'simple-pay' ); ?>
				</label>
			</th>
			<td style="border-bottom: 0;">
				<div class="toolbar toolbar-top">
					<select name="simpay_field_select" id="custom-field-select" class="simpay-field-select">
						<option value=""><?php esc_html_e( 'Choose a field&hellip;', 'simple-pay' ); ?></option>
							<?php foreach ( $field_groups as $group => $options ) : ?>
								<optgroup label="<?php echo esc_attr( $group ); ?>">
									<?php
									foreach ( $options as $option ) :
										if ( ! isset( $option['active'] ) || ! $option['active'] ) :
											continue;
										endif;

										$disabled   = ! isset( $option['repeatable'] ) || ( isset( $fields[ $option['type'] ] ) && ! $option['repeatable'] );
										$repeatable = isset( $option['repeatable'] ) && true === $option['repeatable'];
										?>
										<option
											value="<?php echo esc_attr( $option['type'] ); ?>"
											data-repeatable="<?php echo esc_attr( $repeatable ? 'true' : 'false' ); ?>"
											<?php disabled( true, $disabled ); ?>
										>
											<?php echo esc_html( $option['label'] ); ?>
										</option>
									<?php endforeach; ?>
								</optgroup>
							<?php endforeach; ?>
						</optgroup>
					</select>

					<button type="button" class="button add-field">
						<?php esc_html_e( 'Add Field', 'simple-pay' ); ?>
					</button>
				</div>
			</td>
		</tr>
		<tr class="simpay-panel-field">
			<td>
				<div id="simpay-custom-fields-wrap" class="panel simpay-metaboxes-wrapper">
					<div class="simpay-custom-fields simpay-metaboxes ui-sortable">
						<?php
						foreach ( $fields as $k => $field ) :
							// Don't render settings for custom field types that don't exist,
							// possibly from an upgrade or downgrade.
							if ( ! isset( $field_types[ $field['type'] ] ) ) :
								continue;
							endif;

							$counter = $k + 1;

							echo get_custom_field( $field['type'], $counter, $field, $post_id ); // WPCS: XSS okay.
						endforeach;
						?>
					</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>

	<?php
	/** This filter is documented in includes/core/post-types/simple-pay/edit-form-custom-fields.php */
	do_action( 'simpay_admin_after_custom_fields' );

	/**
	 * Allows further output after "Custom Fields" Payment Form
	 * settings tab content.
	 *
	 * @since 3.0.0
	 */
	do_action( 'simpay_custom_field_panel' );
}
add_action(
	'simpay_form_settings_meta_form_display_panel',
	__NAMESPACE__ . '\\add_custom_fields'
);

remove_action(
	'simpay_form_settings_meta_form_display_panel',
	'SimplePay\Core\Post_Types\Simple_Pay\Edit_Form\__unstable_add_custom_fields'
);

/**
 * Retrieves the markup for a custom field.
 *
 * @since 3.8.0
 * @since 4.6.0 Added $post_id parameter.
 *
 * @param int   $type    Custom field type.
 * @param int   $counter Custom field counter.
 * @param array $field   Custom field arguments.
 * @param int   $post_id Payment form ID.
 * @return string Custom field markup.
 */
function get_custom_field( $type, $counter, $field, $post_id ) {
	$field_types = get_custom_field_types();

	// Generate a label.
	$accordion_label = '';

	if ( isset( $field['label'] ) && ! empty( $field['label'] ) ) {
		$accordion_label = $field['label'];
	} elseif ( isset( $field['placeholder'] ) && ! empty( $field['placeholder'] ) ) {
		$accordion_label = $field['placeholder'];
	} else {
		$accordion_label = $field_types[ $type ]['label'];
	}

	switch ( $type ) {
		case 'total_amount':
			$accordion_label = esc_html__( 'Amount Breakdown', 'simple-pay' );
			break;
		case 'card':
			$accordion_label_base = $accordion_label;
			$accordion_label     .= sprintf(
				'<div style="margin-left: auto; display: flex;">%s</div>',
				__unstable_get_payment_methods_accordion_label_icons()
			);
			break;
		default:
			$accordion_label = $accordion_label;
	}

	// Find the template.
	$admin_field_template = SIMPLE_PAY_INC . 'pro/post-types/simple-pay/edit-form-custom-fields/custom-fields-' . simpay_dashify( $type ) . '-html.php';

	/**
	 * Filters the template for outputting a Payment Form's custom field.
	 *
	 * @since 3.0.0
	 *
	 * @param string $admin_field_template Field path.
	 */
	$admin_field_template = apply_filters( 'simpay_admin_' . esc_attr( $type ) . '_field_template', $admin_field_template );

	$uid = isset( $field['uid'] ) ? $field['uid'] : $counter;

	$type_label = $field_types[ $type ]['label'];

	ob_start();
	?>

	<div
		id="simpay-custom-field-<?php echo esc_attr( simpay_dashify( $type ) . $counter ); ?>-postbox"
		class="postbox closed simpay-field-metabox simpay-metabox simpay-custom-field-<?php echo simpay_dashify( $type ); ?>"
		data-type="<?php echo esc_attr( $type ); ?>"
		aria-expanded="false"
	>
		<button type="button" class="simpay-handlediv">
			<span class="screen-reader-text">
				<?php
				printf(
					/* translators: %s Custom field label */
					__( 'Toggle custom field: %s', 'simple-pay' ),
					strip_tags( $accordion_label )
				);
				?>
			</span>
			<span class="toggle-indicator" aria-hidden="true"></span>
		</button>

		<h2 class="simpay-hndle ui-sortable-handle">
			<span class="custom-field-dashicon dashicons <?php echo 'payment_button' !== $type ? 'dashicons-menu-alt2" style="cursor: move;' : ''; ?>"></span>

			<strong>
				<?php if ( 'payment_request_button' === $type ) : ?>
					<span
						class="dashicons dashicons-warning simpay-show-if"
						data-if="_tax_status"
						data-is="automatic"
						style="margin-right: 5px;"
					></span>
				<?php endif; ?>

				<?php echo $accordion_label; ?>
			</strong>

			<?php
			if (
				( 'card' !== $type && $type_label !== $accordion_label ) ||
				( 'card' === $type && $accordion_label_base !== $type_label )
			) :
				?>
			<div class="simpay-field-type">
				<?php echo esc_html( $type_label ); ?>
			</div>
			<?php endif; ?>
			<?php if ( 'payment_request_button' === $type ) : ?>
				<div
					class="simpay-field-type simpay-show-if"
					data-if="_tax_status"
					data-is="automatic"
					style="display: flex; align-items: center;"
				>
					<?php
					esc_html_e(
						'Incompatible with automatic taxes',
						'simple-pay'
					);
					?>
				</div>
			<?php endif; ?>
		</h2>

		<div class="simpay-field-data simpay-metabox-content inside">
			<table>
				<?php
				if ( file_exists( $admin_field_template ) ) :
					simpay_print_field(
						array(
							'type'    => 'standard',
							'subtype' => 'hidden',
							'name'    => '_simpay_custom_field[' . $type . '][' . $counter . '][id]',
							'id'      => 'simpay-' . $type . '-' . $counter . '-id',
							'value'   => ! empty( $field['id'] ) ? $field['id'] : $uid,
						)
					);

					simpay_print_field(
						array(
							'type'    => 'standard',
							'subtype' => 'hidden',
							'id'      => 'simpay-' . $type . '-' . $counter . '-uid',
							'class'   => array( 'field-uid' ),
							'name'    => '_simpay_custom_field[' . $type . '][' . $counter . '][uid]',
							'value'   => $uid,
						)
					);

					simpay_print_field(
						array(
							'type'    => 'standard',
							'subtype' => 'hidden',
							'id'      => 'simpay-' . $type . '-' . $counter . '-order',
							'class'   => array( 'field-order' ),
							'name'    => '_simpay_custom_field[' . $type . '][' . $counter . '][order]',
							'value'   => isset( $field['order'] ) ? $field['order'] : $counter,
						)
					);

					include $admin_field_template;

					/**
					 * Allows further output after a specific custom field type.
					 *
					 * @since 3.0.0
					 */
					do_action( 'simpay_after_' . $type . '_meta' );
				endif;
				?>
			</table>

			<div class="simpay-metabox-content-actions">
				<?php if ( 'plan_select' !== $type ) : ?>
				<button type="button" class="button-link simpay-remove-field-link">
					<?php esc_html_e( 'Remove', 'simple-pay' ); ?>
				</button>
				<?php else : ?>
					<div></div>
				<?php endif; ?>

				<div class="simpay-metabox-content-actions__field-id">
					<label for="<?php echo esc_attr( 'simpay-' . $type . '-' . $counter . '-' . $uid ); ?>">
						<?php esc_html_e( 'Field ID', 'simple-pay' ); ?>:
					</label>

					<input type="text" value="<?php echo absint( $uid ); ?>" id="<?php echo esc_attr( 'simpay-' . $type . '-' . $counter . '-' . $uid ); ?>" readonly />

					<a href="<?php echo esc_url( simpay_docs_link( 'Find out more about the field ID.', 'custom-form-fields#field-id', 'payment-form-field-settings', true ) ); ?>" class="simpay-docs-icon" target="_blank" rel="noopener noreferrer">
						<span class="dashicons dashicons-editor-help"></span>
						<span class="screen-reader-text"><?php esc_html_e( 'Find out more about the field ID.', 'simple-pay' ); ?></span>
					</a>
				</div>
			</div>
		</div>
	</div>

	<?php
	return ob_get_clean();
}

/**
 * Ensures Payment Forms have required fields and remove unnecessary fields.
 *
 * @since 3.8.0
 *
 * @param array  $fields Payment Form custom fields.
 * @param int    $form_id Payment Form ID.
 * @param string $form_display_type Payment Form display type.
 * @return array
 */
function add_missing_custom_fields( $fields, $form_id, $form_display_type = 'embedded' ) {
	return simpay_payment_form_add_missing_custom_fields(
		$fields,
		$form_id,
		$form_display_type
	);
}

/**
 * Generate a label for the "Payment Methods" custom field based on the enabled
 * Payment Methods.
 *
 * @since 4.4.4
 *
 * @return string
 */
function __unstable_get_payment_methods_accordion_label_icons() {
	global $post;

	$saved_payment_methods = simpay_get_payment_form_setting(
		$post->ID,
		'payment_methods',
		array(),
		__unstable_simpay_get_payment_form_template_from_url()
	);

	$payment_methods = get_payment_methods();

	foreach ( $payment_methods as $payment_method ) {
		$icons[] = sprintf(
			'<span class="simpay-payment-method-title-icon-%s" style="display: %s; align-items: center; margin-left: 4px;">%s</span>',
			$payment_method->id,
			in_array(
				$payment_method->id,
				array_keys( $saved_payment_methods ),
				true
			)
				? 'flex'
				: 'none',
			$payment_method->icon_sm
		);
	}

	return implode( '', $icons );
}
