=== WP Simple Pay Pro ===
Requires at least: 5.2
Tested up to: 6.1
Requires PHP: 5.6
License: GPLv2 or later

== Description ==

WP Simple Pay Pro - Add high conversion Stripe payment and subscription forms to your WordPress site in minutes.

== Changelog ==

= 4.6.4.1 - November 10, 2022 =

* Fix: Ensure frontend coupon functionality is included in build file.

= 4.6.4 - November 9, 2022 =

* New: Purchase Restrictions - Control the total number of payments that can be accepted and schedule start/end times for payment forms.
* New: Add additional feature-specific payment form templates.
* New: Payment Confirmation - add `simpay_payment_receipt_viewed` hook.
* Fix: Ensure `{tax-amount}` Smart Tag for compatibility with automatic taxes.
* Fix: Ensure automatic tax calculations are recorded in transactions table.

= 4.6.3 - November 3, 2022 =

* Fix: Ensure tax status fallback is correct in some scenarios.

= 4.6.2 - October 19, 2022 =

* New: Add additional payment form templates.
* New: Update UI for global anti-spam settings in form builder.
* Fix: Ensure "Default Country" setting is respected in the "Address" field.
* Fix: Ensure Payment Requests can be placed below certain fields.

= 4.6.1 - October 13, 2022 =

* Fix: Only set the Stripe API version to beta when required.
* Fix: Look at other Stripe records when determining if a webhook event should be handled by the plugin.
* Fix: Remove optional parameter before required parameter for PHP 8 compatibility.

= 4.6.0 - October 11, 2022 =

* New: Automatically calculate and collect tax based on customer location.
* New: Add a setting to enable email verification after multiple declines are detected.
* New: Add Thailand to list of supported Stripe countries.
* Fix: Ensure a fallback redirect URL always exists for Stripe Checkout.
* Fix: Improve compatibility with Apple Pay / Google Pay and required customer fields.
* Fix: Ensure payment methods that redirect off-site do not trigger a PHP notice on return.
* Fix: Avoid JavaScript error on certain WP Simple Pay admin pages.

= 4.5.2 - September 27, 2022 =

* Fix: Do not add TinyMCE payment form button to Element TinyMCE instances.
* Fix: Only show transactions for the global payment mode in the dashboard widget.
* i18n: Reduce duplicated strings by providing consistent context.

= 4.5.1 - September 1, 2022 =

* Fix: Ensure ID will result in simple-pay type before proceeding.
* Fix: Ensure Stripe Account ID exists before proceeding.

= 4.5.0 - August 16, 2022 =

* New: Add support for dedicated "Payment Pages". https://docs.wpsimplepay.com/articles/how-to-use-payment-pages/
* New: Smart tag improvements. Add `{card-brand}`, `{card-last4}`, `{customer-name}` smart tags.
* New: Support custom domains for Stripe Checkout.
* Fix: Hide output of payment forms that are not fully published.
* Fix: Prevent submission of empty Card field.
* Fix: Add additional server side validation to required fields.
* Fix: Clear previously shown errors when resubmitting, or switching payment methods.
* Fix: Respect payment form status on output (do not output draft payment forms).
* Fix: Remove "Edit" from bulk options.
* Fix: Update composer autoload to avoid namespace conflicts.
* Fix: Update non-licensed additional fee to 3%.

= 4.4.7.1 - July 12, 2022 =

* Fix: Only enqueue button block scripts in the block editor.

= 4.4.7 - July 5, 2022 =

* New: Add support for launching on-site overlay or off-site Stripe Checkout forms with the core block editor button block.
* New: Stripe Checkout - add support for adjustable quantities.
* New: Stripe Checkout - add support for promotion codes.
* New: Stripe Checkout - add support for collecting customer tax IDs.
* New: Stripe Checkout - add support for ACH Direct Debit subscriptions.
* New: Update styling for payment form previews.
* New: Update form builder UI and organization.
* New: Improve System Report by utilizing Site Health.
* New: Allow decimals in coupon amounts and percentages.
* Fix: Ensure dashboard widget does not cause a JavaScript error in some instances.
* Fix: Ensure UI elements that cannot be used are not shown.
* Fix: Ensure "Invoice Receipt" email does not send when it cannot be customized.
* Fix: Ensure ACH Direct Debit can be enabled as a single payment method.
* Fix: Ensure more compatibility with MySQL indexes.
* Dev: Update translator comments.
* Dev: Update Stripe API PHP library to `8.5.0`.

= 4.4.6 - June 1, 2022 =

* New: Dashboard Widget Report - view transaction volume in the last 7 or 30 days.
* New: Introduce "Help" panel. Quickly find documentation or request support.
* New: Introduce "Invoice Receipt" to send receipts on subsequent subscription invoices.
* Fix: Amount Breakdown - do not show billing cycles larger than applied coupon duration.
* Fix: Do not present irrelevant email settings in the Setup Wizard.
* Fix: Display relevant Stripe API error when saving payment forms.
* Fix: Limit billing intervals to 1 year, as imposed by Stripe.
* Fix: Avoid outputting an error when viewing a payment confirmation with invalid keys.
* Fix: Taxes - improve UX when adding tax rates less than 1%.
* Dev: Restrict payment form editing and creation with expired or invalid licenses.
* Dev: Initialize `stripe.js` with Stripe API version.
* Dev: Remove coupon management notice for existing installs.

= 4.4.5.1 - April 28, 2022 =

* Fix: ACH Direct Debit - reCAPTCHA compatibility.

= 4.4.5 - April 27, 2022 =

* New: Accept ACH Direct Debit payments without a Plaid account.
* New: Stay up to date with WP Simple Pay via the notification inbox.
* Fix: Ensure "Amount Breakdown" field properly reflects coupons, taxes, and free trials.
* Fix: Form builder price option UI refinements.
* Fix: Ensure payment form template explorer displays properly with WooCommerce active.
* Fix: Ensure top of page notice remains dismissed after dismissal.
* Fix: Ensure setup wizard does not override previously set settings.
* Dev: Remove beta release opt-ins.

= 4.4.4.1 - March 31, 2022 =

* Fix: Do not send irrelevant shipping address to payment methods that do not require it.
* Fix: Reference the correct license level needed to use coupon durations.

= 4.4.4 - March 30, 2022 =

* New: Payment Methods: Add Afterpay/Clearpay - buy now, pay later payment method.
* New: Payment Methods: Add Klarna - buy now, pay later payment method.
* New: Payment Methods: SEPA Direct Debit - add support for subscriptions with Stripe Checkout.
* New: Payment Methods: Promote certain methods based on account country.
* New: Display a notice to site admins and do not output a form without price options.
* New: Allow "Start Trial" to be customized when a price option has a trial period.
* New: Add Stripe Instant Payouts education.
* New: Add additional payment form templates.
* New: Improve license key UI/UX.
* Fix: Add more specific UTM arguments to URLs.
* Fix: Expand expected event window for webhook events.
* Fix: Add an explicit `line-height` definition to form controls.
* Fix: Add additional opinionated styles to form preview notice.
* Fix: Update "Tested up to" to `5.9`.

