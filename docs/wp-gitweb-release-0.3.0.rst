Release 0.3.0
-------------

The major update for the release 0.3.0 is using database for 
active Git repositories management.

Here is the design story for the new Gir repository management:
`<wp_gitweb_Git_Repo_Management.rst>`_.

Here are some changes:

- new repository management dashbord page.
- the **wpg_active_repos** option is not in use any more.
- using `jQuery DataTables`_ for git Repos list.
- using `jQuery UI Autocomplete`_ for selecting contributors.

.. _jQuery DataTables: https://github.com/seanchen/DataTablesSrc
.. _jQuery UI Autocomplete: http://jqueryui.com/autocomplete/#multiple
