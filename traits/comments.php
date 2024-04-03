<?php

namespace KiTT;

trait Comments
{
  private
    $remove_comments = false;

  protected function __construct(array $args = [])
  {
    /** update default private variables */
    if (isset($args['remove_comments'])) $this->remove_comments = $args['remove_comments'];

    if ($this->remove_comments) {

      $self = self::get_instance();

      /**
       * remove unnecessary comments
       * 
       * GitHub
       * Author: mattclements
       * Author URI: https://gist.github.com/mattclements/eab5ef656b2f946c4bfb
       */
      add_action('admin_init', function () use ($self) {
        /** redirect any user trying to access comments page */
        if ($self->pagenow === 'edit-comments.php') {
          wp_redirect(admin_url());
          exit;
        }

        /** remove comments meta box from dashboard */
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

        /** disable support for comments and trackbacks in post types */
        foreach (get_post_types() as $post_type) {
          if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
          }
        }
        /** remove comments in database */
        if (get_comments()) {
          $self->wpdb->query('TRUNCATE TABLE wp_comments');
          $self->wpdb->query('TRUNCATE TABLE wp_commentmeta');
        }
      });

      /** remove comments in sidebar menu */
      add_action('admin_menu', function () {
        remove_menu_page('edit-comments.php');
        remove_submenu_page('options-general.php', 'options-discussion.php');
      });

      /** remove comments icon in admin bar */
      add_action('wp_before_admin_bar_render', function () {
        global $wp_admin_bar;
        $wp_admin_bar->remove_node('comments');
      });

      /** remove comments links from admin bar */
      add_action('init', function () {
        if (is_admin_bar_showing()) {
          remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }
      });

      /** close comments on the front-end */
      add_filter('comments_open', '__return_false', 20, 2);
      add_filter('pings_open', '__return_false', 20, 2);

      /** hide existing comments */
      add_filter('comments_array', '__return_empty_array', 10, 2);
    }
  }
}
