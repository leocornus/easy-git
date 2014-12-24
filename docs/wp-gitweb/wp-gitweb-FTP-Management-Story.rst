`wp-gitweb RElease 0.4.0 <wp-gitweb-release-0.4.0.rst>`_ > 
FTP Management Story

Overview
--------

We will introduce an admin page on dashboard for superadmin to 
manage FTP access for a regular user.
The FTP access depends on Pure-FTPd_ FTP server.
The admin page will offer the following functionalities:

1. manage users access information for Pure-FTPd_ server:
   a user's access information includes:user name, access key,
   and chroot home directory
1. randomly generate the access key.
1. autocomplete user name from **wp-users** table.
1. generate Pure-FTPd_ configuration file.
1. generate shell script to mount those chroot folder.

Use Cases
---------

For **superadmin**:

- view all ftp access information in a list.
- able to grant ftp access to a user.
- able to remove ftp access from a user.
- able to update ftp access for a user.
- able to generate random ftp access key for a user.
- ability to generate and update shell script.

Here is a alternative way to execute sudo commands::

  shell_exec ssh user@localhost 'sudo mkdir -v /chroot/user/repo-label'

**Create and Update FTP access**

**Leverage the WPG_USER_REPO_ASSOCIATE**

The repo label will be the folder name in **chroot** folder.
The repo path will be mounted to the **chroot** foler.

Database Schema
---------------

Moved to `Data Structure Story`_.

PHP Functions
-------------

Before define PHP functions, we need figure out what actions/cases:

- add user: create secret key, make the chroot home dir, 
- associate user to a repo: make the chroot repo dir, mount the 
  chroot repo dir to repo path.
- un-associate user to a repo: unmount, remove the chroot repo dir

Ideas
-----

- integrate the pure-ftpd configuration.
- Re-use the table **WPG_USER_REPO_ASSOCIATE** to associate 
  user and FTP access. the **REPO_LABEL** will be the empty symlink
  folder in **/chroot** folder.

Planning
--------

- PHASE ONE: create dashboard to manage the ftp access info,
  basically manage the ftp service table.
  Similar with `Git Repo Management Story`_.

How is it working now?
----------------------

Currently it depends on the manual process:

#. create new db record on ftp service table
#. create the ftp home directory, which is a chroot directory.
#. create empty folder for each access target folder
#. mount the target folder to the empty foler.
#. the empty folder name should be the same with repository
   name. 

.. _Pure-FTPd: https://github.com/jedisct1/pure-ftpd
.. _Git Repo Management Story: ../wp_gitweb_Git_Repo_Management.rst
.. _Data Structure Story: ../wp-gitweb-story-data-structure.rst
