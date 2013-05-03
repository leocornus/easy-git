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
 * the base url to ticket system.
 */
function wpg_get_ticket_base_url() {

    return get_site_option('wpg_ticket_base_url');
}

/**
 * return true if the given user is one of the code reviewers.
 */
function wpg_is_code_reviewer($user_login = null) {

    if($user_login === null) {
        // get current user.
        global $current_user;
        $user_login = $current_user->user_login;
    }
    $reviewers = wpg_get_option_as_array('wpg_code_reviewers');

    return in_array($user_login, $reviewers);
}

/**
 * return Git repositories' root path as the following format.
 * array() {
 *     REPO_LABEL => REPO_PATH,
 * }
 */
function wpg_get_repos_root_path() {

    $roots = wpg_get_option_as_array('wpg_repo_roots');
    $pathes = array();
    foreach($roots as $root) {

        $one_repo = explode(";", $root);
        $pathes[$one_repo[0]] = $one_repo[1];
    }

    return $pathes;
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
 * return all contributors as an array.
 */
function wpg_get_contributors() {

    $all = wpg_get_option_as_array('wpg_active_repos');
    $users = array();
    foreach($all as $repo) {
        $pos = strpos($repo, ";");
        if($pos !== false) {
            // found
            // the first one is user_login.
            $user_login = substr($repo, 0, $pos);
            if(!in_array($user_login, $users)) {
                $users[] = $user_login;
            }
        }
    }

    return $users;
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

    if(is_string($value)) {
        $value = stripslashes($value);
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

    $current_user = wp_get_current_user();
    // we will always using the current user as the commit author.
    $user_fullname = $current_user->display_name;
    $user_email = $current_user->user_email;
    // git user.
    $user = wpg_get_request_param('gituser');
    if($user === "") {
        $user = $current_user->user_login;
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
            $files[$fileName] = wpg_get_status_name(trim($status));
        }
    }
//var_dump($files);

    return $files;
}

/**
 * return full status name for the given status code.
 * Git status code is explained in 
 *   git help status.
 */
function wpg_get_status_name($status_code) {

    // return the code anyway.
    $status = $status_code;
    switch($status_code) {
        case "??":
            // this is an untracked file, we mark as new.
            $status = 'new';
            break;
        case "M":
            $status = 'modified';
            break;
        case "D":
            $status = 'deleted';
            break;
        case "A":
            $status = 'added';
            break;
     }

    return $status;
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
                "comment" => wpg_auto_link_ticket_id($commitComment)
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

    // escape shell arguments,
    // filename might have whitespace, which will break git command.
    $escapedFiles = array();
    foreach($commitFiles as $filename) {
        $escapedFiles[] = escapeshellarg($filename);
    }

    // git add will add new files,
    $commitFilesStr = implode(" ", $escapedFiles);
    $gitadd = shell_exec('git add ' . $commitFilesStr);
    //echo "<p>$gitadd</p>";

    // now let's commit the selected files.
    $cmd = 'git commit -m "' . $comment . 
           '" --author="' . $author["fullname"] . ' <' . 
           $author["email"] . '>' .
           '" ' . $commitFilesStr;
    //echo "<pre>commit command: $cmd<br/>";
    $gitcommit = shell_exec($cmd);
    // need push to git repo.
    shell_exec('git push');

    // set the action hook for after commit.
    // TODO: need make sure the commit is success.
    do_action('wpg_after_perform_commit', $author['gituser'], 
              $comment, $gitcommit); 

    return $gitcommit;
}

/**
 * the default after commit hook to create a minium ticket.
 */
//add_action('wpg_after_perform_commit',
//           'wpg_create_ticket_after_commit', 10, 3);
function wpg_create_ticket_after_commit($reporter, $comment, 
    $gitcommit) {

    // this depends on the wp-trac-client plugin.
    if(function_exists('wptc_create_ticket')) {
    
        // ticket attributes.
        $attrs['reporter'] = $reporter;
        wptc_create_ticket($comment, $gitcommit, $attrs);
    }
}

/**
 * get ready the git difference view.
 */
function wpg_get_git_diff($base_path, $filename, 
                          $ignore_whitespace=false) {

    chdir($base_path);
    $diff_cmd = "git diff " . $filename;
    $diff = htmlentities(shell_exec($diff_cmd));

    // TODO: NOT suppose format the difference here.
    // should just return the difference line by line as an array
    $pre = <<<EOT
<pre style="font-size: 2em; white-space: pre-wrap; 
  text-align: left; overflow: auto; max-height:398px"
>{$diff}</pre>
EOT;

    return $pre;
}

/**
 * return the git log difference for the fiven file.
 */
function wpg_get_git_log_diff($base_path, $filename, $commit_id,
                              $ignore_whitespace=false) {

    chdir($base_path);
    $git_cmd = "git log -1 -w -p " . $commit_id . " " . $filename;
    $diff = htmlentities(shell_exec($git_cmd));

    // TODO: 
    $pre = <<<EOT
<pre style="font-size: 2em; white-space: pre-wrap; 
  text-align: left; overflow: auto; max-height:398px"
>{$diff}</pre>
EOT;

    return $pre;
}

/**
 * return true if the commit id is exist in the given path.
 */
function wpg_is_commit_valid($repo_path, $commit_id) {

    chdir($repo_path);
    $git_cmd = "git rev-list --all | grep ^" . $commit_id;
    $check = shell_exec($git_cmd);
    $pos = strpos($check, $commit_id);

    if($pos === 0) {
        return true;
    } else {
        return false;
    }
}

/**
 * return the details changeset for a commit with the following
 * format:
 *   id => 
 *   author => <a href="mailto: email">Full Name</a>
 *   age => 20 hours ago
 *   comment => why this change
 *   changestat => git log -1 --shortstat --oneline
 *   working folder => /var/www/html/wp-content/themes/
 *   changeset => {
 *       filename => status
 *   }
 */
function wpg_get_commit_changeset($repo_path, $commit_id) {

    chdir($repo_path);

    // change stats.
    $git_cmd = "git log -1 --oneline --shortstat " . $commit_id;
    list($summary, $change_stat) = 
        explode("\n", shell_exec($git_cmd));
    $git_cmd = 'git log -1 --pretty=format:%B ' . $commit_id;
    $commit_comment = shell_exec($git_cmd);

    // the pretty format, check more details using the follwoing:
    // git help log
    $format = '--pretty=format:"%H|%an|%ae|%ad" --relative-date ';
    $git_cmd = 'git log -1 --name-status ' . $format . $commit_id;
    $raw_log = shell_exec($git_cmd);
    $logs = explode("\n", $raw_log);

    // change summary is in the first line
    list($commit_fullid, $author_name, $author_email, 
         $commit_age) = explode("|", $logs[0], 5);
    // the rest are changed files
    $files = array_slice($logs, 1);
    // find the smallest last slash position as the
    // working folder.
    $last_slash = PHP_MAXPATHLEN;
    $working_folder = "";
    $changeset = array();
    // declare filename outside of foreach, so we could use it later.
    foreach($files as $file) {
        if(empty($file)) {
            // skip the empty line.
            continue;
        }
        list($status, $filename) = explode("\t", $file);
        // the position for last /
        $last_slash = min($last_slash, strrpos($filename, "/"));
        $changeset[$filename] = wpg_get_status_name($status);
        // here is the working folder.
        $working_folder = substr($filename, 0, $last_slash);
    }

    // the fine grind log
    $fine_log = array( 
        'repo_path' => $repo_path,
        'commit_id' => $commit_fullid,
        'author_name' => $author_name,
        'author_email' => $author_email,
        'commit_age' => $commit_age,
        'comment' => wpg_auto_link_ticket_id($commit_comment),
        'working_folder' => $working_folder,
        'branch' => wpg_get_current_branch($repo_path),
        'change_stat' => $change_stat,
        'changeset' => $changeset
    );

    return $fine_log;
}
