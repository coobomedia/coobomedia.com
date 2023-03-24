<?php
/**
 * Taxes: Tax Rate
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

<td>
	{{ data.display_name }}
	<div class="simpay-tax-rate-actions">
		<button class="button-link edit">
			<?php esc_html_e( 'Edit', 'simple-pay' ); ?>
		</button>
		|
		<button class="button-link button-link-delete remove">
			<?php esc_html_e( 'Remove', 'simple-pay' ); ?>
		</button>
	</div>

	<input
		type="hidden"
		name="simpay_settings[tax_rates][{{ data.instanceId }}][id]"
		value="{{ data.id }}"
	/>

	<input
		type="hidden"
		name="simpay_settings[tax_rates][{{ data.instanceId }}][display_name]"
		value="{{ data.display_name }}"
	/>

	<input
		type="hidden"
		name="simpay_settings[tax_rates][{{ data.instanceId }}][percentage]"
		value="{{ data.percentage }}"
	/>

	<input
		type="hidden"
		name="simpay_settings[tax_rates][{{ data.instanceId }}][calculation]"
		value="{{ data.calculation }}"
	/>
</td>
<td>
	{{ data.percentage }}% {{ data.calculation }}
</td>