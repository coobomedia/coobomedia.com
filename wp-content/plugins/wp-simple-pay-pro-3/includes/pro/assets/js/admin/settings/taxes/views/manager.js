/** global wp */

/**
 * External dependencies
 */
import { v4 as uuidv4 } from 'uuid';

/**
 * Internal dependencies
 */
import FormAddTaxRate from './form-add-tax-rate.js';
import TaxRates from './tax-rates.js';
import EmptyTaxRates from './empty-tax-rates.js';
import TaxRate from './../models/tax-rate.js';

/**
 * Manager.
 *
 * @since 4.1.0
 *
 * @class Manager
 * @augments wp.Backbone.View
 */
const Manager = wp.Backbone.View.extend( {
	/**
	 * @since 4.1.0
	 */
	el: '#simpay-tax-rate-manager',

	/**
	 * @since 4.1.0
	 */
	events: {
		'click .simpay-add-tax-rate': 'onAddTaxRate',
	},

	/**
	 * TaxRates view.
	 *
	 * @since 4.1.0
	 *
	 * @constructs TaxRates
	 * @augments wp.Backbone.View
	 */
	initialize() {
		this.listenTo( this.collection, 'add', this.render );
		this.listenTo( this.collection, 'remove', this.render );
	},

	/**
	 * Renders the view.
	 *
	 * @since 4.1.0
	 *
	 * @return {Manager} Current view.
	 */
	render() {
		this.$el.find( 'tbody' ).remove();

		if ( 0 === this.collection.length ) {
			return this.views.add( 'table', new EmptyTaxRates() );
		}

		this.views.add(
			'table',
			new TaxRates( {
				collection: this.collection,
			} )
		);

		return this;
	},

	/**
	 * Opens the "New Tax Rate" dialog.
	 *
	 * @since 4.1.0
	 *
	 * @param {Object} e Click event.
	 */
	onAddTaxRate( e ) {
		e.preventDefault();

		const id = uuidv4();

		new FormAddTaxRate( {
			collection: this.collection,
			model: new TaxRate( {
				id,
				instanceId: id,
				display_name: 'Sales tax',
			} ),
		} )
			.openDialog()
			.render();
	},
} );

export default Manager;
