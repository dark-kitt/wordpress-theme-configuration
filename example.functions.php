<?php

/** debug */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

global $wpdb,
  $wp_rewrite,
  $pagenow;

$kitt_instance = KiTT\ThemeSetUp::get_instance();
$kitt_instance->set_up_theme(
  $wpdb,
  /** reqiured */
  $wp_rewrite,
  /** reqiured */
  $pagenow,
  /** reqiured */
  [
    'set_up' => [
      /** custom favicon, logos and login logo url */
      'favicon' => $kitt_instance->theme_url . '/image/wp-favicon.png',
      'login_logo' => $kitt_instance->theme_url . '/image/wp-login-logo.svg',
      'login_logo_url' => WP_HOME,
      'admin_bar_logo' => $kitt_instance->theme_url . '/image/wp-admin-bar-logo.svg',
      'permalink_structure' => '/%postname%/',
      'default_user_role' => 'editor',
      'remove_welcome' => true,
      /** 1 = Mon, 2 = Thu ... 7 = Sun */
      'start_of_week' => 1,
      'timezone_string' => 'Europe/Berlin',
      'time_format' => 'H:i',
      'date_format' => 'd/m/Y',
      /**
       * install backend languages
       * 
       * list of available translations
       * https://translate.wordpress.org/
       * 
       * to remove a translation
       * delete the language code and the
       * files in the /web/storage/lang directory
       */
      'install_languages' => ['de_DE'],
      /** configure ACF (Advanced Custom Fields) */
      'ACF' => [
        /** add background color button to WYSIWYG editor */
        'WYSIWYG_BG_color' => true,
        'save_load' => [
          /** default: null, if null = ACF default -> /themes/your-theme/acf-json */
          'JSON_save' => constant('WP_STORAGE_DIR') . '/acf/acf-json',
          /** default: null, if null = ACF default -> /themes/your-theme/acf-json */
          'JSON_load' => constant('WP_STORAGE_DIR') . '/acf/acf-json',
          /** default: null, if null = ACF default -> /themes/your-theme/acf-json */
          'AutoSync_JSON_save' => constant('WP_STORAGE_DIR') . '/acf/acf-json',
          /** default: null, if null = ACF default -> /themes/your-theme/acf-json */
          'AutoSync_JSON_load' => constant('WP_STORAGE_DIR') . '/acf/acf-json',
          /** default: null, if null = ACF default -> /themes/your-theme/acf-php */
          'AutoSync_PHP_save' => constant('WP_STORAGE_DIR') . '/acf/acf-php',
          /** default: null, if null = ACF default -> /themes/your-theme/acf-php */
          'AutoSync_PHP_load' => constant('WP_STORAGE_DIR') . '/acf/acf-php'
        ],
        'google_api_key' => false
      ],
      /** add or remove company settings menu page */
      'company_settings' => true,
    ]
  ]
);

$kitt_instance->post([
  /** removes completely the default post section */
  'remove_post' => false,
  'post' => [
    /**
     * info:
     * https://developer.wordpress.org/reference/functions/remove_post_type_support/
     * 
     * NOTE:
     * Gutenberg editor is always disabled
     */
    'remove_support' => ['excerpt', 'comments', 'trackbacks', 'author'],
    /** inspect the label attribute for="" in the screen options panel */
    'remove_meta_box' => ['commentsdiv', 'slugdiv'],
    /** en- or disable the SEO meta box */
    'SEO' => true,
    /** to disable tag support */
    'tag' => true,
    /** to disable category support */
    'category' => true,
    /** set default post slug */
    'post_slug' => false,
    /** rename default post section */
    'rename_labels' => [
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
      'not_found_in_trash' => 'No Posts found in trash'
    ]
  ]
]);

