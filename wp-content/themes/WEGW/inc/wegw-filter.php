<?php
/**
 * Wanderung filter.
 **/
function wegw_filter_html() {
	$filter_title  = get_field( 'text', 'option' );
	$filter_fields = get_field( 'filter_fields', 'option' );
	$args          = array(
		'taxonomy'         => array( 'aktivitat' ),
		'hide_empty'       => false,
		'orderby'          => 'include',
		'parent'           => 0,
		'suppress_filters' => false,
	);
	$aktivitat     = get_terms( $args );

	$anforderung_args = array(
		'taxonomy'         => array( 'anforderung' ),
		'hide_empty'       => false,
		'orderby'          => 'include',
		'parent'           => 0,
		'suppress_filters' => false,
	);
	$anforderung      = get_terms( $anforderung_args );

	$wanderregionen_args = array(
		'taxonomy'         => array( 'wanderregionen' ),
		'hide_empty'       => false,
		'orderby'          => 'include',
		// 'parent'           => 0,
		'suppress_filters' => false,
	);

	/* Check if page URL - tourenportal */
	if ( is_page( 'tourenportal' ) || is_page( 'tourenportal-json' ) ) {
 		$wanderregionen_args['parent'] = 0;
	}
	
	$wanderregionen = get_terms( $wanderregionen_args );

	$angebote_args = array(
		'taxonomy'         => array( 'angebot' ),
		'hide_empty'       => false,
		'orderby'          => 'include',
		'parent'           => 0,
		'suppress_filters' => false,
	);
	$angebote      = get_terms( $angebote_args );

	$thema_args = array(
		'taxonomy'         => array( 'thema' ),
		'hide_empty'       => false,
		'orderby'          => 'include',
		'parent'           => 0,
		'suppress_filters' => false,
	);
	$thema      = get_terms( $thema_args );

	$routenverlauf_args = array(
		'taxonomy'         => array( 'routenverlauf' ),
		'hide_empty'       => false,
		'orderby'          => 'include',
		'parent'           => 0,
		'suppress_filters' => false,
	);
	$routenverlauf      = get_terms( $routenverlauf_args );

	$ausdauer_args = array(
		'taxonomy'         => array( 'ausdauer' ),
		'hide_empty'       => false,
		'orderby'          => 'include',
		'parent'           => 0,
		'suppress_filters' => false,
	);
	$ausdauer      = get_terms( $ausdauer_args );

	$wander_saison_args = array(
		'taxonomy'         => array( 'wander-saison' ),
		'hide_empty'       => false,
		'orderby'          => '',
		'parent'           => 0,
		'suppress_filters' => false,
	);
	$wander_saison      = get_terms( $wander_saison_args );

	$km       = get_km_option();
	$duration = get_dauer_option();
	$ascent   = get_aufstieg_option();
	$descent  = get_abstieg_option();
	$altitude = get_altitude_option();
	?>

	<div class="filterMenu filterWindow">
		<div  class="filter_title">
			<h2><?php echo $filter_title; ?></h2>
			<div class="close_warap"><span class="filter_close" onclick="closeFilter()"></span>   </div>     
		</div>
		<div class="filter_content_wrapper">
			<?php if ( in_array( 'activity', $filter_fields ) ) { ?>
			<div class="fc_heading">
				<h2><?php echo __( 'Aktivität', 'wegwandern' ); ?></h2>
				<?php if ( ! empty( $aktivitat ) ) { ?>
				<div class="fc_check_wrap">
					<?php
					$count = 0;
					foreach ( $aktivitat as $activity ) {
						$count++;
						$hide_from_filter = get_field( 'hide_from_filter', $activity );
						if ( $hide_from_filter ) {
							continue;
						}
						?>
					<label class="check_wrapper"><?php echo $activity->name; ?>
						<input type="checkbox" value="<?php echo $activity->term_id; ?>" class="activity_search activity_type_<?php echo $count; ?>">
						<span class="redmark"></span>
					</label>
					<?php } ?>		
				</div>
				<?php } ?>
			</div>
			<?php } ?>

			<?php if ( in_array( 'difficulty', $filter_fields ) ) { ?>
			<div class="fc_heading fc_diff_level fc_devel_default">
				<h2><?php echo __( 'Schwierigkeitsgrad', 'wegwandern' ); ?></h2>
				<?php
				if ( ! empty( $anforderung ) ) {
					$d = 1;
					?>
				<div class="fc_block_select_wrapper">
					<?php
					foreach ( $anforderung as $difficulty ) {
						$hide_from_filter = get_field( 'hide_from_filter', $difficulty );
						if ( $hide_from_filter ) {
							continue;
						}
						$act_class          = '';
						$block_class        = '';
						$difficulty_t_array = array( 28, 29, 30, 31, 32, 33 );
						if ( in_array( $difficulty->term_id, $difficulty_t_array ) ) {
							$block_class = 'fc_difficult_t_block';
						} else {
							$block_class = 'fc_difficult_wt_block';
						}
						?>
					<div class="fc_block_select <?php echo $block_class; ?> <?php echo $act_class; ?>">
						<label class=""><input type="checkbox" name="difficulty[]" class="difficulty_search" value="<?php echo $difficulty->term_id; ?>">
							<p><?php echo $difficulty->name; ?></p>
						</label>
					</div>
						<?php
						$d++;
					}
					?>
				</div>
				<?php } ?>
			</div>
			<?php } ?>

			<?php if ( in_array( 'duration', $filter_fields ) ) { ?>
				<div class="fc_heading">
					<h2>Dauer</h2> 
					<?php echo $duration; ?> 
				</div>
			<?php } ?>

			<?php if ( in_array( 'distance', $filter_fields ) ) { ?>
				<div class="fc_heading">
					<h2>Kilometer</h2>
					<?php echo $km; ?>
				</div>
			<?php } ?>

			<?php if ( in_array( 'ascent', $filter_fields ) ) { ?>
				<div class="fc_heading">
					<h2>Aufstieg</h2>
					<?php echo $ascent; ?>	
				</div>
			<?php } ?>

			<?php if ( in_array( 'descent', $filter_fields ) ) { ?>
				<div class="fc_heading fc_last">
					<h2>Abstieg</h2>
					<?php echo $descent; ?>
				</div>
			<?php } ?>
			<hr>

			<?php if ( in_array( 'region', $filter_fields ) ) { ?>
				<div class="accordion wanderregionen_accordion"><?php echo __( 'Wanderregion', 'wegwandern' ); ?></div> 
				<?php if ( ! empty( $wanderregionen ) ) { ?>           
				<div class="panel">              
					<div class="fc_check_wrap">
					<?php
					foreach ( $wanderregionen as $regionen ) {
						$regionen_child_class = ($regionen->parent != 0) ? "hide" : "";
						
						$hide_from_filter = get_field( 'hide_from_filter', $regionen );
						if ( $hide_from_filter ) {
							continue;
						}
						?>
						<label class="check_wrapper <?php echo $regionen_child_class; ?>"><?php echo $regionen->name; ?>
							<input type="checkbox" name="wanderregionen[]" class="wanderregionen_search" value="<?php echo $regionen->term_id; ?>">
							<span class="redmark"></span>
						</label>
					<?php } ?>
					</div>     
				</div>
				<?php } ?>
				<hr class="wanderregionen_hr">
			<?php } ?>

			<?php if ( in_array( 'angebot', $filter_fields ) ) { ?>
				<div class="accordion"><?php echo __( 'Angebote', 'wegwandern' ); ?></div>      
				<?php if ( ! empty( $angebote ) ) { ?>        
				<div class="panel">
					<div class="fc_check_wrap">
					<?php
					foreach ( $angebote as $angebote ) {
							$hide_from_filter = get_field( 'hide_from_filter', $angebote );
						if ( $hide_from_filter ) {
							continue;
						}
						?>
						<label class="check_wrapper"><?php echo $angebote->name; ?>
							<input type="checkbox" name="angebote[]" class="angebote_search" value="<?php echo $angebote->term_id; ?>">
							<span class="redmark"></span>
						</label>
						<?php } ?>
					</div>
				</div>
				<?php } ?>
				<hr>
			<?php } ?>

			<?php if ( in_array( 'thema', $filter_fields ) ) { ?>
			<div class="accordion"><?php echo __( 'Thema', 'wegwandern' ); ?></div> 
				<?php if ( ! empty( $thema ) ) { ?> 
				<div class="panel">              
					<div class="fc_check_wrap">
					<?php
					foreach ( $thema as $thema ) {
						$hide_from_filter = get_field( 'hide_from_filter', $thema );
						if ( $hide_from_filter ) {
							continue;
						}
						?>
						<label class="check_wrapper"><?php echo $thema->name; ?>
							<input type="checkbox" name="thema[]" class="thema_search" value="<?php echo $thema->term_id; ?>">
							<span class="redmark"></span>
						</label>
					<?php } ?>	
					</div>      
				</div>
				<?php } ?>
				<hr>
			<?php } ?>

			<?php if ( in_array( 'itinerary', $filter_fields ) ) { ?>
			<div class="accordion"> <?php echo __( 'Routenverlauf', 'wegwandern' ); ?></div>   
				<?php if ( ! empty( $routenverlauf ) ) { ?>          
				<div class="panel">
					<div class="fc_check_wrap">
					<?php
					foreach ( $routenverlauf as $routenverlauf ) {
						$hide_from_filter = get_field( 'hide_from_filter', $routenverlauf );
						if ( $hide_from_filter ) {
							continue;
						}
						?>
						<label class="check_wrapper"><?php echo $routenverlauf->name; ?>
							<input type="checkbox" name="routenverlauf[]" class="routenverlauf_search" value="<?php echo $routenverlauf->term_id; ?>">
							<span class="redmark"></span>
						</label>	
					<?php } ?>	
					</div>
				</div>
				<?php } ?>
			<hr>
			<?php } ?>

			<?php if ( in_array( 'ausdauer', $filter_fields ) ) { ?>
				<div class="accordion"><?php echo __( 'Ausdauer', 'wegwandern' ); ?></div>    
				<?php if ( ! empty( $ausdauer ) ) { ?>          
				<div class="panel">              	
					<div class="fc_check_wrap">
					<?php
					foreach ( $ausdauer as $ausdauer ) {
						$hide_from_filter = get_field( 'hide_from_filter', $ausdauer );
						if ( $hide_from_filter ) {
							continue;
						}
						?>
						<label class="check_wrapper"><?php echo $ausdauer->name; ?>
							<input type="checkbox" name="ausdauer[]" class="ausdauer_search" value="<?php echo $ausdauer->term_id; ?>">
							<span class="redmark"></span>
						</label>
					<?php } ?>	
					</div>           
				</div>
				<?php } ?>
				<hr>
			<?php } ?>

			<?php if ( in_array( 'lowest-highest', $filter_fields ) ) { ?>
				<div class="accordion">Tiefster / höchster Punkt</div>            
				<div class="panel">
					<?php echo $altitude; ?>
				</div>
				<hr>
			<?php } ?>

			<?php if ( in_array( 'months', $filter_fields ) ) { ?>
				<div class="accordion"><?php echo __( 'Nach Monaten', 'wegwandern' ); ?></div>     
				<?php
				if ( ! empty( $wander_saison ) ) {
					$ws = 1;
					?>
					<div class="panel">
						<div class="fc_block_month">
							<div class="fc_block_select_wrapper">
							<?php
							foreach ( $wander_saison as $saison ) {
								$hide_from_filter = get_field( 'hide_from_filter', $saison );
								if ( $hide_from_filter ) {
									continue;
								}
									$act_class = '';

								?>
								<div class="fc_block_select <?php echo $act_class; ?>">
									<label class=""><input type="checkbox" name="wander_saison[]" class="wander_saison_search" value="<?php echo $saison->term_id; ?>">
										<p> <?php echo $saison->name; ?></p>
									</label>
								</div>	
								<?php
								$ws++;
							}
							?>
						</div>    
					</div>
				<?php } ?>
				</div>
				<hr>
			<?php } ?>

			<?php if ( in_array( 'valuation', $filter_fields ) ) { ?>
				<div class="accordion">Bewertung</div>            
				<div class="panel">             	
					<div class="fc_check_wrap_star">
						<label class="check_wrapper">min
							<input type="checkbox">
							<span class="redmark"></span>
						</label>
						<label class="check_wrapper">min
							<input type="checkbox">
							<span class="redmark"></span>
						</label>	
						<label class="check_wrapper">min
							<input type="checkbox">
							<span class="redmark"></span>
						</label>	
					</div> 
				</div>
				<hr>
			<?php } ?>

			<div class="filter_reset"><a>Filter zurücksetzen</a></div>	
		</div>
		
		<div class="filter_result_count" id="wegw_map_filter_btn">
			<?php
			/* Get total published hikes count */
			$wegwandern_hikes_count = wp_count_posts('wanderung');
			
			if ( $wegwandern_hikes_count ) {
				$wegwandern_published_hikes_count = $wegwandern_hikes_count->publish;
			} else {
				$wegwandern_published_hikes_count = '600';
			}
			?>
			<p>
				<b>
					<span id="loader-icon-filter" class="hide"></span>
					<span class="wegw_filtered_result_count"><?php echo $wegwandern_published_hikes_count; ?></span>
				</b>
				&nbsp;Wanderungen anzeigen
			</p>
		</div>
	</div>

<?php } ?>
