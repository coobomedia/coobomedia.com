<?php
/**
 * Emails: Invoice confirmation
 *
 * @package SimplePay\Pro\Emails
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Pro\Emails;

use SimplePay\Core\Settings;
use SimplePay\Core\Payments\Payment_Confirmation;
use SimplePay\Pro\Payments\Payment_Confirmation as Pro_Payment_Confirmation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email_Invoice_Confirmation class
 *
 * @since 4.4.6
 */
class Email_Invoice_Confirmation extends Email {

	/**
	 * Determines if the email is enabled, and should send.
	 *
	 * Overrides the default determination and uses the Payment Receipt email
	 * enabled status as the fallback value.
	 *
	 * @since 4.4.6
	 *
	 * @return bool
	 */
	public function is_enabled() {
		$license        = simpay_get_license();
		$lowest_license = current( $this->licenses );

		if ( false === $license->is_pro( $lowest_license, '>=' ) ) {
			return false;
		}

		return 'yes' === simpay_get_setting(
			sprintf(
				'email_%s',
				$this->id
			),
			simpay_get_setting( 'email_payment-confirmation', 'yes' )
		);
	}

	/**
	 * Registers the "Send To" setting.
	 *
	 * @since 4.4.6
	 *
	 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
	 */
	protected function register_settings_to( $settings ) {
		$settings->add(
			new Settings\Setting_Input(
				array(
					'id'         => sprintf( 'email_%s_to', $this->id ),
					'section'    => 'emails',
					'subsection' => $this->id,
					'label'      => esc_html_x(
						'Send To',
						'setting label',
						'simple-pay'
					),
					'value'      => esc_html__( 'Customer email address', 'simple-pay' ),
					'classes'    => array(
						'regular-text',
						'readonly',
					),
					'priority'   => 20,
					'schema'     => array(
						'type'    => 'string',
					),
					'readonly'   => true,
				)
			)
		);
	}

	/**
	 * Registers the "Message" setting.
	 *
	 * @since 4.4.6
	 *
	 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
	 */
	protected function register_settings_body( $settings ) {
		$settings->add(
			new Settings\Setting(
				array(
					'id'         => sprintf( 'email_%s_body', $this->id ),
					'section'    => 'emails',
					'subsection' => $this->id,
					'label'      => esc_html_x(
						'Message',
						'setting label',
						'simple-pay'
					),
					'output'     => function() {
						wp_editor(
							$this->get_setting( 'body' ),
							sprintf( 'email_%s_body', $this->id ),
							array(
								'textarea_name' => sprintf(
									'simpay_settings[email_%s_body]',
									$this->id
								),
								'textarea_rows' => 10,
							)
						);

						Payment_Confirmation\Template_Tags\__unstable_print_tag_list(
							'',
							array_merge(
								Payment_Confirmation\Template_Tags\__unstable_get_tags_and_descriptions(),
								Pro_Payment_Confirmation\Template_Tags\__unstable_get_tags_and_descriptions()
							)
						);
					},
					'priority'   => 40,
					'schema'     => array(
						'type' => 'string',
					),
				)
			)
		);
	}

}
