<?php

namespace KiTT;

trait Post
{
  private
    $remove_post = false,
    $post = [
      'remove_support'  => [],
      'remove_meta_box' => [],
      'SEO' => true,
      'tag' => false,
      'category' => false,
      'post_slug' => false,
      'rename_labels' => [
        'name' => 'Posts',
        'singular_name' => 'Posts',
        'menu_name' => 'Posts',
        'all_items' => 'All Posts',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Posts',
        'edit' => 'Edit',
        'edit_item' => 'Edit Posts',
        'new_item' => 'New Posts',
        'view' => 'View',
        'view_item' => 'View Posts',
        'search_items' => 'Search Posts',
        'not_found' => 'No Posts found',
        'not_found_in_trash' => 'No Posts found in trash'
      ]
    ];

  protected function __construct(array $args = [])
  {
    /** update default private variables */
    if (isset($args['remove_post'])) $this->remove_post = $args['remove_post'];

    if (isset($args['post'])) {
      $this->post = self::replace_val_in_array($this->post, $args['post']);

      (isset($args['post']['rename_labels']) || !empty($args['post']['rename_labels'])) ?: $this->post['rename_labels'] = [];
      (isset($args['post']['post_slug']) || !empty($args['post']['post_slug'])) ?: $this->post['post_slug'] = false;
    }

    if (!$this->post['SEO']) {
      if (($key = array_search('post', $this->meta_box_seo_screens)) !== false) unset($this->meta_box_seo_screens[$key]);
    }

    /** prevents BUGs of Gutenberg editor */
    if (!in_array('editor', $this->post['remove_support'])) array_push($this->post['remove_support'], 'editor');

    /** call functions / methods on init */
    add_filter('init', [$this, 'post_init']);

    /** add / remove meta boxes */
    add_action('add_meta_boxes', [$this, 'post_add_meta_boxes'], 0);

    /** remove default posts section */
    if ($this->remove_post) {

      /** remove posts in sidebar menu */
      add_action('admin_menu', function () {
        remove_menu_page('edit.php');
      });

      /** remove posts in admin bar menu */
      add_action('admin_bar_menu', function ($wp_admin_bar) {
        $wp_admin_bar->remove_node('new-post');
      }, 999);

      /** remove posts metabox from dashboard */
      add_action('wp_dashboard_setup', function () {
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
      }, 999);

      /** delete all existing posts */
      $posts = get_posts();
      if (count($posts) > 0) foreach ($posts as $post) {
        wp_delete_post($post->ID);
      }

      /** 
       * remove dashboard activity panel
       * shows only the upcoming scheduled posts
       */
      add_action('wp_dashboard_setup', function () {
        remove_meta_box('dashboard_activity', 'dashboard', 'normal');
      }, 0);
    }
  }

  public static function post_init()
  {
    global $wp_post_types;

    $self = self::get_instance();
    $support = $self->post['remove_support'];

    if (!empty($support)) foreach ($support as $feature) {
      /** remove default support */
      remove_post_type_support('post', $feature);
    }

    /** remove tag and category metabox */
    if (!$self->post['tag']) unregister_taxonomy_for_object_type('post_tag', 'post');
    if (!$self->post['category']) unregister_taxonomy_for_object_type('category', 'post');

    if (!empty($self->post['post_slug'])) {
      /** update post rewrite */
      $slug = &$wp_post_types['post']->rewrite;
      $slug = $self->post['post_slug'];

      /** update permalink */
      add_filter('pre_post_link', function ($permalink, $post) use ($self) {
        if ($post->post_type !== 'post') return $permalink;
        return $self->post['post_slug']['slug'] . '/%postname%/';
      }, 10, 2);

      /**
       * set option homepage + post_type
       * to handle multiple post_type homepages
       */
      if (!get_option('kitt_option_homepage_' . 'post')) update_option('kitt_option_homepage_' . 'post', 0);

      /** add Meta Box Homepage */
      $self->meta_box_homepage_screens[] = 'post';
    }

    if (!empty($self->post['rename_labels'])) {
      /** get all registered posts */
      $labels = &$wp_post_types['post']->labels;

      /** replace old value */
      $labels = (object) self::replace_val_in_array((array) $labels, $self->post['rename_labels'], true);
    }
  }

  public static function post_add_meta_boxes()
  {
    $self = self::get_instance();
    $meta_box = $self->post['remove_meta_box'];

    if (!empty($meta_box)) foreach ($meta_box as $feature) {
      /** remove default meta box */
      remove_meta_box($feature, ['post'], 'normal');
    }
  }
}
