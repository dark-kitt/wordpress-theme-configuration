jQuery(document).ready(function ($) {

    /** functions for media library */
    if ($('body').is('.upload-php, .post-type-attachment')) {

        /** build result list */
        let buildList = function (obj) {

            let list = '',
                totalLength = 0;

            $.each(obj, function (key, value) {
                /** list icons */
                let src = null,
                    vkey = ('duplicate' in value) ? 'duplicate' : 'reference',
                    id = (value[vkey].file_id) ? value[vkey].file_id : '';
                /** check extension */
                if (value[vkey].file_ext === 'jpg' || value[vkey].file_ext === 'jpeg' || value[vkey].file_ext === 'svg') {
                    src = value[vkey].file_src;
                } else if (value[vkey].file_ext === 'mp3') {
                    src = KiTT_WP_DATA.plugin_location + '/img/audio.png';
                } else if (value[vkey].file_ext === 'mp4') {
                    src = KiTT_WP_DATA.plugin_location + '/img/video.png';
                } else if (value[vkey].file_ext === 'zip') {
                    src = KiTT_WP_DATA.plugin_location + '/img/archive.png';
                } else if (value[vkey].file_ext === 'pdf') {
                    src = KiTT_WP_DATA.plugin_location + '/img/document.png';
                } else {
                    src = KiTT_WP_DATA.plugin_location + '/img/default.png';
                }

                list +=
                    '<li class="KiTT-clearfix">' +
                        '<input class="KiTT-media-result-checkbox" type="checkbox" data-file_path="' + value[vkey].file_src + '" data-file_id="' + id + '">' +
                        '<div class="KiTT-media-result-img-con">' +
                            '<img src="' + src + '">' +
                        '</div>' +
                        '<div>' +
                            '<a href="' + value[vkey].file_src + '" target="_blank">' +
                                '<h2>' + value[vkey].file_name + '</h2>' +
                            '</a>' +
                            '<div>' +
                                '<input type="text" value="' + value[vkey].file_src + '">' +
                            '</div>' +
                            '<span>Size: ' + value[vkey].file_size[1] + ' | <strong style="color:' + ((vkey === 'reference') ? '#3ab829' : '#E63939') + '">' + vkey + '</strong></span>' +
                        '</div>' +
                    '</li>';
                
                totalLength++;
            });

            return {
                list: '<ul>' + list + '</ul>',
                total: totalLength
            };
        },
        duplicatesList = function (obj) {

            if (obj.length) {
                let list = '',
                totalLength = 0;

                $.each(obj, function (key, value) {
                    let tmp = buildList(value);
                        totalLength += tmp.total;

                    list += '<li>' + tmp.list + '</li>';
                });

                return '<div class="KiTT-clearfix">' +
                            '<input class="KiTT-media-select-all" type="checkbox" name="select-all-results"><label for="select-all-results">All</label>' +
                            '<span>... found <strong>' + totalLength + '</strong> assets.</span>' +
                        '</div>' +
                        '<ul class="KiTT-media-duplicates">' + list + '</ul>';

            } else {
                return '<ul><li>Nothing found.</li></ul>';
            }
        };

        /** select all media search results */
        $(document).on('change', '.KiTT-media-select-all', function () {
            let val = ($(this).is(':checked')) ? true : false;
            $('#KiTT-media-result ul input[type=checkbox]').attr('checked', val);
        });

        /** delete media */
        $(document).on('click', '#KiTT-modal-delete-btn', function () {
            $(this).prop('disabled', true);

            let files = [];
            /** get all checked files */
            $('.KiTT-media-result-checkbox:checked').each(function (key, value) {
                files.push($(this).data('file_id'));
            });

            if (files.length) {
                let data = {
                    action: 'delete_duplcate_attachments',
                    nonce: KiTT_WP_DATA.nonce,
                    files: files
                };

                $.ajax({
                    type: 'POST',
                    url: KiTT_WP_DATA.ajaxurl,
                    data: data,
                    success: function (data, textStatus, XMLHttpRequest) {
                        window.location.replace(location.origin + location.pathname);
                    },
                    error: function (XMLHttpRequest, textStatus, error) {
                        console.error(error);
                    }
                });
            } else {
                $(this).prop('disabled', false);
            }
        });

        /** KiTT duplicates media modal box */
        $('#KiTT-media-duplicates-btn').on('click', function () {

            $.fancybox.open({
                type: 'html',
                content:
                    '<div class="KiTT-media-modal">' +
                        '<div class="KiTT-media-col">' +
                            '<h1>As duplicates detected</h1>' +
                            '<div id="KiTT-media-result"></div>' +
                            '<button id="KiTT-modal-delete-btn" class="button" disabled>Delete Media</button>' +
                            '<div class="KiTT-media-loading">' +
                                '<span class="KiTT-media-spin spinner is-active"></span>' +
                            '</div>' +
                        '</div>' +
                    '</div>',
                afterShow: function () {
                    $.ajax({
                        type: 'POST',
                        url: KiTT_WP_DATA.ajaxurl,
                        dataType: 'json',
                        data: {
                            action: 'search_duplicate_attachments',
                            nonce: KiTT_WP_DATA.nonce
                        },
                        success: function (data, textStatus, XMLHttpRequest) {
                            $('#KiTT-media-result').html(duplicatesList(data));
                            $('#KiTT-modal-delete-btn').prop('disabled', false);
                            $('.KiTT-media-col .KiTT-media-loading').css('display', 'none');
                        },
                        error: function (XMLHttpRequest, textStatus, error) {
                            console.error(error);
                        }
                    });
                },
                afterClose: function () {}
            });

        });
    }
    /** functions for post, page and custom post types */
    if ($('body').is('.post-php, .post-new-php')) {

        /**
         * save Meta Box Homepage data
         * return -> success or error
         */
        $('#KiTT-meta-box-homepage').on('change', function () {
            let type = $(this).data('post-type'),
                title = $(this).find(':selected').data('post-title');

            if (this.value !== '0') {
                $.ajax({
                    type: 'POST',
                    url: KiTT_WP_DATA.ajaxurl,
                    data: {
                        action: 'update_kitt_option_homepage',
                        nonce: KiTT_WP_DATA.nonce,
                        ID: this.value,
                        post_type: type
                    },
                    success: function (data, textStatus, XMLHttpRequest) {
                        $('#KiTT-meta-box-homepage-msg').html('<span style="color: #3ab829;">success:</span> ' + title + ' is the new Homepage!');
                    },
                    error: function (XMLHttpRequest, textStatus, error) {
                        $('#KiTT-meta-box-homepage-msg').html('<span style="color: #E63939;">error:</span> There went something wrong!');
                        console.error(error);
                    }
                });
            }
        });

        /**
         * save Meta Box Error Page data
         * return -> success or error
         */
         $('#KiTT-meta-box-error-page').on('change', function () {
            let type = $(this).data('post-type'),
                title = $(this).find(':selected').data('post-title');

            if (this.value !== '0') {
                $.ajax({
                    type: 'POST',
                    url: KiTT_WP_DATA.ajaxurl,
                    data: {
                        action: 'update_kitt_option_error_page',
                        nonce: KiTT_WP_DATA.nonce,
                        ID: this.value,
                        post_type: type
                    },
                    success: function (data, textStatus, XMLHttpRequest) {
                        $('#KiTT-meta-box-error-page-msg').html('<span style="color: #3ab829;">success:</span> ' + title + ' is the new Error Page!');
                    },
                    error: function (XMLHttpRequest, textStatus, error) {
                        $('#KiTT-meta-box-error-page-msg').html('<span style="color: #E63939;">error:</span> There went something wrong!');
                        console.error(error);
                    }
                });
            }
        });

        /** clear date in Meta Box SEO */
        $('#KiTT-robots-unavailable_after-clear').on('click', function() {
            $('#KiTT-meta_box_seo #KiTT-robots-unavailable_after').val('');
        });

        /**
         * save Meta Box SEO data
         * on click publish and save draft
         */
         $('#publish, #save-post').one('click', function () {

            let id = $('#KiTT-meta_box_seo table:first-child').data('post-id'),
                title = $('#KiTT-meta_box_seo table:first-child input').val(),
                keywords = $('#KiTT-meta_box_seo table tr td:first-child textarea').val(),
                description = $('#KiTT-meta_box_seo table tr td:last-child textarea').val(),
                date = new Date($('#KiTT-meta_box_seo #KiTT-robots-unavailable_after').val()),
                days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'],
                months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                unavailableAfter = null,
                canonical = $('#KiTT-meta_box_seo #KiTT-canonical-links').val().replace(/[\s\n]+/g, '').split(',').filter((val) => {
                    return val !== '';
                }).join(', ');

                if (String(date) !== 'Invalid Date') {
                    let getTimezoneOffset = date.getTimezoneOffset(),
                        universalTime = (getTimezoneOffset > 0) ? 'UTC-' : 'UTC+',
                        timezoneOffset = (getTimezoneOffset < 0) ? (getTimezoneOffset * -1) / 60 : getTimezoneOffset / 60;

                    unavailableAfter = 'unavailable_after: ' +
                        days[date.getDay()] + ', ' + 
                        date.getDate() + '-' + months[date.getMonth()] + '-' + String(date.getFullYear()).slice(-2) + ' ' +
                        '23:59:59 ' +
                        universalTime + ((timezoneOffset > 9 ) ? timezoneOffset : '0' + timezoneOffset);
                }

            let robots = [
                    ($('#KiTT-meta_box_seo #KiTT-robots-index').prop('checked')) ? 'index' : 'noindex',
                    ($('#KiTT-meta_box_seo #KiTT-robots-follow').prop('checked')) ? 'follow' : 'nofollow',
                    ($('#KiTT-meta_box_seo #KiTT-robots-noimageindex').prop('checked')) ? 'noimageindex' : null,
                    ($('#KiTT-meta_box_seo #KiTT-robots-noarchive').prop('checked')) ? 'noarchive' : null,
                    ($('#KiTT-meta_box_seo #KiTT-robots-nocache').prop('checked')) ? 'nocache' : null,
                    ($('#KiTT-meta_box_seo #KiTT-robots-nosnippet').prop('checked')) ? 'nosnippet' : null,
                    ($('#KiTT-meta_box_seo #KiTT-robots-nositelinkssearchbox').prop('checked')) ? 'nositelinkssearchbox' : null,
                    ($('#KiTT-meta_box_seo #KiTT-robots-nopagereadaloud').prop('checked')) ? 'nopagereadaloud' : null,
                    ($('#KiTT-meta_box_seo #KiTT-robots-noyaca').prop('checked')) ? 'noyaca' : null,
                    ($('#KiTT-meta_box_seo #KiTT-robots-notranslate').prop('checked')) ? 'notranslate' : null,
                    ($('#KiTT-meta_box_seo #KiTT-robots-max_snippet').val() >= -1) ? 'max-snippet:' + $('#KiTT-meta_box_seo #KiTT-robots-max_snippet').val() : null,
                    ($('#KiTT-meta_box_seo #KiTT-robots-max_video_preview').val() >= -1) ? 'max-video-preview:' + $('#KiTT-meta_box_seo #KiTT-robots-max_video_preview').val() : null,
                    'max-image-preview:' + $('#KiTT-meta_box_seo #KiTT-robots-max_image_preview').val(),
                    unavailableAfter
                ].filter(function (el) {
                    return el !== null;
                }).join(', ');

            $.ajax({
                type: 'POST',
                url: KiTT_WP_DATA.ajaxurl,
                data: {
                    action: 'update_kitt_meta_seo',
                    nonce: KiTT_WP_DATA.nonce,
                    ID: id,
                    title: title,
                    keywords: keywords,
                    description: description,
                    robots: robots,
                    canonical: (canonical.length === 0) ? '' : canonical
                },
                success: function (data, textStatus, XMLHttpRequest) {},
                error: function (XMLHttpRequest, textStatus, error) {
                    console.error(error);
                }
            });
        });
    }

    /** functions for company settings */
    if ($('body').is('.toplevel_page_company-settings')) {
        /** delete company logo */
        $('#KiTT-delete-company-logo').one('click', function() {

            $.ajax({
                type: 'POST',
                url: KiTT_WP_DATA.ajaxurl,
                data: {
                    action: 'delete_company_logo',
                    nonce: KiTT_WP_DATA.nonce,
                    delete: true
                },
                success: function (data, textStatus, XMLHttpRequest) {
                    /** reload the page without creating a history entry */
                    window.location.replace(window.location.pathname + window.location.search + window.location.hash);
                },
                error: function (XMLHttpRequest, textStatus, error) {
                    console.error(error);
                }
            });
        });   
    }
});
