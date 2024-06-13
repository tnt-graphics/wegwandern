<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Wegwandern
 */
get_header();
global $post;

$wanderung_id        = $post->ID;
$wanderregionen      = get_the_terms( $wanderung_id, 'wanderregionen' );
$wanderregionen_name = ( ! empty( $wanderregionen ) ) ? $wanderregionen[0]->name : 'Region';
$hike_level          = get_the_terms( $wanderung_id, 'anforderung' );
$hike_level_name     = ( ! empty( $hike_level ) ) ? $hike_level[0]->name : '';
$hike_level_cls      = wegw_wandern_hike_level_class_name( $hike_level_name, $wanderung_id );

$hike_time     = ( get_field( 'dauer', $wanderung_id ) ) ? get_field( 'dauer', $wanderung_id ) : '';
$hike_distance = ( get_field( 'km', $wanderung_id ) ) ? get_field( 'km', $wanderung_id ) : '';
$hike_ascent   = ( get_field( 'aufstieg', $wanderung_id ) ) ? get_field( 'aufstieg', $wanderung_id ) : '';
$hike_descent  = ( get_field( 'abstieg', $wanderung_id ) ) ? get_field( 'abstieg', $wanderung_id ) : '';
$kurzbeschrieb = ( get_field( 'kurzbeschrieb', $wanderung_id ) ) ? get_field( 'kurzbeschrieb', $wanderung_id ) : 'Fuga Nequam nos dolupta testinu llaceri ssequi nihilit, ut quissedia voluptassint prenimusam inum harchit imet am, aped mos volorio nsequos qui sundendestis aped mos volorio inum Onsequos et ...';

$wander_saison_name = wegw_wandern_saison_name( $wanderung_id );
$gpx_file           = ( get_field( 'gpx_file', $wanderung_id ) ) ? get_field( 'gpx_file', $wanderung_id ) : 'undefined';

/* Check if have gpx file in field */
if ( $gpx_file != 'undefined' ) {
	$json_gpx_data = get_field( 'json_gpx_file_data', $wanderung_id );
} else {
	$json_gpx_data = 'undefined';
}

if ( have_rows( 'manage_ad_scripts', 'option' ) ) :
	while ( have_rows( 'manage_ad_scripts', 'option' ) ) :
		the_row();

		$desktop_ad_scripts        = get_sub_field( 'desktop_ad_scripts', 'option' );
		$ad_script_desktop_300x600 = '';
		$ad_script_desktop_994x500 = '';

		foreach ( $desktop_ad_scripts as $desktop_ad ) {
			if ( $desktop_ad['ad_size'] = '300×600' ) {
				$ad_script_desktop_300x600 = $desktop_ad['ad_script'];
			}
			if ( $desktop_ad['ad_size'] = '994x500' ) {
				$ad_script_desktop_994x500 = $desktop_ad['ad_script'];
			}
		}

		$tablet_ad_scripts        = get_sub_field( 'tablet_ad_scripts', 'option' );
		$ad_script_tablet_300×250 = '';
		$ad_script_tablet_300×600 = '';

		foreach ( $tablet_ad_scripts as $tablet_ad ) {
			if ( $tablet_ad['ad_size'] = '300×250' ) {
				$ad_script_tablet_300×250 = $tablet_ad['ad_script'];
			}
			if ( $tablet_ad['ad_size'] = '300×600' ) {
				$ad_script_tablet_300×600 = $tablet_ad['ad_script'];
			}
		}

		$mobile_ad_scripts = get_sub_field( 'mobile_ad_scripts', 'option' );
		$ad_script_mobile  = '';
		foreach ( $mobile_ad_scripts as $mob_ad ) {
			if ( $mob_ad['ad_size'] = '300×250' ) {
				$ad_script_mobile = $mob_ad['ad_script'];
			}
		}

   endwhile;
endif;
wanderung_planen_section();
?>

