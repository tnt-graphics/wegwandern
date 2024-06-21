<?php
$post_id       = get_the_ID();
$ad_image      = get_post_meta( $post_id, 'wegw_b2b_ad_image', true );
$ad_bold_text  = get_post_meta( $post_id, 'wegw_b2b_ad_bold_text', true );
$ad_bold_html  = ( '' != $ad_bold_text ) ? '<b>' . wp_trim_words( $ad_bold_text, 50, '...' ) . '</b>' : '';
$ad_link       = get_post_meta( $post_id, 'wegw_b2b_ad_link', true );
$category_html = '';
$kategories    = get_the_terms( $post_id, 'kategorie' );
if ( ! empty( $kategories ) ) {
	$kategories_array = array();
	foreach ( $kategories as $each_kat ) {
		$kat_link           = get_category_link( $each_kat->term_id );
		$kategories_array[] = "<a href='$kat_link'>$each_kat->name</a>";
	}
	$category_html .= implode( ' ', $kategories_array );
}
?>
<div class="blog-wander angebote-wander">
	<div class="blog-wander-img">
		<img class="blog-img" src="<?php echo $ad_image; ?>">
	</div>
	<h6><?php echo $category_html; ?></h6>
	<h2><?php the_title(); ?></h2>
	<div class="blog-desc">
		<?php
		echo wp_trim_words( get_the_content(), 110, '...' ) . ' ' . $ad_bold_html;
		?>
	</div>
	<?php
	echo '<a href="' . $ad_link . '" target="_blank" onclick="b2b_ad_click_calculate(' . $post_id . ')"><span></span>zum Angebot</a>';
	?>
</div>
