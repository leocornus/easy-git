<?php

/**
 * preparing the repo form. the main page for git web.
 */
function wpg_widget_repo_form($context) {

    $repo = $context['repo'];
    $action = $context['action'];
    // active repositories for this user.
    $repos = wpg_get_active_repos($context['gituser']);
    $repo_opts = 
        wpg_widget_options_html(array_keys($repos), $repo);

    $the_view = "";
    // preparing the views based on context.
    if (empty($repo)) {
        // we will reject any action without repo select.
        $the_view = "<b>Please select a Git repository!<b>";
    } else {
        switch($action) {
            case "Check Logs":
                $the_view = wpg_widget_log_view($context);
                break;
            case "Check Status":
                $the_view = wpg_widget_status_view($context);
                break;
            case "Check Status Diff":
                // this is a hidden button.
                $the_view = wpg_widget_status_diff_view($context);
                break;
            case "Commit":
                $the_view = wpg_widget_commit_view($context);
                break;
            default:
                // using check status view.
                $the_view = wpg_widget_status_view($context);
                break;
        }
    }

    $form = <<<EOT
<h1>Welcome to OPSpedia GitWeb</h1>
<form name="repoform" method="POST" action="">
  Pleae select the Git repository: 
  <select name="repo" id="repo">
    {$repo_opts}
  </select>
  <br/>
  <input type="submit" name="submit" value="Check Status"/>
  <input type="submit" name="submit" value="Check Logs"/>
  <!-- input type="submit" name="submit" value="Git Pull"/ -->

  <p>{$the_view}</p>
</form>
EOT;

    return $form;
}

/**
 * The git log view.
 */
function wpg_widget_log_view($context) {

    $repo = $context['repo'];
    $branch = $context['branch'];
    $base_path = $context['base_path'];
    $logs = wpg_get_log_list($base_path);

    $log_rows = array();
    foreach($logs as $log) {
        $log_rows[] = <<<EOT
<tr>
  <td><a href='{$log["url"]}'>{$log["id"]}</a></td>
  <td>{$log["email"]}</td>
  <td>{$log["date"]}</td>
  <td>{$log["comment"]}</td>
</tr>
EOT;
    }

    $log_trs = implode("\n", $log_rows);

    $the_view = <<<EOT
<p>Commit Logs for Git Repository:<br />
<b>{$repo}</b> <br />
-- at Branch: <b>{$branch}</b></p>

<table border = "1" width="90%"><tbody>
  <tr>
    <th width="60">Commit</th>
    <th width="100">Author</th>
    <th width="80">Date</th>
    <th>Comment</th>
  </tr>
  {$log_trs}
</tbody></table>
EOT;

    return $the_view;
}

/**
 * view for git status review
 */
function wpg_widget_status_view($context) {

    $repo = $context['repo'];
    $branch = $context['branch'];
    $base_path = $context['base_path'];
    $changes = wpg_get_change_list($base_path);

    $status_view = "nothing to commit (working directory is clean)";
    if (is_array($changes) && count($changes) > 0) {

        $trs = array();
        foreach($changes as $filename => $status) {

            // the diff url for a commit based on status.
            // TODO:
            $diff_url = $status;
            //    wpg_get_diff_url($repo, $filename, $status);

            $atr = <<<EOT
<tr>
  <td align="center">
    <input type="checkbox" name="commits[]" 
           id="commits" 
           value="{$filename}"/>
  </td>
  <td>{$filename}</td>
  <td align="center">{$diff_url}</td>
</tr>
EOT;
            $trs[] = $atr;
        }

        $change_trs = implode("\n", $trs);
        // commit form fieldset.
        $commit_trs = wpg_widget_commit_fieldset($context); 

        $status_view = <<<EOT
<table border="1"><tbody>
  <tr>
    <th><input type="checkbox" name="toggle" 
         onclick="toggleSelect()"/></th>
    <th>File Name</th>
    <th>Status</th>
  </tr>
  {$change_trs}
  <tr><th colspan="3">
    <span>
    Please fill out the following form to commit 
    the selected files to Git repository.
    </span>
  </th></tr>
  <tr><td colspan="3">
    <table style="width: 80%"><tbody>
    {$commit_trs}
    </tbody></table>
  </td></tr>
</tbody></table>

<script type="text/javascript">
function toggleSelect() {

  var commits = document.repoform["commits[]"];
  if (document.repoform.toggle.checked) {
    for (i = 0; i < commits.length; i++) {
        commits[i].checked = true;
    }
  } else {
    for (i = 0; i < commits.length; i++) {
        commits[i].checked = false;
    }
  }
}
</script>
EOT;
    }

    $the_view = <<<EOT
<p>Change status for Git Repository: <br />
<b>{$repo}</b> <br />
-- at Branch: <b>{$branch}</b></p>
<p>{$status_view}</p>
EOT;

    return $the_view;
}