<main id="primary" class="single-detail-wander">
<?php
	$header_slider = get_field( 'header_slider', $post->ID );


	$post_thumb    = get_the_post_thumbnail_url( $post->ID, 'full' );
	$thumb         = get_the_post_thumbnail_url( $post->ID, 'thumbnail' );
	$thumb_caption = get_the_post_thumbnail_caption( $post->ID );
	$count_html    = '';
if ( ! empty( $post_thumb ) ) {
	if ( ! empty( $header_slider ) ) {
		 $count_slide = count( $header_slider );
		 $count_slide = $count_slide + 1;
		 // $count_html  = '<div id="counter">' . $count_slide . '</div>';
		 $count_html = '<div id="counter" onclick="openLightGallery(this)">1/' . $count_slide . '</div>';
	} else {
		$count_html = '<div id="counter" onclick="openLightGallery(this)">1/1</div>';
	}
	?>



<div class="container-fluid">
	<div class="demo-gallery">
		<div class="single-wander-heart detail-single-wander-heart"></div>
		<?php echo $count_html; ?>
		<div id="lightgallery" class="single-wander-img list-unstyled row">
		 <div class="justified-gallery" data-src="<?php echo $post_thumb; ?>" data-sub-html="<?php echo $thumb_caption; ?>">
				<a href="<?php echo $post_thumb; ?>">
					<img class="wander-img detail-wander-img" src="<?php echo $post_thumb; ?>" />
				</a>
			</div>
		   <?php
			if ( ! empty( $header_slider ) ) {
				foreach ( $header_slider as $slide ) {
					?>
				  <div class="justified-gallery"
				  data-src="<?php echo $slide['image']['url']; ?>" data-thumb="<?php echo $slide['image']['sizes']['thumbnail']; ?>"
					data-sub-html="<?php echo $slide['image']['caption']; ?>">
					<a href="<?php echo $slide['image']['url']; ?>">
					  <img class="wander-img detail-wander-img" src="<?php echo $slide['image']['sizes']['thumbnail']; ?>" />
					</a>
				  </div>
						<?php
				}
			}
			?>
				</div>
			  </div>
</div>


<?php } ?>

<div class="container">
	<div class="detail-region-rating">
		<h6 class='detail-region'><?php echo $wanderregionen_name; ?></h6>
		<?php
		if(is_plugin_active('wegwandern-summit-book/wegwandern-summit-book.php')) {
		?>
		<span class="average-rating-display"><?php echo do_shortcode('[display-hike-average-rating]'); ?>
		<i class="fa fa-star"></i></span>
		<?php } ?>
	</div>
	<h1 class='detail-title'><?php echo get_the_title( $wanderung_id ); ?></h1>
	<div class='detail-wrapper'>
<div class="single-hike-left">
   <h6 class='detail-region-resp'><?php echo $wanderregionen_name; ?></h6>
   <h2 class='detail-title-resp'><?php echo get_the_title( $wanderung_id ); ?></h2>
	
<div class="detail-infobox">
   <div class="detail-hike-details" >

	  <?php if ( isset( $hike_level_cls ) && $hike_level_cls != '' ) { ?>
		 <div class="hike_level">
			   <span class="<?php echo $hike_level_cls; ?>"></span>
			   <p><?php echo $hike_level_name; ?></p>
		 </div>
	  <?php } ?>

	  <?php
		if ( isset( $hike_time ) && $hike_time != '' ) {
			$formatted_date = wegwandern_formated_hiking_time_display( $hike_time );
			?>
		 <div class="hike_time">
			<span class="hike-time-icon"></span>
			<p><?php echo $formatted_date; ?> h</p>
		 </div>
		<?php } ?>

	  <?php if ( isset( $hike_distance ) && $hike_distance != '' ) { ?>
		 <div class="hike_distance">
			<span class="hike-distance-icon"></span>
			<p><?php echo round( $hike_distance, 1 ); ?> km</p>
		 </div>
	  <?php } ?>

	  <?php if ( isset( $hike_ascent ) && $hike_ascent != '' ) { ?>
		 <div class="hike_ascent">
			<span class="hike-ascent-icon"></span>
			<p><?php echo $hike_ascent; ?> m</p>
		 </div>
	  <?php } ?>

	  <?php if ( isset( $hike_descent ) && $hike_descent != '' ) { ?>
		 <div class="hike_descent">
			<span class="hike-descent-icon"></span>
			<p><?php echo $hike_descent; ?> m</p>
		 </div>
	  <?php } ?>

	  <?php if ( isset( $wander_saison_name ) & $wander_saison_name != '' ) { ?>
		 <div class="hike_month">
			   <span class="hike-month-icon"></span>
			   <p><?php echo $wander_saison_name; ?></p>
		 </div>
	  <?php } ?>
   </div>
