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
The new option will have name **wpg_max_commits_per_page**.
It could be set in the page **Dashboard** -> **General Settings**.

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

.. _Pagination: http://en.wikipedia.org/wiki/Pagination
