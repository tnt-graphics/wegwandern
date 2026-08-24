<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WpmfLoadGif
 * This class handles frontend static previews for GIF images.
 */
class WpmfLoadGif
{
    /**
     * Attachment meta key used to track generated still images.
     *
     * @var string
     */
    const STILL_IMAGE_META_KEY = '_wpmf_gif_still_relative_path';

    /**
     * Placeholder type used when no preview can be resolved.
     *
     * @var string
     */
    const STILL_PLACEHOLDER = 'placeholder';

    /**
     * WpmfLoadGif constructor.
     */
    public function __construct()
    {
        if ($this->isStaticGifModeEnabled()) {
            add_filter('the_content', array($this, 'gifReplace'), 12);
            add_filter('wpmf_load_gif_content', array($this, 'gifReplace'));

            if (!is_admin()) {
                add_action('wp_enqueue_scripts', array($this, 'enqueue'));
            }
        }

        add_action('delete_attachment', array($this, 'deleteStillImage'));
    }

    /**
     * Cancel gif file load on front end
     *
     * @param string $content Current post content
     *
     * @return mixed
     */
    public function gifReplace($content)
    {
        if (!$this->isStaticGifModeEnabled() || trim($content) === '' || stripos($content, '<img') === false) {
            return $content;
        }

        $internal_errors = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $loaded = $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div id="wpmf-gif-root">' . $content . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($internal_errors);

        if (!$loaded) {
            return $content;
        }

        $root = $dom->getElementById('wpmf-gif-root');
        if (!$root) {
            return $content;
        }

        $xpath = new DOMXPath($dom);
        $images = $xpath->query('.//img', $root);
        if (!$images || $images->length === 0) {
            return $content;
        }

        $targets = array();
        foreach ($images as $image) {
            if ($image instanceof DOMElement && $this->shouldReplaceImage($image)) {
                $targets[] = $image;
            }
        }

        foreach ($targets as $image) {
            $this->replaceImageNode($dom, $image);
        }

        return $this->getInnerHtml($root);
    }

    /**
     * Determine whether the current image should be wrapped by the GIF player.
     *
     * @param DOMElement $image Image element.
     *
     * @return boolean
     */
    protected function shouldReplaceImage($image)
    {
        $src = $image->getAttribute('src');
        if ($src === '') {
            return false;
        }

        $class = $image->getAttribute('class');
        if ($this->hasClass($class, 'loading_gallery')
            || $this->hasClass($class, '_hidden')
            || $this->hasClass($class, '_showing')
            || preg_match('~/Loading_icon\.gif(?:$|[?#])~i', $src)
        ) {
            return false;
        }

        if ($this->hasAncestorWithClass($image, 'gif_wrap')) {
            return false;
        }

        $context = $this->resolveAttachmentContext($image);
        if (!$context['is_gif']) {
            return false;
        }

        $is_wpmf_image = $image->getAttribute('data-type') === 'wpmfgalleryimg';
        if ($is_wpmf_image) {
            return $this->hasAncestorWithClass($image, 'wpmf-gallery-item');
        }

        if ($this->hasAncestorWithClass($image, 'wpmf_gallery_wrap')
            || $this->hasAncestorWithClass($image, 'wpmf-gallerys')
            || $this->hasAncestorWithClass($image, 'wpmf-gallery-addon-wrap')
        ) {
            return $this->hasAncestorWithClass($image, 'wpmf-gallery-item');
        }

        return true;
    }

