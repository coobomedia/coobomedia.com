/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import {
	couponNameRestrictions,
	discountTypeToggle,
	durationToggle,
	formRestrictionToggle,
	redemptionToggle,
} from './ui.js';
import { formRestrictionSearch } from './restriction-payment-forms.js';

/**
 * Sets up interactions when the DOM is ready.
 *
 * @since 4.3.0
 */
domReady( () => {
	const hasForm = document.getElementById(
		'simpay-admin-add-coupon-wrapper'
	);

	if ( ! hasForm ) {
		return;
	}

	// UI.
	couponNameRestrictions();
	discountTypeToggle();
	durationToggle();
	redemptionToggle();
	formRestrictionToggle();

	// Form restrictions.
	formRestrictionSearch();
} );
