/**
 * Internal dependencies
 */
import Dialog from './dialog.js';

/**
 * FormEditTaxRate
 *
 * @since 4.1.0
 *
 * @class FormTaxRate
 * @augments Dialog
 */
const FormEditTaxRate = Dialog.extend( {
	/**
	 * @since 4.1.0
	 */
	el: '#simpay-form-edit-tax-rate-dialog',

	/**
	 * @since 4.1.0
	 */
	template: wp.template( 'simpay-form-edit-tax-rate' ),

	/**
	 * "Edit Tax Rate" view.
	 *
	 * @since 4.1.0
	 *
	 * @constructs FormEditTaxRate
	 * @augments Dialog
	 */
	initialize() {
		Dialog.prototype.initialize.apply( this, arguments );

		// Delegate additional events.
		this.addEvents( {
			'submit form': 'onEdit',
			'keyup .simpay-tax-rate-display-name': 'onChangeDisplayName',
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
	 * Updates the model to the collection when the form is submitted.
	 *
	 * @since 4.1.0
	 *
	 * @param {Object} e Submit event.
	 */
	onEdit( e ) {
		e.preventDefault();

		this.collection.add( this.model, { merge: true } );
		this.collection.trigger( 'add' );
		this.stopListening( this.model );
	},
} );

export default FormEditTaxRate;
