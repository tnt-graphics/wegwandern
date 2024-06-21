<?php

namespace DevOwl\RealCookieBanner\lite\comp\language;

use DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\settings\AbstractMultisite;
use DevOwl\RealCookieBanner\Vendor\DevOwl\Multilingual\AbstractSyncPlugin;
use DevOwl\RealCookieBanner\Core;
use DevOwl\RealCookieBanner\lite\rest\Forwarding;
use DevOwl\RealCookieBanner\settings\Multisite;
use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Service;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Language specific action and filters for PRO version.
 * @internal
 */
class Hooks
{
    /**
     * Singleton instance.
     *
     * @var Hooks
     */
    private static $me = null;
    /**
     * Create forward endpoints for multilingual sites.
     *
     * @param array $endpoints
     * @param boolean $filter
     * @param int $requestBlogId
     * @param int $currentBlogId
     */
    public function forwardEndpoints($endpoints, $filter, $requestBlogId, $currentBlogId)
    {
        $comp = Core::getInstance()->getCompLanguage();
        if ($comp->isActive()) {
            // Calculate main URL
            $currentLanguage = $comp->getCurrentLanguage();
            $restUrl = Core::getInstance()->getAssets()->getAsciiUrl(Service::getUrl(Core::getInstance(), null, Forwarding::ENDPOINT_CONSENT_FORWARD));
            $restUrl = \add_query_arg(Multisite::FORWARDING_QUERY_BLOG_ID, \get_current_blog_id(), $restUrl);
            // Remove main URL as it is handled through own context (language)
            if (isset($endpoints[$restUrl])) {
                unset($endpoints[$restUrl]);
            }
            foreach ($comp->getActiveLanguages() as $lang) {
                $comp->switchToLanguage($lang, function () use($comp, $lang, &$endpoints, $restUrl, $filter, $currentLanguage, $requestBlogId, $currentBlogId) {
                    if ($comp instanceof AbstractSyncPlugin && $filter === AbstractMultisite::ENDPOINT_FILTER_NOT_CURRENT && $lang === $currentLanguage && $requestBlogId === $currentBlogId) {
                        return;
                    }
                    if ($filter === AbstractMultisite::ENDPOINT_FILTER_ONLY_CURRENT && $lang !== $currentLanguage && $requestBlogId !== $currentBlogId) {
                        return;
                    }
                    $restUrl = \add_query_arg('_dataLocale', $lang, $restUrl);
                    $endpoints[$restUrl] = \sprintf(
                        // translators:
                        \__('Multilingual %1$s: %2$s', RCB_TD),
                        $comp->getTranslatedName($lang),
                        \get_bloginfo('name')
                    );
                });
            }
        }
        return $endpoints;
    }
    /**
     * Get singleton instance.
     *
     * @return Hooks
     * @codeCoverageIgnore
     */
    public static function getInstance()
    {
        return self::$me === null ? self::$me = new \DevOwl\RealCookieBanner\lite\comp\language\Hooks() : self::$me;
    }
}
