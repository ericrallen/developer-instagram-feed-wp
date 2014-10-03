developer-instagram-feed
================

REQUIRES PHP 5.3+

Coming soon to the WordPress Plugin Repository.

Allows developers to easily connect to the Instagram API and provides functions for retrieving a user's photos. It only returns an array so developers can style the feed as they wish.

Tired of opinionated plug-ins that do too much styling of content from the Instagram API? Tired of having to get user credentials to authorize the Instagram API for your clients? Tired of plugins that only allow one Instagram user's feed?

That's why we built this one. You set up an Instagram API Client for your WordPress installation and then your user's can authenticate the API Client when editing their user profile. All you need to do is send a user ID to a function and you can pull in that user's latest Instagrams.

Utilizes the [PHP-Instagram-API](https://github.com/galen/PHP-Instagram-API) by [galen](https://github.com/galen/)

Using the Plugin
==========

1.  Upload the `developer-instagram-feed` directory to your WordPress `/wp-content/plugins/` directory
2.  Go to the Settings->Developer Instagram Feed page and follow the instructions for setting up your Instagram API Client
3.  Set the number of images and the cache timeout for the plugin after storing your API credentials
4.  Have your users login and authorize your Instagram API Client via their WordPress Profile Page
5.  Use the `DIF_get_user_images($user_id)` function to retrieve a user's images from the Instagram API, or use the `DIF_get_hashtag_images($tag, $user_id)` function to retrieve images from the Instagram API based on a hashtag (you need to provide the WordPress ID of a user who has authorized with the API integration to pull in hashtagged images). Here's a [gist](https://gist.github.com/ericrallen/8393004) to help you get started.
