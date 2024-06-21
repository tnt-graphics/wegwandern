<?php

namespace DevOwl\RealCookieBanner\lite\settings;

use DevOwl\RealCookieBanner\base\UtilsProvider;
use DevOwl\RealCookieBanner\Core;
use DevOwl\RealCookieBanner\lite\tcf\Persist as TcfPersist;
use DevOwl\RealCookieBanner\settings\Cookie;
use DevOwl\RealCookieBanner\settings\Revision;
use DevOwl\RealCookieBanner\Vendor\DevOwl\TcfVendorListNormalize\Persist;
use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Utils;
use WP_Error;
use WP_Post;
use WP_Query;
use WP_REST_Posts_Controller;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Register TCF vendor configuration custom post type.
 * @internal
 */
class TcfVendorConfiguration
{
    use UtilsProvider;
    const CPT_NAME = 'rcb-tcf-vendor-conf';
    // Post type names must be between 1 and 20 characters in length
    const META_NAME_VENDOR_ID = 'vendorId';
    const META_NAME_RESTRICTIVE_PURPOSES = 'restrictivePurposes';
    const META_NAME_DATA_PROCESSING_IN_COUNTRIES = Cookie::META_NAME_DATA_PROCESSING_IN_COUNTRIES;
    const META_NAME_DATA_PROCESSING_IN_COUNTRIES_SPECIAL_TREATMENTS = Cookie::META_NAME_DATA_PROCESSING_IN_COUNTRIES_SPECIAL_TREATMENTS;
    const SYNC_OPTIONS_COPY_AND_COPY_ONCE = [\DevOwl\RealCookieBanner\lite\settings\TcfVendorConfiguration::META_NAME_VENDOR_ID, \DevOwl\RealCookieBanner\lite\settings\TcfVendorConfiguration::META_NAME_RESTRICTIVE_PURPOSES, \DevOwl\RealCookieBanner\lite\settings\TcfVendorConfiguration::META_NAME_DATA_PROCESSING_IN_COUNTRIES, \DevOwl\RealCookieBanner\lite\settings\TcfVendorConfiguration::META_NAME_DATA_PROCESSING_IN_COUNTRIES_SPECIAL_TREATMENTS];
    const SYNC_OPTIONS = ['meta' => ['copy' => \DevOwl\RealCookieBanner\lite\settings\TcfVendorConfiguration::SYNC_OPTIONS_COPY_AND_COPY_ONCE, 'copy-once' => \DevOwl\RealCookieBanner\lite\settings\TcfVendorConfiguration::SYNC_OPTIONS_COPY_AND_COPY_ONCE]];
    const META_KEYS = [\DevOwl\RealCookieBanner\lite\settings\TcfVendorConfiguration::META_NAME_VENDOR_ID, \DevOwl\RealCookieBanner\lite\settings\TcfVendorConfiguration::META_NAME_RESTRICTIVE_PURPOSES, \DevOwl\RealCookieBanner\lite\settings\TcfVendorConfiguration::META_NAME_DATA_PROCESSING_IN_COUNTRIES, \DevOwl\RealCookieBanner\lite\settings\TcfVendorConfiguration::META_NAME_DATA_PROCESSING_IN_COUNTRIES_SPECIAL_TREATMENTS];
    /**
     * Singleton instance.
     *
     * @var TcfVendorConfiguration
     */
    private static $me = null;
    private $cacheGetOrdered = [];
    /**
     * C'tor.
     */
    private function __construct()
    {
        // Silence is golden.
    }
    /**
     * Register custom post type.
     */
    public function register()
    {
        $labels = ['name' => \__('TCF vendor configurations', RCB_TD), 'singular_name' => \__('TCF vendor configuration', RCB_TD)];
        $args = ['label' => $labels['name'], 'labels' => $labels, 'description' => '', 'public' => \false, 'publicly_queryable' => \false, 'show_ui' => \true, 'show_in_rest' => \true, 'rest_base' => self::CPT_NAME, 'rest_controller_class' => WP_REST_Posts_Controller::class, 'has_archive' => \false, 'show_in_menu' => \false, 'show_in_nav_menus' => \false, 'delete_with_user' => \false, 'exclude_from_search' => \true, 'capabilities' => Cookie::CAPABILITIES, 'map_meta_cap' => \false, 'hierarchical' => \false, 'rewrite' => \false, 'query_var' => \true, 'supports' => ['custom-fields']];
        \register_post_type(self::CPT_NAME, $args);
        \register_meta('post', self::META_NAME_VENDOR_ID, ['object_subtype' => self::CPT_NAME, 'type' => 'number', 'single' => \true, 'show_in_rest' => \true]);
        // This meta is stored as JSON (this shouldn't be done usually - 3rd normal form - but it's ok here)
        \register_meta('post', self::META_NAME_RESTRICTIVE_PURPOSES, ['object_subtype' => self::CPT_NAME, 'type' => 'string', 'single' => \true, 'show_in_rest' => \true]);
        // This meta is stored as JSON (this shouldn't be done usually - 3rd normal form - but it's ok here)
        \register_meta('post', self::META_NAME_DATA_PROCESSING_IN_COUNTRIES, ['object_subtype' => self::CPT_NAME, 'type' => 'string', 'single' => \true, 'show_in_rest' => \true]);
        // This meta is stored as JSON (this shouldn't be done usually - 3rd normal form - but it's ok here)
        \register_meta('post', self::META_NAME_DATA_PROCESSING_IN_COUNTRIES_SPECIAL_TREATMENTS, ['object_subtype' => self::CPT_NAME, 'type' => 'string', 'single' => \true, 'show_in_rest' => \true]);
    }
    /**
     * Get all available configurations ordered.
     *
     * @param boolean $force
     * @param WP_Post[] $usePosts If set, only meta is applied to the passed posts
     * @return WP_Post[]|WP_Error
     */
    public function getOrdered($force = \false, $usePosts = null)
    {
        $context = Revision::getInstance()->getContextVariablesString();
        if ($force === \false && isset($this->cacheGetOrdered[$context]) && $usePosts === null) {
            return $this->cacheGetOrdered[$context];
        }
        $posts = $usePosts === null ? \get_posts(Core::getInstance()->queryArguments(['post_type' => self::CPT_NAME, 'numberposts' => -1, 'nopaging' => \true, 'post_status' => 'publish'], 'tcfVendorConfigurationsGetOrdered')) : $usePosts;
        foreach ($posts as &$post) {
            $post->metas = [];
            foreach (self::META_KEYS as $meta_key) {
                $metaValue = \get_post_meta($post->ID, $meta_key, \true);
                switch ($meta_key) {
                    case self::META_NAME_RESTRICTIVE_PURPOSES:
                    case self::META_NAME_DATA_PROCESSING_IN_COUNTRIES:
                    case self::META_NAME_DATA_PROCESSING_IN_COUNTRIES_SPECIAL_TREATMENTS:
                        $metaValue = Utils::isJson($metaValue, []);
                        break;
                    case self::META_NAME_VENDOR_ID:
                        $metaValue = \intval($metaValue);
                        break;
                    default:
                        break;
                }
                $post->metas[$meta_key] = $metaValue;
            }
        }
        if ($usePosts === null) {
            $this->cacheGetOrdered[$context] = $posts;
        }
        return $posts;
    }
    /**
     * When using `WP_Query` with a search term `s` and search for e.g. "Google", it does not return any vendor configurations
     * as the vendor name is not e.g. the post title. This hooks into the SQL `WHERE` statement and searches vendors by name.
     *
     * @param string[] $clauses
     * @param WP_Query $query
     */
    public function modify_search_query_where($clauses, $query)
    {
        global $wpdb;
        if ($query->is_search() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === self::CPT_NAME) {
            $search_term = $wpdb->esc_like($query->query_vars['s']);
            // or use get_search_query()
            $search_term = '%' . $wpdb->esc_like($search_term) . '%';
            $clauses['join'] .= \sprintf(' LEFT JOIN %s AS postmeta_vendorId ON postmeta_vendorId.post_id = %s.ID AND postmeta_vendorId.meta_key = "%s" ', $wpdb->postmeta, $wpdb->posts, self::META_NAME_VENDOR_ID);
            $clauses['join'] .= \sprintf(' LEFT JOIN %s AS rcb_tcf_vendors ON rcb_tcf_vendors.id = postmeta_vendorId.meta_value ', Core::getInstance()->getTcfVendorListNormalizer()->getTableName(TcfPersist::TABLE_NAME_VENDORS));
            $clauses['where'] = \str_replace(\sprintf("%s.post_title LIKE '", $wpdb->posts), "rcb_tcf_vendors.name LIKE '", $clauses['where']);
        }
        return $clauses;
    }
    /**
     * Get a total count of all TCF vendor configurations.
     *
     * @return int
     */
    public function getAllCount()
    {
        return \array_sum(\array_map('intval', \array_values((array) \wp_count_posts(self::CPT_NAME))));
    }
    /**
     * Get singleton instance.
     *
     * @codeCoverageIgnore
     */
    public static function getInstance()
    {
        return self::$me === null ? self::$me = new \DevOwl\RealCookieBanner\lite\settings\TcfVendorConfiguration() : self::$me;
    }
}
