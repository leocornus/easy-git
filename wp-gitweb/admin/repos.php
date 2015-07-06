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
            case "delete":
                // delete a repository.
                wpg_handle_repos_admin_delete($repo_label);
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
    $anim_image = plugins_url('wp-gitweb/images/ui-anim_basic_16x16.gif');
    $js = <<<EOT
<style>
  .ui-autocomplete-loading {
    background: white url("{$anim_image}") right center no-repeat;
  }
</style>
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
          $.getJSON(ajaxurl + "?callback=?&action="  + 
                    wpg_username_ac, 
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
        // strip extra space and , fro mthe end of the contributors.
        // jQuery UI autocomplete add the ', ' at the end
        // of the contributors.
        $trimmed = rtrim($repo_contributors, " ,");
        $users = explode(', ', $trimmed);
        wpg_associate_users_to_repo($users, $repo_id);
    }
    // default type is updated.
    wpg_notification_msg($msg);
}

/**
 * handle the request to delete a repoistory.
 */
function wpg_handle_repos_admin_delete($repo_label) {

    $counts = wpg_remove_repo($repo_label);

    if ($counts) {
        $msg = "Successfully Removed Repository: <b>" .
               $repo_label . 
               "</b> and its contributor associations!";
    } else {
        $msg = "Failed to Remove Repository: <b>" . 
               $repo_label . "</b>!";
    }

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
 * delete a repository and all its contributor associations.
 */
function wpg_remove_repo($repo_label) {

    global $wpdb;
    // reomve repository record and all associated records in 
    // user repo associate table.
    $repo = wpg_get_repo($repo_label);
    // delete contributor associations 
    $count = $wpdb->delete('wpg_user_repo_associate', 
                           array('repo_id' => $repo['repo_id']),
                           array('%d'));

    $rows = $wpdb->delete('wpg_active_git_repos',
                          array('repo_id' => $repo['repo_id']),
                          array('%d'));

    return $rows;
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
<strong>{$repo['repo_label']}</strong><br/>
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
                $contributor_names[] = $user->display_name . " - " .
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

