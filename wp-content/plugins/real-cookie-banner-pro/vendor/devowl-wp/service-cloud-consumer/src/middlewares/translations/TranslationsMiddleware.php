<?php

namespace DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\middlewares\translations;

use DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\middlewares\AbstractTemplateMiddleware;
use DevOwl\RealCookieBanner\Vendor\DevOwl\ServiceCloudConsumer\templates\AbstractTemplate;
use stdClass;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Template middleware that automatically creates a list of available translations to consumer data.
 * @internal
 */
abstract class TranslationsMiddleware extends AbstractTemplateMiddleware
{
    /**
     * Prefetched translations by identifier.
     *
     * @var array[][]
     */
    private $prefetchedTranslations = [];
    /**
     * Fetch available translations for templates with the following array scheme:
     *
     * ```
     * [
     *   'identifier' => [
     *      ['isUntranslated' => false, 'machineTranslationStatus' => 'no-translation' | 'full' | 'partly', 'language' => 'de_DE', 'name' => 'German', 'flag' => false],
     *      ...
     *   ],
     *   ...
     * ]
     * ```
     *
     * `name` can be optional.
     *
     * @param AbstractTemplate[] $templates
     * @return array[][] Map of identifier => translations
     */
    public abstract function fetchTranslations($templates);
    // Documented in AbstractTemplateMiddleware
    public function beforePersistTemplate($template, &$allTemplates)
    {
        // Silence is golden.
    }
    // Documented in AbstractTemplateMiddleware
    public function beforeUsingTemplate($template)
    {
        $result = [];
        $translations = $this->prefetchedTranslations[$template->identifier] ?? [];
        foreach ($translations as $translation) {
            $result[$translation['language']] = $translation;
        }
        if (\count($result) > 0) {
            $template->consumerData['translations'] = $result;
        }
    }
    // Documented in AbstractTemplateMiddleware
    public function beforeUsingTemplates(&$templates)
    {
        $this->prefetchedTranslations = $this->fetchTranslations($templates);
        parent::beforeUsingTemplates($templates);
    }
}
