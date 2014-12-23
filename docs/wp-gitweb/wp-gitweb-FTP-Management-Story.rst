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

**Create and Update FTP access**

**Leverage the WPG_USER_REPO_ASSOCIATE**

The repo label will be the folder name in **chroot** folder.
The repo path will be mounted to the **chroot** foler.

Ideas
-----

- integrate the pure-ftpd configuration.
- 

Planning
--------

- PHASE ONE: create dashboard to manage the ftp access info,
  basically manage the ftp service table.
  Similar with `Git Repo Management Story`_.

Database Schema
---------------

WPG_FTP_ACCESS

:SERVICE_ID: auto increase id.
:USER_LOGIN: wordpress user login from wp_users table.
:SECRET_KEY: password to access FTP server.
:FTP_HOME_DIR: the home directory for FTP access.

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
