<?php
/**
 * Active Git Repositories Management Page.
 */

if(isset($_POST['wpg_active_git_repo_form_submit']) &&
   $_POST['wpg_active_git_repo_form_submit'] === 'Y') {

    // handle form submit!
    wpg_handle_repos_admin_form_submit();
} else {
    // normal page load, 
    // need parse the HTTP Request...
    $repo_label = wpg_get_request_param('repo');
    // the repository object.
    $repo = array();
    if($repo_label === "") {
        // no repository selected, skip...
    } else {
        // a repo is selected, what's the action?
        $action = wpg_get_request_param('action');
        switch($action) {
            case "edit":
                // edit a repository.
                $repo = wpg_get_repo($repo_label);
                //var_dump($repo);
                break;
        }
    }
}

?>

<div class="wrap">
  <h2>WP GitWeb - Active Git Repositories Management</h2>

  <p>Active Git Repositories Management Page</p>

  <?php 
    echo wpg_widget_repos_admin_form($repo); 
    echo '<h3>Active Git Repositories</h3>';
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
 * generate the auto complete js script for contributor field.
 */
function wpg_widget_autocomplete_js($input_id) {

    // Since Ajax is already built into the core 
    // WordPress administration screens, 
    // adding more administration-side Ajax functionality to your plugin 
    // is fairly straightforward, and this section describes how to do it.
    $js = <<<EOT
<script type="text/javascript" charset="utf-8">
<!--
jQuery(document).ready(function($) {
  function split(val) {
    return val.split(/,\s*/);
  }

  function extractLast( term ) {
    return split(term).pop();
  }

  // username auto complete for contributors.
  var wpg_username_ac = "wpg_username_autocomplete"
  var username_ac_data = {
      source: function(request, response) {
          $.getJSON(ajaxurl + "?callback=?&action="  + wpg_username_ac, 
                    { term: extractLast(request.term) }, response);
      },
      select: function(event, ui) {
          // do nothing for now.
          // selected value could get from ui param.
          // ui.item.id, ui.item.value.
          // testing...
          //alert (ui.item.value);
          var terms = split(this.value);
          terms.pop();
          terms.push(ui.item.value);
          terms.push("");
          this.value = terms.join(", ");
          return false;
      },
      search: function() {
          var term = extractLast(this.value);
          if(term.length < 2) {
            return false;
          }
      },
      focus: function() {
          return false;
      }
  };

  jQuery("#{$input_id}").autocomplete(username_ac_data);
});
-->
</script>
EOT;

    return $js;
}

/**
 * render the form for creating and editing repos.
 */
function wpg_widget_repos_admin_form($repo) {

    if(!empty($repo)) {
        // trying to edit a existing repo.
        $submit_label = "Update Repository";
        // load the details about the repository.
        //$repo = wpg_get_active_repo($repo_id);
        $repo_id = $repo['repo_id'];
        $repo_label_value = $repo['repo_label'];
        $repo_path_value = $repo['repo_path'];
        $repo_contributors_value = $repo['repo_contributors'];
    } else {
        // trying to create a new repo.
        $submit_label = "Create Repository";
        $repo_id = 0;
    }
    // jQuery UI Autocomplete for contributor field.
    $autocomplete_js = wpg_widget_autocomplete_js('repo_contributors');

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
        <th scope="row">Repository Contributors: </th>
        <td>
          <input name="repo_contributors" size="80"
                 id="repo_contributors"
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
  {$autocomplete_js}
EOT;

  return $form;
}

/**
 * handle the request to create a new repository.
 */
function wpg_handle_repos_admin_form_submit() {

    $repo_id = (int) $_POST['repo_id'];
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

    if ($repo_id > 0) {
        wpg_replace_repo($repo_label, $repo_path, $repo_id);
        // preparing the message for update.
        $msg = 'Updated Repository: <b>' . $repo_id . 
               '</b> - <b>' . $repo_label . '</b>.';
    } else {
        // create new active repo
        $repo_id = wpg_replace_repo($repo_label, $repo_path);
        // preparing the message for creation.
        $msg = 'Created new Repository: <b>' . $repo_id . 
               '</b> - <b>' . $repo_label . '</b>.';
    }
    // associate the user to new repo.
    if(!empty($repo_contributors)) {
        $users = explode(', ', $repo_contributors);
        wpg_associate_users_to_repo($users, $repo_id);
    }
    // default type is updated.
    wpg_notification_msg($msg);
}

/**
 * create a new repo based on repo label and path.
 */
function wpg_replace_repo($repo_label, $repo_path, $repo_id=0) {

    global $wpdb;

    $success = $wpdb->replace(
        'wpg_active_git_repos',
        array(
            'repo_id' => $repo_id,
            'repo_label' => $repo_label,
            'repo_path' => $repo_path
        ),
        array('%d', '%s', '%s')
    );

    // The auto_increment id could be accessed through insert_id.
    if($success) {
        return $wpdb->insert_id;
    } else {
        return -1;
    }
}

