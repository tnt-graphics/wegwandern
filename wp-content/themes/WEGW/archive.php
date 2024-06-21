<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Wegwandern
 */

get_header();

global $wp_query;

$post_type        = get_post_type( get_the_ID() );
$cate             = '';
$current_cat      = get_queried_object();
$cat_id           = $current_cat->term_id;
$current_cat_name = $current_cat->name;
$current_taxonomy = $current_cat->taxonomy;

if ( ! empty( $post_type ) && 'post' != $post_type ) {
	$posts_per_page = 11;
	$total_results  = $wp_query->found_posts; ?>

	<div <?php post_class( 'container' ); ?>>
		<?php echo get_breadcrumb(); ?>
		<h1 class="page-title"><?php echo $current_cat_name; ?></h1>
		<div class="searchResultContainer">
			<div class="searchResultSection">
				<?php $count = 1; ?>
				<div class="searchResult_list">
					<?php
					if ( $wp_query->have_posts() ) :
						/* Start the Loop */
						while ( $wp_query->have_posts() ) :
							$wp_query->the_post();

							/**
							 * Run the loop for the search to output the results.
							 * If you want to overload this in a child theme then include a file
							 * called content-search.php and that will be used instead.
							 */
							get_template_part( 'template-parts/content', 'search', array( 'count' => $count ) );
							$count++;
						endwhile;
					else :
						?>
						<h2 class="noWanderung"> <?php _e( 'Keine Blogs gefunden', 'wegwandern' ); ?></h2>
					<?php endif; ?>
				</div>
			</div>
			<div class="searchResultAdContainer single-hike-right">
				<?php wegwandern_ad_section_display( 'right', true, false, false ); ?>
			</div>
		</div>

		<?php if ( $total_results > $posts_per_page ) { ?>
			<div class="LoadMore" id="taxonomy-loadmore" data-count="<?php echo $total_results; ?>" data-offset="11" data-nonce="<?php echo wp_create_nonce( 'taxonomy_nonce' ); ?>" data-postType="<?php echo $post_type; ?>" data-taxonomy="<?php echo $current_taxonomy; ?>" data-termId="<?php echo $cat_id; ?>" >
				<input type="hidden">
				<span class="LoadMoreIcon"></span>
				<span class="LoadMoreText" id='searchLoadMoreText'><?php echo __( 'Weitere Suchergebnisse', 'wegwandern' ); ?></span>
			</div>

			<div id="loader-icon" class="hide"></div>

			<div class="noWanderungSearchPost" style="display:none;">
				<h2 class="noWanderung"> <?php _e( 'Keine Einträge gefunden.', 'wegwandern' ); ?></h2>
			</div>
		<?php } ?>
	</div>
<?php } else { ?>

	<main id="primary" class="site-main" role="main">
		<?php if ( have_posts() ) : ?>
			<div class="container">
				<?php echo get_breadcrumb(); ?>
				<h1 class="page-title"><?php echo get_the_title( get_option( 'page_for_posts', true ) ); ?></h1>

				<?php
				$categories_args = array(
					'hide_empty' => 1,
					'exclude'    => array( 1873 ),
				);

				$categories = get_categories( $categories_args );
				if ( ! empty( $categories ) ) {
					$cate .= '<div class="blog_top_menu"><ul><li><a href="' . get_permalink( get_option( 'page_for_posts' ) ) . '">' . __( 'Alle Themen', 'wegwandern' ) . '</a></li>';
					foreach ( $categories as $category ) {
						$active_class = '';
						if ( $category->term_id === $cat_id ) {
							$active_class = 'active';
						}
						$cate .= '<li class="' . $active_class . '"><a href="' . get_category_link( $category->term_id ) . '">' . $category->name . '</a></li>';
					}
					$cate .= '</ul></div>';
				}

				echo $cate;
				$count      = 1;
				$trim       = 30;
				$thumb_size = 'teaser-onecol';
				?>

				<input type="hidden" class="page_type" value="<?php echo $cat_id; ?>">

				<div class="blog_list">
					<?php
					while ( have_posts() ) :
						the_post();

						if ( $count > 2 ) {
							$trim       = 20;
							$thumb_size = 'teaser-twocol';
						}

						$post_id    = get_the_ID();
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

				<div class="LoadMore" id="blog-loadmore" onclick="archiveLoadMore('blog')">
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
	
<?php }

get_footer(); ?>
