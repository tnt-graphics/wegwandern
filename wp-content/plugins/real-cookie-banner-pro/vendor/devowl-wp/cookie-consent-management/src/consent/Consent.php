<?php

namespace DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\consent;

use DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\CookieConsentManagement;
use DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\frontend\Frontend;
use DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\services\Service;
use DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\Utils;
use DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\templates\ServiceTemplate;
/**
 * A consent holds all information of a single given consent. In other words, it allows
 * to parse the current consent which is written in the current cookie and additionally,
 * allows to modify it.
 * @internal
 */
class Consent
{
    /**
     * Regexp to validate and parse the cookie value with named capture groups.
     *
     * @see https://regex101.com/r/6UXL8j/2
     */
    const COOKIE_VALUE_REGEXP = '/^(?<createdAt>\\d+)?:?(?<uuids>(?:[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}[,]?)+):(?<revisionHash>[a-f0-9]{32}):(?<json>.*)$/';
    /**
     * See `CookieConsentManagement`.
     *
     * @var CookieConsentManagement
     */
    private $cookieConsentManagement;
    /**
     * The current cookies which are set on the client.
     *
     * @var array
     */
    private $currentCookies = [];
    /**
     * The UUID for this consent.
     *
     * @var string
     */
    private $uuid;
    /**
     * The previous UUIDs for this consent.
     *
     * @var string[]
     */
    private $previousUuids = [];
    /**
     * UNIX timestamp or ISO string of the consent creation time.
     *
     * @var int|string
     */
    private $created;
    /**
     * The revision hash for this consent.
     *
     * @var string
     */
    private $revisionHash;
    /**
     * The clicked button.
     *
     * @var string
     */
    private $buttonClicked;
    /**
     * The decision for this consent.
     *
     * @var array
     */
    private $decision;
    /**
     * The TCF string for this consent.
     *
     * @var string
     */
    private $tcfString;
    /**
     * The Google Consent Mode consent purposes for this consent.
     *
     * @var string[]
     */
    private $gcmConsent;
    /**
     * C'tor.
     *
     * @param CookieConsentManagement $cookieConsentManagement
     */
    public function __construct($cookieConsentManagement)
    {
        $this->cookieConsentManagement = $cookieConsentManagement;
    }
    /**
     * Check if a given technical information (e.g. HTTP Cookie, LocalStorage, ...) has a consent:
     *
     * - When a technical information exists in defined cookies, the Promise is only resolved after given consent
     * - When no technical information exists, the Promise is immediate resolved
     *
     * **Attention:** Do not use this function if you can get the conditional consent into your frontend
     * coding and use instead the `window.consentApi`!
     *
     * @param string|int $typeOrId
     * @param string $name
     * @param string $host
     */
    public function hasConsent($typeOrId, $name = null, $host = null)
    {
        $settings = $this->getCookieConsentManagement()->getSettings();
        $general = $settings->getGeneral();
        if (!$general->isBannerActive()) {
            return ['cookie' => null, 'consentGiven' => \true, 'cookieOptIn' => \true];
        }
        // Find matching cookie
        $found = [];
        /**
         * All services.
         *
         * @var Service[]
         */
        $allServices = [];
        foreach ($general->getServiceGroups() as $group) {
            $allServices = \array_merge($allServices, $group->getItems());
        }
        foreach ($allServices as $service) {
            if (\is_int($typeOrId)) {
                if ($service->getId() === $typeOrId) {
                    $found[] = ['cookie' => $service, 'relevance' => 10];
                }
            } elseif (\is_string($typeOrId) && $name === null && $host === null) {
                if ($service->getUniqueName() === $typeOrId) {
                    $found[] = ['cookie' => $service, 'relevance' => 10];
                }
            } else {
                $technicalDefinitions = $service->getTechnicalDefinitions() ?? [];
                if (\count($technicalDefinitions) > 0) {
                    // Check if technical information matches
                    foreach ($technicalDefinitions as $key => $td) {
                        $regex = Utils::createRegexpPatternFromWildcardName($td->getName());
                        \preg_match_all($regex, $name, $matches, \PREG_SET_ORDER, 0);
                        if ($td->getType() === $typeOrId && ($td->getName() === $name || !empty($matches)) && ($td->getHost() === $host || $host === '*')) {
                            $found[] = [
                                'cookie' => $service,
                                // Create a priority by "relevance" inside the technical definitions
                                // This is the case if e.g. another Cookie consumes the same technical cookie
                                // Example: Vimeo uses Facebook Pixel, too
                                'relevance' => \count($technicalDefinitions) + $key + 1,
                            ];
                        }
                    }
                }
            }
        }
        $hasConsent = !empty($this->getUuid());
        if (\count($found) > 0) {
            \array_multisort(\array_column($found, 'relevance'), \SORT_DESC, $found);
            $relevantCookie = $found[0]['cookie'];
            if ($hasConsent && $this->getCookieConsentManagement()->getRevision()->getEnsuredCurrentHash() === $this->getRevisionHash()) {
                $consentCookieIds = Utils::array_flatten($this->getDecision());
                if (\in_array($relevantCookie->getId(), $consentCookieIds, \true)) {
                    return ['cookie' => $relevantCookie, 'consentGiven' => \true, 'cookieOptIn' => \true];
                } else {
                    return ['cookie' => $relevantCookie, 'consentGiven' => \true, 'cookieOptIn' => \false];
                }
            } else {
                return ['cookie' => $relevantCookie, 'consentGiven' => \false, 'cookieOptIn' => \false];
            }
        } else {
            return ['cookie' => null, 'consentGiven' => $hasConsent, 'cookieOptIn' => \true];
        }
    }
    /**
     * A user has given a new consent, let's commit it.
     *
     * @param Transaction $transaction
     * @param callable $persistDatabase A function which does not get any arguments but needs to return a persisted ID on the database
     */
    public function commit($transaction, $persistDatabase)
    {
        $decision = $this->sanitizeDecision($transaction->getDecision());
        if ($decision === \false) {
            return \false;
        }
        $revision = $this->getCookieConsentManagement()->getRevision();
        // Create the cookie on client-side with the latest requested consent hash instead of current real-time hash
        // Why? So, the frontend can safely compare latest requested hash to user-consent hash
        // What is true, cookie hash or database? I can promise, the database shows the consent hash!
        $currentHash = $revision->getEnsuredCurrentHash();
        // TODO: extract wp_generate_uuid4 mysql2date to an Utils function so it can be used outside WordPress
        $uuid = empty($transaction->getForwardedUuid()) ? \wp_generate_uuid4() : $transaction->getForwardedUuid();
        // Set cookie and merge with previous UUIDs
        $newPreviousUuids = \array_merge($this->getUuid() ? [$this->getUuid()] : [], $this->getPreviousUuids() ?? []);
        $allUuids = \array_merge([$uuid], $newPreviousUuids);
        $cookie = new SetCookie();
        $cookie->key = $this->getCookieConsentManagement()->getFrontend()->getCookieName();
        $cookie->value = \sprintf('%d:%s:%s:%s', \time(), \join(',', $allUuids), $currentHash, \json_encode([
            // Keep keys small as we do not have much space in cookie value (maximum of 4096 bytes in total of cookie size)
            'd' => $decision,
            'bc' => $transaction->getButtonClicked(),
        ]));
        $cookie->expire = $this->getCookieExpire();
        $this->handleCountryBypass($transaction);
        $tcfCookie = $this->handleTcfString($transaction);
        $gcmCookie = $this->handleGcmConsent($transaction);
        $this->uuid = $uuid;
        $this->previousUuids = $newPreviousUuids;
        $this->created = \time();
        $this->revisionHash = $currentHash;
        $this->decision = $decision;
        $this->buttonClicked = $transaction->getButtonClicked();
        //$this->tcfString = null; // see handleTcfString
        //$this->gcmConsent = null; // see handleGcmConsent
        $consentId = $persistDatabase();
        $forward = $this->handleConsentForwarding($transaction, $consentId);
        // Collect all cookies which need to be set on the client
        $setCookie = [$cookie];
        if ($tcfCookie !== null) {
            $setCookie[] = $tcfCookie;
        }
        if ($gcmCookie !== null) {
            $setCookie[] = $gcmCookie;
        }
        return ['response' => ['uuid' => $this->getUuid(), 'revisionHash' => $this->getRevisionHash(), 'decision' => $this->getDecision(), 'consentId' => $consentId, 'forward' => $forward], 'setCookie' => $transaction->isSetCookies() ? $setCookie : []];
    }
    /**
     * When country bypass is active, automatically calculate the user country so it
     * can be saved to the database.
     *
     * @param Transaction $transaction
     */
    protected function handleCountryBypass($transaction)
    {
        $settings = $this->getCookieConsentManagement()->getSettings();
        $countryBypass = $settings->getCountryBypass();
        if ($countryBypass->isActive() && !empty($transaction->getIpAddress())) {
            $country = $countryBypass->lookupCountryCode($transaction->getIpAddress());
            if (!empty($country)) {
                $transaction->setUserCountry($country);
            }
        }
    }
    /**
     * When TCF is active, save the TCF string.
     *
     * @param Transaction $transaction
     */
    protected function handleTcfString($transaction)
    {
        $settings = $this->getCookieConsentManagement()->getSettings();
        $tcf = $settings->getTcf();
        if ($tcf->isActive() && !empty($transaction->getTcfString())) {
            $this->tcfString = $transaction->getTcfString();
            $cookie = new SetCookie();
            $cookie->key = $this->getCookieConsentManagement()->getFrontend()->getCookieName(Frontend::COOKIE_NAME_SUFFIX_TCF);
            $cookie->value = $transaction->getTcfString();
            $cookie->expire = $this->getCookieExpire();
            return $cookie;
        }
        return null;
    }
    /**
     * When Google Consent Mode is active, save the consent mode purposes in an own cookie.
     *
     * @param Transaction $transaction
     */
    protected function handleGcmConsent($transaction)
    {
        $settings = $this->getCookieConsentManagement()->getSettings();
        $googleConsentMode = $settings->getGoogleConsentMode();
        if ($googleConsentMode->isEnabled() && \is_array($transaction->getGcmConsent())) {
            $this->gcmConsent = Validators::sanitizeGcmConsent($transaction->getGcmConsent());
            $cookie = new SetCookie();
            $cookie->key = $this->getCookieConsentManagement()->getFrontend()->getCookieName(Frontend::COOKIE_NAME_SUFFIX_GCM);
            $cookie->value = \json_encode($this->gcmConsent);
            $cookie->expire = $this->getCookieExpire();
            return $cookie;
        }
        return null;
    }
    /**
     * When commiting a new transaction, automatically handle the response so the client
     * can forward the consent.
     *
     * @param Transaction $transaction
     * @param int $persistId
     */
    protected function handleConsentForwarding($transaction, $persistId)
    {
        $settings = $this->getCookieConsentManagement()->getSettings();
        $multisite = $settings->getMultisite();
        if ($multisite->isConsentForwarding() && !$transaction->isMarkAsDoNotTrack()) {
            $endpoints = $multisite->getConfiguredEndpoints();
            if (\count($endpoints) > 0) {
                $data = [
                    'uuid' => $this->getUuid(),
                    'consentId' => $persistId,
                    'blocker' => $transaction->getBlocker() > 0,
                    'buttonClicked' => $transaction->getButtonClicked(),
                    'viewPortWidth' => $transaction->getViewPortWidth(),
                    'viewPortHeight' => $transaction->getViewPortHeight(),
                    'cookies' => $multisite->mapDecisionToUniqueNames($this->getDecision()),
                    'tcfString' => $this->getTcfString(),
                    'gcmConsent' => $this->getGcmConsent(),
                    'referer' => $transaction->getReferer(),
                    // Make wp_get_raw_referer work in WordPress
                    '_wp_http_referer' => $transaction->getReferer(),
                ];
                // Remove `null` data
                foreach ($data as $key => $value) {
                    if ($value === null) {
                        unset($data[$key]);
                    }
                }
                return ['endpoints' => $endpoints, 'data' => $data];
            }
        }
        return null;
    }
    /**
     * Validate a passed decision or create a decision.
     *
     * @param array|string $consent A decision array, `all`, `essentials`, `legitimateInterest` which creates a decision array of all available services
     * @return array
     */
    public function sanitizeDecision($consent)
    {
        if (\is_array($consent)) {
            foreach ($consent as $key => &$value) {
                if (!\is_numeric($key) || !\is_array($value)) {
                    return \false;
                }
                foreach ($value as $cookieId) {
                    if (!\is_numeric($cookieId)) {
                        return \false;
                    }
                }
                $value = \array_map('intval', $value);
            }
            return $consent;
        }
        if (\is_string($consent)) {
            $result = [];
            foreach ($this->getCookieConsentManagement()->getSettings()->getGeneral()->getServiceGroups() as $group) {
                if ($consent === 'essentials' && !$group->isEssential()) {
                    continue;
                }
                $result[$group->getId()] = \array_values(\array_filter(\array_map(function ($service) use($consent, $group) {
                    if ($consent === 'legitimateInterest') {
                        return $service->getLegalBasis() !== ServiceTemplate::LEGAL_BASIS_CONSENT || $group->isEssential() ? $service->getId() : null;
                    }
                    return $service->getId();
                }, $group->getItems())));
            }
            return $result;
        }
        return \false;
    }
    /**
     * Apply a new decision (e.g. opt-out a single cookie in a cookie group) to a new
     * consent. This is e.g. helpful if you are providing a custom bypass (e.g. Geolocation)
     * and want to overtake an opt-out / opt-in from the previous consent.
     *
     * @param array|string $previousDecision
     * @param array|string $newDecision
     * @param string $overtake Can be `optIn` or `optOut`
     */
    public function optInOrOptOutExistingDecision($previousDecision, $newDecision, $overtake)
    {
        $previousDecision = $this->sanitizeDecision($previousDecision);
        $newDecision = $this->sanitizeDecision($newDecision);
        $allCookies = Utils::array_flatten($this->sanitizeDecision('all'));
        $essentialGroupId = $this->getCookieConsentManagement()->getSettings()->getGeneral()->getEssentialServiceGroup()->getId();
        $allPreviousCookies = Utils::array_flatten($previousDecision);
        if ($overtake === 'optOut') {
            foreach ($newDecision as $group => $cookies) {
                if (\intval($group) === $essentialGroupId) {
                    // Skip essentials as they need always be accepted
                    continue;
                }
                foreach ($cookies as $idx => $cookie) {
                    if (!\in_array($cookie, $allPreviousCookies, \true)) {
                        // Remove from our new consent, too
                        unset($newDecision[$group][$idx]);
                        $newDecision[$group] = \array_values($newDecision[$group]);
                        // Force to be numbered array
                        continue;
                    }
                }
            }
        } elseif ($overtake === 'optIn') {
            foreach ($previousDecision as $group => $cookies) {
                if (\intval($group) === $essentialGroupId) {
                    // Skip essentials as they need always be accepted
                    continue;
                }
                foreach ($cookies as $cookie) {
                    if (!\in_array($cookie, $allCookies, \true)) {
                        // Does no longer exist, skip
                        continue;
                    }
                    $newDecision[$group][] = $cookie;
                }
            }
        }
        return $newDecision;
    }
    /**
     * Parse a consent from a given cookie value. The result will hold the unique user id and the accepted revision hash.
     *
     * @param string $value
     * @return array
     */
    protected function parseDecisionFromCookieValue($value)
    {
        // Cookie empty? (https://stackoverflow.com/a/32567915/5506547)
        $value = \stripslashes($value);
        if (empty($value)) {
            return \false;
        }
        // Parse and validate partly
        if (!\preg_match(self::COOKIE_VALUE_REGEXP, $value, $match)) {
            return \false;
        }
        // UUIDs are comma-separated
        $uuids = \explode(',', $match['uuids']);
        $uuid = \array_shift($uuids);
        $revisionHash = $match['revisionHash'];
        $jsonValue = \json_decode($match['json'], ARRAY_A);
        // Since the introduction of saving the clicked button in the cookie value, the `json` contains beside
        // the decision also the clicked button.
        if (!isset($jsonValue['bc'])) {
            // Migrate old format to new format
            $jsonValue = [
                'd' => $jsonValue,
                // We never will know at this time, which button the user clicked a long time ago (only in database, but in terms of performance we do not want to execute a query here)
                'bc' => 'none',
            ];
        }
        $cookieDecision = $this->sanitizeDecision($jsonValue['d']);
        $buttonClicked = Validators::sanitizeButtonClicked($jsonValue['bc']);
        if ($cookieDecision === \false) {
            return \false;
        }
        $result = ['uuid' => $uuid, 'previousUuids' => $uuids, 'created' => \is_numeric($match['createdAt']) ? \mysql2date('c', \gmdate('Y-m-d H:i:s', \intval($match['createdAt'])), \false) : null, 'revisionHash' => $revisionHash, 'decision' => $cookieDecision, 'buttonClicked' => $buttonClicked];
        return $result;
    }
    /**
     * A cookie got updated to `currentCookies`, so we need to reflect the changes to the current consent.
     *
     * @param string $cookieKey
     * @param string $cookieValue
     */
    protected function invalidateCookies($cookieKey, $cookieValue)
    {
        $frontend = $this->getCookieConsentManagement()->getFrontend();
        $decisionCookieName = $frontend->getCookieName();
        $tcfCookieName = $frontend->getCookieName(Frontend::COOKIE_NAME_SUFFIX_TCF);
        $gcmCookieName = $frontend->getCookieName(Frontend::COOKIE_NAME_SUFFIX_GCM);
        switch ($cookieKey) {
            case $decisionCookieName:
                $parsed = $this->parseDecisionFromCookieValue($cookieValue);
                if ($parsed !== \false) {
                    $this->uuid = $parsed['uuid'];
                    $this->previousUuids = $parsed['previousUuids'];
                    $this->created = $parsed['created'];
                    $this->revisionHash = $parsed['revisionHash'];
                    $this->decision = $parsed['decision'];
                    $this->buttonClicked = $parsed['buttonClicked'];
                }
                break;
            case $tcfCookieName:
                $this->tcfString = Validators::sanitizeTcfString($cookieValue);
                break;
            case $gcmCookieName:
                if (\is_string($cookieValue)) {
                    $this->gcmConsent = Validators::sanitizeGcmConsent(\json_decode(\stripslashes($cookieValue), ARRAY_A));
                }
                break;
            default:
                break;
        }
    }
    /**
     * Calculate the cookie expiration date.
     */
    protected function getCookieExpire()
    {
        return \time() + 86400 * $this->getCookieConsentManagement()->getSettings()->getConsent()->getCookieDuration();
    }
    /**
     * Getter.
     *
     * @codeCoverageIgnore
     */
    public function getCookieConsentManagement()
    {
        return $this->cookieConsentManagement;
    }
    /**
     * Getter.
     *
     * @codeCoverageIgnore
     */
    public function getUuid()
    {
        return $this->uuid;
    }
    /**
     * Getter.
     *
     * @codeCoverageIgnore
     */
    public function getPreviousUuids()
    {
        return $this->previousUuids;
    }
    /**
     * Getter.
     *
     * @codeCoverageIgnore
     */
    public function getCreated()
    {
        return $this->created;
    }
    /**
     * Getter.
     *
     * @codeCoverageIgnore
     */
    public function getRevisionHash()
    {
        return $this->revisionHash;
    }
    /**
     * Getter.
     *
     * @codeCoverageIgnore
     */
    public function getDecision()
    {
        return $this->decision;
    }
    /**
     * Getter.
     *
     * @codeCoverageIgnore
     */
    public function getButtonClicked()
    {
        return $this->buttonClicked;
    }
    /**
     * Getter.
     *
     * @codeCoverageIgnore
     */
    public function getTcfString()
    {
        return $this->tcfString;
    }
    /**
     * Getter.
     *
     * @codeCoverageIgnore
     */
    public function getGcmConsent()
    {
        return $this->gcmConsent;
    }
    /**
     * Setter.
     *
     * @param array $currentCookies
     */
    public function setCurrentCookies($currentCookies)
    {
        $this->currentCookies = $currentCookies;
        $this->uuid = null;
        $this->previousUuids = [];
        $this->created = null;
        $this->revisionHash = null;
        $this->decision = null;
        $this->buttonClicked = null;
        $this->tcfString = null;
        $this->gcmConsent = null;
        foreach ($this->currentCookies as $key => $value) {
            $this->invalidateCookies($key, $value);
        }
    }
}
