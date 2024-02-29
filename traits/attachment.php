<?php

namespace KiTT;

trait Attachment
{
    private
        $attachment = [
            'tag' => false,
            'category' => false,
            /** search duplicate attachments in Media Library */
            'search_duplicates' => true
        ],
        /**
         * list of defaulst:
         * https://developer.wordpress.org/reference/functions/get_allowed_mime_types/
         */
        $upload_mimes = [
            /** extend WP default values */
            'extend_defaults' => true,
            'svg' => 'image/svg+xml'
        ],
        $options_media = [
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
            'upload_path' => '',
            /** http://127.0.0.1/uploads */
            'upload_url_path' => ''
        ];

    protected function __construct(array $args = [])
    {
        /** update default private variables */
        if (isset($args['attachment'])) {
            $this->attachment = self::replace_val_in_array($this->attachment, $args['attachment']);
        }
        /** update upload mimes */
        if (isset($args['upload_mimes'])) {
            $updated_args = self::replace_val_in_array($this->upload_mimes, $args['upload_mimes']);
            $this->upload_mimes = array_merge($updated_args, $args['upload_mimes']);
        }
        /** update Media Library options */
        if (isset($args['options_media'])) {
            array_walk_recursive($args['options_media'], function ($media_val, $media_key) {
                if (isset($args['options_media'][$media_key])) $this->options_media[$media_key] = $media_val;
            });
        }
        /** set upload path and URL */
        if ($this->options_media['upload_path'] === '') $this->options_media['upload_path'] = constant('WP_UPLOAD_DIR');
        if ($this->options_media['upload_url_path'] === '') $this->options_media['upload_url_path'] = constant('WP_UPLOAD_URL');

        /** set custom upload mimes */
        add_filter('upload_mimes', [$this, 'set_upload_mimes']);

        /** upload filter */
        add_filter('wp_handle_upload_prefilter', [$this, 'upload_filename_filter']);

        /** disable large image scaling */
        add_filter('big_image_size_threshold', '__return_false');

        /** update attachments permalink */
        add_filter('attachment_link', function ($link, $id) {
            return wp_get_attachment_url($id) ?: false;
        }, 20, 2);

        /** set media settings */
        add_action('after_setup_theme', [$this, 'set_options_media']);

        /** call functions / methods on init */
        add_filter('init', [$this, 'attachment_init']);

        if ($this->attachment['search_duplicates']) {
            /** add modal box in Media Library -> find duplicates button */
            add_action('load-upload.php', [$this, 'attachments_modal_box']);
    
            /** ajax search duplicated attachments in database */
            add_action('wp_ajax_search_duplicate_attachments', [$this, 'search_duplicate_attachments']);
            /** ajax delete duplicated attachments in database */
            add_action('wp_ajax_delete_duplcate_attachments', [$this, 'delete_duplcate_attachments']);
        }
    }

    public static function set_upload_mimes($mimes)
    {
        $self = self::get_instance();
        /** update mimes */
        $mimes = ($self->upload_mimes['extend_defaults']) ? array_merge($mimes, $self->upload_mimes) : $self->upload_mimes;
        unset($mimes['extend_defaults']);
        return $mimes;
    }

    public static function attachment_init()
    {
        $self = self::get_instance();

        /** add tag and category meta box */
        (!$self->attachment['tag']) ?: register_taxonomy_for_object_type('post_tag', 'attachment');
        (!$self->attachment['category']) ?: register_taxonomy_for_object_type('category', 'attachment');
    }

    public static function delete_duplcate_attachments()
    {
        if (check_ajax_referer('KiTT-functions-post', 'nonce', false)) {
            if (isset($_POST['files'])) {
                foreach ($_POST['files'] as $value) {
                    wp_delete_post((float) $value);
                }
            }
        }
        exit;
    }

