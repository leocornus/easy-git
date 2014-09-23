<?php

/**
 * generate link for ticket id.
 */
function wpg_auto_link_ticket_id($subject) {

    // #12 or #3
    $pattern = '/#([0-9]+)/';
    if(preg_match($pattern, $subject) === 1) {
        $base_url = wpg_get_ticket_base_url();
        $href = "<a href='" . $base_url . "?id=\\1'>#\\1</a>";
        $subject = preg_replace($pattern, $href, $subject);
    }

    return $subject;
}

/**
 * extract the commit id from the given message.
 */
function wpg_extract_commit_id($message) {

    // the pattern for the commit id.
    $pattern = '/( ){1}([0-9a-fA-F]{7})(\]| |\)){1}/';
    $commit_id = null;
    if(preg_match($pattern, $message, $matches) === 1) {
        $commit_id = $matches[2];
    }

    return $commit_id;
}

/**
 * extract the ticket id from the commit comment.
 * assume the ticket it is reference in the following format.
 * 
 * Re: #2345
 *
 * as added in the commit form.
 */
function wpg_extract_ticket_id($commit_id, $subject) {

    $pattern = '/Re: #([0-9]+)/';
    // set the default id to -1 if there is not such pattern exist.
    $id = -1;
    if(preg_match($pattern, $subject, $matches) === 1) {
        $id = (int)$matches[1];
    }

    // we will pass 3 params in total to the filter.
    $id = apply_filters('wpg_extract_ticket_id', 
                        $id, $commit_id, $subject);

    return $id;
}

/**
 * form the merged message for the given values.
 */
function wpg_merged_msg($branch, $new_commit) {

    $msg = <<<EOT
Merged to <b>{$branch}</b> at commit <b>{$new_commit}</b>
EOT;

    // TODO: add filter to allow developer to tweak the format.

    return $msg;
}

/**
 * utility function to echo the notification message.
 */
function wpg_notification_msg($msg, $type="updated", $echo=true) {

    $message = <<<EOT
<div class="{$type}"><p>
{$msg}
</p></div>
EOT;

    if($echo) {
        echo $message;
        return;
    } else {
        return $message;
    }
}

/**
 * utilility function to save some states in cookie.
 * it is specificely for form submint redirect.
 *
 * $states provide a array with cookie names and values.
 * $expire tell how log those state will alive, in seconds.
 * $clean indicates clean the cookie states or not, default is false
 */
function wpg_set_cookie_state($states, $expire=60, $clean=false) {

    if($clean) {
        foreach($states as $name => $value) {
            // clean cookie by set the expire time to one hour 
            // before.
            setcookie($name, $value, time() - 3600);
        }
    } else {
        foreach($states as $name => $value) {
            setcookie($name, $value, time() + $expire);
        }
    }

    return;
}
