<?php
/**
 * Payment Methods: Functions
 *
 * @package SimplePay\Core\Payment_Methods
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Pro\Payment_Methods;

use Exception;
use SimplePay\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers available Payment Methods.
 *
 * @since 3.8.0
 *
 * @param \SimplePay\Core\Utils\Collections $registry Collections registry.
 */
function register_payment_methods( $registry ) {
	// Add Payment Methods registry to Collections registry.
	$payment_methods = new Collection();
	$registry->add( 'payment-methods', $payment_methods );

	$account_country = strtolower(
		simpay_get_setting( 'account_country', 'US' )
	);

	// Card.
	$card = new Payment_Method(
		array(
			'id'              => 'card',
			'name'            => esc_html__( 'Card', 'simple-pay' ),
			'nicename'        => esc_html__( 'Credit Card', 'simple-pay' ),
			'licenses'        => array(
				'personal',
				'plus',
				'professional',
				'ultimate',
				'elite',
			),
			'scope'           => 'popular',
			'recurring'       => true,
			'stripe_checkout' => true,
			'external_docs'   => 'https://stripe.com/payments/payment-methods-guide#cards',
			'internal_docs'   => 'https://docs.wpsimplepay.com/articles/accepting-card-payments/',
			'icon'            => '<svg height="32" width="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#e3e8ee"/><path d="M26 11H6v-.938C6 9.2 6.56 8.5 7.25 8.5h17.5c.69 0 1.25.7 1.25 1.563zm0 3.125v8.125c0 .69-.56 1.25-1.25 1.25H7.25c-.69 0-1.25-.56-1.25-1.25v-8.125zM11 18.5a1.25 1.25 0 0 0 0 2.5h1.25a1.25 1.25 0 0 0 0-2.5z" fill="#697386"/></g></svg>',
			'icon_sm'         => '<svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2H0zm0 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6H0zm3 5a1 1 0 0 1 1-1h1a1 1 0 1 1 0 2H4a1 1 0 0 1-1-1z" fill="#6d6e78"/></svg>',
		)
	);

	$payment_methods->add( 'card', $card );

	// ACH Direct Debit.
	$ach_debit = new Payment_Method(
		array(
			'id'              => 'ach-debit',
			'name'            => esc_html__( 'ACH Direct Debit', 'simple-pay' ),
			'nicename'        => esc_html__( 'ACH Direct Debit', 'simple-pay' ),
			'licenses'        => array(
				'personal',
				'plus',
				'professional',
				'ultimate',
				'elite',
			),
			'scope'           => 'popular',
			'recurring'       => true,
			'stripe_checkout' => true,
			'countries'       => array(
				'us',
			),
			'currencies'      => array(
				'usd',
			),
			'external_docs'   => 'https://stripe.com/payments/payment-methods-guide#ach-debits',
			'internal_docs'   => 'https://docs.wpsimplepay.com/articles/accepting-ach-debit-payments/',
			'icon'            => '<svg height="32" width="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#e3e8ee"/><path d="M7.274 13.5a1.25 1.25 0 0 1-.649-2.333C7.024 10.937 10.15 9.215 16 6c5.851 3.215 8.976 4.937 9.375 5.167a1.25 1.25 0 0 1-.65 2.333zm12.476 10v-8.125h3.75V23.5H25a1 1 0 0 1 1 1V26H6v-1.5a1 1 0 0 1 1-1h1.5v-8.125h3.75V23.5h1.875v-8.125h3.75V23.5z" fill="#697386"/></g></svg>',
		)
	);

	$payment_methods->add( 'ach-debit', $ach_debit );

	// SEPA Direct Debit.
	$sepa_debit = new Payment_Method(
		array(
			'id'              => 'sepa-debit',
			'name'            => esc_html__( 'SEPA Direct Debit', 'simple-pay' ),
			'nicename'        => esc_html__( 'SEPA Direct Debit', 'simple-pay' ),
			'licenses'        => array(
				'personal',
				'plus',
				'professional',
				'ultimate',
				'elite',
			),
			'recurring'       => true,
			'stripe_checkout' => true,
			'currencies'      => array(
				'eur',
			),
			'countries'       => array(
				'au',
				'at',
				'be',
				'bu',
				'ca',
				'cy',
				'dk',
				'ee',
				'fi',
				'fr',
				'de',
				'gr',
				'hk',
				'ie',
				'it',
				'jp',
				'lv',
				'lt',
				'lu',
				'mt',
				'mx',
				'nl',
				'nz',
				'no',
				'pl',
				'pt',
				'ro',
				'sg',
				'sk',
				'si',
				'es',
				'se',
				'ch',
				'gb',
				'us',
			),
			'external_docs'   => 'https://stripe.com/payments/payment-methods-guide#sepa-debit',
			'internal_docs'   => 'https://docs.wpsimplepay.com/articles/accepting-sepa-debit-payments/',
			'icon'            => '<svg height="32" width="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#10298d"/><path d="M27.485 18.42h-2.749l-.37 1.342H22.24L24.533 12h3.104l2.325 7.762h-2.083l-.393-1.342zm-.408-1.512-.963-3.364-.936 3.364zm-10.452 2.854V12h3.83c.526 0 .928.044 1.203.13.63.202 1.052.612 1.27 1.233.111.325.167.816.167 1.47 0 .788-.06 1.354-.183 1.699-.247.68-.753 1.072-1.517 1.175-.09.015-.472.028-1.146.04l-.341.011H18.68v2.004zm2.056-3.805h1.282c.407-.015.653-.047.744-.096.12-.068.202-.204.242-.408.026-.136.04-.337.04-.604 0-.329-.026-.573-.079-.732-.073-.222-.25-.358-.53-.407a3.91 3.91 0 0 0-.4-.011h-1.299zm-10.469-1.48H6.3c0-.32-.038-.534-.11-.642-.114-.162-.43-.242-.942-.242-.5 0-.831.046-.993.139-.161.093-.242.296-.242.608 0 .283.072.469.215.558a.91.91 0 0 0 .408.112l.386.026c.517.033 1.033.072 1.55.119.654.066 1.126.243 1.421.53.231.222.37.515.414.875.025.216.037.46.037.73 0 .626-.057 1.083-.175 1.374-.213.532-.693.868-1.437 1.009-.312.06-.788.089-1.43.089-1.072 0-1.819-.064-2.24-.196-.517-.158-.858-.482-1.024-.969-.092-.269-.137-.72-.137-1.353h1.914v.162c0 .337.096.554.287.65.13.067.29.101.477.106h.704c.359 0 .587-.019.687-.056a.57.57 0 0 0 .346-.34 1.38 1.38 0 0 0 .044-.374c0-.341-.123-.55-.368-.624-.092-.03-.52-.071-1.28-.123a15.411 15.411 0 0 1-1.274-.128c-.626-.119-1.044-.364-1.252-.736-.184-.315-.275-.793-.275-1.432 0-.487.05-.877.148-1.17.1-.294.258-.517.48-.669.321-.234.735-.371 1.237-.412.463-.04.927-.058 1.391-.056.803 0 1.375.046 1.717.14.833.227 1.248.863 1.248 1.909a5.8 5.8 0 0 1-.018.385z" fill="#fff"/><path d="M13.786 13.092c.849 0 1.605.398 2.103 1.02l.444-.966a3.855 3.855 0 0 0-2.678-1.077c-1.62 0-3.006.995-3.575 2.402h-.865l-.51 1.111h1.111c-.018.23-.017.46.006.69h-.56l-.51 1.111h1.354a3.853 3.853 0 0 0 3.549 2.335c.803 0 1.55-.244 2.167-.662v-1.363a2.683 2.683 0 0 1-2.036.939 2.7 2.7 0 0 1-2.266-1.248h2.832l.511-1.112h-3.761a2.886 2.886 0 0 1-.016-.69h4.093l.51-1.11h-4.25a2.704 2.704 0 0 1 2.347-1.38" fill="#ffcc02"/></g></svg>',
		)
	);

	$payment_methods->add( 'sepa-debit', $sepa_debit );

	// Alipay.
	$alipay = new Payment_Method(
		array(
			'id'              => 'alipay',
			'name'            => esc_html__( 'Alipay', 'simple-pay' ),
			'nicename'        => esc_html__( 'Alipay', 'simple-pay' ),
			'licenses'        => array(
				'personal',
				'plus',
				'professional',
				'ultimate',
				'elite',
			),
			'scope'           => 'popular',
			'recurring'       => false,
			'stripe_checkout' => true,
			'countries'       => array(
				'au',
				'at',
				'be',
				'ca',
				'dk',
				'ee',
				'fi',
				'fr',
				'de',
				'gr',
				'hk',
				'ie',
				'it',
				'jp',
				'lv',
				'lt',
				'lu',
				'my',
				'nl',
				'nz',
				'no',
				'pt',
				'sg',
				'sk',
				'si',
				'es',
				'se',
				'ch',
				'gb',
				'us',
			),
			'currencies'      => array(
				'aud',
				'cad',
				'cny',
				'eur',
				'gbp',
				'hkd',
				'jpy',
				'myr',
				'sgd',
				'nzd',
				'usd',
			),
			'external_docs'   => 'https://stripe.com/payments/payment-methods-guide#alipay',
			'internal_docs'   => 'https://docs.wpsimplepay.com/articles/accepting-alipay-payments/',
			'icon'            => '<svg height="32" width="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#1c9fe5"/><path d="M23.104 18.98a142.494 142.494 0 0 0 11.052 3.848c2.044.85 0 5.668-2.159 4.674-2.444-1.066-7.359-3.245-11.097-5.108C18.822 24.842 15.556 28 10.907 28 6.775 28 4 25.568 4 21.943c0-3.053 2.11-6.137 6.82-6.137 2.697 0 5.47.766 8.785 1.922a25.007 25.007 0 0 0 1.529-3.838l-11.981-.006v-1.848l6.162.015V9.63H7.808V7.81l7.507.006V5.115c0-.708.38-1.115 1.042-1.115h3.14v3.827l7.442.005v1.805h-7.44v2.431l6.088.016s-.754 3.904-2.483 6.897zM5.691 21.79v-.004c0 1.736 1.351 3.489 4.64 3.489 2.54 0 5.028-1.52 7.408-4.522-3.181-1.592-4.886-2.357-7.348-2.357-2.394 0-4.7 1.164-4.7 3.394z" fill="#fff" fill-rule="nonzero"/></g></svg>',
		)
	);

	$payment_methods->add( 'alipay', $alipay );

	// Bancontaact.
	$bancontact = new Payment_Method(
		array(
			'id'              => 'bancontact',
			'name'            => esc_html__( 'Bancontact', 'simple-pay' ),
			'nicename'        => esc_html__( 'Bancontact', 'simple-pay' ),
			'licenses'        => array(
				'personal',
				'plus',
				'professional',
				'ultimate',
				'elite',
			),
			'recurring'       => false,
			'stripe_checkout' => true,
			'countries'       => array(
				'au',
				'at',
				'be',
				'bg',
				'ca',
				'cy',
				'cz',
				'dk',
				'ee',
				'fi',
				'fr',
				'de',
				'gr',
				'hk',
				'ie',
				'it',
				'jp',
				'lv',
				'lt',
				'lu',
				'mt',
				'mx',
				'nl',
				'nz',
				'no',
				'pl',
				'pt',
				'ro',
				'sg',
				'sk',
				'si',
				'es',
				'se',
				'ch',
				'gb',
				'us',
			),
			'currencies'      => array(
				'eur',
			),
			'external_docs'   => 'https://stripe.com/payments/payment-methods-guide#bancontact',
			'internal_docs'   => 'https://docs.wpsimplepay.com/articles/accepting-bancontact-payments/',
			'icon'            => '<svg height="32" width="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#fff"/><g fill-rule="nonzero"><path d="M25.64 14.412h-7.664l-.783.896-2.525 2.898-.783.896H6.331l.764-.906.362-.428.763-.907H4.746c-.636 0-1.155.548-1.155 1.205v2.55c0 .666.52 1.204 1.155 1.204h13.328c.637 0 1.508-.398 1.928-.896l2.016-2.33z" fill="#005498"/><path d="M27.176 11.694c.636 0 1.154.548 1.154 1.205v2.539c0 .667-.518 1.204-1.154 1.204H23.71l.773-.896.382-.448.773-.896h-7.662l-4.081 4.68H6.292l5.451-6.273.206-.239c.43-.488 1.301-.896 1.937-.896h13.29z" fill="#ffbf00"/></g></g></svg>',
		)
	);

	$payment_methods->add( 'bancontact', $bancontact );

	// FPX.
	$fpx = new Payment_Method(
		array(
			'id'              => 'fpx',
			'name'            => esc_html__( 'FPX', 'simple-pay' ),
			'nicename'        => esc_html__( 'FPX', 'simple-pay' ),
			'licenses'        => array(
				'personal',
				'plus',
				'professional',
				'ultimate',
				'elite',
			),
			'scope'           => in_array( $account_country, array( 'my' ), true )
				? 'popular'
				: 'standard',
			'recurring'       => false,
			'stripe_checkout' => true,
			'countries'       => array(
				'my',
			),
			'currencies'      => array(
				'myr',
			),
			'external_docs'   => 'https://stripe.com/payments/payment-methods-guide#fpx',
			'internal_docs'   => 'https://docs.wpsimplepay.com/articles/accepting-fpx-payments/',
			'icon'            => '<svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M29.174 13.9757c-2.8569-3.6805-5.7383-7.34192-8.644-10.98397-.46-.578-1.132-1.27-1.916-.876-.53.264-1.012 1.05-1.066 1.64-.178 1.938-.164 3.89-.184 5.836-.002.22.22.45997.38.66197 1.208 1.542 2.436 3.07 3.636 4.616.334.43.52.78.58 1.114-.06.468-.246.704-.58 1.134-1.2 1.546-2.428 3.08-3.636 4.622-.16.204-.382.452-.38.672.02 1.946.006 3.898.184 5.834.054.59.536 1.376 1.066 1.64.784.392 1.456-.304 1.916-.882 2.9059-3.6446 5.7872-7.3087 8.644-10.992.508-.654.776-1.092.826-2.028-.05-.68-.32-1.354-.826-2.008Z" fill="#1F2C5C"/><path fill-rule="evenodd" clip-rule="evenodd" d="M2.826 13.9757c2.852-3.68 5.74-7.33797 8.644-10.98397.46-.578 1.132-1.27 1.916-.876.53.264 1.012 1.05 1.066 1.64.178 1.938.164 3.89.184 5.836.002.22-.22.45997-.38.66197-1.208 1.542-2.436 3.07-3.636 4.616-.334.43-.522.78-.58 1.114.058.468.246.704.58 1.134 1.2 1.546 2.428 3.08 3.636 4.622.16.204.382.452.38.672-.02 1.946-.006 3.898-.184 5.834-.054.59-.536 1.376-1.066 1.64-.784.392-1.456-.304-1.916-.882-2.90579-3.6447-5.78719-7.3088-8.644-10.992-.508-.654-.776-1.092-.826-2.028.05-.68.32-1.354.826-2.008Z" fill="#1A8ACB"/></svg>',
		)
	);

	$payment_methods->add( 'fpx', $fpx );

	// giropay.
	$giropay = new Payment_Method(
		array(
			'id'              => 'giropay',
			'name'            => esc_html__( 'giropay', 'simple-pay' ),
			'nicename'        => esc_html__( 'giropay', 'simple-pay' ),
			'licenses'        => array(
				'personal',
				'plus',
				'professional',
				'ultimate',
				'elite',
			),
			'recurring'       => false,
			'stripe_checkout' => true,
			'countries'       => array(
				'au',
				'at',
				'be',
				'bg',
				'ca',
				'cy',
				'cz',
				'dk',
				'ee',
				'fi',
				'fr',
				'de',
				'gr',
				'hk',
				'ie',
				'it',
				'jp',
				'lv',
				'lt',
				'lu',
				'mt',
				'mx',
				'nl',
				'nz',
				'no',
				'pl',
				'pt',
				'ro',
				'sg',
				'sk',
				'si',
				'es',
				'se',
				'ch',
				'gb',
				'us',
			),
			'currencies'      => array(
				'eur',
			),
			'external_docs'   => 'https://stripe.com/payments/payment-methods-guide#giropay',
			'internal_docs'   => 'https://docs.wpsimplepay.com/articles/accepting-giropay-payments/',
			'icon'            => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M0 9.588C0 7.60667 1.652 6 3.688 6h24.624C30.3493 6 32 7.60667 32 9.588v12.824C32 24.3933 30.3493 26 28.312 26H3.688C1.652 26 0 24.3933 0 22.412V9.588Zm1.36 12.8c0 1.2587 1.044 2.28 2.33333 2.28H15.9773V7.33333H3.69333c-1.28933 0-2.33333 1.02-2.33333 2.27734V22.3893v-.0013Zm18.332-.1387h3.0293v-4.76h.0334c.5733 1.0427 1.72 1.4294 2.7786 1.4294 2.6094 0 4.0054-2.1534 4.0054-4.744 0-2.1187-1.3294-4.42537-3.7534-4.42537-1.38 0-2.6586.55597-3.2666 1.78397h-.0334V9.95333H19.692V22.2493Zm6.716-8.0066c0 1.396-.6907 2.3546-1.8347 2.3546-1.0106 0-1.8533-.96-1.8533-2.2373 0-1.312.7413-2.288 1.8533-2.288 1.18 0 1.8347 1.0093 1.8347 2.1693v.0014Z" fill="#04337B"/><path fill-rule="evenodd" clip-rule="evenodd" d="M14 10.1933v7.3079c0 3.4006-1.8012 4.4987-5.44014 4.4987-1.17233.0036-2.33742-.1736-3.44935-.5247l.15574-2.2577c.95206.4288 1.74973.6822 3.05119.6822 1.80116 0 2.77226-.7769 2.77226-2.3985v-.4453h-.0353c-.7448.9688-1.7849 1.4142-3.03354 1.4142C5.54252 18.4701 4 16.7551 4 14.308 4 11.8456 5.26625 10 8.07232 10c1.33396 0 2.41058.6694 3.07018 1.6869h.0338V10.192L14 10.1933Zm-6.77546 4.0661c0 1.2862.83424 2.017 1.76869 2.017 1.10777 0 1.99347-.8575 1.99347-2.1297 0-.9215-.5891-1.9543-1.99347-1.9543-1.16062 0-1.76869.9394-1.76869 2.067Z" fill="#EE3525"/></svg>',
		)
	);

	$payment_methods->add( 'giropay', $giropay );

	// iDEAL.
	$ideal = new Payment_Method(
		array(
			'id'              => 'ideal',
			'name'            => esc_html__( 'iDEAL', 'simple-pay' ),
			'nicename'        => esc_html__( 'iDEAL', 'simple-pay' ),
			'licenses'        => array(
				'personal',
				'plus',
				'professional',
				'ultimate',
				'elite',
			),
			'recurring'       => false,
			'stripe_checkout' => true,
			'countries'       => array(
				'au',
				'at',
				'be',
				'bg',
				'ca',
				'cy',
				'cz',
				'dk',
				'ee',
				'fi',
				'fr',
				'de',
				'gr',
				'hk',
				'hu',
				'ie',
				'it',
				'jp',
				'lv',
				'lt',
				'lu',
				'mt',
				'mx',
				'nl',
				'nz',
				'no',
				'pl',
				'pt',
				'ro',
				'sg',
				'sk',
				'si',
				'es',
				'se',
				'ch',
				'gb',
				'us',
			),
			'currencies'      => array(
				'eur',
			),
			'external_docs'   => 'https://stripe.com/payments/payment-methods-guide#ideal',
			'internal_docs'   => 'https://docs.wpsimplepay.com/articles/accepting-ideal-payments/',
			'icon'            => '<svg height="32" width="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none"><path fill="#FFF" d="M0 0h32v32H0z"/><g transform="translate(3 5)"><path d="M0 1.694v19.464c0 .936.758 1.694 1.694 1.694h11.63c8.788 0 12.599-4.922 12.599-11.448C25.923 4.903 22.112 0 13.323 0H1.694C.759 0 0 .758 0 1.694z" fill="#FFF"/><path d="M13.321 21.296H3.206A1.628 1.628 0 0 1 1.58 19.67V3.182c.001-.898.729-1.625 1.626-1.626h10.115c9.593 0 11.026 6.17 11.026 9.848 0 6.381-3.916 9.892-11.026 9.892zM3.206 2.098c-.598 0-1.084.485-1.085 1.084V19.67c.001.599.487 1.084 1.085 1.084h10.115c6.76 0 10.484-3.32 10.484-9.35 0-8.097-6.569-9.306-10.484-9.306H3.206z" fill="#000"/><path d="M7.781 4.78v14.377h6.259c5.686 0 8.151-3.213 8.151-7.746 0-4.342-2.465-7.716-8.151-7.716H8.865c-.598 0-1.084.485-1.084 1.084z" fill="#C06"/><path fill="#FFF" d="M19.713 9.47v2.8h1.674v.635h-2.429V9.47zm-2.514 0 1.285 3.435H17.7l-.26-.762h-1.285l-.27.762h-.762l1.3-3.435h.776zm.043 2.107-.433-1.26H16.8l-.447 1.26h.89zm-2.63-2.107v.635h-1.814v.736h1.665v.587h-1.665v.842h1.853v.635h-2.607V9.47zm-4.627 0c.21-.002.42.034.617.106.187.068.356.176.496.318.146.15.257.331.328.529.082.24.122.492.117.746.002.234-.03.467-.096.692-.059.2-.157.387-.29.549-.133.156-.3.28-.487.363-.216.093-.45.138-.685.132H8.503V9.47h1.482zm-.053 2.8a.983.983 0 0 0 .317-.053.703.703 0 0 0 .275-.176.888.888 0 0 0 .192-.319c.052-.155.076-.318.072-.481a2.04 2.04 0 0 0-.05-.47.932.932 0 0 0-.17-.357.74.74 0 0 0-.305-.23 1.212 1.212 0 0 0-.47-.079h-.538v2.165h.677z"/><path d="M4.953 13.683a1.2 1.2 0 0 1 1.2 1.2v4.274a2.401 2.401 0 0 1-2.401-2.401v-1.872a1.2 1.2 0 0 1 1.2-1.2z" fill="#000"/><circle fill="#000" cx="4.953" cy="11.188" r="1.585"/></g></g></svg>',
		)
	);

	$payment_methods->add( 'ideal', $ideal );

	// Klarna.
	$klarna_countries = array(
		'at',
		'be',
		'de',
		'ee',
		'fi',
		'fr',
		'de',
		'gr',
		'ie',
		'it',
		'lv',
		'lt',
		'nl',
		'no',
		'sk',
		'si',
		'es',
		'se',
		'us',
		'gb',
	);

	$klarna = new Payment_Method(
		array(
			'id'              => 'klarna',
			'name'            => esc_html__( 'Klarna (buy now, pay later)', 'simple-pay' ),
			'nicename'        => esc_html__( 'Klarna', 'simple-pay' ),
			'licenses'        => array(
				'professional',
				'ultimate',
				'elite',
			),
			'recurring'       => false,
			'stripe_checkout' => true,
			'scope'           => in_array( $account_country, $klarna_countries, true )
				? 'popular'
				: null,
			'bnpl'            => true,
			'countries'       => $klarna_countries,
			'currencies'      => array(
				'eur',
				'usd',
				'gbp',
				'dkk',
				'sek',
				'nok',
			),
			'external_docs'   => 'https://stripe.com/payments/payment-methods-guide#klarna',
			'internal_docs'   => 'https://docs.wpsimplepay.com/articles/accepting-klarna-payments/',
			'icon'            => '<svg height="32" width="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#ffb3c7"/><path d="M16.279 7c0 3.307-1.501 6.342-4.124 8.323l-1.573 1.2 6.126 8.442h5.034l-5.638-7.77C18.777 14.504 20.27 10.888 20.27 7zM6 7h4.087v17.965H6zm16.382 15.665c0-1.289 1.034-2.335 2.309-2.335S27 21.376 27 22.665C27 23.955 25.966 25 24.69 25s-2.308-1.046-2.308-2.335z" fill="#0a0b09" fill-rule="nonzero"/></g></svg>',
		)
	);

	$payment_methods->add( 'klarna', $klarna );

	// Afterpay/Clearpay.
	$afterpay_countries = array(
		'au',
		'ca',
		'fr',
		'ie',
		'it',
		'nz',
		'es',
		'us',
		'gb',
	);

	$name = 'gb' === $account_country
		? esc_html__( 'Clearpay', 'simple-pay' )
		: esc_html__( 'Afterpay', 'simple-pay' );

	$afterpay_clearpay = new Payment_Method(
		array(
			'id'              => 'afterpay-clearpay',
			'name'            => sprintf(
				/* translators: %s Clearpay or Afterpay payment method name, do not translate. */
				esc_html__( '%s (buy now, pay later)', 'simple-pay' ),
				$name
			),
			'nicename'        => $name,
			'licenses'        => array(
				'professional',
				'ultimate',
				'elite',
			),
			'recurring'       => false,
			'stripe_checkout' => true,
			'scope'           => in_array( $account_country, $afterpay_countries, true )
				? 'popular'
				: null,
			'bnpl'            => true,
			'countries'       => $afterpay_countries,
			'currencies'      => array(
				'usd',
				'cad',
				'gbp',
				'aud',
				'nzd',
			),
			'external_docs'   => 'https://stripe.com/payments/payment-methods-guide#afterpay-clearpay',
			'internal_docs'   => 'https://docs.wpsimplepay.com/articles/accepting-afterpay-clearpay-payments/',
			'icon'            => '<svg height="16" width="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16Z" fill="#B2FCE4"/><path d="m12.563 5.187-1.477-.845-1.498-.859c-.99-.567-2.228.146-2.228 1.29v.192a.29.29 0 0 0 .15.256l.695.397a.288.288 0 0 0 .431-.252V4.91c0-.226.243-.367.44-.256l1.366.786 1.362.78a.293.293 0 0 1 0 .509l-1.362.781-1.366.786a.294.294 0 0 1-.44-.257v-.226c0-1.144-1.238-1.861-2.228-1.29l-1.494.863-1.478.846a1.49 1.49 0 0 0 0 2.582l1.478.845 1.498.859c.99.567 2.228-.146 2.228-1.29v-.192a.29.29 0 0 0-.15-.256l-.695-.397a.288.288 0 0 0-.431.252v.457a.294.294 0 0 1-.44.256l-1.366-.786-1.362-.78a.293.293 0 0 1 0-.509l1.362-.781 1.366-.786c.197-.11.44.03.44.257v.226c0 1.144 1.238 1.861 2.228 1.289l1.499-.858 1.477-.845c.99-.577.99-2.015-.005-2.587Z"/></svg>',
		)
	);

	$payment_methods->add( 'afterpay-clearpay', $afterpay_clearpay );

	// P24.
	$p24 = new Payment_Method(
		array(
			'id'              => 'p24',
			'name'            => esc_html__( 'Przelewy24', 'simple-pay' ),
			'nicename'        => esc_html__( 'Przelewy24', 'simple-pay' ),
			'licenses'        => array(
				'personal',
				'plus',
				'professional',
				'ultimate',
				'elite',
			),
			'recurring'       => false,
			'stripe_checkout' => true,
			'countries'       => array(
				'au',
				'at',
				'be',
				'bg',
				'ca',
				'cy',
				'cz',
				'dk',
				'ee',
				'fi',
				'fr',
				'de',
				'gr',
				'hk',
				'ie',
				'it',
				'jp',
				'lv',
				'lt',
				'lu',
				'mt',
				'mx',
				'nl',
				'nz',
				'no',
				'pl',
				'pt',
				'ro',
				'sg',
				'sk',
				'si',
				'es',
				'se',
				'ch',
				'gb',
				'us',
			),
			'currencies'      => array(
				'eur',
				'pln',
			),
			'external_docs'   => 'https://stripe.com/payments/payment-methods-guide#p24',
			'internal_docs'   => 'https://docs.wpsimplepay.com/articles/accepting-p24-payments/',
			'icon'            => '<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="m18.7454 23.0418-.1788.9409H11l.3555-1.8599c.1985-1.0434.5424-1.7264 1.0364-2.05.4928-.3225 1.5639-.5712 3.2131-.7451 1.3181-.1348 2.1352-.3179 2.4468-.5505.3151-.2304.5632-.8316.7479-1.8013.1616-.8499.1177-1.4016-.135-1.6549-.2528-.2534-.883-.3801-1.894-.3801-1.2626 0-2.0774.1037-2.4468.3075-.3716.205-.6313.6991-.779 1.4811l-.127.737h-1.1622l.1062-.5125c.225-1.1827.6405-1.9705 1.2465-2.3643.607-.3916 1.7081-.5897 3.3031-.5897 1.415 0 2.3279.2119 2.7342.6357.4062.4261.4916 1.253.2574 2.4842-.2251 1.1828-.6014 1.9728-1.1311 2.3724-.5309.3962-1.5685.6657-3.1139.8062-1.3573.1244-2.1859.2995-2.4849.5217-.3.2211-.547.8511-.7444 1.8888l-.0635.334 6.3801-.0012Zm9.9522-8.9232-1.2695 6.6602H29l-.18.9409h-1.5697L26.8152 24h-1.183l.434-2.2803h-5.8781l.2482-1.3095 6.432-6.2916h1.8316-.0023Zm-2.4537 6.6602 1.1322-5.9427h-.0231l-6.0107 5.9427h4.9016Z" fill="#99A0A6"/><path fill-rule="evenodd" clip-rule="evenodd" d="m1 24 2.00663-10h5.17288c1.27353 0 2.09989.2173 2.47559.6519.3756.4346.4447 1.2482.206 2.4399-.2303 1.1431-.6277 1.9221-1.19358 2.3358-.56588.4161-1.51467.6242-2.84273.6242l-.49802.0069H3.066L2.27474 24H1Zm2.25624-4.8937h3.02085c1.26263 0 2.0963-.1155 2.49859-.3444.4023-.2288.68584-.757.85064-1.5823.19388-.9674.19751-1.5788.00848-1.8354-.18903-.2554-.73189-.3849-1.6322-.3849l-.48591-.0069H4.08992l-.83368 4.1539Z" fill="#D40E2B"/><path fill-rule="evenodd" clip-rule="evenodd" d="m8.0316 10.3914-1.17173-.76881C8.24211 8.9668 9.68104 8.43533 11.1593 8.0346l.2151 1.04721c-1.0583.29789-2.17454.7241-3.3428 1.30959Zm13.2788-.57518c-1.0905-.54747-2.2561-.93386-3.4597-1.14688L18.7958 7h.0266c2.7321.0126 4.9043.35289 6.6162.82722l-4.1282 1.989Zm-15.51584.34488 1.19602.802c-.5448.3094-1.10232.6542-1.6691 1.0369H3s.96005-.8444 2.79341-1.8389h.00115ZM17.3915 7.05843l-.5598 1.478c-1.5547-.1514-3.124-.04922-4.6453.30248l-.1596-1.05866c1.7582-.41018 3.5557-.65307 5.3647-.72296v.00114Zm10.2252 1.49404C30.092 9.62718 31 10.8497 31 10.8497h-8.0043s-.2637-.2269-.7623-.542l5.3833-1.75523Z" fill="#99A0A6"/></svg>',
		)
	);

	$payment_methods->add( 'p24', $p24 );

	/**
	 * Allows further Payment Methods to be registered.
	 *
	 * @since 3.8.0
	 *
	 * @param \SimplePay\Core\Utils\Collection $payment_methods Payment Methods registry.
	 */
	do_action( 'simpay_register_payment_methods', $payment_methods );
}
add_action( 'simpay_register_collections', __NAMESPACE__ . '\\register_payment_methods' );

