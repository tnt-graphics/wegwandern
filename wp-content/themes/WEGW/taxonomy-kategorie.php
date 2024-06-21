<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Wegwandern
 */

get_header();
$cate        = '';
$current_cat = get_queried_object();
if ( isset( $current_cat->term_id ) ) {
	$cat_id           = $current_cat ? $current_cat->term_id : '';
	$current_cat_name = $current_cat->name;
	$current_taxonomy = $current_cat->taxonomy;
	$query_args       = array(
		'post_type'      => 'b2b-werbung',
		'posts_per_page' => 11,
		'tax_query'      => array(
			array(
				'taxonomy' => 'kategorie',
				'field'    => 'term_id',
				'terms'    => $cat_id,
				'operator' => 'IN',
			),
		),
	);
	$query            = new WP_Query( $query_args );
} else {
	$cat_id = '';
}
?>
<main id="primary" class="site-main" role="main">
	<?php if ( $query->have_posts() ) : ?>
		<div class="container">
			<?php echo get_breadcrumb(); ?>
			<h1 class="page-title"><?php echo __( 'Angebote', 'wegwandern' ); ?></h1>
			<?php
			$categories_args = array(
				'taxonomy'   => 'kategorie',
				'hide_empty' => true,
			);

			$categories = get_categories( $categories_args );
			if ( ! empty( $categories ) ) {
				$category_link = home_url( 'angebote' );
				$cate         .= '<div class="blog_top_menu"><ul><li><a href="' . $category_link . '">' . __( 'Alle Themen', 'wegwandern' ) . '</a></li>';
				foreach ( $categories as $category ) {
					$active_class = '';
					if ( $category->term_id === $cat_id ) {
						$active_class = 'active';
					}
					$category_link = get_category_link( $category->term_id );
					$cate         .= '<li class="' . $active_class . '"><a href="' . $category_link . '">' . $category->name . '</a></li>';
				}
				$cate .= '</ul></div>';
			}

			echo $cate;
			$count      = 1;
			$trim       = 30;
			$thumb_size = 'teaser-onecol';
			?>

			<input type="hidden" class="page_type" value="<?php echo $cat_id; ?>">

			<div class="blog_list angebote_list">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();

					if ( $count > 2 ) {
						$trim       = 20;
						$thumb_size = 'teaser-twocol';
					}
					get_template_part( 'template-parts/content', 'b2b-werbung' );
					if ( $count == 4 ) {
						wegwandern_ad_section_display( 'center-between-contents', false, false, true );
					}

					if ( $count == 5 ) {
						wegwandern_ad_section_display( 'center-between-contents', true, false, false );
						wegwandern_ad_section_display( 'center-between-contents', false, true, false );
					}

					$count++;
				endwhile;
				?>
			</div>

			<div class="LoadMore" id="angebote-loadmore">
				<input type="hidden">
				<span class="LoadMoreIcon"></span>
				<span class="LoadMoreText"><?php echo __( 'Weitere Angebote', 'wegwandern' ); ?></span>
			</div>
			<div id="loader-icon" class="hide"></div>
			<div class="newsletter">
				<?php
				$wegwandernch_newsletter_form_id = FrmForm::get_id_by_key( 'wegwandernch-newsletter' );
				echo do_shortcode( "[formidable id=$wegwandernch_newsletter_form_id]" );
				?>
			</div>

	</div>
	<?php endif; ?>
</main>
<?php
get_footer(); ?>
