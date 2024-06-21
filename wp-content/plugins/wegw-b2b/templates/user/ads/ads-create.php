<?php
if ( ! session_id() ) {
	session_start();
}
/* Unsetting session variable - $_SESSION['b2b_ads_ID'] */
if ( isset( $_SESSION['b2b_ads_ID'] ) && $_SESSION['b2b_ads_ID'] != '' ) {
	unset( $_SESSION['b2b_ads_ID'] );
}

get_header();
b2b_user_menu_callback();
wegwb_b2b_check_user_role_access();

/* Check Ad created by current logged in user */
$user_ID = get_current_user_id();
$url     = $_SERVER['REQUEST_URI'];
$params  = explode( '/', $url );
$ad_ID   = ( isset( $params[3] ) && is_array( $params ) ) ? $params[3] : '';

$author_id = get_post_field( 'post_author', $ad_ID );

if ( $ad_ID != '' ) {
	/* Verify Author ID */
	if ( $user_ID != $author_id ) {
		echo '<div class="container"><p>Access Denied!</p></div>';
		die();
	}

	/* Verify post status == `draft` */
	$current_ad_status = get_post_status( $ad_ID );
	if ( $current_ad_status != 'draft' ) {
		echo '<div class="container"><p>Ad cannot be edited.</p></div>';
		die();
	}
}

$edit_mode  = ( isset( $ad_ID ) && $ad_ID != '' ) ? true : false;
$hide_class = '';
if ( $edit_mode ) {
	$b2b_ad = get_post( $ad_ID, ARRAY_A );
}

$kategorie_terms = get_terms(
	array(
		'taxonomy'   => 'kategorie',
		'hide_empty' => false,
	)
);

$region_terms = get_terms(
	array(
		'taxonomy'   => 'wanderregionen',
		'hide_empty' => false,
	)
);

$get_b2b_ad_kategorie        = get_the_terms( $ad_ID, 'kategorie' );
$get_b2b_ad_region           = get_the_terms( $ad_ID, 'wanderregionen' );
$get_b2b_ad_land             = get_post_meta( $ad_ID, 'wegw_b2b_ad_land', true );
$get_b2b_ad_parent_region    = get_post_meta( $ad_ID, 'wegw_b2b_ad_region', true );
$get_b2b_ad_image            = get_post_meta( $ad_ID, 'wegw_b2b_ad_image', true );
// $get_wegw_b2b_ad_sub_title   = get_post_meta( $ad_ID, 'wegw_b2b_ad_main_title', true );
$get_wegw_b2b_ad_bold_text   = get_post_meta( $ad_ID, 'wegw_b2b_ad_bold_text', true );
$get_wegw_b2b_ad_link        = get_post_meta( $ad_ID, 'wegw_b2b_ad_link', true );
$get_wegw_b2b_ad_credits_end = get_post_meta( $ad_ID, 'wegw_b2b_ad_credits_end', true );
$get_wegw_b2b_ad_end_date    = get_post_meta( $ad_ID, 'wegw_b2b_ad_end_date', true );

if ( ! empty( $get_b2b_ad_region ) && is_array( $get_b2b_ad_region ) ) {
	$b2b_ad_region_arr = array_column( $get_b2b_ad_region, 'term_id' );
} else {
	$b2b_ad_region_arr = array();
}

if ( has_post_thumbnail( $ad_ID ) ) {
	$b2b_ad_image = get_the_post_thumbnail_url( $ad_ID );
} else {
	$b2b_ad_image = '';
}

$b2b_ad_land = isset( $get_b2b_ad_land ) ? $get_b2b_ad_land : '';
// $b2b_ad_image       = isset( $get_b2b_ad_image ) ? $get_b2b_ad_image : '';
// $b2b_ad_sub_title   = isset( $get_wegw_b2b_ad_sub_title ) ? $get_wegw_b2b_ad_sub_title : '';
$b2b_ad_bold_text   = isset( $get_wegw_b2b_ad_bold_text ) ? $get_wegw_b2b_ad_bold_text : '';
$b2b_ad_link        = isset( $get_wegw_b2b_ad_link ) ? $get_wegw_b2b_ad_link : '';
$b2b_ad_credits_end = isset( $get_wegw_b2b_ad_credits_end ) ? $get_wegw_b2b_ad_credits_end : 0;
$b2b_ad_end_date    = isset( $get_wegw_b2b_ad_end_date ) ? $get_wegw_b2b_ad_end_date : '';

$b2b_holiday_start_date = '';
$b2b_holiday_end_date   = '';

