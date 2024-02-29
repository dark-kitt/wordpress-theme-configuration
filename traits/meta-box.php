<?php

namespace KiTT;

trait MetaBoxes
{
    private
        $meta_box_homepage_screens = ['page'],
        $meta_box_seo_screens = ['page', 'post'];

    protected function __construct()
    {
        /**
         * add Meta Box to pages,
         * post (if slug is set) and
         * custom post types (if slug is set)
         * 
         * creates a select with all pages
         * to define the Homepage / Error Page (only published)
         */
        add_action('add_meta_boxes', [$this, 'add_custom_meta_box']);
        /**
         * set option homepage + post_type
         * to handle multiple post_type homepages
         */
        if (!get_option('kitt_option_homepage_' . 'page')) update_option('kitt_option_homepage_' . 'page', 0);
        if (!get_option('kitt_option_error_page_' . 'page')) update_option('kitt_option_error_page_' . 'page', 0);
        /** ajax update kitt meta box homepage / error page option */
        add_action('wp_ajax_update_kitt_option_homepage', [$this, 'update_kitt_option_homepage']);
        add_action('wp_ajax_update_kitt_option_error_page', [$this, 'update_kitt_option_error_page']);

        /**
         * add Meta Box SEO to pages,
         * post and custom post types
         */
        add_action('add_meta_boxes', [$this, 'add_custom_meta_box_seo']);
        /** ajax update kitt meta box seo option */
        add_action('wp_ajax_update_kitt_meta_seo', [$this, 'update_kitt_meta_seo']);
    }

    public static function add_custom_meta_box_homepage()
    {
        $self = self::get_instance();
        /** add meta box for each post type */
        foreach ($self->meta_box_homepage_screens as $screen) {
            add_meta_box('KiTT-meta_box_homepage', 'Homepage', [$self, 'meta_box_homepage_html'], $screen, 'side');
        }
    }

    public static function add_custom_meta_box()
    {
        $self = self::get_instance();
        /** add meta box for each post type */
        foreach ($self->meta_box_homepage_screens as $screen) {
            add_meta_box('KiTT-meta_box_homepage', 'Homepage', [$self, 'meta_box_homepage_html'], $screen, 'side');
            add_meta_box('KiTT-meta_box_error_page', 'Error Page', [$self, 'meta_box_error_page_html'], $screen, 'side');
        }
    }

    public static function meta_box_homepage_html(object $post)
    {
        /** get data */
        $self = self::get_instance();
        $pages = $self->wpdb->get_results(
            "SELECT ID,post_title
                FROM wp_posts
                WHERE post_status = 'publish'
                AND post_type = '$post->post_type'"
        );

        $current_page_on_front = get_option('kitt_option_homepage_' . $post->post_type);
        
        /** build select options */
        $options = '<option value="0">— Select —</option>';
        foreach ($pages as $page) {
            $options .= '<option value="' . $page->ID . '"'  . 'data-post-title="' . $page->post_title . '"' . ($page->ID === $current_page_on_front ? ' selected' : '') . '>' . $page->post_title . '</option>';
        }

        /** print markup */
        printf(
            '<p class="post-attributes-label-wrapper parent-id-label-wrapper">' .
                '<label class="post-attributes-label" for="KiTT-meta-box-homepage">Define the main entry point of the website (only published).</label>' .
            '</p>' .
            '<br>' .
            '<select id="KiTT-meta-box-homepage" name="meta_box_homepage" data-post-type="%2$s">' .
                '%1$s' .
            '</select>' .
            '<br>' .
            '<p class="post-attributes-label-wrapper parent-id-label-wrapper">' .
                '<span id="KiTT-meta-box-homepage-msg" class="post-attributes-label"></span>' .
            '</p>',
            $options,
            $post->post_type
        );
    }

    public static function meta_box_error_page_html(object $post)
    {
        /** get data */
        $self = self::get_instance();
        $pages = $self->wpdb->get_results(
            "SELECT ID,post_title
                FROM wp_posts
                WHERE post_status = 'publish'
                AND post_type = '$post->post_type'"
        );

        $current_page_on_front = get_option('kitt_option_error_page_' . $post->post_type);

        /** build select options */
        $options = '<option value="0">— Select —</option>';
        foreach ($pages as $page) {
            $options .= '<option value="' . $page->ID . '"'  . 'data-post-title="' . $page->post_title . '"' . ($page->ID === $current_page_on_front ? ' selected' : '') . '>' . $page->post_title . '</option>';
        }

        /** print markup */
        printf(
            '<p class="post-attributes-label-wrapper parent-id-label-wrapper">' .
                '<label class="post-attributes-label" for="KiTT-meta-box-error-page">Define the error page (404) for the website (only published).</label>' .
            '</p>' .
            '<br>' .
            '<select id="KiTT-meta-box-error-page" name="meta_box_error_page" data-post-type="%2$s">' .
                '%1$s' .
            '</select>' .
            '<br>' .
            '<p class="post-attributes-label-wrapper parent-id-label-wrapper">' .
                '<span id="KiTT-meta-box-error-page-msg" class="post-attributes-label"></span>' .
            '</p>',
            $options,
            $post->post_type
        );
    }

