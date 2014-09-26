<?php

/**
 * wordpress AJAX callback for git diff view.
 */
add_action('wp_ajax_nopriv_wpg_get_git_diff', 'wpg_get_git_diff_cb');
add_action('wp_ajax_wpg_get_git_diff', 'wpg_get_git_diff_cb');
function wpg_get_git_diff_cb() {

    $base_path = wpg_get_request_param('base_path');
    $filename = wpg_get_request_param('filename');
    $commit_id = wpg_get_request_param('commit_id');

    if($commit_id === "") {
        // git status diff
        $git_diff = wpg_get_git_diff($base_path, $filename);
    } else {
        $git_diff = 
            wpg_get_git_log_diff($base_path, $filename, $commit_id);
    }

    echo $git_diff;
    exit;
}

/**
 * wordpress AJAX callback for dynamic user repos change selection.
 */
add_action('wp_ajax_nopriv_wpg_toggle_repo_opts', 
           'wpg_toggle_repo_opts_cb');
add_action('wp_ajax_wpg_toggle_repo_opts', 
           'wpg_toggle_repo_opts_cb');
function wpg_toggle_repo_opts_cb() {

    $user_login = wpg_get_request_param('user');
    $repos = wpg_get_active_repos($user_login);
    $opts = wpg_widget_options_html($repos);

    echo json_encode($opts);
    exit;
}

/**
 * WordPress AJAX callback for git merge.
 *
 * only logged in user can perform git merge. 
 */
//add_action('wp_ajax_nopriv_wpg_git_perform_merge',
//           'wpg_git_perform_merge_cb');
add_action('wp_ajax_wpg_git_perform_merge',
           'wpg_git_perform_merge_cb');
function wpg_git_perform_merge_cb() {

    $repo_path = wpg_get_request_param('repo_path');
    $from_branch = wpg_get_request_param('from_branch');
    $from_commit_id = wpg_get_request_param('from_commit_id');
    $to_branch = wpg_get_request_param('to_branch');
    $org_commit_id = wpg_get_request_param('org_commit_id');
    $ticket_id = intval(wpg_get_request_param('ticket_id'));

    // if we are merge the original commit, turn on
    // the cherry-pick recording commit option.
    $recording = ($from_commit_id === $org_commit_id);
    // perfrom git merge,
    $cherry_pick = 
        wpg_perform_merge($repo_path, $from_branch, $to_branch, 
                          $from_commit_id, $ticket_id, $recording);
    // parse the new commit id;
    $count = preg_match('/^\[' . $to_branch . ' ([0-9a-fA-F]{7})\]/', 
                        $cherry_pick, $matches);
    // check if there is any match?
    if ($count === 1) {
        // find match, preparing the merge message with 
        // found commit id.
        $ret = wpg_merged_msg($to_branch, $matches[1]);
    } else {
        // merge failed! return the message directly!
        $ret = $cherry_pick;
    }

    echo json_encode($ret);
    exit;
}

/**
 * wordpress AJAX callback for user anme suggestions.
 */
add_action('wp_ajax_nopriv_wpg_username_autocomplete', 'wpg_username_suggestion_cb');
add_action('wp_ajax_wpg_username_autocomplete', 'wpg_username_suggestion_cb');
function wpg_username_suggestion_cb() {

    $searchTerm = $_REQUEST['term'];
    // query wp_users table for the given term.
    global $wpdb;
    $likeTerm = '%' . $searchTerm . '%';
    $query = $wpdb->prepare("
        SELECT user_login, user_email, display_name
        FROM wp_users
        WHERE user_login like %s
        OR display_name like %s
    ",
    $likeTerm, $likeTerm
    );

    // using the default OBJECT as the output format.
    $users = $wpdb->get_results($query);

    $suggestions = array();
    foreach($users as $user) {
        $suggestion = array();
        // preparing label and value for each user.
        // TODO: should we add the avatar too?
        // label: Display Name - Email
        // value: user_login
        $suggestion['label'] = $user->display_name . ' - ' .
            $user->user_email . " - " . $user->user_login;
        $suggestion['value'] = $user->user_login;
        $suggestions[] = $suggestion;
    }

    // we are using jQuery.getJSON to trigger AJAX request,
    // it is different from direct AJAX call.
    $response = $_GET["callback"] . "(" . json_encode($suggestions) . ")";
    echo $response;
    exit;
}

/**
 * WordPress AJAX callback for check merge status.
 */
add_action('wp_ajax_nopriv_wpg_get_merge_status', 'wpg_get_merge_status');
add_action('wp_ajax_wpg_get_merge_status', 'wpg_get_merge_status');
function wpg_get_merge_status() {

    $commit_id = wpg_get_request_param('commit_id');
    $merge_folder = get_site_option('wpg_merge_folder');
    $status = array();

    // check merge status on UAT...
    $uat_branch = get_site_option('wpg_merge_uat_branch');
    $uat_path = $merge_folder . DIRECTORY_SEPARATOR . $uat_branch;
    $uat_id = wpg_git_log_grep($uat_path, $uat_branch, $commit_id);
    if($uat_id === False) {
        // not find in uat.
        $status['uat'] = 'Pending';
        $status['prod'] = 'Pending';
    } else {
        $status['uat'] = $uat_id[0];
        $prod_branch = get_site_option('wpg_merge_prod_branch');
        $prod_path = $merge_folder . DIRECTORY_SEPARATOR . 
                     $prod_branch;
        $prod_id = 
            wpg_git_log_grep($prod_path, $prod_branch, $commit_id);
        if($prod_id === False) {
            $status['prod'] = 'Pending';
        } else {
            $status['prod'] = $prod_id[0];
        }
    }

    echo json_encode($status);
    exit;
}
