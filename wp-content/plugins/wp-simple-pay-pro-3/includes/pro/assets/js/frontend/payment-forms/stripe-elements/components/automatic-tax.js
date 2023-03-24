/* global _, jQuery, spGeneral */

/**
 * External dependencies.
 */
import serialize from 'form-serialize';

/**
 * Internal dependencies.
 */
const { convertToDollars, formatCurrency } = window.spShared;
import { orders } from '@wpsimplepay/api';

/** @typedef {import('@wpsimplepay/payment-forms').PaymentForm} PaymentForm */

/**
 * Builds HTML for address fields using data returned from the order preview.
 *
 * @since 4.6.0
 *
 * @param {PaymentForm} paymentForm Payment form.
 * @param {Object} addressFields Address field data.
 */
function buildAddressFields( paymentForm, addressFields ) {
	const { id, state } = paymentForm;
	const { addressFieldSelection } = state;
	const addressTypes = [ 'billing', 'shipping' ];

	addressTypes.forEach( ( addressType ) => {
		const addressFieldsEl = paymentForm[ 0 ].querySelector(
			`.simpay-${ addressType }-address-container .simpay-address-container`
		);

		if ( ! addressFieldsEl ) {
			return;
		}

		addressFieldsEl.innerHTML = '';

		Object.entries( addressFields[ addressType ] ).forEach(
			( [ key, field ] ) => {
				let type;

				switch ( key ) {
					case 'line1':
						type = 'street';
						break;
					case 'postal_code':
						type = 'zip';
						break;
					default:
						type = key;
				}

				// Outer wrapper.
				const fieldWrapEl = document.createElement( 'div' );
				fieldWrapEl.classList.add(
					'simpay-form-control',
					`simpay-address-${ type }-container`
				);

				// Label wrapper.
				const fieldLabelWrapEl = document.createElement( 'div' );
				fieldLabelWrapEl.classList.add(
					'simpay-address-label',
					'simpay-label-wrap'
				);

				// Label.
				const fieldLabelEl = document.createElement( 'label' );
				fieldLabelEl.for = `simpay-form-${ id }-field-${ addressType }-${ key }`;
				fieldLabelEl.innerText = field.label;

				// Control wrapper.
				const fieldControlWrapEl = document.createElement( 'div' );
				fieldControlWrapEl.classList.add(
					`simpay-${ type }-wrap`,
					'simpay-field-wrap'
				);

				// Control.
				const fieldControlEl =
					'' === field.options
						? document.createElement( 'input' )
						: document.createElement( 'select' );
				fieldControlEl.name = `simpay_${ addressType }_address_${ key }`;
				fieldControlEl.id = `simpay-form-${ id }-field-${ addressType }-${ key }`;
				fieldControlEl.classList.add( `simpay-address-${ type }` );
				fieldControlEl.required = true;

				if ( '' === field.options ) {
					fieldControlEl.type = 'text';
					fieldControlEl.value = field.value;
					fieldControlEl.placeholder = field.placeholder ?? '';
				} else {
					Object.keys( field.options ).forEach( ( optionIndex ) => {
						const optionEl = document.createElement( 'option' );
						optionEl.value = optionIndex;
						optionEl.innerText = field.options[ optionIndex ];
						optionEl.selected = field.value === optionIndex;

						fieldControlEl.appendChild( optionEl );
					} );
				}

				// Put label in label wrap.
				fieldLabelWrapEl.appendChild( fieldLabelEl );

				// Put control in control wrap.
				fieldControlWrapEl.appendChild( fieldControlEl );

				// Put label and control in outer wrapper.
				fieldWrapEl.appendChild( fieldLabelWrapEl );
				fieldWrapEl.appendChild( fieldControlWrapEl );

				// Put outer wrapper in address container.
				addressFieldsEl.appendChild( fieldWrapEl );
			}
		);
	} );

	bindAddressFields( paymentForm );

	if ( addressFieldSelection ) {
		paymentForm[ 0 ]
			.querySelector( `[name="${ addressFieldSelection }"` )
			.focus();
	} else {
		paymentForm[ 0 ].querySelector( '.simpay-address-country' ).focus();
	}

	const taxValueEl = paymentForm[ 0 ].querySelector(
		'.simpay-automatic-tax-label .simpay-tax-amount-value'
	);

	// Show the toggle.
	const shippingToggleFieldEl = paymentForm[ 0 ].querySelector(
		'.simpay-same-address-toggle-container'
	);

	if ( shippingToggleFieldEl ) {
		shippingToggleFieldEl.style.display = 'block';
	}

	// Alert the user an address is required.
	const { addressRequired } = spGeneral.strings;

	if ( false === hasCompleteAddress( paymentForm ) ) {
		if ( taxValueEl ) {
			taxValueEl.innerHTML = addressRequired;
			taxValueEl.classList.add( 'is-empty' );
		}

		resetTaxAmount( paymentForm );
		toggleTotalChangedVisual( paymentForm );
	}
}

