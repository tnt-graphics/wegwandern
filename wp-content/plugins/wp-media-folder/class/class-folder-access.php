<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
use Joomunited\WPMediaFolder\WpmfHelper;

/**
 * Class WpmfFolderAccess
 */
class WpmfFolderAccess
{
    /**
     * WpmfFolderAccess constructor.
     */
    public function __construct()
    {
        add_action('init', array($this, 'createUserFolder'));
        add_filter('wp_generate_attachment_metadata', array($this, 'autoAddAttachmentToFolder'), 10, 2);
    }

    /**
     * Create user folder
     *
     * @return void
     */
    public function createUserFolder()
    {
        // insert term when user login and enable option 'Display only media by User/User'
        global $current_user;
        $user_roles = $current_user->roles;
        $role = array_shift($user_roles);
        /**
         * Filter check capability of current user when create user folder
         *
         * @param boolean The current user has the given capability
         * @param string  Action name
         *
         * @return boolean
         *
         * @ignore Hook already documented
         */
        $wpmf_capability = apply_filters('wpmf_user_can', current_user_can('upload_files'), 'create_user_folder');
        if (($role !== 'administrator' && $wpmf_capability) || $role === 'employer') {
            $wpmf_create_folder = get_option('wpmf_create_folder');
            $parent = $this->getUserParentFolder();
            $cloud_type = get_term_meta($parent, 'wpmf_drive_type', true);
            $check_term = false;
            if ($wpmf_create_folder === 'user') {
                $slug       = sanitize_title($current_user->data->user_login) . '-wpmf';
                $check_term = get_term_by('slug', $slug, WPMF_TAXO);
            } elseif ($wpmf_create_folder === 'role') {
                $slug       = sanitize_title($role) . '-wpmf-role';
                $check_term = get_term_by('slug', $slug, WPMF_TAXO);
            }

            if (empty($check_term) && !$cloud_type) {
                $this->doCreateUserFolder($current_user->data->user_login, $slug, $parent, $current_user);
            }

            $google_connect = WpmfHelper::isConnected('google_drive');
            $dropbox_connect = WpmfHelper::isConnected('dropbox');
            $onedrive_connect = WpmfHelper::isConnected('onedrive');
            $onedrive_business_connect = WpmfHelper::isConnected('onedrive_business');
            if ($google_connect) {
                $options = get_option('_wpmfAddon_cloud_config');
                if (!empty($options['access_by']) && $options['access_by'] === 'user') {
                    $is_exist = get_term_by('slug', $current_user->data->user_login . '-wpmf-google_drive', WPMF_TAXO);
                    $name = $current_user->data->user_login;
                    $slug = $current_user->data->user_login . '-wpmf-google_drive';
                    $type = 'user';
                } else {
                    $is_exist = get_term_by('slug', $role . '-wpmf-role-google_drive', WPMF_TAXO);
                    $name = $role;
                    $slug = $role . '-wpmf-role-google_drive';
                    $type = 'role';
                }

                if (empty($is_exist)) {
                    $parent = WpmfHelper::getCloudRootFolderID('google_drive');
                    if ($parent) {
                        $this->doCreateUserFolder($name, $slug, $parent, $current_user, $type);
                    }
                }
            }

            if ($dropbox_connect) {
                $options = get_option('_wpmfAddon_dropbox_config');
                if (!empty($options['access_by']) && $options['access_by'] === 'user') {
                    $is_exist = get_term_by('slug', $current_user->data->user_login . '-wpmf-dropbox', WPMF_TAXO);
                    $name = $current_user->data->user_login;
                    $slug = $current_user->data->user_login . '-wpmf-dropbox';
                    $type = 'user';
                } else {
                    $is_exist = get_term_by('slug', $role . '-wpmf-role-dropbox', WPMF_TAXO);
                    $name = $role;
                    $slug = $role . '-wpmf-role-dropbox';
                    $type = 'role';
                }

                if (empty($is_exist)) {
                    $parent = WpmfHelper::getCloudRootFolderID('dropbox');
                    if ($parent) {
                        $this->doCreateUserFolder($name, $slug, $parent, $current_user, $type);
                    }
                }
            }

            if ($onedrive_connect) {
                $options = get_option('_wpmfAddon_onedrive_config');
                if (!empty($options['access_by']) && $options['access_by'] === 'user') {
                    $is_exist = get_term_by('slug', $current_user->data->user_login . '-wpmf-onedrive', WPMF_TAXO);
                    $name = $current_user->data->user_login;
                    $slug = $current_user->data->user_login . '-wpmf-onedrive';
                    $type = 'user';
                } else {
                    $is_exist = get_term_by('slug', $role . '-wpmf-role-onedrive', WPMF_TAXO);
                    $name = $role;
                    $slug = $role . '-wpmf-role-onedrive';
                    $type = 'role';
                }

                if (empty($is_exist)) {
                    $parent = WpmfHelper::getCloudRootFolderID('onedrive');
                    if ($parent) {
                        $this->doCreateUserFolder($name, $slug, $parent, $current_user, $type);
                    }
                }
            }

            if ($onedrive_business_connect) {
                $options = get_option('_wpmfAddon_onedrive_business_config');
                if (!empty($options['access_by']) && $options['access_by'] === 'user') {
                    $is_exist = get_term_by('slug', $current_user->data->user_login . '-wpmf-onedrive_business', WPMF_TAXO);
                    $name = $current_user->data->user_login;
                    $slug = $current_user->data->user_login . '-wpmf-onedrive_business';
                    $type = 'user';
                } else {
                    $is_exist = get_term_by('slug', $role . '-wpmf-role-onedrive_business', WPMF_TAXO);
                    $name = $role;
                    $slug = $role . '-wpmf-role-onedrive_business';
                    $type = 'role';
                }

                if (empty($is_exist)) {
                    $parent = WpmfHelper::getCloudRootFolderID('onedrive_business');
                    if ($parent) {
                        $this->doCreateUserFolder($name, $slug, $parent, $current_user, $type);
                    }
                }
            }
        }
    }

