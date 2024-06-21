<?php
/**
 * Template Name: Gipfelbuch Edit Profile Template
 *
 * @package wegwandern-summit-book
 */

get_header();
?>
<div class='summit-book-user-navigation'>
	<?php echo do_shortcode( '[display-summit-book-user-menu]' ); ?>
</div>
<div class='user-edit-profile-page container'>
	<?php echo get_breadcrumb(); ?>
	<h1><?php echo esc_attr_e( 'Dein Profil', 'wegwandern-summit-book' ); ?></h1>
	<div class='user-edit-profile-form-section'>
		<?php
		$user_profile_form_id = FrmForm::get_id_by_key( 'edit-user-profile-summit-book' );
		echo do_shortcode( "[formidable id=$user_profile_form_id]" );
		?>
	</div>
</div>
<?php get_footer(); ?>
