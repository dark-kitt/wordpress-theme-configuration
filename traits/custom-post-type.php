<?php

namespace KiTT;

trait CustomPostType
{
  private
    $custom_post_type = [
      /** similar to the WP method */
      'post_type' => 'custom_post',
      'description' => 'A custom Post type',
      'capability_type' => 'edit_others_posts',
      'public' => true,
      'show_ui'  => true,
      'show_in_menu' => true,
      'map_meta_cap' => true,
      'hierarchical' => true,
      'query_var' => true,
      'has_archive' => true,
      'rewrite' => ['slug' => 'posts'],
      'supports' => ['title', 'editor', 'comments', 'revisions', 'trackbacks', 'author', 'excerpt', 'page-attributes', 'thumbnail', 'custom-fields', 'post-formats'],
      'menu_position' => 6,
      'menu_icon' => 'dashicons-admin-page',
      'taxonomies' => ['post_tag', 'category'],
      'labels' => [
        'name' => 'Posts',
        'singular_name' => 'Post',
        'menu_name' => 'Posts',
        'all_items' => 'All Posts',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Post',
        'edit' => 'Edit',
        'edit_item' => 'Edit Post',
        'new_item' => 'New Post',
        'view' => 'View',
        'view_item' => 'View Post',
        'search_items' => 'Search Posts',
        'not_found' => 'No Posts found',
        'not_found_in_trash' => 'No Posts found in trash',
        'attributes' => 'Post Attributes'
      ],
      'tag' => false,
      'category' => false,
      'remove_meta_box' => ['commentsdiv', 'slugdiv'],
      /**
       * dashboard at a glance widget icon
       * post-count, page-count, comment-count
       * or custom CSS class
       */
      'dashboard_icon' => 'post-count',
      /**
       * menu locations will be registered if a slug exists
       * caution, do not overwrite existing locations
       */
      'menu_locations' => [],
      /** add or remove SEO support */
      'SEO' => true
    ];

  protected function __construct(array $args = [])
  {
    /** update default private variables */
    if (isset($args['custom_post_type'])) {
      if (isset($args['custom_post_type']['labels'])) {
        $labels = self::replace_val_in_array($this->custom_post_type['labels'], $args['custom_post_type']['labels'], true);
      }
      $this->custom_post_type = self::replace_val_in_array($this->custom_post_type, $args['custom_post_type']);
      $this->custom_post_type['labels'] = $labels;
    }

    /**
     * custom register post type
     * 
     * menu icon info:
     * https://developer.wordpress.org/resource/dashicons/#menu
     */
    $post_type_object = register_post_type($this->custom_post_type['post_type'], [
      'description' => $this->custom_post_type['description'],
      'capability_type' => $this->custom_post_type['capability_type'],
      'public' => $this->custom_post_type['public'],
      'show_ui' => $this->custom_post_type['show_ui'],
      'show_in_menu' => $this->custom_post_type['show_in_menu'],
      'map_meta_cap' => $this->custom_post_type['map_meta_cap'],
      'hierarchical' => $this->custom_post_type['hierarchical'],
      'query_var' => $this->custom_post_type['query_var'],
      'has_archive' => $this->custom_post_type['has_archive'],
      'rewrite' => $this->custom_post_type['rewrite'],
      'supports' => $this->custom_post_type['supports'],
      'menu_position' => $this->custom_post_type['menu_position'],
      'menu_icon' => $this->custom_post_type['menu_icon'],
      'taxonomies' => $this->custom_post_type['taxonomies'],
      'labels' => $this->custom_post_type['labels']
    ]);

    /** add or remove meta boxes */
    add_action('add_meta_boxes', [$this, 'post_type_add_meta_boxes']);

    /** add tag metabox to new post type */
    add_filter('init', [$this, 'custom_post_type_init']);

    /** add post type to meta_box_seo_screens */
    if ($this->custom_post_type['SEO']) $this->meta_box_seo_screens[] = $post_type_object->name;

    if ($this->custom_post_type['rewrite']) {
      /** add post type to meta_box_homepage_screens */
      $this->meta_box_homepage_screens[] = $post_type_object->name;

      /**
       * set option homepage + post_type
       * to handle multiple post_type homepages
       */
      if (!get_option('kitt_option_homepage_' . $post_type_object->name)) update_option('kitt_option_homepage_' . $post_type_object->name, 0);

      /** register menu locations */
      if (isset($this->custom_post_type['menu_locations'])) $this->menu['locations'] = array_merge($this->menu['locations'], $this->custom_post_type['menu_locations']);
    }

    /** add custom post type to "At a glance" widget */
    add_filter('dashboard_glance_items', [$this, 'add_dashboard_glance_items'], 10, 1);
  }

  public static function post_type_add_meta_boxes()
  {
    $self = self::get_instance();
    $remove_meta_box = (isset($self->custom_post_type['remove_meta_box'])) ? $self->custom_post_type['remove_meta_box'] : [];

    if (!empty($remove_meta_box)) {
      foreach ($remove_meta_box as $feature) {
        /** remove default meta box */
        remove_meta_box($feature, [$self->custom_post_type['post_type']], 'normal');
      }
    }
  }

  public static function custom_post_type_init()
  {
    $self = self::get_instance();

    if ($self->custom_post_type['tag']) register_taxonomy_for_object_type('post_tag', $self->custom_post_type['post_type']);
    if ($self->custom_post_type['category']) register_taxonomy_for_object_type('category', $self->custom_post_type['post_type']);
  }

  public static function add_dashboard_glance_items($items)
  {
    $self = self::get_instance();
    $num_posts = wp_count_posts($self->custom_post_type['post_type']);

    if ($num_posts) {

      $published = intval($num_posts->publish);
      $post_type_object = get_post_type_object($self->custom_post_type['post_type']);

      $text = _n('%s ' . $post_type_object->labels->singular_name, '%s ' . $post_type_object->labels->name, $published, 'wp-base-configuration');
      $text = sprintf($text, number_format_i18n($published));

      /* set icon class for dashboard */
      $icon = ($self->custom_post_type['dashboard_icon']) ? $self->custom_post_type['dashboard_icon'] : 'post-count';

      if (current_user_can($post_type_object->cap->edit_posts)) {

        $output = '<a href="edit.php?post_type=' . $post_type_object->name . '">' . $text . '</a>';
        echo '<li class="' . $icon . ' ' . $post_type_object->name . '-count">' . $output . '</li>';
      } else {

        $output = '<span>' . $text . '</span>';
        echo '<li class="' . $icon . ' ' . $post_type_object->name . '-count">' . $output . '</li>';
      }
    }

    return $items;
  }
}
