<?php

require_once('config.php');

$fileName = $_GET['file'];
$repo = $_GET['repo'];
if (empty($repo)) {
    echo "<h2>Please specify the Git Repository</h2>";
} else if (empty($fileName)) {
    echo "<h2>Please specify the file name</h2>";
} else {
    echo "<h2>Changes for file: $fileName</h2>";

    $basePath = $activeRepos[$repo][1];
    chdir($basePath);

    $diffcmd = 'git diff ';
    $ignoreSpace = $_GET['ignorespace'];
    if ($ignoreSpace === 'yes') {
        // show changes without spaces change.
        genDiffUrl($repo, $fileName, true);
        $diffcmd = $diffcmd . ' -b ' . $fileName;
    } else {
        // show different with spaces change.
        genDiffUrl($repo, $fileName, false);
        $diffcmd = $diffcmd . $fileName;
    }

    $gitdiff = htmlentities(shell_exec($diffcmd));
    echo "<pre>$gitdiff</pre>";
}

function genDiffUrl($repo, $filename, 
                    $ignoreSpaces = false) {

    echo "<p>";
    if ($ignoreSpaces) {
        echo "<a href='gitdiff.php?repo=" . $repo . 
             "&file=" . $filename . 
             "&ignorespace=no'>Diff with Spaces Change" .
             "</a> | ";
        echo "<b>Diff without Spaces change</b>";
    } else {
        echo "<b>Diff with Spaces change</b> | ";
        echo "<a href='gitdiff.php?repo=" . $repo . 
             "&file=" . $filename . 
             "&ignorespace=yes'>Diff without Spaces Change" .
             "</a>";
    }
    echo " | <a href='index.php?repo=" . $repo . 
         "&submit=Check%20Status'>Back to Status Page</a>";
    echo "</p>";
}
?>
