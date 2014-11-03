`wp-gitweb Release 0.4.0 <wp-gitweb-release-0.4.0.rst>`_ > 
The story to redesign change logs view.


New Requirement
---------------

- Allow anonymous user to view change logs for all repos.
- Option for logged in user to view only the repos he / she is
  working on.
- Allow anonymous user to download snapshots.

**Questions**

- Should we allow anonymous user to view the changes 
  in working folder?

Initial Thinking
----------------

we might split **status** view from **logs** view.

- Any user, including anonymous user, will be able to
  check change logs for all repositories.
- **Check Status** button is ONLY available for logged in user.
- **Check Status** button is ONLY available if the logged in user
  is working on selected repository 
  (the logged in user has FTP access to the selected repository).
- **Check Status** is ALWAYS available for code reviewer.
- Autocomplete for repository name.
- Check box **Only Show Repos I am Working on**.
- Any user, including anonymous user, can download snapshot 
  from any repository as a zip file. 
