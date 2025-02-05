<?php

namespace DevOwl\RealCookieBanner\templates;

use DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\settings\BannerLink as SettingsBannerLink;
use DevOwl\RealCookieBanner\base\UtilsProvider;
use DevOwl\RealCookieBanner\comp\language\Hooks;
use DevOwl\RealCookieBanner\Core;
use DevOwl\RealCookieBanner\lite\settings\TcfVendorConfiguration;
use DevOwl\RealCookieBanner\Localization;
use DevOwl\RealCookieBanner\scanner\Scanner;
use DevOwl\RealCookieBanner\settings\BannerLink;
use DevOwl\RealCookieBanner\settings\Blocker;
use DevOwl\RealCookieBanner\settings\Cookie;
use DevOwl\RealCookieBanner\settings\General;
use DevOwl\RealCookieBanner\settings\GoogleConsentMode;
use DevOwl\RealCookieBanner\settings\TCF;
use DevOwl\RealCookieBanner\Utils;
use DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\consumer\BlockerConsumer;
use DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\consumer\ConsumerPool;
use DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\consumer\ServiceConsumer;
use DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\consumer\VariableResolver;
use DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\templates\AbstractTemplate;
use DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\templates\BlockerTemplate;
use DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\templates\ServiceTemplate;
use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Utils as UtilsUtils;
use WP_Error;
use WP_Post;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Common service cloud consumer manager to consume local and external templates (e.g. service cloud).
 *
 * It makes use of `@devowl-wp/service-cloud-consumer`.
 * @internal
 */
