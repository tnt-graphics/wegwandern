<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
use Joomunited\WPMediaFolder\WpmfHelper;

/**
 * Class WpmfMediaRename
 * This class that holds most of the rename file functionality for Media Folder.
 */
class WpmfMediaRename
{
    /**
     * Allowed image extensions (lowercase) that will be skipped from renaming if AI rename is enabled.
     *
     * This is filterable via 'wpmf_ai_allowed_image_types'.
     *
     * @var string[]
     */
    protected $aiAllowedImageTypes = array();

    /**
     * WpmfMediaRename constructor.
     */
    public function __construct()
    {
        $default_types = array('jpeg', 'jpg', 'png', 'gif', 'webp');
        $this->aiAllowedImageTypes = apply_filters('wpmf_ai_allowed_image_types', $default_types);

        add_filter('wp_handle_upload_prefilter', array($this, 'customUploadFilter'));
        add_filter('wp_generate_attachment_metadata', array($this, 'afterUpload'), 10, 2);
    }

    /**
     * Rename attachment after upload
     *
     * @param array $file An array of data for a single file.
     *
     * @return array $file
     */
    public function customUploadFilter($file)
    {
        global $pagenow;
        if (isset($pagenow) && $pagenow === 'update.php') {
            return $file;
        }

        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (isset($file['type']) && strpos($file['type'], 'image/') === 0 && in_array($file_ext, $this->aiAllowedImageTypes) && get_option('wpmf_ai_rename_image_upload') === '1') {
            return $file;
        }

        $pattern            = get_option('wpmf_patern_rename');
        $upload_dir         = wp_upload_dir();
        $info               = pathinfo($file['name']);
        $parent = 0;
        $parentFolderName = '';
        if (!empty($_POST['wpmf_folder'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
            $parent = (int)$_POST['wpmf_folder']; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
            $current_folder = get_term((int) $parent, WPMF_TAXO);
            $foldername     = sanitize_title($current_folder->name);
            $folderslug = $current_folder->slug;
            if ($current_folder->parent) {
                $parentFolder = get_term((int) $current_folder->parent, WPMF_TAXO);
                $parentFolderName =  sanitize_title($parentFolder->name);
            }
        } else {
            $foldername = 'uncategorized';
            $folderslug = 'uncategorized';
        }

        $sitename          = sanitize_title(get_bloginfo('name'));
        $original_filename = $info['filename'];
        $date              = str_replace('/', '', $upload_dir['subdir']);
        if ($date === '') {
            $date = date('Ym', time());
        }

        $ext               = empty($info['extension']) ? '' : '.' . $info['extension'];
        $format_date = date('Y-m-d H-i-s', current_time('timestamp'));
        $pattern           = str_replace('{sitename}', $sitename, $pattern);
        $pattern           = str_replace('{date}', $date, $pattern);
        $pattern  = str_replace('{original name}', $original_filename, $pattern);
        $pattern  = str_replace('{folderslug}', $folderslug, $pattern);
        $pattern  = str_replace('{parent_folder}', $parentFolderName, $pattern);
        $pattern           = str_replace('{timestamp}', $format_date, $pattern);

        if (strpos($pattern, '#') !== false) {
            $number = 0;
            if (strpos($pattern, '{foldername}') !== false) {
                $pattern  = str_replace('{foldername}', $foldername, $pattern);
                $number_list = get_option('wpmf_rename_number_list');
                if (isset($number_list[$parent])) {
                    $number = $number_list[$parent];
                }
                if (!$number) {
                    $number = 0;
                }
                $number++;
            } else {
                $number = get_option('wpmf_rename_number');
                if (!$number) {
                    $number = 0;
                }
                $number++;
            }

            if (strlen($number) === 1) {
                $number = '0' . $number;
            }

            $pattern  = str_replace('#', $number . $ext, $pattern);
            $pattern = do_shortcode($pattern);
            $filename = $pattern;
        } else {
            $pattern  = str_replace('{foldername}', $foldername, $pattern);
            $pattern = do_shortcode($pattern);
            $filename = wp_unique_filename($upload_dir['path'], $pattern . $ext);
        }

        $file['name'] = $filename;
        return $file;
    }

    /**
     * Update option wpmf_rename_number
     * Base on /wp-admin/includes/image.php
     *
     * @param array   $metadata      An array of attachment meta data.
     * @param integer $attachment_id Current attachment ID.
     *
     * @return mixed $metadata
     */
    public function afterUpload($metadata, $attachment_id)
    {
        $mime_type = get_post_mime_type($attachment_id);
        $file_ext = strtolower(pathinfo(get_attached_file($attachment_id), PATHINFO_EXTENSION));

        if (strpos($mime_type, 'image/') === 0 && in_array($file_ext, $this->aiAllowedImageTypes) && get_option('wpmf_ai_rename_image_upload') === '1') {
            return $metadata;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
        if (isset($_POST['wpmf_folder'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
            $parent = (int) $_POST['wpmf_folder'];
        } else {
            $parent = 0;
        }

        $pattern            = get_option('wpmf_patern_rename');
        if (strpos($pattern, '#') !== false && strpos($pattern, '{foldername}') !== false) {
            $number_list = get_option('wpmf_rename_number_list', false);
            if (!empty($number_list) && is_array($number_list)) {
                if (isset($number_list[$parent])) {
                    $number_list[$parent] =  (int) $number_list[$parent] + 1;
                } else {
                    $number_list[$parent] = 1;
                }
            } else {
                $number_list = array();
                $number_list[$parent] = 1;
            }
            update_option('wpmf_rename_number_list', $number_list);
        } else {
            $number = get_option('wpmf_rename_number');
            if (!$number) {
                $number = 0;
            }
            $number++;
            update_option('wpmf_rename_number', (int) $number);
        }

        return $metadata;
    }
}
