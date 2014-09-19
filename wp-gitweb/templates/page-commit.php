<?php
/*
 * Template Name: WP GitWeb Commit
 * Description: a page template to show details changeset for a
 * git commit.
 */
?>

<?php
get_header();
wp_enqueue_script('jquery-ui-dialog');
wp_enqueue_script('highlight-js');
wp_enqueue_style('jquery-ui');
wp_enqueue_style('highlight-js-default');

$commit = wpg_get_request_param('id');
?>

<div id="right_column">
  <?php echo wpg_widget_changeset_view($commit); ?>
</div>

<?php get_footer(); ?>

