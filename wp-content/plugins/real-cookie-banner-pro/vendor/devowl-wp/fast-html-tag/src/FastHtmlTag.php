<?php

namespace DevOwl\RealCookieBanner\Vendor\DevOwl\FastHtmlTag;

use DevOwl\RealCookieBanner\Vendor\DevOwl\FastHtmlTag\finder\AbstractFinder;
use DevOwl\RealCookieBanner\Vendor\DevOwl\FastHtmlTag\finder\match\AbstractMatch;
/**
 * Initialize a new parser.
 * @internal
 */
class FastHtmlTag
{
    /**
     * JSON key / HTML-comment stem to skip processing for named instances
     * (e.g. `["HeadlessContentBlocker"]` or `<!--$$skipFastHtmlTag:HeadlessContentBlocker:start-->`).
     */
    const SKIP_KEY = '$$skipFastHtmlTag';
    /**
     * Unique name of this instance.
     *
     * Can be useful in conjunction with `$$skipFastHtmlTag`.
     *
     * @var string
     */
    private $name;
    /**
     * Callbacks.
     *
     * @var callable[]
     */
    private $callbacks = [];
    /**
     * Allows to rerun the processor on the resulting HTML again.
     */
    private $rerun = \false;
    /**
     * Callbacks for `SelectorSyntaxAttributeFunction`.
     *
     * @var callable[]
     */
    private $selectorSyntaxFunctions = [];
    /**
     * See `AbstractFinder`.
     *
     * @var AbstractFinder[]
     */
    private $finder = [];
    /**
     * C'tor.
     *
     * @param string $name
     */
    public function __construct($name = 'FastHtmlTag')
    {
        $this->name = $name;
    }
    /**
     * Add a finder scheme. See `finder/` for available ones.
     *
     * @param AbstractFinder $finder
     */
    public function addFinder($finder)
    {
        $this->finder[] = $finder;
    }
    /**
     * Add a callable. The first parameter is the HTML string and should return HTML.
     *
     * @param callable $callback
     */
    public function addCallback($callback)
    {
        $this->callbacks[] = $callback;
    }
    /**
     * Add a callable for a Selector Syntax function.
     *
     * The callback gets the following parameters and expects `boolean` as result:
     *
     * `SelectorSyntaxAttributeFunction $function, AbstractMatch $match, mixed $value`
     *
     * @param string $functionName
     * @param callable $callback
     */
    public function addSelectorSyntaxFunction($functionName, $callback)
    {
        $this->selectorSyntaxFunctions[$functionName] = $callback;
    }
    /**
     * Allows to parse and modify any content. This could be e.g. a JSON string
     * (each value gets iterated and parsed if it is a HTML).
     *
     * @param mixed $mixed
     */
    public function modifyAny($mixed)
    {
        $json = Utils::isJson($mixed, \false, \false);
        // Avoid JSON primitives to be replaced
        if (\is_int($json) || $json === \true || \is_float($json) || Utils::isBinary($mixed)) {
            return $mixed;
        }
        if ($json !== \false) {
            // Is it a primitive JSON string?
            if (\is_string($json)) {
                return \json_encode($this->modifyAny($json));
            }
            // We have now a complete JSON array, let's walk it recursively and apply content blocker
            if ($json !== null) {
                if (isset($json->{self::SKIP_KEY}) && \is_array($json->{self::SKIP_KEY}) && \in_array($this->name, $json->{self::SKIP_KEY}, \true)) {
                    return $mixed;
                }
                Utils::array_and_object_walk_recursive($json, function (&$value) {
                    if (Utils::isHtml($value)) {
                        $value = $this->modifyHtml($value);
                    }
                });
            }
            if (\is_array($mixed) || \is_object($mixed)) {
                // The original passed parameter is an array or object, so we return the modified JSON array or object
                return $json;
            }
            return \json_encode($json);
        } elseif (\is_string($mixed)) {
            // Usual string
            return $this->modifyHtml($mixed);
        }
        // @codeCoverageIgnoreStart
        return $mixed;
        // @codeCoverageIgnoreEnd
    }
    /**
     * Allow to parse and modify a given HTML string.
     *
     * @param string $html
     */
    public function modifyHtml($html)
    {
        list($html, $regions) = self::extractSkipRegions($html, $this->name);
        // With our complex regular expressions, `preg_replace[_callback]` can sometimes lead
        // to `PREG_BACKTRACK_LIMIT_ERROR` errors with large strings. Unfortunately, we can only
        // fix this by setting the backtrack limit to a very high value via PHP configuration (`php.ini`)
        $originalBacktrackLimit = \function_exists('ini_get') ? \ini_get('pcre.backtrack_limit') : \false;
        $canModifyBacktrackLimit = $originalBacktrackLimit !== \false && \wp_is_ini_value_changeable('pcre.backtrack_limit');
        if ($canModifyBacktrackLimit) {
            // phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
            @\ini_set('pcre.backtrack_limit', '10000000');
            // phpcs:enable WordPress.PHP.NoSilencedErrors.Discouraged
        }
        foreach ($this->finder as $finder) {
            $finder->setFastHtmlTag($this);
            $html = $finder->replace($html);
        }
        if ($canModifyBacktrackLimit) {
            // phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
            @\ini_set('pcre.backtrack_limit', $originalBacktrackLimit);
            // phpcs:enable WordPress.PHP.NoSilencedErrors.Discouraged
        }
        foreach ($this->callbacks as $callback) {
            $html = $callback($html);
        }
        if ($this->rerun) {
            $this->rerun = \false;
            $html = $this->modifyHtml($html);
        }
        // Remove invisible attributes (https://regex101.com/r/QAy0R0/2)
        $html = \preg_replace(\sprintf('/\\s+%s[^\\s>\\/]+/m', AbstractMatch::HTML_ATTRIBUTE_INVISIBLE_PREFIX), '', $html);
        return self::restoreSkipRegions($html, $regions);
    }
    /**
     * HTML comment that opens a skip island for `modifyHtml`.
     *
     * Empty `$instanceNames` skips every FastHtmlTag instance (`<!--$$skipFastHtmlTag:start-->`).
     * Otherwise only the listed instance names skip (`<!--$$skipFastHtmlTag:HeadlessContentBlocker:start-->`).
     *
     * @param string[] $instanceNames
     */
    public static function skipRegionStartComment($instanceNames = [])
    {
        $inside = \count($instanceNames) === 0 ? 'start' : \implode(',', $instanceNames) . ':start';
        return '<!--' . self::SKIP_KEY . ':' . $inside . '-->';
    }
    /**
     * HTML comment that closes a skip island.
     */
    public static function skipRegionEndComment()
    {
        return '<!--' . self::SKIP_KEY . ':end-->';
    }
    /**
     * Wrap HTML in skip-island comments for `modifyHtml`.
     *
     * @param string $html
     * @param string|string[] $instanceNames Empty = skip every instance; a string is a single instance name
     */
    public static function wrapSkipRegion($html, $instanceNames = [])
    {
        if (\is_string($instanceNames)) {
            $instanceNames = $instanceNames === '' ? [] : [$instanceNames];
        }
        return self::skipRegionStartComment($instanceNames) . $html . self::skipRegionEndComment();
    }
    /**
     * Lift `$$skipFastHtmlTag` HTML comment islands out before regex scanning.
     * Uses `strpos` (not PCRE) so multi-MB islands stay linear.
     *
     * @param string $html
     * @param string $instanceName See `$this->name`
     * @return array{0: string, 1: array<string, string>}
     */
    public static function extractSkipRegions($html, $instanceName)
    {
        $prefix = '<!--' . self::SKIP_KEY . ':';
        if (\strpos($html, $prefix) === \false) {
            return [$html, []];
        }
        $endMarker = self::skipRegionEndComment();
        $endMarkerLength = \strlen($endMarker);
        $prefixLength = \strlen($prefix);
        $namedStartSuffix = ':start';
        $regions = [];
        $out = '';
        $offset = 0;
        $i = 0;
        while (($startPos = \strpos($html, $prefix, $offset)) !== \false) {
            $commentClose = \strpos($html, '-->', $startPos + $prefixLength);
            if ($commentClose === \false) {
                break;
            }
            $inside = \substr($html, $startPos + $prefixLength, $commentClose - ($startPos + $prefixLength));
            if ($inside === 'start') {
                $names = null;
            } elseif (Utils::endsWith($inside, $namedStartSuffix)) {
                $names = \explode(',', \substr($inside, 0, -\strlen($namedStartSuffix)));
            } else {
                $out .= \substr($html, $offset, $commentClose + 3 - $offset);
                $offset = $commentClose + 3;
                continue;
            }
            if ($names !== null && !\in_array($instanceName, $names, \true)) {
                $out .= \substr($html, $offset, $commentClose + 3 - $offset);
                $offset = $commentClose + 3;
                continue;
            }
            $endPos = \strpos($html, $endMarker, $commentClose + 3);
            if ($endPos === \false) {
                break;
            }
            $endPos += $endMarkerLength;
            $out .= \substr($html, $offset, $startPos - $offset);
            $placeholder = $prefix . 'slot:' . $i . '-->';
            $regions[$placeholder] = \substr($html, $startPos, $endPos - $startPos);
            $out .= $placeholder;
            $offset = $endPos;
            ++$i;
        }
        $out .= \substr($html, $offset);
        return [$out, $regions];
    }
    /**
     * Reinsert islands previously extracted by `extractSkipRegions`.
     *
     * @param string $html
     * @param array<string, string> $regions
     */
    public static function restoreSkipRegions($html, $regions)
    {
        if (empty($regions)) {
            return $html;
        }
        return \str_replace(\array_keys($regions), \array_values($regions), $html);
    }
    /**
     * Get a defined selector syntax function by name.
     *
     * @param string $functionName
     */
    public function getSelectorSyntaxFunction($functionName)
    {
        return $this->selectorSyntaxFunctions[$functionName] ?? null;
    }
    /**
     * Allows to rerun the processor on the resulting HTML again.
     */
    public function registerRerun()
    {
        $this->rerun = \true;
    }
}
