<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
use Joomunited\WPMediaFolder\WpmfHelper;

wp_enqueue_script('wpmf-gallery');

$class_default = array();
$class_default[] = 'gallery gallery_life wpmf_gallery_default gallery_default ';
$class_default[] = 'gallery-columns-' . $columns;
$class_default[] = 'gallery-size-' . $size_class;
$class_default[] = 'gallery-link-' . $link;
$class_default[] = 'wpmf-has-border-radius-' . $img_border_radius;
$class_default[] = 'wpmf-gutterwidth-' . $gutterwidth;
if ($aspect_ratio !== 'default') {
    $class_default[] = 'ratio_' . $aspect_ratio;
} else {
    $class_default[] = 'no_ratio';
}
$style = '';
if ($img_shadow !== '') {
    $style .= '#' . $selector . ' .wpmf-gallery-item img:hover {box-shadow: ' . $img_shadow . ' !important; transition: all 200ms ease;}';
}

if ($border_style !== 'none') {
    $style .= '#' . $selector . ' .wpmf-gallery-item img {border: ' . $border_color . ' '. $border_width .'px '. $border_style .'}';
}
wp_add_inline_style('wpmf-gallery-style', $style);
$output = '';
if (!empty($is_divi)) {
    $output .= '<style>' . $style . '</style>';
}
$galleryStyle = '';
if ($align === 'alignleft' || $align === 'alignright' || $align === 'aligncenter') {
    $galleryStyle = 'style="width: 100%; max-width: 620px!important;"';
} elseif ($align === 'none') {
    $align = '';
}
$output .= '<div class="wpmf-gallerys wpmf-gallerys-life '. $align . '" '. $galleryStyle .'>';
$output .= '<div id="' . $selector . '" class="' . implode(' ', $class_default) . '">';

$pos = 0;
foreach ($gallery_items as $item_id => $attachment) {
    if (strpos($attachment->post_excerpt, '<script>') !== false) {
        $post_excerpt = esc_html($attachment->post_excerpt);
    } else {
        $post_excerpt = $attachment->post_excerpt;
    }

    $link_target = get_post_meta($attachment->ID, '_gallery_link_target', true);
    $link_target = ($link_target !== '') ? $link_target : '_self';
    $downloads = $this->wpmfGalleryGetDownloadLink($attachment->ID);
    switch ($link) {
        case 'file':
            $image_output = $this->getAttachmentLink($item_id, $size, false, $targetsize, false, $link_target, $pos);
            break;
        case 'post':
            $image_output = $this->getAttachmentLink($item_id, $size, true, $targetsize, false, $link_target, $pos);
            break;
        case 'none':
            $preview_url = function_exists('wpmfResolveAttachmentUrl')
                ? wpmfResolveAttachmentUrl($item_id, 'preview', $size)
                : wp_get_attachment_image_url($item_id, $size);
            if ($preview_url === '') {
                $preview_url = wp_get_attachment_url($item_id);
            }
            $alt = trim(strip_tags(get_post_meta($item_id, '_wp_attachment_image_alt', true)));
            if ($alt === '') {
                $alt = $attachment->post_title;
            }
            $image_output = '<img src="' . esc_url($preview_url) . '" data-type="wpmfgalleryimg" data-attachment-id="' . (int) $item_id . '" data-wpmf-size="' . esc_attr($size) . '" alt="' . esc_attr($alt) . '">';
            break;
        case 'custom':
            $image_output = $this->getAttachmentLink($item_id, $size, false, $targetsize, true, $link_target, $pos);
            break;
        default:
            $image_output = $this->getAttachmentLink($item_id, $size, false, $targetsize, false, $link_target, $pos);
    }

    if ($enable_download) {
        $image_output .= '<a href="'.esc_url($downloads['download_link']).'" '. (($downloads['type'] === 'local') ? 'download' : '') .' class="wpmf_gallery_download_icon"><span class="material-icons-outlined"> file_download </span></a>';
    }

    $output .= '<figure class="wpmf-gallery-item" data-index="'. esc_attr($pos) .'">';
    $output .= '<div class="wpmf-gallery-icon">';
    $output .= WpmfHelper::wpmfRenderVideoIcon($attachment->ID);
    $output .= '<div class="square_thumbnail">';
    $output .= '<div class="img_centered">';
    $output .= $image_output;
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</div>';
    if (trim($post_excerpt) !== '') {
        $output .= '<figcaption class="wp-caption-text gallery-caption">';
        $output .= wptexturize($post_excerpt);
        $output .= '</figcaption>';
    }
    $output .= '</figure>';
    $pos++;
}
$output .= '</div></div>';