</div>

<?php if ( isset( $kurzbeschrieb ) & $kurzbeschrieb != '' ) { ?>
   <h2 class="wegw-single-page-desc">
	  <?php echo $kurzbeschrieb; ?>
</h2>
<?php } ?>

<div class="wanderung-plan" onclick="openWanderungPlan()">
	<span class="planIcon"></span>
	<span class="planText">Wanderung planen</span>
</div>

<!-- section -->
<div id="weg-map-popup-full-detail-page-PopContainer">
<div id="weg-map-popup-full-detail-page" >
   <div id="weg-map-popup-inner-wrapper-full-detail-page">
	  <div class="close_map" onclick="closeElementDetailPage(this)"><span class="close_map_icon"></span></div>
	  <div id="cesiumContainer" class="cesiumContainer"></div>
	  <div id="threeD" class="map_3d"></div>
	  <div id="map_direction" class="map_direction"></div>
	  <div class="botom_layer_icon">
		 <div class="accordion" >
			<div class="weg-layer-wrap layer_head">
			   <div class="weg-layer-text">Hintergrund</div>
			</div>
		 </div>
		 <div class="panel">
			<div class="weg-layer-wrap activeLayer" id="colormap_view_section">
			   <div class="weg-layer-text">Karte farbig</div>
			</div>
			<div class="weg-layer-wrap" id="aerial_view_section">
			   <div class="weg-layer-text">Luftbild</div>
			</div>
			<div class="weg-layer-wrap" id="grey_view_section">
			   <div class="weg-layer-text">Karte SW</div>
			</div>
		 </div>
	  </div>
	  <div class="copyRight">
		 <a target="_blank" href="https://www.swisstopo.admin.ch/de/home.html">© swisstopo</a>
	  </div>
	  <div class="map_filter">
		 <div class="map_filter_inner_wrapper">
			<div class="accordion"><?php echo __( 'Karteninformationen', 'wegwandern' ); ?></div>
			<div class="panel">
			   <div class="fc_check_wrap">
				  <label class="check_wrapper"><?php echo __( 'ÖV-Haltestellen', 'wegwandern' ); ?><input type="checkbox" name="" id="transport_layer_checkbox" value=""><span class="redmark"></span></label>
				  <label class="check_wrapper"><?php echo __( 'Wanderwege', 'wegwandern' ); ?><input type="checkbox" name="" id="hikes_trailing_layer" value=""><span class="redmark"></span></label>
				  <label class="check_wrapper"><?php echo __( 'Gesperrte Wanderwege', 'wegwandern' ); ?>
				  <input type="checkbox" name="" id="closure_hikes_layer" value=""><span class="redmark"></span></label>
				  <label class="check_wrapper"><?php echo __( 'Schneehöhe ExoLabs', 'wegwandern' ); ?><input type="checkbox" id="snow_depth_layer" name="" value=""><span class="redmark"></span><div class="info_icon" onclick="infoIconClicked(event,'weg-map-popup-full-detail-page')"></div></label>
				  <label class="check_wrapper"><?php echo __( 'Schneebedeckung ExoLabs', 'wegwandern' ); ?><input type="checkbox" id="snow_cover_layer" name="" value=""><span class="redmark"></span></label>
				  <label class="check_wrapper"><?php echo __( 'Hangneigungen über 30°', 'wegwandern' ); ?><input type="checkbox" id="slope_30_layer" name="" value=""><span class="redmark"></span></label>
				  <label class="check_wrapper"><?php echo __( 'Wildruhezonen', 'wegwandern' ); ?><input type="checkbox" id="wildlife_layer" name="" value=""><span class="redmark"></span></label>
				  <label class="check_wrapper"><?php echo __( 'Wegpunkte WegWandern.ch', 'wegwandern' ); ?><input type="checkbox" id="waypoints_layer" name="" value=""><span class="redmark"></span></label>
			   </div>
			</div>
		 </div>
	  </div>
   </div>
	
	<div class="elevationGraph"></div>		   
   <div class="options" id="mapOptions"></div>
	<div class="snow_info_details hide">
		<div class="snow_inner_wrapper">
			<div class="snow_close_wrapper" onclick="infoIconClosed(event,'weg-map-popup-full-detail-page')"><div class="snow_close"></div></div>
			<div class="snow_tile">Auf der Karte wird die Schneehöhe (in cm) mit den folgenden Farben angezeigt:</div>
			<div class="snow_image"></div>
			<a href="https://wegwandern.ch/schneekarten-wo-liegt-jetzt-schnee/" target="_blank"><div class="snow_link externalLink">Weitere Informationen</div></a>
		</div>
	</div>
				  
   <div id="info"></div>
   <div class="popover" id="transport-layer-info-popup">
	  <div class="arrow"></div>
	  <div class="popover-title">
		 <div class="popup-title"><?php echo __( 'Objekt Informationen', 'wegwandern' ); ?></div>
		 <div class="popup-buttons">
			<!-- <button class="fa-print" title="Print"></button> -->
			<!-- <button class="fa-minus" title="Minimize"></button> -->
			<button class="fa fa-remove" title="Close" onclick="closeTransportLayerPopup()"></button>
		 </div>
	  </div>
	  <div class="popover-content">
		 <div class="popover-scope">
			<div class="popover-binding">
			   <div class="htmlpopup-container" id="tl-content-area"></div>
			</div>
		 </div>
	  </div>
   </div>
	
		   
