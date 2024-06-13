if (typeof $ == 'undefined') {
    var $ = jQuery;
}
   
jQuery(document).ready(function($) { 
    /**ad based on the screen width */
 //   renderAd();
 stickyHeaderforAdAboveHeader();
    /** append download to the a tag who parent has class name is-style-download-button */
    var parentDivs = document.querySelectorAll('.is-style-download-button');

    parentDivs.forEach(function(parentDiv) {
        var link = parentDiv.querySelector('a');
        link.setAttribute('download', '');
    });
    var windowWidth = window.innerWidth;
    var thumbWidth = getThumbWidth(windowWidth);

    /**light gallery in hike detail page */  
    if(jQuery(".demo-gallery").length > 0){
        lightGallery(document.getElementById('lightgallery'), {
            plugins: [lgThumbnail, lgComment, lgFullscreen, lgAutoplay, lgZoom],
            speed: 500,
            thumbnail:true,
            thumbHeight:"72px",
            thumbWidth: thumbWidth,
            thumbMargin:10,
            toggleThumb:true,
            download:false,
            allowMediaOverlap: true,
            showZoomInOutIcons:true,
            appendThumbnailsTo:".lg-components",
            showThumbByDefault: false,
            enableThumbDrag:true,
            enableThumbSwipe : true,
            mobileSettings:{ controls: false, showCloseIcon: true, fullScreen: true, toggleThumb:false, allowMediaOverlap: false}

        });
        
    }

    /**light gallery for master content page */ 
     var elements = document.getElementsByClassName('img-gallery-wrap');
     
    for (let item of elements) {
        lightGallery(item, {
            plugins: [lgThumbnail, lgComment, lgFullscreen, lgAutoplay, lgZoom],
            speed: 500,
            thumbnail:true,
            thumbHeight:"72px",
            thumbWidth: thumbWidth,
            thumbMargin:10,
            toggleThumb:true,
            download:false,
            allowMediaOverlap: true,
            showZoomInOutIcons:true,
            appendThumbnailsTo:".lg-components",
            showThumbByDefault: false,
            enableThumbDrag:true,
            enableThumbSwipe : true,
            mobileSettings:{ controls: false, showCloseIcon: true, fullScreen: true, toggleThumb:false, allowMediaOverlap: false}

        });
    }

    /*accordion*/
    var acc = document.getElementsByClassName("accordion");
    var i;

    for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function() {
            /* Toggle between adding and removing the "active" class,
            to highlight the button that controls the panel */
            this.classList.toggle("active");

            /* Toggle between hiding and showing the active panel */
            var panel = this.nextElementSibling;
            if(this.parentElement.className === 'botom_layer_icon'){
                if (panel.style.display === "block") {
                    panel.style.display = "none";
                } else {
                    panel.style.display = "block";
                }
            }else{
                if (panel.style.maxHeight) {
                    panel.style.maxHeight = null;
                } else {
                    panel.style.maxHeight = panel.scrollHeight + "px";
                }
            }
        });
    }
    

     /*menu*/
    var acc = jQuery("#menu-main-menu .menu-item.menu-item-has-children");
    var i;

    for (i = 0; i < acc.length; i++) {
    var panel = acc[i].children[1];
        if (acc[i].classList.contains('active')) {
                panel.style.display = "block";
        } else {
               panel.style.display = "none";
        }
        acc[i].addEventListener("click", function() {
            /* Toggle between adding and removing the "active" class,
            to highlight the button that controls the panel */
            // this.classList.toggle("active");
            // var panel = this.children[1];
            // /* Toggle between hiding and showing the active panel */
            // if (this.classList.contains('active')) {
            //     panel.style.display = "block";
            // } else {
            //    panel.style.display = "none";
            // }

            jQuery(this).toggleClass("active");
            var panel = jQuery(this).children(":eq(1)");
            
            /* Toggle between hiding and showing the active panel with a smooth fade down effect */
            panel.slideToggle(300);

        });
    }
    
    jQuery('.elevationGraph').on('click', function(){
        $('.options').toggleClass('open');
    });
    jQuery('#mapOptions').on('click', function(event) {
        if (event.target.id === 'mapOptions') {
          this.classList.remove('open');
        }
      });


    //zoom in/out of map
    jQuery(".ol-zoom .ol-zoom-in").html("");
    jQuery(".ol-zoom .ol-zoom-out").html("");
    jQuery(".ol-rotate .ol-rotate-reset .ol-compass").html("");
    jQuery("#map_desktop .ol-full-screen .ol-full-screen-false").html("");
    jQuery("#map_desktop .ol-full-screen .ol-full-screen-true").html("");
    jQuery("#map_desktop .ol-rotate .ol-rotate-reset .ol-compass").html("");

    /*popup dragable in different map layer (elevation map)*/
    jQuery('.popover').draggable({
        handle: ".popover-title"
    });

    /*show hide of the activity in filter sidebar*/
    jQuery('.fc_check_wrap .check_wrapper input').on('click', function(){
        if(this.value === '20' ) {
            if(jQuery(".activity_type_1").prop('checked') === true){
            jQuery(".activity_type_2").prop('checked', false ); 
                jQuery(".activity_type_3").prop('checked', false ); 
                jQuery(".fc_difficult_wt_block").removeClass('hide');
                jQuery(".fc_difficult_t_block").addClass('hide');
                jQuery('.fc_heading.fc_diff_level').removeClass('fc_devel_default');
                jQuery('.fc_heading.fc_diff_level .fc_block_select_wrapper .fc_difficult_t_block.fc_block_select label').removeClass('active');
            }else{
                clearActivityCheck();
                
            }
                
        }else if(this.value === '18' ) {
            if(jQuery(".activity_type_2").prop('checked') === true){
            jQuery(".activity_type_1").prop('checked', false ); 
                jQuery(".activity_type_3").prop('checked', false ); 
                jQuery(".fc_difficult_t_block").removeClass('hide');
                jQuery(".fc_difficult_wt_block").addClass('hide');
                jQuery('.fc_heading.fc_diff_level').removeClass('fc_devel_default');
                jQuery('.fc_heading.fc_diff_level .fc_block_select_wrapper .fc_difficult_wt_block.fc_block_select label').removeClass('active');
            }else{
                clearActivityCheck();
                
            }
                
        }else if(this.value === '19' ) {
        
                jQuery(".activity_type_1").prop('checked', false );
                jQuery(".activity_type_2").prop('checked', false ); 
                jQuery(".fc_difficult_wt_block").removeClass('hide');
                jQuery(".fc_difficult_t_block").removeClass('hide');
                jQuery('.fc_heading.fc_diff_level').addClass('fc_devel_default');
                jQuery('.fc_heading.fc_diff_level .fc_block_select_wrapper .fc_block_select label').removeClass('active');
        }
    });

    /**region page nav slider below featured image */
    $(".mainNav_region").owlCarousel({       
        loop: false,
        autoWidth:true,
        dots:false,
        nav:false,
         autoplay:false,
         responsive : {
            0 : {
              items:1,
            },
            700 : {
             items:1,
            },
            900 : {
              items:1,
            },
            1200 : {
                items:1,
                
            }
        }
    });
   
    /**Region page teaser slider */
    jQuery('.owl-carousel.region_teaser_wrap').owlCarousel({
        nav:true,
        dots:false,
        loop:false,
        rewindNav:false,
        autoplay:false,
        navText: ["<div class='nav-btn prev-slide'></div>","<div class='nav-btn next-slide'></div>"],
        responsive : {
            0 : {
              margin:16, 
              items:1,
               nav:false,
                stagePadding: 43
            },
            700 : {
              margin:7, 
             items:3,
               nav:false,
               stagePadding: 42
            },
            900 : {
              margin:9, 
              items:3,
                 stagePadding: 72
            },
            1200 : {
              margin:12,
                items:3,
                 stagePadding: 100
                
            }
        }
    });

    /**Region icon slider slider */
    jQuery('.region_icon_slider').owlCarousel({
        nav:true,
        dots:false,
        loop:false,
        rewindNav:false,
        autoplay:false,
        navText: ["<div class='nav-btn prev-slide'></div>","<div class='nav-btn next-slide'></div>"],
        responsive : {
            0 : {
              margin:8, 
              items:2,
               nav:false,
                stagePadding: 21
            },
            700 : {
              margin:7, 
             items:4,
               nav:false,
               stagePadding: 72
            },
            900 : {
              margin:9, 
              items:5,
                 stagePadding: 56
            },
            1200 : {
              margin:12,
                items:6,
                 stagePadding: 87
                
            }
        }
    });

    /** carousel for lightgallery as in master content(to make it as a slider if there are more than one image) */
    for (let galleyItem of elements) {
        if(galleyItem.children.length > 1){
            jQuery(galleyItem).owlCarousel({
                items:1,
                lazyLoad:true,
                loop:true,
                dots:false,
                nav:true,
                navText: ["<div class='nav-btn gal_prev-slide'></div>","<div class='nav-btn gal_next-slide'></div>"],
                autoplay: 2000,
                onInitialized  : counter, 
                onTranslated : counter,
                afterMove: function(elem) {
                    var current = this.currentItem;
                    var currentImg = elem.find('.owl-item').eq(current).find('.justified-gallery');
                    jQuery('.figcaption').text(currentImg.attr('data-sub-html'));
                }
            });
        }
        /** to show the caption of light gallery as in masdter content page just below the image */
        if(galleyItem.children.length <= 1){
            var currentImg = jQuery(galleyItem).eq(0).find('.justified-gallery');
            jQuery(galleyItem).parent().parent().find('.figcaption').text(currentImg.attr('data-sub-html'));
        }
    }

    /**hike detail page region slider*/
    jQuery('.owl-carousel.wander-in-region-carousel').owlCarousel({
        nav:true,
        dots:false,
        loop:false,
        rewindNav:false,
        autoplay:false,
        navText: ["<div class='nav-btn prev-slide'></div>","<div class='nav-btn next-slide'></div>"],
        responsive : {
            0 : {
              margin:17, 
              items:1,
               nav:false,
                stagePadding: 43
            },
            700 : {
              margin:26, 
             items:2,
               nav:false,
               stagePadding: 92
            },
            900 : {
              margin:26, 
              items:3,
                 stagePadding: 49
            },
            1200 : {
              margin:36,
                items:3,
                 stagePadding: 115
                
            }
        }
    });
    //angebote slider
    jQuery('.angebote_slider').owlCarousel({
        nav:true,
        dots:false,
        loop:false,
        rewindNav:false,
        autoplay:false,
        navText: ["<div class='nav-btn prev-slide'></div>","<div class='nav-btn next-slide'></div>"],
        responsive : {
            0 : {
              margin:17, 
              items:1,
               nav:false,
                stagePadding: 43
            },
            700 : {
              margin:26, 
             items:2,
               nav:false,
               stagePadding: 92
            },
            900 : {
              margin:26, 
              items:3,
                 stagePadding: 49
            },
            1200 : {
              margin:36,
                items:3,
                 stagePadding: 115
                
            }
        }
    });

    /** Add class 'externalLink' for the a tag inside the master content page where there is external link */
    jQuery('.bild-text-content a[target="_blank"]').addClass('externalLink');
    jQuery('.angebote_slider a[target="_blank"]').addClass('externalLink');
    jQuery('.angebote_2col_wrapper a[target="_blank"]').addClass('externalLink');
    jQuery('.angebote_3col_wrapper a[target="_blank"]').addClass('externalLink');
    jQuery('.angebote_wrapper_7_3 a[target="_blank"]').addClass('externalLink');
    jQuery('.angebote_wrapper_5_5 a[target="_blank"]').addClass('externalLink');

    /**to remove empty hike info*/
    toRemoveEmptyHikeInfo();
});

