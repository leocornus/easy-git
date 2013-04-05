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

/**
 * the clean way to get a http request parameter's value.
 */
function wpg_get_request_param($param) {

    // try to find the selected theme name
    if (array_key_exists($param, $_POST)) {
        $value = $_POST[$param];
    } elseif (array_key_exists($param, $_GET)) {
        $value = $_GET[$param];
    } else {
        $value = '';
    }

    return $value;
}

/**
 * get ready the request context from $_POST and $_GET
 * it will return an array with the follow structure:
 * 'paramname' => 'value'
 */
function wpg_request_context() {

    $context = array();

    $repo = wpg_get_request_param('repo');
    $context['repo'] = $repo;

    // if we have the theme name, get ready the status.
    if ($repo !== '') {
        $changes = changeList($repo);
        $branch = getCurrentBranch($repo);
    } else {
        $changes = '';
        $branch = '';
    } 
    $context['changes'] = $changes;
    $context['branch'] = $branch;

    // the submit action.
    $context['action'] = wpg_get_request_param('submit');
    // git user.
    $user = wpg_get_request_param('gituser');
    if($user === "") {
        global $current_user;
        $user = $current_user->user_login;
    }
    $context['gituser'] = $user;

    return $context;
}
