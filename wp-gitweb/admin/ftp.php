<?php
/**
 * FTP Management Page.
 * 
 * Providing a list of users who is set up with FTP access.
 */

if(isset($_POST['wpg_ftp_admin_form_submit']) &&
   $_POST['wpg_ftp_admin_form_submit'] === 'Y') {

    // handle form submit!
    wpg_handle_ftp_admin_form_submit();
} else {
    // normal page load, 
    // need parse the HTTP Request...
    $user_login = wpg_get_request_param('userlogin');
    // the ftp_access object, basically a record from
    // the WPG_FTP_ACCESS table
    $ftp_access = array();
    if($user_login === "") {
        // no user selected, skip...
    } else {
        // a user is selected, what's the action?
        $action = wpg_get_request_param('action');
        switch($action) {
            case "edit":
                // edit a user.
                $ftp_access = wpg_get_ftp_access($user_login);
                //var_dump($ftp_access);
                break;
            case "delete":
                // delete a repository.
                //wpg_handle_ftp_admin_delete($user_login);
                break;
        }
    }
}

?>

<div class="wrap">
  <h2>WP GitWeb - FTP Management</h2>

  <p>FTP Access Management Page</p>

  <?php 
    echo wpg_widget_ftp_admin_form($ftp_access); 
    echo '<h3>FTP Access Users List</h3>';
    // show all active FTP access in jQuery DataTables.
    echo wpg_widget_ftps_list_dt();
  ?>

</div>

<?php

/**
 * render the form for creating and editing repos.
 */
function wpg_widget_ftp_admin_form($ftp_access) {

    if(!empty($ftp_access)) {
        // trying to edit a existing ftp accesss.
        $submit_label = "Update FTP Access";
        // load the details about the repository.
        $id = $ftp_access['ID'];
        $user_login= $ftp_access['user_login'];
        $secret_key = $ftp_access['secret_key'];
        $ftp_home_dir = $ftp_access['ftp_home_dir'];
    } else {
        // trying to create a new repo.
        $submit_label = "Create FTP Access";
        $id = 0;
    }
    // jQuery UI Autocomplete for contributor field.
    //$autocomplete_js = wpg_widget_autocomplete_js('repo_contributors');

    $form = <<<EOT
  <form name="wpg_ftp_admin_form" method="post"> 
    <input type="hidden" name="wpg_ftp_admin_form_submit" 
           value="Y"
    />
    <input type="hidden" name="id" value="{$id}"/>
    <table class="form-table"><tbody>
      <tr>
        <th scope="row">User Login: </th>
        <td>
          <input name="user_login" size="80"
            value="{$user_login}">
        </td>
      </tr>
      <tr>
        <th scope="row">Secret Key: </th>
        <td>
          <input name="secret_key" size="80"
            value="{$secret_key}">
        </td>
      </tr>
      <tr>
        <th scope="row">FTP Home Directory: </th>
        <td>
          <input name="ftp_home_dir" size="80"
            value="{$ftp_home_dir}">
        </td>
      </tr>
      <tr>
        <th scope="row">
          <input type="submit" name="saveSetting" 
                 class="button-primary" value="{$submit_label}" />
        </th>
      </tr>
    </tbody></table>
  </form>
  {$autocomplete_js}
EOT;

  return $form;
}

/**
 * handle the request to create a new repository.
 */
function wpg_handle_ftp_admin_form_submit() {

    $id = (int) $_POST['id'];
    // analytics the REQUEST/POST.
    // once the form is submitted, the input fields will be set.
    $user_login = $_POST['user_login'];
    $secret_key = $_POST['secret_key'];
    $ftp_home_dir = $_POST['ftp_home_dir'];

    if(empty($user_login) or empty($ftp_home_dir)) {
        // both user and ftp_home_dir are mandatory.
        $msg = 'Both <b>User Login</b> and ' .
               '<b>FTP Home Directory</b> are Mandatory';
        wpg_notification_msg($msg, "error");
        return false;
    }

    if ($id > 0) {
        wpg_replace_ftp_access($user_login, 
            $secret_key, $ftp_home_dir, $id);
        // preparing the message for update.
        $msg = 'Updated FTP Access: <b>' . $id. 
               '</b> - <b>' . $user_login. '</b>.';
    } else {
        // create new ftp access. 
        $id = wpg_replace_ftp_access($user_login, 
            $secret_key, $ftp_home_dir);
        // preparing the message for creation.
        $msg = 'Created new FTP Access: <b>' . $id . 
               '</b> - <b>' . $user_login . '</b>.';
    }

    // mount the ftp folders.
    wpg_mount_user_ftp_folders($user_login, $ftp_home_dir);

    // default type is updated.
    wpg_notification_msg($msg);
}

/**
 * create a new ftp access.
 */
function wpg_replace_ftp_access($user_login, $secret_key, 
    $ftp_home_dir, $id=0) {

    global $wpdb;
    $data = array(
        'id' => $id,
        'user_login' => $user_login,
        'secret_key' => $secret_key,
        'ftp_home_dir' => $ftp_home_dir,
    );
    if ($id > 0) {
        // adding the activate time.
        $data['activate_time'] = 'now()';
    }

    $success = $wpdb->replace(
        'wpg_ftp_access', $data,
        array('%d', '%s', '%s', '%s')
    );

    // The auto_increment id could be accessed through insert_id.
    if($success) {
        return $wpdb->insert_id;
    } else {
        return -1;
    }
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

/**
 * all ftp access in the datatable list.
 */
function wpg_widget_ftps_list_dt() {

    // get all active repositories.
    $ftps = wpg_get_all_ftp_accesses();

    // one repo for each row:
    $rows = array();
    // foreach
    foreach($ftps as $ftp) {

        // get wordpress user.
        $user = get_user_by('login', $ftp['user_login']);
        if($user === false) {
            $user_name = $ftp['user_login'];
        } else {
            $user_name = $user->display_name . " - " .
                         $user->user_email;
        }
        // preparing the href link for edit.
        $user_name = <<<EOT
<strong>{$user_name}</strong><br/>
<a href="?page={$_REQUEST['page']}&userlogin={$ftp['user_login']}&action=edit">
Edit</a> | 
<a href="?page={$_REQUEST['page']}&userlogin={$ftp['user_login']}&action=delete">
Delete</a> 
EOT;

        // get assigned repos.
        $repos = wpg_get_contributor_repos($ftp['user_login']);
        $repos_list = implode("<br/>", $repos);

        // one tr for each row.
        $tr = <<<EOT
<tr>
  <td>{$ftp['ID']}</td>
  <td>{$user_name}</td>
  <td>{$ftp['secret_key']}</td>
  <td>{$ftp['ftp_home_dir']}</td>
  <td>{$repos_list}</td>
</tr>
EOT;
        $rows[] = $tr;
    }

    $trs = implode("\n", $rows);
    $table_id = "ftps";
    // prepare the datatable javascript code.
    $dt_js = wpg_view_datatable_js($table_id, 25); 

    // here is the datatable.
    $dt = <<<EOT
<table cellpadding="0" cellspacing="0" border="0" id="{$table_id}">
<thead>
  <th width="18px">ID</th>
  <th>User</th>
  <th>Secret Key</th>
  <th>FTP Home Dir</th>
  <th>Git Repos</th>
</thead>
<tbody>
  {$trs}
</tbody>
<tfoot>
  <th>ID</th>
  <th>User</th>
  <th>Secret Key</th>
  <th>FTP Home Dir</th>
  <th>Git Repos</th>
</tfoot>
</table>
{$dt_js}
EOT;

    return $dt;
}
