<?php

namespace DevOwl\RealCookieBanner\lite\rest;

use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Service;
use DevOwl\RealCookieBanner\base\UtilsProvider;
use DevOwl\RealCookieBanner\Core;
use DevOwl\RealCookieBanner\lite\settings\TcfVendorConfiguration;
use DevOwl\RealCookieBanner\settings\Blocker;
use DevOwl\RealCookieBanner\settings\TCF as SettingsTCF;
use WP_REST_Response;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Handle TCF compatibility REST services.
 * @internal
 */
class TCF
{
    use UtilsProvider;
    /**
     * C'tor.
     */
    private function __construct()
    {
        // Silence is golden.
    }
    /**
     * Register endpoints.
     */
    public function rest_api_init()
    {
        $namespace = Service::getNamespace($this);
        \register_rest_route($namespace, '/tcf/declarations', ['methods' => 'GET', 'callback' => [$this, 'routeDeclarations'], 'permission_callback' => [$this, 'permission_callback']]);
        \register_rest_route($namespace, '/tcf/vendors', ['methods' => 'GET', 'callback' => [$this, 'routeVendors'], 'permission_callback' => [$this, 'permission_callback']]);
        \register_rest_route($namespace, '/tcf/gvl', ['methods' => 'PUT', 'callback' => [$this, 'routeGvlPut'], 'permission_callback' => [$this, 'permission_callback']]);
    }
    /**
     * Check if user is allowed to call this service requests.
     */
    public function permission_callback()
    {
        return \current_user_can(Core::MANAGE_MIN_CAPABILITY);
    }
    /**
     * See API docs.
     *
     * @api {get} /real-cookie-banner/v1/tcf/declarations
     * @apiHeader {string} X-WP-Nonce
     * @apiName GetDeclarations
     * @apiGroup TCF
     * @apiPermission Pro only
     * @apiVersion 1.0.0
     */
    public function routeDeclarations()
    {
        return new WP_REST_Response(Core::getInstance()->getTcfVendorListNormalizer()->getQuery()->allDeclarations());
    }
    /**
     * See API docs.
     *
     * @api {get} /real-cookie-banner/v1/tcf/vendors
     * @apiHeader {string} X-WP-Nonce
     * @apiName GetVendors
     * @apiGroup TCF
     * @apiPermission Pro only
     * @apiVersion 1.0.0
     */
    public function routeVendors()
    {
        return new WP_REST_Response(Core::getInstance()->getTcfVendorListNormalizer()->getQuery()->vendors());
    }
    /**
     * See API docs.
     *
     * @api {put} /real-cookie-banner/v1/tcf/gvl
     * @apiHeader {string} X-WP-Nonce
     * @apiName UpdateGvl
     * @apiGroup TCF
     * @apiPermission Pro only
     * @apiVersion 1.0.0
     */
    public function routeGvlPut()
    {
        $result = SettingsTCF::getInstance()->updateGvl();
        return \is_wp_error($result) ? $result : new WP_REST_Response(['gvlDownloadTime' => \mysql2date('c', SettingsTCF::getInstance()->getGvlDownloadTime(), \false)]);
    }
    /**
     * Modify the response of the TCF vendor configuration Custom Post Type:
     *
     * - Vendor model
     * - Blocker ID covering this TCF Vendor configuration
     *
     * @param WP_REST_Response $response The response object.
     * @param WP_Post $post Post object.
     */
    public function rest_prepare_vendor($response, $post)
    {
        $vendorId = \get_post_meta($post->ID, TcfVendorConfiguration::META_NAME_VENDOR_ID, \true);
        if (!empty($vendorId)) {
            $vendor = Core::getInstance()->getTcfVendorListNormalizer()->getQuery()->vendor($vendorId);
            $response->data['vendor'] = $vendor;
        }
        // Expand blocker ID
        $blocker = \get_posts(Core::getInstance()->queryArguments(['post_type' => Blocker::CPT_NAME, 'posts_per_page' => 1, 'fields' => 'ids', 'meta_query' => [['key' => Blocker::META_NAME_TCF_VENDORS, 'value' => $post->ID, 'compare' => 'find_in_set']]], 'tcfVendorConfigurationRestPrepare'));
        $response->data['blocker'] = \count($blocker) > 0 ? $blocker[0] : \false;
        return $response;
    }
    /**
     * New instance.
     */
    public static function instance()
    {
        return new \DevOwl\RealCookieBanner\lite\rest\TCF();
    }
}