    /**
     * Replace a GIF image node with the player wrapper markup.
     *
     * @param DOMDocument $dom   Document instance.
     * @param DOMElement  $image Target image node.
     *
     * @return void
     */
    protected function replaceImageNode($dom, $image)
    {
        $context = $this->resolveAttachmentContext($image);
        if (!$context['is_gif']) {
            return;
        }

        $preview = $this->resolveStillPreview($context);
        if ($preview['type'] === self::STILL_PLACEHOLDER) {
            $this->replaceWithStaticPlaceholder($dom, $image);
            return;
        }

        if ($preview['url'] === '') {
            return;
        }

        $gif_source = $this->getAttachmentSourceUrl($context['attachment_id'], $context['src']);
        $alt = $image->getAttribute('alt');
        $width = $image->getAttribute('width');
        $class = trim($image->getAttribute('class'));
        $width_class = trim($width);
        $showing_class = trim('_showing frame no-lazy ' . $class);

        $wrapper = $dom->createElement('div');
        $wrapper->setAttribute('class', trim('gif_wrap ' . $width_class));
        if ($gif_source !== '') {
            $wrapper->setAttribute('data-wpmf-gif-src', $gif_source);
        }
        $wrapper->setAttribute('data-wpmf-still-src', $preview['url']);
        if ($context['attachment_id'] > 0) {
            $wrapper->setAttribute('data-wpmf-attachment-id', (string) $context['attachment_id']);
        }

        $link = $dom->createElement('a');
        $link->setAttribute('href', 'javascript:void(0);');
        $link->setAttribute('class', trim('gif_link_wrap ' . $width_class));
        $link->setAttribute('title', 'Click to play');
        $link->setAttribute('rel', 'nofollow');
        if ($gif_source !== '') {
            $link->setAttribute('data-wpmf-gif-src', $gif_source);
        }
        $wrapper->appendChild($link);

        $badge = $dom->createElement('span', 'GIF');
        $badge->setAttribute('class', trim('play_gif ' . $width_class));
        $wrapper->appendChild($badge);

        $showing_image = $dom->createElement('img');
        $showing_image->setAttribute('src', $preview['url']);
        $showing_image->setAttribute('class', $showing_class);
        $showing_image->setAttribute('alt', $alt);
        if ($gif_source !== '') {
            $showing_image->setAttribute('data-wpmf-gif-src', $gif_source);
            $showing_image->setAttribute('data-lazy-src', $gif_source);
        }
        $showing_image->setAttribute('data-wpmf-still-src', $preview['url']);
        $wrapper->appendChild($showing_image);

        $hidden_image = $dom->createElement('img');
        $hidden_image->setAttribute('src', $preview['url']);
        $hidden_image->setAttribute('class', '_hidden no-lazy');
        $hidden_image->setAttribute('alt', $alt);
        $hidden_image->setAttribute('style', 'display:none;');
        if ($gif_source !== '') {
            $hidden_image->setAttribute('data-wpmf-gif-src', $gif_source);
            $hidden_image->setAttribute('data-lazy-src', $gif_source);
        }
        $hidden_image->setAttribute('data-wpmf-still-src', $preview['url']);

        $parent = $image->parentNode;
        if (!$parent) {
            return;
        }

        $parent->replaceChild($wrapper, $image);
        if ($wrapper->nextSibling) {
            $parent->insertBefore($hidden_image, $wrapper->nextSibling);
        } else {
            $parent->appendChild($hidden_image);
        }
    }

    /**
     * Resolve the attachment context from the rendered image element.
     *
     * @param DOMElement $image Image element.
     *
     * @return array<string, mixed>
     */
    protected function resolveAttachmentContext($image)
    {
        $src = (string) $image->getAttribute('src');
        $attachment_id = $this->getAttachmentIdFromImageElement($image);
        $mime_type = '';
        $is_gif = $this->isGifSource($src);
        if ($attachment_id > 0) {
            $mime_type = (string) get_post_mime_type($attachment_id);
            if ($mime_type !== '') {
                $is_gif = strtolower($mime_type) === 'image/gif';
            }
        }

        return array(
            'attachment_id' => $attachment_id,
            'src' => $src,
            'mime_type' => $mime_type,
            'is_gif' => $is_gif,
            'requested_size' => $this->getRequestedPreviewSize($image),
        );
    }

    /**
     * Resolve the best static preview for the GIF.
     *
     * @param array<string, mixed> $context Attachment context.
     *
     * @return array<string, string>
     */
    protected function resolveStillPreview($context)
    {
        $attachment_id = (int) $context['attachment_id'];
        $requested_size = (string) $context['requested_size'];

        $metadata_preview_url = $this->getStillPreviewUrlFromMetadata($attachment_id, $requested_size);
        if ($this->isUsableStillPreviewUrl($metadata_preview_url)) {
            return array('type' => 'metadata', 'url' => $metadata_preview_url);
        }

        $generated_still = $this->buildGeneratedStillUrl($attachment_id, (string) $context['src']);
        if ($generated_still !== '') {
            return array('type' => 'generated', 'url' => $generated_still);
        }

        return array('type' => self::STILL_PLACEHOLDER, 'url' => $this->getStaticGifPlaceholderDataUri());
    }

    /**
     * Build the generated still-image URL used by the GIF player.
     *
     * @param integer $attachment_id Attachment ID.
     * @param string  $src           GIF image URL.
     *
     * @return string
     */
    protected function buildGeneratedStillUrl($attachment_id, $src)
    {
        if ($attachment_id > 0) {
            $mime_type = get_post_mime_type($attachment_id);
            if (!is_string($mime_type) || strtolower($mime_type) !== 'image/gif') {
                return '';
            }
        } elseif (!$this->isGifSource($src)) {
            return '';
        }

        $paths = $this->resolveStillImagePaths($attachment_id, $src);
        if (empty($paths['still_path']) || empty($paths['still_url'])) {
            return '';
        }

        $tracked_still_path = $this->getTrackedStillPath($attachment_id);
        if ($tracked_still_path !== ''
            && $tracked_still_path !== $paths['still_path']
            && file_exists($tracked_still_path)
            && $this->moveStillImage($tracked_still_path, $paths['still_path'])
        ) {
            $this->updateTrackedStillPath($attachment_id, $paths['still_path']);
        }

        if (file_exists($paths['still_path'])) {
            $this->updateTrackedStillPath($attachment_id, $paths['still_path']);

            return $paths['still_url'];
        }

        $source = $this->resolveGifSource($attachment_id, $src);
        if ((!empty($source['gif_path'])
                && file_exists($source['gif_path'])
                && $this->generateStillImage($source['gif_path'], $paths['still_path']))
            || (!empty($source['gif_contents'])
                && $this->generateStillImageFromContents($source['gif_contents'], $paths['still_path']))
        ) {
            $this->updateTrackedStillPath($attachment_id, $paths['still_path']);

            return $paths['still_url'];
        }

        $this->deleteTrackedStillPath($attachment_id);

        return '';
    }

