<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WpmfWatermark
 * This class that holds most of the image watermark functionality for Media Folder.
 */
class WpmfWatermark
{
    /**
     * Allow logo file extension to add watermark
     *
     * @var array
     */
    public $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');

    /**
     * Wpmf_Watermark constructor.
     */
    public function __construct()
    {
        add_action('wp_ajax_wpmf_watermark_regeneration', array($this, 'regeneratePictures'));
        add_filter('wp_generate_attachment_metadata', array($this, 'createWatermarkImage'), 10, 2);
    }

    /**
     * Create watermark image after upload image
     *
     * @param array   $metadata      An array of attachment meta data.
     * @param integer $attachment_id Current attachment ID.
     * @param boolean $isWoo         Check images of woocommerce product
     *
     * @return mixed $metadata
     */
    public function createWatermarkImage($metadata, $attachment_id, $isWoo = false)
    {
        $option_image_watermark = get_option('wpmf_option_image_watermark');
        if (empty($option_image_watermark)) {
            return $metadata;
        }

        $post_upload            = get_post($attachment_id);
        if (isset($post_upload->post_mime_type) && strpos($post_upload->post_mime_type, 'image') === false) {
            return $metadata;
        }

        if (!empty($attachment_id)) {
            if (is_plugin_active('wp-media-folder-gallery-addon/wp-media-folder-gallery-addon.php')) {
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
                $galleryid = isset($_POST['up_gallery_id']) ? (int)$_POST['up_gallery_id'] : 0;
                $watermark_exclude_public_gallery = wpmfGetOption('watermark_exclude_public_gallery');
                $watermark_exclude_photograph_gallery = wpmfGetOption('watermark_exclude_photograph_gallery');
                $check = false;
                if (!empty($watermark_exclude_public_gallery)) {
                    if ($isWoo) {
                        $gallery_terms = get_the_terms($attachment_id, WPMF_GALLERY_ADDON_TAXO);
                        if (!empty($gallery_terms)) {
                            $check = true;
                            if (empty($watermark_exclude_photograph_gallery)) {
                                foreach ($gallery_terms as $gallery_term) {
                                    $gallery_type = get_term_meta((int)$gallery_term->term_id, 'gallery_type', true);
                                    if (!empty($gallery_type) && $gallery_type === 'photographer') {
                                        $check = false;
                                    }
                                }
                            }
                        }
                    } else {
                        if (!empty($galleryid)) {
                            $check = true;
                            if (empty($watermark_exclude_photograph_gallery)) {
                                $gallery_type = get_term_meta((int)$galleryid, 'gallery_type', true);
                                if (!empty($gallery_type) && $gallery_type === 'photographer') {
                                    $check = false;
                                }
                            }
                        }
                    }
                    if ($check) {
                        return $metadata;
                    }
                }

                if (!empty($watermark_exclude_photograph_gallery)) {
                    if ($isWoo) {
                        $gallery_terms = get_the_terms($attachment_id, WPMF_GALLERY_ADDON_TAXO);
                        if (!empty($gallery_terms)) {
                            foreach ($gallery_terms as $gallery_term) {
                                $gallery_type = get_term_meta((int)$gallery_term->term_id, 'gallery_type', true);
                                if (!empty($gallery_type) && $gallery_type === 'photographer') {
                                    $check = true;
                                }
                            }
                        }
                    } else {
                        if (!empty($galleryid)) {
                            $gallery_type = get_term_meta((int)$galleryid, 'gallery_type', true);
                            if (!empty($gallery_type) && $gallery_type === 'photographer') {
                                $check = true;
                            }
                        }
                    }

                    if ($check) {
                        return $metadata;
                    }
                }
            }

            $current_attachment = get_post($attachment_id);
            if (empty($current_attachment)) {
                return $metadata;
            }
            $watermark_apply = get_option('wpmf_image_watermark_apply');
            $exclude_folders = wpmfGetOption('watermark_exclude_folders');
            $excludes = array();
            foreach (array_unique($exclude_folders) as $folder) {
                if ($folder === 'root') {
                    $excludes[] = 0;
                } else {
                    if ((int) $folder !== 0) {
                        $excludes[] = (int) $folder;
                    }
                }
            }

            $folderIDs = array();
            $terms = get_the_terms($attachment_id, WPMF_TAXO);
            if (empty($terms)) {
                $folderIDs[] = 0;
            } else {
                foreach ($terms as $term) {
                    $folderIDs[] = $term->term_id;
                }
            }

            $same_value = array_intersect($excludes, $folderIDs);
            if (!empty($same_value)) {
                return $metadata;
            }

            $uploads         = wp_upload_dir();
            $imageInfo       = 0;
            if (isset($watermark_apply['all_size']) && (int) $watermark_apply['all_size'] === 1) {
                $sizes = array('full');
                foreach ($metadata['sizes'] as $si => $size_info) {
                    $sizes[] = $si;
                }

                foreach ($sizes as $imageSize) {
                    $image_url = '';
                    if ($imageSize === 'full') {
                        $image_url = $uploads['baseurl'] . '/' . $metadata['file'];
                    } else {
                        if (isset($metadata['sizes'][$imageSize])) {
                            $image_url = $uploads['url'] . '/' . $metadata['sizes'][$imageSize]['file'];
                        }
                    }
                    // Using the wp_upload_dir replace the baseurl with the basedir
                    $path = str_replace($uploads['baseurl'], $uploads['basedir'], $image_url);
                    if (!empty($path) && file_exists($path)) {
                        $pathinfo  = pathinfo($path);
                        $imageInfo = getimagesize($path);
                    }

                    try {
                        if (!empty($pathinfo)) {
                            $this->generatePicture($pathinfo['basename'], $imageInfo, $pathinfo['dirname'], $imageSize, '', $isWoo);
                        } else {
                            if ($isWoo) {
                                return;
                            }
                            wp_send_json(array('status' => false));
                        }
                    } catch (Exception $e) {
                        if ($isWoo) {
                            return;
                        }
                        wp_send_json(array('status' => false));
                    }
                }
            } else {
                foreach ($watermark_apply as $imageSize => $value) {
                    // Using the wp_upload_dir replace the baseurl with the basedir
                    $infos = pathinfo($current_attachment->guid);
                    // Using the wp_upload_dir replace the baseurl with the basedir
                    if (empty($value)) {
                        continue;
                    }

                    $image_url = '';
                    if ($imageSize === 'full') {
                        $image_url = $uploads['baseurl'] . '/' . $metadata['file'];
                    } else {
                        if (isset($metadata['sizes'][$imageSize])) {
                            $image_url = $uploads['url'] . '/' . $metadata['sizes'][$imageSize]['file'];
                        }
                    }
                    // Using the wp_upload_dir replace the baseurl with the basedir
                    $path = str_replace($uploads['baseurl'], $uploads['basedir'], $image_url);
                    if (!empty($path)) {
                        $pathinfo  = pathinfo($path);
                        $imageInfo = getimagesize($path);
                    }

                    try {
                        if (!empty($pathinfo)) {
                            $this->generatePicture($pathinfo['basename'], $imageInfo, $pathinfo['dirname'], $imageSize, '', $isWoo);
                        } else {
                            if ($isWoo) {
                                return;
                            }
                            wp_send_json(array('status' => false));
                        }
                    } catch (Exception $e) {
                        if ($isWoo) {
                            return;
                        }
                        wp_send_json(array('status' => false));
                    }
                }
            }
        }

        return $metadata;
    }

