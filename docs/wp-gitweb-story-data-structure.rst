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


