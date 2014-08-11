FTP Management Story

Overview
--------

We will introduce an admin page on dashboard for superadmin to 
manage FTP access for a regular user.
The FTP access depends on Pure-FTPd_ FTP server.
The admin page will offer the following functionalities:

1. manage users access information for Pure-FTPd_ server:
   a user's access information includes:user name, access key,
   and chroot directory
1. randomly generate the access key.
1. autocomplete user name from **wp-users** table.

.. _Pure-FTPd: http://www.pureftpd.org/project/pure-ftpd