class TemplateConsumers
{
    use UtilsProvider;
    const ONE_OF_PLUGIN_THEME_ACTIVE = 'is-wordpress-plugin-or-theme-active:';
    /**
     * Chunk templates to boost performance.
     *
     * But keep a bit lower, as service templates could be huge and feed the memory when doing `join`
     * for preparing the SQL statement.
     *
     * - 50 is too much, first customers are running into issues with a Memory Limit of 128M
     */
    const PERSIST_CHUNK_SIZE_TEMPLATES = 30;
    /**
     * All available consumers with key as context and value the `ConsumerPool`.
     *
     * @var ConsumerPool[]
     */
    private $pools = [];
    /**
     * Singleton instance.
     *
     * @var TemplateConsumers
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
     * Get the service cloud consumer pool for a given current context (e.g. WPML language).
     *
     * @param string $context
     */
    public function getPool($context)
    {
        if (empty($context)) {
            return null;
        }
        if (!isset($this->pools[$context])) {
            $serviceConsumer = new ServiceConsumer();
            $blockerConsumer = new BlockerConsumer();
            $this->fillVariableResolver($context, $serviceConsumer->getVariableResolver(), $serviceConsumer, $blockerConsumer);
            $this->fillVariableResolver($context, $blockerConsumer->getVariableResolver(), $serviceConsumer, $blockerConsumer);
            // Storage
            $serviceConsumer->setStorage(new \DevOwl\RealCookieBanner\templates\ServiceStorage($serviceConsumer));
            $blockerConsumer->setStorage(new \DevOwl\RealCookieBanner\templates\BlockerStorage($blockerConsumer));
            // Middlewares
            $serviceConsumer->addMiddleware(new \DevOwl\RealCookieBanner\templates\ConsumerMiddleware($serviceConsumer));
            $serviceConsumer->addMiddleware(new \DevOwl\RealCookieBanner\templates\RecommendedHooksMiddleware($serviceConsumer));
            $serviceConsumer->addMiddleware(new \DevOwl\RealCookieBanner\templates\PersistTranslationsMiddlewareImpl($serviceConsumer));
            $serviceConsumer->addMiddleware(new \DevOwl\RealCookieBanner\templates\TranslationsMiddlewareImpl($serviceConsumer));
            $blockerConsumer->addMiddleware(new \DevOwl\RealCookieBanner\templates\ConsumerMiddleware($blockerConsumer));
            $blockerConsumer->addMiddleware(new \DevOwl\RealCookieBanner\templates\PersistTranslationsMiddlewareImpl($blockerConsumer));
            $blockerConsumer->addMiddleware(new \DevOwl\RealCookieBanner\templates\TranslationsMiddlewareImpl($blockerConsumer));
            $blockerConsumer->addMiddleware(new \DevOwl\RealCookieBanner\templates\ServiceCloudConsumerExternalUrlNotifierMiddlewareImpl($blockerConsumer));
            // Data sources
            $serviceConsumer->addDataSource(new \DevOwl\RealCookieBanner\templates\ServiceLocalDataSource($serviceConsumer));
            $serviceConsumer->addDataSource(new \DevOwl\RealCookieBanner\templates\CloudDataSource($serviceConsumer));
            $blockerConsumer->addDataSource(new \DevOwl\RealCookieBanner\templates\CloudDataSource($blockerConsumer));
            $pool = new ConsumerPool([$serviceConsumer, $blockerConsumer]);
            $this->pools[$context] = $pool;
        }
        return $this->pools[$context];
    }
    /**
     * Get the service cloud consumer pool for the current context (e.g. WPML language).
     */
    public function getCurrentPool()
    {
        return $this->getPool(self::getContext());
    }
    /**
     * Fill the variable resolver with our values.
     *
     * @param string $context
     * @param VariableResolver $resolver
     * @param ServiceConsumer $serviceConsumer
     * @param BlockerConsumer $blockerConsumer
     */
    protected function fillVariableResolver($context, $resolver, $serviceConsumer, $blockerConsumer)
    {
        $variables = [
            // Custom properties
            'context' => $context,
            'cache.invalidate.key' => function () use($context) {
                $licenseActivationReceived = Core::getInstance()->getRpmInitiator()->getPluginUpdater()->getCurrentBlogLicense()->getActivation()->getReceived();
                return \json_encode([
                    // Download from cloud API for each language
                    $context,
                    // Download from cloud API when Real Cookie Banner got updated
                    RCB_VERSION,
                    // Download from cloud API when Real Cookie Banner license got changed
                    \is_array($licenseActivationReceived) ? $licenseActivationReceived['id'] : \false,
                ]);
            },
            // Consumer
            'consumer.privacyPolicyUrl' => function () {
                return BannerLink::getInstance()->getLegalLink(SettingsBannerLink::PAGE_TYPE_PRIVACY_POLICY, 'url');
            },
            'consumer.legalNoticeUrl' => function () {
                return BannerLink::getInstance()->getLegalLink(SettingsBannerLink::PAGE_TYPE_LEGAL_NOTICE, 'url');
            },
            'consumer.provider' => function () {
                $address = General::getInstance()->getOperatorContactAddress();
                return empty($address) ? \html_entity_decode(\get_bloginfo('name')) : $address;
            },
            'consumer.providerContact.phone' => function () {
                return General::getInstance()->getOperatorContactPhone();
            },
            'consumer.providerContact.email' => function () {
                return General::getInstance()->getOperatorContactEmail();
            },
            'consumer.providerContact.link' => function () {
                return General::getInstance()->getOperatorContactFormUrl('');
            },
            'consumer.host.main' => function () {
                return Utils::host(Utils::HOST_TYPE_MAIN);
            },
            'consumer.host.main+subdomains' => function () {
                return Utils::host(Utils::HOST_TYPE_MAIN_WITH_ALL_SUBDOMAINS);
            },
            'consumer.host.current' => function () {
                return Utils::host(Utils::HOST_TYPE_CURRENT);
            },
            'consumer.host.current+protocol' => function () {
                return Utils::host(Utils::HOST_TYPE_CURRENT_PROTOCOL);
            },
            'consumer.host.current+subdomains' => function () {
                return Utils::host(Utils::HOST_TYPE_CURRENT_WITH_ALL_SUBDOMAINS);
            },
            'template.fbPixel.scriptLocale' => function () {
                $default = 'en_US';
                $websiteLocale = \get_locale();
                if (Utils::startsWith($websiteLocale, 'de_DE')) {
                    return 'de_DE';
                } elseif (Utils::startsWith($websiteLocale, 'en_')) {
                    return 'en_US';
                }
                return $default;
            },
            'adminUrl' => function () {
                return \admin_url('/');
            },
            'blocker.consumer' => $blockerConsumer,
            'service.consumer' => $serviceConsumer,
            'tier' => $this->isPro() ? 'pro' : 'free',
            'isGcm' => GoogleConsentMode::getInstance()->isEnabled(),
            'manager' => General::getInstance()->getSetCookiesViaManager(),
            'oneOf' => [$this, 'oneOf'],
            'isTcfActive' => function () {
                return $this->isPro() && TCF::getInstance()->isActive();
            },
            'created.' . BlockerTemplate::class => function ($resolver) {
                return $this->blockerCreated($resolver);
            },
            'created.' . ServiceTemplate::class => function ($resolver) {
                return $this->servicesCreated($resolver);
            },
            'created.global.' . BlockerTemplate::class => function ($resolver) {
                return $this->blockerCreated($resolver, \true);
            },
            'created.global.' . ServiceTemplate::class => function ($resolver) {
                return $this->servicesCreated($resolver, \true);
            },
            'tcfVendors.created' => [$this, 'tcfVendorsCreated'],
            'serviceScan' => function () {
                return Core::getInstance()->getScanner()->getQuery()->getScannedTemplateStats();
            },
            'serviceScanIgnored' => function () {
                return Core::getInstance()->getNotices()->getScannerIgnored()['templates'];
            },
            // I18n
            'i18n.ContentTypeButtonTextMiddleware.loadContent' => function () {
                return \__('Load content', Hooks::TD_FORCED);
            },
            'i18n.ContentTypeButtonTextMiddleware.loadMap' => function () {
                return \__('Load map', Hooks::TD_FORCED);
            },
            'i18n.ContentTypeButtonTextMiddleware.loadForm' => function () {
                return \__('Load form', Hooks::TD_FORCED);
            },
            'i18n.disabled' => \__('Disabled', RCB_TD),
            'i18n.ExistsMiddleware.alreadyCreated' => \__('Already created', RCB_TD),
            'i18n.ExistsMiddleware.blockerAlreadyCreatedTooltip' => \__('You have already created a Content Blocker with this template.', RCB_TD),
            'i18n.ExistsMiddleware.serviceAlreadyCreatedTooltip' => \__('You have already created a Service (Cookie) with this template.', RCB_TD),
            // translators:
            'i18n.ManagerMiddleware.tooltip' => \__('This service template is optimized to work with %s.', RCB_TD),
            // translators:
            'i18n.ManagerMiddleware.disabledTooltip' => \__('Please activate %s in settings to use this template.', RCB_TD),
            'i18n.ServiceAvailableBlockerTemplatesMiddleware.tooltip' => \__('A suitable content blocker for this service can be created automatically.', RCB_TD),
            'i18n.GroupMiddleware.group.essential' => function () {
                return \__('Essential', Hooks::TD_FORCED);
            },
            'i18n.GroupMiddleware.group.functional' => function () {
                return \__('Functional', Hooks::TD_FORCED);
            },
            'i18n.GroupMiddleware.group.statistics' => function () {
                return \__('Statistics', Hooks::TD_FORCED);
            },
            'i18n.GroupMiddleware.group.marketing' => function () {
                return \__('Marketing', Hooks::TD_FORCED);
            },
            'i18n.OneOfMiddleware.disabledTooltip' => function () {
                return \sprintf(
                    // translators:
                    \__('This template is currently disabled because the respective WordPress plugin is not installed or the desired function is not active. <a href="%s" target="_blank">Learn more</a>', RCB_TD),
                    \__('https://devowl.io/knowledge-base/real-cookie-banner-disabled-cookie-templates/', RCB_TD)
                );
            },
            'i18n.TcfMiddleware.disabled' => \__('TCF required', RCB_TD),
            'i18n.TcfMiddleware.disabledTooltip' => \__('This template requires the integration of TCF, as the provider of this template uses this standard. Please activate this in the settings to be able to block this service.', RCB_TD),
            // translators:
            'i18n.CdnMiddleware.introduction' => \__('%s is a network of globally distributed servers that cache content and deliver it to your website visitors faster based on their location, reducing website load times and improving performance. This technology is also known as a content delivery network (CDN). {{strong}}Servers may also be located in countries that are considered unsafe in terms of data protection law.{{/strong}}', RCB_TD),
            // translators:
            'i18n.CdnMiddleware.introductionNoScc' => \__('In order to use the service in a way that complies with data protection regulations, the only practical option would be to conclude standard contractual clauses (SCCs) with %1$s that guarantee the safe processing of personal data of your website visitors (in particular IP addresses). Unfortunately, %1$s does not offer the option of concluding standard contractual clauses to our knowledge. Therefore, in our legal opinion, {{strong}}it is not possible to use this service in a legally compliant manner.{{/strong}}', RCB_TD),
            // translators:
            'i18n.CdnMiddleware.introductionNotEssential' => \__('%s unfortunately sets cookies that are not technically essential according to our knowledge, for which consent would be required. At the same time, the CDN cannot be blocked with a content blocker until the website visitors have given their consent, as otherwise parts of your website may malfunction. Therefore, in our legal opinion, {{strong}}it is not possible to use this service in a legally compliant manner.{{/strong}}', RCB_TD),
            // translators:
            'i18n.CdnMiddleware.introductionSccAndEmbedsOnlyExternalResources1' => \__('In order to use the service in a way that complies with data protection regulations, the only practical option would be to conclude standard contractual clauses (SCCs) with %s that guarantee the safe processing of personal data of your website visitors (in particular IP addresses).', RCB_TD),
            // translators:
            'i18n.CdnMiddleware.introductionSccAndEmbedsOnlyExternalResources2' => \__('{{strong}}Please make sure that you have concluded standard contract clauses with %1$s, which can be done on their website!{{/strong}} Since %1$s does not set cookies to our knowledge, we do not recommend creating a service in your cookie banner for it. However, you must mention the use of %1$s in your privacy policy.', RCB_TD),
            // translators:
            'i18n.CdnMiddleware.introductionRemoveService' => \__('Please remove %s from your website!', RCB_TD),
            // translators:
            'i18n.CdnMiddleware.moreInfoTitle' => \__('Why is %s integrated into my website at all?', RCB_TD),
            // translators:
            'i18n.CdnMiddleware.moreInfoDescription' => \__('If you do not consciously integrate %1$s, it is most likely that a plugin or theme you are using uses this CDN to load external scripts, fonts or media such as images. The best way to find out who is integrating the CDN is to disable the individual plugins or themes and scan again. Once you have found out where the integration is coming from, contact the developer of the plugin or theme to see if it can be used without %1$s or replace this plugin/theme!', RCB_TD),
            // translators:
            'i18n.CdnMiddleware.sccConclusionInstructionsNoticeTitle' => \__('How do I conclude standard contractual clauses with %s?', RCB_TD),
            'i18n.CdnMiddleware.buttonLabel' => \__('Acknowledged', RCB_TD),
        ];
        foreach ($variables as $key => $value) {
            $resolver->add($key, $value);
        }
    }
    /**
     * Implementation of created content blocker.
     *
     * @param VariableResolver $resolver
     * @param boolean $global
     */
    public function blockerCreated($resolver, $global = \false)
    {
        $consumer = $resolver->getConsumer();
        $result = [];
        $existing = Blocker::getInstance()->getOrdered(\false, \get_posts(Core::getInstance()->queryArguments(['post_type' => Blocker::CPT_NAME, 'numberposts' => -1, 'nopaging' => \true, 'meta_query' => [['key' => Blocker::META_NAME_PRESET_ID, 'compare' => 'EXISTS']], 'post_status' => ['publish', 'private', 'draft'], 'suppress_filters' => !$global], 'blockerWithTemplate')));
        foreach ($existing as $post) {
            $tmp = new BlockerTemplate($consumer);
            $tmp->identifier = $post->metas[Blocker::META_NAME_PRESET_ID];
            $tmp->consumerData['id'] = $post->ID;
            $tmp->consumerData['post'] = $post;
            $result[] = $tmp;
        }
        return $result;
    }
    /**
     * Implementation of created services.
     *
     * @param VariableResolver $resolver
     * @param boolean $global
     */
    public function servicesCreated($resolver, $global = \false)
    {
        $consumer = $resolver->getConsumer();
        $result = [];
        $existing = Cookie::getInstance()->getOrdered(null, \false, \get_posts(Core::getInstance()->queryArguments(['post_type' => Cookie::CPT_NAME, 'numberposts' => -1, 'nopaging' => \true, 'meta_query' => [['key' => Blocker::META_NAME_PRESET_ID, 'compare' => 'EXISTS']], 'post_status' => ['publish', 'private', 'draft'], 'suppress_filters' => !$global], 'servicesWithTemplate')));
        foreach ($existing as $post) {
            $tmp = new ServiceTemplate($consumer);
            $tmp->identifier = $post->metas[Blocker::META_NAME_PRESET_ID];
            $tmp->consumerData['id'] = $post->ID;
            $result[] = $tmp;
        }
        return $result;
    }
    /**
     * Implementation of `tcfVendors.created`.
     *
     * @param VariableResolver $resolver
     */
    public function tcfVendorsCreated($resolver)
    {
        if (!$this->isPro()) {
            return [];
        }
        $result = [];
        $existing = TcfVendorConfiguration::getInstance()->getOrdered(\false, \get_posts(Core::getInstance()->queryArguments(['post_type' => TcfVendorConfiguration::CPT_NAME, 'numberposts' => -1, 'nopaging' => \true, 'post_status' => ['publish', 'private', 'draft']], 'tcfVendorsCreated')));
        foreach ($existing as $post) {
            $result[] = ['vendorId' => $post->metas[TcfVendorConfiguration::META_NAME_VENDOR_ID], 'vendorConfigurationId' => $post->ID];
        }
        return $result;
    }
    /**
     * Create or update a WordPress post from a given template.
     *
     * @param ServiceTemplate|BlockerTemplate $template
     * @param int[] $assignToTerm The key of the array needs to be the taxonomy name
     * @param int $updatePostId
     * @param string[] $metaKeys
     * @param boolean $updateVersion
     */
    public function createFromTemplate($template, $assignToTerm = null, $updatePostId = null, $metaKeys = null, $updateVersion = \true)
    {
        $metaInput = [];
        $postType = '';
        $postTitle = $template->name;
        $postContent = '';
        if ($template instanceof ServiceTemplate) {
            $use = $template->use();
            $postContent = $use->purpose;
            $postType = Cookie::CPT_NAME;
            if ($metaKeys === null) {
                $metaKeys = Cookie::META_KEYS;
            }
            foreach ($metaKeys as $metaKey) {
                switch ($metaKey) {
                    case Blocker::META_NAME_PRESET_ID:
                    case Blocker::META_NAME_PRESET_VERSION:
                    case Cookie::META_NAME_UNIQUE_NAME:
                    case Cookie::META_NAME_CODE_DYNAMICS:
                        // Skip
                        break;
                    default:
                        if (\property_exists($use, $metaKey)) {
                            $metaInput[$metaKey] = $use->{$metaKey};
                        }
                        break;
                }
            }
        } elseif ($template instanceof BlockerTemplate) {
            $use = $template->use();
            $postContent = $use->description;
            $postType = Blocker::CPT_NAME;
            if ($metaKeys === null) {
                $metaKeys = Blocker::META_KEYS;
            }
            foreach ($metaKeys as $metaKey) {
                switch ($metaKey) {
                    case Blocker::META_NAME_PRESET_ID:
                    case Blocker::META_NAME_PRESET_VERSION:
                    case Blocker::META_NAME_VISUAL_DOWNLOAD_THUMBNAIL:
                        // Skip
                        break;
                    default:
                        if (\property_exists($use, $metaKey)) {
                            $metaInput[$metaKey] = $use->{$metaKey};
                        }
                        break;
                }
            }
        }
        if (\count($metaInput) > 0 || !empty($postContent) || !empty($postTitle)) {
            // `null` values or empty strings should not be persisted to database as they are handled through default-value
            foreach ($metaInput as $key => $value) {
                if ($value === null || $value === '') {
                    unset($metaInput[$key]);
                } elseif (!\is_scalar($value)) {
                    $metaInput[$key] = \wp_slash(\json_encode($value));
                }
            }
            if ($updateVersion) {
                $metaInput[Blocker::META_NAME_PRESET_ID] = $template->identifier;
                $metaInput[Blocker::META_NAME_PRESET_VERSION] = $template->version;
            }
            $postData = ['post_title' => $postTitle, 'post_content' => $postContent, 'meta_input' => $metaInput];
            if ($updatePostId !== null) {
                $post = \get_post($updatePostId);
                if ($post === null) {
                    return \false;
                }
                $result = \wp_update_post(\array_merge(['ID' => $post->ID], $postData), \true);
            } else {
                $result = \wp_insert_post(\array_merge(['post_type' => $postType, 'post_status' => 'publish'], $postData), \true);
            }
            if (\is_wp_error($result)) {
                return $result;
            }
            if (\is_array($assignToTerm)) {
                foreach ($assignToTerm as $taxonomy => $termId) {
                    \wp_set_object_terms($result, $termId, $taxonomy);
                }
            }
            return \is_int($result) ? $postData : $result;
        }
        return \false;
    }
    /**
     * Force re-download from all datasources. This also includes download from the cloud API instead
     * of "only" recalculating middlewares.
     */
    public function currentForceRedownload()
    {
        foreach ($this->getCurrentPool()->getConsumers() as $consumer) {
            /**
             * Storage.
             *
             * @var ServiceStorage|BlockerStorage
             */
            $storage = $consumer->getStorage();
            $storage->getHelper()->getExpireOption()->delete();
        }
    }
    /**
     * Delete all templates from storage except "Real Cookie Banner" template when license got deactivated.
     *
     * Otherwise, download from cloud API again.
     *
     * @param boolean $status
     */
    public function licenseStatusChanged($status)
    {
        global $wpdb;
        if ($status) {
            $this->currentForceRedownload();
            foreach ($this->getCurrentPool()->getConsumers() as $consumer) {
                $consumer->retrieve();
            }
        } else {
            $table_name = $this->getTableName(\DevOwl\RealCookieBanner\templates\StorageHelper::TABLE_NAME);
            // phpcs:disable WordPress.DB.PreparedSQL
            $wpdb->query("DELETE FROM {$table_name} WHERE is_cloud = 1");
            // phpcs:enable WordPress.DB.PreparedSQL
            // Deactivate current running scanner
            Core::getInstance()->getRealQueue()->getPersist()->deleteByType(Scanner::REAL_QUEUE_TYPE);
            \delete_option(\DevOwl\RealCookieBanner\templates\CloudDataSource::OPTION_NAME_LATEST_RESPONSE_RELEASE_INFO_PREFIX . \DevOwl\RealCookieBanner\templates\StorageHelper::TYPE_BLOCKER);
            \delete_option(\DevOwl\RealCookieBanner\templates\CloudDataSource::OPTION_NAME_LATEST_RESPONSE_RELEASE_INFO_PREFIX . \DevOwl\RealCookieBanner\templates\StorageHelper::TYPE_SERVICE);
        }
    }
    /**
     * Translate strings from our template-translations table.
     *
     * @param array $translation
     * @param string $input
     * @param string $sourceLocale
     * @param string $targetLocale
     * @param string $domain
     */
    public function translateInputFromTemplates($translation, $input, $sourceLocale, $targetLocale, $domain)
    {
        // Only translate our text domain and only if no translation is found yet
        if ($domain !== 'real-cookie-banner' || \is_array($translation) || empty($input)) {
            return $translation;
        }
        $potLanguages = (new Localization())->getPotLanguages();
        $this->probablyPersistTranslationsForRequestedLocales([$sourceLocale, $targetLocale]);
        // Find the original string in en_US (de_DE -> en_US, it_IT -> en_US)
        $enUsInput = $input;
        if (!empty($sourceLocale) && !\in_array($sourceLocale, $potLanguages, \true)) {
            // `$input` is already translated, so search by `target_content`
            $sourceTranslations = $this->getTranslations($sourceLocale);
            if (\count($sourceTranslations) > 0) {
                foreach ($sourceTranslations as $t) {
                    if ($t['target_content'] === $input || \wp_strip_all_tags($t['target_content']) === \wp_strip_all_tags($input)) {
                        $enUsInput = $t['source_content'];
                        break;
                    }
                }
                if ($enUsInput === $input) {
                    // We did not find a translation for this string (perhaps manually changed in form?)
                    return $translation;
                }
            }
        }
        $enUsInputStripped = \wp_strip_all_tags($enUsInput);
        if (\in_array($targetLocale, $potLanguages, \true)) {
            return [$enUsInput, $enUsInput];
        } else {
            $translationOfEnUsInput = null;
            $targetTranslations = $this->getTranslations($targetLocale);
            foreach ($targetTranslations as $t) {
                if ($t['source_content'] === $enUsInput || \wp_strip_all_tags($t['source_content']) === $enUsInputStripped) {
                    $translationOfEnUsInput = $t['target_content'];
                    break;
                }
            }
            if (!empty($translationOfEnUsInput)) {
                return [$enUsInput, $translationOfEnUsInput];
            } elseif ($enUsInput !== $input) {
                return [$enUsInput, $enUsInput];
            }
        }
        return $translation;
    }
    /**
     * Check for multiples locales template consumers if they need to be invalidated, invalidate them and
     * persist the translations into database through the `PoolMiddleware`.
     *
     * @param string[] $locales
     */
    public function probablyPersistTranslationsForRequestedLocales($locales)
    {
        foreach ($locales as $locale) {
            $pool = $this->getPool($locale);
            if ($pool === null) {
                continue;
            }
            $consumer = $pool->getConsumer(ServiceTemplate::class);
            if ($consumer !== null && $consumer->isInvalidatedThroughStorage()) {
                $consumer->retrieve();
            }
        }
    }
    /**
     * Get translations for a given target language (context). The source content is always english.
     * For performance reasons it makes use of the WordPress object cache.
     *
     * It is sorted by newly-added translations first.
     *
     * @param string $targetLanguage
     */
    public function getTranslations($targetLanguage)
    {
        global $wpdb;
        $found = \wp_cache_get($targetLanguage, \DevOwl\RealCookieBanner\templates\PersistTranslationsMiddlewareImpl::CACHE_GROUP);
        if (\is_array($found)) {
            return $found;
        }
        $table_name = $this->getTableName(\DevOwl\RealCookieBanner\templates\PersistTranslationsMiddlewareImpl::TABLE_NAME);
        // phpcs:disable WordPress.DB.PreparedSQL
        $rows = $wpdb->get_results($wpdb->prepare("SELECT source_content, target_content FROM {$table_name} WHERE target_language = %s ORDER BY id DESC", $targetLanguage), ARRAY_A);
        // phpcs:enable WordPress.DB.PreparedSQL
        \wp_cache_set($targetLanguage, $rows, \DevOwl\RealCookieBanner\templates\PersistTranslationsMiddlewareImpl::CACHE_GROUP);
        return $rows;
    }
    /**
     * Force recalculation for middlewares (this does not necessarily download from cloud API!) for all
     * blockers and services.
     */
    public function forceRecalculation()
    {
        global $wpdb;
        $table_name = $this->getTableName(\DevOwl\RealCookieBanner\templates\StorageHelper::TABLE_NAME);
        (new \DevOwl\RealCookieBanner\templates\StorageHelper(null))->updateOutdated();
        // phpcs:disable WordPress.DB.PreparedSQL
        $wpdb->query(\sprintf("UPDATE {$table_name} SET is_invalidate_needed = 1 WHERE %s", \join(' AND ', ['is_outdated = 0'])));
        // phpcs:enable WordPress.DB.PreparedSQL
    }
    /**
     * Add the release info of the latest cloud API template download.
     *
     * @param array $arr
     */
    public function revisionCurrent($arr)
    {
        $arr['cloud_release_info'] = [\DevOwl\RealCookieBanner\templates\StorageHelper::TYPE_BLOCKER => \get_option(\DevOwl\RealCookieBanner\templates\CloudDataSource::OPTION_NAME_LATEST_RESPONSE_RELEASE_INFO_PREFIX . \DevOwl\RealCookieBanner\templates\StorageHelper::TYPE_BLOCKER, null), \DevOwl\RealCookieBanner\templates\StorageHelper::TYPE_SERVICE => \get_option(\DevOwl\RealCookieBanner\templates\CloudDataSource::OPTION_NAME_LATEST_RESPONSE_RELEASE_INFO_PREFIX . \DevOwl\RealCookieBanner\templates\StorageHelper::TYPE_SERVICE, null)];
        return $arr;
    }
    /**
     * Implementation of `oneOf`.
     *
     * @param string $statement
     * @param AbstractTemplate $template
     */
    public function oneOf($statement, $template)
    {
        if (Utils::startsWith($statement, self::ONE_OF_PLUGIN_THEME_ACTIVE)) {
            $slug = \substr($statement, \strlen(self::ONE_OF_PLUGIN_THEME_ACTIVE));
            if (Utils::isPluginActive($slug)) {
                /**
                 * Allows you to deactivate a false-positive plugin template.
                 *
                 * Example: Someone has RankMath SEO active, but deactivated the GA function.
                 *
                 * Attention: This filter is only applied for active plugins!
                 *
                 * @hook RCB/Templates/FalsePositive
                 * @param {boolean} $isActive
                 * @param {string} $plugin The active plugin (can be slug or file)
                 * @param {string} $identifier The template identifier
                 * @param {string} $type Can be `service` or `blocker`
                 * @return {boolean}
                 * @since 3.16.0
                 */
                if (\apply_filters('RCB/Templates/FalsePositivePlugin', \true, $slug, $template->identifier, $template instanceof ServiceTemplate ? 'service' : 'blocker')) {
                    $pluginName = UtilsUtils::getActivePluginsMap()[$slug] ?? null;
                    return $pluginName !== null ? $pluginName : \true;
                }
            } elseif (Utils::isThemeActive($slug)) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * Shortcut to directly get the current context `ServiceConsumer`.
     */
    public static function getCurrentServiceConsumer()
    {
        return self::getInstance()->getCurrentPool()->getConsumer(ServiceTemplate::class);
    }
    /**
     * Shortcut to directly get the current context `ServiceConsumer`.
     */
    public static function getCurrentBlockerConsumer()
    {
        return self::getInstance()->getCurrentPool()->getConsumer(BlockerTemplate::class);
    }
    /**
     * Get singleton instance.
     *
     * @codeCoverageIgnore
     */
    public static function getInstance()
    {
        return self::$me === null ? self::$me = new \DevOwl\RealCookieBanner\templates\TemplateConsumers() : self::$me;
    }
    /**
     * Get the context key for consumer pool. Its the language for which we request templates.
     */
    public static function getContext()
    {
        $compLanguage = Core::getInstance()->getCompLanguage();
        $language = isset($_GET['_dataLocale']) ? \sanitize_text_field($_GET['_dataLocale']) : $compLanguage->getCurrentLanguage();
        if ($compLanguage->isActive()) {
            $language = $compLanguage->getWordPressCompatibleLanguageCode($language);
        }
        // Fallback to blog language
        if (empty($language)) {
            $language = \str_replace('-', '_', \get_locale());
        }
        return $language;
    }
}
