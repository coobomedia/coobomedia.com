/* global _ */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
const { hooks } = window.wpsp;

function smartTagFeedback() {
	const smartTagInputEls = document.querySelectorAll(
		'.simpay-field-smart-tag'
	);

	if ( ! smartTagInputEls || 0 === smartTagInputEls.length ) {
		return;
	}

	smartTagInputEls.forEach( ( smartTagInputEl ) => {
		smartTagInputEl.addEventListener( 'keyup', ( event ) => {
			const id = event.target.id;
			const value = event.target.value;
			const smartTagFeedbackEl = document.getElementById(
				`${ id }-smart-tag`
			);

			if ( ! smartTagFeedbackEl ) {
				return;
			}

			if ( '' === value ) {
				smartTagFeedbackEl.style.display = 'none';
			} else {
				smartTagFeedbackEl.style.display = 'flex';
			}

			const copyButtonEl = smartTagFeedbackEl.querySelector( 'button' );

			if ( copyButtonEl ) {
				copyButtonEl.dataset.clipboardText = `{payment:metadata:${ value }}`;
			}

			const previewEl = smartTagFeedbackEl.querySelector( 'code' );

			if ( previewEl ) {
				previewEl.innerText = `{payment:metadata:${ value }}`;
			}
		} );
	} );
}
hooks.addAction( 'customFieldAdded', 'wpsp/payment-form', smartTagFeedback );

/**
 * Update field list when necessary.
 */
domReady( () => {
	const formTypeSelectEl = document.getElementById( 'form-type-select' );

	if ( formTypeSelectEl ) {
		formTypeSelectEl.addEventListener( 'change', updateCustomFieldList );
	}

	const taxStatusEl = document.getElementById( '_tax_status' );

	if ( taxStatusEl ) {
		taxStatusEl.addEventListener( 'change', updateCustomFieldList );
	}

	updateCustomFieldList();
	smartTagFeedback();
} );

hooks.addAction(
	'customFieldAdded',
	'wpsp/payment-form',
	updateCustomFieldList
);
hooks.addAction(
	'customFieldRemoved',
	'wpsp/payment-form',
	updateCustomFieldList
);

/**
 * Updates the field list to set certain fields disabled or enabled.
 */
function updateCustomFieldList() {
	const customFieldSelector = document.getElementById(
		'custom-field-select'
	);

	if ( ! customFieldSelector ) {
		return;
	}

	const options = customFieldSelector.querySelectorAll( 'option' );

	_.each( options, ( option ) => {
		const disabled = hooks.applyFilters(
			'isCustomFieldDisabled',
			false,
			option
		);
		option.disabled = disabled;
	} );
}

/**
 * Determines if a one-time use field is disabled.
 *
 * @param {boolean} disabled If the option to add the field is disabled.
 */
hooks.addFilter(
	'isCustomFieldDisabled',
	'wpsp/payment-form',
	( disabled, option ) => {
		const repeatable = 'true' === option.dataset.repeatable;

		if ( repeatable ) {
			return disabled;
		}

		const existingField = document.querySelector(
			`.simpay-custom-field-${ option.value.replace( /_/g, '-' ) }`
		);

		return null !== existingField;
	},
	10
);

/**
 * Determines if the "Payment Button" is disabled.
 *
 * @param {boolean} disabled If the option to add the field is disabled.
 */
hooks.addFilter(
	'isCustomFieldDisabled',
	'wpsp/payment-form',
	( disabled, option ) => {
		if ( 'payment_button' !== option.value ) {
			return disabled;
		}

		if ( true === disabled ) {
			return disabled;
		}

		const formTypeEl = document.getElementById( 'form-type-select' );
		const formType = formTypeEl.options[ formTypeEl.selectedIndex ].value;

		return 'off-site' !== formType;
	},
	20
);

/**
 * Determines if the "Address" field is disabled.
 *
 * @param {boolean} disabled If the option to add the field is disabled.
 */
hooks.addFilter(
	'isCustomFieldDisabled',
	'wpsp/payment-form',
	( disabled, option ) => {
		if ( 'address' !== option.value ) {
			return disabled;
		}

		if ( true === disabled ) {
			return disabled;
		}

		const formTypeEl = document.getElementById( 'form-type-select' );
		const formType = formTypeEl.options[ formTypeEl.selectedIndex ].value;

		const taxStatusEl = document.getElementById( '_tax_status' );
		const taxStatus =
			taxStatusEl.options[ taxStatusEl.selectedIndex ].value;

		return 'off-site' === formType && 'automatic' === taxStatus;
	},
	20
);

/**
 * Determines if the "Payment Methods" and "Checkout Button" is disabled
 * for Stripe Checkout.
 *
 * @param {boolean} disabled If the option to add the field is disabled.
 */
hooks.addFilter(
	'isCustomFieldDisabled',
	'wpsp/payment-form',
	( disabled, option ) => {
		if ( ! [ 'card', 'checkout_button' ].includes( option.value ) ) {
			return disabled;
		}

		if ( true === disabled ) {
			return disabled;
		}

		const formTypeEl = document.getElementById( 'form-type-select' );
		const formType = formTypeEl.options[ formTypeEl.selectedIndex ].value;

		return 'off-site' === formType;
	},
	50
);

/**
 * Determines if the "Apple Pay / Google Pay" field is disabled.
 * Disabled when using Stripe Checkout.
 *
 * @param {boolean} disabled If the option to add the field is disabled.
 */
hooks.addFilter(
	'isCustomFieldDisabled',
	'wpsp/payment-form',
	( disabled, option ) => {
		if ( 'payment_request_button' !== option.value ) {
			return disabled;
		}

		if ( true === disabled ) {
			return disabled;
		}

		const formTypeEl = document.getElementById( 'form-type-select' );
		const formType = formTypeEl.options[ formTypeEl.selectedIndex ].value;

		return 'off-site' === formType;
	},
	60
);
