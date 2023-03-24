<?php
/**
 * Forms field: Address
 *
 * @package SimplePay\Pro\Forms\Fields
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Pro\Forms\Fields;

use SimplePay\Core\Abstracts\Custom_Field;
use SimplePay\Core\i18n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Address class.
 *
 * @since 3.0.0
 */
class Address extends Custom_Field {

	/**
	 * Prints HTML for field on frontend.
	 *
	 * @since 3.0.0
	 *
	 * @param array $settings Field settings.
	 * @return string
	 */
	public static function print_html( $settings ) {
		$id       = self::get_id_attr();
		$required = isset( $settings['required'] );

		// Billing container label.
		$billing_address_label = isset( $settings['billing-container-label'] )
			? $settings['billing-container-label']
			: '';

		// Shipping toggle.
		$collect_shipping = isset( $settings['collect-shipping'] ) && 'yes' === $settings['collect-shipping'];

		// Shipping container label.
		$shipping_address_label = isset( $settings['shipping-container-label'] )
			? $settings['shipping-container-label']
			: '';

		$tax_status = get_post_meta( self::$form->id, '_tax_status', true );

		if ( 'automatic' === $tax_status ) {
			$fields = array(
				'country',
			);
		} else {
			$fields = array(
				'country',
				'street',
				'city',
				'state',
				'zip',
			);

			/**
			 * Allows the address field order to be altered.
			 *
			 * @since 4.6.2
			 *
			 * @param array<string> $fields Address field order.
			 */
			$fields = apply_filters(
				'simpay_address_field_order',
				$fields
			);
		}

		ob_start();
		?>

		<fieldset class="simpay-form-control simpay-address-container simpay-billing-address-container">
			<?php if ( ! empty( $billing_address_label ) ) : ?>
			<legend class="simpay-address-billing-container-label simpay-label-wrap">
				<?php
				echo esc_html( $billing_address_label );

				if ( false === $required ) :
					echo self::get_optional_indicator(); // WPCS: XSS okay.
				endif;
				?>
			</legend>
			<?php endif; ?>

			<div class="simpay-address-container">
				<?php
				foreach ( $fields as $field ) :
					$field_id          = $id . '-billing-' . $field;
					$field_default     = self::get_default_value( 'default-' . $field );
					$field_placeholder = isset( $settings[ 'placeholder-' . $field ] )
						? $settings[ 'placeholder-' . $field ]
						: '';

					switch ( $field ) :
						case 'street':
							$field_name = 'simpay_billing_address_line1';
							break;
						case 'zip':
							$field_name = 'simpay_billing_address_postal_code';
							break;
						default:
							$field_name = 'simpay_billing_address_' . $field;
					endswitch;

					switch ( $field ) :
						case 'country':
							self::get_country_field( $field, $field_id, $field_name, $field_default, $field_placeholder );
							break;
						default:
							self::get_field( $field, $field_id, $field_name, $field_default, $field_placeholder );
					endswitch;
				endforeach;
				?>
			</div>
		</fieldset>

		<?php if ( true === $collect_shipping ) : ?>
		<div
			class="simpay-form-control simpay-same-address-toggle-container"
			<?php if ( 'automatic' === $tax_status ) : ?>
				style="display: none;"
			<?php endif; ?>
		>
			<div class="simpay-same-address-toggle-wrap simpay-field-wrap">
				<label for="<?php echo esc_attr( $id ); ?>-same-address-toggle">
					<input type="checkbox" name="simpay_same_billing_shipping" id="<?php echo esc_attr( $id ); ?>-same-address-toggle" class="simpay-same-address-toggle" checked="checked" />
					<?php esc_html_e( 'Same billing & shipping info', 'simple-pay' ); ?>
				<label>
			</div>
		</div>

		<fieldset class="simpay-form-control simpay-address-container simpay-shipping-address-container" style="display: none;">
			<legend class="simpay-address-shipping-container-label simpay-label-wrap">
				<?php
				echo esc_html( $shipping_address_label );

				if ( false === $required ) :
					echo self::get_optional_indicator(); // WPCS: XSS okay.
				endif;
				?>
			</legend>

			<div class="simpay-address-container">
				<?php
				foreach ( $fields as $field ) :
					$field_id          = $id . '-shipping-' . $field;
					$field_default     = self::get_default_value( 'default-' . $field );
					$field_placeholder = isset( $settings[ 'placeholder-' . $field ] )
						? $settings[ 'placeholder-' . $field ]
						: '';

					switch ( $field ) :
						case 'street':
							$field_name = 'simpay_shipping_address_line1';
							break;
						case 'zip':
							$field_name = 'simpay_shipping_address_postal_code';
							break;
						default:
							$field_name = 'simpay_shipping_address_' . $field;
					endswitch;

					switch ( $field ) :
						case 'country':
							self::get_country_field( $field, $field_id, $field_name, $field_default, $field_placeholder );
							break;
						default:
							self::get_field( $field, $field_id, $field_name, $field_default, $field_placeholder );
					endswitch;
				endforeach;
				?>
			</div>
		</fieldset>
		<?php endif; ?>

		<?php
		return ob_get_clean();
	}