$kitt_instance->page([
  /** removes completely the default page section */
  'remove_page' => false,
  'page' => [
    /**
     * info:
     * https://developer.wordpress.org/reference/functions/remove_post_type_support/
     * 
     * NOTE:
     * Gutenberg editor is always disabled
     */
    'remove_support' => ['excerpt', 'comments', 'trackbacks', 'author'],
    /** inspect the label attribute for="" in the screen options panel */
    'remove_meta_box' => ['commentsdiv', 'slugdiv'],
    /** en- or disable the SEO meta box */
    'SEO' => true,
    /** to enable tag support */
    'tag' => true,
    /** to enable category support */
    'category' => true,
    /** rename default post section */
    'rename_labels' => [
      'name' => __('Pages') . ' DE',
      'singular_name' => __('Page') . ' DE',
      'menu_name' => __('Pages') . ' DE',
      'all_items' => 'All ' . __('Pages') . ' DE',
      'add_new' => 'Add New',
      'add_new_item' => 'Add New ' . __('Page') . ' DE',
      'edit' => 'Edit',
      'edit_item' => 'Edit ' . __('Page') . ' DE',
      'new_item' => 'New ' . __('Page') . ' DE',
      'view' => 'View',
      'view_item' => 'View Page',
      'search_items' => 'Search ' . __('Pages') . ' DE',
      'not_found' => 'No Pages found',
      'not_found_in_trash' => 'No Pages found in trash'
    ]
  ]
]);

$kitt_instance->attachment([
  'attachment' => [
    /** to enable tag support */
    'tag' => false,
    /** to enable category support */
    'category' => false,
    /** enable search duplicates support */
    'search_duplicates' => true
  ],
  /** 
   * set custom upload mimes
   * 
   * extend_defaults = true|false
   * true = merges the default upload mimes
   * false = replaces the default upload mimes
   * 
   * list of defaulst:
   * https://developer.wordpress.org/reference/functions/get_allowed_mime_types/
   */
  'upload_mimes' => [
    'extend_defaults' => true,
    'jpg|jpeg|jpe' => 'image/jpeg',
    'gif' => 'image/gif',
    'png' => 'image/png',
    /**
     * NOTE:
     * the XML declaration is required
     * in each SVG file, otherwise
     * the SVG upload is not accepted
     * 
     * enter the version and the encoding
     * charset at the top of each SVG file 
     * 
     * <?xml version="1.0" encoding="utf-8"?>
     * <svg xmlns="http://www.w3.org/2000/svg" ... viewBox="0 0 100 57">
     *     ...
     * </svg>
     */
    'svg' => 'image/svg+xml',
    'pdf' => 'application/pdf',
    'mp3|m4a|m4b' => 'audio/mpeg',
    'mp4|m4v' => 'video/mp4',
    'zip' => 'application/zip'
  ],
  'options_media' => [
    /** WP default 150x150px */
    'thumbnail_size' => [
      'thumbnail_size_w' => 150,
      'thumbnail_size_h' => 150
    ],
    /** WP default 1 */
    'thumbnail_crop' => 1,
    /** WP default 300x300px */
    'medium_size' => [
      'medium_size_w' => 300,
      'medium_size_h' => 300
    ],
    /** WP default 768x768px */
    'medium_large_size' => [
      'medium_large_size_w' => 768,
      'medium_large_size_h' => 768
    ],
    /** WP default 1024x1024px */
    'large_size' => [
      'large_size_w' => 1024,
      'large_size_h' => 1024
    ],
    /** WP default 0 */
    'uploads_yearmonth' => 1,
    /** WP default open */
    'ping_status' => 'closed',
    /** WP default open */
    'comment_status' => 'closed',
    /** /wp-content/uploads */
    'upload_path' => constant('WP_UPLOAD_DIR'),
    /** http://127.0.0.1/uploads */
    'upload_url_path' => constant('WP_UPLOAD_URL')
  ]
]);

$kitt_instance->comments([
  /** removes completely the default comments section */
  'remove_comments' => true
]);