= 4.4.3 - March 3, 2022 =

* New: Payment Form Templates - choose from premade templates to quickly create payment forms.
* New: Elementor - select a payment form to launch in a "Button", "Price Table," or "Call to Action" widget.
* New: Divi - select a payment form to launch in the "Button", "Price Table", or "Call to Action" module.
* Fix: Stripe Checkout - disable "Apple Pay / Google Pay" custom field.
* Fix: Allow "Date" custom field default value to be empty.
* Fix: Ensure screen options can be used to hide additional metaboxes added to payment form settings.
* Fix: Ensure classic editor payment form inserter displays payment form title.

= 4.4.2 - February 10, 2022 =

* New: Add "Setup Wizard" for new installs.
* New: Add "WP Simple Pay" payment form block.
* New: Load payment form preview in an isolated environment. Add helper links.
* New: Lite - link branding bar logo to website.
* New: Add option to permanently dismiss webhook event warning.
* New: Update supported country list for payment request button.
* Fix: Lite - Ensure default $1.00 amount can be saved on initial form publish.
* Fix: Update custom amount placeholder when changing price options.
* Fix: Do not display empty form state when searching for payment forms.
* Fix: Update reCAPTCHA setup description.
* Fix: Avoid additional overflow in tax rate modal.
* Fix: Ensure "Test Mode" can be toggled when there is no active connection.
* Fix: Only show published pages in settings dropdowns.
* Fix: Redirect back to relevant page when connecting to Stripe.
* Dev: Update test matrix against WordPress 5.9.

= 4.4.1 - January 11, 2022 =

* New: Lite - add a 2% application fee to Checkout Sessions for new Stripe Connect connections.
* New: Show a notice when an expected webhook event has not been received. Improve webhook configuration UI.
* New: Stripe Checkout - Add support for collecting phone numbers.
* New: Show an alert when leaving unsaved changes on payment forms.
* New: Add "Copy to clipboard" buttons to payment form shortcodes and system report.
* New: Add an admin notice requesting a review after 14 days of installation/update.
* Fix: Update admin bar from "Simple Pay" to "WP Simple Pay".
* Fix: Avoid attempts at updating nonexistent elements.
* Fix: Avoid error when syncing coupons with a `redeem_by` date in the past.
* Dev: Update copyright dates.

= 4.4.0 - December 13, 2021 =

* New: Add additional product education for specific license types.
* New: Add product education dashboard widget.
* New: Add "About Us" menu item.
* New: Add branding to all plugin pages.
* New: Various plugin UI/UX improvements.
* New: Show notice when managing taxes if there is no Stripe connection.
* New: Show notice if payment form title is empty.
* New: Add confirmation when disconnecting a Stripe account.
* New: Only allow Stripe API keys to be managed manually if previously manually set.
* New: Add empty states to payment form list when no forms have been created.
* New: Improve ACH Debit bank selection UX.
* New: Show global reCAPTCHA and tax settings within payment form settings.
* Fix: Add consistency to UTM parameters in `*.wpsimplepay.com` outbound links.
* Fix: Ensure `simpay_get_currencies` filter is called.
* Fix: Decode payment form titles in coupon restriction search.
* Fix: Ensure translations are loaded early enough for all strings.
* Dev: Add plugin service container, service providers, and subscriber architecture in `./src`.
* Dev: Add WordPress `5.9` to test matrix.

= 4.3.1 - November 9, 2021 =

* Fix: Syntax error for PHP < 7.3.

= 4.3.0 - November 8, 2021 =

* New: Manage coupons directly in WP Simple Pay. https://docs.wpsimplepay.com/articles/coupons
* New: Limit coupon application to specific payment forms. https://docs.wpsimplepay.com/articles/coupons
* New: Show limited discount amount if coupon has a limited duration.
* New: Default subscription price option interval to 1 month. Dynamically pluralize interval nouns.
* New: Automatically retry Stripe API requests on a network failure.
* Fix: Respect date format setting when collecting date values.
* Fix: Improve date field datepicker styling.
* Fix: Link to relevant Stripe payment mode in webhook endpoint settings.
* Fix: Do not pass unapplied coupon code to Stripe metadata.
* Fix: Load some license functionality in frontend context to provide compatibility with WPMU Dev Dashboard 4.11.4 -- 4.11.5 has since been released.
* Fix: Avoid fatal error on the_title filter when used incorrectly by other plugins.
* Fix: Ensure Stripe API errors are properly output.
* Dev: Add `simpay_plaid_client_name` filter.
* Dev: Update `stripe/stripe-php` to `7.97.0`.
* Dev: Update `\Stripe` namespace to `\SimplePay\Vendor\Stripe`.
* Dev: Update `\BerlinDB` namespace to `\SimplePay\Vendor\BerlinDB`. Update BerlinDB to 2.0.

= 4.2.3 - September 21, 2021 =

* Fix: ACH Debit + Plaid: support OAuth for institutions that require it. Plaid account changes required. Please review the documentation: https://docs.wpsimplepay.com/articles/accepting-ach-debit-payments/
* Fix: Avoid `get_query_var()` usage within admin panel for better compatibility with other plugins.
* Fix: PHP `5.6.40` support when managing subscriptions.
* i18n: Update Romanian `ro_RO` translation.

= 4.2.2 - August 11, 2021 =

* New: Add Brazil and United Arab Emirates to Stripe country list.
* Fix: Ensure step attribute is set when changing custom input type to number.
* Fix: Backport in-plugin upgrade URL fix from Lite.
* Fix: Do not output PHP warnings when visiting Payment Confirmation page directly.
* Fix: Avoid creating a duplicate Customer record when using on-site fields with Stripe Checkout.

= 4.2.1 - June 23, 2021 =

* Fix: Do not attempt custom amount validations against legacy `plan_` objects.
* Fix: Show generic error message text when REST API response is invalid JSON.
* Fix: Restore `.toggleOverlayForm()` JavaScript API method.

= 4.2.0 - June 8, 2021 =