    /**
     * Ajax create watermark image
     *
     * @return void
     */
    public function regeneratePictures()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        set_time_limit(0);
        /**
         * Filter check capability of current user to create watermark image
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'render_watermark');
        if (!$wpmf_capability) {
            wp_send_json(false);
        }
        global $wpdb;
        $limit         = 1;
        $offset        = ((int) $_POST['paged'] - 1) * $limit;
        $logo_image_id = get_option('wpmf_watermark_image_id', 0);
        $count_images  = $wpdb->get_var($wpdb->prepare(
            'SELECT COUNT(ID) FROM ' . $wpdb->posts . "
             WHERE  post_type = 'attachment' AND ID != %d
              AND post_mime_type LIKE %s AND guid  NOT LIKE %s",
            array($logo_image_id, 'image%', '%.svg')
        ));
        $present       = (100 / $count_images) * $limit;
        $k             = 0;

        $args = array(
            'posts_per_page' => $limit,
            'offset' => $offset,
            'post_type' => 'attachment',
            'post_mime_type' => array('image/jpg', 'image/jpeg', 'image/png', 'image/webp'),
            'post_status' => 'any',
            'orderby' => 'ID',
            'order' => 'DESC',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key'     => 'wpmf_drive_id',
                    'compare' => 'NOT EXISTS'
                ),
                array(
                    'key'     => 'wpmf_awsS3_info',
                    'compare' => 'NOT EXISTS'
                )
            )
        );

        $uploads         = wp_upload_dir();
        $tax_query = array();
        $excludes = array();
        $exclude_folders = wpmfGetOption('watermark_exclude_folders');
        $watermark_apply = get_option('wpmf_image_watermark_apply');
        $option_watermark_only_woo = get_option('wpmf_watermark_only_woo');
        foreach (array_unique($exclude_folders) as $folder) {
            if ($folder === 'root') {
                $excludes[] = 0;
                $root_id = get_option('wpmf_folder_root_id');
                $tax_query[] = array(
                    'taxonomy' => WPMF_TAXO,
                    'field'    => 'term_id',
                    'terms'    => (int)$root_id,
                    'operator' => 'NOT IN'
                );
            } else {
                if ((int) $folder !== 0) {
                    $excludes[] = (int)$folder;
                }
                $tax_query[] = array(
                    'taxonomy' => WPMF_TAXO,
                    'field'    => 'term_id',
                    'terms'    => (int)$folder,
                    'operator' => 'NOT IN'
                );
            }
        }
        if (!empty($tax_query)) {
            $tax_query['relation'] = 'AND';
            $args['tax_query'] = $tax_query;
        }

        $query = new WP_Query($args);
        $attachments = $query->get_posts();
        if (empty($attachments)) {
            wp_send_json(array('status' => 'ok', 'paged' => 0));
        }

        if (empty($watermark_apply)) {
            wp_send_json(array('status' => false));
        }
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $isWoo = false;
                if (!empty($option_watermark_only_woo) && (int) $option_watermark_only_woo === 1 && class_exists('WooCommerce')) {
                    $product_id = $this->getProductIdByImageId($attachment->ID);
                    if (!empty($product_id)) {
                        $isWoo = true;
                    } else {
                        $k ++;
                        continue;
                    }
                }
                // exclude watermark on gallery
                if (is_plugin_active('wp-media-folder-gallery-addon/wp-media-folder-gallery-addon.php')) {
                    $watermark_exclude_public_gallery = wpmfGetOption('watermark_exclude_public_gallery');
                    $watermark_exclude_photograph_gallery = wpmfGetOption('watermark_exclude_photograph_gallery');
                    $check = false;
                    if (!empty($watermark_exclude_public_gallery)) {
                        $gallery_terms = get_the_terms($attachment->ID, WPMF_GALLERY_ADDON_TAXO);
                        if (!empty($gallery_terms)) {
                            $check = true;
                            if (empty($watermark_exclude_photograph_gallery)) {
                                foreach ($gallery_terms as $gallery_term) {
                                    $gallery_type = get_term_meta((int)$gallery_term->term_id, 'gallery_type', true);
                                    if (!empty($gallery_type) && $gallery_type === 'photographer') {
                                        $check = false;
                                    }
                                }
                            }
                        }
                        if ($check) {
                            $k ++;
                            continue;
                        }
                    }

                    if (!empty($watermark_exclude_photograph_gallery)) {
                        $gallery_terms = get_the_terms($attachment->ID, WPMF_GALLERY_ADDON_TAXO);
                        if (!empty($gallery_terms)) {
                            foreach ($gallery_terms as $gallery_term) {
                                $gallery_type = get_term_meta((int)$gallery_term->term_id, 'gallery_type', true);
                                if (!empty($gallery_type) && $gallery_type === 'photographer') {
                                    $check = true;
                                    break;
                                }
                            }
                        }

                        if ($check) {
                            $k ++;
                            continue;
                        }
                    }
                }

                $full_path    = get_attached_file($attachment->ID);
                // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- Ignore warning php if file not exist or not have permission
                if ((int) $logo_image_id === (int) $attachment->ID || !@file_exists($full_path)) {
                    $k ++;
                    continue;
                }

                $terms = get_the_terms($attachment->ID, WPMF_TAXO);
                $folderIDs = array();
                if (empty($terms)) {
                    $folderIDs[] = 0;
                } else {
                    foreach ($terms as $term) {
                        $folderIDs[] = (int) $term->term_id;
                    }
                }

                $same_value = array_intersect($excludes, $folderIDs);
                if (!empty($same_value)) {
                    $k ++;
                    continue;
                }

                $check_remote = get_post_meta($attachment->ID, 'wpmf_remote_video_link');
                if (empty($check_remote)) {
                    if (isset($watermark_apply['all_size']) && (int) $watermark_apply['all_size'] === 1) {
                        $sizes = array_merge(array('full'), get_intermediate_image_sizes());
                        foreach ($sizes as $imageSize) {
                            $image_object = wp_get_attachment_image_src($attachment->ID, $imageSize);
                            // Isolate the url
                            $image_url = $image_object[0];
                            // Using the wp_upload_dir replace the baseurl with the basedir
                            $path      = str_replace($uploads['baseurl'], $uploads['basedir'], $image_url);
                            $pathinfo  = pathinfo($path);
                            $imageInfo = getimagesize($path);
                            try {
                                $this->generatePicture($pathinfo['basename'], $imageInfo, $pathinfo['dirname'], $imageSize, '', $isWoo);
                            } catch (Exception $e) {
                                wp_send_json(array('status' => 'limit', 'percent' => $present));
                            }
                        }
                    } else {
                        foreach ($watermark_apply as $imageSize => $value) {
                            // Isolate the url
                            $metadata = get_post_meta($attachment->ID, '_wp_attachment_metadata', true);
                            if (!isset($metadata['sizes'][$imageSize]) && $imageSize !== 'full') {
                                continue;
                            }

                            $infos    = pathinfo($attachment->guid);
                            // Using the wp_upload_dir replace the baseurl with the basedir
                            if ($imageSize === 'full') {
                                $path = $full_path;
                            } else {
                                $path = str_replace(
                                    $infos['basename'],
                                    $metadata['sizes'][$imageSize]['file'],
                                    $full_path
                                );
                            }

                            if (!file_exists($path)) {
                                wp_send_json(array('status' => 'limit', 'percent' => $present));
                            }

                            $pathinfo  = pathinfo($path);
                            $imageInfo = getimagesize($path);
                            if ((int) $value === 1) {
                                try {
                                    $this->generatePicture($pathinfo['basename'], $imageInfo, $pathinfo['dirname'], $imageSize, '', $isWoo);
                                } catch (Exception $e) {
                                    wp_send_json(array('status' => 'limit', 'percent' => $present));
                                }
                            } else {
                                $check_name_wtm       = 'imageswatermark';
                                $name_change_file_wtm = pathinfo($pathinfo['basename'], PATHINFO_FILENAME) . $check_name_wtm;
                                $name_change_file_wtm .= '.' . pathinfo($pathinfo['basename'], PATHINFO_EXTENSION);
                                $file                 = $pathinfo['dirname'] . '/' . $pathinfo['basename'];
                                $newfile              = $pathinfo['dirname'] . '/' . $name_change_file_wtm;
                                if (file_exists($newfile)) {
                                    unlink($file);
                                    copy($newfile, $file);
                                    unlink($newfile);
                                }
                            }
                        }
                    }
                }
                $k ++;
            }
            if ($k >= $limit) {
                wp_send_json(array('status' => 'limit', 'percent' => $present));
            } else {
                wp_send_json(array('status' => 'ok', 'paged' => 0));
            }
        }
    }

    /**
     * Generate Picture
     *
     * @param string  $newname      New name of image
     * @param array   $imageInfo    Image infomartion
     * @param string  $full_dir     Path of image
     * @param string  $wtm_apply_on Image size
     * @param string  $type         Type
     * @param boolean $isWoo        Check images of woocommerce product
     *
     * @return void
     */
    public function generatePicture($newname, $imageInfo, $full_dir, $wtm_apply_on, $type = '', $isWoo = false)
    {
        $wtm_images             = get_option('wpmf_option_image_watermark');
        //$wtm_apply_on           = get_option('wpmf_image_watermark_apply');
        if ((int) $wtm_images === 0) {
            $logo_image_id = 0;
        } else {
            $logo_image_id = get_option('wpmf_watermark_image_id');
        }
        if ((int) $logo_image_id === 0) {
            $check_image_logo_exit = false;
        } else {
            $wtm_image_logo        = get_attached_file($logo_image_id);
            $info_logo             = pathinfo($wtm_image_logo);
            $check_image_logo_exit = true;
            if (!empty($info_logo['extension']) && !in_array(strtolower($info_logo['extension']), $this->allowed_ext)) {
                $check_image_logo_exit = false;
            }
        }
        $this->copyFileWithNewName($full_dir, $newname, 'initimage', $isWoo);
        if (in_array($imageInfo['mime'], ['image/jpeg', 'image/png', 'image/gif']) && !empty($wtm_images) && $check_image_logo_exit) {
            $this->checkCopyFileWithNewName($full_dir, $newname, $wtm_apply_on, $type, $isWoo);
        }
    }

