<?php
/**
 * General settings page.
 */

wp_enqueue_script("wp_ajax_response");

if (isset($_POST['wpg_settings_form_submit']) &&
    $_POST['wpg_settings_form_submit'] == 'Y') {

    // save settings submit. save user input to database.
    update_site_option('wpg_ignore_files', 
                       stripslashes($_POST['wpg_ignore_files']));
    update_site_option('wpg_ignore_patterns', 
                       stripslashes($_POST['wpg_ignore_patterns']));
    update_site_option('wpg_active_repos', 
                       stripslashes($_POST['wpg_active_repos']));
    update_site_option('wpg_repo_roots',
                       stripslashes($_POST['wpg_repo_roots']));

    // show the message.
    echo '<div class="updated"><p><strong>Settings Updated</strong></p></div>';
}

?>

<div class="wrap">
  <h2>WP GitWeb - General Settings</h2>
  <p>General settings for WP GitWeb.</p>

  <form name="wpg_settings_form" method="post">
    <input type="hidden" name="wpg_settings_form_submit" value="Y"/>
    <table class="form-table"><tbody>
      <tr>
        <th scope="row">Git Repositories Root Path: <br/>
        (One File Each Line)
        </th>
        <td>
          <textarea name="wpg_repo_roots" 
                    rows="6" cols="98"
          ><?php echo get_site_option('wpg_repo_roots')?></textarea>
        </td>
      </tr>
      <tr>
        <th scope="row">Ignore Files: <br/>
        (One File Each Line)
        </th>
        <td>
          <textarea name="wpg_ignore_files" 
                    rows="8" cols="98"
          ><?php echo get_site_option('wpg_ignore_files')?></textarea>
        </td>
      </tr>
      <tr>
        <th scope="row">Ignore File Patterns: <br/>
        (One Pattern Each Line)
        </th>
        <td>
          <textarea name="wpg_ignore_patterns" 
                    rows="8" cols="98"
          ><?php echo get_site_option('wpg_ignore_patterns')?></textarea>
        </td>
      </tr>
      <tr>
        <th scope="row">Active Git Repositories: <br/>
        (One Repo Each Line, with following format:<br/>
         USER_NAME;REPO_LABEL;REPO_PATH)
        </th>
        <td>
          <textarea name="wpg_active_repos" 
                    rows="16" cols="98"
          ><?php echo get_site_option('wpg_active_repos')?></textarea>
        </td>
      </tr>
      <tr>
        <th scope="row"><input type="submit" name="saveSetting" class="button-primary" value="Save Settings" />
        </th>
        <td></td>
      </tr>
    </tbody></table>
  </form>
</div>