* New: Add support for one-time and recurring payments via SEPA Direct Debit.
* New: Add support for one-time payments via Alipay.
* New: Add support for one-time payments via FPX.
* New: Add support for one-time payments via Bancontact.
* New: Add support for one-time payments via giropay.
* New: Add support for one-time payments via Przelewy24.
* New: Add "Tax ID" Customer field to collect and record customer's tax IDs.
* New: Alert users of test/live and publishable/secret swapped API credentials.
* New: Improve "opinionated styles" including checkbox and radio styles.
* New: Hide "Subtotal" in "Amount Breakdown" until subtotal differs from total.
* New: Update UI for enabling multiple payment methods in Payment Form settings.
* New: Add India as a supported account country.
* New: Rename "Payment Options" to "Price Options" in Payment Form settings.
* New: Disable Payment Methods that are not available in the set account country.
* Fix: Show error message when custom amount field is left blank.
* Fix: Escape apostrophe's and special characters in price option labels to avoid parsing errors.
* Fix: Correct closing `</legend>` tag on price selector.
* Fix: Rename "Macedonia" to "North Macedonia".
* Fix: Reset internal "recurring" state when switching between optional recurring price options.
* Fix: Lighten placeholder colors on Payment Form settings to avoid confusion with values.
* Fix: Search "Title" and "Description" fields when searching Payment Form list.
* Fix: Ensure standard "Radio Select" custom fields do not interfere with quantity fields.
* Fix: Valid multiline text fields as required, if needed, before Payment Request submission.
* Fix: Ensure price selector inputs have unique IDs.
* i18n: Update Romanian `ro_RO` translation.
* Dev: Update Stripe API PHP library to `7.77.0`.
* Dev: Update JavaScript coding standards.
* Dev: Update project UTM tags and usage analytic opt-in flow.

= 4.1.8 - May 24, 2021 =

* Fix: Don't handle `invoice.payment_succeeded` for Invoices not originating from WP Simple Pay subscriptions.
* Fix: Avoid duplicate emails being sent on both `charge.succeeded` and `payment_intent.succeeded`.

= 4.1.7 - May 13, 2021 =

* New: Send "Payment Confirmation" and "Payment Notification" emails for delayed ACH Debit payments.
* Fix: Prices API - use Test-mode specific data if available and form is in Test Mode.
* Fix: Prices API - avoid potential race condition creating an empty Product in Stripe.

= 4.1.6 - May 6, 2021 =

* Fix: Ensure manually calculated coupon amounts return a valid integer.
* Fix: Ensure ACH Debit Subscriptions are not incorrectly redirected to Payment Failure page.

= 4.1.5 - April 13, 2021 =

* Fix: Properly store inclusive tax rate in Payment metadata for use with {tax-amount} smart tag.
* Fix: Improve checks on the Stripe Connect disconnection process.

= 4.1.4 - April 7, 2021 =

* Fix: Ensure WordPress 4.9.8 compatibility by removing `WP_Error::has_errors()`.
* Fix: Ensure `{charge-date}` smart tag is translated to the current site language.
* Fix: Ensure `{{amount}}` placeholder is updated in ACH Debit "Select Bank" button.
* Fix: Ensure a "Custom Amount Input" is added to the custom field list during migration if legacy amount filter is used.
* Fix: Avoid PHP error when retrieving custom field list if not previously set.

= 4.1.3 - March 25, 2021 =

* Fix: Remove trailing commas for PHP < 7.3 support.

= 4.1.2 - March 25, 2021 =

* New: Introduce `{form-title}` and `{form-description}` confirmation tags.
* New: Sync price options when a Payment Form's Payment Mode is explicitly non-global.
* Fix: Ensure Stripe Checkout Payment Methods can be synced to the Customer when no on-site form fields are present.
* Fix: Provide a static string fallback when both Payment Form "Title" and WordPress "Site Title" are blank.

= 4.1.1 - March 18, 2021 =

* New: Expand a singular price option by default.
* Fix: Add `required` attribute to Payment Form title.
* Fix: Use correct function name when handling legacy `_default_amount` filter.
* Fix: Remove default box shadow on form controls for iOS devices.
* Fix: Use correct function when applying a fixed amount coupon.
* Dev: Add `simpay_get_payment_form_price_options` filter.

= 4.1.0 - March 16, 2021 =

* New: Create and manage payment options directly in the Payment Form. Allows multiple amounts, mixed payment types (one-time and subscription, and custom), mixed currencies, and new display styles. Powered by Stripe's Prices API.
* New: Create and manage tax rates directly in WP Simple Pay. Allows multiple inclusive and exclusive tax rates.
* New: Update opinionated styles for accessibility and theme compatibility.
* New: Add `{payment-type}` smart tag for One-time or Subscription output.
* New: Map Customer and Payment Method information to Customer object when using Stripe Checkout.
* New: Show Start Trial on Checkout or Payment Button if using a Subscription with trial.
* Fix: Ensure variable exists in shortcode preview.
* Fix: Change "Send Test Email" tool to "Preview Email" to avoid confusion about email deliverability.
* Fix: Add a unique ID to Coupon nonce hidden field.
* Fix: Prevent sending emails for `invoice.payment_succeeded` and `payment_intent.succeeded` events that are unrelated to WP Simple Pay.
* Fix: Use WordPress' `wp-polyfill` if available. Avoid loading multiple copies of the polyfill.
* Fix: Add `setup_future_usage` for Checkout Session creation.
* Fix: Ensure "Save Plugin Settings" setting is available in Lite.
* Fix: Remove invalid `SDG` currency.
* Fix: Use the current admin color scheme's link color for vertical tab indicator.
* Dev: Update Stripe API to `2020-08-27`.
* Dev: Update `ro_RO` translations.

= 4.0.2 - February 1, 2021 =

* Fix: Ensure currency symbol position preview is correct in settings.
* Fix: Avoid PHP notice if no webhook events are recorded.
* Fix: Update Stripe Connect messaging for temporary accounts.
* Fix: Use updated admin URLs to ensure admin menu items are highlighted consistently.
* Fix: Ensure account display name appears in "Connect with Stripe" messaging in Test Mode.

= 4.0.1 - January 5, 2021 =

* New: Give feedback about webhook configuration in email settings.
* Fix: Adjust opinionated form styles for Twenty Twenty One theme.
* Fix: Ensure "Test Email" tool sends an email when Subscriptions are not enabled.
* i18n: Update Romanian translations.

= 4.0.0 - December 1, 2020 =

* New: Update plugin settings screen UI and UX.
* New: Add "Payment Receipt", "Payment Confirmation", and "Upcoming Invoice" email settings.
* New: Add "Resend Payment Receipt" tool.
* New: Allow One-Time Custom Amount + predefined amounts in the same form.
* New: Add support for Stripe's Customer Billing Portal.
* New: Allow Subscriptions to be cancelled when managing the Payment Method.
* New: Parse shortcodes in `[simpay_payment_receipt]` shortcode.
* New: Show notice and don't output Payment Form if REST API is disabled.
* New: Prevent switching to an invalid Payment Mode in per-form settings.
* New: Payment Request - Add "Booking" type and "Button Theme" setting.
* Fix: Clarify Lite to Pro upgrade notice and steps.
* Fix: Ensure plugin update notice spans all columns in table.
* Fix: Ensure Subscription metadata is mapped to PaymentIntent when using Stripe Checkout.
* Fix: Ensure Dropdown used for predefined Amounts handles decimals.
* Fix: Avoid page "jump" when loading Payment Form settings.
* Fix: Avoid additional tab stop when using multiple Payment Methods.
* Fix: Avoid sending URLs in form data for ModSecurity rules.
* Fix: Ensure ACH Debit Payment Forms load Plaid when used second on the page.
* Fix: Ensure notices can be persistently dismissed in WordPress 5.6.
* Fix: Always show "Sandbox" and "Production" Plaid keys for improved UX.
* Fix: Ensure "Recurring Amount Toggle" custom field settings are used to create Subscriptions.
* Fix: Retrieve Coupons using the current form's payment mode.
* Fix: Avoid PHP error when deleting unused Customer record with iDEAL.
* Dev: Update Stripe API PHP Library to `7.53.0`.
* Dev: Rename Stripe script handle to `sandhills-stripe-js-v3`.
* Dev: Rename Stripe script handle to `simpay-google-recaptcha-v3`.

