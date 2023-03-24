/**
 * Internal dependencies
 */
import Base from './base.js';
import FormEditTaxRate from './form-edit-tax-rate.js';

/**
 * TaxRate
 *
 * @since 4.1.0
 *
 * @class TaxRate
 * @augments wp.Backbone.View
 */
const TaxRate = Base.extend( {
	/**
	 * @since 4.1.0
	 */
	tagName: 'tr',

	/**
	 * @since 4.1.0
	 */
	template: wp.template( 'simpay-tax-rate' ),

	/**
	 * @since 4.1.0
	 */
	events: {
		'click .edit': 'onEdit',
		'click .remove': 'onRemove',
	},

	/**
	 * Opens the "Edit Tax Rate" dialog.
	 *
	 * @since 4.1.0
	 *
	 * @param {Object} e Click event.
	 */
	onEdit( e ) {
		e.preventDefault();

		new FormEditTaxRate( {
			collection: this.collection,
			model: this.model,
		} )
			.openDialog()
			.render();
	},

	/**
	 * Removes a tax rate.
	 *
	 * @since 4.1.0
	 *
	 * @param {Object} e Click event.
	 */
	onRemove( e ) {
		e.preventDefault();

		this.collection.remove( this.model );
	},
} );

export default TaxRate;
