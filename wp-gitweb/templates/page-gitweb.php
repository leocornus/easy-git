<?php
/*
 * Template Name: WP GitWeb
 * Description: a page template to interface Git repository
 */
?>

<?php
get_header();
wp_enqueue_script('jquery-ui-dialog');
wp_enqueue_script('highlight-js');
wp_enqueue_style('jquery-ui');
wp_enqueue_style('highlight-js-default');
wp_enqueue_style('wpg-styles');

$context = wpg_request_context();
// we will using the left nav and content layout.
?>

<div id="right_column">
  <h1>Welcome to WordPress GitWeb</h1>
  <?php echo wpg_widget_repo_form($context); ?>
</div> <?php // END content ?>

<?php get_footer(); ?>
