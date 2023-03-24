/* global _ */

/**
 * WordPress dependencies
 */
import { decodeEntities } from '@wordpress/html-entities';

/**
 * Adds a payment form restriction when an item is checked in the results list.
 *
 * @since 4.3.0
 */
export function addFormRestrictions() {
	// Added restricions wrapper.
	const restrictionsWrap = document.getElementById(
		'coupon-restrictions-applies_to_forms'
	);

	// Search results.
	const addRestrictionToggles = document.querySelectorAll(
		'.coupon-add-applies_to_forms'
	);

	// Adds a search result to restrictions.
	function addRestriction( { target } ) {
		const id = target.value;
		const title = target.dataset.title;

		restrictionsWrap.appendChild( createRestriction( { id, title } ) );

		// Remove from list.
		target.closest( '.coupon-add-applies_to_forms-wrap' ).remove();

		// Focus the next result.
		const remainingResults = document.querySelectorAll(
			'.coupon-add-applies_to_forms'
		);

		if ( remainingResults.length > 0 ) {
			remainingResults[ 0 ].focus();
		}

		// Rebind listeners.
		removeFormRestrictions();
	}

	// Listens to search result clicks.
	_.each( addRestrictionToggles, ( addRestrictionToggle ) => {
		addRestrictionToggle.addEventListener( 'change', addRestriction );
	} );
}

/**
 * Removes a payment form restriction when a previously added result is removed.
 *
 * @since 4.3.0
 */
function removeFormRestrictions() {
	// Current restrictions.
	const removeRestrictionToggles = document.querySelectorAll(
		'.coupon-remove-applies_to_forms'
	);

	// Removes an added restriction.
	function removeRestriction( { target } ) {
		target.closest( '.coupon-remove-applies_to_forms' ).remove();
	}

	// Listen to existing restriction clicks.
	_.each( removeRestrictionToggles, ( removeRestrictionToggle ) => {
		removeRestrictionToggle.addEventListener( 'click', removeRestriction );
	} );
}

/**
 * Searches for payment forms to restrict coupons to.
 *
 * @since 4.3.0
 */
export function formRestrictionSearch() {
	// Results location.
	const resultsWrap = document.getElementById(
		'coupon-results-applies_to_forms'
	);

	// Search input and data.
	const searchInput = document.getElementById(
		'coupon-search-applies_to_forms'
	);

	const nonce = searchInput.dataset.nonce;

	// Success callback.
	function setResults( { forms, message } ) {
		resultsWrap.innerHTML = '';

		// No forms were found, just show the message and announce it.
		if ( ! forms && message ) {
			wp.a11y.speak( message, 'polite' );
			resultsWrap.innerHTML = message;

			// Append results.
		} else {
			forms.forEach( ( searchResult ) => {
				resultsWrap.appendChild( createSearchResult( searchResult ) );
			} );

			// Announce any further message.
			wp.a11y.speak( message, 'polite' );
		}

		// Ensure listeners update for new content.
		addFormRestrictions();
	}

	// Create a spinner.
	const spinner = document.createElement( 'div' );
	spinner.classList.add( 'spinner' );
	spinner.classList.add( 'is-active' );
	spinner.style.float = 'none';
	spinner.style.margin = 0;

	// Search on keydown.
	searchInput.addEventListener(
		'keydown',
		_.debounce( ( e ) => {
			const {
				target: { value },
				keyCode,
			} = e;

			if ( 13 === keyCode || '' === value ) {
				return false;
			}

			const appliesTo = document.querySelectorAll(
				'input[name="coupon[applies_to_forms][]"]'
			);

			const exclude = [ ...appliesTo ].map( ( elem ) => elem.value );

			// Show the spinner.
			resultsWrap.innerHTML = '';
			resultsWrap.appendChild( spinner );

			wp.ajax.send( 'simpay_coupons_payment_forms', {
				data: {
					nonce,
					search: value,
					exclude,
				},
				success: setResults,
				error( error ) {
					if ( window.console ) {
						console.log( error );
					}

					resultsWrap.innerHTML = error;
				},
			} );
		}, 500 )
	);
}

/**
 * Creates search result markup.
 *
 * @since 4.3.0
 *
 * @param {Object} searchResult Search result.
 * @param {number} searchResult.id
 * @param {string} searchResult.title
 * @return {HTMLObjectElement} Search result element.
 */
function createSearchResult( { id, title } ) {
	const wrap = document.createElement( 'p' );
	const label = document.createElement( 'label' );
	const input = document.createElement( 'input' );
	const result = document.createElement( 'span' );

	// Add a class to the wrapper so it can be targeted.
	wrap.classList.add( 'coupon-add-applies_to_forms-wrap' );

	// Setup the input.
	input.type = 'checkbox';
	input.value = id;
	input.classList.add( 'coupon-add-applies_to_forms' );
	input.dataset.title = title;

	// Populate result title.
	result.innerText = decodeEntities( title );

	// Put the checkbox in the label.
	label.appendChild( input );

	// Put the result title in the label.
	label.appendChild( result );

	// Put the label in the wrapper.
	wrap.appendChild( label );

	return wrap;
}

/**
 * Creates restriction markup.
 *
 * @since 4.3.0
 *
 * @param {Object} restriction Restriction
 * @param {number} restriction.id
 * @param {string} restriction.title
 * @return {HTMLObjectElement} Restriction element.
 */
function createRestriction( { id, title } ) {
	const badge = document.createElement( 'button' );
	const badgeIcon = document.createElement( 'div' );
	const badgeLabel = document.createElement( 'span' );
	const input = document.createElement( 'input' );

	// Form badge.
	badge.type = 'button';
	badge.classList.add( 'simpay-badge' );
	badge.classList.add( 'coupon-remove-applies_to_forms' );

	badgeIcon.classList.add( 'simpay-badge__icon' );
	badgeIcon.classList.add( 'dashicons' );
	badgeIcon.classList.add( 'dashicons-no-alt' );

	badgeLabel.innerText = decodeEntities( title );

	// Form input.
	input.type = 'hidden';
	input.value = id;
	input.name = 'coupon[applies_to_forms][]';

	// Put badge icon,label, and input in badge.
	badge.appendChild( badgeIcon );
	badge.appendChild( badgeLabel );
	badge.appendChild( input );

	return badge;
}
