/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
const { hooks } = window.wpsp;

/**
 * Builds an inventory entry for a price option.
 *
 * @since 4.6.4
 *
 * @param {string} id Price option instance.
 * @return {HTMLElement} Inventory entry.
 */
function buildInventoryEntry( id ) {
	const wrapperEl = document.createElement( 'div' );
	wrapperEl.id = `inventory-${ id }`;
	wrapperEl.classList.add( 'simpay-form-builder-inset-settings' );
	wrapperEl.classList.add(
		'simpay-form-builder-purchase-restrictions__restriction-item'
	);
	wrapperEl.classList.add( 'simpay-show-if' );
	wrapperEl.dataset.if = '_inventory_behavior';
	wrapperEl.dataset.is = 'individual';

	const inventoryControlEl = document.createElement( 'div' );
	inventoryControlEl.classList.add( 'simpay-form-builder-inventory-control' );
	wrapperEl.appendChild( inventoryControlEl );

	const inventoryControlInputEl = document.createElement( 'input' );
	inventoryControlInputEl.type = 'number';
	inventoryControlInputEl.min = 0;
	inventoryControlInputEl.step = 1;
	inventoryControlInputEl.placeholder = 100;
	inventoryControlInputEl.name = `_inventory_behavior_individual[${ id }]`;
	inventoryControlInputEl.id = `_inventory_behavior_individual-${ id }`;
	inventoryControlEl.appendChild( inventoryControlInputEl );

	const displayLabelEl = document.querySelector(
		`#${ id } .simpay-price-label-display`
	);
	const inventoryLabelEl = document.createElement( 'label' );
	inventoryLabelEl.innerText = displayLabelEl.innerText;
	inventoryLabelEl.htmlFor = `_inventory_behavior_individual-${ id }`;
	wrapperEl.appendChild( inventoryLabelEl );

	return wrapperEl;
}

/**
 * Removes an inventory entry when a price option is removed.
 *
 * @since 4.6.4
 *
 * @param {string} id Price option instance ID.
 */
function removeInventoryEntry( id ) {
	const inventoryEntryEl = document.getElementById( `inventory-${ id }` );

	if ( ! inventoryEntryEl ) {
		return;
	}

	inventoryEntryEl.remove();
}

/**
 * Builds all inventory entries for all price options.
 *
 * @since 4.6.4
 */
function buildInventoryEntries() {
	const inventoryWrapper = document.getElementById(
		'simpay-form-builder-inventory-individual'
	);

	if ( ! inventoryWrapper ) {
		return;
	}

	const priceOptionEls = document.querySelectorAll(
		'.simpay-metabox.simpay-price'
	);

	const isIndividualInventory = document.getElementById(
		'_inventory_behavior_individual'
	).checked;

	[ ...priceOptionEls ].forEach( ( priceOptionEl ) => {
		if (
			inventoryWrapper.querySelector( `#inventory-${ priceOptionEl.id }` )
		) {
			return;
		}

		const inventoryEntry = buildInventoryEntry( priceOptionEl.id );

		if ( isIndividualInventory ) {
			inventoryEntry.style.display = 'block';
		}

		inventoryWrapper.appendChild( inventoryEntry );
	} );
}

/**
 * Updates an inventory entry label when a price option label changes.
 *
 * @since 4.6.4
 *
 * @param {string} label New price option label.
 * @param {HTMLElement} priceEl Price option element.
 */
function updateInventoryEntryLabels( label, priceEl ) {
	const id = priceEl.id;

	const labelEl = document.querySelector(
		`[for="_inventory_behavior_individual-${ id }"]`
	);

	if ( ! labelEl ) {
		return;
	}

	labelEl.innerText = label;
}

domReady( () => {
	// Add inventory items.
	buildInventoryEntries();

	hooks.addAction(
		'simpayFormBuilderPriceAdded',
		'wpsp/formBuilder',
		buildInventoryEntries
	);

	// Update inventory items.
	hooks.addAction(
		'simpayFormBuilderPriceOptionLabelUpdated',
		'wpsp/formBuilder',
		updateInventoryEntryLabels
	);

	// Remove inventory items.
	hooks.addAction(
		'simpayFormBuilderPriceRemoved',
		'wpsp/formBuilder',
		removeInventoryEntry
	);
} );
