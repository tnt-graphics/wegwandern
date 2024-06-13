<?php
get_header();
b2b_user_menu_callback();
wegwb_b2b_check_user_role_access();

$user_ID = get_current_user_id();
$loop    = new WP_Query(
	array(
		'author__in'     => array( $user_ID ),
		'post_type'      => 'b2b-werbung',
		'posts_per_page' => -1,
		'post_status'    => array( 'publish', 'pending', 'draft', 'future' ),
	)
);
?>
<div class="container">
	<div class="b2b-statusPage">
		<h3>Status eingereichte Angebote</h3>
	
		<!-- Ad Delete Popup -->
		<div class="deleteConfirm hide">
			<div class="confirm_wrap"> 
				<div class="close_warap"><span class="filter_close" onclick="closeDeletePopup()"></span></div>
				<h4>Sind Sie sicher, dass dieses Löschen wollen?</h4>
				<div class="confirmDeleteBtn">
					<button id="b2b_ad_confirm_delete" data-id="">Ja</button><button onclick="closeDeletePopup()">Nein</button>
				</div>
				<div id="delete_ad_response"></div>
			</div>
		</div>
		<?php if ( $loop->have_posts() ) : ?>
			<div class="b2b-status-list">
			<?php
			while ( $loop->have_posts() ) :
				$loop->the_post();

				$ad_ID                      = get_the_ID();
				$current_post_status        = get_post_status();
				$b2b_user_available_credits = wegwb_b2b_user_ads_credits_balance();
				$post_date                  = get_the_time( 'd.m.Y', $ad_ID );

				$get_b2b_ad_image                 = get_post_meta( $ad_ID, 'wegw_b2b_ad_image', true );
				// $get_wegw_b2b_ad_main_title       = get_post_meta( $ad_ID, 'wegw_b2b_ad_main_title', true );
				$get_wegw_b2b_ad_bold_text        = get_post_meta( $ad_ID, 'wegw_b2b_ad_bold_text', true );
				$get_wegw_b2b_ad_link             = get_post_meta( $ad_ID, 'wegw_b2b_ad_link', true );
				$get_wegw_b2b_ad_credits_end      = get_post_meta( $ad_ID, 'wegw_b2b_ad_credits_end', true );
				$get_wegw_b2b_ad_credits_end_date = get_post_meta( $ad_ID, 'wegw_b2b_ad_credits_end_date', true );

				/* Check if image URL is valid */
				if ( @getimagesize( $get_b2b_ad_image ) ) {
					$b2b_ad_image = $get_b2b_ad_image;
				} else {
					$b2b_ad_image = 'https://wegwdevelop.dev.displayme.net/wp-content/uploads/2023/05/dornach_arlesheim_ermitage_gempenturm_schartenflue_schlosshof_ruine_dorneck_wanderung.jpg';
				}

				// $b2b_ad_main_title       = isset( $get_wegw_b2b_ad_main_title ) ? $get_wegw_b2b_ad_main_title : '';
				$b2b_ad_bold_text        = isset( $get_wegw_b2b_ad_bold_text ) ? $get_wegw_b2b_ad_bold_text : '';
				$b2b_ad_credits_end      = isset( $get_wegw_b2b_ad_credits_end ) ? $get_wegw_b2b_ad_credits_end : 0;
				$b2b_ad_credits_end_date = isset( $get_wegw_b2b_ad_credits_end_date ) ? $get_wegw_b2b_ad_credits_end_date : 0;
				if ( $b2b_ad_credits_end_date != '' ) {
					$b2b_ad_credits_end_date_stamp    = strtotime( $b2b_ad_credits_end_date );
					$b2b_ad_credits_end_date_formated = date( 'd.m.Y', $b2b_ad_credits_end_date_stamp );
				} else {
					$b2b_ad_credits_end_date_formated = '';
				}


				$b2b_ad_link = isset( $get_wegw_b2b_ad_link ) ? $get_wegw_b2b_ad_link : '';
				if ( $b2b_ad_link != '' ) {
					$display_link = preg_replace( '(^https?://)', '', $b2b_ad_link );
				} else {
					$display_link = '';
				}

				$b2b_ad_edit_option    = true;
				$b2b_ad_delete_option  = false;
				$b2b_ad_status_class   = '';
				$b2b_ad_status_display = '';

				if ( $b2b_ad_credits_end == '1' ) {
					$b2b_ad_delete_option  = true;
					$b2b_ad_status_class   = 'b2b_ended_item';
					$b2b_ad_status_display = '<div class="b2b-status-btn"><i class="fas fa-times icon_white"></i><div class="b2b_ended_Status ">Beendet am ' . $b2b_ad_credits_end_date_formated . '</div></div>';
				} else {
					if ( $current_post_status == 'publish' || $current_post_status == 'future' ) {
						$b2b_ad_edit_option    = false;
						$b2b_ad_status_class   = 'b2b_Published_item';
						$b2b_ad_status_display = '<div class="b2b-status-btn"><i class="fas fa-check icon_white"></i><div class="b2b_Published_Status">Veröffentlicht am ' . $post_date . '</div></div>';
					} elseif ( $current_post_status == 'pending' ) {
						$b2b_ad_edit_option    = false;
						$b2b_ad_status_class   = 'b2b_Verification_item';
						$b2b_ad_status_display = '<div class="b2b-status-btn"><i class="fas fa-eye icon_white"></i><div class="b2b_Verification_Status">In Prüfung</div></div>';
					} else {
						$b2b_ad_delete_option  = true;
						$b2b_ad_status_class   = 'b2b_Verification_item';
						$ad_previous_status    = get_post_meta( $ad_ID, 'ad_previous_status', true );
						$status_html = "";
						if( $ad_previous_status && $ad_previous_status === 'rejected' ) {
							$status_html = " (" . __("Abgelehnt", "wegw-b2b") . ")";
						}
						$b2b_ad_status_display = '<div class="b2b-status-btn b2b-status-btn-draft""><span>DRAFT</span><div class="b2b_Draft_Status">(Entwurf)' . $status_html . '</div></div>';
					}
				}
				?>

				<div class="b2b-item-wrap">
					<div class="b2b-item <?php echo $b2b_ad_status_class; ?>">

						<?php if ( $b2b_ad_delete_option ) { ?>
							<div class="deleteWrap" onclick="deleteItem(<?php echo $ad_ID; ?>)">
								<div class="delete"></div>
							</div>
						<?php } ?>	

						<?php
						if ( $b2b_ad_edit_option ) {
							$b2b_ad_edit_url = site_url( WEGW_B2B_AD_CREATE . '/edit/' . $ad_ID );
							?>
							<a href="<?php echo $b2b_ad_edit_url; ?>"><div class="edit"></div></a>
						<?php } ?>

						<div class="b2b-item-sec">
							<div class="b2b-tem-img">
								<img src="<?php echo $b2b_ad_image; ?>" alt="">
							</div>
							<div class="b2b-item-content">
								<!-- <h6><?php // echo $b2b_ad_main_title; ?></h6> -->
								<h5><?php the_title(); ?> </h5>
								<!-- <p><?php // echo wp_trim_words( get_the_content(), 20, '...' ); ?> <b><?php // echo wp_trim_words( $b2b_ad_bold_text, 5, '...' ); ?></b></p> -->
								<p><?php echo get_the_content(); ?> <b><?php echo $b2b_ad_bold_text; ?></b></p>
								<a href="<?php echo $b2b_ad_link; ?>" target="_blank"><span></span><?php echo $display_link; ?></a>
							</div>
						</div>
						<?php echo $b2b_ad_status_display; ?>
					</div>
				</div>

				<?php
			endwhile;
			wp_reset_postdata();
			?>
			</div>

			<div class="b2b_status_view">
				<div class="b2b_status_view_left_col">
					<div class="b2b_status_view_verify"><i class="fas fa-eye icon_red"></i> = In Prüfung</div>
					<div class="b2b_status_view_publish"><i class="fas fa-check icon_green"></i> = Publikationsdatum</div>
					<div class="b2b_status_view_end"><i class="fas fa-times icon_black"></i> = Datum Angebotsende</div>
					</div>
				<div>
					<div class="b2b_bal_clciks">Ihr Klick-Guthaben = <span><?php echo $b2b_user_available_credits; ?></span></div>
				</div>
				
			</div>
			
			<div id="customTable" class="hide">
				<div class="close_warap"><span class="filter_close" onclick="closetable()"></span></div>
				<h4><?php echo __( 'Klickstatistik', 'wegw-b2b' ); ?></h4>
				<div class="ad_clicks_timeline_popup_wrap">
					<table border="1" id="ad_clicks_timeline_popup">
						<tr>
							<td>Date</td>
							<td>01.07.2023</td>
							<td>02.07.2023</td>
							<td>03.07.2023</td>
							<td>04.07.2023</td>
							<td>05.07.2023</td>
							<td>06.07.2023</td>
							<td>07.07.2023</td>
							<td>08.07.2023</td>
							<td>09.07.2023</td>
							<td>10.07.2023</td>
						</tr>
						<tr>
							<td>Count</td>
							<td>7</td>
							<td>2</td>
							<td>3</td>
							<td>0</td>
							<td>20</td>
							<td>62</td>
							<td>73</td>
							<td>8</td>
							<td>9</td>
							<td>10</td>
						</tr>
					</table>
				</div>
			</div>
			<table class="b2b-table-list">
				<thead>
					<tr>
						<th>Angebots-Titel</th>
						<th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Status: activate to sort column ascending" data-order="1596758400" data-sort-type="numeric">Status</th>
						<th class="sorting" tabindex="0" rowspan="1" colspan="1" data-sort-type="numeric">Gebuchte Enddatum</th>
						<th onclick="sortTable(0)">Erfolgte Klicks</th>
						<th onclick="sortTable(1)">Gebuchte Klicks</th>
						<!-- <th onclick="sortTable(2)">Klicks in CHF</th> -->
						<!-- <th>Bezahl-status</th> -->
						<th>Erneut Einreichen /<br> Bearbeiten / Löschen</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$b2b_ads_listing_loop = new WP_Query(
						array(
							'author__in'     => array( $user_ID ),
							'post_type'      => 'b2b-werbung',
							'posts_per_page' => -1,
							'post_status'    => array( 'publish', 'pending', 'draft', 'future' ),
						)
					);

					if ( $b2b_ads_listing_loop->have_posts() ) :
						while ( $b2b_ads_listing_loop->have_posts() ) :
							$b2b_ads_listing_loop->the_post();

							$ad_ID                = get_the_ID();
							$current_post_status  = get_post_status();
							$post_date            = get_the_time( 'd.m.Y', $ad_ID );
							$clicks_till_date     = wegwb_b2b_ads_clicks_count( $ad_ID );
							$display_clicks_popup = ( $clicks_till_date == 0 ) ? '' : 'onclick="kicksPopup(' . $ad_ID . ')"';

							$get_wegw_b2b_ad_credits_end = get_post_meta( $ad_ID, 'wegw_b2b_ad_credits_end', true );
							$b2b_ad_credits_end          = isset( $get_wegw_b2b_ad_credits_end ) ? $get_wegw_b2b_ad_credits_end : 0;

							$get_wegw_b2b_ad_end_date = get_post_meta( $ad_ID, 'wegw_b2b_ad_end_date', true );
							$b2b_ad_credits_end_date  = '';
							if ( isset( $get_wegw_b2b_ad_end_date ) && $get_wegw_b2b_ad_end_date != '' ) {
								$b2b_ad_credits_end_date = date( 'd.m.Y', strtotime( $get_wegw_b2b_ad_end_date ) );
							}

							$b2b_credits_booked = metadata_exists( 'post', $ad_ID, 'wegw_b2b_credits_booked' ) ? get_post_meta( $ad_ID, 'wegw_b2b_credits_booked', true ) : 0;

							$b2b_ad_edit_option   = true;
							$b2b_ad_delete_option = false;
							$b2b_ad_status_icon   = '';

							if ( $b2b_ad_credits_end == '1' ) {
								$b2b_ad_delete_option = true;
								$b2b_ad_status_icon   = '<i class="fas fa-times icon_black"></i>';
							} else {
								if ( $current_post_status == 'publish' || $current_post_status == 'future' ) {
									$b2b_ad_edit_option = false;
									$b2b_ad_status_icon = '<i class="fas fa-check icon_green"></i>';
								} elseif ( $current_post_status == 'pending' ) {
									$b2b_ad_edit_option = false;
									$b2b_ad_status_icon = '<i class="fas fa-eye icon_red"></i>';
								} else {
									$b2b_ad_delete_option = true;
									$b2b_ad_status_icon   = '<i class="fas fa-edit icon_red"></i>';
								}
							}
							?>
							<tr>
								<td><?php the_title(); ?></td>
								<td class="b2b_listing_status_wrap"><?php echo $b2b_ad_status_icon; ?> <?php echo $post_date; ?></td>
								<td class="b2b_listing_status_wrap"><?php echo $b2b_ad_credits_end_date; ?></td>
								<td <?php echo $display_clicks_popup; ?>><?php echo $clicks_till_date; ?></td>
								<td><?php echo $b2b_credits_booked; ?></td>
								<!-- <td>100</td> -->
								<!-- <td><i class="fas fa-check icon_green"></i></td> -->
								<td>
									<?php
									if ( $b2b_ad_edit_option ) {
										$b2b_ad_edit_url = site_url( WEGW_B2B_AD_CREATE . '/edit/' . $ad_ID );
										?>
										<a href="<?php echo $b2b_ad_edit_url; ?>"><div class="editIcon"></div></a>
									<?php } ?>

									<?php if ( $b2b_ad_delete_option ) { ?>
										<div class="b2b_listing_table_delete_wrap">
											<div class="deleteWrapIcon" onclick="deleteItem(<?php echo $ad_ID; ?>)">
												<div class="deleteIcon"></div>
											</div>
										</div>
									<?php } ?>
								</td>
							</tr>
							<?php
						endwhile;
					endif;
					wp_reset_postdata();
					?>
				</tbody>
			</table>
			<?php
		else :
			echo '<p>' . __( 'Keine Werbung', 'wegw-b2b' ) . '</p>';
		endif;
		?>

	</div>
</div>

<?php get_footer(); ?>
