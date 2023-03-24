<?php
/**
 * Emails: Settings
 *
 * @package SimplePay\Pro\Emails
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Pro\Emails\Settings;

use SimplePay\Core\Utils;
use SimplePay\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// WP Mail SMTP education.
require_once 'class-smtp.php';

/**
 * Registers settings section.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Section_Collection $sections Section collection.
 */
function register_sections( $sections ) {
	$sections->add(
		new Settings\Section(
			array(
				'id'       => 'emails',
				'label'    => esc_html_x(
					'Emails',
					'settings subsection label',
					'simple-pay'
				),
				'priority' => 60,
			)
		)
	);
}
add_action( 'simpay_register_settings_sections', __NAMESPACE__ . '\\register_sections' );

/**
 * Registers settings subsections.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Subsections_Collection $subsections Subsections collection.
 */
function register_subsections( $subsections ) {
	// General.
	$subsections->add(
		new Settings\Subsection(
			array(
				'id'       => 'general',
				'section'  => 'emails',
				'label'    => esc_html_x(
					'General',
					'settings subsection label',
					'simple-pay'
				),
				'priority' => 10,
			)
		)
	);

	// Active emails.
	$emails = Utils\get_collection( 'emails' );

	if ( false === $emails ) {
		return;
	}

	$priority = 20;

	/* @var $emails \SimplePay\Pro\Emails\Email[] */
	foreach ( $emails->get_items() as $email ) {
		if ( false === $email->is_active() ) {
			continue;
		}

		$subsections->add(
			new Settings\Subsection(
				array(
					'id'       => $email->id,
					'section'  => 'emails',
					'label'    => $email->label,
					'priority' => $priority,
				)
			)
		);

		$priority = $priority + 10;
	}
}
add_action( 'simpay_register_settings_subsections', __NAMESPACE__ . '\\register_subsections' );

/**
 * Registers email settings.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_settings( $settings ) {
	register_general_settings( $settings );
	register_email_settings( $settings );
}
add_action( 'simpay_register_settings', __NAMESPACE__ . '\\register_settings' );

/**
 * Registers the general settings for emails.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_general_settings( $settings ) {
	// From name.
	$settings->add(
		new Settings\Setting_Input(
			array(
				'id'          => 'email_from_name',
				'section'     => 'emails',
				'subsection'  => 'general',
				'label'       => esc_html_x(
					'From Name',
					'setting label',
					'simple-pay'
				),
				'value'       => simpay_get_setting(
					'email_from_name',
					get_site_option( 'blogname' )
				),
				'description' => wpautop(
					esc_html__(
						'The name that emails come from. This is usually your site name.',
						'simple-pay'
					)
				),
				'classes'     => array(
					'regular-text',
				),
				'priority'    => 20,
				'schema'      => array(
					'type' => 'string',
				),
			)
		)
	);

	// From email.
	$settings->add(
		new Settings\Setting_Input(
			array(
				'id'          => 'email_from_address',
				'section'     => 'emails',
				'subsection'  => 'general',
				'label'       => esc_html_x(
					'From Address',
					'setting label',
					'simple-pay'
				),
				'value'       => simpay_get_setting(
					'email_from_address',
					get_site_option( 'admin_email' )
				),
				'description' => wpautop(
					esc_html__(
						'The email address to send emails from. This will act as the "from" and "reply-to" address.',
						'simple-pay'
					)
				),
				'classes'     => array(
					'regular-text',
				),
				'priority'    => 30,
				'schema'      => array(
					'type' => 'string',
				),
			)
		)
	);
}

/**
 * Outputs the email configuration selector.
 *
 * @since 4.4.6
 *
 * @return void
 */
