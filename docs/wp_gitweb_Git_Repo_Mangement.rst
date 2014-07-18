Git Rrepositories Management Design 

Create Table for Active Repositories List

Stories
-------

- `Data Structure Story`_

.. _Data Structure Story: wp-gitweb-story-datastructure.rst

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
:Tools: the column to hold convenient ''edit'' and ''delete'' links.

Both **Label** and **Path** columns are sortable.

There will be only one form for:

- add Git repository
- edit a Git reposityr, 
  including add contributor to a Git repository.

The `jQuery UI Autocomplete Multiple Values`_ will be use
to add contributors to a Git repository.

PHP Functions
-------------

The following functions need update to get value from database.

- wpg_get_active_repos($user_login)
- wpg_get_contributtors()

We also need create the following new functions:

wpg_get_active_repo($repo_id)
  return all details for the given repository, including 
  label, path, and contirbutors.

.. _DataTables: https://github.com/DataTables/DataTablesSrc
.. _jQuery UI Autocomplete Multiple Values: http://jqueryui.com/autocomplete/#multiple
