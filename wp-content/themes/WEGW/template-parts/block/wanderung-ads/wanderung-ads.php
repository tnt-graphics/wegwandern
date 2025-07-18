<?php
/**
 * Wanderung Ads Section
 **/

if( get_field('desktop_ad') ) {
	//if desktop ad is set, always execute this specific ad code ?>

		<div class='ad-section-wrap full-width'>
				 <p>Anzeige </p>
				 <div class='ad-section insidewide'>
					<div id="gb-inside-full-pos1-multidevice"></div>
					<script src="https://ch.prod.gbads.io/prod/loader/wegwandern.ch.loader.js"  data-slot="inside-full-pos1-multidevice"  crossorigin>
					</script>
				 </div>
			</div>
	
	
	<?php	
}	


// 2. pos

if ( get_field('ads_2') ) {

	//if ads_2 is set, always execute this specific ad code ?>
	<div class='ad-section-wrap full-width'>
				 <p>Anzeige </p>
				 <div class='ad-section insidewide'>
	<div id="gb-inside-full-pos2-multidevice"></div>
	<script src="https://ch.prod.gbads.io/prod/loader/wegwandern.ch.loader.js"  data-slot="inside-full-pos2-multidevice"  crossorigin>
	</script>
		 </div>
		 </div>
	
	<?php	
}	


// 3. pos

if ( get_field('ads_3') ) {

	if( get_field('ads_3') == '994x250' ) {
	
			//if desktop ad script is 994x250 ?>
			<div class='ad-section-wrap ad-block-content-desktop-wrapper full-width'>
				 <p>Anzeige </p>
				 <div class='ad-section insidewide-3'></div>
			</div>
	
			
		<?php	
		}
		
		if( get_field('ads_3') == 'justmobile' ) { ?>
			
			<div class="ad-section-wrap ad-block-content-desktop-wrapper justmobile">
				<p>Anzeige</p>
				<div class="ad-section justmobile-3"></div>
			</div>
			
		<?php	
		}
}	

// 4. pos

if ( get_field('ads_4') ) {

	if( get_field('ads_4') == '994x250' ) {
	
			//if desktop ad script is 994x250 ?>
			<div class='ad-section-wrap ad-block-content-desktop-wrapper full-width'>
				 <p>Anzeige </p>
				 <div class='ad-section insidewide-4'></div>
			</div>
	
			
		<?php	
		}
		
		if( get_field('ads_4') == 'justmobile' ) { ?>
			
			<div class="ad-section-wrap ad-block-content-desktop-wrapper justmobile">
				<p>Anzeige</p>
				<div class="ad-section justmobile-4"></div>
			</div>
			
		<?php	
		}
}	



	




//echo $ads_html;