    /**
     * Resolve a static preview URL from attachment metadata sizes when available.
     *
     * @param integer $attachment_id Attachment ID.
     * @param string  $requested     Requested size.
     *
     * @return string
     */
    protected function getStillPreviewUrlFromMetadata($attachment_id, $requested = 'thumbnail')
    {
        $attachment_id = (int) $attachment_id;
        if ($attachment_id <= 0) {
            return '';
        }

        $drive_type = get_post_meta($attachment_id, 'wpmf_drive_type', true);
        if ($drive_type === 'google_drive' || $drive_type === 'nextcloud' || $drive_type === 'owncloud') {
            return '';
        }

        $metadata = wp_get_attachment_metadata($attachment_id);
        if (empty($metadata['sizes']) || !is_array($metadata['sizes'])) {
            return '';
        }

        $candidate_sizes = $this->getPreferredMetadataPreviewSizes($metadata['sizes'], $requested);
        foreach ($candidate_sizes as $size_name) {
            if (!isset($metadata['sizes'][$size_name]) || !is_array($metadata['sizes'][$size_name])) {
                continue;
            }

            $preview_url = $this->resolveMetadataSizeUrl($attachment_id, $metadata, $size_name);
            if ($this->isUsableStillPreviewUrl($preview_url)) {
                return $preview_url;
            }
        }

        return '';
    }

    /**
     * Resolve a preview URL for one metadata size.
     *
     * @param integer              $attachment_id Attachment ID.
     * @param array<string, mixed> $metadata      Attachment metadata.
     * @param string               $size_name     Metadata size name.
     *
     * @return string
     */
    protected function resolveMetadataSizeUrl($attachment_id, $metadata, $size_name)
    {
        $size_data = $metadata['sizes'][$size_name];

        if (!empty($size_data['url']) && is_string($size_data['url'])) {
            return $this->normalizeMetadataPreviewUrl($size_data['url']);
        }

        if (!empty($size_data['file']) && is_string($size_data['file']) && $this->isAbsoluteUrl($size_data['file'])) {
            return $this->normalizeMetadataPreviewUrl($size_data['file']);
        }

        $drive_id = get_post_meta($attachment_id, 'wpmf_drive_id', true);
        if (empty($drive_id)
            && !empty($metadata['file'])
            && is_string($metadata['file'])
            && !empty($size_data['file'])
            && is_string($size_data['file'])
        ) {
            $upload_dir = wp_upload_dir();
            if (!empty($upload_dir['baseurl'])) {
                $base_dir = dirname($metadata['file']);
                $base_dir = ($base_dir === '.' || $base_dir === DIRECTORY_SEPARATOR) ? '' : trim($base_dir, '/\\');
                $relative = ltrim($size_data['file'], '/\\');
                $url = trailingslashit($upload_dir['baseurl']);
                if ($base_dir !== '') {
                    $url .= $base_dir . '/';
                }

                return $this->normalizeMetadataPreviewUrl($url . $relative);
            }
        }

        return '';
    }

    /**
     * Pick the preferred metadata sizes to use as the static preview.
     *
     * @param array<string, mixed> $sizes     Attachment metadata sizes.
     * @param string               $requested Requested size.
     *
     * @return array<int, string>
     */
    protected function getPreferredMetadataPreviewSizes($sizes, $requested)
    {
        if (empty($sizes) || !is_array($sizes)) {
            return array();
        }

        $ordered = array();
        $requested = (string) $requested;
        if ($requested !== '' && $requested !== 'full' && isset($sizes[$requested]) && is_array($sizes[$requested])) {
            $ordered[] = $requested;
        }

        foreach (array('thumbnail', 'medium', 'large') as $size_name) {
            if ($size_name !== $requested && isset($sizes[$size_name]) && is_array($sizes[$size_name])) {
                $ordered[] = $size_name;
            }
        }

        foreach ($sizes as $size_name => $size_data) {
            if (is_string($size_name)
                && $size_name !== 'full'
                && !in_array($size_name, $ordered, true)
                && is_array($size_data)
            ) {
                $ordered[] = $size_name;
            }
        }

        return $ordered;
    }

    /**
     * Normalize a preview URL read directly from attachment metadata.
     *
     * @param string $url Preview URL from metadata.
     *
     * @return string
     */
    protected function normalizeMetadataPreviewUrl($url)
    {
        if (!is_string($url) || $url === '') {
            return '';
        }

        return function_exists('wpmfNormalizeResolvedUrl')
            ? wpmfNormalizeResolvedUrl($url)
            : str_replace(array('&amp;', '&#038;'), '&', $url);
    }

