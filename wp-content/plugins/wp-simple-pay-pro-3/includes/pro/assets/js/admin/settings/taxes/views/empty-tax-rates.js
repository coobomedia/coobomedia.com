/** global wp */

/**
 * EmptyTaxRates.
 *
 * @since 4.1.0
 *
 * @class EmptyTaxRates
 * @augments wp.Backbone.View
 */
const EmptyTaxRates = wp.Backbone.View.extend( {
	/**
	 * @since 4.1.0
	 */
	tagName: 'tbody',

	/**
	 * @since 4.1.0
	 */
	template: wp.template( 'simpay-empty-tax-rates' ),
} );

export default EmptyTaxRates;
