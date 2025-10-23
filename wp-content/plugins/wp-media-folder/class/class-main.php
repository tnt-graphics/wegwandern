<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
use Joomunited\WPMediaFolder\WpmfHelper;

/**
 * Class WpMediaFolder
 * This class that holds most of the admin functionality for Media Folder.
 */
class WpMediaFolder
{
    /**
     * Id of root Folder
     *
     * @var integer
     */
    public $folderRootId = 0;
    /**
     * Init option configuration variable
     *
     * @var string
     */
    private static $option_google_drive_config = '_wpmfAddon_cloud_config';
    /**
     * Init option configuration variable
     *
     * @var string
     */
    private static $option_dropbox_config = '_wpmfAddon_dropbox_config';
    /**
     * Init option configuration variable
     *
     * @var string
     */
    private static $option_one_drive_config = '_wpmfAddon_onedrive_config';
    /**
     * Init option configuration variable
     *
     * @var string
     */
    private static $option_one_drive_business_config = '_wpmfAddon_onedrive_business_config';
    /**
     * Check user full access
     *
     * @var boolean
     */
    public $user_full_access = true;

    /**
     * AI API endpoint URL.
     *
     * @var string
     */
    private static $aiApiUrl = 'https://ai.joomunited.com/';

    /**
     * Whether to use AI from URL instead of file upload
     *
     * @var boolean
     */
    private static $useAiFromUrl = true;

    /**
     * Replacement base URL to use instead of local site URL when sending file_url.
     *
     * @var string|null
     */
    public static $aiUrlReplace = null;

    /**
     * Wp_Media_Folder constructor.
     */
    public function __construct()
    {
        global $wp_version;
        $root_id            = get_option('wpmf_folder_root_id');
        $this->folderRootId = (int) $root_id;

        if (defined('WPMF_AI_DOMAIN')) {
            $domain = WPMF_AI_DOMAIN;
            if (!empty($domain) && filter_var($domain, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//', $domain)) {
                self::$aiApiUrl = rtrim($domain, '/') . '/';
            }
        }

        self::$useAiFromUrl = get_option('wpmf_ai_send_image_file_fallback', '0') !== '1';

        if (self::$useAiFromUrl && defined('WPMF_AI_URL_REPLACE')) {
            $replace_url = WPMF_AI_URL_REPLACE;
            if (!empty($replace_url) && filter_var($replace_url, FILTER_VALIDATE_URL) && preg_match('/^https?:\/\//', $replace_url)) {
                self::$aiUrlReplace = rtrim($replace_url, '/') . '/';
            }
        }

        add_action('init', array($this, 'includes'));
        add_action('admin_init', array($this, 'adminRedirects'));
        add_action('admin_init', array($this, 'disableTranslateTaxonomyWPML'));

        if (!get_option('wpmf_update_count', false)) {
            add_action('admin_init', array($this, 'updateCountTerm'));
        }

        if (!get_option('_wpmf_import_size_notice_flag', false)) {
            add_action('admin_notices', array($this, 'showNoticeImportSize'), 3);
        }

        if (is_plugin_active('nextgen-gallery/nggallery.php')) {
            if (!get_option('wpmf_import_nextgen_gallery', false)) {
                add_action('admin_notices', array($this, 'showNoticeImportGallery'), 3);
            }
        }

        add_action('restrict_manage_posts', array($this, 'addImageCategoryFilter'));
        add_action('pre_get_posts', array($this, 'preGetPosts1'));
        add_action('admin_enqueue_scripts', array($this, 'adminPageTableScript'));
        add_action('enqueue_block_editor_assets', array($this, 'addEditorAssets'));
        add_action('wp_enqueue_media', array($this, 'mediaPageTableScript'));
        add_action('pre_get_posts', array($this, 'preGetPosts'), 0, 1);
        add_filter('add_attachment', array($this, 'afterUpload'), 0, 1);
        add_filter('media_send_to_editor', array($this, 'addRemoteVideo'), 10, 3);
        add_filter('upload_mimes', array($this, 'allowMimeTypes'));
        add_filter('wp_check_filetype_and_ext', array($this, 'avifUploadCheck'), 10, 4);
        add_filter('wp_check_filetype_and_ext', array($this, 'svgsUploadCheck'), 10, 4);
        add_filter('wp_check_filetype_and_ext', array($this, 'svgsAllowSvgUpload'), 10, 4);
        add_filter('wp_prepare_attachment_for_js', array($this, 'svgsResponseForSvg'), 10, 3);
        add_filter('wp_prepare_attachment_for_js', array($this, 'prepareAttachmentForJs'), 10, 3);
        add_action('admin_footer', array($this, 'editorFooter'));

        $format_mediatitle = 1;
        $settings         = get_option('wpmf_settings');
        if (isset($settings) && isset($settings['format_mediatitle'])) {
            $format_mediatitle = $settings['format_mediatitle'];
        }
        if ((int) $format_mediatitle === 1) {
            add_action('add_attachment', array($this, 'updateFileTitle'));
        }

        add_filter('attachment_fields_to_edit', array($this, 'attachmentFieldsToEdit'), 10, 2);
        add_filter('attachment_fields_to_save', array($this, 'attachmentFieldsToSave'), 10, 2);
        add_action('wpml_media_create_duplicate_attachment', array($this, 'mediaSetFilesToFolderWpml'), 10, 2);
        add_action('wpml_added_media_file_translation', array($this, 'wpmlAddedMediaFileTranslation'), 10, 3);
        add_action('wpml_after_copy_attached_file_postmeta', array($this, 'wpmlAfterCopyAttachedFilePostmeta'), 10, 2);
        add_action('pll_translate_media', array($this, 'pllTranslateMedia'), 10, 3);
        add_action('wp_ajax_wpmf', array($this, 'startProcess'));
        add_action('wp_ajax_wpmf_upload_folder', array($this, 'uploadFolder'));
        add_filter('wpmf_syncMediaExternal', array($this, 'syncMediaExternal'), 10, 2);
        add_action('delete_attachment', array($this, 'deleteAttachment'), 10);
        add_action('wpmf_create_folder', array($this, 'afterCreateFolder'), 10, 4);
        if (version_compare($wp_version, '5.8', '>=')) {
            add_filter('block_categories_all', array($this, 'addBlockCategories'), 10, 2);
        } else {
            add_filter('block_categories', array($this, 'addBlockCategories'), 10, 2);
        }

        add_filter('wp_generate_attachment_metadata', array($this, 'wpUpdateAttachmentIptc'), 10, 2);
        add_filter('wp_handle_upload', array($this, 'handleUpload'), 10, 1);
        add_filter('pre_delete_attachment', array($this, 'preDeleteAttachment'), 10, 3);
        add_action('check_ajax_referer', array($this, 'disableSaveAjax'), 10, 2);
        add_filter('wp_insert_post_empty_content', array($this, 'disableSave'), 999999, 2);
        add_action('pre-upload-ui', array( $this, 'selectFolderUpload'));
        add_filter('add_attachment', array($this, 'moveFileUploadToSelectFolder'), 0, 1);
        add_filter('bulk_actions-upload', array($this, 'registerTagBulkAction'), 10, 1);
        add_action('wp_enqueue_media', array($this, 'removeDatabaseWhenCloudDisconnected'));
        add_action('pre_get_posts', array($this, 'addTagFilter'), 10, 1);
        add_filter('attachment_fields_to_edit', array($this, 'changeTagSlugToName'), 10, 2);
        add_filter('attachment_fields_to_edit', array($this, 'addTagHelps'), 10, 2);
        add_action('pre_delete_attachment', array($this, 'deleteAttachmentCloud'), 11, 2);
        add_filter('wp_save_image_editor_file', array($this, 'editImage'), 10, 5);
        add_action('load-media-new.php', array($this, 'defaultMultiFileUploader'));
        add_filter('wp_insert_attachment_data', array($this, 'handleFileWhenUploadPlugin'), 10, 1);
        add_filter('posts_clauses', array($this, 'customOrderByMetaValueNumAndTitle'), 10, 2);

        add_action('admin_init', array($this, 'runAIQuotaOnce'));
        add_filter('wp_prepare_attachment_for_js', array($this, 'wpPrepareAttachmentForJs'), 99, 3);
        add_action('wp_ajax_wpmf_analyze_image_ai', array($this, 'analyzeImageWithAI'));
        add_action('wp_ajax_nopriv_wpmf_handle_ai_fallback', array($this, 'handleAiFallback'));
        add_action('wp_ajax_wpmf_check_ai_result', array($this, 'checkAiResult'));
        add_action('wp_ajax_wpmf_get_ai_progress', array($this, 'getAIProgress'));
        add_action('wp_ajax_wpmf_get_ai_quota', array($this, 'getAIQuota'));
        add_action('wp_ajax_wpmf_get_attachments_in_folder', array($this, 'getAttachmentsInFolder'));

        if (get_option('wpmf_ai_new_ai_auto_optimization') === '1') {
            add_filter('wp_generate_attachment_metadata', array($this, 'handleAnalyzeImageWithAI'), 11, 2);
        } else {
            if (get_option('wpmf_ai_rename_image_upload') === '1') {
                add_filter('wp_generate_attachment_metadata', array($this, 'handleRenameImageAIOnly'), 11, 2);
            }
        }

        add_action('admin_enqueue_scripts', array($this, 'enqueueAIAdminStyles'));

        $wpmf_ai_admin_bar = get_option('wpmf_ai_admin_bar', '1');
        if (is_admin() && (int) $wpmf_ai_admin_bar === 1) {
            add_action('admin_bar_menu', array($this, 'addAIQuotaAdminBarItem'), 100);
        }
    }

    /**
     * Fires once the Ajax request has been validated or not.
     *
     * @param string        $action The Ajax nonce action.
     * @param false|integer $result False if the nonce is invalid, 1 if the nonce is valid and generated between
     *                          0-12 hours ago, 2 if the nonce is valid and generated between 12-24 hours ago.
     *
     * @return void
     */
    public function disableSaveAjax($action, $result)
    {
        if (strpos($action, 'update-post_') !== false) {
            $pid = str_replace('update-post_', '', $action);
            $attachment = get_post((int)$pid);
            if (!is_wp_error($attachment) && !empty($attachment) && $attachment->post_type === 'attachment') {
                $active_media = get_option('wpmf_active_media');
                if (!empty($active_media)) {
                    $user_id = get_current_user_id();
                    $terms = get_the_terms((int)$pid, WPMF_TAXO);
                    $check = null;
                    if (!empty($terms) && is_array($terms)) {
                        foreach ($terms as $term) {
                            $is_access = WpmfHelper::getAccess($term->term_id, $user_id, 'update_media');
                            if (!$is_access) {
                                $check = 'not_permission';
                                break;
                            }
                        }
                    }

                    if ($check === 'not_permission') {
                        wp_die(esc_html__('You do not have permission to update this attachment', 'wpmf'));
                    }
                }
            }
        }
    }

    /**
     * Filters whether the post should be considered "empty".
     *
     * @param boolean $maybe_empty Whether the post should be considered "empty".
     * @param array   $postarr     Array of post data.
     *
     * @return boolean
     */
    public function disableSave($maybe_empty, $postarr)
    {
        $active_media = get_option('wpmf_active_media');
        if (empty($active_media)) {
            return $maybe_empty;
        }

        $user_id = get_current_user_id();
        $terms = get_the_terms($postarr['ID'], WPMF_TAXO);
        if (empty($terms)) {
            return $maybe_empty;
        }

        $check = null;
        foreach ($terms as $term) {
            $is_access = WpmfHelper::getAccess($term->term_id, $user_id, 'update_media');
            if (!$is_access) {
                $check = 'not_permission';
                break;
            }
        }

        if ($check === 'not_permission') {
            wp_die(esc_html__('You do not have permission to update this attachment', 'wpmf'));
        }
        return $maybe_empty;
    }

    /**
     * Filters whether an attachment deletion should take place.
     *
     * @param boolean|null $delete       Whether to go forward with deletion.
     * @param WP_Post      $post         Post object.
     * @param boolean      $force_delete Whether to bypass the Trash.
     *
     * @return boolean|null
     */
    public function preDeleteAttachment($delete, $post, $force_delete)
    {
        $active_media = get_option('wpmf_active_media');
        if (empty($active_media)) {
            return $delete;
        }

        $user_id = get_current_user_id();
        $terms = get_the_terms($post->ID, WPMF_TAXO);

        if (empty($terms)) {
            return $delete;
        }

        $check = null;
        foreach ($terms as $term) {
            $is_access = WpmfHelper::getAccess($term->term_id, $user_id, 'remove_media');
            if (!$is_access) {
                $check = true;
                break;
            }
        }

        if (null !== $check) {
            wp_die(esc_html__('You do not have permission to delete this attachment', 'wpmf'));
        }

        return $delete;
    }

    /**
     * Handler upload
     *
     * @param array $data Attachment data
     *
     * @return mixed
     */
    public function handleUpload($data)
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        if (isset($_REQUEST['wpmf_folder'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
            $parent = $this->getFolderParent($_POST['wpmf_folder'], $_POST['wpmf_folder']);
            $user_id  = get_current_user_id();
            $is_access = WpmfHelper::getAccess($parent, $user_id, 'add_media');
            if (!$is_access) {
                $data['error'] = esc_html__('You not have a permission to upload the file to this folder!', 'wpmf');
            }
        }

        return $data;
    }

    /**
     * Update attachment metadata
     *
     * @param array   $data    Meta data
     * @param integer $post_id Attachment ID
     *
     * @return array
     */
    public function wpUpdateAttachmentIptc($data, $post_id)
    {
        if (is_null($data)) {
            $data = wp_get_attachment_metadata($post_id, true);
        }

        $import_iptc_meta = wpmfGetOption('import_iptc_meta');

        if ((int)$import_iptc_meta === 1) {
            $attachment = get_post($post_id);
            if (strpos($attachment->post_mime_type, 'image') !== false) {
                $iptc_fields = wpmfGetOption('iptc_fields');
                $filepath = get_attached_file($post_id);
                $title = '';
                $caption = '';
                if (!empty($data['image_meta']['title']) && !empty($iptc_fields['title'])) {
                    $title = $data['image_meta']['title'];
                }

                if (!empty($data['image_meta']['caption']) && !empty($iptc_fields['caption'])) {
                    $caption = $data['image_meta']['caption'];
                }

                global $wpdb;
                // update post
                $field = array();
                if ($title !== '') {
                    $field['post_title'] = $title;
                }

                if ($caption !== '') {
                    $field['post_excerpt'] = $caption;
                }

                // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged -- Ignore warning php if can't read data
                $exif = @exif_read_data($filepath);
                if (!empty($exif['ImageDescription']) && $exif['ImageDescription'] !== null && !empty($iptc_fields['description'])) {
                    $field['post_content'] = $exif['ImageDescription'];
                    $format[] = '%s';
                }

                if (!empty($field)) {
                    $wpdb->update(
                        $wpdb->posts,
                        $field,
                        array('ID' => $post_id)
                    );
                }

                WpmfHelper::saveIptcMetadata($import_iptc_meta, $post_id, $filepath, $iptc_fields, $title, $attachment->post_mime_type);
            }
        }

        return $data;
    }

    /**
     * Check cloud media
     *
     * @param array          $response   Array of prepared attachment data.
     * @param integer|object $attachment Attachment ID or object.
     * @param array          $meta       Array of attachment meta data.
     *
     * @return mixed $response
     */
    public function prepareAttachmentForJs($response, $attachment, $meta)
    {
        $drive_meta = get_post_meta($attachment->ID, 'wpmf_drive_id', true);
        if (!empty($drive_meta)) {
            $response['cloud_media'] = 1;
        } else {
            $response['cloud_media'] = 0;
        }

        $remote_video = get_post_meta($attachment->ID, 'wpmf_remote_video_link', true);
        if (!empty($remote_video)) {
            $response['is_video'] = 1;
            $response['video_url'] = $remote_video;
        } else {
            $response['is_video'] = 0;
            $response['video_url'] = '';
        }

        return $response;
    }

    /**
     * Add blocks category
     *
     * @param array  $categories List of current blocks categories
     * @param object $post       Current post
     *
     * @return array New array include our category
     */
    public function addBlockCategories($categories, $post)
    {
        $newcats = array(
            array(
                'slug' => 'wp-media-folder',
                'title' => esc_html__('WP Media Folder', 'wpmf')
            )
        );
        return array_merge($categories, $newcats);
    }

    /**
     * Update count files in a folder
     *
     * @return void
     */
    public function updateCountTerm()
    {
        global $wpdb;
        $terms = get_categories(
            array(
                'hide_empty' => false,
                'taxonomy'   => WPMF_TAXO,
                'fields'     => 'ids'
            )
        );

        foreach ((array) $terms as $term) {
            $count = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . $wpdb->term_relationships . ' t, ' . $wpdb->posts . ' p1 WHERE p1.ID = t.object_id AND ( post_status = %s OR post_status = %s) AND post_type = %s AND term_taxonomy_id = %d', array('publish','inherit','attachment',$term)));
            $wpdb->update($wpdb->term_taxonomy, array('count' => $count), array('term_taxonomy_id' => (int) $term));
        }

        update_option('wpmf_update_count', 1);
    }

