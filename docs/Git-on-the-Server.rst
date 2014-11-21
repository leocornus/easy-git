A Quick memo for setting up Git on server.

Protocols
---------

- local
- http
- ssh
- git

Local Repository
----------------

Solutions
---------

fast solution for local:

- using local protocol for dev
- using ssh read-only for uat and prod
- using ssh read-only for ci server

Git Switch Remote
-----------------

switch remote is as simple as::

  git remote set-url origin /local/path/to/git/repo.git
  git remote -v

Migration Actions
-----------------

- git clone --bare REMOTE_URL

.. _Git on the Server: http://git-scm.com/book/en/v2/Git-on-the-Server-The-Protocols
