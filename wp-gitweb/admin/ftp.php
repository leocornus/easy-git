<?php
/**
 * FTP Management Page.
 * 
 * Providing a list of users who is set up with FTP access.
 */

if(isset($_POST['wpg_ftp_admin_form_submit']) &&
   $_POST['wpg_ftp_admin_form_submit'] === 'Y') {

    // handle form submit!
    //wpg_handle_ftp_admin_form_submit();
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
    //echo wpg_widget_ftp_admin_form($ftp_access); 
    echo '<h3>FTP Access Users List</h3>';
    // show all active FTP access in jQuery DataTables.
    echo wpg_widget_ftps_list_dt();
  ?>

</div>

<?php

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

        // one tr for each row.
        $tr = <<<EOT
<tr>
  <td>{$ftp['ID']}</td>
  <td>{$user_name}</td>
  <td>{$ftp['secret_key']}</td>
  <td>{$ftp['ftp_home_dir']}</td>
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
</thead>
<tbody>
  {$trs}
</tbody>
<tfoot>
  <th>ID</th>
  <th>User</th>
  <th>Secret Key</th>
  <th>FTP Home Dir</th>
</tfoot>
</table>
{$dt_js}
EOT;

    return $dt;
}
