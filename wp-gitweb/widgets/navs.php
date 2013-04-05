<?php

/**
 * preparing the repository navigation bar for the given user.
 */
function wpg_widget_repos_nav($user) {

    // load this user's repos.
    $repos = wpg_get_active_repos($user);
    //$gitweb_url = wpg_get_gitweb_url();
    $lis = "";
    foreach($repos as $label => $path) {

        $ali = <<<EOT
 <li>
   <a href="{$gitweb_url}?repo={$label}">{$label}</a>
 </li>
EOT;
        $lis = $lis . $ali;
    }

    $nav = "<ul>" . $lis . "</ul>";

    return $nav;
}
