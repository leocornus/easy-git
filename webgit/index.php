<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <title>Web Git</title>
  <script type="text/javascript" src="js/webgit.js"></script>
</head>
<body>

<?php
require_once('config.php');
require_once('gitfuncs.php');

// load the request context
$context = requestContext();
// default values for the following:
// repo for the repository name.
$repo = $context['repo'];
//// action: check status or check logs
$action = $context['action'];
//// a list of changed file, result from git status
$changes = $context['changes'];
?>

<form name="repoform" method="POST" action="index.php">
  Pleae select the Git repository: 
  <select name="repo" id="repo">

<?php 
// preparing the repository selection options.

// this the empty entry.
if ($repo === '') {
    echo "<option selected></option>";
} else {
    echo "<option></option>";
}

// $activeRepos is defined in config.php.
foreach (array_keys($activeRepos) as $repoName) {
    if ($repo === $repoName) {
        echo "<option selected>";
    } else {
        echo "<option>";
    }
    echo "$repoName</option>";
}
?>

  </select>
  <br/>
  <input type="submit" name="submit" value="Check Status"/>
  <input type="submit" name="submit" value="Check Logs"/>

  <p>
<?php
if ((!empty($action)) && empty($repo)) {

    // we will reject any action without repo select.
    echo "<h2>Please select a Git repository!</h2>";
    die;
}

if ($action === "Check Status") {
?>

    <h2>Change status for Git Repository: 
        <?php echo $repo; ?></h2>
<?php
    if (count($changes) <= 0) {
        echo "nothing to commit (working directory clean)";
    } else {
        // preparing the selection check box.
        // present as a table.
?>

        <table border="1"><tbody>
          <tr>
            <th><input type="checkbox" name="toggle" 
                 onclick="toggleSelect()"/></th>
            <th>File Name</th>
            <th>Status</th>
          </tr>
<?php
        foreach ($changes as $filename => $status) {
?>
          <tr>
            <td align="center">
              <input type="checkbox" name="commits[]" 
                     id="commits" 
                     value="<?php echo $filename; ?>"/>
            </td>
            <td><?php echo $filename; ?></td>
            <td align="center">
              <?php
                  // need the diff url for modified files.
                  echo generateDiffUrl($repo, $filename, 
                                       $status); 
              ?>
            </td>
          </tr>
<?php
        } //end foreach
        // preparing the commit form.
?>
          <tr><td colspan="3">
            Please fill out the following form to commit 
            the selected files to Git repository.<br/>
            <b>All Fields are REQUIRED</b>.
          </td></tr>
          <tr><td colspan="3">
            <table><tbody>
            <tr>
              <td>Commit Author Name:</td>
              <td><input type="text" name="authorname"/></td>
            </tr>
            <tr>
              <td>Commit Author Email:</td>
              <td><input type="text" name="authoremail"/></td>
            </tr>
            <tr>
              <td>Commit Access Key:</td>
              <td><input type="password" 
                         name="accesskey"/></td>
            </tr>
            <tr>
              <td>Commit Comment:</td>
              <td><input type="text" name="comment" 
                         size="80"/></td>
            </tr>
            <tr>
              <td colspan="2">
                <input type="submit" name="submit" 
                  value="Commit" 
                  onclick="return validateCommitForm()"/></td>
            </tr>
            </tbody></table>
          </td></tr>
        </tbody></table>
<?php
    } // end else
} // end action Check Status.

if ($action === 'Commit') {
?>

    <h2>Commit status for Git Repository: 
        <?php echo $repo; ?></h2>

<?php

    // try to find the selected repository name
    if (array_key_exists('accesskey', $_POST)) {
        $accessKey = $_POST['accesskey'];
    } else {
        $accessKey = '';
    }

    if (array_key_exists('commits', $_POST) === false) {
        echo "<b>No file selected for commit! Please select files that you want to commit</b>";
    // verify the access key based on the repository.
    } elseif (! isAuthorized($repo, $accessKey)) {
        // wrong access key
        echo "<b>Wrong access key! Please provide right access key!</b>";
    } else {
        // perform the commit.
        $comment = $_POST['comment'];
        $authorName = $_POST['authorname'];
        $authorEmail = $_POST['authoremail'];
        $author = $authorName . " <" . $authorEmail . ">";
        $commitFiles = $_POST['commits'];
        $gitcommit = performCommit($repo, $commitFiles, 
                                   $comment, $author);
        // show the raw commit result:
        echo "<pre>" . htmlentities($gitcommit) . "</pre>";
    }
} // end action Commit

if ($action === 'Check Logs') {
    // generate the change logs list.
?>

    <h2>Commit Logs for Git Repository: 
        <?php echo $repo; ?></h2>

<?php
    $logs = logList($repo);
?>

    <table border = "1" width="90%"><tbody>
      <tr><th width="60">Commit</th><th width="100">Author</th>
      <th width="80">Date</th><th>Comment</th></tr>
<?php
    foreach ($logs as $log) {
?>
      <tr>
        <td><a href='<?php echo $log["url"]; ?>'>
              <?php echo $log["id"]; ?></a></td>
        <td><?php echo $log["email"]; ?></td>
        <td><?php echo $log["date"]; ?></td>
        <td><?php echo $log["comment"]; ?></td>
      </tr>
<?php
    } //end foreach $logs
?>
    </tbody></table>

<?php
} // end action Check Logs
?>
  </p>

</body>
</html>
