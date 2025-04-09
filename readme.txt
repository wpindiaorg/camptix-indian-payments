=== Camptix Indian Payments ===
Contributors:      wpindia, codexdemon, ravinderk, vachan, arvindbarskar, GautamMKGarg, knitpay
Tags:              camptix, camptix payment, event ticketing, razorpay, instamojo, Indian payment, camptix Indian gateway, camptix payment gateway
Requires at least: 3.5
Tested up to:      6.6
Stable tag:        1.9
License:           GPLv2 or later
License URI:       http://www.gnu.org/licenses/gpl-2.0.html

Simple and Flexible payment ticketing for Camptix using Multiple Indian Payment Gateway Solution

== Description ==

CampTix Multiple Indian Payment Gateway Solution for Camptix Plugin. Allow visitors to purchase tickets to your online or offline event using Camptix Multiple Indian payment gateway, directly from your WordPress or WordCamp website.

Camptix Multiple Indian Payment Gateway Solutions Accept payments in INR through Instamojo, Razorpay, Knit Pay Connect and more coming soon using the CampTix plugin. CampTix plugin needs to be installed and activated for the Camptix Multiple Indian Payment Gateway Solution to work. Knit Pay currently supports more than 10 Indian payment gateways.

Feel free to post your feature requests, issues and pull requests to [Camptix Indian Payments on GitHub](https://github.com/wpindiaorg/camptix-indian-payments "Camptix Indian Payments on GitHub").


== Installation ==

1. Please Install and Activate CampTix Plugin first in your site. Plugin URL (https://wordpress.org/plugins/camptix/)
2. Once you Activated CampTix, Please upload `camptix-indian-payments` to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to `Tickets -> Setup` in your WordPress admin area to set the currency to INR and activate the desire gateway.
5. Start selling ticket using Indian Payment Gateway!

== Frequently Asked Questions ==

= How to Sign Up? =
*Instamojo*: Signup at [Instamojo.com](https://www.instamojo.com/accounts/register/) and complete registration profile to get account activated.
*Razorpay*: Signup at [Razorpay.com](https://dashboard.razorpay.com/#/access/signup) and complete registration profile to get account activated.
*Knit Pay Connect*: Signup at Knit Pay is not required, just create an account in the desired payment gateway using their registration link. Knit Pay Connect supports integration with more than 15 Indian Payment Gateways.

= Is there any documentation available? =
*Instamojo*: Documentation available [here](https://docs.instamojo.com/v2/)
*Razorpay*: Documentation available [here](https://docs.razorpay.com/)
*Knit Pay Connect*: Install [Knit Pay](https://wordpress.org/plugins/knit-pay/) on your WordPress Instance, and connect that server with Camptix using [WordPress Rest API](https://developer.wordpress.org/rest-api/).

= How to get Access Token and API Key? =
*Instamojo*: Login > [API &amp; Plugins](https://www.instamojo.com/integrations) | Also Check [Youtube Video](https://www.youtube.com/watch?v=9j5RThz3FD0).
*Razorpay*: Dashboard > Test/Live Mode > Settings > [API Keys](https://dashboard.razorpay.com/#/app/keys).
*Knit Pay Connect*: Install [Knit Pay](https://wordpress.org/plugins/knit-pay/) Plugin on any Community Hosting, and get the [Application Password](https://make.wordpress.org/core/2020/11/05/application-passwords-integration-guide/)

== Screenshots ==

1. Instamojo Enable
2. Razorpay Enable
3. Knit Pay Connect Enable

== Changelog ==
=1.9=
* Added Support for Knit Pay Connect. Using which almost any Indian Payment Gateway can be integrated.

=1.8=
* FIX: Product Info title length - Github issue [#45](https://github.com/wpindiaorg/camptix-indian-payments/issues/45#issuecomment-392804508)

=1.7=
* FIX: Hardcoded attendee phone - Github issue [#43](https://github.com/wpindiaorg/camptix-indian-payments/issues/43), [#46](https://github.com/wpindiaorg/camptix-indian-payments/issues/46)
* FIX: Charachter length of 30 - Github issue [#45](https://github.com/wpindiaorg/camptix-indian-payments/issues/45)

=1.5=
JavaScript breaks registration flow for Instamojo #41 Fixed

=1.4=
Fixed Text domain and Language Translation Bug Fixed

=1.3 =
* Fixed Bug and Enhancement 

= 1.2 =
* Fixed Bug and Enhancement 

= 1.1 =
* Fixed Bug and Enhancement 

= 1.0 =
* First version
