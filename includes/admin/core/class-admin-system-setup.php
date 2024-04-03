<?php

namespace wams\admin\core;


if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Admin_System_Setup')) {

    /**
     * Class General Functions
     * @package wams\admin\core
     */
    class Admin_System_Setup
    {
        public function site_setup_ajax_handler()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'wams-admin-nonce') || !current_user_can('manage_options')) {
                wp_die(esc_attr__('Security Check', 'wams'));
            }

            if (empty($_POST['param'])) {
                wp_send_json_error(__('Invalid Action.', 'wams'));
            }
            switch ($_POST['param']) {
                case 'install_pages':
                    $messages = $this->create_pages();
                    wp_send_json_success(['messages' => $messages]);
                    wp_die();
                    break;
                case 'install_views':
                    $messages = [];
                    $messages = $this->create_views();
                    wp_send_json_success(['messages' => $messages]);
                    wp_die();
                    break;
                case 'install_user_menu':
                    $menu_name = 'User Menu';
                    $menu_location = 'user-menu';
                    $pages = [
                        ['inbox', '<i class="bx bx-task"></i>'],
                        ['status', '<i class="bx bx-list-ol"></i>'],
                        ['notifications', '<i class="bx bx-bell"></i>'],
                        ['account', '<i class="bx bx-user-check"></i>'],
                        ['members', '<i class="bx bx-group"></i>'],
                        ['user', '<i class="bx bx-user"></i>'],
                        ['my-logins-history', '<i class="bx bx-log-in"></i>'],
                        ['link-my-telegram', '<i class="fab fa-telegram"></i>'],
                        ['charts', '<i class="fas fa-chart-bar"></i>'],
                        ['calendar', '<i class="far fa-calendar-alt"></i>'],
                        ['logout', '<i class="bx bx-log-out"></i>'],
                    ];
                    $messages = $this->setup_menu($menu_name, $menu_location, $pages);
                    wp_send_json_success(['messages' => $messages]);
                    break;
                case 'install_main_menu':
                    $messages = [];
                    $pages = [];
                    $menu_name = 'Main Menu';
                    $menu_location = 'main-menu';
                    $site_pages = get_pages();
                    foreach ($site_pages as $page) {
                        $user_pages = ['inbox', 'status', 'notifications', 'account', 'members', 'user', 'my-logins-history', 'link-my-telegram', 'charts', 'calendar'];
                        if (in_array($page->post_name, $user_pages)) continue;
                        $pages[] = [$page->post_name, '<i class="far fa-dot-circle"></i>'];
                    }
                    if (!empty($pages)) {
                        $messages = $this->setup_menu($menu_name, $menu_location, $pages);
                    }
                    wp_send_json_success(['messages' => $messages]);
                    break;
                case 'install_topbar_icon_box':

                    $icons = $this->create_icon_box();
                    $messages = ['icons created'];
                    wp_send_json_success(['messages' => $messages]);
                    break;
            }
        }

        private function create_icon_box()
        {
            if (get_current_blog_id() != WAMS_MAIN_BLOG_ID) {
                $site = get_blog_details(WAMS_MAIN_BLOG_ID);
                set_theme_mod('icon_' . WAMS_MAIN_BLOG_ID . '_icon_tag', '<img class="rounded-circle" src="' . get_site_icon_url(100, '', WAMS_MAIN_BLOG_ID) . '">');
                set_theme_mod('icon_' . WAMS_MAIN_BLOG_ID . '_page', __('Main Site', 'wams'));
                set_theme_mod('icon_' . WAMS_MAIN_BLOG_ID . '_page_link', $site->path);
                // set_theme_mod('icon_1_icon_tag', '<i class="fas fa-compress-arrows-alt fa-2x"></i>');
                // set_theme_mod('icon_1_page', __('Main Site', 'wams'));
                // set_theme_mod('icon_1_page_link', '/');
                $inbox = get_posts(['post_type' => 'page', 'name' => 'inbox']);
                if ($inbox) {
                    set_theme_mod('icon_2_icon_tag', '<i class="fas fa-tasks fa-2x"></i>');
                    set_theme_mod('icon_2_page', __('Inbox', 'wams'));
                    set_theme_mod('icon_2_page_link', get_blog_details()->path  . $inbox[0]->post_name);
                }

                $status = get_posts(['post_type' => 'page', 'name' => 'status']);
                if ($status) {
                    set_theme_mod('icon_3_icon_tag', '<i class="fas fa-hourglass-half fa-2x"></i>');
                    set_theme_mod('icon_3_page', __('Status', 'wams'));
                    set_theme_mod('icon_3_page_link', get_blog_details()->path  . $status[0]->post_name);
                }
            } else {
                $sites = get_sites();
                foreach ($sites as $site) {
                    if ($site->blog_id == WAMS_MAIN_BLOG_ID) {
                        set_theme_mod('icon_' . WAMS_MAIN_BLOG_ID . '_icon_tag', '<img class="rounded-circle" src="' . get_site_icon_url(100, '', WAMS_MAIN_BLOG_ID) . '">');
                        set_theme_mod('icon_' . WAMS_MAIN_BLOG_ID . '_page', __('Main Site', 'wams'));
                        set_theme_mod('icon_' . WAMS_MAIN_BLOG_ID . '_page_link', $site->path);
                    } else {
                        set_theme_mod('icon_' . $site->blog_id . '_icon_tag', '<img class="rounded-circle" src="' . get_site_icon_url(100, '', $site->blog_id) . '">');
                        set_theme_mod('icon_' . $site->blog_id . '_page', __(ucwords(str_replace('/', '', $site->path)), 'wams'));
                        set_theme_mod('icon_' . $site->blog_id . '_page_link', $site->path);
                    }
                }
            }
        }
        private function create_views()
        {
            $messages = [];
            $view = [];
            $forms = WAMS()->admin()->get_forms();
            foreach ($forms as $id => $title) {
                if (get_page_by_path(sanitize_title('view-' . $title), OBJECT, 'gravityview')) {
                    $messages[] = 'View : ' . $title . ' already exists';
                    continue;
                }
                $messages[] = $title;
                $view = array(
                    'post_title'     => 'View: ' . $title,
                    'post_content'   => '',
                    'post_name'      => sanitize_title('view-' . $title),
                    'post_type'      => 'gravityview',
                    'post_status'    => 'publish',
                    'post_author'    => get_current_user_id(),
                );
                $widgets = array(
                    'header_top' => array(
                        substr(md5(microtime(true)), 0, 13) => array(
                            'id' => 'search_bar',
                            'label' => __('Search Bar', 'gravityview'),
                            'search_layout' => 'horizontal',
                            'search_clear' => '0',
                            'search_fields' => '[{"field":"search_all","input":"input_text"}]',
                            'search_mode' => 'any',
                            'form_id' => $id
                        ),
                    ),
                    'header_left' => array(
                        substr(md5(microtime(true)), 0, 13) => array(
                            'id' => 'page_info',
                            'label' => __('Show Pagination Info', 'gravityview'),
                            'form_id' => $id
                        ),
                    ),
                    'header_right' => array(
                        substr(md5(microtime(true)), 0, 13) => array(
                            'id' => 'page_links',
                            'label' => __('Page Links', 'gravityview'),
                            'show_all' => '1',
                            'form_id' => $id
                        ),
                    ),
                    'footer_right' => array(
                        substr(md5(microtime(true)), 0, 13) => array(
                            'id' => 'page_links',
                            'label' => __('Page Links', 'gravityview'),
                            'show_all' => '1',
                            'form_id' => $id
                        ),
                    ),
                );

                $view_id = wp_insert_post($view);
                $post_meta = 'a:39:{s:8:"lightbox";s:1:"0";s:18:"show_only_approved";s:1:"0";s:23:"admin_show_all_statuses";s:1:"0";s:9:"page_size";s:2:"25";s:19:"hide_until_searched";s:1:"0";s:10:"hide_empty";s:1:"1";s:18:"no_entries_options";s:1:"0";s:15:"no_results_text";s:0:"";s:15:"no_entries_form";s:0:"";s:21:"no_entries_form_title";s:1:"1";s:27:"no_entries_form_description";s:1:"1";s:19:"no_entries_redirect";s:0:"";s:22:"no_search_results_text";s:0:"";s:12:"single_title";s:0:"";s:15:"back_link_label";s:0:"";s:17:"hide_empty_single";s:1:"1";s:12:"edit_locking";s:1:"1";s:9:"user_edit";s:1:"0";s:14:"unapprove_edit";s:1:"0";s:13:"edit_redirect";s:0:"";s:17:"edit_redirect_url";s:0:"";s:19:"action_label_update";s:6:"Update";s:19:"action_label_cancel";s:6:"Cancel";s:19:"action_label_delete";s:6:"Delete";s:11:"user_delete";s:1:"0";s:15:"delete_redirect";s:1:"1";s:19:"delete_redirect_url";s:0:"";s:12:"sort_columns";s:0:"";s:10:"sort_field";a:2:{i:0;s:0:"";i:1;s:0:"";}s:14:"sort_direction";a:2:{i:0;s:3:"ASC";i:1;s:3:"ASC";}s:10:"start_date";s:0:"";s:8:"end_date";s:0:"";s:10:"embed_only";s:1:"0";s:14:"user_duplicate";s:1:"0";s:11:"rest_enable";s:1:"0";s:10:"csv_enable";s:1:"1";s:11:"csv_nolimit";s:1:"0";s:10:"custom_css";s:0:"";s:17:"custom_javascript";s:0:"";}';
                update_post_meta($view_id, '_wams_views', sanitize_title('view_' . $title));
                update_post_meta($view_id, '_gravityview_form_id', $id);
                update_post_meta($view_id, '_gravityview_directory_template', 'default_table');
                update_post_meta($view_id, '_gravityview_template_settings', unserialize($post_meta));
                update_post_meta($view_id, '_gravityview_directory_widgets', $widgets);
                $messages[] = 'new View created :' . $title;

                if ($old_view_page = get_page_by_path(sanitize_title('view_' . $title))) {
                    wp_delete_post($old_view_page->ID);
                    $messages[] = 'An old view page founded and deteled : ' . $old_view_page->ID;
                }
                $view_page = array(
                    'post_title'     => 'View: ' . $title,
                    'post_content'   => '[gravityview id="' . $view_id . '"]',
                    'post_name'      => sanitize_title('view_' . $title),
                    'post_type'      => 'page',
                    'post_status'    => 'publish',
                    'post_author'    => get_current_user_id(),
                    'comment_status' => 'closed',
                );

                $post_id = wp_insert_post($view_page);
                update_post_meta($post_id, '_wams_views', sanitize_title($title));
                $messages[] = 'new page created :' . $title . ' for view id:  ' . $view_id;
            }
            return  $messages;
        }
        private function create_pages()
        {
            $messages = [];
            $default_pages = [
                [
                    'title' => 'Home',
                    'slug' => 'home',
                    'content' => '[home-page]',
                ],
                [
                    'title' => 'Inbox',
                    'slug' => 'inbox',
                    'content' => '[gravityflow page="inbox"]',
                ],
                [
                    'title' => 'Status',
                    'slug' => 'status',
                    'content' => '[gravityflow page="status"]',
                ],
                [
                    'title' => 'Notifications',
                    'slug' => 'notifications',
                    'content' => '[wams_notifications]',
                ],
                [
                    'title' => 'Messages',
                    'slug' => 'messages',
                    'content' => '[wams_messages]',
                ],
                [
                    'title' => 'My Logins',
                    'slug' => 'my-logins',
                    'content' => '[wams-user-logins]',
                ],
                [
                    'title' => 'Tasks Calendar',
                    'slug' => 'tasks-calendar',
                    'content' => '[wams-tasks-calendar]',
                ],
                [
                    'title' => 'Charts',
                    'slug' => 'charts',
                    'content' => '[wams-charts]',
                ],
                [
                    'title' => 'Link Telegram',
                    'slug' => 'link-my-telegram',
                    'content' => '[link-my-telegram]',
                ],
                [
                    'title' => 'Page Parser',
                    'slug' => 'page-parser',
                    'content' => '[page-parser]',
                ],
                [
                    'title' => 'Upload Vendors',
                    'slug' => 'upload-vendors-list',
                    'content' => '[upload-vendors-list]',
                ],
                [
                    'title' => 'Fetch RSS Feed',
                    'slug' => 'fetch-rss',
                    'content' => '[fetch-rss]',
                ],
            ];
            foreach ($default_pages as $page) {
                if (get_page_by_path($page['slug'])) {
                    $messages[] = 'Page : ' . $page['title'] . ' already exists';
                    continue;
                }
                $core_page = array(
                    'post_title'     => $page['title'],
                    'post_content'   => $page['content'],
                    'post_name'      => sanitize_title($page['slug']),
                    'post_type'      => 'page',
                    'post_status'    => 'publish',
                    'post_author'    => get_current_user_id(),
                    'comment_status' => 'closed',
                );
                $post_id = wp_insert_post($core_page);
                update_post_meta($post_id, '_wams_core', sanitize_title($page['title']));
                $messages[] = 'new page created :' . $page['title'];
            }
            $forms_page = [];
            $forms = WAMS()->admin()->get_forms();
            foreach ($forms as $id => $title) {
                if (get_page_by_path(sanitize_title($title))) {
                    $messages[] = 'Page : ' . $title . ' already exists';
                    continue;
                }
                $forms_page = array(
                    'post_title'     => $title,
                    'post_content'   => '[gravityform id="' . $id . '" title="false" description="false" ajax="true" ]',
                    'post_name'      => sanitize_title($title),
                    'post_type'      => 'page',
                    'post_status'    => 'publish',
                    'post_author'    => get_current_user_id(),
                    'comment_status' => 'closed',
                );

                $post_id = wp_insert_post($forms_page);
                update_post_meta($post_id, '_wams_forms', sanitize_title($title));
                $messages[] = 'new page created :' . $title;
            }
            return  $messages;
        }
        private function setup_menu($menu_name, $menu_location, $pages = [])
        {
            $messages = [];
            $new_menu_item = '';
            $menu_id = $this->create_menu($menu_name, $menu_location);
            if ($menu_id) {
                foreach ($pages as $page) {
                    $page_details = get_page_by_path($page[0]);
                    if ($page_details) {
                        $menu_item = [
                            'menu-item-object-id'   => $page_details->ID,
                            'menu-item-object'      => $page_details->post_type,
                            'menu-item-type'        => 'post_type',
                            'menu-item-status'      => 'publish',
                            'menu-item-title'       => __($page_details->post_title, 'wams'),
                            'menu-item-classes'     => 'user-menu-item',
                        ];
                        $new_menu_item = $this->create_menu_item($menu_id, $menu_item, $page[1]);
                        $messages[] = 'New Menu with ID: ' . $new_menu_item . ' has been created';
                    }
                }
            }
            return $messages;
        }

        private function create_menu($menu_name, $menu_location)
        {
            $menu_id = wp_create_nav_menu($menu_name);
            if (is_wp_error($menu_id)) {
                $messages[] =   $menu_id->get_error_message();
                $nav_menus = wp_get_nav_menus(['slug' => $menu_name]);
                if (isset($nav_menus[0]->term_id)) {
                    $menu_id = isset($nav_menus[0]->term_id) ? $nav_menus[0]->term_id : 0;
                }
            }
            $locations = get_theme_mod('nav_menu_locations');
            if (!$locations) {
                $locations = array();
            }
            // Assign the menu to the desired locations
            $locations[$menu_location] = $menu_id; // Assigning to 'main-menu' location
            set_theme_mod('nav_menu_locations', $locations);
            return $menu_id;
        }

        private function create_menu_item($menu_id, $item, $icon = '')
        {
            $messages = [];
            $item_object_id = $item['menu-item-object-id'];
            if (!$this->check_if_menu_item_exists($menu_id, $item_object_id)) {
                $new_menu = wp_update_nav_menu_item($menu_id, 0, $item);
                if (!is_wp_error($new_menu)) {
                    $messages[] = 'new menu item created' . $new_menu;
                    update_post_meta($new_menu, '_menu_item_icon', $icon);
                } else {
                    $messages[] = $new_menu->get_error_message();
                }
            }
            return $messages;
        }
        private function check_if_menu_item_exists($menu_id, $item_object_id)
        {
            $menu_items = wp_get_nav_menu_items($menu_id);
            if ($menu_items) {
                foreach ($menu_items as $menu_item) {
                    // If a menu item matches the page ID, it already exists in the menu
                    if ($menu_item->object_id == $item_object_id) {
                        return true; // Exit the loop since we found a match
                    }
                }
            }
            return false;
        }
    }
}
