Git Rrepositories Management Design Doc

Create Table for Active Repositories List
-----------------------------------------

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

Git Repositories Admin Page
---------------------------

This will be an admin settings page to 

- a list of all active Git repositories
- all contributors will list under a repository.
- add new git repository
- remove a git repository
- add a contributor for a Git repository
- remove a contributor from a Git repository

We will use DataTables_ to show the list of active repositories.
Here are the 4 columns:

:ID: the repositories id.
:Label: the repository label
:Path: Full absolute path to the repository.
:Contributors: a list of user who have access to this repository.

Both **Label** and **Path** columns are sortable.

There will 2 forms for:

- add Git repository
- edit a Git reposityr, including add user to a Git repository

PHP Functions
-------------

The following functions need update to get value from database.

- wpg_get_active_repos($user_login)
- wpg_get_contributtors()

We also need create the following new functions:

-  

.. _DataTables: https://github.com/DataTables/DataTablesSrc
