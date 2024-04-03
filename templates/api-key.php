<table class="form-table">
    <tbody>
        <tr>
            <th>
                <?php _e('GiveWP API Keys', 'give'); ?>
            </th>
            <td>
                <?php
                $public_key = $this->get_user_public_key($user->ID);
                $secret_key = $this->get_user_secret_key($user->ID);
                ?>
                <?php if (empty($user->wams_user_public_key)) { ?>
                    <input name="wams_set_api_key" type="checkbox" id="wams_set_api_key" />
                    <span class="description"><label for="wams_set_api_key"><?php _e('Generate API Key', 'give'); ?></label></span>
                <?php } else { ?>
                    <strong style="display:inline-block; width: 125px;"><?php _e('Public key:', 'give'); ?>
                        &nbsp;</strong>
                    <input type="text" disabled="disabled" class="regular-text" id="publickey" value="<?php echo esc_attr($public_key); ?>" />
                    <br />
                    <strong style="display:inline-block; width: 125px;"><?php _e('Secret key:', 'give'); ?>
                        &nbsp;</strong>
                    <input type="text" disabled="disabled" class="regular-text" id="privatekey" value="<?php echo esc_attr($secret_key); ?>" />
                    <br />
                    <strong style="display:inline-block; width: 125px;"><?php _e('Token:', 'give'); ?>
                        &nbsp;</strong>
                    <input type="text" disabled="disabled" class="regular-text" id="token" value="<?php echo esc_attr($this->get_token($user->ID)); ?>" />
                    <br />
                    <input name="wams_revoke_api_key" type="checkbox" id="wams_revoke_api_key" />
                    <span class="description"><label for="wams_revoke_api_key"><?php _e('Revoke API Keys', 'give'); ?></label></span>
                <?php } ?>
            </td>
        </tr>
    </tbody>
</table>
<?php
if ((get_option('api_allow_user_keys', false) || current_user_can('manage_wams_settings')) && current_user_can('edit_user', $user->ID)) {

    $user = get_userdata($user->ID);
} // End if().٫


