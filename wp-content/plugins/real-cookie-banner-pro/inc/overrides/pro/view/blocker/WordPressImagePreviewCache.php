<?php

namespace DevOwl\RealCookieBanner\lite\view\blocker;

use DevOwl\RealCookieBanner\Vendor\DevOwl\HeadlessContentBlocker\plugins\imagePreview\FsImagePreviewCache;
use DevOwl\RealCookieBanner\Vendor\DevOwl\HeadlessContentBlocker\plugins\imagePreview\Thumbnail;
use DevOwl\RealCookieBanner\base\UtilsProvider;
use DevOwl\RealCookieBanner\view\blocker\Plugin;
use Exception;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Create a filesystem cache for your image preview / thumbnails.
 * @internal
 */
class WordPressImagePreviewCache extends FsImagePreviewCache
{
    use UtilsProvider;
    /**
     * See `ImagePreviewCache`.
     *
     * @param Thumbnail[] $thumbnails Key is the embed URL
     */
    public function allowance($thumbnails)
    {
        $result = [];
        foreach ($thumbnails as $embedUrl => $thumbnail) {
            $thumbnail->setAllowance(Thumbnail::ALLOWANCE_PUBLIC);
        }
        return $result;
    }
    /**
     * See `ImagePreviewCache`.
     *
     * Additionally, persist all found thumbnails to the database so we can e.g. reuse for List of consents.
     *
     * @param Thumbnail[] $thumbnails Key is the embed URL
     */
    public function set($thumbnails)
    {
        parent::set($thumbnails);
        global $wpdb;
        $persistValues = [];
        foreach ($thumbnails as $thumbnail) {
            if (!empty($thumbnail->getError())) {
                continue;
            }
            // phpcs:disable WordPress.DB.PreparedSQL
            $persistValues[] = $wpdb->prepare('%s, %s, %s, %s, %s, %d, %d', $thumbnail->getId(), $thumbnail->getMd5File(), $thumbnail->getEmbedUrl(), \basename($thumbnail->getCacheUrl()), $thumbnail->getTitle() ?? 'NULL', $thumbnail->getWidth(), $thumbnail->getHeight());
            // phpcs:enable WordPress.DB.PreparedSQL
        }
        if (\count($persistValues) === 0) {
            return;
        }
        // Chunk to boost performance
        $chunks = \array_chunk($persistValues, 150);
        $table_name = $this->getTableName(Plugin::TABLE_NAME_BLOCKER_THUMBNAILS);
        foreach ($chunks as $sqlInsert) {
            $sql = \str_ireplace("'NULL'", 'NULL', "INSERT IGNORE INTO {$table_name} (embed_id, file_md5, embed_url, cache_filename, title, width, height) VALUES (" . \implode('),(', $sqlInsert) . ')');
            // phpcs:disable WordPress.DB.PreparedSQL
            $wpdb->query($sql);
            // phpcs:enable WordPress.DB.PreparedSQL
        }
    }
    /**
     * Create the instance.
     *
     * @param string $uploadsSubfolder
     */
    public static function create($uploadsSubfolder = 'embed-thumbnails')
    {
        $uploadDir = \wp_get_upload_dir();
        $imagePreviewFolder = \trailingslashit($uploadDir['basedir']) . $uploadsSubfolder;
        if (\wp_mkdir_p($imagePreviewFolder) && \wp_is_writable($imagePreviewFolder)) {
            try {
                return new \DevOwl\RealCookieBanner\lite\view\blocker\WordPressImagePreviewCache($imagePreviewFolder . '/', $uploadDir['baseurl'] . '/' . $uploadsSubfolder . '/', 60 * 60 * 24 * 14);
            } catch (Exception $e) {
                return \false;
            }
        } else {
            return \false;
        }
    }
}