/**
 * Returns a list of registered Payment Methods.
 *
 * @since 3.8.0
 *
 * @return array List of Payment Methods.
 */
function get_payment_methods() {
	$payment_methods = Utils\get_collection( 'payment-methods' );

	if ( false === $payment_methods ) {
		return array();
	}

	return $payment_methods->get_items();
}

/**
 * Returns a Payment Method.
 *
 * @since 3.8.0
 *
 * @param string $payment_method_id ID of the registered Payment Method.
 * @return false|\SimplePay\Core\Payment_Methods\Payment_Method Payment Method if found, otherwise `false`.
 */
function get_payment_method( $payment_method_id ) {
	$payment_methods = Utils\get_collection( 'payment-methods' );

	if ( false === $payment_methods ) {
		return false;
	}

	return $payment_methods->get_item( $payment_method_id );
}

/**
 * Returns a list of registered Payment Methods that
 * support Stripe Checkout.
 *
 * @since 3.8.0
 *
 * @return array List of Payment Methods that support Stripe Checkout.
 */
function get_stripe_checkout_payment_methods() {
	$payment_methods = Utils\get_collection( 'payment-methods' );

	if ( false === $payment_methods ) {
		return array();
	}

	return array_filter(
		$payment_methods->get_items(),
		function( $payment_method ) {
			return (
				true === $payment_method->stripe_checkout ||
				is_array( $payment_method->stripe_checkout )
			);
		}
	);
}

