<?php
/**
 * Template Name: Gipfelbuch Pinwand Template
 *
 * @package wegwandern-summit-book
 */

get_header();
$pinwand_heading           = __( 'Pinwand', 'wegwandern-summit-book' );
$pinwand_desc              = __( 'Sundendestis aped mos volorio nsequos et aut diciae conecus evendisima quam, se molore minia del int, similis sitat ma qui sum quis ab ipsaectur assinum, ut eostem ex eum et volupis quosto Mottet.', 'wegwandern-summit-book', 'wegwandern-summit-book' );
$pinwand_button_text       = __( 'Inserat erstellen', 'wegwandern-summit-book' );
$pinwand_button_below_text = __( 'Inserat erfassen', 'wegwandern-summit-book' );
$post_thumb                = get_the_post_thumbnail_url( $post->ID, 'full' );

if ( $post_thumb ) { ?>
	<div class="container-fluid">
		<img class="region-wander-img master-img" src="<?php echo $post_thumb; ?>" />
		<!-- <h5 class="master-category">Thema</h5> -->
		<!-- <h1 class='master-head-title'><?php // echo get_the_title( $post->ID ); ?></h1> -->
		<!-- <h3 class="master-sub-head"><?php // echo get_the_excerpt( $post->ID ); ?></h3> -->
		<!-- <span class="master-date"><?php // echo get_the_date( 'd, M Y' ); ?></span> -->
	</div>
<?php } ?>

<div class="container">
	<div class='pinwand-page'>

		<?php echo get_breadcrumb(); ?>
		<h1 class='page-title'><?php echo esc_attr( $pinwand_heading ); ?></h1>
		<p class='pinwand-desc'><?php echo esc_attr( $pinwand_desc ); ?></p>
		<?php if ( is_user_logged_in() ) { ?>
			<a class="create-pinwand-ad-link pinwand-action" href="<?php echo INSERAT_ERSTELLEN_URL; ?>">
		<?php } else { ?>
			<div class='pinwand-action'>
		<?php } ?>
			<span class="pinwand-create-btn-icon"></span>
			<span class="pinwand-create-btn-text"><?php echo esc_attr( $pinwand_button_text ); ?></span>
			<?php if ( ! is_user_logged_in() ) { ?>
				<script type="text/javascript">
					jQuery(document).ready(function(){
						jQuery('.pinwand-action').on('click', function() {
							openSummitBookLoginMenu();
						});
					})
				</script>
			<?php } ?>
		<?php if ( is_user_logged_in() ) { ?>
			</a>
		<?php } else { ?>
			</div>
		<?php } ?>
		<div class='pinwand-list'>
			<?php
			$args              = array(
				'post_type'   => 'pinnwand_eintrag',
				'post_status' => 'publish',
				'numberposts' => -1,
			);
			$pinnwand_eintrags = get_posts( $args );
			if ( ! empty( $pinnwand_eintrags ) ) {
				foreach ( $pinnwand_eintrags as $key => $each_ad ) {
					get_pinwand_ad_view( $each_ad->ID );
					if ( 8 === $key ) {
						wegwandern_ad_section_display( 'center-between-contents', true, true, true );
					}
				}
			}
			?>
		</div>
		<?php if ( is_user_logged_in() ) { ?>
			<a class="create-pinwand-ad-link pinwand-action-below" href="<?php echo INSERAT_ERSTELLEN_URL; ?>">
		<?php } else { ?>
			<div class='pinwand-action pinwand-action-below'>
		<?php } ?>
			<span class="pinwand-create-btn-icon"></span>
			<span class="pinwand-create-btn-text"><?php echo esc_attr( $pinwand_button_text ); ?></span>
		<?php if ( is_user_logged_in() ) { ?>
			</a>
		<?php } else { ?>
			</div>
		<?php } ?>	
	</div>
</div>

<?php get_footer(); ?>