</div>
</div>
<!-- section -->
<!-- map section -->

<div id="weg-map-popup-detail-page-wrapper">
   <div id="cesiumContainer" class="cesiumContainer"></div>
   <div id="threeD" class="map_3d"></div>
   <div id="map_direction" class="map_direction"></div>
   <div class="botom_layer_icon">
	  <div class="accordion">
		 <div class="weg-layer-wrap layer_head">
			<div class="weg-layer-text">Hintergrund</div>
		 </div>
	  </div>
	  <div class="panel">
		 <div class="weg-layer-wrap activeLayer" id="colormap_view_section">
			<div class="weg-layer-text">Karte farbig</div>
		 </div>
		 <div class="weg-layer-wrap" id="aerial_view_section">
			<div class="weg-layer-text">Luftbild</div>
		 </div>
		 <div class="weg-layer-wrap" id="grey_view_section">
			<div class="weg-layer-text">Karte SW</div>
		 </div>
	  </div>
   </div>
   <div class="copyRight">
	  <a target="_blank" href="https://www.swisstopo.admin.ch/de/home.html">© swisstopo</a>
   </div>
   <div class="karte-eleProfile-btn" onclick="openPopupMapDetailPage(this)" data-hikeid="<?php echo $wanderung_id; ?>">
	  <span class="karte-eleProfile-btn-icon"></span>
	  <span class="karte-eleProfile-btn-text">Karte & Höhenprofil</span>
   </div>
</div>