/**
 * Retrieves saved Payment Methods for a specific form.
 *
 * @since 3.8.0
 *
 * @param SimplePay\Core\Abstracts\Form $form Payment Form.
 * @return SimplePay\Pro\Payment_Methods\Payment_Method[] List of Payment Methods.
 */
function get_form_payment_methods( $form ) {
	$payment_form = 'stripe_checkout' === $form->get_form_display_type()
		? 'stripe-checkout'
		: 'stripe-elements';

	$payment_methods = simpay_get_filtered(
		'payment_methods',
		simpay_get_saved_meta( $form->id, '_payment_methods', array() ),
		$form->id
	);

	// Form hasn't been updated since 3.8.0.
	if ( empty( $payment_methods ) ) {
		$payment_methods = array(
			'card' => array(
				'id' => 'card',
			),
		);
		// Use saved Payment Methods for Payment Form.
	} else {
		$payment_methods = isset( $payment_methods[ $payment_form ] )
			? $payment_methods[ $payment_form ]
			: array();
	}

	$payment_methods = array_map(
		/**
		 * Attach saved Payment Method settings to the \SimplePay\Pro\Payment_Methods\Payment_Method.
		 *
		 * @since 3.8.0
		 *
		 * @param array $payment_method Saved Payment Method data.
		 * @return false|\SimplePay\Pro\Payment_Methods\Paymetn_Method Payment Method object if available
		 *                                                             otherwise false.
		 */
		function( $payment_method ) use ( $form ) {
			if ( ! isset( $payment_method['id'] ) ) {
				return false;
			}

			$payment_method_obj = get_payment_method( $payment_method['id'] );

			if ( false === $payment_method_obj ) {
				return false;
			}

			$payment_method_obj->config = $payment_method;

			return $payment_method_obj;
		},
		$payment_methods
	);

	return array_filter(
		$payment_methods,
		function( $payment_method ) {
			return is_a( $payment_method, 'SimplePay\Pro\Payment_Methods\Payment_Method' );
		}
	);
}

