

jQuery( document ).ready(function($) {

	//console.log(stripe_form_params);

	var $body = $( 'body' );
	var scFormList = $body.find('.stripe-payment-form');


	var scCheckboxFields = scFormList.find('.sc-cf-checkbox');

	//scFormList.find('#amount').mask('00000.00', { reverse: true });
	//scFormList.find('#amount').mask('00.0000', {reverse: false});



	scCheckboxFields.change(function() {

		var checkbox = $(this);

		var checkboxId = checkbox.prop('id');

		// Hidden ID field is simply "_hidden" appended to checkbox ID field.
		var hiddenField = $body.find('#' + checkboxId + '_hidden');

		// Change to "Yes" or "No" dending on checked or not.
		hiddenField.val( checkbox.is(':checked') ? 'Yes' : 'No' );
	});
	//Process the form(s)
	scFormList.each(function() {
		var scForm = $(this);

		scForm.find('#amount').inputmask('decimal',
				{ 'alias': 'numeric',
					'groupSeparator': ',',
					'autoGroup': true,
					'digits': 2,
					'digitsOptional': false,
					'allowMinus': false,
					'prefix': '$ ',
					'placeholder': '0'
				}
		);



		if (scForm.has('.sc-payment-btn').length != 1) {
			scForm.find('.sc-payment-btn').prop('disabled', true);
		}
		// Get the "sc-id" ID of the current form as there may be multiple forms on the page.
		var formId = scForm.data('id') || '';

		// Set form's ParsleyJS validation error container.
		scForm.parsley({
			errorsContainer: function (el) {
				return el.closest('.sc-form-group');
			}
		});

		var PlaidHandler = Plaid.create({
			env: stripe_form_params['plaid-env'],
			clientName: stripe_form_params['data-name'],
			key: stripe_form_params['plaid-key'],
			product: 'auth',
			selectAccount: true,
			onSuccess: function(public_token, metadata) {
				// Send the public_token and account ID to your app server.
				//console.log('public_token: ' + public_token);
				//console.log('account ID: ' + metadata.account_id);
				scForm.find('.sc_plaidToken').val( public_token );
				scForm.find('.sc_plaidAccountId').val( metadata.account_id );
				scForm.find('.sc_stripeEmail').val( $body.find('#sc-email').val() );
				scForm.find('#str_customer_id').val( $body.find('#str_customer_id').val() );
				scForm.find('#qb_invoice_id').val( $body.find('#qb_invoice_id').val() );
				scForm.find('#amount').val( $body.find('#amount').val() );

				scForm.unbind('submit');


				scForm.submit();

				//Disable original payment button and change text for UI feedback while POST-ing to Stripe.
				scForm.find('.sc-payment-btn')
						.prop('disabled', true)
						.find('span')
						.text('Please wait...');


			},
		});


		var Stripehandler = StripeCheckout.configure({
			key: stripe_form_params['data-key'],
			image: stripe_form_params['data-image'],
			locale: 'auto',
			zipCode:'true',
			billingAddress:'true',
			token: function(token) {
				// You can access the token ID with `token.id`.
				// Get the token ID to your server-side code for use.
				scForm.find('.sc_stripeToken').val( token.id );
				scForm.find('.sc_stripeEmail').val( token.email );
				scForm.unbind('submit');
				scForm.submit();


				//Disable original payment button and change text for UI feedback while POST-ing to Stripe.
				scForm.find('.sc-payment-btn')
						.prop('disabled', true)
						.find('span')
						.text('Please wait...');


			}
		});



		scForm.find( '#stripe_pay_with_card' ).on( 'click.stripePayWithCard', function( event ) {

			if ( scForm.parsley().validate() ) {

				Stripehandler.open({
					email: scForm.find('#sc-email').val(),
					name: stripe_form_params['data-name'],
					description: "Invoice: " + scForm.find('#qb_invoice_id').val(),
					amount: Number(scForm.find('#amount').val().replace("$","").trim()).toFixed(2)*100,
					panelLabel: stripe_form_params['data-panel-label'],
				});

				event.preventDefault();
			}
		});
		scForm.find( '#stripe_pay_with_ach' ).on( 'click.stripePayWithAch', function( event ) {

			if ( scForm.parsley().validate() ) {

				PlaidHandler.open();
				event.preventDefault();
			}

		});


		// Close Checkout on page navigation:
		$(window).on('popstate', function() {
			Stripehandler.close();
			PlaidHandler.close();
		});








	});




});