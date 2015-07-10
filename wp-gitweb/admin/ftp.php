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
    $button = wpg_widget_ftp_mount_button();
    echo "<h3>FTP Access Users List {$button}</h3>";
    // show all active FTP access in jQuery DataTables.
    echo wpg_widget_ftps_list_dt();
  ?>

</div>

<?php

/**
 * get ready the mount button.
 */
function wpg_widget_ftp_mount_button() {

    $ajax_url = admin_url('admin-ajax.php');
    $button = <<<EOT
<button type="button" class="btn btn-primary" id="ftp-mount"
>Mount Repos</button>
<script type="text/javascript" charset="utf-8">
<!--
jQuery(document).ready(function($) {
  $("#ftp-mount").click(function($) {

    var data = {
      'action' : 'wpg_mount_all_users_repo'
    };

    // get ready the waiting cursor,
    jQuery('html,body').css('cursor', 'wait');
    jQuery(':button').css('cursor', 'wait');

    jQuery.post('{$ajax_url}', data, function(response) {
      var res = JSON.parse(response);
      alert(res['summary']);
      // reset the cursor.
      jQuery('html,body').css('cursor', 'default');
      jQuery(':button').css('cursor', 'default');
    });
  });
});
-->
</script>
EOT;

    return $button;
}

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
        $base_url = "page={$_REQUEST['page']}&userlogin={$ftp['user_login']}&action";
        $user_name = <<<EOT
<strong>{$user_name}</strong><br/>
<a href="?{$base_url}=edit">
Edit</a> | 
<a href="?{$base_url}=delete">
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