function add_email_selector() {
	$emails = Utils\get_collection( 'emails' );

	if ( false === $emails ) {
		return;
	}

	$subsection = isset( $_GET['subsection'] )
		? sanitize_text_field( $_GET['subsection'] )
		: '';

	$license = simpay_get_license();
	?>

	<form action="" method="GET" class="simpay-settings-emails-configure">
		<select name="subsection">
			<option value="">
				<?php esc_html_e( 'Select email&hellip;', 'simple-pay' ); ?>
			</option>
			<?php
			foreach ( $emails->get_items() as $email ) :
				$upgrade_title = sprintf(
					/* translators: Email label. */
					__(
						'Unlock "%s" Email',
						'simple-pay'
					),
					esc_html( $email->label )
				);

				$upgrade_description = sprintf(
					/* translators: %1$s Email label. %2$s License level required. */
					__(
						'We\'re sorry, customizing and sending the "%1$s" email is not available on your plan. Please upgrade to the <strong>%2$s</strong> plan or higher to unlock this and other awesome features.',
						'simple-pay'
					),
					esc_html( $email->label ),
					ucfirst( $email->licenses[0] )
				);

				$upgrade_url = simpay_pro_upgrade_url(
					'email-settings',
					$email->label
				);

				$upgrade_purchased_url = simpay_docs_link(
					$email->label,
					$license->is_lite()
						? 'upgrading-wp-simple-pay-lite-to-pro'
						: 'activate-wp-simple-pay-pro-license',
					'email-settings',
					true
				);

				echo wp_kses(
					sprintf(
						'<option value="%1$s" %2$s data-available="%3$s" data-upgrade-title="%4$s" data-upgrade-description="%5$s" data-upgrade-url="%6$s" data-upgrade-purchased-url="%7$s" >%8$s</option>',
						esc_attr( $email->id ),
						selected( true, $email->id === $subsection, false ),
						in_array( $license->get_level(), $email->licenses, true ) ? 'yes' : 'no',
						esc_attr( $upgrade_title ),
						esc_attr( $upgrade_description ),
						esc_url( $upgrade_url ),
						esc_url( $upgrade_purchased_url ),
						esc_html( $email->label )
					),
					array(
						'option' => array(
							'value'                      => true,
							'selected'                   => true,
							'data-available'             => true,
							'data-upgrade-title'         => true,
							'data-upgrade-description'   => true,
							'data-upgrade-url'           => true,
							'data-upgrade-purchased-url' => true,
						),
					)
				);
			endforeach;
			?>
		</select>
		<button type="submit" class="button button-secondary">
			<?php echo esc_html_e( 'Configure', 'simple-pay' ); ?>
		</button>
		<input type="hidden" name="post_type" value="simple-pay" />
		<input type="hidden" name="page" value="simpay_settings" />
		<input type="hidden" name="tab" value="emails" />
	</form>

	<?php
}
add_action(
	'simpay_admin_page_settings_emails_before',
	__NAMESPACE__ . '\\add_email_selector'
);

/**
 * Registers the settings for active emails.
 *
 * @since 4.0.0
 *
 * @param \SimplePay\Core\Settings\Setting_Collection $settings Settings collection.
 */
function register_email_settings( $settings ) {
	$emails = Utils\get_collection( 'emails' );

	if ( false === $emails ) {
		return;
	}

	/* @var $emails \SimplePay\Pro\Emails\Email[] */
	foreach ( $emails->get_items() as $email ) {
		if ( false === $email->is_active() ) {
			continue;
		}

		$email->register_settings( $settings );
	}
}

/**
 * Sanitizes Email settings.
 *
 * @since 4.0.0
 *
 * @param array $settings Settings to save.
 * @return array $settings Setttings to save.
 */
function sanitize_settings( $settings ) {
	// Ensure a valid "From Address".
	if ( isset( $settings['email_from_address'] ) ) {
		if ( ! is_email( $settings['email_from_address'] ) ) {
			$settings['email_from_address'] = get_bloginfo( 'admin_email' );
		}
	} else {
		$settings['email_from_address'] = get_bloginfo( 'admin_email' );
	}

	return $settings;
}
add_filter( 'simpay_update_settings', __NAMESPACE__ . '\\sanitize_settings' );
