`wp-gitweb RElease 0.4.0 <wp-gitweb-release-0.4.0.rst>`_ > 
Design Story for display the details path on change set view

Show the details patch in the commit changeset view will
be very helpful for code review.

Git Patch Options
-----------------

We will mainly based on Git log patch to generate 
the difference view for each commit.
Here some Git command line samples. 

Show details message for one commit::

  $ git log --patch -1 [COMMIT_ID]

Whow only the body for a commit::

  $ git log --patch -1 --pretty=format:%b [COMMIT_ID]

Syntax Highlight Solutions
--------------------------

Here are some options we could consider

- Python Syntax Highliter: Pygments_
- Pure JavaScript Syntax Highlighter: highlight.js_

There will be different solutions for different tools.

- command line process on server side and show the html
- Pure JavaScript solution, highlight on Web page.
- PHP parse the diff patch format and provide proper styles.

.. _Pygments: http://pygments.org/
.. _highlight.js: https://highlightjs.org/
