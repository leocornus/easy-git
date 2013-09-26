<?php
/**
 * provide some sample functions to show how to hook up to the 
 * actions offered the the wp-gitweb plugin.
 */

/**
 * the sample after commit hook to create a new ticket or
 * update an existing ticket.
 */
add_action('wpg_after_perform_commit', 
           'create_or_update_ticket', 10, 5);
function create_or_update_ticket($author, $comment, $commit_result,
    $commit_action, $ticket_id) {

    // using the current logged in user as the author.
    $current_user = wp_get_current_user();
    // reformat the comments.
    $comment_content = <<<EOT
{$comment}

{{{
{$commit_result}
}}}
EOT;

    // we are using the functions from wp-trac-client plugin
    //  - wptc_create_ticket
    //  - wptc_update_ticket
    // developer could use function_exists to make sure those
    // 2 functions are available!
    switch($commit_action) {

        case "create_ticket":
            // ticket attributes.
            $attrs = array();
            // current logged in user as the reporter
            $attrs['reporter'] = $current_user->user_login;
            $attrs['cc'] = $current_user->user_email;
            // default projects.
            $attrs['project'] = 'Default Project';
            // default priority.
            $attrs['priority'] = 'major';
            $attrs['owner'] = $current_user->user_login;
            // TODO: you could add your own default value here.
            // using the first line of comment as the summary
            $lines = explode("\n", $comment);
            wptc_create_ticket($lines[0], $comment_content, $attrs);
            break;
        case "update_ticket":
            // update ticket function will use current user
            // as the author.
            wptc_update_ticket(intval($ticket_id),
                               $comment_content, null);
            break;
        default:
            // do nothing.
            break;
    }
}

/**
 * the sample hook to update the existing ticket after merge finish.
 */
add_action('wpg_after_perform_merge', 'update_after_merge', 10, 3);
function update_after_merge($to_branch, $merge_msg, $ticket_id) {

    $comment = <<<EOT
Merged to branch '''{$to_branch}''', Please test.

Details Merge Message:

{{{
{$merge_msg}
}}}
EOT;

    if(function_exists('wptc_update_ticket')) {
        wptc_update_ticket($ticket_id, $comment, null);
    } 
}
