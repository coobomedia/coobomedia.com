/* global jQuery */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { upgradeModal } from '@wpsimplepay/utils';

/**
 * DOM ready.
 */
domReady( () => {
	// Email configuration.
	const configurationSelectorEl = document.querySelector(
		'.simpay-settings-emails-configure'
	);

	if ( configurationSelectorEl ) {
		const deliverySubsectionEl = document.querySelector(
			'.simpay-settings-subsection-delivery'
		);

		deliverySubsectionEl.after( configurationSelectorEl );

		setupConfigurationEducation( configurationSelectorEl );
	}

	// Test emails.
	const sendTestEmailFormEl = document.querySelector(
		'.simpay-send-test-email'
	);

	if ( sendTestEmailFormEl ) {
		setupTestEmailForm( sendTestEmailFormEl );
	}
} );

/**
 * Handles product education when configuring an email not available to the current license.
 *
 * @since 4.4.6
 *
 * @param {HTMLElement} configurationSelectorEl Email configuration form.
 */
function setupConfigurationEducation( configurationSelectorEl ) {
	const selector = configurationSelectorEl.querySelector( 'select' );

	// Listen for changes.
	selector.addEventListener( 'change', maybeShowUpgradeModal );

	// Show upgrade modal if necessary.
	function maybeShowUpgradeModal( { target } ) {
		const { options, selectedIndex } = target;
		const selected = options[ selectedIndex ];
		const {
			available,
			upgradeTitle,
			upgradeDescription,
			upgradeUrl,
			upgradePurchasedUrl,
		} = selected.dataset;

		if ( 'no' === available ) {
			upgradeModal( {
				title: upgradeTitle,
				description: upgradeDescription,
				url: upgradeUrl,
				purchasedUrl: upgradePurchasedUrl,
			} );

			selector.value = '';
			selector.selectedIndex = 0;
		}
	}
}

/**
 * Sets up the "form" for sending a test email.
 *
 * @param {HTMLElement} form Wrapping "form" (not a real form, since it is nested).
 */
function setupTestEmailForm( form ) {
	// Email change.
	const emailEl = form.querySelector( '#send-test-email-email' );

	emailEl.addEventListener( 'change', ( { target } ) => {
		const typeEl = form.querySelector( '.simpay-send-test-email__type' );

		typeEl.style.display =
			'payment-confirmation' === target.value ? 'block' : 'none';
	} );

	// Submit buton click.
	const submitEl = form.querySelector( '.simpay-send-test-email__button' );

	submitEl.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		sendTestEmailForm( form );
	} );
}

/**
 * Sends a test email.
 *
 * @param {HTMLElement} form Wrapping "form" (not a real form, since it is nested).
 */
function sendTestEmailForm( form ) {
	// Clear existing notice.
	const existingNotice = form.querySelector( '.notice' );

	if ( existingNotice ) {
		existingNotice.parentNode.removeChild( existingNotice );
	}

	// Send email.
	const { nonce } = form.dataset;

	const toEl = document.getElementById( 'send-test-email-to' );
	const to = toEl.value;

	const emailEl = document.getElementById( 'send-test-email-email' );
	const email = emailEl.value;

	const typeEl = document.getElementById( 'send-test-email-type' );
	let type = '';

	if ( typeEl ) {
		type = typeEl.value;
	}

	const notice = document.createElement( 'div' );
	notice.classList.add( 'notice' );

	const noticeMessage = document.createElement( 'p' );
	notice.appendChild( noticeMessage );

	wp.ajax.send( {
		data: {
			action: 'simpay_send_test_email',
			to,
			email,
			type,
			nonce,
		},
		/**
		 * Appends a success notice.
		 *
		 * @param {Object} result
		 * @param result.message
		 */
		success: ( { message } ) => {
			notice.classList.add( 'notice-success' );
			noticeMessage.innerText = message;
			form.appendChild( notice );
		},
		/**
		 * Appends an error notice.
		 *
		 * @param {Object} result
		 * @param result.message
		 */
		error: ( { message } ) => {
			notice.classList.add( 'notice-error' );
			noticeMessage.innerText = message;
			form.appendChild( notice );
		},
	} );
}
