/* global $, simpayAdmin */

/**
 * Internal dependencies.
 */
import { maybeBlockCheckboxWithUpgradeModal } from '@wpsimplepay/utils';

/**
 * Toggles the Payment Method or opens an upgrade modal.
 *
 * @since 4.4.7
 *
 * @param {Event} e Change event.
 * @param {Event} e.target Payment Method togggle.
 * @param {Object} paymentMethod Payment Method data.
 * @param {string} paymentMethod.id Payment Method ID.
 * @param {Object} paymentMethod.licenses Payment method licenses.
 */
function onToggle( e, { id, licenses } ) {
	const { licenseLevel } = simpayAdmin;
	const { target } = e;

	// Upgrade required.
	if ( ! licenses.includes( licenseLevel ) ) {
		maybeBlockCheckboxWithUpgradeModal( e );

		// Show more information.
	} else {
		// Restrictions.
		const restrictions = target.parentNode.nextElementSibling;

		restrictions.style.display = target.checked ? 'block' : 'none';

		// Toggle icon in the "Payment Methods" custom field title.
		const paymentMethodIcon = document.querySelector(
			`.simpay-payment-method-title-icon-${ id }`
		);

		if ( paymentMethodIcon ) {
			paymentMethodIcon.style.display = target.checked ? 'flex' : 'none';
		}
	}
}

/**
 * Opens a jQuery UI dialog to configure the Payment Method.
 *
 * @since 3.8.0
 *
 * @param {Object} paymentMethod Payment Method data.
 * @param {string} paymentMethod.id Payment Method ID.
 */
function onConfigure( { id } ) {
	$( `#simpay-payment-method-configure-${ id }` ).dialog( {
		position: {
			my: 'center',
			at: 'center',
			of: window,
		},
		modal: true,
		width: 400,
		resizable: false,
		draggable: false,
		appendTo: $( `label[for="simpay-payment-method-${ id }"]` ).parent(),
		open( event ) {
			$( event.target )
				.find( '.update' )
				.on( 'click', ( clickEvent ) => {
					clickEvent.preventDefault();

					$( this ).dialog( 'close' );
				} );
		},
	} );
}

/**
 * Binds events.
 *
 * @since 4.1.0
 */
function bindEvents() {
	// Payment Method.
	const paymentMethods = document.querySelectorAll(
		'.simpay-panel-field-payment-method'
	);

	if ( 0 === paymentMethods.length ) {
		return;
	}

	[ ...paymentMethods ].forEach( ( paymentMethod ) => {
		// Payment Method data.
		const pm = JSON.parse( paymentMethod.dataset.paymentMethod );

		// Toggle.
		const toggle = paymentMethod.querySelector( '.simpay-payment-method' );

		toggle.addEventListener( 'change', ( e ) => onToggle( e, pm ) );

		// Configure.
		const configureButtons = paymentMethod.querySelectorAll(
			'.simpay-panel-field-payment-method__configure'
		);

		[ ...configureButtons ].forEach( ( button ) => {
			button.addEventListener( 'click', ( e ) => {
				e.preventDefault();
				onConfigure( pm );
			} );
		} );
	} );
}

/**
 * Binds jQuery UI Sortable to Payment Methods.
 *
 * @since 4.2.0
 */
function bindSortablePaymentMethods() {
	$( '.simpay-payment-methods' ).each( function () {
		$( this ).sortable( {
			handle: '.simpay-panel-field-payment-method__move',
			placeholder: 'sortable-placeholder',
			cursor: 'move',
			delay: $( document.body ).hasClass( 'mobile' ) ? 200 : 0,
			distance: 2,
			tolerance: 'pointer',
			forcePlaceholderSize: true,
			opacity: 0.65,

			// @link https://core.trac.wordpress.org/changeset/35809
			helper( _event, element ) {
				/* `helper: 'clone'` is equivalent to `return element.clone();`
				 * Cloning a checked radio and then inserting that clone next to the original
				 * radio unchecks the original radio (since only one of the two can be checked).
				 * We get around this by renaming the helper's inputs' name attributes so that,
				 * when the helper is inserted into the DOM for the sortable, no radios are
				 * duplicated, and no original radio gets unchecked.
				 */
				return element
					.clone()
					.find( ':input' )
					.attr( 'name', function ( i, currentName ) {
						const rand = parseInt(
							Math.random() * 100000,
							10
						).toString();

						return `sort_${ rand }_${ currentName }`;
					} )
					.end();
			},
		} );
	} );
}

/**
 * DOM ready.
 */
$( document ).ready( () => {
	bindEvents();
	bindSortablePaymentMethods();
} );
