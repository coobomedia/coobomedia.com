<?php
/**
 * License Management
 *
 * @package SimplePay\Pro\License_Management
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.5.0
 */

namespace SimplePay\Pro\License_Management;

use SimplePay\Core\Settings;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Show a notice in the plugin list if there is no license key.
 *
 * @since 3.5.0
 */
function plugin_list_show_empty_license_notice() {
	$license_data = get_option( 'simpay_license_data', false );

	if ( $license_data && 'valid' === $license_data->license ) {
		return;
	}

	wp_add_inline_script(
		'updates',
		sprintf(
			implode(
				"\n",
				array(
					'( function() {',
					'  var row = document.querySelector( \'[data-plugin="wp-simple-pay-pro-3/simple-pay.php"]\' );',
					'  if ( row ) {',
					'    row.classList.add( "update" );',
					'    document.querySelector( \'.simpay-plugin-update-license td\' ).colSpan = row.cells.length;',
					'  }',
					'} )();',
				)
			)
		)
	);
	?>

<tr class="simpay-plugin-update-license plugin-update-tr active">
	<td colspan="3" class="plugin-update colspanchange">
		<div class="notice inline notice-warning notice-alt">
			<p>
				<strong><?php esc_html_e( 'A valid license key is required for access to automatic updates.', 'simple-pay' ); ?></strong>
			</p>

			<p>
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. %3$s Opening anchor tag, do not translate. */
					__( 'Retrieve your license key from %1$syour WP Simple Pay account%3$s or purchase receipt email then %2$sactivate your website%3$s.', 'simple-pay' ),
					sprintf( '<a href="%s" target="_blank" rel="noopener noreferrer">', simpay_ga_url( 'https://wpsimplepay.com/my-account/licenses/', 'plugin-listing-link', 'activate your website' ) ),
					sprintf(
						'<a href="%s">',
						Settings\get_url(
							array(
								'section'    => 'general',
								'subsection' => 'license',
							)
						)
					),
					'</a>'
				)
			);
			?>
			</p>
		</div>
	</td>
</tr>

	<?php
}
add_action(
	'after_plugin_row_' . plugin_basename( SIMPLE_PAY_MAIN_FILE ),
	__NAMESPACE__ . '\\plugin_list_show_empty_license_notice',
	5
);
