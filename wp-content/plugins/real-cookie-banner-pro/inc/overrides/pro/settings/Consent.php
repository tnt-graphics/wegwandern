<?php

namespace DevOwl\RealCookieBanner\lite\settings;

use DevOwl\RealCookieBanner\settings\Consent as SettingsConsent;
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
        Utils::enableOptionAutoload(SettingsConsent::SETTING_DATA_PROCESSING_IN_UNSAFE_COUNTRIES_SAFE_COUNTRIES, SettingsConsent::DEFAULT_DATA_PROCESSING_IN_UNSAFE_COUNTRIES_SAFE_COUNTRIES);
    }
    // Documented in IOverrideConsent
    public function overrideRegister()
    {
        \register_setting(SettingsConsent::OPTION_GROUP, SettingsConsent::SETTING_DATA_PROCESSING_IN_UNSAFE_COUNTRIES, ['type' => 'boolean', 'show_in_rest' => \true]);
        // WP < 5.3 does not support array types yet, so we need to store serialized
        \register_setting(SettingsConsent::OPTION_GROUP, SettingsConsent::SETTING_DATA_PROCESSING_IN_UNSAFE_COUNTRIES_SAFE_COUNTRIES, ['type' => 'string', 'show_in_rest' => \true]);
    }
    // Documented in AbstractConsent
    public function isDataProcessingInUnsafeCountries()
    {
        return \get_option(SettingsConsent::SETTING_DATA_PROCESSING_IN_UNSAFE_COUNTRIES);
    }
    // Documented in AbstractConsent
    public function getDataProcessingInUnsafeCountriesSafeCountriesRaw()
    {
        $list = \get_option(SettingsConsent::SETTING_DATA_PROCESSING_IN_UNSAFE_COUNTRIES_SAFE_COUNTRIES);
        if (empty($list)) {
            return [];
        }
        return \explode(',', $list);
    }
}
