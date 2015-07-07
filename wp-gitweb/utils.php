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
 * $expire tell how long those state will alive, in seconds.
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

/**
 * a re-usable function to generate JavaScript code to configurate
 * and load jQuery DataTable for the given table id.
 * "aoColumns" will turn on and off the sort for each column.
 * default is all columns are sortable. we will use default here.
 * 
 *    "aoColumns" : [
 *        {"bSortable":false},
 *        {"bSortable":true},
 *        {"bSortable":true},
 *        {"bSortable":true},
 *    ]
 */
function wpg_view_datatable_js($table_id, $per_page=25) {

    $js = <<<EOT
<script type="text/javascript" charset="utf-8">
<!--
jQuery(document).ready(function() {
    jQuery('#{$table_id}').dataTable( {
        "bProcessing": true,
        "bServerSide": false,
        // trun off the length change drop down.
        "bLengthChange" : true,
        // define the length memn option
        "aLengthMenu" : [[15, 25, 50, -1], [15, 25, 50, "All"]],
        // turn off filter.
        "bFilter" : true,
        // turn off sorting.
        "bSort" : true,
        // items per page.
        "iDisplayLength" : {$per_page},
        "sPaginationType": "full_numbers"
    } );
} );
-->
</script>
EOT;

    return $js;
}

/**
 * mount user's ftp folders.
 * return summary of the ftp folders.
 */
function wpg_mount_user_ftp_folders($user_login, $ftp_home) {

    // get repo_labels
    // foreach repo:
    //   get repo object 
    //   create ftp_folder: ftp_home/repo_label
    //   mount ftp_folder to repo_path
    $repo_labels = wpg_get_contributor_repos($user_login);
    //var_dump($repo_labels);
    foreach($repo_labels as $repo_label) {
        $repo = wpg_get_repo($repo_label);
        $repo_path = $repo['repo_path'];
        $ftp_folder = "{$ftp_home}/{$repo_label}";
        if(file_exists($ftp_folder)) {
            // try to get the source mount path:
            $source_path = wpg_mount_source($ftp_folder);
            if($source_path == NULL) {
                // not mounted at all!
                // do nothing here.
            } else if($source_path == $repo_path) {
                // the ftp folder is already mounted properly.
                // continue to next one.
                continue;
            } else {
                // umount the current one!
                wpg_sudo_shell_exec("umount {$ftp_folder}");
            }
        } else {
            // directory is not exist!
            wpg_sudo_shell_exec("mkdir -pv {$ftp_folder}");
        }
        // sudo mount -v --bind repo_path ftp_folder
        wpg_sudo_shell_exec("mount -v --bind {$repo_path} {$ftp_folder}");
    }
}

/**
 * execute a command as sudo.
 * this function depends on the settings for current system user,
 * the user execute php or php-fpm.
 */
function wpg_sudo_shell_exec($command) {

    // TODO: the user name should be configurable!
    //var_dump($command);
    shell_exec("ssh localhost 'sudo {$command}'");
}

/**
 * return the source path id the given path is mounted.
 */
function wpg_mount_source($path) {

    // assume the given path is exist, 
    // it could be simplly check by using the file_exists function.
    $command =  "mount -l | grep {$path}";
    $output = shell_exec($command);
    if($output == NULL) {
        // output is NULL tells not mounted.
        return NULL;
    } else {
        // analyze the output, try to find the source path.
        $source = explode("on", $output);
        // thie first one will be the source path.
        $source = trim($source[0]);
        return $source;
    }
}