function toRemoveEmptyHikeInfo(){
     // Remove "hike_level" div if "hike-level" child does not have a class
     jQuery('.hike_level').each(function() {
        var hikeLevelChild = jQuery(this).children().eq(0);
        if (!hikeLevelChild.attr('class').match(/(^|\s)hike-\S+/)) {
            $(this).remove();
          }
    });
    // Remove "hike_time" div if "hike-time" child p tag has only h
    jQuery('.hike_time').each(function() {
        var hikeTimeChild = jQuery(this).find('p');
        if (hikeTimeChild.text().trim() === 'h') {
            jQuery(this).remove();
        }
    });
    // Remove "hike_distance" div if "hike_distance" child p tag has only h
    jQuery('.hike_distance').each(function() {
        var hikeTimeChild = jQuery(this).find('p');
        if (hikeTimeChild.text().trim() === 'km') {
            jQuery(this).remove();
        }
    });
    // Remove "hike_ascent" div if "hike_ascent" child p tag has only h
    jQuery('.hike_ascent').each(function() {
        var hikeTimeChild = jQuery(this).find('p');
        if (hikeTimeChild.text().trim() === 'm') {
            jQuery(this).remove();
        }
    });
    // Remove "hike_descent" div if "hike_descent" child p tag has only h
    jQuery('.hike_descent').each(function() {
        var hikeTimeChild = jQuery(this).find('p');
        if (hikeTimeChild.text().trim() === 'm') {
            jQuery(this).remove();
        }
    });
    // Remove "hike_month" div if "hike_month" child p tag has only h
    jQuery('.hike_month').each(function() {
        var hikeTimeChild = jQuery(this).find('p');
        if (hikeTimeChild.text().trim() === '') {
            jQuery(this).remove();
        }
    });
}

