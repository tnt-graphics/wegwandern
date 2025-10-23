<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
$tabs_data = array(
    array(
        'id'       => 'general',
        'title'    => __('General', 'wpmf'),
        'icon'     => 'home',
        'sub_tabs' => array(
            'additional_features' => __('Main settings', 'wpmf'),
            'media_filtering'     => __('Media filtering', 'wpmf'),
            'folder_settings'      => __('Folder settings', 'wpmf')
        )
    ),
    array(
        'id'       => 'wordpress_gallery',
        'title'    => __('Wordpress Gallery', 'wpmf'),
        'icon'     => 'image',
        'sub_tabs' => array(
            'gallery_features' => __('Gallery features', 'wpmf'),
            'default_settings' => __('Default settings', 'wpmf'),
            'wp_gallery_shortcode' => __('Shortcode', 'wpmf')
        )
    ),
    array(
        'id'       => 'gallery_addon',
        'title'    => __('Galleries Addon', 'wpmf'),
        'icon'     => 'gallery-add',
        'sub_tabs' => array(
            'galleryadd_default_settings' => __('Default settings', 'wpmf'),
            'gallery_shortcode_generator' => __('Shortcode generator', 'wpmf'),
            'gallery_social_sharing'      => __('Social sharing', 'wpmf')
        )
    ),
    array(
        'id'       => 'gallery_photographer_addon',
        'title'    => __('Photographer', 'wpmf'),
        'icon'     => 'photo'
    ),
    array(
        'id'       => 'media_access',
        'title'    => __('Access & design', 'wpmf'),
        'icon'     => 'design-services',
        'sub_tabs' => array(
            'user_media_access' => __('Media access', 'wpmf'),
            'file_design'       => __('File Design', 'wpmf')
        )
    ),
    array(
        'id'       => 'files_folders',
        'title'    => __('Rename & Watermark', 'wpmf'),
        'icon'     => 'abc',
        'sub_tabs' => array(
            'rename_on_upload' => __('Rename on upload', 'wpmf'),
            'watermark'        => __('Watermark', 'wpmf'),
        )
    ),
    array(
        'id'       => 'ai_tools',
        'title'    => __('AI tools', 'wpmf'),
        'icon'     => 'AI',
        'sub_tabs' => array(
            'ai_settings'  => __('Settings', 'wpmf'),
            'ai_subscribe' => __('Subscribe', 'wpmf')
        )
    ),
    array(
        'id'       => 'import_export',
        'title'    => __('Import/Export', 'wpmf'),
        'icon'     => 'import-export',
        'sub_tabs' => array(
            'wordpress_import' => __('Wordpress', 'wpmf'),
            'other_plugin_import' => __('Other plugins', 'wpmf')
        )
    ),
    array(
        'id'       => 'server_sync',
        'title'    => __('Server Folder Sync', 'wpmf'),
        'icon'     => 'sync',
        'sub_tabs' => array(
            'server_folder_sync'   => __('Folder Sync', 'wpmf'),
            'server_sync_settings'   => __('Filters', 'wpmf')
        )
    ),
    array(
        'id'       => 'regenerate_thumbnails',
        'title'    => __('Regenerate Thumb', 'wpmf'),
        'icon'     => 'refresh',
        'sub_tabs' => array()
    ),
    array(
        'id'       => 'physical_server_folders',
        'title'    => __('Physical folders', 'wpmf'),
        'icon'     => 'folder',
        'sub_tabs' => array()
    ),
    array(
        'id'       => 'image_compression',
        'title'    => __('Image compression', 'wpmf'),
        'icon'     => 'img-compression',
        'sub_tabs' => array()
    )
);

if (!is_plugin_active('wp-media-folder-gallery-addon/wp-media-folder-gallery-addon.php')) {
    unset($tabs_data[2]);
    unset($tabs_data[3]);
}