/**
 * Returns a list of payment method IDs (slugs) that are enabled for a specific form.
 *
 * @since 4.6.0
 *
 * @param SimplePay\Core\Abstracts\Form $form Payment Form.
 * @return array<string> List of payment method IDs.
 */
function get_form_payment_method_ids( $form ) {
	$allowed_payment_methods = get_form_payment_methods( $form );

	return array_reduce(
		$allowed_payment_methods,
		function( $carry, $payment_method ) {
			switch ( $payment_method->id ) {
				case 'sepa-debit':
					$id = 'sepa_debit';
					break;
				case 'ach-debit':
					$id = 'us_bank_account';
					break;
				case 'afterpay-clearpay':
					$id = 'afterpay_clearpay';
					break;
				default:
					$id = $payment_method->id;
			}

			array_push( $carry, $id );

			return $carry;
		},
		array()
	);
}

/**
 * Retrieves saved Payment Method settings for a specific form.
 *
 * @since 3.9.0
 *
 * @param SimplePay\Core\Abstracts\Form $form           Payment Form.
 * @param string                        $payment_method Payment Method ID.
 * @return array List of Payment Method settings.
 */
function get_form_payment_method_settings( $form, $payment_method ) {
	$payment_form = 'stripe_checkout' === $form->get_form_display_type()
		? 'stripe-checkout'
		: 'stripe-elements';

	$payment_methods = simpay_get_filtered(
		'payment_methods',
		simpay_get_saved_meta( $form->id, '_payment_methods', array() ),
		$form->id
	);

	// Form hasn't been updated since 3.8.0.
	if ( empty( $payment_methods ) ) {
		$payment_methods = array(
			'card' => array(
				'id' => 'card',
			),
		);
		// Use saved Payment Methods for Payment Form.
	} else {
		$payment_methods = isset( $payment_methods[ $payment_form ] )
			? $payment_methods[ $payment_form ]
			: array();
	}

	if ( ! isset( $payment_methods[ $payment_method ] ) ) {
		return array();
	}

	return $payment_methods[ $payment_method ];
}

