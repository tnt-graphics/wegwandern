<?php
/**
 * Wanderung Ads Section
 **/

if( get_field('desktop_ad') ) {

	if( get_field('desktop_ad') == '994x250' ) {
	
			//if desktop ad script is 994x250 ?>
			<div class='ad-section-wrap ad-block-content-desktop-wrapper full-width'>
			 	<p>Anzeige </p>
			 	<div class='ad-section insidewide'></div>
			</div>
			<script>
			 	// Define a function to execute on load and resize
			function loadAdInside() {
				var windowWidth = $(window).width();
				$('.ad-section.insidewide').empty();
			 	
			 	if (windowWidth > 900) {
			   	
			   	// Create a div with the ID 'div-ad-gds-1280-1'
			   	var adDiv = $('<div>', { id: 'div-ad-gds-1280-4' });
			 	
			   	// Create a script element and set its type and content
			   	var adScript = document.createElement('script');
			   	adScript.type = 'text/javascript';
			   	adScript.innerHTML = 'gbcallslot1280("div-ad-gds-1280-4", "");';
			 	
			   	// Append the script to the created div
			   	adDiv.append(adScript);
			 	
			   	// Append the created div with the script to the '.ad-section' div
			   	$('.ad-section.insidewide').html(adDiv);
			 	} else if (windowWidth > 700) {
			 	
					// Create a div with the ID 'div-ad-gds-1280-1'
					var adDiv = $('<div>', { id: 'div-ad-gds-4440-1' });
			  	
					// Create a script element and set its type and content
					var adScript = document.createElement('script');
					adScript.type = 'text/javascript';
					adScript.innerHTML = 'gbcallslot4440("div-ad-gds-4440-1", "");';
			  	
					// Append the script to the created div
					adDiv.append(adScript);
			  	
					// Append the created div with the script to the '.ad-section' div
					$('.ad-section.insidewide').html(adDiv);
			  	} else if (windowWidth < 700) {
			   	
				  	// Create a div with the ID 'div-ad-gds-1280-1'
				  	var adDiv = $('<div>', { id: 'div-ad-gds-1281-2' });
					
				  	// Create a script element and set its type and content
				  	var adScript = document.createElement('script');
				  	adScript.type = 'text/javascript';
				  	adScript.innerHTML = 'gbcallslot1281("div-ad-gds-1281-2", "");';
					
				  	// Append the script to the created div
				  	adDiv.append(adScript);
					
				  	// Append the created div with the script to the '.ad-section' div
				  	$('.ad-section.insidewide').html(adDiv);
					} 
			
			}
			 	
			 	// Execute the function on page load
			 	jQuery(document).ready(function(){
			   	loadAdInside();
			 	});
			 	
				
			 	
			</script>
	
			
		<?php	
		}
		
		if( get_field('desktop_ad') == '300x600' ) { ?>
			
			<div class="ad-section-wrap ad-block-content-desktop-wrapper rightside">
				<p>Anzeige</p>
				<div class="ad-section rightside"></div>
			</div>
			
			<script>
			 	// Define a function to execute on load and resize
			function loadAdRightside() {
				var windowWidth = $(window).width();
				$('.ad-section.rightside').empty();
			 	
			 	if (windowWidth > 1199) {
			   	
			   	// Create a div with the ID 'div-ad-gds-1280-1'
			   	var adDiv = $('<div>', { id: 'div-ad-gds-1280-2' });
			 	
			   	// Create a script element and set its type and content
			   	var adScript = document.createElement('script');
			   	adScript.type = 'text/javascript';
			   	adScript.innerHTML = 'gbcallslot1280("div-ad-gds-1280-2", "");';
			 	
			   	// Append the script to the created div
			   	adDiv.append(adScript);
			 	
			   	// Append the created div with the script to the '.ad-section' div
			   	$('.ad-section.rightside').html(adDiv);
			 	} else  {
			   		$('.ad-section.rightside').empty();
				} 
			
			}
			 	
			 	// Execute the function on page load
			 	jQuery(document).ready(function(){
			   	loadAdRightside();
			 	});
	
			 	
			</script>
	
		<?php	
		}
		
		if( get_field('desktop_ad') == 'justmobile' ) { ?>
			
			<div class="ad-section-wrap ad-block-content-desktop-wrapper justmobile">
				<p>Anzeige</p>
				<div class="ad-section justmobile"></div>
			</div>
			
			<script>
			 	// Define a function to execute on load and resize
			function loadAdJustmobile() {
				var windowWidth = $(window).width();
				$('.ad-section.justmobile').empty();
			 	
			 	if (windowWidth < 700) {
			   	
			   	// Create a div with the ID 'div-ad-gds-1280-1'
			   	var adDiv = $('<div>', { id: 'div-ad-gds-1281-2' });
			 	
			   	// Create a script element and set its type and content
			   	var adScript = document.createElement('script');
			   	adScript.type = 'text/javascript';
			   	adScript.innerHTML = 'gbcallslot1281("div-ad-gds-1281-2", "");';
			 	
			   	// Append the script to the created div
			   	adDiv.append(adScript);
			 	
			   	// Append the created div with the script to the '.ad-section' div
			   	$('.ad-section.justmobile').html(adDiv);
			 	} else  {
					$('.ad-section.justmobile').empty();
				} 
			
			}
			 	
			 	// Execute the function on page load
			 	jQuery(document).ready(function(){
			   	loadAdJustmobile();
			 	});
		
			 	
			</script>
		
		<?php	
		}
}	


