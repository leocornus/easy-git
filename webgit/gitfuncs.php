<?php

require_once('config.php');

/**
 * get ready the request context from $_POST and $_GET
 * it will return an array with the follow structure:
 * 'paramname' => 'value'
 */
function requestContext() {

    $context = array();

    // try to find the selected theme name
    if (array_key_exists('repo', $_POST)) {
        $repo = $_POST['repo'];
    } elseif (array_key_exists('repo', $_GET)) {
        $repo= $_GET['repo'];
    } else {
        $repo = '';
    }
    $context['repo'] = $repo;

    // if we have the theme name, get ready the status.
    if ($repo !== '') {
        $changes = changeList($repo);
    } else {
        $changes = '';
    } 
    $context['changes'] = $changes;

    // find out the action, based on submit button's value.
    if (array_key_exists('submit', $_POST)) {
        $action = $_POST['submit'];
    } elseif (array_key_exists('submit', $_GET)) {
        $action = $_GET['submit'];
    } else {
        $action = '';
    }
    $context['action'] = $action;

    return $context;
}

/**
 * verify the access key
 */
function isAuthorized($repo, $accessKey) {

    global $activeRepos;
    $key = $activeRepos[$repo][2];
    return ($key === $accessKey);
}

/**
 * generate a list of chagnes for the given theme.
 */
function changeList($repo) {

    // we will use the activeRepos as global
    global $activeRepos;
    $basePath = $activeRepos[$repo][1];
//echo $basePath;

    // prepare a list of files as an array, 
    // with the following format.
    // filename => status
    // status will be the readable status:
    // new, modified, deleted, etc

    chdir($basePath);
    // using the short format from git output
    $rawStatus = shell_exec('git status -s .');
//echo '<pre>';
//echo $rawStatus;
//echo '</pre>';
    // split by newline, this is wired! have to use "\n"
    $rawFiles = explode("\n", $rawStatus);
    $files = array();
    foreach ($rawFiles as $file) {
        if ($file === '') {
            // skip the empty line.
            continue;
        }
        // the status will be the short-format status.
        // XY PATH1 -> PATH2
        $status = substr($file, 0, 2);
        $fileName = substr($file, 3);
        // we will skip the ignored files.
        if (($fileName !== '') && isGoodFile($fileName)) {
            // now let's check the git short format status.
            if ($status === '??') {
                // this is an untracked file.
                $files[$fileName] = 'new';
            } else if ($status === ' M') {
                // this is a changed file.
                $files[$fileName] = 'modified';
            } else if ($status === ' D') {
                $files[$fileName] = 'deleted';
            } else {
                $files[$fileName] = $status;
            }
        }
    }
//var_dump($files);

    return $files;
}

/**
 * is good file to commit? check if the given file is 
 * one of the ignore 
 * files.
 */
function isGoodFile($file) {

    // using the global ignoreFiles;
    global $ignoreFiles;
    global $ignorePatterns;

    if (in_array($file, $ignoreFiles)) {
        return false;
    }

    foreach ($ignorePatterns as $pattern) {

        if(preg_match($pattern, $file) === 1) {

            return false;
        }
    }

    return true;
}

/**
 * return a list of change logs from git log 
 * raw output.
 */
function logList($repo) {

    // using the global variables
    global $activeRepos;
    $basePath = $activeRepos[$repo][1];

    chdir($basePath);
    // check details by using the following command:
    // git help log
    // %ae for author email
    // %an for author name
    $gitlog = shell_exec('git log --pretty=format:"%h|%an|%ae|%ad|%s" --date=short .');
    $commits = explode("\n", $gitlog);
    $logs = array();
    foreach ($commits as $commit) {

        if($commit !== '') {

            list($commitId, $authorName, $commitEmail, 
                 $commitDate, $commitComment) = 
              explode("|", $commit, 5);
            // we need theme in the url, so we could come back.
            $commitLogUrl = 'gitlog.php?repo=' . 
                            $repo. '&commit=' . 
                            $commitId;
            $mailto = '<a href="mailto:' . $commitEmail .
                      '">' . $authorName . '</a>';
            // append a new log entry.
            $logs[] = array(
                "id" => $commitId,
                "email" => $mailto,
                "date" => $commitDate,
                "url" => $commitLogUrl,
                "comment" => $commitComment
                );
        }
    }

    return $logs;
}

/**
 * perform commit for the commit files based on the status on change files.
 * We will do the following work on different status.
 * - sync with server by perform git pull
 * - if there is new files, add them.
 * - perform git commit.
 * - perform git push
 *
 * the author has to be format like:
 * Sean Chen <sean.chen@ontario.ca>
 */
function performCommit($repo, $commitFiles, 
                       $comment, $author) {

    global $activeRepos;
    $basePath = $activeRepos[$repo][1];
    chdir($basePath);

    // pull the latest from git repository.
    $gitpull = shell_exec('git pull');
    //echo "<p>$gitpull</p>";

    // git add will add new files,
    $commitFilesStr = implode(" ", $commitFiles);
    $gitadd = shell_exec('git add ' . $commitFilesStr);
    //echo "<p>$gitadd</p>";

    // now let's commit the selected files.
    $cmd = 'git commit -m "' . $comment . 
           '" --author="' . $author . 
           '" ' . $commitFilesStr;
    //echo "<pre>commit command: $cmd<br/>";
    $gitcommit = shell_exec($cmd);
    // need push to git repo.
    shell_exec('git push');

    return $gitcommit;
}

/**
 * generate the diff url for the given file 
 * based on the status.
 */
function generateDiffUrl($repo, $filename, $status) {

    if ($status === 'modified') {

        return '<a href="gitdiff.php?repo=' . $repo . 
               '&file=' . $filename . 
               '&ignorespace=yes">' . $status . '</a>';

    } else {
        return $status;
    }
}

?>
