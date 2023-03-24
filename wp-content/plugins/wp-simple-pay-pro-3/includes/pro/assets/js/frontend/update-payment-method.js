/* global simpayUpdatePaymentMethod, Stripe, jQuery, _ */

/**
 * Bespoke handling of creating a new Payment Method and updating a Susbcription.
 *
 * Potentially this could be registered as another form type to handle, but for
 * the time being consists of mocking a faked Payment Form and doing some manual
 * processing. It pulls in some functionality from the Stripe Elements Payment Form type.
 */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies.
 */
import { setupintents, subscriptions } from '@wpsimplepay/api';
import { default as onBaseError } from './payment-forms/stripe-elements/error.js';
import { default as onBaseDisable } from './payment-forms/stripe-elements/disable.js';
import {
	getElementStyle,
	getOwnerData,
} from './payment-forms/stripe-elements/utils.js';
const { getPaymentMethod } = window.wpsp.paymentForms;

/** @typedef {import('@wpsimplepay/payment-forms').PaymentForm} PaymentForm */

/**
 * Sets up the ACH Direct Debit bank account link.
 * Because this is a two step process an additional setup function is needed, and called manually.
 *
 * @param {PaymentForm} paymentForm Payment form.
 * @return {void}
 */
function setupAchDirectDebit( paymentForm ) {
	const achDebitEl = paymentForm[ 0 ].querySelector(
		'.simpay-ach-debit-wrap'
	);

	if ( ! achDebitEl ) {
		return;
	}

	const submitEl = achDebitEl.querySelector( 'button' );

	if ( ! submitEl ) {
		return;
	}

	const {
		error: onError,
		disable: disableForm,
		enable: enableForm,
		state,
		setState,
		stripeInstance: stripe,
	} = paymentForm;

	const { customerId } = state;

	const {
		customer_setupintent_nonce: customerNonce,
	} = simpayUpdatePaymentMethod;

	/**
	 * "Select Bank" button click handler.
	 *
	 * @param {Object} e Click event.
	 */
	submitEl.addEventListener( 'click', async ( e ) => {
		e.preventDefault();

		disableForm();

		// Create a SetupIntent for the Payment Method.
		const { client_secret: clientSecret } = await setupintents
			.create(
				{
					customer_id: customerId,
					customer_nonce: customerNonce,
					payment_method_types: [ 'us_bank_account' ],
				},
				paymentForm
			)
			.catch( ( error ) => onError( error ) );

		const { name, email } = getOwnerData( paymentForm );

		// Collect bank account information.
		const { setupIntent, error } = await stripe.collectBankAccountForSetup(
			{
				clientSecret,
				params: {
					payment_method_type: 'us_bank_account',
					payment_method_data: {
						billing_details: {
							name,
							email,
						},
					},
				},
			}
		);

		if ( error ) {
			throw error;
		}

		// Customer canceled the hosted verification modal.
		if ( setupIntent.status === 'requires_payment_method' ) {
			return enableForm();
		}

		// Confirm mandates.
		if ( setupIntent.status === 'requires_confirmation' ) {
			setState( {
				paymentMethodId: setupIntent.payment_method,
			} );

			enableForm();

			// Hide button.
			paymentForm[ 0 ].querySelector(
				'.simpay-ach-debit-wrap button'
			).style.display = 'none';

			// Show terms.
			paymentForm[ 0 ].querySelector(
				'#simpay-ach-debit-terms'
			).style.display = 'block';

			paymentForm[ 0 ].querySelector(
				'#simpay-ach-debit-terms-recurring'
			).style.display = 'block';

			// Show checkout button.
			paymentForm[ 0 ].querySelector(
				'.simpay-checkout-btn-container'
			).style.display = 'block';
		}
	} );
}

const createPaymentMethod = {
	/**
	 * Creates a Card Payment Method.
	 *
	 * @param {Object} paymentForm
	 */
	card( paymentForm ) {
		const {
			stripeInstance: stripe,
			stripeInstance: { elements },
		} = paymentForm;

		return stripe
			.createPaymentMethod( {
				type: 'card',
				card: elements.card,
				billing_details: getOwnerData( paymentForm ),
			} )
			.then( ( result ) => {
				if ( result.error ) {
					throw result.error;
				}

				return result.paymentMethod;
			} );
	},
	/**
	 * Creates a ACH Direct Debit Payment Method.
	 *
	 * @param {Object} paymentForm
	 */
	async 'ach-debit'( paymentForm ) {
		const { state } = paymentForm;
		const { paymentMethodId } = state;

		return {
			id: paymentMethodId,
		};
	},
	/**
	 * Creates a SEPA Direct Debit Payment Method.
	 *
	 * @param {Object} paymentForm
	 */
	async 'sepa-debit'( paymentForm ) {
		const {
			state,
			stripeInstance: stripe,
			stripeInstance: { elements },
		} = paymentForm;

		const {
			customer_setupintent_nonce: customerNonce,
		} = simpayUpdatePaymentMethod;

		const { customerId } = state;

		// Create a SetupIntent for the Payment Method.
		const { client_secret: clientSecret } = await setupintents.create(
			{
				customer_id: customerId,
				customer_nonce: customerNonce,
				payment_method_types: [ 'sepa_debit' ],
			},
			paymentForm
		);

		// Confirm the Payment Method's information against the SetupIntent.
		const { error, setupIntent } = await stripe.confirmSepaDebitSetup(
			clientSecret,
			{
				payment_method: {
					sepa_debit: elements.sepaDebit,
					billing_details: getOwnerData( paymentForm ),
				},
			}
		);

		if ( error ) {
			throw error;
		}

		return {
			id: setupIntent.payment_method,
		};
	},
};

