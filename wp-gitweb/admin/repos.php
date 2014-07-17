<?php
/**
 * Active Git Repositories Management Page.
 */


if(isset($_POST['wpg_active_git_repo_form_submit']) &&
   $_POST['wpg_active_git_repo_form_submit'] === 'Y') {

    $repo_id = (int) $_POST['repo_id'];
    if($repo_id > 0) {
        // update a existing repo.
        //wpg_handle_repos_admin_form_update();
    } else {
        // create a new repo.
        wpg_handle_repos_admin_form_create();
    }
}

?>

<div class="wrap">
  <h2>WP GitWeb - Active Git Repositories Management</h2>

  <p>Active Git Repositories Management Page</p>

  <?php 
    echo wpg_widget_repos_admin_form(); 
    echo '<h3>List Active Git Repositories</h3>';
    // show all active git repos in jQuery DataTables.
    echo wpg_widget_repos_list_dt();
  ?>

  <?php
  // search user name and list all associated Git repos.
  ?>

</div>

<?php

// function to show all git repos in a DataTables.

/**
 * render the form for creating and editing repos.
 */
function wpg_widget_repos_admin_form($repo_id=0) {

    if($repo_id > 1) {
        // trying to edit a existing repo.
        $submit_label = "Update Repository";
        // load the details about the repository.
        //$repo = wpg_get_active_repo($repo_id);
        //$repo_label_value = $repo['label'];
        //$repo_path_value = $repo['path'];
        //$repo_contributors_value = 
        //    implode(', ', $repo['contributors']);
    } else {
        // trying to create a new repo.
        $submit_label = "Create Repository";
    }

    $form = <<<EOT
  <form name="wpg_active_git_repo_form" method="post">
    <input type="hidden" name="wpg_active_git_repo_form_submit" 
           value="Y"
    />
    <input type="hidden" name="repo_id" value="{$repo_id}"/>
    <table class="form-table"><tbody>
      <tr>
        <th scope="row">Repository Label: </th>
        <td>
          <input name="repo_label" size="80"
            value="{$repo_label_value}">
        </td>
      </tr>
      <tr>
        <th scope="row">Repository Path: </th>
        <td>
          <input name="repo_path" size="80"
            value="{$repo_path_value}">
        </td>
      </tr>
      <tr>
        <th scope="row">Contributors: </th>
        <td>
          <input name="repo_contributors" size="80"
            value="{$repo_contributors_value}">
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
EOT;

  return $form;
}

/**
 * handle the request to create a new repository.
 */
function wpg_handle_repos_admin_form_create() {

    // analytics the REQUEST/POST.
    // once the form is submitted, the input fields will be set.
    $repo_label = $_POST['repo_label'];
    $repo_path = $_POST['repo_path'];
    $repo_contributors = $_POST['repo_contributors'];

    if(empty($repo_label) or empty($repo_path)) {
        // both label and path field are mandatory.
        $msg = 'Both <b>Repository Label</b> and ' .
               '<b>Repository Path</b> are Mandatory';
        wpg_notification_msg($msg, "error");
        return false;
    }

    // create new active repo
    $repo_id = wpg_create_repo($repo_label, $repo_path);
    // associate the user to new repo.
    if(!empty($repo_contributors)) {
        //wpg_associate_users_to_repo($list_of_users, $repo_id);
    }
    // preparing the message.
    $msg = 'Created new Repository: <b>' . $repo_id . 
           '</b> - <b>' . $repo_label . '</b>.';
    // default type is updated.
    wpg_notification_msg($msg);
}

/**
 * create a new repo based on repo label and path.
 */
function wpg_create_repo($repo_label, $repo_path) {

    global $wpdb;

    $success = $wpdb->insert(
        'wpg_active_git_repos',
        array(
            'repo_label' => $repo_label,
            'repo_path' => $repo_path
        ),
        array('%s', '%s')
    );

    // The auto_increment id could be accessed through insert_id.
    if($success) {
        return $wpdb->insert_id;
    } else {
        return -1;
    }
}

/**
 * get all active repos in a array with the following format:
 *
 * repo = array(
 */
function wpg_get_all_repos() {

    global $wpdb;
    $repos = $wpdb->get_results(
        "SELECT * FROM wpg_active_git_repos",
        ARRAY_A
    );
    return $repos;
}

/**
 * all repos in the datatable list.
 */
function wpg_widget_repos_list_dt() {

    // get all active repositories.
    $repos = wpg_get_all_repos();

    // one repo for each row:
    $rows = array();
    // foreach
    foreach($repos as $repo) {

        // one tr for each row.
        $tr = <<<EOT
<tr>
  <td>{$repo['repo_id']}</td>
  <td>{$repo['repo_label']}</td>
  <td>{$repo['repo_path']}</td>
  <td>{$repo['repo_contributors']}</td>
  <td>[tools comming]</td>
</tr>
EOT;
        $rows[] = $tr;
    }

    $trs = implode("\n", $rows);
    $table_id = "repos";
    // prepare the datatable javascript code.
    $dt_js = wpg_view_datatable_js($table_id, 25); 
    // jQuery UI Autocomplete 
    $autocomplet_js = "";

    // here is the datatable.
    $dt = <<<EOT
<table cellpadding="0" cellspacing="0" border="0" id="{$table_id}">
<thead>
  <th width="18px">ID</th>
  <th>Repository Label</th>
  <th>Repository Path</th>
  <th>Contributors</th>
  <th>Tools</th>
</thead>
<tbody>
  {$trs}
</tbody>
<tfoot>
  <th>ID</th>
  <th>Repository Label</th>
  <th>Repository Path</th>
  <th>Contributors</th>
  <th>Tools</th>
</tfoot>
</table>
{$dt_js}
EOT;

    return $dt;
}

/**
 * a re-usable function to generate JavaScript code to configurate
 * and load jQuery DataTable for the given table id.
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
        "sPaginationType": "full_numbers",
        "aoColumns" : [
            {"bSortable":false},
            {"bSortable":true},
            {"bSortable":true},
            {"bSortable":true},
            {"bSortable":false},
        ]
    } );
} );
-->
</script>
EOT;

    return $js;
}
