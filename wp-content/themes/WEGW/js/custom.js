if (typeof $ == 'undefined') {
    var $ = jQuery;
}

jQuery(document).ready(function ($) {

    //easy fix to speed up the optical filled heart when logged in
    $('.single-wander-heart').on('click', function () {
        var isWatchlisted = $(this).data('watchlist');

        if (isWatchlisted === "true") {
            // Perform action if watchlisted
            $(this).data('watchlist', 'false'); // Update data attribute
            // Perform action here when already watchlisted
            //alert('Already watchlisted!'); // Example: show an alert
        } else {
            // Add the watchlist state and perform action if not watchlisted
            $(this).data('watchlist', 'true'); // Update data attribute
            // Perform action here when not watchlisted
            $(this).addClass('watchlisted'); // Example: add a class
        }
    });

    // show more on hiking comments would not from plugin script.
    $('.show-full-comment').on('click', function () {
        $(this).parent().parent().find('.long-comment-version').removeClass('hide');
        $(this).parent().addClass('hide');
    });

    $(".comment-imgs .each-comment-img img").on("click", function () {
        var originalDiv = $(this).parent();
        var clonedDiv = originalDiv.clone().addClass("cloned regMenu regWindow");
        var parentDiv = $('<div class="parent-zoom-image"></div>');
        clonedDiv.append('<div class="close_wrap"><span class="filter_close" onclick="closeReg()"></span></div>');
        parentDiv.append(clonedDiv);
        $(".comments-section-wrapper").after(parentDiv);
    });

    $(document).on("click", ".parent-zoom-image span.filter_close", function () {
        var parentZoomImage = $(this).closest('.parent-zoom-image');
        parentZoomImage.remove();
    });


    // $('.ad-section').append('<div id="div-ad-gds-1280-1">' +
    //                   '<script type="text/javascript">gbcallslot1280("div-ad-gds-1280-1", "");</script>' +
    //                   '</div>');

    stickyHeaderforAdAboveHeader();
    /** append download to the a tag who parent has class name is-style-download-button */
    var parentDivs = document.querySelectorAll('.is-style-download-button');

    parentDivs.forEach(function (parentDiv) {
        var link = parentDiv.querySelector('a');
        link.setAttribute('download', '');
    });


    if (jQuery(".demo-gallery").length > 0 || jQuery(".img-gallery-wrap").length > 0) {
        // Define URLs for CSS and JavaScript files
        var cssURLs = [
            window.location.origin + "/wp-content/themes/WEGW/css/lightgallery.min.css",
        ];

        var jsURLs = [
            window.location.origin + "/wp-content/themes/WEGW/js/lightgallery-umd.min.js", // OpenLayers library
            window.location.origin + "/wp-content/themes/WEGW/js/lg-autoplay-umd.min.js",
            window.location.origin + "/wp-content/themes/WEGW/js/lg-fullscreen-umd.min.js",
            window.location.origin + "/wp-content/themes/WEGW/js/lg-video-umd.min.js",
            window.location.origin + "/wp-content/themes/WEGW/js/lg-comment-umd.min.js",
            window.location.origin + "/wp-content/themes/WEGW/js/lg-thumbnail-umd.min.js",
            window.location.origin + "/wp-content/themes/WEGW/js/lg-zoom-umd.min.js",
            window.location.origin + "/wp-content/themes/WEGW/js/lg-hash-umd.min.js",
            window.location.origin + "/wp-content/themes/WEGW/js/lg-medium-zoom-umd.min.js",
            window.location.origin + "/wp-content/themes/WEGW/js/lg-pager-umd.min.js",
            window.location.origin + "/wp-content/themes/WEGW/js/lg-relative-caption-umd.min.js",
            window.location.origin + "/wp-content/themes/WEGW/js/lg-vimeo-thumbnail-umd.min.js"
        ];
        // Insert CSS files if not already loaded
        cssURLs.forEach(function (url) {
            insertCSS(url);
        });

        // Insert JavaScript files if not already loaded
        var loadedScripts = 0;
        function checkAllScriptsLoaded() {
            loadedScripts++;
            if (loadedScripts === jsURLs.length) {
                loadLightGallery();
            }
        }

        jsURLs.forEach(function (url) {
            insertJS(url, checkAllScriptsLoaded);
        });
    }

    // /**light gallery in hike detail page */  
    // if(jQuery(".demo-gallery").length > 0){
    //     lightGallery(document.getElementById('lightgallery'), {
    //         plugins: [lgThumbnail, lgComment, lgFullscreen, lgAutoplay, lgZoom],
    //         speed: 500,
    //         thumbnail:true,
    //         thumbHeight:"72px",
    //         thumbWidth: thumbWidth,
    //         thumbMargin:10,
    //         toggleThumb:true,
    //         download:false,
    //         allowMediaOverlap: true,
    //         showZoomInOutIcons:true,
    //         appendThumbnailsTo:".lg-components",
    //         showThumbByDefault: false,
    //         enableThumbDrag:true,
    //         enableThumbSwipe : true,
    //         mobileSettings:{ controls: false, showCloseIcon: true, fullScreen: true, toggleThumb:false, allowMediaOverlap: false}

    //     });

    // }

    // /**light gallery for master content page */ 
    // var elements = document.getElementsByClassName('img-gallery-wrap'); 
    // for (let item of elements) {
    //     lightGallery(item, {
    //         plugins: [lgThumbnail, lgComment, lgFullscreen, lgAutoplay, lgZoom],
    //         speed: 500,
    //         thumbnail:true,
    //         thumbHeight:"72px",
    //         thumbWidth: thumbWidth,
    //         thumbMargin:10,
    //         toggleThumb:true,
    //         download:false,
    //         allowMediaOverlap: true,
    //         showZoomInOutIcons:true,
    //         appendThumbnailsTo:".lg-components",
    //         showThumbByDefault: false,
    //         enableThumbDrag:true,
    //         enableThumbSwipe : true,
    //         mobileSettings:{ controls: false, showCloseIcon: true, fullScreen: true, toggleThumb:false, allowMediaOverlap: false}

    //     });
    // }

    /*accordion*/
    var acc = document.getElementsByClassName("accordion");
    var i;

    for (i = 0; i < acc.length; i++) {
        acc[i].addEventListener("click", function () {
            const parentWithMapFixedPosition = this.closest('.map-fixed-position');
            if (!parentWithMapFixedPosition) {
                /* Toggle between adding and removing the "active" class,
                to highlight the button that controls the panel */
                this.classList.toggle("active");

                /* Toggle between hiding and showing the active panel */
                var panel = this.nextElementSibling;
                if (this.parentElement.className === 'botom_layer_icon') {
                    if (panel.style.display === "block") {
                        panel.style.display = "none";
                    } else {
                        panel.style.display = "block";
                    }
                } else {
                    if (panel.style.maxHeight) {
                        panel.style.maxHeight = null;
                    } else {
                        panel.style.maxHeight = panel.scrollHeight + "px";
                    }
                }
            }
        });
    }

    jQuery('body').on('click', '.map-fixed-position .accordion', function (event) {
        this.classList.toggle("active");

        /* Toggle between hiding and showing the active panel */
        var panel = this.nextElementSibling;
        if (this.parentElement.className === 'botom_layer_icon') {
            if (panel.style.display === "block") {
                panel.style.display = "none";
            } else {
                panel.style.display = "block";
            }
        } else {
            if (panel.style.maxHeight) {
                panel.style.maxHeight = null;
            } else {
                panel.style.maxHeight = panel.scrollHeight + "px";
            }
        }
    });

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
        acc[i].addEventListener("click", function () {
            /* Toggle between adding and removing the "active" class,
            to highlight the button that controls the panel */

            jQuery(this).toggleClass("active");
            var panel = jQuery(this).children(":eq(1)");
            /* Toggle between hiding and showing the active panel with a smooth fade down effect */
            panel.slideToggle(300);

        });
    }

    jQuery('body').on('click', '.elevationGraph', function (event) {
        $('.options').toggleClass('open');
    });
    jQuery('body').on('click', '#mapOptions', function (event) {
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
    jQuery('.fc_check_wrap .check_wrapper input').on('click', function () {
        if (this.value === '20') {
            if (jQuery(".activity_type_1").prop('checked') === true) {
                jQuery(".activity_type_2").prop('checked', false);
                jQuery(".activity_type_3").prop('checked', false);
                jQuery(".fc_difficult_wt_block").removeClass('hide');
                jQuery(".fc_difficult_t_block").addClass('hide');
                jQuery(".difficulty_search").prop('checked', false);
                jQuery('.fc_heading.fc_diff_level').removeClass('fc_devel_default');
                jQuery('.fc_heading.fc_diff_level .fc_block_select_wrapper .fc_difficult_t_block.fc_block_select label').removeClass('active');
            } else {
                clearActivityCheck();

            }

        } else if (this.value === '18') {
            if (jQuery(".activity_type_2").prop('checked') === true) {
                jQuery(".activity_type_1").prop('checked', false);
                jQuery(".activity_type_3").prop('checked', false);
                jQuery(".fc_difficult_t_block").removeClass('hide');
                jQuery(".fc_difficult_wt_block").addClass('hide');
                jQuery(".difficulty_search").prop('checked', false);
                jQuery('.fc_heading.fc_diff_level').removeClass('fc_devel_default');
                jQuery('.fc_heading.fc_diff_level .fc_block_select_wrapper .fc_difficult_wt_block.fc_block_select label').removeClass('active');
            } else {
                clearActivityCheck();

            }

        } else if (this.value === '19') {

            jQuery(".activity_type_1").prop('checked', false);
            jQuery(".activity_type_2").prop('checked', false);
            jQuery(".fc_difficult_wt_block").removeClass('hide');
            jQuery(".fc_difficult_t_block").removeClass('hide');
            jQuery(".difficulty_search").prop('checked', false);
            jQuery('.fc_heading.fc_diff_level').addClass('fc_devel_default');
            jQuery('.fc_heading.fc_diff_level .fc_block_select_wrapper .fc_block_select label').removeClass('active');
        }
    });

    /**region page nav slider below featured image */
    $(".mainNav_region").owlCarousel({
        loop: false,
        autoWidth: true,
        dots: false,
        nav: false,
        autoplay: false,
        responsive: {
            0: {
                items: 1,
            },
            700: {
                items: 1,
            },
            900: {
                items: 1,
            },
            1200: {
                items: 1,

            }
        }
    });

    /**Region page teaser slider */
    jQuery('.owl-carousel.region_teaser_wrap').owlCarousel({
        nav: true,
        dots: false,
        loop: false,
        rewindNav: false,
        autoplay: false,
        navText: ["<div class='nav-btn prev-slide'></div>", "<div class='nav-btn next-slide'></div>"],
        responsive: {
            0: {
                margin: 16,
                items: 1,
                nav: false,
                stagePadding: 43
            },
            700: {
                margin: 7,
                items: 3,
                nav: false,
                stagePadding: 42
            },
            900: {
                margin: 9,
                items: 3,
                stagePadding: 72
            },
            1200: {
                margin: 12,
                items: 3,
                stagePadding: 100

            }
        }
    });

    /**Region icon slider slider */
    jQuery('.region_icon_slider').owlCarousel({
        nav: true,
        dots: false,
        loop: false,
        rewindNav: false,
        autoplay: false,
        navText: ["<div class='nav-btn prev-slide'></div>", "<div class='nav-btn next-slide'></div>"],
        responsive: {
            0: {
                margin: 8,
                items: 2,
                nav: false,
                stagePadding: 21
            },
            700: {
                margin: 7,
                items: 4,
                nav: false,
                stagePadding: 72
            },
            900: {
                margin: 9,
                items: 5,
                stagePadding: 56
            },
            1200: {
                margin: 12,
                items: 6,
                stagePadding: 87

            }
        }
    });

    // /** carousel for lightgallery as in master content(to make it as a slider if there are more than one image) */
    // for (let galleyItem of elements) {
    //     if(galleyItem.children.length > 1){
    //         jQuery(galleyItem).owlCarousel({
    //             items:1,
    //             lazyLoad:true,
    //             loop:true,
    //             dots:false,
    //             nav:true,
    //             navText: ["<div class='nav-btn gal_prev-slide'></div>","<div class='nav-btn gal_next-slide'></div>"],
    //             autoplay: 2000,
    //             onInitialized  : counter, 
    //             onTranslated : counter,
    //             afterMove: function(elem) {
    //                 var current = this.currentItem;
    //                 var currentImg = elem.find('.owl-item').eq(current).find('.justified-gallery');
    //                 jQuery('.figcaption').text(currentImg.attr('data-sub-html'));
    //             }
    //         });
    //     }
    //     /** to show the caption of light gallery as in masdter content page just below the image */
    //     if(galleyItem.children.length <= 1){
    //         var currentImg = jQuery(galleyItem).eq(0).find('.justified-gallery');
    //         jQuery(galleyItem).parent().parent().find('.figcaption').text(currentImg.attr('data-sub-html'));
    //     }
    // }

    /**hike detail page region slider*/
    jQuery('.owl-carousel.wander-in-region-carousel').owlCarousel({
        nav: true,
        dots: false,
        loop: false,
        rewindNav: false,
        autoplay: false,
        navText: ["<div class='nav-btn prev-slide'></div>", "<div class='nav-btn next-slide'></div>"],
        responsive: {
            0: {
                margin: 17,
                items: 1,
                nav: false,
                stagePadding: 43
            },
            700: {
                margin: 26,
                items: 2,
                nav: false,
                stagePadding: 92
            },
            900: {
                margin: 26,
                items: 3,
                stagePadding: 49
            },
            1200: {
                margin: 36,
                items: 3,
                stagePadding: 115

            }
        }
    });
    //angebote slider
    jQuery('.angebote_slider').owlCarousel({
        nav: true,
        dots: false,
        loop: false,
        rewindNav: false,
        autoplay: false,
        navText: ["<div class='nav-btn prev-slide'></div>", "<div class='nav-btn next-slide'></div>"],
        responsive: {
            0: {
                margin: 17,
                items: 1,
                nav: false,
                stagePadding: 43
            },
            700: {
                margin: 26,
                items: 2,
                nav: false,
                stagePadding: 92
            },
            900: {
                margin: 26,
                items: 3,
                stagePadding: 49
            },
            1200: {
                margin: 36,
                items: 3,
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

    /*keyup event on search field*/
    jQuery(".search input").on("keyup", function (e) {

        /* show/hide close icon in search in menu sidebar  */
        if (jQuery('.navigation_search input').val().length != 0) {
            jQuery('.navigation_search_close').removeClass("hide");
        } else {
            jQuery('.navigation_search_close').addClass("hide");
        }

        /* show/hide close icon in search in header  */
        if (jQuery('.head_navigation_search input').val().length != 0) {
            jQuery('.head_navigation_search_close').removeClass("hide");
        } else {
            jQuery('.head_navigation_search_close').addClass("hide");
        }

        /* show/hide close icon in search in responsive cluster map(tourenportal)  */
        if (jQuery('#map-resp .map_main_search input').val().length != 0) {
            jQuery('#map-resp .map_main_search_close').removeClass("hide");
        } else {
            jQuery('#map-resp .map_main_search_close').addClass("hide");
        }

        /* show/hide close icon in search in desktop cluster map(tourenportal)  */
        if (jQuery('#map_desktop .map_main_search input').val().length != 0) {
            jQuery('#map_desktop .map_main_search_close').removeClass("hide");
        } else {
            jQuery('#map_desktop .map_main_search_close').addClass("hide");
        }
    });

    if (jQuery('.searchinputField input').length > 0) {
        /* show/hide close icon in search in search results page  */
        if (jQuery('.searchinputField input').val().length != 0) {
            jQuery('.searchResult_search_close').removeClass("hide");
        } else {
            jQuery('.searchResult_search_close').addClass("hide");
        }
    }


    jQuery(".searchinputField input").on("keyup", function (e) {
        /* show/hide close icon in search in search results page  */
        if (jQuery('.searchinputField input').val().length != 0) {
            jQuery('.searchResult_search_close').removeClass("hide");
        } else {
            jQuery('.searchResult_search_close').addClass("hide");
        }
    });

    // header search
    // jQuery('.header_menu .search_head').keyup(function (e) {
    //     if (e.key === 'Enter' || e.keyCode === 13) {

    //     /* Remove `dragMap` from load more btn */
    //     $('#wanderung-loadmore').attr("data-event", "");

    //     var data = generate_data_array(null);

    //     data['action'] = "get_wanderung_sort_query";
    //     data['nonce'] = ajax_object.ajax_nonce;
    //     jQuery.ajax({
    //         url: ajax_object.ajax_url,
    //         type: "post",
    //         data: data,
    //         beforeSend: function () {
    //             loadmore.show();
    //             jQuery("#wegw-preloader").css("display", "block");
    //         },
    //         success: function (response) {
    //             var posts = JSON.parse(response);
    //             var countp = jQuery(posts[0]).filter(".single-wander").length;

    //             if (posts == "" || countp < 1) {
    //                 jQuery(".LoadMore").hide();
    //             }
    //             if (countp > 0) {
    //                 if (filterOtherPage === 'region') {
    //                     jQuery(".region-single-wander-wrappe").html(posts);
    //                 } else {
    //                     jQuery(".single-wander-wrappe").html(posts);
    //                 }

    //                 jQuery(".noWanderung").remove();
    //             } else {
    //                 if (filterOtherPage === 'region') {
    //                     jQuery(".region-single-wander-wrappe").html('');
    //                 } else {
    //                     jQuery(".single-wander-wrappe").html('');
    //                 }

    //                 if (jQuery(".noWanderung").length < 1) {
    //                     jQuery("#wanderung-loadmore").before('<h2 class="noWanderung">' + posts[1] + '</h2>');
    //                 }
    //             }
    //             jQuery("#wegw-preloader").css("display", "none");
    //             jQuery('#searchbox_main_filter').val(jQuery('.header_menu .search_head').val());
    //         },
    //         error: function () { },
    //     });

    //     // wegw_map_filter_results('btnClick', e, filterOtherPage);
    //     FilterList('btnClick', e, filterOtherPage);
    //     }
    // });

    /* clear the menu serach when clicked on close icon  */
    jQuery('.navigation_search .navigation_search_close').on("click", function (e) {
        jQuery(".navigation_search input").val("");
        jQuery('.navigation_search_close').addClass("hide");
    });

    /* clear the serach on responsive map when clicked on close icon  */
    jQuery('#map-resp .map_main_search  .map_main_search_close').on("click", function (e) {
        jQuery("#map-resp .map_main_search  input").val("");
        jQuery('#map-resp .map_main_search_close').addClass("hide");
    });

    /* clear the serach on desktop map when clicked on close icon  */
    jQuery('#map_desktop .map_main_search  .map_main_search_close').on("click", function (e) {
        jQuery("#map_desktop .map_main_search  input").val("");
        jQuery('#map_desktop .map_main_search_close').addClass("hide");
    });

    /* clear the serach in header when clicked on close icon  */
    jQuery('.head_navigation_search .head_navigation_search_close').on("click", function (e) {
        jQuery(".head_navigation_search input").val("");
        jQuery('.head_navigation_search_close').addClass("hide");
    });

    /* clear the serach in search results page  when clicked on close icon  */
    jQuery('.searchinputField .searchResult_search_close').on("click", function (e) {
        jQuery(".searchinputField input").val("");
        jQuery('.searchResult_search_close').addClass("hide");
    });

    /* Fn to check if the ad div is present and visible */
    checkAdVisibility();

});

function toRemoveEmptyHikeInfo() {
    // Remove "hike_level" div if "hike-level" child does not have a class
    jQuery('.hike_level').each(function () {
        var hikeLevelChild = jQuery(this).children().eq(0);
        if (!hikeLevelChild.attr('class').match(/(^|\s)hike-\S+/)) {
            $(this).remove();
        }
    });
    // Remove "hike_time" div if "hike-time" child p tag has only h
    jQuery('.hike_time').each(function () {
        var hikeTimeChild = jQuery(this).find('p');
        if (hikeTimeChild.text().trim() === 'h') {
            jQuery(this).remove();
        }
    });
    // Remove "hike_distance" div if "hike_distance" child p tag has only h
    jQuery('.hike_distance').each(function () {
        var hikeTimeChild = jQuery(this).find('p');
        if (hikeTimeChild.text().trim() === 'km') {
            jQuery(this).remove();
        }
    });
    // Remove "hike_ascent" div if "hike_ascent" child p tag has only h
    jQuery('.hike_ascent').each(function () {
        var hikeTimeChild = jQuery(this).find('p');
        if (hikeTimeChild.text().trim() === 'm') {
            jQuery(this).remove();
        }
    });
    // Remove "hike_descent" div if "hike_descent" child p tag has only h
    jQuery('.hike_descent').each(function () {
        var hikeTimeChild = jQuery(this).find('p');
        if (hikeTimeChild.text().trim() === 'm') {
            jQuery(this).remove();
        }
    });
    // Remove "hike_month" div if "hike_month" child p tag has only h
    jQuery('.hike_month').each(function () {
        var hikeTimeChild = jQuery(this).find('p');
        if (hikeTimeChild.text().trim() === '') {
            jQuery(this).remove();
        }
    });
}

/* slide count of the carousel slider*/
function counter(event) {
    var element = event.target;         // DOM element, in this example .owl-carousel
    var items = event.item.count;     // Number of items
    var item = event.item.index + 1;     // Position of the current item

    // it loop is true then reset counter from 1
    if (item > items) {
        item = item - items
    }
    var current = event.item.index;
    var currentImg = jQuery(element).find('.owl-item').eq(current).find('.justified-gallery');
    jQuery(element).parent().parent().find('.figcaption').text(currentImg.attr('data-sub-html'));
    jQuery(element).parent().parent().find('#count').html(item + "/" + items);
}

/*close elevation map*/
function closeElement(element) {
    // if($('.single-wander-wrappe-json').length > 0 || jQuery('.map_region').length > 0){
    jQuery('#weg-map-popup' + jQuery(element).attr("data-hikeid")).css("display", "none");
    // jQuery('body').removeClass("weg_ele_popup_show");
    jQuery('#weg-map-popup' + jQuery(element).attr("data-hikeid") + ' .ol-viewport').addClass("hide");
    jQuery(".popover").css("display", "none");
    // }
    // else{
    //     jQuery('#weg-map-popup').css("display", "none");
    //     jQuery('body').removeClass("weg_ele_popup_show");
    //     jQuery('#weg-map-popup .ol-viewport').addClass("hide");
    //     jQuery(".popover").css("display", "none");
    // }
}
/*close cluster map in responsive*/
function closeElementMapResp(element) {
    jQuery('#map-resp').addClass('hide');
    jQuery("#map-resp .ol-viewport").addClass('hide');
}
/*close login sidebar*/
function closeSummitLoginContent() {
    jQuery(".summitLoginMenu").addClass("summitLoginWindow");
    if ($('.home').length > 0) {
        $('.login').toggleClass("loginHome");
    }
}
/*close navigation menu summit book sidebar*/
function closeNavigationMenu() {
    jQuery(".userNavigationMenu").addClass("userNavigationWindow");
    if ($('.home').length > 0) {
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
function openLightGallery(e) {
    jQuery(e.nextElementSibling).find(".justified-gallery a:first-child > img").eq(0).trigger("click");
    jQuery(e.nextElementSibling).find(".owl-item.active > .justified-gallery a >img").eq(0).trigger("click");
}

/*loader*/
jQuery(window).on("load", function () {
    setTimeout(function () {
        jQuery("#wegw-preloader").css("display", "none");
    }, 1000);

    /**ad based on the screen width */
    stickyHeaderforAdAboveHeader();
});

/*clear activity filter*/
function clearActivityCheck() {
    jQuery(".activity_type_1").prop('checked', false);
    jQuery(".activity_type_2").prop('checked', false);
    jQuery(".activity_type_3").prop('checked', false);
    jQuery('.difficulty_search').prop('checked', false);
    jQuery(".fc_difficult_wt_block").removeClass('hide');
    jQuery(".fc_difficult_t_block").removeClass('hide');
    jQuery('.fc_heading.fc_diff_level').addClass('fc_devel_default');
    jQuery('.fc_heading.fc_diff_level .fc_block_select_wrapper .fc_block_select label').removeClass('active');
}
/**close the ad above the header */
function adCloseHeader() {
    jQuery('.ad-above-header-container').addClass("hide");
    jQuery('body').removeClass('Top');
}
/** scroll to header on the button click over the ad above the header */
function adScrollToHeader() {
    if (jQuery('.home').length > 0) {
        jQuery('html, body').animate({
            scrollTop: jQuery('header').offset().top + 0
        }, 500);
    } else {
        jQuery('html, body').animate({
            scrollTop: jQuery('header').offset().top + 2
        }, 500);
    }

}

jQuery(window).scroll(function (elem) {
    var headerHeight = $('header').outerHeight();
    var scrollTop = $(this).scrollTop();
    var adContainer;
    var adContainerHeight = 0;

    //  jQuery(window).scrollTop() > 100 ? $('body.home').addClass('sticky') : $('body.home').removeClass('sticky');

    /**ad above the header */
    if (window.innerWidth >= 1200 /*&& jQuery('.ad-above-header-container.header-ad-desktop-wrapper').length > 0*/) {
        if (jQuery('.ad-above-header-container.header-ad-desktop-wrapper').length > 0) {
            adContainer = $('.ad-above-header-container.header-ad-desktop-wrapper');
            if (!jQuery('.ad-above-header-container.header-ad-desktop-wrapper').hasClass('hide')) {
                var myElement = jQuery('.ad-above-header-container.header-ad-desktop-wrapper')[0];
                var bounding = myElement.getBoundingClientRect();
                var myElementHeight = myElement.offsetHeight;
                var myElementWidth = myElement.offsetWidth;


                if (bounding.top >= -myElementHeight
                    && bounding.left >= -myElementWidth
                    && bounding.right <= (window.innerWidth || document.documentElement.clientWidth) + myElementWidth
                    && bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight) + myElementHeight) {

                    jQuery('body').addClass('Top');
                } else {
                    jQuery('body').removeClass('Top');
                }
            }
            adContainerHeight = adContainer.offset().top + adContainer.outerHeight();
        } else {
            jQuery('body').removeClass('Top');
        }
    }
    else if (window.innerWidth < 1200 && window.innerWidth >= 900 /*&& jQuery('.ad-above-header-container.header-ad-tablet-wrapper').length > 0*/) {
        if (jQuery('.ad-above-header-container.header-ad-tablet-wrapper').length > 0) {
            adContainer = $('.ad-above-header-container.header-ad-tablet-wrapper');
            if (!jQuery('.ad-above-header-container.header-ad-tablet-wrapper').hasClass('hide')) {
                var myElement = jQuery('.ad-above-header-container.header-ad-tablet-wrapper')[0];
                var bounding = myElement.getBoundingClientRect();
                var myElementHeight = myElement.offsetHeight;
                var myElementWidth = myElement.offsetWidth;


                if (bounding.top >= -myElementHeight
                    && bounding.left >= -myElementWidth
                    && bounding.right <= (window.innerWidth || document.documentElement.clientWidth) + myElementWidth
                    && bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight) + myElementHeight) {

                    jQuery('body').addClass('Top');
                } else {
                    jQuery('body').removeClass('Top');
                }
            }
            adContainerHeight = adContainer.offset().top + adContainer.outerHeight();
        } else {
            jQuery('body').removeClass('Top');
        }
    }
    else if (window.innerWidth < 900 && window.innerWidth > 0  /*&& jQuery('.ad-above-header-container.header-ad-mobile-wrapper').length > 0*/) {
        if (jQuery('.ad-above-header-container.header-ad-mobile-wrapper').length > 0) {
            adContainer = $('.ad-above-header-container.header-ad-mobile-wrapper');
            if (!jQuery('.ad-above-header-container.header-ad-mobile-wrapper').hasClass('hide')) {
                var myElement = jQuery('.ad-above-header-container.header-ad-mobile-wrapper')[0];
                var bounding = myElement.getBoundingClientRect();
                var myElementHeight = myElement.offsetHeight;
                var myElementWidth = myElement.offsetWidth;


                if (bounding.top >= -myElementHeight
                    && bounding.left >= -myElementWidth
                    && bounding.right <= (window.innerWidth || document.documentElement.clientWidth) + myElementWidth
                    && bounding.bottom <= (window.innerHeight || document.documentElement.clientHeight) + myElementHeight) {

                    jQuery('body').addClass('Top');
                } else {
                    jQuery('body').removeClass('Top');
                }
            }
            adContainerHeight = adContainer.offset().top + adContainer.outerHeight();
        } else {
            jQuery('body').removeClass('Top');
        }
    }
    if (scrollTop >= headerHeight + adContainerHeight) {
        $('body.home').addClass('sticky');
    } else {
        $('body.home').removeClass('sticky');
    }
});

function stickyHeaderforAdAboveHeader() {
    if (window.innerWidth >= 1200 && jQuery('.ad-above-header-container.header-ad-desktop-wrapper').length > 0) {
        jQuery('body').addClass('Top');
    } else if (window.innerWidth >= 900 && jQuery('.ad-above-header-container.header-ad-tablet-wrapper').length > 0) {
        jQuery('body').addClass('Top');
    } else if (window.innerWidth >= 0 && jQuery('.ad-above-header-container.header-ad-mobile-wrapper').length > 0) {
        jQuery('body').addClass('Top');
    } else {
        jQuery('body').removeClass('Top');
    }
}
jQuery(window).resize(function () {
    /**ad based on the screen width */
    stickyHeaderforAdAboveHeader();
});

function checkAdVisibility() {
    var closeAdDiv = document.querySelector('.close_ad');
    var adVisibility = document.querySelector('.ad-visibility');
    // Check if the ad div is present and visible

    // Initialize a variable to hold the banner div
    var bannerDiv = null;

    if (adVisibility !== null) {
        // Get all div children inside adVisibilityDiv
        var childDivs = adVisibility.querySelectorAll('div');

        if (childDivs !== null) {
            // Iterate through the child divs
            childDivs.forEach(function (div) {
                // Check if this div is not the close_ad
                if (!div.classList.contains('close_ad')) {
                    bannerDiv = div; // Assign the first non-close_ad div found
                    return; // Exit the loop after the first match
                }
            });
        }

    }


    if (bannerDiv !== null) {
        if (bannerDiv.innerHTML.length > 0) {
            closeAdDiv.style.display = 'block'; // Show close button
        } else {
            closeAdDiv.style.display = 'none'; // Hide close button
        }
    }
}

// Call the function on page load
window.onload = function () {
    checkAdVisibility();

    // Set up MutationObserver to monitor changes to the body
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            // Check if the ad div element is added/modified
            if (mutation.type === 'childList') {
                checkAdVisibility();
            }
        });
    });

    // Start observing the body for changes
    observer.observe(document.body, { childList: true, subtree: true });
};

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

/** header search clear  */
function clearSearch() {
    jQuery(".header_menu .search.search input").val("");
    jQuery('.header_menu .filter_search_close').addClass("hide");
    jQuery('#searchbox_main_filter').val("");
    //  document.cookie = 'wegw_loc' +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    location.reload();
}

//loadmore for blog

jQuery(document).on("click", "#blog-loadmore", function () {
    var itemcount = jQuery(".blog-wander").length;
    var loadmore = jQuery(".LoadMore");
    var listDiv = jQuery('.blog_list');
    var page_type = jQuery(".page_type").val();
    jQuery('#loader-icon').removeClass("hide");
    var data = {
        'action': 'wanderung_blogs_load_more',
        'nonce': ajax_object.ajax_nonce,
        'count': itemcount,
        'page_type': page_type,
    };

    jQuery.ajax({
        url: ajax_object.ajax_url,
        type: "post",
        data: data,
        beforeSend: function () {
            loadmore.addClass("active");
        },
        complete: function () {
            loadmore.removeClass("active");
        },
        success: function (response) {
            var posts = JSON.parse(response);
            var countp = jQuery(posts[0]).filter(".blog-wander").length;
            console.log(countp);
            if (posts == "" || countp < 1) {
                console.log("empty");
                jQuery(".LoadMore").hide();
            }
            jQuery('#loader-icon').addClass("hide");

            if (countp > 0) {
                console.log(posts[0]);
                listDiv.append(posts[0]);
                jQuery(".noWanderung").remove();
            } else {

                if (jQuery(".noWanderung").length < 1) {
                    jQuery("#blog-loadmore").before(posts);
                }
            }
        },
        error: function () {
            jQuery('#loader-icon').addClass("hide");
        },
    });

});

//loadmore for Search page
jQuery(document).on("click", "#search-loadmore", function () {

    var loadmore = jQuery(".LoadMore");

    var itemcounts = jQuery(this).data("count");
    var search_query = jQuery(this).data("query");
    var search_offset = jQuery(this).data("offset");
    var post_type = jQuery(this).attr("data-postType");
    let next_offset = parseInt(search_offset) + 9;
    jQuery(this).data("offset", next_offset);

    var search_nonce = jQuery(this).data("nonce");


    if (itemcounts < next_offset) {
        jQuery('#search-loadmore').hide();
        jQuery('.noWanderungSearchPost').show();
    }


    jQuery('#loader-icon').removeClass("hide");
    var data = {
        'action': 'wanderung_search_load_more',
        'count': itemcounts,
        'search_query': search_query,
        'offset': search_offset,
        'search_nonce': search_nonce,
        'post_type': post_type

    };

    jQuery.ajax({
        url: ajax_object.ajax_url,
        type: "post",
        data: data,
        beforeSend: function () {
            loadmore.addClass("active");
        },
        complete: function () {
            loadmore.removeClass("active");
        },
        success: function (response) {
            jQuery('.searchResult_list').append(response.data);
            jQuery('#loader-icon').addClass("hide");
        },
        error: function () {
            jQuery('#loader-icon').addClass("hide");
        },
    });

});

//loadmore for taxonomies
jQuery(document).on("click", "#taxonomy-loadmore", function () {

    var loadmore = jQuery(".LoadMore");

    var itemcounts = jQuery(this).data("count");
    var search_offset = jQuery(this).data("offset");
    var post_type = jQuery(this).attr("data-postType");
    var taxonomy = jQuery(this).attr("data-taxonomy");
    var term_id = jQuery(this).attr("data-termId");
    let next_offset = parseInt(search_offset) + 9;
    jQuery(this).data("offset", next_offset);

    var taxonomy_nonce = jQuery(this).data("nonce");


    if (itemcounts < next_offset) {
        jQuery('#taxonomy-loadmore').hide();
        jQuery('.noWanderungSearchPost').show();
    }


    jQuery('#loader-icon').removeClass("hide");
    var data = {
        'action': 'wanderung_taxonomy_load_more',
        'count': itemcounts,
        'offset': search_offset,
        'taxonomy_nonce': taxonomy_nonce,
        'post_type': post_type,
        'taxonomy': taxonomy,
        'term_id': term_id
    };

    jQuery.ajax({
        url: ajax_object.ajax_url,
        type: "post",
        data: data,
        beforeSend: function () {
            loadmore.addClass("active");
        },
        complete: function () {
            loadmore.removeClass("active");
        },
        success: function (response) {
            jQuery('.searchResult_list').append(response.data);
            jQuery('#loader-icon').addClass("hide");
        },
        error: function () {
            jQuery('#loader-icon').addClass("hide");
        },
    });

});

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

                var gpx_middle_cordinates = parseInt(gpx_trackpoints.length / 2);
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

                var GPXdata = new Array();
                GPXdata['latitude'] = lat;
                GPXdata['longitude'] = lon;
                GPXdata['dauer'] = hike_hours;
                GPXdata['km'] = totalDistance.toFixed(2);
                GPXdata['aufstieg'] = sumUp;
                GPXdata['abstieg'] = Math.abs(sumDown);
                GPXdata['tiefster_punkt'] = Number(min_altitude).toFixed(2);
                GPXdata['hochster_punkt'] = Number(max_altitude).toFixed(2);
                return GPXdata;
            }
        }
    }
}


