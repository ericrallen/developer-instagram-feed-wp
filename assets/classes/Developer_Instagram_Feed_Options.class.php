<?php

	if(!class_exists('ia_Developer_Instagram_Feed_Options')) {

		//options for our plug-in
		class ia_Developer_Instagram_Feed_Options {

			public $urls = array(
				'client_id' => 'http://instagram.com/developer/clients/manage/',
				'revoke' => 'https://instagram.com/accounts/manage_access'
			);

			//IMPORTANT: Update the version number here whenever you release a new version
			protected $v_num = '0.0.2';

			//prefix for option names, table names, and capability names
			protected $prefix = 'dev_instagram_feed_';

			//namespace for any Debug messages
			protected $namespace = 'DEV INSTAGRAM FEED';

			//initialize vars for plugin options
			protected $db;
			protected $options;
			protected $caps;

			//initialize options
			public function __construct() {
				//reference global $wpdb class instance
				global $wpdb;

				//store reference to $wpdb so we don't have to declare it constantly
				$this->db = $wpdb;

				$this->plugin_options();
				$this->plugin_capabilities();
			}

			//set up options array
			protected function plugin_options() {
				$this->options = array(
					$this->fix_name('version') => $this->v_num,
					$this->fix_name('options') => array(
						'client_id' => '',
						'client_secret' => '',
						'redirect_url' => get_admin_url() .  'profile.php',
						'number_of_photos' => 8,
						'instagram_contact_method' => 'iainstagram'
					)
				);
			}

			//set up capability array
			protected function plugin_capabilities() {
				$this->caps  = array(
					'activate_plugins' => array(
						$this->fix_name( 'settings')
					)
				);
			}

			//create a prefixed version of a table name or option name
			protected function fix_name($short_name = null, $db = false) {
				//see if short_name was provided
				if(isset($short_name)) {
					//if short_name doesn't start with _ and prefix doesn't end with _
					if(substr($this->prefix, -1, 1) != '_' && substr($short_name, 0, 1) != '_') {
						//add an _ between prefix and short_name
						$name = $this->prefix . '_' . $short_name;
					//if short_name starts with _ and prefix ends with _
					} elseif(substr($this->prefix, -1, 1) == '_' && substr($short_name, 0, 1) == '_') {
						//remove _ from short_name and prepend prefix
						$name = $this->prefix . substr($short_name, 0, 1);
					//if only one has an _
					} else {
						//concatenate the prefix and short_name
						$name = $this->prefix . $short_name;
					}

					//check if this is a table and needs the $wpdb->prefix added
					if($db) {
						$name = $this->db->prefix . $name;
					}

					//return the newly generated name
					return $name;
				}
			}

			//WP_DEBUG logging method
			protected function log($message, $namespace = null) {
				//if debugging is enabled
				if(WP_DEBUG) {
					//if we weren't given a namespace
					if(!is_string($namespace)) {
						//use the one defined in the class initialization
						$namespace = $this->namespace;
					//if we were
					} else {
						//convert it to caps so it's easily recognizable in the debug.log
						$namespace = strtoupper($namespace);
					}

					//append a colon and a space
					$namespace .= ': ';

					//if the message is an object or an array
					if(is_array($message) || is_object($message)) {
						//print out the object or array structure
						error_log($namespace . print_r($message, true));
					//if it isn't
					} else {
						//just echo out the message
						error_log($namespace . $message);
					}
				}
			}
		}
	}