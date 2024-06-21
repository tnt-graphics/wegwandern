<?php

namespace DevOwl\RealCookieBanner\lite\settings;

use DevOwl\RealCookieBanner\settings\Blocker as SettingsBlocker;
use WP_Post;
// @codeCoverageIgnoreStart
\defined('ABSPATH') or die('No script kiddies please!');
// Avoid direct file request
// @codeCoverageIgnoreEnd
/** @internal */
trait Blocker
{
    /**
     * Documented in IOverrideBlocker.
     *
     * @param WP_Post $post
     * @param array $meta
     */
    public function overrideGetOrderedCastMeta($post, &$meta)
    {
        $meta[SettingsBlocker::META_NAME_VISUAL_DOWNLOAD_THUMBNAIL] = \boolval($meta[SettingsBlocker::META_NAME_VISUAL_DOWNLOAD_THUMBNAIL]);
        $meta[SettingsBlocker::META_NAME_VISUAL_BLUR] = \intval($meta[SettingsBlocker::META_NAME_VISUAL_BLUR]);
        $meta[SettingsBlocker::META_NAME_IS_VISUAL_DARK_MODE] = \boolval($meta[SettingsBlocker::META_NAME_IS_VISUAL_DARK_MODE]);
        // Calculate thumbnail URL
        $thumbnailUrl = null;
        $thumbnailWidth = 0;
        $thumbnailHeight = 0;
        $hide = [];
        $titleType = 'top';
        $attachmentId = $meta[SettingsBlocker::META_NAME_VISUAL_MEDIA_THUMBNAIL];
        $contentType = $meta[SettingsBlocker::META_NAME_VISUAL_CONTENT_TYPE];
        $isDarkMode = $meta[SettingsBlocker::META_NAME_IS_VISUAL_DARK_MODE];
        if ($attachmentId > 0) {
            $image = \wp_get_attachment_image_src($attachmentId, 'large');
            if ($image) {
                list($src, $width, $height) = $image;
                $thumbnailUrl = $src;
                $thumbnailWidth = $width;
                $thumbnailHeight = $height;
            }
        }
        // Calculate on predefined template thumbnail
        if (empty($thumbnailUrl) && !empty($contentType)) {
            $templateThumbnailFile = \sprintf('public/images/visual-content-blocker/%s-%s.svg', $contentType, $isDarkMode ? 'dark' : 'light');
            $templateThumbnailPath = RCB_PATH . '/' . $templateThumbnailFile;
            if (\is_file($templateThumbnailPath) && \preg_match("#viewbox=[\"']\\d* \\d* (\\d*) (\\d*)#i", \file_get_contents($templateThumbnailPath), $dimensions)) {
                $thumbnailUrl = \plugins_url($templateThumbnailFile, RCB_FILE);
                $thumbnailWidth = \intval($dimensions[1]);
                $thumbnailHeight = \intval($dimensions[2]);
                // For our predefined skeleton images we should avoid top titles and overlays
                $hide[] = 'overlay';
                $titleType = 'center';
            }
        }
        // A map should never show a top title and an overlay as it is mostly a more concrete image (our skeleton or uploaded through media library)
        if (\in_array($contentType, ['map'], \true)) {
            $hide[] = 'overlay';
            $titleType = 'center';
        }
        if (!empty($thumbnailUrl) && $thumbnailWidth > 0 && $thumbnailHeight > 0) {
            $meta['visualThumbnail'] = ['url' => $thumbnailUrl, 'width' => $thumbnailWidth, 'height' => $thumbnailHeight, 'hide' => \array_unique($hide), 'titleType' => $titleType];
        }
    }
}