= 3.9.8 - October 20, 2020 =

* New: Add theme compatibility for reCAPTCHA badge display.
* Fix: Avoid double conversion to cents when using comma decimal separators.
* Fix: Ensure generated Plans without Customer data are automatically purged.
* Fix: Disable `autocomplete` in plugin setting inputs.
* Fix: Ensure long Plan names do not cause overflow in form settings.
* Fix: Ensure webhook event dates are recorded as valid dates.
* Fix: Remove references to Public Key in Plaid settings.
* Fix: Do not register unused API endpoints in Lite.
* Fix: Ensure reCAPTCHA compatibility with Payment Request buttons.
* i18n: Update Romanian translations.

= 3.9.7 - September 17, 2020 =

* New: Add notice discouraging the use of manually set API keys.
* New: Add collected address to iDEAL Payment Method.
* New: Add invalid reCAPTCHA configuration feedback to settings.
* Fix: Ensure one-time set amounts can be dynamically set.
* Dev: Add reCAPTCHA and rate limit information to System Report.

= 3.9.6 - September 9, 2020 =

* New: Add reCAPTCHA validations to API endpoints (in addition to page load).
* i18n: Add Japanese translation.
* i18n: Update Romanian translation.

= 3.9.5 - August 25, 2020 =

* New: Add IP-based rate limiting to internal API endpoints.
* New: Add setting to increase minimum reCAPTCHA threshold.
* Fix: Remove extra wrapping `div` from output in the "Text" custom field.
* Dev: Display latest webhook event in System Report.

= 3.9.4 - August 12, 2020 =

* Fix: Use global Payment Mode when processing Invoice webhooks if no form context is available.
* i18n: Update `simple-pay-ro_RO`

= 3.9.3 - August 11, 2020 =

* Fix: Update ACH Debit via Plaid to work with Plaid's new Link tokens. https://plaid.com/docs/upgrade-to-link-tokens/
* Fix: Avoid potential PHP error when a Payment has no Charges.
* Fix: Allow "Same billing and shipping info" to submit a "required" Address field.
* Fix: Ensure checkboxes in Safari do not shrink when the label is long.
* Fix: Provide context-specific Stripe error message string for invalid coupons.
* Fix: Ensure {charge-date} Payment Confirmation Smart Tag is output in the site's local timezone.
* Fix: Avoid marking Webhook processing as failed if the object is missing identifying metadata.
* Fix: Ensure approriate error message is shown when an SCA check fails.

= 3.9.2 - July 27, 2020 =

* Fix: Avoid PHP error on some versions when accessing class property.
* Fix: Ensure manual verification of webhooks works in all modes when not using an Endpoint Secret.
* Fix: Ensure Single Subscription settings are properly reflected when using per-form Live Mode.
* Fix: Ensure "Subscription Plan Selector" can have a custom label.
* Fix: Ensure generated Customer records with active payments are not migrated are not retroactively deleted.
* Fix: Improve Stripe Webhook setting strings.
* Dev: Update file copyrights.

= 3.9.1 - July 8, 2020 =

* Fix: Ensure HTML is allowed in custom field labels.
* Fix: Ensure address postal code is mapped to Customer object.
* Fix: Ensure radio fields can increment quantity.
* Fix: Ensure proper namespace is referenced when deleting a generated Plan.

= 3.9.0 - July 6, 2020 =

* New: Add ACH Debits (via Plaid) Payment Method.
* New: Add per-form "Test Mode" toggles.
* New: Add localization support for Stripe errors.
* New: Add default labels and remove placeholders in form builder.
* New: Update available Stripe Elements and Stripe Checkout locales.
* Fix: Update supported country and currency lists.
* Fix: Wait 24 hours before deleting generated records.
* Fix: Disable custom fields that cannot be added to the current Payment Form.
* Fix: Avoid PHP notice in `array_unique`.
* i18n: Update `simple-pay-ro_RO`
* Dev: Update WordPress coding standards.
* Dev: Update Stripe PHP library to `7.37.1`.
* Dev: Add `simpay_send_upcoming_invoice_email` filter.

= 3.8.3 - June 1, 2020 =

* Fix: Ensure Payment Request Button is initialized regardless of form type used.
* Fix: Ensure Payment Request Button button type is properly saved.
* Fix: Remove 20 field limit.

= 3.8.2 - May 13, 2020 =

* Fix: Ensure "Amount" and "Quantity" settings in Radio and Dropdown fields respect settings.
* Fix: Add extra error catching around incorrectly configured reCAPTCHA.
* Fix: Avoid duplicate filter names with inconsistent argument signatures which can cause multiple Payment Forms on the same page to not output correctly.

= 3.8.1 - May 7, 2020 =

* Fix: Ensure custom post type row actions are not overridden.
* Fix: Ensure relevant fields show when Subscriptions are not active on install.

= 3.8.0 - April 29, 2020 =

* New: Add one-time amount support for iDEAL Payment Method.
* New: Update Payment Form builder user interface design and accessibility.
* New: Stripe Checkout - Allow collection of Shipping Address.
* New: Add "Heading" custom field type.
* New: Support importing and exporting Payment Forms.
* New: Use matching "Test Mode" badge styles in WordPress toolbar.
* Fix: Hide "Image" field in "Stripe Checkout Display" when using Subscriptions (unsupported by Stripe API).
* Fix: Stripe Checkout - Fall back to generic "WP Simple Pay" line item has no name.
* Fix: Hide "minimum amount" error until a custom amount has been entered.
* Fix: Ensure name and email from Payment Request Button are used for Customer record.
* Fix: Ensure adequate spacing under Payment Form title with opinionated styles.
* Fix: Remove "Subscription Plan Selector" custom field when not using Subscriptions.
* Fix: Remove Subscription "Custom Amount" custom field when not using Subscriptions.
* Fix: Remove "Custom Amount" custom field when not using custom amounts.
* Fix: Update notice about Product Plans being separate in Live and Test modes.
* Dev: Use WordPress core custom post type screens for managing Payment Forms.
* Dev: Update Stripe API PHP library to `7.28.0`.
* Dev: Update Stripe API version to `2020-03-02`.
* Dev: Update `EDD_SL_Plugin_Updater` to `1.7`.
* Dev: Add `\SimplePay\Core\Utils\Collection` for managing generic registries.
* Dev: Cache calls to `\SimplePay\Vendor\Stripe\Plan::all()` when more than 25 Plans are found.
* Dev: Use WordPress core `.button` styles for WP Simple Pay button base.
* Dev: Allow `SIMPLE_PAY_LICENSE_KEY` to be defined in `wp-config.php`.
* Dev: Add `simple-pay-es_ES` translation files.

