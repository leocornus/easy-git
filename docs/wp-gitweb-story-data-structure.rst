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

There will be a special user_login for some sandbox repositories.
For example: ALL-USER

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

We will have the following PHP functions to manipulate Git repos and contributors.

wpg_get_all_repos()
  return all active Git repository as a array including contributors.

wpg_get_all_contributors()
  return all contributors for all active Git repository, an one dimensional array.

wpg_get_repo_contributors($repo_id)
  get all contributors associated to the given repository in an array format.

wpg_replace_repo($repo_label, $repo_path, $repo_id=0)
  create a new repository if the repo_id is 0. Otherwise, replace the existing one.

wpg_associate_users_to_repo($users, $repo_id, $replace=true)
  associate a list of users to the given repository

wpg_get_repo($repo_label)
  return all info for the given repository label, in the repository object format.

wpg_get_contributor_repos($user_login)
  return all repositories associated to a contributor