if (is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) {
    $tabs_data[] = array(
        'id'       => 'cloud',
        'title'    => __('Cloud & Media offload', 'wpmf'),
        'icon'     => 'cloud',
        'sub_tabs' => array(
            'cloud_connector'  => __('Cloud connectors', 'wpmf'),
            'storage_provider' => __('Media offload', 'wpmf'),
            'synchronization'  => __('Synchronization', 'wpmf')
        )
    );
}
$tabs_data[] = array(
    'id'       => 'jutranslation',
    'title'    => __('Translation', 'wpmf'),
    'icon'     => 'translate',
    'sub_tabs' => array()
);

$tabs_data[] = array(
    'id' => 'system_license',
    'title' => __('System and License', 'wpmf'),
    'content' => 'system-check',
    'icon' => 'shield',
    'sub_tabs' => array(
        'system_check'      => __('System check', 'wpmf'),
        'account_license'   => __('Account license', 'wpmf')
    )
);

$excluded_tabs = array('files_folders', 'ai_tools', 'image_compression', 'cloud', 'import_export', 'server_sync', 'system_license');

$dropbox_config = get_option('_wpmfAddon_dropbox_config');
$google_config = get_option('_wpmfAddon_cloud_config');
$onedrive_config = get_option('_wpmfAddon_onedrive_config');
$onedrive_business_config = get_option('_wpmfAddon_onedrive_business_config');

