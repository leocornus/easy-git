<?php
/**
 * provide some sample functions to show how to hook up to the 
 * actions offered the the wp-gitweb plugin.
 */

/**
 * the default after commit hook to create a minium ticket.
 */
//add_action('wpg_after_perform_commit',
//           'wpg_create_ticket_after_commit', 10, 5);
function wpg_create_ticket_after_commit($reporter, $comment, 
    $gitcommit) {

    // this depends on the wp-trac-client plugin.
    if(function_exists('wptc_create_ticket')) {
    
        // ticket attributes.
        $attrs['reporter'] = $reporter;
        wptc_create_ticket($comment, $gitcommit, $attrs);
    }
}

/**
 * update ticket after commit.
 */
function wpg_update_ticket_after_commit($author, $ticket_id,
    $comment, $gitcommit) {

    if(function_exists('wptc_update_ticket')) {

        // 
        wptc_update_ticket($ticket_id, 
                           $comment . "\n\n" . $gitcommit,
                           null);
    }
}


