<?php

namespace KiTT;

/** Import PHPMailer classes into the global namespace */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';

trait REST_API
{
  public
    $rest_routes = [
      /**
       * default posts route
       * 
       * available args WP_Query::parse_query():
       * https://developer.wordpress.org/reference/classes/wp_query/parse_query/
       */
      'posts' => [
        /**
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
        'callback' => 'get_posts_API',
        'permission_callback' => 'rest_api_user',
        'args' => [
          'posts_per_page' => ['default' => -1],
          'offset' => ['default' => 0],
          'cat' => ['default' => ''],
          'category_name' => ['default' => ''],
          'orderby' => ['default' => 'date'],
          'order' => ['default' => 'DESC'],
          'include' => ['default' => []],
          'exclude' => ['default' => []],
          'meta_key' => ['default' => ''],
          'meta_value' => ['default' => ''],
          'post_type' => ['default' => ['post']],
          'post_mime_type' => ['default' => ''],
          'post_parent' => ['default' => 0],
          'author' => ['default' => ''],
          'author_name' => ['default' => ''],
          'post_status' => ['default' => ['publish', 'pending', 'draft', 'auto-draft', 'future', 'private']],
          'suppress_filters' => ['default' => true],
          'fields' => ['default' => '']
        ]
      ],
      /**
       * pages default route
       * 
       * available arguments:
       * https://developer.wordpress.org/reference/functions/get_pages/
       */
      'pages' => [
        'methods'  => \WP_REST_Server::CREATABLE,
        'callback' => 'get_pages_API',
        'permission_callback' => 'rest_api_user',
        'args' => [
          'sort_order' => ['default' => 'ASC'],
          'sort_column' => ['default' => 'post_title'],
          'hierarchical' => ['default' => true],
          'exclude' => ['default' => ''],
          'include' => ['default' => ''],
          'meta_key' => ['default' => ''],
          'meta_value' => ['default' => ''],
          'authors' => ['default' => ''],
          'child_of' => ['default' => 0],
          'parent' => ['default' => -1],
          'exclude_tree' => ['default' => ''],
          'number' => ['default' => 0],
          'offset' => ['default' => 0],
          'post_type' => ['default' => 'page'],
          'post_status' => ['default' => ['publish', 'pending', 'draft', 'auto-draft', 'future', 'private']]
        ]
      ],
      /**
       * default attachment route
       * 
       * available args WP_Query::parse_query():
       * https://developer.wordpress.org/reference/classes/wp_query/parse_query/
       */
      'attachments' => [
        'methods'  => \WP_REST_Server::CREATABLE,
        'callback' => 'get_attachments_API',
        'permission_callback' => 'rest_api_user',
        'args' => [
          'post_type' => ['default' => 'attachment'],
          'post_mime_type' => ['default' => 'image'],
          'post_status' => ['default' => 'inherit'],
          'posts_per_page' => ['default' => -1]
        ]
      ],
      /**
       * default comments route
       * 
       * available args WP_Comment_Query::__construct():
       * https://developer.wordpress.org/reference/classes/wp_query/parse_query/
       */
      'comments' => [
        'methods'  => \WP_REST_Server::CREATABLE,
        'callback' => 'get_comments_API',
        'permission_callback' => 'rest_api_user',
        'args' => [
          'author_email' => ['default' => ''],
          'author_url' => ['default' => ''],
          'author__in' => ['default' => ''],
          'author__not_in' => ['default' => ''],
          'include_unapproved' => ['default' => ''],
          'fields' => ['default' => ''],
          'ID' => ['default' => ''],
          'comment__in' => ['default' => ''],
          'comment__not_in' => ['default' => ''],
          'karma' => ['default' => ''],
          'number' => ['default' => ''],
          'offset' => ['default' => ''],
          'no_found_rows' => ['default' => true],
          'orderby' => ['default' => ''],
          'order' => ['default' => 'DESC'],
          'paged' => ['default' => 1],
          'parent' => ['default' => ''],
          'parent__in' => ['default' => ''],
          'parent__not_in' => ['default' => ''],
          'post_author__in' => ['default' => ''],
          'post_author__not_in' => ['default' => ''],
          'post_ID' => ['default' => ''],
          'post_id' => ['default' => 0],
          'post__in' => ['default' => ''],
          'post__not_in' => ['default' => ''],
          'post_author' => ['default' => ''],
          'post_name' => ['default' => ''],
          'post_parent' => ['default' => ''],
          'post_status' => ['default' => ''],
          'post_type' => ['default' => ''],
          'status' => ['default' => 'all'],
          'type' => ['default' => ''],
          'type__in' => ['default' => ''],
          'type__not_in' => ['default' => ''],
          'user_id' => ['default' => ''],
          'search' => ['default' => ''],
          'count' => ['default' => false],
          'meta_key' => ['default' => ''],
          'meta_value' => ['default' => ''],
          'meta_query' => ['default' => ''],
          'date_query' => ['default' => null], // See WP_Date_Query
          'hierarchical' => ['default' => false],
          'cache_domain' => ['default' => 'core'],
          'update_comment_meta_cache' => ['default' => true],
          'update_comment_post_cache' => ['default' => false]
        ]
      ],
      /**
       * default terms arguments
       * get categories or tags
       * 
       * available args WP_Term_Query::__construct():
       * https://developer.wordpress.org/reference/classes/wp_term_query/__construct/
       */
      'terms' => [
        'methods'  => \WP_REST_Server::CREATABLE,
        'callback' => 'get_terms_API',
        'permission_callback' => 'rest_api_user',
        'args' => [
          'taxonomy' => ['default' => null],
          'object_ids' => ['default' => null],
          'orderby' => ['default' => 'name'],
          'order' => ['default' => 'ASC'],
          'hide_empty' => ['default' => true],
          'include' => ['default' => []],
          'exclude' => ['default' => []],
          'exclude_tree' => ['default' => []],
          'number' => ['default' => ''],
          'offset' => ['default' => ''],
          'fields' => ['default' => 'all'],
          'count' => ['default' => false],
          'name' => ['default' => ''],
          'slug' => ['default' => ''],
          'term_taxonomy_id' => ['default' => ''],
          'hierarchical' => ['default' => true],
          'search' => ['default' => ''],
          'name__like' => ['default' => ''],
          'description__like' => ['default' => ''],
          'pad_counts' => ['default' => false],
          'get' => ['default' => ''],
          'child_of' => ['default' => 0],
          'parent' => ['default' => ''],
          'childless' => ['default' => false],
          'cache_domain' => ['default' => 'core'],
          'update_term_meta_cache' => ['default' => true],
          'meta_query' => ['default' => ''],
          'meta_key' => ['default' => ''],
          'meta_value' => ['default' => ''],
          'meta_type' => ['default' => ''],
          'meta_compare' => ['default' => '']
        ]
      ],
      /**
       * default menus route
       * 
       * available args:
       * https://developer.wordpress.org/reference/functions/wp_nav_menu/
       */
      'menus' => [
        'methods'  => \WP_REST_Server::CREATABLE,
        'callback' => 'get_menus_API',
        'permission_callback' => 'rest_api_user',
        'args' => [
          'all_menus' => ['default' => false],
          'menu' => ['default' => ''],
          'menu_class' => ['default' => ''],
          'menu_id' => ['default' => ''],
          'container' => ['default' => 'ul'],
          'container_class' => ['default' => ''],
          'container_id' => ['default' => ''],
          'container_aria_label' => ['default' => ''],
          'fallback_cb' => ['default' => false],
          'before' => ['default' => ''],
          'after' => ['default' => ''],
          'link_before' => ['default' => ''],
          'link_after' => ['default' => ''],
          'echo' => ['default' => false],
          'depth' => ['default' => 0],
          'walker' => ['default' => ''],
          'theme_location' => ['default' => ''],
          'items_wrap' => ['default' => '<ul id="%1$s" class="%2$s">%3$s</ul>'],
          'item_spacing' => ['default' => 'preserve']
        ]
      ],
      /**
       * default ACF get_fields route
       * 
       * resources:
       * https://www.advancedcustomfields.com/resources/get_fields/
       */
      'fields' => [
        'methods'  => \WP_REST_Server::CREATABLE,
        'callback' => 'get_fields_API',
        'permission_callback' => 'rest_api_user',
        'args' => [
          'ID' => [
            'default' => 0,
            'required' => true
          ],
          'fields' => ['default' => false]
        ]
      ],
      /**
       * default wpdb route
       * 
       * all arguments will be set
       * as SQL query with REGEXP
       * 
       * e.g.:
       * WHERE post_type REGEXP 'post$'
       * AND ID REXEXP '1'
       * 
       * exceptions:
       * SQL = new SQL query
       * order = 'DESC' | 'ASC'
       * order_by = 'ID'
       */
      'wpdb' => [
        'methods'  => \WP_REST_Server::CREATABLE,
        'callback' => 'get_wpdb_API',
        'permission_callback' => 'rest_api_user',
        'args' => [
          'SQL' => ['default' => false],
          'ID' => ['default' => ''],
          'post_author' => ['default' => ''],
          'post_date' => ['default' => ''],
          'post_date_gmt' => ['default' => ''],
          'post_content' => ['default' => ''],
          'post_title' => ['default' => ''],
          'post_excerpt' => ['default' => ''],
          'post_status' => ['default' => ''],
          'comment_status' => ['default' => ''],
          'ping_status' => ['default' => ''],
          'post_password' => ['default' => ''],
          'post_name' => ['default' => ''],
          'to_ping' => ['default' => ''],
          'pinged' => ['default' => ''],
          'post_modified' => ['default' => ''],
          'post_modified_gmt' => ['default' => ''],
          'post_content_filtered' => ['default' => ''],
          'post_parent' => ['default' => ''],
          'guid' => ['default' => ''],
          'menu_order' => ['default' => ''],
          'post_mime_type' => ['default' => ''],
          'comment_count' => ['default' => ''],
          'post_type' => ['default' => ''],
          'order' => ['default' => 'DESC'],
          'group_by' => ['default' => 'ID']
        ]
      ],
      /**
       * default mail route
       * 
       * arguments based on PHPMailer:
       * https://github.com/PHPMailer/PHPMailer
       */
      'email' => [
        'methods'  => \WP_REST_Server::CREATABLE,
        'callback' => 'send_email_API',
        'permission_callback' => 'rest_api_user',
        'args' => [
          'host' => ['required' => true],
          'SMTP_auth' => ['required' => true],
          'username' => ['required' => true],
          'password' => ['required' => true],
          'SMTP_secure' => ['required' => true],
          'port' => ['required' => true],
          'set_from' => ['required' => true],
          'add_address' => [
            'required' => true,
            'validate_callback' => 'is_array'
          ],
          'add_reply_to' => ['default' => false],
          'add_cc' => ['default' => false],
          'add_bcc' => ['default' => false],
          'add_attachment' => ['default' => []],
          'subject' => ['default' => 'Subject'],
          'body' => ['default' => 'This is the HTML message body <b>in bold!</b>'],
          'alt_body' => ['default' => 'This is the body in plain text for non-HTML mail clients'],
          'debug' => ['default' => false]
        ]
      ]
    ];

  private
    $rest_api = [
      'namespace' => null,
      'remove_default' => true,
      'headers' => [
        'Access-Control-Allow-Headers: Authorization, X-WP-Nonce, Content-Disposition, Content-MD5, Content-Type'
      ],
      'token' => [
        'expiration_time' => 604800,
        'header' => 'Access-Control-Allow-Headers, Content-Type, Authorization'
      ]
    ],
    $capabilities = [
      "switch_themes" => false,
      "edit_themes" => false,
      "activate_plugins" => false,
      "edit_plugins" => false,
      "edit_users" => false,
      "edit_files" => false,
      "manage_options" => false,
      "moderate_comments" => false,
      "manage_categories" => false,
      "manage_links" => false,
      "upload_files" => false,
      "import" => false,
      "unfiltered_html" => false,
      "edit_posts" => false,
      "edit_others_posts" => false,
      "edit_published_posts" => false,
      "publish_posts" => false,
      "edit_pages" => false,
      "read" => false,
      "level_10" => false,
      "level_9" => false,
      "level_8" => false,
      "level_7" => false,
      "level_6" => false,
      "level_5" => false,
      "level_4" => false,
      "level_3" => false,
      "level_2" => false,
      "level_1" => false,
      "level_0" => false,
      "edit_others_pages" => false,
      "edit_published_pages" => false,
      "publish_pages" => false,
      "delete_pages" => false,
      "delete_others_pages" => false,
      "delete_published_pages" => false,
      "delete_posts" => false,
      "delete_others_posts" => false,
      "delete_published_posts" => false,
      "delete_private_posts" => false,
      "edit_private_posts" => false,
      "read_private_posts" => false,
      "delete_private_pages" => false,
      "edit_private_pages" => false,
      "read_private_pages" => false,
      "delete_users" => false,
      "create_users" => false,
      "unfiltered_upload" => false,
      "edit_dashboard" => false,
      "update_plugins" => false,
      "delete_plugins" => false,
      "install_plugins" => false,
      "update_themes" => false,
      "install_themes" => false,
      "update_core" => false,
      "list_users" => false,
      "remove_users" => false,
      "promote_users" => false,
      "edit_theme_options" => false,
      "delete_themes" => false,
      "export" => false
    ];

  protected function __construct(array $args = [])
  {
    /** update default private variables */
    if (isset($args['rest_api'])) {
      $this->rest_api = self::replace_val_in_array($this->rest_api, $args['rest_api']);
    }
    /** if namespace is empty set default */
    if (!$this->rest_api['namespace']) $this->rest_api['namespace'] = explode('.', parse_url(WP_HOME)['host'])[0];

    /**
     * add REST API User Role
     * deny access to WordPress backend
     */
    add_role('rest_api_user', 'REST API User', $this->capabilities);
    /** clear session data in database */
    add_action('init', function () {
      $user = wp_get_current_user();
      if (!empty($user->roles) && in_array('rest_api_user', $user->roles, true)) {
        // delete session token data in database
        $sessions  = \WP_Session_Tokens::get_instance(get_current_user_id());
        $token = wp_get_session_token();
        $sessions->destroy_others($token);
        // log current user out
        wp_logout();
      }
    });

    /**
     * remove default REST API endpoints
     * 
     * test:
     * http://api.domain-name.kitt/wp/index.php/wp-json/wp/v2
     */
    if ($this->rest_api['remove_default']) {
      add_filter('rest_endpoints', function ($endpoints) {

        foreach ($endpoints as $route => $endpoint) {
          if (0 === stripos($route, '/wp/')) {
            unset($endpoints[$route]);
          }
        }

        return $endpoints;
      });
    }
    /** 
     * register custom REST API
     * 
     * test:
     * http://api.domain-name.kitt/wp/index.php/wp-json/namespace/route
     *
     * shorthand defined in web/.htaccess:
     * http://api.domain-name.kitt/wp-json/namespace/route
     */
    add_action('rest_api_init', function () {
      /**
       * remove default REST API response headers
       * remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
       * 
       * rewrite REST API response headers
       */
      add_filter('rest_pre_serve_request', function ($value) {
        foreach ($this->rest_api['headers'] as $header) {
          header($header);
        }

        return $value;
      });
      /**
       * register REST routes
       * 
       * functions.php
       * modifing default args
       * $kitt_instance->rest_routes['route'][0]['args']['param'] = ['default' => 'value']
       * 
       * add route
       * extend $this->rest_routes array
       * e.g.
       * $kitt_instance->rest_routes['posts'][] = [
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
      foreach ($this->rest_routes as $rest_key => $rest_val) {

        $routes = [
          'methods'  => $rest_val['methods'],
          'callback' => [
            $this,
            $rest_val['callback']
          ],
          'permission_callback' => function () use ($rest_val) {
            /** check if User is REST API User */
            return in_array($rest_val['permission_callback'], wp_get_current_user()->roles);
          },
          'args' => $rest_val['args']
        ];

        register_rest_route($this->rest_api['namespace'], '/' . $rest_key, $routes);
      }
    });
    /**
     * edit JWT authentication token object
     */
    add_filter('jwt_auth_token_before_sign', function (array $data, $user) {
      /** modify expiration time of token */
      $data['exp'] = $this->rest_api['token']['expiration_time'];
      return $data;
    }, 10, 2);
    /**
     * edit JWT authentication token object
     */
    add_filter('jwt_auth_token_before_dispatch', function (array $data, object $user) {
      $response = [];

      $response['token'] = $data['token'];
      $response['user'] = $data['user_nicename'];
      $response['role'] = $user->roles[0];

      return $response;
    }, 10, 2);
    /**
     * edit JWT allow headers object
     */
    add_filter('jwt_auth_cors_allow_headers', function (string $headers) {
      $headers = $this->rest_api['token']['header'];
      return $headers;
    }, 10, 2);
  }

  /** add methods to instance */
  public function __call($method, $args)
  {
    if (isset($this->$method)) {
      $func = $this->$method;
      return call_user_func_array($func, $args);
    }
  }

  public static function get_posts_API(\WP_REST_Request $request)
  {
    $params = $request->get_params();
    $default = $request->get_default_params();

    /* update posts default arguments with request data */
    $args = self::replace_val_in_array($default, $params);
    $data = get_posts($args);

    $response = new \WP_REST_Response($data, 200);

    return $response;
  }

  public static function get_pages_API(\WP_REST_Request $request)
  {
    $params = $request->get_params();
    $default = $request->get_default_params();

    /* update pages default arguments with request data */
    $args = self::replace_val_in_array($default, $params);
    $data = get_pages($args);

    $response = new \WP_REST_Response($data, 200);

    return $response;
  }

  public static function get_attachments_API(\WP_REST_Request $request)
  {
    $params = $request->get_params();
    $default = $request->get_default_params();

    /* update attachments default arguments with request data */
    $args = self::replace_val_in_array($default, $params);
    $query_media = new \WP_Query($args);

    $media = [];
    $image_sizes = get_intermediate_image_sizes();
    foreach ($query_media->posts as $post) {
      $post->image_sizes = [];
      foreach ($image_sizes as $value) {
        $obj = (object) [
          $value => wp_get_attachment_image_src(
            $post->ID,
            $value
          )
        ];
        $post->image_sizes[] = $obj;
      }
      $media[] = $post;
    }

    $response = new \WP_REST_Response($media, 200);

    return $response;
  }

  public static function get_comments_API(\WP_REST_Request $request)
  {
    $params = $request->get_params();
    $default = $request->get_default_params();

    /* update comments default arguments with request data */
    $args = self::replace_val_in_array($default, $params);
    $comments = get_comments($args);

    $response = new \WP_REST_Response($comments, 200);

    return $response;
  }

  public static function get_terms_API(\WP_REST_Request $request)
  {
    $params = $request->get_params();
    $default = $request->get_default_params();

    /* update terms default arguments with request data */
    $args = self::replace_val_in_array($default, $params);
    $data = get_terms($args);

    $response = new \WP_REST_Response($data, 200);

    return $response;
  }

  public static function get_menus_API(\WP_REST_Request $request)
  {
    $params = $request->get_params();
    $default = $request->get_default_params();

    /* update menus default arguments with request data */
    $args = self::replace_val_in_array($default, $params);
    $locations = get_nav_menu_locations();
    $data = [];

    if ($args['all_menus']) {

      foreach ($locations as $key => $val) {
        $args['theme_location'] = $key;
        $data[$key]['menu'] = wp_nav_menu($args);
        $data[$key]['meta'] = wp_get_nav_menu_object($val);
      }
    } else {

      $key = ($args['theme_location'] !== '') ? $args['theme_location'] : 'theme_location';
      $data[$key]['menu'] = wp_nav_menu($args);

      $meta = false;
      if ($args['theme_location'] !== '') $meta = $locations[$args['theme_location']];
      if ($args['menu'] !== '') $meta = $args['menu'];

      $data[$key]['meta'] = ($meta) ? wp_get_nav_menu_object($meta) : false;
    }

    $response = new \WP_REST_Response($data, 200);

    return $response;
  }

  public static function get_fields_API(\WP_REST_Request $request)
  {
    $params = $request->get_params();
    $default = $request->get_default_params();

    /* update fields default arguments with request data */
    $args = self::replace_val_in_array($default, $params);
    $data = [];

    if (is_array($args['fields'])) {

      foreach ($args['fields'] as $key => $value) {
        $data[] = get_fields($value);
        $data[$key]['ID'] = $value;
      }
    } else {

      $data[0]['ID'] = $args['ID'];
      $data[] = get_fields($args['ID']);
    }

    $response = new \WP_REST_Response($data, 200);

    return $response;
  }

  public static function get_wpdb_API(\WP_REST_Request $request)
  {
    $self = self::get_instance();
    $params = $request->get_params();
    $default = $request->get_default_params();

    /* update wpdb default arguments with request data */
    $args = self::replace_val_in_array($default, $params);
    $sql_query = $args['SQL'];

    if ($sql_query) {
      /** SQL query must start with SELECT [a-zA-Z,]+ FROM [a-zA-Z,]+ */
      if (!preg_match('/^SELECT\s[\w,]+\sFROM\s[\w,]+\sWHERE\s[^(][\w\s,\'%|]+$/', $sql_query)) {
        return new \WP_Error('invalid-request', "The following SQL query pattern is required > SELECT [a-zA-Z,]+ FROM [a-zA-Z,]+ WHERE [^(][a-zA-Z\s,\'%|]+ ... ", array('status' => 400 /* Bad Request */));
      }

      /** deny access for wp_usermeta and wp_users */
      if (str_contains($sql_query, 'wp_usermeta') || str_contains($sql_query, 'wp_users')) {
        $message = (str_contains($sql_query, 'wp_usermeta')) ? 'wp_usermeta' : 'wp_users';
        return new \WP_Error('invalid-request', 'Acces denied for ' . $message, array('status' => 400 /* Bad Request */));
      }
    }

    /** if custom SQL query is empty */
    if (!$sql_query || empty($sql_query)) {

      $conditions = '';
      $where = false;
      $group_by = $args['group_by'];
      $order = $args['order'];

      foreach ($args as $key => $value) {

        if ($key === 'order') {
          $order = $value;
          continue;
        }
        if ($key === 'group_by') {
          $group_by = $value;
          continue;
        }

        if ($value === '' || $value === false) continue;
        if ($where === false) {
          $conditions .= "WHERE $key REGEXP '$value' ";
          $where = true;
          continue;
        }

        $conditions .= "AND $key REGEXP '$value' ";
      }

      $sql_query = "SELECT * FROM wp_posts {$conditions} GROUP BY {$group_by} {$order};";
    }

    $data = $self->wpdb->get_results($sql_query, OBJECT);
    /** unserialize SQL values  */
    $data = self::unserialize_SQL_value($data);

    $response = new \WP_REST_Response($data, 200);

    return $response;
  }

  public static function send_email_API(\WP_REST_Request $request)
  {
    $params = $request->get_params();
    $default = $request->get_default_params();

    /* update menus default arguments with request data */
    $args = self::replace_val_in_array($default, $params);

    /** instantiation and passing `true` enables exceptions */
    $mail = new PHPMailer(true);
    try {

      /** server settings */
      if ($args['debug']) $mail->SMTPDebug = SMTP::DEBUG_SERVER;
      $mail->isSMTP();
      $mail->Host = $args['host'];
      $mail->SMTPAuth = $args['SMTP_auth'];
      $mail->Username = $args['username'];
      $mail->Password = $args['password'];
      $mail->SMTPSecure = $args['SMTP_secure'];
      $mail->Port = $args['port'];


      $mail->setFrom($args['set_from']['address'], $args['set_from']['name']);
      /** add recipients */
      foreach ($args['add_address'] as $recipient) {
        $mail->addAddress($recipient['address'], $recipient['name']);
      }

      if ($args['add_reply_to']) $mail->addReplyTo($args['add_reply_to']['address'], $args['add_reply_to']['name']);
      if ($args['add_cc']) $mail->addCC($args['add_cc']);
      if ($args['add_bcc']) $mail->addBCC($args['add_bcc']);

      /** add attachments */
      if (!empty($args['add_attachment'])) {
        foreach ($args['add_attachment'] as $attachment) {
          $mail->addAttachment($attachment['path'], $attachment['name']);
        }
      }

      /** add content */
      $mail->isHTML(true);
      $mail->Subject = $args['subject'];
      $mail->Body = $args['body'];
      $mail->AltBody = $args['alt_body'];

      /** send email */
      $mail->send();

      /** return message */
      $return = [
        'status' => 200,
        'message' => 'Message has been sent'
      ];
    } catch (Exception $e) {
      /** return error */
      $return = [
        'status' => 400,
        'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"
      ];
    }

    $response = new \WP_REST_Response($return, 200);

    return $response;
  }
}
