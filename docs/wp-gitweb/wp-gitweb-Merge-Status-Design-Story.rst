`wp-gitweb RElease 0.4.0 <wp-gitweb-release-0.4.0.rst>`_ > 
Design Story for displaying the merge status for each commit.

Actions
-------

- create a separate folder for checking merge status.
- this folder will be read only for all regular users and 
  anonymous users.
- jQuery client to query each commit and display the result.
- call back PHP fucntions to execute git query and reply the 
  result to jQuery client.


