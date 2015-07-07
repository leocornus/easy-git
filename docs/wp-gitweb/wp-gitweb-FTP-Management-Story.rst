`wp-gitweb RElease 0.4.0 <wp-gitweb-release-0.4.0.rst>`_ > 
FTP Management Story

.. contents:: Table of Contents
    :depth: 5

Overview
--------

We will introduce an admin page on dashboard for superadmin to 
manage FTP access for a regular user.
The FTP access depends on Pure-FTPd_ FTP server.
The admin page will offer the following functionalities:

#. manage users access information for Pure-FTPd_ server:
   a user's access information includes:user name, access key,
   and chroot home directory
#. randomly generate the access key.
#. autocomplete user name from **wp-users** table.
#. generate Pure-FTPd_ configuration file.
#. generate shell script to mount those chroot folder.

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

Code Memo
---------

**file_exists($path)** to check if the directory exists?

here is the logic to create chroot folder and mount to repo path::

  if ftp_folder is exist
    execute mount -l | grep ftp_folder
    if there is output (currently mounted)
      get the source_path
      if the source_path != repo_path
        unmount the current one
      else (already mounted to the same folder)
        continue to next one.
    else (no output, do nothing here.)
  else (not exist, create new one)
    sudo mkdir ftp_folder
  sudo mount -v --bind repo_path ftp_folder

Possible functions:

- wpg_mount_source($path), return $source_path for the given
  $path.

.. _Pure-FTPd: https://github.com/jedisct1/pure-ftpd
.. _Git Repo Management Story: ../wp_gitweb_Git_Repo_Management.rst
.. _Data Structure Story: ../wp-gitweb-story-data-structure.rst
