/**
 * Internal dependencies.
 */
import { default as submitBankRedirect } from './../utils/one-time-redirect-submit.js';

/** @typedef {import('@wpsimplepay/payment-forms').PaymentForm} PaymentForm */

/**
 * Submit the giropay Payment Method.
 *
 * @param {PaymentForm} paymentForm
 */
function submit( paymentForm ) {
	const { getOwnerData } = paymentForm;
	const { address, email, name, phone } = getOwnerData( paymentForm );

	submitBankRedirect( paymentForm, 'giropay', 'confirmGiropayPayment', {
		payment_method: {
			billing_details: {
				address,
				email,
				name,
				phone,
			},
		},
	} );
}

export default submit;
