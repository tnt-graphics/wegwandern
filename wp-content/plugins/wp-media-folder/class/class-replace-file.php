<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
use Joomunited\WPMediaFolder\WpmfHelper;

/**
 * Class WpmfReplaceFile
 * This class that holds most of the replace file functionality for Media Folder.
 */
class WpmfReplaceFile
{

    /**
     * WpmfReplaceFile constructor.
     */
    public function __construct()
    {
        add_action('wp_enqueue_media', array($this, 'enqueueAdminScripts'));
        add_action('wp_ajax_wpmf_replace_file', array($this, 'replaceFile'));
        add_filter('attachment_fields_to_edit', array($this, 'attachmentFieldsToEdit'), 10, 2);
    }

    /**
     * Ajax replace attachment
     *
     * @return void
     */
    public function replaceFile()
    {
        $msg = __('Can not replace this file', 'wpmf');
        $parse_url = parse_url(site_url());
        $host = md5($parse_url['host']);

        if (empty($_POST['wpmf_nonce']) || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            if (isset($_POST['mode_list'])) {
                setcookie('msgReplaceFile_' . $host, $msg, time() + (365 * 24 * 60 * 60), '/', COOKIE_DOMAIN);
                die();
            } else {
                die();
            }
        }

        /**
         * Filter check capability of current user to replace a file
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('edit_posts'), 'replace_file');
        if (!$wpmf_capability) {
            if (isset($_POST['mode_list'])) {
                setcookie('msgReplaceFile_' . $host, $msg, time() + (365 * 24 * 60 * 60), '/', COOKIE_DOMAIN);
                die();
            } else {
                wp_send_json(false);
            }
        }
        if (!empty($_FILES['wpmf_replace_file'])) {
            if (empty($_POST['post_selected'])) {
                if (isset($_POST['mode_list'])) {
                    setcookie('msgReplaceFile_' . $host, 'Post empty', time() + (365 * 24 * 60 * 60), '/', COOKIE_DOMAIN);
                } else {
                    esc_html_e('Post empty', 'wpmf');
                }
                die();
            }

            $id       = $_POST['post_selected'];
            $metadata = wp_get_attachment_metadata($id);
            $allowedImageTypes = array('gif', 'jpg', 'png', 'bmp', 'webp', 'pdf');
            $new_filetype      = wp_check_filetype($_FILES['wpmf_replace_file']['name']);
            if ($new_filetype['ext'] === 'jpeg') {
                $new_filetype['ext'] = 'jpg';
            }

            $cloud_file_type = wpmfGetCloudFileType($id);
            $awsS3infos = get_post_meta($id, 'wpmf_awsS3_info', true);
            $isLocal = false;
            if ($cloud_file_type === 'local' && empty($awsS3infos)) {
                $isLocal = true;
            }

            if ($isLocal || empty($awsS3infos)) {
                $filepath = get_attached_file($id);
                if ($cloud_file_type === 'nextcloud' || $cloud_file_type === 'owncloud') {
                    $filepath = get_post_meta($id, 'wpmf_drive_path', true);
                }
            } else {
                if (isset($awsS3infos['Key'])) {
                    $cloud_file_type = 'offload';
                    $filepath = $awsS3infos['Key'];
                    $localPath = get_attached_file($id);
                } else {
                    if (isset($_POST['mode_list'])) {
                        setcookie('msgReplaceFile_' . $host, __('File doesn\'t exist', 'wpmf'), time() + (365 * 24 * 60 * 60), '/', COOKIE_DOMAIN);
                        die();
                    } else {
                        wp_send_json(
                            array(
                                'status' => false,
                                'msg'    => __('File doesn\'t exist', 'wpmf')
                            )
                        );
                    }
                }
            }

            $infopath = pathinfo($filepath);

            if ($infopath['extension'] === 'jpeg') {
                $infopath['extension'] = 'jpg';
            }

            $settings = get_option('wpmf_settings');
            $allowWebpReplace = false;
            if (isset($settings['auto_generate_webp']) && $settings['auto_generate_webp']) {
                if ($infopath['extension'] === 'webp' && in_array($new_filetype['ext'], array('jpg', 'jpeg', 'png'))) {
                    $allowWebpReplace = true;
                }
            }

            if ($new_filetype['ext'] !== $infopath['extension'] && !$allowWebpReplace) {
                if (isset($_POST['mode_list'])) {
                    setcookie('msgReplaceFile_' . $host, __('To replace a media and keep the link to this media working,
                    it must be in the same format, ie. jpg > jpg… Thanks!', 'wpmf'), time() + (365 * 24 * 60 * 60), '/', COOKIE_DOMAIN);
                    die();
                } else {
                    wp_send_json(
                        array(
                            'status' => false,
                            'msg'    => __('To replace a media and keep the link to this media working,
    it must be in the same format, ie. jpg > jpg… Thanks!', 'wpmf')
                        )
                    );
                }
            }

            if ($_FILES['wpmf_replace_file']['error'] > 0) {
                if (isset($_POST['mode_list'])) {
                    setcookie('msgReplaceFile_' . $host, $_FILES['wpmf_replace_file']['error'], time() + (365 * 24 * 60 * 60), '/', COOKIE_DOMAIN);
                    die();
                } else {
                    wp_send_json(
                        array(
                            'status' => false,
                            'msg'    => $_FILES['wpmf_replace_file']['error']
                        )
                    );
                }
            } else {
                $uploadpath = wp_upload_dir();
                if (!file_exists($filepath)) {
                    if ($isLocal) {
                        if (isset($_POST['mode_list'])) {
                            setcookie('msgReplaceFile_' . $host, __('File doesn\'t exist', 'wpmf'), time() + (365 * 24 * 60 * 60), '/', COOKIE_DOMAIN);
                            die();
                        } else {
                            wp_send_json(
                                array(
                                    'status' => false,
                                    'msg'    => __('File doesn\'t exist', 'wpmf')
                                )
                            );
                        }
                    }
                } else {
                    wp_delete_file($filepath);
                }

                if ($isLocal) {
                    if (in_array($infopath['extension'], $allowedImageTypes)) {
                        if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
                            foreach ($metadata['sizes'] as $size => $sizeinfo) {
                                $intermediate_file = str_replace(basename($filepath), $sizeinfo['file'], $filepath);
                                // This filter is documented in wp-includes/functions.php
                                $intermediate_file = apply_filters('wp_delete_file', $intermediate_file);
                                $link = path_join(
                                    $uploadpath['basedir'],
                                    $intermediate_file
                                );
                                if (file_exists($link) && is_writable($link)) {
                                    unlink($link);
                                }
                            }
                        }
                    }

                    move_uploaded_file(
                        $_FILES['wpmf_replace_file']['tmp_name'],
                        $infopath['dirname'] . '/' . $infopath['basename']
                    );
                    update_post_meta($id, 'wpmf_size', filesize($infopath['dirname'] . '/' . $infopath['basename']));

                    if (isset($settings['auto_generate_webp']) && $settings['auto_generate_webp'] && $allowWebpReplace) {
                        $file_path = $infopath['dirname'] . '/' . $infopath['basename'];
                        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

                        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                            $upload_dir = dirname($file_path);
                            $file_name  = pathinfo($file_path, PATHINFO_FILENAME);
                            $unique_webp_name = wp_unique_filename($upload_dir, $file_name . '.webp');
                            $webp_path = trailingslashit($upload_dir) . $unique_webp_name;

                            $image = wp_get_image_editor($file_path);
                            if (!is_wp_error($image)) {
                                $image->set_quality(85);
                                $saved = $image->save($webp_path, 'image/webp');

                                if (!is_wp_error($saved) && file_exists($webp_path)) {
                                    // Delete old JPG/PNG
                                    unlink($file_path);

                                    // Update to use WebP file
                                    $infopath['basename'] = basename($webp_path);
                                    $infopath['extension'] = 'webp';
                                    $filepath = $webp_path;

                                    // Update attachment file info
                                    update_attached_file($id, $webp_path);

                                    // Update URL and MIME type
                                    $file_url = wp_get_attachment_url($id);
                                    $webp_url = preg_replace('/\.(jpe?g|png)$/i', '.webp', $file_url);
                                    wp_update_post(array(
                                        'ID' => $id,
                                        'guid' => $webp_url,
                                        'post_mime_type' => 'image/webp'
                                    ));
                                }
                            }
                        }
                    }
                    
                    if ($infopath['extension'] === 'pdf') {
                        WpmfHelper::createPdfThumbnail($filepath);
                    }

                    if (in_array($infopath['extension'], $allowedImageTypes)) {
                        if ($infopath['extension'] !== 'pdf') {
                            $actual_sizes_array = getimagesize($filepath);
                            $metadata['width']  = $actual_sizes_array[0];
                            $metadata['height'] = $actual_sizes_array[1];
                            WpmfHelper::createThumbs($filepath, $infopath['extension'], $metadata, $id);
                        }
                    }
                    do_action('wpmf_after_file_replace', $infopath, $id);
                } else {
                    $aws3config = getOffloadOption();
                    $newContent = file_get_contents($_FILES['wpmf_replace_file']['tmp_name']);
                    if (isset($settings['auto_generate_webp']) && $settings['auto_generate_webp'] && $allowWebpReplace) {
                        $tmp_file = $_FILES['wpmf_replace_file']['tmp_name'];
                        $ext = strtolower(pathinfo($_FILES['wpmf_replace_file']['name'], PATHINFO_EXTENSION));

                        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                            $image = wp_get_image_editor($tmp_file);
                            if (!is_wp_error($image)) {
                                $image->set_quality(85);

                                $webp_tmp = tempnam(sys_get_temp_dir(), 'wpmf_webp_');
                                $webp_path = $webp_tmp . '.webp';
                                $saved = $image->save($webp_path, 'image/webp');

                                if (!is_wp_error($saved) && file_exists($webp_path)) {
                                    $newContent = file_get_contents($webp_path);

                                    $_FILES['wpmf_replace_file']['type'] = 'image/webp';
                                    $_FILES['wpmf_replace_file']['name'] = pathinfo($_FILES['wpmf_replace_file']['name'], PATHINFO_FILENAME) . '.webp';
                                }
                            }
                        }
                    }
                    switch ($cloud_file_type) {
                        case 'offload':
                            if (isset($aws3config['mendpoint']) && $aws3config['mendpoint'] === 'bunny') {
                                //delete file on cloud bunny
                                do_action('wpmf_delete_attachment_cloud', $_POST['post_selected']);
                                if (isset($_FILES['wpmf_replace_file']) && empty($_FILES['wpmf_replace_file']['error'])) {
                                    wp_delete_file($localPath);
                                    if (in_array($infopath['extension'], $allowedImageTypes)) {
                                        if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
                                            foreach ($metadata['sizes'] as $size => $sizeinfo) {
                                                $intermediate_file = str_replace(basename($localPath), $sizeinfo['file'], $localPath);
                                                // This filter is documented in wp-includes/functions.php
                                                $intermediate_file = apply_filters('wp_delete_file', $intermediate_file);
                                                $link = path_join(
                                                    $uploadpath['basedir'],
                                                    $intermediate_file
                                                );
                                                if (file_exists($link) && is_writable($link)) {
                                                    unlink($link);
                                                }
                                            }
                                        }
                                    }

                                    $file = $_FILES['wpmf_replace_file'];
                                    $file['name'] = $infopath['basename'];
        
                                    $overrides = array('test_form' => false);
                                    $uploaded_file = wp_handle_upload($file, $overrides);
        
                                    if (!isset($uploaded_file['error'])) {
                                        $file_path = $uploaded_file['file'];
                                        $file_url = $uploaded_file['url'];
                                        $file_type = wp_check_filetype(basename($file_path), null);
                                
                                        wp_update_post(array(
                                            'ID' => $_POST['post_selected'],
                                            'guid' => $file_url,
                                            'post_mime_type' => $file_type['type'],
                                            'post_title' => sanitize_file_name($file['name']),
                                        ));
                                
                                        update_attached_file($_POST['post_selected'], $file_path);
                                
                                        $attach_data = wp_generate_attachment_metadata($_POST['post_selected'], $file_path);
                                        wp_update_attachment_metadata($_POST['post_selected'], $attach_data);
        
                                        apply_filters('wpmf_uploadto_s3_when_replace', $_POST['post_selected'], false, 'sync');
                                    } else {
                                        if (isset($_POST['mode_list'])) {
                                            setcookie('msgReplaceFile_' . $host, __('An error occurred during the file upload process', 'wpmf'), time() + (365 * 24 * 60 * 60), '/', COOKIE_DOMAIN);
                                            die();
                                        } else {
                                            wp_send_json(array('status' => false, 'msg' => __('An error occurred during the file upload process', 'wpmf')));
                                        }
                                    }
                                }
                            } else {
                                if (isset($localPath) && file_exists($localPath)) {
                                    wp_delete_file($localPath);
                                    if (in_array($infopath['extension'], $allowedImageTypes)) {
                                        if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
                                            foreach ($metadata['sizes'] as $size => $sizeinfo) {
                                                $intermediate_file = str_replace(basename($localPath), $sizeinfo['file'], $localPath);
                                                // This filter is documented in wp-includes/functions.php
                                                $intermediate_file = apply_filters('wp_delete_file', $intermediate_file);
                                                $link = path_join(
                                                    $uploadpath['basedir'],
                                                    $intermediate_file
                                                );
                                                if (file_exists($link) && is_writable($link)) {
                                                    unlink($link);
                                                }
                                            }
                                        }
                                    }
    
                                    move_uploaded_file(
                                        $_FILES['wpmf_replace_file']['tmp_name'],
                                        $infopath['dirname'] . '/' . $infopath['basename']
                                    );
                                    update_post_meta($id, 'wpmf_size', filesize($infopath['dirname'] . '/' . $infopath['basename']));
                                    
                                    if ($infopath['extension'] === 'pdf') {
                                        WpmfHelper::createPdfThumbnail($localPath);
                                    }
    
                                    if (in_array($infopath['extension'], $allowedImageTypes)) {
                                        if ($infopath['extension'] !== 'pdf') {
                                            $actual_sizes_array = getimagesize($localPath);
                                            $metadata['width']  = $actual_sizes_array[0];
                                            $metadata['height'] = $actual_sizes_array[1];
                                            WpmfHelper::createThumbs($localPath, $infopath['extension'], $metadata, $id, true);
                                        }
                                    }
                                }
    
                                apply_filters('wpmfAddonReplaceFileOffload', $newContent, $filepath);
                                $s3FilePath = apply_filters('wp_get_attachment_url', $filepath, $id);
                                if (in_array($infopath['extension'], $allowedImageTypes)) {
                                    if ($infopath['extension'] !== 'pdf') {
                                        $actual_sizes_array = getimagesize($s3FilePath);
                                        $metadata['width']  = $actual_sizes_array[0];
                                        $metadata['height'] = $actual_sizes_array[1];
                                        WpmfHelper::createThumbs($filepath, $infopath['extension'], $metadata, $id, true);
                                    }
                                }
                            }
                            break;
                        case 'google_drive':
                            apply_filters('wpmfAddonReplaceFileGGD', $newContent, $id);
                            break;
                        case 'dropbox':
                            apply_filters('wpmfAddonReplaceFileDropbox', $newContent, $id);
                            break;
                        case 'onedrive':
                            apply_filters('wpmfAddonReplaceFileOnedrive', $newContent, $id);
                            break;
                        case 'onedrive_business':
                            apply_filters('wpmfAddonReplaceFileOnedriveBusiness', $newContent, $id);
                            break;
                        case 'nextcloud':
                            apply_filters('wpmfAddonReplaceFileNextcloud', $newContent, $filepath);
                            break;
                        case 'owncloud':
                            apply_filters('wpmfAddonReplaceFileOwncloud', $newContent, $filepath);
                            break;
                        default:
                            break;
                    }
                }

                if (isset($_FILES['wpmf_replace_file']['size'])) {
                    $size = $_FILES['wpmf_replace_file']['size'];
                    $metadata   = wp_get_attachment_metadata($id);
                    $metadata['filesize'] = $size;
                    update_post_meta($id, '_wp_attachment_metadata', $metadata);
                    update_post_meta($id, 'wpmf_size', $size);
                    if ($size >= 1024 && $size < 1024 * 1024) {
                        $size = ceil($size / 1024) . ' KB';
                    } elseif ($size >= 1024 * 1024) {
                        $size = ceil($size / (1024 * 1024)) . ' MB';
                    } elseif ($size < 1024) {
                        $size = $size . ' B';
                    }
                } else {
                    $size = '0 B';
                }

                // apply watermark after replace
                $origin_infos = pathinfo($filepath);
                $origin_name = $origin_infos['filename'] . 'imageswatermark.' . $origin_infos['extension'];
                $path_origin = str_replace(wp_basename($filepath), $origin_name, $filepath);
                if (file_exists($path_origin)) {
                    $paths = array(
                        'origin' => $path_origin
                    );

                    if (isset($metadata['sizes'])) {
                        foreach ($metadata['sizes'] as $size => $file) {
                            $infos = pathinfo($file['file']);
                            $filewater = $infos['filename'] . 'imageswatermark.' . $infos['extension'];
                            $paths[$size] = str_replace(wp_basename($filepath), $filewater, $filepath);
                        }
                    }

                    foreach ($paths as $path) {
                        if (!realpath($path)) {
                            continue;
                        }

                        if (file_exists($path) && is_writable($path)) {
                            unlink($path);
                        }
                    }

                    require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-image-watermark.php');
                    $wpmfwatermark = new WpmfWatermark();
                    $wpmfwatermark->createWatermarkImage($metadata, $id);
                }

                /**
                 * Do action after replace file
                 *
                 * @param integer       Attachment ID
                 */
                do_action('wpmf_after_replace', $id);
                // end apply watermark after replace
                if (in_array($infopath['extension'], $allowedImageTypes) && $infopath['extension'] !== 'pdf') {
                    $metadata   = wp_get_attachment_metadata($id);
                    $dimensions = $metadata['width'] . ' x ' . $metadata['height'];
                    if (isset($_POST['mode_list'])) {
                        setcookie('msgReplaceFile_' . $host, 'File replace!', time() + (365 * 24 * 60 * 60), '/', COOKIE_DOMAIN);
                        die();
                    } else {
                        wp_send_json(array('status' => true, 'size' => $size, 'dimensions' => $dimensions, 'cloud_file_type' => $cloud_file_type));
                    }
                } else {
                    if (isset($_POST['mode_list'])) {
                        setcookie('msgReplaceFile_' . $host, 'File replaced!', time() + (365 * 24 * 60 * 60), '/', COOKIE_DOMAIN);
                        die();
                    } else {
                        wp_send_json(array('status' => true, 'size' => $size));
                    }
                }
            }
        } else {
            if (isset($_POST['mode_list'])) {
                setcookie('msgReplaceFile_' . $host, __('File doesn\'t exist', 'wpmf'), time() + (365 * 24 * 60 * 60), '/', COOKIE_DOMAIN);
                die();
            } else {
                wp_send_json(array('status' => false, 'msg' => __('File doesn\'t exist', 'wpmf')));
            }
        }
    }

    /**
     * Includes styles and some scripts
     *
     * @return void
     */
    public function enqueueAdminScripts()
    {
        /**
         * Filter check capability of current user to load assets
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('edit_posts'), 'load_script_style');
        if ($wpmf_capability) {
            wp_enqueue_script(
                'wpmf-folder-snackbar',
                plugins_url('/assets/js/snackbar.js', dirname(__FILE__)),
                array('jquery'),
                WPMF_VERSION
            );
            wp_enqueue_script(
                'wpmf-jquery-form',
                plugins_url('assets/js/jquery.form.js', dirname(__FILE__)),
                array('jquery'),
                WPMF_VERSION
            );

            wp_enqueue_script(
                'replace-image',
                plugins_url('assets/js/replace-image.js', dirname(__FILE__)),
                array('jquery'),
                WPMF_VERSION
            );

            wp_enqueue_style(
                'wpmf-material-icon',
                plugins_url('/assets/css/google-material-icon.css', dirname(__FILE__)),
                array(),
                WPMF_VERSION
            );

            wp_enqueue_style(
                'wpmf-style',
                plugins_url('/assets/css/style.css', dirname(__FILE__)),
                array(),
                WPMF_VERSION
            );
            global $pagenow;
            $get_plugin_active   = json_encode(get_option('active_plugins'));
            $option_override  = get_option('wpmf_option_override');
            $params = array(
                'vars' => array(
                    'ajaxurl'               => admin_url('admin-ajax.php'),
                    'wpmf_nonce'            => wp_create_nonce('wpmf_nonce'),
                    'wpmf_pagenow'           => $pagenow,
                    'get_plugin_active'     => $get_plugin_active,
                    'override'              => (int) $option_override
                ),
                'l18n' => array(
                    'file_uploading'        => __('File upload on the way...', 'wpmf'),
                    'replace'               => __('Replace', 'wpmf'),
                    'filesize_label'        => __('File size:', 'wpmf'),
                    'dimensions_label'      => __('Dimensions:', 'wpmf'),
                    'wpmf_file_replace'     => __('File replaced!', 'wpmf')
                )
            );
            wp_localize_script('replace-image', 'wpmfParams', $params);
        }
    }

    /**
     * Create attachment fields
     * Based on /wp-admin/includes/media.php
     *
     * @param array   $form_fields An array of attachment form fields.
     * @param WP_Post $post        The WP_Post attachment object.
     *
     * @return mixed
     */
    public function attachmentFieldsToEdit($form_fields, $post)
    {
        global $pagenow;
        if (!empty($pagenow) && $pagenow === 'post.php') {
            $this->enqueueAdminScripts();
        }

        return $form_fields;
    }
}
