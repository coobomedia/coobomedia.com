/* global Backbone */

/**
 * Internal dependencies.
 */
import TaxRate from './../models/tax-rate.js';

/**
 * A collection of multiple tax rates.
 *
 * @since 4.1.0
 */
const TaxRates = Backbone.Collection.extend( {
	model: TaxRate,
} );

export default TaxRates;