/**
 * Creates or updates an Order Preview (not a Stripe Order) to calculate automatic tax.
 *
 * @since 4.6.0
 *
 * @param {PaymentForm} paymentForm The payment form instance.
 * @return {Object} The updated Order preview.
 */
async function createOrUpdateOrderPreview( paymentForm ) {
	const {
		cart,
		disable: disableForm,
		enable: enableForm,
		error: onError,
		getFormData,
		getOwnerData,
		state,
		setState,
	} = paymentForm;
	const { isSubscription, isRecurring, order, paymentMethod } = state;

	disableForm();

	const baseLineItem = cart.getLineItem( 'base' );
	const addressType = getAddressType( paymentForm );
	const { address, email, name, shippingAddress } = getOwnerData(
		paymentForm
	);
	const shippingToggleFieldEl = paymentForm[ 0 ].querySelector(
		'.simpay-same-address-toggle'
	);

	// Determine the order (WP Simple Pay, not Stripe) ID. If an order exists
	// in the state, ensure it is compatible with the current price option
	// recurring settings.
	let orderId = order ? order.id : null;

	if (
		orderId &&
		orderId.includes( 'sub_' ) &&
		! ( isSubscription || isRecurring )
	) {
		orderId = null;
	} else if (
		orderId &&
		! orderId.includes( 'sub_' ) &&
		( isSubscription || isRecurring )
	) {
		orderId = null;
	}

	const orderArgs = {
		object_id: orderId,
		payment_method_type: paymentMethod.id,
		form_id: paymentForm.id,
		currency: baseLineItem.price.currency,
		line_items: filteredLineItems,
		billing_details: {
			name,
			email,
			address,
		},
		shipping_details: {
			name,
			address: shippingAddress,
		},
		automatic_tax: {
			address_type: addressType,
			current_address_country: order
				? order[ `${ addressType }_details` ]?.address.country
				: '',
			next_address_country:
				'shipping' === addressType
					? shippingAddress.country
					: address.country,
			is_shipping_address_same_as_billing:
				shippingToggleFieldEl && shippingToggleFieldEl.checked === true,
		},
		coupon: cart.coupon || null,
		// Helper to determine if the order is a subscription. This saves us from
		// needing to loop through line items in the REST API. This might change.
		__unstable_is_recurring: isSubscription || isRecurring,
		form_values: serialize( paymentForm[ 0 ], { hash: true } ),
		form_data: getFormData(),
	};

	// Build line items.
	// Remove fee line items from the cart. For some reason using the `getLineItems` method
	// and passing directly causes an error. Use .reduce() to return a new array.
	const filteredLineItems = cart
		.getLineItems()
		.reduce( ( lineItems, lineItem ) => {
			if ( ! lineItem.price ) {
				return lineItems;
			}

			lineItems.push( {
				price: lineItem.price.id,
				quantity: lineItem.quantity,
				unit_amount: lineItem.amount,
				recurring: lineItem.price.recurring,
			} );

			return lineItems;
		}, [] );

	orderArgs.line_items = filteredLineItems;

	// Preview order.
	const orderPreview = await orders.preview( orderArgs ).catch( onError );

	if ( ! orderPreview ) {
		return;
	}

	const { order: updatedOrder, address_fields: addressFields } = orderPreview;

	buildAddressFields( paymentForm, addressFields );

	// Store the updated order in the state.
	setState( {
		order: updatedOrder,
	} );

	// Update the cart with the updated order information.
	const cartUpdate = {
		taxBehavior: updatedOrder.tax.behavior,
		automaticTax: {
			...updatedOrder.total_details,
		},
	};

	if ( updatedOrder.upcoming_invoice ) {
		cartUpdate.automaticTax.upcomingInvoice =
			updatedOrder.upcoming_invoice.total_details;
	}

	cart.update( cartUpdate );

	// Reenable form (which ensures UI is up to date).
	enableForm();

	return updatedOrder;
}

