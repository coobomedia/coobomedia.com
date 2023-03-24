<?php
/**
 * Emails: Email
 *
 * @package SimplePay\Pro\Emails
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Emails;

use SimplePay\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email class
 *
 * @since 4.0.0
 */
class Email {

	/**
	 * Email ID.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $id;

	/**
	 * Email label.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $label;

	/**
	 * Email description.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $description;

	/**
	 * Email settings defaults.
	 *
	 * @since 4.0.0
	 * @var array {
	 *   Default settings.
	 *
	 *   @type string $to Default "Send To" setting.
	 *   @type string $subject Default "Subject" setting.
	 *   @type string $body Default "Message" setting.
	 * }
	 */
	public $settings;

	/**
	 * Email to.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $to;

	/**
	 * Email subject.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $subject;

	/**
	 * Email body.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public $body;

	/**
	 * Email license requirements.
	 *
	 * @since 4.4.6
	 * @var array<string>
	 */
	public $licenses;

	/**
	 * Constructs the Email.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args {
	 *   Setting section configuration.
	 *
	 *   @type string $id Email ID.
	 *   @type string $label Email label.
	 * }
	 */
	public function __construct( $args ) {
		$defaults = array(
			'id'          => '',
			'label'       => '',
			'description' => '',
			'settings'    => array(
				'to'      => '',
				'subject' => '',
				'body'    => '',
			),
			'licenses'    => array(
				'personal',
				'plus',
				'professional',
				'ultimate',
				'elite',
			)
		);

		$args = wp_parse_args( $args, $defaults );

		// ID.
		$this->id = sanitize_text_field( $args['id'] );

		// Label.
		$this->label = ! empty( $args['label'] )
			? sanitize_text_field( $args['label'] )
			: $this->id;

		// Description.
		$this->description = wp_kses_post( $args['description'] );

		// Settings defaults.
		foreach ( $args['settings'] as $setting => $default ) {
			$this->settings[ $setting ] = $default;
		}

		// Licenses.
		$this->licenses = array_map( 'sanitize_text_field', $args['licenses'] );
	}

	/**
	 * Helper function to retrieve a stored email setting or return the default
	 * added on registration.
	 *
	 * @since 4.0.0
	 *
	 * @param string $setting Setting suffix "key". Retrieves settings formatted
	 *                        like `email_{$id}_{$setting}`.
	 * @return string
	 */
	public function get_setting( $setting ) {
		$default = isset( $this->settings[ $setting ] )
			? $this->settings[ $setting ]
			: '';

		return simpay_get_setting(
			sprintf(
				'email_%s_%s',
				$this->id,
				$setting
			),
			$default
		);
	}

	/**
	 * Sends the email.
	 *
	 * @since 4.0.0
	 *
	 * @return bool Whether the email contents were sent successfully.
	 */
	public function send() {
		if ( false === $this->is_enabled() ) {
			return false;
		}

		if (
			empty( $this->to ) ||
			empty( $this->subject ) ||
			empty( $this->body )
		) {
			return false;
		}

		add_filter( 'wp_mail_content_type', __NAMESPACE__ . '\\html_content_type' );

		$sent = wp_mail(
			$this->to,
			$this->subject,
			$this->body,
			$this->get_header()
		);

		remove_filter( 'wp_mail_content_type', __NAMESPACE__ . '\\html_content_type' );

		return $sent;
	}

	/**
	 * Determines if the email is active, and can be configured.
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public function is_active() {
		$license = simpay_get_license();

		return in_array( $license->get_level(), $this->licenses, true );
	}

	/**
	 * Determines if the email is enabled, and should send.
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public function is_enabled() {
		$enabled = 'yes' === simpay_get_setting(
			sprintf(
				'email_%s',
				$this->id
			),
			'yes'
		);

		return $this->is_active() && $enabled;
	}

	/**
	 * Returns email header information.
	 *
	 * Pulls name and address from stored settings.
	 *
	 * @since 4.0.0
	 *
	 * @return string
	 */
	public function get_header() {
		$name = wp_specialchars_decode(
			simpay_get_setting(
				'email_from_name',
				get_site_option( 'blogname' )
			),
			ENT_QUOTES
		);

		$email = simpay_get_setting(
			'email_from_address',
			get_site_option( 'admin_email' )
		);

		$header = 'From: ' . $name . ' <' . $email . ">\r\n";

		return $header;
	}

	/**
	 * Registers default settings for configuring the email.
	 *
	 * @since 4.0.0
	 *
	 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
	 */
	public function register_settings( $settings ) {
		$this->register_settings_enable( $settings );
		$this->register_settings_to( $settings );
		$this->register_settings_subject( $settings );
		$this->register_settings_body( $settings );
	}

	/**
	 * Registers a setting to enable or disable the email.
	 *
	 * @since 4.0.0
	 *
	 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
	 */
	protected function register_settings_enable( $settings ) {
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
							sprintf( 'email_%s_test', $this->id ),
						),
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
					'value'      => $this->get_setting( 'to' ),
					'classes'    => array(
						'regular-text',
					),
					'priority'   => 20,
					'schema'     => array(
						'type' => 'string',
					),
				)
			)
		);
	}

	/**
	 * Registers the "Subject" setting.
	 *
	 * @since 4.0.0
	 *
	 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
	 */
	protected function register_settings_subject( $settings ) {
		$settings->add(
			new Settings\Setting_Input(
				array(
					'id'         => sprintf( 'email_%s_subject', $this->id ),
					'section'    => 'emails',
					'subsection' => $this->id,
					'label'      => esc_html_x(
						'Subject',
						'setting label',
						'simple-pay'
					),
					'value'      => $this->get_setting( 'subject' ),
					'classes'    => array(
						'regular-text',
					),
					'priority'   => 30,
					'schema'     => array(
						'type' => 'string',
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