/**
 * preparing the commit form fieldset in tr format.
 */
function wpg_widget_commit_fieldset($context) {

    $fullname = $context['user_fullname'];
    $email = $context['user_email'];

    $trs = <<<EOT
<tr>
  <td>Commit Author Name:</td>
  <td><b>{$fullname}</b></td>
</tr>
<tr>
  <td>Commit Author Email:</td>
  <td><b>{$email}</b></td>
</tr>
<!-- tr>
  <td>Commit Access Key:</td>
  <td><input type="password" 
             name="accesskey"/></td>
</tr -->
<tr>
  <td>Commit Comment:</td>
  <td>
    <textarea name="gitcomment" cols="68" rows="3"></textarea>
  </td>
</tr>
<tr>
  <td colspan="2">
    <input type="submit" name="submit" 
      value="Commit" 
      onclick="return validateCommitForm()"/></td>
</tr>
<script type="text/javascript">
function validateCommitForm() {
  if (checkValue('gitcomment', 'Commit Comment')) {
    return false;
  }
}  

function checkValue(fieldName, label) {
  var x = document.forms["repoform"][fieldName].value;
  if (x == null || x == "") {
    alert(label + " must be filled out");
    return true;
  }

  return false;
}

</script>
EOT;

    return $trs;
}

/**
 * preparing the options html.
 */
function wpg_widget_options_html($options, $selected="", 
                                 $hasEmpty=true,
                                 $useNumeric=false) {

    $ret = $hasEmpty ? "<option></option>" : "";
    foreach ($options as $option => $label) {
        $sel = "";
        if (is_numeric($option)) {
            if (!$useNumeric) {
                // not use numeric!
                $option = $label;
            }
        }
        if($option === $selected) {
            $sel = "selected=\"selected\"";
        }

        $opt = <<<EOT
<option value="{$option}" {$sel}>{$label}</option>
EOT;
        $ret = $ret . $opt;
    }

    return $ret;
}

/**
 * preparing the view to show the status of a commit.
 */
function wpg_widget_commit_view($context) {

    $repo = $context['repo'];
    $branch = $context['branch'];
    $base_path = $context['base_path'];
    $author = $context['user_fullname'] . " <" . 
              $context['user_email'] . ">";

    $the_view = <<<EOT
<p>Commit status for Git Repository: <br/>
<b>{$repo}</b> <br />
-- at Branch: <b>{$branch}</b></p>
EOT;

    // TODO: verify the access key.
    $commit_files = wpg_get_request_param('commits');
    $comment = wpg_get_request_param('gitcomment');
    // TODO: Server site validation to make sure!
    if ($commit_files === "") {
        $result = "<b>No file selected for commit! " .
            "Please select at least one file and try again.</b>";
    } else if ($comment === "") {
        $result = "<b>Comment must be provied for a commit! " .
            "Please add some comment and try again.</b>";
    } else {
        // perform the commit now.
        $gitcommit = wpg_perform_commit($base_path, $commit_files,
                                        $comment, $author);
        $result = "<pre>" . htmlentities($gitcommit) . "</pre>";
    }

    return $the_view . $result;
}
