<?php

namespace wams\admin\core;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\core\Admin_Notices')) {

	/**
	 * Class Admin_Notices
	 * @package um\admin\core
	 */
	class Admin_Notices
	{

		/**
		 * Notices list
		 *
		 * @var array
		 */
		private $list = array();

		/**
		 * Admin_Notices constructor.
		 */
		public function __construct()
		{
			add_action('admin_init', array(&$this, 'create_languages_folder'));

			add_action('admin_init', array(&$this, 'create_list'));
			add_action('admin_notices', array(&$this, 'render_notices'), 1);

			add_action('wp_ajax_wams_dismiss_notice', array(&$this, 'dismiss_notice'));
			add_action('admin_init', array(&$this, 'force_dismiss_notice'));

			add_action('current_screen', array(&$this, 'create_list_for_screen'));
		}

		/**
		 *
		 */
		public function create_list()
		{
			$this->check_required_plugins();
			$this->show_update_messages();
			$this->check_wrong_install_folder();

			$this->template_version();
			do_action('wams_admin_create_notices');
		}

		public function create_list_for_screen()
		{
			// if (WAMS()->admin()->screen()->is_own_screen()) {
			// 	// $this->secure_settings();
			// }
		}


		/**
		 * @return array
		 */
		public function get_admin_notices()
		{
			return $this->list;
		}


		/**
		 * @param $admin_notices
		 */
		function set_admin_notices($admin_notices)
		{
			$this->list = $admin_notices;
		}


		/**
		 * @param $a
		 * @param $b
		 *
		 * @return mixed
		 */
		function notice_priority_sort($a, $b)
		{
			if ($a['priority'] == $b['priority']) {
				return 0;
			}
			return ($a['priority'] < $b['priority']) ? -1 : 1;
		}


		/**
		 * Add notice to WAMS notices array
		 *
		 * @param string $key
		 * @param array $data
		 * @param int $priority
		 */
		function add_notice($key, $data, $priority = 10)
		{
			$admin_notices = $this->get_admin_notices();

			if (empty($admin_notices[$key])) {
				$admin_notices[$key] = array_merge($data, array('priority' => $priority));
				$this->set_admin_notices($admin_notices);
			}
		}


		/**
		 * Remove notice from WAMS notices array
		 *
		 * @param string $key
		 */
		function remove_notice($key)
		{
			$admin_notices = $this->get_admin_notices();

			if (!empty($admin_notices[$key])) {
				unset($admin_notices[$key]);
				$this->set_admin_notices($admin_notices);
			}
		}


		/**
		 * Render all admin notices
		 */
		function render_notices()
		{
			if (!current_user_can('manage_options')) {
				return;
			}

			$admin_notices = $this->get_admin_notices();

			$hidden = get_option('wams_hidden_admin_notices', array());

			uasort($admin_notices, array(&$this, 'notice_priority_sort'));

			foreach ($admin_notices as $key => $admin_notice) {
				if (empty($hidden) || !in_array($key, $hidden)) {
					$this->display_notice($key);
				}
			}
		}


		/**
		 * Display single admin notice
		 *
		 * @param string $key
		 * @param bool $echo
		 *
		 * @return void|string
		 */
		function display_notice($key, $echo = true)
		{
			$admin_notices = $this->get_admin_notices();

			if (empty($admin_notices[$key])) {
				return;
			}

			$notice_data = $admin_notices[$key];

			$class = !empty($notice_data['class']) ? $notice_data['class'] : 'updated';

			$dismissible = !empty($admin_notices[$key]['dismissible']);

			ob_start(); ?>

			<div class="<?php echo esc_attr($class) ?> wams-admin-notice notice <?php echo $dismissible ? 'is-dismissible' : '' ?>" data-key="<?php echo esc_attr($key) ?>">
				<?php echo !empty($notice_data['message']) ? $notice_data['message'] : '' ?>
			</div>

			<?php $notice = ob_get_clean();
			if ($echo) {
				echo $notice;
				return;
			} else {
				return $notice;
			}
		}




		/**
		 * To store plugin languages
		 */
		function create_languages_folder()
		{
			$path = WAMS()->files()->upload_basedir;
			$path = str_replace('/uploads/wams', '', $path);
			$path = $path . '/languages/plugins/';
			$path = str_replace('//', '/', $path);

			if (!file_exists($path)) {
				$old = umask(0);
				@mkdir($path, 0777, true);
				umask($old);
			}
		}

		/**
		 * Regarding page setup
		 */
		function check_required_plugins()
		{
			if (!class_exists('GFAPI')) {
				$this->add_notice(
					'required_plugins',
					array(
						'class'       => 'error',
						'message'     => 'Gravity Forms is required to run the website',
						'dismissible' => true,
					),
					20
				);
			}
		}

		/**
		 * Regarding page setup
		 */
		function install_core_page_notice()
		{
			$pages = WAMS()->config()->permalinks;

			if ($pages && is_array($pages)) {

				foreach ($pages as $slug => $page_id) {
					$page = get_post($page_id);

					if (!isset($page->ID) && array_key_exists($slug, WAMS()->config()->core_pages)) {
						$url = add_query_arg(
							array(
								'wams_adm_action' => 'install_core_pages',
								'_wpnonce'      => wp_create_nonce('install_core_pages'),
							)
						);

						ob_start();
			?>

						<p>
							<?php
							// translators: %s: Plugin name.
							echo wp_kses(sprintf(__('%s needs to create several pages (User Profiles, Account, Registration, Login, Password Reset, Logout, Member Directory) to function correctly.', 'wams'), WAMS_PLUGIN_NAME), WAMS()->get_allowed_html('admin_notice'));
							?>
						</p>

						<p>
							<a href="<?php echo esc_url($url); ?>" class="button button-primary"><?php esc_html_e('Create Pages', 'wams'); ?></a>
							&nbsp;
							<a href="javascript:void(0);" class="button-secondary wams_secondary_dismiss"><?php esc_html_e('No thanks', 'wams'); ?></a>
						</p>

			<?php
						$message = ob_get_clean();

						$this->add_notice(
							'wrong_pages',
							array(
								'class'       => 'updated',
								'message'     => $message,
								'dismissible' => true,
							),
							20
						);

						break;
					}
				}

				if (isset($pages['user'])) {
					$test = get_post($pages['user']);
					if (isset($test->post_parent) && $test->post_parent > 0) {
						$this->add_notice(
							'wrong_user_page',
							array(
								'class'   => 'updated',
								'message' => '<p>' . esc_html__('WAMS Setup Error: User page can not be a child page.', 'wams') . '</p>',
							),
							25
						);
					}
				}

				if (isset($pages['account'])) {
					$test = get_post($pages['account']);
					if (isset($test->post_parent) && $test->post_parent > 0) {
						$this->add_notice(
							'wrong_account_page',
							array(
								'class'   => 'updated',
								'message' => '<p>' . esc_html__('WAMS Setup Error: Account page can not be a child page.', 'wams') . '</p>',
							),
							30
						);
					}
				}
			}
		}

		/**
		 * Updating users
		 */
		public function show_update_messages()
		{
			if (!isset($_REQUEST['update'])) {
				return;
			}

			$update = sanitize_key($_REQUEST['update']);
			switch ($update) {
				case 'wams_purged_temp':
					$messages[0]['content'] = __('Your temp uploads directory is now clean.', 'wams');
					break;
				case 'wams_cleared_cache':
					$messages[0]['content'] = __('Your user cache is now removed.', 'wams');
					break;
				case 'wams_cleared_status_cache':
					$messages[0]['content'] = __('Your user statuses cache is now removed.', 'wams');
					break;
				case 'wams_got_updates':
					$messages[0]['content'] = __('You have the latest updates.', 'wams');
					break;
				case 'wams_often_updates':
					$messages[0]['err_content'] = __('Try again later. You can run this action once daily.', 'wams');
					break;
				case 'wams_form_duplicated':
					$messages[0]['content'] = __('The form has been duplicated successfully.', 'wams');
					break;
				case 'wams_settings_updated':
					$messages[0]['content'] = __('Settings have been saved successfully.', 'wams');
					break;
				case 'wams_user_updated':
					$messages[0]['content'] = __('User has been updated.', 'wams');
					break;
				case 'wams_users_updated':
					$messages[0]['content'] = __('Users have been updated.', 'wams');
					break;
				case 'wams_secure_expire_sessions':
					$messages[0]['content'] = __('All users sessions have been successfully destroyed.', 'wams');
					break;
				case 'wams_secure_restore':
					$messages[0]['content'] = __('Account has been successfully restored.', 'wams');
					break;
					// default:
					// 	$messages[0]['content'] = '';
					// 	break;
			}

			if (!empty($messages)) {
				foreach ($messages as $message) {
					if (isset($message['err_content'])) {
						$this->add_notice(
							'actions',
							array(
								'class'   => 'error',
								'message' => '<p>' . $message['err_content'] . '</p>',
							),
							50
						);
					} else {
						$this->add_notice(
							'actions',
							array(
								'class'   => 'updated',
								'message' => '<p>' . $message['content'] . '</p>',
							),
							50
						);
					}
				}
			}
		}

		/**
		 * Check if plugin is installed with correct folder
		 */
		function check_wrong_install_folder()
		{
			$invalid_folder = false;
			$slug_array = explode('/', WAMS_PLUGIN);
			if ($slug_array[0] != 'wams') {
				$invalid_folder = true;
			}

			if ($invalid_folder) {
				$this->add_notice(
					'invalid_dir',
					array(
						'class'   => 'error',
						// translators: %s: Plugin name.
						'message' => '<p>' . sprintf(__('You have installed <strong>%s</strong> with wrong folder name. Correct folder name is <strong>"wams"</strong>.', 'wams'), WAMS_PLUGIN_NAME) . '</p>',
					),
					1
				);
			}
		}




		/**
		 * Check Future Changes notice
		 */
		function future_changed()
		{

			ob_start(); ?>

			<p>
				<?php
				// translators: %1$s is a plugin name; %2$s is a #.
				echo wp_kses(sprintf(__('<strong>%1$s</strong> future plans! Detailed future list is <a href="%2$s" target="_blank">here</a>', 'wams'), WAMS_PLUGIN_NAME, '#'), WAMS()->get_allowed_html('admin_notice'));
				?>
			</p>

			<?php $message = ob_get_clean();

			$this->add_notice('future_changes', array(
				'class'         => 'updated',
				'message'       => $message,
			), 2);
		}

		/**
		 * Check Templates Versions notice
		 */
		public function template_version()
		{
			if (true === (bool) get_option('wams_override_templates_outdated')) {
				$link = admin_url('admin.php?page=wams_options&tab=override_templates');
				ob_start();
			?>

				<p>
					<?php
					// translators: %s override templates page link.
					echo wp_kses(sprintf(__('Your templates are out of date. Please visit <a href="%s">override templates status page</a> and update templates.', 'wams'), $link), WAMS()->get_allowed_html('admin_notice'));
					?>
				</p>

<?php
				$message = ob_get_clean();
				WAMS()->admin()->notices()->add_notice(
					'wams_override_templates_notice',
					array(
						'class'       => 'error',
						'message'     => $message,
						'dismissible' => false,
					),
					10
				);
			}
		}


		public function dismiss_notice()
		{
			WAMS()->admin()->check_ajax_nonce();

			if (empty($_POST['key'])) {
				wp_send_json_error(__('Wrong Data', 'wams'));
			}

			$this->dismiss(sanitize_key($_POST['key']));

			wp_send_json_success();
		}

		/**
		 * Dismiss notice by key.
		 *
		 * @param string $key
		 *
		 * @return void
		 */
		public function dismiss($key)
		{
			$hidden_notices = get_option('wams_hidden_admin_notices', array());
			if (!is_array($hidden_notices)) {
				$hidden_notices = array();
			}
			$hidden_notices[] = $key;
			update_option('wams_hidden_admin_notices', $hidden_notices);
		}

		function force_dismiss_notice()
		{
			if (!empty($_REQUEST['wams_dismiss_notice']) && !empty($_REQUEST['wams_admin_nonce'])) {
				if (wp_verify_nonce($_REQUEST['wams_admin_nonce'], 'um-admin-nonce')) {
					$hidden_notices = get_option('wams_hidden_admin_notices', array());
					if (!is_array($hidden_notices)) {
						$hidden_notices = array();
					}

					$hidden_notices[] = sanitize_key($_REQUEST['wams_dismiss_notice']);

					update_option('wams_hidden_admin_notices', $hidden_notices);
				} else {
					wp_die(__('Security Check', 'wams'));
				}
			}
		}
	}
}
