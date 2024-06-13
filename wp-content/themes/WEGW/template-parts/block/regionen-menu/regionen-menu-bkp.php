<?php
/**
 * Regionen menu
 **/
global $post;
$region_type                = get_field( 'region_type' );
$wanderung_regionen         = get_field( 'wanderung_regionen' );
$reg                        = get_term( $wanderung_regionen );
$wanderung_regionen_url_upd = get_term_link( $reg );
$child_reg                  = get_term_children( $wanderung_regionen, 'wanderregionen' );
$termParent                 = '';
$arr                        = array();
$term_name                  = ( isset( get_term( $wanderung_regionen )->name ) ) ? get_term( $wanderung_regionen )->name : '';
$count_child_reg            = count( $child_reg );
$slider_sub_titel           = get_field( 'slider_sub_titel' );
$hide_icon                  = get_field( 'hide_icon' );
$slider_titel               = get_field( 'slider_titel' );

if ( is_string( $wanderung_regionen_url_upd ) && $wanderung_regionen_url_upd != '' ) {
	$wanderung_regionen_url = $wanderung_regionen_url_upd;
} else {
	$wanderung_regionen_url = '';
}

if ( empty( $child_reg ) ) {
	$term = get_term( $wanderung_regionen, 'wanderregionen' );
	if ( $term && ! is_wp_error($term) ) {
		$termParent                    = ( $term->parent == 0 ) ? $term : get_term( $term->parent, 'wanderregionen' );
		$wanderung_regionen_url_parent = get_term_link( $termParent );
		$child_reg                     = isset( $termParent->term_id ) ? get_term_children( $termParent->term_id, 'wanderregionen' ) : array();
		$count_child_reg               = count( $child_reg );
	}
}
if ( $region_type == 'slider' ) { ?>
	<div class="region_teaser_container">
		<h3 class="region_teaser_title">
			<?php echo __( 'Wanderregion ', 'wegwandern' ); ?><?php echo $term_name; ?>
			<span class="counter-in-region"><?php echo $count_child_reg; ?></span>
		</h3>
		<h6 class="region_teaser_sub__title"><?php echo $slider_sub_titel; ?></h6>
		<div class="region_teaser_wrap owl-carousel owl-theme">
			<?php
			if ( ! empty( $child_reg ) ) {
				foreach ( $child_reg as $reg ) {
					$reg_img    = get_field( 'region_img', get_term( $reg ) );
					$def_img    = get_template_directory_uri() . '/img/elm_munggae_huettae_winterwandern-350x350.jpg';
					$teaser_img = ( $reg_img ) ? $reg_img['sizes']['region-slider'] : $def_img;
					?>
					<div class="region_teaser_slider">
						<a href="<?php echo get_term_link( $reg ); ?>">
							<div class="single_region_teaser">
								<img class="" src="<?php echo $teaser_img; ?>">
								<div class="reg_name">
									<h3><?php echo get_term( $reg )->name; ?></h3>
								</div>
							</div>
						</a>
					</div>
				<?php } ?>
			<?php } ?>
		</div>
	</div>
<?php } ?>

<?php if ( $region_type == 'menu' ) { ?>
	<div class="nav_region">
		<div class="mainNav_region owl-carousel">
			<div class="mainNav_item">
				<?php if ( $termParent == '' ) { ?>
					<a href="<?php echo $wanderung_regionen_url; ?>"><?php echo $term_name; ?></a>
				<?php } else { ?>
					<a href="<?php echo $wanderung_regionen_url_parent; ?>"><?php echo $termParent->name; ?></a>
				<?php } ?>
			</div>
			<?php if ( $termParent != '' && $term_name != $termParent->name ) { ?>
				<div class="subNav_region active"><a class="2222" href="<?php echo $wanderung_regionen_url; ?>"><?php echo $term_name; ?></a></div>
			<?php } ?>
			<?php if ( ! empty( $child_reg ) ) { ?>
				<?php
				foreach ( $child_reg as $reg ) {
					$arr[] = array(
						'name' => get_term( $reg )->name,
						'id'   => get_term( $reg )->term_id,
					);
				}
				asort( $arr );

				foreach ( $arr as $reg ) {
					?>
					<?php if ( $reg['name'] != $term_name ) { ?>
						<div class="subNav_region"><a href="<?php echo get_term_link( $reg['id'] ); ?>"><?php echo $reg['name']; ?></a></div>
						<?php
					}
				}
				?>
			<?php } ?>
		</div>
	</div>
<?php } ?>