/* slide count of the carousel slider*/
function counter(event) {
    var element   = event.target;         // DOM element, in this example .owl-carousel
    var items     = event.item.count;     // Number of items
    var item      = event.item.index + 1;     // Position of the current item
  
    // it loop is true then reset counter from 1
    if(item > items) {
        item = item - items
    }
    var current = event.item.index;
    var currentImg = jQuery(element).find('.owl-item').eq(current).find('.justified-gallery');
    jQuery(element).parent().parent().find('.figcaption').text(currentImg.attr('data-sub-html'));
    jQuery(element).parent().parent().find('#count').html(item+"/"+items);
}

/*close elevation map*/
function closeElement(element) {
    jQuery('#weg-map-popup').css("display", "none");
    jQuery('body').removeClass("weg_ele_popup_show");
    jQuery("#weg-map-popup .ol-viewport").addClass("hide");
    jQuery(".popover").css("display", "none");
}

/*close cluster map in responsive*/
function closeElementMapResp(element) {
    jQuery('#map-resp').addClass('hide');
    jQuery("#map-resp .ol-viewport").addClass('hide');
}

/*Open filter sidebar*/
function openFilter(event) {
   /* if(event !== undefined){
        if(event.classList.contains("region-filter")){
            $('#wegw_map_filter_btn_region').removeClass('hide');
            $('#wegw_map_filter_btn').addClass('hide');
        }
    }
    else{
        $('#wegw_map_filter_btn_region').addClass('hide');
        $('#wegw_map_filter_btn').removeClass('hide');
    }*/
    jQuery(".filterMenu").toggleClass("filterWindow");
}
/*close login sidebar*/
function closeSummitLoginContent(){
    jQuery(".summitLoginMenu").addClass("summitLoginWindow");
    if($('.home').length > 0){
        $('.login').toggleClass("loginHome");  
    }
}
/*close filter sidebar*/
function closeFilter() {
    jQuery(".filterMenu").addClass("filterWindow");
}
/*Open wanderungPlan sidebar*/
function openWanderungPlan() {
    jQuery(".wanderungPlanMenu").toggleClass("wanderungPlanWindow");
}
/*close wanderungPlan sidebar*/
function closeWanderungPlan() {
    jQuery(".wanderungPlanMenu").addClass("wanderungPlanWindow");
}
/*Open main menu*/
function openMainMenu() {
    jQuery(".main-menu").toggleClass("mainMenuWindow");
}
/*close main menu*/
function closeMainMenu() {
    jQuery(".main-menu").addClass("mainMenuWindow");
}

