<?php

namespace DevOwl\RealCookieBanner\Vendor\DevOwl\Multilingual;

use DevOwl\RealCookieBanner\Vendor\MatthiasWeb\Utils\Utils;
use WP_Post;
use WP_Term;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * There are plugins like WPML or PolyLang which allows you to create a multilingual
 * WordPress installation. devowl.io plugins needs to be compatible with those plugins
 * so this abstract implementation handles actions like get original ID and get default language.
 * @internal
 */
abstract class AbstractSyncPlugin extends AbstractLanguagePlugin
{
    use SyncFromExternalSourceTrait;
    /**
     * `copyTermToOtherLanguage` and `copyPostToOtherLanguage` create a duplicate of a term and post. This map
     * holds the mapping of original-ID to translation-ID. This map is filled with the translations like this:
     *
     * ```
     * {
     *   "post": {
     *     "de": {
     *          800: 801
     *     }
     *   }
     * }
     * ```
     */
    protected $copyToOtherLanguageMap = ['post' => [], 'term' => []];
    /**
     * During `syncPostFromExternalSource()`, the duplicate post id excluded from WPML taxonomy
     * sibling fallback (the in-progress translation must not supply term assignments as source).
     *
     * @var int|null
     */
    protected $taxonomySourceResolutionExcludePostId = null;
    /**
     * Set while `assignTranslatedPostTerms()` calls `wp_set_object_terms()`.
     *
     * Without this, `onSetObjectTermsCopyPostTaxonomies()` would fan out again on the same write and
     * recurse until PHP memory is exhausted (observed on WPML TM batch duplicate).
     */
    private $postTaxonomyCopyingInProgress = \false;
    /**
     * Master post id => { sourceLocale, taxonomies, to: locale => translation post id }.
     *
     * Feeds the single global `set_object_terms` listener below. Replaces registering a new closure
     * on every `copyPostTaxonomies()` call (the old pattern grew `$wp_filter` without bound during
     * duplicate batches and contributed to OOM).
     *
     * @var array<int, array{sourceLocale: string, taxonomies: string[], to: array<string, int>}>
     */
    private $postTaxonomyCopyTargets = [];
    /**
     * Ensures exactly one `set_object_terms` hook exists for the whole request.
     */
    private static $postTaxonomySetObjectTermsHookRegistered = \false;
    // Documented in AbstractLanguagePlugin
    public function getSkipHTMLForTag($force = \false)
    {
        return '';
    }
    // Documented in AbstractLanguagePlugin
    public function maybePersistTranslation($sourceContent, $content, $sourceLocale, $targetLocale, $force = \false)
    {
        // Silence is golden.
    }
    // Documented in AbstractLanguagePlugin
    public function isCurrentlyInEditorPreview()
    {
        return \false;
    }
    // Documented in AbstractLanguagePlugin
    public function translateStrings(&$content, $locale, $context = null)
    {
        return $content;
    }
    // Documented in AbstractLanguagePlugin
    public function translatableStrings($content)
    {
        return [];
    }
    /**
     * This method is called due to `Sync::created_term`. It allows you to get a list of all
     * translations of a term in an associate array.
     *
     * @param int[] $translations
     */
    public function termCopiedToAllOtherLanguages($translations)
    {
        // Silence is golden.
    }
    /**
     * This method is called due to `Sync::save_post`. It allows you to get a list of all
     * translations of a post in an associate array.
     *
     * @param int[] $translations
     */
    public function postCopiedToAllOtherLanguages($translations)
    {
        // Silence is golden.
    }
    /**
     * This method is called due to `Sync::created_term`. It allows you to assign a term to a given language.
     *
     * @param int $termId
     * @param string $locale
     */
    public function setTermLanguage($termId, $locale)
    {
        // Silence is golden.
    }
    /**
     * This method is called due to `Sync::save_post`. It allows you to assign a post to a given language.
     *
     * @param int $postId
     * @param string $locale
     */
    public function setPostLanguage($postId, $locale)
    {
        // Silence is golden.
    }
    /**
     * Translate given term to other language.
     *
     * @param string $locale
     * @param string $currentLanguage
     * @param int $term_id
     * @param string $taxonomy
     * @param string[] $meta The meta keys to copy
     * @return int|boolean The new created term id
     */
    public function copyTermToOtherLanguage($locale, $currentLanguage, $term_id, $taxonomy, $meta)
    {
        // WPML/PolyLang may already link this term (e.g. after `CopyContent::copyAll()` on reset).
        // Creating another translation fires `created_term` again and multiplies terms + hook work
        // (duplicate service groups on `edit-tags.php`, extra memory on TM duplicate).
        $translations = $this->getTaxonomyTranslationIds($term_id, $taxonomy);
        if (isset($translations[$locale])) {
            $existing = (int) $translations[$locale];
            if ($existing > 0 && $existing !== (int) $term_id) {
                return $existing;
            }
        }
        // Read term
        $term = \get_term($term_id);
        // Create
        $createdTermId = $this->duplicateTerm($term, $taxonomy);
        if (\is_wp_error($createdTermId)) {
            return \false;
        }
        $this->copyToOtherLanguageMap['term'][$locale] = $this->copyToOtherLanguageMap['term'][$locale] ?? [];
        $this->copyToOtherLanguageMap['term'][$locale][$term_id] = $createdTermId;
        $this->duplicateTermMeta($term_id, $createdTermId, $meta);
        return $createdTermId;
    }
    /**
     * Translate given post to other language.
     *
     * @param string $locale
     * @param string $currentLanguage
     * @param string $post_id
     * @param string[] $meta The meta keys to copy
     * @param string[] $taxonomies The taxonomies to copy
     * @return int|boolean The new created post id
     */
    public function copyPostToOtherLanguage($locale, $currentLanguage, $post_id, $meta, $taxonomies)
    {
        // Read post
        $post = \get_post($post_id);
        // Create
        $created = $this->duplicatePost($post);
        if ($created === 0) {
            return \false;
        }
        $this->copyToOtherLanguageMap['post'][$locale] = $this->copyToOtherLanguageMap['post'][$locale] ?? [];
        $this->copyToOtherLanguageMap['post'][$locale][$post_id] = $created;
        $this->copyPostTaxonomies($post_id, $created, $taxonomies, $locale);
        $this->duplicatePostMeta($post_id, $created, $meta);
        return $created;
    }
    /**
     * A simple `get_term` => `wp_insert_term` wrapper.
     *
     * @param WP_Term $term
     * @param string $taxonomy
     */
    protected function duplicateTerm($term, $taxonomy)
    {
        list(, $name) = $this->translateInput($term->name);
        list(, $description) = $this->translateInput($term->description);
        // Create
        $created = \wp_insert_term($name, $taxonomy, [
            'slug' => $name . '-' . $this->getCurrentLanguageFallback(),
            // E. g. PolyLang reports "Term already exists"
            'description' => $description,
        ]);
        return \is_wp_error($created) ? $created : $created['term_id'];
    }
    /**
     * A simple `get_post` => `wp_insert_post` wrapper.
     *
     * @param WP_Post $post
     */
    protected function duplicatePost($post)
    {
        // Translate name and description
        $args = ['post_title' => $this->translateInput($post->post_title)[1], 'post_content' => $this->translateInput($post->post_content)[1], 'post_status' => $post->post_status, 'post_type' => $post->post_type, 'menu_order' => $post->menu_order];
        // Create
        return \wp_insert_post($args);
    }
    /**
     * Listen to term meta addition and copy.
     *
     * @param int $from
     * @param int $to
     * @param string[] $meta Meta keys to copy
     */
    protected function duplicateTermMeta($from, $to, $meta)
    {
        $this->duplicateMeta('term', $from, $to, $meta);
    }
    /**
     * Copy already existing meta as it can be inserted with `meta_input` directly.
     * Additionally listen to term meta addition and copy.
     *
     * @param int $from
     * @param int $to
     * @param string[] $meta Meta keys to copy
     */
    public function duplicatePostMeta($from, $to, $meta)
    {
        $customMeta = \get_post_custom($from);
        foreach ($customMeta as $key => $values) {
            if (\in_array($key, $meta, \true)) {
                foreach ($values as $value) {
                    \add_post_meta($to, $key, $this->filterMetaValue('post', $from, $to, $key, $value, $this->getCurrentLanguageFallback()));
                }
            }
        }
        $this->duplicateMeta('post', $from, $to, $meta);
    }
    /**
     * Run a callback with the source-language PO/MO catalog snapshotted (same as `CopyContent::copy`).
     *
     * While locked, `translateInput()` resolves msgids from the snapshot and translates via the active
     * target-language text domain inside nested `switchToLanguage()` calls.
     *
     * @param string $sourceLocale
     * @param callable $callback
     */
    public function withSourceTranslationCatalog($sourceLocale, $callback)
    {
        $this->switchToLanguage($sourceLocale, function () use($callback) {
            $this->snapshotCurrentTranslations();
            $this->lockCurrentTranslations(\true);
            $this->teardownTemporaryTextDomain();
            try {
                \call_user_func($callback);
            } finally {
                $this->lockCurrentTranslations(\false);
                $this->unsetCurrentTranslations();
            }
        });
    }
    /**
     * Listen to meta (term, post, ...) addition and copy.
     *
     * @param string $type E. g. 'post'
     * @param int $from
     * @param int $to
     * @param string[] $meta Meta keys to copy
     */
    protected function duplicateMeta($type, $from, $to, $meta)
    {
        $locale = $this->getCurrentLanguageFallback();
        // Temporarily save the current translations so it can be used "later" when we call the action
        $currentTranslationEntries = $this->currentTranslationEntries;
        \add_action('added_' . $type . '_meta', function ($mid, $object_id, $meta_key, $_meta_value) use($from, $to, $meta, $type, $locale, $currentTranslationEntries) {
            if ($object_id === $from && \in_array($meta_key, $meta, \true)) {
                // Restore our previous locale because we are outside our sync mechanism (caused by `add_action`)
                ($previousCurrentTranslationEntries =& $this->currentTranslationEntries) ?? null;
                $this->currentTranslationEntries =& $currentTranslationEntries;
                $this->switchToLanguage($locale, function () use($type, $from, $to, $meta_key, $_meta_value, $locale) {
                    \call_user_func('add_' . $type . '_meta', $to, $meta_key, $this->filterMetaValue($type, $from, $to, $meta_key, $_meta_value, $locale));
                });
                $this->currentTranslationEntries = $previousCurrentTranslationEntries;
            }
        }, 10, 4);
    }
    /**
     * Term ids assigned to a post in a specific language context (avoids WPML read fallbacks).
     *
     * @param int $postId
     * @param string $taxonomy
     * @param string|null $locale When empty, uses the post's language
     * @return int[]
     */
    public function getObjectTermIdsForPostLanguage($postId, $taxonomy, $locale = null)
    {
        if ($locale === null || $locale === '') {
            $locale = $this->getPostLanguage($postId);
        }
        if ($locale === '') {
            return $this->fetchObjectTermIds($postId, $taxonomy);
        }
        // Read assignments under the post's language. WPML otherwise returns EN term ids for DE posts
        // (wrong REST field + admin list filter); not an OOM issue but breaks Phase C assignment checks.
        $ids = [];
        $this->switchToLanguage($locale, function () use($postId, $taxonomy, &$ids) {
            $ids = $this->fetchObjectTermIds($postId, $taxonomy);
        });
        return $ids;
    }
    /**
     * Read taxonomy term ids currently assigned to a post.
     *
     * @param int $postId
     * @param string $taxonomy
     * @return int[]
     */
    private function fetchObjectTermIds($postId, $taxonomy)
    {
        $terms = \wp_get_object_terms($postId, $taxonomy, ['fields' => 'ids', 'limit' => 0]);
        return \is_wp_error($terms) ? [] : \array_values(\array_map('intval', (array) $terms));
    }
    /**
     * Read assigned term ids bypassing WPML `get_object_terms` filters.
     *
     * @param int $postId
     * @param string $taxonomy
     * @return int[]
     */
    protected function fetchObjectTermIdsWithoutFilters($postId, $taxonomy)
    {
        return Utils::withoutFilters('get_object_terms', function () use($postId, $taxonomy) {
            return $this->fetchObjectTermIds($postId, $taxonomy);
        });
    }
    /**
     * Taxonomy term ids assigned to the source post before remapping onto a translation.
     *
     * Default: unfiltered read on `$from` only. {@see WPML::resolveSourceTermsByTaxonomyForPostCopy()}
     * adds WPML-specific fallbacks (snapshot at `icl_before_make_duplicate`, then trid siblings)
     * when the live read is empty after WPML duplicate housekeeping.
     *
     * @param int $from Source post id
     * @param string[] $taxonomies
     * @return array<string, int[]>
     */
    protected function resolveSourceTermsByTaxonomyForPostCopy($from, array $taxonomies)
    {
        $sourceTermsByTaxonomy = [];
        foreach ($taxonomies as $taxonomy) {
            $termIds = $this->fetchObjectTermIdsWithoutFilters($from, $taxonomy);
            if ($termIds !== []) {
                $sourceTermsByTaxonomy[$taxonomy] = $termIds;
            }
        }
        return $sourceTermsByTaxonomy;
    }
    /**
     * Copy already existing taxonomies as it can be inserted with `tax_input` directly.
     * Additionally listen to term additions and copy.
     *
     * @param int $from
     * @param int $to
     * @param string[] $taxonomies Taxonomy keys to copy
     * @param string $locale The destination locale
     */
    public function copyPostTaxonomies($from, $to, $taxonomies, $locale)
    {
        // Remap master taxonomy ids to the target locale after WPML `make_duplicate` (and for later
        // `set_object_terms` on the master). Bounded by configured taxonomies × active languages.
        $sourceLocale = $this->getPostLanguage($from);
        if ($sourceLocale === '') {
            $sourceLocale = $this->getDefaultLanguage();
        }
        if (!isset($this->postTaxonomyCopyTargets[$from])) {
            $this->postTaxonomyCopyTargets[$from] = ['sourceLocale' => $sourceLocale, 'taxonomies' => $taxonomies, 'to' => []];
        }
        $this->postTaxonomyCopyTargets[$from]['to'][$locale] = $to;
        $this->postTaxonomyCopyTargets[$from]['taxonomies'] = \array_values(\array_unique(\array_merge($this->postTaxonomyCopyTargets[$from]['taxonomies'], $taxonomies)));
        // One listener per request — do not `add_action` inside this method on every duplicate/locale pair.
        if (!self::$postTaxonomySetObjectTermsHookRegistered) {
            self::$postTaxonomySetObjectTermsHookRegistered = \true;
            \add_action('set_object_terms', [$this, 'onSetObjectTermsCopyPostTaxonomies'], 10, 5);
        }
        $sourceTermsByTaxonomy = $this->resolveSourceTermsByTaxonomyForPostCopy($from, $taxonomies);
        if ($sourceTermsByTaxonomy === []) {
            return;
        }
        $this->switchToLanguage($locale, function () use($to, $sourceTermsByTaxonomy, $locale) {
            foreach ($sourceTermsByTaxonomy as $taxonomy => $sourceTermIds) {
                $this->assignTranslatedPostTerms($to, $sourceTermIds, $taxonomy, $locale);
            }
        });
    }
    /**
     * Map source term ids to the target locale and assign them on a translation post.
     *
     * WordPress stores only numeric term ids in `wp_term_relationships` — no language. A DE
     * translation post can therefore reference an EN group term (e.g. after reset when only EN
     * `rcb-cookie-group` rows exist, then WPML `make_duplicate` copies those relationships and
     * `getCurrentTermId()` returns the same id when no linked DE term exists). That is valid data
     * for remap: when resolved id equals source id, check `getTermLanguage()` — assign if the term
     * already belongs to `$locale`, otherwise `copyTermToOtherLanguage()`. Do not call
     * `wp_set_object_terms(…, [])` when the source had terms but nothing could be mapped (would
     * wipe groups on the new translation, notably DE → EN duplicate of a DE cookie still wired to EN Essential).
     *
     * @param int $postId
     * @param int[] $sourceTermIds
     * @param string $taxonomy
     * @param string $locale
     */
    private function assignTranslatedPostTerms($postId, array $sourceTermIds, $taxonomy, $locale)
    {
        if ($this->postTaxonomyCopyingInProgress) {
            return;
        }
        $copyIds = [];
        foreach ($sourceTermIds as $sourceTermId) {
            $sourceTermId = (int) $sourceTermId;
            $resolvedTermId = (int) $this->getCurrentTermId($sourceTermId, $taxonomy, $locale);
            if ($resolvedTermId <= 0) {
                continue;
            }
            if ($resolvedTermId !== $sourceTermId) {
                $copyIds[] = $resolvedTermId;
                continue;
            }
            $term = \get_term($sourceTermId, $taxonomy);
            if (!$term instanceof WP_Term) {
                continue;
            }
            $termLocale = $this->getTermLanguage((int) $term->term_taxonomy_id, $taxonomy);
            if ($termLocale === $locale) {
                $copyIds[] = $resolvedTermId;
                continue;
            }
            $sourceLocale = $termLocale !== '' ? $termLocale : $this->getDefaultLanguage();
            $createdTermId = (int) $this->copyTermToOtherLanguage($locale, $sourceLocale, $sourceTermId, $taxonomy, []);
            if ($createdTermId > 0 && $createdTermId !== $sourceTermId) {
                $copyIds[] = $createdTermId;
            }
        }
        if ($copyIds === [] && $sourceTermIds !== []) {
            return;
        }
        $this->postTaxonomyCopyingInProgress = \true;
        try {
            Utils::withoutFilters('set_object_terms', function () use($postId, $copyIds, $taxonomy) {
                \wp_set_object_terms($postId, $copyIds, $taxonomy);
            });
        } finally {
            $this->postTaxonomyCopyingInProgress = \false;
        }
    }
    /**
     * Copy taxonomy assignments from a master post to its translations when terms are set on the master.
     *
     * @param int $object_id
     * @param int[]|string[] $terms
     * @param int[] $tt_ids
     * @param string $taxonomy
     * @param boolean $append
     */
    public function onSetObjectTermsCopyPostTaxonomies($object_id, $terms, $tt_ids, $taxonomy, $append)
    {
        if ($this->postTaxonomyCopyingInProgress) {
            return;
        }
        $config = $this->postTaxonomyCopyTargets[$object_id] ?? null;
        if ($config === null || !\in_array($taxonomy, $config['taxonomies'], \true)) {
            return;
        }
        // Master post was updated: push the same taxonomy set to every registered translation.
        $sourceTermIds = [];
        $this->switchToLanguage($config['sourceLocale'], function () use($terms, &$sourceTermIds) {
            foreach ((array) $terms as $term) {
                if (\is_numeric($term)) {
                    $sourceTermIds[] = (int) $term;
                    continue;
                }
                if ($term instanceof WP_Term) {
                    $sourceTermIds[] = $term->term_id;
                    continue;
                }
                $resolved = \get_term($term);
                if ($resolved instanceof WP_Term) {
                    $sourceTermIds[] = $resolved->term_id;
                }
            }
        });
        if ($sourceTermIds === []) {
            return;
        }
        foreach ($config['to'] as $locale => $toPostId) {
            if ($toPostId === $object_id) {
                continue;
            }
            $this->switchToLanguage($locale, function () use($toPostId, $sourceTermIds, $taxonomy, $locale) {
                $this->assignTranslatedPostTerms($toPostId, $sourceTermIds, $taxonomy, $locale);
            });
        }
    }
    /**
     * Apply a WordPress filter so a meta value can be modified for copy process
     * to other languages.
     *
     * @param string $type E. g. 'post'
     * @param int $from Object id of source language item
     * @param int $to Object id of destination language item
     * @param string $meta_key
     * @param mixed $meta_value
     * @param string $locale Destination locale
     */
    public function filterMetaValue($type, $from, $to, $meta_key, $meta_value, $locale)
    {
        // See https://developer.wordpress.org/reference/functions/update_post_meta/#workaround
        if (Utils::isJson($meta_value)) {
            $meta_value = \wp_slash($meta_value);
        }
        /**
         * Allows to modify a meta value when it gets copied to another language.
         *
         * @hook DevOwl/Multilingual/Copy/Meta/$type/$meta_key
         * @param {mixed} $meta_value
         * @param {int} $from Object id of source language item
         * @param {int} $to Object id of destination language item
         * @param string $locale Destination locale
         * @param string $meta_key
         * @return {mixed}
         */
        return \apply_filters('DevOwl/Multilingual/Copy/Meta/' . $type . '/' . $meta_key, $meta_value, $from, $to, $locale, $meta_key);
    }
}
