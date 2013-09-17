<?php
/**
 * General settings page for merge.
 */

if (isset($_POST['wpg_merge_settings_form_submit']) &&
    $_POST['wpg_merge_settings_form_submit'] == 'Y') {

    // save settings submit. save user input to database.
    update_site_option('wpg_merge_folder', 
                       stripslashes($_POST['wpg_merge_folder']));
    // the dev branch name.
    update_site_option('wpg_merge_dev_branch', 
                       stripslashes($_POST['wpg_merge_dev_branch']));
    update_site_option('wpg_merge_uat_branch', 
                       stripslashes($_POST['wpg_merge_uat_branch']));
    //update_site_option('wpg_merge_prod_branch', 
    //                   stripslashes($_POST['wpg_merge_prod_branch']));
    // show the message.
    echo '<div class="updated"><p><strong>Settings Updated</strong></p></div>';
}

?>

<div class="wrap">
  <h2>WP GitWeb - Merge Settings</h2>
  <p>General settings for automatic merge.</p>

  <form name="wpg_merge_settings_form" method="post">
    <input type="hidden" name="wpg_merge_settings_form_submit" value="Y"/>
    <table class="form-table"><tbody>
      <tr>
        <th scope="row">Merge Folder Path: </th>
        <td>
          <input name="wpg_merge_folder" size="80"
            value="<?php echo get_site_option('wpg_merge_folder')?>">
        </td>
      </tr>
      <tr>
        <th scope="row">Development Branch Name: </th>
        <td>
          <input name="wpg_merge_dev_branch" size="18"
            value="<?php echo get_site_option('wpg_merge_dev_branch')?>">
        </td>
      </tr>
      <tr>
        <th scope="row">UAT Branch Name: </th>
        <td>
          <input name="wpg_merge_uat_branch" size="18"
            value="<?php echo get_site_option('wpg_merge_uat_branch')?>">
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
