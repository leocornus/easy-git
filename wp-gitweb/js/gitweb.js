/**
 * @summary wp-gitweb JavaScript client
 * @description JavaScript client for wp-gitweb plugin
 * @version 
 *
 * Some utilities to serve frontend of WordPress gitweb plugin.
 */

(function ($, window, document) {

  /**
   * check the merge status for a given commit.
   */
  function mergeStatus(commitId, uatBranch, prodBranch) {

    // query merge status.
    var data = {
        "action" : "wpg_get_merge_status",
        "commit_id" : commitId
    };
    // ajax_url will be set up by wp_localize_script
    $.post(ajax_url, data, function(response) {

        var status = JSON.parse(response);
        //console.log(status);
        var uat_td = $("td[id='uat-" + commitId + "']")
        uat_td.html(status[uatBranch]);
        if(status[uatBranch] == 'Pending') {
            uat_td.css('background-color', 'red');
        } else {
            uat_td.css('background-color', 'green');
        }
        var prod_td = $("td[id='prod-" + commitId + "']")
        prod_td.html(status[prodBranch]);
        if(status[prodBranch] == 'Pending') {
            prod_td.css('background-color', 'red');
        } else {
            prod_td.css('background-color', 'green');
        }
    });
  }

} (jQuery, window, document));
