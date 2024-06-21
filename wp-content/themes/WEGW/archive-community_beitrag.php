<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Wegwandern
 */

get_header();

?>
<main id="primary" class="site-main" role="main">
	<?php if ( have_posts() ) : ?>
		<div class="container">
			<?php echo get_breadcrumb(); ?>
			<h1 class="page-title"><?php echo __( 'Wanderung Community', 'wegwandern' ); ?></h1>
			<?php
			$count      = 1;
			$trim       = 30;
			$thumb_size = 'teaser-onecol';
			?>
			<div class="blog_list article_list">
				<?php
				while ( have_posts() ) :
					the_post();

					if ( $count > 2 ) {
						$trim       = 20;
						$thumb_size = 'teaser-twocol';
					}
					$post_id          = get_the_ID();
					$article_image_id = get_post_meta( $post_id, 'teaser_image', true );
					$article_image    = wp_get_attachment_image_url( $article_image_id, 'large' );
					$article_text     = get_post_meta( $post_id, 'einleitung', true );
					$article_link     = get_permalink( $post_id );
					?>
					<div class="blog-wander article-wander">
						<div class="blog-wander-img">
							<a href="<?php echo $article_link; ?>"><img class="blog-img" src="<?php echo $article_image; ?>"></a>
						</div>
						<h2><a href="<?php echo $article_link; ?>"><?php the_title(); ?></a></h2>
						<div class="blog-desc">
							<?php
							echo wp_trim_words( $article_text, 110, '...' );
							?>
						</div>
					</div>
					<?php
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

			<div class="LoadMore" id="article-loadmore">
				<input type="hidden">
				<span class="LoadMoreIcon"></span>
				<span class="LoadMoreText"><?php echo __( 'Weitere Artikel', 'wegwandern' ); ?></span>
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