/*clear search filter*/
function clearSearchFilter() {
    jQuery(".filter_search.search input").val("");
    jQuery('.filter_search_close').addClass("hide");
    jQuery('.header_menu .search_head').val("");
}

/*sort dropdown*/
function openDropdown(element) {
   // jQuery(".sort_dropdown").toggleClass("show");
    // jQuery(".sort_dropdown").slideToggle(300);
    // jQuery(".ListSort").toggleClass("open");
    // jQuery(this).find(".sort_dropdown").toggleClass("showSort");

    // var panel = jQuery(this).find(".sort_dropdown");
    
    // for(i=0; i< panel.length; i++){

    //     if (panel[i].style.maxHeight) {
    //         // panel.style.maxHeight = null;
    //         jQuery(".sort_dropdown").css('max-height', "");
    //         jQuery(".sort_dropdown").css('padding', "0px");
    //      } else {
    //         jQuery(".sort_dropdown").css('padding', "20px");
    //          jQuery(".sort_dropdown").css('max-height', panel[i].scrollHeight + "px");
    //         // panel.style.maxHeight = panel.scrollHeight + "px";
             
    //      }
    // }
    var $dropdown = jQuery(element).find(".sort_dropdown");
    $dropdown.toggleClass("showSort");
  
    var panel = $dropdown[0];
  
    if (panel.style.maxHeight) {
      $dropdown.css('max-height', "");
     // $dropdown.css('padding', "0px");
    } else {
    //  $dropdown.css('padding', "20px");
      $dropdown.css('max-height', panel.scrollHeight + "px");
    }
    
}

