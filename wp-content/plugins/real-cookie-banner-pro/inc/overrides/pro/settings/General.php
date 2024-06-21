<?php

namespace DevOwl\RealCookieBanner\lite\settings;

use DevOwl\RealCookieBanner\settings\General as SettingsGeneral;
use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Utils;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/** @internal */
trait General
{
    // Documented in IOverrideGeneral
    public function overrideEnableOptionsAutoload()
    {
        Utils::enableOptionAutoload(SettingsGeneral::SETTING_HIDE_PAGE_IDS, SettingsGeneral::DEFAULT_HIDE_PAGE_IDS);
        Utils::enableOptionAutoload(SettingsGeneral::SETTING_SET_COOKIES_VIA_MANAGER, SettingsGeneral::DEFAULT_SET_COOKIES_VIA_MANAGER);
    }
    // Documented in IOverrideMultisite
    public function overrideRegister()
    {
        // WP < 5.3 does not support array types yet, so we need to store serialized
        \register_setting(SettingsGeneral::OPTION_GROUP, SettingsGeneral::SETTING_HIDE_PAGE_IDS, ['type' => 'string', 'show_in_rest' => \true]);
        \register_setting(SettingsGeneral::OPTION_GROUP, SettingsGeneral::SETTING_SET_COOKIES_VIA_MANAGER, ['type' => 'string', 'show_in_rest' => \true]);
    }
    // Documented in AbstractGeneral
    public function getAdditionalPageHideIds()
    {
        $ids = \get_option(SettingsGeneral::SETTING_HIDE_PAGE_IDS);
        if (empty($ids)) {
            return [];
        }
        return \array_map('absint', \explode(',', $ids));
    }
    // Documented in AbstractGeneral
    public function getSetCookiesViaManager()
    {
        return \get_option(SettingsGeneral::SETTING_SET_COOKIES_VIA_MANAGER);
    }
}