/**
 * Updates tax amounts if a complete address is available.
 *
 * @since 4.6.0
 * @param {Object|null} e Event object, or null.
 * @param {PaymentForm} paymentForm The payment form instance.
 */
async function updateTax( e, paymentForm ) {
	const { addressRequired, addressInvalid } = spGeneral.strings;
	const {
		state: { order },
		getOwnerData,
		error: onError,
	} = paymentForm;
	const { address } = getOwnerData( paymentForm );

	if ( null === address.country ) {
		return;
	}

	// Clear errors.
	onError( '' );

	const taxValueEl = paymentForm[ 0 ].querySelector(
		'.simpay-automatic-tax-label .simpay-tax-amount-value'
	);

	// Reset tax amount and show "Enter an address" messsage if the form is not complete.
	if (
		order &&
		false === hasCompleteAddress( paymentForm ) &&
		e &&
		// If either country field changes, allow it to be updated.
		! (
			e.target.name === `simpay_billing_address_country` ||
			e.target.name === `simpay_shipping_address_country`
		)
	) {
		if ( taxValueEl ) {
			taxValueEl.innerHTML = addressRequired;
			taxValueEl.classList.add( 'is-empty' );
		}

		resetTaxAmount( paymentForm );

		return;
	}

	const updatedOrder = await createOrUpdateOrderPreview( paymentForm );

	if ( ! updatedOrder ) {
		return;
	}

	const {
		automatic_tax: automaticTax,
		total_details: totalDetails,
	} = updatedOrder;

	// Do not proceed if the address is invalid.
	if (
		'complete' !== automaticTax.status &&
		true === hasCompleteAddress( paymentForm )
	) {
		if ( taxValueEl ) {
			taxValueEl.innerHTML = addressInvalid;
			taxValueEl.classList.add( 'is-invalid' );
			taxValueEl.classList.remove( 'is-empty' );
		}

		resetTaxAmount( paymentForm );
		return;
	}

	if ( false === hasCompleteAddress( paymentForm ) ) {
		if ( taxValueEl ) {
			taxValueEl.innerHTML = addressRequired;
			taxValueEl.classList.add( 'is-empty' );
			taxValueEl.classList.remove( 'is-invalid' );
		}

		resetTaxAmount( paymentForm );
		return;
	}

	const { cart } = paymentForm;

	// Trigger `totalChanged` event to update the cart totals.
	toggleTotalChangedVisual( paymentForm );

	// Update tax amount.
	if ( taxValueEl ) {
		taxValueEl.classList.remove( 'is-empty' );
		taxValueEl.classList.remove( 'is-invalid' );
		taxValueEl.innerHTML = formatCurrency(
			cart.isZeroDecimal()
				? totalDetails.amount_tax
				: convertToDollars( totalDetails.amount_tax ),
			true,
			cart.getCurrencySymbol(),
			cart.isZeroDecimal()
		);
	}
}

/**
 * Binds each address field to potentially update the tax amount when changed.
 *
 * @since 4.6.0
 *
 * @param {PaymentForm} paymentForm The payment form instance.
 */
function bindAddressFields( paymentForm ) {
	const addressType = getAddressType( paymentForm );

	// Retrieve address fields for the current address used for tax calculation.
	const addressFields = [
		...paymentForm[ 0 ].querySelectorAll(
			`.simpay-${ addressType }-address-container input, .simpay-${ addressType }-address-container select:not(.simpay-address-country)`
		),
	];

	// Ensure both Billing and Shipping country fields are always bound.
	const shippingCountryEl = paymentForm[ 0 ].querySelector(
		'[name="simpay_shipping_address_country"]'
	);

	if ( shippingCountryEl ) {
		addressFields.push( shippingCountryEl );
	}

	const billingCountryEl = paymentForm[ 0 ].querySelector(
		'[name="simpay_billing_address_country"]'
	);

	if ( billingCountryEl ) {
		addressFields.push( billingCountryEl );
	}

	const updateTaxListener = ( e ) => updateTax( e, paymentForm );

	// Bind address fields.
	addressFields.forEach( ( field ) => {
		field.removeEventListener( 'change', updateTaxListener );
		field.addEventListener( 'change', updateTaxListener );
	} );

	// Track selection to restore after the order is updated.
	const allFields = paymentForm[ 0 ].querySelectorAll( 'input, select' );

	allFields.forEach( ( field ) => {
		field.addEventListener( 'focusin', ( e ) => {
			paymentForm.setState( {
				addressFieldSelection: e.target.name,
			} );
		} );
	} );
}

