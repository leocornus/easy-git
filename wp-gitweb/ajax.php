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