<?php
if ( $region_type == 'menulinks' ) {
	$verlinkungen_auswahlen = get_field( 'verlinkungen_auswahlen' );
	?>
	<div class="nav_region">
		<div class="mainNav_region owl-carousel">
			<?php foreach ( $verlinkungen_auswahlen as $menulinks ) { ?>
			<div class="subNav_region"><a
					href="<?php echo get_permalink( $menulinks->ID ); ?>"><?php echo $menulinks->post_title; ?></a></div>
			<?php } ?>
		</div>
	</div>
<?php } ?>

<?php
if ( $region_type == 'wander-slider' ) {
	$verlinkungen_auswahlen = get_field( 'verlinkungen_auswahlen' );
	if( isset($verlinkungen_auswahlen) && is_array($verlinkungen_auswahlen) ) {
		$all_count_ws              = count( $verlinkungen_auswahlen );
	} else {
		$all_count_ws              = 0;
	}
	$slider_button_text     = get_field( 'slider_button_text' );
	$slider_button_link     = get_field( 'slider_button_link' );
	$hide_icon_class        = ( $hide_icon ) ? 'hide' : '';
	?>
	<div class="container-fluid wander-tipps-outer">
		<div class="wander-in-region-wrapper">
			<div class="wander-in-region">
				<h3><?php echo $slider_titel; ?><span class="counter-in-region"><?php echo $all_count_ws; ?></span></h3>
				<h6><span class="small-heart <?php echo $hide_icon_class; ?>"></span><?php echo $slider_sub_titel; ?></h6>
			</div>
			<div class="owl-carousel owl-theme wander-in-region-carousel">
			<?php
			if ( ! empty( $verlinkungen_auswahlen ) ) {
				$i = 0;
				foreach ( $verlinkungen_auswahlen as $reg ) {
					$post_thumb          = get_the_post_thumbnail_url( $reg->ID, 'teaser-twocol' );
					$wanderregionen      = get_the_terms( $reg->ID, 'wanderregionen' );
					$wanderregionen_name = ( ! empty( $wanderregionen ) ) ? $wanderregionen[0]->name : 'Region';
					$wanderregionen_id   = ( ! empty( $wanderregionen ) ) ? $wanderregionen[0]->term_id : '';

					if ( $wanderregionen_id == $reg->ID ) {
						continue; }

					$kurzbeschrieb = ( get_field( 'kurzbeschrieb', $reg->ID ) ) ? get_field( 'kurzbeschrieb', $reg->ID ) : 'Fuga Nequam nos dolupta testinu llaceri ssequi nihilit, ut quissedia voluptassint prenimusam inum harchit imet am, aped mos volorio nsequos qui sundendestis aped mos volorio inum Onsequos et ...';
					$watchlisted_array = wegwandern_get_watchlist_hikes_list();
					if ( in_array( $reg->ID, $watchlisted_array, false ) ) {
						$watchlisted_class  = 'watchlisted';
						$watchlist_on_click = '';
					} else {
						$watchlisted_class  = '';
						$watchlist_on_click = ' onclick="addToWatchlist(this, ' . $reg->ID . ')" ';
					}
					?>

					<div class="single-wander-block">
						<div class="single-wander-img">
							<a href="<?php echo get_the_permalink( $reg->ID ); ?>">
								<img decoding="async" class="wander-img" src="<?php echo $post_thumb; ?>">
							</a>
							<div class="single-wander-heart <?php echo $watchlisted_class; ?>" <?php echo $watchlist_on_click; ?>></div>
							<div class="single-wander-map" onclick="openPopupMap(this)" data-hikeid="<?php echo $reg->ID; ?>">
							</div>
						</div>
						<div class='single-region-rating'>
							<h6 class='single-region'><?php echo $wanderregionen_name; ?></h6>
							<?php
							if ( is_plugin_active( 'wegwandern-summit-book/wegwandern-summit-book.php' ) ) {
								$average_rating = get_wanderung_average_rating( $reg->ID );
								?>
								<span class="average-rating-display"><?php echo $average_rating; ?><i class="fa fa-star"></i></span>
								<?php
							}
							?>
						</div>
						<h3><a href="<?php echo get_the_permalink( $reg->ID ); ?>"><?php echo $reg->post_title; ?></a></h3>
						<div class="wanderung-desc"><?php echo mb_strimwidth( $kurzbeschrieb, 0, 295, '...' ); ?></div>
					</div>

					<?php
					if ( $i == 12 ) {
						break; }
					$i++;
				}
			}
			?>
			</div>

			<a href="<?php echo $slider_button_link; ?>">
				<div class="wander-in-region-btn region-desktop"><?php echo $slider_button_text; ?></div>
			</a>
			<a href="<?php echo $slider_button_link; ?>">
				<div class="wander-in-region-btn region-tab"><?php echo $slider_button_text; ?></div>
			</a>
			<a href="<?php echo $slider_button_link; ?>">
				<div class="wander-in-region-btn region-mob"><?php echo $slider_button_text; ?></div>
			</a>
		</div>
	</div>

	<!-- Map Elevation Popup start -->
	<div class="region-hike-list-container">
		<div id="weg-map-popup">
			<div id="weg-map-popup-inner-wrapper">
				<div class="close_map" onclick="closeElement(this)"><span class="close_map_icon"></span></div>
				<div id="cesiumContainer" class="cesiumContainer"></div>
				<div class="map_currentLocation"></div>
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
				<div class="map_filter">
					<div class="map_filter_inner_wrapper">
						<div class="accordion"><?php echo __( 'Karteninformationen', 'wegwandern' ); ?></div>
						<div class="panel">
							<div class="fc_check_wrap">
								<label class="check_wrapper">
								<?php echo __( 'ÖV-Haltestellen', 'wegwandern' ); ?>
									<input type="checkbox" name="" id="transport_layer_checkbox" value="">
									<span class="redmark"></span>
								</label>
								<label class="check_wrapper">
								<?php echo __( 'Wanderwege', 'wegwandern' ); ?>
									<input type="checkbox" name="" id="hikes_trailing_layer" value="">
									<span class="redmark"></span>
								</label>
								<label class="check_wrapper">
								<?php echo __( 'Gesperrte Wanderwege', 'wegwandern' ); ?>
									<input type="checkbox" name="" id="closure_hikes_layer" value="">
									<span class="redmark"></span>
								</label>
								<label class="check_wrapper">
								<?php echo __( 'Schneehöhe ExoLabs', 'wegwandern' ); ?>
									<input type="checkbox" id="snow_depth_layer" name="" value="">
									<span class="redmark"></span>
									<div class="info_icon" onclick="infoIconClicked(event,'weg-map-popup')"></div>
								</label>
								<label class="check_wrapper">
								<?php echo __( 'Schneebedeckung ExoLabs', 'wegwandern' ); ?>
									<input type="checkbox" id="snow_cover_layer" name="" value="">
									<span class="redmark"></span>
								</label>
								<label class="check_wrapper">
								<?php echo __( 'Hangneigungen über 30°', 'wegwandern' ); ?>
									<input type="checkbox" id="slope_30_layer" name="" value="">
									<span class="redmark"></span>
								</label>
								<label class="check_wrapper">
								<?php echo __( 'Wildruhezonen', 'wegwandern' ); ?>
									<input type="checkbox" id="wildlife_layer" name="" value="">
									<span class="redmark"></span>
								</label>
								<label class="check_wrapper">
								<?php echo __( 'Wegpunkte WegWandern.ch', 'wegwandern' ); ?>
									<input type="checkbox" id="waypoints_layer" name="" value="">
									<span class="redmark"></span>
								</label>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="detailPgPopup">
				<div id="detailPgPopupContent"></div>
			</div>
			<div class="elevationGraph"></div>
			<div class="options" id="mapOptions"></div>
			<div class="snow_info_details hide">
				<div class="snow_inner_wrapper">
					<div class="snow_close_wrapper" onclick="infoIconClosed(event,'weg-map-popup')"><div class="snow_close"></div></div>
					<div class="snow_tile">Auf der Karte wird die Schneehöhe (in cm) mit den folgenden Farben angezeigt:</div>
					<div class="snow_image"></div>
					<a href="https://wegwandern.ch/schneekarten-wo-liegt-schnee" target="_blank"><div class="snow_link externalLink">Weitere Informationen</div></a>
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
							<div class="htmlpopup-container" id="tl-content-area">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<!-- Map Elevation Popup end -->
<?php } ?>

<?php if ( $region_type == 'icon-slider' ) { ?>

	<div class="region_icon_slider_container">
		<h6 class="region_icon_slider_sub__title"><?php echo $slider_sub_titel; ?></h6>
		<div class="region_icon_slider owl-carousel owl-theme">
		<?php
		if ( have_rows( 'slider_icon_title_repeater' ) ) :
			while ( have_rows( 'slider_icon_title_repeater' ) ) :
				the_row();
				$slider_icon       = get_sub_field( 'slider_icon' );
				$slider_icon_title = get_sub_field( 'slider_icon_title' );
				$slider_icon_url = get_sub_field( 'slider_icon_url' );

				if ( $slider_icon_url ) {
					$slider_icon_url_start = '<a href="' . $slider_icon_url . '">';
					$slider_icon_url_end = '</a>';
				} else {
					$slider_icon_url_start = '';
					$slider_icon_url_end = '';
				}

				echo $slider_icon_url_start . '<div class="region_icon_slider_item_wrapper"><img src="' . $slider_icon['url'] . '"><p>' . $slider_icon_title . '</p></div>' .$slider_icon_url_end;
				endwhile;
			endif;
		?>
		</div>
	</div>
<?php } ?>
