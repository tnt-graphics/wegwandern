<?php
/**
 * The template for displaying Wanderungen planen
 **/
function wanderung_planen_section() {
	global $post;
	$wanderung_id = $post->ID;

	$gpx_file                  = ( get_field( 'gpx_file', $wanderung_id ) ) ? get_field( 'gpx_file', $wanderung_id ) : '';
	$wanderbeschrieb           = ( get_field( 'wanderbeschrieb', $wanderung_id ) ) ? get_field( 'wanderbeschrieb', $wanderung_id ) : '';
	$startpunkt                = ( get_field( 'startpunkt', $wanderung_id ) ) ? get_field( 'startpunkt', $wanderung_id ) : '';
	$startpunkt_sbb_link       = ( get_field( 'startpunkt_sbb_link', $wanderung_id ) ) ? get_field( 'startpunkt_sbb_link', $wanderung_id ) : '';
	$endpunkt                  = ( get_field( 'endpunkt', $wanderung_id ) ) ? get_field( 'endpunkt', $wanderung_id ) : '';
	$endpunkt_sbb_link         = ( get_field( 'endpunkt_sbb_link', $wanderung_id ) ) ? get_field( 'endpunkt_sbb_link', $wanderung_id ) : '';
	$wetter                    = ( get_field( 'wetter', $wanderung_id ) ) ? get_field( 'wetter', $wanderung_id ) : '';
	$webcam                    = ( get_field( 'webcam', $wanderung_id ) ) ? get_field( 'webcam', $wanderung_id ) : '';
	$accommodations            = ( get_field( 'unterkunfte_und_verpflegung', $wanderung_id ) ) ? get_field( 'unterkunfte_und_verpflegung', $wanderung_id ) : '';
	$wanderpartnerin_finden    = ( get_field( 'wanderpartnerin_finden', 'option' ) ) ? get_field( 'wanderpartnerin_finden', 'option' ) : '';
	$button_text               = ( get_field( 'button_text', 'option' ) ) ? get_field( 'button_text', 'option' ) : '';
	$button_link               = ( get_field( 'button_link', 'option' ) ) ? get_field( 'button_link', 'option' ) : '';
	$swisstopo_app_link        = ( get_field( 'swisstopo_app_descp', 'option' ) ) ? get_field( 'swisstopo_app_descp', 'option' ) : '';
	$swisstopo_app_button_text = ( get_field( 'swisstopo_app_button_text', 'option' ) ) ? get_field( 'swisstopo_app_button_text', 'option' ) : '';
	$swisstopo_app_button_link = ( get_field( 'swisstopo_app_button_link', 'option' ) ) ? get_field( 'swisstopo_app_button_link', 'option' ) : '';
	?>

<div class="wanderungPlanMenu wanderungPlanWindow">
	<div class="wanderungPlan_title">
		<h3><?php echo esc_html__( 'Wanderung planen', 'wegwandern' ); ?></h3>
		<div class="close_warap">
			<span class="wanderungPlan_close" onclick="closeWanderungPlan()"></span>
		</div>
	</div>

	<div class="wanderungPlan_content_wrapper">
		<?php if ( $gpx_file ) { ?>
		<a href="<?php echo $gpx_file; ?>" download class="planen-download">
			<div class="gpx-Wanderung">
				<span class="gpx-WanderungIcon"></span>
				<span class="gpx-WanderungText"><?php echo esc_html__( 'GPX Wanderung', 'wegwandern' ); ?></span>
			</div>
		</a>
		<?php } ?>

		<?php
		if ( $wanderbeschrieb ) {
			$url = $wanderbeschrieb['url'];
			?>
		<a href="<?php echo $url; ?>" download class="planen-download">
			<div class="Wanderbeschrieb">
				<span class="WanderbeschriebIcon"></span>
				<span class="WanderbeschriebText"><?php echo esc_html__( 'Wanderbeschrieb', 'wegwandern' ); ?></span>
			</div>
		</a>
		<?php } ?>

		<div class="karteAndprofil" onclick="openPopupMapDetailPage(this)" data-hikeid="<?php echo $wanderung_id; ?>">
			<span class="karteAndprofilIcon"></span>
			<span class="karteAndprofilText"><?php echo esc_html__( 'Karte', 'wegwandern' ); ?> &amp;
				<?php echo esc_html__( 'Höhenprofil', 'wegwandern' ); ?></span>
		</div>

		<?php if ( $startpunkt ) { ?>
		<div class="startpunkt">
			<h3><?php echo esc_html__( 'Startpunkt', 'wegwandern' ); ?></h3>
			<div class="descPunkt">
				<?php
				if ( $startpunkt_sbb_link != '' ) {
					echo '<a class="wanderung-plannen-custom-link" href="' . $startpunkt_sbb_link . '" target="_blank"><div class="extenal_link"></div></a>';
				}
						echo $startpunkt;
				?>
			</div>
		</div>
		<?php } ?>

		<?php if ( $endpunkt ) { ?>
		<div class="endpunkt">
			<h3><?php echo esc_html__( 'Endpunkt', 'wegwandern' ); ?></h3>
			<div class="descPunkt">
				<?php
				if ( $endpunkt_sbb_link != '' ) {
					echo '<a class="wanderung-plannen-custom-link" href="' . $endpunkt_sbb_link . '" target="_blank"><div class="extenal_link"></div></a>';
				}
						echo $endpunkt;
				?>
			</div>
		</div>
		<?php } ?>

		<?php if ( $wetter ) { ?>
		<div class='wetterprognose'>
			<h3><?php echo esc_html__( 'Wetterprognose', 'wegwandern' ); ?></h3>
			<ul>
				<?php foreach ( $wetter as $wet ) { ?>
				<li>
					<div class="list_side_item">
						<div class="wetter_wrapper"><span
								class="wetterIcon"></span><span><?php echo $wet['wetter_bezeichnung']; ?></span></div>
						<a href="<?php echo $wet['link']; ?>" target="_blank">
							<div class="extenal_link"></div>
						</a>
					</div>
				</li>
				<?php } ?>
			</ul>
		</div>
		<?php } ?>

		<?php if ( ! empty( $webcam ) ) { ?>
		<div class="webcam">
			<h3><?php echo esc_html__( 'Webcam', 'wegwandern' ); ?></h3>
			<ul>
				<?php foreach ( $webcam as $web ) { ?>
				<li>
					<div class="list_side_item">
						<div class="webcam_wrapper">
							<span class="webcamIcon"></span>
							<span><?php echo $web['webcam_bezeichnung']; ?></span>
						</div>
						<?php if ( isset( $web['link'] ) && $web['link'] != '' ) { ?>
							<a href="<?php echo $web['link']; ?>" target="_blank">
								<div class="extenal_link"></div>
							</a>
						<?php } ?>
					</div>
				</li>
				<?php } ?>
			</ul>
		</div>
		<?php } ?>

		<?php if ( $accommodations ) { ?>
		<div class="accommodations ">
			<h3><?php echo esc_html__( 'Unterkünfte', 'wegwandern' ); ?> &amp;
				<?php echo esc_html__( 'Verpflegung', 'wegwandern' ); ?></h3>
			<div class="accom_wrapper">
				<?php foreach ( $accommodations as $accom ) { ?>
				<div class="descPunkt">
					<p><b><?php echo $accom['name']; ?> </b><br><?php echo $accom['adresse']; ?></p>
					<a href="tel:<?php echo $accom['tel']; ?>"><b><?php echo $accom['tel']; ?></b></a>
					<a target="_blank" href="<?php echo $accom['url']; ?>"><b><?php echo $accom['url_text']; ?></b></a>
				</div>
				<?php } ?>
			</div>
		</div>
		<?php } ?>

		<?php if ( $wanderpartnerin_finden ) { ?>
		<div class="hiking_partner">
			<h3><?php echo esc_html__( 'WanderpartnerIn finden', 'wegwandern' ); ?></h3>
			<div class="hiking_partner_desc"><?php echo $wanderpartnerin_finden; ?></div>
		</div>
		<a href="<?php echo site_url( '/' ) . $button_link; ?>">
			<div class="pinnwand_btn">
				<span><?php echo $button_text; ?></span>
			</div>
		</a>
		<?php } ?>

		<?php if ( $swisstopo_app_link ) { ?>
		<div class="hiking_partner swisstopo-app-section-mobile">
			<h3><?php echo esc_html__( 'Wanderroute in der Swisstopo-App', 'wegwandern' ); ?></h3>
			<div class="hiking_partner_desc"><?php echo $swisstopo_app_link; ?></div>
		</div>
			<?php
			if ( $swisstopo_app_button_link != '' && $gpx_file ) {
				// Encode the GPX data as base64.
				$base64_data = base64_encode( $gpx_file );
				// URL-encode the base64 data.
				$base64_url = str_replace( array( '+', '/', '=' ), array( '-', '_', '' ), $base64_data );
				?>
		<a href="<?php echo $swisstopo_app_button_link . $base64_url; ?>">
			<div class="pinnwand_btn">
				<span><?php echo $swisstopo_app_button_text; ?></span>
			</div>
		</a>
		<?php } ?>
		<?php } ?>
	</div>

</div>
<?php } ?>
