<?php

/**
 * preparing the repo form. the main page for git web.
 */
function wpg_widget_repo_form($context) {

    // redirect to login page if user not logged in.
    auth_redirect();

    $gituser = $context['gituser'];
    $repo = $context['repo'];
    $action = $context['action'];
    // active repositories for this user.
    $repos = wpg_get_active_repos($gituser);
    $repo_opts = 
        wpg_widget_options_html($repos, $repo);
    // preparin the user dropdown based on current user's role.
    $user_select_html = "";
    if(wpg_is_code_reviewer()) {
        // code reviewer has
        $contributors = wpg_get_contributors();
        $user_opts =
            wpg_widget_options_html($contributors, $gituser);
        $repo_select_js = wpg_widget_user_repo_js();
        $user_select_html = <<<EOT
  <select name="gituser" id="gituser">
    {$user_opts}
  </select>
  {$repo_select_js}
EOT;
    }

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
                // set the action to check logs
                //$context['action'] = "Check Logs";
                //// save the commit result on context.
                //$context['commit_message'] = $the_view;
                //session_start();
                //// save the context on session.
                //$_SESSION['commit_context'] = $context;
                ////session_write_close();
                // redirect
                $commit_id = wpg_extract_commit_id($the_view);
                header('Location: ' . 
                       $_SERVER['REQUEST_URI'] . '/commit/?id=' .
                       $commit_id);
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
  {$user_select_html}
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
 * the javascript for user and repository 
 */
function wpg_widget_user_repo_js($user_id="gituser",
                                 $repo_id="repo") {

    $ajax_url = admin_url("admin-ajax.php");

    $js = <<<EOT
<script type="text/javascript" charset="utf-8">
<!--
jQuery("select#{$user_id}").change(function() {
    user = this.value;
    if(user == "") {
        // reset.
        jQuery("select#{$repo_id}").html("");
    } else {
        // preparing the ajax request data
        var data ={
            "action" : "wpg_toggle_repo_opts",
            "user"   : user
        };
        jQuery.post("{$ajax_url}",
            data,
            function(response) {
                res = JSON.parse(response);
                jQuery("select#{$repo_id}").html(res);
            });
    }
});
-->
</script>
EOT;

    return $js;
}

/**
 * The git log view.
 */
function wpg_widget_log_view($context) {

    $repo = $context['repo'];
    $branch = $context['branch'];
    $base_path = $context['base_path'];
    // get commit message if it is exist.
    $commit_message = $context['commit_message'];
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
{$commit_message}
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
    // preparing the patch view js.
    $patch_js = wpg_widget_files_patch_js($base_path, null,
        10, 'filename', 'status', 3);

    $status_view = "nothing to commit (working directory is clean)";
    if (is_array($changes) && count($changes) > 0) {

        $trs = array();
        foreach($changes as $filename => $status) {

            // the diff url for a commit based on status.
            $diff_url = $status;
            // status name is return of function
            // wpg_get_status_name.
            if($status === 'modified') {
                // only add diff dialog for modified files.
                $diff_url = <<<EOT
<a style="cursor: pointer" 
  onclick="javascript: changeDiff('{$base_path}', '{$filename}')"
>{$status}</a>
EOT;
            }

            $atr = <<<EOT
<tr id="change">
  <td align="center">
    <input type="checkbox" name="commits[]" 
           id="commits" 
           value="{$filename}"/>
  </td>
  <td id="filename">{$filename}</td>
  <td align="center">{$diff_url}</td>
</tr>

EOT;
            $trs[] = $atr;
        }

        $change_trs = implode("\n", $trs);
        $alt_color_js = wpg_widget_tr_alternate_js("tr[id='change']",
            array("even" => "#FCFCEF"));
        // diff for changes on working directory.
        $diff_dialog_js = wpg_widget_diff_dialog_js();

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
  <tr><th colspan="3">
    <b>Change in Details</b>
  </th></tr>
</tbody></table>
{$patch_js}
{$alt_color_js}
{$diff_dialog_js}

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
 * preparing the jQuery UI dialog to show change difference.
 *
 * @param $has_commit_id distinguish this is a git status 
 *        difference or git log difference.
 */
function wpg_widget_diff_dialog_js($has_commit_id=false) {

    $ajax_url = admin_url("admin-ajax.php");

    $signature = "basePath, fileName";
    $data = <<<EOT
        "base_path" : basePath,
        "filename"  : fileName
EOT;
    if($has_commit_id) {
        $signature = $signature . ", commitId";
        $data = $data . ",\n        \"commit_id\" : commitId";
    }

    $js = <<<EOT
<script type="text/javascript">
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

function changeDiff({$signature}) {

    // load the dialog...
    jQuery("#gitDiff").html("<b>Loading ...</b>");
    jQuery("#gitDiffDialog").dialog("open");

    // get ready the post data.
    var data = {
        "action"    : "wpg_get_git_diff",
{$data}
    };

    jQuery.post("{$ajax_url}", data, function(response) {

        var patch = '<pre style="font-size: 2em; ' +
                    'white-space: pre-wrap; ' + 
                    'text-align: left; overflow: auto; ' + 
                    'max-height:398px">' + response + '</pre>';
        jQuery("#gitDiff").html(patch);
    });
}
</script>

<div id="gitDiffDialog" title="WordPress GitWeb Diff Dialog">
  <div id="gitDiff">
  </div>
</div>
EOT;

    return $js;
}

/**
 *
 */
function wpg_widget_files_patch_js($base_path, 
                                   $commit_id=null,
                                   $max_files=10,
                                   $filecontainer_id='filename',
                                   $table_id='changeset',
                                   $td_colspan=2) {

    $ajax_url = admin_url('admin-ajax.php');

    $commit_id_data = "";
    if($commit_id) {
        $commit_id_data = '"commit_id" : "' . $commit_id . '",';
    }

    $js = <<<EOT
<script type="text/javascript">
jQuery(document).ready(function($) {
    $("td[id='{$filecontainer_id}']").each(function(index) {

        if(index >= {$max_files}) {
            // we only show up to 10 files a one time.
            // TODO: do nothing for now.
            return;
        }

        // console.log($(this).html());
        // send ajax request to get patch for each file.
        var data = {
            "action" : "wpg_get_git_diff",
            {$commit_id_data}
            "base_path" : "{$base_path}",
            "filename" : $(this).html()
        };
        $.post("{$ajax_url}", data, 
               function(response) {
            var last = $("table[id='{$table_id}'] > tbody:last");
            var codeId = 'file' + index;
            last.append('<tr><td colspan="{$td_colspan}">' + 
                        '<pre style="font-size: 1.5em; ' +
                        'white-space: pre-wrap; ' + 
                        'text-align: left;' + 
                        '"><code class="diff"' +
                        'id="' + codeId + 
                        '">' + response + '</code></pre>' +
                        '</td></tr>');
            $('pre code').each(
                function(i, block) {
                    hljs.highlightBlock(block);
                }
            );
        });
    });
});
</script>
EOT;

    return $js;
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
  <td>Commit Action:</td>
  <td>
    <select name="commitaction" id="commitaction">
      <option value="create_ticket">Create New Ticket</option>
      <option value="update_ticket" selected>
        Update Existing Ticket
      </option>
    </select>
    <span id="ticket_input">
      <label>Ticket ID: </label>
      <input type="text" id="ticketid" name="ticketid" size="8"/>
    </span>
  </td>
</tr>
<tr>
  <td colspan="2">
    <input type="submit" name="submit" 
      value="Commit" 
      onclick="return validateCommitForm()"/></td>
</tr>

<script type="text/javascript">
<!--

jQuery.fn.intOnly = function(limit) { 
    jQuery(this).keydown(function(e) { 
        var key = e.charCode || e.keyCode || 0; 
        // Numbers 0-9 (including NumLock) 
        var numbers = new Array(57,56,55,54,53,52,51,50,49,48,96,97,98,99,100,101,102,103,104,105); 
        // Navigation keys: Left Arrow, Right Arrow, Home, End, Delete, Backspace, Tab 
        var navigation = new Array(37,39,36,35,46,8,9); 
        if ( jQuery.inArray(key, numbers) > -1) { 
            if (limit != "undefined" && $(this).val().length < limit) { 
                return true; 
            } else return false; 
        } else if ( jQuery.inArray(key, navigation) > -1 ) { 
            return true; 
        } 
        return false; 
    }); 
}

jQuery(document).ready(function($) {
  $("input#ticketid").intOnly(5);
});

function validateCommitForm() {
  if (checkValue('gitcomment', 'Commit Comment')) {
    return false;
  }
  action = jQuery("select#commitaction").val();
  if (action == "update_ticket") {
    // need make sure the ticket id is valid.
    if (checkValue('ticketid', 'Existing Ticket Id')) {
        return false;
    }
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

jQuery("select#commitaction").change(function() {

    action = this.value;
    if(action == "create_ticket") {
        jQuery("span#ticket_input").hide();
    }

    if(action == "update_ticket") {
        jQuery("span#ticket_input").show();
    }
});
-->
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
    $author = array(
        "gituser" => $context['gituser'],
        "fullname" => $context['user_fullname'],
        "email" => $context['user_email']
    );

    $the_view = <<<EOT
<p>Commit status for Git Repository: <br/>
<b>{$repo}</b> <br />
-- at Branch: <b>{$branch}</b></p>
EOT;

    // TODO: verify the access key.

    $commit_files = wpg_get_request_param('commits');
    $comment = wpg_get_request_param('gitcomment');
    $commit_action = wpg_get_request_param('commitaction');
    $ticket_id = wpg_get_request_param('ticketid');
    if ($commit_files === "") {
        $result = "<b>No file selected for commit! " .
            "Please select at least one file and try again.</b>";
    } else if ($comment === "") {
        $result = "<b>Comment must be provied for a commit! " .
            "Please add some comment and try again.</b>";
    } else {
        // perform the commit now.
        $gitcommit = wpg_perform_commit($base_path, $commit_files,
                                        $comment, $author,
                                        $commit_action, $ticket_id);
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

    // add 1 to skip the last slash '/'
    $pos = strlen($working_folder) + 1;
    $commit_id = $commit_log['commit_id'];
    $base_path = $commit_log['repo_path'];

    $file_trs = array();
    foreach($commit_log['changeset'] as $filename => $status) {
        $diff_url = $status;
        if($status === 'modified') {
            $diff_url = <<<EOT
<a style="cursor: pointer" 
  onclick="javascript: changeDiff('{$base_path}', '{$filename}', '{$commit_id}')"
>{$status}</a>
EOT;
        }
        // one tr for each file
        $file_trs[] = <<<EOT
<tr id="log">
  <td align="center">{$diff_url}</td>
  <td id="filename">{$filename}</td>
</tr>
EOT;
    }

    $file_trs = implode("\n", $file_trs);
    $alt_tr_js = wpg_widget_tr_alternate_js("tr[id='log']",
        array("even" => "#FCFCEF"));
    // we will pass commit id here.
    $diff_dialog_js = wpg_widget_diff_dialog_js(true);
    // the java script to show the patch view...
    $patch_js = wpg_widget_files_patch_js($base_path, $commit_id);
    // generate html safe comment.
    $comment = htmlspecialchars($commit_log['comment']);
    $comment = wpg_auto_link_ticket_id($comment);
    $merge_view = wpg_widget_merge_html($commit_log);

    $changeset = <<<EOT
<table id="changeset"><tbody>
<tr>
  <th>Full ID:</th>
  <td>{$commit_log['commit_id']}</td>
</tr>
<tr>
  <th>Comment:</th>
  <td>
    <pre style="font-size: 1.2em; white-space: pre-wrap;"
    >{$comment}</pre>
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
{$merge_view}
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
<tr>
  <th colspan="2">
    <b>Change in Details</b>
  </th>
</tr>
</tbody></table>
{$patch_js}
{$alt_tr_js}
{$diff_dialog_js}
EOT;

    return $changeset;
}

/**
 * ajax based merge form.
 */
function wpg_widget_merge_form_html($repo_path, $from_branch,
    $from_commit_id, $to_branch, $org_commit_id, $ticket_id) {

    // return message No Merge Available!
    if(!wpg_is_code_reviewer()) {
        // not a code reviewer!
        return "No Merge Performed on <b>" . 
               $to_branch . "</b> Branch!";
    }

    $ajax_url = admin_url("admin-ajax.php");

    $form = <<<EOT
<div id="merge_msg">
  Reference Ticket:
  <input type="text" size="5" name="ticket_id" id="ticket_id"
    value="{$ticket_id}"
  />
  <input id="merge_{$to_branch}" type="button" 
    value="Merge to {$to_branch}"
  />
</div>

<script type="text/javascript" charset="utf-8">
<!--
jQuery("input#merge_{$to_branch}").click(function() {
    var ticketId = parseInt(jQuery("input#ticket_id").val()); 
    if(!jQuery.isNumeric(ticketId) || ticketId <= 0) {
        // reset.
        alert("A valid ticket id is required to perform merge!");
    } else {
        // preparing the ajax request data
        var data ={
            "action"         : "wpg_git_perform_merge",
            "repo_path"      : "{$repo_path}",
            "from_branch"    : "{$from_branch}",
            "from_commit_id" : "{$from_commit_id}",
            "to_branch"      : "{$to_branch}",
            "org_commit_id"  : "{$org_commit_id}",
            "ticket_id"      : ticketId
        };
        // set the mouse cursor to progress...
        jQuery("*").css("cursor", "progress");
        jQuery.post("{$ajax_url}",
            data,
            function(response) {
                res = JSON.parse(response);
                jQuery("div#merge_msg").html(res);
                // set the mouse cursor back to default...
                jQuery("*").css("cursor", "default");
            });
    }
});
-->
</script>
EOT;

    return $form;
}

/**
 * generate the merge history html for the given from and to branches.
 * if the commit exists in to_branch:
 *   return the merge message.
 * if NOT:
 *   return the merge form.
 */
function wpg_widget_merge_history_html($commit_comment, 
                                       $org_commit_id, $merge_path,
                                       $from_branch, $from_commit_id,
                                       $to_branch) {

    // check the commit is merged or not, 2 steps to make sure.
    // 1. grep the the full orginal commit id from the git log
    // 2. grep the first line of commit comment from the git log
    $matches = wpg_git_log_grep($merge_path, $to_branch, 
                                $org_commit_id);
    // we don't match by the first line of comment.
    //if($matches === False) {
    //    // find the fist line of comments.
    //    $comment_lines = explode("\n", trim($commit_comment));
    //    $matches = wpg_git_log_grep($merge_path, $to_branch,
    //                                $comment_lines[0]);
    //}

    // if it is merged, get the commit id 
    // if not, 
    // 1. extract the ticket id from commit comment
    // 2. show the merge form with ticket id.
    // 3. present JavaScript, the merge button.
    if($matches === False) {
        // find the ticket id
        $ticket_id = wpg_extract_ticket_id($org_commit_id, 
                                           $commit_comment);
        // merge the commit id in from branch.
        $merge_html = wpg_widget_merge_form_html($merge_path,
            $from_branch, $from_commit_id, $to_branch, 
            $org_commit_id, $ticket_id);
    } else {
        $merge_html = wpg_merged_msg($to_branch, $matches[0]);
    }

    return $merge_html;
}

/**
 * get ready the merge role for the changeset html.
 * we need the ticket id if we could figure out it from git comments.
 */
function wpg_widget_merge_html($commit_log) {

    // if no merge folder set up, skip it.
    $merge_folder= get_site_option('wpg_merge_folder');
    $merge_path = wpg_get_user_merge_path($merge_folder);
    if(empty($merge_path)) {
        // return empty view.
        return "";
    }

    $dev_branch = get_site_option('wpg_merge_dev_branch');
    $uat_branch = get_site_option('wpg_merge_uat_branch');
    $prod_branch = get_site_option('wpg_merge_prod_branch');

    // check the uat merge (first level merge).
    $merge_html = 
        wpg_widget_merge_history_html($commit_log['comment'], 
                                      $commit_log['commit_id'],
                                      $merge_path,
                                      $dev_branch, 
                                      $commit_log['commit_id'],
                                      $uat_branch);
    // now let's check if UAT merge is done?
    // we will based on the commit id exist on the message or not.
    $pattern = wpg_merged_msg($uat_branch, '([0-9a-fA-F]{7})');
    $pattern = str_replace("/", "\/", $pattern);
    if(preg_match('/' . $pattern . '/', $merge_html, $matches)) {
        // this tells UAT merge is finished.
        $uat_commit_id = $matches[1];
        $prod_merge_html = 
            wpg_widget_merge_history_html($commit_log['comment'],
                                          $commit_log['commit_id'],
                                          $merge_path,
                                          $uat_branch, 
                                          $uat_commit_id,
                                          $prod_branch);
        $merge_html = $merge_html . "<br/>" . $prod_merge_html;
    }
 
    $view = <<<EOT
<tr>
  <th>Merge:</th>
  <td style="background-color: rgb(252, 252, 239);">
    {$merge_html}
  </td>
</tr>
EOT;

    // make sure get back to dev branch.
    // might not need this if the merge folder is separate.
    //shell_exec('git checkout ' . $dev_branch);
    return $view;
}
