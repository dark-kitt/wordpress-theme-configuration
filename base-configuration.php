<?php

/**
 * Plugin Name: Base WP configuration MU-Plugin
 * Description: Configuration Plugin for WordPress Back-End.
 * Version:     0.1
 * Author:      KITT digital
 * Author URI:  https://www.kitt.digital
 * Text Domain: wordpres-theme-configuration
 */

namespace KiTT;

require_once __DIR__ . '/traits/helper.php';
require_once __DIR__ . '/traits/comments.php';
require_once __DIR__ . '/traits/attachment.php';
require_once __DIR__ . '/traits/page.php';
require_once __DIR__ . '/traits/post.php';
require_once __DIR__ . '/traits/menu.php';
require_once __DIR__ . '/traits/meta-box.php';
require_once __DIR__ . '/traits/company-settings.php';
require_once __DIR__ . '/traits/custom-post-type.php';
require_once __DIR__ . '/traits/rest-api.php';

/** required to install languages */
require_once ABSPATH . 'wp-admin/includes/translation-install.php';

class ThemeSetUp
{
    public
        $theme_directory,
        $theme_url,
        $plugin_directory,
        $plugin_url;

    private
        $set_up = [
            'favicon' => null,
            'login_logo' => null,
            'login_logo_url' => null,
            'admin_bar_logo' => null,
            'permalink_structure' => '/%postname%/',
            'default_user_role' => 'editor',
            'remove_welcome' => false,
            'start_of_week' => 1,
            'timezone_string' => 'Europe/Berlin',
            'time_format' => 'H:i',
            'date_format' => 'd/m/Y',
            'install_languages' => [],
            'ACF' => [
                'WYSIWYG_BG_color' => true,
                'save_load' => [
                    'JSON_save' => null,
                    'JSON_load' => null,
                    'AutoSync_JSON_save' => null,
                    'AutoSync_JSON_load' => null,
                    'AutoSync_PHP_save' => null,
                    'AutoSync_PHP_load' => null
                ],
                'google_api_key' => false
            ],
            'company_settings' => true
        ];

    /** @var static singleton instance */
    private static
        $instance = null;

    use
        Helper,
        Comments,
        Attachment,
        Page,
        Post,
        Menu,
        MetaBoxes,
        CompanySettings,
        CustomPostType,
        REST_API {
            Comments::__construct as public comments;
            Attachment::__construct as public attachment;
            Page::__construct as public page;
            Post::__construct as public post;
            Menu::__construct as public menu;
            MetaBoxes::__construct as public meta_boxes;
            CompanySettings::__construct as public company_settings;
            CustomPostType::__construct as public custom_post_type;
            REST_API::__construct as public REST_API;
        }

    protected function __construct()
    {
        $this->theme_directory = get_template_directory();
        $this->theme_url = get_template_directory_uri();

        $this->plugin_directory = WPMU_PLUGIN_DIR . '/' . basename(__DIR__);
        $this->plugin_url = WPMU_PLUGIN_URL . '/' . basename(__DIR__);

        if (!file_exists($this->theme_directory) && !is_dir($this->theme_directory)) mkdir($this->theme_directory);
    }

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    final public function set_up_theme(\wpdb $wpdb, \WP_Rewrite $wp_rewrite, string $pagenow, array $args = [])
    {
        $self = self::get_instance();

        $self->wpdb = $wpdb;
        $self->wp_rewrite = $wp_rewrite;
        $self->pagenow = $pagenow;

        /** update default private variables */
        if (isset($args['set_up'])) {
            $this->set_up = self::replace_val_in_array($this->set_up, $args['set_up']);
        }

        /**
         * custom favicon and admin bar logo
         * located in /themes/your-theme/img
         */
        add_action('admin_head', [$this, 'set_wp_admin_styles']);

        /**
         * custom login logo and favicon
         * located in /themes/your-theme/img
         */
        add_filter('login_enqueue_scripts', [$this, 'custom_login_style']);

        /** custom login URL -> get_bloginfo('url') */
        add_filter('login_headerurl', [$this, 'custom_login_logo_url']);

        /**
         * enqueue
         * /css/style.css
         * /js/functions.js
         */
        add_action('admin_enqueue_scripts', [$this, 'enqueue_styles_and_scripts']);

        /** call functions / methods on init */
        add_filter('init', [$this, 'base_init']);

        add_action('after_setup_theme', function () use ($wp_rewrite, $self) {
            /** update permalink structure after install */
            $wp_rewrite->set_permalink_structure($self->set_up['permalink_structure']);
            $wp_rewrite->flush_rules(true);

            /** set default user role */
            update_option('default_role', $self->set_up['default_user_role']);

            /** 1 = Mon, 2 = Thu ... 7 = Sun */
            update_option('start_of_week', $self->set_up['start_of_week']);
            /** 
             * list of Supported Timezones:
             * https://www.php.net/manual/en/timezones.php
             */
            update_option('timezone_string', $self->set_up['timezone_string']);
            update_option('time_format', $self->set_up['time_format']);
            update_option('date_format', $self->set_up['date_format']);

            /** install languages */
            $self->install_languages($self->set_up['install_languages']);

            /** load theme translations */
            load_theme_textdomain('wp-base-configuration', WP_LANG_DIR);
        });

        /** on ACF init */
        add_action('acf/init', [$this, 'acf_init']);

        /** set up PHPMailer for user registration */
        add_action('phpmailer_init', [$this, 'set_up_smtp_email']);

        /** init MetaBoxes trait */
        $this->meta_boxes();

        /** init CompanySettings trait */
        if ($this->set_up['company_settings']) $this->company_settings();

        return self::$instance;
    }

