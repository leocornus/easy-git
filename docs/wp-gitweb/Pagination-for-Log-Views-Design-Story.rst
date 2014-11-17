`wp-gitweb Release 0.4.0 <wp-gitweb-release-0.4.0.rst>`_ > 
Design Story for adding pagination for git logs view.

As the change logs are more than 50, it will be very hard to 
browse all change in one page and
the page loading time will be a lot slower.
Pagination_ will be the common solution for this challenge.

Implementation Proposal
-----------------------

This is mainly about how the track the paging information
from page to page.
There 2 solutions we are considering now:

- cookie solution
- AJAX solution

No need to do pagination, just load batch change logs at a time.
We will offfer **Load More ...** link at the end of the page.
Once user click it, we will load another set of batch change logs.
A none display div on page will be used to track the page number.


New Site Option
---------------

Need introduce new site option for user to set the maxium commits
for each page.
The new option will have name **wpg_commits_per_page**.
It could be set in the page **Dashboard** -> **General Settings**.

AJAX Actions
------------

We will create WordPress AJAX actions to return the logs.

The function **wpg_get_log_list** need update to handle pagination.

jQuery Frontend
---------------

We will introduce a new file **gitweb.js** for JavaScript code.

Actions:

- load logs page by page
- triger merge status JavaScript after load each page. 

**Page Loading Workflow**

- Initially the page just load necessary JavaScript with no logs.
- The status row in **tfoot** will display a log summary:
  loaded xxx of total xxx commits.
- **load more** will show progressing icon.
- Once the page DOM is ready, JavaScript start to execute.
- JavaScript to read the page number, starts from 0
- AJAX call to load one page of logs.
- JavaScript to append the logs to **tbody:last**.
- hide the progressing icon when logs are all loaded.
- update the page number hidden field.
- update the status row
- AJAX call to load merge status for all commis in one page.

Change Logs View Update
-----------------------

The change log list table will have a status **tfoot** row.
It will have the hidden field to track the page number and
**load more** link to load next page.
It will also show progressing while it is loading logs.
The **load more** link will change according to pagination situation:
more pages, last page, ...

**Commits Number Summary**

The message should be something like::

  Show xxx of Total xxx Commits

Q: How to the total number of commits for a repository?

Q: When should we get the total number? first batch of loading or
every batch of loading?

**Toggle Load More**

We will toggle the Load More link in the following situation:

- When it is loading logs.
- When it is loading merge status?
- After load all commits.

The **disabled** attribute for a html button type will be used 
to disable the Load More button::

  jQuery("#loadMore").attr('disabled', true);
  jQuery("#loadMore").attr('disabled', false);

Pagination Support from Git
---------------------------

The Git log commmand has very good support for Pagination_.
We can use either **since until** or **commit limiting** to
do Pagination_.

Sample for commit limiting::

  ; first page, 20 per page.
  $ git log -20 --oneline
  ; second page.
  $ git log --skip=20 -20 --oneline
  ; 4th page.
  $ git log --skip=60 -20 --oneline
  ; Here is the pattern. PAGE_NUMBER starts from 0
  $ git log --skip=PAGE_NUMBER * PER_PAGE -PER_PAGE --oneline

How to get the commit count from Git::

  $ git rev-list HEAD --count .
  $ git log --oneline . | wc

How to toggle mouse cursor to wait and default ::

  jQuery('html,body').css('cursor', 'wait');
  jQuery(':button').css('cursor', 'wait');
  jQuery('html,body').css('cursor', 'default');

Code Memos
----------

**How to Scroll to Bottom of page**

The jQuery_ funtion **scrollTop** will be used to scroll down to
the bottom of a page. Here is a one line sample::

  jQuery('html,body').scrollTop(jQuery(windown).height());

.. _Pagination: http://en.wikipedia.org/wiki/Pagination
.. _jQuery: http://jquery.com
