/* global jQuery */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * DOM ready.
 */
domReady( () => {
	// Payment Receipt.
	const sendPaymentReceiptFormEl = document.querySelector(
		'.simpay-send-payment-confirmation'
	);

	if ( sendPaymentReceiptFormEl ) {
		setupPaymentReceiptForm( sendPaymentReceiptFormEl );
	}
} );

/**
 * Sets up the "form" for sending a Payment Receipt.
 *
 * @param {HTMLElement} form Wrapping "form" (not a real form, since it is nested).
 */
function setupPaymentReceiptForm( form ) {
	// Search buton click.
	const searchButtonEl = document.getElementById(
		'simpay-send-payment-confirmation-search'
	);

	searchButtonEl.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		getPaymentReceiptResults( form );
	} );

	// Submit button click.
	const submitButtonEl = document.getElementById(
		'simpay-send-payment-confirmation-submit'
	);

	submitButtonEl.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		resendPaymentReceipt( form );
	} );
}

/**
 * Retrieves Customer results and builds output for resending a Payment Receipt.
 *
 * @param {HTMLElement} form Wrapping "form" (not a real form, since it is nested).
 */
function getPaymentReceiptResults( form ) {
	// Current email value.
	const emailEl = document.getElementById(
		'simpay-send-payment-confirmation-email'
	);

	const email = emailEl.value;

	// Create a spinner.
	const spinner = document.createElement( 'div' );
	spinner.classList.add( 'spinner' );
	spinner.classList.add( 'is-active' );

	// Find results area.
	const resultsContainer = document.querySelector(
		'.simpay-send-payment-confirmation__results'
	);

	const resultsEl = document.getElementById(
		'simpay-send-payment-confirmation-results'
	);

	// Find submit button.
	const submitButtonEl = document.getElementById(
		'simpay-send-payment-confirmation-submit'
	);

	resultsContainer.classList.remove( 'hidden' );

	// Show spinner.
	resultsEl.innerHTML = '';
	resultsEl.appendChild( spinner );

	const { nonce } = form.dataset;

	wp.ajax.send( {
		data: {
			action: 'simpay_resend_payment_confirmation_results',
			email,
			nonce,
		},
		/**
		 * Appends the list of customers.
		 *
		 * @param {Object} result AJAX result.
		 * @param result.customers
		 */
		success: ( { customers } ) => {
			// Reset content.
			resultsEl.innerHTML = '';

			customers.forEach( ( customer, i ) => {
				const { id, name, created_i18n: date, link } = customer;

				// Build a label.
				const label = document.createElement( 'label' );
				label.id = id;
				label.for = id;
				label.classList.add(
					'simpay-send-payment-confirmation__result'
				);

				// Build a radio button.
				const input = document.createElement( 'input' );
				input.id = id;
				input.value = id;
				input.name = 'simpay-send-payment-confirmation-customer';
				input.type = 'radio';
				input.checked = 0 === i;

				// Build description.
				const description = document.createElement( 'span' );
				description.innerHTML = `${
					name ? name : id
				} &bull; ${ date }<br />
					<a href="${ link }" target="_blank" rel="">View in Stripe</a>`;

				label.appendChild( input );
				label.appendChild( description );

				resultsEl.appendChild( label );
			} );

			// Enable Submit button.
			submitButtonEl.disabled = false;
		},
		/**
		 * Appends a message when there is an error or no results found.
		 *
		 * @param {Object} result AJAX result.
		 * @param result.message
		 */
		error: ( { message } ) => {
			// Reset content.
			resultsEl.innerHTML = '';

			// Show error message.
			resultsEl.innerText = message;

			// Disable Submit button.
			submitButtonEl.disabled = true;
		},
	} );
}

/**
 * Resends a Payment Confirmation.
 *
 * @param {HTMLElement} form Wrapping "form" (not a real form, since it is nested).
 */
function resendPaymentReceipt( form ) {
	// Set submit loading state.
	const submitButtonEl = document.getElementById(
		'simpay-send-payment-confirmation-submit'
	);

	submitButtonEl.innerText = submitButtonEl.dataset.loading;
	submitButtonEl.disabled = true;

	const { nonce } = form.dataset;

	// Find the selected Customer.
	const customerEl = document.querySelector(
		'input[name="simpay-send-payment-confirmation-customer"]:checked'
	);

	const customer = customerEl.value;

	// Clear existing notice.
	const existingNotice = form.querySelector( '.notice' );

	if ( existingNotice ) {
		existingNotice.parentNode.removeChild( existingNotice );
	}

	// Create a notice.
	const notice = document.createElement( 'div' );
	notice.classList.add( 'notice' );

	const noticeMessage = document.createElement( 'p' );
	notice.appendChild( noticeMessage );

	// Attempt to send.
	wp.ajax.send( {
		data: {
			action: 'simpay_resend_payment_confirmation',
			customer,
			nonce,
		},
		/**
		 * Appends a success notice.
		 *
		 * @param {Object} result
		 * @param result.message
		 */
		success: ( { message } ) => {
			// Append notice.
			notice.classList.add( 'notice-success' );
			noticeMessage.innerText = message;
			form.appendChild( notice );

			// Enable Submit.
			submitButtonEl.innerText = submitButtonEl.dataset.active;
			submitButtonEl.disabled = false;
		},
		/**
		 * Appends an error notice.
		 *
		 * @param {Object} result
		 * @param result.message
		 */
		error: ( { message } ) => {
			// Append notice.
			notice.classList.add( 'notice-error' );
			noticeMessage.innerText = message;
			form.appendChild( notice );

			// Enable Submit.
			submitButtonEl.innerText = submitButtonEl.dataset.active;
			submitButtonEl.disabled = false;
		},
	} );
}
