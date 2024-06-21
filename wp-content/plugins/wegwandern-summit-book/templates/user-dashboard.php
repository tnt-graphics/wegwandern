<?php
/**
 * Template used for Summit Book Dashboard page
 *
 * @package wegwandern-summit-book
 */

get_header();
global $current_user;
?>
<div class='summit-book-user-navigation'>
	<?php echo do_shortcode( '[display-summit-book-user-menu]' ); ?>
	</div>
<div class='user-dashboard-page container'>
<?php
echo get_breadcrumb();
if ( get_field( 'b2b_holiday', 'option' ) ) {
	$b2b_holiday_content_title       = esc_html( get_field( 'b2b_holiday_content_title', 'option' ) );
	$b2b_holiday_content_description = get_field( 'b2b_holiday_content_description', 'option' );
	?>
	<div class="wegwandern-holiday-msg">
		<div class="holiday_info">
			<div class="holiday_info_title"><?php echo $b2b_holiday_content_title; ?></div>
			<div class="holiday_info_content"> <?php echo $b2b_holiday_content_description; ?></div>
		</div>
	</div>
	<?php } ?>
	<div class="user-watchlists-section">
		<h2><?php echo esc_attr_e( 'Meine Merkliste', 'wegwandern-summit-book' ); ?></h2>
		<?php
		$watchlists = get_user_meta( $current_user->ID, 'watchlist' );
		if ( ! empty( $watchlists ) ) {
			$watchlists = array_reverse( $watchlists );
			?>
			<div class="user-watchlists">
				<?php
				$args              = array(
					'post_type'   => 'wanderung',
					'post__in'    => array_map( 'intval', $watchlists ),
					'orderby'     => 'post__in',
					'numberposts' => -1,
				);
				$watchlisted_hikes = get_posts( $args );
				$posts_html        = '';
				?>
				<div class='single-wander-wrappe'>
					<?php
					foreach ( $watchlisted_hikes as $wanderung ) {
						setup_postdata( $wanderung );

						$wanderregionen = get_the_terms( $wanderung->ID, 'wanderregionen' );
						$hike_level     = get_the_terms( $wanderung->ID, 'anforderung' );

						$wanderregionen_name = ( ! empty( $wanderregionen ) ) ? $wanderregionen[0]->name : 'Region';

						$hike_level_name    = ( ! empty( $hike_level ) ) ? $hike_level[0]->name : '';
						$wander_saison_name = wegw_wandern_saison_name( $wanderung->ID );
						$hike_level_cls     = wegw_wandern_hike_level_class_name( $hike_level_name, $wanderung->ID );

						$hike_time     = ( get_field( 'dauer', $wanderung->ID ) ) ? wegwandern_formated_hiking_time_display( get_field( 'dauer', $wanderung->ID ) ) : '';
						$hike_distance = ( get_field( 'km', $wanderung->ID ) ) ? get_field( 'km', $wanderung->ID ) : '';
						$hike_ascent   = ( get_field( 'aufstieg', $wanderung->ID ) ) ? get_field( 'aufstieg', $wanderung->ID ) : '';
						$hike_descent  = ( get_field( 'abstieg', $wanderung->ID ) ) ? get_field( 'abstieg', $wanderung->ID ) : '';
						$kurzbeschrieb = ( get_field( 'kurzbeschrieb', $wanderung->ID ) ) ? get_field( 'kurzbeschrieb', $wanderung->ID ) : 'Fuga Nequam nos dolupta testinu llaceri ssequi nihilit, ut quissedia voluptassint prenimusam inum harchit imet am, aped mos volorio nsequos qui sundendestis aped mos volorio inum Onsequos et ...';

						/* Cluster updations */
						$latitude      = ( get_post_meta( $wanderung->ID, 'latitude', true ) ) ? get_post_meta( $wanderung->ID, 'latitude', true ) : '';
						$longitude     = ( get_post_meta( $wanderung->ID, 'longitude', true ) ) ? get_post_meta( $wanderung->ID, 'longitude', true ) : '';
						$gpx_file      = ( get_field( 'gpx_file', $wanderung->ID ) ) ? get_field( 'gpx_file', $wanderung->ID ) : '';
						$location_link = get_the_permalink( $wanderung->ID );
						$thumbsize     = 'hike-region';
						$post_thumb    = get_the_post_thumbnail_url( $wanderung->ID, $thumbsize );

						$wander_region_html  = "<div class='single-region-rating'>";
						$wander_region_html .= "<h6 class='single-region'>" . $wanderregionen_name . '</h6>';
						if ( is_plugin_active( 'wegwandern-summit-book/wegwandern-summit-book.php' ) ) {
							$average_rating      = get_wanderung_average_rating( $wanderung->ID );
							$wander_region_html .= '<span class="average-rating-display">' . $average_rating . '<i class="fa fa-star"></i></span>';
						}
						$wander_region_html .= '</div>';

						$watchlisted_array = wegwandern_get_watchlist_hikes_list();
						if ( in_array( $wanderung->ID, $watchlisted_array, false ) ) {
							$watchlisted_class  = 'watchlisted';
							$watchlist_on_click = '';
						} else {
							$watchlisted_class  = '';
							$watchlist_on_click = ' onclick="addToWatchlist(this, ' . $wanderung->ID . ')" ';
						}

						$posts_html .= '<div class="single-wander" id="data-watchlist-' . $wanderung->ID . '">
							<div class="single-wander-img">
								<a href="' . get_the_permalink( $wanderung->ID ) . '"><img class="wander-img" src="' . $post_thumb . '"></a>
								<div class="single-wander-heart ' . $watchlisted_class . '" ' . $watchlist_on_click . '></div>
								<div class="single-wander-map" onclick="openPopupMap(this)" data-hikeid="' . $wanderung->ID . '"></div>
							</div>' .
								$wander_region_html .
								'<a href="' . get_the_permalink( $wanderung->ID ) . '" class="wander-redirect"><h2>' . $wanderung->post_title . '</h2></a>
							<div class="wanderung-infobox">
								<div class="hiking_info">
									<div class="hike_level">
									<span class="' . $hike_level_cls . '"></span>
									<p>' . $hike_level_name . '</p>
									</div>
									<div class="hike_time">
									<span class="hike-time-icon"></span>
									<p>' . $hike_time . ' h</p>
									</div>
									<div class="hike_distance">
									<span class="hike-distance-icon"></span>
									<p>' . $hike_distance . ' km</p>
									</div>
									<div class="hike_ascent">
									<span class="hike-ascent-icon"></span>
									<p>' . $hike_ascent . ' m</p>
									</div>
									<div class="hike_descent">
									<span class="hike-descent-icon"></span>
									<p>' . $hike_descent . ' m</p>
									</div>
									<div class="hike_month">
									<span class="hike-month-icon"></span>
									<p>' . $wander_saison_name . '</p>
									</div>
							    </div>
							</div>
						<div class="wanderung-desc">
						' . mb_strimwidth( $kurzbeschrieb, 0, 200, '...' ) . '
					    </div>' .
						'<div class="weg-map-popup-outter"><div id="weg-map-popup' . $wanderung->ID . '" ><div class="map-fixed-position">' .
						'<div id="weg-map-popup-inner-wrapper' . $wanderung->ID . '">' .
						'<div class="close_map" onclick="closeElement(this)"><span class="close_map_icon"></span></div>' .
						'<div id="cesiumContainer" class="cesiumContainer"></div>' .
						'<div class="map_currentLocation"></div>' .
						'<div id="threeD" class="map_3d"></div>' .
						'<div id="map_direction" class="map_direction"></div>' .
						'<div class="botom_layer_icon">' .
						'<div class="accordion" >' .
						'<div class="weg-layer-wrap layer_head">' .
						'<div class="weg-layer-text">Hintergrund</div>' .
						'</div>' .
						'</div>' .
						'<div class="panel">' .
						'<div class="weg-layer-wrap activeLayer" id="colormap_view_section">' .
						'<div class="weg-layer-text">Karte farbig</div>' .
						'</div>' .
						'<div class="weg-layer-wrap" id="aerial_view_section">' .
						'<div class="weg-layer-text">Luftbild</div>' .
						'</div>' .
						'<div class="weg-layer-wrap" id="grey_view_section">' .
						'<div class="weg-layer-text">Karte SW</div>' .
						'</div>' .
						'</div>' .
						'</div>' .
						'<div class="copyRight">' .
						'<a target="_blank" href="https://www.swisstopo.admin.ch/de/home.html">© swisstopo</a>' .
						'</div>' .
						'<div class="map_filter">' .
						'<div class="map_filter_inner_wrapper">' .
						'<div class="accordion">Karteninformationen</div>' .
						'<div class="panel">' .
						'<div class="fc_check_wrap">' .
						'<label class="check_wrapper">ÖV-Haltestellen' .
						'<input type="checkbox" name="" id="transport_layer_checkbox" value="">' .
						'<span class="redmark"></span>' .
						'</label>' .
						'<label class="check_wrapper">Wanderwege' .
						'<input type="checkbox" name="" id="hikes_trailing_layer" value="">' .
						'<span class="redmark"></span>' .
						'</label>' .
						'<label class="check_wrapper">Gesperrte Wanderwege' .
						'<input type="checkbox" name="" id="closure_hikes_layer" value="">' .
						'<span class="redmark"></span>' .
						'</label>' .
						'<label class="check_wrapper">Schneehöhe Exolabs' .
						'<input type="checkbox" name="" id="snow_depth_layer" value="">' .
						'<span class="redmark"></span>' .
						'<div class="info_icon" onclick="infoIconClicked(event,&quot;weg-map-popup' . $wanderung->ID . '&quot;)"></div>' .
						'</label>' .
						'<label class="check_wrapper">Schneebedeckung ExoLabs' .
						'<input type="checkbox" name="" id="snow_cover_layer" value="">' .
						'<span class="redmark"></span>' .
						'</label>' .
						'<label class="check_wrapper">Hangneigungen über 30°' .
						'<input type="checkbox" id="slope_30_layer" name="" value="">' .
						'<span class="redmark"></span>' .
						'</label>' .
						'<label class="check_wrapper">Wildruhezonen' .
						'<input type="checkbox" id="wildlife_layer" name="" value="">' .
						'<span class="redmark"></span>' .
						'</label>' .
						'<label class="check_wrapper">Wegpunkte WegWandern.ch' .
						'<input type="checkbox" id="waypoints_layer" name="" value="">' .
						'<span class="redmark"></span>' .
						'</label>' .
						'</div>' .
						'</div>' .
						'</div>' .
						'</div>' .
						'</div>' .
						'<div id="detailPgPopup"><div id="detailPgPopupContent"></div></div>' .
						'<div class="elevationGraph"></div> ' .
						'<div class="options" id="mapOptions"></div>' .
						'<div class="snow_info_details hide">' .
						'<div class="snow_inner_wrapper">' .
							'<div class="snow_close_wrapper" onclick="infoIconClosed(event,&quot;weg-map-popup' . $wanderung->ID . '&quot;)"><div class="snow_close"></div></div>' .
							'<div class="snow_tile">Auf der Karte wird die Schneehöhe (in cm) mit den folgenden Farben angezeigt:</div>' .
							'<div class="snow_image"></div>' .
							'<a href="https://wegwandern.ch/schneekarten-wo-liegt-jetzt-schnee/" target="_blank"><div class="snow_link externalLink">Weitere Informationen</div></a>' .
						'</div>' .
						'</div>' .
						'<div id="info"></div>' .
						'<div class="popover" id="transport-layer-info-popup">' .
						'<div class="arrow"></div>' .
						'<div class="popover-title">' .
						'<div class="popup-title">Objekt Informationen</div>' .
						'<div class="popup-buttons">' .

						'<button class="fa fa-remove" title="Close" onclick="closeTransportLayerPopup()"></button>' .
						'</div>' .
						'</div>' .
						'<div class="popover-content">' .
						'<div class="popover-scope">' .
						'<div class="popover-binding">' .
						'<div class="htmlpopup-container" id="tl-content-area">' .
						'</div>' .
						'</div>' .
						'</div>' .
						'</div>' .
						'</div>' .
						'</div></div></div>' .
						'</div>';
						$posts_html .= '<script type="text/javascript">
					var gpx_file = "' . $gpx_file . '";
				</script>';
					}
					echo $posts_html;
					$watchlist_error_msg = __(
						'Es befinden sich noch keine Touren in deiner Merkliste. Zum Hinzufügen klicke im
				Tourenportal einfach auf das kleine Herz. Damit die Touren hierher übertragen werden,
				musst du eingeloggt sein.',
						'wegwandern-summit-book'
					);
					?>
				</div>
			</div>
			<input type="hidden" name="data-watchlist-error" id="data-watchlist-error" value="<?php echo esc_attr( $watchlist_error_msg ); ?>">
			<?php
		} else {
			?>
			<div class="user-dash-no-content-msg watchlists-none-msg">
				<?php
				echo esc_attr_e(
					'Es befinden sich noch keine Touren in deiner Merkliste. Zum Hinzufügen klicke im
					Tourenportal einfach auf das kleine Herz. Damit die Touren hierher übertragen werden,
					musst du eingeloggt sein.',
					'wegwandern-summit-book'
				);
				?>
			</div>
			<?php
		}
		?>
	</div>
	<div class="user-ratings-section">
		<h2><?php echo esc_attr_e( 'Meine Bewertungen', 'wegwandern-summit-book' ); ?></h2>
		<?php
		$bewertungen = array();
		if ( $current_user->ID > 0 ) {
			$args        = array(
				'post_type'   => 'bewertung',
				'author'      => $current_user->ID,
				'numberposts' => -1,
			);
			$bewertungen = get_posts( $args );
		}
		if ( ! empty( $bewertungen ) ) {
			?>
			<div class="user-ratings">
			<?php
			foreach ( $bewertungen as $each_bewertung ) {
				$tour_id = get_post_meta( $each_bewertung->ID, 'rated_wanderung', true );
				?>
				<div class='each-rating'>
					<h4 class='rating-text'><?php echo esc_attr_e( 'Meine Bewertungen im Wanderbeschrieb', 'wegwandern-summit-book' ); ?>:</h4>
					<?php echo get_tour_link( $tour_id ); ?>
					<br>
					<?php
					$rating = get_post_meta( $each_bewertung->ID, 'rating', true );
					echo show_star_rating( $rating );
					?>
				</div>
				<?php
			}
			?>
			</div>
			<?php
		} else {
			?>
			<div class="user-dash-no-content-msg ratings-none-msg">
				<?php
				echo esc_attr_e(
					'Du hast noch keine Bewertungen abgegeben. Wenn du eine Tour im Community-Bereich
					kommentierst, kannst du dort auch eine Bewertung abgeben. Dazu musst du eingeloggt
					sein.',
					'wegwandern-summit-book'
				);
				?>
			</div>
			<?php
		}
		?>
	</div>
	<div class="user-comments-section">
		<h2><?php echo esc_attr_e( 'Meine Kommentare', 'wegwandern-summit-book' ); ?></h2>
		<?php
		$args          = array(
			'user_id' => $current_user->ID,
			'status'  => array( 'hold', 'approve', 'spam' ),
		);
		$user_comments = get_comments( $args );
		if ( ! empty( $user_comments ) ) {
			?>
			<div class="user-comments">
			<?php
			foreach ( $user_comments as $each_comment ) {
				?>
				<div class='each-user-comment' id="data-comment-<?php echo esc_attr( $each_comment->comment_ID ); ?>">
					<div class='each-user-comment-content'>
						<h4 class='comment-text'><?php echo esc_attr_e( 'Mein Kommentar im Wanderbeschrieb', 'wegwandern-summit-book' ); ?>:</h4>
						<?php echo get_tour_link( $each_comment->comment_post_ID ); ?>
						<br>
						<p class='comment-content'><?php echo esc_attr( $each_comment->comment_content ); ?></p>
						<?php echo show_comment_images( $each_comment->comment_ID, 'user-dashboard' ); ?>
					</div>
					<div class='comment-actions'>
						<div class='comment-status'>
							<?php
							if ( '0' === $each_comment->comment_approved ) {
								echo esc_attr_e( 'In Prüfung', 'wegwandern-summit-book' );
							} elseif ( '1' === $each_comment->comment_approved ) {
								echo esc_attr_e( 'Veröffentlicht', 'wegwandern-summit-book' );
								echo '<br>';
								echo esc_attr( gmdate( 'd.m.Y', strtotime( $each_comment->comment_date_gmt ) ) );
							} elseif ( 'spam' === $each_comment->comment_approved ) {
								echo esc_attr_e( 'Abgelehnt', 'wegwandern-summit-book' );
							}
							?>
						</div>
						<div class='delete-comment' id="delete-comment_<?php echo esc_attr( $each_comment->comment_ID ); ?>">
						</div>
					</div>
				</div>
				<?php
			}
			$comment_error_msg = __(
				'Du hast noch keinen Kommentar abgegeben. Du kannst eine Tour jeweils im CommunityBereich kommentieren und deinem Kommentar auch bis zu 6 Bilder hinzufügen. Dort
				kannst du auch eine Bewertung abgeben. Dazu musst du eingeloggt sein. Dein Kommentar
				wird vor Veröffentlichung geprüft. Dies kann einige Tage dauern. An Wochenenden und
				Feiertagen werden keine Kommentare publiziert.',
				'wegwandern-summit-book'
			);
			?>
			</div>
			<input type="hidden" name="data-comment-error" id="data-comment-error" value="<?php echo esc_attr( $comment_error_msg ); ?>">
			<?php
		} else {
			?>
			<div class="user-dash-no-content-msg comments-none-msg">
				<?php
				echo esc_attr_e(
					'Du hast noch keinen Kommentar abgegeben. Du kannst eine Tour jeweils im CommunityBereich kommentieren und deinem Kommentar auch bis zu 6 Bilder hinzufügen. Dort
					kannst du auch eine Bewertung abgeben. Dazu musst du eingeloggt sein. Dein Kommentar
					wird vor Veröffentlichung geprüft. Dies kann einige Tage dauern. An Wochenenden und
					Feiertagen werden keine Kommentare publiziert.',
					'wegwandern-summit-book'
				);
				?>
			</div>
			<?php
		}
		?>
	</div>
	<div class="user-pinwall-ads-section">
		<h2><?php echo esc_attr_e( 'Meine Pinnwand', 'wegwandern-summit-book' ); ?></h2>
		<?php
		$pinnwand_eintrags = array();
		if ( $current_user->ID > 0 ) {
			$args              = array(
				'post_type'      => 'pinnwand_eintrag',
				'author'         => $current_user->ID,
				// 'meta_key'    => 'user',
				// 'meta_value'  => $current_user->ID,
				'post_status'    => array( 'publish', 'future', 'pending', 'draft' ),
				'posts_per_page' => -1,
			);
			$pinnwand_eintrags = get_posts( $args );
		}
		if ( ! empty( $pinnwand_eintrags ) ) {
			?>
			<div class="user-pinwall-ads">
				<?php
				foreach ( $pinnwand_eintrags as $each_ad ) {
					get_pinwand_ad_view( $each_ad->ID, 'user-dashboard' );
				}
				$pinwall_ad_error_msg = __(
					'Du hast noch kein Inserat erstellt oder deine Einträge sind abgelaufen. Bitte beachte, die
					Beiträge haben eine maximale Laufzeit von 6 Monaten und werden dann automatisch
					gelöscht und können nicht wieder hergestellt werden. Dein Inserat wird vor
					Veröffentlichung geprüft. Dies kann einige Tage dauern. An Wochenenden und Feiertagen
					werden keine Inserate publiziert.',
					'wegwandern-summit-book'
				);
				?>
			</div>
			<input type="hidden" name="data-pinwand-ad-error" id="data-pinwand-ad-error" value="<?php echo esc_attr( $pinwall_ad_error_msg ); ?>">
			<?php
		} else {
			?>
			<div class="user-dash-no-content-msg pinwall-ads-none-msg">
				<?php
				echo esc_attr_e(
					'Du hast noch kein Inserat erstellt oder deine Einträge sind abgelaufen. Bitte beachte, die
					Beiträge haben eine maximale Laufzeit von 6 Monaten und werden dann automatisch
					gelöscht und können nicht wieder hergestellt werden. Dein Inserat wird vor
					Veröffentlichung geprüft. Dies kann einige Tage dauern. An Wochenenden und Feiertagen
					werden keine Inserate publiziert.',
					'wegwandern-summit-book'
				);
				?>
			</div>
			<?php
		}
		?>
	</div>
	<div class="user-hiking-articles-section">
		<h2><?php echo esc_attr_e( 'Meine Wanderungen', 'wegwandern-summit-book' ); ?></h2>
		<?php
		$community_beitrags = array();
		if ( $current_user->ID > 0 ) {
			$args               = array(
				'post_type'   => 'community_beitrag',
				'meta_key'    => 'user',
				'meta_value'  => $current_user->ID,
				'post_status' => 'any',
				'numberposts' => -1,
			);
			$community_beitrags = get_posts( $args );
		}
		if ( ! empty( $community_beitrags ) ) {
			?>
			<div class="user-hiking-articles">
			<?php
			foreach ( $community_beitrags as $each_article ) {
				$all_post_meta          = get_post_meta( $each_article->ID );
				$teaser_image_post_id   = isset( $all_post_meta['teaser_image'][0] ) ? $all_post_meta['teaser_image'][0] : '';
				$region_id              = isset( $all_post_meta['region'][0] ) ? $all_post_meta['region'][0] : '';
				$region                 = '' !== $region_id ? get_term( $region_id ) : null;
				$article_status         = isset( $all_post_meta['article_status'][0] ) ? $all_post_meta['article_status'][0] : '';
				$entry_id               = FrmDb::get_var( 'frm_items', array( 'post_id' => $each_article->ID ), 'id' );
				$community_article_link = get_permalink( $each_article );
				?>
				<div class="each-hiking-article" id="data-article-<?php echo esc_attr( $entry_id ); ?>">
					<div class="each-hiking-article-content">
						<div class="hiking-article-teaser-img">
							<?php
							$each_article_img_url = wp_get_attachment_image_url( $teaser_image_post_id, 'full' );
							if ( '' !== $teaser_image_post_id ) {
								?>
								<img src="<?php echo $each_article_img_url; ?>">
								<?php
							}
							?>
						</div>
						<div class="hiking-article-region"><?php echo $region ? esc_attr( $region->name ) : ''; ?></div>
						<div class="hiking-article-title"><a href="<?php echo $community_article_link; ?>"><?php echo isset( $all_post_meta['titel'][0] ) ? esc_attr( $all_post_meta['titel'][0] ) : ''; ?></a></div>
						<div class="hiking-article-desc">
							<?php echo isset( $all_post_meta['einleitung'][0] ) ? wp_trim_words( esc_attr( $all_post_meta['einleitung'][0] ), 35, '...' ) : ''; ?>
						</div>
					</div>
					<div class="hiking-article-actions">
						<div class="hiking-article-actions-left">
							<?php
							if ( 'published' === $article_status ) {
								$published_date = get_the_modified_date( 'd.m.Y', $each_article->ID );
								$published_text = __( 'Veröffentlicht am ', 'wegwandern-summit-book' );
								echo esc_attr( $published_text ) . '<br>' . esc_attr( $published_date );
							} elseif ( 'inVerification' === $article_status ) {
								echo esc_attr( SUMMIT_BOOK_COMMUNITY_BEITRAG_STATUS[ $article_status ] ? SUMMIT_BOOK_COMMUNITY_BEITRAG_STATUS[ $article_status ] : '' );
							} elseif ( 'saved' === $article_status ) {
								echo esc_attr( SUMMIT_BOOK_COMMUNITY_BEITRAG_STATUS[ $article_status ] ? SUMMIT_BOOK_COMMUNITY_BEITRAG_STATUS[ $article_status ] : '' );
								$previous_article_status = get_post_meta( $each_article->ID, 'previous_article_status', true );
								if ( $previous_article_status === 'rejected' ) {
									echo ' <span class="article-reject-status"> (' . __( 'Abgelehnt', 'wegwandern' ) . ')</span>';
								}
							}
							?>
						</div>
						<div class="hiking-article-actions-right">
							<?php
							if ( 'saved' === $article_status ) {
								$edit_page_id = url_to_postid( NEUE_TOUR_POSTEN_PAGE_URL );
								$edit_text    = __( 'Edit', 'wegwandern-summit-book' );
								echo "<a href='" . do_shortcode( "[frm-entry-edit-link id=$entry_id label=0 page_id=$edit_page_id class='edit-hiking-article']" ) . "'><div class='article-edit-link'></div></a>";
							}
							$delete_text = __( 'Delete', 'wegwandern-summit-book' );
							// Shortcode for delete entry provides only alert msg [frm-entry-delete-link id=$entry_id label=$delete_text confirm='Bist du sicher, dass du diese Wanderung löschen möchtest?' class='delete-hiking-article' prefix='each-hiking-article-'].
							?>
							<div class="delete-hiking-article" id="delete-hiking-article_<?php echo esc_attr( $entry_id ); ?>"></div>
						</div>
					</div>
				</div>
				<?php
			}
			$article_error_msg = __(
				'Du hast noch keinen Wander-Beitrag erstellt oder hast diese gelöscht. Bitte beachte:
gelöschte Beiträge können nicht wieder hergestellt werden',
				'wegwandern-summit-book'
			);
			?>
			</div>
			<input type="hidden" name="data-article-error" id="data-article-error" value="<?php echo esc_attr( $article_error_msg ); ?>">
			<?php
		} else {
			?>
		<div class="user-dash-no-content-msg hiking-articles-none-msg">
			<?php
			echo esc_attr_e(
				'Du hast noch keinen Wander-Beitrag erstellt oder hast diese gelöscht. Bitte beachte:
gelöschte Beiträge können nicht wieder hergestellt werden',
				'wegwandern-summit-book'
			);
			?>
		</div>
			<?php
		}
		?>
	</div>
</div>
<?php get_footer(); ?>
