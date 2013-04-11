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
<tr id="log">
  <td><a href='{$log["url"]}'>{$log["id"]}</a></td>
  <td>{$log["email"]}</td>
  <td>{$log["date"]}</td>
  <td>{$log["comment"]}</td>
</tr>
EOT;
    }

    $log_trs = implode("\n", $log_rows);
    $alt_color_js = wpg_widget_tr_alternate_js("tr[id='log']",
          array("even" => "#FCFCEF"));
$the_view = <<<EOT
<p>Commit Logs for Git Repository:<br />
<b>{$repo}</b> <br />
-- at Branch: <b>{$branch}</b></p>

<table border = "1" width="90%">
  <thead>
  <tr>
    <th width="60">Commit</th>
    <th width="100">Author</th>
    <th width="80">Date</th>
    <th>Comment</th>
  </tr>
  </thead>
  <tfoot>
  <tr>
    <th width="60">Commit</th>
    <th width="100">Author</th>
    <th width="80">Date</th>
    <th>Comment</th>
  </tr>
  </tfoot>
  <tbody>
  {$log_trs}
</tbody></table>
{$alt_color_js}
EOT;

    return $the_view;
}

/**
 * jQuery way to alternate table role color.
 * @param $selector tr or tr[#log]
 * @param $alt_colors is array with the following format.
 *   array() (
 *     'odd' => 'white',
 *     'even' => 'grey'
 *   )
 */
function wpg_widget_tr_alternate_js($selector, $alt_colors) {

    $even_odd = array();
    foreach($alt_colors as $alt => $color) {
        $even_odd[] = <<<EOT
    $("{$selector}:{$alt}").css("background-color", "{$color}");
EOT;
    }
    
    $even_odd = implode("\n", $even_odd);

    $js = <<<EOT
<script type="text/javascript">
// using jQuery to alternate table row colors.
jQuery(document).ready(function($) {
{$even_odd}
});
</script>
EOT;

    return $js;
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
<tr id="change">
  <td align="center">
    <input type="checkbox" name="commits[]" 
           id="commits" 
           value="{$filename}"/>
  </td>
  <td>{$filename}</td>
  <td align="center">
    <a style="cursor: pointer" 
      onclick="javascript: changeDiff('{$base_path}', '{$filename}')"
    >{$status}</a>
  </td>
</tr>

EOT;
            $trs[] = $atr;
        }

        $change_trs = implode("\n", $trs);
        $alt_color_js = wpg_widget_tr_alternate_js("tr[id='change']",
            array("even" => "#FCFCEF"));
        // commit form fieldset.
        $commit_trs = wpg_widget_commit_fieldset($context); 

        $status_view = <<<EOT
<table border="1" id="status">
  <thead>
  <tr>
    <th align="center"><input type="checkbox" name="toggle" 
         onclick="toggleSelect()"/></th>
    <th>File Name</th>
    <th align="center">Status</th>
  </tr>
  </thead>
  <tbody>
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
{$alt_color_js}

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

jQuery(function($) {
    $("#gitDiffDialog").dialog({
        autoOpen: false,
        // minimum width in pixels
        position: "center",
        minWidth: 680,
        height: 446,
        show: {
            effect: "blind",
            duration: 1000
        },
        hide: {
            effect: "explode",
            duration: 1000
        }
    });
});

function changeDiff(basePath, fileName) {

    // load the dialog...
    jQuery("#gitDiff").html("<b>Loading ...</b>");
    jQuery("#gitDiffDialog").dialog("open");

    // get ready the post data.
    var data = {
        "action"    : "wpg_get_git_diff",
        "base_path" : basePath,
        "filename"  : fileName
    };

    jQuery.post("wp-admin/admin-ajax.php", data, function(response) {

        jQuery("#gitDiff").html(response);
    });
}
</script>

<div id="gitDiffDialog" title="WordPress GitWeb Diff Dialog">
  <div id="gitDiff">
  </div>
</div>
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
        // TODO: after commit hook.
        $result = "<pre>" . htmlentities($gitcommit) . "</pre>";
    }

    return $the_view . $result;
}

/**
 * the commit log view will for details change set for each file.
 */
function wpg_widget_changeset_view($commit_id) {

    $repos = wpg_get_repos_root_path();
    $pathes = array_values($repos);
    $changeset = "<b>" . $commit_id . "</b> is NOT a valid commit!";
    foreach($pathes as $path) {
       if(wpg_is_commit_valid($path, $commit_id)) {
           $commit_log = wpg_get_commit_changeset($path, $commit_id);
           $changeset = wpg_widget_changeset_html($commit_log);
           break;
       }
    }

    $the_view = <<<EOT
<h1>Details Change for Commit: {$commit_id}</h1>

<div style="font-size: 1.2em">{$changeset}</div>
EOT;

    return $the_view;
}

/**
 * the html view a commit changeset.
 */
function wpg_widget_changeset_html($commit_log) {

    $pos = strlen($commit_log['working_folder']) + 1;
    $file_trs = array();
    foreach($commit_log['changeset'] as $file => $status) {
        $filename = substr($file, $pos);
        $file_trs[] = <<<EOT
<tr id="log">
  <td align="center">{$status}</td>
  <td>{$filename}</td>
</tr>
EOT;
    }

    $file_trs = implode("\n", $file_trs);
    $alt_tr_js = wpg_widget_tr_alternate_js("tr[id='log']",
        array("even" => "#FCFCEF"));

    $changeset = <<<EOT
<table><tbody>
<tr>
  <th>Full ID:</th>
  <td>{$commit_log['commit_id']}</td>
</tr>
<tr>
  <th>Comment:</th>
  <td>
    <pre style="font-size: 1.2em; white-space: pre-wrap;"
    >{$commit_log['comment']}</pre>
  </td>
</tr>
<tr>
  <th>Branch:</th>
  <td>{$commit_log['branch']}</td>
</tr>
<tr>
  <th>Author:</th>
  <td>
    <a href="mailto:{$commit_log['author_email']}">
      {$commit_log['author_name']}
    </a>
    authored <b>{$commit_log['commit_age']}</b>
  </td>
</tr>
<tr>
  <td colspan="2">
  <b>{$commit_log['working_folder']}</b>
  </td>
</tr>
<tr>
  <td colspan="2">
  ---- <b>{$commit_log['change_stat']}</b>
  </td>
</tr>
<tr>
  <th align="center" width="120">Status</th>
  <th>File Name</th>
</tr>
{$file_trs}
<tr>
  <th align="center">Status</th>
  <th>File Name</th>
</tr>
</tbody></table>
{$alt_tr_js}
EOT;

    return $changeset;
}