    /**
     * Check whether a preview URL is usable as a static still image.
     *
     * @param string $url Candidate preview URL.
     *
     * @return boolean
     */
    protected function isUsableStillPreviewUrl($url)
    {
        if (!is_string($url) || $url === '') {
            return false;
        }

        if (strpos($url, 'data:image/svg+xml') === 0) {
            return false;
        }

        return true;
    }

    /**
     * Check whether the given value is an absolute URL.
     *
     * @param string $value Candidate URL value.
     *
     * @return boolean
     */
    protected function isAbsoluteUrl($value)
    {
        if (!is_string($value) || $value === '') {
            return false;
        }

        return (bool) preg_match('#^(https?:)?//#i', $value);
    }

    /**
     * Try to resolve the attachment ID directly from the rendered image element.
     *
     * @param DOMElement $image Image element.
     *
     * @return integer
     */
    protected function getAttachmentIdFromImageElement($image)
    {
        $candidate_attributes = array('data-id', 'data-attachment-id', 'data-attachment_id');
        foreach ($candidate_attributes as $attribute) {
            $value = $image->getAttribute($attribute);
            if ($value !== '' && ctype_digit((string) $value)) {
                return (int) $value;
            }
        }

        $class = $image->getAttribute('class');
        if ($class !== '' && preg_match('/(?:^|\s)wp-image-(\d+)(?:\s|$)/', $class, $matches)) {
            return (int) $matches[1];
        }

        $parent = $image->parentNode;
        while ($parent instanceof DOMElement) {
            foreach ($candidate_attributes as $attribute) {
                $value = $parent->getAttribute($attribute);
                if ($value !== '' && ctype_digit((string) $value)) {
                    return (int) $value;
                }
            }

            $class = $parent->getAttribute('class');
            if ($class !== '' && preg_match('/(?:^|\\s)wp-image-(\\d+)(?:\\s|$)/', $class, $matches)) {
                return (int) $matches[1];
            }

            $parent = $parent->parentNode;
        }

        return 0;
    }

    /**
     * Resolve the requested preview size from image classes and context.
     *
     * @param DOMElement $image Image element.
     *
     * @return string
     */
    protected function getRequestedPreviewSize($image)
    {
        $data_size = trim((string) $image->getAttribute('data-wpmf-size'));
        if ($data_size !== '') {
            return sanitize_key($data_size);
        }

        $class = trim((string) $image->getAttribute('class'));
        if ($class !== '' && preg_match('/(?:^|\s)size-([a-z0-9_-]+)(?:\s|$)/i', $class, $matches)) {
            return sanitize_key($matches[1]);
        }

        $parent = $image->parentNode;
        while ($parent instanceof DOMElement) {
            $data_size = trim((string) $parent->getAttribute('data-wpmf-size'));
            if ($data_size !== '') {
                return sanitize_key($data_size);
            }
            $class = trim((string) $parent->getAttribute('class'));
            if ($class !== '' && preg_match('/(?:^|\s)size-([a-z0-9_-]+)(?:\s|$)/i', $class, $matches)) {
                return sanitize_key($matches[1]);
            }
            $parent = $parent->parentNode;
        }

        return 'thumbnail';
    }

    /**
     * Resolve local filesystem paths for the GIF source and its still preview.
     *
     * @param integer $attachment_id Attachment ID.
     * @param string  $gif_url       Original GIF URL.
     *
     * @return array<string, string>
     */
    protected function resolveStillImagePaths($attachment_id, $gif_url)
    {
        $upload_dir = wp_upload_dir();
        if (empty($upload_dir['basedir']) || empty($upload_dir['baseurl'])) {
            return array();
        }

        $relative_path = $this->getAttachmentRelativePath($attachment_id);
        if ($relative_path === '') {
            $gif_path = $this->mapUploadUrlToPath($gif_url, $upload_dir);
            if ($gif_path !== '') {
                $relative_path = ltrim(str_replace(wp_normalize_path($upload_dir['basedir']), '', wp_normalize_path($gif_path)), '/');
            }
        }

        if ($relative_path === '' || !$this->isGifFilePath($relative_path)) {
            return array();
        }

        $still_relative_path = preg_replace('/\.gif$/i', '_still_tmp.jpeg', $relative_path);
        if (!is_string($still_relative_path) || $still_relative_path === '') {
            return array();
        }

        $gif_path = trailingslashit($upload_dir['basedir']) . str_replace('/', DIRECTORY_SEPARATOR, $relative_path);

        return array(
            'gif_path' => file_exists($gif_path) ? $gif_path : '',
            'still_path' => trailingslashit($upload_dir['basedir']) . str_replace('/', DIRECTORY_SEPARATOR, $still_relative_path),
            'still_url' => trailingslashit($upload_dir['baseurl']) . str_replace(DIRECTORY_SEPARATOR, '/', $still_relative_path),
        );
    }

