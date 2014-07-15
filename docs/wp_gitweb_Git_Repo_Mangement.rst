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

Git Repositories Admin Page
---------------------------

This will be an admin settings page to 

- list all active Git repositories
- view contributors to a Git repo
- view Git repos for a contributor
- add new git repositor
- add contributor to a git repository

PHP Functions
-------------

Currently we are using the following functions to get 

- wpg_get_active_repos($user_login)
- wpg_get_contributtors()
