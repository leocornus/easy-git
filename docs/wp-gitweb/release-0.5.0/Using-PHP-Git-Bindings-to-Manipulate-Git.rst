`wp-gitweb Release 0.4.0 <wp-gitweb-release-0.4.0.rst>`_ > 
Using php-git bindings to manipulate Git repo

Why
---

Performance is the main reason we want to change the way
how we communicate with Git repository.

PHP bindings (php-git_) from libgit2_ is the best Git api now.

php-git research
----------------

The php-git_ binding will be a PHP extension.
Research actions:

- install libgit2_
- install php-git_ extension
- try some short sample php code.

Current Usage
-------------

We are currently using **22** usage of **shell_exec** in our code::

  ./tags.php:272:    $rawBranch = shell_exec('git branch | grep \*');
  ./tags.php:290:    $rawStatus = shell_exec('git status -s .');
  ./tags.php:373:    $gitlog = shell_exec($cmd);
  ./tags.php:409:    $count = shell_exec($cmd);
  ./tags.php:459:    $gitpull = shell_exec('git pull');
  ./tags.php:471:    $gitadd = shell_exec('git add ' . $commitFilesStr);
  ./tags.php:487:    $gitcommit = shell_exec($cmd);
  ./tags.php:489:    shell_exec('git push');
  ./tags.php:507:    $diff = htmlentities(shell_exec($diff_cmd));
  ./tags.php:523:    $diff = htmlentities(shell_exec($git_cmd));
  ./tags.php:535:    $check = shell_exec($git_cmd);
  ./tags.php:565:        explode("\n", shell_exec($git_cmd));
  ./tags.php:567:    $commit_comment = shell_exec($git_cmd);
  ./tags.php:573:    $raw_log = shell_exec($git_cmd);
  ./tags.php:630:    shell_exec('git checkout ' . $branch . '; git pull');
  ./tags.php:634:    $grep_result = shell_exec($git_log);
  ./tags.php:659:    shell_exec('git checkout ' . $from_branch . '; git pull');
  ./tags.php:660:    shell_exec('git checkout ' . $to_branch . '; git pull');
  ./tags.php:668:    $cherry_pick = shell_exec($cmd . $commit_id);
  ./tags.php:674:    shell_exec('git push');

It should be a simple replace...

.. _php-git: https://github.com/libgit2/php-git
.. _libgit2: https://libgit2.github.com/

