<?php get_header(); 
b2b_user_menu_callback();
?>

<main id="primary" class="site-main">
<div class="container">
		<?php
		while ( have_posts() ) :
			the_post();
			
			get_template_part( 'template-parts/content', 'page' );

		endwhile; // End of the loop.
		?>
</div>
</main><!-- #main -->

<?php get_footer(); ?>