    public static function base_init()
    {
        $self = self::get_instance();
        /** remove welcome panel */
        if ($self->set_up['remove_welcome']) remove_action('welcome_panel', 'wp_welcome_panel');
    }

    public static function acf_init()
    {
        $self = self::get_instance()->set_up;
        /**
         * ACF callback function to filter the MCE settings
         * add background color button to WYSIWYG editor
         */
        add_filter('mce_buttons_2', function ($buttons) use ($self) {
            if ($self['ACF']['WYSIWYG_BG_color']) array_unshift($buttons, 'backcolor');
            return $buttons;
        });

        foreach ($self['ACF']['save_load'] as $path) {
            if ($path) {
                if (!file_exists($path)) mkdir($path, 0777, true);
            }
        }

        /** change ACF JSON save / load folder */
        (!$self['ACF']['save_load']['JSON_save']) ?: acf_update_setting('save_json', $self['ACF']['save_load']['JSON_save']);
        (!$self['ACF']['save_load']['JSON_load']) ?: acf_update_setting('load_json', array($self['ACF']['save_load']['JSON_load']));

        /** change AutoSync ACF JSON save / load path */
        (!$self['ACF']['save_load']['AutoSync_JSON_save']) ?: acf_update_setting('acfe/json_save', $self['ACF']['save_load']['AutoSync_JSON_save']);
        (!$self['ACF']['save_load']['AutoSync_JSON_load']) ?: acf_update_setting('acfe/json_load', array($self['ACF']['save_load']['AutoSync_JSON_load']));

        /** change AutoSync ACF PHP save / load path */
        (!$self['ACF']['save_load']['AutoSync_PHP_save']) ?: acf_update_setting('acfe/php_save', $self['ACF']['save_load']['AutoSync_PHP_save']);
        (!$self['ACF']['save_load']['AutoSync_PHP_load']) ?: acf_update_setting('acfe/php_load', array($self['ACF']['save_load']['AutoSync_PHP_load']));

        /** set Google API key */
        if ($self['ACF']['google_api_key']) acf_update_setting('google_api_key', $self['ACF']['google_api_key']);
    }

    public static function set_wp_admin_styles()
    {
        $self = self::get_instance();

        $logo = ($self->set_up['admin_bar_logo']) ? $self->set_up['admin_bar_logo'] : $self->plugin_url . '/img/wp-admin-bar-logo.svg';
        $favicon = ($self->set_up['favicon']) ? $self->set_up['favicon'] : $self->plugin_url . '/img/wp-favicon.svg';

        /** admin bar logo and favicon */
        print '<style rel="stylesheet" type="text/css" media="screen">
                    #wp-admin-bar-wp-logo span.ab-icon {
                        width: 22px !important;
                        height: 100% !important;
                        padding: 0 !important;
                        margin-right: 0 !important;
                    }

