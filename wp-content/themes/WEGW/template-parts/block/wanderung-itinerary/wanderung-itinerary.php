<?php
/**
 * Wanderung Itinerary
 */
$wegw_choose_section = get_field( 'wegw_choose_section' );

global $post;
$wanderung_id = $post->ID;

if ( $wegw_choose_section ) {

	if ( in_array( 'routenverlauf', $wegw_choose_section ) ) {
		?>
	<div class="accordion single-page-accord">
		<?php echo esc_html__( 'Routenverlauf', 'wegwandern' ); ?>
	</div>
	<div class="panel single-page-accord acc_1">
		<div class="">
			<div class="timeline">
				<?php
				if ( have_rows( 'itinerary_details' ) ) :
					$numrows = count( get_field( 'itinerary_details' ) );
					$i       = 1;
					?>
					<ul>
						<?php
						while ( have_rows( 'itinerary_details' ) ) :
							the_row();
							$li_class = '';
							if ( $i == 1 ) {
								$li_class = 'first';
							} elseif ( $i == $numrows ) {
								$li_class = 'last';
							}
							?>
							<li class="timeline_item <?php echo $li_class; ?>">
								<div class="title">
									<h3><?php echo get_sub_field( 'itinerary_details_std' ); ?></h3>
								</div>
								<div class="point"></div>
								<div class="content">
									<p><b><?php echo get_sub_field( 'itinerary_details_titel' ); ?> </b><br><?php echo get_sub_field( 'itinerary_details_mum' ); ?> m.ü.M.</p>
									<?php if ( have_rows( 'itinerary_icons' ) ) : ?>
										<ul class='icon_wrapper'>
											<?php
											while ( have_rows( 'itinerary_icons' ) ) :
												the_row();
												$itinerary_icon = get_sub_field( 'icon' );
												?>
												<li>
													<?php
													$itinerary_link = get_sub_field( 'link' );
													if($itinerary_link != '') {
														?>
														<a href="<?php echo $itinerary_link; ?>" target="_blank"><img src="<?php echo $itinerary_icon['url']; ?>"></a>
														<?php
													} else {
														?>
														<img src="<?php echo $itinerary_icon['url']; ?>">
														<?php
													}
													?>
												</li>
											<?php endwhile; ?>
										</ul>
									<?php endif; ?>
								</div>
							</li>
							<?php
							$i++;
					endwhile;
						?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php } ?>

	<?php
	if ( in_array( 'technisch_daten', $wegw_choose_section ) ) {
		$season_array    = array();
		$allseason       = array();
		$hike_level      = get_the_terms( $wanderung_id, 'anforderung' );
		$hike_level_name = ( ! empty( $hike_level ) ) ? $hike_level[0]->name : '';

		$hike_level_cls = wegw_wandern_hike_level_class_name( $hike_level_name, $wanderung_id );

		$hike_time      = ( get_field( 'dauer', $wanderung_id ) ) ? wegwandern_formated_hiking_time_display(get_field( 'dauer', $wanderung_id )) : '';
		$hike_distance  = ( get_field( 'km', $wanderung_id ) ) ? get_field( 'km', $wanderung_id ) : '';
		$hike_ascent    = ( get_field( 'aufstieg', $wanderung_id ) ) ? get_field( 'aufstieg', $wanderung_id ) : '';
		$hike_descent   = ( get_field( 'abstieg', $wanderung_id ) ) ? get_field( 'abstieg', $wanderung_id ) : '';
		$tiefster_punkt = ( get_field( 'tiefster_punkt', $wanderung_id ) ) ? get_field( 'tiefster_punkt', $wanderung_id ) : '';
		$hochster_punkt = ( get_field( 'hochster_punkt', $wanderung_id ) ) ? get_field( 'hochster_punkt', $wanderung_id ) : '';
		$ausdauer       = get_the_terms( $wanderung_id, 'ausdauer' );
		$ausdauer_name  = ( ! empty( $ausdauer ) ) ? $ausdauer[0]->name : '';
		$wander_saison  = get_the_terms( $wanderung_id, 'wander-saison' );

		if ( ! empty( $wander_saison ) ) {
			foreach ( $wander_saison as $saison ) {
				array_push( $season_array, $saison->name );
			}
		}

		$wander_saison_args = array(
			'taxonomy'         => array( 'wander-saison' ),
			'hide_empty'       => false,
			'orderby'          => 'ID',
			'parent'           => 0,
			'suppress_filters' => false,
		);

		$allwander_saison = get_terms( $wander_saison_args );
		foreach ( $allwander_saison as $saison ) {
			array_push( $allseason, $saison->name );
		}
		?>
		<div class="accordion single-page-accord"><?php echo esc_html__( 'Technische Daten', 'wegwandern' ); ?></div>
			<div class="panel single-page-accord">
				<div class="techItem-wrapper">
					<ul>
						<li class="techItem">
							<p><?php echo esc_html__( 'Anforderung', 'wegwandern' ); ?></p>
							<div class="hike-detail-wrap"><span class="<?php echo $hike_level_cls; ?>"></span>
							<?php if ( $hike_level_name ) { ?>
							<p class="hike_name"><?php echo $hike_level_name; ?></p>
							<?php } ?>
						</div>
						</li>

						<?php if ( $ausdauer_name ) { ?>
						<li class="techItem">
							<p><?php echo esc_html__( 'Körperliche Anforderung', 'wegwandern' ); ?></p>
							<p><?php echo $ausdauer_name; ?></p>
						</li>
						<?php } ?>

						<?php if ( $hike_time ) { ?>
						<li class="techItem">
							<p><?php echo esc_html__( 'Dauer', 'wegwandern' ); ?></p>
							<p><?php echo $hike_time; ?> h</p>
						</li>
						<?php } ?>

						<?php if ( $hike_distance ) { ?>
						<li class="techItem">
							<p><?php echo esc_html__( 'Distanz', 'wegwandern' ); ?></p>
							<p><?php echo round( $hike_distance, 1 ); ?> km</p>
						</li>
						<?php } ?>

						<?php if ( $hike_ascent ) { ?>
						<li class="techItem">
							<p><?php echo esc_html__( 'Aufstieg', 'wegwandern' ); ?></p>
							<p><?php echo $hike_ascent; ?> m</p>
						</li>
						<?php } ?>

						<?php if ( $hike_descent ) { ?>
						<li class="techItem">
							<p><?php echo esc_html__( 'Abstieg', 'wegwandern' ); ?></p>
							<p><?php echo $hike_descent; ?> m</p>
						</li>
						<?php } ?>

						<?php if ( $tiefster_punkt ) { ?>
						<li class="techItem">
							<p><?php echo esc_html__( 'Tiefster Punkt', 'wegwandern' ); ?></p>
							<p><?php echo round( $tiefster_punkt, 0 ); ?> m</p>
						</li>
						<?php } ?>

						<?php if ( $hochster_punkt ) { ?>
						<li class="techItem">
							<p><?php echo esc_html__( 'Höchster Punkt', 'wegwandern' ); ?></p>
							<p><?php echo round( $hochster_punkt, 0 ); ?> m</p>
						</li>
						<?php } ?>

						<?php if ( ! empty( $allseason ) ) { ?>
						<li class="techItem">
							<p><?php echo esc_html__( 'Beste Jahreszeit', 'wegwandern' ); ?></p>
							<div class="techItem_month">
							<?php
							foreach ( $allseason as $all_season ) {
								$classname = '';
								if ( in_array( $all_season, $season_array ) ) {
									$classname = 'active';
								}
								?>
								<div class="techItem_select ">
									<label class="<?php echo $classname; ?>"><input type="checkbox" name="" class="" value="">
										<p> <?php echo $all_season; ?></p>
									</label>
								</div>
							<?php } ?>
							</div>
						</li>
					<?php } ?>
				</ul>
			</div>
		</div>
	<?php } ?>
<?php } ?>
