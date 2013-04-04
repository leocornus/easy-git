<?php

/**
 * return the given option's values as an array
 */
function wpg_get_option_as_array($option_name) {

    $opt = get_site_option($option_name);
    // replace to make sure.
    $opt = str_replace("\r\n", "\n", $opt);
    return explode("\n", $opt);
}

/**
 * retrun the a list of ignore files as an array
 */
function wpg_get_ignore_files() {

    return wpg_get_option_as_array('wpg_ignore_files');
}

function wpg_get_ignore_patterns() {

    return wpg_get_option_as_array('wpg_ignore_patterns');
}

/**
 * return active repositories as an array with following
 * structure:
 *
 * array() {
 *     REPO_LABEL => REPO_PATH
 * }
 */
function wpg_get_active_repos($user_name=null) {

    if($user_name === null) {
        // find the current login user.
        global $current_user;
        $user_name = $current_user->user_login;
        // TODO: what id current user is not loged in?
    }

    $all = wpg_get_option_as_array('wpg_active_repos');
    $myRepos = array();
    foreach($all as $repo) {
        // user name is the beginning.
        $pos = strpos($repo, $user_name);
        if($pos === 0) {
            // on the one starts with user name.
            $theOne = array_slice(explode(";", $repo), 1);
            $myRepos[$theOne[0]] = $theOne[1];
        }
    }

    return $myRepos;
}
