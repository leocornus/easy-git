< `Active Git Repository Management Design Story 
<wp_gitweb_Git_Repo_Management.rst>`_

In wp-gitweb plugin we are using context object to track the whole request and
response lifecycle.

The function **wpg_request_context** will get ready the context for any request.
A context will have the following fields

:gituser: current logged in user or selected user for a code reviewer.
:user_email: current user's email
:user_fullname: current user's full name / display name.
:repo: selected repository label
:base_path: the base path for the selected repository.
:branch: the Git branch for the selected repository.
:action: the submit action.