$kitt_instance->menu([
  /** register main menu locations */
  'menu' => [
    'locations'  => [
      'header' => 'Header',
      'main' => 'Main',
      'footer' => 'Footer'
    ]
  ]
]);

/** 
 * register new post type
 * example: "Pages EN"
 * 
 * menu icon info:
 * https://developer.wordpress.org/resource/dashicons/#menu
 */
$kitt_instance->custom_post_type([
  'custom_post_type' => [
    /** similar to the WP method */
    'post_type' => 'pages_en',
    'description' => 'Pages EN',
    'capability_type' => 'post',
    'public' => true,
    'show_ui'  => true,
    'show_in_menu' => true,
    'map_meta_cap' => true,
    'hierarchical' => true,
    'query_var' => true,
    'has_archive' => true,
    'rewrite' => ['slug' => 'en'],
    'supports' => ['title', 'revisions', 'page-attributes', 'thumbnail', 'custom-fields', 'post-formats'],
    'menu_position' => 21,
    'menu_icon' => 'dashicons-admin-page',
    'taxonomies' => ['post_tag', 'category'],
    'labels' => [
      'name' => __('Pages') . ' EN',
      'singular_name' => __('Page') . ' EN',
      'menu_name' => __('Pages') . ' EN',
      'all_items' => 'All ' . __('Pages') . ' EN',
      'add_new' => 'Add New',
      'add_new_item' => 'Add New ' . __('Page') . ' EN',
      'edit' => 'Edit',
      'edit_item' => 'Edit ' . __('Page') . ' EN',
      'new_item' => 'New ' . __('Page') . ' EN',
      'view' => 'View',
      'view_item' => 'View Page',
      'search_items' => 'Search ' . __('Pages') . ' EN',
      'not_found' => 'No Pages found',
      'not_found_in_trash' => 'No Pages found in trash',
      'attributes' => 'Page Attributes'
    ],
    'tag' => true,
    'category' => true,
    'remove_meta_box' => ['commentsdiv', 'slugdiv'],
    /**
     * dashboard at a glance widget icon
     * post-count, page-count, comment-count
     * or custom CSS class
     */
    'dashboard_icon' => 'page-count',
    /**
     * menu locations will be registered if a slug exists
     * caution, do not overwrite existing locations
     */
    'menu_locations' => [
      'header_en' => 'Header EN',
      'main_en' => 'Main EN',
      'footer_en' => 'Footer EN'
    ],
    /** add or remove SEO support */
    'SEO' => true
  ]
]);

$kitt_instance->REST_API([
  'rest_api' => [
    /**
     * set the namespace for your routes
     * => example.com/wp-json/->namespace<-/route
     */
    'namespace' => explode('.', parse_url(WP_HOME)['host'])[0],
    /** removes the default REST API */
    'remove_default' => true,
    /**
     * examples:
     * 'Access-Control-Allow-Origin: ' . WP_HOME
     * 'Access-Control-Allow-Methods: POST, GET'
     * 'Access-Control-Allow-Credentials: true'
     * 'Access-Control-Max-Age: 600'
     */
    'headers' => [
      'Access-Control-Allow-Headers: Authorization, X-WP-Nonce, Content-Disposition, Content-MD5, Content-Type',
      'Access-Control-Allow-Origin: ' . WP_HOME,
      'Access-Control-Allow-Methods: POST, GET',
      'Access-Control-Allow-Credentials: true',
      'Access-Control-Max-Age: 600'
    ],
    /** JWT token arguments */
    'token' => [
      'expiration_time' => time() + (DAY_IN_SECONDS * 7),
      'header' => 'Access-Control-Allow-Headers, Access-Control-Allow-Origin, Content-Type, Authorization'
    ]
  ]
]);