    /**
     * Get the attachment-relative file path when available.
     *
     * @param integer $attachment_id Attachment ID.
     *
     * @return string
     */
    protected function getAttachmentRelativePath($attachment_id)
    {
        if (empty($attachment_id)) {
            return '';
        }

        $relative_path = get_post_meta($attachment_id, '_wp_attached_file', true);
        if (is_string($relative_path) && $relative_path !== '') {
            return ltrim($relative_path, '/');
        }

        $metadata = wp_get_attachment_metadata($attachment_id);
        if (!empty($metadata['file']) && is_string($metadata['file'])) {
            return ltrim($metadata['file'], '/');
        }

        return '';
    }

    /**
     * Map an uploads URL back to its local filesystem path when possible.
     *
     * @param string $url        Upload URL.
     * @param array  $upload_dir Result from wp_upload_dir().
     *
     * @return string
     */
    protected function mapUploadUrlToPath($url, $upload_dir)
    {
        $url_path = parse_url($url, PHP_URL_PATH);
        if (!is_string($url_path) || $url_path === '') {
            return '';
        }

        $base_url_path = parse_url($upload_dir['baseurl'], PHP_URL_PATH);
        $relative_path = '';
        if (is_string($base_url_path) && $base_url_path !== '' && strpos($url_path, $base_url_path) === 0) {
            $relative_path = ltrim(substr($url_path, strlen($base_url_path)), '/');
        } else {
            $uploads_marker = '/wp-content/uploads/';
            $uploads_pos = strpos($url_path, $uploads_marker);
            if ($uploads_pos === false) {
                return '';
            }

            $relative_path = ltrim(substr($url_path, $uploads_pos + strlen($uploads_marker)), '/');
        }

        if ($relative_path === '') {
            return '';
        }

        return trailingslashit($upload_dir['basedir']) . str_replace('/', DIRECTORY_SEPARATOR, $relative_path);
    }

    /**
     * Generate the still JPEG preview used by the GIF player.
     *
     * @param string $gif_path   Local GIF path.
     * @param string $still_path Target JPEG path.
     *
     * @return boolean
     */
    protected function generateStillImage($gif_path, $still_path)
    {
        $directory = dirname($still_path);
        if (!file_exists($directory)) {
            wp_mkdir_p($directory);
        }

        if ($this->generateStillImageWithImagick($gif_path, $still_path)) {
            return true;
        }

        if ($this->generateStillImageWithGd($gif_path, $still_path)) {
            return true;
        }

        return file_exists($still_path);
    }

    /**
     * Generate the still JPEG preview from GIF contents.
     *
     * @param string $gif_contents GIF file contents.
     * @param string $still_path   Target JPEG path.
     *
     * @return boolean
     */
    protected function generateStillImageFromContents($gif_contents, $still_path)
    {
        $directory = dirname($still_path);
        if (!file_exists($directory)) {
            wp_mkdir_p($directory);
        }

        if ($this->generateStillImageWithImagickBlob($gif_contents, $still_path)) {
            return true;
        }

        if ($this->generateStillImageWithGdBlob($gif_contents, $still_path)) {
            return true;
        }

        return file_exists($still_path);
    }

    /**
     * Generate the still image with Imagick when available.
     *
     * @param string $gif_path   Local GIF path.
     * @param string $still_path Target JPEG path.
     *
     * @return boolean
     */
    protected function generateStillImageWithImagick($gif_path, $still_path)
    {
        if (!class_exists('Imagick')) {
            return false;
        }

        try {
            $imagick = new Imagick();
            $imagick->readImage($gif_path . '[0]');
            if (method_exists($imagick, 'setImageBackgroundColor')) {
                $imagick->setImageBackgroundColor('white');
            }
            if (method_exists($imagick, 'setImageAlphaChannel') && defined('Imagick::ALPHACHANNEL_REMOVE')) {
                $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
            }
            if (method_exists($imagick, 'mergeImageLayers') && defined('Imagick::LAYERMETHOD_FLATTEN')) {
                $imagick = $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            }
            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompressionQuality(90);
            $result = $imagick->writeImage($still_path);
            $imagick->clear();
            $imagick->destroy();

            return $result && file_exists($still_path);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Generate the still image with GD as a fallback.
     *
     * @param string $gif_path   Local GIF path.
     * @param string $still_path Target JPEG path.
     *
     * @return boolean
     */
    protected function generateStillImageWithGd($gif_path, $still_path)
    {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
            return false;
        }

        if (!is_readable($gif_path)) {
            return false;
        }

        $gif_contents = file_get_contents($gif_path);
        if ($gif_contents === false) {
            return false;
        }

        $source = imagecreatefromstring($gif_contents);
        if (!$source) {
            return false;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $canvas = imagecreatetruecolor($width, $height);
        if (!$canvas) {
            imagedestroy($source);
            return false;
        }

        $background = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $background);
        imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);
        $result = imagejpeg($canvas, $still_path, 90);
        imagedestroy($canvas);
        imagedestroy($source);

        return $result && file_exists($still_path);
    }

