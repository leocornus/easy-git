`wp-gitweb Release 0.4.0 <wp-gitweb-release-0.4.0.rst>`_ > 
Design Story for displaying the merge status for each commit.

Actions
-------

- create a separate folder for checking merge status.
- this folder will be read only for all regular users and 
  anonymous users.
- jQuery client to query the merge status for each commit and 
  display the result.
- call back PHP fucntions to execute git query and reply the 
  result to jQuery client.

API Data Structure
------------------

As we will use wp-ajax action to query the merge status for 
each commit.

Here is the data structure for query::

  var data = {
    "action" : "wpg_get_merge_status",
    "commit_id" : commitId
  }

The response data structure will be like the following::

  var status = JSON.parse(response);
  console.log('UAT Merge Status: " + status['uat']);
  console.log('Prod Merge Status: " + status['prod']);

New Options
-----------

Need introduce new options to save the location of 
staging branch and production branch.

If we use branch name as the folder name and 
reuse the merge folder as the base folder for staging 
and production branch's location, we don't need new options.

Here is the code sample to get those pathes::

  $merge_folder = get_site_option('wpg_merge_folder');
  $uat_branch = get_site_option('wpg_merge_uat_branch');
  $prod_branch = get_site_option('wpg_merge_prod_branch');

  $uat_path = $merge_folder . DIRECTORY_SEPARATOR . $uat_branch;
  $prod_path = $merge_folder . DIRECTORY_SEPARATOR . $prod_branch;

UI Design
---------

**New columns for log view**

Just need add two columns for log view commits list table to 
display the merge status, one for UAT branch and one for Production branch.
Column header will call '''UAT''' and '''Production'''.
Both columns will have initial value '''loading image''' 
for each commit.

**Merge Status for Anonymous User**

Update the details changeset view to show merge status for 
anonymous users (none code reviewer).
Bascially, update the function '''wpg_widget_merge_html''' to handle
users who don't have '''code_reviewer''' role, or anonymous users.

Sorting the jQuery Selector Result
----------------------------------

here are code samples::

  jQuery("td[id='commit-id']").sort(function(a, b) {
    return a - b;
  }).each(...);
