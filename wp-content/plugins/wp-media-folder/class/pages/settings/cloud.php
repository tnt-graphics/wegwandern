<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
$cloud_services = array(
    'google_drive' => array(
        'key' => 'google_drive_box',
        'name' => 'Google Drive',
        'img' => WPMF_PLUGIN_URL . 'assets/images/googledrive.svg',
    ),
    'google_photo' => array(
        'key' => 'google_photo',
        'name' => 'Google Photos',
        'img' => WPMF_PLUGIN_URL . 'assets/images/googlephotos.svg',
    ),
    'dropbox' => array(
        'key' => 'dropbox_box',
        'name' => 'Dropbox',
        'img' => WPMF_PLUGIN_URL . 'assets/images/dropbox.svg',
    ),
    'onedrive' => array(
        'key' => 'one_drive_box',
        'name' => 'OneDrive',
        'img' => WPMF_PLUGIN_URL . 'assets/images/microsoftonedrive.svg',
    ),
    'onedrive_business' => array(
        'key' => 'onedrive_business_box',
        'name' => 'OneDrive Business',
        'img' => WPMF_PLUGIN_URL . 'assets/images/microsoftonedrive.svg',
    ),
    'nextcloud' => array(
        'key' => 'nextcloud',
        'name' => 'Nextcloud',
        'img' => WPMF_PLUGIN_URL . 'assets/images/nextcloud.svg',
    ),
    'owncloud' => array(
        'key' => 'owncloud',
        'name' => 'ownCloud',
        'img' => WPMF_PLUGIN_URL . 'assets/images/owncloud.svg',
    ),
);
?>
<div id="cloud_connector" class="tab-content">
    <div class="wpmf_width_100 top_bar">
        <h1><?php esc_html_e('Cloud connector', 'wpmf'); ?></h1>
    </div>
    <div class="wpmf-cloud-providers">
        <?php foreach ($cloud_services as $key => $cloud) :
            $isConnected = Joomunited\WPMediaFolder\WpmfHelper::isCloudConnected($key);
            $cardClass = 'provider-card' . ($isConnected ? ' connected' : '');
            ?>
            <div class="<?php echo esc_attr($cardClass); ?>" data-tab="<?php echo esc_attr($cloud['key']); ?>">
                <img class="provider-image" src="<?php echo esc_url($cloud['img']); ?>" alt="<?php echo esc_attr($cloud['name']); ?>">
                <div class="provider-info">
                    <span class="provider-name"><?php echo esc_html($cloud['name']); ?></span>
                    <button class="provider-connect-btn" type="button">
                        <?php if ($isConnected) : ?>
                            <span><?php esc_html_e('Connected', 'wpmf') ?></span>
                            <img src="<?php echo esc_url(WPMF_PLUGIN_URL . 'assets/images/icons/check-icon-white.svg') ?>">
                        <?php else : ?>
                            <span><?php esc_html_e('Connect', 'wpmf') ?></span>
                        <?php endif; ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="wpmf-divider"></div>
    <div id="google_drive_box" class="sub-tab-content">
        <div class="wpmf_width_100 top_bar">
            <h2 class="wpmf_left"><?php esc_html_e('Google Drive', 'wpmf') ?></h2>
            <?php
            do_action('cloudconnector_wpmf_display_ggd_connect_button');
            if (isset($googleconfig['googleClientId']) && $googleconfig['googleClientId'] !== ''
                && isset($googleconfig['googleClientSecret']) && $googleconfig['googleClientSecret'] !== '') {
                if (empty($googleconfig['connected'])) {
                    $urlGoogle = $googleDrive->getAuthorisationUrl();
                    ?>
                    <div class="btn_wpmf_saves">
                        <a class="ju-button primary-button waves-effect waves-light btndrive ggd-connector-button" href="#"
                           onclick="window.location.assign('<?php echo esc_html($urlGoogle); ?>','foo','width=600,height=600');return false;">
                            <?php esc_html_e('Connect Google Drive', 'wpmf') ?></a>
                    </div>

                    <?php
                } else {
                    $url_logout = admin_url('options-general.php?page=option-folder&task=wpmf&function=wpmf_gglogout');
                    ?>
                    <div class="btn_wpmf_saves">
                        <a class="ju-button no-background primary-button waves-effect waves-light btndrive ggd-connector-button"
                           href="<?php echo esc_html($url_logout) ?>">
                            <?php esc_html_e('Disconnect Google Drive', 'wpmf') ?></a>
                    </div>
                    <?php
                }
            } else {
                $config_mode = get_option('joom_cloudconnector_wpmf_ggd_connect_mode', 'manual');
                if ($config_mode && $config_mode === 'automatic') {
                    echo '<div class="btn_wpmf_saves"><div class="ggd-connector-button"></div></div>';
                } else {
                    echo '<div class="btn_wpmf_saves"><div class="ggd-connector-button"></div></div>';
                    require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/submit_button.php';
                }
            }
            ?>
        </div>
        <div class="content-box">
            <?php
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- View request, no action
            if (isset($_POST['btn_wpmf_save'])) {
                ?>
                <div class="wpmf_width_100 top_bar saved_infos" style="padding: 20px 0">
                    <?php
                    require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/saved_info.php';
                    ?>
                </div>
                <?php
            }
            ?>

            <div class="wpmf_width_100 ju-settings-option">
                <div class="p-d-20">
                    <?php
                    if (is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) {
                        // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
                        echo $html_tabgoogle;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div id="google_photo" class="sub-tab-content">
        <div class="wpmf_width_100 top_bar">
            <h2 class="wpmf_left"><?php esc_html_e('Google Photos', 'wpmf') ?></h2>
            <?php
            do_action('cloudconnector_wpmf_display_gpt_connect_button');
            if (isset($google_photo_config['googleClientId']) && $google_photo_config['googleClientId'] !== ''
                && isset($google_photo_config['googleClientSecret']) && $google_photo_config['googleClientSecret'] !== '') {
                if (empty($google_photo_config['connected'])) {
                    $urlGooglePhoto = $googlePhoto->getAuthorisationUrl();
                    ?>
                    <div class="btn_wpmf_saves">
                        <a class="ju-button primary-button waves-effect waves-light btndrive gpt-connector-button" href="#"
                           onclick="window.location.assign('<?php echo esc_html($urlGooglePhoto); ?>','foo','width=600,height=600');return false;">
                            <?php esc_html_e('Connect Google Photo', 'wpmf') ?></a>
                    </div>

                    <?php
                } else {
                    ?>
                    <div class="btn_wpmf_saves">
                        <a class="ju-button no-background primary-button waves-effect waves-light btndrive gpt-connector-button"
                           href="<?php echo esc_html(admin_url('options-general.php?page=option-folder&task=wpmf&function=wpmf_google_photo_logout')) ?>">
                            <?php esc_html_e('Disconnect Google Photo', 'wpmf') ?></a>
                    </div>
                    <?php
                }
            } else {
                $config_mode = get_option('joom_cloudconnector_wpmf_gpt_connect_mode', 'manual');
                if ($config_mode && $config_mode === 'automatic') {
                    echo '<div class="btn_wpmf_saves"><div class="gpt-connector-button"></div></div>';
                } else {
                    echo '<div class="btn_wpmf_saves"><div class="gpt-connector-button"></div></div>';
                    require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/submit_button.php';
                }
            }
            ?>
        </div>
        <div class="content-box">
            <?php
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- View request, no action
            if (isset($_POST['btn_wpmf_save'])) {
                ?>
                <div class="wpmf_width_100 top_bar saved_infos" style="padding: 20px 0">
                    <?php
                    require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/saved_info.php';
                    ?>
                </div>
                <?php
            }
            ?>

            <div class="wpmf_width_100 ju-settings-option">
                <div class="p-d-20">
                    <?php
                    if (is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) {
                        // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
                        echo $html_google_photo;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div id="dropbox_box" class="sub-tab-content">
        <div class="wpmf_width_100 top_bar">
            <h2 class="wpmf_left"><?php esc_html_e('Dropbox', 'wpmf') ?></h2>
            <?php
            do_action('cloudconnector_wpmf_display_dropbox_connect_button');
            if (isset($dropboxconfig['dropboxKey']) && $dropboxconfig['dropboxKey'] !== ''
                && isset($dropboxconfig['dropboxSecret']) && $dropboxconfig['dropboxSecret'] !== '') {
                if ($Dropbox->checkAuth()) {
                    try {
                        $urlDropbox = $Dropbox->getAuthorizeDropboxUrl();
                    } catch (Exception $e) {
                        $urlDropbox = '';
                    }
                }
                if ($Dropbox->checkAuth()) {
                    ?>
                    <div class="btn_wpmf_saves">
                        <a class="ju-button primary-button waves-effect waves-light btndrive dropbox-connector-button" href="#"
                           onclick="window.open('<?php echo esc_html($urlDropbox); ?>','foo','width=600,height=600');return false;">
                            <?php esc_html_e('Connect Dropbox', 'wpmf') ?></a>
                    </div>

                    <?php
                } else { ?>
                    <div class="btn_wpmf_saves">
                        <a class="ju-button no-background primary-button waves-effect waves-light btndrive dropbox-connector-button"
                           href="<?php echo esc_html(admin_url('options-general.php?page=option-folder&task=wpmf&function=wpmf_dropboxlogout')) ?>">
                            <?php esc_html_e('Disconnect Dropbox', 'wpmf') ?></a>
                    </div>
                    <?php
                }
            } else {
                $config_mode = get_option('joom_cloudconnector_wpmf_dropbox_connect_mode', 'manual');
                if ($config_mode && $config_mode === 'automatic') {
                    echo '<div class="btn_wpmf_saves"><div class="dropbox-connector-button"></div></div>';
                } else {
                    echo '<div class="btn_wpmf_saves"><div class="dropbox-connector-button"></div></div>';
                    require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/submit_button.php';
                }
            }
            ?>
        </div>
        <div class="content-box">
            <?php
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- View request, no action
            if (isset($_POST['btn_wpmf_save'])) {
                ?>
                <div class="wpmf_width_100 top_bar saved_infos" style="padding: 20px 0">
                    <?php
                    require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/saved_info.php';
                    ?>
                </div>
                <?php
            }
            ?>

            <div class="wpmf_width_100  ju-settings-option">
                <div class="p-d-20">
                    <?php
                    if (is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) {
                        // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
                        echo $html_tabdropbox;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div id="one_drive_box" class="sub-tab-content">
        <div class="wpmf_width_100 top_bar">
            <h2 class="wpmf_left"><?php esc_html_e('OneDrive Personal', 'wpmf') ?></h2>
            <?php
            require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/submit_button.php';
            ?>
        </div>

        <div class="content-box">
            <?php
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- View request, no action
            if (isset($_POST['btn_wpmf_save'])) {
                ?>
                <div class="wpmf_width_100 top_bar saved_infos" style="padding: 20px 0">
                    <?php
                    require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/saved_info.php';
                    ?>
                </div>
                <?php
            }
            ?>

            <div class="wpmf_width_100 ju-settings-option">
                <div class="p-d-20">
                    <?php
                    if (is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) {
                        // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
                        echo $html_onedrive_settings;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div id="onedrive_business_box" class="sub-tab-content">
        <div class="wpmf_width_100 top_bar">
            <h2 class="wpmf_left"><?php esc_html_e('OneDrive Business', 'wpmf') ?></h2>
            <?php
            require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/submit_button.php';
            ?>
        </div>
        <div class="content-box">
            <?php
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- View request, no action
            if (isset($_POST['btn_wpmf_save'])) {
                ?>
                <div class="wpmf_width_100 top_bar saved_infos" style="padding: 20px 0">
                    <?php
                    require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/saved_info.php';
                    ?>
                </div>
                <?php
            }
            ?>

            <div class="wpmf_width_100 ju-settings-option">
                <div class="p-d-20">
                    <?php
                    if (is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) {
                        // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
                        echo $html_onedrive_business_settings;
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>

    <div id="nextcloud" class="sub-tab-content">
        <div class="wpmf_width_100 top_bar">
            <h2 class="wpmf_left"><?php esc_html_e('Nextcloud', 'wpmf') ?></h2>
            <?php
            require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/submit_button.php';
            ?>
        </div>

        <div class="content-box">
            <?php
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- View request, no action
            if (isset($_POST['btn_wpmf_save'])) {
                ?>
                <div class="wpmf_width_100 top_bar saved_infos" style="padding: 20px 0">
                    <?php
                    require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/saved_info.php';
                    ?>
                </div>
                <?php
            }
            ?>

            <div class="wpmf_width_100 ju-settings-option">
                <div class="p-d-20">
                    <?php
                    if (is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) {
                        // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
                        echo $html_nextcloud;
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="owncloud" class="sub-tab-content">
    <div class="wpmf_width_100 p-tb-20 wpmf_left top_bar">
        <h1 class="wpmf_left"><?php esc_html_e('ownCloud', 'wpmf') ?></h1>
        <?php
        require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/submit_button.php';
        ?>
    </div>

    <div class="content-box">
        <?php
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- View request, no action
        if (isset($_POST['btn_wpmf_save'])) {
            ?>
            <div class="wpmf_width_100 top_bar saved_infos" style="padding: 20px 0">
                <?php
                require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/saved_info.php';
                ?>
            </div>
            <?php
        }
        ?>

        <div class="wpmf_width_100 ju-settings-option">
            <div class="p-d-20">
                <?php
                if (is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) {
                    // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
                    echo $html_owncloud;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div id="storage_provider" class="tab-content">
    <div class="wpmf_width_100 top_bar wp_storage">
        <?php
            $clouds = array(
                'aws3' => array(
                    'key' => 'aws3',
                    'name' => 'Amazon S3',
                    'img' => WPMFAD_PLUGIN_URL . 'assets/images/AWS-cloud-storage.png',
                ),
                'digitalocean' => array(
                    'key' => 'digitalocean',
                    'name' => 'DigitalOcean',
                    'img' => WPMFAD_PLUGIN_URL . 'assets/images/digitalocean-cloud-storage.png',
                ),
                'wasabi' => array(
                    'key' => 'wasabi',
                    'name' => 'Wasabi',
                    'img' => WPMFAD_PLUGIN_URL . 'assets/images/wasabi-cloud-storage.png',
                ),
                'linode' => array(
                    'key' => 'linode',
                    'name' => 'Linode',
                    'img' => WPMFAD_PLUGIN_URL . 'assets/images/linode-cloud-storage.png',
                ),
                'google_cloud_storage' => array(
                    'key' => 'google_cloud_storage',
                    'name' => 'Google Cloud Storage',
                    'img' => WPMFAD_PLUGIN_URL . 'assets/images/google-cloud-storage.png',
                ),
                'cloudflare_r2' => array(
                    'key' => 'cloudflare_r2',
                    'name' => 'Cloudflare R2',
                    'img' => WPMFAD_PLUGIN_URL . 'assets/images/cloudflare_r2.png',
                ),
                'bunny' => array(
                    'key' => 'bunny',
                    'name' => 'Bunny Storage',
                    'img' => WPMFAD_PLUGIN_URL . 'assets/images/bunny.png',
                )
            );

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
            if (isset($_GET['cloud'])) {
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No action, nonce is not required
                $storage = $_GET['cloud'];
            } else {
                $storage = get_option('wpmf_cloud_endpoint');
                if (empty($storage)) {
                    $storage = 'aws3';
                }
            }
            ?>
        <h1><?php esc_html_e('Storage provider', 'wpmf'); ?></h1>
        <?php require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/submit_button.php'; ?>
    </div>
    <div class="content-box content-wpmf-general">
        <?php
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- View request, no action
        if (isset($_POST['btn_wpmf_save'])) {
            ?>
            <div class="wpmf_width_100 top_bar saved_infos" style="padding: 20px 0">
                <?php
                require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/saved_info.php';
                ?>
            </div>
            <?php
        }
        ?>

        <div>
            <div class="wpmf_row_full">
                <?php
                if (is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) {
                    // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
                    echo $html_tabaws3;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<div id="synchronization" class="tab-content">
    <div class="wpmf_width_100 top_bar">
        <h1 class="wpmf_left"><?php esc_html_e('Synchronization', 'wpmf') ?></h1>
        <?php
        require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/submit_button.php';
        ?>
    </div>
    <div class="content-box content-wpmf-general">
        <?php
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- View request, no action
        if (isset($_POST['btn_wpmf_save'])) {
            ?>
            <div class="wpmf_width_100 top_bar saved_infos" style="padding: 20px 0">
                <?php
                require WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/settings/saved_info.php';
                ?>
            </div>
            <?php
        }
        ?>

        <div>
            <div class="wpmf_row_full">
                <?php
                if (is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) {
                    // phpcs:ignore WordPress.Security.EscapeOutput -- Content already escaped in the method
                    echo $synchronization;
                }
                ?>
            </div>
        </div>
    </div>
</div>