/**
 * Validates a PaymentIntent's Payment Methods are allowed on the Payment Form.
 *
 * @since 4.6.0
 *
 * @param array<string, mixed>          $paymentintent_args PaymentIntent arguments.
 * @param SimplePay\Core\Abstracts\Form $form Payment Form.
 * @param array<string, mixed>          $form_data Payment Form data.
 * @param array<string, mixed>          $form_values Payment Form values.
 * @return array<string, mixed>
 * @throws \Exception If the requested payment methods are not valid for the payment form.
 */
function validate_paymentintent_payment_methods(
	$paymentintent_args,
	$form,
	$form_data,
	$form_values
) {
	$payment_method_type = $form_values['payment_method_type'];

	switch ( $payment_method_type ) {
		case 'ach-debit':
			$payment_method_type = 'us_bank_account';
			break;
		default:
			$payment_method_type = str_replace( '-', '_', $payment_method_type );
	}

	$allowed_payment_methods = get_form_payment_method_ids( $form );

	if ( ! in_array( $payment_method_type, $allowed_payment_methods, true ) ) {
		throw new Exception(
			esc_html__( 'Invalid request. Please try again.', 'simple-pay' )
		);
	}

	$paymentintent_args['payment_method_types'] = array(
		$payment_method_type,
	);

	return $paymentintent_args;
}
add_filter(
	'simpay_get_paymentintent_args_from_payment_form_request',
	__NAMESPACE__ . '\\validate_paymentintent_payment_methods',
	10,
	4
);

