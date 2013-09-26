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
    $opts = wpg_widget_options_html(array_keys($repos));

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
    $to_branch = wpg_get_request_param('to_branch');
    $commit_id = wpg_get_request_param('commit_id');
    $ticket_id = intval(wpg_get_request_param('ticket_id'));


    // perfrom git merge,
    // parse the new commit id;
    $new_id = "abcdef1";
    $ret = "Merged to <b>" . $to_branch . "</b> at " . 
           "commit <b>" . $new_id . "</b>";

    echo json_encode($ret);
    exit;
}
