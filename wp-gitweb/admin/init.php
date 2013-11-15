<?php

// need the WordPress database object.
global $wpdb;
global $wpg_db_version;
// we will need this when we upgrade...
$wpg_db_version = "0.3";

// Here are the tables name:
// the merge settings table.
define('WPG_DB_MERGE', 'wpg_merge');

// initialize functions for admin...

/**
 * installation function
 */
function wpg_install() {

    global $wpg_db_version;
    wpg_create_tables();
    add_site_option("wpg_db_version", $wpg_db_version);
}
// hook to the activation action.
register_activation_hook(__FILE__, 'wpg_install');

/**
 * create database tables for the wp_gitweb plugin.
 */
function wpg_create_tables($force=false) {

    // dbDelta function is in this file.
    require_once(ABSPATH . "wp-admin/includes/upgrade.php");

    // thw wpg_merge table.
    $sql = "CREATE TABLE " . WPG_DB_MERGE . " (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_login varchar(60) NOT NULL DEFAULT '',
        merge_folder varchar(255) NOT NULL DEFAULT '',
        dev_branch varchar(64) NOT NULL DEFAULT '',
        uat_branch varchar(64) NOT NULL DEFAULT '',
        prod_branch varchar(64) NOT NULL DEFAULT '',
        PRIMARY KEY (id),
        UNIQUE KEY user_login (user_login)
    );";
    dbDelta($sql);
}
