<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Control comment author display
 *
 * @param $return
 * @param $author
 * @param $comment_ID
 *
 * @return string
 */
function wams_comment_link_to_profile($return, $author, $comment_ID)
{

	$comment = get_comment($comment_ID);

	if (isset($comment->user_id) && !empty($comment->user_id)) {
		if (isset(WAMS()->user()->cached_user[$comment->user_id]) && WAMS()->user()->cached_user[$comment->user_id]) {

			$return = '<a href="' . WAMS()->user()->cached_user[$comment->user_id]['url'] . '">' . WAMS()->user()->cached_user[$comment->user_id]['name'] . '</a>';
		} else {

			wams_fetch_user($comment->user_id);

			WAMS()->user()->cached_user[$comment->user_id] = array('url' => wams_user_profile_url(), 'name' => wams_user('display_name'));
			$return = '<a href="' . WAMS()->user()->cached_user[$comment->user_id]['url'] . '">' . WAMS()->user()->cached_user[$comment->user_id]['name'] . '</a>';

			wams_reset_user();
		}
	}

	return $return;
}

add_filter('get_comment_author_link', 'wams_comment_link_to_profile', 10000, 3);