/**
 * get repository from a given repo label.
 * the rpository label should be unique
 */
function wpg_get_repo($repo_label, $include_contributors=true) {

    global $wpdb;

    $repo = $wpdb->get_row(
        "SELECT * FROM wpg_active_git_repos WHERE repo_label = '" . 
        $repo_label . "'",
        ARRAY_A
    );

    if ($include_contributors and $repo) {
        // attach the contributors list.
        $contributors = wpg_get_repo_contributors($repo['repo_id']);
        if($contributors) {
            $repo['repo_contributors'] = implode(', ', $contributors);
        }
    }

    return $repo;
}

/**
 * associate a list users to a repository.
 */
function wpg_associate_users_to_repo($users, $repo_id, $replace=true) {

    global $wpdb;

    // get all contributors for a git repository
    $existing = wpg_get_repo_contributors($repo_id);
    // only add those new users.
    foreach($users as $user) {
        if(!in_array($user, $existing)) {
            $success = $wpdb->insert(
                'wpg_user_repo_associate',
                array(
                    'user_login' => $user,
                    'repo_id' => $repo_id
                ),
                array('%s', '%d')
            );
        }
    }
    if($replace) {
        // remove all existing congributors 
        // which is not in the new user list.
        $remove = array_diff($existing, $users);
        foreach($remove as $user) {
            $wpdb->delete(
                'wpg_user_repo_associate',
                array(
                    'repo_id' => $repo_id,
                    'user_login' => $user
                ),
                array('%d', '%s')
            );
        }
    }
}

/**
 * get all active repos in a array with the following format:
 *
 * $repo = array(
 *     'repo_id' => 1,
 *     'repo_label' => 'label for the repo',
 *     'repo_path' => 'full path to the repo',
 *     'repo_contributors' => '',
 * );
 */
function wpg_get_all_repos() {

    global $wpdb;
    $repos = $wpdb->get_results(
        "SELECT * FROM wpg_active_git_repos",
        ARRAY_A
    );

    // get all contributors for each repo.
    $ret = array();
    foreach($repos as $repo) {
        $contributors = wpg_get_repo_contributors($repo['repo_id']);
        if($contributors) {
            // the contributor will implode with ', ' to
            // get ready for the jQuery Autocomplete multiple value
            // input field.
            $repo['repo_contributors'] = implode(', ', $contributors);
        }
        $ret[] = $repo;
    }

    return $ret;
}

/**
 * get all contributors for a repo.
 */
function wpg_get_repo_contributors($repo_id) {

    global $wpdb;

    // get_col will return a one dimensional array,
    // an empty array will be returned if now result found
    $contributors = $wpdb->get_col(
        "SELECT user_login FROM wpg_user_repo_associate WHERE
         repo_id = " . $repo_id 
    );

    return $contributors;
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

        // preparing the href link for edit.
        $label = <<<EOT
{$repo['repo_label']}<br/>
<a href="?page={$_REQUEST['page']}&repo={$repo['repo_label']}&action=edit">
Edit</a> | 
<a href="?page={$_REQUEST['page']}&repo={$repo['repo_label']}&action=delete">
Delete</a> 
EOT;

        // preparing the contributors list, using user's display name.
        $contributors = explode(', ', $repo['repo_contributors']);
        $contributor_names = array();
        foreach($contributors as $user_login) {
            $user = get_user_by('login', $user_login);
            if($user === false) {
                $contributor_names[] = $user_login;
            } else {
                $contributor_names[] = $user->first_name . " " . 
                                       $user->last_name . " - " .
                                       $user->user_email;
            }
        }
        $contributor_td = implode("<br/>", $contributor_names);

        // one tr for each row.
        $tr = <<<EOT
<tr>
  <td>{$repo['repo_id']}</td>
  <td>{$label}</td>
  <td>{$repo['repo_path']}</td>
  <td>{$contributor_td}</td>
</tr>
EOT;
        $rows[] = $tr;
    }

    $trs = implode("\n", $rows);
    $table_id = "repos";
    // prepare the datatable javascript code.
    $dt_js = wpg_view_datatable_js($table_id, 25); 

    // here is the datatable.
    $dt = <<<EOT
<table cellpadding="0" cellspacing="0" border="0" id="{$table_id}">
<thead>
  <th width="18px">ID</th>
  <th>Repository Label</th>
  <th>Repository Path</th>
  <th>Contributors</th>
</thead>
<tbody>
  {$trs}
</tbody>
<tfoot>
  <th>ID</th>
  <th>Repository Label</th>
  <th>Repository Path</th>
  <th>Contributors</th>
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
        ]
    } );
} );
-->
</script>
EOT;

    return $js;
}
