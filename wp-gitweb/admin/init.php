<?php

// need the WordPress database object.
global $wpdb;

// Here are the tables name:
// the merge settings table.
define('WPG_DB_MERGE', 'wpg_merge');

// initialize functions for admin...

/**
 * create database tables for the wp_gitweb plugin.
 */
function wpg_create_tables($force=false) {

    // dbDelta function is in this file.
    require_once(ABSPATH . "wp-admin/includes/upgrade.php");

    // thw wpg_merge table.
    // we are using site options for this settings now.
    //$sql = "CREATE TABLE " . WPG_DB_MERGE . " (
    //    id mediumint(9) NOT NULL AUTO_INCREMENT,
    //    user_login varchar(60) NOT NULL DEFAULT '',
    //    merge_folder varchar(255) NOT NULL DEFAULT '',
    //    dev_branch varchar(64) NOT NULL DEFAULT '',
    //    uat_branch varchar(64) NOT NULL DEFAULT '',
    //    prod_branch varchar(64) NOT NULL DEFAULT '',
    //    PRIMARY KEY (id),
    //    UNIQUE KEY user_login (user_login)
    //);";
    //dbDelta($sql);

    // table to save the active repositories.
    $sql = "CREATE TABLE wpg_active_git_repos (
          repo_id mediumint(9) NOT NULL AUTO_INCREMENT,
          repo_label varchar(128) NOT NULL DEFAULT '',
          repo_path varchar(255) NOT NULL DEFAULT '',
          PRIMARY KEY (repo_id),
          UNIQUE KEY repo_label (repo_label)
        );";
    dbDelta($sql);

    // table to associate a user and a Git repo.
    $sql = "CREATE TABLE wpg_user_repo_associate (
          ID mediumint(9) NOT NULL AUTO_INCREMENT,
          user_login varchar(64) NOT NULL DEFAULT '',
          repo_id mediumint(9) NOT NULL DEFAULT 0,
          PRIMARY KEY (ID),
          UNIQUE KEY ID (ID)
        );";
    dbDelta($sql);

    // table to manage user's FTP access.
    $sql = "CREATE TABLE wpg_ftp_access (
          ID mediumint(9) NOT NULL AUTO_INCREMENT,
          user_login varchar(64) NOT NULL DEFAULT '',
          secret_key varchar(128) NOT NULL DEFAULT '',
          ftp_home_dir varchar(64) NOT NULL DEFAULT '',
          activate_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY (ID),
          UNIQUE KEY ID (ID)
        );";
    dbDelta($sql);
}
