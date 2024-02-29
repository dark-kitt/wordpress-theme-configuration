<?php

namespace KiTT;

trait Menu
{
    private
        $menu = [
            'locations'  => [
                'header' => 'Header',
                'main' => 'Main',
                'footer' => 'Footer'
            ]
        ];

    protected function __construct(array $args = [])
    {
        /** update default private variables */
        if (isset($args['menu'])) {
            $this->menu = self::replace_val_in_array($this->menu, $args['menu']);
        }

        /** call functions / methods on init */
        add_action('init', [$this, 'menu_init']);

        /** call functions / methods on admin init (after init) */
        add_action('admin_init', [$this, 'menu_admin_init']);

        /** rearrange nav-menus.php */
        add_action('admin_menu', [$this, 'rearrange_nav_menus'], 110, 1);

        /** add data attributes to menu links */
        add_filter('nav_menu_link_attributes', [$this, 'add_menu_atts'], 10, 3);
    }

    public static function add_menu_atts($atts, $item, $args) {

        $atts['data-post-parent'] = $item->post_parent;
        $atts['data-menu-order'] = $item->menu_order;
        $atts['data-menu-item'] = $item->nav_menu_item;
        $atts['data-menu-item-parent'] = $item->menu_item_parent;

        return $atts;
    }

    public static function menu_init()
    {
        /**
         * remove menu and widgets theme support
         * to rearrange nav-menus.php to top-level
         * 
         * prevents registering nav-menus.php
         * in wp-admin/menu.php -> line 195
         */
        remove_theme_support('menus');
        remove_theme_support('widgets');
    }

    public static function menu_admin_init()
    {
        $self = self::get_instance();
        /**
         * register default menus
         * 
         * add "menus" theme support belated
         * after admin menu is already registered
         * 
         * this function automatically registers
         * custom menu support for the theme,
         * therefore you do not need to call
         * add_theme_support('menus')
         */
        register_nav_menus(
            $self->menu['locations']
        );
    }

    public static function rearrange_nav_menus()
    {
        global $menu;
        /**
         * add nav-menus.php to admin menu top-level
         * 
         * positions:
         * 2 – Dashboard
         * 4 – Separator
         * 5 – Posts
         * 10 – Media
         * 15 – Links
         * 20 – Pages
         * 25 – Comments
         * 59 – Separator
         * 60 – Appearance
         * 65 – Plugins
         * 70 – Users
         * 75 – Tools
         * 80 – Settings
         * 99 – Separator
         */
        $menu[50] = [
            __('Menus', 'wp-base-configuration'),
            /** menu title */
            'edit_others_posts',
            /** capability */
            'nav-menus.php',
            /** file */
            __('Menus', 'wp-base-configuration'),
            /** page title */
            'menu-top menu-nav',
            /** class="" */
            'toplevel_page_nav-menus',
            /** id="" */
            'dashicons-menu'
            /** menu icon */
        ];

        if (in_array('editor', (array) wp_get_current_user()->roles)) {
            /** remove appearance for editors */
            remove_menu_page('themes.php');
        }
    }
}