	/**
	 * Returns a standard address field.
	 *
	 * @since 3.9.0
	 *
	 * @param string $field             Field type.
	 * @param string $field_id          Field ID.
	 * @param string $field_name        Field name.
	 * @param string $field_default     Field default.
	 * @param string $field_placeholder Field placeholder.
	 */
	private static function get_field(
		$field,
		$field_id,
		$field_name,
		$field_default,
		$field_placeholder
	) {
		$required = isset( self::$settings['required'] );
		?>

		<div class="simpay-form-control simpay-address-<?php echo esc_attr( $field ); ?>-container">
			<?php echo self::get_label( 'label-' . $field ); // WPCS: XSS okay. ?>
			<div class="simpay-<?php echo esc_attr( $field ); ?>-wrap simpay-field-wrap">
				<input
					type="text"
					name="<?php echo esc_attr( $field_name ); ?>"
					id="<?php echo esc_attr( $field_id ); ?>"
					class="simpay-address-<?php echo esc_attr( $field ); ?>"
					value="<?php echo esc_attr( $field_default ); ?>"
					placeholder="<?php echo esc_attr( $field_placeholder ); ?>"
					maxlength="500"
					<?php if ( true === $required ) : ?>
						required
					<?php endif; ?>
				/>
			</div>
		</div>

		<?php
	}

	/**
	 * Returns a country address field.
	 *
	 * @since 3.9.0
	 *
	 * @param string $field             Field type.
	 * @param string $field_id          Field ID.
	 * @param string $field_name        Field name.
	 * @param string $field_default     Field default.
	 * @param string $field_placeholder Field placeholder.
	 */
	private static function get_country_field(
		$field,
		$field_id,
		$field_name,
		$field_default,
		$field_placeholder
	) {
		$required = isset( self::$settings['required'] );

		$tax_status       = get_post_meta( self::$form->id, '_tax_status', true );
		$countries        = i18n\get_countries();
		$selected_country = self::get_default_value( 'default-country', 'US' );
		?>

		<div class="simpay-form-control simpay-address-<?php echo esc_attr( $field ); ?>-container">
			<?php echo self::get_label( 'label-' . $field ); // WPCS: XSS okay. ?>
			<div class="simpay-<?php echo esc_attr( $field ); ?>-wrap simpay-field-wrap">
				<select
					name="<?php echo esc_attr( $field_name ); ?>"
					id="<?php echo esc_attr( $field_id ); ?>"
					class="simpay-address-<?php echo esc_attr( $field ); ?>"
					<?php if ( true === $required ) : ?>
						required
					<?php endif; ?>
				>
					<option value="">
						<?php esc_html_e( 'Select a country&hellip;', 'simple-pay' ); ?>
					</option>
					<?php foreach ( $countries as $country_code => $country ) : ?>
					<option
						value="<?php echo esc_attr( $country_code ); ?>"
						<?php
						if ( 'automatic' !== $tax_status ) :
							selected( $country_code, $selected_country );
						endif;
						?>
					>
						<?php echo esc_html( $country ); ?>
					</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<?php
	}
}
