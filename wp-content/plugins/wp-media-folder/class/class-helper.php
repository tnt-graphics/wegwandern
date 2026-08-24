<?php
namespace Joomunited\WPMediaFolder;

use \WP_Query;

/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
/**
 * Class WpmfHelper
 * This class that holds most of the main functionality for Media Folder.
 */
class WpmfHelper
{
    /**
     * User full access ID
     *
     * @var array
     */
    public static $user_full_access_id = array();

    /**
     * Vimeo pattern
     *
     * @var string
     */
    public static $vimeo_pattern = '%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im';

    /**
     * Initialize plugin hooks and integrations.
     *
     * Registers all WordPress actions and filters required for the WP Media Folder
     * plugin, including cron jobs, media handling, REST API routes, and integrations
     * with popular page builders.
     *
     * @return void
     */
    public static function init()
    {
        add_filter('cron_schedules', [__CLASS__, 'wpmfSchedules']);
        add_action('wpmf_save_settings', [__CLASS__, 'wpmfDoCrontab']);
        add_action('wpmfSyncServerFolder', [__CLASS__, 'wpmfSyncServerFolder']);
        add_action('init', [__CLASS__, 'onInit']);
        // Elementor
        add_action('elementor/editor/after_enqueue_styles', [__CLASS__, 'wpmfLoadElementorWidgetStyle']);
        add_action('elementor/editor/after_enqueue_styles', [__CLASS__, 'wpmfLoadElementorWidgetScript']);
        add_action('elementor/elements/categories_registered', [__CLASS__, 'wpmfAddElementorWidgetCategories']);
        // Divi
        add_action('divi_extensions_init', [__CLASS__,  'wpmfInitializeDiviExtension']);
        // Bakery
        add_action('vc_frontend_editor_enqueue_js_css', [__CLASS__, 'wpmfVcEnqueueJsCss']);
        add_action('vc_before_init', [__CLASS__, 'wpmfVcBeforeInit']);

        add_action('admin_enqueue_scripts', [__CLASS__, 'wpmfAddStyle']);
        add_action('wp_enqueue_media', [__CLASS__, 'wpmfAddStyle']);
        add_filter('wp_get_attachment_url', [__CLASS__, 'wpmfGetAttachmentImportUrl'], 99, 2);
        add_filter('wp_prepare_attachment_for_js', [__CLASS__, 'wpmfGetAttachmentImportData'], 10, 3);
        add_filter('mailpoet_conflict_resolver_whitelist_script', [__CLASS__, 'wpmf_mailpoet_conflict_resolver_whitelist_script'], 10, 1);
        add_filter('mailpoet_conflict_resolver_whitelist_style', [__CLASS__, 'wpmf_mailpoet_conflict_resolver_whitelist_style'], 10, 1);
        add_filter('update_plugins_www.joomunited.com', [__CLASS__, 'wpmfPluginCheckForUpdates'], 10, 3);
        //Hide remote video
        $remote_video = self::wpmfGetOption('hide_remote_video');
        if ($remote_video) {
            add_filter('the_content', [__CLASS__, 'wpmfFindImages'], 20);
        }

        add_action('transition_post_status', [__CLASS__, 'wpmfTransitionPostStatus'], 10, 3);
        add_filter('the_content', [__CLASS__, 'replaceVideoAndAudioGoogleDriveWithIframe']);
        add_filter('elementor/frontend/the_content', [__CLASS__, 'replaceVideoAndAudioGoogleDriveWithIframe']);
        add_filter('after_setup_theme', [__CLASS__, 'loadDivi5Integration'], 9);
        add_filter('rest_api_init', [__CLASS__, 'registerRestRoutes']);
    }

    /**
     * Handle tasks that should run on 'init' hook
     *
     * @return void
     */
    public static function onInit()
    {
        self::initLoadGif();
        self::wpmfTnitAvada();
        self::wpmfRegisterTaxonomyForImages();
        self::wpmfDownloadFile();
        /* Register wpmf_tag taxonomy */
        self::wpmfTagRegisterTaxonomy();
        self::initWooCommerceIntegration();
    }

    /**
     * Initialize admin hooks
     *
     * @return void
     */
    public static function initAdminHooks()
    {
        if (!is_admin()) {
            return;
        }

        add_action('wp_ajax_wpmfju_update_license', [__CLASS__, 'wpmfUpdateToken']);
        add_action('init', [__CLASS__, 'wpmfWizardSetupLoaded']);
        add_action('admin_init', [__CLASS__, 'wpmfAdminRedirects'], 0);
    }

    /**
     * Check PHP version and deactivate plugin if needed
     *
     * @return boolean
     */
    public static function checkPhpVersion()
    {
        if (version_compare(PHP_VERSION, '5.6', '<')) {
            add_action('admin_init', [__CLASS__, 'disablePlugin']);
            add_action('admin_notices', [__CLASS__, 'showError']);
            return false;
        }
        return true;
    }

    /**
     * Deactivate plugin
     *
     * @return void
     */
    public static function disablePlugin()
    {
        $capability = apply_filters(
            'wpmf_user_can',
            current_user_can('activate_plugins'),
            'activate_plugins'
        );

        if ($capability && is_plugin_active(plugin_basename(WPMF_FILE))) {
            deactivate_plugins(WPMF_FILE);
            unset($_GET['activate']);// phpcs:ignore WordPress.Security.NonceVerification.Recommended
        }
    }

    /**
     * Show admin notice
     *
     * @return void
     */
    public static function showError()
    {
        echo '<div class="error"><p>';
        echo '<strong>WP Media Folder</strong> ';
        echo esc_html__('needs at least PHP 5.6. Please update PHP before installing the plugin.', 'wpmf');
        echo '</p></div>';
    }

    /**
     * Add recurrences
     *
     * @param array $schedules Schedules
     *
     * @return mixed
     */
    public static function wpmfSchedules($schedules)
    {
        $enable_sync          = get_option('wpmf_option_sync_media');
        $periodicity = get_option('wpmf_time_sync', true);
        $periodicity = (int)$periodicity*60;
        if ((int)$periodicity !== 0 && !empty($enable_sync)) {
            $schedules[$periodicity . 's'] = array('interval' => $periodicity, 'display' => $periodicity . 's');
        }
        return $schedules;
    }

    /**
     * CLear and add new crontab
     *
     * @return void
     */
    public static function wpmfDoCrontab()
    {
        $enable_sync          = get_option('wpmf_option_sync_media');
        $periodicity = get_option('wpmf_time_sync', true);
        $periodicity = (int)$periodicity*60;
        $hooks = array('wpmfSyncServerFolder');
        if (!empty($enable_sync) && (int)$periodicity !== 0) {
            foreach ($hooks as $synchook) {
                wp_clear_scheduled_hook($synchook);
                if (!wp_next_scheduled($synchook)) {
                    wp_schedule_event(time(), $periodicity . 's', $synchook);
                }
            }
        } else {
            foreach ($hooks as $synchook) {
                wp_clear_scheduled_hook($synchook);
            }
        }
    }

    /**
     * Sync server folder with cronjob
     *
     * @return void
     */
    public static function wpmfSyncServerFolder()
    {
        set_time_limit(0);
        $lists     = get_option('wpmf_list_sync_media');
        update_option('wpmf_lastRun_sync', time());
        if (!class_exists('\Joomunited\Queue\JuMainQueue')) {
            require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'queue/JuMainQueue.php';
        }

        if (!class_exists('WpmfMediaFolderOption')) {
            require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-wp-folder-option.php');
        }
        $sync = get_option('wpmf_option_sync_media');
        foreach ($lists as $folderId => $v) {
            if (file_exists($v['folder_ftp'])) {
                // add to queue
                if (!empty($sync)) {
                    $wpmfQueue = \Joomunited\Queue\JuMainQueue::getInstance('wpmf');
                    $directory = $v['folder_ftp'];
                    $dir_files = glob($directory . '*');
                    foreach ($dir_files as $dir_file) {
                        if (!is_readable($dir_file)) {
                            continue;
                        }

                        $validate_path = str_replace('//', '/', $dir_file);
                        $name = basename($validate_path);
                        $datas = array(
                            'path' => $dir_file,
                            'server_parent' => $directory,
                            'folder_parent' => $folderId,
                            'action' => 'wpmf_sync_ftp_to_library'
                        );
                        if (is_dir($dir_file)) {
                            $datas['name'] = $name;
                            $datas['type'] = 'folder';
                        } else {
                            $is_thumb_or_scaled = preg_match('/(-scaled|[_-]\d+x\d+)|@[2-6]\x(?=\.[a-z]{3,4}$)/im', $name);
                            if ($is_thumb_or_scaled) {
                                continue;
                            }

                            $datas['name'] = $name;
                            $datas['hash'] = md5_file($dir_file);
                            $datas['type'] = 'file';
                        }

                        $row = $wpmfQueue->checkQueueExist(json_encode($datas));
                        if (!$row) {
                            $wpmfQueue->addToQueue($datas);
                        } else {
                            $class_option = new \WpmfMediaFolderOption();
                            $responses = json_decode($row->responses, true);
                            if (is_dir($dir_file)) {
                                if (isset($responses['folder_id'])) {
                                    $class_option->doAddSyncFtpQueue($datas['path'] . DIRECTORY_SEPARATOR, (int)$responses['folder_id']);
                                }
                            } else {
                                self::wpmfAddToQueue($datas);
                            }

                            if (isset($responses['folder_id'])) {
                                $class_option->doAddExternalSyncFtpQueue((int)$responses['folder_id'], $datas['path']);
                            }
                        }
                    }

                    $wpmfQueue->proceedQueueAsync();
                }
            }
        }
    }

    /**
     * Initialize queue integration for WP Media Folder
     *
     * @return void
     */
    public static function initQueueIntegration()
    {
        if (!is_admin()) {
            return;
        }

        if (!class_exists('\Joomunited\Queue\JuMainQueue')) {
            require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'queue/JuMainQueue.php';
        }

        /**
         * Translate for queue class.
         * ***** DO NOT REMOVE *****
         * Translate strings in JuMainQueue.php file
         * esc_html__('Some of JoomUnited\'s plugins require to process some task in background (cloud synchronization, file processing, ...).', 'wpmf');
         * esc_html__('To prevent PHP timeout errors during the process, it\'s done asynchronously in the background.', 'wpmf');
         * esc_html__('These settings let you optimize the process depending on your server resources.', 'wpmf'); ?>
         * esc_html__('Show the number of items waiting to be processed in the admin menu bar.', 'wpmf');
         * esc_html__('You can reduce the background task processing by changing this parameter. It could be necessary when the plugin is installed on small servers instances but requires consequent task processing. Default 75%.', 'wpmf');
         * esc_html__('You can reduce the background task ajax calling by changing this parameter. It could be necessary when the plugin is installed on small servers instances or shared hosting. Default 15s.', 'wpmf');
         * esc_html__('Pause queue', 'wpmf');
         * esc_html__('Pause queue', 'wpmf');
         * esc_html__('Start queue', 'wpmf');
         * esc_html__('Enable', 'wpmf');
         *
         * ***** DO NOT REMOVE *****
         * End translate for queue class
         */
        add_action('init', function () {
            $args = self::wpmfGetQueueOptions(false);
            $wpmfQueue = call_user_func('\Joomunited\Queue\JuMainQueue::getInstance', 'wpmf');
            $wpmfQueue->init($args);
            $folder_options = get_option('wpmf_queue_options');
            if (!empty($folder_options['enable_physical_folders'])) {
                require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/physical-folder' . DIRECTORY_SEPARATOR . 'wpmf.php';
                new \JUQueueActions();
                require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/physical-folder' . DIRECTORY_SEPARATOR . 'helper.php';
            }
        });

        add_action(
            'wpmf_before_delete_folder',
            function ($folder_term) {
                $wpmfQueue = \Joomunited\Queue\JuMainQueue::getInstance('wpmf');
                $queue_id = get_term_meta($folder_term->term_id, 'wpmf_sync_queue', true);
                if (!empty($queue_id)) {
                    if (is_array($queue_id)) {
                        foreach ($queue_id as $queueID) {
                            $wpmfQueue->deleteQueue($queueID);
                        }
                    } else {
                        $wpmfQueue->deleteQueue($queue_id);
                    }
                }
            },
            2,
            2
        );
        add_action('delete_attachment', function ($id) {
            $queue_id = get_post_meta($id, 'wpmf_sync_queue', true);
            $wpmfQueue = \Joomunited\Queue\JuMainQueue::getInstance('wpmf');
            if (!empty($queue_id)) {
                if (is_array($queue_id)) {
                    foreach ($queue_id as $queueID) {
                        $wpmfQueue->deleteQueue($queueID);
                    }
                } else {
                    $wpmfQueue->deleteQueue($queue_id);
                }
            }
        }, 10);
        add_action('wpmf_delete_attachment', function ($id) {
            $queue_id = get_post_meta($id, 'wpmf_sync_queue', true);
            $wpmfQueue = \Joomunited\Queue\JuMainQueue::getInstance('wpmf');
            if (!empty($queue_id)) {
                if (is_array($queue_id)) {
                    foreach ($queue_id as $queueID) {
                        $wpmfQueue->deleteQueue($queueID);
                    }
                } else {
                    $wpmfQueue->deleteQueue($queue_id);
                }
            }
        }, 10);
    }

    /**
     * Initialize plugin requirements check
     *
     * @return void
     */
    public static function initRequirementsCheck()
    {
        add_action('init', [__CLASS__, 'checkRequirements']);
    }

    /**
     * Check plugin requirements and load addons
     *
     * @return void
     */
    public static function checkRequirements()
    {
        if (!class_exists('\Joomunited\WPMF\JUCheckRequirements')) {
            require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'requirements.php';
        }

        if (class_exists('\Joomunited\WPMF\JUCheckRequirements')) {
            // Plugins name for translate
            $args = array(
                'plugin_name' => esc_html__('WP Media Folder', 'wpmf'),
                'plugin_path' => self::wpmfGetPath(),
                'plugin_textdomain' => 'wpmf',
                'requirements' => array(
                    'php_version' => '7.4',
                    'php_modules' => array(
                        'curl' => 'warning'
                    ),
                    'functions' => array(
                        'gd_info' => 'warning'
                    ),
                    // Minimum addons version
                    'addons_version' => array(
                        'wpmfAddons' => '3.6.9',
                        'wpmfGalleryAddons' => '2.4.6'
                    )
                ),
            );
            $wpmfCheck = call_user_func('\Joomunited\WPMF\JUCheckRequirements::init', $args);

            if (!$wpmfCheck['success']) {
                // Do not load anything more
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
                unset($_GET['activate']);
                return;
            }

            if (isset($wpmfCheck) && !empty($wpmfCheck['load'])) {
                foreach ($wpmfCheck['load'] as $addonName) {
                    if (function_exists($addonName . 'Init')) {
                        call_user_func($addonName . 'Init');
                    }
                }
            }
        }
    }

    /**
     * Get queue options
     *
     * @param boolean $cron Is cron
     *
     * @return array
     */
    public static function wpmfGetQueueOptions($cron = false)
    {
        $args = array(
            'use_queue' => true, // required
            'assets_url' => WPMF_PLUGIN_URL . 'queue/assets/queue.js',
            'plugin_prefix' => 'ju',
            'status_templates' => array(
                'wpmf_sync_google_drive' => esc_html__('Syncing %d Google Drive files', 'wpmf'),
                'wpmf_sync_onedrive' => esc_html__('Syncing %d OneDrive files', 'wpmf'),
                'wpmf_sync_onedrive_business' => esc_html__('Syncing %d OneDrive Business files', 'wpmf'),
                'wpmf_sync_dropbox' => esc_html__('Syncing %d Dropbox files', 'wpmf'),
                'wpmf_sync_nextcloud' => esc_html__('Syncing %d Nextcloud files', 'wpmf'),
                'wpmf_sync_owncloud' => esc_html__('Syncing %d ownCloud files', 'wpmf'),
                'wpmf_google_drive_remove' => esc_html__('Comparing %d Google Drive folders', 'wpmf'),
                'wpmf_dropbox_remove' => esc_html__('Comparing %d Dropbox folders', 'wpmf'),
                'wpmf_onedrive_remove' => esc_html__('Comparing %d OneDrive folders', 'wpmf'),
                'wpmf_onedrive_business_remove' => esc_html__('Comparing %d OneDrive Business folders', 'wpmf'),
                'wpmf_nextcloud_remove' => esc_html__('Comparing %d Nextcloud folders', 'wpmf'),
                'wpmf_owncloud_remove' => esc_html__('Comparing %d ownCloud folders', 'wpmf'),
                'wpmf_s3_import' => esc_html__('Importing %d files from Amazon S3', 'wpmf'),
                'wpmf_digitalocean_import' => esc_html__('Importing %d files from DigitalOcean', 'wpmf'),
                'wpmf_wasabi_import' => esc_html__('Importing %d files from Wasabi', 'wpmf'),
                'wpmf_linode_import' => esc_html__('Importing %d files from Linode', 'wpmf'),
                'wpmf_google_cloud_storage_import' => esc_html__('Importing %d files from Google Cloud', 'wpmf'),
                'wpmf_cloudflare_r2_import' => esc_html__('Importing %d files from Cloudflare R2', 'wpmf'),
                'wpmf_vultr_import' => esc_html__('Importing %d files from Vultr', 'wpmf'),
                'wpmf_bunny_import' => esc_html__('Importing %d files from Bunny Storage', 'wpmf'),
                'wpmf_replace_s3_url_by_page' => esc_html__('%d actions in queue to updating Amazon S3 URL', 'wpmf'),
                'wpmf_replace_aws3_url_by_page' => esc_html__('%d actions in queue to updating Amazon S3 URL', 'wpmf'),
                'wpmf_replace_digitalocean_url_by_page' => esc_html__('%d actions in queue to updating DigitalOcean URL', 'wpmf'),
                'wpmf_replace_wasabi_url_by_page' => esc_html__('%d actions in queue to updating Wasabi URL', 'wpmf'),
                'wpmf_replace_linode_url_by_page' => esc_html__('%d actions in queue to updating Linode URL', 'wpmf'),
                'wpmf_replace_google_cloud_storage_url_by_page' => esc_html__('%d actions in queue to updating Google Cloud URL', 'wpmf'),
                'wpmf_physical_folders' => esc_html__('Moving %d real files', 'wpmf'),
                'wpmf_replace_physical_url' => esc_html__('Updating URL of %d files', 'wpmf'),
                'wpmf_sync_ftp_to_library' => esc_html__('Syncing %d files from FTP', 'wpmf'),
                'wpmf_sync_library_to_ftp' => esc_html__('Syncing %d files from Media to FTP', 'wpmf'),
                'wpmf_import_ftp_to_library' => esc_html__('Importing %d files from FTP', 'wpmf'),
                'wpmf_s3_remove_local_file' => esc_html__('Removing %d files after offload', 'wpmf'),
                'wpmf_move_local_to_cloud' => esc_html__('Moving %d files from server to cloud', 'wpmf'),
                'wpmf_replace_cloud_url_by_page' => esc_html__('%d actions in queue to updating file URL', 'wpmf'),
                'wpmf_remove_local_file' => esc_html__('Removing %d files after upload to cloud', 'wpmf'),
                'wpmf_import_nextgen_gallery' => esc_html__('Importing %d galleries from NextGen', 'wpmf'),
                'wpmf_nextcloud_render_thumbnail' => esc_html__('Regenerating thumbnails for %d Nextcloud files', 'wpmf'),
                'wpmf_owncloud_render_thumbnail' => esc_html__('Regenerating thumbnails for %d ownCloud files', 'wpmf'),
                'wpmf_dropbox_render_thumbnail' => esc_html__('Regenerating thumbnails for %d Dropbox files', 'wpmf')
            ), // required
            'queue_options' => array(
                'mode_debug' => 0, // required
                'enable_physical_folders' => 0,
                'auto_detect_tables' => 1,
                'replace_relative_paths' => (get_option('uploads_use_yearmonth_folders')) ? 1 : 0,
                'search_full_database' => 0,
            ) // required
        );

        return $args;
    }

    /**
     * Get plugin path
     *
     * @return string
     */
    public static function wpmfGetPath()
    {
        if (!function_exists('plugin_basename')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        return plugin_basename(WPMF_FILE);
    }

    /**
     * Load term
     *
     * @param string $taxonomy Taxonomy name
     *
     * @return array|object|null
     */
    public static function wpmfLoadTerms($taxonomy)
    {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare('SELECT DISTINCT t.term_id FROM '.$wpdb->terms.' t INNER JOIN '.$wpdb->term_taxonomy.' tax ON tax.term_id = t.term_id WHERE tax.taxonomy = %s', array($taxonomy)), ARRAY_A);
        return $results;
    }

