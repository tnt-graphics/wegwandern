<?php
get_header();
b2b_user_menu_callback();
wegwb_b2b_check_user_role_access(); ?>

<div class="container">
	<div class="ppc_container">
		<div class="ppc_head">
			<h1><?php echo get_the_title(); ?></h1>
			<h3><?php _e( 'B2B Login', 'wegw-b2b' ) ?></h3>
			<?php $b2b_profile_fields = get_b2b_profile_fields();

			if ( empty( $b2b_profile_fields['gender'] ) || empty( $b2b_profile_fields['firstname'] ) || empty( $b2b_profile_fields['lastname'] ) || empty( $b2b_profile_fields['address'] ) || empty( $b2b_profile_fields['ort'] ) || empty( $b2b_profile_fields['plz'] ) || empty( $b2b_profile_fields['phonenumber'] ) || empty( $b2b_profile_fields['email'] ) ) { ?>
				<p><?php _e( 'Bitte vervollständigen Sie ihr Profil um Inserate zu erstellen.', 'wegw-b2b' ) ?></p>
				<a href="<?php echo site_url() . '/profil'; ?>" class="angebote-btn-wrapper"><div class="angebote_btn"><div class="angebote_btn_text"><?php _e( 'Profil vervollständigen', 'wegw-b2b' ); ?></div></div></a>
			<?php }else{
				 if ( have_rows( 'section_1_right' ) ) : ?>
					<?php
					while ( have_rows( 'section_1_right' ) ) :
						  the_row();
						  $section_1_right_button_text = get_sub_field( 'section_1_right_button_text' );
						  $section_1_right_button_link = get_sub_field( 'section_1_right_button_link' );
						
					
						if ( $section_1_right_button_text ) { ?>
							<a href="<?php echo $section_1_right_button_link; ?>" class="angebote-btn-wrapper"><div class="angebote_btn"><div class="angebote_btn_text"><?php echo $section_1_right_button_text; ?></div></div></a>
				<?php } 
				
				  endwhile; 
			 	endif; 
			
			} ?>
		</div>
		
	</div>
	<div class="ppc_container">
		<div>
			<div class="pay_per_click_wrapper">
				<?php if ( have_rows( 'section_1_left' ) ) : ?>
					<?php
					while ( have_rows( 'section_1_left' ) ) :
						  the_row();
						  $section_1_left_title   = get_sub_field( 'section_1_left_title' );
						  $section_1_left_content = get_sub_field( 'section_1_left_content' );
						?>
				<h3><?php echo $section_1_left_title; ?></h3>
				<div class="ppc_content"><?php echo $section_1_left_content; ?>
						<?php
						if ( have_rows( 'credits_price_settings', 'option' ) ) :
							/* Loop through rows. */
							$i = 1;
							?>

					<div class="klicks_list">
						Preisbeispiele:
						<ul>
							<?php
							while ( have_rows( 'credits_price_settings', 'option' ) ) :
								the_row();
								$b2b_ad_clicks_count = get_sub_field( 'b2b_ad_clicks_count' );
								$b2b_ad_clicks_price = get_sub_field( 'b2b_ad_clicks_price' );
								$min_text            = ( $i == 1 ) ? '(Mindestbuchung)' : '';
								?>

							<li><?php echo $b2b_ad_clicks_count; ?> Klicks = CHF <?php echo $b2b_ad_clicks_price; ?> <?php echo $min_text; ?></li>
								<?php
								$i++;
				endwhile;
							?>
						</ul>
					</div>
					
				<?php endif; ?>
				</div>
				<?php endwhile; ?>
				<?php endif; ?>
			</div>
			<?php if ( have_rows( 'section_2_left' ) ) : ?>
					<?php
					while ( have_rows( 'section_2_left' ) ) :
						  the_row();
						  $section_2_left_title   = get_sub_field( 'section_2_left_title' );
						  $section_2_left_content = get_sub_field( 'section_2_left_content' );
						?>
			<h3><?php echo $section_2_left_title; ?></h3>
			<div class="hinweis_content"><?php echo $section_2_left_content; ?></div>
			<?php endwhile; ?>
				<?php endif; ?>
		</div>
		<?php if ( have_rows( 'section_1_right' ) ) : ?>
			<?php
			while ( have_rows( 'section_1_right' ) ) :
				  the_row();
				  $section_1_right_title       = get_sub_field( 'section_1_right_title' );
				  $section_1_right_content     = get_sub_field( 'section_1_right_content' );
				  $section_1_right_button_text = get_sub_field( 'section_1_right_button_text' );
				  $section_1_right_button_link = get_sub_field( 'section_1_right_button_link' );
				?>
		<div class="ppc_right_side_wrapper">
			<h3><?php echo $section_1_right_title; ?></h3>
			<div class="werbeplatzierungen_content"><?php echo $section_1_right_content; ?></div>
		</div>
		<?php endwhile; ?>
				<?php endif; ?>
	</div>
</div>
<?php
get_footer();