    public static function search_duplicate_attachments()
    {
        if (check_ajax_referer('KiTT-functions-post', 'nonce', false)) {

            $self = self::get_instance();
            /** fetch all attachments */
            $all_attachments = $self->wpdb->get_results("SELECT * FROM {$self->wpdb->posts} WHERE post_type = 'attachment'");
            $duplicates = [];

            array_walk_recursive($all_attachments, function ($value) use (&$duplicates, $self) {

                $filename = $value->post_title;
                /** get all posts where post_title is like filename except itself */
                $dup_results = $self->wpdb->get_results("SELECT * FROM {$self->wpdb->posts}
                                                            WHERE post_title LIKE '%$filename%'
                                                                AND ID != {$value->ID}");

                $exists = false;
                /** check if object of duplicate attachment already exist */
                array_walk_recursive($duplicates, function ($result) use (&$exists, $value) {

                    /** compare guid with file_src (old guid) */
                    if (property_exists($result, 'duplicate')) {
                        if ($value->guid === $result->duplicate['file_src']) $exists = true;
                    }

                    if (property_exists($result, 'reference')) {
                        if ($value->guid === $result->reference['file_src']) $exists = true;
                    }

                });
                /** if exists skip loop (continue) */
                if ($exists) return false;

                if (count($dup_results)) {

                    $reference = $value;
                    $dup_arr = [];
                    /** build result object */
                    array_walk_recursive($dup_results, function ($value) use (&$dup_arr, $reference) {

                        $base_url = wp_upload_dir()['baseurl'];
                        $base_dir = wp_upload_dir()['basedir'];

                        $ref_path = str_replace($base_url, $base_dir, $reference->guid);
                        $dup_path = str_replace($base_url, $base_dir, $value->guid);

                        $ref_size = filesize($ref_path);
                        $dup_size = filesize($dup_path);

                        /**
                         * compare filesize
                         * 1000 = 1KB tolerance
                         * if the file has been modified slightly
                         */
                        $tolerance = 1000;

                        if ($dup_size <= ($ref_size + $tolerance) && $dup_size >= ($ref_size - $tolerance)) {

                            $ref_filename = pathinfo($ref_path)['filename'];
                            $dup_filename = pathinfo($dup_path)['filename'];
                            $ref_ext = pathinfo($ref_path)['extension'];
                            $dup_ext = pathinfo($dup_path)['extension'];
                            /** duplicate result object */
                            $dup_arr[] = (object) ['duplicate' => [
                                'file' => basename($dup_path),
                                'file_name' => $dup_filename,
                                'file_ext' => $dup_ext,
                                'file_src' => $value->guid,
                                'file_id' => $value->ID,
                                'file_size' => [
                                    $dup_size,
                                    self::get_file_size($dup_size)
                                ]
                            ]];
                            /** reference result object */
                            $dup_arr[] = (object) ['reference' => [
                                'file' => basename($ref_path),
                                'file_name' => $ref_filename,
                                'file_ext' => $ref_ext,
                                'file_src' => $reference->guid,
                                'file_id' => $reference->ID,
                                'file_size' => [
                                    $ref_size,
                                    self::get_file_size($ref_size)
                                ]
                            ]];
                        }
                    });

                    /** remove multiple references in $dup_key array */
                    foreach($dup_arr as $dup_key => $dup_value) {
                        unset($dup_arr[$dup_key]);
                        if (!in_array($dup_value, $dup_arr)) $dup_arr[] = $dup_value;
                    }

                    $duplicates[] = $dup_arr;
                }
            });

            header('Content-Type: application/json');
            print json_encode($duplicates);
        }
        exit;
    }

    public static function attachments_modal_box()
    {
        add_action('admin_notices', function () {
            add_filter('esc_html', [self::get_instance(), 'set_attachments_modal_box'], 999, 2);
        });
    }

    public static function set_attachments_modal_box(string $safe_text, string $text)
    {
        if (!current_user_can('upload_files')) return $safe_text;
        if ($text === __('Media Library') && did_action('all_admin_notices')) {

            remove_filter('esc_html', [self::get_instance(), 'set_attachments_modal_box'], 999, 2);

            $safe_text = '
                <h1 class="wp-heading-inline">' . __('Media Library') . '</h1>
                <div class="KiTT-media-box">
                    <p class="KiTT-clearfix">
                        <a id="KiTT-media-duplicates-btn" title="find duplicates in database" class="page-title-action">Find Duplicates</a>
                    </p>
                </div>';
        }

        return $safe_text;
    }

    public static function upload_filename_filter(array $file)
    {

        /**
         * browser uploader BUG
         * if user click upload button without selecting a file
         */
        if ($file['name'] === '') return false;

        /** get filename */
        $filename = pathinfo($file['name'])['filename'];

        /** prevents multiple dots in filename */
        if (preg_match('/[\.]+/', $filename)) {
            $file['error'] = __('It is not allowed, to have dot/s in the filename!', 'wp-base-configuration');
            return $file;
        }
        /** prevents only dashe/s and only underscore/s as filename */
        if (preg_match('/^-+$|^_+$/', $filename)) {
            $file['error'] = __('It is not allowed, to have only dashes or only underscores in the filename!', 'wp-base-configuration');
            return $file;
        }

        /** 
         * replace beginning 
         * _ - + * / @ ; , ! ? = 
         * with null
         * 
         * add / remove - if you need
         */
        $filename = preg_replace('/^[_\-\+\*\/@;,!\?\=]+/', '', $filename);
        /**
         * replaces
         * -\d{1,4}x\d{1,4}
         * -pdf
         * -\d{1,4}x\d{1,4}-pdf
         * with null
         * 
         * because WP sets file size (-pdf)
         * at the end of each file
         */
        $filename = preg_replace('/-(?:\d{1,4}x\d{1,4}|\bpdf\b|\d{1,4}x\d{1,4}-\bpdf\b)$/', '', $filename);
        /** replaces multiple space/s with dashe/s */
        $filename = preg_replace('/\s+/', '-', $filename);
        /** replaces multiple _ - with - */
        $filename = preg_replace('/[_-]{2,}/', '-', $filename);
        /** replaces @:,; in filename with null */
        $filename = preg_replace('/[@;,]+/', '', $filename);
        /** removes special characters */
        $filename = self::replace_speacial_chars($filename);

        /** reconstruct filename */
        $file['name'] = $filename . '.' . strtolower(pathinfo($file['name'])['extension']);

        return $file;
    }

    public static function set_options_media()
    {
        array_walk_recursive(self::get_instance()->options_media, function ($opt_val, $opt_key) {
            update_option($opt_key, $opt_val);
        });
    }
}
