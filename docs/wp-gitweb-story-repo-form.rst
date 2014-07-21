< `Active Git Repository Management Design Story 
<wp_gitweb_Git_Repo_Management.rst>`_

The `data structure <wp-gitweb-story-data-structure.rst>`_ for 
a Active Git Repository is very simple and straight forward. 
We will use only one form to create and update Git repos.

jQuery UI Autocomplete Multiple Values
--------------------------------------

The repository contributor field will use jQuery UI Autocomplete input
field with multiple values support.
We will have the following component for this feature:

- load the jquery ui js libs using **admin_enqueue_scripts** action.
- AJAX request PHP callback function
- PHP function to generate JavaScript client. 
  The js client will attach jQuery autocomplete action to 
  the contributor input field.
- the js client will prepare the multiple value support for the 
  jQuery UI autocomplete input field.
