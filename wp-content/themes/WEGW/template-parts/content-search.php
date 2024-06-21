<?php
/**
 * Template part for displaying results in search pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Wegwandern
 */



 $trim      = 65;
 $post_id   = ! empty( $args['id'] ) ? $args['id'] : get_the_ID();
 $post_type = get_post_type( $post_id );
if ( $post_type == 'community_beitrag' ) {
	$post_type = __( 'Beitrag Community', 'wegwandern' );
}
 $post_thumb      = get_the_post_thumbnail_url( $post_id, 'teaser-twocol' );
 $post_link       = get_permalink( $post_id );
 $title           = get_the_title( $post_id );
 $post_thumb      = '';
 $excerpt_content = '';
if ( has_post_thumbnail( $post_id ) ) {
	$post_thumb = get_the_post_thumbnail_url( $post_id, 'hike-thumbnail' );
}

if ( has_excerpt( $post_id ) ) {
	$excerpt_content = get_the_excerpt( $post_id );
} else {
	$excerpt_content = get_the_content( $post_id );
}

?>

<div class="searchResult-wander">

	<h6><?php echo ucfirst( $post_type ); ?></h6>

	<h2><a href="<?php echo $post_link; ?>"><?php echo $title; ?></a></h2>

	<div class="img-content-wrapper">
		<?php if ( ! empty( $post_thumb ) ) : ?>
			<div class="searchresult-wander-img">
				<a href="<?php echo $post_link; ?>"><img class="searchresult-img" src="<?php echo $post_thumb; ?>"></a>
			</div>
		<?php endif; ?>
		<div class="searchResult-desc">
			<?php echo wp_trim_words( $excerpt_content, $trim, ' ...' ); ?>
		</div>
	</div>
</div>

<?php

if ( $args['count'] == 5 ) {
	?>
<div class="searchResultAdContainer">

	<?php wegwandern_ad_section_display( 'center-between-contents', false, true, true ); ?>

</div>
<?php } ?>
