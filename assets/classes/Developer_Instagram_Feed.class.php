<?php

	//class for our plug-in logic
	if(!class_exists('ia_Developer_Instagram_Feed') && class_exists('ia_Developer_Instagram_Feed_Options')) {

		class ia_Developer_Instagram_Feed extends ia_Developer_Instagram_Feed_Options {

			public function __construct() {
				//run Plugin_Name_Options constructor
				parent::__construct();

				//get options from DB and store them
				$this->get_settings();
			}

			//our plug-in activation
			public function activate() {
				//call methods to initialize plug-in functionality
				$this->set_options();
				$this->add_caps();
			}

			//our plug-in deactivation
			public function deactivate() {
				//call methods to remove capabilities
				//we don't remove the tables or options here, they are removed in uninstall.php
				$this->remove_caps();
			}

			//our plug-in uninstall
			public function uninstall() {
				//call methods to remove tables and unset options
				//other plugin data should have been removed on deactivation
				$this->unset_options();

				$this->remove_usermeta();

				$this->remove_transients();
			}

			//get current options
			public function get_settings() {
				//get options, use defaults from plugin-options.php if they aren't found
				$opts = get_option($this->fix_name('options'), $this->options[$this->fix_name('options')]);

				if(is_string($opts)) {
					//decode the JSON string into an array and save it to $this->settings
					$this->settings = json_decode($opts, true);
				} else {
					$this->settings = $opts;
				}
			}

			//admin menu set up
			public function admin_menu() {
				//settings page
				add_options_page(__('Developer Instagram Feed'), __('Developer Instagram Feed'), $this->caps['activate_plugins'][0], 'developer-instagram-feed.php', array($this, 'settings_page'));
			}

			public function profile_buttons($user) {
				//check authorization status
				$authorized = get_user_meta($user->ID, $this->fix_name('authorized'), true);

				if($authorized) {
					$instagram = new Instagram\Instagram;
					$instagram->setAccessToken($authorized);
					$auth_test = $instagram->getCurrentUser();

					$user_name = $auth_test->data->username;
				} else {
					$auth_test = -1;
					$user_name = '';
				}

				//add section to profile page
				$output = '<h3>Registration/Renewal Dates</h3>';
				$output .= '<table class="form-table">';
					$output .= '	<tr>';
					$output .= '		<th><label>Instagram API</label></th>';
					$output .= '		<td>';

						//check to see if we have received an authorization code
						if(isset($_GET['code']) || $authorized && $auth_test) {
							//if we just got one
							if(isset($_GET['code'])) {
								//set up authorization config
								$auth_config = array(
									'client_id' => $this->settings['client_id'],
									'client_secret' => $this->settings['client_secret'],
									'redirect_uri' => $this->settings['redirect_url'],
									'scope' => array('basic')
								);

								//initialize authorization
								$auth = new Instagram\Auth($auth_config);

								$value = $auth->getAccessToken( $_GET['code'] );

								//if we were previously authorized
								if($authorized) {
									//remove old access token
									delete_user_meta($user->ID, $this->fix_name('authorized'));
								}

								//save the code to the user's meta
								$authorized = add_user_meta($user->ID, $this->fix_name('authorized'), $value);
							//if we already had one
							} elseif($authorized) {
								$value = $authorized;
							}

							//display authorization code in disabled input
							$output .= '			Authorized<br /><span class="description"><a href="' . $this->urls['revoke'] . '">Revoke Access</a></span>';
						//if we haven't received one
						} else {
							//display check box allowing user to save profile and then authorize Instagram API
							$output .= '			<input type="checkbox" name="authorized" value="1" id="authorized" /> <label for="authorized">Authorize API Access</label>';

							if($auth_test !== -1) {
								$output .= '			<br /><span class="description">Your access token is no longer valid, please re-authorize this API integration.</span>';
							}
						}

					$output .= '		</td>';
					$output .= '	</tr>';

				$output .= '</table>';

				echo $output;
			}

			//save extra profile fields
			public function profile_auth($user_id) {
				//if the user wants to authorize
				if(isset($_REQUEST['authorized'])) {
					//set up authorization config
					$auth_config = array(
						'client_id' => $this->settings['client_id'],
						'client_secret' => $this->settings['client_secret'],
						'redirect_uri' => $this->settings['redirect_url'],
						'scope' => array('basic')
					);

					//initialize authorization
					$auth = new Instagram\Auth($auth_config);

					//authorize
					$auth->authorize();
				}
			}

			//get user images from API
			public function get_images($user_id, $count = null) {
				if(!$user_id) {
					global $post;
					$user_id = $post->post_author;
				}

				if(!$count || !is_integer($count)) {
					$count = (int) $this->settings['number_of_photos'];
				}

				$token = get_user_meta($user_id, $this->fix_name('authorized'), true);

				if($token) {
					$return_array = get_transient($this->fix_name('images_' . $user_id . '_' . $count));

					if(!$return_array) {
						$instagram = new Instagram\Instagram;
						$instagram->setAccessToken($token);
						$current_user = $instagram->getCurrentUser();
						$images = $current_user->getMedia(
							array(
								'count' => $count
							)
						);

						$return_array = $this->convert_array($images);

						if(count($return_array)) {
							$short_term = (int) $this->settings['short_term_cache'];

							delete_transient($this->fix_name('images_' . $user_id . '_' . $count));

							delete_transient($this->fix_name('images_' . $user_id . '_' . $count . '_long'));

							set_transient($this->fix_name('images_' . $user_id . '_' . $count), $return_array, $short_term * 60 * 60);

							set_transient($this->fix_name('images_' . $user_id . '_' . $count . '_long'), $return_array, 84600);
						} else {
							$return_array = get_transient($this->fix_name('images_' . $user_id . '_' . $count . '_long'));

							if(!$return_array) {
								$return_array = false;
							}
						}
					}

					return $return_array;
				} else {
					return false;
				}
			}

			//get images for hashtag
			public function get_hashtag($tag = null, $user_id = null, $count = null) {
				if(!$tag || !$user_id) {
					return false;
				} else {
					if(!$count || !is_integer($count)) {
						$count = (int) $this->settings['number_of_photos'];
					}

					$token = get_user_meta($user_id, $this->fix_name('authorized'), true);

					if($token) {
						$return_array = get_transient($this->fix_name('images_' . $tag . '_' . $count));

						if(!$return_array) {
							$instagram = new Instagram\Instagram;
							$instagram->setAccessToken($token);
							$hashtag = $instagram->getTag($tag);
							$images = $hashtag->getMedia(
								array(
									'count' => $count
								)
							);

							$return_array = $this->convert_array($images);

							if(count($return_array)) {
								$short_term = (int) $this->settings['short_term_cache'];

								delete_transient($this->fix_name('images_' . $tag . '_' . $count));

								delete_transient($this->fix_name('images_' . $tag . '_' . $count . '_long'));

								set_transient($this->fix_name('images_' . $tag . '_' . $count), $return_array, $short_term * 60 * 60);

								set_transient($this->fix_name('images_' . $tag . '_' . $count . '_long'), $return_array, 84600);
							} else {
								$return_array = get_transient($this->fix_name('images_' . $tag . '_' . $count . '_long'));

								if(!$return_array) {
									$return_array = false;
								}
							}
						}

						return $return_array;
					} else {
						return false;
					}
				}
			}

			//format returned image object
			private function convert_array($images = null) {
				$instagram_feed = array();

				if(isset($images)) {
					foreach($images as $key => $image_data) {
						$instagram_feed[$key] = array(
							'type' => $image_data->getType(),
							'location' => $image_data->getLocation(),
							'filter' => $image_data->getFilter(),
							'created_time' => $image_data->getCreatedTime(),
							'link' => $image_data->getLink(),
							'image' => array(
								'thumb' => $image_data->getThumbnail(),
								'low_resolution' => $image_data->getLowResImage(),
								'standard_resolution' => $image_data->getStandardResImage()
							),
							'video' => array(
								'low_resolution' => $image_data->getLowResVideo(),
								'standard_resolution' => $image_data->getStandardResVideo()
							)
						);

						$caption = $image_data->getCaption();

						$instagram_feed[$key]['caption'] = $caption->getText();
					}
				}

				return $instagram_feed;
			}

			//settings page
			public function settings_page() {
				if($this->settings['client_id'] && !isset($_REQUEST['reset_access'])) {
					$this->primary_settings_page();
				} else {
					$this->initial_settings_page();
				}
			}

			private function initial_settings_page() {
				//check to see if nonce and referer were sent
				if(isset($_REQUEST['_wpnonce']) && isset($_REQUEST['_wp_http_referer'])) {
					//check nonce validity and make sure there is actually a client_id
					if(wp_verify_nonce($_REQUEST['_wpnonce'], $this->fix_name('store_api_credentials')) && isset($_REQUEST['client_id']) && isset($_REQUEST['client_secret'])) {
						//save options
						$this->initial_settings_page_save();

						//display other settings
						$this->primary_settings_page_display();
					//if nonce is invalid or client id wasn't sent
					} else {
						//redisplay page with error
						$this->initiali_settings_page_display(true);
					}
				//if nonce and referrer were not sent
				} else {
					//display initial settings page
					$this->initial_settings_page_display();
				}
			}

			//api access form
			private function initial_settings_page_display($fail = false) {
				//build page
				$output = '<div class="wrap" id="' . $this->fix_name('settings') . '">';
					$output .= '	<h2>Instagram API</h2>';

					//check for error
					if($fail) {
						$output .= '	<div class="error"><p>' . __('Could not store') .  ' client_id</p></div>';
					}

					$output .= '	<p>' . __('Please') . ' <a href="' . $this->urls['client_id'] . '" target="_blank">' . __('create an Instagram API Client') . '</a> ' . __('using the following redirect URL') . ': <strong>' . $this->settings['redirect_url'] . '</strong></p>';
					$output .= '	<form method="post">';
						$output .= '		<label for="client_id">Client ID&nbsp;&nbsp;<input type="text" name="client_id" id="client_id" value="' . $this->settings['client_id'] . '" /></label><br />';
						$output .= '		<label for="client_secret">Client Secret&nbsp;&nbsp;<input type="text" name="client_secret" id="client_secret" value="' . $this->settings['client_secret'] . '" /></label><br />';
						$output .= '		' . wp_nonce_field($this->fix_name('store_api_credentials'), '_wpnonce', true, false);
						$output .= '		<input type="submit" name="submit" value="Save" />';
					$output .= '	</form>';
				$output .= '</div>';

				echo $output;
			}

			//store client id
			private function initial_settings_page_save() {
				$this->settings['client_id'] = $_REQUEST['client_id'];
				$this->settings['client_secret'] = $_REQUEST['client_secret'];

				//save new options
				$this->update_option($this->fix_name('options'), json_encode($this->settings));
			}

			//plugin settings
			private function primary_settings_page() {
				//check to see if nonce and referer were sent
				if(isset($_REQUEST['_wpnonce']) && isset($_REQUEST['_wp_http_referer'])) {
					//check nonce validity and make sure there is actually a client_id
					if(wp_verify_nonce($_REQUEST['_wpnonce'], $this->fix_name('save_settings'))) {
						//save options
						$this->primary_settings_page_save();

						//display settings page
						$this->primary_settings_page_display();
					//if nonce is invalid or client id wasn't sent
					} else {
						//redisplay page with error
						$this->primary_settings_page_display(true);
					}
				//if nonce and referrer were not sent
				} else {
					//display settings page
					$this->primary_settings_page_display();
				}
			}

			//plugin options form
			private function primary_settings_page_display($fail = false) {
				//build page
				$output = '<div class="wrap" id="' . $this->fix_name('settings') . '">';
					$output .= '	<h2>Instagram API</h2>';

					//check for error
					if($fail) {
						$output .= '	<div class="error"><p>' . __('Could not save options') . '</p></div>';
					}

					$output .= '	<form method="post">';
						$output .= '		<label for="number_of_photos">Number of Photos&nbsp;&nbsp;<input type="number" name="number_of_photos" id="number_of_photos" value="' . $this->settings['number_of_photos'] . '" /></label><br />';
						$output .= '		<label for="instagram_contact_method">Instagram Contact Method&nbsp;&nbsp;<input type="text" name="instagram_contact_method" id="instagram_contact_method" value="' . $this->settings['instagram_contact_method'] . '" /></label><br />';
						$output .= '		<label for="short_term_cache">Cache Results for &nbsp;&nbsp;<input type="text" name="short_term_cache" id="short_term_cache" value="' . $this->settings['short_term_cache'] . '" />&nbsp;&nbsp;Hours</label><br />';
						$output .= '		' . wp_nonce_field($this->fix_name('save_settings'), '_wpnonce', true, false);
						$output .= '		<input type="submit" name="submit" value="Save" />';
					$output .= '	</form>';
					$output .= '	<p><a href="' . get_admin_url() . 'options-general.php?page=developer-instagram-feed.php&reset_access=1">' . __('Reset my Instagram API Client ID') . '</a>.</p>';
				$output .= '</div>';

				echo $output;
			}

			//save plugin options
			private function primary_settings_page_save() {
				$this->settings['number_of_photos'] = (int) $_REQUEST['number_of_photos'];

				//save new options
				$this->update_option($this->fix_name('options'), json_encode($this->settings));
			}

			//add capabilities
			private function add_caps() {
				//get roles object
				global $wp_roles;

				//iterate through all roles and add the capabilities
				foreach($wp_roles->role_names as $role => $info) {
					//get the role
					$role_obj = get_role($role);

					//iterate through capabilities in the options
					//this gives us an array of capabilities and the capability they require
					foreach($this->caps as $req => $caps) {
						//iterate through our capabilities
						foreach($caps as $key => $cap) {
							//if this role has the required capability
							//but not the capability we want to add
							if(!$role_obj->has_cap($cap) && $role_obj->has_cap($req)) {
								//add capability
								$role_obj->add_cap($cap, true);
							}
						}
					}
				}
			}

			//remove capabilities
			private function remove_caps() {
				//get roles object
				global $wp_roles;

				//iterate through all roles and remove the capabilities
				foreach($wp_roles->roles as $role => $info) {
					//get the role
					$role_obj = get_role($role);

					//iterate through capabilities in the options
					//this gives us an array of capabilities and the capability they require
					foreach($this->caps as $req => $caps) {
						//iterate through our capabilities
						foreach($caps as $key => $cap) {
							//if this role has our capability
							if($role_obj->has_cap($cap)) {
								//remove the capability
								$role_obj->remove_cap($cap);
							}
						}
					}
				}
			}

			//this method sets any necessary options
			private function set_options() {
				//iterate through our options
				foreach($this->options as $name => $val) {
					//if this is our options array
					if($name == $this->fix_name('options')) {
						//iterate through each value
						foreach($val as $key => $value) {
							//check it against the current settings
							if(isset($this->settings[$key]) && $this->settings[$key] !== $value) {
								//if the setting was different, store the current setting, not our default
								$val[$key] = $this->settings[$key];
							}
						}

						//json encode our options array into a string
						$val = json_encode($val);
					}

					//run the option through our update method
					$this->update_option($name, $val);
				}
			}

			//this method removes any necessary options
			public function unset_options() {
				//iterate through our options
				foreach($this->options as $name => $val) {
					//remove the option
					delete_option($name);
				}
			}

			//remove plugin added user meta
			private function remove_usermeta() {
				$remove['contact_method'] = 'iainstagram';
				$remove['access_key'] = $this->fix_name('authorized');

				foreach($remove as $name => $val) {
					$query = "SELECT user_id FROM " . $this->db->usermeta . " WHERE meta_key = '" . $val . "';";

					$get_users = $this->db->get_results($query);

					if($get_users) {
						foreach($get_users as $user_id) {
							delete_user_meta($user_id, $val);
						}
					}
				}
			}

			private function remove_transients() {
				$transient_like = fix_name('images_');

				$query = "SELECT option_id FROM " . $this->db->options . " WHERE option_name LIKE '%" . $transient_link . "%';";

				$get_transients = $this->db->get_results($query);

				if($get_transients) {
					foreach($get_transients as $option_id) {
						$this->db->delete($this->db->options, array('option_id' => $option_id));
					}
				}
			}

			//this method allows us to run some checks when updating versions and changing options
			private function update_option($option, $value) {
				//if the option exists
				if($curr_value = get_option($option)) {
					//if the current value isn't what we want
					if($curr_value !== $value) {
						//check with the pre_update_option method which lets us perform any necessary actions when updating our options
						if($this->pre_update_option($option, $curr_value, $value)) {
							//update the option value
							update_option($option, $value);
						}
					}
				//if it doesn't add it
				} else {
					add_option($option, $value);
				}
			}

			//this method performs checks against specific option names to run update functions prior to saving the option
			private function pre_update_option($name, $old, $new) {
				//we'll make this true when the option is safe to update
				$good_to_go = false;

				//if this is our version number
				if($name === $this->options[$this->fix_name('version')]) {

					//IMPORTANT: call necessary update functions for each version here

					$good_to_go = true;

				//add other elseif branches based on other option updates that might require custom update functionality here

				//otherwise
				} else {
					//if we've got some values in there, we're good
					if($old || $new) {
						$good_to_go = true;
					}
				}

				return $good_to_go;
			}

		}

	}