/*popup in elevation map(different layer)*/
function closeTransportLayerPopup() {
    jQuery(".popover").css("display", "none");
}
/**to show lightgallery on the click of the fullscreen icon in lightgallery */
function openLightGallery(e){
    jQuery(e.nextElementSibling).find(".justified-gallery a:first-child > img").eq(0).trigger("click");
    jQuery(e.nextElementSibling).find(".owl-item.active > .justified-gallery a >img").eq(0).trigger("click"); 
   
}
// function renderAdBasedWidth(adSection){
//     //to show/hide close icon in the ad above header id ad is not displayed
  
//     const childDivs = adSection.querySelectorAll('div');
        
//         let hasNonZeroHeight = false;
        
//         for (let j = 0; j < childDivs.length; j++) {
//           const childDiv = childDivs[j];
          
//           // Check if the child div has non-zero height
//           const childDivHeight = childDiv.offsetHeight;
//           if (childDivHeight > 0) {
//             hasNonZeroHeight = true;
//             break;
//           }
//         }
//         // Add class to the ad section if child div has height
//         if (!hasNonZeroHeight) {
//             // adSection.classList.add('isAd');
//             jQuery('.close_ad').addClass('hide');
//          } else{
//              jQuery('.close_ad').removeClass('hide');
//            //  adSection.classList.remove('isAd');
//          }
    
// }
//  function renderAd(){
//     //check if its which resolution to show/hide close icon in the ad above header id ad is not displayed
//     if(window.innerWidth >= 1200){
//         const adSection = document.querySelector('.ad-above-header-container .ad-section.ad-desktop');
//         renderAdBasedWidth(adSection);
//     }
//     if(window.innerWidth >= 900 && window.innerWidth < 1200){
//         const adSection = document.querySelector('.ad-above-header-container .ad-section.ad-ipad');
//         renderAdBasedWidth(adSection);
//     }
//  }


