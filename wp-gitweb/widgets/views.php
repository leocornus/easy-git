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
<form name="repoform" method="POST" action="">
  Pleae select the Git repository: 
  <select name="repo" id="repo">
    {$repo_opts}
  </select>
  <br/>
  <input type="submit" name="submit" value="Check Status"/>
  <input type="submit" name="submit" value="Check Logs"/>
  <input type="submit" name="submit" value="Git Pull"/>

  <p>{$the_view}</p>
EOT;

    return $form;
}

/**
 * view for git status review
 */
function wpg_widget_status_view($context) {

    $repo = $context['repo'];
    $branch = $context['branch'];
    $changes = $context['changes'];

    $status_view = "nothing to commit (working directory is clean)";
    if (is_array($changes) && count($changes) > 0) {

        $trs = array();
        foreach($changes as $filename => $status) {

            // the diff url for a commit based on status.
            // TODO:
            //$diff_url = 
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

        $change_trs = implode(" ", $trs);
        // TODO: commit form.
        $commit_trs = "";

        $status_view = <<<EOT
<table border="1"><tbody>
  <tr>
    <th><input type="checkbox" name="toggle" 
         onclick="toggleSelect()"/></th>
    <th>File Name</th>
    <th>Status</th>
  </tr>
  {$change_trs}
  <tr><td colspan="3">
    Please fill out the following form to commit 
    the selected files to Git repository.<br/>
    <b>All Fields are REQUIRED</b>.
  </td></tr>
  <tr><td colspan="3">
    <table><tbody>
    {$commit_trs}
    </tbody></table>
  </td></tr>
</tbody></table>
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