    /**
     * Delete attachment
     *
     * @param integer $pid Attachment ID
     *
     * @return void
     */
    public function deleteAttachment($pid)
    {
        $file_path = get_attached_file($pid);
        if (!file_exists($file_path)) {
            return;
        }
        $origin_infos = pathinfo($file_path);
        $origin_name = $origin_infos['filename'] . 'imageswatermark.' . $origin_infos['extension'];
        $paths = array(
            'origin' => str_replace(wp_basename($file_path), $origin_name, $file_path)
        );
        $metas = get_post_meta($pid, '_wp_attachment_metadata', true);
        if (isset($metas['sizes'])) {
            foreach ($metas['sizes'] as $size => $file) {
                $infos = pathinfo($file['file']);
                if (!empty($infos['extension'])) {
                    $filewater = $infos['filename'] . 'imageswatermark.' . $infos['extension'];
                    $paths[$size] = str_replace(wp_basename($file_path), $filewater, $file_path);
                }
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
    }

    /**
     * Disable translate taxonomy WPML
     *
     * @return void
     */
    public function disableTranslateTaxonomyWPML()
    {
        if (defined('ICL_SITEPRESS_VERSION') && ICL_SITEPRESS_VERSION) {
            global $iclTranslationManagement;
            if (isset($iclTranslationManagement->settings['taxonomies_readonly_config']) && is_array($iclTranslationManagement->settings['taxonomies_readonly_config'])) {
                $iclTranslationManagement->settings['taxonomies_readonly_config'][ 'wpmf-category' ] = 1;
                $iclTranslationManagement->settings['taxonomies_readonly_config'][ 'wpmf-gallery-category' ] = 1;
            }
        }
    }

    /**
     * Handle redirects to setup/welcome page after install and updates.
     *
     * For setup wizard, transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
     *
     * @return void
     */
    public function adminRedirects()
    {
        //global $sitepress;
        //var_dump($sitepress->is_translated_taxonomy(WPMF_TAXO));
        // Disable all admin notice for page belong to plugin
        add_action('admin_print_scripts', function () {
            global $wp_filter;
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
            if ((!empty($_GET['page']) && in_array($_GET['page'], array('wpmf-setup', 'option-folder')))) {
                if (is_user_admin()) {
                    if (isset($wp_filter['user_admin_notices'])) {
                        unset($wp_filter['user_admin_notices']);
                    }
                } elseif (isset($wp_filter['admin_notices'])) {
                    unset($wp_filter['admin_notices']);
                }
                if (isset($wp_filter['all_admin_notices'])) {
                    unset($wp_filter['all_admin_notices']);
                }
            }
        });

        // Setup wizard redirect
        if (is_null(get_option('_wpmf_activation_redirect', null)) && is_null(get_option('wpmf_version', null))) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View request, no action
            if ((!empty($_GET['page']) && in_array($_GET['page'], array('wpmf-setup')))) {
                return;
            }

            update_option('_wpmf_activation_redirect', 1);
            wp_safe_redirect(admin_url('index.php?page=wpmf-setup'));
            exit;
        }
    }

    /**
     * Includes WP Media Folder setup
     *
     * @return void
     */
    public function includes()
    {
        // check user full access
        $this->user_full_access = WpmfHelper::checkUserFullAccess();
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View request, no action
        if (!empty($_GET['page'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View request, no action
            switch ($_GET['page']) {
                case 'wpmf-setup':
                    require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/install-wizard/install-wizard.php';
                    break;
            }
        }
    }

    /**
     * Sync media from media library to server
     *
     * @param integer $folderID      Id folder on media library
     * @param integer $attachment_id Id of file
     *
     * @return mixed
     */
    public function syncMediaExternal($folderID = 0, $attachment_id = 0)
    {
        $lists      = get_option('wpmf_list_sync_media');
        $folder_ftp = '';
        if (isset($lists[$folderID])) {
            $folder_ftp = $lists[$folderID]['folder_ftp'];
        }

        $file_path = get_attached_file($attachment_id);

        $filename = pathinfo($file_path);
        if (file_exists($file_path) && file_exists($folder_ftp)) {
            copy($file_path, $folder_ftp . '/' . $filename['basename']);
        }

        return $folderID;
    }

    /**
     * Run ajax
     *
     * @return void
     */
    public function startProcess()
    {
        if (empty($_REQUEST['wpmf_nonce'])
            || !wp_verify_nonce($_REQUEST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        if (isset($_REQUEST['task'])) {
            switch ($_REQUEST['task']) {
                case 'reload_folder_tree':
                    $this->reloadFolderTree();
                    break;
                case 'import':
                    $this->importCategories();
                    break;
                case 'add_folder':
                    $this->addFolder();
                    break;
                case 'edit_folder':
                    $this->editFolder();
                    break;
                case 'delete_folder':
                    $this->deleteFolder();
                    break;
                case 'delete_multiple_folders':
                    $this->deleteMultipleFolders();
                    break;
                case 'download_folder':
                    $this->downloadFolder();
                    break;
                case 'move_file':
                    $this->moveFile();
                    break;
                case 'move_folder':
                    $this->moveFolder();
                    break;
                case 'get_terms':
                    $this->getTerms();
                    break;
                case 'get_assign_tree':
                    $this->getAssignTree();
                    break;
                case 'gallery_get_image':
                    $this->galleryGetImage();
                    break;
                case 'set_object_term':
                    $this->setObjectTerm();
                    break;
                case 'create_remote_video':
                    $this->createRemoteVideo();
                    break;
                case 'get_user_media_tree':
                    $this->getUserMediaTree();
                    break;
                case 'set_folder_color':
                    $this->setFolderColor();
                    break;
                case 'delete_file':
                    $this->deleteFile();
                    break;
                case 'reorderfile':
                    $this->reorderFile();
                    break;
                case 'reorderfolder':
                    $this->reorderfolder();
                    break;
                case 'import_order':
                    $this->importOrder();
                    break;
                case 'update_link':
                    $this->updateLink();
                    break;
                case 'getcountfiles':
                    $this->getCountFilesInFolder();
                    break;
                case 'get_folder_permissions':
                    $this->getFolderPermissions();
                    break;
                case 'save_folder_permissions':
                    $this->saveFolderPermissions();
                    break;
                case 'auto_load_video_thumbnail':
                    $this->autoLoadVideoThumbnail();
                    break;
                case 'check_local_to_cloud':
                    $this->checkLocalToCloud();
                    break;
                case 'wpmf_download_file':
                    $this->wpmfDownloadFile();
                    break;
                case 'get_tag_item':
                    $this->getTagItem();
                    break;
                case 'save_tag_item':
                    $this->saveTagItem();
                    break;
            }
        }
    }

    /**
     * Auto load video thumbnail
     *
     * @return void
     */
    public function autoLoadVideoThumbnail()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        $thumb_url = '';
        $video_url = (isset($_POST['video_url'])) ? $_POST['video_url'] : '';
        if (!preg_match(WpmfHelper::$vimeo_pattern, $video_url, $output_array)
            && !preg_match('/(youtube.com|youtu.be)\/(watch)?(\?v=)?(\S+)?/', $video_url, $match)
            && !preg_match('/\b(?:dailymotion)\.com\b/i', $video_url, $vresult)
            && !preg_match('/(videos.kaltura)\.com\b/i', $video_url, $vresult)) {
            wp_send_json(array('status' => false));
        } elseif (preg_match(WpmfHelper::$vimeo_pattern, $video_url, $output_array)) {
            // for vimeo
            $id = WpmfHelper::getVimeoVideoIdFromUrl($video_url);
            $videos = wp_remote_get('https://player.vimeo.com/video/' . $id . '/config');
            $body = json_decode($videos['body']);
            if (!empty($body->video->thumbs->base)) {
                $thumb_url = $body->video->thumbs->base;
            } else {
                $videos = wp_remote_get('https://vimeo.com/api/v2/video/' . $id . '.json');
                $body = json_decode($videos['body']);
                $body = $body[0];
                $thumb_url = '';
                if (isset($body->thumbnail_large)) {
                    $thumb_url = $body->thumbnail_large;
                } elseif (isset($body->thumbnail_medium)) {
                    $thumb_url = $body->thumbnail_large;
                } elseif (isset($body->thumbnail_small)) {
                    $thumb_url = $body->thumbnail_small;
                }
            }
        } elseif (preg_match('/(youtube.com|youtu.be)\/(watch)?(\?v=)?(\S+)?/', $video_url, $match)) {
            // for youtube
            $parts = parse_url($video_url);
            if ($parts['host'] === 'youtu.be') {
                $id = trim($parts['path'], '/');
            } else {
                parse_str($parts['query'], $query);
                $id = $query['v'];
            }

            $thumb_url = 'http://img.youtube.com/vi/' . $id . '/maxresdefault.jpg';
            $timeout = apply_filters('add_remote_video_youtube_timeout', 5);
            $gets = wp_remote_get($thumb_url, array('timeout' => $timeout));
            if (!empty($gets) && $gets['response']['code'] !== 200) {
                $thumb_url = 'http://img.youtube.com/vi/' . $id . '/sddefault.jpg';
                $gets = wp_remote_get($thumb_url, array('timeout' => $timeout));
            }

            if (!empty($gets) && $gets['response']['code'] !== 200) {
                $thumb_url = 'http://img.youtube.com/vi/' . $id . '/hqdefault.jpg';
                $gets = wp_remote_get($thumb_url, array('timeout' => $timeout));
            }

            if (!empty($gets) && $gets['response']['code'] !== 200) {
                $thumb_url = 'http://img.youtube.com/vi/' . $id . '/mqdefault.jpg';
                $gets = wp_remote_get($thumb_url, array('timeout' => $timeout));
            }

            if (!empty($gets) && $gets['response']['code'] !== 200) {
                $thumb_url = 'http://img.youtube.com/vi/' . $id . '/default.jpg';
            }
        } elseif (preg_match('/\b(?:dailymotion)\.com\b/i', $video_url, $vresult)) {
            // for dailymotion
            $id   = WpmfHelper::getDailymotionVideoIdFromUrl($video_url);
            $gets = wp_remote_get('http://www.dailymotion.com/services/oembed?format=json&url=http://www.dailymotion.com/embed/video/' . $id);
            $info = json_decode($gets['body'], true);
            if (empty($info)) {
                wp_send_json(array('status' => false));
            }

            if (!empty($info['thumbnail_url'])) {
                $thumb_url = $info['thumbnail_url'];
            }
        } elseif (preg_match('/(videos.kaltura)\.com\b/i', $video_url, $vresult)) {
            // for kaltura
            $id   = WpmfHelper::getKalturaVideoIdFromUrl($video_url);
            $partner_id = 5944002;
            $thumb_url = 'http://cdnsecakmi.kaltura.com/p/' . $partner_id . '/thumbnail/entry_id/' . $id . '/width/2560/height/1920';
        }

        if (!empty($thumb_url)) {
            wp_send_json(array('status' => true, 'thumb_url' => $thumb_url));
        }
        wp_send_json(array('status' => false));
    }

    /**
     * Fires after attachment post meta is copied
     *
     * @param integer $original_attachment_id The ID of the source/original attachment.
     * @param integer $attachment_id          The ID of the duplicated attachment.
     *
     * @return void
     */
    public function wpmlAfterCopyAttachedFilePostmeta($original_attachment_id, $attachment_id)
    {
        $terms = wp_get_post_terms($original_attachment_id, WPMF_TAXO, array('fields' => 'ids'));
        if (!empty($terms)) {
            foreach ($terms as $id_term) {
                wp_set_object_terms($attachment_id, $id_term, WPMF_TAXO, true);
                $this->addSizeFiletype($attachment_id);
            }
        }
    }

    /**
     * Added media file translation
     *
     * @param integer $original_attachment_id   The ID of the source/original attachment.
     * @param string  $file                     Absolute path to file.
     * @param string  $translated_language_code Attachment language code.
     *
     * @return void
     */
    public function wpmlAddedMediaFileTranslation($original_attachment_id, $file, $translated_language_code)
    {
        global $sitepress;
        $attachment_translations = $sitepress->get_element_translations($original_attachment_id, 'post_attachment', true, true);
        if (is_array($attachment_translations)) {
            foreach ($attachment_translations as $attachment_translation) {
                if ($attachment_translation->language_code === $translated_language_code) {
                    $terms = wp_get_post_terms($original_attachment_id, WPMF_TAXO, array('fields' => 'ids'));
                    if (!empty($terms)) {
                        foreach ($terms as $id_term) {
                            wp_set_object_terms($attachment_translation->element_id, $id_term, WPMF_TAXO, true);
                            $this->addSizeFiletype($attachment_translation->element_id);
                        }
                    }
                }
            }
        }
    }

    /**
     * Sync folders/files structure in all languages
     *
     * @param integer $attachment_id            ID of media file
     * @param integer $duplicated_attachment_id ID of duplicate media file
     *
     * @return void
     */
    public function mediaSetFilesToFolderWpml($attachment_id, $duplicated_attachment_id)
    {
        $terms = wp_get_post_terms($attachment_id, WPMF_TAXO, array('fields' => 'ids'));
        if (!empty($terms)) {
            foreach ($terms as $id_term) {
                wp_set_object_terms($duplicated_attachment_id, $id_term, WPMF_TAXO, true);
                $this->addSizeFiletype($duplicated_attachment_id);

                /**
                 * Set attachment folder after upload with WPML plugin
                 *
                 * @param integer Attachment ID
                 * @param integer Target folder
                 * @param array   Extra informations
                 *
                 * @ignore Hook already documented
                 */
                do_action('wpmf_attachment_set_folder', $duplicated_attachment_id, $id_term, array('trigger' => 'upload'));
            }
        }
    }

    /**
     * Sync folders/files structure in all languages
     *
     * @param integer $attachment_id            ID of media file
     * @param integer $duplicated_attachment_id ID of duplicate media file
     * @param string  $lang                     Current language
     *
     * @return void
     */
    public function pllTranslateMedia($attachment_id, $duplicated_attachment_id, $lang)
    {
        $terms = wp_get_post_terms($attachment_id, WPMF_TAXO, array('fields' => 'ids'));
        if (!empty($terms)) {
            foreach ($terms as $id_term) {
                wp_set_object_terms($duplicated_attachment_id, $id_term, WPMF_TAXO, true);
                $this->addSizeFiletype($duplicated_attachment_id);

                /**
                 * Set attachment folder after upload with WPML plugin
                 *
                 * @param integer Attachment ID
                 * @param integer Target folder
                 * @param array   Extra informations
                 *
                 * @ignore Hook already documented
                 */
                do_action('wpmf_attachment_set_folder', $duplicated_attachment_id, $id_term, array('trigger' => 'upload'));
            }
        }
    }

    /**
     * Filters the attachment data prepared for JavaScript.
     * Base on /wp-includes/media.php
     *
     * @param array          $response   Array of prepared attachment data.
     * @param integer|object $attachment Attachment ID or object.
     * @param array          $meta       Array of attachment meta data.
     *
     * @return mixed $response
     */
    public function svgsResponseForSvg($response, $attachment, $meta)
    {
        if ($response['mime'] === 'image/svg+xml' && empty($response['sizes'])) {
            $svg_path = get_attached_file($attachment->ID);
            if (!file_exists($svg_path)) {
                // If SVG is external, use the URL instead of the path
                $svg_path = $response['url'];
            }

            if (!file_exists($svg_path)) {
                return $response;
            }

            $dimensions        = $this->svgsGetDimensions($svg_path);
            $response['sizes'] = array(
                'full' => array(
                    'url'         => $response['url'],
                    'width'       => $dimensions->width,
                    'height'      => $dimensions->height,
                    'orientation' => $dimensions->width > $dimensions->height ? 'landscape' : 'portrait'
                )
            );
        }

        return $response;
    }

    /**
     * Get dimension svg file
     *
     * @param string $svg Path of svg
     *
     * @return object width and height
     */
    public function svgsGetDimensions($svg)
    {
        $svg = simplexml_load_file($svg);
        if ($svg === false) {
            $width  = '0';
            $height = '0';
        } else {
            $attributes = $svg->attributes();
            $width      = (string) $attributes->width;
            $height     = (string) $attributes->height;
        }

        return (object) array('width' => $width, 'height' => $height);
    }

    /**
     * Filters list of allowed mime types and file extensions.
     *
     * @param array $mimes Mime types keyed by the file extension regex corresponding to
     *                     those types. 'swf' and 'exe' removed from full list. 'htm|html' also
     *                     removed depending on '$user' capabilities.
     *
     * @return array $mimes
     */
    public function allowMimeTypes($mimes = array())
    {
        $mimes['svg']  = 'image/svg+xml';
        $mimes['svgz'] = 'image/svg+xml';
        $mimes['xlsm'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $mimes['json'] = 'application/json';
        $mimes['avif'] = 'image/avif';
        return $mimes;
    }

    /**
     * Filters the "real" file type of the given file.
     *
     * @param array  $checked  File data array containing 'ext', 'type', and
     *                         'proper_filename' keys.
     * @param string $file     Full path to the file.
     * @param string $filename The name of the file (may differ from $file due to
     *                         $file being in a tmp directory).
     * @param array  $mimes    Key is the file extension with value as the mime type.
     *
     * @return array
     */
    public function avifUploadCheck($checked, $file, $filename, $mimes)
    {
        if (!$checked['type']) {
            $check_filetype = wp_check_filetype($filename, $mimes);
            $ext = $check_filetype['ext'];
            $type = $check_filetype['type'];
            $proper_filename = $filename;
            if ($type && 0 === strpos($type, 'image/') && $ext !== 'avif') {
                $ext = false;
                $type = false;
            }
            $checked = compact('ext', 'type', 'proper_filename');
        }
        return $checked;
    }

    /**
     * Filters the "real" file type of the given file.
     *
     * @param array  $checked  File data array containing 'ext', 'type', and
     *                         'proper_filename' keys.
     * @param string $file     Full path to the file.
     * @param string $filename The name of the file (may differ from $file due to
     *                         $file being in a tmp directory).
     * @param array  $mimes    Key is the file extension with value as the mime type.
     *
     * @return array
     */
    public function svgsUploadCheck($checked, $file, $filename, $mimes)
    {
        if (!$checked['type']) {
            $check_filetype = wp_check_filetype($filename, $mimes);
            $ext = $check_filetype['ext'];
            $type = $check_filetype['type'];
            $proper_filename = $filename;

            if ($type && 0 === strpos($type, 'image/') && $ext !== 'svg') {
                $ext = false;
                $type = false;
            }

            $checked = compact('ext', 'type', 'proper_filename');
        }

        return $checked;
    }

    /**
     * Mime Check fix for WP 4.7.1 / 4.7.2
     *
     * @param array  $data     File data
     * @param string $file     Full path to the file.
     * @param string $filename The name of the file (may differ from $file due to $file being in a tmp directory).
     * @param array  $mimes    Array of mime types keyed by their file extension regex.
     *
     * @return array
     */
    public function svgsAllowSvgUpload($data, $file, $filename, $mimes)
    {
        global $wp_version;
        if ($wp_version !== '4.7.1' || $wp_version !== '4.7.2') {
            return $data;
        }

        $filetype = wp_check_filetype($filename, $mimes);

        return array(
            'ext' => $filetype['ext'],
            'type' => $filetype['type'],
            'proper_filename' => $data['proper_filename']
        );
    }

    /**
     * Ajax get gallery image
     *
     * @return void
     */
    public function galleryGetImage()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user when get gallery image
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'gallery_sort_image');
        if (!$wpmf_capability) {
            wp_send_json(false);
        }
        if (!empty($_POST['ids']) && isset($_POST['wpmf_orderby']) && isset($_POST['wpmf_order'])) {
            $ids          = $_POST['ids'];
            $wpmf_orderby = $_POST['wpmf_orderby'];
            $wpmf_order   = $_POST['wpmf_order'];
            if ($wpmf_orderby === 'title' || $wpmf_orderby === 'date') {
                $wpmf_orderby = 'post_' . $wpmf_orderby;
                // query attachment by orderby and order
                $args  = array(
                    'posts_per_page' => - 1,
                    'post_type'      => 'attachment',
                    'post__in'       => $ids,
                    'post_status'    => 'any',
                    'orderby'        => $wpmf_orderby,
                    'order'          => $wpmf_order
                );
                $query = new WP_Query($args);
                $posts = $query->get_posts();
                wp_send_json($posts);
            }
        }
        wp_send_json(false);
    }

    /**
     * Load styles
     *
     * @return void
     */
    public function loadAssetsMediaUpload()
    {
        wp_enqueue_style(
            'wpmf-style',
            plugins_url('/assets/css/style.css', dirname(__FILE__)),
            array(),
            WPMF_VERSION
        );
        
        wp_register_script(
            'wpmf-base',
            plugins_url('/assets/js/script.js', dirname(__FILE__)),
            array('jquery', 'plupload'),
            WPMF_VERSION
        );

        wp_enqueue_script('wpmf-base');

        wp_register_script(
            'wpmf-folder-upload',
            plugins_url('/assets/js/folder_upload.js', dirname(__FILE__)),
            array('jquery'),
            WPMF_VERSION
        );
        
        wp_enqueue_script('wpmf-folder-upload');

        wp_register_script(
            'wpmf-folder-tree',
            plugins_url('/assets/js/folder_tree.js', dirname(__FILE__)),
            array('wpmf-base'),
            WPMF_VERSION
        );
        wp_enqueue_script('wpmf-folder-tree');

        $params = $this->localizeScript();
        wp_localize_script('wpmf-base', 'wpmf', $params);
    }

    /**
     * Load styles
     *
     * @return void
     */
    public function loadAssets()
    {
        global $typenow, $current_screen;

        if (WpmfHelper::isForThisPostType($typenow) && 'edit' === $current_screen->base) {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');
        wp_enqueue_style(
            'wpmf-material-icon',
            plugins_url('/assets/css/google-material-icon.css', dirname(__FILE__)),
            array(),
            WPMF_VERSION
        );

        if (!get_option('_wpmf_import_order_notice_flag', false)) {
            wp_enqueue_script(
                'import-custom-order',
                plugins_url('assets/js/imports/import_custom_order.js', dirname(__FILE__)),
                array('jquery'),
                WPMF_VERSION
            );
        }

        wp_enqueue_style(
            'wpmf-jaofiletree',
            plugins_url('/assets/css/jaofiletree.css', dirname(__FILE__)),
            array(),
            WPMF_VERSION
        );

        wp_enqueue_style(
            'wpmf-style',
            plugins_url('/assets/css/style.css', dirname(__FILE__)),
            array(),
            WPMF_VERSION
        );

        wp_enqueue_style(
            'wpmf-mdl',
            plugins_url('/assets/css/modal-dialog/mdl-jquery-modal-dialog.css', dirname(__FILE__)),
            array(),
            WPMF_VERSION
        );

        wp_enqueue_style(
            'wpmf-deep_orange',
            plugins_url('/assets/css/modal-dialog/material.deep_orange-amber.min.css', dirname(__FILE__)),
            array(),
            WPMF_VERSION
        );

        wp_enqueue_style(
            'wpmf-style-tippy',
            plugins_url('/assets/js/tippy/tippy.css', dirname(__FILE__)),
            array(),
            WPMF_VERSION
        );

        wp_enqueue_script(
            'wpmf-material',
            plugins_url('/assets/js/modal-dialog/material.min.js', dirname(__FILE__)),
            array('jquery'),
            WPMF_VERSION
        );

        wp_enqueue_script(
            'wpmf-mdl',
            plugins_url('/assets/js/modal-dialog/mdl-jquery-modal-dialog.js', dirname(__FILE__)),
            array('jquery'),
            WPMF_VERSION
        );

        wp_enqueue_style(
            'wpmf-gallery-popup-style',
            plugins_url('/assets/css/display-gallery/magnific-popup.css', dirname(__FILE__)),
            array(),
            WPMF_VERSION
        );

        wp_enqueue_style(
            'wpmf-tagify-style',
            plugins_url('/assets/css/tagify.css', dirname(__FILE__)),
            array(),
            WPMF_VERSION
        );

        wp_enqueue_script(
            'wpmf-gallery-popup',
            plugins_url('/assets/js/display-gallery/jquery.magnific-popup.min.js', dirname(__FILE__)),
            array('jquery'),
            WPMF_VERSION,
            true
        );

        global $current_screen;
        if (isset($current_screen) && $current_screen->id !== 'forminator_page_forminator-cform-wizard' && $current_screen->id !== 'post') {
            wp_enqueue_style(
                'wpmfselect2',
                plugins_url('/assets/select2/select2.min.css', dirname(__FILE__)),
                array(),
                WPMF_VERSION
            );

            wp_enqueue_script(
                'wpmfselect2',
                plugins_url('/assets/select2/select2.min.js', dirname(__FILE__)),
                array('jquery'),
                WPMF_VERSION
            );
        }

        wp_register_script(
            'wpmf-tagify',
            plugins_url('/assets/js/tagify.js', dirname(__FILE__)),
            array(),
            WPMF_VERSION
        );

        wp_enqueue_script('wpmf-tagify');

        wp_enqueue_script('resumable', plugins_url('/assets/js/resumable.js', dirname(__FILE__)), array('jquery'), WPMF_VERSION);
        wp_register_script(
            'wpmf-base',
            plugins_url('/assets/js/script.js', dirname(__FILE__)),
            array('jquery', 'plupload'),
            WPMF_VERSION
        );

        wp_enqueue_script('wpmf-base');

        wp_enqueue_script(
            'wpmf-scrollbar',
            plugins_url('/assets/js/scrollbar/jquery.scrollbar.min.js', dirname(__FILE__)),
            array('jquery'),
            WPMF_VERSION
        );
        wp_enqueue_style(
            'wpmf-scrollbar',
            plugins_url('/assets/js/scrollbar/jquery.scrollbar.css', dirname(__FILE__)),
            array(),
            WPMF_VERSION
        );

        wp_register_script(
            'wpmf-folder-tree',
            plugins_url('/assets/js/folder_tree.js', dirname(__FILE__)),
            array('wpmf-base'),
            WPMF_VERSION
        );
        wp_enqueue_script('wpmf-folder-tree');

        wp_register_script(
            'wpmf-folder-snackbar',
            plugins_url('/assets/js/snackbar.js', dirname(__FILE__)),
            array('wpmf-base'),
            WPMF_VERSION
        );
        wp_enqueue_script('wpmf-folder-snackbar');

        wp_register_script(
            'wpmf-media-filters',
            plugins_url('/assets/js/media-filters.js', dirname(__FILE__)),
            array('jquery', 'plupload'),
            WPMF_VERSION
        );
        wp_enqueue_script('wpmf-media-filters');

        wp_enqueue_script(
            'wpmf-tippy-core',
            plugins_url('/assets/js/tippy/tippy-core.js', dirname(__FILE__)),
            array('jquery'),
            WPMF_VERSION
        );

        wp_enqueue_script(
            'wpmf-tippy',
            plugins_url('/assets/js/tippy/tippy.js', dirname(__FILE__)),
            array('jquery'),
            WPMF_VERSION
        );

        $active_media = get_option('wpmf_active_media');
        if ((!empty($active_media) && (current_user_can('administrator') || current_user_can('editor'))) || empty($active_media)) {
            wp_enqueue_script(
                'wpmf-assign-tree',
                plugins_url('/assets/js/assign_image_folder_tree.js', dirname(__FILE__)),
                array('jquery'),
                WPMF_VERSION
            );
        }

        if (is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php') && $this->user_full_access) {
            wp_enqueue_script(
                'wpmf-import-cloud-tree',
                plugins_url('/assets/js/imports/import_cloud_tree.js', dirname(__FILE__)),
                array('jquery'),
                WPMF_VERSION
            );
        }

        $params = $this->localizeScript();
        wp_localize_script('wpmf-base', 'wpmf', $params);
        wp_enqueue_script('wplink');
        wp_enqueue_style('editor-buttons');

        //replace and duplicate file
        global $pagenow;
        $get_plugin_active   = json_encode(get_option('active_plugins'));
        $option_override  = get_option('wpmf_option_override');
        $option_duplicate  = get_option('wpmf_option_duplicate');
        $params = array(
            'vars' => array(
                'ajaxurl'               => admin_url('admin-ajax.php'),
                'wpmf_nonce'            => wp_create_nonce('wpmf_nonce'),
                'wpmf_pagenow'           => $pagenow,
                'get_plugin_active'     => $get_plugin_active,
                'override'              => (int) $option_override,
                'duplicate'              => (int) $option_duplicate,
            ),
            'l18n' => array(
                'file_uploading'        => __('File upload on the way...', 'wpmf'),
                'replace'               => __('Replace', 'wpmf'),
                'duplicate'             => __('Duplicate', 'wpmf'),
                'filesize_label'        => __('File size:', 'wpmf'),
                'dimensions_label'      => __('Dimensions:', 'wpmf'),
                'wpmf_file_replace'     => __('File replaced!', 'wpmf')
            )
        );

        if (isset($option_override) && (int) $option_override === 1) {
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
            wp_localize_script('replace-image', 'wpmfParams', $params);
        }

        if (isset($option_duplicate) && (int) $option_duplicate === 1) {
            wp_register_script(
                'duplicate-image',
                plugins_url('assets/js/duplicate-image.js', dirname(__FILE__)),
                array('jquery'),
                WPMF_VERSION,
                true
            );
            wp_enqueue_script('duplicate-image');
            wp_localize_script('duplicate-image', 'wpmfParams', $params);
        }
    }

    /**
     * Enqueue styles and scripts for gutenberg
     *
     * @return void
     */
    public function addEditorAssets()
    {
        wp_enqueue_style(
            'wpmf_gallery_blocks',
            WPMF_PLUGIN_URL . 'assets/js/blocks/style.css',
            array(),
            WPMF_VERSION
        );
    }

    /**
     * Load style and script
     *
     * @return void
     */
    public function adminPageTableScript()
    {
        global $pagenow;
        /**
         * Filter check capability of current user when load assets
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'load_script_style');
        if ($wpmf_capability) {
            if ($pagenow === 'upload.php' || $pagenow === 'edit-tags.php') {
                $mode = get_user_option('media_library_mode', get_current_user_id()) ? get_user_option('media_library_mode', get_current_user_id()) : 'grid';
                if ($mode === 'list') {
                    $this->loadAssets();
                }
            } elseif ($pagenow === 'media-new.php') {
                $this->loadAssetsMediaUpload();
                // Add current folder to hidden fields on media-new.php page
                add_filter('upload_post_params', function ($params) {
                    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
                    if (isset($_GET['wpmf-folder'])) {
                        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
                        $params['wpmf_folder'] = (int) $_GET['wpmf-folder'];
                    }
                    return $params;
                }, 1, 1);
            }
        }
    }

    /**
     * Includes styles and some scripts
     *
     * @return void
     */
    public function mediaPageTableScript()
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
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'load_script_style');
        if ($wpmf_capability) {
            $this->loadAssets();
        }
    }

    /**
     * Parse Size
     *
     * @param integer $size Site input
     *
     * @return float
     */
    public function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is
            // the power of magnitude to multiply a kilobyte by.
            return round(floatval($size) * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round(floatval($size));
        }
    }

    /**
     * Localize a script.
     * Works only if the script has already been added.
     *
     * @return array
     */
    public function localizeScript()
    {
        global $pagenow, $wp_roles, $wpdb;
        $option_override  = get_option('wpmf_option_override');
        $option_duplicate = get_option('wpmf_option_duplicate');
        $active_media_access = (int)get_option('wpmf_active_media');
        if ($pagenow === 'upload.php') {
            $categorytype = $this->getFiletype();
            // get count file archive
            $count_zip = $this->countExt('application');
            // get count file pdf
            $count_pdf = $this->countExt('application/pdf');
        } else {
            $categorytype = '';
            $count_zip    = 0;
            $count_pdf    = 0;
        }

        $parse_url = parse_url(site_url());
        $host = md5($parse_url['host']);
        // get some options
        $terms               = $this->getAttachmentTerms();
        $parents_array       = $this->getParrentsArray($terms['attachment_terms']);
        $usegellery          = get_option('wpmf_usegellery');
        $get_plugin_active   = json_encode(get_option('active_plugins'));
        $option_media_remove = get_option('wpmf_option_media_remove');

        $s_dimensions = get_option('wpmf_selected_dimension');
        $size         = json_decode($s_dimensions);
        $s_weights    = get_option('wpmf_weight_selected');
        $weight       = json_decode($s_weights);
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        // get param sort media
        if (isset($_GET['orderby']) && isset($_GET['order'])) {
            $media_order = $_GET['orderby'] . '|' . $_GET['order'];
        } else {
            if (isset($_GET['media-order-media'])) {
                $media_order = $_GET['media-order-media'];
            } else {
                $media_order = 'all';
            }
        }

        if (!isset($_GET['attachment_types'])) {
            if (isset($_COOKIE['wpmf_post_mime_type' . $host]) && $_COOKIE['wpmf_post_mime_type' . $host] !== '' && $_COOKIE['wpmf_post_mime_type' . $host] !== 'all') {
                $attachment_types = $_COOKIE['wpmf_post_mime_type' . $host];
            } else {
                $attachment_types = 'all';
            }
        } else {
            $attachment_types = (isset($_GET['attachment_types'])) ? $_GET['attachment_types'] : (($pagenow === 'upload.php') ? '' : 'all');
        }

        if (strpos($attachment_types, 'uploaded') !== false) {
            $attachment_types = explode(',', $attachment_types);
            $key = array_search('uploaded', $attachment_types);
            unset($attachment_types[$key]);
            $attachment_types = implode(',', $attachment_types);
        }

        if (strpos($attachment_types, 'trash') !== false) {
            $library_mode = get_user_meta(get_current_user_id(), $wpdb->prefix . 'media_library_mode', true);
            if (!empty($library_mode) && $library_mode === 'list') {
                if (empty($_GET['attachment-filter'])) {
                    $attachment_types = 'all';
                } elseif ($_GET['attachment-filter'] === 'trash') {
                    $attachment_types = 'trash';
                }
            }
        }

        if (!isset($_GET['attachment_dates'])) {
            if (isset($_COOKIE['wpmf_wpmf_date' . $host]) && $_COOKIE['wpmf_wpmf_date' . $host] !== '' && $_COOKIE['wpmf_wpmf_date' . $host] !== '0') {
                $attachment_dates = $_COOKIE['wpmf_wpmf_date' . $host];
            } else {
                $attachment_dates = 'all';
            }
        } else {
            $attachment_dates = (isset($_GET['attachment_dates'])) ? $_GET['attachment_dates'] : 'all';
        }

        if (!isset($_GET['attachment_sizes'])) {
            if (isset($_COOKIE['wpmf_wpmf_size' . $host]) && $_COOKIE['wpmf_wpmf_size' . $host] !== '' && $_COOKIE['wpmf_wpmf_size' . $host] !== 'all') {
                $attachment_sizes = $_COOKIE['wpmf_wpmf_size' . $host];
            } else {
                $attachment_sizes = 'all';
            }
        } else {
            $attachment_sizes = (isset($_GET['attachment_sizes'])) ? $_GET['attachment_sizes'] : 'all';
        }

        if (!isset($_GET['attachment_weights'])) {
            if (isset($_COOKIE['wpmf_wpmf_weight' . $host]) && $_COOKIE['wpmf_wpmf_weight' . $host] !== '' && $_COOKIE['wpmf_wpmf_weight' . $host] !== 'all') {
                $attachment_weights = $_COOKIE['wpmf_wpmf_weight' . $host];
            } else {
                $attachment_weights = 'all';
            }
        } else {
            $attachment_weights = (isset($_GET['attachment_weights'])) ? $_GET['attachment_weights'] : 'all';
        }

        // get param sort folder
        $folder_order = (isset($_GET['folder_order'])) ? $_GET['folder_order'] : 'all';
        $wpmf_order_f = isset($_GET['folder_order']) ? $_GET['folder_order'] : '';
        $display_own_media = (isset($_GET['wpmf-display-media-filters']) && $_GET['wpmf-display-media-filters'] === 'yes') ? 'yes' : 'all';
        $display_all_media = (!empty($_GET['wpmf_all_media'])) ? 1 : 0;
        $filter_media_type = (!empty($_GET['attachment-filter'])) ? $_GET['attachment-filter'] : '';
        // phpcs:enable
        $option_countfiles = get_option('wpmf_option_countfiles');
        if (!empty($option_countfiles) && $terms['role'] !== 'administrator') {
            $option_countfiles = 0;
            $show_folder_count = apply_filters('wpmf_show_folder_count', false);
            if ($show_folder_count) {
                $option_countfiles = 1;
            }
        }

        $option_hoverimg   = get_option('wpmf_option_hoverimg');
        $cloud_endpoint = get_option('wpmf_cloud_endpoint');
        if (empty($cloud_endpoint)) {
            $cloud_endpoint = 'aws3';
        }
        $aws3config   = get_option('_wpmfAddon_'. $cloud_endpoint .'_config');
        $aws3_label = (isset($aws3config) && isset($aws3config['attachment_label']) && (int) $aws3config['attachment_label'] === 1) ? 1 : 0;
        $root_media_root   = get_term_by('id', $this->folderRootId, WPMF_TAXO);
        $root_media = (empty($root_media_root)) ? 0 : $root_media_root->term_id;
        $hide_tree         = wpmfGetOption('hide_tree');
        /**
         * Filter to set limit of the folder number loaded
         *
         * @param integer Limit folder number
         *
         * @return integer
         */
        $limit_folders_number = apply_filters('wpmf_limit_folders', 99999);
        $enable_folders = wpmfGetOption('enable_folders');
        $auto_generate_webp = wpmfGetOption('auto_generate_webp');
        $remote_video = wpmfGetOption('hide_remote_video');
        $remote_video = ((int)$remote_video === 1);
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
        $remote_video = apply_filters('wpmf_user_can', $remote_video, 'hide_remote_video');
        $show_folder_id = wpmfGetOption('show_folder_id');
        $enable_download_media = wpmfGetOption('enable_download_media');
        // get colors folder option
        $colors_option = wpmfGetOption('folder_color');

        // get default gallery config
        $gallery_configs = wpmfGetOption('gallery_settings');
        // get cloud sync settings
        $sync_method = wpmfGetOption('sync_method');
        $sync_periodicity = wpmfGetOption('sync_periodicity');
        $cloudNameSyncing = get_option('wpmf_cloud_name_syncing');
        $cloud_endpoint = get_option('wpmf_cloud_endpoint');
        if (empty($cloud_endpoint)) {
            $cloud_endpoint = 'aws3';
        }
        $configs = get_option('_wpmfAddon_'. $cloud_endpoint .'_config');
        $root_media_count = wpmfGetOption('root_media_count');
        $roles = $wp_roles->roles;
        $role_in = array();
        foreach ($roles as $role_name => $r) {
            if (isset($r['capabilities']['upload_files']) && $r['capabilities']['upload_files']) {
                $role_in[] = $role_name;
            }
        }
        unset($roles['administrator']);
        $args = array (
            'role__in' => $role_in,
            'order' => 'ASC',
            'orderby' => 'display_name',
            'fields' => array('ID', 'display_name')
        );

        $wp_user_query = new WP_User_Query($args);
        $users = $wp_user_query->get_results();

        $serverUploadLimit = min(
            10 * 1024 * 1024, // Maximum for chunks size is 10MB if other settings is greater than 10MB
            $this->parseSize(ini_get('upload_max_filesize')),
            $this->parseSize(ini_get('post_max_size'))
        );

        global $current_user; //$current_user->allcaps
        if (!empty($current_user->ID)) {
            $enable_permissions_settings = ((isset($current_user->allcaps['wpmf_enable_permissions_settings']) && $current_user->allcaps['wpmf_enable_permissions_settings']) || in_array('administrator', $current_user->roles));
        }

        $enable_all_files_button = in_array('administrator', $current_user->roles) || !$active_media_access ;
        /**
         * Filter to enable "Display all files" button for specific user roles
         *
         * @return boolean
         */
        $show_all_files_button =    apply_filters('wpmf_enable_all_files_button', $enable_all_files_button) ? 1 : 0;

        $plan_status = get_option('wpmf_ai_plan_status', 'not_paid');
        $wpmf_ai_keys = array(
            'batch_ai_optimization',
            'new_ai_auto_optimization',
            'ai_image_title',
            'ai_image_alt',
            'ai_image_description',
            'ai_image_caption',
            'rename_image_upload',
            'admin_bar'
        );
        $wpmf_ai_options = array('ai_use_url' => self::$useAiFromUrl, 'ai_plan_status' => $plan_status);

        foreach ($wpmf_ai_keys as $key) {
            if ($plan_status === 'not_paid') {
                $wpmf_ai_options[$key] = '0';
            } else {
                $wpmf_ai_options[$key] = get_option('wpmf_ai_' . $key, '0');
            }
        }

        $l18n            = $this->translation();
        $vars            = array(
            'site_url'              => site_url(),
            'host'                  => $host,
            'media_new_url'         => admin_url('media-new.php'),
            'plugin_url_image'      => WPMF_PLUGIN_URL . 'assets/images/',
            'serverUploadLimit'     => $serverUploadLimit,
            'roles'                 => $roles,
            'users'                 => $users,
            'override'              => (int) $option_override,
            'duplicate'             => (int) $option_duplicate,
            'wpmf_file'             => $categorytype,//
            'wpmfcount_zip'         => $count_zip,//
            'wpmfcount_pdf'         => $count_pdf, //
            'wpmf_categories'       => $terms['attachment_terms'],
            'wpmf_categories_order' => $terms['attachment_terms_order'],
            'parents_array'         => $parents_array, //
            'taxo'                  => WPMF_TAXO,
            'wpmf_role'             => $terms['role'],
            'enable_permissions_settings' => $enable_permissions_settings,
            'wpmf_active_media'     => (int) $terms['wpmf_active_media'],
            'access_type'           => $terms['access_type'],
            'term_root_username'    => $terms['term_root_username'],
            'term_root_id'          => $terms['term_root_id'],
            'wpmf_pagenow'          => $terms['wpmf_pagenow'],
            'base'                  => $terms['base'],
            'usegellery'            => (int) $usegellery,
            'get_plugin_active'     => $get_plugin_active,
            'wpmf_post_type'        => $terms['wpmf_post_type'],
            'wpmf_current_userid'   => get_current_user_id(),
            'wpmf_post_mime_type'   => $terms['post_mime_types'],
            'wpmf_type'             => $terms['post_type'],
            'usefilter'             => (int) $terms['useorder'],
            'wpmf_remove_media'     => (int) $option_media_remove,
            'ajaxurl'               => admin_url('admin-ajax.php'),
            'wpmf_size'             => $size,
            'size'                  => $attachment_sizes,
            'attachment_dates'      => $attachment_dates,
            'attachment_types'      => $attachment_types,
            'wpmf_weight'           => $weight,
            'weight'                => $attachment_weights,
            'wpmf_order_media'      => $media_order,
            'folder_order'          => $folder_order,
            'display_own_media'     => $display_own_media,
            'display_all_media'     => $display_all_media,
            'filter_media_type'     => $filter_media_type,
            'wpmf_order_f'          => $wpmf_order_f,
            'option_countfiles'     => (int) $option_countfiles,
            'option_hoverimg'       => (int) $option_hoverimg,
            'aws3_label'            => (int) $aws3_label,
            'root_media_root'       => $root_media,
            'parent'                => $terms['parent'],
            'colors'                => $colors_option,
            'hide_tree'             => $hide_tree,
            'limit_folders_number'  => $limit_folders_number,
            'enable_folders'        => $enable_folders,
            'show_folder_id'        => ((int) $show_folder_id === 1) ? true : false,
            'enable_download_media' => ((int) $enable_download_media === 1) ? true : false,
            'root_media_count' => ((int) $root_media_count === 1) ? true : false,
            'hide_remote_video'     => $remote_video,
            'auto_generate_webp'    => $auto_generate_webp,
            'gallery_configs'       => $gallery_configs,
            'sync_method'           => $sync_method,
            'sync_periodicity'      => (int) $sync_periodicity,
            'cloudNameSyncing'      => $cloudNameSyncing,
            'wpmf_addon_active'     => (is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) ? 1 : 0,
            'wpmf_nonce'            => wp_create_nonce('wpmf_nonce'),
            'img_url' => WPMF_PLUGIN_URL . 'assets/images/',
            'copy_files_to_bucket' => (!empty($configs['copy_files_to_bucket']) && is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) ? 1 : 0,
            'hide_own_media_button' => current_user_can('wpmf_hide_own_media_button') ? 1 : 0,
            'show_all_files_button' => $show_all_files_button
        );

        $vars = array_merge($vars, $wpmf_ai_options);

        return array('l18n' => $l18n, 'vars' => $vars);
    }

    /**
     * Get translation string
     *
     * @return array
     */
    public function translation()
    {
        $l18n = array(
            'upload_text'           => __('Less than a min', 'wpmf'),
            'empty_url'             => __('Please add a video URL', 'wpmf'),
            'empty_thumbnail'       => __('Please add a thumbnail', 'wpmf'),
            'add_video'             => __('Add a video', 'wpmf'),
            'question_quit_video_edit' => __('Are you sure you want to quit the video edition?', 'wpmf'),
            'video_url'             => __('Paste video URL: https://www.youtube.com/watch...', 'wpmf'),
            'or'                    => __('or', 'wpmf'),
            'select_from_library'   => __('Select from Library', 'wpmf'),
            'download_folder'       => __('Downloading folder', 'wpmf'),
            'download_zip'          => __('ZIP Folder', 'wpmf'),
            'download_sub'          => __('ZIP Folder & Subfolder', 'wpmf'),
            'uploaded'              => __('Uploaded:', 'wpmf'),
            'total_size'            => __('Total size:', 'wpmf'),
            'bulk_select'           => __('Bulk select folders', 'wpmf'),
            'add_media'             => __('Add media', 'wpmf'),
            'view_media'            => __('View media', 'wpmf'),
            'move_media'            => __('Move media', 'wpmf'),
            'remove_media'          => __('Remove media', 'wpmf'),
            'update_media'          => __('Update media', 'wpmf'),
            'view_folder'           => __('View folder', 'wpmf'),
            'add_folder'            => __('Add Folder', 'wpmf'),
            'update_folder'         => __('Update folder', 'wpmf'),
            'remove_folder'         => __('Remove folder', 'wpmf'),
            'inherit_folder'        => __('Inherit permissions', 'wpmf'),
            'permissions_list_of'   => __('Permission lists of', 'wpmf'),
            'folder'                => __('folder', 'wpmf'),
            'user'                  => __('User', 'wpmf'),
            'role'                  => __('Role', 'wpmf'),
            'add_role'              => __('Add another role permission', 'wpmf'),
            'add_user'              => __('Add another user permission', 'wpmf'),
            'change_folder'         => __('Move to / Multi folders', 'wpmf'),
            'create_folder'         => __('Add new folder', 'wpmf'),
            'load_more'             => __('Load More', 'wpmf'),
            'refresh'               => __('Refresh', 'wpmf'),
            'new_folder'            => __('New Folder', 'wpmf'),
            'media_folder'          => __('Media Library', 'wpmf'),
            'promt'                 => __('New folder name:', 'wpmf'),
            'edit_file_lb'          => __('Please enter a new name for the item:', 'wpmf'),
            'edit_media'            => __('Edit media', 'wpmf'),
            'title_media'           => __('Title', 'wpmf'),
            'caption_media'         => __('Caption', 'wpmf'),
            'alt_media'             => __('Alternative Text', 'wpmf'),
            'desc_media'            => __('Description', 'wpmf'),
            'new_folder_tree'       => __('NEW FOLDER', 'wpmf'),
            'alert_add'             => __('A folder already exists here with the same name. Please try with another name, thanks :)', 'wpmf'),
            'alert_delete_file'     => __('Are you sure to want to delete this file?', 'wpmf'),
            'update_file_msg'       => __('Update failed. Please try with another name, thanks :)', 'wpmf'),
            'alert_delete'          => __('Are you sure to want to delete this folder?', 'wpmf'),
            'delete_multiple_folder' => __('Are you sure to want to delete %d folder? Note that some folders contain subfolders or files.', 'wpmf'),
            'alert_delete_all'      => __('Are you sure to want to delete this folder? Note that this folder contain subfolders or files.', 'wpmf'),
            'alert_delete1'         => __('This folder contains media and/or subfolders, please delete them before or activate the setting that allows to remove a folder with its media', 'wpmf'),
            'display_own_media'     => __('Display only my own medias', 'wpmf'),
            'create_gallery_folder' => __('Create a gallery from folder', 'wpmf'),
            'home'                  => __('Media Library', 'wpmf'),
            'youarehere'            => __('You are here', 'wpmf'),
            'back'                  => __('Back', 'wpmf'),
            'dragdrop'              => __('Drag and Drop me hover a folder', 'wpmf'),
            'pdf'                   => __('PDF', 'wpmf'),
            'zip'                   => __('Zip & archives', 'wpmf'),
            'other'                 => __('Other', 'wpmf'),
            'link_to'               => __('Link To', 'wpmf'),
            'error_replace'         => __('To replace a media and keep the link to this media working,it must be in the same format, ie. jpg > jpg, zip > zip Thanks!', 'wpmf'),
            'uploaded_to_this'      => __('Uploaded to this ', 'wpmf'),
            'mimetype'              => __('All media items', 'wpmf'),
            'replace'               => __('Replace', 'wpmf'),
            'duplicate_text'        => __('Duplicate', 'wpmf'),
            'wpmf_undo'             => __('Undo.', 'wpmf'),
            'wpmf_undo_remove'      => __('Folder removed.', 'wpmf'),
            'files_moved'           => __('files moved.', 'wpmf'),
            'files_moving'          => __('files moving...', 'wpmf'),
            'wpmf_undo_movefolder'  => __('Moved a folder.', 'wpmf'),
            'wpmf_undo_editfolder'  => __('Folder name updated', 'wpmf'),
            'wpmf_file_replace'     => __('File replaced!', 'wpmf'),
            'file_uploading'        => __('File upload on the way...', 'wpmf'),
            'folder_uploading'        => __('Folder upload on the way...', 'wpmf'),
            'uploading'             => __('Uploading', 'wpmf'),
            'syncing_with_cloud'    => __('Cloud syncing on the way...', 'wpmf'),
            'wpmf_undofilter'       => __('Filter applied', 'wpmf'),
            'wpmf_remove_filter'    => __('Media filters removed', 'wpmf'),
            'cancel'                => __('Cancel', 'wpmf'),
            'create'                => __('Create', 'wpmf'),
            'add'                   => __('Add', 'wpmf'),
            'add_tag'               => __('Add tags', 'wpmf'),
            'create_or_select_tag'  => __('Create or select tags', 'wpmf'),
            'save'                  => __('Save', 'wpmf'),
            'save_close'            => __('Save and close', 'wpmf'),
            'ok'                    => __('OK', 'wpmf'),
            'delete'                => __('Delete', 'wpmf'),
            'remove'                => __('Remove', 'wpmf'),
            'get_url_file'          => __('Get URL', 'wpmf'),
            'download'              => __('Download', 'wpmf'),
            'edit_folder'           => __('Rename', 'wpmf'),
            'proceed'               => __('proceed', 'wpmf'),
            'move_confirm_msg'      => __('The size of your file is larger than 20MB, processing may take time depending on your server configuration', 'wpmf'),
            'copy_folder_id'        => __('Copy folder ID: ', 'wpmf'),
            'copy_folderID_msg'     => __('Folder ID copied to clipboard', 'wpmf'),
            'success_copy_shortcode' => __('Gallery shortcode copied!', 'wpmf'),
            'success_copy'          => __('OK it\'s copied!', 'wpmf'),
            'change_color'          => __('Change color', 'wpmf'),
            'permissions_setting'   => __('Permissions settings', 'wpmf'),
            'change_thumbnail'      => __('Change thumbnail', 'wpmf'),
            'add_image'             => __('Add thumbnail', 'wpmf'),
            'edit_file'             => __('Edit', 'wpmf'),
            'empty_video_thumbnail' => __('Please add a thumbnail for video', 'wpmf'),
            'information'           => __('Information', 'wpmf'),
            'cannot_copy'           => __('Cannot copy text', 'wpmf'),
            'unable_copy'           => __('Unable to copy.', 'wpmf'),
            'clear_filters'         => __('Clear filters and sorting', 'wpmf'),
            'label_filter_order'    => __('Filter or order media', 'wpmf'),
            'label_remove_filter'   => __('Remove all filters', 'wpmf'),
            'wpmf_remove_file'      => __('Media removed', 'wpmf'),
            'wpmf_addfolder'        => __('Folder added', 'wpmf'),
            'media_uploaded'        => __('New file(s) uploaded', 'wpmf'),
            'folder_uploaded'       => __('New folder(s) uploaded', 'wpmf'),
            'wpmf_folder_adding'    => __('Adding folder...', 'wpmf'),
            'wpmf_folder_deleting'  => __('Removing folder...', 'wpmf'),
            'folder_editing'        => __('Editing folder...', 'wpmf'),
            'folder_moving'         => __('Moving folder...', 'wpmf'),
            'folder_moving_text'    => __('Moving folder', 'wpmf'),
            'file_moving_text'      => __('Moving file', 'wpmf'),
            'moving'                => __('Moving', 'wpmf'),
            'files'                 => __('files', 'wpmf'),
            'file'                  => __('file', 'wpmf'),
            'file_moving'           => __('Moving file...', 'wpmf'),
            'file_moved'            => __('File moved', 'wpmf'),
            'video_uploaded'        => __('New video uploaded', 'wpmf'),
            'assign_tree_label'     => __('Media folders selection', 'wpmf'),
            'label_assign_tree'     => __('Select the folders where the media belong', 'wpmf'),
            'label_apply'           => __('Apply', 'wpmf'),
            'folder_selection'      => __('New folder selection applied to media', 'wpmf'),
            'all_size_label'        => __('All sizes', 'wpmf'),
            'all_weight_label'      => __('All weight', 'wpmf'),
            'order_folder_label'    => __('Sort folder', 'wpmf'),
            'order_img_label'       => __('Sort media', 'wpmf'),
            'sort_media'            => __('Default sorting', 'wpmf'),
            'media_type'            => __('Media type', 'wpmf'),
            'date'                  => __('Date', 'wpmf'),
            'lang_size'             => __('Minimum size', 'wpmf'),
            'lang_weight'           => __('Weight range', 'wpmf'),
            'remote_video_lb_box'   => __('Copy Youtube, Vimeo or Dailymotion video URL', 'wpmf'),
            'remote_video'          => __('Add Remote Video', 'wpmf'),
            'upload_folder_label'   => __('Upload folder', 'wpmf'),
            'select_folder_label'   => __('Select folder:', 'wpmf'),
            'filesize_label'        => __('File size:', 'wpmf'),
            'dimensions_label'      => __('Dimensions:', 'wpmf'),
            'no_media_label'        => __('No', 'wpmf'),
            'yes_media_label'       => __('Yes', 'wpmf'),
            'filter_label'            => __('Filtering', 'wpmf'),
            'sort_label'            => __('Sorting', 'wpmf'),
            'order_folder'          => array(
                'name-ASC'  => __('Name (Ascending)', 'wpmf'),
                'name-DESC' => __('Name (Descending)', 'wpmf'),
                'id-ASC'    => __('ID (Ascending)', 'wpmf'),
                'id-DESC'   => __('ID (Descending)', 'wpmf'),
                'custom'    => __('Custom order', 'wpmf'),
            ), // List of available ordering type for folders
            'order_media'           => array(
                'date|asc'      => __('Date (Ascending)', 'wpmf'),
                'date|desc'     => __('Date (Descending)', 'wpmf'),
                'title|asc'     => __('Title (Ascending)', 'wpmf'),
                'title|desc'    => __('Title (Descending)', 'wpmf'),
                'size|asc'      => __('Size (Ascending)', 'wpmf'),
                'size|desc'     => __('Size (Descending)', 'wpmf'),
                'filetype|asc'  => __('File type (Ascending)', 'wpmf'),
                'filetype|desc' => __('File type (Descending)', 'wpmf'),
                'custom'        => __('Custom order', 'wpmf'),
            ), // List of available ordering type for attachements
            'colorlists'            => array(
                '#ac725e' => __('Chocolate ice cream', 'wpmf'),
                '#d06b64' => __('Old brick red', 'wpmf'),
                '#f83a22' => __('Cardinal', 'wpmf'),
                '#fa573c' => __('Wild strawberries', 'wpmf'),
                '#ff7537' => __('Mars orange', 'wpmf'),
                '#ffad46' => __('Yellow cab', 'wpmf'),
                '#42d692' => __('Spearmint', 'wpmf'),
                '#16a765' => __('Vern fern', 'wpmf'),
                '#7bd148' => __('Asparagus', 'wpmf'),
                '#b3dc6c' => __('Slime green', 'wpmf'),
                '#fbe983' => __('Desert sand', 'wpmf'),
                '#fad165' => __('Macaroni', 'wpmf'),
                '#92e1c0' => __('Sea foam', 'wpmf'),
                '#9fe1e7' => __('Pool', 'wpmf'),
                '#9fc6e7' => __('Denim', 'wpmf'),
                '#4986e7' => __('Rainy sky', 'wpmf'),
                '#9a9cff' => __('Blue velvet', 'wpmf'),
                '#b99aff' => __('Purple dino', 'wpmf'),
                '#8f8f8f' => __('Mouse', 'wpmf'),
                '#cabdbf' => __('Mountain grey', 'wpmf'),
                '#cca6ac' => __('Earthworm', 'wpmf'),
                '#f691b2' => __('Bubble gum', 'wpmf'),
                '#cd74e6' => __('Purple rain', 'wpmf'),
                '#a47ae2' => __('Toy eggplant', 'wpmf'),
            ), // colorlists
            'placegolder_color'     => __('Custom color #8f8f8f', 'wpmf'),
            'bgcolorerror'          => __('Change background folder has failed', 'wpmf'),
            'search_folder'         => __('Search folders...', 'wpmf'),
            'copy_url'              => __('Media URL copied!', 'wpmf'),
            'reload_media'          => __('Refresh media library', 'wpmf'),
            'msg_upload_folder'     => __('You are uploading media to folder: ', 'wpmf'),
            'addon_ajax_button'     => __('Use ajax link', 'wpmf'),
            'sync_drive' => __('Run full synchronization', 'wpmf'),
            'move_file_fail' => __('Sorry, Media & Folders can only be moved to the same cloud or media system (Google Drive to Google Drive, WordPress media to WordPress media...)', 'wpmf'),
            'import_cloud' => __('Import to library', 'wpmf'),
            'insert_pdfembed' => __('Insert PDF Embed', 'wpmf'),
            'insert_image_lightbox' => __('Insert Image Lightbox', 'wpmf'),
            'import' => __('Import', 'wpmf'),
            'import_cloud_title' => __('Import this file to media library', 'wpmf'),
            'import_google_photo_title' => __('Import Google Photos to Wordpress', 'wpmf'),
            'import_album_as_new_folder' => __('Import as new folder', 'wpmf'),
            'importing_cloud_file'  => __('Cloud file(s) importing...', 'wpmf'),
            'import_cloud_btn'     => __('Import to media library', 'wpmf'),
            'hover_cloud_syncing' => esc_html__('Cloud syncing on the way', 'wpmf'),
            'selected_photos' => esc_html__('Selected Photos', 'wpmf'),
            'album' => esc_html__('Selected Album', 'wpmf'),
            'import_source' => esc_html__('Import Source', 'wpmf'),
            'importing_goolge_photo_album'  => esc_html__('Photo album importing...', 'wpmf'),
            'importing_goolge_photo'  => esc_html__('Importing Google photos...', 'wpmf'),
            'copy_file_to_s3'  => esc_html__('Copy this file to Storage', 'wpmf'),
            'bulk_copy_files_to_s3'  => esc_html__('Upload files to Storage', 'wpmf'),
            'uploading_files_to_s3'  => esc_html__('File(s) uploading to Storage', 'wpmf'),
            'removing_files_from_local'  => esc_html__('File(s) removing from local', 'wpmf'),
            'display_all_files'  => esc_html__('Display all files', 'wpmf'),
            'queue_sync_alert' => esc_html__('Media will be synchronized in background', 'wpmf'),
            'gallery_image_size' => esc_html__('Gallery image size', 'wpmf'),
            'columns' => esc_html__('Columns', 'wpmf'),
            'lightbox_size' => esc_html__('Lightbox size', 'wpmf'),
            'action_on_click' => esc_html__('Action on click', 'wpmf'),
            'orderby' => esc_html__('Order by', 'wpmf'),
            'custom' => esc_html__('Custom', 'wpmf'),
            'random' => esc_html__('Random', 'wpmf'),
            'title' => esc_html__('Title', 'wpmf'),
            'order' => esc_html__('Order', 'wpmf'),
            'ascending' => esc_html__('Ascending', 'wpmf'),
            'descending' => esc_html__('Descending', 'wpmf'),
            'update_with_new_folder' => esc_html__('Update with new folder content', 'wpmf'),
            'yes' => esc_html__('Yes', 'wpmf'),
            'no' => esc_html__('No', 'wpmf'),
            'autoplay' => esc_html__('Autoplay', 'wpmf'),
            'crop_image' => esc_html__('Crop Image', 'wpmf'),
            'border' => esc_html__('Border', 'wpmf'),
            'border_radius' => esc_html__('Border radius', 'wpmf'),
            'border_radius_desc' => esc_html__('Add rounded corners to the gallery items.', 'wpmf'),
            'border_style' => esc_html__('Border style', 'wpmf'),
            'border_color' => esc_html__('Border color', 'wpmf'),
            'border_width' => esc_html__('Border width', 'wpmf'),
            'margin' => esc_html__('Margin', 'wpmf'),
            'gutter' => esc_html__('Gutter', 'wpmf'),
            'shadow' => esc_html__('Shadow', 'wpmf'),
            'shadow_h' => esc_html__('Shadow H offset', 'wpmf'),
            'shadow_v' => esc_html__('Shadow V offset', 'wpmf'),
            'shadow_blur' => esc_html__('Shadow blur', 'wpmf'),
            'shadow_spread' => esc_html__('Shadow Spread', 'wpmf'),
            'shadow_color' => esc_html__('Shadow Color', 'wpmf'),
            'color_settings' => esc_html__('Color Settings', 'wpmf'),
            'image_settings' => esc_html__('Image Settings', 'wpmf'),
            'caption' => esc_html__('Caption', 'wpmf'),
            'custom_link' => esc_html__('Custom link', 'wpmf'),
            'link_target' => esc_html__('Link target', 'wpmf'),
            'same_window' => esc_html__('Same Window', 'wpmf'),
            'new_window' => esc_html__('New Window', 'wpmf'),
            'default' => esc_html__('Default', 'wpmf'),
            'masonry' => esc_html__('Masonry', 'wpmf'),
            'portfolio' => esc_html__('Portfolio', 'wpmf'),
            'slider' => esc_html__('Slider', 'wpmf'),
            'lightbox' => esc_html__('Lightbox', 'wpmf'),
            'attachment_page' => esc_html__('Attachment Page', 'wpmf'),
            'gallery_settings' => esc_html__('Gallery Settings', 'wpmf'),
            'media_gallery' => esc_html__('WP Media Folder Gallery', 'wpmf'),
            'create_gallery' => esc_html__('CREATE GALLERY', 'wpmf'),
            'media_gallery_desc' => esc_html__('Load images from media folder, from your media library or just upload new images', 'wpmf'),
            'media_download_desc' => esc_html__('WP Media Folder media download. Select a media and transform it into a download button', 'wpmf'),
            'upload' => esc_html__('Upload', 'wpmf'),
            'theme' => esc_html__('Theme', 'wpmf'),
            'select_folder' => esc_html__('Select a folder', 'wpmf'),
            'select_role' => esc_html__('Select a role', 'wpmf'),
            'select_user' => esc_html__('Select a user', 'wpmf'),
            'remove_image' => esc_html__('Remove image', 'wpmf'),
            'folder_no_image' => esc_html__('Ooups, this folder does not have any images...', 'wpmf'),
            'none' => esc_html__('None', 'wpmf'),
            'solid' => esc_html__('Solid', 'wpmf'),
            'dotted' => esc_html__('Dotted', 'wpmf'),
            'dashed' => esc_html__('Dashed', 'wpmf'),
            'double' => esc_html__('Double', 'wpmf'),
            'groove' => esc_html__('Groove', 'wpmf'),
            'ridge' => esc_html__('Ridge', 'wpmf'),
            'inset' => esc_html__('Inset', 'wpmf'),
            'outset' => esc_html__('Outset', 'wpmf'),
            'edit_gallery' => esc_html__('Edit Gallery', 'wpmf'),
            'upload_an_image' => esc_html__('Upload an image', 'wpmf'),
            'mv_local_cloud_msg' => esc_html__('Moving %d files from server to cloud', 'wpmf'),
            'search_no_result' => esc_html__('Sorry, no folder found', 'wpmf'),
            'by_role' => esc_html__('By Role', 'wpmf'),
            'by_user' => esc_html__('By User', 'wpmf'),
            'remove_file_permission_msg' => esc_html__('You do not have permission to delete this file. Refresh folder to see image again', 'wpmf'),
            'remove_file_permission_msg1' => esc_html__('You do not have permission to delete this file', 'wpmf'),
            'update_file_permission_msg' => esc_html__('You do not have permission to update this file', 'wpmf'),
            'select_file_required'  => esc_html__('Please select file to do this action', 'wpmf'),
            'cannot_download'  => esc_html__('This file cannot be downloaded.', 'wpmf'),
            'ai_image_optimization' => __('AI image optimization', 'wpmf'),
            'tooltip_opt_single' => __('To optimize an image, click on it and then click on the "Generate with AI" button.', 'wpmf'),
            'tooltip_opt_bulk' => __('To optimize multiple images, click on the "Bulk select" button.', 'wpmf'),
            'tooltip_select_images' => __('Select the images to optimize', 'wpmf'),
            'generate_with_ai' => __('Generate with AI', 'wpmf'),
            'try_again' => __('Try Again', 'wpmf'),
            'generated' => __('Generated', 'wpmf'),
            'select_at_least_one_image' => __('Please select at least one image.', 'wpmf'),
            'ai_request_sent' => __('Sent AI optimization request for %d image(s).', 'wpmf'),
            'bulk_ai_optimization_in_progress' => __('Bulk AI optimization already in progress', 'wpmf'),
            'analyzing_text' => __('Analyzing...', 'wpmf'),
            'analyzing_with_ai' => __('Analyzing images with AI...', 'wpmf'),
            'no_attachments_found' => __('No attachments found in folder.', 'wpmf'),
            'ai_image_process_error' => __('Error processing file', 'wpmf')
        );

        return $l18n;
    }

    /**
     * Get parrents folder array
     *
     * @param array $attachment_terms All wpmf categories
     *
     * @return array
     */
    public function getParrentsArray($attachment_terms)
    {
        $wcat    = isset($_GET['wcat']) ? $_GET['wcat'] : '0'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        $parents = array();
        $pCat    = (int) $wcat;
        while ((int) $pCat !== 0) {
            $parents[] = $pCat;
            $pCat      = (int) $attachment_terms[$pCat]['parent_id'];
        }

        $parents_array = array_reverse($parents);
        return $parents_array;
    }

    /**
     * Get all params
     *
     * @param string $from Call from
     *
     * @return array
     */
    public function getAttachmentTerms($from = '')
    {
        global $pagenow, $current_user, $current_screen, $sitepress;
        if (isset($current_screen->base)) {
            $base = $current_screen->base;
        } else {
            $base = '';
        }

        $cf_count_files = get_option('wpmf_option_countfiles');
        $enable_count = ((int) $cf_count_files === 1) ? true : false;
        // get categories
        $attachment_terms = array();
        $args = array(
            'hide_empty'                   => false,
            'taxonomy'                     => WPMF_TAXO,
            'pll_get_terms_not_translated' => 1
        );

        /**
         * Filter to custom aguments for get all categories
         *
         * @param array Agument
         *
         * @return array
         */
        $args = apply_filters('wpmf_get_categories_args', $args);
        if ($sitepress) {
            remove_filter('get_terms_args', array($sitepress, 'get_terms_args_filter'));
            $filter_removed = remove_filter('get_term', array($sitepress, 'get_term_adjust_id'));
            remove_filter('terms_clauses', array($sitepress, 'terms_clauses'));
            $terms = get_categories($args);
            add_filter('terms_clauses', array($sitepress, 'terms_clauses'), 10, 4);
            if ($filter_removed) {
                add_filter('get_term', array($sitepress, 'get_term_adjust_id'), 1, 1);
            }
            add_filter('get_terms_args', array($sitepress, 'get_terms_args_filter'), 10, 2);
        } else {
            $terms = get_categories($args);
        }

        if ($from === 'builder') {
            $terms = WpmfHelper::parentSort($terms);
        } else {
            $terms = WpmfHelper::parentSort($terms, $enable_count);
        }

        $attachment_terms_order = array();
        $access_type     = get_option('wpmf_create_folder');
        $wpmf_active_media      = get_option('wpmf_active_media');
        $role                   = WpmfHelper::getRoles(get_current_user_id());
        $term_root_username     = '';
        $parent = 0;
        $root_folder = $this->getFolderInfos(0, $enable_count, true);

        $ancestors = array();
        if ($access_type === 'user' && $current_user->ID) {
            $user_folder = get_term_by('slug', sanitize_title($current_user->data->user_login) . '-wpmf', WPMF_TAXO);
            if (!empty($user_folder)) {
                $ancestors = get_ancestors($user_folder->term_id, WPMF_TAXO, 'taxonomy');
            }
        }

        /* role != administrator or enable option 'Display only media by User/User' */
        if (!$this->user_full_access) {
            // get all childs cloud folder
            $cloud_user_folders = array();
            $cloud_types = array('google_drive', 'dropbox', 'onedrive', 'onedrive_business', 'nextcloud', 'owncloud');
            foreach ($cloud_types as $cloud_type) {
                //isLoadAllChildsCloud
                if (WpmfHelper::isConnected($cloud_type)) {
                    if ($cloud_type === 'google_drive') {
                        $options = get_option('_wpmfAddon_cloud_config');
                    } elseif ($cloud_type === 'dropbox') {
                        $options = get_option('_wpmfAddon_dropbox_config');
                    } elseif ($cloud_type === 'onedrive') {
                        $options = get_option('_wpmfAddon_onedrive_config');
                    } elseif ($cloud_type === 'onedrive_business') {
                        $options = get_option('_wpmfAddon_onedrive_business_config');
                    } elseif ($cloud_type === 'nextcloud') {
                        $options = get_option('_wpmfAddon_nextcloud_config');
                    } elseif ($cloud_type === 'owncloud') {
                        $options = get_option('_wpmfAddon_owncloud_config');
                    }
                    if (!empty($options['access_by']) && $options['access_by'] === 'user') {
                        $slug = $current_user->user_login . '-wpmf-' . $cloud_type;
                    } else {
                        $slug = $role . '-wpmf-role-' . $cloud_type;
                    }
                    $cloud_root_id = get_term_by('slug', $slug, WPMF_TAXO);
                    if ($cloud_root_id) {
                        if (WpmfHelper::isLoadAllChildsCloud($cloud_type)) {
                            if (!empty($cloud_root_id)) {
                                $cloud_folder_childs = get_term_children($cloud_root_id->term_id, WPMF_TAXO);
                                $cloud_user_folders = array_merge($cloud_user_folders, $cloud_folder_childs);
                                $cloud_user_folders[] = $cloud_root_id->term_id;
                            }
                        } else {
                            $cloud_user_folders[] = $cloud_root_id->term_id;
                        }
                    }
                }
            }

            foreach ($terms as $term) {
                // check is load childrent folders for local and cloud
                if (in_array($term->term_id, $ancestors)) {
                    $is_access = true;
                } else {
                    $is_access = WpmfHelper::getAccess($term->term_id, get_current_user_id(), 'view_folder');
                }

                if ($is_access) {
                    $attachment_terms_order[$term->term_id] = $term;
                }
            }
        } else { // role == administrator or disable option 'Display only media by User/User'
            $exclude = $this->getDriveFolderExcludes();
            foreach ($terms as $term) {
                if ((int) $term->term_id === (int) $this->folderRootId) {
                    continue;
                }

                if (in_array($term->name, $exclude) && (int) $term->parent === 0) {
                    continue;
                }

                // Todo: Upon disconnection, remove cloud folder data from the database. This will bypass the unnecessary 'exclude' check, enhancing overall performance
                if (false && defined('WPMFAD_PLUGIN_DIR')) {
                    $type = $this->getTermMeta($term->term_id, 'wpmf_drive_type');
                    if ((in_array('Dropbox', $exclude) && $type === 'dropbox')
                        || (in_array('Google Drive', $exclude) && $type === 'google_drive')
                        || (in_array('Onedrive', $exclude) && $type === 'onedrive')
                        || (in_array('Onedrive Business', $exclude) && $type === 'onedrive_business')
                        || (in_array('Owncloud', $exclude) && $type === 'owncloud')
                        || (in_array('Nextcloud', $exclude) && $type === 'nextcloud')) {
                        continue;
                    }
                }

                $attachment_terms_order[$term->term_id] = $term;
            }
        }
        $attachment_terms = $this->getMultiFolderInfos($attachment_terms_order);
        $attachment_terms_order = array_keys($attachment_terms_order);
        $attachment_terms[0] = $root_folder;
        array_unshift($attachment_terms_order, 0);

        $post_mime_types = get_post_mime_types();
        $useorder        = get_option('wpmf_useorder');

        // get post type
        global $post;
        if (!empty($post) && !empty($post->post_type)) {
            $post_type = $post->post_type;
        } else {
            $post_type = '';
        }

        if (in_array('js_composer/js_composer.php', get_option('active_plugins'))) {
            $wpmf_post_type = 1;
        } else {
            $wpmf_post_type = 0;
        }

        return array(
            'role'                   => $role,
            'wpmf_active_media'      => (int) $wpmf_active_media,
            'access_type'            => $access_type,
            'term_root_username'     => $term_root_username,
            'term_root_id'           => 0,
            'attachment_terms'       => $attachment_terms,
            'attachment_terms_order' => $attachment_terms_order,
            'wpmf_pagenow'           => $pagenow,
            'base'                   => $base,
            'post_mime_types'        => $post_mime_types,
            'useorder'               => (int) $useorder,
            'post_type'              => $post_type,
            'wpmf_post_type'         => $wpmf_post_type,
            'parent'                 => $parent
        );
    }

    /**
     * Get folder infos
     *
     * @param array $terms List folder details
     *
     * @return array
     */
    public function getMultiFolderInfos($terms)
    {
        if (!$terms) {
            return array();
        }
        $term_ids = array_keys($terms);
        $return = array();
        $drive_types = array();

        if (defined('WPMFAD_PLUGIN_DIR')) {
            $drive_types = $this->getDriveType($term_ids);
        }
        $orders   = $this->getTermMeta($term_ids, 'wpmf_order');

        foreach ($terms as $term_id => $term) {
            // get custom order folder
            $details = array(
                'id'            => $term->term_id,
                'label'         => $term->name,
                'lower_label'   => strtolower($term->name),
                'slug'          => $term->slug,
                'parent_id'     => $term->category_parent,
                'depth'         => $term->depth,
                'term_group'    => $term->term_group,
                'order'         => isset($orders[$term_id]) ? $orders[$term_id] : '',
                'drive_type'    => isset($drive_types[$term_id]) ? $drive_types[$term_id] : ''
            );

//            $view_media = WpmfHelper::getAccess($term->term_id, get_current_user_id(), 'view_media');
            if (isset($term->files_count)) {
                $details['files_count'] = $term->files_count;
                $details['count_all'] = $term->count_all;
            }
            $return[$term_id] = $details;
        }
        return $return;
    }

    /**
     * Get folder infos
     *
     * @param object         $term         Folder details
     * @param boolean        $enable_count Enable count
     * @param boolean        $isRoot       Is root folder
     * @param integer        $parent       Parent folder ID
     * @param integer|string $depth        Depth
     *
     * @return array
     */
    public function getFolderInfos($term, $enable_count = false, $isRoot = false, $parent = '', $depth = '')
    {
        if ($isRoot) {
            $details = array(
                'id'        => 0,
                'label'     => __('Media Library', 'wpmf'),
                'lower_label'   => __('Media Library', 'wpmf'),
                'slug'      => '',
                'parent_id' => 0
            );

            if ($enable_count) {
                $details['files_count'] = WpmfHelper::getRootFolderCount($this->folderRootId);
            }
        } else {
            // get custom order folder
            $order   = $this->getOrderFolder($term->term_id);
            $drive_type = '';
            if (defined('WPMFAD_PLUGIN_DIR')) {
                $drive_type = $this->getTermMeta($term->term_id, 'wpmf_drive_root_type');
                if (empty($drive_type)) {
                    $drive_type = $this->getTermMeta($term->term_id, 'wpmf_drive_type');
                }
            }

            if ($depth !== '') {
                if ($depth === 'down') {
                    $depth = (int)$term->depth - 1;
                }
            } else {
                $depth = $term->depth;
            }

            $details = array(
                'id'            => $term->term_id,
                'label'         => $term->name,
                'lower_label'   => strtolower($term->name),
                'slug'          => $term->slug,
                'parent_id'     => ($parent !== '') ? 0 : $term->category_parent,
                'depth'         => ($parent !== '') ? 0 : $depth,
                'term_group'    => $term->term_group,
                'order'         => $order,
                'drive_type'    => !empty($drive_type) ? $drive_type : ''
            );

            $view_media = WpmfHelper::getAccess($term->term_id, get_current_user_id(), 'view_media');
            if (isset($term->files_count)) {
                $details['files_count'] = $term->files_count;
                $details['count_all'] = $term->count_all;
            }
        }

        return $details;
    }

    /**
     * Get `term_id` and `meta_value` of term meta by meta_key `wpmf_drive_root_type` or `wpmf_drive_type` if  `wpmf_drive_root_type` is empty
     *
     * @param integer|array $term_id Term ID
     *
     * @return string|array
     */
    public function getDriveType($term_id)
    {
        global $wpdb;

        if (is_array($term_id)) {
            $results = array();
            $term_id = implode(',', $term_id);
        }
        $rows = $wpdb->get_results($wpdb->prepare(
            'SELECT DISTINCT tm.term_id, COALESCE(
                        (SELECT DISTINCT tm_root_type.meta_value from '. $wpdb->prefix . 'termmeta  AS tm_root_type
                            Where tm.term_id = tm_root_type.term_id AND tm_root_type.meta_key = %s), 
                        (SELECT DISTINCT tm_drive_type.meta_value from '. $wpdb->prefix . 'termmeta  AS tm_drive_type
                            Where tm.term_id = tm_drive_type.term_id AND tm_drive_type.meta_key = %s)
               ) AS meta_value
            FROM '. $wpdb->prefix . 'termmeta AS tm WHERE tm.term_id IN ('.$term_id.')', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            array('wpmf_drive_root_type', 'wpmf_drive_type')
        ));
        foreach ($rows as $row) {
            $results[$row->term_id] = $row->meta_value;
        }
        return $results;
    }
    /**
     * Get term meta value by $wpdb->get_var to reduce memory useage in big termmeta table.
     *
     * @param integer|array $term_id  Term ID
     * @param string        $meta_key Meta key
     *
     * @return string|array
     */
    public function getTermMeta($term_id, $meta_key)
    {
        global $wpdb;

        if (is_array($term_id)) {
            $results = array();
            $term_id = implode(',', $term_id);
            $rows = $wpdb->get_results($wpdb->prepare(
                'SELECT term_id, meta_value FROM ' . $wpdb->prefix . 'termmeta WHERE term_id IN ('.$term_id.') AND meta_key = %s', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                array($meta_key)
            ));
            foreach ($rows as $row) {
                $results[$row->term_id] = $row->meta_value;
            }
            return $results;
        }
        return $wpdb->get_var($wpdb->prepare('SELECT meta_value FROM ' . $wpdb->prefix . 'termmeta WHERE term_id=%s AND meta_key=%s', array($term_id, $meta_key)));
    }

    /**
     * Get custom order folder
     *
     * @param integer $term_id Id of folder
     *
     * @return integer|mixed
     */
    public function getOrderFolder($term_id)
    {
        $order = $this->getTermMeta($term_id, 'wpmf_order');
        if (empty($order)) {
            $order = 0;
        }
        return $order;
    }

    /**
     * Show notice of import size for files
     *
     * @return void
     */
    public function showNoticeImportSize()
    {
        global $wpdb;
        $total         = $wpdb->get_var($wpdb->prepare('SELECT COUNT(posts.ID) as total FROM ' . $wpdb->prefix . 'posts as posts
               WHERE   posts.post_type = %s', array('attachment')));

        if ($total > 5000) {
            wp_enqueue_script(
                'wpmfimport-size-filetype',
                plugins_url('/assets/js/imports/import_size_filetype.js', dirname(__FILE__)),
                array('jquery'),
                WPMF_VERSION
            );

            $vars            = array(
                'ajaxurl'               => admin_url('admin-ajax.php'),
                'wpmf_nonce'            => wp_create_nonce('wpmf_nonce')
            );

            $params = array('l18n' => array(), 'vars' => $vars);
            wp_localize_script('wpmfimport-size-filetype', 'wpmfimport', $params);
            echo '<div class="error" id="wpmf_error">'
                . '<p>'
                . esc_html__('Your website has a large file library (>5000 files).
                 WP Media Folder needs to index all of them to run smoothly.
                  It may take few minutes... keep the window open and keep cool :)', 'wpmf')
                . '<a href="#" class="button button-primary"
                 style="margin: 0 5px;" data-page="0" id="wmpfImportsize">
                 ' . esc_html__('Run synchronization now', 'wpmf') . ' 
                 <span class="spinner" style="display:none"></span></a>'
                . '</p>'
                . '</div>';
        }
    }

    /**
     * Add NextGEN galleries notice
     *
     * @return void
     */
    public function showNoticeImportGallery()
    {
        /**
         * Filter check capability of current user to show notice import gallery
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('manage_options'), 'notice_import_gallery');
        if ($wpmf_capability) {
            echo '<div class="error" id="wpmf_error">'
                . '<p>'
                . esc_html__('You\'ve just installed WP Media Folder,
            to save your time we can import your nextgen gallery into WP Media Folder', 'wpmf')
                . '<a href="#" class="button button-primary"
            style="margin: 0 5px;" id="wmpfImportgallery">
            ' . esc_html__('Sync/Import NextGEN galleries', 'wpmf') . '</a> or
             <a href="#" style="margin: 0 5px;" class="button wmpfNoImportgallery">
             ' . esc_html__('No thanks ', 'wpmf') . '</a>
             <span class="spinner" style="display:none; margin:0; float:none"></span>'
                . '</p>'
                . '</div>';
        }
    }

    /**
     * Show notice of import category
     *
     * @return void
     */
    public function showNotice()
    {
        /**
         * Filter check capability of current user to show notice import category
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('manage_options'), 'notice_import_category');
        if ($wpmf_capability) {
            wp_enqueue_script(
                'wpmfimport-category',
                plugins_url('/assets/js/imports/import_category.js', dirname(__FILE__)),
                array('jquery'),
                WPMF_VERSION
            );

            $vars            = array(
                'ajaxurl'               => admin_url('admin-ajax.php'),
                'wpmf_nonce'            => wp_create_nonce('wpmf_nonce')
            );

            $params = array('l18n' => array(), 'vars' => $vars);
            wp_localize_script('wpmfimport-category', 'wpmfimport', $params);

            echo '<div class="error" id="wpmf_error">'
                . '<p>'
                . esc_html__('Thanks for using WP Media Folder!
                 Save time by transforming post categories into media folders automatically. More info', 'wpmf')
                . '<a href="#" class="button button-primary"
                 style="margin: 0 5px;" id="wmpfImportBtn">
                 ' . esc_html__('Import categories now', 'wpmf') . ' 
                 <span class="spinner" style="display:none"></span></a> or 
                 <a href="#" 
                  style="margin: 0 5px;" class="button wmpfNoImportBtn">
                  ' . esc_html__('No thanks ', 'wpmf') . ' <span class="spinner" style="display:none"></span></a>'
                . '</p>'
                . '</div>';
        }
    }

    /**
     * This function do import wordpress category default
     *
     * @return void
     */
    public static function importCategories()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        set_time_limit(0);
        $taxonomy = 'category';
        $option_import_taxo = get_option('_wpmf_import_notice_flag');
        if (isset($option_import_taxo) && $option_import_taxo === 'yes') {
            die();
        }

        if ($_POST['doit'] === 'true') {
            // get all term taxonomy 'category'
            $terms = get_categories(array(
                'taxonomy'   => $taxonomy,
                'orderby'    => 'name',
                'order'      => 'ASC',
                'hide_empty' => false,
                'child_of'   => 0
            ));

            $termsRel = array('0' => 0);
            // insert wpmf-category term
            foreach ($terms as $term) {
                $inserted = wp_insert_term(
                    $term->name,
                    WPMF_TAXO,
                    array('slug' => wp_unique_term_slug($term->slug, $term))
                );
                if (is_wp_error($inserted)) {
                    wp_send_json($inserted->get_error_message());
                }
                $termsRel[$term->term_id] = array('id' => $inserted['term_id'], 'name' => $term->name);
            }
            // update parent wpmf-category term
            foreach ($terms as $term) {
                wp_update_term($termsRel[$term->term_id]['id'], WPMF_TAXO, array('parent' => $termsRel[$term->parent]['id']));

                /**
                 * Create a folder when importing categories
                 *
                 * @param integer Created folder ID
                 * @param string  Created folder name
                 * @param integer Parent folder ID
                 * @param array   Extra informations
                 *
                 * @ignore Hook already documented
                 */
                do_action('wpmf_create_folder', $termsRel[$term->term_id]['id'], $termsRel[$term->term_id]['name'], $termsRel[$term->parent], array('trigger' => 'import_categories'));
            }

            //update attachments
            $attachments = get_posts(array('posts_per_page' => - 1, 'post_type' => 'attachment'));
            foreach ($attachments as $attachment) {
                $terms      = wp_get_post_terms($attachment->ID, $taxonomy);
                $termsArray = array();
                foreach ($terms as $term) {
                    $termsArray[] = $termsRel[$term->term_id]['id'];
                }
                if (!empty($termsArray)) {
                    wp_set_post_terms($attachment->ID, $termsArray, WPMF_TAXO);

                    /**
                     * Set attachment folder after categories import
                     *
                     * @param integer Attachment ID
                     * @param integer Target folder
                     * @param array   Extra informations
                     *
                     * @ignore Hook already documented
                     */
                    do_action('wpmf_attachment_set_folder', $attachment->ID, $termsArray, array('trigger' => 'import_categories'));
                }
            }
        }

        if ($_POST['doit'] === 'true') {
            update_option('_wpmf_import_notice_flag', 'yes');
        } else {
            update_option('_wpmf_import_notice_flag', 'no');
        }
        die();
    }

    /**
     * Display or retrieve the HTML dropdown list of categories.
     *
     * @return void
     */
    public function addImageCategoryFilter()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        global $pagenow;
        $parse_url = parse_url(site_url());
        $host = md5($parse_url['host']);
        if ($pagenow === 'upload.php') {
            $term_rootId = 0;
            $term_label  = __('Select a folder', 'wpmf');
            if (!$this->user_full_access) {
                $wpmfterm           = $this->termRoot();
                $term_rootId        = $wpmfterm['term_rootId'];
                $term_label         = $wpmfterm['term_label'];
                $this->folderRootId = $term_rootId;
            }

            $root_media_root = get_term_by('id', $this->folderRootId, WPMF_TAXO);
            // get cookie last access folder
            if (isset($_COOKIE['lastAccessFolder_' . $host])) {
                $selected = $_COOKIE['lastAccessFolder_' . $host];
            } else {
                if (isset($_GET['wcat'])) {
                    $selected = $_GET['wcat'];
                } else {
                    if (!$this->user_full_access) {
                        $selected = $term_rootId;
                    } else {
                        $selected = 0;
                    }
                }
            }

            if (!$this->user_full_access) {
                $dropdown_options = array(
                    'exclude'           => $root_media_root->term_id,
                    'show_option_none'  => $term_label,
                    'option_none_value' => $term_rootId,
                    'hide_empty'        => false,
                    'hierarchical'      => true,
                    'orderby'           => 'name',
                    'taxonomy'          => WPMF_TAXO,
                    'class'             => 'wpmf-categories',
                    'name'              => 'wcat',
                    'child_of'          => $root_media_root->term_id,
                    'id'                => 'wpmf-media-category',
                    'selected'          => (int) $selected
                );
            } else {
                $excludes = $this->getDriveFolderExcludesWithIDs();
                $excludes[] = $root_media_root->term_id;
                $dropdown_options = array(
                    'exclude'           => $excludes,
                    'show_option_none'  => __('Select a folder', 'wpmf'),
                    'option_none_value' => 0,
                    'hide_empty'        => false,
                    'hierarchical'      => true,
                    'orderby'           => 'name',
                    'taxonomy'          => WPMF_TAXO,
                    'class'             => 'wpmf-categories',
                    'name'              => 'wcat',
                    'id'                => 'wpmf-media-category',
                    'selected'          => (int) $selected
                );
            }

            wp_dropdown_categories($dropdown_options);
        }
        // phpcs:enable
    }

    /**
     * Query post in media list view
     *
     * @param object $query Params use to query attachment
     *
     * @return object $query
     */
    public function preGetPosts1($query)
    {
        // phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        if (!isset($query->query_vars['post_type']) || $query->query_vars['post_type'] !== 'attachment') {
            return $query;
        }

        if (!empty($query->query_vars['wpmf_gallery'])) {
            return $query;
        }

        global $pagenow, $current_screen;
        $parse_url = parse_url(site_url());
        $host = md5($parse_url['host']);
        if ($pagenow === 'upload.php' && isset($current_screen) && $current_screen->base === 'upload') {
            $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            $redirect    = false;
            if (isset($_GET['s']) && $_GET['s'] === '') {
                $current_url = remove_query_arg('s', $current_url);
                $redirect    = true;
            }
            if ($redirect) {
                wp_redirect($current_url);
                ob_end_flush();
                exit();
            }
            if (isset($_GET['page']) && $_GET['page'] === 'wp-retina-2x') {
                return $query;
            }

            // get last access folder
            $cookie_name = str_replace('.', '_', 'lastAccessFolder_' . $host);
            if (isset($_COOKIE[$cookie_name])) {
                $selected = $_COOKIE[$cookie_name];
            } else {
                if (isset($_GET['wcat']) && (int) $_GET['wcat'] !== 0) {
                    $selected = $_GET['wcat'];
                } else {
                    $selected = 0;
                }
            }

            $all_files = false;
            if ((!empty($_COOKIE['wpmf_all_media' . $host]) && (int) $_COOKIE['wpmf_all_media' . $host] === 1) || !empty($_GET['wpmf_all_media'])) {
                $all_files = true;
            }

            if ((int) $selected === 0 && $all_files) {
                return $query;
            }

            $search_file_include_childrent = wpmfGetOption('search_file_include_childrent');
            $include_childrent = ($all_files || !empty($search_file_include_childrent) && isset($_GET['s']) && $_GET['s'] !== '');
            if (isset($selected) && (int) $selected !== 0) {
                // list view , query post with term_id != 0
                $query->tax_query->queries[]    = array(
                    'taxonomy'         => WPMF_TAXO,
                    'field'            => 'term_id',
                    'terms'            => (int) $selected,
                    'include_children' => $include_childrent
                );
                $query->query_vars['tax_query'] = $query->tax_query->queries;
            } else {
                // grid view , query post with term_id != 0
                $terms = get_categories(array('hide_empty' => false, 'taxonomy' => WPMF_TAXO));
                $cats  = array();
                foreach ($terms as $term) {
                    if (!empty($term->term_id)) {
                        $cats[] = $term->term_id;
                    }
                }

                if (in_array($this->folderRootId, $cats)) {
                    $key = array_search($this->folderRootId, $cats);
                    unset($cats[$key]);
                }
                if (count($terms) !== 1) {
                    if (isset($query->tax_query)) {
                        $ob_in_root = get_objects_in_term($this->folderRootId, WPMF_TAXO);
                        if (count($ob_in_root) > 0) {
                            $args = array(
                                'relation' => 'OR',
                                array(
                                    'taxonomy' => WPMF_TAXO,
                                    'field'    => 'term_id',
                                    'terms'    => $cats,
                                    'operator' => 'NOT IN'
                                ),
                                array(
                                    'taxonomy' => WPMF_TAXO,
                                    'field'    => 'term_id',
                                    'terms'    => $this->folderRootId,
                                    'operator' => 'IN'
                                )
                            );
                        } else {
                            $args = array(
                                array(
                                    'taxonomy' => WPMF_TAXO,
                                    'field'    => 'term_id',
                                    'terms'    => $cats,
                                    'operator' => 'NOT IN'
                                )
                            );
                        }

                        $query->set('tax_query', $args);
                    }
                }
            }

            if (isset($_GET['wpmf-display-media-filters']) && $_GET['wpmf-display-media-filters'] === 'yes') {
                $user_id = get_current_user_id();
                $query->set('author', $user_id);
            } else {
                if ((!empty($_COOKIE['wpmf-display-media-filters' . $host]) && $_COOKIE['wpmf-display-media-filters' . $host] === 'yes')) {
                    $user_id = get_current_user_id();
                    $query->set('author', $user_id);
                }
            }

            // check view media permission
            if (isset($selected)) {
                $is_access = WpmfHelper::getAccess($selected, get_current_user_id(), 'view_media');
                if ($is_access) {
                    return $query;
                }
            }
        }
        // phpcs:enable
        return $query;
    }

    /**
     * Add editor layout to footer
     *
     * @return void
     */
    public function editorFooter()
    {
        global $pagenow;
        if ($pagenow === 'upload.php') {
            if (!class_exists('_WP_Editors', false)) {
                require_once ABSPATH . 'wp-includes/class-wp-editor.php';
                _WP_Editors::wp_link_dialog();
            }
        }
    }

    /**
     * Query post in media gird view and ifame
     *
     * @param object $query Params use to query attachment
     *
     * @return object $query
     */
    public function preGetPosts($query)
    {
        // phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        if (!isset($query->query_vars['post_type']) || $query->query_vars['post_type'] !== 'attachment') {
            return $query;
        }

        if (!empty($query->query_vars['wpmf_gallery'])) {
            return $query;
        }

        $parse_url = parse_url(site_url());
        $host = md5($parse_url['host']);
        $all_files = false;
        if ((!empty($_COOKIE['wpmf_all_media' . $host]) && (int) $_COOKIE['wpmf_all_media' . $host] === 1) || !empty($_REQUEST['query']['wpmf_all_media']) || apply_filters('wp_grant_view_media_permission', false)) {
            $all_files = true;
        }

        if (isset($_REQUEST['query']['wpmf_taxonomy']) && $_REQUEST['query']['term_slug'] === '' && $all_files) {
            return $query;
        }

        $folderSlug = isset($_REQUEST['query']['term_slug']) ? $_REQUEST['query']['term_slug'] : '';
        $search_file_include_childrent = wpmfGetOption('search_file_include_childrent');
        $include_childrent = ($all_files || !empty($search_file_include_childrent) && isset($_REQUEST['query']['s']) && $_REQUEST['query']['s'] !== '');
        if (isset($_REQUEST['query']['orderby']) && $_REQUEST['query']['orderby'] !== 'menu_order ID') {
            $taxonomies = apply_filters('attachment-category', get_object_taxonomies('attachment', 'objects'));
            if (!$taxonomies) {
                return $query;
            }

            foreach ($taxonomies as $taxonomyname => $taxonomy) :
                if ($taxonomyname === WPMF_TAXO) {
                    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
                    if (isset($_REQUEST['query']['wpmf_taxonomy']) && $folderSlug) {
                        $query->set(
                            'tax_query',
                            array(
                                'relation' => 'AND',
                                array(
                                    'taxonomy'         => $taxonomyname,
                                    'field'            => 'slug',
                                    'terms'            => $folderSlug,
                                    'include_children' => $include_childrent
                                )
                            )
                        );
                    } elseif (isset($_REQUEST[$taxonomyname]) && is_numeric($_REQUEST[$taxonomyname])
                        && intval($_REQUEST[$taxonomyname]) !== 0) {
                        $term = get_term_by('id', $_REQUEST[$taxonomyname], $taxonomyname);
                        if (is_object($term)) {
                            set_query_var($taxonomyname, $term->slug);
                        }
                    } elseif (isset($_REQUEST['query']['wpmf_taxonomy']) && $folderSlug === '') {
                        $terms     = get_categories(
                            array(
                                'taxonomy'     => $taxonomyname,
                                'hide_empty'   => false,
                                'hierarchical' => false
                            )
                        );
                        $unsetTags = array();
                        foreach ($terms as $term) {
                            $unsetTags[] = $term->slug;
                        }

                        $root_media_root = get_term_by('id', $this->folderRootId, WPMF_TAXO);
                        if (in_array($root_media_root->slug, $unsetTags)) {
                            $key = array_search($root_media_root->slug, $unsetTags);
                            unset($unsetTags[$key]);
                        }

                        if (count($terms) !== 1) {
                            $ob_in_root = get_objects_in_term($this->folderRootId, WPMF_TAXO);
                            if (count($ob_in_root) > 0) {
                                $query->set(
                                    'tax_query',
                                    array(
                                        'relation' => 'OR',
                                        array(
                                            'taxonomy'         => $taxonomyname,
                                            'field'            => 'slug',
                                            'terms'            => $unsetTags,
                                            'operator'         => 'NOT IN',
                                            'include_children' => false
                                        ),
                                        array(
                                            'taxonomy'         => $taxonomyname,
                                            'field'            => 'slug',
                                            'terms'            => $root_media_root->slug,
                                            'include_children' => false
                                        )
                                    )
                                );
                            } else {
                                $query->set(
                                    'tax_query',
                                    array(
                                        array(
                                            'taxonomy'         => $taxonomyname,
                                            'field'            => 'slug',
                                            'terms'            => $unsetTags,
                                            'operator'         => 'NOT IN',
                                            'include_children' => false
                                        )
                                    )
                                );
                            }
                        }
                    }
                }
            endforeach;
        }

        $access_type = get_option('wpmf_create_folder');
        $id_author          = get_current_user_id();

        if (is_plugin_active('polylang/polylang.php') || is_plugin_active('polylang-pro/polylang.php')) {
            global $polylang;
            if ($polylang->curlang && $polylang->model->is_translated_post_type('attachment')) {
                $polylang_current = $polylang->curlang->slug;
                $query->query_vars['lang'] = $polylang_current;
            }
        }

        if (apply_filters('wp_grant_view_media_permission', false)) {
            return $query;
        }

        if ($this->user_full_access) {
            if (isset($_POST['query']) && isset($_POST['query']['wpmf_display_media'])
                && $_POST['query']['wpmf_display_media'] === 'yes') {
                $query->query_vars['author'] = $id_author;
            }
        } else {
            $all_inroot = wpmfGetOption('all_media_in_user_root');
            if (!empty($_REQUEST['query']['term_id'])) {
                $cloud_type = get_term_meta($_REQUEST['query']['term_id'], 'wpmf_drive_type', true);
                if (!empty($cloud_type)) {
                    $is_load_chids = WpmfHelper::isLoadAllChildsCloud($cloud_type);
                } else {
                    $is_load_chids = $all_inroot;
                }
            } else {
                $is_load_chids = $all_inroot;
            }

            if (!empty($is_load_chids)) {
                return $query;
            }

            $cookie_name = str_replace('.', '_', 'lastAccessFolder_' . $host);
            // check view folder permission
            if (isset($_REQUEST['query']['term_id'])) {
                $term_id = $_REQUEST['query']['term_id'];
            } elseif (isset($_COOKIE[$cookie_name])) {
                $term_id = $_COOKIE[$cookie_name];
            }

            if (isset($term_id)) {
                $is_access = WpmfHelper::getAccess($term_id, get_current_user_id(), 'view_media');
                if ($is_access) {
                    return $query;
                }
            }

            if (empty($is_load_chids)) {
                if ($access_type === 'user') {
                    $query->query_vars['author'] = $id_author;
                } else {
                    $current_role = WpmfHelper::getRoles(get_current_user_id());
                    $user_query   = new WP_User_Query(array('role' => $current_role));
                    $user_lists   = $user_query->get_results();
                    $user_array   = array();

                    foreach ($user_lists as $user) {
                        $user_array[] = $user->data->ID;
                    }

                    $query->query_vars['author__in'] = $user_array;
                }
            }
        }
        // phpcs:enable
        return $query;
    }

    /**
     * Get folder parent root ID
     *
     * @param integer $parent Current ID
     * @param integer $folder Folder ID
     *
     * @return mixed
     */
    public function getFolderParent($parent, $folder)
    {
        if (!$this->user_full_access) {
            $wpmf_active_media      = get_option('wpmf_active_media');
            if ((int)$wpmf_active_media === 1 && empty($folder)) {
                $wpmfterm = $this->termRoot();
                if (!empty($wpmfterm)) {
                    $parent = $wpmfterm['term_rootId'];
                }
            }

            $drive_type = get_term_meta($parent, 'wpmf_drive_type', true);
            if (!empty($drive_type)) {
                $cloud_folder_id = WpmfHelper::getCloudRootFolderID($drive_type);
                if ((int)$cloud_folder_id === (int)$folder) {
                    return false;
                }
            }
        }

        return $parent;
    }

    /**
     * Set file to current folder after upload files
     *
     * @param integer $attachment_id Id of attachment
     *
     * @return void
     */
    public function afterUpload($attachment_id)
    {
        if (!isset($_POST['id_category']) && isset($_POST['wpmf_nonce']) && wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            // Get the parent folder from the post request
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
            if (isset($_POST['wpmf_folder'])) {
                // phpcs:disable WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
                $parent = (int)$_POST['wpmf_folder'];
                $folder = (int)$_POST['wpmf_folder'];
                // phpcs:enable
            } else {
                if ($this->user_full_access) {
                    $parent = 0;
                }
                $folder = 0;
            }

            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
            $relativePath = isset($_POST['relativePath']) ? $_POST['relativePath'] : '';
            if ($relativePath !== '') {
                $relativePath = html_entity_decode($relativePath, ENT_COMPAT, 'UTF-8');
                if ($relativePath) {
                    $fileUploadName = basename($relativePath);
                    $fileRelativePath = str_replace($fileUploadName, '', $relativePath);
                    if ($fileRelativePath) {
                        $newCatID = $this->getCategoryByPath($fileRelativePath, $parent);
                        if ($newCatID) {
                            $parent = $newCatID;
                        } else {
                            $folder_name = basename($fileRelativePath);
                            $parent_path = dirname($fileRelativePath) . '/';
                            // create folder & sub folder
                            $parent_paths = explode('/', $parent_path);
                            $pr = $parent;
                            foreach ($parent_paths as $pp) {
                                if ($pp !== '' && $pp !== '.' && $pp !== '\\') {
                                    $inserted = wp_insert_term($pp, WPMF_TAXO, array('parent' => $pr));
                                    if (is_wp_error($inserted)) {
                                        $pr = $inserted->error_data['term_exists'];
                                    } else {
                                        $pr = $inserted['term_id'];
                                    }
                                }
                            }

                            if ($parent_path === './' || dirname($fileRelativePath) === '/') {
                                $inserted = wp_insert_term($folder_name, WPMF_TAXO, array('parent' => $parent));
                            } else {
                                $parent_id = $this->getCategoryByPath($parent_path, $parent);
                                $inserted = wp_insert_term($folder_name, WPMF_TAXO, array('parent' => $parent_id));
                            }

                            if (!is_wp_error($inserted)) {
                                $parent = $inserted['term_id'];
                            }
                        }
                        if ($parent === 0) {
                            $parent = $this->getFolderParent($parent, $folder);
                        }
                    }
                }
            } else {
                // phpcs:ignore WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
                $parent = $this->getFolderParent($parent, $folder);
            }

            $post_upload = get_post($attachment_id);
            // only set object to term when upload files from media library screen
            if (!empty($post_upload) && !strpos($post_upload->post_content, 'wpmf-nextgen-image')
                && !strpos($post_upload->post_content, '[wpmf-ftp-import]')) {
                if ($parent) {
                    wp_set_object_terms($attachment_id, $parent, WPMF_TAXO, true);

                    /**
                     * Set attachmnent folder after upload
                     *
                     * @param integer Attachment ID
                     * @param integer Target folder
                     * @param array   Extra informations
                     *
                     * @ignore Hook already documented
                     */
                    do_action('wpmf_attachment_set_folder', $attachment_id, $parent, array('trigger' => 'upload'));
                }
            }

            if (!empty($attachment_id)) {
                $this->addSizeFiletype($attachment_id);
                // add custom order position
                if (!metadata_exists('post', $attachment_id, 'wpmf_order')) {
                    add_post_meta($attachment_id, 'wpmf_order', 0);
                }
            }
        }
    }

    /**
     * Get size and file type of a file
     *
     * @param integer $pid Id of attachment
     *
     * @return array
     */
    public function getSizeFiletype($pid)
    {
        $wpmf_size_filetype = array();
        $path_attachment = get_attached_file($pid);
        if (file_exists($path_attachment)) {
            // get size
            $size = filesize($path_attachment);
            // get file type
            $categorytype = wp_check_filetype($path_attachment);
            $ext          = $categorytype['ext'];
        } else {
            $size = 0;
            $ext  = '';
        }
        $wpmf_size_filetype['size'] = $size;
        $wpmf_size_filetype['ext']  = $ext;

        return $wpmf_size_filetype;
    }

    /**
     * Add meta size and file type of a file
     *
     * @param integer $attachment_id Id of attachment
     *
     * @return void
     */
    public function addSizeFiletype($attachment_id)
    {
        $wpmf_size_filetype = $this->getSizeFiletype($attachment_id);
        $size               = $wpmf_size_filetype['size'];
        $ext                = $wpmf_size_filetype['ext'];
        update_post_meta($attachment_id, 'wpmf_size', $size);
        update_post_meta($attachment_id, 'wpmf_filetype', $ext);
    }

    /**
     * Save folder permissions
     *
     * @return void
     */
    public function getFolderPermissions()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            wp_send_json(array('status' => false));
        }