/*loader*/
jQuery(window).on("load", function() {
    setTimeout(function() {
        jQuery("#wegw-preloader").css("display", "none");
    }, 1000);

    /**ad based on the screen width */
  //  renderAd();
  stickyHeaderforAdAboveHeader();
  
 // jQuery(window).scrollTop() > 100 ? $('body.home').addClass('sticky') : $('body.home').removeClass('sticky');

    /**cluster font family */
   /*let fontRoboto = new FontFace("Roboto", "url('../fonts/Roboto-Regular.ttf')");
    
            var canvas = document.getElementsByTagName('canvas');
            for(i=0;i<canvas.length;i++){
                 const ctx = canvas[i].getContext("2d");
                   ctx.font = '36px Roboto';
            }*/
});

/*clear activity filter*/
function clearActivityCheck(){
    jQuery(".activity_type_1").prop('checked', false );
    jQuery(".activity_type_2").prop('checked', false ); 
    jQuery(".activity_type_3").prop('checked', false );
    jQuery('.difficulty_search').prop('checked', false );
    jQuery(".fc_difficult_wt_block").removeClass('hide');
    jQuery(".fc_difficult_t_block").removeClass('hide');
    jQuery('.fc_heading.fc_diff_level').addClass('fc_devel_default');
    jQuery('.fc_heading.fc_diff_level .fc_block_select_wrapper .fc_block_select label').removeClass('active');
}
/**close the ad above the header */
function adCloseHeader(){
    jQuery('.ad-above-header-container').addClass("hide");
    jQuery('body').removeClass('TopAd');
}
/** scroll to header on the button click over the ad above the header */
function adScrollToHeader(){
    if(jQuery('.home').length > 0){
        jQuery('html, body').animate({
            scrollTop: jQuery('header').offset().top + 0
    }, 500);
    }else{
        jQuery('html, body').animate({
            scrollTop: jQuery('header').offset().top + 2
    }, 500);
    }
    
}