    public static function update_kitt_option_homepage()
    {
        if (check_ajax_referer('KiTT-functions-post', 'nonce', false)) {
            if (isset($_POST['ID']) && !empty($_POST['ID']) &&
                isset($_POST['post_type']) && !empty($_POST['post_type'])) {

                update_option('kitt_option_homepage_' . $_POST['post_type'], $_POST['ID']);
            }
        }
        exit;
    }

    public static function update_kitt_option_error_page()
    {
        if (check_ajax_referer('KiTT-functions-post', 'nonce', false)) {
            if (isset($_POST['ID']) && !empty($_POST['ID']) &&
                isset($_POST['post_type']) && !empty($_POST['post_type'])) {

                update_option('kitt_option_error_page_' . $_POST['post_type'], $_POST['ID']);
            }
        }
        exit;
    }

    public static function add_custom_meta_box_seo()
    {
        $self = self::get_instance();
        /** add meta box for each post type */
        foreach ($self->meta_box_seo_screens as $screen) {
            add_meta_box('KiTT-meta_box_seo', 'SEO', [$self, 'meta_box_seo_html'], $screen, 'normal');
        }
    }

    public static function meta_box_seo_html(object $post)
    {
        /** get current meta values */
        $title = get_post_meta($post->ID, 'kitt_meta_seo_title');
        $keywords = get_post_meta($post->ID, 'kitt_meta_seo_keywords');
        $description = get_post_meta($post->ID, 'kitt_meta_seo_description');
        $robots = get_post_meta($post->ID, 'kitt_meta_seo_robots');
        $canonical = get_post_meta($post->ID, 'kitt_meta_seo_canonical');
        /** set default robots values */
        $index = ' checked';
        $noindex = '';
        $follow = ' checked';
        $nofollow = '';
        $noimageindex = '';
        $noarchive = '';
        $nocache = '';
        $nosnippet = '';
        $nositelinkssearchbox = '';
        $nopagereadaloud = '';
        $noyaca = '';
        $notranslate = '';
        $max_snippet = -1;
        $max_video_preview = -1;
        $max_image_preview = 'standard';
        $option_none = '';
        $option_standard = ' selected';
        $option_large = '';
        $unavailable_after = '';

        if (!empty($robots)) {
            /** filter unavailable_after before explode  */
            if (preg_match('/unavailable_after/', $robots[0])) {

                preg_match_all('/(.*),\sunavailable_after:\s\w+,\s(\d+)-(\w+)-(\d+).*/', $robots[0], $matches);
                $unavailable_after = substr(date("Y"), 0, 2) . $matches[4][0] . '-' . $matches[3][0] . '-' . ((intval($matches[2][0]) < 10) ? '0' . $matches[2][0] : $matches[2][0]);

                foreach([
                    'Jan'=>'01',
                    'Feb'=>'02',
                    'Mar'=>'03',
                    'Apr'=>'04',
                    'May'=>'05',
                    'Jun'=>'06',
                    'Jul'=>'07',
                    'Aug'=>'08',
                    'Sep'=>'09',
                    'Oct'=>'10',
                    'Nov'=>'11',
                    'Dec'=>'12'
                ] as $month_key => $month_value) {
                    if (preg_match("/$month_key/", $unavailable_after)) {
                        $unavailable_after = preg_replace("/$month_key/", $month_value, $unavailable_after);
                    }
                }

                $robots = explode(', ', $matches[1][0]);
            } else {
                $robots = explode(', ', $robots[0]);
            }

            /** update robots values */
            foreach($robots as $robots_values) {

                if ($robots_values === 'index') $index = ' checked';
                if ($robots_values === 'noindex') $noindex = ' checked';
                if ($robots_values === 'follow') $follow = ' checked';
                if ($robots_values === 'nofollow') $nofollow = ' checked';
                if ($robots_values === 'noimageindex') $noimageindex = ' checked';
                if ($robots_values === 'noarchive') $noarchive = ' checked';
                if ($robots_values === 'nocache') $nocache = ' checked';
                if ($robots_values === 'nosnippet') $nosnippet = ' checked';
                if ($robots_values === 'nositelinkssearchbox') $nositelinkssearchbox = ' checked';
                if ($robots_values === 'nopagereadaloud') $nopagereadaloud = ' checked';
                if ($robots_values === 'noyaca') $noyaca = ' checked';
                if ($robots_values === 'notranslate') $notranslate = ' checked';
                if (str_contains($robots_values, 'max-snippet:')) $max_snippet = preg_replace('/max-snippet:/', '', $robots_values);
                if (str_contains($robots_values, 'max-video-preview:')) $max_video_preview = preg_replace('/max-video-preview:/', '', $robots_values);
                if (str_contains($robots_values, 'max-image-preview:')) $max_image_preview = preg_replace('/max-image-preview:/', '', $robots_values);

            }
            /** update max-image-preview select */
            $option_none = ($max_image_preview === 'none') ? ' selected' : '';
            $option_standard = ($max_image_preview === 'standard') ? ' selected' : '';
            $option_large = ($max_image_preview === 'large') ? ' selected' : '';
        }

        /** print markup */
        printf(
            '<table data-post-id="%1$s">' .
                '<tbody>' .
                    '<tr>' .
                        '<td>' .
                            '<p class="post-attributes-label-wrapper parent-id-label-wrapper">' .
                                '<label class="post-attributes-label">Enter title</label>' .
                                '<span>(This title will overwrite the post title above)</span>' .
                            '</p>' .
                            '<input id="KiTT-title" type="text" name="KiTT-title" placeholder="Google typically displays the first 50–60 characters of a title tag." value="%2$s">' .
                        '</td>' .
                    '</tr>' .
                '</tbody>' .
            '</table>' .
            '<table>' .
                '<tbody>' .
                    '<tr>' .
                        '<td>' .
                            '<p class="post-attributes-label-wrapper parent-id-label-wrapper">' .
                                '<label class="post-attributes-label">Enter keywords</label>' .
                                '<span>(separated by commas, ~100–150 characters)</span>' .
                            '</p>' .
                            '<textarea rows="3" placeholder="Meta keywords can be any length, but Google generally truncates snippets to ~100–150 characters.">%3$s</textarea>' .
                        '</td>' .
                        '<td>' .
                            '<p class="post-attributes-label-wrapper parent-id-label-wrapper">' .
                                '<label class="post-attributes-label">Enter description</label>' .
                                '<span>(~155–160 characters)</span>' .
                            '</p>' .
                            '<textarea rows="3" placeholder="Meta descriptions can be any length, but Google generally truncates snippets to ~155–160 characters.">%4$s</textarea>' .
                        '</td>' .
                    '</tr>' .
                '</tbody>' .
            '</table>' .
            '<p class="post-attributes-label-wrapper parent-id-label-wrapper">' .
                '<label class="post-attributes-label">The following values (‘parameters’) can be placed to control how search engines interact with your page.</label>' .
                '<a href="https://yoast.com/robots-meta-tags" target="_blank">Info about robots meta tag</a>' .
                '<br>' .
            '</p>' .
            '<div>' .
                '<span>' .
                    '<input id="KiTT-robots-index" type="radio" name="KiTT-robots-index" value="index"%5$s>' .
                    '<label for="KiTT-robots-index">index</label>' .
                    '<input id="KiTT-robots-noindex" type="radio" name="KiTT-robots-index" value="noindex"%6$s>' .
                    '<label for="KiTT-robots-noindex">noindex</label>' .
                '</span>' .
                '<span>' .
                    '<input id="KiTT-robots-follow" type="radio" name="KiTT-robots-follow" value="follow"%7$s>' .
                    '<label for="KiTT-robots-follow">follow</label>' .
                    '<input id="KiTT-robots-nofollow" type="radio" name="KiTT-robots-follow" value="nofollow"%8$s>' .
                    '<label for="KiTT-robots-nofollow">nofollow</label>' .
                '</span>' .
                '<span>' .
                    '<input id="KiTT-robots-noimageindex" type="checkbox" name="KiTT-robots-noimageindex" value="noimageindex"%9$s>' .
                    '<label for="KiTT-robots-noimageindex">noimageindex</label>' .
                '</span>' .
                '<span>' .
                    '<input id="KiTT-robots-noarchive" type="checkbox" name="KiTT-robots-noarchive" value="noarchive"%10$s>' .
                    '<label for="KiTT-robots-noarchive">noarchive</label>' .
                '</span>' .
                '<span>' .
                    '<input id="KiTT-robots-nocache" type="checkbox" name="KiTT-robots-nocache" value="nocache"%11$s>' .
                    '<label for="KiTT-robots-nocache">nocache</label>' .
                '</span>' .
                '<span>' .
                    '<input id="KiTT-robots-nosnippet" type="checkbox" name="KiTT-robots-nosnippet" value="nosnippet"%12$s>' .
                    '<label for="KiTT-robots-nosnippet">nosnippet</label>' .
                '</span>' .
                '<span>' .
                    '<input id="KiTT-robots-nositelinkssearchbox" type="checkbox" name="KiTT-robots-nositelinkssearchbox" value="nositelinkssearchbox"%13$s>' .
                    '<label for="KiTT-robots-nositelinkssearchbox">nositelinkssearchbox</label>' .
                '</span>' .
                '<span>' .
                    '<input id="KiTT-robots-nopagereadaloud" type="checkbox" name="KiTT-robots-nopagereadaloud" value="nopagereadaloud"%14$s>' .
                    '<label for="KiTT-robots-nopagereadaloud">nopagereadaloud</label>' .
                '</span>' .
                '<span>' .
                    '<input id="KiTT-robots-noyaca" type="checkbox" name="KiTT-robots-noyaca" value="noyaca"%15$s>' .
                    '<label for="KiTT-robots-noyaca">noyaca</label>' .
                '</span>' .
                '<span>' .
                    '<input id="KiTT-robots-notranslate" type="checkbox" name="KiTT-robots-notranslate" value="notranslate"%16$s>' .
                    '<label for="KiTT-robots-notranslate">notranslate</label>' .
                '</span>' .
                '<span>' .
                    '<label for="KiTT-robots-max_snippet">max snippet</label>' .
                    '<input id="KiTT-robots-max_snippet" type="number" name="KiTT-robots-max_snippet" value="%17$s">' .
                '</span>' .
                '<span>' .
                    '<label for="KiTT-robots-max_video_preview">max video preview</label>' .
                    '<input id="KiTT-robots-max_video_preview" type="number" name="KiTT-robots-max_video_preview" value="%18$s">' .
                '</span>' .
                '<span>' .
                    '<label for="KiTT-robots-max-image-preview">max image preview</label>' .
                    '<select id="KiTT-robots-max_image_preview" name="KiTT-robots-max_image_preview">' .
                        '<option value="none"%19$s>none</option>' .
                        '<option value="standard"%20$s>standard</option>' .
                        '<option value="large"%21$s>large</option>' .
                    '</select>' .
                '</span>' .
                '<span>' .
                    '<label for="KiTT-robots-unavailable_after">unavailable after</label>' .
                    '<input id="KiTT-robots-unavailable_after" type="date" name="KiTT-robots-unavailable_after" value="%22$s" placeholder="YYYY-MM-DD">' .
                    '<i id="KiTT-robots-unavailable_after-clear"></i>' . 
                '</span>' .
            '</div>' .
            '<p class="post-attributes-label-wrapper parent-id-label-wrapper">' .
                '<label class="post-attributes-label">Enter canonical URLs to tell search engines that certain similar URLs are actually the same.</label>' .
                '<a href="https://yoast.com/rel-canonical/" target="_blank">Info about canonical link tag</a>' .
                '<span>&nbsp;&nbsp;(separated by commas + line break)</span>' .
            '</p>' .
            '<textarea id="KiTT-canonical-links" rows="4" placeholder="https://example.com/wordpress/your-site,&#10;https://example.com/wordpress/your/site">%23$s</textarea>',
            $post->ID,
            ($title) ? $title[0] : '',
            ($keywords) ? $keywords[0] : '',
            ($description) ? $description[0] : '',
            $index,
            $noindex,
            $follow,
            $nofollow,
            $noimageindex,
            $noarchive,
            $nocache,
            $nosnippet,
            $nositelinkssearchbox,
            $nopagereadaloud,
            $noyaca,
            $notranslate,
            $max_snippet,
            $max_video_preview,
            $option_none,
            $option_standard,
            $option_large,
            $unavailable_after,
            ($canonical) ? str_replace(', ', ',&#10', $canonical[0]) : ''
        );
    }

    public static function update_kitt_meta_seo()
    {
        if (check_ajax_referer('KiTT-functions-post', 'nonce', false)) {
            if (isset($_POST['ID']) && !empty($_POST['ID'])){
                /** update existing kitt meta box seo data */
                if (isset($_POST['title'])) update_metadata('post', $_POST['ID'], 'kitt_meta_seo_title', $_POST['title']);
                if (isset($_POST['keywords'])) update_metadata('post', $_POST['ID'], 'kitt_meta_seo_keywords', $_POST['keywords']);
                if (isset($_POST['description'])) update_metadata('post', $_POST['ID'], 'kitt_meta_seo_description', $_POST['description']);
                if (isset($_POST['robots']) && !empty($_POST['robots'])) update_metadata('post', $_POST['ID'], 'kitt_meta_seo_robots', $_POST['robots']);
                if (isset($_POST['canonical'])) update_metadata('post', $_POST['ID'], 'kitt_meta_seo_canonical', $_POST['canonical']);
            }
        }
        exit;
    }
}