        if (empty($_POST['folder_id'])) {
            wp_send_json(array('status' => false));
        }

        $role_permissions = get_term_meta((int)$_POST['folder_id'], 'wpmf_folder_role_permissions');
        $user_permissions = get_term_meta((int)$_POST['folder_id'], 'wpmf_folder_user_permissions');
        $inherit_folder = get_term_meta((int)$_POST['folder_id'], 'inherit_folder', true);
        if ($inherit_folder === '' && $role_permissions === '' && $user_permissions === '') {
            $inherit_folder = 1;
        }
        wp_send_json(array('status' => true, 'role_permissions' => $role_permissions, 'user_permissions' => $user_permissions, 'inherit_folder' => $inherit_folder));
    }

    /**
     * Save folder permissions
     *
     * @return void
     */
    public function saveFolderPermissions()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            wp_send_json(array('status' => false));
        }

        if (empty($_POST['folder_id'])) {
            wp_send_json(array('status' => false));
        }

        $inherit_folder = isset($_POST['inherit_folder']) ? $_POST['inherit_folder'] : 1;
        update_term_meta((int)$_POST['folder_id'], 'inherit_folder', $inherit_folder);
        $role_permissions = (isset($_POST['role_permissions'])) ? json_decode(stripslashes($_POST['role_permissions']), true) : array();
        delete_term_meta((int)$_POST['folder_id'], 'wpmf_folder_role_permissions');
        foreach ($role_permissions as $role_permission) {
            add_term_meta((int)$_POST['folder_id'], 'wpmf_folder_role_permissions', $role_permission);
        }

        $user_permissions = (isset($_POST['user_permissions'])) ? json_decode(stripslashes($_POST['user_permissions']), true) : array();
        delete_term_meta((int)$_POST['folder_id'], 'wpmf_folder_user_permissions');
        foreach ($user_permissions as $user_permission) {
            add_term_meta((int)$_POST['folder_id'], 'wpmf_folder_user_permissions', $user_permission);
        }

        wp_send_json(array('status' => true));
    }

    /**
     * Upload folder
     *
     * @return void
     */
    public function uploadFolder()
    {
        /**
         * Filter check capability of current user to add a folder
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'add_folder');
        if (!$wpmf_capability) {
            wp_send_json(array('status' => false, 'msg' => esc_html__('You not have a permission to create folder!', 'wpmf')));
        }

        $upload_dir = wp_upload_dir();
        $file_dir = $upload_dir['path'] . '/';
        // phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        $id_category = isset($_POST['id_category']) ? $_POST['id_category'] : 0;
        $resumableIdentifier = isset($_POST['resumableIdentifier']) ? $_POST['resumableIdentifier'] : '';
        $resumableIdentifier  = html_entity_decode($resumableIdentifier, ENT_COMPAT, 'UTF-8');
        if (!empty($resumableIdentifier)) {
            $id_category = current(explode('|||', $resumableIdentifier));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $resumableIdentifier = isset($_GET['resumableIdentifier']) ? $_GET['resumableIdentifier'] : null;
            $resumableFilename = isset($_GET['resumableFilename']) ? $_GET['resumableFilename'] : null;
            $resumableChunkNumber = isset($_GET['resumableChunkNumber']) ? (int)$_GET['resumableChunkNumber'] : null;
            $temp_dir = $file_dir . md5($resumableIdentifier);
            $filename = md5($resumableFilename);
            $chunk_file = $temp_dir . '/' . $filename . '.part' . $resumableChunkNumber;

            if (file_exists($chunk_file)) {
                header('HTTP/1.0 200');
            } else {
                // File's chunk not yet uploaded. Upload it!
                header('HTTP/1.0 204');
            }
        }

        if (!empty($_FILES)) {
            foreach ($_FILES as $file_upload) {
                // check the error status
                if ((int)$file_upload['error'] !== 0) {
                    header('HTTP/1.0 400 Bad Request');
                    continue;
                }
                // init the destination file (format <filename.ext>.part<#chunk>
                // the file is stored in a temporary directory
                $resumableIdentifier = isset($_POST['resumableIdentifier']) ? $_POST['resumableIdentifier'] : '';
                $resumableFilename = isset($_POST['resumableFilename']) ? $_POST['resumableFilename'] : null;

                $resumableIdentifier = html_entity_decode($resumableIdentifier, ENT_COMPAT, 'UTF-8');
                $resumableFilename = html_entity_decode($resumableFilename, ENT_COMPAT, 'UTF-8');
                $resumableChunkNumber = isset($_POST['resumableChunkNumber']) ? (int)$_POST['resumableChunkNumber'] : null;
                $resumableTotalSize = isset($_POST['resumableTotalSize']) ? $_POST['resumableTotalSize'] : null;
                $resumableTotalChunks = isset($_POST['resumableTotalChunks']) ? $_POST['resumableTotalChunks'] : null;
                $temp_dir = $file_dir . md5($resumableIdentifier);
                $filename = md5($resumableFilename);
                $dest_file = $temp_dir . '/' . $filename . '.part' . $resumableChunkNumber;
                // create the temporary directory
                if (!is_dir($temp_dir)) {
                    mkdir($temp_dir, 0777, true);
                }

                $newname = $file_upload['name'];
                // move the temporary file
                if (!move_uploaded_file($file_upload['tmp_name'], $dest_file)) {
                    wp_send_json(array('status' => false, 'msg' => esc_html__('Cannot move uploaded file', 'wpmf') . ' ' . $file_upload['name']));
                } else {
                    // check if all the parts present, and create the final destination file
                    $joinFiles = $this->createFileFromChunks(
                        $temp_dir,
                        $file_dir,
                        $filename,
                        $newname,
                        $resumableTotalSize,
                        $resumableTotalChunks
                    );
                    if ($joinFiles === false) {
                        wp_send_json(array('status' => false, 'msg' => 'Error saving file ' . $file_upload['name']));
                    } elseif ($joinFiles === true) {
                        // correct file category by relatetive path
                        $resumableRelativePath = isset($_POST['resumableRelativePath']) ? $_POST['resumableRelativePath'] : null;
                        // phpcs:enable
                        $resumableRelativePath = html_entity_decode($resumableRelativePath, ENT_COMPAT, 'UTF-8');
                        if ($resumableRelativePath) {
                            $fileUploadName = basename($resumableRelativePath);
                            $fileRelativePath = str_replace($fileUploadName, '', $resumableRelativePath);
                            if ($fileRelativePath) {
                                $newCatID = $this->getCategoryByPath($fileRelativePath, $id_category);
                                if ($newCatID) {
                                    $id_category = $newCatID;
                                } else {
                                    $folder_name = basename($fileRelativePath);
                                    $parent_path = dirname($fileRelativePath) . '/';

                                    // create folder & sub folder
                                    $parent_paths = explode('/', $parent_path);
                                    $pr = $id_category;
                                    foreach ($parent_paths as $pp) {
                                        if ($pp !== '' && $pp !== '.') {
                                            $inserted = wp_insert_term($pp, WPMF_TAXO, array('parent' => $pr));
                                            /**
                                             * Create a folder from media library
                                             *
                                             * @param integer Created folder ID
                                             * @param string  Created folder name
                                             * @param integer Parent folder ID
                                             * @param array   Extra informations
                                             *
                                             * @ignore Hook already documented
                                             */
                                            if (is_wp_error($inserted)) {
                                                $pr = $inserted->error_data['term_exists'];
                                            } else {
                                                do_action('wpmf_create_folder', $inserted['term_id'], $pp, $pr, array('trigger' => 'media_library_action'));
                                                $pr = $inserted['term_id'];
                                            }
                                        }
                                    }

                                    if ($parent_path === './' || dirname($fileRelativePath) === '/') {
                                        $inserted = wp_insert_term($folder_name, WPMF_TAXO, array('parent' => $id_category));
                                        /**
                                         * Create a folder from media library
                                         *
                                         * @param integer Created folder ID
                                         * @param string  Created folder name
                                         * @param integer Parent folder ID
                                         * @param array   Extra informations
                                         *
                                         * @ignore Hook already documented
                                         */
                                        do_action('wpmf_create_folder', $inserted['term_id'], $folder_name, $id_category, array('trigger' => 'media_library_action'));
                                    } else {
                                        $parent_id = $this->getCategoryByPath($parent_path, $id_category);
                                        $inserted = wp_insert_term($folder_name, WPMF_TAXO, array('parent' => $parent_id));
                                        /**
                                         * Create a folder from media library
                                         *
                                         * @param integer Created folder ID
                                         * @param string  Created folder name
                                         * @param integer Parent folder ID
                                         * @param array   Extra informations
                                         *
                                         * @ignore Hook already documented
                                         */
                                        do_action('wpmf_create_folder', $inserted['term_id'], $folder_name, $parent_id, array('trigger' => 'media_library_action'));
                                    }

                                    if (!is_wp_error($inserted)) {
                                        $id_category = $inserted['term_id'];
                                    }
                                }
                            }
                        }

                        //Insert new image into database when success
                        $file_ext = pathinfo($resumableFilename, PATHINFO_EXTENSION);
                        $file_title = str_replace('.' . $file_ext, '', $resumableFilename);
                        $file_title = str_replace('|', '/', $file_title);

                        $mimes     = get_allowed_mime_types();
                        $info_file     = wp_check_filetype_and_ext($file_dir . $newname, $newname, $mimes);
                        $ext             = empty($info_file['ext']) ? '' : $info_file['ext'];
                        $type            = empty($info_file['type']) ? '' : $info_file['type'];
                        if ((!$type || !$ext) && !current_user_can('unfiltered_upload')) {
                            wp_send_json(array('status' => false, 'msg' => esc_html__('Sorry, you are not allowed to upload this file type.', 'wpmf') . ' ' . $file_upload['name']));
                        }

                        $attachment = array(
                            'guid' => $upload_dir['url'] . '/' . $newname,
                            'post_mime_type' => $type,
                            'post_title' => $file_title,
                            'post_status' => 'inherit'
                        );

                        $id_file = wp_insert_attachment($attachment, $file_dir . $newname);
                        if (!$id_file) {
                            unlink($file_dir . $newname);
                            wp_send_json(array('status' => false, 'msg' => esc_html__('Can\'t save to database', 'wpmf')));
                        }

                        // check generate metadata
                        $is_generate_metadata = false;
                        $attachment = get_post($id_file);
                        $mime_type = get_post_mime_type($attachment);
                        if (preg_match('!^image/!', $mime_type) && file_is_displayable_image($file_dir . $newname)) {
                            $is_generate_metadata = true;
                        } elseif (wp_attachment_is('video', $attachment)) {
                            $is_generate_metadata = true;
                        } elseif (wp_attachment_is('audio', $attachment)) {
                            $is_generate_metadata = true;
                        }

                        if ($is_generate_metadata) {
                            $attach_data = wp_generate_attachment_metadata($id_file, $file_dir . $newname);
                            wp_update_attachment_metadata($id_file, $attach_data);
                        }
                        
                        //check is cloud
                        $id_category_new = $id_category;
                        $parent = $this->getFolderParent($id_category, $id_category);
                        $cloud_folder_type = wpmfGetCloudFolderType($parent);

                        if ($cloud_folder_type !== 'local') {
                            $id_category = 0;
                        }

                        do_action('wpmf_add_attachment', $id_file, $id_category);
                        // set attachment to term
                        wp_set_object_terms((int)$id_file, (int)$id_category_new, WPMF_TAXO, false);

                        if ($cloud_folder_type !== 'local') {
                            // check current folder
                            $current_category = 0;
                            // compability with WPML plugin
                            WpmfHelper::moveFileWpml($id_file, $current_category, $parent);
                            wp_remove_object_terms((int) $id_file, $current_category, WPMF_TAXO);
                            $params = array('trigger' => 'move_attachment');
                            $params['local_to_cloud'] = 1;

                            do_action('wpmf_attachment_set_folder', $id_file, (int)$parent, $params);
                        }


                        wp_send_json(array('status' => true, 'id_file' => $id_file, 'name' => $newname, 'id_category' => $id_category));
                    }
                    wp_send_json(array('status' => true));
                }
            }
        }
        wp_send_json(array('status' => false));
    }

    /**
     * Reload folder tree
     *
     * @return void
     */
    public function reloadFolderTree()
    {
        $terms = $this->getAttachmentTerms();
        // Send back all needed informations in json format
        wp_send_json(array(
            'status'           => true,
            'categories'       => $terms['attachment_terms'],
            'categories_order' => $terms['attachment_terms_order']
        ));
    }

    /**
     * Get category by path
     *
     * @param string         $path           The path
     * @param string|integer $rootCategoryId The root category id
     *
     * @return array|false Deepest category id
     */
    public function getCategoryByPath($path, $rootCategoryId = null)
    {
        if (empty($path)) {
            return false;
        }
        $terms = array();
        $termExists = true;
        if (!empty($path)) {
            // Remove last slash or filename
            $path = untrailingslashit($path);
            $filePathArr = explode('/', $path);
            if (!empty($filePathArr)) {
                $parentTermId = $rootCategoryId;
                while ($termExists && !empty($filePathArr)) {
                    $categoryName = array_shift($filePathArr);
                    if (empty($categoryName)) {
                        continue;
                    }
                    $term = term_exists($categoryName, WPMF_TAXO, $parentTermId);

                    if (is_array($term) && !empty($term['term_id'])) {
                        $parentTermId = $term['term_id'];
                        $terms[] = (int) $term['term_id'];
                    } elseif ($term) {
                        $parentTermId = (int)$term;
                        $terms[] = (int) $term;
                    } else {
                        $termExists = false;
                        $terms[] = false;
                    }
                }
            }
        }

        return array_pop($terms);
    }

    /**
     * Check if all the parts exist, and
     * gather all the parts of the file together
     *
     * @param string  $temp_dir        The temporary directory holding all the parts of the file
     * @param string  $destination_dir The directory to save joined file
     * @param string  $fileName        The original file name
     * @param string  $newName         The new unique file name
     * @param string  $totalSize       Original file size (in bytes)
     * @param integer $total_files     Total files
     *
     * @return   boolean true   If success
     *                 false    If got error while joining
     *                 null     If not uploaded all chunk yet
     * @internal param string $chunkSize Each chunk size (in bytes)
     */
    public function createFileFromChunks($temp_dir, $destination_dir, $fileName, $newName, $totalSize, $total_files)
    {
        // count all the parts of this file
        $total_files_on_server_size = 0;
        $temp_total                 = 0;
        foreach (scandir($temp_dir) as $file) {
            $temp_total                 = $total_files_on_server_size;
            $tempfilesize               = filesize($temp_dir . '/' . $file);
            $total_files_on_server_size = $temp_total + $tempfilesize;
        }
        // check that all the parts are present
        // If the Size of all the chunks on the server is equal to the size of the file uploaded.
        if ($total_files_on_server_size >= $totalSize) {
            if (mkdir($temp_dir .'/lock', 0700)) {
                // create the final destination file
                $file = fopen($destination_dir . '/' . $newName, 'w');
                if ($file !== false) {
                    for ($i = 1; $i <= $total_files; $i++) {
                        fwrite($file, file_get_contents($temp_dir . '/' . $fileName . '.part' . $i));
                    }
                    fclose($file);
                } else {
                    return false;
                }
                // rename the temporary directory (to avoid access from other
                // concurrent chunks uploads) and than delete it
                if (rename($temp_dir, $temp_dir . '_UNUSED')) {
                    $this->rrmdir($temp_dir . '_UNUSED');
                } else {
                    $this->rrmdir($temp_dir);
                }

                return true;
            }
        }

        return null;
    }

    /**
     * Delete a directory RECURSIVELY
     *
     * @param string $dir Directory path
     *
     * @link http://php.net/manual/en/function.rmdir.php
     *
     * @return void;
     */
    public function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (filetype($dir . '/' . $object) === 'dir') {
                        $this->rrmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    /**
     * Add a new folder via ajax
     *
     * @return void
     */
    public function addFolder()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to add a folder
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'add_folder');
        if (!$wpmf_capability) {
            wp_send_json(array('status' => false, 'msg' => esc_html__('You not have a permission to create folder!', 'wpmf')));
        }
        if (isset($_POST['name']) && $_POST['name'] !== '') {
            $term = esc_attr(trim($_POST['name']));
        } else {
            $term = __('New folder', 'wpmf');
        }

        $termParent = (int)$_POST['parent'] | 0;
        $termParent = $this->getFolderParent($termParent, $termParent);
        $user_id  = get_current_user_id();
        $is_access = WpmfHelper::getAccess($termParent, $user_id, 'add_folder');
        if (!$is_access) {
            wp_send_json(array('status' => false, 'msg' => esc_html__('You not have a permission to create folder!', 'wpmf')));
        }
        // insert new term
        $inserted = wp_insert_term($term, WPMF_TAXO, array('parent' => $termParent));
        if (is_wp_error($inserted)) {
            wp_send_json(array('status' => false, 'msg' => $inserted->get_error_message()));
        } else {
            // create physical folder
            $folder_options = get_option('wpmf_queue_options');
            if (isset($folder_options['enable_physical_folders']) && !empty($folder_options['enable_physical_folders'])) {
                $cat_path = get_term_parents_list($inserted['term_id'], WPMF_TAXO, array(
                    'separator' => '/',
                    'link'      => false,
                    'format'    => 'name',
                ));
                $upload_dir = wp_upload_dir();
                $folder_dir = $upload_dir['basedir'] . '/' . trim($cat_path, '/');
                if (!file_exists($folder_dir)) {
                    wp_mkdir_p($folder_dir);
                }
            }

            // update term_group for new term
            $updateted = wp_update_term($inserted['term_id'], WPMF_TAXO, array('term_group' => $user_id));
            $termInfos = get_term($updateted['term_id'], WPMF_TAXO);

            /**
             * Create a folder from media library
             * This hook is also used when syncing and importing files from FTP, creating user and role based folders
             * and importing from Nextgen Gallery
             *
             * @param integer Created folder ID
             * @param string  Created folder name
             * @param integer Parent folder ID
             * @param array   Extra informations
             */
            do_action('wpmf_create_folder', $inserted['term_id'], $term, $termParent, array('trigger' => 'media_library_action'));

            // Retrieve the updated folders hierarchy
            $terms = $this->getAttachmentTerms();
            // Send back all needed informations in json format
            wp_send_json(array(
                'status'           => true,
                'term'             => $termInfos,
                'categories'       => $terms['attachment_terms'],
                'categories_order' => $terms['attachment_terms_order']
            ));
        }
    }

    /**
     * After create folder
     *
     * @param integer $folder_id   Folder ID
     * @param string  $folder_name Folder name
     * @param integer $parent      Folder parent
     * @param array   $params      Params details
     *
     * @return void
     */
    public function afterCreateFolder($folder_id, $folder_name, $parent, $params)
    {
        $user_id  = get_current_user_id();
        // add permission
        $role = WpmfHelper::getRoles($user_id);
        if ($role === 'administrator') {
            $role = 0;
            $user_id = 0;
        }

        add_term_meta((int)$folder_id, 'inherit_folder', 1);
        add_term_meta((int)$folder_id, 'wpmf_folder_role_permissions', array($role, 'add_media', 'move_media', 'view_folder', 'add_folder', 'update_folder', 'remove_folder', 'view_media', 'remove_media', 'update_media'));
        add_term_meta((int)$folder_id, 'wpmf_folder_user_permissions', array($user_id, 'add_media', 'move_media', 'view_folder', 'add_folder', 'update_folder', 'remove_folder', 'view_media', 'remove_media', 'update_media'));
    }

    /**
     * Change a folder name from ajax request
     *
     * @return void
     */
    public function editFolder()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to edit a folder
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'edit_folder');
        if (!$wpmf_capability) {
            wp_send_json(array('status' => false, 'msg' => esc_html__('You not have a permission to edit folder!', 'wpmf')));
        }

        $folder_id = $this->getFolderParent($_POST['id'], $_POST['id']);
        $user_id  = get_current_user_id();
        $is_access = WpmfHelper::getAccess($folder_id, $user_id, 'update_folder');
        if (!$is_access) {
            wp_send_json(array('status' => false, 'msg' => esc_html__('You not have a permission to edit folder!', 'wpmf')));
        }

        $term_name = esc_attr($_POST['name']);
        if (!$term_name) {
            wp_send_json(array('status' => false, 'msg' => esc_html__('Folder names can\'t be empty', 'wpmf')));
        }

        $type = get_term_meta((int)$folder_id, 'wpmf_drive_root_type', true);
        if (!empty($type)) {
            wp_send_json(array('status' => false, 'msg' => esc_html__('Can\'t edit cloud root folder!', 'wpmf')));
        }

        // Retrieve term informations
        $term = get_term((int)$folder_id, WPMF_TAXO);

        // check duplicate name
        $siblings = get_categories(
            array(
                'taxonomy' => WPMF_TAXO,
                'fields'   => 'names',
                'get'      => 'all',
                'parent'   => $term->parent
            )
        );

        if (in_array($term_name, $siblings)) {
            // Another folder with the same name exists
            wp_send_json(array('status' => false, 'msg' => esc_html__('A folder already exists here with the same name. Please try with another name, thanks :)', 'wpmf')));
        }

        $updated_term = wp_update_term((int)$folder_id, WPMF_TAXO, array('name' => $term_name));
        if ($updated_term instanceof WP_Error) {
            wp_send_json(array('status' => false, 'msg' => $updated_term->get_error_messages()));
        } else {
            // Retrieve more information than wp_update_term function returns
            $term_details = get_term($updated_term['term_id'], WPMF_TAXO);

            /**
             * Update folder name
             *
             * @param integer Folder ID
             * @param string  Updated name
             */
            do_action('wpmf_update_folder_name', $folder_id, $term_name);
            wp_send_json(array('status' => true, 'details' => $term_details));
        }
    }

    /**
     * Do remove multiple folders
     *
     * @param string  $folder_list     Folder list id
     * @param boolean $remove_on_cloud Remove file on cloud
     *
     * @return boolean
     */
    public function doRemoveFolders($folder_list, $remove_on_cloud = true)
    {
        $wpmf_list_sync_media = get_option('wpmf_list_sync_media');
        $wpmf_ao_lastRun      = get_option('wpmf_ao_lastRun');
        $colors_option        = wpmfGetOption('folder_color');
        // delete all subfolder and subfile
        $folders = explode(',', $folder_list);
        $sub_folders = array();
        $user_id  = get_current_user_id();
        foreach ($folders as $fid) {
            $childs   = get_term_children((int)$fid, WPMF_TAXO);
            foreach ($childs as $child) {
                $folder_id = $this->getFolderParent($child, $child);
                $is_access = WpmfHelper::getAccess($folder_id, $user_id, 'remove_folder');
                if ($is_access) {
                    $sub_folders[] = (int)$child;
                }
            }
            $folder_id2 = $this->getFolderParent($fid, $fid);
            $is_access = WpmfHelper::getAccess($folder_id2, $user_id, 'remove_folder');
            if ($is_access) {
                $sub_folders[] = $fid;
            }
        }

        $query = new WP_Query(array(
            'posts_per_page' => 15,
            'post_type' => 'attachment',
            'post_status' => 'any',
            'fields' => 'ids',
            'tax_query'      => array(
                array(
                    'taxonomy'         => WPMF_TAXO,
                    'field'            => 'term_id',
                    'terms'            => $folders,
                    'include_children' => true
                )
            )
        ));

        $attachments = $query->get_posts();
        if (empty($attachments)) {
            foreach ($sub_folders as $sub_folder_id) {
                // Retrieve the term before deleting it
                $type = get_term_meta((int) $sub_folder_id, 'wpmf_drive_root_type', true);
                if (empty($type)) {
                    $term = get_term($sub_folder_id, WPMF_TAXO);
                    if ($remove_on_cloud) {
                        do_action('wpmf_before_delete_folder', $term);
                    }
                    wp_delete_term($sub_folder_id, WPMF_TAXO);
                    /**
                     * Delete a folder
                     *
                     * @param WP_Term Folder, this term is not available anymore as it as been deleted
                     */
                    do_action('wpmf_delete_folder', $term);

                    if (isset($wpmf_list_sync_media[$sub_folder_id])) {
                        unset($wpmf_list_sync_media[$sub_folder_id]);
                    }
                    if (isset($wpmf_ao_lastRun[$sub_folder_id])) {
                        unset($wpmf_ao_lastRun[$sub_folder_id]);
                    }
                    if (isset($colors_option[$sub_folder_id])) {
                        unset($colors_option[$sub_folder_id]);
                    }

                    // update option 'wpmf_list_sync_media' , 'wpmf_ao_lastRun'
                    update_option('wpmf_list_sync_media', $wpmf_list_sync_media);
                    update_option('wpmf_ao_lastRun', $wpmf_ao_lastRun);
                    wpmfSetOption('folder_color', $colors_option);
                }
            }
        } else {
            foreach ($attachments as $attachment_id) {
                if (!$remove_on_cloud) {
                    remove_action('pre_delete_attachment', array($this, 'deleteAttachmentCloud'), 11);
                }
                wp_delete_attachment($attachment_id);
            }

            return true;
        }

        return false;
    }

    /**
     * Get children of folder
     *
     * @param integer $folder_id Folder id
     * @param integer $index     Index
     *
     * @return array|object|stdClass|null
     */
    public function getChildrenOfFolder($folder_id, $index = 0)
    {
        global $wpdb;
        $detail = null;
        if ((int)$index === 0 && (int)$folder_id !== 0) {
            $detail = $wpdb->get_results('SELECT name, term_id FROM ' . $wpdb->terms . ' WHERE term_id = ' . (int)$folder_id);
        }
        if ((int)$folder_id === 0) {
            $children = $wpdb->get_results($wpdb->prepare('SELECT t.name, t.term_id FROM ' . $wpdb->terms . ' as t INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt ON tt.term_id = t.term_id WHERE tt.taxonomy="wpmf-category" AND t.term_id != %d AND tt.parent = 0', array((int)$this->folderRootId)));
        } else {
            $children = $wpdb->get_results($wpdb->prepare('SELECT t.name, t.term_id FROM ' . $wpdb->terms . ' as t INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt ON tt.term_id = t.term_id WHERE tt.taxonomy="wpmf-category" AND t.term_id != %d AND tt.parent = %d', array((int)$this->folderRootId, (int)$folder_id)));
        }

        foreach ($children as $k => $v) {
            $children[$k]->children = $this->getChildrenOfFolder($v->term_id, $index + 1);
        }

        if ((int)$index === 0) {
            $return = new \stdClass();
            $return->term_id = ($detail !== null) ? $detail[0]->term_id : 0;
            $return->name = ($detail !== null) ? $detail[0]->name : __('Media Library', 'wpmf');
            $return->children = $children;
            return $return;
        }

        return $children;
    }

    /**
     * Add folder to zip
     *
     * @param object $zip        Zip archive
     * @param array  $children   Children folder
     * @param string $parent_dir Parent directory
     *
     * @return void
     */
    public function addFolderToZip(&$zip, $children, $parent_dir = '')
    {
        foreach ($children as $k => $v) {
            $folder_name = $v->name;
            $folder_id = $v->term_id;
            $attachment_ids = get_objects_in_term($folder_id, WPMF_TAXO);
            $empty_dir = $parent_dir !== '' ? $parent_dir . '/' . $folder_name : $folder_name;
            $zip->addEmptyDir($empty_dir);

            foreach ($attachment_ids as $id) {
                $is_cloud = get_post_meta($id, 'wpmf_awsS3_info', true);
                $is_cloud1 = get_post_meta($id, 'wpmf_drive_id', true);
                if (!empty($is_cloud) || !empty($is_cloud1)) {
                    continue;
                }
                $file = get_attached_file($id);
                if ($file && file_exists($file)) {
                    $zip->addFile($file, $empty_dir . '/' . \basename($file));
                }
            }
            if (\is_array($v->children)) {
                $this->addFolderToZip($zip, $v->children, $empty_dir);
            }
        }
    }

    /**
     * Ajax download folder
     *
     * @return void
     */
    public function downloadFolder()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        try {
            $wp_dir = wp_upload_dir();

            if (!file_exists($wp_dir['basedir'] . '/wpmf/download-folders/')) {
                mkdir($wp_dir['basedir'] . '/wpmf/download-folders/', 0777, true);
            }


            $folder_id = (isset($_POST['folder_id'])) ? intval($_POST['folder_id']) : 0;
            $download_sub = (isset($_POST['download_sub'])) ? intval($_POST['download_sub']) : 0;
            $folder = $this->getChildrenOfFolder($folder_id, 0);
            $zip = new \ZipArchive();
            $zipname = $folder->name . '-' . time() . '.zip';
            $zip->open($wp_dir['basedir'] . DIRECTORY_SEPARATOR . 'wpmf' . DIRECTORY_SEPARATOR . 'download-folders' . DIRECTORY_SEPARATOR . $zipname, \ZipArchive::CREATE);

            // get attachment
            if ($folder_id === 0) {
                $terms = get_categories(array('hide_empty' => false, 'taxonomy' => WPMF_TAXO));
                $cats = array();
                foreach ($terms as $term) {
                    if (!empty($term->term_id)) {
                        $cats[] = $term->term_id;
                    }
                }

                $args = array(
                    'posts_per_page' => -1,
                    'post_type' => 'attachment',
                    'post_status' => 'any',
                    'fields' => 'ids',
                    'wpmf_download_folder' => 1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => WPMF_TAXO,
                            'field' => 'term_id',
                            'terms' => $cats,
                            'include_children' => true,
                            'operator' => 'NOT IN'
                        )
                    ),
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'wpmf_drive_id',
                            'compare' => 'NOT EXISTS'
                        ),
                        array(
                            'key' => 'wpmf_awsS3_info',
                            'compare' => 'NOT EXISTS'
                        )
                    )
                );
            } else {
                $args = array(
                    'posts_per_page' => -1,
                    'post_type' => 'attachment',
                    'post_status' => 'any',
                    'fields' => 'ids',
                    'wpmf_download_folder' => 1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => WPMF_TAXO,
                            'field' => 'term_id',
                            'terms' => $folder_id,
                            'include_children' => false,
                            'operator' => 'IN'
                        )
                    ),
                    'meta_query' => array(
                        'relation' => 'AND',
                        array(
                            'key' => 'wpmf_drive_id',
                            'compare' => 'NOT EXISTS'
                        ),
                        array(
                            'key' => 'wpmf_awsS3_info',
                            'compare' => 'NOT EXISTS'
                        )
                    )
                );
            }

            $query = new WP_Query($args);
            $attachment_ids = $query->get_posts();
            foreach ($attachment_ids as $id) {
                $is_cloud = get_post_meta($id, 'wpmf_awsS3_info', true);
                $is_cloud1 = get_post_meta($id, 'wpmf_drive_id', true);
                if (!empty($is_cloud) || !empty($is_cloud1)) {
                    continue;
                }
                $file = get_attached_file($id);
                if ($file && file_exists($file)) {
                    $zip->addFile($file, \basename($file));
                }
            }

            if ((int)$download_sub === 1) {
                $this->addFolderToZip($zip, $folder->children, '');
            }

            if (count($attachment_ids) === 0) {
                $zip->addEmptyDir('.');
            }
            $zip->close();
            $link = trailingslashit($wp_dir['baseurl'] . DIRECTORY_SEPARATOR . 'wpmf' . DIRECTORY_SEPARATOR . 'download-folders' . DIRECTORY_SEPARATOR) . $zipname;
            if (is_ssl() && substr($link, 0, 7) === 'http://') {
                $link = str_replace('http://', 'https://', $link);
            }
            wp_send_json(array('status' => true, 'link' => $link, 'zipname' => $zipname));
        } catch (\Exception $ex) {
            wp_send_json(array('status' => false));
        }
    }

    /**
     * Delete multiple folders
     *
     * @return void
     */
    public function deleteMultipleFolders()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to delete a folder
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'delete_folder');
        if (!$wpmf_capability) {
            wp_send_json(array('status' => false, 'error' => esc_html__('You don\'t have permission to delete this folder!', 'wpmf')));
        }

        $folder_id = isset($_POST['id']) ? $_POST['id'] : 0;
        if (empty($folder_id)) {
            wp_send_json(array(
                'status'           => true
            ));
        }
        $return = $this->doRemoveFolders($folder_id);
        if ($return) {
            wp_send_json(array(
                'status' => false,
                'msg'    => 'limit'
            ));
        } else {
            // Retrieve the updated folders hierarchy
            $terms = $this->getAttachmentTerms();

            // Send full json response
            wp_send_json(array(
                'status'           => true,
                'categories'       => $terms['attachment_terms'],
                'categories_order' => $terms['attachment_terms_order']
            ));
        }
    }

    /**
     * Delete folder via ajax
     *
     * @return void
     */
    public function deleteFolder()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to delete a folder
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'delete_folder');
        if (!$wpmf_capability) {
            wp_send_json(array('status' => false, 'error' => esc_html__('You don\'t have permission to delete this folder!', 'wpmf')));
        }

        $folder_id = $this->getFolderParent($_POST['id'], $_POST['id']);
        $user_id  = get_current_user_id();
        $is_access = WpmfHelper::getAccess($folder_id, $user_id, 'remove_folder');
        if (!$is_access) {
            wp_send_json(array('status' => false, 'error' => esc_html__('You don\'t have permission to delete this folder!', 'wpmf')));
        }

        $option_media_remove  = get_option('wpmf_option_media_remove');
        $wpmf_list_sync_media = get_option('wpmf_list_sync_media');
        $wpmf_ao_lastRun      = get_option('wpmf_ao_lastRun');
        $colors_option        = wpmfGetOption('folder_color');

        if ((int) $option_media_remove === 1) {
            $return = $this->doRemoveFolders($folder_id);
            if ($return) {
                wp_send_json(array(
                    'status' => false,
                    'msg'    => 'limit'
                ));
            } else {
                // Retrieve the updated folders hierarchy
                $terms = $this->getAttachmentTerms();

                // Send full json response
                wp_send_json(array(
                    'status'           => true,
                    'categories'       => $terms['attachment_terms'],
                    'categories_order' => $terms['attachment_terms_order']
                ));
            }
        } else {
            // delete current folder
            $childs         = get_term_children((int) $_POST['id'], WPMF_TAXO);
            if (is_array($childs) && count($childs) > 0) {
                // Folder not empty
                wp_send_json(array('status' => false, 'error' => esc_html__('This folder contains media and/or subfolders, please delete them before or activate the setting that allows to remove a folder with its media', 'wpmf')));
            }

            // remove element $folder_id in option 'wpmf_list_sync_media' , 'wpmf_ao_lastRun'
            if (isset($wpmf_list_sync_media[(int)$folder_id])) {
                unset($wpmf_list_sync_media[(int)$folder_id]);
            }

            if (isset($wpmf_ao_lastRun[(int)$folder_id])) {
                unset($wpmf_ao_lastRun[(int)$folder_id]);
            }

            if (isset($colors_option[(int)$folder_id])) {
                unset($colors_option[(int)$folder_id]);
            }
            update_option('wpmf_list_sync_media', $wpmf_list_sync_media);
            update_option('wpmf_ao_lastRun', $wpmf_ao_lastRun);
            wpmfSetOption('folder_color', $colors_option);

            // Retrieve the term before deleting it
            $term = get_term((int)$folder_id, WPMF_TAXO);
            /**
             * Before delete a folder
             *
             * @param WP_Term Folder, this term is not available anymore as it as been deleted
             */
            do_action('wpmf_before_delete_folder', $term);
            if (wp_delete_term((int)$folder_id, WPMF_TAXO)) {
                /**
                 * Delete a folder
                 *
                 * @param WP_Term Folder
                 *
                 * @ignore Hook already documented
                 */
                do_action('wpmf_delete_folder', $term);

                // Retrieve the updated folders hierarchy
                $terms = $this->getAttachmentTerms();

                wp_send_json(array(
                    'status'           => true,
                    'categories'       => $terms['attachment_terms'],
                    'categories_order' => $terms['attachment_terms_order']
                ));
            } else {
                wp_send_json(array('status' => false, 'error' => esc_html__('You don\'t have permission to delete this folder!', 'wpmf')));
            }
        }
    }

    /**
     * Move a file via ajax from a category to another
     *
     * @return void
     */
    public function moveFile()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to move the files
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'move_file');
        if (!$wpmf_capability) {
            wp_send_json(array('status' => false, 'msg' => esc_html__('You not have a permission to move the file to this folder!', 'wpmf')));
        }

        $parent = $this->getFolderParent($_POST['id_category'], $_POST['id_category']);
        $user_id  = get_current_user_id();
        $is_access = WpmfHelper::getAccess($parent, $user_id, 'move_media');
        if (empty($_POST['current_category'])) {
            $is_access1 = true;
        } else {
            $is_access1 = WpmfHelper::getAccess($_POST['current_category'], $user_id, 'move_media');
        }
        if (!$is_access || !$is_access1) {
            wp_send_json(array('status' => false, 'msg' => esc_html__('You not have a permission to move the file to this folder!', 'wpmf')));
        }

        $return = true;
        // check current folder
        if ((int) $_POST['current_category'] === 0) {
            $current_category = $this->folderRootId;
        } else {
            $current_category = (int) $_POST['current_category'];
        }

        $is_local_to_cloud = false;
        if (!empty($_POST['ids']) && is_array($_POST['ids'])) {
            foreach (array_unique($_POST['ids']) as $id) {
                $cloud_file_type = wpmfGetCloudFileType($id);
                $cloud_folder_type = wpmfGetCloudFolderType($parent);
                $file_s3_infos = get_post_meta((int) $id, 'wpmf_awsS3_info', true);
                if ($cloud_file_type === 'local' && $cloud_folder_type !== 'local' && empty($file_s3_infos)) {
                    $is_local_to_cloud = true;
                }
                if (($cloud_file_type === $cloud_folder_type) || ($cloud_file_type === 'local' && $cloud_folder_type !== 'local' && empty($file_s3_infos))) {
                    // compability with WPML plugin
                    WpmfHelper::moveFileWpml($id, $current_category, $parent);
                    wp_remove_object_terms((int) $id, $current_category, WPMF_TAXO);
                    if ((int)$parent === 0 || wp_set_object_terms((int) $id, (int)$parent, WPMF_TAXO, true)) {
                        $params = array('trigger' => 'move_attachment');
                        if (($cloud_file_type === 'local' && $cloud_folder_type !== 'local' && empty($file_s3_infos))) {
                            $params['local_to_cloud'] = 1;
                        }

                        /**
                         * Set attachment folder after moving an attachment to a folder in the media manager
                         * This hook is also used when importing attachment to categories, after an attachment upload and
                         * when assigning multiple folder to an attachment
                         *
                         * @param integer       Attachment ID
                         * @param integer|array Target folder or array of target folders
                         * @param array         Extra informations
                         */
                        do_action('wpmf_attachment_set_folder', $id, (int)$parent, $params);

                        // reset order
                        update_post_meta(
                            (int) $id,
                            'wpmf_order',
                            0
                        );
                    } else {
                        $return = false;
                    }
                } else {
                    $return = false;
                }
            }
        }
        if (!isset($_POST['no_return'])) {
            wp_send_json(array('status' => $return, 'is_local_to_cloud' => $is_local_to_cloud));
        }
    }

    /**
     * Move a folder via ajax
     *
     * @return void
     */
    public function moveFolder()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to move a folder
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'move_folder');
        if (!$wpmf_capability) {
            wp_send_json(array('status' => false, 'msg' => esc_html__('You not have a permission to move folder!', 'wpmf')));
        }

        $user_id  = get_current_user_id();
        $is_access = WpmfHelper::getAccess($_POST['id'], $user_id, 'update_folder');
        $parent = $this->getFolderParent($_POST['id_category'], $_POST['id_category']);
        $is_access1 = WpmfHelper::getAccess($parent, $user_id, 'update_folder');
        if (!$is_access || !$is_access1) {
            wp_send_json(array('status' => false, 'msg' => esc_html__('You not have a permission to move folder!', 'wpmf')));
        }

        $cloud_folder_type = wpmfGetCloudFolderType($_POST['id']);
        $cloud_folder_target_type = wpmfGetCloudFolderType($parent);
        if ($cloud_folder_type !== $cloud_folder_target_type) {
            wp_send_json(array('status' => false, 'msg' => esc_html__('Sorry, Media & Folders can only be moved to the same cloud or media system (Google Drive to Google Drive, WordPress media to WordPress media...)', 'wpmf')));
        }

        // Check that the folder we move into is not a child of the folder we're moving
        $wpmf_childs = $this->getFolderChild($_POST['id'], array());
        if (in_array((int)$parent, $wpmf_childs)) {
            wp_send_json(array('status' => false, 'msg' => esc_html__('A folder already exists here with the same name. Please try with another name, thanks :)', 'wpmf')));
        }

        /*
         * Check if there is another folder with the same name
         * in the folder we moving into
         */
        $term     = get_term($parent);
        $siblings = get_categories(
            array(
                'taxonomy' => WPMF_TAXO,
                'fields'   => 'names',
                'get'      => 'all',
                'parent'   => (int)$parent
            )
        );
        if (in_array($term->name, $siblings)) {
            wp_send_json(array('status' => false, 'msg' => 'Error, can\'t move'));
        }

        $r = wp_update_term((int) $_POST['id'], WPMF_TAXO, array('parent' => (int)$parent));
        if ($r instanceof WP_Error) {
            wp_send_json(array('status' => false, 'msg' => 'Error, can\'t move'));
        } else {
            /**
             * Move a folder from media library
             * This hook is also used when role folder option is changed
             *
             * @param integer Folder moved ID
             * @param string  Destination folder ID
             * @param array   Extra informations
             */
            do_action('wpmf_move_folder', $_POST['id'], $parent, array('trigger' => 'media_library_action'));
            // Retrieve the updated folders hierarchy
            $terms = $this->getAttachmentTerms();
            wp_send_json(
                array(
                    'status'           => true,
                    'categories'       => $terms['attachment_terms'],
                    'categories_order' => $terms['attachment_terms_order']
                )
            );
        }
    }

    /**
     * Ajax get term to display folder tree
     * todo : this function use in WPMF addon
     *
     * @return void
     */
    public function getTerms()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to get categories list
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'get_terms');
        if (!$wpmf_capability) {
            wp_send_json(false);
        }

        $dir = '/';
        if (!empty($_POST['dir'])) {
            $dir = $_POST['dir'];
            if ($dir[0] === '/') {
                $dir = '.' . $dir . '/';
            }
        }
        $dir  = str_replace('..', '', $dir);
        $dirs = array();
        $id   = 0;
        if (!empty($_POST['id'])) {
            $id = (int) $_POST['id'];
        }

        // get orderby and order
        if (isset($_COOKIE['wpmf_folder_order'])) {
            $sortbys = explode('-', $_COOKIE['wpmf_folder_order']);
            $orderby = $sortbys[0];
            $order   = $sortbys[1];
        } else {
            $orderby = 'name';
            $order   = 'ASC';
        }

        // Retrieve the terms in a given taxonomy or list of taxonomies.
        $categorys          = get_categories(
            array(
                'taxonomy'   => WPMF_TAXO,
                'orderby'    => $orderby,
                'order'      => $order,
                'parent'     => $id,
                'hide_empty' => false
            )
        );
        $access_type = get_option('wpmf_create_folder');
        $current_role       = WpmfHelper::getRoles(get_current_user_id());
        foreach ($categorys as $category) {
            if ((int) $this->folderRootId === (int) $category->term_id) {
                continue;
            }
            $drive_type = get_term_meta($category->term_id, 'wpmf_drive_root_type', true);
            if (empty($drive_type)) {
                $drive_type = get_term_meta($category->term_id, 'wpmf_drive_type', true);
            }

            if (!empty($drive_type)) {
                continue;
            }

            if (!$this->user_full_access) {
                $child      = get_term_children((int) $category->term_id, WPMF_TAXO);
                $countchild = count($child);
                if ($access_type === 'user') {
                    if ((int) $category->term_group === (int) get_current_user_id()) {
                        $dirs[] = array(
                            'type'        => 'dir',
                            'dir'         => $dir,
                            'file'        => $category->name,
                            'id'          => $category->term_id,
                            'parent_id'   => $category->parent,
                            'count_child' => $countchild,
                            'term_group'  => $category->term_group
                        );
                    }
                } else {
                    $crole = WpmfHelper::getRoles($category->term_group);
                    if ($current_role === $crole) {
                        $dirs[] = array(
                            'type'        => 'dir',
                            'dir'         => $dir,
                            'file'        => $category->name,
                            'id'          => $category->term_id,
                            'parent_id'   => $category->parent,
                            'count_child' => $countchild,
                            'term_group'  => $category->term_group
                        );
                    }
                }
            } else {
                $child      = get_term_children((int) $category->term_id, WPMF_TAXO);
                $countchild = count($child);
                $dirs[]     = array(
                    'type'        => 'dir',
                    'dir'         => $dir,
                    'file'        => $category->name,
                    'id'          => $category->term_id,
                    'parent_id'   => $category->parent,
                    'count_child' => $countchild,
                    'term_group'  => $category->term_group
                );
            }
        }

        if (count($dirs) < 0) {
            wp_send_json('not empty');
        } else {
            wp_send_json($dirs);
        }
    }

    /**
     * Get info root folder
     *
     * @return array
     */
    public function termRoot()
    {
        global $current_user;
        $wpmf_checkbox_tree = get_option('wpmf_checkbox_tree');
        $access_type = get_option('wpmf_create_folder');
        if ($access_type === 'user') {
            if (!empty($wpmf_checkbox_tree)) {
                $current_parrent = get_term($wpmf_checkbox_tree, WPMF_TAXO);
                if (!empty($current_parrent)) {
                    $parent = $wpmf_checkbox_tree;
                } else {
                    $parent = 0;
                }
            } else {
                $parent = 0;
            }
        } else {
            $parent = 0;
        }

        $term_roots = get_categories(array('taxonomy' => WPMF_TAXO, 'parent' => $parent, 'hide_empty' => false));
        $wpmfterm   = array();
        $user_roles = $current_user->roles;
        $role       = array_shift($user_roles);

        if (count($term_roots) > 0) {
            if ($access_type === 'user') {
                foreach ($term_roots as $term) {
                    if ($term->name === $current_user->user_login && (int) $term->term_group === (int) get_current_user_id() && defined('WPMF_HIDE_USER_MEDIA_FOLDER_ROOT')) {
                        $wpmfterm['term_rootId'] = $term->term_id;
                        $wpmfterm['term_label']  = $term->name;
                        $wpmfterm['term_parent'] = $term->parent;
                        $wpmfterm['term_slug']   = $term->slug;
                    } else {
                        $wpmfterm['term_rootId'] = $current_parrent->term_id;
                        $wpmfterm['term_label']  = __('Media Library', 'wpmf');
                        $wpmfterm['term_parent'] = $current_parrent->parent;
                        $wpmfterm['term_slug']   = $current_parrent->slug;
                    }
                }
            } else {
                foreach ($term_roots as $term) {
                    if ($term->name === $role && strpos($term->slug, '-wpmf-role') !== false) {
                        $wpmfterm['term_rootId'] = $term->term_id;
                        $wpmfterm['term_label']  = $term->name;
                        $wpmfterm['term_parent'] = $term->parent;
                        $wpmfterm['term_slug']   = $term->slug;
                    }
                }
            }
        }

        return $wpmfterm;
    }

    /**
     * Get children categories
     *
     * @param integer $id_parent Parent of attachment
     * @param array   $lists     List childrens folder
     *
     * @return array
     */
    public function getFolderChild($id_parent, $lists)
    {
        if (empty($lists)) {
            $lists = array();
        }
        $folder_childs = get_categories(
            array(
                'taxonomy'   => WPMF_TAXO,
                'parent'     => (int) $id_parent,
                'hide_empty' => false
            )
        );
        if (count($folder_childs) > 0) {
            foreach ($folder_childs as $child) {
                $lists[] = $child->term_id;
                $lists   = $this->getFolderChild($child->term_id, $lists);
            }
        }

        return $lists;
    }

    /**
     * Get count file by type
     *
     * @param string $app Mime type of post
     *
     * @return integer|null|string
     */
    public function countExt($app)
    {
        global $wpdb;
        if ($app === 'application/pdf') {
            $count = $wpdb->get_var($wpdb->prepare(
                'SELECT COUNT(ID) FROM ' . $wpdb->prefix . 'posts WHERE post_type = %s AND post_mime_type= %s ',
                array('attachment', 'application/pdf')
            ));
        } else {
            $post_mime_type = array(
                'application/zip',
                'application/rar',
                'application/ace',
                'application/arj',
                'application/bz2',
                'application/cab',
                'application/gzip',
                'application/iso',
                'application/jar',
                'application/lzh',
                'application/tar',
                'application/uue',
                'application/xz',
                'application/z',
                'application/7-zip'
            );
            $post_types     = "'" . implode("', '", $post_mime_type) . "'";
            $count          = $wpdb->get_var($wpdb->prepare(
                'SELECT COUNT(ID) FROM ' . $wpdb->prefix . 'posts WHERE post_type = %s AND post_mime_type IN (%s) ',
                array('attachment', $post_types)
            ));
        }

        return $count;
    }

    /**
     * Get current filter file type
     *
     * @return string
     */
    public function getFiletype()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        if (isset($_GET['attachment-filter'])) {
            if ($_GET['attachment-filter'] === 'wpmf-pdf'
                || $_GET['attachment-filter'] === 'wpmf-other') {
                $categorytype = $_GET['attachment-filter'];
            } else {
                $categorytype = '';
            }
        } else {
            $categorytype = '';
        }
        // phpcs:enable
        return $categorytype;
    }

    /**
     * Get folder tree
     *
     * @return void
     */
    public function getUserMediaTree()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to get categories user media
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'get_user_media');
        if (!$wpmf_capability) {
            wp_send_json(false);
        }

        $dir = '/';
        if (!empty($_POST['dir'])) {
            $dir = $_POST['dir'];
            if ($dir[0] === '/') {
                $dir = '.' . $dir . '/';
            }
        }
        $dir  = str_replace('..', '', $dir);
        $dirs = array();
        $id   = 0;
        if (!empty($_POST['id'])) {
            $id = (int) $_POST['id'];
        }

        // Retrieve the terms in a given taxonomy or list of taxonomies.
        $categories             = get_categories(
            array(
                'taxonomy'   => WPMF_TAXO,
                'orderby'    => 'name',
                'order'      => 'ASC',
                'parent'     => $id,
                'hide_empty' => false
            )
        );
        $access_type     = get_option('wpmf_create_folder');
        $current_role           = WpmfHelper::getRoles(get_current_user_id());
        $user_media_folder_root = get_option('wpmf_checkbox_tree');
        $current_parrent        = get_term((int) $user_media_folder_root, WPMF_TAXO);
        if (empty($current_parrent)) {
            $user_media_folder_root = 0;
        }

        if (empty($user_media_folder_root)) {
            $user_media_folder_root = 0;
        }

        $exclude = $this->getDriveFolderExcludes();
        foreach ($categories as $category) {
            if (in_array($category->name, $exclude) && (int) $category->parent === 0) {
                continue;
            }

            if ((int) $category->term_id === (int) $user_media_folder_root) {
                $checked  = true;
                $pchecked = false;
            } else {
                $checked  = false;
                $pchecked = $this->userMediaCheckChecked($category->term_id, $user_media_folder_root);
            }
            if (!$this->user_full_access) {
                $child      = get_term_children((int) $category->term_id, WPMF_TAXO);
                $countchild = count($child);
                if ($access_type === 'user') {
                    if ((int) $category->term_group === (int) get_current_user_id()) {
                        $dirs[] = array(
                            'type'        => 'dir',
                            'dir'         => $dir,
                            'file'        => $category->name,
                            'id'          => $category->term_id,
                            'parent_id'   => $category->parent,
                            'count_child' => $countchild,
                            'term_group'  => $category->term_group,
                            'checked'     => $checked,
                            'pchecked'    => $pchecked
                        );
                    }
                } else {
                    $role = WpmfHelper::getRoles($category->term_group);
                    if ($current_role === $role) {
                        $dirs[] = array(
                            'type'        => 'dir',
                            'dir'         => $dir,
                            'file'        => $category->name,
                            'id'          => $category->term_id,
                            'parent_id'   => $category->parent,
                            'count_child' => $countchild,
                            'term_group'  => $category->term_group,
                            'checked'     => $checked,
                            'pchecked'    => $pchecked
                        );
                    }
                }
            } else {
                $child      = get_term_children((int) $category->term_id, WPMF_TAXO);
                $countchild = count($child);
                $dirs[]     = array(
                    'type'        => 'dir',
                    'dir'         => $dir,
                    'file'        => $category->name,
                    'id'          => $category->term_id,
                    'parent_id'   => $category->parent,
                    'count_child' => $countchild,
                    'term_group'  => $category->term_group,
                    'checked'     => $checked,
                    'pchecked'    => $pchecked
                );
            }
        }

        if (count($dirs) < 0) {
            wp_send_json(array('status' => false));
        } else {
            wp_send_json(array('dirs' => $dirs, 'user_media_folder_root' => $user_media_folder_root, 'status' => true));
        }
    }

    /**
     * Get assign folder tree
     *
     * @return void
     */
    public function getAssignTree()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to get categories assign media
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'get_assign_media');
        if (!$wpmf_capability) {
            wp_send_json(false);
        }
        global $wpdb;
        $dirs = array();
        $id   = 0;
        if (!empty($_POST['id'])) {
            $id = (int) $_POST['id'];
        }

        if (defined('ICL_SITEPRESS_VERSION') && ICL_SITEPRESS_VERSION) {
            global $sitepress;
            $sitepress->switch_lang('all', true);
        }

        // Retrieve the terms in a given taxonomy or list of taxonomies.
        $categories         = get_categories(
            array(
                'taxonomy'   => WPMF_TAXO,
                'orderby'    => 'name',
                'order'      => 'ASC',
                'parent'     => $id,
                'hide_empty' => false
            )
        );

        $access_type = get_option('wpmf_create_folder');
        $current_role       = WpmfHelper::getRoles(get_current_user_id());
        $term_of_file       = wp_get_object_terms(
            (int) $_POST['attachment_id'],
            WPMF_TAXO,
            array(
                'orderby' => 'name',
                'order'   => 'ASC',
                'fields'  => 'ids'
            )
        );

        // check image in root
        $root_id = get_option('wpmf_folder_root_id', false);
        $root_media_root = get_term_by('id', $root_id, WPMF_TAXO);
        if (empty($term_of_file) || (!empty($term_of_file) && in_array($root_media_root->term_id, $term_of_file))) {
            $root_check = true;
        } else {
            $root_check = false;
        }

        foreach ($categories as $category) {
            $drive_type = get_term_meta($category->term_id, 'wpmf_drive_root_type', true);
            if (empty($drive_type)) {
                $drive_type = get_term_meta($category->term_id, 'wpmf_drive_type', true);
            }

            if (!empty($drive_type)) {
                continue;
            }

            if (in_array($category->term_id, $term_of_file)) {
                $checked  = true;
                $pchecked = false;
            } else {
                $checked  = false;
                $pchecked = $this->checkChecked($category->term_id, (array) $term_of_file);
            }
            if (!$this->user_full_access) {
                $countchild = $wpdb->get_var($wpdb->prepare('SELECT COUNT(term_id) FROM ' . $wpdb->prefix . 'term_taxonomy 
               WHERE parent = %d', array((int) $category->term_id)));
                $is_access = WpmfHelper::getAccess($category->term_id, get_current_user_id(), 'view_folder');
                if ($access_type === 'user') {
                    if ((int) $category->term_group === (int) get_current_user_id() || $is_access) {
                        $dirs[] = array(
                            'id'          => $category->term_id,
                            'name'        => $category->name,
                            'parent_id'   => $category->parent,
                            'count_child' => $countchild,
                            'term_group'  => $category->term_group,
                            'checked'     => $checked,
                            'pchecked'    => $pchecked
                        );
                    }
                } else {
                    $role = WpmfHelper::getRoles($category->term_group);
                    if ($current_role === $role || $is_access) {
                        $dirs[] = array(
                            'id'          => $category->term_id,
                            'name'        => $category->name,
                            'parent_id'   => $category->parent,
                            'count_child' => $countchild,
                            'term_group'  => $category->term_group,
                            'checked'     => $checked,
                            'pchecked'    => $pchecked
                        );
                    }
                }
            } else {
                $countchild = $wpdb->get_var($wpdb->prepare('SELECT COUNT(term_id) FROM ' . $wpdb->prefix . 'term_taxonomy 
               WHERE parent = %d', array((int) $category->term_id)));
                $dirs[]     = array(
                    'id'          => $category->term_id,
                    'name'        => $category->name,
                    'parent_id'   => $category->parent,
                    'count_child' => $countchild,
                    'term_group'  => $category->term_group,
                    'checked'     => $checked,
                    'pchecked'    => $pchecked
                );
            }
        }

        if (count($dirs) < 0) {
            wp_send_json(array('status' => false));
        } else {
            $res = array('dirs' => $dirs, 'root_check' => $root_check, 'status' => true);
            if ((int)$id === 0) {
                $res['folders'] = implode(',', $term_of_file);
            }
            wp_send_json($res);
        }
    }

    /**
     * Get status folder
     *
     * @param integer $term_id      Id of folder
     * @param array   $term_of_file Parent of file
     *
     * @return boolean
     */
    public function checkChecked($term_id, $term_of_file = array())
    {
        $childs   = get_term_children((int) $term_id, WPMF_TAXO);
        $pchecked = false;
        foreach ($childs as $child) {
            if (in_array($child, (array) $term_of_file)) {
                $pchecked = true;
                break;
            } else {
                $pchecked = false;
                $this->checkChecked($child, $term_of_file);
            }
        }
        return $pchecked;
    }

    /**
     * Get status folder
     *
     * @param integer $term_id Id of folder
     * @param integer $termID  Id of folder
     *
     * @return boolean
     */
    public function userMediaCheckChecked($term_id, $termID)
    {
        $childs   = get_term_children((int) $term_id, WPMF_TAXO);
        $pchecked = false;
        foreach ($childs as $child) {
            if ((int) $child === (int) $termID) {
                $pchecked = true;
                break;
            } else {
                $pchecked = false;
                $this->checkChecked($child, $termID);
            }
        }
        return $pchecked;
    }

    /**
     * Set file to multiple folders
     *
     * @return void
     */
    public function setObjectTerm()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to update file to category
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'update_object_term');
        if (!$wpmf_capability) {
            wp_send_json(false);
        }
        if (isset($_POST['attachment_ids']) && $_POST['attachment_ids'] !== '') {
            $attachment_ids = explode(',', $_POST['attachment_ids']);
            $enable_count = get_option('wpmf_option_countfiles');
            $need_update_folders = array();
            foreach ($attachment_ids as $attachment_id) {
                // get list folder need update count
                if ((int) $enable_count === 1) {
                    $folders = get_the_terms($attachment_id, WPMF_TAXO);
                    if (!empty($folders)) {
                        foreach ($folders as $folder) {
                            if (!in_array($folder->term_id, $need_update_folders)) {
                                $need_update_folders[] = $folder->term_id;
                            }
                        }
                    } else {
                        $need_update_folders[] = $this->folderRootId;
                    }
                }

                $cloud_file_type = wpmfGetCloudFileType($attachment_id);
                if ($cloud_file_type !== 'local') {
                    continue;
                }
                // unset file to list folder checked
                $the_terms = wp_get_post_terms($attachment_id, WPMF_TAXO, array('fields' => 'ids'));
                foreach ($the_terms as $id_term) {
                    // compability with WPML plugin
                    WpmfHelper::moveFileWpml($attachment_id, $id_term, 'no');
                    wp_remove_object_terms((int) $attachment_id, (int) $id_term, WPMF_TAXO);
                }

                // set file to list folder checked
                if (isset($_POST['wpmf_term_ids_check'])) {
                    $wpmf_term_ids_check = explode(',', $_POST['wpmf_term_ids_check']);
                    foreach ($wpmf_term_ids_check as $term_id) {
                        if ((int) $enable_count === 1) {
                            // get list folder need update count
                            if (!in_array($term_id, $need_update_folders)) {
                                $need_update_folders[] = $term_id;
                            }
                        }
                        $cloud_file_type = wpmfGetCloudFileType($attachment_id);
                        $cloud_folder_type = wpmfGetCloudFolderType($term_id);
                        if ($cloud_file_type === $cloud_folder_type) {
                            // compability with WPML plugin
                            WpmfHelper::moveFileWpml($attachment_id, 'no', $term_id);
                            wp_set_object_terms((int) $attachment_id, (int) $term_id, WPMF_TAXO, true);
                        }
                    }
                    /**
                     * Assign multiple folders to an attachment
                     *
                     * @param integer Attachment ID
                     * @param array   Target folders
                     * @param array   Extra informations
                     *
                     * @ignore Hook already documented
                     */
                    do_action('wpmf_attachment_set_folder', $attachment_id, $wpmf_term_ids_check, array('trigger' => 'set_multiple_folders'));
                }
            }

            $folders_count = array();
            if ((int) $enable_count === 1) {
                foreach ($need_update_folders as $update_folder) {
                    if ((int) $update_folder === (int) $this->folderRootId) {
                        $count = WpmfHelper::getRootFolderCount($update_folder);
                        $folders_count[] = '0-' . $count;
                    } else {
                        $count = WpmfHelper::getCountFiles($update_folder);
                        $folders_count[] = $update_folder . '-' . $count;
                    }
                }
            }
            wp_send_json(array('status' => true, 'folders_count' => $folders_count));
        } else {
            wp_send_json(array('status' => false));
        }
    }

    /**
     * Update file title to database
     *
     * @param integer $pid Id of attachment
     *
     * @return void
     */
    public function updateFileTitle($pid)
    {
        global $wpdb;
        $options_format_title = wpmfGetOption('wpmf_options_format_title');
        $post                 = get_post($pid);
        if (!empty($post)) {
            $title = $post->post_title;

            /* create array character from settings */
            $character = array();
            if (isset($options_format_title['tilde']) && $options_format_title['tilde']) {
                $character[] = '~';
            }
            if (isset($options_format_title['underscore']) && $options_format_title['underscore']) {
                $character[] = '_';
            }
            if (isset($options_format_title['period']) && $options_format_title['period']) {
                $character[] = '.';
            }
            if (isset($options_format_title['plus']) && $options_format_title['plus']) {
                $character[] = '+';
            }
            if (isset($options_format_title['hyphen']) && $options_format_title['hyphen']) {
                $character[] = '-';
            }

            if (isset($options_format_title['hash']) && $options_format_title['hash']) {
                $character[] = '#';
            }

            if (isset($options_format_title['ampersand']) && $options_format_title['ampersand']) {
                $character[] = '@';
            }

            if (isset($options_format_title['number']) && $options_format_title['number']) {
                for ($i = 0; $i <= 9; $i ++) {
                    $character[] = $i;
                }
            }

            if (isset($options_format_title['square_brackets']) && $options_format_title['square_brackets']) {
                $character[] = '[';
                $character[] = ']';
            }

            if (isset($options_format_title['round_brackets']) && $options_format_title['round_brackets']) {
                $character[] = '(';
                $character[] = ')';
            }

            if (isset($options_format_title['curly_brackets']) && $options_format_title['curly_brackets']) {
                $character[] = '{';
                $character[] = '}';
            }

            if (isset($options_format_title['copyright']) && $options_format_title['copyright']) {
                $character[] = '©';
            }

            /* Replace character to space */
            if (!empty($character)) {
                $title = str_replace($character, ' ', $title);
            }

            $title  = preg_replace('/\s+/', ' ', $title);
            $capita = $options_format_title['capita'];

            /* Capitalize Title. */
            switch ($capita) {
                case 'cap_all':
                    $title = ucwords($title);
                    break;
                case 'all_upper':
                    $title = strtoupper($title);
                    break;
                case 'cap_first':
                    $title = ucfirst(strtolower($title));
                    break;
                case 'all_lower':
                    $title = strtolower($title);
                    break;
                case 'dont_alter':
                    break;
            }

            /**
             * Manipulate file title before saving it into database
             *
             * @param string File title
             *
             * @return string
             */
            $title = apply_filters('wpmf_set_file_title', $title);

            // update _wp_attachment_image_alt
            if (isset($options_format_title['alt']) && $options_format_title['alt']) {
                update_post_meta($pid, '_wp_attachment_image_alt', $title);
            }

            // update post
            $field  = array(
                'post_title' => $title
            );
            $format = array('%s');
            if (isset($options_format_title['description']) && $options_format_title['description']) {
                $field['post_content'] = $title;
                $format[]              = '%s';
            }

            if (isset($options_format_title['caption']) && $options_format_title['caption']) {
                $field['post_excerpt'] = $title;
                $format[]              = '%s';
            }

            $wpdb->update(
                $wpdb->posts,
                $field,
                array('ID' => $pid),
                $format,
                array('%d')
            );
        }
    }

    /**
     * Ajax create remote video
     *
     * @return void
     */
    public function createRemoteVideo()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        $video_url = (isset($_POST['video_url'])) ? $_POST['video_url'] : '';
        $folder_id = (isset($_POST['folder_id'])) ? (int)$_POST['folder_id'] : 0;
        $thumbnail_id = (isset($_POST['thumbnail_id'])) ? (int)$_POST['thumbnail_id'] : 0;
        if (empty($video_url)) {
            wp_send_json(array('status' => false));
        }

        $video_library_id = WpmfHelper::doCreateVideo($video_url, $thumbnail_id, 'video_to_gallery');
        if ($video_library_id) {
            if (!empty($folder_id)) {
                wp_set_object_terms((int)$video_library_id, (int)$folder_id, WPMF_TAXO, true);
            }
            wp_send_json(array('status' => true));
        }
        wp_send_json(array('status' => false));
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
        global $current_user;
        $remote_video = get_post_meta($post->ID, 'wpmf_remote_video_link');
        $iptc = get_post_meta($post->ID, 'wpmf_iptc', true);
        if (!empty($iptc)) {
            $iptcHeaderArray = getIptcHeader();
            $iptchtml = '';
            $iptchtml .= '<div class="wpmf_iptc_wrap">';
            foreach ($iptc as $code => $iptcValue) {
                $iptchtml .= '<span><b>' . $iptcHeaderArray[$code] . ': </b>'. implode(',', $iptcValue) .'</span><br>';
            }
            $iptchtml .= '</div>';
            $form_fields['wpmf_iptc'] = array(
                'label' => __('IPTC Meta', 'wpmf'),
                'input' => 'html',
                'html'  => $iptchtml
            );
        }
        if (!empty($remote_video)) {
            $form_fields['wpmf_remote_video_link'] = array(
                'label' => __('Remote video', 'wpmf'),
                'input' => 'html',
                'html'  => '<input type="text" class="text"
                 id="attachments-' . $post->ID . '-wpmf_remote_video_link"
                  name="attachments[' . $post->ID . '][wpmf_remote_video_link]"
                   value="' . get_post_meta($post->ID, 'wpmf_remote_video_link', true) . '">'
            );
        }

        $form_fields['wpmf_attachment_id'] = array(
            'label' => '',
            'input' => 'html',
            'html'  => '<input type="hidden" class="wpmf_attachment_id"
                   value="' . $post->ID . '">'
        );

        if (!empty($current_user->ID)) {
            $roles = WpmfHelper::getAllRoles($current_user->ID);
            if (in_array('administrator', $roles) || in_array('editor', $roles)) {
                $drive_meta = get_post_meta($post->ID, 'wpmf_drive_id', true);
                if (empty($drive_meta)) {
                    $form_fields['wpmf_media_selection'] = array(
                        'label' => '',
                        'input' => 'html',
                        'html'  => '<div class="wpmfjaoassign_row"><div class="wpmfjaoassign_right"><a class="open-popup-tree"><span class="material-icons-outlined"> snippet_folder </span>'. esc_html__('Media folders selection', 'wpmf') .'</a></div></div>'
                    );
                }
            }
        }

        return $form_fields;
    }

    /**
     * Add video html to editor
     *
     * @param string  $html       HTML markup for a media item sent to the editor.
     * @param integer $id         The first key from the $_POST['send'] data.
     * @param array   $attachment Array of attachment metadata.
     *
     * @return mixed
     */
    public function addRemoteVideo($html, $id, $attachment)
    {
        $remote_video = get_post_meta($id, 'wpmf_remote_video_link');
        if (!empty($remote_video)) {
            $html = $remote_video;
        }
        return $html;
    }

    /**
     * Save attachment fields
     * Based on /wp-admin/includes/media.php
     *
     * @param array $post       An array of post data.
     * @param array $attachment An array of attachment metadata.
     *
     * @return mixed $post
     */
    public function attachmentFieldsToSave($post, $attachment)
    {
        if (isset($attachment['wpmf_remote_video_link'])) {
            $url = $attachment['wpmf_remote_video_link'];
            update_post_meta($post['ID'], 'wpmf_remote_video_link', esc_url_raw($url));
        }

        if (isset($attachment['wpmf_tag'])) {
            $attachment['wpmf_tag'] = strtolower($attachment['wpmf_tag']);
            wp_set_post_terms($post['ID'], explode(',', $attachment['wpmf_tag']), 'wpmf_tag');
        }

        return $post;
    }

    /**
     * Ajax set folder color
     *
     * @return void
     */
    public function setFolderColor()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        $colors_option = wpmfGetOption('folder_color');
        if (isset($_POST['folder_id']) && isset($_POST['color'])) {
            if (empty($colors_option)) {
                $colors_option                      = array();
                $colors_option[$_POST['folder_id']] = $_POST['color'];
            } else {
                $colors_option[$_POST['folder_id']] = $_POST['color'];
            }
            wpmfSetOption('folder_color', $colors_option);
            wp_send_json(array('status' => true));
        }
        wp_send_json(array('status' => false));
    }

    /**
     * Ajax delete file
     *
     * @return void
     */
    public function deleteFile()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        if (isset($_POST['id'])) {
            wp_delete_attachment((int) $_POST['id']);
            wp_send_json(array('status' => true));
        }
        wp_send_json(array('status' => false));
    }

    /**
     * Ajax custom order for file
     *
     * @return void
     */
    public function reorderFile()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        if (isset($_POST['order'])) {
            $orders = (array) json_decode(stripslashes_deep($_POST['order']));
            if (is_array($orders) && !empty($orders)) {
                foreach ($orders as $position => $id) {
                    update_post_meta(
                        (int) $id,
                        'wpmf_order',
                        (int) $position
                    );
                }
            }
        }
    }

    /**
     * Ajax custom order for folder
     *
     * @return void
     */
    public function reorderfolder()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        if (isset($_POST['order'])) {
            $orders = (array) json_decode(stripslashes_deep($_POST['order']));
            if (is_array($orders) && !empty($orders)) {
                foreach ($orders as $position => $id) {
                    update_term_meta(
                        (int) $id,
                        'wpmf_order',
                        (int) $position
                    );
                }
            }
        }
    }

    /**
     * Import custom order
     *
     * @return void
     */
    public function importOrder()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to import order of file
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('manage_options'), 'import_order_file');
        if (!$wpmf_capability) {
            wp_send_json(false);
        }
        global $wpdb;
        $limit       = 50;
        $offset      = (int) $_POST['current_import_page'] * $limit;
        $attachments = $wpdb->get_results($wpdb->prepare('SELECT ID FROM ' . $wpdb->prefix . 'posts as posts
               WHERE   posts.post_type     = %s LIMIT %d OFFSET %d', array('attachment', $limit, $offset)));
        $i           = 0;
        foreach ($attachments as $attachment) {
            if (!get_post_meta($attachment->ID, 'wpmf_order')) {
                update_post_meta($attachment->ID, 'wpmf_order', 0);
            }

            $i ++;
        }
        if ($i >= $limit) {
            wp_send_json(array('status' => false, 'page' => (int) $_POST['current_import_page']));
        } else {
            update_option('_wpmf_import_order_notice_flag', 'yes');
            wp_send_json(array('status' => true));
        }
    }

    /**
     * Ajax update link for attachment
     *
     * @return void
     */
    public function updateLink()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        /**
         * Filter check capability of current user to update link image
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'update_link');
        if (!$wpmf_capability || !isset($_POST['id'])) {
            wp_send_json(false);
        }
        $attachment_id = $_POST['id'];
        update_post_meta($attachment_id, '_wpmf_gallery_custom_image_link', esc_url_raw($_POST['link']));
        update_post_meta($attachment_id, '_gallery_link_target', $_POST['link_target']);
        $link   = get_post_meta($attachment_id, '_wpmf_gallery_custom_image_link');
        $target = get_post_meta($attachment_id, '_gallery_link_target');
        wp_send_json(array('link' => $link, 'target' => $target));
    }

    /**
     * Ajax get count files in a folder
     *
     * @return void
     */
    public function getCountFilesInFolder()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        if (!empty($_POST['term_id'])) {
            $count = WpmfHelper::getCountFiles($_POST['term_id']);
        } else {
            $count = WpmfHelper::getRootFolderCount($this->folderRootId);
        }

        wp_send_json(
            array('status' => true, 'count' => $count)
        );
    }

    /**
     * Get drive folder excludes
     *
     * @return array
     */
    public function getDriveFolderExcludes()
    {
        $exclude = array();
        if (is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) {
            $addon_active = true;
        } else {
            $addon_active = false;
        }

        if (!$addon_active) {
            $exclude = array('Google Drive', 'Dropbox', 'Onedrive', 'Onedrive Business', 'Nextcloud', 'ownCloud');
        } else {
            // hide Drive folder if not coonect
            $odv_config = get_option('_wpmfAddon_onedrive_config');
            $odvbs_config = get_option('_wpmfAddon_onedrive_business_config');
            $dropbox_config = get_option('_wpmfAddon_dropbox_config');
            $google_config = get_option('_wpmfAddon_cloud_config');
            $nextcloud_config = get_option('_wpmfAddon_nextcloud_config');
            $connect_nextcloud = wpmfGetOption('connect_nextcloud');
            $owncloud_config = get_option('_wpmfAddon_owncloud_config');
            $connect_owncloud = wpmfGetOption('connect_owncloud');

            if (empty($odv_config['connected'])) {
                $exclude[] = 'Onedrive';
            }

            if (empty($odvbs_config['connected'])) {
                $exclude[] = 'Onedrive Business';
            }

            if (empty($google_config['connected'])) {
                $exclude[] = 'Google Drive';
            }

            if (empty($dropbox_config['dropboxToken'])) {
                $exclude[] = 'Dropbox';
            }

            if (empty($nextcloud_config['username']) || empty($nextcloud_config['password']) || empty($nextcloud_config['nextcloudurl']) || empty($nextcloud_config['rootfoldername']) || empty($connect_nextcloud)) {
                $exclude[] = 'Nextcloud';
            }

            if (empty($owncloud_config['username']) || empty($owncloud_config['password']) || empty($owncloud_config['owncloudurl']) || empty($owncloud_config['rootfoldername']) || empty($connect_owncloud)) {
                $exclude[] = 'ownCloud';
            }
        }

        return $exclude;
    }

    /**
     * Get drive folder excludes with IDs
     *
     * @return array
     */
    public function getDriveFolderExcludesWithIDs()
    {
        $exclude = array();
        if (is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) {
            $addon_active = true;
        } else {
            $addon_active = false;
        }

        if (!$addon_active) {
            $id = get_option('wpmf_odv_folder_id', true);
            if (!empty($id)) {
                $exclude[] = $id;
                $childs   = get_term_children((int) $id, WPMF_TAXO);
                $exclude = array_merge($exclude, $childs);
            }

            $id = get_option('wpmf_odv_business_folder_id', true);
            if (!empty($id)) {
                $exclude[] = $id;
                $childs   = get_term_children((int) $id, WPMF_TAXO);
                $exclude = array_merge($exclude, $childs);
            }

            $id = get_option('wpmf_google_folder_id', true);
            if (!empty($id)) {
                $exclude[] = $id;
                $childs   = get_term_children((int) $id, WPMF_TAXO);
                $exclude = array_merge($exclude, $childs);
            }

            $id = get_option('wpmf_dropbox_folder_id', true);
            if (!empty($id)) {
                $exclude[] = $id;
                $childs   = get_term_children((int) $id, WPMF_TAXO);
                $exclude = array_merge($exclude, $childs);
            }
        } else {
            // hide Drive folder if not coonect
            $odv_config = get_option('_wpmfAddon_onedrive_config');
            $odvbs_config = get_option('_wpmfAddon_onedrive_business_config');
            $dropbox_config = get_option('_wpmfAddon_dropbox_config');
            $google_config = get_option('_wpmfAddon_cloud_config');

            if (empty($odv_config['connected'])) {
                $id = get_option('wpmf_odv_folder_id', true);
                if (!empty($id)) {
                    $exclude[] = $id;
                    $childs   = get_term_children((int) $id, WPMF_TAXO);
                    $exclude = array_merge($exclude, $childs);
                }
            }

            if (empty($odvbs_config['connected'])) {
                $id = get_option('wpmf_odv_business_folder_id', true);
                if (!empty($id)) {
                    $exclude[] = $id;
                    $childs   = get_term_children((int) $id, WPMF_TAXO);
                    $exclude = array_merge($exclude, $childs);
                }
            }

            if (empty($google_config['connected'])) {
                $id = get_option('wpmf_google_folder_id', true);
                if (!empty($id)) {
                    $exclude[] = $id;
                    $childs   = get_term_children((int) $id, WPMF_TAXO);
                    $exclude = array_merge($exclude, $childs);
                }
            }

            if (empty($dropbox_config['dropboxToken'])) {
                $id = get_option('wpmf_dropbox_folder_id', true);
                if (!empty($id)) {
                    $exclude[] = $id;
                    $childs   = get_term_children((int) $id, WPMF_TAXO);
                    $exclude = array_merge($exclude, $childs);
                }
            }
        }

        return $exclude;
    }

    /**
     * Select Folder To Upload
     *
     * @return void
     */
    public function selectFolderUpload()
    {
        if (strpos($_SERVER['REQUEST_URI'], 'media-new')) {
            ?>
            <div class="wpmf-upload-inline">
                <label for="wpmfu"><?php esc_html_e('Choose folder: ', 'wpmf'); ?></label>
                <select class="wpmf-gallery-folder" data-wmpf-nonce="<?php echo esc_attr(wp_create_nonce('wpmf_nonce')) ?>"></select>
            </div>
            <?php
        }
    }

    /**
     * Set file to current folder after upload files
     *
     * @param integer $attachment_id Id of attachment
     *
     * @return void
     */
    public function moveFileUploadToSelectFolder($attachment_id)
    {
        if (isset($_POST['id_category']) && isset($_POST['wpmf_nonce'])) {
            $_POST['ids'][] = $attachment_id;
            $_POST['no_return'] = 1;

            if (empty($_POST['wpmf_nonce']) || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
                die();
            }
            
            if ($attachment_id) {
                $parent = $this->getFolderParent($_POST['id_category'], $_POST['id_category']);
                // check is local or cloud
                $cloud_file_type = wpmfGetCloudFileType($attachment_id);
                $cloud_folder_type = wpmfGetCloudFolderType($parent);
                $file_s3_infos = get_post_meta((int) $attachment_id, 'wpmf_awsS3_info', true);
                if ($cloud_file_type === 'local' && $cloud_folder_type !== 'local' && empty($file_s3_infos)) {
                    $this->moveFile();
                }
            }
        }
    }

    /**
     * Add bulk select tag
     *
     * @param array $bulk_actions List bulk actions
     *
     * @return array
     */
    public function registerTagBulkAction($bulk_actions)
    {
        $bulk_actions['tag'] = __('Add tags', 'wpmf');
        return $bulk_actions;
    }

    /**
     * Get list tags
     *
     * @return void
     */
    public function getTagItem()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }
        global $wpdb;

        if (isset($_POST['tag_name']) && !empty($_POST['tag_name'])) {
            $list_tags = $wpdb->get_results($wpdb->prepare('SELECT t.name FROM ' . $wpdb->terms . ' as t INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt ON tt.term_id = t.term_id WHERE tt.taxonomy="wpmf_tag" AND t.name LIKE %s LIMIT %d', array('%' . $_POST['tag_name'] . '%',(int)10)));
        } else {
            $list_tags = $wpdb->get_results($wpdb->prepare('SELECT t.name FROM ' . $wpdb->terms . ' as t INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt ON tt.term_id = t.term_id WHERE tt.taxonomy="wpmf_tag" ORDER BY RAND() LIMIT %d', array((int)10)));
        }

        if ($list_tags) {
            $list_tags = array_column($list_tags, 'name');
            wp_send_json(array('status' => true, 'list_tags' => $list_tags));
        } else {
            wp_send_json(array('status' => false));
        }
    }

    /**
     * Save tags
     *
     * @return void
     */
    public function saveTagItem()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }
        if (isset($_POST['tag_name']) && !empty($_POST['tag_name']) && isset($_POST['post_ids'])) {
            $array_tags = array();
            foreach ($_POST['tag_name'] as $tag) {
                $array_tags[] = strtolower($tag);
            }
            foreach ($_POST['post_ids'] as $post_id) {
                $old_terms = array();
                $terms = wp_get_post_terms($post_id, 'wpmf_tag');
                if ($terms) {
                    $old_terms = array_column($terms, 'name');
                }
                $new_term = array_unique(array_merge($array_tags, $old_terms));
                wp_set_post_terms($post_id, $new_term, 'wpmf_tag');
            }
            wp_send_json(array('status' => true));
        }
        wp_send_json(array('status' => false));
    }

     /**
     * Delete files and folders information in database if cloud was disconnected
     *
     * @return void
     */
    public function removeDatabaseWhenCloudDisconnected()
    {
        $delete_all_datas = wpmfGetOption('delete_all_datas');
        if (empty($delete_all_datas)) {
            return;
        }
        //on Google Drive
        $option_cloud_google_drive = get_option(self::$option_google_drive_config);
        $folder_google_drive = get_terms(array('name' => 'Google Drive', 'parent' => 0, 'hide_empty' => false, 'taxonomy' => WPMF_TAXO));
        if (!is_wp_error($folder_google_drive) && $folder_google_drive && (!isset($option_cloud_google_drive['connected']) || $option_cloud_google_drive['connected'] !== 1)) {
            $this->doRemoveFolders($folder_google_drive[0]->term_id, false);
        }
        //on Dropbox
        $option_cloud_dropbox = get_option(self::$option_dropbox_config);
        $folder_dropbox = get_terms(array('name' => 'Dropbox', 'parent' => 0, 'hide_empty' => false, 'taxonomy' => WPMF_TAXO));
        if (!is_wp_error($folder_dropbox) && $folder_dropbox && empty($option_cloud_dropbox['dropboxToken'])) {
            $this->doRemoveFolders((int)$folder_dropbox[0]->term_id, false);
        }
        //on One Drive
        $option_cloud_one_drive = get_option(self::$option_one_drive_config);
        $folder_one_drive = get_terms(array('name' => 'Onedrive', 'parent' => 0, 'hide_empty' => false, 'taxonomy' => WPMF_TAXO));
        if (!is_wp_error($folder_one_drive) && $folder_one_drive && !isset($option_cloud_one_drive['connected'])) {
            $this->doRemoveFolders((int)$folder_one_drive[0]->term_id, false);
        }
        //on One Drive business
        $option_cloud_one_drive_business = get_option(self::$option_one_drive_business_config);
        $folder_one_drive_business = get_terms(array('name' => 'Onedrive Business', 'parent' => 0, 'hide_empty' => false, 'taxonomy' => WPMF_TAXO));
        if (!is_wp_error($folder_one_drive_business) && $folder_one_drive_business && !isset($option_cloud_one_drive_business['connected'])) {
            $this->doRemoveFolders((int)$folder_one_drive_business[0]->term_id, false);
        }
        //on Next Cloud
        $connect_nextcloud = wpmfGetOption('connect_nextcloud');
        $folder_next_cloud = get_terms(array('name' => 'Nextcloud', 'parent' => 0, 'hide_empty' => false, 'taxonomy' => WPMF_TAXO));
        if (!is_wp_error($folder_next_cloud) && $folder_next_cloud && empty($connect_nextcloud)) {
            $this->doRemoveFolders((int)$folder_next_cloud[0]->term_id, false);
        }
        //on Own Cloud
        $connect_owncloud = wpmfGetOption('connect_owncloud');
        $folder_own_cloud = get_terms(array('name' => 'ownCloud', 'parent' => 0, 'hide_empty' => false, 'taxonomy' => WPMF_TAXO));
        if (!is_wp_error($folder_own_cloud) && $folder_own_cloud && empty($connect_owncloud)) {
            $this->doRemoveFolders((int)$folder_own_cloud[0]->term_id, false);
        }
    }

     /**
     * Check file local to cloud
     *
     * @return void
     */
    public function checkLocalToCloud()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }
        
        $cloud_folder_type = wpmfGetCloudFolderType($_POST['id_category']);
        if ($cloud_folder_type !== 'local') {
            wp_send_json(array('status' => true));
        }
        wp_send_json(array('status' => false));
    }

    /**
     * Download file
     *
     * @return void
     */
    public function wpmfDownloadFile()
    {
        if (empty($_POST['wpmf_nonce'])
            || !wp_verify_nonce($_POST['wpmf_nonce'], 'wpmf_nonce')) {
            die();
        }

        $file_id = (isset($_POST['id'])) ? intval($_POST['id']) : 0;
        if (!empty($file_id)) {
            $path = get_attached_file($file_id);
            if (file_exists($path)) {
                wp_send_json(array('status' => true));
            } else {
                $drive_type = get_post_meta($file_id, 'wpmf_drive_type', true);
                if (!empty($drive_type)) {
                    if (!is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) {
                        die();
                    }
                    $drive_id = get_post_meta($file_id, 'wpmf_drive_id', true);
                    if (!empty($drive_id)) {
                        wp_send_json(array('status' => true));
                    }
                    wp_send_json(array('status' => false));
                }
                wp_send_json(array('status' => false));
            }
        }
        wp_send_json(array('status' => false));
    }
    
    /**
     * Add tag helps
     *
     * @param array $form_fields Array of post fields.
     *
     * @return array
     */
    public function addTagHelps($form_fields)
    {
        $form_fields['wpmf_tag']['label'] = '';
        $form_fields['wpmf_tag']['helps'] = 'Separate tags with commas';
        return $form_fields;
    }

    /**
     * Add tag filter
     *
     * @param object $query Query params.
     *
     * @return void
     */
    public function addTagFilter($query)
    {
        if (isset($query->query['post_type']) && $query->query['post_type'] === 'attachment') {
            if (isset($query->query['wpmf_tag'])) {
                $query->unset('wpmf_tag');
            }
            if (isset($_COOKIE['wpmf_tag']) && !empty($_COOKIE['wpmf_tag']) && $_COOKIE['wpmf_tag'] !== 'uncategorized') {
                $query->set('wpmf_tag', $_COOKIE['wpmf_tag']);
            }
        }
    }

    /**
     * Change value of tags from slug to name
     *
     * @param array  $form_fields Form fields.
     * @param object $post        Post.
     *
     * @return array
     */
    public function changeTagSlugToName($form_fields, $post)
    {
        global $wpdb;

        $terms = $wpdb->get_results($wpdb->prepare('SELECT tr.object_id, t.name FROM ' . $wpdb->terms . ' as t INNER JOIN ' . $wpdb->term_taxonomy . ' as tt ON tt.term_id = t.term_id INNER JOIN  ' . $wpdb->term_relationships . ' as tr ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy="wpmf_tag" AND tr.object_id = %s', array($post->ID)));
        
        $values = array();

        foreach ($terms as $term) {
            $values[] = $term->name;
        }

        $form_fields['wpmf_tag']['value'] = implode(', ', $values);
        
        return $form_fields;
    }

    /**
     * Delete file on cloud
     *
     * @param object $null Null.
     * @param object $post Post.
     *
     * @return void
     */
    public function deleteAttachmentCloud($null, $post)
    {
        do_action('wpmf_delete_attachment_cloud', $post->ID);
    }

    /**
     * Edit image on cloud
     *
     * @param void    $override  Override.
     * @param string  $filename  Filename.
     * @param object  $image     Image.
     * @param string  $mime_type Mime type.
     * @param integer $post_id   Post ID.
     *
     * @return boolean|null
     */
    public function editImage($override, $filename, $image, $mime_type, $post_id)
    {
        $cloud_file_type = wpmfGetCloudFileType($post_id);

        $upload_dir = wp_upload_dir();
        $dir_path = $upload_dir['basedir'];
        $image_edited_dir = $dir_path . '/wpmf-image-edited/';
        $extension = explode('/', $mime_type)[1];

        if ($extension && $cloud_file_type !== 'local') {
            $saved_image = $image->save($image_edited_dir . time() . '.' . $extension);
            if (!is_wp_error($saved_image)) {
                $newContent = file_get_contents($saved_image['path']);
                if ($newContent) {
                    $awsS3infos = get_post_meta($post_id, 'wpmf_awsS3_info', true);
                    if ($awsS3infos && isset($awsS3infos['Key'])) {
                        $cloud_file_type = 'offload';
                        $filepath = $awsS3infos['Key'];
                    } else {
                        $filepath = get_attached_file($post_id);
                    }

                    switch ($cloud_file_type) {
                        case 'offload':
                            apply_filters('wpmfAddonReplaceFileOffload', $newContent, $filepath);
                            break;
                        case 'google_drive':
                            apply_filters('wpmfAddonReplaceFileGGD', $newContent, $post_id);
                            break;
                        case 'dropbox':
                            apply_filters('wpmfAddonReplaceFileDropbox', $newContent, $post_id);
                            break;
                        case 'onedrive':
                            apply_filters('wpmfAddonReplaceFileOnedrive', $newContent, $post_id);
                            break;
                        case 'onedrive_business':
                            apply_filters('wpmfAddonReplaceFileOnedriveBusiness', $newContent, $post_id);
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
                //remove file edited
                $files = glob($image_edited_dir . '*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }

        return $override;
    }

    /**
     * Set default multi file uploader
     *
     * @return void
     */
    public function defaultMultiFileUploader()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        if (isset($_GET['browser-uploader'])) {
            wp_redirect(admin_url('media-new.php'));
            exit;
        }

        $settings = get_user_meta(get_current_user_id(), 'wp_user-settings', true);
        if (strpos($settings, 'uploader=1') !== false) {
            $settings = str_replace('uploader=1', '', $settings);
            update_user_meta(get_current_user_id(), 'wp_user-settings', $settings);
        }
    }

    /**
     * Upload plugin without create file in Media Library
     *
     * @param array $data Data.
     *
     * @return array
     */
    public function handleFileWhenUploadPlugin($data)
    {
        if ($data['post_mime_type'] !== 'application/zip') {
            return $data;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        if (isset($_POST['install-plugin-submit'])) {
            return false;
        }

        return $data;
    }

    /**
     * Send image to AI API for analysis.
     *
     * @return void
     */
    public function analyzeImageWithAI()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
        if (!$attachment_id) {
            wp_send_json_error(array('message' => __('Invalid attachment ID', 'wpmf')));
        }

        if (self::$useAiFromUrl) {
            $result = $this->doAnalyzeImageWithAIFromURL($attachment_id, true);
            if (isset($result['error'])) {
                wp_send_json_error(['message' => $result['error']]);
            }

            wp_send_json_success($result);
        } else {
            $result = $this->doAnalyzeImageWithAI($attachment_id, true);
            if (isset($result['error'])) {
                wp_send_json_error(['message' => $result['error']]);
            }

            wp_send_json_success($result['success']);
        }
    }

    /**
     * Handle AI analysis for given attachment.
     *
     * @param array   $metadata      The attachment metadata.
     * @param integer $attachment_id The attachment post ID.
     *
     * @return array
     */
    public function handleAnalyzeImageWithAI($metadata, $attachment_id)
    {
        if (self::$useAiFromUrl) {
            $this->doAnalyzeImageWithAIFromURL($attachment_id, false);
            if (get_option('wpmf_ai_rename_image_upload') === '1') {
                $metadata = $this->renameFileFromAI($metadata, $attachment_id);
            }
        } else {
            $this->doAnalyzeImageWithAI($attachment_id, false);
        }

        return $metadata;
    }

    /**
     * Process AI image analysis for a given attachment ID.
     *
     * @param integer $attachment_id Id of attachment
     * @param boolean $is_ajax       Check is ajax request
     *
     * @return array|null
     */
    public function doAnalyzeImageWithAI($attachment_id, $is_ajax = false)
    {
        $original_path = get_attached_file($attachment_id);
        if (!$original_path || !file_exists($original_path)) {
            return $is_ajax ? ['error' => __('File does not exist', 'wpmf')] : null;
        }

        $mime = get_post_mime_type($attachment_id);
        if (strpos($mime, 'image/') !== 0) {
            return $is_ajax ? ['error' => __('Only image files are supported', 'wpmf')] : null;
        }

        $meta = wp_get_attachment_metadata($attachment_id);
        $upload_dir = wp_upload_dir();
        $base_dir = trailingslashit($upload_dir['basedir']);
        $subdir = trailingslashit(dirname($meta['file'] ?? ''));

        $image_sizes = array('medium', 'large');
        $file_path = $original_path;

        foreach ($image_sizes as $size) {
            if (!empty($meta['sizes'][$size]['file'])) {
                $variant_path = $base_dir . $subdir . $meta['sizes'][$size]['file'];
                if (file_exists($variant_path)) {
                    $file_path = $variant_path;
                    break;
                }
            }
        }

        $option_to_field = array(
            'ai_image_title'       => 'title',
            'ai_image_alt'         => 'alt',
            'ai_image_description' => 'description',
            'ai_image_caption'     => 'caption'
        );

        $enabled_fields = array();
        if ($is_ajax || get_option('wpmf_ai_new_ai_auto_optimization') === '1') {
            foreach ($option_to_field as $option_key => $api_field) {
                if (get_option('wpmf_ai_' . $option_key, '0') === '1') {
                    $enabled_fields[] = $api_field;
                }
            }
        }

        if (!$is_ajax && get_option('wpmf_ai_rename_image_upload') === '1') {
            $enabled_fields[] = 'fileName';
        }

        if (empty($enabled_fields)) {
            return $is_ajax ? ['error' => __('No AI options are enabled', 'wpmf')] : null;
        }

        $force_override = get_option('wpmf_ai_force_override_metadata', '0') === '1';
        $only_rename = in_array('fileName', $enabled_fields, true) && count($enabled_fields) === 1;
        if (!$force_override && !$only_rename) {
            $meta = wp_get_attachment_metadata($attachment_id);
            $post = get_post($attachment_id);

            $existing_data = array(
                'title'       => $post->post_title,
                'alt'         => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
                'description' => $post->post_content,
                'caption'     => $post->post_excerpt
            );

            $has_empty = false;
            foreach ($enabled_fields as $field) {
                if (isset($existing_data[$field]) && trim($existing_data[$field]) === '') {
                    $has_empty = true;
                    break;
                }
            }

            if (!$has_empty) {
                return $is_ajax ? [
                    'success' => [
                        'skipped' => true,
                        'message' => __('Metadata already filled — skipping AI analysis', 'wpmf')
                    ]
                ] : null;
            }
        }

        $api_url          = self::$aiApiUrl . 'api/upload';

        $admin_ajax_url = admin_url('admin-ajax.php');
        if (defined('WPMF_AI_URL_REPLACE')) {
            $parsed = wp_parse_url($admin_ajax_url);
            $replace_base = rtrim(WPMF_AI_URL_REPLACE, '/');

            $admin_ajax_url = $replace_base . $parsed['path'];
        }

        $notification_url = add_query_arg([
            'action'        => 'wpmf_handle_ai_fallback',
            'attachment_id' => $attachment_id,
        ], $admin_ajax_url);

        $token = get_site_option('wpmf_license_token');
        if (empty($token)) {
            return $is_ajax ? ['error' => __('Missing license token', 'wpmf')] : null;
        }

        $mime_type = mime_content_type($file_path);
        $language = $this->getCurrentLanguageCode();
        $ai_lang  = get_option('wpmf_ai_image_language', 'default');
        if ($ai_lang !== 'default') {
            $language = $ai_lang;
        }
        $language = $this->convertLangCodeToName($language);

        $system_prompt = trim(get_option('wpmf_ai_system_prompt_context', ''));
        $boundary  = wp_generate_password(24, false);
        $eol       = "\r\n";

        $body  = '';
        $body .= '--' . $boundary . $eol;
        $body .= 'Content-Disposition: form-data; name="file"; filename="' . basename($file_path) . '"' . $eol;
        $body .= 'Content-Type: ' . $mime_type . $eol . $eol;
        $body .= file_get_contents($file_path) . $eol;

        $body .= '--' . $boundary . $eol;
        $body .= 'Content-Disposition: form-data; name="notification"' . $eol . $eol;
        $body .= $notification_url . $eol;

        $body .= '--' . $boundary . $eol;
        $body .= 'Content-Disposition: form-data; name="fields"' . $eol . $eol;
        $body .= implode(',', $enabled_fields) . $eol;

        $body .= '--' . $boundary . $eol;
        $body .= 'Content-Disposition: form-data; name="lang"' . $eol . $eol;
        $body .= $language . $eol;

        if ($system_prompt !== '') {
            $body .= '--' . $boundary . $eol;
            $body .= 'Content-Disposition: form-data; name="system_prompt"' . $eol . $eol;
            $body .= $system_prompt . $eol;
        }

        $body .= '--' . $boundary . '--' . $eol;

        $response = wp_remote_post($api_url, array(
            'timeout'  => 60,
            'headers'  => array(
                'Authorization' => $token,
                'Content-Type'  => 'multipart/form-data; boundary=' . $boundary,
            ),
            'body'     => $body,
            'method'   => 'POST'
        ));

        if (is_wp_error($response)) {
            return $is_ajax ? array('error' => $response->get_error_message()) : null;
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $body      = wp_remote_retrieve_body($response);
        $result    = json_decode($body, true);

        if ($http_code !== 200 || (isset($result['success']) && $result['success'] === false)) {
            $message = isset($result['error']) ? $result['error'] : sprintf(__('Unexpected HTTP response: %s', 'wpmf'), $http_code);
            return $is_ajax ? array('error' => $message) : null;
        }

        update_option('wpmf_ai_pending_' . $attachment_id, true, false);

        return $is_ajax ? ['success' => $result] : null;
    }

    /**
     * Process AI image analysis for a given attachment ID using its URL instead of uploading the file.
     *
     * @param integer $attachment_id ID of the attachment.
     * @param boolean $is_ajax       Whether this is an AJAX request.
     *
     * @return array|null
     */
    public function doAnalyzeImageWithAIFromURL($attachment_id, $is_ajax = false)
    {
        $file_url = wp_get_attachment_url($attachment_id);
        if (empty($file_url)) {
            return $is_ajax ? ['error' => __('Unable to retrieve attachment URL', 'wpmf')] : null;
        }

        $mime = get_post_mime_type($attachment_id);
        if (strpos($mime, 'image/') !== 0) {
            return $is_ajax ? ['error' => __('Only image files are supported', 'wpmf')] : null;
        }

        $image_sizes = array('medium', 'large');
        foreach ($image_sizes as $size) {
            $src = wp_get_attachment_image_src($attachment_id, $size);
            if (!empty($src) && isset($src[0])) {
                $file_url = $src[0];
                break;
            }
        }

        if (defined('WPMF_AI_URL_REPLACE') && !empty(WPMF_AI_URL_REPLACE) && strpos($file_url, home_url()) === 0) {
            $site_url = rtrim(home_url(), '/');
            $replacement = rtrim(WPMF_AI_URL_REPLACE, '/');
            $file_url = str_replace($site_url, $replacement, $file_url);
        }

        $option_to_field = array(
            'ai_image_title'       => 'title',
            'ai_image_alt'         => 'alt',
            'ai_image_description' => 'description',
            'ai_image_caption'     => 'caption'
        );

        $enabled_fields = array();
        if ($is_ajax || get_option('wpmf_ai_new_ai_auto_optimization') === '1') {
            foreach ($option_to_field as $option_key => $api_field) {
                if (get_option('wpmf_ai_' . $option_key, '0') === '1') {
                    $enabled_fields[] = $api_field;
                }
            }
        }

        if (!$is_ajax && get_option('wpmf_ai_rename_image_upload') === '1') {
            $enabled_fields[] = 'fileName';
        }

        if (empty($enabled_fields)) {
            return $is_ajax ? ['error' => __('No AI options are enabled', 'wpmf')] : null;
        }

        $force_override = get_option('wpmf_ai_force_override_metadata', '0') === '1';
        $post = get_post($attachment_id);
        $existing_data = array(
            'title'       => $post->post_title,
            'alt'         => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
            'description' => $post->post_content,
            'caption'     => $post->post_excerpt
        );

        $has_empty = false;
        foreach ($enabled_fields as $field) {
            if (isset($existing_data[$field]) && trim($existing_data[$field]) === '') {
                $has_empty = true;
                break;
            }
        }

        if (!$force_override && !$has_empty) {
            return $is_ajax ? [
                'success' => [
                    'skipped' => true,
                    'message' => __('Metadata already filled — skipping AI analysis', 'wpmf')
                ]
            ] : null;
        }

        $token   = get_site_option('wpmf_license_token');
        $api_url = rtrim(self::$aiApiUrl, '/') . '/api/upload/url';

        if (empty($token)) {
            return $is_ajax ? ['error' => __('Missing license token', 'wpmf')] : null;
        }

        $language = $this->getCurrentLanguageCode();
        $ai_lang  = get_option('wpmf_ai_image_language', 'default');
        if ($ai_lang !== 'default') {
            $language = $ai_lang;
        }
        $language = $this->convertLangCodeToName($language);
        $system_prompt = trim(get_option('wpmf_ai_system_prompt_context', ''));

        $body = array(
            'file_url' => $file_url,
            'fields'   => implode(',', $enabled_fields),
            'lang'     => $language
        );

        if ($system_prompt !== '') {
            $body['system_prompt'] = $system_prompt;
        }

        $response = wp_remote_post($api_url, array(
            'method'    => 'POST',
            'timeout'   => 60,
            'headers'   => array(
                'Authorization' => $token
            ),
            'body'      => $body
        ));

        if (is_wp_error($response)) {
            return $is_ajax ? array('error' => $response->get_error_message()) : null;
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $body      = wp_remote_retrieve_body($response);
        $result    = json_decode($body, true);

        if ($http_code !== 200 || (isset($result['success']) && $result['success'] === false)) {
            $message = isset($result['error']) ? $result['error'] : sprintf(__('Unexpected HTTP response: %s', 'wpmf'), $http_code);
            return $is_ajax ? array('error' => $message) : null;
        }

        $response_data = $result;
        if (empty($response_data) || !is_array($response_data)) {
            return $is_ajax ? ['error' => __('Invalid API response', 'wpmf')] : null;
        }

        if (empty($response_data['success']) || empty($response_data['data']['file'])) {
            return $is_ajax ? array('error' => __('AI API did not return expected data', 'wpmf')) : null;
        }

        $file_data = $response_data['data']['file'];

        $is_optimized = get_post_meta($attachment_id, 'wpmf_ai_optimized', true) === '1';

        // Update attachment metadata immediately
        $data         = array('status' => 'done');
        $update_post  = array('ID' => $attachment_id);

        if (!empty($file_data['altText'])) {
            $alt = sanitize_text_field($file_data['altText']);
            $current_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
            if ($force_override || trim($current_alt) === '' || (!$is_optimized && !$is_ajax)) {
                update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
                $data['alt'] = $alt;
            }
        }

        if (!empty($file_data['title'])) {
            $title = sanitize_text_field($file_data['title']);
            if ($force_override || trim($post->post_title) === '' || (!$is_optimized && !$is_ajax)) {
                $update_post['post_title'] = $title;
                $data['title'] = $title;
            }
        }

        if (!empty($file_data['caption'])) {
            $caption = sanitize_text_field($file_data['caption']);
            if ($force_override || trim($post->post_excerpt) === '' || (!$is_optimized && !$is_ajax)) {
                $update_post['post_excerpt'] = $caption;
                $data['caption'] = $caption;
            }
        }

        if (!empty($file_data['description'])) {
            $description = sanitize_text_field($file_data['description']);
            if ($force_override || trim($post->post_content) === '' || (!$is_optimized && !$is_ajax)) {
                $update_post['post_content'] = $description;
                $data['description'] = $description;
            }
        }

        if (!empty($file_data['newFileName'])) {
            $raw_filename = $file_data['newFileName'];
            $new_filename = sanitize_file_name($raw_filename);

            set_transient('wpmf_ai_new_filename_' . $attachment_id, $new_filename, 5 * MINUTE_IN_SECONDS);
        }

        if (count($update_post) > 1) {
            wp_update_post($update_post);
        }

        set_transient('wpmf_ai_result_' . $attachment_id, $data, 5 * MINUTE_IN_SECONDS);
        update_post_meta($attachment_id, 'wpmf_ai_optimized', 1);

        $this->getAIQuota(false);

        return $is_ajax ? ['success' => $data] : null;
    }

    /**
     * Trigger AI rename only when rename_image_upload is enabled.
     *
     * @param array   $metadata      The attachment metadata.
     * @param integer $attachment_id The attachment post ID.
     *
     * @return void
     */
    public function handleRenameImageAIOnly($metadata, $attachment_id)
    {
        if (!self::$useAiFromUrl) {
            $this->doAnalyzeImageWithAI($attachment_id, false);
        } else {
            $file_url = wp_get_attachment_url($attachment_id);
            if (empty($file_url)) {
                return;
            }

            $mime = get_post_mime_type($attachment_id);
            if (strpos($mime, 'image/') !== 0) {
                return;
            }

            $image_sizes = array('medium', 'large');
            foreach ($image_sizes as $size) {
                $src = wp_get_attachment_image_src($attachment_id, $size);
                if (!empty($src) && isset($src[0])) {
                    $file_url = $src[0];
                    break;
                }
            }

            if (defined('WPMF_AI_URL_REPLACE') && !empty(WPMF_AI_URL_REPLACE) && strpos($file_url, home_url()) === 0) {
                $site_url = rtrim(home_url(), '/');
                $replacement = rtrim(WPMF_AI_URL_REPLACE, '/');
                $file_url = str_replace($site_url, $replacement, $file_url);
            }

            $token = get_site_option('wpmf_license_token');
            if (empty($token)) {
                return;
            }

            $language = $this->getCurrentLanguageCode();
            $ai_lang  = get_option('wpmf_ai_image_language', 'default');
            if ($ai_lang !== 'default') {
                $language = $ai_lang;
            }
            $language = $this->convertLangCodeToName($language);
            $system_prompt = trim(get_option('wpmf_ai_system_prompt_context', ''));

            $body = array(
                'file_url' => $file_url,
                'fields'   => 'fileName',
                'lang'     => $language
            );

            if ($system_prompt !== '') {
                $body['system_prompt'] = $system_prompt;
            }

            $response = wp_remote_post(rtrim(self::$aiApiUrl, '/') . '/api/upload/url', array(
                'method'    => 'POST',
                'timeout'   => 60,
                'headers'   => array(
                    'Authorization' => $token
                ),
                'body'      => $body
            ));

            if (is_wp_error($response)) {
                return;
            }

            $http_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $result = json_decode($body, true);

            if ($http_code !== 200 || empty($result['success']) || empty($result['data']['file']['newFileName'])) {
                return;
            }

            $new_filename = sanitize_file_name($result['data']['file']['newFileName']);
            update_post_meta($attachment_id, 'wpmf_ai_optimized', 1);
            set_transient('wpmf_ai_new_filename_' . $attachment_id, $new_filename, 5 * MINUTE_IN_SECONDS);

            $metadata = $this->renameFileFromAI($metadata, $attachment_id);
        }

        return $metadata;
    }

    /**
     * Renames the attachment file and its resized versions using AI generated filename.
     *
     * @param array   $metadata      The attachment metadata.
     * @param integer $attachment_id The attachment post ID.
     *
     * @return array
     */
    public function renameFileFromAI($metadata, $attachment_id)
    {
        $new_filename = get_transient('wpmf_ai_new_filename_' . $attachment_id);
        if (empty($new_filename)) {
            return $metadata;
        }

        $new_filename = sanitize_file_name($new_filename);
        $file_path = get_attached_file($attachment_id);
        if (!file_exists($file_path)) {
            delete_transient('wpmf_ai_new_filename_' . $attachment_id);
            return $metadata;
        }

        $info = pathinfo($file_path);
        $upload_dir = $info['dirname'];

        $ext = pathinfo($file_path, PATHINFO_EXTENSION);
        if (!preg_match('/\.(jpe?g|png|webp)$/i', $new_filename)) {
            $new_filename .= '.' . $ext;
        } else {
            if (strtolower($ext) === 'webp') {
                $new_filename = preg_replace('/\.(jpe?g|png)$/i', '.webp', $new_filename);
            }
        }
        $new_path = $upload_dir . '/' . $new_filename;

        if ($file_path === $new_path || file_exists($new_path)) {
            delete_transient('wpmf_ai_new_filename_' . $attachment_id);
            return $metadata;
        }

        if (!empty($metadata['original_image'])) {
            $old_base = pathinfo($metadata['original_image'], PATHINFO_FILENAME);
        } else {
            $old_base = pathinfo($info['basename'], PATHINFO_FILENAME);
        }

        $new_base = pathinfo($new_filename, PATHINFO_FILENAME);

        rename($file_path, $new_path);
        update_attached_file($attachment_id, $new_path);

        if (!empty($metadata['file'])) {
            $metadata['file'] = str_replace($old_base, $new_base, $metadata['file']);
        }

        if (!empty($metadata['original_image'])) {
            $old_original = $metadata['original_image'];
            $new_original = str_replace($old_base, $new_base, $old_original);
            $old_original_path = $upload_dir . '/' . $old_original;
            $new_original_path = $upload_dir . '/' . $new_original;

            if (file_exists($old_original_path)) {
                rename($old_original_path, $new_original_path);
                $metadata['original_image'] = $new_original;
            }
        }

        if (!empty($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size => &$size_data) {
                if (!empty($size_data['file'])) {
                    $old_size_file = $size_data['file'];
                    $new_size_file = str_replace($old_base, $new_base, $old_size_file);
                    $old_size_path = $upload_dir . '/' . $old_size_file;
                    $new_size_path = $upload_dir . '/' . $new_size_file;

                    if (file_exists($old_size_path)) {
                        rename($old_size_path, $new_size_path);
                        $size_data['file'] = $new_size_file;
                    }
                }
            }
        }

        wp_update_attachment_metadata($attachment_id, $metadata);
        delete_transient('wpmf_ai_new_filename_' . $attachment_id);

        return $metadata;
    }

    /**
     * Handle AI fallback callback to update attachment metadata.
     *
     * @return void
     */
    public function handleAiFallback()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        $attachment_id = isset($_GET['attachment_id']) ? absint($_GET['attachment_id']) : 0;

        $raw_body = file_get_contents('php://input');
        $response = json_decode($raw_body, true);

        if (empty($response) || !$attachment_id) {
            wp_send_json_error(array('message' => __('Invalid response or missing attachment_id', 'wpmf')));
        }

        $force_override = get_option('wpmf_ai_force_override_metadata', '0') === '1';

        $is_optimized = get_post_meta($attachment_id, 'wpmf_ai_optimized', true) === '1';

        $post = get_post($attachment_id);
        $data = array('status' => 'done');

        if (!empty($response['altText'])) {
            $current_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
            $data['alt'] = $current_alt;
            if ($force_override || trim($current_alt) === '' || !$is_optimized) {
                $data['alt'] = sanitize_text_field($response['altText']);
                update_post_meta($attachment_id, '_wp_attachment_image_alt', sanitize_text_field($response['altText']));
            }
        }

        $update_data = [];
        if (!empty($response['title'])) {
            if ($force_override || trim($post->post_title) === '' || !$is_optimized) {
                $data['title'] = sanitize_text_field($response['title']);
                $update_data['post_title'] = sanitize_text_field($response['title']);
            }
        }
        if (!empty($response['caption'])) {
            if ($force_override || trim($post->post_excerpt) === '' || !$is_optimized) {
                $data['caption'] = sanitize_text_field($response['caption']);
                $update_data['post_excerpt'] = sanitize_text_field($response['caption']);
            }
        }
        if (!empty($response['description'])) {
            if ($force_override || trim($post->post_content) === '' || !$is_optimized) {
                $data['description'] = sanitize_text_field($response['description']);
                $update_data['post_content'] = sanitize_text_field($response['description']);
            }
        }

        if (!empty($update_data)) {
            $update_data['ID'] = $attachment_id;
            wp_update_post($update_data);
        }

        set_transient('wpmf_ai_result_' . $attachment_id, $data, 5 * MINUTE_IN_SECONDS);
        update_post_meta($attachment_id, 'wpmf_ai_optimized', 1);
        delete_option('wpmf_ai_pending_' . $attachment_id);

        if (!empty($response['newFileName']) && get_option('wpmf_ai_rename_image_upload') === '1') {
            set_transient('wpmf_ai_new_filename_' . $attachment_id, $response['newFileName'], 5 * MINUTE_IN_SECONDS);
            $metadata = wp_get_attachment_metadata($attachment_id);
            $this->renameFileFromAI($metadata, $attachment_id);
        }

        $this->getAIQuota(false);

        wp_send_json_success();
    }

    /**
     * Check if AI analysis result is ready.
     *
     * @return void
     */
    public function checkAIResult()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        $attachment_id = isset($_POST['attachment_id']) ? absint($_POST['attachment_id']) : 0;

        if (!$attachment_id) {
            wp_send_json_error(array('message' => __('Missing attachment ID', 'wpmf')));
        }

        $result = get_transient('wpmf_ai_result_' . $attachment_id);

        if (!empty($result) && isset($result['status']) && $result['status'] === 'done') {
            wp_send_json_success($result);
        }

        wp_send_json_error(array('message' => __('Not ready yet', 'wpmf')));
    }

    /**
     * Get AI image optimization progress.
     *
     * @return void
     */
    public function getAIProgress()
    {
        global $wpdb;

        $results = $wpdb->get_col(
            'SELECT option_name 
             FROM ' . $wpdb->options . " 
             WHERE option_name LIKE 'wpmf_ai_pending_%'"
        );

        $ids = array_map(function ($name) {
            return (int) str_replace('wpmf_ai_pending_', '', $name);
        }, $results);

        wp_send_json_success([
            'total' => count($ids),
            'ids'   => $ids
        ]);
    }

    /**
     * Run getAIQuota once for admin users.
     *
     * @return void
     */
    public function runAIQuotaOnce()
    {
        if (!is_admin() || !current_user_can('manage_options')) {
            return;
        }

        if (get_option('wpmf_ai_quota_initialized') !== 'yes') {
            $this->getAIQuota(false);
            update_option('wpmf_ai_quota_initialized', 'yes');
        }
    }

    /**
     * Get AI account info from the API.
     *
     * @param boolean $is_ajax Check is ajax request
     *
     * @return void|array
     */
    public function getAIQuota($is_ajax = true)
    {
        if ($is_ajax === '' || $is_ajax === null) {
            $is_ajax = wp_doing_ajax();
        }

        $token = get_site_option('wpmf_license_token');
        if (empty($token)) {
            $error = array('message' => __('Missing license token', 'wpmf'));
            return $is_ajax ? wp_send_json_error($error) : $error;
        }

        $api_url = self::$aiApiUrl . 'api/accounts/me';

        $response = wp_remote_get($api_url, [
            'timeout' => 30,
            'headers' => [
                'Authorization' => $token
            ]
        ]);

        if (is_wp_error($response)) {
            $error_data = ['message' => $response->get_error_message()];
            return $is_ajax ? wp_send_json_error($error_data) : $error_data;
        }

        $http_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);

        if ($http_code !== 200 || (isset($result['success']) && $result['success'] === false)) {
            if (is_multisite()) {
                delete_site_option('wpmf_ai_quota_info');
                delete_site_option('wpmf_ai_plan_status');
                delete_site_option('wpmf_ai_quota_initialized');
            } else {
                delete_option('wpmf_ai_quota_info');
                delete_option('wpmf_ai_plan_status');
                delete_option('wpmf_ai_quota_initialized');
            }

            if (isset($result['error'])) {
                $message = $result['error'];
            } else {
                $message = sprintf(__('Unexpected HTTP response: %s', 'wpmf'), $http_code);
            }

            $error_data = ['message' => $message];

            return $is_ajax ? wp_send_json_error($error_data) : $error_data;
        }

        update_option('wpmf_ai_plan_status', 'paid');

        $data = $result['data'];
        $quota = isset($data['quota']) ? (int)$data['quota'] : 0;
        $used = isset($data['consummate_quota']) ? (int)$data['consummate_quota'] : 0;
        $remaining = max($quota - $used, 0);

        $output = array(
            'quota'             => $quota,
            'consummate_quota'  => $used,
            'remaining_quota'   => $remaining
        );

        update_option('wpmf_ai_quota_info', $output, false);

        if ($is_ajax) {
            $percent_used = ($quota > 0) ? min(($used / $quota) * 100, 100) : 0;
            $percent_used_display = floor($percent_used) == $percent_used ? (int) $percent_used : number_format($percent_used, 2);

            $bar_color = '#01AB6A';
            if ($percent_used >= 90) {
                $bar_color = '#DD2929';
            } elseif ($percent_used >= 50) {
                $bar_color = '#EC7E00';
            }

            $formatted_output = array(
                'quota'             => wpmfCustomNumberFormat($quota),
                'consummate_quota'  => wpmfCustomNumberFormat($used),
                'remaining_quota'   => wpmfCustomNumberFormat($remaining),
                'percent_used'      => $percent_used_display,
                'bar_color'         => $bar_color
            );

            wp_send_json_success($formatted_output);
        }
    }

    /**
     * Add AI quota icon to admin bar
     *
     * @param WP_Admin_Bar $wp_admin_bar The WP admin bar object.
     *
     * @return void
     */
    public function addAIQuotaAdminBarItem($wp_admin_bar)
    {
        $ai_quota_info = get_option('wpmf_ai_quota_info', array());
        $html = '';

        if (!empty($ai_quota_info)) {
            $used      = (int) $ai_quota_info['consummate_quota'];
            $limit     = (int) $ai_quota_info['quota'];
            $remaining = (int) $ai_quota_info['remaining_quota'];

            if ($limit > 0) {
                $percent_used      = min(($used / $limit) * 100, 100);
                $percent_remaining = 100 - $percent_used;

                $percent_used_display = floor($percent_used) == $percent_used ? (int) $percent_used : number_format($percent_used, 2);

                if ($percent_remaining <= 10) {
                    $bar_color = '#DD2929';
                } elseif ($percent_remaining < 50) {
                    $bar_color = '#EC7E00';
                } else {
                    $bar_color = '#01AB6A';
                }

                ob_start();
                ?>
                <div class="wpmf-ai-quota-container">
                    <div class="wpmf-ai-quota-status" style="background-color: <?php echo esc_attr($bar_color); ?>">
                        <span class="ju-icon-AI"></span>
                        <?php echo esc_html($percent_used_display); ?>%
                    </div>
                    <div class="wpmf-ai-quota-popup">
                        <div class="wpmf-ai-quota-title"><?php esc_html_e('Your plan', 'wpmf'); ?></div>
                        <div class="wpmf-ai-quota-content">
                            <div class="wpmf-ai-quota-usage">
                                <span class="wpmf-ai-quota-used"><?php echo esc_html(wpmfCustomNumberFormat($used)); ?></span> / <?php echo esc_html(wpmfCustomNumberFormat($limit)); ?>
                                <?php esc_html_e('credits', 'wpmf'); ?>
                            </div>
                            <div class="wpmf-ai-quota-progress-container">
                                <div class="wpmf-ai-quota-progress-bar" style="width: <?php echo esc_attr($percent_used); ?>%; background-color: <?php echo esc_attr($bar_color); ?>;"></div>
                            </div>
                            <div class="wpmf-ai-quota-remaining">
                                <?php esc_html_e('Credits usage left:', 'wpmf'); ?>
                                <span class="wpmf-ai-quota-remaining-count" style="color: <?php echo esc_attr($bar_color); ?>">
                                    <?php echo esc_html(wpmfCustomNumberFormat($remaining)); ?>
                                </span>
                            </div>
                        </div>
                        <div>
                            <a href="<?php echo esc_url(admin_url('options-general.php?page=option-folder#ai_subscribe')); ?>" class="wpmf-ai-subscribe-link button"><?php esc_html_e('Get More Credits', 'wpmf') ?></a>
                        </div>
                    </div>
                </div>
                <?php
                $html = ob_get_clean();
            }
        }

        $wp_admin_bar->add_node(
            array(
                'id'    => 'wpmf_ai_quota_status',
                'title' => $html,
                'meta'  => array('class' => 'wpmf-ai-quota-bar')
            )
        );
    }

    /**
     * Enqueue admin styles for AI features.
     *
     * @return void
     */
    public function enqueueAIAdminStyles()
    {
        wp_enqueue_style(
            'wpmf-ai-image-optimization-style',
            plugin_dir_url(dirname(__FILE__)) . 'assets/css/ai-image-optimization.css',
            array(),
            WPMF_VERSION
        );

        wp_enqueue_script(
            'wpmf-ai-image-optimization',
            plugins_url('/assets/js/ai-image-optimization.js', dirname(__FILE__)),
            array('jquery'),
            WPMF_VERSION
        );

        $params = $this->localizeScript();
        wp_localize_script('wpmf-ai-image-optimization', 'wpmf', $params);
    }

    /**
     * Filters the attachment data prepared for JavaScript.
     * Base on /wp-includes/media.php
     *
     * @param array          $response   Array of prepared attachment data.
     * @param integer|object $attachment Attachment ID or object.
     * @param array          $meta       Array of attachment meta data.
     *
     * @return mixed $response
     */
    public function wpPrepareAttachmentForJs($response, $attachment, $meta)
    {
        $plan_status = get_option('wpmf_ai_plan_status', 'not_paid');
        if ($plan_status === 'not_paid') {
            return $response;
        }

        $wpmf_ai_optimized = get_post_meta($attachment->ID, 'wpmf_ai_optimized', true);
        if (empty($wpmf_ai_optimized)) {
            return $response;
        }

        $response['wpmf_ai_optimized'] = $wpmf_ai_optimized;
        return $response;
    }

    /**
     * Ajax handler: Get all image attachment IDs in a given folder.
     *
     * @return void
     */
    public function getAttachmentsInFolder()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        $folder_id = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : 0;
        if (!$folder_id) {
            wp_send_json_error(__('Missing folder ID', 'wpmf'));
        }

        $image_ids = get_posts(
            array(
                'post_type'      => 'attachment',
                'post_status'    => 'inherit',
                'post_mime_type' => 'image',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'wpmf-category',
                        'field'    => 'term_id',
                        'terms'    => $folder_id,
                        'include_children' => true
                    )
                )
            )
        );

        if (!empty($image_ids)) {
            wp_send_json_success($image_ids);
        } else {
            wp_send_json_error(__('No image attachments found in folder', 'wpmf'));
        }
    }

    /**
     * Get the current language code.
     *
     * @return string Current language code
     */
    public function getCurrentLanguageCode()
    {
        if (defined('ICL_LANGUAGE_CODE')) {
            return ICL_LANGUAGE_CODE;
        } elseif (function_exists('pll_current_language')) {
            return pll_current_language();
        }

        return get_locale();
    }

    /**
     * Convert language code to full language name
     *
     * @param string $lang_code Language code
     *
     * @return string Full name of the language
     */
    public function convertLangCodeToName($lang_code)
    {
        require_once ABSPATH . 'wp-admin/includes/translation-install.php';
        $translations = wp_get_available_translations();

        if (isset($translations[$lang_code])) {
            return $translations[$lang_code]['english_name'] ?? $lang_code;
        }

        foreach ($translations as $code => $data) {
            if (strpos($code, $lang_code) === 0) {
                return $data['english_name'] ?? $lang_code;
            }
        }

        return $lang_code;
    }

    /**
     * Customize ORDER BY clause to sort by meta_value_num and post_title
     *
     * @param array    $clauses SQL query clauses.
     * @param WP_Query $query   WP_Query object.
     *
     * @return array
     */
    public function customOrderByMetaValueNumAndTitle($clauses, $query)
    {
        $title_asc = apply_filters('sort_media_custom_order_and_title_asc', false);
        if ($query->get('meta_key') === 'wpmf_order' && $query->get('orderby') === 'meta_value_num') {
            global $wpdb;
            if ($title_asc) {
                $clauses['orderby'] = $wpdb->postmeta . '.meta_value+0 ASC, ' . $wpdb->posts . '.post_title ASC';
            } else {
                $clauses['orderby'] = $wpdb->postmeta . '.meta_value+0 ASC, ' . $wpdb->posts . '.post_title DESC';
            }
        }

        return $clauses;
    }
}
