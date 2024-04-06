# **WordPress Theme Configuration**

Project: [Part 1](https://github.com/dark-kitt/wordpress-boilerplate/tree/main), [**Part 2**](https://github.com/dark-kitt/wordpress-theme-configuration), [Part 3](https://github.com/dark-kitt/wordpress-theme-vue)

---

## Introduction

A base WordPress theme configuration written as MU-Plugin, which includes multiple WordPress hooks to improve your theme. Configure the selected theme in the **_functions.php_** file with the listed methods below.

The [Autoloader MU-Plugin](https://github.com/dark-kitt/wordpress-mu-plugin-autoloader) is required to load this plugin by WordPress. After **composer update** copy the **_mu-plugin-autoloader.php_** file inside of the _`/mu-plugins`_ directory, to load the WordPress Theme Configuration MU-Plugin automatically. Note: The REST API uses the **JWT Authentication for WP REST API** to secure the API endpoints.

General WordPress configurations are placed in the **_base-configuration.php_** file. For each type / part of WordPress, you can find a PHP Trait in the _`/traits`_ directory. E.g.: attachment.php file for Media Library settings, post.php file for the Post settings... etc.

**Note: The Gutenberg editor is always disabled!** If WP_HOME is different than WP_SITEURL the Gutenberg editor throws multiple errors. If you are interested, take a look at [Github WordPress / gutenberg (issue 1761).](https://github.com/WordPress/gutenberg/issues/1761)

Use [ACF (Advanced Custom Fields)](https://www.advancedcustomfields.com/) or install the [Classic Editor Plugin](https://wordpress.org/plugins/classic-editor/), to add content to each post / page.

### Methods

- [get_instance()](#get_instance)
- [set_up_theme()](#set_up_theme-wpdb-wp_rewrite-pagenow-array-args--defaults-)
- [post()](#post-array-args--defaults-)
- [page()](#page-array-args--defaults-)
- [attachment()](#attachment-array-args--defaults-)
- [comments()](#comments-array-args--defaults-)
- [menu()](#menu-array-args--defaults-)
- [custom_post_type()](#custom_post_type-array-args--defaults-)
- [REST_API()](#rest_api-array-args--defaults-)

Control each method with the passed **arguments** array or use the default settings. If you are interested in the defaults, want to check out each argument, or set your own custom defaults, take a look inside the trait files in the _`/trait`_ directory.

### Requirements

- [PHP: ^7.\*](https://www.php.net/manual/de/mysql-xdevapi.installation.php)
- [WordPress: ^5.5\*](https://wordpress.org/support/article/how-to-install-wordpress/)
- [WordPress MU-Plugin Autoloader](https://github.com/dark-kitt/wordpress-mu-plugin-autoloader)
- [JWT Authentication for WP REST API](https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/)

**to maintain content (optional)**

- [ACF (Advanced Custom Fields)](https://www.advancedcustomfields.com/)
- [Classic Editor Plugin](https://wordpress.org/plugins/classic-editor/)

Note: If you need an easy way to install this project with all requirements take a look at [part 1](#) of the **[WordPress Boilerplate](#)** project.

---

## Installation

Edit your composer.json file and insert the following lines to install the **WordPress base configuration MU-Plugin** and the required **Autoloader MU-Plugin**. Don't forget to insert the **JWT Authentication for WP REST API** if you want to use the **`REST_API()`** method. Run **composer update** to fetch the required files.

```json
...
"repositories": [{
        "type": "composer",
        "url": "https://wpackagist.org"
    },
    {
        "type": "vcs",
        "url": "https://#"
    },
    {
        "type": "vcs",
        "url": "https://#"
    }
]
...
"require": {
    "dark-kitt/wordpress-theme-configuration": "dev-main",
    "dark-kitt/wordpress-mu-plugin-autoloader": "dev-main",
    "wpackagist-plugin/jwt-authentication-for-wp-rest-api": "^1.2.6"
}
...
```

- copy-paste the code inside of **_composer.json_** file
- run **composer update**
- configure the plugin with the **_functions.php_** file in the _`/themes/your-theme`_ directory

Note: For a specific commit of your VCS Repo `"require": { "vendor/repo_name": "dev-main#eec8698" }` (branch#commit).

**composer cmds**

```shell
composer install
composer update

composer clear-cache
composer show -i (installed packages)
```

**Autoloader for WordPress MU-Plugins**

The **[wordpress-mu-plugin-autoloader](#)** is a WordPress MU-Plugin based on [richardtape/subdir-loader.php](https://gist.github.com/richardtape/05c70849e949a5017147) Github Gist. Copy the **_mu-plugin-autoloader.php_** file inside of the _`/mu-plugins`_ directory, to load MU-Plugins automatically.

---

## Methods [ Arguments ]

General WordPress configurations are placed in the **_base-configuration.php_** file. For each type / part of WordPress, you can find a PHP Trait in the _`/traits`_ directory. Check out the **_example.function.php_** file to get knowledge about the usage and how you can configure your theme inside of the **_functions.php_** file in your _`/themes/your-theme`_ directory.

### get_instance()

Executes the singleton instance and return itself.

```PHP
$kitt_instance = KiTT\ThemeSetUp::get_instance();
```

### set_up_theme( $wpdb, $wp_rewrite, $pagenow, array $args = defaults )

Set the defined globals `$wpdb`, `$wp_rewrite` and `$pagenow` and call the main theme set up method. This method will also call the `meta_box();` and the `company_settings();` methods, which includes the custom **SEO** meta box, the custom **Homepage** meta box, the custom **Error Page** meta box and the **Company** menu page. The `set_up_theme()` method gives you the ability to install languages for WordPress and sets further settings for **ACF (Advanced Custom Fields)**. All default arguments are listed below.

```PHP
global $wpdb,
        $wp_rewrite,
        $pagenow;

$kitt_instance = KiTT\ThemeSetUp::get_instance();
$kitt_instance->set_up_theme(
    $wpdb, /** reqiured */
    $wp_rewrite, /** reqiured */
    $pagenow, /** reqiured */
    [
        'set_up' => [
            /** custom favicon, logos and login logo url */
            'favicon' => $kitt_instance->theme_url . '/img/wp-favicon.png',
            'login_logo' => $kitt_instance->theme_url . '/img/wp-login-logo.svg',
            'login_logo_url' => WP_HOME,
            'admin_bar_logo' => $kitt_instance->theme_url . '/img/wp-admin-bar-logo.svg',
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
            'company_settings' => true
        ]
    ]
);
```

### post( array $args = defaults )

Use this method to modify the **default post section**. All default arguments are listed below.

Note: Use [ACF (Advanced Custom Fields)](https://www.advancedcustomfields.com/) or install the [Classic Editor Plugin](https://wordpress.org/plugins/classic-editor/), to add content to each post.

```PHP
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
```

### page( array $args = defaults )

Use this method to modify the **default page section**. All default arguments are listed below.

Note: Use [ACF (Advanced Custom Fields)](https://www.advancedcustomfields.com/) or install the [Classic Editor Plugin](https://wordpress.org/plugins/classic-editor/), to add content to each page.

```PHP
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
            'name' => 'Pages',
            'singular_name' => 'Page',
            'menu_name' => 'Pages',
            'all_items' => 'All Pages',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New Page',
            'edit' => 'Edit',
            'edit_item' => 'Edit Page',
            'new_item' => 'New Page',
            'view' => 'View',
            'view_item' => 'View Page',
            'search_items' => 'Search Pages',
            'not_found' => 'No Pages found',
            'not_found_in_trash' => 'No Pages found in trash'
        ]
    ]
]);
```

### attachment( array $args = defaults )

Set up options for the **Media Library**. All default arguments are listed below.

Note: If another theme was previously activated and the **new upload directory** is different, check the project for the old upload directory and when it is unnecessary delete it manually.

Note: That this method can add also the "Find Duplicates" attachments modal, which goes through the database and compares images to find duplicates.

```PHP
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
```

### comments( array $args = defaults )

Remove completely **Comments** from WordPress.

```PHP
$kitt_instance->comments([
    /** removes completely the default comments section */
    'remove_comments' => true
]);
```

### menu( array $args = defaults )

This method will **replace** the Menu (**_nav-menus.php_**) section from an **Appearance** submenu page to a **top-level** page underneath the Pages section. Additionally, the method adds the **support** for the role **editor** to edit the Menu section (=> editors can edit menus).

```PHP
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
```

### custom_post_type( array $args = defaults )

This is a custom **register_post_type** method, which includes some additional options. A **slug is required** to register custom **menu locations** and to use the custom **homepage meta box**. All default arguments are listed below.

```PHP
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
            'name' => 'Pages EN',
            'singular_name' => 'Page EN',
            'menu_name' => 'Pages EN',
            'all_items' => 'All EN Pages',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New EN Page',
            'edit' => 'Edit',
            'edit_item' => 'Edit EN Page',
            'new_item' => 'New EN Page',
            'view' => 'View',
            'view_item' => 'View EN Page',
            'search_items' => 'Search EN Pages',
            'not_found' => 'No EN Pages found',
            'not_found_in_trash' => 'No EN Pages found in trash',
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
```

### REST_API( array $args = defaults )

This **`REST_API()`** method uses the **JWT Authentication for WP REST API** to secure the API endpoints. It uses also **PHP Mailer** to send emails via a REST route. In the examples below, you can see how you can optionally use this instance to configure PHP Mailer for your forms. You can also send the server **settings** via a POST body request with **axios**, **jQuery** etc.

If you configure the JWT Authentication, don't forget to create a `user` (with the role -> `rest_api_user`) and test the API requests. If you are using Postman, send the `username` and `password` as post body (raw/json), to retrieve the token. The `\WP_REST_Server::CREATABLE` or other methods are described on [developer.wordpress.org](https://developer.wordpress.org/reference/classes/wp_rest_server/). Please take a deeper look inside the **_rest-api.php_** file to get more information about the handling.

Use the existing instance to push additional methods (`$kitt_instance->rest_routes['posts'][] = []`) to existing routes or register totally new routes (`$kitt_instance->rest_routes['new_route'][] = []`) to your REST API configuration. You can find an example below, which creates a Vue Router object.

Note: The **axios GET** (READABLE) request can not send data via the body.

**define your custom REST-API**

```PHP
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
```

**EXAMPLE: configuration for PHP Mailer in the functions.php file**

```PHP
/**
 * update email route arguments
 * set server settings
 *
 * update values with WP constants
 * or set your custom settings
 */
$kitt_instance->rest_routes['email'][0]['args']['host'] = ['default' => constant('SMTP_HOST')]; // 'smtp.gmail.com'
$kitt_instance->rest_routes['email'][0]['args']['SMTP_auth'] = ['default' => constant('SMTP_AUTH')]; // boolean
$kitt_instance->rest_routes['email'][0]['args']['username'] = ['default' => constant('SMTP_USERNAME')]; // 'your@username.com'
/**
 * use google app password:
 * https://support.google.com/accounts/answer/185833?hl=en
 */
$kitt_instance->rest_routes['email'][0]['args']['password'] = ['default' => constant('SMTP_PASSWORD')]; // 'app-password'
$kitt_instance->rest_routes['email'][0]['args']['SMTP_secure'] = ['default' => constant('SMTP_SECURE')]; // 'tls'
$kitt_instance->rest_routes['email'][0]['args']['port'] = ['default' => constant('SMTP_PORT')]; // 587
/** PHPMailer debug */
$kitt_instance->rest_routes['email'][0]['args']['debug'] = ['default' => false];
/**
 * email test data
 *
 * test e.g. via postman
 * send data with route -> /wp-json/namespace/email
 */
$kitt_instance->rest_routes['email'][0]['args']['set_from'] = ['default' => [
    'address' => 'from@address.com',
    'name' => 'User Joe'
]];
$kitt_instance->rest_routes['email'][0]['args']['add_address'] = ['default' => [[
    'address' => 'add_to@address.com',
    'name' => 'User Foo'
]]];
$kitt_instance->rest_routes['email'][0]['args']['add_reply_to'] = ['default' => [
    'address' => 'add_to_reply@address.com',
    'name' => 'User Bar'
]];
```

**EXAMPLE: for adding a custom REST route in the functions.php file**

```PHP
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
$kitt_instance->rest_routes['routes'][] = [
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
    'callback' => 'get_my_custom_callback', // string required!!!
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
        'SQL' => ['default' => false],
        'ID' => ['default' => ''],
        'post_title' => ['default' => ''],
        'post_status' => ['default' => 'publish|pending|draft|auto-draft|future|private'],
        'post_name' => ['default' => ''],
        'post_parent' => ['default' => ''],
        'guid' => ['default' => ''],
        'menu_order' => ['default' => ''],
        'post_type' => ['default' => 'post$|page'],
        'order' => ['default' => 'DESC'],
        'group_by' => ['default' => 'ID']
    ]
];

/**
 * add a custom REST callback method
 */
$kitt_instance->get_my_custom_callback = function (\WP_REST_Request $request) use ($kitt_instance)
{
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
```

---

**Note: The Gutenberg editor is always disabled!** If WP_HOME is different from WP_SITEURL the Gutenberg editor throws multiple errors. If you are interested, take a look at [Github WordPress / gutenberg (issue 1761).](https://github.com/WordPress/gutenberg/issues/1761)

Use [ACF (Advanced Custom Fields)](https://www.advancedcustomfields.com/) or install the [Classic Editor Plugin](https://wordpress.org/plugins/classic-editor/), to add content to each post / page.

---

---

## License

[![](https://upload.wikimedia.org/wikipedia/commons/e/e5/CC_BY-SA_icon.svg)](https://creativecommons.org/licenses/by-sa/4.0)

---

## Includes

- [dark-kitt / wordpress-mu-plugin-autoloader](https://github.com/dark-kitt/wordpress-mu-plugin-autoloader)
- [JWT Authentication for WP REST API](https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/)
