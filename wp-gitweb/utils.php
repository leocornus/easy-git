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
 * extract the ticket id from the commit comment.
 * assume the ticket it is reference in the following format.
 * 
 * Re: #2345
 *
 * as added in the commit form.
 */
function wpg_extract_ticket_id($subject) {

    $pattern = '/Re: #([0-9]+)/';
    // set the default id to -1 if there is not such pattern exist.
    $id = -1;
    if(preg_match($pattern, $subject, $matches) === 1) {
        $id = (int)$matches[1];
    }

    return $id;
}
