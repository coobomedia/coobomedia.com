<?php
/**
 * Taxes: Edit Tax Rate template
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<form>
	<div style="margin-bottom: 18px;">
		<label
			for="simpay-tax-rate-display-name"
			style="display: block; margin-bottom: 4px;"
		>
			<strong>
				<?php esc_html_e( 'Display Name', 'simple-pay' ); ?>
			</strong>
		</label>
		<div>
			<input
				type="text"
				id="simpay-tax-rate-display-name"
				class="simpay-tax-rate-display-name"
				value="{{ data.display_name }}"
				style="width: 100%;"
			/>
		</div>
	</div>

	<div style="margin-bottom: 18px;">
		<label
			for="simpay-tax-rate-percentage"
			style="display: flex; align-items: center; margin-bottom: 4px;"
		>
			<strong><?php esc_html_e( 'Rate', 'simple-pay' ); ?></strong>
			<span class="dashicons dashicons-lock"></span>
		</label>

		<div class="simpay-currency-field">
			<input
				type="number"
				min="0"
				max="100"
				step="any"
				id="simpay-tax-rate-percentage"
				class="simpay-field-amount"
				value="{{ data.percentage }}"
				style="border-top-right-radius: 0; border-bottom-right-radius: 0;"
				readonly
			/>

			<div
				class="simpay-price-currency-symbol simpay-currency-symbol simpay-currency-symbol-right"
				style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
			>
				%
			</div>

			<span style="margin-left: 8px;">{{ data.calculation }}</span>
		</div>

		<div style="font-style: italic; color: #555; margin-top: 4px;">
			<?php
			esc_html_e(
				'Existing tax rate percentages cannot be edited. Create a new tax rate to make other changes.',
				'simple-pay'
			);
			?>
		</div>
	</div>

	<div
		style="margin: 0 -16px -16px; padding: 16px; display: flex; justify-content: flex-end; background: #fcfcfc; border-top: 1px solid #dfdfdf;"
	>
		<button type="submit" class="button button-primary">
			<?php esc_html_e( 'Update Tax Rate', 'simple-pay' ); ?>
		</button>
	</div>
</form>
