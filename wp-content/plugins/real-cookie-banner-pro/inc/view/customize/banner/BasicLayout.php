<?php

namespace DevOwl\RealCookieBanner\view\customize\banner;

use DevOwl\RealCookieBanner\Vendor\DevOwl\Customize\AbstractCustomizePanel;
use DevOwl\RealCookieBanner\Vendor\DevOwl\Customize\controls\CssMarginInput;
use DevOwl\RealCookieBanner\Vendor\DevOwl\Customize\controls\CustomHTML;
use DevOwl\RealCookieBanner\Vendor\DevOwl\Customize\controls\Headline;
use DevOwl\RealCookieBanner\Vendor\DevOwl\Customize\controls\RangeInput;
use DevOwl\RealCookieBanner\base\UtilsProvider;
use DevOwl\RealCookieBanner\view\BannerCustomize;
use WP_Customize_Color_Control;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Basic Layout.
 * @internal
 */
class BasicLayout
{
    use UtilsProvider;
    const SECTION = BannerCustomize::PANEL_MAIN . '-layout';
    const CUSTOM_HTML_ANIMATION_IN_CLS_NOTICE = self::SECTION . '-custom-html-animation-in-cls-notice';
    const CUSTOM_HTML_MAX_HEIGHT_NOTICE = self::SECTION . '-custom-html-max-height-notice';
    const HEADLINE_OVERLAY = self::SECTION . '-headline-overlay';
    const HEADLINE_ANIMATION_IN = self::SECTION . '-headline-animation-in';
    const HEADLINE_ANIMATION_OUT = self::SECTION . '-headline-animation-out';
    const SETTING = RCB_OPT_PREFIX . '-banner-layout';
    const SETTING_TYPE = self::SETTING . '-type';
    const SETTING_MAX_HEIGHT_ENABLED = self::SETTING . '-max-height-enabled';
    const SETTING_MAX_HEIGHT = self::SETTING . '-max-height';
    const SETTING_DIALOG_POSITION = self::SETTING . '-dialog-position';
    const SETTING_DIALOG_MARGIN = self::SETTING . '-dialog-margin';
    const SETTING_DIALOG_MAX_WIDTH = self::SETTING . '-dialog-max-width';
    const SETTING_BANNER_POSITION = self::SETTING . '-banner-position';
    const SETTING_BANNER_MAX_WIDTH = self::SETTING . '-banner-max-width';
    const SETTING_OVERLAY = self::SETTING . '-overlay';
    const SETTING_OVERLAY_BG = self::SETTING . '-overlayBg';
    const SETTING_OVERLAY_BG_ALPHA = self::SETTING . '-overlayBgAlpha';
    const SETTING_OVERLAY_BLUR = self::SETTING . '-overlayBlur';
    const SETTING_ANIMATION_IN = self::SETTING . '-animation-in';
    const SETTING_ANIMATION_IN_DURATION = self::SETTING . '-animation-in-duration';
    const SETTING_ANIMATION_IN_ONLY_MOBILE = self::SETTING . '-animation-in-only-mobile';
    const SETTING_ANIMATION_OUT = self::SETTING . '-animation-out';
    const SETTING_ANIMATION_OUT_DURATION = self::SETTING . '-animation-out-duration';
    const SETTING_ANIMATION_OUT_ONLY_MOBILE = self::SETTING . '-animation-out-only-mobile';
    const SETTING_BORDER_RADIUS = self::SETTING . '-border-radius';
    const SETTING_DIALOG_BORDER_RADIUS = self::SETTING . '-dialog-border-radius';
    const DEFAULT_TYPE = 'dialog';
    const DEFAULT_MAX_HEIGHT_ENABLED = \true;
    const DEFAULT_MAX_HEIGHT = 740;
    const DEFAULT_DIALOG_POSITION = 'middleCenter';
    const DEFAULT_DIALOG_MARGIN = [0, 0, 0, 0];
    const DEFAULT_DIALOG_MAX_WIDTH = 530;
    const DEFAULT_BANNER_POSITION = 'bottom';
    const DEFAULT_BANNER_MAX_WIDTH = 1024;
    const DEFAULT_OVERLAY = \true;
    const DEFAULT_OVERLAY_BG = '#000000';
    const DEFAULT_OVERLAY_BG_ALPHA = 50;
    const DEFAULT_OVERLAY_BLUR = 2;
    const DEFAULT_ANIMATION_IN = 'slideInUp';
    const DEFAULT_ANIMATION_IN_DURATION = 500;
    const DEFAULT_ANIMATION_IN_ONLY_MOBILE = \true;
    const DEFAULT_ANIMATION_OUT = 'none';
    const DEFAULT_ANIMATION_OUT_DURATION = 500;
    const DEFAULT_ANIMATION_OUT_ONLY_MOBILE = \true;
    const DEFAULT_BORDER_RADIUS = 5;
    const DEFAULT_DIALOG_BORDER_RADIUS = 3;
    /**
     * Return arguments for this section.
     */
    public function args()
    {
        $textAnimationInClsNotice = \sprintf('<div class="notice notice-warning inline below-h2 notice-alt" style="margin: 10px 0 0 0"><p>%s</p></div>', \__('The animation you choose will have a negative impact on your PageSpeed Insights score. Please choose an animation where the cookie banner is animated from outside the website to achieve the best website loading speed!', 'real-cookie-banner'));
        $textMaxHeightNotice = \sprintf('<div class="notice notice-warning inline below-h2 notice-alt" style="margin: 10px 0 0 0"><p>%s</p></div>', \__('Limiting the height can make it difficult to see all important information. At most, limit it to the extent that all core information remains available without scrolling in order to obtain effective consent!', 'real-cookie-banner'));
        return ['name' => 'layout', 'title' => \__('Basic Layout', 'real-cookie-banner'), 'controls' => [self::SETTING_TYPE => ['name' => 'type', 'label' => \__('Layout', 'real-cookie-banner'), 'type' => 'radio', 'choices' => ['dialog' => \__('Dialog', 'real-cookie-banner'), 'banner' => \__('Banner', 'real-cookie-banner')], 'setting' => ['default' => self::DEFAULT_TYPE]], self::SETTING_MAX_HEIGHT_ENABLED => ['name' => 'maxHeightEnabled', 'label' => \__('Define a maximum height for the cookie banner', 'real-cookie-banner'), 'type' => 'checkbox', 'setting' => ['default' => self::DEFAULT_MAX_HEIGHT_ENABLED, 'sanitize_callback' => [AbstractCustomizePanel::class, 'sanitize_checkbox']]], self::SETTING_MAX_HEIGHT => ['name' => 'maxHeight', 'label' => \__('Maximum height', 'real-cookie-banner'), 'class' => RangeInput::class, 'unit' => 'px', 'input_attrs' => ['step' => 5, 'min' => 100, 'max' => 1500], 'setting' => ['default' => self::DEFAULT_MAX_HEIGHT, 'sanitize_callback' => 'absint']], self::CUSTOM_HTML_MAX_HEIGHT_NOTICE => ['class' => CustomHTML::class, 'name' => 'customHtmlLayoutMaxHeightNotice', 'description' => $textMaxHeightNotice], self::SETTING_DIALOG_MAX_WIDTH => ['name' => 'dialogMaxWidth', 'label' => \__('Maximum width', 'real-cookie-banner'), 'class' => RangeInput::class, 'unit' => 'px', 'input_attrs' => ['step' => 5, 'min' => 200, 'max' => 3000], 'setting' => ['default' => self::DEFAULT_DIALOG_MAX_WIDTH, 'sanitize_callback' => 'absint']], self::SETTING_DIALOG_POSITION => ['name' => 'dialogPosition', 'label' => \__('Position', 'real-cookie-banner'), 'type' => 'select', 'choices' => ['topLeft' => \__('Top left', 'real-cookie-banner'), 'topCenter' => \__('Top center', 'real-cookie-banner'), 'topRight' => \__('Top right', 'real-cookie-banner'), 'middleLeft' => \__('Middle left', 'real-cookie-banner'), 'middleCenter' => \__('Center', 'real-cookie-banner'), 'middleRight' => \__('Middle right', 'real-cookie-banner'), 'bottomLeft' => \__('Bottom left', 'real-cookie-banner'), 'bottomCenter' => \__('Bottom center', 'real-cookie-banner'), 'bottomRight' => \__('Bottom right', 'real-cookie-banner')], 'setting' => ['default' => self::DEFAULT_DIALOG_POSITION]], self::SETTING_DIALOG_MARGIN => ['class' => CssMarginInput::class, 'name' => 'dialogMargin', 'label' => \__('Margin', 'real-cookie-banner'), 'description' => \__('Define outer distance of the dialog.', 'real-cookie-banner'), 'setting' => ['default' => self::DEFAULT_DIALOG_MARGIN]], self::SETTING_BANNER_POSITION => ['name' => 'bannerPosition', 'label' => \__('Position', 'real-cookie-banner'), 'type' => 'select', 'choices' => ['top' => \__('Top', 'real-cookie-banner'), 'bottom' => \__('Bottom', 'real-cookie-banner')], 'setting' => ['default' => self::DEFAULT_BANNER_POSITION]], self::SETTING_BANNER_MAX_WIDTH => ['name' => 'bannerMaxWidth', 'label' => \__('Maximum content width', 'real-cookie-banner'), 'class' => RangeInput::class, 'unit' => 'px', 'input_attrs' => ['step' => 5, 'min' => 200, 'max' => 3000], 'setting' => ['default' => self::DEFAULT_BANNER_MAX_WIDTH, 'sanitize_callback' => 'absint']], self::SETTING_DIALOG_BORDER_RADIUS => ['name' => 'dialogBorderRadius', 'label' => \__('Dialog border radius', 'real-cookie-banner'), 'class' => RangeInput::class, 'unit' => 'px', 'input_attrs' => ['min' => 0, 'max' => 50, 'step' => 0], 'setting' => ['default' => self::DEFAULT_DIALOG_BORDER_RADIUS, 'sanitize_callback' => 'absint']], self::SETTING_BORDER_RADIUS => ['name' => 'borderRadius', 'label' => \__('Button border radius', 'real-cookie-banner'), 'class' => RangeInput::class, 'unit' => 'px', 'input_attrs' => ['min' => 0, 'max' => 50, 'step' => 0], 'setting' => ['default' => self::DEFAULT_BORDER_RADIUS, 'sanitize_callback' => 'absint']], self::HEADLINE_ANIMATION_IN => ['class' => Headline::class, 'label' => \__('Animation on display', 'real-cookie-banner')], self::SETTING_ANIMATION_IN => ['name' => 'animationIn', 'label' => \__('Type', 'real-cookie-banner'), 'type' => 'select', 'choices' => $this->getAvailableAnimations(), 'setting' => ['default' => self::DEFAULT_ANIMATION_IN]], self::CUSTOM_HTML_ANIMATION_IN_CLS_NOTICE => ['class' => CustomHTML::class, 'name' => 'customHtmlLayoutAnimationInClsNotice', 'description' => $textAnimationInClsNotice], self::SETTING_ANIMATION_IN_DURATION => ['name' => 'animationInDuration', 'label' => \__('Duration', 'real-cookie-banner'), 'class' => RangeInput::class, 'unit' => 'ms', 'input_attrs' => ['step' => 500, 'min' => 0, 'max' => 20000], 'setting' => ['default' => self::DEFAULT_ANIMATION_IN_DURATION, 'sanitize_callback' => 'absint']], self::SETTING_ANIMATION_IN_ONLY_MOBILE => ['name' => 'animationInOnlyMobile', 'label' => \__('Animation only on mobile devices', 'real-cookie-banner'), 'type' => 'checkbox', 'setting' => ['default' => self::DEFAULT_ANIMATION_IN_ONLY_MOBILE, 'sanitize_callback' => [AbstractCustomizePanel::class, 'sanitize_checkbox']]], self::HEADLINE_ANIMATION_OUT => ['class' => Headline::class, 'label' => \__('Animation on hide', 'real-cookie-banner')], self::SETTING_ANIMATION_OUT => ['name' => 'animationOut', 'label' => \__('Type', 'real-cookie-banner'), 'type' => 'select', 'choices' => $this->getAvailableAnimationsOut(), 'setting' => ['default' => self::DEFAULT_ANIMATION_OUT]], self::SETTING_ANIMATION_OUT_DURATION => ['name' => 'animationOutDuration', 'label' => \__('Duration', 'real-cookie-banner'), 'class' => RangeInput::class, 'unit' => 'ms', 'input_attrs' => ['step' => 500, 'min' => 0, 'max' => 20000], 'setting' => ['default' => self::DEFAULT_ANIMATION_OUT_DURATION, 'sanitize_callback' => 'absint']], self::SETTING_ANIMATION_OUT_ONLY_MOBILE => ['name' => 'animationOutOnlyMobile', 'label' => \__('Animation only on mobile devices', 'real-cookie-banner'), 'type' => 'checkbox', 'setting' => ['default' => self::DEFAULT_ANIMATION_OUT_ONLY_MOBILE, 'sanitize_callback' => [AbstractCustomizePanel::class, 'sanitize_checkbox']]], self::HEADLINE_OVERLAY => ['class' => Headline::class, 'label' => \__('Overlay', 'real-cookie-banner'), 'description' => \__('The overlay prevents clicks outside the consent dialog until consent is given and seperates the consent dialog from the rest of the website.', 'real-cookie-banner')], self::SETTING_OVERLAY => ['name' => 'overlay', 'label' => \__('Block content and show overlay until consent', 'real-cookie-banner'), 'type' => 'checkbox', 'setting' => ['default' => self::DEFAULT_OVERLAY, 'sanitize_callback' => [AbstractCustomizePanel::class, 'sanitize_checkbox']]], self::SETTING_OVERLAY_BG => ['name' => 'overlayBg', 'class' => WP_Customize_Color_Control::class, 'label' => \__('Overlay background color', 'real-cookie-banner'), 'setting' => ['default' => self::DEFAULT_OVERLAY_BG, 'sanitize_callback' => 'sanitize_hex_color']], self::SETTING_OVERLAY_BG_ALPHA => ['name' => 'overlayBgAlpha', 'label' => \__('Overlay opacity', 'real-cookie-banner'), 'class' => RangeInput::class, 'unit' => '%', 'input_attrs' => ['min' => 0, 'max' => 100, 'step' => 0], 'setting' => ['default' => self::DEFAULT_OVERLAY_BG_ALPHA, 'sanitize_callback' => 'absint']], self::SETTING_OVERLAY_BLUR => ['name' => 'overlayBlur', 'label' => \__('Blur effect', 'real-cookie-banner'), 'type' => 'range', 'input_attrs' => \array_merge(['min' => 0, 'max' => 20, 'step' => 0], $this->isPro() ? [] : ['disabled' => 'disabled']), 'setting' => ['default' => self::DEFAULT_OVERLAY_BLUR, 'sanitize_callback' => 'absint']]]];
    }
    /**
     * Get all available animate.css animations.
     */
    protected function getAvailableAnimations()
    {
        /**
         * Allows to provide additional animations-in compatible with animate.css.
         *
         * @hook RCB/Customize/Animation/In
         * @param {string[]} $animations
         * @return {string[]}
         */
        $result = \apply_filters('RCB/Customize/Animation/In', ['none' => \__('None', 'real-cookie-banner'), 'fadeIn' => 'fadeIn', 'slideInUp' => 'slideInUp']);
        \asort($result);
        return $result;
    }
    /**
     * Get all available animate.css animations for exit.
     */
    protected function getAvailableAnimationsOut()
    {
        /**
         * Allows to provide additional animations-out compatible with animate.css.
         *
         * @hook RCB/Customize/Animation/Out
         * @param {string[]} $animations
         * @return {string[]}
         */
        $result = \apply_filters('RCB/Customize/Animation/Out', ['none' => \__('None', 'real-cookie-banner'), 'fadeOut' => 'fadeOut']);
        \asort($result);
        return $result;
    }
}
