/** global wp */

/**
 * Internal dependencies
 */
import TaxRate from './tax-rate.js';

/**
 * TaxRates.
 *
 * @since 4.1.0
 *
 * @class TaxRates
 * @augments wp.Backbone.View
 */
const TaxRates = wp.Backbone.View.extend( {
	/**
	 * @since 4.1.0
	 */
	tagName: 'tbody',

	/**
	 * Renders the view.
	 *
	 * @since 4.1.0
	 *
	 * @return {TaxRates} Current view.
	 */
	render() {
		this.collection.forEach( ( model ) => {
			this.views.add(
				new TaxRate( {
					collection: this.collection,
					model,
				} )
			);
		} );
	},
} );

export default TaxRates;
