`wp-gitweb RElease 0.4.0 <wp-gitweb-release-0.4.0.rst>`_ > 
Double POST Problem (Caused by browser Back button) and Its Solutions

What's the Issue?
-----------------

The message of command **git commit** (without any file) 
is displayed whenever user submit a commit and click **back** 
button of the browser.
If there is action **wpg_after_perform_commit** hooked,
the action will be executed and the git status message will be 
record.
This is basicly an issue of HTTP double post.

Here is the steps to reproduce:

- commit some change from gitweb page.
- when see the gitweb commit success message, click the back button.
- you will see a git status message.

The `PRG Pattern`_ will solve this problem.
And we will use PHP session to carry the success git commit message
to the redirected get page.

Solution
--------

We will implement the `PRG Pattern`_ to solve this issue.
Introduce the PHP seesion **commit_context** to save 
the whole commit context when the commit successed.
The commit result will be saved in the context.
The context will be update with action **Check Logs**.

Then the page will be redirect to gitweb check logs page.
The commit result will be show on the beginning of logs.

PHP Session
-----------

The PHP Session will be used to pass the status from the
POST request to the GET request.
Here are the important PHP functions we are using::

  session_start();
  isset($_SESSION['commit_context']);
  unset($_SESSION['commit_context']);
  session_write_close();

.. _PRG Pattern: http://en.wikipedia.org/wiki/Post/Redirect/Get
