<?php
/**
 * Template Name: Single Community Beitrag Template
 *
 * @package wegwandern-summit-book
 */

get_header();
global $post;
$all_meta          = get_post_meta( $post->ID );
$teaser_image_id   = isset( $all_meta['teaser_image'][0] ) ? $all_meta['teaser_image'][0] : '';
$gallery_images    = array( $teaser_image_id );
$additional_images = isset( $all_meta['additional_images'][0] ) ? unserialize( $all_meta['additional_images'][0] ) : '';
if ( is_array( $additional_images ) ) {
	foreach ( $additional_images as $each_image ) {
		$gallery_images[] = $each_image;
	}
}
$count_slide = count( $gallery_images );
$count_html  = '<div id="counter" onclick="openLightGallery(this)">1/' . $count_slide . '</div>';
?>
<div class="container-fluid">
	<div class="demo-gallery">
		<?php echo $count_html; ?>
		<div id="lightgallery" class="single-wander-img list-unstyled row">
			<?php
			foreach ( $gallery_images as $key => $each_gallery_image ) {
				$attachment_url = wp_get_attachment_image_url( $each_gallery_image, 'large' );
				?>
				<div class="justified-gallery" data-src="<?php echo $attachment_url; ?>" data-sub-html="Caption<?php echo $key + 1; ?>">
					<a href="<?php echo $attachment_url; ?>">
						<img class="wander-img detail-wander-img" src="<?php echo $attachment_url; ?>" />
					</a>
				</div>
				<?php
			}
			?>
		</div>
	</div>
</div>
<div class="container">
	<div class="community-beitrag-content-section">
		<div class="community-beitrag-region">
			<?php
			$region            = '';
			$region_link       = '#';
			$region_meta_value = isset( $all_meta['region'][0] ) ? $all_meta['region'][0] : '';
			if ( '' !== $region_meta_value ) {
				$region_item = get_term( $region_meta_value );
				$region_link = get_term_link( $region_item );
				$region      = $region_item->name;
			}
			echo esc_attr( $region );
			?>
		</div>
		<h1 class="community-beitrag-headline"><?php echo esc_attr( isset( $all_meta['titel'][0] ) ? $all_meta['titel'][0] : '' ); ?></h1>
		<div class="detail-wrapper">
			<div class="single-hike-left">
				<p class="community-beitrag-leadtext"><?php echo esc_attr( isset( $all_meta['einleitung'][0] ) ? $all_meta['einleitung'][0] : '' ); ?></p>
				<div class="community-beitrag-infobox">
					<div class="community-beitrag-info-left-avatar">
						<?php
						$article_author_id = isset( $all_meta['user'][0] ) ? $all_meta['user'][0] : '';
						if ( '' !== $article_author_id ) {
							echo get_user_avatar( $article_author_id );
						}
						?>
					</div>
					<div class="community-beitrag-info-right">
						<div class="community-beitrag-author-name">
						<?php
						if ( '' !== $article_author_id ) {
							echo get_user_display_name( $article_author_id );
						}
						?>
						</div>
						<div class="community-beitrag-hiking-info">
							<?php
								$date_of_tour_text    = __( 'Datum der Tour', 'wegwandern-summit-book' );
								$date_of_tour         = isset( $all_meta['date_of_hiking'][0] ) ? gmdate( 'd.m.Y', strtotime( $all_meta['date_of_hiking'][0] ) ) : '';
								$start_of_tour_text   = __( 'Startpunkt', 'wegwandern-summit-book' );
								$end_of_tour_text     = __( 'Endpunkt', 'wegwandern-summit-book' );
								$start_of_tour        = isset( $all_meta['start_of_the_tour'][0] ) ? $all_meta['start_of_the_tour'][0] : '';
								$end_of_tour          = isset( $all_meta['end_of_the_tour'][0] ) ? $all_meta['end_of_the_tour'][0] : '';
								$star_rating_field_id = FrmField::get_id_by_key( 'k792c' );
								$kind_of_tour_id      = FrmField::get_id_by_key( '7bnya' );
								$star_rating          = do_shortcode( "[frm-field-value field_id=$star_rating_field_id user_id=$article_author_id show='star']" );
								echo esc_attr( $date_of_tour_text . ': ' . $date_of_tour ) . '<br>';
								echo esc_attr( $start_of_tour_text . ': ' . $start_of_tour ) . '<br>';
								echo esc_attr( $end_of_tour_text . ': ' . $end_of_tour ) . '<br>';
								echo show_star_rating( $star_rating );
							?>
							<div class="community-beitrag-tag-section">
								<span class="community-beitrag-tagged-item"><?php echo do_shortcode( "[frm-field-value field_id=$kind_of_tour_id user_id=$article_author_id]" ); ?></span>
								<span class="community-beitrag-tagged-item"><a href="<?php echo $region_link; ?>"><?php echo esc_attr( $region ); ?></a></span>
							</div>
						</div>
					</div>
				</div>
				<?php
				$highlights = isset( $all_meta['highlights'][0] ) ? unserialize( $all_meta['highlights'][0] ) : '';
				if ( ! empty( $highlights ) ) {
					$wegw_grey_background_section_icon = get_field( 'wegw_grey_background_section_icon' );
					?>
					<div class="community-beitrag-highlights hightlight-wrapper-container">
						<div class="hightlight-wrapper wegw-td-box-list-green">
						<h3><?php esc_attr_e( 'Highlights', 'wegwandern-summit-book' ); ?></h3>
						<ul>
							<?php
							$highlight_field_id = FrmField::get_id_by_key( '80pl2' );
							foreach ( $highlights as $each_highlight ) {
								echo '<li>' . esc_attr( FrmEntryMeta::get_entry_meta_by_field( $each_highlight, $highlight_field_id ) ) . '</li>';
							}
							?>
						</ul>
						<img src="<?php echo SUMMIT_BOOK_PLUGIN_DIR_URL . 'assets/images/highlights.svg'; ?>" />
						</div>
					</div>
					<?php
				}
				?>
				<div class="community-beitrag-maintext">
					<?php echo isset( $all_meta['wanderbeschrieb'][0] ) ? nl2br( esc_attr( $all_meta['wanderbeschrieb'][0] ) ) : ''; ?>
				</div>
			</div>
			<div class="single-hike-right">
				<?php wegwandern_ad_section_display( 'right', true, false, false ); ?>
			</div>
		</div>
	</div>
</div>
<?php
get_footer();
