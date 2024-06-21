<?php

namespace DevOwl\RealCookieBanner\lite\settings;

use DevOwl\RealCookieBanner\Core;
use DevOwl\RealCookieBanner\lite\view\GcmBanner;
use DevOwl\RealCookieBanner\settings\GoogleConsentMode as SettingsGoogleConsentMode;
use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Constants;
use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Utils;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/** @internal */
trait GoogleConsentMode
{
    // Documented in IOverrideGeneral
    public function overrideEnableOptionsAutoload()
    {
        Utils::enableOptionAutoload(SettingsGoogleConsentMode::SETTING_GCM_ENABLED, SettingsGoogleConsentMode::DEFAULT_GCM_ENABLED, 'boolval');
        Utils::enableOptionAutoload(SettingsGoogleConsentMode::SETTING_GCM_SHOW_RECOMMONDATIONS_WITHOUT_CONSENT, SettingsGoogleConsentMode::DEFAULT_GCM_SHOW_RECOMMONDATIONS_WITHOUT_CONSENT, 'boolval');
        Utils::enableOptionAutoload(SettingsGoogleConsentMode::SETTING_GCM_ADDITIONAL_URL_PARAMETERS, SettingsGoogleConsentMode::DEFAULT_GCM_ADDITIONAL_URL_PARAMETERS, 'boolval');
        Utils::enableOptionAutoload(SettingsGoogleConsentMode::SETTING_GCM_REDACT_DATA_WITHOUT_CONSENT, SettingsGoogleConsentMode::DEFAULT_GCM_REDACT_DATA_WITHOUT_CONSENT, 'boolval');
        Utils::enableOptionAutoload(SettingsGoogleConsentMode::SETTING_GCM_LIST_PURPOSES, SettingsGoogleConsentMode::DEFAULT_GCM_LIST_PURPOSES, 'boolval');
    }
    // Documented in IOverrideGoogleConsentMode
    public function overrideRegister()
    {
        \register_setting(SettingsGoogleConsentMode::OPTION_GROUP, SettingsGoogleConsentMode::SETTING_GCM_ENABLED, ['type' => 'boolean', 'show_in_rest' => \true]);
        \register_setting(SettingsGoogleConsentMode::OPTION_GROUP, SettingsGoogleConsentMode::SETTING_GCM_SHOW_RECOMMONDATIONS_WITHOUT_CONSENT, ['type' => 'boolean', 'show_in_rest' => \true]);
        \register_setting(SettingsGoogleConsentMode::OPTION_GROUP, SettingsGoogleConsentMode::SETTING_GCM_ADDITIONAL_URL_PARAMETERS, ['type' => 'boolean', 'show_in_rest' => \true]);
        \register_setting(SettingsGoogleConsentMode::OPTION_GROUP, SettingsGoogleConsentMode::SETTING_GCM_REDACT_DATA_WITHOUT_CONSENT, ['type' => 'boolean', 'show_in_rest' => \true]);
        \register_setting(SettingsGoogleConsentMode::OPTION_GROUP, SettingsGoogleConsentMode::SETTING_GCM_LIST_PURPOSES, ['type' => 'boolean', 'show_in_rest' => \true]);
    }
    /**
     * Localize frontend.
     *
     * @param array $arr
     * @param string $context
     */
    public function localize($arr, $context)
    {
        $isEnabled = $this->isEnabled();
        $isFrontend = $context === Constants::ASSETS_TYPE_FRONTEND || \is_customize_preview();
        $banner = GcmBanner::getInstance();
        if ($isFrontend && $isEnabled || $context === Constants::ASSETS_TYPE_ADMIN) {
            $arr['bannerI18n'] = \array_merge($arr['bannerI18n'], $banner->localizeTexts());
        }
        return $arr;
    }
    // Documented in AbstractGoogleConsentMode
    public function isEnabled()
    {
        return \get_option(SettingsGoogleConsentMode::SETTING_GCM_ENABLED) && Core::getInstance()->isLicenseActive();
    }
    // Documented in AbstractGoogleConsentMode
    public function isShowRecommandationsWithoutConsent()
    {
        return \get_option(SettingsGoogleConsentMode::SETTING_GCM_SHOW_RECOMMONDATIONS_WITHOUT_CONSENT);
    }
    // Documented in AbstractGoogleConsentMode
    public function isCollectAdditionalDataViaUrlParameters()
    {
        return \get_option(SettingsGoogleConsentMode::SETTING_GCM_ADDITIONAL_URL_PARAMETERS);
    }
    // Documented in AbstractGoogleConsentMode
    public function isRedactAdsDataWithoutConsent()
    {
        return \get_option(SettingsGoogleConsentMode::SETTING_GCM_REDACT_DATA_WITHOUT_CONSENT);
    }
    // Documented in AbstractGoogleConsentMode
    public function isListPurposes()
    {
        return \get_option(SettingsGoogleConsentMode::SETTING_GCM_LIST_PURPOSES);
    }
}
