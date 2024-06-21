<?php

namespace DevOwl\RealCookieBanner\lite\view;

use DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\tcf\StackCalculator;
use DevOwl\RealCookieBanner\Vendor\DevOwl\Customize\ContrastRatioValidator;
use DevOwl\RealCookieBanner\Vendor\DevOwl\Customize\Utils;
use DevOwl\RealCookieBanner\Vendor\DevOwl\Multilingual\LanguageDependingOption;
use DevOwl\RealCookieBanner\Core;
use DevOwl\RealCookieBanner\lite\view\customize\banner\TcfTexts;
use DevOwl\RealCookieBanner\settings\TCF;
use DevOwl\RealCookieBanner\view\BannerCustomize;
use DevOwl\RealCookieBanner\view\customize\banner\BasicLayout;
use DevOwl\RealCookieBanner\view\customize\banner\BodyDesign;
use DevOwl\RealCookieBanner\view\customize\banner\Decision;
use DevOwl\RealCookieBanner\view\customize\banner\Design;
use DevOwl\RealCookieBanner\view\customize\banner\individual\SaveButton;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * If TCF is active, we need to modify some behaviors like forbid the use of "Hide"
 * in decisions in customizer. For "new controls" check out the `TcfBannerCustomize` class.
 * @internal
 */
