/* global Backbone */

/**
 * Model a tax rate.
 *
 * @since 4.1.0
 */
const TaxRate = Backbone.Model.extend( {
	defaults: {
		id: '',
		instanceId: '',
		displayName: 'Sales tax',
		percentage: '',
		calculation: 'exclusive',
	},

	/**
	 * Validates the model.
	 *
	 * Error messages are not used in UI, so are not translated.
	 *
	 * @since 4.1.0
	 *
	 * @param {Object} attributes Model attributes.
	 */
	validate( attributes ) {
		// Validate percenetage as a decimal.
		const { percentage } = attributes;
		const errors = [];

		if ( percentage >= 100 || percentage < 0 ) {
			errors.push( 'Invalid percentage' );
		}

		if ( false === /^\d*\.?\d*$/.test( percentage ) ) {
			errors.push( 'Invalid percentage' );
		}

		if ( 0 !== errors.length ) {
			return errors;
		}
	},
} );

export default TaxRate;
