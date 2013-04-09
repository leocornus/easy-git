<?php

/**
 * wordpress AJAX callback for git diff view.
 */
add_action('wp_ajax_nopriv_wpg_get_git_diff', 'wpg_get_git_diff_cb');
add_action('wp_ajax_wpg_get_git_diff', 'wpg_get_git_diff_cb');
function wpg_get_git_diff_cb() {

    $base_path = $_REQUEST['base_path'];
    $filename = $_REQUEST['filename'];

    $git_diff = wpg_get_git_diff($base_path, $filename);

    echo $git_diff;
    exit;
}
