<?php

/**
 * Template for the profile posts
 *
 * This template can be overridden by copying it to yourtheme/wams/profile/posts.php
 *
 * Page: "Profile"
 *
 * @version 2.6.1
 *
 * @var object $posts
 * @var int    $count_posts
 */
if (!defined('ABSPATH')) {
	exit;
}

if (defined('DOING_AJAX') && DOING_AJAX) {
	//Only for AJAX loading posts
	if (!empty($posts)) {
		foreach ($posts as $post) {
			WAMS()->get_template('profile/posts-single.php', '', array('post' => $post), true);
		}
	}
} else {
	if (!empty($posts)) { ?>
		<div class="um-ajax-items">

			<?php foreach ($posts as $post) {
				WAMS()->get_template('profile/posts-single.php', '', array('post' => $post), true);
			}

			if ($count_posts > 10) { ?>
				<div class="um-load-items">
					<a href="javascript:void(0);" class="um-ajax-paginate um-button" data-hook="wams_load_posts" data-author="<?php echo esc_attr(wams_get_requested_user()); ?>" data-page="1" data-pages="<?php echo esc_attr(ceil($count_posts / 10)); ?>">
						<?php _e('load more posts', 'wams'); ?>
					</a>
				</div>
			<?php } ?>

		</div>

	<?php } else { ?>

		<div class="um-profile-note">
			<span>
				<?php if (wams_profile_id() == get_current_user_id()) {
					_e('You have not created any posts.', 'wams');
				} else {
					_e('This user has not created any posts.', 'wams');
				} ?>
			</span>
		</div>

<?php }
}
