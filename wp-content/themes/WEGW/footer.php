<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Wegwandern testdddddddd
 */
?>

<footer>
   <div class="container">
	   <div class="footer-wrapper">
		   
		  
	 
	  <div class="column2">
		<?php // dynamic_sidebar( 'footer_widget' ); ?>
		<section>
		<h2 class="widget-title foot-col-title"><?php echo __( 'Service', 'wegwandern' ); ?></h2>
		<div class="menu-footer-menu-1-container">
			<?php
				wp_nav_menu(
					array(
						'menu'            => 'Footer menu 1',
						'container'       => 'ul',
						'container_class' => 'menu',
						'menu_class'      => 'menu',
					)
				);
				?>
		</div>
		</section>
		<section>
		<div class="menu-footer-menu-2-container">
			<?php
				wp_nav_menu(
					array(
						'menu'            => 'Footer menu 2',
						'container'       => 'ul',
						'container_class' => 'menu',
						'menu_class'      => 'menu',
					)
				);
				?>
		</div>
		</section>
	  </div>
	  
	  <div class="column"> 
		   <div class="column1">
			   <?php /*
			 <div class="foot-col-title"><?php echo __( 'Unser Hauptsponsor', 'wegwandern' ); ?></div>
			  <div class="footer_swica_logo"><img src="<?php echo get_template_directory_uri() . '/img/swica_logo.png'; ?>"></div>*/ ?>
			 </div> 
	   </div>
	 
	  <div class="column3">
		 <div class="foot-col-title"><?php echo __( 'Bleib auf unseren Spuren', 'wegwandern' ); ?></div>
		 <div class="social_icons_wrapper">
			<div class="social_icons">
				<a class="icon_insta" href="<?php the_field( 'instagram_url', 'option' ); ?>" target="_blank"><img src="<?php echo get_template_directory_uri() . '/img/insta.svg'; ?>"></a>
				<a class="icon_twitter" href="<?php the_field( 'twitter_url', 'option' ); ?>" target="_blank"><img src="<?php echo get_template_directory_uri() . '/img/twitter.svg'; ?>"></a>
				<a class="icon_youTube" href="<?php the_field( 'youtube_url', 'option' ); ?>" target="_blank"><img src="<?php echo get_template_directory_uri() . '/img/youtube.svg'; ?>"></a>
				<a class="icon_facebook" href="<?php the_field( 'facebook_url', 'option' ); ?>" target="_blank"><img src="<?php echo get_template_directory_uri() . '/img/facebook.svg'; ?>"></a>

			</div>
		 </div>
		 <div class="footer_address">
			<div>
			<?php
					_e( '© ', 'wegwandern' );
					echo date( 'Y' ) . ' ';
					the_field( 'copyright_contents', 'option' );
			?>
			</div>
		<div class="awards">
			<a href="https://www.bestofswissweb.swiss/bosw/hall-of-fame" target="_blank">
					<img alt="best of swiss web award" class="award_img" src="<?php echo get_template_directory_uri() . '/img/bosw-2024-shortlist.svg'; ?>">
			</a>
			<a href="<?php the_field( 'award_link', 'option' ); ?>" target="_blank">
			<img class="award_img" src="<?php echo get_template_directory_uri() . '/img/q_award.png'; ?>">
			</a>
		</div>	
		 </div>
	  </div>
	  
	  <a class="cookie-Settings" href="#consent-change"><?php _e( 'Cookie-Einstellungen', 'bbvb-domain' ); ?></a>

</div>
   </div>
</footer>

<?php wp_footer(); ?>

<div class="overlayAngebote hide"></div>

</body>
</html>
