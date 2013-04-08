<?php

/**
 * return the given option's values as an array
 */
function wpg_get_option_as_array($option_name) {

    $opt = get_site_option($option_name);
    // replace to make sure.
    $opt = str_replace("\r\n", "\n", $opt);
    return explode("\n", $opt);
}

/**
 * retrun the a list of ignore files as an array
 */
function wpg_get_ignore_files() {

    return wpg_get_option_as_array('wpg_ignore_files');
}

function wpg_get_ignore_patterns() {

    return wpg_get_option_as_array('wpg_ignore_patterns');
}

/**
 * return active repositories as an array with following
 * structure:
 *
 * array() {
 *     REPO_LABEL => REPO_PATH
 * }
 */
function wpg_get_active_repos($user_name=null) {

    if($user_name === null) {
        // find the current login user.
        global $current_user;
        $user_name = $current_user->user_login;
        // TODO: what id current user is not loged in?
    }

    $all = wpg_get_option_as_array('wpg_active_repos');
    $myRepos = array();
    foreach($all as $repo) {
        // user name is the beginning.
        $pos = strpos($repo, $user_name);
        if($pos === 0) {
            // on the one starts with user name.
            $theOne = array_slice(explode(";", $repo), 1);
            $myRepos[$theOne[0]] = $theOne[1];
        }
    }

    return $myRepos;
}

/**
 * the clean way to get a http request parameter's value.
 */
function wpg_get_request_param($param) {

    // try to find the selected theme name
    if (array_key_exists($param, $_POST)) {
        $value = $_POST[$param];
    } elseif (array_key_exists($param, $_GET)) {
        $value = $_GET[$param];
    } else {
        $value = '';
    }

    return $value;
}

/**
 * get ready the request context from $_POST and $_GET
 * it will return an array with the follow structure:
 * 'paramname' => 'value'
 */
function wpg_request_context() {

    $context = array();

    $repo = wpg_get_request_param('repo');
    $context['repo'] = $repo;

    // git user.
    $user = wpg_get_request_param('gituser');
    if($user === "") {
        global $current_user;
        $user = $current_user->user_login;
        $user_fullname = $current_user->display_name;
        $user_email = $current_user->user_email;
    }
    $context['gituser'] = $user;
    $context['user_email'] = $user_email;
    $context['user_fullname'] = $user_fullname;

    // if we have the theme name, get ready the status.
    if ($repo !== '') {
        $base_path = wpg_get_base_path($user, $repo);
        $branch = wpg_get_current_branch($base_path);
    } else {
        $base_path= '';
        $branch = '';
    } 
    $context['base_path'] = $base_path;
    $context['branch'] = $branch;

    // the submit action.
    $context['action'] = wpg_get_request_param('submit');

    return $context;
}

/**
 * return base absolute path for the given user's given 
 * repository.
 */
function wpg_get_base_path($user, $repo) {

    $repos = wpg_get_active_repos($user);
    $base_path = $repos[$repo];

    return $base_path;
}

/**
 * from current active branch.
 */
function wpg_get_current_branch($base_path) {

    chdir($base_path);
    // using the short format from git output
    $rawBranch = shell_exec('git branch | grep \*');
    // php substr starts from 0
    return substr($rawBranch, 2);
}

/**
 * generate a list of chagnes for the given theme.
 */
function wpg_get_change_list($base_path) {

    // prepare a list of files as an array, 
    // with the following format.
    // filename => status
    // status will be the readable status:
    // new, modified, deleted, etc

    chdir($base_path);
    // using the short format from git output
    $rawStatus = shell_exec('git status -s .');
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
        if (($fileName !== '') && wpg_is_good_file($fileName)) {
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
 * generate the diff url for the given file 
 * based on the status.
 */
function wpg_get_diff_url($base_path, $filename, $status) {

    if ($status === 'modified') {

        return '<a href="gitdiff.php?repo=' . $repo . 
               '&file=' . $filename . 
               '&ignorespace=yes">' . $status . '</a>';

    } else {
        return $status;
    }
}


/**
 * return the commit log as array list
 */
function wpg_get_log_list($base_path) {

    chdir($base_path);
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
            $commitLogUrl = 'commit?id=' . $commitId;
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
 * is good file to commit? check if the given file is 
 * one of the ignore 
 * files.
 */
function wpg_is_good_file($file) {

    // using the global ignoreFiles;
    $ignoreFiles = wpg_get_ignore_files();
    $ignorePatterns = wpg_get_ignore_patterns();

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
 * perform commit for the commit files based on the status on 
 * change files.
 * We will do the following work on different status.
 * - sync with server by perform git pull
 * - if there is new files, add them.
 * - perform git commit.
 * - perform git push
 *
 * the author has to be format like:
 * Sean Chen <sean.chen@example.com>
 */
function wpg_perform_commit($base_path, $commitFiles, 
                            $comment, $author) {

    chdir($base_path);

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