/**
		 * Generate new API keys for a user
		 *
		 * @param int     $user_id    User ID the key is being generated for.
		 * @param boolean $regenerate Regenerate the key for the user.
		 *
		 * @access public
		 * @since  1.1
		 *
		 * @return boolean True if (re)generated successfully, false otherwise.
		 */
		public function generate_api_key($user_id = 0, $regenerate = false)
		{

			// Bail out, if user doesn't exists.
			if (empty($user_id)) {
				return false;
			}

			$user = get_userdata($user_id);

			// Bail Out, if user object doesn't exists.
			if (!$user) {
				return false;
			}

			$new_public_key = '';
			$new_secret_key = '';

			if (!empty($_POST['from']) && 'profile' === $_POST['from']) {
				// For User Profile Page.
				if (!empty($_POST['wams_set_api_key'])) {
					// Generate API Key from User Profile page.
					$new_public_key = $this->generate_public_key($user->user_email);
					$new_secret_key = $this->generate_private_key($user->ID);
				} elseif (!empty($_POST['wams_revoke_api_key'])) {
					// Revoke API Key from User Profile page.
					$this->revoke_api_key($user->ID);
				} else {
					return false;
				}
			} else {
				// For Tools > API page.
				$public_key = $this->get_user_public_key($user_id);

				if (empty($public_key) && !$regenerate) {
					// Generating API for first time.
					$new_public_key = $this->generate_public_key($user->user_email);
					$new_secret_key = $this->generate_private_key($user->ID);
				} elseif ($public_key && $regenerate) {
					// API Key already exists and Regenerating API Key.
					$this->revoke_api_key($user->ID);
					$new_public_key = $this->generate_public_key($user->user_email);
					$new_secret_key = $this->generate_private_key($user->ID);
				} elseif (!empty($public_key) && !$regenerate) {
					// Doing nothing, when API Key exists but still try to generate again instead of regenerating.
					return false;
				} else {
					// Revoke API Key.
					$this->revoke_api_key($user->ID);
				}
			}

			update_user_meta($user_id, $new_public_key, 'wams_user_public_key');
			update_user_meta($user_id, $new_secret_key, 'wams_user_secret_key');

			return true;
		}

		/**
		 * Revoke a users API keys
		 *
		 * @access public
		 * @since  1.1
		 *
		 * @param int $user_id User ID of user to revoke key for
		 *
		 * @return bool
		 */
		public function revoke_api_key($user_id = 0)
		{

			if (empty($user_id)) {
				return false;
			}

			$user = get_userdata($user_id);

			if (!$user) {
				return false;
			}

			$public_key = $this->get_user_public_key($user_id);
			$secret_key = $this->get_user_secret_key($user_id);
			if (!empty($public_key)) {
				Wams_Cache::delete(Wams_Cache::get_key(md5('wams_api_user_' . $public_key)));
				Wams_Cache::delete(Wams_Cache::get_key(md5('wams_api_user_public_key' . $user_id)));
				Wams_Cache::delete(Wams_Cache::get_key(md5('wams_api_user_secret_key' . $user_id)));
				delete_user_meta($user_id, $public_key);
				delete_user_meta($user_id, $secret_key);
			} else {
				return false;
			}

			return true;
		}

        /**
		 * Retrieve the user's token
		 *
		 * @access private
		 * @since  1.1
		 *
		 * @param int $user_id
		 *
		 * @return string
		 */
		public function get_token($user_id = 0)
		{
			return hash('md5', $this->get_user_secret_key($user_id) . $this->get_user_public_key($user_id));
		}

        		/**
		 * Generate the public key for a user
		 *
		 * @access private
		 * @since  1.1
		 *
		 * @param string $user_email
		 *
		 * @return string
		 */
		private function generate_public_key($user_email = '')
		{
			$auth_key = defined('AUTH_KEY') ? AUTH_KEY : '';
			$public   = hash('md5', $user_email . $auth_key . date('U'));

			return $public;
		}

		/**
		 * Generate the secret key for a user
		 *
		 * @access private
		 * @since  1.1
		 *
		 * @param int $user_id
		 *
		 * @return string
		 */
		private function generate_private_key($user_id = 0)
		{
			$auth_key = defined('AUTH_KEY') ? AUTH_KEY : '';
			$secret   = hash('md5', $user_id . $auth_key . date('U'));

			return $secret;
		}

        /**
		 * Process an API key generation/revocation
		 *
		 * @access public
		 * @since  1.1
		 *
		 * @param array $args
		 *
		 * @return void
		 */
		public function process_api_key($args)
		{

			if (!wp_verify_nonce($_REQUEST['_wpnonce'], 'wams-api-nonce')) {
				wp_die(
					__('We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.', 'give'),
					__('Error', 'give'),
					array(
						'response' => 403,
					)
				);
			}

			if (empty($args['user_id'])) {
				wp_die(
					__('User ID Required.', 'give'),
					__('Error', 'give'),
					array(
						'response' => 401,
					)
				);
			}

			if (is_numeric($args['user_id'])) {
				$user_id = isset($args['user_id']) ? absint($args['user_id']) : get_current_user_id();
			} else {
				$userdata = get_user_by('login', $args['user_id']);
				$user_id  = $userdata->ID;
			}
			$process = isset($args['wams_api_process']) ? strtolower($args['wams_api_process']) : false;

			if ($user_id == get_current_user_id() && !wams_get_option('allow_user_api_keys') && !current_user_can('manage_wams_settings')) {
				wp_die(
					sprintf( /* translators: %s: process */
						__('You do not have permission to %s API keys for this user.', 'give'),
						$process
					),
					__('Error', 'give'),
					array(
						'response' => 403,
					)
				);
			} elseif (!current_user_can('manage_wams_settings')) {
				wp_die(
					sprintf( /* translators: %s: process */
						__('You do not have permission to %s API keys for this user.', 'give'),
						$process
					),
					__('Error', 'give'),
					array(
						'response' => 403,
					)
				);
			}

			switch ($process) {
				case 'generate':
					if ($this->generate_api_key($user_id)) {
						Wams_Cache::delete(Wams_Cache::get_key('wams_total_api_keys'));
						wp_redirect(esc_url_raw(add_query_arg('wams-messages[]', 'api-key-generated', 'edit.php?post_type=wams_forms&page=wams-tools&tab=api')));
						exit();
					} else {
						wp_redirect(esc_url_raw(add_query_arg('wams-messages[]', 'api-key-failed', 'edit.php?post_type=wams_forms&page=wams-tools&tab=api')));
						exit();
					}
					break;
				case 'regenerate':
					$this->generate_api_key($user_id, true);
					Wams_Cache::delete(Wams_Cache::get_key('wams_total_api_keys'));
					wp_redirect(esc_url_raw(add_query_arg('wams-messages[]', 'api-key-regenerated', 'edit.php?post_type=wams_forms&page=wams-tools&tab=api')));
					exit();
					break;
				case 'revoke':
					$this->revoke_api_key($user_id);
					Wams_Cache::delete(Wams_Cache::get_key('wams_total_api_keys'));
					wp_redirect(esc_url_raw(add_query_arg('wams-messages[]', 'api-key-revoked', 'edit.php?post_type=wams_forms&page=wams-tools&tab=api')));
					exit();
					break;
				default;
					break;
			}
		}