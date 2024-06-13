<?php
/**
 * Template used for Hiking blog/post/article create page - `Meine Wanderungen`
 *
 * @package wegwandern-summit-book
 */

get_header(); ?>
<div class='summit-book-user-navigation'>
	<?php echo do_shortcode( '[display-summit-book-user-menu]' ); ?>
</div>
<div class='container'>
<?php echo get_breadcrumb(); ?>
	<div class='user-create-article'>
		<?php
		$article_heading_text = __( 'Wanderbeschrieb erstellen', 'wegwandern-summit-book' );

		if ( ! empty( $_GET ) && isset( $_GET['frm_action'] ) && 'edit' === $_GET['frm_action'] ) {
			$article_heading_text = __( 'Wanderbeschrieb bearbeiten', 'wegwandern-summit-book' );
		}
		?>

		<h1 class='summit-book-create-article-title'><?php echo esc_attr( $article_heading_text ); ?></h1>
		<p class='summit-book-create-article-desc'>
			<?php
			echo esc_attr_e(
				'Hier kannst du einen Wanderbeschrieb erstellen. Zu deinem Beitrag wird dein
				Avatarbild und Nickname angezeigt. Wenn du beides in deinem Profil nicht angelegt hast,
				erscheint ein grauer Platzhalter und dein Vorname.',
				'wegwandern-summit-book'
			);
			?>

			<br>

			<?php
			echo esc_attr_e(
				'In deinem Beitrag stehen dir 70 Zeichen für eine aussagekräftige Überschrift, 250 Zeichen
				für eine Einleitung und 4000 Zeichen für deinen Wanderbeschrieb zur Verfügung. Darüber
				hinaus kannst du in deinem Beitrag bis zu 6 Bilder hochladen (ideal zwischen 1500 und 1800
				Pixel Breite), die dann in einer Bildergalerie angezeigt werden. Das erste Bild wird als
				Aufmacher-Bild (Headerbild) des Beitrags für die Teaserboxen verwendet.',
				'wegwandern-summit-book'
			);
			?>

			<br><br>

			<?php
			echo esc_attr_e(
				'So bald du den Beitrag zur Veröffentlichung frei gegeben hast geht dieser zu uns zur
				Kontrolle und wird dann frei geschalten oder abgelehnt. Bei einer Ablehnung bekommst du
				ein E-Mail von uns zur Information. Dedizierte Gründe für eine Ablehnung nennen wir nicht,
				bitte schau dir in so einem Fall unsere Regeln für die Beitragserstellung an.',
				'wegwandern-summit-book'
			);
			?>
		</p>

		<div class='summit-book-create-article-form-section'>
			<?php
			while ( have_posts() ) :
				the_post();

				get_template_part( 'template-parts/content', 'page' );

			endwhile;
			?>
		</div>
	</div>
</div>

<?php get_footer(); ?>
