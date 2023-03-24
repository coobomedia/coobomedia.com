<?php
/**
 * Emails: Payment Confirmation
 *
 * @package SimplePay\Pro\Emails
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Emails;

use SimplePay\Core\Settings;
use SimplePay\Core\Payments\Payment_Confirmation;

use SimplePay\Pro\Payments\Payment_Confirmation as Pro_Payment_Confirmation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email_Payment_Confirmation class
 *
 * @since 4.0.0
 */
class Email_Payment_Confirmation extends Email {

	/**
	 * Registers a setting to enable or disable the email.
	 *
	 * @since 4.0.0
	 *
	 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
	 */
	protected function register_settings_enable( $settings ) {
		$resend_confirmation_url = Settings\get_url(
			array(
				'section'    => 'emails',
				'subsection' => 'emails-tools',
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
							sprintf( 'email_%s_body_one_time', $this->id ),
							sprintf( 'email_%s_body_subscription', $this->id ),
							sprintf( 'email_%s_body_trial', $this->id ),
						),
					),
					'description' => wpautop(
						wp_kses(
							sprintf(
								/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
								__(
									'Use the %1$sResend Payment Receipt tool%2$s to resend a receipt for a previous purchase.',
									'simple-pay'
								),
								'<a href="' . esc_url( $resend_confirmation_url ) . '">',
								'</a>'
							),
							array(
								'a' => array(
									'href'   => true,
									'class'  => true,
									'target' => true,
									'rel'    => true,
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
	 * @since 4.0.0
	 *
	 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
	 */
	protected function register_settings_body( $settings ) {
		// One Time Payment.
		$settings->add(
			new Settings\Setting(
				array(
					'id'         => sprintf( 'email_%s_body_one_time', $this->id ),
					'section'    => 'emails',
					'subsection' => $this->id,
					'label'      => esc_html_x(
						'One Time Payment',
						'settings subsection label',
						'simple-pay'
					),
					'output'     => function() {
						wp_editor(
							$this->get_body_setting_or_default( 'one_time' ),
							sprintf( 'email_%s_body_one_time', $this->id ),
							array(
								'textarea_name' => sprintf(
									'simpay_settings[email_%s_body_one_time]',
									$this->id
								),
								'textarea_rows' => 10,
							)
						);

						Payment_Confirmation\Template_Tags\__unstable_print_tag_list(
							'',
							Payment_Confirmation\Template_Tags\__unstable_get_tags_and_descriptions()
						);
					},
					'priority'   => 40,
					'schema'     => array(
						'type' => 'string',
					),
				)
			)
		);

		if ( false === simpay_subscriptions_enabled() ) {
			return;
		}

		// Subscription.
		$settings->add(
			new Settings\Setting(
				array(
					'id'         => sprintf( 'email_%s_body_subscription', $this->id ),
					'section'    => 'emails',
					'subsection' => $this->id,
					'label'      => esc_html_x(
						'Subscription',
						'settings subsection label',
						'simple-pay'
					),
					'output'     => function() {
						wp_editor(
							$this->get_body_setting_or_default( 'subscription' ),
							sprintf( 'email_%s_body_subscription', $this->id ),
							array(
								'textarea_name' => sprintf(
									'simpay_settings[email_%s_body_subscription]',
									$this->id
								),
								'textarea_rows' => 10,
							)
						);

						$pro_tags = Pro_Payment_Confirmation\Template_Tags\__unstable_get_tags_and_descriptions();
						unset( $pro_tags['trial-end-date'] );

						Payment_Confirmation\Template_Tags\__unstable_print_tag_list(
							'',
							array_merge(
								Payment_Confirmation\Template_Tags\__unstable_get_tags_and_descriptions(),
								$pro_tags
							)
						);
					},
					'priority'   => 60,
					'schema'     => array(
						'type' => 'string',
					),
				)
			)
		);


		if ( false === simpay_get_license()->is_enhanced_subscriptions_enabled() ) {
			return;
		}

		// Subscription with Trial.
		$settings->add(
			new Settings\Setting(
				array(
					'id'         => sprintf( 'email_%s_body_trial', $this->id ),
					'section'    => 'emails',
					'subsection' => $this->id,
					'label'      => esc_html_x(
						'Subscription with Trial',
						'settings subsection label',
						'simple-pay'
					),
					'output'     => function() {
						wp_editor(
							$this->get_body_setting_or_default( 'trial' ),
							sprintf( 'email_%s_body_trial', $this->id ),
							array(
								'textarea_name' => sprintf(
									'simpay_settings[email_%s_body_trial]',
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
					'priority'   => 80,
					'schema'     => array(
						'type' => 'string',
					),
				)
			)
		);
	}

	/**
	 * Returns the stored "Message" setting, or uses the Payment Confirmation
	 * default messaging.
	 *
	 * @since 4.0.0
	 *
	 * @param string $type Body type (one_time, subscription, trial).
	 * @return string Email message body.
	 */
	public function get_body_setting_or_default( $type ) {
		$subscription_management = simpay_get_setting(
			'subscription_management',
			'on-site'
		);

		switch ( $type ) {
			case 'one_time':
				$default = 'Dear {customer:email},<br /><br />Thank you for your payment on {charge-date} for &ldquo;{form-title}&rdquo;.<br /><ul><li><strong>Payment ID:</strong> {charge-id}</li><li><strong>Payment Date:</strong> {charge-date}</li><li><strong>Payment Amount:</strong> {total-amount}</li></ul>';
				break;
			case 'subscription':
				$default = 'Dear {customer:email},<br /><br />Thank you for subscribing to &ldquo;{form-title}&rdquo; on {charge-date}.<br /><ul><li><strong>Payment ID:</strong> {charge-id}</li><li><strong>Subscription Activation Date:</strong> {charge-date}</li><li><strong>Initial Payment Amount:</strong> {total-amount}</li><li><strong>Recurring Payment Amount:</strong> {recurring-amount}</li></ul>';

				if ( 'none' !== $subscription_management ) {
					$default .= '<br /><br />You can manage your subscription at any time by visiting: {update-payment-method-url}';
				}

				break;
			case 'trial':
				$default = 'Dear {customer:email},<br /><br />Thank you for subscribing to &ldquo;{form-title}&rdquo; on {charge-date}. Your subscription includes a free trial until {trial-end-date}. <br /><ul><li><strong>Subscription Activation Date:</strong> {charge-date}</li><li><strong>Free Trial End Date:</strong> {trial-end-date}</li><li><strong>Recurring Payment Amount:</strong> {recurring-amount}</li></ul>';

				if ( 'none' !== $subscription_management ) {
					$default .= '<br /><br />You can manage your subscription at any time by visiting: {update-payment-method-url}';
				}

				break;
		}

		$body = simpay_get_setting(
			sprintf(
				'email_%s_body_%s',
				$this->id,
				$type
			),
			$default
		);

		return $body;
	}

}