                    #wp-admin-bar-wp-logo span.ab-icon::before {
                        position: absolute;
                        left: 10%;
                        top: 50% !important;
                        -webkit-transform: translateY(-50%);
                        -moz-transform: translateY(-50%);
                        -ms-transform: translateY(-50%);
                        -o-transform: translateY(-50%);
                        transform: translateY(-50%);
                        content: "" !important;
                        width: 90%;
                        height: 90%;
                        background: transparent url("'  . $logo . '") center/90% no-repeat;
                    }
                </style>
                <link rel="shortcut icon" type="image/x-icon" href="' . $favicon . '" />';
    }

    public static function custom_login_logo_url()
    {
        $self = self::get_instance();
        return ($self->set_up['login_logo_url']) ? $self->set_up['login_logo_url'] : get_bloginfo('url');
    }

    public static function custom_login_style()
    {
        $self = self::get_instance();

        $logo = ($self->set_up['login_logo']) ? $self->set_up['login_logo'] : $self->plugin_url . '/img/wp-login-logo.svg';
        $favicon = ($self->set_up['favicon']) ? $self->set_up['favicon'] : $self->plugin_url . '/img/wp-favicon.svg';
        /** login logo and favicon */
        print '<style rel="stylesheet" type="text/css" media="screen">
                    #login h1 a, login h1 a:focus, login h1 a:active {
                        background: rgba(0,0,0,0) url("'  . $logo . '") center/360px 74px no-repeat;
                        width: 100%;
                        padding: 10px 0;
                        outline: none;
                        box-shadow: none;
                    }
                </style>
                <link rel="shortcut icon" type="image/x-icon" href="' . $favicon . '" />';
    }

    public static function enqueue_styles_and_scripts(string $hook)
    {
        $self = self::get_instance();

        /**
         * scripts in specific hook
         * if ($hook !== 'upload.php') {
         *    wp_enqueue_script('functions', '/functions.js');
         * }
         */
        wp_enqueue_style('fancybox-styles', 'https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css', [], false, 'screen');
        wp_enqueue_style('KiTT-styles', $self->plugin_url . '/css/styles.css', [], false, 'screen');

        wp_enqueue_script('fancybox-js', 'https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js', [], false, true);
        wp_register_script('KiTT-functions', $self->plugin_url . '/js/functions.js', ['jquery'], false, true);
        /** transfer information from PHP to JS */
        $global_var = [
            'ajaxurl'   => admin_url('admin-ajax.php'),
            'nonce'  => wp_create_nonce('KiTT-functions-post'),
            'plugin_location' => $self->plugin_url
        ];
        wp_localize_script('KiTT-functions', 'KiTT_WP_DATA', $global_var);
        wp_enqueue_script('KiTT-functions');
    }

    /**
     * install backend languages
     * 
     * list of available translations
     * https://translate.wordpress.org/
     * 
     * to remove a translation
     * delete all lang-files in
     * /web/storage/lang directory
     */
    public static function install_languages(array $languages)
    {

        if (empty($languages)) return false;

        foreach ($languages as $lang_val) if (file_exists(WP_LANG_DIR . '/' . $lang_val . '.mo')) return false;

        $translations = wp_get_available_translations();

        foreach ($languages as $lang_val) {

            if (isset($translations[$lang_val])) {

                /**
                 * based on thagxt
                 * https://gist.github.com/thagxt/d9b4388156aeb7f1d66b108d728470d2
                 */
                $url = $translations[$lang_val]['package'];
                $zipFile = constant('WP_CACHE_DIR') . '/wordpress-language-package.zip';
                $extractDir = WP_LANG_DIR;
                $zipResource = fopen($zipFile, 'w');

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_FAILONERROR, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                // curl_setopt($ch, CURLOPT_BINARYTRANSFER,true); // deprecated intelephense(1007)
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
                curl_setopt($ch, CURLOPT_FILE, $zipResource);

                $page = curl_exec($ch);

                if(!$page) {
                    echo 'Error :- ' . curl_error($ch);
                }

                curl_close($ch);

                /* Open the Zip file */
                $zip = new \ZipArchive;
                $extractPath = $extractDir;

                if($zip->open($zipFile) != 'true'){
                    echo 'Error :- Unable to open the Zip File';
                } 

                /* Extract Zip File */
                $zip->extractTo($extractPath);
                $zip->close();

                unlink($zipFile);
            }
        }
    }

    public static function set_up_smtp_email($phpmailer)
    {
        if (constant('SMTP_HOST') &&
            constant('SMTP_AUTH') &&
            constant('SMTP_PORT') &&
            constant('SMTP_SECURE') &&
            constant('SMTP_USERNAME') &&
            constant('SMTP_PASSWORD') &&
            constant('SMTP_FROM') &&
            constant('SMTP_FROMNAME')) {

            $phpmailer->isSMTP();
            $phpmailer->Host = constant('SMTP_HOST');
            $phpmailer->SMTPAuth = constant('SMTP_AUTH');
            $phpmailer->Port = constant('SMTP_PORT');
            $phpmailer->SMTPSecure = constant('SMTP_SECURE');
            $phpmailer->Username = constant('SMTP_USERNAME');
            $phpmailer->Password = constant('SMTP_PASSWORD');
            $phpmailer->From = constant('SMTP_FROM');
            $phpmailer->FromName = constant('SMTP_FROMNAME');
        }
    }
}
