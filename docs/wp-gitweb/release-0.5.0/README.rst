Release 0.5.0
-------------

Chage logs

- `Change Logs View Redesign <Change-Logs-Redesign.rst>`_

  - Ability to download repository snapshot from commit logs view,
    for any commit.
  - Allow anonymous user to view all change logs
  - Allow anonymous user to download snapshots
  - auto complete contributor name for code reviewer.
  - auto complete repository label.
  - Allow users to browse all files for a repository. 

- `FTP Management Tune Up <ftp-management-tune-up.rst>`_ 
  more utilities for the FTP management page:

  - sync button to sync the mount between repo PATH ane ftp folder.
    This is necessary for recorvering from system reboot.
  - ability to random generate secret_key,
  - ability email secret_key to user.
  - hide or encrypt secret key.
  - ability to handle umount and rm if user is not set as contributor.
    basically the house-keeping work.

- integration with `leocornus ci`_

  - just simple configurable option to like to the CI build result page.
  - adding the CI column on change logs to show the CI build status, 
    and link to CI build result page. for each commit.

- `Using php-git binding to manipulate Git repo 
  <Using-PHP-Git-Bindings-to-Manipulate-Git.rst>`_

.. _leocornus ci: https://github.com/leocornus/leocornus.recipe.ci