/**
 * Setup "Update Payment Method" form.
 */
function setup() {
	const formEl = document.getElementById(
		'simpay-form-update-payment-method'
	);

	if ( ! formEl ) {
		return;
	}

	const {
		id,
		customer_id: customerId,
		customer_nonce: customerNonce,
		subscription_id: subscriptionId,
		subscription_key: subscriptionKey,
		nonce: updateNonce,
		i18n,
	} = simpayUpdatePaymentMethod;

	// Mock legacy script variables and form data...
	const {
		[ id ]: {
			form: { config, bools, strings, i18n: formI18n },
			stripe,
		},
	} = window.simplePayForms;

	const { paymentMethods } = config;

	const __unstableLegacyFormData = {
		formDisplayType: 'embedded',
		checkoutButtonLoadingText: i18n.loading,
		checkoutButtonText: i18n.submit,
		stripeParams: {
			key: stripe.strings.key,
		},
		formId: id,
		isTestMode: bools.isTestMode,
		companyName: strings.companyName,
		stripeErrorMessages: formI18n.stripeErrorMessages,
	};

	// Mock a Payment Form.
	const paymentForm = jQuery.extend( jQuery( formEl ), {
		id,
		__unstableLegacyFormData,
		state: {
			paymentMethods,
			paymentMethod: _.first( paymentMethods ),
			customerId,
		},
		stripeInstance: Stripe( stripe.strings.key ),
		getElementStyle,
		getOwnerData,
	} );

	paymentForm.setState = function ( updatedState ) {
		paymentForm.state = {
			...paymentForm.state,
			...updatedState,
		};
	};

	paymentForm.enable = function () {
		paymentForm
			.removeClass( 'simpay-checkout-form--loading' )
			.find( '.simpay-checkout-btn' )
			.prop( 'disabled', false )
			.removeClass( 'simpay-disabled' )
			.text( i18n.submit );
	};

	paymentForm.disable = _.bind( onBaseDisable, paymentForm, paymentForm );
	paymentForm.error = _.bind( onBaseError, paymentForm, paymentForm );

	// Setup the form's Payment Methods.
	paymentMethods.forEach( ( { id: paymentMethodId } ) => {
		// Call the setup method.
		getPaymentMethod( paymentMethodId ).setup( paymentForm, true );

		// Add special setup for ACH Direct Debit.
		if ( 'ach-debit' === paymentMethodId ) {
			setupAchDirectDebit( paymentForm );
		}

		// Bind UI toggles to update the internal state.
		const paymentMethodToggleEl = paymentForm[ 0 ].querySelector(
			`.simpay-payment-method-toggle[data-payment-method="${ paymentMethodId }"]`
		);

		if ( ! paymentMethodToggleEl ) {
			return;
		}

		paymentMethodToggleEl.addEventListener( 'click', () => {
			paymentForm.setState( {
				paymentMethod: _.findWhere( paymentMethods, {
					id: paymentMethodId,
				} ),
			} );
		} );
	} );

	// Attach submission handler.
	formEl.addEventListener( 'submit', async ( e ) => {
		e.preventDefault();

		const { error: onError, disable: disableForm, state } = paymentForm;
		const { paymentMethod: selectedPaymentMethod } = state;

		disableForm();

		const paymentMethod = await createPaymentMethod[
			selectedPaymentMethod.id
		]( paymentForm ).catch( onError );

		if ( ! paymentMethod ) {
			return;
		}

		try {
			await subscriptions
				.updatePaymentMethod(
					customerId,
					customerNonce,
					subscriptionId,
					id,
					{
						payment_method_id: paymentMethod.id,
						_wpnonce: updateNonce,
						subscription_key: subscriptionKey,
					}
				)
				.catch( ( updateError ) => {
					throw updateError;
				} );

			window.location.replace( window.location.href );
		} catch ( _updateError ) {
			onError( _updateError );
		}
	} );
}

// DOM Ready.
domReady( setup );
