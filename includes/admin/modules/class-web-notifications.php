<?php

namespace wams\admin\modules;

use wams\admin\core\Admin_Settings_API;


if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\modules\Web_Notifications')) {

	/**
	 * Class Debug
	 * @package wams\admin\Web_Notifications
	 */
	class Web_Notifications
	{
		/**
		 * @var object
		 */
		private $settings_api;

		/**
		 * @var array
		 */

		private $page;

		/**
		 * Admin_Menu constructor.
		 */
		function __construct()

		{
			$this->settings_api = new Admin_Settings_API();
			$this->init_variables();
			$this->settings_api->addSubpages($this->page['subpage']);
			$this->settings_api->set_sections($this->page['sections']);
			$this->settings_api->set_fields($this->page['fields']);
			$this->settings_api->register();
		}


		public function init_variables()
		{
			$this->page = [
				'subpage' => [
					[
						'parent_slug' => 'wams',
						'page_title' => 'Web_Notifications',
						'menu_title' => 'Web_Notifications',
						'capability' => 'edit_wams_settings',
						'menu_slug' => 'web-notifications',
						'callback' => [$this, 'show_page']
					]
				],
				'sections' => [
					[
						'id'    => 'wams_web_notifications_settings',
						'title' => __('Notifications Settings', 'wams')
					]
				],
				'fields' => [
					'wams_web_notifications_settings' => [
						[
							'name'  => 'enabled',
							'label' => __('Enable', 'wams'),
							'desc'  => __('Enable web notifications', 'wams'),
							'default' => 'on',
							'type'  => 'checkbox'
						],
						[
							'name'  => 'sound_enabled',
							'label' => __('Enable Sound', 'wams'),
							'desc'  => __('Enable Sound on new notifications', 'wams'),
							'default' => 'on',
							'type'  => 'checkbox'
						],
						[
							'name'              => 'interval',
							'label'             => __('Refresh Interval', 'wams'),
							'desc'              => __('Time in second between each AJAX call for new notification', 'wams'),
							'placeholder'       => __(''),
							'min'               => 45,
							'max'               => 10000,
							'step'              => '1',
							'type'              => 'number',
							'default'           => '60',
						],
					]
				]

			];
		}
		public function show_page()
		{
			wp_enqueue_script("web-notifications", WAMS_URL . 'assets/js/admin/web-notifications.js', array(), WAMS_VERSION, false);
			wp_enqueue_style("sweetalert2", WAMS_URL . 'assets/css/sweetalert2.min.css', array(), WAMS_VERSION);
			wp_enqueue_script("sweetalert2", WAMS_URL . 'assets/js/sweetalert2.min.js', array(), WAMS_VERSION, false);
			echo '<h1>Web_Notifications</h1>';
			echo '<div class="wraper">';
			echo '<div id="tabs">';
			$this->settings_api->show_navigation();
			echo '<div class="border p-3">';
			$this->settings_api->show_forms();
			echo '</div>';
			echo '</div>';
			echo '</div>';
			echo '<hr>';
			$this->web_notifications();
		}

		function web_notifications()
		{

			$users = get_users();

?>
			<div class="row">
				<div class="col-lg-12">
					<div class="card">
						<div class="card-body">
							<div class="row align-items-center">
								<div class="col-md-6">
									<div class="mb-2">
										<h5 class="card-title">Installation</h5>
									</div>
								</div>
								<div class="col-md-6">
									<div class="d-flex flex-wrap align-items-center justify-content-center gap-2 mb-3">

										<?php
										echo '<button class="btn btn-light" id="install-page">Install Page</button>';
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>


				<div class="col-lg-12">
					<div class="card">
						<div class="card-body">
							<div class="mb-2">
								<h5 class="card-title">Test Notification</h5>
							</div>
							<form id="notifications-test">
								<div class="mb-3">
									<label for="" class="form-label">Message</label>
									<textarea class="form-control" name="message" id="message" rows="6"></textarea>
								</div>
								<div class="mb-3">
									<label for="" class="form-label">User</label>
									<select class="form-select form-select-lg" name="user" id="user-id">
										<option selected>Select User</option>
										<?php
										foreach ($users as $user) {
											echo '<option value="' . $user->ID . '">' . $user->display_name . '</option>';
										}
										?>
									</select>
								</div>


								<div><button type="submit" id="send-notification-test" class="btn btn-primary">Submit</button></div>
							</form>
						</div>
					</div>
				</div>

			</div>
<?php

		}
	}
}