    /**
     * Generate Picture
     *
     * @param string  $full_dir     Path of image
     * @param string  $newname      New name of image
     * @param string  $wtm_apply_on The sizes to apply watermark
     * @param string  $type         Type
     * @param boolean $isWoo        Check images of woocommerce product
     *
     * @return void
     */
    public function checkCopyFileWithNewName($full_dir, $newname, $wtm_apply_on, $type = '', $isWoo = false)
    {
        $this->copyFileWithNewName($full_dir, $newname, $wtm_apply_on, $type, $isWoo);
    }

    /**
     * Generate Picture
     *
     * @param string  $pathdir    Path to file
     * @param string  $fname      New file name
     * @param string  $wtmApplyOn The size to apply watermark
     * @param string  $type       Type
     * @param boolean $isWoo      Check images of woocommerce product
     *
     * @return void
     */
    public function copyFileWithNewName($pathdir, $fname, $wtmApplyOn, $type = '', $isWoo = false)
    {
        $option_image_watermark = get_option('wpmf_option_image_watermark');
        if ((int) $option_image_watermark === 0) {
            $add_water = false;
        } else {
            $logo_image_id = get_option('wpmf_watermark_image_id');
            if ((int) $logo_image_id === 0) {
                $add_water = false;
            } else {
                $wtm_image_logo = get_attached_file($logo_image_id);
                if (file_exists($wtm_image_logo)) {
                    $add_water = true;
                } else {
                    $add_water = false;
                }
            }
        }

        $wtm_position   = get_option('wpmf_watermark_position');
        $wtm_apply_on   = get_option('wpmf_image_watermark_apply');
        if ($type === 'photographer') {
            $wtm_apply_on = wpmfGetOption('photograper_image_watermark_apply');
        }

        $watermark_image_scaling = wpmfGetOption('watermark_image_scaling');
        $watermark_margin        = wpmfGetOption('watermark_margin');
        $watermark_opacity        = wpmfGetOption('watermark_opacity');
        $watermark_margin_unit        = wpmfGetOption('watermark_margin_unit');
        if ((int) $watermark_opacity > 100) {
            $watermark_opacity = 100;
        }

        if ((int) $watermark_opacity < 0) {
            $watermark_opacity = 0;
        }
        
        $watermark_opacity = (int) $watermark_opacity / 100;

        $check_name_wtm       = 'imageswatermark';
        $name_change_file_wtm = pathinfo($fname, PATHINFO_FILENAME) . $check_name_wtm;
        $name_change_file_wtm .= '.' . pathinfo($fname, PATHINFO_EXTENSION);
        $file                 = $pathdir . '/' . $fname;
        $newfile              = $pathdir . '/' . $name_change_file_wtm;
        if ($wtmApplyOn === 'initimage') {
            if (file_exists($newfile)) {
                if (unlink($file)) {
                    if (copy($newfile, $file)) {
                        unlink($newfile);
                    }
                }
            }
        }

        $option_watermark_only_woo = get_option('wpmf_watermark_only_woo');
        if (!empty($option_watermark_only_woo) && (int) $option_watermark_only_woo === 1 && !$isWoo && class_exists('WooCommerce')) {
            return;
        }

        if ($add_water) {
            if ((int) $wtm_apply_on['all_size'] === 1) {
                if (file_exists($newfile)) {
                    $this->watermark(
                        $file,
                        $wtm_image_logo,
                        $wtm_position,
                        $watermark_margin,
                        $watermark_image_scaling,
                        $watermark_opacity,
                        $watermark_margin_unit
                    );
                } else {
                    if (copy($file, $newfile)) {
                        $this->watermark(
                            $file,
                            $wtm_image_logo,
                            $wtm_position,
                            $watermark_margin,
                            $watermark_image_scaling,
                            $watermark_opacity,
                            $watermark_margin_unit
                        );
                    }
                }
            } else {
                if (empty($wtm_apply_on[$wtmApplyOn])) {
                    if (file_exists($newfile)) {
                        unlink($file);
                        copy($newfile, $file);
                        unlink($newfile);
                    }
                } else {
                    if (file_exists($newfile)) {
                        if (unlink($file)) {
                            if (copy($newfile, $file)) {
                                $this->watermark(
                                    $file,
                                    $wtm_image_logo,
                                    $wtm_position,
                                    $watermark_margin,
                                    $watermark_image_scaling,
                                    $watermark_opacity,
                                    $watermark_margin_unit
                                );
                            }
                        }
                    } else {
                        if (file_exists($file)) {
                            if (copy($file, $newfile)) {
                                $this->watermark(
                                    $file,
                                    $wtm_image_logo,
                                    $wtm_position,
                                    $watermark_margin,
                                    $watermark_image_scaling,
                                    $watermark_opacity,
                                    $watermark_margin_unit
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Create a new image from file or URL
     *
     * @param string $image Path to the JPEG image.
     *
     * @return resource
     */
    public function imagecreatefrom($image)
    {
        $size = getimagesize($image);
        // Load image from file
        switch (strtolower($size['mime'])) {
            case 'image/jpeg':
            case 'image/pjpeg':
                return imagecreatefromjpeg($image);
            case 'image/png':
                return imagecreatefrompng($image);
            case 'image/gif':
                return imagecreatefromgif($image);
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    return imagecreatefromwebp($image);
                } else {
                    return imagecreatefromstring(readfile($image));
                }
            default:
                return imagecreatefromstring(readfile($image));
        }
    }

    /**
     * Opacity watermark
     *
     * @param resource $imageSrc Image resource. Not being modified.
     * @param float    $opacity  Opacity to set from 0 (fully transparent) to 1 (no change)
     *
     * @return resource Transparent image resource
     */
    public function imagesetopacity($imageSrc, $opacity)
    {
        $width = imagesx($imageSrc);
        $height = imagesy($imageSrc);

        // Duplicate image and convert to TrueColor
        $imageDst = imagecreatetruecolor($width, $height);
        imagealphablending($imageDst, false);
        imagefill($imageDst, 0, 0, imagecolortransparent($imageDst));
        imagecopy($imageDst, $imageSrc, 0, 0, 0, 0, $width, $height);

        // Set new opacity to each pixel
        for ($x = 0; $x < $width; ++$x) {
            for ($y = 0; $y < $height; ++$y) {
                $pixelColor = imagecolorat($imageDst, $x, $y);
                $pixelOpacity = 127 - (($pixelColor >> 24) & 0xFF);
                if ($pixelOpacity > 0) {
                    $pixelOpacity = $pixelOpacity * $opacity;
                    $pixelColor = ($pixelColor & 0xFFFFFF) | ((int)round(127 - $pixelOpacity) << 24);
                    imagesetpixel($imageDst, $x, $y, $pixelColor);
                }
            }
        }

        return $imageDst;
    }

    /**
     * Create image with watermark logo
     *
     * @param string  $image_path            Path of image
     * @param string  $logoImage_path        Path of logo
     * @param string  $position              Possition of logo
     * @param array   $watermark_margin      Margin of logo
     * @param integer $percent               Image scaling
     * @param integer $watermark_opacity     Watermark opacity
     * @param integer $watermark_margin_unit Watermark margin unit
     *
     * @return void
     */
    public function watermark($image_path, $logoImage_path, $position, $watermark_margin, $percent, $watermark_opacity, $watermark_margin_unit)
    {
        if (!file_exists($image_path)) {
            die('Image does not exist.');
        }

        try {
            // Find base image size
            $image     = $this->imagecreatefrom($image_path);
            $logoImage = $this->imagecreatefrom($logoImage_path);
            if (!$image || !$logoImage) {
                return;
            }
            // opacity watermark
            if ((int) $watermark_opacity !== 1) {
                $logoImage = $this->imagesetopacity($logoImage, $watermark_opacity);
            }

            list($image_x, $image_y) = getimagesize($image_path);
            list($logo_x, $logo_y) = getimagesize($logoImage_path);
            $watermark_pos_x = 0;
            $watermark_pos_y = 0;

            // get image margin
            if ($watermark_margin_unit === '%') {
                $percent_watermark_margin = array();
                $percent_watermark_margin['left'] = ($image_x / 100) * $watermark_margin['left'];
                $percent_watermark_margin['right'] = ($image_x / 100) * $watermark_margin['right'];
                $percent_watermark_margin['top'] = ($image_y / 100) * $watermark_margin['top'];
                $percent_watermark_margin['bottom'] = ($image_y / 100) * $watermark_margin['bottom'];
                $watermark_margin = $percent_watermark_margin;
            }

            // set image scaling
            $r = $logo_x / $logo_y;
            $new_width = round($image_x * (int) $percent / 100);
            if ($new_width > $logo_x) {
                $new_width = $logo_x;
            }

            $new_height = round($new_width / $r);
            if ($new_height > $logo_y) {
                $new_height = $logo_y;
            }

            if ($position === 'center' || (int) $position === 0) {
                $watermark_pos_x = round(($image_x - $new_width) / 2); //watermark left
                $watermark_pos_y = round(($image_y - $new_height) / 2); //watermark bottom
            }
            if ($position === 'top_left') {
                $watermark_pos_x = (int) $watermark_margin['left'];
                $watermark_pos_y = (int) $watermark_margin['top'];
            }
            if ($position === 'top_right') {
                $watermark_pos_x = round($image_x - $new_width - (int) $watermark_margin['right']);
                $watermark_pos_y = (int) $watermark_margin['top'];
            }
            if ($position === 'bottom_right') {
                $watermark_pos_x = round($image_x - $new_width - (int) $watermark_margin['right']);
                $watermark_pos_y = round($image_y - $new_height - (int) $watermark_margin['bottom']);
            }
            if ($position === 'bottom_left') {
                $watermark_pos_x = (int) $watermark_margin['left'];
                $watermark_pos_y = round($image_y - $new_height - (int) $watermark_margin['bottom']);
            }

            imagecopyresampled(
                $image,
                $logoImage,
                $watermark_pos_x,
                $watermark_pos_y,
                0,
                0,
                $new_width,
                $new_height,
                $logo_x,
                $logo_y
            );
            // Output to the browser
            $imageInfo = getimagesize($image_path);
            switch (strtolower($imageInfo['mime'])) {
                case 'image/jpeg':
                case 'image/pjpeg':
                    header('Content-Type: image/jpeg');
                    imagejpeg($image, $image_path);
                    break;
                case 'image/png':
                    header('Content-Type: image/png');
                    $background = imagecolorallocate($image, 0, 0, 0);
                    // removing the black from the placeholder
                    imagecolortransparent($image, $background);

                    // turning off alpha blending (to ensure alpha channel information
                    // is preserved, rather than removed (blending with the rest of the
                    // image in the form of black))
                    imagealphablending($image, false);

                    // turning on alpha channel information saving (to ensure the full range
                    // of transparency is preserved)
                    imagesavealpha($image, true);
                    imagepng($image, $image_path, 9);
                    break;
                case 'image/gif':
                    header('Content-Type: image/gif');
                    imagegif($image, $image_path);
                    break;
                case 'image/webp':
                    header('Content-Type: image/gif');
                    imagewebp($image, $image_path);
                    break;
                default:
                    die('Image is of unsupported type.');
            }
            // Destroy the images
            imagedestroy($image);
            imagedestroy($logoImage);
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * Optimized function to get WooCommerce product ID by image attachment ID.
     *
     * @param integer $attachment_id The ID of the image attachment.
     *
     * @return integer|boolean Product ID if found, otherwise false.
     */
    public function getProductIdByImageId($attachment_id)
    {
        $args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'meta_query'     => array(
                'relation' => 'OR',
                array(
                    'key'     => '_thumbnail_id',
                    'value'   => $attachment_id,
                    'compare' => '='
                ),
                array(
                    'key'     => '_product_image_gallery',
                    'value'   => $attachment_id,
                    'compare' => 'LIKE'
                ),
            ),
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            $query->the_post();
            $product_id = get_the_ID();
            wp_reset_postdata();
            return $product_id;
        }

        wp_reset_postdata();
        return false;
    }
}
