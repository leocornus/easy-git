The design scratch pad for WordPress_ plugin wp_gitweb

Current Options
---------------

Here is a list of site options that we are using now:

wpg_ignore_files
  A list of files to ignore when we review the change list.

wpg_ignore_patterns
  A list of regular express patterns to ignore when
  we review the change list.

wpg_code_reviewers
  A list of user login who will have the role **code reviewer**.
  The code reviewer will be able to review all contributors 
  change and perform code merge if the associated merge folder 
  is set properly.

wpg_active_repos
  A list of active Git repositories. One repos each line with
  the following format::

    USER_LOGIN;REPO_LABEL;REPO_PATH

wpg_repo_roots
  A list of absolute path to Git repository's root folder.

wpg_ticket_base_url
  The base URL to ticket system. This will be used for generate 
  the herf link to a ticket id, which will be mentioned in 
  commit comments for commit notes.

.. _WordPress: http://www.wordpress.org
