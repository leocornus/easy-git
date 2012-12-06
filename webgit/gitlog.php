<?php

require_once('config.php');

$repo = $_GET['repo'];
if (empty($repo)) {
    // 
    echo "<h2>Pleae specify the Git repository</h2>";
    die;
}
$basePath = $activeRepos[$repo][1];
// change to the base directory
chdir($basePath);

$commitId = $_GET['commit'];
$file = $_GET['file'];

$commitHref = "gitlog.php?repo=" . $repo . 
              "&commit=" . $commitId;

if (empty($file)) {
    // file is not specified, show the whole change 
    // list for this commit.
?>

    <h2>Details Log for Commit: <?php echo $commitId; ?></h2>
    <p>
    <a href="index.php?repo=<?php echo $repo?>&submit=Check%20Logs">
      Back to Commit Logs</a>
    </p>

<?php
    // the --relative option will return the file list 
    // with relative path.
    $rawLog = shell_exec('git log -1 --name-status --relative ' . 
                         $commitId);
    $commitLog = htmlentities($rawLog);
    //echo "<pre>$commitLog</pre>";

    // preparing the URL for each file.
    $commitEntries = explode("\n", $commitLog);
    $commitOutput = array();
    foreach ($commitEntries as $entry) {

        if(preg_match('/^[M|A]\t/', $entry) === 1) {
    
            $href = $commitHref . "&file=";
            //$newEntry = str_replace('var/www/html/', "", $entry);
            $newEntry = 
                preg_replace("/([M|A]\t)(.*)/", 
                             "\\1<a href='" . $href . "\\2'>\\2</a>", 
                             $entry);
            $commitOutput[] = $newEntry;
        } else {
            $commitOutput[] = $entry;
        }
    }
    echo "<pre>" . implode("\n", $commitOutput) . "</pre>";
} else {
    // file is specified.
    // show the details for a file.
?>

    <h2>Changes for file: <?php echo $file; ?> <br/>
    on commit: <?php echo $commitId; ?></h2>

    <p>
    <a href="<?php echo $commitHref; ?>">
    Change Log for Commit: <?php echo $commitId; ?></a>
    | 
    <a href="index.php?repo=<?php echo $repo; ?>&submit=Check%20Logs">
    Back to Commit Logs</a>
    </p>

<?php
    $gitchange = htmlentities(shell_exec('git log -1 -w -p ' . $commitId . ' ' . $file));
    echo "<pre>$gitchange</pre>";
}
?>
