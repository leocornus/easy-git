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

Workflow Thinking
-----------------

The easy way to implement this is changing the jQuery_ dialog
to show the details change under the summary area.
In case of big changeset, we will initialy show the first 10 files.
More thang 10 files changes will be considered as **big changeset**.
User can still review the details change for any file in this case
by clicking the status link for the file.

The patch view will appear for both check status view and
commit view.

Syntax Highlight Solutions
--------------------------

Here are some options we could consider

- Python Syntax Highliter: Pygments_
- Pure JavaScript Syntax Highlighter: highlight.js_

There will be different solutions for different tools.

- command line process on server side and show the html
- Pure JavaScript solution, highlight on Web page.
- PHP parse the diff patch format and provide proper styles.

Code Samples
------------

The following code shows how to use jQuery to get all changed files.
Assume each file has a td with id '''filename'''::

  <script type="text/javascript">
  jQuery(document).ready(function($) {
      $("td[id='filename']").each(function(index) {
          console.log($(this).html());
      });
  });
  </script>

The following code try to demonstrate how to append rows to 
an existing table::

  var last = $("table[id='{$table_id}'] > tbody:last");
  var codeId = 'file' + index;
  last.append('<tr><td colspan="{$td_colspan}">' + 
              '<pre style="font-size: 1.5em; ' +
              'white-space: pre-wrap; ' + 
              'text-align: left;' + 
              '"><code class="diff"' +
              'id="' + codeId + 
              '">' + response + '</code></pre>' +
              '</td></tr>');

.. _Pygments: http://pygments.org/
.. _highlight.js: https://highlightjs.org/
