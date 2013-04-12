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
