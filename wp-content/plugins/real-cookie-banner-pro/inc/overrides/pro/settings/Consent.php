<?php

namespace DevOwl\RealCookieBanner\lite\settings;

use DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\consent\Transaction;
use DevOwl\RealCookieBanner\Core;
use DevOwl\RealCookieBanner\MyConsent;
use DevOwl\RealCookieBanner\settings\Consent as SettingsConsent;
use DevOwl\RealCookieBanner\Utils as RealCookieBannerUtils;
use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Utils;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/** @internal */
trait Consent
{
    // Documented in IOverrideGeneral
    public function overrideEnableOptionsAutoload()
    {
        Utils::enableOptionAutoload(SettingsConsent::SETTING_DATA_PROCESSING_IN_UNSAFE_COUNTRIES, SettingsConsent::DEFAULT_DATA_PROCESSING_IN_UNSAFE_COUNTRIES, 'boolval');
        Utils::enableOptionAutoload(SettingsConsent::SETTING_BANNER_LESS_CONSENT, SettingsConsent::DEFAULT_BANNER_LESS_CONSENT, 'boolval');
        Utils::enableOptionAutoload(SettingsConsent::SETTING_BANNER_LESS_SHOW_ON_PAGE_IDS, SettingsConsent::DEFAULT_BANNER_LESS_SHOW_ON_PAGE_IDS);
    }
    // Documented in IOverrideConsent
    public function overrideRegister()
    {
        \register_setting(SettingsConsent::OPTION_GROUP, SettingsConsent::SETTING_DATA_PROCESSING_IN_UNSAFE_COUNTRIES, ['type' => 'boolean', 'show_in_rest' => \true]);
        \register_setting(SettingsConsent::OPTION_GROUP, SettingsConsent::SETTING_BANNER_LESS_CONSENT, ['type' => 'boolean', 'show_in_rest' => \true]);
        // WP < 5.3 does not support array types yet, so we need to store serialized
        \register_setting(SettingsConsent::OPTION_GROUP, SettingsConsent::SETTING_BANNER_LESS_SHOW_ON_PAGE_IDS, ['type' => 'string', 'show_in_rest' => \true]);
    }
    // Documented in AbstractConsent
    public function isDataProcessingInUnsafeCountries()
    {
        return \get_option(SettingsConsent::SETTING_DATA_PROCESSING_IN_UNSAFE_COUNTRIES);
    }
    // Documented in AbstractConsent
    public function isBannerLessConsent()
    {
        return \get_option(SettingsConsent::SETTING_BANNER_LESS_CONSENT);
    }
    // Documented in AbstractConsent
    public function getBannerLessConsentShowOnPageIds()
    {
        $ids = \get_option(SettingsConsent::SETTING_BANNER_LESS_SHOW_ON_PAGE_IDS);
        if (empty($ids)) {
            return [];
        }
        return \array_map('absint', \explode(',', $ids));
    }
    /**
     * Determines, if the current page request should be bypassed by bannerless mode.
     *
     * @param false|string $result
     * @param WP_REST_Request $request
     */
    public function dynamicPredecision($result, $request)
    {
        if ($result === \false) {
            $transaction = new Transaction();
            $transaction->setIpAddress(RealCookieBannerUtils::getIpAddress());
            $transaction->setUserAgent($_SERVER['HTTP_USER_AGENT']);
            $transaction->setReferer($request->get_param('referer'));
            $transaction->setViewPort($request->get_param('viewPortWidth'), $request->get_param('viewPortHeight'));
            $postId = 0;
            if (!empty($transaction->getReferer())) {
                $postId = \url_to_postid($transaction->getReferer());
                $postType = \get_post_type($postId);
                if (\is_string($postType)) {
                    $postId = Core::getInstance()->getCompLanguage()->getOriginalPostId($postId, $postType);
                }
            }
            $bypass = $this->probablyCreateBannerlessConsentTransaction(MyConsent::getInstance()->getCurrentUser(), $transaction, $postId, $request->get_param('tcfStringImplicitEssentials'));
            if ($bypass) {
                $persist = MyConsent::getInstance()->persist($transaction);
                if (!\is_wp_error($persist)) {
                    return 'consent';
                    // Through the above code we have ensured there is a cookie set, so we can use the current consent
                }
            }
        }
        return $result;
    }
}
