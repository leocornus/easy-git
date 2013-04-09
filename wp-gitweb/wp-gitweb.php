<?php
/*
Plugin Name: WP-GitWeb
Plugin URI: http://www.github.com/leocornus/easy-git
Description: GitWeb Interface for WordPress
Version: 0.1
Author: Sean Chen
Author URI: http://www.leocorn.com
License: GPLv2
*/

// we will usng WPG or wpg as the prefix for this plugin.

// the symlink safe way for plugin path.
$plugin_file = __FILE__;
define('WPG_PLUGIN_FILE', $plugin_file);
define('WPG_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . basename(dirname($plugin_file)));

// load php file for this plugin.
require_once(WPG_PLUGIN_PATH . '/tags.php');
require_once(WPG_PLUGIN_PATH . '/ajax.php');
require_once(WPG_PLUGIN_PATH . '/widgets/navs.php');
require_once(WPG_PLUGIN_PATH . '/widgets/views.php');

// TODO: network activation hook.

/**
 * register the dataTables JavaScript lib.
 * DataTables depends on jQuery.  
 * we assume jQuery is already loaded.
 */
add_action('init', 'wpg_register_resources');
function wpg_register_resources() {

    // using wp_enqueue_style to load this css.
    // jquery ui dialog style seens not automatically loaded.
    wp_register_style('jquery-ui',
                      'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
}

// adding the network admin menu.
add_action('network_admin_menu', 'wpg_admin_init');
function wpg_admin_init() {

    add_menu_page('WP GitWeb', 'WP GitWeb',
                  'manage_options',
                  'wp-gitweb/admin/settings.php',
                  '');
    // the general settings page.
    add_submenu_page('wp-gitweb/admin/settings.php',
                     'WP GitWeb General Settings',
                     'General Settings',
                     'manage_options',
                     'wp-gitweb/admin/settings.php');
}