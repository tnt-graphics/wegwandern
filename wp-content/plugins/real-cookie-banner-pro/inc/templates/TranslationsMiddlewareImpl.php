<?php

namespace DevOwl\RealCookieBanner\templates;

use DevOwl\RealCookieBanner\base\UtilsProvider;
use DevOwl\RealCookieBanner\Core;
use DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\middlewares\translations\TranslationsMiddleware;
use DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\templates\ServiceTemplate;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * See `TranslationsMiddleware`.
 * @internal
 */
class TranslationsMiddlewareImpl extends TranslationsMiddleware
{
    use UtilsProvider;
    /**
     * Active language map of WP-compatible locale => multilingual plugin code.
     *
     * @var string[]
     */
    private $activeLanguagesMap;
    /**
     * Resolve active language map once.
     *
     * @return string[]
     */
    protected function getActiveLanguagesMap()
    {
        if (\is_array($this->activeLanguagesMap)) {
            return $this->activeLanguagesMap;
        }
        $compLanguage = Core::getInstance()->getCompLanguage();
        $activeLanguages = $compLanguage->getActiveLanguages();
        if (\count($activeLanguages) === 0) {
            $activeLanguages[] = \DevOwl\RealCookieBanner\templates\TemplateConsumers::getContext();
        }
        $this->activeLanguagesMap = [];
        foreach ($activeLanguages as $activeLanguage) {
            $this->activeLanguagesMap[$compLanguage->getWordPressCompatibleLanguageCode($activeLanguage)] = $activeLanguage;
        }
        return $this->activeLanguagesMap;
    }
    /**
     * Normalize translation rows and enrich with language meta.
     *
     * @param array[] $rows
     * @return array[]
     */
    protected function normalizeTranslations($rows)
    {
        $compLanguage = Core::getInstance()->getCompLanguage();
        $activeLanguagesMap = $this->getActiveLanguagesMap();
        $result = [];
        foreach ($rows as $row) {
            if (!isset($activeLanguagesMap[$row['language']])) {
                continue;
            }
            $useLanguageCode = $activeLanguagesMap[$row['language']] ?? $row['language'];
            $row['isUntranslated'] = \boolval($row['isUntranslated']);
            $row['machineTranslationStatus'] = $row['machineTranslationStatus'];
            $row['flag'] = $compLanguage->getCountryFlag($useLanguageCode);
            $translatedName = $compLanguage->getTranslatedName($useLanguageCode);
            if ($row['language'] !== $translatedName) {
                $row['name'] = $translatedName;
            }
            $result[] = $row;
        }
        return $result;
    }
    // Documented in TranslationsMiddleware
    public function fetchTranslations($templates)
    {
        global $wpdb;
        $table_name = $this->getTableName(\DevOwl\RealCookieBanner\templates\StorageHelper::TABLE_NAME);
        $identifiers = [];
        foreach ($templates as $template) {
            $identifiers[] = $template->identifier;
        }
        $identifiers = \array_values(\array_unique($identifiers));
        if (\count($identifiers) === 0) {
            return [];
        }
        // A consumer instance does not mix types, so we can use the first template to determine the type
        $type = $templates[0] instanceof ServiceTemplate ? 'service' : 'blocker';
        // phpcs:disable WordPress.DB
        $rows = $wpdb->get_results(\sprintf("SELECT identifier, context AS `language`, is_untranslated AS isUntranslated, machine_translation_status AS machineTranslationStatus\n                    FROM {$table_name}\n                    WHERE identifier IN (%s) AND type = %s", \join(',', \array_map(function ($identifier) use($wpdb) {
            return $wpdb->prepare('%s', $identifier);
        }, $identifiers)), $wpdb->prepare('%s', $type)), ARRAY_A);
        // phpcs:enable WordPress.DB
        if (!\is_array($rows)) {
            return [];
        }
        $result = [];
        foreach ($rows as $row) {
            $identifier = $row['identifier'];
            unset($row['identifier']);
            $result[$identifier] = $result[$identifier] ?? [];
            $result[$identifier][] = $row;
        }
        foreach ($result as $identifier => $translations) {
            $result[$identifier] = $this->normalizeTranslations($translations);
        }
        foreach ($identifiers as $identifier) {
            if (!isset($result[$identifier])) {
                $result[$identifier] = [];
            }
        }
        return $result;
    }
}