    /**
     * UnInstall plugin
     *
     * @return void
     */
    public static function wpmfUnInstall()
    {
        $delete_all_datas = self::wpmfGetOption('delete_all_datas');
        if (!empty($delete_all_datas)) {
            // delete folder
            $folders = self::wpmfLoadTerms('wpmf-category');
            foreach ($folders as $folder) {
                wp_delete_term((int) $folder['term_id'], 'wpmf-category');
            }

            $folders = self::wpmfLoadTerms('wpmf-gallery-category');
            foreach ($folders as $folder) {
                wp_delete_term((int) $folder['term_id'], 'wpmf-gallery-category');
            }

            // delete cloud media
            global $wpdb;
            $limit = 100;
            $total         = $wpdb->get_var($wpdb->prepare('SELECT COUNT(posts.ID) as total FROM ' . $wpdb->prefix . 'posts as posts
                WHERE   posts.post_type = %s', array('attachment')));

            $j = ceil((int) $total / $limit);
            for ($i = $j; $i > 0; $i --) {
                $offset      = ($i - 1) * $limit;
                $args = array(
                    'post_type' => 'attachment',
                    'posts_per_page' => $limit,
                    'offset' => $offset,
                    'post_status' => 'any'
                );

                $files = get_posts($args);
                foreach ($files as $file) {
                    $wpmf_drive_id = get_post_meta($file->ID, 'wpmf_drive_type', true);
                    if (!empty($wpmf_drive_id)) {
                        wp_delete_attachment($file->ID);
                    } else {
                        delete_post_meta($file->ID, 'wpmf_size');
                        delete_post_meta($file->ID, 'wpmf_filetype');
                        delete_post_meta($file->ID, 'wpmf_order');
                        delete_post_meta($file->ID, 'wpmf_awsS3_info');
                    }
                }
            }

            // delete table
            global $wpdb;
            $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wpmf_s3_queue');

            // delete all options with prefix 'wpmf_';
            $wpdb->query('DELETE FROM '.$wpdb->options. " WHERE option_name LIKE '%wpmf_%'");
            // delete all options with prefix '_wpmfAddon_';
            $wpdb->query('DELETE FROM '.$wpdb->options. " WHERE option_name LIKE '%_wpmfAddon_%'");

            // delete other options
            $options_list = array(
                '_wpmf_import_notice_flag',
                '_wpmf_import_order_notice_flag',
                '_wpmf_import_size_notice_flag',
                '_wpmf_activation_redirect',
                'wpmfgrl_relationships_media',
                'wpmfgrl_relationships',
                'wp-media-folder-addon-tables'
            );

            foreach ($options_list as $option) {
                delete_option($option);
            }
        }
    }

    /**
     * Install plugin
     *
     * @return void
     */
    public static function wpmfInstall()
    {
        set_time_limit(0);
        global $wpdb;
        $limit         = 100;
        $values        = array();
        $place_holders = array();
        $total         = $wpdb->get_var($wpdb->prepare('SELECT COUNT(posts.ID) as total FROM ' . $wpdb->prefix . 'posts as posts
                WHERE   posts.post_type = %s', array('attachment')));

        if ($total <= 5000) {
            $j = ceil((int) $total / $limit);
            for ($i = 1; $i <= $j; $i ++) {
                $offset      = ($i - 1) * $limit;
                $attachments = $wpdb->get_results($wpdb->prepare('SELECT ID FROM ' . $wpdb->prefix . 'posts as posts
                WHERE   posts.post_type     = %s LIMIT %d OFFSET %d', array('attachment', $limit, $offset)));
                foreach ($attachments as $attachment) {
                    $wpmf_size_filetype = self::wpmfGetSizeFiletype($attachment->ID);
                    $size               = $wpmf_size_filetype['size'];
                    $ext                = $wpmf_size_filetype['ext'];
                    if (!get_post_meta($attachment->ID, 'wpmf_size')) {
                        array_push($values, $attachment->ID, 'wpmf_size', $size);
                        $place_holders[] = "('%d', '%s', '%s')";
                    }

                    if (!get_post_meta($attachment->ID, 'wpmf_filetype')) {
                        array_push($values, $attachment->ID, 'wpmf_filetype', $ext);
                        $place_holders[] = "('%d', '%s', '%s')";
                    }

                    if (!get_post_meta($attachment->ID, 'wpmf_order')) {
                        array_push($values, $attachment->ID, 'wpmf_order', 0);
                        $place_holders[] = "('%d', '%s', '%d')";
                    }
                }

                if (count($place_holders) > 0) {
                    $query = 'INSERT INTO ' . $wpdb->prefix . 'postmeta (post_id, meta_key, meta_value) VALUES ';
                    $query .= implode(', ', $place_holders);
                    $wpdb->query($wpdb->prepare($query, $values)); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Insert multiple row, can't write sql in prepare
                    $place_holders = array();
                    $values        = array();
                }
            }
        }
    }

    /**
     * Includes WP Media Folder setup
     *
     * @return void
     */
    public static function wpmfWizardSetupLoaded()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View request, no action
        if (!empty($_GET['page'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View request, no action
            switch ($_GET['page']) {
                case 'wpmf-setup':
                    require_once WP_MEDIA_FOLDER_PLUGIN_DIR . '/class/install-wizard/install-wizard.php';
                    break;
            }
        }
    }

    /**
     * Admin redirect
     *
     * @return void
     */
    public static function wpmfAdminRedirects()
    {
        global $pagenow;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View request, no action
        if (($pagenow !== 'upload.php' && $pagenow !== 'plugins.php') && (!isset($_GET['page']) || $_GET['page'] !== 'option-folder')) {
            return;
        }

        // validate and convert old token to new license token
        if (empty(get_site_option('wpmf_license_token')) && !empty(get_option('ju_user_token'))) {
            $extName = 'wp-media-folder';
            $ju_update_link = JU_BASE . 'index.php?option=com_juupdater&task=licenses.convert&token=' . get_option('ju_user_token').'&extName='.$extName.'&site=' . site_url() ;
            $res = wp_remote_get($ju_update_link);
            $new_license = wp_remote_retrieve_body($res);
            if (!empty($new_license)) {
                $new_license = json_decode($new_license);
                if (is_multisite() && current_user_can('manage_network')) {
                    update_site_option('wpmf_license_token', $new_license->token);
                } else {
                    update_option('wpmf_license_token', $new_license->token);
                    delete_option('_wpmf_activation_redirect');
                }
            }
        }

        // Setup wizard redirect
        if ($pagenow !== 'plugins.php' && empty(get_site_option('wpmf_license_token'))) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View request, no action
            if ((!empty($_GET['page']) && in_array($_GET['page'], array('wpmf-setup')))) {
                return;
            }
            if (defined('DOING_AJAX') && DOING_AJAX) {
                return;
            }
            wp_safe_redirect(admin_url('index.php?page=wpmf-setup'));
            exit;
        }
    }

    /**
     * Update license token
     *
     * @return void
     */
    public static function wpmfUpdateToken()
    {

        if (empty($_POST['ju_updater_nonce'])
            || !wp_verify_nonce($_POST['ju_updater_nonce'], 'ju_updater_nonce')) {
            die();
        }

        if (isset($_POST['token'])) {
            //check if it's a multisite and have network admin access
            if (is_multisite() && current_user_can('manage_network')) {
                update_site_option('wpmf_license_token', sanitize_text_field($_POST['token']));
            } else {
                update_option('wpmf_license_token', $_POST['token']);
                delete_option('_wpmf_activation_redirect');
                update_option('wpmf_db_install', '1');
            }
        }
    }

    /**
     * Get size and file type for attachment
     *
     * @param integer $pid ID of attachment
     *
     * @return array
     */
    public static function wpmfGetSizeFiletype($pid)
    {
        $wpmf_size_filetype = array();
        $meta               = get_post_meta($pid, '_wp_attached_file');
        $upload_dir         = wp_upload_dir();
        if (empty($meta)) {
            return array('size' => 0, 'ext' => '');
        }
        $url_attachment     = $upload_dir['basedir'] . '/' . $meta[0];
        if (file_exists($url_attachment)) {
            $size     = filesize($url_attachment);
            $filetype = wp_check_filetype($url_attachment);
            $ext      = $filetype['ext'];
        } else {
            $size = 0;
            $ext  = '';
        }
        $wpmf_size_filetype['size'] = $size;
        $wpmf_size_filetype['ext']  = $ext;

        return $wpmf_size_filetype;
    }

    /**
     * Set a option
     *
     * @param string            $option_name Option name
     * @param string|array|void $value       Value of option
     *
     * @return boolean
     */
    public static function wpmfSetOption($option_name, $value)
    {
        $settings = get_option('wpmf_settings');
        if (empty($settings)) {
            $settings               = array();
            $settings[$option_name] = $value;
        } else {
            $settings[$option_name] = $value;
        }

        $return = update_option('wpmf_settings', $settings);
        return $return;
    }

    /**
     * Get a option
     *
     * @param string $option_name Option name
     *
     * @return mixed
     */
    public static function wpmfGetOption($option_name)
    {
        $formats_title       = get_option('wpmf_options_format_title');
        if (empty($formats_title)) {
            $formats_title = array();
        }

        $media_download       = json_decode(get_option('wpmf_color_singlefile'), true);
        if (empty($media_download)) {
            $media_download = array();
        }

        $params_theme     = array(
            'default_theme'     => array(
                'columns'    => 3,
                'size'       => 'medium',
                'targetsize' => 'large',
                'link'       => 'file',
                'orderby'    => 'post__in',
                'order'      => 'ASC',
                'aspect_ratio' => 'default'
            ),
            'portfolio_theme'   => array(
                'columns'    => 3,
                'size'       => 'medium',
                'targetsize' => 'large',
                'link'       => 'file',
                'orderby'    => 'post__in',
                'order'      => 'ASC',
                'aspect_ratio' => 'default'
            ),
            'masonry_theme'     => array(
                'columns'    => 3,
                'size'       => 'medium',
                'targetsize' => 'large',
                'link'       => 'file',
                'orderby'    => 'post__in',
                'order'      => 'ASC'
            ),
            'slider_theme'      => array(
                'columns'        => 3,
                'size'           => 'medium',
                'targetsize'     => 'large',
                'link'           => 'file',
                'orderby'        => 'post__in',
                'order'          => 'ASC',
                'animation'      => 'slide',
                'duration'       => 4000,
                'auto_animation' => 1,
                'aspect_ratio' => 'default'
            ),
            'flowslide_theme'   => array(
                'columns'      => 3,
                'size'         => 'medium',
                'targetsize'   => 'large',
                'link'         => 'file',
                'orderby'      => 'post__in',
                'order'        => 'ASC',
                'show_buttons' => 1
            ),
            'square_grid_theme' => array(
                'columns'    => 3,
                'size'       => 'medium',
                'targetsize' => 'large',
                'link'       => 'file',
                'orderby'    => 'post__in',
                'order'      => 'ASC',
                'aspect_ratio' => 'default'
            ),
            'material_theme'    => array(
                'columns'    => 3,
                'size'       => 'medium',
                'targetsize' => 'large',
                'link'       => 'file',
                'orderby'    => 'post__in',
                'order'      => 'ASC',
                'aspect_ratio' => 'default'
            ),
        );
        $gallery_settings = array(
            'theme' => $params_theme
        );

        $gallery_shortcode_settings = array(
            'choose_gallery_id'       => 0,
            'choose_gallery_theme'    => 'default',
            'display_tree'            => 0,
            'sub_galleries_listing'   => 0,
            'display_tag'             => 0,
            'disable_overlay'             => 0,
            'theme'                   => $params_theme,
            'gallery_shortcode_input' => ''
        );

        $default_settings = array(
            'photograper_default_dimensions' => array(
                '640x427' => array(
                    'name' => esc_html__('Small', 'wpmf'),
                    'width' => 640,
                    'height' => 427
                ),
                '1280x853' => array(
                    'name' => esc_html__('Medium', 'wpmf'),
                    'width' => 1280,
                    'height' => 853
                ),
                '1920x1280' => array(
                    'name' => esc_html__('Large', 'wpmf'),
                    'width' => 1920,
                    'height' => 1280
                ),
                '6000x4000' => array(
                    'name' => esc_html__('Extra Large', 'wpmf'),
                    'width' => 6000,
                    'height' => 4000
                )
            ),
            'photograper_dimension' => array('640x427', '1280x853', '1920x1280', '6000x4000', 'full'),
            'photograper_image_watermark_apply' => array(),
            'root_media_count' => 0,
            'delete_all_datas' => 0,
            'watermark_exclude_public_gallery' => 0,
            'watermark_exclude_photograph_gallery' => 0,
            'all_media_in_user_root' => 0,
            'load_gif' => 1,
            'hide_tree' => 1,
            'enable_folders' => 1,
            'caption_lightbox_gallery' => 0,
            'hide_remote_video' => 1,
            'enable_download_media' => 0,
            'auto_generate_webp' => 0,
            'trash' => 0,
            'trash_days' => 30,
            'wpmf_share_folders_multisite' => 0,
            'image_info' => 0,
            'default_featured_image_type' => 'fixed',
            'default_featured_image' => 0,
            'featured_image_folder' => 0,
            'folder_color' => array(),
            'watermark_image_scaling' => 100,
            'social_sharing' => 0,
            'search_file_include_childrent' => 0,
            'social_sharing_link' => array(
                'facebook' => '',
                'twitter' => '',
                'google' => '',
                'instagram' => '',
                'pinterest' => ''
            ),
            'watermark_margin' => array(
                'top' => 0,
                'right' => 0,
                'bottom' => 0,
                'left' => 0
            ),
            'format_mediatitle' => 1,
            'gallery_settings' => $gallery_settings,
            'gallery_shortcode' => $gallery_shortcode_settings,
            'gallery_shortcode_cf' => array(
                'wpmf_folder_id' => 0,
                'display' => 'default',
                'columns' => 3,
                'size' => 'medium',
                'targetsize' => 'large',
                'link' => 'file',
                'wpmf_orderby' => 'post__in',
                'wpmf_order' => 'ASC',
                'autoplay' => 1,
                'include_children' => 0,
                'gutterwidth' => 10,
                'img_border_radius' => 0,
                'border_style' => 'none',
                'border_width' => 0,
                'border_color' => 'transparent',
                'img_shadow' => '0 0 0 0 transparent',
                'value' => ''
            ),
            'watermark_exclude_folders' => array(),
            'sync_method' => 'ajax',
            'sync_periodicity' => '900',
            'show_folder_id' => 0,
            'connect_nextcloud' => 0,
            'connect_owncloud' => 0,
            'watermark_opacity' => 100,
            'watermark_margin_unit' => 'px',
            'allow_sync_extensions' => 'jpg,jpeg,jpe,gif,png,svg,webp,bmp,tiff,tif,ico,7z,bz2,gz,rar,tgz,zip,csv,doc,docx,ods,odt,pdf,pps,ppt,pptx,ppsx,rtf,txt,xls,xlsx,psd,tif,tiff,mid,mp3,mp4,ogg,wma,3gp,avi,flv,m4v,mkv,mov,mpeg,mpg,swf,vob,wmv,webm',
            'allow_syncs3_extensions' => 'jpg,jpeg,jpe,gif,png,svg,webp,bmp,tiff,tif,ico,7z,bz2,gz,rar,tgz,zip,csv,doc,docx,ods,odt,pdf,pps,ppt,pptx,ppsx,rtf,txt,xls,xlsx,psd,tif,tiff,mid,mp3,mp4,ogg,wma,3gp,avi,flv,m4v,mkv,mov,mpeg,mpg,swf,vob,wmv,webm',
            'import_iptc_meta' => 0,
            'iptc_fields' => array(
                'title' => 1,
                'alt' => 1,
                'description' => 0,
                'caption' => 0,
                '2#025' => 0,
                'credit' => 0,
                '2#005' => 0,
                '2#010' => 0,
                '2#015' => 0,
                '2#020' => 0,
                '2#040' => 0,
                '2#055' => 0,
                '2#080' => 0,
                '2#085' => 0,
                '2#090' => 0,
                '2#095' => 0,
                '2#100' => 0,
                '2#101' => 0,
                '2#103' => 0,
                '2#105' => 1,
                '2#110' => 0,
                '2#115' => 0,
                '2#116' => 0
            ),
            'export_folder_type' => 'only_folder',
            'tasks_speed' => 100,
            'status_menu_bar' => 0,
            'wpmf_export_folders' => array(),
            'wp-media-folder-tables' => array(
                'wp_posts' => array(
                    'post_content' => 1,
                    'post_excerpt' => 1
                )
            ),
            'wpmf_options_format_title' => array_merge(array(
                'hyphen'          => 1,
                'underscore'      => 1,
                'period'          => 0,
                'tilde'           => 0,
                'plus'            => 0,
                'capita'          => 'cap_all',
                'alt'             => 0,
                'caption'         => 0,
                'description'     => 0,
                'hash'            => 0,
                'ampersand'       => 0,
                'copyright'       => 0,
                'number'          => 0,
                'square_brackets' => 0,
                'round_brackets'  => 0,
                'curly_brackets'  => 0
            ), $formats_title),
            'media_download' => array_merge(array(
                'bgdownloadlink'   => '#202231',
                'hvdownloadlink'   => '#1c1e2a',
                'fontdownloadlink' => '#f4f6ff',
                'hoverfontcolor'   => '#ffffff',
                'margin_top' => 30,
                'margin_right' => 30,
                'margin_bottom' => 30,
                'margin_left' => 30,
                'padding_top' => 20,
                'padding_right' => 30,
                'padding_bottom' => 20,
                'padding_left' => 70,
                'border_radius' => 15,
                'border_width' => 0,
                'border_type' => 'solid',
                'border_color' => '#f4f6ff',
                'icon_image' => 'download_style_0',
                'icon_color' => '#f4f6ff'
            ), $media_download),
            'wpmf_minimize_folder_tree_post_type' => 1,
            'wpmf_option_folder_post' => 0,
            'wpmf_folder_tree_status' => array(),
            'wpmf_active_folders_post_types' => array()
        );
        $settings         = get_option('wpmf_settings');
        if (isset($settings) && isset($settings[$option_name])) {
            if (is_array($settings[$option_name]) && !empty($default_settings[$option_name])) {
                if ($option_name === 'photograper_default_dimensions') {
                    return $settings[$option_name];
                } else {
                    return array_merge($default_settings[$option_name], $settings[$option_name]);
                }
            } else {
                return $settings[$option_name];
            }
        }

        if (!isset($default_settings[$option_name])) {
            return false;
        }

        return $default_settings[$option_name];
    }

    /**
     * Load script for elementor
     *
     * @return void
     */
    public static function wpmfLoadElementorWidgetStyle()
    {
        wp_enqueue_style(
            'wpmf-widgets',
            WPMF_PLUGIN_URL . 'assets/css/elementor-widgets/widgets.css',
            array(),
            WPMF_VERSION,
            'all'
        );
        $ui_theme = \Elementor\Core\Settings\Manager::get_settings_managers('editorPreferences')->get_model()->get_settings('ui_theme');
        wp_enqueue_style(
            'wpmf-widgets-light',
            WPMF_PLUGIN_URL . 'assets/css/elementor-widgets/widgets-light.css',
            array('elementor-editor'),
            WPMF_VERSION,
            'all'
        );

        if ('light' !== $ui_theme) {
            $ui_theme_media_queries = 'all';
            if ('auto' === $ui_theme) {
                $ui_theme_media_queries = '(prefers-color-scheme: dark)';
            }

            wp_enqueue_style(
                'wpmf-widgets-dark',
                WPMF_PLUGIN_URL . 'assets/css/elementor-widgets/widgets-dark.css',
                array('elementor-editor-dark-mode'),
                WPMF_VERSION,
                $ui_theme_media_queries
            );
        }
    }


    /**
     * Load script for elementor
     *
     * @return void
     */
    public static function wpmfLoadElementorWidgetScript()
    {
        wp_enqueue_media();
        wp_enqueue_script(
            'wpmf-widgets',
            WPMF_PLUGIN_URL . 'class/elementor-widgets/widgets.js',
            array('jquery'),
            WPMF_VERSION
        );
    }

    /**
     * Add elementor widget categories
     *
     * @param object $elements_manager Elements manager
     *
     * @return void
     */
    public static function wpmfAddElementorWidgetCategories($elements_manager)
    {
        $elements_manager->add_category(
            'wpmf',
            array(
                'title' => __('WP Media Folder', 'wpmf'),
                'icon' => 'fa fa-plug'
            )
        );
    }

    /**
     * Creates the extension's main class instance.
     *
     * @return void
     */
    public static function wpmfInitializeDiviExtension()
    {
        require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/divi-widgets/includes/WpmfDivi.php';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
        if (isset($_REQUEST['et_fb']) && (int)$_REQUEST['et_fb'] === 1) {
            require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-pdf-embed.php');
            $pdf = new \WpmfPdfEmbed;
            $pdf->registerScript();
            $pdf->enqueue();

            $enable_gallery = get_option('wpmf_usegellery');
            if (isset($enable_gallery) && (int) $enable_gallery === 1) {
                require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-display-gallery.php');
                $gallery = new \WpmfDisplayGallery;
                $gallery->galleryScripts();
                $gallery->enqueueScript('divi');
            }

            do_action('wpmf_init_gallery_addon_divi');
        }
        wp_enqueue_style(
            'wpmf_divi_css',
            WPMF_PLUGIN_URL . 'assets/css/divi-widgets.css',
            array(),
            WPMF_VERSION,
            'all'
        );
    }

    /**
     * Load Divi 5 integration if Divi 5 Builder is enabled
     *
     * @return void
     */
    public static function loadDivi5Integration()
    {
        if (function_exists('et_builder_d5_enabled') && et_builder_d5_enabled()) {
            $loader = WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/divi5/includes/loader.php';
            if (file_exists($loader)) {
                require_once $loader;
            }
        }
    }

    /**
     * Get Divi 5 gallery addon image size options.
     *
     * @return array<string, array<string, string>>
     */
    public static function wpmfGetDivi5ImageSizeOptions()
    {
        $sizes = apply_filters('image_size_names_choose', array(
            'thumbnail' => __('Thumbnail', 'wpmf'),
            'medium' => __('Medium', 'wpmf'),
            'large' => __('Large', 'wpmf'),
            'full' => __('Full Size', 'wpmf'),
        ));

        $options = array();
        foreach ($sizes as $size => $label) {
            $options[(string) $size] = array(
                'label' => wp_strip_all_tags((string) $label),
            );
        }

        return $options;
    }

    /**
     * Get Divi 5 gallery addon defaults.
     *
     * Matches Divi 4 defaults where they already derive from plugin settings.
     *
     * @return array<string, string>
     */
    public static function wpmfGetDivi5GalleryAddonDefaults()
    {
        $settings = get_option('wpmf_gallery_settings');
        $masonry  = $settings['theme']['masonry_theme'] ?? array();

        return array(
            'galleryId' => '0',
            'theme' => 'masonry',
            'layout' => 'vertical',
            'rowHeight' => '200',
            'aspectRatio' => '1_1',
            'columns' => (string) ($masonry['columns'] ?? 3),
            'numberLines' => '1',
            'size' => (string) ($masonry['size'] ?? 'medium'),
            'targetsize' => (string) ($masonry['targetsize'] ?? 'large'),
            'action' => (string) ($masonry['link'] ?? 'file'),
            'orderby' => (string) ($masonry['orderby'] ?? 'post__in'),
            'order' => (string) ($masonry['order'] ?? 'ASC'),
            'gutterwidth' => '5',
            'galleryNavigation' => 'off',
            'subGalleriesListing' => 'off',
            'galleryImageTags' => 'off',
            'downloadAll' => 'off',
            'borderRadius' => '0',
            'borderStyle' => 'solid',
            'borderWidth' => '0',
            'borderColor' => '#cccccc',
            'enableShadow' => 'off',
            'shadowColor' => '#cccccc',
            'shadowHorizontal' => '0',
            'shadowVertical' => '0',
            'shadowBlur' => '0',
            'shadowSpread' => '0',
            'disableOverlay' => 'off',
            'hoverColor' => '#000000',
            'hoverOpacity' => '0.4',
            'hoverTitlePosition' => 'center_center',
            'hoverTitleSize' => '16',
            'hoverTitleColor' => '#ffffff',
            'hoverDescPosition' => 'none',
            'hoverDescSize' => '14',
            'hoverDescColor' => '#ffffff',
        );
    }

    /**
     * Get Divi 5 gallery addon gallery choices.
     *
     * Matches Divi 4 ordering/filtering for gallery select fields.
     *
     * @return array<int, array<string, string>>
     */
    public static function wpmfGetDivi5GalleryAddonChoices()
    {
        $choices = array(
            array(
                'value' => '0',
                'label' => esc_html__('Select a gallery', 'wpmf'),
            ),
        );

        if (!defined('WPMF_GALLERY_ADDON_TAXO')) {
            return $choices;
        }

        $galleries = get_categories(array(
            'hide_empty' => false,
            'taxonomy' => WPMF_GALLERY_ADDON_TAXO,
            'pll_get_terms_not_translated' => 1,
        ));

        if (is_wp_error($galleries) || empty($galleries)) {
            return $choices;
        }

        if (function_exists('wpmfParentSort')) {
            $galleries = wpmfParentSort($galleries);
        }

        $term_ids = array();
        foreach ($galleries as $gallery) {
            $term_ids[] = (int) $gallery->term_id;
        }

        $gallery_types = array();
        if (defined('WPMF_GALLERY_ADDON_PLUGIN_DIR')
            && file_exists(WPMF_GALLERY_ADDON_PLUGIN_DIR . 'admin/class/helper.php')) {
            require_once WPMF_GALLERY_ADDON_PLUGIN_DIR . 'admin/class/helper.php';
            $helper        = new \WpmfGlrAddonHelper();
            $gallery_types = $helper->getGalleriesType($term_ids);
        }

        foreach ($galleries as $gallery) {
            $gallery_type = $gallery_types[(int) $gallery->term_id] ?? '';
            if ($gallery_type === 'photographer' || $gallery_type === 'archive') {
                continue;
            }

            $depth     = isset($gallery->depth) ? (int) $gallery->depth + 1 : 1;
            $choices[] = array(
                'value' => (string) $gallery->term_id,
                'label' => str_repeat('— ', $depth) . $gallery->name,
            );
        }

        $root = get_term_by('slug', 'photographer-gallery', WPMF_GALLERY_ADDON_TAXO);
        foreach ($galleries as $gallery) {
            $gallery_type = $gallery_types[(int) $gallery->term_id] ?? '';
            if ($gallery_type === 'archive') {
                continue;
            }

            if ($gallery_type === 'photographer') {
                if ($root && (int) $root->term_id === (int) $gallery->term_id) {
                    $label = esc_html__('Photographer Gallery', 'wpmf');
                } else {
                    $depth = isset($gallery->depth) ? (int) $gallery->depth : 0;
                    $label = str_repeat('— ', $depth) . $gallery->name;
                }

                $choices[] = array(
                    'value' => (string) $gallery->term_id,
                    'label' => $label,
                );
            }
        }

        return $choices;
    }

    /**
     * Get Divi 5 gallery addon options map for Divi field metadata.
     *
     * @return array<string, array<string, string>>
     */
    public static function wpmfGetDivi5GalleryAddonOptions()
    {
        $options = array();
        foreach (self::wpmfGetDivi5GalleryAddonChoices() as $choice) {
            $options[$choice['value']] = array(
                'label' => $choice['label'],
            );
        }

        return $options;
    }

    /**
     * Register REST API endpoint for Divi 5 VB block preview
     *
     * @return void
     */
    public static function registerRestRoutes()
    {
        register_rest_route('wp/v2', '/wpmf-block-preview', array(
            'methods'  => 'GET',
            'callback' => function (\WP_REST_Request $request) {
                $shortcode = $request->get_param('shortcode');
                if (empty($shortcode)) {
                    return new \WP_REST_Response(['html' => ''], 200);
                }

                // Strip leading/trailing slashes and only allow wpmf/wpmfpdf shortcodes.
                $shortcode = stripslashes($shortcode);
                if (!preg_match('/^\[wpmf(pdf|filedesign|gallery|_gallery|_single_file)?[\s\]]/i', $shortcode)) {
                    return new \WP_REST_Response(['html' => ''], 400);
                }

                // Enqueue gallery scripts/styles if needed.
                $enable_gallery = get_option('wpmf_usegellery');
                if (isset($enable_gallery) && (int)$enable_gallery === 1) {
                    if (!class_exists('WpmfDisplayGallery')) {
                        require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-display-gallery.php';
                    }
                }

                $html = do_shortcode($shortcode);
                $html = apply_filters('wpmf_load_gif_content', $html);
                return new \WP_REST_Response(['html' => $html], 200);
            },
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ));

        // Used by GalleryAddon edit.tsx to populate the gallery select dropdown.
        register_rest_route('wp/v2', '/wpmf-gallery-addon-list', array(
            'methods'  => 'GET',
            'callback' => function () {
                return new \WP_REST_Response(['galleries' => self::wpmfGetDivi5GalleryAddonChoices()], 200);
            },
            'permission_callback' => function () {
                return current_user_can('edit_posts');
            },
        ));
    }

    /**
     * This action registers all styles(fonts) to be enqueue later
     *
     * @return void
     */
    public static function wpmfVcEnqueueJsCss()
    {
        // load jquery library
        require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-pdf-embed.php');
        $pdf = new \WpmfPdfEmbed;
        $pdf->registerScript();
        $pdf->enqueue();
    }

    /**
     * Get main class
     *
     * @return mixed|WpMediaFolder
     */
    public static function wpmfGetMainClass()
    {
        if (!empty($GLOBALS['wp_media_folder'])) {
            $main_class = $GLOBALS['wp_media_folder'];
        } else {
            require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-helper.php');
            require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-main.php');
            $main_class = new \WpMediaFolder;
        }

        return $main_class;
    }

    /**
     * Register media frame field
     *
     * @param array  $settings Setting details
     * @param string $value    Default value
     *
     * @return string
     */
    public static function wpmfMediaSettingsField($settings, $value)
    {
        return '<div class="' . esc_attr($settings['block_name'] . '_block') . '">'
            . '<input name="' . esc_attr($settings['param_name']) . '" class="wpb_vc_param_value wpb-textinput ' .
            esc_attr($settings['param_name']) . ' ' .
            esc_attr($settings['block_name']) . '_field" type="text" value="' . esc_attr($value) . '" /><button class="' . esc_attr($settings['class']) . '" type="button">' . $settings['button_label'] . '</button>' .
            '</div>';
    }

    /**
     * Register number field
     *
     * @param array  $settings Setting details
     * @param string $value    Default value
     *
     * @return string
     */
    public static function wpmfNumberSettingsField($settings, $value)
    {
        return '<input name="' . esc_attr($settings['param_name']) . '" min="' . esc_attr($settings['min']) . '" max="' . esc_attr($settings['max']) . '" step="' . esc_attr($settings['step']) . '" class="wpb_vc_param_value wpb-textinput ' .
            esc_attr($settings['param_name']) . '_field" type="number" value="' . esc_attr($value) . '" />';
    }

    /**
     * Add bakery widgets
     *
     * @return void
     */
    public static function wpmfVcBeforeInit()
    {
        vc_add_shortcode_param('wpmf_media', [self::class, 'wpmfMediaSettingsField']);
        vc_add_shortcode_param('wpmf_number', [self::class, 'wpmfNumberSettingsField']);
        wp_enqueue_style(
            'wpmf-bakery-style',
            WPMF_PLUGIN_URL . 'assets/css/vc_style.css',
            array(),
            WPMF_VERSION
        );
        wp_enqueue_style(
            'wpmf-bakery-display-gallery-style',
            WPMF_PLUGIN_URL . 'assets/css/display-gallery/style-display-gallery.css',
            array(),
            WPMF_VERSION
        );
        if (is_plugin_active(WP_PLUGIN_DIR . '/wp-media-folder-gallery-addon/wp-media-folder-gallery-addon.php')) {
            wp_enqueue_style(
                'wpmf-bakery-download-all-style',
                WP_PLUGIN_URL . '/wp-media-folder-gallery-addon/assets/css/download_gallery.css',
                array(),
                WPMF_VERSION
            );
        }

        require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/bakery-widgets/PdfEmbed.php';
        $enable_singlefile = get_option('wpmf_option_singlefile');
        if (isset($enable_singlefile) && (int)$enable_singlefile === 1) {
            require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/bakery-widgets/FileDesign.php';
        }

        $enable_gallery = get_option('wpmf_usegellery');
        if (isset($enable_gallery) && (int)$enable_gallery === 1) {
            require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/bakery-widgets/Gallery.php';
        }

        do_action('wpmf_vc_init_gallery_addon');
    }

    /**
     * Create custom field for avada
     *
     * @param array $field_types File types
     *
     * @return mixed
     */
    public static function wpmfAvadaFields($field_types)
    {
        $field_types['wpmf_gallery_select'] = array(
            'wpmf_gallery_select',
            WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/avada-widgets/fields/select_images.php'
        );

        $field_types['wpmf_single_file'] = array(
            'wpmf_single_file',
            WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/avada-widgets/fields/single_file.php'
        );

        $field_types['wpmf_pdf_embed'] = array(
            'wpmf_pdf_embed',
            WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/avada-widgets/fields/pdf_embed.php'
        );

        return $field_types;
    }

    /**
     * Init Avada module
     *
     * @return void
     */
    public static function wpmfTnitAvada()
    {
        if (!defined('AVADA_VERSION') || !defined('FUSION_BUILDER_VERSION')) {
            return;
        }

        add_filter('fusion_builder_fields', [self::class, 'wpmfAvadaFields'], 10, 1);
        require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/avada-widgets/PdfEmbed.php';
        $enable_singlefile = get_option('wpmf_option_singlefile');
        if (isset($enable_singlefile) && (int)$enable_singlefile === 1) {
            require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/avada-widgets/FileDesign.php';
        }

        $enable_gallery = get_option('wpmf_usegellery');
        if (isset($enable_gallery) && (int)$enable_gallery === 1) {
            require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/avada-widgets/Gallery.php';
        }

        if (fusion_is_builder_frame()) {
            add_action('fusion_builder_enqueue_live_scripts', [self::class, 'wpmfAvadaEnqueueSeparateLiveScripts']);
        }
    }

    /**
     * Avada enqueue live scripts
     *
     * @return void
     */
    public static function wpmfAvadaEnqueueSeparateLiveScripts()
    {
        wp_enqueue_script('jquery-masonry');
        $js_folder_url = FUSION_LIBRARY_URL . '/assets' . ((true === FUSION_LIBRARY_DEV_MODE) ? '' : '/min') . '/js';
        wp_enqueue_script('isotope', $js_folder_url . '/library/isotope.js', array(), FUSION_BUILDER_VERSION, true);
        wp_enqueue_script('packery', $js_folder_url . '/library/packery.js', array(), FUSION_BUILDER_VERSION, true);
        wp_enqueue_script('images-loaded', $js_folder_url . '/library/imagesLoaded.js', array(), FUSION_BUILDER_VERSION, true);
        wp_enqueue_script(
            'wpmf-fusion-slick-script',
            WPMF_PLUGIN_URL . 'assets/js/slick/slick.min.js',
            array('jquery'),
            WPMF_VERSION,
            true
        );
        // load jquery library
        require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-pdf-embed.php');
        $pdf = new \WpmfPdfEmbed;
        $pdf->registerScript();
        $pdf->enqueue();
        wp_enqueue_script('wpmf_fusion_view_element', WPMF_PLUGIN_URL . 'class/avada-widgets/js/avada.js', array(), WPMF_VERSION, true);
    }

    /**
     * Initialize GIF loading feature
     *
     * @return void
     */
    public static function initLoadGif()
    {
        require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-load-gif.php');
        new \WpmfLoadGif();
    }

    /**
     * Get cloud folder ID
     *
     * @param string $folder_id Folder ID
     *
     * @return boolean|mixed
     */
    public static function wpmfGetCloudFolderID($folder_id)
    {
        $cloud_id = get_term_meta($folder_id, 'wpmf_drive_root_id', true);
        if (empty($cloud_id)) {
            $cloud_id = get_term_meta($folder_id, 'wpmf_drive_id', true);
        }

        $cloud_type = get_term_meta($folder_id, 'wpmf_drive_type', true);
        if (empty($cloud_id)) {
            if (isset($cloud_type) && $cloud_type !== 'dropbox') {
                return false;
            } else {
                if ($cloud_id === '') {
                    return 'root';
                }
                return $cloud_id;
            }
        } else {
            return $cloud_id;
        }
    }

    /**
     * Get cloud folder type
     *
     * @param string $folder_id Folder ID
     *
     * @return boolean|mixed
     */
    public static function wpmfGetCloudFolderType($folder_id)
    {
        $type = get_term_meta($folder_id, 'wpmf_drive_root_type', true);
        if (empty($type)) {
            $type = get_term_meta($folder_id, 'wpmf_drive_type', true);
        }

        if (empty($type)) {
            return 'local';
        } else {
            return $type;
        }
    }

    /**
     * Get cloud file ID
     *
     * @param string $file_id File ID
     *
     * @return boolean|mixed
     */
    public static function wpmfGetCloudFileID($file_id)
    {
        $cloud_id = get_post_meta($file_id, 'wpmf_drive_id', true);
        if (empty($cloud_id)) {
            return false;
        } else {
            return $cloud_id;
        }
    }

    /**
     * Get cloud file type
     *
     * @param string $file_id File ID
     *
     * @return boolean|mixed
     */
    public static function wpmfGetCloudFileType($file_id)
    {
        $type = get_post_meta($file_id, 'wpmf_drive_type', true);
        if (empty($type)) {
            return 'local';
        } else {
            return $type;
        }
    }

    /**
     * Get IPTC header default
     *
     * @return array
     */
    public static function getIptcHeader()
    {
        $iptcHeaderArray = array
        (
            '2#005'=>'DocumentTitle',
            '2#025'=>'Keywords',
            '2#010'=>'Urgency',
            '2#015'=>'Category',
            '2#020'=>'Subcategories',
            '2#040'=>'SpecialInstructions',
            '2#055'=>'CreationDate',
            '2#080'=>'AuthorByline',
            '2#085'=>'AuthorTitle',
            '2#090'=>'City',
            '2#095'=>'State',
            '2#100'=>'Location',
            '2#101'=>'Country',
            '2#103'=>'OTR',
            '2#105'=>'Headline',
            '2#110'=>'Credit',
            '2#115'=>'PhotoSource',
            '2#116'=>'Copyright'
        );

        return $iptcHeaderArray;
    }

    /**
     * Add style and script
     *
     * @return void
     */
    public static function wpmfAddStyle()
    {
        wp_enqueue_style(
            'wpmf-material-design-iconic-font.min',
            WPMF_PLUGIN_URL . 'assets/css/material-design-iconic-font.min.css',
            array(),
            WPMF_VERSION
        );

        wp_enqueue_script(
            'wpmf-link-dialog',
            WPMF_PLUGIN_URL . 'assets/js/open_link_dialog.js',
            array('jquery'),
            WPMF_VERSION
        );
    }

    /**
     * Register 'wpmf-category' taxonomy
     *
     * @return void
     */
    public static function wpmfRegisterTaxonomyForImages()
    {
        /**
         * Filter to change public param wpmf-category taxonomy
         *
         * @param boolean Toxonomy public status
         *
         * @return boolean
         */
        $public = apply_filters('wpmf_taxonomy_public', false);
        register_taxonomy(
            WPMF_TAXO,
            'attachment',
            array(
                'hierarchical'          => true,
                'show_in_nav_menus'     => false,
                'show_ui'               => false,
                'public'                => $public,
                'update_count_callback' => '_update_generic_term_count',
                'labels'                => array(
                    'name'              => __('WPMF Categories', 'wpmf'),
                    'singular_name'     => __('WPMF Category', 'wpmf'),
                    'menu_name'         => __('WPMF Categories', 'wpmf'),
                    'all_items'         => __('All WPMF Categories', 'wpmf'),
                    'edit_item'         => __('Edit WPMF Category', 'wpmf'),
                    'view_item'         => __('View WPMF Category', 'wpmf'),
                    'update_item'       => __('Update WPMF Category', 'wpmf'),
                    'add_new_item'      => __('Add New WPMF Category', 'wpmf'),
                    'new_item_name'     => __('New WPMF Category Name', 'wpmf'),
                    'parent_item'       => __('Parent WPMF Category', 'wpmf'),
                    'parent_item_colon' => __('Parent WPMF Category:', 'wpmf'),
                    'search_items'      => __('Search WPMF Categories', 'wpmf'),
                )
            )
        );

        $root_id = get_option('wpmf_folder_root_id', false);
        if (!$root_id) {
            $tag = get_term_by('name', 'WP Media Folder Root', WPMF_TAXO);
            if (empty($tag)) {
                $inserted = wp_insert_term('WP Media Folder Root', WPMF_TAXO, array('parent' => 0));
                if (!get_option('wpmf_folder_root_id', false)) {
                    add_option('wpmf_folder_root_id', $inserted['term_id'], '', 'yes');
                }
            } else {
                if (!get_option('wpmf_folder_root_id', false)) {
                    add_option('wpmf_folder_root_id', $tag->term_id, '', 'yes');
                }
            }
        } else {
            $root = get_term_by('id', (int) $root_id, WPMF_TAXO);
            if (!$root) {
                $inserted = wp_insert_term('WP Media Folder Root', WPMF_TAXO, array('parent' => 0));
                if (!is_wp_error($inserted)) {
                    update_option('wpmf_folder_root_id', (int) $inserted['term_id']);
                } else {
                    if (is_numeric($inserted->error_data['term_exists'])) {
                        update_option('wpmf_folder_root_id', $inserted->error_data['term_exists']);
                    }
                }
            }
        }
    }

    /**
     * Filters the attachment URL.
     *
     * @param string  $url           URL for the given attachment.
     * @param integer $attachment_id Attachment post ID.
     *
     * @return mixed
     */
    public static function wpmfGetAttachmentImportUrl($url, $attachment_id)
    {
        $site_path = apply_filters('wpmf_site_path', ABSPATH);
        $path = get_post_meta($attachment_id, 'wpmf_import_path', true);
        if (!empty($path) && file_exists($path)) {
            $url = str_replace($site_path, site_url('/'), $path);
        }

        return $url;
    }

    /**
     * Filters the attachment data prepared for JavaScript.
     *
     * @param array       $response   Array of prepared attachment data.
     * @param WP_Post     $attachment Attachment object.
     * @param array|false $meta       Array of attachment meta data, or false if there is none.
     *
     * @return mixed
     */
    public static function wpmfGetAttachmentImportData($response, $attachment, $meta)
    {
        $site_path = apply_filters('wpmf_site_path', ABSPATH);
        $path = get_post_meta($attachment->ID, 'wpmf_import_path', true);
        if (!empty($path) && file_exists($path)) {
            $url = str_replace($site_path, site_url('/'), $path);
            $response['url'] = $url;
        }

        return $response;
    }

    /**
     * Mailpoet conflict resolver whitelist script
     *
     * @param array $scripts Scripts list
     *
     * @return array
     */
    public static function wpmf_mailpoet_conflict_resolver_whitelist_script($scripts)// phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid, WordPress.NamingConventions.ValidFunctionName.NotCamelCaps, PSR1.Methods.CamelCapsMethodName
    {
        $scripts[] = 'wp-media-folder';
        return $scripts;
    }

    /**
     * Mailpoet conflict resolver whitelist stype
     *
     * @param array $tyles Style list
     *
     * @return array
     */
    public static function wpmf_mailpoet_conflict_resolver_whitelist_style($tyles)// phpcs:ignore WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid, WordPress.NamingConventions.ValidFunctionName.NotCamelCaps, PSR1.Methods.CamelCapsMethodName
    {
        $tyles[] = 'wp-media-folder';
        return $tyles;
    }

    /**
     * Plugin check for updates
     *
     * @param object $update      Update
     * @param array  $plugin_data Plugin data
     * @param string $plugin_file Plugin file
     *
     * @return array|boolean|object
     */
    public static function wpmfPluginCheckForUpdates($update, $plugin_data, $plugin_file)
    {
        if ($plugin_file !== 'wp-media-folder/wp-media-folder.php') {
            return $update;
        }

        if (empty($plugin_data['UpdateURI']) || !empty($update)) {
            return $update;
        }

        $response = wp_remote_get($plugin_data['UpdateURI']);

        if (is_wp_error($response) || empty($response['body'])) {
            return $update;
        }

        $custom_plugins_data = json_decode($response['body'], true);

        $package = null;
        $token = get_site_option('wpmf_license_token');
        if (!empty($token)) {
            $package = $custom_plugins_data['download_url'] . '&token=' . $token . '&siteurl=' . get_option('siteurl');
        }

        return array(
            'version' => $custom_plugins_data['version'],
            'slug' =>  $custom_plugins_data['slug'],
            'package' => $package
        );
    }

    /**
     * Render Video Icon
     *
     * @param integer $attachment_id Attachment ID
     *
     * @return string
     */
    public static function wpmfRenderVideoIcon($attachment_id)
    {
        $remote_url = get_post_meta($attachment_id, 'wpmf_remote_video_link', true);
        if (!empty($remote_url)) {
            return '<i class="material-icons wpmf_remote_video_fe">play_circle_filled</i>';
        }

        return '';
    }

    /**
     * Find image in content
     *
     * @param string $content Content
     *
     * @return string|string[]|null
     */
    public static function wpmfFindImages($content)
    {
        global $wpdb;

        if (!class_exists('DOMDocument')) {
            return $content;
        }

        // Get all attachments that have a remote video link
        $attachments = $wpdb->get_results(
            'SELECT p.ID, pm.meta_value AS remote_url
            FROM ' . $wpdb->posts . ' p
            INNER JOIN ' . $wpdb->postmeta . ' pm ON p.ID = pm.post_id
            WHERE p.post_type = "attachment" AND pm.meta_key = "wpmf_remote_video_link"',
            ARRAY_A
        );

        // Build map basename => [id, remote_url]
        $url_map = [];
        foreach ($attachments as $row) {
            $url = wp_get_attachment_url($row['ID']);
            if ($url) {
                $basename = wp_basename($url);
                $url_map[$basename] = [
                    'id'     => $row['ID'],
                    'remote' => $row['remote_url']
                ];
            }
        }

        if (preg_match_all('/(<img[^>]+>)/i', $content, $matches)) {
            if (!empty($matches[0])) {
                foreach ($matches[0] as $img) {
                    $dom = new \DOMDocument();
                    $dom->loadHTML($img, LIBXML_NOERROR);
                    $imgItem = $dom->getElementsByTagName('img')->item(0);

                    if (empty($imgItem)) {
                        return $content;
                    }

                    $src  = $imgItem->getAttribute('src');
                    $type = $imgItem->getAttribute('data-type');

                    // Skip gallery images
                    if ($type === 'wpmfgalleryimg') {
                        return $content;
                    }

                    // Normalize the original URL (remove -300x200 or similar suffixes)
                    $pathinfo = pathinfo($src);
                    if (strpos($pathinfo['basename'], '-') !== false) {
                        $last   = strripos($src, '-');
                        $last1  = strripos($src, '.');
                        $last2  = strripos($src, 'x');
                        $filename = substr($src, 0, $last);
                        $ext      = substr($src, $last1);
                        $width    = substr($src, $last + 1, ($last2 - $last - 1));
                        $full_src = (!$width) ? $src : $filename . $ext;
                    } else {
                        $full_src = $src;
                    }

                    $basename = wp_basename($full_src);

                    if (isset($url_map[$basename]) && !empty($url_map[$basename]['remote'])) {
                        $remote_video_url = $url_map[$basename]['remote'];

                        wp_enqueue_style(
                            'wpmf-remote-video',
                            WPMF_PLUGIN_URL . 'assets/css/remote_video.css',
                            array(),
                            WPMF_VERSION
                        );

                        list($iframeVideoUrl, $videoType) = self::parseVideoUrl($remote_video_url);

                        if ($videoType === 'dailymotion') {
                            $return = '<div style="left:0;width:100%;height:0;position:relative;padding-bottom:56%;">'
                                    . '<iframe src="' . esc_url($iframeVideoUrl) . '" style="top:0;left:0;width:100%;height:100%;position:absolute;border:0;" allowfullscreen allow="encrypted-media"></iframe>'
                                    . '</div>';
                        } else {
                            $return = '<figure class="wpmf-block-embed"><div class="wpmf-block-embed__wrapper">'
                                    . '<iframe src="' . esc_url($iframeVideoUrl) . '" frameborder="0" allowfullscreen></iframe>'
                                    . '</div></figure>';
                        }

                        $content = str_replace($img, $return, $content);
                    }
                }
            }
        }

        // otherwise returns the database content
        return $content;
    }

    /**
     * Find remote video thumbnail then replace it by video code
     *
     * @param array $images Image info
     *
     * @return mixed|string
     */
    public static function wpmfDetectYTImages($images)
    {
        $return = $images[0];
        require_once WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-helper.php';
        // Get the image ID from the unique class added by insert to editor: "wp-image-ID"
        if (preg_match('/wp-image-([0-9]+)/', $return, $match)) {
            $remote_video_url = get_post_meta($match[1], 'wpmf_remote_video_link', true);
            if (!empty($remote_video_url)) {
                list($iframeVideoUrl, $videoType) = self::parseVideoUrl($remote_video_url);
                if ($videoType === 'dailymotion') {
                    $return = '<div style="left: 0; width: 100%; height: 0; position: relative; padding-bottom: 56%;"><iframe src="' . $iframeVideoUrl . '" style="top: 0; left: 0; width: 100%; height: 100%; position: absolute; border: 0;" allowfullscreen allow="encrypted-media;"></iframe></div>';
                } else {
                    $return = '<iframe src="' . $iframeVideoUrl . '" frameborder="0"  allowFullScreen></iframe>';
                }
            }
        }

        return $return;
    }

    /**
     * Add to the queue
     *
     * @param array   $datas        Datas details
     * @param array   $responses    Responses details
     * @param boolean $check_status Check status
     *
     * @return void
     */
    public static function wpmfAddToQueue($datas = array(), $responses = array(), $check_status = false)
    {
        $wpmfQueue = \Joomunited\Queue\JuMainQueue::getInstance('wpmf');
        $row = $wpmfQueue->checkQueueExist(json_encode($datas));
        $exist = false;
        if (!$row) {
            $exist = false;
        } else {
            if (!$check_status) {
                if ((int)$row->status === 0) {
                    $exist = true;
                }
            } else {
                $exist = true;
            }
        }

        if (!$exist) {
            $wpmfQueue->addToQueue($datas, $responses);
        }
    }

    /**
     * Transition post status
     *
     * @param string $new_status New status
     * @param string $old_status Old status
     * @param object $post       Post object
     *
     * @return void
     */
    public static function wpmfTransitionPostStatus($new_status, $old_status, $post)
    {
        if ($post->post_type === 'post') {
            if ($new_status !== 'auto-draft' && $old_status === 'auto-draft') {
                $_thumbnail_id = get_post_meta($post->ID, '_thumbnail_id', true);
                if (empty($_thumbnail_id)) {
                    $default_featured_image_type = self::wpmfGetOption('default_featured_image_type');
                    // Get the Default Featured Image ID.
                    $default_featured_image = 0;
                    if ($default_featured_image_type === 'fixed') {
                        $default_featured_image = self::wpmfGetOption('default_featured_image');
                    } else {
                        $featured_image_folder = self::wpmfGetOption('featured_image_folder');
                        $args = array(
                            'posts_per_page' => 1,
                            'post_type' => 'attachment',
                            'post_status' => 'any',
                            'post_mime_type' => 'image',
                            'fields' => 'ids',
                            'orderby' => 'rand',
                            'tax_query' => array(
                                array(
                                    'taxonomy' => WPMF_TAXO,
                                    'field' => 'term_id',
                                    'terms' => $featured_image_folder,
                                    'operator' => 'IN',
                                    'include_children' => false
                                )
                            )
                        );

                        $query = new WP_Query($args);
                        $ids = $query->get_posts();
                        if (!empty($ids)) {
                            $default_featured_image = $ids[0];
                        }
                    }

                    if (!empty($default_featured_image)) {
                        update_post_meta($post->ID, '_thumbnail_id', $default_featured_image);
                    }
                }
            }
        }
    }

    /**
     * Download file
     *
     * @return void
     */
    public static function wpmfDownloadFile()
    {
        if (!empty($_GET['act']) && $_GET['act'] === 'wpmf_download_file') {
            if (empty($_GET['wpmf_nonce'])
                || !wp_verify_nonce($_GET['wpmf_nonce'], 'wpmf_nonce')) {
                die();
            }

            $file_id = (isset($_GET['id'])) ? intval($_GET['id']) : 0;
            if (!empty($file_id)) {
                $path = get_attached_file($file_id);
                if (file_exists($path)) {
                    $types = wp_check_filetype($path);
                    header('Content-Description: File Transfer');
                    header('Content-Type: ' . $types['type']);
                    header('Content-Disposition: attachment; filename="' . basename($path) . '"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($path));
                    readfile($path);
                    exit;
                } else {
                    $drive_type = get_post_meta($file_id, 'wpmf_drive_type', true);
                    if (!empty($drive_type)) {
                        if (!is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) {
                            die();
                        }
                        $drive_id = get_post_meta($file_id, 'wpmf_drive_id', true);
                        if (!empty($drive_id)) {
                            switch ($drive_type) {
                                case 'dropbox':
                                    require_once WPMFAD_PLUGIN_DIR . '/class/wpmfAddonDropboxAdmin.php';
                                    include_once WPMFAD_PLUGIN_DIR . '/class/includes/mime-types.php';
                                    $library = new \WpmfAddonDropboxAdmin;
                                    $dropbox = $library->getAccount();
                                    $getFile = $dropbox->getMetadata($drive_id);
                                    $pinfo = pathinfo($getFile['path_lower']);
                                    $tempfile = $pinfo['basename'];
                                    $contenType = getMimeType($pinfo['extension']);
                                    header('Content-Description: File Transfer');
                                    header('Content-Type: ' . $contenType);
                                    header('Content-Disposition: attachment; filename="' . basename($tempfile) . '"');
                                    header('Expires: 0');
                                    header('Cache-Control: must-revalidate');
                                    header('Pragma: public');
                                    header('Content-Length: ' . $getFile['size']);
                                    $content = $dropbox->get_filecontent($getFile['path_lower']);
                                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- String is escaped
                                    echo $content;
                                    break;
                                case 'onedrive':
                                    require_once WPMFAD_PLUGIN_DIR . '/class/wpmfAddonOneDriveAdmin.php';
                                    $library = new \WpmfAddonOneDrive;
                                    $library->getContentFile($drive_id, 1);
                                    break;
                                case 'onedrive_business':
                                    require_once WPMFAD_PLUGIN_DIR . '/class/wpmfAddonOneDriveBusinessAdmin.php';
                                    $library = new \WpmfAddonOneDriveBusinessAdmin;
                                    $library->getContentFile($drive_id, 1);
                                    break;
                                case 'google_drive':
                                    require_once WPMFAD_PLUGIN_DIR . '/class/wpmfAddonGoogleAdmin.php';
                                    include_once WPMFAD_PLUGIN_DIR . '/class/includes/mime-types.php';
                                    $library = new \WpmfAddonGoogle;
                                    $config = get_option('_wpmfAddon_cloud_config');
                                    $client = $library->getClient($config);
                                    $service = new \WpmfGoogle_Service_Drive($client);
                                    $file = $service->files->get($drive_id, array('fields' => 'id,parents,name,size,mimeType,fileExtension,thumbnailLink', 'supportsAllDrives' => $library->isTeamDrives($config)));
                                    $contenType = getMimeType($file->fileExtension);
                                    header('Content-Description: File Transfer');
                                    header('Content-Type: ' . $contenType);
                                    header('Content-Disposition: attachment; filename="' . basename($file->name) . '"');
                                    header('Expires: 0');
                                    header('Cache-Control: must-revalidate');
                                    header('Pragma: public');
                                    header('Content-Length: ' . $file->size);
                                    $content = $service->files->get($drive_id, array('alt' => 'media', 'supportsAllDrives' => $library->isTeamDrives($config)));
                                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- String is escaped
                                    echo $content;
                                    break;
                                case 'nextcloud':
                                    require_once WPMFAD_PLUGIN_DIR . '/class/wpmfAddonNextCloudAdmin.php';
                                    include_once WPMFAD_PLUGIN_DIR . '/class/includes/mime-types.php';
                                    $library = new \WpmfAddonNextcloudAdmin;
                                    $path = get_post_meta($file_id, 'wpmf_drive_path', true);
                                    $valid_path = $library->getValidPath($path);
                                    $params = get_option('_wpmfAddon_nextcloud_config');
                                    $isConnected = $library->isConnected();
                                    if ($isConnected) {
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $library->davUrl . $valid_path);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                        curl_setopt($ch, CURLOPT_USERPWD, $params['username'] . ':' . $params['password']);
                                        $content = curl_exec($ch);
                                        curl_close($ch);

                                        if ($content) {
                                            $info = pathinfo($path);
                                            $meta = get_post_meta($file_id, '_wp_attachment_metadata', true);
                                            $extension = strtolower($info['extension']);
                                            $contenType = getMimeType($extension);
                                            header('Content-Description: File Transfer');
                                            header('Content-Type: ' . $contenType);
                                            header('Content-Disposition: attachment; filename="' . basename($path) . '"');
                                            header('Expires: 0');
                                            header('Cache-Control: must-revalidate');
                                            header('Pragma: public');
                                            header('Content-Length: ' . $meta['filesize']);
                                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- String is escaped
                                            echo $content;
                                        }
                                    }
                                    break;
                                case 'owncloud':
                                    require_once WPMFAD_PLUGIN_DIR . '/class/wpmfAddonOwnCloudAdmin.php';
                                    include_once WPMFAD_PLUGIN_DIR . '/class/includes/mime-types.php';
                                    $library = new \WpmfAddonOwncloudAdmin;
                                    $path = get_post_meta($file_id, 'wpmf_drive_path', true);
                                    $valid_path = $library->getValidPath($path);
                                    $params = get_option('_wpmfAddon_owncloud_config');
                                    $isConnected = $library->isConnected();
                                    if ($isConnected) {
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $library->davUrl . $valid_path);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                        curl_setopt($ch, CURLOPT_USERPWD, $params['username'] . ':' . $params['password']);
                                        $content = curl_exec($ch);
                                        curl_close($ch);

                                        if ($content) {
                                            $info = pathinfo($path);
                                            $meta = get_post_meta($file_id, '_wp_attachment_metadata', true);
                                            $extension = strtolower($info['extension']);
                                            $contenType = getMimeType($extension);
                                            header('Content-Description: File Transfer');
                                            header('Content-Type: ' . $contenType);
                                            header('Content-Disposition: attachment; filename="' . basename($path) . '"');
                                            header('Expires: 0');
                                            header('Cache-Control: must-revalidate');
                                            header('Pragma: public');
                                            header('Content-Length: ' . $meta['filesize']);
                                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- String is escaped
                                            echo $content;
                                        }
                                    }
                                    break;
                            }
                            exit;
                        }
                    }
                }
            }
        }
    }

    /**
     * Register gallery taxonomy
     *
     * @return void
     */
    public static function wpmfTagRegisterTaxonomy()
    {
        if (!taxonomy_exists('wpmf_tag')) {
            register_taxonomy(
                'wpmf_tag',
                'attachment',
                array(
                    'hierarchical' => false,
                    'show_in_nav_menus' => false,
                    'show_admin_column' => true,
                    'show_ui' => true,
                    'public' => true,
                    'update_count_callback' => '_update_generic_term_count',
                    'labels' => array(
                        'name' => __('Tags', 'wpmf'),
                        'singular_name' => __('Tags', 'wpmf'),
                        'menu_name' => __('Media Folder Tags', 'wpmf'),
                        'all_items' => __('All Tags', 'wpmf'),
                        'edit_item' => __('Edit Tag', 'wpmf'),
                        'view_item' => __('View Tag', 'wpmf'),
                        'update_item' => __('Update Tag', 'wpmf'),
                        'add_new_item' => __('Add New Tag', 'wpmf'),
                        'new_item_name' => __('New Tag Name', 'wpmf'),
                        'parent_item' => __('Parent Tag', 'wpmf'),
                        'parent_item_colon' => __('Parent Tag:', 'wpmf'),
                        'search_items' => __('Search Tag', 'wpmf'),
                    )
                )
            );
        }
    }

    /**
     * Initialize WooCommerce watermark integration
     *
     * @return void
     */
    public static function initWooCommerceIntegration()
    {
        // Ensure WooCommerce is active
        if (!class_exists('WooCommerce')) {
            return;
        }

        $option_image_watermark     = get_option('wpmf_option_image_watermark');
        $option_watermark_only_woo  = get_option('wpmf_watermark_only_woo');

        if (!empty($option_image_watermark) && (int) $option_image_watermark === 1 && !empty($option_watermark_only_woo) && (int) $option_watermark_only_woo === 1) {
            add_action('woocommerce_new_product', [__CLASS__, 'wpmfCreateWatermarkAfterProductSave'], 10, 1);
            add_action('woocommerce_update_product', [__CLASS__, 'wpmfCreateWatermarkAfterProductSave'], 10, 1);
        }
    }

    /**
     * Create watermark image after product creation or product update
     *
     * @param integer $product_id Current product ID.
     *
     * @return void
     */
    public static function wpmfCreateWatermarkAfterProductSave($product_id)
    {
        $product = wc_get_product($product_id);
        $main_image_id = get_post_thumbnail_id($product_id);
        $gallery_image_ids = $product->get_gallery_image_ids();

        $all_image_ids = array_merge([$main_image_id], $gallery_image_ids);
        
        require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-image-watermark.php');
        $wpmfwatermark = new \WpmfWatermark();

        foreach ($all_image_ids as $image_id) {
            $metadata   = wp_get_attachment_metadata($image_id);
            $wpmfwatermark->createWatermarkImage($metadata, $image_id, true);
        }
    }

    /**
     * Replace Google Drive video and audio blocks with responsive iframes.
     *
     * @param string $content Post content.
     *
     * @return string
     */
    public static function replaceVideoAndAudioGoogleDriveWithIframe($content)
    {
        // Replace Google Drive video
        $content = preg_replace_callback(
            '#<video[^>]+src=["\']([^"\']*drive\.google\.com[^"\']*)["\'][^>]*>.*?</video>#is',
            function ($matches) {
                $video_src = html_entity_decode($matches[1]);

                if (preg_match('~/d/([^/]+)~', $video_src, $m) || preg_match('/id=([^&]+)/', $video_src, $m)) {
                    $file_id = $m[1];

                    return '<iframe src="https://drive.google.com/file/d/' . esc_attr($file_id) . '/preview"
                        style="width:100%; aspect-ratio:16/9; border:none; display:block"
                        allow="autoplay"
                        allowfullscreen></iframe>';
                }

                return $matches[0];
            },
            $content
        );


        // Replace Google Drive audio
        $content = preg_replace_callback(
            '#<figure class="wp-block-audio">.*?<audio[^>]+src=["\']([^"\']*drive\.google\.com[^"\']*)["\'][^>]*>.*?</audio>.*?</figure>#is',
            function ($matches) {
                $audio_src = html_entity_decode($matches[1]);
                if (preg_match('/id=([^&]+)/', $audio_src, $id_match)) {
                    $file_id = $id_match[1];
                    return '<iframe src="https://drive.google.com/file/d/' . esc_attr($file_id) . '/preview" style="width:100%; height:130px; border:none; display:flex" allow="autoplay" allowfullscreen></iframe>';
                }
                return $matches[0];
            },
            $content
        );

        return $content;
    }

    /**
     * Format a number using a space as thousands separator if >= 10000,
     * otherwise return as plain number without separator.
     *
     * @param integer $number The number to format.
     *
     * @return string The formatted number string.
     */
    public static function wpmfCustomNumberFormat($number)
    {
        return $number >= 10000
            ? number_format($number, 0, '', ' ')
            : number_format($number, 0, '', '');
    }

    /**
     * Load import Enhanced Media Library categories script
     *
     * @param array  $categories    External categories list
     * @param string $category_name Category name
     *
     * @return array
     */
    public static function loadImportExternalCatsScript($categories, $category_name = '')
    {
        $attachment_terms_order = array();
        $attachment_terms[]       = array(
            'id'        => 0,
            'label'     => esc_html__('Media Library', 'wpmf'),
            'parent_id' => 0
        );
        $attachment_terms_order[] = '0';
        foreach ($categories as $category) {
            if ((int)$category->parent === -1) {
                $parent = 0;
            } else {
                $parent = $category->parent;
            }
            $attachment_terms[$category->term_id] = array(
                'id'            => $category->term_id,
                'label'         => $category->name,
                'parent_id'     => $parent,
                'depth'         => $category->depth
            );
            $attachment_terms_order[] = $category->term_id;
        }

        if ($category_name === 'filebird') {
            $vars['filebird_categories'] = $attachment_terms;
            $vars['filebird_categories_order'] = $attachment_terms_order;
        }

        if ($category_name === 'real_media_library') {
            $vars['rml_categories'] = $attachment_terms;
            $vars['rml_categories_order'] = $attachment_terms_order;
        }

        if ($category_name === 'media_category') {
            $vars['media_category_categories'] = $attachment_terms;
            $vars['media_category_categories_order'] = $attachment_terms_order;
        }

        if ($category_name === 'media_folder') {
            $vars['mf_categories'] = $attachment_terms;
            $vars['mf_categories_order'] = $attachment_terms_order;
        }

        if ($category_name === 'happyfiles_category') {
            $vars['happy_categories'] = $attachment_terms;
            $vars['happy_categories_order'] = $attachment_terms_order;
        }

        return $vars;
    }

    /**
     * Move file compatiple with WPML plugin
     *
     * @param integer $id               Id of attachment
     * @param integer $current_category Id of current folder
     * @param integer $id_category      Id of new folder
     *
     * @return void
     */
    public static function moveFileWpml($id, $current_category, $id_category)
    {
        if (is_plugin_active('polylang/polylang.php') || is_plugin_active('polylang-pro/polylang.php')) {
            global $polylang;
            $polylang_current = $polylang->curlang;
            foreach ($polylang->model->get_languages_list() as $language) {
                if (!empty($polylang_current) && (int) $language->term_id === (int) $polylang_current->term_id) {
                    continue;
                }
                $translation_id = $polylang->model->post->get_translation($id, $language);
                if (($translation_id) && (int) $translation_id !== (int) $id) {
                    if ($current_category !== 'no') {
                        wp_remove_object_terms(
                            (int) $translation_id,
                            (int) $current_category,
                            WPMF_TAXO
                        );
                    } else {
                        wp_set_object_terms(
                            (int) $translation_id,
                            (int) $id_category,
                            WPMF_TAXO,
                            true
                        );
                    }

                    if ($id_category !== 'no') {
                        wp_set_object_terms(
                            (int) $translation_id,
                            (int) $id_category,
                            WPMF_TAXO,
                            true
                        );

                        /**
                         * Set attachmnent folder after moving file with WPML plugin
                         *
                         * @param integer Attachment ID
                         * @param integer Target folder
                         * @param array   Extra informations
                         *
                         * @ignore Hook already documented
                         */
                        do_action('wpmf_attachment_set_folder', $translation_id, $id_category, array('trigger' => 'move_attachment'));
                    } else {
                        wp_remove_object_terms(
                            (int) $translation_id,
                            (int) $current_category,
                            WPMF_TAXO
                        );
                    }

                    // reset order of file
                    update_post_meta(
                        (int) $translation_id,
                        'wpmf_order',
                        0
                    );
                }
            }
        } elseif (defined('ICL_SITEPRESS_VERSION') && ICL_SITEPRESS_VERSION) {
            global $sitepress;
            $trid = $sitepress->get_element_trid($id, 'post_attachment');
            if ($trid) {
                $translations = $sitepress->get_element_translations($trid, 'post_attachment', true, true, true);
                foreach ($translations as $translation) {
                    if ((int) $translation->element_id !== (int) $id) {
                        if ($current_category !== 'no') {
                            wp_remove_object_terms(
                                (int) $translation->element_id,
                                (int) $current_category,
                                WPMF_TAXO
                            );
                        } else {
                            wp_set_object_terms(
                                (int) $translation->element_id,
                                (int) $id_category,
                                WPMF_TAXO,
                                true
                            );
                        }

                        if ($id_category !== 'no') {
                            wp_set_object_terms(
                                (int) $translation->element_id,
                                (int) $id_category,
                                WPMF_TAXO,
                                true
                            );

                            /**
                             * Set attachmnent folder after moving file with WPML plugin
                             *
                             * @param integer Attachment ID
                             * @param integer Target folder
                             * @param array   Extra informations
                             *
                             * @ignore Hook already documented
                             */
                            do_action('wpmf_attachment_set_folder', $translation->element_id, $id_category, array('trigger' => 'move_attachment'));
                        } else {
                            wp_remove_object_terms(
                                (int) $translation->element_id,
                                (int) $current_category,
                                WPMF_TAXO
                            );
                        }

                        // reset order of file
                        update_post_meta(
                            (int) $translation->element_id,
                            'wpmf_order',
                            0
                        );
                    }
                }
            }
        }
    }

    /**
     * Check user full access
     *
     * @return boolean
     */
    public static function checkUserFullAccess()
    {
        global $current_user;
        $wpmf_active_media = get_option('wpmf_active_media');
        $user_roles        = $current_user->roles;
        $role              = array_shift($user_roles);
        if (isset($wpmf_active_media) && (int) $wpmf_active_media === 1
            && $role !== 'administrator' && !current_user_can('administrator') && (!in_array($current_user->ID, self::$user_full_access_id) || self::$user_full_access_id === 0) && !current_user_can('wpmf_full_access')) {
            $user_full_access = false;
        } else {
            $user_full_access = true;
        }

        $user_full_access = apply_filters('wpmf_user_full_access', $user_full_access, $role);
        return $user_full_access;
    }

    /**
     * Update modify file when sync
     *
     * @param integer $id        ID of file
     * @param string  $filepath  Old file path
     * @param string  $form_file New file path
     *
     * @return void
     */
    public static function replace($id, $filepath, $form_file)
    {
        $upload_dir = wp_upload_dir();
        $metadata = wp_get_attachment_metadata($id);
        $infopath = pathinfo($filepath);
        $allowedImageTypes = array('gif', 'jpg', 'png', 'bmp', 'pdf');
        unlink($filepath);
        if (in_array($infopath['extension'], $allowedImageTypes)) {
            if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
                foreach ($metadata['sizes'] as $size => $sizeinfo) {
                    $intermediate_file = str_replace(basename($filepath), $sizeinfo['file'], $filepath);
                    // This filter is documented in wp-includes/functions.php
                    $intermediate_file = apply_filters('wp_delete_file', $intermediate_file);
                    $link = path_join(
                        $upload_dir['basedir'],
                        $intermediate_file
                    );
                    if (file_exists($link) && is_writable($link)) {
                        unlink($link);
                    }
                }
            }
        }

        $upload = copy($form_file, $filepath);
        if ($upload) {
            update_post_meta($id, 'wpmf_size', filesize($filepath));
            if ($infopath['extension'] === 'pdf') {
                self::createPdfThumbnail($filepath);
            }

            if (in_array($infopath['extension'], $allowedImageTypes)) {
                if ($infopath['extension'] !== 'pdf') {
                    $actual_sizes_array = getimagesize($filepath);
                    $metadata['width']  = $actual_sizes_array[0];
                    $metadata['height'] = $actual_sizes_array[1];
                    self::createThumbs($filepath, $infopath['extension'], $metadata, $id);
                }
            }
        }
    }

    /**
     * Create Pdf Thumbnail
     *
     * @param string $filepath File path
     *
     * @return void
     */
    public static function createPdfThumbnail($filepath)
    {
        $metadata       = array();
        $fallback_sizes = array(
            'thumbnail',
            'medium',
            'large',
        );

        /**
         * Filters the image sizes generated for non-image mime types.
         *
         * @param array $fallback_sizes An array of image size names.
         * @param array $metadata       Current attachment metadata.
         */
        $fallback_sizes = apply_filters('fallback_intermediate_image_sizes', $fallback_sizes, $metadata);

        $sizes                      = array();
        $_wp_additional_image_sizes = wp_get_additional_image_sizes();

        foreach ($fallback_sizes as $s) {
            if (isset($_wp_additional_image_sizes[$s]['width'])) {
                $sizes[$s]['width'] = intval($_wp_additional_image_sizes[$s]['width']);
            } else {
                $sizes[$s]['width'] = get_option($s . '_size_w');
            }

            if (isset($_wp_additional_image_sizes[$s]['height'])) {
                $sizes[$s]['height'] = intval($_wp_additional_image_sizes[$s]['height']);
            } else {
                $sizes[$s]['height'] = get_option($s . '_size_h');
            }

            if (isset($_wp_additional_image_sizes[$s]['crop'])) {
                $sizes[$s]['crop'] = $_wp_additional_image_sizes[$s]['crop'];
            } else {
                // Force thumbnails to be soft crops.
                if ('thumbnail' !== $s) {
                    $sizes[$s]['crop'] = get_option($s . '_crop');
                }
            }
        }

        // Only load PDFs in an image editor if we're processing sizes.
        if (!empty($sizes)) {
            $editor = wp_get_image_editor($filepath);

            if (!is_wp_error($editor)) { // No support for this type of file
                /*
                 * PDFs may have the same file filename as JPEGs.
                 * Ensure the PDF preview image does not overwrite any JPEG images that already exist.
                 */
                $dirname      = dirname($filepath) . '/';
                $ext          = '.' . pathinfo($filepath, PATHINFO_EXTENSION);
                $preview_file = $dirname . wp_unique_filename($dirname, wp_basename($filepath, $ext) . '-pdf.jpg');

                $uploaded = $editor->save($preview_file, 'image/jpeg');
                unset($editor);

                // Resize based on the full size image, rather than the source.
                if (!is_wp_error($uploaded)) {
                    $editor = wp_get_image_editor($uploaded['path']);
                    unset($uploaded['path']);

                    if (!is_wp_error($editor)) {
                        $metadata['sizes']         = $editor->multi_resize($sizes);
                        $metadata['sizes']['full'] = $uploaded;
                    }
                }
            }
        }
    }

    /**
     * Create thumbnail after replace
     *
     * @param string  $filepath  Physical path of file
     * @param string  $extimage  Extension of file
     * @param array   $metadata  Meta data of file
     * @param integer $post_id   ID of file
     * @param boolean $isOffload Check file is AWS
     *
     * @return void
     */
    public static function createThumbs($filepath, $extimage, $metadata, $post_id, $isOffload = false)
    {
        if (!file_exists($filepath)) {
            return;
        }

        $real_type = exif_imagetype($filepath);
        switch ($real_type) {
            case IMAGETYPE_JPEG:
                $extimage = 'jpg';
                break;
            case IMAGETYPE_PNG:
                $extimage = 'png';
                break;
            case IMAGETYPE_GIF:
                $extimage = 'gif';
                break;
            case IMAGETYPE_WEBP:
                $extimage = 'webp';
                break;
            default:
                return;
        }
        
        if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
            $uploadpath = wp_upload_dir();
            foreach ($metadata['sizes'] as $size => $sizeinfo) {
                $intermediate_file = str_replace(basename($filepath), $sizeinfo['file'], $filepath);
                if ($isOffload) {
                    $filepath = apply_filters('wp_get_attachment_url', $filepath, $post_id);
                    $physicalPath = get_attached_file($post_id);
                    $intermediate_file = str_replace(basename($physicalPath), $sizeinfo['file'], $physicalPath);
                }

                // load image and get image size
                list($width, $height) = getimagesize($filepath);
                $new_width = $sizeinfo['width'];
                $new_height = floor($height * ($sizeinfo['width'] / $width));
                $tmp_img = imagecreatetruecolor($new_width, $new_height);

                imagealphablending($tmp_img, false);
                imagesavealpha($tmp_img, true);

                switch ($extimage) {
                    case 'jpeg':
                    case 'jpg':
                        $source = imagecreatefromjpeg($filepath);
                        break;

                    case 'png':
                        $source = imagecreatefrompng($filepath);
                        break;

                    case 'gif':
                        $source = imagecreatefromgif($filepath);
                        break;

                    case 'bmp':
                        $source = imagecreatefromwbmp($filepath);
                        break;

                    case 'webp':
                        if (function_exists('imagecreatefromwebp')) {
                            $source = imagecreatefromwebp($filepath);
                        } else {
                            $source = imagecreatefromstring(readfile($filepath));
                        }
                        break;

                    default:
                        $source = imagecreatefromjpeg($filepath);
                }

                if ($source === false) {
                    $img_data = file_get_contents($filepath);
                    if ($img_data !== false) {
                        $source = imagecreatefromstring($img_data);
                    }
                }

                if ($source === false) {
                    continue;
                }

                imagealphablending($source, true);
                imagecopyresampled($tmp_img, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                switch ($extimage) {
                    case 'jpeg':
                    case 'jpg':
                        imagejpeg($tmp_img, path_join($uploadpath['basedir'], $intermediate_file), 100);
                        break;

                    case 'png':
                        imagepng($tmp_img, path_join($uploadpath['basedir'], $intermediate_file), 9);
                        break;

                    case 'gif':
                        imagegif($tmp_img, path_join($uploadpath['basedir'], $intermediate_file));
                        break;

                    case 'bmp':
                        imagewbmp($tmp_img, path_join($uploadpath['basedir'], $intermediate_file));
                        break;
                    case 'webp':
                        imagewebp($tmp_img, path_join($uploadpath['basedir'], $intermediate_file));
                        break;
                }

                $metadata[$size]['width'] = $new_width;
                $metadata[$size]['width'] = $new_height;
                wp_update_attachment_metadata($post_id, $metadata);

                if ($isOffload) {
                    $physicalPath = path_join($uploadpath['basedir'], $intermediate_file);
                    if (file_exists($physicalPath)) {
                        $awsS3infos = get_post_meta($post_id, 'wpmf_awsS3_info', true);
                        if (isset($awsS3infos['Key'])) {
                            $intermediate_file = str_replace(basename($awsS3infos['Key']), $sizeinfo['file'], $awsS3infos['Key']);
                            apply_filters('wpmfAddonReplaceFileOffload', file_get_contents($physicalPath), $intermediate_file);
                        }
                        unlink($physicalPath);
                    }
                }
            }
        } else {
            wp_update_attachment_metadata($post_id, $metadata);
        }
    }

    /**
     * Save pptc metadata
     *
     * @param integer $enable       Enable or disable option
     * @param integer $image_id     ID of image
     * @param string  $path         Path of image
     * @param array   $allow_fields Include fields
     * @param string  $title        Title of image
     * @param string  $mime_type    Mime type
     *
     * @return void
     */
    public static function saveIptcMetadata($enable, $image_id, $path, $allow_fields, $title, $mime_type)
    {
        $iptcMeta = array();
        // update alt
        if ((int) $enable === 1 && strpos($mime_type, 'image') !== false && $title !== '' && !empty($allow_fields['alt'])) {
            update_post_meta($image_id, '_wp_attachment_image_alt', $title);
        }

        if ((int)$enable === 1 && strpos($mime_type, 'image') !== false) {
            $size = getimagesize($path, $info);
            if (!empty($allow_fields['2#105']) && $title !== '') {
                $iptcMeta['2#105'] = array($title);
            }

            if (isset($info['APP13'])) {
                $iptc = iptcparse($info['APP13']);
                if (!empty($iptc)) {
                    foreach ($iptc as $code => $iptcValue) {
                        if (!empty($allow_fields[$code])) {
                            $iptcMeta[$code] = $iptcValue;
                        }
                    }

                    update_post_meta($image_id, 'wpmf_iptc', $iptcMeta);
                }
            }
        }
    }

    /**
     * Sort parents before children
     * http://stackoverflow.com/questions/6377147/sort-an-array-placing-children-beneath-parents
     *
     * @param array   $objects      List folder
     * @param integer $enable_count Enable count
     * @param array   $result       Result
     * @param integer $parent       Parent of folder
     * @param integer $depth        Depth of folder
     *
     * @return array           output
     */
    public static function parentSort(array $objects, $enable_count = false, array &$result = array(), $parent = 0, $depth = 0)
    {
        foreach ($objects as $key => $object) {
            if ((int)$object->parent === -1) {
                $pr = 0;
            } else {
                $pr = $object->parent;
            }

            if ((int) $pr === (int) $parent) {
                if ($enable_count) {
                    $object->files_count = self::getCountFiles($object->term_id);
                    $object->count_all = 0;
                }
                $object->depth = $depth;
                array_push($result, $object);
                unset($objects[$key]);
                self::parentSort($objects, $enable_count, $result, $object->term_id, $depth + 1);
            }
        }
        return $result;
    }

    /**
     * Get count files in folder
     *
     * @param integer $term_id Id of folder
     *
     * @return integer
     */
    public static function getCountFiles($term_id)
    {
        global $wpdb;

        $post_type = 'attachment';
        $params    = [$post_type, (int) $term_id];

        // Base SQL
        $sql = '
            SELECT COUNT(DISTINCT p.ID)
            FROM ' . $wpdb->posts . ' p
            INNER JOIN ' . $wpdb->term_relationships . ' tr ON p.ID = tr.object_id
            INNER JOIN ' . $wpdb->term_taxonomy . ' tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE p.post_type = %s
            AND p.post_status IN ("publish", "inherit")
            AND tt.term_id = %d
        ';

        // WPML support
        if (defined('ICL_SITEPRESS_VERSION') && ICL_SITEPRESS_VERSION) {
            global $sitepress;
            $settings = $sitepress->get_settings();
            if (!empty($settings['custom_posts_sync_option']['attachment'])) {
                $current_lang = $sitepress->get_current_language();
                $sql .= '
                    AND EXISTS (
                        SELECT 1
                        FROM ' . $wpdb->prefix . 'icl_translations wpml
                        WHERE wpml.element_id = p.ID
                        AND wpml.element_type = "post_attachment"
                        AND wpml.language_code = %s
                    )
                ';
                $params[] = $current_lang;
            }
        }

        // Polylang support
        if (is_plugin_active('polylang/polylang.php') || is_plugin_active('polylang-pro/polylang.php')) {
            global $polylang;
            if ($polylang->curlang && $polylang->model->is_translated_post_type('attachment')) {
                $lang_slug = $polylang->curlang->slug;
                $sql .= '
                    AND EXISTS (
                        SELECT 1
                        FROM ' . $wpdb->term_relationships . ' tr2
                        INNER JOIN ' . $wpdb->term_taxonomy . ' tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
                        INNER JOIN ' . $wpdb->terms . ' t2 ON tt2.term_id = t2.term_id
                        WHERE tr2.object_id = p.ID
                        AND tt2.taxonomy = "language"
                        AND t2.slug = %s
                    )
                ';
                $params[] = $lang_slug;
            }
        }
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- dynamic SQL built securely above with placeholders
        return (int) $wpdb->get_var($wpdb->prepare($sql, ...$params));
    }

    /**
     * Get root folder count
     *
     * @param integer $folderRootId Root folder ID
     *
     * @return integer
     */
    public static function getRootFolderCount($folderRootId)
    {
        // if disable root media count
        $root_media_count = self::wpmfGetOption('root_media_count');
        if ((int)$root_media_count === 0) {
            return 0;
        }

        global $wpdb;

        // Retrieve the overall count of attachements
        $query = $wpdb->prepare('SELECT COUNT(DISTINCT(p.ID)) AS count FROM ' . $wpdb->posts . ' AS p
                        WHERE p.post_type = %s 
                            AND (p.post_status = %s OR p.post_status = %s)', array('attachment','publish','inherit'));
        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- SQL not contain variable
        $total_count = (int)$wpdb->get_var($query);

        // Retrieve the number of attachments which are at least in one folder (except the root folder)
        $attachments_in_folders_count = (int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(DISTINCT(p.ID)) AS count FROM ' . $wpdb->posts . ' AS p 
                        LEFT JOIN ' . $wpdb->term_relationships . ' AS tr 
                            ON p.ID = tr.object_id
                        LEFT JOIN ' . $wpdb->term_taxonomy . ' AS tt 
                            ON tt.term_taxonomy_id=tr.term_taxonomy_id AND tt.taxonomy = "wpmf-category"
                        WHERE p.post_type = %s 
                            AND (p.post_status = "publish" OR p.post_status = "inherit")
                            AND tt.term_id IS NOT NULL
                            AND tt.term_id <> %d', array('attachment', (int)$folderRootId)));

        // Retrieve the number of attachments which are simultaneously in the root folder and in another folder
        $attachments_in_root_folder_count = (int)$wpdb->get_var($wpdb->prepare('SELECT COUNT(DISTINCT(p.ID)) AS count FROM ' . $wpdb->posts . ' AS p 
                        LEFT JOIN ' . $wpdb->term_relationships . ' AS tr 
                            ON p.ID = tr.object_id
                        LEFT JOIN ' . $wpdb->term_taxonomy . ' AS tt 
                            ON tt.term_taxonomy_id=tr.term_taxonomy_id AND tt.taxonomy = "wpmf-category"
                        WHERE p.post_type = %s
                            AND (p.post_status = %s OR p.post_status = %s)
                            AND tt.term_id = %d', array('attachment','publish','inherit', (int)$folderRootId)));

        return  $total_count - $attachments_in_folders_count + $attachments_in_root_folder_count;
    }

    /**
     * Tries to convert an attachment URL into a post ID.
     *
     * @param string $url       The URL to resolve.
     * @param string $ext       Extension of file
     * @param string $file_hash File hash
     * @param string $action    Action
     *
     * @return integer The found post ID, or 0 on failure.
     */
    public static function attachmentUrlToPostid($url, $ext = '', $file_hash = '', $action = '')
    {
        global $wpdb;
        $dir = wp_get_upload_dir();
        $path = $url;

        $site_url = parse_url($dir['url']);
        $image_path = parse_url($path);

        // Force the protocols to match if needed.
        if (isset($image_path['scheme']) && ($image_path['scheme'] !== $site_url['scheme'])) {
            $path = str_replace($image_path['scheme'], $site_url['scheme'], $path);
        }

        if (0 === strpos($path, $dir['baseurl'] . '/')) {
            $path = substr($path, strlen($dir['baseurl'] . '/'));
        }

        if ($ext === 'pdf') {
            $path = str_replace(array('-pdf.jpg', '-pdf.jpeg', '-pdf.png'), '.pdf', $path);
        }

        if ($action === 'import') {
            $sql = $wpdb->prepare(
                'SELECT post_id, meta_value FROM '. $wpdb->postmeta .' WHERE meta_key = "wpmf_sync_file_hash" AND meta_value = %s',
                $file_hash
            );
        } else {
            $sql = $wpdb->prepare(
                'SELECT post_id, meta_value FROM '. $wpdb->postmeta .' WHERE meta_key = "_wp_attached_file" AND meta_value = %s',
                $path
            );
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare
        $results = $wpdb->get_results($sql);
        $post_id = null;

        if ($results) {
            // Use the first available result, but prefer a case-sensitive match, if exists.
            $post_id = reset($results)->post_id;

            if (count($results) > 1) {
                foreach ($results as $result) {
                    $drive_id = get_post_meta($result->post_id, 'wpmf_drive_id', true);
                    if ($path === $result->meta_value && empty($drive_id)) {
                        $post_id = $result->post_id;
                        break;
                    }
                }
            }
        }

        return (int)$post_id;
    }

    /**
     * Get current user role
     *
     * @param integer $userId Id of user
     *
     * @return mixed|string
     */
    public static function getRoles($userId)
    {
        if (!function_exists('get_userdata')) {
            require_once(ABSPATH . 'wp-includes/pluggable.php');
        }

        if ((int)$userId === 0) {
            return 'administrator';
        }

        $userdata = get_userdata($userId);
        if (!empty($userdata->roles)) {
            if (in_array('administrator', $userdata->roles)) {
                return 'administrator';
            }
            $role = array_slice($userdata->roles, 0, 1);
            $role = $role[0];
        } else {
            $role = '';
        }

        return $role;
    }

    /**
     * Get current user role
     *
     * @param integer $userId Id of user
     *
     * @return array
     */
    public static function getAllRoles($userId)
    {
        if (!function_exists('get_userdata')) {
            require_once(ABSPATH . 'wp-includes/pluggable.php');
        }

        if ((int)$userId === 0) {
            return array('administrator');
        }

        $userdata = get_userdata($userId);
        if (!empty($userdata->roles)) {
            $roles = $userdata->roles;
        } else {
            $roles = array();
        }

        return $roles;
    }

    /**
     * Get cloud root folder ID
     *
     * @param string $cloud_type Cloud type
     *
     * @return boolean|integer
     */
    public static function getCloudRootFolderID($cloud_type)
    {
        $folder = false;
        switch ($cloud_type) {
            case 'google_drive':
                $folder = get_term_by('name', 'Google Drive', WPMF_TAXO);
                break;
            case 'dropbox':
                $folder = get_term_by('name', 'Dropbox', WPMF_TAXO);
                break;
            case 'onedrive':
                $folder = get_term_by('name', 'Onedrive', WPMF_TAXO);
                break;
            case 'onedrive_business':
                $folder = get_term_by('name', 'Onedrive Business', WPMF_TAXO);
                break;
        }

        if (!empty($folder)) {
            return $folder->term_id;
        }

        return false;
    }

    /**
     * Check cloud connected
     *
     * @param string $cloud_type Cloud type
     *
     * @return boolean
     */
    public static function isConnected($cloud_type)
    {
        $connected = false;
        switch ($cloud_type) {
            case 'google_drive':
                $options = get_option('_wpmfAddon_cloud_config');
                if (!empty($options['connected']) && !empty($options['media_access'])) {
                    $connected = true;
                }
                break;
            case 'dropbox':
                $options = get_option('_wpmfAddon_dropbox_config');
                if (!empty($options['dropboxToken']) && !empty($options['media_access'])) {
                    $connected = true;
                }
                break;
            case 'onedrive':
                $options = get_option('_wpmfAddon_onedrive_config');
                if (!empty($options['connected']) && !empty($options['media_access'])) {
                    $connected = true;
                }
                break;
            case 'onedrive_business':
                $options = get_option('_wpmfAddon_onedrive_business_config');
                if (!empty($options['connected']) && !empty($options['media_access'])) {
                    $connected = true;
                }
                break;
            case 'nextcloud':
                $options = get_option('_wpmfAddon_nextcloud_config');
                $connect_nextcloud = self::wpmfGetOption('connect_nextcloud');
                if (!empty($options['username']) && !empty($options['password']) && !empty($options['nextcloudurl']) && !empty($options['rootfoldername']) && !empty($connect_nextcloud) && !empty($options['media_access'])) {
                    $connected = true;
                }
                break;
            case 'owncloud':
                $options = get_option('_wpmfAddon_owncloud_config');
                $connect_owncloud = self::wpmfGetOption('connect_owncloud');
                if (!empty($options['username']) && !empty($options['password']) && !empty($options['owncloudurl']) && !empty($options['rootfoldername']) && !empty($connect_owncloud) && !empty($options['media_access'])) {
                    $connected = true;
                }
                break;
        }

        return $connected;
    }

    /**
     * Check enable load all media in cloud user folder
     *
     * @param string $cloud_type Cloud type
     *
     * @return boolean
     */
    public static function isLoadAllChildsCloud($cloud_type)
    {
        $connected = false;
        switch ($cloud_type) {
            case 'google_drive':
                $options = get_option('_wpmfAddon_cloud_config');
                if (!empty($options['connected']) && !empty($options['media_access']) && !empty($options['load_all_childs'])) {
                    $connected = true;
                }
                break;
            case 'dropbox':
                $options = get_option('_wpmfAddon_dropbox_config');
                if (!empty($options['dropboxToken']) && !empty($options['media_access']) && !empty($options['load_all_childs'])) {
                    $connected = true;
                }
                break;
            case 'onedrive':
                $options = get_option('_wpmfAddon_onedrive_config');
                if (!empty($options['connected']) && !empty($options['media_access']) && !empty($options['load_all_childs'])) {
                    $connected = true;
                }
                break;
            case 'onedrive_business':
                $options = get_option('_wpmfAddon_onedrive_business_config');
                if (!empty($options['connected']) && !empty($options['media_access']) && !empty($options['load_all_childs'])) {
                    $connected = true;
                }
                break;
            default:
                $connected = false;
        }

        return $connected;
    }

    /**
     * Get access
     *
     * @param integer $term_id            Folder ID
     * @param integer $user_id            User ID
     * @param string  $capability         Capability
     * @param string  $cloud_user_folders Cloud user folders list
     *
     * @return boolean
     */
    public static function getAccess($term_id, $user_id, $capability = '', $cloud_user_folders = array())
    {
        $active_media = get_option('wpmf_active_media');
        if (empty($active_media)) {
            return true;
        }

        $is_access = false;
        $roles = self::getAllRoles($user_id);
        if (in_array('administrator', $roles)) {
            return true;
        }

        if (empty($term_id)) {
            return false;
        }

        global $current_user;
        $term = get_term($term_id, WPMF_TAXO);
        // inherit folder permissions
        $role_permissions = get_term_meta((int)$term_id, 'wpmf_folder_role_permissions', true);
        $user_permissions = get_term_meta((int)$term_id, 'wpmf_folder_user_permissions', true);
        $inherit_folder = get_term_meta((int)$term_id, 'inherit_folder', true);
        if ((($inherit_folder === '' && ($role_permissions === '' || empty($role_permissions[0])) && ($user_permissions === '' || empty($user_permissions[0]))) || !empty($inherit_folder)) && $term->parent !== 0) {
            $ancestors = get_ancestors($term_id, WPMF_TAXO, 'taxonomy');
            if (!empty($ancestors)) {
                $t = false;
                foreach ($ancestors as $ancestor) {
                    $inherit_folder = get_term_meta((int)$ancestor, 'inherit_folder', true);
                    if ((int)$inherit_folder === 0) {
                        $t = true;
                        $term_id = $ancestor;
                        break;
                    }
                }

                if (!$t) {
                    $term_id = $ancestors[count($ancestors) - 1];
                }
            }
        }
        // check is root cloud folder
        if ($term->name === 'Google Drive' && (int)$term->parent === 0 && $capability === 'view_folder') {
            if (self::isConnected('google_drive')) {
                return true;
            } else {
                return false;
            }
        } elseif ($term->name === 'Dropbox' && (int)$term->parent === 0 && $capability === 'view_folder') {
            if (self::isConnected('dropbox')) {
                return true;
            } else {
                return false;
            }
        } elseif ($term->name === 'Onedrive' && (int)$term->parent === 0 && $capability === 'view_folder') {
            if (self::isConnected('onedrive')) {
                return true;
            } else {
                return false;
            }
        } elseif ($term->name === 'Onedrive Business' && (int)$term->parent === 0 && $capability === 'view_folder') {
            if (self::isConnected('onedrive_business')) {
                return true;
            } else {
                return false;
            }
        }

        if ($capability !== 'view_folder' && !$term_id) {
            return false;
        }

        // only show role folder when access type is 'role'
        $access_type     = get_option('wpmf_create_folder');
        if ($access_type === 'role') {
            if (in_array($term->name, $roles) && strpos($term->slug, '-wpmf-role') !== false) {
                return true;
            }
        }

        $type = get_term_meta($term_id, 'wpmf_drive_type', true);
        // if is cloud folder
        if (!empty($type)) {
            if (in_array($term_id, $cloud_user_folders)) {
                return true;
            }
        }

        // get access by role
        $permissions = get_term_meta((int)$term_id, 'wpmf_folder_role_permissions');
        if (!empty($permissions)) {
            foreach ($permissions as $permission) {
                if (!empty($permission[0]) && in_array($permission[0], $roles) && in_array($capability, $permission)) {
                    $is_access = true;
                    break;
                }
            }
        }

        if ($is_access) {
            return true;
        } else {
            // get access by user
            $permissions = get_term_meta((int)$term_id, 'wpmf_folder_user_permissions');
            if ($term->name === $current_user->user_login && (int) $term->term_group === (int) get_current_user_id()) {
                return true;
            }

            if (!empty($permissions)) {
                foreach ($permissions as $permission) {
                    if ((int)$permission[0] === get_current_user_id() && in_array($capability, $permission)) {
                        $is_access = true;
                        break;
                    }
                }
            }
        }

        return $is_access;
    }
    
    /**
     * Get kaltura video ID from URL
     *
     * @param string $url URL of video
     *
     * @return mixed|string
     */
    public static function getKalturaVideoIdFromUrl($url = '')
    {
        $array = explode('/', basename($url));
        return end($array);
    }

    /**
     * Get dailymotion video ID from URL
     *
     * @param string $url URL of video
     *
     * @return mixed|string
     */
    public static function getDailymotionVideoIdFromUrl($url = '')
    {
        $id = strtok(basename($url), '_');
        return $id;
    }

    /**
     * Get vimeo video ID from URL
     *
     * @param string $url URl of video
     *
     * @return mixed|string
     */
    public static function getVimeoVideoIdFromUrl($url = '')
    {
        $regs = array();
        $id   = '';
        if (preg_match(self::$vimeo_pattern, $url, $regs)) {
            $id = $regs[3];
        }

        return $id;
    }

    /**
     * Create video in media library
     *
     * @param string  $video_url Video URL
     * @param integer $thumbnail Video thumbnail
     * @param string  $action    Action
     *
     * @return boolean|integer|WP_Error
     */
    public static function doCreateVideo($video_url = '', $thumbnail = 0, $action = 'remote_video')
    {
        $title   = '';
        $ext     = '';
        $content = '';
        if ($action === 'video_to_gallery' && (int)$thumbnail !== 0 && !strpos($video_url, 'kaltura')) {
            update_post_meta($thumbnail, 'wpmf_remote_video_link', $video_url);
            return $thumbnail;
        }

        $video_url = str_replace('manage/videos/', '', $video_url);
        if (!preg_match(self::$vimeo_pattern, $video_url, $output_array)
            && !preg_match('/(youtube.com|youtu.be)\/(watch)?(\?v=)?(\S+)?/', $video_url, $match)
            && !preg_match('/\b(?:dailymotion)\.com\b/i', $video_url, $vresult)
            && !preg_match('/(videos.kaltura)\.com\b/i', $video_url, $vresult)) {
            return false;
        } elseif (preg_match(self::$vimeo_pattern, $video_url, $output_array)) {
            // for vimeo
            $id = self::getVimeoVideoIdFromUrl($video_url);
            $videos = wp_remote_get('https://player.vimeo.com/video/' . $id . '/config');
            $body = json_decode($videos['body']);
            if (!empty($body->video->thumbs->base)) {
                $thumb = $body->video->thumbs->base;
            } else {
                $videos = wp_remote_get('https://vimeo.com/api/v2/video/' . $id . '.json');
                $body = json_decode($videos['body']);
                $body = $body[0];
                $thumb = '';
                if (isset($body->thumbnail_large)) {
                    $thumb = $body->thumbnail_large;
                } elseif (isset($body->thumbnail_medium)) {
                    $thumb = $body->thumbnail_large;
                } elseif (isset($body->thumbnail_small)) {
                    $thumb = $body->thumbnail_small;
                }
            }

            if ($thumb !== '') {
                $thumb_remote = wp_remote_get($thumb);
                $content = $thumb_remote['body'];
                $title = (isset($body->title)) ? $body->title : $body->video->title;
                $ext = 'jpg';
            } else {
                return false;
            }
        } elseif (preg_match('/(youtube.com|youtu.be)\/(watch)?(\?v=)?(\S+)?/', $video_url, $match)) {
            // for youtube
            // get thumbnail of video
            $parts = parse_url($video_url);
            if ($parts['host'] === 'youtu.be') {
                $id = trim($parts['path'], '/');
            } else {
                parse_str($parts['query'], $query);
                $id = $query['v'];
            }

            $thumb = 'http://img.youtube.com/vi/' . $id . '/maxresdefault.jpg';
            $gets = wp_remote_get($thumb);
            if (!empty($gets) && $gets['response']['code'] !== 200) {
                $thumb = 'http://img.youtube.com/vi/' . $id . '/sddefault.jpg';
                $gets = wp_remote_get($thumb);
            }

            if (!empty($gets) && $gets['response']['code'] !== 200) {
                $thumb = 'http://img.youtube.com/vi/' . $id . '/hqdefault.jpg';
                $gets = wp_remote_get($thumb);
            }

            if (!empty($gets) && $gets['response']['code'] !== 200) {
                $thumb = 'http://img.youtube.com/vi/' . $id . '/mqdefault.jpg';
                $gets = wp_remote_get($thumb);
            }

            if (!empty($gets) && $gets['response']['code'] !== 200) {
                $thumb = 'http://img.youtube.com/vi/' . $id . '/default.jpg';
                $gets = wp_remote_get($thumb);
            }

            if (empty($gets)) {
                return false;
            }

            $content = $gets['body'];
            $json_datas = wp_remote_get('https://www.youtube.com/oembed?url=' . $video_url . '&format=json');
            if (!is_array($json_datas)) {
                return false;
            }

            $infos = json_decode($json_datas['body'], true);
            if (isset($infos['status']) && $infos['status'] === 'fail') {
                return false;
            }

            if (empty($infos['title'])) {
                $title = $id;
            } else {
                $title = $infos['title'];
            }

            $info_thumbnail = pathinfo($thumb); // get info thumbnail
            $ext            = $info_thumbnail['extension'];
        } elseif (preg_match('/\b(?:dailymotion)\.com\b/i', $video_url, $vresult)) {
            // for dailymotion
            $id   = self::getDailymotionVideoIdFromUrl($video_url);
            $gets = wp_remote_get('http://www.dailymotion.com/services/oembed?format=json&url=http://www.dailymotion.com/embed/video/' . $id);
            $info = json_decode($gets['body'], true);
            if (empty($info)) {
                return false;
            }

            // get thumbnail content of video
            $thumb = $info['thumbnail_url'];
            $thumb_gets        = wp_remote_get($thumb);
            if (empty($thumb_gets)) {
                return false;
            }
            $content = $thumb_gets['body'];
            $info_thumbnail = pathinfo($info['thumbnail_url']); // get info thumbnail
            $ext            = (!empty($info_thumbnail['extension'])) ? $info_thumbnail['extension'] : 'jpg';
        } elseif (preg_match('/(videos.kaltura)\.com\b/i', $video_url, $vresult)) {
            // for kaltura
            $id   = self::getKalturaVideoIdFromUrl($video_url);
            $partner_id = '5944002'; //partner id from account on Kaltura
            $thumb = 'http://cdnsecakmi.kaltura.com/p/' . $partner_id . '/thumbnail/entry_id/' . $id . '/width/2560/height/1920';
            $gets = wp_remote_get($thumb);

            if (empty($gets)) {
                return false;
            }
            $content = $gets['body'];

            //get title video
            $array_video_url = explode('/', $video_url);
            array_pop($array_video_url);
            $title = str_replace('+', ' ', end($array_video_url));

            $src = 'https://cdnapisec.kaltura.com/p/' . $partner_id . '/sp/' . $partner_id . '00/playManifest/entryId/'.$id.'/format/url/protocol/https/'.$partner_id.'/2000/name/'.$id.'.mp4';
            $video_url = $src;
            update_post_meta($thumbnail, 'wpmf_remote_video_link', $video_url);
            $json_datas = wp_remote_get('https://cdnapisec.kaltura.com/p/' . $partner_id . '/sp/' . $partner_id . '00/playManifest/entryId/'.$id.'/format/url/protocol/https/'.$partner_id.'/2000/name/'.$id.'.mp4');
            
            if (empty($json_datas)) {
                return false;
            }

            $infos = array();
            $infos['html'] = "<iframe width='200' height='150' src='".$src."' frameborder='0' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share' referrerpolicy='strict-origin-when-cross-origin' allowfullscreen title='".$title."'></iframe>";
            $ext            = 'jpeg';
        }

        $upload_dir = wp_upload_dir();
        // create wpmf_remote_video folder
        if (!file_exists($upload_dir['basedir'] . '/wpmf_remote_video')) {
            if (!mkdir($upload_dir['basedir'] . '/wpmf_remote_video')) {
                return false;
            }
        }

        if ((int)$thumbnail === 0) {
            // upload  thumbnail to wpmf_remote_video folder
            $upload_folder = $upload_dir['basedir'] . '/wpmf_remote_video';
            $thumb_name = sanitize_title($title);
            if (file_exists($upload_folder . '/' . $thumb_name . '.' . $ext)) {
                $fname = wp_unique_filename($upload_folder, $thumb_name . '.' . $ext);
                $upload        = file_put_contents($upload_folder . '/' . $fname, $content);
            } else {
                $fname = $thumb_name . '.' . $ext;
                $upload        = file_put_contents($upload_folder . '/' . $fname, $content);
            }

            $fname = sanitize_file_name($fname);
            // upload images
            if ($upload) {
                if (($ext === 'jpg')) {
                    $mimetype = 'image/jpeg';
                } else {
                    $mimetype = 'image/' . $ext;
                }
                $attachment = array(
                    'guid'           => $upload_dir['baseurl'] . '/' . $fname,
                    'post_mime_type' => $mimetype,
                    'post_title'     => $title,
                    'post_excerpt'   => $title
                );

                $image_path = $upload_folder . '/' . $fname;
                $attach_id  = wp_insert_attachment($attachment, $image_path);
                if (!is_wp_error($attach_id)) {
                    // create image in folder
                    $current_folder_id = $_POST['folder_id']; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
                    wp_set_object_terms((int) $attach_id, (int) $current_folder_id, WPMF_TAXO, false);

                    $attach_data = wp_generate_attachment_metadata($attach_id, $image_path);
                    wp_update_attachment_metadata($attach_id, $attach_data);
                    update_post_meta($attach_id, 'wpmf_remote_video_link', $video_url);
                    return $attach_id;
                }
            }

            return false;
        }

        update_post_meta($thumbnail, 'wpmf_remote_video_link', $video_url);
        return $thumbnail;
    }

    /**
     * Get video URL for iframe embeded
     *
     * @param string $remote_video Remote video url
     *
     * @return array
     */
    public static function parseVideoUrl($remote_video)
    {
        $url = $remote_video;
        $type = 'youtube';
        if ((!empty($remote_video)) && (strpos($remote_video, 'youtube') !== false || strpos($remote_video, 'youtu.be') !== false)) {
            $parts = parse_url($remote_video);
            if ($parts['host'] === 'youtu.be') {
                $youtube_id = trim($parts['path'], '/');
            } else {
                parse_str($parts['query'], $query);
                $youtube_id = $query['v'];
            }
            $url = 'https://www.youtube.com/embed/' . $youtube_id;
        }

        if ((!empty($remote_video)) && strpos($remote_video, 'vimeo') !== false) {
            $vimeo_id = self::getVimeoVideoIdFromUrl($remote_video);
            $url = 'https://player.vimeo.com/video/' . $vimeo_id;
            $type = 'vimeo';
        }

        if ((!empty($remote_video)) && (strpos($remote_video, 'dailymotion') !== false)) {
            $id = self::getDailymotionVideoIdFromUrl($remote_video);
            $url = 'https://dailymotion.com/embed/video/' . $id;
            $type = 'dailymotion';
        }

        if ((!empty($remote_video)) && (strpos($remote_video, 'wistia') !== false)) {
            $type = 'wistia';
        }

        if ((!empty($remote_video)) && (strpos($remote_video, 'facebook') !== false)) {
            $url = 'https://www.facebook.com/plugins/video.php?height=314&href='. urlencode($remote_video) .'&show_text=false&width=560';
            $type = 'facebook';
        }

        if ((!empty($remote_video)) && (strpos($remote_video, 'twitch') !== false)) {
            $parts = parse_url($remote_video);
            if (strpos($parts['path'], '/video') !== false) {
                $twitch_id = str_replace('/videos/', '', $parts['path']);
                $url = 'https://player.twitch.tv/?video='. $twitch_id .'&parent=' . $_SERVER['SERVER_NAME'];
            } else {
                $twitch_id = trim($parts['path'], '/');
                $url = 'https://player.twitch.tv/?channel='. $twitch_id .'&parent=' . $_SERVER['SERVER_NAME'];
            }
            $type = 'twitch';
        }

        return array($url,$type) ;
    }

    /**
     * Get mime type by extension
     *
     * @param string $extension Extension of file
     *
     * @return mixed|string
     */
    public static function getMimeType($extension = '')
    {
        if (empty($extension)) {
            return 'application/octet-stream';
        }
        $extension = strtolower($extension);
        $mime_types_map = array(
            '123'          => 'application/vnd.lotus-1-2-3',
            '3dml'         => 'text/vnd.in3d.3dml',
            '3ds'          => 'image/x-3ds',
            '3g2'          => 'video/3gpp2',
            '3gp'          => 'video/3gpp',
            '7z'           => 'application/x-7z-compressed',
            'aab'          => 'application/x-authorware-bin',
            'aac'          => 'audio/x-aac',
            'aam'          => 'application/x-authorware-map',
            'aas'          => 'application/x-authorware-seg',
            'abw'          => 'application/x-abiword',
            'ac'           => 'application/pkix-attr-cert',
            'acc'          => 'application/vnd.americandynamics.acc',
            'ace'          => 'application/x-ace-compressed',
            'acu'          => 'application/vnd.acucobol',
            'acutc'        => 'application/vnd.acucorp',
            'adp'          => 'audio/adpcm',
            'aep'          => 'application/vnd.audiograph',
            'afm'          => 'application/x-font-type1',
            'afp'          => 'application/vnd.ibm.modcap',
            'ahead'        => 'application/vnd.ahead.space',
            'ai'           => 'application/postscript',
            'aif'          => 'audio/x-aiff',
            'aifc'         => 'audio/x-aiff',
            'aiff'         => 'audio/x-aiff',
            'air'          => 'application/vnd.adobe.air-application-installer-package+zip',
            'ait'          => 'application/vnd.dvb.ait',
            'ami'          => 'application/vnd.amiga.ami',
            'apk'          => 'application/vnd.android.package-archive',
            'appcache'     => 'text/cache-manifest',
            'application'  => 'application/x-ms-application',
            'apr'          => 'application/vnd.lotus-approach',
            'arc'          => 'application/x-freearc',
            'asc'          => 'application/pgp-signature',
            'asf'          => 'video/x-ms-asf',
            'asm'          => 'text/x-asm',
            'aso'          => 'application/vnd.accpac.simply.aso',
            'asx'          => 'video/x-ms-asf',
            'atc'          => 'application/vnd.acucorp',
            'atom'         => 'application/atom+xml',
            'atomcat'      => 'application/atomcat+xml',
            'atomsvc'      => 'application/atomsvc+xml',
            'atx'          => 'application/vnd.antix.game-component',
            'au'           => 'audio/basic',
            'avi'          => 'video/avi',
            'avif'         => 'image/avif',
            'aw'           => 'application/applixware',
            'azf'          => 'application/vnd.airzip.filesecure.azf',
            'azs'          => 'application/vnd.airzip.filesecure.azs',
            'azw'          => 'application/vnd.amazon.ebook',
            'bat'          => 'application/x-msdownload',
            'bcpio'        => 'application/x-bcpio',
            'bdf'          => 'application/x-font-bdf',
            'bdm'          => 'application/vnd.syncml.dm+wbxml',
            'bed'          => 'application/vnd.realvnc.bed',
            'bh2'          => 'application/vnd.fujitsu.oasysprs',
            'bin'          => 'application/octet-stream',
            'blb'          => 'application/x-blorb',
            'blorb'        => 'application/x-blorb',
            'bmi'          => 'application/vnd.bmi',
            'bmp'          => 'image/bmp',
            'book'         => 'application/vnd.framemaker',
            'box'          => 'application/vnd.previewsystems.box',
            'boz'          => 'application/x-bzip2',
            'bpk'          => 'application/octet-stream',
            'btif'         => 'image/prs.btif',
            'buffer'       => 'application/octet-stream',
            'bz'           => 'application/x-bzip',
            'bz2'          => 'application/x-bzip2',
            'c'            => 'text/x-c',
            'c11amc'       => 'application/vnd.cluetrust.cartomobile-config',
            'c11amz'       => 'application/vnd.cluetrust.cartomobile-config-pkg',
            'c4d'          => 'application/vnd.clonk.c4group',
            'c4f'          => 'application/vnd.clonk.c4group',
            'c4g'          => 'application/vnd.clonk.c4group',
            'c4p'          => 'application/vnd.clonk.c4group',
            'c4u'          => 'application/vnd.clonk.c4group',
            'cab'          => 'application/vnd.ms-cab-compressed',
            'caf'          => 'audio/x-caf',
            'cap'          => 'application/vnd.tcpdump.pcap',
            'car'          => 'application/vnd.curl.car',
            'cat'          => 'application/vnd.ms-pki.seccat',
            'cb7'          => 'application/x-cbr',
            'cba'          => 'application/x-cbr',
            'cbr'          => 'application/x-cbr',
            'cbt'          => 'application/x-cbr',
            'cbz'          => 'application/x-cbr',
            'cc'           => 'text/x-c',
            'cct'          => 'application/x-director',
            'ccxml'        => 'application/ccxml+xml',
            'cdbcmsg'      => 'application/vnd.contact.cmsg',
            'cdf'          => 'application/x-netcdf',
            'cdkey'        => 'application/vnd.mediastation.cdkey',
            'cdmia'        => 'application/cdmi-capability',
            'cdmic'        => 'application/cdmi-container',
            'cdmid'        => 'application/cdmi-domain',
            'cdmio'        => 'application/cdmi-object',
            'cdmiq'        => 'application/cdmi-queue',
            'cdx'          => 'chemical/x-cdx',
            'cdxml'        => 'application/vnd.chemdraw+xml',
            'cdy'          => 'application/vnd.cinderella',
            'cer'          => 'application/pkix-cert',
            'cfs'          => 'application/x-cfs-compressed',
            'cgm'          => 'image/cgm',
            'chat'         => 'application/x-chat',
            'chm'          => 'application/vnd.ms-htmlhelp',
            'chrt'         => 'application/vnd.kde.kchart',
            'cif'          => 'chemical/x-cif',
            'cii'          => 'application/vnd.anser-web-certificate-issue-initiation',
            'cil'          => 'application/vnd.ms-artgalry',
            'cla'          => 'application/vnd.claymore',
            'class'        => 'application/java-vm',
            'clkk'         => 'application/vnd.crick.clicker.keyboard',
            'clkp'         => 'application/vnd.crick.clicker.palette',
            'clkt'         => 'application/vnd.crick.clicker.template',
            'clkw'         => 'application/vnd.crick.clicker.wordbank',
            'clkx'         => 'application/vnd.crick.clicker',
            'clp'          => 'application/x-msclip',
            'cmc'          => 'application/vnd.cosmocaller',
            'cmdf'         => 'chemical/x-cmdf',
            'cml'          => 'chemical/x-cml',
            'cmp'          => 'application/vnd.yellowriver-custom-menu',
            'cmx'          => 'image/x-cmx',
            'cod'          => 'application/vnd.rim.cod',
            'com'          => 'application/x-msdownload',
            'conf'         => 'text/plain',
            'cpio'         => 'application/x-cpio',
            'cpp'          => 'text/x-c',
            'cpt'          => 'application/mac-compactpro',
            'crd'          => 'application/x-mscardfile',
            'crl'          => 'application/pkix-crl',
            'crt'          => 'application/x-x509-ca-cert',
            'crx'          => 'application/x-chrome-extension',
            'cryptonote'   => 'application/vnd.rig.cryptonote',
            'csh'          => 'application/x-csh',
            'csml'         => 'chemical/x-csml',
            'csp'          => 'application/vnd.commonspace',
            'css'          => 'text/css',
            'cst'          => 'application/x-director',
            'csv'          => 'text/csv',
            'cu'           => 'application/cu-seeme',
            'curl'         => 'text/vnd.curl',
            'cww'          => 'application/prs.cww',
            'cxt'          => 'application/x-director',
            'cxx'          => 'text/x-c',
            'dae'          => 'model/vnd.collada+xml',
            'daf'          => 'application/vnd.mobius.daf',
            'dart'         => 'application/vnd.dart',
            'dataless'     => 'application/vnd.fdsn.seed',
            'davmount'     => 'application/davmount+xml',
            'dbk'          => 'application/docbook+xml',
            'dcr'          => 'application/x-director',
            'dcurl'        => 'text/vnd.curl.dcurl',
            'dd2'          => 'application/vnd.oma.dd2+xml',
            'ddd'          => 'application/vnd.fujixerox.ddd',
            'deb'          => 'application/x-debian-package',
            'def'          => 'text/plain',
            'deploy'       => 'application/octet-stream',
            'der'          => 'application/x-x509-ca-cert',
            'dfac'         => 'application/vnd.dreamfactory',
            'dgc'          => 'application/x-dgc-compressed',
            'dic'          => 'text/x-c',
            'dir'          => 'application/x-director',
            'dis'          => 'application/vnd.mobius.dis',
            'dist'         => 'application/octet-stream',
            'distz'        => 'application/octet-stream',
            'djv'          => 'image/vnd.djvu',
            'djvu'         => 'image/vnd.djvu',
            'dll'          => 'application/x-msdownload',
            'dmg'          => 'application/x-apple-diskimage',
            'dmp'          => 'application/vnd.tcpdump.pcap',
            'dms'          => 'application/octet-stream',
            'dna'          => 'application/vnd.dna',
            'doc'          => 'application/msword',
            'docm'         => 'application/vnd.ms-word.document.macroenabled.12',
            'docx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dot'          => 'application/msword',
            'dotm'         => 'application/vnd.ms-word.template.macroenabled.12',
            'dotx'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'dp'           => 'application/vnd.osgi.dp',
            'dpg'          => 'application/vnd.dpgraph',
            'dra'          => 'audio/vnd.dra',
            'dsc'          => 'text/prs.lines.tag',
            'dssc'         => 'application/dssc+der',
            'dtb'          => 'application/x-dtbook+xml',
            'dtd'          => 'application/xml-dtd',
            'dts'          => 'audio/vnd.dts',
            'dtshd'        => 'audio/vnd.dts.hd',
            'dump'         => 'application/octet-stream',
            'dvb'          => 'video/vnd.dvb.file',
            'dvi'          => 'application/x-dvi',
            'dwf'          => 'model/vnd.dwf',
            'dwg'          => 'image/vnd.dwg',
            'dxf'          => 'image/vnd.dxf',
            'dxp'          => 'application/vnd.spotfire.dxp',
            'dxr'          => 'application/x-director',
            'ecelp4800'    => 'audio/vnd.nuera.ecelp4800',
            'ecelp7470'    => 'audio/vnd.nuera.ecelp7470',
            'ecelp9600'    => 'audio/vnd.nuera.ecelp9600',
            'ecma'         => 'application/ecmascript',
            'edm'          => 'application/vnd.novadigm.edm',
            'edx'          => 'application/vnd.novadigm.edx',
            'efif'         => 'application/vnd.picsel',
            'ei6'          => 'application/vnd.pg.osasli',
            'elc'          => 'application/octet-stream',
            'emf'          => 'application/x-msmetafile',
            'eml'          => 'message/rfc822',
            'emma'         => 'application/emma+xml',
            'emz'          => 'application/x-msmetafile',
            'eol'          => 'audio/vnd.digital-winds',
            'eot'          => 'application/vnd.ms-fontobject',
            'eps'          => 'application/postscript',
            'epub'         => 'application/epub+zip',
            'es3'          => 'application/vnd.eszigno3+xml',
            'esa'          => 'application/vnd.osgi.subsystem',
            'esf'          => 'application/vnd.epson.esf',
            'et3'          => 'application/vnd.eszigno3+xml',
            'etx'          => 'text/x-setext',
            'eva'          => 'application/x-eva',
            'event-stream' => 'text/event-stream',
            'evy'          => 'application/x-envoy',
            'exe'          => 'application/x-msdownload',
            'exi'          => 'application/exi',
            'ext'          => 'application/vnd.novadigm.ext',
            'ez'           => 'application/andrew-inset',
            'ez2'          => 'application/vnd.ezpix-album',
            'ez3'          => 'application/vnd.ezpix-package',
            'f'            => 'text/x-fortran',
            'f4v'          => 'video/x-f4v',
            'f77'          => 'text/x-fortran',
            'f90'          => 'text/x-fortran',
            'fbs'          => 'image/vnd.fastbidsheet',
            'fcdt'         => 'application/vnd.adobe.formscentral.fcdt',
            'fcs'          => 'application/vnd.isac.fcs',
            'fdf'          => 'application/vnd.fdf',
            'fe_launch'    => 'application/vnd.denovo.fcselayout-link',
            'fg5'          => 'application/vnd.fujitsu.oasysgp',
            'fgd'          => 'application/x-director',
            'fh'           => 'image/x-freehand',
            'fh4'          => 'image/x-freehand',
            'fh5'          => 'image/x-freehand',
            'fh7'          => 'image/x-freehand',
            'fhc'          => 'image/x-freehand',
            'fig'          => 'application/x-xfig',
            'flac'         => 'audio/flac',
            'fli'          => 'video/x-fli',
            'flo'          => 'application/vnd.micrografx.flo',
            'flv'          => 'video/x-flv',
            'flw'          => 'application/vnd.kde.kivio',
            'flx'          => 'text/vnd.fmi.flexstor',
            'fly'          => 'text/vnd.fly',
            'fm'           => 'application/vnd.framemaker',
            'fnc'          => 'application/vnd.frogans.fnc',
            'for'          => 'text/x-fortran',
            'fpx'          => 'image/vnd.fpx',
            'frame'        => 'application/vnd.framemaker',
            'fsc'          => 'application/vnd.fsc.weblaunch',
            'fst'          => 'image/vnd.fst',
            'ftc'          => 'application/vnd.fluxtime.clip',
            'fti'          => 'application/vnd.anser-web-funds-transfer-initiation',
            'fvt'          => 'video/vnd.fvt',
            'fxp'          => 'application/vnd.adobe.fxp',
            'fxpl'         => 'application/vnd.adobe.fxp',
            'fzs'          => 'application/vnd.fuzzysheet',
            'g2w'          => 'application/vnd.geoplan',
            'g3'           => 'image/g3fax',
            'g3w'          => 'application/vnd.geospace',
            'gac'          => 'application/vnd.groove-account',
            'gam'          => 'application/x-tads',
            'gbr'          => 'application/rpki-ghostbusters',
            'gca'          => 'application/x-gca-compressed',
            'gdl'          => 'model/vnd.gdl',
            'geo'          => 'application/vnd.dynageo',
            'gex'          => 'application/vnd.geometry-explorer',
            'ggb'          => 'application/vnd.geogebra.file',
            'ggt'          => 'application/vnd.geogebra.tool',
            'ghf'          => 'application/vnd.groove-help',
            'gif'          => 'image/gif',
            'gim'          => 'application/vnd.groove-identity-message',
            'gml'          => 'application/gml+xml',
            'gmx'          => 'application/vnd.gmx',
            'gnumeric'     => 'application/x-gnumeric',
            'gph'          => 'application/vnd.flographit',
            'gpx'          => 'application/gpx+xml',
            'gqf'          => 'application/vnd.grafeq',
            'gqs'          => 'application/vnd.grafeq',
            'gram'         => 'application/srgs',
            'gramps'       => 'application/x-gramps-xml',
            'gre'          => 'application/vnd.geometry-explorer',
            'grv'          => 'application/vnd.groove-injector',
            'grxml'        => 'application/srgs+xml',
            'gsf'          => 'application/x-font-ghostscript',
            'gtar'         => 'application/x-gtar',
            'gtm'          => 'application/vnd.groove-tool-message',
            'gtw'          => 'model/vnd.gtw',
            'gv'           => 'text/vnd.graphviz',
            'gxf'          => 'application/gxf',
            'gxt'          => 'application/vnd.geonext',
            'h'            => 'text/x-c',
            'h261'         => 'video/h261',
            'h263'         => 'video/h263',
            'h264'         => 'video/h264',
            'hal'          => 'application/vnd.hal+xml',
            'hbci'         => 'application/vnd.hbci',
            'hdf'          => 'application/x-hdf',
            'hh'           => 'text/x-c',
            'hlp'          => 'application/winhlp',
            'hpgl'         => 'application/vnd.hp-hpgl',
            'hpid'         => 'application/vnd.hp-hpid',
            'hps'          => 'application/vnd.hp-hps',
            'hqx'          => 'application/mac-binhex40',
            'htc'          => 'text/x-component',
            'htke'         => 'application/vnd.kenameaapp',
            'htm'          => 'text/html',
            'html'         => 'text/html',
            'hvd'          => 'application/vnd.yamaha.hv-dic',
            'hvp'          => 'application/vnd.yamaha.hv-voice',
            'hvs'          => 'application/vnd.yamaha.hv-script',
            'i2g'          => 'application/vnd.intergeo',
            'icc'          => 'application/vnd.iccprofile',
            'ice'          => 'x-conference/x-cooltalk',
            'icm'          => 'application/vnd.iccprofile',
            'ico'          => 'image/x-icon',
            'ics'          => 'text/calendar',
            'ief'          => 'image/ief',
            'ifb'          => 'text/calendar',
            'ifm'          => 'application/vnd.shana.informed.formdata',
            'iges'         => 'model/iges',
            'igl'          => 'application/vnd.igloader',
            'igm'          => 'application/vnd.insors.igm',
            'igs'          => 'model/iges',
            'igx'          => 'application/vnd.micrografx.igx',
            'iif'          => 'application/vnd.shana.informed.interchange',
            'imp'          => 'application/vnd.accpac.simply.imp',
            'ims'          => 'application/vnd.ms-ims',
            'in'           => 'text/plain',
            'ink'          => 'application/inkml+xml',
            'inkml'        => 'application/inkml+xml',
            'install'      => 'application/x-install-instructions',
            'iota'         => 'application/vnd.astraea-software.iota',
            'ipfix'        => 'application/ipfix',
            'ipk'          => 'application/vnd.shana.informed.package',
            'irm'          => 'application/vnd.ibm.rights-management',
            'irp'          => 'application/vnd.irepository.package+xml',
            'iso'          => 'application/x-iso9660-image',
            'itp'          => 'application/vnd.shana.informed.formtemplate',
            'ivp'          => 'application/vnd.immervision-ivp',
            'ivu'          => 'application/vnd.immervision-ivu',
            'jad'          => 'text/vnd.sun.j2me.app-descriptor',
            'jam'          => 'application/vnd.jam',
            'jar'          => 'application/java-archive',
            'java'         => 'text/x-java-source',
            'jisp'         => 'application/vnd.jisp',
            'jlt'          => 'application/vnd.hp-jlyt',
            'jnlp'         => 'application/x-java-jnlp-file',
            'joda'         => 'application/vnd.joost.joda-archive',
            'jpe'          => 'image/jpe',
            'jpeg'         => 'image/jpeg',
            'jpg'          => 'image/jpg',
            'jpgm'         => 'video/jpm',
            'jpgv'         => 'video/jpeg',
            'jpm'          => 'video/jpm',
            'js'           => 'application/javascript',
            'json'         => 'application/json',
            'jsonml'       => 'application/jsonml+json',
            'kar'          => 'audio/midi',
            'karbon'       => 'application/vnd.kde.karbon',
            'kfo'          => 'application/vnd.kde.kformula',
            'kia'          => 'application/vnd.kidspiration',
            'kml'          => 'application/vnd.google-earth.kml+xml',
            'kmz'          => 'application/vnd.google-earth.kmz',
            'kne'          => 'application/vnd.kinar',
            'knp'          => 'application/vnd.kinar',
            'kon'          => 'application/vnd.kde.kontour',
            'kpr'          => 'application/vnd.kde.kpresenter',
            'kpt'          => 'application/vnd.kde.kpresenter',
            'kpxx'         => 'application/vnd.ds-keypoint',
            'ksp'          => 'application/vnd.kde.kspread',
            'ktr'          => 'application/vnd.kahootz',
            'ktx'          => 'image/ktx',
            'ktz'          => 'application/vnd.kahootz',
            'kwd'          => 'application/vnd.kde.kword',
            'kwt'          => 'application/vnd.kde.kword',
            'lasxml'       => 'application/vnd.las.las+xml',
            'latex'        => 'application/x-latex',
            'lbd'          => 'application/vnd.llamagraphics.life-balance.desktop',
            'lbe'          => 'application/vnd.llamagraphics.life-balance.exchange+xml',
            'les'          => 'application/vnd.hhe.lesson-player',
            'lha'          => 'application/x-lzh-compressed',
            'link66'       => 'application/vnd.route66.link66+xml',
            'list'         => 'text/plain',
            'list3820'     => 'application/vnd.ibm.modcap',
            'listafp'      => 'application/vnd.ibm.modcap',
            'lnk'          => 'application/x-ms-shortcut',
            'log'          => 'text/plain',
            'lostxml'      => 'application/lost+xml',
            'lrf'          => 'application/octet-stream',
            'lrm'          => 'application/vnd.ms-lrm',
            'ltf'          => 'application/vnd.frogans.ltf',
            'lua'          => 'text/x-lua',
            'luac'         => 'application/x-lua-bytecode',
            'lvp'          => 'audio/vnd.lucent.voice',
            'lwp'          => 'application/vnd.lotus-wordpro',
            'lzh'          => 'application/x-lzh-compressed',
            'm13'          => 'application/x-msmediaview',
            'm14'          => 'application/x-msmediaview',
            'm1v'          => 'video/mpeg',
            'm21'          => 'application/mp21',
            'm2a'          => 'audio/mpeg',
            'm2v'          => 'video/mpeg',
            'm3a'          => 'audio/mpeg',
            'm3u'          => 'audio/x-mpegurl',
            'm3u8'         => 'application/x-mpegURL',
            'm4a'          => 'audio/mp4',
            'm4p'          => 'application/mp4',
            'm4u'          => 'video/vnd.mpegurl',
            'm4v'          => 'video/x-m4v',
            'ma'           => 'application/mathematica',
            'mads'         => 'application/mads+xml',
            'mag'          => 'application/vnd.ecowin.chart',
            'maker'        => 'application/vnd.framemaker',
            'man'          => 'text/troff',
            'manifest'     => 'text/cache-manifest',
            'mar'          => 'application/octet-stream',
            'markdown'     => 'text/x-markdown',
            'mathml'       => 'application/mathml+xml',
            'mb'           => 'application/mathematica',
            'mbk'          => 'application/vnd.mobius.mbk',
            'mbox'         => 'application/mbox',
            'mc1'          => 'application/vnd.medcalcdata',
            'mcd'          => 'application/vnd.mcd',
            'mcurl'        => 'text/vnd.curl.mcurl',
            'md'           => 'text/x-markdown',
            'mdb'          => 'application/x-msaccess',
            'mdi'          => 'image/vnd.ms-modi',
            'me'           => 'text/troff',
            'mesh'         => 'model/mesh',
            'meta4'        => 'application/metalink4+xml',
            'metalink'     => 'application/metalink+xml',
            'mets'         => 'application/mets+xml',
            'mfm'          => 'application/vnd.mfmp',
            'mft'          => 'application/rpki-manifest',
            'mgp'          => 'application/vnd.osgeo.mapguide.package',
            'mgz'          => 'application/vnd.proteus.magazine',
            'mid'          => 'audio/midi',
            'midi'         => 'audio/midi',
            'mie'          => 'application/x-mie',
            'mif'          => 'application/vnd.mif',
            'mime'         => 'message/rfc822',
            'mj2'          => 'video/mj2',
            'mjp2'         => 'video/mj2',
            'mk3d'         => 'video/x-matroska',
            'mka'          => 'audio/x-matroska',
            'mkd'          => 'text/x-markdown',
            'mks'          => 'video/x-matroska',
            'mkv'          => 'video/x-matroska',
            'mlp'          => 'application/vnd.dolby.mlp',
            'mmd'          => 'application/vnd.chipnuts.karaoke-mmd',
            'mmf'          => 'application/vnd.smaf',
            'mmr'          => 'image/vnd.fujixerox.edmics-mmr',
            'mng'          => 'video/x-mng',
            'mny'          => 'application/x-msmoney',
            'mobi'         => 'application/x-mobipocket-ebook',
            'mods'         => 'application/mods+xml',
            'mov'          => 'video/quicktime',
            'movie'        => 'video/x-sgi-movie',
            'mp2'          => 'audio/mpeg',
            'mp21'         => 'application/mp21',
            'mp2a'         => 'audio/mpeg',
            'mp3'          => 'audio/mpeg',
            'mp4'          => 'video/mp4',
            'mp4a'         => 'audio/mp4',
            'mp4s'         => 'application/mp4',
            'mp4v'         => 'video/mp4',
            'mpc'          => 'application/vnd.mophun.certificate',
            'mpe'          => 'video/mpeg',
            'mpeg'         => 'video/mpeg',
            'mpg'          => 'video/mpeg',
            'mpg4'         => 'video/mp4',
            'mpga'         => 'audio/mpeg',
            'mpkg'         => 'application/vnd.apple.installer+xml',
            'mpm'          => 'application/vnd.blueice.multipass',
            'mpn'          => 'application/vnd.mophun.application',
            'mpp'          => 'application/vnd.ms-project',
            'mpt'          => 'application/vnd.ms-project',
            'mpy'          => 'application/vnd.ibm.minipay',
            'mqy'          => 'application/vnd.mobius.mqy',
            'mrc'          => 'application/marc',
            'mrcx'         => 'application/marcxml+xml',
            'ms'           => 'text/troff',
            'mscml'        => 'application/mediaservercontrol+xml',
            'mseed'        => 'application/vnd.fdsn.mseed',
            'mseq'         => 'application/vnd.mseq',
            'msf'          => 'application/vnd.epson.msf',
            'msh'          => 'model/mesh',
            'msi'          => 'application/x-msdownload',
            'msl'          => 'application/vnd.mobius.msl',
            'msty'         => 'application/vnd.muvee.style',
            'mts'          => 'model/vnd.mts',
            'mus'          => 'application/vnd.musician',
            'musicxml'     => 'application/vnd.recordare.musicxml+xml',
            'mvb'          => 'application/x-msmediaview',
            'mwf'          => 'application/vnd.mfer',
            'mxf'          => 'application/mxf',
            'mxl'          => 'application/vnd.recordare.musicxml',
            'mxml'         => 'application/xv+xml',
            'mxs'          => 'application/vnd.triscape.mxs',
            'mxu'          => 'video/vnd.mpegurl',
            'n-gage'       => 'application/vnd.nokia.n-gage.symbian.install',
            'n3'           => 'text/n3',
            'nb'           => 'application/mathematica',
            'nbp'          => 'application/vnd.wolfram.player',
            'nc'           => 'application/x-netcdf',
            'ncx'          => 'application/x-dtbncx+xml',
            'nfo'          => 'text/x-nfo',
            'ngdat'        => 'application/vnd.nokia.n-gage.data',
            'nitf'         => 'application/vnd.nitf',
            'nlu'          => 'application/vnd.neurolanguage.nlu',
            'nml'          => 'application/vnd.enliven',
            'nnd'          => 'application/vnd.noblenet-directory',
            'nns'          => 'application/vnd.noblenet-sealer',
            'nnw'          => 'application/vnd.noblenet-web',
            'npx'          => 'image/vnd.net-fpx',
            'nsc'          => 'application/x-conference',
            'nsf'          => 'application/vnd.lotus-notes',
            'ntf'          => 'application/vnd.nitf',
            'nzb'          => 'application/x-nzb',
            'oa2'          => 'application/vnd.fujitsu.oasys2',
            'oa3'          => 'application/vnd.fujitsu.oasys3',
            'oas'          => 'application/vnd.fujitsu.oasys',
            'obd'          => 'application/x-msbinder',
            'obj'          => 'application/x-tgif',
            'oda'          => 'application/oda',
            'odb'          => 'application/vnd.oasis.opendocument.database',
            'odc'          => 'application/vnd.oasis.opendocument.chart',
            'odf'          => 'application/vnd.oasis.opendocument.formula',
            'odft'         => 'application/vnd.oasis.opendocument.formula-template',
            'odg'          => 'application/vnd.oasis.opendocument.graphics',
            'odi'          => 'application/vnd.oasis.opendocument.image',
            'odm'          => 'application/vnd.oasis.opendocument.text-master',
            'odp'          => 'application/vnd.oasis.opendocument.presentation',
            'ods'          => 'application/vnd.oasis.opendocument.spreadsheet',
            'odt'          => 'application/vnd.oasis.opendocument.text',
            'oga'          => 'audio/ogg',
            'ogg'          => 'audio/ogg',
            'ogv'          => 'video/ogg',
            'ogx'          => 'application/ogg',
            'omdoc'        => 'application/omdoc+xml',
            'onepkg'       => 'application/onenote',
            'onetmp'       => 'application/onenote',
            'onetoc'       => 'application/onenote',
            'onetoc2'      => 'application/onenote',
            'opf'          => 'application/oebps-package+xml',
            'opml'         => 'text/x-opml',
            'oprc'         => 'application/vnd.palm',
            'org'          => 'application/vnd.lotus-organizer',
            'osf'          => 'application/vnd.yamaha.openscoreformat',
            'osfpvg'       => 'application/vnd.yamaha.openscoreformat.osfpvg+xml',
            'otc'          => 'application/vnd.oasis.opendocument.chart-template',
            'otf'          => 'font/opentype',
            'otg'          => 'application/vnd.oasis.opendocument.graphics-template',
            'oth'          => 'application/vnd.oasis.opendocument.text-web',
            'oti'          => 'application/vnd.oasis.opendocument.image-template',
            'otp'          => 'application/vnd.oasis.opendocument.presentation-template',
            'ots'          => 'application/vnd.oasis.opendocument.spreadsheet-template',
            'ott'          => 'application/vnd.oasis.opendocument.text-template',
            'oxps'         => 'application/oxps',
            'oxt'          => 'application/vnd.openofficeorg.extension',
            'p'            => 'text/x-pascal',
            'p10'          => 'application/pkcs10',
            'p12'          => 'application/x-pkcs12',
            'p7b'          => 'application/x-pkcs7-certificates',
            'p7c'          => 'application/pkcs7-mime',
            'p7m'          => 'application/pkcs7-mime',
            'p7r'          => 'application/x-pkcs7-certreqresp',
            'p7s'          => 'application/pkcs7-signature',
            'p8'           => 'application/pkcs8',
            'pas'          => 'text/x-pascal',
            'paw'          => 'application/vnd.pawaafile',
            'pbd'          => 'application/vnd.powerbuilder6',
            'pbm'          => 'image/x-portable-bitmap',
            'pcap'         => 'application/vnd.tcpdump.pcap',
            'pcf'          => 'application/x-font-pcf',
            'pcl'          => 'application/vnd.hp-pcl',
            'pclxl'        => 'application/vnd.hp-pclxl',
            'pct'          => 'image/x-pict',
            'pcurl'        => 'application/vnd.curl.pcurl',
            'pcx'          => 'image/x-pcx',
            'pdb'          => 'application/vnd.palm',
            'pdf'          => 'application/pdf',
            'pfa'          => 'application/x-font-type1',
            'pfb'          => 'application/x-font-type1',
            'pfm'          => 'application/x-font-type1',
            'pfr'          => 'application/font-tdpfr',
            'pfx'          => 'application/x-pkcs12',
            'pgm'          => 'image/x-portable-graymap',
            'pgn'          => 'application/x-chess-pgn',
            'pgp'          => 'application/pgp-encrypted',
            'pic'          => 'image/x-pict',
            'pkg'          => 'application/octet-stream',
            'pki'          => 'application/pkixcmp',
            'pkipath'      => 'application/pkix-pkipath',
            'plb'          => 'application/vnd.3gpp.pic-bw-large',
            'plc'          => 'application/vnd.mobius.plc',
            'plf'          => 'application/vnd.pocketlearn',
            'pls'          => 'application/pls+xml',
            'pml'          => 'application/vnd.ctc-posml',
            'png'          => 'image/png',
            'pnm'          => 'image/x-portable-anymap',
            'portpkg'      => 'application/vnd.macports.portpkg',
            'pot'          => 'application/vnd.ms-powerpoint',
            'potm'         => 'application/vnd.ms-powerpoint.template.macroenabled.12',
            'potx'         => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'ppam'         => 'application/vnd.ms-powerpoint.addin.macroenabled.12',
            'ppd'          => 'application/vnd.cups-ppd',
            'ppm'          => 'image/x-portable-pixmap',
            'pps'          => 'application/vnd.ms-powerpoint',
            'ppsm'         => 'application/vnd.ms-powerpoint.slideshow.macroenabled.12',
            'ppsx'         => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'ppt'          => 'application/vnd.ms-powerpoint',
            'pptm'         => 'application/vnd.ms-powerpoint.presentation.macroenabled.12',
            'pptx'         => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pqa'          => 'application/vnd.palm',
            'prc'          => 'application/x-mobipocket-ebook',
            'pre'          => 'application/vnd.lotus-freelance',
            'prf'          => 'application/pics-rules',
            'ps'           => 'application/postscript',
            'psb'          => 'application/vnd.3gpp.pic-bw-small',
            'psd'          => 'image/vnd.adobe.photoshop',
            'psf'          => 'application/x-font-linux-psf',
            'pskcxml'      => 'application/pskc+xml',
            'ptid'         => 'application/vnd.pvi.ptid1',
            'pub'          => 'application/x-mspublisher',
            'pvb'          => 'application/vnd.3gpp.pic-bw-var',
            'pwn'          => 'application/vnd.3m.post-it-notes',
            'pya'          => 'audio/vnd.ms-playready.media.pya',
            'pyv'          => 'video/vnd.ms-playready.media.pyv',
            'qam'          => 'application/vnd.epson.quickanime',
            'qbo'          => 'application/vnd.intu.qbo',
            'qfx'          => 'application/vnd.intu.qfx',
            'qps'          => 'application/vnd.publishare-delta-tree',
            'qt'           => 'video/quicktime',
            'qwd'          => 'application/vnd.quark.quarkxpress',
            'qwt'          => 'application/vnd.quark.quarkxpress',
            'qxb'          => 'application/vnd.quark.quarkxpress',
            'qxd'          => 'application/vnd.quark.quarkxpress',
            'qxl'          => 'application/vnd.quark.quarkxpress',
            'qxt'          => 'application/vnd.quark.quarkxpress',
            'ra'           => 'audio/x-pn-realaudio',
            'ram'          => 'audio/x-pn-realaudio',
            'rar'          => 'application/rar',
            'ras'          => 'image/x-cmu-raster',
            'rcprofile'    => 'application/vnd.ipunplugged.rcprofile',
            'rdf'          => 'application/rdf+xml',
            'rdz'          => 'application/vnd.data-vision.rdz',
            'rep'          => 'application/vnd.businessobjects',
            'res'          => 'application/x-dtbresource+xml',
            'rgb'          => 'image/x-rgb',
            'rif'          => 'application/reginfo+xml',
            'rip'          => 'audio/vnd.rip',
            'ris'          => 'application/x-research-info-systems',
            'rl'           => 'application/resource-lists+xml',
            'rlc'          => 'image/vnd.fujixerox.edmics-rlc',
            'rld'          => 'application/resource-lists-diff+xml',
            'rm'           => 'application/vnd.rn-realmedia',
            'rmi'          => 'audio/midi',
            'rmp'          => 'audio/x-pn-realaudio-plugin',
            'rms'          => 'application/vnd.jcp.javame.midlet-rms',
            'rmvb'         => 'application/vnd.rn-realmedia-vbr',
            'rnc'          => 'application/relax-ng-compact-syntax',
            'roa'          => 'application/rpki-roa',
            'roff'         => 'text/troff',
            'rp9'          => 'application/vnd.cloanto.rp9',
            'rpss'         => 'application/vnd.nokia.radio-presets',
            'rpst'         => 'application/vnd.nokia.radio-preset',
            'rq'           => 'application/sparql-query',
            'rs'           => 'application/rls-services+xml',
            'rsd'          => 'application/rsd+xml',
            'rss'          => 'application/rss+xml',
            'rtf'          => 'application/rtf',
            'rtx'          => 'text/richtext',
            's'            => 'text/x-asm',
            's3m'          => 'audio/s3m',
            'saf'          => 'application/vnd.yamaha.smaf-audio',
            'sbml'         => 'application/sbml+xml',
            'sc'           => 'application/vnd.ibm.secure-container',
            'scd'          => 'application/x-msschedule',
            'scm'          => 'application/vnd.lotus-screencam',
            'scq'          => 'application/scvp-cv-request',
            'scs'          => 'application/scvp-cv-response',
            'scurl'        => 'text/vnd.curl.scurl',
            'sda'          => 'application/vnd.stardivision.draw',
            'sdc'          => 'application/vnd.stardivision.calc',
            'sdd'          => 'application/vnd.stardivision.impress',
            'sdkd'         => 'application/vnd.solent.sdkm+xml',
            'sdkm'         => 'application/vnd.solent.sdkm+xml',
            'sdp'          => 'application/sdp',
            'sdw'          => 'application/vnd.stardivision.writer',
            'see'          => 'application/vnd.seemail',
            'seed'         => 'application/vnd.fdsn.seed',
            'sema'         => 'application/vnd.sema',
            'semd'         => 'application/vnd.semd',
            'semf'         => 'application/vnd.semf',
            'ser'          => 'application/java-serialized-object',
            'setpay'       => 'application/set-payment-initiation',
            'setreg'       => 'application/set-registration-initiation',
            'sfd-hdstx'    => 'application/vnd.hydrostatix.sof-data',
            'sfs'          => 'application/vnd.spotfire.sfs',
            'sfv'          => 'text/x-sfv',
            'sgi'          => 'image/sgi',
            'sgl'          => 'application/vnd.stardivision.writer-global',
            'sgm'          => 'text/sgml',
            'sgml'         => 'text/sgml',
            'sh'           => 'application/x-sh',
            'shar'         => 'application/x-shar',
            'shf'          => 'application/shf+xml',
            'sid'          => 'image/x-mrsid-image',
            'sig'          => 'application/pgp-signature',
            'sil'          => 'audio/silk',
            'silo'         => 'model/mesh',
            'sis'          => 'application/vnd.symbian.install',
            'sisx'         => 'application/vnd.symbian.install',
            'sit'          => 'application/x-stuffit',
            'sitx'         => 'application/x-stuffitx',
            'skd'          => 'application/vnd.koan',
            'skm'          => 'application/vnd.koan',
            'skp'          => 'application/vnd.koan',
            'skt'          => 'application/vnd.koan',
            'sldm'         => 'application/vnd.ms-powerpoint.slide.macroenabled.12',
            'sldx'         => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'slt'          => 'application/vnd.epson.salt',
            'sm'           => 'application/vnd.stepmania.stepchart',
            'smf'          => 'application/vnd.stardivision.math',
            'smi'          => 'application/smil+xml',
            'smil'         => 'application/smil+xml',
            'smv'          => 'video/x-smv',
            'smzip'        => 'application/vnd.stepmania.package',
            'snd'          => 'audio/basic',
            'snf'          => 'application/x-font-snf',
            'so'           => 'application/octet-stream',
            'spc'          => 'application/x-pkcs7-certificates',
            'spf'          => 'application/vnd.yamaha.smaf-phrase',
            'spl'          => 'application/x-futuresplash',
            'spot'         => 'text/vnd.in3d.spot',
            'spp'          => 'application/scvp-vp-response',
            'spq'          => 'application/scvp-vp-request',
            'spx'          => 'audio/ogg',
            'sql'          => 'application/x-sql',
            'src'          => 'application/x-wais-source',
            'srt'          => 'application/x-subrip',
            'sru'          => 'application/sru+xml',
            'srx'          => 'application/sparql-results+xml',
            'ssdl'         => 'application/ssdl+xml',
            'sse'          => 'application/vnd.kodak-descriptor',
            'ssf'          => 'application/vnd.epson.ssf',
            'ssml'         => 'application/ssml+xml',
            'st'           => 'application/vnd.sailingtracker.track',
            'stc'          => 'application/vnd.sun.xml.calc.template',
            'std'          => 'application/vnd.sun.xml.draw.template',
            'stf'          => 'application/vnd.wt.stf',
            'sti'          => 'application/vnd.sun.xml.impress.template',
            'stk'          => 'application/hyperstudio',
            'stl'          => 'application/vnd.ms-pki.stl',
            'str'          => 'application/vnd.pg.format',
            'stw'          => 'application/vnd.sun.xml.writer.template',
            'sub'          => 'text/vnd.dvb.subtitle',
            'sus'          => 'application/vnd.sus-calendar',
            'susp'         => 'application/vnd.sus-calendar',
            'sv4cpio'      => 'application/x-sv4cpio',
            'sv4crc'       => 'application/x-sv4crc',
            'svc'          => 'application/vnd.dvb.service',
            'svd'          => 'application/vnd.svd',
            'svg'          => 'image/svg+xml',
            'svgz'         => 'image/svg+xml',
            'swa'          => 'application/x-director',
            'swf'          => 'application/x-shockwave-flash',
            'swi'          => 'application/vnd.aristanetworks.swi',
            'sxc'          => 'application/vnd.sun.xml.calc',
            'sxd'          => 'application/vnd.sun.xml.draw',
            'sxg'          => 'application/vnd.sun.xml.writer.global',
            'sxi'          => 'application/vnd.sun.xml.impress',
            'sxm'          => 'application/vnd.sun.xml.math',
            'sxw'          => 'application/vnd.sun.xml.writer',
            't'            => 'text/troff',
            't3'           => 'application/x-t3vm-image',
            'taglet'       => 'application/vnd.mynfc',
            'tao'          => 'application/vnd.tao.intent-module-archive',
            'tar'          => 'application/x-tar',
            'tcap'         => 'application/vnd.3gpp2.tcap',
            'tcl'          => 'application/x-tcl',
            'teacher'      => 'application/vnd.smart.teacher',
            'tei'          => 'application/tei+xml',
            'teicorpus'    => 'application/tei+xml',
            'tex'          => 'application/x-tex',
            'texi'         => 'application/x-texinfo',
            'texinfo'      => 'application/x-texinfo',
            'text'         => 'text/plain',
            'tfi'          => 'application/thraud+xml',
            'tfm'          => 'application/x-tex-tfm',
            'tga'          => 'image/x-tga',
            'thmx'         => 'application/vnd.ms-officetheme',
            'tif'          => 'image/tiff',
            'tiff'         => 'image/tiff',
            'tmo'          => 'application/vnd.tmobile-livetv',
            'torrent'      => 'application/x-bittorrent',
            'tpl'          => 'application/vnd.groove-tool-template',
            'tpt'          => 'application/vnd.trid.tpt',
            'tr'           => 'text/troff',
            'tra'          => 'application/vnd.trueapp',
            'trm'          => 'application/x-msterminal',
            'ts'           => 'video/MP2T',
            'tsd'          => 'application/timestamped-data',
            'tsv'          => 'text/tab-separated-values',
            'ttc'          => 'application/x-font-ttf',
            'ttf'          => 'application/x-font-ttf',
            'ttl'          => 'text/turtle',
            'twd'          => 'application/vnd.simtech-mindmapper',
            'twds'         => 'application/vnd.simtech-mindmapper',
            'txd'          => 'application/vnd.genomatix.tuxedo',
            'txf'          => 'application/vnd.mobius.txf',
            'txt'          => 'text/plain',
            'u32'          => 'application/x-authorware-bin',
            'udeb'         => 'application/x-debian-package',
            'ufd'          => 'application/vnd.ufdl',
            'ufdl'         => 'application/vnd.ufdl',
            'ulx'          => 'application/x-glulx',
            'umj'          => 'application/vnd.umajin',
            'unityweb'     => 'application/vnd.unity',
            'uoml'         => 'application/vnd.uoml+xml',
            'uri'          => 'text/uri-list',
            'uris'         => 'text/uri-list',
            'urls'         => 'text/uri-list',
            'ustar'        => 'application/x-ustar',
            'utz'          => 'application/vnd.uiq.theme',
            'uu'           => 'text/x-uuencode',
            'uva'          => 'audio/vnd.dece.audio',
            'uvd'          => 'application/vnd.dece.data',
            'uvf'          => 'application/vnd.dece.data',
            'uvg'          => 'image/vnd.dece.graphic',
            'uvh'          => 'video/vnd.dece.hd',
            'uvi'          => 'image/vnd.dece.graphic',
            'uvm'          => 'video/vnd.dece.mobile',
            'uvp'          => 'video/vnd.dece.pd',
            'uvs'          => 'video/vnd.dece.sd',
            'uvt'          => 'application/vnd.dece.ttml+xml',
            'uvu'          => 'video/vnd.uvvu.mp4',
            'uvv'          => 'video/vnd.dece.video',
            'uvva'         => 'audio/vnd.dece.audio',
            'uvvd'         => 'application/vnd.dece.data',
            'uvvf'         => 'application/vnd.dece.data',
            'uvvg'         => 'image/vnd.dece.graphic',
            'uvvh'         => 'video/vnd.dece.hd',
            'uvvi'         => 'image/vnd.dece.graphic',
            'uvvm'         => 'video/vnd.dece.mobile',
            'uvvp'         => 'video/vnd.dece.pd',
            'uvvs'         => 'video/vnd.dece.sd',
            'uvvt'         => 'application/vnd.dece.ttml+xml',
            'uvvu'         => 'video/vnd.uvvu.mp4',
            'uvvv'         => 'video/vnd.dece.video',
            'uvvx'         => 'application/vnd.dece.unspecified',
            'uvvz'         => 'application/vnd.dece.zip',
            'uvx'          => 'application/vnd.dece.unspecified',
            'uvz'          => 'application/vnd.dece.zip',
            'vcard'        => 'text/vcard',
            'vcd'          => 'application/x-cdlink',
            'vcf'          => 'text/x-vcard',
            'vcg'          => 'application/vnd.groove-vcard',
            'vcs'          => 'text/x-vcalendar',
            'vcx'          => 'application/vnd.vcx',
            'vis'          => 'application/vnd.visionary',
            'viv'          => 'video/vnd.vivo',
            'vob'          => 'video/x-ms-vob',
            'vor'          => 'application/vnd.stardivision.writer',
            'vox'          => 'application/x-authorware-bin',
            'vrml'         => 'model/vrml',
            'vsd'          => 'application/vnd.visio',
            'vsf'          => 'application/vnd.vsf',
            'vss'          => 'application/vnd.visio',
            'vst'          => 'application/vnd.visio',
            'vsw'          => 'application/vnd.visio',
            'vtt'          => 'text/vtt',
            'vtu'          => 'model/vnd.vtu',
            'vxml'         => 'application/voicexml+xml',
            'w3d'          => 'application/x-director',
            'wad'          => 'application/x-doom',
            'wav'          => 'audio/wav',
            'wax'          => 'audio/x-ms-wax',
            'wbmp'         => 'image/vnd.wap.wbmp',
            'wbs'          => 'application/vnd.criticaltools.wbs+xml',
            'wbxml'        => 'application/vnd.wap.wbxml',
            'wcm'          => 'application/vnd.ms-works',
            'wdb'          => 'application/vnd.ms-works',
            'wdp'          => 'image/vnd.ms-photo',
            'weba'         => 'audio/webm',
            'webapp'       => 'application/x-web-app-manifest+json',
            'webm'         => 'video/webm',
            'webp'         => 'image/webp',
            'wg'           => 'application/vnd.pmi.widget',
            'wgt'          => 'application/widget',
            'wks'          => 'application/vnd.ms-works',
            'wm'           => 'video/x-ms-wm',
            'wma'          => 'audio/x-ms-wma',
            'wmd'          => 'application/x-ms-wmd',
            'wmf'          => 'application/x-msmetafile',
            'wml'          => 'text/vnd.wap.wml',
            'wmlc'         => 'application/vnd.wap.wmlc',
            'wmls'         => 'text/vnd.wap.wmlscript',
            'wmlsc'        => 'application/vnd.wap.wmlscriptc',
            'wmv'          => 'video/x-ms-wmv',
            'wmx'          => 'video/x-ms-wmx',
            'wmz'          => 'application/x-msmetafile',
            'woff'         => 'application/x-font-woff',
            'wpd'          => 'application/vnd.wordperfect',
            'wpl'          => 'application/vnd.ms-wpl',
            'wps'          => 'application/vnd.ms-works',
            'wqd'          => 'application/vnd.wqd',
            'wri'          => 'application/x-mswrite',
            'wrl'          => 'model/vrml',
            'wsdl'         => 'application/wsdl+xml',
            'wspolicy'     => 'application/wspolicy+xml',
            'wtb'          => 'application/vnd.webturbo',
            'wvx'          => 'video/x-ms-wvx',
            'x32'          => 'application/x-authorware-bin',
            'x3d'          => 'model/x3d+xml',
            'x3db'         => 'model/x3d+binary',
            'x3dbz'        => 'model/x3d+binary',
            'x3dv'         => 'model/x3d+vrml',
            'x3dvz'        => 'model/x3d+vrml',
            'x3dz'         => 'model/x3d+xml',
            'xaml'         => 'application/xaml+xml',
            'xap'          => 'application/x-silverlight-app',
            'xar'          => 'application/vnd.xara',
            'xbap'         => 'application/x-ms-xbap',
            'xbd'          => 'application/vnd.fujixerox.docuworks.binder',
            'xbm'          => 'image/x-xbitmap',
            'xdf'          => 'application/xcap-diff+xml',
            'xdm'          => 'application/vnd.syncml.dm+xml',
            'xdp'          => 'application/vnd.adobe.xdp+xml',
            'xdssc'        => 'application/dssc+xml',
            'xdw'          => 'application/vnd.fujixerox.docuworks',
            'xenc'         => 'application/xenc+xml',
            'xer'          => 'application/patch-ops-error+xml',
            'xfdf'         => 'application/vnd.adobe.xfdf',
            'xfdl'         => 'application/vnd.xfdl',
            'xht'          => 'application/xhtml+xml',
            'xhtml'        => 'application/xhtml+xml',
            'xhvml'        => 'application/xv+xml',
            'xif'          => 'image/vnd.xiff',
            'xla'          => 'application/vnd.ms-excel',
            'xlam'         => 'application/vnd.ms-excel.addin.macroenabled.12',
            'xlc'          => 'application/vnd.ms-excel',
            'xlf'          => 'application/x-xliff+xml',
            'xlm'          => 'application/vnd.ms-excel',
            'xls'          => 'application/vnd.ms-excel',
            'xlsb'         => 'application/vnd.ms-excel.sheet.binary.macroenabled.12',
            'xlsm'         => 'application/vnd.ms-excel.sheet.macroenabled.12',
            'xlsx'         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlt'          => 'application/vnd.ms-excel',
            'xltm'         => 'application/vnd.ms-excel.template.macroenabled.12',
            'xltx'         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xlw'          => 'application/vnd.ms-excel',
            'xm'           => 'audio/xm',
            'xml'          => 'application/xml',
            'xo'           => 'application/vnd.olpc-sugar',
            'xop'          => 'application/xop+xml',
            'xpi'          => 'application/x-xpinstall',
            'xpl'          => 'application/xproc+xml',
            'xpm'          => 'image/x-xpixmap',
            'xpr'          => 'application/vnd.is-xpr',
            'xps'          => 'application/vnd.ms-xpsdocument',
            'xpw'          => 'application/vnd.intercon.formnet',
            'xpx'          => 'application/vnd.intercon.formnet',
            'xsl'          => 'application/xml',
            'xslt'         => 'application/xslt+xml',
            'xsm'          => 'application/vnd.syncml+xml',
            'xspf'         => 'application/xspf+xml',
            'xul'          => 'application/vnd.mozilla.xul+xml',
            'xvm'          => 'application/xv+xml',
            'xvml'         => 'application/xv+xml',
            'xwd'          => 'image/x-xwindowdump',
            'xyz'          => 'chemical/x-xyz',
            'xz'           => 'application/x-xz',
            'yang'         => 'application/yang',
            'yin'          => 'application/yin+xml',
            'z1'           => 'application/x-zmachine',
            'z2'           => 'application/x-zmachine',
            'z3'           => 'application/x-zmachine',
            'z4'           => 'application/x-zmachine',
            'z5'           => 'application/x-zmachine',
            'z6'           => 'application/x-zmachine',
            'z7'           => 'application/x-zmachine',
            'z8'           => 'application/x-zmachine',
            'zaz'          => 'application/vnd.zzazz.deck+xml',
            'zip'          => 'application/zip',
            'zir'          => 'application/vnd.zul',
            'zirz'         => 'application/vnd.zul',
            'zmm'          => 'application/vnd.handheld-entertainment+xml'
        );

        /* Add Google Mimetypes */
        $mime_types_map['gdoc']    = 'application/vnd.google-apps.document';
        $mime_types_map['gslides'] = 'application/vnd.google-apps.presentation';
        $mime_types_map['gsheet']  = 'application/vnd.google-apps.spreadsheet';
        $mime_types_map['gdraw']   = 'application/vnd.google-apps.drawing';
        $mime_types_map['gtable']  = 'application/vnd.google-apps.fusiontable';
        $mime_types_map['gform']   = 'application/vnd.google-apps.form';

        if (isset($mime_types_map[$extension])) {
            return $mime_types_map[$extension];
        } else {
            return 'application/octet-stream';
        }
    }

    /**
     * Check is folder active for post type
     *
     * @param string $post_type Post type name
     *
     * @return boolean
     */
    public static function isForThisPostType($post_type)
    {
        $settings         = get_option('wpmf_settings');
        if (isset($settings) && isset($settings['wpmf_active_folders_post_types'])) {
            $post_types = $settings['wpmf_active_folders_post_types'];
            $post_types = is_array($post_types) ? $post_types : array();
        } else {
            $post_types = array();
        }

        return in_array($post_type, $post_types);
    }

    /**
     * Check cloud connected
     *
     * @param string $cloud_type Cloud type
     *
     * @return boolean
     */
    public static function isCloudConnected($cloud_type)
    {
        $connected = false;
        switch ($cloud_type) {
            case 'google_drive':
                $options = get_option('_wpmfAddon_cloud_config');
                if (!empty($options['connected'])) {
                    $connected = true;
                }
                break;
            case 'google_photo':
                $options = get_option('_wpmfAddon_google_photo_config');
                if (!empty($options['googleCredentials'])) {
                    $connected = true;
                }
                break;
            case 'dropbox':
                $options = get_option('_wpmfAddon_dropbox_config');
                if (!empty($options['dropboxToken'])) {
                    $connected = true;
                }
                break;
            case 'onedrive':
                $options = get_option('_wpmfAddon_onedrive_config');
                if (!empty($options['connected'])) {
                    $connected = true;
                }
                break;
            case 'onedrive_business':
                $options = get_option('_wpmfAddon_onedrive_business_config');
                if (!empty($options['connected'])) {
                    $connected = true;
                }
                break;
            case 'nextcloud':
                $options = get_option('_wpmfAddon_nextcloud_config');
                $connect_nextcloud = self::wpmfGetOption('connect_nextcloud');
                if (!empty($options['username']) && !empty($options['password']) && !empty($options['nextcloudurl']) && !empty($options['rootfoldername']) && !empty($connect_nextcloud)) {
                    $connected = true;
                }
                break;
            case 'owncloud':
                $options = get_option('_wpmfAddon_owncloud_config');
                $connect_owncloud = self::wpmfGetOption('connect_owncloud');
                if (!empty($options['username']) && !empty($options['password']) && !empty($options['owncloudurl']) && !empty($options['rootfoldername']) && !empty($connect_owncloud)) {
                    $connected = true;
                }
                break;
        }

        return $connected;
    }

    /**
     * Check if Network Media Library plugin is active (MU or normal plugin).
     *
     * @return boolean True if the plugin is active and multisite is enabled, false otherwise.
     */
    public static function isNetworkMediaLibraryActive()
    {
        if (!is_multisite()) {
            return false;
        }

        if (!function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        // Check MU plugin
        $mu_direct = file_exists(WPMU_PLUGIN_DIR . '/network-media-library.php');
        $mu_sub = glob(WPMU_PLUGIN_DIR . '/*/network-media-library.php');
        $is_mu = $mu_direct || !empty($mu_sub);

        // Check normal plugin
        $plugins = get_plugins();
        $is_active = false;

        foreach ($plugins as $path => $plugin) {
            if (strpos($path, 'network-media-library.php') !== false) {
                if (is_plugin_active($path) || is_plugin_active_for_network($path)) {
                    $is_active = true;
                    break;
                }
            }
        }

        return (is_multisite() && ($is_mu || $is_active));
    }

    /**
     * Get main site ID for shared media in multisite.
     *
     * This value is filterable via 'network-media-library/site_id'.
     *
     * @return integer Main site ID.
     */
    public static function getMainSiteId()
    {
        return (int) apply_filters('network-media-library/site_id', 2);
    }
}
