<?php
/*
 * Template Name: WP GitWeb
 * Description: a page template to interface Git repository
 */
?>

<?php
get_header();
$context = wpg_request_context();
// we will using the left nav and content layout.
?>

<div id="left_column">
  <div class='leftnav'>
    <div id="repo-nav" class="widget">
      <h2 class="widgettitle">Repositories</h2>
      <?php echo wpg_widget_repos_nav($context['gituser']);?>
    </div>
  </div>
</div> <?php // END left_column ?>

<div id="content">

</div> <?php // END content ?>

<?php get_footer(); ?>
