<?php

// the sample configuration files for webgit application.

// a list of files / folders that can be ignored for 
// Git repository. 
// Typically, it includes config files, data files,
// and images.
$ignoreFiles = array('./', 
                     'wp-config.php',
                     'avatars',
                     'wiki/LocalSettings.php',
                     'wiki/images',
                     'wiki/cache',
                     'wp-content/blogs.dir',
                     'wp-content/cache'
                    );

// regex pattern for ignore files.
$ignorePatterns = array('/^(wp-content\/plugins\/buddypress).*/'
                       );

// a list of active repositories.
$activeRepos = array();

// using the following pattern:
//$activeRepos['REPO_NAME'] = 
//    array('USER_NAME', 'REPO_BASE_PATH', 'ACCESS_KEY');

$activeRepos['test.project.com-widget'] = 
    array('sean', '/full/path/to/test/project/widget',
          'a md5 string or somthing like that');
?>