?>
<div class="wpmf-settings-wrapper ju-main-wrapper">
    <div class="wpmf-settings-sidebar">
        <div class="wpmf-settings-sidebar-menu">
            <div>
                <div class="wpmf-settings-sidebar-logo">
                    <h2><?php esc_html_e('WP Media Folder', 'wpmf') ?></h2>
                    <img class="wpmf-logo-expanded" src="<?php echo esc_url(WPMF_PLUGIN_URL . 'assets/images/joomunited.svg'); ?>" alt="<?php esc_attr_e('JoomUnited logo', 'wpmf') ?>">
                    <img class="wpmf-logo-collapsed" src="<?php echo esc_url(WPMF_PLUGIN_URL . 'assets/images/joomunited-collapsed.svg'); ?>" alt="<?php esc_attr_e('JoomUnited logo', 'wpmf') ?>">
                </div>
                <div class="wpmf-settings-search">
                    <img class="wpmf-search-icon" src="<?php echo esc_url(WPMF_PLUGIN_URL . 'assets/images/icons/search-icon.svg'); ?>" alt="<?php esc_attr_e('Search icon', 'wpmf') ?>">
                    <div class="wpmf-search-input-wrapper">
                        <input type="text" placeholder="<?php esc_attr_e('Search settings', 'wpmf') ?>" value="">
                    </div>
                </div>
            </div>
            <div class="ju-custom-scroll">
                <ul class="wpmf-settings-sidebar-menu-items">
                    <?php foreach ($tabs_data as $ju_tab) :
                        $has_sub_tabs = !empty($ju_tab['sub_tabs']);
                        ?>
                        <li class="sidebar-menu-item<?php echo $has_sub_tabs ? ' has-submenu' : ''; ?>" <?php if ($has_sub_tabs) {
                            echo 'data-parent-id="' . esc_attr($ju_tab['id']) . '"';
                                                    } ?>>
                            <a <?php if ($has_sub_tabs) :
                                ?>href="javascript:void(0);"<?php
                               else :
                                    ?>href="#<?php echo esc_attr($ju_tab['id']); ?>"<?php
                               endif; ?> class="link-tab">
                                <div>
                                    <span class="ju-sidebar-icon ju-icon-<?php echo esc_attr($ju_tab['icon']) ?>"></span>
                                    <span><?php echo esc_html($ju_tab['title']); ?></span>
                                </div>
                                <?php if ($has_sub_tabs) : ?>
                                    <span class="ju-icon-chevron-down wpmf-toggle-icon"></span>
                                <?php endif; ?>
                            </a>
                            <?php if ($has_sub_tabs) : ?>
                                <div class="sidebar-sub-wrapper">
                                    <ul class="sidebar-sub-menu">
                                        <?php foreach ($ju_tab['sub_tabs'] as $sub_id => $sub_title) : ?>
                                            <li class="sidebar-sub-item">
                                                <a href="#<?php echo esc_attr($sub_id); ?>" class="link-sub-tab">
                                                    <?php echo esc_html($sub_title); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="wpmf-settings-sidebar-toggle">
            <button class="wpmf-sidebar-collapse-btn">
                <span class="ju-icon-collapse"></span>
                <span>Collapse</span>
            </button>
            <button class="wpmf-sidebar-expand-btn">
                <span class="ju-icon-expand"></span>
            </button>
        </div>
    </div>
    <div class="wpmf-settings-content">
        <div id="profiles-container">
            <?php
            if (!get_option('wpmf_cloud_connection_notice', false)) :
                if (!empty($dropbox_config['dropboxToken'])
                    || (!empty($google_config['connected']) && !empty($google_config['googleBaseFolder']))
                    || (!empty($onedrive_config['connected']) && !empty($onedrive_config['onedriveBaseFolder']['id']))
                    || (!empty($onedrive_business_config['connected']) && !empty($onedrive_business_config['onedriveBaseFolder']['id']))) :
                    ?>
                    <div class="error wpmf_cloud_connection_notice" id="wpmf_error">
                        <p><?php esc_html_e('WP Media Folder plugin has updated its cloud connection system, it\'s now fully integrated in the media library. It requires to make a synchronization', 'wpmf') ?>
                            <button class="button button-primary btn-run-sync-cloud" style="margin: 0 5px;">
                                <?php esc_html_e('RUN NOW', 'wpmf') ?><span class="spinner spinner-cloud-sync"
                                                                                 style="display:none; visibility:visible"></span>
                            </button>
                        </p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <form enctype="multipart/form-data" name="form1" action="" method="post">
                <input type="hidden" name="wpmf_nonce"
                       value="<?php echo esc_html(wp_create_nonce('wpmf_nonce')) ?>">
                <?php foreach ($tabs_data as $ju_tab) : ?>
                    <div class="ju-content-wrapper" id="<?php echo esc_attr($ju_tab['id']) ?>" style="display: none">
                        <?php if (!in_array($ju_tab['id'], $excluded_tabs, true)) : ?>
                            <div class="wpmf_width_100 top_bar">
                                <h1><?php echo esc_html($ju_tab['title']) ?></h1>
                                <?php
                                require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/submit_button.php';
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php
                        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- View request, no action
                        if (isset($_POST['btn_wpmf_save']) && $ju_tab['id'] !== 'cloud') {
                            ?>
                            <div class="wpmf_width_100 top_bar saved_infos">
                                <?php
                                require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/saved_info.php';
                                ?>
                            </div>
                            <?php
                        }
                        ?>

                        <?php include_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/' . $ju_tab['id'] . '.php'); ?>
                        <?php
                        require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/submit_button.php';
                        ?>
                    </div>
                <?php endforeach; ?>
                <input type="hidden" class="setting_tab_value" name="setting_tab_value" value="wpmf-general">
            </form>
        </div>
    </div>
</div>

<script>
    (function ($) {
        $(function () {
            jQuery('.wp-color-field-bg').wpColorPicker({width: 180, defaultColor: '#202231'});
            jQuery('.wp-color-field-hv').wpColorPicker({width: 180, defaultColor: '#1c1e2a'});
            jQuery('.wp-color-field-font').wpColorPicker({width: 180, defaultColor: '#f4f6ff'});
            jQuery('.wp-color-field-hvfont').wpColorPicker({width: 180, defaultColor: '#ffffff'});
            jQuery('.wp-color-field-icon-color').wpColorPicker({width: 180, defaultColor: '#f4f6ff'});
            jQuery('.wp-color-field-border-color').wpColorPicker({width: 180, defaultColor: '#f4f6ff'});
        });
    })(jQuery);
</script>