<?php

namespace wams\core;

// Exit if accessed directly
if (!defined('ABSPATH')) exit;


if (!class_exists('wams\core\Roles_Capabilities')) {


	/**
	 * Class Roles_Capabilities
	 * @package wams\core
	 */
	class Roles_Capabilities
	{


		/**
		 * Roles_Capabilities constructor.
		 */
		function __construct()
		{
			add_action('wp_roles_init', array(&$this, 'wams_roles_init'), 99999);
			add_action('update_option', array(&$this, 'wams_on_roles_update'), 10, 3);
			add_action('set_user_role', array(&$this, 'remove_user_cache'), 10, 1);
		}


		/**
		 * Flush the Cache User Profile on set new user role(s)
		 *
		 * @param int $user_id
		 */
		function remove_user_cache($user_id)
		{
			$user = get_userdata($user_id);

			if (!is_a($user, '\WP_User')) {
				return;
			}

			WAMS()->user()->remove_cache($user_id);
		}


		/**
		 * @param string $option
		 * @param mixed $old_value
		 * @param mixed $value
		 */
		function wams_on_roles_update($option, $old_value, $value)
		{
			global $wp_roles;

			if (is_object($wp_roles) && isset($wp_roles->role_key) && $option == $wp_roles->role_key) {
				foreach ($value as $role_key => $role_data) {
					$role_keys = get_option('wams_roles', array());
					$role_keys = array_map(function ($item) {
						return 'wams_' . $item;
					}, $role_keys);

					if (!empty($role_keys) && in_array($role_key, $role_keys)) {
						$role_meta = get_option('wams_role_' . substr($role_key, 3) . '_meta');

						if (!isset($role_meta['wp_capabilities'])) {
							$role_meta['wp_capabilities'] = array();
						}

						if (!empty($role_data['capabilities']) && is_array($role_data['capabilities'])) {
							$old_role_caps = !empty($old_value[$role_key]['capabilities']) ? array_keys($old_value[$role_key]['capabilities']) : array();

							if (!empty($old_role_caps)) {
								$unset_caps = array_diff($old_role_caps, array_keys($role_data['capabilities']));

								if (!empty($unset_caps)) {
									foreach ($unset_caps as $cap) {
										if (!empty($role_meta['wp_capabilities'][$cap])) {
											unset($role_meta['wp_capabilities'][$cap]);
										}
									}
								}
							}

							foreach ($role_data['capabilities'] as $cap => $grant) {
								if ($grant) {
									$role_meta['wp_capabilities'][$cap] = true;
								}
							}
						}

						update_option('wams_role_' . substr($role_key, 3) . '_meta', $role_meta);
					}
				}
			}
		}

		/**
		 * Loop through dynamic roles and add them to the $wp_roles array
		 *
		 * @param null|object $wp_roles
		 * @return null
		 */
		public function wams_roles_init($wp_roles = null)
		{
			$role_keys = get_option('wams_roles', array());
			$wams_roles  = array_map(array(&$this, 'key_to_role_id_mapping'), $role_keys);

			// Add WAMS role data to WP Roles.
			foreach ($wp_roles->roles as $role_id => $role_data) {
				// Skip custom WAMS roles meta here, because it's added below. See: "Add custom WAMS roles".
				if (in_array($role_id, $wams_roles, true)) {
					continue;
				}

				$role_meta = get_option("wams_role_{$role_id}_meta");
				if (!empty($role_meta)) {
					$wp_roles->roles[$role_id] = array_merge($role_data, $role_meta);
				}
			}

			// Add custom WAMS roles.
			$roles = array();
			foreach ($role_keys as $role_key) {
				$role_meta = get_option("wams_role_{$role_key}_meta");
				if (!empty($role_meta)) {
					$roles['wams_' . $role_key] = $role_meta;
				}
			}

			if (empty($roles)) {
				return $wp_roles;
			}

			foreach ($roles as $role_id => $details) {
				$capabilities            = !empty($details['wp_capabilities']) ? array_keys($details['wp_capabilities']) : array();
				$details['capabilities'] = array_fill_keys(array_values($capabilities), true);
				unset($details['wp_capabilities']);

				$wp_roles->roles[$role_id]        = $details;
				$wp_roles->role_objects[$role_id] = new \WP_Role($role_id, $details['capabilities']);
				$wp_roles->role_names[$role_id]   = $details['name'];
			}

			// Return the modified $wp_roles array
			return $wp_roles;
		}

		public function key_to_role_id_mapping($role_key)
		{
			return 'wams_' . $role_key;
		}

		/**
		 * Check if role is custom
		 *
		 * @param $role
		 * @return bool
		 */
		public function is_role_custom($role)
		{
			// User has roles so look for a WAMS Role one
			$role_keys = get_option('wams_roles', array());
			if (empty($role_keys)) {
				return false;
			}

			$wams_roles = array_map(array(&$this, 'key_to_role_id_mapping'), $role_keys);
			return in_array($role, $wams_roles, true);
		}

		/**
		 * Return a user's main role
		 *
		 * @param int $user_id
		 * @param string $new_role
		 * @uses get_userdata() To get the user data
		 * @uses apply_filters() Calls 'wams_set_user_role' with the role and user id
		 * @return string
		 */
		function set_role($user_id, $new_role = '')
		{
			// Validate user id
			$user = get_userdata($user_id);

			// User exists
			if (!empty($user)) {
				// Get users old WAMS role
				$role = WAMS()->roles()->get_wams_user_role($user_id);

				// User already has this role so no new role is set
				if ($new_role === $role || (!$this->is_role_custom($new_role) && user_can($user, $new_role))) {
					$new_role = false;
				} else {
					// Users role is different than the new role

					// Remove the old WAMS role
					if (!empty($role) && $this->is_role_custom($role)) {
						$user->remove_role($role);
					}

					// Add the new role
					if (!empty($new_role)) {
						$user->add_role($new_role);
					}

					/**
					 * WAMS hook
					 *
					 * @type action
					 * @title wams_when_role_is_set
					 * @description Action before user role changed
					 * @input_vars
					 * [{"var":"$user_id","type":"int","desc":"User ID"}]
					 * @change_log
					 * ["Since: 2.0"]
					 * @usage add_action( 'wams_when_role_is_set', 'function_name', 10, 1 );
					 * @example
					 * <?php
					 * add_action( 'wams_when_role_is_set', 'my_when_role_is_set', 10, 1 );
					 * function my_when_role_is_set( $user_id ) {
					 *     // your code here
					 * }
					 * ?>
					 */
					do_action('wams_when_role_is_set', $user_id);
					/**
					 * WAMS hook
					 *
					 * @type action
					 * @title wams_before_user_role_is_changed
					 * @description Action before user role changed
					 * @change_log
					 * ["Since: 2.0"]
					 * @usage add_action( 'wams_before_user_role_is_changed', 'function_name', 10 );
					 * @example
					 * <?php
					 * add_action( 'wams_before_user_role_is_changed', 'my_before_user_role_is_changed', 10 );
					 * function my_before_user_role_is_changed() {
					 *     // your code here
					 * }
					 * ?>
					 */
					do_action('wams_before_user_role_is_changed');

					WAMS()->user()->profile['role'] = $new_role;

					/**
					 * WAMS hook
					 *
					 * @type action
					 * @title wams_member_role_upgrade
					 * @description Action on user role changed
					 * @input_vars
					 * [{"var":"$user_id","type":"int","desc":"User ID"},
					 * {"var":"$role","type":"string","desc":"User role"}]
					 * @change_log
					 * ["Since: 2.0"]
					 * @usage add_action( 'wams_member_role_upgrade', 'function_name', 10, 2 );
					 * @example
					 * <?php
					 * add_action( 'wams_member_role_upgrade', 'my_member_role_upgrade', 10, 2 );
					 * function my_member_role_upgrade( $old_role, $new_role ) {
					 *     // your code here
					 * }
					 * ?>
					 */
					do_action('wams_member_role_upgrade', $role, WAMS()->user()->profile['role']);

					WAMS()->user()->update_usermeta_info('role');
					/**
					 * WAMS hook
					 *
					 * @type action
					 * @title wams_after_user_role_is_changed
					 * @description Action after user role changed
					 * @change_log
					 * ["Since: 2.0"]
					 * @usage add_action( 'wams_after_user_role_is_changed', 'function_name', 10 );
					 * @example
					 * <?php
					 * add_action( 'wams_after_user_role_is_changed', 'my_after_user_role_is_changed', 10 );
					 * function my_after_user_role_is_changed() {
					 *     // your code here
					 * }
					 * ?>
					 */
					do_action('wams_after_user_role_is_changed');
					/**
					 * WAMS hook
					 *
					 * @type action
					 * @title wams_after_user_role_is_updated
					 * @description Action after user role changed
					 * @input_vars
					 * [{"var":"$user_id","type":"int","desc":"User ID"},
					 * {"var":"$role","type":"string","desc":"User role"}]
					 * @change_log
					 * ["Since: 2.0"]
					 * @usage add_action( 'wams_after_user_role_is_updated', 'function_name', 10, 2 );
					 * @example
					 * <?php
					 * add_action( 'wams_after_user_role_is_updated', 'my_after_user_role_is_updated', 10, 2 );
					 * function my_after_user_role_is_updated( $user_id, $role ) {
					 *     // your code here
					 * }
					 * ?>
					 */
					do_action('wams_after_user_role_is_updated', $user_id, $role);
				}
			} else {
				// User does don exist so return false
				$new_role = false;
			}

			/**
			 * WAMS hook
			 *
			 * @type filter
			 * @title wams_set_user_role
			 * @description User role was changed
			 * @input_vars
			 * [{"var":"$new_role","type":"string","desc":"New role"},
			 * {"var":"$user_id","type":"int","desc":"User ID"},
			 * {"var":"$user","type":"array","desc":"Userdata"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage
			 * <?php add_filter( 'wams_set_user_role', 'function_name', 10, 1 ); ?>
			 * @example
			 * <?php
			 * add_filter( 'wams_set_user_role', 'my_set_user_role', 10, 1 );
			 * function my_set_user_role( $new_role ) {
			 *     // your code here
			 *     return $new_role;
			 * }
			 * ?>
			 */
			return apply_filters('wams_set_user_role', $new_role, $user_id, $user);
		}


		/**
		 * Remove user role
		 *
		 * @param $user_id
		 * @param $role
		 */
		function remove_role($user_id, $role)
		{
			// Validate user id
			$user = get_userdata($user_id);

			// User exists
			if (!empty($user)) {
				// Remove role
				$user->remove_role($role);
			}
		}


		/**
		 * Remove user role
		 *
		 * @param $user_id
		 * @param $role
		 */
		function set_role_wp($user_id, $role)
		{
			// Validate user id
			$user = get_userdata($user_id);

			// User exists
			if (!empty($user)) {
				// Remove role
				$user->add_role($role);
			}
		}


		/**
		 * Get user one of WAMS roles if it has it
		 *
		 * @deprecated since 2.0
		 * @param int $user_id
		 * @return bool|mixed
		 */
		function wams_get_user_role($user_id)
		{
			return $this->get_wams_user_role($user_id);
		}


		/**
		 * @param $user_id
		 *
		 * @return array|bool
		 */
		function get_all_user_roles($user_id)
		{
			$user = get_userdata($user_id);

			if (empty($user->roles)) {
				return false;
			}

			return array_values($user->roles);
		}


		/**
		 * @param $user_id
		 *
		 * @return bool|mixed
		 */
		function get_priority_user_role($user_id)
		{
			$user = get_userdata($user_id);

			if (empty($user->roles)) {
				return false;
			}

			// User has roles so look for a WAMS Role one
			$wams_roles_keys = get_option('wams_roles', array());

			if (!empty($wams_roles_keys)) {
				$wams_roles_keys = array_map(
					function ($item) {
						return 'wams_' . $item;
					},
					$wams_roles_keys
				);
			}

			$orders = array();
			foreach (array_values($user->roles) as $userrole) {
				if (!empty($wams_roles_keys) && in_array($userrole, $wams_roles_keys, true)) {
					$userrole_metakey = substr($userrole, 3);
				} else {
					$userrole_metakey = $userrole;
				}

				$rolemeta = get_option("wams_role_{$userrole_metakey}_meta", false);

				if (!$rolemeta) {
					$orders[$userrole] = 0;
					continue;
				}

				$orders[$userrole] = !empty($rolemeta['_wams_priority']) ? $rolemeta['_wams_priority'] : 0;
			}

			arsort($orders);
			$roles_in_priority = array_keys($orders);

			return array_shift($roles_in_priority);
		}


		/**
		 * Get editable WAMS user roles
		 *
		 * @return array
		 */
		function get_editable_user_roles()
		{
			$editable_roles = array('subscriber');

			// User has roles so look for a WAMS Role one
			$wams_roles_keys = get_option('wams_roles', array());
			if (!empty($wams_roles_keys) && is_array($wams_roles_keys)) {
				$wams_roles_keys = array_map(function ($item) {
					return 'wams_' . $item;
				}, $wams_roles_keys);

				$editable_roles = array_merge($editable_roles, $wams_roles_keys);
			}

			/**
			 * WAMS hook
			 *
			 * @type filter
			 * @title wams_extend_editable_roles
			 * @description Extend Editable User Roles
			 * @input_vars
			 * [{"var":"$editable_roles","type":"array","desc":"Editable Roles Keys"}]
			 * @change_log
			 * ["Since: 2.6.0"]
			 * @usage add_filter( 'wams_extend_editable_roles', 'function_name', 10, 1 );
			 * @example
			 * <?php
			 * add_filter( 'wams_extend_editable_roles', 'my_wams_extend_editable_roles', 10, 1 );
			 * function my_wams_extend_editable_roles( $editable_roles ) {
			 *     // your code here
			 *     return $editable_roles;
			 * }
			 * ?>
			 */
			$editable_roles = apply_filters('wams_extend_editable_roles', $editable_roles);
			return $editable_roles;
		}


		/**
		 * @param $user_id
		 *
		 * @return bool|mixed
		 */
		function get_editable_priority_user_role($user_id)
		{
			$user = get_userdata($user_id);

			if (empty($user->roles))
				return false;

			// User has roles so look for a WAMS Role one
			$wams_roles_keys = get_option('wams_roles', array());

			if (!empty($wams_roles_keys)) {
				$wams_roles_keys = array_map(function ($item) {
					return 'wams_' . $item;
				}, $wams_roles_keys);
			}

			$orders = array();
			foreach (array_values($user->roles) as $userrole) {
				if (!empty($wams_roles_keys) && in_array($userrole, $wams_roles_keys)) {
					$userrole_metakey = substr($userrole, 3);
				} else {
					$userrole_metakey = $userrole;
				}

				$rolemeta = get_option("wams_role_{$userrole_metakey}_meta", false);

				if (!$rolemeta) {
					$orders[$userrole] = 0;
					continue;
				}

				$orders[$userrole] = !empty($rolemeta['_wams_priority']) ? $rolemeta['_wams_priority'] : 0;
			}

			arsort($orders);
			$roles_in_priority = array_keys($orders);
			$roles_in_priority = array_intersect($roles_in_priority, $this->get_editable_user_roles());

			return array_shift($roles_in_priority);
		}


		/**
		 * @param $user_id
		 *
		 * @return bool|mixed
		 */
		function get_wams_user_role($user_id)
		{
			// User has roles so look for a WAMS Role one
			$wams_roles_keys = get_option('wams_roles', array());

			if (empty($wams_roles_keys)) {
				return false;
			}

			$user = get_userdata($user_id);

			if (empty($user->roles)) {
				return false;
			}

			$wams_roles_keys = array_map(function ($item) {
				return 'wams_' . $item;
			}, $wams_roles_keys);

			$user_wams_roles_array = array_intersect($wams_roles_keys, array_values($user->roles));

			if (empty($user_wams_roles_array)) {
				return false;
			}

			return array_shift($user_wams_roles_array);
		}


		/**
		 * Get role name by roleID
		 *
		 * @param $slug
		 * @return bool|string
		 */
		function get_role_name($slug)
		{
			$roledata = $this->role_data($slug);

			if (empty($roledata['name'])) {
				global $wp_roles;

				if (empty($wp_roles->roles[$slug]))
					return false;
				else
					return $wp_roles->roles[$slug]['name'];
			}


			return $roledata['name'];
		}


		/**
		 * Get role data.
		 *
		 * @param int $role_id Role ID.
		 *
		 * @return array
		 */
		public function role_data($role_id)
		{
			if (empty($role_id)) {
				return array();
			}

			if (strpos($role_id, 'wams_') === 0) {
				$role_id   = substr($role_id, 3);
				$role_data = get_option("wams_role_{$role_id}_meta", array());
			}

			if (empty($role_data)) {
				$role_data = get_option("wams_role_{$role_id}_meta", array());
			}

			if (!$role_data) {
				return array();
			}

			$temp = array();
			foreach ($role_data as $key => $value) {
				if (strpos($key, '_wams_') === 0) {
					$key = preg_replace('/_wams_/', '', $key, 1);
				}
				$temp[$key] = $value;
			}
			/**
			 * Filters the WAMS related user role data.
			 *
			 * @since 2.0
			 * @hook  wams_change_role_data
			 *
			 * @param {array}  $role_data Role data.
			 * @param {string} $role_id   Role ID.
			 *
			 * @return {array} Role data.
			 *
			 * @example <caption>Set {some_capability_key} capability for subscriber user role.</caption>
			 * function my_change_role_data( $role_data, $role_id ) {
			 *     // your code here
			 *     if ( 'subscriber' === $role_id ) {
			 *          $role_data['{some_capability_key}'] = true;
			 *     }
			 *     return $role_data;
			 * }
			 * add_filter( 'wams_change_role_data', 'my_change_role_data', 10, 2 );
			 */
			return apply_filters('wams_change_role_data', $temp, $role_id);
		}

		/**
		 * Query for WAMS roles
		 *
		 * @param bool $add_default
		 * @param null $exclude
		 *
		 * @return array
		 */
		public function get_roles($add_default = false, $exclude = null)
		{
			global $wp_roles;

			if (empty($wp_roles)) {
				return array();
			}

			$roles = $wp_roles->role_names;

			if ($add_default) {
				$roles[0] = $add_default;
			}

			if ($exclude) {
				foreach ($exclude as $role) {
					unset($roles[$role]);
				}
			}

			$roles = array_map(
				function ($role) {
					if (is_string($role)) {
						return stripslashes($role);
					}
					return $role;
				},
				$roles
			);

			return $roles;
		}


		/**
		 * Current user can
		 *
		 * @param $cap
		 * @param $user_id
		 *
		 * @return bool|int
		 */
		function wams_current_user_can($cap, $user_id)
		{
			if (!is_user_logged_in()) {
				return false;
			}

			$return = 1;

			wams_fetch_user(get_current_user_id());

			$current_user_roles = $this->get_all_user_roles($user_id);

			switch ($cap) {
				case 'edit':

					if (get_current_user_id() == $user_id) {
						if (wams_user('can_edit_profile')) {
							$return = 1;
						} else {
							$return = 0;
						}
					} else {

						if (!wams_user('can_access_private_profile') && WAMS()->user()->is_private_profile($user_id)) {
							$return = 0;
						} else {
							if (!wams_user('can_edit_everyone')) {
								$return = 0;
							} else {
								if (wams_user('can_edit_roles') && (empty($current_user_roles) || count(array_intersect($current_user_roles, wams_user('can_edit_roles'))) <= 0)) {
									$return = 0;
								} else {
									$return = 1;
								}
							}
						}
					}

					break;

				case 'delete':
					if (!wams_user('can_delete_everyone'))
						$return = 0;
					elseif (wams_user('can_delete_roles') && (empty($current_user_roles) || count(array_intersect($current_user_roles, wams_user('can_delete_roles'))) <= 0))
						$return = 0;
					break;
			}

			wams_fetch_user($user_id);

			return $return;
		}

		/**
		 * User can (role settings)
		 *
		 * @param $permission
		 * @return bool|mixed
		 */
		public function wams_user_can($permission)
		{
			if (!is_user_logged_in()) {
				return false;
			}

			$user_id = get_current_user_id();
			$role    = WAMS()->roles()->get_priority_user_role($user_id);

			$permissions = $this->role_data($role);
			/**
			 * Filters User Permissions.
			 *
			 * @param {array} $permissions User Permissions.
			 * @param {int}   $user_id     User ID.
			 *
			 * @return {array} User Permissions.
			 *
			 * @since 2.0
			 * @hook wams_user_permissions_filter
			 *
			 * @example <caption>Add custom user permissions.</caption>
			 * function my_user_permissions( $permissions, $user_id ) {
			 *     // your code here
			 *     return $permissions;
			 * }
			 * add_filter( 'wams_user_permissions_filter', 'my_user_permissions', 10, 2 );
			 */
			$permissions = apply_filters('wams_user_permissions_filter', $permissions, $user_id);

			if (isset($permissions[$permission]) && is_serialized($permissions[$permission])) {
				return maybe_unserialize($permissions[$permission]);
			}

			if (isset($permissions[$permission]) && is_array($permissions[$permission])) {
				return $permissions[$permission];
			}

			if (isset($permissions[$permission]) && $permissions[$permission] == 1) {
				return true;
			}

			return false;
		}
	}
}
