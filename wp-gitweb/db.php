<?php
// utility functions to do database manipulation.

/**
 * get repository from a given repo label.
 * the rpository label should be unique
 */
function wpg_get_repo($repo_label, $include_contributors=true) {

    global $wpdb;

    $repo = $wpdb->get_row(
        "SELECT * FROM wpg_active_git_repos WHERE repo_label = '" . 
        $repo_label . "'",
        ARRAY_A
    );

    if ($include_contributors and $repo) {
        // attach the contributors list.
        $contributors = wpg_get_repo_contributors($repo['repo_id']);
        if($contributors) {
            $repo['repo_contributors'] = implode(', ', $contributors);
        }
    }

    return $repo;
}

/**
 * get all active repos in a array with the following format:
 *
 * $repo = array(
 *     'repo_id' => 1,
 *     'repo_label' => 'label for the repo',
 *     'repo_path' => 'full path to the repo',
 *     'repo_contributors' => '',
 * );
 */
function wpg_get_all_repos() {

    global $wpdb;
    $repos = $wpdb->get_results(
        "SELECT * FROM wpg_active_git_repos",
        ARRAY_A
    );

    // get all contributors for each repo.
    $ret = array();
    foreach($repos as $repo) {
        $contributors = wpg_get_repo_contributors($repo['repo_id']);
        if($contributors) {
            // the contributor will implode with ', ' to
            // get ready for the jQuery Autocomplete multiple value
            // input field.
            $repo['repo_contributors'] = implode(', ', $contributors);
        }
        $ret[] = $repo;
    }

    return $ret;
}

/**
 * get all contributors for all active Git repositories.
 */
function wpg_get_all_contributors() {

    
}

/**
 * get all contributors for a repo.
 */
function wpg_get_repo_contributors($repo_id) {

    global $wpdb;

    // get_col will return a one dimensional array,
    // an empty array will be returned if now result found
    $contributors = $wpdb->get_col(
        "SELECT user_login FROM wpg_user_repo_associate WHERE
         repo_id = " . $repo_id 
    );

    return $contributors;
}

/**
 * get all repositories for the given contributor.
 */
function wpg_get_contributor_repos($user_login) {

    $repos = array();

    return $repos;
}

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
