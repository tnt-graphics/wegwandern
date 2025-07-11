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

	<!-- <div class="close_ad" onclick="adCloseHeader()"><span class="close_ad_icon"></span></div> 
	<div class="ad-scroll-btn">
		<div class="ad-scroll-btn-icon"></div>
		<div class="ad-scroll-btn-text" onclick="adScrollToHeader()">Scrolle zu WegWandern.ch</div>
	</div> -->
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
			// Create the new ad structure for big screens
			//var adCloseBtn = '<div class="close_ad" onclick="adCloseHeader()"><span class="close_ad_icon"></span></div>';
			var adDiv = '<div id="gb-div-ad-gds-1280-1"></div>';
			
			// Create the ad container
			$('.ad-section.header').html(adCloseBtn + adDiv);
			
			// Create and append the script element properly
			var adScript = document.createElement('script');
			adScript.src = 'https://ch.prod.gbads.io/prod/loader/wegwandern.ch.loader.js';
			adScript.setAttribute('data-slot', 'div-ad-gds-1280-1');
			adScript.crossOrigin = '';
			document.head.appendChild(adScript);
			
		} else if (windowWidth < 900) {
			// Create the new ad structure for mobile screens
			//var adCloseBtn = '<div class="close_ad" onclick="adCloseHeader()"><span class="close_ad_icon"></span></div>';
			var adDiv = '<div id="gb-div-ad-gds-1281-1"></div>';
			
			// Create the ad container
			$('.ad-section.header').html(adCloseBtn + adDiv);
			
			// Create and append the script element properly
			var adScript = document.createElement('script');
			adScript.src = 'https://ch.prod.gbads.io/prod/loader/wegwandern.ch.loader.js';
			adScript.setAttribute('data-slot', 'div-ad-gds-1281-1');
			adScript.crossOrigin = '';
			document.head.appendChild(adScript);
		}
	}

	// Execute the function on page load
	jQuery(document).ready(function() {
		loadAndResizeFunction();
	});

	// Execute the function on window resize
	jQuery(window).on('resize', function() {
		loadAndResizeFunction();
	});
</script>