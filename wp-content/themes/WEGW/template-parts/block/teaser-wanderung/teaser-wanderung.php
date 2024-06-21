<?php
/**
 * Template for teaser wanderung
 */
$wanderung_layout = get_field( 'wanderung_layout' );
$choose_wanderung = get_field( 'choose_wanderung' );

if ( ! empty( $choose_wanderung ) ) {

	if ( $wanderung_layout == 'content_right' ) { ?>
		<div class="teaser-wanderung-wrap">
		<?php if ( get_field("titel") )  { ?>
				<h2><?php the_field("titel"); ?></h2>
			<?php } ?>
		<?php
		foreach ( $choose_wanderung as $wanderung ) {
			$wand_title          = $wanderung['sel_wanderung']->post_title;
			$wand_id             = $wanderung['sel_wanderung']->ID;
			$wand_url            = get_permalink( $wand_id );
			$wand_desc           = get_field( 'kurzbeschrieb', $wand_id );
			$wand_thumb          = get_the_post_thumbnail_url( $wand_id, 'hike-thumbnail' );
			$wanderregionen      = get_the_terms( $wand_id, 'wanderregionen' );
			$wanderregionen_name = ( ! empty( $wanderregionen ) ) ? $wanderregionen[0]->name : 'Region';
			$watchlisted_array   = wegwandern_get_watchlist_hikes_list();
			if ( in_array( $wand_id, $watchlisted_array, false ) ) {
				$watchlisted_class  = 'watchlisted';
				$watchlist_on_click = '';
			} else {
				$watchlisted_class  = '';
				$watchlist_on_click = ' onclick="addToWatchlist(this, ' . $wand_id . ')" ';
			}
			if ( is_plugin_active( 'wegwandern-summit-book/wegwandern-summit-book.php' ) ) {
				$average_rating = get_wanderung_average_rating( $wand_id );
			}
			?>
				<div class="teaser-wanderung">
					<div class="wand-img">
						<a href="<?php echo $wand_url; ?>"><img src="<?php echo $wand_thumb; ?>"></a>
						<div class="single-wander-heart <?php echo $watchlisted_class; ?>" <?php echo $watchlist_on_click; ?>></div>
					</div>
					<div class="wand-desc">
						<div class='wand-region single-region-rating'>
							<h6 class='single-region'><?php echo $wanderregionen_name; ?></h6>
							<?php
							if ( is_plugin_active( 'wegwandern-summit-book/wegwandern-summit-book.php' ) ) {
								?>
								<span class="average-rating-display"><?php echo $average_rating; ?><i class="fa fa-star"></i></span>
								<?php
							}
							?>
						</div>
						<a href="<?php echo $wand_url; ?>" class="wander-redirect"><h4><?php echo $wand_title; ?></h4></a>
						<p><?php echo $wand_desc; ?></p>
					</div>
				</div>
			<?php
		}
		?>
		</div>
		<?php
	} elseif ( $wanderung_layout == 'content_below' ) {

		foreach ( $choose_wanderung as $wanderung ) {
			$wand_title          = $wanderung['sel_wanderung']->post_title;
			$wand_id             = $wanderung['sel_wanderung']->ID;
			$wand_url            = get_permalink( $wand_id );
			$wand_desc           = get_field( 'kurzbeschrieb', $wand_id );
			$wand_thumb          = get_the_post_thumbnail_url( $wand_id, 'large' );
			$wanderregionen      = get_the_terms( $wand_id, 'wanderregionen' );
			$wanderregionen_name = ( ! empty( $wanderregionen ) ) ? $wanderregionen[0]->name : 'Region';
			$hike_time           = ( get_field( 'dauer', $wand_id ) ) ? wegwandern_formated_hiking_time_display(get_field( 'dauer', $wand_id )) : '';
			$hike_distance       = ( get_field( 'km', $wand_id ) ) ? get_field( 'km', $wand_id ) : '';
			$hike_ascent         = ( get_field( 'aufstieg', $wand_id ) ) ? get_field( 'aufstieg', $wand_id ) : '';
			$hike_descent        = ( get_field( 'abstieg', $wand_id ) ) ? get_field( 'abstieg', $wand_id ) : '';
			$hike_level          = get_the_terms( $wand_id, 'anforderung' );
			$hike_level_name     = ( ! empty( $hike_level ) ) ? $hike_level[0]->name : '';
			$hike_level_cls      = wegw_wandern_hike_level_class_name( $hike_level_name, $wand_id );
			$wander_saison_name = wegw_wandern_saison_name( $wand_id );
			?>
			<div class="teaser-single-wanderung-wrap">
				<div class="teaser-single-wanderung">
					<a href="<?php echo $wand_url; ?>"><img src="<?php echo $wand_thumb; ?>"></a>
				</div>
				<a href="<?php echo $wand_url; ?>" class="wander-redirect"><h3><?php echo $wand_title; ?></h3></a>
				<div class="detail-infobox">
					<div class="detail-hike-details">
					<?php if ( $hike_level_cls ) { ?>
						<div class="hike_level">
							<span class="<?php echo $hike_level_cls; ?>"></span>
							<p><?php echo $hike_level_name; ?></p>
						</div>
					<?php } ?>

					<?php if ( $hike_time ) { ?>
						<div class="hike_time">
							<span class="hike-time-icon"></span>
							<p><?php echo $hike_time; ?> h</p>
						</div>
					<?php } ?>

					<?php if ( $hike_distance ) { ?>
						<div class="hike_distance">
							<span class="hike-distance-icon"></span>
							<p><?php echo $hike_distance; ?> km</p>
						</div>
					<?php } ?> 

					<?php if ( $hike_ascent ) { ?>
						<div class="hike_ascent">
							<span class="hike-ascent-icon"></span>
							<p><?php echo $hike_ascent; ?> m</p>
						</div>
					<?php } ?>

					<?php if ( $hike_descent ) { ?>
						<div class="hike_descent">
							<span class="hike-descent-icon"></span>
							<p><?php echo $hike_descent; ?> m</p>
						</div>
					<?php } ?>

					<?php if ( $wander_saison_name ) { ?>
						<div class="hike_month">
							<span class="hike-month-icon"></span>
							<p><?php echo $wander_saison_name; ?></p>
						</div>
					<?php } ?>
				</div>
			</div>
			<p><?php echo $wand_desc; ?></p>
		</div>
		<?php }
	}
}
?>
