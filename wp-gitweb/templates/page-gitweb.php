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

<div id="right_column">
  <?php echo wpg_widget_repo_form($context); ?>
</div> <?php // END content ?>

<?php get_footer(); ?>
