<?php

namespace DevOwl\RealCookieBanner\comp\migration;

use DevOwl\RealCookieBanner\Core;
use DevOwl\RealCookieBanner\settings\Consent;
use DevOwl\RealCookieBanner\settings\Cookie;
use DevOwl\RealCookieBanner\settings\CookieGroup;
use DevOwl\RealCookieBanner\settings\General;
use DevOwl\RealCookieBanner\view\BannerCustomize;
use DevOwl\RealCookieBanner\view\Checklist;
use DevOwl\RealCookieBanner\view\checklist\OperatorContact;
use DevOwl\RealCookieBanner\view\checklist\PrivacyPolicyMentionUsage;
use DevOwl\RealCookieBanner\view\customize\banner\individual\Texts as IndividualTexts;
use DevOwl\RealCookieBanner\view\customize\banner\Texts;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Migration for Major version 4.
 *
 * @see https://app.clickup.com/t/861n7amqx
 * @internal
 */
class DashboardTileMigrationMajor4 extends \DevOwl\RealCookieBanner\comp\migration\AbstractDashboardTileMigration
{
    const DELETE_LANGUAGES = ['de', 'en', 'fr', 'es', 'pt', 'it', 'nl', 'pl', 'cz', 'da', 'sv', 'nl'];
    const DELETE_OPTIONS_TEXTS = [Texts::SETTING_HEADLINE, Texts::SETTING_DESCRIPTION, Texts::SETTING_DATA_PROCESSING_IN_UNSAFE_COUNTRIES, Texts::SETTING_AGE_NOTICE, Texts::SETTING_AGE_NOTICE_BLOCKER, Texts::SETTING_LIST_SERVICES_NOTICE, Texts::SETTING_LIST_LEGITIMATE_INTEREST_SERVICES_NOTICE, Texts::SETTING_CONSENT_FORWARDING, Texts::SETTING_ACCEPT_ALL, Texts::SETTING_ACCEPT_ESSENTIALS, Texts::SETTING_ACCEPT_INDIVIDUAL, Texts::SETTING_POWERED_BY_TEXT, Texts::SETTING_BLOCKER_HEADLINE, Texts::SETTING_BLOCKER_LINK_SHOW_MISSING, Texts::SETTING_BLOCKER_LOAD_BUTTON, Texts::SETTING_BLOCKER_ACCEPT_INFO, IndividualTexts::SETTING_HEADLINE, IndividualTexts::SETTING_DESCRIPTION, IndividualTexts::SETTING_SAVE, IndividualTexts::SETTING_SHOW_MORE, IndividualTexts::SETTING_HIDE_MORE];
    /**
     * Initialize hooks and listen to saves to content blockers so we can update the transient of `fetchBlockersWithBetterPotentialVisualType`.
     */
    public function init()
    {
        parent::init();
    }
    // Documented in AbstractDashboardTileMigration
    public function actions()
    {
        $notices = Core::getInstance()->getNotices();
        $needsUpdate = $notices->servicesWithUpdatedTemplates();
        $servicesWithoutDataProcessingCountries = '';
        $configUrlPage = $this->getConfigUrl('/cookies');
        $groups = CookieGroup::getInstance()->getOrdered();
        $core = Core::getInstance();
        $license = $core->getRpmInitiator()->getPluginUpdater()->getCurrentBlogLicense();
        $compLanguage = Core::getInstance()->getCompLanguage();
        $showLegalBasisAction = General::getInstance()->getOperatorCountry() === 'CH' || \count(\array_filter(\array_merge($compLanguage->getActiveLanguages(), [\get_locale()]), function ($language) {
            return \in_array(\substr($language, 0, 2), ['de', 'fr', 'it'], \true);
        })) > 0;
        foreach ($groups as $group) {
            $cookies = Cookie::getInstance()->getOrdered($group->term_id);
            foreach ($cookies as $cookie) {
                $countries = $cookie->metas[Cookie::META_NAME_DATA_PROCESSING_IN_COUNTRIES];
                if (\count($countries) === 0) {
                    $servicesWithoutDataProcessingCountries .= \sprintf('<li><strong>%s</strong> - <a href="%s">%s</a></li>', \esc_html($cookie->post_title), \sprintf('%s/%d/edit/%d', $configUrlPage, $group->term_id, $cookie->ID), \__('Configure data processing countries', 'real-cookie-banner'));
                }
            }
        }
        $this->addAction('translations', \__('Legal adjustments of texts and translations in 12 languages', 'real-cookie-banner'), \join('<br /><br/ >', [\__('We have further improved the texts and placements of these in Real Cookie Banner in an audit by a lawyer. This makes them even less legally vulnerable!', 'real-cookie-banner'), \__('In addition, we have adapted Real Cookie Banner to legal details of more EU countries and now offer translations in 12 languages. Official translations of the plugin are now available in English, French, Spanish, Portuguese, Italian, German (informal and formal), Dutch (informal and formal), Polish, Czech, Danish, Swedish, Norwegian Bokmal. Translations of service templates and content blocker templates will follow.', 'real-cookie-banner'), \sprintf('<strong>%s</strong>', \__('We strongly advise you to adopt the new texts for all languages with official translations!', 'real-cookie-banner'))]), ['linkText' => \__('Apply new texts', 'real-cookie-banner'), 'confirmText' => \__('We will overwrite all texts in your cookie banner with new text suggestions. Please check afterwards if all adjustments are correct for your individual requirements and reconfigure your cookie banner yourself if necessary. Are you sure you want to apply the changes?', 'real-cookie-banner'), 'callback' => [$this, 'applyNewTexts']])->addAction('a11y', \__('Accessibility to comply with the European Accessibility Act', 'real-cookie-banner'), \join('<br /><br/ >', [\sprintf(
            // translators:
            \__('As of June 2025, the <a href="%1$s" target="_blank">European Accessibility Act</a> requires accessibility for most websites. You can already offer an equal and obstacle-free website experience with Real Cookie Banner!', 'real-cookie-banner'),
            \__('https://eur-lex.europa.eu/legal-content/EN/TXT/?uri=celex:32019L0882', 'real-cookie-banner')
        ), \sprintf(
            // translators:
            \__('Real Cookie Banner complies with <a href="%1$s" target="_blank">WCAG 2.2 Level AA standard</a>, covers legal requirements and makes you ready for the new law. For more details, <a href="%2$s" target="_blank">check out our knowledge base</a>.', 'real-cookie-banner'),
            \__('https://www.w3.org/TR/WCAG22/', 'real-cookie-banner'),
            \__('https://devowl.io/knowledge-base/accessibility-of-real-cookie-banner/', 'real-cookie-banner')
        ), \sprintf(
            // translators:
            \__('All design templates have been revised by us with regard to accessibility. In addition, when <a href="%1$s" target="_blank">customizing the cookie banner design</a>, you will now find an accessibility score that evaluates how accessible your current design settings are. Customize your design now!', 'real-cookie-banner'),
            \add_query_arg(['autofocus[panel]' => BannerCustomize::PANEL_MAIN, 'customAutofocus[rcb-a11y-score]' => 1, 'return' => \wp_get_raw_referer()], \admin_url('customize.php'))
        )]), ['linkText' => \__('Show revised design presets', 'real-cookie-banner'), 'callback' => \add_query_arg(['autofocus[panel]' => BannerCustomize::PANEL_MAIN, 'customAutofocus[rcb-presets]' => 1, 'return' => \wp_get_raw_referer()], \admin_url('customize.php')), 'previewImage' => $core->getBaseAssetsUrl(\__('upgrade-wizard/v4/accessibility-score.png', 'real-cookie-banner'))])->addAction('website-operator', \__('Provide details of the website operator for self-hosted services', 'real-cookie-banner'), \join('<br /><br/ >', [\sprintf(
            // translators:
            \__('In services, you can provide contact details of the provider/data processor (postal address, email, phone, etc.) to fulfill your information obligations under <a href="%1$s" target="_blank">Art. 13 GDPR</a> in conjunction with <a href="%2$s" target="_blank">Art. 5 GDPR</a>. For self-hosted services, you are the data processor and should also provide this data in the cookie banner.', 'real-cookie-banner'),
            \__('https://gdpr-text.com/read/article-13/', 'real-cookie-banner'),
            \__('https://gdpr-text.com/read/article-5/', 'real-cookie-banner')
        ), \__('Save your contact details once in the settings and they will be available for all current and future services!', 'real-cookie-banner')]), ['linkText' => \__('Set website operator details', 'real-cookie-banner'), 'linkDisabled' => 'performed', 'callback' => $this->getConfigUrl('/settings'), 'performed' => Checklist::getInstance()->isChecked(OperatorContact::IDENTIFIER), 'performedLabel' => \__('Provider details are set!', 'real-cookie-banner'), 'previewImage' => $core->getBaseAssetsUrl(\__('upgrade-wizard/v4/website-operator.png', 'real-cookie-banner'))]);
        if ($showLegalBasisAction) {
            $this->addAction('legal-basis', \__('Define DSG (Switzerland) as legal basis', 'real-cookie-banner'), \join('<br /><br/ >', [\sprintf(
                // translators:
                \__('Real Cookie Banner can be used not only to meet the requirements of the GDPR in the EU, but also to comply with the new Swiss data protection law (DSG or nDSG). <a href="%1$s" target="_blank">More information about the DSG and cookie banners</a> can be found in our knowledge base!', 'real-cookie-banner'),
                \__('https://devowl.io/knowledge-base/cookie-banner-swiss-data-protection-fadp/', 'real-cookie-banner')
            ), \__('If you intentionally target website visitors from Switzerland, you should activate the DSG as the applicable legal basis to get useful tips in Real Cookie Banner.', 'real-cookie-banner')]), ['linkText' => \__('Specify legal basis', 'real-cookie-banner'), 'callback' => $this->getConfigUrl('/settings')]);
        }
        $this->addAction('privacy-policy-mention', \__('Explain data processing by Real Cookie Banner in privacy policy', 'real-cookie-banner'), \join('<br /><br/ >', [\__('You should explain the data processing of Real Cookie Banner (as well as of any other service) in more detail in your privacy policy in order to fulfill your information obligations under GDPR.', 'real-cookie-banner'), \__('If you do not yet explain the use of Real Cookie Banner in your privacy policy, simply use our suggestion!', 'real-cookie-banner')]), ['linkText' => \__('Show text suggestion', 'real-cookie-banner'), 'linkDisabled' => 'performed', 'callback' => $this->getConfigUrl('/settings'), 'performed' => Checklist::getInstance()->isChecked(PrivacyPolicyMentionUsage::IDENTIFIER), 'performedLabel' => \__('Real Cookie Banner in privacy policy mentioned!', 'real-cookie-banner'), 'previewImage' => $core->getBaseAssetsUrl(\__('upgrade-wizard/v4/privacy-policy-text-copy.png', 'real-cookie-banner'))])->addAction('service-cloud', \__('Updates for services and content blockers', 'real-cookie-banner'), \__('We have revised the information given in numerous service and content blocker templates. Services and content blockers you created may contain suggestions for changes that you may want to apply. You should review the proposed changes and adjust your services if necessary to be able to remain legally compliant. When editing the service/content blocker, you will find a blue "Different from template" next to the respective fields with more information.', 'real-cookie-banner'), ['performed' => \count($needsUpdate) === 0, 'performedLabel' => \__('All the services and content blockers you created are up to date!', 'real-cookie-banner'), 'info' => \count($needsUpdate) > 0 ? \sprintf('<p>%s</p>%s', \__('The following services should be reviewed:', 'real-cookie-banner'), $notices->servicesWithUpdatedTemplatesHtml($needsUpdate, 'tile-migration')) : null]);
        if (!empty($license->getActivation()->getCode()) && !$license->getActivation()->isTelemetryDataSharingOptIn()) {
            $this->addAction('telemetry', \__('Help us make Real Cookie Banner even better!', 'real-cookie-banner'), \join('<br /><br/ >', [\__('Recently, we have started actively collecting telemetry data about how Real Cookie Banner is used - if you have consented to it. This helps us e.g. to better prioritize often needed service templates or to implement much used features even better.', 'real-cookie-banner'), \__('<strong>You have not yet consented to collecting telemetry data.</strong> They do not contain any personal data about your website visitors. We would be very pleased if we could learn from your usage behavior as well!', 'real-cookie-banner')]), ['linkText' => \__('Collect telemetry data', 'real-cookie-banner'), 'performed' => \false, 'performedLabel' => \__('Telemetry data enabled.', 'real-cookie-banner'), 'confirmText' => \sprintf(
                // translators:
                \__('I allow telemetry data about the use of this WordPress plugin to be collected in accordance with the <a href="%s" target="_blank">privacy policy</a>. This data does not include any personal information about users of the plugin. Collected data will be used to provide you with the best possible support and to improve the plugin.', 'real-cookie-banner'),
                Core::getInstance()->getRpmInitiator()->getPrivacyPolicy()
            ), 'callback' => [$this, 'enableTelemetry']]);
        }
        $this->addAction('data-processing-in-unsafe-countries', \__('Consent to data processing in insecure third countries (instead of US data processing only)', 'real-cookie-banner'), \join('<br /><br/ >', [\sprintf(
            // translators:
            \__('Real Cookie Banner offered you so far to obtain special consent according to <a href="%1$s" target="_blank">Art. 49 (1) (a) GDPR</a> for data processing in the USA. <a href="%2$s" target="_blank">The USA is again a secure third country under data protection law since July 2023</a>. However, data processing of many services takes place worldwide and thus in other insecure countries.', 'real-cookie-banner'),
            \__('https://gdpr-text.com/read/article-49/', 'real-cookie-banner'),
            \__('https://devowl.io/data-protection/tadpf-us-data-processing/', 'real-cookie-banner')
        ), \sprintf(
            // translators:
            \__('You can now specify the countries of data processing in each service! We have already added this information in service templates. You can find a <a href="%1$s" target="_blank">list of secure third countries as defined by the EU in the settings</a>. This means that specific consents are now obtained for all unsecure third countries worldwide.', 'real-cookie-banner'),
            $this->getConfigUrl('/settings/consent')
        )]), ['linkText' => \__('Obtain consent for data processing in unsecure third countries', 'real-cookie-banner'), 'linkDisabled' => 'performed', 'callback' => $this->getConfigUrl('/settings/consent'), 'performed' => Consent::getInstance()->isDataProcessingInUnsafeCountries(), 'performedLabel' => \__('Feature is enabled', 'real-cookie-banner'), 'previewImage' => $core->getBaseAssetsUrl(\__('upgrade-wizard/v4/service-special-treatments.png', 'real-cookie-banner')), 'info' => !empty($servicesWithoutDataProcessingCountries) ? \sprintf('<p>%s</p><ul>%s</ul>', \__('The following services should be reviewed:', 'real-cookie-banner'), $servicesWithoutDataProcessingCountries) : null])->addAction('footer-links', \__('Specify additional links in the footer of the cookie banner', 'real-cookie-banner'), \join('<br /><br/ >', [\__('You should link all legally relevant subpages of your website (e.g. privacy policy, legal notice/imprint, terms and conditions) in the cookie banner so that they are quickly accessible. On these pages, the cookie banner should also not be displayed, so as not to put a barrier in front of the pages.', 'real-cookie-banner'), \__('So far, only privacy policy and legal notice could be linked. From now on you can link any other pages!', 'real-cookie-banner')]), ['linkText' => \__('Add more links to the cookie banner footer', 'real-cookie-banner'), 'callback' => $this->getConfigUrl('/settings'), 'previewImage' => $core->getBaseAssetsUrl(\__('upgrade-wizard/v4/banner-footer-links.png', 'real-cookie-banner'))]);
    }
    /**
     * Enable to collect telemetry data.
     *
     * @param array $result
     */
    public function enableTelemetry($result)
    {
        if (!\is_wp_error($result)) {
            $enabledFor = [];
            $licenses = Core::getInstance()->getRpmInitiator()->getPluginUpdater()->getUniqueLicenses(\true);
            foreach ($licenses as $license) {
                if ($license->getActivation()->isTelemetryDataSharingOptIn(\true)) {
                    $license->syncWithRemote();
                    $enabledFor[] = $license->getActivation()->getCode();
                }
            }
            $result['success'] = \true;
            $result['enabled_for'] = $enabledFor;
            $result['message'] = \__('Telemetry data enabled. Thanks for your support!', 'real-cookie-banner');
            $result['overrideAction'] = ['linkText' => ''];
        }
        return $result;
    }
    /**
     * Apply new customizer texts.
     *
     * @param array $result
     */
    public function applyNewTexts($result)
    {
        if (!\is_wp_error($result)) {
            $deletedOptionsTexts = $this->deleteCustomizerTexts(self::DELETE_LANGUAGES, self::DELETE_OPTIONS_TEXTS);
            // Update group texts
            $this->applyNewGroupTexts(self::DELETE_LANGUAGES);
            $result['success'] = \true;
            $result['deleted_options_texts'] = $deletedOptionsTexts;
            $result['redirect'] = \add_query_arg(['autofocus[section]' => Texts::SECTION, 'return' => \wp_get_raw_referer()], \admin_url('customize.php'));
        }
        return $result;
    }
    // Documented in AbstractDashboardTileMigration
    public function getId()
    {
        return 'v4';
    }
    // Documented in AbstractDashboardTileMigration
    public function getHeadline()
    {
        return \__('Updates in v4.0: You need to make adjustments!', 'real-cookie-banner');
    }
    // Documented in AbstractDashboardTileMigration
    public function getDescription()
    {
        return \join('<br /><br/ >', [\sprintf(
            // translators:
            \__('Discover the new Real Cookie Banner 4.0! With this update, we\'ve made legal adjustments to make your cookie banner legally even more rubust. Learn more about the changes in the <a href="%s" target="_blank">release notes</a> on our blog!', 'real-cookie-banner'),
            \__('https://devowl.io/news/real-cookie-banner-4-0/', 'real-cookie-banner')
        ), \__('<strong>You should definitely take a look at the following points and apply them to your cookie banner configuration!</strong> You decide which changes to activate or ignore - we don\'t make any fundamental changes without your consent.', 'real-cookie-banner')]);
    }
    // Documented in AbstractDashboardTileMigration
    public function isActive()
    {
        $isMajor4 = \version_compare(RCB_VERSION, '4.0.0', '>=');
        return $isMajor4 && $this->hasMajorPreviouslyInstalled(3);
    }
    // Documented in AbstractDashboardTileMigration
    public function dismiss()
    {
        return $this->removeMajorVersionFromPreviouslyInstalled(3);
    }
}
