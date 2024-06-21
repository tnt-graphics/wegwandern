<?php

namespace DevOwl\RealCookieBanner\lite;

use DevOwl\RealCookieBanner\Vendor\DevOwl\Freemium\CorePro;
use DevOwl\RealCookieBanner\comp\language\Hooks as LanguageHooks;
use DevOwl\RealCookieBanner\lite\comp\language\Hooks;
use DevOwl\RealCookieBanner\lite\view\Misc;
use DevOwl\RealCookieBanner\lite\rest\Forwarding as RestForwarding;
use DevOwl\RealCookieBanner\lite\rest\TCF;
use DevOwl\RealCookieBanner\lite\settings\Affiliate;
use DevOwl\RealCookieBanner\lite\view\TcfBanner;
use DevOwl\RealCookieBanner\lite\settings\TcfVendorConfiguration;
use DevOwl\RealCookieBanner\lite\view\customize\banner\TcfTexts;
use DevOwl\RealCookieBanner\settings\GoogleConsentMode;
use DevOwl\RealCookieBanner\settings\TCF as SettingsTCF;
use DevOwl\RealCookieBanner\view\BannerCustomize;
use DevOwl\RealCookieBanner\view\customize\banner\StickyLinks;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/** @internal */
trait Core
{
    use CorePro;
    /**
     * The updater instance.
     *
     * @see https://github.com/Capevace/wordpress-plugin-updater
     */
    private $updater;
    // Documented in IOverrideCore
    public function overrideConstruct()
    {
        \add_filter('RCB/Customize/Animation/In', [Misc::getInstance(), 'animationsIn']);
        \add_filter('RCB/Customize/Animation/Out', [Misc::getInstance(), 'animationsOut']);
        \add_filter('DevOwl/Customize/Sections/' . BannerCustomize::PANEL_MAIN, [TcfTexts::getInstance(), 'stacks']);
        \add_filter('plugins_loaded', [TcfBanner::getInstance(), 'hooks']);
    }
    // Documented in IOverrideCore
    public function overrideRegisterSettings()
    {
        Affiliate::getInstance()->register();
        // Make this customize option also available in REST API
        \register_setting('options', StickyLinks::SETTING_ENABLED, ['type' => 'boolean', 'show_in_rest' => \true]);
    }
    // Documented in IOverrideCore
    public function overrideRegisterPostTypes()
    {
        TcfVendorConfiguration::getInstance()->register();
    }
    // Documented in IOverrideCore
    public function overrideInit()
    {
        $affiliateSettings = Affiliate::getInstance();
        $tcfService = TCF::instance();
        \add_action('rest_api_init', [RestForwarding::instance(), 'rest_api_init']);
        \add_action('rest_api_init', [$tcfService, 'rest_api_init']);
        \add_filter('rest_prepare_' . TcfVendorConfiguration::CPT_NAME, [$tcfService, 'rest_prepare_vendor'], 10, 2);
        \add_filter('posts_clauses', [TcfVendorConfiguration::getInstance(), 'modify_search_query_where'], 10, 2);
        \add_filter('RCB/Forward/Endpoints', [Hooks::getInstance(), 'forwardEndpoints'], 10, 4);
        \add_filter('RCB/Localize', [$affiliateSettings, 'localize'], 10, 2);
        \add_filter('RCB/Localize', [SettingsTCF::getInstance(), 'localize'], 10, 2);
        \add_filter('RCB/Localize', [GoogleConsentMode::getInstance(), 'localize'], 10, 2);
        // Multilingual
        \add_filter('rest_' . TcfVendorConfiguration::CPT_NAME . '_query', [LanguageHooks::getInstance(), 'rest_query']);
        $affiliateSettings->enableOptionsAutoload();
        TcfBanner::getInstance()->multilingual();
    }
}
