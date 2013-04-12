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