// 2. pos

if ( get_field('ads_2') ) {

	if( get_field('ads_2') == '994x250' ) {
	
			//if desktop ad script is 994x250 ?>
			<div class='ad-section-wrap ad-block-content-desktop-wrapper full-width'>
				 <p>Anzeige </p>
				 <div class='ad-section insidewide-2'></div>
			</div>
			<script>
				 // Define a function to execute on load and resize
			function loadAdInside2() {
				var windowWidth = $(window).width();
				$('.ad-section.insidewide-2').empty();
				 
				 if (windowWidth > 900) {
				   
				   // Create a div with the ID 'div-ad-gds-1280-1'
				   var adDiv = $('<div>', { id: 'div-ad-gds-1280-5' });
				 
				   // Create a script element and set its type and content
				   var adScript = document.createElement('script');
				   adScript.type = 'text/javascript';
				   adScript.innerHTML = 'gbcallslot1280("div-ad-gds-1280-5", "");';
				 
				   // Append the script to the created div
				   adDiv.append(adScript);
				 
				   // Append the created div with the script to the '.ad-section' div
				   $('.ad-section.insidewide-2').html(adDiv);
				 } else if (windowWidth > 700) {
				 
					// Create a div with the ID 'div-ad-gds-1280-1'
					var adDiv = $('<div>', { id: 'div-ad-gds-4440-2' });
				  
					// Create a script element and set its type and content
					var adScript = document.createElement('script');
					adScript.type = 'text/javascript';
					adScript.innerHTML = 'gbcallslot4440("div-ad-gds-4440-2", "");';
				  
					// Append the script to the created div
					adDiv.append(adScript);
				  
					// Append the created div with the script to the '.ad-section' div
					$('.ad-section.insidewide-2').html(adDiv);
				  } else if (windowWidth < 700) {
				   
					  // Create a div with the ID 'div-ad-gds-1280-1'
					  var adDiv = $('<div>', { id: 'div-ad-gds-1281-3' });
					
					  // Create a script element and set its type and content
					  var adScript = document.createElement('script');
					  adScript.type = 'text/javascript';
					  adScript.innerHTML = 'gbcallslot1281("div-ad-gds-1281-3", "");';
					
					  // Append the script to the created div
					  adDiv.append(adScript);
					
					  // Append the created div with the script to the '.ad-section' div
					  $('.ad-section.insidewide-2').html(adDiv);
					} 
			
			}
				 
				 // Execute the function on page load
				jQuery(document).ready(function(){
				   loadAdInside2();
				 });
				 
				
				 
			</script>
	
			
		<?php	
		}
		
		if( get_field('ads_2') == 'justmobile' ) { ?>
			
			<div class="ad-section-wrap ad-block-content-desktop-wrapper justmobile">
				<p>Anzeige</p>
				<div class="ad-section justmobile-2"></div>
			</div>
			
			<script>
				 // Define a function to execute on load and resize
			function loadAdJustmobile2() {
				var windowWidth = $(window).width();
				$('.ad-section.justmobile-2').empty();
				 
				 if (windowWidth < 700) {
				   
				   // Create a div with the ID 'div-ad-gds-1280-1'
				   var adDiv = $('<div>', { id: 'div-ad-gds-1281-2' });
				 
				   // Create a script element and set its type and content
				   var adScript = document.createElement('script');
				   adScript.type = 'text/javascript';
				   adScript.innerHTML = 'gbcallslot1281("div-ad-gds-1281-2", "");';
				 
				   // Append the script to the created div
				   adDiv.append(adScript);
				 
				   // Append the created div with the script to the '.ad-section' div
				   $('.ad-section.justmobile-2').html(adDiv);
				 } else  {
					$('.ad-section.justmobile-2').empty();
				} 
			
			}
				 
				 // Execute the function on page load
				jQuery(document).ready(function(){
				   loadAdJustmobile2();
				 });
		
				 
			</script>
		
		<?php	
		}
}	


