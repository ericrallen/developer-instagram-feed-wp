<?php

	//uninstallation
	if(!defined('WP_UNINSTALL_PLUGIN')) {
		exit();
	} else {
		if(!class_exists('ia_Developer_Instagram_Feed_Options')) {
			include_once(dirname(__FILE__) . '/assets/classes/Developer_Instagram_Feed_Options.class.php');
		}

		if(!class_exists('ia_Developer_Instagram_Feed')) {
			include_once(dirname(__FILE__) . '/assets/classes/Developer_Instagram_Feed.class.php');
		}

		$instagram_developer_feed = new ia_Developer_Instagram_Feed();

		$instagram_developer_feed->uninstall();
	}