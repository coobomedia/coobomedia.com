/* global simpayAdmin, jQuery */

/**
 * Internal dependencies.
 */
import './form-settings/payment-methods.js';
import './form-settings/taxes.js';
import './form-settings/custom-fields.js';
import './form-settings/purchase-restrictions.js';
import './settings/emails.js';
import './settings/customers.js';
import './settings/taxes';
import './coupons';

const { hooks } = window.wpsp;

let spAdminPro = {};

( function ( $ ) {
	'use strict';

	let body, spFormSettings;

	spAdminPro = {
		init() {
			// We need to set these in here because this is when the page is ready to grab this info.
			body = $( document.body );
			spFormSettings = body.find( '#simpay-form-settings' );

			this.loadCustomFieldMetaboxes();

			// Setup datepicker fields
			this.addDatepicker();

			// Make custom fields sortable
			this.initSortableFields(
				spFormSettings.find( '.simpay-custom-fields' )
			);

			// Add custom fields fields
			spFormSettings
				.find( '.add-field' )
				.on( 'click.simpayAddField', this.addField );

			// Remove custom fields
			spFormSettings
				.find( '.simpay-custom-fields' )
				.on( 'click', '.simpay-remove-field-link', this.removeField );

			// Handle tax percent length
			$( '.simpay-tax-percent-field' ).on(
				'blur.simpayTaxPercentBlur',
				function () {
					spAdminPro.handleTaxPercent( $( this ) );
				}
			);
		},

		handleTaxPercent( el ) {
			el.val( accounting.toFixed( el.val(), 4 ) );
		},

		initSortableFields( el ) {
			// Field ordering
			el.sortable( {
				items:
					'.simpay-field-metabox:not(.simpay-custom-field-payment-button)',
				containment: '#simpay-form-settings',
				handle: '.simpay-hndle',
				placeholder: 'sortable-placeholder',
				cursor: 'move',
				delay: $( document.body ).hasClass( 'mobile' ) ? 200 : 0,
				distance: 2,
				tolerance: 'pointer',
				forcePlaceholderSize: true,
				opacity: 0.65,
				stop( e, ui ) {
					spAdminPro.orderFields();
				},

				// @link https://core.trac.wordpress.org/changeset/35809
				helper( event, element ) {
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
							return (
								'sort_' +
								parseInt(
									Math.random() * 100000,
									10
								).toString() +
								'_' +
								currentName
							);
						} )
						.end();
				},
			} );
		},

		addField( e ) {
			let size = spFormSettings.find(
					'.simpay-custom-fields .simpay-field-metabox'
				).length,
				totalFields = size,
				wrapper = $( '#simpay-custom-fields-wrap' ),
				boxes = wrapper.find( '.simpay-metaboxes' ),
				nonMaxFields = [
					'total_amount',
					'payment_button',
					'custom_amount',
					'plan_select',
				],
				selectField = spFormSettings.find( '#custom-field-select' ),
				fieldType = selectField.val(),
				currentId,
				data;

			boxes.find( '.simpay-field-metabox' ).each( function () {
				if (
					$( this ).hasClass(
						'simpay-custom-field-payment-button'
					) ||
					$( this ).hasClass( 'simpay-custom-field-custom-amount' ) ||
					$( this ).hasClass( 'simpay-custom-field-plan-select' ) ||
					$( this ).hasClass( 'simpay-custom-field-total-amount' )
				) {
					totalFields--;
				}
			} );

			const uids = [
				...document.querySelectorAll( '.field-uid' ),
			].map( ( field ) => parseInt( field.value ) );

			data = {
				action: 'simpay_add_field',
				post_id: $( '#post_ID' ).val(),
				fieldType,
				counter: parseInt( size ) + 1,
				nextUid: parseInt( _.max( uids ) ) + 1,
				addFieldNonce: spFormSettings
					.find( '#simpay_custom_fields_nonce' )
					.val(),
			};

			e.preventDefault();

			spFormSettings.find( '.simpay-field-data' ).each( function () {
				if ( $( this ).is( ':visible' ) ) {
					$( this ).hide();
					$( this ).addClass( 'closed' );
				}
			} );

			$.ajax( {
				url: ajaxurl,
				method: 'POST',
				data,
				success( response ) {
					const temp = $( '<div/>' ).append( response );

					spAdminPro.orderFields();

					boxes.append( temp.html() );

					// If it is a date field we need to rerun the datepicker to make sure it is added to the dynamic element
					if ( 'date' === fieldType ) {
						spAdminPro.addDatepicker();
					}

					// Reset <select>.
					selectField.prop( 'selectedIndex', 0 );

					hooks.doAction( 'customFieldAdded', response );
				},
				error( response ) {
					window.spShared.debugLog( response );
				},
			} );
		},

		removeField( e ) {
			e.preventDefault();

			const selectField = spFormSettings.find( '#custom-field-select' );

			if (
				window.confirm( 'Are you sure you want to remove this field?' )
			) {
				const metabox = $( this ).closest( '.simpay-field-metabox' );
				const fieldType = metabox.data( 'type' );

				metabox.remove();
				hooks.doAction( 'customFieldRemoved' );
			}
		},

		orderFields() {
			$( '.simpay-custom-fields .simpay-field-metabox' ).each( function (
				index,
				el
			) {
				const fieldIndex = parseInt(
					$( el ).index(
						'.simpay-custom-fields .simpay-field-metabox'
					)
				);

				$( '.field-order', el ).val( fieldIndex + 1 );
			} );
		},

		loadCustomFieldMetaboxes() {
			const simpayCustomFields = body.find( '.simpay-custom-fields' ),
				simpayCustomFieldsMetaBox = simpayCustomFields
					.find( '.simpay-field-metabox' )
					.get();

			// First we need to sort all the custom fields by their "rel" attribute
			simpayCustomFieldsMetaBox.sort( function ( a, b ) {
				const compA = parseInt( $( a ).attr( 'rel' ), 10 );
				const compB = parseInt( $( b ).attr( 'rel' ), 10 );
				return compA < compB ? -1 : compA > compB ? 1 : 0;
			} );

			// After being sorted we append each one to the main content area where they are viewed
			$( simpayCustomFieldsMetaBox ).each( function ( idx, itm ) {
				simpayCustomFields.append( itm );
			} );
		},

		addDatepicker() {
			const dateInput = $( '.simpay-date-input' );

			dateInput.datepicker( {
				dateFormat: simpayAdmin.i18n.dateFormat,
				beforeShow() {
					$( '#ui-datepicker-div' )
						.removeClass( 'ui-datepicker' )
						.addClass( 'simpay-datepicker' );
				},
			} );

			if ( '' === dateInput.val() ) {
				return;
			}

			dateInput.datepicker( 'setDate', new Date( dateInput.val() ) );
		},
	};

	$( document ).ready( function ( $ ) {
		spAdminPro.init();
	} );
} )( jQuery );
