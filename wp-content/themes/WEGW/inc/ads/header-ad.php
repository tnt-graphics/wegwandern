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

	<!-- <div class="close_ad" onclick="adCloseHeader()"><span class="close_ad_icon"></span></div> -->
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
	// Track if ad was recently closed
	var adRecentlyClosed = false;
	var lastAdCloseTime = 0;
	
	// Override the adCloseHeader function to track when ads are closed
	var originalAdCloseHeader = window.adCloseHeader;
	window.adCloseHeader = function() {
		adRecentlyClosed = true;
		lastAdCloseTime = Date.now();
		console.log('Ad closed at:', new Date().toISOString());
		
		// Call the original function
		if (originalAdCloseHeader) {
			originalAdCloseHeader();
		} else {
			jQuery('.ad-above-header-container').addClass("hide");
			jQuery('body').removeClass('Top');
		}
	};

	// Define a function to execute on load and resize
	function loadAndResizeFunction() {
		var windowWidth = $(window).width();
		$('.ad-section.header').empty();

		// Check if ad was recently closed (within last 30 seconds)
		var timeSinceLastClose = Date.now() - lastAdCloseTime;
		if (adRecentlyClosed && timeSinceLastClose < 30000) {
			console.log('Ad was recently closed, waiting 2 seconds before loading new ad...');
			setTimeout(function() {
				loadAndResizeFunction();
			}, 2000);
			return;
		}

		if (windowWidth > 900) {
			// Create the new ad structure for big screens
			var adCloseBtn = '<div class="close_ad" onclick="adCloseHeader()"><span class="close_ad_icon"></span></div>';
			var adDiv = '<div id="gb-div-ad-gds-1280-1"></div>';
			
			// Create the ad container
			$('.ad-section.header').html(adCloseBtn + adDiv);
			
			// Create and append the script element properly
			var adScript = document.createElement('script');
			adScript.src = 'https://ch.prod.gbads.io/prod/loader/wegwandern.ch.loader.js';
			adScript.setAttribute('data-slot', 'div-ad-gds-1280-1');
			adScript.crossOrigin = '';
			
			// Add error handling and debugging
			adScript.onload = function() {
				console.log('Desktop ad script loaded successfully');
				// Check if ad content loads within 10 seconds
				setTimeout(function() {
					var adContainer = document.getElementById('gb-div-ad-gds-1280-1');
					if (adContainer && adContainer.innerHTML.trim() === '') {
						console.warn('Desktop ad container is empty after 10 seconds');
					} else if (adContainer) {
						console.log('Desktop ad content loaded successfully');
						adRecentlyClosed = false; // Reset the flag when ad loads successfully
					}
				}, 10000);
			};
			
			adScript.onerror = function() {
				console.error('Failed to load desktop ad script');
			};
			
			document.head.appendChild(adScript);
			
		} else if (windowWidth < 900) {
			// Create the new ad structure for mobile screens
			var adCloseBtn = '<div class="close_ad" onclick="adCloseHeader()"><span class="close_ad_icon"></span></div>';
			var adDiv = '<div id="gb-div-ad-gds-1281-1"></div>';
			
			// Create the ad container
			$('.ad-section.header').html(adCloseBtn + adDiv);
			
			// Create and append the script element properly
			var adScript = document.createElement('script');
			adScript.src = 'https://ch.prod.gbads.io/prod/loader/wegwandern.ch.loader.js';
			adScript.setAttribute('data-slot', 'div-ad-gds-1281-1');
			adScript.crossOrigin = '';
			
			// Add error handling and debugging
			adScript.onload = function() {
				console.log('Mobile ad script loaded successfully');
				// Check if ad content loads within 10 seconds
				setTimeout(function() {
					var adContainer = document.getElementById('gb-div-ad-gds-1281-1');
					if (adContainer && adContainer.innerHTML.trim() === '') {
						console.warn('Mobile ad container is empty after 10 seconds');
					} else if (adContainer) {
						console.log('Mobile ad content loaded successfully');
						adRecentlyClosed = false; // Reset the flag when ad loads successfully
					}
				}, 10000);
			};
			
			adScript.onerror = function() {
				console.error('Failed to load mobile ad script');
			};
			
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