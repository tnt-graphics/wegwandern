<?php

namespace DevOwl\RealCookieBanner\lite\view;

// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Misc view settings for Pro version.
 * @internal
 */
class Misc
{
    /**
     * Singleton instance.
     *
     * @var Misc
     */
    private static $me = null;
    /**
     * Add PRO-only animations-in.
     *
     * @param string[] $animations
     */
    public function animationsIn($animations)
    {
        return \array_merge($animations, ['bounce' => 'bounce', 'flash' => 'flash', 'pulse' => 'pulse', 'rubberBand' => 'rubberBand', 'shake' => 'shake', 'headShake' => 'headShake', 'swing' => 'swing', 'tada' => 'tada', 'wobble' => 'wobble', 'jello' => 'jello', 'bounceIn' => 'bounceIn', 'bounceInDown' => 'bounceInDown', 'bounceInLeft' => 'bounceInLeft', 'bounceInRight' => 'bounceInRight', 'bounceInUp' => 'bounceInUp', 'fadeInDown' => 'fadeInDown', 'fadeInDownBig' => 'fadeInDownBig', 'fadeInLeft' => 'fadeInLeft', 'fadeInLeftBig' => 'fadeInLeftBig', 'fadeInRight' => 'fadeInRight', 'fadeInRightBig' => 'fadeInRightBig', 'fadeInUp' => 'fadeInUp', 'fadeInUpBig' => 'fadeInUpBig', 'flipInX' => 'flipInX', 'flipInY' => 'flipInY', 'lightSpeedIn' => 'lightSpeedIn', 'rotateIn' => 'rotateIn', 'rotateInDownLeft' => 'rotateInDownLeft', 'rotateInDownRight' => 'rotateInDownRight', 'rotateInUpLeft' => 'rotateInUpLeft', 'rotateInUpRight' => 'rotateInUpRight', 'jackInTheBox' => 'jackInTheBox', 'rollIn' => 'rollIn', 'zoomIn' => 'zoomIn', 'zoomInDown' => 'zoomInDown', 'zoomInLeft' => 'zoomInLeft', 'zoomInRight' => 'zoomInRight', 'zoomInUp' => 'zoomInUp', 'slideInDown' => 'slideInDown', 'slideInLeft' => 'slideInLeft', 'slideInRight' => 'slideInRight']);
    }
    /**
     * Add PRO-only animations-out.
     *
     * @param string[] $animations
     */
    public function animationsOut($animations)
    {
        return \array_merge($animations, ['bounceOut' => 'bounceOut', 'bounceOutDown' => 'bounceOutDown', 'bounceOutLeft' => 'bounceOutLeft', 'bounceOutRight' => 'bounceOutRight', 'bounceOutUp' => 'bounceOutUp', 'fadeOutDown' => 'fadeOutDown', 'fadeOutDownBig' => 'fadeOutDownBig', 'fadeOutLeft' => 'fadeOutLeft', 'fadeOutLeftBig' => 'fadeOutLeftBig', 'fadeOutRight' => 'fadeOutRight', 'fadeOutRightBig' => 'fadeOutRightBig', 'fadeOutUp' => 'fadeOutUp', 'fadeOutUpBig' => 'fadeOutUpBig', 'flipOutX' => 'flipOutX', 'flipOutY' => 'flipOutY', 'lightSpeedOut' => 'lightSpeedOut', 'rotateOut' => 'rotateOut', 'rotateOutDownLeft' => 'rotateOutDownLeft', 'rotateOutDownRight' => 'rotateOutDownRight', 'rotateOutUpLeft' => 'rotateOutUpLeft', 'rotateOutUpRight' => 'rotateOutUpRight', 'rollOut' => 'rollOut', 'zoomOut' => 'zoomOut', 'zoomOutDown' => 'zoomOutDown', 'zoomOutLeft' => 'zoomOutLeft', 'zoomOutRight' => 'zoomOutRight', 'zoomOutUp' => 'zoomOutUp', 'slideOutDown' => 'slideOutDown', 'slideOutLeft' => 'slideOutLeft', 'slideOutRight' => 'slideOutRight', 'slideOutUp' => 'slideOutUp']);
    }
    /**
     * Get singleton instance.
     *
     * @codeCoverageIgnore
     */
    public static function getInstance()
    {
        return self::$me === null ? self::$me = new \DevOwl\RealCookieBanner\lite\view\Misc() : self::$me;
    }
}