    /**
     * Do create user folder
     *
     * @param string  $name         Folder name
     * @param string  $slug         Folder slug
     * @param integer $parent       Folder parent
     * @param object  $current_user Current_user
     * @param string  $type         Type
     *
     * @return void
     */
    public function doCreateUserFolder($name, $slug, $parent, $current_user, $type = 'user')
    {
        $inserted = wp_insert_term(
            $name,
            WPMF_TAXO,
            array('parent' => $parent, 'slug' => $slug)
        );
        if (!is_wp_error($inserted)) {
            if ($type === 'user') {
                wp_update_term($inserted['term_id'], WPMF_TAXO, array('term_group' => $current_user->data->ID));
                add_term_meta((int)$inserted['term_id'], 'wpmf_folder_user_permissions', array($current_user->data->ID, 'add_media', 'move_media', 'view_folder', 'add_folder', 'update_folder', 'remove_folder', 'view_media', 'remove_media', 'update_media'));
                add_term_meta((int)$inserted['term_id'], 'inherit_folder', 0);
            } else {
                $role = WpmfHelper::getRoles($current_user->data->ID);
                add_term_meta((int)$inserted['term_id'], 'wpmf_folder_role_permissions', array($role, 'add_media', 'move_media', 'view_folder', 'add_folder', 'update_folder', 'remove_folder', 'view_media', 'remove_media', 'update_media'));
                add_term_meta((int)$inserted['term_id'], 'inherit_folder', 0);
            }

            $cloud_type = get_term_meta($parent, 'wpmf_drive_type', true);
            if (!empty($cloud_type)) {
                do_action('wpmf_create_folder', $inserted['term_id'], $name, $parent, array('trigger' => 'media_library_action'));
            }
        }
    }