= 3.7.1 - March 2, 2020 =

* New: Update Romanian translations.
* Fix: Avoid extra formatting changes when converting amounts to dollar.
* Fix: WordPress 5.4 UI compatibility.
* Fix: Validate reCAPTCHA on form load and block submissions if necessary.

= 3.7.0 - February 25, 2020 =

* New: Allow payment methods to be updated before a Subscription renews.
* New: Implement confirmation smart tag support for accessing payment, subscription, and customer record data via dynamic tags such as `{customer:name}`.
* New: Split structural and visual styles and inherit more theme defaults when no styles are applied.
* New: Disable entire form during submission.
* New: Add "Hidden" custom field type.
* New: Allow field values to be set dynamically with via `simpay_form_{$form_id}_field_{$field_id}_default_value` filters.
* New: Add option to always hide the ZIP/postal code on the Credit Card field.
* New: Improve default form styles.
* New: Stripe Checkout - Add support for Stripe Checkout's "Booking", "Donate", and "Pay" button types.
* New: Stripe Checkout - Automatically remove generated Customer, Product, and Plan records upon completed Stripe Checkout Sessions.
* New: Stripe Checkout - Add separate "Payment Cancelled" page setting for incomplete Stripe Checkout Sessions.
* New: Stripe Checkout - Add reCAPTCHA v3 support.
* New: Stripe Checkout - Add notice about branding options in form settings.
* Fix: Ensure the order of user-select subscription plans save correctly.
* Fix: Ensure WordPress core's `WP_List_Table` is used to prevent future markup change breakage.
* Fix: Reduce complexity of "Upgrade" submenu item for Lite.
* Fix: Strip HTML from custom field label previews.
* Fix: Update Mexican Peso symbol to MXN
* Fix: WordPress 5.3 style adjustments.
* Fix: Only enqueue jQuery UI Datepicker when needed.
* Fix: Align tax calculations with Stripe and ensure all results are valid.
* Dev: Remove Javascript Coding Standards Checker in favor of ESLint.
* Dev: Rename class-based files to include a class- prefix.
* Dev: Add WordPress Coding Standards rulesets.
* Dev: Ensure all files have a PHPDoc . header.
* Dev: Update Stripe API Version to 2019-12-03.
* Dev: Update Stripe API PHP bindings to 7.20.0.

= 3.6.8 - January 7, 2020 =

* New: Hide generated Plans from Subscription Plan selector in form settings.
* Fix: Handle saving payment confirmation messages in WordPress 5.3.1+
* Fix: Ensure multiple forms with Payment Request Buttons on the same page do not interfere with each other's validation.
* Dev: Rename duplicate hook `simpay_before_subscription_created` to `simpay_after_subscription_created`.

= 3.6.7 - December 19, 2019 =

* Fix: Avoid ending Subscriptions too early in certain instances of Webhook failures.

= 3.6.6 - December 17, 2019 (skipped for version 3.6.7) =

* New: Show a descriptive error (vs. -1) when the coupon AJAX security check fails.
* New: Attach generated Plans to existing Product and prepend `- generated by WP Simple Pay`.
* Fix: Ensure Payment Request Buttons appear in Overlay forms.
* Fix: Ensure references to `$` in the WordPress admin resolve properly.
* Fix: Stripe Checkout - Always create a Stripe Customer record to track coupon usage.
* Fix: Apply the discount amount against item quantities and reflect properly in the UI.
* Fix: Ensure Subscription's PaymentIntent has fully `succeeded` before redirecting.
* Fix: Ensure `{max-charges}` properly outputs the amount of maximum charges.
* Dev: Introduce `simpay_customer_create` filter to return a Customer ID and short circuit creation.
* Dev: Introduce `simpay_stripe_api_publishable_key` and `simpay_stripe_api_secret_key` filters.
* Dev: Introduce `\SimplePay\Core\Payments\Payment_Confirmation\get_confirmation_data()` for use in custom snippets to return any relevant confirmation data.
* Dev: Add `?form_id=` to Payment Confirmation and Error redirect URLs.

= 3.6.5 - November 13, 2019 =

* Fix: Ensure "Manage API keys manually" button is always available.
* Fix: Ensure billing frequency appears in `{recurring-amount}` payment confirmation smart tag.
* Fix: Ensure Stripe Elements locale is used in the Credit Card field.
* Fix: Ensure custom cron schedule is registered.
* Fix: Ensure Statement Descriptor always results in a valid string.
* Fix: Ensure WordPress 5.3 admin UI appears correctly.
* Fix: Avoid PHP notices for undefined Stripe objects on payment confirmation.
* Fix: Clarify Stripe Checkout "Require Billing Address" form setting description.

= 3.6.4 - October 1, 2019 =

* Fix: Avoid rounding error when converting amounts to cents in PHP 7.1+.
* Fix: Do not reference "Stripe Checkout overlay" in setting descriptions.
* Fix: Remove coupon when total amount changes to ensure no negative values exist.
* Fix: Ensure "Total Amount Label"'s "Show Recurring Total" label uses the correct subscription interval.
* Fix: Ensure "Country" field appearance is correct in Firefox.

= 3.6.3 - September 20, 2019 =

* Fix: Stripe Checkout - Use Site Title if Company Name field is blank.
* Fix: IE 11 Javascript support for `Promise` and `Object.assign`
* Fix: IE 11 CSS support for `flexbox` alignment.
* Fix: Avoid uncaught PHP error while handling legacy `simpay_stripe_charge_args` filter.
* Fix: Ensure "Recurring Amount Toggle" field can be used with "One-Time Amount" payment forms.
* Fix: Ensure Stripe Plans created before 2018-05-21 have spaces stripped before using to generate a new ID.
* Dev: Add `simpay_webhook_subscription_created` and `simpay_webhook_payment_intent_succeeded` actions
       fired via Webhooks to allow easier action to be taken after successful payments.

= 3.6.2 - September 17, 2019 =

* Fix: Stripe Checkout - only generate a Customer record when collecting Customer-specific data.
* Fix: Stripe Checkout - update available locales (add Polish, Portuguese).
* Fix: Ensure custom subscription names can be generated when item description is not available.
* i18n: Update Romanian translations.

= 3.6.1 - September 14, 2019 =

