<?php

namespace DevOwl\RealCookieBanner\Vendor\DevOwl\Multilingual;

use WP_Post;
use WP_Term;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * WPML language handler.
 * @internal
 */
class WPML extends AbstractSyncPlugin
{
    private $disabledCopyAndSync = \false;
    /**
     * True while `sitepress->make_duplicate()` runs (Translation Dashboard duplicate).
     * `Sync::save_post` must not create extra language copies during this window.
     */
    private $duplicateCreationInProgress = \false;
    /**
     * True while WPML taxonomy translation UI saves a term (`wp_ajax_wpml_save_term`).
     * `Sync::created_term` must not spawn copies in other languages during this window.
     */
    private $wpmlTermSaveInProgress = \false;
    /**
     * Per-master taxonomy assignments captured at `icl_before_make_duplicate`.
     *
     * WPML can clear or rewrite `wp_term_relationships` on the master post before DevOwl
     * `copyPostTaxonomies()` runs. The snapshot preserves ids that were still on the master at
     * duplicate start (typical stack: DE translation still wired to EN `rcb-cookie-group` term ids).
     * Consumed once when resolving source terms during {@see resolveSourceTermsByTaxonomyForPostCopy()}.
     *
     * @var array<int, array<string, int[]>> master post id => taxonomy => term ids
     */
    private $duplicateSourceTermsByMaster = [];
    // Documented in AbstractSyncPlugin
    public function switch($locale)
    {
        global $sitepress;
        $sitepress->switch_lang($locale);
    }
    // Documented in AbstractSyncPlugin
    public function copyTermToOtherLanguage($locale, $currentLanguage, $term_id, $taxonomy, $meta)
    {
        global $sitepress;
        $createdTermId = parent::copyTermToOtherLanguage($locale, $currentLanguage, $term_id, $taxonomy, $meta);
        if (!$createdTermId) {
            return \false;
        }
        // Create term translation (https://wordpress.stackexchange.com/a/309046/83335)
        $originalElementId = \get_term($term_id)->term_taxonomy_id;
        $createdElementId = \get_term($createdTermId)->term_taxonomy_id;
        $trid = $sitepress->get_element_trid($originalElementId, 'tax_' . $taxonomy);
        $sitepress->set_element_language_details($createdElementId, 'tax_' . $taxonomy, $trid, $locale, $this->getSourceLanguage($term_id, $taxonomy, $currentLanguage));
        return $createdTermId;
    }
    // Documented in AbstractSyncPlugin
    public function copyPostToOtherLanguage($locale, $currentLanguage, $post_id, $meta, $taxonomies)
    {
        global $sitepress;
        // Read post
        $post = \get_post($post_id);
        // Create
        $created = parent::copyPostToOtherLanguage($locale, $currentLanguage, $post_id, $meta, $taxonomies);
        if (!$created) {
            return \false;
        }
        // WPML has an issue that the `trid` is not yet known directly in `save_post` (https://wpml.org/forums/topic/get_element_trid-returns-null/)
        // So, the posts are linked through the `shutdown` action.
        \add_action('shutdown', function () use($sitepress, $post, $post_id, $created, $locale, $currentLanguage) {
            $trid = $sitepress->get_element_trid($post_id, 'post_' . $post->post_type);
            $sitepress->set_element_language_details($created, 'post_' . $post->post_type, $trid, $locale, $this->getSourceLanguage($post_id, $post->post_type, $currentLanguage));
        });
        return $created;
    }
    // Documented in AbstractLanguagePlugin
    public function getActiveLanguages()
    {
        return \array_keys(\apply_filters('wpml_active_languages', []));
    }
    // Documented in AbstractLanguagePlugin
    public function getLanguageSwitcher()
    {
        $result = [];
        foreach (\apply_filters('wpml_active_languages', [], ['skip_missing' => 0]) as $row) {
            $result[] = ['name' => $row['native_name'], 'current' => \boolval($row['active'] ?? \false), 'flag' => $row['country_flag_url'] ?? '', 'url' => $row['url'], 'locale' => $row['language_code']];
        }
        return $result;
    }
    // Documented in AbstractLanguagePlugin
    public function getTranslatedName($locale)
    {
        $activeLanguages = \apply_filters('wpml_active_languages', []);
        return isset($activeLanguages[$locale]) ? $activeLanguages[$locale]['translated_name'] : $locale;
    }
    // Documented in AbstractLanguagePlugin
    public function getCountryFlag($locale)
    {
        $settings = \apply_filters('wpml_active_languages', []);
        if (isset($settings[$locale])) {
            return $settings[$locale]['country_flag_url'] ?? \false;
        }
        return \false;
    }
    // Documented in AbstractLanguagePlugin
    public function getPermalink($url, $locale)
    {
        return \apply_filters('wpml_permalink', $url, $locale, \true);
    }
    // Documented in AbstractLanguagePlugin
    public function getWordPressCompatibleLanguageCode($locale)
    {
        $objects = \apply_filters('wpml_active_languages', []);
        return isset($objects[$locale]) ? $objects[$locale]['default_locale'] : $locale;
    }
    // Documented in AbstractLanguagePlugin
    public function getDefaultLanguage()
    {
        global $sitepress;
        return $sitepress->get_default_language();
    }
    // Documented in AbstractLanguagePlugin
    public function getCurrentLanguage()
    {
        global $sitepress;
        return $sitepress->get_current_language();
    }
    // Documented in AbstractLanguagePlugin
    public function getPostLanguage($id)
    {
        $result = \apply_filters('wpml_post_language_details', '', $id);
        return \is_array($result) ? $result['language_code'] : '';
    }
    // Documented in AbstractLanguagePlugin
    public function getTermLanguage($termTaxonomyId, $taxonomy)
    {
        $result = \apply_filters('wpml_element_language_details', null, ['element_id' => $termTaxonomyId, 'element_type' => 'tax_' . $taxonomy]);
        return \is_object($result) && isset($result->language_code) ? $result->language_code : '';
    }
    // Documented in AbstractSyncPlugin
    public function isExternalTranslationSyncDeferredForPost($postId)
    {
        // Only while `make_duplicate` runs — not for posts that still had `_icl_lang_duplicate_of`
        // (we remove that meta after `syncPostFromExternalSource` so DevOwl Sync owns updates).
        return $this->duplicateCreationInProgress;
    }
    /**
     * Resolve source taxonomy ids for {@see AbstractSyncPlugin::copyPostTaxonomies()} (WPML).
     *
     * Three fallbacks, first non-empty wins:
     *
     * 1. **Live read** ({@see AbstractSyncPlugin::resolveSourceTermsByTaxonomyForPostCopy()}):
     *    `fetchObjectTermIdsWithoutFilters()` on `$from`. Succeeds when relationships are still on
     *    the master at copy time (normal duplicate, or cross-language term ids still stored).
     *
     * 2. **Duplicate snapshot** (`$duplicateSourceTermsByMaster`): ids captured on `$from` in
     *    `icl_before_make_duplicate` before WPML clears the master, then unset here. Needed when
     *    step 1 is empty because WPML already removed assignments (common for DE → EN duplicate of
     *    a DE cookie wired to EN group terms). Empty when no taxonomies were on the master at hook
     *    time or this master was not part of the current duplicate run.
     *
     * 3. **Translation sibling** (trid scan below): read from another post in the same WPML trid
     *    when steps 1 and 2 are empty, but a sibling translation still has assignments (e.g. master
     *    already cleared, EN row not deleted yet). Does **not** help when every translation in the
     *    trid has empty taxonomies (e.g. EN master removed and only DE remains) — then step 2 must
     *    have captured terms at `icl_before_make_duplicate`. Snapshot fixes “WPML cleared the master
     *    between hook and copy”; siblings fix “master empty, another language row still has groups”.
     *
     * @param int $from Source post id (WPML master for the duplicate being built)
     * @param string[] $taxonomies Taxonomy slugs from Sync post configuration
     * @return array<string, int[]> taxonomy slug => term ids to remap onto the target locale
     */
    protected function resolveSourceTermsByTaxonomyForPostCopy($from, array $taxonomies)
    {
        $sourceTermsByTaxonomy = parent::resolveSourceTermsByTaxonomyForPostCopy($from, $taxonomies);
        if ($sourceTermsByTaxonomy !== []) {
            return $sourceTermsByTaxonomy;
        }
        $from = (int) $from;
        $sourceTermsByTaxonomy = $this->duplicateSourceTermsByMaster[$from] ?? [];
        unset($this->duplicateSourceTermsByMaster[$from]);
        if ($sourceTermsByTaxonomy !== []) {
            return $sourceTermsByTaxonomy;
        }
        global $sitepress;
        $post = \get_post($from);
        if (!isset($sitepress) || !$post instanceof WP_Post) {
            return [];
        }
        $elementType = 'post_' . $post->post_type;
        $trid = (int) $sitepress->get_element_trid($from, $elementType);
        if ($trid <= 0) {
            return [];
        }
        $translations = $sitepress->get_element_translations($trid, $elementType);
        if (!\is_array($translations)) {
            return [];
        }
        $excludePostId = (int) ($this->taxonomySourceResolutionExcludePostId ?? 0);
        foreach ($translations as $translation) {
            $altPostId = isset($translation->element_id) ? (int) $translation->element_id : 0;
            if ($altPostId <= 0 || $altPostId === $from || $excludePostId > 0 && $altPostId === $excludePostId) {
                continue;
            }
            $sourceTermsByTaxonomy = parent::resolveSourceTermsByTaxonomyForPostCopy($altPostId, $taxonomies);
            if ($sourceTermsByTaxonomy !== []) {
                return $sourceTermsByTaxonomy;
            }
        }
        return [];
    }
    // Documented in AbstractSyncPlugin
    public function isExternalTranslationSyncDeferredForTerm($term_id, $taxonomy)
    {
        if ($this->wpmlTermSaveInProgress) {
            return \true;
        }
        $term = \get_term($term_id, $taxonomy);
        if (!$term instanceof WP_Term) {
            return \false;
        }
        $details = \apply_filters('wpml_element_language_details', null, ['element_id' => $term->term_taxonomy_id, 'element_type' => 'tax_' . $taxonomy]);
        return \is_object($details) && !empty($details->source_language_code);
    }
    // Documented in AbstractSyncPlugin
    public function getOriginalPostId($id, $post_type)
    {
        return \intval(\icl_object_id($id, $post_type, \false, $this->getDefaultLanguage()));
    }
    // Documented in AbstractSyncPlugin
    public function getOriginalTermId($id, $taxonomy)
    {
        return \intval(\icl_object_id($id, $taxonomy, \false, $this->getDefaultLanguage()));
    }
    // Documented in AbstractLanguagePlugin
    public function getPostTranslationIds($id, $post_type)
    {
        $result = [];
        $trid = \apply_filters('wpml_element_trid', null, $id, 'post_' . $post_type);
        $translations = \apply_filters('wpml_get_element_translations', null, $trid, 'post_' . $post_type);
        if (\is_array($translations)) {
            foreach ($translations as $translation) {
                $result[$translation->language_code] = \intval($translation->element_id);
            }
        }
        return $result;
    }
    // Documented in AbstractLanguagePlugin
    public function getTaxonomyTranslationIds($id, $taxonomy)
    {
        $result = [];
        $trid = \apply_filters('wpml_element_trid', null, $id, 'tax_' . $taxonomy);
        $translations = \apply_filters('wpml_get_element_translations', null, $trid, 'tax_' . $taxonomy);
        if (\is_array($translations)) {
            foreach ($translations as $translation) {
                $result[$translation->language_code] = \intval($translation->element_id);
            }
        }
        return $result;
    }
    // Documented in AbstractSyncPlugin
    public function getCurrentPostId($id, $post_type, $locale = null)
    {
        if ($locale !== null && isset($this->copyToOtherLanguageMap['post'][$locale])) {
            $map = $this->copyToOtherLanguageMap['post'][$locale];
            if (isset($map[$id])) {
                return $map[$id];
            }
        }
        // When `$locale` is set (meta remap, REST), do not fall back to the source post id — same rule as
        // `getCurrentTermId()` (`return_original` false). `true` is only for "current language" lookups.
        $returnOriginal = $locale === null;
        return \intval(\icl_object_id($id, $post_type, $returnOriginal, $locale === null ? $this->getCurrentLanguage() : $locale));
    }
    // Documented in AbstractSyncPlugin
    public function getCurrentTermId($id, $taxonomy, $locale = null)
    {
        if ($locale !== null && isset($this->copyToOtherLanguageMap['term'][$locale])) {
            $map = $this->copyToOtherLanguageMap['term'][$locale];
            if (isset($map[$id])) {
                return $map[$id];
            }
        }
        $lang = $locale === null ? $this->getCurrentLanguage() : $locale;
        // `true` falls back to the source term id in another language (wrong group on DE cookies).
        // Callers that need a real translation compare `$resolved !== $sourceTermId` before assigning.
        $resolved = \intval(\icl_object_id($id, $taxonomy, \false, $lang));
        return $resolved > 0 ? $resolved : $id;
    }
    /**
     * Get the source language of a given element id and element type.
     *
     * @param int $element_id
     * @param string $element_type
     * @param mixed $fallback
     */
    protected function getSourceLanguage($element_id, $element_type, $fallback = null)
    {
        $details = \apply_filters('wpml_element_language_details', null, ['element_id' => $element_id, 'element_type' => $element_type]);
        if (\is_object($details) && !empty($details->source_language_code)) {
            return $details->source_language_code;
        }
        if (\is_array($details) && !empty($details['source_language_code'])) {
            return $details['source_language_code'];
        }
        return $fallback;
    }
    // Documented in AbstractLanguagePlugin
    public function disableCopyAndSync($sync)
    {
        if ($this->disabledCopyAndSync) {
            return;
        }
        $this->disabledCopyAndSync = \true;
        // WPML Translation Dashboard "Duplicate" copies post meta via SQL and runs
        // `wpml_duplicate_generic_string`, which can empty translatable custom fields.
        // Our Sync config marks fields as copy/copy-once — preserve the master value.
        \add_filter('wpml_duplicate_generic_string', function ($value, $lang, $meta_data) use($sync) {
            if (($meta_data['context'] ?? '') !== 'custom_field') {
                return $value;
            }
            $meta_key = $meta_data['key'] ?? '';
            $master_post_id = (int) ($meta_data['master_post_id'] ?? 0);
            if ($master_post_id <= 0 || $meta_key === '') {
                return $value;
            }
            $copyKeys = $this->getConfiguredPostMetaCopyKeys($sync, $master_post_id);
            if ($copyKeys === null || !\in_array($meta_key, $copyKeys, \true)) {
                return $value;
            }
            if (!\metadata_exists('post', $master_post_id, $meta_key)) {
                return $value;
            }
            return \get_post_meta($master_post_id, $meta_key, \true);
        }, 999, 3);
        \add_action('wpml_before_make_duplicate', function () {
            $this->duplicateCreationInProgress = \true;
        }, 0, 0);
        // Snapshot master taxonomy ids before WPML clears relationships (see
        // resolveSourceTermsByTaxonomyForPostCopy() step 2).
        \add_action('icl_before_make_duplicate', function ($master_post_id) use($sync) {
            $this->duplicateCreationInProgress = \true;
            $post = \get_post($master_post_id);
            if (!$post instanceof WP_Post) {
                return;
            }
            $configuration = $sync->getPostsConfiguration()[$post->post_type] ?? null;
            if ($configuration === null) {
                return;
            }
            $taxonomies = $configuration['taxonomies'] ?? [];
            if ($taxonomies === []) {
                return;
            }
            $snapshot = [];
            foreach ($taxonomies as $taxonomy) {
                $termIds = $this->fetchObjectTermIdsWithoutFilters((int) $master_post_id, $taxonomy);
                if ($termIds !== []) {
                    $snapshot[$taxonomy] = $termIds;
                }
            }
            if ($snapshot !== []) {
                $this->duplicateSourceTermsByMaster[(int) $master_post_id] = $snapshot;
            }
        }, 0, 2);
        \add_action('icl_make_duplicate', function ($master_post_id, $lang, $post_array, $duplicate_id) use($sync) {
            // WPML has already duplicated the post and SQL-copied meta. We only add taxonomy
            // remapping + translated title/content — not a second full meta copy (RCB JSON meta
            // can be megabytes and was a contributor to TM duplicate OOM / emptied fields).
            $post = \get_post($master_post_id);
            if (!$post instanceof WP_Post) {
                return;
            }
            $configuration = $sync->getPostsConfiguration()[$post->post_type] ?? null;
            if ($configuration === null) {
                return;
            }
            $taxonomies = $configuration['taxonomies'] ?? [];
            $this->syncPostFromExternalSource($master_post_id, $duplicate_id, $lang, $taxonomies, $configuration);
            // Same detach as `wpml_copy_post_to_language` with `$mark_as_duplicate === false`:
            // stops WPML `sync_with_duplicates` from mirroring master saves onto this translation.
            \delete_post_meta($duplicate_id, '_icl_lang_duplicate_of');
        }, 20, 4);
        \add_action('icl_make_duplicate', function () {
            $this->duplicateCreationInProgress = \false;
        }, 9999, 0);
        \add_action('wp_ajax_wpml_save_term', [$this, 'beforeWpmlSaveTerm'], 0);
        \add_action('wp_ajax_wpml_save_term', [$this, 'endWpmlSaveTerm'], 9999, 0);
        \add_action('created_term', [$this, 'syncTermAfterWpmlTranslation'], 20, 3);
    }
    /**
     * Mark WPML taxonomy term save so `Sync::created_term` does not recurse.
     */
    public function beforeWpmlSaveTerm()
    {
        $this->wpmlTermSaveInProgress = \true;
    }
    /**
     * Clear the WPML taxonomy save flag after the AJAX handler finishes.
     */
    public function endWpmlSaveTerm()
    {
        $this->wpmlTermSaveInProgress = \false;
    }
    /**
     * After WPML linked a new term translation, apply PO translations and copy meta from the source term.
     *
     * Runs on `created_term` (priority 20), after WPML's `create_term` language assignment (priority 1).
     * Uses `wpml_element_language_details` instead of `$_POST` so CLI/API paths work too.
     *
     * @param int $term_id
     * @param int $tt_id
     * @param string $taxonomy
     */
    public function syncTermAfterWpmlTranslation($term_id, $tt_id, $taxonomy)
    {
        $sync = $this->getSync();
        if ($sync === null) {
            return;
        }
        $configuration = $sync->getTaxonomies()[$taxonomy] ?? null;
        if ($configuration === null) {
            return;
        }
        $term = \get_term($term_id, $taxonomy);
        if (!$term instanceof WP_Term) {
            return;
        }
        $elementType = 'tax_' . $taxonomy;
        $details = \apply_filters('wpml_element_language_details', null, ['element_id' => $term->term_taxonomy_id, 'element_type' => $elementType]);
        if (!\is_object($details) || empty($details->source_language_code)) {
            return;
        }
        $sourceLocale = $details->source_language_code;
        $targetLocale = isset($details->language_code) ? $details->language_code : '';
        if ($targetLocale === '' || $sourceLocale === $targetLocale) {
            return;
        }
        global $sitepress;
        $trid = isset($details->trid) ? (int) $details->trid : 0;
        if ($trid <= 0) {
            $trid = (int) $sitepress->get_element_trid($term->term_taxonomy_id, $elementType);
        }
        if ($trid <= 0) {
            return;
        }
        $translations = $sitepress->get_element_translations($trid, $elementType);
        if (!\is_array($translations)) {
            return;
        }
        $sourceTtid = isset($translations[$sourceLocale]) ? (int) $translations[$sourceLocale]->element_id : 0;
        $targetTtid = isset($translations[$targetLocale]) ? (int) $translations[$targetLocale]->element_id : 0;
        if ($sourceTtid <= 0 || $targetTtid <= 0) {
            return;
        }
        $sourceTerm = \get_term_by('term_taxonomy_id', $sourceTtid, $taxonomy);
        $targetTerm = \get_term_by('term_taxonomy_id', $targetTtid, $taxonomy);
        if (!$sourceTerm instanceof WP_Term || !$targetTerm instanceof WP_Term) {
            return;
        }
        $metaKeys = \array_unique(\array_merge($configuration['meta']['copy-once'] ?? [], $configuration['meta']['copy'] ?? []));
        $this->syncTermFromExternalSource($sourceTerm->term_id, $targetTerm->term_id, $metaKeys, $targetLocale);
    }
    /**
     * Meta keys configured for copy sync on the post's type, if any.
     *
     * @param Sync $sync
     * @param int $post_id
     * @return string[]|null
     */
    private function getConfiguredPostMetaCopyKeys($sync, $post_id)
    {
        $post = \get_post($post_id);
        if (!$post instanceof WP_Post) {
            return null;
        }
        $configuration = $sync->getPostsConfiguration()[$post->post_type] ?? null;
        if ($configuration === null) {
            return null;
        }
        return \array_unique(\array_merge($configuration['meta']['copy-once'] ?? [], $configuration['meta']['copy'] ?? []));
    }
    /**
     * Check if WPML is active.
     */
    public static function isPresent()
    {
        return \is_plugin_active('sitepress-multilingual-cms/sitepress.php');
    }
}
