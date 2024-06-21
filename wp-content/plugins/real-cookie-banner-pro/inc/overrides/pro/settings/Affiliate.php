<?php

namespace DevOwl\RealCookieBanner\lite\settings;

use DevOwl\RealCookieBanner\base\UtilsProvider;
use DevOwl\RealCookieBanner\comp\language\Hooks;
use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Constants;
use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Utils;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Affiliate settings. This is only available in PRO version!
 * @internal
 */
class Affiliate
{
    use UtilsProvider;
    const OPTION_GROUP = 'options';
    const SETTING_AFFILIATE_LINK = RCB_OPT_PREFIX . '-affiliate-link';
    const SETTING_AFFILIATE_LABEL_BEHIND = RCB_OPT_PREFIX . '-affiliate-label-behind';
    const SETTING_AFFILIATE_DESCRIPTION = RCB_OPT_PREFIX . '-affiliate-description';
    const DEFAULT_AFFILIATE_LINK = '';
    const DEFAULT_AFFILIATE_LABEL_BEHIND = '*';
    /**
     * Singleton instance.
     *
     * @var Affiliate
     */
    private static $me = null;
    /**
     * C'tor.
     */
    private function __construct()
    {
        // Silence is golden.
    }
    /**
     * Initially `add_option` to avoid autoloading issues.
     */
    public function enableOptionsAutoload()
    {
        Utils::enableOptionAutoload(self::SETTING_AFFILIATE_LINK, self::DEFAULT_AFFILIATE_LINK);
        Utils::enableOptionAutoload(self::SETTING_AFFILIATE_LABEL_BEHIND, self::DEFAULT_AFFILIATE_LABEL_BEHIND);
        Utils::enableOptionAutoload(self::SETTING_AFFILIATE_DESCRIPTION, $this->getDefaultTexts()['description']);
    }
    /**
     * Register settings.
     */
    public function register()
    {
        \register_setting(self::OPTION_GROUP, self::SETTING_AFFILIATE_LINK, ['type' => 'string', 'show_in_rest' => \true, 'sanitize_callback' => 'esc_url_raw']);
        \register_setting(self::OPTION_GROUP, self::SETTING_AFFILIATE_LABEL_BEHIND, ['type' => 'string', 'show_in_rest' => \true]);
        \register_setting(self::OPTION_GROUP, self::SETTING_AFFILIATE_DESCRIPTION, ['type' => 'string', 'show_in_rest' => \true]);
    }
    /**
     * Localize frontend.
     *
     * @param array $arr
     * @param string $context
     */
    public function localize($arr, $context)
    {
        if ($context === Constants::ASSETS_TYPE_FRONTEND || \is_customize_preview()) {
            $link = $this->getAffiliateLink();
            if ($link) {
                $arr['affiliate'] = ['link' => $link, 'labelBehind' => $this->getAffiliateLabelBehind(), 'description' => $this->getAffiliateDescription()];
            }
        }
        return $arr;
    }
    // Self-explaining
    public function getAffiliateLink()
    {
        $result = \get_option(self::SETTING_AFFILIATE_LINK);
        return empty($result) ? \false : $result;
    }
    // Self-explaining
    public function getAffiliateLabelBehind()
    {
        $result = \get_option(self::SETTING_AFFILIATE_LABEL_BEHIND);
        return empty($result) ? \false : $result;
    }
    // Self-explaining
    public function getAffiliateDescription()
    {
        $result = \get_option(self::SETTING_AFFILIATE_DESCRIPTION);
        return empty($result) ? \false : $result;
    }
    /**
     * Get the affiliate default texts.
     */
    public function getDefaultTexts()
    {
        $tempTd = Hooks::getInstance()->createTemporaryTextDomain();
        $defaults = ['description' => \__('The link to Real Cookie Banner is an affiliate link. If you buy the product, we may receive a commission, but the price of the product will not change for you.', Hooks::TD_FORCED)];
        $tempTd->teardown();
        return $defaults;
    }
    /**
     * Get singleton instance.
     *
     * @codeCoverageIgnore
     * @return Affiliate
     */
    public static function getInstance()
    {
        return self::$me === null ? self::$me = new \DevOwl\RealCookieBanner\lite\settings\Affiliate() : self::$me;
    }
}
