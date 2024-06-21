<?php

namespace DevOwl\RealCookieBanner\lite\view\customize\banner;

use DevOwl\RealCookieBanner\Vendor\DevOwl\Customize\controls\Headline;
use DevOwl\RealCookieBanner\comp\language\Hooks;
use DevOwl\RealCookieBanner\Core;
use DevOwl\RealCookieBanner\settings\TCF;
use DevOwl\RealCookieBanner\view\customize\banner\Texts;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * If TCF is active, add additional text options.
 * @internal
 */
class TcfTexts
{
    const CONTROLS_STACK_BEFORE = Texts::HEADLINE_CONSENT_FORWARDING;
    const HEADLINE_STACKS = Texts::SECTION . '-headline-tcf-stacks';
    const SETTING = RCB_OPT_PREFIX . '-banner-texts-tcf';
    const SETTING_STACKS_CUSTOM_NAME = self::SETTING . '-stacks-custom-name';
    const SETTING_STACKS_CUSTOM_DESCRIPTION = self::SETTING . '-stacks-custom-description';
    /**
     * Singleton instance.
     *
     * @var TcfTexts
     */
    private static $me = null;
    /**
     * Add additional fields for TCF stacks.
     *
     * @param array $sections
     */
    public function stacks(&$sections)
    {
        $controls =& $sections[Texts::SECTION]['controls'];
        $defaultButtonTexts = self::getDefaultTexts();
        $isTcfActive = TCF::getInstance()->isActive();
        // Insert before "Age notice"
        $offset = \array_search(self::CONTROLS_STACK_BEFORE, \array_keys($controls), \true);
        $newControls = [self::HEADLINE_STACKS => ['class' => Headline::class, 'name' => 'textsTcfStacks', 'label' => \__('TCF stacks', RCB_TD), 'level' => 3, 'isSubHeadline' => \true, 'description' => $isTcfActive ? '' : $this->getTcfDisabledNotice()], self::SETTING_STACKS_CUSTOM_NAME => ['name' => 'tcfStacksCustomName', 'label' => \__('Stack name for non-TCF services', RCB_TD), 'input_attrs' => $isTcfActive ? [] : ['disabled' => 'disabled'], 'setting' => ['default' => $defaultButtonTexts['stackCustomName'], 'allowEmpty' => \true]], self::SETTING_STACKS_CUSTOM_DESCRIPTION => ['name' => 'tcfStacksCustomDescription', 'label' => \__('Stack description for non-TCF services', RCB_TD), 'input_attrs' => $isTcfActive ? [] : ['disabled' => 'disabled'], 'setting' => ['default' => $defaultButtonTexts['stackCustomDescription'], 'allowEmpty' => \true]]];
        $controls = \array_slice($controls, 0, $offset, \true) + $newControls + \array_slice($controls, $offset, null, \true);
        return $sections;
    }
    /**
     * Return a notice HTML for the customize description when TCF is deactivated.
     */
    public static function getTcfDisabledNotice()
    {
        return \sprintf('<div class="notice notice-info inline below-h2 notice-alt" style="margin: 10px 0px 0px;"><p>%s</p></div>', \sprintf(
            // translators:
            \__('TCF is currently disabled. Please navigate to %1$sSettings > Transparency & Consent Framework (TCF)%2$s to activate it.', RCB_TD),
            '<a href="' . \esc_attr(Core::getInstance()->getConfigPage()->getUrl()) . '#/settings/tcf" target="_blank">',
            '</a>'
        ));
    }
    /**
     * Get the default texts.
     */
    public static function getDefaultTexts()
    {
        $tempTd = Hooks::getInstance()->createTemporaryTextDomain();
        $defaults = ['stackCustomName' => \__('Services with various purposes outside the TCF standard', Hooks::TD_FORCED), 'stackCustomDescription' => \__('Services that do not share consents via the TCF standard, but via other technologies. These are divided into several groups according to their purpose. Some of them are used based on a legitimate interest (e.g. threat prevention), others are used only with your consent. Details about the individual groups and purposes of the services can be found in the individual privacy settings.', Hooks::TD_FORCED)];
        $tempTd->teardown();
        return $defaults;
    }
    /**
     * Get singleton instance.
     *
     * @codeCoverageIgnore
     */
    public static function getInstance()
    {
        return self::$me === null ? self::$me = new \DevOwl\RealCookieBanner\lite\view\customize\banner\TcfTexts() : self::$me;
    }
}
