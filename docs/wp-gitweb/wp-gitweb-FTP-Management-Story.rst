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

**Create and Update FTP access**

**Leverage the WPG_USER_REPO_ASSOCIATE**

The repo label will be the folder name in **chroot** folder.
The repo path will be mounted to the **chroot** foler.

Database Schema
---------------

WPG_FTP_ACCESS

:SERVICE_ID: auto increase id.
:USER_LOGIN: wordpress user login from wp_users table.
:SECRET_KEY: password to access FTP server.
:FTP_HOME_DIR: the home directory for FTP access.

.. _Pure-FTPd: https://github.com/jedisct1/pure-ftpd
