/* global jQuery */

/**
 * Initailize jQuery UI datepicker.
 *
 * @param {Event} e simpayBindCoreFormEventsAndTriggers
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 */
export function setup( e, spFormElem, formData ) {
	if ( ! jQuery.datepicker ) {
		return;
	}

	const dateInputEl = spFormElem.find( '.simpay-date-input' );

	dateInputEl.datepicker( {
		dateFormat: formData.dateFormat,
		beforeShow() {
			jQuery( '.ui-datepicker' ).addClass( 'simpay-datepicker' );
		},
	} );

	if ( '' === dateInputEl.val() ) {
		return;
	}

	dateInputEl.datepicker( 'setDate', new Date( dateInputEl.val() ) );
}

/**
 * Bind events to Payment Form.
 */
jQuery( document.body ).on(
	'simpayBindCoreFormEventsAndTriggers',
	// eslint-disable-line no-unused-vars
	( e, spFormElem, formData ) => {
		setup( e, spFormElem, formData );
	}
);
