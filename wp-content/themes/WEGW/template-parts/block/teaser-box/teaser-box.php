<?php
/**
 * Template for teaser box
 */
global $post;
$titel          = get_field( 'titel' );
$sub_title      = get_field( 'sub_title' );
$teaser_layout  = get_field( 'teaser_layout' );
$background_clr = get_field( 'background_clr' );

$bild       = get_field( 'bild' );
$count_bild = count( $bild );
$div_outer  = '';
$div_class  = '';
$cat_title  = '';

if ( $count_bild == 3 ) {
	$div_outer  = 'col3_wrap';
	$div_class  = 'col3_natur';
	// $t_img_size = 'hike-region';
	$t_img_size = 'teaser-twocol-lg-dimension';
	$cat_title  = 'kategorie_titel';
} elseif ( $count_bild == 2 ) {
	$div_outer  = 'col2_wrap';
	$div_class  = 'col2_natur';
	$t_img_size = 'teaser-twocol-lg-dimension';
	$cat_title  = 'kategorie_titel';
} elseif ( $count_bild == 1 ) {
	$div_outer  = 'col1_wrap';
	$t_img_size = 'teaser-onecol';

}

$layout_class = '';
if ( $teaser_layout == 'background_image' ) {
	$layout_class = 'bgImage';
	//$t_img_size   = ( $count_bild == 3 ) ? 'teaser-onecol' : 'teaser-twocol';
} elseif ( $teaser_layout == 'background_image_number' ) {
	$layout_class = 'bgImageNumber';
	//$t_img_size   = ( $count_bild == 3 ) ? 'teaser-onecol' : 'teaser-twocol';
	$cat_title    = 'amount';
}

$count_bild1_html  = '';
$count_bild23_html = '';
$teaser_html       = '';
if ( $count_bild == 1 || $background_clr ) {
	if ( $titel ) {

		$count_bild1_html .= '<h3 class="natur_title">' . $titel . '</h3>';
	} ?>

		<?php
		if ( $sub_title ) {
			$count_bild1_html .= '<h6 class="natur_sub__title">' . $sub_title . '</h6>';
		}
		if ( $count_bild == 1 && ! $background_clr ) { 
			$bild_url = ( isset( $bild[0]['teaser_link']['url'] ) && $bild[0]['teaser_link']['url'] != "" ) ? $bild[0]['teaser_link']['url'] : '';
			$bild_url_target = ( isset( $bild[0]['teaser_link']['target'] ) && $bild[0]['teaser_link']['target'] != "" ) ? $bild[0]['teaser_link']['target'] : '_self';
			$count_bild1_html .= '<a href="' . $bild_url . '" target="' . $bild_url_target . '"><img class="teaser_img" src="' . $bild[0]['teaser_Image']['sizes'][ $t_img_size ] . '">
			<div class="natur-img-content-wrap">
				<p>' . $bild[0]['kategorie_titel'] . '</p>
				<h3 class="natur-title">' . $bild[0]['img_titel'] . '</h3>
			</div>
		</a>';
		}
}

if ( $count_bild == 2 || $count_bild == 3 ) {
	$count_bild23_html .= '<div class="' . $div_class . '">';

	foreach ( $bild as $teaser ) {
		$teaser_url = ( isset( $teaser['teaser_link']['url'] ) && $teaser['teaser_link']['url'] != "" ) ? $teaser['teaser_link']['url'] : '';
		$teaser_url_target = ( isset( $teaser['teaser_link']['target'] ) && $teaser['teaser_link']['target'] != "" ) ? $teaser['teaser_link']['target'] : '_self';
		if ( $teaser_layout == 'background_image' || $teaser_layout == 'background_image_number' ) {
			$cont_html = '<a href="' . $teaser_url . '" target="' . $teaser_url_target . '"><img class="teaser_img" src="' . $teaser['teaser_Image']['sizes'][ $t_img_size ] . '">
								<div class="bgWrap"><h6>' . $teaser[ $cat_title ] . '</h6>
							    <h3>' . $teaser['img_titel'] . '</h3></div></a>';
		} else {
			$cont_html = '<a href="' . $teaser_url . '" target="' . $teaser_url_target . '"><img class="teaser_img" src="' . $teaser['teaser_Image']['sizes'][ $t_img_size ] . '"></a>
							<a class="region-link-wrap" href="' . $teaser_url . '" target="' . $teaser_url_target . '"><h6>' . $teaser[ $cat_title ] . '</h6>
							<h3>' . $teaser['img_titel'] . '</h3></a>';
		}

		$count_bild23_html .= '<div class="single_natur">' . $cont_html . '</div>';
	}
		 $count_bild23_html .= '</div>';
}
?>


<?php
$teaser_html .= '<div class="' . $div_outer . ' ' . $layout_class . '">' . $count_bild1_html . $count_bild23_html . '</div>';
if ( $background_clr ) {
	$weitere_tips = get_field( 'weitere_tips' );
	$tipss 		  = '';
	$tipshtml     = '';
	if ( ! empty( $weitere_tips ) ) {
		foreach ( $weitere_tips as $tips ) {
			$tipss .= '<li><a href="' . $tips['tipps_url'] . '">' . $tips['tipps'] . '</a></li>';
		}

		$tipshtml = '<div class="tipps-outer">
            <h3 class="tipps-head">' . __( 'Weitere Tipps', 'wegwandern' ) . '</h3>
            <ul>
            ' . $tipss . '
            </ul>
         </div>';

	}

	$teaser_html = '<div class="teaser-bg-wrapper grey-back"><div class="teaser-bg-inner-wrapper">' . $teaser_html . $tipshtml . '</div></div>';
}
echo $teaser_html;


