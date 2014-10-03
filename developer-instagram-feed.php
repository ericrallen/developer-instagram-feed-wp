<?php
/*
Plugin Name: Developer Instagram Feed
Plugin URI: http://www.example.com/plug-in-name/
Description: Allows developers to easily connect to the Instagram API and provides functions for retrieving a user's photos. It only returns JSON so developers can style the feed as they wish.
Version: 0.0.3
Author: Eric Allen
Author URI: http://www.internetalche.me/
License: MIT
*/

	if(!defined('ABSPATH')) {
		exit();
	}

	// GLOBAL PATHS

	//this is the plug-in directory name
	if(!defined("DEVELOPER_INSTAGRAM_FEED")) {
		define("DEVELOPER_INSTAGRAM_FEED", trim(dirname(plugin_basename(__FILE__)), '/'));
	}

	//this is the path to the plug-in's directory
	if(!defined("DEVELOPER_INSTAGRAM_FEED_DIR")) {
		define("DEVELOPER_INSTAGRAM_FEED_DIR", WP_PLUGIN_DIR . '/' . DEVELOPER_INSTAGRAM_FEED);
	}

	//this is the url to the plug-in's directory
	if(!defined("DEVELOPER_INSTAGRAM_FEED_URL")) {
		define("DEVELOPER_INSTAGRAM_FEED_URL", WP_PLUGIN_URL . '/' . DEVELOPER_INSTAGRAM_FEED);
	}

	// OPTIONS
	include_once(DEVELOPER_INSTAGRAM_FEED_DIR . '/assets/classes/Developer_Instagram_Feed_Options.class.php');

	//LOGIC
	include_once(DEVELOPER_INSTAGRAM_FEED_DIR . '/assets/classes/Developer_Instagram_Feed.class.php');

	//Instagram API Wrapper
	include_once(DEVELOPER_INSTAGRAM_FEED_DIR . '/assets/classes/Instagram/Instagram.php');

	/* =======================================================
		Any classes you add should extend the Plugin_Options_Name
		class so that you have access to the prefixing and logging methods.
	======================================================= */

	if(class_exists('ia_Developer_Instagram_Feed')) {
		$developer_instagram_feed = new ia_Developer_Instagram_Feed();

		register_activation_hook(__FILE__, array($developer_instagram_feed, 'activate'));

		register_deactivation_hook(__FILE__, array($developer_instagram_feed, 'deactivate'));

		add_action('admin_menu', array($developer_instagram_feed, 'admin_menu'));

		add_action('show_user_profile', array($developer_instagram_feed, 'profile_buttons'));

		add_action('personal_options_update', array($developer_instagram_feed, 'profile_auth'));

		function DIF_get_user_images($user_id = null, $count = null) {
			$developer_instagram_feed = new ia_Developer_Instagram_Feed();

			$images = $developer_instagram_feed->get_images($user_id, $count);

			return $images;
		}

		function DIF_get_hashtag_images($hashtag = null, $authorized_user = null, $count = null) {
			$developer_instagram_feed = new ia_Developer_Instagram_Feed();

			$images = $developer_instagram_feed->get_hashtag($hashtag, $authorized_user, $count);

			return $images;
		}

	}