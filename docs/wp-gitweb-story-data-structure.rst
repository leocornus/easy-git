< `Active Git Repository Management Design Story 
<wp_gitweb_Git_Repo_Management.rst>`_

This story is all about data structure designed for wp-gitweb plugin,
which including:

- database schema
- main data structure

Database Schema
---------------

Create table or tables to manage the active respsitories list.
2 tables might be better choice:

WPG_ACTIVE_GIT_REPOS

:REPO_ID: unique id
:REPO_LABEL: label the a git repository
:REPO_PATH: full absolute path to the git repository.

WPG_USER_REPO_ASSOCIATE

:ID: unique id
:USER_LOGIN: user_login from wp_users table
:REPO_ID: REPO_ID from table WPG_ACTIVE_GIT_REPOS.

WPG_FTP_ACCESS

:SERVICE_ID: auto increase id.
:USER_LOGIN: wordpress user login from wp_users table.
:SECRET_KEY: password to access FTP server.
:FTP_HOME_DIR: the home directory for FTP access.
:ACTIVATE_TIME: the activation timestamp.

There will be a special user_login for some sandbox repositories.
For example: **ALL-USER**.

Some constrains
~~~~~~~~~~~~~~~

here some scenarios:

- user will gain FTP access by adding a record in table
  **WPG_FTP_ACCESS**.
- user will associate with a repository by adding a record in table
  **WPG_USER_REPO_ASSOCIATE**, (USER_LOGIN, REPO_ID)
- BY NOW, user can review the change status on GitWeb page, 
  and can commit changes to the repo.
- As long as a repo is assigned to a user, 
  this folder will be created: **FTP_HOME_DIR/REPO_LABEL**.
  And it will be mount to **REPO_PATH**.
- BY NOW, user and the ftp access this this repository.

Repository Object in Application
--------------------------------

Here is an example::

  $repo = array(
    'repo_id' => 1,
    'repo_lable' => 'the repo name',
    'repo_path' => 'full absolute path',
    'repo_conbtributors' => 'sean, jerry, jonh'
  );

The **repository** object will be used in many PHP functions.

PHP Functions
-------------

We will have the following PHP functions to manipulate 
Git repos and contributors.

wpg_get_all_repos()
  return all active Git repository as a array including contributors.

wpg_get_all_contributors()
  return all contributors for all active Git repository, 
  an one dimensional array.

wpg_get_repo_contributors($repo_id)
  get all contributors associated to the given repository 
  in an array format.

wpg_replace_repo($repo_label, $repo_path, $repo_id=0)
  create a new repository if the repo_id is 0. Otherwise, 
  replace the existing one.

wpg_remove_repo($repo_label)
  remove the given repository and all its contributor associations.

wpg_associate_users_to_repo($users, $repo_id, $replace=true)
  associate a list of users to the given repository

wpg_get_repo($repo_label)
  return all info for the given repository label, 
  in the repository object format.

wpg_get_contributor_repos($user_login)
  return all repositories associated to a contributor
