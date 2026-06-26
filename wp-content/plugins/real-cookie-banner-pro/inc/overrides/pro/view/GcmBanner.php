<?php

namespace DevOwl\RealCookieBanner\lite\view;

use DevOwl\RealCookieBanner\Core;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * If Google Consent Mode is active, we need to add some texts for the frontend.
 * @internal
 */
class GcmBanner
{
    /**
     * Singleton instance.
     *
     * @var GcmBanner
     */
    private static $me = null;
    /**
     * Texts for Google Consent Mode groups view.
     */
    public function localizeTexts()
    {
        return Core::getInstance()->getCompLanguage()->translateArray(['gcm' => ['teaching' => \_x('You also allow data processing in accordance with Google Consent Mode of participating partners on the basis of consent for the following purposes:', 'legal-text', 'real-cookie-banner'), 'standard' => \_x('Data processing standardized according to Google Consent Mode', 'legal-text', 'real-cookie-banner'), 'standardDesc' => \_x('Google Consent Mode is a standard for obtaining consents to the processing of personal data and the setting of cookies by participating partners. It is possible to give consent to data processing for defined purposes so that Google services and third-party tags integrated with Google Tag used on this website can only process data to the desired scope. If you do not consent, you will receive an offer that is less personalized for you. However, the most important services remain the same and there are no missing features that do not necessarily require your consent. Irrespective of this, in the section "Non-standardized data processing", it is possible to consent to the services or to exercise the right to object to legitimate interests. Details on the specific data processing can be found in the named section.', 'legal-text', 'real-cookie-banner'), 'moreInfo' => \_x('More information on personal data processing by Google and partners:', 'legal-text', 'real-cookie-banner'), 'moreInfoLink' => \_x('https://business.safety.google/privacy/', 'legal-text', 'real-cookie-banner'), 'dataProcessingInService' => \_x('Additional purposes of data processing according to Google Consent Mode on the basis of consent (applies to all services)', 'legal-text', 'real-cookie-banner'), 'purposes' => ['ad_storage' => \_x('Storing and reading of data such as cookies (web) or device identifiers (apps) related to advertising.', 'legal-text', 'real-cookie-banner'), 'ad_user_data' => \_x('Sending user data to Google for online advertising purposes.', 'legal-text', 'real-cookie-banner'), 'ad_personalization' => \_x('Evaluation and display of personalized advertising.', 'legal-text', 'real-cookie-banner'), 'analytics_storage' => \_x('Storing and reading of data such as cookies (web) or device identifiers (apps), related to analytics (e.g. visit duration).', 'legal-text', 'real-cookie-banner'), 'functionality_storage' => \_x('Storing and reading of data that supports the functionality of the website or app (e.g. language settings).', 'legal-text', 'real-cookie-banner'), 'personalization_storage' => \_x('Storing and reading of data related to personalization (e.g. video recommendations).', 'legal-text', 'real-cookie-banner'), 'security_storage' => \_x('Storing and reading of data related to security (e.g. authentication functionality, fraud prevention, and other user protection).', 'legal-text', 'real-cookie-banner')]]], [], null, ['legal-text']);
    }
    /**
     * Get singleton instance.
     *
     * @codeCoverageIgnore
     */
    public static function getInstance()
    {
        return self::$me === null ? self::$me = new \DevOwl\RealCookieBanner\lite\view\GcmBanner() : self::$me;
    }
}
