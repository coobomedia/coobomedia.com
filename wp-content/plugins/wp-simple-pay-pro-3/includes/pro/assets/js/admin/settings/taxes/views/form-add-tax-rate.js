/**
 * Internal dependencies
 */
import Dialog from './dialog.js';

/**
 * FormTaxRate
 *
 * @since 4.1.0
 *
 * @class FormTaxRate
 * @augments Dialog
 */
const FormAddTaxRate = Dialog.extend( {
	/**
	 * @since 4.1.0
	 */
	el: '#simpay-form-add-tax-rate-dialog',

	/**
	 * @since 4.1.0
	 */
	template: wp.template( 'simpay-form-add-tax-rate' ),

	/**
	 * "Add Tax Rate" view.
	 *
	 * @since 4.1.0
	 *
	 * @constructs FormAddTaxRate
	 * @augments Dialog
	 */
	initialize() {
		Dialog.prototype.initialize.apply( this, arguments );

		// Delegate additional events.
		this.addEvents( {
			'submit form': 'onAdd',
			'keyup .simpay-tax-rate-display-name': 'onChangeDisplayName',
			'keyup .simpay-tax-rate-percentage': 'onChangePercentage',
			'change .simpay-tax-rate-calculation': 'onChangeCalculation',
		} );

		// Listen to changes in model.
		this.listenTo( this.model, 'change', this.render );
		this.listenTo( this.collection, 'add', this.closeDialog );
	},

	/**
	 * Updates the model when the "Display Name" changes.
	 *
	 * @since 4.1.0
	 * @param e.target
	 * @param e.target.value
	 * @param {Object} e Keyup event.
	 */
	onChangeDisplayName( { target: { value } } ) {
		this.model.set( 'display_name', value );
	},

	/**
	 * Updates the model when the "Percentage" changes.
	 *
	 * input[type="number"] does not support selectionStart, making it
	 * unusable with the Base view internal tracking. Validate it as a number.
	 *
	 * @since 4.1.0
	 * @param e.target
	 * @param e.target.value
	 * @param {Object} e Keyup event.
	 */
	onChangePercentage( { target: { value } } ) {
		this.model.set( 'percentage', value );

		if ( false === this.model.isValid() ) {
			this.model.set( 'percentage', '' );
			this.model.trigger( 'change', this.model, {} );
		}
	},

	/**
	 * Updates the model when the "Calculation" changes.
	 *
	 * @since 4.1.0
	 * @param e.target
	 * @param e.target.options
	 * @param e.target.selectedIndex
	 * @param {Object} e Change event.
	 */
	onChangeCalculation( { target: { options, selectedIndex } } ) {
		this.model.set( 'calculation', options[ selectedIndex ].value );
	},

	/**
	 * Adds the model to the collection when the form is submitted.
	 *
	 * @since 4.1.0
	 *
	 * @param {Object} e Submit event.
	 */
	onAdd( e ) {
		e.preventDefault();

		this.collection.add( this.model );
		this.stopListening( this.model );
	},
} );

export default FormAddTaxRate;
