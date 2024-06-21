<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Wegwandern
 */

get_header();
$cate = '';
?>

	<main id="primary" class="site-main" role="main">

		<?php
		$args = array(
			'post_type'  => 'post',
			'hide_empty' => 1,
			// 'posts_per_page' => 8,
		);

		$post_query = new WP_Query( $args );

		if ( $post_query->have_posts() ) :
			?>

			<div class="container">  
				<?php echo get_breadcrumb(); ?>
				<h1 class="page-title"><?php echo get_the_title( get_option( 'page_for_posts', true ) ); ?></h1>
			
				<?php
				$categories_args = array(
					'post_type'  => 'post',
					'hide_empty' => 1,
					'exclude'    => array( 1873 ),
				);

				$categories = get_categories( $categories_args );
				if ( ! empty( $categories ) ) {
					$cate .= '<div class="blog_top_menu"><ul><li class="active"><a href="' . get_permalink( get_option( 'page_for_posts' ) ) . '">' . __( 'Alle Themen', 'wegwandern' ) . '</a></li>';

					foreach ( $categories as $category ) {
						$cate .= '<li><a href="' . get_category_link( $category->term_id ) . '">' . $category->name . '</a></li>';
					}

					$cate .= '</ul></div>';
				}

				echo $cate;

				$count      = 1;
				$trim       = 30;
				$thumb_size = 'teaser-onecol';
				?>

				<input type="hidden" class="page_type" value="page">
				<div class="blog_list">

				<?php
				while ( $post_query->have_posts() ) :
					$post_query->the_post();

					if ( $count > 2 ) {
						$trim       = 20;
						$thumb_size = 'teaser-twocol';
					}

					$post_id = get_the_ID();
					// $category_object = get_the_category( $post_id );
					// $cat_count       = count( $category_object );
					$post_thumb = get_the_post_thumbnail_url( $post_id, $thumb_size );
					?>
					
					<div class="blog-wander">
						<div class="blog-wander-img">
							<a href="<?php echo get_permalink( $post_id ); ?>">
								<img class="blog-img" src="<?php echo $post_thumb; ?>">
							</a>
						</div>
						<h6><?php echo category_html( $post_id ); ?></h6>
						<h2><a href="<?php echo get_permalink( $post_id ); ?>"><?php the_title(); ?></a></h2>
						<div class="blog-desc">
							<?php
							if ( has_excerpt() ) {
								echo wp_trim_words( get_the_excerpt(), $trim, ' ...' );
							} else {
								echo wp_trim_words( get_the_content(), $trim, ' ...' );
							}
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
			  
			<div class="LoadMore" id="blog-loadmore">
				<input type="hidden">
					<span class="LoadMoreIcon"></span>
					<span class="LoadMoreText"><?php echo __( 'Weitere Artikel', 'wegwandern' ); ?></span>
				</div>
				<div id="loader-icon" class="hide"></div>
				<div class="newsletter">
					<?php $wegwandernch_newsletter_form_id = FrmForm::get_id_by_key( 'wegwandernch-newsletter' );
					echo do_shortcode( "[formidable id=$wegwandernch_newsletter_form_id]" ); ?>
				</div>
			</div>
		
		<?php endif; ?>

	</main>

<?php get_footer(); ?>
