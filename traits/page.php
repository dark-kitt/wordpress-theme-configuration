<?php

namespace KiTT;

trait Page
{
  private
    $remove_page = false,
    $page = [
      'remove_support'  => [],
      'remove_meta_box' => [],
      'SEO' => true,
      'tag' => false,
      'category' => false,
      'rename_labels' => [
        'name' => 'Pages',
        'singular_name' => 'Pages',
        'menu_name' => 'Pages',
        'all_items' => 'All Pages',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Pages',
        'edit' => 'Edit',
        'edit_item' => 'Edit Pages',
        'new_item' => 'New Pages',
        'view' => 'View',
        'view_item' => 'View Pages',
        'search_items' => 'Search Pages',
        'not_found' => 'No Pages found',
        'not_found_in_trash' => 'No Pages found in trash'
      ]
    ];

  protected function __construct(array $args = [])
  {
    /** update default private variables */
    if (isset($args['remove_page'])) $this->remove_page = $args['remove_page'];

    if (isset($args['page'])) {
      $this->page = self::replace_val_in_array($this->page, $args['page']);

      (isset($args['page']['rename_labels']) || !empty($args['page']['rename_labels'])) ?: $this->page['rename_labels'] = [];
    }

    if (!$this->page['SEO']) {
      if (($key = array_search('page', $this->meta_box_seo_screens)) !== false) unset($this->meta_box_seo_screens[$key]);
    }

    /** prevents BUGs of Gutenberg editor */
    if (!in_array('editor', $this->page['remove_support'])) array_push($this->page['remove_support'], 'editor');

    /**
     * call functions/methods
     * required on init
     */
    add_filter('init', [$this, 'page_init']);

    /** add / remove meta boxes */
    add_action('add_meta_boxes', [$this, 'page_add_meta_boxes']);

    /** remove default pages section */
    if ($this->remove_page) {

      /** remove page in sidebar menu */
      add_action('admin_menu', function () {
        remove_menu_page('edit.php?post_type=page');
      });

      /** remove page in admin bar menu */
      add_action('admin_bar_menu', function ($wp_admin_bar) {
        $wp_admin_bar->remove_node('new-page');
      }, 999);

      /** delete all existing pages */
      $pages = get_pages();
      if (count($pages) > 0) foreach ($pages as $page) {
        wp_delete_post($page->ID);
      }
    }
  }

  public static function page_init()
  {
    $self = self::get_instance();
    $support = $self->page['remove_support'];

    if (!empty($support)) foreach ($support as $feature) {
      /** remove default support */
      remove_post_type_support('page', $feature);
    }

    /** add tag and category metabox */
    (!$self->page['tag']) ?: register_taxonomy_for_object_type('post_tag', 'page');
    (!$self->page['category']) ?: register_taxonomy_for_object_type('category', 'page');

    if (!empty($self->page['rename_labels'])) {
      /** get all registered posts */
      global $wp_post_types;
      $labels = &$wp_post_types['page']->labels;

      /** replace old values */
      $labels = (object) self::replace_val_in_array((array) $labels, $self->page['rename_labels'], true);
    }
  }

  public static function page_add_meta_boxes()
  {
    $self = self::get_instance();
    $meta_box = $self->page['remove_meta_box'];

    if (!empty($meta_box)) foreach ($meta_box as $feature) {
      /** remove default meta box */
      remove_meta_box($feature, ['page'], 'normal');
    }
  }
}
