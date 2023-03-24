<?php
/**
 * Routines: Payment Form "Display Type" meta.
 *
 * @package SimplePay\Core\Utils\Migrations
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Utils\Migrations\Routines;

use SimplePay\Core\Utils\Migrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Payment_Form_Display_Type class
 *
 * @since 4.0.0
 */
class Payment_Form_Display_Type extends Migrations\Bulk_Migration {

	/**
	 * Runs the migration.
	 *
	 * @since 4.0.0
	 */
	public function run() {
		$forms = get_posts(
			array(
				'post_type'      => 'simple-pay',
				'posts_per_page' => -1,
				'meta_key'       => '_form_display_type',
				'meta_compare'   => 'NOT EXISTS',
			)
		);

		if ( empty( $forms ) ) {
			return $this->complete();
		}

		$upgraded = false;

		if ( $forms ) {
			foreach ( $forms as $form ) {
				if ( ! get_post_meta( $form->ID, '_form_display_type', true ) ) {
					$upgraded = update_post_meta( $form->ID, '_form_display_type', 'stripe_checkout' );
				}
			}
		}

		if ( false !== $upgraded ) {
			$this->complete();
		}
	}

}