    /**
     * Generate the still image with Imagick from GIF contents.
     *
     * @param string $gif_contents GIF file contents.
     * @param string $still_path   Target JPEG path.
     *
     * @return boolean
     */
    protected function generateStillImageWithImagickBlob($gif_contents, $still_path)
    {
        if (!class_exists('Imagick')) {
            return false;
        }

        try {
            $imagick = new Imagick();
            $imagick->readImageBlob($gif_contents);
            if (method_exists($imagick, 'setIteratorIndex')) {
                $imagick->setIteratorIndex(0);
            }
            if (method_exists($imagick, 'setImageBackgroundColor')) {
                $imagick->setImageBackgroundColor('white');
            }
            if (method_exists($imagick, 'setImageAlphaChannel') && defined('Imagick::ALPHACHANNEL_REMOVE')) {
                $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
            }
            if (method_exists($imagick, 'mergeImageLayers') && defined('Imagick::LAYERMETHOD_FLATTEN')) {
                $imagick = $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            }
            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompressionQuality(90);
            $result = $imagick->writeImage($still_path);
            $imagick->clear();
            $imagick->destroy();

            return $result && file_exists($still_path);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Generate the still image with GD from GIF contents.
     *
     * @param string $gif_contents GIF file contents.
     * @param string $still_path   Target JPEG path.
     *
     * @return boolean
     */
    protected function generateStillImageWithGdBlob($gif_contents, $still_path)
    {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) {
            return false;
        }

        $source = imagecreatefromstring($gif_contents);
        if (!$source) {
            return false;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $canvas = imagecreatetruecolor($width, $height);
        if (!$canvas) {
            imagedestroy($source);
            return false;
        }

        $background = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $background);
        imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);
        $result = imagejpeg($canvas, $still_path, 90);
        imagedestroy($canvas);
        imagedestroy($source);

        return $result && file_exists($still_path);
    }

    /**
     * Resolve the readable GIF source for a local or cloud attachment.
     *
     * @param integer $attachment_id Attachment ID.
     * @param string  $gif_url       Original GIF URL.
     *
     * @return array<string, string>
     */
    protected function resolveGifSource($attachment_id, $gif_url)
    {
        if (!empty($attachment_id)) {
            $mime_type = get_post_mime_type($attachment_id);
            if (is_string($mime_type) && $mime_type !== '' && strtolower($mime_type) !== 'image/gif') {
                return array();
            }

            $gif_path = get_attached_file($attachment_id);
            if (is_string($gif_path) && $gif_path !== '' && file_exists($gif_path) && $this->isGifFilePath($gif_path)) {
                return array('gif_path' => $gif_path);
            }

            $relative_path = $this->getAttachmentRelativePath($attachment_id);
            if ($relative_path !== '') {
                $upload_dir = wp_upload_dir();
                if (!empty($upload_dir['basedir'])) {
                    $candidate_path = trailingslashit($upload_dir['basedir']) . str_replace('/', DIRECTORY_SEPARATOR, $relative_path);
                    if (file_exists($candidate_path) && $this->isGifFilePath($candidate_path)) {
                        return array('gif_path' => $candidate_path);
                    }
                }
            }
        }

        $source_url = $this->getAttachmentSourceUrl($attachment_id, $gif_url);
        if ($source_url === '') {
            return array();
        }

        $gif_contents = $this->fetchRemoteFileContents($source_url);
        if ($gif_contents === '' || !$this->isGifContents($gif_contents)) {
            return array();
        }

        return array('gif_contents' => $gif_contents);
    }

    /**
     * Get a readable GIF source URL for an attachment.
     *
     * @param integer $attachment_id Attachment ID.
     * @param string  $fallback_url  Fallback GIF URL.
     *
     * @return string
     */
    protected function getAttachmentSourceUrl($attachment_id, $fallback_url)
    {
        $url = $fallback_url;
        if (!empty($attachment_id)) {
            if (function_exists('wpmfResolveAttachmentUrl')) {
                $resolved_url = wpmfResolveAttachmentUrl($attachment_id, 'gif_source');
                if (is_string($resolved_url) && $resolved_url !== '') {
                    return $this->normalizeMetadataPreviewUrl($resolved_url);
                }
            }

            $attachment_url = wp_get_attachment_url($attachment_id);
            if (is_string($attachment_url) && $attachment_url !== '') {
                $url = $attachment_url;
            }

            $cloud_type = get_post_meta((int) $attachment_id, 'wpmf_drive_type', true);
            $drive_id = get_post_meta((int) $attachment_id, 'wpmf_drive_id', true);
            $aws3_info = get_post_meta((int) $attachment_id, 'wpmf_awsS3_info', true);

            if ($cloud_type && $drive_id) {
                switch ($cloud_type) {
                    case 'dropbox':
                    case 'onedrive':
                    case 'onedrive_business':
                    case 'google_drive':
                        if (function_exists('wpmfGetDriveLink')) {
                            $url = wpmfGetDriveLink($attachment_id, $drive_id);
                        }
                        break;
                    case 'nextcloud':
                        $url = admin_url('admin-ajax.php') . '?action=wpmf_nextcloud_get_content&url=' . urlencode($url) . '/download';
                        break;
                    case 'owncloud':
                        $url = admin_url('admin-ajax.php') . '?action=wpmf_owncloud_get_content&url=' . urlencode($url) . '/download';
                        break;
                }
            } elseif (!empty($aws3_info)) {
                $url = admin_url('admin-ajax.php') . '?action=wpmf_offload_get_content&url=' . urlencode($url);
            }
        }

        return $this->normalizeMetadataPreviewUrl((string) $url);
    }

