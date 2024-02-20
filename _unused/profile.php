<?php

/**
 * Template for the profile page
 *
 * This template can be overridden by copying it to yourtheme/wams/templates/profile.php
 *
 * Page: "Profile"
 *
 * @version 2.6.9
 *
 * @var string $mode
 * @var int    $form_id
 * @var array  $args
 */
if (!defined('ABSPATH')) {
	exit;
}
$description_key = WAMS()->profile()->get_show_bio_key($args);
?>

<div class="um <?php echo esc_attr($this->get_class($mode)); ?> um-<?php echo esc_attr($form_id); ?> um-role-<?php echo esc_attr(wams_user('role')); ?> ">

	<div class="um-form" data-mode="<?php echo esc_attr($mode) ?>">

		<?php
		/**
		 * WAMS hook
		 *
		 * @type action
		 * @title wams_profile_before_header
		 * @description Some actions before profile form header
		 * @input_vars
		 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage add_action( 'wams_profile_before_header', 'function_name', 10, 1 );
		 * @example
		 * <?php
		 * add_action( 'wams_profile_before_header', 'my_profile_before_header', 10, 1 );
		 * function my_profile_before_header( $args ) {
		 *     // your code here
		 * }
		 * ?>
		 */
		do_action('wams_profile_before_header', $args);

		if (wams_is_on_edit_profile()) { ?>
			<form method="post" action="" data-description_key="<?php echo esc_attr($description_key); ?>">
			<?php }

		/**
		 * WAMS hook
		 *
		 * @type action
		 * @title wams_profile_header_cover_area
		 * @description Profile header cover area
		 * @input_vars
		 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage add_action( 'wams_profile_header_cover_area', 'function_name', 10, 1 );
		 * @example
		 * <?php
		 * add_action( 'wams_profile_header_cover_area', 'my_profile_header_cover_area', 10, 1 );
		 * function my_profile_header_cover_area( $args ) {
		 *     // your code here
		 * }
		 * ?>
		 */
		do_action('wams_profile_header_cover_area', $args);

		/**
		 * WAMS hook
		 *
		 * @type action
		 * @title wams_profile_header
		 * @description Profile header area
		 * @input_vars
		 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage add_action( 'wams_profile_header', 'function_name', 10, 1 );
		 * @example
		 * <?php
		 * add_action( 'wams_profile_header', 'my_profile_header', 10, 1 );
		 * function my_profile_header( $args ) {
		 *     // your code here
		 * }
		 * ?>
		 */
		do_action('wams_profile_header', $args);

		/**
		 * WAMS hook
		 *
		 * @type filter
		 * @title wams_profile_navbar_classes
		 * @description Additional classes for profile navbar
		 * @input_vars
		 * [{"var":"$classes","type":"string","desc":"WAMS Posts Tab query"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage
		 * <?php add_filter( 'wams_profile_navbar_classes', 'function_name', 10, 1 ); ?>
		 * @example
		 * <?php
		 * add_filter( 'wams_profile_navbar_classes', 'my_profile_navbar_classes', 10, 1 );
		 * function my_profile_navbar_classes( $classes ) {
		 *     // your code here
		 *     return $classes;
		 * }
		 * ?>
		 */
		$classes = apply_filters('wams_profile_navbar_classes', ''); ?>

			<div class="um-profile-navbar <?php echo esc_attr($classes); ?>">
				<?php
				/**
				 * WAMS hook
				 *
				 * @type action
				 * @title wams_profile_navbar
				 * @description Profile navigation bar
				 * @input_vars
				 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
				 * @change_log
				 * ["Since: 2.0"]
				 * @usage add_action( 'wams_profile_navbar', 'function_name', 10, 1 );
				 * @example
				 * <?php
				 * add_action( 'wams_profile_navbar', 'my_profile_navbar', 10, 1 );
				 * function my_profile_navbar( $args ) {
				 *     // your code here
				 * }
				 * ?>
				 */
				do_action('wams_profile_navbar', $args); ?>
				<div class="um-clear"></div>
			</div>

			<?php
			/**
			 * WAMS hook
			 *
			 * @type action
			 * @title wams_profile_menu
			 * @description Profile menu
			 * @input_vars
			 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'wams_profile_menu', 'function_name', 10, 1 );
			 * @example
			 * <?php
			 * add_action( 'wams_profile_menu', 'my_profile_navbar', 10, 1 );
			 * function my_profile_navbar( $args ) {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action('wams_profile_menu', $args);

			if (wams_is_on_edit_profile() || WAMS()->user()->preview) {

				$nav = 'main';
				$subnav = WAMS()->profile()->active_subnav();
				$subnav = !empty($subnav) ? $subnav : 'default'; ?>

				<div class="um-profile-body <?php echo esc_attr($nav . ' ' . $nav . '-' . $subnav); ?>">

					<?php
					/**
					 * WAMS hook
					 *
					 * @type action
					 * @title wams_profile_content_{$nav}
					 * @description Custom hook to display tabbed content
					 * @input_vars
					 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
					 * @change_log
					 * ["Since: 2.0"]
					 * @usage add_action( 'wams_profile_content_{$nav}', 'function_name', 10, 1 );
					 * @example
					 * <?php
					 * add_action( 'wams_profile_content_{$nav}', 'my_profile_content', 10, 1 );
					 * function my_profile_content( $args ) {
					 *     // your code here
					 * }
					 * ?>
					 */
					do_action("wams_profile_content_{$nav}", $args);

					/**
					 * WAMS hook
					 *
					 * @type action
					 * @title wams_profile_content_{$nav}_{$subnav}
					 * @description Custom hook to display tabbed content
					 * @input_vars
					 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
					 * @change_log
					 * ["Since: 2.0"]
					 * @usage add_action( 'wams_profile_content_{$nav}_{$subnav}', 'function_name', 10, 1 );
					 * @example
					 * <?php
					 * add_action( 'wams_profile_content_{$nav}_{$subnav}', 'my_profile_content', 10, 1 );
					 * function my_profile_content( $args ) {
					 *     // your code here
					 * }
					 * ?>
					 */
					do_action("wams_profile_content_{$nav}_{$subnav}", $args); ?>

					<div class="clear"></div>
				</div>

				<?php if (!WAMS()->user()->preview) { ?>

			</form>

		<?php }
			} else {
				$menu_enabled = WAMS()->options()->get('profile_menu');
				$tabs = WAMS()->profile()->tabs_active();

				$nav = WAMS()->profile()->active_tab();
				$subnav = WAMS()->profile()->active_subnav();
				$subnav = !empty($subnav) ? $subnav : 'default';

				if ($menu_enabled || !empty($tabs[$nav]['hidden'])) { ?>

			<div class="um-profile-body <?php echo esc_attr($nav . ' ' . $nav . '-' . $subnav); ?>">

				<?php
					// Custom hook to display tabbed content
					/**
					 * WAMS hook
					 *
					 * @type action
					 * @title wams_profile_content_{$nav}
					 * @description Custom hook to display tabbed content
					 * @input_vars
					 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
					 * @change_log
					 * ["Since: 2.0"]
					 * @usage add_action( 'wams_profile_content_{$nav}', 'function_name', 10, 1 );
					 * @example
					 * <?php
					 * add_action( 'wams_profile_content_{$nav}', 'my_profile_content', 10, 1 );
					 * function my_profile_content( $args ) {
					 *     // your code here
					 * }
					 * ?>
					 */
					do_action("wams_profile_content_{$nav}", $args);

					/**
					 * WAMS hook
					 *
					 * @type action
					 * @title wams_profile_content_{$nav}_{$subnav}
					 * @description Custom hook to display tabbed content
					 * @input_vars
					 * [{"var":"$args","type":"array","desc":"Profile form shortcode arguments"}]
					 * @change_log
					 * ["Since: 2.0"]
					 * @usage add_action( 'wams_profile_content_{$nav}_{$subnav}', 'function_name', 10, 1 );
					 * @example
					 * <?php
					 * add_action( 'wams_profile_content_{$nav}_{$subnav}', 'my_profile_content', 10, 1 );
					 * function my_profile_content( $args ) {
					 *     // your code here
					 * }
					 * ?>
					 */
					do_action("wams_profile_content_{$nav}_{$subnav}", $args); ?>

				<div class="clear"></div>
			</div>

	<?php }
			}

			do_action('wams_profile_footer', $args); ?>
	</div>
</div>