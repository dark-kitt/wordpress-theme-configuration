<?php

namespace KiTT;

trait CompanySettings
{

  protected function __construct()
  {
    /**
     * add custom menu page
     * company settings
     */
    add_action('admin_menu', [$this, 'company_settings_page']);

    /**
     * add company options
     */
    add_action('admin_init', [$this, 'company_settings_options']);

    /** allow editors to edit options */
    add_filter('option_page_capability_kitt_company_options', function () {
      return 'edit_others_posts';
    });

    /** ajax delete company logo */
    add_action('wp_ajax_delete_company_logo', [$this, 'delete_company_logo']);
  }

  public static function company_settings_page()
  {
    /**
     * add company settings page to top-level menu
     * 
     * menu icon info:
     * https://developer.wordpress.org/resource/dashicons/#menu
     */
    add_menu_page(
      __('Company', 'wp-base-configuration'),
      __('Company', 'wp-base-configuration'),
      'edit_others_posts',
      'company-settings',
      [
        self::get_instance(),
        'company_settings_content'
      ],
      'dashicons-admin-home',
      55
    );
  }

  public static function company_settings_content()
  {
?>
    <h1><?php _e('Company Settings', 'wp-base-configuration') ?></h1>
    <?php settings_errors(); ?>

    <form method="post" action="options.php" enctype="multipart/form-data">
      <?php settings_fields('kitt_company_options'); ?>
      <?php do_settings_sections('kitt_do_company_settings'); ?>
      <?php submit_button('Save Data', 'primary', 'submit-settings-update'); ?>
    </form>
<?php
  }

  public static function company_settings_options()
  {
    $self = self::get_instance();

    /** register company options */
    register_setting('kitt_company_options', 'kitt_company_name');
    register_setting('kitt_company_options', 'kitt_company_street');
    register_setting('kitt_company_options', 'kitt_company_zip');
    register_setting('kitt_company_options', 'kitt_company_city');
    register_setting('kitt_company_options', 'kitt_company_state');
    register_setting('kitt_company_options', 'kitt_company_country');
    register_setting('kitt_company_options', 'kitt_company_contact_name');
    register_setting('kitt_company_options', 'kitt_company_contact_email');
    register_setting('kitt_company_options', 'kitt_company_phone');
    register_setting('kitt_company_options', 'kitt_company_fax');
    register_setting('kitt_company_options', 'kitt_company_availability');
    register_setting('kitt_company_options', 'kitt_company_logo', [
      $self,
      'handle_company_logo_upload'
      /** callback to validate upload */
    ]);
    register_setting('kitt_company_options', 'kitt_company_social_media');
    register_setting('kitt_company_options', 'kitt_company_app_store');
    register_setting('kitt_company_options', 'kitt_company_play_store');
    register_setting('kitt_company_options', 'kitt_company_copyright');
    register_setting('kitt_company_options', 'kitt_company_google_maps');

    /** add company settings section */
    add_settings_section(
      'kitt_add_company_settings',
      __('Add Company Data', 'wp-base-configuration'),
      [
        $self,
        'add_company_settings_section'
      ],
      'kitt_do_company_settings'
    );
  }