    /**
     * Fetch remote file contents for cloud/offloaded GIFs.
     *
     * @param string $url Readable source URL.
     *
     * @return string
     */
    protected function fetchRemoteFileContents($url)
    {
        if ($url === '') {
            return '';
        }

        $response = wp_remote_get($url, array(
            'timeout' => 20,
            'redirection' => 5,
        ));

        if (is_wp_error($response)) {
            return '';
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        if ($code < 200 || $code >= 300) {
            return '';
        }

        $body = wp_remote_retrieve_body($response);

        return is_string($body) ? $body : '';
    }

    /**
     * Check whether fetched remote contents look like a GIF file.
     *
     * @param string $contents Remote file contents.
     *
     * @return boolean
     */
    protected function isGifContents($contents)
    {
        if (!is_string($contents) || $contents === '') {
            return false;
        }

        return strpos($contents, 'GIF87a') === 0 || strpos($contents, 'GIF89a') === 0;
    }

    /**
     * Replace the original GIF image with a static placeholder.
     *
     * @param DOMDocument $dom   Document instance.
     * @param DOMElement  $image Target image node.
     *
     * @return void
     */
    protected function replaceWithStaticPlaceholder($dom, $image)
    {
        $placeholder = $dom->createElement('img');
        $placeholder->setAttribute('src', $this->getStaticGifPlaceholderDataUri());
        $placeholder->setAttribute('alt', $image->getAttribute('alt'));

        $class = trim($image->getAttribute('class'));
        if ($class !== '') {
            $placeholder->setAttribute('class', $class);
        }

        $width = $image->getAttribute('width');
        if ($width !== '') {
            $placeholder->setAttribute('width', $width);
        }

        $height = $image->getAttribute('height');
        if ($height !== '') {
            $placeholder->setAttribute('height', $height);
        }

        $placeholder->setAttribute('data-wpmf-gif-placeholder', '1');

        $parent = $image->parentNode;
        if ($parent) {
            $parent->replaceChild($placeholder, $image);
        }
    }

    /**
     * Get a lightweight static placeholder for GIFs.
     *
     * @return string
     */
    protected function getStaticGifPlaceholderDataUri()
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 240">'
            . '<rect width="320" height="240" fill="#f1f1f1"/>'
            . '<rect x="1" y="1" width="318" height="238" fill="none" stroke="#d0d0d0"/>'
            . '<text x="160" y="126" text-anchor="middle" font-family="Arial, sans-serif" font-size="26" fill="#6b6b6b">GIF</text>'
            . '</svg>';

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    }

    /**
     * Get the tracked still file path from attachment meta.
     *
     * @param integer $attachment_id Attachment ID.
     *
     * @return string
     */
    protected function getTrackedStillPath($attachment_id)
    {
        if (empty($attachment_id)) {
            return '';
        }

        $relative_path = get_post_meta($attachment_id, self::STILL_IMAGE_META_KEY, true);
        if (!is_string($relative_path) || $relative_path === '') {
            return '';
        }

        $upload_dir = wp_upload_dir();
        if (empty($upload_dir['basedir'])) {
            return '';
        }

        return trailingslashit($upload_dir['basedir']) . str_replace('/', DIRECTORY_SEPARATOR, ltrim($relative_path, '/'));
    }

    /**
     * Persist the tracked still file path in attachment meta.
     *
     * @param integer $attachment_id Attachment ID.
     * @param string  $still_path    Absolute still file path.
     *
     * @return void
     */
    protected function updateTrackedStillPath($attachment_id, $still_path)
    {
        if (empty($attachment_id) || $still_path === '') {
            return;
        }

        $upload_dir = wp_upload_dir();
        if (empty($upload_dir['basedir'])) {
            return;
        }

        $normalized_base = trailingslashit(wp_normalize_path($upload_dir['basedir']));
        $normalized_still = wp_normalize_path($still_path);
        if (strpos($normalized_still, $normalized_base) !== 0) {
            return;
        }

        $relative_path = ltrim(substr($normalized_still, strlen($normalized_base)), '/');
        update_post_meta($attachment_id, self::STILL_IMAGE_META_KEY, $relative_path);
    }

    /**
     * Remove the tracked still file path attachment meta.
     *
     * @param integer $attachment_id Attachment ID.
     *
     * @return void
     */
    protected function deleteTrackedStillPath($attachment_id)
    {
        if (!empty($attachment_id)) {
            delete_post_meta($attachment_id, self::STILL_IMAGE_META_KEY);
        }
    }

