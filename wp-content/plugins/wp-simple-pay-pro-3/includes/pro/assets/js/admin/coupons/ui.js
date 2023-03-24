/**
 * Internal dependencies
 */
import { upgradeModal } from '@wpsimplepay/utils';

/**
 * Ensures only alphanumeric, underscores, and dashes can be entered in the coupon name.
 *
 * @since 4.3.0
 */
export function couponNameRestrictions() {
	const nameInput = document.getElementById( 'coupon-name' );
	const ALLOWED_CHARS = /^[a-zA-Z0-9-_]+$/;

	function restrictInput( e ) {
		if ( ! ALLOWED_CHARS.test( e.key ) ) {
			e.preventDefault();
		}
	}

	nameInput.addEventListener( 'keydown', restrictInput );
}

/**
 * Toggles inputs related to "Type" selection.
 *
 * @since 4.3.0
 */
export function discountTypeToggle() {
	const type = document.getElementById( 'coupon-type' );
	const amountOffWrap = document.getElementById( 'coupon-type-amount_off' );
	const amountOffInput = document.getElementById( 'coupon-amount_off' );
	const percentOffWrap = document.getElementById( 'coupon-type-percent_off' );
	const percentOffInput = document.getElementById( 'coupon-percent_off' );

	type.addEventListener( 'change', function () {
		amountOffWrap.style.display =
			'amount_off' === type.value ? 'table-row' : 'none';
		amountOffInput.required = 'amount_off' === type.value;

		percentOffWrap.style.display =
			'percent_off' === type.value ? 'table-row' : 'none';
		percentOffInput.required = 'percent_off' === type.value;
	} );
}

/**
 * Toggles inputs related to "Duration" selection.
 *
 * @since 4.3.0
 */
export function durationToggle() {
	const duration = document.getElementById( 'coupon-enable-duration' );
	const durationWrap = document.getElementById(
		'coupon-has-duration_in_months'
	);
	const durationInput = document.getElementById(
		'coupon-duration_in_months'
	);

	duration.addEventListener( 'change', function ( e ) {
		const { target } = e;
		const { available } = target.dataset;

		if ( 'once' !== target.value && 'no' === available ) {
			const {
				upgradeTitle,
				upgradeDescription,
				upgradeUrl,
				upgradePurchasedUrl,
			} = target.dataset;

			target.value = 'once';

			upgradeModal( {
				title: upgradeTitle,
				description: upgradeDescription,
				url: upgradeUrl,
				purchasedUrl: upgradePurchasedUrl,
			} );
		} else {
			durationWrap.style.display =
				'repeating' === duration.value ? 'block' : 'none';
			durationInput.required = 'repeating' === duration.value;
		}
	} );
}

/**
 * Toggles inputs related to "Redemption Limits" selection.
 *
 * @since 4.3.0
 */
export function redemptionToggle() {
	// Redeem by.
	const redeemBy = document.getElementById( 'coupon-enable-redeem_by' );
	const redeemByWrap = document.getElementById( 'coupon-has-redeem_by' );

	redeemBy.addEventListener( 'change', function () {
		redeemByWrap.style.display = redeemBy.checked ? 'block' : 'none';
	} );

	// Max redemptions.
	const maxRedemptions = document.getElementById(
		'coupon-enable-max_redemptions'
	);

	const maxRedemptionsWrap = document.getElementById(
		'coupon-has-max_redemptions'
	);

	const maxRedemptionsInput = document.getElementById(
		'coupon-max_redemptions'
	);

	const maxRedemptionsDecorator = document.getElementById(
		'coupon-max_redemptions-decorator'
	);

	maxRedemptions.addEventListener( 'change', function () {
		maxRedemptionsWrap.style.display = maxRedemptions.checked
			? 'block'
			: 'none';

		maxRedemptionsInput.required = maxRedemptions.checked;
	} );

	maxRedemptionsInput.addEventListener(
		'change',
		function ( { target: { value } } ) {
			maxRedemptionsDecorator.innerText =
				parseInt( value ) === 1
					? maxRedemptionsDecorator.dataset.singular
					: maxRedemptionsDecorator.dataset.plural;
		}
	);
}

/**
 * Toggles inputs related to "Payment Form Limit" selection.
 *
 * @since 4.3.0
 */
export function formRestrictionToggle() {
	const appliesTo = document.getElementById(
		'coupon-enable-applies_to_forms'
	);

	const appliesToWrap = document.getElementById(
		'coupon-has-applies_to_forms'
	);

	appliesTo.addEventListener( 'change', function () {
		appliesToWrap.style.display = appliesTo.checked ? 'block' : 'none';
	} );
}
