<?php
/**
 * Template used for Hiking description create page
 *
 * @package wegwandern-summit-book
 */

get_header();
?>
<div class='summit-book-user-navigation'>
	<?php echo do_shortcode( '[display-summit-book-user-menu]' ); ?>
</div>
<div class='container'>
	<?php echo get_breadcrumb(); ?>
	<div class='user-create-pinwall-ad'>
		<?php
		$pinwall_ad_heading_text = __( 'Inserat erstellen', 'wegwandern-summit-book' );
		if ( ! empty( $_GET ) && isset( $_GET['frm_action'] ) && 'edit' === $_GET['frm_action'] ) {
			$pinwall_ad_heading_text = __( 'Inserat bearbeiten', 'wegwandern-summit-book' );
		}
		?>

		<h1 class='summit-book-create-pinwall-ad-title'><?php echo esc_attr( $pinwall_ad_heading_text ); ?></h1>
		<p class='summit-book-create-pinwall-ad-desc'>

			<?php
			echo esc_attr_e(
				'Hier kannst du ein Inserat erstellen. In deinem Post wird dein Avatarbild und
		        Nickname angezeigt. Wenn du beides in deinem Profil nicht angelegt hast, erscheint ein
		        grauer Platzhalter und dein Vorname. Bei der Anzeige stehen dir 70 Zeichen für eine
		        aussagekräftige Überschrift und 500 Zeichen für dein Inserat zur Verfügung. Zur
		        Kontaktaufnahme ist die Angabe einer E-Mail-Adresse verpflichtend. Optional kannst du
		        auch deine Telefonnummer angeben.',
				'wegwandern-summit-book'
			);
			?>

			<br>

			<?php
			echo esc_attr_e(
				'Du kannst eine Laufzeit angeben, ohne diese Angabe wird das Inserat automatisch nach 6
			    Monaten gelöscht. Dein Inserat wird vor Veröffentlichung geprüft. Dies kann einige Tage
			    dauern. An Wochenenden und Feiertagen werden keine Inserate publiziert.
			    Du kannst die Anzeige vor der Veröffentlichung beliebig oft speichern und korrigieren.
			    Wenn du die Anzeige nicht sofort aufgeben möchtest, bleibt diese in deiner Übersicht als
			    gespeichert markiert stehen. Einmal veröffentlicht, kannst du das Insert nicht mehr
			    bearbeiten und nur löschen.',
				'wegwandern-summit-book'
			);
			?>

			<br>

			<?php
			echo esc_attr_e(
				'Wir behalten uns vor, unpassende Beiträge ohne Rücksprache abzulehnen. Bitte beachte
			    unsere Nutzungsbedingungen.',
				'wegwandern-summit-book'
			);
			?>

		</p>

		<div class='summit-book-create-pinwall-ad-form-section'>
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
