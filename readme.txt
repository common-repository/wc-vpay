=== VPay Payment Gateway for WooCommerce ===
Contributors: darakolawole, elabscode
Donate link: https://example.com/
Tags: vpay, woocommerce, payment gateway, Dare Kolawole plugins, verve, nigeria, naira, mastercard, visa
Requires at least: 4.7
Tested up to: 6.4
Stable tag: 1.2
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

VPay Payment Gateway for WooCommerce enables you receive instant and fast payments via bank transfer, USSD and card payment.

== Description ==

This is VPay payment gateway for WooCommerce.

VPay is a payment solution that enables cashiers and online shopping carts to increase customer loyalty by accepting payments via bank transfer and instantly confirm these payments without depending on the business owner or accountant.

VPay is a payment gateway for businesses to accept payments from customers, either on a recurring or one-time basis. VPay offers an easier, faster and cheaper way for businesses to get paid on their web and mobile applications using convenient payment methods for customers with the highest success rates obtainable.

With VPay Payment Gateway for WooCommerce, you can accept payments via:

* Credit/Debit Cards — Visa, Mastercard, Verve (NG)
* Bank transfer (Nigeria)
* USSD (Nigeria)
* Many more coming soon

= Why VPay? =

* Easy Reconciliation. VPay makes it simple for you to balance your books because we handle all the difficult tasks in real time, letting you know who paid for what and when it was paid.
* Start receiving payments instantly—go from sign-up to your first real transaction in as little as 10 minutes
* Simple, transparent pricing—no hidden charges or fees.
* Intelligent fraud detection
* Fully Featured Sandbox
* Understand your customers better through a simple and elegant dashboard
* Access to attentive, empathetic customer support 24/7
* Free updates as we launch new features and payment options
* Clearly documented APIs to build your custom payment experiences

Over 50,000 businesses of all sizes in Nigeria rely on VPay's suite of products to receive payments and make payouts seamlessly. Sign up on [vpay.africa/signup](https://vpay.africa/signup) to get started.


= Note =

This plugin is meant to be used by merchants in Nigeria.

= Plugin Features =

*   __Accept payment__ via Mastercard, Visa, Verve, USSD, Bank Transfer.
*   __Seamless integration__ into the WooCommerce checkout page. Accept payment directly on your site


= Suggestions / Feature Request =

If you have suggestions or a new feature request, feel free to get in touch with me via the contact form on my website [here](https://vpay.africa/contact)

You can also follow me on Twitter! **[@myvpayafrica](https://twitter.com/myvpayafrica)**


== Installation ==

*   Go to __WordPress Admin__ > __Plugins__ > __Add New__ from the left-hand menu
*   In the search box type __VPay Payment Gateway for WooCommerce__
*   Click on Install now when you see __VPay Payment Gateway for WooCommerce__ to install the plugin
*   After installation, __activate__ the plugin.


= VPay Setup and Configuration =
*   __Access WooCommerce Settings:__ Go to __WooCommerce > Settings__ and locate the __Payments__ tab.
*   __Select VPay:__ Find __VPay__ listed among your other payment methods and click on __Set Up__

*   __Plugin Configuration:__ Configure the VPay plugin on the next screen by following these steps:

1. __Enable/Disable__ - Check this checkbox to Enable VPay on your store's checkout.
2. __Webhook Authentication Token Expiry Check__ - Check this checkbox to enable Webhook Authentication Token Expiry Check.
      __NOTE:__ Copy Webhook URL provided and paste it into the Webhook input field on your VPay dashboard under __Settings > Webhooks__ tab. Save the changes.
3. __Title__ - Enter a caption for what users will see during checkout.
4. __Description__ - Provide a message that appears under the payment fields on the checkout page, explaining VPay and the available payment methods.
5. __Test Mode__ - Check this box to enable test mode. This allows you to test payments using SANDBOX details before going live. Uncheck it when your store is ready to accept real payments.
6. __Transaction_Charge__ - Specify the amount to be charged to the paying customer in addition to the order amount. This amount must be greater than 0 and shares the currency with the order amount.
7. __Transaction_Charge_Type__ - Either flat or percentage. This indicates how the payment total should be computed using the order amount and txn_charge above. E.g. order amount = 1000, txn_charge = 5, txn_charge_type = “percentage” then total = 1000 + (1000 * 5%) = 1000 + 50 = 1050.
8. __API & Secret Keys__ - Enter your VPay API key & Secret key, which can be obtained from your VPay Dashboard. If Test Mode is enabled, use your SANDBOX API & Secret keys.

* __Save Changes:__ Click on __Save Changes__ to update the settings.

= Notes =
 We __strongly__ recommend that you copy the generated Webhook URL on the Woocommerce Settings page and paste it into the Webhook input field on your VPay dashboard under __Settings > Webhooks__ tab. This way, whenever a transaction is complete on your store, we'll send a notification to the Webhook URL, which will update the order and mark it as paid. Just copy the URL and save it as your webhook URL on your VPay dashboard under __Settings > Webhooks__ tab.

If you do not find VPay on the Payment method options, please go through the settings again and ensure that:

*   You've checked the __"Enable/Disable"__ checkbox
*   You've entered your __API Keys__ in the appropriate field
*   You've clicked on __Save Changes__ during setup

== Frequently Asked Questions ==

= What Do I Need To Use The Plugin =

*   A VPay merchant account —- use an existing account or [create an account here](https://vpay.africa/signup)
*   An active [WooCommerce installation](https://docs.woocommerce.com/document/installing-uninstalling-woocommerce/)
*   A valid [SSL Certificate](https://docs.woocommerce.com/document/ssl-and-https/)

== Screenshots ==

1. To make payment through Bank transfer, choose the bank you are paying from and enter the last 4 digits of the account number you are paying from.
2. To make payment through USSD, dial the USSD code and confirm payment.
3. Check all payment details before confirming payment.
4. Feedback confirming that payment was successful.

== Changelog ==

= 1.2 =
* WordPress-Generated Webhook URL
* Webhook Authentication Checkbox
* Live Secret Key Option
* Bug Fixes and Modifications
* Added Extra Layer of Security

= 1.1 =
* Bug fixes and Modifications

= 1.0 =
* A change since the previous version.
* Another change.

= 0.5 =
* List versions from most recent at top to oldest at bottom.

== Upgrade Notice ==

= 1.0 =
Upgrade notices describe the reason a user should upgrade.  No more than 300 characters.

= 0.5 =
This version fixes a security related bug.  Upgrade immediately.