* Fix: Avoid creating duplicate Customer records when multiple Stripe Checkout forms exist on the same page.
* Fix: Ensure Overlay form types can always popup when multiple exist on the same page.
* Fix: Remove attempt at passing POST values through Stripe Checkout to ensure a valid return URL is generated.
* Fix: Pass full Customer object to legacy `simpay_subscription_created` hook.

= 3.6.0 - September 12, 2019 =

* New: Strong Customer Authentication (SCA) support.
* New: Support Stripe's off-site Checkout pages.
* New: Add setting to change Card field locale.
* New: Show separate Initial Setup Fee and Plan Setup Fee line items for Subscriptions.
* New: Improve Stripe connected account information in admin settings.
* New: Help WP Simple Pay improve by reporting usage analytics.
* New: Automatically attempt to reverify domain for Apple Pay when switching API keys.
* Fix: Ensure custom subscription amount does not inherit a previously selected plans' setup fee.
* Fix: Remove extra apostrophe escaping from meta in the Stripe Dashboard.
* Dev: Update to v6.43.0 of Stripe's PHP API library.
* Dev: Remove WP_Session library.
* Dev: Enforce Stripe's PHP library `cURL` requirement.
* Dev: Deprecated many hooks/filters that no longer apply to the new payment flows.
       Please review any custom snippets that may change functionality.

= 3.5.3 - July 10, 2019 =

* Fix: Ensure setup fees over 999 are calculated properly.
* Fix: Ensure trial periods do not end too soon or too late by using the same timezone settings as Stripe.
* Dev: Update ro_RO translations.

= 3.5.2 - June 17, 2019 =

* Fix: Ensure Checkout Button amount is updated when amount changes, when using reCAPTCHA.
* Fix: Use filtered description for Stripe Payment description.
* Fix: Remove removeProp usage for better jQuery 3.x compatibility.
* Fix: Do not attempt to combine `boolean` and `array`.
* Fix: Update "Tax Percent" setting description.

= 3.5.1 - May 22, 2019 =

* Fix: Respect Payment Button and Checkout Button "Text" and "Processing Text" settings when outputting buttons.
* Fix: Ensure Payment Button style defaults to "Stripe blue" when no default is available.
* Fix: Hide Payment Request Button (Apple Pay/Google Pay) when Account Country value is not supported.
* Fix: Ensure one-time set amount payments that are toggled recurring can be properly processed.
* Dev: Update ro_RO translations.
* Dev: Add fr_FR translations.

= 3.5.0 - May 13, 2019 =

* New: Add "Pay", "Donate", and "Buy" button types to "Apple Pay/Google Pay Button". Find out more: https://docs.wpsimplepay.com/articles/apple-pay-google-pay/
* New: Attach coupon usage to Stripe Customers to allow accurate coupon usage tracking. Find out more: https://docs.wpsimplepay.com/articles/adding-coupons-stripe/
* New: Accurately track coupon usage and respect restrictions created in Stripe. Find out more: https://docs.wpsimplepay.com/articles/adding-coupons-stripe/
* New: Add "Customer Phone" custom form field. Find out more: https://docs.wpsimplepay.com/articles/custom-form-fields/
* New: Save Billing Address data to Stripe Charge record.
* New: Save Shipping Address data to Stripe Customer record.
* New: Option to automatically create and verify Webhooks in Stripe (not available with Stripe Connect). Find out more: https://docs.wpsimplepay.com/articles/webhooks/
* New: Allow opinionated form styles to be applied to on-page fields while using Stripe Checkout. Find out more: https://docs.wpsimplepay.com/articles/style-options/
* New: Add option for reCAPTCHA v3 Invisible verification. Find out more: https://docs.wpsimplepay.com/articles/recaptcha/
* New: Per-form button styling options. Find out more: https://docs.wpsimplepay.com/articles/style-options/
* New: Verify Stripe Webhook signatures for enhanced security. Find out more: https://docs.wpsimplepay.com/articles/webhooks/
* New: Alert the need to reconnect to Stripe and update other settings when toggling "Test Mode".
* New: Improve onboarding notices and alerts.
* New: Run `simpay_{$filter}` alongside all usage of `simpay_form_{$form_id}_{$filter}`
* New: Add helpful hint about additional fields available while using Stripe Checkout.
* New: Add option to disconnect from Stripe.
* New: Show site administrators a notice when Stripe API keys are missing.
* New: Add viewport tag to <head> for Stripe Elements.
* Fix: Ensure subscription product plan nickname is output in label by default.
* Fix: Ensure coupon code label is not improperly appended multiple times.
* Fix: Ensure {item-description} payment details tag is not empty for subscriptions.
* Fix: Flush plugin update cache when enabling beta versions.
* Fix: Remove potential theme-set background image from payment button.
* Fix: Remove custom amount field while using subscriptions.
* Fix: Group Stripe Checkout display settings.
* Fix: Avoid Javascript error in Internet Explorer 11.
* Fix: Do not pass `country` to Stripe Checkout configuration.
* Fix: Reenable Checkout Button with the proper value (Pay {{amount}}, etc).
* Dev: Remove extraneous colons from field labels.
* Dev: Add notice about upcoming changes to Stripe Checkout.
* Dev: Implement robust webhook handling with idempotency.
* Dev: Update company name throughout files.
* Dev: Updated to Stripe PHP library v6.34.2.
* Dev: Updated to use Stripe API version 2019-03-14.

= 3.4.0 - March 28, 2019 =

* New: Accept payments through Apple Pay and Google Pay using the Payment Request API. Find out more: https://demo.wpsimplepay.com/apple-pay-google-pay/
* New: Separate custom field types in to "Customer", "Payment", and "Standard" categories.
* New: Prevent duplicate Payment and Customer fields from being added to the same form.
* New: Enable ZIP/Postal Code verification by default on new forms.
* New: Allow "Company Name" value to be blank.
* New: Add "Country" setting in Stripe Setup settings to send with Stripe API requests.
* Fix: Show correct minimum amount error when custom amount is invalid.
* Fix: Ensure multiple of the same overlay form can appear on a single page.
* Fix: Update styling for `number` amount field for better currency symbol alignment.
* Fix: Ensure Embedded form width limited to 400px.
* Fix: Avoid text clipping on Dropdown fields.
* Fix: Change "Plan Setup Fee" string to "Initial Setup Fee" for additional fees applying to all subscription plans.
* Fix: Clarify what amounts the tax rates are applied to in the setting's description.
* Dev: Updated to Stripe PHP library v6.30.4.
* Dev: Updated to use Stripe API version 2019-02-19.
* Dev: Remove unused files.

= 3.3.10 - March 4, 2019 =

* Fix: Ensure custom overlay forms remains open in Safari.

= 3.3.9 - March 1, 2019 =

* Fix: Ensure custom overlay forms launch properly on mobile browsers.

= 3.3.8 - February 28, 2019 =

