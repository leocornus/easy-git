<?php
// utility functions to do database manipulation.

/**
 * create a merge setting.
 */
function wpg_add_merge_setting($user_login, $merge_folder,
    $dev_branch, $uat_branch, $prod_branch) {

    global $wpdb; 
}

/**
 * return all merge setting for the given user.
 */
function wpg_get_merge_setting($user_login = null) {

    if($user_login === null) {
        if(is_user_logged_in()) {
            // try to get the current user.
            $user = wp_get_current_user();
            $user_login = $user->user_login;
        } else {
            // user not logged in
            return null;
        }
    }

    global $wpdb;

    $query = "SELECT * FROM " . WPG_DB_MERGE .
             " WHERE user_login = %s";
    $query = $wpdb->prepare($query, $user_login);
    // ARRAY_A will return the the row as 
    // 'COLUMN_NAME' => 'value' 
    // null if not match record found.
    $setting = $wpdb->get_row($query, ARRAY_A);

    return $setting;
}