    /**
     * Auto add attachment to folder
     *
     * @param array   $data    Meta data
     * @param integer $post_id Attachment ID
     *
     * @return array
     */
    public function autoAddAttachmentToFolder($data, $post_id)
    {
        $active_media = get_option('wpmf_active_media');
        if (isset($active_media) && (int) $active_media === 1) {
            $wpmf_create_folder = get_option('wpmf_create_folder');
            if ($wpmf_create_folder === 'user') {
                global $wpdb, $current_user;
                if (!empty($current_user->ID)) {
                    $user_login = $current_user->data->user_login;
                    $user_roles = $current_user->roles;
                    $role = array_shift($user_roles);
                    if ($role === 'employer') {
                        $parent = $this->getUserParentFolder();
                        $user_folder = $wpdb->get_row($wpdb->prepare('SELECT * FROM ' . $wpdb->terms . ' as t INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt ON tt.term_id = t.term_id WHERE t.name = %s AND t.term_group = %d AND tt.parent = %d AND tt.taxonomy = %s', array($user_login, (int) $current_user->ID, (int) $parent, WPMF_TAXO)));
                        if (!empty($user_folder)) {
                            wp_set_object_terms($post_id, $user_folder->term_id, WPMF_TAXO, true);
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Get user parent folder
     *
     * @return integer|mixed|void
     */
    public function getUserParentFolder()
    {
        $wpmf_checkbox_tree = get_option('wpmf_checkbox_tree');
        $parent = 0;
        if (!empty($wpmf_checkbox_tree)) {
            $current_parrent = get_term($wpmf_checkbox_tree, WPMF_TAXO);
            if (!empty($current_parrent)) {
                $parent = $wpmf_checkbox_tree;
            }
        }

        return $parent;
    }

    /**
     * Delete folders created by the user
     *
     * @param integer $user_id User ID
     *
     * @return void
     */
    public static function deleteUserFolders($user_id)
    {
        global $wpdb;

        // Get folders created by the user
        $folders = $wpdb->get_results($wpdb->prepare(
            'SELECT term_id FROM ' . $wpdb->terms . ' WHERE term_group = %d',
            $user_id
        ));

        if (!empty($folders)) {
            foreach ($folders as $folder) {
                wp_delete_term($folder->term_id, WPMF_TAXO);
            }
        }
    }

    /**
     * Delete folders created by deleted users
     *
     * @return void
     */
    public static function deleteFoldersOfDeletedUsers()
    {
        global $wpdb;

        $wpmf_checkbox_tree = get_option('wpmf_checkbox_tree');
        $parent = 0;
        if (!empty($wpmf_checkbox_tree)) {
            $current_parrent = get_term($wpmf_checkbox_tree, WPMF_TAXO);
            if (!empty($current_parrent)) {
                $parent = $wpmf_checkbox_tree;
            }
        }

        $descendant_term_ids = get_term_children($parent, WPMF_TAXO);

        if (empty($descendant_term_ids)) {
            return;
        }
        $descendant_term_ids_str = implode(',', $descendant_term_ids);

        $query = '
            SELECT t.term_id, t.term_group 
            FROM ' . $wpdb->terms . ' t
            INNER JOIN ' . $wpdb->term_taxonomy . ' tt ON t.term_id = tt.term_id
            WHERE tt.taxonomy = %s
            AND t.term_group != 0
            AND tt.term_id IN (' . $descendant_term_ids_str . ')
        ';
        
        $folders = $wpdb->get_results($wpdb->prepare($query, WPMF_TAXO)); // phpcs:ignore

        if (!empty($folders)) {
            foreach ($folders as $folder) {
                // Check if the user still exists
                if (!get_user_by('ID', $folder->term_group)) {
                    // If the user does not exist, delete the folder
                    wp_delete_term($folder->term_id, WPMF_TAXO);
                }
            }
        }
    }

    /**
     * Add admin menu for deleting user folders
     *
     * @return void
     */
    public static function addDeleteUserFoldersMenu()
    {
        add_menu_page(
            esc_html__('WPMF Delete Folders of Deleted Users', 'wpmf'),
            esc_html__('WPMF Delete Folders', 'wpmf'),
            'manage_options',
            'wpmf-delete-user-folders',
            array('WpmfFolderAccess', 'deleteUserFoldersPage')
        );
    }

    /**
     * Callback to render the admin page for deleting folders
     *
     * @return void
     */
    public static function deleteUserFoldersPage()
    {
        // Handle the form submission
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- No action, nonce is not required
        if (isset($_POST['delete_folders'])) {
            self::deleteFoldersOfDeletedUsers();
            echo '<div class="updated"><p>' . esc_html__('All folders of deleted users have been removed.', 'wpmf') . '</p></div>';
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Delete Folders of Deleted Users', 'wpmf'); ?></h1>
            <form method="post" action="">
                <input type="hidden" name="delete_folders" value="1" />
                <p>
                    <input type="submit" class="button-primary" value="<?php echo esc_attr__('Delete Folders', 'wpmf'); ?>" />
                </p>
            </form>
        </div>
        <?php
    }
}