/**
 * Binds the "Same as billing address" checkbox to potentially update the tax amount when changed.
 *
 * @since 4.6.0
 *
 * @param {PaymentForm} paymentForm The payment form instance.
 */
function bindShippingAddressToggle( paymentForm ) {
	updateTax( null, paymentForm );

	const shippingToggleFieldEl = paymentForm[ 0 ].querySelector(
		'.simpay-same-address-toggle'
	);

	if ( ! shippingToggleFieldEl ) {
		return;
	}

	// Update address type when toggling.
	shippingToggleFieldEl.addEventListener( 'change', ( e ) => {
		updateTax( e, paymentForm );
		bindAddressFields( paymentForm );
	} );
}

/**
 * Determines if the address is complete.
 *
 * @since 4.6.0
 *
 * @param {PaymentForm} paymentForm The payment form instance.
 * @return {boolean} True if the address is complete.
 */
function hasCompleteAddress( paymentForm ) {
	const addressType = getAddressType( paymentForm );
	const { address, shippingAddress } = paymentForm.getOwnerData(
		`.simpay-${ addressType }-address-container`
	);

	const addressData = addressType === 'shipping' ? shippingAddress : address;
	const completeAddressFields = Object.entries( addressData ).filter(
		( [ , value ] ) => value !== null
	);

	return completeAddressFields.length === 5;
}

/**
 * Returns the address type that should be used for tax calculation.
 *
 * @since 4.6.0
 *
 * @param {PaymentForm} paymentForm The payment form instance.
 * @return {string} The address type.
 */
function getAddressType( paymentForm ) {
	const shippingToggleFieldEl = paymentForm[ 0 ].querySelector(
		'.simpay-same-address-toggle'
	);

	return shippingToggleFieldEl && ! shippingToggleFieldEl.checked
		? 'shipping'
		: 'billing';
}

/**
 * Resets the automatic tax amount.
 *
 * @since 4.6.0
 *
 * @param {PaymentForm} paymentForm The payment form instance.
 */
function resetTaxAmount( paymentForm ) {
	const { cart } = paymentForm;

	cart.update( {
		automaticTax: {},
	} );

	toggleTotalChangedVisual( paymentForm );
}

/**
 * Toggles the `totalChanged` event without causing an infinite loop to occur.
 * `updateTax` is bound to `totalChanged` event, and needs to be removed.
 *
 * @since 4.6.0
 *
 * @param {PaymentForm} paymentForm The payment form instance.
 */
function toggleTotalChangedVisual( paymentForm ) {
	paymentForm.off( 'totalChanged', updateTax );
	paymentForm.trigger( 'totalChanged', [ paymentForm ] );
	paymentForm.on( 'totalChanged', updateTax );
}

/**
 * Bind events to Payment Form.
 */
jQuery( document.body ).on(
	'simpayBindCoreFormEventsAndTriggers',
	// eslint-disable-next-line no-unused-vars
	( e, paymentForm ) => {
		const {
			cart: { taxStatus },
			state,
		} = paymentForm;
		const { displayType } = state;

		// Do nothing if automatic tax is not enabled, or using Stripe Checkout.
		// Stripe Checkout and automatic tax restrict addresses to off-site.
		if ( 'automatic' !== taxStatus || 'stripe_checkout' === displayType ) {
			return;
		}

		// Bind address fields.
		bindAddressFields( paymentForm );

		// Bind shipping address toggle.
		bindShippingAddressToggle( paymentForm );

		// Update tax when the total changes.
		paymentForm.on( 'totalChanged', updateTax );

		// Potentially block submission based on Element status.
		paymentForm.on( 'simpayBeforeStripePayment', () => {
			const {
				error: onError,
				state: { order },
				__unstableLegacyFormData,
			} = paymentForm;

			// Already invalid, do not perform additional checks.
			if ( false === __unstableLegacyFormData.isValid ) {
				return;
			}

			const isValid = 'complete' === order.automatic_tax.status;

			__unstableLegacyFormData.isValid = isValid;

			if ( ! isValid ) {
				onError( window.spGeneral.strings.addressInvalid );
			}
		} );
	}
);
