**NOTE**
This story is Abandonded, using `FTP Management Story`_ instead.

.. _FTP Management Story: wp-gitweb/wp-gitweb-FTP-Management-Story.rst

create a dashboard management page to manage FTP access.

We need a page to grant FTP access to an regular user.

Use Cases
---------

For **superadmin**:

- view all ftp access information in a list.
- able to grant ftp access to a user.
- able to remove ftp access from a user.
- able to update ftp access for a user.
- able to generate random ftp access key for a user.
- ability to generate and update shell script.

Database Design
---------------

We need create a table to manage user and target FTP directory.
Maybe add some columns on the existing ftp service table.

Ideas
-----

- integrate the pure-ftpd configuration.
- 

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

.. _Git Repo Management Story: wp_gitweb_Git_Repo_Management.rst