    /**
     * Move a still image to a new target path.
     *
     * @param string $source_path Source still path.
     * @param string $target_path Target still path.
     *
     * @return boolean
     */
    protected function moveStillImage($source_path, $target_path)
    {
        if ($source_path === '' || $target_path === '') {
            return false;
        }

        if ($source_path === $target_path) {
            return file_exists($target_path);
        }

        if (!file_exists($source_path)) {
            return false;
        }

        $directory = dirname($target_path);
        if (!file_exists($directory)) {
            wp_mkdir_p($directory);
        }

        if (file_exists($target_path)) {
            wp_delete_file($source_path);

            return true;
        }

        return rename($source_path, $target_path);
    }

    /**
     * Delete the generated still image when the source GIF attachment is removed.
     *
     * @param integer $attachment_id Attachment ID.
     *
     * @return void
     */
    public function deleteStillImage($attachment_id)
    {
        $gif_path = get_attached_file($attachment_id);
        if (!is_string($gif_path) || $gif_path === '' || !$this->isGifFilePath($gif_path)) {
            $tracked_still_path = $this->getTrackedStillPath($attachment_id);
            if ($tracked_still_path !== '' && file_exists($tracked_still_path)) {
                $tracked_still_path = apply_filters('wp_delete_file', $tracked_still_path);
                if (is_string($tracked_still_path) && $tracked_still_path !== '' && file_exists($tracked_still_path)) {
                    wp_delete_file($tracked_still_path);
                }
            }
            $this->deleteTrackedStillPath($attachment_id);

            return;
        }

        $still_paths = array_filter(array_unique(array(
            $this->getTrackedStillPath($attachment_id),
            preg_replace('/\.gif$/i', '_still_tmp.jpeg', $gif_path),
        )));

        foreach ($still_paths as $still_path) {
            if (!is_string($still_path) || !file_exists($still_path)) {
                continue;
            }

            $still_path = apply_filters('wp_delete_file', $still_path);
            if (is_string($still_path) && $still_path !== '' && file_exists($still_path)) {
                wp_delete_file($still_path);
            }
        }

        $this->deleteTrackedStillPath($attachment_id);
    }

    /**
     * Check whether the given local path is a GIF file path.
     *
     * @param string $path Local file path.
     *
     * @return boolean
     */
    protected function isGifFilePath($path)
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'gif';
    }

    /**
     * Check whether a class string contains a specific class.
     *
     * @param string $class_string Class attribute value.
     * @param string $target_class Target class name.
     *
     * @return boolean
     */
    protected function hasClass($class_string, $target_class)
    {
        $classes = preg_split('/\s+/', trim((string) $class_string));

        return in_array($target_class, array_filter($classes), true);
    }

    /**
     * Check whether an element has an ancestor with the given class.
     *
     * @param DOMElement $element      Current element.
     * @param string     $target_class Class name to look for.
     *
     * @return boolean
     */
    protected function hasAncestorWithClass($element, $target_class)
    {
        $parent = $element->parentNode;
        while ($parent instanceof DOMElement) {
            if ($this->hasClass($parent->getAttribute('class'), $target_class)) {
                return true;
            }
            $parent = $parent->parentNode;
        }

        return false;
    }

    /**
     * Check whether the given source points to a GIF image.
     *
     * @param string $src Image URL.
     *
     * @return boolean
     */
    protected function isGifSource($src)
    {
        $path = parse_url($src, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return false;
        }

        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'gif';
    }

    /**
     * Determine whether static GIF preview mode is enabled.
     *
     * @return boolean
     */
    protected function isStaticGifModeEnabled()
    {
        $load_gif = wpmfGetOption('load_gif');

        return isset($load_gif) && (int) $load_gif === 0;
    }

    /**
     * Render the inner HTML for the wrapper root node.
     *
     * @param DOMElement $root Root wrapper node.
     *
     * @return string
     */
    protected function getInnerHtml($root)
    {
        $html = '';
        foreach ($root->childNodes as $child) {
            $html .= $root->ownerDocument->saveHTML($child);
        }

        return $html;
    }

    /**
     * Load script
     *
     * @return void
     */
    public function enqueue()
    {
        if (!is_admin()) {
            wp_register_script(
                'wpmf_play_gifs',
                plugins_url('assets/js/gif/play_gif.js', dirname(__FILE__)),
                array('jquery'),
                WPMF_VERSION,
                true
            );
            wp_enqueue_script('wpmf_play_gifs');
            wp_register_script(
                'wpmf_spin',
                plugins_url('assets/js/gif/spin.js', dirname(__FILE__)),
                array('jquery'),
                '1.0',
                true
            );
            wp_enqueue_script('wpmf_spin');
            wp_register_script(
                'wpmf_spinjQuery',
                plugins_url('assets/js/gif/jquery.spin.js', dirname(__FILE__)),
                array('jquery'),
                '1.0',
                true
            );
            wp_enqueue_script('wpmf_spinjQuery');
        }
    }
}