// 3. pos

if ( get_field('ads_3') ) {

	if( get_field('ads_3') == '994x250' ) {
	
			//if desktop ad script is 994x250 ?>
			<div class='ad-section-wrap ad-block-content-desktop-wrapper full-width'>
				 <p>Anzeige </p>
				 <div class='ad-section insidewide-3'></div>
			</div>
			<script>
				 // Define a function to execute on load and resize
			function loadAdInside3() {
				var windowWidth = $(window).width();
				$('.ad-section.insidewide-3').empty();
				 
				 if (windowWidth > 900) {
				   
				   // Create a div with the ID 'div-ad-gds-1280-1'
				   var adDiv = $('<div>', { id: 'div-ad-gds-1280-6' });
				 
				   // Create a script element and set its type and content
				   var adScript = document.createElement('script');
				   adScript.type = 'text/javascript';
				   adScript.innerHTML = 'gbcallslot1280("div-ad-gds-1280-6", "");';
				 
				   // Append the script to the created div
				   adDiv.append(adScript);
				 
				   // Append the created div with the script to the '.ad-section' div
				   $('.ad-section.insidewide-3').html(adDiv);
				 } else if (windowWidth > 700) {
				 
					// Create a div with the ID 'div-ad-gds-1280-1'
					var adDiv = $('<div>', { id: 'div-ad-gds-4440-3' });
				  
					// Create a script element and set its type and content
					var adScript = document.createElement('script');
					adScript.type = 'text/javascript';
					adScript.innerHTML = 'gbcallslot4440("div-ad-gds-4440-3", "");';
				  
					// Append the script to the created div
					adDiv.append(adScript);
				  
					// Append the created div with the script to the '.ad-section' div
					$('.ad-section.insidewide-3').html(adDiv);
				  } else if (windowWidth < 700) {
				   
					  // Create a div with the ID 'div-ad-gds-1280-1'
					  var adDiv = $('<div>', { id: 'div-ad-gds-1281-4' });
					
					  // Create a script element and set its type and content
					  var adScript = document.createElement('script');
					  adScript.type = 'text/javascript';
					  adScript.innerHTML = 'gbcallslot1281("div-ad-gds-1281-4", "");';
					
					  // Append the script to the created div
					  adDiv.append(adScript);
					
					  // Append the created div with the script to the '.ad-section' div
					  $('.ad-section.insidewide-3').html(adDiv);
					} 
			
			}
				 
				 // Execute the function on page load
				jQuery(document).ready(function(){
				   loadAdInside3();
				 });
				 
				
				 
			</script>
	
			
		<?php	
		}
		
		if( get_field('ads_3') == 'justmobile' ) { ?>
			
			<div class="ad-section-wrap ad-block-content-desktop-wrapper justmobile">
				<p>Anzeige</p>
				<div class="ad-section justmobile-3"></div>
			</div>
			
			<script>
				 // Define a function to execute on load and resize
			function loadAdJustmobile3() {
				var windowWidth = $(window).width();
				$('.ad-section.justmobile-3').empty();
				 
				 if (windowWidth < 700) {
				   
				   // Create a div with the ID 'div-ad-gds-1280-1'
				   var adDiv = $('<div>', { id: 'div-ad-gds-1281-4' });
				 
				   // Create a script element and set its type and content
				   var adScript = document.createElement('script');
				   adScript.type = 'text/javascript';
				   adScript.innerHTML = 'gbcallslot1281("div-ad-gds-1281-4", "");';
				 
				   // Append the script to the created div
				   adDiv.append(adScript);
				 
				   // Append the created div with the script to the '.ad-section' div
				   $('.ad-section.justmobile-3').html(adDiv);
				 } else  {
					$('.ad-section.justmobile-3').empty();
				} 
			
			}
				 
				 // Execute the function on page load
				jQuery(document).ready(function(){
				   loadAdJustmobile3();
				 });
		
				 
			</script>
		
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
			<script>
				 // Define a function to execute on load and resize
			function loadAdInside4() {
				var windowWidth = $(window).width();
				$('.ad-section.insidewide-4').empty();
				 
				 if (windowWidth > 900) {
				   
				   // Create a div with the ID 'div-ad-gds-1280-1'
				   var adDiv = $('<div>', { id: 'div-ad-gds-1280-7' });
				 
				   // Create a script element and set its type and content
				   var adScript = document.createElement('script');
				   adScript.type = 'text/javascript';
				   adScript.innerHTML = 'gbcallslot1280("div-ad-gds-1280-7", "");';
				 
				   // Append the script to the created div
				   adDiv.append(adScript);
				 
				   // Append the created div with the script to the '.ad-section' div
				   $('.ad-section.insidewide-4').html(adDiv);
				 } else if (windowWidth > 700) {
				 
					// Create a div with the ID 'div-ad-gds-1280-1'
					var adDiv = $('<div>', { id: 'div-ad-gds-4440-4' });
				  
					// Create a script element and set its type and content
					var adScript = document.createElement('script');
					adScript.type = 'text/javascript';
					adScript.innerHTML = 'gbcallslot4440("div-ad-gds-4440-4", "");';
				  
					// Append the script to the created div
					adDiv.append(adScript);
				  
					// Append the created div with the script to the '.ad-section' div
					$('.ad-section.insidewide-4').html(adDiv);
				  } else if (windowWidth < 700) {
				   
					  // Create a div with the ID 'div-ad-gds-1280-1'
					  var adDiv = $('<div>', { id: 'div-ad-gds-1281-5' });
					
					  // Create a script element and set its type and content
					  var adScript = document.createElement('script');
					  adScript.type = 'text/javascript';
					  adScript.innerHTML = 'gbcallslot1281("div-ad-gds-1281-5", "");';
					
					  // Append the script to the created div
					  adDiv.append(adScript);
					
					  // Append the created div with the script to the '.ad-section' div
					  $('.ad-section.insidewide-4').html(adDiv);
					} 
			
			}
				 
				 // Execute the function on page load
				jQuery(document).ready(function(){
				   loadAdInside4();
				 });
				 
				
				 
			</script>
	
			
		<?php	
		}
		
		if( get_field('ads_4') == 'justmobile' ) { ?>
			
			<div class="ad-section-wrap ad-block-content-desktop-wrapper justmobile">
				<p>Anzeige</p>
				<div class="ad-section justmobile-4"></div>
			</div>
			
			<script>
				 // Define a function to execute on load and resize
			function loadAdJustmobile4() {
				var windowWidth = $(window).width();
				$('.ad-section.justmobile-4').empty();
				 
				 if (windowWidth < 700) {
				   
				   // Create a div with the ID 'div-ad-gds-1280-1'
				   var adDiv = $('<div>', { id: 'div-ad-gds-1281-5' });
				 
				   // Create a script element and set its type and content
				   var adScript = document.createElement('script');
				   adScript.type = 'text/javascript';
				   adScript.innerHTML = 'gbcallslot1281("div-ad-gds-1281-5", "");';
				 
				   // Append the script to the created div
				   adDiv.append(adScript);
				 
				   // Append the created div with the script to the '.ad-section' div
				   $('.ad-section.justmobile-4').html(adDiv);
				 } else  {
					$('.ad-section.justmobile-4').empty();
				} 
			
			}
				 
				 // Execute the function on page load
				jQuery(document).ready(function(){
				   loadAdJustmobile4();
				 });
		
				 
			</script>
		
		<?php	
		}
}	



	




//echo $ads_html;