jQuery(window).scroll(function (elem) {
    var headerHeight = $('header').outerHeight();
    var scrollTop = $(this).scrollTop();
    var adContainer;
    var adContainerHeight =0;
    
  //  jQuery(window).scrollTop() > 100 ? $('body.home').addClass('sticky') : $('body.home').removeClass('sticky');

    /**ad above the header */
    if(window.innerWidth >= 1200 /*&& jQuery('.ad-above-header-container.header-ad-desktop-wrapper').length > 0*/){
        if(jQuery('.ad-above-header-container.header-ad-desktop-wrapper').length > 0){
        adContainer = $('.ad-above-header-container.header-ad-desktop-wrapper');
        if(!jQuery('.ad-above-header-container.header-ad-desktop-wrapper').hasClass('hide')){
            var myElement = jQuery('.ad-above-header-container.header-ad-desktop-wrapper')[0];
            var bounding = myElement.getBoundingClientRect();
            var myElementHeight = myElement.offsetHeight;
            var myElementWidth = myElement.offsetWidth;

        
            if (bounding.top >= -myElementHeight 
                && bounding.left >= -myElementWidth
                && bounding.right <= (window.innerWidth || document.documentElement.clientWidth) + myElementWidth
                && bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight) + myElementHeight) {

                jQuery('body').addClass('TopAd');
            } else {
                jQuery('body').removeClass('TopAd');
            }
        }
        adContainerHeight = adContainer.offset().top + adContainer.outerHeight();
        }else{
            jQuery('body').removeClass('TopAd');
        }
    }
    else if(window.innerWidth < 1200 && window.innerWidth >= 900 /*&& jQuery('.ad-above-header-container.header-ad-tablet-wrapper').length > 0*/){
        if(jQuery('.ad-above-header-container.header-ad-tablet-wrapper').length > 0){
        adContainer = $('.ad-above-header-container.header-ad-tablet-wrapper');
        if(!jQuery('.ad-above-header-container.header-ad-tablet-wrapper').hasClass('hide')){
            var myElement = jQuery('.ad-above-header-container.header-ad-tablet-wrapper')[0];
            var bounding = myElement.getBoundingClientRect();
            var myElementHeight = myElement.offsetHeight;
            var myElementWidth = myElement.offsetWidth;

        
            if (bounding.top >= -myElementHeight 
                && bounding.left >= -myElementWidth
                && bounding.right <= (window.innerWidth || document.documentElement.clientWidth) + myElementWidth
                && bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight) + myElementHeight) {

                jQuery('body').addClass('TopAd');
            } else {
                jQuery('body').removeClass('TopAd');
            }
        }
        adContainerHeight = adContainer.offset().top + adContainer.outerHeight();
        }else{
            jQuery('body').removeClass('TopAd');
        }
    }
    else if(window.innerWidth < 900 && window.innerWidth > 0  /*&& jQuery('.ad-above-header-container.header-ad-mobile-wrapper').length > 0*/){
        if(jQuery('.ad-above-header-container.header-ad-mobile-wrapper').length > 0){
        adContainer = $('.ad-above-header-container.header-ad-mobile-wrapper');
        if(!jQuery('.ad-above-header-container.header-ad-mobile-wrapper').hasClass('hide')){
            var myElement = jQuery('.ad-above-header-container.header-ad-mobile-wrapper')[0];
            var bounding = myElement.getBoundingClientRect();
            var myElementHeight = myElement.offsetHeight;
            var myElementWidth = myElement.offsetWidth;

        
            if (bounding.top >= -myElementHeight 
                && bounding.left >= -myElementWidth
                && bounding.right <= (window.innerWidth || document.documentElement.clientWidth) + myElementWidth
                && bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight) + myElementHeight) {

                jQuery('body').addClass('TopAd');
            } else {
                jQuery('body').removeClass('TopAd');
            }
        }
        adContainerHeight = adContainer.offset().top + adContainer.outerHeight();
        }else{
            jQuery('body').removeClass('TopAd');
        }
    }
    if (scrollTop >= headerHeight + adContainerHeight) {
        $('body.home').addClass('sticky');
    } else {
        $('body.home').removeClass('sticky');
    }
});
function stickyHeaderforAdAboveHeader(){
    if(window.innerWidth >= 1200 && jQuery('.ad-above-header-container.header-ad-desktop-wrapper').length > 0){
        jQuery('body').addClass('TopAd');
    }else if(window.innerWidth >= 900 && jQuery('.ad-above-header-container.header-ad-tablet-wrapper').length > 0){
        jQuery('body').addClass('TopAd');
    }else if(window.innerWidth >= 0 && jQuery('.ad-above-header-container.header-ad-mobile-wrapper').length > 0){
        jQuery('body').addClass('TopAd');
    }else{
        jQuery('body').removeClass('TopAd');
    }
}
jQuery(window).resize(function () {
    /**ad based on the screen width */
   // renderAd();
   stickyHeaderforAdAboveHeader();
  
 });