/* Payment Confirm Popup */
$hide_class_payment_popup = 'hide';
if ( isset( $_SESSION['payment_status'] ) && $_SESSION['payment_status'] == 1 ) {
	echo "<script type=\"text/javascript\">
		var e = document.querySelector('.overlay'); e.classList.remove('hide');
	</script>";
	$hide_class_payment_popup = '';
	?>

	<div class="paymentConfirm <?php echo $hide_class_payment_popup; ?>">
		<div class="payment_confirm_wrap"> 
			<div class="close_warap"><span class="filter_close" onclick="closePaymetnConfirmPopup()"></span></div>
			<h4>Ihre Zahlung war erfolgreich. Fahren Sie nun mit dem Erfassen Ihres Angebotes fort.</h4>
		</div>
	</div>

	<?php
	unset( $_SESSION['payment_status'] );
}
?>

<div class="container">
	<div class="create_offer">
		<div class="c_o_title">
			<h3><?php echo get_the_title(); ?> <span class="required mandatory_text">* Pflichtfeld</span></h3>
		</div>

		<!-- Delete Ad popup starts -->
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
		<!-- Delete Ad popup ends -->
		
		<form method="POST" enctype="multipart/form-data">
			<div class="c_o_category">
				<h6>Kategorie <span class="required">*</span></h6>
				<div class="c_o_category_check_wrap">
					<div class="c_o_category_check">
						<?php
						if ( ! empty( $kategorie_terms ) ) {
							if ( ! empty( $get_b2b_ad_kategorie ) && is_array( $get_b2b_ad_kategorie ) ) {
								$b2b_ad_kategorie_arr = array_column( $get_b2b_ad_kategorie, 'term_id' );
							} else {
								$b2b_ad_kategorie_arr = array();
							}

							foreach ( $kategorie_terms as $k ) {
								$kategorie_checked = ( in_array( $k->term_id, $b2b_ad_kategorie_arr ) ) ? 'checked' : '';
								?>
								<label class="check_wrapper">
								<?php echo $k->name; ?>
									<input type="checkbox" class="b2b-ad-kategorie" value="<?php echo $k->term_id; ?>" class="" <?php echo $kategorie_checked; ?>>
									<span class="redmark"></span>
								</label>
							<?php } ?> 
						<?php } ?>     
					</div>
					<div class="cat_error hide">Please choose a category</div>
				</div>
			</div>
			<div class="c_o_region_sec">
				<div class="b2b-ads-land-dropdown custom-select">
					<?php
						$land_checked_switzerland = ( $b2b_ad_land == 'schweiz' ) ? 'selected' : '';
						$land_checked_ausland     = ( $b2b_ad_land == 'ausland' ) ? 'selected' : '';
					?>
					<select name="land" id="land">
						<option value="">Land  </option>
						<option value="schweiz" <?php echo $land_checked_switzerland; ?>>Schweiz</option>
						<option value="ausland" <?php echo $land_checked_ausland; ?>>Ausland</option>
					</select>
				</div>
				<div class="b2b-ads-region-dropdown custom-select">
					<select name="region" id="region">
						<option value="">Region </option>
						<?php
						foreach ( $region_terms as $r ) {
							$region_checked = ( in_array( $r->term_id, $b2b_ad_region_arr ) ) ? 'selected' : '';
							if ( $r->parent != 0 ) {
								continue;
							}
							?>
							<option value="<?php echo $r->term_id; ?>" <?php echo $region_checked; ?>><?php echo $r->name; ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="b2b-ads-subregion-dropdown custom-select">
					<select name="subregion" id="subregion">
						<option value="">Unterregion </option>
						<?php
						if ( $edit_mode ) {
							$termchildren = get_terms(
								'wanderregionen',
								array(
									'parent'     => $get_b2b_ad_parent_region,
									'hide_empty' => false,
								)
							);
							foreach ( $termchildren as $r ) {
								$subregion_checked = ( in_array( $r->term_id, $b2b_ad_region_arr ) ) ? 'selected' : '';
								if ( $r->parent == 0 ) {
									continue;
								}
								?>
								<option value="<?php echo $r->term_id; ?>" <?php echo $subregion_checked; ?>><?php echo $r->name; ?></option>
								<?php
							}
						}
						?>
					</select>
				</div>
				<div class="region_error_message"></div>
			</div>
			<div class="c_o_detail_container">
				<h3>Preview Ihres Angebotes</h3>
				<div class="c_o_detail">
					<div class="c_o_preview_wrap">
						<div class="deleteWrap hide" onclick="deleteItem()">
								<div class="delete"></div>
							</div>
						<div class="edit hide" onclick="edit()"></div>
						<div class="c_o_preview">
							<?php if ( $edit_mode ) { ?>

								<img class="" src="<?php echo $b2b_ad_image; ?>">
								<div class="c_o_preview_content">
									<!-- <h6><?php // echo $b2b_ad_sub_title; ?> </h6> -->
									<h5><?php echo $b2b_ad['post_title']; ?> </h5>
									<p><?php echo $b2b_ad['post_content']; ?> <b><?php echo $b2b_ad_bold_text; ?></b></p>
									<a><span></span><?php echo $b2b_ad_link; ?></a>
								</div>

							<?php } else { ?>

								<img class="" src="https://wegwdevelop.dev.displayme.net/wp-content/uploads/2023/05/dornach_arlesheim_ermitage_gempenturm_schartenflue_schlosshof_ruine_dorneck_wanderung.jpg">
								<div class="c_o_preview_content">
									<!-- <h6>Beispiel </h6> -->
									<h5>Beispiel </h5>
									<p>Fuga Nequam nos dolupta testinu llaceri ssequi nihilit, ut quissedia voluptassint prenimusam inum harchit. <b>qui sunden destis aped mos volorio.</b></p>
									<a><span></span>www.externerlink.ch</a>
								</div>

							<?php } ?>
						</div>
					</div>
					<div class="c_o_content">
						<div class="upload-btn" onclick="pictureUpload()">
							<div class="upload-btn-text"><i class="fas fa-external-link-alt"></i>Upload Foto (max. 1 MB)</div>
							<input type="file" accept=".png, .jpg, .jpeg" id="b2b-ad-img" name="file" class="hide">
						</div>
						<span class="uploadFileName"></span>
						<img id="uploaded-image" class="hide" alt="Uploaded Image">
						<p class="upload_error"><span class="required">** Upload Foto</span> im Querformat (mind. 500 Pixel breit).</p>
						<div class="upload_error_message required hide"></div>
						<div class="form_fd">
							<label><b>Titel</b> (max. 25 Zeichen) <span class="required">*</span></label>
							<input type="text" placeholder="Ihr Text" id="b2b-ad-title" value="<?php echo @$b2b_ad['post_title']; ?>" maxlength="25">
							<div class="title_error_messgae required hide"></div>
							<!-- <label><b>Haupttitel Ihres Angebotes</b> (max. 50 Zeichen) <span class="required">*</span></label>
							<input type="text" placeholder="Ihr Text" id="b2b-ad-main-title" value="<?php // echo @$b2b_ad['post_title']; ?>" maxlength="50"> -->
							<label><b>Text</b> (max. 110 Zeichen) <span class="required">*</span></label>
							<input type="textarea" placeholder="Ihr Text" id="b2b-ad-description" value="<?php echo @$b2b_ad['post_content']; ?>" maxlength="110">
							<label><b>Text in Fett</b> (max. 50 Zeichen) <span class="required">*</span></label>
							<input type="text" placeholder="Ihr Text" id="b2b-ad-bold-descp" value="<?php echo $b2b_ad_bold_text; ?>" maxlength="50">
							<label><b>Link zu Ihrem Angebot</b> (URL Website) <span class="required">*</span></label>
							<input type="text" placeholder="Ihr Text" id="b2b-ad-link" value="<?php echo $b2b_ad_link; ?>">
						</div>

						<div class="c_o_btn_container">
							<div class="edit-mode-btn">
								<div class="edit-mode-btn-text">Vorschau</div>
							</div>
							<div class="go-to-checkout-mode-btn" id="proceed-credits-section">
								<?php if ( $edit_mode ) { ?>
									<input type="hidden" id="b2b_ad_id" value="<?php echo $ad_ID; ?>">
									<input type="hidden" id="edit_mode" value="1">
								<?php } ?>
								<input type="hidden" id="edit_mode" value="0">
								<div class="go-to-checkout-mode-btn-text">Plan auswählen</div>
							</div>
						</div>
					</div>
					<div class="c_o_proceed_content hide">
					<?php
					$b2b_user_available_credits      = wegwb_b2b_user_ads_credits_balance();
					$b2b_ad_existing_credits_balance = metadata_exists( 'post', $ad_ID, 'wegw_b2b_credits_count' ) ? get_post_meta( $ad_ID, 'wegw_b2b_credits_count', true ) : 0;
					echo '<p>' . __( 'Sie haben noch ' . $b2b_user_available_credits . ' bezahlte Klicks zur Verfügung.', 'wegw-b2b' ) . '</p>';

					if ( $b2b_user_available_credits == 0 ) {
						$b2b_credit_purchase_page = site_url( WEGW_B2B_CREDIT_PURCHASE_PAGE );
						echo '<p>' . __( 'Sie verfügen nicht über genügend Credits, um Anzeigen zu erstellen. Bitte erwerben Sie hier Credits. ', 'wegw-b2b' ) . '</p><a href="' . $b2b_credit_purchase_page . '" class="verfication-btn-text">Click</a>';
					}

					if ( $edit_mode ) {
						if ( $b2b_ad_existing_credits_balance != 0 ) {
							$hide_class = 'hide';
							echo '<p>' . __( 'Werbeguthaben ausgleichen: ', 'wegw-b2b' ) . $b2b_ad_existing_credits_balance . '</p>';

							if ( $b2b_user_available_credits > 0 ) {
								echo '<label class="check_wrapper">' . __( 'Credits hinzufügen', 'wegw-b2b' ) . '
							<input type="checkbox" id="wegwb_b2b_ads_credit_display"><span class="redmark"></span>
							</label>';
							}
						}
					}

					/* Check either User credit balance/Ad credit balance exists */
					if ( $b2b_user_available_credits > 0 || $b2b_ad_existing_credits_balance > 0 ) {
						?>

						<div class="c_o_proceed_content_select <?php echo $hide_class; ?>" id="wegwb_b2b_ads_credit_select">
							<p>Anzahl Klicks buchen <span class="required">*</span></p>
							<?php
							if ( have_rows( 'credits_price_settings', 'option' ) ) :
								/* Loop through rows. */
								while ( have_rows( 'credits_price_settings', 'option' ) ) :
									the_row();

									$b2b_ad_clicks_count = get_sub_field( 'b2b_ad_clicks_count' );
									$b2b_ad_clicks_price = get_sub_field( 'b2b_ad_clicks_price' );

									echo '<input type="radio" id="clicks' . $b2b_ad_clicks_count . '" name="b2b_credits" value="' . $b2b_ad_clicks_count . '" class="radio-button-input">';
									echo '<label for="clicks' . $b2b_ad_clicks_count . '">' . $b2b_ad_clicks_count . ' Klicks </label><br>';

								endwhile;
							endif;
							?>
							<input type="radio" id="clicksCustom" name="b2b_credits" value="custom" class="radio-button-input">
							<label for="clicksCustom">Custom Klicks</label><br>
							<input type="number" placeholder="Custom" name="b2b_credits_count_custom" min="1" class="custom_clicks hide">
							<div class="klicksVal required"></div>
						</div>
						<div class="c_o_proceed_content_pubkl">
							<div class="startDate">
								<?php
								/* Check if holiday dates set */
								if ( get_field( 'b2b_holiday', 'option' ) ) {
									$b2b_holiday_start_date = esc_html( get_field( 'b2b_holiday_start_date_selection', 'option' ) );
									$b2b_holiday_end_date   = esc_html( get_field( 'b2b_holiday_end_date_selection', 'option' ) );
								}
								?>
								<p>Start der Publikation (Datum)<span class="required">*</span></p>
								<input id="b2b-ad-activate-date" data-startDate="<?php echo $b2b_holiday_start_date; ?>" data-endDate="<?php echo $b2b_holiday_end_date; ?>" placeholder="dd/mm/yyyy" readonly>
								<div class="dateVal required"></div>
							</div>
							<div class="endDate">
								<?php
								/*
								if ( $b2b_ad_end_date != '' ) {
									$b2b_ad_end_date_stamp    = strtotime( $b2b_ad_end_date );
									$b2b_ad_end_date_formated = date( 'd/m/Y', $b2b_ad_end_date_stamp );
								} else {
									$b2b_ad_end_date_formated = '';
								} */
								?>
								<p>Ende der Publikation (Datum)</p>
								<input id="b2b-ad-activate-date-end" value="" placeholder="dd/mm/yyyy" readonly>
							</div>
							
						</div>
						<div id="create_ad_response" class="hide"></div>
						<?php
						if ( get_field( 'b2b_holiday', 'option' ) ) :
							$b2b_holiday_content_title       = esc_html( get_field( 'b2b_holiday_content_title', 'option' ) );
							$b2b_holiday_content_description = get_field( 'b2b_holiday_content_description', 'option' );
							?>
							<div class="holiday_info holiday_head">
								<div class="holiday_info_title"><?php echo $b2b_holiday_content_title; ?></div>
								<div class="holiday_info_content"> <?php echo $b2b_holiday_content_description; ?></div>
							</div>
						<?php endif; ?>
						<div class="verfication-btn">
							<div class="verfication-btn-text" id="b2b-ads-create-btn">Angebot veröffentlichen</div>
						</div>
						<div class="c_o_terms_n_condition">
							<input type="radio" id="b2b-ad-create-acceptance" checked name="" value="1">
							<label for="">Ja, ich akzeptiere die <a>AGBs</a>, <a>Nutzungsbedingungen</a>
								<br>und <a>Datenschutzbestimmungen</a>.</label>
						</div>
						
					<?php } ?>
					</div>
				</div>
			</div>
		</form>
	</div>

</div>

<?php get_footer(); ?>
