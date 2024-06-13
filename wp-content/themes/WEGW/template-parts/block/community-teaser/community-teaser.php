<div class="community-teaser-container grey-back">
<div class="community-teaser">
	<?php
	/**
	 * Get the content from acf field
	 *
	 * @package wegwandern-summit-book
	 */

	$community_heading        = get_field( 'community_teaser_heading' );
	$left_side_text           = get_field( 'community_teaser_left_side_text' );
	$right_side_text          = get_field( 'community_teaser_right_side_text' );
	$community_feature_list   = get_field( 'community_teaser_feature_list' );
	$registration_button_text = get_field( 'community_teaser_registration_button_text' );
	$login_button_text        = get_field( 'community_teaser_login_button_text' );

	$teaser_articles_args = array(
		'post_type'   => 'community_beitrag',
		'post_status' => 'publish',
		'orderby'     => 'rand',
		'numberposts' => 3,
	);
	$teaser_artcles       = get_posts( $teaser_articles_args );
	if ( $community_heading && $community_heading !== '' ) {
		?>
		<h2 class="community-teaser-heading"><?php echo esc_attr( $community_heading ); ?></h2>
		<?php
	}
	?>
	<div class="community-teaser-content">
	<div class="left-side-section">
		<?php
		if ( $left_side_text && $left_side_text !== '' ) {
			?>
			<h3 class="community-teaser-sub-text"><?php echo esc_attr( $left_side_text ); ?></h3>
			<?php
		}
		if ( ! empty( $community_feature_list ) ) {
			?>
			<ul class="community-teaser-feature-list">
				<?php
				foreach ( $community_feature_list as $each_feature ) {
					?>
					<li class="community-teaser-list-item"><?php echo esc_attr( $each_feature['community_teasure_feature_list_item'] ); ?></li>
					<?php
				}
				?>
			</ul>
			<?php
		}
		?>
		<?php
		$current_logged_in_user = wp_get_current_user();
		if ( ! is_user_logged_in() ) {
			?>
			<div class="left-side-buttons">
				<button class="community-teaser-reg-button" onclick="openRegPoppup('summitRegMenu')"><?php echo esc_attr( $registration_button_text ); ?></button>
				<button class="community-teaser-login-button" onclick="openSummitBookLoginMenu()"><?php echo esc_attr( $login_button_text ); ?></button>
			</div>
			<?php
		}
		?>
	</div>
	<?php
	if ( ! empty( $teaser_articles_args ) ) {
		?>
		<div class="right-side-section">
			<?php
			if ( $right_side_text && $right_side_text !== '' ) {
				?>
				<h3 class="community-teaser-sub-text"><?php echo esc_attr( $right_side_text ); ?></h3>
				<?php
			}
			?>
			<div class="community-teaser-articles-section">
				<?php
				foreach ( $teaser_artcles as $each_article ) {
					?>
					<div class="each-community-teaser-article">
						<?php
							$community_article_link = get_permalink( $each_article );
							$teaser_image           = get_post_meta( $each_article->ID, 'teaser_image', true );
							$region                 = get_post_meta( $each_article->ID, 'region', true );
							$wanderregionen         = get_term( $region );
							$image_url              = wp_get_attachment_image_url( $teaser_image, 'full' );
							echo "<div class='article-img'><a href='$community_article_link'><img src='" . $image_url . "'></a></div>";
							echo "<div class='article-sub-section'>";
							echo "<div class='article-region'>" . $wanderregionen->name . '</div>';
							echo "<div class='article-title'><a href='$community_article_link'>" . get_post_meta( $each_article->ID, 'titel', true ) . '</a></div>';
							echo "<div class='article-author'>" . get_user_avatar( $each_article->post_author ) . get_user_display_name( $each_article->post_author ) . '</div>';
							echo '</div>';
						?>
					</div>
					<?php
				}
				?>
			</div>
		</div>
		<?php
	}
	?>
	</div>
</div>
</div>