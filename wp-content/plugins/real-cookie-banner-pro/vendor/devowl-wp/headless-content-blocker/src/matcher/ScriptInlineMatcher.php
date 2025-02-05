<?php

namespace DevOwl\RealCookieBanner\Vendor\DevOwl\HeadlessContentBlocker\matcher;

use DevOwl\RealCookieBanner\Vendor\DevOwl\FastHtmlTag\finder\match\ScriptInlineMatch;
use DevOwl\RealCookieBanner\Vendor\DevOwl\HeadlessContentBlocker\Constants;
/**
 * Match by `ScriptInlineMatcher`.
 * @internal
 */
class ScriptInlineMatcher extends AbstractMatcher
{
    const DO_NOT_COMPUTE = 'DO_NOT_COMPUTE';
    /**
     * See `AbstractMatcher`.
     *
     * @param ScriptInlineMatch $match
     */
    public function match($match)
    {
        $result = $this->createResult($match);
        if (!$result->isBlocked()) {
            return $result;
        }
        $this->applyCommonAttributes($result, $match);
        // Example: SendInBlue could be blocked twice by URL in script and Selector Syntax
        if (!$match->hasAttribute(Constants::HTML_ATTRIBUTE_INLINE)) {
            $match->setAttribute(Constants::HTML_ATTRIBUTE_INLINE, $match->getScript());
        }
        $match->setScript('');
        return $result;
    }
    /**
     * See `AbstractMatcher`.
     *
     * @param ScriptInlineMatch $match
     */
    public function createResult($match)
    {
        $result = $this->createPlainResultFromMatch($match);
        if ($match->isJavascript() && !$match->isCData() && !$match->isScriptOnlyVariableAssignment(['realCookieBanner'], \false)) {
            $this->iterateBlockablesInString($result, $match->getScript(), \true, \true);
        }
        $this->probablyDisableDueToSkipped($result, $match);
        if (\strpos($match->getScript(), Constants::INLINE_SCRIPT_CONTAINING_STRING_TO_SKIP_BLOCKER) !== \false) {
            $result->disableBlocking();
        }
        if ($result->isBlocked() && $this->isLocalizedVariable($match)) {
            $result->disableBlocking();
        }
        return $this->applyCheckResultHooks($result, $match);
    }
    /**
     * Check if a given inline script is produced by `wp_localized_script` and starts with
     * something like `var xxxxx=`.
     *
     * @param ScriptInlineMatch $match
     */
    protected function isLocalizedVariable($match)
    {
        $cb = $this->getHeadlessContentBlocker();
        $names = $cb->getSkipInlineScriptVariableAssignments();
        $names = $cb->runSkipInlineScriptVariableAssignmentsCallback($names, $this, $match);
        return $match->isScriptOnlyVariableAssignment($names, !\in_array(self::DO_NOT_COMPUTE, $names, \true));
    }
    /**
     * Add a non-minifyable string to the passed JavaScript so it can be skipped by this identifier.
     *
     * @param string $js
     * @codeCoverageIgnore
     */
    public static function makeInlineScriptSkippable($js)
    {
        $js .= Constants::INLINE_SCRIPT_CONTAINING_STRING_TO_SKIP_BLOCKER_UNMINIFYABLE;
        return $js;
    }
}