  public static function add_company_settings_section()
  {
    /** company name input */
    add_settings_field('kitt_company_name', __('Company Name', 'wp-base-configuration'), function () {
      print '<input type="text" name="kitt_company_name" value="' . esc_attr(get_option('kitt_company_name')) . '" placeholder="' . __('Company Name', 'wp-base-configuration') . '">';
    }, 'kitt_do_company_settings', 'kitt_add_company_settings');

    /** company street input */
    add_settings_field('kitt_company_street', __('Street, Number', 'wp-base-configuration'), function () {
      print '<input type="text" name="kitt_company_street" value="' . esc_attr(get_option('kitt_company_street')) . '" placeholder="' . __('Street, Number', 'wp-base-configuration') . '">';
    }, 'kitt_do_company_settings', 'kitt_add_company_settings');

    /** company zip input */
    add_settings_field('kitt_company_zip', __('ZIP / Postal Code', 'wp-base-configuration'), function () {
      print '<input type="text" name="kitt_company_zip" value="' . esc_attr(get_option('kitt_company_zip')) . '" placeholder="' . __('ZIP / Postal Code', 'wp-base-configuration') . '">';
    }, 'kitt_do_company_settings', 'kitt_add_company_settings');

    /** company city input */
    add_settings_field('kitt_company_city', __('City', 'wp-base-configuration'), function () {
      print '<input type="text" name="kitt_company_city" value="' . esc_attr(get_option('kitt_company_city')) . '" placeholder="' . __('City', 'wp-base-configuration') . '">';
    }, 'kitt_do_company_settings', 'kitt_add_company_settings');

    /** company state input */
    add_settings_field('kitt_company_state', __('State / Province / Region', 'wp-base-configuration'), function () {
      print '<input type="text" name="kitt_company_state" value="' . esc_attr(get_option('kitt_company_state')) . '" placeholder="' . __('State / Province / Region', 'wp-base-configuration') . '">';
    }, 'kitt_do_company_settings', 'kitt_add_company_settings');

    /** company country input */
    add_settings_field('kitt_company_country', __('Country', 'wp-base-configuration'), function () {
      print '<input type="text" name="kitt_company_country" value="' . esc_attr(get_option('kitt_company_country')) . '" placeholder="' . __('Country', 'wp-base-configuration') . '">';
    }, 'kitt_do_company_settings', 'kitt_add_company_settings');

    /** company contact name input */
    add_settings_field('kitt_company_contact_name', __('Contact Name', 'wp-base-configuration'), function () {
      print '<input type="text" name="kitt_company_contact_name" value="' . esc_attr(get_option('kitt_company_contact_name')) . '" placeholder="' . __('Contact Name', 'wp-base-configuration') . '">';
    }, 'kitt_do_company_settings', 'kitt_add_company_settings');

    /** company contact email input */
    add_settings_field('kitt_company_contact_email', __('Contact Email', 'wp-base-configuration'), function () {
      print '<input type="text" name="kitt_company_contact_email" value="' . esc_attr(get_option('kitt_company_contact_email')) . '" placeholder="' . __('Contact Email', 'wp-base-configuration') . '">';
    }, 'kitt_do_company_settings', 'kitt_add_company_settings');

    /** company phone number input */
    add_settings_field('kitt_company_phone', __('Phone Number', 'wp-base-configuration'), function () {
      print '<input type="text" name="kitt_company_phone" value="' . esc_attr(get_option('kitt_company_phone')) . '" placeholder="' . __('Phone Number', 'wp-base-configuration') . '">';
    }, 'kitt_do_company_settings', 'kitt_add_company_settings');

    /** company fax number input */
    add_settings_field('kitt_company_fax', __('Fax Number', 'wp-base-configuration'), function () {
      print '<input type="text" name="kitt_company_fax" value="' . esc_attr(get_option('kitt_company_fax')) . '" placeholder="' . __('Fax Number', 'wp-base-configuration') . '">';
    }, 'kitt_do_company_settings', 'kitt_add_company_settings');

    /** company availability input */
    add_settings_field('kitt_company_availability', __('Availability', 'wp-base-configuration'), function () {
      print '<input type="text" name="kitt_company_availability" value="' . esc_attr(get_option('kitt_company_availability')) . '" placeholder="' . __('Monday to Friday, 08.00-18.00', 'wp-base-configuration') . '">';
    }, 'kitt_do_company_settings', 'kitt_add_company_settings');

    /** company upload logo input */
    add_settings_field('kitt_company_logo', __('Add Logo', 'wp-base-configuration'), function () {

      $logo = get_option('kitt_company_logo');

      if (is_array($logo)) {
        if (isset($logo['url'])) print '<span class="company-logo"><img src="' . $logo['url'] . '"/><i id="KiTT-delete-company-logo"></i></span>';
      }

      print '<input type="file" name="kitt_company_logo" class="button"/>';
    }, 'kitt_do_company_settings', 'kitt_add_company_settings');

    /** company social media textarea */
    add_settings_field('kitt_company_social_media', __('Social Media<br/><span class="KiTT-company-note">(separated by commas + line break)</span>', 'wp-base-configuration'), function () {
      print '<textarea type="text" rows="4" name="kitt_company_social_media" placeholder="' . __('https://facebook.com/your/company,&#10;https://linkedin.com/your/company', 'wp-base-configuration') . '">' . esc_attr(get_option('kitt_company_social_media')) . '</textarea>';
    }, 'kitt_do_company_settings', 'kitt_add_company_settings');

    /** company app store input */
    add_settings_field('kitt_company_app_store', __('App Store', 'wp-base-configuration'), function () {
      print '<input type="text" name="kitt_company_app_store" value="' . esc_attr(get_option('kitt_company_app_store')) . '" placeholder="' . __('https://apps.apple.com/your/company', 'wp-base-configuration') . '">';
    }, 'kitt_do_company_settings', 'kitt_add_company_settings');

    /** company play store input */
    add_settings_field('kitt_company_play_store', __('Play Store', 'wp-base-configuration'), function () {
      print '<input type="text" name="kitt_company_play_store" value="' . esc_attr(get_option('kitt_company_play_store')) . '" placeholder="' . __('https://play.google.com/your/company', 'wp-base-configuration') . '">';
    }, 'kitt_do_company_settings', 'kitt_add_company_settings');

    /** company copyright input */
    add_settings_field('kitt_company_copyright', __('© Copyright', 'wp-base-configuration'), function () {
      print '<input type="text" name="kitt_company_copyright" value="' . esc_attr(get_option('kitt_company_copyright')) . '" placeholder="' . __('© Copyright', 'wp-base-configuration') . '">';
    }, 'kitt_do_company_settings', 'kitt_add_company_settings');

    /** company google maps input */
    add_settings_field('kitt_company_google_maps', __('Google Maps Link', 'wp-base-configuration'), function () {
      print '<input type="text" name="kitt_company_google_maps" value="' . esc_attr(get_option('kitt_company_google_maps')) . '" placeholder="' . __('https://www.google.com/maps/place/lat,lng', 'wp-base-configuration') . '">';
    }, 'kitt_do_company_settings', 'kitt_add_company_settings');
  }