// Function to check if a CSS file is already loaded
function isCSSLoaded(url) {
    return document.querySelector('link[href="' + url + '"]') !== null;
}

// Function to check if a JavaScript file is already loaded
function isJSLoaded(url) {
    return document.querySelector('script[src="' + url + '"]') !== null;
}

// Function to insert CSS dynamically if not already loaded
function insertCSS(url) {
    if (!isCSSLoaded(url)) {
        var link = document.createElement("link");
        link.rel = "stylesheet";
        link.type = "text/css";
        link.href = url;
        var head = document.head || document.getElementsByTagName('head')[0];
        head.insertBefore(link, head.lastChild.nextSibling);
    }
}

// Function to insert JavaScript dynamically if not already loaded
function insertJS(url, callback) {
    if (!isJSLoaded(url)) {
        var script = document.createElement("script");
        script.src = url;
        script.onload = callback;
        var head = document.head || document.getElementsByTagName('head')[0];
        head.insertBefore(script, head.lastChild.nextSibling);
    } else {
        // If the script is already loaded, call the callback immediately
        callback();
    }
}

function loadLightGallery() {
    var windowWidth = window.innerWidth;
    var thumbWidth = getThumbWidth(windowWidth);
    /**light gallery in hike detail page */
    if (jQuery(".demo-gallery").length > 0) {
        lightGallery(document.getElementById('lightgallery'), {
            plugins: [lgThumbnail, lgComment, lgFullscreen, lgAutoplay, lgZoom],
            speed: 500,
            thumbnail: true,
            thumbHeight: "72px",
            thumbWidth: thumbWidth,
            thumbMargin: 10,
            toggleThumb: true,
            download: false,
            allowMediaOverlap: true,
            showZoomInOutIcons: true,
            appendThumbnailsTo: ".lg-components",
            showThumbByDefault: false,
            enableThumbDrag: true,
            enableThumbSwipe: true,
            mobileSettings: { controls: false, showCloseIcon: true, fullScreen: true, toggleThumb: false, allowMediaOverlap: false }

        });

    }

    /**light gallery for master content page */
    var elements = document.getElementsByClassName('img-gallery-wrap');
    for (let item of elements) {
        lightGallery(item, {
            plugins: [lgThumbnail, lgComment, lgFullscreen, lgAutoplay, lgZoom],
            speed: 500,
            thumbnail: true,
            thumbHeight: "72px",
            thumbWidth: thumbWidth,
            thumbMargin: 10,
            toggleThumb: true,
            download: false,
            allowMediaOverlap: true,
            showZoomInOutIcons: true,
            appendThumbnailsTo: ".lg-components",
            showThumbByDefault: false,
            enableThumbDrag: true,
            enableThumbSwipe: true,
            mobileSettings: { controls: false, showCloseIcon: true, fullScreen: true, toggleThumb: false, allowMediaOverlap: false }

        });
    }

    /** carousel for lightgallery as in master content(to make it as a slider if there are more than one image) */
    for (let galleyItem of elements) {
        if (galleyItem.children.length > 1) {
            jQuery(galleyItem).owlCarousel({
                items: 1,
                lazyLoad: true,
                loop: true,
                dots: false,
                nav: true,
                navText: ["<div class='nav-btn gal_prev-slide'></div>", "<div class='nav-btn gal_next-slide'></div>"],
                autoplay: 2000,
                onInitialized: counter,
                onTranslated: counter,
                afterMove: function (elem) {
                    var current = this.currentItem;
                    var currentImg = elem.find('.owl-item').eq(current).find('.justified-gallery');
                    jQuery('.figcaption').text(currentImg.attr('data-sub-html'));
                }
            });
        }
        /** to show the caption of light gallery as in masdter content page just below the image */
        if (galleyItem.children.length <= 1) {
            var currentImg = jQuery(galleyItem).eq(0).find('.justified-gallery');
            jQuery(galleyItem).parent().parent().find('.figcaption').text(currentImg.attr('data-sub-html'));
        }
    }
}

function infoIconClicked(event, parentId) {
    event.preventDefault();

    if (window.innerWidth < 767) {
        $("#" + parentId + " .snow_info_details").addClass("hide");
        window.open('https://wegwandern.ch/schneekarten-wo-liegt-jetzt-schnee/', '_blank');
    } else {
        $("#" + parentId + " .snow_info_details").removeClass("hide");
    }
}

function infoIconClosed(event, parentId) {
    $("#" + parentId + " .snow_info_details").addClass("hide");
}

// Add this before the ad script loads (for testing only)
window.tcfApi = {
    getTCData: function() {
        return { tcString: "1---" }; // Minimal consent string
    }
};