<div id="detailPgPopup"><div id="detailPgPopupContent"></div></div>
<!-- map section -->
			<?php
			while ( have_posts() ) :
				the_post();
					get_template_part( 'template-parts/content', 'page' );

			endwhile; // End of the loop.
			?>
	</div>
	<div class="single-hike-right">
		<?php
		if ( $ad_script_desktop_300x600 != '' ) {
			echo '<div class="ad-section-wrap ad-block-content-desktop-wrapper"><p>Anzeige</p><div class="ad-section">' . $ad_script_desktop_300x600 . '</div></div>';
		}

		if ( $ad_script_tablet_300×250 != '' ) {
			echo '<div class="ad-section-wrap ad-block-content-tablet-wrapper"><p>Anzeige</p><div class="ad-section">' . $ad_script_tablet_300×250 . '</div></div>';
		}

		if ( $ad_script_mobile != '' ) {
			echo '<div class="ad-section-wrap ad-block-content-mobile-wrapper"><p>Anzeige</p><div class="ad-section">' . $ad_script_mobile . '</div></div>';
		}
		?>
	</div>
			
			
	</div>
<?php
if ( is_plugin_active( 'wegw-b2b/wegw-b2b.php' ) ) {
	 angebote_slider_display();
}

?>

	<div class='ad-section-wrap ad-block-content-desktop-wrapper full-width'>
		 <p>Anzeige </p>
		 <div class='ad-section insidewide'></div>
	</div>
	<script>
		 // Define a function to execute on load and resize
	function loadAdInside() {
		var windowWidth = $(window).width();
		$('.ad-section.insidewide').empty();
		 
		 if (windowWidth > 900) {
		   
		   // Create a div with the ID 'div-ad-gds-1280-1'
		   var adDiv = $('<div>', { id: 'div-ad-gds-1280-7' });
		 
		   // Create a script element and set its type and content
		   var adScript = document.createElement('script');
		   adScript.type = 'text/javascript';
		   adScript.innerHTML = 'gbcallslot1280("div-ad-gds-1280-7", "");';
		 
		   // Append the script to the created div
		   adDiv.append(adScript);
		 
		   // Append the created div with the script to the '.ad-section' div
		   $('.ad-section.insidewide').html(adDiv);
		 } else if (windowWidth > 700) {
		 
			// Create a div with the ID 'div-ad-gds-1280-1'
			var adDiv = $('<div>', { id: 'div-ad-gds-4440-4' });
		  
			// Create a script element and set its type and content
			var adScript = document.createElement('script');
			adScript.type = 'text/javascript';
			adScript.innerHTML = 'gbcallslot4440("div-ad-gds-4440-4", "");';
		  
			// Append the script to the created div
			adDiv.append(adScript);
		  
			// Append the created div with the script to the '.ad-section' div
			$('.ad-section.insidewide').html(adDiv);
		  } else if (windowWidth < 700) {
		   
			  // Create a div with the ID 'div-ad-gds-1280-1'
			  var adDiv = $('<div>', { id: 'div-ad-gds-1281-5' });
			
			  // Create a script element and set its type and content
			  var adScript = document.createElement('script');
			  adScript.type = 'text/javascript';
			  adScript.innerHTML = 'gbcallslot1281("div-ad-gds-1281-5", "");';
			
			  // Append the script to the created div
			  adDiv.append(adScript);
			
			  // Append the created div with the script to the '.ad-section' div
			  $('.ad-section.insidewide').html(adDiv);
			} 
	
	}
		 
		 // Execute the function on page load
		 jQuery(document).ready(function(){
		   loadAdInside();
		 });
		 
	</script>

</div>
<?php wanderung_region_slider(); 
if(is_plugin_active('wegwandern-summit-book/wegwandern-summit-book.php')) {
	wegwandern_summit_book_add_comment_form();
}
?>


</main><!-- #main -->

<script type="text/javascript">
	var json_gpx_data_single_hike_detail = <?php echo $json_gpx_data; ?>; 
	var gpx_file_single_hike_detail = '<?php echo $gpx_file; ?>';
</script>


<?php
get_footer();
