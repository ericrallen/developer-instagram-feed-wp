=== Developer Instagram Feed ===
Contributors: ericrallen
Tags: instagram, developer
Requires at least: 3.6.1
Tested up to: 4.0.0
Stable tag: 0.0.5
License: MIT
License URI: http://opensource.org/licenses/mit-license.php

Allows developers to easily connect to the Instagram API and provides functions for retrieving a user's photos. It returns an associative array so that developers can style the feed as they wish. REQUIRES PHP 5.3+

== Description ==

Allows developers to easily connect to the Instagram API and provides functions for retrieving a user's photos. It only returns an array so developers can style the feed as they wish.

Tired of opinionated plug-ins that do too much styling of content from the Instagram API? Tired of having to get user credentials to authorize the Instagram API for your clients? Tired of plugins that only allow one Instagram user's feed?

That's why we built this one. You set up an Instagram API Client for your WordPress installation and then your user's can authenticate the API Client when editing their user profile. All you need to do is send a user ID to a function and you can pull in that user's latest Instagrams.

== Options ==

On the plugin's settings page you can set how many images the user's Instagram feed pulls in and how many hours the feed should be cached for. There is a backup transient cache that lasts 24 hours that will pull in if the cache is not available and the Instagram API is unavailable.

You can pass a user ID into the `DIF_get_user_images()` function, or you can let it pull in the current $post object's author ID.

This plugin also adds an Instagram contact method to user profiles. If your site already has an Instagram contact method, you can change the value in the plugin's settings page to the name of the contact method field that already exits and this plugin will not add another Instagram contact method.

== Screenshots ==

1. The plugin Client ID registration page
2. The plugin's settings page
3. The user profile after authentication

== Installation ==

1. Upload `developer-instagram-feed` to your plug-in directory or install it from the Wordpress Plug-in Repository
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the Developer Instagram Feed page under the Settings menu.
4. Follow the instructions on that page to set up your API Client.
5. Instruct your users to check the "Authorize Instagram API Access" checkbox on the Edit Your Profile page and then click Update.
6. Your user is redirected to Instagram to grant access to your API Client
7. The user is then redirected back to their profile page and the API Client is authorized
8. Use the `DIF_get_user_images()` function to retrieve a user's Instagram posts

== Frequently Asked Questions ==

This plugin has not yet been tested with a multi-site installation.

== Changelog ==

= 0.0.3 =
* Updated some logic and added better readme and screenshots.

= 0.0.2 =
* Updated `DIF_get_user_images()` function to format Instagram informaiton in a more easily accessible format.

= 0.0.1 =
* Initial set up. Getting it ready for the WP Plug-in repo and github duality.

== Support ==

Feel free to fill out a [GitHub Issue](https://github.com/ericrallen/developer-instagram-feed-wp/issues) or post on the support forums.