class TcfBanner
{
    const MINIMUM_BUTTON_CONTRAST_RATIO = 5;
    /**
     * Singleton instance.
     *
     * @var TcfBanner
     */
    private static $me = null;
    /**
     * Make texts multilingual.
     */
    public function multilingual()
    {
        $comp = Core::getInstance()->getCompLanguage();
        $adminDefaultTcfTexts = TcfTexts::getDefaultTexts();
        new LanguageDependingOption($comp, TcfTexts::SETTING_STACKS_CUSTOM_NAME, $adminDefaultTcfTexts['stackCustomName']);
        new LanguageDependingOption($comp, TcfTexts::SETTING_STACKS_CUSTOM_DESCRIPTION, $adminDefaultTcfTexts['stackCustomDescription']);
    }
    /**
     * Texts for TCF groups view.
     */
    public function localizeTexts()
    {
        return Core::getInstance()->getCompLanguage()->translateArray(['tcf' => ['teaching' => \_x('You also allow data transfer under the TCF standard to {{consentCount}} based on consent, and {{legIntCount}} based on legitimate interest, for the following purposes:', 'legal-text', RCB_TD), 'vendorList' => \_x('Vendor list', 'legal-text', RCB_TD), 'vendors' => \_x('Vendors', 'legal-text', RCB_TD), 'vendorsCount' => [\_x('partner', 'legal-text', RCB_TD), \_x('partners', 'legal-text', RCB_TD)], 'dataRetentionPeriod' => \_x('data retention period', 'legal-text', RCB_TD), 'showMore' => \__('Show more', RCB_TD), 'hideMore' => \__('Hide', RCB_TD), 'example' => \__('Example', RCB_TD), 'legIntClaim' => \__('Explanation of the legitimate interest', RCB_TD), 'filterText' => \_x('Data processing on the legal basis of:', 'legal-text', RCB_TD), 'filterNoVendors' => \_x('No vendor requests purposes under this legal basis.', 'legal-text', RCB_TD), 'standard' => \_x('Data processing standardized according to TCF', 'legal-text', RCB_TD), 'standardDesc' => \_x('The Transparency and Consent Framework (TCF) is a standard for obtaining consistent consent for processing of personal data and cookie setting. This should enable all parties in the digital (advertising) chain to ensure that they set, process and store data and cookies in accordance with the GDPR and the ePrivacy Directive.', 'legal-text', RCB_TD), 'declarations' => [StackCalculator::DECLARATION_TYPE_PURPOSES => ['title' => \_x('Purposes', 'legal-text', RCB_TD), 'desc' => \_x('Purposes describe for which purpose which providers may set cookies and process personal data. Purposes are pre-selected if there is a legitimate interest for its data processing. For all other purposes, data will only be processed with explicit consent.', 'legal-text', RCB_TD)], StackCalculator::DECLARATION_TYPE_SPECIAL_PURPOSES => ['title' => \_x('Special purposes', 'legal-text', RCB_TD), 'desc' => \_x('Special purposes for setting cookies and processing personal data by our vendors describe purposes for which we have a legitimate interest that cannot be rejected. For example, we need to process data to prevent fraud.', 'legal-text', RCB_TD)], StackCalculator::DECLARATION_TYPE_FEATURES => ['title' => \_x('Features', 'legal-text', RCB_TD), 'desc' => \_x('Features for processing personal data describe how data is used to fulfill one or more purposes. Features cannot be opted out, but in the "Purposes" section, purposes that lead to the use of features can be selected or deselected. Any purpose can lead to features being used.', 'legal-text', RCB_TD)], StackCalculator::DECLARATION_TYPE_SPECIAL_FEATURES => ['title' => \_x('Special features', 'legal-text', RCB_TD), 'desc' => \_x('Special features for processing personal data describe how data is used to fulfill one or more purposes in a profound way. Personal data will only be processed in this way with explicit consent.', 'legal-text', RCB_TD)], StackCalculator::DECLARATION_TYPE_DATA_CATEGORIES => ['title' => \_x('Data categories', 'legal-text', RCB_TD), 'desc' => \_x('Data categories describe which data is used and obtained while processing personal data.', 'legal-text', RCB_TD)]]]], [], null, ['legal-text']);
    }
    /**
     * Initialize filters at `plugins_loaded` time.
     */
    public function hooks()
    {
        if (TCF::getInstance()->isActive()) {
            \add_filter('DevOwl/Customize/Sections/' . BannerCustomize::PANEL_MAIN, [$this, 'customizeBasicLayoutHint']);
            \add_filter('DevOwl/Customize/Sections/' . BannerCustomize::PANEL_MAIN, [$this, 'customizeDisableHide']);
            \add_filter('DevOwl/Customize/Sections/' . BannerCustomize::PANEL_MAIN, [$this, 'customizeButtonFontSizeNotice']);
            \add_filter('DevOwl/Customize/Sections/' . BannerCustomize::PANEL_MAIN, [$this, 'customizeButtonContrastRatioValidator']);
            \add_filter('option_' . Decision::SETTING_GROUPS_FIRST_VIEW, '__return_false');
            // `pre_option_{option}` does not support `false` as return
            \add_filter('pre_option_' . Decision::SETTING_ACCEPT_ALL, [$this, 'optionDisableDecisionHide'], 10, 2);
            \add_filter('pre_option_' . Decision::SETTING_ACCEPT_ESSENTIALS, [$this, 'optionDisableDecisionHide'], 10, 2);
            \add_filter('pre_option_' . Decision::SETTING_ACCEPT_INDIVIDUAL, [$this, 'optionDisableDecisionHide'], 10, 2);
        }
    }
    /**
     * Add a notice to the "Basic Layout" section.
     *
     * @param array $sections
     */
    public function customizeBasicLayoutHint(&$sections)
    {
        $sections[BasicLayout::SECTION]['controls'][BasicLayout::SETTING_TYPE]['description'] = \sprintf('<div class="notice notice-info inline below-h2 notice-alt" style="margin: 10px 0"><p>%s</p></div>', \__('The TCF requires that the consent dialogue must "[cover] all or substantially all of the content of the website". We therefore recommend displaying the consent dialogue as a banner in the middle of the website. Also make sure that the overlay remains active so that your website cannot be used until the consent decision has been made.', RCB_TD));
        return $sections;
    }
    /**
     * The decisions no longer can have the "Hide" as available dropdown selection, remove it.
     *
     * @param array $sections
     */
    public function customizeDisableHide(&$sections)
    {
        foreach ([Decision::SETTING_ACCEPT_ALL, Decision::SETTING_ACCEPT_ESSENTIALS, Decision::SETTING_ACCEPT_INDIVIDUAL] as $key) {
            $setting =& $sections[Decision::SECTION]['controls'][$key];
            \array_pop($setting['choices']);
        }
        return $sections;
    }
    /**
     * If TCF is active, the font (family), font size and font weight must be the same as of "Accept all".
     *
     * @param array $sections
     */
    public function customizeButtonFontSizeNotice(&$sections)
    {
        $notice = \sprintf('<div class="notice notice-info inline below-h2 notice-alt" style="margin: 10px 0 0 0"><p>%s</p></div>', \__('You currently have TCF mode enabled. Therefore, you are not allowed to change the font size and font thickness for this button.', RCB_TD));
        $sections[BodyDesign::SECTION]['controls'][BodyDesign::HEADLINE_BUTTON_ACCEPT_ESSENTIALS_FONT]['description'] = $notice;
        $sections[SaveButton::SECTION]['controls'][SaveButton::HEADLINE_FONT]['description'] = $notice;
        return $sections;
    }
    /**
     * If TCF is active, there must be a minimum contrast ratio of 5 for call-to-action buttons.
     *
     * @param array $sections
     */
    public function customizeButtonContrastRatioValidator(&$sections)
    {
        $panel = Core::getInstance()->getBanner()->getCustomize();
        // translators:
        $message = \__('Your current contrast ratio between background and font color (%1$s) does not reach the minimum of %2$s. Please adjust your colors!', RCB_TD);
        // Accept all
        $validator = [new ContrastRatioValidator($message, self::MINIMUM_BUTTON_CONTRAST_RATIO, BodyDesign::SETTING_BUTTON_ACCEPT_ALL_BG, BodyDesign::SETTING_BUTTON_ACCEPT_ALL_FONT_COLOR, $panel, function ($color1, $color2, $panel) {
            // If the button is a link, use the main background color
            if (Utils::getValue(Decision::SETTING_ACCEPT_ALL, $panel) === 'link') {
                return [Utils::getValue(Design::SETTING_COLOR_BG, $panel), $color2];
            }
            return [$color1, $color2];
        }), 'validate_callback'];
        $sections[Decision::SECTION]['controls'][Decision::SETTING_ACCEPT_ALL]['setting']['validate_callback'] = $validator;
        $sections[BodyDesign::SECTION]['controls'][BodyDesign::SETTING_BUTTON_ACCEPT_ALL_BG]['setting']['validate_callback'] = $validator;
        $sections[BodyDesign::SECTION]['controls'][BodyDesign::SETTING_BUTTON_ACCEPT_ALL_FONT_COLOR]['setting']['validate_callback'] = $validator;
        // Accept essentials
        $validator = [new ContrastRatioValidator($message, self::MINIMUM_BUTTON_CONTRAST_RATIO, BodyDesign::SETTING_BUTTON_ACCEPT_ESSENTIALS_BG, BodyDesign::SETTING_BUTTON_ACCEPT_ESSENTIALS_FONT_COLOR, $panel, function ($color1, $color2, $panel) {
            // If the button is a link, use the main background color
            if (Utils::getValue(Decision::SETTING_ACCEPT_ESSENTIALS, $panel) === 'link') {
                return [Utils::getValue(Design::SETTING_COLOR_BG, $panel), $color2];
            }
            return [$color1, $color2];
        }), 'validate_callback'];
        $sections[Decision::SECTION]['controls'][Decision::SETTING_ACCEPT_ESSENTIALS]['setting']['validate_callback'] = $validator;
        $sections[BodyDesign::SECTION]['controls'][BodyDesign::SETTING_BUTTON_ACCEPT_ESSENTIALS_BG]['setting']['validate_callback'] = $validator;
        $sections[BodyDesign::SECTION]['controls'][BodyDesign::SETTING_BUTTON_ACCEPT_ESSENTIALS_FONT_COLOR]['setting']['validate_callback'] = $validator;
        // Save custom choices
        $validator = [new ContrastRatioValidator($message, self::MINIMUM_BUTTON_CONTRAST_RATIO, SaveButton::SETTING_BG, SaveButton::SETTING_FONT_COLOR, $panel, function ($color1, $color2, $panel) {
            // If the button is a link, use the main background color
            if (Utils::getValue(SaveButton::SETTING_TYPE, $panel) === 'link') {
                return [Utils::getValue(Design::SETTING_COLOR_BG, $panel), $color2];
            }
            return [$color1, $color2];
        }), 'validate_callback'];
        $sections[SaveButton::SECTION]['controls'][SaveButton::SETTING_TYPE]['setting']['validate_callback'] = $validator;
        $sections[SaveButton::SECTION]['controls'][SaveButton::SETTING_BG]['setting']['validate_callback'] = $validator;
        $sections[SaveButton::SECTION]['controls'][SaveButton::SETTING_FONT_COLOR]['setting']['validate_callback'] = $validator;
        return $sections;
    }
    /**
     * The decisions no longer can have "Hide" as value, fallback to default.
     *
     * @param string $value
     * @param string $id
     */
    public function optionDisableDecisionHide($value, $id)
    {
        \remove_filter('pre_option_' . $id, [$this, 'optionDisableDecisionHide']);
        $original = \get_option($id);
        \add_filter('pre_option_' . $id, [$this, 'optionDisableDecisionHide'], 10, 2);
        if ($original !== 'hide') {
            return $value;
        }
        switch ($id) {
            case Decision::SETTING_ACCEPT_ALL:
            case Decision::SETTING_ACCEPT_ESSENTIALS:
                return Decision::DEFAULT_ACCEPT_ALL;
            case Decision::SETTING_ACCEPT_INDIVIDUAL:
                return Decision::DEFAULT_ACCEPT_INDIVIDUAL;
            default:
                break;
        }
        return $value;
    }
    /**
     * Get singleton instance.
     *
     * @codeCoverageIgnore
     */
    public static function getInstance()
    {
        return self::$me === null ? self::$me = new \DevOwl\RealCookieBanner\lite\view\TcfBanner() : self::$me;
    }
}
