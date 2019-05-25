=== Plugin Name ===
Contributors: Andre de Almeida, John Sabo
Donate link: www.darksources.com
Tags: comments, spam
Requires at least: 5.0.1
Tested up to: 5.2.1
Stable tag: 5.11
Requires PHP: 5.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Connect live and protect your users with the worlds largest stolen credential collection on planet. 

== Description ==

This plugin provides usable action based on the Dark Sources RESTful API service. The Dark Sources databases is the 
worlds largest full disclosure collection of recovered stolen data reaching over 20 billion lines of credential and
person information over 3 billion identified individuals globally. 


Action Triggers(note: any possible trigger will spawn alerts or actions):

*   Password exists for user in Dark Sources's hacker database

    This will trigger if the user's e-mail address and the submitted password is known to be stolen.

*   Known password usage is in the top X

    This will trigger if the passwords is ranked within the top X of known uses. A rank of 1 means it's the password
    most known to be used and stolen while a higher rank (currently 900m is top) will be more restrictive.

*   User has used the password at least X time(s) on other clean sites

    This will trigger if the user has been seen X times using the submitted password by our cloud but has not been 
    recovered as known to be stolen.

*   Password used the password at least X time(s) on other clean sites

    This will trigger if the password has been seen X times by our cloud with the last month but has not been
    recovered as known to be stolen.


Triggered Actions - On login:

*   Force password change

    Force the user to change their password 

*   Popup alert to user

    Alert the user upon login alerting to the risk with a custom generated popup message from our service or a
    configured custom text message. 

*   E-Mail alert to user

    Send the user an e-mail upon login alerting to the risk with a custom generated message from our service or
    a configured custom text message. 

* Send alert to admin

    Send an e-mail alert to the admin logging a positive triggers and actions taken.


Triggered Actions - On password change (Front of site and user profile menu):

*   Reject password and alert user

    Reject the submitted password and alert the user to the risk with a custom generated popup message from
    our service or a configured custom text message. 

*   E-Mail alert to user

    Send the user an e-mail upon password change alerting to the risk with a custom generated message from our service
    or a configured custom text message. 

* Send alert to admin

    Send an e-mail alert to the admin logging a positive triggers and actions taken.

== Installation ==

1. Navigate to the 'Plugins' menu in the WordPress Admin area.
2. Click the 'Add New' button in the top ''Plugins' section.
3. Upload dark-sources-password-scrubber zip file via the 'Upload Plugin' button.
4. Click the 'Activate' link to enable the plugin.
5. Navigate to the 'Dark Sources Settings' link in the left admin menu.
6. Choose the appropriate plan link and complete the signup process to get your API Key.
7. Enter your new API Key in the 'Dark Sources Settings' section under 'API Key'.
8. Once validated choose your appropriate settings. 

== Frequently Asked Questions ==

= Does this plugin require a service plan? =

This plugin is an extension of our risk manage RESTful API service. Good news we offer a completely free plan.

Once you install the plugin you will be presented with a plan selection slider. Select your plan and click the signup link to get started. 

= What happens if my free plan reaches it's monthly query limit? =

Once you run out of queries for the month the plugin will stop checking credentials and allow all login attempts.

= How do plan query overages work? = 

Once you've reached 90% of your monthly limit a preload of an additional amount will be attempted to ensure you never run out and have room to flex.

= Do you offer rate wholesale pricing or reductions higher query volumes? = 

The plan structure is based on a lower per query cost the higher the monthly commitment.


== Screenshots ==

1. Configuration menu 

== Changelog ==

= 1.0.5 =
* Initial public release

== Upgrade Notice ==

= 1.0.5 =
This version is the initial release canidate
