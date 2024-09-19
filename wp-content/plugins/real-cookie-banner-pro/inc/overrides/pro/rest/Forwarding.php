<?php

namespace DevOwl\RealCookieBanner\lite\rest;

use DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\consent\Transaction;
use DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\settings\AbstractMultisite;
use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Service;
use DevOwl\RealCookieBanner\base\UtilsProvider;
use DevOwl\RealCookieBanner\Core;
use DevOwl\RealCookieBanner\IpHandler;
use DevOwl\RealCookieBanner\MyConsent;
use DevOwl\RealCookieBanner\settings\Cookie;
use DevOwl\RealCookieBanner\settings\Multisite;
use DevOwl\RealCookieBanner\UserConsent;
use WP_Error;
use WP_REST_Response;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Handle consent forwarding REST services.
 * @internal
 */
class Forwarding
{
    use UtilsProvider;
    const ENDPOINT_CONSENT_FORWARD = 'consent/forward';
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
        \register_rest_route($namespace, '/' . self::ENDPOINT_CONSENT_FORWARD, ['methods' => 'POST', 'callback' => [$this, 'routePost'], 'permission_callback' => '__return_true', 'args' => [
            'uuid' => ['type' => 'string', 'required' => \true],
            'consentId' => ['type' => 'number', 'required' => \true],
            // Also ported to wp-api/consent.post.tsx
            'buttonClicked' => ['type' => 'string', 'enum' => UserConsent::CLICKABLE_BUTTONS, 'required' => \true],
            // Content Blocker used?
            'blocker' => ['type' => 'boolean', 'default' => \false],
            'viewPortWidth' => ['type' => 'number', 'default' => 0],
            'viewPortHeight' => ['type' => 'number', 'default' => 0],
            'tcfString' => ['type' => 'string'],
            'referer' => ['type' => 'string'],
        ]]);
        \register_rest_route($namespace, '/forward/endpoints', ['methods' => 'GET', 'callback' => [$this, 'routeGetEndpoints'], 'permission_callback' => [$this, 'permission_callback'], 'args' => ['filter' => ['type' => 'string', 'enum' => AbstractMultisite::ALL_ENDPOINT_FILTERS, 'default' => AbstractMultisite::ENDPOINT_FILTER_ALL]]]);
        \register_rest_route($namespace, '/forward/cookie/(?P<slug>[^/]+)', ['methods' => 'GET', 'callback' => [$this, 'routeGetUniqueCookie'], 'permission_callback' => [$this, 'permission_callback']]);
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
     * @param WP_REST_Request $request
     *
     * @api {post} /real-cookie-banner/v1/consent/forward Create or update an existing consent (forwarded)
     * @apiHeader {string} X-WP-Nonce
     * @apiParam {string[]} cookies Unique, mappable names of all accepted cookies
     * @apiParam {string} uuid The UUID to use
     * @apiParam {number} consentId The reference to the consent ID of the source website
     * @apiParam {string} buttonClicked
     * @apiParam {number} [viewPortWidth=0]
     * @apiParam {number} [viewPortHeight=0]
     * @apiParam {boolean} [blocker]
     * @apiParam {string} [tcfString]
     * @apiParam {array} [gcmConsent]
     * @apiParam {string} [referer]
     * @apiName Forward
     * @apiGroup Consent
     * @apiPermission Pro only
     * @apiVersion 1.0.0
     */
    public function routePost($request)
    {
        $uuid = $request->get_param('uuid');
        $consentId = $request->get_param('consentId');
        $cookies = $request->get_param('cookies');
        $buttonClicked = $request->get_param('buttonClicked');
        $viewPortWidth = $request->get_param('viewPortWidth');
        $viewPortHeight = $request->get_param('viewPortHeight');
        $tcfString = $request->get_param('tcfString');
        $blocker = $request->get_param('blocker');
        $gcmConsent = $request->get_param('gcmConsent');
        $referer = $request->get_param('referer');
        if (IpHandler::getInstance()->isFlooding() || !\is_array($cookies)) {
            return new WP_Error('rest_rcb_forbidden');
        }
        if (!Multisite::getInstance()->isConsentForwarding()) {
            return new WP_Error('rest_rcb_forbidden', \__('Consent Forwarding is not active.', RCB_TD));
        }
        $transaction = new Transaction();
        $transaction->setForwarded($consentId, $uuid, $blocker);
        $transaction->setDecision(Multisite::getInstance()->mapUniqueNamesToDecision($cookies));
        $transaction->setButtonClicked($buttonClicked);
        $transaction->setViewPort($viewPortWidth, $viewPortHeight);
        $transaction->setReferer($referer);
        $transaction->setBlocker($blocker);
        $transaction->setTcfString($tcfString);
        $transaction->setGcmConsent($gcmConsent);
        $persist = MyConsent::getInstance()->persist($transaction);
        if (\is_wp_error($persist)) {
            return $persist;
        }
        return \rest_send_cors_headers(new WP_REST_Response($persist));
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     *
     * @api {get} /real-cookie-banner/v1/forward/endpoints Get all available and predefined forwarding endpoints
     * @apiHeader {string} X-WP-Nonce
     * @apiParam {string='all','onlyCurrent','notCurrent'} [filter=all]
     * @apiName ForwardEndpointsGet
     * @apiGroup Config
     * @apiPermission manage_options, Pro only
     * @apiVersion 1.0.0
     */
    public function routeGetEndpoints($request)
    {
        return new WP_REST_Response(Multisite::getInstance()->getAvailableEndpoints($request->get_param('filter')));
    }
    /**
     * See API docs.
     *
     * @param WP_REST_Request $request
     *
     * @api {get} /real-cookie-banner/v1/forward/cookie/:slug Get cookies by a given unique name (can be multiple -> misconfiguration)
     * @apiHeader {string} X-WP-Nonce
     * @apiParam {string} slug
     * @apiName ForwardCookieUniqueGet
     * @apiGroup Config
     * @apiPermission manage_options, Pro only
     * @apiVersion 1.0.0
     */
    public function routeGetUniqueCookie($request)
    {
        $slug = $request->get_param('slug');
        $result = Cookie::getInstance()->getServiceByUniqueName($slug);
        if (\is_wp_error($result)) {
            return $result;
        }
        return new WP_REST_Response($result);
    }
    /**
     * New instance.
     */
    public static function instance()
    {
        return new \DevOwl\RealCookieBanner\lite\rest\Forwarding();
    }
}