* New: Add updated methods for toggling custom overlay forms.
* Fix: Ensure generated item description uses correct dollar amount in Stripe receipt.
* Fix: Ensure generated item description is translateable.
* Fix: Ensure Overlay payment forms appear over all other content.
* Fix: Ensure Overlay payment form text is not blurry.
* Fix: Allow coupon to be validated using "Enter" keypress.
* Fix: Ensure payment form can be submitted using "Enter" keypress (when not adding a coupon).
* Fix: Prefix Stripe API request from library with "WordPress".
* Fix: Avoid PHP error when requesting subscriptions that do not exist.
* Fix: Keep radio button indicators when reordering custom fields or subscription plans.
* Fix: Ensure "Dropdown" custom field type can be properly set as required.
* Dev: Requires WordPress 4.9+

= 3.3.7 - February 21, 2019 =

* Fix: Correct setup fee calculation when submitting subscription sign up more than once.
* Fix: Correct tax amount displayed on payment confirmation due to improper decimal placement.
* Fix: Don't format custom amount before validation when input type set to 'number'.
* Tweak: Set z-index to max for custom overlay form container so it appears above other high z-index elements.
* Tweak: Add dimiss link to Stripe Connect notice.
* Tweak: Minor payment form default style updates.

= 3.3.6 - January 8, 2019 =

* Fix: Adjust form object to ID conversion.
* Tweak: Allow entering of Stripe API keys manually before first time authorizing with Stripe Connect.

= 3.3.5 - January 6, 2019 =

* Fix: Corrected tax amount calculation regardless of comma or period decimal separator.
* Fix: Corrected Stripe Checkout amount when using enter key to submit immediately after entering a new custom amount.
* Fix: Payment form rendered more than the intended amount when used multiple times on the same page.
* Tweak: When custom amount input type set to "number" using a filter, the step attribute will be set to "0.01" to allow decimals.
* Dev: Check and convert form object to ID when necessary.

= 3.3.4 - December 28, 2018 =

* New: You can now easily connect your Stripe account with Stripe Connect. See your settings page for more details.
* New: Display admin notice about upcoming PHP 5.6 requirement if running an older version.
* Fix: Don't set focus to the first field in embedded forms (only in overlay forms).
* Fix: Reset checkout button text back to original after card validation fails.
* Fix: Prevent JavaScript errors when trying to validate hidden shipping address fields.
* Fix: Prevent Stripe.js version conflict with Restrict Content Pro (and possibly other plugins using Stripe.js).
* Fix: Correct rounding issue in total amount label with subscription plans that contain cents.
* Dev: Updated non-specific form filter hooks with added form ID.
* Dev: Added filter hook for additional custom field types.

= 3.3.3 - December 5, 2018 =

* Fix: Coupon codes used with set single plan subscription forms improperly calculated.
* Fix: Trial subscriptions were charging immediately in some cases.
* Fix: Setup fees for user-selectable subscriptions miscalculated in the initial payment label.
* Fix: Allow decimal numbers to be used for quantity multiplier custom fields.

= 3.3.2 - December 3, 2018 =

* Fix: Error adding metadata to invoices after finalizing in some cases.

= 3.3.1 - December 2, 2018 =

* Fix: Amount off coupon codes improperly calculated.
* Fix: Embedded and Overlay custom fields not saving to Stripe metadata.

= 3.3.0 - November 27, 2018 =

* New: Added embedded and overlay custom form display options. See https://wpsimplepay.com/embedded-overlay-custom-form-display/
* Dev: Remove excessive post meta calls.
* Tweak: Updated to Stripe PHP library v6.23.0.

= 3.2.5 - November 12, 2018 =

* Fix: Incorrect variable name in new beta opt-in option.

= 3.2.4 - November 12, 2018 =

* New: Added setting to opt into WP Simple Pay Pro beta releases.

= 3.2.3 - October 3, 2018 =

* Dev: Added filter hook to add or modify arguments when a Stripe customer is created.
* Dev: Added filter hook to add or modify arguments when a Stripe subscription is created.
* Dev: Added filter hook to allow additional attributes to be added to the payment form tag.
* Dev: Updated and hardened webhook code incorporating updates as of Stripe API 2018-09-06.
* Dev: Updated to Stripe PHP library v6.19.1.

= 3.2.2 - August 3, 2018 =

* Fix: Subscriptions with trials incorrectly charging right away as of Stripe API 2018-05-21.
* Dev: Updated to Stripe PHP library v6.15.0.

= 3.2.1 - July 27, 2018 =

* Fix: Recurring amount label doesn't properly factor in discounts from repeating or multi-month coupon codes.
* Fix: Sanitize custom amount plan IDs so they only contain alphanumeric and _- characters on creation (added in Stripe API 2018-02-05).
* Dev: System report: Update Stripe API endpoint for the TLS 1.2 compatibility check.
* Dev: Updated to Stripe PHP library v6.11.0.

= 3.2.0 - July 13, 2018 =

* Fix: Custom amount 1-cent rounding error in some cases.
* Fix: Adding metadata to invoices after sending causes PHP error in some cases.
* Fix: Displayed tax amount, total amount and recurring amount labels weren't accurate in some cases.
* Tweak: Display more than 100 subscription (product) plans when selecting within payment form settings.
* Dev: Updated to Stripe PHP library v6.10.2.
* Dev: Updated Accounting JS & Chosen JS libraries.

= 3.1.19 - May 15, 2018 =

* Tweak: PHP 5.4 or higher now required.
* Tweak: If the custom amount plan is selected in a user-selects plan form, focus input and blank out value.
* Dev: Updated to Stripe PHP library v6.7.1.

= 3.1.18 - April 2, 2018 =

