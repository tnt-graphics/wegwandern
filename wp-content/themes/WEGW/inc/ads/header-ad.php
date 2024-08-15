<?php 
/**
 * Display Header Ad's
 *
 * @package Wegwandern
 */
 
 /* Header ads scripts */

 ?>
 

 
 <div class='ad-above-header-container header-ad-desktop-wrapper'>
	<div class="ad-section header"></div>
  
	  <div class="close_ad" onclick="adCloseHeader()"><span class="close_ad_icon"></span></div>
	  <div class="ad-scroll-btn">
		  <div class="ad-scroll-btn-icon"></div>
		  <div class="ad-scroll-btn-text" onclick="adScrollToHeader()">Scrolle zu WegWandern.ch</div>
	  </div>
  </div>
  
  <script type="text/javascript">
	window.addEventListener('message', function(event) {
	  if (event.data.type === 'connector' && event.data.name === 'codevelop') {
		var script = document.createElement('script');
		script.async = true;
		script.src = 'https://bcdn.codevelop.network/static/adformats/global/publisherHelper.js';
		document.head.appendChild(script);
	  }
	}, false);
  </script>
  
  <script>
	 // Define a function to execute on load and resize
function loadAndResizeFunction() {
	var windowWidth = $(window).width();
	$('.ad-section.header').empty();
	 
	 if (windowWidth > 900) {
	
	   // Create a div with the ID 'div-ad-gds-1280-1'
	   var adDiv = $('<div>', { id: 'div-ad-gds-1280-1' });
	 
	   // Create a script element and set its type and content
	   var adScript = document.createElement('script');
	   adScript.type = 'text/javascript';
	   adScript.innerHTML = 'gbcallslot1280("div-ad-gds-1280-1", "");';
	 
	   // Append the script to the created div
	   adDiv.append(adScript);
	 
	   // Append the created div with the script to the '.ad-section' div
	   $('.ad-section.header').html(adDiv);
	 } else if (windowWidth < 900) {
		// Create a div with the ID 'div-ad-gds-1280-1'
		var adDiv = $('<div>', { id: 'div-ad-gds-1281-1' });
	  
		// Create a script element and set its type and content
		var adScript = document.createElement('script');
		adScript.type = 'text/javascript';
		adScript.innerHTML = 'gbcallslot1281("div-ad-gds-1281-1", "");';
	  
		// Append the script to the created div
		adDiv.append(adScript);
	  
		// Append the created div with the script to the '.ad-section' div
		$('.ad-section.header').html(adDiv);
	  } 
}
	 
	// Execute the function on page load
	jQuery(document).ready(function(){
	   loadAndResizeFunction();
	});
	 
	// Execute the function on window resize
	jQuery(window).on('resize', function(){
	  // loadAndResizeFunction();
	});
   </script>