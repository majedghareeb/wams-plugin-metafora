<?php

namespace wams\admin\core;


if (!defined('ABSPATH')) exit;


if (!class_exists('wams\admin\core\Admin_Upgrade')) {


	/**
	 * Class Admin_Upgrade
	 *
	 * This class handles all functions that changes data structures and moving files
	 *
	 * @package um\admin\core
	 */
	class Admin_Upgrade
	{


		/**
		 * @var null
		 */
		protected static $instance = null;


		/**
		 * @var
		 */
		var $update_versions;
		var $update_packages;
		var $necessary_packages;


		/**
		 * @var string
		 */
		var $packages_dir;


		/**
		 * Main Admin_Upgrade Instance
		 *
		 * Ensures only one instance of WAMS is loaded or can be loaded.
		 *
		 * @since 1.0
		 * @static
		 * @see WAMS()
		 * @return Admin_Upgrade - Main instance
		 */
		static public function instance()
		{
			if (is_null(self::$instance)) {
				self::$instance = new self();
			}

			return self::$instance;
		}


		/**
		 * Admin_Upgrade constructor.
		 */
		function __construct()
		{
			$this->packages_dir = plugin_dir_path(__FILE__) . 'packages' . DIRECTORY_SEPARATOR;
			$this->necessary_packages = $this->need_run_upgrades();

			if (!empty($this->necessary_packages)) {
				add_action('admin_menu', array($this, 'admin_menu'), 0);
				add_action('wp_loaded', array($this, 'initialize_upgrade_packages'), 0);
			}

			add_action('in_plugin_update_message-' . WAMS_PLUGIN, array($this, 'in_plugin_update_message'));
		}

		/**
		 * Initialize packages for upgrade process.
		 * Note: Making that only for the 'manage_options' user and when AJAX running.
		 */
		public function initialize_upgrade_packages()
		{
			if (defined('DOING_AJAX') && DOING_AJAX && current_user_can('manage_options')) {
				$this->init_packages_ajax();

				add_action('wp_ajax_wams_run_package', array($this, 'ajax_run_package'));
				add_action('wp_ajax_wams_get_packages', array($this, 'ajax_get_packages'));
			}
		}

		/**
		 * Function for major updates
		 *
		 */
		function in_plugin_update_message($args)
		{
			$show_additional_notice = false;
			if (isset($args['new_version'])) {
				$old_version_array = explode('.', WAMS_VERSION);
				$new_version_array = explode('.', $args['new_version']);

				if ($old_version_array[0] < $new_version_array[0]) {
					$show_additional_notice = true;
				} else {
					if ($old_version_array[1] < $new_version_array[1]) {
						$show_additional_notice = true;
					}
				}
			}

			if ($show_additional_notice) {
				ob_start(); ?>

				<style type="text/css">
					.wams_plugin_upgrade_notice {
						font-weight: 400;
						color: #fff;
						background: #d53221;
						padding: 1em;
						margin: 9px 0;
						display: block;
						box-sizing: border-box;
						-webkit-box-sizing: border-box;
						-moz-box-sizing: border-box;
					}

					.wams_plugin_upgrade_notice:before {
						content: "\f348";
						display: inline-block;
						font: 400 18px/1 dashicons;
						speak: none;
						margin: 0 8px 0 -2px;
						-webkit-font-smoothing: antialiased;
						-moz-osx-font-smoothing: grayscale;
						vertical-align: top;
					}
				</style>

				<span class="wams_plugin_upgrade_notice">
					<?php
					// translators: %s: new version.
					echo wp_kses(sprintf(__('%s is a major update, and we highly recommend creating a full backup of your site before updating.', 'wams'), $args['new_version']), WAMS()->get_allowed_html('admin_notice'));
					?>
				</span>

			<?php ob_get_flush();
			}
		}


		/**
		 * @return array
		 */
		function get_extension_upgrades()
		{
			$extensions = WAMS()->extensions()->get_list();
			if (empty($extensions)) {
				return array();
			}

			$upgrades = array();
			foreach ($extensions as $extension) {
				$upgrades[$extension] = WAMS()->extensions()->get_packages($extension);
			}

			return $upgrades;
		}


		/**
		 * Get array of necessary upgrade packages
		 *
		 * @return array
		 */
		function need_run_upgrades()
		{
			$wams_last_version_upgrade = get_option('wams_last_version_upgrade', '1.3.88');

			$diff_packages = array();

			$all_packages = $this->get_packages();
			foreach ($all_packages as $package) {
				if (version_compare($wams_last_version_upgrade, $package, '<') && version_compare($package, WAMS_VERSION, '<=')) {
					$diff_packages[] = $package;
				}
			}

			return $diff_packages;
		}


		/**
		 * Get all upgrade packages
		 *
		 * @return array
		 */
		function get_packages()
		{
			$update_versions = array();
			$handle = opendir($this->packages_dir);
			if ($handle) {
				while (false !== ($filename = readdir($handle))) {
					if ($filename != '.' && $filename != '..') {
						if (is_dir($this->packages_dir . $filename)) {
							$update_versions[] = $filename;
						}
					}
				}
				closedir($handle);

				usort($update_versions, array(&$this, 'version_compare_sort'));
			}

			return $update_versions;
		}


		/**
		 *
		 */
		function init_packages_ajax()
		{
			foreach ($this->necessary_packages as $package) {
				$hooks_file = $this->packages_dir . $package . DIRECTORY_SEPARATOR . 'hooks.php';
				if (file_exists($hooks_file)) {
					$pack_ajax_hooks = include_once $hooks_file;

					foreach ($pack_ajax_hooks as $action => $function) {
						add_action('wp_ajax_wams_' . $action, "wams_upgrade_$function");
					}
				}
			}
		}


		/**
		 *
		 */
		function init_packages_ajax_handlers()
		{
			foreach ($this->necessary_packages as $package) {
				$handlers_file = $this->packages_dir . $package . DIRECTORY_SEPARATOR . 'functions.php';
				if (file_exists($handlers_file)) {
					include_once $handlers_file;
				}
			}
		}


		/**
		 * Add Upgrades admin menu
		 */
		function admin_menu()
		{
			add_submenu_page('wams', __('Upgrade', 'wams'), '<span style="color:#ca4a1f;">' . __('Upgrade', 'wams') . '</span>', 'manage_options', 'wams_upgrade', array(&$this, 'upgrade_page'));
		}


		/**
		 * Upgrade Menu Callback Page
		 */
		function upgrade_page()
		{
			$wams_last_version_upgrade = get_option('wams_last_version_upgrade', __('empty', 'wams')); ?>

			<div class="wrap">
				<h2>
					<?php
					// translators: %s: plugin name.
					echo wp_kses(sprintf(__('%s - Upgrade Process', 'wams'), WAMS_PLUGIN_NAME), WAMS()->get_allowed_html('admin_notice'));
					?>
				</h2>
				<p>
					<?php
					// translators: %1$s is a plugin version; %2$s is a last version upgrade.
					echo wp_kses(sprintf(__('You have installed <strong>%1$s</strong> version. Your latest DB version is <strong>%2$s</strong>. We recommend creating a backup of your site before running the update process. Do not exit the page before the update process has complete.', 'wams'), WAMS_VERSION, $wams_last_version_upgrade), WAMS()->get_allowed_html('admin_notice'));
					?>
				</p>
				<p><?php _e('After clicking the <strong>"Run"</strong> button, the update process will start. All information will be displayed in the <strong>"Upgrade Log"</strong> field.', 'wams'); ?></p>
				<p><?php _e('If the update was successful, you will see a corresponding message. Otherwise, contact technical support if the update failed.', 'wams'); ?></p>
				<h4><?php _e('Upgrade Log', 'wams') ?></h4>
				<div id="upgrade_log" style="width: 100%;height:300px; overflow: auto;border: 1px solid #a1a1a1;margin: 0 0 10px 0;"></div>
				<div>
					<input type="button" id="run_upgrade" class="button button-primary" value="<?php esc_attr_e('Run', 'wams') ?>" />
				</div>
			</div>

			<script type="text/javascript">
				var wams_request_throttle = 15000;
				var wams_packages;

				jQuery(document).ready(function() {
					jQuery('#run_upgrade').click(function() {
						jQuery(this).prop('disabled', true);

						wams_add_upgrade_log('Upgrade Process Started...');
						wams_add_upgrade_log('Get Upgrades Packages...');

						jQuery.ajax({
							url: '<?php echo esc_js(admin_url('admin-ajax.php')) ?>',
							type: 'POST',
							dataType: 'json',
							data: {
								action: 'wams_get_packages',
								nonce: wams_admin_scripts.nonce
							},
							success: function(response) {
								wams_packages = response.data.packages;

								wams_add_upgrade_log('Upgrades Packages are ready, start unpacking...');

								//run first package....the running of the next packages will be at each init.php file
								wams_run_upgrade();
							}
						});
					});
				});


				/**
				 *
				 * @returns {boolean}
				 */
				function wams_run_upgrade() {
					if (wams_packages.length) {
						// 30s between upgrades
						setTimeout(function() {
							var pack = wams_packages.shift();
							wams_add_upgrade_log('<br />=================================================================');
							wams_add_upgrade_log('<h4 style="font-weight: bold;">Prepare package "' + pack + '" version...</h4>');
							jQuery.ajax({
								url: '<?php echo esc_js(admin_url('admin-ajax.php')) ?>',
								type: 'POST',
								dataType: 'html',
								data: {
									action: 'wams_run_package',
									pack: pack,
									nonce: wams_admin_scripts.nonce
								},
								success: function(html) {
									wams_add_upgrade_log('Package "' + pack + '" is ready. Start the execution...');
									jQuery('#run_upgrade').after(html);
								}
							});
						}, wams_request_throttle);
					} else {
						window.location = '<?php echo add_query_arg(array('page' => 'wams', 'msg' => 'updated'), admin_url('admin.php')) ?>'
					}

					return false;
				}


				/**
				 *
				 * @param line
				 */
				function wams_add_upgrade_log(line) {
					var log_field = jQuery('#upgrade_log');
					var previous_html = log_field.html();
					log_field.html(previous_html + line + "<br />");
				}


				function wams_wrong_ajax() {
					wams_add_upgrade_log('Wrong AJAX response...');
					wams_add_upgrade_log('Your upgrade was crashed, please contact with support');
				}


				function wams_something_wrong() {
					wams_add_upgrade_log('Something went wrong with AJAX request...');
					wams_add_upgrade_log('Your upgrade was crashed, please contact with support');
				}
			</script>

<?php

		}


		function ajax_run_package()
		{
			WAMS()->admin()->check_ajax_nonce();

			if (empty($_POST['pack'])) {
				exit('');
			} else {
				$pack = sanitize_text_field($_POST['pack']);
				if (in_array($pack, $this->necessary_packages, true)) {
					$file = $this->packages_dir . $pack . DIRECTORY_SEPARATOR . 'init.php';
					if (file_exists($file)) {
						ob_start();
						include_once $file;
						ob_get_flush();
						exit;
					} else {
						exit('');
					}
				} else {
					exit('');
				}
			}
		}


		function ajax_get_packages()
		{
			WAMS()->admin()->check_ajax_nonce();

			$update_versions = $this->need_run_upgrades();
			wp_send_json_success(array('packages' => $update_versions));
		}


		/**
		 * Parse packages dir for packages files
		 */
		function set_update_versions()
		{
			$update_versions = array();
			$handle = opendir($this->packages_dir);
			if ($handle) {
				while (false !== ($filename = readdir($handle))) {
					if ($filename != '.' && $filename != '..')
						$update_versions[] = preg_replace('/(.*?)\.php/i', '$1', $filename);
				}
				closedir($handle);

				usort($update_versions, array(&$this, 'version_compare_sort'));

				$this->update_versions = $update_versions;
			}
		}






		/**
		 * Sort versions by version compare function
		 * @param $a
		 * @param $b
		 * @return mixed
		 */
		function version_compare_sort($a, $b)
		{
			return version_compare($a, $b);
		}
	}
}
