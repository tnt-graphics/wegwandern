<?php

namespace DevOwl\RealCookieBanner\lite\settings;

use DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\settings\AbstractMultisite;
use DevOwl\RealCookieBanner\comp\RevisionContextDependingOption;
use DevOwl\RealCookieBanner\Core;
use DevOwl\RealCookieBanner\lite\rest\Forwarding as RestForwarding;
use DevOwl\RealCookieBanner\settings\Multisite as SettingsMultisite;
use DevOwl\RealCookieBanner\Utils;
use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Service;
use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Utils as UtilsUtils;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/** @internal */
trait Multisite
{
    // Documented in IOverrideGeneral
    public function overrideEnableOptionsAutoload()
    {
        UtilsUtils::enableOptionAutoload(SettingsMultisite::SETTING_CONSENT_FORWARDING, SettingsMultisite::DEFAULT_CONSENT_FORWARDING, 'boolval');
        UtilsUtils::enableOptionAutoload(SettingsMultisite::SETTING_FORWARD_TO, SettingsMultisite::DEFAULT_FORWARD_TO);
        UtilsUtils::enableOptionAutoload(SettingsMultisite::SETTING_CROSS_DOMAINS, SettingsMultisite::DEFAULT_CROSS_DOMAINS);
        // Make options context-dependent
        new RevisionContextDependingOption(SettingsMultisite::SETTING_FORWARD_TO, SettingsMultisite::DEFAULT_FORWARD_TO);
        new RevisionContextDependingOption(SettingsMultisite::SETTING_CROSS_DOMAINS, SettingsMultisite::DEFAULT_CROSS_DOMAINS);
        \add_action('updated_option', [$this, 'updated_option_forward_to']);
    }
    // Documented in IOverrideMultisite
    public function overrideRegister()
    {
        \register_setting(SettingsMultisite::OPTION_GROUP, SettingsMultisite::SETTING_CONSENT_FORWARDING, ['type' => 'boolean', 'show_in_rest' => \true]);
        // WP < 5.3 does not support array types yet, so we need to store serialized
        \register_setting(SettingsMultisite::OPTION_GROUP, SettingsMultisite::SETTING_FORWARD_TO, ['type' => 'string', 'show_in_rest' => \true]);
        \register_setting(SettingsMultisite::OPTION_GROUP, SettingsMultisite::SETTING_CROSS_DOMAINS, ['type' => 'string', 'show_in_rest' => \true, 'sanitize_callback' => 'sanitize_textarea_field']);
    }
    /**
     * The option "Forward to" got updated. Let's iterate all available sites and activate
     * consent forwarding automatically.
     *
     * @param string $option Name of the updated option
     */
    public function updated_option_forward_to($option)
    {
        if (!Utils::startsWith($option, SettingsMultisite::SETTING_FORWARD_TO)) {
            return;
        }
        $forwardTo = $this->getForwardTo();
        if (\count($forwardTo) > 0 && \is_multisite()) {
            foreach ($forwardTo as $url) {
                \parse_str(\parse_url($url, \PHP_URL_QUERY), $result);
                if (isset($result[SettingsMultisite::FORWARDING_QUERY_BLOG_ID])) {
                    $blogId = \intval($result[SettingsMultisite::FORWARDING_QUERY_BLOG_ID]);
                    \switch_to_blog($blogId);
                    \update_option(SettingsMultisite::SETTING_CONSENT_FORWARDING, \true);
                    \restore_current_blog();
                }
            }
        }
    }
    // Documented in AbstractMultisite
    public function isConsentForwarding()
    {
        return \get_option(SettingsMultisite::SETTING_CONSENT_FORWARDING);
    }
    // Documented in AbstractMultisite
    public function getForwardTo()
    {
        if ($this->isConsentForwarding()) {
            $forwardTo = \get_option(SettingsMultisite::SETTING_FORWARD_TO);
            return \array_filter(\explode('|', $forwardTo));
        }
        return [];
    }
    // Documented in AbstractMultisite
    public function getCrossDomains()
    {
        if ($this->isConsentForwarding()) {
            $crossDomains = \get_option(SettingsMultisite::SETTING_CROSS_DOMAINS);
            return \array_filter(\explode("\n", $crossDomains));
        }
        return [];
    }
    // Documented in AbstractMultisite
    public function getConfiguredEndpoints()
    {
        $endpoints = [];
        // Get configured known endpoints and only allow not-current one
        $forwardTo = $this->getForwardTo();
        $notCurrentForwardTo = \array_keys($this->getAvailableEndpoints(AbstractMultisite::ENDPOINT_FILTER_NOT_CURRENT));
        foreach ($forwardTo as $ft) {
            if (\in_array($ft, $notCurrentForwardTo, \true)) {
                $endpoints[] = $ft;
            }
        }
        // Get configured unknown-cross-domains and only allow not-current one
        $crossDomains = $this->getCrossDomains();
        $currentForwardTo = \array_keys($this->getAvailableEndpoints(AbstractMultisite::ENDPOINT_FILTER_ONLY_CURRENT));
        foreach ($crossDomains as $cd) {
            if (!\in_array($cd, $currentForwardTo, \true)) {
                $endpoints[] = $cd;
            }
        }
        /**
         * (Pro only) Get all forward endpoints independent of current option. This allows you to create custom
         * and computed endpoints depending on the current context.
         *
         * @hook RCB/Forward/Endpoints/Computed
         * @param {string[]} $endpoints
         * @return {string[]}
         */
        return \apply_filters('RCB/Forward/Endpoints/Computed', $endpoints);
    }
    // Documented in AbstractMultisite
    public function getAvailableEndpoints($filter = AbstractMultisite::ENDPOINT_FILTER_ALL)
    {
        $isMu = \is_multisite();
        $currentBlogId = \get_current_blog_id();
        $endpoints = [];
        // Multisite (all blog IDs)
        $blogIds = [];
        if ($isMu && \function_exists('get_sites') && \class_exists('WP_Site_Query') && $filter !== AbstractMultisite::ENDPOINT_FILTER_ONLY_CURRENT) {
            $blogIds = \get_sites(['number' => 0, 'fields' => 'ids']);
        } else {
            $blogIds[] = $currentBlogId;
        }
        foreach ($blogIds as $blogId) {
            if ($isMu) {
                \switch_to_blog($blogId);
            }
            $siteEndpoints = [];
            // Add this blog only if requested
            $addThis = \true;
            if ($filter === AbstractMultisite::ENDPOINT_FILTER_NOT_CURRENT && $blogId === $currentBlogId) {
                $addThis = \false;
            }
            if ($filter === AbstractMultisite::ENDPOINT_FILTER_ONLY_CURRENT && $blogId !== $currentBlogId) {
                $addThis = \false;
            }
            if ($addThis) {
                $restUrl = Service::getUrl(Core::getInstance(), null, RestForwarding::ENDPOINT_CONSENT_FORWARD);
                // E.g. when using https://wordpress.org/plugins/ns-cloner-site-copier/ the `rest_url()` function
                // could potentially return `null` and leads to an error.
                if (!empty($restUrl)) {
                    $restUrl = Core::getInstance()->getAssets()->getAsciiUrl($restUrl);
                    // Add `blog` query argument so we can identify the query at option-update runtime (see Multisite::update_option_forward_to)
                    $restUrl = \add_query_arg(SettingsMultisite::FORWARDING_QUERY_BLOG_ID, $blogId, $restUrl);
                    $siteEndpoints[$restUrl] = \get_bloginfo('name');
                }
            }
            /**
             * (Pro only) Get all available and predefined endpoints. E. g. if your are running within a mulitisite,
             * you will get all available WP REST API endpoints for each site within this multisite. That means, you need
             * to add your endpoint as follows: `$endpoints["https://example.com/wp-json/real-cookie-banner/v1/consent/forward"] = 'My example'.`
             *
             * Attention: This filter can be run multiple times when you are within a multisite. For each blog, the filter is applied.
             *
             * @hook RCB/Forward/Endpoints
             * @param {array} $endpoints
             * @param {boolean} $filter Can be `all`, `notCurrent` and `onlyCurrent`
             * @param {int} $requestBlogId since 2.15.1
             * @param {int} $currentBlogId since 2.15.1
             * @return {array}
             */
            $endpoints = \array_merge($endpoints, \apply_filters('RCB/Forward/Endpoints', $siteEndpoints, $filter, $currentBlogId, $blogId));
            if ($isMu) {
                \restore_current_blog();
            }
        }
        return $endpoints;
    }
}