  public static function handle_company_logo_upload()
  {
    /** prevent callback on ajax delete company logo */
    if (check_ajax_referer('KiTT-functions-post', 'nonce', false)) {
      /** if ajax delete_company_logo() is called => return null  */
      if (isset($_POST['delete']) && !empty($_POST['delete'])) {
        if ((bool) $_POST['delete']) return null;
      }
      exit;
    }
    /** check if $_FILES is empty */
    if (!isset($_FILES) || empty($_FILES) || !isset($_FILES['kitt_company_logo'])) return get_option('kitt_company_logo');
    if ($_FILES['kitt_company_logo']['name'] === '') return get_option('kitt_company_logo');

    if (!function_exists('wp_handle_upload')) {
      require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    /** define upload values */
    $files = $_FILES['kitt_company_logo'];
    if ($files['name']) {
      $file = array(
        'name' => $files['name'],
        'type' => $files['type'],
        'tmp_name' => $files['tmp_name'],
        'error' => $files['error'],
        'size' => $files['size']
      );
      $attributes = wp_handle_upload($file, array(
        'test_form' => false,
        'mimes' => get_allowed_mime_types()
      ));
    }
    /** send notice on error */
    if ($attributes['error']) {
      $error_SVG = ' If it\'s an SVG file it could be that the XML declaration is missing. <? ... xml version="1.0" encoding="utf-8" ... ?>';
      add_settings_error(
        'kitt_company_options',
        'upload_error',
        __($attributes['error'] . $error_SVG, 'wp-base-configuration'),
        'error',
      );
    }
    /** delete old data */
    if ($attributes) wp_delete_file(get_option('kitt_company_logo')['file']);
    /** return new data */
    return $attributes;
  }

  public static function delete_company_logo()
  {
    if (check_ajax_referer('KiTT-functions-post', 'nonce', false)) {
      if (isset($_POST['delete']) && !empty($_POST['delete'])) {
        if ((bool) $_POST['delete']) {
          /** delete existsing file */
          wp_delete_file(get_option('kitt_company_logo')['file']);
          /** update logo data */
          update_option('kitt_company_logo', null);
        }
      }
    }
    exit;
  }
}
