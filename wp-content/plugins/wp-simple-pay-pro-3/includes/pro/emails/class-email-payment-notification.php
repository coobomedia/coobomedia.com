<?php
/**
 * Emails: Payment Notification
 *
 * @package SimplePay\Pro\Emails
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Emails;

use SimplePay\Core\Utils;
use SimplePay\Core\Settings;
use SimplePay\Core\Payments\Payment_Confirmation;

use SimplePay\Pro\Payments\Payment_Confirmation as Pro_Payment_Confirmation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email_Payment_Notification class
 *
 * @since 4.0.0
 */
class Email_Payment_Notification extends Email {

	/**
	 * Registers the "Send To" setting.
	 *
	 * @since 4.4.2
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
					'value'      => $this->get_setting( 'to' ),
					'classes'    => array(
						'regular-text',
					),
					'priority'   => 20,
					'schema'     => array(
						'type'    => 'string',
						'default' => get_option( 'admin_email', '' ),
					),
				)
			)
		);
	}

	/**
	 * Registers the "Message" setting.
	 *
	 * @since 4.0.0
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

						$pro_tags = Pro_Payment_Confirmation\Template_Tags\__unstable_get_tags_and_descriptions();
						unset( $pro_tags['update-payment-method-url'] );

						Payment_Confirmation\Template_Tags\__unstable_print_tag_list(
							'',
							array_merge(
								Payment_Confirmation\Template_Tags\__unstable_get_tags_and_descriptions(),
								$pro_tags
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