/**
 * Validates a Subscriptions's Payment Methods are allowed on the Payment Form.
 *
 * @since 4.6.0
 *
 * @param array<string, mixed>          $subscription_args Subscription arguments.
 * @param SimplePay\Core\Abstracts\Form $form Payment Form.
 * @param array<string, mixed>          $form_data Payment Form data.
 * @param array<string, mixed>          $form_values Payment Form values.
 * @return array<string, mixed>
 * @throws \Exception If the requested payment methods are not valid for the payment form.
 */
function validate_subscription_payment_methods(
	$subscription_args,
	$form,
	$form_data,
	$form_values
) {
	$payment_method_type = $form_values['payment_method_type'];

	switch ( $payment_method_type ) {
		case 'ach-debit':
			$payment_method_type = 'us_bank_account';
			break;
		default:
			$payment_method_type = str_replace( '-', '_', $payment_method_type );
	}

	$allowed_payment_methods = get_form_payment_method_ids( $form );

	if ( ! in_array( $payment_method_type, $allowed_payment_methods, true ) ) {
		throw new Exception(
			esc_html__( 'Invalid request. Please try again.', 'simple-pay' )
		);
	}

	$subscription_args['payment_settings'] = array(
		'save_default_payment_method' => 'on_subscription',
		'payment_method_types'        => array(
			$payment_method_type,
		),
	);

	return $subscription_args;
}
add_filter(
	'simpay_get_subscription_args_from_payment_form_request',
	__NAMESPACE__ . '\\validate_subscription_payment_methods',
	10,
	4
);