/**
 * register custom REST routes
 * 
 * functions.php
 * modifing default args
 * $kitt_instance->rest_routes['route']['args']['param'] = ['default' => 'value']
 * 
 * add route
 * extend $this->rest_routes array
 * e.g.
 * $kitt_instance->rest_routes['endpoint'] = [
 *     'methods'  => \WP_REST_Server::CREATABLE,
 *     'callback' => 'call_callback_API',
 *     'permission_callback' => 'edit_others_posts',
 *     'args' => [
 *        'param_one' => ['default' => 'one']
 *     ]
 * ];
 * add REST route method
 * extend $instance
 * e.g.
 * $kitt_instance->call_callback_API = function (\WP_REST_Request $request)
 * {
 *     // $_GET and $_POST params
 *     $params = $request->get_params();
 *     $response = new \WP_REST_Response($params, 200);
 *     return $response;
 * };
 */
$kitt_instance->rest_routes['endpoint'] = [
  /**
   * add a custom REST route
   * example: return Vue.js routes
   * 
   * const READABLE = 'GET';
   * const CREATABLE = 'POST';
   * ...
   * const ALLMETHODS = 'GET, POST, PUT, PATCH, DELETE';
   * 
   * NOTE: axios GET request can not send data via body
   * 
   * documentation
   * https://developer.wordpress.org/reference/classes/wp_rest_server/
   */
  'methods'  => \WP_REST_Server::CREATABLE,
  'callback' => 'my_custom_callback', // string required
  /**
   * string required
   * ... current_user_can($route['permission_callback']); ...
   * 
   * list of roles and capabilities
   * https://wordpress.org/support/article/roles-and-capabilities/
   */
  'permission_callback' => 'rest_api_user',
  /** set the defaults */
  'args' => [
    'param_one' => ['default' => false]
  ]
];

/**
 * add a custom REST callback method
 */
$kitt_instance->my_custom_callback = function (\WP_REST_Request $request) use ($kitt_instance) {
  /** $_GET and $_POST params */
  $params = $request->get_params();
  /** get the default params */
  $default = $request->get_default_params();

  /** update posts default arguments with request data */
  $args = $kitt_instance->replace_val_in_array($default, $params);

  /** do stuff!!! */
  $response = "my custom response";

  return new \WP_REST_Response($response, 200);
};

/**
 * update email route arguments
 * set server settings
 *
 * update values with WP constants
 * or set your custom settings
 */
$kitt_instance->rest_routes['email']['args']['host'] = ['default' => constant('SMTP_HOST')]; // 'smtp.gmail.com'
$kitt_instance->rest_routes['email']['args']['SMTP_auth'] = ['default' => constant('SMTP_AUTH')]; // boolean
$kitt_instance->rest_routes['email']['args']['username'] = ['default' => constant('SMTP_USERNAME')]; // 'your@username.com'
/**
 * use google app password:
 * https://support.google.com/accounts/answer/185833?hl=en
 */
$kitt_instance->rest_routes['email']['args']['password'] = ['default' => constant('SMTP_PASSWORD')]; // 'app-password'
$kitt_instance->rest_routes['email']['args']['SMTP_secure'] = ['default' => constant('SMTP_SECURE')]; // 'tls'
$kitt_instance->rest_routes['email']['args']['port'] = ['default' => constant('SMTP_PORT')]; // 587
/** PHPMailer debug */
$kitt_instance->rest_routes['email']['args']['debug'] = ['default' => false];
/**
 * email test data
 *
 * test e.g. via postman
 * send data with route -> /wp-json/namespace/email
 */
$kitt_instance->rest_routes['email']['args']['set_from'] = ['default' => [
  'address' => 'from@address.com',
  'name' => 'User Foo'
]];
$kitt_instance->rest_routes['email']['args']['add_address'] = ['default' => [[
  'address' => 'add_to@address.com',
  'name' => 'User Bar'
]]];
$kitt_instance->rest_routes['email']['args']['add_reply_to'] = ['default' => [
  'address' => 'add_to_reply@address.com',
  'name' => 'User Foo'
]];
