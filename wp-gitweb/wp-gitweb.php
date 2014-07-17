<?php
/*
Plugin Name: WP-GitWeb
Plugin URI: http://www.github.com/leocornus/easy-git
Description: GitWeb Interface for WordPress
Version: 0.2.2
Author: Leocornus Ltd.
Author URI: http://www.leocorn.com
License: GPLv2
*/

global $wpg_db_version;
// we will need this when we upgrade...
$wpg_db_version = "0.3";

// we will usng WPG or wpg as the prefix for this plugin.

// the symlink safe way for plugin path.
$plugin_file = __FILE__;
define('WPG_PLUGIN_FILE', $plugin_file);
define('WPG_PLUGIN_PATH', WP_PLUGIN_DIR . '/' . basename(dirname($plugin_file)));

// load php file for this plugin.
require_once(WPG_PLUGIN_PATH . '/tags.php');
require_once(WPG_PLUGIN_PATH . '/utils.php');
require_once(WPG_PLUGIN_PATH . '/ajax.php');
require_once(WPG_PLUGIN_PATH . '/widgets/navs.php');
require_once(WPG_PLUGIN_PATH . '/widgets/views.php');
require_once(WPG_PLUGIN_PATH . '/admin/init.php');

// network activation hook.
/**
 * installation function
 */
function wpg_install() {

    global $wpg_db_version;
    wpg_create_tables();
    add_site_option("wpg_db_version", $wpg_db_version);
}
// hook to the activation action.
register_activation_hook(WPG_PLUGIN_PATH . '/' . basename(__FILE__),
                         'wpg_install');

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

    // plugins_url will check this is a ssl request or not.
    wp_register_script('jquery.dataTables',
              plugins_url('wp-gitweb/js/jquery.dataTables.js'));
    //          array('jquery'), '1.9.1');
    // using wp_enqueue_script to load this js lib where you need.
    wp_register_style('jquery.dataTables',
              plugins_url('wp-gitweb/css/jquery.dataTables.css'));
}

/**
 * enqeue the jquery-ui-dialog lib
 */
function load_datatables() {

    // enqueue the jQuery DataTables lib
    wp_enqueue_script('jquery.dataTables');
    // enqueue the jQuery ui theme for the DataTables.
    wp_enqueue_style('jquery.dataTables');
}
add_action( 'admin_enqueue_scripts', 'load_datatables' );

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
    // the active Git repos management page.
    add_submenu_page('wp-gitweb/admin/settings.php',
                     'WP GitWeb Active Git Repositories Mangement',
                     'Repos Mangement',
                     'manage_options',
                     'wp-gitweb/admin/repos.php');
    // the merge management page.
    add_submenu_page('wp-gitweb/admin/settings.php',
                     'WP GitWeb Merge Mangement',
                     'Merge Mangement',
                     'manage_options',
                     'wp-gitweb/admin/merge.php');
}
