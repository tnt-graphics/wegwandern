<?php
/**
 * The template for displaying Wanderungen planen
 **/
function wanderung_region_slider() {
	global $post;
	$wanderung_id   = $post->ID;
	$wanderregionen = get_the_terms( $wanderung_id, 'wanderregionen' );
	$cat_link       = '';
	if ( ! empty( $wanderregionen ) ) {
		$term_id   = $wanderregionen[0]->term_id;
		$cat_link  = get_term_link( $term_id );
		$reg_array = array();
		foreach ( $wanderregionen as $reg ) {
			array_push( $reg_array, $reg->term_id );
		}

		$args = array(
			'post_type'      => 'wanderung',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'tax_query'      => array(
				array(
					'taxonomy' => 'wanderregionen',
					'field'    => 'term_id',
					'terms'    => $reg_array,
					'operator' => 'IN',
				),
			),
		);

		$all_reg       = get_posts( $args );
		$all_reg_count = count( $all_reg );

		if ( ! empty( $all_reg ) && $all_reg_count != 1 ) {
			$all_reg_count = $all_reg_count - 1;
			$i             = 1;
			?>

			<!-- Slider section starts here -->
			<div class="container-fluid">
				<div class="wander-in-region-wrapper">
					<div class="wander-in-region">
						<h3><?php echo __( 'Wanderungen in der Region', 'wegwandern' ); ?><span class="counter-in-region"><?php echo $all_reg_count; ?></span></h3>
						<h6><span class="small-heart"></span><?php echo __( 'Für deine Favoriten', 'wegwandern' ); ?></h6>
					</div>
					<div class="owl-carousel owl-theme wander-in-region-carousel">
					<?php
					foreach ( $all_reg as $reg ) {
						$post_thumb          = get_the_post_thumbnail_url( $reg->ID, 'teaser-twocol' );
						$wanderregionen      = get_the_terms( $reg->ID, 'wanderregionen' );
						$wanderregionen_name = ( ! empty( $wanderregionen ) ) ? $wanderregionen[0]->name : 'Region';
						$wanderregionen_id   = ( ! empty( $wanderregionen ) ) ? $wanderregionen[0]->term_id : '';
						if ( $wanderung_id == $reg->ID ) {
							continue;
						}
						$kurzbeschrieb = ( get_field( 'kurzbeschrieb', $reg->ID ) ) ? get_field( 'kurzbeschrieb', $reg->ID ) : 'Fuga Nequam nos dolupta testinu llaceri ssequi nihilit, ut quissedia voluptassint prenimusam inum harchit imet am, aped mos volorio nsequos qui sundendestis aped mos volorio inum Onsequos et ...';
						$watchlisted_array = wegwandern_get_watchlist_hikes_list();
						if ( in_array( $reg->ID, $watchlisted_array, false ) ) {
							$watchlisted_class  = 'watchlisted';
							$watchlist_on_click = '';
						} else {
							$watchlisted_class  = '';
							$watchlist_on_click = ' onclick="addToWatchlist(this, ' . $reg->ID . ')" ';
						}
						if ( is_plugin_active( 'wegwandern-summit-book/wegwandern-summit-book.php' ) ) {
							$average_rating = get_wanderung_average_rating( $reg->ID );
						}
						?>

						<div class="single-wander">
							<div class="single-wander-img">
								<a href="<?php echo get_the_permalink( $reg->ID ); ?>">
									<img decoding="async" class="wander-img" src="<?php echo $post_thumb; ?>">
								</a>
								<div class="single-wander-heart <?php echo $watchlisted_class; ?>" <?php echo $watchlist_on_click; ?>></div>
								<div class="single-wander-map" onclick="openPopupMapDetailPage(this)" data-hikeid="<?php echo $reg->ID; ?>"></div>
							</div>
							<div class='single-region-rating'>
								<h6 class='single-region'><?php echo $wanderregionen_name; ?></h6>
								<?php
								if ( is_plugin_active( 'wegwandern-summit-book/wegwandern-summit-book.php' ) ) {
									?>
									<span class="average-rating-display"><?php echo $average_rating; ?><i class="fa fa-star"></i></span>
									<?php
								}
								?>
							</div>
							<h3><a href="<?php echo get_the_permalink( $reg->ID ); ?>"><?php echo $reg->post_title; ?></a></h3>
							<div class="wanderung-desc"><?php echo mb_strimwidth( $kurzbeschrieb, 0, 295, '...' ); ?>
							</div>
						</div>

						<?php
						if ( $i == 12 ) {
							break;
						}
						$i++;
					}
					?>
				</div>

				<a href="<?php echo $cat_link; ?>"><div class="wander-in-region-btn region-desktop">Alle Wanderungen dieser Region</div></a>
				<a href="<?php echo $cat_link; ?>"><div class="wander-in-region-btn region-tab">Alle Wanderungen dieser Region</div></a>
				<a href="<?php echo $cat_link; ?>"><div class="wander-in-region-btn region-mob">Alle Wanderungen dieser Region</div></a>
			</div>
		</div>
			<?php
		}
	}
} ?>
