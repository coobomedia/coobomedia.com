/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { maybeBlockSelectWithUpgradeModal } from '@wpsimplepay/utils';

/**
 * Disable payment methods that are incompatible with automatic taxes.
 *
 * @sicne 4.6.0
 */
domReady( () => {
	const selector = document.getElementById( '_tax_status' );

	if ( selector ) {
		selector.addEventListener( 'change', maybeBlockSelectWithUpgradeModal );

		const restrictedPaymentMethods = [ 'ach-debit', 'fpx' ];

		restrictedPaymentMethods.forEach( ( paymentMethod ) => {
			selector.addEventListener( 'change', ( e ) => {
				const paymentMethodInput = document.querySelector(
					`input[value="${ paymentMethod }"]`
				);

				const formTypeEl = document.querySelector(
					'[name="_form_type"]'
				);

				paymentMethodInput.disabled =
					'automatic' === e.target.value &&
					formTypeEl.value !== 'off-site';
			} );
		} );
	}
} );