* Fix: Detection and warning about upcoming PHP 5.4 requirement.
* Fix: Error when activating plugin with WP-CLI.
* Tweak: PHP 5.3 or higher now required.
* Tweak: Removed Bitcoin support inline with Stripe (https://stripe.com/blog/ending-bitcoin-support).

= 3.1.17 - March 20, 2018 =

* Fix: Integrate Stripe Plans with Stripe Products as introduced in Stripe API 2018-02-05.
* Tweak: Only check software license in admin max once per day.
* Dev: System report: Add mbstring (Multibyte String) check.

= 3.1.16 - February 22, 2018 =

* Feature: Add Romanian translation.
* Fix: "Start your free trial" checkout button text not appearing when a tax rate is in place.
* Fix: PHP error with software licensing updater in some cases.
* Fix: Dequeue all public CSS when "Default Plugin Styles" option is disabled.
* Dev: Updated to Stripe PHP library v5.9.2.

= 3.1.15 - January 23, 2018 =

* Fix: Detection and warning about PHP 5.3 requirement.
* Fix: Load custom sessions class in admin as it's still used for subscription plan setup fees.

= 3.1.14 - January 22, 2018 =

* Fix: Fix and simplify payment form previews.
* Fix: Translations weren't getting loaded properly (missing load_plugin_textdomain call).
* Dev: Don't load custom sessions class in admin.
* Dev: Updated to Stripe PHP library v5.9.0.

= 3.1.13 - January 3, 2018 =

* Fix: (Better) session handling to work across various hosts. Back to using the current version of WP Session Manager (https://github.com/ericmann/wp-session-manager) (2.0.2) with the option of switching to native PHP sessions.
* Fix: Force use of native PHP sessions when hosting with Pantheon.
* Fix: Custom fields (except for first) were losing metabox settings content.
* Dev: Updated to Stripe PHP library v5.8.0.
* Dev: Updated jQuery Validation & Chosen JS libraries.

= 3.1.12 - December 12, 2017 =

* Fix: Check for an existing session before starting a new one.

= 3.1.11 - December 12, 2017 =

* Fix: Session handling updated to work across various managed hosts. Now uses code from WP Native PHP Sessions (https://github.com/pantheon-systems/wp-native-php-sessions) over previously used WP Session Manager (https://github.com/ericmann/wp-session-manager).
* Fix: PHP 7.2 incompatibility - Misuse of reserved keyword "Object".
* Dev: Added filters to provide alternate custom field front-end HTML and admin UI.
* Dev: Updated action fired after a subscription is created to include the initial charge.
* Dev: Added action hook for adding metabox setting panel content.
* Dev: Added jQuery trigger to fire after front-end form validation is setup (in order to add custom validation rules).
* Dev: Updated to Stripe PHP library v5.7.0.

= 3.1.10 - November 20, 2017 =

* Fix: Recurring total amount miscalculation when tax in use.
* Fix: Ordering of plans when User Selects Plan option is set was not saving the order correctly in some cases.
* Fix: jQuery conflict where "jQuery" prefix was improperly used instead of "$".
* Fix: Payment receipt session error message produced by a shortcode was improperly appearing in WP admin.

= 3.1.9 - November 13, 2017 =

* Fix: Duplicate field rendering when custom amount field present and amount type toggled off.
* Fix: Tax metadata not saving when custom fields are present.
* Fix: Rounding issue with tax percent set to 4 decimals used with zero-decimal currencies.
* Fix: Coupon code now works with recurring toggle.
* Dev: Updated to Stripe PHP library v5.6.0.
* Dev: Updated to EDD SL Plugin Updater v1.6.15.

= 3.1.8 - October 31, 2017 =

* Fix: Add metadata for tax percent option.
* Fix: Recurring toggle now works properly.
* Fix: Default for total amount now defaults to minimum amount when using a custom amount input.
* UI: Stay on selected form settings tab after saving.
* UI: Updated grammar in error message.
* Dev: Added simpay_charge_error_message filter.
* Dev: Updated to Stripe PHP library v5.5.1.

= 3.1.7 - October 25, 2017 =

* Feature: Added option to set the payment success page (or redirect URL) per payment form.
* Fix: Super rare case where a certain amount value was off by one cent when using dropdowns as an amount field.
* Fix: Add fallback check for wp_doing_ajax() introduced in WP 4.7.
* Dev: Better handling of alternate Stripe API keys.
* Dev: Add better extensibility to webhook handling with some new and refined action hooks.
* Dev: Updated to Stripe PHP library v5.4.0.

= 3.1.6 - September 28, 2017 =

* Feature: Add support for zero amount and less than 50 currency unit subscription plans.
* Fix: Refresh license key check on license page to make it easier for upgraded accounts to see changes right away.
* Fix: Make sure automatic updates work on multi-site.
* Tweak: Add an error message if trying to activate Pro with Lite already installed.
* Dev: Overhaul to plugin code structure.
* Dev: Add filter simpay_payment_button_class to add or remove classes from the on-page form payment button.
* Dev: Add metadata to the charge_created and subscription_created action hooks.
* Dev: Updated to Stripe PHP library v5.2.3.

= 3.1.5 - September 8, 2017 =

* Fix: Metadata values for radio field + custom amount will show the label instead of "on".
* Fix: Prevent activation when WP Simple Pay Lite is active to avoid a fatal error.

= 3.1.4 - August 29, 2017 =

* Fix: Live mode keys will now load properly.
* Updated to Stripe PHP library v5.2.0.

= 3.1.3 - August 28, 2017 =

* Fix: Numeric only plan IDs will now work correctly.
* Tweak: JavaScript updates to improve performance.
* Dev: Update success page redirect filter to allow for external URLs.
* Dev: Make simpay_fee_amount filter also work as a form-specific filter.
* Dev: Make simpay_fee_percent filter also work as a form-specific filter.
* Dev: Add simpay_plan_name_label filter.
* Updated to Stripe PHP library v5.1.3.

= 3.1.2 - July 24, 2017 =

* Fix: Correct a JavaScript bug that was breaking forms.

= 3.1.1 - July 24, 2017 =

* Feature: Added setting to control Tax Rate Percent.
* Fix: Fix bug with invoices showing an initial $0.00 charge in some cases.
* Fix: Send Stripe API Version information with requests.
* Fix: Remove payment confirmation pages on full uninstall.
* Tweak: Automatic cache exclusion for payment confirmation pages.
* Dev: Add simpay_cache_exclusion_uris filter.
* Dev: Add per-form filter for new tax percent setting.

= 3.1.0 - July 13, 2017 =

* Feature: Installment plans added for subscriptions.
* Feature: Add a setting to control free trial button text.
* Fix: Remove support for Alipay since it is no longer supported through Stripe Checkout.
* Fix: Added plugin information to Stripe API calls.
* Tweak: Make recurring total label show the correct amount when multiplied by a quantity.
* UI: Minor tweaks to the multi-plan admin area.
* Dev: Updated to Stripe PHP library v5.1.1.

= 3.0.3 - June 29, 2017 =

* UI: Update field label description for checkbox custom field.
* UI: Add a placeholder setting for coupon fields.
* Fix: Make sure metadata gets added to the subscription if it has a trial period.
* Fix: Get the processing text setting to work correctly.
* Dev: Add 3 new action hooks.
* Dev: Updated to Stripe PHP library v5.0.0.

= 3.0.2 - June 21, 2017 =

* Fix: Make trial details template load correctly for multi-plans.

= 3.0.1 - June 21, 2017 =

* Fix: Bug with fee amount filter causing issues with zero-decimal currencies.
* Fix: Subscription custom amount field will properly take the default value.
* Fix: Custom amount default fields can now be left blank.
* Fix: Allow HTML in the custom field checkbox label.
* Fix: Checkout overlay will load properly now in IE.
* Dev: Updated to Stripe PHP library v4.13.0.

= 3.0.0 - June 13, 2017 =

* A brand spankin' new rewrite from the ground up. Too many updates to list here.

== Upgrade Notice ==

Changes to provide support for Strong Customer Authentication (SCA) results in changes to the purchase flow. Custom code snippets may need to be updated to work with updated Stripe objects and WP Simple Pay APIs.