// Function to determine the appropriate thumbWidth based on window width
function getThumbWidth(windowWidth) {
    if (windowWidth >= 1200) {
      return 108;
    } else if (windowWidth >= 900) {
      return 96;
    } else {
      return 75;
    }
}
function wegw_get_data(json_gpx_data) {
    /* Check if GPX file is uploaded */
    if (json_gpx_data !== undefined) {
        var gpx_trackpoints = json_gpx_data.trk.trkseg.trkpt;
        var tkpt_length = parseFloat(gpx_trackpoints.length);

        /* Get altitudes and load dynamically to acf fields */
        if (tkpt_length > 0) {
            var start_altitude = gpx_trackpoints[0].ele;
            var end_altitude = gpx_trackpoints[tkpt_length - 1].ele;

            let min_altitude = start_altitude;
            let max_altitude = start_altitude;
            for (var i = 0; i < tkpt_length; i++) {

                if (gpx_trackpoints[i].ele < min_altitude) {
                    min_altitude = gpx_trackpoints[i].ele;
                }

                if (gpx_trackpoints[i].ele > max_altitude) {
                    max_altitude = gpx_trackpoints[i].ele;
                }
            }

            var wayTime = 0,
                sum = 0;

            // Constants of the formula (schweizmobil)
            var arrConstants = [
                14.271, 3.6991, 2.5922, -1.4384,
                0.32105, 0.81542, -0.090261, -0.20757,
                0.010192, 0.028588, -0.00057466, -0.0021842,
                1.5176e-5, 8.6894e-5, -1.3584e-7, -1.4026e-6
            ];

            for (i = 1; i < gpx_trackpoints.length; i++) {
                var data = gpx_trackpoints[i];
                var dataBefore = gpx_trackpoints[i - 1];

                // Distance betwen 2 points
                var dist = data.distance - dataBefore.distance;
                var distance = dist * 1000;

                if (!distance) {
                    continue;
                }

                // Difference of elevation between 2 points
                var elevDiff = data.ele - dataBefore.ele;

                // Slope value between the 2 points
                var s = (elevDiff * 10.0) / distance;

                var minutesPerKilometer = 0;
                if (s > -4 && s < 4) {
                    for (var j = 0; j < arrConstants.length; j++) {
                        minutesPerKilometer += arrConstants[j] * Math.pow(s, j);
                    }
                    // outside the -40% to +40% range, we use a linear formula
                } else if (s > 0) {
                    minutesPerKilometer = (10 * s);
                } else {
                    minutesPerKilometer = (-10 * s);
                }
                wayTime += distance * minutesPerKilometer / 1000;
            }

            // var hike_hours = Number(Math.floor(Math.round(wayTime))/60).toFixed(2) + " h";
            var hike_hours = Number(Math.floor(Math.round(wayTime)) / 60).toFixed(2);
            // console.log("Total Way Time in hours = " + hike_hours);

            if (gpx_trackpoints.length) {

                var gpx_middle_cordinates = parseInt(gpx_trackpoints.length/2);
                var lat = parseFloat(gpx_trackpoints[gpx_middle_cordinates]["@attributes"].lat);
                var lon = parseFloat(gpx_trackpoints[gpx_middle_cordinates]["@attributes"].lon);

                var sumDown = 0;
                var sumUp = 0;
                for (var i = 0; i < gpx_trackpoints.length - 1; i++) {
                    var h1 = Math.round(gpx_trackpoints[i].ele) || 0;
                    var h2 = Math.round(gpx_trackpoints[i + 1].ele) || 0;
                    var dh = h2 - h1;
                    if (dh < 0) {
                        sumDown += dh;
                    } else if (dh >= 0) {
                        sumUp += dh;
                    }
                }

                var sumSlopeDist = 0;

                for (var i = 0; i < gpx_trackpoints.length - 1; i++) {
                    var h1 = gpx_trackpoints[i].ele || 0;
                    var h2 = gpx_trackpoints[i + 1].ele || 0;
                    var s1 = gpx_trackpoints[i].dist || 0;
                    var s2 = gpx_trackpoints[i + 1].dist || 0;
                    var dh = h2 - h1;
                    var ds = s2 - s1;
                    // Pythagorean theorem (hypotenuse: the slope/surface distance)
                    sumSlopeDist += Math.sqrt(Math.pow(dh, 2) + Math.pow(ds, 2));
                }

                var totalDistance = gpx_trackpoints[gpx_trackpoints.length - 1].distance;

                var GPXdata                 = new Array();
                GPXdata['latitude']         = lat;
                GPXdata['longitude']        = lon;
                GPXdata['dauer']            = hike_hours;
                GPXdata['km']               = totalDistance.toFixed(2);
                GPXdata['aufstieg']         = sumUp;
                GPXdata['abstieg']          = Math.abs(sumDown);
                GPXdata['tiefster_punkt']   = Number(min_altitude).toFixed(2);
                GPXdata['hochster_punkt']   = Number(max_altitude).toFixed(2);
                return GPXdata;
            }
        }
    }
}