/* global simpayTaxRates */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import TaxRates from './collections/tax-rates.js';
import Manager from './views/manager.js';

/**
 * DOM ready.
 */
domReady( () => {
	if ( 'undefined' === typeof simpayTaxRates ) {
		return;
	}

	const taxRates = _.map( simpayTaxRates, ( taxRate, instanceId ) => {
		return {
			...taxRate,
			instanceId,
		};
	} );

	const manager = new Manager( {
		collection: new TaxRates( taxRates ),
	} );

	manager.render();
} );
