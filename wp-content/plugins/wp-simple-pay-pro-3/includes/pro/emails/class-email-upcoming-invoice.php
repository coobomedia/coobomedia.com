<?php
/**
 * Emails: Upcoming Invoice
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
 * Email_Upcoming_Invoice class
 *
 * @since 4.0.0
 */
class Email_Upcoming_Invoice extends Email {

	/**
	 * Determines if the email is enabled, and should send.
	 *
	 * Overrides the default implementation because this email was enabled by
	 * default in 3.7.0
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return 'yes' === simpay_get_setting(
			sprintf(
				'email_%s',
				$this->id
			),
			'yes'
		);
	}

	/**
	 * Registers a setting to enable or disable the email.
	 *
	 * @since 4.0.0
	 *
	 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
	 */
	protected function register_settings_enable( $settings ) {
		$subscription_management_link = Settings\get_url(
			array(
				'section'    => 'customers',
				'subsection' => 'subscription-management',
				'setting'    => 'subscription_management',
			)
		);

		$settings->add(
			new Settings\Setting_Checkbox(
				array(
					'id'          => sprintf( 'email_%s', $this->id ),
					'section'     => 'emails',
					'subsection'  => $this->id,
					'label'       => $this->label,
					'input_label' => $this->description,
					'value'       => $this->is_enabled() ? 'yes' : 'no',
					'priority'    => 10,
					'schema'      => array(
						'type'    => 'string',
						'enum'    => array( 'yes', 'no' ),
						'default' => 'yes',
					),
					'toggles'     => array(
						'value'    => 'yes',
						'settings' => array(
							sprintf( 'email_%s_to', $this->id ),
							sprintf( 'email_%s_subject', $this->id ),
							sprintf( 'email_%s_body', $this->id ),
						),
					),
					'description' => wpautop(
						wp_kses(
							sprintf(
								/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
								__(
									'Email is scheduled based on the %1$supcoming renewal events%2$s setting in Stripe.',
									'simple-pay'
								),
								'<a href="https://dashboard.stripe.com/settings/billing/automatic" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
								Utils\get_external_link_markup() . '</a>'
							) . '<br />' .
							sprintf(
								/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
								__(
									'Customers will be able to update their subscription based on the %1$ssubscription management%2$s setting.',
									'simple-pay'
								),
								'<a href="' . esc_url( $subscription_management_link ) . '">',
								'</a>'
							),
							array(
								'br'   => true,
								'a'    => array(
									'href'   => true,
									'class'  => true,
									'target' => true,
									'rel'    => true,
								),
								'span' => array(
									'class' => 'screen-reader-text',
								),
							)
						)
					),
				)
			)
		);
	}

	/**
	 * Registers the "Send To" setting.
	 *
	 * @since 4.0.0
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
						'type' => 'string',
					),
					'readonly'   => true,
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
